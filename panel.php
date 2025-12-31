<?php
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

// Oturum + veritabanı kullanıcısı kontrolü
$user = requireLogin();
$pdo  = Database::connection();

// Kullanıcıyı çek
$stmt = $pdo->prepare('SELECT id, name, email, role, phone, birthdate FROM users WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $user['id']]);
$dbUser = $stmt->fetch();

if (!$dbUser) {
    $_SESSION = [];
    session_destroy();
    header('Location: login.php');
    exit;
}

$displayName = $dbUser['name'] ?? t('panel.user.default');
$role        = $dbUser['role'] ?? 'user';
$isAdmin     = $role === 'admin';

$email    = (string)($dbUser['email'] ?? '');
$phone    = (string)($dbUser['phone'] ?? '');
$birthRaw = $dbUser['birthdate'] ?? null;

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(string $token): bool
{
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function formatBirthdate(?string $date): string
{
    if (!$date) return '';
    $dt = DateTime::createFromFormat('Y-m-d', $date);
    return $dt ? $dt->format('d.m.Y') : '';
}

$profileMessage  = '';
$passwordMessage = '';

// Öneri listeleri
$pendingSuggestions = $pdo->prepare('SELECT content, created_at FROM suggestions WHERE user_id = :id AND status = "pending" AND (deleted_at IS NULL) ORDER BY created_at DESC');
$pendingSuggestions->execute(['id' => $dbUser['id']]);
$pendingSuggestions = $pendingSuggestions->fetchAll();

$approvedSuggestions = $pdo->prepare('SELECT content, created_at, admin_reply, replied_at FROM suggestions WHERE user_id = :id AND status = "approved" AND (deleted_at IS NULL) ORDER BY created_at DESC');
$approvedSuggestions->execute(['id' => $dbUser['id']]);
$approvedSuggestions = $approvedSuggestions->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $token  = $_POST['csrf'] ?? '';

    if (!verify_csrf($token)) {
        $profileMessage  = t('panel.errors.csrf');
        $passwordMessage = t('panel.errors.csrf');
    } else {

        // Profil güncelle
        if ($action === 'profile_update') {
            $newEmail    = mb_strtolower(trim((string)($_POST['email'] ?? '')));
            $newPhoneRaw = preg_replace('/\D+/', '', (string)($_POST['phone'] ?? ''));
            $newBirthRaw = trim((string)($_POST['birthdate'] ?? ''));
            $newBirth    = null;

            if ($newEmail === '' || $newPhoneRaw === '') {
                $profileMessage = t('panel.profile.errors.required');
            } elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
                $profileMessage = t('panel.profile.errors.email');
            } elseif (!preg_match('/^05\d{9}$/', $newPhoneRaw)) {
                $profileMessage = t('panel.profile.errors.phone');
            } else {
                if ($newBirthRaw !== '') {
                    $birthNorm = str_replace('-', '.', $newBirthRaw);
                    $dt = DateTime::createFromFormat('d.m.Y', $birthNorm);
                    $err = DateTime::getLastErrors();

                    if (
                        !$dt ||
                        ($err['warning_count'] ?? 0) > 0 ||
                        ($err['error_count'] ?? 0) > 0 ||
                        $dt->format('d.m.Y') !== $birthNorm
                    ) {
                        $profileMessage = t('panel.profile.errors.birth_invalid');
                    } else {
                        $dt->setTime(0, 0, 0);
                        $minDate = new DateTime('1900-01-01');
                        $today   = new DateTime('today');

                        if ($dt < $minDate) {
                            $profileMessage = t('panel.profile.errors.birth_min');
                        } elseif ($dt > $today) {
                            $profileMessage = t('panel.profile.errors.birth_future');
                        } else {
                            $newBirth = $dt->format('Y-m-d');
                        }
                    }
                }
            }

            if ($profileMessage === '') {
                // E-posta benzersizlik kontrolü
                $chk = $pdo->prepare('SELECT id FROM users WHERE email = :email AND id <> :id LIMIT 1');
                $chk->execute(['email' => $newEmail, 'id' => $dbUser['id']]);

                if ($chk->fetch()) {
                    $profileMessage = t('panel.profile.errors.email_used');
                } else {
                    $upd = $pdo->prepare('
                        UPDATE users
                        SET email = :email, phone = :phone, birthdate = :birth, session_version = session_version + 1
                        WHERE id = :id
                        LIMIT 1
                    ');
                    $upd->execute([
                        'email' => $newEmail,
                        'phone' => $newPhoneRaw,
                        'birth' => $newBirth,
                        'id'    => $dbUser['id'],
                    ]);

                    $profileMessage = t('panel.profile.success');

                    // Ekranda güncel göster
                    $email    = $newEmail;
                    $phone    = $newPhoneRaw;
                    $birthRaw = $newBirth;
                }
            }
        }

        // Şifre değiştir
        if ($action === 'password_change') {
            $current = (string)($_POST['current_password'] ?? '');
            $new     = (string)($_POST['new_password'] ?? '');
            $confirm = (string)($_POST['new_password_confirm'] ?? '');

            if ($current === '' || $new === '' || $confirm === '') {
                $passwordMessage = t('panel.password.errors.required');
            } elseif ($new !== $confirm) {
                $passwordMessage = t('panel.password.errors.mismatch');
            } elseif (mb_strlen($new) < 8) {
                $passwordMessage = t('panel.password.errors.length');
            } else {
                $stmt = $pdo->prepare('SELECT password FROM users WHERE id = ? LIMIT 1');
                $stmt->execute([$dbUser['id']]);
                $row = $stmt->fetch();

                if (!$row || !password_verify($current, (string)$row['password'])) {
                    $passwordMessage = t('panel.password.errors.old_wrong');
                } else {
                    $hash = password_hash($new, PASSWORD_DEFAULT);
                    $update = $pdo->prepare('UPDATE users SET password = ?, session_version = session_version + 1 WHERE id = ? LIMIT 1');
                    $update->execute([$hash, $dbUser['id']]);

                    $passwordMessage = t('panel.password.success');
                }
            }
        }
    }
}

$csrf = csrf_token();
$birthFormatted = formatBirthdate($birthRaw);
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo t('panel.head.title'); ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600&family=General+Sans:wght@400;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="/assets/css/style.css?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'].'/assets/css/style.css') ?>">
    <?php require __DIR__ . "/partials/head.php"; ?>
</head>

<body class="page-panel">
<?php require_once __DIR__ . '/partials/loader.php'; ?>
<?php require_once __DIR__ . '/partials/navbar.php'; ?>

<div class="page">
    <div class="glass">
        <div class="header">
            <div>
                <h2>
                    <?php echo t('panel.welcome'); ?>,
                    <?php echo htmlspecialchars($displayName !== '' ? $displayName : t('panel.user.default'), ENT_QUOTES, 'UTF-8'); ?>
                </h2>
            </div>

            <div class="btn-row">
                <?php if ($isAdmin): ?>
                    <a class="btn" href="admin.php"><?php echo t('panel.admin'); ?></a>
                <?php endif; ?>

                <a class="btn primary" href="logout.php"><?php echo t('panel.logout'); ?></a>
            </div>
        </div>
    </div>

    <div class="cards">
        <div class="card" style="grid-column: 1 / -1;">
            <h3><?php echo t('panel.info.title'); ?></h3>

            <div class="meta-small">
                <?php echo t('panel.info.phone'); ?>:
                <?php echo htmlspecialchars($phone !== '' ? $phone : '—', ENT_QUOTES, 'UTF-8'); ?>
            </div>

            <div class="meta-small">
                <?php echo t('panel.info.email'); ?>:
                <?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>
            </div>

            <div class="meta-small">
                <?php echo t('panel.info.birth'); ?>:
                <?php echo $birthFormatted !== '' ? htmlspecialchars($birthFormatted, ENT_QUOTES, 'UTF-8') : '—'; ?>
            </div>

            <div class="meta-small">
                <?php echo t('panel.info.role'); ?>:
                <?php echo htmlspecialchars($role, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        </div>
    </div>

    <div class="panel-two-cols">
        <div class="glass">
            <h3><?php echo t('panel.profile.title'); ?></h3>

            <?php if ($profileMessage): ?>
                <div class="alert <?php echo $profileMessage === t('panel.profile.success') ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($profileMessage, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="action" value="profile_update">

                <div class="field">
                    <label><?php echo t('panel.profile.email'); ?></label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>

                <div class="field">
                    <label><?php echo t('panel.profile.phone'); ?></label>
                    <input type="tel" name="phone" maxlength="11" placeholder="05XXXXXXXXX"
                           value="<?php echo htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'); ?>"
                           oninput="maskPhone(this)" required>
                </div>

                <div class="field">
                    <label><?php echo t('panel.profile.birth'); ?></label>
                    <input type="text" name="birthdate" placeholder="gg.aa.yyyy" maxlength="10"
                           value="<?php echo htmlspecialchars($birthFormatted, ENT_QUOTES, 'UTF-8'); ?>"
                           oninput="maskDate(this)">
                </div>

                <button type="submit" class="submit"><?php echo t('panel.profile.submit'); ?></button>
            </form>
        </div>

        <div class="glass">
            <h3><?php echo t('panel.password.title'); ?></h3>

            <?php if ($passwordMessage): ?>
                <div class="alert <?php echo $passwordMessage === t('panel.password.success') ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($passwordMessage, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="action" value="password_change">

                <div class="field">
                    <label><?php echo t('panel.password.current'); ?></label>
                    <input type="password" name="current_password" placeholder="<?php echo t('panel.password.placeholders.current'); ?>" required>
                </div>

                <div class="field">
                    <label><?php echo t('panel.password.new'); ?></label>
                    <input type="password" name="new_password" placeholder="<?php echo t('panel.password.placeholders.new'); ?>" required>
                </div>

                <div class="field">
                    <label><?php echo t('panel.password.confirm'); ?></label>
                    <input type="password" name="new_password_confirm" placeholder="<?php echo t('panel.password.placeholders.confirm'); ?>" required>
                </div>

                <button type="submit" class="submit"><?php echo t('panel.password.submit'); ?></button>
            </form>
        </div>
    </div>

    <!-- Öneriler -->
    <div class="glass">
        <h3><?php echo t('panel.suggest.pending.title'); ?></h3>

        <?php if (empty($pendingSuggestions)): ?>
            <p class="meta-small"><?php echo t('panel.suggest.pending.empty'); ?></p>
        <?php else: ?>
            <div class="cards">
                <?php foreach ($pendingSuggestions as $ps): ?>
                    <div class="card">
                        <div class="meta-small"><?php echo htmlspecialchars($ps['created_at'], ENT_QUOTES, 'UTF-8'); ?></div>
                        <p class="lead" style="margin:6px 0 0;"><?php echo nl2br(htmlspecialchars($ps['content'], ENT_QUOTES, 'UTF-8')); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="glass" style="margin-top:16px;">
        <h3><?php echo t('panel.suggest.approved.title'); ?></h3>

        <?php if (empty($approvedSuggestions)): ?>
            <p class="meta-small"><?php echo t('panel.suggest.approved.empty'); ?></p>
        <?php else: ?>
            <div class="cards">
                <?php foreach ($approvedSuggestions as $as): ?>
                    <div class="card">
                        <div class="meta-small"><?php echo htmlspecialchars($as['created_at'], ENT_QUOTES, 'UTF-8'); ?></div>
                        <p class="lead" style="margin:6px 0 8px;"><?php echo nl2br(htmlspecialchars($as['content'], ENT_QUOTES, 'UTF-8')); ?></p>

                        <?php if (!empty($as['admin_reply'])): ?>
                            <div class="meta-small" style="margin-top:6px;">
                                <?php echo t('panel.suggest.reply'); ?>:
                                <?php echo nl2br(htmlspecialchars($as['admin_reply'], ENT_QUOTES, 'UTF-8')); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>

<script>
function maskDate(input) {
    let v = input.value.replace(/\D/g, '').slice(0, 8);
    if (v.length >= 3) v = v.slice(0, 2) + '.' + v.slice(2);
    if (v.length >= 6) v = v.slice(0, 5) + '.' + v.slice(5);
    input.value = v;
}
function maskPhone(input) {
    let v = input.value.replace(/\D/g, '').slice(0, 11);
    if (v.startsWith('0') === false && v !== '') v = '0' + v;
    if (!v.startsWith('05') && v !== '') v = '05' + v.replace(/^0+/, '');
    input.value = v;
}
</script>

</body>
</html>

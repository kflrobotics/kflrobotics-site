<?php
require_once __DIR__ . '/config/bootstrap.php';

require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/mail.php';
require_once __DIR__ . '/config/lang.php';


$currentUser = requireAdmin(); // DB’den rol kontrolü (sadece admin girer)
$pdo = Database::connection();
// Kullanıcı eklerken/düzenlerken seçim için geçerli roller:
$allowedRoles = ['user','admin','pd','leader','coding'];

// Basit fallback: mail.php’de sendTemplatedMail yoksa burada tanımla
if (!function_exists('sendTemplatedMail')) {
    function sendTemplatedMail(string $to, string $subject, string $title, string $message, ?string $ctaText = null, ?string $ctaUrl = null): bool
    {
        // sendMail yoksa sessizce düş
        if (!function_exists('sendMail')) return false;

        $html = '
        <div style="font-family:Arial,sans-serif; background:#0b1624; color:#e6edf5; padding:24px;">
          <div style="max-width:520px; margin:0 auto; border:1px solid rgba(255,255,255,0.08); border-radius:16px; background:linear-gradient(145deg, rgba(255,255,255,0.04), rgba(255,255,255,0.02)); box-shadow:0 16px 40px rgba(0,0,0,0.45); padding:24px;">
            <h2 style="margin:0 0 12px; color:#2dd4bf;">'.htmlspecialchars($title,ENT_QUOTES,'UTF-8').'</h2>
            <p style="margin:0 0 18px; line-height:1.6;">'.nl2br(htmlspecialchars($message,ENT_QUOTES,'UTF-8')).'</p>';
        if ($ctaText && $ctaUrl) {
            $html .= '
            <div style="margin-top:12px;">
              <a href="'.htmlspecialchars($ctaUrl,ENT_QUOTES,'UTF-8').'" style="display:inline-block; padding:12px 18px; background:linear-gradient(135deg,#2dd4bf,#1f7a6b); color:#051013; font-weight:700; border-radius:12px; text-decoration:none;">'.htmlspecialchars($ctaText,ENT_QUOTES,'UTF-8').'</a>
            </div>';
        }
        $html .= '
            <div style="margin-top:22px; font-size:12px; color:#9bb3c7;">KFL Robotics</div>
          </div>
        </div>';
        return sendMail($to, $subject, $html);
    }
}

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
function parseBirth(?string $raw): array
{
    $raw = trim((string)$raw);
    if ($raw === '') return [null, ''];
    $norm = str_replace('-', '.', $raw);
    $dt = DateTime::createFromFormat('d.m.Y', $norm);
    $err = DateTime::getLastErrors();
    if (!$dt || ($err['warning_count'] ?? 0) > 0 || ($err['error_count'] ?? 0) > 0 || $dt->format('d.m.Y') !== $norm) {
        return [null, 'Geçerli bir doğum tarihi giriniz (gg.aa.yyyy).'];
    }
    $dt->setTime(0, 0, 0);
    $min = new DateTime('1900-01-01');
    $today = new DateTime('today');
    if ($dt < $min) return [null, 'Doğum tarihi 1900 yılından küçük olamaz.'];
    if ($dt > $today) return [null, 'Doğum tarihi bugünden büyük olamaz.'];
    return [$dt->format('Y-m-d'), ''];
}

$adminMessage = '';
$stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$dbUser = $stmt->fetch(PDO::FETCH_ASSOC);
$displayName = $dbUser['name'] ?? 'Kullanıcı';
$mailWarn = '';
$editId = (int)($_GET['edit'] ?? 0);
$editUser = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $token = $_POST['csrf'] ?? '';

    if (!verify_csrf($token)) {
        $adminMessage = 'Geçersiz CSRF token.';
    } else {
        /* ---------- KULLANICI İŞLEMLERİ ---------- */
        if ($action === 'add') {
            $name = trim($_POST['name'] ?? '');
            $email = mb_strtolower(trim($_POST['email'] ?? ''));
            $password = $_POST['password'] ?? '';
            $role = in_array($_POST['role'] ?? 'user', $allowedRoles, true) ? $_POST['role'] : 'user';
            $phone = preg_replace('/\D+/', '', (string)($_POST['phone'] ?? ''));
            [$birth, $birthErr] = parseBirth($_POST['birthdate'] ?? '');

            if ($name === '' || $email === '' || $password === '') {
                $adminMessage = 'Tüm alanları doldurun.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $adminMessage = 'Geçerli bir e-posta giriniz.';
            } elseif (mb_strlen($password) < 8) {
                $adminMessage = 'Şifre en az 8 karakter olmalı.';
            } else {
                $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = :email');
                $stmt->execute(['email' => $email]);
                if ($stmt->fetchColumn() > 0) {
                    $adminMessage = 'Bu e-posta zaten kayıtlı.';
                } else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $insert = $pdo->prepare('INSERT INTO users (name, email, phone, birthdate, password, role, created_at) VALUES (:name, :email, :phone, :birth, :password, :role, NOW())');
                    $insert->execute([
                        'name' => $name,
                        'email' => $email,
                        'phone' => $phone,
                        'birth' => $birth,
                        'password' => $hash,
                        'role' => $role,
                    ]);
                    $adminMessage = 'Kullanıcı eklendi.';
                }
            }
        }

        if ($action === 'edit_user') {
            $id = (int)($_POST['user_id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $email = mb_strtolower(trim($_POST['email'] ?? ''));
            $role = in_array($_POST['role'] ?? 'user', $allowedRoles, true) ? $_POST['role'] : 'user';
            $newPassword = $_POST['new_password'] ?? '';
            $phone = preg_replace('/\D+/', '', (string)($_POST['phone'] ?? ''));
            [$birth, $birthErr] = parseBirth($_POST['birthdate'] ?? '');

            if ($id <= 0 || $name === '' || $email === '') {
                $adminMessage = 'İsim ve e-posta gereklidir.';
                $editUser = ['id' => $id, 'name' => $name, 'email' => $email, 'role' => $role, 'phone' => $phone, 'birthdate' => $_POST['birthdate'] ?? ''];
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $adminMessage = 'Geçerli bir e-posta giriniz.';
                $editUser = ['id' => $id, 'name' => $name, 'email' => $email, 'role' => $role, 'phone' => $phone, 'birthdate' => $_POST['birthdate'] ?? ''];
              } else {
                $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email AND id <> :id');
                $stmt->execute(['email' => $email, 'id' => $id]);
                if ($stmt->fetch()) {
                    $adminMessage = 'Bu e-posta başka bir kullanıcıda mevcut.';
                    $editUser = ['id' => $id, 'name' => $name, 'email' => $email, 'role' => $role, 'phone' => $phone, 'birthdate' => $_POST['birthdate'] ?? ''];
                } else {
                    $params = ['name' => $name, 'email' => $email, 'role' => $role, 'phone' => $phone, 'birth' => $birth, 'id' => $id];
                    $sql = 'UPDATE users SET name = :name, email = :email, role = :role, phone = :phone, birthdate = :birth';
                    if ($newPassword !== '') {
                        if (mb_strlen($newPassword) < 8) {
                            $adminMessage = 'Yeni şifreniz en az 8 karakter olmalı.';
                            $editUser = ['id' => $id, 'name' => $name, 'email' => $email, 'role' => $role, 'phone' => $phone, 'birthdate' => $_POST['birthdate'] ?? ''];
                            goto skip_update;
                        }
                        $sql .= ', password = :password';
                        $params['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
                    }
                    $sql .= ' WHERE id = :id';
                    $update = $pdo->prepare($sql);
                    $update->execute($params);
                    $adminMessage = 'Kullanıcı güncellendi.';
                    $editId = $id;
                }
            }
        }
        skip_update:;

        if ($action === 'reset') {
            $id = (int)($_POST['user_id'] ?? 0);
            $password = $_POST['password'] ?? '';

            if ($password === '') {
                $adminMessage = 'Yeni şifre giriniz.';
            } elseif (mb_strlen($password) < 8) {
                $adminMessage = 'Yeni şifre en az 8 karakter olmalı.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('UPDATE users SET password = :password WHERE id = :id');
                $stmt->execute(['password' => $hash, 'id' => $id]);
                $adminMessage = 'Şifre güncellendi.';
            }
        }

        if ($action === 'delete') {
            $id = (int)($_POST['user_id'] ?? 0);
            if ($id === (int)$_SESSION['user_id']) {
                $adminMessage = 'Kendi hesabınızı silemezsiniz.';
            } else {
                // Silinecek kullanıcının e-postasını al
                $stmt = $pdo->prepare('SELECT email FROM users WHERE id = :id LIMIT 1');
                $stmt->execute(['id' => $id]);
                $userRow = $stmt->fetch();
                $email = $userRow['email'] ?? null;

                $pdo->beginTransaction();
                // Kullanıcıyı sil
                $del = $pdo->prepare('DELETE FROM users WHERE id = :id');
                $del->execute(['id' => $id]);

                // Aynı e-postaya ait approved kayıt isteğini temizle (UNIQUE kilidini açmak için)
                if ($email) {
                    $pdo->prepare('DELETE FROM registration_requests WHERE email = :email AND status = "approved"')->execute(['email' => $email]);
                }
                $pdo->commit();

                $adminMessage = 'Kullanıcı silindi.';
            }
        }

        /* ---------- KAYIT İSTEKLERİ ONAY/RET ---------- */
        if ($action === 'approve_request' || $action === 'reject_request') {
            $reqId = (int)($_POST['req_id'] ?? 0);
            $adminNote = trim((string)($_POST['admin_note'] ?? ''));

            $pdo->beginTransaction();
            $stmt = $pdo->prepare('SELECT * FROM registration_requests WHERE id = :id AND status = "pending" FOR UPDATE');
            $stmt->execute(['id' => $reqId]);
            $req = $stmt->fetch();

            if (!$req) {
                $adminMessage = 'İstek bulunamadı veya zaten işlenmiş.';
                $pdo->rollBack();
            } else {
                if ($action === 'approve_request') {
                    $chk = $pdo->prepare('SELECT id FROM users WHERE email = :e LIMIT 1');
                    $chk->execute(['e' => $req['email']]);
                    if ($chk->fetch()) {
                        $adminMessage = 'Bu e-posta zaten kayıtlı, onaylanamadı.';
                        $pdo->rollBack();
                    } else {
                        $hash = $req['password_hash'];
                        $insUser = $pdo->prepare('INSERT INTO users (name, email, password, role, created_at) VALUES (:n, :e, :p, "user", NOW())');
                        $insUser->execute([
                            'n' => $req['full_name'],
                            'e' => $req['email'],
                            'p' => $hash,
                        ]);

                        $upd = $pdo->prepare('UPDATE registration_requests SET status="approved", admin_note=NULL, decided_at=NOW(), decided_by=:admin WHERE id=:id');
                        $upd->execute(['admin' => (int)$_SESSION['user_id'], 'id' => $reqId]);
                        $pdo->commit();

                        $mailOk = sendKflMail(
                            $req['email'],
                            'Hesabın Onaylandı',
                            'Hesabın Onaylandı',
                            $req['full_name'],
                            'Kayıt isteğin yetkililer tarafından onaylandı ve artık hesabınla giriş yapabilirsin.',
                            'GİRİŞ YAP',
                            (isset($_SERVER['HTTP_HOST'])
                                ? 'https://' . $_SERVER['HTTP_HOST'] . '/login.php'
                                : '/login.php'
                            )
                        );



                        $adminMessage = 'Kayıt isteği onaylandı ve kullanıcı eklendi.';
                        if (!$mailOk) {
                            $mailWarn = 'Mail gönderilemedi (SMTP ayarlarını kontrol edin).';
                        }
                    }
                }

                if ($action === 'reject_request') {
                    $upd = $pdo->prepare('UPDATE registration_requests SET status="rejected", admin_note=:note, decided_at=NOW(), decided_by=:admin WHERE id=:id');
                    $upd->execute([
                        'note' => ($adminNote !== '' ? $adminNote : null),
                        'admin' => (int)$_SESSION['user_id'],
                        'id' => $reqId,
                    ]);
                    $pdo->commit();
                    $text = 'Kayıt isteğin yetkililer tarafından reddedildi.';
                    if ($adminNote !== '') {
                        $text .= "\n\nNot: {$adminNote}";
                    }

                    $mailOk = sendKflMail(
                        $req['email'],
                        'Kayıt İsteğin Reddedildi',
                        'Kayıt İsteğin Reddedildi',
                        $req['full_name'],
                        $text
                    );

                    $adminMessage = 'Kayıt isteği reddedildi.';
                    if (!$mailOk) {
                        $mailWarn = 'Mail gönderilemedi (SMTP ayarlarını kontrol edin).';
                    }
                }
            }
        }

        /* ---------- ÖNERİLER İŞLEMLERİ ---------- */
        if ($action === 'approve_suggestion' || $action === 'reject_suggestion' || $action === 'reply_suggestion' || $action === 'delete_suggestion') {
            $sid = (int)($_POST['suggestion_id'] ?? 0);
            $note = trim((string)($_POST['admin_note'] ?? ''));
            $reply = trim((string)($_POST['admin_reply'] ?? ''));

            $stmt = $pdo->prepare('SELECT * FROM suggestions WHERE id = :id AND deleted_at IS NULL LIMIT 1');
            $stmt->execute(['id' => $sid]);
            $sg = $stmt->fetch();

            $contentText = trim((string)($sg['content'] ?? ''));
            if (!$sg) {
                $adminMessage = 'Öneri bulunamadı.';
            } else {
                // Kullanıcı e-postasını çek
                $userEmail = null;
                $userName  = null;
                if (!empty($sg['user_id'])) {
                    $usr = $pdo->prepare('SELECT email, name FROM users WHERE id = ? LIMIT 1');
                    $usr->execute([$sg['user_id']]);
                    $found = $usr->fetch();
                    if ($found) { $userEmail = $found['email']; $userName = $found['name']; }
                }

                if ($action === 'approve_suggestion') {
                    $upd = $pdo->prepare('UPDATE suggestions SET status="approved", approved_at=NOW(), admin_note=NULL, rejected_at=NULL WHERE id=:id');
                    $upd->execute(['id' => $sid]);
                    $adminMessage = 'Öneri onaylandı.';

                    if ($userEmail) {
                        sendKflMail(
                            $userEmail,
                            'Önerin Onaylandı',
                            'Önerin Onaylandı',
                            $userName,
                            "Gönderdiğin öneri yetkililer tarafından onaylandı ve yayına alındı.\n\n“{$contentText}”",
                            'ÖNERİLERİ GÖR',
                            (isset($_SERVER['HTTP_HOST'])
                                ? 'https://' . $_SERVER['HTTP_HOST'] . '/suggests.php'
                                : '/suggests.php'
                            )
                        );


                    }
                }


                if ($action === 'reject_suggestion') {
                    $upd = $pdo->prepare('UPDATE suggestions SET status="rejected", rejected_at=NOW(), admin_note=:note WHERE id=:id');
                    $upd->execute(['id' => $sid, 'note' => ($note !== '' ? $note : null)]);
                    $adminMessage = 'Öneri reddedildi.';

                    if ($userEmail) {
                        $text = "Gönderdiğin öneri yetkililer tarafından reddedildi.\n\n\"{$contentText}\"";
                        if ($note !== '') {
                            $text .= "\n\nNot: {$note}";
                        }

                        sendKflMail(
                            $userEmail,
                            'Önerin Reddedildi',
                            'Önerin Reddedildi',
                            $userName,
                            $text
                        );


                    }
                }


                if ($action === 'reply_suggestion') {
                    $upd = $pdo->prepare('UPDATE suggestions SET admin_reply=:r, replied_at=NOW() WHERE id=:id');
                    $upd->execute(['id' => $sid, 'r' => ($reply !== '' ? $reply : null)]);
                    $adminMessage = 'Yanıt kaydedildi.';

                    if ($userEmail && $reply !== '') {
                        sendKflMail(
                            $userEmail,
                            'Önerin Yanıtlandı',
                            'Önerin Yanıtlandı',
                            $userName,
                            "Önerin yetkililer tarafından yanıtlandı.\n\n\"{$reply}\"",
                            'ÖNERİYE GİT',
                            (isset($_SERVER['HTTP_HOST'])
                                ? 'https://' . $_SERVER['HTTP_HOST'] . '/suggests.php'
                                : '/suggests.php'
                            )
                        );


                    }
                }

                if ($action === 'delete_suggestion') {
                    $upd = $pdo->prepare('UPDATE suggestions SET deleted_at=NOW() WHERE id=:id');
                    $upd->execute(['id' => $sid]);
                    $adminMessage = 'Öneri silindi.';
                }
            }
        }
    }
}

/* ---------- VERİLER ---------- */
$users = $pdo->query('SELECT id, name, email, role, phone, birthdate, created_at FROM users ORDER BY id ASC')->fetchAll();

if ($editUser === null && $editId > 0) {
    $stmt = $pdo->prepare('SELECT id, name, email, role, phone, birthdate FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $editId]);
    $editUser = $stmt->fetch();
    if (!$editUser) {
        $editUser = null;
        $editId = 0;
    }
}

$pendingRegs = $pdo->query('SELECT * FROM registration_requests WHERE status = "pending" ORDER BY created_at DESC')->fetchAll();
$historyRegs = $pdo->query('SELECT * FROM registration_requests WHERE status IN ("approved","rejected") ORDER BY created_at DESC')->fetchAll();

$pendingSuggestions = $pdo->query('SELECT s.*, u.name AS uname, u.email AS uemail FROM suggestions s LEFT JOIN users u ON s.user_id = u.id WHERE s.status="pending" AND s.deleted_at IS NULL ORDER BY s.created_at DESC')->fetchAll();
$approvedSuggestions = $pdo->query('SELECT s.*, u.name AS uname, u.email AS uemail FROM suggestions s LEFT JOIN users u ON s.user_id = u.id WHERE s.status="approved" AND s.deleted_at IS NULL ORDER BY s.created_at DESC')->fetchAll();

$csrf = csrf_token();
$pendingRegsCount = count($pendingRegs);
$pendingSugCount  = count($pendingSuggestions);

function fmtBirth(?string $date): string {
    if (!$date) return '—';
    $dt = DateTime::createFromFormat('Y-m-d', $date);
    return $dt ? $dt->format('d.m.Y') : '—';
}
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KFL Robotics | Yönetim Paneli</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600&family=General+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'].'/assets/css/style.css') ?>">
    <?php require __DIR__ . "/partials/head.php"; ?>
</head>
<body class="page-admin">
    <?php require_once __DIR__ . '/partials/loader.php'; ?>
    <?php require_once __DIR__ . '/partials/navbar.php'; ?>

    <div class="page">
        <div class="glass">
            <h2>Admin Paneli</h2>
            <?php if ($adminMessage): ?>
                <p class="lead"><?php echo htmlspecialchars($adminMessage, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php else: ?>
                <p class="lead">Hoş geldin, <?php echo htmlspecialchars($displayName !== '' ? $displayName : 'Kullanıcı', ENT_QUOTES, 'UTF-8'); ?>. Yalnızca admin rolü bu sayfaya erişebilir.</p>
            <?php endif; ?>
            <?php if ($mailWarn): ?>
                <p class="lead" style="color:#fca5a5;"><?php echo htmlspecialchars($mailWarn, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>
            <div class="btn-row">
                <a class="btn" href="panel.php">Kullanıcı Paneli</a>
                <a class="btn primary" href="logout.php">Çıkış Yap</a>
            </div>
        </div>

        <!-- ACCORDION: Kayıt İstekleri -->
        <div class="admin-accordion">
            <button type="button" class="admin-acc-header" data-target="reg-requests">
                <span class="title">Kayıt İstekleri</span>
                <span class="meta">
                    <span class="badge"><?php echo $pendingRegsCount; ?></span>
                    <span class="chevron"></span>
                </span>
            </button>
            <div class="admin-acc-body" id="reg-requests">
                <div class="glass">
                    <h3>Kayıt İstekleri (Bekleyen)</h3>
                    <?php if (!$pendingRegs): ?>
                        <p class="meta-small">Bekleyen kayıt isteği yok.</p>
                    <?php else: ?>
                        <table style="width:100%; border-collapse:collapse; font-size:14px;">
                            <thead>
                                <tr>
                                    <th style="text-align:left; padding:8px;">Ad Soyad</th>
                                    <th style="text-align:left; padding:8px;">E-posta</th>
                                    <th style="text-align:left; padding:8px;">Tarih</th>
                                    <th style="padding:8px;">İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendingRegs as $req): ?>
                                    <tr>
                                        <td style="padding:8px;"><?php echo htmlspecialchars($req['full_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td style="padding:8px;"><?php echo htmlspecialchars($req['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td style="padding:8px;"><?php echo htmlspecialchars($req['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td style="padding:8px; display:flex; gap:6px; flex-wrap:wrap;">
                                            <form method="post" style="display:inline-flex; gap:6px;">
                                                <input type="hidden" name="csrf" value="<?php echo $csrf; ?>">
                                                <input type="hidden" name="action" value="approve_request">
                                                <input type="hidden" name="req_id" value="<?php echo (int)$req['id']; ?>">
                                                <button type="submit" class="btn primary">Onayla</button>
                                            </form>
                                            <form method="post" style="display:inline-flex; gap:6px;">
                                                <input type="hidden" name="csrf" value="<?php echo $csrf; ?>">
                                                <input type="hidden" name="action" value="reject_request">
                                                <input type="hidden" name="req_id" value="<?php echo (int)$req['id']; ?>">
                                                <input type="text" name="admin_note" placeholder="Not (opsiyonel)" style="background:rgba(255,255,255,0.05); color:#e6edf5; border:none; padding:6px 8px; border-radius:8px;">
                                                <button type="submit" class="btn" style="border:1px solid rgba(239,68,68,0.6); color:#f87171;">Reddet</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <div class="glass">
                    <h3>Kayıt İstekleri (Geçmiş)</h3>
                    <?php if (!$historyRegs): ?>
                        <p class="meta-small">Henüz işlenmiş istek yok.</p>
                    <?php else: ?>
                        <table style="width:100%; border-collapse:collapse; font-size:14px;">
                            <thead>
                                <tr>
                                    <th style="text-align:left; padding:8px;">Ad Soyad</th>
                                    <th style="text-align:left; padding:8px;">E-posta</th>
                                    <th style="text-align:left; padding:8px;">Durum</th>
                                    <th style="text-align:left; padding:8px;">Tarih</th>
                                    <th style="text-align:left; padding:8px;">Not</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($historyRegs as $req): ?>
                                    <tr>
                                        <td style="padding:8px;"><?php echo htmlspecialchars($req['full_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td style="padding:8px;"><?php echo htmlspecialchars($req['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td style="padding:8px;"><?php echo htmlspecialchars($req['status'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td style="padding:8px;"><?php echo htmlspecialchars($req['decided_at'] ?? $req['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td style="padding:8px;"><?php echo htmlspecialchars($req['admin_note'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ACCORDION: Onay Bekleyen Öneriler -->
        <div class="admin-accordion">
            <button type="button" class="admin-acc-header" data-target="pending-suggestions">
                <span class="title">Onay Bekleyen Öneriler</span>
                <span class="meta">
                    <span class="badge"><?php echo $pendingSugCount; ?></span>
                    <span class="chevron"></span>
                </span>
            </button>
            <div class="admin-acc-body" id="pending-suggestions">
                <div class="glass">
                    <h3>Onay Bekleyen Öneriler</h3>
                    <?php if (!$pendingSuggestions): ?>
                        <p class="meta-small">Bekleyen öneri yok.</p>
                    <?php else: ?>
                        <table style="width:100%; border-collapse:collapse; font-size:14px;">
                            <thead>
                                <tr>
                                    <th style="text-align:left; padding:8px;">Kullanıcı</th>
                                    <th style="text-align:left; padding:8px;">İçerik</th>
                                    <th style="text-align:left; padding:8px;">Tarih</th>
                                    <th style="padding:8px;">İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendingSuggestions as $sg): ?>
                                    <tr>
                                        <td style="padding:8px;"><?php echo htmlspecialchars($sg['uname'] ?? ($sg['uemail'] ?? 'Anonim'), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td style="padding:8px;"><?php echo htmlspecialchars(mb_strimwidth($sg['content'], 0, 120, '...'), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td style="padding:8px;"><?php echo htmlspecialchars($sg['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td style="padding:8px; display:flex; gap:6px; flex-wrap:wrap;">
                                            <form method="post" style="display:inline-flex; gap:6px;">
                                                <input type="hidden" name="csrf" value="<?php echo $csrf; ?>">
                                                <input type="hidden" name="action" value="approve_suggestion">
                                                <input type="hidden" name="suggestion_id" value="<?php echo (int)$sg['id']; ?>">
                                                <button type="submit" class="btn primary">Onayla</button>
                                            </form>
                                            <form method="post" style="display:inline-flex; gap:6px;">
                                                <input type="hidden" name="csrf" value="<?php echo $csrf; ?>">
                                                <input type="hidden" name="action" value="reject_suggestion">
                                                <input type="hidden" name="suggestion_id" value="<?php echo (int)$sg['id']; ?>">
                                                <input type="text" name="admin_note" placeholder="Not (opsiyonel)" style="background:rgba(255,255,255,0.05); color:#e6edf5; border:none; padding:6px 8px; border-radius:8px;">
                                                <button type="submit" class="btn" style="border:1px solid rgba(239,68,68,0.6); color:#f87171;">Reddet</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <div class="glass">
                    <h3>Öneriler (Onaylanmış)</h3>
                    <?php if (!$approvedSuggestions): ?>
                        <p class="meta-small">Henüz onaylanmış öneri yok.</p>
                    <?php else: ?>
                        <table style="width:100%; border-collapse:collapse; font-size:14px;">
                            <thead>
                                <tr>
                                    <th style="text-align:left; padding:8px;">Kullanıcı</th>
                                    <th style="text-align:left; padding:8px;">İçerik</th>
                                    <th style="text-align:left; padding:8px;">Yanıt</th>
                                    <th style="text-align:left; padding:8px;">Tarih</th>
                                    <th style="padding:8px;">İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($approvedSuggestions as $sg): ?>
                                    <tr>
                                        <td style="padding:8px;"><?php echo htmlspecialchars($sg['uname'] ?? ($sg['uemail'] ?? 'Anonim'), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td style="padding:8px;"><?php echo nl2br(htmlspecialchars($sg['content'], ENT_QUOTES, 'UTF-8')); ?></td>
                                        <td style="padding:8px;"><?php echo $sg['admin_reply'] ? nl2br(htmlspecialchars($sg['admin_reply'], ENT_QUOTES, 'UTF-8')) : '—'; ?></td>
                                        <td style="padding:8px;"><?php echo htmlspecialchars($sg['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td style="padding:8px; display:flex; gap:6px; flex-wrap:wrap;">
                                            <form method="post" style="display:inline-flex; gap:6px; flex-direction:column; width:220px;">
                                                <input type="hidden" name="csrf" value="<?php echo $csrf; ?>">
                                                <input type="hidden" name="action" value="reply_suggestion">
                                                <input type="hidden" name="suggestion_id" value="<?php echo (int)$sg['id']; ?>">
                                                <textarea name="admin_reply" rows="2" placeholder="Yanıt" style="background:rgba(255,255,255,0.05); color:#e6edf5; border:none; padding:6px 8px; border-radius:8px;"><?php echo htmlspecialchars($sg['admin_reply'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                                <button type="submit" class="btn primary">Yanıtı Kaydet</button>
                                            </form>
                                            <form method="post" style="display:inline-flex; gap:6px;">
                                                <input type="hidden" name="csrf" value="<?php echo $csrf; ?>">
                                                <input type="hidden" name="action" value="delete_suggestion">
                                                <input type="hidden" name="suggestion_id" value="<?php echo (int)$sg['id']; ?>">
                                                <button type="submit" class="btn" style="background:transparent; border:1px solid rgba(239,68,68,0.6); color:#f87171;">Sil</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ACCORDION: Kullanıcı Listesi -->
        <div class="admin-accordion">
            <button type="button" class="admin-acc-header" data-target="user-list">
                <span class="title">Kullanıcı Listesi</span>
                <span class="meta">
                    <span class="badge"><?php echo count($users); ?></span>
                    <span class="chevron"></span>
                </span>
            </button>
            <div class="admin-acc-body" id="user-list">
                <div class="glass">
                    <h3>Kullanıcı Listesi</h3>
                    <table style="width:100%; border-collapse:collapse; font-size:14px;">
                        <thead>
                            <tr>
                                <th style="text-align:left; padding:8px;">ID</th>
                                <th style="text-align:left; padding:8px;">İsim</th>
                                <th style="text-align:left; padding:8px;">E-posta</th>
                                <th style="text-align:left; padding:8px;">Telefon</th>
                                <th style="text-align:left; padding:8px;">Doğum Tarihi</th>
                                <th style="text-align:left; padding:8px;">Rol</th>
                                <th style="text-align:left; padding:8px;">Oluşturma</th>
                                <th style="padding:8px;">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td style="padding:8px;"><?php echo htmlspecialchars($user['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td style="padding:8px;"><?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td style="padding:8px;"><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td style="padding:8px;"><?php echo htmlspecialchars($user['phone'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td style="padding:8px;"><?php echo htmlspecialchars(fmtBirth($user['birthdate']), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td style="padding:8px;"><?php echo htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td style="padding:8px;"><?php echo htmlspecialchars($user['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td style="padding:8px; display:flex; gap:6px; flex-wrap:wrap;">
                                        <a href="?edit=<?php echo (int)$user['id']; ?>" class="btn" style="text-decoration:none;">Düzenle</a>
                                        <form method="post" style="display:inline-flex; gap:6px;">
                                            <input type="hidden" name="csrf" value="<?php echo $csrf; ?>">
                                            <input type="hidden" name="user_id" value="<?php echo (int)$user['id']; ?>">
                                            <input type="hidden" name="action" value="reset">
                                            <input type="password" name="password" placeholder="Yeni şifre" required style="background:rgba(255,255,255,0.05); color:#e6edf5; border:none; padding:6px 8px; border-radius:8px;">
                                        </form>
                                        <?php if ((int)$user['id'] !== (int)$_SESSION['user_id']): ?>
                                            <form method="post" style="display:inline-flex; gap:6px;">
                                                <input type="hidden" name="csrf" value="<?php echo $csrf; ?>">
                                                <input type="hidden" name="user_id" value="<?php echo (int)$user['id']; ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <button type="submit" class="btn" style="background:transparent; border:1px solid rgba(239,68,68,0.6); color:#f87171;">Sil</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php if ($editUser): ?>
            <div class="glass">
                <h3>Kullanıcı Düzenle (ID: <?php echo htmlspecialchars($editUser['id'], ENT_QUOTES, 'UTF-8'); ?>)</h3>
                <form method="post">
                    <input type="hidden" name="csrf" value="<?php echo $csrf; ?>">
                    <input type="hidden" name="action" value="edit_user">
                    <input type="hidden" name="user_id" value="<?php echo (int)$editUser['id']; ?>">
                    <div class="field">
                        <label>İsim</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($editUser['name'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="field">
                        <label>E-posta</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($editUser['email'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="field">
                        <label>Telefon (05XXXXXXXXX)</label>
                        <input type="tel" name="phone" maxlength="11" value="<?php echo htmlspecialchars($editUser['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="field">
                        <label>Doğum Tarihi</label>
                        <input type="text" name="birthdate" placeholder="gg.aa.yyyy" value="<?php echo htmlspecialchars(fmtBirth($editUser['birthdate'] ?? null), ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="field">
                        <label>Rol</label>
                        <select name="role">
                            <?php foreach ($allowedRoles as $r): ?>
                                <option value="<?php echo $r; ?>" <?php echo ($editUser['role'] === $r ? 'selected' : ''); ?>><?php echo $r; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label>Yeni Şifre (opsiyonel, min 8)</label>
                        <input type="password" name="new_password" placeholder="Yeni şifre (en az 8 karakter)">
                    </div>
                    <div class="btn-row">
                        <button type="submit" class="btn primary">Kaydet</button>
                        <a class="btn" href="admin.php">İptal</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <div class="glass">
            <h3>Yeni Kullanıcı Ekle</h3>
            <form method="post">
                <input type="hidden" name="csrf" value="<?php echo $csrf; ?>">
                <input type="hidden" name="action" value="add">
                <div class="field">
                    <label>İsim</label>
                    <input type="text" name="name" placeholder="İsim" required>
                </div>
                <div class="field">
                    <label>E-posta</label>
                    <input type="email" name="email" placeholder="email@site.com" required>
                </div>
                <div class="field">
                    <label>Telefon (05XXXXXXXXX)</label>
                    <input type="tel" name="phone" maxlength="11" placeholder="05XXXXXXXXX">
                </div>
                <div class="field">
                    <label>Doğum Tarihi</label>
                    <input type="text" name="birthdate" placeholder="gg.aa.yyyy">
                </div>
                <div class="field">
                    <label>Şifre</label>
                    <input type="password" name="password" placeholder="Şifre" required>
                </div>
                <div class="field">
                    <label>Rol</label>
                    <select name="role">
                        <?php foreach ($allowedRoles as $r): ?>
                            <option value="<?php echo $r; ?>"><?php echo $r; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="submit">Kaydet</button>
            </form>
        </div>
    </div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const headers = document.querySelectorAll('.admin-acc-header');
    headers.forEach(header => {
        header.addEventListener('click', () => {
            const targetId = header.getAttribute('data-target');
            const body = document.getElementById(targetId);
            if (!body) return;
            header.classList.toggle('open');
            body.classList.toggle('open');
        });
    });
});
</script>
</body>
</html>

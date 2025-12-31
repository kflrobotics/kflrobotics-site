<?php
require_once __DIR__ . '/config/bootstrap.php';
$SEO_TITLE = t('suggests.seo.title');
$SEO_DESC  = t('suggests.seo.desc');
$SEO_PATH  = "/suggests";

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/mail.php';
require_once __DIR__ . '/config/captcha.php';
$pdo = Database::connection();

$isLoggedIn = isset($_SESSION['user_id']);

$message = '';

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(string $token): bool {
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'suggest') {

    $captchaToken = $_POST['cf-turnstile-response'] ?? null;
    [$captchaOk, $captchaMsg] = turnstile_verify($captchaToken, $_SERVER['REMOTE_ADDR'] ?? null);

    if (!$captchaOk) {
        $message = t('suggests.form.errors.turnstile');
    } else {

        $token = $_POST['csrf'] ?? '';
        $content = trim((string)($_POST['content'] ?? ''));

        if (!verify_csrf($token)) {
            $message = t('suggests.form.errors.csrf');
        } elseif ($content === '' || mb_strlen($content) < 10) {
            $message = t('suggests.form.errors.min');
        } elseif (mb_strlen($content) > 1000) {
            $message = t('suggests.form.errors.max');
        } else {

            $stmt = $pdo->prepare(
                'SELECT COUNT(*) FROM suggestions
                 WHERE user_id = :uid
                   AND status = "pending"
                   AND deleted_at IS NULL'
            );
            $stmt->execute(['uid' => $_SESSION['user_id']]);
            $pendingCount = (int) $stmt->fetchColumn();

            if ($pendingCount >= 5) {
                $message = t('suggests.form.errors.too_many_pending');
            } else {

                $stmt = $pdo->prepare(
                    'INSERT INTO suggestions (user_id, content, status, created_at)
                     VALUES (:uid, :content, "pending", NOW())'
                );
                $stmt->execute([
                    'uid' => $_SESSION['user_id'],
                    'content' => $content
                ]);

                $message = t('suggests.form.success.pending');

                $admins = $pdo->prepare('SELECT name, email FROM users WHERE role = "admin"');
                $admins->execute();
                $adminRows = $admins->fetchAll(PDO::FETCH_ASSOC);

                $adminLink = (isset($_SERVER['HTTP_HOST'])
                    ? 'https://' . $_SERVER['HTTP_HOST'] . '/admin.php'
                    : '/admin.php'
                );

                foreach ($adminRows as $a) {
                    if (empty($a['email'])) continue;

                    sendKflMail(
                        $a['email'],
                        t('suggests.mail.new_suggest.subject'),
                        t('suggests.mail.new_suggest.title'),
                        $a['name'] ?? t('suggests.mail.new_suggest.default_name'),
                        t('suggests.mail.new_suggest.body_prefix') . "\n\n“{$content}”",
                        t('suggests.mail.new_suggest.cta'),
                        $adminLink
                    );
                }
            }
        }
    }
}

$approved = $pdo->query("
    SELECT s.id, s.content, s.created_at, s.admin_reply, s.replied_at,
           COALESCE(u.name, u.email) AS author_name
    FROM suggestions s
    LEFT JOIN users u ON u.id = s.user_id
    WHERE s.status = 'approved' AND s.deleted_at IS NULL
    ORDER BY s.created_at DESC
")->fetchAll();

$csrf = csrf_token();
?>

<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php require __DIR__ . "/partials/head.php"; ?>
    <title><?php echo t('suggests.head.title'); ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600&family=General+Sans:wght@400;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="/assets/css/style.css?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'].'/assets/css/style.css') ?>">
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
</head>

<body class="page-panel">

<?php require_once __DIR__ . '/partials/loader.php'; ?>
<?php require_once __DIR__ . '/partials/navbar.php'; ?>

<div class="page">

    <div class="glass">
        <h2><?php echo t('suggests.hero.title'); ?></h2>
        <p class="lead"><?php echo t('suggests.hero.lead'); ?></p>
    </div>

    <?php if ($isLoggedIn): ?>
        <div class="glass">
            <h3><?php echo t('suggests.form.title'); ?></h3>

            <?php if ($message): ?>
                <div class="alert <?php echo $message === t('suggests.form.success.pending') ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="action" value="suggest">

                <div class="field">
                    <label><?php echo t('suggests.form.labels.content'); ?></label>
                    <textarea name="content" rows="4" maxlength="1000" style="padding:12px; border-radius:12px; border:1px solid var(--border); background:rgba(255,255,255,0.03); color:var(--text);" required></textarea>
                </div>

                <div class="captcha-wrap">
                    <div class="cf-turnstile" data-sitekey="<?= htmlspecialchars(turnstile_site_key(), ENT_QUOTES, 'UTF-8') ?>"></div>
                </div>

                <button type="submit" class="submit"><?php echo t('suggests.form.submit'); ?></button>
            </form>
        </div>

    <?php else: ?>

        <div class="glass hero-form" style="max-width:540px; margin:0 auto;">
            <h3><?php echo t('suggests.guest.title'); ?></h3>
            <p class="lead"><?php echo t('suggests.guest.lead'); ?></p>
            <a class="cta-btn" href="login.php"><?php echo t('suggests.guest.cta'); ?></a>
        </div>

    <?php endif; ?>

    <div class="glass">
        <h3><?php echo t('suggests.approved.title'); ?></h3>

        <?php if (!$approved): ?>
            <p class="lead"><?php echo t('suggests.approved.empty'); ?></p>
        <?php else: ?>
            <div class="cards suggests-grid">
                <?php foreach ($approved as $item): ?>
                    <div class="card">
                        <div class="meta-small">
                            <?php echo htmlspecialchars($item['author_name'] ?? t('suggests.approved.unknown'), ENT_QUOTES, 'UTF-8'); ?>
                            · <?php echo htmlspecialchars($item['created_at'], ENT_QUOTES, 'UTF-8'); ?>
                        </div>

                        <p class="lead" style="margin:8px 0 0;"><?php echo nl2br(htmlspecialchars($item['content'], ENT_QUOTES, 'UTF-8')); ?></p>

                        <?php if (!empty($item['admin_reply'])): ?>
                            <div class="glass" style="margin-top:10px; padding:12px;">
                                <strong><?php echo t('suggests.approved.reply_label'); ?></strong><br>
                                <span class="lead mini"><?php echo nl2br(htmlspecialchars($item['admin_reply'], ENT_QUOTES, 'UTF-8')); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
</body>
</html>

<?php
require_once __DIR__ . '/config/bootstrap.php';
if (isset($_SESSION['user_id'])) {
    header('Location: panel.php');
    exit;
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/captcha.php';

$message = ['type' => '', 'text' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CAPTCHA KONTROLÜ (Turnstile - ENV)
    $captchaToken = $_POST['cf-turnstile-response'] ?? null;
    [$captchaOk, $captchaMsg] = turnstile_verify($captchaToken, $_SERVER['REMOTE_ADDR'] ?? null);

    if (!$captchaOk) {
        $message = ['type' => 'error', 'text' => t('login.form.errors.turnstile')];
    } else {

        $email = mb_strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $message = ['type' => 'error', 'text' => t('login.form.errors.required')];
        } else {
            $pdo = Database::connection();
            $stmt = $pdo->prepare('SELECT id, name, password, role FROM users WHERE email = :email LIMIT 1');
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['user'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                header('Location: panel.php');
                exit;
            }

            $message = ['type' => 'error', 'text' => t('login.form.errors.invalid_login')];
        }
    }
}

?>

<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo t('login.head.title'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600&family=General+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'].'/assets/css/style.css') ?>">
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <?php require __DIR__ . "/partials/head.php"; ?>
</head>

<body class="page-login">
    <?php require_once __DIR__ . '/partials/loader.php'; ?>
    <?php require_once __DIR__ . '/partials/navbar.php'; ?>

    <div class="page">
        <div class="glass login-wide">
            <h1><?php echo t('login.hero.title'); ?></h1>
            <p class="lead"><?php echo t('login.hero.lead'); ?></p>

            <?php if ($message['text']): ?>
                <div class="alert <?php echo htmlspecialchars($message['type'], ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars($message['text'], ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="/login" novalidate>
                <div class="field">
                    <label><?php echo t('login.form.labels.email'); ?></label>
                    <input type="email" name="email" placeholder="<?php echo t('login.form.placeholders.email'); ?>" required>
                </div>

                <div class="field">
                    <label><?php echo t('login.form.labels.password'); ?></label>
                    <input type="password" name="password" placeholder="<?php echo t('login.form.placeholders.password'); ?>" required>
                </div>

                <div class="captcha-wrap">
                    <div class="cf-turnstile" data-sitekey="<?= htmlspecialchars(turnstile_site_key(), ENT_QUOTES, 'UTF-8') ?>"></div>
                </div>

                <button type="submit" class="submit"><?php echo t('login.form.submit'); ?></button>
            </form>

            <div style="margin-top:12px; text-align:center;">
                <a href="forgot_password.php" style="color:#14b8a6; text-decoration:none; font-weight:600;">
                    <?php echo t('login.links.forgot_password'); ?>
                </a>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/partials/footer.php'; ?>
</body>

</html>

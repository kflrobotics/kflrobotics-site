<?php
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/config/captcha.php';

if (!empty($_SESSION['user_id'])) {
  header('Location: panel.php');
  exit;
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/mail.php';

$pdo = Database::connection();

function csrf_token(): string {
  if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
  }
  return $_SESSION['csrf_token'];
}

function verify_csrf(string $token): bool {
  return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

$message = '';
$isError = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $token = (string)($_POST['csrf'] ?? '');
  $email = mb_strtolower(trim((string)($_POST['email'] ?? '')));
  $ts    = $_POST['cf-turnstile-response'] ?? null;

  [$captchaOk, $captchaMsg] = turnstile_verify($ts, $_SERVER['REMOTE_ADDR'] ?? null);
  if (!$captchaOk) {
    $message = t('fp.errors.turnstile', 'Lütfen doğrulamayı tamamlayın.');
    $isError = true;

  } elseif (!verify_csrf($token)) {
    $message = t('fp.unavailable.request', 'Geçersiz istek.');
    $isError = true;

  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $message = t('fp.unavailable.mail', 'Lütfen geçerli bir e-posta girin.');
    $isError = true;

  } else {
    $st = $pdo->prepare('SELECT id, name, email FROM users WHERE email = :e LIMIT 1');
    $st->execute(['e' => $email]);
    $user = $st->fetch(PDO::FETCH_ASSOC);

    $message = t(
      'fp.sent.link',
      'Eğer bu e-posta sistemimizde varsa, şifre sıfırlama bağlantısı gönderildi.'
    );
    $isError = false;

    if ($user) {
      $rawToken  = bin2hex(random_bytes(32));
      $tokenHash = hash('sha256', $rawToken);

      $pdo->prepare(
        'UPDATE password_resets
         SET used_at = NOW()
         WHERE user_id = :uid AND used_at IS NULL'
      )->execute(['uid' => $user['id']]);

      // 30 dk geçerli
      $pdo->prepare(
        'INSERT INTO password_resets (user_id, token_hash, expires_at)
         VALUES (:uid, :th, DATE_ADD(NOW(), INTERVAL 30 MINUTE))'
      )->execute([
        'uid' => $user['id'],
        'th'  => $tokenHash
      ]);

      $host = $_SERVER['HTTP_HOST'] ?? '';
      $resetLink = $host
        ? 'https://' . $host . '/reset_password.php?token=' . $rawToken
        : '/reset_password.php?token=' . $rawToken;

      sendKflMail(
        $user['email'],
        'Şifre Sıfırlama',
        'Şifre Sıfırlama',
        $user['name'] ?: 'Kullanıcı',
        "Şifre sıfırlama isteği aldık.\n\nDevam etmek için aşağıdaki butona tıkla. Bu bağlantı 30 dakika geçerlidir.",
        'ŞİFREMİ SIFIRLA',
        $resetLink
      );
    }
  }
}

$csrf = csrf_token();
?>
<!doctype html>
<html lang="<?= e($lang ?? 'tr') ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e(t('fp.seo.title', 'KFL Robotics | Şifre Sıfırla')) ?></title>

  <?php require __DIR__ . "/partials/head.php"; ?>

  <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>

  <style>
    .btn-row{ margin-top:12px; display:flex; justify-content:center; }
    .btn-full{ width:100%; display:block; text-align:center; }
    .btn-ghost{ display:inline-block; text-align:center; opacity:.95; }
  </style>
</head>

<body class="page-login">
  <?php require_once __DIR__ . '/partials/loader.php'; ?>
  <?php require_once __DIR__ . '/partials/navbar.php'; ?>

  <main class="page">
    <div class="glass hero-form" style="max-width:540px;margin:0 auto;">
      <h3><?= e(t('fp.header.forgotmypass', 'Şifremi Unuttum')) ?></h3>

      <?php if (!empty($message)): ?>
        <div class="alert <?= $isError ? 'error' : 'success'; ?>">
          <?= e($message) ?>
        </div>
      <?php endif; ?>

      <form method="post">
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">

        <div class="field">
          <label><?= e(t('fp.mail', 'E-posta')) ?></label>
          <input type="email" name="email" required>
        </div>

        <div class="captcha-wrap">
          <div class="cf-turnstile" data-sitekey="<?= e(turnstile_site_key()) ?>"></div>
        </div>

        <button type="submit" class="cta-btn btn-full"><?= e(t('fp.send.resetlink', 'Sıfırlama Linki Gönder')) ?></button>
      </form>

      <div class="btn-row">
        <a href="login.php" class="cta-btn btn-ghost"><?= e(t('fp.goback', 'Girişe Dön')) ?></a>
      </div>
    </div>
  </main>

  <?php require_once __DIR__ . '/partials/footer.php'; ?>
</body>
</html>

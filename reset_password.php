<?php
require_once __DIR__ . '/config/bootstrap.php';
if (isset($_SESSION['user_id'])) {
    header('Location: panel.php');
    exit;
}

require_once __DIR__ . '/config/database.php';
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

$token = (string)($_GET['token'] ?? '');
$token = trim($token);

$message = '';
$isError = false;
$valid = false;
$resetRow = null;

if ($token !== '' && ctype_xdigit($token) && strlen($token) >= 32) {
    $tokenHash = hash('sha256', $token);

    $st = $pdo->prepare("
        SELECT pr.id AS reset_id, pr.user_id, pr.expires_at, pr.used_at
        FROM password_resets pr
        WHERE pr.token_hash = :th
        LIMIT 1
    ");
    $st->execute(['th' => $tokenHash]);
    $resetRow = $st->fetch(PDO::FETCH_ASSOC);

    if ($resetRow && empty($resetRow['used_at']) && strtotime($resetRow['expires_at']) > time()) {
        $valid = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf'] ?? '';
    $newPass = (string)($_POST['password'] ?? '');
    $newPass2 = (string)($_POST['password_confirm'] ?? '');
    $postToken = (string)($_POST['token'] ?? '');

    if (!verify_csrf($csrf)) {
        $message = t('reset.form.errors.invalid_request');
        $isError = true;
    } elseif ($postToken === '' || !ctype_xdigit($postToken)) {
        $message = t('reset.form.errors.invalid_link');
        $isError = true;
    } elseif (mb_strlen($newPass) < 8) {
        $message = t('reset.form.errors.pass_len');
        $isError = true;
    } elseif ($newPass !== $newPass2) {
        $message = t('reset.form.errors.pass_mismatch');
        $isError = true;
    } else {
        $tokenHash = hash('sha256', $postToken);

        // Token hala geçerli mi?
        $st = $pdo->prepare("
            SELECT id, user_id, expires_at, used_at
            FROM password_resets
            WHERE token_hash = :th
            LIMIT 1
        ");
        $st->execute(['th' => $tokenHash]);
        $row = $st->fetch(PDO::FETCH_ASSOC);

        if (!$row || !empty($row['used_at']) || strtotime($row['expires_at']) <= time()) {
            $message = t('reset.form.errors.link_expired');
            $isError = true;
        } else {
            $hash = password_hash($newPass, PASSWORD_DEFAULT);

            // users tablosundaki şifre alanın farklıysa burayı değiştir:
            $upd = $pdo->prepare("UPDATE users SET password = :p WHERE id = :uid");
            $upd->execute(['p' => $hash, 'uid' => $row['user_id']]);

            // tokenı kullanıldı işaretle
            $pdo->prepare("UPDATE password_resets SET used_at = NOW() WHERE id = :id")
                ->execute(['id' => $row['id']]);

            $message = t('reset.form.success.updated');
            $isError = false;
            $valid = false;
        }
    }
}

$csrf = csrf_token();
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo t('reset.head.title'); ?></title>
  <link rel="stylesheet" href="/assets/css/style.css?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'].'/assets/css/style.css') ?>">
  <style>
    .btn-row{
        margin-top:12px;
        display:flex;
        justify-content:center;
    }
    .btn-full{
        width:100%;
        display:block;
        text-align:center;
    }
    .btn-ghost{
        display:inline-block;
        text-align:center;
        opacity:.95;
    }
  </style>
  <?php require __DIR__ . "/partials/head.php"; ?>
</head>
<body class="page-login">
  <?php require_once __DIR__ . '/partials/navbar.php'; ?>
  <main class="page">
    <div class="glass hero-form" style="max-width:540px;margin:0 auto;">
      <h3><?php echo t('reset.hero.title'); ?></h3>

      <?php if ($message): ?>
        <div class="alert <?php echo $isError ? 'error' : 'success'; ?>">
          <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
        </div>
      <?php endif; ?>

      <?php if ($valid): ?>
        <form method="post">
          <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">
          <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">

          <div class="field">
            <label><?php echo t('reset.form.labels.new_password'); ?></label>
            <input type="password" name="password" minlength="8" required>
          </div>
          <div class="field">
            <label><?php echo t('reset.form.labels.new_password_confirm'); ?></label>
            <input type="password" name="password_confirm" minlength="8" required>
          </div>

          <button type="submit" class="cta-btn"><?php echo t('reset.form.submit'); ?></button>
        </form>
      <?php else: ?>
        <p class="lead"><?php echo t('reset.state.invalid_or_expired'); ?></p>
        <a class="cta-btn btn-full" href="forgot_password.php"><?php echo t('reset.links.new_link'); ?></a>
      <?php endif; ?>

      <div class="btn-row">
        <a href="login.php" class="cta-btn btn-ghost"><?php echo t('reset.links.back_to_login'); ?></a>
      </div>

    </div>
  </main>
  <?php require_once __DIR__ . '/partials/footer.php'; ?>
</body>
</html>

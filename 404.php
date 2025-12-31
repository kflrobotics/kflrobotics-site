<?php
http_response_code(404);
require_once __DIR__ . '/config/bootstrap.php';
?>
<!doctype html>
<html lang="tr">
<head>
    <?php require __DIR__ . '/partials/head.php'; ?>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= $L['notfound.title'] ?? 'Sayfa Bulunamadı' ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600&family=General+Sans:wght@400;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="/assets/css/style.css?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'].'/assets/css/style.css') ?>">
</head>
<body class="page-login">

<?php require_once __DIR__ . '/partials/loader.php'; ?>
<?php require_once __DIR__ . '/partials/navbar.php'; ?>

<div class="page" style="display:flex; align-items:center; justify-content:center;">
    <div class="glass" style="max-width:720px; width:100%; text-align:center;">
        <p class="badge">404</p>

        <h1><?= $L['notfound.header'] ?? 'Sayfa Bulunamadı' ?></h1>

        <p class="lead">
            <?= $L['notfound.desc'] ?? 'Aradığınız sayfa mevcut değil veya kaldırılmış olabilir.' ?>
        </p>

        <div style="margin-top:16px;">
            <a class="cta-btn" href="/">
                <?= $L['notfound.goback'] ?? 'Anasayfaya Dön' ?>
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>

</body>
</html>

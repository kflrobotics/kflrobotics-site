<?php
require_once __DIR__ . '/config/bootstrap.php';
$SEO_TITLE = t('projects.seo.title');
$SEO_DESC  = t('projects.seo.desc');
$SEO_PATH  = "/projects";
?>

<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?php echo t('projects.head.title'); ?></title>
    <?php require __DIR__ . "/partials/head.php"; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600&family=General+Sans:wght@400;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="/assets/css/style.css?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'].'/assets/css/style.css') ?>">
    <?php require __DIR__ . "/partials/head.php"; ?>
</head>

<body class="page-vision">
<?php require_once __DIR__ . '/partials/loader.php'; ?>
<?php require_once __DIR__ . '/partials/navbar.php'; ?>

<div class="page">
    <div class="glass">
        <h1><?php echo t('projects.hero.title'); ?></h1>
        <p class="lead"><?php echo t('projects.hero.lead'); ?></p>

        <ul class="list"></ul>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
</body>
</html>

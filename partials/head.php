<?php
$SEO_TITLE = $SEO_TITLE ?? t('seo.title', 'KFL Robotics');
$SEO_DESC  = $SEO_DESC  ?? t('seo.desc', 'KFL Robotics VEX robotik takımı.');
$SEO_PATH  = $SEO_PATH  ?? strtok($_SERVER['REQUEST_URI'] ?? '/', '?');

$SITE_URL  = $SITE_URL ?? 'https://kflrobotics.com';
$CANONICAL = $SITE_URL . $SEO_PATH;

$OG_IMAGE  = $OG_IMAGE ?? ($SITE_URL . '/assets/images/website.png');
?>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($SEO_TITLE) ?></title>
<meta name="description" content="<?= e($SEO_DESC) ?>">

<link rel="canonical" href="<?= e($CANONICAL) ?>">

<meta property="og:title" content="<?= e($SEO_TITLE) ?>">
<meta property="og:description" content="<?= e($SEO_DESC) ?>">
<meta property="og:url" content="<?= e($CANONICAL) ?>">
<meta property="og:image" content="<?= e($OG_IMAGE) ?>">

<link rel="stylesheet" href="/assets/css/style.css?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'].'/assets/css/style.css') ?>">
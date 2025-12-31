<?php
require_once __DIR__ . '/config/bootstrap.php';

$SEO_TITLE = t('apply.seo.title');
$SEO_DESC  = t('apply.seo.desc');
$SEO_PATH  = "/basvuru";

session_start();
const FORM_PR       = "https://basvuru.kflrobotics.com/pr";
const FORM_YAZILIM  = "https://basvuru.kflrobotics.com/coding";
const FORM_ELEKTRIK = "https://basvuru.kflrobotics.com/electrical";
const FORM_MEKANIK  = "https://basvuru.kflrobotics.com/mechanical";

$cards = [
  [
    "title" => t('apply.cards.pr.title'),
    "desc"  => t('apply.cards.pr.desc'),
    "href"  => FORM_PR,
    "icon"  => "pr"
  ],
  [
    "title" => t('apply.cards.software.title'),
    "desc"  => t('apply.cards.software.desc'),
    "href"  => FORM_YAZILIM,
    "icon"  => "code"
  ],
  [
    "title" => t('apply.cards.electric.title'),
    "desc"  => t('apply.cards.electric.desc'),
    "href"  => FORM_ELEKTRIK,
    "icon"  => "bolt"
  ],
  [
    "title" => t('apply.cards.mechanic.title'),
    "desc"  => t('apply.cards.mechanic.desc'),
    "href"  => FORM_MEKANIK,
    "icon"  => "gear"
  ],
];

function icon_svg(string $key): string {
  switch ($key) {
    case 'pr':   // megafon
      return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 11v2a2 2 0 0 0 2 2h1l4 4v-6h6a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M21 8a4 4 0 0 1 0 8" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    case 'code': // </>
      return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 9 5 12l3 3M16 9l3 3-3 3M10 19l4-14" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    case 'bolt': // şimşek
      return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M13 2 3 14h7l-1 8 10-12h-7l1-8Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>';
    case 'gear': // dişli
    default:
      return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M19.4 15a7.9 7.9 0 0 0 .1-1l2-1.2-2-3.4-2.3.6a8.4 8.4 0 0 0-.8-.8l.6-2.3-3.4-2-1.2 2a7.9 7.9 0 0 0-1-.1l-1.2-2-3.4 2 .6 2.3c-.3.2-.6.5-.8.8l-2.3-.6-2 3.4 2 1.2a7.9 7.9 0 0 0 .1 1l-2 1.2 2 3.4 2.3-.6c.2.3.5.6.8.8l-.6 2.3 3.4 2 1.2-2c.3 0 .7 0 1-.1l1.2 2 3.4-2-.6-2.3c.3-.2.6-.5.8-.8l2.3.6 2-3.4-2-1.2Z" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round" opacity="0.9"/></svg>';
  }
}
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($SEO_TITLE, ENT_QUOTES, 'UTF-8') ?></title>
  <?php require __DIR__ . "/partials/head.php"; ?>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600&family=General+Sans:wght@400;600&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="/assets/css/style.css?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'].'/assets/css/style.css') ?>">
  <link rel="stylesheet" href="/assets/css/lang.css?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'].'/assets/css/lang.css') ?>">
  <link rel="stylesheet" href="/assets/css/basvuru.css?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'].'/assets/css/basvuru.css') ?>">
</head>

<body class="page-basvuru is-loading">
  <?php require_once __DIR__ . '/partials/loader.php'; ?>
  <?php require_once __DIR__ . '/partials/navbar.php'; ?>

  <main class="page">
    <section class="glass apply-head reveal reveal-up">
      <p class="badge"><?= e(t('apply.badge'))?></p>
      <h1 style="margin-top:10px;"><?= e(t('apply.title'))?></h1>
      <p class="lead"><?= e(t('apply.lead'))?></p>
    </section>

    <section class="apply-grid">
      <?php foreach ($cards as $c): ?>
        <a class="apply-card glass reveal reveal-up"
           href="<?= htmlspecialchars($c['href'], ENT_QUOTES, 'UTF-8') ?>"
           target="_blank" rel="noopener"
           aria-label="<?= htmlspecialchars($c['title'], ENT_QUOTES, 'UTF-8') ?> başvuru formuna git">
          <div class="apply-ico"><?= icon_svg($c['icon']); ?></div>
          <div class="apply-body">
            <h3><?= htmlspecialchars($c['title'], ENT_QUOTES, 'UTF-8') ?></h3>
            <p><?= htmlspecialchars($c['desc'], ENT_QUOTES, 'UTF-8') ?></p>
          </div>
          <div class="apply-arrow">→</div>
        </a>
      <?php endforeach; ?>
    </section>
  </main>

  <script>
    window.initReveal = function () {
      const reveals = document.querySelectorAll('.reveal');
      const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('visible');
            obs.unobserve(entry.target);
          }
        });
      }, { threshold: 0.18 });

      reveals.forEach(el => observer.observe(el));
    };
    window.addEventListener('load', () => {
      document.body.classList.remove('is-loading');
      if (window.initReveal) window.initReveal();
    });
  </script>

  <?php require_once __DIR__ . '/partials/footer.php'; ?>
</body>
</html>

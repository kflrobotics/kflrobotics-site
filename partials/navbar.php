<?php
$isLoggedIn = false;
$canSeeLogs = false;

$allowedLogRoles = ['pd', 'admin', 'coding', 'leader'];

if (isset($_SESSION['user_id'])) {
  require_once __DIR__ . '/../config/database.php';
  $pdo = Database::connection();

  $stmt = $pdo->prepare('SELECT id, role FROM users WHERE id = ? LIMIT 1');
  $stmt->execute([$_SESSION['user_id']]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($user) {
    $isLoggedIn = true;
    $canSeeLogs = in_array($user['role'] ?? '', $allowedLogRoles, true);
  } else {
    session_unset();
    session_destroy();
  }
}

// ✅ aktif dil
$currentLang = $_SESSION['lang'] ?? ($_COOKIE['site_lang'] ?? 'tr');
if (!in_array($currentLang, ['tr', 'en', 'de'], true)) $currentLang = 'tr';

$annText = t('announce.text', 'Duyuru: Takım alımları başladı, aramıza katılmak için başvuru anketimize katıl!');
$annLink = t('announce.link', '/basvuru.php');
$annCta  = t('announce.cta', 'Başvur');
?>
<link rel="stylesheet" href="/assets/css/lang.css?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'].'/assets/css/lang.css') ?>">
<div class="nav-shell">
  <div class="nav">
    <div class="brand">
      <a href="/index.php" class="brand-link">
        <img src="/assets/images/logo.png" alt="KFL Robotics Logo" class="brand-logo">
        <span class="brand-text">KFL Robotics</span>
      </a>
    </div>

    <button class="hamburger" id="hamburgerBtn" aria-label="<?= e(t('nav.menu', 'Menüyü Aç')) ?>" aria-expanded="false" aria-controls="navLinks">
      <span></span><span></span><span></span>
    </button>

    <div class="nav-links" id="navLinks">
      <a href="/index.php"><?= e(t('nav.home', 'Anasayfa')) ?></a>
      <a href="/vision.php"><?= e(t('nav.vision', 'Vizyon')) ?></a>
      <a href="/team.php"><?= e(t('nav.team', 'Ekip')) ?></a>
      <a href="/suggests.php"><?= e(t('nav.suggests', 'Öneriler')) ?></a>
      <a href="/sponsors.php"><?= e(t('nav.sponsors', 'Sponsorlar')) ?></a>
      <a href="/projects.php"><?= e(t('nav.projects', 'Projelerimiz')) ?></a>
      <a class="pill nav-cta" href="/basvuru.php"><?= e(t('nav.basvuru', 'Başvuru')) ?></a>
      
      <?php if ($canSeeLogs): ?>
        <a href="/logs.php"><?= e(t('nav.logs', 'Günlükler')) ?></a>
      <?php endif; ?>

      <!-- ✅ Dil dropdown -->
      <div class="lang-dd" id="langDD">
        <button class="lang-btn" type="button" aria-haspopup="true" aria-expanded="false">
          <img class="lang-icon" src="/assets/images/flag-<?= e($currentLang) ?>.png" alt="<?= e(strtoupper($currentLang)) ?>">
          <span class="lang-code"><?= e(strtoupper($currentLang)) ?></span>
          <span class="lang-caret">▾</span>
        </button>

        <div class="lang-menu" role="menu">
          <a class="lang-item" href="?lang=tr" role="menuitem">
            <img src="/assets/images/flag-tr.png" alt="TR">
            <span><?= e(t('lang.turkish', 'Türkçe')) ?></span>
          </a>

          <a class="lang-item" href="?lang=en" role="menuitem">
            <img src="/assets/images/flag-en.png" alt="EN">
            <span><?= e(t('lang.english', 'English')) ?></span>
          </a>

          <a class="lang-item" href="?lang=de" role="menuitem">
            <img src="/assets/images/flag-de.png" alt="DE">
            <span><?= e(t('lang.german', 'Deutsch')) ?></span>
          </a>
        </div>
      </div>

      <?php if ($isLoggedIn): ?>
        <a class="pill nav-cta" href="/panel.php"><?= e(t('nav.panel', 'Kullanıcı Paneli')) ?></a>
      <?php else: ?>
        <a class="pill nav-cta" href="/login.php"><?= e(t('nav.login', 'Giriş Yap')) ?></a>
      <?php endif; ?>
    </div>
  </div>
</div>

<script src="/assets/js/main.js"></script>
<script>
(function(){
  const dd = document.getElementById('langDD');
  if(!dd) return;

  const btn = dd.querySelector('.lang-btn');
  const menu = dd.querySelector('.lang-menu');

  btn.addEventListener('click', (e) => {
    e.stopPropagation();
    dd.classList.toggle('open');
    btn.setAttribute('aria-expanded', dd.classList.contains('open') ? 'true' : 'false');
  });

  document.addEventListener('click', () => {
    dd.classList.remove('open');
    btn.setAttribute('aria-expanded', 'false');
  });

  if (menu) {
    menu.addEventListener('click', (e) => e.stopPropagation());
  }
})();
</script>

<?php
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/config/database.php';
$SEO_TITLE = t('sponsor.seo.title');
$SEO_DESC  = t('sponsor.seo.desc');
$SEO_PATH  = "/sponsor";
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo htmlspecialchars($SEO_TITLE, ENT_QUOTES, 'UTF-8'); ?></title>

  <?php require __DIR__ . "/partials/head.php"; ?>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=General+Sans:wght@400;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="/assets/css/lang.css?v=<?= @filemtime($_SERVER['DOCUMENT_ROOT'].'/assets/css/lang.css') ?>">
  <link rel="stylesheet" href="/assets/css/sponsors.css?v=<?= time() ?>">
</head>

<body class="page-sponsor is-loading">
  <?php require_once __DIR__ . '/partials/loader.php'; ?>
  <?php require_once __DIR__ . '/partials/navbar.php'; ?>

  <main class="page">
    <!-- HERO -->
    <section class="hero-panel">
      <div class="glass hero-main hero-card">
        <div class="hero-left" style="max-width:760px;">
          <p class="badge"><?php echo t('sponsor.hero.badge'); ?></p>
          <h1><?php echo t('sponsor.hero.title'); ?></h1>
          <p class="lead">
            <?php echo t('sponsor.hero.lead'); ?>
          </p>
          <div class="meta">
            <span class="tag"><?php echo t('sponsor.hero.tag1'); ?></span>
            <span class="tag"><?php echo t('sponsor.hero.tag2'); ?></span>
            <span class="tag"><?php echo t('sponsor.hero.tag3'); ?></span>
          </div>

          <div class="btn-row" style="margin-top:16px;">
            <a class="btn primary" href="#paketler" id="goPackages"><?php echo t('sponsor.hero.cta_packages'); ?></a>
          </div>
        </div>

        <div class="hero-right">
          <div class="hero-form">
            <h3><?php echo t('sponsor.contact.title'); ?></h3>
            <p class="mini lead" style="margin-top:-6px;">
              <?php echo t('sponsor.contact.lead'); ?>
            </p>

            <div class="contact-list" style="display:flex; flex-direction:column; gap:12px; margin-top:16px;">

              <!-- MAIL -->
              <div class="contact-item" style="display:flex; justify-content:space-between; align-items:center; gap:12px;">
                <div>
                  <div style="opacity:.7; font-size:12px;"><?php echo t('sponsor.contact.email_label'); ?></div>
                  <div style="font-weight:700;">info@kflrobotics.com</div>
                </div>
                <div style="display:flex; gap:8px;">
                  <a class="btn" href="mailto:info@kflrobotics.com?subject=KFL%20Robotics%20Sponsorluk">
                    <?php echo t('sponsor.contact.send_mail'); ?>
                  </a>
                  <button class="btn" type="button" data-copy="info@kflrobotics.com">
                    <?php echo t('sponsor.contact.copy'); ?>
                  </button>
                </div>
              </div>

              <!-- INSTAGRAM -->
              <div class="contact-item" style="display:flex; justify-content:space-between; align-items:center; gap:12px;">
                <div>
                  <div style="opacity:.7; font-size:12px;"><?php echo t('sponsor.contact.instagram_label'); ?></div>
                  <div style="font-weight:700;">@kflrobotics</div>
                </div>
                <a class="btn" target="_blank" href="https://instagram.com/kflrobotics">
                  <?php echo t('sponsor.contact.open_profile'); ?>
                </a>
              </div>

            </div>
          </div>

        </div>
      </div>
    </section>

    <!-- NEDEN SPONSORLUK -->
    <section class="about-team-section reveal right">
      <h2><?php echo t('sponsor.why.title'); ?></h2>
      <div class="glass about-team">
        <div class="about-text">
          <p class="lead">
            <?php echo t('sponsor.why.lead'); ?>
          </p>
          <div class="btn-row" style="margin-top:16px;">
            <a class="btn primary" href="/assets/sponsors/2025_2026_Tanıtım_ve_Sponsorluk_Dosyası.pdf" target="_blank" id="goPackages"><?php echo t('sponsor.why.cta_pdf'); ?></a>
          </div>
        </div>
      </div>
    </section>

    <!-- Sponsorlar -->
    <section class="sponsors-section reveal up">
        <div class="glass sponsors-wrap">
        <div class="sponsors-head">
            <h3><?php echo t('index.sponsors.title'); ?></h3>
            <p class="lead"><?php echo t('index.sponsors.lead'); ?></p>
        </div>

        <div class="sponsors-slider" id="sponsorsSlider">
            <div class="sponsors-track">
            <div class="sponsor-card" aria-label="<?php echo t('sponsor.sponsors.s1'); ?>">
                <img src="assets/images/sponsors/sponsor1.png" alt="<?php echo t('sponsor.sponsors.s1'); ?>">
            </div>
            <div class="sponsor-card" aria-label="<?php echo t('sponsor.sponsors.s2'); ?>">
                <img src="assets/images/sponsors/sponsor2.png" alt="<?php echo t('sponsor.sponsors.s2'); ?>">
            </div>
            <div class="sponsor-card" aria-label="<?php echo t('sponsor.sponsors.s3'); ?>">
                <img src="assets/images/sponsors/sponsor3.png" alt="<?php echo t('sponsor.sponsors.s3'); ?>">
            </div>
            <div class="sponsor-card" aria-label="<?php echo t('sponsor.sponsors.s4'); ?>">
                <img src="assets/images/sponsors/sponsor4.png" alt="<?php echo t('sponsor.sponsors.s4'); ?>">
            </div>
            <div class="sponsor-card" aria-label="<?php echo t('sponsor.sponsors.s5'); ?>">
                <img src="assets/images/sponsors/sponsor5.png" alt="<?php echo t('sponsor.sponsors.s5'); ?>">
            </div>
            </div>
        </div>
        </div>
    </section>

    <!-- PAKETLER -->
    <section id="paketler" class="features-section reveal left">
      <h2><?php echo t('sponsor.packages.title'); ?></h2>

      <div class="cards">
        <div class="card">
          <h3><?php echo t('sponsor.packages.bronze.title'); ?></h3>
          <p class="meta-small"><?php echo t('sponsor.packages.bronze.price'); ?></p>
          <p class="lead"><?php echo t('sponsor.packages.bronze.p1'); ?></p>
          <p class="lead"><?php echo t('sponsor.packages.bronze.p2'); ?></p>
          <p class="lead"><?php echo t('sponsor.packages.bronze.p3'); ?></p>
        </div>

        <div class="card">
          <h3><?php echo t('sponsor.packages.silver.title'); ?></h3>
          <p class="meta-small"><?php echo t('sponsor.packages.silver.price'); ?></p>
          <p class="lead"><?php echo t('sponsor.packages.silver.p1'); ?></p>
          <p class="lead"><?php echo t('sponsor.packages.silver.p2'); ?></p>
          <p class="lead"><?php echo t('sponsor.packages.silver.p3'); ?></p>
        </div>

        <div class="card">
          <h3><?php echo t('sponsor.packages.gold.title'); ?></h3>
          <p class="meta-small"><?php echo t('sponsor.packages.gold.price'); ?></p>
          <p class="lead"><?php echo t('sponsor.packages.gold.p1'); ?></p>
          <p class="lead"><?php echo t('sponsor.packages.gold.p2'); ?></p>
          <p class="lead"><?php echo t('sponsor.packages.gold.p3'); ?></p>
        </div>

        <div class="card">
          <h3><?php echo t('sponsor.packages.platinum.title'); ?></h3>
          <p class="meta-small"><?php echo t('sponsor.packages.platinum.price'); ?></p>
          <p class="lead"><?php echo t('sponsor.packages.platinum.p1'); ?></p>
          <p class="lead"><?php echo t('sponsor.packages.platinum.p2'); ?></p>
          <p class="lead"><?php echo t('sponsor.packages.platinum.p3'); ?></p>
        </div>
      </div>
    </section>
    <section class="kfl-mark" aria-hidden="true">
    <div class="kfl-mark-inner" id="kflMark">
        <img src="/assets/images/kflGlass.png" alt="KFLRobotics" class="kfl-mark-img" draggable="false">
        <div class="kfl-mark-spot"></div>
    </div>
    </section>
  </main>

  <script>
    // Reveal animasyonu (index ile aynı mantık)
    window.initReveal = function () {
      const reveals = document.querySelectorAll('.reveal');
      const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('visible');
            obs.unobserve(entry.target);
          }
        });
      }, { threshold: 0.25 });
      reveals.forEach(el => observer.observe(el));
    };
  </script>
  <script src="assets/js/sponsors.js"></script>
  <script src="/assets/js/kflrobotics.js?v=3"></script>
  <script>
    // Sponsor slider: mouse/touch ile sürükle-kaydır
    (function(){
      const slider = document.getElementById('sponsorsSlider');
      if(!slider) return;

      let isDown = false, startX = 0, scrollLeft = 0;

      const down = (clientX) => {
        isDown = true;
        slider.classList.add('dragging');
        startX = clientX;
        scrollLeft = slider.scrollLeft;
      };

      const move = (clientX) => {
        if(!isDown) return;
        const walk = (clientX - startX);
        slider.scrollLeft = scrollLeft - walk;
      };

      const up = () => {
        isDown = false;
        slider.classList.remove('dragging');
      };

      slider.addEventListener('mousedown', e => down(e.pageX));
      window.addEventListener('mouseup', up);
      window.addEventListener('mousemove', e => move(e.pageX));

      slider.addEventListener('touchstart', e => down(e.touches[0].pageX), {passive:true});
      slider.addEventListener('touchend', up, {passive:true});
      slider.addEventListener('touchmove', e => move(e.touches[0].pageX), {passive:true});
    })();
  </script>
  <script>
    document.querySelectorAll('[data-copy]').forEach(btn => {
      btn.addEventListener('click', () => {
        navigator.clipboard.writeText(btn.dataset.copy);
        btn.textContent = '<?php echo t('sponsor.contact.copied'); ?>';
        setTimeout(() => btn.textContent = '<?php echo t('sponsor.contact.copy'); ?>', 1200);
      });
    });
  </script>
  <script>
    document.getElementById('goPackages')?.addEventListener('click', function (e) {
      e.preventDefault();
      document.querySelector('#paketler').scrollIntoView({
        behavior: 'smooth',
        block: 'start'
      });
    });
  </script>

  <script src="assets/js/kflrobotics.js"></script>
  <?php require_once __DIR__ . '/partials/footer.php'; ?>
</body>
</html>

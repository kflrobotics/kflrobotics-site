<?php
$isLoggedIn = !empty($_SESSION['user_id']);
$currentYear = (int)date('Y');
?>

<footer class="site-footer">
  <div class="footer-grid">
    <div class="footer-col">
      <img src="/assets/images/logo.png" alt="KFL Robotics Logo">
      <div class="footer-brand">KFL Robotics</div>
      <p class="footer-desc"><?= e(t('footer.desc1', 'KFL Robotics VEX robotik takımı.')) ?></p>
      <p class="footer-desc"><?= e(t('footer.desc2', 'Hedeflerimiz, vizyonumuz ve çalışmalarımız.')) ?></p>
    </div>

    <div class="footer-col">
      <div class="footer-title"><?= e(t('footer.links')) ?></div>
      <ul class="footer-links">
        <li><a href="index.php"><?= e(t('nav.home', 'Anasayfa')) ?></a></li>
        <li><a href="vision.php"><?= e(t('nav.vision', 'Vizyon')) ?></a></li>
        <li><a href="team.php"><?= e(t('nav.team', 'Ekip')) ?></a></li>
        <li><a href="suggests.php"><?= e(t('nav.suggests', 'Öneriler')) ?></a></li>
        <li><a href="sponsors.php"><?= e(t('nav.sponsors', 'Projelerimiz')) ?></a></li>
        <li><a href="projects.php"><?= e(t('nav.projects', 'Projelerimiz')) ?></a></li>

        <?php if ($isLoggedIn): ?>
          <li><a href="panel.php"><?= e(t('nav.panel', 'Panel')) ?></a></li>
        <?php else: ?>
          <li><a href="login.php"><?= e(t('nav.login', 'Giriş Yap')) ?></a></li>
        <?php endif; ?>
      </ul>
    </div>

    <div class="footer-col">
      <div class="footer-title"><?= e(t('footer.down.contact')) ?></div>
      <div class="footer-contact">info@kflrobotics.com</div>
      <div class="footer-contact">instagram.com/kflrobotics</div>
      <div class="footer-contact">github.com/kflrobotics</div>
      <div class="footer-copyright">© <?= $currentYear ?> KFL Robotics</div>
      <div class="footer-contact">made with ❤️ by ege</div>
    </div>
  </div>
</footer>

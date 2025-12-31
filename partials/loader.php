<head>
  <link rel="preload" as="image" href="/assets/images/logo.png">
</head>
<div id="loader" class="loader-overlay">
  <div class="loader-logo-wrap" aria-label="Loading">
    <img src="/assets/images/logo.png" alt="KFL Robotics" class="loader-logo loader-logo--base" draggable="false">
    <img src="/assets/images/logo.png" alt="" class="loader-logo loader-logo--fill" draggable="false">
  </div>
</div>


<script>
  document.body.classList.add('is-loading');

  window.addEventListener('load', () => {
    setTimeout(() => {
      document.body.classList.remove('is-loading');
      const el = document.getElementById('loader');
      if (el) el.classList.add('hide');

      setTimeout(() => {
        if (window.initReveal) window.initReveal();
      }, 100);
    }, 450);
  });
</script>

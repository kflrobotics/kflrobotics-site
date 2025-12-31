(function () {
  const navbar = document.querySelector('.nav-shell');
  if (!navbar) return;

  // -----------------------
  // Hamburger (mobile)
  // -----------------------
  const hamburgerBtn = document.getElementById('hamburgerBtn');
  const navLinks = document.getElementById('navLinks') || navbar.querySelector('.nav-links');

  function setExpanded(isOpen) {
    if (hamburgerBtn) hamburgerBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  }

  function closeMenu() {
    if (!navbar.classList.contains('menu-open')) return;
    navbar.classList.remove('menu-open');
    setExpanded(false);
  }

  function toggleMenu() {
    const isOpen = navbar.classList.toggle('menu-open');
    setExpanded(isOpen);
    // Menü açıkken navbar gizlenmesin
    if (isOpen) navbar.classList.remove('nav-hidden');
  }

  if (hamburgerBtn) {
    hamburgerBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      toggleMenu();
    });
  }

  // Menü dışına dokununca kapat
  document.addEventListener('click', (e) => {
    if (!navbar.classList.contains('menu-open')) return;
    if (!navbar.contains(e.target)) closeMenu();
  });

  // ESC ile kapat
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeMenu();
  });

  // Menüden bir linke basınca kapat
  if (navLinks) {
    navLinks.addEventListener('click', (e) => {
      const a = e.target.closest('a');
      if (a) closeMenu();
    });
  }

  // -----------------------
  // Navbar hide/show on scroll
  // -----------------------
  let lastScrollY = window.scrollY;
  let ticking = false;

  function setNavbarState(currentScrollY, force = false) {
    // Menü açıkken navbar gizlenmesin (index.php'de tıklanabilirlik sorunu buradan çıkıyordu)
    if (navbar.classList.contains('menu-open')) {
      navbar.classList.remove('nav-hidden');
      return;
    }

    if (currentScrollY <= 0) {
      navbar.classList.remove('nav-hidden');
      return;
    }

    if (force && currentScrollY > 120) {
      navbar.classList.add('nav-hidden');
      return;
    }

    if (currentScrollY > lastScrollY && currentScrollY > 120) {
      navbar.classList.add('nav-hidden');
    } else {
      navbar.classList.remove('nav-hidden');
    }
  }

  function onScroll() {
    const currentScrollY = window.scrollY;

    if (Math.abs(currentScrollY - lastScrollY) < 10) {
      ticking = false;
      return;
    }

    setNavbarState(currentScrollY);
    lastScrollY = currentScrollY;
    ticking = false;
  }

  function init() {
    const y = window.scrollY;
    setNavbarState(y, true);
    lastScrollY = y;
  }

  document.addEventListener('DOMContentLoaded', init);
  window.addEventListener('load', init);
  window.addEventListener('pageshow', init);

  window.addEventListener('scroll', () => {
    if (!ticking) {
      window.requestAnimationFrame(onScroll);
      ticking = true;
    }
  });

  // Ekran büyüyünce mobil menüyü kapat (örn. rotasyon / tablet)
  window.addEventListener('resize', () => {
    if (window.innerWidth > 768) closeMenu();
  });
})();
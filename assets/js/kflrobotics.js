/* KFL BIG MARK spotlight hover (mousemove follow) */
(function () {
  const wrap = document.getElementById('kflMark');
  if (!wrap) return;

  const spot = wrap.querySelector('.kfl-mark-spot');
  if (!spot) return;

  // mouse hareketi inner üzerinde, ama ölçüm için rect'i inner'dan alıyoruz
  function setSpot(e) {
    const r = wrap.getBoundingClientRect();
    const x = ((e.clientX - r.left) / r.width) * 100;
    const y = ((e.clientY - r.top) / r.height) * 100;

    // clamp (taşma olmasın)
    const cx = Math.max(0, Math.min(100, x));
    const cy = Math.max(0, Math.min(100, y));

    spot.style.setProperty('--sx', `${cx}%`);
    spot.style.setProperty('--sy', `${cy}%`);
  }

  wrap.addEventListener('mousemove', setSpot, { passive: true });

  // ilk girişte merkezle
  wrap.addEventListener('mouseenter', () => {
    spot.style.setProperty('--sx', `50%`);
    spot.style.setProperty('--sy', `35%`);
  });
})();

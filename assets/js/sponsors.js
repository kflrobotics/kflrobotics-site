(function(){
  const slider = document.getElementById('sponsorsSlider');
  if (!slider) return;

  const track = slider.querySelector('.sponsors-track');
  if (!track) return;

  const originals = Array.from(track.children);
  if (originals.length < 2) return;

  // === 1) 3 SET YAP (orijinal + 2 klon) ===
  originals.forEach(el => track.appendChild(el.cloneNode(true)));
  originals.forEach(el => track.appendChild(el.cloneNode(true)));

  // === 2) GAP HESABI ===
  function getGapPx(){
    const cs = getComputedStyle(track);
    return parseFloat(cs.gap || cs.columnGap || 0) || 0;
  }

  let setWidth = 0;

  function recalc(){
    const gap = getGapPx();
    setWidth = 0;
    for (let i = 0; i < originals.length; i++){
      setWidth += originals[i].offsetWidth;
      if (i !== originals.length - 1) setWidth += gap;
    }

    // Başlangıç: ORTA SET
    slider.scrollLeft = setWidth;
  }

  window.addEventListener('load', recalc);
  window.addEventListener('resize', recalc);
  setTimeout(recalc, 50);

  // === 3) LOOP FIX (İLERİ + GERİ) ===
  function loopFix(){
    if (slider.scrollLeft < setWidth * 0.5) {
      slider.scrollLeft += setWidth;
    }
    else if (slider.scrollLeft > setWidth * 1.5) {
      slider.scrollLeft -= setWidth;
    }
  }

  // === 4) DRAG ===
  let dragging = false;
  let startX = 0;
  let startScroll = 0;

  const getX = e => (e.touches ? e.touches[0].clientX : e.clientX);

  function dragStart(e){
    dragging = true;
    slider.classList.add('dragging');
    startX = getX(e);
    startScroll = slider.scrollLeft;
  }

  function dragMove(e){
    if (!dragging) return;
    e.preventDefault();
    const dx = getX(e) - startX;
    slider.scrollLeft = startScroll - dx;
    loopFix();
  }

  function dragEnd(){
    dragging = false;
    slider.classList.remove('dragging');
  }

  slider.addEventListener('mousedown', e => { if (e.button === 0) dragStart(e); });
  window.addEventListener('mousemove', dragMove);
  window.addEventListener('mouseup', dragEnd);

  slider.addEventListener('touchstart', dragStart, {passive:false});
  window.addEventListener('touchmove', dragMove, {passive:false});
  window.addEventListener('touchend', dragEnd);

  // === 5) AUTO SCROLL (SADECE DRAG'DE DURUR) ===
  const speed = 0.6; // negatif yaparsan sola akar

  function auto(){
    if (!dragging) {
      slider.scrollLeft += speed;
      loopFix();
    }
    requestAnimationFrame(auto);
  }
  requestAnimationFrame(auto);

})();

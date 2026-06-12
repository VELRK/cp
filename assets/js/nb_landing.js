(function () {
  var nav = document.getElementById('nbNavbar');
  if (!nav || !document.body.classList.contains('nb-page-home')) return;

  function onScroll() {
    if (window.scrollY > 48) {
      nav.classList.add('nb-nav-solid');
    } else {
      nav.classList.remove('nb-nav-solid');
    }
  }

  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
})();

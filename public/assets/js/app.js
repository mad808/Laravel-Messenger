(function () {
  const html = document.documentElement;
  const sidebar = document.getElementById('sidebar');
  const backdrop = document.getElementById('sidebarBackdrop');

  /* ---------- theme ---------- */
  function updateThemeIcons(theme) {
    document.querySelectorAll('.theme-toggle-icon').forEach(function (icon) {
      icon.className = 'bi ' + (theme === 'light' ? 'bi-moon-fill' : 'bi-sun-fill') + ' theme-toggle-icon';
    });
  }
  window.toggleTheme = function () {
    const next = html.getAttribute('data-bs-theme') === 'light' ? 'dark' : 'light';
    html.setAttribute('data-bs-theme', next);
    localStorage.setItem('theme', next);
    updateThemeIcons(next);
  };
  updateThemeIcons(html.getAttribute('data-bs-theme'));

  /* ---------- mobile sidebar ---------- */
  window.openSidebar = function () {
    if (!sidebar || !backdrop) return;
    sidebar.classList.add('show');
    backdrop.classList.add('show');
  };
  window.closeSidebar = function () {
    if (!sidebar || !backdrop) return;
    sidebar.classList.remove('show');
    backdrop.classList.remove('show');
  };
  window.toggleSidebar = function () {
    if (!sidebar) return;
    sidebar.classList.contains('show') ? window.closeSidebar() : window.openSidebar();
  };
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') window.closeSidebar(); });
  window.addEventListener('resize', function () { if (window.innerWidth >= 768) window.closeSidebar(); });

  /* ---------- touch swipe for mobile sidebar ---------- */
  let touchX = 0, touchY = 0, edgeSwipe = false;
  document.addEventListener('touchstart', function (e) {
    if (window.innerWidth >= 768 || !e.touches.length) return;
    touchX = e.touches[0].clientX;
    touchY = e.touches[0].clientY;
    edgeSwipe = touchX < 36 || (sidebar && sidebar.classList.contains('show'));
  }, { passive: true });

  document.addEventListener('touchend', function (e) {
    if (!edgeSwipe || window.innerWidth >= 768 || !e.changedTouches.length) return;
    const dx = e.changedTouches[0].clientX - touchX;
    const dy = e.changedTouches[0].clientY - touchY;
    edgeSwipe = false;
    if (Math.abs(dy) > Math.abs(dx)) return;
    if (dx > 60 && !sidebar.classList.contains('show')) window.openSidebar();
    else if (dx < -60 && sidebar.classList.contains('show')) window.closeSidebar();
  }, { passive: true });

  /* ---------- sidebar search ---------- */
  const search = document.getElementById('sidebarSearch');
  const noResults = document.getElementById('sidebarNoResults');
  if (search) {
    search.addEventListener('input', function () {
      const q = search.value.trim().toLowerCase();
      const items = document.querySelectorAll('#chat-list [data-search-item]');
      let visible = 0;
      items.forEach(function (el) {
        const show = !q || el.textContent.toLowerCase().includes(q);
        el.classList.toggle('d-none', !show);
        if (show) visible++;
      });
      if (noResults) noResults.classList.toggle('d-none', !(q && items.length && visible === 0));
    });
  }

  /* ---------- flash messages ---------- */
  try {
    const flash = JSON.parse(document.getElementById('flash-data').textContent);
    if (window.toastr) {
      toastr.options = { closeButton: true, progressBar: true, positionClass: 'toast-top-right' };
      if (flash.success) toastr.success(flash.success);
      if (flash.status)  toastr.info(flash.status);
      if (flash.error)   toastr.error(flash.error);
    }
  } catch (e) {}
})();
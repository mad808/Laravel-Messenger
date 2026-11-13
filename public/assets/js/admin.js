(function () {
  const html = document.documentElement;
  const sidebar = document.getElementById('admSidebar');
  const backdrop = document.getElementById('admBackdrop');

  /* theme */
  const icon = document.getElementById('theme-icon');
  function setIcon(t) { if (icon) icon.className = 'bi ' + (t === 'light' ? 'bi-moon-fill' : 'bi-sun-fill'); }
  setIcon(html.getAttribute('data-bs-theme'));
  const toggle = document.getElementById('theme-toggle');
  if (toggle) toggle.addEventListener('click', function () {
    const next = html.getAttribute('data-bs-theme') === 'light' ? 'dark' : 'light';
    html.setAttribute('data-bs-theme', next);
    localStorage.setItem('theme', next);
    setIcon(next);
  });

  /* mobile sidebar */
  function open()  { sidebar.classList.add('show');    backdrop.classList.add('show'); }
  function close() { sidebar.classList.remove('show'); backdrop.classList.remove('show'); }
  document.querySelectorAll('[data-adm-open]').forEach(function (b) { b.addEventListener('click', open); });
  document.querySelectorAll('[data-adm-close]').forEach(function (b) { b.addEventListener('click', close); });
  backdrop.addEventListener('click', close);
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
  window.addEventListener('resize', function () { if (window.innerWidth >= 992) close(); });

  document.addEventListener('submit', function (e) {
    const msg = e.target.getAttribute && e.target.getAttribute('data-confirm');
    if (msg && !window.confirm(msg)) e.preventDefault();
  });

  /* flash messages */
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
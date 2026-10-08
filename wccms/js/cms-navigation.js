(() => {
 function initializeNavigation() {
  const sidebar = document.getElementById('sidebar');
  if (!sidebar || sidebar.dataset.navigationReady === 'true') return;
  sidebar.dataset.navigationReady = 'true';
  const toggle = document.getElementById('sidebarToggle');
  const mobile = window.matchMedia('(max-width: 768px)');
  function setSidebarVisible(visible) {
   sidebar.hidden = !visible;
   document.body.classList.toggle('wccms-sidebar-hidden', !visible);
   if (toggle) toggle.setAttribute('aria-expanded', String(visible));
  }
  setSidebarVisible(!mobile.matches);
  if (toggle) {
   toggle.setAttribute('aria-controls', 'sidebar');
   toggle.addEventListener('click', (event) => {
    event.preventDefault();
    setSidebarVisible(sidebar.hidden);
   });
  }
  sidebar.querySelectorAll('[data-wccms-submenu]').forEach((link) => {
   const submenu = document.getElementById(link.getAttribute('aria-controls'));
   if (!submenu) return;
   link.addEventListener('click', (event) => {
    event.preventDefault();
    submenu.hidden = !submenu.hidden;
    link.setAttribute('aria-expanded', String(!submenu.hidden));
   });
  });
  mobile.addEventListener('change', () => setSidebarVisible(!mobile.matches));
 }
 if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initializeNavigation, { once: true });
 } else initializeNavigation();
})();

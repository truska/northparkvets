(() => {
 function initializeComponents() {
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((element) => {
   bootstrap.Tooltip.getOrCreateInstance(element);
  });
  document.querySelectorAll('[data-bs-toggle="popover"]').forEach((element) => {
   bootstrap.Popover.getOrCreateInstance(element);
  });
 }
 if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initializeComponents, { once: true });
 } else initializeComponents();
})();

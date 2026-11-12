/* Không dùng JavaScript inline để giữ CSP strict. */
(() => {
  const button = document.querySelector('[data-nav-toggle]');
  const nav = document.getElementById('jh-primary-nav');
  const filters = document.querySelector('.jh-filter-details');
  const mobile = window.matchMedia('(max-width: 990px)');
  if (filters) {
    const syncFilters = () => { filters.open = !mobile.matches; };
    syncFilters();
    mobile.addEventListener('change', syncFilters);
  }
  if (!button || !nav) return;
  const account = document.getElementById('jh-account-menu');
  function close() { button.setAttribute('aria-expanded', 'false'); nav.classList.remove('is-open'); }
  button.addEventListener('click', () => {
    const open = button.getAttribute('aria-expanded') !== 'true';
    button.setAttribute('aria-expanded', String(open));
    nav.classList.toggle('is-open', open);
  });
  nav.addEventListener('click', event => { if (event.target.closest('a')) close(); });
  document.addEventListener('keydown', event => { if (event.key === 'Escape') close(); });
  document.addEventListener('click', event => { if (!nav.contains(event.target) && !button.contains(event.target) && !account?.contains(event.target)) close(); });
})();

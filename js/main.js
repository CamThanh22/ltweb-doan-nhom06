import { docYeuThich, doiYeuThich } from './yeu-thich.js';
document.documentElement.classList.add('js');
const menu = document.querySelector('#menu-chinh');
const nut = document.querySelector('.nut-menu');
if (menu && nut) {
  nut.addEventListener('click', () => {
    const mo = menu.classList.toggle('mo');
    nut.setAttribute('aria-expanded', String(mo));
  });
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && menu.classList.contains('mo')) {
      menu.classList.remove('mo'); nut.setAttribute('aria-expanded', 'false'); nut.focus();
    }
  });
}
function capNhat() {
  document.querySelectorAll('.dem-yeu-thich').forEach(el => { el.textContent = String(docYeuThich().length); });
  document.querySelectorAll('[data-yeu-thich]').forEach(btn => {
    const dangThich = docYeuThich().includes(Number(btn.dataset.yeuThich));
    btn.textContent = dangThich ? 'Bỏ yêu thích' : 'Yêu thích';
    btn.setAttribute('aria-pressed', String(dangThich));
  });
}
document.addEventListener('click', e => {
  const btn = e.target.closest('[data-yeu-thich]');
  if (btn) doiYeuThich(Number(btn.dataset.yeuThich));
});
window.addEventListener('jadehub:yeu-thich', capNhat);
window.addEventListener('storage', capNhat);
capNhat();

/*
 * Nút yêu thích ở danh-sach.php và chi-tiet.php (HTML do PHP sinh, nút mang data-yeuthich="<id>").
 * Danh sách yêu thích lưu trong localStorage (yeu-thich.js); dùng event delegation trên document.
 * Không có JavaScript thì nút bị ẩn bằng CSS (html:not(.js)), các chức năng chính của site vẫn dùng được.
 */
import { daoYeuThich, coYeuThich } from './yeu-thich.js';

function capNhatNut(nut) {
    const dang = coYeuThich(nut.dataset.yeuthich);
    nut.setAttribute('aria-pressed', String(dang));
    nut.textContent = dang ? '♥ Bỏ yêu thích' : '♡ Yêu thích';
}

document.querySelectorAll('[data-yeuthich]').forEach(capNhatNut);

document.addEventListener('click', (event) => {
    const nut = event.target.closest('[data-yeuthich]');
    if (!nut) {
        return;
    }
    daoYeuThich(nut.dataset.yeuthich);
    capNhatNut(nut);
});

// Đồng bộ khi tab khác đổi danh sách
window.addEventListener('storage', () => {
    document.querySelectorAll('[data-yeuthich]').forEach(capNhatNut);
});

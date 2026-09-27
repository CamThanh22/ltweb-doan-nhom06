// JavaScript dùng chung cho mọi trang JadeHub.
// Tạo menu điều hướng trên thiết bị di động.
// Hỗ trợ đóng menu bằng phím Escape.
// Cập nhật số lượng sản phẩm yêu thích từ localStorage.

import { docYeuThich } from './yeu-thich.js';

document.documentElement.classList.add('js');

const nav = document.querySelector('nav');

if (nav) {
    const ul = nav.querySelector('ul');

    if (ul) {
        const nutMenu = document.createElement('button');

        nutMenu.type = 'button';
        nutMenu.className = 'nut-menu';
        nutMenu.textContent = '☰ Menu';
        nutMenu.setAttribute('aria-expanded', 'false');
        nutMenu.setAttribute('aria-controls', 'menu-chinh');

        ul.id = 'menu-chinh';

        nav.insertBefore(nutMenu, ul);

        // Mở hoặc đóng menu
        nutMenu.addEventListener('click', () => {
            const dangMo = ul.classList.toggle('mo');

            nutMenu.setAttribute(
                'aria-expanded',
                String(dangMo)
            );
        });

        // Nhấn Escape để đóng menu
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                ul.classList.remove('mo');
                nutMenu.setAttribute('aria-expanded', 'false');
                nutMenu.focus();
            }
        });
    }
}

// Cập nhật số lượng sản phẩm yêu thích
function capNhatDemYeuThich() {
    const soLuong = docYeuThich().length;

    document
        .querySelectorAll('[data-dem-yeu-thich]')
        .forEach((phanTu) => {
            phanTu.textContent = String(soLuong);
        });
}

capNhatDemYeuThich();

// Cập nhật khi danh sách yêu thích thay đổi
window.addEventListener(
    'yeuthich-thaydoi',
    capNhatDemYeuThich
);

// Đồng bộ khi localStorage thay đổi ở tab khác
window.addEventListener(
    'storage',
    capNhatDemYeuThich
);
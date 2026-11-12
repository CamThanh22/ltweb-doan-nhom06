import { docYeuThich } from './yeu-thich.js';
document.documentElement.classList.add('js');
// Menu được quản lý bởi jadehub-navigation.js.
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
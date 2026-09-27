/*
 * JavaScript cho trang cá nhân Huỳnh Trần Phương Trinh.
 * Tương tác 1: hiển thị thanh tiến độ đọc khi cuộn trang.
 * Tương tác 2: nút quay trở lại đầu trang.
 * Cách thử: cuộn trang xuống và nhấn nút Đầu trang.
 */

const thanhTienDo = document.createElement('div');
thanhTienDo.className = 'thanh-tien-do';
document.body.prepend(thanhTienDo);

const nutDauTrang = document.createElement('button');
nutDauTrang.type = 'button';
nutDauTrang.className = 'nut-dau-trang';
nutDauTrang.textContent = '↑ Đầu trang';
nutDauTrang.setAttribute('aria-label', 'Quay lại đầu trang');
document.body.append(nutDauTrang);


// ==============================
// 1. THANH TIẾN ĐỘ ĐỌC
// ==============================

function capNhatTienDo() {
    const doCaoCuon =
        document.documentElement.scrollHeight -
        window.innerHeight;

    const phanTram =
        doCaoCuon > 0
            ? (window.scrollY / doCaoCuon) * 100
            : 0;

    thanhTienDo.style.width =
        `${Math.min(100, phanTram)}%`;
}

window.addEventListener(
    'scroll',
    capNhatTienDo,
    { passive: true }
);

window.addEventListener(
    'resize',
    capNhatTienDo
);

capNhatTienDo();


// ==============================
// 2. NÚT QUAY LẠI ĐẦU TRANG
// ==============================

function capNhatNutDauTrang() {
    nutDauTrang.classList.toggle(
        'hien',
        window.scrollY > 300
    );
}

window.addEventListener(
    'scroll',
    capNhatNutDauTrang,
    { passive: true }
);

nutDauTrang.addEventListener(
    'click',
    () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }
);

capNhatNutDauTrang();
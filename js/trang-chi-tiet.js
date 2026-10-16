/*
 * Hiển thị chi tiết một sản phẩm JadeHub theo id trên URL.
 * Dữ liệu sản phẩm được đọc từ data/san-pham.json.
 * Cập nhật ảnh, chứng thư, bảng kiểm định và thông tin sản phẩm.
 * Hỗ trợ yêu thích và tự tạo danh sách sản phẩm liên quan.
 */

import { taiJSON } from './api.js';

import {
    daoYeuThich,
    coYeuThich
} from './yeu-thich.js';

const khungChiTiet =
    document.querySelector('#chi-tiet-dong');

const trangThai =
    document.querySelector('#trang-thai-chi-tiet');

const danhSachLienQuan =
    document.querySelector('#san-pham-lien-quan');


// =========================
// ĐỌC ID TRÊN URL
// =========================

const thamSo =
    new URLSearchParams(location.search);

const id =
    Number(thamSo.get('id') || 1);


// =========================
// GÁN NỘI DUNG
// =========================

function ganNoiDung(selector, noiDung) {
    const phanTu =
        khungChiTiet.querySelector(selector);

    if (phanTu) {
        phanTu.textContent = noiDung;
    }
}


// =========================
// HIỂN THỊ SẢN PHẨM LIÊN QUAN
// =========================

function hienThiLienQuan(danhSach, sanPhamHienTai) {
    if (!danhSachLienQuan) {
        return;
    }

    danhSachLienQuan.replaceChildren();

    const lienQuan = danhSach
        .filter(
            (sanPham) =>
                sanPham.id !== sanPhamHienTai.id
        )
        .slice(0, 3);

    lienQuan.forEach((sanPham) => {
        const li =
            document.createElement('li');

        const link =
            document.createElement('a');

        link.href =
            `chi-tiet.html?id=${sanPham.id}`;

        link.textContent =
            sanPham.ten;

        li.append(link);
        danhSachLienQuan.append(li);
    });
}


// =========================
// HIỂN THỊ CHI TIẾT
// =========================

function hienThiSanPham(sanPham, danhSach) {

    document.title =
        `${sanPham.ten} | JadeHub`;

    ganNoiDung(
        '[data-ten]',
        `${sanPham.ten} - Mã số ${sanPham.ma}`
    );

    ganNoiDung(
        '[data-mota]',
        sanPham.moTa
    );

    ganNoiDung(
        '[data-ma]',
        sanPham.ma
    );

    ganNoiDung(
        '[data-loai]',
        sanPham.loai
    );

    ganNoiDung(
        '[data-xuatxu]',
        sanPham.xuatXu
    );

    ganNoiDung(
        '[data-kiem-dinh]',
        sanPham.kiemDinh
    );


    // Xác định loại khoáng vật
    const khoangVat =
        sanPham.loai.includes('Nephrite')
            ? 'Nephrite tự nhiên'
            : 'Jadeite tự nhiên';

    ganNoiDung(
        '[data-khoang]',
        khoangVat
    );

    ganNoiDung(
        '[data-trongluong]',
        sanPham.trongLuong
    );

    ganNoiDung(
        '[data-docung]',
        sanPham.doCung
    );

    ganNoiDung(
        '[data-chietsuat]',
        sanPham.chietSuat
    );

    ganNoiDung(
        '[data-tytrong]',
        sanPham.tyTrong
    );


    // =====================
    // ẢNH SẢN PHẨM
    // =====================

    const anh =
        khungChiTiet.querySelector(
            '[data-anh]'
        );

    if (anh) {
        anh.src =
            `images/${sanPham.anh}`;

        anh.alt =
            `Ảnh sản phẩm ${sanPham.ten}`;
    }


    // =====================
    // ẢNH CHỨNG THƯ
    // =====================

    const chungThu =
        khungChiTiet.querySelector(
            '[data-chungthu]'
        );

    if (chungThu) {
        const chuThichChungThu =
            chungThu.closest('figure')?.querySelector('figcaption');

        if (sanPham.anhChungThu) {
            // Sản phẩm đã có ảnh chứng thư
            chungThu.hidden = false;
            chungThu.src = `images/${sanPham.anhChungThu}`;
            chungThu.alt = `Chứng thư kiểm định ${sanPham.ma}`;

            if (chuThichChungThu) {
                chuThichChungThu.textContent =
                    `Chứng thư kiểm định của ${sanPham.ma}`;
            }
        } else {
            // Sản phẩm chưa có ảnh chứng thư
            chungThu.hidden = true;

            if (chuThichChungThu) {
                chuThichChungThu.textContent =
                    'Ảnh chứng thư kiểm định đang chờ bổ sung sau khi hoàn tất hồ sơ thẩm định.';
            }
        }
    }


    // =====================
    // YÊU THÍCH
    // =====================

    const nutYeuThich =
        khungChiTiet.querySelector(
            '[data-yeuthich]'
        );

    if (nutYeuThich) {
        function capNhatNutYeuThich() {
            const dangYeuThich =
                coYeuThich(sanPham.id);

            nutYeuThich.textContent =
                dangYeuThich
                    ? '♥ Bỏ yêu thích'
                    : '♡ Thêm vào yêu thích';

            nutYeuThich.setAttribute(
                'aria-pressed',
                String(dangYeuThich)
            );
        }

        nutYeuThich.addEventListener(
            'click',
            () => {
                daoYeuThich(sanPham.id);
                capNhatNutYeuThich();
            }
        );

        capNhatNutYeuThich();
    }

    // Sản phẩm liên quan
    hienThiLienQuan(
        danhSach,
        sanPham
    );
}


// =========================
// TẢI JSON
// =========================

async function taiChiTietSanPham() {
    try {
        trangThai.textContent =
            'Đang tải thông tin sản phẩm…';

        const danhSach =
            await taiJSON(
                'data/san-pham.json'
            );

        const sanPham =
            danhSach.find(
                (item) => item.id === id
            );


        // Không tìm thấy ID
        if (!sanPham) {
            khungChiTiet.hidden = true;
            trangThai.replaceChildren();

            const thongBao = document.createElement('p');
            thongBao.textContent = 'Không tìm thấy sản phẩm.';
            trangThai.append(thongBao);
            return;
        }


        hienThiSanPham(
            sanPham,
            danhSach
        );

        trangThai.textContent = '';

    } catch (error) {
        console.error(
            'Không thể tải chi tiết sản phẩm:',
            error
        );

        // Giữ nội dung tĩnh trong HTML làm phương án dự phòng.
        khungChiTiet.hidden = false;
        trangThai.replaceChildren();

        const thongBao = document.createElement('p');
        thongBao.textContent =
            'Không tải được dữ liệu mới. ' +
            'Đang hiển thị nội dung dự phòng.';

        const nutThuLai = document.createElement('button');
        nutThuLai.type = 'button';
        nutThuLai.textContent = 'Thử lại';
        nutThuLai.addEventListener('click', taiChiTietSanPham);

        trangThai.append(thongBao, nutThuLai);
    }
}

taiChiTietSanPham();
/*
 * Tải danh sách sản phẩm JadeHub từ tệp JSON.
 * Hỗ trợ tìm kiếm không dấu, lọc theo loại và sắp xếp sản phẩm.
 * Hiển thị sản phẩm an toàn bằng createElement và textContent.
 * Sử dụng event delegation cho chức năng yêu thích.
 */

import { taiJSON } from './api.js';
import {
    daoYeuThich,
    coYeuThich
} from './yeu-thich.js';

const khung = document.querySelector('#ds-dong');
const thongBao = document.querySelector('#trang-thai-ds');

const timKiem = document.querySelector('#tim-kiem');
const locLoai = document.querySelector('#loc-loai');
const sapXep = document.querySelector('#sap-xep');

let danhSachSanPham = [];


/* =========================
   HÀM BỎ DẤU TIẾNG VIỆT
   ========================= */

function boDau(chuoi) {
    return (chuoi ?? '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/đ/g, 'd')
        .replace(/Đ/g, 'D')
        .toLowerCase();
}


/* =========================
   HÀM TẠO PHẦN TỬ HTML
   ========================= */

function taoPhanTu(tag, noiDung, className) {
    const phanTu = document.createElement(tag);

    if (noiDung !== undefined) {
        phanTu.textContent = noiDung;
    }

    if (className) {
        phanTu.className = className;
    }

    return phanTu;
}


/* =========================
   TẠO THẺ SẢN PHẨM
   ========================= */

function taoTheSanPham(sanPham) {
    const article = taoPhanTu(
        'article',
        undefined,
        'the-san-pham'
    );

    // Ảnh sản phẩm
    const anh = document.createElement('img');

    anh.src = `images/${sanPham.anh}`;
    anh.alt = sanPham.ten;
    anh.loading = 'lazy';
    anh.width = 400;
    anh.height = 300;

    // Nội dung sản phẩm
    const noiDung = taoPhanTu(
        'div',
        undefined,
        'noi-dung-the'
    );

    const ten = taoPhanTu(
        'h3',
        sanPham.ten
    );

    const loai = taoPhanTu(
        'p',
        sanPham.loai
    );

    const gia = taoPhanTu(
        'p',
        `${sanPham.gia.toLocaleString('vi-VN')} VNĐ`,
        'gia-san-pham'
    );

    // Link chi tiết
    const linkChiTiet = taoPhanTu(
        'a',
        'Xem chi tiết'
    );

    linkChiTiet.href =
        `chi-tiet.html?id=${sanPham.id}`;

    // Nút yêu thích
    const dangYeuThich =
        coYeuThich(sanPham.id);

    const nutYeuThich = taoPhanTu(
        'button',
        dangYeuThich
            ? '♥ Bỏ yêu thích'
            : '♡ Yêu thích',
        'nut-yeu-thich'
    );

    nutYeuThich.type = 'button';
    nutYeuThich.dataset.id = sanPham.id;

    nutYeuThich.setAttribute(
        'aria-pressed',
        String(dangYeuThich)
    );

    noiDung.append(
        ten,
        loai,
        gia,
        linkChiTiet,
        nutYeuThich
    );

    article.append(
        anh,
        noiDung
    );

    return article;
}


/* =========================
   LỌC VÀ SẮP XẾP
   ========================= */

function layDanhSachHienThi() {
    const tuKhoa = boDau(
        timKiem.value.trim()
    );

    let ketQua = danhSachSanPham.filter(
        (sanPham) => {
            const noiDungTimKiem = boDau(
                `${sanPham.ten} ${sanPham.loai} ${sanPham.ma}`
            );

            const dungTuKhoa =
                !tuKhoa ||
                noiDungTimKiem.includes(tuKhoa);

            const dungLoai =
                !locLoai.value ||
                sanPham.loai === locLoai.value;

            return dungTuKhoa && dungLoai;
        }
    );

    ketQua = [...ketQua];

    switch (sapXep.value) {
        case 'gia-tang':
            ketQua.sort(
                (a, b) => a.gia - b.gia
            );
            break;

        case 'gia-giam':
            ketQua.sort(
                (a, b) => b.gia - a.gia
            );
            break;

        case 'ten':
            ketQua.sort(
                (a, b) =>
                    a.ten.localeCompare(
                        b.ten,
                        'vi'
                    )
            );
            break;

        default:
            ketQua.sort(
                (a, b) => a.id - b.id
            );
    }

    return ketQua;
}


/* =========================
   HIỂN THỊ DANH SÁCH
   ========================= */

function hienThiDanhSach() {
    const ketQua = layDanhSachHienThi();

    khung.replaceChildren();

    // Trạng thái rỗng
    if (ketQua.length === 0) {
        thongBao.textContent =
            'Không có sản phẩm phù hợp.';

        return;
    }

    thongBao.textContent =
        `Tìm thấy ${ketQua.length} sản phẩm.`;

    ketQua.forEach((sanPham) => {
        const theSanPham =
            taoTheSanPham(sanPham);

        khung.append(theSanPham);
    });
}


/* =========================
   YÊU THÍCH
   Event Delegation
   ========================= */

khung.addEventListener(
    'click',
    (event) => {
        const nut = event.target.closest(
            '.nut-yeu-thich'
        );

        if (!nut) {
            return;
        }

        const dangYeuThich =
            daoYeuThich(nut.dataset.id);

        nut.textContent = dangYeuThich
            ? '♥ Bỏ yêu thích'
            : '♡ Yêu thích';

        nut.setAttribute(
            'aria-pressed',
            String(dangYeuThich)
        );
    }
);


/* =========================
   TÌM KIẾM TỨC THỜI
   ========================= */

timKiem.addEventListener(
    'input',
    hienThiDanhSach
);


/* =========================
   LỌC THEO LOẠI
   ========================= */

locLoai.addEventListener(
    'change',
    hienThiDanhSach
);


/* =========================
   SẮP XẾP
   ========================= */

sapXep.addEventListener(
    'change',
    hienThiDanhSach
);


/* =========================
   TẢI DỮ LIỆU JSON
   ========================= */

async function taiDanhSachSanPham() {
    try {
        thongBao.textContent =
            'Đang tải danh sách sản phẩm…';

        danhSachSanPham = await taiJSON(
            'data/san-pham.json'
        );

        // Lấy danh sách loại sản phẩm duy nhất
        const cacLoai = [
            ...new Set(
                danhSachSanPham.map(
                    (sanPham) => sanPham.loai
                )
            )
        ];

        cacLoai.forEach((loai) => {
            const option =
                document.createElement('option');

            option.value = loai;
            option.textContent = loai;

            locLoai.append(option);
        });

        hienThiDanhSach();

    } catch (error) {
        console.error(
            'Không thể tải danh sách sản phẩm:',
            error
        );

        thongBao.textContent =
            'Không tải được dữ liệu sản phẩm. ';

        const nutThuLai =
            taoPhanTu(
                'button',
                'Thử lại'
            );

        nutThuLai.type = 'button';

        nutThuLai.addEventListener(
            'click',
            () => {
                location.reload();
            }
        );

        thongBao.append(nutThuLai);
    }
}

taiDanhSachSanPham();
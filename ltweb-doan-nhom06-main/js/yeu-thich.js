/*
 * Quản lý danh sách sản phẩm yêu thích bằng localStorage.
 * Dữ liệu lưu trong localStorage chỉ gồm id của sản phẩm.
 * Danh sách vẫn tồn tại sau khi tải lại trang.
 * Khi dữ liệu thay đổi, phát sự kiện để cập nhật số lượng.
 */

const KHOA_YEU_THICH =
    'jadehubYeuThich';


// ==============================
// ĐỌC DANH SÁCH YÊU THÍCH
// ==============================

export function docYeuThich() {
    try {
        const duLieu =
            localStorage.getItem(
                KHOA_YEU_THICH
            );


        if (duLieu === null) {
            return [];
        }


        const danhSach =
            JSON.parse(duLieu);


        if (!Array.isArray(danhSach)) {
            return [];
        }


        /*
         * Chỉ giữ các id số hợp lệ,
         * tránh dữ liệu localStorage bị sai.
         */
        return danhSach
            .map((id) => Number(id))
            .filter(
                (id) =>
                    Number.isInteger(id) &&
                    id > 0
            );


    } catch (error) {
        console.warn(
            'Không thể đọc danh sách yêu thích:',
            error
        );

        return [];
    }
}


// ==============================
// GHI DANH SÁCH YÊU THÍCH
// ==============================

export function ghiYeuThich(
    danhSach
) {
    try {
        localStorage.setItem(
            KHOA_YEU_THICH,
            JSON.stringify(
                danhSach
            )
        );


        /*
         * Báo cho main.js biết dữ liệu
         * yêu thích vừa thay đổi.
         */
        window.dispatchEvent(
            new CustomEvent(
                'yeuthich-thaydoi'
            )
        );


        return true;


    } catch (error) {
        console.warn(
            'Không thể lưu danh sách yêu thích:',
            error
        );

        return false;
    }
}


// ==============================
// KIỂM TRA SẢN PHẨM YÊU THÍCH
// ==============================

export function coYeuThich(id) {
    const maSanPham =
        Number(id);


    if (!Number.isInteger(maSanPham)) {
        return false;
    }


    return docYeuThich().includes(
        maSanPham
    );
}


// ==============================
// THÊM / BỎ YÊU THÍCH
// ==============================

export function daoYeuThich(id) {
    const maSanPham =
        Number(id);


    if (!Number.isInteger(maSanPham)) {
        return false;
    }


    const danhSach =
        docYeuThich();


    const dangYeuThich =
        danhSach.includes(
            maSanPham
        );


    const danhSachMoi =
        dangYeuThich
            ? danhSach.filter(
                (ma) =>
                    ma !== maSanPham
            )
            : [
                ...danhSach,
                maSanPham
            ];


    const daLuu =
        ghiYeuThich(
            danhSachMoi
        );


    /*
     * Nếu localStorage không ghi được,
     * trả lại trạng thái ban đầu.
     */
    if (!daLuu) {
        return dangYeuThich;
    }


    return !dangYeuThich;
}
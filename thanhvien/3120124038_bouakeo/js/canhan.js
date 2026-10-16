/*
 * JavaScript cho trang cá nhân Bouakeo.
 * Tương tác 1: lọc và làm nổi bật kỹ năng theo từ khóa người dùng nhập.
 * Tương tác 2: ẩn hoặc hiện ảnh chân dung bằng nút bấm.
 * Cách thử: nhập từ khóa kỹ năng và nhấn nút Ẩn/hiện ảnh chân dung.
 */

const main = document.querySelector('main');

if (main) {
    const bar = document.createElement('div');
    bar.className = 'tuong-tac-ca-nhan';


    // ==============================
    // 1. TÌM / LỌC KỸ NĂNG
    // ==============================

    const nhanTimKiem =
        document.createElement('label');

    nhanTimKiem.textContent =
        'Tìm kỹ năng: ';

    const oTimKiem =
        document.createElement('input');

    oTimKiem.type = 'search';
    oTimKiem.placeholder =
        'Ví dụ: Python, AI, SQL...';

    oTimKiem.setAttribute(
        'aria-label',
        'Tìm kỹ năng'
    );

    nhanTimKiem.append(oTimKiem);


    const thongBao =
        document.createElement('span');

    thongBao.setAttribute(
        'aria-live',
        'polite'
    );


    /*
     * Lấy danh sách kỹ năng trong mục
     * "Định hướng chuyên môn và kỹ năng".
     */
    const cacTieuDe = [
        ...main.querySelectorAll('h2')
    ];

    const tieuDeKyNang =
        cacTieuDe.find((tieuDe) =>
            tieuDe.textContent
                .toLowerCase()
                .includes('kỹ năng')
        );

    let danhSachKyNang = [];

    if (tieuDeKyNang) {
        let phanTu =
            tieuDeKyNang.nextElementSibling;

        while (phanTu) {
            if (
                phanTu.tagName === 'H2' ||
                phanTu.tagName === 'SECTION'
            ) {
                break;
            }

            if (phanTu.tagName === 'UL') {
                danhSachKyNang = [
                    ...phanTu.querySelectorAll('li')
                ];

                break;
            }

            phanTu =
                phanTu.nextElementSibling;
        }
    }


    function boDau(chuoi) {
        return chuoi
            .normalize('NFD')
            .replace(
                /[\u0300-\u036f]/g,
                ''
            )
            .replace(/đ/g, 'd')
            .replace(/Đ/g, 'D')
            .toLowerCase();
    }


    function locKyNang() {
        const tuKhoa =
            boDau(
                oTimKiem.value.trim()
            );

        let soKetQua = 0;

        danhSachKyNang.forEach(
            (mucKyNang) => {
                const noiDung =
                    boDau(
                        mucKyNang.textContent
                    );

                const phuHop =
                    !tuKhoa ||
                    noiDung.includes(
                        tuKhoa
                    );

                mucKyNang.hidden =
                    !phuHop;

                mucKyNang.classList.toggle(
                    'ky-nang-phu-hop',
                    Boolean(
                        tuKhoa &&
                        phuHop
                    )
                );

                if (phuHop) {
                    soKetQua += 1;
                }
            }
        );


        if (!tuKhoa) {
            thongBao.textContent = '';
            return;
        }


        thongBao.textContent =
            soKetQua > 0
                ? ` Tìm thấy ${soKetQua} kỹ năng phù hợp.`
                : ' Không tìm thấy kỹ năng phù hợp.';
    }


    oTimKiem.addEventListener(
        'input',
        locKyNang
    );


    // ==============================
    // 2. ẨN / HIỆN ẢNH CHÂN DUNG
    // ==============================

    const nutAnh =
        document.createElement('button');

    nutAnh.type = 'button';
    nutAnh.textContent =
        'Ẩn ảnh chân dung';

    nutAnh.setAttribute(
        'aria-pressed',
        'false'
    );


    const anhChanDung =
        main.querySelector('img');


    if (!anhChanDung) {
        nutAnh.disabled = true;

        nutAnh.textContent =
            'Không có ảnh chân dung';
    }


    nutAnh.addEventListener(
        'click',
        () => {
            if (!anhChanDung) {
                return;
            }


            anhChanDung.hidden =
                !anhChanDung.hidden;


            nutAnh.setAttribute(
                'aria-pressed',
                String(
                    anhChanDung.hidden
                )
            );


            nutAnh.textContent =
                anhChanDung.hidden
                    ? 'Hiện ảnh chân dung'
                    : 'Ẩn ảnh chân dung';
        }
    );


    // ==============================
    // ĐƯA CÁC TƯƠNG TÁC VÀO TRANG
    // ==============================

    bar.append(
        nhanTimKiem,
        thongBao,
        nutAnh
    );

    main.insertBefore(
        bar,
        main.firstChild
    );
}
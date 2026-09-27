/*
 * JavaScript cho trang cá nhân Cẩm Thanh.
 * Tương tác 1: chuyển chế độ sáng/tối và lưu bằng localStorage.
 * Tương tác 2: sao chép địa chỉ email và hiển thị trạng thái.
 * Cách thử: nhấn Đổi sáng/tối và nhấn Sao chép email.
 */

const main = document.querySelector('main');

if (main) {
    const bar = document.createElement('div');
    bar.className = 'tuong-tac-ca-nhan';

    const nutGiaoDien =
        document.createElement('button');

    nutGiaoDien.type = 'button';
    nutGiaoDien.textContent =
        'Đổi sáng / tối';

    const nutSaoChep =
        document.createElement('button');

    nutSaoChep.type = 'button';
    nutSaoChep.textContent =
        'Sao chép email';

    const thongBao =
        document.createElement('span');

    thongBao.setAttribute(
        'aria-live',
        'polite'
    );

    bar.append(
        nutGiaoDien,
        nutSaoChep,
        thongBao
    );

    main.insertBefore(
        bar,
        main.firstChild
    );


    // ==============================
    // 1. CHẾ ĐỘ SÁNG / TỐI
    // ==============================

    const giaoDienDaLuu =
        localStorage.getItem('thanhTheme');

    if (giaoDienDaLuu === 'toi') {
        document.body.classList.add(
            'che-do-toi'
        );
    }

    nutGiaoDien.addEventListener(
        'click',
        () => {
            document.body.classList.toggle(
                'che-do-toi'
            );

            const dangToi =
                document.body.classList.contains(
                    'che-do-toi'
                );

            localStorage.setItem(
                'thanhTheme',
                dangToi ? 'toi' : 'sang'
            );
        }
    );


    // ==============================
    // 2. SAO CHÉP EMAIL
    // ==============================

    nutSaoChep.addEventListener(
        'click',
        async () => {
            const ketQua =
                document.body.textContent.match(
                    /[\w.+-]+@[\w.-]+\.[A-Za-z]{2,}/
                );

            if (!ketQua) {
                thongBao.textContent =
                    'Không tìm thấy email trên trang.';
                return;
            }

            const email = ketQua[0];

            try {
                await navigator.clipboard.writeText(
                    email
                );

                thongBao.textContent =
                    'Đã sao chép email.';
            } catch {
                thongBao.textContent =
                    `Email: ${email}`;
            }
        }
    );
}
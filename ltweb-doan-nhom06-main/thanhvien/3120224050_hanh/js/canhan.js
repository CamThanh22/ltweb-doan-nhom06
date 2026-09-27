/*
 * JavaScript cho trang cá nhân Bích Hạnh.
 * Tương tác 1: tìm nhanh nội dung trong trang.
 * Tương tác 2: thu gọn hoặc mở từng phần nội dung.
 * Cách thử: nhập từ khóa tìm kiếm và nhấn nút Thu gọn/Mở mục.
 */

const main = document.querySelector('main');

if (main) {
    const bar = document.createElement('div');
    bar.className = 'tuong-tac-ca-nhan';


    // ==============================
    // 1. TÌM KIẾM NỘI DUNG
    // ==============================

    const input = document.createElement('input');

    input.type = 'search';
    input.placeholder = 'Tìm trong trang…';
    input.setAttribute(
        'aria-label',
        'Tìm nội dung'
    );

    bar.append(input);
    main.insertBefore(bar, main.firstChild);

    const sections = [
        ...main.querySelectorAll('section')
    ];

    input.addEventListener('input', () => {
        const tuKhoa =
            input.value.toLowerCase().trim();

        sections.forEach((section) => {
            const noiDung =
                section.textContent.toLowerCase();

            section.hidden =
                Boolean(
                    tuKhoa &&
                    !noiDung.includes(tuKhoa)
                );
        });
    });


    // ==============================
    // 2. THU GỌN / MỞ TỪNG MỤC
    // ==============================

    sections.forEach((section) => {
        const tieuDe =
            section.querySelector('h2, h3');

        if (!tieuDe) {
            return;
        }

        const nutThuGon =
            document.createElement('button');

        nutThuGon.type = 'button';
        nutThuGon.textContent = 'Thu gọn mục';

        nutThuGon.setAttribute(
            'aria-expanded',
            'true'
        );

        tieuDe.insertAdjacentElement(
            'afterend',
            nutThuGon
        );

        nutThuGon.addEventListener(
            'click',
            () => {
                const dangMo =
                    nutThuGon.getAttribute(
                        'aria-expanded'
                    ) === 'true';

                [...section.children]
                    .filter(
                        (phanTu) =>
                            phanTu !== tieuDe &&
                            phanTu !== nutThuGon
                    )
                    .forEach((phanTu) => {
                        phanTu.hidden = dangMo;
                    });

                nutThuGon.setAttribute(
                    'aria-expanded',
                    String(!dangMo)
                );

                nutThuGon.textContent =
                    dangMo
                        ? 'Mở mục'
                        : 'Thu gọn mục';
            }
        );
    });
}
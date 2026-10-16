/*
 * JavaScript cho trang cá nhân Cẩm Thanh.
 * Tương tác: sao chép địa chỉ email và hiển thị trạng thái (addEventListener + thay đổi DOM).
 * Giao diện sáng/tối nay do máy chủ PHP xử lý bằng cookie (xem gioithieu.php) nên không còn ở đây.
 * Cách thử: nhấn nút Sao chép email.
 */

const main = document.querySelector('main');

if (main) {
    const bar = document.createElement('div');
    bar.className = 'tuong-tac-ca-nhan';

    const nutSaoChep = document.createElement('button');
    nutSaoChep.type = 'button';
    nutSaoChep.textContent = 'Sao chép email';

    const thongBao = document.createElement('span');
    thongBao.setAttribute('aria-live', 'polite');

    bar.append(nutSaoChep, thongBao);
    main.insertBefore(bar, main.firstChild);

    nutSaoChep.addEventListener('click', async () => {
        const ketQua = document.body.textContent.match(/[\w.+-]+@[\w.-]+\.[A-Za-z]{2,}/);

        if (!ketQua) {
            thongBao.textContent = 'Không tìm thấy email trên trang.';
            return;
        }

        const email = ketQua[0];

        try {
            await navigator.clipboard.writeText(email);
            thongBao.textContent = 'Đã sao chép email.';
        } catch {
            thongBao.textContent = `Email: ${email}`;
        }
    });
}

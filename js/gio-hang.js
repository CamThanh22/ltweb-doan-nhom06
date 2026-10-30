/*
 * js/gio-hang.js - Thêm sản phẩm vào giỏ không tải lại trang bằng fetch tới api/gio-hang.php (slide 52).
 * Cập nhật ngay số món trên header (#so-mon) và hiển thị thông báo ở #thong-bao bằng textContent.
 * Nếu mạng lỗi hoặc tắt JavaScript, biểu mẫu form.them-gio vẫn gửi POST truyền thống tới gio-hang.php.
 * Cách thử: mở chi-tiet.php?id=1, bấm "Thêm vào giỏ" -> số món trên header tăng ngay, trang không tải lại.
 */

function baoTin(noiDung, laLoi = false) {
    const o = document.querySelector('#thong-bao');
    if (!o) {
        return;
    }
    o.textContent = noiDung;
    o.className = laLoi ? 'thong-bao loi' : 'thong-bao thanh-cong';
}

document.addEventListener('submit', async (e) => {
    const form = e.target.closest('form.them-gio');
    if (!form) {
        return;
    }
    e.preventDefault();
    const du = new FormData(form);
    try {
        const res = await fetch('api/gio-hang.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id: Number(du.get('id')),
                sl: Number(du.get('sl')),
            }),
        });
        const kq = await res.json();
        if (res.ok) {
            const dem = document.querySelector('#so-mon');
            if (dem) {
                dem.textContent = String(kq.so_mon);
            }
            baoTin('Đã thêm sản phẩm vào giỏ hàng!', false);
        } else {
            baoTin(kq.loi || 'Không thể thêm vào giỏ hàng.', true);
        }
    } catch {
        form.submit();
    }
});

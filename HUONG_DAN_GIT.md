# Quy trình Git cho Bài tập nhóm 5 (nhóm trưởng làm bước 1–3, mọi thành viên làm bước 4)

1. Gắn thẻ bản đã nộp Bài 4 (commit đã ghi trong báo cáo Bài 4 là `cd7e906`; kiểm tra bằng `git log --oneline`):
   ```bash
   git tag btn4 cd7e906
   git push origin btn4
   ```
2. Đổi đuôi các trang bằng `git mv` (giữ lịch sử), rồi chép đè mã PHP mới vào:
   ```bash
   git mv index.html index.php
   git mv danh-sach.html danh-sach.php
   git mv chi-tiet.html chi-tiet.php
   git mv gioi-thieu.html gioi-thieu.php
   git mv lien-he.html lien-he.php
   for d in thanhvien/*/; do git mv "${d}gioithieu.html" "${d}gioithieu.php"; done
   git rm danh-sach-bootstrap.html chi-tiet-vong-tay.html chi-tiet-chuoi-hat.html chi-tiet-day-chuyen.html
   git rm js/trang-danh-sach.js js/trang-chi-tiet.js
   ```
   Sau đó chép các tệp mới/ghi đè từ gói mã (inc/, src/, *.php, css/style.css, js/*.js, README.md, composer.*, .gitignore, tests/...).
3. `composer install`, chạy thử (`php -S localhost:8000 router.php`), `git add -A`, commit với thông điệp rõ nghĩa, `git push`.
4. **Mỗi thành viên** tự commit phần của mình từ tài khoản GitHub của mình (thư mục trang cá nhân + chức năng Phần A phụ trách), ví dụ:
   `git commit -m "Them so luu but va bo dem luot xem cho trang ca nhan cua Hanh"`.
   Lịch sử commit là minh chứng đóng góp; số commit từng người lấy bằng `git shortlog -sn --all`.
5. Sau khi chạy kiểm thử thật: bỏ ảnh chụp vào `kiemtra/`, điền Bảng 3, 4, 5 trong báo cáo, ghi **mã commit cuối** (`git rev-parse --short HEAD`) lên trang bìa.
6. Nộp: `LTW_BTN5_Nhom06_code.zip` (nén cả kho trừ `.git` và `vendor/`) và `Nhom06_Baitap5.pdf`.

Lưu ý: trang cá nhân của Bích Hạnh trước đây còn dấu xung đột merge (`=======`, `>>>>>>>`). Bản `gioithieu.php` mới đã giữ **một** bản nội dung (bản đầu, phía HEAD); Hạnh cần mở lại để xác nhận đúng nội dung mình muốn giữ.

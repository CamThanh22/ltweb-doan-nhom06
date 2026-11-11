# JadeHub · Hướng dẫn XAMPP

Giải nén dự án vào `C:\xampp\htdocs\`. Bật Apache và MySQL trong XAMPP.

## Đang có website và dữ liệu

1. Xuất bản sao cơ sở dữ liệu trong phpMyAdmin và sao lưu thư mục dự án.
2. Trong phpMyAdmin, bấm vào cơ sở dữ liệu JadeHub đang dùng (mặc định là `ltweb_nhom06`), vào **Nhập** và chọn `database/02-cap-nhat.sql`.
3. Chép các tệp bản mới vào thư mục dự án hiện tại. Dùng lại tệp `.env` đang chạy. Mở website và nhấn Ctrl + Shift + R.

Tệp cập nhật dùng cho cơ sở dữ liệu JadeHub đã có các bảng nền như `nguoi_dung`, `san_pham`, `don_hang`. Tệp bổ sung các bảng/cột còn thiếu và đồng bộ hồ sơ 34 ngọc mẫu theo mã. Giá, tồn kho, đơn hàng và tài khoản hiện có được giữ nguyên. Chỉ nhập tệp 02 trong trường hợp này.

## Cài mới

1. Trong phpMyAdmin, tạo cơ sở dữ liệu **trống** tên `ltweb_nhom06` với đối chiếu `utf8mb4_unicode_ci`. Nếu tên này đã có dữ liệu, tạo tên khác và điền đúng tên vào `DB_DATABASE`.
2. Chọn cơ sở dữ liệu vừa tạo, vào **Nhập** và chọn `database/01-cai-moi.sql`. Tệp gồm cấu trúc đầy đủ và dữ liệu mẫu.
3. Sao chép `.env.example` thành `.env` và điền thông tin MySQL. Chạy `composer install` ở thư mục dự án nếu có Composer.
4. Mở `http://localhost/ltweb-doan-nhom06/` hoặc đổi phần tên thư mục cho khớp với thư mục trong htdocs.

Mật khẩu dữ liệu mẫu là `matkhau123`. Các phiếu DEMO-JADE dùng để minh họa đồ án.

## Các thư mục của dự án

| Thư mục | Chức năng |
|---|---|
| `pages/` | Các trang PHP, chia theo cửa hàng, tài khoản, cộng đồng, người bán, quản trị, thông tin và trang lỗi. |
| `images/` | Logo, ảnh ngọc và ảnh phiếu hồ sơ đi kèm dự án. |
| `css/`, `js/` | Kiểu trình bày và tương tác trên trình duyệt. |
| `database/` | Hai tệp SQL cài đặt, cập nhật và sơ đồ ERD. |
| `inc/`, `src/` | Thành phần PHP dùng chung và lớp xử lý nghiệp vụ. |
| `api/` | Các điểm truy cập dữ liệu dùng bởi giao diện. |
| `thanhvien/` | Các trang thành viên của nhóm đồ án. |
| `uploads/`, `logs/` | Ảnh tải lên và nhật ký của bản đang chạy. |
| `tests/`, `kiemtra/` | Tệp phục vụ kiểm tra dự án. |

Địa chỉ cũ như `chi-tiet.php?id=1` đi qua `index.php` tới tệp trong `pages/`. XAMPP cần bật Apache `mod_rewrite` và cho phép dùng `.htaccess`. Khi chuyển sang cấu trúc này, giải nén bản mới vào thư mục riêng trong htdocs, sao chép `.env` và dữ liệu trong `uploads/` từ bản đang chạy, rồi mở website bằng tên thư mục mới. Nếu cơ sở dữ liệu đã được cập nhật cho bản trước, việc sắp xếp thư mục không yêu cầu nhập thêm SQL.

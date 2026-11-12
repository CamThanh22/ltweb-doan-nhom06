# JadeHub – Sàn giao dịch và thẩm định ngọc quý (Nhóm 06)

Bài tập thực hành nhóm số 6 – Chương 6: **PHP và cơ sở dữ liệu MySQL (PDO)**.
Học phần Thiết kế và Lập trình Web – Khoa Toán – Tin, Trường Đại học Sư phạm – Đại học Đà Nẵng.

- Bản nộp của Bài tập nhóm số 5 được gắn thẻ Git `btn5`.
- Toàn bộ dữ liệu sản phẩm, danh mục, người dùng, đơn hàng, chi tiết đơn, liên hệ và dữ liệu trang cá nhân của 5 thành viên được lưu trong CSDL MySQL `ltweb_nhom06` (`utf8mb4_unicode_ci`).

## Cài đặt và chạy
Cần: PHP ≥ 8.1 (bật `pdo_mysql`, `mbstring`, `fileinfo`), MySQL hoặc MariaDB (XAMPP / Laragon), Composer.

1. Cài đặt autoload PSR-4 của Composer:
   ```bash
   composer install
   ```
2. Tạo CSDL `ltweb_nhom06` (xóa và tạo lại từ đầu): mở **phpMyAdmin** → **Import** `database/schema.sql`, rồi **Import** `database/seed.sql`; hoặc chạy lệnh trong Git Bash / Terminal (`mysql` của XAMPP ở `C:\xampp\mysql\bin`):
   ```bash
   mysql -u root < database/schema.sql
   mysql -u root ltweb_nhom06 < database/seed.sql
   ```
3. Chạy máy chủ tích hợp của PHP tại thư mục gốc của kho:
   ```bash
   php -S localhost:8000
   # hoặc kèm router chặn truy cập trực tiếp database/, inc/, src/, logs/, storage/:
   php -S localhost:8000 router.php
   ```
   Sau đó mở `http://localhost:8000` (hoặc chép thư mục vào `htdocs` của XAMPP và mở `http://localhost/ltweb-doan-nhom06/`).

## Tài khoản thử (mật khẩu chung: `matkhau123`)
| Email đăng nhập | Vai trò (`vai_tro`) | Ghi chú |
|---|---|---|
| `admin@ued.vn` | `quan_tri` | Quản trị viên — vào được `quan-tri.php`, thêm/sửa/xóa sản phẩm, xem mọi đơn hàng và đổi trạng thái |
| `lan@ued.vn` | `khach_hang` | Khách hàng thử 1 — đã có đơn hàng `#1` và `#3` |
| `an@ued.vn` | `khach_hang` | Khách hàng thử 2 — đã có đơn hàng `#2` |
| `thanh@ued.vn` | `quan_tri` | Chủ trang cá nhân Trần Thị Cẩm Thanh (`3120224133`) |
| `hanh@ued.vn` | `khach_hang` | Chủ trang cá nhân Nguyễn Thị Bích Hạnh (`3120224050`) — có đơn hàng `#4` |
| `hong@ued.vn` | `khach_hang` | Chủ trang cá nhân Nguyễn Nhật Ánh Hồng (`3120224063`) |
| `trinh@ued.vn` | `khach_hang` | Chủ trang cá nhân Huỳnh Trần Phương Trinh (`3120124031`) |
| `bouakeo@ued.vn` | `khach_hang` | Chủ trang cá nhân Ladisa Bouakeo (`3120124038`) |

## Chức năng – URL – Tệp PHP (Bảng 1)
| TT | Chức năng | URL chính | Tệp PHP | Người phụ trách |
|---|---|---|---|---|
| 1 | Đọc dữ liệu từ MySQL: trang chủ (mục mới, đã xem gần đây), chi tiết kèm tên danh mục & 4 mục cùng danh mục, giỏ hàng (`WHERE id IN (...)`), liên hệ lưu bảng `lien_he` | `/index.php`, `/chi-tiet.php?id=1`, `/gio-hang.php`, `/lien-he.php` | `index.php`, `chi-tiet.php`, `gio-hang.php`, `lien-he.php`, `inc/db.php`, `src/Data/KhoSanPham.php`, `src/Data/KhoLienHe.php` | Trần Thị Cẩm Thanh |
| 2 | Tìm kiếm theo từ khóa (`LIKE` + `addcslashes`), lọc danh mục và giá tối đa, sắp xếp theo danh sách cho phép, phân trang (`COUNT(*)` + `LIMIT ? OFFSET ?`), giữ bộ lọc | `/danh-sach.php?q=nhan&dm=1&gia=20000000&sx=gia-tang&trang=1` | `danh-sach.php`, `src/Data/KhoSanPham.php` | Nguyễn Thị Bích Hạnh |
| 3 | Quản trị sản phẩm ngọc quý: danh sách có phân trang, thêm, sửa, xóa bằng POST (bắt lỗi `1062`, `1451`), tải ảnh đại diện, xem liên hệ đã nhận | `/quan-tri.php`, `/them-san-pham.php`, `/sua-san-pham.php?id=1`, `/xoa-san-pham.php` | `quan-tri.php`, `them-san-pham.php`, `sua-san-pham.php`, `xoa-san-pham.php`, `inc/kiem-tra.php` | Nguyễn Nhật Ánh Hồng |
| 4 | Đăng ký, đăng nhập, đăng xuất với bảng `nguoi_dung` (`password_hash`/`verify`, bắt lỗi `1062`, `session_regenerate_id`), ghi nhớ đăng nhập, phân quyền `403.php` | `/dang-ky.php`, `/dang-nhap.php`, `/dang-xuat.php` | `dang-ky.php`, `dang-nhap.php`, `dang-xuat.php`, `inc/xac-thuc.php`, `403.php` | Huỳnh Trần Phương Trinh |
| 5 | Đặt hàng từ giỏ trong giao dịch (`beginTransaction`/`commit`/`rollBack`, `UPDATE ... WHERE ton_kho >= ?`), xem "Đơn hàng của tôi", quản trị đổi trạng thái đơn | `/dat-hang.php`, `/don-hang.php`, `/don-hang.php?id=1` | `dat-hang.php`, `don-hang.php`, `inc/don-hang.php` | Ladisa Bouakeo |
| 6 | REST API trả JSON và giao diện `fetch`: tìm kiếm tức thời (debounce 300ms) ở `danh-sach.php`, thêm vào giỏ không tải lại trang ở `chi-tiet.php` | `/api/san-pham.php`, `/api/gio-hang.php` | `api/san-pham.php`, `api/gio-hang.php`, `js/tim-kiem.js`, `js/gio-hang.js`, `api.http` | Trần Thị Cẩm Thanh & Nguyễn Thị Bích Hạnh |

## Bảng các Endpoint của REST API
| Phương thức & URL | Ý nghĩa | Mã trạng thái & Phản hồi JSON |
|---|---|---|
| `GET /api/san-pham.php?q=nhan` | Tìm kiếm sản phẩm theo từ khóa (tối đa 8 mục) | `200 OK` `{"du_lieu": [...]}` |
| `GET /api/san-pham.php?id=1` | Xem thông tin công khai của một sản phẩm | `200 OK` `{"id": 1, ...}` · `400` (id sai kiểu) · `404` `{"loi": "..."}` |
| `GET /api/gio-hang.php` | Xem giỏ hàng hiện tại trong session | `200 OK` `{"gio": {...}, "so_mon": ...}` |
| `POST /api/gio-hang.php` | Thêm vào giỏ, thân JSON `{"id": 1, "sl": 2}` | `200 OK` `{"so_mon": ...}` · `400` (JSON hỏng) · `404` (id không có) · `422` (sl âm / vượt tồn kho) · `405` (sai phương thức, kèm header `Allow: GET, POST`) |

## Kiểm thử
- Mở `api.http` bằng VS Code (extension **REST Client**) để chạy kiểm thử trực tiếp các nhánh thành công và lỗi của REST API.
- Chạy kiểm thử tự động dòng lệnh:
  ```bash
  php tests/kiem-thu.php
  bash tests/kiem-thu-http.sh http://localhost:8000
  ```

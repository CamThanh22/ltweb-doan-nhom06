# JadeHub – Sàn giao dịch và thẩm định ngọc quý (Nhóm 06)

Bài tập nhóm số 5 – Chương 5: website động bằng **PHP thuần** (không framework, không CMS).
Học phần Thiết kế và Lập trình Web – Khoa Toán – Tin, Trường ĐH Sư phạm – ĐHĐN.

- Bản tĩnh của Bài tập nhóm 4 được giữ ở thẻ Git `btn4`.
- Dữ liệu sản phẩm vẫn lấy từ `data/san-pham.json` (Chương 6 sẽ chuyển sang MySQL, chỉ cần viết lại lớp `src/Data/KhoSanPham.php` và `KhoLienHe.php`).

## Yêu cầu môi trường
- PHP >= 8.1 với các extension mặc định: `mbstring`, `fileinfo` (upload), `json`; `intl` không bắt buộc.
- Composer (getcomposer.org).

## Cài đặt và chạy
```bash
composer install                  # tạo vendor/ (autoload PSR-4: App\ -> src/)
php -S localhost:8000             # chạy tại thư mục gốc của kho
# hoặc, nên dùng khi chạy bằng php -S vì máy chủ này bỏ qua .htaccess, chặn truy cập storage/, logs/, inc/...:
php -S localhost:8000 router.php
```
Mở http://localhost:8000/. Có thể chép cả thư mục vào `htdocs` của XAMPP và mở
http://localhost/ltweb-doan-nhom06/ (mọi đường dẫn đều tương đối; `.htaccess` trong `storage/` và `logs/` chặn truy cập qua URL).

> Nếu quên `composer install`, `inc/config.php` vẫn tự nạp lớp `App\` theo PSR-4 nên site vẫn chạy.
> Thư mục `storage/`, `logs/`, `uploads/` cần ghi được; dữ liệu phát sinh khi chạy không được commit (`.gitignore`).

## Tài khoản thử trang quản trị
| Tên đăng nhập | Mật khẩu |
|---|---|
| `admin` | `Jade@2026` |
| `giangvien` | `Quantri@2026` |

Chỉ dùng cho website của nhóm; mật khẩu lưu dạng băm bcrypt trong `inc/tai-khoan.php`.

## Chức năng – URL – Tệp PHP
| Chức năng | URL | Tệp PHP chính |
|---|---|---|
| Khung trang dùng chung, 404, 500 | mọi trang | `inc/config.php`, `inc/header.php`, `inc/footer.php`, `404.php`, `500.php` |
| Danh sách: tìm, lọc, sắp xếp | `/danh-sach.php?q=nhan&dm=phi-thuy&sx=gia-tang` | `danh-sach.php`, `src/Data/KhoSanPham.php` |
| Chi tiết, đã xem gần đây | `/chi-tiet.php?id=1`, khối ở `/index.php` | `chi-tiet.php`, `index.php`, `src/Services/DaXem.php` |
| Liên hệ (kiểm tra, upload, lưu) | `/lien-he.php` | `lien-he.php`, `src/Data/KhoLienHe.php`, `src/Services/TaiAnh.php` |
| Giỏ hàng (session) | `/gio-hang.php` | `gio-hang.php`, `src/Services/GioHang.php` |
| Đăng nhập, quản trị, đăng xuất | `/dang-nhap.php`, `/quan-tri.php`, `/dang-xuat.php` | `dang-nhap.php`, `quan-tri.php`, `dang-xuat.php`, `inc/bao-ve.php`, `inc/tai-khoan.php` |
| Giới thiệu nhóm | `/gioi-thieu.php` | `gioi-thieu.php` |
| Trang cá nhân + 2 chức năng PHP mỗi người | `/thanhvien/<MSSV>_<ten>/gioithieu.php` | xem bảng dưới |

| Thành viên | Hai chức năng PHP trên trang cá nhân |
|---|---|
| Trần Thị Cẩm Thanh (3120224133) | Giao diện sáng/tối bằng cookie; máy tính điểm học phần |
| Nguyễn Thị Bích Hạnh (3120224050) | Sổ lưu bút (jsonl, PRG); bộ đếm lượt xem mỗi phiên một lần |
| Nguyễn Nhật Ánh Hồng (3120224063) | Ảnh đại diện tải lên an toàn; thời khóa biểu lọc theo thứ |
| Huỳnh Trần Phương Trinh (3120124031) | Kỹ năng lọc theo nhóm; việc cần làm lưu trong session |
| Ladisa Bouakeo (3120124038) | Nhớ tên khách bằng cookie; đánh giá hồ sơ 1–5 sao |

## Kiểm thử
```bash
php tests/kiem-thu.php                                   # kiểm thử lớp và hàm, không cần web server
php -S localhost:8000 router.php &                       # chạy site ở một cửa sổ khác
bash tests/kiem-thu-http.sh http://localhost:8000        # các ca của Bảng 3 bằng curl, ghi kiemtra/ket-qua-curl.txt
```
Ca 12 (MOI_TRUONG = 'prod' + đổi tên tệp JSON) làm tay: xem hướng dẫn trong báo cáo.

## Cấu trúc
```
inc/    config.php ham.php header.php footer.php thong-bao.php bao-ve.php tai-khoan.php
src/    Models/SanPham.php  Data/{KhoSanPham,KhoJsonl,KhoLienHe}.php  Services/{GioHang,DaXem,TaiAnh,BoDem}.php
data/   san-pham.json (từ Bài tập 4)
storage/ logs/ uploads/   dữ liệu phát sinh khi chạy (không commit)
css/ js/ images/ kiemtra/ thanhvien/
```

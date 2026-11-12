# JadeHub · Bản rà soát Bài 7 ngày 13/11/2026

## Cập nhật từ Nhom06_JadeHub_XAMPP_CapNhat.zip đang chạy

Bản này bổ sung hủy đơn, hoàn tiền, đánh giá giao dịch và hồ sơ cộng đồng. **Cần cập nhật SQL một lần.**

1. Sao lưu cơ sở dữ liệu và thư mục website hiện tại.
2. Trong phpMyAdmin, chọn đúng cơ sở dữ liệu JadeHub đang dùng, vào **Nhập** và chọn `database/cap-nhat-huy-hoan-danh-gia.sql`. Dùng tài khoản quản trị MySQL để cập nhật cấu trúc. Không nhập tệp cài mới vào dữ liệu đang sử dụng.
3. Chép đè mã nguồn trong ZIP vào thư mục website; giữ nguyên `.env` và ảnh trong `uploads/` trên máy. ZIP không chứa `.env`.
4. Mở lại URL cũ, nhấn Ctrl+Shift+R. Đã kèm `vendor/` và `composer.lock`.

### Các chức năng bổ sung

- Người mua hủy đơn trước khi giao hàng, nhập lý do; tồn kho được hoàn lại đúng một lần. Đơn đang giao cần liên hệ shop.
- Đơn đã xác nhận thanh toán chuyển sang chờ hoàn tiền khi bị hủy. Người mua cung cấp tài khoản nhận; quản trị chuyển tiền thực tế rồi ghi mã giao dịch hoàn tiền. **Website không tự chuyển tiền qua ngân hàng.** Khoản chuyển chưa được xác nhận phải đối soát trước; không tự coi lời báo đã chuyển là tiền đã nhận.
- Chỉ giao dịch hoàn tất mới được đánh giá: người mua chấm sản phẩm và shop; người bán đánh giá người mua. Mỗi sản phẩm trong đơn được đánh giá một lần cho mỗi bên. Người được đánh giá có thể phản hồi.
- Hồ sơ công khai hiển thị điểm đánh giá, số người đánh giá, số giao dịch hoàn tất, sản phẩm của shop và nhận xét gắn giao dịch. Điểm số là dữ liệu tham khảo, không phải bảo đảm uy tín tuyệt đối.
- Danh bạ thành viên/shop và các liên kết từ sản phẩm, đơn hàng, cộng đồng, hồ sơ dẫn tới trò chuyện trực tiếp. Thông tin tài khoản nhận hoàn tiền chỉ hiện cho chủ đơn và quản trị.
- Sản phẩm cũ chưa có chủ bán được gán cho shop vận hành JadeHub (tài khoản quản trị đầu tiên). Sản phẩm đăng bán đã duyệt giữ chủ bán thực tế; chủ bán được lưu riêng trên từng dòng đơn hàng.

## Hướng dẫn cho bản cũ hơn hoặc cài mới

Giải nén dự án vào `C:\xampp\htdocs\`. Bật Apache và MySQL trong XAMPP.

## Đang có website và dữ liệu

1. Xuất bản sao cơ sở dữ liệu trong phpMyAdmin và sao lưu thư mục dự án.
2. Chỉ khi nâng từ phiên bản cũ chưa có cấu trúc mở rộng: trong phpMyAdmin, bấm vào cơ sở dữ liệu JadeHub đang dùng (mặc định là `ltweb_nhom06`), vào **Nhập** và chọn `database/cap-nhat-co-so-du-lieu-hien-co.sql`.
3. Chép các tệp bản mới vào thư mục dự án hiện tại. Dùng lại tệp `.env` đang chạy. Mở website và nhấn Ctrl + Shift + R.

Tệp cập nhật dùng cho cơ sở dữ liệu JadeHub đã có các bảng nền như `nguoi_dung`, `san_pham`, `don_hang`. Tệp bổ sung các bảng/cột còn thiếu và đồng bộ hồ sơ 34 ngọc mẫu theo mã. Giá, tồn kho, đơn hàng và tài khoản hiện có được giữ nguyên. Chỉ nhập tệp cập nhật trong trường hợp này.

## Cài mới

1. Trong phpMyAdmin, tạo cơ sở dữ liệu **trống** tên `ltweb_nhom06` với đối chiếu `utf8mb4_unicode_ci`. Nếu tên này đã có dữ liệu, tạo tên khác và điền đúng tên vào `DB_DATABASE`.
2. Chọn cơ sở dữ liệu vừa tạo, vào **Nhập** và chọn `database/cai-moi-cau-truc-va-du-lieu-mau.sql`. Tệp gồm cấu trúc đầy đủ và dữ liệu mẫu.
3. Sao chép `.env.example` thành `.env` và điền thông tin MySQL. Chạy `composer install` ở thư mục dự án.
4. Mở `http://localhost/ltweb-doan-nhom06/` hoặc đổi phần tên thư mục cho khớp với thư mục trong htdocs.

Mật khẩu dữ liệu mẫu là `matkhau123`. Các phiếu DEMO-JADE dùng để minh họa đồ án.

## Các thư mục của dự án

| Thư mục | Chức năng |
|---|---|
| `pages/` | Các trang PHP, chia theo cửa hàng, tài khoản, cộng đồng, người bán, quản trị, thông tin và trang lỗi. |
| `images/` | Logo, ảnh ngọc và ảnh phiếu hồ sơ đi kèm dự án. |
| `css/`, `js/` | Kiểu trình bày và tương tác trên trình duyệt. |
| `database/` | SQL cài đặt, cập nhật cơ sở dữ liệu và sơ đồ ERD. |
| `inc/`, `src/` | Thành phần PHP dùng chung và lớp xử lý nghiệp vụ. |
| `api/` | Các điểm truy cập dữ liệu dùng bởi giao diện. |
| `thanhvien/` | Các trang thành viên của nhóm đồ án. |
| `uploads/`, `logs/` | Ảnh tải lên và nhật ký của bản đang chạy. |
| `tests/`, `kiemtra/` | Tệp phục vụ kiểm tra dự án. |

Địa chỉ cũ như `chi-tiet.php?id=1` đi qua `index.php` tới tệp trong `pages/`. XAMPP cần bật Apache `mod_rewrite` và cho phép dùng `.htaccess`. Khi chuyển sang cấu trúc này, giải nén bản mới vào thư mục riêng trong htdocs, sao chép `.env` và dữ liệu trong `uploads/` từ bản đang chạy, rồi mở website bằng tên thư mục mới. Nếu cơ sở dữ liệu đã được cập nhật cho bản trước, việc sắp xếp thư mục không yêu cầu nhập thêm SQL.

## Kiểm tra trước khi nộp Bài 7

- Cài PHP 8.1 trở lên, bật `pdo_mysql`, `mbstring`, `fileinfo`, `curl`, rồi chạy `composer install`.
- Để dùng tài khoản giới hạn, chạy `database/tai-khoan.sql` với tài khoản quản trị CSDL sau khi thay mật khẩu giữ chỗ. Trong `.env`, đặt `DB_USERNAME=nhom06_app` và điền mật khẩu vừa đặt. Tệp cấp quyền dùng CSDL mặc định `ltweb_nhom06`. Trên hosting dùng HTTPS và đặt `APP_DEBUG=false`.
- Từ thư mục dự án, chạy `php -S localhost:8000 router.php`. Với XAMPP, dùng URL dưới htdocs, bật `mod_rewrite` và `mod_headers`.
- Chạy `bash tests/bao-mat.sh` trong Git Bash. Với XAMPP, dùng `BASE=http://localhost/LTW_BTN6_Nhom06_code SQL_STATUS=403 bash tests/bao-mat.sh` và đổi tên thư mục theo URL thật. Bộ kiểm tra báo PASS/FAIL và trả mã khác 0 khi thất bại.
- Chạy `php tests/tu-kiem-tra.php` để rà form POST, `php tests/kiem-tra-mo-rong.php` để rà các trang mở rộng, `php tests/kiem-thu.php` để kiểm thử với CSDL mẫu, và `composer audit` để kiểm tra thư viện.
- Hoàn thiện PDF `Nhom06_Baitap7.pdf` bằng kết quả thực tế. Bộ tự động chưa thay thế Bảng 3, Bảng 5, phần thảo luận, ảnh DevTools hoặc minh chứng commit cá nhân.

### Chạy gộp kiểm tra XAMPP

Sau khi Apache và MySQL đã chạy, dùng Git Bash tại thư mục dự án:

```bash
bash tests/kiem-tra-xampp.sh
```

Nếu URL hoặc vị trí PHP khác mặc định, đặt biến `BASE` hoặc `PHP_BIN` trước lệnh.
Bộ này kiểm tra môi trường, mã nguồn, HTTP, đăng nhập, quyền MySQL, đặt đơn,
phân quyền, thanh toán, kiểm duyệt và năm trang thành viên bằng cấu hình `.env`.
Bộ tích hợp cần dữ liệu mẫu và các tài khoản thử chưa đổi mật khẩu. Nên chạy trên bản sao
kiểm thử có CSDL riêng: script tạo rồi dọn dữ liệu mẫu, làm tăng bộ đếm AUTO_INCREMENT.
Không chạy đồng thời bộ kiểm thử và thao tác mua hàng thật.
PHP CLI và Apache cần dùng cùng `session.save_path` để ca hết hạn phiên kiểm tra đúng.

Ca cố tình làm sai mật khẩu CSDL được tách riêng, chỉ chạy trên bản sao:
`php tests/loi-cau-hinh.php --isolated`. Script khôi phục `.env` trong finally
và shutdown handler. Không chạy trên hệ thống đang phục vụ người dùng.

Chuyển khoản trong bản này vẫn là đối soát thủ công: gửi mã giao dịch không tự
chuyển đơn thành đã thanh toán; chỉ quản trị xác nhận. Không có kết nối ngân hàng thật.


## Kết quả rà soát bản này

- 141 kiểm tra hồi quy đạt trên PHP built-in và 141 trên Apache ở thư mục con.
- 19 kiểm tra hàm/lớp đạt; 51 kiểm tra cấu trúc/CSRF và 28 kiểm tra mô-đun đạt.
- Kiểm tra Apache: tệp nội bộ, uploads không chạy PHP, header và cookie đều đạt.
- Thử sai mật khẩu MySQL với APP_DEBUG=false: trang trả 500 thân thiện, API trả JSON 500.
- 23 trang đã mở trong Chromium headless: không lỗi JavaScript/tài nguyên nội bộ/CSP ngoài ca cố ý.
- Đã thử thêm giỏ bằng AJAX, CSRF header, menu mobile 390px và cookie HttpOnly; script inline bị CSP chặn.
- Composer audit không có cảnh báo bảo mật; composer.lock khóa các phiên bản đã dùng để thử.
- Xem tests/KET_QUA_KIEM_THU.txt để biết kết quả chạy và giới hạn.

Những lỗi sửa: quyền đổi avatar trang Hồng; GET ghi bộ đếm/đánh dấu đã đọc;
phiên hết hạn tự khôi phục bởi cookie ghi nhớ; giới hạn đăng nhập theo email;
import JavaScript trùng; ảnh liên hệ mẫu bị mất tệp; hủy đơn hoàn kho đúng một lần;
kiểm tra đầu vào giỏ hàng và upload; API lỗi cấu hình trả JSON; định tuyến .htaccess gọn hơn.
Giữ nguyên cơ chế chuyển khoản đối soát thủ công.

Kiểm tra trình duyệt có thể chạy lại với Node + Playwright:
`BASE=http://localhost/LTW_BTN6_Nhom06_code node tests/trinh-duyet.cjs`.
Chuẩn bị bản sao CSDL mẫu, tài khoản admin đã được duyệt người bán để mở dang-ban.php.
Ca thời tiết dùng dữ liệu kiểm thử cố định, không kiểm chứng nhà cung cấp bên ngoài.

Bài 7 còn phần nộp học thuật: tag btn6 đúng commit đã nộp, commit cá nhân của từng
thành viên, báo cáo PDF và trả lời thảo luận. Kết quả tự động không thay thế các phần này.

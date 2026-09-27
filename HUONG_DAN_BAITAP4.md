# Bài tập nhóm 4 – Chương 4 – JadeHub (Nhóm 06)

## Phần B đã cài đặt
1. Menu mobile: `js/main.js` – classList.toggle, aria-expanded, Esc đóng menu.
2. Danh sách động: `js/trang-danh-sach.js` + `data/san-pham.json` (12 mục) – fetch, tìm không dấu, lọc, sắp xếp, trạng thái tải/lỗi/rỗng.
3. Chi tiết theo URL: `chi-tiet.html?id=1..12` + `js/trang-chi-tiet.js` – URLSearchParams, find, đổi document.title; giữ ảnh kiểm định và bảng thông số.
4. Liên hệ: `js/trang-lien-he.js` – Constraint Validation API, lỗi dưới trường, POST JSONPlaceholder, khóa nút lúc gửi.
5. REST API trang chủ: `js/trang-chu.js` – Open-Meteo, thời tiết Đà Nẵng.
6. Yêu thích: `js/yeu-thich.js` – localStorage, JSON.parse/stringify, số đếm trên header.

## Phần C đã cài đặt
- Cẩm Thanh: đổi sáng/tối + sao chép email.
- Bích Hạnh: tìm nội dung + accordion.
- Hồng: đồng hồ thời gian thực + tăng/giảm cỡ chữ.
- Trinh: tiến độ đọc + quay lại đầu trang.
- Bouakeo: lời chào theo thời gian + ẩn/hiện ảnh chân dung.

## Kiểm thử bắt buộc trước khi nộp
- Chạy bằng Live Server/Apache, không mở bằng `file://`.
- Console: không có `Uncaught` ở 5 trang chính.
- Network > Offline: kiểm tra thông báo lỗi ở danh sách, trang chi tiết, trang chủ và form.
- `danh-sach.html`: thử chuỗi không có kết quả để chụp trạng thái rỗng; thử tìm không dấu.
- `chi-tiet.html?id=999`: phải báo “Không tìm thấy sản phẩm”.
- Application > Local Storage: kiểm tra khóa `jadehubYeuThich` còn sau reload.
- Disable JavaScript: menu và nội dung tĩnh chính vẫn đọc được; form vẫn có action/method; noscript ở danh sách giải thích phần động.
- W3C: 0 lỗi HTML; 360px không cuộn ngang.
- Lighthouse Mobile trên GitHub Pages: Accessibility >= 90, Best Practices >= 90.
- Mỗi thành viên tự commit phần C và ít nhất một chức năng Phần B bằng tài khoản GitHub của mình.

## Phần A và số liệu báo cáo
Không điền giả số liệu. Nhóm phải đo trực tiếp website thực tế bằng DevTools: số/tổng KB JS, 3 tệp nặng nhất, 4 tương tác, 2 Event Listeners, 2 Fetch/XHR, storage/cookie, kết quả Disable JavaScript, Console và Lighthouse.

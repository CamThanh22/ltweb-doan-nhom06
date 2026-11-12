-- Chạy bằng tài khoản quản trị sau khi đã nhập cấu trúc CSDL.
-- Thay chuỗi giữ chỗ trước khi chạy. Dùng tài khoản mới dành riêng cho JadeHub.
CREATE USER 'nhom06_app'@'localhost' IDENTIFIED BY 'THAY_BANG_MAT_KHAU_RIENG';
GRANT SELECT, INSERT, UPDATE, DELETE ON ltweb_nhom06.* TO 'nhom06_app'@'localhost';

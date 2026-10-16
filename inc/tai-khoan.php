<?php
/**
 * inc/tai-khoan.php - tài khoản THỬ của trang quản trị; mật khẩu chỉ lưu dạng băm (bcrypt).
 * Trả về mảng: tên đăng nhập => ['hash' => ..., 'hoTen' => ...]. Mật khẩu thật nằm trong README.
 * Tạo băm mới: php -r "echo password_hash('mat-khau-moi', PASSWORD_DEFAULT), PHP_EOL;"
 * Cách thử: đăng nhập ở dang-nhap.php; password_verify() so khớp mật khẩu với chuỗi băm.
 */
return [
    'admin' => [
        'hash'  => '$2y$10$qAbDINNPT.ZI6wbWUMZVme/G1t6uVI8d1p68IOn6LTJRlu4WnBoBO',
        'hoTen' => 'Quản trị JadeHub',
    ],
    'giangvien' => [
        'hash'  => '$2y$10$m1DoqMKd8cUMEp5Q2Hm2RuVatZmRn2MbmzBQTA.NZDxtWU5G0DT4G',
        'hoTen' => 'Giảng viên chấm bài',
    ],
];

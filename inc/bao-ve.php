<?php
/**
 * inc/bao-ve.php - trang cần đăng nhập require tệp này ngay sau inc/config.php.
 * Chưa đăng nhập: chuyển về dang-nhap.php và dừng (exit) để phần dưới không chạy.
 * Cách thử: mở quan-tri.php khi chưa đăng nhập -> bị chuyển về dang-nhap.php.
 */
if (!isset($_SESSION['user'])) {
    header('Location: ' . ($goc ?? '') . 'dang-nhap.php');
    exit;
}
// Trang quản trị chứa dữ liệu riêng: không cho trình duyệt lưu lại (nút Back sau khi đăng xuất)
header('Cache-Control: no-store');

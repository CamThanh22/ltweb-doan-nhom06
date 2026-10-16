<?php
/**
 * dang-xuat.php - Đăng xuất: gỡ người dùng khỏi session, đổi mã phiên, rồi chuyển về trang chủ.
 * Giữ nguyên giỏ hàng của khách (chỉ xóa thông tin đăng nhập). Sau đó quan-tri.php không vào lại được.
 * Cách thử: đăng nhập, bấm Đăng xuất ở header, rồi mở quan-tri.php -> bị chuyển về dang-nhap.php.
 */
require __DIR__ . '/inc/config.php';

unset($_SESSION['user']);
session_regenerate_id(true);
datFlash('ok', 'Bạn đã đăng xuất.');
header('Location: index.php');
exit;

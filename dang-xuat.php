<?php
/**
 * dang-xuat.php - Đăng xuất: xóa mã ghi nhớ đăng nhập, gỡ $_SESSION['uid'], đổi mã phiên,
 * giữ nguyên giỏ hàng của khách rồi chuyển về trang chủ.
 * Cách thử: đăng nhập, bấm Đăng xuất ở header, rồi mở quan-tri.php hoặc don-hang.php -> chuyển về dang-nhap.php.
 */
require __DIR__ . '/inc/config.php';

xoaGhiNhoDangNhap();
unset($_SESSION['uid'], $_SESSION['user']);
session_regenerate_id(true);
datFlash('ok', 'Bạn đã đăng xuất.');
header('Location: index.php');
exit;

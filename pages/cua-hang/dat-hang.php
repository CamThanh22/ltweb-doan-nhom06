<?php
/**
 * dat-hang.php - Đặt hàng từ giỏ hàng trong một giao dịch (Transaction) (slide 34-35).
 * Bắt buộc đã đăng nhập (yeuCauDangNhap); chỉ nhận phương thức POST (GET chuyển về gio-hang.php).
 * Gọi luuDonHang() trong inc/don-hang.php: nếu đủ tồn kho thì tạo đơn, trừ kho, lưu đơn giá lúc đặt,
 * xóa giỏ hàng và chuyển hướng (PRG) tới don-hang.php?id=<maDon>; nếu thiếu hàng thì rollBack() và báo lỗi.
 * Cách thử: đặt 2 món hợp lệ rồi F5 (không tạo đơn thứ hai); hạ tồn kho món thứ 2 rồi đặt -> rollback.
 */
require dirname(__DIR__, 2) . '/inc/config.php';
require dirname(__DIR__, 2) . '/inc/don-hang.php';

use App\Services\GioHang;

$nd = yeuCauDangNhap();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: gio-hang.php');
    exit;
}

$gioObj = new GioHang();
$gio    = $gioObj->tatCa();
if (!$gio) {
    datFlash('loi', 'Giỏ hàng đang trống, không thể đặt hàng.');
    header('Location: gio-hang.php');
    exit;
}

 $phuongThuc = layChuoi($_POST, 'phuong_thuc');
if (!in_array($phuongThuc, ['cod','chuyen_khoan'], true)) { http_response_code(422); exit('Phương thức thanh toán không hợp lệ'); }
$ghiChu = mb_substr(layChuoi($_POST, 'ghi_chu'), 0, 500);

try {
    $maDon = luuDonHang($gio, (int) $nd['id'], $ghiChu, $phuongThuc);
    $gioObj->xoaHet();
    datFlash('ok', "Đặt hàng thành công! Mã đơn hàng của bạn là #$maDon.");
    header('Location: don-hang.php?id=' . $maDon);
    exit;
} catch (HetHangException $e) {
    datFlash('loi', $e->getMessage() . ' Giỏ hàng của bạn được giữ nguyên.');
    header('Location: gio-hang.php');
    exit;
}

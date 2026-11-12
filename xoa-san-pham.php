<?php
/**
 * xoa-san-pham.php - Xóa một sản phẩm ngọc quý (chỉ dành cho quan_tri, chỉ nhận phương thức POST, slide 25).
 * Nếu truy cập bằng GET (ví dụ gõ xoa-san-pham.php?id=1 lên thanh địa chỉ) -> trả mã 405 Method Not Allowed.
 * Nếu sản phẩm đã có trong chi_tiet_don -> bắt lỗi khóa ngoại 1451 và báo thông báo thân thiện.
 * Cách thử: xóa sản phẩm id=1 (đã có trong đơn hàng #1) -> báo không xóa được; GET -> nhận mã 405.
 */
require __DIR__ . '/inc/config.php';

yeuCauQuyen('quan_tri');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Chỉ nhận phương thức POST');
}

$id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($id === false) {
    datFlash('loi', 'Mã sản phẩm cần xóa không hợp lệ.');
    header('Location: quan-tri.php');
    exit;
}

try {
    $st = db()->prepare('DELETE FROM san_pham WHERE id = ?');
    $st->execute([$id]);
    if ($st->rowCount() > 0) {
        datFlash('ok', "Đã xóa sản phẩm #$id.");
    } else {
        datFlash('loi', "Không tìm thấy sản phẩm #$id.");
    }
} catch (PDOException $e) {
    if (($e->errorInfo[1] ?? 0) !== 1451) {
        throw $e;
    }
    datFlash('loi', "Sản phẩm #$id đã có trong đơn hàng nên không xóa được.");
}

header('Location: quan-tri.php');
exit;

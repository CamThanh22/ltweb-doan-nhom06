<?php
/**
 * api/san-pham.php - REST API trả JSON cho sản phẩm ngọc quý (slide 47-50):
 * - GET api/san-pham.php?q=... : tìm kiếm sản phẩm theo tên/mã/loại (tối đa 8 mục), trả 200 {"du_lieu": [...]}.
 * - GET api/san-pham.php?id=...: xem một sản phẩm theo id; id không hợp lệ -> 400; không tìm thấy -> 404.
 * Đăng ký set_exception_handler trả JSON {"loi": "Lỗi máy chủ"} với mã 500 khi mất kết nối CSDL (slide 50).
 * Cách thử: curl -i "http://localhost:8000/api/san-pham.php?q=nhan" ; ?id=999999 ; ?id=abc.
 */
declare(strict_types=1);

require __DIR__ . '/../inc/config.php';

use App\Data\KhoSanPham;

set_exception_handler(function (Throwable $e): void {
    error_log((string) $e);
    traJson(['loi' => 'Lỗi máy chủ'], 500);
});

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    traJson(['loi' => 'Chỉ nhận phương thức GET'], 405);
}

$kho = new KhoSanPham(db());

if (isset($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($id === false) {
        traJson(['loi' => 'Tham số id không hợp lệ'], 400);
    }
    $sp = $kho->timTheoId((int) $id);
    if ($sp === null) {
        traJson(['loi' => 'Không tìm thấy sản phẩm'], 404);
    }
    traJson($sp, 200);
}

$q = mb_substr(layChuoi($_GET, 'q'), 0, 100);
traJson(['du_lieu' => $kho->timKiem($q, 8)], 200);

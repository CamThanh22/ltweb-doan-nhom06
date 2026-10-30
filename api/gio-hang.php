<?php
/**
 * api/gio-hang.php - REST API xem và thêm sản phẩm vào giỏ hàng trong session (slide 49-50):
 * - GET  api/gio-hang.php : trả 200 {"gio": {...}, "so_mon": ...}.
 * - POST api/gio-hang.php : đọc thân JSON từ php://input {"id": 1, "sl": 2};
 *   thân không phải JSON -> 400; id/sl sai kiểu hoặc ngoài 1..99 -> 422;
 *   sản phẩm không có trong CSDL -> 404; tổng số lượng vượt tồn kho -> 422;
 *   hợp lệ -> cập nhật session và trả 200 {"so_mon": ...}.
 * - Phương thức khác (PUT, DELETE...) -> 405 kèm header Allow: GET, POST.
 * Cách thử: mở api.http trong VS Code (REST Client) hoặc chạy các lệnh curl ở Bảng 3 ca 12.
 */
declare(strict_types=1);

require __DIR__ . '/../inc/config.php';

set_exception_handler(function (Throwable $e): void {
    error_log((string) $e);
    traJson(['loi' => 'Lỗi máy chủ'], 500);
});

$_SESSION['gio'] ??= [];
if (!is_array($_SESSION['gio'])) {
    $_SESSION['gio'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    traJson([
        'gio'    => $_SESSION['gio'],
        'so_mon' => array_sum($_SESSION['gio']),
    ], 200);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: GET, POST');
    traJson(['loi' => 'Chỉ nhận GET hoặc POST'], 405);
}

$thanTho = file_get_contents('php://input');
$vao     = is_string($thanTho) ? json_decode($thanTho, true) : null;
if (!is_array($vao)) {
    traJson(['loi' => 'Thân request phải là JSON'], 400);
}

$id = filter_var($vao['id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$sl = filter_var($vao['sl'] ?? '', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1, 'max_range' => 99],
]);
if ($id === false || $sl === false) {
    traJson(['loi' => 'id hoặc sl không hợp lệ'], 422);
}

$st = db()->prepare('SELECT ton_kho FROM san_pham WHERE id = ?');
$st->execute([(int) $id]);
$ton = $st->fetchColumn();
if ($ton === false) {
    traJson(['loi' => 'Không có sản phẩm này'], 404);
}

$tonInt = (int) $ton;
$moi    = (int) ($_SESSION['gio'][(int) $id] ?? 0) + (int) $sl;
if ($moi > $tonInt) {
    traJson(['loi' => "Chỉ còn $tonInt sản phẩm trong kho"], 422);
}

$_SESSION['gio'][(int) $id] = $moi;
traJson(['so_mon' => array_sum($_SESSION['gio'])], 200);

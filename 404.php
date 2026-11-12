<?php
/**
 * 404.php - trang "không tìm thấy" thân thiện, tự đặt mã trạng thái 404, dùng chung header/footer.
 * Được require từ chi-tiet.php (id sai) và có thể mở trực tiếp.
 * Cách thử: chi-tiet.php?id=abc -> tab Network thấy Status 404.
 */
require_once __DIR__ . '/inc/config.php';

http_response_code(404);
$tieuDe = 'Không tìm thấy trang';
$trang = '';
$moTa = 'Trang bạn tìm không tồn tại trên JadeHub.';
require __DIR__ . '/inc/header.php';
?>
    <main id="noi-dung">
        <h1>404 — Không tìm thấy nội dung</h1>
        <p>Xin lỗi, địa chỉ bạn mở không tồn tại hoặc sản phẩm đã được gỡ khỏi sàn.</p>
        <p>
            <a href="index.php">← Về trang chủ</a> ·
            <a href="danh-sach.php">Xem danh mục ngọc quý</a>
        </p>
    </main>
<?php require __DIR__ . '/inc/footer.php'; ?>

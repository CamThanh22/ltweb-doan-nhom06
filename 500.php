<?php
/**
 * 500.php - trang lỗi máy chủ thân thiện, KHÔNG lộ đường dẫn hay chi tiết lỗi (chi tiết nằm trong logs/php-error.log).
 * Cố ý viết độc lập (không require header/config) để vẫn hiện được khi chính phần nạp trang bị lỗi.
 * Được inc/config.php require khi MOI_TRUONG = 'prod'; cũng có thể mở trực tiếp để xem.
 */
if (!headers_sent()) {
    http_response_code(500);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Có lỗi xảy ra | JadeHub</title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 0; background: #f7f8f7; color: #333; }
        main { max-width: 36rem; margin: 10vh auto; padding: 1.5rem; background: #fff; border: 1px solid #ddd; border-radius: 10px; }
        h1 { color: #0f4f3f; margin-top: 0; }
        a { color: #0f4f3f; }
    </style>
</head>
<body>
    <main>
        <h1>Xin lỗi, có lỗi xảy ra</h1>
        <p>Hệ thống JadeHub đang gặp sự cố. Chúng tôi đã ghi nhận và sẽ xử lý sớm.</p>
        <p>Bạn có thể quay lại trang trước hoặc thử tải lại sau ít phút.</p>
    </main>
</body>
</html>

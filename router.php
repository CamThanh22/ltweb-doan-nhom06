<?php
/**
 * router.php - TÙY CHỌN khi chạy: php -S localhost:8000 router.php
 * Máy chủ tích hợp của PHP bỏ qua .htaccess nên storage/, logs/, inc/, src/, data/ sẽ mở được qua URL.
 * Router này trả 403 cho các thư mục đó (giống Require all denied của Apache); mọi đường dẫn khác chạy bình thường.
 */
$duongDan = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if (preg_match('#^/(storage|logs|inc|src|vendor|data|tests|tools)(/|$)#', $duongDan) === 1) {
    http_response_code(403);
    echo 'Forbidden';
    return true;
}
return false;   // để máy chủ tích hợp phục vụ tệp tĩnh / chạy tệp .php như thường

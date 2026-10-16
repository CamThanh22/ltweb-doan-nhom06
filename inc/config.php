<?php
/**
 * inc/config.php - nạp ĐẦU TIÊN ở mọi trang (require __DIR__ . '/inc/config.php').
 * Việc làm: nạp autoload Composer (App\ -> src/), hàm e()/vnd(), cấu hình hiện/ghi lỗi
 * theo MOI_TRUONG, bắt ngoại lệ không ai catch (hiện 500.php khi 'prod'), mở session.
 * Cách thử: đổi MOI_TRUONG sang 'prod', làm hỏng data/san-pham.json rồi mở danh-sach.php.
 */

// Autoload: ưu tiên của Composer; nếu chưa chạy "composer install" thì tự nạp theo PSR-4
// (chỉ là lưới an toàn để site không chết; cách chuẩn vẫn là composer install).
$autoloadComposer = __DIR__ . '/../vendor/autoload.php';
if (is_file($autoloadComposer)) {
    require_once $autoloadComposer;
} else {
    spl_autoload_register(function (string $lop): void {
        $tienTo = 'App\\';
        if (strncmp($lop, $tienTo, strlen($tienTo)) !== 0) {
            return;
        }
        $tep = __DIR__ . '/../src/' . str_replace('\\', '/', substr($lop, strlen($tienTo))) . '.php';
        if (is_file($tep)) {
            require_once $tep;
        }
    });
}
unset($autoloadComposer);

require_once __DIR__ . '/ham.php';

const MOI_TRUONG = 'dev';                  // máy thật: 'prod'

error_reporting(E_ALL);
ini_set('display_errors', MOI_TRUONG === 'dev' ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/php-error.log');

date_default_timezone_set('Asia/Ho_Chi_Minh');
mb_internal_encoding('UTF-8');

// Cookie phiên: JavaScript không đọc được (HttpOnly), không gửi kèm request chéo trang (SameSite)
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
]);
session_start();

// Lưới an toàn cuối cùng: ngoại lệ không ai bắt -> ghi log, trả mã 500
set_exception_handler(function (Throwable $loi): void {
    error_log((string) $loi);              // đủ chi tiết vào logs/php-error.log
    if (!headers_sent()) {
        http_response_code(500);
    }
    if (MOI_TRUONG === 'dev') {
        echo '<pre>' . htmlspecialchars((string) $loi, ENT_QUOTES, 'UTF-8') . '</pre>';
    } else {
        require __DIR__ . '/../500.php';   // "Xin lỗi, có lỗi xảy ra"
    }
});

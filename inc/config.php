<?php
/**
 * inc/config.php - nạp ĐẦU TIÊN ở mọi trang (require __DIR__ . '/inc/config.php').
 * Việc làm: nạp autoload Composer (App\ -> src/), hàm tiện ích ham.php, kết nối PDO db.php,
 * xác thực xac-thuc.php, cấu hình lỗi/log theo MOI_TRUONG, mở session và bắt ngoại lệ.
 * Cách thử: đặt MOI_TRUONG = 'prod', đổi tên CSDL trong inc/db.php rồi mở danh-sach.php -> hiện 500.php.
 */

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
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/xac-thuc.php';

const MOI_TRUONG = 'dev';                  // khi thử ca 14 hoặc lên máy chủ thật: đổi thành 'prod'

error_reporting(E_ALL);
ini_set('display_errors', MOI_TRUONG === 'dev' ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/php-error.log');

date_default_timezone_set('Asia/Ho_Chi_Minh');
mb_internal_encoding('UTF-8');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

set_exception_handler(function (Throwable $loi): void {
    error_log((string) $loi);
    if (!headers_sent()) {
        http_response_code(500);
    }
    if (MOI_TRUONG === 'dev') {
        echo '<pre>' . htmlspecialchars((string) $loi, ENT_QUOTES, 'UTF-8') . '</pre>';
    } else {
        require __DIR__ . '/../500.php';
    }
});

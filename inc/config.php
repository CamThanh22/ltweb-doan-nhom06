<?php
/** Cấu hình tập trung JadeHub – Bài 7. */
declare(strict_types=1);
$autoloadComposer = __DIR__ . '/../vendor/autoload.php';
if (!is_file($autoloadComposer)) {
    http_response_code(500);
    exit('Chạy composer install trong thư mục JadeHub trước khi mở website.');
}
require_once $autoloadComposer;
error_reporting(E_ALL);
ini_set('display_errors','0');
ini_set('log_errors','1');
ini_set('error_log',__DIR__.'/../logs/php-error.log');
try {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->safeLoad();
    $dotenv->required(['DB_HOST', 'DB_DATABASE', 'DB_USERNAME'])->notEmpty();
    $dotenv->required('DB_PASSWORD');
    $debug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';
    ini_set('display_errors', $debug ? '1' : '0');
} catch (Throwable $error) {
    error_log('Không thể nạp cấu hình JadeHub: ' . get_class($error));
    http_response_code(500);
    if (str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/api/')) {
        header('Content-Type: application/json; charset=utf-8');
        exit(json_encode(['loi'=>'Cấu hình máy chủ chưa đầy đủ'], JSON_UNESCAPED_UNICODE));
    }
    exit('Cấu hình JadeHub chưa đầy đủ. Kiểm tra tệp .env theo README.');
}
date_default_timezone_set('Asia/Ho_Chi_Minh');
if(function_exists('mb_internal_encoding')) mb_internal_encoding('UTF-8');
ini_set('session.use_strict_mode','1');
ini_set('session.use_only_cookies','1');
ini_set('session.cookie_httponly','1');
ini_set('session.cookie_samesite','Lax');
$https=!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off';
if(session_status()!==PHP_SESSION_ACTIVE) {
    session_set_cookie_params(['lifetime'=>0,'path'=>'/','httponly'=>true,'samesite'=>'Lax','secure'=>$https]);
    session_start();
}
require_once __DIR__.'/ham.php';
require_once __DIR__.'/db.php';
require_once __DIR__.'/xac-thuc.php';
require_once __DIR__.'/csrf.php';
require_once __DIR__.'/bao-mat.php';
if (isset($_SESSION['last_activity']) && time()-(int)$_SESSION['last_activity']>1800) {
    xoaGhiNhoDangNhap();
    unset($_COOKIE['ghi_nho']);
    $_SESSION=[];
    session_regenerate_id(true);
}
$_SESSION['last_activity']=time();
header_remove('X-Powered-By');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Frame-Options: DENY');
header("Content-Security-Policy: default-src 'self'; base-uri 'self'; object-src 'none'; frame-ancestors 'none'; form-action 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:; font-src 'self'; connect-src 'self' https://api.open-meteo.com");
if($https) header('Strict-Transport-Security: max-age=31536000');
if(($_SERVER['REQUEST_METHOD'] ?? 'GET')==='POST' && !csrfHopLe()) {
    ghiLogBaoMat('csrf_sai');
    if(str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/api/')) traJson(['loi'=>'CSRF không hợp lệ'],403);
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    exit('403: Mã bảo vệ CSRF thiếu hoặc không hợp lệ.');
}
set_exception_handler(function(Throwable $e) use ($debug): void {
    error_log((string)$e);
    if(!headers_sent()) http_response_code(500);
    if (str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/api/')) { traJson(['loi'=>'Lỗi máy chủ'], 500); }
    if($debug) echo '<pre>'.htmlspecialchars((string)$e,ENT_QUOTES,'UTF-8').'</pre>';
    else require __DIR__.'/../pages/loi/500.php';
});

<?php
// Bắt buộc dùng khi chạy php -S localhost:8000 router.php
$path=parse_url($_SERVER['REQUEST_URI'] ?? '/',PHP_URL_PATH) ?: '/';
$decoded=rawurldecode($path);
if (preg_match('~(^|/)(\.|pages/|inc/|src/|vendor/|database/|logs/|storage/|tests/)~i',$decoded)
 || preg_match('~\.(sql|log|bak|ini|lock)$~i',$decoded)
 || preg_match('~(^|/)(composer\.(json|lock)|README[^/]*|api\.http|router\.php)$~i',$decoded)
 || preg_match('~^/uploads/.*\.(?:php[0-9]?|phtml|phar|cgi|pl)(?:/|$)~i',$decoded)
 || str_contains($decoded,'..')) { http_response_code(404); exit('Not Found'); }
$routes = require __DIR__ . '/inc/routes.php';
if ($path === '/' || isset($routes[ltrim($path, '/')])) {
    require __DIR__ . '/index.php';
    return true;
}
$file=realpath(__DIR__.$path);
if($file!==false && str_starts_with($file,__DIR__.DIRECTORY_SEPARATOR) && is_file($file)) return false;
http_response_code(404); require __DIR__.'/pages/loi/404.php';

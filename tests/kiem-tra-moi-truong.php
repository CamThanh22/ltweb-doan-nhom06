<?php
/** Kiểm tra điều kiện chạy JadeHub; không yêu cầu CSDL đang mở. */
declare(strict_types=1);
$requirements = [
 'PHP >= 8.1' => PHP_VERSION_ID >= 80100,
 'curl (kiểm thử HTTP)' => extension_loaded('curl'),
 'PDO' => extension_loaded('pdo'),
 'pdo_mysql' => extension_loaded('pdo_mysql'),
 'mbstring' => extension_loaded('mbstring'),
 'fileinfo' => extension_loaded('fileinfo'),
 'Ảnh upload có thể kiểm tra' => function_exists('getimagesize'),
 'Thư mục logs ghi được' => is_dir(__DIR__.'/../logs') && is_writable(__DIR__.'/../logs'),
 'Thư mục uploads ghi được' => is_dir(__DIR__.'/../uploads') && is_writable(__DIR__.'/../uploads'),
];
$failed=0;
foreach($requirements as $name=>$ok) { echo ($ok?'PASS':'FAIL').' '.$name.PHP_EOL; if(!$ok)$failed++; }
if (!is_file(__DIR__.'/../.env')) echo 'INFO Chưa có .env; sao chép .env.example khi chạy local.'.PHP_EOL;
echo "Tổng kiểm tra: ".count($requirements)."; không đạt: $failed".PHP_EOL;
exit($failed?1:0);

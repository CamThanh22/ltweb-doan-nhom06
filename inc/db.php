<?php
/**
 * inc/db.php - hàm db() dùng chung mở kết nối PDO tới CSDL MySQL ltweb_nhom06 (slide 17).
 * Dùng static $pdo nên mỗi request chỉ kết nối một lần, tự đóng khi script kết thúc.
 * Cách thử: gọi db()->query('SELECT COUNT(*) FROM san_pham')->fetchColumn(); đổi tên CSDL để thử lỗi 1049.
 */
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $tuyChon = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $pdo = new PDO(
            'mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_DATABASE'] . ';charset=utf8mb4',
            $_ENV['DB_USERNAME'],
            $_ENV['DB_PASSWORD'],
            $tuyChon
        );
    }
    return $pdo;
}

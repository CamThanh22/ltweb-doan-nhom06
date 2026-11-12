<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/inc/config.php';

try {
    $pdo = db();
    $account = $pdo->query('SELECT CURRENT_USER()')->fetchColumn();
    if ($account !== 'nhom06_app@localhost') {
        throw new RuntimeException('Kết nối chưa dùng nhom06_app@localhost.');
    }
    echo "PASS kết nối bằng nhom06_app@localhost\n";

    $database = $pdo->query('SELECT DATABASE()')->fetchColumn();
    $scope = ' ON `' . str_replace('`', '``', (string) $database) . '`.* TO ';
    $permissions = [];
    foreach ($pdo->query('SHOW GRANTS')->fetchAll(PDO::FETCH_COLUMN) as $grant) {
        if (str_starts_with($grant, 'GRANT USAGE ON *.* TO ') && !str_contains($grant, 'WITH GRANT OPTION')) {
            continue;
        }
        $parts = explode($scope, $grant, 2);
        if (count($parts) !== 2 || !str_starts_with($grant, 'GRANT ') || str_contains($grant, 'WITH GRANT OPTION')) {
            throw new RuntimeException('Có quyền ngoài phạm vi cơ sở dữ liệu JadeHub.');
        }
        array_push($permissions, ...explode(', ', substr($parts[0], 6)));
    }
    sort($permissions);
    if ($permissions !== ['DELETE', 'INSERT', 'SELECT', 'UPDATE']) {
        throw new RuntimeException('Quyền không khớp SELECT, INSERT, UPDATE, DELETE.');
    }
    echo "PASS quyền tối thiểu trên cơ sở dữ liệu đang dùng\n";

    $pdo->query('SELECT id FROM san_pham LIMIT 1')->fetchColumn();
    echo "PASS đọc bảng sản phẩm\n";
} catch (RuntimeException $error) {
    if ($error instanceof PDOException) {
        fwrite(STDERR, "FAIL kết nối hoặc truy vấn CSDL. Kiểm tra MySQL và cấu hình .env.\n");
    } else {
        fwrite(STDERR, 'FAIL ' . $error->getMessage() . PHP_EOL);
    }
    exit(1);
}

<?php
// Điểm vào chung của các trang JadeHub.
$routes = require __DIR__ . '/inc/routes.php';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$page = str_ends_with($path, '/') ? 'index.php' : basename($path);
require __DIR__ . '/' . ($routes[$page] ?? $routes['404.php']);

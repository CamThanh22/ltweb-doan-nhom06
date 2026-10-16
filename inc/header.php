<?php
/**
 * inc/header.php - phần đầu dùng chung: <head>, banner, menu (đánh dấu trang đang xem),
 * số món trong giỏ, người đăng nhập hoặc nút Đăng nhập. Trang gọi gán TRƯỚC khi require:
 *   $tieuDe, $trang (khóa menu), $goc (đường dẫn về gốc, vd '../../'), tùy chọn $moTa,
 *   $bodyClass, $cssTruoc / $cssThem (mảng đường dẫn CSS). Sau header: trang tự viết <main>.
 */
$goc ??= '';
$trang ??= '';
$tieuDe ??= 'JadeHub';
$moTa ??= 'JadeHub - Sàn giao dịch và thẩm định ngọc quý, minh bạch nguồn gốc.';
$bodyClass ??= 'trang';
$cssTruoc ??= [];
$cssThem ??= [];

// use chỉ có hiệu lực trong từng tệp nên ở đây viết tên lớp đầy đủ
$hdGio = new App\Services\GioHang();
$hdMenu = [
    'index'      => ['index.php', 'Trang chủ'],
    'danh-sach'  => ['danh-sach.php', 'Danh mục ngọc'],
    'chi-tiet'   => ['chi-tiet.php?id=1', 'Viên ngọc tiêu biểu'],
    'gioi-thieu' => ['gioi-thieu.php', 'Giới thiệu'],
    'lien-he'    => ['lien-he.php', 'Liên hệ & Thẩm định'],
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= e($moTa) ?>">
    <title><?= e($tieuDe) ?> | JadeHub</title>
<?php foreach ($cssTruoc as $hdCss): ?>
    <link rel="stylesheet" href="<?= e($hdCss) ?>">
<?php endforeach; ?>
    <link rel="stylesheet" href="<?= e($goc) ?>css/style.css">
<?php foreach ($cssThem as $hdCss): ?>
    <link rel="stylesheet" href="<?= e($hdCss) ?>">
<?php endforeach; ?>
</head>
<body<?= $bodyClass !== '' ? ' class="' . e($bodyClass) . '"' : '' ?>>
    <a class="bo-qua" href="#noi-dung">Bỏ qua, tới nội dung chính</a>
    <header>
        <p><strong>JadeHub</strong> — Minh bạch nguồn gốc &amp; Thẩm định ngọc quý</p>
        <nav aria-label="Menu chính">
            <ul>
<?php foreach ($hdMenu as $hdKhoa => [$hdLienKet, $hdNhan]): ?>
                <li><a href="<?= e($goc . $hdLienKet) ?>"<?= $hdKhoa === $trang ? ' aria-current="page"' : '' ?>><?= e($hdNhan) ?></a></li>
<?php endforeach; ?>
            </ul>
        </nav>
        <p class="tom-tat-yeu-thich">
            <a href="<?= e($goc) ?>gio-hang.php">Giỏ hàng: <span data-dem-gio><?= $hdGio->soMon() ?></span></a>
            · <a href="<?= e($goc) ?>danh-sach.php">Yêu thích: <span data-dem-yeu-thich="">0</span></a>
<?php if (isset($_SESSION['user'])): ?>
            · <a href="<?= e($goc) ?>quan-tri.php">Quản trị</a>
            · <span class="ten-nguoi-dung">Xin chào, <?= e((string) $_SESSION['user']) ?></span>
            · <a href="<?= e($goc) ?>dang-xuat.php">Đăng xuất</a>
<?php else: ?>
            · <a href="<?= e($goc) ?>dang-nhap.php">Đăng nhập</a>
<?php endif; ?>
        </p>
    </header>

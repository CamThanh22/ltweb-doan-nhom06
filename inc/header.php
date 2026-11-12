<?php
/**
 * inc/header.php - phần đầu dùng chung: <head>, banner, menu (đánh dấu trang đang xem),
 * số món trong giỏ (#so-mon), người đăng nhập (họ tên từ nguoiDung()), mục Đơn hàng của tôi
 * và mục Quản trị (chỉ hiện khi vai_tro === 'quan_tri').
 * Cách thử: đăng nhập lan@ued.vn (không thấy Quản trị) và admin@ued.vn (thấy Quản trị).
 */
$goc ??= '';
$trang ??= '';
$tieuDe ??= 'JadeHub';
$moTa ??= 'JadeHub - Sàn giao dịch và thẩm định ngọc quý, minh bạch nguồn gốc.';
$bodyClass ??= 'trang';
$cssTruoc ??= [];
$cssThem ??= [];

$hdGio = new App\Services\GioHang();
$hdNd  = nguoiDung();
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
<body<?= $bodyClass !== '' ? ' class="' . e($bodyClass) . '"' : '' ?> data-goc="<?= e($goc) ?>">
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
            <a href="<?= e($goc) ?>gio-hang.php">Giỏ hàng: <span id="so-mon" data-dem-gio><?= $hdGio->soMon() ?></span></a>
            · <a href="<?= e($goc) ?>danh-sach.php">Yêu thích: <span data-dem-yeu-thich="">0</span></a>
<?php if ($hdNd !== null): ?>
            · <a href="<?= e($goc) ?>don-hang.php">Đơn hàng của tôi</a>
<?php if (($hdNd['vai_tro'] ?? '') === 'quan_tri'): ?>
            · <a href="<?= e($goc) ?>quan-tri.php">Quản trị</a>
<?php endif; ?>
            · <span class="ten-nguoi-dung">Xin chào, <strong><?= e((string) $hdNd['ho_ten']) ?></strong></span>
            · <a href="<?= e($goc) ?>dang-xuat.php">Đăng xuất</a>
<?php else: ?>
            · <a href="<?= e($goc) ?>dang-nhap.php">Đăng nhập</a>
            · <a href="<?= e($goc) ?>dang-ky.php">Đăng ký</a>
<?php endif; ?>
        </p>
    </header>

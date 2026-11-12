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
    'cong-dong'  => ['cong-dong.php', 'Cộng đồng'],
    'lien-he'    => ['lien-he.php', 'Liên hệ & Thẩm định'],
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="<?= e(csrfToken()) ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= e($moTa) ?>">
    <title><?= e($tieuDe) ?> | JadeHub</title>
<?php foreach ($cssTruoc as $hdCss): ?>
    <link rel="stylesheet" href="<?= e($hdCss) ?>">
<?php endforeach; ?>
    <link rel="stylesheet" href="<?= e($goc) ?>css/style.css">
    <link rel="stylesheet" href="<?= e($goc) ?>css/cong-dong.css">
<?php foreach ($cssThem as $hdCss): ?>
    <link rel="stylesheet" href="<?= e($hdCss) ?>">
<?php endforeach; ?>
    <link rel="stylesheet" href="<?= e($goc) ?>css/jadehub-design.css?v=5">
    <link rel="stylesheet" href="<?= e($goc) ?>css/jadehub-reference.css?v=4">
    <link rel="stylesheet" href="<?= e($goc) ?>css/giao-dich.css">
</head>
<body<?= $bodyClass !== '' ? ' class="' . e($bodyClass) . '"' : '' ?> data-goc="<?= e($goc) ?>">
    <a class="bo-qua" href="#noi-dung">Bỏ qua, tới nội dung chính</a>
    <div class="jh-announcement">Minh bạch nguồn gốc. Trọn vẹn giá trị. &nbsp; <span>Dự án JadeHub · Nhóm 06</span></div>
    <header class="site-header">
        <div class="site-header__inner">
            <a class="jh-brand" href="<?= e($goc) ?>index.php" aria-label="JadeHub – Trang chủ">
                <img class="jh-brand__logo" src="<?= e($goc) ?>images/logo-jadehub.jpg" alt="" width="74" height="56">
                <span class="jh-brand__text"><strong>JADEHUB</strong><small>NATURAL JADE &amp; STORIES</small></span>
            </a>
            <button class="jh-nav-toggle" type="button" aria-label="Menu" aria-expanded="false" aria-controls="jh-primary-nav jh-account-menu" data-nav-toggle>
                <span aria-hidden="true">☰</span> <span>Menu</span>
            </button>
            <nav class="jh-primary-nav" id="jh-primary-nav" aria-label="Điều hướng chính">
                <ul>
                    <li><a href="<?= e($goc) ?>index.php"<?= $trang === 'index' ? ' aria-current="page"' : '' ?>>Trang chủ</a></li>
                    <li><a href="<?= e($goc) ?>danh-sach.php"<?= in_array($trang, ['danh-sach','chi-tiet'], true) ? ' aria-current="page"' : '' ?>>Bộ sưu tập</a></li>
                    <li><a href="<?= e($goc) ?>lien-he.php"<?= $trang === 'lien-he' ? ' aria-current="page"' : '' ?>>Thẩm định</a></li>
                    <li><a href="<?= e($goc) ?>cong-dong.php"<?= $trang === 'cong-dong' ? ' aria-current="page"' : '' ?>>Cộng đồng</a></li>
                    <li><a href="<?= e($goc) ?>gioi-thieu.php"<?= $trang === 'gioi-thieu' ? ' aria-current="page"' : '' ?>>Về JadeHub</a></li>
                </ul>
            </nav>
            <div class="jh-header-actions">
                <a class="jh-header-icon" href="<?= e($goc) ?>danh-sach.php" aria-label="Xem bộ sưu tập và sản phẩm yêu thích" title="Bộ sưu tập yêu thích">♡ <span data-dem-yeu-thich>0</span></a>
                <a class="jh-header-icon" href="<?= e($goc) ?>gio-hang.php" aria-label="Giỏ hàng"><svg width="21" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M5 7h14l1 14H4L5 7Z"/><path d="M8 8V6a4 4 0 0 1 8 0v2"/></svg> <span id="so-mon" data-dem-gio><?= $hdGio->soMon() ?></span></a>
<?php if ($hdNd === null): ?>
                <a class="jh-header-login" href="<?= e($goc) ?>dang-nhap.php">Đăng nhập</a>
<?php else: ?>
                <a class="jh-header-login" href="<?= e($goc) ?>ho-so.php" title="Hồ sơ cá nhân"><?= e((string) $hdNd['ho_ten']) ?></a>
<?php endif; ?>
            </div>
        </div>
        <div class="jh-account-bar" id="jh-account-menu">
            <div class="jh-account-bar__inner">
<?php if ($hdNd !== null): ?>
                <a class="jh-mobile-profile" href="<?= e($goc) ?>ho-so.php">Hồ sơ cá nhân</a>
                <a href="<?= e($goc) ?>don-hang.php">Đơn hàng của tôi</a>
                <a href="<?= e($goc) ?>nguoi-ban.php">Gian hàng</a>
                <a href="<?= e($goc) ?>tin-nhan.php">Tin nhắn</a>
                <a href="<?= e($goc) ?>thong-bao-xa-hoi.php">Thông báo</a>
<?php if (($hdNd['vai_tro'] ?? '') === 'quan_tri'): ?>
                <a href="<?= e($goc) ?>quan-tri.php">Quản trị</a>
                <a href="<?= e($goc) ?>kiem-duyet.php">Kiểm duyệt</a>
<?php endif; ?>
                <form class="dang-xuat-inline" method="post" action="<?= e($goc) ?>dang-xuat.php"><?= csrfField() ?><button type="submit">Đăng xuất</button></form>
<?php else: ?>
                <a href="<?= e($goc) ?>dang-nhap.php">Đăng nhập</a><a href="<?= e($goc) ?>don-hang.php">Đơn của tôi</a><a href="<?= e($goc) ?>nguoi-ban.php">Kênh người bán</a><a href="<?= e($goc) ?>tin-nhan.php">Tin nhắn</a>
<?php endif; ?>
            </div>
        </div>
    </header>

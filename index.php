<?php
/**
 * index.php - Trang chủ: đọc sản phẩm mới nhất từ MySQL qua KhoSanPham(db()),
 * khối "Đã xem gần đây" đọc từ cookie da_xem bằng một câu WHERE id IN (...) (DaXem::doc),
 * và khối thời tiết Đà Nẵng do JavaScript lấy từ Open-Meteo API.
 * Cách thử: xem chi-tiet.php?id=2 và ?id=5 rồi quay lại trang chủ; sửa cookie da_xem thành abc,-1,999999.
 */
require __DIR__ . '/inc/config.php';

use App\Data\KhoSanPham;
use App\Services\DaXem;

$kho = new KhoSanPham(db());
$noiBat = $kho->moiNhat(4);
$tongSp = $kho->dem();
$daXem  = DaXem::doc($kho);

$tieuDe = 'Trang chủ';
$trang  = 'index';
$moTa   = 'JadeHub - Sàn giao dịch và mạng xã hội thẩm định ngọc bích, phỉ thúy thiên nhiên có giấy chứng thư kiểm định uy tín.';
$jsThem = ['js/trang-chu.js'];
require __DIR__ . '/inc/header.php';
?>
    <main id="noi-dung">
<?php require __DIR__ . '/inc/thong-bao.php'; ?>
        <section class="hero">
            <h1>Nền tảng giao dịch và thẩm định ngọc quý tự nhiên JadeHub</h1>
            <h2>Giới thiệu sàn JadeHub</h2>
            <p>
                JadeHub là nền tảng kết nối những người đam mê ngọc bích,
                cẩm thạch và phỉ thúy loại A. Hiện sàn đang niêm yết <strong><?= $tongSp ?></strong> vật phẩm
                ngọc quý đã qua quy trình thẩm định nghiêm ngặt.
            </p>
            <figure>
                <img alt="Logo sàn giao dịch ngọc quý JadeHub" height="300" src="images/logo-jadehub.jpg" width="300">
                <figcaption>Hình 1: Logo chính thức của sàn JadeHub</figcaption>
            </figure>
        </section>

        <section>
            <h2>Cam kết chất lượng 3 chuẩn</h2>
            <ul>
                <li><strong>Chuẩn thiên nhiên:</strong> 100% ngọc tự nhiên không xử lý nhiệt, không tẩm keo nhuộm màu.</li>
                <li><strong>Chuẩn chứng thư:</strong> Bắt buộc có chứng nhận giám định từ các trung tâm kiểm định độc lập uy tín.</li>
                <li><strong>Chuẩn cộng đồng:</strong> Mọi thành viên đều có thể gửi ảnh soi nước ngọc để nhận phản hồi từ chuyên gia.</li>
            </ul>
        </section>

        <section>
            <h2>Vật phẩm ngọc quý mới niêm yết</h2>
            <div class="luoi-gioi-thieu">
<?php foreach ($noiBat as $sp): ?>
                <article class="the-gioi-thieu">
                    <h3><a href="chi-tiet.php?id=<?= $sp->id ?>"><?= e($sp->ten) ?></a></h3>
                    <p><small><?= e($sp->tenDanhMuc()) ?> · Mã: <?= e($sp->ma) ?></small></p>
                    <p><?= e($sp->moTa) ?></p>
                    <p class="gia-san-pham"><?= e(vnd($sp->gia)) ?></p>
                    <p><a href="chi-tiet.php?id=<?= $sp->id ?>">Xem chi tiết sản phẩm và chứng thư kiểm định →</a></p>
                </article>
<?php endforeach; ?>
            </div>
        </section>

<?php if ($daXem): ?>
        <section id="da-xem-gan-day">
            <h2>Đã xem gần đây</h2>
            <ul>
<?php foreach ($daXem as $sp): ?>
                <li><a href="chi-tiet.php?id=<?= $sp->id ?>"><?= e($sp->ten) ?></a> (<?= e($sp->tenDanhMuc()) ?>) — <?= e(vnd($sp->gia)) ?></li>
<?php endforeach; ?>
            </ul>
        </section>
<?php endif; ?>

        <div aria-live="polite" id="du-lieu-api">Đang tải dữ liệu thời tiết…</div>
        <noscript><p>JavaScript đang tắt nên khối thời tiết Đà Nẵng không hiển thị; các nội dung khác vẫn dùng bình thường.</p></noscript>
    </main>
<?php require __DIR__ . '/inc/footer.php'; ?>

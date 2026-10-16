<?php
/**
 * index.php - Trang chủ: giới thiệu, ba viên ngọc nổi bật (từ data/san-pham.json qua KhoSanPham),
 * khối "Đã xem gần đây" đọc từ cookie da_xem (kiểm tra lại từng id), khối thời tiết do JavaScript lấy.
 * Cách thử: xem chi-tiet.php?id=2 và ?id=5 rồi quay lại trang chủ; sửa cookie da_xem thành abc,-1,999999.
 */
require __DIR__ . '/inc/config.php';

use App\Data\KhoSanPham;
use App\Services\DaXem;

$kho = new KhoSanPham(__DIR__ . '/data/san-pham.json');
$noiBat = array_slice($kho->tatCa(), 0, 3);   // phần xử lý: không echo
$daXem = DaXem::doc($kho);                    // đọc lại cookie, bỏ qua giá trị lạ

$tieuDe = 'Trang chủ';
$trang = 'index';
$moTa = 'JadeHub - Sàn giao dịch và mạng xã hội thẩm định ngọc bích, phỉ thúy thiên nhiên có giấy chứng thư kiểm định uy tín.';
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
                cẩm thạch và phỉ thúy loại A. Chúng tôi giải quyết triệt để
                vấn đề hàng giả bằng quy trình thẩm định nghiêm ngặt kết hợp
                mạng xã hội chia sẻ kiến thức chuyên sâu.
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
            <h2>Bộ sưu tập ngọc nổi bật tuần này</h2>
            <div class="luoi-gioi-thieu">
<?php foreach ($noiBat as $sp): ?>
                <article class="the-gioi-thieu">
                    <h3><?= e($sp->ten) ?></h3>
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
                <li><a href="chi-tiet.php?id=<?= $sp->id ?>"><?= e($sp->ten) ?></a> — <?= e(vnd($sp->gia)) ?></li>
<?php endforeach; ?>
            </ul>
        </section>
<?php endif; ?>

        <div aria-live="polite" id="du-lieu-api">Đang tải dữ liệu thời tiết…</div>
        <noscript><p>JavaScript đang tắt nên khối thời tiết Đà Nẵng không hiển thị; các nội dung khác vẫn dùng bình thường.</p></noscript>
    </main>
<?php require __DIR__ . '/inc/footer.php'; ?>

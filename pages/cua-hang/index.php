<?php
require dirname(__DIR__, 2) . '/inc/config.php';

use App\Data\KhoSanPham;
use App\Services\DaXem;
$kho = new KhoSanPham(db());
$noiBat = $kho->moiNhat(4);
$tongSp = $kho->dem();
$daXem = DaXem::doc($kho);
$tieuDe = 'Trang chủ';
$trang = 'index';
$moTa = 'JadeHub – sàn giao dịch và cộng đồng yêu ngọc quý, khám phá sản phẩm và thông tin chứng thư.';
$jsThem = ['js/trang-chu.js'];
require dirname(__DIR__, 2) . '/inc/header.php';
?>
<main id="noi-dung" class="jh-home">
<?php require dirname(__DIR__, 2) . '/inc/thong-bao.php'; ?>
    <section class="jh-editorial-hero" aria-labelledby="jh-hero-title">
        <div class="jh-editorial-hero__content">
            <span class="jh-eyebrow">TINH HOA TỪ THIÊN NHIÊN</span>
            <h1 id="jh-hero-title">Mỗi viên ngọc,<br>một câu chuyện<br><em>đáng trân quý.</em></h1>
            <p>Nơi kết nối người yêu ngọc với vẻ đẹp tự nhiên. Khám phá, tìm hiểu và lưu giữ giá trị nguyên bản.</p>
            <div class="jh-hero-actions">
                <a class="jh-btn jh-btn--solid" href="danh-sach.php">Khám phá bộ sưu tập <span aria-hidden="true">↗</span></a>
                <a class="jh-btn jh-btn--text" href="lien-he.php">Tìm hiểu thẩm định <span aria-hidden="true">→</span></a>
            </div>
            <div class="jh-hero-stats"><strong><?= (int)$tongSp ?></strong><span>vật phẩm đang niêm yết</span><i aria-hidden="true"></i><span>Ngọc quý · Câu chuyện · Cộng đồng</span></div>
        </div>
        <div class="jh-editorial-hero__visual">
            <img src="images/JADE-06.jpg" alt="Vẻ đẹp ngọc quý tại JadeHub" fetchpriority="high" width="700" height="850">
            <span class="jh-hero-vertical">NATURAL BEAUTY · JADEHUB</span>
            <div class="jh-art-label"><span>BỘ SƯU TẬP TIÊU BIỂU</span><h2>Sắc ngọc. An nhiên.</h2><a href="chi-tiet.php?id=6">Phỉ thúy Bạch Nguyệt <span aria-hidden="true">→</span></a></div>
        </div>
    </section>
    <section class="jh-values" aria-label="Giá trị JadeHub">
        <article><span class="jh-values__number">01</span><div><h2>Thông tin rõ ràng</h2><p>Khám phá xuất xứ, thông số và chứng thư được cung cấp cho sản phẩm.</p></div></article>
        <article><span class="jh-values__number">02</span><div><h2>Vẻ đẹp nguyên bản</h2><p>Tôn vinh sự độc đáo về màu sắc, đường vân và cảm hứng từ thiên nhiên.</p></div></article>
        <article><span class="jh-values__number">03</span><div><h2>Cộng đồng yêu ngọc</h2><p>Chia sẻ kiến thức, thảo luận và kết nối với những người cùng sở thích.</p></div></article>
    </section>
    <section class="jh-featured" aria-labelledby="jh-featured-title">
        <div class="jh-section-heading"><div><span class="jh-eyebrow">ĐƯỢC CHỌN LỌC TỪ BỘ SƯU TẬP</span><h2 id="jh-featured-title">Tìm viên ngọc <em>của riêng bạn</em></h2><p>Khám phá những vật phẩm được giới thiệu trên JadeHub.</p></div><a class="jh-inline-link" href="danh-sach.php">Xem tất cả <span aria-hidden="true">↗</span></a></div>
        <nav class="jh-category-chips" aria-label="Lọc bộ sưu tập theo loại ngọc">
          <a class="is-active" href="danh-sach.php">Tất cả</a>
          <a href="danh-sach.php?q=ph%E1%BB%89+th%C3%BAy">Ngọc phỉ thúy</a>
          <a href="danh-sach.php?q=c%E1%BA%A9m+th%E1%BA%A1ch">Ngọc cẩm thạch</a>
          <a href="danh-sach.php?q=H%C3%B2a+%C4%90i%E1%BB%81n">Ngọc bích Hòa Điền</a>
        </nav>
        <div class="jh-featured-grid">
<?php foreach ($noiBat as $sp): ?>
            <article class="jh-featured-card">
                <a class="jh-featured-card__image" href="chi-tiet.php?id=<?= (int)$sp->id ?>"><img src="<?= e($sp->duongDanAnh()) ?>" alt="<?= e($sp->ten) ?>" loading="lazy" width="520" height="620"><span>HỒ SƠ SẢN PHẨM</span></a>
                <div class="jh-featured-card__meta"><?= e($sp->tenDanhMuc()) ?> · <?= e($sp->ma) ?></div>
                <h3><a href="chi-tiet.php?id=<?= (int)$sp->id ?>"><?= e($sp->ten) ?></a></h3>
                <p class="gia-san-pham"><?= e(vnd($sp->gia)) ?></p>
            </article>
<?php endforeach; ?>
        </div>
    </section>
    <section class="jh-story-banner" aria-labelledby="jh-story-title">
        <div class="jh-story-banner__image"><img src="images/JADE-03.jpg" loading="lazy" alt="Một viên ngọc trong bộ sưu tập JadeHub" width="700" height="700"></div>
        <div class="jh-story-banner__content"><span class="jh-eyebrow">HIỂU NGỌC, TRÂN QUÝ NGỌC</span><h2 id="jh-story-title">Vẻ đẹp có thể cảm nhận.<br><em>Nguồn gốc cần được hiểu.</em></h2><p>Một viên ngọc không chỉ có màu sắc. Chất liệu, xuất xứ và hồ sơ đi kèm giúp bạn có thêm cơ sở khi tìm hiểu và lựa chọn.</p><a class="jh-btn jh-btn--outline" href="lien-he.php">Tìm hiểu về thẩm định <span aria-hidden="true">→</span></a></div>
        <p class="jh-disclaimer">Hồ sơ trong bộ sưu tập là dữ liệu của đồ án; chưa được xác minh độc lập.</p>
    </section>
    <section class="jh-community-cta"><span class="jh-eyebrow">CÙNG NHAU CHIA SẺ</span><h2>Một cộng đồng, muôn câu chuyện về ngọc.</h2><p>Đăng bài, đặt câu hỏi và cùng khám phá những điều thú vị về ngọc quý.</p><a class="jh-btn jh-btn--solid" href="cong-dong.php">Đến cộng đồng <span aria-hidden="true">↗</span></a></section>
<?php if ($daXem): ?>
    <section class="jh-recent" id="da-xem-gan-day"><div class="jh-section-heading"><div><span class="jh-eyebrow">DÀNH CHO BẠN</span><h2>Đã xem gần đây</h2></div></div><div class="jh-recent-links"><?php foreach ($daXem as $sp): ?><a href="chi-tiet.php?id=<?= (int)$sp->id ?>"><?= e($sp->ten) ?><span><?= e(vnd($sp->gia)) ?> ↗</span></a><?php endforeach; ?></div></section>
<?php endif; ?>
    <aside class="jh-weather"><div class="jh-weather__label">THỜI TIẾT ĐÀ NẴNG</div><div id="du-lieu-api" aria-live="polite">Đang tải dữ liệu thời tiết…</div><noscript><p>Cần bật JavaScript để xem thời tiết.</p></noscript></aside>
</main>
<?php require dirname(__DIR__, 2) . '/inc/footer.php'; ?>

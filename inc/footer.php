<?php
/**
 * inc/footer.php - phần cuối dùng chung: chân trang và các thẻ <script type="module">.
 * Trang có thể gán $jsThem (mảng đường dẫn JS, tính từ thư mục của trang đó).
 */
$goc ??= '';
$jsThem ??= [];
?>
    <footer class="jh-footer">
        <div class="jh-footer__inner">
            <div><a class="jh-footer__brand" href="<?= e($goc) ?>index.php"><img src="<?= e($goc) ?>images/logo-jadehub.jpg" alt="Logo JP JadeHub" width="66" height="50"> JADEHUB</a>
                 <p>Minh bạch nguồn gốc &amp; Thẩm định ngọc quý. Khám phá vẻ đẹp nguyên bản và kết nối những người trân quý ngọc thiên nhiên.</p></div>
            <div><h2>Khám phá</h2><a href="<?= e($goc) ?>danh-sach.php">Bộ sưu tập</a><a href="<?= e($goc) ?>lien-he.php">Liên hệ &amp; thẩm định</a><a href="<?= e($goc) ?>cong-dong.php">Cộng đồng</a></div>
            <div><h2>Hỗ trợ</h2><a href="<?= e($goc) ?>gioi-thieu.php">Về chúng tôi</a><a href="<?= e($goc) ?>gio-hang.php">Giỏ hàng</a><a href="<?= e($goc) ?>don-hang.php">Đơn hàng</a></div>
        </div>
        <div class="jh-footer__bottom">© 2026 JADEHUB. Trân quý giá trị nguyên bản. · Nhóm 06 — Trường Đại học Sư phạm, Đại học Đà Nẵng.</div>
    </footer>
    <script src="<?= e($goc) ?>js/main.js" type="module"></script>
    <script src="<?= e($goc) ?>js/jadehub-navigation.js?v=3" defer></script>
<?php foreach ($jsThem as $ftJs): ?>
    <script src="<?= e($ftJs) ?>" type="module"></script>
<?php endforeach; ?>
<script src="<?= e($goc) ?>js/tu-kiem-tra.js" defer></script>
</body>
</html>

<?php
/**
 * inc/footer.php - phần cuối dùng chung: chân trang và các thẻ <script type="module">.
 * Trang có thể gán $jsThem (mảng đường dẫn JS, tính từ thư mục của trang đó).
 */
$goc ??= '';
$jsThem ??= [];
?>
    <footer>
        <p>© 2026 Nhóm 06 — Học phần Thiết kế và Lập trình Web — Khoa Toán – Tin, Trường ĐH Sư phạm – ĐHĐN.</p>
    </footer>
    <script src="<?= e($goc) ?>js/main.js" type="module"></script>
<?php foreach ($jsThem as $ftJs): ?>
    <script src="<?= e($ftJs) ?>" type="module"></script>
<?php endforeach; ?>
</body>
</html>

<?php $maBai = (int)$baiHienThi['id']; ?>
<?php if ($baiHienThi['co_anh']): ?>
    <img class="social-post-image" src="anh-xa-hoi.php?loai=bai-viet&amp;id=<?= $maBai ?>" alt="Ảnh của bài viết" loading="lazy">
<?php endif; ?>
<div class="social-actions">
    <a href="ho-so.php?id=<?= (int)$baiHienThi['tac_gia_id'] ?>#danh-gia">Xem hồ sơ & đánh giá</a>
    <?php if($uid!==(int)$baiHienThi['tac_gia_id']): ?><a href="tin-nhan.php?id=<?= (int)$baiHienThi['tac_gia_id'] ?>">Nhắn tác giả</a><?php endif;?>
    <?php if ($uid): ?>
        <form method="post" action="cong-dong.php?id=<?= $maBai ?>">
            <?= csrfField() ?>
            <input type="hidden" name="bai_viet_id" value="<?= $maBai ?>">
            <input type="hidden" name="hanh_dong" value="<?= $baiHienThi['da_thich'] ? 'bo_yeu_thich' : 'yeu_thich' ?>">
            <button type="submit" class="nut-phu" aria-pressed="<?= $baiHienThi['da_thich'] ? 'true' : 'false' ?>">
                <?= $baiHienThi['da_thich'] ? '♥ Bỏ yêu thích' : '♡ Yêu thích' ?> · <?= (int)$baiHienThi['so_thich'] ?>
            </button>
        </form>
        <a href="chia-se-bai.php?id=<?= $maBai ?>">Chia sẻ qua tin nhắn</a>
    <?php else: ?>
        <span><?= (int)$baiHienThi['so_thich'] ?> lượt yêu thích</span>
        <a href="dang-nhap.php">Đăng nhập để tương tác</a>
    <?php endif; ?>
</div>

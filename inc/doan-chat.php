<?php if (!$tin): ?><p>Hãy gửi lời chào đầu tiên.</p><?php endif; ?>
<?php foreach ($tin as $t): ?>
    <div class="social-message<?= (int)$t['nguoi_gui_id'] === $uid ? ' social-message-own' : '' ?>" data-message-id="<?= (int)$t['id'] ?>">
        <?php if ($t['noi_dung'] !== ''): ?><div><?= nl2br(e($t['noi_dung'])) ?></div><?php endif; ?>
        <?php if ($t['co_anh']): ?>
            <a href="anh-xa-hoi.php?loai=tin-nhan&amp;id=<?= (int)$t['id'] ?>">
                <img src="anh-xa-hoi.php?loai=tin-nhan&amp;id=<?= (int)$t['id'] ?>" alt="Ảnh đính kèm" loading="lazy">
            </a>
        <?php endif; ?>
        <?php if ($t['bai_chia_se_id']): ?>
            <?php if ($t['tieu_de'] !== null): ?>
                <a class="social-shared-post" href="cong-dong.php?id=<?= (int)$t['bai_chia_se_id'] ?>">Bài viết được chia sẻ · <?= e($t['tieu_de']) ?></a>
            <?php else: ?><span>Bài viết đã được gỡ.</span><?php endif; ?>
        <?php endif; ?>
        <small><?= (int)$t['nguoi_gui_id'] === $uid ? 'Bạn' : e($doiTen) ?> · <?= e($t['ngay_gui']) ?></small>
    </div>
<?php endforeach; ?>

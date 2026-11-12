<?php
require dirname(__DIR__, 2).'/inc/config.php';
require dirname(__DIR__, 2).'/inc/nen-tang.php';

$uid = (int)yeuCauDangNhap()['id'];
$id = idDuong($_GET['id'] ?? null);
$s = db()->prepare('SELECT tieu_de FROM bai_viet_cong_dong WHERE id=?');
$s->execute([$id]);
$ten = $s->fetchColumn();
if ($ten === false) {
    http_response_code(404);
    require dirname(__DIR__, 2).'/pages/loi/404.php';
    exit;
}
$s = db()->prepare('SELECT id,ho_ten FROM nguoi_dung WHERE id<>? ORDER BY ho_ten,id');
$s->execute([$uid]);
$nguoiNhan = $s->fetchAll();
$tieuDe = 'Chia sẻ bài viết';
require dirname(__DIR__, 2).'/inc/header.php';
?>
<main id="noi-dung" class="jh-page">
    <p><a href="cong-dong.php?id=<?= $id ?>">← Về bài viết</a></p>
    <h1>Chia sẻ bài viết qua tin nhắn</h1>
    <p><?= e($ten) ?></p>
    <?php if ($nguoiNhan): ?>
    <form method="post" action="tin-nhan.php">
        <?= csrfField() ?>
        <input type="hidden" name="bai_chia_se_id" value="<?= $id ?>">
        <label for="nguoi-nhan">Người nhận</label>
        <select id="nguoi-nhan" name="nguoi_nhan_id" required>
            <option value="">Chọn thành viên</option>
            <?php foreach ($nguoiNhan as $nd): ?>
                <option value="<?= (int)$nd['id'] ?>"><?= e($nd['ho_ten']) ?></option>
            <?php endforeach; ?>
        </select>
        <label for="loi-nhan">Lời nhắn (không bắt buộc)</label>
        <textarea id="loi-nhan" name="noi_dung" maxlength="2000" rows="3"></textarea>
        <button type="submit">Gửi bài viết</button>
    </form>
    <?php else: ?><p>Chưa có thành viên khác để chia sẻ.</p><?php endif; ?>
</main>
<?php require dirname(__DIR__, 2).'/inc/footer.php'; ?>

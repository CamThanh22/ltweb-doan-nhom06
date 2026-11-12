<?php
require dirname(__DIR__, 2).'/inc/config.php';
require dirname(__DIR__, 2).'/inc/nen-tang.php';

$uid = (int)yeuCauDangNhap()['id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    db()->prepare('UPDATE thong_bao_xa_hoi SET da_doc=1 WHERE nguoi_nhan_id=?')->execute([$uid]);
    db()->prepare('UPDATE tin_nhan SET da_doc=1 WHERE nguoi_nhan_id=? AND da_doc=0')->execute([$uid]);
    chuyenTrang('thong-bao-xa-hoi.php');
}
$s = db()->prepare('SELECT t.*, n.ho_ten FROM thong_bao_xa_hoi t JOIN nguoi_dung n ON n.id=t.tac_nhan_id WHERE t.nguoi_nhan_id=? ORDER BY t.id DESC LIMIT 50');
$s->execute([$uid]);
$hoatDong = $s->fetchAll();
$s = db()->prepare('SELECT t.nguoi_gui_id, n.ho_ten, COUNT(*) AS so_tin FROM tin_nhan t JOIN nguoi_dung n ON n.id=t.nguoi_gui_id WHERE t.nguoi_nhan_id=? AND t.da_doc=0 GROUP BY t.nguoi_gui_id,n.ho_ten ORDER BY MAX(t.id) DESC LIMIT 30');
$s->execute([$uid]);
$tinMoi = $s->fetchAll();
$tieuDe = 'Thông báo';
require dirname(__DIR__, 2).'/inc/header.php';
?>
<main id="noi-dung" class="jh-page">
    <h1>Thông báo</h1>
    <?php if (!$tinMoi && !$hoatDong): ?><p>Chưa có thông báo.</p><?php endif; ?>
    <?php if ($tinMoi || $hoatDong): ?>
    <form method="post"><?= csrfField() ?><button type="submit">Đánh dấu tất cả đã đọc</button></form>
    <?php endif; ?>
    <?php foreach ($tinMoi as $tin): ?>
        <p class="jh-card"><a href="tin-nhan.php?id=<?= (int)$tin['nguoi_gui_id'] ?>"><?= e($tin['ho_ten']) ?> đã gửi <?= (int)$tin['so_tin'] ?> tin nhắn mới</a></p>
    <?php endforeach; ?>
    <?php foreach ($hoatDong as $tb):
        $loai = $tb['loai'];
        $dich = $loai === 'theo_doi' ? 'ho-so.php?id='.(int)$tb['tac_nhan_id'] : 'cong-dong.php?id='.(int)$tb['doi_tuong_id'];
        $nhan = ['theo_doi'=>'đã theo dõi bạn', 'binh_luan'=>'đã bình luận bài viết của bạn', 'yeu_thich'=>'đã yêu thích bài viết của bạn'][$loai];
    ?>
        <p class="jh-card"><a href="<?= e($dich) ?>"><?= e($tb['ho_ten']) ?> <?= e($nhan) ?></a> · <?= e($tb['ngay_tao']) ?><?= $tb['da_doc'] ? '' : ' · Mới' ?></p>
    <?php endforeach; ?>
</main>
<?php require dirname(__DIR__, 2).'/inc/footer.php'; ?>

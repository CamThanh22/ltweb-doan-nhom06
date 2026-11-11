<?php
require dirname(__DIR__, 2).'/inc/config.php';
require dirname(__DIR__, 2).'/inc/nen-tang.php';
require dirname(__DIR__, 2).'/inc/xa-hoi.php';

$uid = (int)yeuCauDangNhap()['id'];
$doi = idDuong($_GET['id'] ?? null);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doi = idDuong($_POST['nguoi_nhan_id'] ?? null);
    $noiDung = layChuoi($_POST, 'noi_dung');
    $baiChiaSe = idDuong($_POST['bai_chia_se_id'] ?? null);
    $anh = anhXaHoi('anh');
    if (!$doi || $doi === $uid || mb_strlen($noiDung) > 2000 || ($noiDung === '' && $anh === null && !$baiChiaSe)) {
        saiDuLieu('Chọn người nhận và nhập nội dung hoặc đính kèm ảnh.');
    }
    $s = db()->prepare('SELECT 1 FROM nguoi_dung WHERE id=?');
    $s->execute([$doi]);
    if (!$s->fetchColumn()) {
        saiDuLieu('Người nhận không tồn tại.');
    }
    if ($baiChiaSe) {
        $s = db()->prepare('SELECT 1 FROM bai_viet_cong_dong WHERE id=?');
        $s->execute([$baiChiaSe]);
        if (!$s->fetchColumn()) {
            saiDuLieu('Bài viết không còn tồn tại.');
        }
    }
    db()->prepare('INSERT INTO tin_nhan (nguoi_gui_id,nguoi_nhan_id,noi_dung,anh,bai_chia_se_id) VALUES (?,?,?,?,?)')
        ->execute([$uid, $doi, $noiDung, $anh, $baiChiaSe ?: null]);
    chuyenTrang('tin-nhan.php?id='.$doi);
}

$tin = [];
$doiTen = '';
if ($doi) {
    if ($doi === $uid) saiDuLieu('Không thể nhắn cho chính mình.');
    $s = db()->prepare('SELECT ho_ten FROM nguoi_dung WHERE id=?');
    $s->execute([$doi]);
    $doiTen = $s->fetchColumn();
    if ($doiTen === false) {
        http_response_code(404);
        require dirname(__DIR__, 2).'/pages/loi/404.php';
        exit;
    }
    $s = db()->prepare('SELECT * FROM (
        SELECT t.id,t.nguoi_gui_id,t.noi_dung,t.ngay_gui,t.anh IS NOT NULL AS co_anh,t.bai_chia_se_id,b.tieu_de
        FROM tin_nhan t LEFT JOIN bai_viet_cong_dong b ON b.id=t.bai_chia_se_id
        WHERE (t.nguoi_gui_id=? AND t.nguoi_nhan_id=?) OR (t.nguoi_gui_id=? AND t.nguoi_nhan_id=?)
        ORDER BY t.id DESC LIMIT 100
    ) recent ORDER BY id');
    $s->execute([$uid,$doi,$doi,$uid]);
    $tin = $s->fetchAll();
    if ($tin) {
        db()->prepare('UPDATE tin_nhan SET da_doc=1 WHERE nguoi_gui_id=? AND nguoi_nhan_id=? AND da_doc=0 AND id<=?')
            ->execute([$doi,$uid,end($tin)['id']]);
    }
    if (isset($_GET['cap_nhat'])) {
        require dirname(__DIR__, 2).'/inc/doan-chat.php';
        exit;
    }
}

$s = db()->prepare('SELECT nd.id,nd.ho_ten,MAX(tn.id) AS cuoi,
    SUM(CASE WHEN tn.nguoi_nhan_id=? AND tn.da_doc=0 THEN 1 ELSE 0 END) AS chua_doc
    FROM tin_nhan tn JOIN nguoi_dung nd ON nd.id=IF(tn.nguoi_gui_id=?,tn.nguoi_nhan_id,tn.nguoi_gui_id)
    WHERE tn.nguoi_gui_id=? OR tn.nguoi_nhan_id=?
    GROUP BY nd.id,nd.ho_ten ORDER BY cuoi DESC LIMIT 30');
$s->execute([$uid,$uid,$uid,$uid]);
$hop = $s->fetchAll();
$s = db()->prepare('SELECT nd.id,nd.ho_ten FROM nguoi_dung nd
    WHERE nd.id<>? AND nd.vai_tro=?
      AND NOT EXISTS (SELECT 1 FROM tin_nhan t
         WHERE (t.nguoi_gui_id=? AND t.nguoi_nhan_id=nd.id)
            OR (t.nguoi_gui_id=nd.id AND t.nguoi_nhan_id=?))
    ORDER BY nd.id DESC LIMIT 8');
$s->execute([$uid,'khach_hang',$uid,$uid]);
$goiY = $s->fetchAll();
$tieuDe = 'Tin nhắn';
$cssThem = ['css/jadehub-social.css'];
$jsThem = ['js/jadehub-chat.js'];
require dirname(__DIR__, 2).'/inc/header.php';
?>
<main id="noi-dung" class="jh-page">
    <h1>Hộp thư JadeHub</h1>
    <div class="social-chat">
        <aside class="jh-card social-chat-list">
            <h2>Cuộc trò chuyện</h2>
            <?php if (!$hop): ?><p>Chưa có cuộc trò chuyện.</p><?php endif; ?>
            <?php foreach ($hop as $h): ?>
                <a href="tin-nhan.php?id=<?= (int)$h['id'] ?>"<?= (int)$h['id'] === $doi ? ' aria-current="page"' : '' ?>><?= e($h['ho_ten']) ?><?= $h['chua_doc'] ? ' · '.(int)$h['chua_doc'].' mới' : '' ?></a>
            <?php endforeach; ?>
            <?php if ($goiY): ?><h3>Thành viên gợi ý</h3><?php endif; ?>
            <?php foreach ($goiY as $h): ?>
                <a href="tin-nhan.php?id=<?= (int)$h['id'] ?>"><?= e($h['ho_ten']) ?></a>
            <?php endforeach; ?>
        </aside>
        <section class="jh-card social-chat-panel">
        <?php if ($doi): ?>
            <h2><a href="ho-so.php?id=<?= $doi ?>"><?= e($doiTen) ?></a></h2>
            <div class="social-chat-log" role="log" aria-label="Tin nhắn" data-chat-url="tin-nhan.php?id=<?= $doi ?>&amp;cap_nhat=1">
                <?php require dirname(__DIR__, 2).'/inc/doan-chat.php'; ?>
            </div>
            <form method="post" enctype="multipart/form-data" class="social-chat-compose">
                <?= csrfField() ?>
                <input type="hidden" name="nguoi_nhan_id" value="<?= $doi ?>">
                <label for="chat-noi-dung">Tin nhắn</label>
                <textarea id="chat-noi-dung" name="noi_dung" maxlength="2000" rows="2" placeholder="Viết tin nhắn…"></textarea>
                <label for="chat-anh">Đính kèm ảnh (tối đa 2 MB)</label>
                <input type="file" id="chat-anh" name="anh" accept="image/jpeg,image/png,image/webp">
                <button type="submit">Gửi</button>
                <p data-chat-status role="status"></p>
            </form>
        <?php else: ?>
            <p>Chọn một cuộc trò chuyện hoặc một thành viên gợi ý để nhắn tin.</p>
        <?php endif; ?>
        </section>
    </div>
</main>
<?php require dirname(__DIR__, 2).'/inc/footer.php'; ?>

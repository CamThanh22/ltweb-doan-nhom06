<?php
require dirname(__DIR__, 2).'/inc/config.php';require dirname(__DIR__, 2).'/inc/nen-tang.php';require dirname(__DIR__, 2).'/inc/xa-hoi.php';$nd=nguoiDung();$uid=(int)($nd['id']??0);
if($_SERVER['REQUEST_METHOD']==='POST'){
 $nd=yeuCauDangNhap();$uid=(int)$nd['id'];$hd=layChuoi($_POST,'hanh_dong');
 if($hd==='dang'){$title=layChuoi($_POST,'tieu_de');$noi=layChuoi($_POST,'noi_dung');$loai=layChuoi($_POST,'loai');if(mb_strlen($title)<5||mb_strlen($title)>180||mb_strlen($noi)<20||mb_strlen($noi)>10000||!in_array($loai,['thao_luan','nho_tham_dinh'],true)) saiDuLieu('Bài viết phải có tiêu đề 5–180 và nội dung 20–10000 ký tự.');$s=db()->prepare('INSERT INTO bai_viet_cong_dong(tac_gia_id,tieu_de,noi_dung,loai,anh) VALUES(?,?,?,?,?)');$s->execute([$uid,$title,$noi,$loai,anhXaHoi('anh')]);chuyenTrang('cong-dong.php?id='.db()->lastInsertId(),'Đã đăng bài.');}
 if($hd==='binh_luan'){$bai=idDuong($_POST['bai_viet_id']??null);$text=layChuoi($_POST,'noi_dung');if(!$bai||mb_strlen($text)<2||mb_strlen($text)>2000)saiDuLieu('Bình luận phải từ 2–2000 ký tự.');$s=db()->prepare('INSERT INTO binh_luan_cong_dong(bai_viet_id,tac_gia_id,noi_dung) VALUES(?,?,?)');try{$s->execute([$bai,$uid,$text]);}catch(PDOException $e){if($e->getCode()==='23000')saiDuLieu('Bài viết không tồn tại.');throw $e;}$s=db()->prepare('SELECT tac_gia_id FROM bai_viet_cong_dong WHERE id=?');$s->execute([$bai]);$tacGia=(int)$s->fetchColumn();if($tacGia) baoHoatDong($tacGia,$uid,'binh_luan',$bai);chuyenTrang('cong-dong.php?id='.$bai.'#binh-luan','Đã gửi bình luận.');}
 if (in_array($hd, ['yeu_thich','bo_yeu_thich'], true)) {
    $bai = idDuong($_POST['bai_viet_id'] ?? null);
    $s = db()->prepare('SELECT tac_gia_id FROM bai_viet_cong_dong WHERE id=?');
    $s->execute([$bai]);
    $tacGia = $s->fetchColumn();
    if ($tacGia === false) saiDuLieu('Bài viết không còn tồn tại.');
    if ($hd === 'yeu_thich') {
        $s = db()->prepare('INSERT IGNORE INTO yeu_thich_bai_viet (nguoi_dung_id,bai_viet_id) VALUES (?,?)');
        $s->execute([$uid,$bai]);
        if ($s->rowCount()) baoHoatDong((int)$tacGia,$uid,'yeu_thich',$bai);
    } else {
        db()->prepare('DELETE FROM yeu_thich_bai_viet WHERE nguoi_dung_id=? AND bai_viet_id=?')->execute([$uid,$bai]);
    }
    chuyenTrang('cong-dong.php?id='.$bai);
 }
 if($hd==='xoa'){$bai=idDuong($_POST['bai_viet_id']??null);$s=db()->prepare('DELETE FROM bai_viet_cong_dong WHERE id=?'.(laAdminHienTai()?'':' AND tac_gia_id=?'));$s->execute(laAdminHienTai()?[$bai]:[$bai,$uid]);chuyenTrang('cong-dong.php','Đã xử lý bài viết.');}
 saiDuLieu('Thao tác không hợp lệ.');
}
$id=idDuong($_GET['id']??null);$bai=null;$binhLuan=[];
if($id){$s=db()->prepare('SELECT b.id,b.tac_gia_id,b.tieu_de,b.noi_dung,b.loai,b.ngay_tao,b.anh IS NOT NULL AS co_anh,nd.ho_ten FROM bai_viet_cong_dong b JOIN nguoi_dung nd ON nd.id=b.tac_gia_id WHERE b.id=?');$s->execute([$id]);$bai=$s->fetch();if(!$bai){http_response_code(404);require dirname(__DIR__, 2).'/pages/loi/404.php';exit;}$s=db()->prepare('SELECT bl.*,nd.ho_ten FROM binh_luan_cong_dong bl JOIN nguoi_dung nd ON nd.id=bl.tac_gia_id WHERE bl.bai_viet_id=? ORDER BY bl.id LIMIT 200');$s->execute([$id]);$binhLuan=$s->fetchAll();}
if ($bai) {
    $s = db()->prepare('SELECT COUNT(*) AS so_thich, COUNT(CASE WHEN nguoi_dung_id=? THEN 1 END) AS da_thich FROM yeu_thich_bai_viet WHERE bai_viet_id=?');
    $s->execute([$uid,$id]);
    $bai += $s->fetch();
    $ds = [];
} else {
$s=db()->prepare('SELECT b.id,b.tieu_de,b.noi_dung,b.loai,b.ngay_tao,b.anh IS NOT NULL AS co_anh,nd.ho_ten,nd.id AS tac_gia_id, (SELECT COUNT(*) FROM binh_luan_cong_dong bl WHERE bl.bai_viet_id=b.id) AS so_binh_luan, (SELECT COUNT(*) FROM yeu_thich_bai_viet y WHERE y.bai_viet_id=b.id) AS so_thich, EXISTS (SELECT 1 FROM yeu_thich_bai_viet y WHERE y.bai_viet_id=b.id AND y.nguoi_dung_id=?) AS da_thich FROM bai_viet_cong_dong b JOIN nguoi_dung nd ON nd.id=b.tac_gia_id ORDER BY b.id DESC LIMIT 50');
$s->execute([$uid]);
$ds=$s->fetchAll();
}
$trang='cong-dong';
$tieuDe='Câu chuyện người yêu ngọc';
$cssThem=['css/jadehub-community.css?v=2','css/jadehub-social.css'];
$jsThem=['js/jadehub-community.js'];
function ngayCongDong(string $ngay): string {
    $date=DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $ngay);
    return $date ? $date->format('d/m/Y') : $ngay;
}
require dirname(__DIR__, 2).'/inc/header.php';
?>
<main id="noi-dung" class="jh-community">
<p class="social-actions"><a href="thanh-vien.php">Tìm thành viên & shop · Xem đánh giá</a><a href="tin-nhan.php">Hộp thư riêng</a></p>
    <header class="community-heading">
        <div><span class="jh-eyebrow">GÓC KẾT NỐI JADEHUB</span><h1>Câu chuyện người yêu ngọc</h1>
        <p>Không gian gặp gỡ, chia sẻ trải nghiệm và cùng tìm hiểu vẻ đẹp của ngọc tự nhiên.</p></div>
        <a class="jh-btn jh-btn--solid" href="cong-dong.php?viet=1#viet-bai" data-community-open>Chia sẻ câu chuyện <span aria-hidden="true">＋</span></a>
    </header>
    <?php require dirname(__DIR__, 2).'/inc/thong-bao.php'; ?>
    <?php if ($bai): ?>
    <div class="community-reading">
        <a class="community-back" href="cong-dong.php">← Tất cả câu chuyện</a>
        <article class="community-story community-story--full">
            <p class="community-meta"><?= $bai['loai']==='nho_tham_dinh'?'NHỜ CỘNG ĐỒNG NHẬN XÉT':'CHIA SẺ CỘNG ĐỒNG' ?> · <?= e(ngayCongDong((string)$bai['ngay_tao'])) ?></p>
            <h2><?= e($bai['tieu_de']) ?></h2>
            <div class="community-content"><?= nl2br(e($bai['noi_dung'])) ?></div>
            <?php $baiHienThi = $bai; require dirname(__DIR__, 2).'/inc/tuong-tac-bai.php'; ?>
            <p class="community-author">— <a href="ho-so.php?id=<?= (int)$bai['tac_gia_id'] ?>"><?= e($bai['ho_ten']) ?></a></p>
            <?php if ($uid && ($uid===(int)$bai['tac_gia_id'] || laAdminHienTai())): ?>
            <form method="post" class="community-delete"><?= csrfField() ?><input type="hidden" name="hanh_dong" value="xoa"><input type="hidden" name="bai_viet_id" value="<?= $id ?>"><button class="nut-phu" type="submit">Xóa bài viết</button></form>
            <?php endif; ?>
        </article>
        <section id="binh-luan" class="community-comments" aria-labelledby="comments-heading">
            <h2 id="comments-heading">Cuộc trò chuyện <span>(<?= count($binhLuan) ?><?= count($binhLuan)===200?'+':'' ?>)</span></h2>
            <?php foreach ($binhLuan as $bl): ?>
            <article class="community-comment"><p><a href="ho-so.php?id=<?= (int)$bl['tac_gia_id'] ?>"><?= e($bl['ho_ten']) ?></a><time><?= e(ngayCongDong((string)$bl['ngay_tao'])) ?></time></p><div><?= nl2br(e($bl['noi_dung'])) ?></div></article>
            <?php endforeach; ?>
            <?php if (!$binhLuan): ?><p class="community-muted">Chưa có bình luận. Hãy bắt đầu cuộc trò chuyện.</p><?php endif; ?>
            <?php if ($uid): ?>
            <form method="post" class="community-comment-form"><?= csrfField() ?><input type="hidden" name="hanh_dong" value="binh_luan"><input type="hidden" name="bai_viet_id" value="<?= $id ?>"><label for="community-comment">Chia sẻ suy nghĩ của bạn</label><textarea id="community-comment" name="noi_dung" required minlength="2" maxlength="2000" rows="4" placeholder="Viết bình luận…"></textarea><button type="submit">Gửi bình luận →</button></form>
            <?php else: ?><a class="community-text-link" href="dang-nhap.php">Đăng nhập để bình luận →</a><?php endif; ?>
        </section>
    </div>
    <?php elseif ($ds): ?>
    <section class="community-feed" aria-label="Những câu chuyện mới nhất">
        <?php foreach ($ds as $d): ?>
        <article class="community-story">
            <p class="community-meta"><?= $d['loai']==='nho_tham_dinh'?'NHỜ CỘNG ĐỒNG NHẬN XÉT':'CHIA SẺ CỘNG ĐỒNG' ?> · <?= e(ngayCongDong((string)$d['ngay_tao'])) ?></p>
            <h2><a href="cong-dong.php?id=<?= (int)$d['id'] ?>"><?= e($d['tieu_de']) ?></a></h2>
            <div class="community-excerpt"><?= nl2br(e(mb_substr((string)$d['noi_dung'],0,350))) ?><?= mb_strlen((string)$d['noi_dung'])>350?'…':'' ?></div>
            <?php $baiHienThi = $d; require dirname(__DIR__, 2).'/inc/tuong-tac-bai.php'; ?>
            <p class="community-author">— <a href="ho-so.php?id=<?= (int)$d['tac_gia_id'] ?>"><?= e($d['ho_ten']) ?></a></p>
            <div class="community-story-footer"><a href="cong-dong.php?id=<?= (int)$d['id'] ?>#binh-luan"><?= (int)$d['so_binh_luan'] ?> bình luận</a><a href="cong-dong.php?id=<?= (int)$d['id'] ?>">Đọc câu chuyện <span aria-hidden="true">↗</span></a></div>
        </article>
        <?php endforeach; ?>
    </section>
    <?php else: ?>
    <section class="community-empty" aria-labelledby="empty-heading"><svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" aria-hidden="true"><path d="m3 8 4-5h10l4 5-9 13L3 8Zm0 0h18M7 3l2 5 3 13 3-13 2-5"/></svg><h2 id="empty-heading">Mỗi hành trình đều có câu chuyện đầu tiên.</h2><p>Hãy chia sẻ điều bạn yêu ở ngọc tự nhiên.</p></section>
    <?php endif; ?>
    <dialog class="community-dialog" id="viet-bai" aria-labelledby="compose-heading"<?= isset($_GET['viet'])?' open':'' ?>>
        <a class="community-close" href="cong-dong.php" data-community-close aria-label="Đóng biểu mẫu">×</a>
        <span class="jh-eyebrow">GÓC CHIA SẺ</span><h2 id="compose-heading">Câu chuyện của bạn</h2>
        <?php if ($uid): ?>
        <form method="post" action="cong-dong.php" enctype="multipart/form-data" class="community-compose"><?= csrfField() ?><input type="hidden" name="hanh_dong" value="dang">
            <p class="community-muted">Chia sẻ với tên <strong><?= e($nd['ho_ten']) ?></strong></p>
            <label for="community-type">Chủ đề</label><select name="loai" id="community-type"><option value="thao_luan">Chia sẻ câu chuyện</option><option value="nho_tham_dinh">Nhờ cộng đồng nhận xét</option></select>
            <label for="community-title">Tiêu đề</label><input id="community-title" name="tieu_de" minlength="5" maxlength="180" required placeholder="Đặt tên cho câu chuyện của bạn">
            <label for="community-body">Nội dung</label><textarea id="community-body" name="noi_dung" minlength="20" maxlength="10000" required rows="6" placeholder="Kể về viên ngọc, trải nghiệm hoặc điều bạn đang tìm hiểu…"></textarea>
            <label for="community-image">Đính kèm ảnh (tối đa 2 MB)</label>
            <input id="community-image" type="file" name="anh" accept="image/jpeg,image/png,image/webp">
            <p class="community-muted">Bài chia sẻ hiển thị trong cộng đồng. Nhận xét của thành viên không thay thế kết quả giám định chuyên môn.</p><button type="submit">Đăng chia sẻ <span aria-hidden="true">↗</span></button>
        </form>
        <?php else: ?><p>Đăng nhập để chia sẻ câu chuyện và trò chuyện cùng cộng đồng yêu ngọc.</p><a class="jh-btn jh-btn--solid community-login" href="dang-nhap.php">Đăng nhập để chia sẻ →</a><p class="community-muted">Chưa có tài khoản? <a href="dang-ky.php">Tạo tài khoản</a></p><?php endif; ?>
    </dialog>
</main>
<?php require dirname(__DIR__, 2).'/inc/footer.php'; ?>

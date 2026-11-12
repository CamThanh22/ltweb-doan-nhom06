<?php
require dirname(__DIR__, 2).'/inc/config.php';require dirname(__DIR__, 2).'/inc/nen-tang.php';require dirname(__DIR__, 2).'/inc/xa-hoi.php';$nd=nguoiDung();$uid=(int)($nd['id']??0);$id=idDuong($_GET['id']??($uid?:null));if(!$id){header('Location: dang-nhap.php');exit;}
$s=db()->prepare('SELECT nd.id,nd.ho_ten,nd.ngay_tao,nd.tieu_su,nd.anh_dai_dien IS NOT NULL AS co_dai_dien,nd.anh_bia IS NOT NULL AS co_bia,h.ten_gian_hang,h.gioi_thieu FROM nguoi_dung nd LEFT JOIN ho_so_nguoi_ban h ON h.nguoi_dung_id=nd.id AND h.trang_thai=\'da_duyet\' WHERE nd.id=?');$s->execute([$id]);$hoSo=$s->fetch();if(!$hoSo){http_response_code(404);require dirname(__DIR__, 2).'/pages/loi/404.php';exit;}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = (int)yeuCauDangNhap()['id'];
    $hd = layChuoi($_POST, 'hanh_dong');
    if ($hd === 'sua_ho_so') {
        if ($uid !== $id) {
            http_response_code(403);
            exit;
        }
        $ten = layChuoi($_POST, 'ho_ten');
        $tieuSu = layChuoi($_POST, 'tieu_su');
        if ($ten === '' || mb_strlen($ten) > 100 || mb_strlen($tieuSu) > 500) {
            saiDuLieu('Tên cần từ 1 đến 100 ký tự. Tiểu sử tối đa 500 ký tự.');
        }
        $daiDien = anhXaHoi('anh_dai_dien');
        $bia = anhXaHoi('anh_bia');
        db()->prepare('UPDATE nguoi_dung SET ho_ten=?, tieu_su=?, anh_dai_dien=COALESCE(?,anh_dai_dien), anh_bia=COALESCE(?,anh_bia) WHERE id=?')
            ->execute([$ten,$tieuSu,$daiDien,$bia,$uid]);
        chuyenTrang('ho-so.php?id='.$uid, 'Đã cập nhật trang cá nhân.');
    }
    if ($id === $uid) saiDuLieu('Không thể theo dõi chính mình.');
    if ($hd === 'theo_doi') {
        $s = db()->prepare('INSERT IGNORE INTO theo_doi (nguoi_theo_doi_id,nguoi_duoc_theo_doi_id) VALUES(?,?)');
        $s->execute([$uid,$id]);
        if ($s->rowCount()) baoHoatDong($id,$uid,'theo_doi',$id);
    } elseif ($hd === 'bo_theo_doi') {
        db()->prepare('DELETE FROM theo_doi WHERE nguoi_theo_doi_id=? AND nguoi_duoc_theo_doi_id=?')->execute([$uid,$id]);
    } else {
        saiDuLieu('Thao tác không hợp lệ.');
    }
    chuyenTrang('ho-so.php?id='.$id);
}
$s=db()->prepare('SELECT COUNT(*) FROM theo_doi WHERE nguoi_duoc_theo_doi_id=?');$s->execute([$id]);$dem=(int)$s->fetchColumn();$daTheo=false;if($uid){$s=db()->prepare('SELECT 1 FROM theo_doi WHERE nguoi_theo_doi_id=? AND nguoi_duoc_theo_doi_id=?');$s->execute([$uid,$id]);$daTheo=(bool)$s->fetchColumn();}
$s=db()->prepare('SELECT id,tieu_de FROM bai_viet_cong_dong WHERE tac_gia_id=? ORDER BY id DESC LIMIT 20');$s->execute([$id]);$ds=$s->fetchAll();$tieuDe='Hồ sơ thành viên';$cssThem=['css/jadehub-social.css'];require dirname(__DIR__, 2).'/inc/header.php';?>
<main id="noi-dung" class="jh-page social-profile">
    <?php require dirname(__DIR__, 2).'/inc/thong-bao.php'; ?>
    <div class="social-cover">
        <?php if ($hoSo['co_bia']): ?><img src="anh-xa-hoi.php?loai=bia&amp;id=<?= $id ?>" alt="Ảnh bìa"><?php endif; ?>
    </div>
    <div class="social-profile-head">
        <?php if ($hoSo['co_dai_dien']): ?>
            <img class="social-avatar" src="anh-xa-hoi.php?loai=dai-dien&amp;id=<?= $id ?>" alt="Ảnh đại diện">
        <?php else: ?>
            <span class="social-avatar" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($hoSo['ho_ten'],0,1))) ?></span>
        <?php endif; ?>
        <h1><?= e($hoSo['ho_ten']) ?></h1>
        <p><?= $dem ?> người theo dõi · Tham gia <?= e(substr($hoSo['ngay_tao'],0,10)) ?></p>
        <?php if ($uid === $id): ?>
            <div class="social-actions"><a class="jh-btn jh-btn--solid" href="ho-so.php?id=<?= $id ?>&amp;sua=1#chinh-sua">Chỉnh sửa trang cá nhân</a>
            <a class="jh-btn jh-btn--outline" href="cong-dong.php?viet=1#viet-bai">Đăng bài</a></div>
        <?php elseif ($uid): ?>
            <div class="social-actions">
                <form method="post" action="ho-so.php?id=<?= $id ?>"><?= csrfField() ?>
                    <input type="hidden" name="hanh_dong" value="<?= $daTheo ? 'bo_theo_doi' : 'theo_doi' ?>">
                    <button type="submit"><?= $daTheo ? 'Bỏ theo dõi' : 'Theo dõi' ?></button>
                </form>
                <a class="jh-btn jh-btn--outline" href="tin-nhan.php?id=<?= $id ?>">Nhắn tin / trao đổi giao dịch</a>
            </div>
        <?php else: ?><a class="jh-btn jh-btn--outline" href="tin-nhan.php?id=<?= $id ?>">Đăng nhập để nhắn tin</a>
        <?php endif; ?>
    </div>
    <?php if ($uid === $id && isset($_GET['sua'])): ?>
    <section id="chinh-sua" class="jh-card">
        <h2>Chỉnh sửa trang cá nhân</h2>
        <form method="post" action="ho-so.php?id=<?= $id ?>" enctype="multipart/form-data">
            <?= csrfField() ?>
            <input type="hidden" name="hanh_dong" value="sua_ho_so">
            <label for="ho-ten">Tên hiển thị</label><input id="ho-ten" name="ho_ten" required maxlength="100" value="<?= e($hoSo['ho_ten']) ?>">
            <label for="tieu-su">Tiểu sử</label><textarea id="tieu-su" name="tieu_su" maxlength="500" rows="3"><?= e($hoSo['tieu_su']) ?></textarea>
            <label for="anh-dai-dien">Ảnh đại diện (tối đa 2 MB)</label><input id="anh-dai-dien" type="file" name="anh_dai_dien" accept="image/jpeg,image/png,image/webp">
            <label for="anh-bia">Ảnh bìa (tối đa 2 MB)</label><input id="anh-bia" type="file" name="anh_bia" accept="image/jpeg,image/png,image/webp">
            <button type="submit">Lưu thay đổi</button> <a href="ho-so.php?id=<?= $id ?>">Hủy</a>
        </form>
    </section>
    <?php endif; ?>
    <div class="jh-grid">
        <section class="jh-card"><h2>Giới thiệu</h2>
            <p><?= $hoSo['tieu_su'] !== '' ? nl2br(e($hoSo['tieu_su'])) : 'Chưa có tiểu sử.' ?></p>
            <?php if ($hoSo['ten_gian_hang']): ?><p>Gian hàng <strong><?= e($hoSo['ten_gian_hang']) ?></strong></p><p><?= e($hoSo['gioi_thieu']) ?></p><?php endif; ?>
        </section>
        <section class="jh-card"><h2>Bài viết</h2>
            <?php if (!$ds): ?><p>Chưa có bài viết.</p><?php endif; ?>
            <?php foreach ($ds as $d): ?><p><a href="cong-dong.php?id=<?= (int)$d['id'] ?>"><?= e($d['tieu_de']) ?></a></p><?php endforeach; ?>
        </section>
    </div>
<?php require dirname(__DIR__,2).'/inc/ho-so-uy-tin.php'; ?>
</main>
<?php require dirname(__DIR__, 2).'/inc/footer.php'; ?>

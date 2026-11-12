<?php
require dirname(__DIR__,2).'/inc/config.php';
require dirname(__DIR__,2).'/inc/giao-dich.php';
yeuCauQuyen('quan_tri');
$s=db()->query("SELECT ht.*,dh.nguoi_dung_id,dh.ly_do_huy,nd.ho_ten,tt.ma_tham_chieu FROM hoan_tien ht JOIN don_hang dh ON dh.id=ht.don_hang_id JOIN nguoi_dung nd ON nd.id=dh.nguoi_dung_id JOIN thanh_toan tt ON tt.don_hang_id=dh.id ORDER BY FIELD(ht.trang_thai,'cho_hoan','can_doi_soat','khong_phat_sinh','da_hoan'),ht.ngay_tao DESC LIMIT 200");
$refunds=$s->fetchAll();$tieuDe='Quản lý hoàn tiền';require dirname(__DIR__,2).'/inc/header.php';?>
<main id="noi-dung" class="jh-page"><h1>Quản lý hoàn tiền</h1>
<?php require dirname(__DIR__,2).'/inc/thong-bao.php';?>
<p>Chuyển trả tiền qua ngân hàng trước khi xác nhận “Đã hoàn”. JadeHub lưu kết quả đối soát, không tự thực hiện chuyển tiền.</p>
<?php if(!$refunds): ?><p>Chưa có hồ sơ hoàn tiền.</p><?php endif;?>
<?php foreach($refunds as $rf): ?><article class="jh-card jh-refund">
<h2><a href="don-hang.php?id=<?= (int)$rf['don_hang_id'] ?>">Đơn #<?= (int)$rf['don_hang_id'] ?></a> · <?= e(vnd((int)$rf['so_tien'])) ?></h2>
<p><strong><?= e(NHAN_HOAN_TIEN[$rf['trang_thai']]) ?></strong> · <?= e($rf['ho_ten']) ?> · <a href="tin-nhan.php?id=<?= (int)$rf['nguoi_dung_id'] ?>">Nhắn người mua</a></p>
<p>Lý do hủy: <?= e($rf['ly_do_huy']??'') ?></p><p>Mã chuyển tiền của khách: <?= e($rf['ma_tham_chieu']??'Chưa có') ?></p>
<p>Thông tin nhận hoàn tiền: <?= nl2br(e($rf['tai_khoan_nhan']?:'Khách chưa cung cấp')) ?></p>
<?php if(in_array($rf['trang_thai'],['can_doi_soat','cho_hoan'],true)): ?>
<form method="post" action="hau-mai.php"><?= csrfField() ?><input type="hidden" name="hanh_dong" value="xu_ly_hoan"><input type="hidden" name="don_hang_id" value="<?= (int)$rf['don_hang_id'] ?>">
<?php if($rf['trang_thai']==='can_doi_soat'): ?><label>Kết quả đối soát<select name="xu_ly"><option value="da_nhan_tien">Đã nhận tiền — chuyển sang chờ hoàn</option><option value="chua_nhan_tien">Chưa ghi nhận tiền đã nhận</option></select></label>
<?php else: ?><input type="hidden" name="xu_ly" value="da_hoan"><label>Mã giao dịch hoàn tiền thực tế<input name="ma_giao_dich" minlength="6" maxlength="100" required></label><?php endif;?>
<label>Ghi chú đối soát<textarea name="ghi_chu" maxlength="1000" minlength="10" required></textarea></label>
<button type="submit"><?= $rf['trang_thai']==='cho_hoan'?'Xác nhận đã chuyển trả đủ tiền':'Lưu kết quả đối soát' ?></button></form>
<?php else: ?><p>Mã hoàn: <?= e($rf['ma_giao_dich']??'—') ?> · <?= e($rf['ngay_hoan']??'') ?></p><p><?= e($rf['ghi_chu']) ?></p><?php endif;?>
</article><?php endforeach;?></main><?php require dirname(__DIR__,2).'/inc/footer.php';?>

<?php
// Included only after order ownership/admin authorization in don-hang.php.
$s=db()->prepare('SELECT * FROM hoan_tien WHERE don_hang_id=?');$s->execute([(int)$donChiTiet['id']]);$refund=$s->fetch();
$owned=(int)$donChiTiet['nguoi_dung_id']===$uid;
?>
<section class="jh-card jh-after-sales" aria-label="Hủy đơn và hoàn tiền">
<?php if($owned && $donChiTiet['trang_thai']==='cho_xac_nhan'): ?>
<details><summary>Hủy đơn hàng này</summary>
<p>Đơn chưa giao cho vận chuyển nên có thể hủy. Tiền đã được xác nhận sẽ chuyển sang hồ sơ chờ hoàn; không hoàn tự động qua ngân hàng.</p>
<form method="post" action="hau-mai.php"><?= csrfField() ?><input type="hidden" name="hanh_dong" value="huy_don"><input type="hidden" name="don_hang_id" value="<?= (int)$donChiTiet['id'] ?>">
<label>Lý do hủy<textarea name="ly_do" minlength="5" maxlength="500" required></textarea></label><button type="submit">Xác nhận hủy đơn</button></form></details>
<?php elseif($owned && $donChiTiet['trang_thai']==='dang_giao'): ?><p>Đơn đang giao, không thể tự hủy. Nhắn shop để trao đổi về giao nhận.</p><?php endif;?>
<?php if($donChiTiet['trang_thai']==='da_huy'): ?><p><strong>Đơn đã hủy.</strong> <?= e($donChiTiet['ly_do_huy']??'') ?></p><?php endif;?>
<?php if($refund): ?>
<h3>Hoàn tiền · <?= e(NHAN_HOAN_TIEN[$refund['trang_thai']]) ?></h3>
<p>Số tiền <?= $refund['trang_thai']==='can_doi_soat'?'cần đối soát':'trong hồ sơ' ?>: <strong><?= e(vnd((int)$refund['so_tien'])) ?></strong></p>
<?php if($refund['trang_thai']==='da_hoan'): ?><p>Mã giao dịch hoàn: <strong><?= e($refund['ma_giao_dich']) ?></strong> · <?= e($refund['ngay_hoan']) ?></p><?php endif;?>
<?php if($refund['ghi_chu']): ?><p><?= nl2br(e($refund['ghi_chu'])) ?></p><?php endif;?>
<?php if($owned && in_array($refund['trang_thai'],['cho_hoan','can_doi_soat'],true)): ?>
<form method="post" action="hau-mai.php"><?= csrfField() ?><input type="hidden" name="hanh_dong" value="tai_khoan_hoan"><input type="hidden" name="don_hang_id" value="<?= (int)$donChiTiet['id'] ?>">
<label>Ngân hàng, số tài khoản và tên người nhận<textarea name="tai_khoan_nhan" minlength="10" maxlength="500" required><?= e($refund['tai_khoan_nhan']) ?></textarea></label>
<p>Thông tin này chỉ hiển thị cho bạn và quản trị xử lý hoàn tiền.</p><button type="submit">Lưu thông tin nhận tiền</button></form>
<?php endif;?>
<?php if($laAdmin): ?><p><a href="hoan-tien.php">Xử lý hồ sơ hoàn tiền →</a></p><?php endif;?>
<?php endif;?>
<?php if($owned && $donChiTiet['trang_thai']==='da_huy' && ($thanhToan['phuong_thuc']??'')==='chuyen_khoan' && (!$refund || $refund['trang_thai']==='khong_phat_sinh')): ?>
<details><summary>Tôi đã chuyển tiền, cần đối soát để hoàn</summary><form method="post" action="hau-mai.php"><?= csrfField() ?>
<input type="hidden" name="hanh_dong" value="bao_da_chuyen"><input type="hidden" name="don_hang_id" value="<?= (int)$donChiTiet['id'] ?>">
<label>Mã giao dịch chuyển tiền<input name="ma_tham_chieu" minlength="6" maxlength="100" required></label><button type="submit">Gửi yêu cầu đối soát</button></form></details>
<?php endif;?>
</section>

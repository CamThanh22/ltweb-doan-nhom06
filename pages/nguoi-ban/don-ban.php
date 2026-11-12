<?php
require dirname(__DIR__,2).'/inc/config.php';require dirname(__DIR__,2).'/inc/don-hang.php';
$uid=(int)yeuCauDangNhap()['id'];
$s=db()->prepare('SELECT dh.id,dh.ngay_dat,dh.trang_thai,dh.nguoi_dung_id,nd.ho_ten,ct.san_pham_id,ct.so_luong,ct.don_gia,sp.ten FROM chi_tiet_don ct JOIN don_hang dh ON dh.id=ct.don_hang_id JOIN nguoi_dung nd ON nd.id=dh.nguoi_dung_id JOIN san_pham sp ON sp.id=ct.san_pham_id WHERE ct.nguoi_ban_id=? ORDER BY dh.id DESC LIMIT 200');$s->execute([$uid]);$items=$s->fetchAll();
$tieuDe='Đơn bán của shop';require dirname(__DIR__,2).'/inc/header.php';?>
<main id="noi-dung" class="jh-page"><h1>Đơn bán của shop</h1><p><a href="nguoi-ban.php">← Trung tâm người bán</a></p>
<p>Chỉ hiển thị sản phẩm thuộc shop trong từng đơn. Quản trị điều phối giao hàng và hoàn tiền cho đơn chung.</p>
<?php if(!$items): ?><p>Chưa có đơn bán.</p><?php endif;?>
<?php foreach($items as $it): ?><article class="jh-card"><h2>Đơn #<?= (int)$it['id'] ?> · <?= e($it['ten']) ?></h2><p><?= e(TRANG_THAI_DON[$it['trang_thai']]) ?> · <?= e($it['ngay_dat']) ?></p>
<p><?= (int)$it['so_luong'] ?> sản phẩm · <?= e(vnd((int)$it['so_luong']*(int)$it['don_gia'])) ?></p>
<p>Người mua: <a href="ho-so.php?id=<?= (int)$it['nguoi_dung_id'] ?>"><?= e($it['ho_ten']) ?> · Xem đánh giá</a></p>
<div class="social-actions"><a href="tin-nhan.php?id=<?= (int)$it['nguoi_dung_id'] ?>">Nhắn người mua</a>
<?php if($it['trang_thai']==='hoan_thanh'): ?><a href="danh-gia.php?don=<?= (int)$it['id'] ?>&amp;sp=<?= (int)$it['san_pham_id'] ?>">Đánh giá người mua</a><?php endif;?></div></article><?php endforeach;?></main>
<?php require dirname(__DIR__,2).'/inc/footer.php';?>

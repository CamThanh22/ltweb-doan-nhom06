<?php
require_once __DIR__.'/giao-dich.php';
$sellerStats=thongKeDanhGia($id,'san_pham');$buyerStats=thongKeDanhGia($id,'nguoi_mua');
$s=db()->prepare("SELECT COUNT(DISTINCT dh.id) FROM chi_tiet_don ct JOIN don_hang dh ON dh.id=ct.don_hang_id WHERE ct.nguoi_ban_id=? AND dh.trang_thai='hoan_thanh'");$s->execute([$id]);$sold=(int)$s->fetchColumn();
$s=db()->prepare("SELECT COUNT(*) FROM don_hang WHERE nguoi_dung_id=? AND trang_thai='hoan_thanh'");$s->execute([$id]);$bought=(int)$s->fetchColumn();
$type=layChuoi($_GET,'vai_tro_dg')==='nguoi_mua'?'nguoi_mua':'san_pham';
$total=(int)($type==='san_pham'?$sellerStats['so_danh_gia']:$buyerStats['so_danh_gia']);
$page=max(1,min((int)ceil(max(1,$total)/10),(int)(filter_var($_GET['trang_dg']??1,FILTER_VALIDATE_INT)?:1)));
$s=db()->prepare('SELECT dg.*,nd.ho_ten AS ten_nguoi_viet,sp.ten AS ten_san_pham FROM danh_gia_giao_dich dg JOIN nguoi_dung nd ON nd.id=dg.nguoi_viet_id JOIN san_pham sp ON sp.id=dg.san_pham_id WHERE dg.nguoi_nhan_id=? AND dg.loai=? ORDER BY dg.id DESC LIMIT 10 OFFSET ?');$s->execute([$id,$type,($page-1)*10]);$reviews=$s->fetchAll();
?>
<section id="danh-gia" class="jh-card jh-trust"><h2>Đánh giá & lịch sử giao dịch</h2>
<div class="jh-trust-grid">
<div><strong><?= $sellerStats['diem']!==null?e($sellerStats['diem']).'/5':'Chưa có điểm' ?></strong><span>Người bán · <?= (int)$sellerStats['so_danh_gia'] ?> đánh giá từ <?= (int)$sellerStats['so_nguoi'] ?> người mua</span></div>
<div><strong><?= $buyerStats['diem']!==null?e($buyerStats['diem']).'/5':'Chưa có điểm' ?></strong><span>Người mua · <?= (int)$buyerStats['so_danh_gia'] ?> đánh giá từ <?= (int)$buyerStats['so_nguoi'] ?> người bán</span></div>
<div><strong><?= $sold ?> đơn bán · <?= $bought ?> đơn mua</strong><span>Đã hoàn thành trên JadeHub</span></div></div>
<?php if($hoSo['ten_gian_hang']): ?><p class="jh-verified">Hồ sơ người bán đã được quản trị duyệt.</p><?php endif;?>
<p>Điểm được tính từ các đánh giá có giao dịch hoàn thành, không phải cam kết bảo đảm của JadeHub. Hãy đọc nội dung nhận xét và kiểm tra chứng thư trước khi giao dịch.</p>
<nav class="social-actions jh-pagination" aria-label="Loại đánh giá"><a href="ho-so.php?id=<?= $id ?>&amp;vai_tro_dg=san_pham#danh-gia">Đánh giá shop/người bán</a><a href="ho-so.php?id=<?= $id ?>&amp;vai_tro_dg=nguoi_mua#danh-gia">Đánh giá người mua</a></nav>
<?php if(!$reviews): ?><p>Chưa có đánh giá ở vai trò này. Chưa đủ dữ liệu để kết luận về độ tin cậy.</p><?php endif;?>
<?php foreach($reviews as $review)require __DIR__.'/the-danh-gia.php';?>
<?php if($page>1): ?><a href="ho-so.php?id=<?= $id ?>&amp;vai_tro_dg=<?= e($type) ?>&amp;trang_dg=<?= $page-1 ?>#danh-gia">← Trước</a><?php endif;?>
<?php if($page*10<$total): ?><a href="ho-so.php?id=<?= $id ?>&amp;vai_tro_dg=<?= e($type) ?>&amp;trang_dg=<?= $page+1 ?>#danh-gia">Tiếp →</a><?php endif;?>
</section>
<?php
$s=db()->prepare('SELECT id,ten,gia,ton_kho FROM san_pham WHERE nguoi_ban_id=? ORDER BY id DESC LIMIT 24');$s->execute([$id]);$products=$s->fetchAll();
if($products): ?>
<section class="jh-card"><h2>Sản phẩm mới nhất của shop</h2><div class="jh-grid"><?php foreach($products as $product): ?><article><h3><a href="chi-tiet.php?id=<?= (int)$product['id'] ?>"><?= e($product['ten']) ?></a></h3><p><?= e(vnd((int)$product['gia'])) ?> · <?= (int)$product['ton_kho'] ?> còn lại</p></article><?php endforeach;?></div></section>
<?php endif;?>

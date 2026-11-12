<?php
$s=db()->prepare('SELECT COUNT(*) AS n,ROUND(AVG(sao),1) AS diem FROM danh_gia_giao_dich WHERE san_pham_id=? AND loai=\'san_pham\'');$s->execute([$sp->id]);$stats=$s->fetch();
$total=(int)$stats['n'];$page=max(1,min((int)ceil(max(1,$total)/10),(int)(filter_var($_GET['trang_dg']??1,FILTER_VALIDATE_INT)?:1)));
$s=db()->prepare("SELECT dg.*,nd.ho_ten AS ten_nguoi_viet FROM danh_gia_giao_dich dg JOIN nguoi_dung nd ON nd.id=dg.nguoi_viet_id WHERE dg.san_pham_id=? AND dg.loai='san_pham' ORDER BY dg.id DESC LIMIT 10 OFFSET ?");$s->execute([$sp->id,($page-1)*10]);$reviews=$s->fetchAll();
?>
<section class="jh-card" id="danh-gia"><h2>Đánh giá từ người đã mua</h2>
<p><strong><?= $stats['diem']!==null?e($stats['diem']).'/5':'Chưa có đánh giá' ?></strong> · <?= $total ?> nhận xét</p>
<p>Chỉ người mua sản phẩm trong đơn đã hoàn thành mới được đánh giá. <a href="don-hang.php">Vào đơn hàng để viết đánh giá</a>.</p>
<?php foreach($reviews as $review)require __DIR__.'/the-danh-gia.php';?>
<?php if($page>1): ?><a href="chi-tiet.php?id=<?= $sp->id ?>&amp;trang_dg=<?= $page-1 ?>#danh-gia">← Trước</a><?php endif;?>
<?php if($page*10<$total): ?><a href="chi-tiet.php?id=<?= $sp->id ?>&amp;trang_dg=<?= $page+1 ?>#danh-gia">Tiếp →</a><?php endif;?></section>

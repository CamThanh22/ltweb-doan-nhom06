<?php
require dirname(__DIR__,2).'/inc/config.php';
require dirname(__DIR__,2).'/inc/nen-tang.php';
$nd=yeuCauDangNhap();$uid=(int)$nd['id'];$order=idDuong($_GET['don']??null);$product=idDuong($_GET['sp']??null);
$s=db()->prepare('SELECT dh.nguoi_dung_id,dh.trang_thai,ct.nguoi_ban_id,sp.ten FROM chi_tiet_don ct JOIN don_hang dh ON dh.id=ct.don_hang_id JOIN san_pham sp ON sp.id=ct.san_pham_id WHERE dh.id=? AND sp.id=? AND (dh.nguoi_dung_id=? OR ct.nguoi_ban_id=?)');
$s->execute([$order,$product,$uid,$uid]);$item=$s->fetch();
if(!$item){http_response_code(404);require dirname(__DIR__,2).'/pages/loi/404.php';exit;}
$buyer=(int)$item['nguoi_dung_id']===$uid;
$s=db()->prepare('SELECT * FROM danh_gia_giao_dich WHERE don_hang_id=? AND san_pham_id=? AND nguoi_viet_id=?');$s->execute([$order,$product,$uid]);$existing=$s->fetch();
$tieuDe='Đánh giá giao dịch';require dirname(__DIR__,2).'/inc/header.php';?>
<main id="noi-dung" class="jh-page">
<?php require dirname(__DIR__,2).'/inc/thong-bao.php';?>
<p><a href="<?= $buyer?'don-hang.php?id='.$order:'don-ban.php' ?>">← Quay lại đơn hàng</a></p>
<h1><?= $buyer?'Đánh giá sản phẩm và shop':'Đánh giá người mua' ?></h1>
<p>Đơn #<?= $order ?> · <a href="chi-tiet.php?id=<?= $product ?>"><?= e($item['ten']) ?></a></p>
<?php if($existing): ?>
<section class="jh-card"><h2>Đánh giá đã đăng · <?= (int)$existing['sao'] ?>/5 sao</h2><p><?= nl2br(e($existing['noi_dung'])) ?></p><p>Điểm đối tác: <?= (int)$existing['sao_doi_tac'] ?>/5</p><p>Mỗi sản phẩm trong một đơn chỉ được đánh giá một lần.</p></section>
<?php elseif($item['trang_thai']!=='hoan_thanh'): ?><p class="thong-bao">Bạn có thể đánh giá sau khi đơn hoàn thành.</p>
<?php elseif((int)$item['nguoi_ban_id']===(int)$item['nguoi_dung_id'] || !$item['nguoi_ban_id']): ?><p>Giao dịch này không đủ điều kiện đánh giá đối tác.</p>
<?php else: ?>
<form class="jh-card jh-review-form" method="post" action="hau-mai.php"><?= csrfField() ?>
<input type="hidden" name="hanh_dong" value="danh_gia"><input type="hidden" name="don_hang_id" value="<?= $order ?>"><input type="hidden" name="san_pham_id" value="<?= $product ?>">
<label><?= $buyer?'Chất lượng sản phẩm':'Trải nghiệm giao dịch với người mua' ?>
<select name="sao" required><option value="">Chọn số sao</option><?php for($i=5;$i>=1;$i--): ?><option value="<?= $i ?>"><?= $i ?> sao</option><?php endfor;?></select></label>
<?php if($buyer): ?><label>Dịch vụ của shop<select name="sao_doi_tac" required><option value="">Chọn số sao</option><?php for($i=5;$i>=1;$i--): ?><option value="<?= $i ?>"><?= $i ?> sao</option><?php endfor;?></select></label><?php endif;?>
<label>Nhận xét từ trải nghiệm thực tế<textarea name="noi_dung" rows="5" minlength="10" maxlength="2000" required placeholder="Chất lượng, giao tiếp và quá trình giao nhận…"></textarea></label>
<p>Đánh giá sẽ công khai trên sản phẩm/hồ sơ. Không đưa số tài khoản, số điện thoại hoặc thông tin riêng tư của đối tác vào nhận xét.</p>
<button type="submit">Đăng đánh giá</button></form>
<?php endif;?></main><?php require dirname(__DIR__,2).'/inc/footer.php';?>

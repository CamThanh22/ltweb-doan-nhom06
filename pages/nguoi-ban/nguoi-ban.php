<?php
require dirname(__DIR__, 2).'/inc/config.php';require dirname(__DIR__, 2).'/inc/nen-tang.php';
$nd=yeuCauDangNhap();$uid=(int)$nd['id'];
if($_SERVER['REQUEST_METHOD']==='POST'){
 $ten=layChuoi($_POST,'ten_gian_hang');$gioiThieu=layChuoi($_POST,'gioi_thieu');
 if(mb_strlen($ten)<3 || mb_strlen($ten)>120 || mb_strlen($gioiThieu)>1000) saiDuLieu('Tên gian hàng 3–120 ký tự, giới thiệu tối đa 1000.');
 $st=db()->prepare("INSERT INTO ho_so_nguoi_ban(nguoi_dung_id,ten_gian_hang,gioi_thieu,trang_thai) VALUES(?, ?, ?, 'cho_duyet') ON DUPLICATE KEY UPDATE ten_gian_hang=IF(trang_thai='da_duyet',ten_gian_hang,VALUES(ten_gian_hang)),gioi_thieu=IF(trang_thai='da_duyet',gioi_thieu,VALUES(gioi_thieu)),trang_thai=IF(trang_thai='da_duyet',trang_thai,'cho_duyet')");$st->execute([$uid,$ten,$gioiThieu]);chuyenTrang('nguoi-ban.php','Đã gửi hồ sơ để quản trị viên xem xét.');
}
$s=db()->prepare('SELECT * FROM ho_so_nguoi_ban WHERE nguoi_dung_id=?');$s->execute([$uid]);$hoso=$s->fetch();
$tin=[];if($hoso && $hoso['trang_thai']==='da_duyet'){$s=db()->prepare('SELECT * FROM tin_dang_ngoc WHERE nguoi_ban_id=? ORDER BY id DESC');$s->execute([$uid]);$tin=$s->fetchAll();}
$tieuDe='Gian hàng người bán';require dirname(__DIR__, 2).'/inc/header.php';?>
<main id="noi-dung" class="jh-page"><h1>Trung tâm người bán</h1><?php require dirname(__DIR__, 2).'/inc/thong-bao.php';?>
<?php if(!$hoso || $hoso['trang_thai']!=='da_duyet'): ?>
<p>Trở thành người bán trên JadeHub. Hồ sơ cần được quản trị duyệt trước khi đăng bán.</p>
<?php if($hoso): ?><p>Trạng thái: <strong><?=e($hoso['trang_thai'])?></strong></p><?php endif;?>
<form method="post"><?=csrfField()?><label>Tên gian hàng <input name="ten_gian_hang" required minlength="3" maxlength="120" value="<?=e($hoso['ten_gian_hang']??'')?>"></label><label>Giới thiệu <textarea name="gioi_thieu" maxlength="1000"><?=e($hoso['gioi_thieu']??'')?></textarea></label><button type="submit">Gửi hồ sơ xét duyệt</button></form>
<?php else: ?><p>Gian hàng: <strong><?=e($hoso['ten_gian_hang'])?></strong> · Đã duyệt.</p><p><a class="nut-chinh" href="dang-ban.php">Đăng sản phẩm và chứng thư</a></p><h2>Sản phẩm đã gửi</h2><div class="jh-grid"><?php foreach($tin as $t):?><article class="jh-card"><h3><?=e($t['ten'])?></h3><p><?=e(vnd((int)$t['gia']))?> · <?=e($t['trang_thai'])?></p><p><a href="chung-thu.php?id=<?=(int)$t['id']?>">Xem hồ sơ chứng thư</a></p><?php if($t['san_pham_id']):?><a href="chi-tiet.php?id=<?=(int)$t['san_pham_id']?>">Xem trên sàn</a><?php endif;?></article><?php endforeach;?></div><?php endif;?></main><?php require dirname(__DIR__, 2).'/inc/footer.php';?>

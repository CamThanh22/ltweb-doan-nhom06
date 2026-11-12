<?php
require dirname(__DIR__,2).'/inc/config.php';
$q=mb_substr(layChuoi($_GET,'q'),0,100);$shops=layChuoi($_GET,'loai')==='shop';
$page=max(1,min(10000,(int)(filter_var($_GET['trang']??1,FILTER_VALIDATE_INT)?:1)));
$where=" WHERE (nd.ho_ten LIKE ? OR h.ten_gian_hang LIKE ?)".($shops?" AND h.trang_thai='da_duyet'":'');
$pattern='%'.addcslashes($q,'%_\\').'%';
$s=db()->prepare('SELECT COUNT(*) FROM nguoi_dung nd LEFT JOIN ho_so_nguoi_ban h ON h.nguoi_dung_id=nd.id'.$where);$s->execute([$pattern,$pattern]);$total=(int)$s->fetchColumn();
$page=min($page,max(1,(int)ceil($total/12)));
$s=db()->prepare("SELECT nd.id,nd.ho_ten,nd.ngay_tao,h.ten_gian_hang,h.trang_thai,
(SELECT ROUND(AVG(sao_doi_tac),1) FROM danh_gia_giao_dich WHERE nguoi_nhan_id=nd.id AND loai='san_pham') AS diem_shop,
(SELECT COUNT(*) FROM danh_gia_giao_dich WHERE nguoi_nhan_id=nd.id AND loai='san_pham') AS so_dg,
(SELECT ROUND(AVG(sao_doi_tac),1) FROM danh_gia_giao_dich WHERE nguoi_nhan_id=nd.id AND loai='nguoi_mua') AS diem_mua
FROM nguoi_dung nd LEFT JOIN ho_so_nguoi_ban h ON h.nguoi_dung_id=nd.id".$where.' ORDER BY nd.id DESC LIMIT 12 OFFSET ?');
$s->execute([$pattern,$pattern,($page-1)*12]);$members=$s->fetchAll();$tieuDe='Thành viên & gian hàng';require dirname(__DIR__,2).'/inc/header.php';?>
<main id="noi-dung" class="jh-page"><h1>Thành viên & gian hàng</h1><p>Tìm người bạn muốn trao đổi, đọc đánh giá giao dịch trước khi mua bán.</p>
<form class="jh-members-search" method="get"><input name="q" maxlength="100" value="<?= e($q) ?>" placeholder="Tên thành viên hoặc shop" aria-label="Tên thành viên hoặc shop"><select name="loai" aria-label="Loại tài khoản"><option value="">Tất cả thành viên</option><option value="shop"<?= $shops?' selected':'' ?>>Shop đã duyệt</option></select><button type="submit">Tìm kiếm</button></form>
<p><?= $total ?> kết quả</p><div class="jh-grid">
<?php foreach($members as $m): ?><article class="jh-card"><h2><a href="ho-so.php?id=<?= (int)$m['id'] ?>"><?= e($m['ho_ten']) ?></a></h2>
<?php if($m['trang_thai']==='da_duyet'): ?><p><?= e($m['ten_gian_hang']) ?> <span class="jh-verified">Hồ sơ shop đã duyệt</span></p><?php endif;?>
<p>Điểm người bán: <?= $m['diem_shop']!==null?e($m['diem_shop']).'/5 · '.(int)$m['so_dg'].' đánh giá':'Chưa có đánh giá' ?></p>
<p>Điểm người mua: <?= $m['diem_mua']!==null?e($m['diem_mua']).'/5':'Chưa có đánh giá' ?></p>
<div class="social-actions"><a href="ho-so.php?id=<?= (int)$m['id'] ?>#danh-gia">Xem thông tin & nhận xét</a>
<?php if((int)(nguoiDung()['id']??0)!==(int)$m['id']): ?><a href="tin-nhan.php?id=<?= (int)$m['id'] ?>">Nhắn tin</a><?php endif;?></div></article><?php endforeach;?></div>
<nav class="jh-pagination" aria-label="Phân trang"><?php if($page>1): ?><a href="?<?= e(http_build_query(['q'=>$q,'loai'=>$shops?'shop':'','trang'=>$page-1])) ?>">← Trước</a><?php endif;?> <span>Trang <?= $page ?></span> <?php if($page*12<$total): ?><a href="?<?= e(http_build_query(['q'=>$q,'loai'=>$shops?'shop':'','trang'=>$page+1])) ?>">Tiếp →</a><?php endif;?></nav></main><?php require dirname(__DIR__,2).'/inc/footer.php';?>

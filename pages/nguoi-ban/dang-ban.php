<?php
require dirname(__DIR__, 2).'/inc/config.php';require dirname(__DIR__, 2).'/inc/nen-tang.php';$nd=yeuCauDangNhap();$uid=(int)$nd['id'];
if(!coGianHang($uid)){http_response_code(403);require dirname(__DIR__, 2).'/pages/loi/403.php';exit;}
if($_SERVER['REQUEST_METHOD']==='POST'){
 $ten=layChuoi($_POST,'ten');$mota=layChuoi($_POST,'mo_ta');$xuatxu=layChuoi($_POST,'xuat_xu');$donvi=layChuoi($_POST,'don_vi_kiem_dinh');$so=layChuoi($_POST,'so_chung_thu');$danhMuc=idDuong($_POST['danh_muc_id']??null);$gia=idDuong($_POST['gia']??null);$ton=idDuong($_POST['ton_kho']??null);
 if(mb_strlen($ten)<5 || mb_strlen($ten)>200 || mb_strlen($mota)<20 || mb_strlen($mota)>5000 || mb_strlen($xuatxu)>100 || mb_strlen($donvi)<2 || mb_strlen($donvi)>150 || mb_strlen($so)<2 || mb_strlen($so)>100 || $gia<1000 || $gia>2000000000 || $ton<1 || $ton>999) saiDuLieu('Thông tin sản phẩm/chứng thư chưa hợp lệ.');
 $st=db()->prepare('SELECT id FROM danh_muc WHERE id=?');$st->execute([$danhMuc]);if(!$st->fetchColumn()) saiDuLieu('Danh mục không hợp lệ.');
 // Nếu ảnh chứng thư lỗi, không giữ lại ảnh sản phẩm mồ côi.
 $anh=xuLyAnh('anh');
 try { $chung=xuLyAnh('anh_chung_thu'); }
 catch (Throwable $e) { @unlink(dirname(__DIR__, 2).'/uploads/'.$anh); throw $e; }
 $db=db();$db->beginTransaction();try{$s=$db->prepare('INSERT INTO tin_dang_ngoc (nguoi_ban_id,danh_muc_id,ten,mo_ta,gia,ton_kho,xuat_xu,anh,anh_chung_thu,don_vi_kiem_dinh,so_chung_thu) VALUES (?,?,?,?,?,?,?,?,?,?,?)');$s->execute([$uid,$danhMuc,$ten,$mota,$gia,$ton,$xuatxu,$anh,$chung,$donvi,$so]);$id=(int)$db->lastInsertId();$db->prepare('INSERT INTO kiem_dinh_ngoc(tin_dang_id) VALUES(?)')->execute([$id]);$db->commit();}catch(Throwable $e){if($db->inTransaction())$db->rollBack();@unlink(dirname(__DIR__, 2).'/uploads/'.$anh);@unlink(dirname(__DIR__, 2).'/uploads/'.$chung);throw $e;}
 chuyenTrang('nguoi-ban.php','Đã gửi bài đăng và chứng thư chờ kiểm duyệt.');
}
$dm=db()->query('SELECT id,ten FROM danh_muc ORDER BY id')->fetchAll();$tieuDe='Đăng bán ngọc';require dirname(__DIR__, 2).'/inc/header.php';?>
<main id="noi-dung" class="jh-page"><h1>Đăng bán ngọc có chứng thư</h1><p>Ảnh giấy chứng thư là tài liệu người bán cung cấp, không đồng nghĩa xác nhận của phòng giám định. Quản trị chỉ xác thực hồ sơ tài liệu.</p><form method="post" enctype="multipart/form-data"><?=csrfField()?>
<label>Tên sản phẩm <input name="ten" required minlength="5" maxlength="200"></label><label>Danh mục <select name="danh_muc_id" required><?php foreach($dm as $d):?><option value="<?=(int)$d['id']?>"><?=e($d['ten'])?></option><?php endforeach;?></select></label>
<label>Giá (VNĐ) <input name="gia" type="number" min="1000" max="2000000000" required></label><label>Tồn kho <input name="ton_kho" type="number" min="1" max="999" required></label><label>Xuất xứ <input name="xuat_xu" maxlength="100" required></label><label>Đơn vị ghi trên chứng thư <input name="don_vi_kiem_dinh" maxlength="150" required></label><label>Số chứng thư <input name="so_chung_thu" maxlength="100" required></label><label>Mô tả chi tiết <textarea name="mo_ta" minlength="20" maxlength="5000" required></textarea></label>
<label>Ảnh sản phẩm (JPG/PNG/WEBP, ≤3 MB) <input type="file" name="anh" accept="image/jpeg,image/png,image/webp" required></label><label>Ảnh chứng thư (JPG/PNG/WEBP, ≤3 MB) <input type="file" name="anh_chung_thu" accept="image/jpeg,image/png,image/webp" required></label><button type="submit">Gửi kiểm duyệt</button></form></main><?php require dirname(__DIR__, 2).'/inc/footer.php';?>

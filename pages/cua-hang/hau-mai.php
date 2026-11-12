<?php
require dirname(__DIR__,2).'/inc/config.php';
require dirname(__DIR__,2).'/inc/nen-tang.php';
require dirname(__DIR__,2).'/inc/don-hang.php';
if($_SERVER['REQUEST_METHOD']!=='POST'){header('Allow: POST');http_response_code(405);exit;}
$nd=yeuCauDangNhap();$uid=(int)$nd['id'];$action=layChuoi($_POST,'hanh_dong');$id=idDuong($_POST['don_hang_id']??null);
$back='don-hang.php?id='.$id;
try{
    if($action==='huy_don'){
        khachHuyDon($id,$uid,layChuoi($_POST,'ly_do'));
        $message='Đã hủy đơn và hoàn lại tồn kho. Nếu có tiền đã nhận, hồ sơ hoàn tiền được tạo bên dưới.';
    }elseif($action==='tai_khoan_hoan'){
        $dest=layChuoi($_POST,'tai_khoan_nhan');
        if(mb_strlen($dest)<10||mb_strlen($dest)>500)throw new InvalidArgumentException('Nhập ngân hàng, số tài khoản và tên người nhận (10–500 ký tự).');
        $db=db();$db->beginTransaction();
        try{
            $s=$db->prepare('SELECT nguoi_dung_id FROM don_hang WHERE id=? FOR UPDATE');$s->execute([$id]);
            if((int)$s->fetchColumn()!==$uid)throw new DomainException('Không có quyền cập nhật hồ sơ này.');
            $s=$db->prepare('SELECT trang_thai FROM hoan_tien WHERE don_hang_id=? FOR UPDATE');$s->execute([$id]);
            if(!in_array($s->fetchColumn(),['can_doi_soat','cho_hoan'],true))throw new InvalidArgumentException('Hồ sơ không còn nhận thay đổi tài khoản hoàn tiền.');
            $db->prepare('UPDATE hoan_tien SET tai_khoan_nhan=? WHERE don_hang_id=?')->execute([$dest,$id]);
            $db->commit();
        }catch(Throwable $e){if($db->inTransaction())$db->rollBack();throw $e;}
        $message='Đã lưu thông tin nhận hoàn tiền.';
    }elseif($action==='bao_da_chuyen'){
        $ref=layChuoi($_POST,'ma_tham_chieu');
        if(!preg_match('/^[\p{L}\p{N} ._\/-]{6,100}$/u',$ref))throw new InvalidArgumentException('Mã giao dịch cần 6–100 ký tự hợp lệ.');
        $db=db();$db->beginTransaction();
        try{
            $s=$db->prepare('SELECT nguoi_dung_id,trang_thai FROM don_hang WHERE id=? FOR UPDATE');$s->execute([$id]);$o=$s->fetch();
            if(!$o || (int)$o['nguoi_dung_id']!==$uid)throw new DomainException('Không có quyền xem đơn này.');
            if($o['trang_thai']!=='da_huy')throw new InvalidArgumentException('Đơn chưa hủy; hãy gửi mã ở phần thanh toán.');
            $s=$db->prepare('SELECT phuong_thuc FROM thanh_toan WHERE don_hang_id=?');$s->execute([$id]);
            if($s->fetchColumn()!=='chuyen_khoan')throw new InvalidArgumentException('Đơn không dùng chuyển khoản.');
            $s=$db->prepare('SELECT trang_thai FROM hoan_tien WHERE don_hang_id=?');$s->execute([$id]);$state=$s->fetchColumn();
            if(in_array($state,['cho_hoan','da_hoan'],true))throw new InvalidArgumentException('Hồ sơ đã được xác nhận, không thể gửi lại yêu cầu.');
            $db->prepare('UPDATE thanh_toan SET ma_tham_chieu=? WHERE don_hang_id=?')->execute([$ref,$id]);
            $s=$db->prepare('SELECT SUM(so_luong*don_gia) FROM chi_tiet_don WHERE don_hang_id=?');$s->execute([$id]);$amount=(int)$s->fetchColumn();
            $db->prepare("INSERT INTO hoan_tien(don_hang_id,so_tien,trang_thai) VALUES(?,?,'can_doi_soat') ON DUPLICATE KEY UPDATE trang_thai='can_doi_soat'")->execute([$id,$amount]);
            $db->commit();
        }catch(Throwable $e){if($db->inTransaction())$db->rollBack();throw $e;}
        $message='Đã gửi yêu cầu đối soát. Chưa xác nhận nhận tiền hoặc hoàn tiền.';
    }elseif($action==='xu_ly_hoan'){
        if($nd['vai_tro']!=='quan_tri')throw new DomainException('Chỉ quản trị được xử lý hoàn tiền.');
        xuLyHoanTien($id,$uid,layChuoi($_POST,'xu_ly'),layChuoi($_POST,'ma_giao_dich'),layChuoi($_POST,'ghi_chu'));
        $message='Đã cập nhật hồ sơ hoàn tiền.';$back='hoan-tien.php';
    }elseif($action==='danh_gia'){
        $product=idDuong($_POST['san_pham_id']??null);
        guiDanhGia($id,$product,$uid,idDuong($_POST['sao']??null),idDuong($_POST['sao_doi_tac']??$_POST['sao']??null),layChuoi($_POST,'noi_dung'));
        $message='Đã đăng đánh giá từ giao dịch đã hoàn thành.';$back='danh-gia.php?don='.$id.'&sp='.$product;
    }elseif($action==='phan_hoi'){
        $review=idDuong($_POST['danh_gia_id']??null);$content=layChuoi($_POST,'phan_hoi');
        if(mb_strlen($content)<5||mb_strlen($content)>1000)throw new InvalidArgumentException('Phản hồi cần 5–1000 ký tự.');
        $s=db()->prepare('SELECT nguoi_nhan_id FROM danh_gia_giao_dich WHERE id=?');$s->execute([$review]);
        if((int)$s->fetchColumn()!==$uid)throw new DomainException('Chỉ người được đánh giá mới được phản hồi.');
        db()->prepare('UPDATE danh_gia_giao_dich SET phan_hoi=?,ngay_phan_hoi=NOW() WHERE id=? AND nguoi_nhan_id=?')->execute([$content,$review,$uid]);
        $message='Đã lưu phản hồi.';$back='ho-so.php?id='.$uid.'#danh-gia';
    }else throw new InvalidArgumentException('Thao tác không hợp lệ.');
    datFlash('ok',$message);header('Location: '.$back,true,303);exit;
}catch(DomainException $e){
    ghiLogBaoMat('cam_truy_cap');http_response_code(403);exit(e($e->getMessage()));
}catch(InvalidArgumentException $e){http_response_code(422);exit(e($e->getMessage()));
}catch(PDOException $e){
    if(($e->errorInfo[1]??0)===1062){http_response_code(409);exit('Đánh giá hoặc mã giao dịch này đã được ghi nhận. Không tạo thêm bản trùng.');}
    throw $e;
}

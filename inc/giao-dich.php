<?php
declare(strict_types=1);
const NHAN_HOAN_TIEN = ['can_doi_soat'=>'Cần đối soát tiền đã chuyển','cho_hoan'=>'Chờ hoàn tiền','da_hoan'=>'Đã hoàn tiền','khong_phat_sinh'=>'Chưa ghi nhận tiền cần hoàn'];

/** Called with the order row locked inside the cancellation transaction. */
function taoHoanTienKhiHuy(PDO $db, int $id): void {
    $s=$db->prepare('SELECT * FROM thanh_toan WHERE don_hang_id=? FOR UPDATE');$s->execute([$id]);$tt=$s->fetch();
    if(!$tt)return;
    $state=$tt['trang_thai']==='da_xac_nhan'?'cho_hoan':
        (($tt['phuong_thuc']==='chuyen_khoan' && !empty($tt['ma_tham_chieu']))?'can_doi_soat':null);
    if($state===null)return;
    $s=$db->prepare('SELECT COALESCE(SUM(so_luong*don_gia),0) FROM chi_tiet_don WHERE don_hang_id=?');$s->execute([$id]);
    $db->prepare('INSERT IGNORE INTO hoan_tien(don_hang_id,so_tien,trang_thai) VALUES(?,?,?)')->execute([$id,(int)$s->fetchColumn(),$state]);
}

function khachHuyDon(int $id,int $uid,string $reason): void {
    if(mb_strlen($reason)<5 || mb_strlen($reason)>500)throw new InvalidArgumentException('Lý do hủy cần từ 5 đến 500 ký tự.');
    // Ownership and state are checked under the same lock as the stock transition.
    doiTrangThaiDon($id,'da_huy',$uid,$reason);
}
function xuLyHoanTien(int $id,int $admin,string $action,string $reference,string $note): void {
    $db=db();$db->beginTransaction();
    try{
        $s=$db->prepare('SELECT vai_tro FROM nguoi_dung WHERE id=?');$s->execute([$admin]);
        if($s->fetchColumn()!=='quan_tri')throw new DomainException('Chỉ quản trị được xử lý hoàn tiền.');
        $s=$db->prepare('SELECT trang_thai FROM don_hang WHERE id=? FOR UPDATE');$s->execute([$id]);
        if($s->fetchColumn()!=='da_huy')throw new InvalidArgumentException('Chỉ xử lý hoàn tiền cho đơn đã hủy.');
        $s=$db->prepare('SELECT * FROM hoan_tien WHERE don_hang_id=? FOR UPDATE');$s->execute([$id]);$refund=$s->fetch();
        if(!$refund)throw new InvalidArgumentException('Không có hồ sơ hoàn tiền.');
        if(mb_strlen($note)>1000)throw new InvalidArgumentException('Ghi chú tối đa 1000 ký tự.');
        if($action==='da_nhan_tien' && $refund['trang_thai']==='can_doi_soat'){
            $db->prepare("UPDATE thanh_toan SET trang_thai='da_xac_nhan' WHERE don_hang_id=?")->execute([$id]);
            $db->prepare("UPDATE hoan_tien SET trang_thai='cho_hoan',ghi_chu=?,xu_ly_boi=? WHERE don_hang_id=?")->execute([$note,$admin,$id]);
        }elseif($action==='chua_nhan_tien' && $refund['trang_thai']==='can_doi_soat'){
            if(mb_strlen($note)<10)throw new InvalidArgumentException('Ghi rõ kết quả đối soát, tối thiểu 10 ký tự.');
            $db->prepare("UPDATE hoan_tien SET trang_thai='khong_phat_sinh',ghi_chu=?,xu_ly_boi=? WHERE don_hang_id=?")->execute([$note,$admin,$id]);
        }elseif($action==='da_hoan'){
            if($refund['trang_thai']==='da_hoan' && hash_equals((string)$refund['ma_giao_dich'],$reference)){$db->commit();return;}
            if($refund['trang_thai']!=='cho_hoan')throw new InvalidArgumentException('Hồ sơ chưa đủ điều kiện xác nhận hoàn tiền.');
            if(!preg_match('/^[A-Za-z0-9._\/-]{6,100}$/D',$reference))throw new InvalidArgumentException('Mã giao dịch hoàn tiền cần 6–100 ký tự chữ/số.');
            if($refund['tai_khoan_nhan']==='')throw new InvalidArgumentException('Người mua cần cung cấp thông tin nhận tiền trước.');
            $db->prepare("UPDATE hoan_tien SET trang_thai='da_hoan',ma_giao_dich=?,ghi_chu=?,xu_ly_boi=?,ngay_hoan=NOW() WHERE don_hang_id=?")->execute([$reference,$note,$admin,$id]);
        }else throw new InvalidArgumentException('Thao tác không phù hợp trạng thái hoàn tiền.');
        $db->commit();
    }catch(Throwable $e){if($db->inTransaction())$db->rollBack();throw $e;}
}
function guiDanhGia(int $order,int $product,int $writer,int $stars,int $partnerStars,string $content): void {
    if($stars<1||$stars>5||$partnerStars<1||$partnerStars>5||mb_strlen($content)<10||mb_strlen($content)>2000)throw new InvalidArgumentException('Chọn điểm 1–5 sao, nhận xét từ 10 đến 2000 ký tự.');
    $db=db();$db->beginTransaction();
    try{
        $s=$db->prepare('SELECT nguoi_dung_id,trang_thai FROM don_hang WHERE id=? FOR UPDATE');$s->execute([$order]);$o=$s->fetch();
        $s=$db->prepare('SELECT nguoi_ban_id FROM chi_tiet_don WHERE don_hang_id=? AND san_pham_id=?');$s->execute([$order,$product]);$item=$s->fetch();
        if(!$o||!$item||$o['trang_thai']!=='hoan_thanh')throw new InvalidArgumentException('Chỉ đánh giá sản phẩm thuộc đơn đã hoàn thành.');
        $buyer=(int)$o['nguoi_dung_id'];$seller=(int)$item['nguoi_ban_id'];
        if(!$seller||$buyer===$seller)throw new InvalidArgumentException('Không thể tự đánh giá giao dịch của mình.');
        if($writer===$buyer){$type='san_pham';$target=$seller;}
        elseif($writer===$seller){$type='nguoi_mua';$target=$buyer;$partnerStars=$stars;}
        else throw new DomainException('Bạn không tham gia giao dịch này.');
        $db->prepare('INSERT INTO danh_gia_giao_dich(don_hang_id,san_pham_id,nguoi_viet_id,nguoi_nhan_id,loai,sao,sao_doi_tac,noi_dung) VALUES(?,?,?,?,?,?,?,?)')->execute([$order,$product,$writer,$target,$type,$stars,$partnerStars,$content]);
        $db->commit();
    }catch(Throwable $e){if($db->inTransaction())$db->rollBack();throw $e;}
}
function thongKeDanhGia(int $uid,string $type): array {
    $s=db()->prepare('SELECT COUNT(*) AS so_danh_gia,ROUND(AVG(sao_doi_tac),1) AS diem,COUNT(DISTINCT nguoi_viet_id) AS so_nguoi FROM danh_gia_giao_dich WHERE nguoi_nhan_id=? AND loai=?');
    $s->execute([$uid,$type]);return $s->fetch();
}

<?php
/**
 * inc/don-hang.php - Xử lý đặt hàng trong một giao dịch (Transaction) với PDO (slide 34-35).
 * Nếu bất kỳ mặt hàng nào không đủ tồn kho (ton_kho < số lượng đặt), ném HetHangException
 * và rollBack() toàn bộ giao dịch để không để lại đơn hàng dở dang hay trừ tồn kho sai.
 * Cách thử: cho 2 món vào giỏ, hạ tồn kho món 2 trong CSDL rồi đặt hàng -> cả 2 món giữ nguyên tồn kho.
 */
declare(strict_types=1);
require_once __DIR__.'/giao-dich.php';

class HetHangException extends RuntimeException
{
}

const TRANG_THAI_DON = [
    'cho_xac_nhan' => 'Chờ xác nhận',
    'dang_giao'    => 'Đang giao',
    'hoan_thanh'   => 'Hoàn thành',
    'da_huy'       => 'Đã hủy',
];

/**
 * Lưu đơn hàng từ mảng giỏ [$idSanPham => $soLuong] trong một giao dịch.
 * Trả về mã đơn hàng mới tạo (lastInsertId).
 *
 * @param array<int, int> $gio
 */
function luuDonHang(array $gio, int $uid, string $ghiChu = '', string $phuongThuc = 'cod'): int
{
    if (!in_array($phuongThuc, ['cod','chuyen_khoan'], true)) throw new InvalidArgumentException('Phương thức thanh toán không hợp lệ.');
    if (!$gio) {
        throw new InvalidArgumentException('Giỏ hàng đang trống.');
    }

    foreach ($gio as $id => $sl) {
        if (filter_var($id, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]) === false
            || filter_var($sl, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1,'max_range'=>99]]) === false) {
            throw new InvalidArgumentException('Giỏ hàng có mã hoặc số lượng không hợp lệ.');
        }
    }
    ksort($gio);
    $db = db();
    $db->beginTransaction();
    try {
        $db->prepare('INSERT INTO don_hang (nguoi_dung_id, ghi_chu) VALUES (?, ?)')
           ->execute([$uid, $ghiChu !== '' ? $ghiChu : null]);
        $maDon = (int) $db->lastInsertId();

        $tru = $db->prepare('UPDATE san_pham SET ton_kho = ton_kho - ?
                             WHERE id = ? AND ton_kho >= ? AND (nguoi_ban_id IS NULL OR nguoi_ban_id <> ?)');
        $ct  = $db->prepare('INSERT INTO chi_tiet_don (don_hang_id, san_pham_id, so_luong, don_gia, nguoi_ban_id)
                             SELECT ?, id, ?, gia, nguoi_ban_id FROM san_pham WHERE id = ?');

        foreach ($gio as $id => $sl) {
            $id = (int) $id;
            $sl = (int) $sl;
            $tru->execute([$sl, $id, $sl, $uid]);
            if ($tru->rowCount() === 0) {
                throw new HetHangException("Sản phẩm #$id không đủ số lượng tồn kho để đặt hàng.");
            }
            $ct->execute([$maDon, $sl, $id]);
        }

        $db->prepare('INSERT INTO thanh_toan (don_hang_id, phuong_thuc, trang_thai) VALUES (?, ?, ?)')
           ->execute([$maDon, $phuongThuc, $phuongThuc === 'cod' ? 'chua_thanh_toan' : 'cho_doi_soat']);
        $db->commit();
        return $maDon;
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $e;
    }
}

/** State transition and stock restoration are atomic and cannot be replayed. */
function doiTrangThaiDon(int $id, string $moi, ?int $khach = null, string $lyDo = ''): void
{
    $db=db();$db->beginTransaction();
    try {
        $s=$db->prepare('SELECT trang_thai,nguoi_dung_id FROM don_hang WHERE id=? FOR UPDATE');
        $s->execute([$id]);$don=$s->fetch();$cu=$don['trang_thai']??false;
        if($cu===false)throw new InvalidArgumentException('Đơn hàng không tồn tại.');
        if($khach!==null){
            if(!$don || (int)$don['nguoi_dung_id']!==$khach)throw new DomainException('Không có quyền hủy đơn này.');
            if($moi!=='da_huy' || !in_array($cu,['cho_xac_nhan','da_huy'],true))throw new InvalidArgumentException('Đơn đã giao cho vận chuyển hoặc đã hoàn thành. Vui lòng nhắn shop để xử lý.');
        }
        if($cu===$moi){$db->commit();return;}
        $choPhep=['cho_xac_nhan'=>['dang_giao','da_huy'],'dang_giao'=>['hoan_thanh','da_huy'],'hoan_thanh'=>[],'da_huy'=>[]];
        if(!in_array($moi,$choPhep[$cu]??[],true))throw new InvalidArgumentException('Không thể chuyển trạng thái đơn hàng theo thứ tự này.');
        if(in_array($moi,['dang_giao','hoan_thanh'],true)){
            $s=$db->prepare('SELECT phuong_thuc,trang_thai FROM thanh_toan WHERE don_hang_id=? FOR UPDATE');$s->execute([$id]);$pay=$s->fetch();
            if(!$pay || ($pay['phuong_thuc']==='chuyen_khoan' && $pay['trang_thai']!=='da_xac_nhan'))throw new InvalidArgumentException('Cần xác nhận tiền chuyển khoản trước khi giao/hoàn thành đơn.');
        }
        if($moi==='da_huy'){
            taoHoanTienKhiHuy($db,$id);
            $db->prepare('UPDATE don_hang SET ly_do_huy=?,huy_boi=?,huy_luc=NOW() WHERE id=?')->execute([$lyDo?:'Quản trị hủy đơn',$khach??($_SESSION['uid']??null),$id]);
            $s=$db->prepare('SELECT san_pham_id,so_luong FROM chi_tiet_don WHERE don_hang_id=? ORDER BY san_pham_id');
            $s->execute([$id]);
            foreach($s->fetchAll() as $dong)$db->prepare('UPDATE san_pham SET ton_kho=ton_kho+? WHERE id=?')->execute([$dong['so_luong'],$dong['san_pham_id']]);
        }
        $db->prepare('UPDATE don_hang SET trang_thai=? WHERE id=?')->execute([$moi,$id]);
        if($moi==='hoan_thanh')$db->prepare("UPDATE thanh_toan SET trang_thai='da_xac_nhan' WHERE don_hang_id=? AND phuong_thuc='cod'")->execute([$id]);
        $db->commit();
    } catch(Throwable $e){if($db->inTransaction())$db->rollBack();throw $e;}
}

<?php
/**
 * inc/don-hang.php - Xử lý đặt hàng trong một giao dịch (Transaction) với PDO (slide 34-35).
 * Nếu bất kỳ mặt hàng nào không đủ tồn kho (ton_kho < số lượng đặt), ném HetHangException
 * và rollBack() toàn bộ giao dịch để không để lại đơn hàng dở dang hay trừ tồn kho sai.
 * Cách thử: cho 2 món vào giỏ, hạ tồn kho món 2 trong CSDL rồi đặt hàng -> cả 2 món giữ nguyên tồn kho.
 */
declare(strict_types=1);

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
function luuDonHang(array $gio, int $uid, string $ghiChu = ''): int
{
    if (!$gio) {
        throw new InvalidArgumentException('Giỏ hàng đang trống.');
    }

    $db = db();
    $db->beginTransaction();
    try {
        $db->prepare('INSERT INTO don_hang (nguoi_dung_id, ghi_chu) VALUES (?, ?)')
           ->execute([$uid, $ghiChu !== '' ? $ghiChu : null]);
        $maDon = (int) $db->lastInsertId();

        $tru = $db->prepare('UPDATE san_pham SET ton_kho = ton_kho - ?
                             WHERE id = ? AND ton_kho >= ?');
        $ct  = $db->prepare('INSERT INTO chi_tiet_don (don_hang_id, san_pham_id, so_luong, don_gia)
                             SELECT ?, id, ?, gia FROM san_pham WHERE id = ?');

        foreach ($gio as $id => $sl) {
            $id = (int) $id;
            $sl = (int) $sl;
            $tru->execute([$sl, $id, $sl]);
            if ($tru->rowCount() === 0) {
                throw new HetHangException("Sản phẩm #$id không đủ số lượng tồn kho để đặt hàng.");
            }
            $ct->execute([$maDon, $sl, $id]);
        }

        $db->commit();
        return $maDon;
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $e;
    }
}

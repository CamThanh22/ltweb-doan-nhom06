<?php
/**
 * gio-hang.php - Giỏ hàng lưu trong session (lớp GioHang bọc $_SESSION['gio'] = [id => số lượng]).
 * Lấy toàn bộ sản phẩm trong giỏ bằng một câu WHERE id IN (?, ...) qua KhoSanPham(db()).
 * Kiểm tra tồn kho và số lượng 1..99; giá luôn lấy từ CSDL. Có biểu mẫu POST tới dat-hang.php.
 * Cách thử: thêm 2 sản phẩm, đổi số lượng, xóa một mục; bấm Đặt hàng.
 */
require __DIR__ . '/inc/config.php';

use App\Data\KhoSanPham;
use App\Services\GioHang;

$kho = new KhoSanPham(db());
$gio = new GioHang();
$loi = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hanhDong = layChuoi($_POST, 'hanhdong') ?: 'them';
    $id = GioHang::idHopLe($_POST['id'] ?? null);
    $sl = GioHang::soLuongHopLe($_POST['sl'] ?? null);

    switch ($hanhDong) {
        case 'them':
        case 'capnhat':
            $sp = $id !== null ? $kho->timTheoId($id) : null;
            if ($sp === null) {
                $loi = 'Sản phẩm không tồn tại, giỏ hàng được giữ nguyên.';
            } elseif ($sl === null) {
                $loi = 'Số lượng phải là số nguyên từ 1 đến ' . GioHang::SL_TOI_DA . ', giỏ hàng được giữ nguyên.';
            } else {
                $slMoi = $hanhDong === 'them' ? (($gio->tatCa()[$id] ?? 0) + $sl) : $sl;
                if ($slMoi > $sp->tonKho) {
                    $loi = "Sản phẩm “{$sp->ten}” chỉ còn {$sp->tonKho} món trong kho.";
                } elseif ($hanhDong === 'them') {
                    $gio->them($id, $sl);
                    datFlash('ok', 'Đã thêm sản phẩm vào giỏ hàng.');
                } elseif ($gio->capNhat($id, $sl)) {
                    datFlash('ok', 'Đã cập nhật số lượng.');
                } else {
                    $loi = 'Sản phẩm này không có trong giỏ.';
                }
            }
            break;
        case 'xoa':
            if ($id !== null && $gio->xoa($id)) {
                datFlash('ok', 'Đã xóa sản phẩm khỏi giỏ.');
            } else {
                $loi = 'Không có sản phẩm này trong giỏ.';
            }
            break;
        case 'xoahet':
            $gio->xoaHet();
            datFlash('ok', 'Đã xóa toàn bộ giỏ hàng.');
            break;
        default:
            $loi = 'Thao tác không hợp lệ.';
    }

    if ($loi === '') {
        header('Location: gio-hang.php');
        exit;
    }
}

$dong = $gio->chiTiet($kho);
$tong = $gio->tongTien($kho);
$nd   = nguoiDung();

$tieuDe = 'Giỏ hàng';
$trang  = 'gio-hang';
$moTa   = 'Giỏ hàng JadeHub: xem, đổi số lượng, xóa sản phẩm và đặt hàng.';
require __DIR__ . '/inc/header.php';
?>
    <main id="noi-dung">
        <h1>Giỏ hàng của bạn</h1>
<?php require __DIR__ . '/inc/thong-bao.php'; ?>
<?php if ($loi !== ''): ?>
        <p class="thong-bao loi" role="alert"><?= e($loi) ?></p>
<?php endif; ?>

<?php if (!$dong): ?>
        <p>Giỏ hàng đang trống. <a href="danh-sach.php">Xem danh mục ngọc quý →</a></p>
<?php else: ?>
        <ul class="ds-gio">
<?php foreach ($dong as $d): $sp = $d['sp']; ?>
            <li class="dong-gio">
                <img src="<?= e($sp->duongDanAnh()) ?>" alt="" width="80" height="60" loading="lazy">
                <div class="thong-tin-dong">
                    <h2><a href="chi-tiet.php?id=<?= $sp->id ?>"><?= e($sp->ten) ?></a></h2>
                    <p>Đơn giá: <?= e(vnd($sp->gia)) ?> · Tồn kho: <?= $sp->tonKho ?></p>
                    <form class="form-gio" method="post" action="gio-hang.php">
                        <input type="hidden" name="hanhdong" value="capnhat">
                        <input type="hidden" name="id" value="<?= $sp->id ?>">
                        <label for="sl-<?= $sp->id ?>">Số lượng của <?= e($sp->ma) ?></label>
                        <input id="sl-<?= $sp->id ?>" name="sl" type="number" min="1" max="<?= GioHang::SL_TOI_DA ?>" value="<?= $d['sl'] ?>" required>
                        <button type="submit">Cập nhật</button>
                    </form>
                    <p class="gia-san-pham">Thành tiền: <?= e(vnd($d['thanhTien'])) ?></p>
                    <form method="post" action="gio-hang.php">
                        <input type="hidden" name="hanhdong" value="xoa">
                        <input type="hidden" name="id" value="<?= $sp->id ?>">
                        <button type="submit" class="nut-phu">Xóa <?= e($sp->ma) ?> khỏi giỏ</button>
                    </form>
                </div>
            </li>
<?php endforeach; ?>
        </ul>

        <p class="tong-tien">Số món: <strong><?= $gio->soMon() ?></strong> · Tổng tiền: <strong><?= e(vnd($tong)) ?></strong></p>

        <section class="khu-dat-hang">
            <h2>Đặt mua các sản phẩm trong giỏ</h2>
<?php if ($nd === null): ?>
            <p>Bạn cần <a href="dang-nhap.php"><strong>Đăng nhập</strong></a> hoặc <a href="dang-ky.php"><strong>Đăng ký tài khoản</strong></a> để đặt hàng.</p>
<?php else: ?>
            <form class="form-tham-dinh" method="post" action="dat-hang.php">
                <p>
                    <label for="ghi-chu-don">Ghi chú giao hàng / yêu cầu chứng thư (không bắt buộc):</label>
                    <input id="ghi-chu-don" name="ghi_chu" type="text" maxlength="500" placeholder="Ví dụ: Giao giờ hành chính kèm hộp nhung và chứng thư gốc">
                </p>
                <p><button type="submit" class="nut-chinh">Xác nhận đặt hàng (<?= e(vnd($tong)) ?>)</button></p>
            </form>
<?php endif; ?>
        </section>

        <form method="post" action="gio-hang.php">
            <input type="hidden" name="hanhdong" value="xoahet">
            <button type="submit" class="nut-phu">Xóa hết giỏ hàng</button>
        </form>
<?php endif; ?>
    </main>
<?php require __DIR__ . '/inc/footer.php'; ?>

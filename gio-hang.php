<?php
/**
 * gio-hang.php - Giỏ hàng lưu trong session (lớp GioHang bọc $_SESSION['gio'] = [id => số lượng]).
 * POST hanhdong = them | capnhat | xoa | xoahet. Kiểm tra: id phải có trong dữ liệu, số lượng 1..99
 * (filter_var min_range/max_range); GIÁ lấy từ KhoSanPham, không lấy từ form. Thành công -> PRG.
 * Cách thử: thêm 2 sản phẩm, đổi số lượng, xóa một mục; thử sl=0, -3, 1000, id lạ bằng curl (xem tests/).
 */
require __DIR__ . '/inc/config.php';

use App\Data\KhoSanPham;
use App\Services\GioHang;

$kho = new KhoSanPham(__DIR__ . '/data/san-pham.json');
$gio = new GioHang();
$loi = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hanhDong = layChuoi($_POST, 'hanhdong');
    $id = GioHang::idHopLe($_POST['id'] ?? null);
    $sl = GioHang::soLuongHopLe($_POST['sl'] ?? null);

    switch ($hanhDong) {
        case 'them':
        case 'capnhat':
            if ($id === null || $kho->timTheoId($id) === null) {
                $loi = 'Sản phẩm không tồn tại, giỏ hàng được giữ nguyên.';
            } elseif ($sl === null) {
                $loi = 'Số lượng phải là số nguyên từ 1 đến ' . GioHang::SL_TOI_DA . ', giỏ hàng được giữ nguyên.';
            } elseif ($hanhDong === 'them') {
                $gio->them($id, $sl);
                datFlash('ok', 'Đã thêm sản phẩm vào giỏ hàng.');
            } elseif ($gio->capNhat($id, $sl)) {
                datFlash('ok', 'Đã cập nhật số lượng.');
            } else {
                $loi = 'Sản phẩm này không có trong giỏ.';
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
        header('Location: gio-hang.php');          // PRG: F5 không gửi lại thao tác
        exit;
    }
}

$dong = $gio->chiTiet($kho);
$tong = $gio->tongTien($kho);

$tieuDe = 'Giỏ hàng';
$trang = 'gio-hang';
$moTa = 'Giỏ hàng JadeHub: xem, đổi số lượng và xóa sản phẩm ngọc quý đã chọn.';
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
                <img src="images/<?= e($sp->anh) ?>" alt="" width="80" height="60" loading="lazy">
                <div class="thong-tin-dong">
                    <h2><a href="chi-tiet.php?id=<?= $sp->id ?>"><?= e($sp->ten) ?></a></h2>
                    <p>Đơn giá: <?= e(vnd($sp->gia)) ?></p>
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
        <form method="post" action="gio-hang.php">
            <input type="hidden" name="hanhdong" value="xoahet">
            <button type="submit" class="nut-phu">Xóa hết giỏ hàng</button>
        </form>
        <p>Để đặt mua hoặc thẩm định, hãy <a href="lien-he.php">gửi yêu cầu ở trang Liên hệ</a>.</p>
<?php endif; ?>
    </main>
<?php require __DIR__ . '/inc/footer.php'; ?>

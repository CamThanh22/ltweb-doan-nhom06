<?php
require dirname(__DIR__, 2) . '/inc/config.php';
require dirname(__DIR__, 2) . '/inc/don-hang.php';

use App\Data\KhoSanPham;
use App\Services\GioHang;

$id = GioHang::idHopLe($_GET['id'] ?? null);
$sp = $id === null ? null : (new KhoSanPham(db()))->timTheoId($id);
if ($sp === null) {
    http_response_code(404);
    require dirname(__DIR__, 2) . '/pages/loi/404.php';
    exit;
}

$nd = nguoiDung();
if ($nd === null) {
    header('Location: dang-nhap.php?mua_ngay_id=' . $id, true, 303);
    exit;
}

header('Cache-Control: no-store');
$toiDa = min(GioHang::SL_TOI_DA, $sp->tonKho);
$soLuong = 1;
$phuongThuc = 'cod';
$ghiChu = '';
$loi = '';
$maXacNhan = $_SESSION['mua_ngay_token'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $soLuong = filter_var($_POST['sl'] ?? null, FILTER_VALIDATE_INT);
    $phuongThuc = layChuoi($_POST, 'phuong_thuc');
    $ghiChu = mb_substr(layChuoi($_POST, 'ghi_chu'), 0, 500);
    $token = layChuoi($_POST, 'mua_ngay_token');

    if ($maXacNhan === '' || !hash_equals($maXacNhan, $token)
        || ($_SESSION['mua_ngay_id'] ?? null) !== $id) {
        $loi = 'Phiên đặt mua đã hết hiệu lực. Vui lòng kiểm tra lại đơn trước khi xác nhận.';
    } elseif ($soLuong === false || $soLuong < 1 || $soLuong > $toiDa) {
        $loi = 'Số lượng đặt mua vượt giới hạn hoặc tồn kho hiện tại.';
    } elseif (!in_array($phuongThuc, ['cod', 'chuyen_khoan'], true)) {
        $loi = 'Vui lòng chọn phương thức thanh toán hợp lệ.';
    } else {
        try {
            $maDon = luuDonHang([$id => $soLuong], (int) $nd['id'], $ghiChu, $phuongThuc);
            unset($_SESSION['mua_ngay_token'], $_SESSION['mua_ngay_id']);
            datFlash('ok', "Đặt hàng thành công! Mã đơn hàng của bạn là #$maDon.");
            header('Location: don-hang.php?id=' . $maDon, true, 303);
            exit;
        } catch (HetHangException $e) {
            $loi = 'Tồn kho vừa thay đổi. Vui lòng tải lại trang để kiểm tra số lượng còn lại.';
        }
    }
}

$maXacNhan = bin2hex(random_bytes(32));
$_SESSION['mua_ngay_token'] = $maXacNhan;
$_SESSION['mua_ngay_id'] = $id;
$tieuDe = 'Mua ngay';
$trang = 'chi-tiet';
require dirname(__DIR__, 2) . '/inc/header.php';
?>
<main id="noi-dung">
    <p><a href="chi-tiet.php?id=<?= $id ?>">← Về sản phẩm</a></p>
    <h1>Xác nhận đặt mua</h1>
    <?php require dirname(__DIR__, 2) . '/inc/thong-bao.php'; ?>
    <section class="khu-dat-hang" aria-labelledby="san-pham-mua">
        <h2 id="san-pham-mua"><?= e($sp->ten) ?></h2>
        <p>Đơn giá <strong><?= e(vnd($sp->gia)) ?></strong></p>
        <?php if ($loi !== ''): ?>
            <p class="thong-bao loi" role="alert"><?= e($loi) ?></p>
        <?php endif; ?>
        <?php if ($toiDa < 1): ?>
            <p role="status">Sản phẩm hiện đã hết hàng.</p>
        <?php else: ?>
            <form class="form-tham-dinh" method="post" action="mua-ngay.php?id=<?= $id ?>">
                <?= csrfField() ?>
                <input type="hidden" name="mua_ngay_token" value="<?= e($maXacNhan) ?>">
                <p><label for="mua-so-luong">Số lượng (1–<?= $toiDa ?>)</label>
                <input id="mua-so-luong" name="sl" type="number" min="1" max="<?= $toiDa ?>" value="<?= max(1, min($toiDa, (int) $soLuong)) ?>" required></p>
                <p><label for="mua-ghi-chu">Ghi chú giao hàng (không bắt buộc)</label>
                <input id="mua-ghi-chu" name="ghi_chu" type="text" maxlength="500" value="<?= e($ghiChu) ?>"></p>
                <fieldset><legend>Phương thức thanh toán</legend>
                    <label><input type="radio" name="phuong_thuc" value="cod" <?= $phuongThuc !== 'chuyen_khoan' ? 'checked' : '' ?>> Thanh toán khi nhận hàng (COD)</label>
                    <label><input type="radio" name="phuong_thuc" value="chuyen_khoan" <?= $phuongThuc === 'chuyen_khoan' ? 'checked' : '' ?>> Chuyển khoản – chờ nhân viên đối soát</label>
                    <p><small>Chỉ chuyển tiền sau khi nhận được hướng dẫn chính thức từ cửa hàng.</small></p>
                </fieldset>
                <button type="submit" class="nut-chinh">Xác nhận đặt hàng</button>
            </form>
        <?php endif; ?>
    </section>
</main>
<?php require dirname(__DIR__, 2) . '/inc/footer.php'; ?>

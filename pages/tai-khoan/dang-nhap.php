<?php
/**
 * dang-nhap.php - Đăng nhập với bảng nguoi_dung của MySQL (slide 41).
 * Dùng password_verify() và password_needs_rehash(); đăng nhập đúng gọi dangNhap() (cấp mã phiên mới
 * bằng session_regenerate_id(true) và chỉ lưu $_SESSION['uid']); hỗ trợ ghi nhớ đăng nhập (ghi_nho).
 * Sai email hoặc sai mật khẩu đều báo CÙNG một thông báo "Sai email hoặc mật khẩu.".
 * Cách thử: đăng nhập admin@ued.vn / lan@ued.vn với mật khẩu matkhau123; so giá trị PHPSESSID trước và sau.
 */
require dirname(__DIR__, 2) . '/inc/config.php';

$muaNgayId = App\Services\GioHang::idHopLe($_POST['mua_ngay_id'] ?? $_GET['mua_ngay_id'] ?? null);
$nhanTinId = App\Services\GioHang::idHopLe($_POST['nhan_tin_id'] ?? $_GET['nhan_tin_id'] ?? null);
$veTinNhan = $nhanTinId === null ? null : 'tin-nhan.php?id='.$nhanTinId;
$veDatMua = $muaNgayId === null ? null : 'mua-ngay.php?id=' . $muaNgayId;

if (nguoiDung() !== null) {
    header('Location: ' . ($veDatMua ?? $veTinNhan ?? 'index.php'));
    exit;
}

$email = '';
$loi   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = layChuoi($_POST, 'email');
    if ($email === '') {
        $email = layChuoi($_POST, 'ten'); // tương thích ngược nếu gửi tham số cũ
    }
    $mk = is_string($_POST['mat_khau'] ?? null)
        ? $_POST['mat_khau']
        : (is_string($_POST['matkhau'] ?? null) ? $_POST['matkhau'] : '');

    $dinhDanh = hash('sha256', mb_strtolower($email));
    $demSai = db()->prepare('SELECT COUNT(*) FROM dang_nhap_sai WHERE dinh_danh=? AND thoi_gian > NOW() - INTERVAL 15 MINUTE');
    $demSai->execute([$dinhDanh]);
    if ((int)$demSai->fetchColumn() >= 5) {
        http_response_code(429);
        $loi = 'Đăng nhập tạm khóa 15 phút. Vui lòng thử lại sau.';
    } else {
    $st = db()->prepare('SELECT id, mat_khau, vai_tro FROM nguoi_dung WHERE email = ?');
    $st->execute([$email]);
    $nd = $st->fetch();

    // Luôn gọi password_verify với băm giả nếu email không tồn tại để thời gian phản hồi đồng nhất
    $bamGia = '$2y$12$qHb8qF.xgsQsDEN5nvxo6OsTDLDQmDQmTnjDdlNCp7MqAzd4fkWBG';
    $bam    = $nd !== false ? (string) $nd['mat_khau'] : $bamGia;
    $matKhauDung = strlen($mk) <= 200 && password_verify($mk, $bam);
    $hopLe = ($nd !== false) && $matKhauDung;

    if ($hopLe) {
        if (password_needs_rehash($bam, PASSWORD_DEFAULT)) {
            db()->prepare('UPDATE nguoi_dung SET mat_khau = ? WHERE id = ?')
                ->execute([password_hash($mk, PASSWORD_DEFAULT), (int) $nd['id']]);
        }
        db()->prepare('DELETE FROM dang_nhap_sai WHERE dinh_danh=?')->execute([$dinhDanh]);
        dangNhap((int) $nd['id']);
        if (!empty($_POST['ghi_nho'])) {
            ghiNhoDangNhap((int) $nd['id']);
        }
        datFlash('ok', 'Đăng nhập thành công.');
        $dich = ($nd['vai_tro'] ?? '') === 'quan_tri' ? 'quan-tri.php' : 'index.php';
        header('Location: ' . ($veDatMua ?? $veTinNhan ?? $dich));
        exit;
    }

    db()->prepare('INSERT INTO dang_nhap_sai (dinh_danh) VALUES (?)')->execute([$dinhDanh]);
    ghiLogBaoMat('dang_nhap_sai');
    usleep(250000);
    $loi = 'Sai email hoặc mật khẩu.';
    }
}

$tieuDe = 'Đăng nhập';
$trang  = '';
$moTa   = 'Đăng nhập tài khoản khách hàng hoặc quản trị viên JadeHub.';
require dirname(__DIR__, 2) . '/inc/header.php';
?>
    <main id="noi-dung">
        <h1>Đăng nhập tài khoản JadeHub</h1>
<?php require dirname(__DIR__, 2) . '/inc/thong-bao.php'; ?>
<?php if ($loi !== ''): ?>
        <p class="thong-bao loi" role="alert"><?= e($loi) ?></p>
<?php endif; ?>
        <form class="form-tham-dinh form-dang-nhap" method="post" action="dang-nhap.php">
                <?= csrfField() ?>
<?php if($nhanTinId!==null): ?><input type="hidden" name="nhan_tin_id" value="<?= $nhanTinId ?>"><?php endif;?>
<?php if ($muaNgayId !== null): ?>
            <input type="hidden" name="mua_ngay_id" value="<?= $muaNgayId ?>">
<?php endif; ?>
            <p>
                <label for="dn-email">Email đăng nhập (*):</label>
                <input id="dn-email" name="email" type="email" maxlength="150" required autocomplete="username" value="<?= e($email) ?>" placeholder="Ví dụ: lan@ued.vn hoặc admin@ued.vn">
            </p>
            <p>
                <label for="dn-mk">Mật khẩu (*):</label>
                <input id="dn-mk" name="mat_khau" type="password" maxlength="200" required autocomplete="current-password">
            </p>
            <p>
                <input id="dn-ghinho" name="ghi_nho" type="checkbox" value="1">
                <label for="dn-ghinho">Ghi nhớ đăng nhập trên thiết bị này (30 ngày)</label>
            </p>
            <p><button type="submit">Đăng nhập</button></p>
        </form>
        <p>Chưa có tài khoản? <a href="dang-ky.php">Đăng ký tài khoản mới →</a></p>
        <p><small>Tài khoản thử (mật khẩu <code>matkhau123</code>): <code>admin@ued.vn</code> (quản trị), <code>lan@ued.vn</code> và <code>an@ued.vn</code> (khách hàng).</small></p>
    </main>
<?php require dirname(__DIR__, 2) . '/inc/footer.php'; ?>

<?php
/**
 * dang-nhap.php - Đăng nhập đơn giản: password_verify() với mật khẩu đã băm trong inc/tai-khoan.php.
 * Đăng nhập đúng -> session_regenerate_id(true) (chống chiếm phiên), lưu $_SESSION['user'], chuyển tới quan-tri.php.
 * Sai -> báo lỗi CHUNG (không cho biết sai tên hay sai mật khẩu) và ghi một dòng vào logs/php-error.log.
 * Cách thử: tài khoản thử trong README; thử sai mật khẩu rồi xem logs/php-error.log.
 */
require __DIR__ . '/inc/config.php';

if (isset($_SESSION['user'])) {
    header('Location: quan-tri.php');
    exit;
}

$taiKhoan = require __DIR__ . '/inc/tai-khoan.php';
$du = ['ten' => ''];
$loiChung = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $du['ten'] = mb_substr(layChuoi($_POST, 'ten'), 0, 50);
    $matKhau = is_string($_POST['matkhau'] ?? null) ? $_POST['matkhau'] : '';

    // Luôn gọi password_verify (kể cả khi tên không tồn tại) để thời gian phản hồi không lộ tên có thật
    $bam = $taiKhoan[$du['ten']]['hash'] ?? reset($taiKhoan)['hash'];
    $dung = isset($taiKhoan[$du['ten']])
        && strlen($matKhau) <= 200
        && password_verify($matKhau, $bam);

    if ($dung) {
        session_regenerate_id(true);               // cấp mã phiên mới khi đăng nhập
        $_SESSION['user'] = $du['ten'];
        datFlash('ok', 'Đăng nhập thành công.');
        header('Location: quan-tri.php');
        exit;
    }

    // Ghi log lần đăng nhập sai (bỏ ký tự điều khiển để không bị chèn dòng giả vào log)
    $tenLog = preg_replace('/[^\p{L}\p{N}_.@-]/u', '?', $du['ten']) ?? '';
    error_log('Dang nhap sai: ten=' . $tenLog . ' ip=' . ($_SERVER['REMOTE_ADDR'] ?? '?'));
    usleep(400000);                                // làm chậm việc dò mật khẩu
    $loiChung = 'Sai tên đăng nhập hoặc mật khẩu.';
}

$tieuDe = 'Đăng nhập';
$trang = '';
$moTa = 'Đăng nhập khu vực quản trị JadeHub.';
require __DIR__ . '/inc/header.php';
?>
    <main id="noi-dung">
        <h1>Đăng nhập quản trị</h1>
<?php require __DIR__ . '/inc/thong-bao.php'; ?>
<?php if ($loiChung !== ''): ?>
        <p class="thong-bao loi" role="alert"><?= e($loiChung) ?></p>
<?php endif; ?>
        <form class="form-tham-dinh form-dang-nhap" method="post" action="dang-nhap.php">
            <p>
                <label for="ten">Tên đăng nhập</label>
                <input id="ten" name="ten" type="text" maxlength="50" required autocomplete="username" value="<?= e($du['ten']) ?>">
            </p>
            <p>
                <label for="matkhau">Mật khẩu</label>
                <input id="matkhau" name="matkhau" type="password" maxlength="200" required autocomplete="current-password">
            </p>
            <p><button type="submit">Đăng nhập</button></p>
        </form>
        <p><small>Tài khoản thử được ghi trong README.md của dự án.</small></p>
    </main>
<?php require __DIR__ . '/inc/footer.php'; ?>

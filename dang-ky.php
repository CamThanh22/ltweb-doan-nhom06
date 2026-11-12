<?php
/**
 * dang-ky.php - Đăng ký tài khoản mới vào bảng nguoi_dung (slide 40).
 * Kiểm tra email hợp lệ, họ tên không rỗng, mật khẩu tối thiểu 8 ký tự.
 * Chỉ lưu chuỗi băm của password_hash(..., PASSWORD_DEFAULT); không nhận vai_tro từ form (luôn là khach_hang).
 * Bắt lỗi 1062 (Duplicate entry) khi email trùng (kể cả viết HOA nhờ collation utf8mb4_unicode_ci).
 * Cách thử: gửi email "abc", mật khẩu 7 ký tự; gửi email "LAN@UED.VN"; gửi kèm vai_tro=quan_tri bằng curl.
 */
require __DIR__ . '/inc/config.php';

if (nguoiDung() !== null) {
    header('Location: index.php');
    exit;
}

$du  = ['email' => '', 'ho_ten' => ''];
$loi = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $du['email']  = mb_strtolower(layChuoi($_POST, 'email'), 'UTF-8');
    $du['ho_ten'] = layChuoi($_POST, 'ho_ten');
    $mk           = is_string($_POST['mat_khau'] ?? null) ? $_POST['mat_khau'] : '';

    $emailHopLe = filter_var($du['email'], FILTER_VALIDATE_EMAIL);
    if ($emailHopLe === false || mb_strlen($du['email']) > 150) {
        $loi['email'] = 'Email không hợp lệ (ví dụ: ten@ued.vn).';
    }
    if ($du['ho_ten'] === '' || mb_strlen($du['ho_ten']) < 2 || mb_strlen($du['ho_ten']) > 100) {
        $loi['ho_ten'] = 'Vui lòng nhập họ và tên (2 – 100 ký tự).';
    }
    if (mb_strlen($mk) < 8 || mb_strlen($mk) > 200) {
        $loi['mat_khau'] = 'Mật khẩu tối thiểu 8 ký tự.';
    }

    if (!$loi) {
        try {
            $st = db()->prepare(
                'INSERT INTO nguoi_dung (email, ho_ten, mat_khau) VALUES (?, ?, ?)'
            );
            $st->execute([
                $emailHopLe,
                $du['ho_ten'],
                password_hash($mk, PASSWORD_DEFAULT),
            ]);
            datFlash('ok', 'Đăng ký thành công, mời bạn đăng nhập.');
            header('Location: dang-nhap.php');
            exit;
        } catch (PDOException $e) {
            if (($e->errorInfo[1] ?? 0) !== 1062) {
                throw $e;
            }
            $loi['email'] = 'Email này đã được đăng ký.';
        }
    }
}

$tieuDe = 'Đăng ký tài khoản';
$trang  = '';
$moTa   = 'Đăng ký tài khoản thành viên tại sàn giao dịch ngọc quý JadeHub.';
require __DIR__ . '/inc/header.php';
?>
    <main id="noi-dung">
        <h1>Đăng ký tài khoản thành viên</h1>
<?php require __DIR__ . '/inc/thong-bao.php'; ?>
<?php if ($loi): ?>
        <p class="thong-bao loi" role="alert">Có <?= count($loi) ?> ô chưa hợp lệ, vui lòng kiểm tra lại bên dưới.</p>
<?php endif; ?>
        <form class="form-tham-dinh form-dang-nhap" method="post" action="dang-ky.php">
            <p>
                <label for="dk-hoten">Họ và tên (*):</label>
                <input id="dk-hoten" name="ho_ten" type="text" maxlength="100" required autocomplete="name"
                       value="<?= e($du['ho_ten']) ?>"
                       <?= isset($loi['ho_ten']) ? 'aria-invalid="true" aria-describedby="loi-dk-hoten"' : '' ?>>
                <small class="loi-truong" id="loi-dk-hoten"><?= e($loi['ho_ten'] ?? '') ?></small>
            </p>
            <p>
                <label for="dk-email">Địa chỉ Email (*):</label>
                <input id="dk-email" name="email" type="email" maxlength="150" required autocomplete="email"
                       value="<?= e($du['email']) ?>"
                       <?= isset($loi['email']) ? 'aria-invalid="true" aria-describedby="loi-dk-email"' : '' ?>>
                <small class="loi-truong" id="loi-dk-email"><?= e($loi['email'] ?? '') ?></small>
            </p>
            <p>
                <label for="dk-mk">Mật khẩu (tối thiểu 8 ký tự) (*):</label>
                <input id="dk-mk" name="mat_khau" type="password" minlength="8" maxlength="200" required autocomplete="new-password"
                       <?= isset($loi['mat_khau']) ? 'aria-invalid="true" aria-describedby="loi-dk-mk"' : '' ?>>
                <small class="loi-truong" id="loi-dk-mk"><?= e($loi['mat_khau'] ?? '') ?></small>
            </p>
            <p><button type="submit">Đăng ký tài khoản</button></p>
        </form>
        <p>Đã có tài khoản? <a href="dang-nhap.php">Đăng nhập ngay →</a></p>
    </main>
<?php require __DIR__ . '/inc/footer.php'; ?>

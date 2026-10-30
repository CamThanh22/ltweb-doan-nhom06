<?php
/**
 * lien-he.php - Biểu mẫu liên hệ / đăng ký thẩm định, kiểm tra ở máy chủ và lưu vào bảng lien_he (MySQL)
 * qua lớp KhoLienHe(db()). Ảnh đính kèm (<= 2 MB) kiểm tra kiểu thật bằng finfo, lưu uploads/.
 * Nhờ lớp truy cập dữ liệu KhoLienHe, trang này chỉ đổi đúng dòng khởi tạo new KhoLienHe(db()).
 * Cách thử: gửi ô trống / email sai / ảnh giả; gửi hợp lệ rồi F5 (PRG).
 */
require __DIR__ . '/inc/config.php';

use App\Data\KhoLienHe;
use App\Services\TaiAnh;

const LOAI_NGOC = [
    'phithuy'  => 'Ngọc phỉ thúy (Jadeite)',
    'camthach' => 'Ngọc cẩm thạch',
    'hoadien'  => 'Ngọc bích Hòa Điền (Nephrite)',
    'khac'     => 'Loại đá quý khác',
];

$du = ['hoten' => '', 'email' => '', 'sdt' => '', 'loaingoc' => 'phithuy',
       'soluong' => '1', 'ngayhen' => '', 'ghichu' => '', 'camket' => ''];
$loi = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kichThuocGui = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
    $gioiHan = (int) ini_get('post_max_size');
    $donVi = strtoupper(substr(trim((string) ini_get('post_max_size')), -1));
    $gioiHan *= match ($donVi) { 'G' => 1073741824, 'M' => 1048576, 'K' => 1024, default => 1 };
    if (!$_POST && $kichThuocGui > 0 && $gioiHan > 0 && $kichThuocGui > $gioiHan) {
        $loi['anhngoc'] = 'Dữ liệu gửi lên quá lớn (ảnh phải nhỏ hơn 2 MB).';
    }

    foreach (['hoten', 'email', 'sdt', 'loaingoc', 'soluong', 'ngayhen', 'ghichu'] as $o) {
        $du[$o] = layChuoi($_POST, $o);
    }
    $du['camket'] = layChuoi($_POST, 'camket');
    $tep = isset($_FILES['anhngoc']) && is_array($_FILES['anhngoc']) ? $_FILES['anhngoc'] : null;

    if ($du['hoten'] === '') {
        $loi['hoten'] = 'Vui lòng nhập họ và tên.';
    } elseif (mb_strlen($du['hoten']) < 4 || mb_strlen($du['hoten']) > 50) {
        $loi['hoten'] = 'Họ và tên cần từ 4 đến 50 ký tự.';
    } elseif (preg_match('/^[\p{L} .\'-]+$/u', $du['hoten']) !== 1) {
        $loi['hoten'] = 'Họ và tên chỉ gồm chữ cái, khoảng trắng, dấu chấm, gạch nối.';
    }

    if ($du['email'] === '') {
        $loi['email'] = 'Vui lòng nhập địa chỉ email.';
    } elseif (mb_strlen($du['email']) > 100 || filter_var($du['email'], FILTER_VALIDATE_EMAIL) === false) {
        $loi['email'] = 'Email không hợp lệ (ví dụ: ten@example.com).';
    }

    if ($du['sdt'] === '') {
        $loi['sdt'] = 'Vui lòng nhập số điện thoại.';
    } elseif (preg_match('/^0[0-9]{9}\z/', $du['sdt']) !== 1) {
        $loi['sdt'] = 'Số điện thoại phải gồm 10 chữ số và bắt đầu bằng 0.';
    }

    if (!array_key_exists($du['loaingoc'], LOAI_NGOC)) {
        $loi['loaingoc'] = 'Loại ngọc không hợp lệ, vui lòng chọn trong danh sách.';
        $du['loaingoc'] = 'phithuy';
    }

    $soLuong = filter_var($du['soluong'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 20]]);
    if ($soLuong === false) {
        $loi['soluong'] = 'Số lượng mẫu phải là số nguyên từ 1 đến 20.';
    }

    $ngay = DateTimeImmutable::createFromFormat('!Y-m-d', $du['ngayhen']);
    if ($du['ngayhen'] === '') {
        $loi['ngayhen'] = 'Vui lòng chọn ngày dự kiến mang mẫu đến.';
    } elseif ($ngay === false || $ngay->format('Y-m-d') !== $du['ngayhen']) {
        $loi['ngayhen'] = 'Ngày hẹn không hợp lệ (định dạng năm-tháng-ngày).';
    } elseif ($ngay < new DateTimeImmutable('today')) {
        $loi['ngayhen'] = 'Ngày hẹn không được trước ngày hiện tại.';
    } elseif ($ngay > new DateTimeImmutable('+1 year')) {
        $loi['ngayhen'] = 'Ngày hẹn không được quá 1 năm kể từ hôm nay.';
    }

    if (mb_strlen($du['ghichu']) > 1000) {
        $loi['ghichu'] = 'Ghi chú tối đa 1000 ký tự.';
    }

    if (!in_array($du['camket'], ['on', '1'], true)) {
        $loi['camket'] = 'Bạn cần xác nhận cam kết sở hữu hợp pháp.';
    }

    $loiAnh = TaiAnh::kiemTra($tep);
    if ($loiAnh !== null) {
        $loi['anhngoc'] = $loiAnh;
    }

    if (!$loi) {
        $tenAnh = TaiAnh::coTep($tep) ? (new TaiAnh(__DIR__ . '/uploads'))->luu($tep) : null;

        $kho = new KhoLienHe(db());
        $kho->them([
            'hoTen'    => $du['hoten'],
            'email'    => $du['email'],
            'sdt'      => $du['sdt'],
            'loaiNgoc' => $du['loaingoc'],
            'soLuong'  => $soLuong,
            'ngayHen'  => $du['ngayhen'],
            'ghiChu'   => $du['ghichu'],
            'anh'      => $tenAnh,
        ]);
        datFlash('ok', 'Đã gửi liên hệ, JadeHub cảm ơn bạn và sẽ phản hồi sớm!');
        header('Location: lien-he.php');
        exit;
    }
}

$tieuDe = 'Liên hệ & Gửi mẫu thẩm định';
$trang = 'lien-he';
$moTa = 'Biểu mẫu liên hệ, đặt lịch gửi mẫu kiểm định ngọc quý và gửi phản hồi cho ban quản trị JadeHub.';
$jsThem = ['js/trang-lien-he.js'];
require __DIR__ . '/inc/header.php';

function loiO(array $loi, string $khoa, string $idO): string
{
    return '<small class="loi-truong" id="loi-' . e($idO) . '" data-loi="' . e($idO) . '">' . e($loi[$khoa] ?? '') . '</small>';
}
?>
    <main id="noi-dung">
        <h1>Gửi yêu cầu giám định và liên hệ</h1>
<?php require __DIR__ . '/inc/thong-bao.php'; ?>
        <section class="khu-vuc-lien-he">
            <h2>Biểu mẫu đăng ký thẩm định ngọc</h2>
            <p>Vui lòng điền đầy đủ các thông tin bên dưới. Các trường có dấu (*) là bắt buộc.</p>
<?php if ($loi): ?>
            <p class="thong-bao loi" role="alert">Có <?= count($loi) ?> ô chưa hợp lệ, vui lòng kiểm tra lại các thông báo màu đỏ bên dưới.</p>
<?php endif; ?>

            <form action="lien-he.php" class="form-tham-dinh" method="post" enctype="multipart/form-data">
                <fieldset>
                    <legend>1. Thông tin người gửi</legend>
                    <p>
                        <label for="txt-hoten">Họ và tên (*):</label>
                        <input id="txt-hoten" name="hoten" type="text" minlength="4" maxlength="50" required
                               value="<?= e($du['hoten']) ?>" placeholder="Ví dụ: Trần Thị Cẩm Thanh"
                               <?= isset($loi['hoten']) ? 'aria-invalid="true" aria-describedby="loi-txt-hoten"' : '' ?>>
                        <?= loiO($loi, 'hoten', 'txt-hoten') ?>
                    </p>
                    <p>
                        <label for="txt-email">Địa chỉ Email (*):</label>
                        <input id="txt-email" name="email" type="email" maxlength="100" required
                               value="<?= e($du['email']) ?>" placeholder="name@domain.com"
                               <?= isset($loi['email']) ? 'aria-invalid="true" aria-describedby="loi-txt-email"' : '' ?>>
                        <?= loiO($loi, 'email', 'txt-email') ?>
                    </p>
                    <p>
                        <label for="txt-sdt">Số điện thoại (*):</label>
                        <input id="txt-sdt" name="sdt" type="tel" pattern="0[0-9]{9}" required
                               value="<?= e($du['sdt']) ?>" placeholder="Ví dụ: 0912345678 (10 chữ số)"
                               <?= isset($loi['sdt']) ? 'aria-invalid="true" aria-describedby="loi-txt-sdt"' : '' ?>>
                        <?= loiO($loi, 'sdt', 'txt-sdt') ?>
                    </p>
                </fieldset>

                <fieldset>
                    <legend>2. Chi tiết hiện vật cần thẩm định</legend>
                    <p>
                        <label for="opt-loaingoc">Loại ngọc quý:</label><br>
                        <select id="opt-loaingoc" name="loaingoc">
<?php foreach (LOAI_NGOC as $khoa => $nhan): ?>
                            <option value="<?= e($khoa) ?>"<?= $khoa === $du['loaingoc'] ? ' selected' : '' ?>><?= e($nhan) ?></option>
<?php endforeach; ?>
                        </select>
                        <?= loiO($loi, 'loaingoc', 'opt-loaingoc') ?>
                    </p>
                    <p>
                        <label for="num-soluong">Số lượng mẫu gửi (1 - 20 viên) (*):</label><br>
                        <input id="num-soluong" name="soluong" type="number" min="1" max="20" required
                               value="<?= e($du['soluong']) ?>"
                               <?= isset($loi['soluong']) ? 'aria-invalid="true" aria-describedby="loi-num-soluong"' : '' ?>>
                        <?= loiO($loi, 'soluong', 'num-soluong') ?>
                    </p>
                    <p>
                        <label for="date-hen">Ngày dự kiến mang mẫu đến phòng lab (*):</label><br>
                        <input id="date-hen" name="ngayhen" type="date" required min="<?= e(date('Y-m-d')) ?>"
                               value="<?= e($du['ngayhen']) ?>"
                               <?= isset($loi['ngayhen']) ? 'aria-invalid="true" aria-describedby="loi-date-hen"' : '' ?>>
                        <?= loiO($loi, 'ngayhen', 'date-hen') ?>
                    </p>
                    <p>
                        <label for="file-anh">Ảnh chụp mẫu ngọc hoặc giấy tờ kèm theo (JPG, PNG, WebP, tối đa 2 MB; không bắt buộc):</label><br>
                        <input id="file-anh" name="anhngoc" type="file" accept="image/jpeg, image/png, image/webp"
                               <?= isset($loi['anhngoc']) ? 'aria-invalid="true" aria-describedby="loi-file-anh"' : '' ?>>
                        <?= loiO($loi, 'anhngoc', 'file-anh') ?>
<?php if ($loi && TaiAnh::coTep($tep ?? null)): ?>
                        <small>Do trình duyệt không giữ lại tệp, bạn cần chọn lại ảnh sau khi sửa lỗi.</small>
<?php endif; ?>
                    </p>
                    <p>
                        <label for="txt-ghichu">Mô tả đặc điểm mẫu hoặc yêu cầu riêng:</label><br>
                        <textarea id="txt-ghichu" name="ghichu" rows="4" cols="50" maxlength="1000"
                                  placeholder="Ví dụ: Cần kiểm tra xem có tẩm polymer hay nhuộm màu không..."
                                  <?= isset($loi['ghichu']) ? 'aria-invalid="true" aria-describedby="loi-txt-ghichu"' : '' ?>><?= e($du['ghichu']) ?></textarea>
                        <?= loiO($loi, 'ghichu', 'txt-ghichu') ?>
                    </p>
                </fieldset>

                <fieldset>
                    <legend>3. Xác nhận</legend>
                    <p>
                        <input id="chk-camket" name="camket" type="checkbox" required<?= in_array($du['camket'], ['on', '1'], true) ? ' checked' : '' ?>
                               <?= isset($loi['camket']) ? 'aria-invalid="true" aria-describedby="loi-chk-camket"' : '' ?>>
                        <label for="chk-camket">Tôi cam kết hiện vật gửi thẩm định là sở hữu hợp pháp (*)</label>
                        <?= loiO($loi, 'camket', 'chk-camket') ?>
                    </p>
                </fieldset>

                <p>
                    <button type="submit">Gửi thông tin thẩm định</button>
                    <button type="reset">Nhập lại từ đầu</button>
                </p>
            </form>
            <p id="ket-qua-form" role="status"></p>
        </section>
    </main>
<?php require __DIR__ . '/inc/footer.php'; ?>

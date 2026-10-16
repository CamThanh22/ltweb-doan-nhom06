<?php
/**
 * thanhvien/3120224063_hong/gioithieu.php - trang cá nhân của Nguyễn Nhật Ánh Hồng (dùng chung header/footer).
 * Chức năng PHP 1: ảnh đại diện tải lên an toàn (finfo, 2 MB, tên ngẫu nhiên). 2: thời khóa biểu lọc theo thứ (GET ?thu=).
 * Cách thử: ?thu=tu, ?thu=abc (bị bỏ qua); tải tệp văn bản đổi đuôi .jpg lên -> bị từ chối.
 */
require __DIR__ . '/../../inc/config.php';
use App\Data\KhoLienHe;
use App\Services\TaiAnh;


// Dữ liệu thời khóa biểu (mảng PHP) - chức năng 2 lọc theo thứ bằng tham số GET ?thu=
$tkb = [
    'hai' => ['thu' => 'Thứ Hai', 'sang' => 'Tự học', 'chieu' => 'Tự học'],
    'ba' => ['thu' => 'Thứ Ba', 'sang' => 'Tự học', 'chieu' => 'Tự học'],
    'tu' => ['thu' => 'Thứ Tư', 'sang' => 'Học trên trường', 'chieu' => 'Làm bài nhóm'],
    'nam' => ['thu' => 'Thứ Năm', 'sang' => 'Học trên lớp', 'chieu' => 'Làm bài nhóm'],
    'sau' => ['thu' => 'Thứ Sáu', 'sang' => 'Học trên lớp', 'chieu' => 'Sinh hoạt nhóm'],
    'bay' => ['thu' => 'Thứ Bảy', 'sang' => 'Làm bài tập', 'chieu' => 'Làm dự án JadeHub'],
];
$thuLoc = layChuoi($_GET, 'thu');
if ($thuLoc !== '' && !array_key_exists($thuLoc, $tkb)) {
    $thuLoc = '';                      // chỉ nhận giá trị trong danh sách cho phép
}
$dongTkb = $thuLoc === '' ? $tkb : [$thuLoc => $tkb[$thuLoc]];

// Chức năng 1: ảnh đại diện tải lên an toàn (finfo, <= 2 MB, tên ngẫu nhiên, lưu uploads/)
$tepGhiNho = __DIR__ . '/../../storage/3120224063_avatar.txt';
$tenAnh = is_file($tepGhiNho) ? trim((string) file_get_contents($tepGhiNho)) : '';
$anhDaiDien = KhoLienHe::tenAnhHopLe($tenAnh) && is_file(__DIR__ . '/../../uploads/' . $tenAnh)
    ? '../../uploads/' . $tenAnh : 'chan-dung-hong.jpg';
$loiAnh = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tep = isset($_FILES['anhdaidien']) && is_array($_FILES['anhdaidien']) ? $_FILES['anhdaidien'] : null;
    if (!TaiAnh::coTep($tep)) {
        $loiAnh = 'Vui lòng chọn một ảnh JPG, PNG hoặc WebP.';
    } else {
        $loiAnh = TaiAnh::kiemTra($tep) ?? '';
    }
    if ($loiAnh === '') {
        $moi = (new TaiAnh(__DIR__ . '/../../uploads'))->luu($tep);
        file_put_contents($tepGhiNho, $moi, LOCK_EX);
        if (KhoLienHe::tenAnhHopLe($tenAnh)) {
            $cu = __DIR__ . '/../../uploads/' . $tenAnh;
            if (is_file($cu)) {
                unlink($cu);           // xóa ảnh đại diện cũ
            }
        }
        datFlash('ok', 'Đã cập nhật ảnh đại diện.');
        header('Location: gioithieu.php');   // PRG
        exit;
    }
}

$goc = '../../';                      // đường dẫn từ trang này về thư mục gốc
$tieuDe = 'Hồ sơ cá nhân | Nguyễn Nhật Ánh Hồng';
$trang = 'gioi-thieu';
$moTa = 'Trang giới thiệu cá nhân, kỹ năng chuyên môn và thời khóa biểu của sinh viên Nguyễn Nhật Ánh Hồng.';
$bodyClass = '';
$cssThem = ['css/style-hong.css'];
$jsThem = ['js/canhan.js'];
require __DIR__ . '/../../inc/header.php';
?>
    <main id="noi-dung">
<?php require __DIR__ . '/../../inc/thong-bao.php'; ?>
                <h1>Hồ sơ sinh viên: Nguyễn Nhật Ánh Hồng</h1>
                <section>
                    <h2>1. Thông tin bản thân</h2>
                    <figure>
                        <img alt="Ảnh thẻ chân dung của sinh viên Nguyễn Nhật Ánh Hồng" src="<?= e($anhDaiDien) ?>" width="200" />
                        <figcaption>Ảnh 1: Chân dung sinh viên</figcaption>
                    </figure>
                    <p><strong>Mã sinh viên:</strong> 3120224063 </p>
                    <p>
                        <strong>Lớp:</strong> 24CNTT3 — Trường Đại học Sư phạm -
                        Đại học Đà Nẵng
                    </p>
                    <p>
                        <strong>Vai trò trong nhóm:</strong> Thành viên,
                        tham gia xây dựng và phát triển dự án JadeHub.
                    </p>
                </section>
                <article>
                    <h2>2. Định hướng chuyên môn và kỹ năng</h2>
                    <p>
                        Em định hướng phát triển trong lĩnh vực công nghệ thông tin,
                        đặc biệt là công nghệ trong giáo dục. Em yêu thích việc tìm
                        hiểu công nghệ mới và phát triển các sản phẩm website
                        có tính thực tiễn cao.
                    </p>
                    <h3>Kỹ năng chính:</h3>
                    <ul>
                        <li>HTML5, CSS3 và PHP cơ bản</li>
                        <li>Git và GitHub</li>
                        <li>Sử dụng Visual Studio Code</li>
                        <li>Làm việc nhóm và giải quyết vấn đề</li>
                        <li>Tìm hiểu và phát triển giao diện website</li>
                    </ul>
                </article>
                <section>
                    <h2>3. Thời khóa biểu học tập tuần này</h2>
        <form method="get" action="gioithieu.php#tkb" class="form-loc-thu">
            <label for="loc-thu">Xem theo thứ</label>
            <select id="loc-thu" name="thu">
                <option value="">Cả tuần</option>
        <?php foreach ($tkb as $khoa => $d): ?>
                <option value="<?= e($khoa) ?>"<?= $khoa === $thuLoc ? ' selected' : '' ?>><?= e($d['thu']) ?></option>
        <?php endforeach; ?>
            </select>
            <button type="submit">Lọc</button>
        </form>
        <div class="khung-thoi-khoa-bieu" id="tkb">
            <table>
                <caption>Bảng 1: Lịch học các học phần trong tuần</caption>
                <thead>
                    <tr><th scope="col">Thứ</th><th scope="col">Buổi sáng (Tiết 1 - 6)</th><th scope="col">Buổi chiều (Tiết 7 - 12)</th></tr>
                </thead>
                <tbody>
        <?php foreach ($dongTkb as $d): ?>
                    <tr><th scope="row"><?= e($d['thu']) ?></th><td><?= e($d['sang']) ?></td><td><?= e($d['chieu']) ?></td></tr>
        <?php endforeach; ?>
                </tbody>
            </table>
        </div>
                </section>
    

        <section id="avatar">
            <h2>4. Đổi ảnh đại diện</h2>
            <p>Chọn ảnh JPG, PNG hoặc WebP (tối đa 2 MB). Máy chủ kiểm tra kiểu thật của tệp và đặt tên ngẫu nhiên.</p>
<?php if ($loiAnh !== ''): ?>
            <p class="thong-bao loi" role="alert"><?= e($loiAnh) ?></p>
<?php endif; ?>
            <form method="post" action="gioithieu.php" enctype="multipart/form-data">
                <label for="anhdaidien">Ảnh đại diện mới</label>
                <input id="anhdaidien" name="anhdaidien" type="file" accept="image/jpeg, image/png, image/webp" required>
                <button type="submit">Tải ảnh lên</button>
            </form>
        </section>
    </main>
<?php require __DIR__ . '/../../inc/footer.php'; ?>

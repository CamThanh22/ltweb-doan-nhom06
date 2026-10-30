<?php
/**
 * thanhvien/3120224063_hong/gioithieu.php - Trang cá nhân của Nguyễn Nhật Ánh Hồng (Phần B - MySQL).
 * Chức năng CSDL 1: Thời khóa biểu lưu trong bảng tv_thoi_khoa_bieu, tìm kiếm theo môn/phòng và lọc theo thứ (?thu=&q=);
 * chủ trang (hong@ued.vn) đăng nhập có thể thêm/xóa ca học trong CSDL (PRG).
 * Chức năng CSDL 2: Ảnh đại diện tải lên an toàn (finfo, <= 2 MB, tên ngẫu nhiên) và cập nhật cột anh_dai_dien
 * trong bảng thanh_vien (chỉ chủ trang hoặc quản trị viên được phép đổi).
 * Cách thử: lọc ?thu=tu&q=JadeHub; đăng nhập hong@ued.vn để thêm ca học hoặc đổi ảnh đại diện.
 */
require __DIR__ . '/../../inc/config.php';

use App\Data\KhoLienHe;
use App\Services\TaiAnh;

const MSSV = '3120224063';
$goc = '../../';

const DS_THU = [
    'hai' => 'Thứ Hai',
    'ba'  => 'Thứ Ba',
    'tu'  => 'Thứ Tư',
    'nam' => 'Thứ Năm',
    'sau' => 'Thứ Sáu',
    'bay' => 'Thứ Bảy',
];

$stTv = db()->prepare('SELECT ho_ten, nguoi_dung_id, anh_dai_dien FROM thanh_vien WHERE mssv = ?');
$stTv->execute([MSSV]);
$tv = $stTv->fetch() ?: ['ho_ten' => 'Nguyễn Nhật Ánh Hồng', 'nguoi_dung_id' => null, 'anh_dai_dien' => 'chan-dung-hong.jpg'];
$laChuTrang = ((int) (nguoiDung()['id'] ?? 0)) !== 0
    && (((int) (nguoiDung()['id'] ?? 0)) === (int) ($tv['nguoi_dung_id'] ?? -1) || laQuanTri());

$tenAnh = (string) ($tv['anh_dai_dien'] ?? '');
$anhDaiDien = KhoLienHe::tenAnhHopLe($tenAnh) && is_file(__DIR__ . '/../../uploads/' . $tenAnh)
    ? '../../uploads/' . $tenAnh
    : 'chan-dung-hong.jpg';

$loiAnh = '';
$loiTkb = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hd = layChuoi($_POST, 'hanhdong');

    if ($hd === 'them_tkb') {
        if (!$laChuTrang) {
            datFlash('loi', 'Chỉ chủ trang mới được thêm lịch học.');
            header('Location: gioithieu.php#tkb');
            exit;
        }
        $thu     = layChuoi($_POST, 'thu');
        $buoi    = layChuoi($_POST, 'buoi');
        $hocPhan = layChuoi($_POST, 'hoc_phan');
        $phong   = layChuoi($_POST, 'phong') ?: 'Tự học';

        if (!array_key_exists($thu, DS_THU)) {
            $loiTkb['thu'] = 'Chọn thứ trong danh sách.';
        }
        if (!in_array($buoi, ['Sáng', 'Chiều'], true)) {
            $loiTkb['buoi'] = 'Chọn buổi Sáng hoặc Chiều.';
        }
        if (mb_strlen($hocPhan) < 3 || mb_strlen($hocPhan) > 150) {
            $loiTkb['hoc_phan'] = 'Tên học phần / công việc từ 3 đến 150 ký tự.';
        }
        if (!$loiTkb) {
            db()->prepare(
                'INSERT INTO tv_thoi_khoa_bieu (mssv, thu, buoi, hoc_phan, phong) VALUES (?, ?, ?, ?, ?)'
            )->execute([MSSV, $thu, $buoi, $hocPhan, mb_substr($phong, 0, 50)]);
            datFlash('ok', 'Đã thêm lịch học mới vào CSDL.');
            header('Location: gioithieu.php#tkb');
            exit;
        }
    } elseif ($hd === 'xoa_tkb') {
        $idXoa = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($laChuTrang && $idXoa !== false) {
            db()->prepare('DELETE FROM tv_thoi_khoa_bieu WHERE id = ? AND mssv = ?')
                ->execute([(int) $idXoa, MSSV]);
            datFlash('ok', 'Đã xóa dòng lịch học.');
        }
        header('Location: gioithieu.php#tkb');
        exit;
    } else {
        $tep = isset($_FILES['anhdaidien']) && is_array($_FILES['anhdaidien']) ? $_FILES['anhdaidien'] : null;
        if (!TaiAnh::coTep($tep)) {
            $loiAnh = 'Vui lòng chọn một ảnh JPG, PNG hoặc WebP.';
        } else {
            $loiAnh = TaiAnh::kiemTra($tep) ?? '';
        }
        if ($loiAnh === '') {
            $moi = (new TaiAnh(__DIR__ . '/../../uploads'))->luu($tep);
            db()->prepare('UPDATE thanh_vien SET anh_dai_dien = ? WHERE mssv = ?')
                ->execute([$moi, MSSV]);
            if (KhoLienHe::tenAnhHopLe($tenAnh)) {
                $cu = __DIR__ . '/../../uploads/' . $tenAnh;
                if (is_file($cu)) {
                    unlink($cu);
                }
            }
            datFlash('ok', 'Đã cập nhật ảnh đại diện vào CSDL.');
            header('Location: gioithieu.php#avatar');
            exit;
        }
    }
}

// Lọc thời khóa biểu từ bảng tv_thoi_khoa_bieu
$thuLoc = layChuoi($_GET, 'thu');
if ($thuLoc !== '' && !array_key_exists($thuLoc, DS_THU)) {
    $thuLoc = '';
}
$qTkb = mb_substr(layChuoi($_GET, 'q'), 0, 80);

$dk = ['mssv = ?'];
$ts = [MSSV];
if ($thuLoc !== '') {
    $dk[] = 'thu = ?';
    $ts[] = $thuLoc;
}
if ($qTkb !== '') {
    $mau = '%' . addcslashes($qTkb, '%_\\') . '%';
    $dk[] = '(hoc_phan LIKE ? OR phong LIKE ?)';
    $ts[] = $mau;
    $ts[] = $mau;
}
$stTkb = db()->prepare(
    'SELECT id, thu, buoi, hoc_phan, phong FROM tv_thoi_khoa_bieu
     WHERE ' . implode(' AND ', $dk) . '
     ORDER BY FIELD(thu, "hai", "ba", "tu", "nam", "sau", "bay"), FIELD(buoi, "Sáng", "Chiều"), id ASC'
);
$stTkb->execute($ts);
$dongTkb = $stTkb->fetchAll();

$tieuDe    = 'Hồ sơ cá nhân | Nguyễn Nhật Ánh Hồng';
$trang     = 'gioi-thieu';
$moTa      = 'Trang giới thiệu cá nhân, thời khóa biểu MySQL và đổi ảnh đại diện của sinh viên Nguyễn Nhật Ánh Hồng.';
$bodyClass = '';
$cssThem   = ['css/style-hong.css'];
$jsThem    = ['js/canhan.js'];
require __DIR__ . '/../../inc/header.php';
?>
    <main id="noi-dung">
<?php require __DIR__ . '/../../inc/thong-bao.php'; ?>
        <h1>Hồ sơ sinh viên: <?= e((string) $tv['ho_ten']) ?></h1>
        <section>
            <h2>1. Thông tin bản thân</h2>
            <figure>
                <img alt="Ảnh thẻ chân dung của sinh viên Nguyễn Nhật Ánh Hồng" src="<?= e($anhDaiDien) ?>" width="200">
                <figcaption>Ảnh 1: Chân dung sinh viên </figcaption>
            </figure>
            <p><strong>Mã sinh viên:</strong> <?= e(MSSV) ?></p>
            <p><strong>Lớp:</strong> 24CNTT3 — Trường Đại học Sư phạm - Đại học Đà Nẵng</p>
            <p><strong>Vai trò trong nhóm:</strong> Thành viên, phụ trách Chức năng 3 (CRUD Quản trị sản phẩm và xem liên hệ ở quan-tri.php, them-san-pham.php, sua-san-pham.php, xoa-san-pham.php).</p>
        </section>

        <article>
            <h2>2. Định hướng chuyên môn và kỹ năng</h2>
            <p>Em định hướng phát triển trong lĩnh vực công nghệ thông tin và công nghệ giáo dục, yêu thích xây dựng giao diện web thân thiện, chuẩn khả năng tiếp cận (Accessibility).</p>
            <ul>
                <li>Lập trình Web: HTML5, CSS3, PHP 8 &amp; MySQL (PDO)</li>
                <li>Quản lý mã nguồn: Git và GitHub</li>
                <li>Đánh giá chất lượng: W3C Validator, Chrome Lighthouse</li>
            </ul>
        </article>

        <section id="tkb">
            <h2>3. Thời khóa biểu học tập từ CSDL </h2>
            <form method="get" action="gioithieu.php#tkb" class="form-loc-thu">
                <label for="loc-thu">Thứ:</label>
                <select id="loc-thu" name="thu">
                    <option value="">Cả tuần</option>
<?php foreach (DS_THU as $khoa => $nhan): ?>
                    <option value="<?= e($khoa) ?>"<?= $khoa === $thuLoc ? ' selected' : '' ?>><?= e($nhan) ?></option>
<?php endforeach; ?>
                </select>
                <label for="tim-tkb">Từ khóa:</label>
                <input id="tim-tkb" name="q" type="search" maxlength="80" value="<?= e($qTkb) ?>" placeholder="Môn học hoặc phòng…">
                <button type="submit">Lọc</button>
<?php if ($thuLoc !== '' || $qTkb !== ''): ?>
                <a href="gioithieu.php#tkb" class="nut-phu">Xóa lọc</a>
<?php endif; ?>
            </form>

            <div class="khung-thoi-khoa-bieu">
                <table>
                    <caption>Bảng 1: Lịch học các học phần trong tuần (<?= count($dongTkb) ?> ca học)</caption>
                    <thead>
                        <tr>
                            <th scope="col">Thứ</th>
                            <th scope="col">Buổi</th>
                            <th scope="col">Học phần / Hoạt động</th>
                            <th scope="col">Phòng</th>
<?php if ($laChuTrang): ?>
                            <th scope="col">Xóa</th>
<?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
<?php foreach ($dongTkb as $d): ?>
                        <tr>
                            <th scope="row"><?= e(DS_THU[$d['thu']] ?? (string) $d['thu']) ?></th>
                            <td><?= e((string) $d['buoi']) ?></td>
                            <td><?= e((string) $d['hoc_phan']) ?></td>
                            <td><?= e((string) $d['phong']) ?></td>
<?php if ($laChuTrang): ?>
                            <td>
                                <form method="post" action="gioithieu.php#tkb" style="display:inline">
                                    <input type="hidden" name="hanhdong" value="xoa_tkb">
                                    <input type="hidden" name="id" value="<?= (int) $d['id'] ?>">
                                    <button type="submit">Xóa</button>
                                </form>
                            </td>
<?php endif; ?>
                        </tr>
<?php endforeach; ?>
                    </tbody>
                </table>
            </div>

<?php if ($laChuTrang): ?>
            <h3>Thêm ca học mới</h3>
            <form method="post" action="gioithieu.php#tkb" class="form-tham-dinh">
                <input type="hidden" name="hanhdong" value="them_tkb">
                <p>
                    <label for="tkb-thu">Thứ (*)</label>
                    <select id="tkb-thu" name="thu">
<?php foreach (DS_THU as $khoa => $nhan): ?>
                        <option value="<?= e($khoa) ?>"><?= e($nhan) ?></option>
<?php endforeach; ?>
                    </select>
                </p>
                <p>
                    <label for="tkb-buoi">Buổi (*)</label>
                    <select id="tkb-buoi" name="buoi">
                        <option value="Sáng">Sáng</option>
                        <option value="Chiều">Chiều</option>
                    </select>
                </p>
                <p>
                    <label for="tkb-hp">Học phần (*)</label>
                    <input id="tkb-hp" name="hoc_phan" type="text" maxlength="150" required>
                    <small class="loi-truong"><?= e($loiTkb['hoc_phan'] ?? '') ?></small>
                </p>
                <p>
                    <label for="tkb-phong">Phòng học</label>
                    <input id="tkb-phong" name="phong" type="text" maxlength="50" value="Phòng B1.204">
                </p>
                <button type="submit">Thêm vào thời khóa biểu</button>
            </form>
<?php endif; ?>
        </section>

        <section id="avatar">
            <h2>4. Đổi ảnh đại diện lưu vào CSDL</h2>
            <p>Chọn ảnh JPG, PNG hoặc WebP (tối đa 2 MB). Máy chủ kiểm tra kiểu thật bằng finfo, đặt tên ngẫu nhiên và lưu vào cột anh_dai_dien của bảng thanh_vien.</p>
<?php if ($loiAnh !== ''): ?>
            <p class="thong-bao loi" role="alert"><?= e($loiAnh) ?></p>
<?php endif; ?>
            <form method="post" action="gioithieu.php#avatar" enctype="multipart/form-data">
                <label for="anhdaidien">Ảnh đại diện mới</label>
                <input id="anhdaidien" name="anhdaidien" type="file" accept="image/jpeg, image/png, image/webp" required>
                <button type="submit">Tải ảnh lên</button>
            </form>
        </section>
    </main>
<?php require __DIR__ . '/../../inc/footer.php'; ?>

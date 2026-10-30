<?php
/**
 * thanhvien/3120124031_trinh/gioithieu.php - Trang cá nhân của Huỳnh Trần Phương Trinh (Phần B - MySQL).
 * Chức năng CSDL 1: Danh sách kỹ năng lưu trong bảng tv_ky_nang, lọc theo nhóm (?nhom=) và sắp xếp theo mức độ thành thạo.
 * Chức năng CSDL 2: Danh sách việc cần làm lưu trong bảng tv_viec_can_lam (thay cho session ở Bài tập 5):
 * thêm việc mới, đánh dấu hoàn thành / chưa xong, xóa việc (POST + PRG).
 * Cách thử: lọc ?nhom=web; thêm/đánh dấu/xóa việc rồi đóng mở trình duyệt -> dữ liệu vẫn còn trong MySQL.
 */
require __DIR__ . '/../../inc/config.php';

const MSSV = '3120124031';
$goc = '../../';

$nhomKyNang = [
    'web'      => 'Lập trình Web',
    'csdl'     => 'Cơ sở dữ liệu',
    'cong-cu'  => 'Công cụ & Kiểm thử',
    'he-thong' => 'Hệ thống',
];

$nhomLoc = layChuoi($_GET, 'nhom');
if ($nhomLoc !== '' && !array_key_exists($nhomLoc, $nhomKyNang)) {
    $nhomLoc = '';
}

// Chức năng CSDL 1: Đọc kỹ năng từ bảng tv_ky_nang
if ($nhomLoc !== '') {
    $stKn = db()->prepare('SELECT id, nhom, ten, muc_do FROM tv_ky_nang WHERE mssv = ? AND nhom = ? ORDER BY muc_do DESC, id ASC');
    $stKn->execute([MSSV, $nhomLoc]);
} else {
    $stKn = db()->prepare('SELECT id, nhom, ten, muc_do FROM tv_ky_nang WHERE mssv = ? ORDER BY muc_do DESC, id ASC');
    $stKn->execute([MSSV]);
}
$kyNangHienThi = $stKn->fetchAll();

// Chức năng CSDL 2: Việc cần làm lưu trong bảng tv_viec_can_lam (CRUD + PRG)
$loiViec = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hd     = layChuoi($_POST, 'hanhdong');
    $idViec = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    if ($hd === 'them') {
        $nd = layChuoi($_POST, 'noidung');
        $stDem = db()->prepare('SELECT COUNT(*) FROM tv_viec_can_lam WHERE mssv = ?');
        $stDem->execute([MSSV]);
        $soViec = (int) $stDem->fetchColumn();

        if (mb_strlen($nd) < 3 || mb_strlen($nd) > 150) {
            $loiViec = 'Nội dung việc cần từ 3 đến 150 ký tự.';
        } elseif ($soViec >= 15) {
            $loiViec = 'Chỉ lưu tối đa 15 việc trong danh sách, hãy xóa bớt trước.';
        } else {
            db()->prepare('INSERT INTO tv_viec_can_lam (mssv, noi_dung) VALUES (?, ?)')
                ->execute([MSSV, $nd]);
            datFlash('ok', 'Đã thêm công việc mới vào CSDL.');
        }
    } elseif ($hd === 'xong' && $idViec !== false) {
        db()->prepare('UPDATE tv_viec_can_lam SET da_xong = NOT da_xong WHERE id = ? AND mssv = ?')
            ->execute([(int) $idViec, MSSV]);
    } elseif ($hd === 'xoa' && $idViec !== false) {
        db()->prepare('DELETE FROM tv_viec_can_lam WHERE id = ? AND mssv = ?')
            ->execute([(int) $idViec, MSSV]);
        datFlash('ok', 'Đã xóa công việc.');
    } else {
        $loiViec = 'Thao tác không hợp lệ.';
    }

    if ($loiViec === '') {
        header('Location: gioithieu.php#viec');
        exit;
    }
}

$stViec = db()->prepare('SELECT id, noi_dung, da_xong, ngay_tao FROM tv_viec_can_lam WHERE mssv = ? ORDER BY da_xong ASC, id DESC');
$stViec->execute([MSSV]);
$viec = $stViec->fetchAll();

$tieuDe    = 'Hồ sơ cá nhân | Huỳnh Trần Phương Trinh';
$trang     = 'gioi-thieu';
$moTa      = 'Trang giới thiệu cá nhân, kỹ năng chuyên môn và danh sách công việc (MySQL) của sinh viên Huỳnh Trần Phương Trinh.';
$bodyClass = '';
$cssThem   = ['css/style-trinh.css'];
$jsThem    = ['js/canhan.js'];
require __DIR__ . '/../../inc/header.php';
?>
    <main id="noi-dung">
<?php require __DIR__ . '/../../inc/thong-bao.php'; ?>
        <h1>Hồ sơ sinh viên: Huỳnh Trần Phương Trinh</h1>
        <section id="gioi-thieu">
            <h2>1. Giới thiệu bản thân &amp; Kỹ năng </h2>
            <img alt="Ảnh chân dung của Huỳnh Trần Phương Trinh" height="150" src="chan-dung-trinh.jpg" width="150">
            <p><strong>Họ và tên:</strong> Huỳnh Trần Phương Trinh — <strong>MSSV:</strong> <?= e(MSSV) ?></p>
            <p><strong>Vai trò trong nhóm:</strong> Thành viên, phụ trách Chức năng 4 (Đăng ký, đăng nhập, đăng xuất, phân quyền ở dang-ky.php, dang-nhap.php, inc/xac-thuc.php).</p>

            <h3>Danh sách kỹ năng chuyên môn từ CSDL</h3>
            <form method="get" action="gioithieu.php#ky-nang" class="form-loc-thu" id="ky-nang">
                <label for="loc-nhom">Lọc theo nhóm:</label>
                <select id="loc-nhom" name="nhom">
                    <option value="">Tất cả nhóm kỹ năng</option>
<?php foreach ($nhomKyNang as $khoa => $nhan): ?>
                    <option value="<?= e($khoa) ?>"<?= $khoa === $nhomLoc ? ' selected' : '' ?>><?= e($nhan) ?></option>
<?php endforeach; ?>
                </select>
                <button type="submit">Lọc</button>
            </form>
            <ul>
<?php foreach ($kyNangHienThi as $k): ?>
                <li>
                    <strong><?= e((string) $k['ten']) ?></strong>
                    (<?= e($nhomKyNang[$k['nhom']] ?? (string) $k['nhom']) ?> — Mức độ: <strong><?= (int) $k['muc_do'] ?>%</strong>)
                </li>
<?php endforeach; ?>
            </ul>
        </section>

        <section id="thoi-khoa-bieu">
            <h2>2. Thời khóa biểu tuần</h2>
            <div class="khung-thoi-khoa-bieu">
                <table>
                    <caption>Thời khóa biểu các học phần trong tuần</caption>
                    <thead>
                        <tr>
                            <th scope="col">Ca / Thứ</th>
                            <th scope="col">Thứ 2</th>
                            <th scope="col">Thứ 3</th>
                            <th scope="col">Thứ 4</th>
                            <th scope="col">Thứ 5</th>
                            <th scope="col">Thứ 6</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th scope="row">Sáng</th>
                            <td>Tự học</td>
                            <td>Phân tích thiết kế hệ thống</td>
                            <td>Phương pháp dạy học Tin học</td>
                            <td>Lịch sử Đảng</td>
                            <td>HQT CSDL</td>
                        </tr>
                        <tr>
                            <th scope="row">Chiều</th>
                            <td>Tự học</td>
                            <td>Thực hành Web &amp; MySQL</td>
                            <td>Tự học</td>
                            <td>Cơ sở dữ liệu</td>
                            <td>Đi dạy thêm</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section id="viec">
            <h2>3. Việc cần làm lưu trong CSDL </h2>
<?php if ($loiViec !== ''): ?>
            <p class="thong-bao loi" role="alert"><?= e($loiViec) ?></p>
<?php endif; ?>
            <form method="post" action="gioithieu.php#viec" class="form-loc-thu">
                <input type="hidden" name="hanhdong" value="them">
                <label for="viec-nd">Việc mới (3–150 ký tự):</label>
                <input id="viec-nd" name="noidung" type="text" maxlength="150" required>
                <button type="submit">Thêm việc</button>
            </form>
<?php if (!$viec): ?>
            <p>Chưa có việc nào.</p>
<?php else: ?>
            <ul>
<?php foreach ($viec as $v): $xong = (bool) $v['da_xong']; ?>
                <li>
                    <span<?= $xong ? ' style="text-decoration: line-through"' : '' ?>><?= e((string) $v['noi_dung']) ?></span>
                    <small>(<?= e((string) $v['ngay_tao']) ?>)</small>
                    <form method="post" action="gioithieu.php#viec" style="display:inline">
                        <input type="hidden" name="id" value="<?= (int) $v['id'] ?>">
                        <button type="submit" name="hanhdong" value="xong"><?= $xong ? 'Bỏ đánh dấu' : 'Đã xong' ?></button>
                        <button type="submit" name="hanhdong" value="xoa">Xóa</button>
                    </form>
                </li>
<?php endforeach; ?>
            </ul>
<?php endif; ?>
        </section>
    </main>
<?php require __DIR__ . '/../../inc/footer.php'; ?>

<?php
/**
 * thanhvien/3120224050_hanh/gioithieu.php - Trang cá nhân của Nguyễn Thị Bích Hạnh (Phần B - MySQL).
 * Chức năng CSDL 1: Sổ lưu bút lưu trong bảng tv_luu_but có phân trang (5 lời nhắn/trang),
 * kiểm tra dữ liệu + PRG, chỉ chủ trang (hanh@ued.vn) mới thấy và thực hiện được nút Xóa (gợi ý 5 Phụ lục B).
 * Chức năng CSDL 2: Bộ đếm lượt xem theo ngày trong bảng tv_luot_xem (INSERT ... ON DUPLICATE KEY UPDATE)
 * kèm bảng thống kê 7 ngày gần nhất.
 * Cách thử: gửi lời nhắn trống (báo lỗi); gửi hợp lệ rồi F5; sang trang 2; đăng nhập hanh@ued.vn để xóa.
 */
require __DIR__ . '/../../inc/config.php';

const MSSV = '3120224050';
$goc = '../../';

$stTv = db()->prepare('SELECT ho_ten, nguoi_dung_id FROM thanh_vien WHERE mssv = ?');
$stTv->execute([MSSV]);
$tv = $stTv->fetch() ?: ['ho_ten' => 'Nguyễn Thị Bích Hạnh', 'nguoi_dung_id' => null];
$laChuTrang = ((int) (nguoiDung()['id'] ?? 0)) !== 0 && ((int) (nguoiDung()['id'] ?? 0)) === (int) ($tv['nguoi_dung_id'] ?? -1);

// Chức năng CSDL 2: Bộ đếm lượt xem theo ngày (INSERT ... ON DUPLICATE KEY UPDATE, mỗi phiên tăng 1 lần)
if (empty($_SESSION['da_dem_xem_hanh'])) {
    db()->prepare(
        'INSERT INTO tv_luot_xem (mssv, ngay, so_luot) VALUES (?, CURDATE(), 1)
         ON DUPLICATE KEY UPDATE so_luot = so_luot + 1'
    )->execute([MSSV]);
    $_SESSION['da_dem_xem_hanh'] = true;
}

$stTongXem = db()->prepare('SELECT COALESCE(SUM(so_luot), 0) FROM tv_luot_xem WHERE mssv = ?');
$stTongXem->execute([MSSV]);
$tongLuotXem = (int) $stTongXem->fetchColumn();

$stBayNgay = db()->prepare(
    'SELECT ngay, so_luot FROM tv_luot_xem
     WHERE mssv = ? ORDER BY ngay DESC LIMIT 7'
);
$stBayNgay->execute([MSSV]);
$dsBayNgay = $stBayNgay->fetchAll();

// Chức năng CSDL 1: Sổ lưu bút có phân trang và phân quyền chủ trang xóa lời nhắn (gợi ý 5)
$du  = ['ten' => '', 'loi_nhan' => ''];
$loi = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $xoa = filter_var($_POST['xoa'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($xoa !== false) {
        if ($laChuTrang) {
            db()->prepare('DELETE FROM tv_luu_but WHERE id = ? AND mssv = ?')
                ->execute([(int) $xoa, MSSV]);
            datFlash('ok', 'Đã xóa lời nhắn khỏi sổ lưu bút.');
        } else {
            datFlash('loi', 'Chỉ chủ trang mới có quyền xóa lời nhắn.');
        }
        header('Location: gioithieu.php#luu-but');
        exit;
    }

    $du['ten']      = layChuoi($_POST, 'ten');
    $du['loi_nhan'] = layChuoi($_POST, 'loi_nhan');
    if ($du['loi_nhan'] === '') {
        $du['loi_nhan'] = layChuoi($_POST, 'loinhan');
    }

    if (mb_strlen($du['ten']) < 2 || mb_strlen($du['ten']) > 50) {
        $loi['ten'] = 'Tên cần từ 2 đến 50 ký tự.';
    }
    if (mb_strlen($du['loi_nhan']) < 5 || mb_strlen($du['loi_nhan']) > 500) {
        $loi['loi_nhan'] = 'Lời nhắn cần từ 5 đến 500 ký tự.';
    }

    if (!$loi) {
        db()->prepare(
            'INSERT INTO tv_luu_but (mssv, ten_khach, loi_nhan) VALUES (?, ?, ?)'
        )->execute([MSSV, $du['ten'], $du['loi_nhan']]);
        datFlash('ok', 'Cảm ơn bạn đã ghi lưu bút!');
        header('Location: gioithieu.php#luu-but');
        exit;
    }
}

$stDemLb = db()->prepare('SELECT COUNT(*) FROM tv_luu_but WHERE mssv = ?');
$stDemLb->execute([MSSV]);
$tongLb  = (int) $stDemLb->fetchColumn();
$soTrang = max(1, (int) ceil($tongLb / 5));
$hienTai = filter_var($_GET['trang'] ?? 1, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1, 'default' => 1],
]);
$hienTai = min((int) $hienTai, $soTrang);

$stLb = db()->prepare(
    'SELECT id, ten_khach, loi_nhan, thoi_gian
     FROM tv_luu_but WHERE mssv = ?
     ORDER BY thoi_gian DESC, id DESC
     LIMIT 5 OFFSET ?'
);
$stLb->execute([MSSV, ($hienTai - 1) * 5]);
$dsLoiNhan = $stLb->fetchAll();

$tieuDe    = 'Hồ sơ cá nhân | Nguyễn Thị Bích Hạnh';
$trang     = 'gioi-thieu';
$moTa      = 'Trang giới thiệu cá nhân, sổ lưu bút có phân trang và bộ đếm lượt xem theo ngày của Nguyễn Thị Bích Hạnh.';
$bodyClass = '';
$cssThem   = ['css/style-hanh.css'];
$jsThem    = ['js/canhan.js'];
require __DIR__ . '/../../inc/header.php';
?>
    <main id="noi-dung">
<?php require __DIR__ . '/../../inc/thong-bao.php'; ?>
        <h1>Hồ sơ sinh viên: <?= e((string) $tv['ho_ten']) ?></h1>
        <section>
            <h2>1. Thông tin bản thân</h2>
            <figure>
                <img alt="Ảnh thẻ chân dung của sinh viên Nguyễn Thị Bích Hạnh" src="chan-dung-hanh.jpg" width="200">
                <figcaption>Ảnh 1: Chân dung sinh viên</figcaption>
            </figure>
            <p><strong>Mã sinh viên:</strong> <?= e(MSSV) ?></p>
            <p><strong>Lớp:</strong> 24CNTT2 — Khoa Toán - Tin, Trường Đại học Sư phạm - ĐHĐN</p>
            <p><strong>Vai trò trong nhóm:</strong> Thành viên, phụ trách Chức năng 2 (tìm kiếm, lọc, sắp xếp, phân trang ở <code>danh-sach.php</code>) và kiểm thử 14 ca Bảng 3.</p>
        </section>

        <article>
            <h2>2. Định hướng chuyên môn và kỹ năng</h2>
            <p>Em định hướng phát triển theo lĩnh vực kỹ thuật phần mềm, yêu thích tìm hiểu kiến trúc mạng máy tính và xây dựng ứng dụng web với PHP &amp; MySQL.</p>
            <ul>
                <li>Ngôn ngữ lập trình: HTML5, CSS3, PHP 8, Python, C++</li>
                <li>Hệ quản trị cơ sở dữ liệu: MySQL / MariaDB, PostgreSQL, SQL Server</li>
                <li>Công cụ làm việc: Visual Studio Code, Git/GitHub, curl, REST Client</li>
            </ul>
        </article>

        <section id="luot-xem">
            <h2>3. Thống kê lượt xem theo ngày (bảng <code>tv_luot_xem</code>)</h2>
            <p>Tổng lượt xem tích lũy: <strong><?= $tongLuotXem ?></strong> lượt (mỗi phiên truy cập tính 1 lần bằng <code>INSERT ... ON DUPLICATE KEY UPDATE</code>).</p>
            <div class="khung-thoi-khoa-bieu">
                <table>
                    <caption>Bảng 1: Thống kê lượt xem 7 ngày gần nhất</caption>
                    <thead>
                        <tr>
                            <th scope="col">Ngày</th>
                            <th scope="col">Số lượt xem</th>
                        </tr>
                    </thead>
                    <tbody>
<?php foreach ($dsBayNgay as $ng): ?>
                        <tr>
                            <th scope="row"><?= e((string) $ng['ngay']) ?></th>
                            <td><strong><?= (int) $ng['so_luot'] ?></strong> lượt</td>
                        </tr>
<?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section id="luu-but">
            <h2>4. Sổ lưu bút có phân trang (bảng <code>tv_luu_but</code> — <?= $tongLb ?> lời nhắn)</h2>
<?php if ($loi): ?>
            <p class="thong-bao loi" role="alert">Vui lòng kiểm tra các ô báo lỗi bên dưới.</p>
<?php endif; ?>
            <form method="post" action="gioithieu.php#luu-but" class="form-tham-dinh">
                <p>
                    <label for="lb-ten">Tên của bạn (2–50 ký tự) (*)</label>
                    <input id="lb-ten" name="ten" type="text" maxlength="50" required value="<?= e($du['ten']) ?>">
                    <small class="loi-truong"><?= e($loi['ten'] ?? '') ?></small>
                </p>
                <p>
                    <label for="lb-loinhan">Lời nhắn (5–500 ký tự) (*)</label>
                    <textarea id="lb-loinhan" name="loi_nhan" rows="3" maxlength="500" required><?= e($du['loi_nhan']) ?></textarea>
                    <small class="loi-truong"><?= e($loi['loi_nhan'] ?? '') ?></small>
                </p>
                <button type="submit">Gửi lời nhắn</button>
            </form>

            <h3>Danh sách lời nhắn (trang <?= $hienTai ?>/<?= $soTrang ?>)</h3>
<?php if (!$dsLoiNhan): ?>
            <p>Chưa có lời nhắn nào.</p>
<?php else: ?>
            <ul>
<?php foreach ($dsLoiNhan as $lb): ?>
                <li>
                    <strong><?= e((string) $lb['ten_khach']) ?></strong>
                    <small>(<?= e((string) $lb['thoi_gian']) ?>)</small><br>
                    <?= nl2br(e((string) $lb['loi_nhan'])) ?>
<?php if ($laChuTrang): ?>
                    <form method="post" action="gioithieu.php#luu-but" style="display:inline">
                        <input type="hidden" name="xoa" value="<?= (int) $lb['id'] ?>">
                        <button type="submit">Xóa</button>
                    </form>
<?php endif; ?>
                </li>
<?php endforeach; ?>
            </ul>

<?php if ($soTrang > 1): ?>
            <nav class="phan-trang" aria-label="Phân trang sổ lưu bút">
<?php for ($i = 1; $i <= $soTrang; $i++): ?>
                <a href="?trang=<?= $i ?>#luu-but"<?= $i === $hienTai ? ' aria-current="page"' : '' ?>><?= $i ?></a>
<?php endfor; ?>
            </nav>
<?php endif; ?>
<?php endif; ?>
        </section>
    </main>
<?php require __DIR__ . '/../../inc/footer.php'; ?>

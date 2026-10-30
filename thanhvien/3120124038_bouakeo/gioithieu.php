<?php
/**
 * thanhvien/3120124038_bouakeo/gioithieu.php - Trang cá nhân của Ladisa Bouakeo (Phần B - MySQL).
 * Chức năng CSDL 1: Đánh giá hồ sơ 1–5 sao lưu trong bảng tv_danh_gia (thay cho tệp JSONL ở Bài tập 5),
 * mỗi phiên đánh giá một lần, chủ trang (bouakeo@ued.vn) có quyền xóa đánh giá.
 * Chức năng CSDL 2: Thống kê tỉ lệ phần trăm và phân bố từng mức sao (1..5 sao) bằng câu SQL
 * GROUP BY so_sao + COUNT(*), AVG(so_sao). Giữ chức năng nhớ tên khách bằng cookie bouakeo_khach.
 * Cách thử: gửi đánh giá 5 sao rồi F5; xem bảng thống kê tỉ lệ theo từng mức sao.
 */
require __DIR__ . '/../../inc/config.php';

const MSSV = '3120124038';
$goc = '../../';

$stTv = db()->prepare('SELECT ho_ten, nguoi_dung_id FROM thanh_vien WHERE mssv = ?');
$stTv->execute([MSSV]);
$tv = $stTv->fetch() ?: ['ho_ten' => 'Ladisa Bouakeo', 'nguoi_dung_id' => null];
$laChuTrang = ((int) (nguoiDung()['id'] ?? 0)) !== 0 && ((int) (nguoiDung()['id'] ?? 0)) === (int) ($tv['nguoi_dung_id'] ?? -1);

$tenKhach = $_COOKIE['bouakeo_khach'] ?? '';
if (!is_string($tenKhach) || mb_strlen($tenKhach) < 2 || mb_strlen($tenKhach) > 30
    || preg_match('/^[\p{L} .\'-]+$/u', $tenKhach) !== 1) {
    $tenKhach = '';
}

$loiTen = '';
$loiDg  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hd = layChuoi($_POST, 'hanhdong');
    if ($hd === 'luuten') {
        $ten = layChuoi($_POST, 'ten');
        if (mb_strlen($ten) < 2 || mb_strlen($ten) > 30 || preg_match('/^[\p{L} .\'-]+$/u', $ten) !== 1) {
            $loiTen = 'Tên cần từ 2 đến 30 ký tự, chỉ gồm chữ cái, khoảng trắng, dấu chấm, gạch nối.';
        } else {
            setcookie('bouakeo_khach', $ten, ['expires' => time() + 30 * 24 * 3600, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
            datFlash('ok', 'Đã ghi nhớ tên của bạn.');
        }
    } elseif ($hd === 'quenten') {
        setcookie('bouakeo_khach', '', ['expires' => time() - 3600, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
        datFlash('ok', 'Đã xóa tên đã ghi nhớ.');
    } elseif ($hd === 'xoa_dg') {
        $idXoa = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($laChuTrang && $idXoa !== false) {
            db()->prepare('DELETE FROM tv_danh_gia WHERE id = ? AND mssv = ?')
                ->execute([(int) $idXoa, MSSV]);
            datFlash('ok', 'Đã xóa đánh giá.');
        }
    } elseif ($hd === 'danhgia') {
        $diemSao = filter_var($_POST['diem'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]);
        $nguoiGui = layChuoi($_POST, 'ten_khach') ?: ($tenKhach !== '' ? $tenKhach : 'Khách ghé thăm');
        $nhanXet  = layChuoi($_POST, 'nhanxet');

        if (!empty($_SESSION['da_danh_gia_bouakeo'])) {
            $loiDg = 'Bạn đã gửi đánh giá trong phiên này rồi.';
        } elseif ($diemSao === false) {
            $loiDg = 'Điểm đánh giá phải là số nguyên từ 1 đến 5.';
        } elseif (mb_strlen($nguoiGui) < 2 || mb_strlen($nguoiGui) > 50) {
            $loiDg = 'Tên người đánh giá cần từ 2 đến 50 ký tự.';
        } elseif (mb_strlen($nhanXet) > 300) {
            $loiDg = 'Nhận xét tối đa 300 ký tự.';
        } else {
            db()->prepare(
                'INSERT INTO tv_danh_gia (mssv, ten_khach, so_sao, nhan_xet) VALUES (?, ?, ?, ?)'
            )->execute([MSSV, $nguoiGui, (int) $diemSao, $nhanXet]);
            $_SESSION['da_danh_gia_bouakeo'] = true;
            datFlash('ok', 'Cảm ơn bạn đã gửi đánh giá vào CSDL!');
        }
    } else {
        $loiDg = 'Thao tác không hợp lệ.';
    }

    if ($loiTen === '' && $loiDg === '') {
        header('Location: gioithieu.php#danh-gia');
        exit;
    }
}

// Chức năng CSDL 2: Tổng hợp điểm trung bình và thống kê phân bố sao bằng GROUP BY
$stTk = db()->prepare('SELECT COUNT(*) AS tong, COALESCE(AVG(so_sao), 0) AS tb FROM tv_danh_gia WHERE mssv = ?');
$stTk->execute([MSSV]);
$tkChung = $stTk->fetch() ?: ['tong' => 0, 'tb' => 0];
$soDg = (int) $tkChung['tong'];
$tbDg = (float) $tkChung['tb'];

$stPhanBo = db()->prepare(
    'SELECT so_sao, COUNT(*) AS so_luot
     FROM tv_danh_gia WHERE mssv = ?
     GROUP BY so_sao ORDER BY so_sao DESC'
);
$stPhanBo->execute([MSSV]);
$phanBo = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
foreach ($stPhanBo->fetchAll() as $dong) {
    $phanBo[(int) $dong['so_sao']] = (int) $dong['so_luot'];
}

// Chức năng CSDL 1: Danh sách các đánh giá mới nhất
$stDsDg = db()->prepare(
    'SELECT id, ten_khach, so_sao, nhan_xet, thoi_gian
     FROM tv_danh_gia WHERE mssv = ?
     ORDER BY thoi_gian DESC, id DESC LIMIT 10'
);
$stDsDg->execute([MSSV]);
$dsDanhGia = $stDsDg->fetchAll();

$tieuDe    = 'Hồ sơ cá nhân | Ladisa Bouakeo';
$trang     = 'gioi-thieu';
$moTa      = 'Trang giới thiệu cá nhân và hệ thống đánh giá hồ sơ (MySQL) của sinh viên Ladisa Bouakeo.';
$bodyClass = '';
$cssThem   = ['css/style-bouakeo.css'];
$jsThem    = ['js/canhan.js'];
require __DIR__ . '/../../inc/header.php';
?>
    <main id="noi-dung">
<?php require __DIR__ . '/../../inc/thong-bao.php'; ?>
        <h1>Hồ sơ sinh viên: <?= e((string) $tv['ho_ten']) ?></h1>
        <section>
            <h2>1. Thông tin bản thân</h2>
            <figure>
                <img alt="Ảnh thẻ chân dung của sinh viên Ladisa Bouakeo" src="chan-dung-bouakeo.jpg" width="200">
                <figcaption>Ảnh 1: Chân dung sinh viên</figcaption>
            </figure>
            <p><strong>Mã sinh viên:</strong> <?= e(MSSV) ?></p>
            <p><strong>Lớp:</strong> Sư phạm Tin học (SPT) — Khoa Toán - Tin, Trường Đại học Sư phạm - ĐHĐN</p>
            <p><strong>Vai trò trong nhóm:</strong> Thành viên, phụ trách Chức năng 5 (Đặt hàng trong giao dịch <code>dat-hang.php</code>, <code>inc/don-hang.php</code> và xem đơn hàng <code>don-hang.php</code>).</p>
        </section>

        <article>
            <h2>2. Định hướng chuyên môn và kỹ năng</h2>
            <p>Em định hướng phát triển trong lĩnh vực công nghệ thông tin và giáo dục sáng tạo, yêu thích lập trình game, ứng dụng AI và thiết kế cơ sở dữ liệu.</p>
            <ul>
                <li>Lập trình &amp; Phát triển Web/Game: PHP 8 (PDO), C++, Python, HTML5, CSS3</li>
                <li>Hệ quản trị cơ sở dữ liệu: MySQL / MariaDB, PostgreSQL, SQL Server</li>
                <li>Công cụ làm việc: Visual Studio Code, Git/GitHub</li>
            </ul>
        </article>

        <section id="danh-gia">
            <h2>3. Lời chào và Đánh giá hồ sơ lưu trong CSDL (bảng <code>tv_danh_gia</code>)</h2>
<?php if ($tenKhach !== ''): ?>
            <p>Chào mừng quay lại, <strong><?= e($tenKhach) ?></strong>!</p>
            <form method="post" action="gioithieu.php#danh-gia">
                <input type="hidden" name="hanhdong" value="quenten">
                <button type="submit">Quên tên của tôi</button>
            </form>
<?php else: ?>
            <form method="post" action="gioithieu.php#danh-gia" class="form-loc-thu">
                <input type="hidden" name="hanhdong" value="luuten">
                <label for="ten-khach">Tên của bạn (để lần sau chào lại):</label>
                <input id="ten-khach" name="ten" type="text" maxlength="30" required>
                <button type="submit">Ghi nhớ tên</button>
                <small class="loi-truong"><?= e($loiTen) ?></small>
            </form>
<?php endif; ?>

            <h3>Thống kê phân bố đánh giá bằng <code>GROUP BY</code></h3>
            <p>Điểm trung bình: <strong><?= $soDg > 0 ? e(number_format($tbDg, 2, ',', '.')) . ' / 5,00' : 'chưa có' ?></strong> (<?= $soDg ?> lượt đánh giá).</p>
            <div class="khung-thoi-khoa-bieu">
                <table>
                    <caption>Bảng 1: Tỉ lệ đánh giá theo từng mức sao từ bảng <code>tv_danh_gia</code></caption>
                    <thead>
                        <tr>
                            <th scope="col">Mức đánh giá</th>
                            <th scope="col">Số lượt</th>
                            <th scope="col">Tỉ lệ (%)</th>
                        </tr>
                    </thead>
                    <tbody>
<?php foreach ($phanBo as $sao => $luot):
    $tiLe = $soDg > 0 ? round(($luot * 100) / $soDg, 1) : 0.0;
?>
                        <tr>
                            <th scope="row"><?= $sao ?> sao (<?= str_repeat('★', $sao) ?>)</th>
                            <td><?= $luot ?> lượt</td>
                            <td><?= e(number_format($tiLe, 1, ',', '.')) ?>%</td>
                        </tr>
<?php endforeach; ?>
                    </tbody>
                </table>
            </div>

<?php if ($loiDg !== ''): ?>
            <p class="thong-bao loi" role="alert"><?= e($loiDg) ?></p>
<?php endif; ?>
            <h3>Gửi đánh giá mới</h3>
            <form method="post" action="gioithieu.php#danh-gia" class="form-tham-dinh">
                <input type="hidden" name="hanhdong" value="danhgia">
                <p>
                    <label for="dg-ten">Tên của bạn (*)</label>
                    <input id="dg-ten" name="ten_khach" type="text" maxlength="50" required value="<?= e($tenKhach) ?>">
                </p>
                <p>
                    <label for="dg-diem">Số sao (1–5) (*)</label>
                    <select id="dg-diem" name="diem">
<?php for ($i = 5; $i >= 1; $i--): ?>
                        <option value="<?= $i ?>"><?= $i ?> sao</option>
<?php endfor; ?>
                    </select>
                </p>
                <p>
                    <label for="dg-nhanxet">Nhận xét (tối đa 300 ký tự)</label>
                    <input id="dg-nhanxet" name="nhanxet" type="text" maxlength="300">
                </p>
                <button type="submit">Gửi đánh giá</button>
            </form>

            <h3>Các nhận xét gần đây</h3>
            <ul>
<?php foreach ($dsDanhGia as $dg): ?>
                <li>
                    <strong><?= e((string) $dg['ten_khach']) ?></strong> — <strong><?= (int) $dg['so_sao'] ?> ★</strong>
                    <small>(<?= e((string) $dg['thoi_gian']) ?>)</small>
                    <?= ($dg['nhan_xet'] ?? '') !== '' ? ': ' . e((string) $dg['nhan_xet']) : '' ?>
<?php if ($laChuTrang): ?>
                    <form method="post" action="gioithieu.php#danh-gia" style="display:inline">
                        <input type="hidden" name="hanhdong" value="xoa_dg">
                        <input type="hidden" name="id" value="<?= (int) $dg['id'] ?>">
                        <button type="submit">Xóa</button>
                    </form>
<?php endif; ?>
                </li>
<?php endforeach; ?>
            </ul>
        </section>
    </main>
<?php require __DIR__ . '/../../inc/footer.php'; ?>

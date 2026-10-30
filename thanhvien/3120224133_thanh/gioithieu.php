<?php
/**
 * thanhvien/3120224133_thanh/gioithieu.php - Trang cá nhân của Trần Thị Cẩm Thanh (Phần B - MySQL).
 * Chức năng CSDL 1: Bảng điểm học phần lưu trong bảng tv_diem_hoc_phan (tính điểm 0,2*A1 + 0,3*A2 + 0,5*A3
 * và điểm trung bình tích lũy GPA; chủ trang thanh@ued.vn đăng nhập mới thêm/xóa được học phần, PRG).
 * Chức năng CSDL 2: Thống kê số sản phẩm, tổng tồn kho và doanh thu đã đặt theo từng danh mục ngọc từ
 * dữ liệu đồ án (LEFT JOIN danh_muc + san_pham + chi_tiet_don + GROUP BY). Giữ giao diện sáng/tối bằng cookie.
 * Cách thử: mở trang, đăng nhập thanh@ued.vn (matkhau123) để thêm/xóa môn học trong bảng điểm.
 */
require __DIR__ . '/../../inc/config.php';

const MSSV = '3120224133';
$goc = '../../';

$stTv = db()->prepare('SELECT ho_ten, nguoi_dung_id, anh_dai_dien FROM thanh_vien WHERE mssv = ?');
$stTv->execute([MSSV]);
$tv = $stTv->fetch() ?: ['ho_ten' => 'Trần Thị Cẩm Thanh', 'nguoi_dung_id' => null, 'anh_dai_dien' => 'chan-dung-thanh.jpg'];
$laChuTrang = ((int) (nguoiDung()['id'] ?? 0)) !== 0 && ((int) (nguoiDung()['id'] ?? 0)) === (int) ($tv['nguoi_dung_id'] ?? -1);

// Giao diện sáng/tối bằng cookie (kế thừa từ Bài tập 5)
$giaoDien = $_COOKIE['thanh_giaodien'] ?? 'sang';
if (!is_string($giaoDien) || !in_array($giaoDien, ['sang', 'toi'], true)) {
    $giaoDien = 'sang';
}

$du = ['ten_mon' => '', 'so_tin' => '3', 'a1' => '', 'a2' => '', 'a3' => ''];
$loiDiem = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['giaodien'])) {
        $moi = layChuoi($_POST, 'giaodien');
        if (in_array($moi, ['sang', 'toi'], true)) {
            setcookie('thanh_giaodien', $moi, [
                'expires' => time() + 365 * 24 * 3600, 'path' => '/',
                'httponly' => true, 'samesite' => 'Lax',
            ]);
            datFlash('ok', 'Đã đổi giao diện sang chế độ ' . ($moi === 'toi' ? 'tối' : 'sáng') . '.');
        }
        header('Location: gioithieu.php');
        exit;
    }

    $xoaId = filter_var($_POST['xoa_mon'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($xoaId !== false) {
        if ($laChuTrang) {
            db()->prepare('DELETE FROM tv_diem_hoc_phan WHERE id = ? AND mssv = ?')
                ->execute([(int) $xoaId, MSSV]);
            datFlash('ok', 'Đã xóa học phần khỏi bảng điểm.');
        } else {
            datFlash('loi', 'Chỉ chủ trang mới được xóa học phần.');
        }
        header('Location: gioithieu.php#tinh-diem');
        exit;
    }

    if (!$laChuTrang) {
        datFlash('loi', 'Bạn cần đăng nhập tài khoản chủ trang (thanh@ued.vn) để lưu điểm học phần.');
        header('Location: gioithieu.php#tinh-diem');
        exit;
    }

    $du['ten_mon'] = layChuoi($_POST, 'ten_mon');
    $du['so_tin']  = layChuoi($_POST, 'so_tin');
    $soTin = filter_var($du['so_tin'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 10]]);
    if (mb_strlen($du['ten_mon']) < 3 || mb_strlen($du['ten_mon']) > 120) {
        $loiDiem['ten_mon'] = 'Tên học phần cần từ 3 đến 120 ký tự.';
    }
    if ($soTin === false) {
        $loiDiem['so_tin'] = 'Số tín chỉ phải là số nguyên từ 1 đến 10.';
    }

    $so = [];
    foreach (['a1', 'a2', 'a3'] as $k) {
        $v = layChuoi($_POST, $k);
        $du[$k] = $v;
        if ($v === '' || preg_match('/^[0-9]{1,2}([.,][0-9]{1,2})?\z/', $v) !== 1) {
            $loiDiem[$k] = 'Điểm phải là số hợp lệ từ 0 đến 10 (ví dụ: 8.5).';
        } else {
            $n = (float) str_replace(',', '.', $v);
            if ($n < 0 || $n > 10) {
                $loiDiem[$k] = 'Điểm phải nằm trong khoảng 0 đến 10.';
            } else {
                $so[$k] = round($n, 2);
            }
        }
    }

    if (!$loiDiem) {
        $st = db()->prepare(
            'INSERT INTO tv_diem_hoc_phan (mssv, ten_mon, so_tin, diem_a1, diem_a2, diem_a3)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $st->execute([MSSV, $du['ten_mon'], (int) $soTin, $so['a1'], $so['a2'], $so['a3']]);
        datFlash('ok', 'Đã lưu điểm học phần “' . $du['ten_mon'] . '” vào CSDL.');
        header('Location: gioithieu.php#tinh-diem');
        exit;
    }
}

// Chức năng CSDL 1: Đọc danh sách điểm học phần từ bảng tv_diem_hoc_phan
$stDiem = db()->prepare(
    'SELECT id, ten_mon, so_tin, diem_a1, diem_a2, diem_a3,
            ROUND(0.2 * diem_a1 + 0.3 * diem_a2 + 0.5 * diem_a3, 2) AS diem_hp
     FROM tv_diem_hoc_phan
     WHERE mssv = ?
     ORDER BY id DESC'
);
$stDiem->execute([MSSV]);
$dsDiem = $stDiem->fetchAll();

$tongTin = 0;
$tongTichLuy = 0.0;
foreach ($dsDiem as $d) {
    $tc = (int) $d['so_tin'];
    $tongTin += $tc;
    $tongTichLuy += $tc * (float) $d['diem_hp'];
}
$gpa = $tongTin > 0 ? round($tongTichLuy / $tongTin, 2) : 0.0;

// Chức năng CSDL 2: Thống kê dữ liệu đồ án theo danh mục bằng LEFT JOIN + GROUP BY + SUM
$thongKeDm = db()->query(
    'SELECT dm.id, dm.ten,
            COUNT(DISTINCT sp.id) AS so_sp,
            COALESCE(SUM(DISTINCT sp.ton_kho), 0) AS tong_ton,
            COALESCE(SUM(ctd.so_luong * ctd.don_gia), 0) AS doanh_thu
     FROM danh_muc dm
     LEFT JOIN san_pham sp ON sp.danh_muc_id = dm.id
     LEFT JOIN chi_tiet_don ctd ON ctd.san_pham_id = sp.id
     GROUP BY dm.id, dm.ten
     ORDER BY doanh_thu DESC, so_sp DESC'
)->fetchAll();

$tieuDe    = 'Hồ sơ cá nhân | Trần Thị Cẩm Thanh';
$trang     = 'gioi-thieu';
$moTa      = 'Trang giới thiệu cá nhân, bảng điểm học phần (MySQL) và thống kê sàn JadeHub của sinh viên Trần Thị Cẩm Thanh.';
$bodyClass = $giaoDien === 'toi' ? 'che-do-toi' : '';
$cssThem   = ['css/style-thanh.css'];
$jsThem    = ['js/canhan.js'];
require __DIR__ . '/../../inc/header.php';
?>
    <main id="noi-dung">
<?php require __DIR__ . '/../../inc/thong-bao.php'; ?>
        <h1>Hồ sơ sinh viên: <?= e((string) $tv['ho_ten']) ?></h1>
        <section>
            <h2>1. Thông tin bản thân</h2>
            <figure>
                <img alt="Ảnh thẻ chân dung của sinh viên Trần Thị Cẩm Thanh" src="chan-dung-thanh.jpg" width="200">
                <figcaption>Ảnh 1: Chân dung sinh viên</figcaption>
            </figure>
            <p><strong>Mã sinh viên:</strong> <?= e(MSSV) ?></p>
            <p><strong>Email liên hệ:</strong> camthanh0975@gmail.com</p>
            <p><strong>Lớp:</strong> 24CNTT2 — Khoa Toán - Tin, Trường Đại học Sư phạm - ĐHĐN</p>
            <p><strong>Vai trò trong nhóm:</strong> Nhóm trưởng, phụ trách kiến trúc CSDL,  quản lý phiên bản Git.</p>
        </section>

        <section id="giao-dien">
            <h2>2. Giao diện sáng / tối (cookie)</h2>
            <p>Đang dùng chế độ: <strong><?= $giaoDien === 'toi' ? 'tối' : 'sáng' ?></strong>.</p>
            <form method="post" action="gioithieu.php">
                <button type="submit" name="giaodien" value="sang">Giao diện sáng</button>
                <button type="submit" name="giaodien" value="toi">Giao diện tối</button>
            </form>
        </section>

        <section id="tinh-diem">
            <h2>3. Sổ điểm học phần lưu trong CSDL </h2>
            <p>
                Công thức: 0,2 × A1 + 0,3 × A2 + 0,5 × A3.
            </p>
            <p>
                Tổng số tín chỉ: <strong><?= $tongTin ?></strong> ·
            </p>
            <p>
                Điểm trung bình tích lũy (GPA hệ 10): <strong><?= e(number_format($gpa, 2, ',', '.')) ?></strong>.
            </p>

            <div class="khung-thoi-khoa-bieu">
                <table>
                    <caption>Bảng 1: Điểm các học phần đã lưu trong MySQL của <?= e((string) $tv['ho_ten']) ?></caption>
                    <thead>
                        <tr>
                            <th scope="col">Học phần</th>
                            <th scope="col">Tín chỉ</th>
                            <th scope="col">A1 (20%)</th>
                            <th scope="col">A2 (30%)</th>
                            <th scope="col">A3 (50%)</th>
                            <th scope="col">Tổng kết</th>
                            <th scope="col">Xếp loại</th>
<?php if ($laChuTrang): ?>
                            <th scope="col">Thao tác</th>
<?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
<?php foreach ($dsDiem as $d):
    $hp = (float) $d['diem_hp'];
    $xepLoai = $hp >= 8.5 ? 'Giỏi (A)' : ($hp >= 7.0 ? 'Khá (B)' : ($hp >= 5.5 ? 'Trung bình (C)' : ($hp >= 4.0 ? 'Đạt (D)' : 'Chưa đạt (F)')));
?>
                        <tr>
                            <th scope="row"><?= e((string) $d['ten_mon']) ?></th>
                            <td><?= (int) $d['so_tin'] ?></td>
                            <td><?= e(number_format((float) $d['diem_a1'], 2, ',', '.')) ?></td>
                            <td><?= e(number_format((float) $d['diem_a2'], 2, ',', '.')) ?></td>
                            <td><?= e(number_format((float) $d['diem_a3'], 2, ',', '.')) ?></td>
                            <td><strong><?= e(number_format($hp, 2, ',', '.')) ?></strong></td>
                            <td><?= e($xepLoai) ?></td>
<?php if ($laChuTrang): ?>
                            <td>
                                <form method="post" action="gioithieu.php#tinh-diem" style="display:inline">
                                    <input type="hidden" name="xoa_mon" value="<?= (int) $d['id'] ?>">
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
            <h3>Thêm học phần mới vào bảng điểm</h3>
            <form method="post" action="gioithieu.php#tinh-diem" class="form-tham-dinh">
                <p>
                    <label for="mon-ten">Tên học phần (*)</label>
                    <input id="mon-ten" name="ten_mon" type="text" maxlength="120" required value="<?= e($du['ten_mon']) ?>">
                    <small class="loi-truong"><?= e($loiDiem['ten_mon'] ?? '') ?></small>
                </p>
                <p>
                    <label for="mon-tc">Số tín chỉ (1–10) (*)</label>
                    <input id="mon-tc" name="so_tin" type="number" min="1" max="10" required value="<?= e($du['so_tin']) ?>">
                    <small class="loi-truong"><?= e($loiDiem['so_tin'] ?? '') ?></small>
                </p>
<?php foreach (['a1' => 'Điểm A1 (20%)', 'a2' => 'Điểm A2 (30%)', 'a3' => 'Điểm A3 (50%)'] as $k => $nhan): ?>
                <p>
                    <label for="diem-<?= $k ?>"><?= e($nhan) ?> (*)</label>
                    <input id="diem-<?= $k ?>" name="<?= $k ?>" type="text" inputmode="decimal" required value="<?= e($du[$k]) ?>">
                    <small class="loi-truong"><?= e($loiDiem[$k] ?? '') ?></small>
                </p>
<?php endforeach; ?>
                <button type="submit">Lưu vào bảng điểm</button>
            </form>
<?php else: ?>
            <p><small>Đăng nhập bằng tài khoản chủ trang (<code>thanh@ued.vn</code>) để thêm hoặc xóa học phần.</small></p>
<?php endif; ?>
        </section>

        <section id="thong-ke-san">
            <h2>4. Thống kê danh mục và doanh thu sàn JadeHub (JOIN + GROUP BY)</h2>
            <div class="khung-thoi-khoa-bieu">
                <table>
                    <caption>Bảng 2: Thống kê số lượng sản phẩm và doanh thu theo danh mục từ CSDL đồ án</caption>
                    <thead>
                        <tr>
                            <th scope="col">Danh mục ngọc</th>
                            <th scope="col">Số sản phẩm</th>
                            <th scope="col">Tổng tồn kho</th>
                            <th scope="col">Doanh thu đã đặt</th>
                        </tr>
                    </thead>
                    <tbody>
<?php foreach ($thongKeDm as $tk): ?>
                        <tr>
                            <th scope="row"><a href="../../danh-sach.php?dm=<?= (int) $tk['id'] ?>"><?= e((string) $tk['ten']) ?></a></th>
                            <td><?= (int) $tk['so_sp'] ?></td>
                            <td><?= (int) $tk['tong_ton'] ?></td>
                            <td><strong><?= e(vnd((int) $tk['doanh_thu'])) ?></strong></td>
                        </tr>
<?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
<?php require __DIR__ . '/../../inc/footer.php'; ?>

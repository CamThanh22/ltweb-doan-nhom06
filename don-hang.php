<?php
/**
 * don-hang.php - Xem danh sách đơn hàng kèm tổng tiền (JOIN + GROUP BY + SUM) và xem chi tiết một đơn (?id=).
 * Phân quyền theo vai trò và chủ sở hữu dữ liệu (slide 42):
 * - Khách hàng: chỉ xem danh sách và chi tiết các đơn CỦA MÌNH (WHERE dh.nguoi_dung_id = ?);
 *   nếu mở ?id= của khách khác -> trả 404 Not Found, không lộ nội dung đơn.
 * - Quản trị viên (quan_tri): xem mọi đơn hàng trên sàn và đổi trạng thái đơn (POST, danh sách cho phép).
 * Cách thử: đăng nhập lan@ued.vn mở don-hang.php?id=2 (đơn của An) -> nhận mã 404.
 */
require __DIR__ . '/inc/config.php';
require __DIR__ . '/inc/don-hang.php';

$nd       = yeuCauDangNhap();
$laAdmin  = ($nd['vai_tro'] ?? '') === 'quan_tri';
$uid      = (int) $nd['id'];

// Quản trị viên đổi trạng thái đơn hàng (chỉ nhận POST, trạng thái thuộc TRANG_THAI_DON)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$laAdmin) {
        http_response_code(403);
        require __DIR__ . '/403.php';
        exit;
    }
    $idDonCapNhat = filter_var($_POST['don_hang_id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $ttMoi        = layChuoi($_POST, 'trang_thai');
    if ($idDonCapNhat === false || !array_key_exists($ttMoi, TRANG_THAI_DON)) {
        datFlash('loi', 'Trạng thái hoặc mã đơn hàng không hợp lệ.');
    } else {
        $st = db()->prepare('UPDATE don_hang SET trang_thai = ? WHERE id = ?');
        $st->execute([$ttMoi, $idDonCapNhat]);
        datFlash('ok', "Đã cập nhật trạng thái đơn hàng #$idDonCapNhat thành “" . TRANG_THAI_DON[$ttMoi] . "”.");
    }
    $quayVe = $idDonCapNhat !== false && isset($_GET['id']) ? 'don-hang.php?id=' . (int) $idDonCapNhat : 'don-hang.php';
    header('Location: ' . $quayVe);
    exit;
}

// Nếu có ?id= -> xem chi tiết một đơn hàng (kiểm tra chủ sở hữu ngay trong câu SQL)
$donChiTiet = null;
$dsMonTrongDon = [];
if (isset($_GET['id'])) {
    $idDon = filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($idDon !== false) {
        if ($laAdmin) {
            $st = db()->prepare(
                'SELECT dh.*, nd.ho_ten, nd.email
                 FROM don_hang dh
                 JOIN nguoi_dung nd ON nd.id = dh.nguoi_dung_id
                 WHERE dh.id = ?'
            );
            $st->execute([$idDon]);
        } else {
            $st = db()->prepare(
                'SELECT dh.*, nd.ho_ten, nd.email
                 FROM don_hang dh
                 JOIN nguoi_dung nd ON nd.id = dh.nguoi_dung_id
                 WHERE dh.id = ? AND dh.nguoi_dung_id = ?'
            );
            $st->execute([$idDon, $uid]);
        }
        $donChiTiet = $st->fetch() ?: null;
    }

    if ($donChiTiet === null) {
        http_response_code(404);
        require __DIR__ . '/404.php';
        exit;
    }

    $stCt = db()->prepare(
        'SELECT ctd.san_pham_id, ctd.so_luong, ctd.don_gia,
                (ctd.so_luong * ctd.don_gia) AS thanh_tien,
                sp.ma, sp.ten, sp.anh
         FROM chi_tiet_don ctd
         JOIN san_pham sp ON sp.id = ctd.san_pham_id
         WHERE ctd.don_hang_id = ?
         ORDER BY ctd.san_pham_id ASC'
    );
    $stCt->execute([(int) $donChiTiet['id']]);
    $dsMonTrongDon = $stCt->fetchAll();
}

// Danh sách đơn hàng kèm tổng số món và tổng tiền bằng JOIN + GROUP BY + SUM
if ($laAdmin) {
    $dsDon = db()->query(
        'SELECT dh.id, dh.nguoi_dung_id, dh.trang_thai, dh.ghi_chu, dh.ngay_dat,
                nd.ho_ten, nd.email,
                COALESCE(SUM(ctd.so_luong), 0) AS tong_mon,
                COALESCE(SUM(ctd.so_luong * ctd.don_gia), 0) AS tong_tien
         FROM don_hang dh
         JOIN nguoi_dung nd ON nd.id = dh.nguoi_dung_id
         LEFT JOIN chi_tiet_don ctd ON ctd.don_hang_id = dh.id
         GROUP BY dh.id, dh.nguoi_dung_id, dh.trang_thai, dh.ghi_chu, dh.ngay_dat, nd.ho_ten, nd.email
         ORDER BY dh.ngay_dat DESC, dh.id DESC'
    )->fetchAll();
} else {
    $stDs = db()->prepare(
        'SELECT dh.id, dh.nguoi_dung_id, dh.trang_thai, dh.ghi_chu, dh.ngay_dat,
                COALESCE(SUM(ctd.so_luong), 0) AS tong_mon,
                COALESCE(SUM(ctd.so_luong * ctd.don_gia), 0) AS tong_tien
         FROM don_hang dh
         LEFT JOIN chi_tiet_don ctd ON ctd.don_hang_id = dh.id
         WHERE dh.nguoi_dung_id = ?
         GROUP BY dh.id, dh.nguoi_dung_id, dh.trang_thai, dh.ghi_chu, dh.ngay_dat
         ORDER BY dh.ngay_dat DESC, dh.id DESC'
    );
    $stDs->execute([$uid]);
    $dsDon = $stDs->fetchAll();
}

$tieuDe = $laAdmin ? 'Quản lý đơn hàng' : 'Đơn hàng của tôi';
$trang  = '';
require __DIR__ . '/inc/header.php';
?>
    <main id="noi-dung">
        <h1><?= $laAdmin ? 'Quản lý toàn bộ đơn hàng trên sàn' : 'Đơn hàng của tôi' ?></h1>
<?php require __DIR__ . '/inc/thong-bao.php'; ?>

<?php if ($donChiTiet !== null):
    $tongDonCt = array_sum(array_map(fn(array $m): int => (int) $m['thanh_tien'], $dsMonTrongDon));
?>
        <section class="chi-tiet-don-hang">
            <h2>Chi tiết đơn hàng #<?= (int) $donChiTiet['id'] ?></h2>
            <p><a href="don-hang.php">← Quay lại danh sách đơn hàng</a></p>
            <p>
                <strong>Khách hàng:</strong> <?= e((string) $donChiTiet['ho_ten']) ?> (<?= e((string) $donChiTiet['email']) ?>) ·
                <strong>Ngày đặt:</strong> <?= e((string) $donChiTiet['ngay_dat']) ?> ·
                <strong>Trạng thái:</strong> <?= e(TRANG_THAI_DON[$donChiTiet['trang_thai']] ?? (string) $donChiTiet['trang_thai']) ?>
            </p>
<?php if (($donChiTiet['ghi_chu'] ?? '') !== '' && $donChiTiet['ghi_chu'] !== null): ?>
            <p><strong>Ghi chú giao hàng:</strong> <?= e((string) $donChiTiet['ghi_chu']) ?></p>
<?php endif; ?>

<?php if ($laAdmin): ?>
            <form method="post" action="don-hang.php?id=<?= (int) $donChiTiet['id'] ?>" class="form-loc-thu">
                <input type="hidden" name="don_hang_id" value="<?= (int) $donChiTiet['id'] ?>">
                <label for="tt-don-ct">Đổi trạng thái đơn:</label>
                <select id="tt-don-ct" name="trang_thai">
<?php foreach (TRANG_THAI_DON as $maTt => $nhanTt): ?>
                    <option value="<?= e($maTt) ?>"<?= $maTt === $donChiTiet['trang_thai'] ? ' selected' : '' ?>><?= e($nhanTt) ?></option>
<?php endforeach; ?>
                </select>
                <button type="submit" class="nut-chinh">Cập nhật trạng thái</button>
            </form>
<?php endif; ?>

            <div class="khung-bang">
                <table>
                    <caption>Các vật phẩm trong đơn hàng #<?= (int) $donChiTiet['id'] ?></caption>
                    <thead>
                        <tr>
                            <th scope="col">Mã</th>
                            <th scope="col">Tên sản phẩm</th>
                            <th scope="col">Đơn giá lúc đặt</th>
                            <th scope="col">Số lượng</th>
                            <th scope="col">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
<?php foreach ($dsMonTrongDon as $m): ?>
                        <tr>
                            <td><?= e((string) $m['ma']) ?></td>
                            <td><a href="chi-tiet.php?id=<?= (int) $m['san_pham_id'] ?>"><?= e((string) $m['ten']) ?></a></td>
                            <td><?= e(vnd((int) $m['don_gia'])) ?></td>
                            <td><?= (int) $m['so_luong'] ?></td>
                            <td><strong><?= e(vnd((int) $m['thanh_tien'])) ?></strong></td>
                        </tr>
<?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th scope="row" colspan="4">Tổng cộng đơn hàng #<?= (int) $donChiTiet['id'] ?></th>
                            <td><strong><?= e(vnd($tongDonCt)) ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>
<?php endif; ?>

        <section>
            <h2>Danh sách đơn hàng (<?= count($dsDon) ?> đơn)</h2>
<?php if (!$dsDon): ?>
            <p>Bạn chưa có đơn hàng nào. <a href="danh-sach.php">Khám phá danh mục ngọc quý →</a></p>
<?php else: ?>
            <div class="khung-bang">
                <table>
                    <caption>Bảng tổng hợp đơn hàng</caption>
                    <thead>
                        <tr>
                            <th scope="col">Mã đơn</th>
                            <th scope="col">Ngày đặt</th>
<?php if ($laAdmin): ?>
                            <th scope="col">Khách hàng</th>
<?php endif; ?>
                            <th scope="col">Số món</th>
                            <th scope="col">Tổng tiền</th>
                            <th scope="col">Trạng thái</th>
                            <th scope="col">Chi tiết</th>
                        </tr>
                    </thead>
                    <tbody>
<?php foreach ($dsDon as $d): ?>
                        <tr>
                            <td><strong>#<?= (int) $d['id'] ?></strong></td>
                            <td><?= e((string) $d['ngay_dat']) ?></td>
<?php if ($laAdmin): ?>
                            <td><?= e((string) $d['ho_ten']) ?><br><small><?= e((string) $d['email']) ?></small></td>
<?php endif; ?>
                            <td><?= (int) $d['tong_mon'] ?></td>
                            <td><strong><?= e(vnd((int) $d['tong_tien'])) ?></strong></td>
                            <td>
<?php if ($laAdmin): ?>
                                <form method="post" action="don-hang.php" class="form-loc-thu">
                                    <input type="hidden" name="don_hang_id" value="<?= (int) $d['id'] ?>">
                                    <label for="tt-<?= (int) $d['id'] ?>" class="an-man-hinh">Trạng thái đơn #<?= (int) $d['id'] ?></label>
                                    <select id="tt-<?= (int) $d['id'] ?>" name="trang_thai">
<?php foreach (TRANG_THAI_DON as $maTt => $nhanTt): ?>
                                        <option value="<?= e($maTt) ?>"<?= $maTt === $d['trang_thai'] ? ' selected' : '' ?>><?= e($nhanTt) ?></option>
<?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="nut-phu">Lưu</button>
                                </form>
<?php else: ?>
                                <?= e(TRANG_THAI_DON[$d['trang_thai']] ?? (string) $d['trang_thai']) ?>
<?php endif; ?>
                            </td>
                            <td><a href="don-hang.php?id=<?= (int) $d['id'] ?>">Xem chi tiết →</a></td>
                        </tr>
<?php endforeach; ?>
                    </tbody>
                </table>
            </div>
<?php endif; ?>
        </section>
    </main>
<?php require __DIR__ . '/inc/footer.php'; ?>

<?php
/**
 * sua-san-pham.php - Trang sửa sản phẩm ngọc quý theo ?id= (chỉ dành cho vai trò quan_tri, gợi ý 4).
 * Đọc dữ liệu cũ từ CSDL để điền sẵn vào form; nếu id sai hoặc không tồn tại -> 404.
 * Dùng chung hàm kiemTraSanPham(); hỗ trợ đổi ảnh đại diện mới; thành công -> UPDATE + PRG + flash.
 * Cách thử: mở sua-san-pham.php?id=1 bằng tài khoản khách -> 403; bằng admin -> sửa giá, tồn kho rồi lưu.
 */
require __DIR__ . '/inc/config.php';
require __DIR__ . '/inc/kiem-tra.php';

use App\Services\TaiAnh;

yeuCauQuyen('quan_tri');

$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$cu = false;
if ($id !== false) {
    $st = db()->prepare('SELECT * FROM san_pham WHERE id = ?');
    $st->execute([$id]);
    $cu = $st->fetch();
}
if ($cu === false) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

$dsDanhMuc = db()->query('SELECT id, ten FROM danh_muc ORDER BY id')->fetchAll();
$idDanhMuc = array_map('intval', array_column($dsDanhMuc, 'id'));

$loi = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$du, $loi] = kiemTraSanPham($_POST, $idDanhMuc);

    $tep = isset($_FILES['anh']) && is_array($_FILES['anh']) ? $_FILES['anh'] : null;
    $loiAnh = TaiAnh::kiemTra($tep);
    if ($loiAnh !== null) {
        $loi['anh'] = $loiAnh;
    }

    if (!$loi) {
        $tenAnh = TaiAnh::coTep($tep)
            ? (new TaiAnh(__DIR__ . '/uploads'))->luu($tep)
            : (string) ($cu['anh'] ?? 'nhan-phi-thuy-01.jpg');

        try {
            $st = db()->prepare(
                'UPDATE san_pham
                 SET danh_muc_id = :danh_muc_id, ma = :ma, ten = :ten, loai = :loai,
                     gia = :gia, ton_kho = :ton_kho, xuat_xu = :xuat_xu, kiem_dinh = :kiem_dinh,
                     trong_luong = :trong_luong, do_cung = :do_cung, chiet_suat = :chiet_suat,
                     ty_trong = :ty_trong, anh = :anh, mo_ta = :mo_ta
                 WHERE id = :id'
            );
            $st->execute($du + ['anh' => $tenAnh, 'id' => (int) $id]);
            datFlash('ok', "Đã lưu sản phẩm #$id ({$du['ma']}).");
            header('Location: quan-tri.php');
            exit;
        } catch (PDOException $e) {
            if (($e->errorInfo[1] ?? 0) !== 1062) {
                throw $e;
            }
            $loi['ma'] = 'Mã sản phẩm này đã thuộc về một sản phẩm khác.';
        }
    }
}

$f = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $cu;

$tieuDe = 'Sửa sản phẩm #' . $id;
$trang  = '';
require __DIR__ . '/inc/header.php';
?>
    <main id="noi-dung">
        <h1>Sửa sản phẩm #<?= (int) $id ?> — <?= e((string) $cu['ten']) ?></h1>
<?php require __DIR__ . '/inc/thong-bao.php'; ?>
        <p><a href="quan-tri.php">← Quay lại trang Quản trị</a></p>

<?php if ($loi): ?>
        <p class="thong-bao loi" role="alert">Có <?= count($loi) ?> ô chưa hợp lệ, vui lòng kiểm tra bên dưới.</p>
<?php endif; ?>

        <form class="form-tham-dinh" method="post" action="sua-san-pham.php?id=<?= (int) $id ?>" enctype="multipart/form-data">
            <fieldset>
                <legend>Thông tin cơ bản &amp; Định giá</legend>
                <p>
                    <label for="sp-ma">Mã sản phẩm (*):</label>
                    <input id="sp-ma" name="ma" type="text" maxlength="30" required value="<?= e((string) ($f['ma'] ?? '')) ?>">
                    <small class="loi-truong"><?= e($loi['ma'] ?? '') ?></small>
                </p>
                <p>
                    <label for="sp-ten">Tên sản phẩm (*):</label>
                    <input id="sp-ten" name="ten" type="text" maxlength="200" required value="<?= e((string) ($f['ten'] ?? '')) ?>">
                    <small class="loi-truong"><?= e($loi['ten'] ?? '') ?></small>
                </p>
                <p>
                    <label for="sp-dm">Danh mục (*):</label>
                    <select id="sp-dm" name="danh_muc_id" required>
                        <option value="">-- Chọn danh mục --</option>
<?php foreach ($dsDanhMuc as $dm): ?>
                        <option value="<?= (int) $dm['id'] ?>"<?= (string) ($f['danh_muc_id'] ?? '') === (string) $dm['id'] ? ' selected' : '' ?>><?= e((string) $dm['ten']) ?></option>
<?php endforeach; ?>
                    </select>
                    <small class="loi-truong"><?= e($loi['danh_muc_id'] ?? '') ?></small>
                </p>
                <p>
                    <label for="sp-gia">Giá niêm yết (VNĐ, tối thiểu 1.000) (*):</label>
                    <input id="sp-gia" name="gia" type="number" min="1000" required value="<?= e((string) ($f['gia'] ?? '')) ?>">
                    <small class="loi-truong"><?= e($loi['gia'] ?? '') ?></small>
                </p>
                <p>
                    <label for="sp-ton">Số lượng tồn kho (0 – 9999) (*):</label>
                    <input id="sp-ton" name="ton_kho" type="number" min="0" max="9999" required value="<?= e((string) ($f['ton_kho'] ?? '0')) ?>">
                    <small class="loi-truong"><?= e($loi['ton_kho'] ?? '') ?></small>
                </p>
                <p>
                    <label for="sp-anh">Đổi ảnh đại diện mới (bỏ trống nếu giữ ảnh hiện tại: <?= e((string) ($cu['anh'] ?? '')) ?>):</label>
                    <input id="sp-anh" name="anh" type="file" accept="image/jpeg, image/png, image/webp">
                    <small class="loi-truong"><?= e($loi['anh'] ?? '') ?></small>
                </p>
            </fieldset>

            <fieldset>
                <legend>Thông số giám định &amp; Mô tả</legend>
                <p>
                    <label for="sp-loai">Chủng loại ngọc:</label>
                    <input id="sp-loai" name="loai" type="text" maxlength="150" value="<?= e((string) ($f['loai'] ?? '')) ?>">
                </p>
                <p>
                    <label for="sp-xuatxu">Xuất xứ:</label>
                    <input id="sp-xuatxu" name="xuat_xu" type="text" maxlength="100" value="<?= e((string) ($f['xuat_xu'] ?? '')) ?>">
                </p>
                <p>
                    <label for="sp-kiemdinh">Tình trạng kiểm định:</label>
                    <input id="sp-kiemdinh" name="kiem_dinh" type="text" maxlength="150" value="<?= e((string) ($f['kiem_dinh'] ?? '')) ?>">
                </p>
                <p>
                    <label for="sp-tl">Trọng lượng:</label>
                    <input id="sp-tl" name="trong_luong" type="text" maxlength="50" value="<?= e((string) ($f['trong_luong'] ?? '')) ?>">
                </p>
                <p>
                    <label for="sp-docung">Độ cứng Mohs:</label>
                    <input id="sp-docung" name="do_cung" type="text" maxlength="50" value="<?= e((string) ($f['do_cung'] ?? '6.5 - 7.0')) ?>">
                </p>
                <p>
                    <label for="sp-cs">Chiết suất:</label>
                    <input id="sp-cs" name="chiet_suat" type="text" maxlength="50" value="<?= e((string) ($f['chiet_suat'] ?? '1.66')) ?>">
                </p>
                <p>
                    <label for="sp-tt">Tỷ trọng:</label>
                    <input id="sp-tt" name="ty_trong" type="text" maxlength="50" value="<?= e((string) ($f['ty_trong'] ?? '3.33 g/cm³')) ?>">
                </p>
                <p>
                    <label for="sp-mota">Mô tả chi tiết:</label>
                    <textarea id="sp-mota" name="mo_ta" rows="4" maxlength="2000"><?= e((string) ($f['mo_ta'] ?? '')) ?></textarea>
                    <small class="loi-truong"><?= e($loi['mo_ta'] ?? '') ?></small>
                </p>
            </fieldset>

            <p><button type="submit">Lưu thay đổi</button></p>
        </form>
    </main>
<?php require __DIR__ . '/inc/footer.php'; ?>

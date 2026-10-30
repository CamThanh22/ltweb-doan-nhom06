<?php
/**
 * them-san-pham.php - Trang thêm sản phẩm ngọc quý mới (chỉ dành cho vai trò quan_tri).
 * Dùng chung hàm kiemTraSanPham() trong inc/kiem-tra.php (gợi ý 4 Phụ lục B); hỗ trợ tải ảnh đại diện
 * an toàn qua lớp TaiAnh; bắt lỗi 1062 khi trùng mã sản phẩm; thành công -> lastInsertId + PRG + flash.
 * Cách thử: gửi tên trống, giá 0, tồn kho -1, danh mục 999 -> báo lỗi cạnh từng ô và giữ lại dữ liệu đã nhập.
 */
require __DIR__ . '/inc/config.php';
require __DIR__ . '/inc/kiem-tra.php';

use App\Services\TaiAnh;

yeuCauQuyen('quan_tri');

$dsDanhMuc = db()->query('SELECT id, ten FROM danh_muc ORDER BY id')->fetchAll();
$idDanhMuc = array_map('intval', array_column($dsDanhMuc, 'id'));

$loi = [];
$f = [
    'ma'          => '',
    'ten'         => '',
    'danh_muc_id' => '',
    'gia'         => '',
    'ton_kho'     => '1',
    'loai'        => 'Phỉ thúy Type A',
    'xuat_xu'     => 'Myanmar',
    'kiem_dinh'   => 'Đã thẩm định (PNJ Lab)',
    'trong_luong' => '10.0 g',
    'do_cung'     => '6.5 - 7.0',
    'chiet_suat'  => '1.66',
    'ty_trong'    => '3.33 g/cm³',
    'mo_ta'       => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array_keys($f) as $k) {
        $f[$k] = layChuoi($_POST, $k);
    }
    [$du, $loi] = kiemTraSanPham($_POST, $idDanhMuc);

    $tep = isset($_FILES['anh']) && is_array($_FILES['anh']) ? $_FILES['anh'] : null;
    $loiAnh = TaiAnh::kiemTra($tep);
    if ($loiAnh !== null) {
        $loi['anh'] = $loiAnh;
    }

    if (!$loi) {
        $tenAnh = TaiAnh::coTep($tep)
            ? (new TaiAnh(__DIR__ . '/uploads'))->luu($tep)
            : 'nhan-phi-thuy-01.jpg';

        try {
            $st = db()->prepare(
                'INSERT INTO san_pham (danh_muc_id, ma, ten, loai, gia, ton_kho, xuat_xu, kiem_dinh,
                                       trong_luong, do_cung, chiet_suat, ty_trong, anh, mo_ta)
                 VALUES (:danh_muc_id, :ma, :ten, :loai, :gia, :ton_kho, :xuat_xu, :kiem_dinh,
                         :trong_luong, :do_cung, :chiet_suat, :ty_trong, :anh, :mo_ta)'
            );
            $st->execute($du + ['anh' => $tenAnh]);
            $idMoi = (int) db()->lastInsertId();
            datFlash('ok', "Đã thêm sản phẩm mới #$idMoi ({$du['ma']}).");
            header('Location: quan-tri.php');
            exit;
        } catch (PDOException $e) {
            if (($e->errorInfo[1] ?? 0) !== 1062) {
                throw $e;
            }
            $loi['ma'] = 'Mã sản phẩm này đã tồn tại trong CSDL.';
        }
    }
}

$tieuDe = 'Thêm sản phẩm ngọc quý';
$trang  = '';
require __DIR__ . '/inc/header.php';
?>
    <main id="noi-dung">
        <h1>Thêm sản phẩm ngọc quý mới</h1>
<?php require __DIR__ . '/inc/thong-bao.php'; ?>
        <p><a href="quan-tri.php">← Quay lại trang Quản trị</a></p>

<?php if ($loi): ?>
        <p class="thong-bao loi" role="alert">Có <?= count($loi) ?> ô chưa hợp lệ, vui lòng kiểm tra bên dưới.</p>
<?php endif; ?>

        <form class="form-tham-dinh" method="post" action="them-san-pham.php" enctype="multipart/form-data">
            <fieldset>
                <legend>Thông tin cơ bản &amp; Định giá</legend>
                <p>
                    <label for="sp-ma">Mã sản phẩm (*):</label>
                    <input id="sp-ma" name="ma" type="text" maxlength="30" required value="<?= e((string) $f['ma']) ?>" placeholder="Ví dụ: JADE-35">
                    <small class="loi-truong"><?= e($loi['ma'] ?? '') ?></small>
                </p>
                <p>
                    <label for="sp-ten">Tên sản phẩm (*):</label>
                    <input id="sp-ten" name="ten" type="text" maxlength="200" required value="<?= e((string) $f['ten']) ?>" placeholder="Ví dụ: Nhẫn ngọc phỉ thúy Băng Chủng">
                    <small class="loi-truong"><?= e($loi['ten'] ?? '') ?></small>
                </p>
                <p>
                    <label for="sp-dm">Danh mục (*):</label>
                    <select id="sp-dm" name="danh_muc_id" required>
                        <option value="">-- Chọn danh mục --</option>
<?php foreach ($dsDanhMuc as $dm): ?>
                        <option value="<?= (int) $dm['id'] ?>"<?= (string) $f['danh_muc_id'] === (string) $dm['id'] ? ' selected' : '' ?>><?= e((string) $dm['ten']) ?></option>
<?php endforeach; ?>
                    </select>
                    <small class="loi-truong"><?= e($loi['danh_muc_id'] ?? '') ?></small>
                </p>
                <p>
                    <label for="sp-gia">Giá niêm yết (VNĐ, tối thiểu 1.000) (*):</label>
                    <input id="sp-gia" name="gia" type="number" min="1000" required value="<?= e((string) $f['gia']) ?>">
                    <small class="loi-truong"><?= e($loi['gia'] ?? '') ?></small>
                </p>
                <p>
                    <label for="sp-ton">Số lượng tồn kho (0 – 9999) (*):</label>
                    <input id="sp-ton" name="ton_kho" type="number" min="0" max="9999" required value="<?= e((string) $f['ton_kho']) ?>">
                    <small class="loi-truong"><?= e($loi['ton_kho'] ?? '') ?></small>
                </p>
                <p>
                    <label for="sp-anh">Ảnh đại diện (JPG, PNG, WebP, tối đa 2 MB; bỏ trống sẽ dùng ảnh mặc định):</label>
                    <input id="sp-anh" name="anh" type="file" accept="image/jpeg, image/png, image/webp">
                    <small class="loi-truong"><?= e($loi['anh'] ?? '') ?></small>
                </p>
            </fieldset>

            <fieldset>
                <legend>Thông số giám định &amp; Mô tả</legend>
                <p>
                    <label for="sp-loai">Chủng loại ngọc:</label>
                    <input id="sp-loai" name="loai" type="text" maxlength="150" value="<?= e((string) $f['loai']) ?>">
                </p>
                <p>
                    <label for="sp-xuatxu">Xuất xứ:</label>
                    <input id="sp-xuatxu" name="xuat_xu" type="text" maxlength="100" value="<?= e((string) $f['xuat_xu']) ?>">
                </p>
                <p>
                    <label for="sp-kiemdinh">Tình trạng kiểm định:</label>
                    <input id="sp-kiemdinh" name="kiem_dinh" type="text" maxlength="150" value="<?= e((string) $f['kiem_dinh']) ?>">
                </p>
                <p>
                    <label for="sp-tl">Trọng lượng:</label>
                    <input id="sp-tl" name="trong_luong" type="text" maxlength="50" value="<?= e((string) $f['trong_luong']) ?>">
                </p>
                <p>
                    <label for="sp-mota">Mô tả chi tiết:</label>
                    <textarea id="sp-mota" name="mo_ta" rows="4" maxlength="2000"><?= e((string) $f['mo_ta']) ?></textarea>
                    <small class="loi-truong"><?= e($loi['mo_ta'] ?? '') ?></small>
                </p>
            </fieldset>

            <p><button type="submit">Thêm sản phẩm</button></p>
        </form>
    </main>
<?php require __DIR__ . '/inc/footer.php'; ?>

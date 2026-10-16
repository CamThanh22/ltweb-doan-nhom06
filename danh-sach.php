<?php
/**
 * danh-sach.php - Danh sách sản phẩm do PHP sinh từ data/san-pham.json qua KhoSanPham.
 * Một biểu mẫu GET: ?q=từ khóa&dm=danh mục&sx=kiểu sắp xếp (chia sẻ được qua URL, giữ lại lựa chọn).
 * dm và sx phải thuộc danh sách cho phép, giá trị lạ bị bỏ và có báo. Mọi dữ liệu in ra đều qua e().
 * Cách thử: danh-sach.php?q=nhan&dm=phi-thuy&sx=gia-tang ; danh-sach.php?dm=abc ; ?q=kim (rỗng).
 */
require __DIR__ . '/inc/config.php';

use App\Data\KhoSanPham;

$kho = new KhoSanPham(__DIR__ . '/data/san-pham.json');

$tuKhoa = mb_substr(layChuoi($_GET, 'q'), 0, 100);
$danhMuc = layChuoi($_GET, 'dm');
$sapXep = layChuoi($_GET, 'sx');
$dmCoSan = $kho->danhMucCoSan();            // khóa => số sản phẩm
$canhBao = [];

if ($danhMuc !== '' && !array_key_exists($danhMuc, $dmCoSan)) {
    $canhBao[] = 'Danh mục không hợp lệ nên đã hiện tất cả danh mục.';
    $danhMuc = '';
}
if ($sapXep === '') {
    $sapXep = 'mac-dinh';
} elseif (!array_key_exists($sapXep, KhoSanPham::SAP_XEP)) {
    $canhBao[] = 'Kiểu sắp xếp không hợp lệ nên đã dùng thứ tự mặc định.';
    $sapXep = 'mac-dinh';
}

$ketQua = $kho->timKiem($tuKhoa, $danhMuc, $sapXep);

$tieuDe = 'Danh mục ngọc quý niêm yết';
$trang = 'danh-sach';
$moTa = 'Bảng danh mục các loại ngọc phỉ thúy, ngọc bích, cẩm thạch thiên nhiên đang niêm yết tại sàn giao dịch JadeHub.';
$jsThem = ['js/nut-yeu-thich.js'];
require __DIR__ . '/inc/header.php';
?>
    <main id="noi-dung">
        <h1>Danh mục các loại ngọc quý đang giao dịch</h1>
<?php require __DIR__ . '/inc/thong-bao.php'; ?>
        <section>
            <h2>Tìm kiếm, lọc và sắp xếp sản phẩm</h2>
            <form class="bo-loc" method="get" action="danh-sach.php">
                <label for="tim-kiem">Tìm sản phẩm</label>
                <input id="tim-kiem" name="q" type="search" maxlength="100" value="<?= e($tuKhoa) ?>" placeholder="Gõ tên, mã hoặc loại ngọc…">

                <label for="loc-dm">Lọc theo danh mục</label>
                <select id="loc-dm" name="dm">
                    <option value="">Tất cả</option>
<?php foreach ($dmCoSan as $khoa => $soLuong): ?>
                    <option value="<?= e($khoa) ?>"<?= $khoa === $danhMuc ? ' selected' : '' ?>><?= e(App\Models\SanPham::DANH_MUC[$khoa] ?? $khoa) ?> (<?= $soLuong ?>)</option>
<?php endforeach; ?>
                </select>

                <label for="sap-xep">Sắp xếp</label>
                <select id="sap-xep" name="sx">
<?php foreach (KhoSanPham::SAP_XEP as $khoa => $nhan): ?>
                    <option value="<?= e($khoa) ?>"<?= $khoa === $sapXep ? ' selected' : '' ?>><?= e($nhan) ?></option>
<?php endforeach; ?>
                </select>

                <button type="submit" class="nut-loc">Áp dụng</button>
            </form>

<?php foreach ($canhBao as $cb): ?>
            <p class="thong-bao loi" role="alert"><?= e($cb) ?></p>
<?php endforeach; ?>

            <p id="trang-thai-ds" role="status">
<?php if ($ketQua): ?>
                Tìm thấy <?= count($ketQua) ?> sản phẩm<?= $tuKhoa !== '' ? ' cho từ khóa “' . e($tuKhoa) . '”' : '' ?>.
<?php else: ?>
                Không có sản phẩm phù hợp<?= $tuKhoa !== '' ? ' với từ khóa “' . e($tuKhoa) . '”' : '' ?>. <a href="danh-sach.php">Xóa bộ lọc</a>
<?php endif; ?>
            </p>

            <div class="luoi-san-pham" id="ds-san-pham">
<?php foreach ($ketQua as $sp): ?>
                <article class="the-san-pham">
                    <img src="images/<?= e($sp->anh) ?>" alt="<?= e($sp->ten) ?>" loading="lazy" width="400" height="300">
                    <div class="noi-dung-the">
                        <h3><a href="chi-tiet.php?id=<?= $sp->id ?>"><?= e($sp->ten) ?></a></h3>
                        <p><?= e($sp->loai) ?> · <?= e($sp->xuatXu) ?></p>
                        <p class="gia-san-pham"><?= e(vnd($sp->gia)) ?></p>
                        <p><small><?= e($sp->kiemDinh) ?></small></p>
                        <button class="nut-yeu-thich" type="button" data-yeuthich="<?= $sp->id ?>" aria-pressed="false">♡ Yêu thích</button>
                    </div>
                </article>
<?php endforeach; ?>
            </div>
        </section>
    </main>
<?php require __DIR__ . '/inc/footer.php'; ?>

<?php
/**
 * danh-sach.php - Tìm kiếm, lọc (danh mục, giá tối đa), sắp xếp, phân trang sản phẩm từ MySQL (gợi ý 3).
 * Gõ có dấu, không dấu, chữ hoa đều tìm được nhờ utf8mb4_unicode_ci + LIKE + addcslashes.
 * Giữ nguyên bộ lọc khi chuyển trang bằng http_build_query (slide 32). Có khối gợi ý tức thời #goi-y (js/tim-kiem.js).
 * Tiếp thu góp ý Bài tập 5: khối "Viên ngọc tiêu biểu" đặt ở cuối trang và tự ẩn khi đang tìm/lọc hoặc không có kết quả.
 * Cách thử: danh-sach.php?q=nhan&dm=1&gia=20000000&sx=gia-tang&trang=2 ; ?sx=xyz&trang=abc ; ?dm=999.
 */
require dirname(__DIR__, 2) . '/inc/config.php';

use App\Data\KhoSanPham;

$kho   = new KhoSanPham(db());
$duong = ['options' => ['min_range' => 1]];

$loc = [
    'q'        => mb_substr(layChuoi($_GET, 'q'), 0, 100),
    'dm'       => filter_var($_GET['dm'] ?? '', FILTER_VALIDATE_INT, $duong),
    'giaToiDa' => filter_var($_GET['gia'] ?? '', FILTER_VALIDATE_INT, $duong),
];

$sx       = layChuoi($_GET, 'sx') ?: 'moi';
$kieuSx   = KhoSanPham::SAP_XEP;
$sxHopLe  = array_key_exists($sx, $kieuSx) ? $sx : 'moi';

$moiTrang = 8;
$tong     = $kho->dem($loc);
$soTrang  = max(1, (int) ceil($tong / $moiTrang));
$hienTai  = filter_var($_GET['trang'] ?? 1, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1, 'default' => 1],
]);
$hienTai  = min((int) $hienTai, $soTrang);
$ds       = $kho->trang($loc, $sxHopLe, $moiTrang, ($hienTai - 1) * $moiTrang);

$dsDanhMuc = $kho->danhSachDanhMuc();
$dangLoc   = ($loc['q'] !== '' || $loc['dm'] !== false || $loc['giaToiDa'] !== false);
$tieuBieu  = (!$dangLoc && $hienTai === 1) ? $kho->timTheoId(1) : null;

function lienKetTrang(int $so): string
{
    $thamSo = [];
    foreach (['q', 'dm', 'gia', 'sx'] as $k) {
        if (isset($_GET[$k]) && is_string($_GET[$k]) && trim($_GET[$k]) !== '') {
            $thamSo[$k] = trim($_GET[$k]);
        }
    }
    $thamSo['trang'] = $so;
    return '?' . http_build_query($thamSo);
}

$tieuDe = 'Danh mục ngọc quý niêm yết';
$trang  = 'danh-sach';
$moTa   = 'Bảng danh mục các loại ngọc phỉ thúy, ngọc bích, cẩm thạch thiên nhiên đang niêm yết tại sàn giao dịch JadeHub.';
$jsThem = ['js/nut-yeu-thich.js', 'js/tim-kiem.js'];
require dirname(__DIR__, 2) . '/inc/header.php';
?>
    <main id="noi-dung">
        <div class="jh-collection-heading"><span class="jh-eyebrow">BỘ SƯU TẬP JADEHUB</span><h1>Bộ sưu tập ngọc quý</h1><p>Những câu chuyện nguyên bản, cùng thông tin sản phẩm và hồ sơ đi kèm.</p></div>
<?php require dirname(__DIR__, 2) . '/inc/thong-bao.php'; ?>
        <div class="jh-catalog-layout">
        <aside class="jh-catalog-sidebar" aria-label="Danh mục ngọc"><h2>Danh mục</h2>
        <a href="danh-sach.php"<?= $loc['dm'] === false ? ' aria-current="page"' : '' ?>>Tất cả</a>
        <?php foreach ($dsDanhMuc as $dm): ?><a href="?<?= e(http_build_query(['dm'=>(int)$dm['id'], 'q'=>$loc['q'], 'gia'=>$loc['giaToiDa'] ?: '', 'sx'=>$sxHopLe])) ?>"<?= $loc['dm'] === (int)$dm['id'] ? ' aria-current="page"' : '' ?>><span><?= e((string)$dm['ten']) ?></span><small><?= (int)$dm['so_sp'] ?></small></a><?php endforeach; ?>
        <p class="jh-catalog-note">Đọc kỹ thông tin nguồn gốc và hồ sơ của từng sản phẩm trước khi lựa chọn.</p></aside>
        <section class="jh-catalog-results">
            <h2 class="bo-qua">Tìm viên ngọc dành cho bạn</h2>
            <form class="bo-loc jh-collection-filter" method="get" action="danh-sach.php">
                <div class="jh-filter-search">
                <label for="o-tim">Tìm sản phẩm</label>
                <div class="khung-o-tim">
                    <input id="o-tim" name="q" type="search" maxlength="100" value="<?= e($loc['q']) ?>" autocomplete="off" placeholder="Gõ tên, mã hoặc loại ngọc (vd: nhẫn, Levi's)…">
                    <ul id="goi-y" class="ds-goi-y" aria-live="polite"></ul>
                </div>

                <button type="submit" class="nut-loc">Tìm kiếm</button>
                </div>
                <details class="jh-filter-details" open>
                    <summary>Bộ lọc</summary>
                    <div class="jh-filter-fields">
                        <div class="jh-filter-category">
                <label for="loc-dm">Danh mục</label>
                <select id="loc-dm" name="dm">
                    <option value="">Tất cả danh mục</option>
<?php foreach ($dsDanhMuc as $dm): ?>
                    <option value="<?= (int) $dm['id'] ?>"<?= $loc['dm'] === (int) $dm['id'] ? ' selected' : '' ?>><?= e((string) $dm['ten']) ?> (<?= (int) $dm['so_sp'] ?>)</option>
<?php endforeach; ?>
                </select>

                        </div>
                        <div>
                <label for="loc-gia">Giá tối đa (VNĐ)</label>
                <input id="loc-gia" name="gia" type="number" min="1000" step="500000" value="<?= $loc['giaToiDa'] !== false ? (int) $loc['giaToiDa'] : '' ?>" placeholder="Ví dụ: 20000000">

                        </div>
                        <div>
                <label for="sap-xep">Sắp xếp</label>
                <select id="sap-xep" name="sx">
<?php foreach ($kieuSx as $gt => $nhan): ?>
                    <option value="<?= e($gt) ?>"<?= $gt === $sxHopLe ? ' selected' : '' ?>><?= e($nhan) ?></option>
<?php endforeach; ?>
                </select>

                        </div>
                <button type="submit" class="nut-loc">Áp dụng</button>
<?php if ($dangLoc): ?>
                <a href="danh-sach.php" class="nut-phu">Xóa bộ lọc</a>
<?php endif; ?>
                    </div>
                </details>
            </form>

            <p id="trang-thai-ds" role="status">
<?php if ($ds): ?>
                Trang <strong><?= $hienTai ?>/<?= $soTrang ?></strong> — Tìm thấy <strong><?= $tong ?></strong> sản phẩm<?= $loc['q'] !== '' ? ' cho từ khóa “' . e($loc['q']) . '”' : '' ?>.
<?php else: ?>
                Không có kết quả phù hợp<?= $loc['q'] !== '' ? ' với từ khóa “' . e($loc['q']) . '”' : '' ?>. <a href="danh-sach.php">Xóa bộ lọc</a>
<?php endif; ?>
            </p>

            <div class="luoi-san-pham jh-collection-grid" id="ds-san-pham">
<?php foreach ($ds as $sp): ?>
                <article class="the-san-pham">
                    <a class="jh-listing-image" href="chi-tiet.php?id=<?= (int)$sp->id ?>"><img src="<?= e($sp->duongDanAnh()) ?>" alt="<?= e($sp->ten) ?>" loading="lazy" width="400" height="500"><span>Hồ sơ sản phẩm</span></a>
                    <div class="noi-dung-the">
                        <h3><a href="chi-tiet.php?id=<?= $sp->id ?>"><?= e($sp->ten) ?></a></h3>
                        <p><?= e($sp->tenDanhMuc()) ?> · <?= e($sp->xuatXu) ?></p>
                        <p class="gia-san-pham"><?= e(vnd($sp->gia)) ?></p>
                        <p><small><?= e($sp->kiemDinh) ?> · <?= $sp->conHang() ? 'Còn ' . $sp->tonKho : 'Hết hàng' ?></small></p>
                        <button class="nut-yeu-thich" type="button" data-yeuthich="<?= $sp->id ?>" aria-pressed="false">♡ Yêu thích</button>
                    </div>
                </article>
<?php endforeach; ?>
            </div>

<?php if ($soTrang > 1 && $ds): ?>
            <nav class="phan-trang" aria-label="Phân trang danh sách sản phẩm">
<?php if ($hienTai > 1): ?>
                <a href="<?= e(lienKetTrang($hienTai - 1)) ?>">← Trước</a>
<?php endif; ?>
<?php for ($i = 1; $i <= $soTrang; $i++): ?>
                <a href="<?= e(lienKetTrang($i)) ?>"<?= $i === $hienTai ? ' aria-current="page"' : '' ?>><?= $i ?></a>
<?php endfor; ?>
<?php if ($hienTai < $soTrang): ?>
                <a href="<?= e(lienKetTrang($hienTai + 1)) ?>">Sau →</a>
<?php endif; ?>
            </nav>
<?php endif; ?>
        </section>
        </div>

<?php if ($tieuBieu !== null): ?>
        <section class="vien-ngoc-tieu-bieu">
            <h2>Viên ngọc tiêu biểu của sàn</h2>
            <p>
                <strong><a href="chi-tiet.php?id=<?= $tieuBieu->id ?>"><?= e($tieuBieu->ten) ?> (<?= e($tieuBieu->ma) ?>)</a></strong>
                — <?= e($tieuBieu->moTa) ?> Giá niêm yết: <strong><?= e(vnd($tieuBieu->gia)) ?></strong>.
            </p>
        </section>
<?php endif; ?>
    </main>
<?php require dirname(__DIR__, 2) . '/inc/footer.php'; ?>

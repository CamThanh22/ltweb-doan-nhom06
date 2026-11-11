<?php
/**
 * chi-tiet.php - Chi tiết một sản phẩm theo ?id= kèm tên danh mục (JOIN) và 4 mục cùng danh mục.
 * id thiếu/sai kiểu/không có -> trang 404 (mã 404). Ghi cookie da_xem trước mọi output.
 * Form "Thêm vào giỏ" có class="them-gio form-gio" để js/gio-hang.js gửi fetch không tải lại trang;
 * khi tắt JavaScript thì form vẫn POST tới gio-hang.php (PRG).
 * Cách thử: chi-tiet.php?id=1 ; ?id=abc ; ?id=999999 ; bấm Thêm vào giỏ khi bật và tắt JS.
 */
require dirname(__DIR__, 2) . '/inc/config.php';

use App\Data\KhoSanPham;
use App\Services\DaXem;
use App\Services\GioHang;

$kho = new KhoSanPham(db());

$id = GioHang::idHopLe($_GET['id'] ?? null);
$sp = $id !== null ? $kho->timTheoId($id) : null;

if ($sp === null) {
    require dirname(__DIR__, 2) . '/pages/loi/404.php';
    exit;
}

$hoSoMau = str_starts_with($sp->kiemDinh, 'Hồ sơ minh họa · ');
DaXem::ghi($sp->id);
$maTinDang = null;
if(str_starts_with($sp->ma,'SELL-')) { $maTinDang=(int)substr($sp->ma,5); }

$cungDanhMuc = $kho->cungDanhMuc($sp, 4);

$tieuDe = $sp->ten;
$trang  = 'danh-sach';
$moTa   = 'Chi tiết, thông số giám định và chứng thư của ' . $sp->ten . '.';
$jsThem = ['js/nut-yeu-thich.js', 'js/gio-hang.js'];
require dirname(__DIR__, 2) . '/inc/header.php';
?>
    <main id="noi-dung">
<?php require dirname(__DIR__, 2) . '/inc/thong-bao.php'; ?>
        <h1>Thông tin kiểm định sản phẩm</h1>
        <?php if($maTinDang): ?><p><a href="chung-thu.php?id=<?= $maTinDang ?>">Xem hồ sơ chứng thư và mức kiểm duyệt của JadeHub</a></p><?php endif; ?>
        <div class="bo-cuc-chi-tiet">
            <article>
                <h2><?= e($sp->ten) ?> — Mã số <?= e($sp->ma) ?></h2>
                <p><?= e($sp->moTa) ?></p>
                <figure>
                    <img src="<?= e($sp->duongDanAnh()) ?>" alt="<?= e($sp->ten) ?>">
                    <figcaption>Hình 1: Ảnh sản phẩm <?= e($sp->ma) ?></figcaption>
                </figure>
<?php if ($sp->anhChungThu !== null): ?>
                <figure id="anh-chung-thu">
                    <img src="<?= str_starts_with($sp->ma, 'SELL-') ? 'uploads/' : 'images/' ?><?= e($sp->anhChungThu) ?>" alt="Chứng thư giám định đá quý của <?= e($sp->ma) ?>">
                    <figcaption>Hình 2: <?= $hoSoMau ? 'Phiếu thông tin minh họa của đồ án · Không có giá trị chứng nhận' : 'Chứng thư giám định (' . e($sp->kiemDinh) . ')' ?></figcaption>
                </figure>
<?php else: ?>
                <p><em>Sản phẩm này chưa có ảnh chứng thư kiểm định (<?= e($sp->kiemDinh) ?>).</em></p>
<?php endif; ?>
                <h3>Bảng thông số giám định vật lý</h3>
                <div class="khung-bang">
                    <table>
                        <caption><?= $hoSoMau ? 'Thông số khai báo của' : 'Bảng 1: Kết quả đo kiểm định của' ?> <?= e($sp->ma) ?></caption>
                        <thead>
                            <tr><th scope="col">Tiêu chí kiểm định</th><th scope="col">Kết quả</th></tr>
                        </thead>
                        <tbody>
                            <tr><th scope="row">Danh mục</th><td><?= e($sp->tenDanhMuc()) ?></td></tr>
                            <tr><th scope="row">Chủng loại</th><td><?= e($sp->loai) ?></td></tr>
                            <tr><th scope="row">Trọng lượng</th><td><?= e($sp->trongLuong) ?></td></tr>
                            <tr><th scope="row">Độ cứng Mohs</th><td><?= e($sp->doCung) ?></td></tr>
                            <tr><th scope="row">Chiết suất</th><td><?= e($sp->chietSuat) ?></td></tr>
                            <tr><th scope="row">Tỷ trọng</th><td><?= e($sp->tyTrong) ?></td></tr>
                        </tbody>
                    </table>
                </div>
            </article>

            <aside class="thong-tin-phu">
                <h2>Thông tin giao dịch</h2>
                <p><strong>Mã sản phẩm:</strong> <?= e($sp->ma) ?></p>
                <p><strong>Danh mục:</strong> <a href="danh-sach.php?dm=<?= $sp->danhMucId ?>"><?= e($sp->tenDanhMuc()) ?></a></p>
                <p><strong>Xuất xứ:</strong> <?= e($sp->xuatXu) ?></p>
                <p class="jh-verification"><strong><?= $hoSoMau ? 'Hồ sơ sản phẩm:' : 'Trạng thái thẩm định:' ?></strong> <?= e($sp->kiemDinh) ?></p>
<?php if ($sp->anhChungThu !== null): ?>
                <p><a class="jh-btn jh-btn--outline" href="#anh-chung-thu"><?= $hoSoMau ? 'Xem phiếu minh họa' : 'Xem chứng thư' ?></a></p>
<?php endif; ?>
                <p><strong>Tồn kho:</strong> <?= $sp->conHang() ? 'Còn ' . $sp->tonKho . ' sản phẩm' : 'Tạm hết hàng' ?></p>
                <p class="gia-san-pham"><strong>Giá niêm yết:</strong> <?= e(vnd($sp->gia)) ?></p>

<?php if ($sp->conHang()): ?>
                <form class="them-gio form-gio" method="post" action="gio-hang.php">
                <?= csrfField() ?>
                    <input type="hidden" name="hanhdong" value="them">
                    <input type="hidden" name="id" value="<?= $sp->id ?>">
                    <label for="so-luong">Số lượng (1–<?= min(GioHang::SL_TOI_DA, $sp->tonKho) ?>)</label>
                    <input id="so-luong" name="sl" type="number" min="1" max="<?= min(GioHang::SL_TOI_DA, $sp->tonKho) ?>" value="1" required>
                    <div class="jh-purchase-actions">
                        <button type="submit" class="nut-phu">Thêm vào giỏ</button>
                        <a class="jh-btn jh-btn--solid" href="mua-ngay.php?id=<?= $sp->id ?>">Mua ngay</a>
                    </div>
                </form>
<?php else: ?>
                <p class="thong-bao loi" role="status">Sản phẩm này hiện đã hết hàng.</p>
<?php endif; ?>
                <p id="thong-bao" role="status" aria-live="polite"></p>

                <button class="nut-yeu-thich" type="button" data-yeuthich="<?= $sp->id ?>" aria-pressed="false">♡ Thêm vào yêu thích</button>

                <h3>Bốn mục cùng danh mục (<?= e($sp->tenDanhMuc()) ?>)</h3>
<?php if (!$cungDanhMuc): ?>
                <p>Chưa có sản phẩm khác trong danh mục này.</p>
<?php else: ?>
                <ul>
<?php foreach ($cungDanhMuc as $lq): ?>
                    <li><a href="chi-tiet.php?id=<?= $lq->id ?>"><?= e($lq->ten) ?></a> — <?= e(vnd($lq->gia)) ?></li>
<?php endforeach; ?>
                </ul>
<?php endif; ?>
            </aside>
        </div>
    </main>
<?php require dirname(__DIR__, 2) . '/inc/footer.php'; ?>

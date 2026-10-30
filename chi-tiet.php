<?php
/**
 * chi-tiet.php - Chi tiết một sản phẩm theo ?id= kèm tên danh mục (JOIN) và 4 mục cùng danh mục.
 * id thiếu/sai kiểu/không có -> trang 404 (mã 404). Ghi cookie da_xem trước mọi output.
 * Form "Thêm vào giỏ" có class="them-gio form-gio" để js/gio-hang.js gửi fetch không tải lại trang;
 * khi tắt JavaScript thì form vẫn POST tới gio-hang.php (PRG).
 * Cách thử: chi-tiet.php?id=1 ; ?id=abc ; ?id=999999 ; bấm Thêm vào giỏ khi bật và tắt JS.
 */
require __DIR__ . '/inc/config.php';

use App\Data\KhoSanPham;
use App\Services\DaXem;
use App\Services\GioHang;

$kho = new KhoSanPham(db());

$id = GioHang::idHopLe($_GET['id'] ?? null);
$sp = $id !== null ? $kho->timTheoId($id) : null;

if ($sp === null) {
    require __DIR__ . '/404.php';
    exit;
}

DaXem::ghi($sp->id);

$cungDanhMuc = $kho->cungDanhMuc($sp, 4);

$tieuDe = $sp->ten;
$trang  = 'danh-sach';
$moTa   = 'Chi tiết, thông số giám định và chứng thư của ' . $sp->ten . '.';
$jsThem = ['js/nut-yeu-thich.js', 'js/gio-hang.js'];
require __DIR__ . '/inc/header.php';
?>
    <main id="noi-dung">
<?php require __DIR__ . '/inc/thong-bao.php'; ?>
        <h1>Thông tin kiểm định sản phẩm</h1>
        <div class="bo-cuc-chi-tiet">
            <article>
                <h2><?= e($sp->ten) ?> — Mã số <?= e($sp->ma) ?></h2>
                <p><?= e($sp->moTa) ?></p>
                <figure>
                    <img src="<?= e($sp->duongDanAnh()) ?>" alt="<?= e($sp->ten) ?>">
                    <figcaption>Hình 1: Ảnh sản phẩm <?= e($sp->ma) ?></figcaption>
                </figure>
<?php if ($sp->anhChungThu !== null): ?>
                <figure>
                    <img src="images/<?= e($sp->anhChungThu) ?>" alt="Chứng thư giám định đá quý của <?= e($sp->ma) ?>">
                    <figcaption>Hình 2: Chứng thư giám định (<?= e($sp->kiemDinh) ?>)</figcaption>
                </figure>
<?php else: ?>
                <p><em>Sản phẩm này chưa có ảnh chứng thư kiểm định (<?= e($sp->kiemDinh) ?>).</em></p>
<?php endif; ?>
                <h3>Bảng thông số giám định vật lý</h3>
                <div class="khung-bang">
                    <table>
                        <caption>Bảng 1: Kết quả đo kiểm định của <?= e($sp->ma) ?></caption>
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
                <p><strong>Kiểm định:</strong> <?= e($sp->kiemDinh) ?></p>
                <p><strong>Tồn kho:</strong> <?= $sp->conHang() ? 'Còn ' . $sp->tonKho . ' sản phẩm' : 'Tạm hết hàng' ?></p>
                <p class="gia-san-pham"><strong>Giá niêm yết:</strong> <?= e(vnd($sp->gia)) ?></p>

<?php if ($sp->conHang()): ?>
                <form class="them-gio form-gio" method="post" action="gio-hang.php">
                    <input type="hidden" name="hanhdong" value="them">
                    <input type="hidden" name="id" value="<?= $sp->id ?>">
                    <label for="so-luong">Số lượng (1–<?= min(GioHang::SL_TOI_DA, $sp->tonKho) ?>)</label>
                    <input id="so-luong" name="sl" type="number" min="1" max="<?= min(GioHang::SL_TOI_DA, $sp->tonKho) ?>" value="1" required>
                    <button type="submit" class="nut-chinh">Thêm vào giỏ</button>
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
<?php require __DIR__ . '/inc/footer.php'; ?>

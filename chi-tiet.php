<?php
/**
 * chi-tiet.php - Chi tiết một sản phẩm theo ?id=. id thiếu/sai kiểu/không có -> trang 404 (mã 404).
 * Ghi cookie da_xem (4 mục xem gần nhất) TRƯỚC mọi output; có form "Thêm vào giỏ" (POST tới gio-hang.php).
 * Cách thử: chi-tiet.php?id=3 ; chi-tiet.php?id=abc ; ?id=999999 ; chi-tiet.php (không id) -> đều 404.
 */
require __DIR__ . '/inc/config.php';

use App\Data\KhoSanPham;
use App\Services\DaXem;
use App\Services\GioHang;

$kho = new KhoSanPham(__DIR__ . '/data/san-pham.json');

$id = GioHang::idHopLe($_GET['id'] ?? null);       // filter_var(..., FILTER_VALIDATE_INT, min_range 1)
$sp = $id !== null ? $kho->timTheoId($id) : null;

if ($sp === null) {                                // id thiếu, sai kiểu hoặc không có
    require __DIR__ . '/404.php';                  // 404.php tự đặt mã 404, dùng chung header/footer
    exit;
}

// Đã xem gần đây: setcookie() gửi header nên phải gọi trước mọi output
DaXem::ghi($sp->id);

$lienQuan = $kho->lienQuan($sp, 3);

$tieuDe = $sp->ten;
$trang = 'danh-sach';
$moTa = 'Chi tiết, thông số giám định và chứng thư của ' . $sp->ten . '.';
$jsThem = ['js/nut-yeu-thich.js'];
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
                    <img src="images/<?= e($sp->anh) ?>" alt="<?= e($sp->ten) ?>">
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
                <h2>Thông tin thêm</h2>
                <p><strong>Mã sản phẩm:</strong> <?= e($sp->ma) ?></p>
                <p><strong>Danh mục:</strong> <?= e($sp->tenDanhMuc()) ?></p>
                <p><strong>Xuất xứ:</strong> <?= e($sp->xuatXu) ?></p>
                <p><strong>Tình trạng:</strong> <?= e($sp->kiemDinh) ?></p>
                <p class="gia-san-pham"><strong>Giá:</strong> <?= e(vnd($sp->gia)) ?></p>

                <form class="form-gio" method="post" action="gio-hang.php">
                    <input type="hidden" name="hanhdong" value="them">
                    <input type="hidden" name="id" value="<?= $sp->id ?>">
                    <label for="so-luong">Số lượng (1–<?= GioHang::SL_TOI_DA ?>)</label>
                    <input id="so-luong" name="sl" type="number" min="1" max="<?= GioHang::SL_TOI_DA ?>" value="1" required>
                    <button type="submit" class="nut-chinh">Thêm vào giỏ</button>
                </form>

                <button class="nut-yeu-thich" type="button" data-yeuthich="<?= $sp->id ?>" aria-pressed="false">♡ Thêm vào yêu thích</button>

                <h3>Sản phẩm liên quan</h3>
                <ul>
<?php foreach ($lienQuan as $lq): ?>
                    <li><a href="chi-tiet.php?id=<?= $lq->id ?>"><?= e($lq->ten) ?></a></li>
<?php endforeach; ?>
                </ul>
            </aside>
        </div>
    </main>
<?php require __DIR__ . '/inc/footer.php'; ?>

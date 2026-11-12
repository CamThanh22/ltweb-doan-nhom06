<?php
/**
 * quan-tri.php - Trang quản trị thực thể chính (phải có quyền quan_tri):
 * 1) Danh sách sản phẩm ngọc quý có phân trang, liên kết Thêm mới, Sửa, và biểu mẫu POST Xóa (có hỏi lại).
 * 2) Danh sách các liên hệ / yêu cầu thẩm định đã nhận từ bảng lien_he (mới nhất trước) kèm ảnh đính kèm.
 * Cách thử: đăng nhập admin@ued.vn (matkhau123) -> mở quan-tri.php; thử bằng tài khoản khách -> nhận mã 403.
 */
require __DIR__ . '/inc/config.php';

use App\Data\KhoLienHe;
use App\Data\KhoSanPham;

yeuCauQuyen('quan_tri');

const NHAN_LOAI_NGOC = [
    'phithuy'  => 'Ngọc phỉ thúy (Jadeite)',
    'camthach' => 'Ngọc cẩm thạch',
    'hoadien'  => 'Ngọc bích Hòa Điền (Nephrite)',
    'khac'     => 'Loại đá quý khác',
];

$khoSp    = new KhoSanPham(db());
$moiTrang = 8;
$tongSp   = $khoSp->dem([]);
$soTrang  = max(1, (int) ceil($tongSp / $moiTrang));
$hienTai  = filter_var($_GET['trang'] ?? 1, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1, 'default' => 1],
]);
$hienTai  = min((int) $hienTai, $soTrang);
$dsSp     = $khoSp->trang([], 'moi', $moiTrang, ($hienTai - 1) * $moiTrang);

$khoLh  = new KhoLienHe(db());
$tongLh = $khoLh->dem();
$dsLh   = $khoLh->moiNhat(30);

$tieuDe = 'Quản trị sản phẩm và liên hệ';
$trang  = '';
require __DIR__ . '/inc/header.php';
?>
    <main id="noi-dung">
        <h1>Bảng điều khiển Quản trị JadeHub</h1>
<?php require __DIR__ . '/inc/thong-bao.php'; ?>

        <section>
            <h2>1. Quản trị sản phẩm ngọc quý (<?= $tongSp ?> sản phẩm)</h2>
            <p>
                <a href="them-san-pham.php" class="nut-chinh">+ Thêm sản phẩm mới</a> ·
                <a href="don-hang.php" class="nut-phu">Quản lý mọi đơn hàng</a>
            </p>

            <div class="khung-bang">
                <table>
                    <caption>Bảng danh sách sản phẩm ngọc quý (trang <?= $hienTai ?>/<?= $soTrang ?>)</caption>
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Ảnh</th>
                            <th scope="col">Mã</th>
                            <th scope="col">Tên sản phẩm</th>
                            <th scope="col">Danh mục</th>
                            <th scope="col">Giá niêm yết</th>
                            <th scope="col">Tồn kho</th>
                            <th scope="col">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
<?php foreach ($dsSp as $sp): ?>
                        <tr>
                            <td><?= $sp->id ?></td>
                            <td><img src="<?= e($sp->duongDanAnh()) ?>" alt="" width="56" height="42" loading="lazy"></td>
                            <td><strong><?= e($sp->ma) ?></strong></td>
                            <td><a href="chi-tiet.php?id=<?= $sp->id ?>"><?= e($sp->ten) ?></a></td>
                            <td><?= e($sp->tenDanhMuc()) ?></td>
                            <td><?= e(vnd($sp->gia)) ?></td>
                            <td><?= $sp->tonKho ?></td>
                            <td>
                                <a href="sua-san-pham.php?id=<?= $sp->id ?>" class="nut-phu">Sửa</a>
                                <form method="post" action="xoa-san-pham.php" style="display:inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa sản phẩm #<?= $sp->id ?>?');">
                                    <input type="hidden" name="id" value="<?= $sp->id ?>">
                                    <button type="submit" class="nut-phu">Xóa</button>
                                </form>
                            </td>
                        </tr>
<?php endforeach; ?>
                    </tbody>
                </table>
            </div>

<?php if ($soTrang > 1): ?>
            <nav class="phan-trang" aria-label="Phân trang quản trị sản phẩm">
<?php for ($i = 1; $i <= $soTrang; $i++): ?>
                <a href="?trang=<?= $i ?>"<?= $i === $hienTai ? ' aria-current="page"' : '' ?>><?= $i ?></a>
<?php endfor; ?>
            </nav>
<?php endif; ?>
        </section>

        <section id="ds-lien-he-nhan">
            <h2>2. Liên hệ &amp; yêu cầu thẩm định đã nhận (<?= $tongLh ?> liên hệ)</h2>
<?php if (!$dsLh): ?>
            <p>Chưa có liên hệ nào. Hãy gửi thử ở trang <a href="lien-he.php">Liên hệ</a>.</p>
<?php endif; ?>
            <div class="ds-lien-he">
<?php foreach ($dsLh as $lh): ?>
                <article class="the-lien-he">
                    <h3>#<?= (int) $lh['id'] ?> — <?= e((string) $lh['ho_ten']) ?></h3>
                    <p><small>Thời gian gửi: <?= e((string) $lh['thoi_gian']) ?></small></p>
                    <p>Email: <?= e((string) $lh['email']) ?> · Điện thoại: <?= e((string) $lh['sdt']) ?></p>
                    <p>Loại ngọc: <?= e(NHAN_LOAI_NGOC[$lh['loai_ngoc'] ?? ''] ?? '—') ?> · Số lượng: <?= (int) $lh['so_luong'] ?> · Ngày hẹn: <?= e((string) $lh['ngay_hen']) ?></p>
<?php if (($lh['ghi_chu'] ?? '') !== '' && $lh['ghi_chu'] !== null): ?>
                    <p class="ghi-chu"><?= nl2br(e((string) $lh['ghi_chu'])) ?></p>
<?php endif; ?>
<?php if (KhoLienHe::tenAnhHopLe($lh['anh'] ?? null)): ?>
                    <p><a href="uploads/<?= e((string) $lh['anh']) ?>"><img src="uploads/<?= e((string) $lh['anh']) ?>" alt="Ảnh đính kèm của <?= e((string) $lh['ho_ten']) ?>" width="160" loading="lazy"></a></p>
<?php endif; ?>
                </article>
<?php endforeach; ?>
            </div>
        </section>
    </main>
<?php require __DIR__ . '/inc/footer.php'; ?>

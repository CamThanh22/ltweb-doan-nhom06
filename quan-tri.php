<?php
/**
 * quan-tri.php - Trang quản trị (phải đăng nhập): liệt kê các liên hệ đã nhận kèm ảnh, mới nhất trước.
 * inc/bao-ve.php chuyển người chưa đăng nhập về dang-nhap.php. Mọi nội dung người dùng gửi đều in qua e().
 * Cách thử: gửi vài liên hệ (có ảnh) ở lien-he.php rồi mở trang này sau khi đăng nhập.
 */
require __DIR__ . '/inc/config.php';
require __DIR__ . '/inc/bao-ve.php';

use App\Data\KhoLienHe;

const NHAN_LOAI_NGOC = [
    'phithuy' => 'Ngọc phỉ thúy (Jadeite)', 'camthach' => 'Ngọc cẩm thạch',
    'hoadien' => 'Ngọc bích Hòa Điền (Nephrite)', 'khac' => 'Loại đá quý khác',
];

$kho = new KhoLienHe(__DIR__ . '/storage/lien-he.jsonl');
$tong = $kho->dem();
$ds = $kho->moiNhat(50);                 // mới nhất đứng đầu

$tieuDe = 'Quản trị - Liên hệ đã nhận';
$trang = '';
require __DIR__ . '/inc/header.php';
?>
    <main id="noi-dung">
        <h1>Quản trị: liên hệ đã nhận</h1>
<?php require __DIR__ . '/inc/thong-bao.php'; ?>
        <p>Tổng cộng <strong><?= $tong ?></strong> liên hệ<?= $tong > count($ds) ? ' (hiện ' . count($ds) . ' liên hệ mới nhất)' : '' ?>.</p>
<?php if (!$ds): ?>
        <p>Chưa có liên hệ nào. Hãy gửi thử ở trang <a href="lien-he.php">Liên hệ</a>.</p>
<?php endif; ?>
        <div class="ds-lien-he">
<?php foreach ($ds as $lh): ?>
            <article class="the-lien-he">
                <h2><?= e((string) ($lh['hoTen'] ?? '')) ?></h2>
                <p><small><?= e((string) ($lh['thoiGian'] ?? '')) ?></small></p>
                <p>Email: <?= e((string) ($lh['email'] ?? '')) ?> · Điện thoại: <?= e((string) ($lh['sdt'] ?? '')) ?></p>
                <p>Loại ngọc: <?= e(NHAN_LOAI_NGOC[$lh['loaiNgoc'] ?? ''] ?? '—') ?> · Số lượng: <?= e((string) ($lh['soLuong'] ?? '')) ?> · Ngày hẹn: <?= e((string) ($lh['ngayHen'] ?? '')) ?></p>
<?php if (($lh['ghiChu'] ?? '') !== ''): ?>
                <p class="ghi-chu"><?= nl2br(e((string) $lh['ghiChu'])) ?></p>
<?php endif; ?>
<?php if (KhoLienHe::tenAnhHopLe($lh['anh'] ?? null)): ?>
                <p><a href="uploads/<?= e($lh['anh']) ?>"><img src="uploads/<?= e($lh['anh']) ?>" alt="Ảnh đính kèm của <?= e((string) ($lh['hoTen'] ?? '')) ?>" width="160" loading="lazy"></a></p>
<?php endif; ?>
            </article>
<?php endforeach; ?>
        </div>
    </main>
<?php require __DIR__ . '/inc/footer.php'; ?>

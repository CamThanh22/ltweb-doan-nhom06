<?php
/**
 * gioi-thieu.php - Giới thiệu đội ngũ: danh sách thành viên sinh từ mảng PHP (foreach), mỗi người
 * có liên kết tới trang cá nhân thanhvien/<MSSV>_<ten>/gioithieu.php. Dùng chung header/footer.
 * Cách thử: mở trang, bấm từng liên kết hồ sơ cá nhân.
 */
require __DIR__ . '/inc/config.php';

$thanhVien = [
    ['ten' => 'Trần Thị Cẩm Thanh', 'vaiTro' => 'Trưởng nhóm', 'msv' => '3120224133', 'thuMuc' => '3120224133_thanh', 'ten_ngan' => 'Cẩm Thanh'],
    ['ten' => 'Nguyễn Thị Bích Hạnh', 'vaiTro' => 'Thành viên', 'msv' => '3120224050', 'thuMuc' => '3120224050_hanh', 'ten_ngan' => 'Bích Hạnh'],
    ['ten' => 'Nguyễn Nhật Ánh Hồng', 'vaiTro' => 'Thành viên', 'msv' => '3120224063', 'thuMuc' => '3120224063_hong', 'ten_ngan' => 'Ánh Hồng'],
    ['ten' => 'Huỳnh Trần Phương Trinh', 'vaiTro' => 'Thành viên', 'msv' => '3120124031', 'thuMuc' => '3120124031_trinh', 'ten_ngan' => 'Phương Trinh'],
    ['ten' => 'Ladisa Bouakeo', 'vaiTro' => 'Thành viên', 'msv' => '3120124038', 'thuMuc' => '3120124038_bouakeo', 'ten_ngan' => 'Ladisa Bouakeo'],
];

$tieuDe = 'Giới thiệu đội ngũ phát triển';
$trang = 'gioi-thieu';
$moTa = 'Đội ngũ phát triển dự án đồ án JadeHub - Nhóm 06, Khoa Toán - Tin, Trường Đại học Sư phạm - ĐHĐN.';
require __DIR__ . '/inc/header.php';
?>
    <main id="noi-dung">
<?php require __DIR__ . '/inc/thong-bao.php'; ?>
        <h1>Đội ngũ kỹ thuật Nhóm 06</h1>
        <section class="gioi-thieu-du-an">
            <h2>Mục tiêu dự án JadeHub</h2>
            <p>
                Dự án được xây dựng trong khuôn khổ học phần Thiết kế và Lập trình Web nhằm kết hợp
                công nghệ web hiện đại với giải pháp thương mại điện tử chuyên sâu cho thị trường đá quý.
            </p>
        </section>
        <section>
            <h2>Danh sách thành viên nhóm phát triển</h2>
            <p>Chọn vào tên thành viên để xem trang hồ sơ cá nhân và thời khóa biểu học tập chi tiết:</p>
            <ul class="danh-sach-thanh-vien">
<?php foreach ($thanhVien as $tv): ?>
                <li class="the-thanh-vien">
                    <strong><?= e($tv['ten']) ?></strong>
                    <span><?= e($tv['vaiTro']) ?> - <?= e($tv['msv']) ?></span>
                    <a href="thanhvien/<?= e($tv['thuMuc']) ?>/gioithieu.php">Xem hồ sơ cá nhân của <?= e($tv['ten_ngan']) ?> →</a>
                </li>
<?php endforeach; ?>
            </ul>
        </section>
    </main>
<?php require __DIR__ . '/inc/footer.php'; ?>

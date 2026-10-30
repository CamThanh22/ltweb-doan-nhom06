<?php
/**
 * 403.php - trang báo lỗi phân quyền (403 Forbidden) thân thiện, dùng chung header/footer.
 * Được require từ yeuCauQuyen() khi tài khoản đã đăng nhập nhưng không có vai trò cần thiết.
 * Cách thử: đăng nhập bằng tài khoản khách (lan@ued.vn) rồi mở quan-tri.php -> nhận mã 403.
 */
require_once __DIR__ . '/inc/config.php';

if (!headers_sent()) {
    http_response_code(403);
}
$tieuDe = 'Không có quyền truy cập';
$trang = '';
$moTa = 'Tài khoản của bạn không có quyền thực hiện thao tác hoặc xem trang này trên JadeHub.';
require __DIR__ . '/inc/header.php';
?>
    <main id="noi-dung">
        <h1>403 — Không có quyền truy cập</h1>
        <p>Xin lỗi, tài khoản của bạn không có quyền truy cập khu vực này (yêu cầu quyền quản trị viên).</p>
        <p>
            <a href="<?= e($goc ?? '') ?>index.php">← Về trang chủ</a> ·
            <a href="<?= e($goc ?? '') ?>danh-sach.php">Xem danh mục ngọc quý</a>
        </p>
    </main>
<?php require __DIR__ . '/inc/footer.php'; ?>

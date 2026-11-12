<?php
/**
 * inc/xac-thuc.php - Xác thực và phân quyền người dùng với bảng nguoi_dung và ghi_nho (slide 38-43).
 * Session chỉ giữ $_SESSION['uid']; thông tin và vai_tro luôn đọc lại từ CSDL ở mỗi request.
 * Cách thử: đăng nhập khách rồi mở quan-tri.php -> nhận mã 403; chưa đăng nhập mở don-hang.php -> về dang-nhap.php.
 */
declare(strict_types=1);

/** Đăng nhập người dùng: cấp mã phiên mới và chỉ lưu uid vào session (slide 41). */
function dangNhap(int $uid): void
{
    session_regenerate_id(true);
    $_SESSION['uid'] = $uid;
}

/** Lấy thông tin người đang đăng nhập từ CSDL (mỗi request chỉ truy vấn 1 lần), hoặc null. */
function nguoiDung(): ?array
{
    static $nd = false;
    static $uidDaDoc = null;

    $uid = filter_var($_SESSION['uid'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($uid === false) {
        $uid = tuDangNhapTuCookie();
    }
    if ($uid === 0) {
        return null;
    }
    if ($nd === false || $uidDaDoc !== $uid) {
        $st = db()->prepare('SELECT id, email, ho_ten, vai_tro FROM nguoi_dung WHERE id = ?');
        $st->execute([$uid]);
        $dong = $st->fetch();
        $nd = $dong !== false ? $dong : null;
        $uidDaDoc = $uid;
        if ($nd === null) {
            unset($_SESSION['uid']);
        }
    }
    return $nd;
}

/** Kiểm tra người hiện tại có phải quản trị viên không. */
function laQuanTri(): bool
{
    return (nguoiDung()['vai_tro'] ?? '') === 'quan_tri';
}

/** Bắt buộc đã đăng nhập; nếu chưa thì chuyển tới dang-nhap.php và dừng script. */
function yeuCauDangNhap(string $goc = ''): array
{
    $nd = nguoiDung();
    if ($nd === null) {
        header('Location: ' . $goc . 'dang-nhap.php');
        exit;
    }
    header('Cache-Control: no-store');
    return $nd;
}

/** Bắt buộc có đúng vai trò (ví dụ 'quan_tri'); sai vai trò thì trả mã 403 và hiện 403.php (slide 42). */
function yeuCauQuyen(string $vaiTro, string $goc = ''): array
{
    $nd = yeuCauDangNhap($goc);
    if (($nd['vai_tro'] ?? '') !== $vaiTro) {
        http_response_code(403);
        require __DIR__ . '/../403.php';
        exit;
    }
    return $nd;
}

/** Tạo mã ghi nhớ đăng nhập selector:validator (CSDL chỉ lưu sha256 của validator, slide 43). */
function ghiNhoDangNhap(int $uid): void
{
    $sel = bin2hex(random_bytes(8));   // 16 ký tự hex
    $val = bin2hex(random_bytes(32));  // 64 ký tự hex
    db()->prepare('INSERT INTO ghi_nho (selector, validator_hash, nguoi_dung_id, het_han)
                   VALUES (?, ?, ?, NOW() + INTERVAL 30 DAY)')
        ->execute([$sel, hash('sha256', $val), $uid]);
    setcookie('ghi_nho', "$sel:$val", [
        'expires'  => time() + 30 * 24 * 3600,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
}

/** Xóa cookie và bản ghi ghi nhớ đăng nhập khi đăng xuất. */
function xoaGhiNhoDangNhap(): void
{
    $cookie = $_COOKIE['ghi_nho'] ?? '';
    if (is_string($cookie) && str_contains($cookie, ':')) {
        [$sel] = explode(':', $cookie, 2);
        if (preg_match('/^[a-f0-9]{16}\z/', $sel) === 1) {
            try {
                db()->prepare('DELETE FROM ghi_nho WHERE selector = ?')->execute([$sel]);
            } catch (Throwable) {
            }
        }
    }
    setcookie('ghi_nho', '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

/** Khôi phục phiên từ cookie ghi_nho nếu hợp lệ và còn hạn (slide 43). */
function tuDangNhapTuCookie(): int
{
    $cookie = $_COOKIE['ghi_nho'] ?? '';
    if (!is_string($cookie) || !str_contains($cookie, ':')) {
        return 0;
    }
    [$sel, $val] = explode(':', $cookie, 2);
    if (preg_match('/^[a-f0-9]{16}\z/', $sel) !== 1 || preg_match('/^[a-f0-9]{64}\z/', $val) !== 1) {
        return 0;
    }
    try {
        $st = db()->prepare('SELECT validator_hash, nguoi_dung_id FROM ghi_nho
                             WHERE selector = ? AND het_han > NOW()');
        $st->execute([$sel]);
        $dong = $st->fetch();
        if ($dong && hash_equals((string) $dong['validator_hash'], hash('sha256', $val))) {
            $uid = (int) $dong['nguoi_dung_id'];
            db()->prepare('DELETE FROM ghi_nho WHERE selector = ?')->execute([$sel]);
            dangNhap($uid);
            ghiNhoDangNhap($uid);
            return $uid;
        }
    } catch (Throwable) {
    }
    return 0;
}

<?php
/**
 * inc/ham.php - Hàm tiện ích dùng chung, được inc/config.php nạp sớm.
 * e(): thoát HTML (mọi dữ liệu động in ra HTML đều qua hàm này); vnd(): định dạng tiền.
 * traJson(): trả phản hồi JSON kèm mã HTTP cho REST API (slide 48).
 * datFlash()/layFlash(): thông báo hiện đúng một lần qua $_SESSION.
 * Cách thử: mở bất kỳ trang nào; gọi api/san-pham.php?q=nhan để kiểm tra traJson().
 */
declare(strict_types=1);

/** Thoát ký tự đặc biệt của HTML (kể cả dấu nháy) để chống XSS. */
function e(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Định dạng số tiền theo kiểu Việt Nam: 18500000 -> "18.500.000 VNĐ". */
function vnd(int|float $so): string
{
    return number_format((float) $so, 0, ',', '.') . ' VNĐ';
}

/** Trả phản hồi JSON với mã trạng thái HTTP và kết thúc script (slide 48). */
function traJson(mixed $duLieu, int $ma = 200): never
{
    if (!headers_sent()) {
        http_response_code($ma);
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode($duLieu, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Bỏ dấu tiếng Việt và hạ chữ thường: "Nhẫn Phỉ Thúy" -> "nhan phi thuy".
 */
function boDau(string $s): string
{
    static $bang = null;
    if ($bang === null) {
        $bang = [
            'à' => 'a', 'á' => 'a', 'ạ' => 'a', 'ả' => 'a', 'ã' => 'a', 'â' => 'a',
            'ầ' => 'a', 'ấ' => 'a', 'ậ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a', 'ă' => 'a',
            'ằ' => 'a', 'ắ' => 'a', 'ặ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a', 'è' => 'e',
            'é' => 'e', 'ẹ' => 'e', 'ẻ' => 'e', 'ẽ' => 'e', 'ê' => 'e', 'ề' => 'e',
            'ế' => 'e', 'ệ' => 'e', 'ể' => 'e', 'ễ' => 'e', 'ì' => 'i', 'í' => 'i',
            'ị' => 'i', 'ỉ' => 'i', 'ĩ' => 'i', 'ò' => 'o', 'ó' => 'o', 'ọ' => 'o',
            'ỏ' => 'o', 'õ' => 'o', 'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ộ' => 'o',
            'ổ' => 'o', 'ỗ' => 'o', 'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ợ' => 'o',
            'ở' => 'o', 'ỡ' => 'o', 'ù' => 'u', 'ú' => 'u', 'ụ' => 'u', 'ủ' => 'u',
            'ũ' => 'u', 'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ự' => 'u', 'ử' => 'u',
            'ữ' => 'u', 'ỳ' => 'y', 'ý' => 'y', 'ỵ' => 'y', 'ỷ' => 'y', 'ỹ' => 'y',
            'đ' => 'd',
        ];
    }
    if (class_exists('Normalizer')) {
        $chuan = Normalizer::normalize($s, Normalizer::FORM_C);
        if ($chuan !== false) {
            $s = $chuan;
        }
    }
    return strtr(mb_strtolower($s, 'UTF-8'), $bang);
}

/**
 * Đọc một ô dạng chuỗi từ $_GET/$_POST: bỏ khoảng trắng hai đầu và ký tự điều khiển.
 */
function layChuoi(array $nguon, string $khoa): string
{
    $v = $nguon[$khoa] ?? '';
    if (!is_string($v)) {
        return '';
    }
    $v = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $v) ?? '';
    return trim(str_replace("\r\n", "\n", $v));
}

/** Lưu thông báo để trang kế tiếp hiện (loai: 'ok' hoặc 'loi'). */
function datFlash(string $loai, string $noiDung): void
{
    $_SESSION['flash'] = ['loai' => $loai, 'nd' => $noiDung];
}

/** Lấy rồi xóa thông báo: gọi lần hai sẽ không còn gì (hiện đúng một lần). */
function layFlash(): ?array
{
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    if (is_string($f) && $f !== '') {
        return ['loai' => 'ok', 'nd' => $f];
    }
    return is_array($f) && isset($f['loai'], $f['nd']) ? $f : null;
}

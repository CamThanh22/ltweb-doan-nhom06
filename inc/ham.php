<?php
/**
 * inc/ham.php - Hàm tiện ích dùng chung, được inc/config.php nạp sớm.
 * e(): thoát HTML (mọi dữ liệu động in ra HTML đều qua hàm này); vnd(): định dạng tiền.
 * boDau(): bỏ dấu tiếng Việt để tìm kiếm; layChuoi(): đọc ô nhập an toàn từ $_GET/$_POST.
 * datFlash()/layFlash(): thông báo hiện đúng một lần qua $_SESSION.
 * Cách thử: mở bất kỳ trang nào; chạy php tests/kiem-thu.php để thử riêng các hàm này.
 */

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

/**
 * Bỏ dấu tiếng Việt và hạ chữ thường: "Nhẫn Phỉ Thúy" -> "nhan phi thuy".
 * Dùng để so khớp từ khóa không phân biệt dấu (kiểu mb_stripos).
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
            $s = $chuan; // gộp dấu rời về dạng dựng sẵn để strtr khớp
        }
    }
    return strtr(mb_strtolower($s, 'UTF-8'), $bang);
}

/**
 * Đọc một ô dạng chuỗi từ $_GET/$_POST: bỏ khoảng trắng hai đầu và ký tự điều khiển.
 * Nếu ô thiếu hoặc bị gửi dạng mảng (?q[]=x) thì trả về '' thay vì gây TypeError.
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
    return is_array($f) && isset($f['loai'], $f['nd']) ? $f : null;
}

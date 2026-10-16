<?php
/**
 * src/Services/TaiAnh.php - nhận ảnh tải lên an toàn: giới hạn 2 MB, kiểm tra KIỂU THẬT bằng finfo
 * (không tin đuôi tệp hay Content-Type của trình duyệt), chỉ JPG/PNG/WebP, đặt tên ngẫu nhiên.
 * kiemTra() chỉ kiểm tra; luu() mới chuyển tệp vào thư mục đích (gọi sau khi mọi ô khác đã hợp lệ).
 * Cách thử: lien-he.php, đính kèm tệp .jpg thực chất là văn bản -> bị từ chối, uploads/ không có tệp mới.
 */
declare(strict_types=1);

namespace App\Services;

use RuntimeException;

class TaiAnh
{
    public const TOI_DA = 2 * 1024 * 1024; // 2 MB
    private const CHO_PHEP = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    public function __construct(private string $thuMuc)
    {
    }

    /** Người dùng có chọn tệp không (UPLOAD_ERR_NO_FILE nghĩa là không chọn: không phải lỗi). */
    public static function coTep(?array $f): bool
    {
        return is_array($f) && ($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    }

    /** Trả về thông báo lỗi, hoặc null nếu không chọn tệp / tệp hợp lệ. */
    public static function kiemTra(?array $f): ?string
    {
        if ($f === null || !self::coTep($f)) {
            return null;
        }
        if (!is_int($f['error'] ?? null) || !is_string($f['tmp_name'] ?? null)) {
            return 'Dữ liệu tệp tải lên không hợp lệ.'; // ví dụ gửi anhngoc[] dạng mảng
        }
        if ($f['error'] === UPLOAD_ERR_INI_SIZE || $f['error'] === UPLOAD_ERR_FORM_SIZE) {
            return 'Ảnh lớn hơn 2 MB, vui lòng chọn ảnh nhỏ hơn.';
        }
        if ($f['error'] !== UPLOAD_ERR_OK) {
            return 'Không tải được ảnh lên (mã lỗi ' . $f['error'] . '), vui lòng thử lại.';
        }
        $tmp = $f['tmp_name'];
        if (!is_uploaded_file($tmp)) {
            return 'Tệp tải lên không hợp lệ.';
        }
        if (filesize($tmp) > self::TOI_DA) {
            return 'Ảnh lớn hơn 2 MB, vui lòng chọn ảnh nhỏ hơn.';
        }
        if (!class_exists('finfo')) {
            throw new RuntimeException('Thiếu extension fileinfo của PHP.');
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($tmp);
        if ($mime === false || !isset(self::CHO_PHEP[$mime])) {
            return 'Chỉ nhận ảnh JPG, PNG hoặc WebP.';
        }
        // Kiểm tra thêm: tệp phải đọc được như một ảnh thật (@ chỉ để chặn cảnh báo "Read error" với ảnh cắt cụt)
        if (@getimagesize($tmp) === false) {
            return 'Chỉ nhận ảnh JPG, PNG hoặc WebP.';
        }
        return null;
    }

    /** Lưu ảnh đã qua kiemTra() với tên ngẫu nhiên; trả về tên tệp (không kèm đường dẫn). */
    public function luu(array $f): string
    {
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file((string) $f['tmp_name']);
        $duoi = self::CHO_PHEP[$mime] ?? null;
        if ($duoi === null) {
            throw new RuntimeException('Kiểu ảnh không được phép.');
        }
        if (!is_dir($this->thuMuc) && !mkdir($this->thuMuc, 0775, true) && !is_dir($this->thuMuc)) {
            throw new RuntimeException('Không tạo được thư mục uploads.');
        }
        $ten = bin2hex(random_bytes(16)) . '.' . $duoi;
        if (!move_uploaded_file((string) $f['tmp_name'], rtrim($this->thuMuc, '/\\') . '/' . $ten)) {
            throw new RuntimeException('Không lưu được ảnh tải lên.');
        }
        return $ten;
    }
}

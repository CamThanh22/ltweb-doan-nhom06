<?php
/**
 * src/Data/KhoLienHe.php - kho liên hệ: storage/lien-he.jsonl (mỗi dòng một liên hệ).
 * Kế thừa KhoJsonl; Chương 6 thay bằng bảng lien_he của MySQL (chỉ sửa tệp này).
 * Cách thử: gửi liên hệ ở lien-he.php rồi xem ở quan-tri.php (mới nhất đứng đầu).
 */
declare(strict_types=1);

namespace App\Data;

class KhoLienHe extends KhoJsonl
{
    /** Chỉ chấp nhận tên tệp ảnh do TaiAnh tạo ra (32 ký tự hex + đuôi), dùng khi in ra trang. */
    public static function tenAnhHopLe(?string $ten): bool
    {
        return $ten !== null && preg_match('/^[a-f0-9]{32}\.(jpg|png|webp)\z/', $ten) === 1;
    }
}

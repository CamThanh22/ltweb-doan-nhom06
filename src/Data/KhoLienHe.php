<?php
/**
 * src/Data/KhoLienHe.php - lớp truy cập dữ liệu cho bảng lien_he của MySQL (nhận PDO qua constructor).
 * Thay thế hoàn toàn KhoJsonl của Bài tập 5; các trang lien-he.php và quan-tri.php chỉ đổi đối số khởi tạo.
 * Cách thử: gửi liên hệ ở lien-he.php rồi mở quan-tri.php xem danh sách liên hệ mới nhất trước.
 */
declare(strict_types=1);

namespace App\Data;

use PDO;

class KhoLienHe
{
    public function __construct(private PDO $db)
    {
    }

    /** Thêm một yêu cầu thẩm định / liên hệ mới vào bảng lien_he bằng prepared statement. */
    public function them(array $banGhi): int
    {
        $st = $this->db->prepare(
            'INSERT INTO lien_he (ho_ten, email, sdt, loai_ngoc, so_luong, ngay_hen, ghi_chu, anh)
             VALUES (:ho_ten, :email, :sdt, :loai_ngoc, :so_luong, :ngay_hen, :ghi_chu, :anh)'
        );
        $st->execute([
            'ho_ten'    => (string) ($banGhi['hoTen'] ?? $banGhi['ho_ten'] ?? ''),
            'email'     => (string) ($banGhi['email'] ?? ''),
            'sdt'       => (string) ($banGhi['sdt'] ?? ''),
            'loai_ngoc' => (string) ($banGhi['loaiNgoc'] ?? $banGhi['loai_ngoc'] ?? 'phithuy'),
            'so_luong'  => (int) ($banGhi['soLuong'] ?? $banGhi['so_luong'] ?? 1),
            'ngay_hen'  => (string) ($banGhi['ngayHen'] ?? $banGhi['ngay_hen'] ?? date('Y-m-d')),
            'ghi_chu'   => ($banGhi['ghiChu'] ?? $banGhi['ghi_chu'] ?? '') !== ''
                           ? (string) ($banGhi['ghiChu'] ?? $banGhi['ghi_chu'])
                           : null,
            'anh'       => $banGhi['anh'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    /** Lấy $n liên hệ mới nhất (mới nhất đứng đầu). */
    public function moiNhat(int $n = 50): array
    {
        $st = $this->db->prepare(
            'SELECT id, ho_ten, email, sdt, loai_ngoc, so_luong, ngay_hen, ghi_chu, anh, thoi_gian
             FROM lien_he
             ORDER BY thoi_gian DESC, id DESC
             LIMIT ?'
        );
        $st->execute([max(1, $n)]);
        return $st->fetchAll();
    }

    /** Tổng số liên hệ đã nhận. */
    public function dem(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM lien_he')->fetchColumn();
    }

    /** Chỉ chấp nhận tên tệp ảnh do TaiAnh tạo ra (32 ký tự hex + đuôi), dùng khi in ra trang. */
    public static function tenAnhHopLe(?string $ten): bool
    {
        return $ten !== null && preg_match('/^[a-f0-9]{32}\.(jpg|png|webp)\z/', $ten) === 1;
    }
}

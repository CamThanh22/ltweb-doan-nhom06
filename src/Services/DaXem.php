<?php
/**
 * src/Services/DaXem.php - cookie "da_xem": nhớ tối đa 4 sản phẩm xem gần nhất (mới nhất đứng đầu).
 * ghi() phải gọi TRƯỚC mọi output (setcookie gửi header). doc() KHÔNG tin cookie: kiểm tra từng id.
 * Cách thử: xem vài chi-tiet.php?id=..., về index.php thấy khối "Đã xem gần đây"; sửa cookie thành abc,-1,999999.
 */
declare(strict_types=1);

namespace App\Services;

use App\Data\KhoSanPham;
use App\Models\SanPham;

class DaXem
{
    public const TEN = 'da_xem';
    public const TOI_DA = 4;

    /** Ghi sản phẩm vừa xem lên đầu danh sách (bỏ trùng, giữ tối đa 4). */
    public static function ghi(int $id): void
    {
        $moi = array_values(array_unique([$id, ...self::docId()]));
        setcookie(self::TEN, implode(',', array_slice($moi, 0, self::TOI_DA)), [
            'expires'  => time() + 30 * 24 * 3600, // 30 ngày
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        ]);
    }

    /**
     * Các sản phẩm đã xem; id lạ (abc, âm, quá lớn, không có trong dữ liệu) bị bỏ qua.
     *
     * @return SanPham[]
     */
    public static function doc(KhoSanPham $kho): array
    {
        $ds = [];
        foreach (self::docId() as $id) {
            $sp = $kho->timTheoId($id);
            if ($sp !== null) {
                $ds[] = $sp;
            }
        }
        return $ds;
    }

    /** Đọc cookie thành danh sách id số nguyên dương hợp lệ, không trùng. */
    private static function docId(): array
    {
        $tho = $_COOKIE[self::TEN] ?? '';
        if (!is_string($tho) || $tho === '' || strlen($tho) > 100) {
            return [];
        }
        $kq = [];
        foreach (explode(',', $tho) as $x) {
            $id = filter_var($x, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 999999]]);
            if ($id !== false && !in_array($id, $kq, true)) {
                $kq[] = $id;
            }
        }
        return array_slice($kq, 0, self::TOI_DA);
    }
}

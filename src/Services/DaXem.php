<?php
/**
 * src/Services/DaXem.php - cookie "da_xem": nhớ tối đa 4 sản phẩm xem gần nhất (mới nhất đứng đầu).
 * doc() lấy toàn bộ sản phẩm đã xem bằng MỘT câu SQL WHERE id IN (...) qua KhoSanPham::timTheoNhieuId()
 * để tránh lỗi truy vấn trong vòng lặp (N+1 query, slide 21 & 33), đồng thời giữ nguyên thứ tự xem gần nhất.
 * Cách thử: xem chi-tiet.php?id=2 và ?id=5 rồi về index.php; sửa cookie da_xem thành abc,-1,999999.
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
            'expires'  => time() + 30 * 24 * 3600,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        ]);
    }

    /**
     * Lấy các sản phẩm đã xem bằng MỘT câu truy vấn WHERE id IN (...), giữ đúng thứ tự mới nhất trước.
     *
     * @return SanPham[]
     */
    public static function doc(KhoSanPham $kho): array
    {
        $ids = self::docId();
        if (!$ids) {
            return [];
        }
        $bangSp = $kho->timTheoNhieuId($ids);
        $ds = [];
        foreach ($ids as $id) {
            if (isset($bangSp[$id])) {
                $ds[] = $bangSp[$id];
            }
        }
        return $ds;
    }

    /** Đọc cookie thành danh sách id số nguyên dương hợp lệ, không trùng. */
    public static function docId(): array
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

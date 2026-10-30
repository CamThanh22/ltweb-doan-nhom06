<?php
/**
 * src/Services/GioHang.php - giỏ hàng bọc $_SESSION['gio'] = [id => số lượng].
 * chiTiet() lấy cả giỏ hàng bằng MỘT câu SQL WHERE id IN (?, ...) qua KhoSanPham::timTheoNhieuId()
 * để không truy vấn trong vòng lặp (slide 21 & Bảng 1 mục 1); GIÁ luôn lấy từ CSDL.
 * Cách thử: thêm vài sản phẩm ở chi-tiet.php rồi mở gio-hang.php; số món hiện ở header mọi trang.
 */
declare(strict_types=1);

namespace App\Services;

use App\Data\KhoSanPham;
use InvalidArgumentException;

class GioHang
{
    public const SL_TOI_DA = 99;
    private const KHOA = 'gio';

    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public static function idHopLe(mixed $giaTri): ?int
    {
        $id = filter_var($giaTri, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        return $id === false ? null : $id;
    }

    public static function soLuongHopLe(mixed $giaTri): ?int
    {
        $sl = filter_var($giaTri, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => self::SL_TOI_DA],
        ]);
        return $sl === false ? null : $sl;
    }

    /** Toàn bộ giỏ dưới dạng [id => số lượng], đã làm sạch kiểu dữ liệu. */
    public function tatCa(): array
    {
        $gio = $_SESSION[self::KHOA] ?? [];
        if (!is_array($gio)) {
            return [];
        }
        $sach = [];
        foreach ($gio as $id => $sl) {
            $idInt = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $slInt = filter_var($sl, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($idInt !== false && $slInt !== false) {
                $sach[(int) $idInt] = min((int) $slInt, self::SL_TOI_DA);
            }
        }
        return $sach;
    }

    public function them(int $id, int $sl): void
    {
        $this->kiemTra($id, $sl);
        $gio = $this->tatCa();
        $gio[$id] = min(($gio[$id] ?? 0) + $sl, self::SL_TOI_DA);
        $_SESSION[self::KHOA] = $gio;
    }

    public function capNhat(int $id, int $sl): bool
    {
        $this->kiemTra($id, $sl);
        $gio = $this->tatCa();
        if (!isset($gio[$id])) {
            return false;
        }
        $gio[$id] = $sl;
        $_SESSION[self::KHOA] = $gio;
        return true;
    }

    public function xoa(int $id): bool
    {
        $gio = $this->tatCa();
        if (!isset($gio[$id])) {
            return false;
        }
        unset($gio[$id]);
        $_SESSION[self::KHOA] = $gio;
        return true;
    }

    public function xoaHet(): void
    {
        $_SESSION[self::KHOA] = [];
    }

    public function coMuc(int $id): bool
    {
        return isset($this->tatCa()[$id]);
    }

    public function soMon(): int
    {
        return array_sum($this->tatCa());
    }

    /**
     * Lấy chi tiết mọi mục trong giỏ bằng MỘT câu SQL WHERE id IN (?, ...) (slide 21).
     *
     * @return array<int, array{sp: \App\Models\SanPham, sl: int, thanhTien: int}>
     */
    public function chiTiet(KhoSanPham $kho): array
    {
        $gio = $this->tatCa();
        if (!$gio) {
            return [];
        }
        $bangSp = $kho->timTheoNhieuId(array_keys($gio));
        $dong = [];
        foreach ($gio as $id => $sl) {
            if (!isset($bangSp[$id])) {
                $this->xoa($id);
                continue;
            }
            $sp = $bangSp[$id];
            $dong[] = ['sp' => $sp, 'sl' => $sl, 'thanhTien' => $sp->gia * $sl];
        }
        return $dong;
    }

    public function tongTien(KhoSanPham $kho): int
    {
        return array_sum(array_column($this->chiTiet($kho), 'thanhTien'));
    }

    private function kiemTra(int $id, int $sl): void
    {
        if ($id < 1 || $sl < 1 || $sl > self::SL_TOI_DA) {
            throw new InvalidArgumentException('id hoặc số lượng không hợp lệ.');
        }
    }
}

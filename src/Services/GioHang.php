<?php
/**
 * src/Services/GioHang.php - giỏ hàng bọc $_SESSION['gio'] = [id => số lượng].
 * Chỉ lưu id và số lượng; GIÁ luôn lấy từ KhoSanPham (không tin dữ liệu từ form).
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

    /** id hợp lệ = số nguyên dương; trả về null nếu sai (kể cả khi bị gửi dạng mảng). */
    public static function idHopLe(mixed $giaTri): ?int
    {
        $id = filter_var($giaTri, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        return $id === false ? null : $id;
    }

    /** Số lượng hợp lệ = số nguyên từ 1 đến SL_TOI_DA; ngược lại null. */
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
            if (is_int($id) && $id > 0 && is_int($sl) && $sl > 0) {
                $sach[$id] = min($sl, self::SL_TOI_DA);
            }
        }
        return $sach;
    }

    /** Thêm $sl vào số lượng đang có (tối đa SL_TOI_DA). */
    public function them(int $id, int $sl): void
    {
        $this->kiemTra($id, $sl);
        $gio = $this->tatCa();
        $gio[$id] = min(($gio[$id] ?? 0) + $sl, self::SL_TOI_DA);
        $_SESSION[self::KHOA] = $gio;
    }

    /** Đặt số lượng mới cho mục đã có trong giỏ; trả false nếu mục không có. */
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

    /** Số món trong giỏ = tổng số lượng (hiện ở header). */
    public function soMon(): int
    {
        return array_sum($this->tatCa());
    }

    /**
     * Các dòng của giỏ kèm thông tin sản phẩm và thành tiền (giá lấy từ kho dữ liệu).
     * Mục nào không còn trong dữ liệu thì tự bị gỡ khỏi giỏ.
     *
     * @return array<int, array{sp: \App\Models\SanPham, sl: int, thanhTien: int}>
     */
    public function chiTiet(KhoSanPham $kho): array
    {
        $dong = [];
        foreach ($this->tatCa() as $id => $sl) {
            $sp = $kho->timTheoId($id);
            if ($sp === null) {
                $this->xoa($id);
                continue;
            }
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

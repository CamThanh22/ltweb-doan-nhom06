<?php
/**
 * src/Data/KhoSanPham.php - lớp truy cập dữ liệu: nơi DUY NHẤT đọc data/san-pham.json.
 * Chương 6 chỉ cần viết lại lớp này bằng PDO; các trang gọi tatCa/timTheoId/timKiem không đổi.
 * Cách thử: mở danh-sach.php?q=nhan&dm=phi-thuy&sx=gia-tang; đổi tên tệp JSON để thấy lỗi 500.
 */
declare(strict_types=1);

namespace App\Data;

use App\Models\SanPham;
use JsonException;
use RuntimeException;

class KhoSanPham
{
    /** Các kiểu sắp xếp được phép (khóa trên URL => nhãn). */
    public const SAP_XEP = [
        'mac-dinh' => 'Mặc định',
        'gia-tang' => 'Giá tăng dần',
        'gia-giam' => 'Giá giảm dần',
        'ten'      => 'Tên A–Z',
    ];

    private ?array $ds = null; // mỗi request chỉ đọc tệp một lần

    public function __construct(private string $tepJson)
    {
    }

    /** @return SanPham[] */
    public function tatCa(): array
    {
        if ($this->ds === null) {
            if (!is_file($this->tepJson)) {
                throw new RuntimeException('Không có tệp dữ liệu sản phẩm: ' . $this->tepJson);
            }
            $json = file_get_contents($this->tepJson);
            if ($json === false) {
                throw new RuntimeException('Không đọc được tệp dữ liệu sản phẩm.');
            }
            try {
                $mang = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                throw new RuntimeException('Tệp dữ liệu sản phẩm không phải JSON hợp lệ.', 0, $e);
            }
            if (!is_array($mang)) {
                throw new RuntimeException('Tệp dữ liệu sản phẩm phải là một mảng.');
            }
            $this->ds = array_map(fn($d) => SanPham::tuMang(is_array($d) ? $d : []), array_values($mang));
        }
        return $this->ds;
    }

    public function timTheoId(int $id): ?SanPham
    {
        foreach ($this->tatCa() as $sp) {
            if ($sp->id === $id) {
                return $sp;
            }
        }
        return null;
    }

    /**
     * Tìm theo từ khóa (không phân biệt dấu, hoa/thường), lọc theo danh mục, sắp xếp.
     * $danhMuc và $sapXep do trang kiểm tra theo danh sách cho phép trước khi gọi.
     *
     * @return SanPham[]
     */
    public function timKiem(string $tuKhoa = '', string $danhMuc = '', string $sapXep = 'mac-dinh'): array
    {
        $ds = $this->tatCa();
        $tk = \boDau(trim($tuKhoa));

        if ($tk !== '') {
            $ds = array_filter($ds, function (SanPham $sp) use ($tk): bool {
                $kho = \boDau($sp->ten . ' ' . $sp->ma . ' ' . $sp->loai . ' ' . $sp->xuatXu);
                return mb_stripos($kho, $tk) !== false;
            });
        }
        if ($danhMuc !== '') {
            $ds = array_filter($ds, fn(SanPham $sp): bool => $sp->danhMuc === $danhMuc);
        }

        $ds = array_values($ds);
        usort($ds, match ($sapXep) {
            'gia-tang' => fn(SanPham $a, SanPham $b): int => $a->gia <=> $b->gia,
            'gia-giam' => fn(SanPham $a, SanPham $b): int => $b->gia <=> $a->gia,
            'ten'      => fn(SanPham $a, SanPham $b): int => strcmp(\boDau($a->ten), \boDau($b->ten)),
            default    => fn(SanPham $a, SanPham $b): int => $a->id <=> $b->id,
        });
        return $ds;
    }

    /** Các danh mục đang có sản phẩm: khóa => số sản phẩm. */
    public function danhMucCoSan(): array
    {
        $kq = [];
        foreach ($this->tatCa() as $sp) {
            $kq[$sp->danhMuc] = ($kq[$sp->danhMuc] ?? 0) + 1;
        }
        return $kq;
    }

    /**
     * Sản phẩm liên quan: cùng danh mục trước, thiếu thì lấy thêm sản phẩm khác.
     *
     * @return SanPham[]
     */
    public function lienQuan(SanPham $sp, int $soLuong = 3): array
    {
        $khac = array_filter($this->tatCa(), fn(SanPham $x): bool => $x->id !== $sp->id);
        usort($khac, fn(SanPham $a, SanPham $b): int =>
            (int) ($b->danhMuc === $sp->danhMuc) <=> (int) ($a->danhMuc === $sp->danhMuc)
            ?: $a->id <=> $b->id);
        return array_slice($khac, 0, $soLuong);
    }
}

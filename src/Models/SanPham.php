<?php
/**
 * src/Models/SanPham.php - lớp thực thể: một viên ngọc/sản phẩm (thuộc tính readonly).
 * tuMang() tạo đối tượng từ một mục của data/san-pham.json và tự suy ra danh mục lọc.
 * Cách thử: php tests/kiem-thu.php (kiểm tra tuMang với dữ liệu thiếu trường).
 */
declare(strict_types=1);

namespace App\Models;

use InvalidArgumentException;

final class SanPham
{
    /** Danh mục dùng cho bộ lọc: khóa (an toàn trên URL) => nhãn hiển thị. */
    public const DANH_MUC = [
        'phi-thuy'  => 'Phỉ thúy',
        'cam-thach' => 'Cẩm thạch',
        'ngoc-bich' => 'Ngọc bích Hòa Điền',
        'khac'      => 'Loại khác',
    ];

    public function __construct(
        public readonly int $id,
        public readonly string $ma,
        public readonly string $ten,
        public readonly string $loai,
        public readonly string $danhMuc,
        public readonly string $anh,
        public readonly int $gia,
        public readonly string $xuatXu,
        public readonly string $kiemDinh,
        public readonly string $moTa,
        public readonly string $trongLuong,
        public readonly string $doCung,
        public readonly string $chietSuat,
        public readonly string $tyTrong,
        public readonly ?string $anhChungThu,
    ) {
    }

    /** Tạo đối tượng từ một phần tử của tệp JSON (id phải là số nguyên dương). */
    public static function tuMang(array $d): self
    {
        $id = filter_var($d['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false) {
            throw new InvalidArgumentException('Mục dữ liệu sản phẩm thiếu id hợp lệ.');
        }
        $loai = (string) ($d['loai'] ?? '');
        $chungThu = $d['anhChungThu'] ?? null;

        return new self(
            id: $id,
            ma: (string) ($d['ma'] ?? ''),
            ten: (string) ($d['ten'] ?? ''),
            loai: $loai,
            danhMuc: self::suyDanhMuc($loai),
            anh: (string) ($d['anh'] ?? ''),
            gia: (int) round((float) ($d['gia'] ?? 0)),
            xuatXu: (string) ($d['xuatXu'] ?? ''),
            kiemDinh: (string) ($d['kiemDinh'] ?? ''),
            moTa: (string) ($d['moTa'] ?? ''),
            trongLuong: (string) ($d['trongLuong'] ?? ''),
            doCung: (string) ($d['doCung'] ?? ''),
            chietSuat: (string) ($d['chietSuat'] ?? ''),
            tyTrong: (string) ($d['tyTrong'] ?? ''),
            anhChungThu: is_string($chungThu) && $chungThu !== '' ? $chungThu : null,
        );
    }

    /** Nhãn danh mục để hiển thị. */
    public function tenDanhMuc(): string
    {
        return self::DANH_MUC[$this->danhMuc] ?? self::DANH_MUC['khac'];
    }

    /** Suy danh mục từ trường "loai" của JSON (không đổi dữ liệu gốc của Bài tập 4). */
    private static function suyDanhMuc(string $loai): string
    {
        $l = \boDau($loai);
        return match (true) {
            str_contains($l, 'phi thuy') => 'phi-thuy',
            str_contains($l, 'cam thach') => 'cam-thach',
            str_contains($l, 'ngoc bich'), str_contains($l, 'nephrite') => 'ngoc-bich',
            default => 'khac',
        };
    }
}

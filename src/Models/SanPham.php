<?php
/**
 * src/Models/SanPham.php - lớp thực thể: một viên ngọc/sản phẩm từ bảng san_pham (kèm JOIN danh_muc).
 * Cài đặt JsonSerializable để REST API trả JSON chỉ gồm các trường công khai, không lộ trường nội bộ (slide 48).
 * Cách thử: json_encode($kho->timTheoId(1), JSON_UNESCAPED_UNICODE) -> chuỗi JSON gọn, an toàn.
 */
declare(strict_types=1);

namespace App\Models;

use InvalidArgumentException;
use JsonSerializable;

final class SanPham implements JsonSerializable
{
    public function __construct(
        public readonly int $id,
        public readonly int $danhMucId,
        public readonly string $ma,
        public readonly string $ten,
        public readonly string $loai,
        public readonly string $danhMucSlug,
        public readonly string $tenDanhMuc,
        public readonly string $anh,
        public readonly int $gia,
        public readonly int $tonKho,
        public readonly string $xuatXu,
        public readonly string $kiemDinh,
        public readonly string $moTa,
        public readonly string $trongLuong,
        public readonly string $doCung,
        public readonly string $chietSuat,
        public readonly string $tyTrong,
        public readonly ?string $anhChungThu,
        public readonly string $ngayTao = '',
    ) {
    }

    /** Tạo đối tượng từ một dòng kết quả truy vấn PDO (bảng san_pham JOIN danh_muc). */
    public static function tuMang(array $d): self
    {
        $id = filter_var($d['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false) {
            throw new InvalidArgumentException('Dòng dữ liệu sản phẩm thiếu id hợp lệ.');
        }
        $dmId = (int) ($d['danh_muc_id'] ?? 1);
        $chungThu = $d['anh_chung_thu'] ?? ($d['anhChungThu'] ?? null);
        $anh = (string) ($d['anh'] ?? '');

        return new self(
            id: $id,
            danhMucId: $dmId,
            ma: (string) ($d['ma'] ?? ('JADE-' . str_pad((string) $id, 2, '0', STR_PAD_LEFT))),
            ten: (string) ($d['ten'] ?? ''),
            loai: (string) ($d['loai'] ?? 'Ngọc tự nhiên'),
            danhMucSlug: (string) ($d['danh_muc_slug'] ?? 'phi-thuy'),
            tenDanhMuc: (string) ($d['ten_danh_muc'] ?? 'Ngọc quý'),
            anh: $anh !== '' ? $anh : 'nhan-phi-thuy-01.jpg',
            gia: (int) ($d['gia'] ?? 0),
            tonKho: max(0, (int) ($d['ton_kho'] ?? 0)),
            xuatXu: (string) ($d['xuat_xu'] ?? ($d['xuatXu'] ?? 'Myanmar')),
            kiemDinh: (string) ($d['kiem_dinh'] ?? ($d['kiemDinh'] ?? 'Đã thẩm định')),
            moTa: (string) ($d['mo_ta'] ?? ($d['moTa'] ?? '')),
            trongLuong: (string) ($d['trong_luong'] ?? ($d['trongLuong'] ?? '—')),
            doCung: (string) ($d['do_cung'] ?? ($d['doCung'] ?? '6.5 - 7.0')),
            chietSuat: (string) ($d['chiet_suat'] ?? ($d['chietSuat'] ?? '1.66')),
            tyTrong: (string) ($d['ty_trong'] ?? ($d['tyTrong'] ?? '3.33 g/cm³')),
            anhChungThu: is_string($chungThu) && $chungThu !== '' ? $chungThu : null,
            ngayTao: (string) ($d['ngay_tao'] ?? ''),
        );
    }

    public function tenDanhMuc(): string
    {
        return $this->tenDanhMuc;
    }

    public function conHang(): bool
    {
        return $this->tonKho > 0;
    }

    /** Đường dẫn ảnh sản phẩm (trong uploads/ nếu là ảnh tải lên, hoặc images/ nếu là ảnh mẫu). */
    public function duongDanAnh(string $goc = ''): string
    {
        if (preg_match('/^[a-f0-9]{32}\.(jpg|png|webp)\z/', $this->anh) === 1) {
            return $goc . 'uploads/' . $this->anh;
        }
        return $goc . 'images/' . $this->anh;
    }

    /** Chỉ trả các trường công khai cho REST API (slide 48). */
    public function jsonSerialize(): array
    {
        return [
            'id'         => $this->id,
            'ma'         => $this->ma,
            'ten'        => $this->ten,
            'danh_muc'   => $this->tenDanhMuc,
            'loai'       => $this->loai,
            'xuat_xu'    => $this->xuatXu,
            'gia'        => $this->gia,
            'ton_kho'    => $this->tonKho,
            'con_hang'   => $this->conHang(),
            'anh'        => $this->duongDanAnh(),
        ];
    }
}

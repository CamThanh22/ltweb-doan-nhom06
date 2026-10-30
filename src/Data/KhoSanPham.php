<?php
/**
 * src/Data/KhoSanPham.php - lớp truy cập dữ liệu (Repository): mọi câu SQL về san_pham và danh_muc
 * đều nằm trong lớp này (nhận PDO qua constructor, slide 23 & gợi ý 3 Phụ lục B).
 * Dùng prepared statement cho mọi dữ liệu người dùng; lấy nhiều id bằng một câu WHERE id IN (...).
 * Cách thử: mở danh-sach.php?q=nhan&dm=1&gia=20000000&sx=gia-tang&trang=2.
 */
declare(strict_types=1);

namespace App\Data;

use App\Models\SanPham;
use PDO;

class KhoSanPham
{
    /** Các kiểu sắp xếp được phép trên URL (danh sách trắng cho ORDER BY, slide 30). */
    public const SAP_XEP = [
        'moi'      => 'Mới nhất',
        'gia-tang' => 'Giá tăng dần',
        'gia-giam' => 'Giá giảm dần',
        'ten'      => 'Tên A–Z',
    ];

    private const SELECT_CO_BAN = 'SELECT sp.*, dm.ten AS ten_danh_muc, dm.slug AS danh_muc_slug
                                   FROM san_pham sp
                                   JOIN danh_muc dm ON dm.id = sp.danh_muc_id';

    public function __construct(private PDO $db)
    {
    }

    /** Tất cả sản phẩm, mới nhất trước. @return SanPham[] */
    public function tatCa(): array
    {
        $ds = $this->db->query(self::SELECT_CO_BAN . ' ORDER BY sp.id DESC')->fetchAll();
        return array_map(fn(array $d): SanPham => SanPham::tuMang($d), $ds);
    }

    /** $soLuong sản phẩm mới nhất cho trang chủ. @return SanPham[] */
    public function moiNhat(int $soLuong = 4): array
    {
        $st = $this->db->prepare(self::SELECT_CO_BAN . ' ORDER BY sp.id DESC LIMIT ?');
        $st->execute([max(1, $soLuong)]);
        return array_map(fn(array $d): SanPham => SanPham::tuMang($d), $st->fetchAll());
    }

    /** Tìm một sản phẩm theo khóa chính id (JOIN kèm tên danh mục). */
    public function timTheoId(int $id): ?SanPham
    {
        $st = $this->db->prepare(self::SELECT_CO_BAN . ' WHERE sp.id = ?');
        $st->execute([$id]);
        $d = $st->fetch();
        return $d !== false ? SanPham::tuMang($d) : null;
    }

    /**
     * Lấy nhiều sản phẩm trong MỘT câu SQL bằng WHERE sp.id IN (?, ...) (slide 21 - tránh lỗi N+1).
     * Trả về mảng kết hợp [id => SanPham].
     *
     * @param int[] $dsId
     * @return array<int, SanPham>
     */
    public function timTheoNhieuId(array $dsId): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $dsId), fn(int $x): bool => $x > 0)));
        if (!$ids) {
            return [];
        }
        $dau = implode(',', array_fill(0, count($ids), '?'));
        $st = $this->db->prepare(self::SELECT_CO_BAN . " WHERE sp.id IN ($dau)");
        $st->execute($ids);
        $kq = [];
        foreach ($st->fetchAll() as $d) {
            $sp = SanPham::tuMang($d);
            $kq[$sp->id] = $sp;
        }
        return $kq;
    }

    /**
     * Bốn sản phẩm cùng danh mục với $sp (trừ chính $sp) cho trang chi tiết (Bảng 1, mục 1).
     *
     * @return SanPham[]
     */
    public function cungDanhMuc(SanPham $sp, int $soLuong = 4): array
    {
        $st = $this->db->prepare(
            self::SELECT_CO_BAN
            . ' WHERE sp.danh_muc_id = ? AND sp.id <> ?
                ORDER BY sp.id DESC LIMIT ?'
        );
        $st->execute([$sp->danhMucId, $sp->id, max(1, $soLuong)]);
        return array_map(fn(array $d): SanPham => SanPham::tuMang($d), $st->fetchAll());
    }

    /**
     * Dựng mệnh đề WHERE và mảng tham số từ bộ lọc $loc (gợi ý 3 Phụ lục B & slide 29).
     * Dùng chung cho dem() và trang() để hai câu SQL luôn khớp điều kiện.
     *
     * @return array{0: string, 1: array<int, mixed>}
     */
    private function dieuKien(array $loc): array
    {
        $dk = [];
        $ts = [];
        $q = trim((string) ($loc['q'] ?? ''));
        if ($q !== '') {
            $mau = '%' . addcslashes($q, '%_\\') . '%';
            $dk[] = '(sp.ten LIKE ? OR sp.ma LIKE ? OR sp.loai LIKE ?)';
            $ts[] = $mau;
            $ts[] = $mau;
            $ts[] = $mau;
        }
        if (!empty($loc['dm'])) {
            $dk[] = 'sp.danh_muc_id = ?';
            $ts[] = (int) $loc['dm'];
        }
        if (!empty($loc['giaToiDa'])) {
            $dk[] = 'sp.gia <= ?';
            $ts[] = (int) $loc['giaToiDa'];
        }
        $where = $dk ? 'WHERE ' . implode(' AND ', $dk) : '';
        return [$where, $ts];
    }

    /** Đếm tổng số dòng khớp bộ lọc để tính số trang (slide 31). */
    public function dem(array $loc = []): int
    {
        [$where, $ts] = $this->dieuKien($loc);
        $st = $this->db->prepare("SELECT COUNT(*) FROM san_pham sp $where");
        $st->execute($ts);
        return (int) $st->fetchColumn();
    }

    /**
     * Lấy một trang sản phẩm theo bộ lọc, kiểu sắp xếp và phân trang LIMIT ? OFFSET ? (gợi ý 3).
     *
     * @return SanPham[]
     */
    public function trang(array $loc, string $sx, int $soDong, int $boQua): array
    {
        [$where, $ts] = $this->dieuKien($loc);
        $orderBy = match ($sx) {
            'gia-tang' => 'sp.gia ASC, sp.id ASC',
            'gia-giam' => 'sp.gia DESC, sp.id DESC',
            'ten'      => 'sp.ten ASC, sp.id ASC',
            default    => 'sp.id DESC',
        };
        $sql = self::SELECT_CO_BAN . " $where ORDER BY $orderBy LIMIT ? OFFSET ?";
        $st = $this->db->prepare($sql);
        $st->execute([...$ts, max(1, $soDong), max(0, $boQua)]);
        return array_map(fn(array $d): SanPham => SanPham::tuMang($d), $st->fetchAll());
    }

    /**
     * Tìm kiếm nhanh cho REST API (api/san-pham.php?q=..., tối đa $gioiHan mục, slide 48).
     *
     * @return SanPham[]
     */
    public function timKiem(string $tuKhoa = '', int $gioiHan = 8): array
    {
        return $this->trang(['q' => $tuKhoa], 'moi', $gioiHan, 0);
    }

    /** Danh sách mọi danh mục kèm số sản phẩm (LEFT JOIN + GROUP BY, slide 12). */
    public function danhSachDanhMuc(): array
    {
        return $this->db->query(
            'SELECT dm.id, dm.ten, dm.slug, COUNT(sp.id) AS so_sp
             FROM danh_muc dm
             LEFT JOIN san_pham sp ON sp.danh_muc_id = dm.id
             GROUP BY dm.id, dm.ten, dm.slug
             ORDER BY dm.id ASC'
        )->fetchAll();
    }
}

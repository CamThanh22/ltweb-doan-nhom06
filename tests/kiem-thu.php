<?php
/**
 * tests/kiem-thu.php - Kiểm thử các lớp và hàm của Chương 6 (MySQL + PDO).
 * Chạy:  php tests/kiem-thu.php      (in PASS/FAIL từng ca, thoát mã 1 nếu có ca hỏng)
 * Kiểm tra: e(), vnd(), boDau(), db(), SanPham (JsonSerializable), KhoSanPham (timTheoId,
 * timTheoNhieuId, dem, trang, cungDanhMuc), KhoLienHe, GioHang, DaXem, kiemTraSanPham, TaiAnh.
 */
require __DIR__ . '/../inc/config.php';
require __DIR__ . '/../inc/kiem-tra.php';

use App\Data\KhoLienHe;
use App\Data\KhoSanPham;
use App\Models\SanPham;
use App\Services\DaXem;
use App\Services\GioHang;
use App\Services\TaiAnh;

$dat  = 0;
$hong = 0;
function ca(string $ten, bool $ok): void
{
    global $dat, $hong;
    $ok ? $dat++ : $hong++;
    echo ($ok ? 'PASS' : 'FAIL') . '  ' . $ten . PHP_EOL;
}

ca('e() thoát <, >, " và \'', e('<a href="x" onclick=\'y\'>') === '&lt;a href=&quot;x&quot; onclick=&#039;y&#039;&gt;');
ca('vnd(18500000)', vnd(18500000) === '18.500.000 VNĐ');
ca('boDau("Nhẫn Phỉ Thúy Đẹp")', boDau('Nhẫn Phỉ Thúy Đẹp') === 'nhan phi thuy dep');

try {
    SanPham::tuMang(['ten' => 'thiếu id']);
    ca('SanPham::tuMang thiếu id phải ném ngoại lệ', false);
} catch (InvalidArgumentException) {
    ca('SanPham::tuMang thiếu id phải ném ngoại lệ', true);
}

$kho = new KhoSanPham(db());
ca('KhoSanPham::dem() có ít nhất 30 sản phẩm', $kho->dem() >= 30);
ca('timTheoId(1) trả về JADE-01 kèm tên danh mục', $kho->timTheoId(1)?->ma === 'JADE-01' && $kho->timTheoId(1)?->tenDanhMuc() !== '');
ca('timTheoId(999999) trả null', $kho->timTheoId(999999) === null);
ca('timTheoNhieuId([1, 2, 999999]) trả đúng 2 mục trong 1 câu SQL', count($kho->timTheoNhieuId([1, 2, 999999])) === 2);
ca('tìm kiếm "nhẫn", "NHẪN", "nhan" trả cùng số kết quả', $kho->dem(['q' => 'nhẫn']) === $kho->dem(['q' => 'NHẪN']) && $kho->dem(['q' => 'nhẫn']) === $kho->dem(['q' => 'nhan']));
ca('tìm kiếm "Levi\'s" không lỗi SQL và khớp đúng 1 mục', $kho->dem(['q' => "Levi's"]) === 1);
ca('tìm kiếm "50%" thoát ký tự đại diện % đúng (chỉ khớp chuỗi 50%)', $kho->dem(['q' => '50%']) === 1);

$trangGiaTang = array_map(fn($s) => $s->gia, $kho->trang([], 'gia-tang', 10, 0));
$daSapXep = $trangGiaTang;
sort($daSapXep);
ca('trang() sắp xếp giá tăng dần đúng', $trangGiaTang === $daSapXep);

$gio = new GioHang();
$gio->xoaHet();
ca('idHopLe("abc") là null', GioHang::idHopLe('abc') === null);
ca('soLuongHopLe: 0, -3, 1000, "x" đều null', GioHang::soLuongHopLe(0) === null && GioHang::soLuongHopLe(-3) === null && GioHang::soLuongHopLe(1000) === null && GioHang::soLuongHopLe('x') === null);
$gio->them(1, 2);
$gio->them(2, 1);
ca('soMon() = 3 và tongTien() lấy giá từ MySQL', $gio->soMon() === 3 && $gio->tongTien($kho) === 2 * $kho->timTheoId(1)->gia + $kho->timTheoId(2)->gia);
$gio->xoaHet();

$_COOKIE['da_xem'] = 'abc,-1,999999,3,3,5';
$ids = array_map(fn($s) => $s->id, DaXem::doc($kho));
ca('DaXem::doc bỏ qua abc, -1, 999999 và giữ thứ tự [3, 5]', $ids === [3, 5]);

[$duSp, $loiSp] = kiemTraSanPham(['ma' => '', 'ten' => '', 'gia' => '0', 'ton_kho' => '-1', 'danh_muc_id' => '999'], [1, 2, 3]);
ca('kiemTraSanPham bắt đủ lỗi tên trống, giá 0, tồn kho -1, danh mục 999', isset($loiSp['ten'], $loiSp['gia'], $loiSp['ton_kho'], $loiSp['danh_muc_id']));

$khoLh = new KhoLienHe(db());
ca('KhoLienHe::dem() >= 4', $khoLh->dem() >= 4);
ca('TaiAnh::kiemTra(không chọn tệp) = null', TaiAnh::kiemTra(['error' => UPLOAD_ERR_NO_FILE]) === null);

echo PHP_EOL . "Tổng: $dat đạt, $hong hỏng" . PHP_EOL;
exit($hong > 0 ? 1 : 0);

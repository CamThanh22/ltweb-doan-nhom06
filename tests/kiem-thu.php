<?php
/**
 * tests/kiem-thu.php - kiểm thử nhanh các lớp và hàm (không cần mở trình duyệt, không cần máy chủ web).
 * Chạy:  php tests/kiem-thu.php      (in PASS/FAIL từng ca, thoát mã 1 nếu có ca hỏng)
 * Kiểm tra: e(), vnd(), boDau(), SanPham::tuMang, KhoSanPham (tìm/lọc/sắp xếp), GioHang, DaXem, KhoJsonl, TaiAnh.
 */
require __DIR__ . '/../inc/config.php';

use App\Data\KhoJsonl;
use App\Data\KhoSanPham;
use App\Models\SanPham;
use App\Services\DaXem;
use App\Services\GioHang;
use App\Services\TaiAnh;

$dat = 0;
$hong = 0;
function ca(string $ten, bool $ok): void
{
    global $dat, $hong;
    $ok ? $dat++ : $hong++;
    echo ($ok ? 'PASS' : 'FAIL') . '  ' . $ten . PHP_EOL;
}

// --- hàm tiện ích
ca('e() thoát <, >, " và \'', e('<a href="x" onclick=\'y\'>') === '&lt;a href=&quot;x&quot; onclick=&#039;y&#039;&gt;');
ca('vnd(18500000)', vnd(18500000) === '18.500.000 VNĐ');
ca('boDau("Nhẫn Phỉ Thúy Đẹp")', boDau('Nhẫn Phỉ Thúy Đẹp') === 'nhan phi thuy dep');
ca('layChuoi bỏ qua mảng (?q[]=x)', layChuoi(['q' => ['x']], 'q') === '');
ca('layChuoi cắt khoảng trắng', layChuoi(['q' => '  abc  '], 'q') === 'abc');

// --- thực thể
try {
    SanPham::tuMang(['ten' => 'thiếu id']);
    ca('SanPham::tuMang thiếu id phải ném ngoại lệ', false);
} catch (InvalidArgumentException) {
    ca('SanPham::tuMang thiếu id phải ném ngoại lệ', true);
}

// --- kho sản phẩm
$kho = new KhoSanPham(__DIR__ . '/../data/san-pham.json');
ca('KhoSanPham::tatCa() có 12 sản phẩm', count($kho->tatCa()) === 12);
ca('timTheoId(1) trả về JADE-01', $kho->timTheoId(1)?->ma === 'JADE-01');
ca('timTheoId(999999) trả null', $kho->timTheoId(999999) === null);
ca('timKiem("NHẪN") không phân biệt dấu/hoa thường', count($kho->timKiem('NHẪN')) === count($kho->timKiem('nhan')) && count($kho->timKiem('nhan')) > 0);
ca('timKiem("kim") rỗng', $kho->timKiem('kim') === []);
$giaTang = array_map(fn($s) => $s->gia, $kho->timKiem('', '', 'gia-tang'));
$daSapXep = $giaTang;
sort($daSapXep);
ca('sắp xếp giá tăng dần đúng', $giaTang === $daSapXep);
ca('lọc danh mục phi-thuy chỉ trả phỉ thúy', array_reduce($kho->timKiem('', 'phi-thuy'), fn($c, $s) => $c && $s->danhMuc === 'phi-thuy', true));

// --- giỏ hàng
$gio = new GioHang();
$gio->xoaHet();
ca('idHopLe("abc") là null', GioHang::idHopLe('abc') === null);
ca('soLuongHopLe: 0, -3, 1000, "x", mảng đều null', GioHang::soLuongHopLe(0) === null && GioHang::soLuongHopLe(-3) === null
    && GioHang::soLuongHopLe(1000) === null && GioHang::soLuongHopLe('x') === null && GioHang::soLuongHopLe([1]) === null);
$gio->them(1, 2);
$gio->them(2, 1);
ca('soMon() = 3', $gio->soMon() === 3);
ca('tongTien() lấy giá từ dữ liệu', $gio->tongTien($kho) === 2 * $kho->timTheoId(1)->gia + $kho->timTheoId(2)->gia);
$gio->capNhat(1, 1);
$gio->xoa(2);
ca('sau cập nhật và xóa: soMon() = 1', $gio->soMon() === 1);
try {
    $gio->them(1, -3);
    ca('them() với số lượng âm phải ném ngoại lệ', false);
} catch (InvalidArgumentException) {
    ca('them() với số lượng âm phải ném ngoại lệ', true);
}
$_SESSION['gio'] = [999999 => 2];
ca('mục không còn trong dữ liệu bị gỡ khỏi giỏ', $gio->chiTiet($kho) === [] && $gio->soMon() === 0);

// --- cookie "đã xem" (không tin giá trị cookie)
$_COOKIE['da_xem'] = 'abc,-1,999999,3,3,5';
$ids = array_map(fn($s) => $s->id, DaXem::doc($kho));
ca('DaXem::doc bỏ qua abc, -1, 999999 và trùng', $ids === [3, 5]);

// --- kho JSON Lines
$tam = sys_get_temp_dir() . '/ltweb_kt_' . bin2hex(random_bytes(4)) . '.jsonl';
$k = new KhoJsonl($tam);
$k->them(['a' => 1]);
$k->them(['a' => 2]);
ca('KhoJsonl: mới nhất đứng đầu, đủ 2 dòng', $k->dem() === 2 && $k->moiNhat(1)[0]['a'] === 2);
unlink($tam);

// --- upload: tệp không do PHP nhận qua HTTP bị từ chối; không chọn tệp thì không lỗi
ca('TaiAnh::kiemTra(không chọn tệp) = null', TaiAnh::kiemTra(['error' => UPLOAD_ERR_NO_FILE]) === null);
ca('TaiAnh::kiemTra(vượt upload_max_filesize) có báo lỗi', str_contains((string) TaiAnh::kiemTra(['error' => UPLOAD_ERR_INI_SIZE, 'tmp_name' => '']), '2 MB'));
ca('TaiAnh::kiemTra(tệp giả không qua HTTP) bị từ chối', TaiAnh::kiemTra(['error' => UPLOAD_ERR_OK, 'tmp_name' => __FILE__, 'name' => 'a.jpg']) !== null);

echo PHP_EOL . "Tổng: $dat đạt, $hong hỏng" . PHP_EOL;
exit($hong > 0 ? 1 : 0);

<?php
/**
 * inc/kiem-tra.php - hàm kiemTraSanPham() dùng chung cho cả trang thêm (them-san-pham.php)
 * và trang sửa (sua-san-pham.php) sản phẩm ngọc quý (gợi ý 4, Phụ lục B).
 * Trả về [$du, $loi]; các khóa của $du khớp tên tham số :ten, :ma, :gia... của câu SQL.
 * Cách thử: gửi tên trống, giá 0, tồn kho -1, danh mục 999 -> trả mảng lỗi đầy đủ.
 */
declare(strict_types=1);

function kiemTraSanPham(array $vao, array $idDanhMuc): array
{
    $ma  = strtoupper(layChuoi($vao, 'ma'));
    $ten = layChuoi($vao, 'ten');
    $gia = filter_var($vao['gia'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1000]]);
    $ton = filter_var($vao['ton_kho'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 9999]]);
    $dm  = filter_var($vao['danh_muc_id'] ?? '', FILTER_VALIDATE_INT);

    $loai        = layChuoi($vao, 'loai') ?: 'Phỉ thúy Type A';
    $xuatXu      = layChuoi($vao, 'xuat_xu') ?: 'Myanmar';
    $kiemDinh    = layChuoi($vao, 'kiem_dinh') ?: 'Đã thẩm định (PNJ Lab)';
    $trongLuong  = layChuoi($vao, 'trong_luong') ?: '—';
    $doCung      = layChuoi($vao, 'do_cung') ?: '6.5 - 7.0';
    $chietSuat   = layChuoi($vao, 'chiet_suat') ?: '1.66';
    $tyTrong     = layChuoi($vao, 'ty_trong') ?: '3.33 g/cm³';
    $moTa        = layChuoi($vao, 'mo_ta');

    $du = [
        'danh_muc_id' => $dm === false ? 0 : (int) $dm,
        'ma'          => $ma,
        'ten'         => $ten,
        'loai'        => mb_substr($loai, 0, 150),
        'gia'         => $gia === false ? 0 : (int) $gia,
        'ton_kho'     => $ton === false ? -1 : (int) $ton,
        'xuat_xu'     => mb_substr($xuatXu, 0, 100),
        'kiem_dinh'   => mb_substr($kiemDinh, 0, 150),
        'trong_luong' => mb_substr($trongLuong, 0, 50),
        'do_cung'     => mb_substr($doCung, 0, 50),
        'chiet_suat'  => mb_substr($chietSuat, 0, 50),
        'ty_trong'    => mb_substr($tyTrong, 0, 50),
        'mo_ta'       => $moTa,
    ];

    $loi = [];
    if ($ma === '' || preg_match('/^[A-Z0-9-]{3,30}\z/', $ma) !== 1) {
        $loi['ma'] = 'Mã sản phẩm dài 3 – 30 ký tự (chữ in hoa, số, dấu gạch ngang, ví dụ JADE-35).';
    }
    $n = mb_strlen($ten);
    if ($n < 2 || $n > 200) {
        $loi['ten'] = 'Tên sản phẩm phải dài từ 2 đến 200 ký tự.';
    }
    if ($gia === false) {
        $loi['gia'] = 'Giá niêm yết phải là số nguyên từ 1.000 VNĐ trở lên.';
    }
    if ($ton === false) {
        $loi['ton_kho'] = 'Số lượng tồn kho phải là số nguyên từ 0 đến 9.999.';
    }
    $dsIdDm = array_map('intval', $idDanhMuc);
    if ($dm === false || !in_array((int) $dm, $dsIdDm, true)) {
        $loi['danh_muc_id'] = 'Vui lòng chọn một danh mục có sẵn trong hệ thống.';
    }
    if (mb_strlen($moTa) > 2000) {
        $loi['mo_ta'] = 'Mô tả sản phẩm tối đa 2.000 ký tự.';
    }

    return [$du, $loi];
}

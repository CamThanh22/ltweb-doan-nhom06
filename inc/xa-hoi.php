<?php
declare(strict_types=1);

use App\Services\TaiAnh;

function anhXaHoi(string $truong): ?string
{
    $tep = $_FILES[$truong] ?? null;
    $loi = TaiAnh::kiemTra($tep);
    if ($loi !== null) {
        saiDuLieu($loi);
    }
    if (!TaiAnh::coTep($tep)) {
        return null;
    }
    $duLieu = file_get_contents($tep['tmp_name']);
    if ($duLieu === false) {
        saiDuLieu('Không đọc được ảnh đã chọn.');
    }
    return $duLieu;
}

function baoHoatDong(int $nguoiNhan, int $tacNhan, string $loai, int $doiTuong): void
{
    if ($nguoiNhan === $tacNhan) {
        return;
    }
    db()->prepare('INSERT INTO thong_bao_xa_hoi (nguoi_nhan_id,tac_nhan_id,loai,doi_tuong_id) VALUES (?,?,?,?)')
        ->execute([$nguoiNhan, $tacNhan, $loai, $doiTuong]);
}

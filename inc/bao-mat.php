<?php
declare(strict_types=1);
function ghiLogBaoMat(string $loai): void {
    if (!in_array($loai, ['csrf_sai','cam_truy_cap','dang_nhap_sai'], true)) return;
    $ip = filter_var($_SERVER['REMOTE_ADDR'] ?? '', FILTER_VALIDATE_IP) ?: 'unknown';
    $dong = gmdate('c').' '. $loai . ' ip=' . $ip . "\n";
    @file_put_contents(__DIR__.'/../logs/bao-mat.log', $dong, FILE_APPEND | LOCK_EX);
}
function tuKiemTraDuLieu(string $ten, mixed $giaTri, int $toiDa = 255): ?string {
    if (!is_string($giaTri)) return "$ten phải là chuỗi hợp lệ.";
    if (trim($giaTri)==='') return "$ten không được để trống.";
    if (mb_strlen($giaTri)>$toiDa) return "$ten vượt quá $toiDa ký tự.";
    return null;
}

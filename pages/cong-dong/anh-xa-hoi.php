<?php
require dirname(__DIR__, 2).'/inc/config.php';
require dirname(__DIR__, 2).'/inc/nen-tang.php';

$id = idDuong($_GET['id'] ?? null);
$loai = layChuoi($_GET, 'loai');
$thamSo = [$id];
switch ($loai) {
    case 'dai-dien':
        $sql = 'SELECT anh_dai_dien FROM nguoi_dung WHERE id=?';
        break;
    case 'bia':
        $sql = 'SELECT anh_bia FROM nguoi_dung WHERE id=?';
        break;
    case 'bai-viet':
        $sql = 'SELECT anh FROM bai_viet_cong_dong WHERE id=?';
        break;
    case 'tin-nhan':
        $uid = (int)(nguoiDung()['id'] ?? 0);
        $sql = 'SELECT anh FROM tin_nhan WHERE id=? AND (nguoi_gui_id=? OR nguoi_nhan_id=?)';
        $thamSo = [$id, $uid, $uid];
        break;
    default:
        http_response_code(404);
        exit;
}
$s = db()->prepare($sql);
$s->execute($thamSo);
$anh = $s->fetchColumn();
$mime = is_string($anh) ? (@getimagesizefromstring($anh)['mime'] ?? '') : '';
if (!in_array($mime, ['image/jpeg','image/png','image/webp'], true)) {
    http_response_code(404);
    exit;
}
header('Content-Type: '.$mime);
header('Cache-Control: private, no-store');
echo $anh;

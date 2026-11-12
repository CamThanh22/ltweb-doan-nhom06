<?php
/** Các hàm nghiệp vụ mô-đun mới; mọi truy vấn dùng prepared statements. */
declare(strict_types=1);
function idDuong(mixed $v): int { $i=filter_var($v,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]); return $i===false?0:(int)$i; }
function saiDuLieu(string $tb): never { http_response_code(422); exit(e($tb)); }
function chuyenTrang(string $url, string $tb=''): never { if($tb!=='') datFlash('ok',$tb);header('Location: '.$url, true,303);exit; }
function xuLyAnh(string $key): string {
  $f=$_FILES[$key]??null;
  if(!is_array($f) || ($f['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK) saiDuLieu('Cần tải ảnh hợp lệ.');
  if (!is_string($f['tmp_name'] ?? null) || !is_int($f['size'] ?? null)) saiDuLieu('Dữ liệu ảnh không hợp lệ.');
  if(($f['size']??0)<1 || $f['size']>3*1024*1024 || !is_uploaded_file($f['tmp_name'])) saiDuLieu('Ảnh tối đa 3 MB.');
  $mime=(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
  $ext=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'][$mime]??null;
  if(!$ext || @getimagesize($f['tmp_name'])===false) saiDuLieu('Chỉ cho phép ảnh JPG, PNG, WEBP.');
  $ten=bin2hex(random_bytes(16)).'.'.$ext;
  $dir=__DIR__.'/../uploads/';
  if(!move_uploaded_file($f['tmp_name'],$dir.$ten)) throw new RuntimeException('Không thể lưu ảnh.');
  return $ten;
}
function coGianHang(int $uid): bool { $s=db()->prepare("SELECT 1 FROM ho_so_nguoi_ban WHERE nguoi_dung_id=? AND trang_thai='da_duyet'");$s->execute([$uid]);return (bool)$s->fetchColumn(); }
function laAdminHienTai(): bool { return (nguoiDung()['vai_tro']??'')==='quan_tri'; }

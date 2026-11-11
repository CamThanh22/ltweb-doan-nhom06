<?php
// Static smoke test for added JadeHub features. Does not assert MySQL integration succeeds.
$root=dirname(__DIR__);$count=0;$err=[];
$expected=['database/nang-cap-san-pham-hoan-chinh.sql','inc/nen-tang.php','nguoi-ban.php','dang-ban.php','kiem-duyet.php','chung-thu.php','cong-dong.php','ho-so.php','tin-nhan.php','css/cong-dong.css','README_SAN_PHAM_HOAN_CHINH.md'];
foreach($expected as $file){$count++;if(!is_file($root.'/'.$file))$err[]="Missing $file";}
$schema=file_get_contents($root.'/database/nang-cap-san-pham-hoan-chinh.sql');
foreach(['ho_so_nguoi_ban','tin_dang_ngoc','kiem_dinh_ngoc','bai_viet_cong_dong','binh_luan_cong_dong','theo_doi','tin_nhan'] as $table){$count++;if(!str_contains($schema,"CREATE TABLE $table"))$err[]="Missing table $table";}
foreach(['nguoi-ban.php','dang-ban.php','kiem-duyet.php','cong-dong.php','ho-so.php','tin-nhan.php'] as $file){$s=file_get_contents($root.'/'.$file);$count++;if(!str_contains($s,'csrfField()'))$err[]="No csrfField in $file";}
foreach(['nguoi-ban.php','dang-ban.php','kiem-duyet.php','cong-dong.php','ho-so.php','tin-nhan.php','chung-thu.php'] as $file){$s=file_get_contents($root.'/'.$file);$count++;if(!str_contains($s,"inc/config.php"))$err[]="No centralized config in $file";}
foreach($err as $e)echo "FAIL $e\n";
echo "Extended checks: $count; errors: ".count($err)."\n";exit(count($err)?1:0);

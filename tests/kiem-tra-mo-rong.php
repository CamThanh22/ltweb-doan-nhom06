<?php
// Static smoke test for added JadeHub features. Does not assert MySQL integration succeeds.
$root=dirname(__DIR__);$count=0;$err=[];
$pages = [
    'pages/nguoi-ban/nguoi-ban.php',
    'pages/nguoi-ban/dang-ban.php',
    'pages/quan-tri/kiem-duyet.php',
    'pages/cong-dong/cong-dong.php',
    'pages/tai-khoan/ho-so.php',
    'pages/cong-dong/tin-nhan.php',
];
$schemaFile = 'database/cai-moi-cau-truc-va-du-lieu-mau.sql';
$expected = array_merge($pages, [$schemaFile, 'inc/nen-tang.php', 'css/cong-dong.css']);
foreach ($expected as $file) {
    $count++;
    if (!is_file($root . '/' . $file)) $err[] = "Missing $file";
}
$schema = is_file($root . '/' . $schemaFile) ? file_get_contents($root . '/' . $schemaFile) : '';
foreach(['ho_so_nguoi_ban','tin_dang_ngoc','kiem_dinh_ngoc','bai_viet_cong_dong','binh_luan_cong_dong','theo_doi','tin_nhan'] as $table){$count++;if(!str_contains($schema,"CREATE TABLE $table"))$err[]="Missing table $table";}
foreach ($pages as $file) {
    if (!is_file($root . '/' . $file)) continue;
    $source = file_get_contents($root . '/' . $file);
    $count += 2;
    if (!str_contains($source, 'csrfField()')) $err[] = "No csrfField in $file";
    if (!str_contains($source, 'inc/config.php')) $err[] = "No centralized config in $file";
}
foreach($err as $e)echo "FAIL $e\n";
echo "Extended checks: $count; errors: ".count($err)."\n";exit(count($err)?1:0);

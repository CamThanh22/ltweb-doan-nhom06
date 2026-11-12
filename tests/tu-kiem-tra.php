<?php
// Chạy: php tests/tu-kiem-tra.php; kiểm tra tĩnh nhanh – KHÔNG thay kiểm thử HTTP/MySQL.
$root=dirname(__DIR__); $errors=[]; $checks=0;
foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)) as $f){
 $path=$f->getPathname();
 if(str_contains($path,'/.git/') || str_contains($path,'/vendor/') || $f->getExtension()!=='php') continue;
 $code=file_get_contents($path);
 preg_match_all('~<form\b[^>]*method=["\']post["\'][^>]*>~i',$code,$matches,PREG_OFFSET_CAPTURE);
 foreach($matches[0] as [$tag,$pos]){
   ++$checks;
   if(!str_contains(substr($code,$pos+strlen($tag),180),'csrfField()')) $errors[]='Missing CSRF field: '.$path;
 }
}
foreach(['.env.example','database/cai-moi-cau-truc-va-du-lieu-mau.sql','database/cap-nhat-co-so-du-lieu-hien-co.sql','inc/csrf.php','.htaccess','uploads/.htaccess','database/tai-khoan.sql'] as $path){++$checks;if(!is_file($root.'/'.$path))$errors[]='Missing '.$path;}
if(is_file($root.'/.env')) echo "CẢNH BÁO: .env tồn tại tại máy cục bộ; không đưa vào gói nộp.\n";
foreach($errors as $error) echo "FAIL: $error\n";
echo 'Checked: '.$checks.' | Errors: '.count($errors)."\n";
exit($errors ? 1 : 0);

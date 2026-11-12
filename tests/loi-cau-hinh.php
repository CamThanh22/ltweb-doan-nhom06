<?php
// Run only on an isolated local copy: briefly changes .env, always restores it.
if(PHP_SAPI!=='cli' || !in_array('--isolated',$argv,true))exit("Dùng --isolated trên bản sao kiểm thử.\n");
$base=rtrim(getenv('BASE')?:'http://localhost:8000','/');
if(!in_array(parse_url($base,PHP_URL_HOST),['localhost','127.0.0.1'],true))exit(2);
$file=dirname(__DIR__).'/.env';$original=file_get_contents($file);$failed=0;
register_shutdown_function(static function()use($file,$original){file_put_contents($file,$original,LOCK_EX);});
try{
 $bad=preg_replace('/^APP_DEBUG=.*$/m','APP_DEBUG=false',$original);
 $bad=preg_replace('/^DB_PASSWORD=.*$/m','DB_PASSWORD=deliberate-invalid-test-password',$bad);
 file_put_contents($file,$bad,LOCK_EX);
 foreach(['index.php','api/san-pham.php'] as $path){
  $ch=curl_init($base.'/'.$path);curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);curl_setopt($ch,CURLOPT_TIMEOUT,10);
  $body=curl_exec($ch);$status=curl_getinfo($ch,CURLINFO_RESPONSE_CODE);
  $ok=$status===500 && !preg_match('/SQLSTATE|PDOException|Stack trace|DB_PASSWORD|deliberate-invalid/',$body);
  if(str_starts_with($path,'api/'))$ok=$ok && isset(json_decode($body,true)['loi']);
  echo ($ok?'PASS ':'FAIL ')."09 production database failure $path\n";if(!$ok)$failed++;
 }
}finally{file_put_contents($file,$original,LOCK_EX);}
exit($failed?1:0);

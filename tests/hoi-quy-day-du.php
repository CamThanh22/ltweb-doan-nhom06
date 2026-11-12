<?php
/** CLI only. Runs on a disposable LOCAL copy of JadeHub; creates and removes test rows.
 * BASE=http://localhost:8000 php tests/hoi-quy-day-du.php
 * Requires curl extension, .env and the project's sample accounts.
 */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$base = rtrim(getenv('BASE') ?: 'http://localhost:8000', '/');
if (!in_array(parse_url($base, PHP_URL_HOST), ['localhost','127.0.0.1'], true)) exit("LOCALHOST only\n");
require dirname(__DIR__).'/inc/config.php';
require dirname(__DIR__).'/inc/don-hang.php';
session_write_close();
$pdo=db(); $pass=0; $fail=0; $jars=[]; $orders=[]; $rows=[];
$prefix='JH_TEST_'.bin2hex(random_bytes(5));
function check(string $name, bool $ok): void {
 global $pass,$fail;
 $ok ? ++$pass : ++$fail;
 echo ($ok?'PASS ':'FAIL ').$name.PHP_EOL;
}
function client(): string {
 global $jars;
 $p=tempnam(sys_get_temp_dir(),'jade-'); $jars[]=$p;return $p;
}
function req(string $jar,string $path,?array $data=null,array $headers=[],?string $raw=null): array {
 global $base;
 $c=curl_init($base.'/'.$path);
 curl_setopt_array($c,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_HEADER=>true,CURLOPT_COOKIEFILE=>$jar,CURLOPT_COOKIEJAR=>$jar,CURLOPT_TIMEOUT=>20,CURLOPT_FOLLOWLOCATION=>false]);
 if($data!==null || $raw!==null){curl_setopt($c,CURLOPT_POST,true);curl_setopt($c,CURLOPT_POSTFIELDS,$raw??(count(array_filter($data??[],static fn($v)=>$v instanceof CURLFile))?$data:http_build_query($data)));}
 if($headers)curl_setopt($c,CURLOPT_HTTPHEADER,$headers);
 $result=curl_exec($c);
 if($result===false)throw new RuntimeException(curl_error($c));
 $n=curl_getinfo($c,CURLINFO_HEADER_SIZE);$status=curl_getinfo($c,CURLINFO_RESPONSE_CODE);
 return [$status,substr($result,$n),substr($result,0,$n)];
}
function token(string $jar,string $path='dang-nhap.php'): string {
 $r=req($jar,$path);preg_match('/name="csrf-token" content="([a-f0-9]{64})"/',$r[1],$m);
 if(!isset($m[1]))throw new RuntimeException("Missing CSRF: $path (HTTP {$r[0]})");
 return $m[1];
}
function sid(string $jar): string {
 preg_match('/PHPSESSID\s+([^\s]+)/',file_get_contents($jar),$m);return $m[1]??'';
}
function login(string $email): string {
 $j=client();$t=token($j);$before=sid($j);
 $r=req($j,'dang-nhap.php',['csrf'=>$t,'email'=>$email,'mat_khau'=>'matkhau123']);
 check("Login $email",$r[0]===302);
 check("Rotate session $email",sid($j)!=='' && sid($j)!==$before);
 return $j;
}
function scalar(string $sql,array $params=[]): mixed {
 global $pdo;$s=$pdo->prepare($sql);$s->execute($params);return $s->fetchColumn();
}
try {
 $j=client();[$status,$body,$h]=req($j,'dang-nhap.php');
 check('01 headers + cookie',$status===200 && stripos($h,'Content-Security-Policy:')!==false && stripos($h,'nosniff')!==false && stripos($h,'Referrer-Policy:')!==false && stripos($h,'HttpOnly')!==false && stripos($h,'SameSite=Lax')!==false && stripos($h,'X-Powered-By:')===false && !str_contains($h,"unsafe-inline"));
 foreach([[],['csrf'=>'abc'],['csrf'=>['abc']]] as $bad) check('02 CSRF malformed/absent',req($j,'dang-nhap.php',$bad)[0]===403);
 $t=token($j);
 $a=req($j,'api/gio-hang.php',null,['Content-Type: application/json'],'{}');
 check('03 API CSRF JSON',$a[0]===403 && isset(json_decode($a[1],true)['loi']));
 $a=req($j,'api/gio-hang.php',null,['Content-Type: application/json','X-CSRF-Token: '.$t],'{"id":1,"sl":1}');
 check('03 API valid CSRF',$a[0]===200 && (json_decode($a[1],true)['so_mon']??0)===1);
 foreach(['dang-xuat.php','xoa-san-pham.php'] as $path){$a=req($j,$path);check('04 GET 405 '.$path,$a[0]===405 && stripos($a[2],'Allow: POST')!==false);}
 $fake=client();$a=req($fake,'dang-nhap.php',null,['Cookie: PHPSESSID=attackerchosen12345678901234567890']);
 check('05 strict session rejects supplied id',str_contains($a[2],'Set-Cookie: PHPSESSID=') && !str_contains($a[2],'PHPSESSID=attackerchosen'));
 // Expire only a test session; production timeout remains 1800 seconds.
 $expired=client();$et=token($expired);
 req($expired,'dang-nhap.php',['csrf'=>$et,'email'=>'lan@ued.vn','mat_khau'=>'matkhau123','ghi_nho'=>'1']);
 $et=token($expired,'gio-hang.php');$oldSid=sid($expired);
 $savePath=explode(';',session_save_path());$sessionFile=end($savePath).'/sess_'.$oldSid;
 if(is_file($sessionFile) && is_writable($sessionFile)){
  $sessionData=file_get_contents($sessionFile);
  $sessionData=preg_replace('/last_activity\|i:\d+;/', 'last_activity|i:'.(time()-1801).';',$sessionData,1,$changed);
  if($changed)file_put_contents($sessionFile,$sessionData,LOCK_EX);
  check('06 expired form rejected',req($expired,'dat-hang.php',['csrf'=>$et,'phuong_thuc'=>'cod'])[0]===403);
  check('06 no remembered-login bypass',req($expired,'don-hang.php')[0]===302 && sid($expired)!==$oldSid);
 }else{check('06 session file accessible (CLI and server must share session.save_path)',false);}
 $customer=login('lan@ued.vn');$admin=login('admin@ued.vn');$other=login('an@ued.vn');
 $ct=token($customer,'gio-hang.php');$at=token($admin,'quan-tri.php');
 check('04 customer admin access',req($customer,'quan-tri.php')[0]===403);
 $n=(int)scalar('SELECT COUNT(*) FROM san_pham');
 check('04 customer delete denied',req($customer,'xoa-san-pham.php',['csrf'=>$ct,'id'=>1])[0]===403 && (int)scalar('SELECT COUNT(*) FROM san_pham')===$n);
 check('13 certificate moderation denied',req($customer,'kiem-duyet.php',['csrf'=>$ct,'id'=>1,'loai'=>'tin_dang','hanh_dong'=>'duyet'])[0]===403);
 foreach(['danh-sach.php?q%5B%5D=ao','chi-tiet.php?id%5B%5D=1','danh-sach.php?sx%5B%5D=x'] as $path) check('08 array '.$path,in_array(req($j,$path)[0],[200,404],true));
 check('08 contact arrays',req($j,'lien-he.php',['csrf'=>$t,'hoten'=>['x'],'email'=>['x']])[0]===200);
 $a=req($j,'danh-sach.php?q='.rawurlencode('<b>ao</b>'));
 check('07 reflected XSS escaped',str_contains($a[1],'&lt;b&gt;ao&lt;/b&gt;') && !str_contains($a[1],'<b>ao</b>'));
 // Checkout: verify persisted rows, server price, inventory, PRG and IDOR.
 $stock=(int)scalar('SELECT ton_kho FROM san_pham WHERE id=1');
 foreach(['cod','chuyen_khoan'] as $method){
  req($customer,'api/gio-hang.php',null,['Content-Type: application/json','X-CSRF-Token: '.$ct],'{"id":1,"sl":1}');
  $a=req($customer,'dat-hang.php',['csrf'=>$ct,'phuong_thuc'=>$method,'ghi_chu'=>$prefix]);
  preg_match('/Location: don-hang.php\?id=(\d+)/i',$a[2],$m);$id=(int)($m[1]??0);
  check('11 checkout '.$method,$a[0]===302 && $id>0);
  if(!$id)continue;
  $orders[]=$id;
  check('11 persisted price '.$method,(int)scalar('SELECT don_gia FROM chi_tiet_don WHERE don_hang_id=?',[$id])===(int)scalar('SELECT gia FROM san_pham WHERE id=1'));
  check('14 other buyer cannot view',req($other,'don-hang.php?id='.$id)[0]===404);
  check('14 buyer cannot confirm payment',req($customer,'don-hang.php',['csrf'=>$ct,'hanh_dong'=>'xac_nhan_thanh_toan','don_hang_id'=>$id])[0]===403);
  if($method==='chuyen_khoan'){
   req($admin,'don-hang.php',['csrf'=>$at,'hanh_dong'=>'xac_nhan_thanh_toan','don_hang_id'=>$id]);
   check('14 cannot confirm without reference',scalar('SELECT trang_thai FROM thanh_toan WHERE don_hang_id=?',[$id])==='cho_doi_soat');
   req($customer,'don-hang.php',['csrf'=>$ct,'hanh_dong'=>'gui_ma_chuyen_khoan','don_hang_id'=>$id,'ma_tham_chieu'=>$prefix]);
   check('14 submitting reference is not payment',scalar('SELECT trang_thai FROM thanh_toan WHERE don_hang_id=?',[$id])==='cho_doi_soat');
   req($admin,'don-hang.php',['csrf'=>$at,'hanh_dong'=>'xac_nhan_thanh_toan','don_hang_id'=>$id]);
   check('14 admin confirms verified payment',scalar('SELECT trang_thai FROM thanh_toan WHERE don_hang_id=?',[$id])==='da_xac_nhan');
  }
 }
 check('11 inventory decremented exactly twice',(int)scalar('SELECT ton_kho FROM san_pham WHERE id=1')===$stock-2);
 $n=(int)scalar('SELECT COUNT(*) FROM don_hang');
 req($customer,'dat-hang.php',['csrf'=>$ct,'phuong_thuc'=>'cod']);
 check('11 repeat checkout does not duplicate',(int)scalar('SELECT COUNT(*) FROM don_hang')===$n);
 try{luuDonHang([1=>1,999999=>1],2);check('11 rollback out of stock',false);}catch(HetHangException){check('11 rollback out of stock',(int)scalar('SELECT COUNT(*) FROM don_hang')===$n && (int)scalar('SELECT ton_kho FROM san_pham WHERE id=1')===$stock-2);}
 $cancelId=$orders[0];
 req($admin,'don-hang.php',['csrf'=>$at,'don_hang_id'=>$cancelId,'trang_thai'=>'da_huy']);
 check('11 cancelling restores inventory',(int)scalar('SELECT ton_kho FROM san_pham WHERE id=1')===$stock-1);
 req($admin,'don-hang.php',['csrf'=>$at,'don_hang_id'=>$cancelId,'trang_thai'=>'da_huy']);
 check('11 repeated cancellation cannot inflate stock',(int)scalar('SELECT ton_kho FROM san_pham WHERE id=1')===$stock-1);
 req($admin,'don-hang.php',['csrf'=>$at,'don_hang_id'=>$cancelId,'trang_thai'=>'dang_giao']);
 check('11 cancelled order cannot reopen',scalar('SELECT trang_thai FROM don_hang WHERE id=?',[$cancelId])==='da_huy');
 // Permission tests use a nonexistent table name: never drop a real table.
 foreach(['CREATE TABLE '.$prefix.' (id INT)','DROP TABLE '.$prefix] as $sql){
  try{$pdo->exec($sql);check('11 denied DDL',false);}catch(PDOException $e){check('11 denied DDL',($e->errorInfo[1]??0)===1142);}
 }
 // Rate limiter uses dedicated test identity, so existing accounts are not locked.
 $email=$prefix.'@example.test';$hash=hash('sha256',mb_strtolower($email));
 $pdo->prepare('INSERT INTO nguoi_dung(email,ho_ten,mat_khau,vai_tro) VALUES(?,?,?,?)')->execute([$email,$prefix,password_hash('matkhau123',PASSWORD_DEFAULT),'khach_hang']);
 $testUid=(int)$pdo->lastInsertId();$rows[]=['nguoi_dung',$testUid];
 $br=client();$bt=token($br);
 for($i=0;$i<5;$i++)check('12 wrong attempt '.($i+1),req($br,'dang-nhap.php',['csrf'=>$bt,'email'=>$email,'mat_khau'=>'wrong-test-value'])[0]===200);
 check('12 correct password blocked on sixth',req($br,'dang-nhap.php',['csrf'=>$bt,'email'=>$email,'mat_khau'=>'matkhau123'])[0]===429);
 $ind=login('an@ued.vn');
 $pdo->prepare('UPDATE dang_nhap_sai SET thoi_gian=NOW()-INTERVAL 16 MINUTE WHERE dinh_danh=?')->execute([$hash]);
 check('12 recovers after window',req($br,'dang-nhap.php',['csrf'=>$bt,'email'=>$email,'mat_khau'=>'matkhau123'])[0]===302 && (int)scalar('SELECT COUNT(*) FROM dang_nhap_sai WHERE dinh_danh=?',[$hash])===0);
 // Real moderation transaction: one listing creates one product and one certificate decision.
 $pdo->prepare("INSERT INTO ho_so_nguoi_ban(nguoi_dung_id,ten_gian_hang,gioi_thieu,trang_thai) VALUES(?,?,?,'da_duyet')")->execute([$testUid,$prefix,'Local regression fixture']);
 $pdo->prepare("INSERT INTO tin_dang_ngoc(nguoi_ban_id,danh_muc_id,ten,mo_ta,gia,ton_kho,xuat_xu,anh,anh_chung_thu,don_vi_kiem_dinh,so_chung_thu) VALUES(?,1,?,?,10000,2,'Test','test.jpg','certificate.jpg','Test Lab',?)")->execute([$testUid,$prefix,'Regression fixture description',$prefix]);
 $listing=(int)$pdo->lastInsertId();
 $pdo->prepare('INSERT INTO kiem_dinh_ngoc(tin_dang_id) VALUES(?)')->execute([$listing]);
 try {
  $a=req($admin,'kiem-duyet.php',['csrf'=>$at,'id'=>$listing,'loai'=>'tin_dang','hanh_dong'=>'duyet']);
  $product=(int)scalar('SELECT san_pham_id FROM tin_dang_ngoc WHERE id=?',[$listing]);
  check('13 admin approves real listing',$a[0]===303 && $product>0 && scalar('SELECT trang_thai FROM kiem_dinh_ngoc WHERE tin_dang_id=?',[$listing])==='tai_lieu_hop_le');
  $a=req($admin,'kiem-duyet.php',['csrf'=>$at,'id'=>$listing,'loai'=>'tin_dang','hanh_dong'=>'duyet']);
  check('13 repeated approval cannot duplicate',$a[0]===422 && (int)scalar('SELECT COUNT(*) FROM san_pham WHERE ma=?',['SELL-'.$listing])===1);
 }finally{
  $pdo->prepare('DELETE FROM tin_dang_ngoc WHERE id=?')->execute([$listing]);
  $pdo->prepare('DELETE FROM san_pham WHERE ma=?')->execute(['SELL-'.$listing]);
  $pdo->prepare('DELETE FROM ho_so_nguoi_ban WHERE nguoi_dung_id=?')->execute([$testUid]);
 }
 // Contact stores special characters as data, admin output escapes them.
 $contact=['csrf'=>$ct,'hoten'=>'<b>Bình</b>','email'=>'test@example.test','sdt'=>'0900000000','loaingoc'=>'phithuy','soluong'=>1,'ngayhen'=>date('Y-m-d',time()+86400),'ghichu'=>$prefix.' " \' &','camket'=>'1'];
 $a=req($customer,'lien-he.php',$contact);
 $contactId=(int)scalar('SELECT id FROM lien_he WHERE email=? ORDER BY id DESC LIMIT 1',['test@example.test']);
 if($contactId)$rows[]=['lien_he',$contactId];
 check('07 contact stores XSS as plain text',$a[0]===302 && $contactId>0);
 check('07 admin escapes contact name',str_contains(req($admin,'quan-tri.php')[1],'&lt;b&gt;Bình&lt;/b&gt;'));
 // Fake image is rejected; valid upload saves under a random filename.
 $fakeImage=tempnam(sys_get_temp_dir(),'jade-image-');
 file_put_contents($fakeImage,'<?php echo "not an image";');
 try {
  $n=(int)scalar('SELECT COUNT(*) FROM lien_he');
  $a=req($customer,'lien-he.php',$contact+['anhngoc'=>new CURLFile($fakeImage,'image/jpeg','photo.jpg')]);
  check('upload disguised PHP rejected',$a[0]===200 && (int)scalar('SELECT COUNT(*) FROM lien_he')===$n);
  $a=req($customer,'lien-he.php',$contact+['anhngoc'=>new CURLFile(dirname(__DIR__).'/images/logo-jadehub.jpg','image/jpeg','photo.jpg')]);
  $uploaded=(int)scalar('SELECT id FROM lien_he WHERE email=? ORDER BY id DESC LIMIT 1',['test@example.test']);
  if($uploaded && $uploaded!==$contactId)$rows[]=['lien_he',$uploaded];
  $filename=scalar('SELECT anh FROM lien_he WHERE id=?',[$uploaded]);
  check('upload real image uses random filename',$a[0]===302 && is_string($filename) && preg_match('/^[a-f0-9]{32}\.jpg$/',$filename)===1);
 }finally{@unlink($fakeImage);}
 // Private messages: GET is read-only; POST read-receipt is CSRF protected.
 $uidLan=(int)scalar('SELECT id FROM nguoi_dung WHERE email=?',['lan@ued.vn']);
 $uidAn=(int)scalar('SELECT id FROM nguoi_dung WHERE email=?',['an@ued.vn']);
 $a=req($customer,'tin-nhan.php',['csrf'=>$ct,'nguoi_nhan_id'=>$uidAn,'noi_dung'=>$prefix]);
 $message=(int)scalar('SELECT id FROM tin_nhan WHERE noi_dung=? ORDER BY id DESC LIMIT 1',[$prefix]);
 if($message)$rows[]=['tin_nhan',$message];
 check('chat send persists',$a[0]===303 && $message>0);
 $chat='tin-nhan.php?id='.$uidLan;
 $anToken=token($other,$chat);
 check('chat GET keeps unread',scalar('SELECT da_doc FROM tin_nhan WHERE id=?',[$message])===0);
 check('chat read receipt CSRF',req($other,$chat,['hanh_dong'=>'da_doc','last_id'=>$message])[0]===403);
 $a=req($other,$chat,['csrf'=>$anToken,'hanh_dong'=>'da_doc','last_id'=>$message]);
 check('chat POST marks read',$a[0]===200 && (int)scalar('SELECT da_doc FROM tin_nhan WHERE id=?',[$message])===1);
 // All member pages: CSRF, array input, and forbidden deletion.
 $members=[
 ['3120224133_thanh','thanh@ued.vn','tv_diem_hoc_phan',['xoa_mon'=>1]],
 ['3120224050_hanh','hanh@ued.vn','tv_luu_but',['xoa'=>1]],
 ['3120224063_hong','hong@ued.vn','tv_thoi_khoa_bieu',['hanhdong'=>'xoa_tkb','id'=>1]],
 ['3120124031_trinh','trinh@ued.vn','tv_viec_can_lam',['hanhdong'=>'xoa','id'=>1]],
 ['3120124038_bouakeo','bouakeo@ued.vn','tv_danh_gia',['hanhdong'=>'xoa_dg','id'=>1]],
 ];
 foreach($members as [$folder,$email,$table,$del]){
  $path='thanhvien/'.$folder.'/gioithieu.php';$tMember=token($customer,$path);
  check('B '.$folder.' GET',req($customer,$path)[0]===200);
  $count=(int)scalar('SELECT COUNT(*) FROM '.$table);
  check('B '.$folder.' missing CSRF',req($customer,$path,$del)[0]===403 && (int)scalar('SELECT COUNT(*) FROM '.$table)===$count);
  req($customer,$path,['csrf'=>$tMember]+$del);
  check('B '.$folder.' non-owner cannot delete',(int)scalar('SELECT COUNT(*) FROM '.$table)===$count);
  check('B '.$folder.' arrays',req($customer,$path.'?trang%5B%5D=1&q%5B%5D=x')[0]===200);
  $owner=login($email);check('B '.$folder.' owner page',req($owner,$path)[0]===200);
  $ot=token($owner,$path);$mssv=substr($folder,0,10);$foreign=$mssv==='3120224133'?'3120224050':'3120224133';
  $fixture=match($table){
   'tv_diem_hoc_phan'=>['ten_mon'=>'<b>Audit</b>','so_tin'=>3,'diem_a1'=>8,'diem_a2'=>8,'diem_a3'=>8],
   'tv_luu_but'=>['ten_khach'=>'<b>Audit</b>','loi_nhan'=>'XSS " \' & test'],
   'tv_thoi_khoa_bieu'=>['thu'=>'hai','buoi'=>'Sáng','hoc_phan'=>'<b>Audit</b>','phong'=>'XSS " \' &'],
   'tv_viec_can_lam'=>['noi_dung'=>'<b>Audit</b>'],
   'tv_danh_gia'=>['ten_khach'=>'<b>Audit</b>','so_sao'=>5,'nhan_xet'=>'XSS " \' &'],
  };
  foreach([$foreign,$mssv] as $who){
   $values=['mssv'=>$who]+$fixture;
   $pdo->prepare('INSERT INTO '.$table.' ('.implode(',',array_keys($values)).') VALUES ('.implode(',',array_fill(0,count($values),'?')).')')->execute(array_values($values));
   $fixtureId=(int)$pdo->lastInsertId();$rows[]=[$table,$fixtureId];
   if($who===$foreign){
    $fakeDelete=$del;foreach(['id','xoa','xoa_mon'] as $key)if(isset($fakeDelete[$key]))$fakeDelete[$key]=$fixtureId;
    req($owner,$path,['csrf'=>$ot]+$fakeDelete);
    check('B '.$folder.' owner cannot delete foreign row',(int)scalar('SELECT COUNT(*) FROM '.$table.' WHERE id=?',[$fixtureId])===1);
   }else{
    $rendered=req($owner,$path)[1];
    check('B '.$folder.' stored XSS escaped',str_contains($rendered,'&lt;b&gt;Audit&lt;/b&gt;') && !str_contains($rendered,'<b>Audit</b>'));
   }
  }
  check('B '.$folder.' POST arrays no 500',req($owner,$path,['csrf'=>$ot,'ten'=>['x'],'noidung'=>['x'],'hanhdong'=>['x']])[0]<500);

 }
 check('B Hong avatar owner protection',req($customer,'thanhvien/3120224063_hong/gioithieu.php',['csrf'=>$ct,'hanhdong'=>'avatar'])[0]===403);
 $p='thanhvien/3120224050_hanh/gioithieu.php';
 $before=(int)scalar('SELECT SUM(so_luot) FROM tv_luot_xem');
 req(client(),$p);
 check('B GET does not increment DB',(int)scalar('SELECT SUM(so_luot) FROM tv_luot_xem')===$before);
 $xss=$prefix.' <b>test</b> " \' &';
 req($customer,$p,['csrf'=>$ct,'ten'=>'<b>Test</b>','loi_nhan'=>$xss]);
 $lb=(int)scalar('SELECT id FROM tv_luu_but WHERE loi_nhan=?',[$xss]);if($lb)$rows[]=['tv_luu_but',$lb];
 $a=req($customer,$p);
 check('B stored XSS escaped',$lb>0 && str_contains($a[1],'&lt;b&gt;Test&lt;/b&gt;') && !str_contains($a[1],'<b>Test</b>'));
 // Full route crawl, checks local script/style/image resources.
 $routes=require dirname(__DIR__).'/inc/routes.php';
 foreach($routes as $route=>$file){
  if(in_array($route,['dang-xuat.php','xoa-san-pham.php','403.php','404.php','500.php'],true))continue;
  $a=req($admin,$route.(in_array($route,['chi-tiet.php','sua-san-pham.php','mua-ngay.php'],true)?'?id=1':''));
  check('route '.$route,$a[0]<500 && !preg_match('/(?:Warning|Fatal error|Deprecated|Notice):/',$a[1]));
 }
 $log=file_get_contents(dirname(__DIR__).'/logs/bao-mat.log');
 check('logs all 3 security events',str_contains($log,'csrf_sai') && str_contains($log,'cam_truy_cap') && str_contains($log,'dang_nhap_sai') && !str_contains($log,'matkhau123') && !str_contains($log,$ct));
} catch(Throwable $e) {check('uncaught: '.$e->getMessage(),false);}
finally{
 foreach($orders as $id){
  $s=$pdo->prepare('SELECT san_pham_id,so_luong FROM chi_tiet_don WHERE don_hang_id=?');$s->execute([$id]);
  foreach($s->fetchAll() as $line)if(scalar('SELECT trang_thai FROM don_hang WHERE id=?',[$id])!=='da_huy')$pdo->prepare('UPDATE san_pham SET ton_kho=ton_kho+? WHERE id=?')->execute([$line['so_luong'],$line['san_pham_id']]);
  $pdo->prepare('DELETE FROM don_hang WHERE id=?')->execute([$id]);
 }
 foreach(array_reverse($rows) as [$table,$id]){
  if($table==='lien_he'){
   $name=scalar('SELECT anh FROM lien_he WHERE id=?',[$id]);
   if(is_string($name) && preg_match('/^[a-f0-9]{32}\.(jpg|png|webp)$/',$name))@unlink(dirname(__DIR__).'/uploads/'.$name);
  }
  $pdo->prepare('DELETE FROM '.$table.' WHERE id=?')->execute([$id]);
 }
 foreach($jars as $jar)@unlink($jar);
}
echo "TOTAL $pass PASS / $fail FAIL\n";exit($fail?1:0);

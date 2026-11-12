<?php
/** CLI only. Runs on a disposable LOCAL copy of JadeHub; creates and removes test rows.
 * BASE=http://localhost:8000 php tests/huy-hoan-danh-gia.php
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
$fixtureUsers=[];$fixtureProduct=0;
function postAction(string $jar,string $csrf,string $action,int $order=0,array $extra=[]): array {
 return req($jar,'hau-mai.php',['csrf'=>$csrf,'hanh_dong'=>$action,'don_hang_id'=>$order]+$extra);
}
function makeOrder(string $method='cod'): int {
 global $fixtureProduct,$buyerId,$orders;
 $id=luuDonHang([$fixtureProduct=>2],$buyerId,'Regression after-sales',$method);$orders[]=$id;return $id;
}
try{
 foreach(['buyer','seller','outsider'] as $role){
  $email=$prefix.'-'.$role.'@example.test';
  $pdo->prepare('INSERT INTO nguoi_dung(email,ho_ten,mat_khau) VALUES(?,?,?)')->execute([$email,$prefix.' '.$role,password_hash('matkhau123',PASSWORD_DEFAULT)]);
  $fixtureUsers[$role]=(int)$pdo->lastInsertId();
  $clients[$role]=login($email);$tokens[$role]=token($clients[$role],'gio-hang.php');
 }
 $buyerId=$fixtureUsers['buyer'];$sellerId=$fixtureUsers['seller'];$b=$clients['buyer'];$bt=$tokens['buyer'];$seller=$clients['seller'];$st=$tokens['seller'];$other=$clients['outsider'];$ot=$tokens['outsider'];
 $admin=login('admin@ued.vn');$at=token($admin,'quan-tri.php');
 $pdo->prepare("INSERT INTO ho_so_nguoi_ban(nguoi_dung_id,ten_gian_hang,trang_thai) VALUES(?,?,'da_duyet')")->execute([$sellerId,$prefix.' shop']);
 $pdo->prepare("INSERT INTO san_pham(danh_muc_id,ma,ten,gia,ton_kho,nguoi_ban_id,anh,mo_ta) VALUES(1,?,?,12345,50,?,'JADE-02.jpg','Local test product')")->execute([$prefix,$prefix.' product',$sellerId]);$fixtureProduct=(int)$pdo->lastInsertId();
 $o=makeOrder();$stock=(int)scalar('SELECT ton_kho FROM san_pham WHERE id=?',[$fixtureProduct]);
 check('GET after-sales forbidden',req($b,'hau-mai.php')[0]===405);
 check('CSRF required',req($b,'hau-mai.php',['hanh_dong'=>'huy_don','don_hang_id'=>$o,'ly_do'=>'Testing cancellation'])[0]===403);
 check('Stranger cannot cancel',postAction($other,$ot,'huy_don',$o,['ly_do'=>'Testing cancellation'])[0]===403);
 check('Seller cannot cancel buyer order',postAction($seller,$st,'huy_don',$o,['ly_do'=>'Testing cancellation'])[0]===403);
 check('Array reason rejected',postAction($b,$bt,'huy_don',$o,['ly_do'=>['x']])[0]===422);
 check('Buyer cancellation succeeds',postAction($b,$bt,'huy_don',$o,['ly_do'=>'Testing cancellation'])[0]===303);
 check('Cancelled state',scalar('SELECT trang_thai FROM don_hang WHERE id=?',[$o])==='da_huy');
 check('Stock returned once',(int)scalar('SELECT ton_kho FROM san_pham WHERE id=?',[$fixtureProduct])===$stock+2);
 postAction($b,$bt,'huy_don',$o,['ly_do'=>'Testing cancellation again']);
 check('Repeat cancellation idempotent',(int)scalar('SELECT ton_kho FROM san_pham WHERE id=?',[$fixtureProduct])===$stock+2);
 check('Unpaid COD has no refund',(int)scalar('SELECT COUNT(*) FROM hoan_tien WHERE don_hang_id=?',[$o])===0);
 $shipped=makeOrder();doiTrangThaiDon($shipped,'dang_giao');
 check('Buyer cannot cancel shipped order',postAction($b,$bt,'huy_don',$shipped,['ly_do'=>'Testing cancellation'])[0]===422);
 check('No premature review',postAction($b,$bt,'danh_gia',$shipped,['san_pham_id'=>$fixtureProduct,'sao'=>5,'sao_doi_tac'=>5,'noi_dung'=>'Review before completion'])[0]===422);
 $paid=makeOrder('chuyen_khoan');
 $pdo->prepare("UPDATE thanh_toan SET trang_thai='da_xac_nhan',ma_tham_chieu='PAY-TEST-REF' WHERE don_hang_id=?")->execute([$paid]);
 check('Cancel paid order',postAction($b,$bt,'huy_don',$paid,['ly_do'=>'Paid order cancellation'])[0]===303);
 check('Paid cancellation opens refund',scalar('SELECT trang_thai FROM hoan_tien WHERE don_hang_id=?',[$paid])==='cho_hoan');
 check('Refund amount from server',(int)scalar('SELECT so_tien FROM hoan_tien WHERE don_hang_id=?',[$paid])===24690);
 check('Refund destination owner only',postAction($other,$ot,'tai_khoan_hoan',$paid,['tai_khoan_nhan'=>'FAKE outsider destination'])[0]===403);
 check('Refund confirmation admin only',postAction($b,$bt,'xu_ly_hoan',$paid,['xu_ly'=>'da_hoan','ma_giao_dich'=>'REFUND-ONE','ghi_chu'=>'Test actual refund'])[0]===403);
 check('No confirm without destination',postAction($admin,$at,'xu_ly_hoan',$paid,['xu_ly'=>'da_hoan','ma_giao_dich'=>'REFUND-ONE','ghi_chu'=>'Test actual refund'])[0]===422);
 $destination='TEST BANK 0000000000 Test Buyer';
 check('Owner saves destination',postAction($b,$bt,'tai_khoan_hoan',$paid,['tai_khoan_nhan'=>$destination,'so_tien'=>999999])[0]===303);
 check('Forged refund amount ignored',(int)scalar('SELECT so_tien FROM hoan_tien WHERE don_hang_id=?',[$paid])===24690);
 check('Refund list customer blocked',req($b,'hoan-tien.php')[0]===403);
 check('Bank data absent from public profile',!str_contains(req($other,'ho-so.php?id='.$buyerId)[1],$destination));
 check('Buyer sees own refund',str_contains(req($b,'don-hang.php?id='.$paid)[1],'Chờ hoàn tiền'));
 check('Refund code required',postAction($admin,$at,'xu_ly_hoan',$paid,['xu_ly'=>'da_hoan','ma_giao_dich'=>'','ghi_chu'=>'Test actual refund'])[0]===422);
 $refundRef=$prefix.'-REFUND';
 check('Admin records real-refund reference',postAction($admin,$at,'xu_ly_hoan',$paid,['xu_ly'=>'da_hoan','ma_giao_dich'=>$refundRef,'ghi_chu'=>'Test transfer completed'])[0]===303);
 check('Refund marked done',scalar('SELECT trang_thai FROM hoan_tien WHERE don_hang_id=?',[$paid])==='da_hoan');
 check('Repeat same refund is idempotent',postAction($admin,$at,'xu_ly_hoan',$paid,['xu_ly'=>'da_hoan','ma_giao_dich'=>$refundRef,'ghi_chu'=>'Test transfer completed'])[0]===303);
 check('Cannot overwrite paid refund reference',postAction($admin,$at,'xu_ly_hoan',$paid,['xu_ly'=>'da_hoan','ma_giao_dich'=>'DIFFERENT','ghi_chu'=>'Test transfer completed'])[0]===422);
 check('Completed destination immutable',postAction($b,$bt,'tai_khoan_hoan',$paid,['tai_khoan_nhan'=>'Changed account after refund'])[0]===422);
 $pending=makeOrder('chuyen_khoan');
 postAction($b,$bt,'huy_don',$pending,['ly_do'=>'Pending order cancellation']);
 check('Unverified transfer not automatically refunded',(int)scalar('SELECT COUNT(*) FROM hoan_tien WHERE don_hang_id=?',[$pending])===0);
 check('Report transfer after cancel',postAction($b,$bt,'bao_da_chuyen',$pending,['ma_tham_chieu'=>'SENT-BEFORE-CANCEL'])[0]===303);
 check('Claim is pending verification only',scalar('SELECT trang_thai FROM hoan_tien WHERE don_hang_id=?',[$pending])==='can_doi_soat');
 check('Cannot refund unverified claim',postAction($admin,$at,'xu_ly_hoan',$pending,['xu_ly'=>'da_hoan','ma_giao_dich'=>'UNVERIFIED','ghi_chu'=>'Attempt refund'])[0]===422);
 postAction($admin,$at,'xu_ly_hoan',$pending,['xu_ly'=>'chua_nhan_tien','ghi_chu'=>'No receipt found during test']);
 check('Admin can record no received funds',scalar('SELECT trang_thai FROM hoan_tien WHERE don_hang_id=?',[$pending])==='khong_phat_sinh');
 postAction($b,$bt,'bao_da_chuyen',$pending,['ma_tham_chieu'=>'NEW-RECEIPT-TEST']);
 check('Buyer can resubmit receipt',scalar('SELECT trang_thai FROM hoan_tien WHERE don_hang_id=?',[$pending])==='can_doi_soat');
 postAction($admin,$at,'xu_ly_hoan',$pending,['xu_ly'=>'da_nhan_tien','ghi_chu'=>'Received payment confirmed']);
 check('Verified receipt opens refund',scalar('SELECT trang_thai FROM hoan_tien WHERE don_hang_id=?',[$pending])==='cho_hoan');
 postAction($b,$bt,'tai_khoan_hoan',$pending,['tai_khoan_nhan'=>$destination]);
 check('Duplicate refund reference rejected',postAction($admin,$at,'xu_ly_hoan',$pending,['xu_ly'=>'da_hoan','ma_giao_dich'=>$refundRef,'ghi_chu'=>'Duplicate reference test'])[0]===409);
 check('Duplicate reference rolls back',scalar('SELECT trang_thai FROM hoan_tien WHERE don_hang_id=?',[$pending])==='cho_hoan');
 $o=makeOrder();doiTrangThaiDon($o,'dang_giao');doiTrangThaiDon($o,'hoan_thanh');
 check('Checkout snapshots seller',(int)scalar('SELECT nguoi_ban_id FROM chi_tiet_don WHERE don_hang_id=?',[$o])===$sellerId);
 $data=['san_pham_id'=>$fixtureProduct,'sao'=>2,'sao_doi_tac'=>3,'noi_dung'=>'<b>Honest review</b> & test'];
 check('Stranger cannot review transaction',postAction($other,$ot,'danh_gia',$o,$data)[0]===403);
 check('Score outside range rejected',postAction($b,$bt,'danh_gia',$o,array_replace($data,['sao'=>6]))[0]===422);
 check('Array score rejected',postAction($b,$bt,'danh_gia',$o,array_replace($data,['sao'=>[5]]))[0]===422);
 check('Buyer posts verified product/shop review',postAction($b,$bt,'danh_gia',$o,$data)[0]===303);
 check('Repeat review prevented',postAction($b,$bt,'danh_gia',$o,$data)[0]===409);
 check('Seller evaluates buyer',postAction($seller,$st,'danh_gia',$o,['san_pham_id'=>$fixtureProduct,'sao'=>4,'noi_dung'=>'Verified buyer communication'])[0]===303);
 check('Shop score from buyer reviews',thongKeDanhGia($sellerId,'san_pham')['diem']=='3.0');
 check('Buyer score from seller reviews',thongKeDanhGia($buyerId,'nguoi_mua')['diem']=='4.0');
 $html=req($other,'chi-tiet.php?id='.$fixtureProduct)[1];
 check('Product XSS escaped',str_contains($html,'&lt;b&gt;Honest review&lt;/b&gt;')&&!str_contains($html,'<b>Honest review</b>'));
 check('Shop/profile link present',str_contains($html,'ho-so.php?id='.$sellerId));
 $reviewId=(int)scalar("SELECT id FROM danh_gia_giao_dich WHERE don_hang_id=? AND loai='san_pham'",[$o]);
 check('Reply only by rated party',postAction($other,$ot,'phan_hoi',0,['danh_gia_id'=>$reviewId,'phan_hoi'=>'Unauthorized reply'])[0]===403);
 check('Seller can reply',postAction($seller,$st,'phan_hoi',0,['danh_gia_id'=>$reviewId,'phan_hoi'=>'<b>Shop response</b>'])[0]===303);
 check('Reply does not replace review',scalar('SELECT sao FROM danh_gia_giao_dich WHERE id=?',[$reviewId])===2);
 $profile=req($other,'ho-so.php?id='.$sellerId)[1];
 check('Public profile review + reply escaped',str_contains($profile,'&lt;b&gt;Shop response&lt;/b&gt;') && str_contains($profile,'3.0/5'));
 check('Buyer reputation visible',str_contains(req($seller,'ho-so.php?id='.$buyerId.'&vai_tro_dg=nguoi_mua')[1],'Verified buyer communication'));
 check('Seller own sold order visible',str_contains(req($seller,'don-ban.php')[1],$prefix.' product'));
 check('Unrelated seller no order disclosure',!str_contains(req($other,'don-ban.php')[1],$prefix.' product'));
 check('Public member/shop search',str_contains(req($other,'thanh-vien.php?q='.rawurlencode($prefix).'&loai=shop')[1],$prefix.' shop'));
 check('Search arrays safe',req($other,'thanh-vien.php?q%5B%5D=x&trang%5B%5D=1')[0]===200);
 check('Review page participant access',req($other,'danh-gia.php?don='.$o.'&sp='.$fixtureProduct)[0]===404);
 $guest=client();$redirect=req($guest,'tin-nhan.php?id='.$sellerId);
 check('Guest chat preserves target',str_contains($redirect[2],'nhan_tin_id='.$sellerId));
 $gt=token($guest,'dang-nhap.php?nhan_tin_id='.$sellerId);
 $res=req($guest,'dang-nhap.php',['csrf'=>$gt,'email'=>$prefix.'-buyer@example.test','mat_khau'=>'matkhau123','nhan_tin_id'=>$sellerId]);
 check('Login returns to intended chat',str_contains($res[2],'Location: tin-nhan.php?id='.$sellerId));
 check('Chat links trust profile',str_contains(req($b,'tin-nhan.php?id='.$sellerId)[1],'ho-so.php?id='.$sellerId.'#danh-gia'));
}catch(Throwable $e){check('Unhandled '.$e->getMessage(),false);}
finally{
 foreach($orders as $id)$pdo->prepare('DELETE FROM don_hang WHERE id=?')->execute([$id]);
 if($fixtureProduct)$pdo->prepare('DELETE FROM san_pham WHERE id=?')->execute([$fixtureProduct]);
 foreach($fixtureUsers as $id){
  $pdo->prepare('DELETE FROM ho_so_nguoi_ban WHERE nguoi_dung_id=?')->execute([$id]);
  $pdo->prepare('DELETE FROM nguoi_dung WHERE id=?')->execute([$id]);
 }
 foreach($jars as $jar)@unlink($jar);
}
echo "AFTER-SALES TOTAL $pass PASS / $fail FAIL\n";exit($fail?1:0);

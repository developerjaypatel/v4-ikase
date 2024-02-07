<?php
session_start();
session_write_close();

$time_start = microtime(true);

$uploaded     = $_POST["uploaded"]?: $_GET["uploaded"];
$batchscan_id = $_POST["batchscan_id"]?: $_GET["batchscan_id"];

$customer_dir = UPLOADS_PATH.$_SESSION['user_customer_id'].DC;
$file_path    = $customer_dir.$uploaded.".pdf";

//create a thumbnail
$image_magick = new imagick(); 
$image_magick->readImage($file_path);
$pages = $image_magick->getNumberImages();

$db = DB::conn(DB::DB_CASEUSER);
//update pages
$sql = "UPDATE cse_batchscan
SET pages = " . $pages . ",
processed = 'Y'
WHERE batchscan_id = " . $batchscan_id;
//die($sql);
try {	
	$stmt = DB::run($sql);	
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	die(json_encode($error));
}

for ($int=0;$int<$pages;$int++) {
	$file_path = $customer_dir . $uploaded . ".pdf[" . $int . "]";
	$thumbnail_path = $customer_dir . $uploaded . "_" . $int . ".png";
	$image_magick = new imagick();
	$image_magick->setBackgroundColor("white");
	$image_magick->readImage($file_path);
	$image_magick = $image_magick->flattenImages();
	$image_magick->setResolution(72,72);
	//$image_magick->thumbnailImage(102, 102, true);
	$image_magick->setImageFormat('png');
	
	$image_magick->writeImage($thumbnail_path);
}

//echo json_encode(array("success"=>"Y", "pages"=>$pages));
$time = microtime(true) - $time_start;	//execution time
//echo $time . '<br />';

$waitabit = rand(2, 5);
sleep($waitabit);
$time = microtime(true) - $time;	//execution time
//echo $start. "<br />" . $time . '<br />';
die("location:../../../phpOCR/discrete.php?uploaded=" . $uploaded . "&batchscan_id=" . $batchscan_id . "&page=0&pages=" . $pages);

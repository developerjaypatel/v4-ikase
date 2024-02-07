<?php
session_start();
session_write_close();

$msg = "async started";
include("../api/cls_logging.php");

ini_set('max_execution_time', 600); //300 seconds = 5 minutes

include_once("header.php");
include_once("functions.php");

//If you create a new font include file replace char_inc_6.php with your own
$conf['font_file']					= 'char_inc_highway80.php';


//The default output format. You can chose from xml,html,plain,template.
$conf['default_output_format']		= 'html';

//You shold probably not need to change thees
$conf['word_lines_min_dispersion']	= 0;
$conf['letters_min_dispersion']		= 0;

if (!isset($_SESSION['user_customer_id']) || $_SESSION['user_customer_id']=="") {
	die();
}
$customer_id = $_SESSION['user_customer_id'];
$user_id = $_SESSION['user_id'];
//passed variables
$uploaded = $_POST["uploaded"];
if ($uploaded=="") {
	$uploaded = $_GET["uploaded"];
}
if ($uploaded=="") {
	die("no file");
}

$batchscan_id = $_POST["batchscan_id"];
if ($batchscan_id=="") {
	$batchscan_id = $_GET["batchscan_id"];
}
if ($batchscan_id=="" || !is_numeric($batchscan_id)){
	die();
}
$pages = $_POST["pages"];
if ($pages=="") {
	$pages = $_GET["pages"];
}
if ($pages=="") {
	die("no pages");
}
$timestamp = $_POST["timestamp"];
if ($timestamp=="") {
	$timestamp = $_GET["timestamp"];
}
$db = getConnection();
$pipe = array();
$pipe_name = array();
for($page=0; $page < $pages; $page++) {
	//$image_path = $_SERVER["DOCUMENT_ROOT"] . "/uploads/" . $customer_id . "/" . $timestamp . "/" . $uploaded . "_" . $page . ".png";
	//get image from the imports folder
	$image_path = UPLOADS_PATH . $customer_id . "\\imports\\" . $timestamp . DC . $uploaded . "_" . $page . ".png";
	$image_name = $uploaded . "_" . $page . ".png";
	
	$filesize = filesize($image_path);
	//echo $image_name . " -> " . $filesize . "\r\n";
	//continue;
	if ($filesize > 3000 && $filesize < 4000) {
		//get consideration
		$sql = "SELECT consideration FROM cse_batchscan
		WHERE customer_id = " . $_SESSION['user_customer_id'] . " AND batchscan_id = " . $batchscan_id;
		
		try {
			$stmt = $db->query($sql);
			$stmt->execute();
			$consideration = $stmt->fetchObject();
		} catch(PDOException $e) {
			$error = array("error"=> array("text"=>$e->getMessage()));
			die(json_encode($error));
		}
	
		//break up into array
		if ($consideration->consideration!="") {
			$arrSep = explode("|", $consideration->consideration);
		} else {
			$arrConsider = array();
		}
		$arrConsideration[] = $page;
		if (count($arrConsider) > 0) {
			//add to array
			$arrConsideration = array_merge($arrConsideration, $arrConsider);
			$arrConsideration = array_filter($arrConsideration, "noEmpty");
			sort($arrConsideration);
			$arrConsideration = array_unique($arrConsideration);
		}
		
		//update consideration
		$sql = "UPDATE cse_batchscan
		SET consideration = '" . implode("|", $arrConsideration) . "'
		WHERE customer_id = " . $_SESSION['user_customer_id'] . " AND batchscan_id = " . $batchscan_id;
		//die($sql);
		try {	
			$stmt = DB::run($sql);
		} catch(PDOException $e) {
			$error = array("error"=> array("text"=>$e->getMessage()));
			die(json_encode($error));
		}
		
		$time_start = getmicrotime();
		$url = "https://www.ikase.website/phpOCR/separator_qr_multithread.php";
		$params = array('page'=>$page,'pages'=>$pages,'batchscan_id'=>$batchscan_id,'image_path'=>$image_name,'customer_id'=>$customer_id,'uploaded'=>$uploaded, 'timestamp'=>$timestamp, 'user_id'=>$user_id);
		$param = "page=" . $page . "&pages=" . $pages . "&batchscan_id=" . $batchscan_id . "&image_path=" . $image_name . "&customer_id=" . $customer_id . "&uploaded=" . $uploaded . "&timestamp=" . $timestamp . "&user_id=" . $user_id;
		
		$msg = "\r\n" . $url . "?" . $param . "
		";
		// set path and name of log file (optional)
		$log->lwrite($msg);
		//die($msg);
		curl_post_async($url, $params);
	}
}

//update separators
$sql = "UPDATE cse_batchscan
SET separated = 'Y'
WHERE customer_id = " . $customer_id . " AND batchscan_id = " . $batchscan_id;

try {	
	$stmt = DB::run($sql);		
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	die(json_encode($error));
}

$msg = "\r\n" . $sql . "
";
// set path and name of log file (optional)
$log->lwrite($msg);
		
//update completion
$sql = "UPDATE cse_batchscan
SET `match` = '-1'
WHERE 1
AND customer_id = " . $customer_id . " AND batchscan_id = " . $batchscan_id;
try {	
	$stmt = DB::run($sql);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	die(json_encode($error));
}

$msg = "\r\n" . $sql . "
";
// set path and name of log file (optional)
$log->lwrite($msg);

echo json_encode(array("stacks"=>1, "url"=>$url,  "params"=>$param));


$msg = "
async completed " . $batchscan_id . "
";
// set path and name of log file (optional)
$log->lwrite($msg);
// close log file
$log->lclose();
?>

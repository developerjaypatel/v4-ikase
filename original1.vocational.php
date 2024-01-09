<?php 
error_reporting(E_ALL);
error_reporting(error_reporting(E_ALL) & (-1 ^ E_DEPRECATED));

if(empty($_GET['key'])){
	die("no so");
}
//get the prefix if any from the customer id
//include ("text_editor/ed/functions.php");
//include ("text_editor/ed/datacon.php");
include("api/connection.php");
$key = passed_var("key", "get");

//die("key:" . $key );
$boolAllowMultipleDownload = true;	//download as many times as you wish until expiration date

//check the DB for the key
$sql = "SELECT * 
FROM ikase.cse_downloads 
WHERE downloadkey = :key 
LIMIT 1";
try { 
	$db = getConnection();
	$stmt = $db->prepare($sql);  
	$stmt->bindParam("key", $key);
	$stmt->execute();
	$arrCheck = $stmt->fetchObject();
	$stmt = null; $db = null;


	//die($sql);
	//$resCheck = mysql_query($sql);
	//$arrCheck = mysql_fetch_assoc($resCheck);
	//die(print_r($arrCheck));
	if(strtotime($arrCheck->expires)>=time()){
		if(!$arrCheck->downloads OR $boolAllowMultipleDownload){
			//move through
			//update the DB to say this file has been downloaded
			//mysql_query("UPDATE ikase.cse_downloads SET downloads = downloads + 1 WHERE downloadkey = '".mysql_real_escape_string($_GET['key'])."' LIMIT 1");
			$sql = "UPDATE ikase.cse_downloads 
			SET downloads = downloads + 1 
			WHERE downloadkey = '" . mysql_real_escape_string($key) . "' 
			LIMIT 1";
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			$stmt = null; $db = null;
		} else {
			//this file has already been downloaded and multiple downloads are not allowed
			die( "This file has already been downloaded.");
		}
	} else {
		//this download has passed its expiry date
		die("This download has expired.");
	}
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
}
//die(print_r($arrCheck));

$injury_id = $arrCheck->injury_id;
$customer_id = $arrCheck->customer_id;

if (!is_numeric($injury_id)) {
	die("no i");
}
if (!is_numeric($customer_id)) {
	die("no c");
}

if (strpos($arrCheck->file, "refervocational") !== false) {
	$path = $arrCheck->file;
	$path = str_replace("../uploads", "uploads", $path);
	//die($path);
	$filename = explode("/", $path);
	$filename = $filename[count($filename) - 1];
	//die("filename:" . $filename);
	header('Content-Transfer-Encoding: binary');  // For Gecko browsers mainly
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($path)) . ' GMT');
	header('Accept-Ranges: bytes');  // Allow support for download resume
	header('Content-Length: ' . filesize($path));  // File size
	header('Content-Encoding: none');
	header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');  // Change the mime type if the file is not PDF
	header('Content-Disposition: attachment; filename=' . $filename);  // Make the browser display the Save As dialog
	readfile($path);  // This is necessary in order to get it to actually download the file, otherwise it will be 0Kb

	die();
}
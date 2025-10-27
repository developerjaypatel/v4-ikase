<?php 
error_reporting(E_ALL);
error_reporting(error_reporting(E_ALL) & (-1 ^ E_DEPRECATED));

//die(print_r($_REQUEST));

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
$file = $arrCheck->file;
$arrFile = explode("/", $file);

$customer_id = $arrFile[0];
$case_id = $arrFile[1];

if (!is_numeric($case_id)) {
	die("no c");
}
if (!is_numeric($customer_id)) {
	die("no cs");
}

$file = "https://v4.ikase.org/uploads/" . $file . "/demographics.html";

//die($file);
$page = file_get_contents($file);
echo $page;
die();
?>
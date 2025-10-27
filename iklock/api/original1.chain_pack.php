<?php
//
//if ($_SESSION['user_customer_id']==1033 ||  $_SERVER['REMOTE_ADDR']=='98.148.194.252') {
	//error_reporting(E_ALL);
	//ini_set('display_errors', '1');	
	
	$filename = "C:\\inetpub\\wwwroot\\ikase.org\\iklock\\api\\chain.config";
	
	$handle = fopen($filename, "r");
	$contents = fread($handle, filesize($filename));
	fclose($handle);
	
	$key = base64_decode($contents);
	$key = base64_decode($key);
	$key = base64_decode($key);
	
	define("CRYPT_KEY", $key);
	
//	die("ok");
//} else {

//}
?>

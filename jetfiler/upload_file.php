<?php
//Set no caching
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
header("Cache-Control: no-store, no-cache, must-revalidate"); 
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

header("strict-transport-security: max-age=600");
header('X-Frame-Options: SAMEORIGIN');
header("X-XSS-Protection: 1; mode=block");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); 

//print_r($_REQUEST);//die;
require_once('../shared/legacy_session.php');
include("../api/connection.php");
include("functions.php");

session_write_close();
if($_SERVER["HTTPS"]=="off") {
	
	header("location:https://v4.ikase.org" . $_SERVER['REQUEST_URI']);
}

if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
	//die(print_r($_SESSION));
	//header("location:../index.php");
	die("<script language='javascript'>parent.location.href='../index.php'</script>");
}

session_write_close();

$cus_id = $_SESSION['user_customer_id'];
$client_id = passed_var("client_id");
$case_id = passed_var("case_id");
$uploads = passed_var("uploads");
$form = passed_var("form");
$injury_id = passed_var("injury_id");
$jetfile_id = passed_var("jetfile_id");

//what did we get
require("cls_fileupload.php");
$uploadDir = 'D:\\uploads\\' . $_SESSION['user_customer_id'] . '\\' . $case_id . '\\jetfiler\\';

//die($_SERVER['DOCUMENT_ROOT'] . $uploadDir);
if (!is_dir($uploadDir)) {
	mkdir($uploadDir, 0755, true);
}
//$path = "D:/uploads/" . $_SESSION['user_customer_id'] . "/" . $case_id . "/jetfiler/";
//$path = $_SERVER['DOCUMENT_ROOT'] . $uploadDir;
$path = $uploadDir;
//die($path);
$acceptable_file_types = "application/pdf";
$arrUploads = array();
$arrNames = array();
$arrKaseDocs = array();
$arrKaseNames = array();
//die(print_r($_POST));
//die(print_r($uploads));
for ($int=1;$int<($uploads+1);$int++) {
	$my_uploader = new uploader($_POST['en']);
	$my_uploader->max_filesize(50000000); // 20000 kb
	$my_uploader->max_image_size(50000, 50000); // max_image_size($width, $height)
	$upload_file_browser = "file_up_" . $int;
	$upload_file_name = passed_var("file_name_" . $int);
	$stored_file_name = passed_var("file_stored_" . $int);
	
	$default_extension = "";
	$mode = 1;
	if ($my_uploader->upload($upload_file_browser, $acceptable_file_types, $default_extension)) {
		/*
		$my_uploader->save_file($path, $mode);
		//now that the file has been uploaded, shrink it
		$upfilename1 = $path . $case_id . "_" . $my_uploader->file['name'];
		$arrUploads[] = $upfilename1;
		$arrNames[] = $upload_file_name;
		*/
		$arrFile = explode(".", $my_uploader->file['name']);
		$extension = $arrFile[count($arrFile)-1];
		$my_uploader->file['name'] = str_replace("." . $extension, "_" . $case_id . "." . $extension, $my_uploader->file['name']);
		
		$my_uploader->save_file($path, $mode);
		
		$upfilename1 = $my_uploader->file['name'];
		
		$arrUploads[] = $upfilename1;
		$arrNames[] = $upload_file_name;
		
		//echo $upfilename1. " has been uploaded.<br>";
	} else {
		//echo "could not upload ".$upload_file_name . "<br />";
		$arrKaseNames[] = $upload_file_name;
		$arrKaseDocs[] = $stored_file_name;
		/*
		if (!is_numeric($stored_file_name)) {
			$arrUploads[] = str_replace("D:/uploads/" . $cus_id . "/" . $case_id . "/jetfiler/", "", $stored_file_name);
			$arrNames[] = $upload_file_name;
		} else {
			$arrKaseNames[] = $upload_file_name;
			$arrKaseDocs[] = $stored_file_name;
		}
		*/
		//die();
	}
}
//die(print_r($arrKaseNames));
include("process_documents.php");

set_time_limit(60);

header ("location:app_1_2.php?case_id=" . $case_id . "&injury_id=" . $injury_id . "&jetfile_id=" . $jetfile_id);
?>

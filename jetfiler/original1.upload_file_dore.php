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

error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
ini_set('display_errors', '1');


include("../api/manage_session.php");
include("../api/connection.php");
include("functions.php");

if($_SERVER["HTTPS"]=="off") {
	
	header("location:https://v2.ikase.org" . $_SERVER['REQUEST_URI']);
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
$uploadDir = '\\uploads\\' . $_SESSION['user_customer_id'] . '\\jetfiler\\';
if ($case_id != "") {
	$uploadDir .= $case_id . '\\';
}
//die($_SERVER['DOCUMENT_ROOT'] . $uploadDir);
if (!is_dir($_SERVER['DOCUMENT_ROOT'] . $uploadDir)) {
	mkdir($_SERVER['DOCUMENT_ROOT'] . $uploadDir, 0755, true);
}
$path = "../uploads/" . $_SESSION['user_customer_id'] . "/" . $case_id . "/jetfiler/";

$acceptable_file_types = "";
$arrUploads = array();
$arrNames = array();
for ($int=1;$int<($uploads+1);$int++) {
	$upload_file_browser = "file_up_" . $int;
	$upload_file_name = passed_var("file_name_" . $int);
	$stored_file_name = passed_var("file_stored_" . $int);
	//echo $upload_file_name . "<br />";
	$default_extension = "";
	$mode = 1;
	$my_uploader = new uploader('en');
	$my_uploader->max_filesize(50000000); // 20000 kb
	$my_uploader->max_image_size(50000, 50000); // max_image_size($width, $height)

	//$my_uploader->error = "";
	//$my_uploader->errors = array();
	if ($my_uploader->upload($upload_file_browser, $acceptable_file_types, $default_extension)) {
		$arrFile = explode(".", $my_uploader->file['name']);
		$extension = $arrFile[count($arrFile)-1];
		$my_uploader->file['name'] = str_replace("." . $extension, "_dore_" . $case_id . "." . $extension, $my_uploader->file['name']);
		//echo "uploaded:" . $my_uploader->file['name'] . "<br />";
		$my_uploader->save_file($path, $mode);
		
		$upfilename1 = $my_uploader->file['name'];
		
		$arrUploads[] = $upfilename1;
		$arrNames[] = $upload_file_name;
		
		echo $upfilename1. " has been uploaded.<br>";
	} else {
		echo "could not upload ".$upload_file_name . "<br />";
		//put in the name of the file from form
		$arrUploads[] = str_replace("uploads/", "", $stored_file_name);
		$arrNames[] = $upload_file_name;
	}
}

include("process_documents.php");

set_time_limit(60);

header ("location:upload_dore.php?case_id=" . $case_id . "&injury_id=" . $injury_id . "&jetfile_id=" . $jetfile_id);
?>
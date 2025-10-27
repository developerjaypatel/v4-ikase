<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
ini_set('display_errors', '1');

include("connection.php");
require_once('../shared/legacy_session.php');

$file = passed_var("file", "get");
$case_id = passed_var("case_id", "get");
$folder = passed_var("folder", "get");
$extension = "pdf";
if ($folder=="zips") {
	$extension = "zip";
}
$file = str_replace("../", "", $file);

$destination = "kase_bill__" . $file;
if (strpos($file, "kase_invoice")!==false) {
	$destination = $file;
}
if ($extension == "pdf") {
	$iframe = '<iframe id="medindex_frame" src="https://'. $_SERVER['SERVER_NAME'] .'/uploads/' . $_SESSION["user_customer_id"] . "/" . $case_id . "/" . $folder . "/" . $destination . '".' . $extension . '" width="100%" height="800px"></iframe>';
	echo $iframe;
	
	die();
} else {
	$archive_file_name = 'D:/uploads/' . $_SESSION["user_customer_id"] . "/" . $folder. "/" . $case_id . "/" . $destination . '".' . $extension;
	
	header("Content-type: application/zip"); 
    header("Content-Disposition: attachment; filename=$archive_file_name"); 
    header("Pragma: no-cache"); 
    header("Expires: 0"); 
    readfile("$archive_file_name");
    exit;
}



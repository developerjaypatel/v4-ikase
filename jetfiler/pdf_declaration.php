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
require_once('../shared/legacy_session.php');
include("../api/connection.php");

if($_SERVER["HTTPS"]=="off") {
	die("no go");
}

if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
	die("no go");
}

session_write_close();


$case_id = passed_var("case_id", "post");
$injury_id = passed_var("injury_id", "post");
$jetfile_id = passed_var("jetfile_id", "post");
$cus_id = $_SESSION['user_customer_id'];
$nopublish = passed_var("nopublish", "post");
//die("nopub:" . $nopublish);

if ($case_id == "" || $case_id < 0 || !is_numeric($case_id)) {
	die("no case");
}
if ($injury_id == "" || $injury_id < 0 || !is_numeric($injury_id)) {
	die("no inj");
}
//for page 2, we need to have saved page 1
if ($jetfile_id == "" || $jetfile_id < 0 || !is_numeric($jetfile_id)) {
	die("no jet");
}

$form = "verification";

include("jetfile_kase.php");

$eams_no = $customer->eams_no;
$cus_name = $customer->cus_name;
$cus_name_first = $customer->cus_name_first;
$cus_name_middle = $customer->cus_name_middle;
$cus_name_last = $customer->cus_name_last;

$cus_signature = $cus_name_first;
if ($cus_name_middle!="") {
	$cus_signature .= " " . $cus_name_middle;
}
if ($cus_name_last!="") {
	$cus_signature .= " " . $cus_name_last;
}
//however, if we're logged in as user
if ($user_name!="") {
	$cus_signature = $user_name;
}

//fdf
$filename =  "pdf/4903_8_declaration.fdf";
$somecontent = file_get_contents($filename);


pdfReplacementJetFile("[[S SIGNATURE]]", "S " . $cus_signature, $somecontent);
//output
$host = $_SERVER['HTTP_HOST'];
$host = str_replace("www.", "", $host);
pdfReplacementJetFile("[[DESTINATION]]", "www." . $host, $somecontent);

$filename = "4903_8_out.fdf";
$filename_output = "4903_8_" . $case_id . ".pdf";

if (file_exists("pdf/" . $filename)) {
	unlink($filename);
}
if (!$handle = fopen("pdf/" . $filename, 'w')) {
	 echo "Cannot open file ($filename)";
	 exit;
}

// Write $somecontent to our opened file.
if (fwrite($handle, $somecontent) === FALSE) {
   echo "Cannot write to file ($filename)";
   exit;
}

if ($nopublish=="y") {
	$destination_folder = "../uploads/" . $_SESSION['user_customer_id'] . "/" . $case_id . "/jetfiler/";
	if (!is_dir($destination_folder)) {
		mkdir($destination_folder, 0755, true);
	}
	$filename = $_SERVER['DOCUMENT_ROOT'] . "\\jetfiler\\pdf\\" . $filename;
	$display_filename_output = $filename_output;
	$filename_output = UPLOADS_PATH . $_SESSION['user_customer_id'] . DC . $case_id . "\\jetfiler\\" . $filename_output;
	$source_pdf = $_SERVER['DOCUMENT_ROOT'] . "\\jetfiler\\pdf\\4903_8_declaration.pdf";
	passthru("pdftk " . $source_pdf . " fill_form " . $filename. " output " . $filename_output);
	//exit;

	echo $display_filename_output;
} else {
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
	//header('Content-type: application/pdf');
	header('Content-type: application/vnd.fdf');
	
	// It will be called downloaded.pdf
	header('Content-Disposition: attachment; filename="' . $filename . '"');
	
	// The PDF source is in original.pdf
	readfile($filename);
}

?>

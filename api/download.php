<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$blnIncluded = true;
if (!isset($path)) {
	$blnIncluded = false;
	$path = $_GET["file"];
	$dotpos = strpos($path, "../");
	if ($dotpos===false) {
		$path = "" . $path;
	}
}
$currentmodif = filemtime($path);

if ($blnIncluded) {
//	die("filename:" . $path . "\r\nmodif:" . date("m/d/Y H:i:s", $currentmodif));
}
$filename = explode("/", $path);
//$filename = basename($filename);
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


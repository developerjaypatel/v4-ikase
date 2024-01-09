<?php
include ("connection.php");

$contents = $HTTP_RAW_POST_DATA;
$contents = str_replace(">><<", ">>\r\n<<", $contents);
$filename = ROOT_PATH . "\\chats\\-1\\raw4.html";
$fptr = fopen($filename, "w"); 
fputs($fptr, $contents); 
fclose($fptr);
die();
/*

*/
//
//$contents = cleanWords($contents);


$handle = fopen($filename, "r");
$contents = fread($handle, filesize($filename));
fclose($handle);
/*

$fptr = fopen($filename, "w"); 
fputs($fptr, $contents); 
fclose($fptr);

die("<a href='../chats/-1/raw3.html'>Raw3.html</a>");

/*
$filename1 = $_SERVER['DOCUMENT_ROOT'] . "\\chats\\-1\\out.txt";
$fp = fopen($filename1, 'w');
*/
$strpos = strpos($contents, "/Type/Filespec/UF(");
$endpos = strpos($contents, ")>>/Fields");
$path = substr($contents, $strpos, ($endpos - $strpos));
$path = str_replace("/Type/Filespec/UF(", "", $path);
$arrPath = explode("/", $path);
$path = $arrPath[count($arrPath) - 1];

die($path);

$strpos = strpos($contents, "<</T(customer_id)/V(");
$endpos = strpos($contents, ")>><</T(form_name)/V");
$customer_id = substr($contents, $strpos, ($endpos - $strpos));
$customer_id = str_replace("<</T(customer_id)/V(", "", $customer_id);
$customer_id = substr($customer_id, 2);
$customer_id = cleanWord($customer_id);

$strpos = strpos($contents, "><</T(form_name)/V(");
$endpos = strpos($contents, ")>><</T(case_id)/V(");
$form_name = substr($contents, $strpos, ($endpos - $strpos));
$form_name = str_replace("><</T(form_name)/V(", "", $form_name);
$form_name = substr($form_name, 2);
$form_name = strtolower($form_name);
$form_name = cleanWord($form_name);

$strpos = strpos($contents, "<</T(case_id)/V(");
$endpos = strpos($contents, ")>>", $strpos);
$case_id = substr($contents, $strpos, ($endpos - $strpos));
$case_id = str_replace("<</T(case_id)/V(", "", $case_id);
$case_id = substr($case_id, 2);
$case_id = cleanWord($case_id);
/*
fwrite($fp, 'path:');
fwrite($fp, $path);
fwrite($fp, '
');

fwrite($fp, 'customer_id:');
fwrite($fp, $customer_id);
fwrite($fp, '
');

fwrite($fp, 'case_id:');
fwrite($fp, $case_id);
fwrite($fp, '
');
fwrite($fp, 'form_name:');
fwrite($fp, $form_name);

fclose($fp);
die();
*/

$source_dir = $_SERVER['DOCUMENT_ROOT'] . '\\eams_forms\\';
$file_dir = UPLOADS_PATH . $customer_id . DC . $case_id . DC;
if (!file_exists($file_dir)) {
	mkdir($file_dir);
}
$file_dir .=  "eams_forms\\";
if (!file_exists($file_dir)) {
	mkdir($file_dir);
}

$filename = $file_dir . $form_name . '.fdf';

if (file_exists($filename)) {
	//die("exists:" . $filename);
	unlink($filename);
}
//die("NOT exists:" . $filename);
$fptr = fopen($filename, "w"); 
fputs($fptr, $contents); 
fclose($fptr);

$pdftk_output =  $file_dir . $path;
$cmd = "pdftk " . $source_dir . $form_name . ".pdf fill_form " . $filename . " output " . $pdftk_output . " 2>&1";

die($cmd);

$filename1 = $_SERVER['DOCUMENT_ROOT'] . "\\chats\\-1\\out.txt";
$fp = fopen($filename1, 'w');
fwrite($fp, $cmd);
fwrite($fp, '
file:
');
fwrite($fp, $filename);
fclose($fp);
//die();
system($cmd, $retval);

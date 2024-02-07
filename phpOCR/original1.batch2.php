<?php
ini_set('max_execution_time', 300); //300 seconds = 5 minutes
$uploaded = $_POST["uploaded"];
if ($uploaded=="") {
	$uploaded = "separated";
}
$pages = $_POST["pages"];
if ($pages=="") {
	$pages = 21;
}
include_once("header.php");

/**************************************************************************************************/
//MAIN
/**************************************************************************************************/

//If you create a new font include file replace char_inc_6.php with your own
$conf['font_file']					= 'char_inc_highway.php';


//The default output format. You can chose from xml,html,plain,template.
$conf['default_output_format']		= 'html';

//You shold probably not need to change thees
$conf['word_lines_min_dispersion']	= 0;
$conf['letters_min_dispersion']		= 0;

$arrSeparators = array();
for ($jnt=0;$jnt<$pages;$jnt++) { 
	$image_path = $_SERVER["DOCUMENT_ROOT"] . "/uploads/" . $uploaded . "_" . $jnt . ".png";
	include("separator.php");
}
//get the stacks
$arrStack = array();
$document_count = 0;
for ($jnt=0;$jnt<$pages;$jnt++) {
	if (in_array($jnt, $arrSeparators)) {
		$document_count++;
		continue;
	}
	$arrStack[$document_count][] = $jnt;
}
//die(print_r($arrStack));
$timestamp = time();
for($int=0;$int<count($arrStack);$int++) {
	$new_pdf_path = $_SERVER["DOCUMENT_ROOT"] . "/uploads/stitched2_" . $timestamp . "_" . $int . ".pdf";
	$arrList = array();
	
	foreach($arrStack[$int] as $stack_item) {
		$arrList[] = $_SERVER["DOCUMENT_ROOT"] . "/uploads/" . $uploaded . ".pdf[" . $stack_item . "]";
	}
	
	$document_list = implode(" ", $arrList);
	
	exec("convert -density 150 " . $document_list . " " . $new_pdf_path);
	//die("convert -density 150 " . $document_list . " " . $new_pdf_path);
}
$source = "<a href='../../web/uploads/" . $uploaded . ".pdf' target='_blank'>Source</a>";
/*
$arrOuptuts = array();
for($int=0;$int<count($arrStack);$int++) {
	$new_pdf_path = "stitched_" . $timestamp . "_" . $int . ".pdf";
	$arrOuptuts[] = "<div><a href='../../web/uploads/" . $new_pdf_path . "' target='_blank'>" . $new_pdf_path . "</a></div>";
}
*/
echo json_encode(array("success"=>"Y", "source"=>$source, "stacks"=>count($arrStack), "timestamp"=>$timestamp));

?>
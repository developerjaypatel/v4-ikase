<?php
session_start();
include_once("functions.php");

if (!isset($_SESSION['user_customer_id']) || $_SESSION['user_customer_id']=="") {
	return false;
}

ini_set('max_execution_time', 600); //300 seconds = 5 minutes

//passed variables
$uploaded = $_POST["uploaded"];
if ($uploaded=="") {
	$uploaded = $_GET["uploaded"];
}
if ($uploaded=="") {
	die("no file");
}

$batchscan_id = $_POST["batchscan_id"];
if ($batchscan_id=="") {
	$batchscan_id = $_GET["batchscan_id"];
}
if ($batchscan_id=="" || !is_numeric($batchscan_id)){
	die();
}
$pages = $_POST["pages"];
if ($pages=="") {
	$pages = $_GET["pages"];
}
if ($pages=="") {
	die("no pages");
}
$page = $_POST["page"];
if ($page=="") {
	$page = $_GET["page"];
}
if ($page=="") {
	die("no page");
}
if ($next_page >= $pages) {
	die("done");
}

$image_path = $_SERVER["DOCUMENT_ROOT"] . "/uploads/" . $_SESSION['user_customer_id'] . "/" . $uploaded . "_" . $page . ".png";

$filesize = filesize($image_path);

//die($filesize . " <==");
$db = getConnection();
if ($filesize < 1700) {
	include_once("header.php");
	
	/**************************************************************************************************/
	//MAIN
	/**************************************************************************************************/
	
	//If you create a new font include file replace char_inc_6.php with your own
	$conf['font_file']					= 'char_inc_highway80.php';
	
	
	//The default output format. You can chose from xml,html,plain,template.
	$conf['default_output_format']		= 'html';
	
	//You shold probably not need to change thees
	$conf['word_lines_min_dispersion']	= 0;
	$conf['letters_min_dispersion']		= 0;
	
	$arrSeparators = array();
	
	include("separator.php");
	
	if (count($arrSeparators)>0) {
		//get separators
		$sql = "SELECT separators FROM cse_batchscan
		WHERE customer_id = " . $_SESSION['user_customer_id'] . " AND batchscan_id = " . $batchscan_id;
		
		try {
			$stmt = $db->query($sql);
			$stmt->execute();
			$batchscan = $stmt->fetchObject();
		} catch(PDOException $e) {
			$error = array("error"=> array("text"=>$e->getMessage()));
			die(json_encode($error));
		}
	
		//break up into array
		if ($batchscan->separators!="") {
			$arrSep = explode("|", $batchscan->separators);
		} else {
			$arrSep = array();
		}
		//add to array
		$arrSeparators = array_merge($arrSeparators, $arrSep);
		$arrSeparators = array_filter($arrSeparators, "noEmpty");
		sort($arrSeparators);
		//die(print_r($arrSeparators));
		
		//update separators
		$sql = "UPDATE cse_batchscan
		SET separators = '" . implode("|", $arrSeparators) . "'
		WHERE customer_id = " . $_SESSION['user_customer_id'] . " AND batchscan_id = " . $batchscan_id;
		try {	
			$stmt = $db->prepare($sql);
			$stmt->execute();
		} catch(PDOException $e) {
			$error = array("error"=> array("text"=>$e->getMessage()));
			die(json_encode($error));
		}
	}
}
$next_page = $page + 1;

if ($next_page == $pages) {
	//update separators
	$sql = "UPDATE cse_batchscan
	SET separated = 'Y'
	WHERE customer_id = " . $_SESSION['user_customer_id'] . " AND batchscan_id = " . $batchscan_id;
	
	try {	
		$stmt = $db->prepare($sql);
		$stmt->execute();		
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
		die(json_encode($error));
	}
	header("location:separate.php?uploaded=" . $uploaded . "&pages=" . $pages . "&batchscan_id=" . $batchscan_id);
} else {
	$waitabit = rand (1, 3);
	sleep($waitabit);
	header("location:discrete.php?uploaded=" . $uploaded . "&page=" . $next_page . "&pages=" . $pages . "&batchscan_id=" . $batchscan_id);
}
?>
<?php
require_once('../shared/legacy_session.php');
session_write_close();
if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
	header("location:../index.php");
	die();
}

include("../api/connection.php");

function runLastQuery($sql) {
	
	//die($sql);
	
	ini_set('memory_limit', '256M');
	// die(ini_get('memory_limit'));
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt = $db->query($sql);
		$kases = $stmt->fetchAll(PDO::FETCH_OBJ);
		return $kases;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
include ("report_functions.php");

$sql = getKases("", "show_all");
//die($sql);
$cases = runLastQuery($sql);

$list = array ();
$customer_id = $_SESSION['user_customer_id'] ;
$uploadDir = UPLOADS_PATH . $customer_id . "\\exports";
//die($uploadDir);
if (!file_exists($uploadDir)) {
	mkdir($uploadDir, 0755, true);
}

$blnHeader = false;
$arrOutputColumns = array("case_id", "file_number", "case_number", "case_name", "cpointer", "name", "full_name", "applicant_full_address", "applicant_email", "dob");
$cases_clean = array();
$arrFileNumbers = array();

foreach ($cases as $row) {
	$row_clean = array();
	foreach($row as $column=>$value) {
		//echo $column . " - ";
		if (in_array($column, $arrOutputColumns)) {
			//echo "in";
			$row_clean[$column] = $value;
		}
		//echo "\r\n";
	}
	
	if ($row_clean["dob"]=="") {
		continue;
	}
	if ($row_clean["file_number"]=="" && $row_clean["case_number"]!="") {
		$row_clean["file_number"] = $row_clean["case_number"];
	}
	if ($row_clean["file_number"]=="" && $row_clean["cpointer"]!="") {
		$row_clean["file_number"] = $row_clean["cpointer"];
	}
	
	
	if ($row_clean["case_name"]=="" && $row_clean["name"]!="") {
		$row_clean["case_name"] = $row_clean["name"];
	}
	
	if (!in_array($row_clean["file_number"], $arrFileNumbers)) {
		$arrFileNumbers[] = $row_clean["file_number"];
		//die(print_r($row_clean));
		array_push($cases_clean, $row_clean);
	}
}

foreach ($cases_clean as $row) {
	unset($row->case_id);
	unset($row->file_number);
	unset($row->case_number);
	unset($row->name);
	unset($row->cpointer);
	
	//die(print_r($row));
	if (!$blnHeader) {
		$blnHeader = true;
		$array = array();
		foreach($row as $column=>$values) {
			array_push($array, $column);
		}
		array_push($list, array_values($array));
	}	
	array_push($list, array_values($row));
}
//output file
$filename = 'D:/uploads/' . $customer_id . '/exports/casedobs_' . date('mdy') . '.csv';
$fp = fopen($filename, 'w');

foreach ($list as $ferow) {
	fputcsv($fp, $ferow);
}
fclose($fp);

//die("done");
$path = $filename;
$filename = explode("/", $path);
$filename = $filename[count($filename) - 1];

header('Content-Transfer-Encoding: binary');  // For Gecko browsers mainly
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($path)) . ' GMT');
header('Accept-Ranges: bytes');  // Allow support for download resume
header('Content-Length: ' . filesize($path));  // File size
header('Content-Encoding: none');
//header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');  // Change the mime type if the file is not PDF
header( 'Content-Type: text/csv' );
header('Content-Disposition: attachment; filename=' . $filename);  // Make the browser display the Save As dialog
readfile($path);  // This is necessary in order to get it to actually download the file, otherwise it will be 0Kb
?>

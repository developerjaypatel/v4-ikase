<?php 
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
require_once('../shared/legacy_session.php');

die("off");

session_write_close();

if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
	header("location:../index.php");
	die();
}

include("connection.php");

function runLastQuery() {
	if (!isset($_SESSION["current_kase_query"])) {
		return false;
	}	
	$sql = $_SESSION["current_kase_query"];

	die($sql);
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

$url = "https://". $_SERVER['SERVER_NAME'] ."/api/kases/last";
$cases = runLastQuery();

$list = array ();
$customer_id = $_SESSION['user_customer_id'] ;
$uploadDir = UPLOADS_PATH . $customer_id . "\\exports";
//die($uploadDir);
if (!file_exists($uploadDir)) {
	mkdir($uploadDir, 0755, true);
}

$blnHeader = false;
$arrOutputColumns = array("case_id", "case_number", "file_number", "case_type", "adj_number", "injury_type", "venue_abbr", "case_status", "full_name", "applicant_email", "applicant_phone", "start_date", "end_date", "statute_limitation", "case_name", "attorney_full_name", "worker_full_name", "closed_date");
$cases_clean = array();
foreach ($cases as $row) {
	$row_clean = array();
	foreach($row as $column=>$value) {
		//die(print_r($row));
		if (in_array($column, $arrOutputColumns)) {
			$row_clean[$column] = $value;
		}
	}
	if ($row_clean["file_number"]=="" && $row_clean["case_number"]!="") {
		$row_clean["file_number"] = $row_clean["case_number"];
	}
	unset($row_clean["case_number"]);
	//die(print_r($row_clean));
	array_push($cases_clean, $row_clean);
}

//die(print_r($cases_clean));

foreach ($cases_clean as $row) {
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
$filename = '../uploads/' . $customer_id . '/exports/cases_' . date('mdy') . '.csv';
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

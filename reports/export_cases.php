<?php
require_once('../shared/legacy_session.php');
session_write_close();

if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
	header("location:../index.php");
	die();
}

include("../api/connection.php");

function runLastQuery() {
	session_write_close();
	if (!isset($_SESSION["current_kase_query"])) {
		return false;
	}
	$search_term = '';
	if (isset($_SESSION["current_kase_search_term"])) {
		$search_term = $_SESSION["current_kase_search_term"];
	}
	$sql = strtolower($_SESSION["current_kase_query"]);
	if (strpos($sql, " distinct") === false) {
		$sql = str_replace("select ", "select distinct '" . $search_term . "' search_term, ", $sql);
	} else {
		$sql = str_replace("distinct", "distinct '" . $search_term . "' search_term,", $sql);
	}
	//die($sql);
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

$url = "https://www.ikase.website/api/kases/last";
$cases = runLastQuery();

$list = array ();
$customer_id = $_SESSION['user_customer_id'] ;
$uploadDir = UPLOADS_PATH . $customer_id . "\\exports";
//die($uploadDir);
if (!file_exists($uploadDir)) {
	mkdir($uploadDir, 0755, true);
}

$fp = fopen('../uploads/' . $customer_id . '/exports/cases_' . date('mdy') . '.csv', 'w');

$blnHeader = false;
$arrOutputColumns = array("case_id", "case_number", "file_number", "case_type");

foreach ($cases as $row) {
	if (!$blnHeader) {
		$array = array();
		$blnHeader = true;
		foreach($row as $column=>$value) {
			array_push($array, $column);
		}
		array_push($list, array_values($array));
	}
	
	//convert object to array
	//http://stackoverflow.com/questions/2476876/how-do-i-convert-an-object-to-an-array
	$array = json_decode(json_encode($row), true);
    array_push($list, array_values($array));
}
//die(print_r($list));

foreach ($list as $ferow) {
	fputcsv($fp, $ferow);
}
fclose($fp);

die("done");
?>

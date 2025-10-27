<?php 
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

error_reporting(E_ALL);
error_reporting(error_reporting(E_ALL) & (-1 ^ E_DEPRECATED));

include("../api/manage_session.php");
//$_SESSION["current_kase_query"] = "nick";

if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
	header("location:../index.php");
	die();
}

include("../api/connection.php");
include ("report_functions.php");
$atty = "";
$coord = "";
if (isset($_GET["atty"])) {
	$atty = passed_var("atty", "get");
	$atty = str_replace("_", "", $atty);
	$coord = passed_var("coord", "get");
	$coord = str_replace("_", "", $coord);	
}
function runLastQuery($sql, $sortby = "") {
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt = $db->query($sql);
		$kases = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		return $kases;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

//$url = "https://www.ikase.org/api/kases/last";
$sortby = "";
$sql = "";
if (isset($_GET["alpha"])) {
	$sortby = "alpha";
}
$api = "";
if (isset($_GET["api"])) {
	$api = $_GET["api"];
}
//die(print_r($_GET));
if (!isset($_SESSION["current_kase_query"])) {
	return false;
}	

$blnRecent = false;
//print_r($_SESSION);

if ($api!="") {
	//
	$arrAPI = explode("/", $api);
	//die(print_r($arrAPI));
	if ($arrAPI[1]=="allkases") {
		$search_term = $arrAPI[count($arrAPI) - 1];
		//die($search_term );
		$sql = getKases("", "show_all");
	}
	if ($arrAPI[2]=="search") {
		$search_term = $arrAPI[count($arrAPI) - 1];
		//die($search_term );
		$sql = searchKases($search_term, "");
	}
	if ($arrAPI[2]=="advancesearch") {
		//$sql = $_SESSION["recent_query"];
		//die($sql);
		$session_save_path = 'C:\\inetpub\\wwwroot\\ikase.org\\sessions\\';
		$filename = $session_save_path . 'current_query_' . $_SESSION['user_plain_id'] . '.txt';
		$handle = fopen($filename, "r");
		$sql = fread($handle, filesize($filename));
		fclose($handle);
	}
	if ($arrAPI[2]=="recent") {
		$search_term = $arrAPI[count($arrAPI) - 1];
		//die($search_term );
		$sql = getKases("", "recent", "sql");
		$blnRecent = true;
	}
	
	//die($sql);
} /*
else {
	if (isset($_SESSION["recent_query"])) {
		if ($_SESSION["recent_query"]!="") {
			$sql = $_SESSION["recent_query"];
		}
		//unset($_SESSION["recent_query"]);
		$blnRecent = true;
	} 
}
*/
if ($sql=="") {
	//$sql = $_SESSION["current_kase_query"];
	$session_save_path = 'C:\\inetpub\\wwwroot\\ikase.org\\sessions\\';
	$filename = $session_save_path . 'current_query_' . $_SESSION['user_plain_id'] . '.txt';
	$handle = fopen($filename, "r");
	$sql = fread($handle, filesize($filename));
	fclose($handle);
}

if ($sortby=="alpha" && !$blnRecent) {
	if ($blnRecent) {
		$sql = str_replace("ORDER BY recent.time_stamp", "ORDER BY IFNULL(app.last_name, ''), recent.time_stamp", $sql);
		
	} else {
		$sql = str_replace("ORDER BY", "ORDER BY IFNULL(app.last_name, ''),", $sql);
	}
	
	//die($sql);
}

//clean up
$sql = str_replace("ORDER BY IFNULL(last_name, ''), MAX( time_stamp ) DESC", "ORDER BY MAX( time_stamp ) DESC", $sql);

//echo $sql . "\r\n";

$cases = runLastQuery($sql, $sortby);

session_write_close();

$blnSpecialInstructions = (strpos($sql, "special_instructions != ''") !== false);

$list = array();
$customer_id = $_SESSION['user_customer_id'];
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . "\\uploads\\" . $customer_id . "\\exports";
//die($uploadDir);
if (!file_exists($uploadDir)) {
	mkdir($uploadDir, 0755, true);
}

$blnHeader = false;
//$arrOutputColumns = array("case_id", "case_number", "file_number", "case_type", "adj_number", "injury_type", "venue_abbr", "case_status", "full_name", "start_date", "end_date", "statute_limitation", "attorney_full_name", "worker_full_name", "closed_date");

$arrOutputColumns = array("case_id", "file_number", "case_number", "case_name", "last_name", "first_name", "dob", "applicant_email", "applicant_phone", "case_type", "case_status", "submittedOn", "plaintiff", "client", "passenger");
//if ($blnSpecialInstructions) {
	$arrOutputColumns[] = "special_instructions";
//}
$cases_clean = array();

$arrFileNumbers = array();
//die(print_r($cases));
foreach ($cases as $cindex=>$row) {
	//echo $row->case_id .  " - " . $row->worker .  " - " . $row->worker_name . "\r\n";
	//echo $row->case_id .  " - " . $row->attorney .  " - " . $row->attorney_name . "\r\n" . "\r\n";
	
	if (is_numeric($row->worker)) {
		$row->worker = $row->worker_name;
	}
	if (is_numeric($row->attorney)) {
		$row->attorney = $row->attorney_name;
	}
	if ($coord!="") {
		if (strtoupper($coord)!=strtoupper($row->worker)) {
			continue;
		}
	}
	if ($atty!="") {
		if (strtoupper($atty)!=strtoupper($row->attorney)) {
			continue;
		}
	}
	
	//echo $row->file_number . " - " . $row->case_number . "\r\n";
	
	$row_clean = array();
	foreach($row as $column=>$value) {
		//echo $column . " - ";
		if (in_array($column, $arrOutputColumns)) {
			//echo "in";
			if ($column=="submittedOn") {
				$value = date("m/d/Y", strtotime($value));
			}
			$row_clean[$column] = $value;
		}
		//echo "\r\n";
	}
	//die("");
	$blnWCAB = checkWCAB($row_clean["case_type"]);
	if ($blnWCAB) {
		$row_clean["case_type"] = "WCAB";
		unset($row_clean["plaintiff"]);
		unset($row_clean["client"]);
		unset($row_clean["passenger"]);
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
	
	if (!$blnWCAB) {
		$first_name = "";
		$last_name = "";
		//we need plaintiff first and last
		$full_name = $row_clean["plaintiff"];
		if ($full_name=="") {
			//try the client
			$full_name = $row_clean["client"];
		}
		$arrFullName = explode(" ", $full_name);
		if (count($arrFullName) > 0) {
			$first_name = $arrFullName[0];
			unset($arrFullName[0]);
			$last_name = implode(" ", $arrFullName);
		}
		
		$row_clean["first_name"] = $first_name;
		$row_clean["last_name"] = $last_name;
		
		//unset($row_clean["plaintiff"]);
		//unset($row_clean["client"]);
	}
	unset($row_clean["case_number"]);
	
	if (!in_array($row_clean["file_number"], $arrFileNumbers)) {
		$arrFileNumbers[] = $row_clean["file_number"];
		//die(print_r($row_clean));
		array_push($cases_clean, $row_clean);
	}
}
/*
if ($_SERVER['REMOTE_ADDR']=='47.153.51.181') {
	die(print_r($cases_clean));
}
*/
foreach ($cases_clean as $row) {
	unset($row["case_id"]);
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
$filename = 'D:/uploads/' . $customer_id . '/exports/cases_' . date('mdy') . '.csv';
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
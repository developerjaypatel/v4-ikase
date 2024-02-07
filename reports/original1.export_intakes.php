<?php 
error_reporting(E_ALL);
error_reporting(error_reporting(E_ALL) & (-1 ^ E_DEPRECATED));

include("../api/manage_session.php");
session_write_close();

if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
	header("location:../index.php");
	die();
}

include("../api/connection.php");

$filter = passed_var("filter", "get");
$type = passed_var("type", "get");
$letter = passed_var("letter", "get");
$customer_id = $_SESSION["user_customer_id"];

if ($filter=="_") {
	$filter = "";
}
if ($type=="_") {
	$type = "";
}
if ($letter=="_") {
	$letter = "";
}
$sql = "SELECT cct.time_stamp intake_date, cct.user_uuid, cct.user_logon, 
IFNULL(app.first_name, '*') first_name, IFNULL(app.last_name, '*') last_name, IFNULL(app.full_name, '') `full_name`, 
ccase.case_id, ccase.case_name, ccase.case_number, ccase.file_number,  ccase.case_type, ccase.case_language language, 
IF (ccase.case_status = 'Intake', 'Pending', ccase.case_status) case_status, ccase.special_instructions,
inj.explanation injuries, 
IF(inj.start_date = '0000-00-00 00:00:00', IFNULL(pi.personal_injury_date, '0000-00-00 00:00:00'), inj.start_date) start_date,  
IFNULL(inj.occupation, '') occupation,
inj.end_date,
pi.personal_injury_info

FROM cse_case ccase

LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N'
LEFT OUTER JOIN ";
		
if (($_SESSION['user_customer_id']==1033)) { 
	$sql .= "(" . SQL_PERSONX . ")";
} else {
	$sql .= "cse_person";
}
$sql .= " app ON ccapp.person_uuid = app.person_uuid

LEFT OUTER JOIN cse_case_injury cci
ON ccase.case_uuid = cci.case_uuid

LEFT OUTER JOIN cse_injury inj
ON cci.injury_uuid = inj.injury_uuid

LEFT OUTER JOIN cse_personal_injury pi
ON ccase.case_id = pi.case_id

INNER JOIN (
	SELECT case_uuid, user_uuid, user_logon, time_stamp 
	FROM cse_case_track
	WHERE case_status = 'intake'
	AND operation = 'insert'
) cct
ON ccase.case_uuid = cct.case_uuid

WHERE ccase.deleted != 'Y'
AND ccase.customer_id = :customer_id";

if ($type != "") {
	if ($type=="pi") {
		$sql .= " 
		AND case_type NOT LIKE 'WC%' AND case_type NOT LIKE 'W/C%' AND case_type NOT LIKE 'Worker%' ";
		$sql .= " 
		AND case_type != 'social_security' ";
	}
	if ($type=="wcab") {
		$sql .= " 
		AND (case_type LIKE 'WC%' OR case_type LIKE 'W/C%' OR case_type LIKE 'Worker%') ";
	}
	if ($type=="social_security") {
		$sql .= " 
		AND (case_type = 'social_security') ";
	}
	if ($type=="others") {
		$sql .= " 
		AND case_type != 'social_security' ";
		$sql .= " 
		AND case_type NOT LIKE 'WC%' AND case_type NOT LIKE 'W/C%' AND case_type NOT LIKE 'Worker%' ";
		$sql .= " 
		AND case_type != 'Slip and Fall%' AND case_type != 'NewPI'  AND case_type != 'Other' AND case_type NOT LIKE 'Personal Injury%' ";
	}

}
if ($filter != "") {
	switch($filter) {
		case "pending":
			$sql .= "
			AND ccase.case_status = 'Intake'
			";
			break;
		case "rejected":
			$sql .= "
			AND ccase.case_status = 'REJECTED'
			";
			break;
		case "accepted":
			$sql .= "
			AND ccase.case_status != 'REJECTED'
			AND ccase.case_status != 'Intake'
			";
			break;
	}
}

if ($letter!="") {
	$sql .= "
	AND SUBSTRING(IFNULL(app.last_name, ''), IFNULL(TRIM(app.full_name), ''), 1, 1) = '" . $letter . "'";
}
/*
$sql .= "
ORDER BY TRIM(app.last_name), TRIM(app.first_name)";
*/
$sql .= " ORDER BY IFNULL(app.last_name, ''), IFNULL(TRIM(app.full_name), ''), ccase.case_name,
		ccase.case_id, inj.injury_number";
		
try {
	
	//die($sql);
	
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->execute();
	
	$kases = $stmt->fetchAll(PDO::FETCH_OBJ);
	//die(print_r($kases));
	$stmt->closeCursor(); $stmt = null; $db = null;
	foreach($kases as $kase) {
		//die(print_r($kase));
		$arrFields = array();
		$kase->doi = "";
		if ($kase->start_date!="" && $kase->start_date!="0000-00-00"  && $kase->start_date!="0000-00-00 00:00:00") {
			$kase->doi = date("m/d/Y", strtotime($kase->start_date));
		}
		if ($kase->end_date!="" && $kase->end_date!="0000-00-00"  && $kase->end_date!="0000-00-00 00:00:00") {
			$kase->doi .= " - " .  date("m/d/Y", strtotime($kase->end_date)). " CT";
		}
	}
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
}

$list = array ();
$customer_id = $_SESSION['user_customer_id'] ;
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . "\\uploads\\" . $customer_id . "\\exports";
//die($uploadDir);
if (!file_exists($uploadDir)) {
	mkdir($uploadDir, 0755, true);
}


$blnHeader = false;
$arrOutputColumns = array("case_id", "file_number", "case_type", "language", "intake_date", "start_date", "injuries", "occupation", "case_status");

$cases_clean = array();

$arrFileNumbers = array();

//die(print_r($kases));

foreach ($kases as $cindex=>$row) {
	$row_clean = array();
	foreach($row as $column=>$value) {
		//echo $column . " - ";
		if (in_array($column, $arrOutputColumns)) {
			//echo "in";
			if ($column=="start_date" || $column=="intake_date") {
				if ($value!="0000-00-00 00:00:00") {
					$value = date("m/d/Y", strtotime($value));
				} else {
					$value = "";
				}
			}
			$row_clean[$column] = $value;
		}
		//echo "\r\n";
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
	
	unset($row_clean["case_number"]);
	
	if (!in_array($row_clean["file_number"], $arrFileNumbers)) {
		$arrFileNumbers[] = $row_clean["file_number"];
		//die(print_r($row_clean));
		array_push($cases_clean, $row_clean);
	}
}

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

//die(print_r($list));

$filename = '../uploads/' . $customer_id . '/exports/intakes_' . date('mdy') . '.csv';
$fp = fopen($filename, 'w');

foreach ($list as $ferow) {
	fputcsv($fp, $ferow);
}
fclose($fp);

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
<?php 
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
require_once('../shared/legacy_session.php');

session_write_close();

if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
	header("location:../index.php");
	die();
}

include("../api/connection.php");

$customer_id = $_SESSION['user_customer_id'];
function getClientInfo($sortby = "", $customer_id) {
	$sql = "SELECT DISTINCT pers.person_id id, 'applicant' `type`, pers.full_name, pers.first_name, pers.last_name, 
	REPLACE(pers.email, ' ', '') email, pers.phone, pers.cell_phone, pers.work_phone,
	pers.street, pers.city, pers.state, pers.zip, pers.full_address
	FROM cse_case ccase
	LEFT OUTER JOIN cse_case_person ccp
	ON ccase.case_uuid = ccp.case_uuid
	LEFT OUTER JOIN cse_person pers
	ON ccp.person_uuid = pers.person_uuid
	WHERE ccase.customer_id = :customer_id
	AND INSTR(pers.full_name, '*No Name') = 0 
	#AND INSTR(pers.email, '@') > 0 
	#AND IFNULL(pers.email, '') != ''
	
	UNION
	
	SELECT DISTINCT corp.corporation_id id, 'plaintiff' `type`, corp.full_name, corp.first_name, corp.last_name, REPLACE(corp.email, ' ', '') email, corp.phone, '' cell_phone, '' work_phone,
	corp.street, corp.city, corp.state, corp.zip, corp.full_address
	FROM cse_case ccase
	LEFT OUTER JOIN cse_case_corporation ccc
	ON ccase.case_uuid = ccc.case_uuid AND ccc.attribute = 'plaintiff'
	LEFT OUTER JOIN cse_corporation corp
	ON ccc.corporation_uuid = corp.corporation_uuid
	WHERE ccase.customer_id = :customer_id
	AND INSTR(corp.full_name, '*No Name') = 0 
	#AND INSTR(corp.email, '@') > 0 
	#AND IFNULL(corp.email, '') != ''
	
	ORDER BY TRIM(last_name) ASC, TRIM(first_name) ASC";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$clients = $stmt->fetchAll(PDO::FETCH_OBJ);
		return $clients;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
		
	}
}

//$url = "https://www.ikase.org/api/kases/last";
$sortby = "";
if (isset($_GET["alpha"])) {
	$sortby = "alpha";
}
$clients = getClientInfo($sortby, $customer_id);

$list = array();
$uploadDir = UPLOADS_PATH . $customer_id . "\\exports";
//die($uploadDir);
if (!file_exists($uploadDir)) {
	mkdir($uploadDir, 0755, true);
}


$arrOutputColumns = array("id", "type", "full_name", "first_name", "last_name", "email", "phone", "cell_phone", "work_phone", "street", "city", "state", "zip", "full address");
array_push($list, array_values($arrOutputColumns));

foreach ($clients as $row) {
	$array = array();
	foreach($row as $row_val) {	
		$array[] = $row_val;
	}
	$list[] = $array;
	//die(print_r($list));
}
//die(print_r($list));
//output file
$filename = '../uploads/' . $customer_id . '/exports/clients_full_' . date('mdy') . '.csv';
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

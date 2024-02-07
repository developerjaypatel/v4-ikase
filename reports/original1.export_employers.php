<?php 
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

error_reporting(E_ALL);
error_reporting(error_reporting(E_ALL) & (-1 ^ E_DEPRECATED));

include("../api/manage_session.php");

session_write_close();

if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
	header("location:../index.php");
	die();
}

include("../api/connection.php");


$list = array();
$customer_id = $_SESSION['user_customer_id'];

$sql = "SELECT corporation_id, company_name, full_address, IF(phone='', employee_phone, phone) phone
FROM cse_corporation
WHERE `type` = 'employer'
AND corporation_uuid = parent_corporation_uuid
AND company_name != ''
AND customer_id = :customer_id
ORDER BY TRIM(company_name)";

try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->execute();
	$employers = $stmt->fetchAll(PDO::FETCH_OBJ);
	$stmt->closeCursor(); $stmt = null; $db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}

$blnHeader = false;

$arrOutputColumns = array("company_name", "full_address", "phone");
$employers_clean = array();

$arrFileNumbers = array();
//print_r($cases);
foreach ($employers as $cindex=>$row) {
	$row_clean = array();
	foreach($row as $column=>$value) {
		//echo $column . " - ";
		if (in_array($column, $arrOutputColumns)) {
			//echo "in";
			$row_clean[$column] = $value;
		}
		//echo "\r\n";
	}
	array_push($employers_clean, $row_clean);
}


foreach ($employers_clean as $row) {
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
if ($_SERVER['REMOTE_ADDR']=='47.153.51.181') {
	//die(print_r($list));
}
//output file
$filename = '../uploads/' . $customer_id . '/exports/employers_' . date('mdy') . '.csv';
$fp = fopen($filename, 'w');

foreach ($list as $ferow) {
	fputcsv($fp, $ferow);
}
fclose($fp);

//die("done");
$path = $filename;
$filename = explode("/", $path);
$filename = $filename[count($filename) - 1];
//die($filename);
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
<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once('../../../shared/legacy_session.php');
session_write_close();

if (!isset($_SESSION["user_plain_id"])) {
	die("no id");
}
if ($_SESSION["user_role"]!="owner") {
	die("no go");
}
include("../../api/connection.php");

$cus_id = passed_var("cus_id", "post");
$start_date = passed_var("start_date", "post");
$end_date = passed_var("end_date", "post");

$start_date = date("Y-m-d", strtotime($start_date));
$end_date = date("Y-m-d", strtotime($end_date));

try {
	//customer info
	$query = "SELECT `cus_name`, corporation_rate, user_rate
	FROM cse_customer cus
	WHERE cus.customer_id = :customer_id";

	$db = getConnection();
	$stmt = $db->prepare($query);
	$stmt->bindParam("customer_id", $cus_id);
	$stmt->execute();
	$customer = $stmt->fetchObject();

	$user_rate = $customer->user_rate;
	$corporation_rate = $customer->corporation_rate;

	//attorneys
	$sql = "SELECT * 
	FROM ikase.cse_user
	WHERE customer_id = :customer_id
	AND level != 'masteradmin'
	AND job LIKE 'Attorney%'";
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("customer_id", $cus_id);
	$stmt->execute();
	$attorneys = $stmt->fetchAll(PDO::FETCH_OBJ);
	//die(print_r($attorneys));
	$arrAttorneys = array();
	foreach($attorneys as $attorney) {
		$arrAttorneys[] = $attorney->user_name;
	}
	//invoices in date range
	$sql = "SELECT COUNT(invoice_id) invoice_count, IFNULL(GROUP_CONCAT(invoice_id),'') invoice_ids
	FROM ikase.cse_invoice inv
	WHERE customer_id = :customer_id
	AND deleted = 'N'
	AND (`start_date` = '" . $start_date . "' AND end_date = '" . $end_date . "')";
	//AND (`start_date` BETWEEN :start_date AND :end_date OR `end_date` BETWEEN :start_date AND :end_date)
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("customer_id", $cus_id);
	$stmt->execute();
	$count = $stmt->fetchObject();
	//echo $sql . "<br />\r\n";
	//die(print_r($count));
	echo json_encode(array("success"=>"true", "invoices"=>$count->invoice_count, "invoice_ids"=>$count->invoice_ids));	
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	die( print_r($error));
}
?>

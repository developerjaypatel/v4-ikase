<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once('../../../shared/legacy_session.php');
session_write_close();

if (!isset($_SESSION["user_plain_id"])) {
	die("no id");
}

include("../../api/connection.php");

$cus_id = passed_var("cus_id", "post");
$invoice_id = passed_var("invoice_id", "post");
$check_number = passed_var("check_number", "post");
$check_date = passed_var("check_date", "post");
$invoice_total = passed_var("invoice_total", "post");
$payment = passed_var("payment", "post");
$memo = passed_var("memo", "post");
$balance = passed_var("balance_due", "post");

try {
	$check_uuid = uniqid("CK");
	$sql = "INSERT ikase.cse_check (check_uuid, check_number, check_date, amount_due, payment, balance, transaction_date, memo, customer_id)
	VALUES ('" . $check_uuid . "', '" . addslashes($check_number) . "', '" . date("Y-m-d", strtotime($check_date)) . "', '" . $invoice_total . "', '" . $payment . "','" . $balance . "','" . date("Y-m-d H:i:s") . "', '" . addslashes($memo) . "','" . $cus_id . "')";
	
	$stmt = DB::run($sql);
	
	$sql = "INSERT INTO ikase.cse_invoice_check (invoice_check_uuid, invoice_uuid, check_uuid, attribute, last_updated_date, last_update_user, customer_id)
	SELECT '" . $check_uuid . "', invoice_uuid, '" . $check_uuid . "', 'main', '" . date("Y-m-d H:i:s") . "', '" . $_SESSION["user_id"] . "', '" . $cus_id . "'
	FROM `ikase`.`cse_invoice`
	WHERE invoice_id = '" . $invoice_id . "'
	AND customer_id = '" . $cus_id . "'";
	
	$stmt = DB::run($sql);
	//echo $sql . "<br />";
} catch(PDOException $e) {
	$error = array("error"=> array("sql"=>$sql, "text"=>$e->getMessage()));
	die( print_r($error));
}	
header("location:invoices.php?cus_id=" . $cus_id);
?>

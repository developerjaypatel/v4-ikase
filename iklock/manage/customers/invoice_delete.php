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

$invoice_id = passed_var("invoice_id", "post");
$cus_id = passed_var("cus_id", "post");

if (!is_numeric($invoice_id) || !is_numeric($cus_id)) {
	die("no id");
}
$sql = "UPDATE `ikase`.`cse_invoice`
SET deleted = 'Y'
WHERE customer_id = :customer_id
AND invoice_id = :invoice_id";

try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("customer_id", $cus_id);
	$stmt->bindParam("invoice_id", $invoice_id);
	$stmt->execute();

	echo "deleted";
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	die( print_r($error));
}
?>

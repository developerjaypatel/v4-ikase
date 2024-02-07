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
$total = passed_var("total", "post");
$invoice_number = passed_var("invoice_number", "post");
$invoice_items = @processHTML($_POST["invoice_items"]);
$active_users = @processHTML($_POST["active_users"]);
$start_date = date("Y-m-d", strtotime($start_date));
$end_date = date("Y-m-d", strtotime($end_date));
$diff = dateDiff("d", $start_date, $end_date);
//we are going to need a reminder
if ($diff < 35) {
	//month, remind in 27 days
	$reminder_interval = 3;
}
if ($diff > 35) {
	//a week early
	$reminder_interval = 7;
}
$invoice_id = -1;
if (isset($_GET["invoice_id"])) {
	$invoice_id = passed_var("invoice_id", "get");
}
if (isset($_POST["invoice_id"])) {
	$invoice_id = passed_var("invoice_id", "post");
}

if (!is_numeric($invoice_id)) {
	die("no invoice");
}

try {		
	if ($invoice_id == "-1") {
		$operation = "insert";
		$invoice_uuid = uniqid("IV");
		$sql = "INSERT INTO ikase.cse_invoice (invoice_uuid, invoice_date, start_date, end_date, invoice_number, total, `invoice_items`, `active_users`, customer_id, id_collection)
			VALUES ('" . $invoice_uuid . "','" . date("Y-m-d") . "', '" . $start_date . "', '" . $end_date . "', '" . $invoice_number . "','" . $total . "','" . addslashes($invoice_items) . "','" . addslashes($active_users) . "', '" . $cus_id . "', 'invoice')";
		
		DB::run($sql);
	$invoice_id = DB::lastInsertId();
		
		//reminders				
		$reminder_uuid = uniqid("RM", false);
		$reminder_type = "email";
		$reminder_span = "days";
		$reminder_number = 1;
		$reminder_datetime = date("Y-m-d H:i:s", strtotime($end_date . " 08:00:00" . " - " . $reminder_interval . " " . $reminder_span));
		$values = "'" . $reminder_uuid . "', '" . $reminder_number . "', '" . $reminder_type . "', '" . $reminder_interval . "', '" . $reminder_span . "', '"  . $reminder_datetime . "', '" . $cus_id . "'"; 
		
		//insert the reminder
		$sql = "INSERT `ikase`.`cse_reminder` (`reminder_uuid`, `reminder_number`, `reminder_type`, `reminder_interval`, `reminder_span`,`reminder_datetime`, `customer_id`) 
		VALUES(" . $values . ")";
		//echo $sql . "\r\n";
		$stmt = DB::run($sql);
		
		$invoice_reminder_uuid = uniqid("ER", false);
		//attach each one to the invoice
		$sql = "INSERT INTO `ikase`.`cse_invoice_reminder` (`invoice_reminder_uuid`, `invoice_uuid`, `reminder_uuid`, `attribute_1`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $invoice_reminder_uuid  ."', '" . $invoice_uuid . "', '" . $reminder_uuid . "', '" . $reminder_number . "', '" . date("Y-m-d H:i:s") . "', '" . $_SESSION['user_id'] . "', '" . $cus_id . "')";
		//echo $sql . "\r\n";
		$stmt = DB::run($sql);
	} else {
		$operation = "update";
		$sql = "UPDATE `ikase`.`cse_invoice` 
		SET start_date = '" . $start_date . "', 
		end_date = '" . $end_date . "', 
		total = '" . $total . "'
		WHERE invoice_id = '" . $invoice_id . "'";
		
		$stmt = DB::run($sql);
	}	
	
	echo json_encode(array("success"=>true, "operation"=>$operation, "diff"=>$diff, "invoice_id"=>$invoice_id, "result"=>"<span style='color:white;background:green;padding:1px'>saved&nbsp;&#10003;</span>"));
	//echo $sql . "<br />";
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}	

?>

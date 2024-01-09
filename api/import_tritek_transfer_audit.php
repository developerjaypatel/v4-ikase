<?php
require_once('../shared/legacy_session.php');


include("connection.php");
$customer_id = passed_var("customer_id", "get");
$customer_id = 1055;
if (!is_numeric($customer_id)) {
	die();
}

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	$sql = "DELETE FROM `ikase_" . $data_source . "`.`cse_case_notes` 
	WHERE customer_id = " . $customer_id . "
	";
	$stmt = DB::run($sql);
	
	$sql = "DELETE FROM `ikase_" . $data_source . "`.`cse_notes` 
	WHERE customer_id = " . $customer_id . "
	";
	$stmt = DB::run($sql);
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_notes`
	(`case_notes_uuid`, `case_uuid`, `notes_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id`)
	SELECT `case_notes_uuid`, `case_uuid`, `notes_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id` 
	FROM `" . $data_source . "`.`" . $data_source . "_case_notes` 
	WHERE 1";

	$stmt = DB::run($sql);
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_notes` (`notes_uuid`, `type`, `subject`, `note`, `title`, 
	`attachments`, `entered_by`, `status`, `dateandtime`, `callback_date`, `verified`, `deleted`, `customer_id`)
	SELECT `notes_uuid`, `type`, `subject`, `note`, `title`, `attachments`, `entered_by`, `status`, 
	`dateandtime`, `callback_date`, `verified`, `deleted`, `customer_id` 
	FROM `" . $data_source . "`.`" . $data_source . "_notes` 
	WHERE 1";
	
	$stmt = DB::run($sql);
	
	$success = array("success"=> array("text"=>"done"));
	echo json_encode($success);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}	
//, `notedesc`, , `notepoint`, `notelock`, `lockedby`, `xsoundex`, `locator`, `enteredby`, `mailpoint`, `lockloc`, `docpointer`, `doctype`


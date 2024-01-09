<?php
require_once('../shared/legacy_session.php');

include("connection.php");

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_notes`
	(`case_notes_uuid`, `case_uuid`, `notes_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id`)
	SELECT `case_notes_uuid`, `case_uuid`, `notes_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id` 
	FROM `" . $data_source . "`.`" . $data_source . "_case_notes2` 
	WHERE 1
	AND notes_uuid NOT IN (SELECT notes_uuid FROM `ikase_" . $data_source . "`.`cse_notes`)";
	
	echo $sql . "\r\n";
	$stmt = DB::run($sql);
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_notes` (`notes_uuid`, `type`, `subject`, `note`, `title`, 
	`attachments`, `entered_by`, `status`, `dateandtime`, `callback_date`, `verified`, `deleted`, `customer_id`)
	SELECT `notes_uuid`, `type`, `subject`, `note`, `title`, `attachments`, `entered_by`, `status`, 
	`dateandtime`, `callback_date`, `verified`, `deleted`, `customer_id` 
	FROM `" . $data_source . "`.`" . $data_source . "_notes2` 
	WHERE 1
	AND notes_uuid NOT IN (SELECT notes_uuid FROM `ikase_" . $data_source . "`.`cse_notes`)";
	
	echo $sql . "\r\n";
	$stmt = DB::run($sql);
	
	$success = array("success"=> array("text"=>"done @" . date("H:i:s")));
	echo json_encode($success);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}	
//, `notedesc`, , `notepoint`, `notelock`, `lockedby`, `xsoundex`, `locator`, `enteredby`, `mailpoint`, `lockloc`, `docpointer`, `doctype`
include("cls_logging.php");
?>
<script language="javascript">
parent.setFeedback("notes transfer completed");
</script>

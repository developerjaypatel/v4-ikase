<?php
include("manage_session.php");

include("connection.php");

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	$sql = "DELETE FROM `ikase_" . $data_source . "`.`cse_case_notes` 
	WHERE 1
	AND (`attribute` = 'general2' OR `attribute` = 'access2' OR `attribute2` = 'general2')
	AND `customer_id` = " . $customer_id;
	echo $sql . "\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "DELETE FROM `ikase_" . $data_source . "`.`cse_notes` 
	WHERE 1
	AND (`type` = 'general2' OR `type` = 'access2' OR `status` = 'general2')
	AND `customer_id` = " . $customer_id . "
	";
	echo $sql . "\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	//die();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_notes`
	(`case_notes_uuid`, `case_uuid`, `notes_uuid`, `attribute`, `attribute2`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id`)
	SELECT `case_notes_uuid`, `case_uuid`, `notes_uuid`, `attribute`, 'general2', `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id` 
	FROM `" . $data_source . "`.`" . $data_source . "_case_notes2` 
	WHERE 1";
	
	echo $sql . "\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_notes` (`notes_uuid`, `type`, `subject`, `note`, `title`, 
	`attachments`, `entered_by`, `status`, `dateandtime`, `callback_date`, `verified`, `deleted`, `customer_id`)
	SELECT `notes_uuid`, `type`, `subject`, `note`, `title`, `attachments`, `entered_by`, 'general2', 
	`dateandtime`, `callback_date`, `verified`, `deleted`, `customer_id` 
	FROM `" . $data_source . "`.`" . $data_source . "_notes2` 
	WHERE 1";
	
	echo $sql . "\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$db = null;
	
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
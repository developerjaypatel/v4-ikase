<?php
include("manage_session.php");

include("connection.php");

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_notes`
	(`case_notes_uuid`, `case_uuid`, `notes_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id`)
	SELECT `case_notes_uuid`, ca.`case_uuid`, `notes_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id` 
	FROM `" . $data_source . "`.`" . $data_source . "_case_notes2` ca
	INNER JOIN " . $data_source . ".badcases
	ON ca.case_uuid = badcases.case_uuid
	WHERE 1";
	
	echo $sql . "\r\n\r\n";
	//$stmt = $db->prepare($sql);
	//$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_notes` (`notes_uuid`, `type`, `subject`, `note`, `title`, 
	`attachments`, `entered_by`, `status`, `dateandtime`, `callback_date`, `verified`, `deleted`, `customer_id`)
	SELECT notes.`notes_uuid`, notes.`type`, notes.`subject`, notes.`note`, 
	notes.`title`, notes.`attachments`, notes.`entered_by`, notes.`status`, 
	notes.`dateandtime`, notes.`callback_date`, notes.`verified`, notes.`deleted`, notes.`customer_id` 
	FROM `" . $data_source . "`.`" . $data_source . "_notes2` notes
	INNER JOIN `ikase_" . $data_source . "`.`cse_case_notes` ccn
	ON notes.notes_uuid = ccn.notes_uuid
	INNER JOIN " . $data_source . ".badcases
	ON ccn.case_uuid = badcases.case_uuid
	WHERE 1";
	
	echo $sql . "\r\n\r\n";
	//$stmt = $db->prepare($sql);
	//$stmt->execute();
	
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
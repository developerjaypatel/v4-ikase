<?php
include("manage_session.php");

include("connection.php");

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	$sql = "DELETE FROM `ikase_" . $data_source . "`.`cse_case_notes` 
	WHERE last_update_user = 'system'";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "DELETE FROM `ikase_" . $data_source . "`.`cse_notes` 
	WHERE notes_uuid NOT LIKE 'KS%'";
	//	dateandtime < '2016-09-16 09:40:00'
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_notes` (`notes_uuid`, `type`, `subject`, `note`, `title`, 
	`attachments`, `entered_by`, `status`, `dateandtime`, `callback_date`, `verified`, `deleted`, `customer_id`)
	SELECT HEX(CONCAT(IFNULL(U_ID, ''), IFNULL(U_ID3, ''))) `notes_uuid`, `TYPE` `type`, '', `NOTE` `note`, '', '', `OPERATOR` `entered_by`, '' `status`, 
	CONCAT(`WHEN`, ' 08:00:00') `dateandtime`, '0000-00-00 00:00:00' `callback_date`, 'Y' `verified`, 'N' `deleted`, 
	'" . $customer_id . "' `customer_id` 
	FROM `" . $data_source . "`.`lawnotes` 
	WHERE 1";
	//die($sql);
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_notes`
	(`case_notes_uuid`, `case_uuid`, `notes_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id`)
	SELECT law3.U_ID, law3.CASENUM, HEX(CONCAT(IFNULL(lnot.U_ID, ''), IFNULL(lnot.U_ID3, ''))), 'main' `attribute`, '" . date("Y-m-d H:i:s") . "' last_updated_date, 
	'system' last_update_user, 
	'N' deleted, '" . $customer_id . "' `customer_id`
	FROM `" . $data_source . "`.lawnotes lnot
	INNER JOIN `" . $data_source . "`.law3 
	ON lnot.CASENUM = law3.CASENUM";

	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	//emails
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_notes` (`notes_uuid`, `type`, `subject`, `note`, `title`, 
	`attachments`, `entered_by`, `status`, `dateandtime`, `callback_date`, `verified`, `deleted`, `customer_id`)
	SELECT ID `notes_uuid`, 'EMAIL' `type`, IFNULL(`SUBJECT`, '') `subject`, 
	CONCAT('From:', `FROM`, '\r\n', 'To:', `TO`, IF(`CC` IS NULL, '', CONCAT(', ', `CC`)), '\r\n', `BODY`) `note`, 
	IFNULL(`SUBJECT`, '') `title`, '', `OPERATOR` `entered_by`, '' `status`, 
	CONCAT(`WHEN`, ' 08:00:00') `dateandtime`, '0000-00-00 00:00:00' `callback_date`, 'Y' `verified`, 'N' `deleted`, 
	'" . $customer_id . "' `customer_id` 
	FROM `" . $data_source . "`.`lawemail` 
	WHERE ID IS NOT NULL";
	//die($sql);
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_notes`
	(`case_notes_uuid`, `case_uuid`, `notes_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id`)
	SELECT law3.U_ID, law3.CASENUM, lmail.ID, 'main' `attribute`, '2016-09-09' last_updated_date, 'system' last_update_user, 'N' deleted, 1089
	FROM " . $data_source . ".lawemail lmail
	INNER JOIN " . $data_source . ".law3 
	ON lmail.CASENUM = law3.CASENUM
	WHERE lmail.ID IS NOT NULL";
	
	$db = null;
	
	$success = array("success"=> array("text"=>"done @" . date("H:i:s")));
	echo json_encode($success);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}	
?>
<script language="javascript">
parent.setFeedback("notes transfer completed");
</script>
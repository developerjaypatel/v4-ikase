<?php
include("manage_session.php");

include("connection.php");

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	//$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_case_checkrequest`";
	$sql = "DELETE FROM `ikase_" . $data_source . "`.`cse_case_checkrequest`
	WHERE checkrequest_uuid NOT LIKE 'KS%';";
	echo $sql . "\r\n\r\n";
	//$stmt = $db->prepare($sql);
	//$stmt->execute();
	
	//$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_corporation_checkrequest`";
	$sql = "DELETE FROM `ikase_" . $data_source . "`.`cse_corporation_checkrequest`
	WHERE checkrequest_uuid NOT LIKE 'KS%';";
	echo $sql . "\r\n\r\n";
	//$stmt = $db->prepare($sql);
	//$stmt->execute();
	
	//$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_person_checkrequest`";
	$sql = "DELETE FROM `ikase_" . $data_source . "`.`cse_person_checkrequest`
	WHERE checkrequest_uuid NOT LIKE 'KS%';";
	echo $sql . "\r\n\r\n";
	//$stmt = $db->prepare($sql);
	//$stmt->execute();
	
	//$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_checkrequest` ";
	$sql = "DELETE FROM `ikase_" . $data_source . "`.`cse_checkrequest`
	WHERE checkrequest_uuid NOT LIKE 'KS%';";
	echo $sql . "\r\n\r\n";
	//$stmt = $db->prepare($sql);
	//$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_checkrequest`
	(`case_checkrequest_uuid`, `case_uuid`, `checkrequest_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id`)
	SELECT `case_checkrequest_uuid`, `case_uuid`, `checkrequest_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id` 
	FROM `" . $data_source . "`.`" . $data_source . "_case_checkrequest` 
	WHERE 1 AND customer_id = " . $customer_id . ";";

	echo $sql . "\r\n\r\n";
	//$stmt = $db->prepare($sql);
	//$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_corporation_checkrequest`
	(`corporation_checkrequest_uuid`, `corporation_uuid`, `checkrequest_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id`)
	SELECT `corporation_checkrequest_uuid`, `corporation_uuid`, `checkrequest_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id` 
	FROM `" . $data_source . "`.`" . $data_source . "_corporation_checkrequest` 
	WHERE 1 AND customer_id = " . $customer_id . ";";

	echo $sql . "\r\n\r\n";
	//$stmt = $db->prepare($sql);
	//$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_person_checkrequest`
	(`person_checkrequest_uuid`, `person_uuid`, `checkrequest_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id`)
	SELECT `person_checkrequest_uuid`, `person_uuid`, `checkrequest_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id` 
	FROM `" . $data_source . "`.`" . $data_source . "_person_checkrequest` 
	WHERE 1 AND customer_id = " . $customer_id . ";";

	echo $sql . "\r\n\r\n";
	//$stmt = $db->prepare($sql);
	//$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_checkrequest` (`checkrequest_uuid`, `check_uuid`, `requested_by`, `payable_to`, `payable_type`, `rush_request`, `request_date`, `amount`, `needed_date`, `reason`, `reviewed_by`, `review_date`, `approved`, `check_number`, `rejection_reason`,
	`deleted`, `customer_id`)
	SELECT `checkrequest_uuid`, `check_uuid`, `requested_by`, `payable_to`, `payable_type`, `rush_request`, `request_date`, `amount`, `needed_date`, `reason`, `reviewed_by`, `review_date`, `approved`, `check_number`, `rejection_reason`,
	`deleted`, `customer_id`
	FROM `" . $data_source . "`.`" . $data_source . "_checkrequest` 
	WHERE 1 AND customer_id = " . $customer_id . ";";
	
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
parent.setFeedback("costs transfer completed");
</script>
<?php
include("manage_session.php");

include("connection.php");

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_case_lostincome`";
	
	echo $sql . "\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_corporation_lostincome`";
	
	echo $sql . "\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_lostincome` ";

	echo $sql . "\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_lostincome`
	(`case_lostincome_uuid`, `case_uuid`, `lostincome_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id`)
	SELECT `case_lostincome_uuid`, `case_uuid`, `lostincome_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id` 
	FROM `" . $data_source . "`.`" . $data_source . "_case_lostincome` 
	WHERE 1 AND customer_id = " . $customer_id;

	echo $sql . "\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_corporation_lostincome`
	(`corporation_lostincome_uuid`, `corporation_uuid`, `lostincome_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id`)
	SELECT `corporation_lostincome_uuid`, `corporation_uuid`, `lostincome_uuid`, 'main', `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id` 
	FROM `" . $data_source . "`.`" . $data_source . "_corporation_lostincome` 
	WHERE 1 AND customer_id = " . $customer_id;

	echo $sql . "\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_lostincome` (`lostincome_uuid`, `start_lost_date`, `end_lost_date`, `comments`, `amount`, `deleted`, `customer_id`)
	SELECT `lostincome_uuid`, `start_lost_date`, `end_lost_date`, `comments`, `amount`, `deleted`, `customer_id` 
	FROM `" . $data_source . "`.`" . $data_source . "_lostincome` 
	WHERE 1 AND customer_id = " . $customer_id;
	
	echo $sql . "\r\n\r\n";
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
parent.setFeedback("lostincome transfer completed");
</script>
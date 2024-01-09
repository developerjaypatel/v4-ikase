<?php
include("manage_session.php");

include("connection.php");

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_check`
	(`case_check_uuid`, `case_uuid`, `check_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id`)
	SELECT `case_check_uuid`, ccn.`case_uuid`, `check_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id` 
	FROM `" . $data_source . "`.`" . $data_source . "_case_check` ccn 
	INNER JOIN " . $data_source . ".badcases
	ON ccn.case_uuid = badcases.case_uuid
	WHERE 1";

	echo $sql . "\r\n\r\n";
	//$stmt = $db->prepare($sql);
	//$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_check` (`check_uuid`, `check_number`, `check_date`, `check_type`, `name`, `amount_due`, `payment`, `adjustment`, `balance`, `transaction_date`, `memo`, `carrier_uuid`, `customer_id`, `deleted`)
	SELECT ev.`check_uuid`, `check_number`, `check_date`, `check_type`, `name`, `amount_due`, `payment`, `adjustment`, `balance`, `transaction_date`, `memo`, `carrier_uuid`, ev.`customer_id`, ev.`deleted` 
	FROM `" . $data_source . "`.`" . $data_source . "_check`  ev
	INNER JOIN `ikase_" . $data_source . "`.`cse_case_check` cce
	ON ev.check_uuid = cce.check_uuid
	INNER JOIN " . $data_source . ".badcases
	ON cce.case_uuid = badcases.case_uuid
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
parent.setFeedback("costs transfer completed");
</script>
<?php
require_once('../shared/legacy_session.php');

include("connection.php");

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	//die();
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_deduction`
	(`case_deduction_uuid`, `case_uuid`, `deduction_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id`)
	SELECT `case_deduction_uuid`, ccn.`case_uuid`, `deduction_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id` 
	FROM `" . $data_source . "`.`" . $data_source . "_case_deduction`  ccn 
	INNER JOIN " . $data_source . ".badcases
	ON ccn.case_uuid = badcases.case_uuid 
	WHERE 1";

	echo $sql . "\r\n\r\n";
	//$stmt = $db->prepare($sql);
	//$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_deduction` (
    `deduction_uuid`, `deduction_date`, `tracking_number`, `deduction_description`, `amount`, `payment`, `adjustment`, `balance`, `customer_id`, `deleted`)
	SELECT ev.`deduction_uuid`, `deduction_date`, `tracking_number`, `deduction_description`, `amount`, `payment`, `adjustment`, `balance`, ev.`customer_id`, ev.`deleted`
	FROM `" . $data_source . "`.`" . $data_source . "_deduction` ev
	INNER JOIN `ikase_" . $data_source . "`.`cse_case_deduction` cce
	ON ev.deduction_uuid = cce.deduction_uuid
	INNER JOIN " . $data_source . ".badcases
	ON cce.case_uuid = badcases.case_uuid  
	WHERE 1 ";
	
	echo $sql . "\r\n\r\n";
	//$stmt = $db->prepare($sql);
	//$stmt->execute();
	
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
parent.setFeedback("deductions transfer completed");
</script>

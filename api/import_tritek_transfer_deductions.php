<?php
require_once('../shared/legacy_session.php');

include("connection.php");

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_case_deduction`";
	
	echo $sql . "\r\n\r\n";
	$stmt = DB::run($sql);
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_deduction` ";

	echo $sql . "\r\n\r\n";
	$stmt = DB::run($sql);
	//die();
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_deduction`
	(`case_deduction_uuid`, `case_uuid`, `deduction_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id`)
	SELECT `case_deduction_uuid`, `case_uuid`, `deduction_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id` 
	FROM `" . $data_source . "`.`" . $data_source . "_case_deduction` 
	WHERE 1 AND customer_id = " . $customer_id;

	echo $sql . "\r\n\r\n";
	$stmt = DB::run($sql);
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_deduction` (
    `deduction_uuid`, `deduction_date`, `tracking_number`, `deduction_description`, `amount`, `payment`, `adjustment`, `balance`, `customer_id`, `deleted`)
	SELECT `deduction_uuid`, `deduction_date`, `tracking_number`, `deduction_description`, `amount`, `payment`, `adjustment`, `balance`, `customer_id`, `deleted`
	FROM `" . $data_source . "`.`" . $data_source . "_deduction` 
	WHERE 1 AND customer_id = " . $customer_id;
	
	echo $sql . "\r\n\r\n";
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
parent.setFeedback("deductions transfer completed");
</script>

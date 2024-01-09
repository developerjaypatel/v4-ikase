<?php
require_once('../shared/legacy_session.php');

include("connection.php");

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	
	//$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_case_check`";
	$sql = "DELETE FROM `ikase_" . $data_source . "`.`cse_case_check`
	WHERE case_check_id > 6";
	echo $sql . "\r\n\r\n";
	$stmt = DB::run($sql);
	
	//$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_check` ";
	$sql = "DELETE FROM `ikase_" . $data_source . "`.`cse_check`
	WHERE check_id > 6";
	echo $sql . "\r\n\r\n";
	$stmt = DB::run($sql);
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_check`
	(`case_check_uuid`, `case_uuid`, `check_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id`)
	SELECT `case_check_uuid`, `case_uuid`, `check_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id` 
	FROM `" . $data_source . "`.`" . $data_source . "_case_check` 
	WHERE 1 AND customer_id = " . $customer_id;

	echo $sql . "\r\n\r\n";
	$stmt = DB::run($sql);
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_check` (`check_uuid`, `check_number`, `check_date`, `check_type`, `name`, `amount_due`, `payment`, `adjustment`, `balance`, `transaction_date`, `memo`, `carrier_uuid`, `customer_id`, `deleted`)
	SELECT `check_uuid`, `check_number`, `check_date`, `check_type`, `name`, `amount_due`, `payment`, `adjustment`, `balance`, `transaction_date`, `memo`, `carrier_uuid`, `customer_id`, `deleted` 
	FROM `" . $data_source . "`.`" . $data_source . "_check` 
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
parent.setFeedback("costs transfer completed");
</script>

<?php
require_once('../shared/legacy_session.php');

include("connection.php");

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	
	$sql = "
	DELETE FROM `ikase_" . $data_source . "`.`cse_injury_settlement`
	WHERE injury_settlement_uuid LIKE 'IS%';
	
	DELETE FROM ikase_" . $data_source . ".cse_fee
	WHERE fee_uuid NOT LIKE 'KS%';
	
	DELETE FROM ikase_" . $data_source . ".cse_settlement_fee
	WHERE settlement_uuid LIKE 'SE%';
	
	DELETE FROM ikase_" . $data_source . ".cse_settlement
	WHERE settlement_uuid LIKE 'SE%';
	";
	
	echo $sql . "\r\n\r\n";
	$stmt = DB::run($sql);
	
	//die();
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_settlement_fee`
	(`settlement_fee_uuid`, `settlement_uuid`, `fee_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id`)
	SELECT `settlement_fee_uuid`, `settlement_uuid`, `fee_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id` 
	FROM `" . $data_source . "`.`" . $data_source . "_settlement_fee` 
	WHERE 1 AND customer_id = " . $customer_id;

	echo $sql . "\r\n\r\n";
	$stmt = DB::run($sql);
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_injury_settlement`
	(`injury_settlement_uuid`, `injury_uuid`, `settlement_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id`)
	SELECT `injury_settlement_uuid`, `injury_uuid`, `settlement_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id` 
	FROM `" . $data_source . "`.`" . $data_source . "_injury_settlement` 
	WHERE 1 
	AND injury_settlement_id > 1007
	AND customer_id = " . $customer_id;

	echo $sql . "\r\n\r\n";
	$stmt = DB::run($sql);
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_settlement` (
    `settlement_uuid`, `date_submitted`, `date_settled`, `amount_of_settlement`, `future_medical`, `amount_of_fee`, `c_and_r`, `stip`, `f_and_a`, `date_approved`, `pd_percent`, `date_fee_received`, `attorney`, `customer_id`, `deleted`)
	SELECT `settlement_uuid`, `date_submitted`, `date_settled`, `amount_of_settlement`, `future_medical`, `amount_of_fee`, `c_and_r`, `stip`, `f_and_a`, `date_approved`, `pd_percent`, `date_fee_received`, `attorney`, `customer_id`, `deleted`
	FROM `" . $data_source . "`.`" . $data_source . "_settlement` 
	WHERE 1 AND customer_id = " . $customer_id;
	
	echo $sql . "\r\n\r\n";
	$stmt = DB::run($sql);
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_fee` (
    `fee_uuid`, `fee_type`, `fee_requested`, `fee_date`, `fee_billed`, `fee_paid`, `fee_recipient`, `fee_check_number`, `fee_memo`, `fee_doctor_id`, `fee_referral`, `full_name`, `customer_id`, `deleted`, `paid_fee`, `hourly_rate`, `hours`, `fee_by`)
	SELECT `fee_uuid`, `fee_type`, `fee_requested`, `fee_date`, `fee_billed`, `fee_paid`, `fee_recipient`, `fee_check_number`, `fee_memo`, `fee_doctor_id`, `fee_referral`, `full_name`, `customer_id`, `deleted`, `paid_fee`, `hourly_rate`, `hours`, `fee_by`
	FROM `" . $data_source . "`.`" . $data_source . "_fee` 
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
parent.setFeedback("medical billing transfer completed");
</script>

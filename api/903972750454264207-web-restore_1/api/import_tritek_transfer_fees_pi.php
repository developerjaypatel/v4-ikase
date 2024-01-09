<?php
include("manage_session.php");

include("connection.php");

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_injury_settlement`
	(`injury_settlement_uuid`, `injury_uuid`, `settlement_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id`)
	SELECT `injury_settlement_uuid`, ev.`injury_uuid`, `settlement_uuid`, ev.`attribute`, ev.`last_updated_date`, 
	ev.`last_update_user`, ev.`deleted`, ev.`customer_id` 
	FROM `" . $data_source . "`.`" . $data_source . "_injury_settlement` ev
	INNER JOIN `ikase_" . $data_source . "`.`cse_case_injury` cce
	ON ev.injury_uuid = cce.injury_uuid
	INNER JOIN " . $data_source . ".badcases
	ON cce.case_uuid = badcases.case_uuid  
	WHERE 1";

	echo $sql . "\r\n\r\n";
	//$stmt = $db->prepare($sql);
	//$stmt->execute();
	
	//die();
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_settlement_fee`
	(`settlement_fee_uuid`, `settlement_uuid`, `fee_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id`)
	SELECT `settlement_fee_uuid`, sett.`settlement_uuid`, sett.`fee_uuid`, sett.`attribute`, sett.`last_updated_date`, 
	sett.`last_update_user`, sett.`deleted`, sett.`customer_id` 
	FROM `" . $data_source . "`.`" . $data_source . "_settlement_fee` sett 
	INNER JOIN `ikase_" . $data_source . "`.`cse_injury_settlement` ev
	ON sett.settlement_uuid = ev.settlement_uuid
	INNER JOIN `ikase_" . $data_source . "`.`cse_case_injury` cce
	ON ev.injury_uuid = cce.injury_uuid
	INNER JOIN " . $data_source . ".badcases
	ON cce.case_uuid = badcases.case_uuid  
	WHERE 1";

	echo $sql . "\r\n\r\n";
	//$stmt = $db->prepare($sql);
	//$stmt->execute();
	
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_settlement` (
    `settlement_uuid`, `date_submitted`, `date_settled`, `amount_of_settlement`, `future_medical`, `amount_of_fee`, `c_and_r`, `stip`, `f_and_a`, `date_approved`, `pd_percent`, `date_fee_received`, `attorney`, `legacy_info`, `customer_id`, `deleted`)
	SELECT sett.`settlement_uuid`, `date_submitted`, `date_settled`, `amount_of_settlement`, `future_medical`, `amount_of_fee`, `c_and_r`, `stip`, `f_and_a`, `date_approved`, `pd_percent`, `date_fee_received`, `attorney`, `legacy_info`, sett.`customer_id`, sett.`deleted`
	FROM `" . $data_source . "`.`" . $data_source . "_settlement`  sett 
	INNER JOIN `ikase_" . $data_source . "`.`cse_injury_settlement` ev
	ON sett.settlement_uuid = ev.settlement_uuid
	INNER JOIN `ikase_" . $data_source . "`.`cse_case_injury` cce
	ON ev.injury_uuid = cce.injury_uuid
	INNER JOIN " . $data_source . ".badcases
	ON cce.case_uuid = badcases.case_uuid  
	WHERE 1";
	
	echo $sql . "\r\n\r\n";
	//$stmt = $db->prepare($sql);
	//$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_fee` (
    `fee_uuid`, `fee_type`, `fee_requested`, `fee_date`, `fee_billed`, `fee_paid`, `fee_recipient`, `fee_check_number`, `fee_memo`, `fee_doctor_id`, `fee_referral`, `full_name`, `customer_id`, `deleted`, `paid_fee`, `hourly_rate`, `hours`, `fee_by`)
	SELECT fee.`fee_uuid`, fee.`fee_type`, fee.`fee_requested`, fee.`fee_date`, 
    fee.`fee_billed`, fee.`fee_paid`, fee.`fee_recipient`, fee.`fee_check_number`, fee.`fee_memo`, 
    fee.`fee_doctor_id`, fee.`fee_referral`, fee.`full_name`, fee.`customer_id`, fee.`deleted`, fee.`paid_fee`, fee.`hourly_rate`, fee.`hours`, fee.`fee_by`
	FROM `" . $data_source . "`.`" . $data_source . "_fee` fee
    
    
    LEFT OUTER JOIN ikase_" . $data_source . ".cse_fee gca
    ON fee.fee_uuid = gca.fee_uuid
    
	INNER JOIN `ikase_" . $data_source . "`.`cse_settlement_fee`  sett 
	ON fee.fee_uuid = sett.fee_uuid
	INNER JOIN `ikase_" . $data_source . "`.`cse_injury_settlement` ev
	ON sett.settlement_uuid = ev.settlement_uuid
	INNER JOIN `ikase_" . $data_source . "`.`cse_case_injury` cce
	ON ev.injury_uuid = cce.injury_uuid
	INNER JOIN " . $data_source . ".badcases
	ON cce.case_uuid = badcases.case_uuid  
	WHERE 1
AND gca.fee_uuid IS NULL";
	
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
parent.setFeedback("medical billing transfer completed");
</script>
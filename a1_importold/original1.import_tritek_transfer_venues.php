<?php
include("manage_session.php");

include("connection.php");

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	
	$sql = "DELETE FROM `ikase_" . $data_source . "`.`cse_case_corporation`
	WHERE attribute = 'venue'";
	
	echo $sql . "\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "DELETE FROM `ikase_" . $data_source . "`.`cse_corporation` 
	WHERE `type` = 'venue'";

	echo $sql . "\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	//die();
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_corporation`
	(`case_corporation_uuid`,`case_uuid`,`corporation_uuid`,`attribute`,`last_updated_date`,`last_update_user`,`deleted`,`customer_id`)
	SELECT gc.`case_corporation_uuid`, gc.`case_uuid`, gc.`corporation_uuid`, gc.`attribute`, gc.`last_updated_date`, gc.`last_update_user`, gc.`deleted`, gc.`customer_id`
	FROM " . $data_source . "." . $data_source . "_case_corporation gc
	LEFT OUTER JOIN ikase_" . $data_source . ".cse_case_corporation corp
	ON gc.corporation_uuid = corp.corporation_uuid AND gc.case_uuid = corp.case_uuid
	WHERE gc.`attribute` = 'venue'
	AND corp.case_corporation_uuid IS NULL 
	AND gc.customer_id = " . $customer_id;

	echo $sql . "\r\n\r\n";
	//die();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_corporation`
	(`corporation_uuid`, `parent_corporation_uuid`, `full_name`, `company_name`, `type`, `first_name`, `last_name`, `aka`, `preferred_name`, `employee_phone`, `employee_fax`, `employee_email`, `full_address`, `longitude`, `latitude`, `street`, `city`, `state`, `zip`, `suite`, `company_site`, `phone`, `email`, `fax`, `ssn`, `dob`, `salutation`, `copying_instructions`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
	SELECT DISTINCT gc.`corporation_uuid`, gc.`parent_corporation_uuid`, gc.`full_name`, gc.`company_name`, gc.`type`, gc.`first_name`, gc.`last_name`, gc.`aka`, gc.`preferred_name`, gc.`employee_phone`, gc.`employee_fax`, gc.`employee_email`, gc.`full_address`, gc.`longitude`, gc.`latitude`, gc.`street`, gc.`city`, gc.`state`, gc.`zip`, gc.`suite`, gc.`company_site`, gc.`phone`, gc.`email`, gc.`fax`, gc.`ssn`, gc.`dob`, gc.`salutation`, gc.`copying_instructions`, gc.`last_updated_date`, gc.`last_update_user`, gc.`deleted`, gc.`customer_id` 
	FROM " . $data_source . "." . $data_source . "_corporation gc
	LEFT OUTER JOIN ikase_" . $data_source . ".cse_corporation corp
	ON gc.corporation_uuid = corp.corporation_uuid
	WHERE gc.`type` = 'venue'
	AND corp.corporation_uuid IS NULL 
	AND gc.customer_id = " . $customer_id;
	
	echo $sql . "\r\n\r\n";
	//die();
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
parent.setFeedback("venues transfer completed");
</script>
<?php
require_once('../shared/legacy_session.php');

include("connection.php");

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	$sql = "DELETE FROM `ikase_" . $data_source . "`.`cse_case_corporation`
	WHERE attribute = 'defendant'
	AND case_corporation_id > '63235'";
	
	echo $sql . "\r\n\r\n";
	$stmt = DB::run($sql);
	
	$sql = "DELETE FROM `ikase_" . $data_source . "`.`cse_corporation` 
	WHERE `type` = 'defendant'
	AND corporation_id > '67297'";

	echo $sql . "\r\n\r\n";
	$stmt = DB::run($sql);
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_corporation`
	(`case_corporation_uuid`, `case_uuid`, `corporation_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id`)
	SELECT `case_corporation_uuid`, `case_uuid`, `corporation_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id` 
	FROM `" . $data_source . "`.`" . $data_source . "_case_corporation` 
	WHERE 1 
	AND attribute = 'defendant'
	AND customer_id = " . $customer_id;

	echo $sql . "\r\n\r\n";
	$stmt = DB::run($sql);
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_corporation` (`corporation_uuid`, `parent_corporation_uuid`, `full_name`, `company_name`, `type`, `first_name`, `last_name`, `aka`, `preferred_name`, `employee_phone`, `employee_fax`, `employee_email`, `full_address`, `longitude`, `latitude`, `street`, `city`, `state`, `zip`, `suite`, `company_site`, `phone`, `email`, `fax`, `ssn`, `dob`, `salutation`, `last_updated_date`, `last_update_user`, `copying_instructions`, `deleted`, `customer_id`)
	SELECT`corporation_uuid`, `parent_corporation_uuid`, `full_name`, `company_name`, `type`, `first_name`, `last_name`, `aka`, `preferred_name`, `employee_phone`, `employee_fax`, `employee_email`, `full_address`, `longitude`, `latitude`, `street`, `city`, `state`, `zip`, `suite`, `company_site`, `phone`, `email`, `fax`, `ssn`, `dob`, `salutation`, `last_updated_date`, `last_update_user`, `copying_instructions`, `deleted`, `customer_id`
	FROM `" . $data_source . "`.`" . $data_source . "_corporation` 
	WHERE 1 
	AND `type` = 'defendant'
	AND customer_id = " . $customer_id;
	
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
parent.setFeedback("defendants transfer completed");
</script>

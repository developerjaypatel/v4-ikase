<?php
include("manage_session.php");
session_write_close();
include("connection.php");

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	$data_source = str_replace("2", "", $data_source);
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.cse_case_injury 
	(`case_injury_uuid`,`case_uuid`,`injury_uuid`,`attribute`,`last_updated_date`,`last_update_user`,`deleted`,`customer_id`)
	SELECT hci.`case_injury_uuid`,hci.`case_uuid`,
hci.`injury_uuid`,hci.`attribute`,hci.`last_updated_date`,hci.`last_update_user`,hci.`deleted`,hci.`customer_id`
	FROM " . $data_source . "." . $data_source . "_case_injury hci
	LEFT OUTER JOIN `ikase_" . $data_source . "`.cse_case_injury cci
	ON hci.case_uuid = cci.case_uuid AND hci.injury_uuid = cci.injury_uuid
	WHERE cci.case_injury_id IS NULL
	AND hci.case_uuid != '';";
	
	//die($sql);
	$db = getConnection(); $stmt = $db->prepare($sql);  
	$stmt->execute(); $stmt = null; $db = null;
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.cse_injury
	(`injury_uuid`, `injury_number`, `adj_number`, `type`, `occupation`, `start_date`, `end_date`, `ct_dates_note`, `body_parts`, `statute_limitation`, `explanation`, `deu`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `customer_id`, `deleted`)
	SELECT hci.`injury_uuid`, hci.`injury_number`, hci.`adj_number`, hci.`type`, hci.`occupation`, hci.`start_date`, hci.`end_date`, hci.`ct_dates_note`, hci.`body_parts`, hci.`statute_limitation`, hci.`explanation`, hci.`deu`, hci.`full_address`, hci.`street`, hci.`city`, hci.`state`, hci.`zip`, hci.`suite`, hci.`customer_id`, hci.`deleted`
	FROM " . $data_source . "." . $data_source . "_injury hci
	LEFT OUTER JOIN `ikase_" . $data_source . "`.cse_injury cci
	ON hci.injury_uuid = cci.injury_uuid
	WHERE cci.injury_id IS NULL;";
	
	$db = getConnection(); $stmt = $db->prepare($sql);  
	$stmt->execute(); $stmt = null; $db = null;
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.cse_case_corporation
	(`case_corporation_uuid`, `case_uuid`, `corporation_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
	SELECT hci.`case_corporation_uuid`, hci.`case_uuid`, hci.`corporation_uuid`, hci.`injury_uuid`, hci.`attribute`, hci.`last_updated_date`, hci.`last_update_user`, hci.`deleted`, hci.`customer_id`
	FROM " . $data_source . "." . $data_source . "_case_corporation hci
	LEFT OUTER JOIN `ikase_" . $data_source . "`.cse_case_corporation cci
	ON hci.case_uuid = cci.case_uuid AND hci.corporation_uuid = cci.corporation_uuid
	WHERE cci.case_corporation_id IS NULL
	AND hci.case_uuid != '';";
	
	
	$db = getConnection(); $stmt = $db->prepare($sql);  
	$stmt->execute(); $stmt = null; $db = null;
	
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.cse_corporation
	(`corporation_uuid`, `parent_corporation_uuid`, `full_name`, `company_name`, `type`, `first_name`, `last_name`, `aka`, `preferred_name`, `employee_phone`, `employee_fax`, `employee_email`, `full_address`, `longitude`, `latitude`, `street`, `city`, `state`, `zip`, `suite`, `company_site`, `phone`, `email`, `fax`, `ssn`, `dob`, `salutation`, `copying_instructions`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
	SELECT hci.`corporation_uuid`, hci.`parent_corporation_uuid`, hci.`full_name`, hci.`company_name`, hci.`type`, hci.`first_name`, hci.`last_name`, hci.`aka`, hci.`preferred_name`, hci.`employee_phone`, hci.`employee_fax`, hci.`employee_email`, hci.`full_address`, hci.`longitude`, hci.`latitude`, hci.`street`, hci.`city`, hci.`state`, hci.`zip`, hci.`suite`, hci.`company_site`, hci.`phone`, hci.`email`, hci.`fax`, hci.`ssn`, hci.`dob`, hci.`salutation`, hci.`copying_instructions`, hci.`last_updated_date`, hci.`last_update_user`, hci.`deleted`, hci.`customer_id` 
	FROM " . $data_source . "." . $data_source . "_corporation hci
	LEFT OUTER JOIN `ikase_" . $data_source . "`.cse_corporation cci
	ON hci.corporation_uuid = cci.corporation_uuid
	WHERE cci.corporation_id IS NULL;";
	
	$db = getConnection(); $stmt = $db->prepare($sql);  
	$stmt->execute(); $stmt = null; $db = null;
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.cse_case_person
	(`case_person_uuid`,`case_uuid`,`person_uuid`,`attribute`,`last_updated_date`,`last_update_user`, `deleted`,`customer_id`)
	SELECT hci.`case_person_uuid`, hci.`case_uuid`, hci.`person_uuid`, hci.`attribute`, hci.`last_updated_date`, hci.`last_update_user`, hci.`deleted`, hci.`customer_id`
	FROM " . $data_source . "." . $data_source . "_case_person hci
	LEFT OUTER JOIN `ikase_" . $data_source . "`.cse_case_person cci
	ON hci.case_uuid = cci.case_uuid AND hci.person_uuid = cci.person_uuid
	WHERE cci.case_person_id IS NULL
	AND hci.case_uuid != '';";
	
	$db = getConnection(); $stmt = $db->prepare($sql);  
	$stmt->execute(); $stmt = null; $db = null;	
	
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.cse_person
	(`person_uuid`, `parent_person_uuid`, `full_name`, `company_name`, `first_name`, `middle_name`, `last_name`, `aka`, `preferred_name`, `full_address`, `longitude`, `latitude`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `email`, `fax`, `work_phone`, `cell_phone`, `work_email`, `ssn`, `ssn_last_four`, `dob`, `license_number`, `title`, `ref_source`, `salutation`, `age`, `priority_flag`, `gender`, `language`, `birth_state`, `birth_city`, `marital_status`, `legal_status`, `spouse`, `spouse_contact`, `emergency`, `emergency_contact`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
	SELECT hci.`person_uuid`, hci.`parent_person_uuid`, hci.`full_name`, hci.`company_name`, hci.`first_name`, hci.`middle_name`, hci.`last_name`, hci.`aka`, hci.`preferred_name`, hci.`full_address`, hci.`longitude`, hci.`latitude`, hci.`street`, hci.`city`, hci.`state`, hci.`zip`, hci.`suite`, hci.`phone`, hci.`email`, hci.`fax`, hci.`work_phone`, hci.`cell_phone`, hci.`work_email`, hci.`ssn`, hci.`ssn_last_four`, hci.`dob`, hci.`license_number`, hci.`title`, hci.`ref_source`, hci.`salutation`, hci.`age`, hci.`priority_flag`, hci.`gender`, hci.`language`, hci.`birth_state`, hci.`birth_city`, hci.`marital_status`, hci.`legal_status`, hci.`spouse`, hci.`spouse_contact`, hci.`emergency`, hci.`emergency_contact`, hci.`last_updated_date`, hci.`last_update_user`, hci.`deleted`, hci.`customer_id` 
	FROM " . $data_source . "." . $data_source . "_person hci
	LEFT OUTER JOIN `ikase_" . $data_source . "`.cse_person cci
	ON hci.person_uuid = cci.person_uuid
	WHERE cci.person_id IS NULL;";
	
	$db = getConnection(); $stmt = $db->prepare($sql);  
	$stmt->execute(); $stmt = null; $db = null;
	
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.cse_corporation_adhoc
	(`adhoc_uuid`, `case_uuid`, `corporation_uuid`,`adhoc`, `adhoc_value`, `customer_id`, `deleted`)
	SELECT hci.`adhoc_uuid`, hci.`case_uuid`, hci.`corporation_uuid`, hci.`adhoc`, hci.`adhoc_value`, hci.`customer_id`, hci.`deleted` 
	FROM " . $data_source . "." . $data_source . "_corporation_adhoc hci
	LEFT OUTER JOIN `ikase_" . $data_source . "`.cse_corporation_adhoc cci
	ON hci.case_uuid = cci.case_uuid
	WHERE cci.adhoc_id IS NULL;";
//die($sql); 
	$db = getConnection(); $stmt = $db->prepare($sql);  
	$stmt->execute(); $stmt = null; $db = null;	
	
	$success = array("success"=> array("text"=>"done"));
	echo json_encode($success);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
	die();
}	
//, `notedesc`, , `notepoint`, `notelock`, `lockedby`, `xsoundex`, `locator`, `enteredby`, `mailpoint`, `lockloc`, `docpointer`, `doctype`

//include("cls_logging.php");
?>
<script language="javascript">
parent.setFeedback("missing injuries transfer completed");
</script>
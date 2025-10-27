<?php
$customer_id = 1103;

include("connection.php");

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	$arrSQL = array();
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_partie_type`";
	//die($sql);
	/*
$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();*/ $arrSQL[] = $sql;
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_eams_forms`";
	/*
$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();*/ $arrSQL[] = $sql;
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_case`";
	/*
$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();*/ $arrSQL[] = $sql;
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_case_corporation`";
	//die($sql);
	/*
$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();*/ $arrSQL[] = $sql;
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_case_injury`";
	/*
$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();*/ $arrSQL[] = $sql;
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_case_notes`";
	/*
$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();*/ $arrSQL[] = $sql;
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_case_person`";
	/*
$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();*/ $arrSQL[] = $sql;
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_corporation`";
	/*
$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();*/ $arrSQL[] = $sql;
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_injury`";
	/*
$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();*/ $arrSQL[] = $sql;
	
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_injury_number`";
	/*
$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();*/ $arrSQL[] = $sql;
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_injury_injury_number`";
	/*
$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();*/ $arrSQL[] = $sql;
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_notes`";
	/*
$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();*/ $arrSQL[] = $sql;
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_person`";
	/*
$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();*/ $arrSQL[] = $sql;
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_venue`";
	/*
$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();*/ $arrSQL[] = $sql;
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_bodyparts`";
	/*
$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();*/ $arrSQL[] = $sql;
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_bodyparts` SELECT * FROM ikase.cse_bodyparts";
	/*
$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();*/ $arrSQL[] = $sql;
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_eams_forms`
	SELECT * FROM `ikase`.`cse_eams_forms` WHERE deleted = 'N'
	AND name != ''";
	/*
$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();*/ $arrSQL[] = $sql;
	
	//start inserting
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_partie_type` (partie_type, blurb, sort_order)
SELECT DISTINCT UPPER(REPLACE(`type`, '_', ' ')), `type`, 60
				FROM `" . $data_source . "`.`" . $data_source . "_corporation`
                WHERE `type` NOT IN (SELECT blurb FROM ikase.`cse_partie_type`)";
	//die($sql);
	/*
$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();*/ $arrSQL[] = $sql;
	
	$sql = "INSERT INTO `ikase_ramirez`.`cse_partie_type` (partie_type, blurb, sort_order)
SELECT partie_type, blurb, sort_order
				FROM ikase.`cse_partie_type`
                WHERE `blurb` NOT IN (SELECT blurb FROM `ikase_ramirez`.`cse_partie_type`)";
	
	/*
$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();*/ $arrSQL[] = $sql;
			
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case`
(`case_uuid`,`case_number`, `file_number`, `cpointer`, `case_name`, `source`, `adj_number`, `case_date`, `case_type`, `venue`, `dois`, `case_status`, `case_substatus`, `rating`, `submittedOn`, `attorney`, `worker`, `deleted`, `customer_id`, `medical`, `td`, `rehab`, `edd`)
SELECT `case_uuid`, `case_number`, `file_number`, `cpointer`, `case_name`, 'merus', `adj_number`, `case_date`, `case_type`, `venue`, `dois`, `case_status`, `case_substatus`, `rating`, `submittedOn`, `attorney`, `worker`, `deleted`, `customer_id`, `medical`, `td`, `rehab`, `edd` 
FROM `" . $data_source . "`.`" . $data_source . "_case` WHERE 1 AND `customer_id` = " . $customer_id;
	//die($sql);
	/*
$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();*/ $arrSQL[] = $sql;
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_corporation`
(`case_corporation_uuid`, `case_uuid`, `corporation_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
SELECT `case_corporation_uuid`, `case_uuid`, `corporation_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id` 
FROM `" . $data_source . "`.`" . $data_source . "_case_corporation` WHERE 1 AND `customer_id` = " . $customer_id;

	/*
$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();*/ $arrSQL[] = $sql;
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_injury`
(`case_injury_uuid`, `case_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
SELECT `case_injury_uuid`, `case_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id` 
FROM `" . $data_source . "`.`" . $data_source . "_case_injury` WHERE 1 AND `customer_id` = " . $customer_id;

	/*
$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();*/ $arrSQL[] = $sql;
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_notes`
(`case_notes_uuid`, `case_uuid`, `notes_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
SELECT `case_notes_uuid`, `case_uuid`, `notes_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id` 
FROM `" . $data_source . "`.`" . $data_source . "_case_notes` WHERE 1 AND `customer_id` = " . $customer_id;

	/*
$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();*/ $arrSQL[] = $sql;
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_person`
(`case_person_uuid`, `case_uuid`, `person_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
SELECT `case_person_uuid`, `case_uuid`, `person_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id` 
FROM `" . $data_source . "`.`" . $data_source . "_case_person` WHERE 1 AND `customer_id` = " . $customer_id;

	/*
$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();*/ $arrSQL[] = $sql;
	
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_corporation` (`corporation_uuid`, `parent_corporation_uuid`, `full_name`, `company_name`, `type`, `first_name`, `last_name`, `aka`, `preferred_name`, `employee_phone`, `employee_fax`, `employee_email`, `full_address`, `longitude`, `latitude`, `street`, `city`, `state`, `zip`, `suite`, `company_site`, `phone`, `email`, `fax`, `ssn`, `dob`, `salutation`, `last_updated_date`, `last_update_user`, `copying_instructions`, `deleted`, `customer_id`)
SELECT `corporation_uuid`, `parent_corporation_uuid`, `full_name`, `company_name`, `type`, `first_name`, `last_name`, `aka`, `preferred_name`, `employee_phone`, `employee_fax`, `employee_email`, `full_address`, `longitude`, `latitude`, `street`, `city`, `state`, `zip`, `suite`, `company_site`, `phone`, `email`, `fax`, `ssn`, `dob`, `salutation`, `last_updated_date`, `last_update_user`, '', `deleted`, `customer_id` FROM `" . $data_source . "`.`" . $data_source . "_corporation` WHERE 1 AND `customer_id` = " . $customer_id;

	/*
$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();*/ $arrSQL[] = $sql;
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_injury` (`injury_uuid`, `injury_number`, `adj_number`, `type`, `occupation`, `start_date`, `end_date`, `body_parts`, `statute_limitation`, `explanation`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `customer_id`, `deleted`)
SELECT `injury_uuid`, `injury_number`, `adj_number`, `type`, `occupation`, `start_date`, `end_date`, `body_parts`, `statute_limitation`, `explanation`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `customer_id`, `deleted` FROM `" . $data_source . "`.`" . $data_source . "_injury` WHERE 1 AND `customer_id` = " . $customer_id;

	/*
$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();*/ $arrSQL[] = $sql;
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_injury_injury_number`
(`injury_injury_number_uuid`, `injury_uuid`, `injury_number_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
SELECT `injury_injury_number_uuid`, `injury_uuid`, `injury_number_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id` 
FROM `" . $data_source . "`.`" . $data_source . "_injury_injury_number` WHERE 1 AND `customer_id` = " . $customer_id;

	/*
$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();*/ $arrSQL[] = $sql;
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_injury_number` (`injury_number_uuid`, `insurance_policy_number`, `alternate_policy_number`, `carrier_claim_number`, `alternate_claim_number`, `carrier_building_indentifier`, `carrier_building_description`, `customer_id`, `deleted`)
SELECT `injury_number_uuid`, `insurance_policy_number`, `alternate_policy_number`, `carrier_claim_number`, `alternate_claim_number`, `carrier_building_indentifier`, `carrier_building_description`, `customer_id`, `deleted` FROM `" . $data_source . "`.`" . $data_source . "_injury_number` WHERE 1 AND `customer_id` = " . $customer_id;

	/*
$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();*/ $arrSQL[] = $sql;
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_notes` (`notes_uuid`, `type`, `subject`, `note`, `title`, `attachments`, `entered_by`, `status`, `dateandtime`, `callback_date`, `verified`, `deleted`, `customer_id`)
SELECT `notes_uuid`, `type`, `subject`, `note`, `title`, `attachments`, `entered_by`, `status`, `dateandtime`, `callback_date`, `verified`, `deleted`, `customer_id` FROM `" . $data_source . "`.`" . $data_source . "_notes` WHERE 1 AND `customer_id` = " . $customer_id;
	
	/*
$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();*/ $arrSQL[] = $sql;
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_person` (`person_uuid`, `parent_person_uuid`, `full_name`, `company_name`, `first_name`, `middle_name`, `last_name`, `aka`, `preferred_name`, `full_address`, `longitude`, `latitude`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `email`, `fax`, `work_phone`, `cell_phone`, `work_email`, `ssn`, `ssn_last_four`, `dob`, `license_number`, `title`, `ref_source`, `salutation`, `age`, `priority_flag`, `gender`, `language`, `birth_state`, `birth_city`, `marital_status`, `legal_status`, `spouse`, `spouse_contact`, `emergency`, `emergency_contact`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
SELECT `person_uuid`, `parent_person_uuid`, `full_name`, `company_name`, `first_name`, `middle_name`, `last_name`, `aka`, `preferred_name`, `full_address`, `longitude`, `latitude`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `email`, `fax`, `work_phone`, `cell_phone`, `work_email`, `ssn`, `ssn_last_four`, `dob`, `license_number`, `title`, `ref_source`, `salutation`, `age`, `priority_flag`, `gender`, `language`, `birth_state`, `birth_city`, `marital_status`, `legal_status`, `spouse`, `spouse_contact`, `emergency`, `emergency_contact`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id` FROM `" . $data_source . "`.`" . $data_source . "_person` WHERE 1 AND `customer_id` = " . $customer_id;

	/*
$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();*/ $arrSQL[] = $sql;
	
	$sql = "INSERT INTO ikase_" . $data_source . ".cse_setting (`setting_uuid`, `customer_id`, `category`, `setting`, `setting_value`, `setting_type`, `default_value`, `deleted`)
SELECT `setting_uuid`, '" . $customer_id . "', `category`, `setting`, `setting_value`, `setting_type`, `default_value`, `deleted`
			FROM  ikase.`cse_setting` cs 
			where customer_id = 1033
            AND (category = 'delay' OR category = 'time')";
	
	/*
$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();*/ $arrSQL[] = $sql;
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_activity`;TRUNCATE `ikase_" . $data_source . "`.`cse_case_activity`";
	/*
$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();*/ $arrSQL[] = $sql;
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_activity` (`activity_uuid`, `activity`, `activity_category`, `activity_date`, `hours`, `timekeeper`, `activity_user_id`, `customer_id`, `deleted`)
	SELECT `activity_uuid`, `activity`, `activity_category`, `activity_date`, `hours`, `timekeeper`, `activity_user_id`, `customer_id`, `deleted`
	FROM `" . $data_source . "`.`" . $data_source . "_activity` 
	WHERE 1 AND customer_id = " . $customer_id;
	/*
$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();*/ $arrSQL[] = $sql;
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_activity`
	(`case_activity_uuid`, `case_uuid`, `activity_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id`)
	SELECT `case_activity_uuid`, `case_uuid`, `activity_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id` 
	FROM `" . $data_source . "`.`" . $data_source . "_case_activity` 
	WHERE 1 AND customer_id = " . $customer_id;
	
	/*
$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();*/ $arrSQL[] = $sql;
	
	$sql = implode(";\r\n\r\n", $arrSQL) . ";";
	
	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();
	
	$db = null;
	
	$success = array("success"=> array("text"=>"done"));
	echo json_encode($success);
} catch(PDOException $e) {
	echo "<br />" . $sql . "<br />";
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
	die();
}	
//, `notedesc`, , `notepoint`, `notelock`, `lockedby`, `xsoundex`, `locator`, `enteredby`, `mailpoint`, `lockloc`, `docpointer`, `doctype`

include("cls_logging.php");
?>
<script language="javascript">
parent.setFeedback("main transfer completed");
</script>
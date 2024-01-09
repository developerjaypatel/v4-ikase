<?php
require_once('../shared/legacy_session.php');
session_write_close();
include("connection.php");

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_case`";
	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_case_corporation`";
	//die($sql);
	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_case_injury`";
	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_case_notes`";
	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_case_document`";
	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_case_person`";
	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_corporation_adhoc`";
	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_corporation`";
	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_exam`";
	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_corporation_exam`";
	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_injury`";
	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_injury_injury_number`";
	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_injury_number`";
	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_notes`";
	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_document`";
	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_person`";
	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_venue`";
	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_customer`";
	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_user`";
	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_eams_forms`";
	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_bodyparts`";
	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_bodyparts` SELECT * FROM ikase.cse_bodyparts";
	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	/*
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_customer` SELECT * FROM `ikase`.`cse_customer` WHERE customer_id = " . $customer_id;
	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_user` SELECT * FROM ikase.cse_user where customer_id = " . $customer_id;
	
	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	*/
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_eams_forms`
	SELECT * FROM `ikase`.`cse_eams_forms` WHERE deleted = 'N'
	AND name != ''";
	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case`
(`case_uuid`,`case_number`, `cpointer`, `case_name`, `source`, `adj_number`, `case_date`, `case_type`, `venue`, `dois`, `case_status`, `case_substatus`, `rating`, `submittedOn`, `attorney`, `worker`, `deleted`, `customer_id`, `medical`, `td`, `rehab`, `edd`)
SELECT `case_uuid`, `case_number`, `cpointer`, `case_name`, 'perfect', `adj_number`, `case_date`, `case_type`, `venue`, `dois`, `case_status`, `case_substatus`, `rating`, `submittedOn`, `attorney`, `worker`, `deleted`, `customer_id`, `medical`, `td`, `rehab`, `edd` 
FROM `" . $data_source . "`.`" . $data_source . "_case` WHERE 1 AND `customer_id` = " . $customer_id;
	//die($sql);
	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_corporation`
(`case_corporation_uuid`, `case_uuid`, `corporation_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
SELECT `case_corporation_uuid`, `case_uuid`, `corporation_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id` 
FROM `" . $data_source . "`.`" . $data_source . "_case_corporation` WHERE 1 AND `customer_id` = " . $customer_id;

	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_injury`
(`case_injury_uuid`, `case_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
SELECT `case_injury_uuid`, `case_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id` 
FROM `" . $data_source . "`.`" . $data_source . "_case_injury` WHERE 1 AND `customer_id` = " . $customer_id;

	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_notes`
(`case_notes_uuid`, `case_uuid`, `notes_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
SELECT `case_notes_uuid`, `case_uuid`, `notes_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id` 
FROM `" . $data_source . "`.`" . $data_source . "_case_notes` WHERE 1 AND `customer_id` = " . $customer_id;

	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_person`
(`case_person_uuid`, `case_uuid`, `person_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
SELECT `case_person_uuid`, `case_uuid`, `person_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id` 
FROM `" . $data_source . "`.`" . $data_source . "_case_person` WHERE 1 AND `customer_id` = " . $customer_id;

	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	/*
		$sql = "ALTER TABLE `ikase_" . $data_source . "`.`cse_venue` 
	CHANGE COLUMN `venue_id` `venue_id` INT(11) NOT NULL AUTO_INCREMENT ;";
	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	*/

	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_venue`
(`case_venue_uuid`, `case_uuid`, `venue_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
SELECT `case_venue_uuid`, `case_uuid`, `venue_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id` 
FROM `" . $data_source . "`.`" . $data_source . "_case_venue` WHERE 1 AND `customer_id` = " . $customer_id;

	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_venue` (`venue_uuid`, `venue`, `venue_abbr`, `address1`, `address2`, `city`, `zip`, `phone`, `presiding`)
SELECT `venue_uuid`, `venue`, `venue_abbr`, `address1`, `address2`, `city`, `zip`, `phone`, `presiding` 
FROM `ikase`.`cse_venue` WHERE 1";

	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_corporation` (`corporation_uuid`, `parent_corporation_uuid`, `full_name`, `company_name`, `type`, `first_name`, `last_name`, `aka`, `preferred_name`, `employee_phone`, `employee_fax`, `employee_email`, `full_address`, `longitude`, `latitude`, `street`, `city`, `state`, `zip`, `suite`, `company_site`, `phone`, `email`, `fax`, `ssn`, `dob`, `salutation`, `last_updated_date`, `last_update_user`, `copying_instructions`, `deleted`, `customer_id`)
SELECT `corporation_uuid`, `parent_corporation_uuid`, `full_name`, `company_name`, `type`, `first_name`, `last_name`, `aka`, `preferred_name`, `employee_phone`, `employee_fax`, `employee_email`, `full_address`, `longitude`, `latitude`, `street`, `city`, `state`, `zip`, `suite`, `company_site`, `phone`, `email`, `fax`, `ssn`, `dob`, `salutation`, `last_updated_date`, `last_update_user`, '', `deleted`, `customer_id` FROM `" . $data_source . "`.`" . $data_source . "_corporation` WHERE 1 AND `customer_id` = " . $customer_id;

	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_corporation_adhoc` (`adhoc_uuid`, `case_uuid`, `corporation_uuid`, `adhoc`, `adhoc_value`, `customer_id`, `deleted`)
SELECT `adhoc_uuid`, `case_uuid`, `corporation_uuid`, `adhoc`, `adhoc_value`, `customer_id`, `deleted` FROM `" . $data_source . "`.`" . $data_source . "_corporation_adhoc` WHERE 1 AND `customer_id` = " . $customer_id;

	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_exam`
(`exam_uuid`, `exam_dateandtime`, `exam_status`, `exam_type`, `specialty`, `requestor`, `comments`, `permanent_stationary`, `fs_date`, `customer_id`, `deleted`)
SELECT `exam_uuid`, `exam_dateandtime`, `exam_status`, `exam_type`, `specialty`, `requestor`, `comments`, `permanent_stationary`, `fs_date`, `customer_id`, `deleted`
FROM `" . $data_source . "`.`" . $data_source . "_exam` WHERE 1 AND `customer_id` = " . $customer_id;

	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_corporation_exam`
(`corporation_exam_uuid`, `corporation_uuid`, `exam_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
SELECT `corporation_exam_uuid`, `corporation_uuid`, `exam_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`
FROM `" . $data_source . "`.`" . $data_source . "_corporation_exam` WHERE 1 AND `customer_id` = " . $customer_id;

	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_injury` (`injury_uuid`, `injury_number`, `adj_number`, `type`, `occupation`, `start_date`, `end_date`, `body_parts`, `statute_limitation`, `explanation`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `customer_id`, `deleted`)
SELECT `injury_uuid`, `injury_number`, `adj_number`, `type`, `occupation`, `start_date`, `end_date`, `body_parts`, `statute_limitation`, `explanation`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `customer_id`, `deleted` FROM `" . $data_source . "`.`" . $data_source . "_injury` WHERE 1 AND `customer_id` = " . $customer_id;

	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_injury_injury_number`
(`injury_injury_number_uuid`, `injury_uuid`, `injury_number_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
SELECT `injury_injury_number_uuid`, `injury_uuid`, `injury_number_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id` 
FROM `" . $data_source . "`.`" . $data_source . "_injury_injury_number` WHERE 1 AND `customer_id` = " . $customer_id;

	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_injury_number` (`injury_number_uuid`, `insurance_policy_number`, `alternate_policy_number`, `carrier_claim_number`, `alternate_claim_number`, `carrier_building_indentifier`, `carrier_building_description`, `customer_id`, `deleted`)
SELECT `injury_number_uuid`, `insurance_policy_number`, `alternate_policy_number`, `carrier_claim_number`, `alternate_claim_number`, `carrier_building_indentifier`, `carrier_building_description`, `customer_id`, `deleted` FROM `" . $data_source . "`.`" . $data_source . "_injury_number` WHERE 1 AND `customer_id` = " . $customer_id;

	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_notes` (`notes_uuid`, `type`, `subject`, `note`, `title`, `attachments`, `entered_by`, `status`, `dateandtime`, `callback_date`, `verified`, `deleted`, `customer_id`)
SELECT `notes_uuid`, `type`, `subject`, `note`, `title`, `attachments`, `entered_by`, `status`, `dateandtime`, `callback_date`, `verified`, `deleted`, `customer_id` FROM `" . $data_source . "`.`" . $data_source . "_notes` WHERE 1 AND `customer_id` = " . $customer_id;
	
	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_person` (`person_uuid`, `parent_person_uuid`, `full_name`, `company_name`, `first_name`, `middle_name`, `last_name`, `aka`, `preferred_name`, `full_address`, `longitude`, `latitude`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `email`, `fax`, `work_phone`, `cell_phone`, `work_email`, `ssn`, `ssn_last_four`, `dob`, `license_number`, `title`, `ref_source`, `salutation`, `age`, `priority_flag`, `gender`, `language`, `birth_state`, `birth_city`, `marital_status`, `legal_status`, `spouse`, `spouse_contact`, `emergency`, `emergency_contact`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
SELECT `person_uuid`, `parent_person_uuid`, `full_name`, `company_name`, `first_name`, `middle_name`, `last_name`, `aka`, `preferred_name`, `full_address`, `longitude`, `latitude`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `email`, `fax`, `work_phone`, `cell_phone`, `work_email`, `ssn`, `ssn_last_four`, `dob`, `license_number`, `title`, `ref_source`, `salutation`, `age`, `priority_flag`, `gender`, `language`, `birth_state`, `birth_city`, `marital_status`, `legal_status`, `spouse`, `spouse_contact`, `emergency`, `emergency_contact`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id` FROM `" . $data_source . "`.`" . $data_source . "_person` WHERE 1 AND `customer_id` = " . $customer_id;

	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_document` (document_uuid, document_date, document_name, document_filename, document_extension, source, description, description_html, customer_id)
	SELECT document_uuid, document_date, document_name, document_filename, document_extension, source, description, description_html, customer_id
	FROM `" . $data_source . "`.`" . $data_source . "_document` WHERE 1 AND `customer_id` = " . $customer_id;
	
	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_document`
(`case_document_uuid`, `case_uuid`, `document_uuid`, `attribute_1`, `attribute_2`, `last_updated_date`, `last_update_user`, `customer_id`)
SELECT `case_document_uuid`, `case_uuid`, `document_uuid`, `attribute_1`, `attribute_2`, `last_updated_date`, `last_update_user`, `customer_id`
FROM `" . $data_source . "`.`" . $data_source . "_case_document` WHERE 1 AND `customer_id` = " . $customer_id;

	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
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
parent.setFeedback("main transfer completed");
</script>

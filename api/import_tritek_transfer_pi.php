<?php
require_once('../shared/legacy_session.php');

include("connection.php");

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
		
	//cancel current pis
	/*
	$sql = "
	TRUNCATE " . $data_source . ".badcases;

	INSERT INTO " . $data_source . ".badcases (case_uuid, cpointer)
	SELECT case_uuid, cpointer
	FROM " . $data_source . "." . $data_source . "_case
	WHERE deleted = 'N';
	
	UPDATE ikase_" . $data_source . ".cse_case
	SET deleted = 'C'
	WHERE case_type != 'WCAB'
	AND INSTR(case_type, 'Workers') = 0
	AND case_type != 'social_security'
	AND case_type != 'Social Security';";
	//$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	//$stmt->execute();
	*/
	//current case_id = '8510'
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case`
(`case_uuid`,`case_number`, `cpointer`, `case_name`, `source`, `adj_number`, `case_date`, `case_type`, `venue`, `dois`, `case_status`, `case_substatus`, `rating`, `submittedOn`, `attorney`, `worker`, `deleted`, `customer_id`, `medical`, `td`, `rehab`, `edd`)
SELECT `case_uuid`, `case_number`, `cpointer`, `case_name`, 'tritek', `adj_number`, `case_date`, `case_type`, `venue`, `dois`, `case_status`, `case_substatus`, `rating`, `submittedOn`, `attorney`, `worker`, `deleted`, `customer_id`, `medical`, `td`, `rehab`, `edd` 
FROM `" . $data_source . "`.`" . $data_source . "_case` 
WHERE 1 
AND deleted = 'N'
AND `customer_id` = " . $customer_id;
	//$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	//$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_corporation`
(`case_corporation_uuid`, `case_uuid`, `corporation_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
SELECT `case_corporation_uuid`, cpa.`case_uuid`, `corporation_uuid`, `attribute`, `last_updated_date`, `last_update_user`, cpa.`deleted`, cpa.`customer_id` 
FROM `" . $data_source . "`.`" . $data_source . "_case_corporation` cpa 
INNER JOIN " . $data_source . ".badcases
	ON cpa.case_uuid = badcases.case_uuid 
WHERE 1 ";
	//$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	//$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_injury`
(`case_injury_uuid`, `case_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
SELECT `case_injury_uuid`, cpa.`case_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, cpa.`deleted`, cpa.`customer_id` 
FROM `" . $data_source . "`.`" . $data_source . "_case_injury`  cpa 
INNER JOIN " . $data_source . ".badcases
	ON cpa.case_uuid = badcases.case_uuid 
WHERE 1";

	//$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	//$stmt->execute();
	
	//out of range last_updated_date warning
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_notes`
(`case_notes_uuid`, `case_uuid`, `notes_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
SELECT `case_notes_uuid`, cpa.`case_uuid`, `notes_uuid`, `attribute`, `last_updated_date`, `last_update_user`, cpa.`deleted`, cpa.`customer_id` 
FROM `" . $data_source . "`.`" . $data_source . "_case_notes`   cpa 
INNER JOIN " . $data_source . ".badcases
	ON cpa.case_uuid = badcases.case_uuid 
 WHERE 1 ";

	//$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	//$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_person`
(`case_person_uuid`, `case_uuid`, `person_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
SELECT `case_person_uuid`, cpa.`case_uuid`, `person_uuid`, `attribute`, `last_updated_date`, `last_update_user`, cpa.`deleted`, cpa.`customer_id` 
FROM `" . $data_source . "`.`" . $data_source . "_case_person`  cpa 
INNER JOIN " . $data_source . ".badcases
	ON cpa.case_uuid = badcases.case_uuid 
 WHERE 1 ";
 
	//$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	//$stmt->execute();

	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_venue`
(`case_venue_uuid`, `case_uuid`, `venue_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
SELECT `case_venue_uuid`, cpa.`case_uuid`, `venue_uuid`, `attribute`, `last_updated_date`, `last_update_user`, cpa.`deleted`, cpa.`customer_id` 
FROM `" . $data_source . "`.`" . $data_source . "_case_venue`  cpa 
INNER JOIN " . $data_source . ".badcases
	ON cpa.case_uuid = badcases.case_uuid 
 WHERE 1 ";
	//$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	//$stmt->execute();
	
	
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_corporation` (`corporation_uuid`, `parent_corporation_uuid`, `full_name`, `company_name`, `type`, `first_name`, `last_name`, `aka`, `preferred_name`, `employee_phone`, `employee_fax`, `employee_email`, `full_address`, `longitude`, `latitude`, `street`, `city`, `state`, `zip`, `suite`, `company_site`, `phone`, `email`, `fax`, `ssn`, `dob`, `salutation`, `last_updated_date`, `last_update_user`, `copying_instructions`, `deleted`, `customer_id`)
SELECT tab.`corporation_uuid`, tab.`parent_corporation_uuid`, tab.`full_name`, tab.`company_name`, 
tab.`type`, tab.`first_name`, tab.`last_name`, tab.`aka`, tab.`preferred_name`, tab.`employee_phone`, tab.`employee_fax`, tab.`employee_email`, 
tab.`full_address`, tab.`longitude`, tab.`latitude`, tab.`street`, tab.`city`, tab.`state`, tab.`zip`, tab.`suite`, tab.`company_site`, 
tab.`phone`, tab.`email`, tab.`fax`, tab.`ssn`, tab.`dob`, tab.`salutation`, tab.`last_updated_date`, tab.`last_update_user`, '', tab.`deleted`, tab.`customer_id` 
FROM `" . $data_source . "`.`" . $data_source . "_corporation` tab
INNER JOIN  `" . $data_source . "`.`" . $data_source . "_case_corporation` cpa 
ON tab.corporation_uuid = cpa.corporation_uuid
INNER JOIN " . $data_source . ".badcases
	ON cpa.case_uuid = badcases.case_uuid 
 WHERE 1  ";

	//$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	//$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_corporation_adhoc` (`adhoc_uuid`, `case_uuid`, `corporation_uuid`, `adhoc`, `adhoc_value`, `customer_id`, `deleted`)
SELECT `adhoc_uuid`, adh.`case_uuid`, `corporation_uuid`, `adhoc`, `adhoc_value`, adh.`customer_id`, adh.`deleted` 
FROM `" . $data_source . "`.`" . $data_source . "_corporation_adhoc` adh
INNER JOIN " . $data_source . ".badcases
	ON adh.case_uuid = badcases.case_uuid 
WHERE 1 ";

	//$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	//$stmt->execute();
	/*
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_corporation_exam`
(`corporation_exam_uuid`, `corporation_uuid`, `exam_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
SELECT `corporation_exam_uuid`, tab.`corporation_uuid`, `exam_uuid`, `attribute`, tab.`last_updated_date`, tab.`last_update_user`, tab.`deleted`, tab.`customer_id`
FROM `" . $data_source . "`.`" . $data_source . "_corporation_exam` tab
INNER JOIN `ikase_" . $data_source . "`.`cse_corporation` corp
ON tab.corporation_uuid = corp.corporation_uuid
WHERE 1 ";

	//$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	//$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_exam`
(`exam_uuid`, `exam_dateandtime`, `exam_status`, `exam_type`, `specialty`, `requestor`, `comments`, `permanent_stationary`, `fs_date`, `customer_id`, `deleted`)
SELECT ex.`exam_uuid`, `exam_dateandtime`, `exam_status`, `exam_type`, `specialty`, `requestor`, `comments`, `permanent_stationary`, `fs_date`, ex.`customer_id`, ex.`deleted`
FROM `" . $data_source . "`.`" . $data_source . "_exam` ex
INNER JOIN `ikase_" . $data_source . "`.`cse_corporation_exam` tab
ON ex.exam_uuid = tab.exam_uuid
WHERE 1 ";

	//$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	//$stmt->execute();
	*/
	
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_injury` (`injury_uuid`, `injury_number`, `adj_number`, `type`, `occupation`, `start_date`, `end_date`, `ct_dates_note`, `body_parts`, `statute_limitation`, `explanation`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `customer_id`, `deleted`)
SELECT inj.`injury_uuid`, `injury_number`, `adj_number`, `type`, `occupation`, `start_date`, `end_date`, `ctdates`, `body_parts`, `statute_limitation`, `explanation`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, inj.`customer_id`, inj.`deleted` 
FROM `" . $data_source . "`.`" . $data_source . "_injury` inj
INNER JOIN `ikase_" . $data_source . "`.`cse_case_injury` cci
ON inj.injury_uuid = cci.injury_uuid
INNER JOIN " . $data_source . ".badcases
	ON cci.case_uuid = badcases.case_uuid 
WHERE 1 ";

	//$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	//$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_injury_injury_number`
(`injury_injury_number_uuid`, `injury_uuid`, `injury_number_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
SELECT `injury_injury_number_uuid`, tab.`injury_uuid`, tab.`injury_number_uuid`, tab.`attribute`, tab.`last_updated_date`, tab.`last_update_user`, tab.`deleted`, tab.`customer_id` 
FROM `" . $data_source . "`.`" . $data_source . "_injury_injury_number` tab
INNER JOIN `ikase_" . $data_source . "`.`cse_injury` inj
ON tab.`injury_uuid` = inj.`injury_uuid`
INNER JOIN `ikase_" . $data_source . "`.`cse_case_injury` cci
ON inj.injury_uuid = cci.injury_uuid
INNER JOIN " . $data_source . ".badcases
	ON cci.case_uuid = badcases.case_uuid 
WHERE 1 ";

	//$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	//$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_injury_number` (`injury_number_uuid`, `insurance_policy_number`, `alternate_policy_number`, `carrier_claim_number`, `alternate_claim_number`, `carrier_building_indentifier`, `carrier_building_description`, `customer_id`, `deleted`)
SELECT inumb.`injury_number_uuid`, `insurance_policy_number`, `alternate_policy_number`, `carrier_claim_number`, `alternate_claim_number`, `carrier_building_indentifier`, `carrier_building_description`, inumb.`customer_id`, inumb.`deleted` 
FROM `" . $data_source . "`.`" . $data_source . "_injury_number` inumb
INNER JOIN `ikase_" . $data_source . "`.`cse_injury_injury_number` tab
ON inumb.injury_number_uuid = tab.injury_number_uuid
INNER JOIN `ikase_" . $data_source . "`.`cse_case_injury` cci
ON tab.injury_uuid = cci.injury_uuid
INNER JOIN " . $data_source . ".badcases
	ON cci.case_uuid = badcases.case_uuid 
WHERE 1 ";

	//$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	//$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_notes` (`notes_uuid`, `type`, `subject`, `note`, `title`, `attachments`, `entered_by`, `status`, `dateandtime`, `callback_date`, `verified`, `deleted`, `customer_id`)
SELECT tab.`notes_uuid`, `type`, `subject`, `note`, `title`, `attachments`, `entered_by`, `status`, `dateandtime`, `callback_date`, `verified`, tab.`deleted`, tab.`customer_id` 
FROM `" . $data_source . "`.`" . $data_source . "_notes` tab
INNER JOIN `ikase_" . $data_source . "`.`cse_case_notes` cnote
ON tab.notes_uuid = cnote.notes_uuid
INNER JOIN " . $data_source . ".badcases
	ON cnote.case_uuid = badcases.case_uuid 
WHERE 1 ";
	
	//$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	//$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_person` (`person_uuid`, `parent_person_uuid`, `full_name`, `company_name`, `first_name`, `middle_name`, `last_name`, `aka`, `preferred_name`, `full_address`, `longitude`, `latitude`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `email`, `fax`, `work_phone`, `cell_phone`, `work_email`, `ssn`, `ssn_last_four`, `dob`, `license_number`, `title`, `ref_source`, `salutation`, `age`, `priority_flag`, `gender`, `language`, `birth_state`, `birth_city`, `marital_status`, `legal_status`, `spouse`, `spouse_contact`, `emergency`, `emergency_contact`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
SELECT tab.`person_uuid`, `parent_person_uuid`, `full_name`, `company_name`, `first_name`, `middle_name`, `last_name`, `aka`, `preferred_name`, `full_address`, `longitude`, `latitude`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `email`, `fax`, `work_phone`, `cell_phone`, `work_email`, `ssn`, `ssn_last_four`, `dob`, `license_number`, `title`, `ref_source`, `salutation`, `age`, `priority_flag`, `gender`, `language`, `birth_state`, `birth_city`, `marital_status`, `legal_status`, `spouse`, `spouse_contact`, `emergency`, `emergency_contact`, tab.`last_updated_date`, tab.`last_update_user`, tab.`deleted`, tab.`customer_id` 
FROM `" . $data_source . "`.`" . $data_source . "_person` tab 
INNER JOIN `ikase_" . $data_source . "`.`cse_case_person` cpa
ON tab.person_uuid = cpa.person_uuid
INNER JOIN " . $data_source . ".badcases
	ON cpa.case_uuid = badcases.case_uuid 
WHERE 1 ";

	//$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	//$stmt->execute();
	
	//update cell phones
	$sql = "UPDATE ikase_" . $data_source . ".cse_person pers, 
	(SELECT  sp.person_uuid, sc.cpointer, cli.clientothe
	FROM " . $data_source . "." . $data_source . "_case_person scp
	INNER JOIN " . $data_source . "." . $data_source . "_case sc
	ON scp.case_uuid = sc.case_uuid
	INNER JOIN " . $data_source . ".badcases
	ON sc.case_uuid = badcases.case_uuid 
	INNER JOIN " . $data_source . "." . $data_source . "_person sp
	ON scp.person_uuid = sp.person_uuid AND scp.person_uuid LIKE 'AP%'
	INNER JOIN " . $data_source . ".`client` cli
	ON sc.cpointer = cli.cpointer and othext = 'Cell') cells
	SET cell_phone = cells.clientothe
	WHERE pers.person_uuid = cells.person_uuid
	AND pers.cell_phone = ''";
	//$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	//$stmt->execute();
	
	//update dob
	$sql = "UPDATE ikase_" . $data_source . ".cse_person pers, 
	(SELECT  sp.person_uuid, sc.cpointer, 
	STR_TO_DATE(CONCAT(SUBSTRING(cli.clientdob, 1, 6), '19', SUBSTRING(cli.clientdob, 7, 2)) ,  '%m/%d/%Y' ) fulldob
	FROM " . $data_source . "." . $data_source . "_case_person scp
	INNER JOIN " . $data_source . "." . $data_source . "_case sc
	ON scp.case_uuid = sc.case_uuid
	INNER JOIN " . $data_source . ".badcases
	ON sc.case_uuid = badcases.case_uuid 
	INNER JOIN ikase_" . $data_source . ".cse_person sp
	ON scp.person_uuid = sp.person_uuid AND scp.person_uuid LIKE 'AP%'
	INNER JOIN " . $data_source . ".`client` cli
	ON sc.cpointer = cli.cpointer
    WHERE STR_TO_DATE(CONCAT(SUBSTRING(cli.clientdob, 1, 6), '19', SUBSTRING(cli.clientdob, 7, 2)) ,  '%m/%d/%Y' ) IS NOT NULL
    AND  sp.dob = '1969-12-31'
    ) dobs
	SET `dob` = dobs.fulldob
	WHERE pers.person_uuid = dobs.person_uuid
	AND  pers.dob = '1969-12-31'";
	//$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	//$stmt->execute();
	
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

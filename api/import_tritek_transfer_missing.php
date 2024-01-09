<?php
require_once('../shared/legacy_session.php');

include("connection.php");

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case`
(`case_uuid`,`case_number`, `cpointer`, `case_name`, `source`, `adj_number`, `case_date`, `case_type`, `venue`, `dois`, `case_status`, `case_substatus`, `rating`, `submittedOn`, `attorney`, `worker`, `deleted`, `customer_id`, `medical`, `td`, `rehab`, `edd`)
SELECT `case_uuid`, `case_number`, lc.`cpointer`, `case_name`, 'tritek', `adj_number`, `case_date`, `case_type`, `venue`, `dois`, `case_status`, `case_substatus`, `rating`, `submittedOn`, `attorney`, `worker`, `deleted`, `customer_id`, `medical`, `td`, `rehab`, `edd` 
FROM `" . $data_source . "`.`" . $data_source . "_case` lc
INNER JOIN " . $data_source . ".missings mis
ON lc.cpointer = mis.cpointer
WHERE 1 AND `customer_id` = " . $customer_id;
	//die($sql);
	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_corporation`
	(`case_corporation_uuid`, `case_uuid`, `corporation_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
	SELECT `case_corporation_uuid`, lcc.`case_uuid`, `corporation_uuid`, `attribute`, `last_updated_date`, `last_update_user`, lcc.`deleted`, lcc.`customer_id` 
	FROM `" . $data_source . "`.`" . $data_source . "_case_corporation` lcc
	INNER JOIN `" . $data_source . "`.`" . $data_source . "_case` lc
	ON lcc.case_uuid = lc.case_uuid
	INNER JOIN " . $data_source . ".missings mis
	ON lc.cpointer = mis.cpointer
	WHERE 1 AND lcc.`customer_id` = " . $customer_id;

	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_injury`
(`case_injury_uuid`, `case_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
SELECT `case_injury_uuid`, lcc.`case_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, lcc.`deleted`, lcc.`customer_id` 
FROM `" . $data_source . "`.`" . $data_source . "_case_injury` lcc
INNER JOIN `" . $data_source . "`.`" . $data_source . "_case` lc
ON lcc.case_uuid = lc.case_uuid
INNER JOIN " . $data_source . ".missings mis
ON lc.cpointer = mis.cpointer
WHERE 1 AND lcc.`customer_id` = " . $customer_id;

	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_notes`
(`case_notes_uuid`, `case_uuid`, `notes_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
SELECT `case_notes_uuid`, lcc.`case_uuid`, `notes_uuid`, `attribute`, `last_updated_date`, `last_update_user`, lcc.`deleted`, lcc.`customer_id` 
FROM `" . $data_source . "`.`" . $data_source . "_case_notes` lcc
INNER JOIN `" . $data_source . "`.`" . $data_source . "_case` lc
ON lcc.case_uuid = lc.case_uuid
INNER JOIN " . $data_source . ".missings mis
ON lc.cpointer = mis.cpointer
WHERE 1 AND lcc.`customer_id` = " . $customer_id;

	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_person`
(`case_person_uuid`, `case_uuid`, `person_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
SELECT `case_person_uuid`, lcc.`case_uuid`, `person_uuid`, `attribute`, `last_updated_date`, `last_update_user`, lcc.`deleted`, lcc.`customer_id` 
FROM `" . $data_source . "`.`" . $data_source . "_case_person` lcc
INNER JOIN `" . $data_source . "`.`" . $data_source . "_case` lc
ON lcc.case_uuid = lc.case_uuid
INNER JOIN " . $data_source . ".missings mis
ON lc.cpointer = mis.cpointer
WHERE 1 AND lcc.`customer_id` = " . $customer_id;

	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();


	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_venue`
(`case_venue_uuid`, `case_uuid`, `venue_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
SELECT `case_venue_uuid`, lcc.`case_uuid`, `venue_uuid`, `attribute`, `last_updated_date`, `last_update_user`, lcc.`deleted`, lcc.`customer_id` 
FROM `" . $data_source . "`.`" . $data_source . "_case_venue` lcc
INNER JOIN `" . $data_source . "`.`" . $data_source . "_case` lc
ON lcc.case_uuid = lc.case_uuid
INNER JOIN " . $data_source . ".missings mis
ON lc.cpointer = mis.cpointer
WHERE 1 AND lcc.`customer_id` = " . $customer_id;

	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_corporation` (`corporation_uuid`, `parent_corporation_uuid`, `full_name`, `company_name`, `type`, `first_name`, `last_name`, `aka`, `preferred_name`, `employee_phone`, `employee_fax`, `employee_email`, `full_address`, `longitude`, `latitude`, `street`, `city`, `state`, `zip`, `suite`, `company_site`, `phone`, `email`, `fax`, `ssn`, `dob`, `salutation`, `last_updated_date`, `last_update_user`, `copying_instructions`, `deleted`, `customer_id`)
SELECT corp.`corporation_uuid`, `parent_corporation_uuid`, `full_name`, `company_name`, `type`, `first_name`, `last_name`, `aka`, `preferred_name`, `employee_phone`, `employee_fax`, `employee_email`, `full_address`, `longitude`, `latitude`, `street`, `city`, `state`, `zip`, `suite`, `company_site`, `phone`, `email`, `fax`, `ssn`, `dob`, `salutation`, corp.`last_updated_date`, corp.`last_update_user`, '', corp.`deleted`, corp.`customer_id` 
FROM `" . $data_source . "`.`" . $data_source . "_corporation` corp
INNER JOIN `" . $data_source . "`.`" . $data_source . "_case_corporation` lcc
ON corp.corporation_uuid = lcc.corporation_uuid
INNER JOIN `" . $data_source . "`.`" . $data_source . "_case` lc
ON lcc.case_uuid = lc.case_uuid
INNER JOIN " . $data_source . ".missings mis
ON lc.cpointer = mis.cpointer
WHERE 1 AND lcc.`customer_id` = " . $customer_id;

	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_corporation_adhoc` (`adhoc_uuid`, `case_uuid`, `corporation_uuid`, `adhoc`, `adhoc_value`, `customer_id`, `deleted`)
SELECT `adhoc_uuid`, lcc.`case_uuid`, lcc.`corporation_uuid`, `adhoc`, `adhoc_value`, lcc.`customer_id`, lcc.`deleted` 
FROM `" . $data_source . "`.`" . $data_source . "_corporation_adhoc` cad
INNER JOIN `" . $data_source . "`.`" . $data_source . "_case_corporation` lcc
ON cad.corporation_uuid = lcc.corporation_uuid
INNER JOIN `" . $data_source . "`.`" . $data_source . "_case` lc
ON lcc.case_uuid = lc.case_uuid
INNER JOIN " . $data_source . ".missings mis
ON lc.cpointer = mis.cpointer
WHERE 1 AND lcc.`customer_id` = " . $customer_id;
	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_exam`
	(`exam_uuid`, `exam_dateandtime`, `exam_status`, `exam_type`, `specialty`, `requestor`, `comments`, `permanent_stationary`, `fs_date`, `customer_id`, `deleted`)
	SELECT cad.`exam_uuid`, `exam_dateandtime`, `exam_status`, `exam_type`, `specialty`, `requestor`, `comments`, `permanent_stationary`, `fs_date`, cad.`customer_id`, cad.`deleted`
	FROM `" . $data_source . "`.`" . $data_source . "_exam` cad
INNER JOIN `" . $data_source . "`.`" . $data_source . "_corporation_exam` cex
ON cad.exam_uuid = cex.exam_uuid
INNER JOIN `" . $data_source . "`.`" . $data_source . "_case_corporation` lcc
ON cex.corporation_uuid = lcc.corporation_uuid
INNER JOIN `" . $data_source . "`.`" . $data_source . "_case` lc
ON lcc.case_uuid = lc.case_uuid
INNER JOIN " . $data_source . ".missings mis
ON lc.cpointer = mis.cpointer
WHERE 1 AND lcc.`customer_id` = " . $customer_id;
	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_corporation_exam`
	(`corporation_exam_uuid`, `corporation_uuid`, `exam_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
	SELECT `corporation_exam_uuid`, cex.`corporation_uuid`, cex.`exam_uuid`, cex.`attribute`, cex.`last_updated_date`, cex.`last_update_user`, cex.`deleted`, cex.`customer_id`
	FROM `" . $data_source . "`.`" . $data_source . "_corporation_exam` cex
	INNER JOIN `" . $data_source . "`.`" . $data_source . "_case_corporation` lcc
	ON cex.corporation_uuid = lcc.corporation_uuid
	INNER JOIN `" . $data_source . "`.`" . $data_source . "_case` lc
	ON lcc.case_uuid = lc.case_uuid
	INNER JOIN " . $data_source . ".missings mis
	ON lc.cpointer = mis.cpointer
	WHERE 1 AND lcc.`customer_id` = " . $customer_id;

	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_injury` (`injury_uuid`, `injury_number`, `adj_number`, `type`, 
	`occupation`, `start_date`, `end_date`, `ct_dates_note`, `body_parts`, `statute_limitation`, `explanation`, 
	`full_address`, `street`, `city`, `state`, `zip`, `suite`, `customer_id`, `deleted`)
	SELECT inj.`injury_uuid`, `injury_number`, inj.`adj_number`, `type`, `occupation`, `start_date`, `end_date`, `ctdates`, 
	`body_parts`, `statute_limitation`, `explanation`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, 
	inj.`customer_id`, inj.`deleted` 
	FROM `" . $data_source . "`.`" . $data_source . "_injury` inj
	INNER JOIN `" . $data_source . "`.`" . $data_source . "_case_injury` lcc
	ON inj.injury_uuid = lcc.injury_uuid
	INNER JOIN `" . $data_source . "`.`" . $data_source . "_case` lc
	ON lcc.case_uuid = lc.case_uuid
	INNER JOIN " . $data_source . ".missings mis
	ON lc.cpointer = mis.cpointer
	WHERE 1 AND lcc.`customer_id` = " . $customer_id;

	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_injury_injury_number`
	(`injury_injury_number_uuid`, `injury_uuid`, `injury_number_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
	SELECT `injury_injury_number_uuid`, iin.`injury_uuid`, `injury_number_uuid`, iin.`attribute`, iin.`last_updated_date`, iin.`last_update_user`, iin.`deleted`, iin.`customer_id` 
	FROM `" . $data_source . "`.`" . $data_source . "_injury_injury_number` iin
	INNER JOIN `" . $data_source . "`.`" . $data_source . "_case_injury` lcc
	ON iin.injury_uuid = lcc.injury_uuid
	INNER JOIN `" . $data_source . "`.`" . $data_source . "_case` lc
	ON lcc.case_uuid = lc.case_uuid
	INNER JOIN " . $data_source . ".missings mis
	ON lc.cpointer = mis.cpointer
	WHERE 1 AND lcc.`customer_id` = " . $customer_id;

	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_injury_number` (`injury_number_uuid`, `insurance_policy_number`, `alternate_policy_number`, `carrier_claim_number`, `alternate_claim_number`, `carrier_building_indentifier`, `carrier_building_description`, `customer_id`, `deleted`)
	SELECT tin.`injury_number_uuid`, `insurance_policy_number`, `alternate_policy_number`, `carrier_claim_number`, 
	`alternate_claim_number`, `carrier_building_indentifier`, `carrier_building_description`, tin.`customer_id`, tin.`deleted` 
	FROM `" . $data_source . "`.`" . $data_source . "_injury_number` tin
	INNER JOIN `" . $data_source . "`.`" . $data_source . "_injury_injury_number` iin
	ON tin.`injury_number_uuid` = iin.`injury_number_uuid`
	INNER JOIN `" . $data_source . "`.`" . $data_source . "_case_injury` lcc
	ON iin.injury_uuid = lcc.injury_uuid
	INNER JOIN `" . $data_source . "`.`" . $data_source . "_case` lc
	ON lcc.case_uuid = lc.case_uuid
	INNER JOIN " . $data_source . ".missings mis
	ON lc.cpointer = mis.cpointer
	WHERE 1 AND lcc.`customer_id` = " . $customer_id;

	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_notes` (`notes_uuid`, `type`, `subject`, `note`, `title`, `attachments`, `entered_by`, `status`, `dateandtime`, `callback_date`, `verified`, `deleted`, `customer_id`)
SELECT notes.`notes_uuid`, `type`, `subject`, `note`, `title`, `attachments`, `entered_by`, `status`, `dateandtime`, `callback_date`, `verified`, notes.`deleted`, notes.`customer_id` 
FROM `" . $data_source . "`.`" . $data_source . "_notes` notes
INNER JOIN `" . $data_source . "`.`" . $data_source . "_case_notes` lcc
ON notes.notes_uuid = lcc.notes_uuid
INNER JOIN `" . $data_source . "`.`" . $data_source . "_case` lc
ON lcc.case_uuid = lc.case_uuid
INNER JOIN " . $data_source . ".missings mis
ON lc.cpointer = mis.cpointer
WHERE 1 AND lcc.`customer_id` = " . $customer_id;
	
	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_person` (`person_uuid`, `parent_person_uuid`, `full_name`, `company_name`, `first_name`, `middle_name`, `last_name`, `aka`, `preferred_name`, `full_address`, `longitude`, `latitude`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `email`, `fax`, `work_phone`, `cell_phone`, `work_email`, `ssn`, `ssn_last_four`, `dob`, `license_number`, `title`, `ref_source`, `salutation`, `age`, `priority_flag`, `gender`, `language`, `birth_state`, `birth_city`, `marital_status`, `legal_status`, `spouse`, `spouse_contact`, `emergency`, `emergency_contact`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
SELECT pers.`person_uuid`, `parent_person_uuid`, `full_name`, `company_name`, `first_name`, `middle_name`, `last_name`, `aka`, `preferred_name`, `full_address`, `longitude`, `latitude`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `email`, `fax`, `work_phone`, `cell_phone`, `work_email`, `ssn`, `ssn_last_four`, `dob`, `license_number`, `title`, `ref_source`, `salutation`, `age`, `priority_flag`, `gender`, `language`, `birth_state`, `birth_city`, `marital_status`, `legal_status`, `spouse`, `spouse_contact`, `emergency`, `emergency_contact`, 
pers.`last_updated_date`, pers.`last_update_user`, pers.`deleted`, pers.`customer_id` FROM `" . $data_source . "`.`" . $data_source . "_person`  pers
INNER JOIN `" . $data_source . "`.`" . $data_source . "_case_person` lcc
ON pers.person_uuid = lcc.person_uuid
INNER JOIN `" . $data_source . "`.`" . $data_source . "_case` lc
ON lcc.case_uuid = lc.case_uuid
INNER JOIN " . $data_source . ".missings mis
ON lc.cpointer = mis.cpointer
WHERE 1 AND lcc.`customer_id` = " . $customer_id;

	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();
	
	//update cell phones
	$sql = "UPDATE ikase_" . $data_source . ".cse_person pers, 
	(SELECT  sp.person_uuid, sc.cpointer, cli.clientothe
	FROM " . $data_source . "." . $data_source . "_case_person scp
	INNER JOIN " . $data_source . "." . $data_source . "_case sc
	ON scp.case_uuid = sc.case_uuid
	INNER JOIN " . $data_source . ".missings mis
	ON sc.cpointer = mis.cpointer
	INNER JOIN " . $data_source . "." . $data_source . "_person sp
	ON scp.person_uuid = sp.person_uuid AND scp.person_uuid LIKE 'AP%'
	INNER JOIN " . $data_source . ".`client` cli
	ON sc.cpointer = cli.cpointer and othext = 'Cell') cells
	SET cell_phone = cells.clientothe
	WHERE pers.person_uuid = cells.person_uuid
	AND pers.cell_phone = ''";
	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();
	
	//update dob
	$sql = "UPDATE ikase_" . $data_source . ".cse_person pers, 
	(SELECT  sp.person_uuid, sc.cpointer, 
	STR_TO_DATE(CONCAT(SUBSTRING(cli.clientdob, 1, 6), '19', SUBSTRING(cli.clientdob, 7, 2)) ,  '%m/%d/%Y' ) fulldob
	FROM " . $data_source . "." . $data_source . "_case_person scp
	INNER JOIN " . $data_source . "." . $data_source . "_case sc
	ON scp.case_uuid = sc.case_uuid
	INNER JOIN " . $data_source . ".missings mis
	ON sc.cpointer = mis.cpointer
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
	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();
	
	$success = array("success"=> array("text"=>"done"));
	echo json_encode($success);
} catch(PDOException $e) {
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

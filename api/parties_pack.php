<?php
$app->group('', function (\Slim\Routing\RouteCollectorProxy $app) {
	$app->get('/parties/{id}', 'getKaseParties');
	$app->get('/offices/{id}', 'getKaseOffices');
	$app->get('/partielist/{id}/{type}', 'getKasePartiesByType');
	$app->get('/rolodex', 'getRolodex');
	$app->get('/rolodex/search/{search_term}', 'searchRolodex');
	$app->get('/dashboard/{id}/{title}', 'getKaseParties');
	//$app->get('/parties/{id}', 'getPartie');
	$app->get('/parties/type/', 'typeParties');

	//posts
	$app->post('/bing/search', 'getBingLocation');
	$app->post('/rolodex/relate', 'relateRolodex');
	//$app->post('/parties/add', 'addForm');
	//$app->post('/parties/update', 'updateForm');
})->add(\Api\Middleware\Authorize::class);

function getKasePartiesByTypeInfo($case_id, $type) {
	return getKaseParties($case_id, "", true, $type);
}
function getKasePartiesByType($case_id, $type) {
	getKaseParties($case_id, "", false, $type);
}
function getKaseOffices($case_id) {
	getKaseParties($case_id, "", false, "offices");
}
function getKaseParties($case_id, $title = "", $blnReturn = false, $specific = "") {
	session_write_close();
	
	//we need some info first
	$kase = getKaseInfo($case_id);
	$case_type = $kase->case_type; 
	$case_id = $kase->id;
	$blnWCAB = checkWCAB($case_type);
	
	$sql = "SELECT DISTINCT -1 person_id, corp.`corporation_id`, corp.`corporation_uuid` uuid, corp.`parent_corporation_uuid` parent_uuid, corp.`type`, corp.additional_addresses,
	IFNULL(dash.setting_value, 'N') show_dashboard,  
	IFNULL(corp.`party_type_option`, 'plaintiff') `party_type_option`, 
	IFNULL(corp.`party_defendant_option`, '') `party_defendant_option`, 
	`full_name`, IF(`company_name`='', `full_name`, `company_name`) `company_name`, `first_name`, `last_name`, `aka`, `preferred_name`, corp.`full_address`, `longitude`, `latitude`, corp.`street`, corp.`city`, corp.`state`, corp.`zip`, corp.`suite`, `phone`, '' `cell_phone`, `email`, `fax`, `ssn`, `dob`, '' `language`, `salutation`, 
	corp.employee_email, corp.employee_phone, corp.employee_cell, corp.employee_fax, 
	corp.`last_updated_date`, corp.`last_update_user`, corp.`deleted`, corp.`customer_id`, IFNULL(cpt.partie_type, corp.`type`) partie_type, cpt.employee_title, cpt.color, cpt.blurb, cpt.show_employee, `company_site`, `sort_order`, cse.case_status, cse.case_substatus, cse.file_location, cse.attorney, cse.worker, cse.rating,
	ccad.adhoc_value `claim`, cdoc.adhoc_value `doctor_type`, 
	IFNULL(`ndoc`.`adhoc_value`, '') `claim_number`, IFNULL(`asscad`.`adhoc_value`, '') `assigned_to`,
	IFNULL(inj.injury_id, '') `injury_id`, IFNULL(inj.start_date, '') `start_date`, IFNULL(inj.end_date, '') `end_date`,
	IFNULL(`mpncad`.`adhoc_value`, '') `mpn`, IFNULL(`speccad`.`adhoc_value`, '') `specialty`, 
	IFNULL(`ratcad`.`adhoc_value`, '') `rating`,
	IFNULL(`namecad`.`adhoc_value`, '') `letter_name`,
	IFNULL(cpc.attribute_2, '') medical_prior      
	FROM `cse_corporation` corp 
	LEFT OUTER JOIN `cse_partie_type` cpt
	ON corp.type = cpt.blurb
	INNER JOIN `cse_case_corporation` ccorp
	ON (corp.corporation_uuid = ccorp.corporation_uuid AND ccorp.`deleted` =  'N' AND ccorp.attribute != 'recipient')
	INNER JOIN `cse_case` cse
	ON ccorp.case_uuid = cse.case_uuid
	
	LEFT OUTER JOIN cse_setting dash
	ON corp.type = dash.setting AND dash.category = 'dashboard' AND dash.customer_id = " . $_SESSION['user_customer_id'] . "
	
	LEFT OUTER JOIN `cse_corporation_adhoc` mpncad
    ON (corp.corporation_uuid = mpncad.corporation_uuid AND mpncad.`deleted` =  'N' AND mpncad.adhoc = 'mpn')
	
	LEFT OUTER JOIN `cse_corporation_adhoc` speccad
    ON (corp.corporation_uuid = speccad.corporation_uuid AND speccad.`deleted` =  'N' AND speccad.adhoc = 'specialty')
	
	LEFT OUTER JOIN `cse_corporation_adhoc` ratcad
    ON (corp.corporation_uuid = ratcad.corporation_uuid AND ratcad.`deleted` =  'N' AND ratcad.adhoc = 'rating')
	
	LEFT OUTER JOIN `cse_corporation_adhoc` namecad
    ON (corp.corporation_uuid = namecad.corporation_uuid AND namecad.`deleted` =  'N' AND namecad.adhoc = 'letter_name')
	
	LEFT OUTER JOIN `cse_corporation_adhoc` ccad
    ON (corp.corporation_uuid = ccad.corporation_uuid AND ccad.`deleted` =  'N' AND ccad.adhoc = 'claims')
	LEFT OUTER JOIN `cse_corporation_adhoc` asscad
    ON (corp.corporation_uuid = asscad.corporation_uuid AND asscad.`deleted` =  'N' AND asscad.adhoc = 'assigned_to')
    LEFT OUTER JOIN `cse_corporation_adhoc` cdoc
    ON (corp.corporation_uuid = cdoc.corporation_uuid AND cdoc.`deleted` =  'N' AND cdoc.adhoc = 'doctor_type'
	";
	
	$sql .= ") LEFT OUTER JOIN `cse_corporation_adhoc` ndoc
    ON (corp.corporation_uuid = ndoc.corporation_uuid AND ndoc.`deleted` =  'N' AND ndoc.adhoc = 'claim_number')
	LEFT OUTER JOIN `cse_person_corporation` cpc
	ON corp.corporation_uuid = cpc.corporation_uuid AND cpc.attribute_1 = 'medical_provider'
	
	LEFT OUTER JOIN `cse_injury` inj
	ON ccorp.injury_uuid = inj.injury_uuid
	WHERE corp.deleted = 'N'
	AND corp.customer_id = " . $_SESSION['user_customer_id'] . "
	AND cse.case_id =  :case_id";
	
	if ($specific=="offices") {
		$sql .= " 
		AND	cpt.sort_order > 99";
		$specific = "";
	}
	
	if ($title=="dashboard") {
		//nothing past Venue
		$sql .= " 
			AND ( 
				CONCAT(corp.`type`, IFNULL(cdoc.adhoc_value, '')) = 'medical_providerPTP'
				OR CONCAT(corp.`type`, IFNULL(cdoc.adhoc_value, '')) = 'medical_providersecondary physician'
				OR CONCAT(corp.`type`, IFNULL(cdoc.adhoc_value, '')) NOT LIKE 'medical_provider%'
			)
			OR IFNULL(dash.setting_value, 'N') = 'Y'";

		/*
		 Imported A1 Gaylord Customer, In that to by pass parties page redirection in case level dashboard and should work redirection in new case (that's why added case id condition to check old case or new) we added this query part in if condition 
		*/
		if (!($_SESSION['user_customer_id'] == 1308 && strtolower($case_type) == 'social_security' && $case_id < 20642)) {
			$sql .= "
				AND cpt.sort_order <= 10 
				AND cpt.sort_order IS NOT NULL
			";
		}

		if ($blnWCAB) {
			$sql .= "
				AND cpt.blurb != 'plaintiff'
			";
		}
	}
	if ($specific!="") {
		$sql .= " 
		AND corp.type = :specific";
	}
	
	$blnUnion = false;
	//$_SESSION["user_customer_id"]=="1070" || $_SESSION["user_customer_id"]=="1121"
	if ($specific=="" && ($blnWCAB || $_SESSION['user_data_path']=="tritek")) {
		$blnUnion = true;
		//looks like tritek has persons associated with all cases
		$sql .= " UNION 
		SELECT DISTINCT pers.person_id, -1 `corporation_id`, pers.`person_uuid` uuid, pers.`parent_person_uuid` parent_uuid, 'applicant' `type`, '' `additional_addresses`, 
		'Y' show_dashboard, 
		'xplaintiff' `party_type_option`, '' `party_defendant_option`, `full_name`, `company_name`, `first_name`, `last_name`, `aka`, `preferred_name`, pers.`full_address`, `longitude`, `latitude`, pers.`street`, pers.`city`, pers.`state`, pers.`zip`, pers.`suite`, `phone`, `cell_phone`, `email`, `fax`, `ssn`, `dob`, `language`, `salutation`, 
		'' employee_email, '' employee_phone, '' employee_cell, '' employee_fax, 
		pers.`last_updated_date`, pers.`last_update_user`, pers.`deleted`, pers.`customer_id`, 'Applicant' `partie_type`, '' `employee_title`, '_card_fade_4' `color`, '' `blurb`, '' `show_employee`, '' `company_site`, 0 sort_order, cse.case_status, cse.case_substatus, cse.file_location, cse.attorney, cse.worker, cse.rating, '' `claim`, '' `doctor_type`, '' `claim_number`, '' `assigned_to`,
		'' `injury_id`, '' `start_date`, '' `end_date`, '' `mpn`, '' `specialty`, '' `rating`, '' `letter_name` , '' `medical_prior`  
		FROM ";
		//$sql .= "`cse_person`";
		
		if (($_SESSION['user_customer_id']==1033)) {
			$sql_encrypt = SQL_PERSONX;
			$sql_encrypt = str_replace("SET utf8)", "SET utf8) COLLATE utf8_general_ci", $sql_encrypt);
			$sql .= "(" . $sql_encrypt . ")";
		} else {
			$sql .= "`cse_person`";
		}
		
		$sql .= " pers INNER JOIN `cse_case_person` cper
		ON pers.person_uuid = cper.person_uuid AND cper.deleted = 'N'
		INNER JOIN `cse_case` cse
		ON cper.case_uuid = cse.case_uuid
		WHERE pers.deleted = 'N'
		AND pers.customer_id = " . $_SESSION['user_customer_id'] . "
		AND cse.case_id =  :case_id";
	}

	if ((!$blnWCAB && $_SESSION["user_customer_id"]!="1070") && !$_SESSION["user_customer_id"]!="1121") {
		$sql .= " ORDER BY IFNULL(`party_type_option`, 'plaintiff') DESC, `sort_order`, `type` ASC";
		//die($sql);
	} else {
		$type_prefix = "";
		if (!$blnUnion) {
			$type_prefix = "corp.";
		}
		$sql .= " 
		ORDER BY IF(" . $type_prefix . "`type`='employer', 'applicant1', " . $type_prefix . "`type`) ASC, 
        IF (IF (IF (IF( `doctor_type` = 'PTP', 0, `doctor_type`) = 'AME', 1, IF( `doctor_type` = 'PTP', 0, `doctor_type`)) = 'PQME', 2, IF (IF( `doctor_type` = 'PTP', 0, `doctor_type`) = 'AME', 1, IF( `doctor_type` = 'PTP', 0, `doctor_type`))) IS NULL, 'ZZZ', IF (IF (IF( `doctor_type` = 'PTP', 0, `doctor_type`) = 'AME', 1, IF( `doctor_type` = 'PTP', 0, `doctor_type`)) = 'PQME', 2, IF (IF( `doctor_type` = 'PTP', 0, `doctor_type`) = 'AME', 1, IF( `doctor_type` = 'PTP', 0, `doctor_type`))))";
	}
	
	if ($_SERVER['REMOTE_ADDR']=='47.153.49.248') {
		//die($sql);
	}
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		if ($specific!="" ) {
			$stmt->bindParam("specific", $specific);
		}
		$stmt->execute();
		$parties = $stmt->fetchAll(PDO::FETCH_OBJ);

		if (!$blnReturn) {
        	echo json_encode($parties);
		} else {
			return $parties;
		}
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage(), "sql"=>$sql));
        	echo json_encode($error);
	}
	exit();
}
function getKasePartiesInfo($case_id, $type = "") {
	session_write_close();
	$sql = "SELECT  -1 person_id, corp.`corporation_id`, corp.`corporation_uuid` uuid, `type`, `full_name`, `company_name`, `first_name`, `last_name`, `aka`, `preferred_name`, `full_address`, `longitude`, `latitude`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `email`, `fax`, `ssn`, `dob`, '' `language`, `salutation`, corp.employee_phone, corp.employee_cell, corp.employee_fax, corp.`last_updated_date`, corp.`last_update_user`, corp.`deleted`, corp.`customer_id`, cpt.partie_type, cpt.employee_title, cpt.color, cpt.blurb, cpt.show_employee, `company_site`, `sort_order`, cse.case_status, cse.case_substatus, cse.file_location, cse.attorney, cse.worker, cse.rating     
	FROM `cse_corporation` corp 
	INNER JOIN `cse_partie_type` cpt
	ON corp.type = cpt.blurb
	INNER JOIN `cse_case_corporation` ccorp
	ON (corp.corporation_uuid = ccorp.corporation_uuid AND ccorp.`deleted` =  'N')
	INNER JOIN `cse_case` cse
	ON ccorp.case_uuid = cse.case_uuid
	WHERE corp.deleted = 'N'";
	if ($type!="") {
		$sql .= " AND corp.type = '" . $type . "'";
	}
	$sql .= " AND corp.customer_id = " . $_SESSION['user_customer_id'] . "
	AND cse.case_id =  :case_id";
		//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->execute();
		$parties = $stmt->fetchAll(PDO::FETCH_OBJ);

        return $parties;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	exit();
}
function searchRolodex($search_term) {
	session_write_close();
	
	$search_term = clean_html($search_term);
	$search_term = str_replace("_", " ", $search_term);
	
	if (strlen($search_term) == 2) {
		return false;
	}
	if ($_SESSION['user_customer_id']=="") {
		return false;
	}
	if (!is_numeric($_SESSION['user_customer_id'])) {
		return false;
	}
	
	$sql = "SELECT  -1 person_id, corp.`corporation_id`, corp.`corporation_uuid` uuid, `type`, `full_name`, `company_name`, `first_name`, `last_name`, UPPER(SUBSTRING(`company_name`, 1, 1)) as first_letter, `aka`, `preferred_name`, `full_address`, `longitude`, `latitude`, `street`, `city`, `state`, `zip`, `suite`, `phone`, '' `cell_phone`, `email`, `fax`, `ssn`, `dob`, '' `language`, `salutation`, corp.employee_phone, corp.employee_cell, corp.employee_fax, corp.`last_updated_date`, corp.`last_update_user`, corp.`deleted`, corp.`customer_id`, cpt.partie_type, cpt.employee_title, cpt.color, cpt.blurb, cpt.show_employee, `company_site`, `sort_order`, `company_name` AS display_name, corp.type AS rolo_partie, 
	IF(crr.rolodex_relations_id IS NULL, 'N', 'Y') related
	FROM `cse_corporation` corp
	
	LEFT OUTER JOIN cse_rolodex_relations crr
	ON corp.corporation_uuid = crr.rolodex_uuid
	
	INNER JOIN 
		((SELECT 
            MIN(corporation_id) corporation_id
        FROM
            cse_corporation
        WHERE
            corporation_uuid = parent_corporation_uuid
			AND `customer_id` = " . $_SESSION['user_customer_id'] . "
		GROUP BY `type` , company_name , full_address , employee_phone)) min_ids
	ON corp.corporation_id = min_ids.corporation_id
	
	INNER JOIN `cse_partie_type` cpt
	ON corp.type = cpt.blurb
	WHERE corp.deleted = 'N'
	AND corp.corporation_uuid = corp.parent_corporation_uuid
	AND company_name != ''
	AND (
			first_name LIKE '%" . $search_term . "%'
			OR last_name LIKE '%" . $search_term . "%'
			OR aka = '" . $search_term . "'
			OR company_name LIKE '%" . $search_term . "%'
			OR full_name LIKE '%" . $search_term . "%'
			OR full_address LIKE '%" . $search_term . "%'
			OR city LIKE '%" . $search_term . "%'
			OR `suite` LIKE '%" . $search_term . "%'
			OR `phone` LIKE '%" . $search_term . "%'
			OR `email` LIKE '%" . $search_term . "%'
			OR `fax` LIKE '%" . $search_term . "%'
			OR `ssn` LIKE '%" . $search_term . "%'
			OR `dob` = '" . $search_term . "'
			OR employee_phone LIKE '%" . $search_term . "%'
			OR employee_cell LIKE '%" . $search_term . "%'
			OR employee_fax LIKE '%" . $search_term . "%'
	)
	AND corp.customer_id = " . $_SESSION['user_customer_id'];
	
	//if ($_SERVER['REMOTE_ADDR']=='173.55.229.70' && $_SESSION['user_customer_id']==1033) { 
		//eams rep
		$sql .= " UNION 
		SELECT  -1 person_id,
 corp.`rep_id`,
 corp.`rep_uuid` uuid,
 'rep',
 '' `full_name`,
 `firm_name` `company_name`,
 '' `first_name`,
 '' `last_name`,
 UPPER(SUBSTRING(`firm_name`, 1, 1)) as first_letter,
 `eams_ref_number` `aka`,
 ''	`preferred_name`,
 CONCAT(`street_1`, ' ', `street_2`, ', ', `city`, ', ', `state`, ' ', `zip_code`) `full_address`,
 '0' longitude,
 '0' latitude,
 `street_1` `street`,
 `city`,
`state`,
 `zip_code`,
 `street_2` `suite`,
 `phone`,
 '' `cell_phone`,
 '' `email`,
 '' `fax`,
 '' `ssn`,
 '' `dob`,
 '' `language`,
 '' `salutation`,
 '' `employee_phone`,
 '' `employee_cell`,
 '' `employee_fax`,
 '' `last_updated_date`, 
 '' `last_update_user`,
 'N' `deleted`,
 '" . $_SESSION['user_customer_id'] . "' `customer_id`,
 'eams_rep' `partie_type`,
 '' `employee_title`,
 '' `blurb`,
 'eams_rep',
 'N' `show_employee`,
 '' `company_site`,
 200 `sort_order`,
 `firm_name` AS display_name,
 'rep' AS rolo_partie, 'N' related
		FROM `ikase`.`cse_eams_reps` corp 
		WHERE 1 
		AND (
				`firm_name` LIKE '%" . $search_term . "%'
				OR `eams_ref_number` = '" . $search_term . "'
				OR `street_1` LIKE '%" . $search_term . "%'
				OR `city` LIKE '%" . $search_term . "%'
				OR `street_2` LIKE '%" . $search_term . "%'
				OR `phone` LIKE '%" . $search_term . "%'
				OR `zip_code` LIKE '%" . $search_term . "%'
		)
		";
		
		$sql .= " UNION 
		SELECT  -1 person_id,
 corp.`carrier_id`,
 corp.`carrier_uuid` uuid,
 'carrier',
 '' `full_name`,
 `firm_name` `company_name`,
 '' `first_name`,
 '' `last_name`,
 UPPER(SUBSTRING(`firm_name`, 1, 1)) as first_letter,
 `eams_ref_number` `aka`,
 ''	`preferred_name`,
 CONCAT(`street_1`, ' ', `street_2`, ', ', `city`, ', ', `state`, ' ', `zip_code`) `full_address`,
 '0' longitude,
 '0' latitude,
 `street_1` `street`,
 `city`,
`state`,
 `zip_code`,
 `street_2` `suite`,
 `phone`,
 '' `cell_phone`,
  '' `email`,
 '' `fax`,
 '' `ssn`,
 '' `dob`,
 '' `language`,
 '' `salutation`,
 '' `employee_phone`,
 '' `employee_cell`, 
 '' `employee_fax`,
 '' `last_updated_date`, 
 '' `last_update_user`,
 'N' `deleted`,
 '" . $_SESSION['user_customer_id'] . "' `customer_id`,
 'eams_carrier' `partie_type`,
 '' `employee_title`,
 '' `blurb`,
 'eams_carrier',
 'N' `show_employee`,
 '' `company_site`,
 200 `sort_order`,
 `firm_name` AS display_name,
 'carrier' AS rolo_partie, 'N' related
		FROM `ikase`.`cse_eams_carriers` corp 
		WHERE 1 
		AND (
				`firm_name` LIKE '%" . $search_term . "%'
				OR `eams_ref_number` = '" . $search_term . "'
				OR `street_1` LIKE '%" . $search_term . "%'
				OR `city` LIKE '%" . $search_term . "%'
				OR `street_2` LIKE '%" . $search_term . "%'
				OR `phone` LIKE '%" . $search_term . "%'
				OR `zip_code` LIKE '%" . $search_term . "%'
		)
		";
	//}
	
	//if ($_SERVER['REMOTE_ADDR']=='173.55.229.70' && $_SESSION['user_customer_id']==1033) { 
		//venue
		$sql .= " UNION 
		SELECT  -1 person_id,
 corp.`venue_id`,
 corp.`venue_uuid` uuid,
 'venue',
 `presiding` `full_name`,
 `venue` `company_name`,
 '' `first_name`,
 '' `last_name`,
 UPPER(SUBSTRING(`venue`, 1, 1)) as first_letter,
 `venue_abbr` `aka`,
 ''	`preferred_name`,
 CONCAT(`address1`, ' ', `address2`, ', ', `city`, ', ', 'CA', ' ', `zip`) `full_address`,
 '0' longitude,
 '0' latitude,
 `address1` `street`,
 `city`,
 'CA' `state`,
 `zip`,
 `address2` `suite`,
 `phone`,
 '' `cell_phone`,
  '' `email`,
 '' `fax`,
 '' `ssn`,
 '' `dob`,
 '' `language`,
 'Your Honor' `salutation`,
 '' `employee_phone`,
 '' `employee_cell`,
 '' `employee_fax`,
 '' `last_updated_date`, 
 '' `last_update_user`,
 'N' `deleted`,
 '" . $_SESSION['user_customer_id'] . "' `customer_id`,
 'eams_venue' `partie_type`,
 'Judge' `employee_title`,
 '' `blurb`,
 'eams_venue',
 'N' `show_employee`,
 '' `company_site`,
 100 `sort_order`,
 `venue` AS display_name,
 'venue' AS rolo_partie, 'N' related
		FROM `ikase`.`cse_venue` corp 
		WHERE 1 
		AND (
				`venue` LIKE '%" . $search_term . "%'
				OR `venue_abbr` = '" . $search_term . "'
				OR `presiding` LIKE '%" . $search_term . "%'
				OR `address1` LIKE '%" . $search_term . "%'
				OR `city` LIKE '%" . $search_term . "%'
				OR `address2` LIKE '%" . $search_term . "%'
				OR `zip` LIKE '%" . $search_term . "%'
				OR `phone` LIKE '%" . $search_term . "%'
		)
		";
	//}
	$sql .= " UNION 
	SELECT  pers.person_id,
 -1 `corporation_id`,
 pers.`person_uuid` uuid,
 'applicant' `type`,
 `full_name`,
 `company_name`,
 `first_name`,
 `last_name`,
 UPPER(SUBSTRING(`last_name`,
 1,
 1)) as first_letter, `aka`, `preferred_name`, `full_address`, `longitude`, `latitude`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `cell_phone`, `email`, `fax`, `ssn`, `dob`, `language`, `salutation`, '' employee_phone, '' employee_cell, '' employee_fax, pers.`last_updated_date`, pers.`last_update_user`, pers.`deleted`, pers.`customer_id`, 'Applicant' `partie_type`, '' `employee_title`, '_card_fade_4' `color`, '' `blurb`, '' `show_employee`, '' `company_site`, 0 sort_order, CONCAT(`last_name`, ', ', `first_name`) AS display_name, '' rolo_partie, 
	IF(crr.rolodex_relations_id IS NULL, 'N', 'Y') related 
	FROM `cse_person` pers 

	LEFT OUTER JOIN cse_rolodex_relations crr
	ON pers.person_uuid = crr.rolodex_uuid
	
	WHERE pers.deleted = 'N'
	AND pers.customer_id = " . $_SESSION['user_customer_id'] . "
	AND pers.person_uuid = pers.parent_person_uuid
	AND first_name != ''
	AND last_name != ''
	AND (
			first_name LIKE '%" . $search_term . "%'
			OR last_name LIKE '%" . $search_term . "%'
			OR company_name LIKE '%" . $search_term . "%'
			OR aka = '" . $search_term . "'
			OR full_name LIKE '%" . $search_term . "%'
			OR full_address LIKE '%" . $search_term . "%'
			OR city LIKE '%" . $search_term . "%'
			OR `suite` LIKE '%" . $search_term . "%'
			OR `phone` LIKE '%" . $search_term . "%'
			OR `cell_phone` LIKE '%" . $search_term . "%'
			OR `email` LIKE '%" . $search_term . "%'
			OR `fax` LIKE '%" . $search_term . "%'
			OR `ssn` LIKE '%" . $search_term . "%'
			OR `dob` = '" . $search_term . "'
	)";
	$sql .= " 
	ORDER BY `display_name`";
	
	if (strlen($search_term) == 1) {
		$sql = str_replace("LIKE '%", "LIKE '", $sql);
	}
	if ($_SERVER['REMOTE_ADDR']=='47.153.49.248') { 
		//die($sql);
	}
	try {
		$contacts = DB::select($sql);

        echo json_encode($contacts);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	exit();
}
function getRolodex() {
	session_write_close();
	$sql = "SELECT  -1 person_id, corp.`corporation_id`, corp.`corporation_uuid` uuid, `type`, `full_name`, `company_name`, `first_name`, `last_name`, UPPER(SUBSTRING(`company_name`, 1, 1)) as first_letter, `aka`, `preferred_name`, `full_address`, `longitude`, `latitude`, `street`, `city`, `state`, `zip`, `suite`, `phone`, '' `cell_phone`, `email`, `fax`, `ssn`, `dob`, '' `language`, `salutation`, corp.employee_phone, corp.employee_cell, corp.employee_fax, corp.`last_updated_date`, corp.`last_update_user`, corp.`deleted`, corp.`customer_id`, cpt.partie_type, cpt.employee_title, cpt.color, cpt.blurb, cpt.show_employee, `company_site`, `sort_order`, `company_name` AS display_name, corp.type AS rolo_partie
	FROM `cse_corporation` corp 
	INNER JOIN `cse_partie_type` cpt
	ON corp.type = cpt.blurb
	WHERE corp.deleted = 'N'
	AND corp.corporation_uuid = corp.parent_corporation_uuid
	AND company_name != ''
	AND corp.customer_id = " . $_SESSION['user_customer_id'];
	
	//corp.corporation_uuid = corp.parent_corporation_uuid => rolodex
	$sql .= " UNION 
	SELECT  pers.person_id, -1 `corporation_id`, pers.`person_uuid` uuid, 'applicant' `type`, `full_name`, `company_name`, `first_name`, `last_name`, UPPER(SUBSTRING(`last_name`, 1, 1)) as first_letter, `aka`, `preferred_name`, `full_address`, `longitude`, `latitude`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `cell_phone`, `email`, `fax`, `ssn`, `dob`, `language`, `salutation`, '' employee_phone,'' employee_cell, '' employee_fax, pers.`last_updated_date`, pers.`last_update_user`, pers.`deleted`, pers.`customer_id`, 'Applicant' `partie_type`, '' `employee_title`, '_card_fade_4' `color`, '' `blurb`, '' `show_employee`, '' `company_site`, 0 sort_order, CONCAT(`last_name`, ', ', `first_name`) AS display_name, '' rolo_partie 
	FROM `cse_person` pers 
	WHERE pers.deleted = 'N'
	AND pers.customer_id = " . $_SESSION['user_customer_id'] . "
	AND pers.person_uuid = pers.parent_person_uuid
	AND first_name != ''
	AND last_name != ''
	ORDER BY `display_name`";
	//die($sql);
	try {
		$contacts = DB::select($sql);

        echo json_encode($contacts);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	exit();
}

function getPartie($id) {
	session_write_close();
	if ($id > 0) {
		$sql = "SELECT  `cse_parties`.`parties_id` , `cse_parties`.`parties_uuid` ,  
		`firm` ,  `taxid` ,  `bar_number` ,  `street` ,  `city` ,  `state` ,  `zip` ,  `phone` ,  
		`extension` ,  `fax` ,  `email` ,  `party_name` ,  `salutation` , `represent` ,  
		`comments`,`cse_case`.`case_id`, `cse_case_parties`.`case_uuid`, `type`, `cse_parties`.`company_site`, `cse_parties`.`party_name`
			FROM  `cse_parties` 
			INNER JOIN  `cse_case_parties` 
			ON  `cse_parties`.`parties_uuid` =  `cse_case_parties`.`parties_uuid` 
			INNER JOIN `cse_case` ON  (`cse_case_parties`.`case_uuid` = `cse_case`.`case_uuid`
			AND `cse_parties`.`parties_id` = :id)
			WHERE `cse_parties`.`deleted` = 'N'";
	} else {
		$sql = "SELECT  -1 `parties_id`, '' `parties_uuid` ,  '' `firm` ,  '' `taxid` ,  '' `bar_number` ,  '' `street` ,  '' `city` ,  '' `state` ,  '' `zip` ,  '' `phone` ,  '' `extension` ,  '' `fax` ,  '' `email` ,  '' `party_name` ,  '' `salutation` , '' `represent` ,  '' `comments` , '' `case_id` , '' `case_uuid`, '' `type`, '' `company_site`";
	}
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		
		$stmt->execute();
		$partie = $stmt->fetchObject();
		
		echo json_encode($partie);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	exit();
}

function deleteParties() {
	session_write_close();
	
	$id = passed_var("parties_id", "post");	//$_POST["parties_id"];
	$sql = "UPDATE note 
			SET `deleted` = 'Y'
			WHERE `parties_id`=:id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("parties_id", $id);
		$stmt->execute();
		echo json_encode(array("success"=>"partie marked as deleted"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	exit();
}

function addParties() {
	session_write_close();
	
	$arrFields = array();
	$arrSet = array();
	$table_name = "";
	foreach($_POST as $fieldname=>$value) {
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="case_id" || $fieldname=="case_uuid" || $fieldname=="table_id") {
			continue;
		}
		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "'" . addslashes($value) . "'";
	}
	$table_uuid = uniqid("KS", false);
	$sql = "INSERT INTO `cse_" . $table_name ."` (`" . $table_name . "_uuid`, " . implode(",", $arrFields) . ") 
			VALUES('" . $table_uuid . "', " . implode(",", $arrSet) . ")";
	try { 
		
		DB::run($sql);
	$new_id = DB::lastInsertId();
		
		echo json_encode(array("id"=>$new_id, "uuid"=>$table_uuid)); 
		
		$case_table_uuid = uniqid("KA", false);
		$attribute_1 = "main";
		$last_updated_date = date("Y-m-d H:i:s");
		//now we have to attach the applicant to the case 
		$sql = "INSERT INTO cse_case_" . $table_name . " (`case_" . $table_name . "_uuid`, `case_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $case_table_uuid  ."', '" . $_POST["case_uuid"] . "', '" . $table_uuid . "', 'main', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
		try {
			$stmt = DB::run($sql);
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}
		
		//track now
		$sql = "track";		
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
	exit();
}

function updateParties() {
	session_write_close();
	
	$arrSet = array();
	$where_clause = "";
	$table_name = "";
	$table_id = "";;
	foreach($_POST as $fieldname=>$value) {
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="case_id" || $fieldname=="case_uuid") {
			continue;
		}
		if ($fieldname=="table_id" || $fieldname=="id") {
			$table_id = $value;
			$where_clause = " = " . $value;
		} else {
			$arrSet[] = "`" . $fieldname . "` = '" . addslashes($value) . "'";
		}
	}
	$where_clause = "`" . $table_name . "_id`" . $where_clause;
	$sql = "
	UPDATE `cse_" . $table_name . "`
	SET " . implode(", ", $arrSet) . "
	WHERE " . $where_clause;
	//echo $sql . "\r\n";
	try {
		$stmt = DB::run($sql);
		
		echo json_encode(array("success"=>$table_id)); 
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
	exit();
}

function typeParties() {
	session_write_close();
	$sql = "SELECT * FROM `cse_partie_type` WHERE 1";
	try {
		$stmt = DB::run($sql);
		echo json_encode($parties_type);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	exit();
}
function relateRolodex() {
	session_write_close();
	
	//$main_id = passed_var("main_id", "post");
	//$type = passed_var("type", "post");
	
	$related = passed_var("related", "post");
	$arrRelated = json_decode($related);
	$customer_id = $_SESSION["user_customer_id"];
	try {
		//first, let's get some uuids
		foreach($arrRelated as $index=>$relate_info) {
			$main_id = $relate_info->related_id;
			$type = $relate_info->rolodex_type;
			
			if ($type == "person") {
				$partie = getPersonInfo($main_id);
			}
			if ($type == "corporation") {
				$partie = getCorporationInfo($main_id);
			}
			$rolodex_uuid = $partie->uuid;
			
			$relate_info->rolodex_uuid = $rolodex_uuid;
			$arrRelated[$index] = $relate_info;
		}
		//die(print_r($arrRelated));
		//reset the related with uuids
		$related = json_encode($arrRelated);
		
		foreach($arrRelated as $relate_info) {
			$main_id = $relate_info->related_id;
			$type = $relate_info->rolodex_type;
			
			$rolodex_uuid = $relate_info->rolodex_uuid;
			
			//clear out any previous relation for now
			$sql = "UPDATE cse_rolodex_relations
			SET deleted = 'Y'
			WHERE `rolodex_uuid` = :rolodex_uuid
			AND `customer_id` = :customer_id";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("rolodex_uuid", $rolodex_uuid);
			$stmt->bindParam("customer_id", $customer_id);
			$stmt->execute();
			
			$sql = "INSERT INTO cse_rolodex_relations (`rolodex_uuid`,`related`,`rolodex_type`,`customer_id`)
					VALUES(:rolodex_uuid,:related,:rolodex_type,:customer_id)";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("rolodex_uuid", $rolodex_uuid);
			$stmt->bindParam("related", $related);
			$stmt->bindParam("rolodex_type", $type);
			$stmt->bindParam("customer_id", $customer_id);
			$stmt->execute();
		}
		
		echo json_encode(array("success"=>"parties related"));
		
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
			echo json_encode($error);
	}
	exit();
}

function getBingLocation() {
	$search_term = passed_var("search", "post");

	if (strlen($search_term) < 3) {
		return false;
	}
	// Old Key
	// $key = "AiLZNujKEOV1WIFx4_n0XExGSDIVpeYL0KLKmZnpm-sElaB_FdaLj8qDrk0gJc3q";
	// Old API
	// $url = "http://dev.virtualearth.net/REST/v1/Locations/" . urlencode($search_term) . "?o=&key=" . $key;
	
	// New Key
	$key = "C32lBnOSqIkn0Ik0QZR7KKpxew1Q1I3yY0wOakonYLnm5Cel4ffpJQQJ99BHAC8vTInMIYcSAAAgAZMP4YUB";
	// New API
	$url = "https://atlas.microsoft.com/search/address/json?api-version=1.0&query=" . urlencode($search_term) . "&subscription-key=$key&limit=5";
	$results = file_get_contents($url);
	$arrResults = json_decode($results);
	// $objects = $arrResults->resourceSets;
	$objects = $arrResults->results;
	//print_r($objects);
	$arrNames = array();
	// if($_SERVER['REMOTE_ADDR']=='103.238.106.253') {
	// 	var_dump($objects);
	// 	die();
	// }
	foreach($objects as $result) {
		$resources = $result->address;
		$arrNames[] = $resources;
		// foreach($resources as $resource) {
		// 	$name = $resource->name;
		// 	$address =$resource->address;
		// 	$arrNames[] = array(
		// 					"name"=>$name,
		// 					"address" => $address
		// 					);						
		// }
		//print_r($arrNames);
	}
	$response = array("results" => $arrNames);

	die(json_encode($response));
}


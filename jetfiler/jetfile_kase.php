<?php
function getUserByNickname($nickname) {
    $sql = "SELECT user.user_id, user.user_uuid, user.user_name, user.user_logon, user.user_first_name, user.user_last_name, user.nickname, user.user_email,user.user_cell, `user`.`dateandtime`, `user`.`status`, `user`.`personal_calendar`, `user`.`calendar_color`, `user`.access_token, user.user_type, IF(`user`.user_type='1', 'admin', 'user') `role`, user.user_id id, user.user_uuid uuid, job.job_id, job.job_uuid, if(job.job IS NULL, '', job.job) job, IFNULL(ce.email_name, '') email_name
			FROM ikase.`cse_user` user 
			LEFT OUTER JOIN ikase.`cse_user_job` cjob
			ON (user.user_uuid = cjob.user_uuid AND cjob.deleted = 'N')
			LEFT OUTER JOIN ikase.`cse_job` job
			ON cjob.job_uuid = job.job_uuid
			LEFT OUTER JOIN cse_user_email cue
			ON user.user_uuid = cue.user_uuid
			LEFT OUTER JOIN cse_email ce
			ON cue.email_uuid = ce.email_uuid
			WHERE user.nickname=:nickname
			AND user.customer_id = " . $_SESSION['user_customer_id'] . "";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("nickname", $nickname);
		$stmt->execute();
		$user = $stmt->fetchObject();

        return $user;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

$sql = "SELECT eams_no, jetfile_id, cus_name, cus_name_first, cus_name_last, cus_name_middle,
cus_street, cus_city, cus_state, cus_zip, cus_county 
FROM ikase.cse_customer
WHERE customer_id = :customer_id";

try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("customer_id", $cus_id);
	
	$stmt->execute();
	$customer = $stmt->fetchObject();
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
$applicant_type = "";
$sql = "SELECT ccase.case_id id, ccase.case_uuid uuid, ccase.lien_filed, ccase.case_number, ccase.cpointer,
		inj.injury_id, inj.adj_number, inj.occupation, inj.start_date, inj.end_date, inj.full_address, inj.street, inj.city, inj.state, inj.zip,
		ccase.case_date, ccase.case_type, venue.venue_uuid, ccase.rating, ccase.medical, ccase.td, ccase.rehab,  ccase.edd, ccase.claims, ccase.injury_type,
		
		venue_corporation.corporation_id venue_id, venue.venue_uuid, IF(venue.venue IS NULL, '', venue.venue) venue, venue_abbr, 
		venue_corporation.street venue_street, venue_corporation.city venue_city, 
		venue_corporation.state venue_state, venue_corporation.zip venue_zip,
		
		ccase.case_status, ccase.case_substatus, ccase.case_subsubstatus, ccase.submittedOn, ccase.supervising_attorney,
ccase.attorney, ccase.worker, ccase.interpreter_needed, ccase.case_language `case_language`, 
		app.deleted applicant_deleted, app.person_id applicant_id, app.person_uuid applicant_uuid, app.salutation applicant_salutation, IFNULL(app.full_name, '') `full_name`, app.first_name, app.last_name, app.middle_name, app.`aka`, 
		app.dob, app.gender, app.ssn, app.full_address applicant_full_address, app.street applicant_street, app.suite applicant_suite, app.city applicant_city, app.state applicant_state, app.zip applicant_zip, app.phone applicant_phone, app.fax applicant_fax, app.cell_phone applicant_cell, app.age applicant_age,
		
		IFNULL(cca.adhoc_value, '') employer_primary_secondary, IFNULL(employer.`corporation_id`,-1) employer_id, employer.company_name employer, employer.full_name employer_full_name, employer.full_address employer_full_address, employer.street employer_street, employer.city employer_city,
		employer.state employer_state, employer.zip employer_zip,
		
		IFNULL(defendant.`corporation_id`,-1) defendant_id, defendant.company_name defendant, defendant.full_name defendant_full_name, defendant.street defendant_street, defendant.city defendant_city,
		defendant.state defendant_state, defendant.zip defendant_zip,
		
		CONCAT(app.first_name,' ', IF(app.middle_name='','',CONCAT(app.middle_name,' ')),app.last_name,' vs ', IFNULL(employer.`company_name`, '')) `name`, 
		
		IFNULL(att.user_id, '') as attorney_id, 
		IFNULL(att.nickname, '') as attorney_name, 
		IFNULL(att.user_first_name, '') as attorney_first_name, 
		IFNULL(att.user_last_name, '') as attorney_last_name, 
		IFNULL(att.user_name, '') as attorney_full_name, 
		IFNULL(att.user_email, '') as attorney_email, 
		IFNULL(user.nickname, '') as worker_name, IFNULL(user.user_name, '') as worker_full_name, IFNULL(user.user_email, '') as worker_email,
		IFNULL(lien.lien_id, -1) lien_id, 
		IFNULL(settlement.settlement_id, -1) settlement_id,
		IF (cif.fee_uuid IS NOT NULL, '1', '-1') fee_id,
		job.job_id worker_job_id, job.job_uuid worker_job_uuid, if(job.job IS NULL, '', job.job) worker_job,
		IFNULL(jfile.jetfile_id, '') jetfile_id,
		IFNULL(jfile.jetfile_case_id, '') jetfile_case_id, 
		IFNULL(jfile.app_filing_id, '') app_filing_id, 
		IFNULL(jfile.info, '') jetfile_info,
		IFNULL(jfile.jetfile_dor_id, '') jetfile_dor_id,
		IFNULL(jfile.dor_info, '') dor_info,
		IFNULL(jfile.jetfile_dore_id, '') jetfile_dore_id,
		IFNULL(jfile.dore_info, '') dore_info,
		IFNULL(jfile.jetfile_lien_id, '') jetfile_lien_id,
		IFNULL(jfile.lien_info, '') lien_info,
		IFNULL(jfile.unstruc_info, '') unstruc_info
		FROM cse_case ccase ";

		if (isset($_SESSION["restricted_clients"])) {
			$restricted_clients = $_SESSION["restricted_clients"];
			
			if ($restricted_clients!="") {
				//$restricted_clients = "'" . str_replace(",", "','", $restricted_clients) . "'";
				$sql .= " INNER JOIN (
						SELECT DISTINCT ccorp.case_uuid
						FROM cse_case_corporation ccorp
						INNER JOIN cse_corporation corp
						ON ccorp.corporation_uuid = corp.corporation_uuid
						where corp.parent_corporation_uuid IN (" . $restricted_clients . ")
					) restricteds
					ON ccase.case_uuid = restricteds.case_uuid";
			}
		}
		
		$sql .= " 
		LEFT OUTER JOIN cse_case_person ccapp ON (ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N')
		LEFT OUTER JOIN ";
if (($_SESSION['user_customer_id']==1033)) { 
$sql .= "(" . SQL_PERSONX . ")";
} else {
$sql .= "cse_person";
}
$sql .= " app ON ccapp.person_uuid = app.person_uuid
		LEFT OUTER JOIN `cse_case_venue` cvenue
		ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
		LEFT OUTER JOIN `ikase`.`cse_venue` venue
		ON cvenue.venue_uuid = venue.venue_uuid
		
		LEFT OUTER JOIN `cse_case_corporation` ccorp
		ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
		LEFT OUTER JOIN `cse_corporation` employer
		ON ccorp.corporation_uuid = employer.corporation_uuid
		LEFT OUTER JOIN cse_corporation_adhoc cca
		ON employer.corporation_uuid = cca.corporation_uuid AND cca.deleted = 'N' AND cca.adhoc = 'primary_secondary'
		
		LEFT OUTER JOIN `cse_case_corporation` dcorp
		ON (ccase.case_uuid = dcorp.case_uuid AND ccorp.attribute = 'defendant' AND dcorp.deleted = 'N')
		LEFT OUTER JOIN `cse_corporation` defendant
		ON dcorp.corporation_uuid = defendant.corporation_uuid
		
		LEFT OUTER JOIN `cse_case_corporation` ccorp_venue
		ON (ccase.case_uuid = ccorp_venue.case_uuid AND ccorp_venue.attribute = 'venue' AND ccorp_venue.deleted = 'N')
		LEFT OUTER JOIN `cse_corporation` venue_corporation
		ON ccorp_venue.corporation_uuid = venue_corporation.corporation_uuid
		
		INNER JOIN `cse_case_injury` cinj
		ON ccase.case_uuid = cinj.case_uuid
		INNER JOIN `cse_injury` inj
		ON cinj.injury_uuid = inj.injury_uuid
		
		LEFT OUTER JOIN `cse_jetfile` jfile
		ON inj.injury_uuid = jfile.injury_uuid
		
		LEFT OUTER JOIN `cse_injury_lien` cil
		ON inj.injury_uuid = cil.injury_uuid
		LEFT OUTER JOIN `cse_injury_fee` cif ON inj.injury_uuid = cif.injury_uuid AND cif.deleted = 'N'
		LEFT OUTER JOIN `cse_lien` lien
		ON cil.lien_uuid = lien.lien_uuid
		LEFT OUTER JOIN `cse_injury_settlement` cis
		ON inj.injury_uuid = cis.injury_uuid
		LEFT OUTER JOIN `cse_settlement` settlement
		ON cis.settlement_uuid = settlement.settlement_uuid
		
		LEFT OUTER JOIN ikase.`cse_user` att
		ON ccase.attorney = att.user_id
		LEFT OUTER JOIN ikase.`cse_user` user
		ON ccase.worker = user.user_id
		
		LEFT OUTER JOIN ikase.`cse_user_job` cjob
		ON (user.user_uuid = cjob.user_uuid AND cjob.deleted = 'N')
		LEFT OUTER JOIN ikase.`cse_job` job
		ON cjob.job_uuid = job.job_uuid
		
		where 1
		AND inj.injury_id=:injury_id
		AND ccase.case_id=:case_id
		AND ccase.customer_id = " . $_SESSION['user_customer_id'] . "
		ORDER BY IFNULL(cca.adhoc_value, '')";
/*		
if ($_SERVER['REMOTE_ADDR']=='47.153.50.152') {
	die($sql);
}
*/
try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("case_id", $case_id);
	$stmt->bindParam("injury_id", $injury_id);
	
	$stmt->execute();
	$kases = $stmt->fetchAll(PDO::FETCH_OBJ);
	//die(print_r($kases));	
	$kase = $kases[0];
	$arrEmployerOptions = array();
	
	if (count($kases) > 1) {

		//employer drop down
		$arrEmployerID = array();
		$arrEmployerInfo = array();
		foreach($kases as $case) {
			if (!in_array($case->employer_id, $arrEmployerID)) {
				$arrEmployerID[] = $case->employer_id;
				$arrEmployerInfo[] = array(
					"id"=>$case->employer_id, 
					"name"=>noSpecialAtAll($case->employer), 
					"street"=>$case->employer_street, 
					"city"=>$case->employer_city, 
					"state"=>$case->employer_state,
					"zip"=>$case->employer_zip,  
					"primary_secondary"=>$case->employer_primary_secondary
				);
			}
		}
		
		if (count($arrEmployerID) > 1) {
			foreach($arrEmployerInfo as $employer) {
						//die(print_r($employer));
				$primary_secondary = $employer["primary_secondary"];
				if ($primary_secondary=="primary") {
					$primary_secondary = " (P)";
				}
				if ($primary_secondary=="secondary") {
					$primary_secondary = " (S)";
				}
				$employer_options = "<option value='" . $employer["id"] . "'>" . trim($employer["name"]) . $primary_secondary . "</option>
				";
				$arrEmployerOptions[] = $employer_options;
			}
		}
	}

	if ($jetfile_id=="" && is_numeric($kase->jetfile_id)) {
		$jetfile_id = $kase->jetfile_id;
	}
	if ($_SERVER['REMOTE_ADDR']=='47.153.50.152') {
	//	die(print_r($arrEmployerOptions));
	}
	//workers
	if ($kase->attorney!="" && $kase->attorney_id=="") {
		//if it was numeric, it would have been picked up
		if (!is_numeric($kase->attorney)) {
			//find by nickname
			$user = getUserByNickname($kase->attorney);
			//die(print_r($user));
			if (is_object($user)) {
				$kase->attorney_id = $user->user_id;
			}
		}
	}
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}

//parties
$sql = "SELECT  -1 person_id, corp.`corporation_id`, corp.`corporation_uuid` uuid, corp.`parent_corporation_uuid` parent_uuid, corp.`type`, IFNULL(corp.`party_type_option`, 'plaintiff') `party_type_option`, `full_name`, `company_name`, `first_name`, `last_name`, `aka`, `preferred_name`, corp.`full_address`, `longitude`, `latitude`, corp.`street`, corp.`city`, corp.`state`, corp.`zip`, corp.`suite`, `phone`, '' `cell_phone`, `email`, `fax`, `ssn`, `dob`, '' `language`, `salutation`, corp.employee_phone, corp.employee_fax, corp.`last_updated_date`, corp.`last_update_user`, corp.`deleted`, corp.`customer_id`, cpt.partie_type, cpt.employee_title, cpt.color, cpt.blurb, cpt.show_employee, `company_site`, `sort_order`, cse.case_status, cse.case_substatus, cse.attorney, cse.worker, cse.rating,
ccad.adhoc_value `claim`, cdoc.adhoc_value `doctor_type`, IFNULL(`ndoc`.`adhoc_value`, '') `claim_number`, IFNULL(`ccaref`.`adhoc_value`, '') `eams_ref_number`,
IFNULL(inj.injury_id, '') `injury_id`, IFNULL(inj.start_date, '') `start_date`, IFNULL(inj.end_date, '') `end_date`      
FROM `cse_corporation` corp 
LEFT OUTER JOIN `cse_partie_type` cpt
ON corp.type = cpt.blurb
INNER JOIN `cse_case_corporation` ccorp
ON (corp.corporation_uuid = ccorp.corporation_uuid AND ccorp.`deleted` =  'N')
INNER JOIN `cse_case` cse
ON ccorp.case_uuid = cse.case_uuid
LEFT OUTER JOIN cse_corporation_adhoc  ccaref
ON (corp.corporation_uuid = ccaref.corporation_uuid AND ccaref.`deleted` =  'N' AND ccaref.adhoc = 'eams_ref_number')
LEFT OUTER JOIN `cse_corporation_adhoc` ccad
ON (corp.corporation_uuid = ccad.corporation_uuid AND ccad.`deleted` =  'N' AND ccad.adhoc = 'claims')
LEFT OUTER JOIN `cse_corporation_adhoc` cdoc
ON (corp.corporation_uuid = cdoc.corporation_uuid AND cdoc.`deleted` =  'N' AND cdoc.adhoc = 'doctor_type'
";

$sql .= ") LEFT OUTER JOIN `cse_corporation_adhoc` ndoc
ON (corp.corporation_uuid = ndoc.corporation_uuid AND ndoc.`deleted` =  'N' AND ndoc.adhoc = 'claim_number')
LEFT OUTER JOIN `cse_injury` inj
ON ccorp.injury_uuid = inj.injury_uuid
WHERE corp.deleted = 'N'
AND corp.customer_id = " . $_SESSION['user_customer_id'] . "
AND cse.case_id =  :case_id";

$sql .= " UNION 
SELECT DISTINCT pers.person_id, -1 `corporation_id`, pers.`person_uuid` uuid, pers.`parent_person_uuid` parent_uuid, 'applicant' `type`, 'xplaintiff' `party_type_option`, `full_name`, `company_name`, `first_name`, `last_name`, `aka`, `preferred_name`, pers.`full_address`, `longitude`, `latitude`, pers.`street`, pers.`city`, pers.`state`, pers.`zip`, pers.`suite`, `phone`, `cell_phone`, `email`, `fax`, `ssn`, `dob`, `language`, `salutation`, '' employee_phone, '' employee_fax, pers.`last_updated_date`, pers.`last_update_user`, pers.`deleted`, pers.`customer_id`, 'Applicant' `partie_type`, '' `employee_title`, '_card_fade_4' `color`, '' `blurb`, '' `show_employee`, '' `company_site`, 0 sort_order, cse.case_status, cse.case_substatus, cse.attorney, cse.worker, cse.rating, '' `claim`, '' `doctor_type`, '' `claim_number`, '' `eams_ref_number`,
'' `injury_id`, '' `start_date`, '' `end_date` 
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

if (($_SESSION['user_customer_id']==1033)) {
	
	$sql .= " ORDER BY `party_type_option` DESC, `type` ASC";
	//die($sql);
} else {
	$sql .= " ORDER BY `type` ASC";
}

if ($_SERVER['REMOTE_ADDR']=='71.119.40.148') {
	//die($sql);
}
try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("case_id", $case_id);
	$stmt->execute();
	$parties = $stmt->fetchAll(PDO::FETCH_OBJ);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage(), "sql"=>$sql));
	echo json_encode($error);
}
?>

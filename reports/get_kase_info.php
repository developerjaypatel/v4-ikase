	<?php
$sql = "SELECT inj.injury_id id, ccase.case_id, ccase.lien_filed, inj.injury_number, ccase.case_uuid uuid, ccase.case_number, ccase.cpointer,ccase.source, inj.injury_number, inj.adj_number, ccase.rating, 
		IF (DATE_FORMAT(ccase.case_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.case_date, '%m/%d/%Y')) `case_date`, 
		IF (DATE_FORMAT(ccase.terminated_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.terminated_date, '%m/%d/%Y')) `terminated_date`,
ccase.case_type, ccase.medical, ccase.td, ccase.rehab,  ccase.edd, ccase.claims,
		venue.venue_uuid, IF(venue.venue IS NULL, '', venue.venue) venue, venue.address1 venue_street, venue.address2 venue_suite, venue.city venue_city, venue.zip venue_zip, venue_abbr, ccase.case_status, ccase.case_substatus, ccase.case_subsubstatus, ccase.submittedOn, ccase.supervising_attorney,
ccase.attorney, 
		IFNULL(superatt.nickname, '') as supervising_attorney_name, IFNULL(superatt.user_name, '') as supervising_attorney_full_name,
		ccase.worker, ccase.interpreter_needed, ccase.case_language `case_language`, 
		app.person_id applicant_id, app.person_uuid applicant_uuid, IFNULL(app.salutation, '') applicant_salutation,
		IF (app.first_name IS NULL, '', TRIM(app.first_name)) first_name , IF(app.last_name IS NULL, '', app.last_name) last_name, IFNULL(app.full_name, '') `full_name`, IFNULL(app.language, '') language,
		IF (DATE_FORMAT(app.dob, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(app.dob, '%m/%d/%Y')) dob, app.dob app_dob, 
		IF (app.ssn = 'XXXXXXXXX', '', app.ssn) ssn, IFNULL(app.email, '') applicant_email, IFNULL(app.phone, '') applicant_phone,
		IFNULL(employer.`corporation_id`,-1) employer_id, employer.`corporation_uuid` employer_uuid, IFNULL(employer.`company_name`, '') employer, employer.`full_address` employer_full_address, employer.`suite` `employer_suite`,
		
		IFNULL(defendant.`corporation_id`,-1) defendant_id, defendant.company_name defendant, defendant.full_name defendant_full_name, defendant.street defendant_street, defendant.city defendant_city,
		defendant.state defendant_state, defendant.zip defendant_zip,
		
		IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')) start_date, 
		IF (DATE_FORMAT(inj.end_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.end_date, '%m/%d/%Y')) end_date, inj.ct_dates_note,
		IF (inj.occupation IS NULL, '' , inj.occupation) occupation,
		CONCAT(app.first_name,' ',app.last_name,' vs ', IFNULL(employer.`company_name`, ''),' - ', 
		REPLACE(IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')), '00/00/0000', '')) `name`, ccase.case_name, 
		att.user_id attorney_id, user.user_id, 
		IFNULL(att.nickname, '') as attorney_name, IFNULL(att.user_name, '') as attorney_full_name, 
		IFNULL(user.nickname, '') as worker_name, IFNULL(user.user_name, '') as worker_full_name,
		IFNULL(lien.lien_id, -1) lien_id, 
		IFNULL(settlement.settlement_id, -1) settlement_id,
		IF (cif.fee_uuid IS NOT NULL, '1', '-1') fee_id
		FROM cse_case ccase
		LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N'
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
		
		LEFT OUTER JOIN `cse_case_corporation` dcorp
		ON (ccase.case_uuid = dcorp.case_uuid AND ccorp.attribute = 'defendant' AND dcorp.deleted = 'N')
		LEFT OUTER JOIN `cse_corporation` defendant
		ON dcorp.corporation_uuid = defendant.corporation_uuid
		
		LEFT OUTER JOIN `cse_case_injury` cinj
		ON ccase.case_uuid = cinj.case_uuid AND cinj.deleted = 'N' AND cinj.`attribute` = 'main'
		LEFT OUTER JOIN `cse_injury` inj
		ON cinj.injury_uuid = inj.injury_uuid AND inj.deleted = 'N'
		LEFT OUTER JOIN `cse_injury_lien` cil
		ON inj.injury_uuid = cil.injury_uuid
		LEFT OUTER JOIN `cse_injury_fee` cif ON inj.injury_uuid = cif.injury_uuid AND cif.deleted = 'N'
		LEFT OUTER JOIN `cse_lien` lien
		ON cil.lien_uuid = lien.lien_uuid
		LEFT OUTER JOIN `cse_injury_settlement` cis
		ON inj.injury_uuid = cis.injury_uuid
		LEFT OUTER JOIN `cse_settlement` settlement
		ON cis.settlement_uuid = settlement.settlement_uuid
		LEFT OUTER JOIN ikase.`cse_user` superatt
		ON ccase.supervising_attorney = superatt.user_id
		LEFT OUTER JOIN ikase.`cse_user` att
		ON ccase.attorney = att.user_id
		LEFT OUTER JOIN ikase.`cse_user` user
		ON ccase.worker = user.user_id
		where ccase.case_id=:id
		AND ccase.deleted = 'N'
		AND ccase.customer_id = " . $_SESSION['user_customer_id'];
/*
if ($_SERVER['REMOTE_ADDR']=='71.119.40.148') {
	die($sql);
}
*/
try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("id", $case_id);
	$stmt->execute();
	$kase = $stmt->fetchObject();
	//die(print_r($kase));
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
}

$sql = "SELECT  -1 person_id, corp.`corporation_id`, corp.`corporation_uuid` uuid, corp.`parent_corporation_uuid` parent_uuid, corp.`type`, `full_name`, `company_name`, `first_name`, `last_name`, `aka`, `preferred_name`, corp.`full_address`, `longitude`, `latitude`, corp.`street`, corp.`city`, corp.`state`, corp.`zip`, corp.`suite`, `phone`, '' `cell_phone`, `email`, `fax`, `ssn`, `dob`, '' `language`, `salutation`, corp.employee_phone, corp.employee_fax, corp.`last_updated_date`, corp.`last_update_user`, corp.`deleted`, corp.`customer_id`, cpt.partie_type, cpt.employee_title, cpt.color, cpt.blurb, cpt.show_employee, `company_site`, `sort_order`, cse.case_status, cse.case_substatus, cse.attorney, cse.worker, cse.rating,
ccad.adhoc_value `claim`, cdoc.adhoc_value `doctor_type`, IFNULL(`ndoc`.`adhoc_value`, '') `claim_number`,
IFNULL(inj.start_date, '') `start_date`, IFNULL(inj.end_date, '') `end_date`      
FROM `cse_corporation` corp 
LEFT OUTER JOIN `cse_partie_type` cpt
ON corp.type = cpt.blurb
INNER JOIN `cse_case_corporation` ccorp
ON (corp.corporation_uuid = ccorp.corporation_uuid AND ccorp.`deleted` =  'N')
INNER JOIN `cse_case` cse
ON ccorp.case_uuid = cse.case_uuid
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

	//nothing past Venue
	$sql .= " AND (cpt.sort_order <= 10 AND cpt.sort_order IS NOT NULL)";
	//medical providers only if PTP or Secondary for Dash
	$sql .= " AND (
	CONCAT(corp.`type`, IFNULL(cdoc.adhoc_value, '')) = 'medical_providerPTP'
	OR
	CONCAT(corp.`type`, IFNULL(cdoc.adhoc_value, '')) = 'medical_providersecondary physician'
	OR
	CONCAT(corp.`type`, IFNULL(cdoc.adhoc_value, '')) NOT LIKE 'medical_provider%'
) ";

$sql .= " UNION 
SELECT DISTINCT pers.person_id, -1 `corporation_id`, pers.`person_uuid` uuid, pers.`parent_person_uuid` parent_uuid, 'applicant' `type`, `full_name`, `company_name`, `first_name`, `last_name`, `aka`, `preferred_name`, pers.`full_address`, `longitude`, `latitude`, pers.`street`, pers.`city`, pers.`state`, pers.`zip`, pers.`suite`, `phone`, `cell_phone`, `email`, `fax`, `ssn`, `dob`, `language`, `salutation`, '' employee_phone, '' employee_fax, pers.`last_updated_date`, pers.`last_update_user`, pers.`deleted`, pers.`customer_id`, 'Applicant' `partie_type`, '' `employee_title`, '_card_fade_4' `color`, '' `blurb`, '' `show_employee`, '' `company_site`, 0 sort_order, cse.case_status, cse.case_substatus, cse.attorney, cse.worker, cse.rating, '' `claim`, '' `doctor_type`, '' `claim_number`,
'' `start_date`, '' `end_date` 
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
AND cse.case_id =  :case_id
ORDER BY `sort_order`";

try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("case_id", $case_id);
	$stmt->execute();
	$parties = $stmt->fetchAll(PDO::FETCH_OBJ);
	foreach($parties as $index=>$partie) {
		$parties[$index]->company_name = urlencode($partie->company_name);
		$parties[$index]->first_name = urlencode($partie->first_name);
		$parties[$index]->last_name = urlencode($partie->last_name);
		$parties[$index]->full_name = urlencode($partie->full_name);
	}
	//die(print_r($parties));
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage(), "sql"=>$sql));
		echo json_encode($error);
}

$url = "https://www.matrixdocuments.com/dis/pws/manage/request/forward_ikase_data.php";
$fields = "cus_id=" . $_SESSION['user_customer_id'] . "&customer_name=" . $_SESSION['user_customer_name'] . "&inhouse_id=" . $customer->inhouse_id . "&case_id=" . $case_id;
$fields .= "&kase=" . json_encode($kase);
$fields .= "&parties=" . json_encode($parties);
$fields .= "&adj_numbers=" . json_encode($arrADJs);
$fields .= "&attachment=" . $targetFile;
$fields .= "&ikase_customer_id=" . $_SESSION['user_customer_id'];
$fields .= "&specific_instructions=" . urlencode($specific_instructions);
$fields .= "&dmsauth=" . urlencode($dmsauth);
$fields .= "&kase_attachments=" . $kase_attachments;
$fields .= "&subdomain=v2";
$fields_string = $fields;

//echo $url . "?" . $fields_string;	//
//die("here");

//open connection
$ch = curl_init();

//set the url, number of POST vars, POST data
curl_setopt($ch,CURLOPT_URL,$url);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
curl_setopt($ch,CURLOPT_HEADER, false); 
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt ($ch, CURLOPT_COOKIEFILE, "cookies.txt");
curl_setopt($ch, CURLOPT_POST, count($fields_string));
curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
//curl_setopt($ch,CURLOPT_FOLLOWLOCATION,TRUE);

//execute post
$result = curl_exec($ch);

// echo "<br />" . $result . "<br />";
// die();

$request_id = str_replace("exported ", "", $result);
$request_date = date("Y-m-d H:i:s");
		
//make sure everything is tracked
$sql = "INSERT INTO `cse_case_matrixrequest` (`case_id`, `request_id`, `request_by`, `request_date`, `customer_id`)
SELECT :case_id, :request_id, :request_by, :request_date, :customer_id
FROM dual
WHERE NOT EXISTS (
	SELECT * 
	FROM `cse_case_matrixrequest` 
	WHERE case_id = :case_id
	AND request_id = :request_id
	AND customer_id = :customer_id
)";

try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("case_id", $case_id);
	$stmt->bindParam("request_id", $request_id);
	$stmt->bindParam("request_date", $request_date);
	$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
	$stmt->bindParam("request_by", $_SESSION['user_nickname']);
	$stmt->execute();
} catch(PDOException $e) {
	//$error = array("error"=> array("text"=>$e->getMessage()));
	//	echo json_encode($error);
}

/*
if ($_SERVER['REMOTE_ADDR']=='71.106.134.58') {
	echo $url . "?";
	die($fields);
}
*/
?>

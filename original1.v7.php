<?php
//Set no caching
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
header("Cache-Control: no-store, no-cache, must-revalidate"); 
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();

session_write_close();
if($_SERVER["HTTPS"]=="off") {
	header("location:https://www.ikase.org/v7.php");
}

if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
	//die(print_r($_SESSION));
	header("location:index.php?cusid=-1");
	die();
}
//owners (and administrators?) are redirected
if ($_SESSION['user_customer_id']==-1 && $_SESSION['user_role']=="owner") {
	header("location:../manage/customers/");
	die();
}
$header_start_time = 0;
$arrTiming = array();

include("api/connection.php");
include("browser_detect.php");

if ($blnDebug) {
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = 0;
	
	$arrTiming[] = $total_time;
	$header_start_time = $time;
}
$dbname = "gtg_thecase";
if (isset($_SERVER['DOCUMENT_ROOT'])) {
	if ($_SERVER['DOCUMENT_ROOT']=="C:\\inetpub\\wwwroot\\ikase.org") {
		$dbname = "ikase";	
		/*
		if (isset($_SESSION['user_customer_id'])){
			if ($_SESSION['user_customer_id']=='1049') {
				$dbname .= "_glauber";
			}
			if ($_SESSION['user_customer_id']=='1055') {
				$dbname .= "_reino";
			}
		}
		*/
		if (isset($_SESSION['user_data_source'])){
			if ($_SESSION['user_data_source']!="") {
				$dbname .= "_" . $_SESSION['user_data_source'];
			}
		}	
		
		if ($_SESSION['user_customer_id']=='1049') {
			
		}
	}
}

//setup chat
$chat_dir = $_SERVER['DOCUMENT_ROOT'] . '\\chats\\' . $_SESSION['user_customer_id'];
if (!is_dir($chat_dir)) {
	mkdir($chat_dir, 0755, true);
}
$filename = $chat_dir . '\\changed_' . $_SESSION['user_plain_id'] . '.txt';
if (!$handle = fopen($filename, 'w')) {
	$error = "Cannot open file ($filename)";
	echo json_encode($error);
	exit;
}
if (fwrite($handle, "") === FALSE) {
   $error = "Cannot write to file ($filename)";
   echo json_encode($error);
   exit;
}

//$blnIPad = false;
$db = getConnection();
//get the list of adhocs
$adhoc_settings = array();
$sql = "SELECT `adhoc_id`, `adhoc_uuid`, `adhoc`, `type`, `acceptable_values`, `default_value`, `format`, `deleted` 
FROM `cse_adhoc` 
WHERE 1
ORDER BY adhoc ASC";

try {
	
	$stmt = $db->query($sql);
	$adhoc_settings = $stmt->fetchAll(PDO::FETCH_OBJ);
	
} catch(PDOException $e) {
	$error = array("error1"=> array("text"=>$e->getMessage()));
	die(json_encode($error));
}

if ($blnDebug) {
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	$arrTiming[] = $total_time;
	$header_start_time = $time;
}

//get customer settings
/*
$sql = "SELECT cs.*, cs.setting_id id, cs.setting_uuid uuid
			FROM  `cse_setting` cs
			WHERE 1
			AND `cs`.customer_id = " . $_SESSION['user_customer_id'] . "
			ORDER BY cs.`category`";
*/			
$sql = "SELECT cs.*, cs.setting_id id, cs.setting_uuid uuid
			FROM  `cse_setting` cs
			WHERE 1
			AND `cs`.`deleted` = 'N'
			AND `cs`.customer_id = " . $_SESSION['user_customer_id'] . "
			AND cs.setting_uuid NOT IN (SELECT `setting_uuid` FROM `cse_setting_user`)
			
			UNION
			
			SELECT cs.*, cs.setting_id id, cs.setting_uuid uuid
			FROM  `cse_setting` cs
			INNER JOIN `cse_setting_user` csu
			ON cs.setting_uuid = csu.setting_uuid
			WHERE 1
			AND `cs`.`deleted` = 'N'
			AND `cs`.customer_id = " . $_SESSION['user_customer_id'] . "
			AND csu.user_uuid = '" . $_SESSION['user_id'] . "'
			
			ORDER BY `category`";
try {
	
	$stmt = $db->query($sql);
	//die($sql);
	$customer_settings = $stmt->fetchAll(PDO::FETCH_OBJ);
	
} catch(PDOException $e) {
	$error = array("error2"=> array("text"=>$e->getMessage()));
	die(json_encode($error));
}

if ($blnDebug) {
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	$arrTiming[] = $total_time;
	$header_start_time = $time;
}

$arrSettingValues = array();
$arrSettings = array();

foreach($customer_settings as $setting_info) {
	$category = $setting_info->category;
	$setting = $setting_info->setting;
	$setting_value = $setting_info->setting_value;
	$arrSettings[$setting] = $setting_value;
	$arrSettingValues[$category][$setting] = $setting_value;
}

//basic defaults
if (!isset($arrSettings["case_number_prefix"])) {
	$arrSettings["case_number_prefix"] = "";
}
//event types
$setting_options = "";

$sql = "SELECT DISTINCT *
		FROM `cse_setting` 
		WHERE `category` = 'calendar_type'
		AND customer_id = '" . $_SESSION['user_customer_id'] . "'
        ORDER by `setting`";
		
try {
	$stmt = $db->query($sql);
	$event_types = $stmt->fetchAll(PDO::FETCH_OBJ);
	
} catch(PDOException $e) {
	$error = array("error3"=> array("text"=>$e->getMessage()));
	die(json_encode($error));
}
if ($blnDebug) {
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	$arrTiming[] = $total_time;
	$header_start_time = $time;
}

$setting_options = "<option value=''>Filter By</option>";

foreach($event_types as $event_type) {
	$setting_id = $event_type->setting_id;
	$setting = $event_type->setting;
	
	$option = "<option value='" . $setting . "'>" . $setting . "</option>";
	$setting_options .= "" . $option;
}
$customer_calendars = null;
$sql = "SELECT *
		FROM `" . $dbname . "`.cse_calendar 
		WHERE 1
		AND customer_id = '" . $_SESSION['user_customer_id'] . "'
		ORDER by sort_order";
		//die($sql);
try {
	$stmt = $db->query($sql);
	$customer_calendars = $stmt->fetchAll(PDO::FETCH_OBJ);

} catch(PDOException $e) {
	$error = array("error4"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
if ($blnDebug) {
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	$arrTiming[] = $total_time;
	$header_start_time = $time;
}

//partie type info
$sql = "SELECT * 
FROM `cse_partie_type` 
WHERE 1
ORDER BY blurb ASC";
try {
	
	$stmt = $db->query($sql);
	$partie_settings = $stmt->fetchAll(PDO::FETCH_OBJ);
	
} catch(PDOException $e) {
	$error = array("error5"=> array("text"=>$e->getMessage()));
	die(json_encode($error));
}
if ($blnDebug) {
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	$arrTiming[] = $total_time;
	$header_start_time = $time;
}

//workers/users
$sql = "SELECT user.user_id, user.user_uuid, user.user_name, user.user_logon, user.user_first_name, user.user_last_name, user.nickname, `user`.user_email, `user`.`dateandtime`, `user`.status, `user`.access_token, `user`.user_type, IF(`user`.user_type='1', 'admin', 'user') `role`, `user`.user_id id, `user`.user_uuid uuid, job.job_id, job.job_uuid, if(job.job IS NULL, '', job.job) job
		FROM ikase.`cse_user` user 
		LEFT OUTER JOIN ikase.`cse_user_job` cjob
		ON (user.user_uuid = cjob.user_uuid AND cjob.deleted = 'N')
		LEFT OUTER JOIN ikase.`cse_job` job
		ON cjob.job_uuid = job.job_uuid
		WHERE user.deleted = 'N'
		AND user.customer_id = " . $_SESSION['user_customer_id'] . "
		ORDER by user.user_id";
try {
	
	$stmt = $db->query($sql);
	$users = $stmt->fetchAll(PDO::FETCH_OBJ);
	
} catch(PDOException $e) {
	$error = array("error7"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
}
if ($blnDebug) {
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	$arrTiming[] = $total_time;
	$header_start_time = $time;
}

//attorneys
$sql = "SELECT * FROM `cse_attorney`";	
$sql .= " WHERE 1
AND deleted = 'N'
AND customer_id = " . $_SESSION['user_customer_id'] . "
ORDER by firm_name";
try {
	
	$stmt = $db->prepare($sql);
	//$stmt->bindParam("search_term", $search_term);
	$stmt->execute();
	$attorneys = $stmt->fetchAll(PDO::FETCH_OBJ);
	
} catch(PDOException $e) {
	$error = array("error8"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
if ($blnDebug) {
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	$arrTiming[] = $total_time;
	$header_start_time = $time;
}
/*
//eams carriers
$sql = "SELECT `carrier_id`, `eams_ref_number`, `firm_name`, `street_1`, `street_2`, `city`, `state`, `zip_code`, 
`phone`, `service_method`, `last_update`, `last_import_date`, `carrier_id` `id`, `carrier_uuid` `uuid`
FROM `ikase`.`cse_eams_carriers` ";
$sql .= " ORDER by firm_name";
try {
	
	$stmt = $db->prepare($sql);
	//$stmt->bindParam("search_term", $search_term);
	$stmt->execute();
	$eams_carriers = $stmt->fetchAll(PDO::FETCH_OBJ);
	
} catch(PDOException $e) {
	$error = array("error9"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
*/
if ($blnDebug) {
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	$arrTiming[] = $total_time;
	$header_start_time = $time;
}
/*
//eams attorneys
$sql = "SELECT `rep_id`, `eams_ref_number`, `firm_name`, `street_1`, `street_2`, `city`, `state`, `zip_code`, `phone`, `service_method`, `last_update`, `rep_id` `id`, `rep_uuid` `uuid`
FROM `ikase`.`cse_eams_reps` ";
$sql .= " ORDER by firm_name";
$eams_reps = "";
try {
	
	$stmt = $db->prepare($sql);
	//$stmt->bindParam("search_term", $search_term);
	$stmt->execute();
	$eams_reps = $stmt->fetchAll(PDO::FETCH_OBJ);
	
} catch(PDOException $e) {
	$error = array("errorC"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
*/
if ($blnDebug) {
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	$arrTiming[] = $total_time;
	$header_start_time = $time;
}
//medical specialties
$sql = "SELECT `specialty_id`, `specialty`, `description` FROM `cse_specialties` WHERE 1";
try {
	
	$stmt = $db->query($sql);
	$specialties = $stmt->fetchAll(PDO::FETCH_OBJ);
	
} catch(PDOException $e) {
	$error = array("error11"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
if ($blnDebug) {
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	$arrTiming[] = $total_time;
	$header_start_time = $time;
}
//venues
$sql = "SELECT * FROM `cse_venue` 
WHERE 1
ORDER BY venue ASC";
try {
	
	$stmt = $db->query($sql);
	$venues = $stmt->fetchAll(PDO::FETCH_OBJ);
	
} catch(PDOException $e) {
	$error = array("error10"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
if ($blnDebug) {
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	$arrTiming[] = $total_time;
	$header_start_time = $time;
}

//kases
$sql = "SELECT DISTINCT 
			inj.injury_id id, ccase.case_id, ccase.lien_filed, inj.injury_number, ccase.case_uuid uuid, ccase.case_number, ccase.source, inj.injury_number, inj.adj_number, ccase.rating, 
			IF (DATE_FORMAT(ccase.case_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.case_date, '%m/%d/%Y')) case_date , 
			ccase.case_type, ccase.medical, ccase.td, ccase.rehab,  ccase.edd, ccase.claims,
			venue.venue_uuid, IF(venue.venue IS NULL, '', venue.venue) venue, venue.address1 venue_street, venue.address2 venue_suite, venue.city venue_city, venue.zip venue_zip, venue_abbr, ccase.case_status, ccase.case_substatus, ccase.case_subsubstatus, ccase.submittedOn, ccase.supervising_attorney,
    ccase.attorney, 
			IFNULL(superatt.nickname, '') as supervising_attorney_name, IFNULL(superatt.user_name, '') as supervising_attorney_full_name,
			ccase.worker, ccase.interpreter_needed, ccase.case_language `case_language`, 
			app.person_id applicant_id, app.person_uuid applicant_uuid,
			IF (app.first_name IS NULL, '', TRIM(app.first_name)) first_name , IF(app.last_name IS NULL, '', app.last_name) last_name, IFNULL(app.full_name, '') `full_name`, IFNULL(app.language, '') language,
			IF (DATE_FORMAT(app.dob, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(app.dob, '%m/%d/%Y')) dob, app.dob app_dob, 
			IF (app.ssn = 'XXXXXXXXX', '', app.ssn) ssn,
			IFNULL(employer.`corporation_id`,-1) employer_id, employer.`corporation_uuid` employer_uuid, employer.`company_name` employer, employer.`full_address` employer_full_address,
			IFNULL(defendant.`corporation_id`,-1) defendant_id, defendant.`corporation_uuid` defendant_uuid, defendant.`company_name` defendant, defendant.`full_address` defendant_full_address,
			IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')) start_date, 
			IF (DATE_FORMAT(inj.end_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.end_date, '%m/%d/%Y')) end_date, inj.ct_dates_note,
			IF (inj.occupation IS NULL, '' , inj.occupation) occupation,
			CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`,' - ', IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y'))) `name`, att.user_id attorney_id, user.user_id, 
			IFNULL(att.nickname, '') as attorney_name, IFNULL(att.user_name, '') as attorney_full_name, 
			IFNULL(user.nickname, '') as worker_name, IFNULL(user.user_name, '') as worker_full_name,
			IFNULL(lien.lien_id, -1) lien_id, 
			IFNULL(settlement.settlement_id, -1) settlement_id,
			IF (cif.fee_uuid IS NOT NULL, '1', '-1') fee_id
			FROM cse_case ccase
			INNER JOIN (
				SELECT case_id 
                FROM cse_case 
                WHERE case_status != 'Closed'
				AND case_status != 'Closed by C & R'
				AND case_status != 'Closed by Stipulation'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
                ORDER BY case_date DESC 
                LIMIT 0, 500
			) climit
            ON ccase.case_id = climit.case_id
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N'
			LEFT OUTER JOIN ";

if ($_SESSION["user_customer_id"]==1033) { 
	$sql .= "(" . SQL_PERSONX . ")";
} else {
	$sql .= "cse_person";
}

//$sql .= "cse_person";
$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			LEFT OUTER JOIN `cse_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			LEFT OUTER JOIN `cse_case_corporation` ecorp
			ON (ccase.case_uuid = ecorp.case_uuid AND ecorp.attribute = 'defendant' AND ecorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` defendant
			ON ecorp.corporation_uuid = defendant.corporation_uuid
			INNER JOIN `cse_case_injury` cinj
			ON ccase.case_uuid = cinj.case_uuid AND cinj.deleted = 'N'
			INNER JOIN `cse_injury` inj
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
			WHERE ccase.deleted ='N' 
			AND ccase.case_status NOT LIKE '%close%'
			AND ccase.customer_id = " . $_SESSION['user_customer_id'];

$sql .= " ORDER by IF (TRIM(app.first_name) = '', TRIM(app.full_name), TRIM(app.first_name)), app.last_name, ccase.case_id, inj.injury_number";

try {
	
	$stmt = $db->query($sql);
	$kases = $stmt->fetchAll(PDO::FETCH_OBJ);
	
	if ($blnDebug){
		die($sql);
	}
} catch(PDOException $e) {
	$error = array("error12"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
if ($blnDebug) {
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	$arrTiming[] = $total_time;
	$header_start_time = $time;
}

//recent kases
$sql = "SELECT DISTINCT 
		inj.injury_id id, ccase.case_id, inj.injury_number, ccase.case_uuid uuid, ccase.case_number, inj.injury_number, inj.adj_number, ccase.rating, 
			IF (DATE_FORMAT(ccase.case_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.case_date, '%m/%d/%Y')) case_date , 
			ccase.case_type, ccase.medical, ccase.td, ccase.rehab,  ccase.edd, ccase.claims,
			venue.venue_uuid, IF(venue.venue IS NULL, '', venue.venue) venue, venue_abbr, ccase.case_status, ccase.case_substatus, ccase.case_subsubstatus, ccase.submittedOn, ccase.supervising_attorney,
    ccase.attorney, ccase.worker, ccase.interpreter_needed, ccase.case_language `case_language`,  
			app.person_id applicant_id, app.person_uuid applicant_uuid,
			IF (app.first_name IS NULL, '', app.first_name) first_name , IF(app.last_name IS NULL, '', app.last_name) last_name, app.full_name, app.language,
			IF (DATE_FORMAT(app.dob, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(app.dob, '%m/%d/%Y')) dob, 
			IF (app.ssn = 'XXXXXXXXX', '', app.ssn) ssn,
			employer.`corporation_id` employer_id, employer.`corporation_uuid` employer_uuid, employer.`company_name` employer, 
			IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')) start_date, 
			IF (DATE_FORMAT(inj.end_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.end_date, '%m/%d/%Y')) end_date, 
			IF (inj.occupation IS NULL, '' , inj.occupation) occupation,
			CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`,' - ', IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y'))) `name`
		FROM cse_case ccase

INNER JOIN (
SELECT cct.case_id, MAX( time_stamp ) time_stamp
FROM  `cse_case_track` cct
INNER JOIN cse_case ccase ON cct.case_id = ccase.case_id
WHERE operation =  'view'
AND user_uuid =  '" . $_SESSION['user_id'] . "'
AND ccase.customer_id = " . $_SESSION['user_customer_id'] . "
AND ccase.deleted =  'N'
AND ccase.case_status NOT LIKE '%close%'
GROUP BY cct.case_id
ORDER BY MAX( time_stamp ) DESC 
LIMIT 0 , 5
) recent
ON ccase.case_id = recent.case_id
		LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N'
			LEFT OUTER JOIN ";
if ($blnDebug) { 
	$sql .= "(" . SQL_PERSONX . ")";
} else {
	$sql .= "cse_person";
}
$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			LEFT OUTER JOIN `cse_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			LEFT OUTER JOIN `cse_case_injury` cinj
			ON ccase.case_uuid = cinj.case_uuid
			LEFT OUTER JOIN `cse_injury` inj
			ON cinj.injury_uuid = inj.injury_uuid AND inj.deleted = 'N' AND cinj.deleted = 'N'
		WHERE ccase.deleted ='N' 
		AND ccase.case_status NOT LIKE '%close%'
		AND ccase.customer_id = " . $_SESSION['user_customer_id'] . "
		AND app.person_uuid IS NOT NULL
		ORDER by recent.time_stamp DESC";
//die($sql);
try {
	
	$stmt = $db->query($sql);
	$recent_kases = $stmt->fetchAll(PDO::FETCH_OBJ);
	
} catch(PDOException $e) {
	$error = array("errorb"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
if ($blnDebug) {
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	$arrTiming[] = $total_time;
	$header_start_time = $time;
}

$sql = "SELECT * FROM `cse_task` 
		WHERE customer_id =  " . $_SESSION['user_customer_id'] . "
		AND deleted = 'N'
		ORDER BY task_id DESC
		LIMIT 0, 200";
try {
	$stmt = $db->query($sql);
	$recent_tasks = $stmt->fetchAll(PDO::FETCH_OBJ);
} catch(PDOException $e) {
	$error = array("errora"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
}
if ($blnDebug) {
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	$arrTiming[] = $total_time;
	$header_start_time = $time;
}

$sql = "SELECT DISTINCT inj.*, inj.injury_id id, inj.injury_uuid uuid, ccase.case_id,
		IFNULL(lien.lien_id, -1) lien_id, 
		IFNULL(settlement.settlement_id, -1) settlement_id,
		IF (cif.fee_uuid IS NOT NULL, '1', '-1') fee_id
		FROM `cse_injury` inj 
		LEFT OUTER JOIN `cse_injury_lien` cil
		ON inj.injury_uuid = cil.injury_uuid
		LEFT OUTER JOIN `cse_injury_fee` cif ON inj.injury_uuid = cif.injury_uuid AND cif.deleted = 'N'
		LEFT OUTER JOIN `cse_lien` lien
		ON cil.lien_uuid = lien.lien_uuid
		LEFT OUTER JOIN `cse_injury_settlement` cis
		ON inj.injury_uuid = cis.injury_uuid
		LEFT OUTER JOIN `cse_settlement` settlement
		ON cis.settlement_uuid = settlement.settlement_uuid		
		INNER JOIN cse_case_injury ccinj
		ON inj.injury_uuid = ccinj.injury_uuid
		INNER JOIN cse_case ccase
		ON ccinj.case_uuid = ccase.case_uuid
		INNER JOIN (
			SELECT case_id 
			FROM cse_case 
			WHERE case_status != 'Closed'
			AND case_status != 'Closed by C & R'
			AND case_status != 'Closed by Stipulation'
			AND deleted ='N' 
			AND customer_id = " . $_SESSION['user_customer_id'] . "
			ORDER BY case_date DESC 
			LIMIT 0, 500
		) climit
		ON ccase.case_id = climit.case_id";
$sql .= " WHERE 1
		AND inj.customer_id = " . $_SESSION['user_customer_id'] . "
		AND ccase.deleted = 'N'
		AND ccase.case_status NOT LIKE '%close%'
		AND inj.deleted = 'N'";
try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$dois = $stmt->fetchAll(PDO::FETCH_OBJ);
			
	$db = null;
	//die($injury);   

} catch(PDOException $e) {
	$error = array("error6"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
}
if ($blnDebug) {
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	$arrTiming[] = $total_time;
	$header_start_time = $time;
}

$db = null;
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="img/favicon.jpg">

    <title>iKase - Legal Case Management System. Fast. Mobile</title>

    
    <link rel="stylesheet" type="text/css" href="css/offline-theme-chrome.css" />
    <link rel="stylesheet" type="text/css" href="css/uploadifive.css">
    
    <link rel='stylesheet' type='text/css' href='css/jquery.gridster.css' />
    <link rel="stylesheet" type="text/css" href="css/local_gridster.css" />
    <link rel="stylesheet" type="text/css" href="css/jquery.datetimepicker.css" />
    <link rel="stylesheet" type="text/css" href="css/token-input.css" />
    <link rel="stylesheet" type="text/css" href="css/token-input-eams.css" />
    <link rel="stylesheet" type="text/css" href="css/token-input-facebook.css" />
    <link rel="stylesheet" type="text/css" href="css/token-input-chat.css" />
    <link rel="stylesheet" type="text/css" href="css/token-input-event.css" />
    <link rel="stylesheet" type="text/css" href="css/token-input-facebook.css" />
    <link rel="stylesheet" type="text/css" href="css/token-input-kase.css" />
    <link rel="stylesheet" type="text/css" href="css/token-input-message.css" />
    <link rel="stylesheet" type="text/css" href="css/token-input-person.css" />
    <link rel="stylesheet" type="text/css" href="css/token-input-task.css" />
    <link rel='stylesheet' type='text/css' href='css/fullcalendar.css' />
    
	
    <!-- Bootstrap core CSS -->
    <!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="css/bootstrap.3.0.3.min.css">

	<!-- Optional theme -->
	<link rel="stylesheet" href="css/bootstrap-theme.min.css">
    <!--<link rel="stylesheet" href="css/bootstrap-modal-bs3patch.css">
    <link rel="stylesheet" href="css/bootstrap-modal.css">-->
    

    <!-- Custom styles for this template -->
    <link href="css/sticky-footer-navbar.css" rel="stylesheet">
	<link rel='stylesheet' type='text/css' href='css/jquery-ui-1.8.13.custom.css' />
    
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
    <link href="css/tablesorter_blue.css" rel="stylesheet">
    <link href="text_editor/jquery-te-1.4.0.css" rel="stylesheet">
    
    <link href="cleditor/jquery.cleditor.css" rel="stylesheet">
    
    <!--fonts-->
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,300,600' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Duru+Sans' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Source+Sans+Pro:200,400' rel='stylesheet' type='text/css'>
    <link type="text/css" rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500">
    <link rel="stylesheet" type="text/css" href="css/jquery.timepicker.css">
    <link rel="stylesheet" href="css/jquery-ui.css">
    <link rel='stylesheet' type='text/css' href='css/backbone.autocomplete.css' />
    <link rel='stylesheet' type='text/css' href='css/backbone.autocomplete_reps.css' />
    <link rel='stylesheet' type='text/css' href='css/backbone.autocomplete_attorney.css' />
    <link rel='stylesheet' type='text/css' href='css/backbone.autocomplete_kase.css' />
    <link rel='stylesheet' type='text/css' href='css/backbone.autocomplete_worker.css' />
    <link rel='stylesheet' type='text/css' href='css/backbone.autocomplete_worker_event.css' />
    <link rel='stylesheet' type='text/css' href='css/backbone.autocomplete_specialty.css' />
    
    <link rel="stylesheet" href="multilookup/styles/token-input.css" type="text/css" />    
    
    <link rel='stylesheet' type='text/css' href='css/jquery.jspanel.css' />
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">


    <link href="fonts/fontello-a1b266d9/css/fontawesome.css" rel="stylesheet" />
    <link href="fonts/fontello-a1b266d9/css/fontawesome-codes.css" rel="stylesheet" />
    <link href="fonts/fontello-a1b266d9/css/fontawesome-embedded.css" rel="stylesheet" />
    <link href="fonts/fontello-a1b266d9/css/fontawesome-ie7-codes.css" rel="stylesheet" />
    <link href="fonts/fontello-a1b266d9/css/fontawesome-ie7.css" rel="stylesheet" />
    <link href="fonts/fontello-a1b266d9/css/animation.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet">
  	<style type="text/css">
	.modal-dialog {
		margin: 0;
		position: absolute;
		top: 50%;
		left: 50%;	
	}
	
	.modal-body {
		overflow-y: auto;
		overflow-y: hidden;
	}
	.modal-footer {
		margin-top: 0;
		background:url(../img/glass.png);
	}
	
	@media (max-width: 767px) {
	  .modal-dialog {
		width: 100%;
	  }
	}
	</style>  
  </head>
  
  <body>

    <!-- Wrap all page content here -->
    <div id="wrap">

      <!-- Fixed navbar -->
      <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
        <div class="container kase_header">
        </div>
      </div>
      <div style="width:100%; margin-left:auto; margin-right:auto; text-align:center; display:none" class="large_white_text" id="page_title"></div>
      <!-- Begin page content -->
      <div class="col-md-12">
          <div class="container kase_body">
                
                <a id="left_side_show" style="cursor:pointer; display:none; position:absolute; left:5px; top:70px" onClick="showLeftSide();">
                <i style="font-size:1.5em;color:#FFFFFF" class="glyphicon glyphicon-chevron-right" title="Click to show Recent kases"></i></a>
                <a id="left_side_hide" style="cursor:pointer; display:none; position:absolute; left:5px; top:70px" onClick="hideLeftSide();">
                <i style="font-size:1.5em;color:#FFFFFF" class="glyphicon glyphicon-chevron-left" title="Click to hide Recent kases"></i></a>
                <div id="left_sidebar" class="col-md-2 sidebar left_sidebar" style="display:inline-block;"></div>         
                <div id="document_search" class="col-md-12"></div>
                <div id="search_results" class="col-md-12"></div>
                <div id="ikase_loading" class="col-md-12"></div>
                <div id="content" class="col-md-12 kase_content"></div>
                </div>
      </div>
    </div>
    <div id="chat_holder">
    	<div id="chat_bottom"></div>
        <div id="chat_box"></div>
    </div>
    <div id="footer" style="display:none">
        <div id="footer_left" class="col-md-6"></div>
        <div id="footer_right" class="col-md-6"></div>    
    </div>
    
    <!-- Modal -->
  	<div class="modal fade" id="myModal4" tabindex="-1" role="dialog" aria-labelledby="myModal4Label" aria-hidden="true" style="">
    <div class="modal-dialog" style="opacity:1">
        <div class="modal-content">
          <div class="modal-header">
          	<input type="hidden" id="modal_type" value="">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <div id="modal_save_holder" style="float:right"></div>
            <div id="gifsave" style="float:right; display:none">
            	<i class="icon-spin4 animate-spin" style="font-size:1.5em"></i>
            </div>
            <h4 class="modal-title" id="myModalLabel" style="color:#FFFFFF;">Modal title</h4>
          </div>
          <div class="modal-body" id="myModalBody" style="color:#FFFFFF;">
          <i class="icon-spin4 animate-spin"></i></div>
          <div class="modal-footer" style="color:#FFFFFF; display:none">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button type="button" class="interoffice btn btn-primary save" onClick="saveModal()">Save changes</button>
            <div style="float:left" id="apply_notes_holder">
            	<input type="checkbox" id="apply_notes">&nbsp;Apply to Notes
            </div>
          </div>
        </div>
      </div>
    <!-- /.modal-dialog -->
  </div>
  	<!-- /.modal -->
	
	<!-- Modal -->
  	<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true" style="">
    <div class="modal-dialog" style="opacity:1">
        <div class="modal-content">
          <div class="modal-header">
          	<input type="hidden" id="modal_type" value="">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <div id="modal_save_holder" style="float:right"></div>
            <div id="gifsave" style="float:right; display:none">
            	<i class="icon-spin4 animate-spin" style="font-size:1.5em"></i>
            </div>
            <h4 class="modal-title" id="deleteModalLabel" style="color:#FFFFFF;">Modal title</h4>
          </div>
          <div class="modal-body" id="deleteModalBody" style="color:#FFFFFF;">
          <i class="icon-spin4 animate-spin"></i></div>
          <div class="modal-footer" style="color:#FFFFFF; display:none">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button type="button" class="interoffice btn btn-primary save" onClick="deleteModal()">Delete</button>
            <div style="float:left" id="apply_notes_holder">
            	<input type="checkbox" id="apply_notes">&nbsp;Apply to Notes
            </div>
          </div>
        </div>
      </div>
    <!-- /.modal-dialog -->
  </div>
  	<!-- /.modal -->
   <?php 
	if ($blnDebug) {
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $header_start_time), 4);
		
		$arrTiming[] = $total_time;
	$header_start_time = $time;
	}
	?>
    <!-- cache scripts -->
    <script type="text/javascript" src="lib/jquery.1.10.2.js"></script>
    <script type="text/javascript" src="lib/underscore-min.js"></script>
    <script type="text/javascript" src="lib/backbone.js"></script>
	
	<script type="text/javascript" src="lib/rsvp.min.js"></script>
    <script type="text/javascript" src="lib/basket.js"></script>
    
	<!--main dependencies-->
    <script type="text/javascript" src="lib/backbone.localStorage.js"></script>
	
    <script type="text/javascript" src="lib/fullcalendar.js"></script>
    <!--widgets-->
    <script language="javascript">
	basket
		.require(
			/*{ url: 'lib/fullcalendar.js', unique: "05/04/2015" },*/
			{ url: 'jscolor/jscolor.js' },
			{ url: 'lib/jquery.uploadifive.min.js' },
			{ url: 'lib/jquery.tablesorter.js' },
			{ url: 'lib/list.js' },
			{ url: 'lib/list.fuzzysearch.js' },
			{ url: 'lib/jquery.gridster.js' },
			{ url: 'lib/jquery.datetimepicker.js' },
			{ url: 'lib/jquery.timepicker.js' },
			{ url: 'lib/zipLookup.min.js' },
			{ url: 'lib/backbone.autocomplete.js' },
			{ url: 'lib/backbone.autocomplete_attorney.js' },
			{ url: 'lib/backbone.autocomplete_kase.js' },
			{ url: 'lib/backbone.autocomplete_worker.js' },
			{ url: 'lib/backbone.autocomplete_reps.js' },
			{ url: 'lib/backbone.autocomplete_specialty.js' },
			{ url: 'lib/jquery.ui.touch-punch.js' }
			
		)
		.then(function() {
        	//console.log("lib items loaded");
    	})
	;
	</script>
    <script type="text/javascript" src="lib/jquery.tokeninput.js"></script>
    <script async type="text/javascript" src="lib/jquery.datetimepicker.js"></script>
    <script async type="text/javascript" src="lib/jquery.timepicker.js"></script>
    <script async type="text/javascript" src="lib/zipLookup.min.js"></script>
    <!--autocomplete-->
    <script async type="text/javascript" src="lib/backbone.autocomplete.js"></script>
    <script async type="text/javascript" src="lib/backbone.autocomplete_attorney.js"></script>
    <script async type="text/javascript" src="lib/backbone.autocomplete_kase.js"></script>
    <script async type="text/javascript" src="lib/backbone.autocomplete_worker.js"></script>
	<script async type="text/javascript" src="lib/backbone.autocomplete_reps.js"></script>    
    <script async type="text/javascript" src="lib/backbone.autocomplete_specialty.js"></script>
	<script async type="text/javascript" src="lib/expanding.js"></script>
    
    <!--general utilities-->
    <script async type="text/javascript" src="lib/jquery.ui.touch-punch.js"></script>
    <script async type="text/javascript" src="lib/mobile-detect.min.js"></script>
    
    <script src="js/bootstrap-tab.js"></script>
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Latest compiled and minified JavaScript 
	<script type="text/javascript" src="lib/offline.min.js"></script>-->
    <script type="text/javascript" src="lib/moment.min.js"></script> 
    <script type="text/javascript" src="js/bootstrap.min.js"></script>
    <script src="lib/jquery-ui.min.1_10_3.js"></script>
    <!--<script async type="text/javascript" src="multilookup/src/jquery.tokeninput.js"></script>-->
    
    <script async type="text/javascript" src="cleditor/jquery.cleditor.js"></script>
    
    <script src="lib/tripledes.js"></script>
	<script src="lib/localforage.js"></script>
    
    <script src="js/utilities.js"></script>
    <script async src="js/mask_phone.js"></script>
    
    <script src="lib/jquery.jspanel.js"></script>
    
    <!--load templates-->
    <!--<script src="js/bootstrap-modal.js"></script>
    <script src="js/bootstrap-modalmanager.js"></script>-->
    
    
    <!--cookies-->
    <script src="js/cookies.js"></script>
    <script src="js/md5.js"></script>
    
    <?php 
		if ($blnDebug) {
			$time = microtime();
			$time = explode(' ', $time);
			$time = $time[1] + $time[0];
			$finish_time = $time;
			$total_time = round(($finish_time - $header_start_time), 4);
			
			$arrTiming[] = $total_time;
	$header_start_time = $time;
		}
		?>
    <!--models for data access-->
	<script src="js/models/accidentmodel.js"></script>
	<script src="js/models/activitymodel.js"></script>
	<script src="js/models/applicantmodel.js"></script>
    <script src="js/models/attorney_search_model.js"></script>    
    <script src="js/models/bodypartsmodel.js"></script>    
	<script src="js/models/calendarmodel.js"></script>
	<script src="js/models/chatsmodel.js"></script>
    <script src="js/models/checkmodel.js"></script>
	<script src="js/models/corporationmodel.js"></script>
    <script src="js/models/customersettingmodel.js"></script>
	<script src="js/models/documentmodel.js"></script>
	<script src="js/models/documentsearchmodel.js"></script>
	<script src="js/models/employeemodel.js"></script>
	<script src="js/models/emailmodel.js"></script>
    <script src="js/models/eams_carriers_model.js"></script>
    <script src="js/models/eams_formmodel.js"></script>
	<script src="js/models/eams_reps_model.js"></script>
    <script src="js/models/eventmodel.js"></script>
	<script src="js/models/exammodel.js"></script>
    <script src="js/models/feemodel.js"></script>
	<script src="js/models/home_medicalmodel.js"></script>
	<script src="js/models/injurymodel.js"></script>
    <script src="js/models/injurynumbermodel.js"></script>
	<script src="js/models/kasemodel.js"></script>
    <script src="js/models/lienmodel.js"></script>
    <script src="js/models/messagesmodel.js"></script>
	<script src="js/models/formsmodel.js"></script>
    <script src="js/models/newnotemodel.js"></script>
	<script src="js/models/notemodel.js"></script>
	<script src="js/models/partiemodel.js"></script>
    <script src="js/models/personmodel.js"></script>
    <script src="js/models/qmemodel.js"></script>
    <script src="js/models/rolodexmodel.js"></script>
    <script src="js/models/remindermodel.js"></script>
	<script src="js/models/scrapemodel.js"></script>
	<script src="js/models/settlementmodel.js"></script>
    <script src="js/models/signaturemodel.js"></script>
    <script src="js/models/specialty_model.js"></script>
    <script src="js/models/specialtiesmodel.js"></script>
	<script src="js/models/tasksmodel.js"></script>
	<script src="js/models/usermodel.js"></script>
    <script src="js/models/usersettingmodel.js"></script>
    <script src="js/models/vservicesmodel.js"></script>
    <script src="js/models/webmailmodel.js"></script>
    <script src="js/models/worker_search_model.js"></script>
    <?php 
		if ($blnDebug) {
			$time = microtime();
			$time = explode(' ', $time);
			$time = $time[1] + $time[0];
			$finish_time = $time;
			$total_time = round(($finish_time - $header_start_time), 4);
			
			$arrTiming[] = $total_time;
	$header_start_time = $time;
		}
		?>
    <!--views
    <script language="javascript" src="js/views/parties_details.js"></script>
    -->
	<script language="javascript">
	basket
		.require(
			{ url: 'js/views/person_details.js', unique: '05/04/2015' },
			{ url: 'js/views/parties_details.js', unique: '05/08/2015' },
			
			{ url: 'js/views/activity_listing.js', unique: '05/04/2015' },
			{ url: 'js/views/archive_listing.js', unique: '05/04/2015' },
			{ url: 'js/views/applicant_details.js', unique: '05/04/2015' },
			
			{ url: 'js/views/calendar_details.js', unique: '05/04/2015' },
			{ url: 'js/views/calendar_listing.js', unique: '05/04/2015' },
			{ url: 'js/views/check_details.js', unique: '05/04/2015' },
			
			{ url: 'js/views/costs_details.js', unique: '05/04/2015' },
			{ url: 'js/views/eams_scrape.js', unique: '05/04/2015' },
			
			{ url: 'js/views/event_listing.js', unique: '05/04/2015' },
			{ url: 'js/views/exam_details.js', unique: '05/04/2015' },
			{ url: 'js/views/fee_details.js', unique: '05/04/2015' },
			{ url: 'js/views/kase_nav_bar.js', unique: '05/04/2015' },
			{ url: 'js/views/kase_nav_left.js', unique: '05/04/2015' },
			{ url: 'js/views/kase_list_category.js', unique: '05/04/2015' },
			{ url: 'js/views/kase_home.js', unique: '05/04/2015' },
			
			{ url: 'js/views/kase_listing.js', unique: '05/04/2015' },
			{ url: 'js/views/partie_listing.js', unique: '05/04/2015' },
			{ url: 'js/views/kase_event_list.js', unique: '05/04/2015' },
			{ url: 'js/views/kase_details.js', unique: '05/04/2015' },
			{ url: 'js/views/kase_occurences.js', unique: '05/04/2015' },
			{ url: 'js/views/kase_control_panel.js', unique: '05/04/2015' },
			
			{ url: 'js/views/bodyparts_details.js', unique: '05/04/2015' },
			{ url: 'js/views/chat_details.js', unique: '05/04/2015' },
			{ url: 'js/views/customer_setting_listing.js', unique: '05/04/2015' },
			{ url: 'js/views/dashboard_view.js', unique: '05/04/2015' },
			{ url: 'js/views/dashboard_home_view.js', unique: '05/04/2015' },
			{ url: 'js/views/dashboard_injury_view.js', unique: '05/04/2015' },
			{ url: 'js/views/dashboard_person_view.js', unique: '05/04/2015' },
			{ url: 'js/views/dashboard_settlement_view.js', unique: '05/04/2015' },
			{ url: 'js/views/dashboard_user_view.js', unique: '05/04/2015' },
			{ url: 'js/views/dialog_details.js', unique: '05/04/2015' },
			{ url: 'js/views/document_details.js', unique: '05/04/2015' },
			{ url: 'js/views/document_import.js', unique: '05/04/2015' },
			{ url: 'js/views/document_search.js', unique: '05/04/2015' },
			{ url: 'js/views/document_upload.js', unique: '05/04/2015' },
			{ url: 'js/views/eams_form_attach.js', unique: '05/04/2015' },
			{ url: 'js/views/event_details.js', unique: '05/04/2015' },
			{ url: 'js/views/forms_listing.js', unique: '05/04/2015' },
			{ url: 'js/views/kai_details.js', unique: '05/04/2015' },
			{ url: 'js/views/lien_details.js', unique: '05/04/2015' },
			{ url: 'js/views/message_attach.js', unique: '05/04/2015' },
			{ url: 'js/views/messages_details.js', unique: '05/04/2015' },
			{ url: 'js/views/message_listing.js', unique: '05/04/2015' },
			{ url: 'js/views/new_note_details.js', unique: '05/04/2015' },
			{ url: 'js/views/email_details.js', unique: '05/04/2015' },
			{ url: 'js/views/home_medical_view.js', unique: '05/04/2015' },
			{ url: 'js/views/injury_details.js', unique: '05/04/2015' },
			{ url: 'js/views/injury_number_details.js', unique: '05/04/2015' },
			{ url: 'js/views/interoffice_details.js', unique: '05/04/2015' },
			{ url: 'js/views/letter_attach.js', unique: '05/04/2015' },
			{ url: 'js/views/letters_details.js', unique: '05/04/2015' },
			{ url: 'js/views/partie_cards.js', unique: '05/04/2015' },
			{ url: 'js/views/notes_details.js', unique: '05/04/2015' },
			{ url: 'js/views/rolodex_details.js', unique: '05/04/2015' },
			{ url: 'js/views/setting_attach.js', unique: '05/04/2015' },
			{ url: 'js/views/settlement_details.js', unique: '05/04/2015' },
			{ url: 'js/views/signature_details.js', unique: '05/04/2015' },
			{ url: 'js/views/stacks_details.js', unique: '05/04/2015' },
			{ url: 'js/views/task_listing.js', unique: '05/04/2015' },
			{ url: 'js/views/tasks_details.js', unique: '05/04/2015' },
			{ url: 'js/views/template_listing.js', unique: '05/04/2015' },
			{ url: 'js/views/template_upload.js', unique: '05/04/2015' },
			{ url: 'js/views/user_details.js', unique: '05/04/2015' },
			{ url: 'js/views/user_setting_listing.js', unique: '05/04/2015' },
			{ url: 'js/views/webmail_listing.js', unique: '05/04/2015' }
		)
		.then(function() {
        	//console.log("views loaded");
    	})
	;
	</script>
	<script src="js/views/accident_details.js"></script>
	<script src="js/views/activity_listing.js"></script>
    <script src="js/views/archive_listing.js"></script>
	<script src="js/views/applicant_details.js"></script>
	<script src="js/views/calendar_details.js"></script>
    <script src="js/views/calendar_listing.js"></script>
    <script src="js/views/check_details.js"></script>
	
    <script src="js/views/costs_details.js"></script>
    <script src="js/views/eams_scrape.js"></script>
	<script src="js/views/eams_forms_view.js"></script>
	<script src="js/views/event_listing.js"></script>
	<script src="js/views/exam_details.js"></script>
    <script src="js/views/fee_details.js"></script>
    <script src="js/views/kase_nav_bar.js"></script>
    <script src="js/views/kase_nav_left.js"></script>
    <script src="js/views/kase_list_category.js"></script>
    <script src="js/views/kase_home.js"></script>
	<script src="js/views/kase_listing.js"></script>
    <script src="js/views/kase_list_task.js"></script>
    <script src="js/views/partie_listing.js"></script>
    
	<script src="js/views/kase_event_list.js"></script>
    <script src="js/views/kase_details.js"></script>
    <script src="js/views/kase_occurences.js"></script>
	<script src="js/views/kase_control_panel.js"></script>
    
	
    <script src="js/views/bodyparts_details.js"></script>
    <script src="js/views/bulk_webmail_assign_details.js"></script>
    <script src="js/views/bulk_import_assign_details.js"></script>
	<script src="js/views/rental_details.js"></script>    
	<script src="js/views/chat_details.js"></script>
	<script src="js/views/customer_setting_listing.js"></script>
	<script src="js/views/dashboard_view.js"></script>
    <script src="js/views/dashboard_user_view.js"></script>
	<script src="js/views/dashboard_accident_view.js"></script>
    <script src="js/views/dashboard_home_view.js"></script>
	<script src="js/views/dashboard_injury_view.js"></script>
    <script src="js/views/dashboard_person_view.js"></script>
    <script src="js/views/dashboard_settlement_view.js"></script>
    <script src="js/views/dashboard_email_view.js"></script>
	<script src="js/views/dialog_details.js"></script>
    <script src="js/views/document_details.js"></script>    
    <script src="js/views/document_import.js"></script>
	<script src="js/views/document_search.js"></script>
    <script src="js/views/document_upload.js"></script>
    <script src="js/views/eams_form_attach.js"></script>        
    <script src="js/views/event_details.js"></script>        
    <script src="js/views/forms_listing.js"></script>
	<script src="js/views/kai_details.js"></script>
    <script src="js/views/lien_details.js"></script>
	<script src="js/views/medical_specialties_select.js"></script>
	<script src="js/views/message_attach.js"></script>
	<script src="js/views/messages_details.js"></script>
    <script src="js/views/message_listing.js"></script>
	<script src="js/views/multichat_view.js"></script>
	<script src="js/views/parties_details.js"></script>
	<script src="js/views/new_note_details.js"></script>
    <script src="js/views/email_details.js"></script>
    <script src="js/views/home_medical_view.js"></script>
	<script src="js/views/injury_details.js"></script>
    <script src="js/views/injury_number_details.js"></script>    
    <script src="js/views/interoffice_details.js"></script>
    <script src="js/views/letter_attach.js"></script>
	<script src="js/views/letters_details.js"></script>
	<script src="js/views/partie_cards.js"></script>
    <script src="js/views/person_details.js"></script>
    <script src="js/views/property_damage_details.js"></script>
	<script src="js/views/priors_details.js"></script>
	<script src="js/views/car_passenger_details.js"></script>
	<script src="js/views/notes_details.js"></script>
	<script src="js/views/accident_new_details.js"></script>
    <!--<script src="js/views/person_details.js"></script>-->
    <script src="js/views/rolodex_details.js"></script>
	<script src="js/views/search_qme.js"></script>
	<script src="js/views/setting_attach.js"></script>
    <script src="js/views/settlement_details.js"></script>
	<script src="js/views/setting_details.js"></script>
	<script src="js/views/signature_details.js"></script>
    <script src="js/views/stacks_details.js"></script>
    <script src="js/views/task_listing.js"></script>
	<script src="js/views/tasks_details.js"></script>
    <script src="js/views/template_listing.js"></script>
    <script src="js/views/template_upload.js"></script>
	<script src="js/views/user_details.js"></script>
    <script src="js/views/user_setting_listing.js"></script>
    <script src="js/views/vservice_view.js"></script>
    <script src="js/views/vservices_view.js"></script>

    <script src="js/views/webmail_listing.js"></script>
    
    <!--modules-->
    <script src="js/chat_module.js"></script>
    <script src="js/event_module.js"></script>
    <script src="js/kase_module.js"></script>
    <script src="js/phone_message_module.js"></script>
    <script src="js/setting_module.js"></script>
    
    <script src="text_editor/jquery-te-1.4.0.min.js"></script>
    <!--pagination-->
    <script async type="text/javascript" src="lib/jquery.tablesorter.pager.js"></script>
	
    <!--validation-->
    <script async type="text/javascript" src="lib/parsley.js"></script> 
    
    <?php 
	if ($blnDebug) {
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $header_start_time), 4);
		
		$arrTiming[] = $total_time;
	$header_start_time = $time;
	}
	?>
    
    <script language="javascript" type="text/javascript">
		var appHost = "<?php 
		$script_filename = $_SERVER['SCRIPT_FILENAME'];
		$arrScript = explode("/", $_SERVER['SCRIPT_FILENAME']);
		echo $arrScript[count($arrScript)-1]; 
		?>";
		
		//who is logged in

		var blnAdmin;
		<?php if ($_SESSION['user_role']=="admin" ) { ?>
			blnAdmin = true;
		<?php } ?>
		var dbname = '<?php echo $dbname; ?>'
		var customer_id = '<?php echo $_SESSION['user_customer_id']; ?>';
		var customer_name = '<?php echo $_SESSION['user_customer_name']; ?>';
		var customer_address = '';
		<?php if (isset($_SESSION['user_customer_address'])) { ?>
		var customer_address = '<?php echo $_SESSION['user_customer_address']; ?>';
		<?php } ?>
		
		var user_data_path = '';
		<?php if (isset($_SESSION['user_data_path'])) { ?>
		var user_data_path = '<?php echo $_SESSION['user_data_path']; ?>';
		<?php } ?>
		
		var login_user_id = '<?php echo $_SESSION['user_plain_id']; ?>';
		var login_username = '<?php echo addslashes($_SESSION['user_name']); ?>';
		var login_nickname = '<?php echo $_SESSION['user_nickname']; ?>';
		var blnIE = <?php if ($blnIE) { echo 1; } else { echo 0; } ?>;
		var subscription_string;
		
		<?php if (isset($_SESSION['subscription_string'])) { ?>
		subscription_string = '<?php echo $_SESSION['subscription_string']; ?>';
		<?php } ?>
		var hrefHost = '<?php echo $_SERVER['HTTP_HOST']; ?>';
		//bootstrapping background data, some of these need to be moved to indexedDB
       	
		//event types
		var setting_options = "<?php echo $setting_options; ?>";
		var calendar_filter = "<div id='calendar_filter' class='calendar_print' style='border:0px red solid; position:absolute; left:950px; top:25px; cursor:pointer; height:30px'><select id='event_typeFilter' class='modalInput event input_class' style='height:25px; width:110px;'><?php echo $setting_options; ?></select></div>";
		var calendar_print_button = "<div id='calendar_print' class='calendar_print' style='border:0px green solid; position:absolute; left:1070px; top:25px; cursor:pointer'><i class='glyphicon glyphicon-print' style='color:#9EFF05; font-size:2em'></i></div>";
		var calendar_list_button = "<div id='calendar_list' class='calendar_print white_text' style='border:0px green solid; position:absolute; left:1170px; top:25px; cursor:pointer'>LIST VIEW</div>";
		
		//customer settings
		var customer_settings = new Backbone.Model;
        customer_settings.set(<?php echo json_encode($arrSettings); ?>);
		//adhocs type
        var adhoc_settings = new Backbone.Collection;
        adhoc_settings.reset(<?php echo json_encode($adhoc_settings); ?>);
		
		//partie settings (color, blurb, etc)
        var partie_settings = new Backbone.Collection;
        partie_settings.reset(<?php echo json_encode($partie_settings); ?>);
		
		var customer_calendars = new Backbone.Collection;
		<?php if (is_array($customer_calendars)) { ?>
		customer_calendars.reset(<?php echo json_encode($customer_calendars); ?>);
		<?php } ?>
		//workers
		var worker_searches = new WorkerSearchCollection();
		worker_searches.reset(<?php echo json_encode($users); ?>);
		
		//one just for me
		var user_worker = worker_searches.findWhere({"id": login_user_id});
		
		//attorneys
		var attorney_searches = new AttorneySearchCollection();
		setTimeout(function(){
			attorney_searches.reset(<?php echo json_encode($attorneys); ?>);
		}, 1700);
		
		//eams carriers
		var eams_carriers = new EamsCarrierCollection();
		setTimeout(function() {
			eams_carriers.fetch();
		}, 2700);
		
		
		//eams reps
		var eams_reps = new EamsRepCollection();
		setTimeout(function() {
			eams_reps.fetch();
		}, 3100);
		
		<?php 
		if ($blnDebug) {
			$time = microtime();
			$time = explode(' ', $time);
			$time = $time[1] + $time[0];
			$finish_time = $time;
			$total_time = round(($finish_time - $header_start_time), 4);
			
			$arrTiming[] = $total_time;
	$header_start_time = $time;
		}
		?>
				
		//recent kases
		var recent_kases = new KaseRecentCollection();
		recent_kases.reset(<?php echo json_encode($kases); ?>);
		
		<?php 
		if ($blnDebug) {
			$time = microtime();
			$time = explode(' ', $time);
			$time = $time[1] + $time[0];
			$finish_time = $time;
			$total_time = round(($finish_time - $header_start_time), 4);
			
			$arrTiming[] = $total_time;
			$header_start_time = $time;
		}
		?>
		
		//recent kases
		var recent_tasks = new TaskRecentCollection();
		recent_tasks.reset(<?php echo json_encode($recent_tasks); ?>);
		<?php 
		if ($blnDebug) {
			$time = microtime();
			$time = explode(' ', $time);
			$time = $time[1] + $time[0];
			$finish_time = $time;
			$total_time = round(($finish_time - $header_start_time), 4);
			
			$arrTiming[] = $total_time;
			$header_start_time = $time;	
			
			//die("nic:1174");
		}
		?>
		
		//medical specialties
		var medical_specialties = new SpecialtySearchCollection();
		medical_specialties.reset(<?php echo json_encode($specialties); ?>);
		<?php 
		if ($blnDebug) {
			$time = microtime();
			$time = explode(' ', $time);
			$time = $time[1] + $time[0];
			$finish_time = $time;
			$total_time = round(($finish_time - $header_start_time), 4);
			
			$arrTiming[] = $total_time;
			$header_start_time = $time;	
		}
		?>
		//courts (venues)
		var venues = new Backbone.Collection();
		venues.reset(<?php echo json_encode($venues); ?>);
		<?php 
		if ($blnDebug) {
			$time = microtime();
			$time = explode(' ', $time);
			$time = $time[1] + $time[0];
			$finish_time = $time;
			$total_time = round(($finish_time - $header_start_time), 4);
			
			$arrTiming[] = $total_time;
	$header_start_time = $time;
		}
		?>
		//kases
		var kases = new KaseCollection();
		kases.reset(<?php echo json_encode($kases); ?>);
		blnKasesFetched = true;
		
		<?php 
		if ($blnDebug) {
			
			$time = microtime();
			$time = explode(' ', $time);
			$time = $time[1] + $time[0];
			$finish_time = $time;
			$total_time = round(($finish_time - $header_start_time), 4);
			
			$arrTiming[] = $total_time;
	$header_start_time = $time;
		}
		?>
		var dois = new InjuryCollection();
		dois.reset(<?php echo json_encode($dois); ?>);
		<?php 
		if ($blnDebug) {
			$time = microtime();
			$time = explode(' ', $time);
			$time = $time[1] + $time[0];
			$finish_time = $time;
			$total_time = round(($finish_time - $header_start_time), 4);
			
			$arrTiming[] = $total_time;
	$header_start_time = $time;
		}
		?>
		
		function showLeftSide() {
			$('#left_sidebar').show();
			$('#left_sidebar').css("margin-top", "65px")
			$("#search_results").removeClass("col-md-12");
			$("#search_results").addClass("col-md-10");
			$("#document_search").removeClass("col-md-12");
			$("#document_search").addClass("col-md-10");
			$("#ikase_loading").removeClass("col-md-12");
			$("#ikase_loading").addClass("col-md-10");
			
			$("#content").removeClass("col-md-12");
			$("#search_results").css("float", "right");
			$("#search_results").css("margin-top", "-560px");
			$("#search_results").css("margin-right", "20px");
			$("#kase_content").css("margin-top", "0px");
			var search_height = $("#search_results").height();
			var kase_content_height = $("#kase_content").height();
			$("#content").css("float", "right");
			//$("#kase_content").css("margin-top", "-445px");
			$("#content").css("margin-top", "-445px");
			if (!$('#search_results').height() > 1) { 
				$('#search_results').css("margin-top", "-560px");
				$("#content").css("margin-top", "-" + search_height + "px");
				//$("#kase_content").css("margin-top", "-" + search_height + "px");
				if (!$("#kase_content").height() == null) {
					$("#content").css("margin-top", "-" + (Number(search_height) - kase_content_height) + "px");
					//$("#kase_content").css("margin-top", "-" + ((Number(search_height) - 50) - kase_content_height) + "px");
				}
			} else {
				$("#content").css("margin-top", "-560px");
				//$("#kase_content").css("margin-top", "-" + search_height + "px");
				if (!$("#kase_content").height() == null) {
					$("#content").css("margin-top", "-445px");
					//$("#kase_content").css("margin-top", "-" + search_height + "px");
				}
			}
			$("#content").css("margin-right", "20px");
			$("#content").addClass("col-md-10");
			$("#left_side_show").hide();
			$("#left_side_hide").show();
		}
		function hideLeftSide() {
			$("#search_results").removeClass("col-md-10");
			$("#search_results").addClass("col-md-12");
			$("#content").removeClass("col-md-10");
			$("#search_results").css("float", "");
			$("#search_results").css("margin-top", "65px");
			$("#search_results").css("margin-right", "");
			$("#content").css("float", "");
			$("#content").css("margin-top", "0px");
			$("#content").css("margin-right", "");
			$("#content").addClass("col-md-12");
			$("#document_search").removeClass("col-md-10");
			$("#document_search").addClass("col-md-12");
			$("#ikase_loading").removeClass("col-md-10");
			$("#ikase_loading").addClass("col-md-12");
			
			$("#left_side_hide").hide();
			$("#left_side_show").show();
			$('#left_sidebar').hide();
			
		}
		function setEword(eword){
			var encrypted = CryptoJS.DES.encrypt(eword, "<?php echo $_SESSION["user_id"]; ?>");
			
			localforage.setItem('eword', encrypted.toString(), setEWordCallback);
		}
		var setEWordCallback = function() {
		}
    </script>
    <?php 
		if ($blnDebug) {
			$time = microtime();
			$time = explode(' ', $time);
			$time = $time[1] + $time[0];
			$finish_time = $time;
			$total_time = round(($finish_time - $header_start_time), 4);
			
			$arrTiming[] = $total_time;
	$header_start_time = $time;
		}
		?>
    <!--stored data -->
    <!--<script async language="javascript" src="data/eams_data.js"></script>-->
    
    <!--main app -->    
    <script src="js/app.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places"></script>
    <?php 
		if ($blnDebug) {
			$time = microtime();
			$time = explode(' ', $time);
			$time = $time[1] + $time[0];
			$finish_time = $time;
			$total_time = round(($finish_time - $header_start_time), 4);
			
			$arrTiming[] = $total_time;
	$header_start_time = $time;
		}
		?>
    <?php if (count($arrTiming) > 0) {
		echo "<div style='color:white'>";
		foreach($arrTiming as $key=>$timing) {
			echo $key . "-> " . $timing . "<br>";
		}
		echo "</div>";
	}
	?>
  </body>
</html>

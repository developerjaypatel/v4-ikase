<?php
//Set no caching
// header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
// header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
// header("Cache-Control: no-store, no-cache, must-revalidate"); 
// header("Cache-Control: post-check=0, pre-check=0", false);
// header("Pragma: no-cache");

setcookie('samesite-test', '1', 0, 'samesite=strict', 'secure');

$sixo = strtotime("2017-09-01 18:00:00");
$rightnow = mktime(date("H"), date("i"), date("s"), date("m")  , date("d"), date("Y"));
$blnOpen = true;

//if($_SERVER['REMOTE_ADDR']=='47.153.59.9') {
	if ($sixo < $rightnow) {
		//$blnOpen = false;
	}
//}
if (!$blnOpen) {
	die('<span style="background:yellow; color:black; padding:2px">09/01/2017 - iKase will be down for maintenance over the Labor Day Weekend.  We apologize for any inconvenience.</span>');
}
require_once('shared/legacy_session.php');
require_once('rootdata.php');

if($_SERVER["HTTPS"]=="off") {
	header("location:https://v2.ikase.org" . $_SERVER['REQUEST_URI']);
}

if ($_SESSION["need_password"]) {
	header("location:newpassword.php");
	die();
}

if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
	//die(print_r($_SESSION));
	header("location:index.php?cusid=-1");
	die();
}
//owners (and administrators?) are redirected
if ($_SESSION['user_customer_id']==-1 && $_SESSION['user_role']=="owner") {
	$url = "location:../manage/customers/";
	if (isset($_GET["session_id"])) {
		$url .= "index.php?session_id=" . $_GET["session_id"];
	}
	header($url);
	die();
}



/*

if ($_SESSION['user_customer_id']==1040) {
	die("Moheban iKase database is being updated 12/18/2017 8:00AM.  The system should be up by 9:30AM.  Thank you for your patience.");
}
*/

//die($_SESSION['subscription_string']);
//new window request
$blnNewWindow = (isset($_GET["n"]));

$header_start_time = 0;
$arrTiming = array();

include("api/connection.php");
include("browser_detect.php");

if(!empty($_GET['operation_']) && $_GET['operation_'] == 'delete-email' 
&& !empty($_GET['email_uuid']) && isset( $_SESSION["user_plain_id"]) 
&& isset($_SESSION["user_plain_id"])) {
	

	$user_plain_id1 = $_SESSION["user_plain_id"];
	$sql1 = "DELETE FROM ikase.cse_gmail WHERE user_id = ".$user_plain_id1."";
	$db1 = getConnection();
	$stmt1 = $db1->prepare($sql1); 
	$stmt1->execute();

	$sql = "UPDATE ikase.cse_user
	SET user_email = '' 
	WHERE user_uuid = :user_id";
	$db = getConnection();
	$stmt = $db->prepare($sql); 
	$stmt->bindParam("user_id", $_SESSION["user_plain_id"]); 
	$stmt->execute();
	
	$sql2= "DELETE FROM ikase.cse_email WHERE 
	email_uuid = :uuid and customer_id = :customerid";
	$db2= getConnection();
	$stmt2 = $db2->prepare($sql2); 
	$stmt2->bindParam("uuid", filter_var($_GET['email_uuid'], FILTER_SANITIZE_STRING)); 
	$stmt2->bindParam("customerid", $_SESSION['user_customer_id'] ); 
	$stmt2->execute();
	
	header("Location: v8.php#emailsettings");
	exit();
}

$gtoken = "";
if (isset($_GET["gtok"])) {
	$gtoken = passed_var("gtok", "get");
	//die(print_r($gtoken));
	
	//update the user gmail info
	//ALREADY DONE IN ikase.xyz/ikase/gmail/ui/index.php 
	//$url = "https://v2.ikase.org/api/gmail/settoken";
	try {
		/*
		$user_id = $_SESSION["user_plain_id"];
		
		$sql = "SELECT * FROM ikase.cse_gmail 
		WHERE user_id = :user_id";
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("user_id", $_SESSION["user_plain_id"]);
		$stmt->execute();
		$user = $stmt->fetchObject();
		
		if (!is_object($user)) {
			$sql = "INSERT INTO ikase.cse_gmail (user_id, token)
			VALUES('" . $user_id . "', :token)";
			$db = getConnection();
			$stmt = $db->prepare($sql);
			//$stmt->bindParam("user_id", $user_id);
			$stmt->bindParam("token", $gtoken);
			$stmt->execute();
		} else {
			$sql = "UPDATE ikase.cse_gmail 
			SET token = :token
			WHERE user_id = :user_id";
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("user_id", $user_id);
			$stmt->bindParam("token", $gtoken);
			$stmt->execute();
			
			
		}
		//die($user_id . "<br>" . $gtoken . "<br>" . $sql);
		*/
	} catch(PDOException $e) {
		$error = array("error1"=> array("text"=>$e->getMessage()));
		die(json_encode($error));
	}
}

if($blnMobile) {
	header("location:https://v2.ikase.org/mobilev1.php");
}

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
//FIXME: what's this supposed to do? it should always be true, unless it's running from CLI
if (isset($_SERVER['DOCUMENT_ROOT']) && $_SERVER['DOCUMENT_ROOT'] == $document_root_dir) {
	$dbname = "ikase";
	if (isset($_SESSION['user_data_source']) && $_SESSION['user_data_source'] != "") {
		$dbname .= "_" . $_SESSION['user_data_source'];
	}
}

//setup chat
$chat_dir = ROOT_PATH . '\\chats\\' . $_SESSION['user_customer_id'];
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


$kase_limit = "500";
$task_limit = "200";
if($_SESSION["user_customer_id"]==1064) {
	$kase_limit = "250";
	$task_limit = "100";
}



//kases
$sql_kases_count = "SELECT COUNT(ci.injury_id) injury_count
FROM cse_case ccase
INNER JOIN cse_case_injury cci
ON ccase.case_uuid = cci.case_uuid
INNER JOIN cse_injury ci
ON cci.injury_uuid = ci.injury_uuid
WHERE ccase.deleted = 'N'
AND ci.deleted = 'N'
AND ccase.case_status NOT LIKE '%close%' 
AND ccase.customer_id = :customer_id";

try {
	$db = getConnection();
	$stmt = $db->prepare($sql_kases_count);
	$stmt->bindParam("customer_id", $_SESSION["user_customer_id"]);
	$stmt->execute();
	$kase_counts = $stmt->fetchObject();
	//die(print_r($adhoc_settings));
} catch(PDOException $e) {
	$error = array("error1"=> array("text"=>$e->getMessage()));
	die(json_encode($error));
}


	$sql_kases = "SELECT DISTINCT 
			inj.injury_id id, '-1' `previous_kases`, '" . KASES_LIMIT . "' `start_kases`, ccase.case_id, ccase.lien_filed, ccase.special_instructions,ccase.case_description, inj.injury_number, ccase.case_uuid uuid, IF(ccase.case_number='UNKNOWN' AND ccase.file_number!='', '', ccase.case_number) case_number, ccase.file_number, ccase.filing_date, ccase.cpointer,ccase.source, 
			inj.adj_number, ccase.rating, ccase.injury_type, ccase.sub_in, inj.`type` main_injury_type,
			IF (DATE_FORMAT(ccase.case_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.case_date, '%m/%d/%Y')) `case_date`, 
			IF (DATE_FORMAT(ccase.terminated_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.terminated_date, '%m/%d/%Y')) `terminated_date`,
ccase.case_type, ccase.medical, ccase.td, ccase.rehab,  ccase.edd, ccase.claims,
			venue.venue_uuid, IF(venue.venue IS NULL, '', venue.venue) venue, venue.address1 venue_street, venue.address2 venue_suite, venue.city venue_city, venue.zip venue_zip, venue_abbr, ccase.case_status, ccase.case_substatus, ccase.case_subsubstatus, ccase.submittedOn, ccase.supervising_attorney,
    ccase.supervising_attorney,
    ccase.attorney, 
			IFNULL(superatt.nickname, '') as supervising_attorney_name, IFNULL(superatt.user_name, '') as supervising_attorney_full_name,
			ccase.worker, ccase.interpreter_needed, ccase.file_location, ccase.case_language `case_language`, 
			app.person_id applicant_id, app.person_uuid applicant_uuid, IFNULL(app.salutation, '') applicant_salutation,
			IF (app.first_name IS NULL, '', TRIM(app.first_name)) first_name , IF(app.last_name IS NULL, '', app.last_name) last_name, IFNULL(app.full_name, '') `full_name`, IFNULL(app.language, '') language,
			IF (DATE_FORMAT(app.dob, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(app.dob, '%m/%d/%Y')) dob, app.dob app_dob, 
			IF (app.ssn = 'XXXXXXXXX', '', app.ssn) ssn, IFNULL(app.ein, '') ein, IFNULL(app.email, '') applicant_email, IFNULL(app.phone, '') applicant_phone,IFNULL(app.full_address, '') applicant_full_address,
			IFNULL(employer.`corporation_id`,-1) employer_id, employer.`corporation_uuid` employer_uuid, IFNULL(employer.`company_name`, '') employer, employer.`full_address` employer_full_address, employer.`suite` `employer_suite`,
			IFNULL(defendant.`corporation_id`,-1) defendant_id, defendant.`corporation_uuid` defendant_uuid, defendant.`company_name` defendant, defendant.`full_address` defendant_full_address,
			IFNULL(plaintiff.`corporation_id`,-1) plaintiff_id, plaintiff.`corporation_uuid` plaintiff_uuid, plaintiff.`company_name` plaintiff, plaintiff.`full_address` plaintiff_full_address,
			IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')) start_date, 
			IF (DATE_FORMAT(inj.statute_limitation, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.statute_limitation, '%m/%d/%Y')) statute_limitation, 
			IF (DATE_FORMAT(inj.end_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.end_date, '%m/%d/%Y')) end_date, inj.ct_dates_note,
			IF (inj.occupation IS NULL, '' , inj.occupation) occupation,
			CONCAT(app.first_name,' ',app.last_name,' vs ', IFNULL(employer.`company_name`, ''),' - ', 
            REPLACE(IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')), '00/00/0000', '')) `name`, ccase.case_name, 
			att.user_id attorney_id, user.user_id, 
			IFNULL(att.nickname, '') as attorney_name, IFNULL(att.user_name, '') as attorney_full_name, 
			IFNULL(user.nickname, '') as worker_name, IFNULL(user.user_name, '') as worker_full_name,
			IFNULL(lien.lien_id, -1) lien_id, 
			IFNULL(settlement.settlement_id, IFNULL(settlementsheet.settlementsheet_id, -1)) settlement_id,
			IF (cif.fee_uuid IS NOT NULL, '1', '-1') fee_id, ccase.injury_type, ccase.sub_in,
			IFNULL(referring.`corporation_id`,-1) referring_id, referring.`corporation_uuid` referring_uuid, referring.`company_name` referring, referring.`full_address` referring_full_address, 			
			IFNULL(REPLACE(`referring`.`company_name`, ' ', ''), '') referring_search,
			IFNULL(jet.jetfile_id, '-1') jetfile_id,
			IFNULL(pi.personal_injury_date, '') personal_injury_date,
			IFNULL(pi.loss_date, '') personal_injury_loss_date,
			IFNULL(pi.statute_limitation, '') personal_statute_limitation,
			IFNULL(closed_cases.closed_date, '') closed_date
			FROM cse_case ccase  
			INNER JOIN (
				SELECT case_id 
                FROM cse_case ccase
                WHERE 1
                AND deleted = 'N'
				AND INSTR(ccase.case_status, 'Closed') = 0
				AND INSTR(ccase.case_status, 'Dropped') = 0
				AND INSTR(ccase.case_status, 'REJECTED') = 0
				AND INSTR(ccase.case_status, 'Intake') = 0
				AND customer_id = 1121
			) climit
            ON ccase.case_id = climit.case_id
			
			LEFT OUTER JOIN (
				SELECT cct.case_id, cct.case_status, MIN(time_stamp) closed_date
				FROM cse_case_track cct
				WHERE (case_status LIKE '%close%' OR case_status LIKE 'CLO%' OR case_status = 'DROPPED') 
				AND operation = 'update'
				GROUP BY cct.case_id
            ) closed_cases
            ON ccase.case_id = closed_cases.case_id
			
			LEFT OUTER JOIN `cse_personal_injury` pi 
			ON ccase.case_id = pi.case_id AND pi.deleted = 'N'
	
			LEFT OUTER JOIN `cse_case_corporation` rcorp
			ON (ccase.case_uuid = rcorp.case_uuid AND rcorp.attribute = 'referring' AND rcorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` referring
			ON rcorp.corporation_uuid = referring.corporation_uuid
			
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N'
			LEFT OUTER JOIN ";
			if ($_SESSION["user_customer_id"]==1033) { 
				$sql_kases .= "(" . SQL_PERSONX . ")";
			} else {
				$sql_kases .= "cse_person";
			}
			
			//$sql .= "cse_person";
			$sql_kases .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `ikase`.`cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			LEFT OUTER JOIN `cse_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` employer
			ON (ccorp.corporation_uuid = employer.corporation_uuid AND employer.deleted = 'N')
			
			LEFT OUTER JOIN `cse_case_corporation` ecorp
			ON (ccase.case_uuid = ecorp.case_uuid AND ecorp.attribute = 'defendant' AND ecorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` defendant
			ON (ecorp.corporation_uuid = defendant.corporation_uuid AND defendant.deleted = 'N')
			LEFT OUTER JOIN `cse_case_corporation` pcorp
			ON (ccase.case_uuid = pcorp.case_uuid AND pcorp.attribute = 'plaintiff' AND pcorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` plaintiff
			ON (pcorp.corporation_uuid = plaintiff.corporation_uuid AND plaintiff.deleted = 'N')
			
			INNER JOIN `cse_case_injury` cinj
			ON ccase.case_uuid = cinj.case_uuid AND cinj.deleted = 'N' AND cinj.`attribute` = 'main'
			INNER JOIN `cse_injury` inj
			ON cinj.injury_uuid = inj.injury_uuid AND inj.deleted = 'N'
			LEFT OUTER JOIN `cse_injury_lien` cil
			ON inj.injury_uuid = cil.injury_uuid
			LEFT OUTER JOIN `cse_injury_fee` cif ON inj.injury_uuid = cif.injury_uuid AND cif.deleted = 'N'
			LEFT OUTER JOIN `cse_jetfile` jet
			ON inj.injury_uuid = jet.injury_uuid
			LEFT OUTER JOIN `cse_lien` lien
			ON cil.lien_uuid = lien.lien_uuid
			LEFT OUTER JOIN `cse_injury_settlement` cis
			ON inj.injury_uuid = cis.injury_uuid
			LEFT OUTER JOIN `cse_settlement` settlement
			ON cis.settlement_uuid = settlement.settlement_uuid
			
            LEFT OUTER JOIN `cse_settlementsheet` settlementsheet
			ON cis.settlement_uuid = settlementsheet.settlementsheet_uuid
			
			LEFT OUTER JOIN ikase.`cse_user` superatt
			ON ccase.supervising_attorney = superatt.user_id
			LEFT OUTER JOIN ikase.`cse_user` att
			ON ccase.attorney = att.user_id
			LEFT OUTER JOIN ikase.`cse_user` user
			ON ccase.worker = user.user_id
			
			WHERE ccase.deleted ='N' 
			AND ccase.customer_id = " . $_SESSION['user_customer_id'] . " 
			ORDER BY 
			 TRIM(IFNULL(
				CONCAT(app.first_name,
				' ',
				app.last_name,
				' vs ',
				IFNULL(employer.`company_name`, ''),
				' - ',
				REPLACE(IF(DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL,
						'',
						DATE_FORMAT(inj.start_date, '%m/%d/%Y')),
					'00/00/0000',
					'')),
				ccase.case_name)),
			ccase.case_id, inj.injury_number 
			LIMIT 0, " . KASES_LIMIT;
/*
			IFNULL(IF (TRIM(IFNULL(app.first_name, '')) = '', IFNULL(TRIM(app.full_name), ccase.case_name), TRIM(app.first_name)), IFNULL(plaintiff.`company_name`, '')), 
			ccase.case_id, inj.injury_number
*/

$_SESSION["current_kase_query"] = $sql_kases;

$sql_injury = "SELECT DISTINCT inj.*, inj.injury_id id, inj.injury_uuid uuid, ccase.case_id,
		IFNULL(lien.lien_id, -1) lien_id, 
		IFNULL(settlement.settlement_id, IFNULL(settlementsheet.settlementsheet_id, -1)) settlement_id,
		IF (cif.fee_uuid IS NOT NULL, '1', '-1') fee_id,
		IFNULL(main_case_id, ccase.case_id) `main_case_id`, IFNULL(main_case_uuid, ccase.case_uuid) `main_case_uuid`,
		IFNULL(main_case_number, IF(ccase.case_number='', ccase.file_number, ccase.case_number)) `case_number`,
        ccase.file_number,
		IFNULL(ven.venue_uuid, '') venue_uuid, IFNULL(ven.venue, '') venue, IFNULL(ven.venue_abbr, '') venue_abbr
		FROM `cse_injury` inj 
		LEFT OUTER JOIN `cse_injury_lien` cil
		ON inj.injury_uuid = cil.injury_uuid
		LEFT OUTER JOIN `cse_injury_fee` cif ON inj.injury_uuid = cif.injury_uuid AND cif.deleted = 'N'
		LEFT OUTER JOIN `cse_lien` lien
		ON cil.lien_uuid = lien.lien_uuid
		LEFT OUTER JOIN `cse_injury_settlement` cis
		ON inj.injury_uuid = cis.injury_uuid AND cis.deleted = 'N'
		LEFT OUTER JOIN `cse_settlement` settlement
			ON cis.settlement_uuid = settlement.settlement_uuid
			LEFT OUTER JOIN `cse_settlementsheet` settlementsheet
			ON cis.settlement_uuid = settlementsheet.settlementsheet_uuid	
		INNER JOIN cse_case_injury ccinj
		ON inj.injury_uuid = ccinj.injury_uuid AND ccinj.`deleted` = 'N'
		INNER JOIN cse_case ccase
		ON ccinj.case_uuid = ccase.case_uuid ";

if (isset($_SESSION["restricted_clients"])) {
	$restricted_clients = $_SESSION["restricted_clients"];
	
	if ($restricted_clients!="") {
		//$restricted_clients = "'" . str_replace(",", "','", $restricted_clients) . "'";
		$sql_injury .= " INNER JOIN (
				SELECT DISTINCT ccorp.case_uuid
				FROM cse_case_corporation ccorp
				INNER JOIN cse_corporation corp
				ON ccorp.corporation_uuid = corp.corporation_uuid
				where corp.parent_corporation_uuid IN (" . $restricted_clients . ")
			) restricteds
			ON ccase.case_uuid = restricteds.case_uuid";
	}
}

$sql_injury .= " 
		
		INNER JOIN (
			SELECT case_id 
			FROM cse_case 
			WHERE case_status != 'Closed'
			AND case_status != 'Closed by C & R'
			AND case_status != 'Closed by Stipulation'
			AND deleted ='N' 
			AND customer_id = " . $_SESSION['user_customer_id'] . "
			ORDER BY case_date DESC 
			LIMIT 0, " . $kase_limit . "
		) climit
		ON ccase.case_id = climit.case_id";
		
$sql_injury .= " LEFT OUTER JOIN (
			SELECT ccinj.attribute, ccasemain.case_uuid main_case_uuid, ccasemain.case_id main_case_id, 
			ccasemain.case_number main_case_number, 
			ccinj.case_uuid related_case_uuid, ccase.case_id related_case_id, inj.*   
			FROM `cse_injury` inj 
			
			INNER JOIN cse_case_injury ccinj
			ON inj.injury_uuid = ccinj.injury_uuid AND ccinj.`deleted` = 'N' AND ccinj.attribute = 'related'
			INNER JOIN cse_case ccase
			ON ccinj.case_uuid = ccase.case_uuid
			
			INNER JOIN cse_case_injury ccmain
			ON inj.injury_uuid = ccmain.injury_uuid AND ccmain.`deleted` = 'N' AND ccmain.attribute = 'main'
			INNER JOIN cse_case ccasemain
			ON ccmain.case_uuid = ccasemain.case_uuid
			WHERE ccasemain.deleted = 'N'
        ) maininjury
        ON inj.injury_uuid = maininjury.injury_uuid
								
		LEFT OUTER JOIN `cse_injury_venue` iven
		ON inj.injury_uuid = iven.injury_uuid AND iven.deleted = 'N'
		
		LEFT OUTER JOIN `ikase`.`cse_venue` ven
		ON iven.venue_uuid = ven.venue_uuid";	
		
$sql_injury .= " WHERE 1
		AND inj.customer_id = " . $_SESSION['user_customer_id'] . "
		AND ccase.deleted = 'N'
		AND ccase.case_status NOT LIKE '%close%'
		AND inj.deleted = 'N'
		ORDER BY main_case_id, inj.injury_number ASC";

$_SESSION["dois_sql"] = $sql_injury;

//done writing to session object, release it
session_write_close();

//get the list of adhocs
$adhoc_settings = array();
$sql = "SELECT `adhoc_id`, `adhoc_uuid`, `adhoc`, `type`, `acceptable_values`, `default_value`, `format`, `deleted` 
FROM `cse_adhoc` 
WHERE 1
ORDER BY adhoc ASC";

try {
	$db = getConnection();
		
	$stmt = $db->query($sql);
	$adhoc_settings = $stmt->fetchAll(PDO::FETCH_OBJ);
	//die(print_r($adhoc_settings));
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
	$db = getConnection();
		
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
	$default_value = $setting_info->default_value;
	if ($setting_value=="" && $default_value!="") {
		$setting_value = $default_value;
	}
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
		AND deleted = 'N'
        ORDER by `setting`";
		
try {
	$db = getConnection();
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

$setting_options = "<option value=''>Filter By Type</option>";

foreach($event_types as $event_type) {
	$setting_id = $event_type->setting_id;
	$setting = $event_type->setting;
	
	$option = "<option value='" . $setting . "'>" . ucwords(str_replace("_", " ", $setting)) . "</option>";
	$setting_options .= "" . $option;
}

$setting_options .= "<option style='font-size: 1pt; background-color: #999999;' disabled>&nbsp;</option><option value='case_type_wc'>WC</option><option value='case_type_pi'>PI</option>";
if (strpos($_SESSION['user_role'], "admin") !== false) {
	$filter_option = "<option style='font-size: 1pt; background-color: #000000;' disabled>&nbsp;</option><option value='new_filter'>Manage List</option>";
	$setting_options .= $filter_option;
}

$customer_calendars = null;
$sql = "SELECT *
		FROM `" . $dbname . "`.cse_calendar 
		WHERE 1
		AND customer_id = '" . $_SESSION['user_customer_id'] . "'
		ORDER by sort_order";
		//die($sql);
try {
	$db = getConnection();
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
	$db = getConnection();
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
$sql = "SELECT user.user_id, user.user_uuid, user.user_name, user.user_logon, user.user_first_name, user.user_last_name, user.nickname, `user`.user_email, `user`.`dateandtime`, `user`.status, `user`.access_token, `user`.user_type, IF(`user`.user_type='1', 'admin', 'user') `role`, `user`.user_id id, `user`.user_uuid uuid, job.job_id, job.job_uuid, if(job.job IS NULL, '', job.job) job, `user`.`activated`, `user`.`calendar_color`
		FROM ikase.`cse_user` user 
		LEFT OUTER JOIN ikase.`cse_user_job` cjob
		ON (user.user_uuid = cjob.user_uuid AND cjob.deleted = 'N')
		LEFT OUTER JOIN ikase.`cse_job` job
		ON cjob.job_uuid = job.job_uuid
		WHERE user.deleted = 'N'
		AND user.customer_id = " . $_SESSION['user_customer_id'] . "
		ORDER by user.nickname";
		
//revised less data 1/23/2019
$sql = "SELECT user.user_id, user.user_name, user.user_first_name, user.user_last_name, user.nickname, `user`.user_email, `user`.`dateandtime`, `user`.status,`user`.user_type, IF(`user`.user_type='1', 'admin', 'user') `role`, `user`.user_id id, job.job_id, if(job.job IS NULL, '', job.job) job, `user`.`activated`, `user`.`calendar_color`
		FROM ikase.`cse_user` user 
		LEFT OUTER JOIN ikase.`cse_user_job` cjob
		ON (user.user_uuid = cjob.user_uuid AND cjob.deleted = 'N')
		LEFT OUTER JOIN ikase.`cse_job` job
		ON cjob.job_uuid = job.job_uuid
		WHERE user.deleted = 'N'
		AND user.customer_id = " . $_SESSION['user_customer_id'] . "
		ORDER by user.nickname";
try {
	$db = getConnection();
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
	$db = getConnection();
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

if ($blnDebug) {
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	$arrTiming[] = $total_time;
	$header_start_time = $time;
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
//medical specialties
$sql = "SELECT `specialty_id`, `specialty`, `description` FROM `cse_specialties` WHERE 1";
try {
	$db = getConnection();
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
$sql = "SELECT * FROM `ikase`.`cse_venue`   
WHERE 1 AND deleted!=1
ORDER BY venue ASC";
try {
	$db = getConnection();
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

//sql_kases
try {
	if (!$blnNewWindow) {
		$db = getConnection();
		$stmt = $db->query($sql_kases);
		
		$kases = $stmt->fetchAll(PDO::FETCH_OBJ);
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
		inj.injury_id id, ccase.case_id, inj.injury_number, ccase.case_uuid uuid, IF(ccase.case_number='UNKNOWN' AND ccase.file_number!='', '', ccase.case_number) case_number, ccase.file_number, ccase.cpointer,inj.injury_number, inj.adj_number, ccase.rating, 
			IF (DATE_FORMAT(ccase.case_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.case_date, '%m/%d/%Y')) case_date , 
			ccase.case_type, ccase.medical, ccase.td, ccase.rehab,  ccase.edd, ccase.claims,
			venue.venue_uuid, IFNULL(venue.venue, '') venue, IFNULL(venue_abbr, '') venue_abbr, ccase.case_status, ccase.case_substatus, ccase.case_subsubstatus, ccase.submittedOn, ccase.supervising_attorney,
    ccase.attorney, ccase.worker, ccase.interpreter_needed, ccase.file_location, ccase.case_language `case_language`,  
			app.person_id applicant_id, app.person_uuid applicant_uuid,
			IF (app.first_name IS NULL, '', app.first_name) first_name , IF(app.last_name IS NULL, '', app.last_name) last_name, app.full_name, app.language,
			IF (DATE_FORMAT(app.dob, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(app.dob, '%m/%d/%Y')) dob, 
			IFNULL(IF (app.ssn = 'XXXXXXXXX', '', app.ssn), '') ssn, IFNULL(app.ein, '') ein,
			employer.`corporation_id` employer_id, employer.`corporation_uuid` employer_uuid, IFNULL(employer.`company_name`, '') employer, 
			IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')) start_date, 
			IF (DATE_FORMAT(inj.end_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.end_date, '%m/%d/%Y')) end_date, 
			IF (inj.occupation IS NULL, '' , inj.occupation) occupation,
			CONCAT(app.first_name,' ',app.last_name,' vs ', IFNULL(employer.`company_name`, ''),' - ', 
            REPLACE(IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')), '00/00/0000', '')) `name`
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

$sql .= " INNER JOIN (
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
LIMIT 0 , 15
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
			LEFT OUTER JOIN `ikase`.`cse_venue` venue
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
	$db = getConnection();
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

//Working Good Until Here



$sql = "SELECT * FROM `cse_task` 
		WHERE customer_id =  " . $_SESSION['user_customer_id'] . "
		AND deleted = 'N'
		ORDER BY task_id DESC
		LIMIT 0, " . $task_limit;
try {
	$db = getConnection();
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

/*echo 'Testing Progress - Halir 2';
die();

try {
	$dois = DB::select($sql_injury);
} catch(PDOException $e) {
	$error = array("error6"=> array("text"=>$e->getMessage()));
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


?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex,nofollow">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="img/favicon.jpg">

    <title>iKase - Legal Case Management System. Fast. Mobile</title>

    
    <link rel="stylesheet" type="text/css" href="css/offline-theme-chrome.css" />
    <link rel="stylesheet" type="text/css" href="css/uploadifive.css">
    
    <link rel='stylesheet' type='text/css' href='css/jquery.gridster.css' />
    <link rel='stylesheet' type='text/css' href='editable_select/source/jquery.editable-select.css' />
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
    
    <link rel='stylesheet' type='text/css' href='lib/fullcalendar-2.7.1/fullcalendar.css' />
    <!--
    <link rel='stylesheet' type='text/css' href='css/fullcalendar.css' />
	-->
    
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
    <!--<link rel='stylesheet' type='text/css' href='css/isotope-docs.css' />-->
    
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
    <link href="cssc/main.css" rel="stylesheet">

  	<style type="text/css">
	#content {
		margin-left:10px;
	}
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
	.site-footer {
		background:#FC0;
		border:orange 2px solid;
		position:fixed;
		bottom:10px;
		left:10px;
		z-index:9999;
	}
	.activity_listing td {
		font-size:1.3em;
	}
	.letter_listing td, .stack_activity_listing td {
		font-size:1.5em;
	}
	.activity_category {
		background:#1e4dd2;
		padding:1px;
	}
	#left_side_show {
		left:5px;
		top:40px;
	}
	#left_side_hide {
		left:5px;
		top:40px;
	}
	.navbar-inverse .navbar-brand {
		font-size:14px;
	}
	.dropdown-toggle {
		font-size:14px;
	}
	.marketing_menu {
		display:block
	}
	@media screen and (max-width: 1410px) {
		.marketing_menu {
			display:none
		}
	}
	</style>  
    <?php //if ($_SESSION['user_plain_id']=='1' || $_SERVER['REMOTE_ADDR']=='47.153.59.9') {
		if ($_SESSION['user_plain_id']=='2') { ?>
    <!--override styles.css -->
    <style>
	body {
		/*background-color:#5f5859;*/
		/*background:#1864AF;*/
		background:#13508c;
	}
	.navbar-inverse {
		background:linear-gradient(to bottom,#001A53 0,#13508c 100%);
	}
	.navbar-inverse .navbar-nav>.open>a, .navbar-inverse .navbar-nav>.open>a:hover, .navbar-inverse .navbar-nav>.open>a:focus {
		color: #fff;
		background-color: #13508c;
	}
	#content {
		margin-left:10px;
	}
	.navbar {
		border-bottom:0px solid #EDEDED;
	}
	.navbar-inverse .navbar-brand {
		font-size:1.2em;
	}
	.dropdown-toggle {
		font-size:1.2em;
	}
	.new_import_indicator {
		left:95px;
	}
	#left_side_show {
		left:0px;
		top:55px;
	}
	#left_side_hide {
		left:0px;
		top:55px;
	}
	table.tablesorter {
		background:#191919;
	}
	</style>
	<?php } ?>
	
	<!-- Solulab code sart 26-07-2019 -->
  	<!-- calender -->
	  <!-- <link href='css/calendar/css/core/main.css' rel='stylesheet' /> -->
	<link href='css/calendar/css/daygrid/main.css' rel='stylesheet' />
	<link href='css/calendar/css/timegrid/main.css' rel='stylesheet' />
	<link href='css/calendar/css/list/main.css' rel='stylesheet' />
    <!-- Custom styles-->
    <link href="css/calendar/css/my-style.css" rel="stylesheet">
    <link href="css/calendar/css/media.css" rel="stylesheet">
	<!-- Solulab code end 26-07-2019 -->

	<!-- For hover effect in full calendare -> upcoming event day/week view, start-->
<style>

    .popper,
    .tooltip1 {
      position: absolute;
      z-index: 9999;
      background: "white";
      color: black;
      width: 150px;
      border-radius: 3px;
      box-shadow: 0 0 2px rgba(0, 0, 0, 0.5);
      padding: 10px;
      text-align: left;
    }

    .style5 .tooltip1 {
      background: #1E252B;
      color: #FFFFFF;
      max-width: 200px;
      width: auto;
      font-size: .8rem;
      padding: .5em 1em;
    }

    .popper .popper__arrow,
    .tooltip1 .tooltip1-arrow {
      width: 0;
      height: 0;
      border-style: solid;
      position: absolute;
      margin: 5px;
    }

    .tooltip1 .tooltip1-arrow,
    .popper .popper__arrow {
      border-color: "";
    }

    .style5 .tooltip1 .tooltip1-arrow {
      border-color: #1E252B;
    }

    .popper[x-placement^="top"],
    .tooltip1[x-placement^="top"] {
      margin-bottom: 5px;
    }

    .popper[x-placement^="top"] .popper__arrow,
    .tooltip1[x-placement^="top"] .tooltip1-arrow {
      border-width: 5px 5px 0 5px;
      border-left-color: transparent;
      border-right-color: transparent;
      border-bottom-color: transparent;
      bottom: -5px;
      left: calc(50% - 5px);
      margin-top: 0;
      margin-bottom: 0;
    }

    .popper[x-placement^="bottom"],
    .tooltip1[x-placement^="bottom"] {
      margin-top: 5px;
    }

    .tooltip1[x-placement^="bottom"] .tooltip1-arrow,
    .popper[x-placement^="bottom"] .popper__arrow {
      border-width: 0 5px 5px 5px;
      border-left-color: transparent;
      border-right-color: transparent;
      border-top-color: transparent;
      top: -5px;
      left: calc(50% - 5px);
      margin-top: 0;
      margin-bottom: 0;
    }

    .tooltip1[x-placement^="right"],
    .popper[x-placement^="right"] {
      margin-left: 5px;
    }

    .popper[x-placement^="right"] .popper__arrow,
    .tooltip1[x-placement^="right"] .tooltip1-arrow {
      border-width: 5px 5px 5px 0;
      border-left-color: transparent;
      border-top-color: transparent;
      border-bottom-color: transparent;
      left: -5px;
      top: calc(50% - 5px);
      margin-left: 0;
      margin-right: 0;
    }

    .popper[x-placement^="left"],
    .tooltip1[x-placement^="left"] {
      margin-right: 5px;
    }

    .popper[x-placement^="left"] .popper__arrow,
    .tooltip1[x-placement^="left"] .tooltip1-arrow {
      border-width: 5px 0 5px 5px;
      border-top-color: transparent;
      border-right-color: transparent;
      border-bottom-color: transparent;
      right: -5px;
      top: calc(50% - 5px);
      margin-left: 0;
      margin-right: 0;
	}

	
	#email_signature > .MsoNormal > a{
		color : white !important;
	}


  </style>
  <!-- For hover effect in full calendare -> upcoming event day/week view, end-->
  
</head>
  
  <body>

    <!-- Wrap all page content here -->
    <div id="wrap">
	<a href="#" id="back-to-top" title="Back to top">
    	&uarr;
    </a>
      <!-- Fixed navbar -->
      <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
        <div class="container kase_header">
        </div>
      </div>
      <div style="width:100%; margin-left:auto; margin-right:auto; text-align:center; display:none" class="large_white_text" id="page_title">
      </div>
      <!-- Begin page content -->
      	<div style="vertical-align:top">
            <div class="container-fluid">
                <div class="row">
                    <div id="document_search" class="col-md-12"></div>
                    <div id="search_results" class="col-md-12"></div>
                    <div id="ikase_loading" class="col-md-12" style="top:60px"></div>
                </div>
            </div>
            <div class="container-fluid">
                <div class="row">
                	<div id="chat_panel_holder" class="col-md-12" style="top:100px"></div>
                    <div id="left_sidebar" class="col-md-2 sidebar left_sidebar"></div>         
                    <div id="content" class="col-md-12 kase_content"></div>
                </div>
            </div>
        </div>
        <div id="chat_holder">
            <div id="chat_bottom"></div>
            <div id="chat_box"></div>
        </div>
        <div id="email_feedback_holder" style="position:absolute; bottom:350px; border:0px solid red; display:none"></div>
        <div id="batchscan_feeback_holder" style="position:absolute; left:10px; bottom:350px; border:0px solid red; display:none">
        </div>
        <div id="document_feedback_holder" style="position:absolute; display:none">
        </div>
        <div id="kase_feedback_holder" style="position:absolute; left:10px; bottom:350px; border:0px solid red; display:none">
        </div>
        <div id="site-footer" style="display:none" class="site-footer">
        	<div id="statute_reminders_holder"></div>
            <div id="event_reminders_holder"></div>
        </div>
    </div>
    <!-- Modal -->
  	<div class="modal fade" id="myModal4" tabindex="-1" role="dialog" aria-labelledby="myModal4Label" aria-hidden="true" data-backdrop="static"  data-keyboard="false" style="">
    <div class="modal-dialog" style="opacity:1">
        <div class="modal-content">
          <div class="modal-header">
          	<input type="hidden" id="modal_type" value="">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            
            
            <div id="modal_save_holder" style="float:right; padding-right: inherit"></div>
            
            <div id="gifsave" style="float:right; display:none">
            	<i class="icon-spin4 animate-spin" style="font-size:1.5em"></i>
            </div>
            <h4 class="modal-title" id="myModalLabel" style="color:#FFFFFF;">Modal title</h4>
            <div id="modal_billing_holder"></div>
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
          <i class="icon-spin4 animate-spin" style="margin-left:-20px;"></i></div>
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
	
	<script type="text/javascript" src="lib/rsvp.js"></script>
    <script type="text/javascript" src="lib/basket.js"></script>
    
	<!--main dependencies-->
    <script type="text/javascript" src="lib/backbone.localStorage.js"></script>
	
    <!--widgets-->
    <script language="javascript">
	var dbname = '<?php echo $dbname; ?>'
	var customer_id = '<?php echo $_SESSION['user_customer_id']; ?>';
	var customer_name = '<?php echo $_SESSION['user_customer_name']; ?>';
	var current_session_id = '<?php echo $_SESSION['user']; ?>';
	
	var customer_address = '';
	<?php if (isset($_SESSION['user_customer_address'])) { ?>
	var customer_address = '<?php echo $_SESSION['user_customer_address']; ?>';
	<?php } ?>
	
	var customer_phone = '';
	<?php if (isset($_SESSION['user_customer_phone'])) { ?>
	var customer_phone = '<?php echo $_SESSION['user_customer_phone']; ?>';
	<?php } ?>
	
	
	var customer_email = '';
	<?php if (isset($_SESSION['user_customer_email'])) { ?>
	var customer_email = '<?php echo $_SESSION['user_customer_email']; ?>';
	<?php } ?>
	
	var customer_eams_no = '';
	<?php if (isset($_SESSION['customer_eams_no'])) { ?>
	var customer_eams_no = '<?php echo $_SESSION['customer_eams_no']; ?>';
	<?php } ?>
	
	var blnJetFile = false;
	<?php if (isset($_SESSION['user_jetfile_id'])) {
			if ($_SESSION['user_jetfile_id'] > 0) { ?>
	blnJetFile = true;
	<?php 
			}
		} ?>
	//does the client want billing prompt
	var blnCustomerBillingPermission = true;
	
	<?php if (isset($_SESSION['permissions_billing'])) { ?>
	blnCustomerBillingPermission = <?php if ( $_SESSION['permissions_billing']) { echo "true"; } else { echo "false"; }  ?>;
	<?php } ?>
	
	var user_data_path = '';
	<?php if (isset($_SESSION['user_data_path'])) { ?>
	var user_data_path = '<?php echo $_SESSION['user_data_path']; ?>';
	<?php } ?>
	
	var login_user_rate = '<?php echo $_SESSION['user_rate']; ?>';
	var login_user_id = '<?php echo $_SESSION['user_plain_id']; ?>';
	var login_username = '<?php echo addslashes($_SESSION['user_name']); ?>';
	var login_nickname = '<?php echo $_SESSION['user_nickname']; ?>';
	var login_today = '<?php echo date("Y-m-d"); ?>';
	var customer_data_source = '<?php echo $_SESSION['user_data_source']; ?>';
	var blnIE = <?php if ($blnIE) { echo 1; } else { echo 0; } ?>;
	var subscription_string;
	var subscription_bitly_link;
	var global_login_email;
	
	basket
		.require(
			{ url: 'jscolor/jscolor.js' },
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
			{ url: 'lib/backbone.autocomplete_specialty.js' }
		)
		.then(function() {
        	//console.log("lib items loaded");
    	})
	;
	</script>
    <script async type="text/javascript" src="lib/jqColorPicker.min.js"></script>
    <script async type="text/javascript" src="lib/jquery.datetimepicker.js"></script>
    <script async type="text/javascript" src="velocity-master/velocity.js"></script>
    <script async type="text/javascript" src="lib/jquery.timepicker.js"></script>
    <script async type="text/javascript" src="lib/zipLookup.min.js"></script>
    <script async type="text/javascript" src="editable_select/source/jquery.editable-select.min.js"></script>
    <!--tiny text editor -->
    <script async type="text/javascript" src="lib/external/jquery.hotkeys.js"></script>
    <script async type="text/javascript" src="lib/bootstrap-wysiwyg.js"></script>
    <!--autocomplete-->
    <script async type="text/javascript" src="lib/backbone.autocomplete.js"></script>
    <script async type="text/javascript" src="lib/backbone.autocomplete_attorney.js"></script>
    <script async type="text/javascript" src="lib/backbone.autocomplete_kase.js"></script>
    <script async type="text/javascript" src="lib/backbone.autocomplete_worker.js"></script>
	<script async type="text/javascript" src="lib/backbone.autocomplete_reps.js"></script>    
    <script async type="text/javascript" src="lib/backbone.autocomplete_specialty.js"></script>
    <script async type="text/javascript" src="lib/jquery.disableAutoFill.min.js"></script>
    
	<script async type="text/javascript" src="lib/expanding.js"></script>
    
    <!--read more functionality-->
    
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
    
    <script src="lib/localforage.js"></script>
    <script src="js/utilities.js?version=<?php echo $version; ?>"></script>
    <script src="js/crud.js?version=<?php echo $version; ?>"></script>
    
	<?php if (!$blnNewWindow) { ?>
    	<script src="lib/jquery.uploadifive.js?version=<?php echo $version; ?>"></script>
		<script src="lib/fullcalendar-2.7.1/fullcalendar.js"></script>
        <!--
        <script src="lib/fullcalendar.js"></script>
        -->
		<script src="lib/jquery.tokeninput.js"></script>
    	<script src="lib/tripledes.js"></script>
     	<script src="lib/jquery.jspanel.js"></script>
	 	<script src="js/cookies.js"></script>
	    <script src="js/md5.js"></script>
    <?php } else { ?>
    	<script async src="lib/jquery.uploadifive.js"></script>
		<script async src="lib/fullcalendar-2.7.1/fullcalendar.js"></script>
        <!--
    	<script async src="lib/fullcalendar.js"></script>
        -->
		<script async src="lib/jquery.tokeninput.js"></script>
    	<script async src="lib/tripledes.js"></script>
    	<script async src="lib/jquery.jspanel.js"></script>
	    <script src="js/cookies.js"></script>
    	<script async src="js/md5.js"></script>
    <?php } ?>
    <!--<script src="lib/isotope-docs.min.js"></script>-->
    <script async src="cleditor/jquery.cleditor.js"></script>
    <script async src="lib/clipboard.min.js"></script>
    <script async src="js/mask_phone.js"></script>
    <script async src="lib/get_barcode_from_image.js"></script>
    
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
	<script src="js/models/accidentmodel.js?version=<?php echo $version; ?>"></script>
    <script src="js/models/accountmodel.js?version=<?php echo $version; ?>"></script>
	<script src="js/models/activitymodel.js?version=<?php echo $version; ?>"></script>
    <script src="js/models/adjustmentsmodel.js?version=<?php echo $version; ?>"></script>
	<script src="js/models/applicantmodel.js?version=<?php echo $version; ?>"></script>
    <script src="js/models/attorney_search_model.js?version=<?php echo $version; ?>"></script>
    <script src="js/models/billingmodel.js?version=<?php echo $version; ?>"></script>    
    <script src="js/models/bodypartsmodel.js?version=<?php echo $version; ?>"></script>    
	<script src="js/models/calendarmodel.js?version=<?php echo $version; ?>"></script>
	<script src="js/models/chatsmodel.js?version=<?php echo $version; ?>"></script>
    <script src="js/models/checkmodel.js?version=<?php echo $version; ?>"></script>
    <script src="js/models/checkrequestmodel.js?version=<?php echo $version; ?>"></script>
    <script src="js/models/coamodel.js?version=<?php echo $version; ?>"></script>
	<script src="js/models/contactmodel.js?version=<?php echo $version; ?>"></script>
	<script src="js/models/corporationmodel.js?version=<?php echo $version; ?>"></script>
    <script src="js/models/customersettingmodel.js?version=<?php echo $version; ?>"></script>
	<script src="js/models/deductionsmodel.js?version=<?php echo $version; ?>"></script>
	<script src="js/models/documentmodel.js?version=<?php echo $version; ?>"></script>
	<script src="js/models/documentsearchmodel.js?version=<?php echo $version; ?>"></script>
	<script src="js/models/employeemodel.js?version=<?php echo $version; ?>"></script>
	<script src="js/models/emailmodel.js?version=<?php echo $version; ?>"></script>
    <script src="js/models/eams_carriers_model.js?version=<?php echo $version; ?>"></script>
    <script src="js/models/eams_formmodel.js?version=<?php echo $version; ?>"></script>
	<script src="js/models/eams_reps_model.js?version=<?php echo $version; ?>"></script>
    <script src="js/models/eventmodel.js?version=<?php echo $version; ?>"></script>
	<script src="js/models/exammodel.js?version=<?php echo $version; ?>"></script>
    <script src="js/models/feemodel.js?version=<?php echo $version; ?>"></script>
    <script src="js/models/financialmodel.js?version=<?php echo $version; ?>"></script>
	<script src="js/models/home_medicalmodel.js?version=<?php echo $version; ?>"></script>
	<script src="js/models/injurymodel.js?version=<?php echo $version; ?>"></script>
    <script src="js/models/injurynumbermodel.js?version=<?php echo $version; ?>"></script>
    <script src="js/models/jetfilemodel.js?version=<?php echo $version; ?>"></script>
	<script src="js/models/kasemodel.js?version=<?php echo $version; ?>"></script>
    <script src="js/models/kinvoicemodel.js?version=<?php echo $version; ?>"></script>
    <script src="js/models/lienmodel.js?version=<?php echo $version; ?>"></script>
    <script src="js/models/lostincomemodel.js?version=<?php echo $version; ?>"></script>
    <script src="js/models/messagesmodel.js?version=<?php echo $version; ?>"></script>
	<script src="js/models/formsmodel.js?version=<?php echo $version; ?>"></script>
    <script src="js/models/negotiationmodel.js?version=<?php echo $version; ?>"></script>
	<script src="js/models/newnotemodel.js?version=<?php echo $version; ?>"></script>
	<script src="js/models/newlegalmodel.js?version=<?php echo $version; ?>"></script>
    <script src="js/models/coamodel.js?version=<?php echo $version; ?>"></script>
	<script src="js/models/notemodel.js?version=<?php echo $version; ?>"></script>
	<script src="js/models/partiemodel.js?version=<?php echo $version; ?>"></script>
    <script src="js/models/personmodel.js?version=<?php echo $version; ?>"></script>
    <script src="js/models/personalinjurymodel.js?version=<?php echo $version; ?>"></script>
    <script src="js/models/qmemodel.js?version=<?php echo $version; ?>"></script>
    <script src="js/models/rolodexmodel.js?version=<?php echo $version; ?>"></script>
    <script src="js/models/remindermodel.js?version=<?php echo $version; ?>"></script>
    <script src="js/models/rxmodel.js?version=<?php echo $version; ?>"></script>
	<script src="js/models/scrapemodel.js?version=<?php echo $version; ?>"></script>
	<script src="js/models/settlementmodel.js?version=<?php echo $version; ?>"></script>
    <script src="js/models/signaturemodel.js?version=<?php echo $version; ?>"></script>
    <script src="js/models/specialty_model.js?version=<?php echo $version; ?>"></script>
    <script src="js/models/specialtiesmodel.js?version=<?php echo $version; ?>"></script>
	<script src="js/models/tasksmodel.js?version=<?php echo $version; ?>"></script>
	<script src="js/models/usermodel.js?version=<?php echo $version; ?>"></script>
    <script src="js/models/usersettingmodel.js?version=<?php echo $version; ?>"></script>
    <script src="js/models/vservicesmodel.js?version=<?php echo $version; ?>"></script>
    <script src="js/models/webmailmodel.js?version=<?php echo $version; ?>"></script>
    <script src="js/models/workhistorymodel.js?version=<?php echo $version; ?>"></script>
    <script src="js/models/worker_search_model.js?version=<?php echo $version; ?>"></script>
    <script src="js/models/workflowmodel.js?version=<?php echo $version; ?>"></script>
    
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
        <script language="javascript" src="js/views/parties_details.js?version=<?php echo $version; ?>"></script>
    <!--views
    
    -->
	<script language="javascript">
	basket
		.require(
			{ url: 'js/views/person_details.js', unique: '05/04/2015' },
			
			{ url: 'js/views/archive_listing.js', unique: '05/04/2015' },
			{ url: 'js/views/applicant_details.js', unique: '05/04/2015' },
			
			{ url: 'js/views/calendar_details.js', unique: '05/04/2015' },
			{ url: 'js/views/calendar_listing.js', unique: '05/04/2015' },
			
			{ url: 'js/views/costs_details.js', unique: '05/04/2015' },
			{ url: 'js/views/eams_scrape.js', unique: '05/04/2015' },
			
			{ url: 'js/views/event_listing.js', unique: '05/04/2015' },
			{ url: 'js/views/exam_details.js', unique: '05/04/2015' },
			{ url: 'js/views/fee_details.js', unique: '05/04/2015' },
			{ url: 'js/views/kase_nav_left.js', unique: '05/04/2015' },
			{ url: 'js/views/kase_list_category.js', unique: '05/04/2015' },
			{ url: 'js/views/kase_home.js', unique: '05/04/2015' },
			
			//{ url: 'js/views/kase_listing.js', unique: '05/04/2015' },
			{ url: 'js/views/partie_listing.js', unique: '05/04/2015' },
			{ url: 'js/views/kase_event_list.js', unique: '05/04/2015' },
			{ url: 'js/views/kase_details.js', unique: '05/04/2015' },
			{ url: 'js/views/kase_control_panel.js', unique: '05/04/2015' },
			
			{ url: 'js/views/bodyparts_details.js', unique: '05/04/2015' },
			{ url: 'js/views/chat_details.js', unique: '05/04/2015' },
			{ url: 'js/views/customer_setting_listing.js', unique: '11/14/2016' },
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
			{ url: 'js/views/new_note_details.js', unique: '05/04/2015' },
			{ url: 'js/views/email_details.js', unique: '05/04/2015' },
			{ url: 'js/views/home_medical_view.js', unique: '05/04/2015' },
			{ url: 'js/views/injury_details.js', unique: '05/04/2015' },
			{ url: 'js/views/injury_number_details.js', unique: '05/04/2015' },
			{ url: 'js/views/interoffice_details.js', unique: '05/04/2015' },
			{ url: 'js/views/letter_attach.js', unique: '05/04/2015' },
			{ url: 'js/views/letters_details.js', unique: '05/04/2015' },
			{ url: 'js/views/notes_details.js', unique: '05/04/2015' },
			{ url: 'js/views/rolodex_details.js', unique: '05/04/2015' },
			{ url: 'js/views/setting_attach.js', unique: '05/04/2015' },
			{ url: 'js/views/signature_details.js', unique: '05/04/2015' },
			{ url: 'js/views/task_listing.js', unique: '05/04/2015' },
			{ url: 'js/views/tasks_details.js', unique: '05/04/2015' },
			{ url: 'js/views/template_listing.js', unique: '05/04/2015' },
			{ url: 'js/views/template_upload.js', unique: '05/04/2015' },
			{ url: 'js/views/user_setting_listing.js', unique: '05/04/2015' },
			{ url: 'js/views/webmail_listing.js', unique: '05/04/2015' }
		)
		.then(function() {
        	//console.log("views loaded");
    	})
	;
	</script>
	<script src="js/views/accident_details.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/account_details.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/activity_details.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/adjustment_details.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/billing_details.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/personal_injury_details.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/work_history_view.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/activity_listing.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/archive_listing.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/applicant_details.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/batchscan_listing.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/billing_listing.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/bulk_date_change_details.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/calendar_details.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/calendar_listing.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/check_details.js?version=<?php echo $version; ?>"></script>
    
    <script src="js/views/checkrequest_details.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/contact.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/rate.js?version=<?php echo $version; ?>"></script>
	
    <script src="js/views/costs_details.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/deduction_details.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/eams_scrape.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/eams_forms_view.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/event_listing.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/exam_details.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/fee_details.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/lostincome_details.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/losses_details.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/jetfile_listing_view.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/kase_nav_bar2.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/kase_nav_left.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/kase_list_category.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/kase_home.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/kase_listing.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/kase_list_task.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/kinvoice_details.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/popup_details.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/partie_listing.js?version=<?php echo $version; ?>"></script>
    
	<script src="js/views/kase_event_list.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/kase_details.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/kase_occurences.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/kase_control_panel.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/search_kase_view.js?version=<?php echo $version; ?>"></script>
   
    <script src="js/views/personal_injury_image.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/bodyparts_details.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/financial_details.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/new_legal_details.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/bulk_webmail_assign_details.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/bulk_import_assign_details.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/rental_details.js?version=<?php echo $version; ?>"></script>    
	<script src="js/views/chat_details.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/customer_setting_listing.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/dashboard_related_cases_view.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/dashboard_view.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/dashboard_user_view.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/dashboard_accident_view.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/dashboard_home_view.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/dashboard_injury_view.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/dashboard_person_view.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/dashboard_settlement_view.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/dashboard_email_view.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/dialog_details.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/document_details.js?version=<?php echo $version; ?>"></script>    
    <script src="js/views/document_import.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/document_search.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/document_upload.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/eams_form_attach.js?version=<?php echo $version; ?>"></script>        
    <script src="js/views/event_details.js?version=<?php echo $version; ?>"></script>        
    <script src="js/views/forms_listing.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/kai_details.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/lien_details.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/medical_specialties_select.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/message_attach.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/messages_details.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/message_listing.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/thread_listing.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/multichat_view.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/parties_details.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/negotiation_details.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/new_note_details.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/email_details.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/home_medical_view.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/injury_details.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/injury_number_details.js?version=<?php echo $version; ?>"></script>    
    <script src="js/views/interoffice_details.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/letter_attach.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/letters_details.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/partie_cards.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/person_details.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/property_damage_details.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/priors_details.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/car_passenger_details.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/notes_details.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/accident_new_details.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/related_details.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/rolodex_details.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/rx_details.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/search_qme.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/setting_attach.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/settlement_details.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/setting_details.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/signature_details.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/stacks_details.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/task_listing.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/tasks_details.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/template_listing.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/template_upload.js?version=<?php echo $version; ?>"></script>
	<script src="js/views/user_details.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/user_setting_listing.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/vservice_view.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/vservices_view.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/coa_details.js?version=<?php echo $version; ?>"></script>

    <script src="js/views/webmail_listing.js?version=<?php echo $version; ?>"></script>
    <script src="js/views/workflow_sheet_details.js?version=<?php echo $version; ?>"></script>
    
    <!--modules-->
    <?php if (!$blnNewWindow) { ?>
		<script src="js/chat_module.js?version=<?php echo $version; ?>"></script>
        <script src="js/event_module.js?version=<?php echo $version; ?>"></script>
        <script src="js/kase_module.js?version=<?php echo $version; ?>"></script>
        <script src="js/phone_message_module.js?version=<?php echo $version; ?>"></script>
        <script src="js/setting_module.js?version=<?php echo $version; ?>"></script>
        <script src="js/coa_module.js?version=<?php echo $version; ?>"></script>
        <script src="js/batchscan_module.js?version=<?php echo $version; ?>"></script>
        <script src="js/document_module.js?version=<?php echo $version; ?>"></script>
    <?php } else { ?>
    	<script async src="js/chat_module.js?version=<?php echo $version; ?>"></script>
		<script async src="js/event_module.js?version=<?php echo $version; ?>"></script>
        <script async src="js/kase_module.js?version=<?php echo $version; ?>"></script>
        <script async src="js/phone_message_module.js?version=<?php echo $version; ?>"></script>
        <script async src="js/setting_module.js?version=<?php echo $version; ?>"></script>
        <script async src="js/coa_module.js?version=<?php echo $version; ?>"></script>
        <script async src="js/batchscan_module.js?version=<?php echo $version; ?>"></script>
        <script async src="js/document_module.js?version=<?php echo $version; ?>"></script>
    <?php } ?>
    <script src="text_editor/jquery-te-1.4.0.min.js"></script>
    <!--pagination-->
    <script async type="text/javascript" src="lib/jquery.tablesorter.pager.js"></script>
	
    <!--validation-->
    <script async type="text/javascript" src="lib/parsley.js"></script> 
    
	<!-- Google API Login & Drive -->
	<script async defer src="https://apis.google.com/js/api.js"></script>
	
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
    <script src="js/stopwatch.js?version=<?php echo $version; ?>"></script>
    <script language="javascript" type="text/javascript">
		var appHost = "<?php 
		$script_filename = $_SERVER['SCRIPT_FILENAME'];
		$arrScript = explode("/", $_SERVER['SCRIPT_FILENAME']);
		echo $arrScript[count($arrScript)-1]; 
		?>";
		
		//who is logged in

		var blnAdmin = false;
		
		<?php if ($_SESSION['user_role']=="admin" || $_SESSION['user_role']=="masteradmin") { ?>
			blnAdmin = true;
		<?php } ?>
		//kases search start and previous
		var kases_limit = "<?php echo KASES_LIMIT; ?>";
		//let's highlight the customer name
		setTimeout(function() {
			flashHello("nav_customer_name", true);
		}, 2000);
		
		var calendarContentHeight = 'auto';
		var login_ip_address = '<?php echo $_SERVER['REMOTE_ADDR']; ?>';
		
		writeCookie('sess_id', '<?php echo $_SESSION['user']; ?>', 60);
		writeCookie('logged_in_as', '<?php echo addslashes($_SESSION['user_name']); ?>', 60);
		writeCookie('user_name', '<?php echo $_SESSION['user_logon']; ?>', 60);		
		writeCookie('current_customer_id', '<?php echo $_SESSION['user_customer_id']; ?>', 60);	
			
		<?php if (isset($_GET["masterlogin"]) || isset($_GET["session_id"]) || isset($_GET["gtok"])) {
			// ?>
		window.history.pushState('v8', 'Welcome to iKase', '/v8.php');
		<?php } ?>
		
		<?php if (isset($_SESSION['subscription_string'])) { ?>
		subscription_string = '<?php echo $_SESSION['subscription_string']; ?>';
		subscription_bitly_link = '<?php echo make_bitly_url("https://v2.ikase.org/api/sync_calendar_kase.php?" . $_SESSION['subscription_string']); ?>';
		<?php } ?>
		var hrefHost = '<?php echo $_SERVER['HTTP_HOST']; ?>';
		//bootstrapping background data, some of these need to be moved to indexedDB
		
		//injury types
		var standard_injury_options = '<option value="">Choose One</option><option value="carpass">Car Accident</option><option value="general">General</option><option value="slipandfall">Slip and Fall</option><option value="dogbite">Dog Bite</option><option id="disability_kase_option" value="disability">Disability</option>';
		var standard_representing_options = '<option value="">Plaintiff or Defendant?</option><option id="plaintiff" value="plaintiff">Plaintiff</option><option id="defendant" value="defendant">Defendant</option>';
		<?php //if ($_SESSION["user_customer_id"]==1089) { ?>
		//per maria LG LAW CENTER, INC. 9/20/2017
		var immigration_injury_options = '<option value="">Choose One</option><option value="245">245</option><option value="adjust_status">Adjustment of Status</option><option value="asylum">Asylum</option><option value="consular_process">Consular Process</option><option value="citizenship">Citizenship</option><option value="employment_authorization">Employment Authorization</option><option value="permanent_residency_renewal">Permanent Residency Renewal</option>';
		var immigration_representing_options = '<option value="">Choose One</option><option value="petitioner">Petitioner</option><option value="beneficiary">Beneficiary</option><option value="plaintiff">Applicant</option><option value="respondent">Respondent</option>';
		<?php /*} else { ?>
		var immigration_injury_options = '<option value="">Choose One</option><option value="adjust_status">Adjustment of Status</option><option value="asylum">Asylum</option><option value="consular_process">Consular Process</option><option value="family_petition">Family Petition</option>';
		var immigration_representing_options = standard_representing_options;
		<?php }*/ ?>

       	//kases
		var kases = new KaseOpenCollection();
		//recent kases
		var recent_kases = new KaseRecentCollection();
		<?php if (!$blnNewWindow) { ?>
			var listed_kases = <?php echo count($kases); ?>;
			var kase_pages = <?php echo $kase_counts->injury_count; ?> / kases_limit;
			setTimeout(function(){
				var json_kases = <?php echo json_encode($kases); ?>;
				kases.set(json_kases);
				//recent_kases.set(json_kases);
				blnKasesFetched = true;
				
				recent_kases.fetch({
					success: function(data) {
						recent_kases = data;
					}
				});
			}, 1177);
		<?php } else { ?>
			var listed_kases = kases_limit;
			setTimeout(function() {
				kases.fetch({
					success: function (data) {
						listed_kases = data.length;
						//recent_kases = data;
					}
				});
				
				recent_kases.fetch({
					success: function(data) {
						recent_kases = data;
					}
				});
			}, 2711);
		<?php } ?>
		//event types
		var setting_options = "<?php echo $setting_options; ?>";
		
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
		var firm_calendar_id = "";
		<?php if (is_array($customer_calendars)) { ?>
		customer_calendars.reset(<?php echo json_encode($customer_calendars); ?>);
		var customer_firm_calendar =  customer_calendars.findWhere({"sort_order": "0"});
		if (typeof customer_firm_calendar != "undefined") {
			var customer_firm_calendar_id = customer_firm_calendar.get("calendar_id");
		}
		<?php } ?>
		//workers
		var worker_searches = new WorkerSearchCollection();
		worker_searches.reset(<?php echo json_encode($users); ?>);
		
		//one just for me
		var user_worker = worker_searches.findWhere({"id": login_user_id});
		
		//event assignee
		var arrEmployeeOptions = [];
		arrEmployeeOptions.push("<option value=''>Filter By Assignee</option>");
		worker_searches.forEach(function(element, index, array) { 
			var user_name = element.toJSON().user_name;
			var user_nickname = element.toJSON().nickname;
			if (user_name!="Matrix Admin" && user_name!="") {
				arrEmployeeOptions.push("<option value='" + user_nickname + "'>" + user_nickname + " - " + user_name.toLowerCase().capitalizeWords() + "</option>");
			}
		});

		//worker
		var arrWorkerOptions = [];
		arrWorkerOptions.push("<option value=''>Filter By Kase Coord</option>");
		var arrAttorneyOptions = [];
		arrAttorneyOptions.push("<option value=''>Filter By Kase Atty</option>");
		var arrUserNicks = [];
		var arrWorkers = [];
		var arrAttys = [];
		var arrUserAttys = [];
		setTimeout(function() {				
		//for(var i = 0; i < arrLength; i++) {
			kases.forEach(function(element, index, array) { 
				var theuser = element.toJSON();
				var user_name = theuser.worker_full_name;
				var user_nickname = theuser.nickname;
				if (user_name=="" && theuser.worker!="") {
					if (arrWorkers.indexOf(theuser.worker) < 0) {
						arrWorkers.push(theuser.worker);
						
						var the_worker = worker_searches.findWhere({nickname:theuser.worker});
						if (typeof the_worker != "undefined") {
							the_worker = the_worker.toJSON().user_name.toLowerCase().capitalizeWords();
							
							user_name = the_worker;
							user_nickname = theuser.worker;
						}
					}
				}
				if (user_name!="Matrix Admin" && user_name!="") {
					if (arrUserNicks.indexOf(user_name) < 0) {
						arrUserNicks.push(user_name);
						user_name = user_name.toLowerCase();
						user_name = user_name.capitalizeWords();
						
						arrWorkerOptions.push("<option value='" + user_nickname + "'>" + user_name + "</option>");
					}
				}
				
				var theuser = element.toJSON();
				var user_name = theuser.attorney_full_name;
				
				if (user_name=="" && theuser.attorney!="") {
					if (arrAttys.indexOf(theuser.attorney) < 0) {
						arrAttys.push(theuser.attorney);
						var the_worker = worker_searches.findWhere({nickname:theuser.attorney});
						if (typeof the_worker != "undefined") {
							the_worker = the_worker.toJSON().user_name.toLowerCase().capitalizeWords();
							
							user_name = the_worker;
							user_nickname = theuser.attorney;
						}
					}
				}
				if (user_name!="Matrix Admin" && user_name!="") {
					if (arrUserAttys.indexOf(user_name) < 0) {
						arrUserAttys.push(user_name);
						user_name = user_name.toLowerCase();
						user_name = user_name.capitalizeWords();
						
						arrAttorneyOptions.push("<option value='" + user_nickname + "'>" + user_name + "</option>");
					}
				}
			});
		}, 300);
		
		<?php //if ($_SERVER['REMOTE_ADDR']=='47.153.59.9') { echo "true"; } else { echo "false"; } ?>
		var blnDisplayOverdues = true;
		<?php if ($_SESSION["user_customer_id"]==1064) {	//per thomas 4/24/2018 ?>
		blnDisplayOverdues = false;
		<?php } ?>
		
		var blnPatient = false;
		<?php if ($_SESSION["user_customer_type"]=="Medical Office") { ?>
		blnPatient = true;
		<?php } ?>
		
		var blnTritekApplicant = false;
		<?php if ($_SESSION["user_customer_id"]=="1121") {
				//goldberg for now ?>
		blnTritekApplicant = true;
		<?php } ?>
		
		var blnNewWindow = <?php if ($blnNewWindow) { echo "true"; } else { echo "false"; } ?>;
		arrAttorneyOptions.sort();
		arrWorkerOptions.sort();
		/*
		//attorney drop  down
		var arrAttorneys = worker_searches.where({job: "Attorney"});
		var arrLength = arrAttorneys.length;
		
		//for(var i = 0; i < arrLength; i++) {
		arrAttorneys.forEach(function(element, index, array) { 
			var theattorney = element.toJSON();
			var attorney_name = theattorney.user_name;
			attorney_name = attorney_name.toLowerCase();
			attorney_name = attorney_name.capitalizeWords();
			arrAttorneyOptions.push("<option value='" + theattorney.nickname + "'>" + attorney_name + "</option>");
		});
		//attorneys
		*/
		var attorney_searches = new AttorneySearchCollection();
		setTimeout(function(){
			attorney_searches.reset(<?php echo json_encode($attorneys); ?>);
		}, 1700);
		var assignee_filter = "<select id='assigneeFilter' class='modalInput event input_class' style='height:25px; width:210px;'>" + arrEmployeeOptions.join("") + "</select>";
		var calendar_attorney_filter = "<div id='calendar_attorney_filter' class='calendar_print' style='border:0px red solid; position:absolute; left:350px; cursor:pointer; height:30px'>" + assignee_filter + "</div>";
		/*
		&nbsp;&nbsp;&nbsp;<select id='workerFilter' class='modalInput event input_class' style='height:25px; width:110px;'>" + arrWorkerOptions.join("") + "</select>&nbsp;&nbsp;&nbsp;<select id='attorneyFilter' class='modalInput event input_class' style='height:25px; width:110px;'>" + arrAttorneyOptions.join("") + "</select></div>";
		*/
		var blnBlockDays = false; //(login_user_id==2);
		var event_type_filter = "<select id='event_typeFilter' class='modalInput event input_class' style='height:25px; width:150px;'><?php echo $setting_options; ?></select>";
		
		//LOOK FOR THESE BUTTONS IN lib/fullcalendar-2.7.1/fullcalendar.js
		
		var calendar_filter = "<div id='calendar_filter' class='calendar_print' style='border:0px red solid; position:absolute; left:600px; cursor:pointer; height:30px'>" + event_type_filter + "</div>";
		var calendar_new_button = "<div id='calendar_new' class='calendar_print white_text' style='border:0px green solid; position:absolute; left:1200px; top:30px; cursor:pointer'>NEW EVENT</div>";
		var calendar_print_button = "<div id='calendar_print' class='calendar_print white_text' style='border:0px green solid; position:absolute; left:1070px; top:30px; cursor:pointer'>PRINT</div>";
		var calendar_list_button = "<div id='calendar_list' class='calendar_print white_text' style='border:0px green solid; position:absolute; left:1170px; top:30px; cursor:pointer'>LIST VIEW</div>";
		var calendar_block_button = "";
		if (blnBlockDays) {
			calendar_new_button = "<div id='calendar_new' class='btn btn-primary calendar_print white_text' style='border:0px green solid; position:absolute; left:1200px; top:30px; cursor:pointer'>NEW EVENT</div>";
			calendar_print_button = "<button id='calendar_print' class='btn btn-primary calendar_print white_text' style='position:absolute; left:1070px; top:30px'>PRINT</button>";
			calendar_list_button = "<button id='calendar_list' class='btn btn-info calendar_print white_text' style='position:absolute; left:1170px; top:30px'>LIST VIEW</button>";
			calendar_block_button = "<button id='calendar_block' class='btn btn-danger calendar_print white_text' style='position:absolute; left:1270px; top:30px' onclick='composeBlockedDates()' title='Click to block out calendar dates'>BLOCK</button>";
			//calendar_block_button += "<button id='calendar_unblock' class='btn btn-warning calendar_print white_text' style='position:absolute; left:1370px; top:30px; display:none' onclick='document.location.href=\"#blocked\";' title='Click to unblock out calendar dates'>UNBLOCK <span id='blocked_count'></span></button>";
			/*
			calendar_print_button = "<div id='calendar_print' class='calendar_print white_text' style='border:0px green solid; position:absolute; left:1070px; top:30px; cursor:pointer'><img src='img/gear.png' width='25' height='25'></div>";
			calendar_list_button = "";
			*/
		}
		
		
		
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
		<?php if (!$blnNewWindow) { ?>
			dois.reset(<?php echo json_encode($dois); ?>);
		<?php } else { ?>
			setTimeout(function() {
				dois.fetch({
					success: function (data) {
					}
				});
			}, 711);
		<?php } ?>
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
			$("#content").addClass("col-md-10");
			//$('#left_sidebar').css("margin-top", "65px")
			$('#left_sidebar').css("margin-top", $("#content").css("top"));
			
			
			$("#search_results").removeClass("col-md-12");
			$("#search_results").addClass("col-md-10");
			$("#document_search").removeClass("col-md-12");
			$("#document_search").addClass("col-md-10");
			$("#ikase_loading").removeClass("col-md-12");
			$("#ikase_loading").addClass("col-md-10");
			
			$("#left_side_show").hide();
			$("#left_side_hide").show();
			
			return;
			/*
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
			*/
		}
		function hideLeftSide() {
			$('#left_sidebar').hide();
			
			$("#content").addClass("col-md-12");
			$("#content").removeClass("col-md-10");
			
			$("#search_results").addClass("col-md-12");
			$("#search_results").removeClass("col-md-10");
			
			$("#document_search").addClass("col-md-12");
			$("#document_search").removeClass("col-md-10");
			$("#ikase_loading").addClass("col-md-12");
			$("#ikase_loading").removeClass("col-md-10");
			
			$("#left_side_hide").hide();
			$("#left_side_show").show();
			
			return;
			/*
			$("#content").removeClass("col-md-10");
			$("#search_results").css("float", "");
			$("#search_results").css("margin-top", "65px");
			$("#search_results").css("margin-right", "");
			$("#content").css("float", "");
			$("#content").css("margin-top", "0px");
			$("#content").css("margin-right", "");
			$("#content").addClass("col-md-12");
			
			
			
			$("#left_side_hide").hide();
			$("#left_side_show").show();
			$('#left_sidebar').hide();
			*/
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
	<script src="js/check_boxes.js?version=<?php echo $version; ?>"></script>
    <script async src="js/compose_modal.js?version=<?php echo $version; ?>"></script>
    <script async src="js/save_modal.js?version=<?php echo $version; ?>"></script>
    <script async src="js/workflow.js?version=<?php echo $version; ?>"></script>
    <script src="js/app.js?version=<?php echo $version; ?>"></script>
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
    <?php if ($_SESSION["user_customer_id"]==1033 || $_SESSION["user_customer_id"]==1107 || $_SESSION["user_customer_id"]==1205) { ?>
    <div id="list_email_messages" style="display:none; z-index:9595; position:absolute">
    <!--<iframe src='https://www.ikase.xyz/ikase/gmail/ui/get_messages.php?user_id=<?php echo $_SESSION["user_plain_id"]; ?>&customer_id=<?php echo $_SESSION["user_customer_id"]; ?>&user_name=<?php echo urlencode($_SESSION['user_name']); ?>'></iframe>-->
    </div>
    
    <iframe id="check_gmail_messages" frameborder="0" width="10px" height="10px" style="display: none;"></iframe>
    <!--
    <script type="text/javascript">
	var gmail_token = "";
	function receiveToken(token) {
		gmail_token = token;
		
		if (token=="") {
			var gmail_url = "https://www.ikase.xyz/ikase/gmail/ui/index.php";
			gmail_url += "?user_id=" + login_user_id + "&customer_id=" + customer_id + "&user_name=" + login_username + "&email=" + email_json.email_name + "&destination=" + email_json.email_address + "&hash=" + document.location.hash.substr(1);
			
			document.location.href = gmail_url;
		}
	}
	</script>
    -->
    <?php } ?>
    <textarea id="clipboard_info" style="display:none"></textarea>
  </body>
</html>

<?php 
error_reporting(E_ALL);
error_reporting(error_reporting(E_ALL) & (-1 ^ E_DEPRECATED));

ini_set('SMTP','localhost'); 
ini_set('sendmail_from', 'admin@ikase.website'); 

require_once('../shared/legacy_session.php');
session_write_close();

if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
	header("location:../index.php");
	die();
}

ob_start();

include("../api/connection.php");
include ("../text_editor/ed/datacon.php");

//include("../api/email_message.php");

$case_id = passed_var("case_id");
$injury_id = passed_var("injury_id");
$emailit = passed_var("emailit");
$specific_instructions = passed_var("specific_instructions");

$doi_injury_id = "";
if ($injury_id!="") {
	$arrInjuryIDs = explode("|", $injury_id);
	//$doi_injury_id = $injury_id;
}

//die(print_r($_SERVER));
if ($emailit=="") {
	$emailit = "N";
}
if (!is_numeric($case_id)) {
	die();
}

if ($emailit=="y") {
	include("uploadifive.php");
}

$sql_customer = "SELECT *
FROM  `ikase`.`cse_customer` 
WHERE customer_id = :customer_id";
try {
	$db = getConnection();
	$stmt = $db->prepare($sql_customer);
	$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
	$stmt->execute();
	$customer = $stmt->fetchObject();
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage(), "sql"=>$sql));
		echo json_encode($error);
}

$query = "SELECT pers.*, ccase.*, ccpers.*, cinj.full_address injury_full_address, cinj.start_date, cinj.end_date, cinj.explanation, cinj.occupation, cven.venue_abbr case_venue, 
employer.company_name employer, employer.full_address employer_address, ccase.adj_number, employer.employee_phone employer_phone, employer.employee_fax employer_fax, employer.email employer_email, employer.company_site employer_site
FROM cse_case ccase
INNER JOIN cse_case_person ccpers
ON ccase.case_uuid = ccpers.case_uuid AND ccpers.deleted = 'N'
INNER JOIN ";

if (($_SESSION['user_customer_id']==1033)) { 
	$query .= "(" . SQL_PERSONX . ")";
} else {
	$query .= "cse_person";
}

//$query .= "cse_person";
$query .= " pers 
ON ccpers.person_uuid = pers.person_uuid
LEFT OUTER JOIN `cse_case_corporation` ccorp
ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
LEFT OUTER JOIN `cse_corporation` employer
ON ccorp.corporation_uuid = employer.corporation_uuid
LEFT OUTER JOIN cse_case_venue ccven 
ON ccpers.case_uuid = ccven.case_uuid
LEFT OUTER JOIN cse_venue cven
ON ccven.venue_uuid = cven.venue_uuid
LEFT OUTER JOIN cse_case_injury ccinj 
ON ccase.case_uuid = ccinj.case_uuid
LEFT OUTER JOIN cse_injury cinj 
ON ccinj.injury_uuid = cinj.injury_uuid
WHERE ccase.case_id = '" . $case_id . "'
AND ccase.customer_id = " . $_SESSION['user_customer_id'] . "
ORDER BY injury_id DESC LIMIT 1";

try {
	$persons = DB::select($query);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage(), "sql"=>$sql));
	echo json_encode($error);
}

$attorney_name = "";
$worker_name = "";
$ssn = "";

foreach($persons as $x=>$person) {
	$arrEmployerComm = array();
	$arrComm = array();
	$person_id = $person->person_id;
	
	//die("did:" . $person_id);
	$case_id = $person->case_id;
	$case_uuid = $person->case_uuid;
	$person_uuid = $person->person_uuid;
	$full_name = $person->full_name;
	$case_language = $person->case_language;
	$interpreter_needed = $person->interpreter_needed;
	$language = $person->language;
	$ssn = $person->ssn;
	$sub_in = $person->sub_in;
	$employer = $person->employer;
	$employer_address = $person->employer_address;
	$employer_phone = $person->employer_phone;
	$employer_fax = $person->employer_fax;
	$employer_site = $person->employer_site;
	$employer_email = $person->employer_email;
	if ($employer_phone!=""){
		$arrEmployerComm[] = "<strong>Phone</strong>: " . $employer_phone;
	}
	if ($employer_fax!=""){
		$arrEmployerComm[] = "<strong>Fax:</strong> " . $employer_fax;
	}
	if ($employer_site!=""){
		$arrEmployerComm[] = '<strong>Website:</strong> <a href="http://' . str_replace("http://", "", $employer_site) . '" target="_blank">' . $employer_site . '</a>';
	}
	if ($employer_email!=""){
		$arrEmployerComm[] = "<strong>Email:</strong> <a href='mailto:" . $employer_email . "'>" . $employer_email . "</a>";
	}
	$intake_date = $person->submittedOn;
	$special_instructions = $person->special_instructions;
	$special_instructions = str_replace("\r\n", "<br>", $special_instructions);
	$special_instructions = str_replace("\n", "<br>", $special_instructions);
	$first_name = $person->first_name;
	$middle_name = $person->middle_name;
	$last_name = $person->last_name;
	$aka = $person->aka;
	$preferred_name = $person->preferred_name;
	$full_address = $person->full_address;
	$street = $person->street;
	if ($street!="") {
		$suite = $person->suite;
		if ($suite!="") {
			$street .= ", " . $suite;
		}
		$city = $person->city;
		$state = $person->state;
		$zip = $person->zip;
		
		$full_address = $street. ", " . $city . ", " . $state . " " . $zip;
	}
	$phone = $person->phone;
	if ($phone!="") {
		$arrComm[] = "<strong>Phone:</strong> " . $phone;
	}
	$email = $person->email;
	if ($email!="") {
		$arrComm[] = "<strong>Email:</strong> <a href='mailto:" . $email . "'>" . $email . "</a>";
	}
	$fax = $person->fax;
	if ($fax!="") {
		$arrComm[] = "<strong>Fax:</strong> " . $fax;
	}
	$work_phone = $person->work_phone;
	if ($work_phone!="") {
		$arrComm[] = "<strong>Work:</strong> " . $work_phone;
	}
	$cell_phone = $person->cell_phone;
	if ($cell_phone!="") {
		$arrComm[] = "<strong>Cell:</strong> " . $cell_phone;
	}
	$work_email = $person->work_email;
	$ssn_last_four = $person->ssn_last_four;
	$dob = $person->dob;
	$license_number = $person->license_number;
	$title = $person->title;
	$case_venue = $person->case_venue;
	
	$case_number = $person->case_number;
	$file_number = $person->file_number;
	
	//for now
	if ($case_number=="" && $file_number!="") {
		$case_number=$file_number;
	}
	$case_name = $person->case_name;
	if ($case_name=="") {
		$case_name = $first_name . " " . $last_name . " vs " . $employer;
	}
	$attorney = $person->attorney;
	$worker = $person->worker;
		
	if ($attorney != "") {
		if (is_numeric($attorney)) {
			$query_att = "SELECT user_id, user_first_name, user_last_name 
						  FROM `ikase`.`cse_user`
						  WHERE user_id = :user_id";
			try {
				$db = getConnection();
				$stmt = $db->prepare($query_att);
				$stmt->bindParam("user_id", $attorney);
				$stmt->execute();
				$att = $stmt->fetchObject();
			} catch(PDOException $e) {
				$error = array("error"=> array("text"=>$e->getMessage(), "sql"=>$sql));
					echo json_encode($error);
			}

            $att_first_name = $att->user_first_name;
            $att_last_name = $att->user_last_name;
			$attorney_name = $att_first_name . " " . $att_last_name;
		} else {
			$attorney_name = $attorney;
		}
	}
	if ($worker != "") {
		if (is_numeric($worker)) {
			$query_work = "SELECT * 
					  FROM `ikase`.`cse_user`
					  WHERE user_id = :user_id";
		} else {
			$query_work = "SELECT * 
					  FROM `ikase`.`cse_user`
					  WHERE nickname = :user_id";
		}
		try {
			$db = getConnection();
			$stmt = $db->prepare($query_work);
			$stmt->bindParam("user_id", $worker);
			$stmt->execute();
			$work = $stmt->fetchObject();
		} catch(PDOException $e) {
            echo json_encode(["error" => ["text" => $e->getMessage(), "sql" => $sql]]);
        }
        $worker_name = "$work->user_first_name $work->user_last_name";
	}

	
	$occupation = $person->occupation;
	$start_date = $person->start_date;
	$end_date = $person->end_date;
	$injury_location = $person->injury_full_address;
	//$age = $person->age;
	$age = "";
//	if ($age==0) {
		if (validateDate($dob)) {
			$age = age(date("m/d/Y", strtotime($dob)));
		}
//	}
	$explanation = $person->explanation;
	$adj_number = $person->adj_number;
	
	$arrLanguageOccupation = array();
	if ($language=="" && $case_language!="") {
		$language = $case_language;
	}
	if ($language!="") {
		$language = "<span style='font-weight:bold'>Language:</span> " . $language;
		if ($interpreter_needed=="Y") {
			$language .= " - <span class='highlight'>Interpreter Needed</span>";
		}
		$arrLanguageOccupation[] = $language;
	}
	if ($occupation!="") {
		$arrLanguageOccupation[] = "<span style='font-weight:bold'>Occupation:</span> " . $occupation;
	}
}
$query_applicant_picture = "SELECT doc.*
	FROM  `cse_document` doc
	INNER JOIN  `cse_case_document` 
	ON  (`doc`.`document_uuid` =  `cse_case_document`.`document_uuid` AND `cse_case_document`.`attribute_1` = 'applicant_picture')
	INNER JOIN  `cse_case` 
	ON (  `cse_case_document`.`case_uuid` =  `cse_case`.`case_uuid` 
	AND  `cse_case`.`case_id` = :case_id) 
	WHERE doc.customer_id = :customer_id
	AND doc.deleted =  'N'
	ORDER BY doc.document_date DESC, doc.document_id DESC";

try {
	$db = getConnection();
	$stmt = $db->prepare($query_applicant_picture);
	$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
	$stmt->bindParam("case_id", $case_id);
	$stmt->execute();
	$app_picture = $stmt->fetchObject();
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage(), "sql"=>$sql));
		echo json_encode($error);
}
$document_filename = "";

if (is_object($app_picture)) {
	$document_filename = $app_picture->document_filename;
}

$query_noemp = "SELECT DISTINCT partie.corporation_id, partie.type corporation_type, partie.corporation_uuid, partie.company_name, partie.preferred_name, partie.full_address, cpt.partie_type, partie.phone partie_phone, partie.fax partie_fax, partie.full_name partie_full_name, partie.company_site partie_company_site, partie.email partie_email, cpt.employee_title partie_employee_title, partie.employee_phone partie_employee_phone,
    partie.employee_email partie_employee_email,
    partie.employee_fax partie_employee_fax, ccase.adj_number, cdoc.adhoc_value `doctor_type`
FROM cse_case ccase
INNER JOIN `cse_case_corporation` ccorp
ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute != 'employer' AND ccorp.deleted = 'N')
INNER JOIN `cse_corporation` partie
ON ccorp.corporation_uuid = partie.corporation_uuid
INNER JOIN `cse_partie_type` cpt
ON partie.type = cpt.blurb
LEFT OUTER JOIN `cse_corporation_adhoc` cdoc
ON (partie.corporation_uuid = cdoc.corporation_uuid AND cdoc.`deleted` =  'N' AND cdoc.adhoc = 'doctor_type')
WHERE ccase.case_id = :case_id
AND partie.deleted = 'N'
AND ccase.customer_id = :customer_id
ORDER BY cpt.sort_order, partie.company_name ";

try {
	$db = getConnection();
	$stmt = $db->prepare($query_noemp);
	$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
	$stmt->bindParam("case_id", $case_id);
	$stmt->execute();
	$kase_parties = $stmt->fetchAll(PDO::FETCH_OBJ);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage(), "sql"=>$sql));
	echo json_encode($error);
}
$arrPartieInfo = array();

foreach($kase_parties as $int=>$partie) {
	$arrPartieComm = array();
	$arrPartieEmployeeComm = array();
	$partie_id = $partie->corporation_id;
	$partie_uuid = $partie->corporation_uuid;
	$partie_type = $partie->partie_type;
	$doctor_type = $partie->doctor_type;
	$partie_name = $partie->company_name;
	$partie_preferred_name = $partie->preferred_name;
	$partie_address = $partie->full_address;
	$partie_phone = $partie->partie_phone;
	$partie_fax = $partie->partie_fax;
	$partie_full_name = $partie->partie_full_name;
	$partie_company_site = $partie->partie_company_site;
	$partie_email = $partie->partie_email;
	$partie_employee_title = $partie->partie_employee_title;
	$partie_employee_email = $partie->partie_employee_email;
	$partie_employee_phone = $partie->partie_employee_phone;
	$partie_employee_fax = $partie->partie_employee_fax;
	
	if ($partie_phone!=""){
		$arrPartieComm[] = "<strong>Phone:</strong> " . $partie_phone;
	}
	if ($partie_fax!=""){
		$arrPartieComm[] = "<strong>Fax:</strong> " . $partie_fax;
	}
	if ($partie_company_site!=""){
		$arrPartieComm[] = '<strong>Website:</strong> <a href="http://' . str_replace("http://", "", $partie_company_site) . '" target="_blank">' . $partie_company_site . '</a>';
	}
	if ($partie_email!=""){
		$arrPartieComm[] = "<strong>Email:</strong> <a href='mailto:" . $partie_email . "'>" . $partie_email . "</a>";
	}
	
	if ($partie_employee_phone!=""){
		$arrPartieEmployeeComm[] = "<strong>Phone:</strong> " . $partie_employee_phone;
	}
	if ($partie_employee_fax!=""){
		$arrPartieEmployeeComm[] = "<strong>Fax:</strong> " . $partie_employee_fax;
	}
	if ($partie_employee_email!=""){
		$arrPartieEmployeeComm[] = "<strong>Email:</strong> <a href='mailto:" . $partie_employee_email . "'>" . $partie_employee_email . "</a>";
	}
	
	
	$partie_injury_uuid = "";
	if ($partie_type=="Insurance Carrier") {
		//check for injury
		$query_carrier = "SELECT injury_uuid
		FROM cse_case_corporation 
		WHERE corporation_uuid = :corporation_uuid
		AND customer_id = :customer_id";
		
		//die($query_carrier . "\r\n");
		//$result_carrier = DB::runOrDie($query_carrier);
//$numbs_carrier = $result_carrier->rowCount();
		try {
			$db = getConnection();
			$stmt = $db->prepare($query_carrier);
			$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
			$stmt->bindParam("corporation_uuid", $partie_uuid);
			$stmt->execute();
			$partie_injury = $stmt->fetchObject();
		} catch(PDOException $e) {
			$error = array("error"=> array("text"=>$e->getMessage(), "sql"=>$sql));
			echo json_encode($error);
		}
		if (is_object($partie_injury)) {
			$partie_injury_uuid = $partie_injury->injury_uuid;
		}
	}
	$arrPartieInfo[] = array("partie_id"=>$partie_id, "partie_uuid"=>$partie_uuid, "partie_type"=>$partie_type, "doctor_type"=>$doctor_type, "partie_name"=>$partie_name, "partie_address"=>$partie_address, "partie_phone"=>$partie_phone, "partie_fax"=>$partie_fax, "partie_full_name"=>$partie_full_name, "partie_preferred_name"=>$partie_preferred_name, "partie_company_site"=>$partie_company_site, "partie_email"=>$partie_email, "partie_employee_title"=>$partie_employee_title, "partie_comm"=>implode(" | ", $arrPartieComm), "partie_employee_comm"=>implode(" | ", $arrPartieEmployeeComm), "partie_injury_uuid"=>$partie_injury_uuid);	
}

//die(print_r($arrPartieInfo));

$query_bod = "SELECT DISTINCT ci.injury_id, bp.*, cib.`status` bodyparts_status, cib.attribute bodyparts_number, ccase.case_id, ccase.case_uuid 
			FROM `cse_bodyparts` bp
			INNER JOIN cse_injury_bodyparts cib
			ON bp.bodyparts_uuid = cib.bodyparts_uuid
			INNER JOIN cse_injury ci
			ON (cib.injury_uuid = ci.injury_uuid)
			INNER JOIN cse_case_injury cci
			ON ci.injury_uuid = cci.injury_uuid
			INNER JOIN cse_case ccase
			ON (cci.case_uuid = ccase.case_uuid
			AND `ccase`.`case_id` = :case_id)
			WHERE 1
			AND cci.customer_id = :customer_id
			AND cci.deleted = 'N'
			AND cib.deleted = 'N'";
if ($injury_id != "") {
	$query_bod .= "	
	AND ci.injury_id = :injury_id";
}
$query_bod .= "	
ORDER BY `code` ASC";
try {
	$db = getConnection();
	$stmt = $db->prepare($query_bod);
	$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
	$stmt->bindParam("case_id", $case_id);
	if ($injury_id != "") {
		$stmt->bindParam("injury_id", $injury_id);
	}
	$stmt->execute();
	$bods = $stmt->fetchAll(PDO::FETCH_OBJ);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage(), "sql"=>$sql));
	echo json_encode($error);
}

$arrBodInfo = array();
$arrBodyListing = array();
foreach($bods as $int=>$bod) {
	$body_injury_id = $bod->injury_id;
	$code = $bod->code;
	$bodyparts_status = $bod->bodyparts_status;
	$description = $bod->description;
	$arrDescription = explode(" - ", $description);
	$description = $arrDescription[0];
	$arrBodInfo[$body_injury_id][] = array("code"=>$code, "description"=>$description);	
	$listing = $code . " - " . $description;
	if ($bodyparts_status=="N") {
		$listing = "<span style='color:red;text-decoration:line-through'>" . $listing . "</span>";
	}
	$arrBodyListing[$body_injury_id][] = $listing;	
}

//per dordulian, sol is from doi end date
//$sol_date = new DateTime("+12 months $intake_date");
//die($sol_date->format('Y-m-d') . "\n");

//injuries
$sql = "SELECT `inj`.`injury_id`, `inj`.`injury_uuid`, `injury_number`, `inj`.`adj_number`, `inj`.`type`, `occupation`, `start_date`, `end_date`, `ct_dates_note`, `body_parts`, `statute_limitation`, `explanation`, `inj`.`full_address`, `inj`.`street`, `inj`.`city`, `inj`.`state`, `inj`.`zip`, `inj`.`suite`, `inj`.`customer_id`, `inj`.`deleted`, inj.injury_status, 
IFNULL(`cin`.`alternate_policy_number`, '') addl_claim_number
FROM `cse_injury` inj
LEFT OUTER JOIN cse_injury_injury_number ciin
ON inj.injury_uuid = ciin.injury_uuid
LEFT OUTER JOIN cse_injury_number cin
ON ciin.injury_number_uuid = cin.injury_number_uuid 
INNER JOIN cse_case_injury ccinj
ON inj.injury_uuid = ccinj.injury_uuid
INNER JOIN cse_case ccase
ON (ccinj.case_uuid = ccase.case_uuid";
$sql .= " AND `ccase`.`case_id` = '" . $case_id . "')";
if ($injury_id != "") {
	$sql .= "	AND inj.injury_id IN ('" . implode("','", $arrInjuryIDs) . "')";
}
$sql .= " WHERE 1
AND inj.customer_id = " . $_SESSION['user_customer_id'] . "
AND ccase.deleted = 'N'
AND inj.deleted = 'N'";
try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
	$stmt->bindParam("case_id", $case_id);
	if ($injury_id != "") {
		$stmt->bindParam("injury_id", $injury_id);
	}
	$stmt->execute();
	$injuries = $stmt->fetchAll(PDO::FETCH_OBJ);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage(), "sql"=>$sql));
	echo json_encode($error);
}
$arrInjuries = array();
$arrADJs = array();
foreach($injuries as $int_inj=>$injur) {
	$injury_id = $injur->injury_id;
	$injury_uuid = $injur->injury_uuid;
	$injury_status = $injur->injury_status;
	$adj_number = $injur->adj_number;
	$addl_claim_number = $injur->addl_claim_number;
	
	if ($addl_claim_number!="") {
		$addl_claim_number = "<strong>Addl Claim #:</strong>" . $addl_claim_number;
	}
	$start_date = $injur->start_date;
	$end_date = $injur->end_date;
	$statute_limitation = $injur->statute_limitation;
	$injury_location = $injur->full_address;
	$explanation = $injur->explanation;
	$injury_occupation = $injur->occupation;
	$end_date = $injur->end_date;
	
	$ct = date("m/d/Y", strtotime($start_date));
	if ($end_date!="0000-00-00") {
		$ct .= " - " . date("m/d/Y", strtotime($end_date)) . " CT";
		if ($statute_limitation=="0000-00-00") {
			$sol_date = new DateTime("+5 years $end_date");
			$statute_limitation = $sol_date->format("m/d/Y");
		}
	} else {
		if ($statute_limitation=="0000-00-00") {
			$sol_date = new DateTime("+5 years $start_date");
			$statute_limitation = $sol_date->format("m/d/Y");
		}
	}
	$arrADJs[] = $adj_number;
	//per dordulian 3/22/2017

	
	$arrInjuries[] = array("injury_id"=>$injury_id, "injury_uuid"=>$injury_uuid, "ct"=>$ct, "explanation"=>$explanation, "location"=>$injury_location, "occupation"=>$injury_occupation, "adj_number"=>$adj_number, "addl_claim_number"=>$addl_claim_number, "injury_status"=>$injury_status, "sol_date"=>$statute_limitation);
}

//die(print_r($arrInjuries));

$case_adj_number = implode("~",$arrADJs);

$arrFullName = array();
if ($first_name!="") {
	$arrFullName[] = $first_name;
}
if ($middle_name!="") {
	$arrFullName[] = $middle_name;
}
if ($last_name!="") {
	$arrFullName[] = str_replace("-", " - ", $last_name);
}
$full_name = ucwords(strtolower(implode(" ", $arrFullName)));
$full_name = str_replace(" - ", "-", $full_name);
?>
<!DOCTYPE html>
<html>
<head>
<title>Demographics Report (<?php echo $full_name; ?>)</title>
<META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<style type="text/css">
td {
	color:#000000;
	line-height:95%;
}
.highlight {
	 background:#9FF
}
.applicant_img {
	max-width:150px;
	max-height:150px;
}
</style>
<link rel="stylesheet" href="../css/bootstrap.3.0.3.min.css">
<script type="text/javascript" src="../lib/jquery.1.10.2.js"></script>
<script type="text/javascript" src="../lib/moment.min.js"></script> 
<script language="javascript">
var openSendForm = function() {
	var picture_holder = document.getElementById("picture_holder");
	if (typeof picture_holder != "undefined" && picture_holder != null) {
		picture_holder.style.display = "none";
	}
	
	$("#send_matrix_link").fadeOut(function() {
		$("#form_holder").fadeIn();
		document.getElementById("specific_instructions").focus();
	});
}
</script>
</head>

<body style="color:#EDEDED;" onLoad="init()">
<div style="background:black; text-align:center;display:none" id="matrix_holder"><img src="https://www.ikase.website/img/matrix_blue_logo.jpg" width="267" height="200" alt="Matrix"></div>
<table width="900" border="0" align="center" cellpadding="3" cellspacing="0" style="margin-top:0px">
  <tr>
  	<td width="16%" valign="top"><img src="https://www.ikase.website/img/ikase_logo_login.png" height="32" width="77"></td>
    <td colspan="3" align="left">
    <div style="float:right">
    	<a href="javascript:printSummary()" class="noprint_row">Print Summary Version</a>
    </div>
    <span  style="font-family:Arial, Helvetica, sans-serif; font-weight:bold; font-size:1.5em">
    	DEMOGRAPHICS COVER PAGE
    </span>
        <?php if ($injury_id!="") { ?>
        <div style="font-family:Arial, Helvetica, sans-serif; font-weight:bold; font-size:0.8em">
        	DOI: <?php echo $arrInjuries[0]["ct"]; ?>
        </div>
        <?php } ?>
    </td>
  </tr>
  <tr>
    <td width="16%">&nbsp;</td>
    <td width="28%"><em>as of <?php echo date("m/d/y g:iA"); ?></em></td>
    <td colspan="2" align="right" id="send_matrix_holder">
    	<?php if ($emailit!="y") { 
				//if ($customer->inhouse_id > 0) { ?>
        <a id="send_matrix_link" href="javascript:openSendForm()" title="Click to email this Kase to Matrix Document Imaging" style="font-weight:normal; background:black; color:white;padding:2px" class="noprint_row">Send Kase to Matrix</a><?php //echo $customer->inhouse_id; ?>
				<?php //} ?>
        <div id="form_holder" style="display:none; padding:5px; border:1px solid black; width:350px; text-align:right; z-index:2347" class="noprint_row">
        	<form id="send_form" action="demographics_sheet.php?case_id=<?php echo $case_id; ?>" method="post" enctype="multipart/form-data">
            	<input type="hidden" name="case_id" value="<?php echo $case_id; ?>" />
                <input type="hidden" name="emailit" value="y" />
            	<table>
                	<tr>
                    	<td align="left" style="border-bottom:1px solid #CCC">
                        	<div style="font-size:1.2em; font-weight:bold; padding-bottom:5px">Send this Kase to Matrix</div>
                        	<textarea name="specific_instructions" id="specific_instructions" cols="45" rows="3" placeholder="Enter specific instruction here"></textarea><br>
                            Specific Instructions
                        </td>
                    </tr>
                    <tr>
                    	<td align="left" style="border-bottom:1px solid #CCC">
                        	<input type="file" name="Filedata" /><br>
                            Attach Document
                        </td>
                    </tr>
                    <tr>
                    	<td align="left">
                        	<input type="submit" name="submit" value="Send It" />
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <?php } else { 
			echo "&nbsp;";
		} ?>
    </td>
  </tr>
  <tr>
    <td colspan="4">
        <hr/>
    </td>
  </tr>
  <?php if ($specific_instructions!="") { ?>
  <tr>
    <td valign="top" nowrap><strong>Specific Instructions</strong></td>
    <td colspan="3"><?php echo $specific_instructions; ?></td>
  </tr>
  <tr valign="top">
    <td colspan="4"><hr /></td>
  </tr>
  <?php } ?>
  <?php if ($special_instructions!="") { ?>
  <tr>
    <td valign="top" nowrap><span style="font-weight:bold; background:orange">Special Instructions</span></td>
    <td colspan="3"><?php echo $special_instructions; ?></td>
  </tr>
  <tr valign="top">
    <td colspan="4"><hr /></td>
  </tr>
  <?php } ?>
  <tr>
    <td valign="top" nowrap><strong>Case Number</strong></td>
    <td colspan="3">
    	<?php if ($sub_in=="Y") { ?>
        <div style="float:right">
            (Sub-In)
        </div>
        <?php } ?>
		<a href="../v8.php#kases/<?php echo $case_id; ?>" target="_blank" style='color:black'><?php echo $case_number; ?></a>
    </td>
  </tr>
  <tr>
    <td valign="top" nowrap><strong>Case Name</strong></td>
    <td colspan="2">
      <?php if ($document_filename!="") { ?>
      <?php } ?>
      
    <?php echo $full_name . " vs " . $employer; ?></td>
    <td width="10%" rowspan="4" valign="top">
    	<div style="float:right; z-index:2346" id="applicant_picture">
        <?php if ($document_filename!="") { ?>
        <img src='https://www.ikase.website/uploads/<?php echo $_SESSION['user_customer_id'] ?>/<?php echo $case_id; ?>/<?php echo $document_filename ?>' class='applicant_img' style="border:1px solid white" width='200' height='200'>
        <?php } ?>
        </div></td>
  </tr>
   <tr>
    <td valign="top" nowrap><strong>ADJ Number</strong></td>
    <td style="margin-top:0px" valign="top"><?php echo implode("; " , $arrADJs); ?></td>
    <?php foreach($arrPartieInfo as $party_info) { 
		if ($party_info["partie_type"]=="Venue") {
			if ($case_venue == "") {
				$case_venue = $party_info["partie_preferred_name"];
				if ($case_venue == "") {
					$case_venue = $party_info["partie_name"];
				}
				$judge = $party_info["partie_full_name"];
			}
		}
	}
	?>
    <td width="46%" align="left" valign="top"><strong>Venue</strong>&nbsp;<?php echo $case_venue; ?><?php if ($judge!="") { ?>&nbsp;&nbsp;&nbsp;&nbsp;<strong>Judge</strong>&nbsp;<?php echo $judge; ?><?php } ?></td>
  </tr>
  <tr>
    <td valign="top" nowrap><strong>DOI</strong><?php if (count($arrInjuries) > 1) { echo "(s)"; } ?></td>
    <td colspan="2" valign="top">
    <?php foreach($arrInjuries as $injury_index=>$injury) { 
		echo "<strong>" . $injury["ct"] . "</strong>"; 
		if ($injury_index < count($arrInjuries) - 1) {
			echo "&nbsp;|&nbsp;";
		}
	 } ?>
    </td>
  </tr>
    <tr>
      <td valign="top" nowrap class="highlight"><strong>Applicant</strong></td>
    <td colspan="2" nowrap class="highlight"><?php echo $full_name; ?>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo "<span style='font-weight:bold'>Age:</span> " . $age; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>SSN:</strong>
      <?php 
	if (strlen($ssn)==9) {
		echo substr($ssn, 0, 3) . "-" . substr($ssn, 3, 2) . "-" . substr($ssn, 5, 4); 
	} else {
		echo $ssn;
	}
	?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>DOB:</strong><?php 
	if ($dob!="" && $dob!="0000-00-00") {
		echo date("m/d/Y", strtotime($dob));
	}// . " (" . $age . " years old)"; ?>
    </td>
  </tr>
  <tr>
    <td valign="top" nowrap>&nbsp;</td>
    <td colspan="3"><?php echo $full_address; ?></td>
  </tr>
  <tr>
    <td valign="top" nowrap>&nbsp;</td>
    <td colspan="3"><?php echo implode(" | ", $arrComm); ?></td>
  </tr>
  <tr>
    <td valign="top" nowrap>&nbsp;</td>
    <td colspan="3"><?php echo implode("&nbsp;|&nbsp;", $arrLanguageOccupation); ?></td>
  </tr>
  
  <tr valign="top">
    <td colspan="4"><hr /></td>
  </tr>
  <tr>
    <td valign="top" nowrap><strong>Employer</strong></td>
    <td colspan="3"><?php echo $employer; ?></td>
  </tr>
  <tr>
    <td valign="top" nowrap>&nbsp;</td>
    <td colspan="3"><?php echo $employer_address; ?></td>
  </tr>
  <tr>
    <td valign="top" nowrap>&nbsp;</td>
    <td colspan="3"><?php echo implode(" | ", $arrEmployerComm); ?></td>
  </tr>
  <tr valign="top">
    <td colspan="4"><hr /></td>
  </tr>
  <?php 
  $arrInjuryClaim = array();
 //die(print_r($arrPartieInfo));
  foreach($arrPartieInfo as $party_info) { 
  	if ($party_info["partie_type"]=="Insurance Carrier" || $party_info["partie_type"]=="Defense Attorney") {
		$claim_number = "";
		if ($party_info["partie_type"]=="Insurance Carrier") {
			//die(print_r($party_info));
			//need to see if there is a claim number
            //echo $queryspec . "<br>";
			$resultspec = DB::runOrDie("SELECT adhoc_value  FROM `cse_corporation_adhoc` 
			WHERE `adhoc` = 'claim_number'
			AND deleted = 'N'
			AND corporation_uuid = ?", $party_info["partie_uuid"]);
            if ($resultspec->rowCount() > 0) {
				$claim_number = $resultspec->fetchColumn();
				
				$partie_injury_uuid = $party_info["partie_injury_uuid"];
				//die($partie_injury_uuid  . "cl" . $claim_number);
				
				if ($partie_injury_uuid != "") {
					$arrInjuryClaim[$partie_injury_uuid][] = $claim_number;
				}
			}
		}?>
  <tr>
    <td valign="top" nowrap><strong><?php echo $party_info["partie_type"]; ?></strong></td>
    <td colspan="3"><strong><?php echo $party_info["partie_name"]; ?></strong></td>
  </tr>
   <tr>
    <td valign="top" nowrap class="<?php if ($claim_number!="") { ?>highlight<?php } ?>"><?php if ($claim_number!="") { ?><strong>Claim Number</strong><?php } ?></td>
    <td colspan="3" class="<?php if ($claim_number!="") { ?>highlight<?php } ?>">
	<?php if ($claim_number!="") { 
		echo $claim_number . "&nbsp;&nbsp;&nbsp;&nbsp;"; 
	} ?>
    <strong><?php echo $party_info["partie_employee_title"]; ?></strong>: <?php echo $party_info["partie_full_name"]; ?>
    <?php if ($party_info["partie_employee_comm"]!="") {
		echo "&nbsp;|&nbsp;" . $party_info["partie_employee_comm"];
	} ?>
    </td>
  </tr>
   <tr>
    <td valign="top" nowrap>&nbsp;</td>
    <td colspan="3"><?php echo $party_info["partie_address"]; ?></td>
  </tr>
  <?php if ($party_info["partie_comm"]!="") { ?>
  <tr>
    <td valign="top" nowrap>&nbsp;</td>
    <td colspan="3"><?php echo $party_info["partie_comm"]; ?></td>  </tr>
  <?php } ?>
  <tr valign="top">
    <td colspan="4"><hr /></td>
  </tr>
  <?php
  	}
  } ?>
  <tr>
    <td valign="top" nowrap><strong>Intake Date</strong></td>
    <td><?php echo date("m/d/Y", strtotime($intake_date)); ?></td>
    <td nowrap>&nbsp;
    	
    </td>
    <td nowrap>&nbsp;</td>
  </tr>
  <tr>
    <td valign="top" nowrap><strong>Attorney</strong></td>
    <td><?php echo $attorney_name; ?>
		</td>
    <td nowrap><strong>Coordinator</strong>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $worker_name; ?></td>
    <td nowrap>&nbsp;</td>
  </tr>
  <tr valign="top">
    <td colspan="4"><hr /></td>
  </tr>
  <?php 
  $injury_count = count($arrInjuries);
  //die(print_r($arrInjuries));
  $i_index = 0;
  foreach($arrInjuries as $injury_index=>$injury) {
	  $i_index++;
	  //if ($doi_injury_id!="") {
		if (count($arrInjuryIDs) > 0) {
			//if ($injury["injury_id"]!=$doi_injury_id) {
			if (!in_array($injury["injury_id"], $arrInjuryIDs)) {
				continue;
			}
		}
	  //die(print_r($injury));
	  if (!isset($arrInjuryClaim[$injury["injury_uuid"]])) {
		  $arrInjuryClaim[$injury["injury_uuid"]]= array();
	  }
	  ?>
  <tr>
    <td valign="top" nowrap>
    	<strong>Date of Injury</strong>&nbsp;<?php 
		if ($injury_count > 1) {
			echo '<span style="border:1px solid black; padding:1px" class="injury_count">' . $i_index . '</span>';
		}
		?>
    </td>
    <td colspan="2">
    	<?php if ($injury["injury_status"]!="") { ?>
		<div style="float:right; width:150px; text-align:left">
        	<strong>Status:</strong>&nbsp;<?php echo $injury["injury_status"]; ?>
		</div>
        <?php } ?>
		<?php echo $injury["ct"]; ?>
        &nbsp;|&nbsp;<strong>SOL Date</strong>: <?php echo date("m/d/Y", strtotime($injury["sol_date"])); ?>
    </td>
    <td colspan="1"><strong>ADJ:</strong><?php echo $injury["adj_number"]; ?></td>
  </tr>
  <tr>
    <td valign="top" nowrap><strong>Place of Injury</strong></td>
    <td colspan="3">
    	<div style="float:right; width:310px; text-align:left">
        	<strong>Occupation:</strong>&nbsp;<?php echo $injury["occupation"]; ?>
        </div>
		<?php echo $injury["location"]; ?>
    </td>
  </tr>
  <tr>
  	<td valign="top"><strong>Claim Number</strong></td>
    <td colspan="3">
    	<div style="float:right; width:310px; text-align:left">
        	<?php echo $injury["addl_claim_number"]; ?>
        </div>
		<?php echo implode("; ", $arrInjuryClaim[$injury["injury_uuid"]]); ?>
    </td>
  </tr>
  <tr>
    <td valign="top" nowrap><strong>Explanation</strong></td>
    <td colspan="3"><?php echo $injury["explanation"]; ?></td>
  </tr>
	<tr>
        <td valign="top" nowrap ><strong>Body Parts</strong></td>
        <td colspan="3">
            <?php 
			if (isset($arrBodyListing[$injury["injury_id"]])) {
				echo implode("; ", $arrBodyListing[$injury["injury_id"]]);
			} 
			?>
        </td>
      </tr>
      <tr valign="top">
        <td colspan="4"><hr/></td>
      </tr>
  <?php } ?>
  
  <?php 
  $blnShowRemoveMedical = false;
  foreach($arrPartieInfo as $party_info) { 	
  	if ($party_info["partie_type"]!="Insurance Carrier" && $party_info["partie_type"]!="Defense Attorney" && $party_info["partie_type"]!="Venue") {
	if ($party_info["partie_type"]=="Defendant") {
		continue;
	}
	/*
	if ($party_info["partie_type"]=="Medical Provider" && $party_info["doctor_type"]!="PTP") {
		continue;
	}
	*/
    $specialty = "";
    if ($party_info["partie_type"]=="Medical Provider") {
        //need to see if there is a specialty
        $resultspec = DB::runOrDie("SELECT adhoc_value  FROM `cse_corporation_adhoc` 
    WHERE `adhoc` = 'specialty'
    AND corporation_uuid = ?", $party_info["partie_uuid"]);
        if ($resultspec->rowCount() > 0) {
            $specialty = ucwords($resultspec->fetchColumn());
        }
    }
	?>
    <?php 
	$tr_class = "";
	if ($party_info["partie_type"]=="Medical Provider") {
		$tr_class = "medical_row";
		if ($party_info["doctor_type"]!="PTP") {
			$tr_class .= " noprint_row";
		}
	}
	?>
  <tr class="<?php echo $tr_class; ?>">
    <td valign="top" nowrap><strong><?php echo $party_info["partie_type"]; ?></strong>
    	<?php if ($party_info["partie_type"]=="Medical Provider") {
				if ($party_info["doctor_type"]!="") { ?>
        <div style="position:absolute; z-index:2; margin-top:3px; font-weight:bold">
        	Type:&nbsp;<?php echo $party_info["doctor_type"]; ?>
        </div>
        <?php 	}
			} ?>
    </td>
    <td valign="top" nowrap>
    	<strong><?php echo $party_info["partie_name"]; ?></strong>
    </td>
    <td nowrap>&nbsp;</td>
    <td nowrap align="right">
    	<?php
		if ($party_info["partie_type"]=="Medical Provider") {
			if (!$blnShowRemoveMedical) {
				$blnShowRemoveMedical = true;
			?>
			<a id="remove_medical_link" title="Click to remove Medical Providers from this Demographic Report" style="color:darkseagreen; text-decoration:none; cursor:pointer; font-size:1.8em;" onClick="removeMedicalRows()">&#8226;</a>
		<?php }
		}
		?>
    </td>
  </tr>
  <?php if (trim($party_info["partie_full_name"])!="") { ?>
   <tr class="<?php echo $tr_class; ?>">
    <td valign="top" nowrap>&nbsp;</td>
    <td colspan="3"><?php echo $party_info["partie_employee_title"]; ?>: <?php echo $party_info["partie_full_name"]; ?></td>
  </tr>
  <?php } ?>
  <?php if ($party_info["partie_address"]!="") { ?>
   <tr class="<?php echo $tr_class; ?>">
    <td valign="top" nowrap>&nbsp;</td>
    <td colspan="3"><?php echo $party_info["partie_address"]; ?></td>
  </tr>
  <?php } ?>
  <?php if ($party_info["partie_comm"]!="") { ?>
  <tr class="<?php echo $tr_class; ?>">
    <td valign="top" nowrap>&nbsp;</td>
    <td colspan="3"><?php echo $party_info["partie_comm"]; ?></td>  
  </tr>
  <?php } ?>
  <?php if ($specialty!="") { ?>
  <tr class="<?php echo $tr_class; ?>">
    <td valign="top" nowrap><strong>Specialty</strong></td>
    <td colspan="3"><?php echo $specialty; ?></td>  
  </tr>
  <?php } ?>
  <tr class="<?php echo $tr_class; ?>">
    <td colspan="4"><hr /></td>
  </tr>
  <?php 
  	}
  } ?>
  <?php
  $sql = "SELECT corp.*, corp.corporation_id id , corp.corporation_uuid uuid, cpt.partie_type, cpt.employee_title, cpt.color, cpt.blurb, cpt.show_employee, cpt.adhoc_fields    
		FROM `cse_corporation` corp ";
$sql .= " INNER JOIN `cse_partie_type` cpt
		ON corp.type = cpt.blurb
		INNER JOIN cse_person_corporation ccp ON corp.corporation_uuid = ccp.corporation_uuid
		INNER JOIN cse_person cse ON ccp.person_uuid = cse.person_uuid";	
$sql .= " WHERE corp.deleted = 'N'";
$sql .= " AND cse.person_id = " . $person_id;
$sql .= " AND corp.customer_id = " . $_SESSION['user_customer_id'] . "
		ORDER by ccp.person_corporation_id";

//die($sql);
$result_prior = DB::runOrDie($sql);
  if ($result_prior->rowCount() > 0) { ?>
	<tr class="<?php echo $tr_class; ?>">
    <td valign="top" nowrap colspan="2"><strong>Prior Medical Treatment</strong></td>
  </tr>
  <tr class="<?php echo $tr_class; ?>">
    <td valign="top" nowrap colspan="2"><hr></td>
  </tr>
<?php }
$tr_class = "noprint_row";
while ($row = $result_prior->fetch()) {
	$arrPartieComm = array();
	$partie_id = $row->id;
	$partie_type = $row->partie_type;
	$corporation_type = $row->type;
	$partie_name = $row->company_name;
	$partie_address = $row->full_address;
	$partie_phone = $row->phone;
	$partie_fax = $row->fax;
	$partie_company_site = $row->company_site;
	$partie_email = $row->email;
	$copying_instructions = $row->copying_instructions;
	
	$arrCopying = explode("|", $copying_instructions);

	if ($partie_phone!=""){
		$arrPartieComm[] = "<strong>Phone:</strong> " . $partie_phone;
	}
	if ($partie_fax!=""){
		$arrPartieComm[] = "<strong>Fax:</strong> " . $partie_fax;
	}
	if ($partie_company_site!=""){
		$arrPartieComm[] = '<strong>Website:</strong> <a href="http://' . str_replace("http://", "", $partie_company_site) . '" target="_blank">' . $partie_company_site . '</a>';
	}
	if ($partie_email!=""){
		$arrPartieComm[] = "<strong>Email:</strong> <a href='mailto:" . $partie_email . "'>" . $partie_email . "</a>";
	}
	$partie_comm = implode(" | ", $arrPartieComm);
	
	if ($partie_name!="") { ?>
   <tr class="<?php echo $tr_class; ?>">
    <td valign="top" nowrap>&nbsp;</td>
    <td colspan="3">
    	<div id="add_on_holder_<?php echo $int_prior; ?>" class="add_on_location" style="position:absolute; margin-left:450px; display:none">
        	
        </div>
        <input type="hidden" id="partie_id_<?php echo $int_prior; ?>" value="<?php echo $partie_id; ?>" />
        <input type="hidden" id="partie_type_<?php echo $int_prior; ?>" value="<?php echo $corporation_type; ?>" />
		<span id="partie_name_<?php echo $int_prior; ?>" style="font-weight:bold"><?php echo $partie_name; ?></span>
    </td>
  </tr>
  <?php } ?>
  <?php if ($partie_address!="") { ?>
   <tr class="<?php echo $tr_class; ?>">
    <td valign="top" nowrap>&nbsp;</td>
    <td colspan="3"><?php echo $partie_address; ?></td>
  </tr>
  <?php } ?>
  <?php if ($partie_comm!="") { ?>
  <tr class="<?php echo $tr_class; ?>">
    <td valign="top" nowrap>&nbsp;</td>
    <td colspan="3"><?php echo $partie_comm; ?></td>  
  </tr>
  <?php } ?>
  <tr class="<?php echo $tr_class; ?>">
  	<td valign="top" nowrap>&nbsp;</td>
    <td colspan="3">
		<?php 
		echo "Records Requested:" . $arrCopying[0]; 
		if ($arrCopying[2]=="Y") {
			echo "&nbsp;-&nbsp;Any and All";
		}
		?>
    </td>
  </tr>
  <?php if ($arrCopying[1]!="") { ?>
  <tr class="<?php echo $tr_class; ?>">
    <td valign="top" nowrap>&nbsp;</td>
    <td colspan="3">Other: <?php echo $arrCopying[1]; ?></td>  
  </tr>
  <?php } ?>
  <?php if ($arrCopying[3]!="") { ?>
  <tr class="<?php echo $tr_class; ?>">
    <td valign="top" nowrap>&nbsp;</td>
    <td colspan="3">Special Instructions: <?php echo $arrCopying[3]; ?></td>  
  </tr>
  <?php } ?>
  <tr class="<?php echo $tr_class; ?>">
    <td valign="top" nowrap colspan="2"><hr></td>  
  </tr>
<?php } ?>
</table>
<script language="javascript">
var current_case_id = "<?php echo $case_id; ?>";
var adj_number = "<?php echo $case_adj_number; ?>";
var ssn = "<?php echo $ssn; ?>";
var order_id = "";
var request_id = "";
function init() {
	<?php if (isset($_GET["send"])) { ?>
	openSendForm();
	<?php } else { ?>
	checkRequest();
	<?php } ?>
}
function checkRequest() {
	//is this case in matrix
	var url = "https://www.ikase.website/api/kases/matrix";
	var formValues = "id=" + current_case_id + "&adj_number=" + adj_number + "&nss=" + ssn;
	//return;
	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
		success:function (data) {
			if (data.imported=="Y") {
				$("#send_matrix_holder").html("<span style='background:blue;color:white;font-weight:bold; padding:2px' class='noprint_row'>Sent to Matrix - " + data.time_stamp + "</span>");
				request_id = data.id;
				//show the addon imports
				$(".add_on_location").fadeIn();
				
				checkOrder();
			} else {
				//maybe activity
				checkSent();
			}
		}
	});
}
function checkSent() {
	///kases/matrixsent/:case_id
	//is this case in activity
	var url = "../api/kases/matrixsent/" + current_case_id;

	//return;
	$.ajax({
		url:url,
		type:'GET',
		dataType:"json",
		data: "",
		success:function (data) {
			if (data.activity_count>0) {
				$("#send_matrix_holder").html("<span style='background:blue;color:white;font-weight:bold; padding:2px' class='noprint_row'>Sent to Matrix -- " + moment(data.activity_date).format("MM/DD/YY") + "</span>");
				
				//show the addon imports
				$(".add_on_location").fadeIn();
				
				checkOrder();
			}
		}
	});
}
function checkOrder() {
	if (adj_number=="" && ssn=="") {
		checkRequestLocations();
		return;
	}
	//is this order in matrix
	if (adj_number!="") {
		var url = "https://www.ikase.website/api/kases/matrixadj/" + current_case_id + "/" + adj_number;
	} else {	//if adj is empty
		//is this case in matrix
		var url = "https://www.ikase.website/api/kases/matrixorder/" + current_case_id;
	}
	//return;
	$.ajax({
		url:url,
		type:'GET',
		dataType:"json",
		data: "",
		success:function (data) {
			if (data.imported=="Y") {
				//there is an order
				order_id = data.order_id;
				
				if (order_id!="") {
					//update the message to indicate that it was added to matrix system
	$("#send_matrix_holder").html("<span style='background:green;color:white;font-weight:bold; line-height:21px; padding:2px' class='noprint_row'>Added to Matrix on " + data.assigned_date + "<br>Order ID:&nbsp;" + order_id + "</span>");
				}
			}
			//look for any location
			checkRequestLocations();
		}
	});
}
function checkRequestLocations() {
	/*
	if (order_id=="") {
		return false;
	}
	*/
	var add_on_locations = $(".add_on_location");
	var arrLength = add_on_locations.length;
	for(var i = 0; i < arrLength; i++) {
		var element = add_on_locations[i];
		var element_id = element.id;
		var id = element_id.split("_")[3];
		var partie_name = $("#partie_name_" + id).html();
		//is this location in matrix
		var url = "https://www.ikase.website/api/kases/matrixrequestlocation";
		var formValues = "field_id=" + id + "&id=" + current_case_id + "&order_id=" + request_id + "&facility=" + encodeURI(partie_name);
		//return;
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if (data.imported=="Y") {
					$background_color = "blue";
					
					$("#add_on_holder_" + data.field_id).html("<span style='background:blue;color:white;padding:1px'>Sent to Matrix - " + data.assigned_date + "</span>");
					//was it imported?
					if (data.deleted == "Y" && data.verified == "Y") {
						//it's both, so it was looked at and cancelled, meaning it's in matrix under a different set of data, maybe a misspell?
						$("#add_on_holder_" + data.field_id).html("<span style='background:green;color:white;padding:1px'>Added to Matrix - " + data.assigned_date + "</span>");
					} else {
						//active locations
						checkLocations();
					}
				} else {
					//$("#add_on_holder_" +  data.field_id).html("<button onclick='getAddOn(" + data.field_id + ")' class='btn btn-sm btn-primary'>Send Add-On to Matrix</button>");
					checkMatrixSysLocations(request_id)
				}
			}
		});
	}
}
function checkMatrixSysLocations(the_request_id) {
	//secondary check, it might already be in matrix without the request
	request_id = the_request_id;
	var add_on_locations = $(".add_on_location");
	var arrLength = add_on_locations.length;
	for(var i = 0; i < arrLength; i++) {
		var element = add_on_locations[i];
		var element_id = element.id;
		var id = element_id.split("_")[3];
		var partie_name = $("#partie_name_" + id).html().trim();
		//is this location in matrix
		var url = "https://www.ikase.website/api/kases/matrixsyslocation";
		var formValues = "field_id=" + id + "&id=" + current_case_id + "&order_id=" + request_id + "&facility=" + encodeURI(partie_name);
		//return;
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if (data.imported=="Y") {
					$background_color = "blue";
					
					$("#add_on_holder_" + data.field_id).html("<span style='background:blue;color:white;padding:1px'>Sent to Matrix - " + data.assigned_date + "</span>");
					//was it imported?
					if (data.deleted == "Y" && data.verified == "Y") {
						//it's both, so it was looked at and cancelled, meaning it's in matrix under a different set of data, maybe a misspell?
						$("#add_on_holder_" + data.field_id).html("<span style='background:green;color:white;padding:1px'>Added to Matrix - " + data.assigned_date + "</span>");
					}
				} else {
					$("#add_on_holder_" +  data.field_id).html("<button onclick='getAddOn(" + data.field_id + ")' class='btn btn-xs btn-primary'>Send to Matrix</button>");
					$("#add_on_holder_" +  data.field_id).fadeIn();
				}
			}
		});
	}
}
function checkLocations() {
	/*
	if (order_id=="") {
		return false;
	}
	*/
	var add_on_locations = $(".add_on_location");
	var arrLength = add_on_locations.length;
	for(var i = 0; i < arrLength; i++) {
		var element = add_on_locations[i];
		var element_id = element.id;
		var id = element_id.split("_")[3];
		var partie_name = $("#partie_name_" + id).html();
		//is this location in matrix
		var url = "https://www.ikase.website/api/kases/matrixlocation";
		var formValues = "field_id=" + id + "&id=" + current_case_id + "&order_id=" + order_id + "&facility=" + encodeURI(partie_name);
		//return;
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if (data.imported=="Y") {
					$("#add_on_holder_" + data.field_id).html("<span style='background:green;color:white;padding:1px'>Added to Matrix - " + data.assigned_date + "</span>");
				} 
			}
		});
	}
}
function getAddOn(field_id) {
	//console.log("field_id:", field_id);
	//return;
	var formValues = "order_id=" + request_id;
	var partie_id = $("#partie_id_" + field_id).val();
	var partie_type = $("#partie_type_" + field_id).val();
	//look up the partie
	var url = "https://www.ikase.website/api/corporation/" + partie_type + "/" + partie_id;
	//return;
	$.ajax({
		url:url,
		type:'GET',
		dataType:"text",
		data: "",
		success:function (corp_data) {
			//POST THE DATA to matrix now as a new location
			exportAddOn(corp_data, field_id);
			//console.log(data);
		}
	});
}
function exportAddOn(corp_data, field_id) {
	var url = "https://www.ikase.website/api/kases/addon";
	var formValues = "case_id=<?php echo $case_id; ?>&request_id=" + request_id + "&data=" + corp_data;
	//return;
	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
		success:function (data) {
			if (data.success) {
				$("#add_on_holder_" + field_id).css("background", "green");
				$("#add_on_holder_" + field_id).html("Exported to Matrix &#10003;");
				
				setTimeout(function() {
					$("#add_on_holder_" + field_id).css("background", "none");
				}, 2500);
			}
		}
	});
}
function printSummary() {
	$(".noprint_row").hide();
	window.print();
}
function removeMedicalRows() {
	$(".medical_row").hide();
}
</script>
</body>
</html>
<?php
//die("nick");
$content = ob_get_contents();
ob_end_clean();
echo $content;
if ($emailit!="y") {
	die();
}
if ($emailit=="y") {
	$php_content = '<?php
	if (!isset($_GET["dmsauth"])) {
		die("not allowed");
	} 
	$dmsauth = $_GET["dmsauth"];
	$dmsauth = strrev($dmsauth);
	$dmsauth = $dmsauth / 3;
	$now = mktime(0, 0, 0, date("m"), date("d"),   date("Y"));
	if ($now > $dmsauth) {
		die("not allowed");
	}
	?>';
	//the invoice is valid for 1 month online
	$dmsauth = mktime(0, 0, 0, date("m")+1, date("d"),   date("Y"));
	$dmsauth = $dmsauth * 3;
	$dmsauth = strrev($dmsauth);
	
	$file_url = "https://www.ikase.website/uploads/" . $_SESSION['user_customer_id'] . "/" . $case_id . "/demographics.html";
	//$url = "https://www.ikase.org/uploads/" . $_SESSION['user_customer_id'] . "/" . $case_id . "/demographics.php?dmsauth=" . $dmsauth;
	//$link = make_bitly_url($url);
	//$link = $url;
	
	//$content = $php_content . $content;
	$content = str_replace('display:none" id="matrix_holder"', 'display:" id="matrix_holder"', $content);
	
	//now output to a text file
	$upload_dir_config = 'D:/uploads/' . $_SESSION['user_customer_id'] . '/' . $case_id;
	if (!is_dir($upload_dir_config)) {
		mkdir($upload_dir_config, 0755, true);
	}
	$fp = fopen($upload_dir_config . '/demographics.html', 'w');
	fwrite($fp, $content);
	fclose($fp);
	
	//if ($_SERVER['REMOTE_ADDR']=='71.119.40.148') {
	if ($customer->inhouse_id > 0) {
		//die("inhouse_id");
		include("get_kase_info.php");
		$operation = "exported";
		$homepage = $operation;
		//echo $operation;
		//echo print_r($parties);
		//die(print_r($kase));
	} else {
		//die("send to matrix");
		$filename = "https://www.matrixdocuments.com/dis/pws/manage/request/forward_ikase.php?cus_id=" . $_SESSION['user_customer_id'] . "&customer_name=" . urlencode($_SESSION['user_customer_name']) . "&case_id=" . $case_id . "&case=" . urlencode($case_name) . "&attachment=" . $targetFile . "&specific_instructions=" . urlencode($specific_instructions) . "&dmsauth=" . $dmsauth;
		//echo $filename;
		//die();
		//echo "<br><br>";
		$homepage = file_get_contents($filename);
		
		//echo $homepage;
		//die();
		$operation = $homepage;
	}
	$activity = "Demographics Sheet for Case " . $case_id . " Sent to Matrix By " . $_SESSION["user_name"];
	$category = "Matrix Referral " . $operation;
	
	$activity_uuid = uniqid("KS", false);
	$sql = "INSERT INTO cse_activity (`activity_uuid`, `activity`, `activity_category`, `activity_user_id`, `customer_id`)
	VALUES ('" . $activity_uuid . "', '" . addslashes($activity) . "', '" . addslashes($category) . "', '" . $_SESSION['user_plain_id'] . "', " . $_SESSION['user_customer_id'] . ")";
	//$result_act = DB::runOrDie($sql);
try {
		$stmt = DB::run($sql);
	} catch(PDOException $e) {
		$error = array("error inserting activity"=> array("text"=>$e->getMessage(), "sql"=>$sql));
			echo json_encode($error);
	}
	$last_updated_date = date("Y-m-d H:i:s");
	$case_activity_uuid = uniqid("KA", false);
			
	$sql = "INSERT INTO cse_case_activity (`case_activity_uuid`, `case_uuid`, `activity_uuid`, `attribute`, `case_track_id`, `last_updated_date`, `last_update_user`, `customer_id`)
	VALUES ('" . $case_activity_uuid . "', '" . $case_uuid . "', '" . $activity_uuid . "', '" . addslashes($category) . "', -1, '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
	//$result_act = DB::runOrDie($sql);
try {
		$stmt = DB::run($sql);
	} catch(PDOException $e) {
		$error = array("error inserting case activity"=> array("text"=>$e->getMessage(), "sql"=>$sql));
			echo json_encode($error);
	}
	
	$attachments = $targetFile;
	$subject = $activity;
	
	$html_message = "New request from " . $_SESSION['user_customer_name'] . " via iKase:<br>";
	$html_message .= $file_url . "<br>";
	if ($targetFile!="") {
		$html_message .= "Attachment:<br>https://www.ikase.website/uploads/" . $_SESSION['user_customer_id'] . "/" . $case_id . "/" . urlencode($targetFile) . "<br>";
	}
	if ($specific_instructions!="") {
		$html_message .= "<br>" . $specific_instructions;
	}
	$text_message = str_replace("<br>", "\r\n", $html_message);
	
	$subject = "iKase Copy Request from " . $_SESSION['user_customer_name'];
	$to_name = "nick@kustomweb.com,latommy1@gmail.com,mdisorders@gmail.com";
	//$to_name = "nick@kustomweb.com";
	
	$url = "https://www.matrixdocuments.com/dis/sendit.php";
	$send_fields = array("from_name"=>"iKase System", "from_address"=>"demographics@ikase.website", "to_name"=>$to_name, "cc_name"=>"", "bcc_name"=>"", "html_message"=>urlencode($html_message), "text_message"=>urlencode($text_message), "subject"=>urlencode($subject), "attachments"=>"");
	//die(print_r($send_fields));
	$send_fields_string = "";
	foreach($send_fields as $key=>$value) { 
		$send_fields_string .= $key.'='.$value.'&'; 
	}
	rtrim($send_fields_string, '&');

	$timeout = 5;
	//open connection
	$ch = curl_init();
			
	//set the url, number of POST vars, POST data
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch, CURLOPT_HEADER, false); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_COOKIEFILE, "cookies.txt");
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_POST, count($send_fields_string));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $send_fields_string);
	//curl_setopt($ch,CURLOPT_FOLLOWLOCATION,TRUE);
	
	//execute post
	$result = curl_exec($ch);
	
	$blnSendEmail = ($result=="sent");
	
	if ($blnSendEmail) {
		$homepage .= " and sent";
	} else {
		echo 'Errors: ' . curl_errno($ch) . ' ' . curl_error($ch) . '<br><br>';
		die($result);
	}
	?>
    <script language="javascript">
	alert('<?php echo $homepage; ?>');
    </script>
	<?php
}
/*
$email_message=new email_message_class;

$to = "nick@kustomweb.com";
$from_name = "iKase";
$from_address = "donotreply@ikase.org";
$text_message = "https://www.ikase.org/reports/demographics_sheet.php?case_id=" . $case_id;
$subject = "New Order from iKase";
$recipients = "";
$attachments = "";

$arrEmailTo[$to] = $to;
//die(print_r($arrEmailTo));
$email_message->SetMultipleEncodedEmailHeader('To', $arrEmailTo);

$email_message->SetEncodedEmailHeader("From",$from_address,$from_name);
$email_message->SetEncodedEmailHeader("Reply-To",$from_address,$from_name);
$email_message->SetHeader("Sender",$from_address);

$error_delivery_name=$from_name;
$error_delivery_address=$from_address;
if(defined("PHP_OS")
&& strcmp(substr(PHP_OS,0,3),"WIN"))
	$email_message->SetHeader("Return-Path",$error_delivery_address);

$email_message->SetEncodedHeader("Subject",$subject);

//$text_message="Hello ".strtok($to_name," ")."\n\nThis message is just to let you know that the MIME E-mail message composing and sending PHP class is working as expected.\n\nYou may find attached to this messages a text file and and image file.\n\nThank you,\n$from_name";

$email_message->AddQuotedPrintableTextPart($email_message->WrapText($text_message));

$arrAttachments = explode(",", $attachments);
//die(print_r($arrAttachments ));				
foreach ($arrAttachments as $attachment_file) {
	if ($attachment_file!="") {
		$attachment=array(
			"FileName"=>$attachment_file,
			"Content-Type"=>"automatic/name",
			"Disposition"=>"attachment"
		);
		$email_message->AddFilePart($attachment);
	}
}


$error=$email_message->Send();

$blnSent = (!strcmp($error,""));
//if (mail($emails, $subject, $mail_values, $headers)) { 
if ($blnSent) {
	echo "sent";
} else {
	echo "not sent";
	print_r($error);
}
*/
?>

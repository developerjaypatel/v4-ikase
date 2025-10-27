<?php 
error_reporting(E_ALL);
error_reporting(error_reporting(E_ALL) & (-1 ^ E_DEPRECATED));

ini_set('SMTP','localhost'); 
ini_set('sendmail_from', 'admin@ikase.website'); 

include("../api/manage_session.php");
session_write_close();

if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
	header("location:../index.php");
	die();
}

ob_start();

include("../api/connection.php");
include ("../text_editor/ed/datacon.php");

include("../api/email_message.php");

$case_id = passed_var("case_id");
$emailit = passed_var("emailit");
$specific_instructions = passed_var("specific_instructions");

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
	$stmt->closeCursor(); $stmt = null; $db = null;
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

$result = mysql_query($query, $link) or die("unable to get injury<br>" . $query);
$numbs = mysql_numrows($result);

//echo $query . "<br>" . $numbs . "<br>";
//die();
/*
if (($_SESSION['user_customer_id']==1033)) { 
	echo $query . "<br>" . $numbs . "<br>";
}
*/
$attorney_name = "";
$worker_name = "";
for ($x=0;$x<$numbs;$x++) {
	$arrEmployerComm = array();
	$arrComm = array();
	$person_id = mysql_result($result, $x, "person_id");
	
	//die("did:" . $person_id);
	$case_id = mysql_result($result, $x, "case_id");
	$case_uuid = mysql_result($result, $x, "case_uuid");
	$person_uuid = mysql_result($result, $x, "person_uuid");
	$full_name = mysql_result($result, $x, "full_name");
	$language = mysql_result($result, $x, "language");
	$ssn = mysql_result($result, $x, "ssn");
	
	$employer = mysql_result($result, $x, "employer");
	$employer_address = mysql_result($result, $x, "employer_address");
	$employer_phone = mysql_result($result, $x, "employer_phone");
	$employer_fax = mysql_result($result, $x, "employer_fax");
	$employer_site = mysql_result($result, $x, "employer_site");
	$employer_email = mysql_result($result, $x, "employer_email");
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
	$intake_date = mysql_result($result, $x, "submittedOn");
	$first_name = mysql_result($result, $x, "first_name");
	$middle_name = mysql_result($result, $x, "middle_name");
	$last_name = mysql_result($result, $x, "last_name");
	$aka = mysql_result($result, $x, "aka");
	$preferred_name = mysql_result($result, $x, "preferred_name");
	$full_address = mysql_result($result, $x, "full_address");
	$street = mysql_result($result, $x, "street");
	if ($street!="") {
		$suite = mysql_result($result, $x, "suite");
		if ($suite!="") {
			$street .= ", " . $suite;
		}
		$city = mysql_result($result, $x, "city");
		$state = mysql_result($result, $x, "state");
		$zip = mysql_result($result, $x, "zip");
		
		$full_address = $street. ", " . $city . ", " . $state . " " . $zip;
	}
	$phone = mysql_result($result, $x, "phone");
	if ($phone!="") {
		$arrComm[] = "<strong>Phone:</strong> " . $phone;
	}
	$email = mysql_result($result, $x, "email");
	if ($email!="") {
		$arrComm[] = "<strong>Email:</strong> <a href='mailto:" . $email . "'>" . $email . "</a>";
	}
	$fax = mysql_result($result, $x, "fax");
	if ($fax!="") {
		$arrComm[] = "<strong>Fax:</strong> " . $fax;
	}
	$work_phone = mysql_result($result, $x, "work_phone");
	if ($work_phone!="") {
		$arrComm[] = "<strong>Work:</strong> " . $work_phone;
	}
	$cell_phone = mysql_result($result, $x, "cell_phone");
	if ($cell_phone!="") {
		$arrComm[] = "<strong>Cell:</strong> " . $cell_phone;
	}
	$work_email = mysql_result($result, $x, "work_email");
	$ssn_last_four = mysql_result($result, $x, "ssn_last_four");
	$dob = mysql_result($result, $x, "dob");
	$license_number = mysql_result($result, $x, "license_number");
	$title = mysql_result($result, $x, "title");
	$case_venue = mysql_result($result, $x, "case_venue");
	
	$case_number = mysql_result($result, $x, "case_number");
	$attorney = mysql_result($result, $x, "attorney");
	$worker = mysql_result($result, $x, "worker");
		
	//die($attorney . " - attorney_id");
	if ($attorney != "") {
		if (is_numeric($attorney)) {
			$query_att = "SELECT user_id, user_first_name, user_last_name 
						  FROM `ikase`.`cse_user`
						  WHERE user_id = " . $attorney;
			$result_att = mysql_query($query_att, $link) or die("unable to get attorney name<br>" . $query_att);
			$numbs_att = mysql_numrows($result_att);
			//echo $query . "<br>" . $numbs . "<br>";
	
			for ($i=0;$i<$numbs_att;$i++) {
				$att_first_name = mysql_result($result_att, $i, "user_first_name");
				$att_last_name = mysql_result($result_att, $i, "user_last_name");
			}
			$attorney_name = $att_first_name . " " . $att_last_name;
		} else {
			$attorney_name = $attorney;
		}
	}
	if ($worker != "") {
		if (is_numeric($worker)) {
			$query_work = "SELECT * 
					  FROM `ikase`.`cse_user`
					  WHERE user_id = " . $worker;
		} else {
			$query_work = "SELECT * 
					  FROM `ikase`.`cse_user`
					  WHERE nickname = '" . $worker . "'";
		}
		$result_work = mysql_query($query_work, $link) or die("unable to get worker name<br>" . $query_work);
		$numbs_work = mysql_numrows($result_work);
		//echo $query . "<br>" . $numbs . "<br>";

		for ($w=0;$w<$numbs_work;$w++) {
			$work_first_name = mysql_result($result_work, $w, "user_first_name");
			$work_last_name = mysql_result($result_work, $w, "user_last_name");
		}
		$worker_name = $work_first_name . " " . $work_last_name;
	}

	
	$occupation = mysql_result($result, $x, "occupation");
	$start_date = mysql_result($result, $x, "start_date");
	$end_date = mysql_result($result, $x, "end_date");
	$injury_location = mysql_result($result, $x, "injury_full_address");
	//$age = mysql_result($result, $x, "age");
	$age = "";
//	if ($age==0) {
		if (validateDate($dob)) {
			$age = age(date("m/d/Y", strtotime($dob)));
		}
//	}
	$explanation = mysql_result($result, $x, "explanation");
	$adj_number = mysql_result($result, $x, "adj_number");
	
	$arrLanguageOccupation = array();
	if ($language!="") {
		$arrLanguageOccupation[] = "Language: " . $language;
	}
	if ($occupation!="") {
		$arrLanguageOccupation[] = "Occupation: " . $occupation;
	}
}
$query_applicant_picture = "SELECT doc.*
	FROM  `cse_document` doc
	INNER JOIN  `cse_case_document` 
	ON  (`doc`.`document_uuid` =  `cse_case_document`.`document_uuid` AND `cse_case_document`.`attribute_1` = 'applicant_picture')
	INNER JOIN  `cse_case` 
	ON (  `cse_case_document`.`case_uuid` =  `cse_case`.`case_uuid` 
	AND  `cse_case`.`case_id` =" . $case_id . ") 
	WHERE doc.customer_id = " . $_SESSION['user_customer_id'] . "
	AND doc.deleted =  'N'
	ORDER BY doc.document_date DESC, doc.document_id DESC";

$result_applicant_picture = mysql_query($query_applicant_picture, $link) or die("unable to get pic<br>" . mysql_error() . "<br>" . $query_applicant_picture);
//die($query_applicant_picture);
$numbs_applicant_picture = mysql_numrows($result_applicant_picture);

$document_filename = "";

if ($numbs_applicant_picture > 0) {
	$document_filename = mysql_result($result_applicant_picture, 0, "document_filename");
}

$query_noemp = "SELECT DISTINCT partie.corporation_id, partie.corporation_uuid, partie.company_name, partie.preferred_name, partie.full_address, cpt.partie_type, partie.phone partie_phone, partie.fax partie_fax, partie.full_name partie_full_name, partie.company_site partie_company_site, partie.email partie_email, cpt.employee_title partie_employee_title, partie.employee_phone partie_employee_phone,
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
WHERE ccase.case_id = '" . $case_id . "'
AND partie.deleted = 'N'
AND ccase.customer_id = " . $_SESSION['user_customer_id'] . "
ORDER BY cpt.sort_order, partie.company_name ";
//echo $query_noemp;
//die();
$result_noemp = mysql_query($query_noemp, $link) or die("unable to get no emp<br>" . $query_noemp);
$numbs_noemp = mysql_numrows($result_noemp);

for ($int=0;$int<$numbs_noemp;$int++) {
	$arrPartieComm = array();
	$arrPartieEmployeeComm = array();
	$partie_id = mysql_result($result_noemp, $int, "corporation_id");
	$partie_uuid = mysql_result($result_noemp, $int, "corporation_uuid");
	$partie_type = mysql_result($result_noemp, $int, "partie_type");
	$doctor_type = mysql_result($result_noemp, $int, "doctor_type");
	$partie_name = mysql_result($result_noemp, $int, "company_name");
	$partie_preferred_name = mysql_result($result_noemp, $int, "preferred_name");
	$partie_address = mysql_result($result_noemp, $int, "full_address");
	$partie_phone = mysql_result($result_noemp, $int, "partie_phone");
	$partie_fax = mysql_result($result_noemp, $int, "partie_fax");
	$partie_full_name = mysql_result($result_noemp, $int, "partie_full_name");
	$partie_company_site = mysql_result($result_noemp, $int, "partie_company_site");
	$partie_email = mysql_result($result_noemp, $int, "partie_email");
	$partie_employee_title = mysql_result($result_noemp, $int, "partie_employee_title");
	$partie_employee_email = mysql_result($result_noemp, $int, "partie_employee_email");
	$partie_employee_phone = mysql_result($result_noemp, $int, "partie_employee_phone");
	$partie_employee_fax = mysql_result($result_noemp, $int, "partie_employee_fax");
	
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
		WHERE corporation_uuid = '" . $partie_uuid . "'
		AND customer_id = " . $_SESSION["user_customer_id"];
		
		//die($query_carrier . "\r\n");
		
		$result_carrier = mysql_query($query_carrier, $link) or die("unable to get inj corp<br>" . $query_carrier);
		$numbs_carrier = mysql_numrows($result_carrier);
		if ($numbs_carrier > 0) {
			$partie_injury_uuid = mysql_result($result_carrier, 0, "injury_uuid");
		}
	}
	$arrPartieInfo[] = array("partie_id"=>$partie_id, "partie_uuid"=>$partie_uuid, "partie_type"=>$partie_type, "doctor_type"=>$doctor_type, "partie_name"=>$partie_name, "partie_address"=>$partie_address, "partie_phone"=>$partie_phone, "partie_fax"=>$partie_fax, "partie_full_name"=>$partie_full_name, "partie_preferred_name"=>$partie_preferred_name, "partie_company_site"=>$partie_company_site, "partie_email"=>$partie_email, "partie_employee_title"=>$partie_employee_title, "partie_comm"=>implode(" | ", $arrPartieComm), "partie_employee_comm"=>implode(" | ", $arrPartieEmployeeComm), "partie_injury_uuid"=>$partie_injury_uuid);	
}

$query_bod = "SELECT DISTINCT ci.injury_id, bp.*, cib.attribute bodyparts_number, ccase.case_id, ccase.case_uuid 
			FROM `cse_bodyparts` bp
			INNER JOIN cse_injury_bodyparts cib
			ON bp.bodyparts_uuid = cib.bodyparts_uuid
			INNER JOIN cse_injury ci
			ON (cib.injury_uuid = ci.injury_uuid)
			INNER JOIN cse_case_injury cci
			ON ci.injury_uuid = cci.injury_uuid
			INNER JOIN cse_case ccase
			ON (cci.case_uuid = ccase.case_uuid
			AND `ccase`.`case_id` = '" . $case_id . "')
			WHERE 1
			AND cci.customer_id = " . $_SESSION['user_customer_id'] . "
			AND cci.deleted = 'N'
			AND cib.deleted = 'N'
			ORDER BY `code` ASC";
//die($query_bod);			
$result_bod = mysql_query($query_bod, $link) or die("unable to get bod<br>" . $query_bod);
$numbs_bod = mysql_numrows($result_bod);
$arrBodInfo = array();
$arrBodyListing = array();
for ($int_bod=0;$int_bod<$numbs_bod;$int_bod++) {
	$body_injury_id = mysql_result($result_bod, $int_bod, "injury_id");
	$code = mysql_result($result_bod, $int_bod, "code");
	$description = mysql_result($result_bod, $int_bod, "description");
	$arrDescription = explode(" - ", $description);
	$description = $arrDescription[0];
	$arrBodInfo[$body_injury_id][] = array("code"=>$code, "description"=>$description);	
	$arrBodyListing[$body_injury_id][] = $code . " - " . $description;	
}

$sol_date = new DateTime("+12 months $intake_date");
//die($sol_date->format('Y-m-d') . "\n");

//injuries
$sql = "SELECT `inj`.`injury_id`, `inj`.`injury_uuid`, `injury_number`, `inj`.`adj_number`, `inj`.`type`, `occupation`, `start_date`, `end_date`, `ct_dates_note`, `body_parts`, `statute_limitation`, `explanation`, `inj`.`full_address`, `inj`.`street`, `inj`.`city`, `inj`.`state`, `inj`.`zip`, `inj`.`suite`, `inj`.`customer_id`, `inj`.`deleted`, IFNULL(`cin`.`alternate_policy_number`, '') addl_claim_number
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
$sql .= " WHERE 1
AND inj.customer_id = " . $_SESSION['user_customer_id'] . "
AND ccase.deleted = 'N'
AND inj.deleted = 'N'";
$result_inj = mysql_query($sql, $link) or die("unable to get inj<br>" . $sql);
$numbs_inj = mysql_numrows($result_inj);

$arrInjuries = array();
$arrADJs = array();
for ($int_inj=0;$int_inj<$numbs_inj;$int_inj++) {
	$injury_id = mysql_result($result_inj, $int_inj, "injury_id");
	$injury_uuid = mysql_result($result_inj, $int_inj, "injury_uuid");
	$adj_number = mysql_result($result_inj, $int_inj, "adj_number");
	$addl_claim_number = mysql_result($result_inj, $int_inj, "addl_claim_number");
	
	if ($addl_claim_number!="") {
		$addl_claim_number = "<strong>Addl Claim #:</strong>" . $addl_claim_number;
	}
	$start_date = mysql_result($result_inj, $int_inj, "start_date");
	$end_date = mysql_result($result_inj, $int_inj, "end_date");
	$injury_location = mysql_result($result_inj, $int_inj, "full_address");
	$explanation = mysql_result($result_inj, $int_inj, "explanation");
	$injury_occupation = mysql_result($result_inj, $int_inj, "occupation");
	$end_date = mysql_result($result_inj, $int_inj, "end_date");
	$ct = date("m/d/Y", strtotime($start_date));
	if ($end_date!="0000-00-00") {
		$ct .= " - " . date("m/d/Y", strtotime($end_date)) . " CT";
	}
	$arrADJs[] = $adj_number;
	$arrInjuries[] = array("injury_id"=>$injury_id, "injury_uuid"=>$injury_uuid, "ct"=>$ct, "explanation"=>$explanation, "location"=>$injury_location, "occupation"=>$injury_occupation, "adj_number"=>$adj_number, "addl_claim_number"=>$addl_claim_number);
}
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=0.789, maximum-scale=1.0, user-scalable=0">
<style type="text/css">
td {
	color:#000000;
	line-height:95%;
}
.highlight {
	 background:#9FF
}

</style>
<link rel="stylesheet" href="../css/bootstrap.3.0.3.min.css">

<!-- Optional theme -->
<link rel="stylesheet" href="../css/bootstrap-theme.min.css">
<title>Demographics Report</title>

<script language="javascript">
var openSendForm = function() {
	var picture_holder = document.getElementById("picture_holder");
	if (typeof picture_holder != "undefined" && picture_holder != null) {
		picture_holder.style.display = "none";
	}
	document.getElementById("form_holder").style.display = "";
}
</script>
</head>

<body style="color:#EDEDED;">
<table width="97%" border="0" align="center" cellpadding="3" cellspacing="0" style="margin-top:0px">
  <tr>
  	<td width="16%" valign="top"><img src="https://www.ikase.website/img/ikase_logo_login.png" height="32" width="77"></td>
    <td colspan="3" align="left" style="font-family:Arial, Helvetica, sans-serif; font-weight:bold; font-size:1.5em">
    	DEMOGRAPHICS  PAGE
    </td>
  </tr>
  <tr>
    <td width="16%">&nbsp;</td>
    <td width="28%"><em>as of <?php echo date("m/d/y g:iA"); ?></em></td>
    <td colspan="2" align="right">&nbsp;
    	
    </td>
  </tr>
  <tr>
    <td colspan="4">
        <hr/>
    </td>
  </tr>
  <?php if ($specific_instructions!="") { ?>
  <tr>
    <td valign="top" nowrap><strong>Specific Instr.</strong></td>
    <td colspan="3"><?php echo $specific_instructions; ?></td>
  </tr>
  <tr valign="top">
    <td colspan="4"><hr /></td>
  </tr>
  <?php } ?>
  <tr>
    <td valign="top" nowrap><strong>Case #</strong></td>
    <td colspan="3"><?php echo $case_number; ?></td>
  </tr>
  <tr>
    <td valign="top" nowrap><strong>Case Name</strong></td>
    <td colspan="3">
      <?php if ($document_filename!="") { ?>
      <?php } ?>
      
    <?php 
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
	echo $full_name . " vs " . $employer; ?>   	</td>
  </tr>
   <tr>
    <td valign="top" nowrap><strong>ADJ #</strong></td>
    <td colspan="3" valign="top" style="margin-top:0px"><?php echo implode("; " , $arrADJs); ?><strong>&nbsp;&nbsp;
    </td>
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
  </tr>
  
    <tr>
      <td valign="top" nowrap class="highlight"><strong>Applicant</strong></td>
    <td colspan="3" nowrap class="highlight"><?php echo $full_name; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo "<strong>Age</strong>: " . $age; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br><br>
      <strong>SSN:</strong>
      <?php 
	if (strlen($ssn)==9) {
		echo substr($ssn, 0, 3) . "-" . substr($ssn, 3, 2) . "-" . substr($ssn, 5, 4); 
	} else {
		echo $ssn;
	}
	?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>DOB:</strong><?php if ($dob!="" && $dob!="0000-00-00") {
		echo date("m/d/Y", strtotime($dob));
	}// . " (" . $age . " years old)"; ?><br/><br/>
    </td>
  </tr>
  <tr>
    <td valign="top" nowrap <?php if ($full_address != "") { ?> class="highlight"<?php } ?>>&nbsp;</td>
    <td colspan="3" <?php if ($full_address != "") { ?> class="highlight"<?php } ?>><?php echo $full_address; ?></td>
  </tr>
  <tr>
    <td valign="top" nowrap>&nbsp;</td>
    <td colspan="3"><?php echo ($arrComm && is_array($arrComm))?implode(" | ",$arrComm):$arrComm; ?></td>
  </tr>
  <tr>
    <td valign="top" nowrap>&nbsp;</td>
    <td colspan="3"><?php echo ($arrLanguageOccupation && is_array($arrLanguageOccupation))?implode("&nbsp;|&nbsp;", $arrLanguageOccupation):$arrLanguageOccupation; ?></td>
  </tr>
  <tr>
    <td valign="top" nowrap><strong>Venue</strong></td>
    <td colspan="3"><?php echo $case_venue; ?> 
   </td>
  </tr>
  <tr>
    <td valign="top" nowrap><strong>Judge</strong></td>
    <td colspan="3"><?php if ($judge!="") { ?>
      <?php echo $judge; ?>
	  <?php } ?>
   </td>
  </tr>
  <tr valign="top">
    <td colspan="4"><hr /></td>
  </tr>
  <tr>
  	<td><strong>Firm</strong></td>
  	<td colspan="3"><?php echo $_SESSION['user_customer_name']; ?></td>
  </tr>
  <tr>
  	<td>&nbsp;</td>
    <td colspan="3"><?php echo str_replace("<br>", ", ", $_SESSION['user_customer_address']); ?></td>
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
    <td colspan="3"><?php echo ($arrEmployerComm && is_array($arrEmployerComm))?implode(" | ", $arrEmployerComm):$arrEmployerComm; ?></td>
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
			$queryspec = "SELECT adhoc_value  FROM `cse_corporation_adhoc` 
			WHERE `adhoc` = 'claim_number'
			AND corporation_uuid = '" . $party_info["partie_uuid"] . "'";
			
			$resultspec = mysql_query($queryspec, $r_link) or die("unable to get specialty");
			$numberspec = mysql_numrows($resultspec);
			if ($numberspec > 0) {
				$claim_number = mysql_result($resultspec, 0, "adhoc_value");
				
				$partie_injury_uuid = $party_info["partie_injury_uuid"];
				//die($partie_injury_uuid  . "cl" . $claim_number);
				
				if ($partie_injury_uuid != "") {
					$arrInjuryClaim[$partie_injury_uuid][] = $claim_number;
				}
			}
		}?>
  <tr>
    <td valign="top"><strong><?php if ($party_info["partie_type"] == "Third Party Administration ") { echo "Third PA"; } else { echo $party_info["partie_type"]; } ?></strong></td>
    <td colspan="3"><strong><?php echo $party_info["partie_name"]; ?></strong></td>
  </tr>
   <tr>
    <td valign="top" nowrap class="<?php if ($claim_number!="") { ?>highlight<?php } ?>"><?php if ($claim_number!="") { ?><strong>Claim #</strong><?php } ?></td>
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
    <td colspan="3"><?php echo date("m/d/Y", strtotime($intake_date)); ?></td>
  </tr>
  <tr>
    <td valign="top" nowrap><strong>SOL Date</strong></td>
    <td colspan="3"><?php echo $sol_date->format("m/d/Y") . "\n"; ?></td>
  </tr>
  <tr>
    <td valign="top" nowrap><strong>Attorney</strong></td>
    <td colspan="3"><?php echo $attorney_name; ?></td>
  </tr>
  <tr>
    <td valign="top" nowrap><strong>Coordinator</strong></td>
    <td colspan="3"><?php echo $worker_name; ?></td>
  </tr>
  <tr valign="top">
    <td colspan="4"><hr /></td>
  </tr>
  <?php 
  //die(print_r($arrInjuries));
  foreach($arrInjuries  as $injury) {
	  //die(print_r($injury));
	  if (!isset($arrInjuryClaim[$injury["injury_uuid"]])) {
		  $arrInjuryClaim[$injury["injury_uuid"]]= array();
	  }
	  ?>
  <tr>
    <td valign="top" nowrap><strong>Date of Injury</strong></td>
    <td colspan="3"><?php echo $injury["ct"]; ?></td>
  </tr>
  <tr>
    <td valign="top" nowrap><strong>ADJ</strong></td>
    <td colspan="3"><?php echo $injury["adj_number"]; ?></td>
  </tr>
  <tr>
    <td valign="top" nowrap><strong>Place of Injury</strong></td>
    <td colspan="3"><?php echo $injury["location"]; ?></td>
  </tr>
  <tr>
    <td valign="top" nowrap><strong>Occupation</strong></td>
    <td colspan="3"><?php echo $injury["occupation"]; ?></td>
  </tr>
  <tr>
  	<td valign="top"><strong>Claim Number</strong></td>
    <td colspan="3"><?php echo implode("; ", $arrInjuryClaim[$injury["injury_uuid"]]); ?>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $injury["addl_claim_number"]; ?></td>
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
  
  <?php foreach($arrPartieInfo as $party_info) { 
  	
  	if ($party_info["partie_type"]!="Insurance Carrier" && $party_info["partie_type"]!="Defense Attorney" && $party_info["partie_type"]!="Venue") {
	if ($party_info["partie_type"]=="Defendant") {
		continue;
	}
	$specialty = "";
	if ($party_info["partie_type"]=="Medical Provider") {
		//need to see if there is a specialty
		$queryspec = "SELECT adhoc_value  FROM `cse_corporation_adhoc` 
		WHERE `adhoc` = 'specialty'
		AND corporation_uuid = '" . $party_info["partie_uuid"] . "'";
		$resultspec = mysql_query($queryspec, $r_link) or die("unable to get specialty");
		$numberspec = mysql_numrows($resultspec);
		if ($numberspec > 0) {
			$specialty = mysql_result($resultspec, 0, "adhoc_value");
			$specialty = ucwords($specialty);
		}
	}
	?>
  <tr>
    <td valign="top" nowrap><strong><?php echo $party_info["partie_type"]; ?></strong></td>
    <td colspan="3" nowrap>
    	<?php if ($party_info["partie_type"]=="Medical Provider") { ?>
        <div style="float:right">
        	<?php echo $party_info["doctor_type"]; ?>
        </div>
        <?php } ?>
        <strong><?php echo $party_info["partie_name"]; ?></strong>
    </td>
  </tr>
  <?php if (trim($party_info["partie_full_name"])!="") { ?>
   <tr>
    <td valign="top" nowrap>&nbsp;</td>
    <td colspan="3"><?php echo $party_info["partie_employee_title"]; ?>: <?php echo $party_info["partie_full_name"]; ?></td>
  </tr>
  <?php } ?>
  <?php if ($party_info["partie_address"]!="") { ?>
   <tr>
    <td valign="top" nowrap>&nbsp;</td>
    <td colspan="3"><?php echo $party_info["partie_address"]; ?></td>
  </tr>
  <?php } ?>
  <?php if ($party_info["partie_comm"]!="") { ?>
  <tr>
    <td valign="top" nowrap>&nbsp;</td>
    <td colspan="3"><?php echo $party_info["partie_comm"]; ?></td>  
  </tr>
  <?php } ?>
  <?php if ($specialty!="") { ?>
  <tr>
    <td valign="top" nowrap><strong>Specialty</strong></td>
    <td colspan="3"><? echo $specialty; ?></td>  
  </tr>
  <?php } ?>
  <tr>
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
$result_prior = mysql_query($sql, $link) or die("unable to get prior treatments<br>" . $sql);
$numbs_prior = mysql_numrows($result_prior);

if ($numbs_prior > 0) { ?>
	<tr>
    <td valign="top" nowrap colspan="4"><strong>Prior Medical Treatment</strong></td>
  </tr>
  <tr>
    <td valign="top" nowrap colspan="4"><hr></td>
  </tr>
<?php }
for ($int_prior=0;$int_prior<$numbs_prior;$int_prior++) {
	$partie_name = mysql_result($result_prior, $int_prior, "company_name");
	$partie_address = mysql_result($result_prior, $int_prior, "full_address");
	$partie_phone = mysql_result($result_prior, $int_prior, "phone");
	$partie_fax = mysql_result($result_prior, $int_prior, "fax");
	$partie_company_site = mysql_result($result_prior, $int_prior, "company_site");
	$partie_email = mysql_result($result_prior, $int_prior, "email");
	$copying_instructions = mysql_result($result_prior, $int_prior, "copying_instructions");
	
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
   <tr>
    <td valign="top" nowrap>&nbsp;</td>
    <td colspan="3"><?php echo $partie_name; ?></td>
  </tr>
  <?php } ?>
  <?php if ($partie_address!="") { ?>
   <tr>
    <td valign="top" nowrap>&nbsp;</td>
    <td colspan="3"><?php echo $partie_address; ?></td>
  </tr>
  <?php } ?>
  <?php if ($partie_comm!="") { ?>
  <tr>
    <td valign="top" nowrap>&nbsp;</td>
    <td colspan="3"><?php echo $partie_comm; ?></td>  
  </tr>
  <?php } ?>
  <tr>
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
  <tr>
    <td valign="top" nowrap>&nbsp;</td>
    <td colspan="3">Other: <?php echo $arrCopying[1]; ?></td>  
  </tr>
  <?php } ?>
  <?php if ($arrCopying[3]!="") { ?>
  <tr>
    <td valign="top" nowrap>&nbsp;</td>
    <td colspan="3">Special Instructions: <?php echo $arrCopying[3]; ?></td>  
  </tr>
  <?php } ?>
  <tr>
    <td colspan="3" valign="top" nowrap><hr></td>  
  </tr>
<?php } ?>
</table>
</body>
</html>
<?php
$content = ob_get_contents();
ob_end_clean();
echo $content;

if ($emailit!="y") {
	die();
}
if ($emailit=="y") {
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
		//die("export to matrix");
		include("get_kase_info.php");
		$operation = "exported";
		$homepage = $operation;
		echo $operation;
		//echo print_r($parties);
		//die(print_r($kase));
	} else {
		//die("send to matrix");
		$filename = "https://www.matrixdocuments.com/dis/pws/manage/request/forward_ikase.php?cus_id=" . $_SESSION['user_customer_id'] . "&case_id=" . $case_id . "&case=" . urlencode($first_name . " " . $last_name . " vs " . $employer) . "&attachment=" . $targetFile . "&specific_instructions=" . urlencode($specific_instructions);
		//echo $filename;
		//echo "<br><br>";
		$homepage = file_get_contents($filename);
		
		echo $homepage;
		
		$operation = $homepage;
	}
	$activity = "Demographics Sheet Sent to Matrix By " . $_SESSION["user_name"];
	$category = "Matrix Referral " . $operation;
	
	$activity_uuid = uniqid("KS", false);
	$sql = "INSERT INTO cse_activity (`activity_uuid`, `activity`, `activity_category`, `activity_user_id`, `customer_id`)
	VALUES ('" . $activity_uuid . "', '" . addslashes($activity) . "', '" . addslashes($category) . "', '" . $_SESSION['user_plain_id'] . "', " . $_SESSION['user_customer_id'] . ")";
	//$result_act = mysql_query($sql, $link) or die("unable to set activity<br>" . $sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
$stmt = null; $db = null;
	} catch(PDOException $e) {
		$error = array("error inserting activity"=> array("text"=>$e->getMessage(), "sql"=>$sql));
			echo json_encode($error);
	}
	$last_updated_date = date("Y-m-d H:i:s");
	$case_activity_uuid = uniqid("KA", false);
			
	$sql = "INSERT INTO cse_case_activity (`case_activity_uuid`, `case_uuid`, `activity_uuid`, `attribute`, `case_track_id`, `last_updated_date`, `last_update_user`, `customer_id`)
	VALUES ('" . $case_activity_uuid . "', '" . $case_uuid . "', '" . $activity_uuid . "', '" . addslashes($category) . "', -1, '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
	//$result_act = mysql_query($sql, $link) or die("unable to set case activity<br>" . $sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
$stmt = null; $db = null;
	} catch(PDOException $e) {
		$error = array("error inserting case activity"=> array("text"=>$e->getMessage(), "sql"=>$sql));
			echo json_encode($error);
	}
	?>
    <script language="javascript">
	alert('<?php echo $homepage; ?>');
    </script>
	<?php
}

?>
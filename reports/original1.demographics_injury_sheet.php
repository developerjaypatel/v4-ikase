<?php 
error_reporting(E_ALL);
error_reporting(error_reporting(E_ALL) & (-1 ^ E_DEPRECATED));

session_start();

if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
	header("location:index.html");
	die();
}

ob_start();

include ("../text_editor/ed/functions.php");
include ("../text_editor/ed/datacon.php");
include("../api/email_message.php");


if(!empty($_GET['key'])){
	//check the DB for the key
	$resCheck = mysql_query("SELECT * FROM ikase.cse_downloads WHERE downloadkey = '".mysql_real_escape_string($_GET['key'])."' LIMIT 1");
	$arrCheck = mysql_fetch_assoc($resCheck);
	die(print_r($arrCheck));
} else {
	die();
}
//$injury_id = passed_var("injury_id");
$specific_instructions = passed_var("specific_instructions");

if ($emailit=="") {
	$emailit = "N";
}
if (!is_numeric($injury_id)) {
	die();
}



$query = "SELECT pers.*, ccase.*, ccpers.*, cinj.full_address injury_full_address, cinj.start_date, cinj.end_date, cinj.explanation, cinj.occupation, cven.venue_abbr case_venue, 
employer.company_name employer , employer.full_address employer_address, ccase.adj_number, employer.employee_phone employer_phone, employer.employee_fax employer_fax, employer.email employer_email, employer.company_site employer_site
FROM cse_case ccase
INNER JOIN cse_case_person ccpers
ON ccase.case_uuid = ccpers.case_uuid
INNER JOIN cse_person pers 
ON ccpers.person_uuid = pers.person_uuid
LEFT OUTER JOIN `cse_case_corporation` ccorp
ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
LEFT OUTER JOIN `cse_corporation` employer
ON ccorp.corporation_uuid = employer.corporation_uuid
LEFT OUTER JOIN cse_case_venue ccven 
ON ccpers.case_uuid = ccven.case_uuid
LEFT OUTER JOIN cse_venue cven
ON ccven.venue_uuid = cven.venue_uuid
INNER JOIN cse_case_injury ccinj 
ON ccase.case_uuid = ccinj.case_uuid
INNER JOIN cse_injury cinj 
ON ccinj.injury_uuid = cinj.injury_uuid
WHERE cinj.injury_id = '" . $injury_id . "'
AND ccase.customer_id = " . $_SESSION['user_customer_id'] . "
ORDER BY injury_id DESC LIMIT 1";

$result = mysql_query($query, $link) or die("unable to get kase<br>" . $query);
$numbs = mysql_numrows($result);
//echo $query . "<br>" . $numbs . "<br>";

$attorney_name = "";
$worker_name = "";
for ($x=0;$x<$numbs;$x++) {
	$arrEmployerComm = array();
	$arrComm = array();
	$person_id = mysql_result($result, $x, "person_id");
	
	//die("did:" . $person_id);
	$case_id = mysql_result($result, $x, "case_id");
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
		$arrEmployerComm[] = "Phone: " . $employer_phone;
	}
	if ($employer_fax!=""){
		$arrEmployerComm[] = "Fax: " . $employer_fax;
	}
	if ($employer_site!=""){
		$arrEmployerComm[] = 'Website: <a href="http://' . str_replace("http://", "", $employer_site) . '" target="_blank">' . $employer_site . '</a>';
	}
	if ($employer_email!=""){
		$arrEmployerComm[] = "Email: <a href='mailto:" . $employer_email . "'>" . $employer_email . "</a>";
	}
	$intake_date = mysql_result($result, $x, "submittedOn");
	$first_name = mysql_result($result, $x, "first_name");
	$last_name = mysql_result($result, $x, "last_name");
	$aka = mysql_result($result, $x, "aka");
	$preferred_name = mysql_result($result, $x, "preferred_name");
	$full_address = mysql_result($result, $x, "full_address");
	$street = mysql_result($result, $x, "street");
	$city = mysql_result($result, $x, "city");
	$state = mysql_result($result, $x, "state");
	$phone = mysql_result($result, $x, "phone");
	if ($phone!="") {
		$arrComm[] = "Phone: " . $phone;
	}
	$email = mysql_result($result, $x, "email");
	if ($email!="") {
		$arrComm[] = "Email: <a href='mailto:" . $email . "'>" . $email . "</a>";
	}
	$fax = mysql_result($result, $x, "fax");
	if ($fax!="") {
		$arrComm[] = "Fax: " . $fax;
	}
	$work_phone = mysql_result($result, $x, "work_phone");
	if ($work_phone!="") {
		$arrComm[] = "Work: " . $work_phone;
	}
	$cell_phone = mysql_result($result, $x, "cell_phone");
	if ($cell_phone!="") {
		$arrComm[] = "Cell: " . $cell_phone;
	}
	$work_email = mysql_result($result, $x, "work_email");
	$ssn_last_four = mysql_result($result, $x, "ssn_last_four");
	$dob = mysql_result($result, $x, "dob");
	$license_number = mysql_result($result, $x, "license_number");
	$title = mysql_result($result, $x, "title");
	$venue = mysql_result($result, $x, "case_venue");
	
	$case_number = mysql_result($result, $x, "case_number");
	$attorney = mysql_result($result, $x, "attorney");
	$worker = mysql_result($result, $x, "worker");
		
	//die($attorney . " - attorney_id");
	if ($attorney != "") {
		if (is_numeric($attorney)) {
			$query_att = "SELECT user_id, user_first_name, user_last_name 
						  FROM cse_user
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
					  FROM cse_user
					  WHERE user_id = " . $worker;
		} else {
			$query_work = "SELECT * 
					  FROM cse_user
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

//$first_name . " " . $last_name;
$query_applicant_picture = "SELECT doc.`document_id` , doc.`document_uuid` , doc.`parent_document_uuid` , 
	REPLACE(  `document_name` ,  '/home/cstmwb/public_html/autho/web/fileupload/server/php/file_container/" . $case_id . "/',  '' ) `document_name` ,  
	IF (DATE_FORMAT(document_date, '%m/%d/%Y %l:%i%p') IS NULL, '', DATE_FORMAT(document_date, '%m/%d/%Y %l:%i%p'))`document_date` , 
	IF (DATE_FORMAT(received_date, '%m/%d/%Y %l:%i%p') IS NULL, '', DATE_FORMAT(received_date, '%m/%d/%Y %l:%i%p'))`received_date` , doc.`source`, 	
	`document_filename` ,  `document_extension`, `thumbnail_folder` ,  `description` ,  IF (`description_html` IS NULL, '', `description_html`) `description_html` ,  `type` ,  `verified` , doc.`deleted` , doc.`document_id`  `id` , doc.`document_uuid`  `uuid`, doc.customer_id, cu.user_name,  `cse_case`.`case_uuid`, `cse_case`.`case_id`
	FROM  `cse_document` doc
	INNER JOIN  `cse_case_document` ON  (`doc`.`document_uuid` =  `cse_case_document`.`document_uuid` AND `cse_case_document`.`attribute_1` = 'applicant_picture')
	INNER JOIN  `cse_user` cu ON cse_case_document.last_update_user = cu.user_uuid 
	INNER JOIN  `cse_case` ON (  `cse_case_document`.`case_uuid` =  `cse_case`.`case_uuid` 
	AND  `cse_case`.`case_id` =" . $case_id . ") 
	WHERE doc.customer_id = " . $_SESSION['user_customer_id'] . "
	AND doc.deleted =  'N'
	AND `cse_case_document`.deleted =  'N'
	ORDER BY doc.document_date DESC, doc.document_id DESC";
$result_applicant_picture = mysql_query($query_applicant_picture, $link) or die("unable to get kase<br>" . $query_applicant_picture);
$numbs_applicant_picture = mysql_numrows($result_applicant_picture);
$document_filename = "";
for ($inter=0;$inter<$numbs_applicant_picture;$inter++) {
	$document_filename = mysql_result($result_applicant_picture, $inter, "document_filename");
}
	
$query_noemp = "SELECT partie.corporation_id, partie.corporation_uuid, partie.company_name, partie.preferred_name, partie.full_address, cpt.partie_type, partie.phone partie_phone, partie.fax partie_fax, partie.full_name partie_full_name, partie.company_site partie_company_site, partie.email partie_email, cpt.employee_title partie_employee_title, ccase.adj_number
FROM cse_case ccase
INNER JOIN `cse_case_corporation` ccorp
ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute != 'employer' AND ccorp.deleted = 'N')
INNER JOIN `cse_corporation` partie
ON ccorp.corporation_uuid = partie.corporation_uuid
INNER JOIN `cse_partie_type` cpt
ON partie.type = cpt.blurb
WHERE ccase.case_id = '" . $case_id . "'
AND partie.deleted = 'N'
AND ccase.customer_id = " . $_SESSION['user_customer_id'] . "
ORDER BY cpt.sort_order, partie.company_name ";
//echo $query_noemp;
$result_noemp = mysql_query($query_noemp, $link) or die("unable to get kase<br>" . $query_noemp);
$numbs_noemp = mysql_numrows($result_noemp);

for ($int=0;$int<$numbs_noemp;$int++) {
	$arrPartieComm = array();
	$partie_id = mysql_result($result_noemp, $int, "corporation_id");
	$partie_uuid = mysql_result($result_noemp, $int, "corporation_uuid");
	$partie_type = mysql_result($result_noemp, $int, "partie_type");
	$partie_name = mysql_result($result_noemp, $int, "company_name");
	$partie_preferred_name = mysql_result($result_noemp, $int, "preferred_name");
	$partie_address = mysql_result($result_noemp, $int, "full_address");
	$partie_phone = mysql_result($result_noemp, $int, "partie_phone");
	$partie_fax = mysql_result($result_noemp, $int, "partie_fax");
	$partie_full_name = mysql_result($result_noemp, $int, "partie_full_name");
	$partie_company_site = mysql_result($result_noemp, $int, "partie_company_site");
	$partie_email = mysql_result($result_noemp, $int, "partie_email");
	$partie_employee_title = mysql_result($result_noemp, $int, "partie_employee_title");
	
	if ($partie_phone!=""){
		$arrPartieComm[] = "Phone: " . $partie_phone;
	}
	if ($partie_fax!=""){
		$arrPartieComm[] = "Fax: " . $partie_fax;
	}
	if ($partie_company_site!=""){
		$arrPartieComm[] = 'Website: <a href="http://' . str_replace("http://", "", $partie_company_site) . '" target="_blank">' . $partie_company_site . '</a>';
	}
	if ($partie_email!=""){
		$arrPartieComm[] = "Email: <a href='mailto:" . $partie_email . "'>" . $partie_email . "</a>";
	}
	$arrPartieInfo[] = array("partie_id"=>$partie_id, "partie_uuid"=>$partie_uuid, "partie_type"=>$partie_type, "partie_name"=>$partie_name, "partie_address"=>$partie_address, "partie_phone"=>$partie_phone, "partie_fax"=>$partie_fax, "partie_full_name"=>$partie_full_name, "partie_preferred_name"=>$partie_preferred_name, "partie_company_site"=>$partie_company_site, "partie_email"=>$partie_email, "partie_employee_title"=>$partie_employee_title, "partie_comm"=>implode(" | ", $arrPartieComm));	
}

$query_bod = "SELECT DISTINCT bp.*, cib.attribute bodyparts_number, ccase.case_id, ccase.case_uuid 
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
$result_bod = mysql_query($query_bod, $link) or die("unable to get kase<br>" . $query_bod);
$numbs_bod = mysql_numrows($result_bod);

for ($int_bod=0;$int_bod<$numbs_bod;$int_bod++) {
	$code = mysql_result($result_bod, $int_bod, "code");
	$description = mysql_result($result_bod, $int_bod, "description");
	
	$arrBodInfo[] = array("code"=>$code, "description"=>$description);	
}

$sol_date = new DateTime("+12 months $intake_date");
//die($sol_date->format('Y-m-d') . "\n");

//injuries
$sql = "SELECT `inj`.`injury_id`, `inj`.`injury_uuid`, `injury_number`, `inj`.`adj_number`, `inj`.`type`, `occupation`, `start_date`, `end_date`, `ct_dates_note`, `body_parts`, `statute_limitation`, `explanation`, `inj`.`full_address`, `inj`.`street`, `inj`.`city`, `inj`.`state`, `inj`.`zip`, `inj`.`suite`, `inj`.`customer_id`, `inj`.`deleted`
FROM `cse_injury` inj 
INNER JOIN cse_case_injury ccinj
ON inj.injury_uuid = ccinj.injury_uuid
INNER JOIN cse_case ccase
ON (ccinj.case_uuid = ccase.case_uuid";
//$sql .= " AND `ccase`.`case_id` = '" . $case_id . "')";
$sql .= " AND `inj`.`injury_id` = '" . $injury_id . "')";
$sql .= " WHERE 1
AND inj.customer_id = " . $_SESSION['user_customer_id'] . "
AND ccase.deleted = 'N'
AND inj.deleted = 'N'";

$result_inj = mysql_query($sql, $link) or die("unable to get kase<br>" . $sql);
$numbs_inj = mysql_numrows($result_inj);

$arrInjuries = array();
for ($int_inj=0;$int_inj<$numbs_inj;$int_inj++) {
	$injury_id = mysql_result($result_inj, $int_inj, "injury_id");
	$adj_number = mysql_result($result_inj, $int_inj, "adj_number");
	$start_date = mysql_result($result_inj, $int_inj, "start_date");
	$end_date = mysql_result($result_inj, $int_inj, "end_date");
	$injury_location = mysql_result($result_inj, $int_inj, "full_address");
	$explanation = mysql_result($result_inj, $int_inj, "explanation");
	$end_date = mysql_result($result_inj, $int_inj, "end_date");
	$ct = date("m/d/Y", strtotime($start_date));
	if ($end_date!="0000-00-00") {
		$ct .= " - " . date("m/d/Y", strtotime($end_date)) . " CT";
	}
	$arrInjuries[$adj_number] = array("ct"=>$ct, "explanation"=>$explanation, "location"=>$injury_location);
}
?>
<!DOCTYPE html>
<html>
<head>
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
	max-width:128px;
	max-height:128px;
}
</style>
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
<table width="900" border="0" align="center" cellpadding="3" cellspacing="0" style="margin-top:0px">
  <tr>
  	<td width="20%" valign="top"><img src="https://www.ikase.website/img/ikase_logo_login.png" height="32" width="77"></td>
    <td colspan="3" align="left" style="font-family:Arial, Helvetica, sans-serif; font-weight:bold; font-size:1.5em">
    	DEMOGRAPHICS COVER PAGE
    </td>
  </tr>
  <tr>
    <td width="20%">&nbsp;</td>
    <td width="35%"><em>as of <?php echo date("m/d/y g:iA"); ?></em></td>
    <td colspan="2" align="right">&nbsp;
    	
    </td>
  </tr>
  <tr>
    <td colspan="4">
    	<?php if ($document_filename!="") { ?>
        <div id="picture_holder" style="width:100%; z-index:2345; position:absolute; left:0px; top:100px">
        <div style="float:right; z-index:2346" id="applicant_picture">
            <img src='https://www.ikase.website/uploads/<?php echo $_SESSION['user_customer_id'] ?>/<?php echo $case_id; ?>/<?php echo $document_filename ?>' class='applicant_img' style="border:1px solid white">
        </div>
        </div>
    <?php } ?>
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
  <tr>
    <td valign="top" nowrap><strong>Case Number</strong></td>
    <td colspan="3"><?php echo $case_number; ?></td>
  </tr>
  <tr>
    <td valign="top" nowrap><strong>Case Name</strong></td>
    <td colspan="3"><?php echo $first_name . " " . $last_name . " vs " . $employer; ?></td>
  </tr>
   <tr>
    <td valign="top" nowrap><strong>ADJ Number</strong></td>
    <td><?php echo $adj_number; ?></td>
    <?php foreach($arrPartieInfo as $party_info) { 
		if ($party_info["partie_type"]=="Venue") {
			$venue = $party_info["partie_preferred_name"];
			$judge = $party_info["partie_full_name"];
		}
	}
	?>
    <td width="40%"><strong>Venue</strong>&nbsp;<?php echo $venue; ?><?php if ($judge!="") { ?>&nbsp;&nbsp;&nbsp;&nbsp;<strong>Judge</strong>&nbsp;<?php echo $judge; ?><?php } ?></td>
    <td width="30%">&nbsp;</td>
  </tr>
  <tr>
    <td valign="top" nowrap>&nbsp;</td>
    <td>&nbsp;</td>
    <td nowrap>&nbsp;</td>
    <td nowrap>&nbsp;</td>
  </tr>
    <td valign="top" nowrap class="highlight"><strong>Applicant</strong></td>
    <td colspan="3" class="highlight"><?php echo $first_name . " " . $last_name; ?>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo "Age: " . $age; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>SSN:</strong><?php if (strlen($ssn)==9) {
		echo substr($ssn, 0, 3) . "-" . substr($ssn, 3, 2) . "-" . substr($ssn, 5, 4); 
	}
	?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>Date of Birth:</strong><?php echo date("m/d/Y", strtotime($dob));// . " (" . $age . " years old)"; ?>
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
    <td colspan="3"><?php echo implode(" | ", $arrEmployerComm); ?></td>
  </tr>
  <tr valign="top">
    <td colspan="4"><hr /></td>
  </tr>
  <?php foreach($arrPartieInfo as $party_info) { 
  	if ($party_info["partie_type"]=="Insurance Carrier" || $party_info["partie_type"]=="Defense Attorney") {
		$claim_number = "";
		if ($party_info["partie_type"]=="Insurance Carrier") {
			//need to see if there is a specialty
			$queryspec = "SELECT adhoc_value  FROM `cse_corporation_adhoc` 
			WHERE `adhoc` = 'claim_number'
			AND corporation_uuid = '" . $party_info["partie_uuid"] . "'";
			
			$resultspec = mysql_query($queryspec, $r_link) or die("unable to get specialty");
			$numberspec = mysql_numrows($resultspec);
			if ($numberspec > 0) {
				$claim_number = mysql_result($resultspec, 0, "adhoc_value");
			}
		}?>
  <tr>
    <td valign="top" nowrap><strong><?php echo $party_info["partie_type"]; ?></strong></td>
    <td colspan="3"><strong><?php echo $party_info["partie_name"]; ?></strong></td>
  </tr>
   <tr>
    <td valign="top" nowrap class="<?php if ($claim_number!="") { ?>highlight<?php } ?>"><?php if ($claim_number!="") { ?><strong>Claim Number</strong><?php } ?></td>
    <td colspan="3" class="<?php if ($claim_number!="") { ?>highlight<?php } ?>"><?php if ($claim_number!="") { echo $claim_number . "&nbsp;&nbsp;&nbsp;&nbsp;"; } ?><strong><?php echo $party_info["partie_employee_title"]; ?></strong>: <?php echo $party_info["partie_full_name"]; ?></td>
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
    <td nowrap><strong>SOL Date</strong>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $sol_date->format("m/d/Y") . "\n"; ?></td>
    <td nowrap>&nbsp;</td>
  </tr>
  <tr>
    <td valign="top" nowrap><strong>Attorney</strong></td>
    <td><?php echo $attorney_name; ?>
		</td>
    <td nowrap><strong>Worker</strong>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $worker_name; ?></td>
    <td nowrap>&nbsp;</td>
  </tr>
  <tr valign="top">
    <td colspan="4"><hr /></td>
  </tr>
  <?php foreach($arrInjuries  as $injury) { ?>
  <tr>
    <td valign="top" nowrap><strong>Date of Injury</strong></td>
    <td colspan="3"><?php echo $injury["ct"]; ?></td>
  </tr>
  <tr>
    <td valign="top" nowrap><strong>Place of Injury</strong></td>
    <td colspan="3"><?php echo $injury["location"]; ?></td>
  </tr>
  <tr>
    <td valign="top" nowrap><strong>Explanation</strong></td>
    <td colspan="3"><?php echo $injury["explanation"]; ?></td>
  </tr>
  <tr valign="top">
    <td colspan="4"><hr /></td>
  </tr>
  <?php } ?>
  <?php for($bodyindex=0; $bodyindex < 5; $bodyindex++) { ?>
  <tr>
    <td valign="top" nowrap ><?php if ($bodyindex==0) { ?><strong>Body Parts</strong><?php } ?></td>
    <td colspan="2">
		<?php if (isset($arrBodInfo[$bodyindex])) { ?>
		<?php echo $arrBodInfo[$bodyindex]["code"]; ?>&nbsp; - &nbsp;<?php echo $arrBodInfo[$bodyindex]["description"]; ?>
        <?php } ?>    </td>
    <td nowrap>
		<?php if (isset($arrBodInfo[$bodyindex + 5])) { ?>
        <?php echo $arrBodInfo[$bodyindex + 5]["code"]; ?>&nbsp; - &nbsp;<?php echo $arrBodInfo[$bodyindex + 5]["description"]; ?>
        <?php } ?>    </td>
  </tr>
  <?php } ?>
  <tr valign="top">
    <td colspan="4"><hr/></td>
  </tr>
</table>
</body>
</html>
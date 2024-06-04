<?php 
error_reporting(E_ALL);
error_reporting(error_reporting(E_ALL) & (-1 ^ E_DEPRECATED));

ini_set('SMTP','localhost'); 
ini_set('sendmail_from', 'admin@ikase.website'); 

include("../shared/legacy_session.php");
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
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage(), "sql"=>$sql));
		echo json_encode($error);
}

$query = "SELECT ccase.*, cpinj.*, cven.venue_abbr case_venue, 
defendant.company_name defendant, defendant.full_address defendant_address, ccase.adj_number, defendant.phone defendant_phone, defendant.fax defendant_fax, defendant.email defendant_email, defendant.company_site defendant_site, defendant.kai_info
FROM cse_case ccase
LEFT OUTER JOIN `cse_case_corporation` ccorp
ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'defendant' AND ccorp.deleted = 'N')
LEFT OUTER JOIN `cse_corporation` defendant
ON ccorp.corporation_uuid = defendant.corporation_uuid
LEFT OUTER JOIN cse_case_venue ccven 
ON ccase.case_uuid = ccven.case_uuid
LEFT OUTER JOIN `ikase`.cse_venue cven
ON ccven.venue_uuid = cven.venue_uuid
LEFT OUTER JOIN cse_personal_injury cpinj 
ON ccase.case_id = cpinj.case_id
WHERE ccase.case_id = '" . $case_id . "'
AND ccase.customer_id = " . $_SESSION['user_customer_id'] . "
ORDER BY cpinj.personal_injury_id DESC LIMIT 1";

//die($query);

$result = DB::runOrDie($query);

$attorney_name = "";
$worker_name = "";
$arrdefendantComm = array();
while ($row = $result->fetch()) {
	$arrEmployerComm = array();
	$arrComm = array();
	
	//die("did:" . $person_id);
	$case_id = $row->case_id;
	$case_uuid = $row->case_uuid;
	$kai_info = $row->kai_info;
	$sub_in = $row->sub_in;
	$arrKAIInfo = json_decode($kai_info, true);
	//die(print_r($arrKAIInfo));
	$arrOutputKai = array();
	$arrOutputValKai = array();
	$arrKaiComm = array();
	if (is_array($arrKAIInfo)) {
		foreach ($arrKAIInfo as $the_kai_info) {
			//echo $the_kai_info["name"] . " = " . $the_kai_info["value"] . "\r\n";
			//die();
			$the_kai_info_name = $the_kai_info["name"];
			$the_kai_info_value = $the_kai_info["value"];
			array_push($arrOutputKai, $the_kai_info_name);
			array_push($arrOutputValKai, $the_kai_info_value);
			
		}
	}
	if ($arrOutputValKai[0] != ""){
		$arrKaiComm[] = "<strong>Birthday</strong>: " . $arrOutputValKai[0];
	}
	if ($arrOutputValKai[1] != ""){
		$arrKaiComm[] = "<strong>Gender</strong>: " . $arrOutputValKai[1];
	}
	if ($arrOutputValKai[2] != ""){
		$arrKaiComm[] = "<strong>Marital</strong>: " . $arrOutputValKai[2];
	}
	if ($arrOutputValKai[3] != ""){
		$arrKaiComm[] = "<strong>License #</strong>: " . $arrOutputValKai[3];
	}
	if ($arrOutputValKai[4] != ""){
		$arrKaiComm[] = "<strong>Language</strong>: " . $arrOutputValKai[4];
	}
	if ($arrOutputValKai[5] != ""){
		$arrKaiComm[] = "<strong>Birth City</strong>: " . $arrOutputValKai[5];
	}
	if ($arrOutputValKai[6] != ""){
		$arrKaiComm[] = "<strong>Birth State</strong>: " . $arrOutputValKai[6];
	}
	if ($arrOutputValKai[7] != ""){
		$arrKaiComm[] = "<strong>Legal</strong>: " . $arrOutputValKai[7];
	}
	if ($arrOutputValKai[8] != ""){
		$arrKaiComm[] = "<strong>Status</strong>: " . $arrOutputValKai[8];
	}
	if ($arrOutputValKai[9] != ""){
		$arrKaiComm[] = "<strong>Spouse</strong>: " . $arrOutputValKai[9];
	}
	if ($arrOutputValKai[10] != ""){
		$arrKaiComm[] = "<strong>Contact</strong>: " . $arrOutputValKai[10];
	}
	if ($arrOutputValKai[11] != ""){
		$arrKaiComm[] = "<strong>Emergency</strong>: " . $arrOutputValKai[11];
	}
	if ($arrOutputValKai[12] != ""){
		$arrKaiComm[] = "<strong>Emergency CTC</strong>: " . $arrOutputValKai[12];
	}
	//die(print_r($arrKaiComm));
	//die(print_r($arrOutputKai));
	//die(print_r($arrOutputValKai));
	$defendant = $row->defendant;
	$defendant_address = $row->defendant_address;
	$defendant_phone = $row->defendant_phone;
	$defendant_fax = $row->defendant_fax;
	$defendant_site = $row->defendant_site;
	$defendant_email = $row->defendant_email;
	if ($defendant_phone!=""){
		$arrdefendantComm[] = "<strong>Phone</strong>: " . $defendant_phone;
	}
	if ($defendant_fax!=""){
		$arrdefendantComm[] = "<strong>Fax:</strong> " . $defendant_fax;
	}
	if ($defendant_site!=""){
		$arrdefendantComm[] = '<strong>Website:</strong> <a href="http://' . str_replace("http://", "", $defendant_site) . '" target="_blank">' . $defendant_site . '</a>';
	}
	if ($defendant_email!=""){
		$arrdefendantComm[] = "<strong>Email:</strong> <a href='mailto:" . $defendant_email . "'>" . $defendant_email . "</a>";
	}
	$intake_date = $row->submittedOn;
	$special_instructions = $row->special_instructions;
	$special_instructions = str_replace("\r\n", "<br>", $special_instructions);
	$special_instructions = str_replace("\n", "<br>", $special_instructions);
	$case_venue = $row->case_venue;
	
	$case_number = $row->case_number;
	$file_number = $row->file_number;
	
	//for now
	if ($case_number=="" && $file_number!="") {
		$case_number=$file_number;
	}
	$case_name = $row->case_name;
	if ($case_name=="") {
		$case_name = $first_name . " " . $last_name . " vs " . $employer;
	}
	$attorney = $row->attorney;
	$worker = $row->worker;
		
	//die($attorney . " - attorney_id");
	if ($attorney != "") {
		if (is_numeric($attorney)) {
			$query_att = "SELECT user_id, user_first_name, user_last_name 
						  FROM `ikase`.`cse_user`
						  WHERE user_id = " . $attorney;
			$result_att = DB::runOrDie($query_att);

			while ($row = $result_att->fetch()) { //FIXME: this doesn't seem to make sense...
				$att_first_name = $row->user_first_name;
				$att_last_name = $row->user_last_name;
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
		$result_work = DB::runOrDie($query_work);

		while ($row = $result_work->fetch()) {
			$work_first_name = $row->user_first_name;
			$work_last_name = $row->user_last_name;
		}
		$worker_name = $work_first_name . " " . $work_last_name;
	}

	
	//$age = $row->age;
	$age = "";
//	if ($age==0) {
		if (validateDate($dob)) {
			$age = age(date("m/d/Y", strtotime($dob)));
		}
//	}
	$adj_number = $row->adj_number;
	
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

$result_applicant_picture = DB::runOrDie($query_applicant_picture);
$numbs_applicant_picture = $result_applicant_picture->rowCount();

$document_filename = $numbs_applicant_picture > 0? $result_applicant_picture->fetch()["document_filename"] : "";

$query_noemp = "SELECT DISTINCT partie.corporation_id, partie.type corporation_type, partie.corporation_uuid, partie.company_name, partie.party_type_option, partie.kai_info, partie.preferred_name, partie.full_address, cpt.partie_type, partie.phone partie_phone, partie.fax partie_fax, partie.full_name partie_full_name, partie.company_site partie_company_site, partie.email partie_email, cpt.employee_title partie_employee_title, partie.employee_phone partie_employee_phone,
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
$result_noemp = DB::runOrDie($query_noemp);

while ($row = $result_noemp->fetch()) {
	$arrPartieComm = array();
	$arrPartieEmployeeComm = array();
	$partie_id = $row->corporation_id;
	$partie_uuid = $row->corporation_uuid;
	$partie_type = $row->partie_type;
	$doctor_type = $row->doctor_type;
	$partie_name = $row->company_name;
	$partie_preferred_name = $row->preferred_name;
	$partie_address = $row->full_address;
	$partie_phone = $row->partie_phone;
	$partie_fax = $row->partie_fax;
	$partie_type_option = $row->party_type_option;
	$partie_full_name = $row->partie_full_name;
	$partie_company_site = $row->partie_company_site;
	$partie_email = $row->partie_email;
	$partie_employee_title = $row->partie_employee_title;
	$partie_employee_email = $row->partie_employee_email;
	$partie_employee_phone = $row->partie_employee_phone;
	$partie_employee_fax = $row->partie_employee_fax;
	
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
	
	if ($partie_type == "Plaintiff") { 
		//die("here");
		$plaintiff_kai_info = $row->kai_info;
		$arrPlaintiffKAIInfo = json_decode($plaintiff_kai_info, true);
		//die(print_r($arrKAIInfo));
		$arrOutputPlaintiffKai = array();
		$arrOutputPlaintiffValKai = array();
		$arrPlaintiffKaiComm = array();
		if (is_array($arrPlaintiffKAIInfo)) {
			foreach ($arrPlaintiffKAIInfo as $the_plainitff_kai_info) {
				//echo $the_kai_info["name"] . " = " . $the_kai_info["value"] . "\r\n";
				//die();
				$the_plainitff_kai_info_name = $the_plainitff_kai_info["name"];
				$the_plainitff_kai_info_value = $the_plainitff_kai_info["value"];
				array_push($arrOutputPlaintiffKai, $the_plainitff_kai_info_name);
				array_push($arrOutputPlaintiffValKai, $the_plainitff_kai_info_value);
				
			}
		}
		if ($arrOutputPlaintiffValKai[0] != ""){
			$arrPlaintiffKaiComm[] = "<strong>Birthday</strong>: " . $arrOutputPlaintiffValKai[0];
		}
		if ($arrOutputPlaintiffValKai[1] != ""){
			$arrPlaintiffKaiComm[] = "<strong>Gender</strong>: " . $arrOutputPlaintiffValKai[1];
		}
		if ($arrOutputPlaintiffValKai[2] != ""){
			$arrPlaintiffKaiComm[] = "<strong>Marital</strong>: " . $arrOutputPlaintiffValKai[2];
		}
		if ($arrOutputPlaintiffValKai[3] != ""){
			$arrPlaintiffKaiComm[] = "<strong>License #</strong>: " . $arrOutputPlaintiffValKai[3];
		}
		if ($arrOutputPlaintiffValKai[4] != ""){
			$arrPlaintiffKaiComm[] = "<br/><strong>Language</strong>: " . $arrOutputPlaintiffValKai[4];
		}
		if ($arrOutputPlaintiffValKai[5] != ""){
			$arrPlaintiffKaiComm[] = "<strong>Birth City</strong>: " . $arrOutputPlaintiffValKai[5];
		}
		if ($arrOutputPlaintiffValKai[6] != ""){
			$arrPlaintiffKaiComm[] = "<strong>Birth State</strong>: " . $arrOutputPlaintiffValKai[6];
		}
		if ($arrOutputPlaintiffValKai[7] != ""){
			$arrPlaintiffKaiComm[] = "<strong>Legal</strong>: " . $arrOutputPlaintiffValKai[7];
		}
		if ($arrOutputPlaintiffValKai[8] != ""){
			$arrPlaintiffKaiComm[] = "<br/><strong>Status</strong>: " . $arrOutputPlaintiffValKai[8];
		}
		if ($arrOutputPlaintiffValKai[9] != ""){
			$arrPlaintiffKaiComm[] = "<strong>Spouse</strong>: " . $arrOutputPlaintiffValKai[9];
		}
		if ($arrOutputPlaintiffValKai[10] != ""){
			$arrPlaintiffKaiComm[] = "<strong>Contact</strong>: " . $arrOutputPlaintiffValKai[10];
		}
		if ($arrOutputPlaintiffValKai[11] != ""){
			$arrPlaintiffKaiComm[] = "<br/><strong>Emergency</strong>: " . $arrOutputPlaintiffValKai[11];
		}
		if ($arrOutputPlaintiffValKai[12] != ""){
			$arrPlaintiffKaiComm[] = "<strong>Emergency CTC</strong>: " . $arrOutputPlaintiffValKai[12];
		}
		//die(print_r($arrPlaintiffKaiComm));
		//die(print_r($arrOutputKai));
		//die(print_r($arrOutputValKai));
	}
	
	$partie_injury_uuid = "";
	if ($partie_type=="Insurance Carrier") {
		//check for injury
		$query_carrier = "SELECT injury_uuid
		FROM cse_case_corporation 
		WHERE corporation_uuid = '" . $partie_uuid . "'
		AND customer_id = " . $_SESSION["user_customer_id"];
		
		$result_carrier = DB::runOrDie($query_carrier);
        if ($result_carrier->rowCount() > 0) {
			$partie_injury_uuid = $result_carrier->fetchColumn();
		}
	}
	$arrPartieInfo[] = array("partie_id"=>$partie_id, "partie_uuid"=>$partie_uuid, "partie_type"=>$partie_type, "doctor_type"=>$doctor_type, "partie_name"=>$partie_name, "partie_address"=>$partie_address, "partie_phone"=>$partie_phone, "partie_fax"=>$partie_fax, "partie_full_name"=>$partie_full_name, "partie_preferred_name"=>$partie_preferred_name, "partie_company_site"=>$partie_company_site, "partie_email"=>$partie_email, "partie_employee_title"=>$partie_employee_title, "partie_type_option"=>$partie_type_option, "partie_comm"=>implode(" | ", $arrPartieComm), "partie_employee_comm"=>implode(" | ", $arrPartieEmployeeComm), "partie_injury_uuid"=>$partie_injury_uuid);	
}
//die(print_r($arrPartieInfo));


//$sol_date = new DateTime("+12 months $intake_date");
//die($sol_date->format('Y-m-d') . "\n");

//injuries
$sql = "SELECT `pinj`.*
FROM `cse_personal_injury` pinj
INNER JOIN cse_case ccase
ON (pinj.case_id = ccase.case_id";
$sql .= " AND `ccase`.`case_id` = '" . $case_id . "')";
$sql .= " WHERE 1
AND pinj.customer_id = " . $_SESSION['user_customer_id'] . "
AND ccase.deleted = 'N'
AND pinj.deleted = 'N'";

//die($sql);

$result_pinj = DB::runOrDie($sql);

while ($row = $result_pinj->fetch()) {
	$injury_id = $row->personal_injury_id;
	$injury_uuid = $row->personal_injury_uuid;
	$personal_injury_date = $row->personal_injury_date;
	$personal_injury_info = $row->personal_injury_info;
	$sol_date = $row->statute_limitation;
	
	if ($sol_date=="0000-00-00") {
		$sol_date = "";
	} else {
		$sol_date = date("m/d/Y", strtotime($sol_date));
	}
	if ($personal_injury_date=="0000-00-00") {
		$personal_injury_date = "";
	} else {
		$personal_injury_date = date("m/d/Y", strtotime($personal_injury_date));
	}
	//die($personal_injury_info);
	//echo "demographics_pi_sheet.php <br/> line 327 <br/><br/>";
	$arrPersonalInjuryInfo = json_decode($personal_injury_info, true);
	//die(print_r($arrPersonalInjuryInfo));
	$arrOutput = array();
	$arrOutputVal = array();
	foreach ($arrPersonalInjuryInfo as $personal_injury_info) {
		//echo $personal_injury_info["name"] . " = " . $personal_injury_info["value"] . "\r\n";
		$personal_injury_info_name = $personal_injury_info["name"];
		$personal_injury_info_value = $personal_injury_info["value"];
		array_push($arrOutput, $personal_injury_info_name);
		array_push($arrOutputVal, $personal_injury_info_value);
	}
}
//die(print_r($arrOutput) . "\r\n" . print_r($arrOutputVal));
//die(print_r($arrOutputVal));
?>
<!DOCTYPE html>
<html>
<head>
<title>Demographics Report</title>
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
<script type="text/javascript" src="../lib/jquery.1.10.2.js"></script>
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
<div style="background:black; text-align:center;display:none" id="matrix_holder"><img src="https://www.ikase.website/img/matrix_blue_logo.jpg" width="267" height="200" alt="Matrix"></div>
<div style="border:0px solid red">
<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
    <tr>
    <td colspan="3" align="center" valign="top" style="font-family:Arial, Helvetica, sans-serif; font-size:1.5em"><img src="https://www.ikase.website/img/ikase_logo_login.png" height="32" width="77">&nbsp;&nbsp;<strong>DEMOGRAPHICS PI COVER PAGE</strong><br/><br/><div style="text-align:center; margin-left:0px; font-size:.7em"><strong><?php echo $_SESSION['user_customer_name']; ?></strong><br/><div style="margin-left:0px"><?php echo str_replace("<br>", ", ", $_SESSION['user_customer_address']); ?></div></div><br/></td>
    </tr>
    <tr>
    <td colspan="3" align="center" valign="top" style="font-family:Arial, Helvetica, sans-serif; font-weight:bold; font-size:1.5em"><hr/></td>
    </tr>
    <tr>
    <td colspan="3" align="left" valign="top" style="font-family:Arial, Helvetica, sans-serif;">
            <table align="right" border="0" width="100%">
                <tr>
                    <td valign="top" width="33%" align="center"><div style="text-align:left; margin-left:75px;"><div style="width:150px; display:inline-block"><strong>Date of Accident</strong></div>&nbsp;&nbsp;<?php echo $arrOutputVal[0]; ?></div></td>
                    <td width="33%" align="center" style="text-align:left;">
                    	<?php if ($sub_in=="Y") { ?>
                    	<div style="float:right">
                        	(Sub-In)
                        </div>
                        <?php } ?>
                        <div style="text-align:left; margin-left:75px;"><div style="width:123px; display:inline-block"><strong>Case Number</strong></div>&nbsp;&nbsp;<?php echo $case_number; ?></div>
                    </td>
                    <td width="33%" align="center" style="text-align:left;">
                      <div style="text-align:left; margin-left:75px;"><div style="width:150px; display:inline-block"><strong>Intake Date</strong></div>&nbsp;&nbsp;<?php echo date("m/d/Y", strtotime($intake_date)); ?></div>
                    </td>
                  </tr>
                  <tr>
                  <td valign="top" width="33%" align="center"><div style="text-align:left; margin-left:75px;"><div style="width:150px; display:inline-block"><strong>Place of Accident</strong></div>&nbsp;&nbsp;<?php echo $arrOutputVal[3]; ?></div></td>
                    <td width="33%" align="center" style="text-align:left;">
                        <div style="text-align:left; margin-left:75px;"><div style="width:123px; display:inline-block"><strong>Case Name</strong></div>&nbsp;&nbsp;<?php echo $case_name; ?></div>
                    </td>
                    <td width="33%" align="center" style="text-align:left;">
                      <div style="text-align:left; margin-left:75px;"><div style="width:150px; display:inline-block"><strong>SOL Date</strong></div>&nbsp;&nbsp;<?php echo $sol_date . "\n"; ?></div>
                      <div style="text-align:left; margin-left:75px;"><div style="width:150px; display:inline-block"><strong>Injury Date</strong></div>&nbsp;&nbsp;<?php echo $personal_injury_date . "\n"; ?></div>
                    </td>
                  </tr>
                  <tr>
                  	<td valign="top" width="33%" align="center"><div style="text-align:left; margin-left:75px;"><div style="width:150px; display:inline-block"><strong>Description</strong></div>&nbsp;&nbsp;&nbsp;<?php echo $arrOutputVal[5]; ?></div></td>
                    <td width="33%" align="center" style="text-align:left;">
                        <div style="text-align:left; margin-left:75px;"><div style="width:123px; display:inline-block"><strong>Venue</strong></div>&nbsp;&nbsp;<?php echo $case_venue; ?></div>
                    </td>
                    <td width="33%" align="center" style="text-align:left;">
                      <div style="text-align:left; margin-left:75px;"><div style="width:150px; display:inline-block"><strong>Attorney</strong></div>&nbsp;&nbsp;<?php echo $attorney_name; ?></div>
                    </td>
                  </tr>
                  <tr>
                  	<td valign="top" width="33%" align="center">&nbsp;</td>
                    <td width="33%" align="center" style="text-align:left;">&nbsp;
                        
                    </td>
                    <td width="33%" align="center" style="text-align:left;" valign="top">
                        <div style="text-align:left; margin-left:75px;"><div style="width:150px; display:inline-block"><strong>Coordinator</strong></div>&nbsp;&nbsp;<?php echo $worker_name; ?></div>
                    </td>
              </tr>
           </table>
          </td>
    </tr>
    <tr>
    <td colspan="3" align="center" valign="top" style="font-family:Arial, Helvetica, sans-serif; font-weight:bold; font-size:1.5em"><hr/></td>
    </tr>
    <tr>
    <td width="49%" align="center" valign="top" style="font-family:Arial, Helvetica, sans-serif; font-weight:bold; font-size:1.5em">Plaintiff</td>
    <td width="1%" align="center" style="font-family:Arial, Helvetica, sans-serif; font-weight:bold; font-size:1.5em">&nbsp;</td>
    <td width="50%" align="center" style="font-family:Arial, Helvetica, sans-serif; font-weight:bold; font-size:1.5em">Defendant</td>
  </tr>
  <tr>
    <td valign="top" align="center" style="font-family:Arial, Helvetica, sans-serif; font-weight:bold; font-size:1.5em">&nbsp;</td>
    <td align="center" style="font-family:Arial, Helvetica, sans-serif; font-weight:bold; font-size:1.5em">&nbsp;</td>
    <td align="center" style="font-family:Arial, Helvetica, sans-serif; font-weight:bold; font-size:1.5em">&nbsp;</td>
  </tr>
  <tr>
    <td colspan="3" align="center" valign="top" style="font-family:Arial, Helvetica, sans-serif; font-weight:bold; font-size:1.5em"><hr/></td>
    </tr>
  <tr>
    <td align="center" valign="top">
    	<table width="95%" border="0" cellpadding="3" cellspacing="0" align="center" style="margin-top:0px">
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
  
    <?php foreach($arrPartieInfo as $party_info) { 
  	
  	if ($party_info["partie_type"]!="Insurance Carrier" && $party_info["partie_type"]!="Defense Attorney" && $party_info["partie_type"]!="Venue") {
	if ($party_info["partie_type"]=="Defendant") {
		continue;
	}
	$specialty = "";
	if ($party_info["partie_type"]=="Medical Provider") {
		//need to see if there is a specialty
        $resultspec = DB::runOrDie("SELECT adhoc_value FROM `cse_corporation_adhoc` 
        WHERE `adhoc` = 'specialty'
        AND corporation_uuid = ?", $party_info["partie_uuid"]);
        if ($resultspec->rowCount() > 0) {
            $specialty = ucwords($resultspec->fetchColumn());
		}
	}
	?>
  <tr>
    <td valign="top" nowrap><strong><?php echo $party_info["partie_type"]; ?></strong></td>
    <td nowrap>
    	<?php if ($party_info["partie_type"]=="Medical Provider") { ?>
        <div style="float:right">
        	<?php echo $party_info["doctor_type"]; ?>
        </div>
        <?php } ?>
        <strong><?php echo $party_info["partie_name"]; ?></strong>
    </td>
    <td nowrap>&nbsp;</td>
  </tr>
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
  <tr>
    <td valign="top" nowrap>&nbsp;</td>
    <td colspan="3" height="50" valign="top">
        <?php 
			$seperator = " | ";
			if (count($arrPlaintiffKaiComm) > 4) {
				$seperator = " | ";
			}
			echo implode(" | ", $arrPlaintiffKaiComm); 
		?>
    </td>
  </tr>
    <td colspan="4"><hr /></td>
  </tr>
  <?php 
  	}
  } ?>
  <?php 
  $arrInjuryClaim = array();
 //die(print_r($arrPartieInfo));
  foreach($arrPartieInfo as $party_info) { 
  	if ($party_info["partie_type_option"]!="plaintiff") {
		continue;
	}
  	if ($party_info["partie_type"]=="Insurance Carrier" || $party_info["partie_type"]=="Defense Attorney") {
			$claim_number = "";
			if ($party_info["partie_type"]=="Insurance Carrier") {
				//if ($party_info["partie_type_option"]=="Plaintiff") {
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
			}
		?>
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

</table>
    </td>
    <td align="center" valign="middle">
    	<div style="border-left:1px solid #000; height:400px; margin-top:10%"></div>
    </td>
    <td align="center" valign="top">
    	<table width="95%" border="0" cellpadding="3" cellspacing="0" style="margin-top:0px">
          <?php if ($specific_instructions!="") { ?>
          <tr>
            <td width="16%" valign="top" nowrap><strong>Specific Instructions</strong></td>
            <td><?php echo $specific_instructions; ?></td>
          </tr>
          <tr valign="top">
            <td colspan="2"><hr /></td>
          </tr>
          <?php } ?>
          <?php if ($special_instructions!="") { ?>
          <tr>
            <td valign="top" nowrap><span style="font-weight:bold; background:orange">Special Instructions</span></td>
            <td><?php echo $special_instructions; ?></td>
          </tr>
          <tr valign="top">
            <td colspan="2"><hr /></td>
          </tr>
          <?php } ?>
          <tr>
            <td valign="top" nowrap><strong>Defendant</strong></td>
            <td><strong><?php echo $defendant; ?></strong></td>
          </tr>
          <tr>
            <td valign="top" nowrap>&nbsp;</td>
            <td><?php echo $defendant_address; ?></td>
          </tr>
          <tr>
            <td valign="top" nowrap>&nbsp;</td>
            <td>
				<?php echo implode(" | ", $arrdefendantComm); ?>
            </td>
          </tr>
          <tr>
            <td valign="top" nowrap>&nbsp;</td>
            <td height="50" valign="top">
				<?php echo implode(" | ", $arrKaiComm); ?>
            </td>
          </tr>
          <tr valign="top">
            <td colspan="2"><hr /></td>
          </tr>
          <?php 
          $arrInjuryClaim = array();
         //die(print_r($arrPartieInfo));
          foreach($arrPartieInfo as $party_info) { 
		  	if ($party_info["partie_type_option"]!="defendant") {
				continue;
			}
            if ($party_info["partie_type"]=="Insurance Carrier" || $party_info["partie_type"]=="Defense Attorney") {
                $claim_number = "";
                if ($party_info["partie_type"]=="Insurance Carrier") {
					//need to see if there is a claim number
					$resultspec = DB::runOrDie("SELECT adhoc_value  FROM `cse_corporation_adhoc` 
					WHERE `adhoc` = 'claim_number'
					AND deleted = 'N'
					AND corporation_uuid = '" . $party_info["partie_uuid"] . "'");
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
            <td><strong><?php echo $party_info["partie_name"]; ?></strong></td>
          </tr>
           <tr>
            <td valign="top" nowrap class="<?php if ($claim_number!="") { ?>highlight<?php } ?>"><?php if ($claim_number!="") { ?><strong>Claim Number</strong><?php } ?></td>
            <td class="<?php if ($claim_number!="") { ?>highlight<?php } ?>">
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
            <td><?php echo $party_info["partie_address"]; ?></td>
          </tr>
          <?php if ($party_info["partie_comm"]!="") { ?>
          <tr>
            <td valign="top" nowrap>&nbsp;</td>
            <td><?php echo $party_info["partie_comm"]; ?></td>  </tr>
          <?php } ?>
          <tr valign="top">
            <td colspan="2"><hr /></td>
          </tr>
          <?php
            }
          } ?>
        </table>
    </td>
  </tr>
</table>

</div>
</body>
</html>

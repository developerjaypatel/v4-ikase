<?php
//Set no caching
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
header("Cache-Control: no-store, no-cache, must-revalidate"); 
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

header("strict-transport-security: max-age=600");
header('X-Frame-Options: SAMEORIGIN');
header("X-XSS-Protection: 1; mode=block");

error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
ini_set('display_errors', '1');

require_once('../shared/legacy_session.php');
include("../api/connection.php");

if($_SERVER["HTTPS"]=="off") {
	
	header("location:https://v2.ikase.org" . $_SERVER['REQUEST_URI']);
}

if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
	//die(print_r($_SESSION));
	//header("location:../index.php");
	die("<script language='javascript'>parent.location.href='../index.php'</script>");
}

session_write_close();

$cus_id = $_SESSION['user_customer_id'];
$case_id = passed_var("case_id", "GET");
$injury_id = passed_var("injury_id", "GET");
$jetfile_id = passed_var("jetfile_id", "GET");

if ($case_id == "" || $case_id < 0 || !is_numeric($case_id)) {
	die("<script language='javascript'>window.close()</script>");
}
if ($injury_id == "" || $injury_id < 0 || !is_numeric($injury_id)) {
	die("<script language='javascript'>parent.location.href='app_1_2.php?case_id=" . $case_id . "&injury_id=" . $injury_id . "'</script>");
}

$location_required = "y";
$basic  = "n";

$client_id = "";
$first = "";
$last = "";
$middle = "";
$social_sec = "";
$birth_date = "";
$birth_month = "";
$birth_day = "";
$birth_year = "";

$address1 = "";
$address2 = "";
$city = "";
$state = "";
$zip = "";

$rad_venue = "";
$letter_office_code = "";

//defaults
$blnAllowEdits = true;
$blnADJ = false;

$case_attorney_id = -1;
$blnCaseAttorney = false;
include("jetfile_kase.php");
if (is_numeric($kase->attorney_id)) {
	$case_attorney_id = $kase->attorney_id;
	$blnCaseAttorney = true;
}

$person_id = $kase->applicant_id;
$first = $kase->first_name;
$middle = $kase->middle_name;
$last = $kase->last_name;
$social_sec = $kase->ssn;
$birth_date = $kase->dob;
if ($birth_date!="") {
	$birth_date = date("m/d/Y", strtotime($birth_date));
}
$thedob = $birth_date;
$adj_number = $kase->adj_number;

$blnValidADJ = (strpos($adj_number, "ADJ") > -1);

if ($kase->applicant_street=="" && $kase->applicant_full_address!="") {
	$arrApplicantAddress = explode(",", $kase->applicant_full_address);
	$arrStateZip = explode(" ", trim($arrApplicantAddress[count($arrApplicantAddress) - 1]));
	//die(print_r($arrStateZip));
	$kase->applicant_state = $arrStateZip[0];
	if (count($arrStateZip) == 2) {
		$kase->applicant_zip = $arrStateZip[1];
	}
	unset($arrApplicantAddress[count($arrApplicantAddress) - 1]);
	$kase->applicant_city = trim($arrApplicantAddress[count($arrApplicantAddress) - 1]);
	unset($arrApplicantAddress[count($arrApplicantAddress) - 1]);
	$kase->applicant_street = trim(implode(",", $arrApplicantAddress));
}
$address1 = $kase->applicant_street;
$city = $kase->applicant_city;
$state = $kase->applicant_state;
$zip = $kase->applicant_zip;

$applicant_name = "";
$insurance_carrier = "";
$applicant_address1 = "";
$applicant_address2 = "";
$applicant_city = "";
$applicant_state = "";
$applicant_zip = "";

$emp_name = "";
$emp_street = "";
$emp_city = "";
$emp_state = "";
$emp_zip = "";

$case_injury_street = "";
$case_injury_city = "";
$case_injury_state = "";
$case_injury_zip = "";

//die(print_r($kase));
if ($kase->jetfile_info!="") {
	$jetfile_info = json_decode($kase->jetfile_info);
	$jetfile_id = $kase->jetfile_id;
	if (is_object($jetfile_info)) {
		//die(print_r($jetfile_info));
		if (is_object($jetfile_info->page1)) {
			$page1 = $jetfile_info->page1;
			$rad_venue = $page1->county;
			$letter_office_code = $page1->letter_office_code;
			if ($case_attorney_id == "") {
				$case_attorney_id = $page1->attorney_id;
				$blnCaseAttorney = false;
			}
			$insurance_carrier = $page1->insurance_carrier;
			$applicant_name = $page1->applicant_name;
			$applicant_address1 = $page1->applicant_address;
			$applicant_address2 = $page1->applicant_address2;
			$applicant_city = $page1->applicant_city;
			$applicant_state = $page1->applicant_state;
			$applicant_zip = $page1->applicant_zip_code;
			
			//get the injury location as well
			$case_injury_street = $page1->injury_street;
			$case_injury_city = $page1->injury_city;
			$case_injury_state = $page1->injury_state;
			$case_injury_zip = $page1->injury_zip;
			
			$emp_name = $page1->employer_name;
			$emp_street = $page1->employer_street;
			$emp_city = $page1->employer_city;
			$emp_state = $page1->employer_state;
			$emp_zip = $page1->employer_zip;
		}
		if (is_object($jetfile_info->page2)) {
			$page2 = $jetfile_info->page2;
		}
	}
}

if ($letter_office_code=="") {
	$letter_office_code = $kase->venue_abbr;
}

//allow edits if no ADJ
//echo "adj:" . $adj_number;
if ($adj_number=="Pending")	{
	$adj_number="";
}
if ($adj_number!="" && $adj_number!="Pending")	{
	//$blnADJ = true;
	//echo "adj:" . $adj_number;
}

if (strpos($adj_number, "ADJ") === false) {
	$adj_number = "";
}
$number_pages = 4;
$type = "button";
$style = "";
if ((!$blnAllowEdits || $blnADJ) && $location_required!="y") { 
	$type = "hidden";
	$style = "none";
}

$query = "SELECT `user_id` `attorney_id`, user_first_name `first_name`, '' `middle_initial`, user_last_name `last_name`, `activated`, `default_attorney` 
FROM ikase.cse_user 
WHERE (`job` = 'Attorney at Law' OR `job` = 'attorney') 
AND user_first_name != ''
AND customer_id = '" . $_SESSION['user_customer_id'] . "'
ORDER BY `user_first_name`";

try {
	$attorneys = DB::select($query);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
}
//default at all?
$default_attorney_id = -1;
foreach($attorneys as $attorney) {
	$attorney_id = $attorney->attorney_id;
	$default_attorney = $attorney->default_attorney;
	if ($default_attorney=="Y") {
		$default_attorney_id = $attorney_id;
		break;
	}
}
$arrRows = array();
$selected = "";
if ($case_attorney_id==0 && $default_attorney_id < 0) {
	//no default, one has been chosen
	$selected = " selected";
}
$the_row = "<option value='0'" . $selected . ">Select from List</option>";
$arrRows[] = $the_row;
foreach($attorneys as $attorney) {
	$attorney_id = $attorney->attorney_id;
	$first_name = $attorney->first_name;
	$last_name = $attorney->last_name;
	$active = $attorney->actived;
	$default_attorney = $attorney->default_attorney;
	$selected = "";
	if ($case_attorney_id>0) {
		//no default, one has been chosen
		$default_attorney = "N";
	}
	if ($case_attorney_id==$attorney_id) {
		$selected = " selected";
	} else {	
		if ($case_attorney_id==0) {
			if ($default_attorney=="Y") {
				$selected = " selected";
			}
		}
	}
	$the_row = "<option value='" . $attorney_id . "'" . $selected . ">". $first_name . " " . $middle_initial . " " . $last_name . "</option>";
	
	$arrRows[] = $the_row;
}
//die(print_r($arrRows));
//drop down for venue
$query = "SELECT `venue`, `venue_abbr` FROM `ikase`.`cse_venue` where deleted!=1 ORDER BY `venue_abbr`";
$arrVenueRows = array();
try {
	$venues = DB::select($query);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
}
$arrVenueRows = array();
if ($letter_office_code!="") {
	$the_row = "<option value=''>Select from List</option>";
} else {
	$the_row = "<option value='' selected>Select from List</option>";
}
$arrVenueRows[] = $the_row;
foreach($venues as $venue) {
	$venue_abbr = $venue->venue_abbr;
	$venue = $venue->venue;
	$selected = "";
	if ($venue_abbr==$letter_office_code) {
		$selected = " selected";
	}
	$the_row = "<option value='" . trim($venue_abbr) . "'" . $selected . ">". $venue_abbr . " - " . $venue . "</option>";
	$arrVenueRows[] = $the_row;
}

if ($emp_name=="") {
	if (count($arrEmployerOptions) == 0) {
		$emp_name = $kase->employer;
		if ($kase->employer_street=="" && $kase->employer_full_address!="") {
			$arrEmployerAddress = explode(",", $kase->employer_full_address);
			$arrStateZip = explode(" ", trim($arrEmployerAddress[count($arrEmployerAddress) - 1]));
			//die(print_r($arrStateZip));
			$kase->employer_state = $arrStateZip[0];
			if (count($arrStateZip) == 2) {
				$kase->employer_zip = $arrStateZip[1];
			}
			unset($arrEmployerAddress[count($arrEmployerAddress) - 1]);
			$kase->employer_city = trim($arrEmployerAddress[count($arrEmployerAddress) - 1]);
			unset($arrEmployerAddress[count($arrEmployerAddress) - 1]);
			$kase->employer_street = trim(implode(",", $arrEmployerAddress));
		}
		
		$emp_street = $kase->employer_street;
		$emp_city = $kase->employer_city;
		$emp_state = $kase->employer_state;
		$emp_zip = $kase->employer_zip;
	}
}

//injury
$case_job_desc = $kase->occupation;
$case_injury_start = $kase->start_date;
$case_injury_end = $kase->end_date;
if ($kase->injury_street=="" && $kase->full_address!="") {
	$arrInjuryAddress = explode(",", $kase->full_address);
	$arrStateZip = explode(" ", trim($arrInjuryAddress[count($arrInjuryAddress) - 1]));
	//die(print_r($arrStateZip));
	$kase->injury_state = $arrStateZip[0];
	if (count($arrStateZip) == 2) {
		$kase->injury_zip = $arrStateZip[1];
	}
	unset($arrInjuryAddress[count($arrInjuryAddress) - 1]);
	$kase->injury_city = trim($arrInjuryAddress[count($arrInjuryAddress) - 1]);
	unset($arrInjuryAddress[count($arrInjuryAddress) - 1]);
	$kase->injury_street = trim(implode(",", $arrInjuryAddress));
}
//die(print_r($kase));

if ($case_injury_street=="") {
	$case_injury_street = $kase->injury_street;
	$case_injury_city = $kase->injury_city;
	$case_injury_state = $kase->injury_state;
	$case_injury_zip = $kase->injury_zip;
}
//die("case_injury_street:". $case_injury_street);
$rad_injurytype1 = 0;

if ($case_injury_start!="" && $case_injury_start!="0000-00-00" && ($case_injury_end==""|| $case_injury_end=="0000-00-00")) {
	$rad_injurytype1 = 1;
	$client_case_injury_start = date("m/d/Y", strtotime($case_injury_start));
}
$rad_injurytype2 = 0;
if ($case_injury_start!="" && $case_injury_start!="0000-00-00" && $case_injury_end!="" && $case_injury_end!="0000-00-00") {
	$rad_injurytype2 = 1;
	$client_case_injury_start = date("m/d/Y", strtotime($case_injury_start)) . "-" . date("m/d/Y", strtotime($case_injury_end)) . " CT";
}

$car_name = "";
$car_street = "";
$car_city = "";
$car_state = "";
$car_zip = "";
$car_eams_number = "";

$rep_name = "";
$rep_street = "";
$rep_city = "";
$rep_state = "";
$rep_zip = "";
$rep_eams_number = "";

$arrCarriers = array();
//die(print_r($parties));
foreach($parties as $partie_index=>$party) {
	if ($party->type == "carrier") {
		//$party->parent_uuid . "-" .
		$the_row = "<option value='" . $party->corporation_id . "'>" . $party->company_name . "</option>";
		$arrCarriers[] = $the_row;
	}
	if (count($arrCarriers)==1 && $car_name=="") {
		//$party = $parties[$partie_index];
		//carrier
		//die(print_r($party));
		$car_name = $party->company_name;
		$car_eams_number = $party->parent_uuid;
		if ($car_eams_number=="") {
			$car_eams_number = $party->eams_ref_number;
		}
		if ($party->street=="") {
			$full_address = $party->full_address;
			$arrAddress = explode(",", $full_address);
			
			//die(print_r($arrAddress));
			$arrLength = count($arrAddress);
			
			$party->city = trim($arrAddress[$arrLength - 2]);
			$arrStateCity = explode(" ", trim($arrAddress[$arrLength - 1]));
			//die(print_r($arrStateCity));
			$party->state = trim($arrStateCity[0]);
			$party->zip = trim($arrStateCity[count($arrStateCity) - 1]);
			array_pop($arrAddress);
			array_pop($arrAddress);
			
			//die(print_r($arrAddress));
			$party->street = implode(",", $arrAddress);
			
		}
		$car_street = $party->street;
		$car_city = $party->city;
		$car_state = $party->state;
		$car_zip = $party->zip;
		$claim_number = $party->claim_number;
		$adjuster = $party->full_name;
		/*
		//let's look up the eams number
		$sql = "SELECT eams_ref_number FROM ikase.cse_eams_carriers
		WHERE firm_name = '" . $car_name . "'";
		
		try {
			$stmt = DB::run($sql);
			$firm = $stmt->fetchObject();
			
			$car_eams_number = $firm->eams_ref_number;
		} catch(PDOException $e) {
			$error = array("error"=> array("text"=>$e->getMessage(), "sql"=>$sql));
			echo json_encode($error);
		}
		*/
	}
}
if ($kase->jetfile_info!="") {
	$jetfile_info = json_decode($kase->jetfile_info);
	if (is_object($jetfile_info)) {
		//die(print_r($jetfile_info));
		if (is_object($jetfile_info->page1)) {
			$page1 = $jetfile_info->page1;
			$emp_insurance = $page1->employer_insurance;
			
			if ($case_job_desc=="") {
				$case_job_desc = $page1->occupation;
			}
			if ($car_name=="") {
				//carrier
				$car_name = $page1->carrier_name;
				$car_street = $page1->carrier_street;
				$car_city = $page1->carrier_city;
				$car_state = $page1->carrier_state;
				$car_zip = $page1->carrier_zip;
				$car_eams_number = $page1->carrier_eams_number;
				$claim_number = $page1->claim_number;
				$adjuster = $page1->adjuster;
			}
			//admin
			$rep_name = $page1->rep_name;
			$rep_street = $page1->rep_street;
			$rep_city = $page1->rep_city;
			$rep_state = $page1->rep_state;
			$rep_zip = $page1->rep_zip;
			$rep_eams_number = $page1->rep_eams_number;
		} 
	}

	//can we file?
	//get uploads
	/*
	$sql = "SELECT `document_id` id, `description` `name`, `document_filename` `filepath`
	FROM cse_document doc
	INNER JOIN cse_case_document ccd
	ON doc.document_uuid = ccd.document_uuid
	INNER JOIN cse_case ccase
	ON ccd.case_uuid = ccase.case_uuid
	WHERE `type` = 'App_for_ADJ' 
	AND `document_filename` != ''
	AND case_id = :case_id
	AND `doc`.customer_id = :cus_id
	AND `doc`.deleted = 'N'";
	*/
	
	$sql = "SELECT `document_id` id, `document_name` `name`, `document_filename` `filepath`
	FROM cse_document doc
	INNER JOIN cse_injury_document ccd
	ON doc.document_uuid = ccd.document_uuid
	INNER JOIN cse_injury inj
	ON ccd.injury_uuid = inj.injury_uuid
	WHERE `doc`.`type` = 'App_for_ADJ' 
	AND `document_filename` != ''
	AND injury_id = :injury_id
	AND `doc`.customer_id = :cus_id
	AND `doc`.deleted = 'N'";
	
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("injury_id", $injury_id);
		$stmt->bindParam("cus_id", $cus_id);
		$stmt->execute();
		$documents = $stmt->fetchAll(PDO::FETCH_OBJ);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
	}
	$uploads = "";
	$minimum_files = 4;
	$number_files = count($documents);
	if ($number_files >= $minimum_files) {
		$uploads = "1";
	}
}



//body parts
$sql = "SELECT DISTINCT bp.*, 
		cib.injury_bodyparts_id, cib.attribute bodyparts_number, cib.`status` `bodyparts_status`,
		ccase.case_id, ccase.case_uuid, bp.bodyparts_id id 
		FROM `cse_bodyparts` bp
		INNER JOIN cse_injury_bodyparts cib
		ON bp.bodyparts_uuid = cib.bodyparts_uuid
		INNER JOIN cse_injury ci
		ON (cib.injury_uuid = ci.injury_uuid
		AND `ci`.`injury_id` = :injury_id)
		INNER JOIN cse_case_injury cci
		ON ci.injury_uuid = cci.injury_uuid
		INNER JOIN cse_case ccase
		ON (cci.case_uuid = ccase.case_uuid
		AND `ccase`.`case_id` = :case_id)
		WHERE 1
		AND cci.customer_id = " . $_SESSION['user_customer_id'] . "
		AND cci.deleted = 'N'
		AND cib.deleted = 'N'
		ORDER BY `code` ASC";
try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("case_id", $case_id);
	$stmt->bindParam("injury_id", $injury_id);
	$stmt->execute();
	$bodyparts = $stmt->fetchAll(PDO::FETCH_OBJ);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
}

$bodypart_count = "";
if (count($bodyparts) > 0) {
	$bodypart_count = count($bodyparts);
}
$liab = "";
if (isset($page2)) {
	$chkLiab1 = $page2->temporary_disability;
	$chkLiab2 = $page2->reimbursement;
	$chkLiab3 = $page2->medical_treatment_check;
	$chkLiab4 = $page2->compensation_rate;
	$chkLiab5 = $page2->permanent_disability;
	$chkLiab6 = $page2->rehabilitation;
	$chkLiab7 = $page2->back_to_work;
	$liabOther = $page2->other_method;
	
	if ($chkLiab1=="Yes") {
		$liab = "1";
	}
	if ($chkLiab2=="Yes") {
		$liab = "1";
	}
	if ($chkLiab3=="Yes") {
		$liab = "1";
	}
	if ($chkLiab4=="Yes") {
		$liab = "1";
	}
	if ($chkLiab5=="Yes") {
		$liab = "1";
	}
	if ($chkLiab6=="Yes") {
		$liab = "1";
	}
	if ($chkLiab7=="Yes") {
		$liab = "1";
	}
	if ($liabOther!="") {
		$liab = "1";
	}
} 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>EAMS Jet File - Step 1 out of 2</title>
<style type="text/css">
input {
	text-transform: uppercase;
}
.instructions {
	font-size:0.8em;
	font-style:italic;
}
</style>
<script type="text/javascript" src="../lib/jquery.1.10.2.js"></script>
<script type='text/javascript' src='../lib/mask.js'></script>
<script type='text/javascript' src='../lib/mask_date.js'></script>
<script type='text/javascript' src='jetfile.js'></script>
<script type='text/javascript' src='../js/utilities.js'></script>
</head>
<body onload="init()">
<table border="0" cellpadding="2" cellspacing="0" align="center" width="980">
<tr>
    <td width="77"><img src="../img/ikase_logo_login.png" height="32" width="77"></td>
    <td align="center" colspan="6">
        <div style="float:right">
        	<em>As of <?php echo date("m/d/y g:iA"); ?></em>
        </div>
        <span style="font-family:Arial, Helvetica, sans-serif; font-weight:bold; font-size:1.5em">Application for Adjudication</span></td>
  </tr>
</table>
<hr />
<form action="" enctype="multipart/form-data" name="form1" id="form1">
    <input type="hidden" name="case_id" id="case_id" value="<?php echo $case_id; ?>" />
    <input type="hidden" name="injury_id" id="injury_id" value="<?php echo $injury_id; ?>" />
    <input type="hidden" name="person_id" id="person_id" value="<?php echo $person_id; ?>" />
    <input type="hidden" name="jetfile_id" id="jetfile_id" value="<?php echo $jetfile_id; ?>" />
    <input type="hidden" name="page" value="1" />
    <input type="hidden" id="uploads" value="<?php echo $number_files; ?>" />
    <input type="hidden" id="bodypart_count" value="<?php echo $bodypart_count; ?>" />
    <input type="hidden" id="liab" value="<?php echo $liab; ?>" />
<table width="980" border="0" align="center" cellpadding="0" cellspacing="0">
		<tr>
        <td colspan="10" align="center" class="pagetitle">
        	<div style="float:right; text-align:left">
                <span id="proceed_1" style="display:<?php if ($jetfile_id=="") { ?>none<?php } ?>">
                    <em>
                    <a href="app_3_4.php?case_id=<?php echo $case_id; ?>&injury_id=<?php echo $injury_id; ?>&jetfile_id=<?php echo $jetfile_id; ?>">Page 2</a>&nbsp;|&nbsp;<a href="upload_app.php?case_id=<?php echo $case_id; ?>&injury_id=<?php echo $injury_id; ?>&jetfile_id=<?php echo $jetfile_id; ?>">Uploads</a>
                    </em>        
                </span>
            </div>
        	Page 1
        </td>
      </tr>
      <tr>
        <td colspan="6" align="left">
        <?php if ($case_id!="") { ?>
        <div id="jetfile_feedback" style="float:right"></div>
        <strong>Case ID: <span style="padding-left:17px; padding:3px"><?php echo $case_id; ?></span><br />
        Applicant Name:<span style="padding-left:17px; padding:3px"><?php echo $first . "&nbsp;". $last; ?></span><br />
        DOI:<span style="padding-left:17px; padding:3px"><?php echo $client_case_injury_start; ?></span></strong>
        <?php } ?>        
        </td>
      </tr>
      <tr>
        <td colspan="6" align="center"><hr color="#000000" /></td>
      </tr>
      <?php if (count($arrRows)>0) { ?>
      <tr>
        <td>Attorney:</td>
        <td colspan="5">
        <select id="attorney_id" name="attorney_id">
		<?php echo implode("\r\n", $arrRows); ?>
        </select></td>
      </tr>
	  <?php } ?>
	  <?php if ($card_id!="") { ?> 
      <tr>
        <td>Injured Worker:</td>
        <td colspan="5"><?php echo $first . "&nbsp;" . $last; ?></td>
      </tr>
      <?php } ?>
      <tr>
        <td width="14%">
        	Case Number:            
        </td>
        <td colspan="5">
        	<div style="float:right; display:none" id="save_case_number_holder">
            	<div style="display:inline-block" id="feedback_case_number"></div>
           	  <button id="save_case_number" class="one_line header_injury_button">Save</button>
            </div>
       	  <input type="text" class="nospecial header_input" name="case_number" id="case_number" value="<?php echo noSpecialAtAll($adj_number); ?>" onkeyup="checkADJ()" />
        <span class="instructions">ADJ + numbers only</span></td>
      </tr>
      <tr>
        <td>SSN:        </td>
        <td colspan="5">
            <div style="float:right; display:none" id="save_ssn_holder">
            	<div style="display:inline-block" id="feedback_ssn"></div>
            	<button id="save_ssn" class="one_line header_applicant_button">Save</button>
            </div>
      <input name="ssn" type="text" id="ssn" value="<?php echo str_replace("-", "", $social_sec); ?>" onkeyup="checkSSN(event)" class="" />
            <span class="instructions"><span style="font-weight:bold">SSN required</span> if you have one.</span>
        </td>
      </tr>
      <tr>
        <td colspan="6">
        <hr color="#000000" /></td>
      </tr>
      <?php 
	  if ($location_required=="y" && $rad_venue=="") { 
	  		$background = "#FFCC66";
	  } else {
		  $background = "#00FF00";
	  }
	  ?>
      <tr>
        <td colspan="6" style="background:<?php echo  $background; ?>">
    <div style="float:right; display:none" id="save_county_holder">
        	<div style="display:inline-block" id="feedback_county"></div>
            <button id="save_county" class="one_line save_page_button">Save</button>
        </div>
        <strong>Venue choice is based upon (Completion of this section is required)</strong>
          <p>
            <input name="county" type="radio" id="county_residence" value="R" class="save_page" <?php if ($rad_venue=="R"){ echo " checked"; } ?> />
        County of residence of employee (Labor Code section 5501.5(a)(1) or (d).)</p></td>
      </tr>
      <tr>
        <td colspan="6" style="background:<?php echo  $background; ?>"><input name="county" type="radio" id="county_injury" value="I" class="save_page" <?php if ($rad_venue=="I"){ echo " checked"; } ?> />
County where injury occurred (Labor Code section 5501.5(a)(2) or (d).)</td>
      </tr>
      <tr>
        <td colspan="6" nowrap="nowrap" style="background:<?php echo  $background; ?>"><input name="county" type="radio" id="county_principal" value="B" class="save_page" <?php if ($rad_venue=="B" || $rad_venue==""){ echo " checked"; } ?> />
County of principal place of business of employee’s attorney (Labor Code section 5501.5(a)(3) or (d).)</td>
      </tr>
      <tr>
        <td colspan="6">
        	<div style="float:right; display:none" id="save_letter_office_code_holder">
                <div style="display:inline-block" id="feedback_letter_office_code"></div>
                <button id="save_letter_office_code" class="one_line save_page_button">Save</button>
            </div>
          <select name="letter_office_code" id="letter_office_code" class="required nospecial">
            <?php echo implode("\r\n", $arrVenueRows); ?>
          </select>
          <br />
        Select 3 - Letter Office Code For Place/Venue of Hearing (From the Document Cover Sheet)<br />
        <br />
        </td>
      </tr>
      <tr>
        <td colspan="6"><hr color="#000000" /></td>
      </tr>
</table>
<table width="980" border="0" align="center" cellpadding="3" cellspacing="0" style="display:<?php if ($card_id!="") { echo ""; } ?>">
      <tr>
        <td colspan="6">
        	<div style="float:right; display:none" id="save_applicant_holder">
            	<button id="save_applicant">Save Applicant</button>
            </div>
        	<p><strong>Injured Worker (Completion of this section is required)</strong></p>        
        </td>
      </tr>
      <tr>
        <td nowrap="nowrap">First Name: </td>
        <td colspan="2"><input name="first_name" type="text" class="required nospecial applicant_input" id="first_name" value="<?php echo noSpecialAtAll($first); ?>" maxlength="25" />&nbsp;<span class="instructions">25 characters max</span></td>
        <td width="9%" align="right">MI:</td>
        <td colspan="2"><input name="middle_name" type="text" class="nospecial applicant_input" id="middle_name" value="<?php echo noSpecialAtAll($middle); ?>" size="2" maxlength="1" /></td>
      </tr>
      <tr>
        <td nowrap="nowrap">Last Name:</td>
        <td colspan="2"><input name="last_name" type="text" class="required nospecial applicant_input" id="last_name" value="<?php echo noSpecialAtAll($last); ?>" maxlength="25" />&nbsp;<span class="instructions">25 characters max</span></td>
        <td align="right" <?php if ($location_required=="y" && $thedob=="") { ?>style="background:#FFCC66"<?php } ?>>DOB:</td>
        <td colspan="2" <?php if ($location_required=="y" && $thedob=="") { ?>style="background:#FFCC66"<?php } ?>><label>
          <input name="dob" type="text" id="dob" value="<?php echo $thedob; ?>" autocomplete="off" onkeyup="mask(this, mdate);" onblur="mask(this, mdate);" placeholder="mm/dd/yyyy" class="required applicant_input" />
        </label></td>
      </tr>
      <tr>
        <td colspan="6"><input name="address" type="text" class="required nospecial applicant_input" id="applicant_street" value="<?php echo noSpecialAtAll($address1); ?>" size="50" autocomplete="off" />
          <br />
        Street Address/PO Box (Please leave blank spaces between numbers, names or words)</td>
      </tr>
      <tr>
        <td colspan="6"><p>
          <input name="address2" type="text" class="nospecial applicant_input" id="applicant_suite" value="<?php echo noSpecialAtAll($address2); ?>" size="50" autocomplete="off" />
          <br />
          Street Address2/PO Box (Please leave blank spaces between numbers, names or words)</p>        </td>
      </tr>
      
      <tr>
        <td nowrap="nowrap">Zip Code: </td>
        <td width="25%"><input name="zip_code" type="text" class="required nospecial applicant_input" id="zip_code" onkeyup="sendZip(this, '', '')" value="<?php echo $zip; ?>" size="5" autocomplete="off" /> 
          <span class="instructions">enter zip to autofill city and state</span></td>
        <td width="19%">City: </td>
        <td><input name="city" type="text" class="required nospecial applicant_input" id="city" value="<?php echo noSpecialAtAll($city); ?>" autocomplete="off" /></td>
        <td width="12%" align="right">State:</td>
        <td width="21%"><input name="state" type="text" class="required nospecial applicant_input" id="state" value="<?php echo $state; ?>" size="2" autocomplete="off" /></td>
      </tr>
      </table>
<table width="980" border="0" align="center" cellpadding="3" cellspacing="0">
  <tr>
    <td colspan="6"><hr color="#000000" /></td>
  </tr>
  <tr>
    <td colspan="6"><strong>Applicant</strong> (If other than Injured Worker)</td>
  </tr>
  <tr>
    <td colspan="3">Insurance Carrier
        <input name="insurance_carrier" type="radio" id="applicant_insurance_carrier" value="I" onclick="enableApplicant(this)" <?php if ($insurance_carrier=="I") { echo " checked"; } ?> />
    </td>
    <td width="35%">Employer
      <input type="radio" name="insurance_carrier" id="applicant_employer_app" value="E" onclick="enableApplicant(this)" <?php if ($insurance_carrier=="E") { echo " checked"; } ?> /></td>
    <td nowrap="nowrap">Lien Claimant
      <input type="radio" name="insurance_carrier" id="applicant_lien_claimant" value="L" onclick="enableApplicant(this)" <?php if ($insurance_carrier=="L") { echo " checked"; } ?> /></td>
    <td align="right">None
      <input type="radio" name="insurance_carrier" id="applicant_lien_claimant2" onclick="enableApplicant(this)" value="N" <?php if ($insurance_carrier=="N") { echo " checked"; } ?> /></td>
  </tr>
  <tr>
    <td colspan="6"><input name="applicant_name" type="text" id="applicant_name" value="<?php echo $applicant_name; ?>" class="applicant_field" />
      <br />
    Name (Please leave blank spaces between numbers, names or words)</td>
  </tr>
  
  <tr>
    <td colspan="6"><input name="applicant_address" type="text" id="applicant_address" value="<?php echo $applicant_address1; ?>" size="50" class="applicant_field" autocomplete="off" />
        <br />
      Street Address/PO Box (Please leave blank spaces between numbers, names or words)</td>
  </tr>
  <tr>
    <td colspan="6"><p>
      <input name="applicant_address2" type="text" id="applicant_address2" value="<?php echo $applicant_address2; ?>" size="50" autocomplete="off" />
      <br />
      Street Address2/PO Box (Please leave blank spaces between numbers, names or words)</p></td>
  </tr>
  <tr>
    <td width="8%" nowrap="nowrap">Zip Code: </td>
    <td width="23%" nowrap="nowrap"><input name="applicant_zip_code" type="text" id="applicant_zip_code" onkeyup="sendZip(this, 'applicant_', '')" value="<?php echo $applicant_zip; ?>" size="5" autocomplete="off" class="applicant_field" />
        <span class="instructions">enter zip to autofill city and state</span></td>
    <td width="7%">City: </td>
    <td><input name="applicant_city" type="text" id="applicant_city" value="<?php echo $applicant_city; ?>" class="applicant_field" autocomplete="off" /></td>
    <td width="10%" align="right">State:</td>
    <td width="17%"><input name="applicant_state" type="text" id="applicant_state" value="<?php echo $applicant_state; ?>" size="2" class="applicant_field" autocomplete="off" /></td>
  </tr>
</table>
<table width="980" border="0" align="center" cellpadding="3" cellspacing="0">
      <tr>
        <td colspan="6">
          <?php if ((!$blnAllowEdits || $blnADJ) && $location_required!=y) { ?>
          <span class"required_guide" style="background:#CCFFFF; font-weight:bold">Applicant Information cannot be modified for this Case.<?php if (!$blnAllowEdits) { ?>  It is an Additional Filing (Case ID: <?php echo $parent_case_id; ?>).<?php } ?><?php if ($blnADJ) {?>  ADJ number has been assigned<?php } ?></span>
          <?php }  ?>
          &nbsp;
        </td>
      </tr>
  </table>
  <hr />
  <table width="980" border="0" align="center" cellpadding="3" cellspacing="0">
      <tr>
        <td colspan="6" align="center"><hr color="#000000" /></td>
      </tr>
      <tr>
        <td colspan="6"><strong>Employer Information (Completion of this section is required)<br />
        <br />
        </strong></td>
      </tr>
      <tr>
        <td colspan="6">
        <table width="100%" border="0" cellspacing="0" cellpadding="3">
          <tr>
            <td align="center"><input name="employer_insurance" type="radio" id="employer_insurance_i" value="I" onclick="enableInsurance('carrier')" <?php if ($emp_insurance=="I" || $emp_insurance=="") { echo " checked"; } ?> tabindex="1" /> 
            Insured</td>
            <td align="center"><input name="employer_insurance" type="radio" id="employer_insurance_s" value="S" onclick="enableInsurance('rep')" <?php if ($emp_insurance=="S") { echo " checked"; } ?> tabindex="2" />
            Self-Insured</td>
            <td align="center"><input name="employer_insurance" type="radio" id="employer_insurance_l" value="L" onclick="enableInsurance('rep')" <?php if ($emp_insurance=="L") { echo " checked"; } ?> tabindex="3" />
            Legally Uninsured</td>
            <td align="center"><input name="employer_insurance" type="radio" id="employer_insurance_u" value="U" onclick="enableInsurance('')" <?php if ($emp_insurance=="U") { echo " checked"; } ?> tabindex="4" />
            Uninsured</td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td colspan="6"><hr color="#000000" /></td>
      </tr>
      
      <tr>
        <td colspan="6">
        	<?php if (count($arrEmployerOptions) > 0) {
				//clear out values, they are now lookups
				/*
				$emp_street = "";
				$emp_city = "";
				$emp_state = "";
				$emp_zip = "";
				*/
				 ?>
            <input name="employer_name" type="hidden" class="required nospecial" id="employer_name" tabindex="5" value="" size="50" maxlength="56" />
            <select name="employer_select" id="employer_select" tabindex="5" size="<?php echo count($arrEmployerOptions); ?>" onclick="selectEmployer()">
            	<?php echo implode("", $arrEmployerOptions); ?>
            </select>
            <?php } else { ?>
            <input name="employer_name" type="text" class="required nospecial" id="employer_name" tabindex="5" value="<?php echo noSpecialAtAll($emp_name); ?>" size="50" maxlength="56" />
            <?php } ?>
          <br />
        Employer Name (Please leave blank spaces between numbers, names or words)</td>
      </tr>
      <tr>
        <td colspan="6"><input name="employer_street" type="text" class="required nospecial" id="employer_street" value="<?php echo noSpecialAtAll($emp_street); ?>" size="50" tabindex="6" onkeyup="validCharacters(this)" onblur="validCharacters(this)" />
        <br />
        Employer Street Address/PO Box (Please leave blank spaces between numbers, names or words)</td>
      </tr>
      <tr>
        <td width="13%">Zip Code: </td>
        <td width="24%"><input name="employer_zip" type="text" class="required nospecial" id="employer_zip" onkeyup="noAlpha(this);sendZip(this, 'employer_', '')" value="<?php echo $emp_zip; ?>" size="5" tabindex="7" /></td>
        <td width="4%" align="right">City: </td>
        <td width="30%"><input name="employer_city" type="text" class="required nospecial" id="employer_city" value="<?php echo noSpecialAtAll($emp_city); ?>" tabindex="8" /></td>
        <td width="10%" align="right">State:</td>
        <td width="19%"><input name="employer_state" type="text" class="required nospecial" id="employer_state" value="<?php echo $emp_state; ?>" size="2" tabindex="9" /></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td colspan="5"><span class="instructions">enter zip to autofill city and state</span></td>
      </tr>
      <tr>
        <td colspan="6"><hr color="#000000" />        </td>
      </tr>
      
      <tr>
        <td colspan="6"><strong>Insurance Carrier Information (If known and if applicable - include even if carrier is adjusted by claims administrator)
        </strong></td>
      </tr>
      <tr>
        <td colspan="6" nowrap="nowrap">
       	  <input name="carrier_eams_number" id="carrier_eams_number" type="hidden" value="<?php echo $car_eams_number; ?>" class="insurance_info carrier nospecial" />
            <select name="carrier_id" id="carrier_id" onchange="fillCarrierName()">
            <?php echo implode("", $arrCarriers); ?>
            </select>
            <input type="hidden" name="carrier_name" id="carrier_name" value="<?php echo $car_name; ?>" />
        	<div id="list_carrier_searches" style="display:none; position:absolute; z-index:99; background:#FFFFFF; text-align:left"></div>
			 <span class="instructions">enter terms in this box to look up EAMS info. Click on drop down to select.</span><br />
			Insurance Carrier Name (Please leave blank spaces between numbers, names or words)<br />
			<span style="font-size:0.8em">EAMS records in <span style="color:red">red</span> and <span style="text-decoration:line-through">crossed-out</span> are no longer active</span></td>
      </tr>
      <tr>
        <td colspan="6"><p>
          <input name="carrier_street" type="text" class="insurance_info carrier required nospecial" id="carrier_street" value="<?php echo noSpecialAtAll($car_street); ?>" size="50" />
          <br />
          Insurance Carrier Street Address/PO Box (Please leave blank spaces between numbers, names or words)<br />
        </p>        </td>
      </tr>
      <tr>
        <td>City: </td>
        <td><input name="carrier_city" type="text" class="insurance_info carrier required nospecial" id="carrier_city" value="<?php echo noSpecialAtAll($car_city); ?>" /></td>
        <td align="right">State:</td>
        <td><input name="carrier_state" type="text" class="insurance_info carrier required nospecial" id="carrier_state" value="<?php echo noSpecialAtAll($car_state); ?>" size="2" /></td>
        <td align="right">Zip Code: </td>
        <td><input name="carrier_zip" type="text" class="insurance_info carrier required nospecial" id="carrier_zip" value="<?php echo $car_zip; ?>" size="5" /></td>
      </tr>      
      <tr>
        <td>Claim #</td>
        <td><input name="claim_number" type="text" id="claim_number" value="<?php echo $claim_number; ?>" /></td>
        <td align="right">Adjuster:</td>
        <td><input name="adjuster" type="text" id="adjuster" value="<?php echo $adjuster; ?>" /></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td colspan="6"><hr color="#000000" />        </td>
      </tr>
      <tr>
        <td colspan="6"><strong>Claims Administrator Information (If known and if applicable)</strong>&nbsp;<span class="instructions">
        <input type="checkbox" name="same_carrier" id="same_carrier" />
        click to make <strong>Claims Administrator same as Insurance Carrier</strong></span></td>
      </tr>
      <tr>
        <td colspan="6">
        <input name="rep_eams_number" id="rep_eams_number" type="hidden"  value="<?php echo $rep_eams_number; ?>" class="insurance_info rep carrier nospecial" />
        <input name="rep_name" type="text" class="insurance_info rep carrier nospecial" id="rep_name" onKeyDown="typeSearch(event, 'rep')"  onkeyup="adminLookup()" value="<?php echo noSpecialAtAll($rep_name); ?>" size="50" autocomplete="off" />
        <div id="list_rep_searches" style="display:none; position:absolute; z-index:99; background:#FFFFFF; text-align:left; border:1px solid black; padding:3px"></div>
          <span class="instructions">enter terms in this box  to look up EAMS info. Click on drop down to select.</span><br />
        Name (Please leave blank spaces between numbers, names or words)<br />
			<span style="font-size:0.8em">EAMS records in <span style="color:red">red</span> and <span style="text-decoration:line-through">crossed-out</span> are no longer active</span></td>
      </tr>
      <tr>
        <td colspan="6"><input name="rep_street" type="text" class="insurance_info rep carrier nospecial" id="rep_street" value="<?php echo noSpecialAtAll($rep_street); ?>" size="50" />
          <br />
        Street Address/PO Box (Please leave blank spaces between numbers, names or words)</td>
      </tr>
      <tr>
        <td>City: </td>
        <td><input name="rep_city" type="text" class="insurance_info rep carrier nospecial" id="rep_city" value="<?php echo noSpecialAtAll($rep_city); ?>" /></td>
        <td align="right">State:</td>
        <td><input name="rep_state" type="text" class="insurance_info rep carrier nospecial" id="rep_state" value="<?php echo $rep_state; ?>" size="2" /></td>
        <td>Zip Code: </td>
        <td><input name="rep_zip" type="text" class="insurance_info rep carrier nospecial" id="rep_zip" value="<?php echo $rep_zip; ?>" size="5" /></td>
      </tr>
      <tr>
        <td colspan="6"><hr color="#000000" />        </td>
      </tr>
      <tr>
        <td colspan="6">
        	<div style="float:right" id="injury_save_feedback">
            	<div id="saved_image" style="display:none"></div>
            </div>
        	<strong><a name="doi" id="doi"></a>IT IS CLAIMED THAT (Complete all relevant information):</strong>
        </td>
      </tr>
      
      <tr>
        <td colspan="6">
        	<div style="display:inline-block">OCCUPATION AT THE TIME OF INJURY</div>
            <div style="display:inline-block">
                <input type="text" name="occupation" id="occupation" class="doifield required nospecial" value="<?php echo noSpecialAtAll($case_job_desc); ?>" onblur="saveInjury(this, 'occupation')" onkeyup="scheduleOccupationLookup()" autocomplete="off" style="width:350px" />
                <div id="list_occupations" style="display:none; position:absolute; z-index:99; background:#FFFFFF; text-align:left; border:1px solid; padding:2px"></div>
            </div>
          </td>
      </tr>
      <?php if ($order_doi!="") { ?>
      <tr>
        <td colspan="6" align="left" style="background:#FF0" ><strong>Imported DOI:</strong> <?php echo $order_doi; ?></td>
      </tr>
      <?php } ?>
      <tr>
        <td colspan="4" align="left" <?php if ($doi_required=="y") { ?>style="background:#FFCC66"<?php } ?>><input type="radio" name="injury_type" id="specific_injury" value="S" onclick="enableInjuryDates('specific_injury_date')" <?php if ($rad_injurytype1=="1" && $case_injury_start!="0000-00-00") { echo " checked"; } ?> />
specific injury
  on this date:
        <input type="text" name="specific_injury_date" id="specific_injury_date" autocomplete="off" onkeyup="mask(this, mdate);" onblur="saveInjury(this, 'start_date')" placeholder="mm/dd/yyyy" class="injury_date <?php if ($rad_injurytype1=="1" || ($rad_injurytype1=="0" && $rad_injurytype2=="0")) { ?>required<?php } ?>" <?php if ($rad_injurytype2=="1" || ($rad_injurytype1=="" && $rad_injurytype2=="")) { ?>disabled="disabled"<?php } ?> value="<?php if ($rad_injurytype1=="1" && $case_injury_start!="0000-00-00") { echo date("m/d/Y", strtotime($case_injury_start)); } ?>" /></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td colspan="6" align="left" <?php if ($doi_required=="y") { ?>style="background:#FFCC66"<?php } ?>>
        <input type="radio" name="injury_type" id="cumulative_injury" value="C" onclick="enableInjuryDates('ct_injury_start_date|ct_injury_end_date')" <?php if ($rad_injurytype2=="1") { echo " checked"; } ?> />
cumulative injury which began on
  <input type="text" name="ct_injury_start_date" id="ct_injury_start_date" class="<?php if ($rad_injurytype2=="1" || ($rad_injurytype1=="0" && $rad_injurytype2=="0")) { ?>required<?php } ?> injury_date" <?php if ($rad_injurytype1=="1" || $rad_injurytype1=="") { ?>disabled="disabled"<?php } ?> value="<?php if ($rad_injurytype2=="1" && $case_injury_start!="0000-00-00") { echo date("m/d/Y", strtotime($case_injury_start)); } ?>"  autocomplete="off" onkeyup="mask(this, mdate);" onblur="saveInjury(this, 'start_date')" placeholder="mm/dd/yyyy" /> 
          through 
        <input type="text" name="ct_injury_end_date" id="ct_injury_end_date" class="<?php if ($rad_injurytype2=="1" || $rad_injurytype1=="") { ?>required<?php } ?> injury_date" <?php if ($rad_injurytype1=="1" || $rad_injurytype1=="") { ?>disabled="disabled"<?php } ?> value="<?php if ($rad_injurytype2=="1" && $case_injury_end!="0000-00-00") { echo date("m/d/Y", strtotime($case_injury_end)); } ?>" autocomplete="off" onkeyup="mask(this, mdate);" onblur="mask(this, mdate);checkDateSpan()" placeholder="mm/dd/yyyy" /></td>
      </tr>
      <tr>
        <td colspan="6">The injury occurred at: 
        <input name="injury_street" type="text" id="injury_street" size="50" class="doifield doi_address required" value="<?php echo $case_injury_street; ?>" autocomplete="off" onkeyup="validCharacters(this)" onblur="validCharacters(this); saveInjury(this, 'street')" onfocus="checkDOB()" /> 
        <input type="checkbox" name="same_employer" id="same_employer" />
        <span class="instructions">click to make <strong>Injury Address same  as Employer Address<br />
        </strong></span></td>
      </tr>
      
      <tr>
        <td valign="top">Zip Code: </td>
        <td valign="top"><input name="injury_zip" type="text" id="injury_zip" size="5" onkeyup="noAlpha(this);sendZip(this, 'injury_', '')" class="doifield required doi_address" value="<?php echo $case_injury_zip; ?>" autocomplete="off" onblur="checkInjuryCA();saveInjury(this, 'zip');saveInjury(document.getElementById('injury_city'), 'city');saveInjury(document.getElementById('injury_state'), 'state')" />
          <br />
        <span class="instructions"><em>enter zip to autofill city and state</em></span></td>
        <td valign="top">City: </td>
        <td valign="top"><input type="text" name="injury_city" id="injury_city" class="doifield required doi_address" value="<?php echo $case_injury_city; ?>" autocomplete="off" onblur="saveInjury(this, 'city')" /></td>
        <td align="right" valign="top">State:</td>
        <td valign="top"><input name="injury_state" type="text" id="injury_state" size="2" class="doifield required doi_address" value="<?php echo $case_injury_state; ?>" autocomplete="off" onblur="saveInjury(this, 'state')" /></td>
    </tr>
      <tr>
        <td colspan="6" align="right"><span class="instructions">Injury location <strong>must be</strong> in California</span></td>
      </tr>
      <tr>
        <td colspan="6" align="left"><hr color="#000000" /></td>
      </tr>
      <tr>
        <td colspan="6" align="left">
            <input type="button" class="submit" id="submit" value="Save" disabled="disabled" />
            <span class="required_guide" style="background:#CCFFFF">Please fill out all Required Fields</span>
            <span id="proceed_2" style="display:<?php if ($jetfile_id=="") { ?>none<?php } ?>">
                <a href="app_3_4.php?case_id=<?php echo $case_id; ?>&injury_id=<?php echo $injury_id; ?>&jetfile_id=<?php echo $jetfile_id; ?>">Proceed to Page 2</a>
            </span>
        </td>
      </tr>
  </table>
</form>
<script language="javascript">
var client_birth_date = "<?php echo $thedob; ?>";
var fillCarrierName = function() {
	var obj = document.getElementById("carrier_id");

	companyLookup(obj.value);
}
var selectCompany = function(company_id, eams_type) {
	var url = "../api/eams_" + eams_type + "/" + company_id;
	$.ajax({
		url:url,
		type:'GET',
		dataType:"json",
		success:function (data) {
			$("#rep_name").val(data.firm_name);
			$("#rep_street").val(data.street_1);
			$("#rep_city").val(data.city);
			$("#rep_state").val(data.state);
			$("#rep_zip").val(data.zip_code);
			
			if (!isNaN(data.eams_ref_number)) {
				$("#rep_eams_number").val(data.eams_ref_number);
			} else {
				$("#rep_eams_number").val(0);
			}
		}
	});
	
	$("#list_carrier_searches").css("display", "none");
	$("#list_rep_searches").css("display", "none");
}
var companyLookup = function(company_id) {
	var url = "../api/corporation/carrier/" + company_id;
	$.ajax({
		url:url,
		type:'GET',
		dataType:"json",
		success:function (data) {
			$("#carrier_name").val(data.company_name);
			$("#carrier_street").val(data.street);
			$("#carrier_city").val(data.city);
			$("#carrier_state").val(data.state);
			$("#carrier_zip").val(data.zip);
			$("#adjuster").val(data.full_name);
			$("#claim_number").val(data.claim_number);
			if (!isNaN(data.parent_corporation_uuid)) {
				$("#carrier_eams_number").val(data.parent_corporation_uuid);
			} else {
				$("#carrier_eams_number").val(0);
			}
		}
	});
}
var fileApp = function(jetfile_case_id) {
	var formValues = "jetfile_case_id=" + jetfile_case_id;
	var url = '../api/jetfile/file';
	$("#jetfile_feedback").html("Filing APP&nbsp;<i class='icon-spin4 animate-spin' style='font-size:1.6em; color:black'></i>");
	//console.log("Filing APP");
	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else {
				$("#jetfile_feedback").html("Filed APP");
				//console.log("Filed APP");
				//console.log(data);
				saveFilingID(data.filing_id, data.filing_date)
			}
		}
	});
}
function saveFilingID(filing_id, filing_date) {
	var formValues = "case_id=<?php echo $case_id; ?>&injury_id=<?php echo $injury_id; ?>&jetfile_id=<?php echo $jetfile_id; ?>&app_filing_id=" + filing_id + "&app_filing_date=" + filing_date;
	var url = "../api/jetfile/app/filingid";
	
	$("#jetfile_feedback").html("Saving Filing ID&nbsp;<i class='icon-spin4 animate-spin' style='font-size:1.6em; color:black'></i>");
	//console.log("Saving Filing ID");
	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else {
				//indicate that we are done;
				$("#jetfile_feedback").html("Filed &#10003;");
				//console.log("Filed complete");
			}
		}
	});
}
var sendApp = function() {
	var formValues = "case_id=<?php echo $case_id; ?>&injury_id=<?php echo $injury_id; ?>&jetfile_id=<?php echo $jetfile_id; ?>";
	var url = '../api/jetfile/send';
	$("#jetfile_feedback").html("Sending to EAMS&nbsp;<i class='icon-spin4 animate-spin' style='font-size:1.6em; color:black'></i>");
	//console.log("Sending to EAMS");
	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
				$("#jetfile_feedback").html(data.error.text);
			} else {
				$("#jetfile_feedback").html("Saved &#10003;");
				//console.log("Saved");
				//console.log(data);
				jetfile_case_id = data.case_id;
				//update the cse_jetfile
				updateJetfile(jetfile_case_id);
			}
		}
	});
}
var updateJetfile = function(jetfile_case_id) {
	var formValues = "jetfile_id=<?php echo $jetfile_id; ?>&jetfile_case_id=" + jetfile_case_id;
	var url = '../api/jetfile/updatecase';
	$("#jetfile_feedback").html("Updating ID&nbsp;<i class='icon-spin4 animate-spin' style='font-size:1.6em; color:black'></i>");
	//console.log("Updating ID");
	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else {
				$("#jetfile_feedback").html('ID Updated<br />Jetfile Case ID:<a href="javascript:fileApp(' + jetfile_case_id + ')">File ' + jetfile_case_id + '</a>');
				//console.log("ID Updated");
				//file the app
				//fileApp(jetfile_case_id);
			}
		}
	});
}
var checkApplicant = function() {
	var jetfile_id = $("#jetfile_id").val();
	var case_id = $("#case_id").val();
	var injury_id = $("#injury_id").val();
	var dob = $("#dob").val();
	var ssn = $("#ssn").val();
	var uploads = $("#uploads").val();
	var bodypart_count = $("#bodypart_count").val();
	var liab = $("#liab").val();
	
	var case_injury_start = "";
	var case_injury_end = "";
	//ct?
	var injury_type = document.getElementsByName("injury_type");
	if (injury_type[0].checked) {
		var case_injury_start = $("#specific_injury_date").val();
		var case_injury_end = "0000-00-00";
	}
	if (injury_type[1].checked) {
		var case_injury_start = $("#ct_injury_start_date").val();
		var case_injury_end = $("#ct_injury_end_date").val();
	}
	
	if (jetfile_id=="" || liab == "" || bodypart_count == "" || uploads == "0" || uploads == "" || dob=="" || ssn=="" || case_injury_start=="" || case_injury_end=="") {
		var arrReasons = [];
		if (uploads=="" || uploads=="0") {
			uploads = "no uploads";
			arrReasons.push("Please upload documents");
		}
		if (liab=="") {
			arrReasons.push("Please assign Liability (Page 2)");
		}
		if (bodypart_count=="") {
			arrReasons.push("Please select Body Parts (Page 2)");
		}
		if (dob=="") {
			arrReasons.push("Please add DOB");
		}
		if (ssn=="") {
			arrReasons.push("Please add SSN");
		}
		if (case_injury_start=="") {
			arrReasons.push("Please set DOI");
		}
		$("#jetfile_feedback").html('Not ready to file: ' + arrReasons.join(", "));
		return false;
	}
	var formValues = "case_id=" + case_id + "&injury_id=" + injury_id + "&dob=" + dob;
	formValues += "&ssn=" + ssn.replaceAll("-", "");	
	var url = '../api/jetfile/check/applicant';
	var checkValues = formValues;
	checkValues += "&start=" + case_injury_start;
	checkValues += "&end=" + case_injury_end;
	
	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: checkValues,
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else {
				if (data.case_id != "-1") {
					//$("#jetfile_feedback").html('&#9992;');
					var jetfile_case_id = data.case_id;
					
					if (Number(data.uploads_count) < 4) {
						$("#jetfile_feedback").html("<span style='background:orange; color:black; padding:2px'>" + (4 - Number(data.uploads_count)) + ' Upload(s) Needed</span>');
						return;
					}
					//update the cse_jetfile
					updateJetfile(jetfile_case_id);
					
					<?php if ($kase->app_filing_id=="0") { ?>
					$("#jetfile_feedback").html('<div style="float:right">Jetfile Case ID:<a href="javascript:fileApp(' + data.case_id + ')">File ' + data.case_id + '</a> -> READY TO FILE - CONTACT SUPPORT</div>');
					<?php } else { ?>
					$("#jetfile_feedback").html('<div style="float:right">App for ADJ has been filed (<?php echo $kase->jetfile_case_id; ?>)</div>');
					checkAppStatus("<?php echo $kase->app_filing_id; ?>");
					<?php } ?>
				} else {
					$("#jetfile_feedback").html('<a href="javascript:sendApp()">Send App to EAMS</a>');
				}
				$("#jetfile_feedback").css("display", "inline-block");
			}
		}
	});
}
var checkAppStatus = function(filing_id) {
	var url  = "../api/jetfile/app/status/" + filing_id;
	$.ajax({
		url:url,
		type:'GET',
		dataType:"json",
		success:function (data) {
			var current_feedback = $("#jetfile_feedback").html();
			var thestatus = String(data.status);
			
			switch(thestatus) {
				case "1":
					thestatus = "<span style='background:pink;color:black'>App Pending</span>";
					break;
				case "2":
					thestatus = "<span style='color:orange;background:black'>APP Processing</span>";
					break;
				case "3":
					thestatus = "<span style='color:black;background:orange'>APP Validation Succeeded</span>";
					break;
				case "5":
					thestatus = '<span style="color:white;background:green">APP ✓</span><div style="display:"><a href="javascript:getPDF(\'app\', \'app\', true)">APP PDF</a>&nbsp;|&nbsp;<a href="javascript:getPDF(\'app_cover\', \'app\', false)">APP + POS PDF</a></div>';
					
					break;
				default:
					thestatus = "Sent";
			}
			var current_feedback = $("#jetfile_feedback").html();
			current_feedback = current_feedback.replace("<br>" + thestatus, "");
			$("#jetfile_feedback").html(current_feedback + "<br>" + thestatus);
			
			<?php if (!$blnValidADJ) { ?>
			var adj_number = data.adj_number;
			if (adj_number!="" && adj_number!=null) {
				updateCaseADJ(adj_number);
			}
			<?php } ?>
		}
	});
}
var updateCaseADJ = function(adj_number) {
	var formValues = "injury_id=<?php echo $injury_id; ?>&adj_number=" + adj_number;
	var url = '../api/jetfile/updateadj';
	current_feedback = $("#jetfile_feedback").html();
	$("#jetfile_feedback").html(current_feedback + "<br />Updating ADJ " + adj_number);

	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else {
				current_feedback = $("#jetfile_feedback").html();
				$("#jetfile_feedback").html(current_feedback + "<br>ADJ Updated");
			}
		}
	});
}
var adminLookup = function() {
	enableSave();
	//return;
	
	var obj = $("#rep_name");
	var the_value = obj.val();
	
	//look for the rep
	if (the_value.length > 3) {	
		var url = "../api/eams_claimant_rep_token?q=" + the_value;
		$.ajax({
			url:url,
			type:'GET',
			dataType:"json",
			success:function (data) {
				//fill up the list
				var arrLength = data.length;
				var arrRows = [];
				for(var i = 0; i < arrLength; i++) {
					var datum = data[i];
					var row = "<tr><td valign='top' align='left'><a href='javascript:selectCompany(" + datum.id + ", \"" + datum.eams_type + "\")'>" + datum.eams_ref_number + "</a></td>";
					row += "<td valign='top' align='left'>" + datum.company_name + "</td>";
					arrRows.push(row);
				}
				var list_rows = "<table>" + arrRows.join("") + "</table>";
				document.getElementById("list_rep_searches").innerHTML = list_rows;
				document.getElementById("list_rep_searches").style.display = "";
				enableSave();
			}
		});
	}
}
var occupation_lookup_id = false;
var scheduleOccupationLookup = function() {
	clearTimeout(occupation_lookup_id);
	occupation_lookup_id = setTimeout(function() {
		occupationLookup();
	}, 700);
}

var occupationLookup = function() {
	var obj = $("#occupation");
	var the_value = obj.val();
	
	//look for the zip after 5
	if (the_value.length > 3) {	
		var url = "../api/occupation?q=" + the_value;
		$.ajax({
			url:url,
			type:'GET',
			dataType:"json",
			success:function (data) {
				var arrLength = data.length;
				var arrOptions = [];
				for(var i = 0; i < arrLength; i++) {
					var option = "<div><a href='javascript:setOccupation(\"" + data[i].title + "\")'>" + data[i].title + "</a></div>";
					arrOptions.push(option);
				}
				$("#list_occupations").html(arrOptions.join("\r\n"));
				$("#list_occupations").show();
				enableSave();
			}
		});
	} else {
		//hide
		$("#list_occupations").hide();
		$("#list_occupations").html("");
		
	}
}
var setOccupation = function(title) {
	$("#occupation").val(title);
	//hide
	$("#list_occupations").hide();
	$("#list_occupations").html("");
	
	//save
	saveInjury(document.getElementById("occupation"), "occupation");
}
var enableApplicant = function(obj) {
	var elements = $('.applicant_field');
	if (obj.value!="N") {
		elements.addClass("required");
	} else {
		elements.removeClass("required");
	}
	if (!blnInit) {
		enableSave();
	}
}
var setVenue = function(code) {
	var letter_office_code = document.getElementById("letter_office_code");
	letter_office_code.selectedIndex = 0;
	for(int=0;int<letter_office_code.options.length;int++){
		var the_code = letter_office_code.options[int];
		if (the_code.value == code) {
			letter_office_code.selectedIndex = int;
			break; 
		}
	}
	if (!blnInit) {
		enableSave();
	}
}
var checkSSN = function(event) {
	var ssn = $("#ssn");
	if (ssn.val().length > 0 && ssn.val().length < 9) {
	//	alert("The SSN must be 9 digits long.");
	//	ssn.focus();
	} else {
		showSaveLink(event);
	}
}

function initMask(){

	oSSNMask = new Mask("#########");
	oSSNMask.attach(document.form1.ssn);
	
	oZipMask = new Mask("#####");
	oZipMask.attach(document.form1.zip_code);
	
	//oDateMask0 = new Mask("mm/dd/yyyy", "date");
	//oDateMask0.attach(document.form1.dob);
	if (!blnInit) {
		enableSave();
	}
}
var saveInjury = function(obj, field) {
	//verify
	if (field=="case_number") {
		//correct length and prefix
		if (!checkADJ()) {
			return;
		}
		field = "adj_number";
	}
	
	if (field=="start_date" || field=="end_date") {
		//correct length
		if (obj.value.length < 10) {
			//not a valid date
			return;
		}
	}
	var arrList = obj.classList;
	var blnOneLine = false;
	for (var i = 0; i < arrList.length; i++) { 
	  if (arrList[i] === "one_line") { 
		blnOneLine = true;
		break;
	  } 
	}
	
	if (blnOneLine) {
		//reset the object
		obj = document.getElementById(obj.id.replace("save_", ""));
		var field_id = obj.id.replace("save_", "");
		field_id = "#feedback_" + field_id;
	} else {
		var field_id = "#saved_image";
	}
	$(field_id).html('Saving...');
	$(field_id).show();
	var formValues = "table_name=injury&case_id=<?php echo $case_id; ?>&id=<?php echo $injury_id; ?>&" + field + "=" + obj.value;
	var url = "../api/injury/update";
	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
		success:function (data) {
			$(field_id).html('Saved&nbsp;&#10003;');
			
			setTimeout(
				function() {
					$(field_id).html('');
					$(field_id).fadeOut();
				}, 2500
			);
		}
	});
}
var savePage = function(event) {
	event.preventDefault();
	
	var blnShowFeedback = (event.target.className.indexOf("save_page_button") > -1);
	
	if (blnShowFeedback) {
		var field_id = event.target.id.replace("save_", "");
		field_id = "#feedback_" + field_id;
		
		$(field_id).html('Saving...');
		$(field_id).show();
	}
	var submit_button = $("#submit");
	submit_button.prop("disabled", true);
	submit_button.val("Saving");
	var formValues = $("#form1").serialize();
	var url = "../api/jetfile/save/app";
	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
			success:function (data) {
				submit_button.val("Saved !!");
				submit_button.prop("disabled", false);
				
				if (blnShowFeedback) {
					$(field_id).html('Saved&nbsp;&#10003;');
				}
				$("#jetfile_id").val(data.id);
				
				document.getElementById("proceed_1").style.display = "";
				document.getElementById("proceed_2").style.display = "";
	
				setTimeout(function() {
					submit_button.val("Save");
				}, 2500);
				setTimeout(
					function() {
						$(field_id).html('');
						$(field_id).fadeOut();
					}, 2500
				);
			}
	});
}
var blnInit = false;
var init = function() {
	blnInit = true;
	initMask();

	var elements = $('.required');

	elements.on("change", enableSave);
	elements.on("keyup", releaseMe);
	
	var nospecials = $('.nospecial');
	nospecials.on("keyup", cleanMe);
	nospecials.on("blur", cleanMe);
	
	<?php if ($letter_office_code!="") { ?>
	setVenue('<?php echo $letter_office_code; ?>');
	<?php } ?>
	<?php if ($insurance_carrier!="" && $insurance_carrier!="N") { ?>
	var elements = $('.applicant_field');
	elements.addClass("required");
	<?php } ?>
	
	$(".submit").on("click", savePage);
	
	$(".applicant_input").on("keyup", showSaveApplicantLink);
	$(".applicant_input").on("blur", triggerSaveApplicant);
	$("#save_applicant").on("click", saveApplicant);
	
	//generalize save for tables in ikase
	$(".header_input").on("keydown", showSaveLink);
	$(".header_injury_button").on("click", saveHeaderInjury);
	$(".header_applicant_button").on("click", saveHeaderApplicant);
	
	//venue
	$("#letter_office_code").on("click", showSaveLink);
	$("#save_letter_office_code").on("click", saveVenue);
	
	//save the entire page as json for some elements not found in ikase
	$(".save_page").on("change", showSavePageLink);
	$(".save_page_button").on("click", savePage);
	
	$("#same_employer").on("change", sameEmployer);
	$("#same_carrier").on("change", sameCarrier);
	
	<?php if (count($arrEmployerOptions) > 0 && $emp_name!="") { ?>
	var emp_options = document.getElementById("employer_select").options;
	var emp_name = '<?php echo addslashes($emp_name); ?>';
	for (var i = 0; i < emp_options.length; i++) {
		if (emp_options[i].text.indexOf(emp_name) > -1) {
			//emp_options[i].selected = true;
			 document.getElementById("employer_select").value = emp_options[i].value;
			 selectEmployer();
			break;
		}
	}
	<?php } ?>
	
	blnInit = false;
	
	<?php if ($emp_insurance=="U") { ?>
	enableInsurance("");
	<?php } else { ?>
	enableInsurance("carrier|rep");
	<?php } ?>
	
	//enableSave();
}
var enableInsurance = function(showclassname) {
	$('.insurance_info').removeClass("required");
	$('.insurance_info').css("background", "none");
	$('.insurance_info').css("border", "none");
	
	if (showclassname!="") {
		var arrClasses = showclassname.split("|");
		for(int=0;int<arrClasses.length;int++) {
			
			<?php if ($doi_required=="y") { ?>
			if (arrClasses[int]=="rep") {
			//	continue;
			}
			<?php } ?>
			$("." + arrClasses[int]).addClass("required");
		}
	}
	<?php if ($doi_required=="y") { ?>
	//$('.rep').removeClass("required");
	$("#occupation").removeClass("required");
	<?php } ?>
	/*
	if (showclassname=="carrier") {
		$('.rep').removeClass("required");;
	}
	*/
	//hide any lists
	$("#list_carrier_searches").css("display", "none");
	$("#list_rep_searches").css("display", "none");
	//is save still allowed
	checkDOB();
}
var sameCarrier = function() {
	var same_carrier = document.getElementById("same_carrier");
	if (same_carrier.checked) {
		//get info from carrier address into rep address
		var carrier_eams_number = document.getElementById("carrier_eams_number");
		var carrier_name = document.getElementById("carrier_name");
		var carrier_street = document.getElementById("carrier_street");
		var carrier_city = document.getElementById("carrier_city");
		var carrier_state = document.getElementById("carrier_state");
		var carrier_zip = document.getElementById("carrier_zip");
		//rep
		var rep_eams_number = document.getElementById("rep_eams_number");
		var rep_name = document.getElementById("rep_name");
		var rep_street = document.getElementById("rep_street");
		var rep_city = document.getElementById("rep_city");
		var rep_state = document.getElementById("rep_state");
		var rep_zip = document.getElementById("rep_zip");
		
		rep_eams_number.value = carrier_eams_number.value;
		rep_name.value = carrier_name.value;
		rep_street.value = carrier_street.value;
		rep_city.value = carrier_city.value;
		rep_state.value = carrier_state.value;
		rep_zip.value = carrier_zip.value;
	}
	enableSave();
}
var sameEmployer = function() {
	var same_employer = document.getElementById("same_employer");
	if (same_employer.checked) {
		//get info from employer address into injury address
		var employer_street = document.getElementById("employer_street");
		var employer_city = document.getElementById("employer_city");
		var employer_state = document.getElementById("employer_state");
		var employer_zip = document.getElementById("employer_zip");
		//must be in california
		if (employer_state.value!="CA") {
			clearInjuryAddress();
			return;
		}
		//injury
		var injury_street = document.getElementById("injury_street");
		var injury_city = document.getElementById("injury_city");
		var injury_state = document.getElementById("injury_state");
		var injury_zip = document.getElementById("injury_zip");
		
		injury_street.value = employer_street.value;
		injury_city.value = employer_city.value;
		injury_state.value = employer_state.value;
		injury_zip.value = employer_zip.value;
	}
}
var main_document = "";
var getPDF = function(form, doc, blnSingleForm) {
	main_document = doc;
	
	if (typeof blnSingleForm == "undefined") {
		blnSingleForm = true;
	}
	var url = '../api/jetfile/getpdf';
	var formValues = 'case_id=<?php echo $case_id; ?>&form=' + form;
	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
			success:function (data) {
				if (!data) {
					requestPDF(form, main_document, blnSingleForm);
					//alert("request");
				} else {
					var url = "../uploads/<?php echo $_SESSION['user_customer_id']; ?>/<?php echo $case_id; ?>/jetfiler/" + data.document_filename;
					//window.open(url);
					
					requestPDF(form, main_document, blnSingleForm);
				}
			}
	});
}
var requestPDF = function(form, main_document, blnSingleForm) {
	if (typeof blnSingleForm == "undefined") {
		blnSingleForm = true;
	}
	var first_form = form;
	stack = form;
	
	var pos_description = "";
	switch(first_form) {
		case "app":
			stack = 'app';
			break;
		case "app_cover":
			first_form = "cover";
			stack = 'cover|app_cover|pos';
			pos_description = "Application for Adjudication; compliance with Labor Code Section 4906(g); Fee Disclosure Statement; Venue Authorization";
			break;
	}
	
	var redirect = "filename";
	if (blnSingleForm) {
		redirect = "base64";
	}
	//var url = 'https://www.cajetfile.com/pdf_' + first_form + '.php';
	var url = '../api/jetfile/requestpdf';
	var formValues = 'form=' + first_form + '&stack=' + stack + '&pos_description=' + pos_description + '&cus_id=<?php echo $customer->jetfile_id; ?>&suid=outstanding&case_id=<?php echo $kase->jetfile_case_id; ?>&nopublish=y&redirect=' + redirect + '&ikase_cus_id=<?php echo $_SESSION['user_customer_id']; ?>&ikase_case_id=<?php echo $case_id; ?>&ikase_injury_id=<?php echo $injury_id; ?>&ikase_user_id=<?php echo $_SESSION['user_plain_id']; ?>&ikase_user_name=<?php echo $_SESSION['user_name']; ?>';
	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
			success:function (data) {
				//if (blnSingleForm) {
					console.log(data);
					return;
					
					var url = "../uploads/<?php echo $_SESSION['user_customer_id']; ?>/<?php echo $case_id; ?>/jetfiler/" + data.filename;
					window.open(url);
				
			}
	});
	
}
<?php if (count($arrEmployerOptions) > 0) { ?>
var arrEmployerOptions = JSON.parse('<?php echo addslashes(json_encode($arrEmployerInfo)); ?>');
function selectEmployer() {
	var employer_id = $("#employer_select").val();
	for(var i = 0; i < arrEmployerOptions.length; i++) {
		var employer = arrEmployerOptions[i];
		var option_id = employer.id;
		if (employer_id==option_id) {
			$("#employer_name").val(employer.name);
			$("#employer_street").val(employer.street);
			$("#employer_city").val(employer.city);
			$("#employer_state").val(employer.state);
			$("#employer_zip").val(employer.zip);
			enableSave();
			
			break;
		}
	}
}
<?php } ?>
</script>
</body>
</html>

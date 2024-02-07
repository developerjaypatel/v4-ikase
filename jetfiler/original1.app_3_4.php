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

include("../api/manage_session.php");
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
	die();
}
if ($injury_id == "" || $injury_id < 0 || !is_numeric($injury_id)) {
	die();
}

include("jetfile_kase.php");

if ($jetfile_id == "") {
	$jetfile_id = $kase->jetfile_id;
}
//for page 2, we need to have saved page 1
if ($jetfile_id == "" || $jetfile_id < 0 || !is_numeric($jetfile_id)) {
	die("<script language='javascript'>parent.location.href='app_1_2.php?case_id=" . $case_id . "&injury_id=" . $injury_id . "'</script>");
}

$body1 = "";
$body2 = "";
$body3 = "";
$body4 = "";
$case_injury_desc = "";
$txtAppPayRate = "";
$radPayRate = "";
$txtLastDayOff = "";
$txtTips = "";
$radTipsTime = "";
$dtFinalStart = "";
$dtFinalEnd = "";
$dtSecondStart = "";
$dtSecondEnd = "";
$compTotal = "";
$compWeekly = "";
$txtAppHoursWorked = "";
$compLastPmt = "";
$radUiPaid = "";
$radCompPaid = "";

//defaults
$blnAllowEdits = true;
$blnADJ = false;




//injury
$sql = " SELECT inj.*, inj.injury_id id, inj.injury_uuid uuid, ccase.case_id,
		IFNULL(occ.occupation_id, -1) occupation_id,
		IFNULL(main_case_id, ccase.case_id) `main_case_id`, 
		IFNULL(main_case_uuid, ccase.case_uuid) `main_case_uuid`,
		IFNULL(main_case_number, ccase.case_number) `case_number`,
		FORMAT((DATEDIFF(inj.statute_limitation, inj.start_date) / 365), 0) statute_years
		FROM `cse_injury` inj 
		LEFT OUTER JOIN `cse_injury_occupation` iocc
		ON inj.injury_uuid = iocc.injury_uuid
		LEFT OUTER JOIN `ikase`.`cse_occupation` occ
		ON iocc.occupation_uuid = occ.occupation_uuid
		INNER JOIN cse_case_injury ccinj
		ON inj.injury_uuid = ccinj.injury_uuid AND ccinj.deleted = 'N'
		INNER JOIN cse_case ccase
		ON ccinj.case_uuid = ccase.case_uuid
		
		LEFT OUTER JOIN (
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
		) maininjury
		ON inj.injury_uuid = maininjury.injury_uuid
		
		WHERE 1";
$sql .= " AND `ccase`.`case_id` = :case_id";
$sql .= " AND inj.injury_id = :injury_id";

$sql .= " AND inj.customer_id = " . $_SESSION['user_customer_id'] . "
AND ccase.deleted = 'N'
AND inj.deleted = 'N'";
//die($sql);
try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("case_id", $case_id);
	$stmt->bindParam("injury_id", $injury_id);
	
	$stmt->execute();
	$injury = $stmt->fetchObject();
			
	$stmt->closeCursor(); $stmt = null; $db = null;

} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
}
//die(print_r($injury));   
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
	$db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
}

$body_options = "<option value=''>Select from List</option>";
$sql = "SELECT bodyparts_uuid, code, description 
FROM cse_bodyparts 
ORDER BY code";

try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("case_id", $case_id);
	$stmt->bindParam("injury_id", $injury_id);
	$stmt->execute();
	$bodys = $stmt->fetchAll(PDO::FETCH_OBJ);
	
	foreach ($bodys as $body) {
		$bodyparts_uuid = $body->bodyparts_uuid;
		$code = $body->code;
		$description = $body->description;
		$arrDescription = explode(" - ", $description);
		
		//special case
		if ($code=="700") {
			$arrDescription[0] = "Multiple parts more than five major parts";
		}
		$option = "<option value='" . $bodyparts_uuid . "'>" . $code . " - " . $arrDescription[0] . "</option>";
		$body_options .= "\r\n" . $option;
	}
	$db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}

for($x=1; $x < 11; $x++) {
	${"body" . $x} = "";
	foreach($bodyparts as $bodypart) {
		//echo $x . " -- " . $bodypart->bodyparts_number . " --> " . $bodypart->bodyparts_uuid . "<br />";
		if ($bodypart->bodyparts_number == $x) {
			${"body" . $x} = $bodypart->bodyparts_uuid;
		}
	}
}
$case_attorney_id = $kase->attorney_id;

$person_id = $kase->applicant_id;
$first = $kase->first_name;
$middle = $kase->middle_name;
$last = $kase->last_name;
$social_sec = $kase->ssn;
$birth_date = $kase->dob;
$adj_number = $kase->adj_number;

$arrCaseNumbers = array();
//defaults

$blnNew = true;
if ($kase->jetfile_info!="") {
	$jetfile_info = json_decode($kase->jetfile_info);
	$jetfile_id = $kase->jetfile_id;
	if (is_object($jetfile_info)) {
		//die(print_r($jetfile_info));
		if (is_object($jetfile_info->page2)) {
			$blnNew = false;
			$page2 = $jetfile_info->page2;
			$txtAppPayRate = $page2->pay_rate;
			$radPayRate = $page2->pay_day;
			$txtTips = $page2->advantages_pay;
			$radTipsTime = $page2->advantages;
			$txtAppHoursWorked = $page2->weekly_hours;
			$txtLastDayOff = $page2->last_day_date;
			$dtFinalStart = $page2->first_start_date;
			$dtFinalEnd = $page2->first_end_date;
			$dtSecondStart = $page2->second_start_date;
			$dtSecondEnd = $page2->second_end_date;
			$radCompPaid = $page2->compensation;
			$compTotal = $page2->total_paid;
			$compWeekly = $page2->weekly_rate;
			$compLastPmt = $page2->last_payment_date;
			$radUiPaid = $page2->unemployment_insurance;
			
			$radMedTreatment = $page2->medical_treatment;
			$radTreatmentFurnished = $page2->treatment_furnished;
			$lastDateTreatment = $page2->treatment_date;
			$treatmentBy = $page2->other_treatment;
			$radMediCal = $page2->medical_help;
			$doctor_1 = $page2->treating_doctor;
			$doctor_2 = $page2->treating_doctor2;
			
			$arrCaseNumbers[0] = $page2->case_number_1;
			$arrCaseNumbers[1] = $page2->case_number_2;
			$arrCaseNumbers[2] = $page2->case_number_3;
			$arrCaseNumbers[3] = $page2->case_number_4;
			
			$chkLiab1 = $page2->temporary_disability;
			$chkLiab2 = $page2->reimbursement;
			$chkLiab3 = $page2->medical_treatment_check;
			$chkLiab4 = $page2->compensation_rate;
			$chkLiab5 = $page2->permanent_disability;
			$chkLiab6 = $page2->rehabilitation;
			$chkLiab7 = $page2->back_to_work;
			$liabOther = $page2->other_method;
		}
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>EAMS Jet File - Step 2 out of 2</title>
<style type="text/css">
input {
	text-transform: uppercase;
}
.instructions {
	font-size:0.8em;
	font-style:italic;
}
td {
	padding:2px
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
    <input type="hidden" name="page" value="2" />
    <table width="980" border="0" align="center">
      <tr>
        <td colspan="10" align="center" class="pagetitle">
        	<div style="float:right; text-align:left">
                <em>
                <a href="app_1_2.php?case_id=<?php echo $case_id; ?>&injury_id=<?php echo $injury_id; ?>">Page 1</a>
    | <a href="upload_app.php?case_id=<?php echo $case_id; ?>&injury_id=<?php echo $injury_id; ?>&jetfile_id=<?php echo $jetfile_id; ?>">Uploads</a></em>        
    		</div>
        	Page 2
        </td>
      </tr>
      <tr>
        <td colspan="10" align="left">
        <?php if ($case_id!="") { ?>
        <strong>Case ID: <span style="padding-left:17px; padding:3px"><?php echo $case_id; ?></span><br />
        Applicant Name:<span style="padding-left:17px; padding:3px"><?php echo $first . "&nbsp;". $last; ?></span><br />
        DOI:<span style="padding-left:17px; padding:3px"><?php echo $client_case_injury_start . $client_case_injury_end; ?></span></strong>
        <?php } ?>        </td>
      </tr>
      <tr>
        <td colspan="10" align="center"><hr color="#000000" /></td>
      </tr>
      <tr>
        <td colspan="10" id="bodyparts_header">
            <div style="float:right; display:none" id="save_body_part_holder">
                <div style="display:inline-block" id="feedback_bodyparts"></div>
                <button id="save_bodyparts" class="one_line save_page_button">Save</button>
            </div>
            <strong>1. Body Parts:</strong><br />
            <i>(State which parts of the body were injured. You must select at least 1)</i>
        </td>
      </tr>
      <tr>
        <td colspan="10">
        <table width="100%" border="0" cellspacing="0" cellpadding="3">
          <tr>
            <td width="20%" align="left" nowrap>Body Part 1:</td>
            <td width="21%" align="left"><select name="body_part1" id="body_part1" class="bodypart required" onchange="checkBodyDoubles(this); enablePage3()" onblur="enablePage3()">
            	<?php echo str_replace("value='" . $body1 . "'", "value='" . $body1 . "' selected", $body_options); ?>
            </select>            </td>
            <td width="15%" align="left" nowrap>Body Part 6:</td>
            <td width="44%" align="left"><select name="body_part6" id="body_part6" class="bodypart" onchange="checkBodyDoubles(this); enablePage3()" onblur="enablePage3()">
              <?php echo str_replace("value='" . $body6 . "'", "value='" . $body6 . "' selected", $body_options); ?>
            </select></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td colspan="10"><table width="100%" border="0" cellspacing="0" cellpadding="3">
          <tr>
            <td width="20%" align="left" nowrap>Body Part 2:</td>
            <td width="21%" align="left"><select name="body_part2" id="body_part2" class="bodypart" onchange="checkBodyDoubles(this); enablePage3()" onblur="enablePage3()">
            <?php echo str_replace("value='" . $body2 . "'", "value='" . $body2 . "' selected", $body_options); ?>
              </select>            </td>
            <td width="15%" align="left" nowrap>Body Part 7:</td>
            <td width="44%" align="left"><select name="body_part7" id="body_part7" class="bodypart" onchange="checkBodyDoubles(this); enablePage3()" onblur="enablePage3()">
              <?php echo str_replace("value='" . $body7 . "'", "value='" . $body7 . "' selected", $body_options); ?>
            </select></td>
          </tr>
        </table>        </td>
      </tr>
      <tr>
        <td colspan="10"><table width="100%" border="0" cellspacing="0" cellpadding="3">
            <tr>
              <td width="20%" align="left" nowrap>Body Part 3:</td>
              <td width="21%" align="left"><select name="body_part3" id="body_part3" class="bodypart" onchange="checkBodyDoubles(this); enablePage3()" onblur="enablePage3()">
              <?php echo str_replace("value='" . $body3 . "'", "value='" . $body3 . "' selected", $body_options); ?>
                </select>              </td>
              <td width="15%" align="left" nowrap>Body Part 8:</td>
              <td width="44%" align="left"><select name="body_part8" id="body_part8" class="bodypart" onchange="checkBodyDoubles(this); enablePage3()" onblur="enablePage3()">
                <?php echo str_replace("value='" . $body8 . "'", "value='" . $body8 . "' selected", $body_options); ?>
              </select></td>
            </tr>
          </table></td>
      </tr>
      <tr>
        <td colspan="10"><table width="100%" border="0" cellspacing="0" cellpadding="3">
          <tr>
            <td width="20%" align="left" nowrap>Body Part 4:</td>
            <td width="21%" align="left"><select name="body_part4" id="body_part4" class="bodypart" onchange="checkBodyDoubles(this); enablePage3()" onblur="enablePage3()">
            <?php echo str_replace("value='" . $body4 . "'", "value='" . $body4 . "' selected", $body_options); ?>
              </select>            </td>
            <td width="15%" align="left" nowrap>Body Part 9:</td>
            <td width="44%" align="left"><select name="body_part9" id="body_part9" class="bodypart" onchange="checkBodyDoubles(this); enablePage3()" onblur="enablePage3()">
              <?php echo str_replace("value='" . $body9 . "'", "value='" . $body9 . "' selected", $body_options); ?>
            </select></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td colspan="10"><table width="100%" border="0" cellspacing="0" cellpadding="3">
          <tr>
            <td width="20%" align="left" nowrap>Body Part 5:</td>
            <td width="21%" align="left"><select name="body_part5" id="body_part5" class="bodypart" onchange="checkBodyDoubles(this); enablePage3()">
            <?php echo str_replace("value='" . $body5 . "'", "value='" . $body5 . "' selected", $body_options); ?>
              </select>            </td>
            <td width="15%" align="left" nowrap>Body Part 10:</td>
            <td width="44%" align="left"><select name="body_part10" id="body_part10" class="bodypart" onchange="checkBodyDoubles(this); enablePage3()">
              <?php echo str_replace("value='" . $body10 . "'", "value='" . $body10 . "' selected", $body_options); ?>
            </select></td>
          </tr>
          <tr>
            <td align="left" nowrap>&nbsp;</td>
            <td align="left">&nbsp;</td>
            <td colspan="2" align="left"><span class="instructions">These body parts will <strong>automatically </strong>be listed in the &quot;injury occurred as follows&quot; section</span></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td colspan="10"><hr color="#000000" />
        </td>
      </tr>
  </table>
  <table width="980" border="0" align="center">
      <tr>
        <td colspan="10">
        	<div style="float:right; display:none" id="save_explanation_holder">
                <div style="display:inline-block" id="feedback_explanation"></div>
                <button id="save_explanation" class="one_line save_page_button">Save</button>
            </div>
            <strong>2. The injury occurred as follows:<br />
	            (EXPLAIN WHAT THE WORKER WAS DOING AT THE TIME OF INJURY AND HOW THE INJURY OCCURED)<br />
            </strong>
        </td>
      </tr>
      <tr>
        <td colspan="10" align="left" nowrap="nowrap"><textarea name="explanation" id="explanation" cols="130" rows="4" onkeyup="limitText(this, 325);showSaveLink(event)" class="nospecial"><?php echo noSpecialAtAll($injury->explanation); ?></textarea>
        <div>
            <span id="injury_length"><br />
            0</span> characters (325 max)
        </div>
        </td>
      </tr>
      <tr>
        <td colspan="10"><hr color="#000000" /></td>
     </tr>
  </table>
  <table width="980" border="0" align="center">
      <tr>
        <td colspan="10">
            <div style="float:right; display:none" id="save_earnings_holder">
                <div style="display:inline-block" id="feedback_earnings"></div>
                <button id="save_earnings" class="one_line save_page_button">Save</button>
            </div>
            <strong>3. Actual earnings at the time of injury:          </strong>
          </td>
      </tr>
      <tr>
        <td width="12%" valign="top">Rate of pay </td>
        <td width="10%" valign="top" nowrap="nowrap">$
          <input name="pay_rate" type="text" id="pay_rate" onkeyup="noAlphaComma(this)" value="<?php echo $txtAppPayRate; ?>" size="3" class="earnings_input" /></td>
        <td colspan="2" valign="top"><table width="200" border="0" cellspacing="0" cellpadding="3">
          <tr>
            <td width="24%" align="left"><input type="radio" name="pay_day" class="earnings_input" id="radio_month" value="M" <?php if ($radPayRate=="M") { echo " checked"; } ?> /></td>
            <td width="76%" align="left">Monthly</td>
          </tr>
          <tr>
            <td align="left"><input type="radio" name="pay_day" class="earnings_input" id="radio_week" value="W" <?php if ($radPayRate=="W") { echo " checked"; } ?> /></td>
            <td align="left">Weekly</td>
          </tr>
          <tr>
            <td align="left"><input type="radio" name="pay_day" class="earnings_input" id="radio_hour" value="H" <?php if ($radPayRate=="H") { echo " checked"; } ?> /></td>
            <td align="left">Hourly</td>
          </tr>
          <tr>
            <td align="left"><input type="radio" name="pay_day" class="earnings_input" id="radio_none" value=""  <?php if ($radPayRate=="") { echo " checked"; } ?> /></td>
            <td align="left">None</td>
          </tr>
          
        </table></td>
        <td width="8%" valign="top">&nbsp;</td>
        <td colspan="3" valign="top">State value of tips, meals, lodging, <br />
          or other advantages, regularly received</td>
        <td width="8%" valign="top" nowrap="nowrap">$
        <input name="advantages_pay" type="text" id="advantages_pay" onkeyup="noAlpha(this)" value="<?php echo $txtTips; ?>" size="3" class="earnings_input" /></td>
        <td width="11%" valign="top"><table width="200" border="0" cellspacing="0" cellpadding="3">
          <tr>
            <td width="24%" align="left"><input type="radio" name="advantages" class="earnings_input" id="radio_month_advantages" value="M" <?php if ($radTipsTime=="M") { echo " checked"; }?> /></td>
            <td width="76%" align="left">Monthly</td>
          </tr>
          <tr>
            <td align="left"><input type="radio" name="advantages" class="earnings_input" id="radio_week_advantages" value="W" <?php if ($radTipsTime=="W") { echo " checked"; }?> /></td>
            <td align="left">Weekly</td>
          </tr>
          <tr>
            <td align="left"><input type="radio" name="advantages" class="earnings_input" id="radio_hour_advantages" value="H" <?php if ($radTipsTime=="H") { echo " checked"; }?> /></td>
            <td align="left">Hourly</td>
          </tr>
          <tr>
            <td align="left"><input type="radio" name="advantages" class="earnings_input" id="radio_no_advantages" value="" <?php if ($radPayRate=="") { echo " checked"; } ?> /></td>
            <td align="left">None</td>
          </tr>
          
        </table></td>
      </tr>
      
      <tr>
        <td colspan="10">Number of hours worked per week: 
        <input name="weekly_hours" type="text" id="weekly_hours" size="3" onkeyup="noAlpha(this)" value="<?php echo $txtAppHoursWorked; ?>" class="earnings_input" /></td>
      </tr>
      <tr>
        <td colspan="10"><hr color="#000000" /></td>
      </tr>
  </table>
  <table width="980" border="0" align="center">
      <tr>
        <td colspan="10">
        	<div style="float:right; display:none" id="save_disability_holder">
                <div style="display:inline-block" id="feedback_disability"></div>
                <button id="save_disability" class="one_line save_page_button">Save</button>
            </div>
        	<strong>4. The injury caused disability as follows:</strong>
          <div id="list_rep_searches" style="display:none; position:absolute; z-index:99; background:#FFFFFF; text-align:left"></div>
          <br />
          <br />
          Last day off work due to injury:
        <input type="text" name="last_day_date" id="last_day_date" value="<?php echo $txtLastDayOff; ?>" class="disability_input"  onkeyup="mask(this, mdate);" onblur="mask(this, mdate);" placeholder="dd/mm/yyyy" /></td>
      </tr>
      <tr>
        <td colspan="2">First Period of Disability:<br /></td>
        <td colspan="2" align="right">Start Date: </td>
        <td colspan="2" align="left"><input name="first_start_date" type="text" id="first_start_date" size="15" value="<?php echo $dtFinalStart; ?>" onkeyup="mask(this, mdate);" onblur="mask(this, mdate);" placeholder="dd/mm/yyyy" class="disability_input" /></td>
        <td width="11%" align="right" nowrap="nowrap">End Date:</td>
        <td width="12%"><input name="first_end_date" type="text" id="first_end_date" size="15" value="<?php echo $dtFinalEnd; ?>" onkeyup="mask(this, mdate);" onblur="mask(this, mdate);" placeholder="dd/mm/yyyy" class="disability_input" /></td>
        <td colspan="2">&nbsp;</td>
      </tr>
      <tr>
        <td colspan="2">Second Period of Disability:</td>
        <td colspan="2" align="right">Start Date: </td>
        <td colspan="2" align="left"><input name="second_start_date" type="text" id="second_start_date" size="15" value="<?php echo $dtSecondStart; ?>" onkeyup="mask(this, mdate);" onblur="mask(this, mdate);" placeholder="dd/mm/yyyy" class="disability_input" /></td>
        <td align="right" nowrap="nowrap">End Date:</td>
        <td><input name="second_end_date" type="text" id="second_end_date" size="15" value="<?php echo $dtSecondEnd; ?>" onkeyup="mask(this, mdate);" onblur="mask(this, mdate);" placeholder="dd/mm/yyyy" class="disability_input" /></td>
        <td colspan="2">&nbsp;</td>
      </tr>
      <tr>
        <td colspan="10"><hr color="#000000" /></td>
      </tr>
      <tr>
        <td colspan="10">
        	<div style="float:right; display:none" id="save_compensation_holder">
                <div style="display:inline-block" id="feedback_compensation"></div>
                <button id="save_compensation" class="one_line save_page_button">Save</button>
            </div>
            <strong>5. Compensation:</strong>
        </td>
      </tr>
      <tr>
        <td nowrap="nowrap">
        	Compensation was paid:
        </td>
        <td colspan="9"><input type="radio" class="compensation_input" name="compensation" id="compensation_yes" value="Yes" <?php if ($radCompPaid=="Yes") { echo " checked"; } ?> />
Yes
  <input type="radio" class="compensation_input" name="compensation" id="compensation_no" value="No" <?php if ($radCompPaid=="No") { echo " checked"; } ?> />
No</td>
      </tr>
      <tr>
        <td>Total paid:</td>
        <td colspan="9"><input name="total_paid" type="text" class="compensation_input" id="total_paid" size="10" onkeyup="noAlpha(this)" value="<?php echo $compTotal; ?>" /></td>
      </tr>
      <tr>
        <td>Weekly rate(s):        </td>
        <td colspan="9"><input name="weekly_rate" type="text" class="compensation_input" id="weekly_rate" size="10" onkeyup="noAlpha(this)" value="<?php echo $compWeekly; ?>" /></td>
      </tr>
      <tr>
        <td>Date of last payment:</td>
        <td colspan="9" nowrap="nowrap"><input name="last_payment_date" class="compensation_input" type="text" id="last_payment_date" size="10" value="<?php echo $compLastPmt; ?>" onkeyup="mask(this, mdate);" onblur="mask(this, mdate);" /></td>
      </tr>
      <tr>
        <td colspan="10"><hr color="#000000" /></td>
      </tr>
      <tr>
        <td colspan="10"><strong>6. Has the worker received any unemployment insurance benefits and/or any unemployment compensation<br />
        disability benefits (state disability) since the date of injury?</strong> 
          <input type="radio" name="unemployment_insurance" id="yes_unemployment" value="Yes" <?php if ($radUiPaid=="Yes") { echo " checked"; } ?> />
Yes
<input type="radio" name="unemployment_insurance" id="no_unemployment" value="No"  <?php if ($radUiPaid=="No" || $radUiPaid=="") { echo " checked"; } ?> />
No</td>
      </tr>
      <tr>
        <td colspan="10"><hr color="#000000" /></td>
      </tr>
  </table>
  <hr />
  <table width="980" border="0" align="center" cellpadding="3" cellspacing="0">
    <tr>
      <td colspan="9" align="center"><hr color="#000000" /></td>
    </tr>
    <tr>
      <td colspan="9">
      	<div style="float:right; display:none" id="save_medical_treatment_holder">
            <div style="display:inline-block" id="feedback_medical_treatment"></div>
            <button id="save_medical_treatment" class="one_line save_page_button">Save</button>
        </div>
        <strong>7. Medical treatment:<br />
        
        <br />
        </strong>Medical treatment was received:
        <input type="radio" name="medical_treatment" class="medical_treatment_input" id="yes_treatment" value="Yes" <?php if ($radMedTreatment=="Yes") { echo " checked"; } ?>  tabindex="1"/>
        Yes
        <input type="radio" name="medical_treatment" class="medical_treatment_input" id="no_treatment" value="No" <?php if ($radMedTreatment=="No") { echo " checked"; } ?>  tabindex="2" />
        No</td>
    </tr>
  </table>
  <table width="980" border="0" align="center" cellpadding="3" cellspacing="0" id="medical_treatment_section">
    <tr>
      <td colspan="9">All treatment was furnished by the Employer or Insurance Carrier:
        <input type="radio" class="medical_treatment_input" name="treatment_furnished" id="yes_furnished" value="Yes" <?php if ($radTreatmentFurnished=="Yes") { echo " checked"; } ?>  tabindex="3" />
        Yes
        <input type="radio" class="medical_treatment_input" name="treatment_furnished" id="no_furnished" value="No" <?php if ($radTreatmentFurnished=="No") { echo " checked"; } ?>  tabindex="4" />
        No</td>
    </tr>
    <tr>
      <td colspan="9">Date of last treatment:
        <input name="treatment_date" class="medical_treatment_input" type="text" id="treatment_date" value="<?php echo $lastDateTreatment; ?>" onkeyup="mask(this, mdate);" onblur="mask(this, mdate);" placeholder="dd/mm/yyyy"  tabindex="5" /></td>
    </tr>
    <tr>
      <td colspan="9">Other treatment was provided/paid by: </td>
    </tr>
    <tr>
      <td colspan="9"><input name="other_treatment" type="text" class="nospecial medical_treatment_input" id="other_treatment" value="<?php echo noSpecialAtAll($treatmentBy); ?>" size="50"  tabindex="6" />
        <br />
        (NAME OF PERSON OR AGENCY PROVIDING OR PAYING FOR MEDICAL CARE)</td>
    </tr>
    <tr>
      <td colspan="9"><strong>Did Medi-Cal pay for any health care related to this claim?
        <input type="radio" class="medical_treatment_input" name="medical_help" id="yes_medical" value="Yes" <?php if($radMediCal=="Yes") { echo " checked"; } ?>  tabindex="7" />
        Yes
        <input type="radio" class="medical_treatment_input" name="medical_help" id="no_medical" value="No" <?php if($radMediCal=="No") { echo " checked"; } ?>  tabindex="8" />
        No</strong></td>
    </tr>
    <tr>
      <td colspan="9"><strong>Names and addresses of doctor(s)/hospital(s)/clinic(s) that treated or examined for this injury, but that were not<br />
        provided or paid for by the employer or insurance carrier:</strong></td>
    </tr>
    <tr>
      <td colspan="9"><input name="treating_doctor" type="text" class="nospecial medical_treatment_input" id="treating_doctor" value="<?php echo noSpecialAtAll($doctor_1); ?>" size="50"  tabindex="9" />
        <br />
        Name of Doctor/Hospital/Clinic 1 (Please leave blank spaces between numbers, names or words)</td>
    </tr>
    <tr>
      <td colspan="9"><input name="treating_doctor2" type="text" class="nospecial medical_treatment_input" id="treating_doctor2" value="<?php echo noSpecialAtAll($doctor_2); ?>" size="50"  tabindex="10" />
        <br />
        Name of Doctor/Hospital/Clinic 2 (Please leave blank spaces between numbers, names or words)</td>
    </tr>
  </table>
  <table width="980" border="0" align="center" cellpadding="3" cellspacing="0">
    <tr>
      <td colspan="9"><hr color="#000000" /></td>
    </tr>
    <tr>
      <td colspan="9">
      	<div style="float:right; display:none" id="save_other_cases_holder">
            <div style="display:inline-block" id="feedback_other_cases"></div>
            <button id="save_other_cases" class="one_line save_page_button">Save</button>
        </div>
        <strong>8. Other cases have been filed for industrial injuries by this worker as follows:</strong>&nbsp;<span class="instructions">ADJ + numbers only</span></td>
    </tr>
    <tr>
      <td colspan="4" align="left" nowrap="nowrap">Case Number 1:
        <input type="text" class="nospecial other_cases_input" name="case_number_1" id="case_number_1" value="<?php echo $arrCaseNumbers[0]; ?>" tabindex="11" /></td>
      <td width="50%" colspan="5" align="left" nowrap="nowrap">Case Number 3:
        <input type="text" class="nospecial other_cases_input" name="case_number_3" id="case_number_3" value="<?php echo $arrCaseNumbers[2]; ?>"  tabindex="13" /></td>
    </tr>
    <tr>
      <td colspan="4" align="left" nowrap="nowrap">Case Number 2:
        <input type="text" class="nospecial other_cases_input" name="case_number_2" id="case_number_2" value="<?php echo $arrCaseNumbers[1]; ?>"  tabindex="12" /></td>
      <td colspan="5" align="left" nowrap="nowrap">Case Number 4:
        <input type="text" class="nospecial other_cases_input" name="case_number_4" id="case_number_4" value="<?php echo $arrCaseNumbers[3]; ?>"  tabindex="14" /></td>
    </tr>
    <tr>
      <td colspan="9"><hr color="#000000" /></td>
    </tr>
    <tr>
      <td colspan="9" bgcolor="#CCFFFF">
      	<div style="float:right; display:none" id="save_disagreement_holder">
            <div style="display:inline-block" id="feedback_disagreement"></div>
            <button id="save_disagreement" class="one_line save_page_button">Save</button>
        </div>
        <div style="font-weight:bold" id="disagreement_holder">9. This application is filed because of a disagreement regarding liability for: <span class="instructions" style="font-weight:normal">(you can select more than one choice)</span></div>
      </td>
    </tr>
    <tr>
      <td colspan="9" valign="middle"><table width="100%" border="0" cellspacing="0" cellpadding="3">
        <tr>
          <td width="3%" align="right"><input name="temporary_disability" type="checkbox" id="temporary_disability" value="Yes" class="disagreement" <?php if ($chkLiab1=="Yes" || $blnNew) { echo " checked"; } ?> /></td>
          <td width="41%" align="left">Temporary disability indemnity</td>
          <td width="5%" align="left">&nbsp;</td>
          <td width="3%" align="right"><input name="permanent_disability" type="checkbox" id="permanent_disability" value="Yes" class="disagreement" <?php if ($chkLiab5=="Yes" || $blnNew) { echo " checked"; } ?> /></td>
          <td colspan="2" align="left">Permanent disability indemnity</td>
        </tr>
        <tr>
          <td align="right"><input name="reimbursement" type="checkbox" id="reimbursement" value="Yes" class="disagreement" <?php if ($chkLiab2=="Yes" || $blnNew) { echo " checked"; } ?> /></td>
          <td align="left">Reimbursement for medical expense</td>
          <td align="left">&nbsp;</td>
          <td align="right"><input name="rehabilitation" type="checkbox" id="rehabilitation" value="Yes" class="disagreement" <?php if ($chkLiab6=="Yes" || $blnNew) { echo " checked"; } ?> /></td>
          <td colspan="2" align="left">Rehabilitation</td>
        </tr>
        <tr>
          <td align="right"><input name="medical_treatment_check" type="checkbox" id="medical_treatment_check" value="Yes" class="disagreement" <?php if ($chkLiab3=="Yes" || $blnNew) { echo " checked"; } ?> /></td>
          <td align="left">Medical treatment</td>
          <td align="left">&nbsp;</td>
          <td align="right"><input name="back_to_work" type="checkbox" id="back_to_work" value="Yes" class="disagreement" <?php if ($chkLiab7=="Yes" || $blnNew) { echo " checked"; } ?> /></td>
          <td colspan="2" align="left">Supplemental Job Displacement/Return to Work</td>
        </tr>
        <tr>
          <td align="right"><input name="compensation_rate" type="checkbox" id="compensation_rate" value="Yes" class="disagreement" <?php if ($chkLiab4=="Yes" || $blnNew) { echo " checked"; } ?>  /></td>
          <td align="left">Compensation at proper rate</td>
          <td align="left">&nbsp;</td>
          <td align="right"><input name="other" type="checkbox" id="other" value="Yes" class="disagreement" <?php 
		$blnRequireOther = false;
		$blnRequireAdhoc = false;
		if ($liabOther!="" || $blnNew) { 
			echo " checked"; 
			$blnRequireOther = true;
		}
		if ($liabOther!="") { 
			if ($liabOther!="PENALTIES" && $liabOther!="ALL BENEFITS PER LC") {
				$blnRequireOther = false;
				$blnRequireAdhoc = true;
			}
		}
		?> onclick="requireOther()" /></td>
          <td width="13%" align="left">Other (Specify)</td>
          <td width="35%" align="left"><select name="other_method" id="other_method" onchange="clearAdhoc();enableSave()" class="<?php if ($liabOther=="" || $blnNew || $blnRequireOther) { echo "required"; }?>">
            <option value="" <?php if ($liabOther=="" && !$blnNew) { echo " selected"; }?>>Select from List</option>
            <option value="ALL BENEFITS PER LC"<?php if ($liabOther=="ALL BENEFITS PER LC" || $blnNew) { echo " selected"; }?>>ALL BENEFITS PER LC</option>
            <option value="PENALTIES"<?php if ($liabOther=="PENALTIES") { echo " selected"; }?>>PENALTIES</option>
          </select></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td align="right">or type here:</td>
          <td align="left"><input name="other_adhoc" type="text" id="other_adhoc" size="20" maxlength="20" value="<?php 
			if ($liabOther!="") { 
				if ($liabOther!="PENALTIES" && $liabOther!="ALL BENEFITS PER LC") { 
					echo noSpecialAtAll($liabOther); 
				}
			}?>" onkeyup="adhocOther()" class="nospecial <?php if ($blnRequireAdhoc) { echo "required"; } ?>" />
            <span class="instructions">20 characters max</span></td>
        </tr>
      </table></td>
    </tr>
    <tr>
        <td colspan="9" align="left">
            <input type="button" class="submit" id="submit" value="Save" disabled="disabled" />
	       	<span class="required_guide" style="background:#CCFFFF">Please fill out all Required Fields</span>
            <span id="proceed_2" style="display:none">
                <a href="upload_app.php?case_id=<?php echo $case_id; ?>&injury_id=<?php echo $injury_id; ?>&jetfile_id=<?php echo $jetfile_id; ?>">Proceed to Uploads</a>
            </span>
        </td>
      </tr>
  </table>
</form>
<script language="javascript">
var blnBodyPartsSelected = <?php 
	if (count($bodyparts) > 0) {
		echo "true";
	} else {
		echo "false";
	}
?>;
function limitText(limitField, limitNum) {
	var explanation_value = limitField.value;
	var injury_length = document.getElementById("injury_length");
	injury_length.innerHTML = explanation_value.length
	
	if (limitField.value.length > limitNum) {
		limitField.value = limitField.value.substring(0, limitNum);
	}
}

var limitString = function() {
	var explanation = document.getElementById("explanation");
	var explanation_value = explanation.value;
	var injury_length = document.getElementById("injury_length");
	injury_length.innerHTML = explanation_value.length + "(" + caretPos + ")";
	if (explanation_value.length > 325) {
		explanation_value = explanation_value.substring(0, 325);
		explanation.value = explanation_value;
	}
}
var init = function() {
	$(".submit").on("click", savePage);
	
	var nospecials = $('.nospecial');
	nospecials.on("keyup", cleanMe);
	nospecials.on("blur", cleanMe);
	
	$(".bodypart").on("change", showSaveLink);
	$("#save_bodyparts").on("click", saveBodypart);
	
	$(".explanation").on("keyup", showSaveLink);
	$("#save_explanation").on("click", saveInjuryStory);
	
	$(".earnings_input:radio").on("click", showSaveEarningsLink);
	$(".earnings_input").on("keyup", showSaveEarningsLink);
	$("#save_earnings").on("click", savePage);
	
	$(".disability_input").on("keyup", showSaveDisabilityLink);
	$("#save_disability").on("click", savePage);
	
	//compensation
	$(".compensation_input:radio").on("click", showSaveCompensationLink);
	$(".compensation_input").on("keyup", showSaveCompensationLink);
	$("#save_compensation").on("click", savePage);
	
	$(".medical_treatment_input:radio").on("click", showSaveMedicalTreatmentLink);
	$(".medical_treatment_input").on("keyup", showSaveMedicalTreatmentLink);
	$("#save_medical_treatment").on("click", savePage);
	
	$(".other_cases_input:radio").on("click", showSaveOtherCasesLink);
	$(".other_cases_input").on("keyup", showSaveOtherCasesLink);
	$("#save_other_cases").on("click", savePage);
	
	$(".disagreement:checkbox").on("click", showSaveDisagreementLink);
	$(".disagreement").on("keyup", showSaveDisagreementLink);
	$("#save_disagreement").on("click", savePage);
	
	enablePage3();
}
var hideInfo = function(type) {
	document.getElementById("list_" + type + "_searches").style.display = "none";
}
var checkBodyDoubles = function(obj) {
	//you cannot you cannot pick the same body parts numbers
	var obj_value = obj.value;
	var obj_id = obj.id;
	var bodyparts = document.getElementsByClassName('bodypart');
	blnBodyPartsSelected = false;
	for(element in bodyparts) {
		if (!blnBodyPartsSelected) {
			//if any is selected, then we go green
			blnBodyPartsSelected = (bodyparts[element].value!="");
		}
		//check value, match
		if (bodyparts[element].id!= obj_id) {
			if (bodyparts[element].value != "") {
				if (bodyparts[element].value == obj_value) {
					alert("The Body Part Code [" +  obj_value + "] has already been selected.");
					obj.value = "";
					return;
				}
			}
		}
	}
}
var enablePage3 = function() {
	var submit_button = document.getElementById("submit");
	var bodyparts = document.getElementsByClassName('bodypart');
	var blnProceed = true;
	
	for(element in bodyparts) {
		//alert(bodyparts[element].name);
		if (bodyparts[element].value != undefined) {
			if (bodyparts[element].value != "") {
				document.getElementById("bodyparts_header").style.background = "#00FF00";
				submit_button.disabled = false;
				$(".required_guide").css("display", "none");
				
				enableSave();
				
				blnProceed = false;
				break;
			}
		}
	}
	
	var disagreements = document.getElementsByClassName('disagreement');
	
	document.getElementById("disagreement_holder").style.background = "pink";
	
	//other
	if (!$("#other").prop("checked")) {
		document.getElementById("other_method").style.background = "none";
		document.getElementById("other_method").style.border = "none";
		$("#other_method").removeClass("required");
	}
	for(element in disagreements) {
		//at least one
		if (disagreements[element].checked) {
			//alert(disagreements[element].id);
			submit_button.disabled = false;
			document.getElementById("disagreement_holder").style.background = "#00FF00";
			
			enableSave();
			
			blnProceed = false;
			break;
		}
	}
	
	if (!blnProceed) {
		return;
	}
	$(".required_guide").css("display", "");
	document.getElementById("bodyparts_header").style.background = "#CCFFFF";
	submit_button.disabled = true;
		
	return;
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
</script>
</body>
</html>
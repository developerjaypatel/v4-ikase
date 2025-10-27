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
	
	header("location:https://v4.ikase.org" . $_SERVER['REQUEST_URI']);
}
//die(print_r($_SESSION));
if ($_SESSION['user_customer_id']=="" || $_SESSION['user_customer_id']=="-1" || !isset($_SESSION['user_customer_id'])) {
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
	die("<script language='javascript'>window.close()</script>");
}

include("jetfile_kase.php");

$person_id = $kase->applicant_id;
$first = $kase->first_name;
$jetfile_case_id = $kase->jetfile_case_id;
$middle = $kase->middle_name;
$last = $kase->last_name;
$social_sec = $kase->ssn;
$birth_date = $kase->dob;
if ($birth_date!="") {
	$birth_date = date("m/d/Y", strtotime($birth_date));
}
$thedob = $birth_date;
$adj_number = $kase->adj_number;
$case_injury_start = $kase->start_date;
$case_injury_end = $kase->end_date;
if ($case_injury_start!="0000-00-00") {
	$case_injury_start = date("m/d/Y", strtotime($case_injury_start));
	if ($case_injury_end!="0000-00-00") {
		$case_injury_start .= "-" . date("m/d/Y", strtotime($case_injury_end)) . " CT";
	}
} else {
	$case_injury_start = "";
}
$jetfile_id = $kase->jetfile_id;
$xid = "";
/*
//get the last lien id for this case
$query = "SELECT lien_id FROM tbl_lien WHERE case_id =" . $case_id . " 
ORDER BY lien_id DESC 
LIMIT 0,1";
$result = DB::runOrDie($query);
$numbs = $result->rowCount();
$lien_id = "";
if ($numbs>0) {
	$lien_id = mysql_result($result, 0, "lien_id");
}

$queryfiling = "SELECT `filing_id`, `transaction_number`, `date`, tbl_filing.`dateandtime`, tbl_filing.`user_id`, tbl_user.user_name, tbl_filing.packet_id, tbl_filing.xid, tbl_filing.resubmission_id
FROM tbl_filing
LEFT OUTER JOIN tbl_user
ON tbl_filing.user_id = tbl_user.user_id
WHERE case_id = '" . $case_id  . "'
AND `form` = 'lien'
ORDER BY dateandtime DESC
LIMIT 0, 1";
$resultfiling = DB::runOrDie($queryfiling);
$numbsfiling = $resultfiling->rowCount();

if ($numbsfiling>0) {
	//get the payment confirmation if any
	$xid = mysql_result($resultfiling, 0, "xid");
}
*/
$role = "";
$request = "";
$issues = "";
$doctors = "";
$report_date = "";
$comments = "";

$compensation_rate = "";
$dor_rehab = "";
$temp_disability = "";
$self_pro_med_treatment = "";
$perm_disability = "";
$future_med_treatment = "";
$aoe_coe = "";
$dor_discovery = "";
$dor_employment = "";
$dor_other_box = "";
$dor_other = "";
$dor_statement = "";
$jetfile_dor_id = "";
if ($jetfile_id!="") {
	if ($kase->dor_info!="") {
		$jetfile_info = json_decode($kase->dor_info);
		if (is_object($jetfile_info)) {
			//die(print_r($jetfile_info));
			if (is_object($jetfile_info->pagedor)) {
				$pagedor = $jetfile_info->pagedor;
				//die(print_r($pagedor));
				$role = $pagedor->role;
				//echo $dor_id . " -> role:" . $role . "<br />";
				$request = $pagedor->request;
				$exempt = $pagedor->exempt;
				$dor_statement = $pagedor->dor_statement;
				$dor_doctors = $pagedor->dor_doctors;
				$report_date = $pagedor->report_date;
				if ($report_date!="") {
					$report_date = date("m/d/Y", strtotime($report_date));
				}
				$comments = $pagedor->comments;
				$exempt_signature = $pagedor->exempt_signature;
				if ($xid=="") {
					$xid = $pagedor->xid;
				}
				$compensation_rate = (isset($pagedor->compensation_rate) ? $pagedor->compensation_rate : "");
				$dor_rehab = (isset($pagedor->dor_rehab) ? $pagedor->dor_rehab : "");
				$temp_disability = (isset($pagedor->temp_disability) ? $pagedor->temp_disability : "");
				$self_pro_med_treatment = (isset($pagedor->self_pro_med_treatment) ? $pagedor->self_pro_med_treatment : "");
				$perm_disability = (isset($pagedor->perm_disability) ? $pagedor->perm_disability : "");
				$future_med_treatment = (isset($pagedor->future_med_treatment) ? $pagedor->future_med_treatment : "");
				$aoe_coe = (isset($pagedor->aoe_coe) ? $pagedor->aoe_coe : "");
				$dor_discovery = (isset($pagedor->dor_discovery) ? $pagedor->dor_discovery : "");
				$dor_employment = (isset($pagedor->dor_employment) ? $pagedor->dor_employment : "");
				$dor_other_box = (isset($pagedor->dor_other_box) ? $pagedor->dor_other_box : "");
				$dor_other = (isset($pagedor->dor_other) ? $pagedor->dor_other : "");
			}
		}
	}
}
//print_r($arrIssues);

//can we file?
//get uploads
$sql = "SELECT `document_id` id, `description` `name`, `document_filename` `filepath`
FROM cse_document doc
INNER JOIN cse_case_document ccd
ON doc.document_uuid = ccd.document_uuid
INNER JOIN cse_case ccase
ON ccd.case_uuid = ccase.case_uuid
WHERE `type` = 'DOR' 
AND `document_filename` != ''
AND case_id = :case_id
AND `doc`.customer_id = :cus_id
AND `doc`.deleted = 'N'";

try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("case_id", $case_id);
	$stmt->bindParam("cus_id", $cus_id);
	$stmt->execute();
	$documents = $stmt->fetchAll(PDO::FETCH_OBJ);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
$uploads = "";
$minimum_files = 3;
$number_files = count($documents);
if ($number_files>=$minimum_files) {
	$uploads = "1";
}
$adj_number = $kase->adj_number;
if (strpos($adj_number, "ADJ") === false) {
	$adj_number = "";
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>DOR</title>

<style>
input {
	text-transform: uppercase;
}
textarea {
	text-transform: uppercase;
}
</style>


<script type="text/javascript" src="../lib/jquery.1.10.2.js"></script>
<script type='text/javascript' src='../lib/mask.js'></script>
<script type='text/javascript' src='../lib/mask_date.js'></script>
<script type='text/javascript' src='jetfile.js'></script>
<script type='text/javascript' src='../js/utilities.js'></script>
</head>
<body onload="init()">
<form action="" method="post" enctype="multipart/form-data" name="form1" id="form1">
    <input type="hidden" name="case_id" id="case_id" value="<?php echo $case_id; ?>" />
    <input type="hidden" name="injury_id" id="injury_id" value="<?php echo $injury_id; ?>" />
    <input type="hidden" name="jetfile_id" id="jetfile_id" value="<?php echo $jetfile_id; ?>" />
    <input type="hidden" name="jetfile_case_id" id="jetfile_case_id" value="<?php echo $jetfile_case_id; ?>" />
    <input type="hidden" name="dob" id="dob" value="<?php echo $thedob; ?>" />
    <input type="hidden" name="ssn" id="ssn" value="<?php echo str_replace("-", "", $social_sec); ?>" />
    <input type="hidden" name="case_injury_start" id="case_injury_start" value="<?php echo $kase->start_date; ?>" />
    <input type="hidden" name="case_injury_end" id="case_injury_end" value="<?php echo $kase->end_date; ?>" />
    <input type="hidden" name="page" value="dor" />
    <input type="hidden" id="uploads" value="<?php echo $uploads; ?>" />
    <table width="980" border="0" align="center" cellpadding="3" cellspacing="0">
      <tr>
        <td colspan="2" align="center" class="pagetitle">Declaration of Readiness to Proceed</td>
      </tr>
      <tr>
        <td colspan="2" align="left">
        <div style="float:right; text-align:left">
       	  <em>
		  <?php if ($kase->dor_info!="") { ?>
          <a href="upload_dor.php?cus_id=<?php echo $cus_id; ?>&case_id=<?php echo $case_id; ?>&injury_id=<?php echo $injury_id; ?>&jetfile_id=<?php echo $jetfile_id; ?>">Uploads</a>
		  <?php } ?>
          </em>        
        </div>
        <div id="dor_feedback" style="float:right; padding-right:10px"></div>
        <?php if ($case_id!="") { ?>
        <strong>Case ID: <span style="padding-left:17px; padding:3px"><?php echo $case_id; ?></span><br />
        <?php if ($dor_id!="" && $_SERVER['REMOTE_ADDR']=='71.119.40.148') { ?>
        DOR ID: <span style="padding-left:17px; padding:3px"><?php echo $dor_id; ?></span><br />
        <?php } ?>
        Applicant Name:<span style="padding-left:17px; padding:3px"><?php echo $first . "&nbsp;". $last; ?></span><br />
        DOI:<span style="padding-left:17px; padding:3px"><?php echo $case_injury_start; ?></span></strong>
        <?php } ?>
        <br />
        <strong>ADJ Number:</strong> 
        <?php 
		$adj_number = str_replace("Pending", "", $adj_number);
		$style = "none";
		$type = "text";
		$otherstyle="";
		if ($adj_number!="") {
			$style = "";
			$otherstyle="none";
			$type = "hidden";
		}
		?>
        <input name="adj_number" type="<?php echo $type; ?>" class="required nospecial" id="adj_number" value="<?php echo noSpecialAtAll($adj_number); ?>" />
        <span class="instructions"style="display:<?php echo $otherstyle; ?>">ADJ + numbers only</span><span id="adj_number_show" style="display:<?php echo $style; ?>"><?php echo $adj_number; ?></span></td>
      </tr>
      <tr>
        <td colspan="2" align="center"><hr color="#000000" /></td>
      </tr>
      <tr class="noneed_preamble">
        <td colspan="2" bgcolor="#CCFFFF"><strong>Declarants: Please designate your role </strong></td>
      </tr>
      <tr class="noneed_preamble">
        <td colspan="2"><table width="100%" border="0" cellspacing="0" cellpadding="3">
          <tr>
            <td>Employee
            <input type="radio" name="role" id="dor_employee" value="E" class="dor_info" <?php if ($role=="E") { echo " checked"; } ?> /></td>
            <td>Applicant 
            <input type="radio" name="role" id="dor_applicant" value="A" class="dor_info" <?php if ($role=="A") { echo " checked"; } ?> /></td>
            <td>Defendant 
            <input type="radio" name="role" id="dor_defendant" value="D" class="dor_info" <?php if ($role=="D") { echo " checked"; } ?> /></td>
            <td>Lien Claimant 
            <input type="radio" name="role" id="dor_lien" value="L" class="dor_info" <?php if ($role=="L") { echo " checked"; } ?> /></td>
          </tr>
        </table></td>
      </tr>
      <tr class="noneed_preamble">
        <td colspan="2" bgcolor="#CCFFFF"><strong>Declarant requests: </strong></td>
      </tr>
      <tr class="noneed_preamble">
        <td colspan="2"><table width="100%" border="0" cellspacing="0" cellpadding="3">
          <tr>
            <td width="25%">Mandatory Settlement Conference
            <input type="radio" name="request" id="dor_man_settlement" value="M" class="declarant_info" <?php if ($request=="M") { echo " checked"; } ?> /></td>
            <td width="20%">Status Conference 
            <input type="radio" name="request" id="dor_status" value="S" class="declarant_info" <?php if ($request=="S") { echo " checked"; } ?> /></td>
            <td width="16%">Rating MSC* 
            <input type="radio" name="request" id="dor_rating" value="R" class="declarant_info" <?php if ($request=="R") { echo " checked"; } ?> /></td>
            <td width="22%">Priority Conference 
            <input type="radio" name="request" id="dor_priority" value="P" class="declarant_info" <?php if ($request=="P") { echo " checked"; } ?> /> </td>
            <td width="17%">Lien Conference
            <input type="radio" name="request" id="dor_conference" value="L" class="declarant_info" <?php if ($request=="L") { echo " checked"; } ?> /></td>
          </tr>
        </table></td>
      </tr>
      <tr style="display:<?php if ($dor_info=="") { ?>none<?php } ?>" id="require_payment_holder">
        <td colspan="2" align="center"><p><strong>Does this DOR require Payment?</strong><br />
          </p>
          <table width="450" border="0" cellspacing="0" cellpadding="2">
            <tr>
              <td width="50%" align="left"><input type="radio" name="exempt" id="payment_yes" value="N" <?php if ($exempt=="N" && $dor_info!="") { echo "checked"; } ?> />
              <label for="payment_yes"><strong>Yes</strong></label></td>
              <td width="50%" align="right"><input type="radio" name="exempt" id="payment_no" value="Y" <?php if ($exempt=="Y"  && $dor_info!="") { echo "checked"; } ?>  />
                <label for="payment_no"><strong>No</strong></label></td>
            </tr>
            <tr id="confirm_exempt_table" style="display:none">
              <td colspan="2" align="left">
              		<table width="450" border="0" cellpadding="2" cellspacing="0">
                      <tr class="need_signature">
                        <td colspan="2" align="left"><p>By signing below, I hereby state that I am exempt from paying DOR fees</p></td>
                      </tr>
                      <tr class="need_signature">
                        <td colspan="2" align="center"><input name="exempt_signature" type="text" id="exempt_signature" value="<?php echo $exempt_signature; ?>" size="65" /></td>
                      </tr>
                      <tr class="need_signature">
                        <td colspan="2" align="center">/S/ Signature</td>
                      </tr>
                      <tr class="need_signature">
                        <td colspan="2" align="center"><input type="button" name="proceed" id="proceed" value="Proceed" />
                        <span id="proceed_dor" style="display:<?php if ($kase->dor_info!="") { ?>none<?php } ?>">
            <a href="upload_dor.php?case_id=<?php echo $case_id; ?>&injury_id=<?php echo $injury_id; ?>&jetfile_id=<?php echo $jetfile_id; ?>">Proceed Uploads</a></span>
            </td>
                      </tr>
                    </table>
              </td>
            </tr>
            <tr class="need_paid">
              <td colspan="2" align="left">Have you already paid? (You must have Payment Confirmation Number)</td>
            </tr>
            <tr class="need_paid">
              <td align="left"><input type="radio" name="paid" id="paid_yes" value="Y" <?php if ($xid!="") { echo "checked"; } ?> />
              <label for="paid_yes"><strong>Yes</strong></label></td>
              <td align="right"><input type="radio" name="paid" id="paid_no" value="N" />
              <label for="paid_no"><strong>No</strong></label></td>
            </tr>
            <tr class="need_check">
              <td colspan="2" align="left"><p>Please enter your 13-digit Payment Confirmation Number below:</p></td>
            </tr>
            <tr class="need_check">
              <td colspan="2" align="center"><input name="xid" type="text" id="xid" value="<?php echo $xid; ?>" size="13" onkeyup="preambleProceed(event)" /><span id="xid_ok">&nbsp;</span></td>
            </tr>
            <tr style="display:none">
              <td colspan="2" align="center"><input type="button" name="proceed" id="proceed" value="Proceed" /></td>
            </tr>
        </table></td>
      </tr>
      <tr style="display:none" id="completed_payment_holder">
      	<td colspan="2" align="center" id="completed_payment_message">
        <span style="font-style:italic;color:white; background:green">PAYMENT COMPLETED, DOR CAN PROCEED</span>&nbsp;<span style="">&#10003;</span>
        </td>
      </tr>
      <tr class="need_preamble">
        <td colspan="2" bgcolor="#CCFFFF"><strong>At the present time the principal issues are: (Check all that apply)</strong></td>
      </tr>
      <tr class="need_preamble">
        <td colspan="2"><table width="100%" border="0" cellspacing="0" cellpadding="3">
          <tr>
            <td nowrap="nowrap">Compensation Rate 
            <input name="compensation_rate" type="checkbox" id="compensation_rate" value="Y"<?php if ($compensation_rate=="Y") { echo " checked"; } ?> class="issues" /></td>
            <td nowrap="nowrap">Rehabilitation/SJDB 
            <input name="dor_rehab" type="checkbox" id="dor_rehab" value="Y"<?php if ($dor_rehab=="Y") { echo " checked"; } ?> class="issues" /></td>
            <td nowrap="nowrap">Temporary Disability 
            <input name="temp_disability" type="checkbox" id="temp_disability" value="Y"<?php if ($temp_disability=="Y") { echo " checked"; } ?> class="issues" /></td>
            <td nowrap="nowrap">Self-Procured Medical Treatment 
            <input name="self_pro_med_treatment" type="checkbox" id="self_pro_med_treatment" value="Y"<?php if ($self_pro_med_treatment=="Y") { echo " checked"; } ?> class="issues" /></td>
          </tr>
          <tr>
            <td nowrap="nowrap">Permanent Disability 
            <input name="perm_disability" type="checkbox" id="perm_disability" value="Y"<?php if ($perm_disability=="Y") { echo " checked"; } ?> class="issues" /></td>
            <td nowrap="nowrap">Future Medical Treatment 
            <input name="future_med_treatment" type="checkbox" id="future_med_treatment" value="Y"<?php if ($future_med_treatment=="Y") { echo " checked"; } ?> class="issues" /></td>
            <td nowrap="nowrap">AOE/COE 
            <input name="aoe_coe" type="checkbox" id="aoe_coe" value="Y"<?php if ($aoe_coe=="Y") { echo " checked"; } ?> class="issues" /> </td>
            <td nowrap="nowrap">Discovery 
            <input name="dor_discovery" type="checkbox" id="dor_discovery" value="Y"<?php if ($dor_discovery=="Y") { echo " checked"; } ?> class="issues" /></td>
          </tr>
          <tr>
            <td>Employment 
            <input name="dor_employment" type="checkbox" id="dor_employment" value="Y"<?php if ($dor_employment=="Y") { echo " checked"; } ?> class="issues" /></td>
            <td colspan="3"><input name="dor_other_box" type="checkbox" id="dor_other_box" value="Y"<?php if ($dor_other_box=="Y") { echo " checked"; } ?> class="issues" onchange="requireOtherText()" /> 
              Other: 
                <input name="dor_other" type="text" id="dor_other" onkeyup="checkOtherBox();enableDOR()" value="<?php echo noSpecialAtAll($dor_other); ?>" size="25" maxlength="20" class="nospecial" /></td>
          </tr>
        </table></td>
      </tr>
      <tr class="need_preamble">
        <td colspan="2" nowrap="nowrap"><hr color="#000000" /></td>
      </tr>
      <tr class="need_preamble">
        <td colspan="2"><strong>Declarant relies on the report(s) of:</strong></td>
      </tr>
      <tr class="need_preamble">
        <td>Doctors (s): 
        <input name="dor_doctors" type="text" class="nospecial" id="dor_doctors" onblur="requireReportDate()" onkeyup="requireReportDate()" value="<?php echo noSpecialAtAll($dor_doctors); ?>" size="45" /></td>
        <td>Date: 
        <input name="report_date" type="text" id="report_date" value="<?php echo $report_date; ?>" size="10" autocomplete="off" onkeypress="mask(this, mdate);" onblur="mask(this, mdate);enableDOR()" />
        <br /></td>
      </tr>
      </table>
<table width="980" border="0" align="center" cellpadding="3" cellspacing="0" class="need_preamble">
<tr>
        <td colspan="6"><strong>Declarant states under penalty perjury that he or she is presently ready to proceed to hearing on the issues below and has made the following specific, genuine, good faith efforts to resolve the dispute(s) listed below:<br />
        </strong></td>
      </tr>
      <tr>
        <td colspan="6" align="center"><textarea name="dor_statement" id="dor_statement" cols="95" rows="7" onkeyup="enableDOR()" class="nospecial required"><?php echo noSpecialAtAll($dor_statement); ?></textarea>
        <br /></td>
      </tr>
      <tr>
        <td colspan="6"><strong>Unless a status or priority conference is requested, I have completed discovery on the issues listed above, and that all medical reports in my possession or control have been filed and served as required by the rules promulgated by the Court Administrator.</strong></td>
      </tr>
      <tr>
        <td colspan="6">
          <input type="button" class="submit" id="submit" value="Save" disabled="disabled" />
          <span id="required_guide" style="background:#CCFFFF">Please fill out all Required Fields</span>        </td>
      </tr>
      <tr>
        <td colspan="6"><img src="images/rating_msc.jpg" alt="Rating MSC" width="770" height="43" /></td>
      </tr>
  </table>
</form>
<script language="javascript">
var enableDOR = function(showclassname) {
	var blnDOREnabled = false;
	var blnDeclarantEnabled = false;
	var blnIssuesEnabled = false;
	var dor_fields = $('.dor_info');
	for(element in dor_fields) {
		if (dor_fields[element].checked) {
			blnDOREnabled = true;
			break;
		}
	}
	var declarant_fields = $('.declarant_info');
	for(element in declarant_fields) {
		if (declarant_fields[element].checked) {
			blnDeclarantEnabled = true;
			break;
		}
	}
	var issues_fields = $('.issues');
	for(element in issues_fields) {
		if (issues_fields[element].checked) {
			blnIssuesEnabled = true;
			break;
		}
	}
	if (blnDOREnabled && blnDeclarantEnabled && blnIssuesEnabled) {
		//is save still allowed
		enableSave();
	} else {
		var submit_button = document.getElementById("submit");
		submit_button.disabled = true;
	}
}
var init = function() {
	initMask();
	
	$(".dor_info").on("change", changeLien);
	$(".declarant_info").on("change", changeLien);
	
	$("#proceed").on("click", preambleProceed);
	

	var elements = $('.required');
	elements.on("blur", enableSave);
	elements.on("change", enableSave);
	elements.on("keyup", releaseMe);
	elements.on("blur", enableDOR);
	elements.on("keyup", enableDOR);
	
	$(".nospecials").on("keyup", cleanMe);
	
	$(".dor_info").on("change", enableDOR);
	$(".declarant_info").on("change", enableDOR);
	$(".issues").on("change", enableDOR);
	
	//preamble
	$("#payment_yes").on("change", preambleTerms);
	$("#payment_no").on("change", preambleTerms);
	$("#paid_yes").on("change", preambleTerms);
	$("#paid_no").on("change", preambleTerms);
	
	$("#proceed").on("click", preambleTerms);
	
	$(".submit").on("click", savePage);
	
	//run it to hide stuff
	preambleTerms();
}
var savePage = function(event) {
	event.preventDefault();
	
	var submit_button = $("#submit");
	submit_button.prop("disabled", true);
	submit_button.val("Saving");
	var formValues = $("#form1").serialize();
	//we need exempt value
	if (formValues.indexOf("exempt=") < 0) {
		formValues += "&exempt=";
	}
	
	var url = "../api/jetfile/save/dor";
	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
			success:function (data) {
				submit_button.val("Saved !!");
				submit_button.prop("disabled", false);
				
				$("#jetfile_id").val(data.id);
				
				document.getElementById("proceed_dor").style.display = "";
				
				setTimeout(function() {
					submit_button.val("Save");
				}, 2500);
			}
	});
}
var requireReportDate = function() {
	var dor_doctors = document.getElementById("dor_doctors");
	var report_date =  document.getElementById("report_date");
	$("#report_date").removeClass("required");
	if (dor_doctors.value!="") {
		$("#report_date").addClass("required");
	}
	enableDOR();
}
var checkOtherBox = function() {
	var dor_other_box = document.getElementById("dor_other_box");
	var dor_other =  document.getElementById("dor_other");
	if (dor_other.value != "") {
		dor_other_box.checked = true;
	} else {
		dor_other_box.checked = false;
	}
	requireOtherText();
}
var requireOtherText = function() {
	var dor_other_box = document.getElementById("dor_other_box");
	var dor_other =  document.getElementById("dor_other");
	Dom.removeClass(dor_other, "required");
	if (dor_other_box.checked) {
		Dom.addClass(dor_other, "required");
	}
	enableDOR();
}
function initMask(){
	//oDateMask0 = new Mask("mm/dd/yyyy", "date");
	//oDateMask0.attach(document.form1.report_date);
	
	enableDOR();
}
var preambleProceed = function(event) {
	var current_element = "";
	if (typeof event == "object") {
		current_element = event.target.id;
	}
	var payment_yes = document.getElementById("payment_yes");
	var payment_no = document.getElementById("payment_no");
	var paid_no = document.getElementById("paid_no");
	var paid_yes = document.getElementById("paid_yes");
	var xid = document.getElementById("xid");
	
	var need_preamble = $('.need_preamble');
	var xid_ok = document.getElementById("xid_ok");
	if (xid.value.length==13) {
		xid_ok.innerHTML = "<span class='checked'>&#10003</span>";
		setStyle(need_preamble, "display", "");
	} else {
		xid_ok.innerHTML = "";
		
		if (current_element=="xid") {
			//get out now, cannot proceed
			return true;
		}
	}
	
	
	var exempt_signature = document.getElementById("exempt_signature");
	var dor_lien = document.getElementById("dor_lien");
	var dor_conference = document.getElementById("dor_conference");
	if (dor_lien.checked && dor_conference.checked) {
		if (payment_yes.checked || (payment_no.checked && exempt_signature.value!="")) {
			setStyle(need_preamble, "display", "");
			setStyle($("#proceed"), "display", "none");
		} else {
			//should be defaulted here anyway
			setStyle(need_preamble, "display", "none");
			setStyle($("#proceed"), "display", "");
		}
	}
}
function setStyle (elements, prop, value) {
	elements.css(prop, value); 
}
var preambleTerms = function(event) {
	var current_element = "";
	if (typeof event == "object") {
		current_element = event.target.id;
	}
	var dor_lien = document.getElementById("dor_lien");
	var dor_conference = document.getElementById("dor_conference");
	if (!dor_lien.checked || !dor_conference.checked) {
		document.getElementById("require_payment_holder").style.display = "none";
		return;
	}
	document.getElementById("completed_payment_holder").style.display = "none";
	var payment_yes = document.getElementById("payment_yes");
	var payment_no = document.getElementById("payment_no");
	var paid_yes = document.getElementById("paid_yes");
	var paid_no = document.getElementById("paid_no");
	var need_check =$('.need_check');
	var need_paid = $('.need_paid');
	var need_preamble = $(".need_preamble");
	if (payment_yes.checked){
		setStyle(need_paid, "display", "");
		setStyle(need_preamble, "display", "none");
		$("#confirm_exempt_table").css("display", "none");
	}
	if (paid_yes.checked){
		setStyle(need_check, "display", "");
	}
	if (paid_no.checked || (!paid_yes.checked && !paid_no.checked)){
		setStyle(need_check, "display", "none");
		document.getElementById("completed_payment_holder").style.display = "none";
		turnoffDOR();
		return true;
	}
	if (payment_no.checked) {
		//no need for text and button
		setStyle(need_check, "display", "none");
		setStyle(need_paid, "display", "none");
		//setStyle(need_preamble, "display", "");
		$("#confirm_exempt_table").css("display", "");
	}
	if (!payment_no.checked && !payment_yes.checked) {
		setStyle(need_check, "display", "none");
		setStyle(need_paid, "display", "none");
	}
	var xid = document.getElementById("xid");	
	if (current_element=="payment_yes") {
		//get out now
		if (xid.value.length==13) {
			setStyle(need_preamble, "display", "");
		}
		return true;	
	}
	if (current_element=="paid_yes") {
		//do we have xid
		if (xid.value.length!=13) {
			//get out now
			return true;	
		}
	}
	
	preambleProceed();
}
function changeLien() {
	var need_preamble = $(".need_preamble");
	var dor_lien = document.getElementById("dor_lien");
	var dor_conference = document.getElementById("dor_conference");
	if (!dor_lien.checked || !dor_conference.checked) {
		document.getElementById("require_payment_holder").style.display = "none";
		document.getElementById("completed_payment_holder").style.display = "none";
		setStyle(need_preamble, "display", "");
		document.getElementById("payment_yes").checked = false;
		document.getElementById("payment_no").checked = false;
		document.getElementById("paid_yes").checked = false;
		document.getElementById("paid_no").checked = false;
		return;
	}
	
	var payment_yes = document.getElementById("payment_yes");
	var payment_no = document.getElementById("payment_no");
	var paid_no = document.getElementById("paid_no");
	var paid_yes = document.getElementById("paid_yes");
	var xid = document.getElementById("xid");
	
	display_status = "none";
	document.getElementById("completed_payment_holder").style.display = "none";
	if (dor_lien.checked && dor_conference.checked) {
		display_status = "";
		//payment is required
		document.getElementById("payment_yes").checked = true;
		document.getElementById("payment_no").checked = false;
		preambleTerms();
		if (payment_yes.checked && paid_yes.checked && xid.value !="") {
			//no need to show it, all filled out correctly
			display_status = "none";
			document.getElementById("completed_payment_message").innerHTML = ' <span style="font-style:italic;color:white; background:green">PAYMENT COMPLETED, DOR CAN PROCEED</span>&nbsp;<span style="">&#10003;</span>';
			document.getElementById("completed_payment_holder").style.display = "";
			setStyle(need_preamble, "display", "");
		} else {
			document.getElementById("completed_payment_holder").style.display = "none";
			setStyle(need_preamble, "display", "none");
		}
	}
	
	document.getElementById("require_payment_holder").style.display = display_status;
}
function turnoffDOR() {
	var need_preamble = $(".need_preamble");
	setStyle(need_preamble, "display", "none");
	
	document.getElementById("completed_payment_message").innerHTML = "<span style='color:white;background:red'>PAYMENT IS REQUIRED</span>&nbsp;|&nbsp;<a href='lien.php?case_id=<?php echo $case_id; ?>&injury_id=<?php echo $injury_id; ?>'>File a lien</a>";
	document.getElementById("completed_payment_holder").style.display = "";
}
var sendDOR = function() {
	var formValues = "case_id=<?php echo $case_id; ?>&injury_id=<?php echo $injury_id; ?>&jetfile_id=<?php echo $jetfile_id; ?>&jetfile_case_id=<?php echo $jetfile_case_id; ?>";
	var url = '../api/jetfile/senddor';
	$("#dor_feedback").html('Sending DOR to CAJetfile...');
	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else {
				//console.log(data);
				var jetfile_case_id = data.case_id;
				var jetfile_dor_id = data.dor_id;
				//update the cse_jetfile
				updateJetfile(jetfile_case_id, jetfile_dor_id);
			}
		}
	});
}
var updateJetfile = function(jetfile_case_id, jetfile_dor_id) {
	var formValues = "jetfile_id=<?php echo $jetfile_id; ?>&jetfile_case_id=" + jetfile_case_id + "&jetfile_dor_id=" + jetfile_dor_id;
	var url = '../api/jetfile/updatedor';
	$("#dor_feedback").html('Updating System with DOR Info...');
	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else {
				//file the app
				$("#dor_feedback").html("DOR Saved");
				fileDOR(jetfile_case_id, jetfile_dor_id);
			}
		}
	});
}
var fileDOR = function(jetfile_case_id, jetfile_dor_id) {
	console.log(jetfile_case_id, jetfile_dor_id)
}
var checkDOR = function() {
	var jetfile_id = $("#jetfile_id").val();
	var case_id = $("#case_id").val();
	var injury_id = $("#injury_id").val();
	var dob = $("#dob").val();
	var ssn = $("#ssn").val();
	var uploads = $("#uploads").val();
	
	var case_injury_start = $("#case_injury_start").val();
	var case_injury_end = $("#case_injury_end").val();
	
	
	if (jetfile_id=="" || uploads == "" || case_injury_start=="") {
		$("#dor_feedback").html('Not ready to file');
		return false;
	}
	var formValues = "case_id=" + case_id + "&injury_id=" + injury_id + "&dob=" + dob;
	formValues += "&ssn=" + ssn.replaceAll("-", "");	
	var url = '../api/jetfile/check/dor';
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
					//$("#dor_feedback").html('&#9992;');
					if (data.jetfile_dor_id!="" && data.jetfile_dor_id!="0" && data.dor_filing_id=="0") {
						$("#dor_feedback").html('<div style="float:right">Jetfile Case ID:' + data.case_id + ' -> READY TO FILE - CONTACT SUPPORT</div>');
					} else {
						$("#dor_feedback").html('<div style="float:right">DOR has been filed (' + data.dor_filing_id + ')</div>');
					}
				} else {
					$("#dor_feedback").html('<a href="javascript:sendDOR()">Send DOR to CAJetfile</a>');
				}
				$("#dor_feedback").css("display", "inline-block");
			}
		}
	});
}
</script>
</body>
</html>

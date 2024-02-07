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

$entitlement_labor = "";
$entitlement_temp_dis = "";
$established_mpn = "";
$entitlement_dispute = "";
$comments = "";
$doctors = "";

if ($jetfile_id!="") {
	if ($kase->dore_info!="") {
		$jetfile_info = json_decode($kase->dore_info);
		if (is_object($jetfile_info)) {
			//die(print_r($jetfile_info));
			if (is_object($jetfile_info->pagedore)) {
				$pagedore = $jetfile_info->pagedore;
				
				$entitlement_labor = $pagedore->entitlement_labor;
				$entitlement_temp_dis = $pagedore->entitlement_temp_dis;
				$established_mpn = $pagedore->established_mpn;
				$entitlement_dispute = $pagedore->entitlement_dispute;
				$comments = $pagedore->dor_e_statement;
				$doctors = $pagedore->dor_doctors;
			}
		}
	}
	
	//can we file?
	//get uploads
	$sql = "SELECT `document_id` id, `description` `name`, `document_filename` `filepath`
	FROM cse_document doc
	INNER JOIN cse_case_document ccd
	ON doc.document_uuid = ccd.document_uuid
	INNER JOIN cse_case ccase
	ON ccd.case_uuid = ccase.case_uuid
	WHERE `type` = 'DORE' 
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
	$minimum_files = 1;
	$number_files = count($documents);
	if ($number_files>=$minimum_files) {
		$uploads = "1";
	}
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>DOR E</title>

<script type="text/javascript" src="../lib/jquery.1.10.2.js"></script>
<script type='text/javascript' src='../lib/mask.js'></script>
<script type='text/javascript' src='../lib/mask_date.js'></script>
<script type='text/javascript' src='jetfile.js'></script>
<script type='text/javascript' src='../js/utilities.js'></script>
</head>
<style>
input {
	text-transform: uppercase;
}
textarea {
	text-transform: uppercase;
}
</style>
<body onload="init()">
<form action="dor_e_form_update_2015.php" method="post" enctype="multipart/form-data" name="dor_form" id="dor_form">
    <input type="hidden" name="case_id" id="case_id" value="<?php echo $case_id; ?>" />
    <input type="hidden" name="injury_id" id="injury_id" value="<?php echo $injury_id; ?>" />
    <input type="hidden" name="jetfile_id" id="jetfile_id" value="<?php echo $jetfile_id; ?>" />
    <input type="hidden" name="jetfile_case_id" id="jetfile_case_id" value="<?php echo $jetfile_case_id; ?>" />
    <input type="hidden" name="dob" id="dob" value="<?php echo $thedob; ?>" />
    <input type="hidden" name="ssn" id="ssn" value="<?php echo str_replace("-", "", $social_sec); ?>" />
    <input type="hidden" name="case_injury_start" id="case_injury_start" value="<?php echo $kase->start_date; ?>" />
    <input type="hidden" name="case_injury_end" id="case_injury_end" value="<?php echo $kase->end_date; ?>" />
    <input type="hidden" name="page" value="dore" />
    <input type="hidden" id="uploads" value="<?php echo $uploads; ?>" />
    <table width="980" border="0" align="center" cellpadding="3" cellspacing="0">
      <tr>
        <td align="center" class="pagetitle">Declaration of Readiness to Proceed</td>
      </tr>
      <tr>
        <td colspan="1" align="left">
        <div style="float:right; text-align:left">
       		<em>
			<?php if ($kase->dore_info!="") { ?><a href="upload_dore.php?case_id=<?php echo $case_id; ?>&injury_id=<?php echo $injury_id; ?>">Uploads</a><?php } ?>
            </em>
        </div>
        <?php if ($case_id!="") { ?>
        <div id="dore_feedback" style="float:right; padding-right:10px"></div>
        <strong>Case ID: <span style="padding-left:17px; padding:3px"><?php echo $case_id; ?></span><br />
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
        <span class="instructions" style="display:<?php echo $otherstyle; ?>">ADJ + numbers only</span><span id="adj_number_show" style="display:<?php echo $style; ?>"><?php echo $adj_number; ?></span></td>
      </tr>
      <tr>
        <td align="center"><hr color="#000000" /></td>
      </tr> 
      <tr>
        <td style="background:#CCFFFF" id="issues_holder"><strong>The Declarant requests that this case be set for expedited hearing and decision on the following issues:</strong></td>
      </tr>
      <tr>
      
        <td><input name="entitlement_labor" type="checkbox" id="entitlement_labor" value="Y"<?php if ($entitlement_labor=="Y") { echo " checked"; } ?> class="issues" />
        Entitlement to medical treatment per Labor Code &sect; 4600, except issues determined pursuant to Labor Code &sect;&sect; 4610 and 4610.5</td>
      </tr>
      <tr>
        <td><input name="entitlement_temp_dis" type="checkbox" id="entitlement_temp_dis" value="Y"<?php if ($entitlement_temp_dis=="Y") { echo " checked"; } ?> class="issues" />
        Entitlement to temporary disability, or disagreement on amount of temporary disability.</td>
      </tr>
      <tr>
        <td><input name="established_mpn" type="checkbox" id="established_mpn" value="Y"<?php if ($established_mpn=="Y") { echo " checked"; } ?> class="issues" />
        <span>Whether there is a properly established MPN in which the employee may obtain treatment (if requested, this will be the only issue heard at the hearing.)  See Labor Code &sect;&sect; 4603.2(a)(3); 5502(b)(B)</span></td>
      </tr>
      <tr>
        <td><input name="entitlement_dispute" type="checkbox" id="entitlement_dispute" value="Y"<?php if ($entitlement_dispute=="Y") { echo " checked"; } ?> class="issues" />
        Entitlement to compensation is in dispute because of a disagreement between employers and/or carriers.</td>
      </tr>
      <tr>
        <td><hr color="#000000" /></td>
      </tr>
      <tr>
        <td><strong>Declarant relies on the report(s) of:</strong></td>
      </tr>
      <tr>
        <td>Doctor(s):
          <input name="dor_doctors" type="text" class="nospecial" id="dor_doctors" value="<?php echo noSpecialAtAll($doctors); ?>" size="65" /></td>
      </tr>
      <tr>
        <td><hr color="#000000" /></td>
      </tr>
      <tr>
        <td><strong>Declarant states under penalty of perjury that he or she has made the following specific, genuine, good faith efforts to resolve the dispute(s) listed above:</strong></td>
      </tr>
      <tr>
        <td nowrap="nowrap"><textarea name="dor_e_statement" id="dor_e_statement" cols="95" rows="7" onkeyup="enableDOR(); limitText(this, 455)" class="nospecial"><?php echo noSpecialAtAll($comments); ?></textarea>
        <span id="statement_length"><?php echo strlen($comments); ?></span> characters (455 max)
        </td>
      </tr>
      </table>
<table width="980" border="0" align="center" cellpadding="3" cellspacing="0">
<tr>
        <td colspan="6">&nbsp;</td>
      </tr>
      
      <tr>
        <td colspan="6">
          <input type="button" class="submit" name="submit" id="submit" value="Submit" disabled="disabled" />
          <span id="required_guide" class="required_guide" style="background:#CCFFFF">Please fill out all Required Fields</span>
          <span id="proceed_dore" style="display:<?php if ($kase->dore_info=="") { ?>none<?php } ?>">
            <a href="upload_dore.php?case_id=<?php echo $case_id; ?>&injury_id=<?php echo $injury_id; ?>&jetfile_id=<?php echo $jetfile_id; ?>">Continue to Uploads</a>
        </span>
        </td>
      </tr>
  </table>
</form>
<script language="javascript">
var enableDOR = function(showclassname) {
	var blnIssuesEnabled = false;
	var issues_fields = document.getElementsByClassName('issues');
	for(element in issues_fields) {
		if (issues_fields[element].checked) {
			blnIssuesEnabled = true;
			break;
		}
	}
	if (blnIssuesEnabled) {
		$("#issues_holder").css("background", "green");
		//is save still allowed
		enableSave();
	} else {
		var submit_button = document.getElementById("submit");
		submit_button.disabled = true;
		$("#issues_holder").css("background", "#CCFFFF");
	}
}
var init = function() {
	initMask();
	var elements = $('.required');
	elements.on("blur", enableSave);
	elements.on("change", enableSave);
	elements.on("keyup", releaseMe);
	elements.on("blur", enableDOR);
	elements.on("change", enableDOR);
	
	var nospecials = $('.nospecial');
	nospecials.on("keyup", cleanMe);
	
	var issues_fields = $('.issues');
	issues_fields.on("click", enableDOR);
	
	enableDOR();
	
	$(".submit").on("click", savePage);
}
function limitText(limitField, limitNum) {
	var statement_story_value = limitField.value;
	var statement_length = document.getElementById("statement_length");
	statement_length.innerHTML = statement_story_value.length
	
	if (limitField.value.length > limitNum) {
		limitField.value = limitField.value.substring(0, limitNum);
	}
}
function initMask(){	
	return;
}
var savePage = function(event) {
	event.preventDefault();
	
	var submit_button = $("#submit");
	submit_button.prop("disabled", true);
	submit_button.val("Saving");
	var formValues = $("#dor_form").serialize();
	
	var url = "../api/jetfile/save/dore";
	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
			success:function (data) {
				submit_button.val("Saved !!");
				submit_button.prop("disabled", false);
				
				$("#jetfile_id").val(data.id);
				document.getElementById("proceed_dore").style.display = "";
				setTimeout(function() {
					submit_button.val("Save");
				}, 2500);
			}
	});
}
var checkDORE = function() {
	var jetfile_id = $("#jetfile_id").val();
	var case_id = $("#case_id").val();
	var injury_id = $("#injury_id").val();
	var dob = $("#dob").val();
	var ssn = $("#ssn").val();
	var uploads = $("#uploads").val();
	
	var case_injury_start = $("#case_injury_start").val();
	var case_injury_end = $("#case_injury_end").val();
	
	
	if (jetfile_id=="" || uploads == "" || case_injury_start=="") {
		$("#dore_feedback").html('Not ready to file');
		return false;
	}
	var formValues = "case_id=" + case_id + "&injury_id=" + injury_id + "&dob=" + dob;
	formValues += "&ssn=" + ssn.replaceAll("-", "");	
	var url = '../api/jetfile/check/dore';
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
					//$("#dore_feedback").html('&#9992;');
					if (data.jetfile_dore_id!="" && data.jetfile_dore_id!="0" && data.dore_filing_id=="0") {
						$("#dore_feedback").html('<div style="float:right">Jetfile Case ID:' + data.case_id + ' -> READY TO FILE - CONTACT SUPPORT</div>');
					} else {
						$("#dore_feedback").html('<div style="float:right">DORE has been filed (' + data.dore_filing_id + ')</div>');
					}
				} else {
					$("#dore_feedback").html('<a href="javascript:sendDORE()">Send DORE to CAJetfile</a>');
				}
				$("#dore_feedback").css("display", "inline-block");
			}
		}
	});
}
var sendDORE = function() {
	var formValues = "case_id=<?php echo $case_id; ?>&injury_id=<?php echo $injury_id; ?>&jetfile_id=<?php echo $jetfile_id; ?>";
	var url = '../api/jetfile/senddore';
	$("#dore_feedback").html("Sending to CAJetFile");
	console.log("Sending to CAJetFile");
	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else {
				$("#dore_feedback").html("Saved &#10003;");
				console.log("Saved");
				//console.log(data);
				jetfile_case_id = data.case_id;
				jetfile_dore_id = data.dore_id;
				//update the cse_jetfile
				updateJetfile(jetfile_case_id, jetfile_dore_id);
			}
		}
	});
}
var updateJetfile = function(jetfile_case_id, jetfile_dore_id) {
	var formValues = "jetfile_id=<?php echo $jetfile_id; ?>&jetfile_case_id=" + jetfile_case_id + "&jetfile_dore_id=" + jetfile_dore_id;
	var url = '../api/jetfile/updatedore';
	$("#dore_feedback").html('Updating System with DORE Info...');
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
				$("#dore_feedback").html("DORE Saved");
				fileDORE(jetfile_case_id, jetfile_dore_id);
			}
		}
	});
}
var fileDORE = function(jetfile_case_id, jetfile_dore_id) {
	console.log(jetfile_case_id, jetfile_dore_id)
}
</script>
</body>
</html>

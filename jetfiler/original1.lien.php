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
	die("<script language='javascript'>window.close()</script>");
}
if ($injury_id == "" || $injury_id < 0 || !is_numeric($injury_id)) {
	die("<script language='javascript'>parent.location.href='app_1_2.php?case_id=" . $case_id . "&injury_id=" . $injury_id . "'</script>");
}

include("jetfile_kase.php");

//die(print_r($kase));
$person_id = $kase->applicant_id;
$first = $kase->first_name;
$jetfile_case_id = $kase->jetfile_case_id;
$middle = $kase->middle_name;
$last = $kase->last_name;
$social_sec = $kase->ssn;
$birth_date = $kase->dob;
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
$order_id = "";
/*
$queryorder = "SELECT order_id FROM tbl_order WHERE case_id = '" . $case_id . "'";
$resultorder = mysql_query($queryorder, $r_link) or die("unable to get uploads<br />" . $queryorder . "<br />" . mysql_error()); 
$numberorder = mysql_numrows($resultorder);
if ($numberorder>0) {
	$order_id = mysql_result($resultorder, 0, "order_id");
}
*/

$adj_number = $kase->adj_number;

//die(print_r($customer));
$lien_organization = $customer->cus_name;
$lien_first_name = $customer->cus_name_first;
$lien_last_name = $customer->cus_name_last;
$lien_street = $customer->cus_street;
$lien_city = $customer->cus_city;
$lien_state = $customer->cus_state;
$lien_zip = $customer->cus_zip;
$lien_phone = $customer->cus_phone;

$cus_type = $customer->cus_type;

$attorney_eams_no = $customer->eams_no;
$attorney = $customer->cus_name;
//$lien_attorney = $lien_first_name . " " . $lien_last_name;
$lien_attorney = $attorney;
$lien_attorney_eams_no = $attorney_eams_no;
$claimant_representative = "A";
$lien_signature = $lien_attorney;
$attorney_street = $customer->cus_street;
$attorney_city = $customer->cus_city;
$attorney_state = $customer->cus_state;
$attorney_zip = $customer->cus_zip;
$attorney_phone = $customer->cus_phone;

$lien_attorney_street = $attorney_street;
$lien_attorney_city = $attorney_city;
$lien_attorney_state = $attorney_state;
$lien_attorney_zip = $attorney_zip;
$lien_attorney_first_name = $lien_first_name;
$lien_attorney_last_name = $lien_last_name;

$lien_type= "";
$exempt = "";
$original_lien_date = date("m/d/Y");

$lien_sum = "";
$lien_reason = "";
$lien_reason_other_text = "";
$lien_copy = "";

if ($jetfile_id!="") {
	$query = "SELECT lien_info, jetfile_lien_id
	FROM cse_jetfile
	WHERE jetfile_id = " . $jetfile_id;
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($query);
		
		$stmt->execute();
		$lien = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
			echo json_encode($error);
	}
	if ($lien->lien_info!="") {
		$jetfile_info = json_decode($lien->lien_info);
		if (is_object($jetfile_info)) {
			//die(print_r($jetfile_info));
			if (is_object($jetfile_info->pagelien)) {
				$pagelien = $jetfile_info->pagelien;
				//die(print_r($pagelien));
				$lien_id = $pagelien->lien_id;
				$lien_type = $pagelien->lien_type;
				$exempt = $pagelien->exempt;
				$exempt_signature = $pagelien->exempt_signature;
				$original_lien_date = $pagelien->original_lien_date;
				$original_lien_date = date("m/d/Y", strtotime($original_lien_date));
				$lien_organization = $pagelien->lien_organization;
				$lien_first_name = $pagelien->lien_first_name;
				$lien_last_name = $pagelien->lien_last_name;
				$lien_street = $pagelien->lien_street;
				$lien_city = $pagelien->lien_city;
				$lien_state = $pagelien->lien_state;
				$lien_zip = $pagelien->lien_zip;
				$lien_phone = $pagelien->lien_phone;
				
				$attorney_eams_no = $pagelien->attorney_eams_no;
				$attorney = $pagelien->attorney_name;
				$attorney_street = $pagelien->attorney_street;
				//echo $attorney_street . "[<br />";
				$attorney_city = $pagelien->attorney_city;
				$attorney_state = $pagelien->attorney_state;
				$attorney_zip = $pagelien->attorney_zip;
				
				$claimant_representative = $pagelien->claimant_representative;
				$lien_attorney_eams_no = $pagelien->lien_attorney_eams_no;
				$lien_attorney = $pagelien->lien_attorney_name;
				$lien_attorney_first_name = $pagelien->lien_attorney_first_name;
				$lien_attorney_last_name = $pagelien->lien_attorney_last_name;
				$lien_attorney_street = $pagelien->lien_attorney_street;
				$lien_attorney_city = $pagelien->lien_attorney_city;
				$lien_attorney_state = $pagelien->lien_attorney_state;
				$lien_attorney_zip = $pagelien->lien_attorney_zip;
				
				$lien_sum = $pagelien->lien_sum;
				$lien_reason = $pagelien->lien_reason;
				$lien_reason_other_text = $pagelien->lien_reason_other_text;
				$interpreter_date = $pagelien->interpreter_date;
				$interpreter_date = date("m/d/Y", strtotime($interpreter_date));
				$lien_copy = $pagelien->lien_copy;
				$lien_signature = $pagelien->lien_signature;			
			}
		}
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Jet File - Lien Form</title>
<style>
input {
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
<form action="lien_form_preamble_update.php" method="post" enctype="multipart/form-data" name="lien_form" id="lien_form">
    <input type="hidden" name="case_id" id="case_id" value="<?php echo $case_id; ?>" />
    <input type="hidden" name="injury_id" id="injury_id" value="<?php echo $injury_id; ?>" />
    <input type="hidden" name="jetfile_id" id="jetfile_id" value="<?php echo $jetfile_id; ?>" />
    <input type="hidden" name="jetfile_case_id" id="jetfile_case_id" value="<?php echo $jetfile_case_id; ?>" />
    <input type="hidden" name="page" value="lien" />
    <table width="980" border="0" align="center" cellpadding="3" cellspacing="0">
      <tr>
        <td colspan="2" align="center" class="pagetitle">Notice and Request for Allowance of Lien</td>
      </tr>
      <tr>
        <td colspan="2" align="left">
        <div style="float:right; text-align:left">
       	  <em>
		  <?php if ($lien->lien_info!="") { ?>
          <a href="upload_lien.php?cus_id=<?php echo $cus_id; ?>&case_id=<?php echo $case_id; ?>&injury_id=<?php echo $injury_id; ?>&jetfile_id=<?php echo $jetfile_id; ?>">Uploads</a>
		  <?php } ?>
          </em>        
        </div>
        <div id="jetfile_feedback" style="float:right; padding-right:10px"></div>
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
        <input name="adj_number" type="<?php echo $type; ?>" class="required nospecial" id="adj_number" value="<?php echo $adj_number; ?>" />
        <span class="instructions"style="display:<?php echo $otherstyle; ?>">ADJ + numbers only</span><span id="adj_number_show" style="display:<?php echo $style; ?>"><?php echo $adj_number; ?></span></td>
      </tr>
      <tr>
        <td colspan="2" align="center"><hr color="#000000" /></td>
      </tr>
      <tr>
        <td colspan="2" align="center"><strong>Are you exempt from paying Lien Fees?</strong><br />
          <table width="450" border="0" cellspacing="0" cellpadding="2">
            <tr>
              <td width="50%" align="left">
                <input type="radio" name="exempt" id="exempt_yes" value="Y" <?php if ($exempt=="Y") { echo "checked"; } ?> />
                <label for="exempt_yes"><strong>Yes</strong>, I am exempt</label>
              </td>
              <td width="50%" align="right">
                <input type="radio" name="exempt" id="exempt_no" value="N" <?php if ($exempt=="N") { echo "checked"; } ?> />
                <label for="exempt_no"><strong>No</strong>, I am not exempt</label>
              </td>
            </tr>
            <?php if ($xid!="") { ?>
            <tr>
              <td colspan="2" align="left"><hr /></td>
            </tr>
            <tr>
              <td colspan="2" align="left"><em>Lien Fees have been paid.</em></td>
            </tr>
            <tr>
              <td colspan="2" align="left">Payment Confirmation Number: <strong><?php echo $xid; ?></strong></td>
            </tr>
            <tr class="need_check">
              <td colspan="2" align="left"><hr /></td>
            </tr>
            <?php } ?>
            
            <tr class="need_check">
              <td colspan="2" align="left"><p>By signing below, I hereby state that I am exempt from paying lien fees</p></td>
            </tr>
            <tr class="need_check">
              <td colspan="2" align="center"><input name="exempt_signature" type="text" id="exempt_signature" value="<?php echo $exempt_signature; ?>" size="65" /></td>
            </tr>
            <tr class="need_check">
              <td colspan="2" align="center">/S/ Signature</td>
            </tr>
            <tr class="need_check">
              <td colspan="2" align="center"><input type="button" name="proceed" id="proceed" value="Proceed" /></td>
            </tr>
          </table></td>
      </tr>
      <tr class="need_preamble">
        <td colspan="2" align="center"><hr color="#000000" /></td>
      </tr>
      <tr class="need_preamble">
        <td colspan="2"><table width="94%" border="0" cellspacing="0" cellpadding="3">
          <tr>
            <td bgcolor="#CCFFFF">Original Lien
            <input type="radio" name="lien_type" id="original" value="0" <?php if ($lien_type=="0" || $lien_id=="") { echo " checked"; } ?> class="type" tabindex="2" /></td>
            <td bgcolor="#CCFFFF">Amended Lien 
            <input type="radio" name="lien_type" id="amended" value="1" <?php if ($lien_type=="1") { echo " checked"; } ?> class="type" tabindex="3" /></td>
          </tr>
        </table></td>
      </tr>
      <tr class="need_preamble">
        <td colspan="2">
        
        Date Of Original Lien:
        <input name="original_lien_date" type="text" id="original_lien_date" size="10" class="TCMask[##/##/####,mm/dd/yyyy] required" value="<?php echo $original_lien_date; ?>" tabindex="4" /></td>
      </tr>
      <tr class="need_preamble">
        <td colspan="2"><hr color="#000000" /></td>
      </tr>
      <tr class="need_preamble">
        <td colspan="2"><strong>Attorney/Representative for Injured Worker:</strong></td>
      </tr>
      <tr class="need_preamble">
        <td colspan="2">
        <input name="attorney_eams_number" id="attorney_eams_number" type="hidden" value="<?php echo $attorney_eams_no; ?>" class="insurance_info carrier nospecial" />
        <input name="attorney_name" type="text" id="attorney_name" size="45" value="<?php echo $attorney; ?>" onkeydown="typeSearch(event, 'attorney')"  onkeyup="eamsLookup('attorney')" tabindex="5" />
        <div id="list_attorney_searches" style="display:none; position:absolute; z-index:99; background:#FFFFFF; text-align:left"></div>
          <span class="instructions">enter terms in this box to look up EAMS info. Click on drop down to select.</span></td>
      </tr>
      <tr class="need_preamble">
        <td colspan="2">Name</td>
      </tr>
      <tr class="need_preamble">
        <td colspan="2"><input name="attorney_street" type="text" id="attorney_street" size="65" value="<?php echo $attorney_street; ?>" tabindex="6" class="nospecial" /></td>
      </tr>
      <tr class="need_preamble">
        <td colspan="2">Address/PO Box ( Please leave blank spaces between numbers , names or words)</td>
      </tr>
      <tr class="need_preamble">
        <td width="59%">ZIP Code:
          <input name="attorney_zip" type="text" class="nospecial" id="attorney_zip" onkeyup="sendZip(this, 'attorney_', '')" size="5" autocomplete="off" value="<?php echo $attorney_zip; ?>" tabindex="7" />
          <span class="instructions">enter zip to autofill city and state</span></td>
        <td width="41%"><div style="float:right">State:
          <input name="attorney_state" type="text" class="nospecial" id="attorney_state" size="2" value="<?php echo $attorney_state; ?>" tabindex="8" />
        </div>
        City:
        <input name="attorney_city" type="text" class="nospecial" id="attorney_city" size="20" value="<?php echo $attorney_city; ?>" tabindex="9" /></td>
      </tr>
      
      <tr class="need_preamble">
        <td colspan="2"><hr color="#000000" /></td>
      </tr>
      <tr class="need_preamble">
        <td colspan="2" nowrap="nowrap"><strong>Lien Claimant (Completion of this section is required):</strong></td>
      </tr>
      <tr class="need_preamble">
        <td colspan="2">
        <input name="claimants_eams_number" id="claimants_eams_number" type="hidden" value="<?php echo $attorney_eams_no; ?>" class="insurance_info carrier nospecial" />
        <input name="lien_organization" type="text" id="claimants_name" size="45" value="<?php echo $lien_organization; ?>" class="required nospecial" tabindex="10" onkeydown="typeSearch(event, 'claimants')"  onkeyup="eamsLookup('claimants')" onblur="enableLien()" />
        <div id="list_claimants_searches" style="display:none; position:absolute; z-index:99; background:#FFFFFF; text-align:left"></div>
          <span class="instructions">enter terms in this box to look up EAMS lien claimant info. Click on drop down to select.</span>
        <br />
        Name of Organization filing lien (for individual lien claimants, leave blank)</td>
      </tr>
      <tr class="need_preamble">
        <td colspan="2"><input name="lien_first_name" type="text" id="lien_first_name" size="45" value="<?php echo $lien_first_name; ?>" class="nospecial" tabindex="11" />
        <br />
        First Name of Individual filing lien(organizational lien claimants, leave blank)</td>
      </tr>
      </table>
<table width="980" border="0" align="center" cellpadding="3" cellspacing="0">
<tr class="need_preamble">
        <td colspan="4">
          <input name="lien_last_name" type="text" id="lien_last_name" size="45" value="<?php echo $lien_last_name; ?>" class="nospecial" tabindex="12" />
          <br />
          Last Name of Individual filing lien(organizational lien claimants, leave blank)        <br />        </td>
    </tr>
      <tr class="need_preamble">
        <td colspan="4" align="left"><input name="lien_street" type="text" id="lien_street" size="65" value="<?php echo $lien_street; ?>" class="required nospecial" tabindex="13" />
          <br />
        Address/PO Box ( Please leave blank spaces between numbers, names or words)<br /></td>
    </tr>
      
      <tr class="need_preamble">
        <td nowrap="nowrap">ZIP Code:
        <input name="lien_zip" type="text" id="lien_zip" onkeyup="sendZip(this, 'lien_', '')" size="5" value="<?php echo $lien_zip; ?>" autocomplete="off" class="required nospecial" tabindex="14" />
        <span class="instructions">enter zip to autofill city and state</span></td>
        <td width="35%" align="right">City:
        <input name="lien_city" type="text" class="nospecial" id="lien_city" size="20" value="<?php echo $lien_city; ?>" tabindex="15" /></td>
        <td width="10%" align="right">State:</td>
        <td width="24%"><input name="lien_state" type="text" class="nospecial" id="lien_state" value="<?php echo $lien_state; ?>" size="2" tabindex="16" /></td>
      </tr>
      <tr class="need_preamble" style="display:none">
        <td colspan="4" style="display:none">Phone: 
        <input name="lien_phone" type="text" id="lien_phone" size="25" value="<?php echo $lien_phone; ?>" class="TCMask[(###) ###-####]" tabindex="17" /></td>
      </tr>
      <tr class="need_preamble">
        <td colspan="4"><hr color="#000000" /></td>
      </tr>
      <tr class="need_preamble">
        <td colspan="4"><strong>Lien Claimant's Attorney/Representative, if any</strong></td>
    </tr>
      <tr class="need_preamble">
        <td colspan="4"><table width="100%" border="0" cellspacing="0" cellpadding="3">
          <tr>
            <td>Law Firm/Attorney 
            <input type="radio" name="claimant_representative" id="claimant_representative_attorney" value="A" <?php if ($claimant_representative=="A") { echo " checked"; } ?> class="claimant_rep" tabindex="18" /></td>
            <td>Non-Attorney Representative
            <input type="radio" name="claimant_representative" id="claimant_representative_nonattorney" value="R" <?php if ($claimant_representative=="R") { echo " checked"; } ?> class="claimant_rep" tabindex="19" /></td>
            <td>Lien Claimant not represented
            <input type="radio" name="claimant_representative" id="claimant_representative_not" value="N" <?php if ($claimant_representative=="N") { echo " checked"; } ?> class="claimant_rep" tabindex="20" /></td>
          </tr>
        </table></td>
      </tr>
      <tr class="need_preamble">
        <td colspan="4"><input name="lien_attorney_eams_number" id="lien_attorney_eams_number" type="hidden" value="<?php echo $lien_attorney_eams_no; ?>" class="insurance_info lien_stuff" />
          <input name="lien_attorney_name" type="text" id="lien_attorney_name" size="55" value="<?php echo $lien_attorney; ?>" class="nospecial <?php if ($claimant_representative=="A" || $claimant_representative=="R") { echo "required"; } ?> lien_stuff" onkeydown="typeSearch(event, 'lien_attorney')"  onkeyup="eamsLookup('lien_attorney')" onblur="enableLien()" tabindex="21" />
          <div id="list_lien_attorney_searches" style="display:none; position:absolute; z-index:99; background:#FFFFFF; text-align:left"></div>
          <span class="instructions">enter terms in this box to look up EAMS info. Click on drop down to select.</span>
          <br />
        Lien Claimant Law Firm/Representative</td>
      </tr>
      <tr class="need_preamble">
        <td colspan="4"><input name="lien_attorney_first_name" type="text" id="lien_attorney_first_name" size="45" value="<?php echo $lien_attorney_first_name; ?>" class="lien_stuff nospecial" onblur="enableLien()" tabindex="21" />
        <br />
        First Name</td>
      </tr>
      <tr class="need_preamble">
        <td colspan="4"><input name="lien_attorney_last_name" type="text" id="lien_attorney_last_name" size="45" value="<?php echo $lien_attorney_last_name; ?>" class="lien_stuff nospecial" onblur="enableLien()" tabindex="21" />
          <br />
Last Name</td>
      </tr>
      <tr class="need_preamble">
        <td colspan="4"><input name="lien_attorney_street" type="text" id="lien_attorney_street" size="65" value="<?php echo $lien_attorney_street; ?>" class="lien_stuff nospecial" onblur="enableLien()" tabindex="22" />
        <br />
        Address/PO Box ( Please leave blank spaces between numbers, names or words)</td>
      </tr>
      <tr class="need_preamble">
        <td nowrap="nowrap">ZIP Code:
          <input name="lien_attorney_zip" type="text" id="lien_attorney_zip" onkeyup="sendZip(this, 'lien_attorney_', '')" size="5" value="<?php echo $lien_attorney_zip; ?>" class="lien_stuff nospecial" onblur="enableLien()" autocomplete="off" tabindex="23" />
            <span class="instructions">enter zip to autofill city and state</span></td>
        <td align="right">City:
          <input name="lien_attorney_city" type="text" id="lien_attorney_city" size="20" value="<?php echo $lien_attorney_city; ?>" class="lien_stuff nospecial" onblur="enableLien()" tabindex="24" /></td>
        <td align="right">State:</td>
        <td><input name="lien_attorney_state" type="text" id="lien_attorney_state" value="<?php echo $lien_attorney_state; ?>" size="2" class="lien_stuff nospecial" onblur="enableLien()" tabindex="25" /></td>
      </tr>
      <tr class="need_preamble" style="display:none">
        <td nowrap="nowrap" style="display:none">Phone:
        <input name="lien_attorney_phone" type="text" id="lien_attorney_phone" size="25" value="<?php echo $lien_attorney_phone; ?>" class="TCMask[(###) ###-####]" tabindex="26" /></td>
        <td align="right" style="display:none">&nbsp;</td>
        <td align="right" style="display:none">&nbsp;</td>
        <td style="display:none">&nbsp;</td>
      </tr>  
      <tr class="need_preamble">
        <td colspan="4"><hr color="#000000" /></td>
      </tr>
      
      <tr class="need_preamble">
        <td colspan="4">The lien claimant hereby requests the Workers' Compensation Appeals Board to determine and allow as a lien the sum of $
          <input name="lien_sum" type="text" id="lien_sum" size="10" class="required" value="<?php echo $lien_sum; ?>" tabindex="27" onkeyup="noAlpha(this)" />
         against any amount now due or which may hereafter become payable as compensation to the above-named employee on account of the above-claimed injury.<br /></td>
      </tr>
      <tr class="need_preamble">
        <td colspan="4" bgcolor="#CCFFFF"><strong>This request and claim for lien is for (mark appropriate box):</strong></td>
      </tr>
      <tr class="need_preamble">
        <td colspan="4" align="left"><table width="100%" border="0" align="center" cellpadding="3" cellspacing="0">
          <tr>
            <td width="1%"><input type="radio" name="lien_reason" id="reasonable" value="0"<?php if ($lien_reason=="0") { echo " checked"; } ?> class="reasons" tabindex="28" /></td>
            <td colspan="4">A reasonable attorney's fee for legal services pertaining to any claim for compensation either before the appeals board or before any of the appellate courts, and the reasonable disbursements in connection therewith. (Labor Code § 4903 (a).)</td>
          </tr>
          <tr>
            <td width="1%"><input type="radio" name="lien_reason" id="labor" value="1"<?php if ($lien_reason=="1") { echo " checked"; } ?> class="reasons" tabindex="29" /></td>
            <td colspan="4"> The reasonable expense incurred by or on behalf of the injured employee, as provided by Labor Code § 4600. (Labor Code § 4903 (b).)</td>
          </tr>
          <tr>
            <td width="1%"><input type="radio" name="lien_reason" id="medical" value="2"<?php if ($lien_reason=="2") { echo " checked"; } ?> class="reasons" tabindex="30" onchange="requireOther()" /></td>
            <td colspan="4">Claims of costs. (Labor Code § 4903.05) Specify nature  and statutory basis in the Other Lien Text.</td>
          </tr>
          <tr>
            <td width="1%"><input type="radio" name="lien_reason" id="lien_reason3" value="3"<?php if ($lien_reason=="3") { echo " checked"; } ?> class="reasons" tabindex="31" /></td>
            <td colspan="4"> The reasonable value of the living expenses of an injured employee or of his or her dependents, subsequent to the injury. (Labor Code § 4903 (c).)</td>
          </tr>
          <tr>
            <td width="1%"><input type="radio" name="lien_reason" id="lien_reason4" value="4"<?php if ($lien_reason=="4") { echo " checked"; } ?> class="reasons" tabindex="32" /></td>
            <td colspan="4"> The reasonable burial expenses of the deceased employee. (Labor Code § 4903 (d).)</td>
          </tr>
          <tr>
            <td width="1%"><input type="radio" name="lien_reason" id="lien_reason5" value="5"<?php if ($lien_reason=="5") { echo " checked"; } ?> class="reasons" tabindex="33" /></td>
            <td colspan="4"> The reasonable living expenses of the spouse or minor children of the injured employee, or both, subsequent to the date of the injury, where the employee has deserted or is neglecting his or her family. (Labor Code § 4903 (e).)</td>
          </tr>
          <tr>
            <td width="1%"><input type="radio" name="lien_reason" id="lien_reason6" value="6"<?php if ($lien_reason=="6") { echo " checked"; } ?> class="reasons" tabindex="34" /></td>
            <td colspan="4">The reasonable fee for interpreter's services performed on 
              <label>
              <input type="text" name="interpreter_date" id="interpreter_date" class="<?php if ($lien_reason=="6") { echo " required"; } ?>" onkeyup="enableLien()" value="<?php echo $interpreter_date; ?>" tabindex="35" />
              </label>
            (Labor Code § 4600 (f).) </td>
          </tr>
          <tr>
            <td width="1%"><input type="radio" name="lien_reason" id="lien_reason7" value="7"<?php if ($lien_reason=="7") { echo " checked"; } ?> class="reasons" tabindex="36" /></td>
            <td colspan="4">The amount of indemnification granted by the California Victims of Crime Program. (Labor Code § 4903 (i).) </td>
          </tr>
          <tr style="display:none">
            <td width="1%"><input type="radio" name="lien_reason" id="lien_reason8" value="8"<?php if ($lien_reason=="8") { echo " checked"; } ?> class="reasons" tabindex="37" /></td>
            <td colspan="4">The amount of compensation, including expenses of medical treatment, and recoverable costs that have been paid by the Asbestos Workers' Account. (Labor Code § 4903 (j).)</td>
          </tr>
          <tr>
            <td width="1%"><input type="radio" name="lien_reason" id="lien_reason9" value="9"<?php if ($lien_reason=="9") { echo " checked"; } ?> class="reasons" onchange="requireOther()" tabindex="38" /></td>
            <td colspan="4">Other Lien(s): Specify nature and statutory basis.</td>
          </tr>
          <tr>
            <td colspan="5" align="left"><textarea name="lien_reason_other_text" id="lien_reason_other_text" cols="90" rows="5" onkeyup="enableLien()" tabindex="39" class="nospecial"><?php echo $lien_reason_other_text; ?></textarea></td>
          </tr>
        </table></td>
      </tr>
      <tr class="need_preamble">
        <td colspan="4"><strong>NOTE: ITEMIZED STATEMENT JUSTIFYING THE LIEN MUST BE ATTACHED</strong></td>
      </tr>
      <tr class="need_preamble">
        <td colspan="4"><input type="checkbox" name="lien_copy" id="lien_copy" value="Y"<?php if ($lien_copy=="Y") { echo " checked"; } ?> tabindex="40" />
        A copy of the lien claim and supporting documents was served by mail or delivered to each of the above-named parties.</td>
      </tr>
      
      <tr class="need_preamble">
        <td colspan="4"><input name="lien_signature" type="text" id="lien_signature" size="45" value="<?php echo $lien_signature; ?>" class="required nospecial" onkeyup="enableLien()" tabindex="41" />
          <br />
        (Signature of Lien Claimant)</td>
      </tr>
      
      <tr class="need_preamble">
        <td colspan="4">
          <input type="button" class="submit" name="submit" id="submit" value="Submit" disabled="disabled" tabindex="42" />
          <span id="required_guide" style="background:#CCFFFF">Please fill out all Required Fields</span></td>
      </tr>
  </table>
</form>
<script language="javascript">
var myAttorneyDataSource;
var myAttorneyDataTable;
var myClaimantDataSource;
var myClaimantDataTable;
var myLienAttorneyDataSource;
var myLienAttorneyDataTable;
var typeSearch = function(e, type) {
	if(window.event) // IE
	{
		keynum = e.keyCode
	}
	else if(e.which) // Netscape/Firefox/Opera
	{
		keynum = e.which
	}
	
	if (keynum == 8) {
		var person_name = document.getElementById(type + "_name");
		var the_value = person_name.value;
		if (the_value == "") {
			$(".list_" + type + "_searches").hide();
			//alert("back hide");
		}
	}
	return;
}
var blnClicking = false;
var showFirm = function(eams_no, type) {
	blnClicking = true;
	//alert("showing");
	if (eams_no=="") {
		return;
	}
	if (type=="claimants") {
		mysentData = "type=" + type;
	} else {
		mysentData = "type=reps";
	}
	mysentData += "&query=" + eams_no;
	var eamsURL = "check_eams.php";
	//alert(eamsURL + '?' + mysentData);		
	
	if (mysentData!='') {	
		//alert("about to send request");
		var request = YAHOO.util.Connect.asyncRequest('POST', eamsURL,
		   {success: function(o){
				response = o.responseText;
				//alert(response);
				blnClicking = false;
				if (response != "") {
					var arrData = response.split("|");
					var eams_no = document.getElementById(type + "_eams_number");
					eams_no.value = arrData[0];
					var name = document.getElementById(type + "_name");
					name.value = arrData[1];
					current_type = type;
					if (type=="claimants") {
						//quick fix
						type = "lien";
						document.getElementById(type + "_first_name").value = "";
						document.getElementById(type + "_last_name").value = "";
					}
					var street = document.getElementById(type + "_street");
					street.value = arrData[2];
					if (arrData[3]!="") {
						street.value += " " + arrData[3];
					}
					var city = document.getElementById(type + "_city");
					city.value = arrData[4];
					var state = document.getElementById(type + "_state");
					state.value = arrData[5];
					var zip_code = document.getElementById(type + "_zip");
					zip_code.value = arrData[6];
					
					hideInfo(current_type);
				}
				//logEvent("saved");
			},
		   failure: function(){
			   //
			   alert("failure");
			},
		   after: function(){
			   //
			   //alert("after");
			},
		   scope: this}, mysentData);
		 //logEvent("appointment save done");
	}
}
var hideInfo = function(type) {
	$(".list_" + type + "_searches").hide();
}
var scheduleCheckUAN = function() {
	//need a little delay to allow me to click
	setTimeout("checkUAN()", 1000);
}
var checkUAN = function() {
	if (blnClicking) {
		return;
	}
	//alert("checking");
	var claimants_eams_number = document.getElementById("claimants_eams_number").value;
	//alert(claimants_eams_number);
	if (claimants_eams_number=="") {
		var claimants_name = $("#claimants_name");
		claimants_name.val("");
		claimants_name.addClass("required");
		hideInfo("claimants");
	}
}
var eamsLookup = function (type) {
	//alert("artLookupping");
	
	var search_item = document.getElementById(type + "_name");
	var blnRequired = true;
	
	if (document.getElementById("claimant_representative_nonattorney").checked) {
		return;
	}
	if (!blnRequired) {
		//no lookup
		return;
	}
	var the_value = search_item.value;
	//clear the eams number because we're typing
	document.getElementById(type + "_eams_number").value = "";
		
	if (the_value != "") {
		$(".list_" + type + "_searches").show();
	} else {
		//alert("hide me");
		$(".list_" + type + "_searches").hide();
		return;
	}
	if (the_value.length<3) {
		return;
	}

	this.sentData = "&query=" + the_value;		
	/*
	if (type=="attorney") {
		myAttorneyDataSource.sendRequest(this.sentData, myAttorneyDataTable.onDataReturnInitializeTable, myAttorneyDataTable);
		myAttorneyDataTable.onShow();
	}
	if (type=="lien_attorney") {
		myLienAttorneyDataSource.sendRequest(this.sentData, myLienAttorneyDataTable.onDataReturnInitializeTable, myLienAttorneyDataTable);
		myLienAttorneyDataTable.onShow();
	}
	if (type=="claimants") {
		myClaimantDataSource.sendRequest(this.sentData, myClaimantDataTable.onDataReturnInitializeTable, myClaimantDataTable);
		myClaimantDataTable.onShow();
	}
	*/
	//alert("refreshed");
}
var enableLien = function() {
	//alert("here");
	var claimant_reps = $('.claimant_rep');
	var blnClaimantEnable = false;
	for(element in claimant_reps) {
		if (claimant_reps[element].checked) {
			blnClaimantEnable = true;
			break;
		}
	}
	var reasons = $('.reasons');
	var blnReasonEnable = false;
	for(element in reasons) {
		if (reasons[element].checked) {
			blnReasonEnable = true;
			//alert(reasons[element].id);
			break;
		}
	}
	var types = $('.type');
	var blnTypeEnable = false;
	for(element in types) {
		if (types[element].checked) {
			blnTypeEnable = true;
			break;
		}
	}
	//special cases
	var interpreter_date = document.getElementById("interpreter_date");
	var lien_reason6 = document.getElementById("lien_reason6");
	if (!lien_reason6.checked) {
		interpreter_date.value = "";
	}
	
	var other = document.getElementById("lien_reason9");
	var medical = document.getElementById("medical");
	var lien_reason_other_text = document.getElementById("lien_reason_other_text");
	if (!other.checked && !medical.checked) {
		lien_reason_other_text.value = "";
	}
	//Lien claimant organization optional if first and last name filled out
	var lien_organization = document.getElementById("lien_organization");
	var lien_first_name = document.getElementById("lien_first_name");
	var lien_last_name = document.getElementById("lien_last_name");
	
	if (lien_first_name.value!="" && lien_last_name.value!="") {
		//organization not required
		$("#lien_organization").removeClass("required");
	}
	if (lien_first_name.value=="" || lien_last_name.value=="") {
		//organization not required
		$("#lien_organization").addClass("required");
	}
	if (blnClaimantEnable && blnReasonEnable && blnTypeEnable) {
		enableSave();
	}
}
var requireOther = function() {
	var other = document.getElementById("lien_reason9");
	var lien_reason_other_text = document.getElementById("lien_reason_other_text");
	var medical = document.getElementById("medical");
	$("#lien_reason_other_text").removeClass("required");
	
	if (other.checked || medical.checked) {
		$("#lien_reason_other_text").addClass("required");
	} 
	enableLien();
}
var makeClaimantRequired = function() {
	var claimant_representative_attorney = document.getElementById("claimant_representative_attorney");
	var claimant_representative_nonattorney = document.getElementById("claimant_representative_nonattorney");
	var claimant_representative_not = document.getElementById("claimant_representative_not");
	var lien_attorney_phone = document.getElementById("lien_attorney_phone");
	var lien_stuff = $('.lien_stuff');
	
	if (claimant_representative_attorney.checked || claimant_representative_nonattorney.checked) {
		lien_stuff.addClass("required");
		
		//special case
		if (claimant_representative_nonattorney.checked) {
			$("#lien_attorney_name").removeClass("required");
			$("#lien_attorney_eams_number").removeClass("required");
			//make sure you can save with only first and last name
		}
		if (claimant_representative_attorney.checked) {
			//first and last name are NOT required
			$("#lien_attorney_first_name").removeClass("required");
			$("#lien_attorney_last_name").removeClass("required");
		}
	} 
	//clear every thing out if it's thelast choice
	if (claimant_representative_not.checked) {
		lien_stuff.removeClass("required");
		//not the phone
		lien_attorney_phone.value = "(###) ###-####";
	}
	enableLien();
}
var makeReasonRequired = function() {
	var interpreter_date = document.getElementById("interpreter_date");
	var lien_reason6 = document.getElementById("lien_reason6");
	var lien_reason9 = document.getElementById("lien_reason9");
	var medical = document.getElementById("medical");
	var lien_reason_other_text = document.getElementById("lien_reason_other_text");
	if (lien_reason6.checked) {
		$("#interpreter_date").addClass("required");
	} else {
		$("#interpreter_date").removeClass("required");
	}
	if (lien_reason9.checked || medical.checked) {
		$("#lien_reason_other_text").addClass("required");
	} else {
		$("#lien_reason_other_text").removeClass("required");
	}
	enableLien();
}
var setLienDate = function() {
	var original = document.getElementById("original");
	var original_lien_date = document.getElementById("original_lien_date");
	if (original.checked) {
		original_lien_date.value = "<?php echo date("m/d/Y"); ?>";
	}
	enableLien();
}
var init = function() {	
	var elements = $('.required');
	elements.on("blur", enableLien);
	elements.on("change", enableLien);
	
	var nospecials = $('.nospecial');
	nospecials.on("keyup", cleanMe);
	
	var elements = $('.required');
	elements.on("blur", enableLien);
	elements.on("change", enableLien);
	elements.on("keyup", releaseMe);
	
	var claimant_reps = $('.claimant_rep');
	claimant_reps.on("click", enableLien);
	claimant_reps.on("change", enableLien);
	
	var reasons = $('.reasons');
	reasons.on("click", enableLien);
	reasons.on("change", enableLien);

	reasons.on("click", makeReasonRequired);
	reasons.on("change", makeReasonRequired);
	
	var claimant_reps = $('.claimant_rep');
	claimant_reps.on("click", makeClaimantRequired);
	claimant_reps.on("change", makeClaimantRequired);
	
	var types = $('.type');
	types.on("click", enableLien);
	types.on("change", setLienDate);
	/*
	var formatClaimant= function(elCell, oRecord, oColumn, sData) {
		elCell.innerHTML = "<a href='javascript:showFirm(\"" + oRecord.getData("eams_ref_number") + "\",\"claimants\")'>" + oRecord.getData("eams_ref_number") + "</a>";
		//elCell.innerHTML = "Edit";
		// onmouseout='hideInfo(\"carrier\")'
	}
	var formatLienClaimant= function(elCell, oRecord, oColumn, sData) {
		elCell.innerHTML = "<a href='javascript:showFirm(\"" + oRecord.getData("eams_ref_number") + "\",\"lien_attorney\")'>" + oRecord.getData("eams_ref_number") + "</a>";
		//elCell.innerHTML = "Edit";
	}
	var formatClaimantLocation = function(elCell, oRecord, oColumn, sData) {
		elCell.innerHTML = oRecord.getData("street_1") + " " + oRecord.getData("street_2") + ", " + oRecord.getData("city");
		//elCell.innerHTML = "Edit";
	}
	var myClaimantColumnDefs = [
		{key:"eams_ref_number", label:"EAMS #", formatter:formatClaimant, sortable:true, resizeable:true},
		{key:"firm_name", label:"Firm", sortable:true, resizeable:true},
		{key:"", label:"EAMS #", formatter:formatClaimantLocation, sortable:true, resizeable:true}
	];
		
	//list the data
	myClaimantDataSource = new YAHOO.util.DataSource("../../check_eams.php?type=claimants");
	myClaimantDataSource.responseType = YAHOO.util.DataSource.TYPE_TEXT;
	
	myClaimantDataSource.subscribe('responseParseEvent',  function(e) {
		if (e.response.results == 0) {
			//delay hiding the results for a second to show no data result
			setTimeout("hideInfo('claimants');", 2000);
		}
		if (blnShowDataTable) {
			if (last_key_typed.value==27) {
				//alert("not show:" + last_key_typed.value);
				return;
			}
			setDisplayStyle("list_claimants_searches", "display", "");
			myClaimantDataTable.onShow();
		} else {
			hideInfo("claimants");
		}
	});

	myClaimantDataSource.subscribe('requestEvent',  function(e) {
		//capture request
		var therequest = e.request;
		//alert(therequest);
		//let's make a decision
		if (typeof therequest == "undefined") {
			therequest = null;
		}
		
		//only show when proper request
		if (therequest!=null) {
			blnShowDataTable = true;
		} else {
			blnShowDataTable = false;
		}

	});
	
	myClaimantDataSource.responseSchema = { 
		recordDelim: "\n",
		fieldDelim: "|",
		fields: ["eams_ref_number","firm_name","street_1","street_2","city"]
	};
	
	var form_height_med = 300;
	form_height_med = form_height_med + "px";
	//alert(form_height_med);
	myClaimantDataTable = new YAHOO.widget.ScrollingDataTable("list_claimants_searches", myClaimantColumnDefs,
						myClaimantDataSource,{height:form_height_med});
	
	
	var formatAttorney= function(elCell, oRecord, oColumn, sData) {
		elCell.innerHTML = "<a href='javascript:showFirm(\"" + oRecord.getData("eams_ref_number") + "\",\"attorney\")'>" + oRecord.getData("eams_ref_number") + "</a>";
		//elCell.innerHTML = "Edit";
		// onmouseout='hideInfo(\"carrier\")'
	}
	var formatLienAttorney= function(elCell, oRecord, oColumn, sData) {
		elCell.innerHTML = "<a href='javascript:showFirm(\"" + oRecord.getData("eams_ref_number") + "\",\"lien_attorney\")'>" + oRecord.getData("eams_ref_number") + "</a>";
		//elCell.innerHTML = "Edit";
	}
	var formatLocation = function(elCell, oRecord, oColumn, sData) {
		elCell.innerHTML = oRecord.getData("street_1") + " " + oRecord.getData("street_2") + ", " + oRecord.getData("city");
		//elCell.innerHTML = "Edit";
	}
	var myAttorneyColumnDefs = [
		{key:"eams_ref_number", label:"EAMS #", formatter:formatAttorney, sortable:true, resizeable:true},
		{key:"firm_name", label:"Firm", sortable:true, resizeable:true},
		{key:"", label:"EAMS #", formatter:formatLocation, sortable:true, resizeable:true}
	];
		
	//list the data
	myAttorneyDataSource = new YAHOO.util.DataSource("../../check_eams.php?type=reps");
	myAttorneyDataSource.responseType = YAHOO.util.DataSource.TYPE_TEXT;
	
	myAttorneyDataSource.responseSchema = { 
		recordDelim: "\n",
		fieldDelim: "|",
		fields: ["eams_ref_number","firm_name","street_1","street_2","city"]
	};
	
	var form_height_med = 300;
	form_height_med = form_height_med + "px";
	//alert(form_height_med);
	myAttorneyDataTable = new YAHOO.widget.ScrollingDataTable("list_attorney_searches", myAttorneyColumnDefs,
						myAttorneyDataSource,{height:form_height_med});
						
	var myLienAttorneyColumnDefs = [
		{key:"eams_ref_number", label:"EAMS #", formatter:formatLienAttorney, sortable:true, resizeable:true},
		{key:"firm_name", label:"Firm", sortable:true, resizeable:true},
		{key:"", label:"EAMS #", formatter:formatLocation, sortable:true, resizeable:true}
	];
		
	//list the data
	myLienAttorneyDataSource = new YAHOO.util.DataSource("../../check_eams.php?type=reps");
	myLienAttorneyDataSource.responseType = YAHOO.util.DataSource.TYPE_TEXT;
	
	myLienAttorneyDataSource.responseSchema = { 
		recordDelim: "\n",
		fieldDelim: "|",
		fields: ["eams_ref_number","firm_name","street_1","street_2","city"]
	};
	
	myLienAttorneyDataTable = new YAHOO.widget.ScrollingDataTable("list_lien_attorney_searches", myLienAttorneyColumnDefs,
						myLienAttorneyDataSource,{height:form_height_med});
	*/					
	enableLien();
	
	//preamble
	var exempt_yes = $("#exempt_yes");
	var exempt_no = $("#exempt_no");
	exempt_yes.on("change", preambleTerms);
	exempt_no.on("change", preambleTerms);
	
	$("#proceed").on("click", preambleProceed);
	
	$(".submit").on("click", savePage);
	//run it to hide stuff
	preambleTerms();
}
var preambleProceed = function() {
	var exempt_yes = document.getElementById("exempt_yes");
	var exempt_no = document.getElementById("exempt_no");
	var exempt_signature = document.getElementById("exempt_signature");
	
	var need_preamble = $('.need_preamble');

	if (exempt_no.checked || (exempt_yes.checked && exempt_signature.value!="")) {
		$('.need_preamble').show();
	} else {
		//should be defaulted here anyway
		$('.need_preamble').hide();
	}
}
var preambleTerms = function() {
	var exempt_yes = document.getElementById("exempt_yes");
	var exempt_no = document.getElementById("exempt_no");
	var need_check = $('.need_check');
	if (exempt_yes.checked){
		$('.need_check').show();
	}
	if (exempt_no.checked) {
		//no need for text and button
		$('.need_check').hide();
	}
	if (!exempt_no.checked && !exempt_yes.checked) {
		$('.need_check').hide();
	}
	preambleProceed();
	
	enableSave();
}
var checkApplicant = function() {
	<?php if ($kase->jetfile_lien_id=="" || $kase->jetfile_lien_id=="0") { ?>
	$("#jetfile_feedback").html('<a href="javascript:sendLien()">Send Lien to CAJetfile</a>');
	<?php } else { ?>
	$("#jetfile_feedback").html('Lien sent to CAJetfile&nbsp;&#10003;');
	<?php } ?>
	return;
	var jetfile_case_id = $("#jetfile_case_id").val();
	var jetfile_id = $("#jetfile_id").val();
	var uploads = $("#uploads").val();
	
	
	if (jetfile_id=="" || uploads == "") {
		$("#jetfile_feedback").html('Not ready to file');
		return false;
	}
	var formValues = "jetfile_case_id=" + jetfile_case_id;
	var url = '../api/jetfile/check/lien';
	
	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else {
				if (data.lien_id != "-1") {
					//$("#jetfile_feedback").html('&#9992;');
					$("#jetfile_feedback").html('<div style="float:right">Jetfile Case ID:' + data.case_id + ' -> READY TO FILE - CONTACT SUPPORT<br /></div>');
				} else {
					$("#jetfile_feedback").html('<a href="javascript:sendDOR()">Send DOR to CAJetfile</a>');
				}
				$("#jetfile_feedback").css("display", "inline-block");
			}
		}
	});
}
var savePage = function(event) {
	event.preventDefault();
	
	var submit_button = $("#submit");
	submit_button.prop("disabled", true);
	submit_button.val("Saving");
	var formValues = $("#lien_form").serialize();
	//we need exempt value
	if (formValues.indexOf("exempt=") < 0) {
		formValues += "&exempt=";
	}
	
	var url = "../api/jetfile/save/lien";
	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
			success:function (data) {
				submit_button.val("Saved !!");
				submit_button.prop("disabled", false);
				
				$("#jetfile_id").val(data.id);
				
				setTimeout(function() {
					submit_button.val("Save");
				}, 2500);
			}
	});
}
var sendLien = function() {
	var formValues = "case_id=<?php echo $case_id; ?>&injury_id=<?php echo $injury_id; ?>&jetfile_id=<?php echo $jetfile_id; ?>&jetfile_case_id=<?php echo $jetfile_case_id; ?>";
	var url = '../api/jetfile/lien';
	$("#jetfile_feedback").html('Sending Lien to CAJetfile...');
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
				var jetfile_lien_id = data.lien_id;
				//update the cse_jetfile
				updateJetfile(jetfile_case_id, jetfile_lien_id);
			}
		}
	});
}
var updateJetfile = function(jetfile_case_id, jetfile_lien_id) {
	var formValues = "jetfile_id=<?php echo $jetfile_id; ?>&jetfile_case_id=" + jetfile_case_id + "&jetfile_lien_id=" + jetfile_lien_id;
	var url = '../api/jetfile/updatelien';
	$("#jetfile_feedback").html('Updating System with Lien Info...');
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
				$("#jetfile_feedback").html("Lien Saved");
				fileLien(jetfile_case_id, jetfile_lien_id);
			}
		}
	});
}
var fileLien = function(jetfile_case_id, jetfile_lien_id) {
	console.log(jetfile_case_id, jetfile_lien_id)
}
var checkApplicant = function() {
	<?php if ($kase->jetfile_lien_id=="" || $kase->jetfile_lien_id=="0") { ?>
	$("#jetfile_feedback").html('<a href="javascript:sendLien()">Send Lien to CAJetfile</a>');
	<?php } else { ?>
	$("#jetfile_feedback").html('Lien sent to CAJetfile&nbsp;&#10003;');
	<?php } ?>
	return;
	var jetfile_case_id = $("#jetfile_case_id").val();
	var jetfile_id = $("#jetfile_id").val();
	var uploads = $("#uploads").val();
	
	
	if (jetfile_id=="" || uploads == "") {
		$("#jetfile_feedback").html('Not ready to file');
		return false;
	}
	var formValues = "jetfile_case_id=" + jetfile_case_id;
	var url = '../api/jetfile/check/lien';
	
	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else {
				if (data.lien_id != "-1") {
					//$("#jetfile_feedback").html('&#9992;');
					$("#jetfile_feedback").html('<div style="float:right">Jetfile Case ID:' + data.case_id + ' -> READY TO FILE - CONTACT SUPPORT<br /></div>');
				} else {
					$("#jetfile_feedback").html('<a href="javascript:sendLien()">Send Lien to CAJetfile</a>');
				}
				$("#jetfile_feedback").css("display", "inline-block");
			}
		}
	});
}
</script>
</body>
</html>
<?php 
error_reporting(E_ALL);
ini_set('display_errors', '1');	

include("manage_session.php");

if (!isset($_SESSION["user_customer_id"])) {
	die("no No NO");
}
date_default_timezone_set('America/Los_Angeles');

include("connection.php");

include ("../classes/cls_address.php");

$setup_id = "";
$company_type = "";
$company_type_display = "";
$filing_name = "";
$start_date = "";
$filing_address_street = "";
$filing_address_city = "";
$filing_address_state = "";
$filing_address_zip = "";

$ein = "";
$effective_date = "";
$filing_type = "";
$filing_type_display = "";

$employer_account = "";
$deposit_schedule = "";
$deposit_schedule_display = "";
$state_effective_date = "";
$sui_rate = "";
$unemployment_effective_date = "";
$training_rate = "";
$training_effective_date = "";
//get any setup for this company
$query = "SELECT `setup_id` id, `federal`, `general`, `state`
FROM `setup`
WHERE customer_id = :customer_id
AND deleted = 'N'";

$customer_id = $_SESSION["user_customer_id"];
try {
	$sql = $query;
	$db = getConnection();
	$stmt = $db->prepare($sql);  
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->execute();
	$tax_setup = $stmt->fetchObject();
	$stmt->closeCursor(); $stmt = null; $db = null;
	
	//die(print_r($tax_setup));
	
	if (is_object($tax_setup)) {
		//die($tax_setup->general);
		$setup_id = $tax_setup->id;
		if ($tax_setup->general!="") {
			$arrGeneral = json_decode($tax_setup->general);
			//die(print_r($arrGeneral));
			$start_date = $arrGeneral->start_dateField;
			$company_type = $arrGeneral->company_typeField;
			switch($company_type) {
				case "sole":
					$company_type_display = "Sole Proprietor";
					break;
				case "c":
					$company_type_display = "C Corporation";
					break;
				case "s":
					$company_type_display = "S Corporation";
					break;
				case "llc":
					$company_type_display = "Partnership";
					break;
				case "other":
					$company_type_display = "Other";
					break;
				
			}
			$filing_name = $arrGeneral->filing_nameField;
			
			$filing_address_street = $arrGeneral->streetField;
			$filing_address_city = $arrGeneral->cityField;
			$filing_address_state = $arrGeneral->stateField;
			$filing_address_zip = $arrGeneral->zipField;
		}
		
		if ($tax_setup->federal!="") {
			$arrFederal = json_decode($tax_setup->federal);
			//die(print_r($arrFederal));
			$effective_date = $arrFederal->effective_dateField;
			$ein = $arrFederal->einField;
			$filing_type = $arrFederal->filingTypeField;
			$filing_type_display = "";
			switch($filing_type) {
				case "941_M":
					$filing_type_display = "941 Filer, Monthly Depositor";
					break;
				case "941_S":
					$filing_type_display = "941 Filer, Semi-weekly Depositor";
					break;
				case "941_Q":
					$filing_type_display = "941 Filer, Quarterly Depositor";
					break;
				case "944_M":
					$filing_type_display = "944 Filer, Monthly Depositor";
					break;
				case "944_S":
					$filing_type_display = "944 Filer, Semi-weekly Depositor";
					break;
				case "944_A":
					$filing_type_display = "944 Filer, Annual Depositor";
					break;
			}
		}
		
		if ($tax_setup->state!="") {
			$arrState = json_decode($tax_setup->state);
			//die(print_r($arrFederal));
			$training_effective_date = $arrState->training_effective_dateField;
			$state_effective_date = $arrState->state_effective_dateField;
			$unemployment_effective_date = $arrState->unemployment_effective_dateField;
			$training_rate = $arrState->training_rateField;
			$employer_account = $arrState->employer_accountField;
			$deposit_schedule = $arrState->deposit_scheduleField;
			$sui_rate = $arrState->sui_rateField;
			$deposit_schedule_display = "";
			switch($deposit_schedule) {
				case "M":
					$deposit_schedule_display = "Monthly";
					break;
				case "S":
					$deposit_schedule_display = "Semi-weekly";
					break;
				case "Q":
					$deposit_schedule_display = "Quarterly";
					break;
			}
		}
	}
} catch(PDOException $e) {
	echo '{"error":{"text":'. $e->getMessage() .'}}'; 
}
?>
<style>
.info_holder {
	width:550px;
}
</style>
<table width="700" border="0" align="center" cellpadding="0" cellspacing="0" bordercolor="#000000">
    <tr>
      <td colspan="7" align="left" valign="top" bgcolor="#000033"><span class="admintitle">MANAGE TAXES</span></td>
    </tr>
    <tr>
      <td width="30%" align="center" valign="top" bgcolor="#FFFFFF">
        <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF" bordercolor="#000000">
            <tr>
              <td style="background:#FFFFFF">&nbsp;</td>
              <td align="left" valign="top" bgcolor="#FFFFFF">
                  <form id="general_info_form">
                  	<input type="hidden" name="setup_id" id="setup_id" value="<?php echo $setup_id; ?>" />
                      <table border="1" cellpadding="3" cellspacing="0" bordercolor="#ededed" align="center" class="info_holder">
                          <tr>
                            <td colspan="5" style="background:darkslategray; color:white; font-weight:bold" id="header_general">
                                <div style="float:right">
                                    <button class="btn btn-xs btn-primary" id="edit_general">Edit</button>
                                    <button class="btn btn-xs btn-primary hide_me" id="save_general">Save</button>
                                </div>
                                <span style='font-weight:bold'>General Information</span>
                            </td>
                          </tr>
                          <tr style="visibility:hidden">
                            <td align="right" bgcolor="#FFFFFF" style="height:1px"><img src="images/spacer.gif" width="100" height="1" /></td>
                            <td colspan="2" bgcolor="#FFFFFF" style="height:1px"><img src="images/spacer.gif" width="330" height="1" /></td>
                          </tr>
                          <tr >
                              <td align="left" nowrap="nowrap" class="td_label"><span style='font-weight:bold'>Start Date   :</span></td>
                              <td align="left" nowrap="nowrap">
                              	<input value="<?php echo $start_date; ?>" name="start_dateField" type="text" id="start_dateField" style="width:260px" class="general edit_field hide_me" />
                                  
                                  <span id="start_dateSpan" class="general edit_span"><?php echo $start_date; ?></span>
                              </td>
                          </tr>
                          <tr>
                            <td width="108" align="left" class="td_label"><span style='font-weight:bold'>Filing Name :</span></td>
                            <td colspan="2" align="left">
                                <input name="filing_nameField" id="filing_nameField" style="width:260px" value="<?php echo $filing_name; ?>" class="general edit_field hide_me" />
                                <span id="filing_nameSpan" class="general edit_span"><?php echo $filing_name; ?></span>             
                            </td>
                          </tr>
                          <tr>
                            <td width="108" align="left" class="td_label"><span style='font-weight:bold'>Type :</span></td>
                            <td colspan="2" align="left">
                                <select name="company_typeField" id="company_typeField" class="general edit_field hide_me">  
                                    <option value="" <?php if ($company_type=="") { echo "selected"; } ?>>Select from List ....</option>
                                    <option value="sole" <?php if ($company_type=="sole") { echo "selected"; } ?>>Sole Proprietor</option>
                                    <option value="c" <?php if ($company_type=="c") { echo "selected"; } ?>>C Corporation</option>
                                    <option value="s" <?php if ($company_type=="s") { echo "selected"; } ?>>S Corporation</option>
                                    <option value="llc" <?php if ($company_type=="llc") { echo "selected"; } ?>>LLC</option>
                                    <option value="p" <?php if ($company_type=="p") { echo "selected"; } ?>>Partnership</option>
                                    <option value="other" <?php if ($company_type=="other") { echo "selected"; } ?>>Other</option>
                                </select> 
                                <span id="company_typeSpan" class="general edit_span"><?php echo $company_type_display; ?></span>             
                            </td>
                          </tr>
                          <tr >
                              <td align="left" nowrap="nowrap" class="td_label">
                                <span style='font-weight:bold'>Filing Address  :</span>
                                <br />
                                <input type="checkbox" name="sameasField" id="sameasField" />&nbsp;Same as Business Address
                              </td>
                              <td align="left">
                                <textarea name="streetField" style="width:260px" rows="2" id="streetField" class="general edit_field hide_me"><?php echo $filing_address_street; ?></textarea>
                                <span id="streetSpan" class="general edit_span"><?php echo $filing_address_street; ?></span>
                              </td>
                            </tr>
                            <tr >
                              <td align="left" nowrap="nowrap" class="td_label"><span style='font-weight:bold'>City, State Zip   :</span></td>
                              <td align="left" nowrap="nowrap"><input value="<?php echo $filing_address_city; ?>" name="cityField" type="text" id="cityField" size="20" class="general edit_field hide_me" />
                                  <input name="stateField" type="text" id="stateField"  style="width:25px" value="<?php echo $filing_address_state; ?>" class="general edit_field hide_me" />
                                  <input name="zipField" type="text" id="zipField" style="width:50px" value="<?php echo $filing_address_zip; ?>" class="general edit_field hide_me" />
                                  <span id="citySpan" class="general edit_span"><?php echo $filing_address_city; ?></span>
                                  <span id="stateSpan" class="general edit_span"><?php echo $filing_address_state; ?></span>
                                  <span id="zipSpan" class="general edit_span"><?php echo $filing_address_zip; ?></span>
                               </td>
                            </tr>
                      </table>
                  </form>
             </td>
            </tr>
        </table>
   </td>
   <td align="center" valign="top" bgcolor="#FFFFFF">
   		<img src="images/spacer.gif" width="15" height="1" />
   </td>
   <td width="30%" align="center" valign="top">
   		<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF" bordercolor="#000000">
            <tr>
              <td style="background:#FFFFFF">&nbsp;</td>
              <td align="left" valign="top" bgcolor="#FFFFFF">
                  <form id="federal_info_form">
                  	<input type="hidden" name="setup_id" id="setup_id" value="<?php echo $setup_id; ?>" />
                      <table border="1" cellpadding="3" cellspacing="0" bordercolor="#ededed" align="center" class="info_holder">
                          <tr>
                            <td colspan="5" style="background:darkslategray; color:white; font-weight:bold" id="header_federal">
                                <div style="float:right">
                                    <button class="btn btn-xs btn-primary" id="edit_federal">Edit</button>
                                    <button class="btn btn-xs btn-primary hide_me" id="save_federal">Save</button>
                                </div>
                                <span style='font-weight:bold'>Federal Information</span>
                            </td>
                          </tr>
                          <tr style="visibility:hidden">
                            <td align="right" bgcolor="#FFFFFF" style="height:1px"><img src="images/spacer.gif" width="100" height="1" /></td>
                            <td colspan="2" bgcolor="#FFFFFF" style="height:1px"><img src="images/spacer.gif" width="330" height="1" /></td>
                          </tr>
                        <tr >
                            <td align="left" nowrap="nowrap" class="td_label">
                              <span style='font-weight:bold'>EIN   :</span></td>
                            <td align="left">
                            <input value="<?php echo $ein; ?>" name="einField" onkeyup="mask(this, mein);" onblur="mask(this, mein);" placeholder="12-3456789" type="text" id="einField" style="width:260px" class="federal edit_field hide_me" />	
                            <span id="einSpan" class="federal edit_span"><?php echo $ein; ?></span>
                            </td>
                        </tr>
                        <tr >
                          <td align="left" nowrap="nowrap" class="td_label"><span style='font-weight:bold'>Effective Date   :</span></td>
                          <td align="left" nowrap="nowrap">
                            <input value="<?php echo $effective_date; ?>" name="effective_dateField" type="text" id="effective_dateField" style="width:260px" class="federal edit_field hide_me" />
                              
                              <span id="effective_dateSpan" class="federal edit_span"><?php echo $effective_date; ?></span>
                          </td>
                        </tr>
                          
                        <tr >
                          <td align="left" nowrap="nowrap" class="td_label"><span style='font-weight:bold'>Deposit Schedule   :</span></td>
                          <td align="left" nowrap="nowrap">
                            <select name="filingTypeField" id="filingTypeField" style="width:260px" class="federal edit_field hide_me">
                            	<option value="" <?php if ($filing_type=="") { echo "selected"; } ?>></option>
                                <option value="941_M" <?php if ($filing_type=="941_M") { echo "selected"; } ?>>941 Filer, Monthly Depositor</option>
                                <option value="941_S" <?php if ($filing_type=="941_S") { echo "selected"; } ?>>941 Filer, Semi-weekly Depositor</option>
                                <option value="941_Q" <?php if ($filing_type=="941_Q") { echo "selected"; } ?>>941 Filer, Quarterly Depositor</option>
                                <option value="944_M" <?php if ($filing_type=="944_M") { echo "selected"; } ?>>944 Filer, Monthly Depositor</option>
                                <option value="944_S" <?php if ($filing_type=="944_S") { echo "selected"; } ?>>944 Filer, Semi-weekly Depositor</option>
                                <option value="944_A" <?php if ($filing_type=="944_A") { echo "selected"; } ?>>944 Filer, Annual Depositor</option>
                             </select>
                             <span id="filingTypeSpan" class="federal edit_span"><?php echo $filing_type_display; ?></span>
                          </td>
                        </tr>
                      </table>
                   </form>
              </td>
           </tr>
        </table>
   </td>
   </tr>
    <tr>
      <td align="center" valign="top" bgcolor="#FFFFFF">&nbsp;</td>
      <td align="center" valign="top" bgcolor="#FFFFFF">&nbsp;</td>
      <td align="center" valign="top">&nbsp;</td>
    </tr>
    <tr>
      <td align="center" valign="top" bgcolor="#FFFFFF">
      	<form id="state_info_form">
            <input type="hidden" name="setup_id" id="setup_id" value="<?php echo $setup_id; ?>" />
              <table border="1" cellpadding="3" cellspacing="0" bordercolor="#ededed" align="center" class="info_holder">
                  <tr>
                    <td colspan="5" style="background:darkslategray; color:white; font-weight:bold" id="header_state">
                        <div style="float:right">
                            <button class="btn btn-xs btn-primary" id="edit_state">Edit</button>
                            <button class="btn btn-xs btn-primary hide_me" id="save_state">Save</button>
                        </div>
                        <span style='font-weight:bold'>State Info</span>
                    </td>
                  </tr>
                  <tr style="visibility:hidden">
                    <td align="right" bgcolor="#FFFFFF" style="height:1px"><img src="images/spacer.gif" width="100" height="1" /></td>
                    <td colspan="2" bgcolor="#FFFFFF" style="height:1px"><img src="images/spacer.gif" width="330" height="1" /></td>
                  </tr>
                  <tr>
                      <td colspan="5" style="background:lightslategray; color:white; font-weight:bold">
                        <span style='font-weight:bold'>CA Withholding Information</span>
                      </td>
                  </tr>
                  <tr >
                    <td align="left" nowrap="nowrap" class="td_label">
                      <span style='font-weight:bold'>Employer Account   :</span></td>
                    <td align="left">
                    <input value="<?php echo $employer_account; ?>" name="employer_accountField" onkeyup="mask(this, memp);" onblur="mask(this, memp);" placeholder="123-34567-8" type="text" id="employer_accountField" style="width:260px" class="state edit_field hide_me" />	
                    <span id="employer_accountSpan" class="state edit_span"><?php echo $employer_account; ?></span>
                    </td>
                  </tr>
                  <tr >
                      <td align="left" nowrap="nowrap" class="td_label"><span style='font-weight:bold'>Deposit Schedule   :</span></td>
                      <td align="left" nowrap="nowrap">
                        <select name="deposit_scheduleField" id="deposit_scheduleField" style="width:260px" class="state edit_field hide_me">
                            <option value="" <?php if ($deposit_schedule=="") { echo "selected"; } ?>></option>
                            <option value="M" <?php if ($deposit_schedule=="M") { echo "selected"; } ?>>Monthly</option>
                            <option value="S" <?php if ($deposit_schedule=="S") { echo "selected"; } ?>>Semi-weekly</option>
                            <option value="Q" <?php if ($deposit_schedule=="Q") { echo "selected"; } ?>>Quarterly</option>
                         </select>
                         <span id="deposit_scheduleSpan" class="state edit_span"><?php echo $deposit_schedule_display; ?></span>
                      </td>
                    </tr>
                    <tr >
                      <td align="left" nowrap="nowrap" class="td_label"><span style='font-weight:bold'>Effective Date   :</span></td>
                      <td align="left" nowrap="nowrap">
                        <input value="<?php echo $state_effective_date; ?>" name="state_effective_dateField" type="text" id="state_effective_dateField" style="width:260px" class="state edit_field hide_me" />
                          
                          <span id="state_effective_dateSpan" class="state edit_span"><?php echo $state_effective_date; ?></span>
                      </td>
                    </tr>
                    <tr style="visibility:hidden">
                        <td align="right" bgcolor="#FFFFFF" style="height:1px"><img src="images/spacer.gif" width="100" height="1" /></td>
                        <td colspan="2" bgcolor="#FFFFFF" style="height:1px"><img src="images/spacer.gif" width="330" height="1" /></td>
                    </tr>
                    <tr>
                      <td colspan="5" style="background:lightslategray; color:white; font-weight:bold">
                        <span style='font-weight:bold'>State Unemployment Insurance</span>
                      </td>
                  </tr>
                  <tr style="visibility:hidden">
                    <td align="right" bgcolor="#FFFFFF" style="height:1px"><img src="images/spacer.gif" width="100" height="1" /></td>
                    <td colspan="2" bgcolor="#FFFFFF" style="height:1px"><img src="images/spacer.gif" width="330" height="1" /></td>
                  </tr>
                  <tr >
                    <td align="left" nowrap="nowrap" class="td_label">
                      <span style='font-weight:bold'>Rate   :</span></td>
                    <td align="left">
                    <input value="<?php echo $sui_rate; ?>" name="sui_rateField" type="text" id="sui_rateField" style="width:260px" class="state edit_field hide_me" />	
                    <span id="sui_rateSpan" class="state edit_span"><?php echo $sui_rate; ?></span>
                    </td>
                  </tr>
                  <tr >
                      <td align="left" nowrap="nowrap" class="td_label"><span style='font-weight:bold'>Effective Date   :</span></td>
                      <td align="left" nowrap="nowrap">
                        <input value="<?php echo $unemployment_effective_date; ?>" name="unemployment_effective_dateField" type="text" id="unemployment_effective_dateField" style="width:260px" class="state edit_field hide_me" />
                          
                          <span id="unemployment_effective_dateSpan" class="state edit_span"><?php echo $unemployment_effective_date; ?></span>
                      </td>
                    </tr>
                  <tr>
                      <td colspan="5" style="background:lightslategray; color:white; font-weight:bold">
                        <span style='font-weight:bold'>State Employment Training</span>
                      </td>
                  </tr>
                  <tr style="visibility:hidden">
                    <td align="right" bgcolor="#FFFFFF" style="height:1px"><img src="images/spacer.gif" width="100" height="1" /></td>
                    <td colspan="2" bgcolor="#FFFFFF" style="height:1px"><img src="images/spacer.gif" width="330" height="1" /></td>
                  </tr>
                  <tr >
                    <td align="left" nowrap="nowrap" class="td_label">
                      <span style='font-weight:bold'>Rate   :</span></td>
                    <td align="left">
                    <input value="<?php echo $training_rate; ?>" name="training_rateField" type="text" id="training_rateField" style="width:260px" class="state edit_field hide_me" />	
                    <span id="training_rateSpan" class="state edit_span"><?php echo $training_rate; ?></span>
                    </td>
                  </tr>
                  <tr >
                      <td align="left" nowrap="nowrap" class="td_label"><span style='font-weight:bold'>Effective Date   :</span></td>
                      <td align="left" nowrap="nowrap">
                        <input value="<?php echo $training_effective_date; ?>" name="training_effective_dateField" type="text" id="training_effective_dateField" style="width:260px" class="state edit_field hide_me" />
                          
                          <span id="training_effective_dateSpan" class="state edit_span"><?php echo $training_effective_date; ?></span>
                      </td>
                    </tr>
              </table>
        </form>
      </td>
      <td align="center" valign="top" bgcolor="#FFFFFF">&nbsp;</td>
      <td align="center" valign="top">
      	&nbsp;
      </td>
    </tr>
</table>

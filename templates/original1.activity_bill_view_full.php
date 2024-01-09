<?php
/*
include("../api/manage_session.php");
session_write_close();

include("../api/connection.php");

$sql = "SELECT DISTINCT activity_category
FROM cse_activity
WHERE customer_id = :customer_id";

try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
	$stmt->execute();
	$acitivy_categories =  $stmt->fetchAll(PDO::FETCH_OBJ);
	
	$stmt->closeCursor(); $stmt = null; $db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage(), "sql"=>$sql));
		echo json_encode($error);
}
*/
?>
<form id="billing_form" class="activity_bill" parsley-validate>
    <input type="button" class="btn btn-xs" style="float:right; cursor:pointer; margin-right:-10px; display:none" id="save_billing_modal" value="Save" />
    <input id="table_name" name="table_name" type="hidden" value="activity_bill" />
    <input id="table_id" name="table_id" type="hidden" value="" />
    <input id="table_uuid" name="table_uuid" type="hidden" value="" />
    <input id="billing_id" name="billing_id" type="hidden" value="" />
    <input id="case_id" name="case_id" type="hidden" value="" />
    <input id="action_id" name="action_id" type="hidden" class="activity_bill billing_form" value="<%=id %>" />
    <input id="modal_type" name="modal_type" type="hidden" class="activity_bill billing_form" value="Activity" />
	<table width="600px" border="0" align="left" cellpadding="3" cellspacing="0" style="display:" id="billing_table">
	<tr id="date_row">
        <th align="left" valign="top" scope="row" width="100px">Date:</th>
        <td colspan="2" valign="top">
              <input value="" name="billing_dateInput" id="billing_dateInput" class="activity_bill billing_form" placeholder="" style="width:125px" parsley-error-message="Req" required />
              <span style="margin-left:62px"><strong>Duration (mins):</strong></span>&nbsp;&nbsp;<span>
              <input type="number" value="<%=hours %>" name="durationInput" id="durationInput" class="activity_bill billing_form" placeholder="" style="width:38px" onkeypress='return event.charCode >= 48 && event.charCode <= 57' /></span>
        </td>
	</tr>
	<tr id="status_row">
		<th align="left" valign="top" scope="row">Account:</th>
		<td>
        	<select name="statusInput" id="statusInput" class="activity_bill billing_form" style="width:330px" size="4">
                <option value="regular_billable">Regular Billable</option>
                <option value="special_billable">Special Billable</option>
                <option value="business_development">Business Development</option>
                <option value="professional_development">Professional Development</option>
            </select>
        </td>
	</tr>
    <tr id="wheels">
      <th align="left" valign="top" scope="row">&nbsp;</th>
      <td style="text-align:left; font-size:0.9em; font-style:italic" id="training_wheels">To make Billable, please select an Account above</td>
    </tr>
    <tr id="billing_rate_row">
		<th align="left" valign="top" scope="row">Rate:</th>
		<td>
        	<select name="billing_rateInput" id="billing_rateInput" class="activity_bill billing_form" style="width:330px">
                <option value="contingency">Contingency</option>
                <option value="discount">Discount</option>
                <option value="normal">Normal</option>
                <option value="premium">Premium</option>
                <option value="fixed_fee">Fixed Fee</option>
                <option value="flat_rate_activity">Flat Rate Activity</option>
                <option value="no_charge">No Charge</option>
            </select>
        </td>
	</tr>
    <tr id="activity_code_row">
		<th align="left" valign="top" scope="row">Category:</th>
		<td>
        	<select name="activity_codeInput" id="activity_codeInput" class="activity_bill billing_form" style="width:330px">
                <option selected="selected" value="not_specified">Not Specified</option>
                <option value="Consultation with">Consultation with</option>
                <option value="Correspondence with">Correspondence with</option>
                <option value="Discussion with">Discussion with</option>
                <option value="Drafting documents">Drafting documents</option>
                <option value="Filing Documentation">Filing Documentation</option>
                <option value="Lunch with">Lunch with</option>
                <option value="Meeting with">Meeting with</option>
                <option value="Negotiations">Negotiations</option>
                <option value="Prepare opinion">Prepare opinion</option>
                <option value="Reporting">Reporting</option>
                <option value="Research">Research</option>
                <option value="Reviewing">Reviewing</option>
                <option value="Reviewing Documents">Reviewing Documents</option>
                <option value="Telephone Conference with">Telephone Conference with</option>
                <option value="Motions">Motions</option>
                <option value="Interview witness">Interview witness</option>
                <option value="Consultations with expert witness">Consultations with expert witness</option>
                <option value="Brief witness">Brief witness</option>
                <option value="Discovery preparations">Discovery preparations</option>
                <option value="Attend discovery">Attend discovery</option>
                <option value="Trial preparations">Trial preparations</option>
                <option value="Attend Trial">Attend Trial</option>
                <option value="Taxation advice">Taxation advice</option>
                <option value="Telephone - exchange of voice mail">Telephone - exchange of voice mail</option>
                <option value="Telephone conference with client">Telephone conference with client</option>
                <option value="Telephone conference with other side">Telephone conference with other side</option>
                <option value="Incorporate company">Incorporate company</option>
                <option value="Instructing research assistant">Instructing research assistant</option>
                <option value="On-line research">On-line research</option>
                <option value="Reviewing case-law">Reviewing case-law</option>
                <option value="Plan and prepare for">Plan and prepare for</option>
                <option value="Research">Research</option>
                <option value="Draft/revise">Draft/revise</option>
                <option value="Review/analyze">Review/analyze</option>
                <option value="Communicate (in firm)">Communicate (in firm)</option>
                <option value="Communicate (with client)">Communicate (with client)</option>
                <option value="Communicate (other outside counsel)">Communicate (other outside counsel)</option>
                <option value="Communicate (other external)">Communicate (other external)</option>
                <option value="Appear for/attend">Appear for/attend</option>
                <option value="Manage data/files">Manage data/files</option>
                <option value="Billable Travel Time">Billable Travel Time</option>
                <option value="Medical Record and Medical Bill Management">Medical Record and Medical Bill Management</option>
                <option value="Training">Training</option>
                <option value="Special Handling Copying/Scanning/Imaging (Internal)">Special Handling Copying/Scanning/Imaging (Internal)</option>
                <option value="Collection-Forensic">Collection-Forensic</option>
                <option value="Culling &amp; Filtering">Culling &amp; Filtering</option>
                <option value="Processing">Processing</option>
                <option value="Review and Analysis">Review and Analysis</option>
                <option value="Quality Assurance and Control">Quality Assurance and Control</option>
                <option value="Search Creation and Execution">Search Creation and Execution</option>
                <option value="Privilege Review Culling and Log Creation">Privilege Review Culling and Log Creation</option>
                <option value="Document Production Creation and Preparation">Document Production Creation and Preparation</option>
                <option value="Evidence/Exhibit Creation and Preparation">Evidence/Exhibit Creation and Preparation</option>
                <option value="Project Management">Project Management</option>
                <option value="Collection Closing Activities">Collection Closing Activities</option>
            </select>
        </td>
	</tr>
    <tr id="timekeeper_row">
		<th align="left" valign="top" scope="row">Assigned To:</th>
		<td><input type="input" name="timekeeperInput" id="timekeeperInput" class="activity_bill billing_form" value="" style="width:330px" />
        </td>
	</tr>
    <tr id="description_row">
		<th align="left" valign="top" scope="row">Description:</th>
		<td><textarea type="input" name="descriptionInput" id="descriptionInput" class="activity_bill billing_form" value="" style="width:330px" rows="5"></textarea>
        </td>
	</tr>
</table>
</form>
<div id="activity_bill_all_done"></div>
<script language="javascript">
$( "#activity_bill_all_done" ).trigger("click");
</script>
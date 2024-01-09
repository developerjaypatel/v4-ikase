<?php
include("../api/manage_session.php");
session_write_close();
?>
<?php if ($_SESSION["user_customer_id"]==1033) { ?>
<div style="margin-top:-10px; padding-bottom:5px">
	<input type="button" class="btn btn-xs" id="view_note" value="Rx">
    <% if (id != "-1") { %>
	<input type="button" class="btn btn-xs" id="view_billable" value="Bill This">
    <% } %>
</div>
<?php } ?>
<div class="new_rx">
    <form id="rx_form" parsley-validate>
    <input id="table_name" name="table_name" type="hidden" value="rx" />
    <input id="table_id" name="table_id" type="hidden" value="<%=id %>" />
    <input id="person_id" name="person_id" type="hidden" value="<%=person_id %>" />
    <input id="billing_time" name="billing_time" type="hidden" value="" />
    <table width="550" border="0" align="left" cellpadding="2" cellspacing="0">
        <tr>
            <th width="10%" align="left" valign="top" scope="row">Applicant:</th>
            <td width="90%" valign="top">
                <span class='modal_span'><%=full_name %></span>
            </td>
        </tr>
        <tr>
            <th width="22" align="right" valign="top" scope="row">Date:</th>
            <td valign="top">
              <input name="start_dateInput" id="start_dateInput" class="modal_input rx_date" placeholder="Start" parsley-error-message="" required style="margin-top:-2px; width:75px" value="<%=start_date %>" tabindex="0" /> 
              through
              <input name="end_dateInput" id="end_dateInput" class="modal_input rx_date" placeholder="End" parsley-error-message="" required style="margin-top:-2px; width:75px" value="<%=end_date %>" tabindex="1" /> 
           </td>
        </tr>
        <tr>
            <th align="right" valign="top" scope="row"><strong>Doctor</strong></th>
            <td valign="top">
                <select name="doctor_idInput" id="doctor_idInput" class="modal_input" style="width:203px" tabindex="2">
                </select>
            </td>
        </tr>
        <!--
        <tr>
          <th align="right" valign="top" scope="row"><strong>Medication</strong></th>
          <td valign="top">
          	<div style="float:right">
                <label><strong>Refills</strong></label>
                <input type="checkbox" name="refillsInput" id="refillsInput" tabindex="6" style="margin-top:-2px; display:" value="Y" <% if (refills=="Y") { %>checked<% } %> />
            </div>
            <div style="float:right; margin-right:10px">
            	<label><strong>Dosage</strong></label>
                <input type="text" name="dosageInput" id="dosageInput" placeholder="gr, ml, ..." parsley-error-message="" style="margin-top:-2px; width:80px" value="<%=dosage %>" tabindex="5" />
            </div>
            <input name="medicationInput" id="medicationInput" class="modal_input" placeholder="Name of Medication" parsley-error-message="" style="margin-top:-2px; width:203px" value="<%=medication %>" tabindex="4" />
          </td>
        </tr>
        <tr>
            <th align="right" valign="top" scope="row"><strong>Regimen</strong>:</th>
            <td valign="top">
                <textarea name="regimenInput" id="regimenInput" class="modalInput new_rx input_class" style="width:100%; height:60px" placeholder="How often, how many pills, etc..." tabindex="7"><%=regimen %></textarea>
            </td>
        </tr>
        -->
        <tr>
            <th align="right" valign="top" scope="row"><strong>Rx</strong>:</th>
            <td valign="top">
            	<textarea name="notesInput" id="notesInput" class="modalInput new_rx input_class" style="width:100%; height:200px" tabindex="8"><%=notes %></textarea></td>
        </tr>
</table>
</form>
</div>
<?php if ($_SESSION["user_customer_id"]==1033) { ?>
<div id="billing_holder" style="display:none; padding-right:15px">
	<form id="billing_form" parsley-validate>
    <input type="button" class="btn btn-xs" style="float:right; cursor:pointer; margin-right:-10px" id="save_billing_modal" value="Save" />
    <input id="table_name" name="table_name" type="hidden" value="billing" />
    <input id="table_id" name="table_id" type="hidden" value="" />
    <input id="table_uuid" name="table_uuid" type="hidden" value="" />
    <input id="billing_id" name="billing_id" type="hidden" value="" />
    <input id="case_id" name="case_id" type="hidden" value="" />
    <input id="action_id" name="action_id" type="hidden" class="billing billing_form" value="<%=id %>" />
    <input id="action_type" name="action_type" type="hidden" class="billing billing_form" value="note" />
	<table width="600px" border="0" align="left" cellpadding="3" cellspacing="0" style="display:" id="billing_table">
	<tr id="date_row">
        <th align="left" valign="top" scope="row" width="100px">Date:</th>
        <td colspan="2" valign="top">
              <input value="" name="billing_dateInput" id="billing_dateInput" class="billing billing_form" placeholder="" style="width:160px" parsley-error-message="Req" required />
              <span style="margin-left:15px"><strong>Duration(min):</strong></span>&nbsp;&nbsp;<span>
              <input value="" name="durationInput" id="durationInput" class="billing billing_form" placeholder="" style="width:38px" onkeypress='return event.charCode >= 48 && event.charCode <= 57' /></span>
        </td>
	</tr>
	<tr id="status_row">
		<th align="left" valign="top" scope="row">Status:</th>
		<td>
        	<select name="statusInput" id="statusInput" class="billing billing_form" style="width:310px">
                <option value="">Select Status..</option>
                <option value="regular_billable">Regular Billable</option>
                <option value="special_billable">Special Billable</option>
                <option value="business_development">Business Development</option>
                <option value="professional_development">Professional Development</option>
            </select>
        </td>
	</tr>
    <tr id="billing_rate_row">
		<th align="left" valign="top" scope="row">Billing Rate:</th>
		<td>
        	<select name="billing_rateInput" id="billing_rateInput" class="billing billing_form" style="width:310px">
                <option value="">Select Rate..</option>
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
		<th align="left" valign="top" scope="row">Activity Code:</th>
		<td>
        	<select name="activity_codeInput" id="activity_codeInput" class="billing billing_form" style="width:310px">
                <option selected="selected" value="not_specified">Not Specified</option>
                <option value="398087da-44a7-418b-9deb-f39890a2cea9">Consultation with</option>
                <option value="d971bae8-1a3e-471f-b19c-3124db828a7d">Correspondence with</option>
                <option value="9ae7291c-82d0-4fc9-8d56-7fa9abf218d5">Discussion with</option>
                <option value="d18e6b09-a046-46d5-8f5a-2196c1cc0b78">Drafting documents</option>
                <option value="de83a2e2-92f5-4b03-a03f-86ddea0b33dc">Filing Documentation</option>
                <option value="909b7286-fa44-4cca-9f7c-98405c388927">Lunch with</option>
                <option value="bdc32d74-f284-4b8d-9acc-bd82a12461cb">Meeting with</option>
                <option value="60ed1acd-80a6-4ece-ac8f-ed9ea910303b">Negotiations</option>
                <option value="b55efef8-fc31-4149-b9ce-dd6363c39561">Prepare opinion</option>
                <option value="ad262a84-35db-4c8d-a990-2dff9dd47d51">Reporting</option>
                <option value="3133bec0-c0ed-4591-b671-2a6951269a1f">Research</option>
                <option value="4ffd5629-2c25-42c8-8902-98ba768575c3">Reviewing</option>
                <option value="692f128a-6e32-47e0-8558-dcdb47ffe05a">Reviewing Documents</option>
                <option value="1a13cbae-87ad-43a7-8771-8d6f0db57fa7">Telephone Conference with</option>
                <option value="c2375f26-2a45-490c-bad9-860ff04eff5a">Motions</option>
                <option value="77f9a56b-c282-4f8a-8521-7f864d23b1aa">Interview witness</option>
                <option value="ac86a19b-ea3f-475c-95b9-3a2123fde20c">Consultations with expert witness</option>
                <option value="21681748-fe81-49ac-807c-00753442459a">Brief witness</option>
                <option value="74fb9200-e2f5-4291-b9b0-d3453dd97543">Discovery preparations</option>
                <option value="e597434d-aa25-4b0d-a005-f3054a00359b">Attend discovery</option>
                <option value="4d372c88-6540-4af3-ac87-d3320dd96d14">Trial preparations</option>
                <option value="abf10a6f-40ca-4a11-a90c-f71b87998b30">Attend Trial</option>
                <option value="8fa4e8c7-3c6d-4978-8118-6cc045e5e87c">Taxation advice</option>
                <option value="e00753f3-d782-44e7-8195-da0cff9e1fa9">Telephone - exchange of voice mail</option>
                <option value="cbf992ea-6f4d-4510-9472-6c7952faa32c">Telephone conference with client</option>
                <option value="479205f3-3b4e-4184-ba4b-416081435a1c">Telephone conference with other side</option>
                <option value="6f9d8e59-08a2-4462-a726-7a6e1f104d64">Incorporate company</option>
                <option value="8ee8d265-4398-47d0-a710-5d67b1e2de89">Instructing research assistant</option>
                <option value="e39550bb-2ccd-4c45-a99a-974f71af290e">On-line research</option>
                <option value="1f294c00-1e79-43a1-895b-2e52d549f34e">Reviewing case-law</option>
                <option value="b1714f44-34fb-413f-8896-822fa3feec4c">Plan and prepare for</option>
                <option value="8071a61a-d165-4693-ae41-f28c8b4f13ff">Research</option>
                <option value="df6b850e-e413-4a60-a49d-bbdc04ed5cdf">Draft/revise</option>
                <option value="f1c081b8-622d-4b55-a9cf-3078a9c42920">Review/analyze</option>
                <option value="59b39650-512d-4651-a22a-30a1ad50dc16">Communicate (in firm)</option>
                <option value="7663f710-bf40-4206-bed3-5abe3a827a24">Communicate (with client)</option>
                <option value="b696dd9d-fbb2-4c80-b23d-e7b7160ef10e">Communicate (other outside counsel)</option>
                <option value="5ef5a862-7d12-4673-9771-2f97d10439c2">Communicate (other external)</option>
                <option value="3912cf8c-e224-4284-aede-2c59f44eb771">Appear for/attend</option>
                <option value="2b9b471a-5b14-4667-a3b1-11247773a044">Manage data/files</option>
                <option value="de2f61c7-7148-4e82-9378-821cdff90d08">Billable Travel Time</option>
                <option value="e3b5f1af-ceb1-45d2-b2bb-7d7e62231804">Medical Record and Medical Bill Management</option>
                <option value="30f6b6e1-1e9e-43c2-ad44-199117f50221">Training</option>
                <option value="002d905b-7a41-4312-b07d-6d606453ac83">Special Handling Copying/Scanning/Imaging (Internal)</option>
                <option value="84c8facc-cc75-44d2-bd4c-61be744d2be3">Collection-Forensic</option>
                <option value="bd9b4a01-6b95-443b-bfaa-daf776d824d8">Culling &amp; Filtering</option>
                <option value="f8bfd31f-3461-4a52-919b-2fd7d1964a0f">Processing</option>
                <option value="ff6b8f77-c897-4bf8-91b1-cd552bf6449d">Review and Analysis</option>
                <option value="4130d181-fa13-4afd-a3b1-feac770f36b9">Quality Assurance and Control</option>
                <option value="46c58d7d-cab8-4800-a5ed-483d4b31341b">Search Creation and Execution</option>
                <option value="c7c4652d-f737-4571-b0b2-6a0393dde358">Privilege Review Culling and Log Creation</option>
                <option value="37e931e3-2384-49c4-8cbc-20dcbbb6b65d">Document Production Creation and Preparation</option>
                <option value="ab84e885-1c17-4b4c-ad49-1fef8ed953b8">Evidence/Exhibit Creation and Preparation</option>
                <option value="bfa1eeb1-dbb0-41d9-857a-0eea12972d1a">Project Management</option>
                <option value="3dda6652-f5e6-43fb-bac3-abefd4bab11c">Collection Closing Activities</option>
            </select>
        </td>
	</tr>
    <tr id="timekeeper_row">
		<th align="left" valign="top" scope="row">Timekeeper:</th>
		<td><input type="input" name="timekeeperInput" id="timekeeperInput" class="billing billing_form" value="" style="width:310px" />
        </td>
	</tr>
    <tr id="description_row">
		<th align="left" valign="top" scope="row">Description:</th>
		<td><textarea type="input" name="descriptionInput" id="descriptionInput" class="billing billing_form" value="" style="width:310px" rows="5"></textarea>
        </td>
	</tr>
</table>
</form>
</div>
<?php } ?>
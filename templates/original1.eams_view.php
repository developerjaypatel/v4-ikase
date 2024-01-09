<% if (blnShowBilling) { %>
<div style="margin-top:-10px; padding-bottom:5px">
	<input type="button" class="btn btn-xs" id="view_main" value="Form" style="display:none">
	<input type="button" class="btn btn-xs" id="view_billable" value="Bill This" title="Click to Create/Update Billing">
    <div style="display:none" id="cancel_billable_holder">
    	&nbsp;
    	<input type="button" class="btn btn-xs btn-warning" id="cancel_billable" value="Cancel Bill" title="Click to Clear Billing">
    </div>
</div>
<% } %>
<div class="eams" style="margin-left:5px">
<form id="eams_form" class="eams" parsley-validate>
    <input id="table_name" name="table_name" type="hidden" value="eams" />
    <input id="case_id" name="case_id" type="hidden" value="<%=case_id %>" />
    <input id="nopublish" name="nopublish" type="hidden" value="n" />
    <input type="hidden" id="eams_form_name" name="eams_form_name" value="<%=eams_form_name %>" />
    <input type="hidden" id="eams_form_id" name="eams_form_id" value="" />
    <div id="loading" style="display:none"><i class='icon-spin4 animate-spin' style='font-size:6em; color:white'></i></div>
    <div style="width:350px; height:500px; display:none; float:right;" id="eams_parties_list_holder">
    	<div style="float:right">
            <input type="checkbox" class="form_parties" id="form_parties" value="Y" title="Select All" />Select All
            <input type="hidden" name="partie_count" value="<%=parties.split('\r\n').length %>" />
        </div>
        Parties <span style="font-size:0.8em">(Select from List to set Interested Parties for Form)</span>
        <div style="border:1px solid white; height:450px; overflow-y:scroll" id="eams_parties_list">
        </div>
    </div>
    <div id="fields_holder" style="display:">
        <table width="600px" border="0" align="left" cellpadding="3" cellspacing="0">
            <tr>
                <th width="18%" align="left" valign="top" nowrap="nowrap" scope="row">Form:</th>
                <td width="82%" valign="top" id="eams_form_name_holder">
                  <%=eams_display_name %>
                </td>
            </tr>
            <tr>
                <th width="18%" align="left" valign="top" nowrap="nowrap" scope="row">Case:</th>
                <td width="82%" valign="top">
                  <%=case_name %>
                </td>
            </tr>
            <tr id="separator_holder">
              <th align="left" valign="top" nowrap="nowrap" scope="row">Separator Title:</th>
              <td valign="top">
                <select name="separator_title" id="separator_title" <% if (eams_form_name=="separator") { %>required<% } %>>
                  <option selected="selected" value="">Select Separator Sheet Title</option>
                  <option value="APPLICATION FOR ADJUDICATION">Application for Adjudication</option>
                  <option value="DECLARATION OF READINESS TO PROCEED">Declaration of Readiness to Proceed</option>
                  <option value="DECLARATION OF READINESS TO PROCEED TO EXPEDITED HEARING">Declaration of Readiness to Proceed Expedited</option>
                  <option value="DISMISSAL OF ATTORNEY">DISMISSAL OF ATTORNEY</option>
                  <option value="NOTICE AND REQUEST FOR ALLOWANCE OF LIEN">Notice of Lien Request</option>
                  <option value="NOTICE OF LIEN">NOTICE OF LIEN</option>
                  <option value="PROOF OF SERVICE">Proof of Service</option>
                  <option value="SUBSTITUTION OF ATTORNEY">SUBSTITUTION OF ATTORNEY</option>
                </select>&nbsp;required
              </td>
            </tr>
            <tr>
                <th width="18%" align="left" valign="top" nowrap="nowrap" scope="row" class="doi_cell">DOI:</th>
                <td width="82%" valign="top" class="doi_cell">
                	<div id="show_all_adjs_holder" style="float:right; display:none; margin-right:65px">
                    	<input id="show_all_adjs" name="show_all_adjs" type="checkbox" value="Y" />&nbsp;Show All ADJs
                    </div>
                  <select style="width:250px" class="modalInput" id="doi" name="doi" required>
                        <option value="">Select a DOI from List</option>
                        <%=dois %>
                    </select>&nbsp;<span style="font-size:0.7em; font-style:italic">required</span>
                </td>
            </tr>
            <tr>
                <th width="18%" align="left" valign="top" nowrap="nowrap" scope="row">Examiner:</th>
                <td width="82%" valign="top">
                  <select style="width:418px" class="modalInput" id="adjuster" name="adjuster">
                        <option value="">Select an Examiner from List</option>
                        <%=examiners %>
                    </select>
                </td>
            </tr>
            <tr>
                <th width="18%" align="left" valign="top" nowrap="nowrap" scope="row">Carrier:</th>
                <td width="82%" valign="top">
                  <select style="width:418px" class="modalInput" id="carrier" name="carrier">
                        <option value="">Select a Carrier from List</option>
                        <%=carriers %>
                    </select>
                </td>
            </tr>
            <tr>
                <th width="18%" align="left" valign="top" nowrap="nowrap" scope="row">Employer:</th>
                <td width="82%" valign="top">
                  <select style="width:418px" class="modalInput" id="employer" name="employer">
                        <option value="">Select an Employer from List</option>
                        <%=employers %>
                    </select>
                </td>
            </tr>
            <tr>
                <th width="18%" align="left" valign="top" nowrap="nowrap" scope="row">Defense Attorney:</th>
                <td width="82%" valign="top">
                  <select style="width:418px" class="modalInput" id="defense" name="defense">
                        <option value="">Select an Defense Attorney from List</option>
                        <%=defenses %>
                    </select>
                </td>
            </tr>
            <tr>
                <th width="18%" align="left" valign="top" nowrap="nowrap" scope="row">Primary Physician:</th>
                <td width="82%" valign="top">
                  <select style="width:418px" class="modalInput" id="primary" name="primary">
                        <option value="">Select a Primary Physician from List</option>
                        <%=medical_providers %>
                    </select>
                </td>
            </tr>
            <tr>
                <th width="18%" align="left" valign="top" nowrap="nowrap" scope="row">Lien Holder:</th>
                <td width="82%" valign="top">
                  <select style="width:418px" class="modalInput" id="lien_holder" name="lien_holder">
                        <option value="">Select a Lien Holder</option>
                        <%=lien_holders %>
                    </select>
                </td>
            </tr>
            <tr>
                <th width="18%" align="left" valign="top" nowrap="nowrap" scope="row">Referral:</th>
                <td width="82%" valign="top">
                  <select style="width:418px" class="modalInput" id="referral" name="referral">
                        <option value="">Select a Referral</option>
                        <%=referrals %>
                    </select>
                </td>
            </tr>
            <tr>
                <th width="18%" align="left" valign="top" nowrap="nowrap" scope="row">Letter Text:</th>
                <td width="82%" valign="top">
                    <textarea style="width:418px" class="modalInput" id="eamsInput" name="eamsInput"></textarea>
                </td>
            </tr>
            <tr>
                <th width="18%" align="left" valign="top" nowrap="nowrap" scope="row">
                	Appt Date/Time:
                   	<br />
                    <span style="font-weight:100; font-size:0.7em; font-style:italic">If applicable</span>
                </th>
                <td width="82%" valign="top">
                    <input type="date" class="modalInput" id="appointment_date" name="appointment_date" />
                    &nbsp;
                    <input type="time" placeholder="hh:mm" class="modalInput" id="appointment_time" name="appointment_time" />
                </td>
            </tr>
            <tr>
              <th align="left" valign="top" nowrap="nowrap" scope="row">Judge: <br />
              <span style="font-weight:100; font-size:0.7em; font-style:italic">If applicable</span></th>
              <td valign="top"><input type="text" style="width:418px" class="modalInput" id="appointment_judge" name="appointment_judge" /></td>
            </tr>
        </table>
    </div>
	<div style="width:600px; display:none; float:right" id="lien_form">
		The lien claimant hereby requests the Workers' Compensation Appeals Board to determine and allow as a lien the sum of $<input type="text" style="200" />
against any amount now due or which may hereafter become payable as compensation to the above-named employee on account of the above-claimed injury.<br/><br/>

<input type="checkbox" />A reasonable attorney's fee for legal services pertaining to any claim for compensation either before the appeals board or before any of the appellate courts, and the reasonable disbursements in connection therewith. (Labor Code § 4903 (a).)<br/>
<input type="checkbox" />The reasonable expense incurred by or on behalf of the injured employee, as provided by Labor Code § 4600. (Labor Code § 4903 (b).)<br/>
<input type="checkbox" />Reasonable expense incurred by or on behalf of the injured employee for medical-legal expenses. (Labor Code § 4903 (b).)<br/>
<input type="checkbox" />The reasonable value of the living expenses of an injured employee or of his or her dependents, subsequent to the injury. (Labor Code § 4903 (c).)<br/>
<input type="checkbox" />The reasonable burial expenses of the deceased employee. (Labor Code § 4903 (d).)<br/>
<input type="checkbox" />The reasonable living expenses of the spouse or minor children of the injured employee, or both, subsequent to the date of the injury, where the employee has deserted or is neglecting his or her family. (Labor Code § 4903 (e).)<br/>
<input type="checkbox" />The reasonable fee for interpreter's services performed on<br/>
<input type="checkbox" />The amount of indemnification granted by the California Victims of Crime Program. (Labor Code § 4903 (i).)<br/>
<input type="checkbox" />The amount of compensation, including expenses of medical treatment, and recoverable costs that have been paid by the Asbestos Workers' Account. (Labor Code § 4903 (j).)<br/>
<input type="checkbox" />Other Lien(s): Specify nature and statutory basis
	</div>
</form>
</div>
<div id="billing_holder" style="display:none; padding-right:15px"></div>
<div id="eams_view_all_done"></div>
<script language="javascript">
$( "#eams_view_all_done" ).trigger( "click" );
</script>
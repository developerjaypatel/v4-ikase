<% if (blnShowBilling) { %>
<div style="margin-top:-10px; padding-bottom:5px">
	<input type="button" class="btn btn-xs" id="view_letter" value="Letter" style="display:none">
    <% if (id != "-1") { %>
	<input type="button" class="btn btn-xs" id="view_billable" value="Bill This" title="Click to Create/Update Billing related to this Letter">
    <div style="display:none" id="cancel_billable_holder">
    	&nbsp;
    	<input type="button" class="btn btn-xs btn-warning" id="cancel_billable" value="Cancel Bill" title="Click to Clear Billing related to this Letter">
    </div>
    <% } %>
</div>
<% } %>
<div class="letter" id="letter_div" style="margin-left:5px">
<form id="letter_form" class="letter" parsley-validate>
    <input id="table_name" name="table_name" type="hidden" value="letter" />
    <input id="table_id" name="table_id" type="hidden" value="<%=id %>" />
    <input id="case_id" name="case_id" type="hidden" value="<%=case_id %>" />
    <input id="partie_ids" name="partie_ids" type="hidden" value="" />
    <input id="any_ids" name="any_ids" type="hidden" value="" />
    <div style="width:350px; height:500px; display:none; float:right;" id="letter_parties_list_holder">
    	<div style="float:right">
            <!--<input type="checkbox" class="event_parties" id="event_parties" value="Y" title="Select All" />-->
            <input type="hidden" name="partie_count" id="partie_count" value="<%=parties.split('\r\n').length %>" />
        </div>
        Parties <span style="font-size:0.8em">(Select from List to set Parties for Letter)</span>
        <div style="border:1px solid white; height:450px; overflow-y:scroll" id="letter_parties_list">
        </div>
    </div>
    <div style="float:right; width:300px; margin-right:10px; border:0px solid yellow;" id="middle_section_holder">
		<div id="letter_text_holder">
            Letter Text:&nbsp;&nbsp;
            <textarea style="width:100%;height:150px;" class="modalInput" id="letterInput" name="letterInput"></textarea>
            
            <hr />
        </div>
        <div id="deposition_details" style="display:none">
            <div>
            	<label for="depo_dateandtime" style="width:125px; display:inline-block">Depo Date/Time:</label>
                <input type="text" id="depo_dateandtime" name="depo_dateandtime" placeholder="mm/dd/yyyy h:mA" style="width:418px" class="depo_date depo_field" />
            </div>
            <div style="margin-top:3px">
            	<label for="depo_arrival_time" style="width:125px; display:inline-block">Depo Arrival Time:</label>
                <input type="text" id="depo_arrival_time" name="depo_arrival_time" placeholder="mm/dd/yyyy h:mA" style="width:418px" class="depo_date depo_field" />
            </div>
            <div style="margin-top:3px" id="depo_location_holder">
            	<div>
                    <label for="depo_location" style="width:125px; display:inline-block">Depo Location:</label>
                    <input type="text" id="depo_location" name="depo_location" placholder="Location Name" style="width:418px" class="depo_field" />
                </div>
                <div>
                    <label for="depo_address" style="width:125px; display:inline-block">Address:</label>
                    <textarea id="depo_address" name="depo_address" placholder="Location Address" style="width:418px" class="depo_field"></textarea>
                </div>
            </div>
            <div style="margin-top:3px">
            	<label for="attorney_depo" style="width:125px; display:inline-block">Atty @ Depo:</label>
                <select id="depo_attorney" name="depo_attorney" style="width:418px" class="depo_field">
                	<option value="" selected="selected">Select from List</option>
                    <% if (attorney_name!="" && attorney_name!="<span style='font-size:0.7em'>no atty</span>") { %>
                    <option value="<%=attorney_name %>"><%=attorney_full_name %></option>
                    <% } %>
                    <% if (supervising_attorney_name!="") { %>
                    <option value="<%=supervising_attorney_name %>"><%=supervising_attorney_full_name %></option>
                    <% } %>
                </select>
            </div>
            <div style="margin-top:3px">
            	<label for="depo_bill_dated" style="width:125px; display:inline-block">Bill Dated:</label>
                <input type="text" id="depo_bill_dated" name="depo_bill_dated" placeholder="mm/dd/yyyy" style="width:418px" class="depo_field" />
            </div>
            <div style="margin-top:3px; text-align:center">
            	<div style="display:inline-block; width:95px">
                <label for="depo_preparation" style="width:65px; display:inline-block">Prep Time:</label>
                <input type="number" id="depo_preparation" name="depo_preparation" placeholder="mins" style="width:50px" />
                </div>
                <div style="display:inline-block; width:95px">
                <label for="depo_amount_billed" style="width:65px; display:inline-block">Amt Billed:</label>
                <input type="number" id="depo_amount_billed" name="depo_amount_billed" placeholder="$" style="width:50px" />
                </div>
                <div style="display:inline-block; width:95px">
                <label for="depo_atty_fee" style="width:65px; display:inline-block">Atty Fee:</label>
                <input type="number" id="depo_atty_fee" name="depo_atty_fee" placeholder="$" style="width:50px" />
                </div>
            </div>
        </div>
        <div id="invoices_listing_holder" style="margin-top:25px"></div>
	</div>
    <table width="610" border="0" align="left" cellpadding="3" cellspacing="0">
    	<!--
        <tr>
            <th width="18%" align="left" valign="top" nowrap="nowrap" scope="row" colspan="3" style="background:red; color:white">DO NOT USE - UNDER REPAIRS 12/13/2017</th>
        </tr>
        -->
        <tr>
            <th width="18%" align="left" valign="top" nowrap="nowrap" scope="row">Template:</th>
            <td colspan="2" valign="top">
           	  <%=template_description %>
              <input type="hidden" id="template_name" value="<%=document_name %>" />
            </td>
        </tr>
        <tr>
            <th width="18%" align="left" valign="top" nowrap="nowrap" scope="row">Case:</th>
            <td colspan="2" valign="top">
            	<div style="float:right"><%=attorney_name %> <%=supervising_attorney_name %> <%=worker_name %></div>
           	  <%=case_name %>
            </td>
        </tr>
        <tr>
          <th align="left" valign="top" nowrap="nowrap" scope="row">Applicant:</th>
          <td width="33%" valign="top">
          	<span id="applicant_holder">
            	<%=applicant_full_name %>
            </span>
            <div id="applicant_search_holder" style="position:absolute; z-index:9999; background:white;"></div>
          </td>
          <td width="49%" valign="top">
          	<div style="float:left;" id="import_matrix_data_holder">
            	<button id="search_matrix" title="Click to search Matrix for this Applicant" class="btn btn-xs" style="linear-gradient(to bottom,#6a42ca 0,#2d31a2 100%); border-color: #2b399a;">Search Matrix</button>
            </div>
          	<div id="applicant_partie_holder" style="padding-left:230px"><input type="checkbox" class="parties_option" id="parties_applicant" name="parties_applicant" value="Y" title="Check this box to add Applicant to Parties listed in letter" /></div>
          </td>
        </tr>
        <tr>
          <th align="left" valign="top" nowrap="nowrap" scope="row"><span style="font-weight:bold" id="last_date_holder">Appt Date & Time:</span></th>
          <td colspan="2" valign="top"><span style="font-weight:bold">
            <input type="text" name="last_date" id="last_date" />
          </span></td>
        </tr>
        <tr id="deposition_row" style="display:none">
          <th colspan="3" align="left" valign="top" nowrap="nowrap" scope="row">Deposition:
                <table width="50%" border="0" cellspacing="0" cellpadding="2">
                <tr>
                  <td width="112" style="margin-left:20px; font-weight:normal">Name or Party:</td>
                  <td width="400"><select style="width:418px" class="modalInput" id="deposition_party" name="deposition_party">
                	<option value="">Select a Party from List</option>
                    <%=parties %>
                </select></td>
                </tr>
                <tr>
                  <td style="margin-left:20px; font-weight:normal">Office:</td>
                  <td><input type="text" name="deposition_office" id="deposition_office" style="width:418px" /></td>
                </tr>
                <tr>
                  <td style="margin-left:20px; font-weight:normal">Location:</td>
                  <td>
                  	<textarea id="deposition_location" name="deposition_location" placholder="Location" style="width:418px"></textarea>
                    <!--<input type="text" name="deposition_location" id="deposition_location" style="width:418px" />-->
                  </td>
                </tr>
                <!--
                <tr>
                  <td style="margin-left:20px; font-weight:normal">Address</td>
                  <td>
                    <textarea id="deposition_address" name="deposition_address" placholder="Location Address" style="width:418px"></textarea>
                  </td>
                </tr>
                -->
                <tr>
                  <td style="margin-left:20px; font-weight:normal">County:</td>
                  <td><input type="text" name="deposition_county" id="deposition_county" style="width:418px" /></td>
                </tr>
                <tr>
                  <td style="margin-left:20px; font-weight:normal">Fee:</td>
                  <td><input type="text" name="deposition_fee" id="deposition_fee" style="width:418px" /></td>
                </tr>
                <tr>
                  <td style="margin-left:20px; font-weight:normal">Court Order Date</td>
                  <td><input type="date" name="deposition_court_order_date" id="deposition_court_order_date" style="width:418px" /></td>
                </tr>
              </table>
          </th>
        </tr>
        <tr>
        	<td colspan="3" id="any_list">
            </td>
        </tr>
        <tr class="partie_select_row">
          <th align="left" valign="top" nowrap="nowrap" scope="row"><span style="font-weight:bold" id="subject_holder">Subject:</span></th>
          <td colspan="2" valign="top">
            <input type="text" name="subject" id="subject" style="width:418px" />
          </td>
        </tr>
        <tr style="display:<% if (!blnWCAB) { %>none<% } %>" class="partie_select_row">
          <th align="left" valign="top" nowrap="nowrap" scope="row" class="doi_cell">Judge:</th>
          <td colspan="2" valign="top" class="judge_cell">
          	<input type="hidden" id="judge_dropdown" name="judge_dropdown" value="" />
          </td>
        </tr>
        <tr class="partie_select_row">
            <th width="18%" align="left" valign="top" nowrap="nowrap" scope="row" class="doi_cell">DOI:</th>
            <td colspan="2" valign="top" class="doi_cell">
            	<div style="float:right; margin-right:70px">
                	<a id="multiple_dois" class="white_text" style="cursor:pointer" title="Click to assign multiple DOIs to the letter">multiple</a>
                    <a id="single_dois" class="white_text" style="cursor:pointer; display:none" title="Click to return to single DOI functionality">single</a>
                </div>
                <select style="width:358px" class="modalInput" id="doi" name="doi" required>
                	
                    <%=dois %>
                </select>
                <% if (login_user_id=="75") { %>
                <div id="multiple_doi_instructions" style="display:none; font-style:italic; font-size:0.9em">Ctrl-Click to select multiple DOIs</div>
                <% } %>
            </td>
        </tr>
        <tr class="partie_select_row">
        	<td colspan="3" id="parties_list">
            </td>
        </tr>
        <tr class='partie_row partie_select_row'>
            <th width="18%" align="left" valign="top" nowrap="nowrap" scope="row">Examiner:</th>
            <td colspan="2" valign="top">
           	  <select style="width:418px" class="modalInput" id="adjuster" name="adjuster">
                	<option value="">Select an Examiner from List</option>
                    <%=examiners %>
                </select>
            </td>
        </tr>
        <tr class='partie_row carrier_row'>
            <th width="18%" align="left" valign="top" nowrap="nowrap" scope="row">Carrier:</th>
            <td colspan="2" valign="top">
           	  <select style="width:405px" class="modalInput invoice_firm_select" id="carrier" name="carrier">
                	<option value="">Select a Carrier from List</option>
                    <%=carriers %>
                </select>&nbsp;<input type="checkbox" class="parties_option" id="parties_carrier" name="parties_carrier" value="Y" title="Check this box to add Carrier to Parties listed in letter" />
            </td>
        </tr>
        <tr class='partie_row partie_select_row'>
            <th width="18%" align="left" valign="top" nowrap="nowrap" scope="row">Employer:</th>
            <td colspan="2" valign="top">
           	  <select style="width:405px" class="modalInput" id="employer" name="employer">
                	<option value="">Select an Employer from List</option>
                    <%=employers %>
                </select>&nbsp;<input type="checkbox" class="parties_option" id="parties_employer" name="parties_employer" value="Y" title="Check this box to add Employer to Parties listed in letter" />
            </td>
        </tr>
        <tr class='partie_row defense_row'>
            <th width="18%" align="left" valign="top" nowrap="nowrap" scope="row">Defense Attorney:</th>
            <td colspan="2" valign="top">
           	  <select style="width:405px" class="modalInput invoice_firm_select" id="defense" name="defense">
                	<option value="">Select an Defense Attorney from List</option>
                    <%=defenses %>
                </select>&nbsp;<input type="checkbox" class="parties_option" id="parties_defense" name="parties_defense" value="Y" title="Check this box to add Defense Attorney to Parties listed in letter" />
            </td>
        </tr>
        <% if (law_enforcements.length > 0) { %>
        <tr class='partie_row partie_select_row'>
            <th width="18%" align="left" valign="top" nowrap="nowrap" scope="row">Police:</th>
            <td colspan="2" valign="top">
           	  <select style="width:405px" class="modalInput" id="law_enforcement" name="law_enforcement">
                	<option value="">Select a Police Station from List</option>
                    <%=law_enforcements %>
                </select>&nbsp;<input type="checkbox" class="parties_option" id="parties_law_enforcement" name="parties_law_enforcement" value="Y" title="Check this box to add Police Station to Parties listed in letter" checked="checked" />
            </td>
        </tr>
        <% } %>
        <% if (defendants.length > 0) { %>
        <tr class='partie_row partie_select_row'>
            <th width="18%" align="left" valign="top" nowrap="nowrap" scope="row">Defendant:</th>
            <td colspan="2" valign="top">
           	  <select style="width:405px" class="modalInput" id="defendant" name="defendant">
                	<option value="">Select a Defendant from List</option>
                    <%=defendants %>
                </select>&nbsp;<input type="checkbox" class="parties_option" id="parties_defendant" name="parties_defendant" value="Y" title="Check this box to add Defendant to Parties listed in letter" />
            </td>
        </tr>
        <% } %>
        <% if (witnesses.length > 0) { %>
        <tr class='partie_row partie_select_row'>
            <th width="18%" align="left" valign="top" nowrap="nowrap" scope="row">Witness:</th>
            <td colspan="2" valign="top">
           	  <select style="width:405px" class="modalInput" id="witness" name="witness">
                	<option value="">Select a Witness from List</option>
                    <%=witnesses %>
                </select>&nbsp;<input type="checkbox" class="parties_option" id="parties_witness" name="parties_witness" value="Y" title="Check this box to add Police Station to Parties listed in letter" checked="checked" />
            </td>
        </tr>
        <% } %>
        <% if (uefs.length > 0) { %>
        <tr class='partie_row partie_select_row'>
            <th width="18%" align="left" valign="top" nowrap="nowrap" scope="row">UEF Attorney:</th>
            <td colspan="2" valign="top">
           	  <select style="width:405px" class="modalInput" id="uef" name="uef">
                	<option value="">Select an UEF Attorney from List</option>
                    <%=uefs %>
                </select>&nbsp;<input type="checkbox" class="parties_option" id="parties_uef" name="parties_uef" value="Y" title="Check this box to add UEF Attorney to Parties listed in letter" />
            </td>
        </tr>
        <% } %>
        <tr class='partie_row partie_select_row'>
            <th width="18%" align="left" valign="top" nowrap="nowrap" scope="row">Primary Physician:</th>
            <td colspan="2" valign="top">
           	  <select style="width:405px" class="modalInput" id="primary" name="primary">
                	<option value="">Select a Primary Physician from List</option>
                    <%=medical_providers %>
                </select>&nbsp;<input type="checkbox" class="parties_option" id="parties_primary" name="parties_primary" value="Y" title="Check this box to add Primary Physician to Parties listed in letter" />
            </td>
        </tr>
        <tr class='partie_row partie_select_row'>
            <th width="18%" align="left" valign="top" nowrap="nowrap" scope="row">Lien Holder:</th>
            <td colspan="2" valign="top">
           	  <select style="width:405px" class="modalInput" id="lien_holder" name="lien_holder">
                	<option value="">Select a Lien Holder</option>
                    <%=lien_holders %>
                </select>&nbsp;<input type="checkbox" class="parties_option" id="parties_lien_holder" name="parties_lien_holder" value="Y" title="Check this box to add Lien Holder to Parties listed in letter" />
            </td>
        </tr>
        <tr class='partie_row partie_select_row'>
            <th width="18%" align="left" valign="top" nowrap="nowrap" scope="row">Referral:</th>
            <td colspan="2" valign="top">
           	  <select style="width:418px" class="modalInput" id="referral" name="referral">
                	<option value="">Select a Referral</option>
                    <%=referrals %>
                </select>
            </td>
        </tr>
        <tr class="fax_row partie_select_row">
          <th align="left" valign="top" nowrap="nowrap" scope="row" id="pages_label_holder">Pages:</th>
          <td colspan="2" valign="top" id="pages_input_holder">
          	<input type="text" name="pages" id="pages" size="3" />
            <input type="checkbox" name="rush" id="rush" value="Y" style="display:none" />
          </td>
        </tr> 
        <tr class="matrix_info_row" style="display:none">
          <td colspan="3" valign="top" id="matrix_info_holder">
          </td>
        </tr>    
        <% if (document_extension == "Invoice") { %>
        <tr class='partie_row'>
            <td colspan="4" valign="top">
           	  <hr />
              <span style="font-weight:bold; font-size:1.2em">Invoice Details</span>
            </td>
        </tr>
        <tr class='partie_row'>
        	<td colspan="4" valign="top" align="left">
                <table width="534px" id="invoice_items_table" style="min-height:150px">
                    <tr>
                        <td align="left" colspan="3">
                            <div style="position:absolute; left:500px; margin-top:23px; vertical-align:top">
                            	<!--<button class="btn btn-xs btn-primary" id="invoiced_by">Invoiced By ...</button>-->
                                <div>
                                	<div id="invoice_number_holder"></div>
                                    <span style="font-weight:bold">Invoiced Firm</span>
                                    <br />
                                    <span class="invoiced_firm_span invoiced_firm_defense">
                                    	<input name="invoiced_firm" type="radio" id="invoiced_firm_defense" class="invoiced_firm invoiced_firm_defense modal_input" value="D" checked="checked" />&nbsp;Defense Attorney
                                    </span>
                                    &nbsp;&nbsp;&nbsp;&nbsp;
                                    <span class="invoiced_firm_span invoiced_firm_carrier">
                                    	<input name="invoiced_firm" type="radio" id="invoiced_firm_carrier" class="invoiced_firm invoiced_firm_carrier modal_input" value="C" />&nbsp;Carrier
                                    </span>
                                </div>
                                <div id="employee_invoice_holder">
                                    <span style="font-weight:bold">Invoiced By ...</span>
                                    <br />
                                    <input name="assigneeInput" type="text" id="assigneeInput" style="width:150px" class="modal_input" value="" />
                                </div>
                                <div id="additional_invoice_questions">
                                    <div>
                                        <input type="radio" name="kinvoice_type" id="kinvoice_type_invoice" value="I" checked="checked" /> <a id="invoice_link" class="white_text">Invoice</a> | <input type="radio" name="kinvoice_type" id="kinvoice_type_pre" value="P" /> <a id="pre_bill_link" class="white_text">Pre-Bill</a>
                                    </div>
                                    <div id="transfer_trust_funds" style="display:none" title="Do you want to transfer the invoice total from the Trust Account?
If Yes, the funds will be transferred per the invoice.
If No, you will be able to confirm transfer later">
                                        <input type="checkbox" id="transfer_funds" value="Y" /> <a id="transfer_funds_link" class="white_text">Transfer funds from Trust Account</a>
                                    </div>
                                </div>
                            </div>
                            DEPO BILLING / INVOICE $<span id="hourly_rate"></span> per hour
                            <input type="hidden" id="kinvoice_id" value="" />
                            <input type="hidden" id="kinvoice_document_id" value="" />
                            <input type="hidden" id="kinvoice_number" value="" />
                        </td>
                    </tr>
                    <tr>
                        <th width="20%" align="left" valign="top" nowrap="nowrap" scope="row">Item</th>
                        <th width="20%" align="left" valign="top" nowrap="nowrap" scope="row">Hours</th>
                        <th width="20%" align="left" valign="top" nowrap="nowrap" scope="row">Qty</th>
                        <th width="60%" align="left" valign="top" nowrap="nowrap" scope="row">Amount</th>
                    </tr>
                    
                </table>
            </td>
        </tr>
        <!--
        <tr class='partie_row partie_select_row'>
            <th width="18%" align="left" valign="top" nowrap="nowrap" scope="row">Travel Time:</th>
            <td colspan="2" valign="top">
           	  <input type="text" name="travel_time" id="travel_time" value="" style="width:50px" />&nbsp;Hrs&nbsp;&nbsp;&nbsp;&nbsp;<strong>Parking Fee:</strong> &nbsp;$<input type="text" name="parking_fee" id="parking_fee" value="" style="width:50px" />&nbsp;&nbsp;&nbsp;&nbsp;<strong>Applicant Prep:</strong> &nbsp;<input type="text" name="applicant_prep" id="applicant_prep" value="" style="width:50px" />&nbsp;Hrs
            </td>
        </tr>
        <tr class='partie_row partie_select_row'>
            <th width="18%" align="left" valign="top" nowrap="nowrap" scope="row">Deposition Time:</th>
            <td colspan="2" valign="top">
           	<input type="text" name="depo_time" id="depo_time" style="width:75px" />  
            </td>
        </tr>
        <tr class='partie_row partie_select_row'>
            <th width="18%" align="left" valign="top" nowrap="nowrap" scope="row">Review Depo w/ Client:</th>
            <td colspan="2" valign="top">
           	<input type="text" name="review_depo" id="review_depo" style="width:75px" />
            </td>
        </tr>
        <tr class='partie_row partie_select_row'>
            <th width="18%" align="left" valign="top" nowrap="nowrap" scope="row">Mileage:</th>
            <td colspan="2" valign="top">
           	<input type="text" name="milage" id="milage" style="width:75px" />  
            </td>
        </tr>
        <tr class="fax_row">
          <th align="left" valign="top" nowrap="nowrap" scope="row" id="pages_label_holder">TOTAL HOURS:</th>
          <td colspan="2" valign="top" id="pages_input_holder">
          	<input type="text" name="total_hours" id="total_hours" style="width:100px" />
          </td>
        </tr>
        -->
        <% } %>
	</table>
</form>
</div>
<div id="billing_holder" style="display:none; padding-right:15px"></div>
<div id="letter_view_all_done"></div>
<script language="javascript">
$( "#letter_view_all_done" ).trigger( "click" );
</script>
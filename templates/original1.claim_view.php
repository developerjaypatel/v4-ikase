<% if (embedded) { %>
<div style="font-size:1.25em" id="ssn_claim_title">
	SSN Claim
</div>
<% } else { %>
<div style="margin-top:0px; margin-right:10px; padding-top:5px">            
	<?php 
    $form_name = "ssn_claim";
    //$kase_type_pi_confirm = "yes"; 
    include("dashboard_view_navigation.php"); 
    ?>
</div>
<% } %>
<div id="new_surgery_holder" style="position: absolute;
    margin-top: -55px;
    margin-left: 230px;">
	<button id="new_surgery" class="btn btn-sm btn-primary">Add Surgery</button>
</div>
<form id="claim_form">
	<input type="hidden" id="claim_id" name="claim_id" value="" />
	<table width="97%" id="claim_view_table">
        <tr>
          <th align="left" valign="top">DOI</th>
          <td align="left" valign="top"><input type="text" id="claim_doi" name="claim_doi" class="claim_date claim_sync" style="width:75px" /></td>
          <td align="left" valign="top" nowrap="nowrap">
          	<label style="width:70px; display:inline-block">Disabililty</label><input id="claim_disability" name="claim_disability" style="width:205px" class="claim_small_input" />
          </td>
        </tr>
        <tr>
          <th colspan="3" align="right" valign="top">
              <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td align="left" valign="top" nowrap="nowrap"><label style="width:105px; display:inline-block">Occupation: </label>
                    <input type="text" id="claim_occupation" name="claim_occupation" class="claim_sync claim_small_input" style="width:175px" /></td>
                  <td align="center" valign="top" nowrap="nowrap"><label style="text-align:left; display:inline-block">Last Work Date: </label>
                    <input type="text" id="last_date_worked" name="last_date_worked" class="claim_date" style="width:75px" /></td>
                  <td align="right" valign="top" nowrap="nowrap"><label style="padding-right:5px; text-align:left; display:inline-block">5 of 10:</label>
                    <input type="checkbox" name="five_ten" id="five_ten" /></td>
                </tr>
              </table>
          </th>
        </tr>
        <tr>
            <th width="10%" align="left" valign="top">
                Benefits
            </th>
            <td align="left" valign="top">
                <select name="claim_benefits" id="claim_benefits">
                    <option value="" selected="selected">Select from List ...</option>
                    <option value="SDI">SDI</option>
                    <option value="GR">GR</option>
                    <option value="None">None</option>
                    <option value="Other">Other</option>
                </select>
            </td>
            <td align="left" valign="top"><input id="claim_benefits_other" name="claim_benefits_other" style="width:257px; display:none" placeholder="Other Benefits Description" /></td>
        </tr>
        <tr>
          <th colspan="3" align="left" valign="top">
          	<table width="100%" border="0" cellspacing="0" cellpadding="0" align="left">
                <tr>
                  <td align="left" nowrap="nowrap"><label style="width:105px; display:inline-block">Start Date: </label>                <input type="text" id="claim_benefits_date" name="claim_benefits_date" class="claim_date" style="width:75px" /></td>
                  <td align="center" nowrap="nowrap"><label style="width:70px; text-align:left; display:inline-block">Denial Date: </label>                <input type="text" id="claim_benefits_denial" name="claim_benefits_denial" class="claim_date" style="width:75px" /></td>
                  <td align="right" nowrap="nowrap"><label style="width:60px; text-align:left; display:inline-block">Amount: </label>                <input type="number" id="claim_benefits_amount" name="claim_benefits_amount" style="width:75px" /></td>
                </tr>
              </table>
          </th>
        </tr>
        <tr>
            <th align="left" valign="top">
                Type
            </th>
            <td align="left" valign="top" nowrap="nowrap">
                <select name="claim_type" id="claim_type" class="other_select">
                    <option value="" selected="selected">Select from List ...</option>
                    <option value="SSI">SSI</option>
                    <option value="DIB">DIB</option>
                    <option value="Dependents">Dependents</option>
                    <option value="Widow">Widow</option>
                    <option value="Other">Other</option>
                </select>
            </td>
            <td align="left" valign="top" nowrap="nowrap"><input id="claim_type_other" name="claim_type_other" style="width:257px; display:none" placeholder="Other Type Description" /></td>
        </tr>
        <tr>
            <th rowspan="2" align="left" valign="top" nowrap="nowrap">
                Prior Application
            </th>
            <td width="38%" align="left" valign="top">
                <input type="checkbox" name="prior_app" id="prior_app" />
                <label for="prior_app">Yes</label>
                <div></div>
                <div></div>
            </td>
            <td width="52%" align="left" valign="top"><label style="padding-right:5px; display:inline-block">Year:</label>
            <input type="number" name="prior_year" id="prior_year" style="width:50px" /></td>
        </tr>
        <tr>
          <td colspan="2" align="left" valign="top">
              <label style="padding-right:5px; display:inline-block">Outcome:</label>
              <input name="claim_outcome" id="claim_outcome" type="text" style="width:365px" class="claim_small_input" />
          </td>
        </tr>
        <tr>
            <th align="left" valign="top">
                Stage
            </th>
            <td align="left" valign="top">
                <select name="claim_stage" id="claim_stage" class="other_select">
                    <option value="" selected="selected">Select from List ...</option>
                    <option value="Initial">Initial</option>
                    <option value="Req for Recon">Req for Recon</option>
                    <option value="Hearing">Hearing</option>
                    <option value="Other">Other</option>
                </select>
            </td>
            <td align="left" valign="top"><input id="claim_stage_other" name="claim_stage_other" style="width:257px; display:none" placeholder="Other Stage Description" /></td>
        </tr>
        <tr>
            <th align="left" valign="top">&nbsp;
                
            </th>
            <td colspan="2" align="left" valign="top">
            	<div style="float:right">
                	<label style="width:60px; display:inline-block">Status:</label>
                    <select id="wc_status" name="wc_status">
                    	<option value="" selected="selected">Select from List ...</option>
                        <option value="P+S">P&amp;S</option>
                        <option value="TTD">TTD</option>
                        <option value="Rehab">Rehab</option>
                        <option value="Other">Other</option>
                    </select>
                    <input id="wc_status_other" name="wc_status_other" style="width:257px; display:none" placeholder="Other Status Description" />
                </div>
                <input type="checkbox" name="work_related" id="work_related" />
                <label for="work_related">Work Related</label>
                &nbsp;&nbsp;
                <input type="checkbox" name="wc_claim" id="wc_claim" />
                <label for="wc_claim">WC Claim</label>
                <div>
                	<div style="float:right">
                    	<label style="padding-right:5px; display:inline-block">PD Pct:</label> <input type="number" id="claim_pdpct" name="claim_pdpct" style="width:50px" />
                    </div>
                    <input type="checkbox" name="wc_settled" id="wc_settled" />
                    <label for="wc_settled">Settled</label>
                </div>
            </td>
        </tr>
        <tr>
            <th align="left" valign="top" nowrap="nowrap">
                Doctor Specialties
            </th>
            <td colspan="2" align="left" valign="top">
            	<div style="float:right">
                	<label style="width:70px; display:inline-block">Frequency:</label> <input type="text" id="claim_frequency" name="claim_frequency" style="width:205px" class="claim_small_input" />
                </div>
                <textarea id="claim_specialties" name="claim_specialties" style="width:431px" class="claim_input" cols="3" placeholder="Doctor Specialties"></textarea>
            </td>
        </tr>
        <!--
        <tr>
            <th align="left" valign="top">
                Surgeries
            </th>
            <td align="left" valign="top"><input type="text" id="claim_surgeries_number" name="claim_surgeries_number" placeholder="Number of Surgeries" />
                <div></div>
            </td>
            <td align="right" valign="top"><label style="width:60px; display:inline-block">When:</label>
            <input type="text" id="claim_surgeries_when" name="claim_surgeries_when" style="width:205px" class="claim_small_input" /></td>
        </tr>
        -->
        <tr>
            <th align="left" valign="top">
                Medications
            </th>
            <td colspan="2" align="left" valign="top">
                <textarea id="claim_medications" name="claim_medications" style="width:431px" class="claim_input" cols="3"></textarea>
            </td>
        </tr>
        <tr>
            <th align="left" valign="top">
                Comments
            </th>
            <td colspan="2" align="left" valign="top">
                <textarea id="claim_comments" name="claim_comments" class="claim_sync claim_input" style="width:431px" cols="3"></textarea>
            </td>
        </tr>
    </table>
</form>    
<div id="claim_all_done"></div>
<script language="javascript">
$("#claim_all_done").trigger( "click" );
</script>
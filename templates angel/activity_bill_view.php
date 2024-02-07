<form id="billing_form" class="activity_bill" parsley-validate>
    <input type="button" class="btn btn-xs" style="float:right; cursor:pointer; margin-right:-10px; display:none" id="save_billing_modal" value="Save" />
    <input id="table_name" name="table_name" type="hidden" value="activity_bill" />
    <input id="table_id" name="table_id" type="hidden" value="" />
    <input id="table_uuid" name="table_uuid" type="hidden" value="" />
    <input id="billing_id" name="billing_id" type="hidden" value="" />
    <input id="case_id" name="case_id" type="hidden" value="" />
    <input id="action_id" name="action_id" type="hidden" class="activity_bill billing_form" value="<%=id %>" />
    <input id="modal_type" name="modal_type" type="hidden" class="activity_bill billing_form" value="Activity" />
	<table width="430px" border="0" align="left" cellpadding="3" cellspacing="0" style="display:" id="billing_table">
        <tr>
          <td colspan="2" align="left" valign="top" scope="row">
            <div style="float:right; margin-right:2px">
            	<span style="font-weight:bold">Type:</span><br />
	            <select id="statusInput" name="statusInput" class="activity_bill billing_form" value="" style="width:80px" tabindex="2">
                	<option value="Hourly" selected="selected">Hourly</option>
                    <option value="Cost">Cost</option>
                </select>	
            </div>
            <span style="font-weight:bold">Billing Date:</span><br />
            <input type="input" name="billing_dateInput" id="billing_dateInput" class="activity_bill billing_form" value="" style="width:80px" tabindex="1" />
          </td>
        </tr>
        <tr>
          <td colspan="2" align="left" valign="top" scope="row">
            <span style="font-weight:bold">Billing Employee:</span><br />
            <input type="input" name="timekeeperInput" id="timekeeperInput" class="activity_bill billing_form" value="" style="width:330px" tabindex="3" />
          </td>
        </tr>
        <tr class="" style="display:">
          <td colspan="2" align="left" valign="top" scope="row">
            <span style="font-weight:bold">Activity:</span><br />
            <input type="input" name="activity_codeInput" id="activity_codeInput" class="activity_bill billing_form" value="<%=activity_category %>" style="width:330px" autocomplete="off" tabindex="4" />
            <div>
                <select id="activity_categories" style="display:none; position:absolute; z-index:9999" size="5">
                <%=filters %>
                </select>
            </div>
          </td>
        </tr>
        <tr height="30" valign="middle">
          <td colspan="2" align="left" valign="top">
          	<div style="display:none" class="billing_type_holder cost_holder">
            	<div style="float:right; padding-right:2px">
                	<span style="font-weight:bold">Billing Rate:</span>
                    <br />
                    $<input type="number" value="<%=billing_rate %>" id="billing_rateInput" class="activity_bill billing_form" style="width:68px" tabindex="7" /> per <input type="text" value="<%=billing_unit %>" id="unit_nameInput" class="activity_bill billing_form" style="width:78px" tabindex="7" placeholder="Unit" />
                </div>
            	<span style="font-weight:bold">Units:</span>
                <br />
                <input type="number" value="<%=billing_amount %>" id="unitsInput" class="activity_bill billing_form" placeholder="" style="width:68px" tabindex="6" />
            </div>
            <div id="hourly_holder" style="display:block" class="billing_type_holder">
                <span style="font-weight:bold">Hours:</span>
                <br />
                <input type="number" value="<%=hours %>" name="durationInput" id="durationInput" class="activity_bill billing_form" placeholder="" style="width:68px" tabindex="5" />
            </div>
          </td>
        </tr>
        <tr height="30" valign="middle">
        	<td colspan="2" align="left" valign="top" style="font-size:0.9em; font-style:italic">
            The activity can be billed by the hour OR billed by the unit
            <br />
            (Ex: 3 hours OR 6 miles @ 0.25 per Mile)
            </td>
        </tr>
        <tr height="30" valign="middle">
        	<td colspan="2" align="left" valign="top">
                <strong>Description :</strong>
                <br />
                <textarea name="descriptionInput" type="text" id="descriptionInput" style="width:333px; height:253px" tabindex="8" class="modalInput task input_class"><%=activity %></textarea></td>
        </tr>
   </table>
</form>
<div id="activity_bill_all_done"></div>
<script language="javascript">
$( "#activity_bill_all_done" ).trigger("click");
</script>
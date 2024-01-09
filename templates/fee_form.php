<div class="fee" style="margin-left:10px">
    <form id="fee_form" method="post" action="">
        <input id="table_name" name="table_name" type="hidden" value="fee" />
        <input id="table_id" name="table_id" type="hidden" value="<%=id %>" />
        <input id="parent_table_id" name="parent_table_id" type="hidden" value="<%=parent_table_id %>" />
        <input id="case_id" name="case_id" type="hidden" value="<%=case_id %>" />
        <input id="injury_id" name="injury_id" type="hidden" value="<%=injury_id %>" />
        <input id="settlement_id" name="settlement_id" type="hidden" value="<%=settlement_id %>" />
        <table align="left" width="550" border="0" bordercolor="#dddddd" cellspacing="0" cellpadding="3">
          <tbody>
            <tr height="30" valign="middle">
              <td align="left" valign="top" nowrap=""><span style="font-weight:bold">Case</span></td>
              <td align="left" nowrap=""><%=case_name %></td>
            </tr>
            <tr height="30" valign="middle">
              <td align="left" valign="top" nowrap="">
              	<span style="font-weight:bold">Invoice #</span>
              </td>
              <td align="left" nowrap="">
              	<div style="float:right">
                	<div style="font-weight:bold; display:inline-block">By</div>
                    <div style="display:inline-block">
	                    <input name="fee_byInput" type="text" id="fee_byInput" autocomplete="off" style="width:175px" class="modalInput fee input_class" />
                    </div>
                </div>
                <span id="fee_invoice_number"></span>
                <input type="hidden" value="<%=fee_check_number %>" name="fee_check_number" id="fee_check_number" />
              </td>
            </tr>
            <tr height="30" valign="middle">
              <td width="112" align="left" valign="top" nowrap=""><span style="font-weight:bold">Date</span></td>
              <td width="420" align="left" nowrap="">
              	<div style="float:right">
                	<span id="paid_label" style="font-weight:bold">Type</span>     
                    <select name="fee_typeInput" id="fee_typeInput" class="input_class" tabindex="2" required>
                        <option value="" <% if (fee_type=="") { %>selected<% } %>>Select from List</option>
                        <option value="depo" <% if (fee_type.toLowerCase()=="depo" || fee_type=="depo_fees") { %>selected<% } %>>Deposition</option>
                        <option value="attorney" <% if (fee_type.toLowerCase()=="attorney") { %>selected<% } %>>Attorney</option>
                        <option value="rehab" <% if (fee_type.toLowerCase()=="rehab") { %>selected<% } %>>Rehab</option>
                        <option value="ss" <% if (fee_type.toLowerCase()=="ss") { %>selected<% } %>>Social Security</option>
                        <option value="other" <% if (fee_type.toLowerCase()=="other") { %>selected<% } %>>Other</option>
                    </select>           
                </div>
              	<input name="fee_requestedInput" type="date" id="fee_requestedInput" style="width:133px" class="modalInput fee input_class" tabindex="1" value="<%=fee_requested %>" autocomplete="off" required="required" />
              </td>
            </tr>
            <tr height="30" valign="middle" id="doctor_select_holder" style="display:none">
              <td align="left" valign="top" nowrap=""><span style="font-weight:bold">Doctor</span></td>
              <td align="left" nowrap="">
              	<select id="doctor_attorney" name="doctor_attorney"></select>
              </td>
            </tr>
            <tr height="30" valign="middle" id="depo_rate_hours_holder" style="display:none">
              <td align="left" valign="top" nowrap=""><span style="font-weight:bold">Rate</span></td>
              <td align="left" nowrap="">
              	<div style="float:right">
                	<span id="paid_label" style="font-weight:bold">Hours</span>
                  <input name="hoursInput" type="number" step="0.01" min="0" id="hoursInput" style="width:75px" class="modalInput fee input_class paid_calc" value="<%=hours %>" autocomplete="off" tabindex="4" />
                </div>
              	<input name="hourly_rateInput" type="number" step="0.01" min="0" id="hourly_rateInput" style="width:75px" class="modalInput fee input_class rate_calc" value="<%=hourly_rate %>" autocomplete="off" tabindex="3" /> 
              	per hour
              </td>
            </tr>
            <tr height="30" valign="middle" align="left">
              <td align="left" valign="top" colspan="2"><hr />
              </td>
            </tr>
            <tr height="30" valign="middle" align="left">
              <td align="left" valign="top"><span style="font-weight:bold">Comments</span></td>
              <td align="left" valign="top">
              	<div style="float:right; display:"> 
                    <div style="text-align:right" id="fee_billed_div">
                        <span id="fee_billed_label" style="font-weight:bold">Amount</span> $
                        <input name="fee_billedInput" type="number" step="0.01" min="0" id="fee_billedInput" style="width:75px" class="modalInput fee input_class billed_calc" value="<%=fee_billed %>" autocomplete="off" tabindex="5" required="required" />
                    </div>
                    <div style="text-align:right; margin-top:5px" id="fee_payment_div">
                        <span id="paid_label2" style="font-weight:bold">Payment</span> $
                        <input name="paid_feeInput" type="number" step="0.01" min="0" id="paid_feeInput" style="width:75px" class="modalInput fee input_class paid_calc" value="<%=paid_fee %>" autocomplete="off" tabindex="6" />
                    </div>
              	</div>
                <!-- obsolete -->
                <div style="position: absolute;
    left: 580px;
    font-size: 1.8em;
    z-index: 9999;
    top: 210px; cursor:pointer; display:none" id="bill_paid">
                	&#10552;
                </div>
                <textarea name="fee_memoInput" id="fee_memoInput" rows="3" style="width:233px" class="modalInput fee input_class" tabindex="4" required><%=fee_memo %></textarea>
              </td>
            </tr>
          </tbody>
        </table>
    </form>
</div>
<div id="fee_all_done"></div>
<script language="javascript">
$( "#fee_all_done" ).trigger( "click" );
</script>
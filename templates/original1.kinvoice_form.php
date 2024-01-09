<div class="kinvoice" style="margin-left:10px">
    <form id="kinvoice_form" method="post" action="">
        <input id="table_name" name="table_name" type="hidden" value="kinvoice" />
        <input id="table_id" name="table_id" type="hidden" value="<%=id %>" />
        <input id="case_id" name="case_id" type="hidden" value="<%=case_id %>" />
        <input id="corp_id" name="corp_id" type="hidden" value="<%= corp_id %>" />
      <div>
	        <div id="check_case_label" style="display:none; width:125px; font-weight:bold">Case</div>
            <div id="check_case_name" style="display:inline-block"></div>
        </div>
        <table align="left" width="350" border="0" bordercolor="#dddddd" cellspacing="0" cellpadding="3">
          <tbody>
            <tr height="30" valign="middle">
              <td align="left" valign="top" nowrap=""><span style="font-weight:bold">Case</span></td>
              <td align="left" nowrap=""><%=case_name %></td>
            </tr>
            <tr height="30" valign="middle">
              <td align="left" valign="top" nowrap=""><span style="font-weight:bold">Payable To</span></td>
              <td align="left" nowrap="">
              		<select class="modalInput" id="payable_to" name="payable_to" tabindex="1" required multiple="multiple" style="width:440px; display:<% if (blnBulk) { %>none<% } %>">
                        <option value="">Select a Partie from List</option>
                        <%=parties %>
                    </select>
                    <table id="payable_to_table" width="440px" style="display:<% if (!blnBulk) { %>none<% } %>">
                    	<tbody id="payable_to_rows">
                        </tbody>
                    </table>
               </td>
            </tr>
            <tr height="30" valign="middle">
              <td width="112" align="left" valign="top" nowrap=""><span style="font-weight:bold">Requested</span></td>
              <td width="420" align="left" nowrap="">
              		<div style="float:right">
                    	<input type="checkbox" name="rush_request" id="rush_request" value="Y" tabindex="3" />
                    	<label>RUSH</label>
                    </div>
                    <input name="request_dateInput" type="text" id="request_dateInput" style="width:133px" class="modalInput check input_class" tabindex="2" value="<%=request_date %>" autocomplete="off" required />
              </td>
            </tr>
            <tr height="30" valign="middle">
              <td align="left" valign="top" nowrap="">
	              <span style="font-weight:bold">Needed</span>
              </td>
              <td align="left" nowrap="">
              	<div style="float:right; display:<% if (blnBulk) { %>none<% } %>">
                	<span id="amount_label" style="font-weight:bold">Amount</span>
                    $
                    <input name="amountInput" type="number" step="0.01" min="0" id="amountInput" style="width:75px" class="modalInput check input_class amount_calc" value="<%=amount %>" autocomplete="off" tabindex="5" required >
                </div>
                
              	<input type="text" name="needed_dateInput" id="needed_dateInput" style="width:133px" class="modalInput check input_class" value="<%=needed_date %>" autocomplete="off" tabindex="4" required />
              </td>
            </tr>
            <tr height="30" valign="middle" align="left">
              <td align="left" valign="top" colspan="2"><hr />
              </td>
            </tr>
            <tr height="30" valign="middle" align="left">
              <td align="left" valign="top"><span style="font-weight:bold">Reason</span></td>
              <td align="left" valign="top"><textarea name="reasonInput" id="reasonInput" cols="30" rows="2" style="width:433px" class="modalInput check input_class" tabindex="6" required><%=reason %></textarea></td>
            </tr>
          </tbody>
        </table>
    </form>
</div>
<div id="kinvoice_all_done"></div>
<script language="javascript">
$( "#kinvoice_all_done" ).trigger( "click" );
</script>
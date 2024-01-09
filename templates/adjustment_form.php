<?php 
require_once(APILIB_PATH.'legacy_session.php');
session_write_close();
?>
<div style="float:right">
	<div id="manage_categories_holder"></div>
</div>
<div class="adjustment" style="margin-left:10px">
    <form id="adjustment_form" method="post" action="">
        <input id="table_name" name="table_name" type="hidden" value="adjustment" />
        <input id="table_id" name="table_id" type="hidden" value="<%=id %>" />
        <input id="account_id" name="account_id" type="hidden" value="<%=account_id %>" />
        <div>
	        <div id="adjustment_case_label" style="display:none; width:125px; font-weight:bold">Case</div>
            <div id="adjustment_case_name" style="display:inline-block"></div>
        </div>
        <table align="left" width="350" border="0" bordercolor="#dddddd" cellspacing="0" cellpadding="3">
          <tbody>
            <tr height="30" valign="middle">
              <td width="112" align="left" valign="top" nowrap=""><strong> Date</strong></td>
              <td width="420" align="left" nowrap="">
                  <div style="float:right"> &nbsp;
                    <span id="amount_label" style="font-weight:bold">Amount</span>
                    $
                    <input name="amountInput" type="number" step="0.01" id="amountInput" style="width:75px" class="modalInput adjustment" tabindex="3" value="<%=Number(amount).toFixed(2) %>" autocomplete="off" required >
                    <input type="number" step="0.01" id="amountOriginalInput" style="width:75px; display:none" class="modalInput adjustment" tabindex="3" value="<%=Number(amount).toFixed(2) %>" >
                </div>
              <input type="date" name="adjustment_dateInput" id="adjustment_dateInput" style="width:133px" class="modalInput adjustment input_class" value="<%=adjustment_date %>" autocomplete="off" tabindex="1" required></td>
            </tr>
            <tr height="30" valign="middle">
              <td align="left" valign="top" nowrap=""><strong>Type</strong></td>
              <td align="left" nowrap="">
              	<select id="adjustment_type" name="adjustment_type">
                	<option value="A" <%if (adjustment_type=="A") { %>selected="selected"<% } %>>Adjustment</option>
                    <option value="I" <%if (adjustment_type=="I") { %>selected="selected"<% } %>>Interest</option>
                </select>
              </td>
            </tr>
            <tr height="30" valign="middle">
              <td align="left" valign="top" nowrap=""><strong>Description</strong></td>
              <td align="left" nowrap=""><textarea name="descriptionInput" id="descriptionInput" cols="30" rows="2" style="width:233px; height:80px" class="modalInput adjustment input_class" tabindex="3"><%=description %></textarea>
              </td>
            </tr>
            <tr height="30" valign="middle">
              <td align="left" valign="top" nowrap="">&nbsp;</td>
              <td align="left" nowrap="">&nbsp;</td>
            </tr>
            <tr height="30" valign="middle">
              <td colspan="2" align="left" valign="top" nowrap="" style="font-style:italic">Enter a negative number to take-away from Balance</td>
            </tr>
          </tbody>
        </table>
    </form>
</div>
<div id="adjustment_all_done"></div>
<script language="javascript">
$( "#adjustment_all_done" ).trigger( "click" );
</script>

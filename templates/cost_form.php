<?php 
require_once('../shared/legacy_session.php');
session_write_close();
?>
<div style="float:right">
	<div id="manage_categories_holder"></div>
</div>
<% if (Number(amount).toFixed(2) == 0.00) { 
		Number(amount).toFixed(2) = 150.00;
 } %>
<div class="defaultcost" style="margin-left:10px">
    <form id="defaultcost_form" method="post" action="">
        <input id="table_name" name="table_name" type="hidden" value="defaultcost" />
        <input id="table_id" name="table_id" type="hidden" value="<%=id %>" />
        <input id="case_id" name="case_id" type="hidden" value="<%=case_id %>" />
        <div>
	        <div id="defaultcost_case_label" style="display:none; width:125px; font-weight:bold">Case</div>
            <div id="defaultcost_case_name" style="display:inline-block"></div>
        </div>
        <table align="left" width="350" border="1" bordercolor="#dddddd" cellspacing="0" cellpadding="3">
          <tbody>
            <tr height="30" valign="middle">
              <td width="112" align="left" valign="top" nowrap=""><strong> Date</strong></td>
              <td width="420" align="left" nowrap="">
                  <div style="float:right"> &nbsp;
                    <span id="amount_label" style="font-weight:bold">Amount</span>
                    $
                    <input name="amountInput" type="number" step="0.01" min="0" id="amountInput" style="width:75px" class="modalInput defaultcost" tabindex="3" value="<%=Number(amount).toFixed(2) %>" autocomplete="off" required >
                </div>
              <input type="text" name="defaultcost_dateInput" id="defaultcost_dateInput" style="width:133px" class="modalInput defaultcost input_class" value="<%=defaultcost_date %>" autocomplete="off" tabindex="1" required></td>
            </tr>
            
            <tr height="30" valign="middle">
              <td align="left" valign="top" nowrap=""><strong>Description</strong></td>
              <td align="left" nowrap="">
              	
                <textarea name="defaultcost_descriptionInput" id="defaultcost_descriptionInput" cols="30" rows="2" style="width:233px; height:80px" class="modalInput defaultcost input_class" tabindex="3"><%=defaultcost_description %></textarea>
              </td>
            </tr>
          </tbody>
        </table>
    </form>
</div>
<div id="defaultcost_all_done"></div>
<script language="javascript">
$( "#defaultcost_all_done" ).trigger( "click" );
</script>

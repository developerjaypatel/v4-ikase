<?php 
include("../api/manage_session.php");
session_write_close();
?>
<div style="float:right">
	<div id="manage_categories_holder"></div>
</div>
<div class="deduction" style="margin-left:10px">
    <form id="deduction_form" method="post" action="">
        <input id="table_name" name="table_name" type="hidden" value="deduction" />
        <input id="table_id" name="table_id" type="hidden" value="<%=id %>" />
        <input id="case_id" name="case_id" type="hidden" value="<%=case_id %>" />
        <div>
	        <div id="deduction_case_label" style="display:none; width:125px; font-weight:bold">Case</div>
            <div id="deduction_case_name" style="display:inline-block"></div>
        </div>
        <table align="left" width="350" border="0" bordercolor="#dddddd" cellspacing="0" cellpadding="3">
          <tbody>
            <tr height="30" valign="middle">
              <td width="112" align="left" valign="top" nowrap=""><strong> Date</strong></td>
              <td width="420" align="left" nowrap="">
                  <div style="float:right"> &nbsp;
                    <span id="amount_label" style="font-weight:bold">Amount</span>
                    $
                    <input name="amountInput" type="number" step="0.01" min="0" id="amountInput" style="width:75px" class="modalInput deduction" tabindex="3" value="<%=Number(amount).toFixed(2) %>" autocomplete="off" required >
                </div>
              <input type="text" name="deduction_dateInput" id="deduction_dateInput" style="width:133px" class="modalInput deduction input_class" value="<%=deduction_date %>" autocomplete="off" tabindex="1" required></td>
            </tr>
            <tr height="30" valign="middle">
              <td align="left" valign="top" nowrap=""><strong>Tracking Number</strong></td>
              <td align="left" nowrap="">
              	<div style="float:right"> &nbsp;
                    <span id="payment_label" style="font-weight:bold">Payment</span>
                    $
                    <input name="paymentInput" type="number" step="0.01" min="0" id="paymentInput" style="width:75px" class="modalInput deduction" tabindex="4" value="<%=Number(payment).toFixed(2) %>" autocomplete="off" >
                </div>
              	<input type="text" id="tracking_numberInput" name="tracking_numberInput" style="width:133px" class="modalInput deduction input_class" value="<%=tracking_number %>" autocomplete="off" tabindex="2" />
              </td>
            </tr>
            <tr height="30" valign="middle">
              <td align="left" valign="top" nowrap=""><strong>Description</strong></td>
              <td align="left" nowrap="">
              	<div style="float:right"> &nbsp;
                    <span id="adjusment_label" style="font-weight:bold">Adjustment</span>
                    -$
                    <input name="adjustmentInput" type="number" step="0.01" min="0" id="adjustmentInput" style="width:75px" class="modalInput deduction" tabindex="4" value="<%=Math.abs(Number(adjustment)).toFixed(2) %>" autocomplete="off" >
                    <div style="width:140px">
                        <span id="balance_label" style="font-weight:bold">Balance</span>
                        -$<span id="balanceSpan"><%=formatDollar(balance) %></span>
                    </div>
                </div>
                <textarea name="deduction_descriptionInput" id="deduction_descriptionInput" cols="30" rows="2" style="width:233px; height:80px" class="modalInput deduction input_class" tabindex="3"><%=deduction_description %></textarea>
              </td>
            </tr>
          </tbody>
        </table>
    </form>
</div>
<div id="deduction_all_done"></div>
<script language="javascript">
$( "#deduction_all_done" ).trigger( "click" );
</script>
<?php 
require_once('../shared/legacy_session.php');
session_write_close();
?>
<div class="negotiation" style="margin-left:10px">
    <form id="negotiation_form" method="post" action="">
        <input id="table_name" name="table_name" type="hidden" value="negotiation" />
        <input id="table_id" name="table_id" type="hidden" value="<%=id %>" />
        <input id="case_id" name="case_id" type="hidden" value="<%=case_id %>" />
        <div>
	        <div id="negotiation_case_label" style="display:none; width:125px; font-weight:bold">Case</div>
            <div id="negotiation_case_name" style="display:inline-block"></div>
        </div>
        <table align="left" width="650" border="0" bordercolor="#dddddd" cellspacing="0" cellpadding="3">
          <tbody>
            <tr height="30" valign="middle">
              <td width="82" align="left" valign="top" nowrap=""><strong> Date</strong></td>
              <td align="left" nowrap>
              		<div style="float:right"> 
                    	<div id="amount_label" style="font-weight:bold; display:inline-block">Employee</div>
                        <div style="display:inline-block">
                        	<input name="workerInput" type="text" id="workerInput" autocomplete="off" style="width:227px" class="kase input_class floatlabel" value="<%=worker %>" tabindex="32" />
                        </div>
                    </div>
              		<input type="text" name="negotiation_dateInput" id="negotiation_dateInput" style="width:133px" class="modalInput negotiation input_class" value="<%=negotiation_date %>" autocomplete="off" tabindex="31" required></td>
            </tr>
            <tr height="30" valign="middle">
              <td align="left" valign="top" nowrap="">
              	<strong>Type</strong>
              </td>
              <td align="left" nowrap="">
              		<div style="float:right"> &nbsp; <span id="amount_label" style="font-weight:bold">Amount</span> $
                  		<input name="amountInput" type="number" step="0.01" min="0" id="amountInput" style="width:75px" class="modalInput negotiation" tabindex="34" value="<%=Number(amount).toFixed(2) %>" autocomplete="off" required="required" />
              		</div>
                    <select id="negotiation_typeInput" name="negotiation_typeInput" style="width:227px" class="modalInput negotiation input_class" tabindex="33" required>
                    	<option value="" <% if (negotiation_type=="") { %>selected<% } %>>Select Type</option>
                        <option value="D" <% if (negotiation_type=="D") { %>selected<% } %>>Demand</option>
                        <option value="O" <% if (negotiation_type=="O") { %>selected<% } %>>Offer</option>
                    </select>
              </td>
            </tr>
            <tr height="30" valign="middle">
              <td align="left" valign="top" nowrap=""><strong>Company</strong></td>
              <td align="left" nowrap="">
              	<div style="margin-bottom:5px" id="firm_selectInput_holder">
                    <select id="firm_selectInput" name="firm_selectInput" style="width:227px" class="modalInput negotiation input_class" tabindex="35">
                    </select>
                </div>
                <div>
                    <div style="float:right"> &nbsp;
                        <span id="payment_label" style="font-weight:bold">Negotiator</span>
                        <input name="negotiatorInput" type="text" id="negotiatorInput" style="width:227px" class="modalInput negotiation" tabindex="36" value="<%=negotiator %>" autocomplete="off" >
                    </div>
                    <input type="text" id="firmInput" name="firmInput" style="width:227px" class="modalInput negotiation input_class" value="<%=firm %>" autocomplete="off" tabindex="35" />
                </div>
              </td>
            </tr>
            <tr height="30" valign="middle">
              <td align="left" valign="top" nowrap=""><strong>Description</strong></td>
              <td align="left" nowrap=""><textarea name="commentsInput" id="commentsInput" cols="30" rows="2" style="width:563PX; height:80px" class="modalInput negotiation input_class" tabindex="37"><%=comments %></textarea>
              </td>
            </tr>
          </tbody>
        </table>
    </form>
</div>
<div id="negotiation_all_done"></div>
<script language="javascript">
$( "#negotiation_all_done" ).trigger( "click" );
</script>

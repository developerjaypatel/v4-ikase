<div class="otherbilling">
    <form id="otherbilling_form" parsley-validate>
        <input id="table_name" name="table_name" type="hidden" value="otherbilling" />
        <input id="table_id" name="table_id" type="hidden" value="<%=otherbilling_id %>" />
        <input id="case_id" name="case_id" type="hidden" value="<%=case_id %>" />
        <input id="user_id" name="user_id" type="hidden" value="<%=login_user_id %>" />
        <input id="corporation_id" name="corporation_id" type="hidden" value="<%=corporation_id %>" />
        <table width="550" border="0" align="left" cellpadding="2" cellspacing="0">
            <tr>
              <th align="left" valign="top" scope="row">Bill Date</th>
              <td colspan="2" valign="top">
              	&nbsp;<input type="date" id="bill_dateInput" name="bill_dateInput" value="<%=bill_date %>" />
              </td>
              <td valign="top">&nbsp;</td>
              <th align="left" valign="top" widt="95px">&nbsp;</th>
              <td align="left" valign="top">&nbsp;</td>
            </tr>
            <tr>
                <th width="95" align="left" valign="top" scope="row">
                	Billed:
                </th>
                <td colspan="2" valign="top">
                	$<input name="billedInput" type="number" step="0.01" class="balance_field" id="billedInput" size="10" value="<%=billed %>" tabindex="1" />
                </td>
              <td valign="top" width="50">&nbsp;</td>
                <th align="left" valign="top" widt="95px">Finalized:</th>
              <td align="left" valign="top"><input name="finalizedInput" type="date" id="finalizedInput" size="10" class="modal_input" value="<%=finalized %>" tabindex="5" /></td>
          	</tr>
            <tr>
                <th width="95" align="left" valign="top" scope="row">
                	Paid:
                </th>
                <td colspan="2" valign="top">
                	$<input name="paidInput" type="number" step="0.01" class="balance_field" id="paidInput" size="10" value="<%=paid %>" tabindex="2" />
                </td>
              <td valign="top">&nbsp;</td>
                <th align="left" valign="top" widt="95px">Still Treating:</th>
              <td align="left" valign="top"><input name="still_treatingInput" type="checkbox" id="still_treatingInput" class="modal_input" value="Y" <% if(still_treating=="Y") { %>checked<% } %> tabindex="6" /></td>
          	</tr>
            <tr>
                <th width="95" align="left" valign="top" scope="row">
                	Adjusted:
                </th>
                <td colspan="2" valign="top">
                	$<input name="adjustedInput" type="number" step="0.01" class="balance_field" id="adjustedInput" size="10" value="<%=adjusted %>" tabindex="3" />
                </td>
              <td valign="top">&nbsp;</td>
                <th align="left" valign="top" widt="95px">Prior:</th>
              <td align="left" valign="top"><input name="priorInput" type="checkbox" id="priorInput" class="modal_input" value="Y" <% if(prior=="Y") { %>checked<% } %> tabindex="7" /></td>
          	</tr>
            <tr>
                <th width="95" align="left" valign="top" scope="row">
                	Balance:
                </th>
                <td colspan="2" valign="top">
                	$<input name="balanceInput" type="number" step="0.01" id="balanceInput" size="10" class="modal_input" value="<%=balance %>" tabindex="4" />
                </td>
              <td valign="top">&nbsp;</td>
                <th align="left" valign="top" widt="95px">Lien:</th>
              <td align="left" valign="top"><input name="lienInput" type="checkbox" id="lienInput" class="modal_input" value="Y" <% if(lien=="Y") { %>checked<% } %> tabindex="8" /></td>
          	</tr>
            <!--
            <tr>
              <th align="left" valign="top" scope="row">Override:</th>
              <td colspan="2" valign="top">$
              <input name="overrideInput" type="number" step="0.01" id="overrideInput" size="10" class="modal_input" value="<% //override %>" tabindex="4" /></td>
              <td valign="top">&nbsp;</td>
              <th colspan="2" align="left" valign="top" widt="95px">
              	<div id="override_indicator" style="float:left; margin-left:35px; background:red; color: white; font-size:0.8em; font-weight:bold; padding:3px; display:none">
                    OVERRIDE
                </div>
              </th>
            </tr>
            -->
		</table>
    </form>
</div>
<div id="other_billing_view_done"></div>
<script language="javascript">
$( "#other_billing_view_done" ).trigger( "click" );
</script>
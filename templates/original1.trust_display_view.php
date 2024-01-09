<div id="trust_display" style="margin-top:30px">
    <table id="trust_display_table" width="600px" border="0" cellspacing="0" cellpadding="0" align="center">
        <tr>
            <th colspan="4" style="font-size:1.6em">
            	<div style="float:right;">
                	<span style="font-size:1em; cursor:pointer" id="close_display">&times;</span>
                </div>
            	Trust Account Current Balance
            </td>
        </tr>
        <tr>
            <td align='left' valign='top'>&nbsp;</td>
            <td align='left' valign='top'>Cleared</td>
            <td align='left' valign='top'>Pending</td>
            <td align='left' valign='top'>Total</td>
        </tr>
        <tr>
            <td align='left' valign='top' nowrap>Starting Amount</td>
            <td colspan="2" align='right' valign='top'>
	            Statement Date: <input type="date" id="statement_date" value="<%=starting_statement_date %>" />
            </td>
            <td align='right' valign='top'>
                <div id="save_starting_holder" style="display: none; position: absolute; z-index: 9999; margin-left: 175px; margin-top: 0px;">
                    <button class="btn btn-sm btn-primary" id="save_starting_amount">Save Starting Amount</button>
                    <input type="hidden" id="balance_account_id" value="<%=account_id %>" />
                    <span id="starting_amount_feedback" style="display:none"></span>
                </div>
                $
                <input type="number" name="starting_amount" id="starting_amount" value="<%= starting_amount %>" step="0.01" style="text-align:right; width:110px" />
            </td>
        </tr>
        <tr>
            <td align='left' valign='top' nowrap>Receipts</td>
          <td align='right' valign='top'>
          		<a class="list_cleared" id="list_cleared_receipts" style="cursor:pointer">$<%=formatDollar(total_cleared_receipts) %></a>
          </td>
            <td align='right' valign='top'>
            	<a class="list_uncleared" id="list_uncleared_receipts" style="cursor:pointer">$<%=formatDollar(total_uncleared_receipts) %></a>
            </td>
            <td align='right' valign='top'>
            	<a class="list_checks" id="list_total_receipts" style="cursor:pointer">$<%=formatDollar(Number(total_cleared_receipts) + Number(total_uncleared_receipts)) %></a>
            </td>
        </tr>
        <tr>
            <td align='left' valign='top' nowrap>Disbursments</td>
            <td align='right' valign='top'>
            	<a class="list_cleared" id="list_cleared_disburs" style="cursor:pointer">$<%=formatDollar(total_cleared_disburs) %></a>
            </td>
            <td align='right' valign='top'>
            	<a class="list_uncleared" id="list_uncleared_disburs" style="cursor:pointer">$<%=formatDollar(total_uncleared_disburs) %></a>
            </td>
            <td align='right' valign='top' style="color:red">
            	<a class="list_checks" id="list_total_disburs" style="cursor:pointer; color:red">($<%=formatDollar(Number(total_cleared_disburs) + Number(total_uncleared_disburs)) %>)</a>
            </td>
        </tr>
        <tr>
            <td align='left' valign='top' nowrap>Adjustments</td>
          <td align='right' valign='top'>&nbsp;</td>
            <td align='right' valign='top'>&nbsp;</td>
            <td align='right' valign='top' style="color:red">($<%=formatDollar(total_adjusted) %>)</td>
        </tr>
        <tr>
            <td align='left' valign='top' nowrap>Interest</td>
          <td align='right' valign='top'>&nbsp;</td>
            <td align='right' valign='top'>&nbsp;</td>
            <td align='right' valign='top'>$<%=formatDollar(total_interest) %></td>
        </tr>
        <tr>
            <td align='left' valign='top' nowrap>Balance</td>
          <td align='right' valign='top'>&nbsp;</td>
            <td align='right' valign='top'>&nbsp;</td>
            <td align='right' valign='top' id="balance_cell">$<%=formatDollar(Number(starting_amount) + (Number(total_cleared_receipts) + Number(total_uncleared_receipts)) - (Number(total_cleared_disburs) + Number(total_uncleared_disburs)) - Number(total_adjusted) + Number(total_interest)) %></td>
        </tr>
    </table>
</div>

<div id="trust_display_view_all_done"></div>
<script language="javascript">
$("#trust_display_view_all_done").trigger( "click" );
</script>
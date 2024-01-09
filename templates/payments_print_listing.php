<div>
	<div class="glass_header">
        <div style="float:right;">
        <input id="case_id" name="case_id" type="hidden" value="<%=case_id %>" />
        </div>
    </div>
	<table border="0" cellpadding="3" cellspacing="0" align="center" style="width:70%">
	<tr>
			<td width="20%"><img src="img/ikase_logo_login.png" height="32" width="77"></td>
			<td align="left" style="font-family:Arial, Helvetica, sans-serif; font-weight:bold; font-size:1.5em" colspan="3">
            	<div style="float:right; font-size:10px; font-weight:normal">
                	<em>as of <?php echo date("m/d/y g:iA"); ?></em>
                </div>
                Kase <%=title %> :: <span id="case_name"></span>
            </td>
		</tr>
        <tr>
        	<td colspan="4"><hr color="#000000" /></td>
        </tr>
	</table>
    <table id="check_listing" class="tablesorter check_listing" border="0" cellpadding="3" cellspacing="0" align="center" style="width:70%">
		<thead style="background:#EDEDED;">
		
        <tr style="background:#EDEDED;" >
        	<th align="left" width="100">
            	Check #
            </th>
            <th align="left" width="1%">
            	Date
            </th>
            <th align="left" width="200">
            	Category
            </th>
            <th width="1%">
            	Due
            </th>
            <th width="1%">
            	Amount
            </th>
            <th width="1%">
            	Outstanding
            </th>
            <th>
            	Memo
            </th>
        </tr>
        </thead>
        <tbody>
        <% 
        _.each( checks, function(check) {
        %>
        <tr class="check_data_row">
            <td><%= check.check_number %></td>
            <td align="left" valign="top"><%= check.check_date %></td>
            <td align="left" valign="top"><%= check.check_type %></td>
            <td align="right" valign="top">$<%= check.amount_due %></td>
            <td align="right" valign="top">$<%= check.payment %></td>
            <td align="right" valign="top">$<%= check.balance %></td>
            <td align="left" valign="top"><%= check.memo %></td>
        </tr>
        <% }); %>
        <% if (checks.length > 0) { %>
        <tr style="background:#CCCCCC">
            <td>&nbsp;</td>
            <td align="left" valign="top">&nbsp;</td>
            <td align="left" valign="top">Totals</td>
            <td align="right" valign="top">$<%=totals_due.toFixed(2) %></td>
            <td align="right" valign="top">$<%=totals_payment.toFixed(2) %></td>
            <td align="left" valign="top">&nbsp;</td>
            <td align="left" valign="top">&nbsp;</td>
        </tr>
        <% } %>
        </tbody>
    </table>
</div>
<div id="check_print_listing_all_done"></div>
<script language="javascript">
$( "#check_print_listing_all_done" ).trigger( "click" );
</script>
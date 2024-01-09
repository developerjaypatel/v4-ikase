<?php require_once('../shared/legacy_session.php');
session_write_close();
?>
<div>
  <% if (!embedded) { %>
    <div id="fee_listing_header" class="glass_header">
        <div style="float:right;">
        <input id="case_id" name="case_id" type="hidden" value="<%=current_case_id %>" />
            
            <div class="btn-group">
                
                 <label for="fees_searchList" id="label_search_fee" style="width:120px; font-size:1em; cursor:text; position:relative; top:0px; left:125px; color:#999; margin-left:0px; margin-top:0px; border:#00FF00 0px solid;">Search</label>
                
                <input id="fees_searchList" type="text" class="search-field" placeholder="" autocomplete="off" onkeyup="findIt(this, 'fee_listing', 'fee')">
                <a id="fees_clear_search" style="position: absolute;
                right: 2px;
                top: 0px;
                bottom: 2px;
                height: 14px;
                margin: auto;
                cursor: pointer;
                border: 0px solid green;
                "><i class="glyphicon glyphicon-remove-circle" style='font-size:1em; color:#cccccc'></i></a>
            </div>
        </div>
        <span style="font-size:1.2em; color:#FFFFFF"><%=page_title %></span>
        &nbsp;&nbsp;<span class="white_text">(<%=fees.length %>)</span>
         
    </div>
    <% } %>
    <table id="fee_listing" class="tablesorter fee_listing fee_listing_<%=page_title %>" border="0" cellpadding="0" cellspacing="1" style="font-size:1.1em" width="100%">
    	<% if (!embedded) { %>
        <thead>
        <tr>
            <th width="104">
                Type
            </th>
            <th width="50">
                By
            </th>
            <th width="104" style="text-align:left">
                Fee
            </th>
            <th width="104" style="text-align:right">
                Payments
            </th>
            <!--
            <th width="104" style="text-align:right">
                Due
            </th>
            -->
            <th width="84" style="text-align:left">
                Requested
            </th>
            <th width="84" style="text-align:left">
            	Received
            </th>
            <th>
                Memo
            </th>
        </tr>
        </thead>
        <% } %>
        <tbody>
        <% 
        var total_balance = 0;
        var total_billed = 0;
        var total_paid = 0;
        _.each( fees, function(fee) {
        	var fee_requested = fee.fee_requested;
            if (fee_requested=="" || fee_requested=="0000-00-00") {
            	fee_requested = "";
            } else {
            	fee_requested = moment(fee_requested).format("MM/DD/YY");
            }
            var fee_date = fee.fee_date;
            if (fee_date=="" || fee_date=="0000-00-00") {
            	fee_date = "";
            } else {
            	fee_date = moment(fee_date).format("MM/DD/YY");
            }
            var fee_balance = Number(fee.fee_billed) - Math.abs(Number(fee.paid_fee));
            var balance_style = "";
            if (fee_balance > 0.1) {
            	balance_style = "background:orange; color:black; padding:2px";
            }
            var blnPaid = false;
            if (fee_balance <= 0.1) {
            	balance_style = "background:lime; color:black; padding:2px";
                blnPaid = true;
            }
            total_balance += fee_balance;
            total_billed += Number(fee.fee_billed);
            total_paid += Number(fee.paid_fee);
        %>
        <tr class="fee_data_row fee_data_row_<%= fee.id %>">
            <% if (!embedded) { %>
            <td align="left" valign="top" nowrap="nowrap" width="104">
	            <%=fee.fee_type.capitalize() %>
            </td>
            <% } %>
            <td align="left" valign="top" nowrap="nowrap" width="50">
	            <%=fee.fee_by %>
            </td>
            <td nowrap="nowrap" style="text-align:right;vertical-align:top; width:104px">
            	$<%= formatDollar(fee.fee_billed) %>
                
                <div style="float:left">
                	<a id="editfee_<%=fee.fee_type %>_<%= fee.id %>" class="edit_fee" style="cursor:pointer"><i style="font-size:15px;color:#06f; cursor:pointer;" class="glyphicon glyphicon-edit" title="Click to Edit Fee"></i></a>
                </div>
                
            </td>
            <td nowrap="nowrap" style="text-align:right;vertical-align:top; width:104px;">$&nbsp;<%= formatDollar(fee.paid_fee) %></td>
            <!--
            <td nowrap="nowrap" style="text-align:right;vertical-align:top; width:104px;"><span style='<%=balance_style %>'>$&nbsp;<%= formatDollar((fee_balance)) %></span></td>
            -->
            <td style="text-align:left;vertical-align:top; width:84px;" nowrap="nowrap"><%= fee_requested %></td>
            <td style="text-align:left;vertical-align:top; width:84px;" nowrap="nowrap"><%= fee_date %></td>
            <!--<td style="text-align:left;vertical-align:top; width:68px" nowrap="nowrap"><%= fee.fee_by %></td>-->
            <td style="text-align:left;vertical-align:top;">
            	<%=fee.fee_memo %>
            </td>
            <td style="text-align:right;vertical-align:top; width:120px">
            	<div style="float:right">
                	<i style="font-size:15px; color:#FF3737; cursor:pointer" class="glyphicon glyphicon-trash delete_fee" id="delete_<%= fee.id %>" title="Click to delete"></i>
                </div>
                <% if (fee.fee_billed > 0 && fee.fee_billed > fee.fee_total_paid) { %>
                <button id="pay_<%=fee.fee_type %>_<%= fee.id %>" style="display:" class="input_button btn btn-success btn-xs btn_fee_button pay_settlement_fee">Add Payment</button>
                <% } %>
                <% if (fee.fee_billed > 0 && fee.fee_billed <= fee.fee_total_paid) { %>
                <span style="background:lime; color:black">PAID&nbsp;&#10003;</span>
                <% } %>
            </td>
        </tr>
        <% }); %>
        <% if (fees.length > 1) {
        		var balance_style = "background:black";
                var total_balance = (total_billed - total_paid) ;
                if (total_balance <= 0.1) {
                    balance_style = "background:lime; color:black; padding:2px";
                    total_balance = "&#10003;";
                } else {
                	balance_style = "background:orange; color:black; padding:2px";
                	total_balance = "$" + formatDollar(total_balance);
                }
        %>
        <tr class="fee_data_row fee_data_row_totals">
        	<% if (!embedded) { %>
          <td align="left" valign="top" nowrap="nowrap">&nbsp;</td>
          	<% } %>
          <td style="text-align:left;vertical-align:top;">&nbsp;</td>
          <td nowrap="nowrap" style="text-align:right;vertical-align:top;background:black">$&nbsp;<%= formatDollar(total_billed) %></td>
          <td nowrap="nowrap" style="text-align:right;vertical-align:top;background:black">$&nbsp;<%= formatDollar(total_paid) %></td>
          <!--
          <td nowrap="nowrap" style="text-align:right;vertical-align:top; background:black">$&nbsp;<%= formatDollar(total_balance) %></td>
          -->
          <td style="text-align:left;vertical-align:top;" nowrap="nowrap">&nbsp;<span style="<%=balance_style %>"><%=total_balance %></span></td>
          <td style="text-align:left;vertical-align:top" nowrap="nowrap">&nbsp;</td>
          <td style="text-align:left;vertical-align:top;">&nbsp;</td>
        </tr>
        <% } %>
        </tbody>
    </table>
</div>
<div id="fee_listing_all_done"></div>
<script language="javascript">
$( "#fee_listing_all_done" ).trigger( "click" );
</script>

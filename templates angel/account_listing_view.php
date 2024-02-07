<?php
include("../api/manage_session.php");
session_write_close();

$day = date('w');
$week_start = date('Y-m-d', strtotime('-'.$day.' days'));
$week_end = date('Y-m-d', strtotime('+'.(6-$day).' days'));

if (strpos($_SESSION['user_role'], "admin") === false) {
	die("<div class='white_text' style='text-align:center;padding-top:10px'>You lack permissions to be here, don't you?</div>");
}
?>
<div id="confirm_delete" style="display:none; position:absolute; top:20px; color:white; background:black; border:1px solid white; font-size:1.2em; padding:10px; text-align:center; z-index:1051">
	<div style="width:350px; margin-left:auto; margin-right:auto; ">
    <input type="hidden" name="confirm_delete_id" id="confirm_delete_id" value="" />
    Are you sure you want to delete this account?
    <div style="padding:5px; text-align:center"><a id="delete_account" class="delete_yes white_icon" style="cursor:pointer">YES</a></div>
    <div style="padding:5px; text-align:center"><a id="cancel_account" class="delete_no white_icon" style="cursor:pointer">NO</a></div>
    </div>
</div>
<div>
	<div class="glass_header">
    	<div style="width:500px">
        	<% if (accounts.length==0) { %>
            <div style="float:right">
                <button id="new_account_button" class="compose_new_account btn btn-sm btn-primary" title="Click to create a new <%=display_account_type.capitalizeWords() %> Account" style="margin-top:-5px">New <%=display_account_type.capitalizeWords() %> Account</button> 
            </div>
            <% } %>
            <span style="font-size:1.2em; color:#FFFFFF"><%=page_title %></span>
        </div>
    </div>
    <table id="account_listing" class="tablesorter account_listing" border="0" cellpadding="0" cellspacing="0">
        <thead>
        <tr>
            <th align="left" valign="top" style="font-size:1.5em; width:320px">&nbsp;
                
            </th>
            <!--
            <th align="left" valign="top" style="font-size:1.5em">
                Account #
            </th>
            -->
            <th align="left" valign="top" style="font-size:1.5em" width="1%">
                Deposits
            </th>
            <th align="left" valign="top" style="font-size:1.5em" width="1%">
                Withdrawals
            </th>
            <th align="left" valign="top" style="font-size:1.5em" width="1%">
                Adjust/Interest
            </th>
            <th align="left" valign="top" style="font-size:1.5em" width="1%">
                Balance
            </th>
            <th align="left" valign="top" style="font-size:1.5em" width="1%">
                Pending
            </th>
            <th align="left" valign="top" style="font-size:1.5em" width="1%" nowrap="nowrap">
                Pre-Bill
            </th>
            <th align="left" valign="top" style="font-size:1.5em" width="1%">
                Transfers
            </th>
            <th align="left" valign="top" style="font-size:1.5em" width="1%">
                Available
            </th>
            <th align="left" valign="top" style="font-size:1.5em">&nbsp;
                
            </th>
            <!--
            <th align="left" valign="top" style="font-size:1.5em">Branch</th>
            <th align="left" valign="top" style="font-size:1.5em">Holder</th>
            
            <th align="left" valign="top" style="font-size:1.5em">
            	Created
            </th>
            -->
            <th align="left" valign="top">&nbsp;</th>
        </tr>
        </thead>
        <tbody>
       <% _.each( accounts, function(account) {
       			account.available = Number(account.balance) - Number(account.pendings);
                
                account.available = Number(account.balance) - Number(account.pendings);
                account.available_background = "#1453b3;";
                if (account.available < 0.01) {
                    account.available = "$(" + formatDollar(account.available) + ")";
                    account.available_background = "orange";
                } else {
                    account.available = "$" + formatDollar(account.available);
                }
       	%>
       	<tr class="account_data_row account_data_row_<%= account.id %>">
                <td align="left" valign="top" style="font-size:1.5em; width:320px">
                <a href='#bankaccount/edit/<%= account.id %>' class="list-item_kase" style="color:white;"><%=account.bank %></a>
                </td>
                <!--
                <td align="left" valign="top" style="font-size:1.5em">
                	<%= account.account_number %>
                </td>
                -->
                <td align="right" valign="top" style="font-size:1.5em" id="deposits_cell">
                	$<%= formatDollar(account.deposits) %>
                </td>
                <td align="right" valign="top" style="font-size:1.5em" id="withdrawals_cell">
                	$<%= formatDollar(account.withdrawals) %>
                </td>
                <td align="right" valign="top" style="font-size:1.5em">
                	$<%= formatDollar(account.adjustments) %>
                </td>
                <td align="right" valign="top" style="font-size:1.5em; background:black" nowrap="nowrap">
                	<span id="balance_cell">$<%= formatDollar(account.balance) %></span>
                    &nbsp;|&nbsp;
                    <button id="add_<%=account.account_type %>_adjustment_<%= account.id %>" class="btn btn-xs btn-primary add_adjustment" title="Click to adjust the <%=display_account_type.capitalizeWords() %> Account">Adjustment</button>
                </td>
                <td align="right" valign="top" style="font-size:1.5em" id="pendings_cell">
                	$<%= formatDollar(account.pendings) %>
                </td>
                <td align="right" valign="top" style="font-size:1.5em">
                	$<%= formatDollar(account.pre_bills) %>
                </td>
                <td align="right" valign="top" style="font-size:1.5em">
                	$<%= formatDollar(account.transfers) %>
                </td>
                <td align="right" valign="top" style="font-size:1.5em; background:<%= account.available_background %>" id="available_cell">
                	<%= account.available %>
                </td>
                <td align="left" valign="top">
                	<% if (account.account_type=="trust") { %>
                	&nbsp;
                	<button class="btn btn-xs review_kases" id="review_<%= account.id %>" title="Review Kases">Kases</button>
                    &nbsp;|&nbsp;
                    <% } %>
                    <button class="btn btn-xs review_transactions" id="review_<%= account.id %>" title="Review Transactions Register">Register</button>
                     <% if (account.adjustments!=0) { %>
                    &nbsp;|&nbsp;
                    <button class="btn btn-xs review_adjustments" id="adjustments_<%= account.id %>" title="Review Adjustments/Interest">Adjustments/Interest</button>
                    <% } %>
                    &nbsp;|&nbsp;
                    <button id="add_<%= account.account_type %>_check_<%= account.id %>" class="btn btn-xs add_check" title="Click to deposit a Check into Account">Make Deposit</button>
                    &nbsp;|&nbsp;
                    <button id="add_firm_<%= account.account_type %>_check_<%= account.id %>" class="btn btn-xs add_check firm_deposit" title="Click to deposit a Check into Firm Account">Firm Deposit</button>
                    &nbsp;|&nbsp;
                    <button id="<%= account.account_type %>_display_<%= account.id %>" class="btn btn-xs account_display" title="Click to view Account Balance Summary">Balance Summary</button>
                     &nbsp;|&nbsp;
                    <button id="<%= account.account_type %>_pending_<%= account.id %>" class="btn btn-xs account_pending" title="Click to view Pending Check Requests for this account">Pending Requests</button>
                </td>
                <!--
                <td align="left" valign="top" style="font-size:1.5em"><%= account.branch %></td>
                <td align="left" valign="top" style="font-size:1.5em"><%= account.account_holder %></td>
                
                <td align="left" valign="top" style="font-size:1.5em">
                	<%= moment(account.account_create_date).format("MM/DD/YYYY") %>
                </td>
                -->
                <td align="right" valign="top">
                	<a class="delete_icon" id="confirmdelete_account_<%= account.id %>" title="Click to delete account" style="cursor:pointer"><i class="glyphicon glyphicon-trash" style="color:#FF0000;">&nbsp;</i></a>
                </td>
        </tr>
        <tr class="account_data_row account_summary_row account_balance_row_<%= account.id %>" style="display:none">
        	<td align="left" valign="top" style="font-size:1.5em;" colspan="12" id="account_balance_<%= account.id %>">
            </td>
        </tr>
        <tr class="account_data_row account_summary_row account_adjustments_row_<%= account.id %>" style="display:none">
        	<td align="left" valign="top" style="font-size:1.5em;" colspan="12" id="account_adjustments_<%= account.id %>">
            </td>
        </tr>
        <tr class="account_data_row account_summary_row account_checks_row_<%= account.id %>" style="display:none">
        	<td align="left" valign="top" style="font-size:1.5em;" colspan="12" id="account_checks_<%= account.id %>">
            </td>
        </tr>
        <tr class="account_data_row account_summary_row account_kases_row_<%= account.id %>" style="display:none">
        	<td align="left" valign="top" style="font-size:1.5em;" colspan="12" id="account_kases_<%= account.id %>">
            </td>
        </tr>
        <% }); %>
        </tbody>
    </table>
</div>
<?php include("../api/manage_session.php"); 
session_write_close();
?>
<div>
	<% if (!embedded) { %>
    <div id="deduction_listing_header" class="glass_header">
        <div style="float:right;">
        <input id="case_id" name="case_id" type="hidden" value="<%=current_case_id %>" />
            
            <div class="btn-group">
                
                 <label for="deductions_searchList" id="label_search_deduction" style="width:120px; font-size:1em; cursor:text; position:relative; top:0px; left:125px; color:#999; margin-left:0px; margin-top:0px; border:#00FF00 0px solid;">Search</label>
                
                <input id="deductions_searchList" type="text" class="search-field" placeholder="" autocomplete="off" onkeyup="findIt(this, 'deduction_listing', 'deduction')">
                <a id="deductions_clear_search" style="position: absolute;
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
        <span style="font-size:1.2em; color:#FFFFFF"><%=page_title.replace("Disbursement", "Checks Paid") %>s</span>
        &nbsp;&nbsp;<span class="white_text">(<%=deductions.length %>)</span>
        
        &nbsp;
        <button id="new_deduction" class="btn btn-sm btn-primary btn_deduction" title="Click to add a new Deduction" style="margin-top:-5px">New Deduction</button>
        <button id="new_cost" class="btn btn-sm btn-primary btn_deduction new_deduction" title="Click to add a new Cost" style="margin-top:-5px">New Cost</button> 
    </div>
    <% } %>
    <table id="deduction_listing" class="tablesorter deduction_listing deduction_listing_<%=page_title %>" border="0" cellpadding="0" cellspacing="1">
        <thead>
        <tr>
        	<th width="1%">&nbsp;
                
            </th>
        	<th width="1%">
                Date
            </th>
            <th width="1%">
                Amount
            </th>
            <th width="1%">
                Payment
            </th>
            <th width="1%">
                Adjustment
            </th>
            <!--
            <th width="1%">
                Balance
            </th>
            -->
            <th align="left" width="350px">
                Description
            </th>
            <th width="1%">&nbsp;</th>
        </tr>
        </thead>
        <tbody>
        <% 
        var totals_due = 0;
        var totals_paid = 0;
        var totals_adjust = 0;
        var totals_balance = 0;
        
        _.each( deductions, function(deduction) {
        	totals_due += Number(deduction.amount);
            totals_paid += Number(deduction.payment);
            totals_adjust += Number(deduction.adjustment);
            totals_balance += Number(deduction.balance);
        %>
        <tr class="deduction_data_row deduction_data_row_<%= deduction.id %>">
        	<td align="left" valign="top">
            	<a id="edit_deduction_<%=deduction.id %>" class="edit_deduction">
                	<i style="font-size:15px; color:#a9bafd; cursor:pointer" class="glyphicon glyphicon-edit"></i>
                </a>
            </td>
            <td align="left" valign="top"><%= deduction.deduction_date %></td>
            <td align="right" valign="top" nowrap="nowrap">
            	$<%= formatDollar(deduction.amount) %>
            </td>
            <td align="right" valign="top" nowrap="nowrap">
            	$<%= formatDollar(deduction.payment) %>
            </td>
            <td align="right" valign="top" nowrap="nowrap">
            	$<%= formatDollar(deduction.adjustment) %>
            </td>
            <!--
            <td align="right" valign="top" nowrap="nowrap">
            	$<%= formatDollar(deduction.balance) %>
            </td>
            -->
            <td align="left" valign="top"><%= deduction.deduction_description %></td>
            <td>
            	<i style="font-size:15px; color:#FF3737; cursor:pointer" class="glyphicon glyphicon-trash delete_deduction" id="delete_<%= deduction.id %>" title="Click to delete"></i>
            </td>
        </tr>
        <% }); %>
        <% if (deductions.length > 0) { %>
        <tr style="background:black">
            <td align="left" valign="top">&nbsp;</td>
            <td align="right" valign="top">$<%=formatDollar(totals_due) %></td>
            <td align="right" valign="top">$<%=formatDollar(totals_paid) %></td>
            <td align="right" valign="top">$<%=formatDollar(totals_adjust) %></td>
            <!--
            <td align="right" valign="top">$<%=formatDollar(totals_balance) %></td>
            -->
            <td align="left" valign="top">&nbsp;</td>
            <td align="left" valign="top">&nbsp;</td>
        </tr>
        <% } %>
        </tbody>
    </table>
</div>
<div id="deduction_listing_all_done"></div>
<script language="javascript">
$( "#deduction_listing_all_done" ).trigger( "click" );
</script>
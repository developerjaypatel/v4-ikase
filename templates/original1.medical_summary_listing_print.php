<?php
include("../api/manage_session.php");
session_write_close();
?>
<div class="medical_summary">
	<table align="center" width="1080px">
    	<tr>
            <td style="border:0px solid blue"><img src="img/ikase_logo_login.png" height="32" width="77"></td>
            <td align="left" colspan="2">
            	<div style="float:right; margin-top:10px">
                    <em>as of <?php echo date("m/d/y g:iA"); ?></em>
                </div>
                <div>
                	<%=case_name %>
                </div>
            	<span style="font-size:1.5em; margin-top-3px; margin-left:10px">
                	Medical Summary Report
                </span>
             </td>
		</tr>
	</table>
    <hr />
    <table id="medical_summary_listing" class="tablesorter medical_summary_listing" border="0" cellpadding="0" cellspacing="1" width="1080px" align="center">
        <thead>
        <tr>
            <th style="font-size:1em; width:20%; text-align:left">
            	Provider
            </th>
            <th style="font-size:1em; width:20%; text-align:left">
            	Phone
            </th>
            <th style="font-size:1em; width:1%; text-align:center">
            	Billed
            </th>
            <th style="font-size:1em; width:1%; text-align:center">
            	Paid
            </th>
            <th style="font-size:1em; width:1%; text-align:center">
            	Adjusted
            </th>
            <th style="font-size:1em; width:1%; text-align:center">
            	Balance
            </th>
        </tr>
        </thead>
        <tbody>
        <% 
        var total_billed = 0;
        var total_paid = 0;
        var total_adjusted = 0;

        _.each( medical_summarys, function(medical_summary) {
        	total_billed += Number(medical_summary.billed);
            total_paid += Number(medical_summary.paid);
        	total_adjusted += Math.abs(medical_summary.adjusted);
        %>
        <tr class="medical_summary_data_row billing_data_row_<%= medical_summary.id %>">
            <td align="left" style="font-size:1em;" nowrap="nowrap">
            	<a class="provider_listing" id="provider_listing_<%= medical_summary.corporation_id %>_<%= medical_summary.id %>" style="cursor:pointer; text-decoration:underline" title="Click to see the List of Medical Bills"><%= medical_summary.company_name %></a>
            </td>
            <td align="left" style="font-size:1em;" nowrap="nowrap">
            	<% if (medical_summary.full_name=="") { %>
            	<%= medical_summary.phone %>
                <% } else { %>
                <%= medical_summary.employee_phone %>
                <% } %>
            </td>
            <td align="right" style="font-size:1em;">$<%= formatDollar(medical_summary.billed) %></td>
            <td align="right" style="font-size:1em;">$<%= formatDollar(medical_summary.paid) %></td>
            <td align="right" style="font-size:1em;">$<%= formatDollar(medical_summary.adjusted) %></td>
            <td align="right" style="font-size:1em;">
            	$<%= formatDollar(medical_summary.balance) %>
            </td>
        </tr>
        <tr id="medical_bills_holder_<%= medical_summary.id %>" style="display:none">
        	<td colspan="6" align="left">
            	<div id="medical_billings_<%= medical_summary.id %>" style="padding-bottom:10px; background:aliceblue"></div>
            </td>
        </tr>
        <% }); %>
        <tr>
        	<td align="left" style="font-size:1em; border-top:1px solid black; padding-top:5px" nowrap="nowrap" colspan="2">Totals</td>
            <td align="right" style="font-size:1em; border-top:1px solid black; padding-top:5px">$<%= formatDollar(total_billed) %></td>
            <td align="right" style="font-size:1em; border-top:1px solid black; padding-top:5px">$<%= formatDollar(total_paid) %></td>
            <td align="right" style="font-size:1em; border-top:1px solid black; padding-top:5px">$<%= formatDollar(total_adjusted) %></td>
            <td align="right" style="font-size:1em; border-top:1px solid black; padding-top:5px ">$<%= formatDollar(total_billed - total_paid - Math.abs(total_adjusted)) %></td>
        </tr>
        </tbody>
    </table>
</div>
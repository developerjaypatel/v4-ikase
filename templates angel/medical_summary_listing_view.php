<?php
include("../api/manage_session.php");
session_write_close();
?>
<div class="medical_summary">
	<div class="glass_header">
        <span style="font-size:1.2em; color:#FFFFFF">Medical Summary</span>
        &nbsp;
        <button class="btn btn-sm btn-primary" id="print_medical_summary" role="button">Print</button>
    </div>
    <table id="medical_summary_listing" class="tablesorter medical_summary_listing" border="0" cellpadding="0" cellspacing="1">
        <thead>
        <tr>
            <th style="font-size:1em; width:20%; text-align:left">
            	Provider
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
            
            //medical_summary.balance = Number(medical_summary.billed) - Number(medical_summary.paid) + Number(medical_summary.adjusted);
        %>
        <tr class="medical_summary_data_row billing_data_row_<%= medical_summary.id %>">
            <td align="left" style="font-size:1em;" nowrap="nowrap">
            	<%= medical_summary.company_name %>
            </td>
            <td align="right" style="font-size:1em;">$<%= formatDollar(medical_summary.billed) %></td>
            <td align="right" style="font-size:1em;">$<%= formatDollar(medical_summary.paid) %></td>
            <td align="right" style="font-size:1em;">$<%= formatDollar(medical_summary.adjusted) %></td>
            <td align="right" style="font-size:1em;">
            	$<%= formatDollar(medical_summary.balance) %>
            </td>
        </tr>
        <% }); %>
        <tr>
        	<td align="left" style="font-size:1em;" nowrap="nowrap">&nbsp;</td>
            <td align="right" style="font-size:1em; background:black">$<%= formatDollar(total_billed) %></td>
            <td align="right" style="font-size:1em;">$<%= formatDollar(total_paid) %></td>
            <td align="right" style="font-size:1em;">$<%= formatDollar(total_adjusted) %></td>
            <td align="right" style="font-size:1em; background:black">$<%= formatDollar(total_billed - total_paid - Math.abs(total_adjusted)) %></td>
        </tr>
        </tbody>
    </table>
</div>
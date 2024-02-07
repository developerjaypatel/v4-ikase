<?php
include("../api/manage_session.php");
session_write_close();
?>
<div class="medical_billing">
	<div class="glass_header">
        <div style="float:right;" class="white_text">
        <input id="case_id" name="case_id" type="hidden" value="<%=case_id %>" />
			<div class="btn-group">            	
            	<label for="medical_billings_searchList" id="label_search_medical_billing" style="width:120px; font-size:1em; cursor:text; position:relative; top:0px; left:125px; color:#999; margin-left:0px; margin-top:0px; border:#00FF00 0px solid;">Search</label>
                <input id="medical_billings_searchList" type="text" class="search-field" placeholder="" autocomplete="off" onkeyup="findIt(this, 'medical_billing_listing', 'medical_billing')" style="height:25px; line-height:32px; margin-top:-5px" />
				<a id="medical_billings_clear_search" style="position: absolute;
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
        
        <span style="font-size:1.2em; color:#FFFFFF">Medical Billing
            &nbsp;(<%=medical_billings.length %>)&nbsp;&nbsp;&nbsp;&nbsp;
        </span>
        <button id="medicalbilling_button" class="compose_new_medicalbilling btn btn-sm btn-primary" title="Click to create a new Medical Billing" style="margin-top:-5px">New Medical Billing</button> 
    </div>
    <table id="medical_billing_listing" class="tablesorter medical_billing_listing" border="0" cellpadding="0" cellspacing="1">
        <thead>
        <tr>
        	<th style="font-size:1em; width:1%; text-align:center">&nbsp;
            
            </th>
            <% if (partie_id=="") { %>
            <th style="font-size:1em; width:20%; text-align:left">
            	Provider
            </th>
            <% } %>
            <th style="font-size:1em; width:1%; text-align:center">
            	Date
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
            <!--
            <th style="font-size:1em; width:1%; text-align:left">
            	Override
            </th>
            -->
            <th style="font-size:1em; width:10%; text-align:left">
            	Final
            </th>
            <th style="font-size:1em; width:10%; text-align:left">
            	By
            </th>
            <% if (!embedded) { %>
            <th style="font-size:1em; width:10%; text-align:left">
            	Still
            </th>
            <th style="font-size:1em; width:10%; text-align:left">
            	Prior
            </th>
            <th style="font-size:1em; width:10%; text-align:left">
            	Lien
            </th>
            <% } %>
            <th style="font-size:1em; width:10%; text-align:left">&nbsp;
            	
            </th>
        </tr>
        </thead>
        <tbody>
        <% 
        _.each( medical_billings, function(medical_billing) {
        %>
        <tr class="medical_billing_data_row billing_data_row_<%= medical_billing.id %>">
        	<td align="left" style="font-size:1em;" nowrap="nowrap">
            	<a id="medicalbilling_edit_<%= medical_billing.id %>" style="cursor:pointer" class="edit_medicalbilling">
                	<i class="glyphicon glyphicon-edit" style="color:#0033FF">&nbsp;</i>
                </a>
        	</td>
            <% if (partie_id=="") { %>
            <td align="left" style="font-size:1em;" nowrap="nowrap">
            	<a href="#parties/<%=current_case_id %>/<%=medical_billing.corporation_id %>/medical_provider" title="Click to review Medical Provider Info" class="white_text"><%= medical_billing.company_name %></a>
            </td>
            <% } %>
            <td align="right" style="font-size:1em;"><%= medical_billing.bill_date %></td>
            <td align="right" style="font-size:1em;">$<%= formatDollar(medical_billing.billed) %></td>
            <td align="right" style="font-size:1em;">$<%= formatDollar(medical_billing.paid) %></td>
            <td align="right" style="font-size:1em;">$<%= formatDollar(medical_billing.adjusted) %></td>
            <!--
            <td align="right" style="font-size:1em;">
            	$<% //formatDollar(medical_billing.balance) %>
            </td>
            
            <td align="right" style="font-size:1em;">
            	<% //medical_billing.override %>
            </td>
            -->
            <td align="left" style="font-size:1em;">&nbsp;</td>
            <td align="left" style="font-size:1em;"><%= medical_billing.finalized %></td>
            <td align="left" style="font-size:1em;" nowrap="nowrap"><%= medical_billing.user_name %></td>
            <% if (!embedded) { %>
            <td align="left" style="font-size:1em;"><%= medical_billing.still_treating %></td>
            <td align="left" style="font-size:1em;"><%= medical_billing.prior %></td>
            <td align="left" style="font-size:1em;"><%= medical_billing.lien %></td> 
            <% } %>
            <td align="left" nowrap="nowrap">
            	<a id="medicalbilling_delete_<%= medical_billing.id %>" style="cursor:pointer" class="delete_medicalbilling">
                	<i class="glyphicon glyphicon-trash" style="color:#FF0000;"></i>
                </a>
            </td>            
        </tr>
        <% }); %>
        <tr id="medical_billing_totals_row">
        	<td align="left" style="font-size:1em;" nowrap="nowrap">&nbsp;</td>
            <% if (partie_id=="") { %>
            <td align="left" style="font-size:1em;" nowrap="nowrap">&nbsp;</td>
            <% } %>
             <td align="left" style="font-size:1em;" nowrap="nowrap">&nbsp;</td>
            <td align="right" style="font-size:1em; background:black">$<%= formatDollar(total_billed) %></td>
            <td align="right" style="font-size:1em;">$<%= formatDollar(total_paid) %></td>
            <td align="right" style="font-size:1em;">$<%= formatDollar(total_adjusted) %></td>
            <td align="right" style="font-size:1em; background:black">$<%= formatDollar(total_billed - total_paid + total_adjusted) %></td>
            <!--
            <td align="left" style="font-size:1em;">&nbsp;</td>
            -->
            <td align="left" style="font-size:1em;" nowrap="nowrap">&nbsp;</td>
            <td align="left" style="font-size:1em;" nowrap="nowrap">&nbsp;</td>
            <% if (!embedded) { %>
            <td align="left" style="font-size:1em;">&nbsp;</td>
            <td align="left" style="font-size:1em;">&nbsp;</td>
            <td align="left" style="font-size:1em;">&nbsp;</td>
            <% } %> 
            <td align="left" nowrap="nowrap">&nbsp;</td>
        </tr>
        </tbody>
    </table>
</div>
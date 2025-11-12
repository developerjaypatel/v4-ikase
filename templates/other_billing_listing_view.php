<?php
require_once('../shared/legacy_session.php');
session_write_close();
?>
<div class="other_billing">
	<div class="glass_header">
        <div style="float:right;" class="white_text">
        <input id="case_id" name="case_id" type="hidden" value="<%=case_id %>" />
			<div class="btn-group">            	
            	<label for="other_billings_searchList" id="label_search_other_billing" style="width:120px; font-size:1em; cursor:text; position:relative; top:0px; left:125px; color:#999; margin-left:0px; margin-top:0px; border:#00FF00 0px solid;">Search</label>
                <input id="other_billings_searchList" type="text" class="search-field" placeholder="" autocomplete="off" onkeyup="findIt(this, 'other_billing_listing', 'other_billing')" style="height:25px; line-height:32px; margin-top:-5px" />
				<a id="other_billings_clear_search" style="position: absolute;
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
        
        <span style="font-size:1.2em; color:#FFFFFF">Other Billing
            &nbsp;(<%=other_billings.length %>)&nbsp;&nbsp;&nbsp;&nbsp;
        </span>
        <button id="otherbilling_button" class="compose_new_otherbilling btn btn-sm btn-primary" title="Click to create a new Other Billing" style="margin-top:-5px">New Other Billing</button> 
    </div>
    <table id="other_billing_listing" class="tablesorter other_billing_listing" border="0" cellpadding="0" cellspacing="1">
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
        _.each( other_billings, function(other_billing) {
        %>
        <tr class="other_billing_data_row billing_data_row_<%= other_billing.id %>">
        	<td align="left" style="font-size:1em;" nowrap="nowrap">
            	<a id="otherbilling_edit_<%= other_billing.id %>" style="cursor:pointer" class="edit_otherbilling">
                	<i class="glyphicon glyphicon-edit" style="color:#0033FF">&nbsp;</i>
                </a>
        	</td>
            <% if (partie_id=="") { %>
            <td align="left" style="font-size:1em;" nowrap="nowrap">
            	<a href="#parties/<%=current_case_id %>/<%=other_billing.corporation_id %>/other_provider" title="Click to review Other Provider Info" class="white_text"><%= other_billing.company_name %></a>
            </td>
            <% } %>
            <td align="right" style="font-size:1em;"><%= other_billing.bill_date %></td>
            <td align="right" style="font-size:1em;">$<%= formatDollar(other_billing.billed) %></td>
            <td align="right" style="font-size:1em;">$<%= formatDollar(other_billing.paid) %></td>
            <td align="right" style="font-size:1em;">$<%= formatDollar(other_billing.adjusted) %></td>
            <!--
            <td align="right" style="font-size:1em;">
            	$<% //formatDollar(other_billing.balance) %>
            </td>
            
            <td align="right" style="font-size:1em;">
            	<% //other_billing.override %>
            </td>
            -->
            <td align="left" style="font-size:1em;">&nbsp;</td>
            <td align="left" style="font-size:1em;"><%= other_billing.finalized %></td>
            <td align="left" style="font-size:1em;" nowrap="nowrap"><%= other_billing.user_name %></td>
            <% if (!embedded) { %>
            <td align="left" style="font-size:1em;"><%= other_billing.still_treating %></td>
            <td align="left" style="font-size:1em;"><%= other_billing.prior %></td>
            <td align="left" style="font-size:1em;"><%= other_billing.lien %></td> 
            <% } %>
            <td align="left" nowrap="nowrap">
            	<a id="otherbilling_delete_<%= other_billing.id %>" style="cursor:pointer" class="delete_otherbilling">
                	<i class="glyphicon glyphicon-trash" style="color:#FF0000;"></i>
                </a>
            </td>            
        </tr>
        <% }); %>
        <tr id="other_billing_totals_row">
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

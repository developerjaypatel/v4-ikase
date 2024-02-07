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
<div>
	<div class="glass_header">
    	<div style="width:500px">
			<span style="font-size:1.2em; color:#FFFFFF"><%=page_title %></span>
        </div>
    </div>
    <table id="billable_listing" class="tablesorter billable_listing" border="0" cellpadding="0" cellspacing="0">
        <thead>
        <tr>
            <th align="left" valign="top" style="font-size:1.5em; width:200px">
                Case
            </th>
            <th align="left" valign="top" style="font-size:1.5em; width:200px">&nbsp;
                
            </th>
            <th align="left" valign="top" style="font-size:1.5em" width="50px">
                Trust
            </th>
            <th align="left" valign="top" style="font-size:1.5em" width="50px">&nbsp;</th>
            <th align="left" valign="top" style="font-size:1.5em" width="50px">
            	Operating
            </th>
            <th align="left" valign="top" style="font-size:1.5em" width="50px">&nbsp;</th>
            <th align="left" valign="top" style="font-size:1.5em" width="50px">
            	Billable
            </th>
            <th align="left" valign="top" style="font-size:1.5em">
            	Assigned To
            </th>
        </tr>
        </thead>
        <tbody>
       <% _.each( billables, function(billable) {
       	%>
       	<tr class="billable_data_row billable_data_row_<%= billable.id %>">
                <td align="left" valign="top" style="font-size:1.5em;" nowrap="nowrap">
                	<a href="#kase/<%= billable.case_id %>" class="white_text"><%=billable.case_name %></a>
                </td>
                <td align="left" valign="top" style="font-size:1.5em;" nowrap="nowrap">
                	<button id="review_<%= billable.case_id %>" class="btn btn-xs review_billable">Review&nbsp;Case&nbsp;Billables</button>
                    &nbsp;
                    <button id="books_<%= billable.case_id %>" class="btn btn-xs btn-success review_books">Books</button>
                    
                    <input type="hidden" id="trust_id_<%= billable.case_id %>" value="<%= billable.trust_account_id %>" />
                    <input type="hidden" id="operating_id_<%= billable.case_id %>" value="<%= billable.operating_account_id %>" />
                </td>
                <td align="right" valign="top" style="font-size:1.5em">
                	<%= billable.trust_checks %>
                </td>
                <td align="right" valign="top" style="font-size:1.5em">
                	<% if (billable.trust_account_id!="") { %>
                	<button id="add_trust_check_<%= billable.case_id %>" class="btn btn-sm btn-primary add_check" title="Click to add a Check to Trust Account" style="margin-top:-5px; display:">Deposit $ into Trust</button>
                    <% } else { %>
                    &nbsp;
                    <% } %>
                </td>
                <td align="right" valign="top" style="font-size:1.5em">
                	<%= billable.operating_checks %>
                </td>
                <td align="right" valign="top" style="font-size:1.5em">
                	<% if (billable.operating_account_id!="") { %>
                	<button id="add_operating_check_<%= billable.case_id %>" class="btn btn-sm btn-primary add_check" title="Click to add a Check to Operating Account" style="margin-top:-5px; display:">Deposit $ into Operating</button>
                    <% } else { %>
                    &nbsp;
                    <% } %>
                </td>
                <td align="right" valign="top" style="font-size:1.5em">
                	$<%=formatDollar(billable.billable) %>
                </td>
                <td align="left" valign="top" style="font-size:1.5em">
                	<%=billable.assigneds %>
                </td>
        </tr>
        <tr class="billable_checks_row billable_checks_row_<%= billable.case_id %>" style="display:none">
        	<td align="left" valign="top" style="font-size:1.5em;" colspan="8" id="billable_checks_<%= billable.case_id %>">
            </td>
        </tr>
        <% }); %>
        </tbody>
    </table>
</div>
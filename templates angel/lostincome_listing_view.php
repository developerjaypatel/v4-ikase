<?php
include("../api/manage_session.php");
session_write_close();
?>
<div id="confirm_delete" style="display:none; position:absolute; top:20px; color:white; background:black; border:1px solid white; font-size:1.2em; padding:10px; text-align:center; z-index:1051">
	<div style="width:350px; margin-left:auto; margin-right:auto; ">
    <input type="hidden" name="confirm_delete_id" id="confirm_delete_id" value="" />
    Are you sure you want to delete this lostincome?
    <div style="padding:5px; text-align:center"><a id="delete_lostincome" class="delete_yes white_icon" style="cursor:pointer">YES</a></div>
    <div style="padding:5px; text-align:center"><a id="cancel_lostincome" class="delete_no white_icon" style="cursor:pointer">NO</a></div>
    </div>
</div>
<div>
	<div class="glass_header">
    	<div style="width:215px">
            <div style="float:right">
                <button id="new_lostincome_button" class="compose_new_lostincome btn btn-sm btn-primary" title="Click to create a new Lost Wages entry" style="margin-top:-5px">New Lost Wages</button> 
            </div>
            <span style="font-size:1.2em; color:#FFFFFF"><%=page_title %></span>
        </div>
    </div>
    <% if (lostincomes.length > 0) { %>
    <table id="lostincome_listing" class="tablesorter lostincome_listing" border="0" cellpadding="0" cellspacing="0">
        <thead>
        <tr>
            <th align="left" valign="top" style="font-size:1.5em; width:140px">
                Start&nbsp;Date
            </th>
            <th align="left" valign="top" style="font-size:1.5em; width:100px">
                End&nbsp;Date
            </th>
            <th align="left" valign="top" style="font-size:1.5em" width="1%">
                Pay
            </th>
            <th align="left" valign="top" style="font-size:1.5em" width="1%">
                Lost&nbsp;Wages
            </th>
            <th align="left" valign="top" style="font-size:1.5em">
                Comments
            </th>
            <th align="left" valign="top">&nbsp;</th>
        </tr>
        </thead>
        <tbody>
       <% _.each( lostincomes, function(lostincome) {
       	%>
       	<tr class="lostincome_data_row lostincome_data_row_<%= lostincome.id %>">
                <td align="left" valign="top" style="font-size:1.5em;">
                	<div style="float:right">
                    	<a id='lostincome_<%= lostincome.id %>' class="edit_lostincome list-item_kase" style="color:white; cursor:pointer"><i class="glyphicon glyphicon-edit" style="color:#0033FF">&nbsp;</i></a>
                    </div>
	                <%=lostincome.start_lost_date %>
                </td>
                <td align="left" valign="top" style="font-size:1.5em">
                	<%= lostincome.end_lost_date %>
                </td>
                <td align="right" valign="top" style="font-size:1.5em" nowrap="nowrap">
                	<%= lostincome.perName %>
                </td>
                <td align="right" valign="top" style="font-size:1.5em">
                	$<%= formatDollar(lostincome.amount) %>
                </td>
                <td align="left" valign="top" style="font-size:1.5em">
                	<%= lostincome.comments %>
                </td>
                <td align="right" valign="top">
                	<a class="delete_icon" id="confirmdelete_lostincome_<%= lostincome.id %>" title="Click to delete lostincome" style="cursor:pointer"><i class="glyphicon glyphicon-trash" style="color:#FF0000;">&nbsp;</i></a>
                </td>
        </tr>
       	<% }); %>
        <tr>
            <td align="left" valign="top" style="font-size:1.5em;">&nbsp;</td>
            <td align="left" valign="top" style="font-size:1.5em">&nbsp;</td>
            <td align="right" valign="top" style="font-size:1.5em" nowrap="nowrap">Total</td>
            <td align="right" valign="top" style="font-size:1.5em; background:black">$<%=formatDollar(total_lost_income) %></td>
            <td align="left" valign="top" style="font-size:1.5em">&nbsp;</td>
            <td align="right" valign="top">&nbsp;</td>
        </tr>
        </tbody>
    </table>
    <% } %>
</div>
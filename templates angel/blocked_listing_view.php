<?php
include("../api/manage_session.php");

if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
	header("location:index.html");
	die();
}
?>
<div>
	<div class="glass_header">
	  <div style="width:250px">
       	  <span style="font-size:1.2em; color:#FFFFFF" id="blocked_form_title">Blocked Kalendar Dates</span> (<%=blockeds.length %>)
        </div>
    </div>
    <% if (blockeds.length == 0) { %>
    <div class="large_white_text" style="margin-top:20px">No blockeds.</div>
    <% } %>
    <table id="blocked_listing" class="tablesorter blocked_listing" border="0" cellpadding="0" cellspacing="0" style="font-size:1.2em">
        <thead>
        <% if (blockeds.length > 0) { %>
        <tr>
            <!--
            <th align="left">
            	&nbsp;
            </th>
            -->
            <th align="left" style="font-size:1.2em">
            	Employee
            </th>
            <th style="font-size:1.2em">
            	Start Date
            </th>
            <th style="font-size:1.2em">
            	End Date
            </th>
            <th style="font-size:1.2em">
            	Recurrring
            </th>
            <th style="font-size:1.2em">
            	Span
            </th>
            <th style="font-size:1.2em">
            	&nbsp;
            </th>
        </tr>
        <% } %>
        </thead>
        <tbody>
       <% _.each( blockeds, function(blocked) { %>
       	<% 
        	if (blocked.recurring_count==9999) {
        		blocked.recurring_count = "Forever";
            }
            blocked.recurring_span = blocked.recurring_span.replace("_", " ").capitalizeWords();
            
            if (blocked.user_name=="") {
            	blocked.user_name = "All Employees";
            }
        %>
       	<tr class="blocked_data_row blocked_row_<%=blocked.blocked_id%>">
        	<!--
            <td nowrap="nowrap">
                    <a href="#blocked/<%=blocked.blocked_id %>" title="Click to Edit this Block">Edit</a>
            </td>
            -->
            <td nowrap="nowrap">
                    <%=blocked.user_name%>
            </td>
	        <td><%= moment(blocked.start_date).format("MM/DD/YYYY") %></td>
            <td><%= moment(blocked.end_date).format("MM/DD/YYYY") %></td>
            <td><%= blocked.recurring_count %></td>
            <td><%= blocked.recurring_span %></td>
            <td>
            	<a title="Click to delete Block" class="list_edit delete_blocked" id="deleteblocked_<%=blocked.blocked_id %>" onClick="javascript:composeDelete(<%=blocked.blocked_id%>, 'blocked');" data-toggle="modal" data-target="#deleteModal" style="cursor:pointer">
					<i style="font-size:15px; color:#FF3737; cursor:pointer" id="delete_blocked" class="glyphicon glyphicon-trash delete_blocked"></i></a>
            </td>
          </tr>
       	<% }); %>
        </tbody>
    </table>
</div>

<?php
require_once('../shared/legacy_session.php');
session_write_close();

if (strpos($_SESSION['user_role'], "admin") === false) {
	//die("<div class='white_text' style='text-align:center;padding-top:10px'>You lack permissions to be here, don't you?</div>");
}
?>
<div>
	<!--
    <div class="glass_header">
        <span style="font-size:1.2em; color:#FFFFFF">List of Users currently logged in to iKase</span>
    </div>
    -->
    <table id="user_listing" class="tablesorter currentuser_listing" border="0" cellpadding="0" cellspacing="0">
    	 <thead>
         <tr>
            <th style="font-size:1.5em; width:200px">
                Employee
            </th>
            <th style="font-size:1.5em width:200px">
                Last Login Time
            </th>
            <th style="font-size:1.5em">
            	IP Address
            </th>
        </tr>
        </thead>
        <tbody>
       <% _.each( users, function(user) {
       	%>
        <tr class="user_data_row user_data_row_<%= user.id %>">
            <td style="font-size:1.5em;">
            <%= user.user_name.toLowerCase().capitalizeWords() %>&nbsp;(<%= user.nickname %>)
            </td>
            <td style="font-size:1.5em;">
            <%= moment(user.timestamp).format("hh:mma") %>
            </td>
            <td style="font-size:1.5em;">
            <%= user.ip_address %>
            </td>
        </tr>
        <% }); %>
        </tbody>
    </table>
</div>

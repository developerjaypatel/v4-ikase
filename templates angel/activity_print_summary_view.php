<?php
include("../api/manage_session.php");
session_write_close();

if (strpos($_SESSION['user_role'], "admin") === false) {
	die("<div class='white_text' style='text-align:center;padding-top:10px'>You lack permissions to be here, don't you?</div>");
}
?>
<div style="border:#000000 1px solid; width:99%" align="center">
<table border="0" cellpadding="2" cellspacing="0" style="width:95%; border:red 0px solid;" align="center">  		
    
    <tr>
        <td width="77"><img src="img/ikase_logo_login.png" height="32" width="77"></td>
        <td align="left" colspan="6">
            <div style="float:right">
                <em>As of <?php echo date("m/d/y g:iA"); ?></em>
            </div>
            <span style="font-family:Arial, Helvetica, sans-serif; font-weight:bold; font-size:1.5em">&nbsp;</span>
        </td>
      </tr>
      
</table>
	<table border="1" align="left" style="border:green 1px solid; padding:10px">
        <tr>
        	<td colspan="2"><span style="font-size:1.5em; font-weight:bold">Activity Report</span><br /><br />
            </td>
        </tr>
        <tr>
        	<td colspan="2">    
            <div>
            From
    <input id="start_dateInput" class="range_dates custom_dtp_height_indicator" value="<%= moment(start_date).format('MM/DD/YYYY') %>" style="width:80px" placeholder="From Date" /> &nbsp;&nbsp; through &nbsp;&nbsp; <input id="end_dateInput" class="range_dates custom_dtp_height_indicator_right" value="<%= moment(end_date).format('MM/DD/YYYY') %>" style="width:80px" placeholder="Through Date" />
    	</div>
        <br />
    		</td>
        </tr>
        <tr>
        	<td colspan="1" align="left" valign="top">
            	Employee
            </td>
            <td colspan="1" align="left" valign="top">
            	Count
            </td>
        </tr>
        <tbody>
        <% _.each( activities, function(activity) { %>
   	  <tr align="left" valign="top">
            	<td>
                	<a href="#activity_list/<%= activity.user_id %>/<%=start_date %>/<%=end_date %>">
                    <%= activity.user_name.toLowerCase().capitalizeWords() %>
                    </a>
                </td>
                <td><%= activity.activity_count %></td>
          </tr>
        <% }); %>
        </tbody>
  </table>
  </div>
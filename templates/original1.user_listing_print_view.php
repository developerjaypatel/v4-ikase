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
	<table border="0" cellpadding="2" cellspacing="0" style="width:90%" align="center">  		
        <thead>
        <tr>
            <td width="77"><img src="img/ikase_logo_login.png" height="32" width="77"></td>
            <td align="left" colspan="6">
                <div style="float:right">
                    <em>as of <?php echo date("m/d/y g:iA"); ?></em>
                </div>
                <span style="font-family:Arial, Helvetica, sans-serif; font-weight:bold; font-size:1.5em">&nbsp;</span>
            </td>
          </tr>
        <tr>
            <th style="font-size:1.5em" align="center" colspan="6">
                <?php echo $_SESSION["user_customer_name"]; ?> Users Listing<br /><br />
            </th>
        </tr>
        </thead>
    </table>
    <table id="user_listing" class="tablesorter user_listing" border="0" cellpadding="0" cellspacing="0">
        <thead>
        <tr>
            <th style="font-size:1.5em; width:10%; text-align:left; border-bottom:1px solid black">
                User
            </th>
            <th style="font-size:1.5em; width:10%; text-align:left; border-bottom:1px solid black">
                Logon
            </th>
            <th style="font-size:1.5em; width:10%; text-align:left; border-bottom:1px solid black">
                Email
            </th>
            <th style="font-size:1.5em; width:10%; text-align:left; border-bottom:1px solid black">
                Role
            </th>
            <th style="font-size:1.5em; width:10%; text-align:left; border-bottom:1px solid black">
                Job
            </th>
            <th style="font-size:1.5em; width:10%; text-align:left; border-bottom:1px solid black">Active</th>
        </tr>
        </thead>
        <tbody>
       <% _.each( users, function(user) {
       			user.linkstyle = "";
                if (user.activated=="N") {
                	user.linkstyle = "background:orange";
                }
       	%>
       	<tr class="user_data_row user_data_row_<%= user.id %>">
                <td style="font-size:1.0em;" valign="top" align="left" nowrap="nowrap">
	                <%= user.user_name %>&nbsp;(<%= user.nickname %>)
                </td>
                <td style="font-size:1.0em;" valign="top" align="left" nowrap="nowrap">
	                <%= user.user_logon %>
                </td>
                <td style="font-size:1.0em" valign="top" align="left">
                	<%= user.user_email %>
                </td>
                <td style="font-size:1.0em;" valign="top" align="left">
                	<%=user.role %>
                </td>
                <td style="font-size:1.0em;" valign="top" align="left">
	                <%=user.job %>
                </td>
                <td valign="top" align="left">
                	<%= user.activated %>
                </td>
        </tr>
        
        <% }); %>
        </tbody>
    </table>
</div>
<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
include("../api/manage_session.php");
?>
<div>
    <table align="center" width="1080px">
    	<tr>
            <td style="border:0px solid blue"><img src="img/ikase_logo_login.png" height="32" width="77"></td>
            <td align="left" colspan="2">
            	<div style="float:right; margin-top:10px">
                    <em>as of <?php echo date("m/d/y g:iA"); ?></em>
                    <br />
                    <button class="btn btn-primary" id="send_xl">Send to XL</button>
                </div>
            	<span style="font-size:1.5em; margin-top-3px; margin-left:10px">Employers Report :: <?php echo $_SESSION["user_customer_name"]; ?>
                </span>
             </td>
		</tr>
	</table>
    <hr />
     <table id="employers_listing" class="tablesorter employers_listing" border="0" cellpadding="0" cellspacing="1" align="center">
        <tr>
        	<th align="left" valign="top">Name</th>
            <th align="left" valign="top">Address</th>
            <th align="left" valign="top">Phone</th>
        </tr>
        </thead>
        <tbody>
        <%
        _.each( parties, function(partie) {
        %>
        <tr class="partie_data_row">
            <td align="left" valign="top">
            	<%= partie.company_name %>
            </td>
            <td align="left" valign="top">
            	<%= partie.full_address %>
            </td>
            <td align="left" valign="top">
            	<%= partie.phone %>
            </td>
        </tr>
        <% 
        }); %>
        </tbody>
    </table>
</div>

<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include("../api/manage_session.php");
session_write_close();

$blnAdmin = (strpos($_SESSION['user_role'], "admin"));
?>
<div id="confirm_delete" style="display:none; position:absolute; top:20px; color:white; background:black; border:1px solid white; font-size:1.2em; padding:10px; text-align:center; z-index:1051">
	<div style="width:350px; margin-left:auto; margin-right:auto; ">
    <input type="hidden" name="confirm_delete_id" id="confirm_delete_id" value="" />
    Are you sure you want to delete this rx?
    <div style="padding:5px; text-align:center"><a id="delete_partie" class="delete_yes white_icon" style="cursor:pointer">YES</a></div>
    <div style="padding:5px; text-align:center"><a id="cancel_partie" class="delete_no white_icon" style="cursor:pointer">NO</a></div>
    </div>
</div>
<div class="rx_listing">
	<div id="glass_header" class="glass_header" style="height:45px">
        
       	<div style="width:180px">
        	<div style="float:right">
           
            <a title="Click to compose a new Rx" class="compose_new_rx" id="compose_new_<%=person_id %>" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer"><i class="glyphicon glyphicon-plus-sign" style="color:#99FFFF">&nbsp;</i></a>  
            </div>
            <span style="font-size:1.2em; color:#FFFFFF">Prescriptions</span>&nbsp;<div style="position: relative;left: 110px; padding-left:3px; margin-top:-19px; color:white; font-size:0.8em; width:20px">(<%=rxs.length %>)</div>
        </div>        
    </div>
    <div id="rx_preview_panel" style="position:absolute; width:200px; display:none; z-index:2; background:white; border:1px solid pink" class="attach_preview_panel"></div>
    <table id="rx_listing" class="tablesorter rx_listing" border="1" cellpadding="0" cellspacing="0" width="100%">
        <thead>
        <tr>
            <th style="font-size:1.5em; width:10%; text-align:left">
                Rx ID
            </th>
            <!--
            <th style="font-size:1.5em; width:10%; text-align:left">
                Medication
            </th>
            -->
            <th style="font-size:1.5em; width:10%; text-align:left">
                Dates
            </th>
            <th style="font-size:1.5em; width:5%; text-align:left;">
                Doctor
            </th>
            <th style="font-size:1.5em; width:75%; text-align:left">
                Rx
            </th>
            <th style="font-size:1.5em; text-align:left; width:25px">&nbsp;
            	
            </th>
        </tr>
        </thead>
        <tbody>
       <% 
       var current_day = "";
       _.each( rxs, function(rx) { %>
       	<tr class="rx_data_row rx_row_<%= rx.id %>" style="border:#00CC00 0px solid;">
			<td style="font-size:1.5em; width:1%;" align="left" valign="top" nowrap="nowrap">
            	<div style="float:right">
                	<a class="edit_rx white_text" id="edit_rx_<%= rx.id %>_<%=person_id %>" style="cursor:pointer"><i style="font-size:15px;color:#06f; cursor:pointer; visibility:visible" class="glyphicon glyphicon-edit" title="Click to Edit Rx"></i></a>
                </div>
                <%= rx.id %>
            </td>
            <!--
            <td style="font-size:1.5em; width:5%;" align="left" valign="top" nowrap="nowrap">
                <%= rx.medication %>
            </td>
            -->
            <td style="font-size:1.5em; width:75px;" align="left" valign="top" nowrap="nowrap">
                <%= moment(rx.start_date).format("MM/DD/YYYY") %>&nbsp;through&nbsp;<%= moment(rx.end_date).format("MM/DD/YYYY") %>
            </td>
            <td style="font-size:1.5em; width:5%;" align="left" valign="top" nowrap="nowrap">
                <%= rx.doctor %>
            </td>
            <td style="font-size:1.5em; width:75%;" align="left" valign="top">
                <%= rx.notes.replaceAll("\r", "<br>") %>
            </td>
            <td>
            <?php if (strpos($_SESSION['user_role'], "admin") !== false) { ?>
                <i style="font-size:15px; color:#FF3737; cursor:pointer" class="glyphicon glyphicon-trash delete_rx" id="delete_<%= rx.id %>" title="Click to delete"></i>
            <?php } ?>
            </td>
        </tr>
        <% }); %>
        </tbody>
    </table>
</div>

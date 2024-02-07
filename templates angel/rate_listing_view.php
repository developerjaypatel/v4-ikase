<?php
include("../api/manage_session.php");
session_write_close();
?>
<div id="confirm_delete" style="display:none; position:absolute; top:20px; color:white; background:black; border:1px solid white; font-size:1.2em; padding:10px; text-align:center; z-index:1051">
	<div style="width:350px; margin-left:auto; margin-right:auto; ">
    <input type="hidden" name="confirm_delete_id" id="confirm_delete_id" value="" />
    Are you sure you want to delete this rate?
    <div style="padding:5px; text-align:center"><a id="delete_user" class="delete_yes white_icon" style="cursor:pointer">YES</a></div>
    <div style="padding:5px; text-align:center"><a id="cancel_user" class="delete_no white_icon" style="cursor:pointer">NO</a></div>
    </div>
</div>
<div id="rate_view_holder" style="float:right">
</div>
<div>
	<div class="glass_header">
        <div style="float:right;">
        	<label for="rates_searchList" id="label_search_rates" style="font-size:1em; cursor:text; position:relative; top:0px; left:105px; width:100px; color:#999">Search</label>
            
				<input id="rates_searchList" type="text" class="search-field" placeholder="" autocomplete="off" onkeyup="findIt(this, 'rate_listing', 'rate')" style="height:25px; line-height:32px; margin-top:-5px">
				<a id="rate_clear_search" style="position: absolute;
				right: 2px;
				top: 0;
				bottom: 9px;
				height: 14px;
				margin: auto;
				cursor: pointer;
				"><i class="glyphicon glyphicon-remove-circle" style='font-size:1em; color:#cccccc'></i></a>
        </div>
       
        <span style="font-size:1.2em; color:#FFFFFF">Rates</span>&nbsp;&nbsp;<span style="color:white">(<%=rates.length %>)</span>
    </div>
    <table id="rate_listing" class="tablesorter rate_listing" border="0" cellpadding="0" cellspacing="0">
        <thead>
        <tr>
            <th style="font-size:1.5em; width:200px">
                Name
            </th>
            <th style="font-size:1.5em;">
                Descripton
            </th>
            <th>Created On</th>
        </tr>
        </thead>
        <tbody>
       <% _.each( rates, function(rate) {
       %>
       	<tr class="rate_data_row rate_data_row_<%= rate.id %>">
                <td style="font-size:1.5em; width:200px" align="left" valign="top">
                	<a id="rate_<%= rate.id %>" class="list-item_kase rate_link" style="color:white"><%= rate.rate_name %></a>
                </td>
                <td style="font-size:1.5em; margin-left:0px" align="left" valign="top" nowrap="nowrap">
                	<%= rate.rate_description %>
                </td>
                <td style="font-size:1.5em; width:400px" align="left" valign="top">
                	<%= moment(rate.create_date).format("MM/DD/YYYY") %>
                </td>
				<td style="font-size:1.5em;" align="left" valign="top">
                	<a class="delete_icon" id="confirmdelete_rate_<%= rate.rate_id %>" title="Click to delete Rate" style="cursor:pointer"><i class="glyphicon glyphicon-trash" style="color:#FF0000;">&nbsp;</i></a>
                </td>
        </tr>
        
        <% }); %>
        </tbody>
    </table>
</div>
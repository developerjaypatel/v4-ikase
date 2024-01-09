<?php require_once(APILIB_PATH.'legacy_session.php');
session_write_close();
?>
<div>
	<% if (!embedded) { %>
    <div id="adjustment_listing_header" class="glass_header">
        <div style="float:right;">
        <input id="case_id" name="case_id" type="hidden" value="<%=current_case_id %>" />
            
            <div class="btn-group">
                
                 <label for="adjustments_searchList" id="label_search_adjustment" style="width:120px; font-size:1em; cursor:text; position:relative; top:0px; left:125px; color:#999; margin-left:0px; margin-top:0px; border:#00FF00 0px solid;">Search</label>
                
                <input id="adjustments_searchList" type="text" class="search-field" placeholder="" autocomplete="off" onkeyup="findIt(this, 'adjustment_listing', 'adjustment')">
                <a id="adjustments_clear_search" style="position: absolute;
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
        <span style="font-size:1.2em; color:#FFFFFF">Adjustments/Interest</span>
        &nbsp;&nbsp;<span class="white_text">(<%=adjustments.length %>)</span>
        
        &nbsp;
        <button id="new_adjustment" class="btn btn-sm btn-primary btn_adjustment" title="Click to add a new Deduction" style="margin-top:-5px">New Deduction</button> 
    </div>
    <% } %>
    <table id="adjustment_listing" class="tablesorter adjustment_listing adjustment_listing_<%=page_title %>" border="0" cellpadding="0" cellspacing="1">
        <thead>
        <tr>
        	<th width="1%">&nbsp;
                
            </th>
        	<th width="1%">
                Date
            </th>
            <th width="1%">
                Amount
            </th>
            <th width="1%">
                Type
            </th>
            <th align="left" width="350px">
                Description
            </th>
            <th width="1%">&nbsp;</th>
        </tr>
        </thead>
        <tbody>
        <% 
        var totals_adjust = 0;
        _.each( adjustments, function(adjustment) {
            totals_adjust += Number(adjustment.amount);
        %>
        <tr class="adjustment_data_row adjustment_data_row_<%= adjustment.id %>">
        	<td align="left" valign="top">
            	<a id="edit_adjustment_<%=adjustment.id %>" class="edit_adjustment">
                	<i style="font-size:15px; color:#a9bafd; cursor:pointer" class="glyphicon glyphicon-edit"></i>
                </a>
            </td>
            <td align="left" valign="top"><%= adjustment.adjustment_date %></td>
            <td align="right" valign="top" nowrap="nowrap">
            	$<%= formatDollar(adjustment.amount) %>
            </td>
            <td align="right" valign="top" nowrap="nowrap">
            	<% if(adjustment.adjustment_type=="A") { %>
                Adjustment
                <% } %>
                <% if(adjustment.adjustment_type=="I") { %>
                Interest
                <% } %>
            </td>
            <td align="left" valign="top"><%= adjustment.description %></td>
            <td>
            	<i style="font-size:15px; color:#FF3737; cursor:pointer" class="glyphicon glyphicon-trash delete_adjustment" id="delete_<%= adjustment.id %>" title="Click to delete"></i>
            </td>
        </tr>
        <% }); %>
        <% if (adjustments.length > 0) { %>
        <tr style="background:black">
            <td align="left" valign="top">&nbsp;</td>
            <td align="right" valign="top">&nbsp;</td>
            <td align="right" valign="top">$<%=formatDollar(totals_adjust) %></td>
            <td align="right" valign="top">&nbsp;</td>
            <td align="left" valign="top">&nbsp;</td>
            <td align="left" valign="top">&nbsp;</td>
        </tr>
        <% } %>
        </tbody>
    </table>
</div>
<div id="adjustment_listing_all_done"></div>
<script language="javascript">
$( "#adjustment_listing_all_done" ).trigger( "click" );
</script>

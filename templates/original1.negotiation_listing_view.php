<?php include("../api/manage_session.php"); 
session_write_close();
?>
<div>
	<div id="negotiation_listing_header" class="glass_header">
        <div style="float:right;">
        <input id="case_id" name="case_id" type="hidden" value="<%=current_case_id %>" />
            
            <div class="btn-group">
                
                 <label for="negotiations_searchList" id="label_search_negotiation" style="width:120px; font-size:1em; cursor:text; position:relative; top:0px; left:125px; color:#999; margin-left:0px; margin-top:0px; border:#00FF00 0px solid;">Search</label>
                
                <input id="negotiations_searchList" type="text" class="search-field" placeholder="" autocomplete="off" onkeyup="findIt(this, 'negotiation_listing', 'negotiation')">
                <a id="negotiations_clear_search" style="position: absolute;
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
        <span style="font-size:1.2em; color:#FFFFFF"><%=page_title %>s</span>
        &nbsp;&nbsp;<span class="white_text">(<%=negotiations.length %>)</span>
        
        &nbsp;
        <button id="new_offer" class="btn btn-sm btn-primary btn_negotiation" title="Click to add a new Offer" style="margin-top:-5px">New Offer</button> 
        &nbsp;
        <button id="new_demand" class="btn btn-sm btn-primary btn_negotiation" title="Click to add a new Demand" style="margin-top:-5px">New Demand</button> 
    </div>
    <table id="negotiation_listing" class="tablesorter negotiation_listing negotiation_listing_<%=page_title %>" border="0" cellpadding="0" cellspacing="1">
        <thead>
        <tr>
        	<th width="1%">&nbsp;
                
            </th>
        	<th width="1%">
                Date
            </th>
        	<th width="1%">&nbsp;
            	
            </th>
            <th width="1%">
                Amount
            </th>
            <!--
            <% if (!embedded) { %>
            <th width="1%">
                Company
            </th>
            <th width="1%">
                Negotiator
            </th>
            <% } %>
            -->
            <th width="1%">
                By
            </th>
            <th align="left" width="350px">
                Description
            </th>
            <th width="1%">&nbsp;</th>
        </tr>
        </thead>
        <tbody>
        <% 
        var current_neg = "";
        _.each( negotiations, function(negotiation) {
			if (current_neg != negotiation.firm && negotiation.firm!="" && !embedded) {
            	current_neg = negotiation.firm;
        %>
        <tr class="firm_row firm_row_<%=negotiation.corporation_id %>">
        	<td align="left" valign="top" colspan="7">
            	<div style="float:right">
                	<button id="new_offer_<%=negotiation.corporation_id %>" class="btn btn-xs btn-primary btn_firm_negotiation" title="Click to add a new Offer for this Firm" style="margin-top:-5px">New Offer</button> 
                    &nbsp;
                    <button id="new_demand_<%=negotiation.corporation_id %>" class="btn btn-xs btn-primary btn_firm_negotiation" title="Click to add a new Demand for this Firm" style="margin-top:-5px">New Demand</button>
                </div>
            	 <div style="width:100%; 
                text-align:left;
                padding-left:5px;  
                font-size:1.8em; 
                background:#CFF; 
                color:red;">
                <%=negotiation.firm %> - <%=negotiation.negotiator %>
                </div>
            </td>
        </tr>
        <% } %>
        <tr class="negotiation_data_row negotiation_data_row_<%= negotiation.id %>">
        	<td align="left" valign="top">
            	<a id="edit_negotiation_<%=negotiation.id %>" class="edit_negotiation">
                	<i style="font-size:15px; color:#a9bafd; cursor:pointer" class="glyphicon glyphicon-edit"></i>
                </a>
            </td>
            <td align="left" valign="top"><%= negotiation.negotiation_date %></td>
            <td align="left" valign="top" nowrap="nowrap">
            	<% if (negotiation.negotiation_type=="O") { %>
                Offer
                <% } else { %>
                Demand
                <% } %>
            </td>
            <td align="right" valign="top" nowrap="nowrap">
            	$<%= formatDollar(negotiation.amount) %>
            </td>
            <!--
            <% if (!embedded) { %>
            <td align="left" valign="top" nowrap="nowrap">
            	<%= negotiation.firm %>
            </td>
            <td align="left" valign="top" nowrap="nowrap">
            	<%= negotiation.negotiator %>
            </td>
            <% } %>
            -->
            <td align="left" valign="top" nowrap="nowrap">
            	<%= negotiation.worker %>
            </td>
            <td align="left" valign="top"><%= negotiation.comments %></td>
            <td>
            	<i style="font-size:15px; color:#FF3737; cursor:pointer" class="glyphicon glyphicon-trash delete_negotiation" id="delete_<%= negotiation.id %>" title="Click to delete"></i>
            </td>
        </tr>
        <% }); %>
        </tbody>
    </table>
</div>
<div id="negotiation_listing_all_done"></div>
<script language="javascript">
$( "#negotiation_listing_all_done" ).trigger( "click" );
</script>
<?php
require_once('../shared/legacy_session.php');
session_write_close();

include("../api/connection.php");
?>
<div class="activity">
	<div class="glass_header">
    	<div style="float:right">
        	<div class="btn-group">
        	<label for="demographics_searchList" id="label_search_demographics" style="width:120px; font-size:1em; cursor:text; position:relative; top:0px; left:125px; color:#999; margin-left:0px; margin-top:0px; border:#00FF00 0px solid;">Search <i class="glyphicon glyphicon-search"></i></label>
            <input id="demographics_searchList" type="text" class="search-field" placeholder="" autocomplete="off" onkeyup="findIt(this, 'demographic_listing', 'demographic')">
            <a id="demographics_clear_search" style="position: absolute;
				right: 2px;
				top: 0;
				bottom: 9px;
				height: 14px;
				margin: auto;
				cursor: pointer;
				"><i class="glyphicon glyphicon-remove-circle" style='font-size:1em; color:#cccccc'></i></a>
            </div>
        </div>
    	<span style="font-size:1.2em; color:#FFFFFF">
    		Demographics Submissions
        </span>
    </div>
    
    <div id="demographic_right_pane" style="float:right; display:none; margin-right:10px">
    	<iframe id="demographic_preview_holder" width="100%" allowtransparency="1" frameborder="0"></iframe>
    </div>
    <div id="demographic_listing_holder" style="overflow-y:scroll;">
        <table id="demographic_listing" class="tablesorter demographic_listing" border="0" cellpadding="0" cellspacing="1">
            <thead>
            <tr>
                <th align="left" valign="top">
                    Case
                </th>
                <th align="left" valign="top">
                    Case&nbsp;#
                </th>
 				<th align="left" valign="top">
                    By
                </th>
                <th align="left" valign="top">
                    Date
                </th>
                <th align="left" valign="top">
                    Review
                </th>
            </tr>
            </thead>
            <tbody>
            <% 
            var current_month = "";
            _.each( demographics, function(demographic) {
                var the_month = moment(demographic.activity_date).format("MMMM YYYY");
                var the_display_month = "";
                if (current_month != the_month) {
                    current_month = the_month;
                    the_display_month = the_month;
                }
            %>
            <% if (the_display_month!="") { %>
                <tr>
                    <td align="left" valign="top" colspan="5">
                        <div style="width:100%; 
    text-align:left;
    padding-left:5px;  
    font-size:1.8em; 
    background:#CFF; 
    color:red;">
                        <%= the_display_month %>
                        </div>
                    </td>
                </tr>
            <% } %>
            <tr class="demographic_data_row demographic_data_row_<%=demographic.activity_id %>">
                <td valign="top" width="1%" nowrap="nowrap"><a href="#kase/<%= demographic.case_id %>" title="Click to review case" class="white_text"><%= demographic.case_name %></a></td>
                <td valign="top" width="1%" nowrap="nowrap">
                    <a href="#kase/<%= demographic.case_id %>" title="Click to review case" class="white_text"><%= demographic.case_number %></a>
                </td>
                <td valign="top" width="1%" nowrap="nowrap">
                    <%= demographic.user_name %>
                </td>
                <td valign="top" width="1%" nowrap="nowrap">
                    <%= demographic.activity_date %>
                </td>
                <td valign="top" nowrap="nowrap">
                    <a id="demographic_<%=demographic.case_id %>" title="Click to review demographics" class="white_text demographic_link">Preview</a>
                    &nbsp;|&nbsp;
                    <a href="/reports/demographics_sheet.php?case_id=<%= demographic.case_id %>" target="_blank" title="Click to open demographics" class="white_text demographic_link">Open</a>
                </td>
            </tr>
            <% 
            }); %>
            </tbody>
        </table>
    </div>
</div>

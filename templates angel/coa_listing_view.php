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
<div id="confirm_delete" style="display:none; position:absolute; top:20px; color:white; background:black; border:1px solid white; font-size:1.2em; padding:10px; text-align:center; z-index:1051">
	<div style="width:350px; margin-left:auto; margin-right:auto; ">
    <input type="hidden" name="confirm_delete_id" id="confirm_delete_id" value="" />
    Are you sure you want to delete this coa?
    <div style="padding:5px; text-align:center"><a id="delete_coa" class="delete_yes white_icon" style="cursor:pointer">YES</a></div>
    <div style="padding:5px; text-align:center"><a id="cancel_coa" class="delete_no white_icon" style="cursor:pointer">NO</a></div>
    </div>
</div>
<div>
	<div class="glass_header">
        <div style="float:right;">        
        <label for="coa_searchList" id="label_search_coa" style="width:120px; font-size:1em; cursor:text; position:relative; top:0px; left:125px; color:#999; margin-left:0px; margin-top:0px; border:#00FF00 0px solid;">Search COA</label>
            <input id="coa_searchList" type="text" class="search-field" placeholder="" autocomplete="off" onkeyup="findIt(this, 'coa_listing', 'coa')">
        </div>
       
        <span style="font-size:1.2em; color:#FFFFFF">COAs</span>
        &nbsp;&nbsp;<button class="edit btn btn-transparent border-green new_coa" id="newcoa_<%=case_id %>_<%= id %>" style="width:20px; border:0px solid"><i class="glyphicon glyphicon-plus-sign" style="color:#00CCFF" title="New COA" id="coa_<%=case_id %>_<%= id %>">&nbsp;</i></button>
    </div>
    <table id="coa_listing" class="tablesorter coa_listing" border="0" cellpadding="0" cellspacing="0">
        <thead>
        <tr>
        	<th width="1%">&nbsp;</th>
            <th style="font-size:1.5em;" width="5%">
                Type
            </th>
            <th style="font-size:1.5em" width="30%">
                Disposition
            </th>
            <th style="font-size:1.5em">
            	Resolution
            </th>
        </tr>
        </thead>
        <tbody>
        <% if (coas.length > 0) { %>
            <% _.each( coas, function(coa) {
            %>
            <tr class="coa_data_row coa_data_row_<%= coa.id %>">
                <td>
                	<a title="Click to edit COA" class="list_edit edit_coa" id="coa_<%= case_id %>_<%=id %>_<%=coa.id %>" style="cursor:pointer">
                	<i style="font-size:15px;color:#06f; cursor:pointer;" class="glyphicon glyphicon-edit"></i>
                    </a>
                </td>
                <td style="font-size:1.5em;" nowrap="nowrap">
                    <% if (typeof coa.type != "undefined") { %>
                        <%=coa.type %>
                    <% } %>
                </td>
                <td style="font-size:1.5em">
                <% if (typeof coa.disposition != "undefined") { %>
                    <%= coa.disposition %><br/><%= coa.disposition_explanation %>
                <% } %>
                </td>
                <td style="font-size:1.5em">
                <% if (typeof coa.resolution != "undefined") { %>
                    <%= coa.resolution %><br/><%= coa.resolution_explanation %>
                <% } %>
                </td>
            </tr>
            
            <% }); %>
        <% } %>
        </tbody>
    </table>
</div>
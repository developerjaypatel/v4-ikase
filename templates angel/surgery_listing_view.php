<?php include("../api/manage_session.php"); 
session_write_close();
?>
<div>
	<div id="surgery_listing_header" class="glass_header">
        <div style="float:right;">
        <input id="case_id" name="case_id" type="hidden" value="<%=current_case_id %>" />
            
            <div class="btn-group">
                
                 <label for="surgeries_searchList" id="label_search_surgery" style="width:120px; font-size:1em; cursor:text; position:relative; top:0px; left:125px; color:#999; margin-left:0px; margin-top:0px; border:#00FF00 0px solid;">Search</label>
                
                <input id="surgeries_searchList" type="text" class="search-field" placeholder="" autocomplete="off" onkeyup="findIt(this, 'surgery_listing', 'surgery')">
                <a id="surgeries_clear_search" style="position: absolute;
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
        <div style="width:312px">
            <div style="float:right">
            <button id="compose_new_surgery" class="btn btn-sm btn-primary" title="Click to add a Surgical Procedure" style="margin-top:-5px">Add Surgery</button> 
            </div>
            <span style="font-size:1.2em; color:#FFFFFF"><%=page_title %></span>
            &nbsp;&nbsp;<span class="white_text">(<%=surgeries.length %>)</span>
        </div>
         
    </div>
    <table id="surgery_listing" class="tablesorter surgery_listing surgery_listing_<%=page_title %>" border="0" cellpadding="0" cellspacing="1" style="font-size:1.1em">
        <thead>
        <tr>
        	<th >
                Procedure
            </th>
            <th width="1%">
                Date
            </th>
            <th>
                Memo
            </th>
        </tr>
        </thead>
        <tbody>
        <% 
        _.each( surgeries, function(surgery) {
        	var jdata = JSON.parse(surgery.surgery_info);
            var procedure = jdata.procedureInput;
            var surgery_date = jdata.surgery_dateInput;
            var memo = jdata.memoInput;
            
        %>
        <tr class="surgery_data_row surgery_data_row_<%= surgery.id %>">
            <td align="left" valign="top" nowrap="nowrap">
	            <%=procedure %>
            </td>
            <td align="left" valign="top" nowrap="nowrap"><%= surgery_date %></td>
            <td align="left" valign="top"><%=memo %></td>
        </tr>
        <% }); %>
        </tbody>
    </table>
</div>
<div id="surgery_listing_all_done"></div>
<script language="javascript">
$( "#surgery_listing_all_done" ).trigger( "click" );
</script>
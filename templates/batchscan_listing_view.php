<div id="batscan_listing" style="margin-left:10px">
    <% if (batchscans.length == 0) { %>
    <div class="large_white_text" style="margin-top:20px">No batchscans.</div>
    <% } %>
    <span style="font-size:1.2em; color:#FFFFFF">Batchscans</span>&nbsp;(<%=batchscans.length %>)
    <a title="Click to upload a new batchscan" href="#import" style="cursor:pointer"><i class="glyphicon glyphicon-plus" style="color:#99FFFF">&nbsp;</i></a> 
    <div id="batchscan_review" style="float:right; display:none; width:40%">
    	<iframe id="batchscan_frame" style="width:100%; height:500px"></iframe>
    </div>
    <div id="batchscan_preview_panel" style="position:absolute; width:800px; display:none; z-index:2"></div>
    <table id="batchscan_listing" class="tablesorter batchscan_listing" border="0" cellpadding="0" cellspacing="0" width="45%">
        <thead>
        <% if (batchscans.length > 0) { %>
        <tr>
            <th width="2%">&nbsp;</th>
            <th width="5%" align="left">
                Document
            </th>
            <th width="3%">&nbsp;</th>
            <th>
                Upload Date
            </th>
            <th>By</th>
            <th># Pages Uploaded</th>
            <th># Documents Extracted</th>
        </tr>
        <% } %>
        </thead>
        <tbody>
       <% var current_day = "";
       _.each( batchscans, function(batchscan) {
       		if (batchscan.id != "") {            
                //we might have a new day
                var the_day = moment(batchscan.dateandtime).format("MMDDYY");
                var date_string = moment(batchscan.dateandtime).valueOf();
                if (current_day != the_day) {
                    current_day = the_day;
                
                %>
                <tr class="date_row row_<%= the_day %>">
                    <td colspan="8">
                        <div style="width:100%; 
                            text-align:left; 
                            font-size:1.8em; 
                            background:#CFF; 
                            color:red;"><%= moment(batchscan.dateandtime).format("dddd, MMMM Do YYYY") %>
                         </div>
                    </td>
                </tr>
        <% } %>
        <tr class="batchscan_data_row kase_batchscan_row_<%=batchscans.batchscan_id%>">
            <td nowrap="nowrap" valign="top">
                <%=batchscan.id %>
            </td>
            <td nowrap="nowrap" valign="top">
                <a id="batchscan_<%=batchscan.id %>" class="batchscan_file white_text" style="cursor:pointer; text-decoration:underline" title="Click to review batchscan document"><%=batchscan.filename %>.pdf</a>
            </td>
            <td valign="top">
	            <%= batchscan.preview %>
            </td>
            <td nowrap="nowrap" valign="top">
                <%= batchscan.datestamp %>
            </td>
            <td nowrap="nowrap" valign="top">
                <%=batchscan.user_name %>
            </td>
            <td nowrap="nowrap" valign="top">
                <%=batchscan.pages %>
            </td>
            <td nowrap="nowrap" valign="top">
                <%=batchscan.stacks %>
            </td>
        </tr>
        <% 	}
        }); %>
       </tbody>
    </table>
</div>
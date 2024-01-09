<div style="font-size:1.6em; padding:20px; text-align:center" id="pending_email_info_holder">
<%=messages.length %> Pending Emails  
</div>
<div style="
    width: 80%;
    text-align: center;
    margin-left: auto;
    margin-right: auto;
">
	<div style="display:inline; margin-right:25px">
    	<button class="btn btn-sm btn-primary" id="review_pending_emails">Review</button>
    </div>
	<button class="btn btn-sm btn-info" id="dismiss_pending_emails">Dismiss</button>
</div>
<div style="width:100%; margin-left:auto; margin-right:auto; text-align:center; margin-top:10px">
	<span style="font-style:italic">This box will close automatically in 20 seconds</span>
</div>
<!--
<div>
    <div id="pendings_preview_panel" style="position:absolute; width:20px; display:none; z-index:2; background:white" class="attach_preview_panel"></div>
    <table id="pendings_listing" class="tablesorter pendings_listing" border="1" cellpadding="0" cellspacing="0" <?php echo $table_style; ?>>
        <thead>
        <tr>
        	<th style="border:0px">&nbsp;</th>
            <th style="font-size:1.2em; text-align:left; border:#FF9966 0px solid; width:150px">
                From
            </th>
            <th style="font-size:1.2em; text-align:left; border:#00CCCC 0px solid; width:550px">
                Subject
            </th>
            <th style="font-size:1.2em; text-align:left; border:#00CCCC 0px solid">
                Received
            </th>
            <th style="border:0px">&nbsp;</th>
        </tr>
        </thead>
        <tbody>
       <% 
       
       _.each( messages, function(message) {
       	//clean up
        var from = message.from.replaceAll("|", "");
        
       %>
       	<tr class="pendings_data_row pendings_row_<%= message.id %>" id="messagerow_<%= message.id %>">
        	<td nowrap="nowrap">
                <button class="btn btn-xs assign_case" id="assign_case_<%= message.id %>">Case</button>
                <div style="display:inline-block">
                    <a id="pendings_save_<%=message.id %>" class="save_icon" style="display:; cursor:pointer; color:white" title="Click to Save - Case is not required">Save w/o Kase</a>
                </div>
                <div style="display:none; position:absolute" id="case_lookup_<%= message.id %>">
                    <input type="text" id="pendings_case_<%=message.id%>" class="kase_input" placeholder="Type here to assign email to a kase" value="" />
                    <input type="hidden" id="pendings_case_id_<%=message.id %>" value="" />
                    
                </div>
            </td>
            <td align="left" valign="top" style="font-size:1.2em; border:#FF9966 0px solid" nowrap="nowrap">
                <%= from %>
            </td>
            <td align="left" valign="top" style="font-size:1.2em; border:#FF9966 0px solid">
                <%= message.subject %>
            </td>
            <td align="left" valign="top" style="font-size:1.2em; border:#FF9966 0px solid" nowrap="nowrap">
                <%= moment(message.dateandtime).format("MM/DD/YYYY hh:mma") %>
            </td>
            <td>
                <i style="font-size:15px; color:#FF3737; cursor:pointer" class="glyphicon glyphicon-trash delete_message" id="delete_<%= message.id %>" title="Click to delete"></i>
            </td>
        </tr>
        <% }); %>
        </tbody>
    </table>
    </div>
    

</div>
**>

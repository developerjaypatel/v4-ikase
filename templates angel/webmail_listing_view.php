<div>
	<div class="glass_header">
        <span style="font-size:1.2em; color:#FFFFFF">Emails</span>&nbsp;&nbsp;&nbsp;
        <select name="mass_change" id="mass_change" class="kase_input_select" style="width:150px">
        	<option value="assign_to_kase" selected="selected">Choose Action</option>
            <option value="assign_to_kase">Assign</option>
            <option value="delete_webmail">Delete</option>
        </select>
    	<div style="float:right">
        	<a id="refresh_webmail" style="cursor:pointer" class="white_text">Refresh</a>
        </div>  
    </div>
    <div style="float:right; width:60%; height:600px; padding:7px">
    	<div id="preview_title"></div>
    	<div class="white_text" id="preview_pane"></div>
    </div>
    <div style="height:600px; overflow-y:scroll; width:40%">
    <table id="webmail_listing" class="tablesorter webmail_listing" border="0" cellpadding="0" cellspacing="0" width="100%">
        <thead>
        <tr>
            <th style="font-size:1.2em;; width:75px">
            <input type="checkbox" id="check_all_assign" class="check_all"  />&nbsp;&nbsp;
            Received
            </th>
            <th style="font-size:1.2em;" width="120">
                From
            </th>
            <th style="font-size:1.2em;">
                Subject
            </th>
            <!--
            <th style="font-size:1.2em;">
            	Kase
            </th>
            -->
            <th>&nbsp;
            
            </th>
        </tr>
        </thead>
        <tbody>
       <% 
       var current_day = "";
       _.each( webmails, function(webmail) {%>
       <% var the_day = moment(webmail.date).format("MMDDYY");
        if (webmail.case_id != "") {
            return;
        }
        var date_string = moment(webmail.date).valueOf();
        if (current_day != the_day) {
            current_day = the_day; %>
       <tr class="date_row row_<%= the_day %>">
            <td colspan="6">
                <div style="width:100%; 
                text-align:left; 
                font-size:1.8em; 
                background:#CFF; 
                color:red;"><%= moment(webmail.date).format("dddd, MMMM Do YYYY") %>
			</div>
            </td>
       </tr>
       <% } %>
       	<tr class="webmail_data_row kase_webmail_row_<%=webmail.id %> webmail_row_<%= webmail.id %>">
            <td style="font-size:1.2em; width:110px; border:0px solid red">
            	<div>
                    <input type="checkbox" id="check_assign_<%=webmail.id %>" class="check_thisone"  />
                    <%= webmail.timeofday %>
                    <input type="hidden" id="webmail_date_<%=webmail.id %>" value="<%= moment(webmail.date).format('MM/DD/YYYY hh:mma') %>" />
                    <input type="hidden" id="webmail_case_id_<%=webmail.id %>" />
                    <input type="hidden" id="webmail_message_id_<%=webmail.id %>" value="<%=webmail.message_id %>" />
                </div>
                <i style="font-size:15px; color:#06f; cursor:pointer" class="glyphicon glyphicon-envelope expand_webmail" id="expand_<%=webmail.id %>" title="Click to Expand Details"></i>
                <i style="font-size:15px; color:#FF0000; cursor:pointer; display:none" class="glyphicon glyphicon-resize-small shrink_webmail" id="shrink_<%= webmail.id %>" title="Click to Shrink Email Details"></i>
                
                <a id="reply_<%= webmail.id %>" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer" class="message_action"><i style="font-size:12px;color:#FCC" class="glyphicon glyphicon-arrow-left" title="Click to Reply"></i></a>
                <a id="forward_<%= webmail.id %>" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer" class="message_action"><i style="font-size:13px;color:#66FF99" class="glyphicon glyphicon-arrow-right" title="Click to Forward"></i></a>
                <a id="assign_<%= webmail.id %>" class="assign_webmail white_text" style="cursor:pointer; font-size:0.7em" title="Click to assign this webmail to kase">Assign</a>
                &nbsp;
            	<%= webmail.attachments %>                
            </td>
            <td style="font-size:1.2em; width:170px">
            	<span title="<%= webmail.from %>" style="width:120px" class="dont-break-out" id="webmail_from_<%=webmail.id %>"><%= webmail.from %></span>
            </td>
            <td style="font-size:1.2em">
            	<span class="dont-break-out" id="webmail_subject_<%=webmail.id %>"><%= webmail.subject %></span>
            </td>	
            <!--
            <td nowrap="nowrap">
            	<div>
                    <div style="display:inline-block">
                        <a name="webmail_save_<%=webmail.id %>" id="webmail_save_<%=webmail.id %>" class="save_icon" style="display:none; cursor:pointer" title="Click to Save"><i class="glyphicon glyphicon-saved" style="color:#00FF00">&nbsp;</i></a>
                        <i class="glyphicon glyphicon-saved" style="color:#CCCCCC" id="disabled_webmail_save_<%=webmail.id %>">&nbsp;</i>
                    </div>
                    <div style="display:inline-block">
                        <input type="text" id="webmail_case_<%=webmail.id %>" class="kase_input" placeholder="Type here to assign email to a kase" style="width:275px" />
                        <div style="display:none" id="notes_webmail_case_id_<%=webmail.id %>">
                            Notes
                            <br />
                            <textarea id="notes_webmail_<%=webmail.id %>" class="notes_webmail" rows="3" style="width:275px"></textarea>
                        </div>
                        <input type="hidden" id="webmail_case_id_<%=webmail.id %>" />
                        
                        <input type="hidden" id="webmail_message_id_<%=webmail.id %>" value="<%=webmail.message_id %>" />
                    </div>
                </div>
            </td>
            -->
            <td nowrap="nowrap">
                
                <a title="Click to delete Email.  Warning: this will permanently delete the email both on iKase and on your mail server" class="list_edit delete_webmail" id="deletewebmail_<%= webmail.id %>" style="cursor:pointer">
                <i style="font-size:15px; color:#FF3737; cursor:pointer" class="glyphicon glyphicon-trash list_edit delete_webmail" title="Click to delete Email.  Warning: this will permanently delete the email both on iKase and on your mail server"></i></a>
            </td>
        </tr>
        <% if (webmail.attachments != "&nbsp;") { %>
        <tr class="webmail_data_row webmail_row_<%= webmail.id %>_details" style="display:">
        	<td colspan="6" id="webmail_attachments_<%= webmail.id %>"><%=webmail.attach_files %></td>
        </tr>
        <% } %>
        <tr class="webmail_data_row webmail_row_<%= webmail.id %> webmail_row_<%= webmail.id %>_details" style="display:none">
        	<td colspan="6" id="webmail_body_<%= webmail.id %>">&nbsp;</td>
        </tr>
        <% }); %>
        </tbody>
    </table>
    </div>
</div>
<div id="webmail_listing_all_done"></div>
<script language="javascript">
$( "#webmail_listing_all_done" ).trigger( "click" );
</script>
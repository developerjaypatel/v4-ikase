<div id="confirm_delete" style="display:none; position:absolute; top:20px; color:white; background:black; border:1px solid white; font-size:1.2em; padding:10px; text-align:center; z-index:1051">
	<div style="width:350px; margin-left:auto; margin-right:auto; ">
    <input type="hidden" name="confirm_delete_id" id="confirm_delete_id" value="" />
    Are you sure you want to delete this message?
    <div style="padding:5px; text-align:center"><a id="delete_partie" class="delete_yes white_icon" style="cursor:pointer">YES</a></div>
    <div style="padding:5px; text-align:center"><a id="cancel_partie" class="delete_no white_icon" style="cursor:pointer">NO</a></div>
    </div>
</div>
<div>
	<div class="glass_header">
    	<div style="float:right;">
        	<div class="btn-group">              
                <input id="message_searchList" type="text" class="search-field" placeholder="" autocomplete="off" style="margin-left:0px; margin-top:0px; height:30px">
                <a id="message_clear_search"
                onclick="(function(){  document.getElementById('message_searchList').value ='';   return false;})();" 

                style="position: absolute;
                right: 2px;
                top: 0;
                bottom: 2px;
                height: 14px;
                margin: auto;
                cursor: pointer;
                "><i class="glyphicon glyphicon-remove-circle" style='font-size:1em; color:#cccccc'></i></a>
            </div>
        </div>
        <span style="font-size:1.2em; color:#FFFFFF" id="message_listing_title"><%=title%></span>&nbsp;&nbsp;<span class="white_text">(<%=messages.length %>)</span>
        	<button title="New message" id="new_message" class="btn btn-transparent" style="color:white; border:0px solid; width:20px" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false">
                <i class="glyphicon glyphicon-plus-sign" style="color:#00FFFF">&nbsp;</i>
            </button>
            &nbsp;
            <button id="return_to_contact" type="button" class="btn btn-primary btn-xs" style="display:none">Return to Contact</button>
            <button id="unread_messages" type="button" class="btn btn-primary btn-xs">Unread Messages</button>
            <button id="all_messages" type="button" class="btn btn-success btn-xs all_messages" style="display:none">All Messages</button>
            <%=webmail_inbox %>
            <div id="pager" class="pager" style="<% if (homepage == true) { %>none<% } %>;">
                <form>
                    <img src="img/first.png" class="first"/>
                    <img src="img/prev.png" class="prev"/>
                    <input type="text" class="pagedisplay"/>
                    <img src="img/next.png" class="next"/>
                    <img src="img/last.png" class="last"/>
                    <select class="pagesize">
                        <option selected="selected" value="10">10</option>
                        <option value="20">20</option>
                        <option value="30">30</option>
                        <option  value="40">40</option>
                    </select>
                </form>
    		</div>
    </div>
    <div id="message_preview_panel" style="position:absolute; width:20px; display:none; z-index:2; background:white" class="attach_preview_panel"></div>
    <?php 
	$table_style = "";
	$blnNewLook = true;
	//if ($_SERVER['REMOTE_ADDR']=='71.119.40.148') {
	if ($blnNewLook) {
		$table_style = ' width="100%"';
		 ?>
    <% if (homepage != true) { %>
    <div style="float:right; width:0%; height:600px; padding:7px; display:none" id="preview_pane_holder">
    	<div id="preview_title"></div>
    	<div class="white_text" id="preview_pane"></div>
    </div>
    <div style="height:600px; overflow-y:scroll; width:100%" id="message_list_outer_div">
    <% } else { %>
    <div id="message_list_outer_div">
    <% } %>
    <?php } else { ?>
    <div id="message_list_outer_div">
    <?php } ?>
    <% 
    var from_label = "From";
    if (title=="Outbox" || title=="Drafts") {
    var from_label = "To";
    }
    %>
    <table id="message_listing" class="tablesorter message_listing" border="1" cellpadding="0" cellspacing="0" <?php echo $table_style; ?>>
        <thead>
        <tr>
            <th style="font-size:1.2em; width:<% if (homepage == true) { %>30%<% } else { %>10%<% } %>; text-align:left; border:#FF9966 0px solid">
                Time
            </th>
            <th style="font-size:1.2em; width:20%; text-align:left; border:#00FF33 0px solid">
                <%=from_label %>
            </th>
            <th style="font-size:1.2em; width:<% if (homepage == true) { %>22%<% } else { %>30%<% } %>; text-align:left; border:#00CCCC 0px solid">
                Subject
            </th>
            <% if (homepage == false) { %>
            <!--<th style="font-size:1.2em; width:<% if (homepage == true) { %>22%<% } else { %>30%<% } %>; text-align:left; border:#00CCCC 0px solid">
                Kase
            </th>
            -->
            <% } %>
            <th style="font-size:1.2em; text-align:left; border:#FF0000 0px solid; width:5%">&nbsp;</th>
        </tr>
        </thead>
        <tbody>
       <% 
       var current_day;
        var print_suffix = "";
        if (title=="Outbox") {
	        print_suffix = "out";
        }
       _.each( messages, function(message) {
       		//we may not have a case
            if (message.case_name == null) {
            	message.case_name = "";
            }
            if (message.case_id == null) {
            	message.case_id = "";
            }
       		var holder_display = "";
            var read_display = "none";
			var message_read = "no";
			var message_confirmation = "";
            read_status = "";
            if (title=="Inbox" || title=="Unread Messages" || title=="Phone Messages") {
                if (message.read_date=="0000-00-00 00:00:00") {
                    holder_display = "none";
                    read_display = "";
                }
            }
           
			if (title=="Outbox") {
                if (message.read_status=="Y") {
                    message_read = "yes";
                    message_confirmation = "Seen";
                }
            }
            if (message.read_date!="0000-00-00 00:00:00") {
	            read_status = "background:#337AB7";
            }
            if (message.read_date=="0000-00-00 00:00:00") {
	            read_status = "background:#4F5669";
            }
            var arrToUserNames = [];
            if (first_column_label=="To") {
            	//lookup all the user_name
                var arrToUsers = message.message_to.split(";");
                arrayLength = arrToUsers.length;
                for (var i = 0; i < arrayLength; i++) {
                	var theworker = worker_searches.findWhere({"nickname": arrToUsers[i]});
                    if (typeof theworker != "undefined") {
                		arrToUserNames[arrToUserNames.length] = theworker.get("user_name");
                    } else {
                    	arrToUserNames[arrToUserNames.length] = arrToUsers[i];
                    }
                }
            }
       %>
       <%
        //we might have a new day
        var the_day = moment(message.dateandtime).format("MMDDYY");
        var date_string = moment(message.dateandtime).valueOf();
        if (current_day != the_day) {
            current_day = the_day;
        %>
        <tr class="date_row row_<%= the_day %>">
            <td colspan="<% if (homepage == false) { %>5<% } else { %>4<% } %>">
                <div style="width:100%; 
text-align:left; 
font-size:1.8em; 
background:#CFF; 
color:red;"><%= moment(message.dateandtime).format("dddd, MMMM Do YYYY") %>&nbsp;&nbsp;<a id="open_<%= message.id %>_<%= the_day %>" class="open_messages"><i style="font-size:13px;color:#3300CC" class="glyphicon glyphicon-envelope" title="Click to Open all Messages for this day"></i></a>&nbsp;<a href="report.php#messages<%=print_suffix %>/day/<%= moment(message.dateandtime).format("YYYY-MM-DD") %>" class="message_print" target="_blank"><i style="font-size:15px;color:#CC99CC; cursor:pointer;" class="glyphicon glyphicon-print" title="Click to Print Messages for this day"></i></a></div>
            </td>
        </tr>
        <% } %>
       	<tr class="message_data_row message_row_<%= message.id %> row_<%= the_day %> open_message" id="messagerow_<%= message.id %>" style="<%=read_status %>">
                <td style="font-size:1.2em; border:#FF9966 0px solid" nowrap="nowrap">
                <div class="white_text">
                	<%= moment(message.dateandtime).format("hh:mm a") %>
                    <input type="hidden" id="message_date_<%=message.id %>" value="<%= moment(message.dateandtime).format('MM/DD/YYYY hh:mma') %>" />
                    <input type="hidden" id="message_id_<%=message.id %>" value="<%=message.message_id %>" />
                </div>
                <% if (title != "Pending Emails") { %>
                <div>
                <span style="float:left; display:<%=read_display %>" id="read_holder_<%= message.id %>"><a id="openmessage_<%= message.id %>" class="read_holders" style="cursor:pointer"></a><a href="report.php#message/<%=message.id %>" target="_blank"><i style="font-size:15px;color:#CC99CC; cursor:pointer;" class="glyphicon glyphicon-print" title="Click to Print Message"></i></a>&nbsp;<a href="report.php#memo/<%=message.id %>" target="_blank"><span style="background:#FFFFFF; color:#0066CC;" title="Click to print Memorandum">M</span></a>
               </span>
                <span style="float:left; display:<%=holder_display %>; border:#FF0066 0px solid" id="action_holder_<%= message.id %>">
                    <a id="open_<%= message.id %>" class="open_message"><i style="font-size:13px;color:#06f" class="glyphicon glyphicon-envelope" title="Click to Read Message"></i></a>
                    <a id="reply_<%= message.id %>" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer" class="message_action"><i style="font-size:12px;color:#FCC" class="glyphicon glyphicon-arrow-left" title="Click to Reply"></i></a>
                    <a id="replyall_<%= message.id %>" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer" class="message_action"><i style="font-size:13px;color:#FCC" class="glyphicon glyphicon-circle-arrow-left" title="Click to Reply All"></i></a>
                    <a id="forward_<%= message.id %>" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer" class="message_action"><i style="font-size:13px;color:#66FF99" class="glyphicon glyphicon-arrow-right" title="Click to Forward"></i></a>&nbsp;<a class="print_message" id="print_message_<%= message.id %>"><i style="font-size:13px;color:#CC99CC; cursor:pointer;" class="glyphicon glyphicon-print" title="Click to Print Message"></i></a>&nbsp;<a class="print_memo" id="print_memo_<%= message.id %>"><span style="background:#FFFFFF; cursor:pointer; color:#0066CC;" title="Click to print Memorandum">M</span></a><%=message.reaction_indicator %>
                    
                    <span onmouseover="hideMessagePreview();showAttachmentPreview('message', event, '<%=message.attachments%>', '<%=message.case_id%>', '<%=message.customer_id%>', '<%=message.message_type %>')">
                    <i style="font-size:15px;color:#FFFFFF; display:<%=message.attach_indicator%>" class="glyphicon glyphicon-paperclip"></i>
                    </span>
                    <% if (message.message_type=="email" && message.case_id < 0) { %>
                    <a id="assign_<%= message.id %>" class="assign_webmail white_text" style="cursor:pointer; font-size:0.7em" title="Click to assign this webmail to kase">Assign</a>
                    <% } %>
                </span>
                </div>
                <% } %>
                </td>
                <td style="font-size:1.2em; width:170px; border:#00FF33 0px solid" nowrap="nowrap">
                	<input type="hidden" id="message_destination_<%=message.id %>" value="<%=message.message_to %>" />
                    <% if (first_column_label=="To") { %>
                    	<span id="message_to_<%=message.id %>">
                        <% if (message.to_user_names=="") { %>
                        	<%= arrToUserNames.join("; ") %>
                            <% if (message.read_date != "" && message.read_date != "0000-00-00 00:00:00") { %> 
                                <i class="glyphicon glyphicon-registration-mark" style="color:#00FFFF" title="Message was read <%= moment(message.read_date).format("l hh:mm a") %>">&nbsp;</i>
                            <% } %>
                        <% } else { %>
                        	<div style="float:right">
                            <a id='open_date_<%= message.id %>' class='read_date white_text' style='cursor:pointer; font-size:0.7em'><i style="font-size:13px;color:white; cursor:pointer;" class="glyphicon glyphicon-time" title="Click to Show Read Dates"></i></a>
                            </div>
	                        <%= message.to_user_names %>
                        <% } %>
                        </span>
                        <span style="width:120px; display:none" class="dont-break-out" id="message_from_<%=message.id %>">
                        <%= message.sender %>
                        </span>
                    <% } else { %>
                        <span style="width:120px" class="dont-break-out" id="message_from_<%=message.id %>">
                        <%= message.sender %>
						</span>
                        <span id="message_to_<%=message.id %>" style='display:; font-size:0.7em'>
                        <% if (message.to_user_names=="") { %>
                        	<%= arrToUserNames.join("; ") %>
                            <% if (message.read_date != "0000-00-00 00:00:00") { %> 
                                <i class="glyphicon glyphicon-registration-mark" style="color:#00FFFF" title="Message was read <%= moment(message.read_date).format("l hh:mm a") %>">&nbsp;</i>
                            <% } %>
                        <% } else { %>
	                        <br /><%= message.to_user_names %>
                        <% } %>
                        </span>
                    <% } %>
    			</td>
                <td style="font-size:1.2em; border:#00CCCC 0px solid">
                	<div style="font-weight:bold" id="message_case_<%=message.id %>" class="<%=message.message_type %>">
                    	<% if (message.case_name!="") { %>
                    	<a href="?n=#kase/<%=message.case_id %>" title="Click to review kase" class="list-item_kase" style="color:white" target="_blank"><%=message.case_name %></a>
                        <% } %>
                        <input type="hidden" value="<%=message.case_id %>" id="message_case_id_<%=message.id %>" />
                    </div>
                	<span class="dont-break-out" id="message_subject_<%=message.id %>">
                    <%= message.subject %>
                    </span>
                </td>
                <% if (homepage == false) { %>
                <!--<td style="font-size:1.2em; border:#00CCCC 0px solid"><a href="#kases/<%=message.case_id %>" title="Click to review kase" class="list-item_kase" style="color:white"><%=message.case_name %></a></td>-->
                <% } %>
                <td nowrap="nowrap" align="left">
                	<% if (title == "Pending Emails") { %>
                    	<div style="float:right; margin-right:20px" id="pending_buttons_holder_<%= message.id %>">
                        	<button class="btn btn-xs btn-primary btn_review_pending" id="review_<%= message.id %>">Review</button>&nbsp;&nbsp;&nbsp;<button class="btn btn-xs btn-success btn_accept_pending" id="accept_<%= message.id %>">Accept as is</button>&nbsp;&nbsp;&nbsp;<button class="btn btn-xs btn-warning btn_dismiss_pending" id="dismiss_<%= message.id %>">Dismiss</button>&nbsp;&nbsp;&nbsp;<button class="btn btn-xs btn-danger btn_block_pending" id="block_<%= message.id %>">Block</button>
                        </div>
                    <% } %>
                	<i style="font-size:15px; color:#FF3737; cursor:pointer" class="glyphicon glyphicon-trash delete_message" id="delete_<%= message.id %>" title="Click to delete"></i>
                </td>
        </tr>
        <tr class="message_row_<%= the_day %>" id="message_row_<%= message.id %>" style="display:none">
        	<td style="font-size:1.5em" colspan="<% if (homepage == false) { %>4<% } else { %>3<% } %>">
            <div style='width:100%; background:white; color:black'>
            	<% if (homepage == true) { %>
                <span style="font-weight:bold"><a href="#kases/<%=message.case_id %>" title="Click to review kase" class="list-item_kase"><%=message.case_name %></a></span><br />
                <% } %>
                <%= message.message %>
                <%= message.attach_link %>
            </div>
            </td>
        </tr>
        <% }); %>
        </tbody>
    </table>
    </div>
    

</div>

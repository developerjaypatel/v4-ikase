<div id="confirm_delete" style="display:none; position:absolute; top:20px; color:white; background:black; border:1px solid white; font-size:1.2em; padding:10px; text-align:center; z-index:1051">
	<div style="width:350px; margin-left:auto; margin-right:auto; ">
    <input type="hidden" name="confirm_delete_id" id="confirm_delete_id" value="" />
    Are you sure you want to delete this thread?
    <div style="padding:5px; text-align:center"><a id="delete_partie" class="delete_yes white_icon" style="cursor:pointer">YES</a></div>
    <div style="padding:5px; text-align:center"><a id="cancel_partie" class="delete_no white_icon" style="cursor:pointer">NO</a></div>
    </div>
</div>
<div>
	<div class="glass_header">
    	<div style="float:right;">
        	<% if (title == "Pending Emails") { %>
                <div style="float:left; margin-right:20px; display:none" id="pending_buttons_holder_bulk">
                    <button class="btn btn-sm btn-primary" id="assign_bulk">Assign to Kase</button>
                    &nbsp;&nbsp;&nbsp;
                    <button class="btn btn-sm btn-success" id="accept_bulk">Accept as is</button>
                    &nbsp;&nbsp;&nbsp;
                    <button class="btn btn-sm btn-warning" id="dismiss_bulk">Dismiss</button>
                    &nbsp;&nbsp;&nbsp;
                    <button class="btn btn-sm btn-danger" id="block_bulk">Block</button>
                </div>
            <% } %>
        	<div class="btn-group">              
                <input id="thread_searchList" type="text" class="search-field" placeholder="Search" autocomplete="off" style="margin-left:0px; margin-top:0px; height:30px">
                <a id="thread_clear_search" 
                onclick="(function(){  document.getElementById('thread_searchList').value ='';   return false;})();" 
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
        <span style="font-size:1.2em; color:#FFFFFF" id="thread_title_holder"><%=title%></span>&nbsp;<span style="color:white">(<%=threads.length %>)</span>
        	<button title="new thread" id="new_thread" class="btn btn-sm" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false">New Message</button>
            <div id="refresh_thread_holder" style="display:inline-block; margin-left:10px; cursor:pointer; text-align:center">
            	<button title="Sync Email" id="refresh_thread" class="btn btn-success btn-sm" style="display:none">Sync Email</button>
                <!--
                    By mukesh on 28-4-2023
                <button title="Activate Email" id="activate_email" class="btn btn-success btn-sm" style="display:none">Activate Email</button>
                -->
            </div>
            
            <div id="show_threads" style="display:inline-block; margin-left:10px; cursor:pointer; text-align:center">
            	<button id="unread_threads" type="button" class="btn btn-primary btn-sm">Unread Messages</button>
                <button id="all_threads" type="button" class="btn btn-success btn-sm all_messages" style="display:none">All Messages</button>
            </div>
            <div id="show_drafts" style="display:inline-block; margin-left:10px; cursor:pointer; text-align:center">
            	<button id="unread_drafts" type="button" class="btn btn-default btn-sm" style="display:none">Drafts</button>
                <button id="all_drafts" type="button" class="btn btn-success btn-sm all_messages" style="display:none">All Messages</button>
            </div>
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
            <script>
                var table_id = $('#table_id').val();
                global_login_email = new Email({user_id: login_user_id, email_id: table_id});
                global_login_email.fetch({
                    success: function (email) {
                        var email_json_all = email.toJSON();
                        // console.log(email_json_all);
                        var email_json_all_len = email_json_all.insert.length;
                        for (let i = 0; i < email_json_all_len; i++) {
                            var email_json = email_json_all[i];
                            login_email_name = email_json.email_name;
                            $("#email_list").html($("#email_list").html()+'<option value="'+login_email_name+'">'+login_email_name+'</option>');
                        }
                    }
                });
            </script>
            <select id="email_list">
                <option value="">All Emails</option>
            </select>
            <script>
            $(window).ready(function(){
                let to_email_filter_value= "";
                to_email_filter_value = localStorage.getItem("to_email_filter");
                console.log(to_email_filter_value);
                $('#email_list').val(to_email_filter_value);
                $("#email_list").on("change", function() {
                    var value = $(this).val().toLowerCase();
                    localStorage.setItem("to_email_filter", value);
                    $("#thread_listing tr.thread_data_row").filter(function() {
                        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                    });
                });
                // $('#email_list').trigger("change");
            });
            </script>
    </div>
    <div id="thread_preview_panel" style="position:absolute; width:20px; display:none; z-index:2; background:white" class="attach_preview_panel"></div>
    <div id="threadimage_preview_panel" style="position:absolute; width:20px; display:none; z-index:2; background:white;" class="attach_preview_panel"></div>
    <?php 
	$table_style = "";
	$blnNewLook = true;
	//if ($_SERVER['REMOTE_ADDR']=='71.119.40.148') {
	if ($blnNewLook) {
		$table_style = ' width="100%"';
		 ?>
    <% if (homepage != true) { %>
    <div style="float:right; width:40%; height:600px; padding:1px; display:none" id="preview_pane_holder" class="thread_listing">
    	<div>
        	<div id="slide_holder" style="height:600px; width:12px; padding:2px; display:inline-block; vertical-align:top">
            	<a id="slide_left" style="cursor:pointer" class="white_text" title="Click to slide pane to the left">&#10092;</a>&nbsp;<a id="slide_right" style="cursor:pointer" class="white_text" title="Click to slide pane to the right">&#10093;</a>
            </div>
            <div style="display:inline-block; width:97%" id="preview_block_holder">
                <div id="preview_title"></div>
                <div class="white_text" id="preview_pane"></div>
                <div style="margin-left:5px">
                    <div class="approve_dialog_holder" id="approve_dialog_holder" style="display:none; position:absolute; top:350px;">
                        <div id="check_number_holder">
                            <input type="text" id="check_number" style="width:200px" placeholder="Check # (Optional)" />
                            &nbsp;
                            <span id="approve_complete_holder">
                                <button class="btn btn-xs btn-primary approve_complete" id="approve_complete">Complete</button>
                            </span>
                        </div>
                    </div>
                    <div class="reject_dialog_holder" id="reject_dialog_holder" style="display:none; position:absolute; top:350px;">
                        <div id="reject_reason_holder">
                            <input type="text" id="reject_reason" style="width:400px" placeholder="Reject Reason" />
                            &nbsp;
                            <span id="reject_complete_holder">
                                <button class="btn btn-xs btn-primary reject_complete" id="reject_complete">Complete</button>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div style="height:600px; overflow-y:scroll; width:100%" id="thread_list_outer_div">
    <% } else { %>
    <div id="thread_list_outer_div">
    <% } %>
    <?php } else { ?>
    <div id="thread_list_outer_div">
    <?php } ?>
    <table id="thread_listing" class="tablesorter thread_listing" border="1" cellpadding="0" cellspacing="0" <?php echo $table_style; ?>>
        <tbody>
       <% 
       var current_day = '';
       var read_display = '';
       var holder_display = '';
       _.each( threads, function(thread) {
           //we might have a new day
            var the_day = moment(thread.dateandtime).format("MMDDYY");
            var date_string = moment(thread.dateandtime).valueOf();
            if (current_day != the_day) {
                current_day = the_day;
            
        %>
        <tr class="date_row row_<%= the_day %>">
            <td colspan="3" style="padding:0px">
                <div style="width:100%; 
                    text-align:left; 
                    font-size:1.8em; 
                    background:#CFF; 
                    color:red;"><%= moment(thread.dateandtime).format("dddd, MMMM Do YYYY") %>
                 </div>
            </td>
        </tr>
        <% } %>
		<tr class="thread_data_row threads_row_<%= thread.id %> row_<%= the_day %> <%=thread.read_indicator %>" id="threadrow_<%= thread.id %>" style="cursor:pointer;<%=thread.read_status %>">
			<td style="font-size:1.2em; border-bottom:1px solid #EDEDED" width="180px">
                <div class="white_text">
                	<input type="checkbox" id="check_thread_<%=thread.id %>" class="check_thread" />
                	<%= thread.display_time %>
                    <% if (title != "Pending Emails") { %>
                    &nbsp;<i style="font-size:12px; color:#FF3737; cursor:pointer" class="glyphicon glyphicon-trash delete_thread" id="delete_<%= thread.id %>" title="Click to delete"></i>
                    <%= thread.unread_link %>
                    <input type="hidden" id="thread_date_<%=thread.id %>" value="<%= moment(thread.dateandtime).format('MM/DD/YYYY hh:mma') %>" />
                    <input type="hidden" id="thread_id_<%=thread.id %>" value="<%=thread.id %>" />
                </div>
                <div style="border:0px solid white;width:175px">
                <span style="float:left; display:<%=holder_display %>; border:#FF0066 0px solid" id="action_holder_<%= thread.id %>">
                	<%=thread.read_image %>
                    <a id="open_<%= thread.id %>" class="open_thread"><i style="font-size:13px;color:white" class="glyphicon glyphicon-envelope"  title="Click to open thread"></i></a>
                    <a id="reply_<%= thread.max_message_id %>" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer" class="thread_action"><i style="font-size:12px;color:#FCC" class="glyphicon glyphicon-arrow-left" title="Click to Reply"></i></a>
                    <a id="replyall_<%= thread.max_message_id %>" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer" class="thread_action"><i style="font-size:13px;color:#FCC" class="glyphicon glyphicon-circle-arrow-left" title="Click to Reply All"></i></a>
                    
                    <a id="forward_<%= thread.max_message_id %>" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer" class="thread_action"><i style="font-size:13px;color:#66FF99" class="glyphicon glyphicon-arrow-right" title="Click to Forward"></i></a>
                    &nbsp;<a href="report.php#thread/<%=thread.id %>" target="_blank"><i style="font-size:13px;color:#CC99CC; cursor:pointer;" class="glyphicon glyphicon-print" title="Click to Print Thread"></i></a>
                    <% if (thread.case_id < 0) { %>
                    &nbsp;<a id="assignthread_<%= thread.id %>" class="assign_threadmail" style="cursor:pointer"><i style="font-size:13px;color:#66FFFF; cursor:pointer;" class="glyphicon glyphicon-link" title="Click to Assign Thread"></i></a>
                    <% } %>                    
	                    <!--
                        onmouseout="hideMessagePreview()"
                        <i style="font-size:15px;color:#FFFFFF; display:<%=thread.attach_indicator%>" class="glyphicon glyphicon-paperclip"></i>
                        -->
                        <span style="display:<%=thread.attach_indicator%>"><br />
                        <button type="button" class="btn btn-primary btn-xs" style="display:<%=thread.word_indicator%>">Word<%=thread.pdf_count %></button>
                        <button type="button" class="btn btn-info btn-xs" style="display:<%=thread.excel_indicator%>">XL<%=thread.excel_count %></button>
                        <button type="button" class="btn btn-danger btn-xs" style="display:<%=thread.pdf_indicator%>">PDF<%=thread.pdf_count %></button>
                        </span>
                    </span>
                    <% } %>
                    <input id="thread_message_ids_<%= thread.id %>" value="<%=thread.thread_message_ids %>" type="hidden" />
                    <span onmouseover="showAttachmentPreview('thread', event, '<%=thread.attachments%>', '<%=thread.case_id%>', '<%=thread.customer_id%>', '<%=thread.thread_type %>')">
                </span>
                </div>
                </td>
                <td style="font-size:1.2em; border-bottom:1px solid #EDEDED">
                	<email_to><%=thread.receiver %></email_to> <% if (title == "Pending Emails" || thread.message_status == 'created') { %>
                    	<div style="float:right; margin-right:20px; padding-left:5px; padding-right:5px;" id="pending_buttons_holder_<%= thread.id %>">
                        	<button class="btn btn-xs btn btn_review_pending btn_pending_action" id="review_<%= thread.id %>">Review</button>
                            &nbsp;&nbsp;&nbsp;
                            <button class="btn btn-xs btn-primary btn_assign btn_pending_action" id="assign_<%= thread.id %>">Assign to Kase</button>
                            &nbsp;&nbsp;&nbsp;
                            <button class="btn btn-xs btn-success btn_accept_pending btn_pending_action" id="accept_<%= thread.id %>">Accept as is</button>
                            &nbsp;&nbsp;&nbsp;
                            <button class="btn btn-xs btn-warning btn_dismiss_pending btn_pending_action" id="dismiss_<%= thread.id %>">Dismiss</button>
                            &nbsp;&nbsp;&nbsp;
                            <button class="btn btn-xs btn-danger btn_block_pending btn_pending_action" id="block_<%= thread.id %>">Block</button>
                        </div>
                    <% } %>
                    <div style="text-align:left">
                        <% if (title == "Outbox") { %>
                        <div style="display:inline-block; width:240px; vertical-align:top; word-wrap: break-word;"><%=thread.message_to.replaceAll(';', '; ') %>
                            <% if (thread.message_cc!='') { %>
                            <br>
                            <%=thread.message_cc.replaceAll(';', '<br>') %>
                            <% } %><%=thread.message_count %>
                        </div>
                        <% } else { %>
                        <div style="display:inline-block; width:240px; vertical-align:top"><%=thread.sender %> <%=thread.message_count %></div>
                        <% } %>
                        <div style="display:inline-block; font-weight:bold" class="<%=thread.thread_type.replace("2016", "") %>">
                            <span id="thread_case_<%=thread.id %>"><%=thread.case_name %></span>
                            <%= thread.attach_link %>
                        </div>
                    </div>
                    <div>
                        <div style="display:inline-block; width:240px">
                            <span class="dont-break-out" id="thread_subject_<%=thread.id %>" style="font-weight:bold">
                                <%= thread.subject %>
                            </span>
                        </div>
                        <div style="display:inline-block;"><%=thread.snippet %></div>
                    </div>
                </td>
        </tr>
        <% }); %>
        </tbody>
    </table>
    </div>
    

</div>

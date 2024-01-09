<div id="confirm_delete_task" style="display:none; position:absolute; top:20px; color:white; background:black; border:1px solid white; font-size:1.2em; padding:10px; text-align:center; z-index:1051" class="<%=listing_type %> <%=pagetype %>">
	<div style="width:350px; margin-left:auto; margin-right:auto; ">
    <input type="hidden" name="confirm_delete_id" id="confirm_delete_id" value="" />
    Are you sure you want to delete this task?
    <div style="padding:5px; text-align:center"><a id="delete_partie" class="delete_yes white_icon" style="cursor:pointer">YES</a></div>
    <div style="padding:5px; text-align:center"><a id="cancel_partie" class="delete_no white_icon" style="cursor:pointer">NO</a></div>
    </div>
</div>
<div>
<div>
	<div id="glass_header" class="glass_header" style="padding-top:5px">
        <span style="font-size:1.5em; color:#FFFFFF">Tasks</span>&nbsp;(<%=tasks.length %>)
        <% if (case_id!="") { %>
        <a title="Click to compose a new task" href="#taskmobile/<%=case_id %>" class="compose_new_note_mobile" id="compose_note_<%=case_id %>" style="cursor:pointer"><i class="glyphicon glyphicon-plus" style="color:#99FFFF">&nbsp;</i></a>          
        <% } %>
    </div>
    <% if (tasks.length > 0) { %>
    <div id="task_preview_panel" style="position:absolute; width:200px; display:none; z-index:2; background:white" class="attach_preview_panel"></div>
    <a id="tasks_top" name="tasks_top"></a>
    <table id="task_listing" class="tablesorter task_listing" border="1" cellpadding="0" cellspacing="0" style="width:100%">
        <thead>
        <tr>
            <th style="font-size:1.17em; width:<% if (homepage != true) { %>7%<% } else { %>3%<% } %>">
                <%=receive_label%>
            </th>
            <th style="font-size:1.17em; width:<% if (homepage != true) { %>3%<% } else { %>3%<% } %>">
                By
            </th>
            <th style="font-size:1.17em; width:<% if (homepage != true) { %>3%<% } else { %>3%<% } %>">
                To
            </th>
            <th style="font-size:1.17em; width:<% if (homepage != true) { %>25.5%<% } else { %>22%<% } %>">
                Subject
            </th>
			<% if (title.indexOf("Kase") == -1) { %>
            <th style="font-size:1.17em; width:<% if (homepage != true) { %>29.8%<% } else { %>23.5%<% } %>">
                Kase
            </th>
			<% } %>
            <th style="font-size:1.17em; width:<% if (homepage != true) { %>2%<% } else { %>2%<% } %>">
                Status
            </th>
            <th style="font-size:1.17em; width:5%">&nbsp;</th>
        </tr>
        </thead>
        <tbody>
       <% var current_day;
		var day_print_url = "taskdayoutbox";
        if (blnMyTasks) {
            day_print_url = "taskdayinbox";
        }
        var intCounter = 0;
        var backtotop = "";
        var intLength = tasks.length;
       _.each( tasks, function(task) {
       		var holder_display = "";
            var read_display = "none";
            if (title=="Task Inbox") {
                if (task.read_status=="N") {
                    holder_display = "none";
                    read_display = "";
                }
            }
            var attach_indicator = "none";
            if (task.attachments!="" && typeof task.attachments!="undefined") {
            	attach_indicator = "";
            }
            var arrToUserNames = [];
            
            //lookup all the user_name
            var arrToUsers = task.assignee.split(";");
            arrayLength = arrToUsers.length;
            for (var i = 0; i < arrayLength; i++) {
                var theworker = worker_searches.findWhere({"nickname": arrToUsers[i]});
                if (typeof theworker != "undefined") {
                	arrToUserNames.push(theworker.get("nickname").toUpperCase());
                } else {
                    arrToUserNames.push(arrToUsers[i]);
                }
            }
           var the_day = moment(task.task_dateandtime).format("MMDDYY");
            
            if (current_day != the_day) {
                current_day = the_day;
                backtotop = "";
                if (intCounter > 5) {
                	backtotop = "<a class='backtotop' style='color:black; font-size:1em; text-decoration:none; cursor:pointer' title='Back to top'>&#8679;</a>&nbsp;|&nbsp;";
                }
        	%>
        <tr>
            <td colspan="9">
                <div style="float:right; padding-right:5px">
                
                	<%=backtotop %><span style='color:black; font-size:0.8em' title='There are <%=arrDayCount[the_day] %> tasks for this day'>(<%=arrDayCount[the_day] %>)</span>
                </div>
                <div style="width:100%; text-align:left; font-size:1.8em; background:#CFF; color:red;">
                
                <%= task.anchor_link %>
                <%= moment(task.task_dateandtime).format("dddd, MMMM Do YYYY") %>
                </div>
            </td>
        </tr>
        <% } %>
        <% if (intLength) %>
       	<tr class="task_data_row task_row_<%= task.id %>">
        
        <% if (!blnMyTasks) { 
            	var full_td_col = 7;
             } else {
             	var full_td_col = 6;
             }
             var full_td_col = 9; %>
        	<td colspan="<%= full_td_col %>">
            <table style="width:100%" border="0">
            	<% if (!blnMyTasks) { 
                        var cols_num = "7";
                   } else {
                        var cols_num = "6";
                   }
                   var cols_num = "9";
                %>
            	<% if (task.task_priority=="high") { %>
                <tr class="description_row" id="description_<%= task.id %>">
        			<td colspan="<%= cols_num %>" style="font-size:1.2em;border:1px solid white; background:#F60;color:white;font-weight:bold" align="center">
                    HIGH PRIORITY
                    </td>
                </tr>    
                <% } %>
                <tr>
                    <td style="font-size:1.2em; width:<% if (homepage != true) { %>7%<% } else { %>3%<% } %>" nowrap="nowrap">
                	
                	<div style="float:left">
                    <span style="display:<%=read_display %>" id="read_holder_<%= task.id %>">
                    	<a id="open_task_<%= task.id %>" class="read_holders" style="cursor:pointer"><img src="img/oie_10234757zr1fW7ZB_final.gif" width="15px" height="15px" /></a>
               		</span>
                    <% if (task.task_priority=="high") { %>
                    <span style="padding-left:2px; padding-right:2px" title="High Priority">
                    <img src="img/output_IW6x8Z.gif" width="15" height="20" /></span>
                    <% } %>
					<% if (task.task_priority=="normal") { %>
                    	<i class="glyphicon glyphicon-warning-sign" style="color:yellow;" title="Normal Priority"></i>
                    <% } %>
                    <% if (task.task_priority=="low") { %>
                    	<i class="glyphicon glyphicon-warning-sign" style="color:white;" title="Low Priority"></i>
                    <% } %>
                    
                <span style="margin-left:10px"  onmouseover="showAttachmentPreview('task', event, '<%=task.attachments%>', <%=task.case_id%>, <%=task.customer_id%>)" onmouseout="hideMessagePreview()">
                <i style="font-size:15px;color:#FFFFFF; display:<%=attach_indicator%>" class="glyphicon glyphicon-paperclip"></i>
                </span>
                     
                    </div>
                	<div style="float:<% if (homepage != true) { %>right<% } else { %>left<% } %>; font-size:13px"><%= task.time %></div>
                	
                </td>
                <td style="font-size:1.17em; width:<% if (homepage != true) { %>3%<% } else { %>3%<% } %>">
                <span style="float:right; display:<%=holder_display %>" id="action_holder_<%= task.id %>">
                    <!--
                    <a id="reply_<%= task.id %>" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer" class="task_action"><i style="font-size:15px;color:#FCC" class="glyphicon glyphicon-arrow-left" title="Click to Reply"></i></a>
                    <a id="replyall_<%= task.id %>" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer" class="task_action"><i style="font-size:15px;color:#FCC" class="glyphicon glyphicon-circle-arrow-left" title="Click to Reply All"></i></a>
                    <a id="forward_<%= task.id %>" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer" class="task_action"><i style="font-size:15px;color:#66FF99" class="glyphicon glyphicon-arrow-right" title="Click to Forward"></i></a>
                    -->
               </span>
               <% if (homepage) { %>
               <%= task.originator %>
               <% } else { %>
               <%= task.originator %>
               <% } %>
                </td>
                
                <td style="font-size:1.17em; width:<% if (homepage != true) { %>3%<% } else { %>3%<% } %>">
                	<span class="assignee_values"><%= arrToUserNames.join("; ") %></span>
                </td>
                
                <td style="font-size:1.17em; width:<% if (homepage != true) { %>25.5%<% } else { %>22%<% } %>">
                    <% 
					var maxlength = 255;
                    if (homepage==true) {
						var maxlength = 22;
                    }
                    var task_title = task.task_title;
					if (task.task_title.length > maxlength) {
						task_title = task.task_title.substr(0, maxlength) + "...";
					}
					%>
                    <span title="<%=task.task_title %>"><%= task_title %></span>
                    <div><%=task.subject %></div>
               </td>
			   <% if (title.indexOf("Kase") == -1) { %>
				   <td style="font-size:1.17em; width:<% if (homepage != true) { %>29.8%<% } else { %>23.5%<% } %>">
						<% if (task.case_name!="" && task.case_name!=null) { 
						var maxlength = 255;
                        if (homepage==true) {
                            var maxlength = 32;
                        }
                        var case_name = task.case_name;
						if (task.case_name.length > maxlength) {
							case_name = task.case_name.substr(0, maxlength) + "...";
						} 
						%>
                        <%= task.case_number %><br />
						<a href="?n=#kase/<%=task.case_id %>" title="<%=task.case_number %> - <%=task.case_name %>" class="list-item_kase" style="color:white" target="_blank">
							<%=case_name %>
						</a>
						<% } %>
					</td>
				<% } %>
                <td style="font-size:1.17em; width:<% if (homepage != true) { %>5%<% } else { %>5.5%<% } %>;">
                	<span id="type_holder_<%= task.id %>"><%= task.task_type %></span>
                </td>
				<td style="width:5%" align="right">
                    <div style="float:left">
                    <% if (task.task_type!="closed") { %>
					<i style="font-size:15px; color:#00C000; cursor:pointer; float:right" class="glyphicon glyphicon-remove-circle close_task" id="close_<%= task.id %>" title="Click to mark Task as Completed.  The Task will still be available under Tasks|Completed Tasks"></i>
					<% } %>
                    </div>
					</td>
		        </tr>
        		<tr class="description_row" id="description_<%= task.id %>">
        			<td colspan="<%= cols_num %>" style="font-size:1.17em;">
                        <%= task.task_description %>
                    </td>
                    
                </tr>
            </table>
          </td>
        </tr>
        <% 	intCounter++;
        }); %>
        </tbody>
    </table>
</div>
<% } else { %>
<div>There are no Tasks listed for today.</div>
<% } %>
<div id="task_listing_all_done"></div>
<script language="javascript">
$( "#task_listing_all_done" ).trigger( "click" );
</script>
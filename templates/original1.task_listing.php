<?php
include("../api/manage_session.php");
session_write_close();
$blnDeletePermission = true;
if ($_SESSION["user_customer_id"]==1075) {
	//per steve g 4/3/2017
	$blnDeletePermission = false;
	if (strpos($_SESSION['user_role'], "admin") !== false) {
		$blnDeletePermission = true;
	}
}
?>
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
	<div class="glass_header" style="width:100%; height:45px">
        <div style="float:right">
		<?php
		$day = date('w');
		//echo $day . " - week number; " . $day_next_week . " - next week number";
		$week_start = date('Y-m-d', strtotime('-'.$day.' days'));
		$week_end = date('Y-m-d', strtotime('+'.(6-$day).' days'));
		
		$next_week_start = date('Y-m-d', strtotime('+'.(7-$day).' days'));
		$next_week_end = date('Y-m-d', strtotime('+'.(13-$day).' days'));
		?>
        <%
        var today = "<?php echo date("Y-m-d"); ?>";
        next_week = moment().isoWeek() + 1;
        next_year = moment().year();
		var week_start = "<?php echo $week_start; ?>";
        var week_end = "<?php echo $week_end; ?>";
		var next_week_start = "<?php echo $next_week_start; ?>";
        var next_week_end = "<?php echo $next_week_end; ?>";
        if (next_week > 52) {
        	next_week = 1;
            next_year++;
        }
        %>
		<!--|&nbsp;<a href="report.php#taskweekinbox/<%=moment().isoWeek() %>/<%=moment().year() %>" target="_blank" title='Click to print this week&rsquo;s tasks' style='cursor:pointer' class="white_text">Print This Week</a> -->
        <% if (title=="Task Inbox") { %>
            <% if (blnCc) { %>
            	<span style="background:orange; padding:2px" id="cc_tasks_filter">Cc Tasks</span>&nbsp;|&nbsp;
            <% } %>
        <a href="#taskcompleted" title="Click to list completed tasks" style='cursor:pointer' class="white_text">Completed Tasks</a>&nbsp;|&nbsp;
        <% } %>
        <% if (!homepage) { %>
        <span id="date_range_holder" class="white_text" style="display:none">
            Date Range <input id="start_dateInput" class="range_dates" value="<%=start %>" style="width:80px" /> through <input id="end_dateInput" class="range_dates" value="<%= end %>" style="width:80px" />&nbsp;<span style="cursor:pointer; font-size:0.7em; display:none" id="update_date_range" title="Click to Update the Event Dates on this list">Update List</span>
        </span>
         &nbsp;|&nbsp;
        <% } %>
        <div style="display:none">
            <a id="print_today_link" href="report.php#taskdayinbox/<%=today %>" target="_blank" title='Click to print today&rsquo;s tasks' style='cursor:pointer' class="white_text">Print Today</a><span id="print_task_links">&nbsp;<% if (typeof arrDayCount[<?php echo date("mdy"); ?>] != "undefined") { %>
            <span style='color:black; font-size:0.7em' title='There are <%=arrDayCount[<?php echo date("mdy"); ?>] %> tasks for this day'>(<%=arrDayCount[<?php echo date("mdy"); ?>] %>)</span><% } %><span id="print_weeks">&nbsp;|&nbsp;<a id="print_thisweek_link" href="report.php#taskbydates/<%=week_start %>/<%=week_end %>" target="_blank" title='Click to print this week&rsquo;s tasks' style='cursor:pointer' class="white_text">Print This Week</a>&nbsp;|&nbsp;<a id="print_nextweek_link" href="report.php#taskbydates/<%=next_week_start %>/<%=next_week_end %>" target="_blank" title='Click to print next week&rsquo;s tasks' style='cursor:pointer' class="white_text">Print Next Week</a>
            &nbsp;&nbsp;&nbsp;
                <a id="print_all_link" href="report.php#taskbydates/2000-01-01/2200-01-01" target="_blank" title='Click to print all tasks' style='cursor:pointer' class="white_text">Print All</a>
            </span></span>
        </div>
        <select id="task_print_options">
        	<option value="">Print ...</option>
            <option value="today" id="today_option">Today</option>
             <option value="week" id="week_option">This Week</option>
             <option value="next" id="next_option">Next Week</option>
             <option value="all" id="all_option">All</option>
        </select>
        </div>
		<input type="hidden" name="case_id" id="case_id" value="<%= case_id %>" />
        <input type="hidden" id="user_id" value="<%= user_id %>" />
        <span style="font-size:1.2em; color:#FFFFFF">
        	<%=title%>
            &nbsp;<div style="display:inline-block; padding-left:3px; margin-top:-25px; color:white; font-size:0.8em; width:20px">(<%=tasks.length %>)</div>
        </span>
			<div style="position:relative; left:175px; margin-top:-22px; width:100px" id="task_links">
            	<!--<a title='Click to view assigned tasks' id='show_assigned' style='cursor:pointer' href='#taskoutbox'><i class='glyphicon glyphicon-tasks' style='color:#66FF33'>&nbsp;</i></a>
                
                <a title="Click to create a Task" id="compose_task" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer"><i class="glyphicon glyphicon-inbox" style="color:#66FF33">&nbsp;</i></a>
                -->
                <button title="Click to create a Task" id="compose_task" class="btn btn-sm btn-primary" style="margin-top:-5px">New Task</button>
            </div>
            <select name="mass_change" id="mass_change" style="width:225px; display:none">
              <option value="" selected="selected">Choose Action</option>
              <option value="change_date">Change Date</option>
              <option value="close_task">Close Task</option>
              <option value="delete_task">Delete Task</option>
              <option value="transfer_task">Transfer Tasks</option>
            </select>
    </div>
    <div id="task_preview_panel" style="position:absolute; width:200px; display:none; z-index:2; background:white" class="attach_preview_panel"></div>
    <a id="tasks_top" name="tasks_top"></a>
    <table id="task_listing" class="tablesorter task_listing" border="1" cellpadding="0" cellspacing="0" style="width:100%">
        <thead>
        <tr>
            <th style="font-size:1.17em; width:<% if (homepage != true) { %>7%<% } else { %>3%<% } %>">
            	<input type="checkbox" id="check_assign_0_overall" class="check_all" title="Select All Tasks"  />
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
           /*
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
            
            var arrCcUserNames = [];
            //lookup all the user_name
            var arrCcUsers = task.cc.split(";");
            arrayLength = arrCcUsers.length;
            for (var i = 0; i < arrayLength; i++) {
            	//only my cc are allowed
                if (arrCcUsers[i]==login_nickname) {
                    var theworker = worker_searches.findWhere({"nickname": arrCcUsers[i]});
                    if (typeof theworker != "undefined") {
                        arrCcUserNames.push(theworker.get("nickname").toUpperCase());
                    } else {
                        arrCcUserNames.push(arrCcUsers[i]);
                    }
                }
            }
            */
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
                <div style="float:right; padding-right:5px" class="print_this_day">
                
                	<%=backtotop %><a href="report.php#<%=day_print_url %>/<%=moment(task.task_dateandtime).format("YYYY-MM-DD") %>" target="_blank" title="Click to Print Tasks for <%= moment(task.task_dateandtime).format("dddd, MMMM Do YYYY") %>" style="color:black; font-size:1em; text-decoration:none">Print This Day</a>&nbsp;<span style='color:black; font-size:0.8em' title='There are <%=arrDayCount[the_day] %> tasks for this day'>(<%=arrDayCount[the_day] %>)</span>
                </div>
                <div style="width:100%;padding-left:5px;  text-align:left; font-size:1.8em; background:#CFF; color:red;">
                <span>
                	<input type="checkbox" id="check_assign_<%=task.id %>_<%= moment(task.task_dateandtime).format('MM-DD-YYYY') %>" class="check_all"  />
                </span>
                <%= task.anchor_link %>
                <%= moment(task.task_dateandtime).format("dddd, MMMM Do YYYY") %>
                </div>
            </td>
        </tr>
        <% } %>
       	<tr class="task_data_row task_row_<%= task.id %>">
        
        <% if (!blnMyTasks) { 
            	var full_td_col = 7;
             } else {
             	var full_td_col = 6;
             }
             var full_td_col = 9; %>
        	<td colspan="<%= full_td_col %>">
            
            <table style="width:99.5%" border="0" align="left">
            	<% if (!blnMyTasks) { 
                        var cols_num = "7";
                   } else {
                        var cols_num = "6";
                   }
                   var cols_num = "9";
                %>
            	<% if (task.task_priority=="high" || task.task_priority=="H") { %>
                <tr class="description_row" id="description_<%= task.id %>">
        			<td colspan="<%= cols_num %>" style="font-size:1.2em;border:1px solid white; background:#F60;color:white;font-weight:bold" align="center">
                    HIGH PRIORITY
                    </td>
                </tr>    
                <% } %>
                <% if (task.task_priority=="medium" || task.task_priority=="M") { %>
                <tr class="description_row" id="description_<%= task.id %>">
        			<td colspan="<%= cols_num %>" style="font-size:1.2em;border:1px solid white; background:#FF9;color:black;font-weight:bold" align="center">
                    MEDIUM PRIORITY
                    </td>
                </tr>    
                <% } %>
                <tr>
                    <td style="font-size:1.2em; width:<% if (homepage != true) { %>7%<% } else { %>3%<% } %>" nowrap="nowrap">
                	
                	<div style="float:left">
                    <input type="checkbox" id="check_assign_<%=task.id %>_<%= moment(task.task_dateandtime).format('MM-DD-YYYY') %>" class="check_thisone check_thisone_<%= moment(task.task_dateandtime).format('MM-DD-YYYY') %>"  />
                    <span style="display:<%=read_display %>" id="read_holder_<%= task.id %>">
                    	<a id="open_task_<%= task.id %>" class="read_holders" style="cursor:pointer"><img src="img/oie_10234757zr1fW7ZB_final.gif" width="15px" height="15px" /></a>
               		</span>
                    <% if (task.task_priority=="high") { %>
                    	<span style="padding-left:2px; padding-right:2px" title="High Priority">
	                    <% if (task.task_type!="closed") { %>
                        <img src="img/output_IW6x8Z.gif" width="15" height="20" />
                        <% } else { %>
                        <span style="color:red; font-weight:bold;">!</span>
                        <% } %>
                        </span>
                    <% } %>
					<% if (task.task_priority=="normal") { %>
                    	<i class="glyphicon glyphicon-warning-sign" style="color:yellow;" title="Normal Priority"></i>
                    <% } %>
                    <% if (task.task_priority=="low") { %>
                    	<i class="glyphicon glyphicon-warning-sign" style="color:white;" title="Low Priority"></i>
                    <% } %>
                    	<!--data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false"-->
                    	<a title="Click to edit task" class="list_edit edit_task" id="edit_task_<%= task.id %>_<%= task.case_id %>" style="cursor:pointer"><i style="font-size:15px;color:#06f; cursor:pointer;" class="glyphicon glyphicon-edit" title="Click to Edit Task"></i></a>&nbsp;<a href="report.php#task/<%=task.id %>" target="_blank"><i style="font-size:15px;color:#CC99CC; cursor:pointer;" class="glyphicon glyphicon-print" title="Click to Print Task"></i></a><span style="margin-left:10px"  onmouseover="showAttachmentPreview('task', event, '<%=task.attachments%>', <%=task.case_id%>, <%=task.customer_id%>)" onmouseout="hideMessagePreview()">
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
                	<span class="assignee_values"><%= task.arrToUserNames.join("; ") %></span>
                    <% if (task.arrCcUserNames.length > 0) { %>
                    <div class="cc_values" style="/*background:orange; color:white; padding:2px*/"><%= task.arrCcUserNames.join("; ") %></div>
                    <% } %>
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
                        <% if (task.case_number!="") { %>
                        Case #:<%= task.case_number %><br />
                        <% } %>
                        <% if (task.file_number!="") { %>
                        File #:<%= task.file_number %><br />
                        <% } %>
						<a href="?n=#kase/<%=task.case_id %>" title="<%=task.case_number %> - <%=task.case_name %>" class="list-item_kase" style="color:white; font-weight:bold" target="_blank">
							<%=case_name %>
						</a>
						<% } %>
					</td>
				<% } %>
                <td style="font-size:1.17em; width:<% if (homepage != true) { %>5%<% } else { %>5.5%<% } %>;">
                	<span id="type_holder_<%= task.id %>"><%= task.task_type %></span>
                </td>
				<td style="width:5%" align="right">
                	<?php //per steve at dordulian 3/31/32017
						if ($blnDeletePermission) { ?>
					<a title="Click to delete task" class="list_edit delete_task" id="deletetask_<%= task.id %>" style="cursor:pointer; float:right">
					<i style="font-size:15px; color:#FF3737; cursor:pointer" class="glyphicon glyphicon-trash <%=listing_type %>"></i></a>
                    <?php } ?>
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
<div id="task_listing_all_done"></div>
<script language="javascript">
$( "#task_listing_all_done" ).trigger( "click" );
</script>
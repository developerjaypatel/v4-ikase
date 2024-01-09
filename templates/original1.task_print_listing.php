<table border="0" cellpadding="2" cellspacing="0" style="width:95%" align="center">  		
    <thead>
    <tr>
        <td width="77"><img src="img/ikase_logo_login.png" height="32" width="77"></td>
        <td align="left" colspan="6">
            <div style="float:right">
                <em>Found <%=tasks.length %> Tasks as of <?php echo date("m/d/y g:iA"); ?></em>
            </div>
            <span style="font-family:Arial, Helvetica, sans-serif; font-weight:bold; font-size:1.5em">&nbsp;</span>
        </td>
      </tr>
    <tr>
        <th style="font-size:1.5em" align="center" colspan="6">
            <%=title %>&nbsp;&nbsp;<span id="date_range_area" title="Double-click to activate date range search" style="cursor:pointer"><%=start %> - <%=end %></span><span id="date_range_area_input" style="display:none"><input id="start_dateInput" class="range_dates" value="<%=start %>" style="width:100px" /> - <input id="end_dateInput" class="range_dates" value="<%=end %>" style="width:100px" />&nbsp;<span style="color:green; cursor:pointer; font-size:0.5em" id="update_date_range">Update</span>&nbsp;<span style="color:red; cursor:pointer; font-size:0.5em" id="close_date_range">Cancel</span></span><br /><br />
        </th>
    </tr>
    </thead>
</table>
<table id="task_listing" class="tablesorter task_listing" border="0" cellpadding="2" cellspacing="0" style="width:95%" align="center">
		<tr style="background:#ECECEC">
			<th width="6%" align="left" nowrap="nowrap" style="font-size:1em; border-bottom:1px solid black; background:#ECECEC">
                Assigned
            </th>
			<th width="6%" align="left" style="font-size:1em; border-bottom:1px solid black; background:#ECECEC">
                From 
          </th>
			<th width="6%" align="left" style="font-size:1em; border-bottom:1px solid black; background:#ECECEC">
                To
          </th>
            <th width="7%" align="left" nowrap="nowrap" style="font-size:1em; border-bottom:1px solid black; background:#ECECEC">Finish By</th>
            <th width="6%" align="left" nowrap="nowrap" style="font-size:1em; border-bottom:1px solid black; background:#ECECEC">
        Case
        No</th>
            <th width="6%" align="left" nowrap="nowrap" style="font-size:1em; border-bottom:1px solid black; background:#ECECEC">Status</th>
			<th width="78%" align="left" nowrap="nowrap" style="font-size:1em; border-bottom:1px solid black; background:#ECECEC">Event</th>
        </tr>
        </thead>
        <tbody>
       <% 
       var current_day = "";
        var intCounter = 0;
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
	            arrToUserNames[arrToUserNames.length] = arrToUsers[i];
            }
            if (task.task_description=="" && task.task_name!="") {
                task.task_description = task.task_name;
            }
            var the_day = moment(task.task_dateandtime).format("MMDDYY");
                
            if (current_day != the_day) {
                current_day = the_day;
    
                
        %>
        <tr>
            <td colspan="7">
            	<span style="font-weight:bold">
                <%= moment(task.task_dateandtime).format("dddd, MMMM Do YYYY") %>
                </span>&nbsp;&nbsp;<span style='color:black; font-size:0.8em' title='There are <%=arrDayCount[the_day] %> tasks for this day'>(<%=arrDayCount[the_day] %>)</span>
            </td>
        </tr>
        <% 
	        } 
        %>
       	<tr class="task_data_row task_row_<%= task.id %>">
                <td align="left" valign="top" nowrap="nowrap" style="font-size:1em;;border-top:1px solid black; padding:2px; height:20px">
               	  <div style="float:right">
                    	<a title="Click to edit task" class="list_edit edit_task" id="edit_task_<%= task.id %>_<%= task.case_id %>" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer"><i style="font-size:15px;color:#06f; cursor:pointer;" class="glyphicon glyphicon-edit" title="Click to Edit Task"></i></a>&nbsp;<i style="font-size:15px;color:#FF0; cursor:pointer;" class="glyphicon glyphicon-print" title="Click to Print Task - NOT READY"></i>
                    </div>
                    <%= task.date_assigned %>
           	    <% if (task.task_priority=="high") { %>
                    <span style="color:red; font-weight:bold; background:white; font-size:1em; padding-left:2px; padding-right:2px" title="High Priority">!</span>
                    <% } %>
                  <% if (task.task_priority=="normal") { %>
                    &nbsp;
                    <% } %>
                    <% if (task.task_priority=="low") { %>
                    <i class="glyphicon glyphicon-arrow-down" style="color:#06C; background:white" title="Low Priority"></i>
                    <% } %>
                </td>
                <td align="left" valign="top" nowrap="nowrap" style="font-size:1em;border-top:1px solid black; padding:2px; height:20px">
                    <%= task.task_from %>
               </td>
               <td align="left" valign="top" style="font-size:1em;border-top:1px solid black; padding:2px; height:20px">
                <%= arrToUserNames.join("; ") %>
                <span style="margin-left:10px"  onmouseover="showAttachmentPreview('task', event, '<%=task.attachments%>', <%=task.case_id%>, <%=task.customer_id%>)" onmouseout="hideMessagePreview()">
                <i style="font-size:15px;color:#FFFFFF; display:<%=attach_indicator%>" class="glyphicon glyphicon-paperclip"></i>
                </span>
               </td>
                <!--
                <td style="font-size:1em;border-top:1px solid black">
                    <%= task.task_name %>
                    <span style="float:left; display:<%=read_display %>" id="read_holder_<%= task.id %>"><a id="openmessage_<%= task.id %>" class="read_holders" style="cursor:pointer"><img src="img/oie_10234757zr1fW7ZB_final.gif" width="15px" height="15px" /></a>
               </span>
               </td>
               -->
               <td align="left" valign="top" nowrap="nowrap" style="font-size:1em;border-top:1px solid black; padding:2px; height:20px">
               <%=task.task_dateandtime %>
                </td>
               
                <td align="left" valign="top" style="font-size:1em;border-top:1px solid black; padding:2px; height:20px">
                <% if (user_data_path == 'A1') { %>
                <%= task.cpointer %>
                <% } else { %>
                <%= task.case_number %>
                <% } %>
                </td>
     <td align="left" valign="top" style="font-size:1em;border-top:1px solid black; padding:2px; height:20px"><%= task.task_type.capitalize() %></td>
          <td rowspan="2" valign="top" style="font-size:1em;border-top:1px solid black; padding:2px"><%= task.task_description %></td>
        </tr>
       	<tr class="task_data_row task_row_<%= task.id %>">
       	  <td colspan="6" align="left" valign="top" nowrap="nowrap" style="font-size:1em; padding:4px">
          	<% if (task.case_name != null) { %>
            	<span style="border:1px solid blue; margin:2px; padding:2px;">
    	            <%= task.case_name %>
	            </span>
            <% } %>
          </td>
       	</tr>
        <% 		intCounter++;
        }); %>
        </tbody>
    </table>
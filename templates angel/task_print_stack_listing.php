<table border="0" cellpadding="2" cellspacing="0" style="width:770px" align="center">  		
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
            <%=title %><br /><br />
        </th>
    </tr>
    </thead>
</table>
<table id="task_listing" class="tablesorter task_listing" border="0" cellpadding="2" cellspacing="0" style="width:770px" align="center">
		<tr style="background:#ECECEC">
			<th width="6%" align="left" nowrap="nowrap" style="font-size:1em; border-bottom:1px solid black; background:#ECECEC">
                <%=receive_label%>
            </th>
			<th width="6%" align="left" style="font-size:1em; border-bottom:1px solid black; background:#ECECEC">Case
        No</th>
			<th width="6%" align="left" style="font-size:1em; border-bottom:1px solid black; background:#ECECEC">
                From 
          </th>
			<th width="6%" align="left" style="font-size:1em; border-bottom:1px solid black; background:#ECECEC">
                To
          </th>
            <th width="7%" align="left" nowrap="nowrap" style="font-size:1em; border-bottom:1px solid black; background:#ECECEC">Finish By</th>
            <th width="6%" align="left" nowrap="nowrap" style="font-size:1em; border-bottom:1px solid black; background:#ECECEC">Status</th>
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
                
            if (current_day != the_day && !blnFirmOverdue) {
                current_day = the_day;
    
                
        %>
        <tr>
            <td colspan="6">
            	<span style="font-weight:bold">
                <%= moment(task.task_dateandtime).format("dddd, MMMM Do YYYY") %>
                </span>&nbsp;&nbsp;<span style='color:black; font-size:0.8em' title='There are <%=arrDayCount[the_day] %> tasks for this day'>(<%=arrDayCount[the_day] %>)</span>
            </td>
        </tr>
        <% } %>
        <% if (task.user_name!="") { %>
        <tr>
            <td colspan="6">
            	<span style="font-weight:bold; font-size:1.6em">
                <%= task.user_name.toLowerCase().capitalizeWords() %>
                </span>
            </td>
        </tr>
        <% } %>
       	<tr class="task_data_row task_row_<%= task.id %>">
                <td align="left" valign="top" nowrap="nowrap" style="font-size:1em;;border-top:1px solid black; padding:2px; height:20px">
               	  <div style="float:right">
                    	<a title="Click to edit task" class="list_edit edit_task" id="edit_task_<%= task.id %>_<%= task.case_id %>" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer"><i style="font-size:15px;color:#06f; cursor:pointer;" class="glyphicon glyphicon-edit" title="Click to Edit Task"></i></a>&nbsp;<i style="font-size:15px;color:#FF0; cursor:pointer;" class="glyphicon glyphicon-print" title="Click to Print Task - NOT READY"></i>
                    </div>
                    <%= task.task_dateandtime %>
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
                <% if (user_data_path == 'A1') { %>
                <%= task.cpointer %>
                <% } else { %>
                <%= task.case_number %>
                <% } %>
    	<% if (task.case_name != null) { %>
             - <strong><%= task.case_name %></strong>
        <% } %>
                </td>
                <td align="left" valign="top" nowrap="nowrap" style="font-size:1em;border-top:1px solid black; padding:2px; height:20px">
                    <%= task.originator %>
               </td>
               <td align="left" valign="top" nowrap="nowrap" style="font-size:1em;border-top:1px solid black; padding:2px; height:20px">
                <span class="assignee_values"><%= arrToUserNames.join("; ") %></span>
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
               <%=task.date_assigned %>
                </td>
               
          <td align="left" valign="top" style="font-size:1em;border-top:1px solid black; padding:2px; height:20px"><%= task.task_type.capitalize() %></td>
          </tr>
       	<tr class="task_data_row task_row_<%= task.id %>">
       	  <td colspan="6" align="left" valign="top" style="font-size:1em; padding:4px"><%= task.task_description %></td>
       	  </tr>
        <% 		intCounter++;
        }); %>
        </tbody>
    </table>
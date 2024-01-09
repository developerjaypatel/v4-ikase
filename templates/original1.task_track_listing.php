<div id="task_track">
<table width="100%">
	<thead>
    	<tr>
            <th align="left" valign="top">By</th>
            <th align="left" valign="top">&nbsp;</th>
            <th align="left" valign="top">On</th>
            <th align="left" valign="top">Due</th>
            <th align="left" valign="top">Status</th>
        </tr>
    </thead>
    <tbody>
    	<% _.each( tasks, function(task) { %>
    	<tr>
            <td align="left" valign="top"><%= task.user_logon.toLowerCase().capitalizeWords() %></td>
            <td align="left" valign="top"><%= task.operation.capitalize() %></td>
            <td align="left" valign="top"><%= task.time_stamp %></td>
            <td align="left" valign="top"><%= task.task_dateandtime %></td>
             <td align="left" valign="top"><%= task.task_type.capitalize() %></td>
        </tr>
        <% }); %>
    </tbody>
</table>
</div>
<div id="task_task_listing" style="margin-left:0px">
    <table id="task_list_table" class="list">
    	<tr>
        	<th>
            	<a class="sort list-item" data-sort="task_number">Task</a>
            </th>
            
            <th>
            	<a class="sort list-item" data-sort="task_title">&nbsp;&nbsp; Due Date</a>
            </th>
        </tr>
        <% _.each( recent_tasks, function(task) {%>
        <tr>
        	<td align="left" valign="top">
            	<a id="tasklink_<%= task.id %>" href='#tasks/<%= task.id %>' class="list-item_kase kase_link_left" title="Click to review this task"><%= task.title %></a>
                
                <a id="task_windowlink_left_<%= task.id %>" href='#edit/<%= task.id %>' class="list-item_kase task_windowlink_left" title="Click to open this task" style="display:none">edit</a>
            </td>
            <td align="left" valign="top" style="color:#FFFFFF">
            	<%= task.due_date %>
            </td>
        </tr>
        <tr>
            <td align="left" valign="top" colspan="2" style="color:#FFFFFF; font-size:0.74em;">
            	<% if (task.from != "" || task.assignee != "") { %>
                
            	<a href='#task/<%= task.id %>' class="white_text"><%= task.from %></a><br /><br />

                <a href='#task/<%= task.id %>' class="white_text"><%= task.assignee %></a>
                <% } %>         
                <div style="margin-top:-15px; margin-bottom:-20px"><hr/></div>
            </td>
      </tr>
        <% }); %>
    </table>
</div>
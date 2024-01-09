<div style="float:right; width:40%; height:600px; padding-top:0px; padding-left:10px; display:none; background: url(img/glass_edit_header_task.png;" id="preview_pane_holder">
    <div>
        <div style="display:inline-block; width:97%; height:600px; overflow-y:scroll" id="preview_block_holder">
            <div id="preview_title" style="
                margin-bottom: 30px;
                color: white;
                font-size: 1.60em;
            ">
            </div>
            <div class="white_text" id="preview_pane"></div>
        </div>
    </div>
</div>
<div style="height:600px; overflow-y:scroll; width:100%" id="task_summary_list_outer_div">
	<div class="glass_header" style="width:100%; height:45px">
    	<div style="float:right; padding-right:10px">
        	<a id="print_task_summary" style="cursor:pointer" class="white_text">Print</a>
        </div>
    	<span style="font-size:1.2em; color:#FFFFFF">Employee Open Tasks Summary</span>
    </div>
    <table id="task_summary_listing" class="tablesorter task_summary_listing" border="1" cellpadding="0" cellspacing="0" style="width:100%">
        <thead>
        <tr>
            <th>User</th>
            <th>Tasks</th>
            <th>Overdues</th>
            <th>Earliest</th>
            <th>Latest</th>
        </tr>
        </thead>
        <tbody>
            <%  
            _.each( tasks, function(task) {
                if (task.oldest_task=="0000-00-00 00:00:00") {
                    task.oldest_task = "";
                } else {
                    task.oldest_task = moment(task.oldest_task).format("MM/DD/YYYY");
                }
                if (task.newest_task=="0000-00-00 00:00:00") {
                    task.newest_task = "";
                } else {
                    task.newest_task = moment(task.newest_task).format("MM/DD/YYYY");
                }
            	var overdue_color = "";
                if (task.overdues > 0) {
                	overdue_color = "background:red; color: white; padding: 2px; font-weight:bold";
                }
             %>
                <tr class="user_task_summary_row user_task_row_<%=task.user_id %>">
                    <td width="1%" align="left" nowrap>
                    	<a id="user_<%=task.user_id %>" class="white_text task_user" style="cursor:pointer"><%=task.user_name %></a>
                    </td>
                    <td width="1%" align="right" nowrap><%=task.task_count %></td>
                    <td width="1%" align="right" style="<%=overdue_color %>" nowrap><%=task.overdues %></td>
                    <td width="1%" align="left" nowrap><%=task.oldest_task %></td>
                    <td align="left"><%=task.newest_task %></td>
                </tr>
            <% }); %>
        </tbody>
    </table>
</div>
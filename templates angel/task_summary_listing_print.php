<table border="0" cellpadding="2" cellspacing="0" style="width:95%" align="center">
  <thead>
    <tr>
      <td width="77"><img src="img/ikase_logo_login.png" height="32" width="77" /></td>
      <td align="left" colspan="6"><div style="float:right"> <em>Found <%=tasks.length %>  as of <?php echo date("m/d/y g:iA"); ?></em> </div>
        <span style="font-family:Arial, Helvetica, sans-serif; font-weight:bold; font-size:1.5em">&nbsp;</span></td>
    </tr>
  </thead>
</table>
	<div style="width:100%; margin-left:auto; margin-right:auto; text-align:center">
    	<span style="font-size:1.5em; color:#black">Employee Open Tasks Summary</span>
    </div>
    <table id="task_summary_listing" class="tablesorter task_summary_listing" border="0" cellpadding="2" cellspacing="0" align="center" width="400px" style="margin-top:50px">
     <thead>
    <tr>
        <th align="left" style="font-size:1em; border-bottom:1px solid black; background:#ECECEC">User</th>
        <th align="left" style="font-size:1em; border-bottom:1px solid black; background:#ECECEC">Tasks</th>
        <th align="left" style="font-size:1em; border-bottom:1px solid black; background:#ECECEC">Earliest</th>
        <th align="right" style="font-size:1em; border-bottom:1px solid black; background:#ECECEC">Latest</th>
    </tr>
    </thead>
    <tbody>
        <%  _.each( tasks, function(task) {
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
        	 %>
            <tr class="user_task_summary_row user_task_row_<%=task.user_id %>">
                <td width="1%" nowrap>
                    <a id="user_<%=task.user_id %>" class="task_user" style="cursor:pointer; text-decoration:underline"><%=task.user_name %></a>
                </td>
                <td width="1%" nowrap><%=task.task_count %></td>
                <td width="1%" nowrap><%=task.oldest_task %></td>
                <td align="right"><%=task.newest_task %></td>
            </tr>
        <% }); %>
    </tbody>
</table>
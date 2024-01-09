<div id="blocked_dates">
	<form id="blocked_form">
    	<input type="hidden" id="table_id" name="table_id" />
        <table width="350px" border="0" align="left" cellpadding="3" cellspacing="0" class="event_stuff" style="display:" id="blocked_table_screen">
            <tr>
                <th align="left" valign="top" scope="row">From:</th>
                <td valign="top">
                    <input name="start_dateInput" class="modalInput blocked blocked_dates input_class" id="start_dateInput" style="width:125px;" placeholder="mm/dd/yyyy" value="<%=start_date %>" required />
                 </td>
                <th valign="top">
                    <span class="through_cell">
                        Through:
                    </span>
                </th>
                <th valign="top">
                    <span class="through_cell">
                        <input name="end_dateInput" class="modalInput blocked blocked_dates input_class" id="end_dateInput" style="width:125px;" placeholder="mm/dd/yyyy" value="<%=start_date %>" />
                    </span>
                    <!--<input type="checkbox" id="all_day" checked="checked" />&nbsp;All Day-->
                 </th>
            </tr>
            <tr id="block_recurring_row">
                <th align="left" valign="top" scope="row">Every:</th>
                <td valign="top">
                    <select id="recurring_spanInput" name="recurring_spanInput" style="width:125px;">
                        <option value=""<% if (recurring_span=="") {%>selected<% } %>>Every ...</option>
                        <option value="week"<% if (recurring_span=="week") {%>selected<% } %>>Week</option>
                        <option value="2_weeks"<% if (recurring_span=="2_weeks") {%>selected<% } %>>2 Weeks.</option>
                        <option value="month"<% if (recurring_span=="month") {%>selected<% } %>>Month</option>
                    </select>
                 </td>
                <td valign="top">
                    <span class="recurring_count_cell every_cell" style="visibility:hidden">
                        <input type="number" id="recurring_countInput" name="recurring_countInput" value="<%=recurring_count %>" style="width:40px" />
                    </span>
                </td>
                <th valign="top">
                	<div style="float:right; visibility:hidden" class="recurring_count_cell">
                    	<input type="checkbox" id="block_forever" />&nbsp;Forever
                    </div>
                    <span class="recurring_count_cell every_cell" style="visibility:hidden">
                        Times
                    </span>
                </th>
            </tr>
            <tr>
                <th align="left" valign="top" scope="row">Employees</th>
                <td valign="top"><input type="checkbox" id="all_employees" checked="checked" />&nbsp;All</td>
                <td valign="top" colspan="2">&nbsp;
                	
                </td>
            </tr>
            <tr id="assignee_holder" style="visibility:hidden">
                <td valign="top" colspan="4">
                	<span>
                		<input name="assigneeInput" type="text" id="assigneeInput" style="width:233px" class="modalInput blocked input_class" value="" autocomplete="off" />
                    </span>
                </td>
            </tr>
        </table>
    </form>
</div>
<div id="blocked_all_done"></div>
<script language="javascript">
$( "#blocked_all_done" ).trigger( "click" );
</script>
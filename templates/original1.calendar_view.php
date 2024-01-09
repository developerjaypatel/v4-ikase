<div class="calendar" style="margin-left:10px">
<form id="calendar_form" name="calendar_form" method="post" action="">
<input id="table_name" name="table_name" type="hidden" value="calendar" />
<input id="table_id" name="table_id" type="hidden" value="<%=calendar_id %>" />
<table width="100%" border="0" align="center" cellpadding="3" cellspacing="0">
  <tr>
    <th width="18%" align="left" valign="top" id="calendar_label" scope="row">Calendar:</th>
    <td width="90%"><input name="calendarInput" type="text" id="calendarInput" style="width:433px" class="modalInput calendar input_class" value="<%=calendar%>" placeholder="Enter Calendar Name" required /></td>
  </tr>
  <tr>
    <th align="left" valign="top" scope="row" id="value_label">Active:</th>
    <td>
      <%=active%>
    </td>
  </tr>
  <tr id="sort_order_holder" style="display:none">
    <th align="left" valign="top" scope="row" id="value_label">Sort Order:</th>
    <td>
      <input name="sort_orderInput" type="text" id="sort_orderInput" style="width:433px" class="modalInput calendar input_class" autocomplete"off" value="<%=sort_order%>" />
      </td>
  </tr>
</table>
</form>
</div>
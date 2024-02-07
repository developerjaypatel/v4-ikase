<div class="user_setting" style="margin-left:10px">
<form id="user_setting_form" name="setting_form" method="post" action="">
<input id="table_name" name="table_name" type="hidden" value="user_setting" />
<input id="table_id" name="table_id" type="hidden" value="<%=user_setting_id %>" />
<input id="setting_type" name="setting_type" type="hidden" value="<%=type %>" />
<table width="100%" border="0" align="center" cellpadding="3" cellspacing="0">
  <tr>
    <th width="18%" align="left" valign="top" scope="row">Category:</th>
    <td width="90%" valign="top">
    	
    	<select name="categoryInput" id="categoryInput" class="modalInput setting input_class">
            <option value="" <% if (category=="") {%>selected="selected"<% } %>>Select Category</option>
            <option value="calendar_colors" <% if (category=="calendar_colors") {%>selected="selected"<% } %>>Calendar Color</option>
            <option value="delay" <% if (category=="delay") {%>selected="selected"<% } %>>Delay</option>
            <option value="date" <% if (category=="date") {%>selected="selected"<% } %>>Date</option>
            <option value="date_and_time" <% if (category=="date_and_time") {%>selected="selected"<% } %>>Date and Time</option>
            <option value="time" <% if (category=="time") {%>selected="selected"<% } %>>Time</option>
            <option value="auto_complete" <% if (category=="auto_complete") {%>selected="selected"<% } %>>Autocomplete</option>
            <option value="choice" <% if (category=="choice") {%>selected="selected"<% } %>>Choice</option>
            <option value="case_number" <% if (category=="case_number") {%>selected="selected"<% } %>>Case Number</option>
      	</select>
    	</td>
    </tr>
  <tr>
    <th align="left" valign="top" scope="row">Setting:</th>
    <td><input name="settingInput" type="text" id="settingInput" style="width:433px" class="modalInput setting input_class" value="<%=setting%>" placeholder="Enter Setting" /></td>
    </tr>
  <tr>
    <th align="left" valign="top" scope="row">Value:</th>
    <td><input name="setting_valueInput" type="text" id="setting_valueInput" style="width:433px" class="modalInput setting input_class" placeholder="Enter Value of Setting" autocomplete"off" value="<%=setting_value%>" />
    </td>
    </tr>
  <tr>
    <th align="left" valign="top" scope="row">Default Value:</th>
    <td><input name="default_valueInput" class="modalInput setting input_class" id="default_valueInput" style="width:433px;" placeholder="Enter Default Value" value="<%=default_value%>" />
    </td>
  </tr>
</table>
</form>
</div>
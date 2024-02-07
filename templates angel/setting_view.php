<div class="setting" style="margin-left:10px">
<form id="setting_form" name="setting_form" method="post" action="">
<input id="table_name" name="table_name" type="hidden" value="setting" />
<input id="table_id" name="table_id" type="hidden" value="<%=setting_id %>" />
<input id="setting_type" name="setting_type" type="hidden" value="<%=type %>" />
<table width="100%" border="0" align="center" cellpadding="3" cellspacing="0">
  <tr>
    <th width="18%" align="left" valign="top" scope="row">Category:</th>
    <td width="90%" valign="top">
    	
    	<select name="categoryInput" id="categoryInput" class="modalInput setting input_class">
            <option value="" <% if (category=="") {%>selected="selected"<% } %>>Select Category</option>
            <option value="auto_complete" <% if (category=="auto_complete") {%>selected="selected"<% } %>>Autocomplete</option>
            <option value="calendar_access" <% if (category=="calendar_access") {%>selected="selected"<% } %>>Calendar Access</option>
            <option value="calendar_colors" <% if (category=="calendar_colors") {%>selected="selected"<% } %>>Calendar Color</option>
            <option value="calendar_name" <% if (category=="calendar_name") {%>selected="selected"<% } %>>Calendar Name</option>
            <option value="calendar_type" <% if (category=="calendar_type") {%>selected="selected"<% } %>>Calendar Type</option>
            <option value="case_number" <% if (category=="case_number") {%>selected="selected"<% } %>>Case Number</option>
            <option value="checks" <% if (category=="checks") {%>selected="selected"<% } %>>Checks</option>
            <option value="choice" <% if (category=="choice") {%>selected="selected"<% } %>>Choice</option>
            <option value="dashboard" <% if (category=="dashboard") {%>selected="selected"<% } %>>Dashboard</option>
            <option value="date" <% if (category=="date") {%>selected="selected"<% } %>>Date</option>
            <option value="date_and_time" <% if (category=="date_and_time") {%>selected="selected"<% } %>>Date and Time</option>
            <option value="delay" <% if (category=="delay") {%>selected="selected"<% } %>>Delay</option>
            <option value="document_type" <% if (category=="document_type") {%>selected="selected"<% } %>>Document Type</option>
            <option value="document_category" <% if (category=="document_category") {%>selected="selected"<% } %>>Document Category</option>
            <option value="document_subcategory" <% if (category=="document_subcategory") {%>selected="selected"<% } %>>Document Sub Category</option>
            <option value="email" <% if (category=="email") {%>selected="selected"<% } %>>Email</option>
            <option value="letterhead" <% if (category=="letterhead") {%>selected="selected"<% } %>>Letterhead</option>
            <option value="lettersignature" <% if (category=="lettersignature") {%>selected="selected"<% } %>>Letter Signature</option>
            <option value="note_type" <% if (category=="note_type") {%>selected="selected"<% } %>>Note Type</option>
            <option value="time" <% if (category=="time") {%>selected="selected"<% } %>>Time</option>
            <option value="task" <% if (category=="task") {%>selected="selected"<% } %>>Task</option>
      	</select>
    	</td>
    </tr>
  <tr>
    <th align="left" valign="top" scope="row" id="setting_label">Setting:</th>
    <td><input name="settingInput" type="text" id="settingInput" style="width:433px" class="modalInput setting input_class" value="<%=setting%>" placeholder="Enter Setting" /></td>
    </tr>
  <tr id="setting_value_holder">
    <th align="left" valign="top" scope="row" id="value_label">Value:</th>
    <td>
    	<input name="setting_valueInput" type="text" id="setting_valueInput" style="width:433px" class="modalInput setting input_class" autocomplete"off" value="<%=setting_value%>" />
    </td>
    </tr>
  <tr id="setting_default_value_holder">
    <th align="left" valign="top" scope="row" id="default_label">Default Value:</th>
    <td><input name="default_valueInput" class="modalInput setting input_class" id="default_valueInput" style="width:433px;" value="<%=default_value%>" />
    </td>
  </tr>
  <tr>
    <td colspan="2">
    	<input type='hidden' id='send_document_id' name='send_document_id' value="" />
    	<div id="setting_attachments" style="width:90%; border:#FFFFFF 0px solid; display:none"></div>
    </td>
  </tr>
</table>
</form>
</div>
<div id="setting_all_done"></div>
<script language="javascript">
$( "#setting_all_done" ).trigger( "click" );
</script>
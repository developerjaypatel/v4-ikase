<?php
require_once('../shared/legacy_session.php');
session_write_close();
?>
<div class="eams_form" style="margin-left:10px">
<form id="eams_form_form" name="eams_form_form" method="post" action="">
<input id="table_name" name="table_name" type="hidden" value="eams_form" />
<input id="table_id" name="table_id" type="hidden" value="<%=eams_form_id %>" />
	<div style="width:350px; height:500px; display:none; float:right;" id="eams_parties_list_holder">
    	<div style="float:right">
            <!--<input type="checkbox" class="event_parties" id="event_parties" value="Y" title="Select All" />-->
            <input type="hidden" name="partie_count" value="<% if (typeof parties != "undefined") { %><%=parties.length %><% } %>" />
        </div>
        Parties <span style="font-size:0.8em">(Select from List to set Parties for Form)</span>
        <div style="border:1px solid white; height:450px; overflow-y:scroll" id="letter_parties_list">
        </div>
    </div>
<table width="100%" border="0" align="center" cellpadding="3" cellspacing="0">
  <tr>
    <th width="18%" align="left" valign="top" id="eams_form_label" scope="row">Name:</th>
    <td width="90%"><input name="nameInput" type="text" id="nameInput" style="width:433px" class="modalInput eams_form input_class" value="<%=name%>" placeholder="Enter Form Name (ex: dor)" /></td>
  </tr>
  <tr id="display_name_holder">
    <th align="left" valign="top" scope="row" id="value_label">Display Name:</th>
    <td>
    	<input name="display_nameInput" type="text" id="display_nameInput" style="width:433px" class="modalInput eams_form input_class" autocomplete"off" value="<%=display_name%>" placeholder="Enter Form Display Name (ex: DOR)" />
    </td>
    </tr>
  <tr>
    <th align="left" valign="top" scope="row">Category:</th>
    <td valign="top"><input name="categoryInput" type="text" id="categoryInput" style="width:433px" class="modalInput eams_form input_class" autocomplete="autocomplete""off" value="<%=category%>" placeholder="Enter Form Category (ex: Audit)" /></td>
  </tr>
  <tr>
    <th align="left" valign="top" scope="row">Status:</th>
    <td valign="top"><select name="statusInput" id="statusInput" class="modalInput eams_form input_class">
      <option value="">Select Category</option>
      <option value="ready" <% if (status=="ready") {%>selected="selected"<% } %>>Ready</option>
      <option value="not ready" <% if (status=="not ready" || status=="") {%>selected="selected"<% } %>>Not Ready</option>
      <option value="in progress" <% if (status=="in progress") {%>selected="selected"<% } %>>In Progress</option>
      <option value="working - missing field" <% if (status=="working - missing field") {%>selected="selected"<% } %>>Working / Missing Field</option>
      </select></td>
  </tr>
  <?php if($_SESSION['user_customer_id']=="1033"){ ?>
  <tr>
    <th align="left" valign="top" scope="row">Customer</th>
    <td valign="top">
        <select name="customer_id" id="customer_id">
        	<option value="<?=$_SESSION['user_customer_id']?>" <% if (customer_id==1033) { %> selected="selected" <% } %> ><?=$_SESSION['user_customer_name']?></option>
          <option value="0" <% if (customer_id==0) { %> selected="selected" <% } %> >All Customers</option>
        </select>
    </td>
  </tr>
  <?php } ?>
  <tr>
    <td colspan="2">
      <input type='hidden' id='send_document_id' name='send_document_id' value="" />
      <div id="eams_form_attachments" style="width:90%; border:#FFFFFF 0px solid; display:none"></div>
      </td>
  </tr>
</table>
</form>
</div>
<div id="eams_form_view_done"></div>
<script language="javascript">
$("#eams_form_view_done").trigger("click");
</script>
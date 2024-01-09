<div class="bulk_webmail_assign" style="margin-left:10px">
<form id="bulk_webmail_assign_form" name="bulk_webmail_assign_form" method="post" action="">
<input type="hidden" id="bulk_thread_id" value="<%=thread_id %>" />
<input type="hidden" name="ids" value="<%=ids %>" id="ids" />
<table width="550px" border="0" align="left" cellpadding="3" cellspacing="0" class="event_stuff" style="display:" id="bulk_webmail_assign">
	<tr id="case_id_row">
        <th align="left" valign="top" scope="row">Case:</th>
        <td colspan="2" valign="top" id="case_id_holder">
        	<div class="case_input"><input name="case_idInput" type="text" id="case_idInput" size="20" class="modal_input" value="" /></div>
            <span id="case_idSpan" style=""></span>        
        </td>
  </tr>
  <% if (message_attach!='') { %>
  <tr id="attach_holder">
  	<th align="left" valign="top" scope="row">Attachments</th>
    <td colspan="2" valign="top"><%=message_attach %></td>
  </tr>
  <% } %>
  <tr style="display:none" id="notes_webmail_holder">
  	<td valign="top" align="left">
    	Notes:
	</td>
    <td colspan="2" valign="top" align="left">
        <textarea id="notes_webmail" class="notes_webmail" rows="3" style="width:470px"></textarea>
    </td>
  </tr>
</table>
</form>
</div>
<div id="bulk_webmail_assign_all_done"></div>
<script language="javascript">
$( "#bulk_webmail_assign_all_done" ).trigger( "click" );
</script>
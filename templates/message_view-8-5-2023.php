<?php
require_once('../shared/legacy_session.php');
session_write_close();

if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
	header("location:index.html");
	die();
}
?>
<div class="interoffice">
<form id="interoffice_form" name="interoffice_form" method="post" action="">
<input id="table_name" name="table_name" type="hidden" value="message" />
<input id="table_id" name="table_id" type="hidden" value="" />
<input id="source_message_id" name="source_message_id" type="hidden" value="<%=source_message_id%>" />
<input id="reaction" name="reaction" type="hidden" value="<%=reaction%>" />
<input id="thread_uuid" name="thread_uuid" type="hidden" value="" />
<input id="kinvoice_id" name="kinvoice_id" type="hidden" value="" />
<input id="kinvoice_document_id" name="kinvoice_document_id" type="hidden" value="" />
<input id="kinvoice_path" name="kinvoice_path" type="hidden" value="" />
<input id="kinvoice_invoiced_id" type="hidden" value="" />
<input id="kinvoice_invoiced_type" type="hidden" value="" />
<input name="fromInput" type="hidden" id="fromInput" value="<%=login_username%>" />
<div style="width:350px; display:none; float:right; border:1px solid white; overflow-y:auto;max-height:650px;" id="message_documents_list"></div>
<div style="width:350px; display:none; float:right; border:1px solid white; margin-right:10px" id="message_users_list"></div>
<div>
  <div id="auto_case_override" style="display:none; float:left">
        	<button class="btn btn-primary btn-xs" id="lookup_another">Lookup Another Case</button>
        </div>
        <div style=" position: absolute;
    left: 440px;
    width: 330px;
    top: 0px;">
  <input type='hidden' id='send_document_id' name='send_document_id' value="" />
        <div id="message_attachments" style="width:300px; "></div>
</div>
</div>
<script>
    // added by mukesh for selection of email from multiple emails in drop down
    var table_id = 0;
    global_login_email = new Email({user_id: login_user_id, email_id: table_id});
    global_login_email.fetch({
        success: function (email) {
            var email_json_all = email.toJSON();
            var email_json_all_len = email_json_all.insert.length;
            for (let i = 0; i < email_json_all_len; i++) {
                var email_json = email_json_all[i];
                login_email_name = email_json.email_name;
                
                $("#fromInput1").html($("#fromInput1").html()+'<option value="'+login_email_name+'">'+login_email_name+'</option>');
            }
        }
    });
</script>
<table width="550" border="0" align="left" cellpadding="2" cellspacing="0" id="message_holder_table">
  <tr>
    <th width="10%" align="left" valign="top" scope="row">From:</th>
    <td colspan="2" valign="top">
    
      <div class="case_input" id="case__input_ui">
        <select name="fromInput" id="fromInput1" class="modal_input">          
        </select>
        </div>
        <span id="fromSpan" style="display:"></span>
    </td>
    </tr>
  <tr>
    <th width="10%" align="left" valign="top" scope="row">Case:</th>
    <td colspan="2" valign="top">
    
    	<div class="case_input" id="case__input_ui">
    		<input name="case_fileInput" type="text" id="case_fileInput" size="30" class="modal_input" />
        </div>
      	<span id="case_idSpan" style="display:"></span>
    </td>
    </tr>
  <tr>
    <th align="left" valign="top" scope="row" style="vertical-align:middle">
    	<span class='show_users' title="Click to select from Employees list">To</span>&nbsp;:<br />
        <a id="send_to_all" style="cursor:pointer; font-size:0.7em; display:none" class="white_text">All</a>
    </th>
    <td width="45%" id="message_to_td">
      <div id="select_all_holder" style="display:none; float:right"><input type="checkbox" id="select_all_clients" value="Y">Select All</div>
      <input name="message_toInput" type="text" id="message_toInput" autocomplete="off" class="modalInput interoffice input_class" />
      <input type="hidden" id="emailaddress_toInput" autocomplete="off" class="modalInput interoffice input_class" />
    </td>
    <td width="45%" rowspan="3" valign="top">
    	
    </td>
    </tr>
  <tr class="cc_row">
    <th align="left" valign="top" scope="row"><span class='show_users' title="Click to select from Employees list">Cc</span>&nbsp;:</th>
    <td colspan="2" style="border:#FF0000 0px solid" valign="top" id="message_cc_td">
    <input name="message_ccInput" type="text" id="message_ccInput" class="modalInput interoffice input_class" />
        <input type="hidden" id="emailaddress_ccInput" autocomplete="off" class="modalInput interoffice input_class" />    
  </tr>
  <tr class="cc_row">
    <th align="left" valign="top" scope="row"><span style="font-weight:bold"><span class='show_users' title="Click to select from Employees list">Bcc</span>&nbsp;:</span></th>
    <td colspan="2" style="border:#FF0000 0px solid" valign="top" id="message_cc_td3"><input name="message_bccInput" type="text" id="message_bccInput" class="modalInput interoffice input_class" />
      <input type="hidden" id="emailaddress_bccInput" autocomplete="off" class="modalInput interoffice input_class" />    
  </tr>
  <tr>
    <th align="left" valign="top" scope="row">Subject:</th>
    <td colspan="2"><input name="subjectInput" type="text" id="subjectInput" style="width:100%" class="modalInput" parsley-error-message="" value="<%=subject %>" autocomplete="off" required /></td>
    </tr>
  <tr>
    <th align="left" valign="top" scope="row" id="priority_holder">Priority:</th>
    <td valign="top" id="priority_holder"><select name="priorityInput" id="priorityInput" class="modalInput interoffice input_class">
      <option value="">Select from List</option>
      <option value="high">High</option>
      <option value="normal" selected="selected">Normal</option>
      <option value="low">Low</option>
    </select><div id="medical_specialties_holder"></div>     
    </td>
    <td valign="top">
    	<div id="follow_up_holder">
        	<div>
            	<span id="follow_up_label">Follow Up Date:</span>&nbsp;
        		<input name="callback_dateInput" class="modalInput interoffice input_class" id="callback_dateInput" value="" style="width:135px" placeholder="mm/dd/yyyy" />
            </div>
            <div id="message_task_assignee" class="assignee" style="display:none">
                <span id="follow_up_label">Assignee:</span>&nbsp;
            	<div id="task_assignee_holder" style="margin-left:92px; margin-top:-15px">
                	<input name="task_assigneeInput" type="text" id="task_assigneeInput" autocomplete="off" class="modalInput interoffice input_class" style="width:160px" />
                </div>
            </div>
        </div>
    </td>
  </tr>
  <tr>
    <th align="left" valign="top" scope="row">Message:</th>
    <td colspan="2" align="right">&nbsp;
    	
    </td>
  </tr>
  <tr>
    <th colspan="3" align="left" valign="top" scope="row">
        <div style="float:right;width:0px;height:270px;border:0px solid red"></div>
        <textarea name="messageInput" id="messageInput" class="modalInput interoffice input_class"><%=message %></textarea>
        <div id="email_signature"></div>
        <input type="hidden" id="signatureInput" value="" />
    </th>
    </tr>
    <tr>
  	<td colspan="3">&nbsp;    </td>
  </tr>
  <tr>
  	<td colspan="3">
    </td>
  </tr>
</table>
</form>
</div>
<div id="message_view_all_done"></div>
<script language="javascript">
$( "#message_view_all_done" ).trigger( "click" );
</script>

<div class="bulk_import_assign" style="margin-left:10px">
<form id="bulk_import_assign_form" name="bulk_webmail_assign_form" method="post" action="">
<input type="hidden" name="ids" value="<%=ids %>" id="ids" />
<table width="590px" border="0" align="left" cellpadding="3" cellspacing="0" class="event_stuff" style="display:" id="bulk_import_assign">
	<tr id="case_id_row">
        <th align="left" valign="top" scope="row">Case:</th>
        <td colspan="2" valign="top" id="case_id_holder">
        	<div class="case_input"><input name="case_idInput" type="text" id="case_idInput" size="30" class="modal_input" value="" /></div>
            <span id="case_idSpan" style=""></span>        
        </td>
  </tr>
  <tr id="account_id_row" style="display:none">
        <th align="left" valign="top" scope="row">Trust&nbsp;Account:</th>
        <td colspan="2" valign="top" id="case_id_holder">
        	<div class="account_input">
            	<select name="account_idInput"  id="account_idInput" class="modal_input">
                </select>
                <div id="new_account_holder" style="display:none">
                	<div>
	                    <input value="" id="account_bankInput" class="<?php //echo $form_name; ?> input_class" placeholder="Bank" style="width:274px" />&nbsp;<span style="font-size:0.9em; font-style:italic">optional</span>
                    </div>
                    <div style="margin-top:5px">
	                    <input value="" id="account_numberInput" class="<?php //echo $form_name; ?> input_class" placeholder="Account Number" style="width:274px" />&nbsp;<span style="font-size:0.9em; font-style:italic">optional</span>
                    </div>
                </div>
           </div>
            <span id="account_idSpan" style=""></span>        
        </td>
  </tr>
  <tr>
       <td colspan="3"> 	
       		<div id="template_invoices_holder"></div>
       </td>
  </tr>
</table>
	
</form>
</div>
<div id="bulk_import_assign_all_done"></div>
<script language="javascript">
$( "#bulk_import_assign_all_done" ).trigger( "click" );
</script>
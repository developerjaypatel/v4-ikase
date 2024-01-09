<div class="bulk_task_transfer" style="margin-left:10px">
<form id="bulk_task_transfer_form" name="bulk_task_transfer_form" method="post" action="">
<input type="hidden" name="ids" value="<%=ids %>" id="ids" />
<table width="550px" border="0" align="left" cellpadding="3" cellspacing="0" style="display:" id="bulk_task_transfer">
	<tr>
        <th align="left" valign="top" scope="row" nowrap="nowrap">Transfer From:</th>
        <td colspan="2" valign="top" id="transfer_from">      
        </td>
  </tr>
    <tr>
        <th align="left" valign="top" scope="row" nowrap="nowrap">Assign to:</th>
        <td colspan="2" valign="top" id="date_holder"><div class="task_transfer"><input name="assigneeInput" type="text" id="assigneeInput" style="width:150px" class="modal_input" value="" /></div><span id="transferSpan" style=""></span>        
        </td>
  </tr>
</table>
</form>
</div>
<div id="bulk_task_transfer_all_done"></div>
<script language="javascript">
$( "#bulk_task_transfer_all_done" ).trigger( "click" );
</script>
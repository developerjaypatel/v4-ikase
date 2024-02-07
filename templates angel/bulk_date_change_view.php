<div class="bulk_date_change" style="margin-left:10px">
<form id="bulk_date_change_form" name="bulk_date_change_form" method="post" action="">
<input type="hidden" name="ids" value="<%=ids %>" id="ids" />
<table width="550px" border="0" align="left" cellpadding="3" cellspacing="0" style="display:" id="bulk_date_change">
	<tr>
        <th align="left" valign="top" scope="row">Date:</th>
        <td colspan="2" valign="top" id="date_holder"><div class="date_change"><input name="task_dateInput" type="text" id="task_dateInput" size="30" class="modal_input" value="" /></div><span id="dateSpan" style=""></span>        
        </td>
  </tr>
</table>
</form>
</div>
<div id="bulk_date_change_all_done"></div>
<script language="javascript">
$( "#bulk_date_change_all_done" ).trigger( "click" );
</script>
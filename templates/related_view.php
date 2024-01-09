<div class="related" style="margin-left:10px">
<form id="related_form" name="related_form" method="post" action="">
<input id="table_name" name="table_name" type="hidden" value="related" />
<input id="injury_id" name="injury_id" type="hidden" value="<%=related.injury_id %>" />
<table width="550px" border="0" align="left" cellpadding="3" cellspacing="0" class="related_stuff" style="display:" id="related_table_screen">
	<tr style="display:none" id="uneditable_row">
    	<td colspan="3">
        	<div id="uneditable"></div>
        </td>
    </tr>
	<tr id="case_id_row">
        <th align="left" valign="top" scope="row">Case:</th>
        <td colspan="2" valign="top" id="case_id_holder">
        	<div class="case_input">
            	<input name="case_idInput" type="text" id="case_idInput" size="30" class="modal_input" value="" />
            </div>
        	<span id="case_idSpan" style=""></span>
        </td>
  </tr>
  <tr>
  		<th>&nbsp;</th>
        <td class="white_text" style="font-style:italic">Search for Kases, assign found Kase to Related Cases</td>
  </tr>
</table>
</form>
</div>
<div id="related_view_all_done"></div>
<script language="javascript">
$("#related_view_all_done").trigger( "click" );
</script>
<?php 
require_once('../shared/legacy_session.php');
session_write_close();
?>
<div class="surgery" style="margin-left:10px">
    <form id="surgery_form" method="post" action="">
        <input id="table_name" name="table_name" type="hidden" value="surgery" />
        <input id="table_id" name="table_id" type="hidden" value="<%=id %>" />
        <input id="case_id" name="case_id" type="hidden" value="<%=case_id %>" />
        <div>
	        <div id="surgery_case_label" style="display:none; width:125px; font-weight:bold">Case</div>
            <div id="surgery_case_name" style="display:inline-block"></div>
        </div>
        <table align="left" width="350" border="0" bordercolor="#dddddd" cellspacing="0" cellpadding="3">
          <tbody>
            <tr height="30" valign="middle" align="left">
              <td width="112" align="left" valign="top"><span style="font-weight:bold">Procedure</span></td>
              <td width="420" align="left" valign="top"><input name="procedureInput" type="text" id="procedureInput" style="width:133px" class="modalInput surgery input_class" tabindex="2" value="<%=procedure %>" autocomplete="off" required />
              </td>
            </tr>
            <tr height="30" valign="middle" align="left">
              <td width="112" align="left" valign="top"><span style="font-weight:bold">Date</span></td>
              <td width="420" align="left" valign="top"><input name="surgery_dateInput" type="text" id="surgery_dateInput" style="width:133px" class="modalInput surgery input_class" tabindex="2" value="<%=surgery_date %>" autocomplete="off" required />
              </td>
            </tr>
            <tr height="30" valign="middle" align="left">
              <td align="left" valign="top"><strong>Description</strong></td>
              <td align="left" valign="top"><textarea name="memoInput" id="memoInput" cols="30" rows="3" style="width:433px" class="modalInput surgery input_class" tabindex="5"><%=memo %></textarea></td>
            </tr>
          </tbody>
        </table>
    </form>
</div>
<div id="surgery_all_done"></div>
<script language="javascript">
$( "#surgery_all_done" ).trigger( "click" );
</script>

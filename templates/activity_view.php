<div class="activity" style="margin-left:10px">
    <form id="activity_form" method="post" action="">
    <input id="table_name" name="table_name" type="hidden" value="activity" />
    <input id="table_id" name="table_id" type="hidden" value="<%=activity_uuid %>" />
    <input id="case_id" name="case_id" type="hidden" value="<%=current_case_id %>" />
        <table align="center" width="350" border="0" bordercolor="#dddddd" cellspacing="0" cellpadding="3">
          <tbody>
            <tr height="30" valign="middle">
              <td width="75" align="left" valign="top" nowrap=""><strong>Hours:</strong></td>
              <td width="420" align="left" nowrap="">
              <% if (activity_uuid != "") { %>
              	<input type="number" min="0.00" max="50.00" step="0.50" name="hoursInput" id="hoursInput" style="width:133px" class="modalInput task input_class" value="<%=hours %>" tabindex="1">
              <% } else { %>
              	<input type="number" min="0.00" max="50.00" step="0.50" name="hoursInput" id="hoursInput" style="width:133px" class="modalInput task input_class" value="0.00" tabindex="1">
              <% } %>
              </td>
            </tr>
            <tr height="30" valign="middle">
              <td colspan="2" align="left" valign="top" nowrap=""><strong>Activity :</strong><br /><br /><textarea name="activityInput" type="text" id="activityInput" style="width:133px" class="modalInput task input_class"><%=activity %></textarea></td>
            </tr>
          </tbody>
        </table>
    </form>
</div>
<div id="activity_all_done"></div>
<script language="javascript">
$( "#activity_all_done" ).trigger( "click" );
</script>
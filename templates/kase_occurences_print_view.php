<table width="500" border="0" align="center" cellpadding="3" cellspacing="0">
  <tr>
  	<td><img src="img/ikase_logo_login.png" height="32" width="77"></td>
    <td align="left" style="font-family:Arial, Helvetica, sans-serif; font-weight:bold; font-size:1.5em">EVENT PRINT PAGE</td>
  </tr>
  <tr>
    <td width="20%">&nbsp;</td>
    <td width="35%"><em>as of <?php echo date("m/d/y g:iA"); ?></em></td>
    <td width="40%">&nbsp;</td>
  </tr>
  <tr>
    <td colspan="4"><hr color="#000000" /></td>
  </tr>
  <tr>
    <th width="32%" align="left" valign="top">ID</th>
    <th width="32%" align="left" valign="top">Event</td>
	<th width="32%" align="left" valign="top">When</th>
  </tr>
  <tr>
	<% _.each( occurences, function(occurence) { %>
    <th width="32%" align="left" valign="top"><%=occurence.id %></th>
    <th width="32%" align="left" valign="top"><%=occurence.event_title %></td>
	<th width="32%" align="left" valign="top"><%=moment(occurence.event_dateandtime).format("MM/DD/YYYY h:iA") %></th>
  </tr>
	<% }) %>
</table>

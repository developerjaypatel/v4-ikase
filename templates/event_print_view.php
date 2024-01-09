
<table width="500" border="0" align="center" cellpadding="3" cellspacing="0">
  <tr>
  	<td><img src="img/ikase_logo_login.png" height="32" width="77"></td>
    <td align="left" style="font-family:Arial, Helvetica, sans-serif; font-weight:bold; font-size:1.5em" colspan="3">EVENT PRINT PAGE</td>
  </tr>
  <tr>
    <td width="20%">&nbsp;</td>
    <td width="35%"><em>as of <?php echo date("m/d/y g:iA"); ?></em></td>
    <td width="40%">&nbsp;</td>
    <td width="30%">&nbsp;</td>
  </tr>
  <tr>
    <td colspan="4"><hr color="#000000" /></td>
  </tr>
  <tr>
    <th width="32%" align="left" valign="top" scope="row">Event:</th>
    <td width="53%" valign="top"><%=occurence_id %></td>
  </tr>
  <tr>
    <th width="32%" align="left" valign="top" scope="row">Title:</th>
    <td width="53%" valign="top"><%=event_title %></td>
  </tr>
  <% if (case_name!="") { %>
  <tr>
    <th width="32%" align="left" valign="top" scope="row">Case:</th>
    <td colspan="2" valign="top"><%=case_name %></td>
  </tr>
  <% } %>
  <tr>
    <th align="left" valign="top" scope="row">Assignee:</th>
    <td><%=assignee %></td>
    </tr>
  <tr>
    <th align="left" valign="top" scope="row">Location:</th>
    <td>
    <%=full_address %>
    </td>
    </tr>
  
  <tr>
    <th align="left" valign="top" scope="row">Due:</th>
    <td valign="top">
    	<%=event_dateandtime %>
	</td>
  </tr>
  <tr>
  	<th align="left" valign="top" scope="row">Status:</th>
    <td>
    	<%=event_type %>
    </td>
  </tr>
  <tr>
    <th align="left" valign="top" scope="row">Event Details:</th>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td align="left" valign="top" scope="row">
    <%=event_description %>
    </td>
    </tr>
    <tr>
      <th align="left" valign="top" scope="row">Entered By:</th>
      <td valign="top">
    	<span class="span_class"><%=from %></span></td>
    </tr>
    <tr>
    	<th align="left" valign="top" scope="row">Priority:</th>
        <td><%=event_priority %></td>
      <td width="15%"></td>
  </tr>
    <tr>
      <th align="left" valign="top" scope="row">End Date:</th>
      <td valign="top">
          <%=end_date %>
	</td>
    </tr>
    <tr>
      <th align="left" valign="top" scope="row" nowrap="nowrap">Follow Up:</th>
      <td valign="top"><%=callback_date %>
	</td>
    </tr>
    <tr>
  	<td>&nbsp;</td>
    <td>&nbsp;</td>
    </tr>
  <tr>
  	<td colspan="2">
    	<div id="message_attachments" style="width:90%"></div>    
    </td>
  </tr>
</table>


<table width="1000" border="0" align="center" cellpadding="3" cellspacing="0">
  <tr>
  	<td width="20%"><img src="img/ikase_logo_login.png" height="32" width="77"></td>
    <td colspan="2" align="left" style="font-size:1.6em;font-weight:bold">
    	<%=customer_name %>&nbsp;:&nbsp;Interoffice Message
    </td>
    <td align="left" style="font-size:0.8em" nowrap="nowrap"><em>as of <?php echo date("m/d/y g:iA"); ?></em></td>
  </tr>
  <tr>
    <th colspan="4" align="left" valign="top" scope="row"><hr color="#000000" /></th>
  </tr>
  <tr>
    <th colspan="4" align="left" valign="top" scope="row" style="font-weight:bold"><label style="width:100px; display:inline-block">Case: </label><%=case_name %></th>
  </tr>
  <tr>
    <td colspan="4" align="left" valign="top" scope="row">
    	<label style="width:100px; display:inline-block">Date: </label><%=dateandtime %>
        <br />
        <label style="width:100px; display:inline-block">From: </label><%=from %>
        <br />
        <label style="width:100px; display:inline-block">To: </label><%=message_to %>
        <br />
        <label style="width:100px; display:inline-block">Subject: </label><%=subject %>
    </td>
  </tr>
  <tr>
    <th colspan="4" align="left" valign="top" scope="row"><hr color="#000000" /></th>
  </tr>
  <tr>
    <th align="left" valign="top" scope="row" style="font-weight:normal">Dear <strong><%=message_to.toUpperCase()%></strong>,</th>
    <td valign="top">&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <th colspan="4" align="left" valign="top" scope="row" style="font-weight:normal"><br /><%=message%></th>
    </tr>
  	<td colspan="4">
    	<div id="message_attachments" style="width:90%"></div>    </td>
  </tr>
  <tr>
    <th align="left" valign="top" scope="row" colspan="4" style="font-weight:normal">Sincerely, <br /><br /><strong><%=from%></strong></th>
  </tr>
</table>

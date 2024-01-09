
<table width="1000" border="0" align="center" cellpadding="3" cellspacing="0">
  <tr>
  	<td width="20%"><img src="img/ikase_logo_login.png" height="32" width="77"></td>
    <td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;">
    <span style="font-family:Arial, Helvetica, sans-serif; font-weight:bold; font-size:1.5em">
    	<%=customer_name %>&nbsp;:&nbsp;MEMORANDUM
    </span></td>
    <td align="left" style="font-size:0.8em" nowrap="nowrap"><em>as of <?php echo date("m/d/y g:iA"); ?></em></td>
  </tr>
  <tr style="margin-top:20px">
    <th colspan="4" align="left" valign="top" scope="row">To: <strong><%=message_to.capitalizeWords()%></strong></th>
  </tr>
  <tr>
    <th colspan="4" align="left" valign="top" scope="row">From: <strong><%=from%></strong></th>
  </tr>
    <tr>
    <th colspan="4" align="left" valign="top" scope="row">Re: <%=subject %></th>
  </tr>
  <tr>
    <th colspan="4" align="left" valign="top" scope="row">Date: <%=moment(dateandtime).format("MM-DD-YYYY")%></th>
  </tr>
  <tr>
    <th colspan="4" align="left" valign="top" scope="row"><hr color="#000000" /></th>
  </tr>
  <tr>
    <th colspan="4" align="left" valign="top" scope="row" style="font-weight:normal"><br /><%=message%></th>
    </tr>
  	<td colspan="4">
    	<div id="message_attachments" style="width:90%"></div>    </td>
  </tr>
</table>

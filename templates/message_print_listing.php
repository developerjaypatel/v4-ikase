<table width="1000" border="0" align="center" cellpadding="3" cellspacing="0">
  <tr>
  	<td width="20%"><img src="img/ikase_logo_login.png" height="32" width="77"></td>
    <td align="left" style="font-family:Arial, Helvetica, sans-serif; font-size:1.3em; font-weight:bold" nowrap="nowrap">
    	<% if (title=="") { %>
        <%=customer_name %>&nbsp;:&nbsp;Interoffice Report
        <% } else { %>
        <%=title %>
        <% } %>
    </td>
    <td width="30%" align="right" style="font-family:Arial, Helvetica, sans-serif"><em><%=moment(messages[0].dateandtime).format("MM/DD/YY") %></em></td>
  </tr>
  <% if (case_name!="") { %>
  <tr>
    <td colspan="3" style="font-weight:bold; font-size:1.2em"><%= case_number + " - " + case_name %></td>
  </tr>
  <% } %>
  <tr>
  	<td colspan="3">
    	<hr />
    </td>
  </tr>
  <%  _.each( messages, function(message) { %>
  <tr>
    <th colspan="3" align="left" valign="top" scope="row">
    	<div style="float:right">
        	<%=moment(message.dateandtime).format("h:mma") %>
        </div>
        <div>
	        Subject:<%=message.subject %>
        </div>
        <% if (message.case_name!="" && message.case_name!=null) { %>
        <div>
	        Case:<%=message.case_name %>
        </div>
        <% } %>
        </th>
  </tr>
  <tr>
    <th colspan="3" align="left" valign="top" style="font-weight:normal" scope="row">Dear <strong><%=message.message_to.toUpperCase()%></strong>,</th>
  </tr>
  <tr>
    <th colspan="3" align="left" valign="top" scope="row" style="font-weight:normal"><br /><%=message.message%></th>
    </tr>
  	<td colspan="3">
    	<div id="message_attachments" style="width:90%"></div>    </td>
  </tr>
  <tr>
    <th align="left" valign="top" scope="row" colspan="3" style="font-weight:normal">Sincerely, <br /><br /><strong><%=message.from.replace("|", "<br />") %></strong></th>
  </tr>
  <tr>
    <th colspan="3" align="left" valign="top" scope="row"><hr color="#000000" /></th>
  </tr>
  <% }); %>
</table>
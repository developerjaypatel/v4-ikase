
<table width="700" border="0" align="center" cellpadding="3" cellspacing="0">
  <tr>
  	<td><img src="img/ikase_logo_login.png" height="32" width="77"></td>
    <td align="left">
    	<div style="float:right">
        	ID = <%=id %><br />
        	<em>as of <?php echo date("m/d/y g:iA"); ?></em>
        </div>
        <span style="font-family:Arial, Helvetica, sans-serif; font-weight:bold; font-size:1.5em"><%=report_title %></span>
    </td>
  </tr>
  <tr>
    <td colspan="4"><hr color="#000000" /></td>
  </tr>
  <% if (case_name!="") { %>
  <tr>
    <th width="14%" align="left" valign="top" nowrap="nowrap" scope="row">Case:</th>
    <td valign="top"><%=case_name %></td>
  </tr>
  <% } %>
  <tr>
    <th width="14%" align="left" valign="top" nowrap="nowrap" scope="row">Date:</th>
    <td width="86%" valign="top"><%=dateandtime %></td>
  </tr>
  <tr>
    <th width="14%" align="left" valign="top" nowrap="nowrap" scope="row">Subject:</th>
    <td width="86%" valign="top"><%=subject %></td>
  </tr>
  <!--
  <tr>
    <th width="14%" align="left" valign="top" nowrap="nowrap" scope="row">Type:</th>
    <td valign="top"><%=type %></td>
  </tr>
  -->
  <tr>
    <th align="left" valign="top" nowrap="nowrap" scope="row">By:</th>
    <td><%=entered_by %></td>
  </tr>
  <tr>
    <th align="left" valign="top" nowrap="nowrap" scope="row">Note:</th>
    <td>
    <% if (title!="") { %>
        <%= title %>
        <br />
    <% } %>
    <%= note %>
    </td>
  </tr>
  <tr>
  	<td colspan="2">
    	<div id="message_attachments" style="width:90%"></div>    
    </td>
  </tr>
</table>

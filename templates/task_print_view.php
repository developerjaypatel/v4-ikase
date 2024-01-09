
<table width="700" border="0" align="center" cellpadding="3" cellspacing="0">
  <tr>
  	<td><img src="img/ikase_logo_login.png" height="32" width="77"></td>
    <td align="left">
    	<div style="float:right">
        	ID = <%=id %><br />
        	<em>as of <?php echo date("m/d/y g:iA"); ?></em>
        </div>
        <span style="font-family:Arial, Helvetica, sans-serif; font-weight:bold; font-size:1.5em"><%=title %></span>
    </td>
  </tr>
  <tr>
    <td colspan="4"><hr color="#000000" /></td>
  </tr>
  <tr>
    <th width="14%" align="left" valign="top" nowrap="nowrap" scope="row">Title:</th>
    <td width="86%" valign="top"><%=task_title %></td>
  </tr>
  <% if (case_name!="") { %>
  <tr>
    <th width="14%" align="left" valign="top" nowrap="nowrap" scope="row">Case:</th>
    <td valign="top"><%=case_name %></td>
  </tr>
  <% } %>
  <tr>
    <th align="left" valign="top" nowrap="nowrap" scope="row">Assignee:</th>
    <td><%=assignee.toUpperCase() %></td>
  </tr>
  <% if (full_address!="") { %>
  <tr>
    <th align="left" valign="top" nowrap="nowrap" scope="row">Location:</th>
    <td>
    <%=full_address %>
    </td>
  </tr>
  <% } %>
  <tr>
    <th align="left" valign="top" nowrap="nowrap" scope="row">Due:</th>
    <td valign="top">
    	<%=task_dateandtime %>
	</td>
  </tr>
  <tr>
  	<th align="left" valign="top" nowrap="nowrap" scope="row">Status:</th>
    <td>
    	<%=task_type.capitalizeWords() %>
    </td>
  </tr>
  <tr>
    <th align="left" valign="top" nowrap="nowrap" scope="row">Task Details:</th>
    <td align="left" valign="top"><%=task_description %></td>
  </tr>
    <tr>
      <th align="left" valign="top" nowrap="nowrap" scope="row">Entered By:</th>
      <td valign="top">
   	  <span class="span_class"><%=from %></span></td>
    </tr>
    <tr>
    	<th align="left" valign="top" nowrap="nowrap" scope="row">Priority:</th>
      <td><%=task_priority.capitalizeWords() %></td>
  </tr>
    <tr>
      <th align="left" valign="top" nowrap="nowrap" scope="row">End Date:</th>
      <td valign="top">
          <%=end_date %>
	</td>
    </tr>
    <% if (callback_date!="") { %>
    <tr>
      <th align="left" valign="top" scope="row" nowrap="nowrap">Follow Up:</th>
      <td valign="top"><%=callback_date %>
	</td>
    </tr>
    <% } %>
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

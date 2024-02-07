<table cellpadding="2" cellspacing="0" width="100%">
	<%  
    var iCounter = 1;
    _.each( hearings, function(hearing) { %>
    <tr>
    	<th style="width:150px" align="left" valign="top" nowrap="nowrap">Hearing <%=iCounter %></th>
        <td width="20%" align="left" valign="top" nowrap="nowrap">
        	<%=hearing.date %>
             <input type="hidden" name="hearing_date_<%=iCounter %>"  value="<%=hearing.date %>" />
             <input type="hidden" name="hearing_type_<%=iCounter %>"  value="<%=hearing.type %>" />
             <input type="hidden" name="hearing_location_<%=iCounter %>"  value="<%=hearing.location %>" />
             <input type="hidden" name="hearing_title_<%=iCounter %>"  value="<%=hearing.type %> (Judge: <%=hearing.judge %>)" />
        </td>
        <td width="20%" align="left" valign="top" nowrap="nowrap"><strong>Type</strong>&nbsp;<%=hearing.type %></td>
        <td width="56%" align="left" valign="top"><strong>Venue</strong>&nbsp;<%=hearing.location %></td>
        <td width="5%" align="left" valign="top" nowrap="nowrap">
        	<% if (hearing.driver_case!="") { %>
            <strong>Related</strong>&nbsp;<%=hearing.driver_case %>
            <% } %>
       </td>
        <td width="11%" align="left" valign="top" nowrap="nowrap"><strong>Judge</strong>&nbsp;<%=hearing.judge %></td>
    </tr>
    <%	iCounter++;
    }); %>
</table>
<hr style="margin:5px" />
<table cellpadding="2" cellspacing="0" width="100%">
	<%  
    var iCounter = 1;
    _.each( occurences, function(occurence) { %>
    <tr>
    	<th style="width:150px" align="left" valign="top" nowrap="nowrap">
        	Event <%=iCounter %>
            <input type="hidden" name="event_date_<%=iCounter %>"  value="<%=occurence.date %>" />
            <input type="hidden" name="event_type_<%=iCounter %>"  value="<%=occurence.type %>" />
            <input type="hidden" name="event_description_<%=iCounter %>"  value="<%=occurence.description %>" />
        </th>
        <td width="20%" align="left" valign="top" nowrap="nowrap"><%=occurence.type %></td>
        <td width="61%" align="left" valign="top"><%=occurence.description %></td>
        <td width="11%" align="left" valign="top"><%=occurence.date %></td>
    </tr>
    <%	iCounter++;
    }); %>
</table>
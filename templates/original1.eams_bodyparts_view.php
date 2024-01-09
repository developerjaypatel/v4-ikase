<table cellpadding="2" cellspacing="0" width="100%">
	<%  
    var iCounter = 1;
    _.each( bodyparts, function(bodypart) { %>
    <tr>
    	<th style="width:150px" align="left" nowrap="nowrap">Body Part <%=iCounter %></th>
        <td width="94%" align="left">
        	<%=bodypart.name %>
            <input type="hidden" name="bodypart_<%=iCounter %>"  value="<%=bodypart.name %>" />
        </td>
    </tr>
    <%	iCounter++;
    }); %>
</table>
<hr style="margin:5px" />
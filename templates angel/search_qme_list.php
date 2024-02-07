<hr />
<div class="qme_list glass_header_no_padding" style="padding:15px; font-size:1.5em">
  <table cellpadding="2" class="tablesorter note_listing" border="1" style="color:white; width:850px">
	<th style="font-size:1.5em; width:10%; text-align:left">Name</th>
    <th style="font-size:1.5em; width:20%; text-align:left">Address</th>
    <th style="font-size:1.5em; width:10%; text-align:left">Phone</th>
    <th style="font-size:1.5em; width:10%; text-align:left">Distance</th>
    <%
    var intCounter = 0;
    _.each( qmes, function(qme) {%>
    <tr>
    	<td>
        	<a id="name_<%=intCounter %>" class="qme_name white_text" title="Click to select this Provider" style="cursor:pointer"><%=qme.name %></a>
        </td>
        <td id="address_<%=intCounter %>"><%=qme.address %></td>
        <td id="phone_<%=intCounter %>" class="white_text"><%=qme.phone %></td>
        <td align="left" class="white_text"><%=qme.distance %>&nbsp;miles</td>
    </tr>
    <% intCounter++;
    }); %>
</table>
</div>
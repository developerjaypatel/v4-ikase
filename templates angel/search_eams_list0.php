<hr />
<div class="eams_list glass_header_no_padding" style="padding:15px">
<table cellpadding="2" class="tablesorter note_listing" border="1" style="color:white; width:550px">
	<th style="font-size:1.5em; width:10%; text-align:left">Name</th>
    <th style="font-size:1.5em; width:10%; text-align:left">City</th>
    <th style="font-size:1.5em; width:10%; text-align:left">&nbsp;</th>
    <%
    var intCounter = 0;
    _.each( eamss, function(eams) {%>
    <tr>
    	<td id="name_<%=intCounter %>"><%=eams.name %></td>
        <td id="city_<%=intCounter %>" class="white_text"><%=eams.city %></td>
        <td  class="eams_link white_text"><a id="eams_link_<%=eams.party_id %>" title="Click to import this EAMS Case" style="cursor:pointer" class="white_text import_eams">View Details</a></td>
    </tr>
    <% intCounter++;
    }); %>
</table>
</div>
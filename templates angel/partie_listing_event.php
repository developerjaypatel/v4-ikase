<div>
	Kase Parties <span style="font-size:0.8em"><%=list_title %></span>
    <table id="partie_listing" class="tablesorter partie_listing" border="0" cellpadding="0" cellspacing="1">
        <tbody>
        <% _.each( parties, function(partie) {
        %>
        <tr class="partie_data_row">
            <td style="font-size:0.8em"><input type="radio" class="event_partie" name="event_partie" id="event_partie_<%= partie.partie_id %>" /></td>
            <td style="font-size:0.8em">
            	<span id="event_partie_name_<%= partie.partie_id %>">
                    <% if (partie.company_name!=null && partie.company_name!="") { %><%= partie.company_name %><% } %>
                    <% if (partie.company_name==null || partie.company_name=="") { %><%= partie.full_name %><% } %>
                    <br />
                    <%= partie.type.replace("_", " ").capitalizeWords() %><% if (partie.phone!=null && partie.phone!="") { %> - <span id="event_partie_phone_<%= partie.partie_id %>"><%= partie.phone %></span><% } %>
                </span>
            </td>
            <td style="font-size:0.8em">
            	<span id="event_partie_address_<%= partie.partie_id %>"><%= partie.address %></span>
            </td>
        </tr>
        <% }); %>
        </tbody>
    </table>
</div>

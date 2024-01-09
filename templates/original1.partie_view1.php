<div><%=party.partie %></div>
<div><%=party.adhoc_fields %></div>
<% _.each( adhocs, function(adhoc) { %>
<div><%=adhoc.adhoc %>&nbsp;:&nbsp;<%=adhoc.adhoc_value %></div>
<% }); %>
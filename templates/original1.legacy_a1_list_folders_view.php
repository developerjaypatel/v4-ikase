<div class="large_white_text" style="margin-top:20px">A1 Archived Documents</div>
<% _.each( folders, function(folder) { %>
<div><a class="list_files white_text" style="cursor:pointer"><%=folder.name.replaceAll("~~", " ") %></a></div>
 <% }); %>

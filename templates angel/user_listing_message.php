<div style="float:right" class="user_listing">
    <input type="checkbox" class="message_users" id="message_users" value="Y" title="Select All" />
</div>
<%=source.capitalizeWords() %>&nbsp;&nbsp;<span style="font-weight:bold; text-decoration:none" id="user_link_employees">Employees</span>&nbsp;|&nbsp;<span style="font-weight:normal; text-decoration:underline; cursor:pointer" id="user_link_contacts">Contacts</span>
<table id="user_listing_message_table" class="tablesorter user_listing" border="0" cellpadding="0" cellspacing="0">
    <tbody>
   <% var intCounter = 0; 
   _.each( workers, function(worker) {
        if (worker.user_name.trim()!="") {
    %>
    <tr class="worker_data_row worker_row_<%=worker.user_id%>">
        <td>
            <input type="checkbox" class="message_user" id="message_user_<%= worker.user_id %>" value="<%= worker.user_id %>" />
        </td>
        <td>
            <%=worker.nickname %>
        </td>
        <td>
            <input id="user_id_<%=worker.user_id %>" type="hidden" class="user_message_input" value="<%=worker.user_id %>" />
            <input id="user_name_<%=worker.user_id %>" type="hidden" class="user_message_input" value="<%=worker.user_name %>" />
            <%=worker.user_name %>
        </td>
    </tr>
    <% 		intCounter++;
        }
    }); %>
    </tbody>
</table>
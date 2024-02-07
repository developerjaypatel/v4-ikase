<div style="float:right" class="contact_listing">
    <input type="checkbox" class="message_contacts" id="message_contacts" value="Y" title="Select All" />
</div>
<%=source.capitalizeWords() %>&nbsp;&nbsp;<span style="font-weight:normal; text-decoration:underline; cursor:pointer" id="user_link_employees">Employees</span>&nbsp;|&nbsp;<span style="font-weight:bold; text-decoration:none" id="user_link_contacts">Contacts</span>
<table id="contact_listing_message_table" class="tablesorter contact_listing" border="0" cellpadding="0" cellspacing="0">
    <tbody>
   <% var intCounter = 0; 
   _.each( contacts, function(contact) {
   		contact.full_name = contact.first_name + " " + contact.last_name;
    %>
    <tr class="contact_data_row contact_row_<%=contact.contact_id%>">
    	<td>
        	<input type="checkbox" class="message_contact" id="message_contact_<%= contact.contact_id %>" value="<%= contact.contact_id %>" />
        </td>
        <td>
            <%=contact.email.replaceAll("@", " @ ") %>
        </td>
        <td>
            <input id="contact_id_<%=contact.contact_id %>" type="hidden" class="contact_message_input" value="<%=contact.contact_id %>" />
            <input id="contact_name_<%=contact.contact_id %>" type="hidden" class="contact_message_input" value="<%=contact.email %>" />
            <%=contact.full_name %>
        </td>
    </tr>
    <% 		intCounter++;
    }); %>
    </tbody>
</table>

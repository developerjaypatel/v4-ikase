<% if (kase_documents.length == 0) { %>
<div class="large_white_text" style="margin-top:20px">No documents</div>
<% } %>
<div style="float:right">
    <input type="checkbox" class="message_documents" id="message_documents" value="Y" title="Select All" />
</div>
Kase Documents <span style="font-size:0.8em">(Select from List to set Documents for Message)</span>
<table id="document_listing_message_table" class="tablesorter document_listing" border="0" cellpadding="0" cellspacing="0">
    <thead>
    <% if (kase_documents.length > 0) { %>
    <tr>
        <th>&nbsp;</th>
        <th align="left">
            Document
        </th>
        <th align="left">
            Upload Date
      </th>
    </tr>
    <% } %>
    </thead>
    <tbody>
   <% var intCounter = 0; 
   _.each( kase_documents, function(kase_document) {
    %>
    <tr class="kase_document_data_row kase_document_row_<%=kase_document.document_id%>">
    	<td>
        	<input type="checkbox" class="message_document" id="message_document_<%= kase_document.document_id %>" value="<%= kase_document.document_id %>" />
        </td>
        <td>
            <input id="document_id_<%=kase_document.document_id%>" type="hidden" class="document_message_input" value="<%=kase_document.id%>" />
            <%=kase_document.document_name.replaceAll("_", " ") %>
        </td>
        <td nowrap="nowrap">
        <span id="message_document_name_<%=kase_document.id%>"><%= kase_document.document_date %></span>
        </td>
    </tr>
    <% intCounter++;
    }); %>
    </tbody>
</table>

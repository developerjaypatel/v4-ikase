<div id="user_listing_message_table" class="tablesorter user_listing">    
   <% var intCounter = 0;
   _.each( workers, function(worker) {
        if (worker.user_name.trim()!="" && intCounter < 15) {
    %>
    <span style="margin-right: 5px;" class="worker_data_row worker_row_<%=worker.user_id%>">
        <label style="cursor: pointer;" title="<%=worker.user_name.toUpperCase() %>">
            <input type="checkbox" class="message_user" id="message_user_<%= worker.user_id %>" value="<%= worker.user_id %>" style="display: none;" />        
            <%=worker.nickname.toUpperCase() %>        
            <input id="user_id_<%=worker.user_id %>" type="hidden" class="user_message_input" value="<%=worker.user_id %>" />
            <input id="user_name_<%=worker.user_id %>" type="hidden" class="user_message_input" value="<%=worker.user_name %>" />
        </label>   
    </span>
    <% 		intCounter++;
        }
    }); %>
</div>
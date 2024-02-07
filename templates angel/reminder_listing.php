<div id="reminder_title" style="background:white; padding:2px; text-align:center; margin:0px">
	<div style="float:right; padding-right:2px">
		<i class="glyphicon glyphicon-warning-sign" style='font-size:0.7em; color:orange'></i>
    </div>
    <div style="float:left; padding-left:2px">
		<i class="glyphicon glyphicon-warning-sign" style='font-size:0.7em; color:orange'></i>
    </div>
    <div style="font-size:1.6em; width:250px; margin-left:auto; margin-right:auto">IMPORTANT REMINDER<% if (messages.length > 1) {%>S<% } %></div>
    <div style="font-size:0.7em; font-style:italic; margin-top:-10px; width:250px; margin-left:auto; margin-right:auto">click each to clear</div>
</div>
<div style="padding:3px;">
    <table id="reminder_listing" class="reminder_listing" border="0" cellpadding="0" cellspacing="0">
        <tbody>
       <% 
       _.each( messages, function(message) {
       %>
       	<tr class="message_data_row message_row_<%= message.id %>">
            <td style="font-size:1.2em; border-bottom:#EDEDED 1px solid" align="left" valign="top" class="reminder_row" id="reminder_<%=message.message_id %>_<%=message.case_id %>">
                <div style="font-weight:bold">
                	<div style="float:right">
                    	<%=message.case_name %>
                    </div>
                    <%=message.case_number %>
                </div>
                <div>
                	<%= message.message %>
                </div>
            </td>
        </tr>
        <% }); %>
        </tbody>
    </table>
</div>
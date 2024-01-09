<div id="popup_view">
<!--<form id="event_form" name="event_form" method="post" action="">
<input id="table_name" name="table_name" type="hidden" value="event" />
<input id="table_id" name="table_id" type="hidden" value="" />
<input id="injury_id" name="injury_id" type="hidden" value=" />
<input id="calendar_id" name="calendar_id" type="hidden" value="" />
<input id="case_id" name="case_id" type="hidden" value="" />
<input id="user_id" name="user_id" type="hidden" value="" />
<input id="event_kind" name="event_kind" type="hidden" value="" />-->
<table id="popup_table" border="0" align="left" cellpadding="3" cellspacing="0" width="100%">
    <tr>
        <td align="left" valign="top" id="reminder_popup_row_<%=reminders.panel_id %>">
            <div style="float:left">
                <select id="snooze_intervals_<%=reminders.panel_id %>" class="snooze_intervals" style="display:none">
                    <option value="">Select One...</option>
                    <option value="5">In 5 Minutes</option>
                    <option value="-5">5 Minutes before Event</option>
                    <option value="10">In 10 Minutes</option>
                    <option value="-10">10 Minutes before Event</option>                    
                    <option value="20">In 20 Minutes</option>
                    <option value="-20">20 Minutes before Event</option>    
                    <option value="60">In 60 Minutes</option>
                    <option value="-60">60 Minutes before Event</option>                                      
                </select>            
                <button type="button" class="btn btn-xs snooze" id="snooze_<%=reminders.panel_id %>" data-dismiss="modal" style="float:right;color:<%=reminders.arrMessages.color %>">Snooze</button>
            </div>
            <div style="float:right;">
                <button type="button" class="btn btn-xs read_reminder" id="close_reminder_<%=reminders.panel_id %>" data-dismiss="modal" style="float:right;color:<%=reminders.arrMessages.color %>">Mark Reminder Read</button>
                <input type="hidden" id="reminderbuffer_id_<%=reminders.panel_id %>" value="<%=reminders.arrMessages.reminderbuffer_id %>" />
            </div>
            <br>
            <br>
            <span style="color:white;">
                <label style="width:100px; display:inline-block;">Case:</label><a href="?n=#kase/<%=reminders.arrMessages.case_id %>" target="_blank" class="white_text"><%=reminders.arrMessages.case_name %></a>
            </span>
            <br>
            <span style="color:white;">
                <label style="width:100px; display:inline-block;">Subject:</label><%=reminders.arrMessages.subject %>
            </span>                        
            <br>
            <span style="color:white;">
                <label style="width:100px; display:inline-block;">Event&nbsp;Time:</label><%=reminders.arrMessages.date_time %>
            </span>
            <a title="Click to edit event" class="edit_event" id="<%= reminders.arrMessages.event_id %>_<%= reminders.arrMessages.case_id %>" style="cursor:pointer" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false"><i style="font-size:15px;color:#06f; cursor:pointer;" class="glyphicon glyphicon-edit" title="Click to Edit Event"></i></a>
            <br>
            <span style="color:white;">
                <label style="width:100px; display:inline-block;">Popup&nbsp;Time:</label><%=reminders.arrMessages.reminder_datetime %>
            </span>
            <br>
            <span style="color:white;">
                <label style="width:100px; display:inline-block;">Location:</label><a class="map_location" style="color:white;cursor:pointer;"><%=reminders.arrMessages.location %></a>
            </span> 
            <br>            
            <span style="color:white;">
                <label style="width:100px; display:inline-block;">Message:</label><%=reminders.arrMessages.plain_message %>
            </span>                                   
        </td>
    </tr>
</table>

</div>
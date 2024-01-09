<div class="stack_activity">
	<span style="font-size:1.2em; color:#FFFFFF">Assigned Batchscans (<%=activities.length %>)</span>
    <span style="color:white">&nbsp;&nbsp;From
            <input id="start_dateInput" class="range_dates" value="<%= start_date%>" style="width:80px" placeholder="From Date" /> through <input id="end_dateInput" class="range_dates" value="<%= end_date %>" style="width:80px" placeholder="Through Date" />
    </span>
	<div id="stack_activity_preview_panel" style="position:absolute; width:20px; display:none; z-index:2; background:white;" class="attach_preview_panel"></div>
	<table id="stack_activity_listing" class="tablesorter stack_activity_listing" border="0" cellpadding="0" cellspacing="1">
        <thead>
        <tr>
            <th>
            	Document
            </th>
            <th>
            	Case
            </th>
             <th>
            	
            </th>
            <th>
            	By
            </th>
            <th>
            	Notified
            </th>
            <th>
            	Date
            </th>
            
        </tr>
        </thead>
        <tbody>
        <% 
        var current_day = "";
        _.each( activities, function(activity) {
        //we might have a new day
            var the_day = moment(activity.activity_date).format("MMDDYY");
            var date_string = moment(activity.activity_date).valueOf();
            if (current_day != the_day) {
                current_day = the_day;
            
        %>
        <tr class="date_row row_<%= the_day %>">
            <td colspan="6">
                <div style="width:100%; 
                    text-align:left; 
                    font-size:1.8em; 
                    background:#CFF; 
                    color:red;"><%= moment(activity.activity_date).format("dddd, MMMM Do YYYY") %>
                 </div>
            </td>
        </tr>
        <% } %>
        <tr class="activity_data_row_<%=activity.activity_uuid %>">
            <td valign="top" align="left"><%= activity.path %></td>
            <td valign="top" align="left" width="5%"><%= activity.case_number %></td>
            <td valign="top" align="left" width="40%"><%=activity.name %></td>
            <td valign="top" align="left" width="10%"><%= activity.by %></td>
            <td valign="top" align="left" width="20%"><%= activity.notifieds %></td>
            <td valign="top" align="left" nowrap="nowrap"><%= moment(activity.activity_date).format("MM/DD/YY h:mmA") %></td>
        </tr>
        <% }); %>
        </tbody>
    </table>
</div>
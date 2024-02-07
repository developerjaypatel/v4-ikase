<div>
<div>
	<div>
    	<div style="float:right">
        <?php
		$day = date('w');
		$week_start = date('Y-m-d', strtotime('-'.$day.' days'));
		$week_end = date('Y-m-d', strtotime('+'.(6-$day).' days'));
		
		$next_week_start = date('Y-m-d', strtotime('+'.(13-$day).' days'));
		$next_week_end = date('Y-m-d', strtotime('+'.(19-$day).' days'));
		?>
        <%
        var today = "<?php echo date("Y-m-d"); ?>";
        var week_start = "<?php echo $week_start; ?>";
        var week_end = "<?php echo $week_end; ?>";
        var next_week_start = "<?php echo $next_week_start; ?>";
        var next_week_end = "<?php echo $next_week_end; ?>";
        %>
        
      </div>
    	<%
        var href = Backbone.history.getFragment();
        if (href.indexOf('listkalendar') > -1) {
            var arrHref = href.split("/");
            href = "ikalendar/" + arrHref[1] + "/" + arrHref[2];
        }
        %>
	</div>
    <div id="glass_header" class="glass_header" style="padding-top:5px">
        <span style="font-size:1.5em; color:#FFFFFF">Events</span>&nbsp;(<%=occurences.length %>)
        <% if (case_id!="") { %>
        <a title="Click to compose a new event" href="#eventmobile/<%=case_id %>" class="compose_new_event_mobile" id="compose_event_<%=case_id %>" style="cursor:pointer"><i class="glyphicon glyphicon-plus" style="color:#99FFFF">&nbsp;</i></a>          
        <% } %>
    </div>
    <% if (occurences.length > 0) { %>
    <div id="occurence_preview_panel" style="position:absolute; width:200px; display:none; z-index:2; background:white; font-size:12px" class="attach_preview_panel"></div>
    <table id="occurence_listing" class="tablesorter occurence_listing" border="0" cellpadding="0" cellspacing="0">
        <thead>
        <tr>
            <th style="font-size:18px; width:13%">
                Time
            </th>
            <th style="font-size:18px; width:4%">
            	Type
            </th>
            <th style="font-size:18px; width:4%">
            	Assigned
            </th>
            <th style="font-size:18px; width:10%">
                Venue/Judge
            </th>
        </tr>
       
        </thead>
        <tbody>
       <% var current_day;
       _.each( occurences, function(occurence) {
       		var holder_display = "";
            var read_display = "none";
            if (title=="Phone Messages") {
                if (occurence.read_status=="N") {
                    holder_display = "none";
                    read_display = "";
                }
            }
            var arrToUserNames = [];
            
            //lookup all the user_name
            var arrToUsers = occurence.assignee.split(";");
            arrayLength = arrToUsers.length;
            for (var i = 0; i < arrayLength; i++) {
            	var theworker = worker_searches.findWhere({"user_id": arrToUsers[i]});
                
                if (typeof theworker != "undefined") {
                    arrToUserNames[arrToUserNames.length] = theworker.get("user_name");
                } else {
                    arrToUserNames[arrToUserNames.length] = arrToUsers[i];
                }
            }
            if (occurence.event_title=="") {
	            occurence.event_title = "Edit";
            }
            if (occurence.case_id==null) {
            	occurence.case_id = "";
            }
            //we might have a new day
            var the_day = moment(occurence.event_dateandtime).format("MMDDYY");
            if (current_day != the_day) {
                current_day = the_day;
        %>
        <tr>
            <td colspan="6">
            	<div style="width:100%; 
text-align:left; 
font-size:1.8em; 
background:#CFF; 
color:red;">	
                <%= moment(occurence.event_dateandtime).format("dddd, MMMM Do YYYY") %></div>
            </td>
        </tr>
        <% 	} %>
       	<tr class="occurence_data_row occurence_row_<%= occurence.id %>">
            <td style="font-size:15px" nowrap="nowrap">
            <!-- //moment(occurence.event_dateandtime).format("hh:mm a") -->
            <% if (title!="Phone Messages") { %>
             <input type="checkbox" id="check_assign_<%=occurence.id %>_<%= moment(occurence.event_dateandtime).format('MM-DD-YYYY') %>" class="check_thisone check_thisone_<%= moment(occurence.event_dateandtime).format('MM-DD-YYYY') %>"  />
            <% } %>
            <%= occurence.time %>&nbsp;<% if (occurence.event_priority=="high") { %>
                <span style="padding-left:2px; padding-right:2px" title="High Priority">
                <img src="img/output_IW6x8Z.gif" width="15" height="20" /></span>
                <% } %>
                <% if (occurence.event_priority=="low") { %>
                    <i class="glyphicon glyphicon-arrow-down" style="color:#06C; background:white" title="Low Priority"></i>
                <% } %>
           </td>
           <td style="font-size:15px">
            <%=occurence.event_type %>
           </td>
           <td style="font-size:15px">
            <%= occurence.assignee.toUpperCase() %>
           </td>
           <td style="font-size:15px">
            <%=occurence.venue_abbr %> / <%=occurence.judge %>	
            </td>
        </tr>
        
        <tr class="occurence_data_row occurence_row_<%= occurence.id %> occurence_row_<%= occurence.id %>_details" style="display:">
        	<td style="font-size:15px" nowrap="nowrap" colspan="6">
                <% if (occurence.case_name!="" && occurence.case_name!=null) { 
                    var maxlength = 255;
                    if (homepage==true) {
                        var maxlength = 32;
                    }
                    var case_name = occurence.case_name;
                    if (occurence.case_name.length > maxlength) {
                        case_name = occurence.case_name.substr(0, maxlength) + "...";
                    }
                    
                    %>
                    <a href="?n=#kase/<%=occurence.case_id %>" title="<%=occurence.case_number %> - <%=occurence.case_name %>" class="list-item_kase" style="color:white; background:black" target="_blank">
                        <%=case_name %>
                    </a>
                <% } %>
            </td>
        </tr>
        
        <% if (occurence.event_title!=occurence.event_description) { %>
        <tr class="occurence_data_row occurence_row_<%= occurence.id %> occurence_row_<%= occurence.id %>_details" style="display:">
            <td style="font-size:15px" colspan="6">
            	<% if (occurence.event_description!="" || occurence.event_title!="") { %>
                    <span style="text-decoration:underline">Details:&nbsp;</span>
                    <% if (occurence.event_title!="") { %>
	                    <%=occurence.event_title %><br />
                    <% } %>
                    <% if (occurence.event_description!="") { %>
    	                <%=occurence.event_description %>
                    <% } %>
                <% } %>&nbsp;
            </td>
        </tr>
        <% } %>
        <% }); %>
        </tbody>
    </table>
</div>
<% } else { %>
<div>There are no Events listed for today.</div>
<% } %>
<?php 
$attorney_first_name = "";
$attorney_last_name = "";

$previousmonth_start  = mktime(0, 0, 0, date("m") - 1,  1,   date("Y"));
$previousmonth_end  = date("Y-m-t", $previousmonth_start);
$previousmonth_start = date("Y-m-d", $previousmonth_start);

$nextmonth_start  = mktime(0, 0, 0, date("m") + 1,  1,   date("Y"));
$nextmonth_end  = date("Y-m-t", $nextmonth_start);
$nextmonth_start = date("Y-m-d", $nextmonth_start);
?>
<table id="occurence_listing" align="center" class="tablesorter" border="0" cellpadding="2" cellspacing="0" width="1080px">
        <thead>
        <tr>
            <td style="border:0px solid blue" colspan="2"><img src="img/ikase_logo_login.png" height="32" width="77"></td>
            <td align="left" colspan="9">
				<span style="font-size:1.5em; margin-top-3px; margin-left:10px">
                <% if (typeof calendar_title != "undefined") { %>
                <%=calendar_title %>
                <% } %>
                <% if (typeof case_id != "undefined") { %>Kase <% } %>Events
                <% if (typeof case_id == "undefined") { %>
	                <%=worker %><%=type %>
                    <% if (start!="") { %>
                    <span id="date_range_area" title="Double-click to activate date range search" style="cursor:pointer"><%=start %><%if (start!=end) {%> - <%=end %><% } %></span><span id="date_range_area_input" style="display:none;"><span style="font-size:0.5em">From <input id="start_dateInput" class="range_dates" value="<%=start %>" style="width:70px" /> through <input id="end_dateInput" class="range_dates" value="<%=end %>" style="width:70px" /></span>&nbsp;
                    <button style="color:green; cursor:pointer; font-size:0.5em; visibility:hidden" id="update_date_range" title="Click to Update the Event Dates on this list" class="btn btn-md">Click to Update List</button>
                    </span>
                    <% } %>
                <% } %>
                </span>
                <div style="float:right; margin-top:10px">
                    <em>as of <?php echo date("m/d/y g:iA"); ?></em>
                </div>
                <% if (start!="") { %>
                <br />
                <div style="padding-left:10px;font-size:0.8em">
                	<a href="#ikalendar/<%=current_calendar_id %>/<%=current_sort_order %>/<?php echo $previousmonth_start; ?>/<?php echo $previousmonth_end; ?>">Previous Month</a>&nbsp;|&nbsp;<a href="#ikalendar/<%=current_calendar_id %>/<%=current_sort_order %>/<?php echo date("Y-m-") . "01"; ?>/<?php echo date("Y-m-t"); ?>">This Month</a>&nbsp;|&nbsp;<a href="#ikalendar/<%=current_calendar_id %>/<%=current_sort_order %>/<?php echo $nextmonth_start; ?>/<?php echo $nextmonth_end; ?>">Next Month</a>
                </div>
                <% } %>
            </td>
          </tr>
         <% if (typeof case_id != "undefined") {
         %>
         <tr>
            <th align="left" colspan="10">
                <span style="border:1px solid blue; padding:2px">Case: <%= occurences[0].case_number %><%= occurences[0].case_name %></span>
            </th>
        </tr>
         <% } %>
		 <tr>
            <th style="font-size:1.5em" align="left" colspan="10">
                <br />
            </th>
        </tr>
		<tr style="">
            <th style="font-size:1em" align="left" width="10px">
                <span style="font-size:0.8em">&nbsp;</span>
            </th>
            <th style="font-size:1em" align="left">
                Time
            </th>
            <th style="font-size:1em; width:100px" align="left">Type</th>
            <th style="font-size:1em; width:100px" align="left"> Atty</th>
			<th style="font-size:1em; width:100px" align="left">
        Supv&nbsp;Atty</th>
			<th style="font-size:1em" align="left" colspan="2">
                Case&nbsp;No</th>
			<th style="font-size:1em" align="left" colspan="1" width="38%">
                Venue 
            </th>
            <!--
			<th style="font-size:1em" align="left">
                Assigned 
            </th>
            
            <th style="font-size:1em" align="right">
            Appt&nbsp;No. </th>
            -->
            <th style="font-size:1em" align="left" width="38%">Event</th>
        </tr>
        </thead>
        <tbody>
       <% var current_day;
       var blnNewDay = false;
		var day_counter = 0;
        var intCounter = 1;
        var background = "";
       _.each( occurences, function(occurence) {  
       		var holder_display = "";
            var read_display = "none";
            var arrToUserNames = [];
            
            //lookup all the user_name
            var arrToUsers = occurence.assignee.split(";");
            arrayLength = arrToUsers.length;
            for (var i = 0; i < arrayLength; i++) {
                arrToUserNames[arrToUserNames.length] = arrToUsers[i];
            }
            if (occurences.case_number==null) {
                occurences.case_number = "";
            } else {
                occurences.case_number += " - ";
            }
            if (occurences.case_name==null) {
                occurences.case_name = "";
            }
            if (occurence.event_title=="") {
	            occurence.event_title = "Edit";
            }
            if (occurence.case_id==null) {
            	occurence.case_id = "";
            }
            //we might have a new day
            var the_day = moment(occurence.event_dateandtime).format("MMDDYY");
            blnNewDay = false;
            day_counter++;
            
            if (current_day != the_day) {
            	background = "background:#EDEDED;";
                if ((intCounter%2)==0) {
                    background = "background:#FFFFFF;";
                }
            	intCounter++;
                current_day = the_day;
                blnNewDay = true;
                day_counter = 1;
        %>
        <tr>
            <td style="<%=background %>" align="center" colspan="11">
                <div style="width:100%; 
                text-align:center; 
                font-size:1.4em;
                color:#000080; 
                ">
                	<%= moment(occurence.event_dateandtime).format("dddd, MMMM Do YYYY") %>&nbsp;<span style="font-size:0.5em">(<%=arrDayCount[the_day] %>)</span>
                </div>
            </td>
        </tr>
        <% 	} %>
        <tr style="<%=background %>">
			<td width="10px">
            	<span style="font-size:0.8em"><%=day_counter %>)&nbsp;</span>
            </td>
        	<td colspan="1" align="left" valign="top" style="font-size:.9em; margin-left:10px" nowrap="nowrap">
            
            <% if (occurence.event_priority=="high") { %>
                    High Priority
                    <% } %>
          <% if (occurence.event_priority=="low") { %>
                    	Low Priority
                    <% } %>
                <%
                /*
                var the_time = moment(occurence.event_dateandtime).format("hh:mma");
                if (the_time == "12:00 am") {
                	the_time = "8:00 am";
                }
                */
                var the_time = occurence.time;
                if (the_time != "") { %>
                <strong><%= the_time %></strong>
                <% } %>
            </td>
        	<td align="left" valign="top" style="margin-left:0px" nowrap="nowrap"><%=occurence.event_type.replaceAll("_", " ").capitalizeWords() %></td>
        	<td align="left" valign="top" style="margin-left:0px"><%=occurence.supervising_attorney %></td>
			<td colspan="1" align="left" valign="top" style="margin-left:0px">
	            <%= occurence.assignee %>
			  </td>
                
            
			<td colspan="2" align="left" valign="top" style="font-size:0.9em;" nowrap="nowrap">
                <% if (typeof occurence.case_number != "undefined" && occurence.case_number != "" && occurence.case_number != null) { %>
                <%=occurence.case_number %>
                <% } %>
            </td>
			<td colspan="1" align="left" valign="top">
            	<%
                if (occurence.judge!="") {
                    occurence.location += ",&nbsp;" + occurence.judge; 
                }
                %>
                <%=occurence.location %>
            </td>
			<!--
            <td colspan="1" align="left" valign="top" style="margin-left:10px">
                <% if (arrToUserNames.join("; ")!="") { %><%= arrToUserNames.join("; ") %><% } %>>
                <% if (occurence.event_from!="") { %>&nbsp;&nbsp;by <%= occurence.event_from %><% } %>
		        <% if (occurence.full_address!="") { %>		
	                <%=occurence.event_description %>
				<% } %>
            </td>
            
            <td align="right" valign="top">
            	<%=occurence.id %>
            </td>
            -->
            <td rowspan="2" align="left" valign="top" style="border-bottom:2px solid black; padding-bottom:5px">
            <% if (occurence.event_name.trim() != "") { %>
                <%=occurence.event_name %>
            <% } else { %>
            	<% if (occurence.event_title!="" && occurence.event_title!="Edit") { %>
                    <%=occurence.event_title %>
                <% } %>
            <% } %>
            </td>
        </tr>
        <tr style="<%=background %>;">
          <td colspan="8" align="left" valign="top" style="border-bottom:2px solid black; padding-bottom:5px">
          <% if (occurence.case_name.trim() != "") { %>
          	<span style="border:1px solid blue; margin:2px; padding:2px;">
	          	<%= occurence.case_name %>
            </span>
            <% } %>
            <% if (occurence.event_description.trim() != "") {
            	if (occurence.event_description.indexOf('<p>Kase Intake') > -1) {
                	occurence.event_description = occurence.event_description.replace("<p>", "");
                    occurence.event_description = occurence.event_description.replace("</p>", "");
                } %>
	            <div style='padding-top:3px'><%=occurence.event_description %></div>
            <% } %>
          </td>
          </tr>
        <% }); %>
        </tbody>
    </table>
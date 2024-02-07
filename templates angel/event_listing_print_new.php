<?php 
include("../api/manage_session.php");
session_write_close();
if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
	header("location:index.html");
	die();
}

$attorney_first_name = "";
$attorney_last_name = "";

$previousmonth_start  = mktime(0, 0, 0, date("m") - 1,  1,   date("Y"));
$previousmonth_end  = date("Y-m-t", $previousmonth_start);
$previousmonth_start = date("Y-m-d", $previousmonth_start);

//die($previousmonth_start . " - " . $previousmonth_end);

$nextmonth_start  = mktime(0, 0, 0, date("m") + 1,  1,   date("Y"));
$nextmonth_end  = date("Y-m-t", $nextmonth_start);
$nextmonth_start = date("Y-m-d", $nextmonth_start);

$blnShowApplicantPhone = ($_SESSION["user_customer_id"]==1064);
?>
<table id="occurence_listing" align="center" class="tablesorter" border="0" cellpadding="2" cellspacing="0" width="1080px">
        <thead>
        <tr>
            <td style="border:0px solid blue" colspan="2" width="1%" nowrap=""><img src="img/ikase_logo_login.png" height="32" width="77"></td>
            <td colspan="9" align="left">
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
                	<a href="#ikalendar/<%=current_calendar_id %>/<%=current_sort_order %>/<?php echo $previousmonth_start; ?>/<?php echo $previousmonth_end; ?>" id="link_previous_month">Previous Month</a>
                    &nbsp;|&nbsp;
                    <a href="#ikalendar/<%=current_calendar_id %>/<%=current_sort_order %>/<?php echo date("Y-m-") . "01"; ?>/<?php echo date("Y-m-t"); ?>" id="link_this_month">This Month</a>
                    &nbsp;|&nbsp;
                    <a href="#ikalendar/<%=current_calendar_id %>/<%=current_sort_order %>/<?php echo $nextmonth_start; ?>/<?php echo $nextmonth_end; ?>" id="link_next_month">Next Month</a>
                </div>
                <% } %>
            </td>
          </tr>
         <% if (typeof case_id != "undefined") { %>
         <tr>
            <th align="left" colspan="8">
                <span style="border:1px solid blue; padding:2px">Case: <%= occurences[0].case_number %><%= occurences[0].case_name %></span>
            </th>
        </tr>
         <% } %>
		</thead>
</table>
<table id="occurence_listing" align="center" class="tablesorter" border="0" cellpadding="2" cellspacing="0" width="1080px">
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
            	background = "background:#FFFFFF;";
                if ((intCounter%2)==0) {
                    background = "background:#EDEDED;";
                }
            	intCounter++;
                current_day = the_day;
                blnNewDay = true;
                day_counter = 1;
        %>
        <tr>
            <td style="<%=background %>" align="center" colspan="8">
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
			<td align="left" valign="top" colspan="8">
            	<% if (occurence.case_language!="English" && occurence.case_language!="") { %>
                    <div style="float:right">
                    <%= occurence.case_language %>
                    </div>
                <% } %>
            	<div style="">
                    <div style="font-weight:bold; display:inline-block">
                        <span style="font-size:0.8em"><%=day_counter %>)&nbsp;</span>            
                        <% if (occurence.event_priority=="high") { %>
                        High Priority
                        <% } %>
                        <% if (occurence.event_priority=="low") { %>
                        Low Priority
                        <% } %>
                        <%
                        var the_time = occurence.time;
                        if (the_time != "") { %>
                        <%= the_time %>
                        <% } %>
                    </div>
                    <div style="display:inline-block">
                        <% if (occurence.case_name.trim() != "") { %>
                        <div style="border:1px solid blue; margin:2px; padding:2px;">
                            <% if (typeof occurence.case_number != "undefined" && occurence.case_number != "" && occurence.case_number != null) { %>
                            <%=occurence.case_number %>&nbsp;&nbsp;
                            <% } %>
                            <%= occurence.case_name %>
                        </div>
                        <% } %>
                    </div>
                </div>
                <div>
	                Subject: 
                    <% if (occurence.event_name.trim() != "") { %>
                        <%=occurence.event_name %>
                    <% } else { %>                
                        <% if (occurence.event_title!="" && occurence.event_title!="Edit") { %>
                            <%=occurence.event_title %>
                        <% } %>
                    <% } %>
                </div>
				<div>
                	<div style="float:right">
                    	<% if (occurence.judge!="") { %>
                    	<div>
                            Judge: <%=occurence.judge %>
                        </div> 
                        <% } %>
                    	
                        <?php if ($blnShowApplicantPhone) { ?>
                        <% if (occurence.supervising_attorney_name!="" && occurence.supervising_attorney!=occurence.assignee) { %>
                        <div style="font-weight:bold">Atty: <%=occurence.supervising_attorney_name.split(" ")[0] %></div>
                        <% } %>
                        <% if (occurence.worker_name!="" && occurence.worker_name!=occurence.assignee) { %>
                        <div style="font-weight:bold">Coord: <%=occurence.worker_name.split(" ")[0] %></div>
                        <% } %>
                        <?php } else { ?>
                        <% if (occurence.supervising_attorney!="" && occurence.supervising_attorney!=occurence.assignee) { %>
                        <div style="font-weight:bold">Attorney: <%=occurence.supervising_attorney %></div>
                        <% } %>
                        <?php } ?>
                        <% if (occurence.assignee!="") { %>
                        <div style="font-weight:bold">Assignee: <%= occurence.assignee %></div>
                        <% } %>
                    </div>
                    <% if (occurence.event_title!=occurence.event_type.replaceAll("_", " ").capitalizeWords()) { %>
                	Type: <%=occurence.event_type.replaceAll("_", " ").capitalizeWords() %>
                    <% } %>
                </div> 
                <?php if ($blnShowApplicantPhone) { ?>
                <% if (occurence.applicant_phone!="" || occurence.applicant_work!="" || occurence.applicant_cell!="") { %>         
                    <% if (occurence.applicant_phone!="") { %>         
                    <div style="display:inline-block; margin-right:10px">
                        Applicant Phone: <%=occurence.applicant_phone %>
                    </div> 
                    <% } %>
                    <% if (occurence.applicant_work!="") { %>         
                    <div style="display:inline-block; margin-right:10px">
                        Applicant Work: <%=occurence.applicant_work %>
                    </div> 
                    <% } %>
                    <% if (occurence.applicant_cell!="") { %>         
                    <div style="display:inline-block; margin-right:10px">
                        Applicant Cell: <%=occurence.applicant_cell %>
                    </div> 
                    <% } %>        
                <% } %>
                <?php } ?>
                <% if (occurence.location!="") { %>         
            	<div>
	                Location: <%=occurence.location %>
                </div>         
                <% } %>
                <div style='padding-top:3px'>
                <% if (occurence.event_description.trim() != "") {
                    if (occurence.event_description.indexOf('<p>Kase Intake') > -1) {
                        occurence.event_description = occurence.event_description.replace("<p>", "");
                        occurence.event_description = occurence.event_description.replace("</p>", "");
                    } %>
                    Details: <%=occurence.event_description %>
                <% } %>
                </div>
          </td>
        </tr>
        <tr>
        	<td colspan="8">
            	<hr />
            </td>
        </tr>
        <% }); %>
        </tbody>
    </table>
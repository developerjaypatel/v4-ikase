<?php 
	$attorney_first_name = "";
	$attorney_last_name = "";
?>
<table id="occurence_listing" align="center" class="tablesorter" border="0" cellpadding="2" cellspacing="0" width="90%">
        <thead>
        <tr>
            <td style="border:0px solid blue"><img src="img/ikase_logo_login.png" height="32" width="77"></td>
            <td align="left" colspan="5">
				<span style="font-size:1.5em; margin-top-3px; margin-left:10px"><% if (typeof case_id != "undefined") { %>Kase <% } %>Events Scheduled - <%=start %> - <%=end %></span>
                <div style="float:right">
                    <em>as of <?php echo date("m/d/y g:iA"); ?></em>
                </div>
                <span style="font-family:Arial, Helvetica, sans-serif; font-weight:bold; font-size:1.5em">&nbsp;</span>
            </td>
          </tr>
         <% if (typeof case_id != "undefined") { %>
         <tr>
            <th style="font-size:1.2em; border:1px solid green" align="left" colspan="6">
                Case: <%= occurences[0].case_number %> - <%= occurences[0].case_name %>
            </th>
        </tr>
         <% } %>
		 <tr>
            <th style="font-size:1.5em" align="left" colspan="6">
                <br />
            </th>
        </tr>
		<tr style="">
            <th style="font-size:1em; border-bottom:1px solid black" align="left">
                Time
            </th>
			<th style="font-size:1em; border-bottom:1px solid black; width:100px" align="left">
                Attorney
            </th>
			<th style="font-size:1em; border-bottom:1px solid black" align="left" colspan="2">
                Case 
            </th>
			<th style="font-size:1em; border-bottom:1px solid black" align="left" colspan="1">
                Event 
            </th>
            <?php if ($customer_id == "1033") { ?>
			<th style="font-size:1em; border-bottom:1px solid black" align="left">
                Venue 
            </th>
            <?php } ?>
            <th style="font-size:1em; border-bottom:1px solid black" align="left">
                Assigned 
            </th>
        </tr>
        </thead>
        <tbody>
       <% var current_day;
       var blnNewDay = false;
		var day_counter = 0;
       _.each( occurences, function(occurence) {            	
       		var holder_display = "";
            var read_display = "none";
            var arrToUserNames = [];
            
            //lookup all the user_name
            var arrToUsers = occurence.assignee.split(";");
            arrayLength = arrToUsers.length;
            for (var i = 0; i < arrayLength; i++) {
                var theworker = worker_searches.findWhere({"nickname": arrToUsers[i]});
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
            blnNewDay = false;
            if (current_day != the_day) {
                current_day = the_day;
                blnNewDay = true;
                background = "#ECECEC";
                //if (day_counter > 0) {
                    if ((day_counter%2)==0) {
                        background = "#FFFFFF";
                    }
                //}
                day_counter++;
        %>
        <tr>
            <td style="background:<%=background %>" align="center" colspan="6">
                <div style="width:100%; 
text-align:center; 
font-size:1.4em;
color:#000080; 
"><%= moment(occurence.event_dateandtime).format("dddd, MMMM Do YYYY") %></div>
            </td>
        </tr>
        <% 	} %>
        <tr style="background:<%=background %>; border-bottom:0px solid black">
			
        	<td style="font-size:.9em; margin-left:10px; border-bottom:0px solid black" valign="top" colspan="1">
            
            <% if (occurence.event_priority=="high") { %>
                    High Priority
                    <% } %>
                    <% if (occurence.event_priority=="low") { %>
                    	Low Priority
                    <% } %>
                <%
                var the_time = moment(occurence.event_dateandtime).format("hh:mm a");
                if (the_time == "12:00 am") {
                	the_time = "8:00 am";
                }
                if (the_time != "") { %>
                <%= the_time %>
                <% } %>
            </td>
			<td style="font-size:1.2em; margin-left:0px; border-bottom:0px solid black" colspan="1">
				<?php echo $attorney_first_name . " " . $attorney_last_name; ?>
				</td>
                
            
			<td style="font-size:1.2em; margin-left:10px; border-bottom:0px solid black" valign="top" colspan="2">
                <% 
                if (typeof occurence.case_number != "undefined" && occurence.case_number != "") { %>
                <%=occurence.case_number %> - <%=occurence.case_name %>
                <% 
                } %>
            </td>
			<td style="font-size:1.2em; margin-left:10px; border-bottom:0px solid black;" valign="top" colspan="1" nowrap="nowrap">
                <% 
                if (occurence.event_title!="") { %>
                	<%=occurence.event_title %>
             <% } %>
            </td>
            <?php if ($customer_id == "1033") { ?>
			<td style="font-size:1.2em; margin-left:10px; border-bottom:0px solid black" valign="top" colspan="1">
                <% if (arrToUserNames.join("; ")!="") { %>Assigned to: <%= arrToUserNames.join("; ") %><% } %><% if (occurence.event_from!="") { %>&nbsp;&nbsp;by <%= occurence.event_from %><% } %>
                <% if (occurence.full_address!="") { %>
				
                <%=occurence.event_description %>
                
				<% } %>
            </td>
			<?php
            }
			?>
            <td style="font-size:1.2em; margin-left:10px; border-bottom:0px solid black" valign="top" colspan="1">
                <% if (arrToUserNames.join("; ")!="") { %>Assigned to: <%= arrToUserNames.join("; ") %><% } %><% if (occurence.event_from!="") { %>&nbsp;&nbsp;by <%= occurence.event_from %><% } %>
                <% if (occurence.full_address!="") { %>
				
                <%=occurence.event_description %>
                
				<% } %>
            </td>
        </tr>
        <% }); %>
        </tbody>
    </table>
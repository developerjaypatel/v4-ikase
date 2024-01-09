<?php
require_once('../shared/legacy_session.php');
include_once("../rootdata.php");
session_write_close();
$blnDeletePermission = true;
if ($_SESSION["user_customer_id"]==1075) {
	//per steve g 4/3/2017
	$blnDeletePermission = false;
	if (strpos($_SESSION['user_role'], "admin") !== false) {
		$blnDeletePermission = true;
	}
}
?>
<div id="confirm_delete" style="display:none; position:absolute; top:20px; color:white; background:black; border:1px solid white; font-size:1.2em; padding:10px; text-align:center; z-index:1051">
	<div style="width:350px; margin-left:auto; margin-right:auto; ">
    <input type="hidden" name="confirm_delete_id" id="confirm_delete_id" value="" />
    Are you sure you want to delete this Event?
    <div style="padding:5px; text-align:center"><a id="delete_occurence" class="delete_yes white_icon" style="cursor:pointer">YES</a></div>
    <div style="padding:5px; text-align:center"><a id="cancel_occurence" class="delete_no white_icon" style="cursor:pointer">NO</a></div>
    </div>
</div>
<div>
<div class=<%=event_class %>>
	<div class="glass_header" style="background:url(../img/glass.png);  height:70px">
    	<div style="float:right; margin-top:4px" id="event_print_links">
        <?php
		$day = date('w');
		$week_start = date('Y-m-d', strtotime('-'.$day.' days'));
		$week_end = date('Y-m-d', strtotime('+'.(6-$day).' days'));
		
		$next_week_start = date('Y-m-d', strtotime('+'.(7-$day).' days'));
		$next_week_end = date('Y-m-d', strtotime('+'.(13-$day).' days'));
		?>
        <%
        var today = "<?php echo date("Y-m-d"); ?>";
        var week_start = "<?php echo $week_start; ?>";
        var week_end = "<?php echo $week_end; ?>";
        var next_week_start = "<?php echo $next_week_start; ?>";
        var next_week_end = "<?php echo $next_week_end; ?>";
        var list_worker = worker;
        if (list_worker=="") {
        	list_worker = " ";
        }
        var list_type = thetype;
        if (list_type=="") {
        	list_type = " ";
        }
        var blnCourtCalendar = (title == "Unassigned Court Calendar Events");
        %>
        <% if (kasepage) {%>
        <a href="report.php#kasekalendar/<%= current_case_id %>/2000-01-01/2200-01-01" target="_blank" title='Click to print listed events' style='cursor:pointer' class="white_text">PRINT</a>
        <div id="kase_kalendar_view" style="display:none; margin-right:20px"></div>
        <% } %>
        <% if (title!="Phone Messages" && !kasepage) {%>
        	<% if ( list_type!=" ") {%>
            <% } %>
            <label style="color:white">Print:&nbsp;&nbsp;</label>
            <span style="color:white">
                <a id="print_today_<%= customer_firm_calendar_id %>_<%=start %>_<%=end %>" title='Click to print list&rsquo;s events' style='cursor:pointer' class="print_today white_text">List</a>
                <% if (!blnCourtCalendar) { %>
                &nbsp;|&nbsp;
                <a id="print_today_<%= customer_firm_calendar_id %>_<%=today %>_<%=today %>" title='Click to print today&rsquo;s events' style='cursor:pointer' class="print_today white_text">Today</a><% if (typeof arrDayCount[<?php echo date("mdy"); ?>] != "undefined") { %>&nbsp;<span style='color:black; font-size:0.7em' title='There are <%=arrDayCount[<?php echo date("mdy"); ?>] %> events for this day'>(<%=arrDayCount[<?php echo date("mdy"); ?>] %>)</span><% } %>
                &nbsp;|&nbsp;
                <a id="print_week_<%= customer_firm_calendar_id %>_<%=week_start %>_<%=week_end %>" title='Click to print this week&rsquo;s events' style='cursor:pointer' class="print_today white_text">This Week</a>
                &nbsp;|&nbsp;
                <a id="print_today_<%= customer_firm_calendar_id %>_<%=next_week_start %>_<%=next_week_end %>" title='Click to print next week&rsquo;s events' style='cursor:pointer' class="print_today white_text">Next Week</a>
                <% } else { %>
                	<span style="padding-left:10px; font-size:0.8em; font-style:italic">Import Date: <%=import_date %></span>
                <% } %>
            </span>
        <% } %>
        </div>
    	<%
        var href = Backbone.history.getFragment();
        if (href.indexOf('listkalendar') > -1) {
            var arrHref = href.split("/");
            href = "ikalendar/" + arrHref[1] + "/" + arrHref[2];
        }
        
        %>
        
        <span style="font-size:1.2em; color:#FFFFFF"><%=title %></span>
        &nbsp;<div style="display:inline-block; padding-left:3px; margin-top:-25px; color:white; font-size:0.8em; width:20px">(<%=occurences.length %>)</div>
        
        <% if (title!="Phone Messages" && !kasepage) {%>
        	<!--<div style="position:relative; left:<% if (homepage) { %>195px<% } else { %>385px<% } %>; margin-top:-22px; <% if (homepage) { %>width:150px<% } else { %>width:875px<% } %>">-->
            <!-- <div style=""> -->
                <span class="white_text">
                <% if (!homepage) { %>
                    <%= assignee_filter %>&nbsp;&nbsp;
                <% } %>
                <%= event_type_filter %>
                <% if (!homepage && start!="") { %>
                    &nbsp;|&nbsp;Date Range <input id="start_dateInput" class="range_dates" value="<%=moment(start).format('MM/DD/YYYY') %>" style="width:80px" /> through <input id="end_dateInput" class="range_dates" value="<%= moment(end).format('MM/DD/YYYY') %>" style="width:80px" />&nbsp;<button class="btn btn-xs btn-primary" style="visibility:hidden" id="update_date_range" title="Click to Update the Event Dates on this list">Update List</button>
                <% } %>
                </span>
                &nbsp; 
                <!-- style="margin-top:-5px" in Add Event button tag code -->
                <button class="btn btn-sm btn-primary" id="new_event" >Add Event</button>
            <!-- </div> -->
        <% } %>
        <% if (!homepage && kasepage) { %>
        <button class="btn btn-sm btn-primary" id="add_event" style="margin-top:-5px">Add Event</button>
        <% } %>
        	<!--
            <button title="new occurence" id="new_occurence" class="btn btn-transparent" style="color:white; border:0px solid; width:20px" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false">
                <i class="glyphicon glyphicon-plus-sign" style="color:#00FFFF">&nbsp;</i>
            </button>
            -->
            <% if (title!="Phone Messages") {%>
            <select name="mass_change" id="mass_change" style="width:225px; display:none">
              <option value="" selected="selected">Choose Action</option>
              <option value="change_date">Change Date</option>
            </select>
            <% } %>
    </div>
    <div id="occurence_preview_panel" style="position:absolute; width:200px; display:none; z-index:2; background:white" class="attach_preview_panel"></div>
    <!-- Solulab code start 26-07-2019 --> 
    <br><div id="calendar-container-a">
        <div id="calendar">
                  
        </div>
    </div>
    <!-- Solulab code end 26-07-2019 -->
    <script>
    function timeConvertor(time) {
    var PM = time.match('PM') ? true : false
    
    time = time.split(':')
    var min = time[1]
    
    if (PM) {
        var hour = 12 + parseInt(time[0],10)
        var sec = time[2].replace('PM', '')
    } else {
        var hour = time[0]
        var sec = time[2].replace('AM', '')       
    }
    }
    </script>
    <table id="occurence_listing" class="tablesorter occurence_listing" border="0" cellpadding="0" cellspacing="0">
        <thead>
        <tr>
            <th style="font-size:1.2em; width:13%">
                Date/Time
            </th>
            <th style="font-size:1.2em; width:4%">
            	Atty
            </th>
            <th style="font-size:1.2em; width:4%">
      Supv&nbsp;Atty </th>
            <th style="font-size:1.2em; width:23%">
                Kase
            </th>
            <th style="font-size:1.2em; width:25%">
                Event
            </th>
            <th style="font-size:1.2em; width:10%">
                Venue
            </th>
            <th style="font-size:1.2em; width:10%">
            	Judge
            </th>
            <th style="font-size:1.2em; width:4%">&nbsp;</th>
        </tr>
       
        </thead>
        <tbody>

        <script>
            var set_id_in_a_tag = new Array();
            var calendar_week_data = new Array();
        </script>
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
                
                var url_date = moment(occurence.event_dateandtime).format("YYYY-MM-DD");
        %>
        <tr>
            <td colspan="8">
            	<div style="float:right; padding-right:5px">
                	<a  id="print_today_<%= customer_firm_calendar_id %>_<%=url_date %>_<%=url_date %>" class="print_today" title="Click to Print Events for <%= moment(occurence.event_dateandtime).format("dddd, MMMM Do YYYY") %>" style="color:black; font-size:1em; text-decoration:none; cursor:pointer">Print This Day</a>&nbsp;<span style='color:black; font-size:0.8em' title='There are <%=arrDayCount[the_day] %> events for this day'>(<%=arrDayCount[the_day] %>)</span>
                </div>
                
                <div style="width:100%; 
text-align:left;
padding-left:5px;  
font-size:1.8em; 
background:#CFF; 
color:red;">	<% if (title!="Phone Messages" && !blnCourtCalendar) { %>
				<span>
                	<input type="checkbox" id="check_assign_<%=occurence.id %>_<%= moment(occurence.event_dateandtime).format('MM-DD-YYYY') %>" class="check_all"  />
                </span>
                <% } %>
                <%= moment(occurence.event_dateandtime).format("dddd, MMMM Do YYYY") %></div>
            </td>
        </tr>
        <% 	} %>
       	<tr class="occurence_data_row occurence_row_<%= occurence.id %> occurence_case_type_<%=occurence.case_type %>">
                <td style="font-size:1.17em" nowrap="nowrap">
                    <!-- //moment(occurence.event_dateandtime).format("hh:mm a") -->
                    <% if (title!="Phone Messages" && !blnCourtCalendar) { %>
                     <input type="checkbox" id="check_assign_<%=occurence.id %>_<%= moment(occurence.event_dateandtime).format('MM-DD-YYYY') %>" class="check_thisone check_thisone_<%= moment(occurence.event_dateandtime).format('MM-DD-YYYY') %>"  />
                    <% } %>
                    <span style="<%= occurence.transfer_status_color %>">
                    <%= occurence.time %>
                    </span>
                    &nbsp;<% if (occurence.event_priority=="high") { %>
                        <span style="padding-left:2px; padding-right:2px" title="High Priority">
                        <img src="img/output_IW6x8Z.gif" width="15" height="20" /></span>
                        <% } %>
                        <% if (occurence.event_priority=="low") { %>
                            <i class="glyphicon glyphicon-arrow-down" style="color:#06C; background:white" title="Low Priority"></i>
                        <% } %>
                    <a title="Click to edit event" class="list_edit edit_event" id="<%= occurence.id %>_<%= occurence.case_id %>" <% if (customer_id != 1033 && document.location.pathname.indexOf('v9') > -1) { %> data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" <% } %> style="cursor:pointer"><i style="font-size:15px;color:#06f; cursor:pointer;" class="glyphicon glyphicon-edit" title="Click to Edit Event"></i></a>&nbsp;<a href="report.php#event/<%=occurence.id %>" target="_blank"><i style="font-size:15px;color:#CC99CC; cursor:pointer;" class="glyphicon glyphicon-print" title="Click to Print Event"></i></a>
                    <% 
                    if (blnCourtCalendar) {
                        %>  
						&nbsp;|&nbsp;<span id="pending_buttons_holder_<%=occurence.id %>_<%=occurence.courtcalendar_id %>">
                        <button class="btn btn-xs btn-primary assign_pending" style="visibility:visible" id="assign_pending_<%=occurence.id %>_<%=occurence.courtcalendar_id %>">Assign</button>
                        &nbsp;
                		<button class="btn btn-xs btn-danger dismiss_pending" style="visibility:visible" id="dismiss_pending_<%=occurence.id %>_<%=occurence.courtcalendar_id %>">Dismiss</button>        
                        </span>
                        <div id="assign_event_<%=occurence.id %>_<%=occurence.courtcalendar_id %>" style="visibility:hidden">
                        	<div style="display:inline-block">
                        		<input type="text" id="assigneeInput_<%=occurence.id %>_<%=occurence.courtcalendar_id %>" style="width:203px" class="modalInput event input_class" value="" />
                        	</div>
                            <div style="display:inline-block; vertical-align:top">
	                            <button class="btn btn-xs btn-success approve_pending" style="display:none" id="approve_pending_<%=occurence.id %>_<%=occurence.courtcalendar_id %>">Save</button>
                            </div>
                        </div>	
                    <% 	
                    } %>
              	</td>
               <td>
               	<%=occurence.supervising_attorney.toUpperCase() %>
               </td>
               <td>
               	<%= occurence.assignee.toUpperCase() %>
               </td>
               <td style="font-size:1.1em" nowrap="nowrap">
               		<% if (blnCourtCalendar) { %>
                    <div style="float:right">
                    	<a href="v8.php?n=#kalendarlist/<%=occurence.case_id %>" class="white_text" target="_blank">View Kase Events</a>
                    </div>
                    <% } %>
                    <div style="float:right">
                    	<% if (occurence.case_type!="nocase") { %>
                        <%=occurence.case_type %>
                        <% } %>
                    </div>
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
                    <% } %>
                    <% 
                    var case_link = "#kase/" + occurence.case_id;
                    var target = "";
                    if (!homepage) {
                    	case_link = "?n=" + case_link;
                        var target = "_blank";
                    } %>
                    <a href="<%=case_link %>" title="<%=occurence.case_number %> - <%=occurence.case_name %>" class="list-item_kase" style="color:white<%=occurence.kase_link_background %>" target="<%=target %>">
                    <% if (occurence.case_number!="") { %>
                    Case #:<%= occurence.case_number %><br />
                    <% } %>
                    <% if (occurence.file_number!="") { %>
                    File #:<%= occurence.file_number %><br />
                    <% } 
                    if (occurence.case_name!="" && occurence.case_name!=null) { 
                    %>
                    
                        <%=case_name %><%=occurence.kase_link_indicator %>
                    <% } %>
                    </a>
                </td>
               <td style="font-size:1.17em">
               		<% if (occurence.assignee!="" && !blnCourtCalendar) { %>
               		<div style="float:right; font-size:0.8em">
                    	Assigned to: <%= occurence.assignee.toUpperCase() %>
                    </div>
                    <% } %>
                    <% 
                    var maxlength = 255;
                    if (homepage==true) {
						var maxlength = 40;
                    }
                    var event_title = occurence.event_title;
                    if (occurence.event_type != "" && occurence.event_type != "case_type_pi" && occurence.event_type != "case_type_wc") {
                    	event_title = occurence.event_type;
                    }
                    if (event_title.length > maxlength) {
						event_title = event_title.substr(0, maxlength) + "...";
					}
                    if (title=="Phone Messages") {
                        if (event_title=="phone_call") {
                            event_title = "";
                        }
                    }
                    %>
                    <!-- Solulab Code start 29-07-2019 -->
                    <!-- <% console.log(occurence); %> -->
                   <script>
                    //    console.log('1');
                       
                       let event_start_date = "<%=occurence.event_dateandtime%>".split(" ");
                       event_start_date = event_start_date[0]+"T"+event_start_date[1];
                       let event_end_date = "<%=occurence.end_date%>".split(" ");
                       event_end_date = event_end_date[0]+"T"+event_end_date[1];
                        // console.log(event_start_date);
                        // console.log(event_end_date);
                    
                                              
                       // Date For Description
                    //    event_description_html_text="<html><body>";
                    //    event_description_html_text+="<%= moment(occurence.event_dateandtime).format('YYYY-MM-DD')%><br>";
                    //    event_description_html_text+="<?php //echo  date('H:i:s', strtotime($stripped));?>";
                    //    event_description_html_text+="";
                    //    event_description_html_text+="";
                    //    event_description_html_text+="";
                    //    event_description_html_text+="";
                    //    event_description_html_text+="";
                    //    event_description_html_text+="";
                    //    event_description_html_text+="";
                    //    event_description_html_text+="";
                    //    event_description_html_text+="</body></html>";
                    // console.log('2');
                       priority_value="<%=occurence.event_priority%>";
                    //    console.log(priority_value);
                       let priority_color="";
                       if(priority_value=="high"){
                        priority_color="#3a87ad";
                       }else if(priority_value=="normal"){
                        priority_color="#FF9900";
                       }else if(priority_value=="low"){
                        priority_color="#996666";
                       }
                       var eventid_for_api  = "<%=occurence.event_id%>";
                       var domain_name  = window.location.hostname; 
                    //    console.log(domain_name,eventid_for_api);
                        var api_url = "<?php echo $root_api_url; ?>/api/user/events/"+eventid_for_api+"/to";
                    //    console.log('orignal call times'+api_url);
                    //    var assignee_name_array = "<%=occurence.assignee %>";
                    //    assignee_name_array = assignee_name_array.split(';');
                    //    for (let i = 0; i < assignee_name_array.length; i++) {
                    //     assignee_name_array[i] = assignee_name_array[i].toLowerCase();
                    //     }
                       
                    //    console.log(assignee_name_array);
                    // console.log('3');
                        $.ajax({
                            url: api_url,
                            contentType: "application/json",
                            async:false,
                            dataType: 'json',
                            success: function(result){
                                // console.log(result);
                                var api_response =result;
                      
                var to_assignee_names="";          
                if(api_response.length!=0){ 
                    // console.log('!=0');
                    for (let i = 0; i < api_response.length; i++) {
                        // if(assignee_name_array.includes(api_response[i].nickname.toLowerCase())){
                            if((api_response.length-1) == i){
                                to_assignee_names = to_assignee_names+api_response[i].name;
                                // console.log('if part in for loop 392');
                            }else{
                                to_assignee_names = to_assignee_names+api_response[i].name+",";
                                // console.log('else part in for loop 396');
                            }
                        // }
                    }
                }
                // console.log('not call then');
                // console.log('4');
                       //https://www.ikase.website/api/user/events/7524/to
                    //    const userAction = async () => {
                    //    const response = await fetch('https://www.ikase.website/api/user/events/7524/to', {
                    //         method: 'GET',
                    //         headers: {
                    //         'Content-Type': 'application/json'
                    //         }
                    //     });
                    //     var myJson_event_user_api = await response.json(); //extract JSON from the http response
                    //    console.log('myjson console');
                    //     // do something with myJson
                    //     } 
                        
                        
                       // Data for show in Highlight (event box on hover)
                       var temp="",temp0="",temp1="",temp2="",temp3="",temp4="",temp5="";
                       temp="<%=occurence.time%> - <%=moment(occurence.time, 'hh:mm A').add('minutes', occurence.event_duration).format('hh:mm a')%>";
                       if("<%=occurence.event_type %>"!=""){
                       temp0=`Type : <%=occurence.event_type %><br>`; }
                       temp1="Subject : <%=occurence.event_title%>";
                       temp2=`<%=occurence.event_description %>`;
                       temp2=temp2.toString();
                       if("<%=occurence.event_from %>"!="" && "<%=occurence.event_from %>"!=null){
                            if(to_assignee_names!="" && to_assignee_names!=null){
                                temp4=`<%=occurence.event_from %>`; 
                            }else{
                                temp4=`From: <%=occurence.event_from %>`; 
                            }
                       }
                       if("<%=occurence.event_from %>"!="" && "<%=occurence.event_from %>"!=null && to_assignee_names!="" && to_assignee_names!=null){
                            temp4+=" &#x2192; ";
                       }
                       if(to_assignee_names!="" && to_assignee_names!=null){
                            if("<%=occurence.event_from %>"!="" && "<%=occurence.event_from %>"!=null){
                                temp5=to_assignee_names;
                            }else{
                                temp5=`Assignee: `+to_assignee_names;
                            }
                       }
                       if("<%=occurence.case_name %>"!=""){
                       temp3=`<%=occurence.case_name%><br>`; }else { temp3= ""; }
                       <% if(occurence.case_name == occurence.event_title){ %>
                            highlight_html_data =temp+"<br>"+temp0+""+temp1+"<hr>"+temp2;
                            if(temp4!="" && temp4!=null){ highlight_html_data +="<hr>"+temp4; }
                            if(temp5!="" && temp5!=null){ highlight_html_data +=temp5; }
                       <% }else{ %>
                            highlight_html_data =temp+"<br>"+temp3+""+temp0+""+temp1+"<hr>"+temp2;
                            if(temp4!="" && temp4!=null){ highlight_html_data +="<hr>"+temp4; }
                            if(temp5!="" && temp5!=null){ highlight_html_data +=temp5; }
                       <% } %>
                    //    console.log('not call then nd time classNames" :edit_event,event_id_calendar<%=occurence.event_id%>"title" "<%= event_title %>", "color"'+priority_color+' "start":'+event_start_date+', "end":'+event_end_date+', "description":'+highlight_html_data);
                       //for set id after load fullcalendare
                       set_id_in_a_tag[set_id_in_a_tag.length] = ['event_id_calendar<%=occurence.event_id%>',"<%= occurence.id %>_<%= occurence.case_id %>"];
                       calendar_week_data.push({ "classNames" : ['edit_event','event_id_calendar<%=occurence.event_id%>'], "title": "<%= event_title %>", "color":priority_color, "start":event_start_date, "end":event_end_date, "description":highlight_html_data});
                    //    console.log('after calen... data');
                       
                       }
                    })
                    </script>
                    <!-- Solulab Code end 29-07-2019 -->
                    <span title="<%=occurence.event_title %>"><%= event_title %></span>
                    <span style="float:left; display:<%=read_display %>" id="read_holder_<%= occurence.id %>"><a id="open_event_<%= occurence.id %>" class="read_holders" style="cursor:pointer"><img src="img/oie_10234757zr1fW7ZB_final.gif" width="15px" height="15px" /></a>
               </span>
               
                   
               </td>
               <td style="font-size:1.17em">
                    <%=occurence.venue_abbr %>
               </td>
                <td>
                <%=occurence.judge %>	
                </td>
               <td nowrap="nowrap">
					<?php //per steve at dordulian 3/31/32017
						if ($blnDeletePermission) { ?>
					<a title="Click to delete Event" class="list_edit delete_occurence" id="deleteevent_<%= occurence.id %>" onClick="javascript:composeDelete(<%= occurence.id %>, 'event');" data-toggle="modal" data-target="#deleteModal" style="cursor:pointer">
					<i style="font-size:15px; color:#FF3737; cursor:pointer" id="delete_occurence" class="glyphicon glyphicon-trash delete_occurence"></i></a>
                    	<?php
						}
						?>
				</td>
        </tr>
        <!--
        <tr class="occurence_data_row occurence_row_<%= occurence.id %> occurence_row_<%= occurence.id %>_details" style="display:">
        	<td colspan="8" style="font-size:1.17em">Assigned to: <%= arrToUserNames.join("; ") %> 
            <% if (occurence.event_from != "") { %> by <%= occurence.event_from %><% } %></td>
        </tr>
        -->
        <% if (event_title!=occurence.event_description) { %>
        <tr class="occurence_data_row occurence_row_<%= occurence.id %> occurence_row_<%= occurence.id %>_details occurence_case_type_<%=occurence.case_type %>" style="display:">
            <td style="font-size:1.17em" colspan="4">
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
            <td style="font-size:1.17em" colspan="4">
            	<% if (occurence.location!="") { %>
                <span style="text-decoration:underline">Location:&nbsp;</span><%=occurence.location %>
                <% } %>&nbsp;
            </td>
        </tr>
        <% } %>
        <% }); %>
        </tbody>
    </table>
</div>



<!-- Solulab code start 26-07-2019 --> 
<!-- <script src="../css/calendar/js/jquery-2.2.4.min.js"></script> -->
<script src="../lib/jquery.datetimepicker.js"></script>
<script src="../lib/jquery.tablesorter.js"></script>

<script src="../css/calendar/js/popper.min.js"></script>
<script src="../css/calendar/js/jquery-ui.min.js"></script>
<script src="../css/calendar/js/jquery.layout_and_plugins.min.js"></script>
      <!-- calender -->
<script src='../css/calendar/js/core/main.js'></script>
<script src='../css/calendar/js/interaction/main.js'></script>
<script src='../css/calendar/js/daygrid/main.js'></script>
<script src='../css/calendar/js/timegrid/main.js'></script>
<script src='../css/calendar/js/list/main.js'></script>

<!-- <script src="https://unpkg.com/popper.js/dist/umd/popper.min.js"></script>
<script src="https://unpkg.com/tooltip.js/dist/umd/tooltip.min.js"></script> -->
<script type='text/javascript' src="../lib/fullcalendar-2.7.1/popper.min.js"></script>
<script type='text/javascript' src="../lib/fullcalendar-2.7.1/tooltip.min.js"></script>

<script>
    // console.log(calendar_week_data);
    var tooltip_responses = [];
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
      plugins: [ 'interaction', 'dayGrid', 'timeGrid' ,'list'],
      height: 'parent',
//       eventMouseover: function(calEvent, jsEvent) {
//     var tooltip = '<div class="tooltipevent" style="width:100px;height:100px;background:#ccc;position:absolute;z-index:10001;">' + calEvent.title + '</div>';
//     var $tooltip = $(tooltip).appendTo('body');
//     $(this).mouseover(function(e) {
//         $(this).css('z-index', 10000);
//         $tooltip.fadeIn('500');
//         $tooltip.fadeTo('10', 1.9);
//     }).mousemove(function(e) {
//         $tooltip.css('top', e.pageY + 10);
//         $tooltip.css('left', e.pageX + 20);
//     });
// },
// eventMouseout: function(calEvent, jsEvent) {
//     $(this).css('z-index', 8);
//     $('.tooltipevent').remove();
// },
      eventRender: function(info) {
        // console.log(info);
          
        var tooltip = new Tooltip1(info.el, {
            title: info.event.extendedProps.description,
            placement: 'top',
            trigger: 'hover',
            container: 'body',
            html : 'true',
            
        });
        // console.log(tooltip);
        // console.log(tooltip.popperInstance.popper.style.backgroundColor);
        // tooltip_responses.push(tooltip);
      },
      eventMouseover: function(event, jsEvent, view) {
        //   console.log('hover');          
      },
      header: {
        // left: 'prev,next today',
        // center: 'title',
        right: 'timeGridWeek,timeGridDay,listMonth'
           // right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
      },
    //   eventRender: function ( event, element ) {
    //     element.attr( 'id', event.id );
    //   },
      defaultView: 'timeGridWeek',
      defaultDate: "<?=date("Y-m-d")?>",
      navLinks: true, // can click day/week names to navigate views
      editable: false,
      contentHeight:'auto', 
      selectable: false,
      selectMirror: false,     
      eventLimit: true, // allow "more" link when too many events
      events: calendar_week_data,      
    });
    calendar.render();
    
</script>
<script>
// let len_temp=set_id_in_a_tag.length);
var x;
function setid() {
    set_id_in_a_tag.forEach(element => {
        // console.log(element);
        // console.log(element[0]);
        // console.log(element[1]);
         
        // x = document.getElementsByClassName("event_id_calendar9672")[0];
        // console.log(x);
        if(document.getElementsByClassName(element[0])[0]){
            x = document.getElementsByClassName(element[0])[0];
            // console.log(x);
            // console.log(x.aria-describedby);
            x.id=element[1];   
        }
    });
}
</script>
<script>
    function for_tooltip_color(){
    var temp5="";
    set_id_in_a_tag.forEach(element => {
        temp5="."+element[0];
        $(temp5).mouseover(function(){
            // $("event_id_calendar68").css("background-color", "yellow");
            // console.log(element[1]);
            var id = document.getElementsByClassName(element[0])[0].getAttribute("aria-describedby");   
            // console.log(id);
            
            
            var doSomethingOnceValueIsPresent = function () {
                var id = document.getElementsByClassName(element[0])[0].getAttribute("aria-describedby");   
                if (id != null)
                {
                    // alert('something!');
                    let tooltip_tag = document.getElementById(id);
                    if(tooltip_tag){
                        tooltip_tag.addEventListener("load", loaded(element[0],tooltip_tag));
                    }
                }
                else
                {
                    setTimeout(function(){
                        doSomethingOnceValueIsPresent()
                        }, 30);
                }
            };
            doSomethingOnceValueIsPresent();
            
            });
        });
    function loaded(element1,tooltip_tag){
        
        let getcolor= $( "."+element1 ).css( "background-color" );
            // console.log(getcolor);
            tooltip_tag.style.backgroundColor=getcolor;
    }
//   $("p").mouseout(function(){
//     $("p").css("background-color", "lightgray");
//   });
}
for_tooltip_color();
$(document).on('click','th.fc-day-header',function() {
    setid();
    for_tooltip_color();
});
</script>
<script type="text/javascript">
      $('.fc-view-container').removeAttr('style');
      $('#occurence_listing').removeAttr('style').addClass('hide');
      setid();
      
      $(document).on('click','button.fc-listMonth-button',function() {
          $('.fc-view-container').attr('style','visibility:hidden;opacity:0;position:absolute');
          $('#occurence_listing').removeClass('hide');
      });       
        $(document).on('click','button.fc-timeGridWeek-button',function() {
          $('.fc-view-container').removeAttr('style');
          $('#occurence_listing').removeAttr('style').addClass('hide');
          setid();
          for_tooltip_color();
      });
      $(document).on('click','button.fc-timeGridDay-button',function() {
        $('.fc-view-container').removeAttr('style');
        $('#occurence_listing').removeAttr('style').addClass('hide');
        setid();
        for_tooltip_color();
      });
      $( "button.fc-listMonth-button" ).trigger( "click" );
      //
    //   console.log(tooltip_responses);
    //   console.log(tooltip_responses[0]);
      
</script>
<!-- 
<script>
    console.log();
    
change_color_tooltip();
// let len_temp=set_id_in_a_tag.length);
var y;
function change_color_tooltip() {
    // console.log(tooltip);
    // alert();
    // set_id_in_a_tag.forEach(element => {
    //     // console.log(element);
    //     // console.log(element[0]);
    //     // console.log(element[1]);
    if($('tooltip1').is(':visible')){ //if the container is visible on the page
    
        y = document.getElementsByClassName("tooltip1");
        console.log(y);  //Adds a grid to the html
  } else {
    setTimeout(change_color_tooltip, 50); //wait 50 ms, then try again
  }  
        
    //     // console.log(y);
    //     if(document.getElementsByClassName(element[0])[0]){
    //         y = document.getElementsByClassName(element[0])[0];
    //         console.log(y);
    //         // console.log(y.aria-describedby);
    //         // y.id=element[1];   
    //     }
    // });
}
</script> -->
<!-- Solulab code end 26-07-2019 -->

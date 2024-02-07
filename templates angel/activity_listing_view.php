<?php
include("../api/manage_session.php");
session_write_close();

include("../api/connection.php");
$db = getConnection();

//see if there is a "data_source"_docs database
//lookup the customer name
$sql_customer = "SELECT data_source, data_path
FROM  `ikase`.`cse_customer` 
WHERE customer_id = :customer_id";

$stmt = $db->prepare($sql_customer);
$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
$stmt->execute();
$customer = $stmt->fetchObject();
//die(print_r($customer));
$data_source = $customer->data_source;

$blnA1 = ($customer->data_path == "A1");
$blnPerfect = ($customer->data_path == "perfect");
$blnArchives = false;
if ($data_source!="") {
	$sql = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . $data_source ."_docs'";
	//echo $sql;
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$schema = $stmt->fetchObject();
	if (is_object($schema)) {
		$blnArchives = ($schema->SCHEMA_NAME!="");
	}
}
$db = null;
//special cases
$blnGlauber = ($_SESSION["user_customer_id"]=='1049');
?>
<div id="kase_abstract_holder" style="margin-bottom:10px; display:none"></div>
<div class="activity">
	<div id="threadimage_preview_panel" style="position:absolute; width:20px; display:none; z-index:2; background:white;" class="attach_preview_panel"></div>
	<div class="glass_header">
        <div style="float:right;" class="white_text">
        <input id="case_id" name="case_id" type="hidden" value="<%=case_id %>" />
        <input id="user_id" name="user_id" type="hidden" value="<%=user_id %>" />
        	<?php 
			if ($_SESSION['user_role']=="admin" || $_SESSION['user_role']=="masteradmin") {	
					if($_SESSION['user_job'] == "Administrator") { ?>
						<span id="hide_file_holder"><a id="hide_file_access" style="cursor:pointer" class="white_text">Hide File Access</a>&nbsp;|&nbsp;</span>
					<?php }
			}
			?>
            <?php if ($blnArchives) { ?>
            <a href="#archives/<%=current_case_id %>" style="cursor:pointer" class="white_text">Archives</a>&nbsp;<div id="archive_count" style="left: 60px; padding-left:3px; margin-top:-25px; color:white; font-size:0.8em; display:inline-block"></div>&nbsp;|&nbsp;
            <?php } ?>
            <?php if ($blnA1 || $blnPerfect) { ?>
            <a href="#archives_legacy/<%=current_case_id %>" style="cursor:pointer" class="white_text">Archives Available</a>&nbsp;<span id="archive_count" style=" color:white; font-size:0.8em"></span>&nbsp;|&nbsp;
            <?php } ?>
            <!--report is set in app.js userActivity -->
            <% if (!report) { %>
                <% if (invoice_id=="") { %>
                <a id="print_activity" href="report.php#activity/<%=case_id %>" target="_blank" title='Click to print activity' style='cursor:pointer; display:' class="white_text">Print Activity</a>
                <% } %>
            <% } else { %>                
                <a href="report.php#activities" target="_blank" title='Click to print activity' style='cursor:pointer; display:' class="white_text">Print Activity</a>                
            <% } %>
            <% if (invoice_id!="") { %>
            &nbsp;&nbsp;
        	<a id="invoices_print" href="report.php#invoices/<%=case_id %>/<%=invoice_id %>" class="invoices_print_<%=case_id %>" title="Click to Print Invoice" style="color:#FFFFFF; display:" target="_blank">Print Invoice</a>
            <% } %>
			<div class="btn-group">            	
            	<label for="activities_searchList" id="label_search_activity" style="width:120px; font-size:1em; cursor:text; position:relative; top:0px; left:125px; color:#999; margin-left:0px; margin-top:0px; border:#00FF00 0px solid;">Search Activity</label>
            	
				<input id="activities_searchList" type="text" class="search-field" placeholder="" autocomplete="off" onkeyup="findIt(this, 'activity_listing', 'activity')" style="width:190px; height:30px">
				<a id="activities_clear_search" style="position: absolute;
				right: 2px;
				top: 0px;
				bottom: 2px;
				height: 14px;
				margin: auto;
				cursor: pointer;
				border: 0px solid green;
				"><i class="glyphicon glyphicon-remove-circle" style='font-size:1em; color:#cccccc'></i></a>
			</div>
        </div>
        
        <span style="font-size:1.2em; color:#FFFFFF">
        <% if (invoice_id=="") { %>
        Activity
        <% } else { %>
        Invoice <%= invoice_number %>
        <% } %>
        <% if (report) { %>: <span id='activities_length'><%=activity_count %></span> entries for <%=user_name %> (<%=nickname %>)</span>
        
        <div style="color:white; margin-top:10px;">
        	From <input id="start_dateInput" class="range_dates" value="<%= moment(start_date).format("MM/DD/YYYY") %>" style="width:80px" placeholder="From Date" /> through <input id="end_dateInput" class="range_dates" value="<%= moment(end_date).format("MM/DD/YYYY") %>" style="width:80px" placeholder="Through Date" />
        </div>
        <div style="color:white; margin-top:10px;">
        	<%= totals %>
            <div id="show_all_holder" style="display:inline-block; visibility:hidden"><a id="show_all" style="cursor:pointer; " class="white_text">Show All</a></div>
        </div>
        <% } else { %>
        	<% if (invoice_id=="") { %>
            &nbsp;<div style="position:relative; left:60px; padding-left:3px; margin-top:-25px; color:white; font-size:0.8em; width:20px">(<%=activities.length %>)</div>
            <% } %>
        <% } %>
        <!--
        <div style="position:relative; left:110px; margin-top:-15px; width:300px">
        	<% if (!report) { %>
            <a title="Click to compose a new activity" class="compose_new_activity" id="compose_activity"  data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer"><i class="glyphicon glyphicon-plus-sign" style="color:#99FFFF">&nbsp;</i></a>
            <a title="Click to create event" class="compose_event" id="-1_<%=case_id %>" style="cursor:pointer"><i style="color:#FF9999" class="glyphicon glyphicon-calendar"></i></a>
            <a title="Click to compose a new task" class="compose_task"  data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer"><i class="glyphicon glyphicon-inbox" style="color:#66FF33"></i></a>
            <a title="Click to compose a new note" class="compose_new_note" id="compose_note"  data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer"><i class="glyphicon glyphicon-plus" style="color:#99FFFF">&nbsp;</i></a>
            &nbsp;
            <% } %>
            <?php if ($blnGlauber) { ?>
            <a class="white_text restore_archives" style="cursor:pointer; background:red; color:white; display:none" title="This case was restored from Archives.  The activities have not yet been processed by our bot.&#13;Please click this link to initiate the Archive Restore Process.  You will only have to do this once.&#13;&#13;This process may take a few minutes, because some older cases have up to 5000 entries.">Restore from Archives</a>&nbsp;<span id="restore_archives_count" style="display:none"></span>
            <?php } ?>
            
            <select name="mass_change" id="mass_change" style="width:150px; display:none">
              <option value="" selected="selected">Choose Action</option>
              <option value="print">Print</option>
              <option value="bill">Invoice</option>
            </select>
            
            &nbsp;&nbsp;
            <i class="glyphicon glyphicon-ok" style="color:#32CD32; cursor:pointer; display:none" id="saved_invoice">&nbsp;</i>
            <% if (!report) { %>
            &nbsp;&nbsp;
            <a id="invoices" href="#activities/invoices/<%=case_id %>" class="invoices_<%=case_id %>" title="Click to view Invoices" style="color:#FFFFFF">List Kase Invoices</a>
            <% } %>
        </div>
        -->
    </div>
    <% if (typeof filters != "undefined") {
        if (filters!="") { %>
    <div style="display:block; border: 1px solid white;padding-left: 5px; padding-right:5px; padding-top:2px; padding-bottom:2px; color: white">
        <%=filters %>
    </div>
    <% }
    } %>
    <table id="activity_listing" class="tablesorter activity_listing" border="0" cellpadding="0" cellspacing="1">
        <thead>
        <tr>
        	<th>
            	<input type="checkbox" id="check_print" class="check_all" style="display:"  />
            </th>
            <th>
            	Date
            </th>
            <th>
            	Hours
            </th>
            <th>
            	By
            </th>
            <th>
            	Category
            </th>
            <th>
            	Activity
            </th>
        </tr>
        </thead>
        <tbody>
        <% 
        var arrIDs = [];
        var current_user_name = "";
        var display_row = "";
        if (report) {
	        display_row = "display:none";
        }
        _.each( activities, function(activity) {
        	if (arrIDs.indexOf(activity.activity_id) < 0) {
            	arrIDs.push(activity.activity_id);
                //show case user
                var user_name = activity.user_name; 
                if (report) {
                    if (current_user_name != user_name) {
                        current_user_name = user_name;
                    %>
                        <tr class="user_row">
                            <td colspan="7">
                                <div style="width:100%; 
                text-align:left; 
                font-size:1.8em; 
                background:#CFF; 
                color:red;"><%= user_name %>&nbsp;&nbsp;(<%=activity.user_total_count %>)&nbsp;<span class="expand_activity" id="expand_<%=activity.activity_user_id %>" style="cursor:pointer" title="Click to show activities">+</span><span class="shrink_activity" id="shrink_<%=activity.activity_user_id %>" style="cursor:pointer; display:none" title="Click to hide activities">-</span></div>
                            </td>
                        </tr>
                    <% }
                }
        %>
        <tr class="activity_data_row activity_data_row_<%=activity.activity_uuid %> expand_user_<%=activity.activity_user_id %>" style="<%=display_row %>">
            <td valign="top" width="1%" nowrap="nowrap"><input type="checkbox" id="check_printone_<%=activity.activity_uuid %>" class="check_thisone" style="display:"  <%=activity.check_box %> /></td>
          <td valign="top" width="1%" nowrap="nowrap"><%= activity.activity_date %></td>
            <!--<td><%=activity.send_link %></td>-->
            <td valign="top" width="1%" nowrap="nowrap">
                <div id="activity_hours_input_activator" style="cursor:pointer" class="activity_hours_uuid_<%=activity.activity_uuid %>"><%= activity.hours %></div>
                <div id="activity_hours_input_holder_<%=activity.activity_uuid %>" style="display:none;"><input type="text" id="activity_hours_edit_<%=activity.activity_uuid %>" class="activity_hours_input" style="width:50px" />&nbsp;&nbsp;<i class="glyphicon glyphicon-ok" style="color:#32CD32; cursor:pointer" id="save_edit_hours">&nbsp;</i><i class="glyphicon glyphicon-repeat" style="color:white; cursor:pointer" id="cancel_edit_hours">&nbsp;</i></div>
            </td>
          <td valign="top" width="1%" nowrap="nowrap">
          	<span class="activity_by search_activity_item" style="cursor:pointer" title="Click to Filter by this Employee"><%= activity.by %></span>
          </td>
          <td valign="top" width="1%" nowrap="nowrap">
          	<span class="activity_category" style="cursor:pointer" title="Click to Filter by this category"><%= activity.activity_category %></span>
          </td>
            <% if (customer_id!=1055 && customer_id!=1049) { %>
            <td valign="top">
            	<% if (customer_id == 1033) { %>
                	<%= activity.activity %>
                    <div id="activity_input_activator"  data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer; display:none" class="activity_uuid_<%=activity.activity_uuid %>">edit</div>
                <% } else { %>
                	<table>
                    	<tr id="activity_partial_holder_<%= activity.id %>">
                        	<td align="left" style="background-color:transparent">
                    			<%= activity.activity %>
                            </td>
                        </tr>
                    	<tr id="activity_full_holder_<%= activity.id %>" style="display:none">
                        	<td align="left" style="background-color:transparent">
                    			<%= activity.full_activity %>
                            </td>
                        </tr>
                    </table>
                <% } %>
                
                <div id="activity_input_holder_<%=activity.activity_uuid %>" style="display:none;"><textarea type="text" id="activity_edit_<%=activity.activity_uuid %>" class="activity_input" style="width:400px" rows="4"></textarea>&nbsp;&nbsp;<i class="glyphicon glyphicon-ok" style="color:#32CD32; cursor:pointer">&nbsp;</i><i class="glyphicon glyphicon-repeat" style="color:white; cursor:pointer">&nbsp;</i></div>
            	<% if(activity.name!="" && activity.name!=undefined) { %>
                <a href="?n=#kases/<%= activity.case_id %>" title="Click to review Kase" class="white_text" style="background:orange;color:black;padding:2px" target="_blank"><%= activity.case_number %> - <%= activity.name %></a><br />
                <% } %>
            </td>
            <% } else {
            	activity.activity = activity.activity.replaceAll("---------------------------------------------", "-------------------------------------------------------");
                activity.activity = activity.activity.replaceAll(".,", "; ");
             %>
            <td style="font-size:1.2em">
            	<% if(activity.name!="") { %>
                <a href="#kases/<%= activity.case_id %>" title="Click to review Kase" class="white_text" style="background:orange" target="_blank"><%= activity.case_number %> - <%= activity.name %></a><br />
                <% } %>
                <%= activity.activity.replaceAll("-------------------------------------------------------", "<br>") %>
            </td>
            <% } %>
        </tr>
        <% }
        }); %>
        </tbody>
    </table>
</div>
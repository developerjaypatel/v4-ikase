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


<div style="float:right; width:350px; height:790px; padding-top:5px; padding-left:10px; display:none; background: url(img/glass_dark.png" id="preview_pane_holder">
    <div>
        <div style="display:inline-block; width:97%" id="preview_block_holder">
            <div id="preview_title" style="
                margin-bottom: 30px;
                color: white;
                font-size: 1.6em;
            ">
            </div>
            <div class="white_text" id="preview_pane"></div>
        </div>
    </div>
</div>
<div id="confirm_delete" style="display:none; position:absolute; top:20px; color:white; background:black; border:1px solid white; font-size:1.2em; padding:10px; text-align:center; z-index:1051">
	<div style="width:350px; margin-left:auto; margin-right:auto; ">
    <input type="hidden" name="confirm_delete_id" id="confirm_delete_id" value="" />
    Are you sure you want to delete this activity?
    <div style="padding:5px; text-align:center"><a id="delete_account" class="delete_yes white_icon" style="cursor:pointer">YES</a></div>
    <div style="padding:5px; text-align:center"><a id="cancel_account" class="delete_no white_icon" style="cursor:pointer">NO</a></div>
    </div>
</div>
<div class="activity" id="activity_list_outer_div" style="overflow-y:scroll">
	<div id="threadimage_preview_panel" style="position:absolute; width:20px; display:none; z-index:2; background:white;" class="attach_preview_panel"></div>
	<div class="glass_header">
        <div style="float:right;" class="white_text">
        <input id="case_id" name="case_id" type="hidden" value="<%=case_id %>" />
        <input id="user_id" name="user_id" type="hidden" value="<%=user_id %>" />
        <input id="account_id" name="account_id" type="hidden" value="" />
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
            
            <% if (!report) { %>
                <% if (invoice_id=="") { %>
                <button id="print_activity" title='Click to print activity' class="btn btn-primary btn-sm">Print Kase Activities</button>
                <% } else { %>
                <button id="print_invoice" title='Click to print invoice' class="btn btn-primary btn-sm">Print Invoice <%=invoice_number %></button>
                <% } %>
            <% } else { %>                
                <a id="print_activities" href="report.php#activities" target="_blank" title='Click to print activity' style='cursor:pointer; display:' class="white_text">Print Activity</a>                
            <% } %>
            <div id="show_all_holder" style="display:inline-block; visibility:hidden">&nbsp;|&nbsp;<a id="show_all" style="cursor:pointer; " class="white_text">Show All</a></div>
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
        <%=list_title %>
        <% if (report) { %>: <span id='activities_length'><%=activity_count %></span> entries for <%=user_name %> (<%=nickname %>)</span>
        
        <span style="color:white">&nbsp;&nbsp;From
            <input id="start_dateInput" class="range_dates" value="<%= moment(start_date).format("MM/DD/YYYY") %>" style="width:80px" placeholder="From Date" /> through <input id="end_dateInput" class="range_dates" value="<%= moment(end_date).format("MM/DD/YYYY") %>" style="width:80px" placeholder="Through Date" /></span><%= totals %>
        <% } else { %>
        	<% if (invoice_id=="") { %>
            &nbsp;<div style="position:relative; left:115px; padding-left:3px; margin-top:-25px; color:white; font-size:0.8em; width:20px">(<%=activities.length %>)</div>
            <% } %>
        <% } %>
        <div id="buttons_filters_holder" style="position:relative; left:165px; margin-top:-15px; width:1022px">
        	<% if (!report) { %>
            <button class="compose_button compose_new_activity btn btn-sm btn-primary" title="Click to compose a new activity" id="compose_activity" style="margin-top:-5px;">New Activity</button>
            &nbsp;
            <button id="-1_<%=case_id %>" class="compose_button compose_event btn btn-sm btn-primary" title="Click to create a new kalendar event" style="margin-top:-5px">New Event</button>
            &nbsp;
            <button  id="compose_task_<%= case_id %>" class="compose_button compose_task btn btn-sm btn-primary" title="Click to create a new task" style="margin-top:-5px">New Task</button> 
            &nbsp;
            <button id="compose_note_<%= case_id %>" class="compose_button compose_new_note btn btn-sm btn-primary" title="Click to create a new note" style="margin-top:-5px">New Note</button> 
            &nbsp;
            <button id="invoice_activities" title='Click to invoice activities' class="btn btn-sm btn-primary" style="margin-top:-5px; <% if (!blnBillable) { %>display:none<% } %>">Invoice Activities <%=total_billed %></button>
             &nbsp;
            <button id="bill_activities" title='Click to automatically add billing hours for Letters and Forms' class="btn btn-sm btn-primary" style="margin-top:-5px;">Auto Bill </button>
            &nbsp;
            <span id="activity_dates_holder" style="color:white; display:none; font-size:0.8em; margin-top:-5px">
                &nbsp;&nbsp;
                <% if (kinvoice_total!="" && !isNaN(kinvoice_total)) { %>
                <span style="font-weight:bold">Due: </span>$<%=numberWithCommas(Number(kinvoice_total).toFixed(2)) %>
                <span style="font-weight:bold">&nbsp;&nbsp;|</span>
                <% } %>
                <span style="font-weight:bold">
                    &nbsp;
                    From:
                    <input id="start_dateInput" class="range_dates" value="<%= moment(start_date).format("MM/DD/YYYY") %>" style="width:80px" placeholder="From Date" autocomplete="off" />&nbsp;Through: <input id="end_dateInput" class="range_dates" value="<%= moment(end_date).format("MM/DD/YYYY") %>" style="width:80px" placeholder="Through Date" autocomplete="off" />
                    &nbsp;
                    &nbsp;|&nbsp;
                    <span style="color:white; margin-top:-5px">&nbsp;Carrier&nbsp;</span>
                </span>
                <select id="invoice_carrier"></select>
                &nbsp;&nbsp;
                <button id="invoice_create" title='Click to invoice activities' class="btn btn-sm btn-success" style="margin-top:-5px">Create Invoice</button>
                <button id="cancel_invoice" title='Click to cancel invoice' class="btn btn-sm btn-danger" style="margin-top:-5px; margin-left:10px">Cancel</button>
                <input type="hidden" id="kinvoice_id" name="kinvoice_id" value="<%=invoice_id %>" />
                <input type="hidden" id="kinvoice_number" name="kinvoice_number" value="<%=invoice_number %>" />
            </span>
            <% } %>
            <?php if ($blnGlauber) { ?>
            <a class="white_text restore_archives" style="cursor:pointer; background:red; color:white; display:none" title="This case was restored from Archives.  The activities have not yet been processed by our bot.&#13;Please click this link to initiate the Archive Restore Process.  You will only have to do this once.&#13;&#13;This process may take a few minutes, because some older cases have up to 5000 entries.">Restore from Archives</a>&nbsp;<span id="restore_archives_count" style="display:none"></span>
            <?php } ?>
            <select name="mass_change" id="mass_change" style="width:150px; display:none">
              <option value="" selected="selected">Choose Action</option>
              <option value="print">Print</option>
              <option value="bill">Invoice</option>
            </select>
            <div id="legend_holder" style="display:inline-block; border:1px solid white; padding:5px; font-size:0.8em">
            	<div class="filter_div" style="display:inline-block; margin-right:10px">Legend</div>
                <div id="filter_billable" class="filter_div" style="display:inline-block; padding:2px; background: #7ceeeebd; color:black; cursor:pointer" title="Click to filter by Billable activity items.  Billable activity items have billable hours.">
                	Billable
                </div>
                &nbsp;
                <div id="filter_invoiced" class="filter_div" style="display:inline-block; padding:2px; background: chocolate; color:white; cursor:pointer" title="Click to filter by Invoiced activity items.">
                	Invoiced
                </div>
                <a id="filter_show_all" style="display:none; cursor:pointer; text-decoration:underline" class="white_text">Show All</a>
            </div>
        </div>
        <div id="additional_invoice_questions" style="display:none; margin-left:225px; font-size:0.85em">
        	<div>
            	<input type="radio" name="kinvoice_type" class="kinvoice_type" id="kinvoice_type_invoice" value="I" checked="checked" /> <a id="invoice_link" class="white_text">Invoice</a>
                 | 
                <input type="radio" name="kinvoice_type" class="kinvoice_type" id="kinvoice_type_pre" value="P" /> <a id="pre_bill_link" class="white_text">Pre-Bill</a>
            </div>
            <div id="transfer_trust_funds" style="display:none" title="Do you want to transfer the invoice total from the Trust Account?
If Yes, the funds will be transferred per the invoice.
If No, you will be able to confirm transfer later">
            	<input type="checkbox" id="transfer_funds" value="Y" /> <a id="transfer_funds_link" class="white_text">Transfer funds from Trust Account</a>
            </div>
        </div>
    </div>
    <% if (typeof filters != "undefined") {
        if (filters!="") { %>
        <div style="background:url('img/glass_info.png')">
            <div class="partialactivity" style="display:inline-block;margin-right: 20px;border: 1px solid white;padding-left: 5px;padding-right:5px;padding-top:2px;padding-bottom:2px;font-size:1.2em;width: 100%;text-align: right;color: white;">
                <%=filters %>
            </div>
        </div>
    <% }
    } %>
    <div id="autobill_loading" style="display:none"><%=loading_image %></div>
    <table id="activity_listing" class="tablesorter activity_listing" border="0" cellpadding="0" cellspacing="1">
        <thead>
        <tr>
        	<th valign="bottom">
            	<input type="checkbox" id="check_print" class="check_all hidden" title="Check All"  />
            </th>
            <th valign="bottom">
            	Date
            </th>
            <th valign="bottom">
            	Hours
            </th>
            <th valign="bottom">
            	Cost
            </th>
            <th valign="bottom" class="inv_column" style="display:none">
            	Inv
            </th>
            <th valign="bottom">
            	By
            </th>
            <th valign="bottom">
            	Category
            </th>
            <th align="left" valign="bottom" style="position:relative">        
            	<% if (typeof user_divs != "undefined") { %>
                <div style="position:absolute; top:5px">
                	<span style="font-weight:bold">Total Hours:</span>&nbsp;<%=total_hours.toFixed(2) %> hrs
                    &nbsp;&nbsp;
                    <a id="show_hours_summary" style="font-size:0.9em; color:aqua; cursor:pointer">Hours Summary</a>
                    <a id="close_summary" style="font-size:0.9em; color:aqua; display:none;cursor:pointer">Hide Summary</a>
                    <div style="display:none; background:black" id="user_hours_holder">
                        <%=user_divs %>
                    </div>
                </div>
                <% } %>  
            </th>
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody>
        <% 
        var arrIDs = [];
        var current_user_name = "";
        
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
                color:red;"><%= user_name %>&nbsp;&nbsp;(<%=activity.user_total_count %>)</div>
                            </td>
                        </tr>
                    <% }
                }
        %>
        <tr class="activity_data_row activity_data_row_<%=activity.id %> activity_data_row_<%=activity.activity_uuid %> <% if (activity.billed) { %>billed_data_row<% } %> <% if (activity.billable) { %>billable_data_row<% } %> <% if (activity.billed || activity.billable) { %>invoice_data_row<% } %>">
            <td valign="top" width="1%" nowrap="nowrap">
            <input type="checkbox" id="check_printone_<%=activity.activity_uuid %>" class="check_thisone hidden <% if (activity.billed) { %>billed<% } %>" <%=activity.check_box %> />
            <input type="hidden" id="actual_date_<%=activity.activity_uuid %>" value="<%=activity.activity_actual_date %>" />
            
            <a id="open_activity_<%=current_case_id %>_<%= activity.activity_uuid %>_<%= activity.activity_id %>" class="open_activity" style="; cursor:pointer; padding:2px"><i style="font-size:15px;color:#06f; cursor:pointer;" class="glyphicon glyphicon-edit" title="Click to Edit Activity"></i></a>
            
            </td>
          <td valign="top" width="1%" nowrap="nowrap"><%= activity.activity_date %></td>
            <!--<td><%=activity.send_link %></td>-->
            <td valign="top" width="1%" nowrap="nowrap">
            	<% if (activity.billed) { %>
                <div id="activity_hours_uuid_<%=activity.activity_uuid %>" style="<%=activity.hours_color %>" class="activity_hours_input_billed" title="<%=activity.hours_title %>"><%= activity.hours %></div>
                <% } else { %>
                <div id="activity_hours_uuid_<%=activity.activity_uuid %>" style="cursor:pointer; <%=activity.hours_color %>" class="activity_hours_input_activator" title="<%=activity.hours_title %>"><%= activity.hours %></div>
                <% } %>
                <div id="activity_hours_input_holder_<%=activity.activity_uuid %>" style="display:none; padding:2px; position:relative">
                    <input type="number" step="0.001" min="0.000"  id="activity_hours_edit_<%=activity.activity_uuid %>" class="activity_hours_input" style="width:70px" />
                    <input type="hidden" id="activity_rate_<%=activity.activity_uuid %>" class="activity_rate_input" style="width:50px" value="<%=activity.rate %>" />
                    &nbsp;&nbsp;
                    <i class="glyphicon glyphicon-ok save_edit_hours" style="color:#32CD32; cursor:pointer" id="save_edit_hours_<%=activity.activity_uuid %>">&nbsp;</i>
                    <i class="glyphicon glyphicon-repeat cancel_edit_hours" style="color:white; cursor:pointer" id="cancel_edit_hour_<%=activity.activity_uuid %>">&nbsp;</i>
                    <div style="position:absolute; z-index:9999">
                    	<a id="set_hours_10_<%=activity.activity_uuid %>" class="set_hours white_text" style="cursor:pointer">10</a>&nbsp;|&nbsp;
                        <a id="set_hours_15_<%=activity.activity_uuid %>" class="set_hours white_text" style="cursor:pointer">15</a>&nbsp;|&nbsp;
                        <a id="set_hours_20_<%=activity.activity_uuid %>" class="set_hours white_text" style="cursor:pointer">20</a>&nbsp;|&nbsp;
                        <a id="set_hours_30_<%=activity.activity_uuid %>" class="set_hours white_text" style="cursor:pointer">30</a>&nbsp;|&nbsp;
                        <a id="set_hours_45_<%=activity.activity_uuid %>" class="set_hours white_text" style="cursor:pointer">45</a>
                        &nbsp;minutes
                    </div>
                </div>
            </td>
            <td valign="top" width="1%" nowrap="nowrap" id="activity_billing_amount_holder_<%=activity.activity_uuid %>">
            	<% if (activity.billing_amount!="" && activity.billing_amount!="0" && activity.billing_amount!="0.00") { %>
                <div title="<%=activity.billing_amount %> units @ $<%=activity.billing_rate %> per <%=activity.billing_unit %>" style="<%=activity.hours_color %>">
            	$<%= activity.billing_amount %>
                </div>
                <% } else { %>
                &nbsp;
                <% } %>
            </td>
          <td valign="top" class="inv_column" style="display:none" nowrap="nowrap">
          	<%= activity.kinvoice_number %>
          </td>
          <td valign="top" width="1%" nowrap="nowrap">
          	<span id="activity_by_uuid_<%=activity.activity_uuid %>" class="activity_by search_activity_item" style="cursor:pointer" title="Click to change Activity Employee.  Double Click to Filter by this Employee"><%= activity.by %></span>
            <div id="activity_by_uuid_<%=activity.activity_uuid %>" style="cursor:pointer; display: none" class="activity_by_input_activator" title="By"><%= activity.by %></div>
            
            <div id="activity_by_input_holder_<%=activity.activity_uuid %>" style="display:none;">
                <input type="text" id="activity_by_edit_<%=activity.activity_uuid %>" class="activity_by_input" style="width:50px" />
                <div id="activity_by_apply_<%=activity.activity_uuid %>" style="display:none">
                	<div>
	                    <input class="activity_apply" type="checkbox" value="Y" id="activity_apply_all_<%=activity.activity_uuid %>" />&nbsp;Apply to All Activities on this Case
                    </div>
                    <div>
	                    <input class="activity_apply_only" type="checkbox" value="Y" id="activity_apply_only_<%=activity.activity_uuid %>" />&nbsp;
                        Apply ONLY to Activities by <%= activity.by %>
                    </div>
                </div>
                <div>
                    <i class="glyphicon glyphicon-ok save_edit_by" style="color:#32CD32; cursor:pointer" id="save_edit_by_<%=activity.activity_uuid %>">&nbsp;</i>
                    <i class="glyphicon glyphicon-repeat cancel_edit_by" style="color:white; cursor:pointer" id="cancel_edit_by_<%=activity.activity_uuid %>s">&nbsp;</i>
                </div>
            </div>
          </td>
          <td valign="top" width="1%" nowrap="nowrap">
          	<% if(activity.activity_category!="") { %>
          	<span class="activity_category activity_category_span" id="activity_category_<%=activity.activity_uuid %>" style="cursor:pointer" title="Click to Filter by this category"><%= activity.activity_category %></span>
            <% } %>&nbsp;
          </td>
            <% if (customer_id!=1055 && customer_id!=1049) { %>
            <td valign="top">
            	<% if (customer_id == 1033) { %>
                	<%= activity.activity %>
                    <div id="activity_input_activator"  data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer; display:none" class="activity_uuid_<%=activity.activity_uuid %>">edit</div>
                <% } else { %>
                	<%= activity.activity %>
                <% } %>
                <%= activity.full_activity %>
            	<% if(activity.name!="" && activity.name!=undefined) { %>
                <a href="?n=#kase/<%= activity.case_id %>" title="Click to review Kase" class="white_text" style="background:orange;color:black;padding:2px" target="_blank"><%= activity.case_number %> - <%= activity.name %></a><br />
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
            <td align="right" valign="top">
                <a class="delete_icon" id="confirmdelete_activity_<%= activity.id %>" title="Click to delete activity" style="cursor:pointer"><i class="glyphicon glyphicon-trash" style="color:#FF0000;">&nbsp;</i></a>
            </td>
        </tr>
        <% }
        }); %>
        </tbody>
    </table>
</div>
</div>
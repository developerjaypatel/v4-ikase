<?php
require_once('../shared/legacy_session.php');
session_write_close();
?>
<% 
if (typeof additional_rows == "undefined") {
	additional_rows = false;
}
if (!additional_rows) { %>
<div id="confirm_delete" style="display:none; position:absolute; top:20px; color:white; background:black; border:0px solid white; font-size:1.2em; padding:10px; text-align:center; z-index:1051">
	<div style="width:350px; margin-left:auto; margin-right:auto; ">
    <input type="hidden" name="confirm_delete_id" id="confirm_delete_id" value="" />
    Are you sure you want to delete this kase?
    <div style="padding:5px; text-align:center"><a id="delete_kase" class="delete_yes white_icon" style="cursor:pointer">YES</a></div>
    <div style="padding:5px; text-align:center"><a id="cancel_kase" class="delete_no white_icon" style="cursor:pointer">NO</a></div>
    </div>
</div>
<div class="glass_header" id="list_kases_header">
	<div style="width:100%">
    	<table cellpadding="5px" style="color:white">
        	<tr>
            	<td align="left" valign="middle">
                	<span style="font-size:1.2em; color:#FFFFFF">List of <span id="kase_status_title">Active</span> Kases</span>
                </td>
                <td align="left" valign="top">
                	<span style="font-size:0.8em;" id="active_kase_count">(<%=kases_count %>)</span>
                </td>
                <td align="left" valign="middle">
                	<a title="Click for New Kase" id="new_kase" style="color:#FFFFFF; text-decoration:none; margin-left:10px">
                <button class="btn btn-transparent" style="color:white; border:0px solid; width:20px">
                    <i class="glyphicon glyphicon-plus-sign" style="color:#00FFFF">&nbsp;</i>
                </button>
            </a>
                </td>
                <td align="left" valign="middle">
                	<button id="closed_kases" type="button" class="btn btn-primary btn-sm">Closed Kases</button>
                <button id="open_kases" type="button" class="btn btn-success btn-sm" style="display:none">Open Kases</button>
                </td>
                <td align="left" valign="middle">
                	 Sort By 
                    <select name="kases_sort_by" id="kases_sort_by" >
                        <option value="first_name" selected="selected">First Name</option>
                        <option value="last_name">Last Name</option>
                    </select>
                </td>
                <% if (kase_attys != "" || kase_workers != "") { %>
                <td align="left" valign="middle">
                	Filter
                     <% if (kase_attys != "") { %>
                    <select id="kases_attorney_filter">
                        <%=kase_attys %>
                    </select>
                    <% } %>
              </td>
                <% } %>
                <% if (kase_workers != "") { %>
                <td align="left" valign="middle">
                <select id="kases_worker_filter">
                    <%=kase_workers %>
                </select>
                </td>
				<% } %>
                <%  %>
                <td align="left" valign="middle" id="reassign_holder" style="display:none">
                	<div style="float:right; display:none" id="select_all_assign_holder">
                        <input type="checkbox" id="select_all_assign" /> Select All
                    </div>
                	<button id="kases_assign_button" type="button" class="btn btn-sm btn-info" style="background-image:linear-gradient(to bottom,#c0e4ef 0,rgba(42, 86, 210, 0.64) 100%); visibility:hidden">
	                    Re-Assign Kases
                    </button>
                    <span id="assign_kase_instructions" style="color:white;background:maroon; display:none">Check Kases to Assign</span>
                    <button id="assign_kase" class="btn btn-primary btn-sm assign_kase" style="display:none">Assign to Coordinator</button>
                    <table class="workerInput_holder" style="display:none">
                    	<tr>
                            <td>
                                <label class="workerInput_holder">Coordinator:&nbsp;</label>
                            </td>
                            <td>
                                <input value="" id="workerInput_holder" style="width:170px;" class="kase input_class" />
                            </td>
                            <td>
                                <button id="save_assign" class="save_assign btn btn-success btn-sm" style="visibility:hidden">Save</button>
                            </td>
                        </tr>
                    </table>
                </td>
                <td align="left" valign="middle">
                	<button type="button" class="btn btn-sm" id="kase_report_button">
                    <a id="kase_report" class="kase_report print_kases_link" style="cursor:pointer; color:black">Print Kases</a>
                    </button>
                </td>
                <td align="left" valign="middle">
                	<button type="button" class="btn btn-sm btn-info" style="background-image:linear-gradient(to bottom,#c0e4ef 0,#2aabd2 100%);">
                    <a id="kase_export" class="kase_export print_kases_link" style="cursor:pointer; color:black">XL Export</a>
                    </button>
                    &nbsp;
                    <button type="button" class="btn btn-sm btn-info" style="background-image:linear-gradient(to bottom,#c0e4ef 0,rgba(42, 86, 210, 0.64) 100%);">
                    <a id="kase_export_alpha" class="kase_export print_kases_link" style="cursor:pointer; color:black">XL Alpha Export</a>
                    </button>
                </td>
            </tr>
        </table>
        <div id="search_parameters" class="white_text"></div>
	</div>
</div>
<br />
<div class="alphabet" style="width:100%;
	<% if (blnRecentList) { %>
    display:none;
    <% } %>"
    >
	<a class="letter_click first" id="A">A</a>
	<a class="letter_click" id="B">B</a>
	<a class="letter_click" id="C">C</a>
	<a class="letter_click" id="D">D</a>
	<a class="letter_click" id="E">E</a>
	<a class="letter_click" id="F">F</a>
	<a class="letter_click" id="G">G</a>
	<a class="letter_click" id="H">H</a>
	<a class="letter_click" id="I">I</a>
	<a class="letter_click" id="J">J</a>
	<a class="letter_click" id="K">K</a>
	<a class="letter_click" id="L">L</a>
	<a class="letter_click" id="M">M</a>
	<a class="letter_click" id="N">N</a>
	<a class="letter_click" id="O">O</a>
	<a class="letter_click" id="P">P</a>
	<a class="letter_click" id="Q">Q</a>
	<a class="letter_click" id="R">R</a>
	<a class="letter_click" id="S">S</a>
	<a class="letter_click" id="T">T</a>
	<a class="letter_click" id="U">U</a>
	<a class="letter_click" id="V">V</a>
	<a class="letter_click" id="W">W</a>
	<a class="letter_click" id="X">X</a>
	<a class="letter_click" id="Y">Y</a>
	<a class="letter_click" id="Z">Z</a>
	<a class="last" id="kase_show_all">All</a>
    <a href="report.php#kases/active" id="kase_print_listed_OBSOLETEID" class="last white_text print_kases_link" style="cursor:pointer"><i class='glyphicon glyphicon-print' style='color:#9EFF05; font-size:1em'></i></a>
</div>
<div id="kase_listing_table_holder">
    <div id="transfer_kases_holder" style="display:none">
        <input type="hidden" id="user_id" />
        <input type="checkbox" id="select_all_transfer" /> Select All
        <button id="transfer_button" class="btn btn-primary btn-sm" style="display:none;">Transfer Kases</button>
    </div>
    <table id="kase_listing" class="tablesorter kase_listing" border="0" cellpadding="0" cellspacing="1" width="100%" style="-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; background:none">
        <thead>
        <tr>
            <th width="630px">
                Kase
            </th>
            <th width="100">
                Type
            </th>
            <th>
                ADJ&nbsp;#
            </th>
            <th>
                Venue
            </th>
            <th>
                Status
            </th>
            <th>
                Date
            </th>
            <th>
                SSN
            </th>
            <th>
                DOB
            </th>
            <th>
                Language
            </th>
            <th width="300">
                Occupation
            </th>
			<th>
                Atty
            </th>
            <th>
                SAtty
            </th>
            <th>
                Worker
            </th>
            <th>&nbsp;
                
            </th>
        </tr>
        </thead>
        <tbody class="listing_item">
        <% } %>
        <% 
           var current_letter = "";
           var the_letter = "";
           var letter_string = ""; 
           var kaseCounter = 0;    
           var title = "";
           var last_case_name = "";
           var current_case_name = "";
           
           //title = document.getElementById("kase_status_title").innerHTML;
           //title.indexOf("Active")
           _.each( kase_collection, function(kase) {
			   //console.log(kase);
           		if (customer_id == "1109") {
                	//if ( i === 0) {
                       //last_case_name = kase.case_name;
                    //} 
                	current_case_name = kase.case_name;
                	if (current_case_name == last_case_name) {
                        //alert("hello there");
                        return true;
                    }
                        //alert("hello");
                        //if (title != null) {
                            //if (title == "Active") { 
                                //alert("hello there");
                                //if (kase.case_status == "Settled" || kase.case_status == "settled" || kase.case_status == "SETTLED" || kase.case_status == "Settled - Workers Comp (City Civilian)" || kase.case_status == "DISMISSED" || kase.case_status == "Settled - Workers Comp (Other Civilian)" || kase.case_status == "Dropped - Workers Comp (Other Civilian)") {
                                    //alert("hello there");
                                    //return true;
                                //}
                            //}
                            
                        //} else {
                        	
                        //}
                    }
                //we might have a new letter
                /*
                //console.log(kase.case_name);
                
                if (kase.last_name!="") {
                    the_letter = kase.last_name.trim().charAt(0);
                    letter_string = kase.last_name.charAt(0).valueOf();
                } else {
                    var arrName = kase.alpha_name.split(" ");
                    var last_name = arrName[arrName.length - 1];
                    the_letter = last_name.trim().charAt(0);
                    letter_string = last_name.charAt(0).valueOf();
                }
                */
                
                if (holder!="preview_pane") {
                    if (kase.clean_name=="No Applicant") {
                        the_letter = "*";
                    } else {
                    	//if (!kase.clean_name.indexOf("undefined")) {
                            var arrName = kase.clean_name.trim().split(" ");
                            if (arrName[0]=="vs") {
                                the_letter = "**";
                            } else {
                                the_letter = kase.clean_name.trim().charAt(0).toUpperCase();
                            }
                        //}
                    }
                } else {
                	the_letter = kase.alpha_name.trim().charAt(0).toUpperCase();
                }
                var new_header = "";
                if (kase.exact_match==1 && kaseCounter==0) { 
                    var search_term = document.getElementById("srch-term").value;
                    new_header = '<td colspan="14"><div style="width:100%; text-align:left; font-size:1.8em; background:#f8f866; color:black;">Exact Match [' + search_term + ']</div></td>';
                    %>
                <% }
                if (current_letter != the_letter && (kase.exact_match==0)) {
                    current_letter = the_letter;
            %>
            <tr class="<%=current_letter %> letter_row">
                <td colspan="15">
                	<div style="width:100%; 
                        text-align:left; 
                        font-size:1.8em; 
                        background:#CFF; 
                        color:red;">
                            <input type="checkbox" id="select_letter_<%= current_letter %>" class="select_letter" style="display:none; margin-left:5px; margin-right:5px" />
                            <%= current_letter %>
                    </div>
                </td>
            </tr>
            <% } %>
            <% if (new_header!="") { %>
            <tr>
                <%=new_header %>
            </tr>
            <% } %>
        <% var intCounter = 0;
        if (!kase.skip_me) {
            kaseCounter++;
        %>
        <tr class="kase_data_row injury_row_<%= kase.id %> kase_data_row_<%= kase.case_id %> <%=current_letter %>" style="height:35px; font-family:Arial, Helvetica, sans-serif; font-size:1em;">
            <td style="border:0px solid red;" width="525"> 	
                <span style="float:right; border:0px solid black; margin-right:5px" class="icons_holder">
                    <!--per thomas 9/17/2018-->
                    <!--
                    <a href="#kontrol_panel/<%= kase.case_id %>" title="Click to access the Kase Kontrol Panel"><i style="color:#8658F0" class="glyphicon glyphicon-th"></i></a>
                    &nbsp;
                    <% if (kase.case_status != "Intake") { %>
                    <a title="Click edit kase" class="compose_kase" id="compose_kase_<%= kase.case_id %>" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer"><i class="glyphicon glyphicon-edit" style="color:#0033FF"></i></a>
                    &nbsp;
                    <% } %>
                    -->
                    <!--data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false"-->
                    <a title="Click to compose a new note" class="compose_new_note" id="compose_note_<%= kase.case_id %>" style="cursor:pointer"><i class="glyphicon glyphicon-plus" style="color:#99FFFF"></i></a>
                    &nbsp;<a href="#notes/<%= kase.case_id %>" title="Click to review notes"><i class="glyphicon glyphicon-th-list" style="color:#81B08B"></i></a>
                    &nbsp;<a title="Click to compose a new message" class="compose_message" id="compose_<%= kase.case_id %>" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer"><i class="glyphicon glyphicon-pencil" style="color:#00FFFF"></i></a>
                    &nbsp;<a href="#documents/<%= kase.case_id %>" title="Click to manage documents"><i style="color:#FFFFFF" class="glyphicon glyphicon-upload"></i></a>
                     &nbsp;<a href="#activity/<%= kase.case_id %>" title="Click to review activity"><i style="color:#FF9900" class="glyphicon glyphicon-dashboard"></i></a>
                    
                    &nbsp;<a href="#kalendar/<%= kase.case_id %>"><i style="color:#FF9999" class="glyphicon glyphicon-calendar" title="Click to view kalendar"></i></a>
                    &nbsp;<a title="Click to create a task" class="compose_task" id="compose_task_0_<%= kase.case_id %>" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer"><i style="color:#66FF33" class="glyphicon glyphicon-inbox" title="Click to add task"></i></a>
                    &nbsp;<!--<a href="#phone/<%= kase.case_id %>/-1">-->
                    <a title="Click to add phone message" class="compose_phone" id="compose_phone_<%= kase.case_id %>" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer"><i style="color:#3C9" class="glyphicon glyphicon-earphone" title="Click to add phone message"></i></a>
                    &nbsp;
                    <a href="#letters/<%= kase.case_id %>" title="Click to compose a new letter" id="compose_letter_<%= kase.case_id %>" style="cursor:pointer"><i class="glyphicon glyphicon-file" style="color:#FFFF00"></i></a>
                    &nbsp;
                    <div style="display:inline-block; <%=kase.settlement_border %>">
                        <a href="#settlement/<%= kase.case_id %>/<%= kase.id %>" title="Click to review settlement information" id="settlement_<%= kase.id %>" style="cursor:pointer"><i class="glyphicon glyphicon-usd" style="color:#0F9"></i></a>
                    </div>
                    <!--per thomas 9/17/2018-->
                    <!--
                    &nbsp;<div style="display:inline-block; <%=kase.lien_border %>"><a href="#lien/<%= kase.case_id %>/<%= kase.id %>" title="Click to review lien information" id="lien_<%= kase.id %>" style="cursor:pointer;"><i class="glyphicon glyphicon-link" style="color:#FF972F"></i></a></div>
                    -->
                    <% if (kase.file_app!="") { %>
                    &nbsp;<div style="display:inline-block;"><%=kase.file_app %></div>
                    <% } %>
                </span>
                <div class="listing_item" style="float:right; text-align:left; width:180px; margin-right:5px">
                    <% 
                    kase.eams_search = false;
                    if (kase.start_date!="No DOI") { %>
                    <a id="injury_<%= kase.case_id %>" href='#injury/<%= kase.case_id %>/<%= kase.id %>' class="list-item_kase kase_link white_text" style="padding:2px; border:0px solid #CCC">DOI: <%=kase.start_date %></a>
                    <% } else { 
                        if (kase.full_name!="" && kase.full_name!="No Applicant" && kase.case_type=="WCAB") { %>
                    <a id="injury_<%= kase.case_id %>" href='#eams_injury_search/<%= kase.case_id %>/<%= kase.id %>' class="list-item_kase kase_link white_text" style="padding:2px; border:0px solid #CCC" target="_blank">	 eams search</a>
                        <% kase.eams_search =  true;
                        } else { %>
                            TBD
                        <% } %>
                    <% } %>
                </div>
                <input type="checkbox" id="select_kase_<%=kase.case_id %>" class="select_kase select_kase_<%=current_letter %>" style="display:none" />
                <a id="link_<%= kase.case_id %>" href='#kase/<%= kase.case_id %>' class="list-item_kase kase_link" style="font-size:1.2em"><%=highLight(kase.case_number, key) %><% if (kase.injury_number>1) { %>-<%=kase.injury_number %><% } %></a>&nbsp;<span class="kase_source"><%=kase.source %></span>
                &nbsp;<a id="windowlink_<%= kase.case_id %>" href='?n=#kase/<%= kase.case_id %>' class="list-item_kase kase_windowlink" target="_blank" title="Click to open this kase in its own window" style="display:none">Open&nbsp;in&nbsp;new&nbsp;window</a>
                <br />
                <span class="search_kase_item" style="width:503px; display:inline-block; font-size:1.1em">
                    <div style="float:right">
                        <% if (blnRecentList) { %>
                        <span style="font-size:1em">(last view: <%=kase.recent_time_stamp %>)</span>
                        <% } %>
                    </div>
                    <% if (customer_id == "1128") { %>
                    	<%=kase.case_name %>
                    <% } else { %>
                    	<%=kase.clean_name %>
                    <% } %>
                    
                </span>
            </td>
            <td><span class="listing_item"><%=kase.case_type %></span></td>
            <td>
                <% if (kase.case_type=="WCAB" && !kase.eams_search) { %>
                <span class="listing_item search_kase_item"><%=kase.adj_number %></span>
                <% } %>
            </td>
            <td><span class="listing_item"><%=highLight(kase.venue_abbr, key) %></span></td>
            <td nowrap="nowrap">
                <span class="listing_item"><%=kase.case_status %></span>
            </td>
            <td><span class="listing_item"><%=highLight(kase.case_date, key) %></span></td>
            <td nowrap="nowrap"><span class="listing_item"><%=highLight(kase.ssn, key) %></span></td>
            <td><span class="listing_item"><%=highLight(kase.dob, key) %></span></td>
            <td><span class="listing_item"><%=highLight(kase.language, key) %></span></td>
            <td><span class="listing_item"><%=highLight(kase.occupation, key) %></span></td>
			<td><span class="listing_item attorney_name"><%=kase.supervising_attorney_name == ""?kase.supervising_attorney.toUpperCase(): kase.supervising_attorney_name.toUpperCase()  %></span></td>
            <td><span class="listing_item sattorney_name"><%=kase.attorney_name.toUpperCase() %></span></td>
            <td><span class="listing_item worker_name worker_span_<%= kase.case_id %>"><%=kase.worker_name.toUpperCase() %></span></td>
            <td>        	
                <?php if (strpos($_SESSION['user_role'], "admin") !== false) { ?>
                    <i style="color:#FF3737; cursor:pointer" class="glyphicon glyphicon-trash delete_injury delete_kase_<%= kase.case_id %>" id="delete_<%= kase.id %>" title="Click to delete DOI"></i>
                <?php } else { ?>
                    &nbsp;
                <?php } ?>
            
            </td>
        </tr>
        <% if (kase.special_instructions!="" && kase.special_instructions!="undefined") { %>
        <tr class="kase_data_row injury_row_<%= kase.id %> kase_data_row_<%= kase.case_id %> <%=current_letter %>">
            <td colspan="14" style=""><span style="background:white; color:red; font-weight:bold">SPECIAL INSTRUCTIONS:</span> <%=kase.special_instructions %>
            </td>
        </tr>
        <% } %>
        <% }
        last_case_name = kase.case_name;
        }); %>
        <% if (!additional_rows) { %>
        </tbody>
    </table>
</div>
<div id="kase_listing_all_done"></div>
<script language="javascript">
$( "#kase_listing_all_done" ).trigger( "click" );
</script>
<% } %>

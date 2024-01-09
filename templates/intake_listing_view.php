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
    	<div style="float:right; margin-top:10px">
        	<div class="btn-group">
            
            	<label for="intakes_searchList" id="label_search_intakes" style="font-size:1em; cursor:text; position:relative; top:0px; left:105px; width:100px; color:#999">Search Intake</label>
            
				<input id="intakes_searchList" type="text" class="search-field" placeholder="" autocomplete="off" onkeyup="findIt(this, 'intake_listing', 'intake')" style="height:25px; line-height:32px; margin-top:-5px">
				<a id="intake_clear_search" style="position: absolute;
				right: 2px;
				top: 0;
				bottom: 9px;
				height: 14px;
				margin: auto;
				cursor: pointer;
				"><i class="glyphicon glyphicon-remove-circle" style='font-size:1em; color:#cccccc'></i></a>
			</div>
        </div>
    	<table cellpadding="5px" style="color:white">
        	<tr>
            	<td align="left" valign="middle">
                	<span style="font-size:1.2em; color:#FFFFFF">List of <span id="intake_status_title">Intake</span> Kases</span>
                </td>
                <td align="left" valign="top">
                	<span style="font-size:0.8em;" id="active_intake_count">(<%=kases_count %>)</span>
                </td>
                <td align="left" valign="middle">&nbsp;
                	
                </td>
                <td align="left" valign="middle">
                	Filter
                     
                    <select id="intake_type_filter">
                        <option value="" selected="selected">Select Type from List</option>
                        <option value="wcab">WC</option>
                        <option value="pi">PI</option>
                        <option value="social_security">Social Security</option>
                    </select>
                    &nbsp;
                    <select id="intake_status_filter">
                        <option value="">Select Status from List</option>
                        <option value="pending" selected="selected">Pending</option>
                        <option value="accepted">Accepted</option>
                        <option value="rejected">Rejected</option>
                    </select>
              </td>
                
                <td align="left" valign="middle">
                	<button type="button" class="btn btn-sm">
                    <a id="intake_report" class="intake_report print_kases_link" style="cursor:pointer; color:black">Print Kases</a>
                    </button>
                </td>
                <td align="left" valign="middle">
                	<button type="button" class="btn btn-sm btn-info" style="background-image:linear-gradient(to bottom,#c0e4ef 0,#2aabd2 100%);">
                    <a id="intake_export" class="intake_export print_kases_link" style="cursor:pointer; color:black">XL Export</a>
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
    <a href="report.php#kases/active" id="intake_print_listed_OBSOLETEID" class="last white_text print_kases_link" style="cursor:pointer"><i class='glyphicon glyphicon-print' style='color:#9EFF05; font-size:1em'></i></a>
</div>
<table id="intake_listing" class="tablesorter intake_listing" border="0" cellpadding="0" cellspacing="1" width="100%" style="-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; background:none">
    <thead>
    <tr>
        <th width="100">
            Kase
        </th>
        <th width="100" nowrap="nowrap">Intake Date</th>
        <th width="100" nowrap="nowrap">Injuy Date</th>
        <th width="100">
        	Type
        </th>
        <th width="30%">
            Injuries
        </th>
        <th>
            Status
        </th>
        <th>
            Language
        </th>
        <th width="30%">
            Occupation
        </th>
        <th>&nbsp;</th>
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
       _.each( kase_collection, function(kase) {
            //we might have a new letter
            /*
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
            if (kase.clean_name=="No Applicant") {
            	the_letter = "*";
            } else {
            	var arrName = kase.clean_name.trim().split(" ");
                if (arrName[0]=="vs") {
                	the_letter = "**";
                } else {
            		the_letter = kase.clean_name.trim().charAt(0).toUpperCase();
                }
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
            <td colspan="9">
                <div style="width:100%; 
                    text-align:left; 
                    font-size:1.8em; 
                    background:#CFF; 
                    color:red;">
                    	<%= current_letter %>
				</div>
            </td>
        </tr>
        <% } %>
        <% if (new_header!="") { %>
        <% } %>
    <% var intCounter = 0;
    var row_rejected = "";
    var display_row = "";
    /*
    if (kase.case_status=="REJECTED") {
    	row_rejected = " rejected";
        display_row = "display:none";
    }
    */
    if (!kase.skip_me) {
    	kaseCounter++;
    %>
    <tr class="intake_data_row injury_row_<%= kase.id %> intake_data_row_<%= kase.case_id %> <%=current_letter %> <%=row_rejected %>" style="height:35px; font-family:Arial, Helvetica, sans-serif; font-size:1em; <%=display_row %>">
        <td style="border:0px solid red;"> 
            <input type="checkbox" id="select_intake_<%=kase.case_id %>" class="select_kase" style="display:none" />
            <a id="link_<%= kase.case_id %>" href='#kase/<%= kase.case_id %>' class="list-item_kase intake_link" style="font-size:1.2em"><%=highLight(kase.case_number, key) %><% if (kase.injury_number>1) { %>-<%=kase.injury_number %><% } %></a>&nbsp;<span class="intake_source"><%=kase.source %></span>
            &nbsp;<a id="windowlink_<%= kase.case_id %>" href='?n=#kase/<%= kase.case_id %>' class="list-item_kase intake_windowlink" target="_blank" title="Click to open this kase in its own window" style="display:none">Open&nbsp;in&nbsp;new&nbsp;window</a>
            <br />
            <span class="search_intake_item" style="width:503px; display:inline-block; font-size:1.1em">
            	<div style="float:right">
                	<% if (blnRecentList) { %>
                    <span style="font-size:1em">(last view: <%=kase.recent_time_stamp %>)</span>
                    <% } %>
                </div>
            	<%=kase.clean_name %>
            </span>
        </td>
        <td><%=moment(kase.case_date).format("MM/DD/YYYY") %></td>
        <td><%=highLight(kase.doi, key) %></td>
        <td><span class="listing_item"><%=kase.case_type %></span></td>
        <td><span class="listing_item"><%=kase.explanation %></span></td>
        <td nowrap="nowrap">
        	<span class="listing_item"><%=kase.case_status.capitalize() %></span>
        </td>
        <td><span class="listing_item"><%=highLight(kase.language, key) %></span></td>
        <td><span class="listing_item attorney_name"><%=highLight(kase.occupation, key) %></span></td>
        <td>        	
			<?php if (strpos($_SESSION['user_role'], "admin") !== false) { ?>
                <i style="color:#FF3737; cursor:pointer" class="glyphicon glyphicon-trash delete_injury delete_intake_<%= kase.case_id %>" id="delete_<%= kase.id %>" title="Click to delete DOI"></i>
            <?php } else { ?>
                &nbsp;
            <?php } ?>
        
        </td>
    </tr>
    <% if (kase.special_instructions!="" && kase.special_instructions!="undefined") { %>
    <tr class="intake_data_row injury_row_<%= kase.id %> intake_data_row_<%= kase.case_id %> <%=current_letter %>">
        <td colspan="10" style=""><span style="background:white; color:red; font-weight:bold">SPECIAL INSTRUCTIONS:</span> <%=kase.special_instructions %>
        </td>
    </tr>
    <% } %>
    <% }
    }); %>
    <% if (!additional_rows) { %>
    </tbody>
</table>
<div id="kase_listing_all_done"></div>
<script language="javascript">
$( "#kase_listing_all_done" ).trigger( "click" );
</script>
<% } %>

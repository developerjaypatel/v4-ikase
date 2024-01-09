<?php
require_once('../shared/legacy_session.php');
session_write_close();

//special cases
$blnGlauber = ($_SESSION["user_customer_id"]=='1049');
//$blnAdmin = (strpos($_SESSION['user_role'], "admin"));

error_reporting(E_ALL);
ini_set('display_errors', '1');

include("../api/connection.php");
$db = getConnection();

$arrOptions = array("notes"=>array());

$sqlfilters = "SELECT * 
FROM ikase.cse_customer_document_filters 
WHERE customer_id = :customer_id";
$stmt = $db->prepare($sqlfilters);
$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
$stmt->execute();
$filter = $stmt->fetchObject();

if (is_object($filter)) {
	$filter = json_decode($filter->document_filters);
	
	$filter_types = array();
	
	if (isset($filter->notes)) {
		$filter_types = $filter->notes;
	}
	foreach($filter_types as $filter_type) {
		if (strpos($filter_type, "|deleted")!==false) {
			continue;
		}
		$filter_name = $filter_type;
		$filter_display_name = strtoupper($filter_type);
		$filter_display_name = str_replace("_", " ", $filter_display_name);
		$option = '<option value="' . trim($filter_name) . '">' . trim($filter_display_name) . '</option>';
		$arrOptions["notes"][] = $option;
	}
}
//echo $_SESSION['user_role'] . " - " . strpos($_SESSION['user_role'], "admin");
if (strpos($_SESSION['user_role'], "admin") !== false && count($arrOptions["notes"]) > 0) {
	$option = '<option style="font-size: 1pt; background-color: #000000;" disabled>&nbsp;</option>
	<option value="new_filter" style="background:lime">Manage List</option>';
	$arrOptions["notes"][] = $option;
}
$select_types = implode("\r\n", $arrOptions["notes"]);

?>
<div id="confirm_delete" style="display:none; position:absolute; top:20px; color:white; background:black; border:1px solid white; font-size:1.2em; padding:10px; text-align:center; z-index:1051">
	<div style="width:350px; margin-left:auto; margin-right:auto; ">
    <input type="hidden" name="confirm_delete_id" id="confirm_delete_id" value="" />
    Are you sure you want to delete this note?
    <div style="padding:5px; text-align:center"><a id="delete_partie" class="delete_yes white_icon" style="cursor:pointer">YES</a></div>
    <div style="padding:5px; text-align:center"><a id="cancel_partie" class="delete_no white_icon" style="cursor:pointer">NO</a></div>
    </div>
</div>
<div class="note_listing">
	<div id="glass_header" class="glass_header" style="height:45px">
        <div style="float:right;">
        	<% if (display_mode=="full") { %>
            <?php if ($blnGlauber) { ?>
            <select id="typeFilter" class="modal_input" style="margin-top:-2px;">
                <option value="">Filter by Type</option>
            <option value="General">General</option>
            <option value="quick">Quick Note</option>
            <option value="132A_SW">132A &amp; S&amp;W</option>
            <option value="third_party referrals">3rd Party Referrals</option>
            <option value="AME Report">AME Report</option>
            <option value="AME/QME Prep">AME/QME Prep</option>
            <option value="Calendar - Notes and Orange Slips">Calendar - Notes and Orange Slips</option>
            <option value="Correspondence and Emails">Correspondence and Emails</option>
            <option value="Cross X Summary">Cross X Summary</option>
            <option value="Defense Meds">Defense Meds</option>
            <option value="Depo Trans">Depo Trans</option>
            <option value="Fax Confirmation">Fax Confirmation</option>
            <option value="Hearings">Hearings</option>
            <option value="HomeCare">HomeCare</option>
            <option value="Internal">Internal</option>
            <option value="Legal">Legal</option>
            <option value="Liens">Liens</option>
            <option value="Misc. App. Meds">Misc. App. Meds</option>
            <option value="Monthly Status">Monthly Status</option>
            <option value="MPN Correspondence">MPN Correspondence</option>
            <option value="Neuro">Neuro</option>
            <option value="Ortho">Ortho</option>
            <option value="Out of Pocket/Transportation">Out of Pocket/Transportation</option>
            <option value="POA/Attorney Meeting">POA/Attorney Meeting</option>
            <option value="Psych">Psych</option>
            <option value="QME Objection Request (Correspondence Only)">QME Objection Request (Correspondence Only)</option>
            <option value="SDT Records">SDT Records</option>
            <option value="Settlement/Calls">Settlement/Calls</option>
            <option value="QME OBJ/REQ">QME OBJ/REQ</option>
            <option value="UEF Docs">UEF Docs</option>
            <option value="UR/IMR">UR/IMR</option>
            <option value="Vocational Rehab Expert">Vocational Rehab Expert</option>
            <option value="W2 Forms / Earnings">W2 Forms / Earnings</option>
            </select>
            <?php } else { 
				if (count($arrOptions["notes"]) > 0) { ?>
            <select id="typeFilter" class="modal_input" style="margin-top:-2px;" onchange="filterNotes(event)">
            	<option value="">Filter by Type</option>
                <option value="general">General Note</option>
                <?php echo $select_types; ?>
          </select>
            <?php
				} else {
			?>
            <select id="typeFilter" class="modal_input" style="margin-top:-2px;">
            	<option value="">Filter by Type</option>
                <% if (note_filter_options.indexOf("general") < 0) { %>
                <option value="general">General Note</option>
                <% } %>
                <%=note_filter_options %>
                <?php //if ($blnAdmin) { ?>
                <option style="font-size: 1pt; background-color: #000000;" disabled>&nbsp;</option>
                <option value="new_filter">Manage List</option>
                <?php //} ?>
          </select>
            <?php
				}
			} ?>
            <% } %>
            <input id="case_id" name="case_id" type="hidden" value="<%=case_id %>" />
            <a href="report.php#notes/<%=case_id %>" target="_blank" title='Click to print notes' style='cursor:pointer; display:' class="white_text">Print Notes</a>&nbsp;&nbsp;&nbsp;&nbsp;
			<div class="btn-group">
            
            	<label for="notes_searchList" id="label_search_notes" style="font-size:1em; cursor:text; position:relative; top:0px; left:105px; width:100px; color:#999">Search Notes</label>
            
				<input id="notes_searchList" type="text" class="search-field" placeholder="" autocomplete="off" onkeyup="findIt(this, 'note_listing', 'note')" style="height:25px; line-height:32px; margin-top:-5px">
				<a id="note_clear_search" style="position: absolute;
				right: 2px;
				top: 0;
				bottom: 9px;
				height: 14px;
				margin: auto;
				cursor: pointer;
				"><i class="glyphicon glyphicon-remove-circle" style='font-size:1em; color:#cccccc'></i></a>
			</div>
        </div>
       	<div style="width:180px">
        	<div style="float:right">
            <%
            var the_partie_type = partie_type.replace("_", "~");
            var the_partie_id = partie_id;
            var the_partie_suffix = the_partie_id;
            if (the_partie_type=="injurynote") {
	            the_partie_suffix = current_case_id + "_" + the_partie_id;
            }
            %>
            <!--<a title="Click to compose a new note" class="compose_new_note" id="compose_<%=the_partie_type %><% if (the_partie_id > 0) { %><%="_" + the_partie_suffix %><% } %>" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer"><i class="glyphicon glyphicon-plus" style="color:#99FFFF">&nbsp;</i></a>  
            -->
            <!-- <button id="compose_<%=the_partie_type %><% if (the_partie_id > 0) { %><%="_" + the_partie_suffix %><% } %>" class="compose_new_note btn btn-sm btn-primary" title="Click to create a new Note" style="margin-top:-5px">New Note</button> -->
            <button id="compose_<%=the_partie_type %><% if (the_partie_id > 0) { %><%="_" + the_partie_suffix %><% } %>" class="compose_new_note btn btn-sm btn-primary" title="Click to create a new Note" style="margin-top:-5px" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false">New Note</button> 
            </div>
            <span style="font-size:1.2em; color:#FFFFFF"><span id="note_title_partie_type" style="display:none"><%=partie_type %> </span>Notes</span>&nbsp;<div style="position: relative;left: 60px; padding-left:3px; margin-top:-19px; color:white; font-size:0.8em; width:20px">(<%=notes.length %>)</div>
        </div>        
    </div>
    <div id="note_preview_panel" style="position:absolute; width:200px; display:none; z-index:2; background:white; border:1px solid pink" class="attach_preview_panel"></div>
    <table id="note_listing" class="tablesorter note_listing" border="1" cellpadding="0" cellspacing="0" width="100%">
        <thead>
        <tr>
            <th style="font-size:1.5em; width:10%; text-align:left">
                Time
            </th>
            <th style="font-size:1.5em; width:5%; text-align:left;">
                By
            </th>
            <th style="font-size:1.5em; width:75%; text-align:left">
                Subject
            </th>
            <th style="font-size:1.5em; text-align:left; width:10%">
                Type
            </th>
            <th style="font-size:1.5em; text-align:left; width:25px">&nbsp;
            	
            </th>
        </tr>
        </thead>
        <tbody>
       <% 
       var current_day = "";
       _.each( notes, function(note) {
       	title = note.title;
        attribute = note.attribute;
        if (attribute=="main") {
        	attribute = "";
        }
        if (display_mode=="full") {
            if (attribute!="" && note.type!="Webmail") {
            	if (attribute!="quick") {
                	note.type += "<br />" + attribute;
                }
            }
		}
        //we might have a new day
        var the_day = moment(note.dateandtime).format("MMDDYY");
        
        //they can edit whatever they want because all tracked and activitied 10/17/2015
       var edit_indicator = "hidden";
       	//if (note.entered_by == login_username) {
        	edit_indicator = "visible";
        //}
        if (current_day != the_day) {
            current_day = the_day;
        %>
        	<tr class="date_row row_<%= the_day %>">
                <td colspan="5">
                    <div style="width:100%; 
	text-align:left; 
	font-size:1.8em; 
	background:#CFF; 
	color:red;"><%= note.date %></div>
                </td>
            </tr>
        <% } %>
       	<tr class="note_data_row note_data_row_<%= note.id %> row_<%= the_day %>" style="border:#00CC00 0px solid;">
        	<td colspan="4">
            	<table style="width:100%" border="1">
                <tr>
                    <td style="font-size:1.5em; width:75px;" align="left" nowrap="nowrap">
                    	<div style="float:right; margin-left:5px;">
                        	<%= note.attachment_link %>
                        </div>
                        <div style="float:right;">
                        	<%if (note.editable) { %>
								<a id="open_note_<%=case_id %>_<%= note.id %>" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" class="edit_note" style="; cursor:pointer"><i style="font-size:15px;color:#06f; cursor:pointer; visibility:<%= edit_indicator %>" class="glyphicon glyphicon-edit" title="Click to Edit Note"></i></a>
							<% } %>
                            &nbsp;<a href="report.php#note/<%=note.id %>" target="_blank"><i style="font-size:15px;color:#CC99CC; cursor:pointer;" class="glyphicon glyphicon-print" title="Click to Print Note"></i></a>
						</div>
                        
                        <%= note.time %>
                    </td>
                    <td style="font-size:1.5em; width:5%;" align="left">
                        <%= note.entered_by.firstLetters() %>
                    </td>
                    <td style="font-size:1.5em; width:75%;" align="left">
                        <%= note.subject %>
                    </td>
                    <td style="font-size:1.5em; width:10%" class="note_type_cell" align="left"><%= note.type.replaceAll("_", " ").toUpperCase() %></td>
		        </tr>
        		<tr id="partial_note_holder_<%=note.id %>">
        			<td colspan="4" style="font-size:1.5em;">
                        <%= note.note %>
                    </td>
                </tr>
                <tr id="full_note_holder_<%=note.id %>" style="display:none">
        			<td colspan="4" style="font-size:1.5em;">
                        <%= note.full_note %>
                    </td>
                </tr>
            </table>
          </td>
          <td>
          	<?php if (strpos($_SESSION['user_role'], "admin") !== false) { ?>
            	<i style="font-size:15px; color:#FF3737; cursor:pointer" class="glyphicon glyphicon-trash delete_note" id="delete_<%= note.id %>" title="Click to delete"></i>
            <?php } ?>
            </td>
        </tr>
        <% }); %>
        </tbody>
    </table>
</div>

<?php
//special cases
//$blnGlauber = ($_SESSION["user_customer_id"]=='1049');
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
	<div id="glass_header" class="glass_header" style="padding-top:5px">
        <span style="font-size:1.5em; color:#FFFFFF">Notes</span>&nbsp;(<%=notes.length %>)
        <a title="Click to compose a new note" href="#notemobile/<%=case_id %>" class="compose_new_note_mobile" id="compose_note_<%=case_id %>" style="cursor:pointer"><i class="glyphicon glyphicon-plus" style="color:#99FFFF">&nbsp;</i></a>          
    </div>
    <div id="note_preview_panel" style="position:absolute; width:200px; display:none; z-index:2; background:white; border:1px solid pink" class="attach_preview_panel"></div>
    <table style="width:440px" border="1" cellpadding="0" cellspacing="0" class="tablesorter note_listing_mobile" id="note_listing_mobile">
        <thead>
            <tr>
                <th style="font-size:1.5em; text-align:left; width:60px">
                    Time
                </th>
                <th style="font-size:1.5em; text-align:left; width:10px">
                    By
                </th>
                <th style="font-size:1.5em; text-align:left; width:70px">
                    Subject
                </th>
                <th style="font-size:1.5em; text-align:left; width:40px">
                    Type
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
                <td colspan="4">
                    <div style="width:440px; text-align:left; font-size:1.8em; background:#CFF; color:red;"><%= note.date %></div>
                </td>
            </tr>
        <% } %>
       	<tr class="note_data_row note_data_row_<%= note.id %> row_<%= the_day %>">
        	<td colspan="4">
            	<table border="1" style="width:440px">
                <tr>
                    <td style="font-size:1.5em; width:145px" align="left" nowrap="nowrap">
                    	<div style="float:right; margin-left:5px;">
                        	<%= note.attachment_link %>
                        </div>
                        <div style="float:right; display:none">
                        	<%if (note.editable) { %>
								<a id="open_note_<%=case_id %>_<%= note.id %>" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" class="edit_note" style="; cursor:pointer"><i style="font-size:15px;color:#06f; cursor:pointer; visibility:<%= edit_indicator %>" class="glyphicon glyphicon-edit" title="Click to Edit Note"></i></a>
							<% } %>
						</div>
                        
                        <%= note.time %>
                    </td>
                    <td style="font-size:1.5em; width:30px" align="left">
                        <%= note.entered_by.firstLetters() %>
                    </td>
                    <td style="font-size:1.5em; width:150px" align="left">
                        <%= note.subject %>
                    </td>
                    <td style="font-size:1.5em; width:90px" class="note_type_cell" align="left"><%= note.type.replaceAll("_", "/").toUpperCase() %></td>
		        </tr>
        		<tr>
        			<td colspan="4" style="">
                        <%= new_subject %>
                    </td>
                    
                </tr>
                <tr>
        			<td colspan="4" style="font-size:1.5em;">
                        <%= note.note %>
                    </td>
                    
                </tr>
            </table>
          </td>
        </tr>
        <% }); %>
        </tbody>
    </table>
</div>

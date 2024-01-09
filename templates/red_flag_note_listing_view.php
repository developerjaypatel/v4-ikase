<?php
require_once('../shared/legacy_session.php');
session_write_close();

//special cases
$blnGlauber = ($_SESSION["user_customer_id"]=='1049');
?>
<div class="note_listing">
    <div id="note_preview_panel" style="position:absolute; width:200px; display:none; z-index:2; background:white; border:1px solid pink" class="attach_preview_panel"></div>
    <table id="note_listing" class="tablesorter note_listing" border="1" cellpadding="0" cellspacing="0" width="100%">
        <tbody>
       <% 
       var current_day;
       _.each( notes, function(note) {
       	title = note.title;
        attribute = note.attribute;
        if (attribute=="main") {
        	attribute = "";
        }
        if (display_mode=="full") {
            if (attribute!="" && note.type!="Webmail") {
                note.type = attribute;
            }
		}
        //we might have a new day
        var the_day = moment(note.dateandtime).format("MMDDYY");
        
       var edit_indicator = "hidden";
        if (note.entered_by == login_username) {
        	edit_indicator = "visible";
        }
        %>
       	<tr class="note_data_row note_data_row_<%= note.id %> row_<%= the_day %>" style="border:#00CC00 0px solid;">
        	<td width="1%" style="background:white;border-left:#F00 2px solid; border-top:#F00 2px solid; border-bottom:#F00 2px solid">
            <i style="font-size:15px;color:red;" class="glyphicon glyphicon-flag"></i>
            </td>
            <td style="font-size:1.5em; width:55%;background:white; color:#000; border-top:#F00 2px solid; border-bottom:#F00 2px solid" align="left">
                <%= note.note %>
          </td>
          <td width="1%" style="background:white; border-right:#F00 2px solid; border-top:#F00 2px solid; border-bottom:#F00 2px solid">
          	<i style="font-size:15px;color:red;" class="glyphicon glyphicon-flag"></i>
            </td>
        </tr>
        <% }); %>
        </tbody>
    </table>
</div>

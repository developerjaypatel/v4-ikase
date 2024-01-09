<div style="border:#000000; width:99%" align="center">

	<table border="0" cellpadding="2" cellspacing="0" style="width:90%" align="center">  		
        <thead>
        <tr>
            <td width="77"><img src="img/ikase_logo_login.png" height="32" width="77"></td>
            <td align="left" colspan="6">
                <div style="float:right">
                    <em>Found Notes as of <?php echo date("m/d/y g:iA"); ?></em>
                </div>
                <span style="font-family:Arial, Helvetica, sans-serif; font-weight:bold; font-size:1.0em">&nbsp;</span>
            </td>
          </tr>
        <tr>
            <th style="font-size:1.1em" align="center" colspan="6">
                Note Listing<br /><br />
            </th>
        </tr>
        </thead>
    </table>

    <table id="note_listing" class="tablesorter note_listing" border="0" cellpadding="5" cellspacing="0" width="90%">
        <thead>
        <tr>
            <th style="font-size:1.1em; width:10%; text-align:left; border-bottom:1px solid black">
                Time
            </th>
            <th style="font-size:1.1em; width:5%; text-align:left; border-bottom:1px solid black">
                By
            </th>
            <th style="font-size:1.1em; width:75%; text-align:left; border-bottom:1px solid black">
                Subject
            </th>
            <th style="font-size:1.1em; text-align:left; width:10%; border-bottom:1px solid black">
                Type
            </th>
            <th style="font-size:1.1em; text-align:left; width:25px; border-bottom:1px solid black">&nbsp;
                
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
        //we might have a new day
        var the_day = moment(note.dateandtime).format("MMDDYY");
        var the_day_date = moment(note.dateandtime).format("MM/DD/YY");
        var edit_indicator = "hidden";
       	
        if (current_day != the_day) {
            current_day = the_day;
        %>
        	<tr class="date_row row_<%= the_day %>">
                <td colspan="5">
                    <div style="width:100%; 
	text-align:left; 
	font-size:1.2em; 
	background:#CFF; 
	color:red;"><%= the_day_date %></div>
                </td>
            </tr>
        <% } %>
       	<tr class="note_data_row note_data_row_<%= note.id %> row_<%= the_day %>" style="border:#00CC00 0px solid;">
        	<td colspan="4">
            	<table style="width:100%; border-bottom:1px solid black" border="0">
                <tr>
                    <td style="font-size:1.0em; width:10%;" align="left">
                    	<div style="float:right; margin-left:5px;">
                        	<%= note.attachment_link %>
                        </div>
                        <div style="float:right;">
                        	<%if (note.editable) { %>
								<a id="open_note_<%=case_id %>_<%= note.id %>" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" class="edit_note" style="; cursor:pointer"><i style="font-size:15px;color:#06f; cursor:pointer; visibility:<%= edit_indicator %>" class="glyphicon glyphicon-edit" title="Click to Edit Note"></i></a>
							<% } %>
                            &nbsp;<a href="report.php#note/<%=note.id %>" target="_blank"><i style="font-size:15px;color:#CC99CC; cursor:pointer;" class="glyphicon glyphicon-print" title="Click to Print Note"></i></a>
						</div>
                        
                        <%= moment(note.dateandtime).format("hh:mma") %>
                    </td>
                    <td style="font-size:1.0em; width:5%;" align="left">
                        <%= note.entered_by.firstLetters() %>
                    </td>
                    <td style="font-size:1.0em; width:75%;" align="left">
                        <%= note.subject %>
                    </td>
                    <td style="font-size:1.0em; width:10%" class="note_type_cell" align="left"><%= note.type.replaceAll("_", "/").toUpperCase() %></td>
		        </tr>
        		<tr>
        			<td colspan="4" style="font-size:1.0em;">
                        <%= note.note %>
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
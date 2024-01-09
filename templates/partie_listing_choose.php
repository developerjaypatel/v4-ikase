<div>
    <table id="partie_listing" class="tablesorter partie_listing" border="0" cellpadding="0" cellspacing="1">
    	<% if (letter.letter_category=="Any") { %>
        <thead>
        	<th>To</th>
            <th>CC</th>
            <th>Name</th>
            <th>Address</th>
        </thead>
        <% } %>
        <tbody>
        <%
        var intCounter = 0; 
        _.each( parties, function(partie) {
        	if (!partie.blnSkipPartie) {
        %>
        <tr class="partie_data_row">
        	<% if (letter.letter_category=="Any") { %>
            <td style="font-size:0.8em"><input type="checkbox" class="event_any" name="event_any_<%= intCounter %>" id="event_any_<%= partie.partie_id %>" value="<%= partie.partie_id %>" />
            </td>
            <% } %>
            <td style="font-size:0.8em"><input type="checkbox" class="event_partie" name="event_partie_<%= intCounter %>" id="event_partie_<%= partie.partie_id %>" value="<%= partie.partie_id %>" />
            </td>
            <td>
            	<div style="margin-bottom:5px">
                    <span id="event_partie_name_<%= partie.partie_id %>" style="font-size:0.9em">
                        <%= partie.company_name %>
                    </span>
                </div>
                <div>
                    <div style="float:right; display:none" class="depo_partie_holder" id="depo_partie_<%= partie.partie_id %>">
                        <button class="btn btn-xs depo_set" id="depo_set_<%= partie.partie_id %>">Depo Location</button>
                    </div>
                    <span id="event_partie_type_<%= partie.partie_id %>" style="background:#9FF; color:black;font-size:0.9em">
                        <%= partie.type %>
                    </span>
                    <% if (partie.type!="In House") { %>
                        <div style="width:150px; text-align:right; position:relative; margin-top:-12px">
                            <a title="Click to generate an envelope" class="compose_new_envelope" id="htmlenvelope_<%= partie.original_type %>_<%= partie.partie_id %>" style="cursor:pointer">
                                <span style="position:absolute; margin-top:-8px; margin-left:5px; z-index:9999; color:aquamarine">W</span>
                                <i class="glyphicon glyphicon-envelope" style="color:yellow">&nbsp;</i>
                            </a><span id="feedback_html_<%= partie.original_type %>_<%= partie.partie_id %>"></span>&nbsp;
                            <a title="Click to generate a PDF envelope" class="compose_pdf_envelope" id="pdfenvelope_<%= partie.original_type %>_<%= partie.partie_id %>" style="cursor:pointer">
                                <div style="position:absolute;z-index:9999;left: 135px;top: -8px;font-weight:bold;color:white;" id="pdf_<%= partie.partie_id %>">PDF</div>
                                <i class="glyphicon glyphicon-envelope" style="color:yellow">&nbsp;</i>
                            </a><span id="feedback_pdf_<%= partie.original_type %>_<%= partie.partie_id %>"></span>
                        </div>
                    <% } %>
                </div>
            </td>
            <td style="font-size:0.8em">
            	<span id="event_partie_address_<%= partie.partie_id %>"><%= partie.address %></span>
            </td>
        </tr>
        <% 		intCounter++;
        	}
        }); %>
        </tbody>
    </table>
</div>

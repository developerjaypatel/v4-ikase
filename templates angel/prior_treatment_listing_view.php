<div>
	<div class="glass_header">
        <span style="font-size:1.2em; color:#FFFFFF">Prior Treatment Providers</span>
		<span style="font-size:1.2em; cursor: pointer;">&nbsp;&nbsp;&nbsp;<a id="add_medical" href="#parties/<%=current_case_id %>/-2/medical_provider" class="medical_items" title="Click to add Prior Medical Provider to Applicant" style="background:#CFF; color:black; padding:2px">Add Prior Medical</a></span>
    </div>
    <table id="partie_listing" class="tablesorter partie_listing" border="0" cellpadding="0" cellspacing="1">
        <tbody>
        
        <% _.each( parties, function(partie) {
        	if (typeof partie.other_description == "undefined") {
            	partie.other_description = "";
            }
        %>
        <tr class="partie_data_row corporation_row_<%= partie.id %>" style="border-top:1px solid white">
            <td style="font-size:1.5em"><a href='<%= partie.thehref %>' class='white_text'><%= partie.company_name %></a></td>
            <td style="font-size:1.5em" nowrap="nowrap" align="left"><%= partie.phone %></td>
            <td style="font-size:1.5em" nowrap="nowrap" align="left"><%= partie.email %></td>
            <td style="font-size:1.5em" nowrap="nowrap" align="left"><%= partie.address %></td>
            <td style="font-size:1.5em" align="left"><%= partie.records_requested %></td>
            <td style="font-size:1.5em" align="left"><%= partie.other_description %></td>
            <td style="font-size:1.5em" align="left"><%= partie.any_all %></td>
            <td style="font-size:1.5em" align="left">
            
            <i style="font-size:15px; color:#FF3737; cursor:pointer" class="glyphicon glyphicon-trash delete_prior_medical" id="delete_<%= partie.id %>" title="Click to delete"></i>
            
            </td>
        </tr>
        <% if (partie.special_instructions!="" && typeof partie.special_instructions!="undefined") { %>
        <tr>
        	<td colspan="8" align="left"><span class="special_instructions">Special Instructions:</span> <%=partie.special_instructions %></td>
        </tr>
        <% } %>
        <% }); %>
        </tbody>
    </table>
</div>

<input type="hidden" name="partie_count" value="<%=parties.length %>" />
<table cellpadding="2" cellspacing="0" border="0" width="100%">
	<%  
    var iCounter = 1;
    var attyCounter = 1;
    var empCounter = 1;
    var current_role = parties[0].role;
    _.each( parties, function(partie) { %>
    <% 
    	var role_id = partie.role.replace(" ").toLowerCase();
        if (current_role != partie.role) {
    		current_role = partie.role; %>
    <tr>
    	<td align="left" valign="top" colspan="4" style="border-bottom:1px solid black">
        </td>
    </tr>
    <% } %>
    <tr>
    	<th align="left" valign="top" style="padding-right:10px; width:150px">
        	<% if (partie.count > 1 && (partie.role=="EMPLOYER" || partie.role=="LAW FIRM")) {
            	var thechecked = "";
                if (empCounter==1 && partie.role=="EMPLOYER") {
                	thechecked = " checked";
                }
                if (attyCounter==1 && partie.role=="LAW FIRM") {
                	thechecked = " checked";
                } %>
            <div style="float:right">
            	<input type="radio" id="role_choice_<%= iCounter %>" name="role_choice" value="<%=partie.name %>" title="Click to select as the main <%=partie.role.capitalizeWords() %> for the kase" <%=thechecked %> />
            </div>
            <% if (partie.role=="EMPLOYER") {
            	empCounter++;
               }
            } %>
            <% if (partie.role == "CLAIMS ADMINISTRATOR") { %>
            <select id="role_choice_<%= iCounter %>" class="role_choice">
            	<option value="claims_administrator" selected="selected">Claims Administrator</option>
                <option value="carrier">Carrier</option>
            </select>
            <% } else { %>
            <%=partie.role.capitalizeWords() %>
            <% } %>
        </th>
        <td align="left" valign="top" style="padding-right:10px">
        	<% if (partie.count > 1 && partie.role=="LAW FIRM") { %>
            <div style="float:right">
            <select name="attorney_type_<%= iCounter %>" class="attorney_type">
            	<option value="" <% if (partie.case_firm!="Y") { %>selected="selected"<% } %>>Attorney Type</option>
                <option value="applicant"  <% if (partie.case_firm=="Y") { %>selected="selected"<% } %>>Applicant</option>
                <option value="defense">Defense</option>
                <option value="medical">Medical</option>
                <option value="other">Other</option>
                <option value="prior">Prior</option>
            </select>
            </div>
            <% attyCounter++;
            } %>
            <%=partie.name.capitalizeWords() %>
            <input type="hidden" name="partie_<%= iCounter %>"  value="<%=partie.name %>" />
            <input type="hidden" name="partie_role_<%= iCounter %>" id="partie_role_<%= iCounter %>"  value="<%=partie.role %>" />
            <input type="hidden" name="partie_address_<%= iCounter %>"  value="<%=partie.address %>" />
            <input type="hidden" name="partie_street_<%= iCounter %>"  value="<%=partie.street %>" />
            <input type="hidden" name="partie_city_<%= iCounter %>"  value="<%=partie.city %>" />
            <input type="hidden" name="partie_state_<%= iCounter %>"  value="<%=partie.state %>" />
            <input type="hidden" name="partie_zip_<%= iCounter %>"  value="<%=partie.zip %>" />
        </td>
        <td align="left" valign="top"><%=partie.address.capitalizeWords() %></td>
        <td align="left" valign="top">
        	<% 
            if (partie.role=="EMPLOYER" && employer=="") {
            	%>
                <div id="add_employer_<%=iCounter %>" class="employer_eams_holder" style="display:none">
                	<button class="btn btn-xs  btn-primary" onclick='addEAMSPartie(event, "<%=partie.name.replaceAll("'", "~") %>", "<%=partie.address.replaceAll("'", "~") %>", "<%=partie.street.replaceAll("'", "~") %>", "<%=partie.city.replaceAll("'", "~") %>", "<%=partie.state %>", "<%=partie.zip %>", "<%=case_id %>", "<%=iCounter %>", "employer")'>add employer</button>
                </div>
                <%
            } 
            if (partie.role=="CLAIMS ADMINISTRATOR") {
            %>
            <div id="add_carrier_<%=iCounter %>" class="carrier_eams_holder" style="display:none">
                <button class="btn btn-xs  btn-primary" onclick='addEAMSPartie(event, "<%=partie.name.replaceAll("'", "~") %>", "<%=partie.address.replaceAll("'", "~") %>", "<%=partie.street.replaceAll("'", "~") %>", "<%=partie.city.replaceAll("'", "~") %>", "<%=partie.state %>", "<%=partie.zip %>", "<%=case_id %>", "<%=iCounter %>", "carrier")'>add carrier</button>
            </div>
            <% } %>
        </td>
    </tr>
    <%	iCounter++;
    }); %>
</table>
<hr style="margin:5px" />
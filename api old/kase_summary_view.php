<div>
	<div class="glass_header white_text">
    	<% if (dois!="" && doi_count < 6) { %>
        	<% if (doi_count == 1) { 
            		var height = "-20px";
                }
                if (doi_count == 2) { 
            		var height = "-20px";
                }
                if (doi_count == 3) { 
            		var height = "-15px";
                }
                if (doi_count == 4) { 
            		var height = "-10px";
                }
                if (doi_count == 5) { 
            		var height = "-80px";
                }
             %>
            <div style="float:right; margin-top:0px; border:0px solid green"><table border="0" style="margin-top:<%= height %>"><%=dois %></table></div>
        <% } %>
        
        <% if (full_name != "") { %>
        <a href="#applicant/<%= case_id %>" class="white_text" title="Click to review Applicant information"><%= full_name.capitalizeAllWords() %></a> vs <a href="#parties/<%= case_id %>/<%= employer_id %>/employer" class="white_text" title="Click to review Employer information"><%= employer %></a><span class='black_text'>&nbsp;|&nbsp;</span><% } else { %><%=case_number %>&nbsp;|&nbsp;<% } %><%= venue_abbr %>
        <% if (dob!="") { %>
        <span class='black_text'>&nbsp;|&nbsp;</span><span title='DOB'>DOB: <%= dob %></span>
        <% } %>
        <% if (ssn!="") { %>
        <span class='black_text'>&nbsp;|&nbsp;</span><span title='SSN'>SSN: <%= ssn %></span>
        <% } %>
        <% if (attorney!="") { %>
        <span class='black_text'>&nbsp;|&nbsp;</span><span title='<%= attorney_full_name %>'>ATTY: <%= attorney_name.toUpperCase() %></span>
        <% } %>
        <% if (worker!="") { %>
        <span class='black_text'>&nbsp;|&nbsp;</span><span title='<%= worker_full_name %>'>WRK: <%= worker_name.toUpperCase() %></span>
        <% } %>
        <button id="kase_edit_<%= case_id %>" class="kase_edit edit btn btn-transparent border-blue" style="border:0px solid; width:20px" title="Click to Edit kase information"><i class="glyphicon glyphicon-edit" style="color:#0033FF">&nbsp;</i></button>
		<div id="settlement_injury_view_<%= case_id %>" style="cursor:pointer"><span style="color: white;">Settlement</span> <span style="color: green;">$$</span></div>
        <% if (dois!="" && doi_count > 5) { %>
        <br /><br />
			
            <div style="margin-top:0px; border:0px solid green; display:inline-block; width:100%"><%=dois %></div>
        <% } %>
		
        <% for (i=0;i<doi_count;i++) { 
                if (doi_count > 1 && doi_count < 6 ) {%>
                 <div style="height:15px; border: 0px red solid">&nbsp;</div>
             <% } else { %>
                 <div style="height:3px; border: 0px red solid">&nbsp;</div>
             <% } 
           } %> 
    </div>
    
</div>
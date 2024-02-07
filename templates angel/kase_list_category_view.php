<div id="kase_category_listing" style="margin-left:0px">
    <table id="kase_list_table" class="list" width="100%">
    	<% _.each( kases, function(kase) {%>
        <tr>
        	<td align="left" valign="top" width="75%">
            	<a id="kaselink_<%= kase.case_id %>" href='#kases/<%= kase.case_id %>' class="list-item_kase kase_link_left" title="Click to review this case"><%= kase.case_number %><% if (kase.injury_number>1) { %>-<%=kase.injury_number %><% } %></a>
                
                <a id="kase_windowlink_left_<%= kase.case_id %>" class="kase_modal list-item_kase kase_windowlink_left" title="Click to open this kase" style="display:none; cursor:pointer">edit</a>
            </td>
            <td align="right" valign="top" style="color:#FFFFFF">
            	<%= kase.venue_abbr %>
            </td>
        </tr>
        <tr>
            <td align="left" valign="top" colspan="2" style="color:#FFFFFF; font-size:0.74em;">
            	<% if (kase.first_name != "" || kase.last_name != "") { %>
                
            	<a href='#applicant/<%= kase.case_id %>' class="white_text"><%= kase.first_name + " " + kase.last_name %></a> <span style="color:#33FF99">vs</span> 
                    <% if (kase.employer!=null) { %>
                    <a href='#parties/<%= kase.case_id %>/<%= kase.employer_id %>/employer' class="white_text"><%= kase.employer %></a>
                    <% } %>
                <% } %>
                <% 
                var show_doi = "none";
                if (kase.start_date != "" ) { 
                	show_doi = "";
                    if (kase.end_date=="00/00/0000") {
                    	kase.end_date = "";
                    }
                    if (kase.end_date != ""){
                        kase.end_date =  " - " + kase.end_date + " CT";
                    }
                }
				%>
                <div style="display:<%=show_doi %>;">
                	<span>
                    	<a href='#injury/<%= kase.case_id %>' class="white_text">DOI</a>: <%= kase.start_date + kase.end_date %>
                    </span>
                </div>
                <div style="margin-top:-15px; margin-bottom:-20px"><hr/></div>
            </td>
      </tr>
        <% }); %>
    </table>
</div>
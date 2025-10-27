<div id="summary_view">
	<div class="glass_header white_text" id="summary_data_holder">
    	<% if (dois!="") { %>
        	<div style="float:right; margin-top:0px; border:0px solid green;" id="float_doi_holder">
                	<table border="0" style="margin-top:0px" id="dois_summary_holder">
                    	<%=dois %>
                    </table>
                </div>
             <%      
             if (blnWCAB) { %>
            	
             <% } else { %>
             	<% if (personal_injury_date!="" && personal_injury_date=="turned_off_for_now") { %>
                	<div style="float:right; margin-top:5px; border:0px solid green">
                    	<span title="Date of Loss">DOL: <%=moment(personal_injury_date).format("MM/DD/YY") %></span>
                        <% if (personal_statute_limitation!="") { %>
                        &nbsp;|&nbsp;<span title="Statute of Limitation">SOL: <%=moment(personal_statute_limitation).format("MM/DD/YY") %></span>
                        <% } %>
                        <%
                        var settlement_border = "";
                        if (settlement_id > 0 || fee_id > 0) {
                            settlement_border = "background: black; padding:2px";
                        }
                        %>
                        &nbsp;
                        <div style="display:inline-block; <%=settlement_border %>">
                        	<a title="Click to review settlement information" id="settlement_<%=id %>" class="settlement_link" style="cursor:pointer"><i class="glyphicon glyphicon-usd" style="color:#0F9"></i></a>
                        </div>
                        
                    </div>
                <% } %>
                <%
                if (blnWCAB) {
                    if (case_number=="") {
                        case_number = file_number;
                    }
                }
                %>
                <div style="float:right; margin-top:3px; border:0px solid green" id="summary_case_number_holder">
                	<span id="summary_case_number_label">Case Number</span>: <!--<%=case_number %>--><% if (name != "") { %>&nbsp;|&nbsp;<% } %><span id="settlements_holder" style="display:none"><%=settlements %></span></div>
             <% } %>
        <% } %>
        <% if (!blnWCAB) { %>
            	<span title='Case Name' id='summary_kase_name'><%=name %></span>
                <% if (venue_abbr!="") { %>
                <span class='black_text'>&nbsp;|&nbsp;</span>
                <% } %>
            <%
        } %>
        <% if ((!blnWCAB && full_name != "" && name == "") || (blnWCAB && full_name!="")) { %>
        <a href="#applicant/<%= case_id %>" class="white_text" title="Click to review Applicant information"><%= full_name.capitalizeAllWords() %></a><% if (defendant_link.trim()!="") { %> vs <span id="defendant_link_span"><%=defendant_link %></span><% } %>
            <% if (venue_abbr!="") { %>
            <span class='black_text'>&nbsp;|&nbsp;</span>
            <% } %>
        <% } else { 
        	if (blnWCAB) { %>
        		<%=file_number %>
                <% if (venue_abbr!="") { %>
                <span class='black_text'>&nbsp;|&nbsp;</span>
                <% } %>
        <% 	}
        } %>
        <%= venue_abbr %>
        <% if (dob!="") { %>
        <span class='black_text'>&nbsp;|&nbsp;</span><span title='DOB'>DOB: <%= dob %></span>
        <% } %>
        <% if (ssn==null) {
        	ssn = "";
        }
        if (ssn.trim()!="") { %>
        <span class='black_text'>&nbsp;|&nbsp;</span><span title='SSN'>SSN: <%= ssn %></span>
        <% } %>
        <% if (attorney!="" && attorney!=null) { %>
        <span class='black_text'>&nbsp;|&nbsp;</span><span title='<%= attorney_full_name %>'>SATTY: <%= attorney_name.toUpperCase() %></span>
        <% } %>
        <% if (supervising_attorney!="" && supervising_attorney!=null) { %>
        <span class='black_text'>&nbsp;|&nbsp;</span><span title='<%= supervising_attorney_full_name %>'>ATTY: <%= supervising_attorney_name.toUpperCase() %></span>
        <% } %>
        <% if (worker!="" && worker!=null) { %>
        <span class='black_text'>&nbsp;|&nbsp;</span><span title='<%= worker_full_name %>'>COORD: <%= worker_name.toUpperCase() %></span>
        <% } %>
        <% if (case_status == "Intake") { %>
        <button id="kase_accept_<%= case_id %>" class="kase_accept btn btn-success btn-sm" style="border:0px solid; margin-left:15px;" title="Click to Accept Intake">Accept</button>
        &nbsp;
        <button id="kase_reject_<%= case_id %>" class="kase_reject btn btn-danger btn-sm" style="border:0px solid; margin-left:15px;" title="Click to Reject Intake">Reject</button>
        <button id="kase_edit_<%= case_id %>" class="kase_edit edit btn btn-primary btn-sm" style="border:0px solid; margin-left:15px;" title="Click to Edit kase information">Edit Kase</button>
        <% } else { %>
        <button id="kase_edit_<%= case_id %>" class="kase_edit edit btn btn-primary btn-sm" style="border:0px solid; margin-left:15px;" title="Click to Edit kase information">Edit Kase</button>
        <% } %>
        <% if (worker=="" && supervising_attorney=="" && attorney=="") { %>
        <div style="display: inline-block; background:red; color:white; padding:2px">PLEASE EDIT KASE TO SET KASE EMPLOYEES</div>
        <% } %>
        <div id="injury_type_warning" style="display:none; background:red; color:white; padding:2px">PLEASE EDIT KASE TO SET INJURY TYPE</div>
        <!--per thomas 9/14/2017
        &nbsp;&nbsp;&nbsp;&nbsp;Email All Parties<button id="partie_-<%= case_id %>" class="mass_email btn btn-transparent border-blue" style="border:0px solid; width:10px" title="Click to Email Everyone"><i class="glyphicon glyphicon-pencil" style="color:#00FFFF">&nbsp;</i></button>
        -->
            <% if (special_instructions!="") { %>
    	    <div id="summary_special_instructions" style="width:60%"><%= special_instructions %></div>
            <% } %>
    </div>
</div>
<div id="redflag_notes" style="display:none"></div>
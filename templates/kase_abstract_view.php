<%
var blnImm = (kase.get("case_type")=="immigration");
var case_id = kase.get('case_id');
%>
<div class="glass_header">
    <% if (blnWCAB) { %>
    <div style="float:right">
        <button id="search_qme" class="btn btn-sm btn-primary" title="Click to search the EAMS database of QME Medical Provider">Import QMEs from EAMS</button>
        
        <!--
        <div style="float:left; display:none; margin-right:10px" id="new_note_button_holder">
        	<button id="new_note_button" class="btn btn-sm btn-primary" title="Click to create a new Note">New Note</button>
        </div>
        -->

        <!-- Eams Filer link start -->
        <%
        var injury_start_date = kase.get('start_date');
        var injury_end_date = kase.get('end_date');
        var dob = kase.get('dob');
        var applicant_first_name = kase.get('first_name');
        var applicant_last_name = kase.get('last_name');
        var eams_link = 'https://staging.cajetfile.ikase.org/Main/LoadExternalCase?fname=<%=applicant_first_name %>&lname=<%=applicant_last_name %>&dob=<%=dob %>&injurystartdate=<%=injury_start_date %>&injuryenddate=<%=injury_end_date %>';
        %>

        <% if(applicant_first_name && applicant_last_name && dob && injury_start_date) { %>
        <div style="float:left; margin-right:10px" id="eams_filer_button_holder">
            <a href="<%=eams_link %>" id="eams_filer_button" class="btn btn-sm btn-primary" title="Click to go to Eams File" target="_blank">Eamsfiler</a>
        </div>
        <% } %>
        <!-- Eams Filer link end -->
        
    </div>
    <% } %>
    <div style="float:right; display:none" id="show_loss_holder">
    	<button id="show_loss" class="btn btn-sm btn-primary" title="Click to show the Losses Summary">Losses Summary</button>
    </div>
    
    <% 
    
  

    if (blnUploadDash) { %>
    <div style="float:right" id="abstract_message_attach_holder">
        <div id="message_attachments" style="width:90%">Upload</div>
    </div>
    <div style="float:right; margin-right:15px; display: none">
        <button id="compose_task" class="btn btn-sm btn-success" title="Click to add new Task">New Task</button>
    </div>
    <% } %>
    <input id="case_id" name="case_id" type="hidden" value="<%=case_id %>" />
    <input id="case_uuid" name="case_uuid" type="hidden" value="<%=kase.get('uuid') %>" />
    <div style="float:right; display:none">
        <input id="corporation_searchList" type="text" class="search-field" placeholder="Search" autocomplete="off" onkeyup="findIt(this, 'partie_listing', 'partie')">
    </div>

    <div style="display:inline-block; text-align:left; vertical-align:top">
        <span style="font-size:1.2em; color:#FFFFFF">
            <%=panel_title %>
        </span>
        <div style="display:none">
            <button title="Click for New Partie" id="new_partie" class="btn btn-sm btn-primary">
                New Partie
            </button>
        </div>
    </div>
    <div style="display:inline-block">
        <div style="border:0px solid green; text-align:left">
            <div class="white_text" style="display:inline-block; padding-left:50px">
                <div style="float:right; display:<%=kase.get("claims_display") %>">
                    <span class='black_text'>&nbsp;|&nbsp;</span><%= kase.get("claims_values") %>
                </div>
                <%= kase.get("case_number") %>
                <%
                if (kase.get("case_type")=="NewPI") {
                    kase.set("case_type", "Personal Injury");
                } 
                %>
                <% if (blnWCAB || blnImm) { %>
                <span class='black_text'>&nbsp;|&nbsp;</span><%= kase.get("adj_number") %><span class='black_text'>&nbsp;|&nbsp;</span>
                <% } %>
                <%= kase.get("case_type").replaceAll("_", " ").capitalizeWords().replace("Wcab", "WCAB") %>
                <span class='black_text'>&nbsp;|&nbsp;</span>Case&nbsp;Date:&nbsp;<%= kase.get("case_date") %><span class='black_text'>&nbsp;|&nbsp;</span>Claim&nbsp;#:&nbsp;<%= kase.get("claim_number") %>
                <br />
                <% if (kase.get("sub_in")=="Y") { %>Sub-In<span class='black_text'>&nbsp;|&nbsp;</span><% } %>
                <div style="margin-top:5px" id="abstract_status_holder">
                	Status:&nbsp;<%= kase.get("case_status") %><% if (kase.get("case_substatus")!="") { %><span class='white_text'>&nbsp;/&nbsp;</span><%= kase.get("case_substatus") %><% } %><% if (kase.get("case_subsubstatus")!="") { %><span class='white_text'>&nbsp;/&nbsp;</span><%= kase.get("case_subsubstatus") %><% } %><% if (kase.get("rating")!="") { %><span class='black_text'>&nbsp;|&nbsp;</span>Rating:&nbsp;<%=kase.get("rating") %><% } %><% if (kase.get("interpreter_needed")!="N") { %><span class='black_text'>&nbsp;|&nbsp;</span><span class="red_text white_background">Interpreter&nbsp;Needed&nbsp;for&nbsp;<%=kase.get("case_language") %></span><% } %>
                </div>
            </div>
        </div> 
    </div>
    <div id="bodyparts_warning" style="position:absolute; display:none; left:1160px; color:white; margin-top:20px; width:250px; border:1px solid orange; padding:1px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; "></div>
</div>
<div id="kase_abstract_all_done"></div>
<script language="javascript">
$( "#kase_abstract_all_done" ).trigger( "click" );
</script>
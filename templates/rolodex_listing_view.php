<div id="confirm_delete" style="display:none; position:absolute; top:20px; color:white; background:black; border:1px solid white; font-size:1.2em; padding:10px; text-align:center; z-index:1051">
	<div style="width:350px; margin-left:auto; margin-right:auto; ">
    <input type="hidden" name="confirm_delete_id" id="confirm_delete_id" value="" />
    Are you sure you want to delete this task?
    <div style="padding:5px; text-align:center"><a id="delete_partie" class="delete_yes white_icon" style="cursor:pointer">YES</a></div>
    <div style="padding:5px; text-align:center"><a id="cancel_partie" class="delete_no white_icon" style="cursor:pointer">NO</a></div>
    </div>
</div>
<div>
	<div class="glass_header">
        <div style="float:right;">
            
			<div class="btn-group">
                <select id="rolodex_typeFilter" class="modal_input" style="margin-top:-2px; width:140px">
	                <% if (contact_filter_options!="") { %>
                    <option value="">Filter by Type</option>
                    <% } %>
                    <%=contact_filter_options %>
                </select>
                
            	 <label for="rolodex_searchList" id="label_search_rolodex" style="width:120px; font-size:1em; cursor:text; position:relative; top:0px; left:125px; color:#999; margin-left:0px; margin-top:0px; border:#00FF00 0px solid;">Search Rolodex</label>
            	<input id="rolodex_searchList" type="text" class="search-field" placeholder="" autocomplete="off" onkeyup="scheduleFind(this, 'rolodex_listing', 'contact')" style="width:190px">
				<a id="rolodex_clear_search" style="position: absolute;
				right: 2px;
				top: 0px;
				bottom: 2px;
				height: 14px;
				margin: auto;
				cursor: pointer;
				border: 0px solid green;
				"><i class="glyphicon glyphicon-remove-circle" style='font-size:1em; color:#cccccc'></i></a>
			</div>
        </div>
       
        <span style="font-size:1.2em; color:#CCCCCC">Rolodex</span>&nbsp;&nbsp;&nbsp;&nbsp;
		
		<button title="new party" id="new_party" class="btn btn-sm btn-primary" style="color:white; border:0px solid;">
			New Company
		</button>
        &nbsp;&nbsp;&nbsp;&nbsp;
		<button title="new person" id="new_person" class="btn btn-sm" style="color:white; border:0px solid; background:chocolate">
			New Person
		</button>
        &nbsp;&nbsp;&nbsp;&nbsp;
        <button title="Relate two or more Entries" id="relate_roldex" class="btn btn-sm" style="color:white; border:0px solid; background:cadetblue; display:none" disabled="disabled">
			Relate Entries
		</button>
        <i class="icon-spin4 animate-spin" id="search_rolodex_loading" style="display:none; color:white"></i>
    </div><br />
    <div class="alphabet" style="width:100%">
    	<div>
            <a class="letter_click first" id="A">A</a>
            <a class="letter_click" id="B">B</a>
            <a class="letter_click" id="C">C</a>
            <a class="letter_click" id="D">D</a>
            <a class="letter_click" id="E">E</a>
            <a class="letter_click" id="F">F</a>
            <a class="letter_click" id="G">G</a>
            <a class="letter_click" id="H">H</a>
            <a class="letter_click" id="I">I</a>
            <a class="letter_click" id="J">J</a>
            <a class="letter_click" id="K">K</a>
            <a class="letter_click" id="L">L</a>
            <a class="letter_click" id="M">M</a>
            <a class="letter_click" id="N">N</a>
            <a class="letter_click" id="O">O</a>
            <a class="letter_click" id="P">P</a>
            <a class="letter_click" id="Q">Q</a>
            <a class="letter_click" id="R">R</a>
            <a class="letter_click" id="S">S</a>
            <a class="letter_click" id="T">T</a>
            <a class="letter_click" id="U">U</a>
            <a class="letter_click" id="V">V</a>
            <a class="letter_click" id="W">W</a>
            <a class="letter_click" id="X">X</a>
            <a class="letter_click" id="Y">Y</a>
            <a class="letter_click" id="Z">Z</a>
            <a class="last" id="rolodex_show_all">All</a>
     	</div>
        <div id="alphabet_instructions" style="color:white; font-size:1em; font-style:italic; width:80vw; text-align:center">
        Click letters with dark background to filter results to that letter.  Double-Click any letter to list all rolodex entries starting with that letter
        </div>
     </div>

    <table id="rolodex_listing" class="tablesorter rolodex_listing" border="0" cellpadding="0" cellspacing="0">
        <thead>
        <tr>
            <th style="width:1%">
                &nbsp;
            </th>
            <th style="font-size:1.5em; width:200px">
                Name
            </th>
            <th style="font-size:1.5em">
                Language
            </th>
            <th style="font-size:1.5em">
                Phone
            </th>
            <th style="font-size:1.5em">
                Cell
            </th>
            <th style="font-size:1.5em">
                Fax
            </th>
            <th style="font-size:1.5em">
                Website
            </th>
            <th style="font-size:1.5em">
                Email
            </th>
            <th style="font-size:1.5em">
                Type
            </th>
        </tr>
        </thead>
        <tbody>
        
       <% 
       var current_letter;
       _.each( contacts, function(contact) {
            //we might have a new letter
            var the_letter = contact.display_name.charAt(0);
            var letter_string = contact.display_name.charAt(0).valueOf();
            var contact_id = "";
            if (contact.person_id != "-1") { 
            	contact_id = "P" + contact.person_id;
            } else {
	            contact_id = "C" + contact.corporation_id;
            }
            if (current_letter != the_letter) {
                current_letter = the_letter;
				
        %>
        <tr class="letter_row <%=current_letter %>">
            <td colspan="9">
                <div style="width:100%; 
text-align:left; 
font-size:1.8em; 
background:#CFF; 
color:red;"><%= contact.first_letter %>&nbsp;&nbsp;<a class="letter_click" id="open_<%= contact.id %>_<%= the_letter %>"><i style="font-size:13px;color:#3300CC" class="glyphicon glyphicon-envelope"></i></a></div>
            </td>
        </tr>
        <% } %>
        <% 	var edit_type = "";
            var href_path = "";
            var edit_id = -1;
            if (contact.person_id != "-1") { 
               edit_type = "edit_person";
               href_path = "rolodexperson/" + contact.person_id;					   
               edit_id = contact.person_id;
           } else { 
               edit_type = "edit_partie";
               href_path = "rolodex/" + contact.corporation_id + "/" + contact.rolo_partie;
               edit_id = contact.corporation_id;
           }
        %>
       	<tr class="user_data_row user_data_row_<%=contact_id %>  row_<%=current_letter %> <%=current_letter %>">
        		<td>
                	<input type="checkbox" class="relate_box" id="relate_<%=contact_id %>" value="" title="Click to relate this Entry to another on this list" />
                </td>
                <td style="font-size:1.5em; width:630px" class="contact_name_holder" id="contact_name_holder_<%=contact_id %>">
                <% if (contact.type=="employer" || contact.type=="carrier" || contact.type=="defense"){ %>
                 <!--
                 <div style="float:right; display:none">
                 <span><a id="list_kases_link_<%=contact.corporation_id %>" title="Click to list kases associated with this company" class="list_kases parent_<%=contact.parent_uuid %>_<%=contact.type %>" style="color:white;cursor:pointer; font-size:0.7em" target="_blank" href="#kaseslist/<%=contact.corporation_id %>/<%=contact.type %>">Kases</a></span>
                 </div>
                 -->
                 <% } %>
				
                <span style="float:right">
				&nbsp;<button title="Click to edit" class="btn btn-xs <%= edit_type %>" id="<%= edit_type %>" style="cursor:pointer; <% if (contact.partie_type.indexOf("eams") > -1) { %>display:none<% } %>" onclick="editRolodex('#<%= href_path %>')">Edit</button><% if (contact.partie_type.indexOf("eams") > -1) { %><span class="white_text" title="Companies obtained from EAMS Cannot be Edited"><i class="glyphicon glyphicon-edit" style="color:red"></i></span><% } %>
                &nbsp;<a title="Click to compose a new message" class="compose_message" id="compose_message" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer"><i class="glyphicon glyphicon-pencil" style="color:#00FFFF"></i></a>
                &nbsp;
                <% if (edit_type == "edit_partie") {  %>
                	<% if (contact.partie_type.indexOf("eams") < 0) { %>
            	<a title="Click to generate an envelope" class="compose_new_envelope" id="envelope_<%= contact.rolo_partie %>_<%= contact.corporation_id %>" style="cursor:pointer">
                	<i class="glyphicon glyphicon-envelope" style="color:yellow">&nbsp;</i>
                </a><span id="feedback_<%= contact.rolo_partie %>_<%= contact.corporation_id %>"></span>
                	<% } %>
            <% } else {  %>
            	<a title="Click to generate an envelope" class="compose_new_envelope" id="envelope_applicant_<%= contact.person_id %>" style="cursor:pointer">
                	<i class="glyphicon glyphicon-envelope" style="color:yellow">&nbsp;</i>
                </a><span id="feedback_applicant_<%= contact.person_id %>"></span>
            <% } %>
            <% var corp_id = contact.corporation_id;
            if (corp_id < 0) {
            	corp_id = contact.person_id;
            }
            %>
            <% if (contact.partie_type.indexOf("eams") < 0) { %>
            &nbsp;<button onclick="window.open('#kaseslist/<%=corp_id %>/<%=contact.type %>')" class="btn btn-xs btn-primary">Kases</button>
            <% } %>
            </span>
            	<% if (contact.person_id != "-1") { 
                       var name_slot = contact.last_name.capitalizeWords() + ", " + contact.first_name.capitalizeWords(); 
                   } else { 
                       var name_slot = contact.display_name;
                   }
                %>
                
                <%= name_slot %> <% if (contact.aka!="") { %>- <%=contact.aka %><% } %>
                
                &nbsp;
                <% if (contact.person_id != "-1") { %>
                	<div style="float:right; margin-right:20px"><i style='font-size:15px;color:#FFFFFF; text-decoration:none' class='glyphicon glyphicon-user' title='Contact'></i></div>
                <% } else  { %>
					<div style="float:right; margin-right:20px"><i style='font-size:15px;color:#FFFFFF; text-decoration:none' class='glyphicon glyphicon-tower' title='Corporation'></i></div>
				<% } %>
                <br />
            <% if (contact.company_name != "" && (contact.company_name != contact.full_name && contact.full_name!="") && contact.corporation_id != "-1") { %>
            <div style="float:right; display:none">
                <span><a id="emp_kases_link_<%=contact.corporation_id %>" title="Click to list kases associated with <%=contact.full_name %>" class="list_kases parent_<%=contact.parent_uuid %>_<%=contact.type %>" style="color:white;cursor:pointer; font-size:0.7em" target="_blank" href="#kasesemplist/<%=encodeURIComponent(contact.full_name) %>/<%=contact.type %>">Linked Kases</a></span>
            </div>
            <span style="font-size:0.8em;"><strong><%=contact.full_name %></strong></span>&nbsp;&nbsp;<br />
            <% } %>
            <% if (contact.full_address != "") { %><span style="font-size:0.8em;"> <a class="white_text" href="https://www.google.com/maps/place/<%=encodeURI(contact.full_address) %>" target="_blank" title="Click to see map"><%=contact.full_address %></a></span><% } %>
                </td>
                <td style="font-size:1.5em">
					<% if (contact.language != null) { %>
						<%= contact.language %>
					<% } %>
				</td>
                <td style="font-size:1.5em">
					<% if (contact.phone != null) { %>
						<%= contact.phone %>
					<% } %>
				</td>
                <td style="font-size:1.5em">
					<% if (contact.cell_phone!= null) { %>
						<%= contact.cell_phone %>
					<% } %>
				</td>
                <td style="font-size:1.5em">
					<% if (contact.fax != null) { %>
						<%= contact.fax %>
					<% } %>
				</td>
                <td style="font-size:1.5em">
					<% if (contact.company_site != null && contact.company_site != "") { %>
						<a href="http://<%= contact.company_site %>" target="_blank" style="color:white"><%= contact.company_site %></a>
					<% } %>
				</td>
                <td style="font-size:1.5em">
					<% if (contact.email != null) { %>
						<a title="Click to compose a new email" class="compose_message" id="compose_message" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer; color:white"><%= contact.email %></a>
					<% } %>
				</td>
				<td style="font-size:1.5em" class="note_rolodex_type_cell">
                	<%=contact.partie_type.replace("_", " ") %><%=contact.eams_warning %>
                </td>
        </tr>
        
        <% }); %>
        </tbody>
    </table>
</div>

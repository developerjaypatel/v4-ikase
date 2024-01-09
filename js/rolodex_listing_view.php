<div>
	<div class="glass_header">
        <div style="float:right;">
            <input id="rolodex_searchList" type="text" class="search-field" placeholder="Search" autocomplete="off" onkeyup="findIt(this, 'rolodex_listing', 'contact')">
        </div>
       
        <span style="font-size:1.2em; color:#FFFFFF">Contacts</span>
    </div><br />
    <div class="alphabet" style="width:100%">
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
            <a class="last" id="rolodex_show_all">All</a></div>

    <table id="rolodex_listing" class="tablesorter rolodex_listing" border="0" cellpadding="0" cellspacing="0">
        <thead>
        <tr>
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
                Fax
            </th>
            <th style="font-size:1.5em">
                Website
            </th>
            <th style="font-size:1.5em">
                Email
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
            if (current_letter != the_letter) {
                current_letter = the_letter;
        %>
        <tr class="<%=current_letter %>">
            <td colspan="6">
                <div style="width:100%; 
text-align:left; 
font-size:1.8em; 
background:#CFF; 
color:red;"><%= the_letter %>&nbsp;&nbsp;<a class="letter_click" id="open_<%= contact.id %>_<%= the_letter %>" class="open_messages"><i style="font-size:13px;color:#3300CC" class="glyphicon glyphicon-envelope" title="Click to Open all Messages for this day"></i></a></div>
            </td>
        </tr>
        <% } %>
       	<tr class="user_data_row <%=current_letter %>">
                <td style="font-size:1.5em; width:400px">
				<% if (contact.person_id != "-1") { 
                       var edit_type = "edit_person";
					   var href_path = "person/contact.person_id";					   
                   } else { 
                       var edit_type = "edit_partie";
					   var href_path = "rolodex/contact.corporation_id/contact.partie_type";
                   }
                %>
                <span style="float:right">
				&nbsp;<a title="Click to edit" class="<%= edit_type %>" id="<%= edit_type %>" style="cursor:pointer" href="#<%= href_path %>"><i class="glyphicon glyphicon-edit" style="color:#0033FF"></i></a>
                &nbsp;<a title="Click to compose a new message" class="compose_message" id="compose_message" data-toggle="modal" data-target="#myModal4" style="cursor:pointer"><i class="glyphicon glyphicon-pencil" style="color:#00FFFF"></i></a>
                <a href="#" title="Click to manage documents"><i style="font-size:15px;color:#FFFFFF" class="glyphicon glyphicon-upload"></i></a>
                &nbsp;<a href="#"><i style="font-size:15px;color:#3C9" class="glyphicon glyphicon-earphone" title="Click to add phone message"></i></a>
            </span>
            	<% if (contact.person_id != "-1") { 
                       var name_slot = contact.last_name.capitalizeWords() + ", " + contact.first_name.capitalizeWords(); 
                   } else { 
                       var name_slot = contact.display_name;
                   }
                %>
                
                <a href='#rolodex/<%= contact.id %>' class="list-item_kase kase_link" style="color:#FFFFFF">
                <%= name_slot %>
                </a>
                &nbsp;
                <% if (contact.person_id != "-1") { %>
                	<div style="float:right"><i style='font-size:15px;color:#FFFFFF; text-decoration:none' class='glyphicon glyphicon-user'></i></div>
                <% } %>
                <br />
            <% if (contact.company_name != "" && contact.corporation_id != "-1") { %><span style="font-size:10px;">Name:</span> <a href='#rolodex/<%= contact.id %>' class="list-item_kase" style="color:white; font-size:10px"><%=contact.full_name %></a>&nbsp;&nbsp;<br /><% } %><% if (contact.full_address != "") { %><span style="font-size:10px;">Address:</span> <a href='#rolodex/<%= contact.id %>' class="list-item_kase" style="color:white; font-size:10px"><%=contact.full_address %></a><% } %>
                </td>
                <td style="font-size:1.5em"><%= contact.language %></td>
                <td style="font-size:1.5em"><%= contact.phone %></td>
                <td style="font-size:1.5em"><%= contact.fax %></td>
                <td style="font-size:1.5em"><%= contact.company_site %></td>
                <td style="font-size:1.5em"><%= contact.email %></td>

        </tr>
        
        <% }); %>
        </tbody>
    </table>
    <div id="pager" class="pager">
        <form>
            <img src="img/first.png" class="first"/>
            <img src="img/prev.png" class="prev"/>
            <input type="text" class="pagedisplay"/>
            <img src="img/next.png" class="next"/>
            <img src="img/last.png" class="last"/>
            <select class="pagesize">
                <option selected="selected" value="10">10</option>
                <option value="20">20</option>
                <option value="30">30</option>
                <option  value="40">40</option>
            </select>
        </form>
    </div>

</div>

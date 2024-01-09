<hr />
<div class="eams_list glass_header_no_padding" style="padding:15px">
<table cellpadding="2" class="tablesorter note_listing" border="1" style="color:white; width:550px">
	<% if (results) { %>
	<th style="font-size:1.5em; width:10%; text-align:left">ADJ</th>
    <th style="font-size:1.5em; width:10%; text-align:left">Employer</th>
    <th style="font-size:1.5em; width:10%; text-align:left">DOI</th>
    <% } %>
    <% if (!results) { %>
    <th style="font-size:1.5em; width:10%; text-align:left">&nbsp;</th>
	<th style="font-size:1.5em; width:10%; text-align:left">Name</th>
    <th style="font-size:1.5em; width:10%; text-align:left">City</th>
    <% } %>
    <%
    var intCounter = 0;
    _.each( eamss, function(eams) {%>
    <tr>
    	<% if (results) { %>
    	<td id="adj_<%=intCounter %>" nowrap>
        	<span id='adj_number_<%=intCounter %>' style='width:100px;display:inline-block; text-decoration:underline; cursor:pointer' class='kase_adj_number_fromlist'><%=eams.adj_number %></span>
            <!--
            &nbsp;|&nbsp;
        	<a id="eams_link_<%=intCounter %>" title="Click to import EAMS Injury Info" style="cursor:pointer; text-decoration:underline" class="white_text retrieve_adj_fromlist">
        		Update&nbsp;ADJ
        	</a>
            -->
            <% if (search_case_id=="") { %>
            &nbsp;|&nbsp;
            <a id="eams_import_<%=intCounter %>" title="Click to import a new Kase from EAMS" style="cursor:pointer; text-decoration:underline" class="white_text import_adj_fromlist">
        		Import
        	</a>
            <% } %>
        </td>
        <td id="employer_<%=intCounter %>" class="white_text" nowrap><%=eams.employer %></td>
        <td  class="eams_link white_text" nowrap>
        	<span style="padding:2px; background:
        	<% 
            var doi = start_date;
            if (end_date!="" && end_date!="00/00/0000") {
            	doi += " - " + end_date;
            }
            if (doi==eams.doi) { %>
            green;
            <% } %>
        	"><%=eams.doi %>
            </span>
        </td>
        <% } %>
        <% if (!results) { %>
        <td  class="eams_link white_text">
        	<a id="eams_link_<%=eams.party_id %>" title="Click to list ADJ numbers for this applicant" style="cursor:pointer; text-decoration:underline" class="white_text import_eams_fromlist">List ADJs</a>
        </td>
    	<td id="name_<%=intCounter %>"><%=eams.name %></td>
        <td id="city_<%=intCounter %>" class="white_text"><%=eams.city %></td>
        <% } %>
    </tr>
    <% intCounter++;
    }); %>
</table>
</div>
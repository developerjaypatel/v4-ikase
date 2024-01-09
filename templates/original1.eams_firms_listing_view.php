<?php
include("../api/manage_session.php");
session_write_close();
?>
<div>
	<div class="glass_header">
        <div style="float:right;">
            <input id="eams_firms_searchList" type="text" class="search-field" placeholder="Search" autocomplete="off">
        </div>
       
        <span style="font-size:1.2em; color:#FFFFFF">Search EAMS Firms</span>
    </div>
    <table id="eams_firm_listing" class="tablesorter eams_firm_listing" border="0" cellpadding="0" cellspacing="0">
        <thead>
        <tr>
            <th style="font-size:1.5em;">
                UAN Code
            </th>
            <th style="font-size:1.5em;">
                Firm
            </th>
            <th style="font-size:1.5em;">
                Address
            </th>
            <th style="font-size:1.5em;">Phone</th>
        </tr>
        </thead>
        <tbody>
       <% 
       var current_type = "";
       _.each( eams_firms, function(eams_firm) { %>
       <% if (current_type!=eams_firm.firm_type) { %>
       	<tr>
                <td colspan="4">
                	<div style="width:100%; 
text-align:left;
padding-left:5px;  
font-size:1.8em; 
background:#CFF; 
color:red;"><%= eams_firm.firm_type.capitalizeWords() %>s</div>
                </td>
        </tr>
       <% 	current_type = eams_firm.firm_type;
       } %>
       	<tr class="user_data_row user_data_row_<%= eams_firm.id %>">
                <td style="font-size:1.5em; width:110px">
                	<%= eams_firm.eams_ref_number %></a>
                </td>
                <td style="font-size:1.5em; width:400px">
                	<%= eams_firm.firm_name %></a>
                </td>
                <td style="font-size:1.5em; width:600px">
                	<%= eams_firm.full_address %>
                </td>
				<td style="font-size:1.5em;">
                	<%= eams_firm.phone %>
                </td>
        </tr>
        
        <% }); %>
        </tbody>
    </table>
</div>
<div id="eams_firms_listing_all_done"></div>
<script language="javascript">
$("#eams_firms_listing_all_done").trigger( "click" );
</script>
<div>
	<div class="glass_header">
    	<div style="float:right">
        	<% if (blnCurrentMonth) { %>
        	<button id="export_dob_emails" class="btn btn-xs btn-primary" title="Click to Export DOB Clients Emails to XL">Export DOB Emails</button>
            <% } else { %>
            <button id="export_dob_emails2" class="btn btn-xs btn-primary" title="Click to Export Next Month DOB Clients Emails to XL">Export Next Month DOB Emails</button>
            <% } %>
        </div>
		<span style="font-size:1.2em; color:#FFFFFF"><%=page_title %></span>&nbsp;<span class="white_text">(<%=dobs.length %>)</span>
        <% if (blnCurrentMonth) { %>
        &nbsp;-&nbsp;<a id="next_month_dob" style="cursor:pointer; color:white">Next Month</a>
        <% } %>
    </div>
</div>
<table id="dob_list_view" class="tablesorter dob_list_view_listing" width="100%" cellpadding="0" cellspacing="0">
	<thead>
        <tr>
            <th align="left" valign="top">Name</th>
            <th align="left" valign="top">Address</th>
            <th align="left" valign="top">Email</th>
            <th align="left" valign="top">DOB</th>
        </tr>
    </thead>
    <tbody>
		<% _.each( dobs, function(dob) { %>
        <tr>
        	<td align="left" valign="top">
            	<div style="float:right">
                	<%=dob.language %>
                </div>
            	<%=dob.full_name %>
            </td>
            <td align="left" valign="top">
            	<%=dob.full_address %>
            </td>
            <td align="left" valign="top">
            	<a href="mailto:<%=dob.email %>" class="white_text"><%=dob.email %></a>
            </td>
            <td align="left" valign="top">
            	<%=moment(dob.dob).format("MMM Do") %>
            </td>
        </tr>
        <% }); %>    	
    </tbody>
</table>
<div id="dob_list_view_done"></div>
<script language="javascript">
$( "#dob_list_view_done" ).trigger( "click" );
</script>
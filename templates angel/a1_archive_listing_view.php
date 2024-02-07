<?php
include("../api/manage_session.php");
session_write_close();
if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
	header("location:index.html");
	die();
}
?>
<div>
	<div class="glass_header">
	  <div style="width:250px">
       	  <span style="font-size:1.2em; color:#FFFFFF" id="archive_form_title">Kase A1 Archives</span> (<%=archives.length %>)
        </div>
    </div>
    <% if (archives.length == 0) { %>
    <div class="large_white_text" style="margin-top:20px">No archives.</div>
    <% } %>
    <table id="a1_archive_listing" class="tablesorter a1_archive_listing" border="0" cellpadding="0" cellspacing="0">
        <thead>
        <% if (archives.length > 0) { %>
        <tr>
        	<th align="left">
                Archive
        </th>
            <th>
                Document Date
          </th>
        </tr>
        <% } %>
        </thead>
        <tbody>
       <% _.each( archives, function(archive) { %>
       	<tr class="kase_a1_archive_data_row kase_a1_archive_row_<%=archive.act_no %>">
        	<td nowrap="nowrap" align="left">
                <a class="show_a1_archive list_link white_text" style="font-size:1.2em; cursor:pointer"><%=archive.path%></a>
            </td>
	        <td align="left">
            	<span style="font-size:1.2em;"><%= archive.document_date %></span>
            </td>
          </tr>
       	<% }); %>
        </tbody>
    </table>
</div>

<?php
// require_once('../shared/legacy_session.php');

// if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
// 	header("location:index.html");
// 	die();
// }
?>
<div>
	<div class="glass_header">
	  <div style="width:250px">
       	  <span style="font-size:1.2em; color:#FFFFFF" id="archive_form_title">Kase Archives</span>&nbsp;<span class="white_text">(<%=folders.length %>)</span>
        </div>
    </div>
    <% if (folders.length == 0) { %>
    <div class="large_white_text" style="margin-top:20px">No archives.</div>
    <% } %>
    <table id="archive_listing" class="tablesorter archive_listing" border="0" cellpadding="0" cellspacing="0">
        <thead>
        <% if (folders.length > 0) { %>
        <tr>
        	<th align="left">
                File Name
        </th>
            <th>
                Download
          </th>
        </tr>
        <% } %>
        </thead>
        <tbody>
        <% 
            var case_id = this.collection.case_id         
        %>
        <% _.each( folders, function(kase_archive) { %>
            <tr class="kase_archive_data_row kase_archive_row_<%=folders.archive_id%>">
                <td nowrap="nowrap">
                    <a  style="font-size:1.2em; color:#FFFFFF" target="_blank" href="api/merusarchivesview/<%=case_id%>/<%=encodeURIComponent(kase_archive[0])%>">
                    <%=kase_archive[0]%>
                </a>
            </td>
	        <td><a  style="font-size:1.2em; color:#FFFFFF" target="_blank" href="api/merusarchivesview/<%=case_id%>/<%=encodeURIComponent(kase_archive[0])%>">Download</a></td>
          </tr>
       	<% }); %>
        </tbody>
    </table>
</div>

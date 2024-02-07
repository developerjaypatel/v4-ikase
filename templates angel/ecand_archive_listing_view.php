<?php
include("../api/manage_session.php");

if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
	header("location:index.html");
	die();
}
?>
<div>
	<div class="glass_header">
	  <div style="width:250px">
       	  <span style="font-size:1.2em; color:#FFFFFF" id="archive_form_title">Kase Archives</span>&nbsp;<span class="white_text">(<%=kase_archives.length %>)</span>
        </div>
    </div>
    <% if (kase_archives.length == 0) { %>
    <div class="large_white_text" style="margin-top:20px">No archives.</div>
    <% } %>
    <table id="ecand_archive_listing" class="tablesorter ecand_archive_listing" border="0" cellpadding="0" cellspacing="0">
        <thead>
        <% if (kase_archives.length > 0) { %>
        <tr>
        	<th align="left">
                Archive
        </th>
            <th>
                Folder
          </th>
        </tr>
        <% } %>
        </thead>
        <tbody>
       <% _.each( kase_archives, function(kase_archive) { %>
       	<tr class="kase_archive_data_row kase_archive_row_<%=kase_archive.id%>">
        	<td nowrap="nowrap">
            	
                    <a href="http://kustomweb.xyz/ecand/get_doc.php?customer_id=<?php echo $_SESSION["user_customer_id"]; ?>&doc_id=<%=kase_archive.id %>&sess_id=<%=current_session_id %>" class="white_text" target="_blank">
                        <%=kase_archive.document_filename%>
                  </a>
            </td>
	        <td>
            	<% if( kase_archive.subfolder!="") { %>
	                <%= kase_archive.subfolder %>
                <% } %>
                &nbsp;
            </td>
          </tr>
       	<% }); %>
        </tbody>
    </table>
</div>

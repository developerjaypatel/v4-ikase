<?php
include("../api/manage_session.php");

if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
	header("location:index.html");
	die();
}
?>
<div>
	<div class="glass_header">
	  <div style="width:750px">
      	<div style="float:right">
        	<select id="archiveFilter" class="modal_input filter_select" style="margin-top:-2px;">
            </select>
            <div class="btn-group">
                <input id="archive_searchList" type="text" class="search-field" placeholder="Search Archives" autocomplete="off">
                <a id="archive_clear_search" style="position: absolute;
                right: 2px;
                top: 0;
                bottom: 2px;
                height: 14px;
                margin: auto;
                cursor: pointer;
                "><i class="glyphicon glyphicon-remove-circle" style='font-size:1em; color:#cccccc'></i></a>
            </div>
        </div>
       	  <span style="font-size:1.2em; color:#FFFFFF" id="archive_form_title">Kase Archives</span>&nbsp;<span style="color:white">(<%=kase_archives.length %>)</span>
        </div>
    </div>
    <% if (kase_archives.length == 0) { %>
    <div class="large_white_text" style="margin-top:20px">No archives.</div>
    <% } %>
    <table id="archive_listing" class="tablesorter archive_listing" border="0" cellpadding="0" cellspacing="0">
        <thead>
        <% if (kase_archives.length > 0) { %>
        <tr>
            <th align="left" width="100px">
	            Archive
            </th>        
            <th width="150px" nowrap="nowrap">
	            Document Date
            </th>
            <th width="150px">
	            Category
            </th>
            <th align="left">
    	        Description
            </th>
        </tr>
        <% } %>
        </thead>
        <tbody>
       <% _.each( kase_archives, function(kase_archive) { 
       %>
       	<tr class="kase_archive_data_row kase_archive_row_<%=kase_archive.id %>">
        	<td align="left" valign="top" nowrap="nowrap">
                <% if (kase_archive.file_extention == 'msg') { %>
                    <%=kase_archive.link%>
                <% } else { %>
                    <%=kase_archive.window_link%>
                <% } %>
            </td>
	        <td align="left" valign="top"><%= kase_archive.document_date %></td>
            <td align="left" valign="top" class="note_archive_cell" nowrap="nowrap">
            	<%= kase_archive.category %>
            </td>
            <td align="left" valign="top"><%= kase_archive.description %></td>
          </tr>
       	<% }); %>
        </tbody>
    </table>
</div>
<div id="archive_listing_all_done"></div>
<script language="javascript">
$( "#archive_listing_all_done" ).trigger( "click" );
</script>
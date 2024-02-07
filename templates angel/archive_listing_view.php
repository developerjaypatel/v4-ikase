<?php
include("../api/manage_session.php");

if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
	header("location:index.html");
	die();
}
?>
<?php 
$data_source_archives = $_SESSION["user_data_source"];
if ($data_source_archives == "goldberg3") {
	$data_source_archives = "goldberg2";
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
    <table id="archive_listing" class="tablesorter archive_listing" border="0" cellpadding="0" cellspacing="0">
        <thead>
        <% if (kase_archives.length > 0) { %>
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
       <% _.each( kase_archives, function(kase_archive) { %>
       	<% if (kase_archive.document_name=="") {
        	kase_archive.document_name = "Review Document";
        } %>
       	<tr class="kase_archive_data_row kase_archive_row_<%=kase_archive.archive_id%>">
        	<td nowrap="nowrap">
            	<% if (kase_archive.doc_number.indexOf("A")==0) {
                var archive_number = kase_archive.doc_number.replace("A", "");
                 %>
                <a href="http://kustomweb.xyz/tritek/get_archive.php?db=<?php echo $data_source_archives; ?>&recno=<%=kase_archive.recno %>&archive_number=<%=archive_number %>&case_id=<%=kase.id %>&cpointer=<%=kase.get("cpointer") %>&sess_id=<?php echo $_SESSION["user"]; ?>" class="white_text" target="_blank">
                	<%=kase_archive.document_name%>
                </a>
                <% } else { %>
                <!-- solulab code start - 17-04-2019-->
                    <a href="http://kustomweb.xyz/tritek/get_doc.php?db=<?php echo $data_source_archives; ?>&recno=<%=kase_archive.recno %>&doc=<%=kase_archive.doc_number %>&case_id=<%=kase.id %>&cpointer=<%=kase.get("cpointer") %>&sess_id=<?php echo $_SESSION["user"]; ?>" class="white_text" target="_blank">
                        <%=kase_archive.document_name%>
                    </a>
                    <!-- solulab code end - 17-04-2019-->
                	<!--<% if (customer_id == 1121) { %>
                    <a href="http://kustomweb.xyz/tritek/get_doc.php?db=<?php echo $data_source_archives; ?>&recno=<%=kase_archive.recno %>&doc=<%=kase_archive.doc_number %>&case_id=<%=kase.id %>&cpointer=<%=kase.get("cpointer") %>&sess_id=<?php echo $_SESSION["user"]; ?>" class="white_text" target="_blank">
                        <%=kase_archive.document_name%>
                    </a>
                    <% } else { %>
                    <a href="../api/read_blob.php?recno=<%=kase_archive.id %>&doc=<%=kase_archive.doc_number %>" target="_blank" class="list_link">
                        <%=kase_archive.document_name%>
                    </a>
                    <% } %>-->
                <% } %>
            </td>
	        <td><%= kase_archive.document_date %></td>
          </tr>
       	<% }); %>
        </tbody>
    </table>
</div>

<?php
include("../api/manage_session.php");
session_write_close();

if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
	header("location:index_mobile.php");
	die();
}

include("../api/connection.php");
$db = getConnection();

//see if there is a "data_source"_docs database
//lookup the customer name
$sql_customer = "SELECT data_source
FROM  `ikase`.`cse_customer` 
WHERE customer_id = :customer_id";

$stmt = $db->prepare($sql_customer);
$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
$stmt->execute();
$customer = $stmt->fetchObject();
//die(print_r($customer));
$data_source = $customer->data_source;
$blnArchives = false;
if ($data_source!="") {
	$sql = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . $data_source ."_docs'";
	//echo $sql;
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$schema = $stmt->fetchObject();
	if (is_object($schema)) {
		$blnArchives = ($schema->SCHEMA_NAME!="");
	}
}
//special cases
$blnA1 = ($_SESSION["user_customer_id"]=='1049' || $_SESSION["user_customer_id"]=='1075');

$db = null;
?>
<div id="document_list_content" style="display:none">
	<div>
    	<div style="width:250px">
        	<?php if ($blnArchives) { ?>
        	<div style="float:right">
            	<a href="#archives/<%=kase.case_id %>" style="cursor:pointer" class="white_text">Archives</a>&nbsp;<span id="archive_count"></span>
            </div>
            <?php } ?>
            <?php if ($blnA1) { ?>
            <div style="float:right">
            	<a href="#archives_legacy/<%=kase.case_id %>" style="cursor:pointer" class="white_text">Archives</a>&nbsp;<span id="archive_count"></span>
            </div>
            <?php } ?>
        	<span style="font-size:1.2em; color:#FFFFFF" id="document_form_title">Kase Documents</span>&nbsp;(<%=kase_documents.length %>)
            <!-- solulab added code 11-4-2019-->
            <% if (kase.case_id!="") { %>
        <a title="Click to add a new document" href="#documentmobile/<%=kase.case_id %>" class="add_new_document_mobile" id="add_document_<%=kase.case_id %>" style="cursor:pointer"><i class="glyphicon glyphicon-plus" style="color:#99FFFF">&nbsp;</i></a>          
        <% } %>
        <!-- solulab added code end 11-4-2019-->
        </div>
    </div>
    <div id="preview_panel" style="position:absolute; width:800px; display:none; z-index:2"></div>
    <div id="view_document" style="position:absolute; width:950px; height:600px; display:none; z-index:2; border:1px solid black; background:black">
        <div style="float:right"><a href="javascript:closeDocument()" title="Click to close preview" style="color:white; text-decoration:none">close</a></div>
    </div>
    <div id="upload_documents" style="border:0px solid yellow"></div>
    <% if (kase_documents.length == 0) { %>
    <div class="large_white_text" style="margin-top:20px">No documents.</div>
    <% } %>
    <table id="document_listing" class="tablesorter document_listing" border="0" cellpadding="0" cellspacing="0" style="width:99%">
        <thead>
        <% if (kase_documents.length > 0) { %>
        <tr>
        	<th style="width:130px">&nbsp;</th>
            <th align="left" style="width:230px">
                Document Info
            </th>
            <th style="width:100px">
                Upload Date
          </th>
        </tr>
        <% } %>
        </thead>
        <tbody>
       <% _.each( kase_documents, function(kase_document) {
       	%>
       	<tr class="kase_document_data_row kase_document_row_<%=kase_document.document_id%>">
        	<td nowrap="nowrap" style="width:130px">
              <!--  <div style="display:inline-block">
                <a name="document_save_<%=kase_document.document_id%>" id="document_save_<%=kase_document.document_id%>" class="save_icon" style="display:none; cursor:pointer" title="Click to Save"><i class="glyphicon glyphicon-saved" style="color:#00FF00">&nbsp;</i></a>
                <i class="glyphicon glyphicon-saved" style="color:#CCCCCC" id="disabled_save_<%=kase_document.document_id%>">&nbsp;</i>
                </div>
                <div style="display:inline-block">
                <a class="send_icon" id="senddocument_<%=kase_document.document_id%>" title="Click to send document" style="cursor:pointer" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false"><i class="glyphicon glyphicon-pencil" style="color:#00FFFF">&nbsp;</i></a>
                </div>
                <div style="display:inline-block">
                <a title="Click to delete Document" class="list_edit delete_document" id="deletedocument_<%=kase_document.document_id%>" onClick="javascript:composeDelete(<%=kase_document.document_id%>, 'document');" data-toggle="modal" data-target="#deleteModal" style="cursor:pointer">
					<i style="font-size:15px; color:#FF3737; cursor:pointer" id="delete_document" class="glyphicon glyphicon-trash delete_document"></i></a>
                </div><br /><br />
                
                -->
                <input id="document_id_<%=kase_document.document_id%>" name="document_id_<%=kase_document.document_id%>" type="hidden" class="document_input" value="<%=kase_document.id%>" />
                <a id="thumbnail_<%=kase_document.document_id%>" href="<%=kase_document.href %>" target="_blank" class="list_link">
                    <%=kase_document.preview %>
                </a>
            </td>
            <td align="left" valign="top" style="width:230px">
            	<div>
                <label style="width:50px">Name:</label>
            	<input id="document_name_<%=kase_document.document_id%>" name="document_name_<%=kase_document.document_id%>" type="text" class="document_input" value="<%=kase_document.document_name%>" style="width:150px" />
                </div>
                <div style="padding-top:2px">
                <label style="width:50px">Source:</label>
                <input id="document_source_<%=kase_document.document_id%>" name="document_source_<%=kase_document.document_id%>" type="text" class="document_input" value="<%=kase_document.source%>" style="width:150px" /></div>
                <div style="padding-top:2px">
                <label style="width:50px">Received:</label>
                <input id="document_received_<%=kase_document.document_id%>" name="document_received_<%=kase_document.document_id%>" type="text" class="document_input date_input" value="<%=kase_document.received_date%>" style="width:150px" />
                </div>
                <div>
                	<div style="padding-top:2px">
                    <label style="width:50px">Doc ID:</label>
                    <%=kase_document.document_id%>
                </div>
                </div>
                <div style="display:none">
                	<%=kase_document.source%> <%=kase_document.received_date%>
                </div>
    	    </td>
	        <td style="width:100px"><%= kase_document.document_date %><%= kase_document.user_name %></td>
          </tr>
       	<% }); %>
        </tbody>
    </table>
</div>
<div id="document_listing_all_done_mobile"></div>
<script language="javascript">
$( "#document_listing_all_done_mobile" ).trigger( "click" );
</script>
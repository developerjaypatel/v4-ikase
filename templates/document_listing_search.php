<?php
require_once('../shared/legacy_session.php');
session_write_close();

if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
	header("location:index.html");
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
	$stmt = DB::run($sql);
	$schema = $stmt->fetchObject();
	if (is_object($schema)) {
		$blnArchives = ($schema->SCHEMA_NAME!="");
	}
}
//special cases
$blnA1 = ($_SESSION["user_customer_id"]=='1049' || $_SESSION["user_customer_id"]=='1075');
?>
<div id="document_list_search" style="display:">
	<div class="glass_header">
    <div style="float:right; display:none">
			<select id="typeFilter" class="modal_input filter_select" style="margin-top:-2px;">
                    <option value="">Filter by Type</option>
                    <option value="Client">Client</option>
                    <option value="Carrier Document">Carrier Document</option>
                    <option value="Correspondence">Correspondence</option>
                    <option value="Defense Attorney">Defense Attorney</option>
                    <option value="Document">Document</option>
                    <option value="Employment">Employment</option>
                    <option value="Notes">Notes</option>
                    <option value="Medical">Medical</option>
      </select>
      <select id="categoryFilter" class="modal_input filter_select" style="margin-top:-2px;">
<option value="">Filter by Category</option>
                    <option value="AME Report">AME Report</option>
                    <option value="Copy Service Request">Copy Service Request</option>
                    <option value="COR">COR</option>
                    <option value="COR - C">COR - C</option>
                    <option value="COR - DA">COR - DA</option>
                    <option value="COR - IMR">COR - IMR</option>
                    <option value="COR - INS">COR - INS</option>
                    <option value="COR - UR">COR - UR</option>
                    <option value="Depo Transcript">Depo Transcript</option>
                    <option value="Email Received">Email Received</option>
                    <option value="Email Sent">Email Sent</option>
                    <option value="Fax Received">Fax Received</option>
                    <option value="Fax Sent">Fax Sent</option>
                    <option value="Fee">Fee</option>
                    <option value="Letter Received">Letter Received</option>
                    <option value="Letter Sent">Letter Sent</option>
                    <option value="Manual Entry">Manual Entry</option>
                    <option value="Medical Report">Medical Report</option>
                    <option value="Misc">Misc</option>
                    <option value="MPN">MPN</option>
                    <option value="Note">Note</option>
                    <option value="P & S Report">P & S Report</option>
                    <option value="Payment">Payment</option>
                    <option value="Pleadings">Pleadings</option>
                    <option value="PQME Report">PQME Report</option>
                    <option value="Proof Sent">Proof Sent</option>
                    <option value="Reviewed">Reviewed</option>
                    <option value="SDT Records">SDT Records</option>
                    <option value="Settlement Docs">Settlement Docs</option>
                    <option value="Telephone Call">Telephone Call</option>
            </select>
            <select id="sub_categoryFilter" class="modal_input filter_select" style="margin-top:-2px;">
                <option value="">Filter by Sub Category</option>
                <option value="doctor">Doctor</option>
                <option value="attorney">Attorney</option>
            </select>
            <div class="btn-group">
                <input id="document_searchList" type="text" class="search-field" placeholder="Search Documents" autocomplete="off">
                <a id="document_clear_search" style="position: absolute;
                right: 2px;
                top: 0;
                bottom: 2px;
                height: 14px;
                margin: auto;
                cursor: pointer;
                "><i class="glyphicon glyphicon-remove-circle" style='font-size:1em; color:#cccccc'></i></a>
            </div>
        </div>
    	<div style="width:250px">
        	<span style="font-size:1.2em; color:#FFFFFF" id="document_form_title">Found (<%=kustomer_documents.length %>) Documents</span>
        </div>
    </div>
    <div id="preview_panel" style="position:absolute; width:800px; display:none; z-index:2"></div>
    <div id="view_document" style="position:absolute; width:950px; height:600px; display:none; z-index:2; border:1px solid black; background:black">
        <div style="float:right"><a href="javascript:closeDocument()" title="Click to close preview" style="color:white; text-decoration:none">close</a></div>
    </div>
    <div id="upload_documents" style="border:0px solid yellow"></div>
    <% if (kustomer_documents.length == 0) { %>
    <div class="large_white_text" style="margin-top:20px">No documents.</div>
    <% } %>
    <table id="document_listing" class="tablesorter document_listing" border="0" cellpadding="0" cellspacing="0">
        <thead>
        <% if (kustomer_documents.length > 0) { %>
        <tr>
        	<th>&nbsp;</th>
            <th align="left">
                Document Info
            </th>
            <th>
                Upload Date
          </th>
            <th style="display:none">Actual Name</th>
            <th style="display:none">Actual Type</th>
            <th style="display:none">Actual Category</th>
            <th style="display:none">Actual Sub Category</th>
            <th>&nbsp;</th>
            <th>&nbsp;</th>
            <th>&nbsp;</th>
        </tr>
        <% } %>
        </thead>
        <tbody>
       <% _.each( kustomer_documents, function(kustomer_document) {
       		var preview = documentThumbnail(kustomer_document.document_filename, kustomer_document.customer_id, kustomer_document.thumbnail_folder, kustomer_document.case_id);
            
            if (kustomer_document.description_html.indexOf("{")==0) {
            	kustomer_document.description_html = "";
            }
       	%>
       	<tr class="kustomer_document_data_row kustomer_document_row_<%=kustomer_document.document_id%>">
        	<td nowrap="nowrap">
                <div style="display:inline-block">
                <a name="document_save_<%=kustomer_document.document_id%>" id="document_save_<%=kustomer_document.document_id%>" class="save_icon" style="display:none; cursor:pointer" title="Click to Save"><i class="glyphicon glyphicon-saved" style="color:#00FF00">&nbsp;</i></a>
                <i class="glyphicon glyphicon-saved" style="color:#CCCCCC" id="disabled_save_<%=kustomer_document.document_id%>">&nbsp;</i>
                </div>
                <div style="display:inline-block">
                <a class="send_icon" id="senddocument_<%=kustomer_document.document_id%>" title="Click to send document" style="cursor:pointer" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false"><i class="glyphicon glyphicon-pencil" style="color:#00FFFF">&nbsp;</i></a>
                </div>
                <div style="display:inline-block">
                <a title="Click to delete Document" class="list_edit delete_document" id="deletedocument_<%=kustomer_document.document_id%>" onClick="javascript:composeDelete(<%=kustomer_document.document_id%>, 'document');" data-toggle="modal" data-target="#deleteModal" style="cursor:pointer">
					<i style="font-size:15px; color:#FF3737; cursor:pointer" id="delete_document" class="glyphicon glyphicon-trash delete_document"></i></a>
                </div><br /><br />
                <input id="document_id_<%=kustomer_document.document_id%>" name="document_id_<%=kustomer_document.document_id%>" type="hidden" class="document_input" value="<%=kustomer_document.id%>" />
                <a id="thumbnail_<%=kustomer_document.document_id%>" href="D:/uploads/<?php echo $_SESSION['user_customer_id']; ?>/<%= kustomer_document.case_id %>/<%= kustomer_document.document_filename.replace("#", "%23") %>" target="_blank" class="list_link">
                    <img src="<%=preview %>" width="58" height="75" onmouseover="documentPreview(event, '<%=kustomer_document.document_filename%>', <%=kustomer_document.customer_id%>, '<%=kustomer_document.thumbnail_folder %>')" onmouseout="hidePreview()" />
                </a>
            </td>
            <td align="left" valign="top" nowrap="nowrap">
            	<div>
                	<label style="width:50px">Kase:</label><%=kustomer_document.case_number %>&nbsp;-&nbsp;<%=kustomer_document.case_name %>
                </div>
            	<div>
                <label style="width:50px">Name:</label>
            	<input id="document_name_<%=kustomer_document.document_id%>" name="document_name_<%=kustomer_document.document_id%>" type="text" class="document_input" value="<%=kustomer_document.document_name%>" style="width:300px" />
                </div>
                <div style="padding-top:2px">
                <label style="width:50px">Source:</label>
                <input id="document_source_<%=kustomer_document.document_id%>" name="document_source_<%=kustomer_document.document_id%>" type="text" class="document_input" value="<%=kustomer_document.source%>" style="width:300px" /></div>
                <div style="padding-top:2px">
                <label style="width:50px">Received:</label>
                <input id="document_received_<%=kustomer_document.document_id%>" name="document_received_<%=kustomer_document.document_id%>" type="text" class="document_input date_input" value="<%=kustomer_document.received_date.replace('00/00/0000 12:00AM', '') %>" style="width:300px" />
                </div>
                <div style="display:none">
                <%=kustomer_document.source%> <%=kustomer_document.received_date%>
                </div>
    	    </td>
	        <td><%= kustomer_document.document_date %><br /><br />By: <%= kustomer_document.user_name %></td>
            <td style="display:none"><%=kustomer_document.document_name%></td>
            <td class="note_type_cell" style="display:none"><%=kustomer_document.type%></td>
            <td class="note_category_cell" style="display:none"><%=kustomer_document.document_extension%></td>
            <td class="note_sub_category_cell" style="display:none"><%=kustomer_document.description%></td>
            <td colspan="3">
            	<table border="0" cellpadding="0" cellspacing="0">
                    <tr class="kustomer_document_row_<%=kustomer_document.document_id%>">
                      <td><select class="document_input document_type" name="document_type_<%=kustomer_document.document_id%>" id="document_type_<%=kustomer_document.document_id%>">
                        <option value="">Select Type</option>
                        <option value="Client" <% if (kustomer_document.type=="Client") { %>selected<% } %>>Client</option>
                        <option value="Carrier Document" <% if (kustomer_document.type=="Carrier Document") { %>selected<% } %>>Carrier Document</option>
                        <option value="Correspondence" <% if (kustomer_document.type=="Correspondence") { %>selected<% } %>>Correspondence</option>
                        <option value="Defense Attorney" <% if (kustomer_document.type=="Defense Attorney") { %>selected<% } %>>Defense Attorney</option>
                        <option value="Document" <% if (kustomer_document.type=="Document" || kustomer_document.type=="document" || kustomer_document.type=="") { %>selected<% } %>>Document</option>
                        <option value="Employment" <% if (kustomer_document.type=="Employment") { %>selected<% } %>>Employment</option>
                        <option value="Notes" <% if (kustomer_document.type=="Notes") { %>selected<% } %>>Notes</option>
                        <option value="Medical" <% if (kustomer_document.type=="Medical") { %>selected<% } %>>Medical</option>
                      </select></td>
                      <td><select class="document_input" name="document_category_<%=kustomer_document.document_id%>" id="document_category_<%=kustomer_document.document_id%>">
                        <option value="" <% if (kustomer_document.document_extension=="") { %>selected<% } %>>Select Category</option>
                        <option value="AME Report" <% if (kustomer_document.document_extension=="AME Report") { %>selected<% } %>>AME Report</option>
                        <option value="Copy Service Request" <% if (kustomer_document.document_extension=="Copy Service Request") { %>selected<% } %>>Copy Service Request</option>
                        <option value="COR" <% if (kustomer_document.document_extension=="COR") { %>selected<% } %>>COR</option>
                        <option value="COR - C" <% if (kustomer_document.document_extension=="COR - C") { %>selected<% } %>>COR - C</option>
                        <option value="COR - DA" <% if (kustomer_document.document_extension=="COR - DA") { %>selected<% } %>>COR - DA</option>
                        <option value="COR - IMR" <% if (kustomer_document.document_extension=="COR - IMR") { %>selected<% } %>>COR - IMR</option>
                        <option value="COR - INS" <% if (kustomer_document.document_extension=="COR - INS") { %>selected<% } %>>COR - INS</option>
                        <option value="COR - UR" <% if (kustomer_document.document_extension=="COR - UR") { %>selected<% } %>>COR - UR</option>
                        <option value="Depo Transcript" <% if (kustomer_document.document_extension=="Depo Transcript") { %>selected<% } %>>Depo Transcript</option>
                        <option value="Email Received" <% if (kustomer_document.document_extension=="Email Received") { %>selected<% } %>>Email Received</option>
                        <option value="Email Sent" <% if (kustomer_document.document_extension=="Email Sent") { %>selected<% } %>>Email Sent</option>
                        <option value="Fax Received" <% if (kustomer_document.document_extension=="Fax Received") { %>selected<% } %>>Fax Received</option>
                        <option value="Fax Sent" <% if (kustomer_document.document_extension=="Fax Sent") { %>selected<% } %>>Fax Sent</option>
                        <option value="Fee" <% if (kustomer_document.document_extension=="Fee") { %>selected<% } %>>Fee</option>
                        <option value="Letter Received" <% if (kustomer_document.document_extension=="Letter Received") { %>selected<% } %>>Letter Received</option>
                        <option value="Letter Sent" <% if (kustomer_document.document_extension=="Letter Sent") { %>selected<% } %>>Letter Sent</option>
                        <option value="Manual Entry" <% if (kustomer_document.document_extension=="Manual Entry") { %>selected<% } %>>Manual Entry</option>
                        <option value="Medical Report" <% if (kustomer_document.document_extension=="Medical Report") { %>selected<% } %>>Medical Report</option>
                        <option value="Misc" <% if (kustomer_document.document_extension=="Misc") { %>selected<% } %>>Misc</option>
                        <option value="MPN" <% if (kustomer_document.document_extension=="MPN") { %>selected<% } %>>MPN</option>
                        <option value="Note" <% if (kustomer_document.document_extension=="Note") { %>selected<% } %>>Note</option>
                        <option value="P &amp; S Report" <% if (kustomer_document.document_extension=="P & S Report") { %>selected<% } %>>P &amp; S Report</option>
                        <option value="Payment" <% if (kustomer_document.document_extension=="Payment") { %>selected<% } %>>Payment</option>
                        <option value="Pleadings" <% if (kustomer_document.document_extension=="Pleadings") { %>selected<% } %>>Pleadings</option>
                        <option value="PQME Report" <% if (kustomer_document.document_extension=="PQME Report") { %>selected<% } %>>PQME Report</option>
                        <option value="Proof Sent" <% if (kustomer_document.document_extension=="Proof Sent") { %>selected<% } %>>Proof Sent</option>
                        <option value="Reviewed" <% if (kustomer_document.document_extension=="Reviewed") { %>selected<% } %>>Reviewed</option>
                        <option value="SDT Records" <% if (kustomer_document.document_extension=="SDT Records") { %>selected<% } %>>SDT Records</option>
                        <option value="Settlement Docs" <% if (kustomer_document.document_extension=="Settlement Docs") { %>selected<% } %>>Settlement Docs</option>
                        <option value="Telephone Call" <% if (kustomer_document.document_extension=="Telephone Call") { %>selected<% } %>>Telephone Call</option>
                      </select></td>
                      <td><select class="document_input" name="document_subcategory_<%=kustomer_document.document_id%>" id="document_subcategory_<%=kustomer_document.document_id%>">
                        <option value="">Select Sub Category</option>
                        <option value="doctor" <% if (kustomer_document.description=="doctor") { %>selected<% } %>>Doctor</option>
                        <option value="attorney" <% if (kustomer_document.description=="attorney") { %>selected<% } %>>Attorney</option>
                      </select></td>
                    </tr>
                    <tr>
                      <td colspan="3">Note:<br />
                        <textarea name="document_note_<%=kustomer_document.document_id%>" id="document_note_<%=kustomer_document.document_id%>" rows="3" style="width:392px"><%=kustomer_document.description_html%></textarea></td>
                    </tr>
                </table>
            </td>
          </tr>
       	<% }); %>
        </tbody>
    </table>
</div>
<div id="document_list_search_done"></div>
<script language="javascript">
$( "#document_list_search_done" ).trigger( "click" );
</script>

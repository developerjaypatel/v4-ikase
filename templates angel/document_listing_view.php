<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);  
include("../api/manage_session.php");
session_write_close();

if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
	header("location:index.html");
	die();
}

include("../api/connection.php");
$db = getConnection();

//see if there is a "data_source"_docs database
//lookup the customer name
$sql_customer = "SELECT data_source, data_path
FROM  `ikase`.`cse_customer` 
WHERE customer_id = :customer_id";

$stmt = $db->prepare($sql_customer);
$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
$stmt->execute();
$customer = $stmt->fetchObject();
//die($customer->data_source);
$customer_name = $customer->data_source;
//die(print_r($customer));

$data_source = $customer->data_source;

if ($data_source == "goldberg3") {
			$data_source = "goldberg2";
		}

$blnA1 = ($customer->data_path == "A1");
$blnPerfect = ($customer->data_path == "perfect");
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

$blnECand = ($_SESSION["user_data_path"]=="ecand");

$arrOptions = array("types"=>array(), "categories"=>array(), "subcategories"=>array());

$sqlfilters = "SELECT * 
FROM ikase.cse_customer_document_filters 
WHERE customer_id = :customer_id";
$stmt = $db->prepare($sqlfilters);
$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
$stmt->execute();
$filter = $stmt->fetchObject();
if ($_SERVER['REMOTE_ADDR'] == "47.156.103.17") {
	//ldie(print_r($filter));
}
$filter = json_decode($filter->document_filters);
$filter_types = $filter->types;
$filter_categories = $filter->categories;
$filter_subcategories = $filter->subcategories;

foreach($filter_types as $filter_type) {
	if (strpos($filter_type, "|deleted")!==false) {
		continue;
	}
	$filter_name = $filter_type;
	if (strpos($filter_type, "COR") === false) {
		$filter_name = ucwords(strtolower($filter_type));
	}
	$filter_name = str_replace("Mpn", "MPN", $filter_name);
	$filter_name = str_replace("Ame Report", "AME Report", $filter_name);
	$filter_name = str_replace("SDT RECORDS", "SDT Records", $filter_name);
	
	$filter_display_name = strtoupper($filter_type);
	$option = '<option value="' . trim($filter_name) . '">' . trim($filter_display_name) . '</option>';
	$arrOptions["types"][] = $option;
}

foreach($filter_categories as $filter_category) {
	$filter_name = $filter_category;
	if (strpos($filter_category, "EAMS") === false) {
		$filter_name = ucwords(strtolower($filter_category));
	}
	$option = '<option value="' . trim($filter_name) . '">' . trim($filter_name) . '</option>';
	$arrOptions["categories"][] = $option;
}

foreach($filter_subcategories as $filter_subcategory) {
	$filter_name = $filter_subcategory;
	//if (strpos($filter_subcategory, "EAMS") === false) {
		$filter_name = ucwords(strtolower($filter_subcategory));
	//}
	$option = '<option value="' . trim($filter_name) . '">' . trim($filter_name) . '</option>';
	$arrOptions["subcategories"][] = $option;
}

if (strpos($_SESSION['user_role'], "admin") !== false) {
	$option = '<option style="font-size: 1pt; background-color: #000000;" disabled>&nbsp;</option>
	<option value="new_filter" style="background:lime">Manage List</option>';
	if (count($arrOptions["types"]) > 0) {
		$arrOptions["types"][] = $option;
	}
	if (count($arrOptions["categories"]) > 0) {
		$arrOptions["categories"][] = $option;
	}
	if (count($arrOptions["subcategories"]) > 0) {
		$arrOptions["subcategories"][] = $option;
	}
}
//die(print_r($arrOptions));

$select_types = implode("\r\n", $arrOptions["types"]);
$select_categories = implode("\r\n", $arrOptions["categories"]);
$select_subcategories = implode("\r\n", $arrOptions["subcategories"]);


$blnDeletePermission = true;
if ($_SESSION["user_customer_id"]==1075) {
	//per steve g 4/3/2017
	$blnDeletePermission = false;
	if (strpos($_SESSION['user_role'], "admin") !== false) {
		$blnDeletePermission = true;
	}
}

$db = null;
?>
<div style="background:#FCF; color:black; padding:10px; font-size:1.1em; margin-bottom:10px; display:none" id="4906_announce">
    <div style="float:right; background:black;; padding:2px">
	    <a href="../jetfiler/pdf/4906h.pdf" target="_blank" style=" color:white">Download 4906H Form</a>
    </div>
    <p>FROM EAMS 2/13/2019  -RE: <strong>4906G instead of 4906H</strong></p>
    <p>You  can use the document title 4906G, but name the actual document 4906H.
    Or,  you can wait until the case is assigned, and submit the 4906h as an  unstructured document.</p>
    <p> We  are working to correct this issue, so the above will work for now.</p>
</div>
<div id="confirm_delete" style="display:none; position:absolute; top:20px; color:white; background:black; border:1px solid white; font-size:1.2em; padding:10px; text-align:center; z-index:1051">
	<div style="width:350px; margin-left:auto; margin-right:auto; ">
    <input type="hidden" name="confirm_delete_id" id="confirm_delete_id" value="" />
    Are you sure you want to delete this document?
    <div style="padding:5px; text-align:center"><a id="delete_partie" class="delete_yes white_icon" style="cursor:pointer">YES</a></div>
    <div style="padding:5px; text-align:center"><a id="cancel_partie" class="delete_no white_icon" style="cursor:pointer">NO</a></div>
    </div>
</div>
<div id="kase_abstract_holder" style="margin-bottom:10px; display:none"></div>
<div id="document_list_content" style="display:none">
	<div class="glass_header" style="height:45px; margin-bottom:10px">
    
    <div style="float:right;">
			<% if (customer_id == 1033) { %>
        
            <select name="mass_change" id="mass_change" style="width:225px; display:none; margin-right:400px">
              <option value="" selected="selected">Choose Action</option>
              <option value="send_this">Send</option>
              <option value="bill_this" disabled="disabled">Bill</option>
            </select>
        
    <% } %>
      	<?php if ($blnA1 && !$blnPerfect && count($arrOptions["types"]) == 0) { ?>
        <select id="typeFilter" class="modal_input filter_select" style="margin-top:-2px;">
            <option value="">Filter by Type</option>
            <option value="132A_SW">132A &amp; S&amp;W</option>
            <option value="third_party referrals">3rd Party Referrals</option>
            <option value="AME Report">AME Report</option>
            <option value="AME/QME Prep">AME/QME Prep</option>
            <option value="Calendar - Notes and Orange Slips">Calendar - Notes and Orange Slips</option>
            <option value="Correspondence and Emails">Correspondence and Emails</option>
            <option value="Cross X Summary">Cross X Summary</option>
            <option value="Defense Meds">Defense Meds</option>
            <option value="Depo Trans">Depo Trans</option>
            <option value="Fax Confirmation">Fax Confirmation</option>
            <option value="General">General</option>
            <option value="HomeCare">HomeCare</option>
            <option value="Internal">Internal</option>
            <option value="Legal">Legal</option>
            <option value="Liens">Liens</option>
            <option value="Misc. App. Meds">Misc. App. Meds</option>
            <option value="Monthly Status">Monthly Status</option>
            <option value="MPN Correspondence">MPN Correspondence</option>
            <option value="Neuro">Neuro</option>
            <option value="Ortho">Ortho</option>
            <option value="Out of Pocket/Transportation">Out of Pocket/Transportation</option>
            <option value="POA/Attorney Meeting">POA/Attorney Meeting</option>
            <option value="Psych">Psych</option>
            <option value="QME Objection Request (Correspondence Only)">QME Objection Request (Correspondence Only)</option>
            <option value="SDT Records">SDT Records</option>
            <option value="Settlement/Calls">Settlement/Calls</option>
            <option value="QME OBJ/REQ">QME OBJ/REQ</option>
            <option value="Rating Chart">Rating Chart</option>
            <option value="UEF Docs">UEF Docs</option>
            <option value="UR/IMR">UR/IMR</option>
            <option value="Vocational Rehab Expert">Vocational Rehab Expert</option>
            <option value="W2 Forms / Earnings">W2 Forms / Earnings</option>
        </select>
		<?php } else { ?>
        	
        	<?php 	if (count($arrOptions["types"]) > 0) { ?>
            		<select id="typeFilter" class="modal_input filter_select" style="margin-top:-2px;">
					<option value="">Filter by Types</option>
					<?php echo $select_types; ?>
                    </select>
                    
			<?php
			 		} else { ?>
        <select id="typeFilter" class="modal_input filter_select" style="margin-top:-2px;">
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
            <option value="POA/Attorney Meeting">POA/Attorney Meeting</option>
            <option value="PQME Report">PQME Report</option>
            <option value="Proof Sent">Proof Sent</option>
            <option value="Rating Chart">Rating Chart</option>
            <option value="Reviewed">Reviewed</option>
            <option value="SDT Records">SDT Records</option>
            <option value="Settlement Docs">Settlement Docs</option>
            <option value="Telephone Call">Telephone Call</option>
	    </select>
	        <?php } ?>
        <?php } ?>
        <?php if (count($arrOptions["categories"]) > 0) { ?>
            <select id="categoryFilter" class="modal_input filter_select" style="margin-top:-2px;">
            	<option value="">Filter by Category</option>
                <?php echo $select_categories; ?>
            </select>
        <?php } else { ?>
            <select id="categoryFilter" class="modal_input filter_select" style="margin-top:-2px;">
                        <option value="">Filter by Category</option>
                        <option value="Client">Client</option>
                        <option value="Carrier Document">Carrier Document</option>
                        <option value="Correspondence">Correspondence</option>
                        <option value="Defense Attorney">Defense Attorney</option>
                        <option value="Document">Document</option>
                        <option value="Employment">Employment</option>
                        <option value="EAMS Form">EAMS Form</option>
                        <option value="Notes">Notes</option>
                        <option value="Medical">Medical</option>
            </select>
        <?php } ?>
        <?php if (count($arrOptions["subcategories"]) > 0) { ?>
            <select id="sub_categoryFilter" class="modal_input filter_select" style="margin-top:-2px;">
		        <option value="">Filter by Sub Category</option>
                <?php echo implode("\r\n", $arrOptions["subcategories"]); ?>
            </select>
        <?php } else { ?>
            <select id="sub_categoryFilter" class="modal_input filter_select" style="margin-top:-2px;">
                <option value="">Filter by Sub Category</option>
                <option value="doctor">Doctor</option>
                <option value="attorney">Attorney</option>
            </select>
        <?php } ?>
            <div class="btn-group">
            	
            	 <label for="document_searchList" id="label_search_docs" style="width:130px; font-size:1em; cursor:text; position:relative; top:0px; left:138px; color:#999; margin-left:50px; margin-top:0px; border:#00FF00 0px solid;">Search Documents</label>
            	
                <input id="document_searchList" type="text" class="search-field" placeholder="" autocomplete="off" style="width:150px; height:25px">
                <a id="document_clear_search" style="position: absolute;
                right: 2px;
                top: 0;
                bottom: 7px;
                height: 14px;
                margin: auto;
                cursor: pointer;
                "><i class="glyphicon glyphicon-remove-circle" style='font-size:1em; color:#cccccc'></i></a>
            </div>
        </div>
    	<div style="width:300px">
        	<?php if ($blnArchives) { ?>
        	<div style="float:right; margin-top:4px">
            	<a href="#archives/<%=kase.case_id %>" style="cursor:pointer" class="white_text">Archives</a>&nbsp;<div id="archive_count" style="position: relative;left: 60px; padding-left:3px; margin-top:-25px; color:white; font-size:0.8em"></div>
            </div>
            <?php } ?>
            <?php if ($blnA1 || $blnPerfect) { ?>
            <div style="float:right" id="archive_legacy_holder">
            	<a href="#archives_legacy/<%=kase.case_id %>" style="cursor:pointer" class="white_text">Archives Available</a>&nbsp;<span id="archive_count" style=" color:white; font-size:0.8em"></span>
            </div>
            <?php } ?>
            <?php if ($blnA1 && $_SESSION["user_customer_id"]==1117) { ?>
            <div style="float:right" id="archive_a1_legacy_holder">
            	<a href="#archives_a1_folders/<%=kase.case_id %>" style="cursor:pointer" class="white_text">A1 Archives</a>&nbsp;<span id="a1_archive_count" style=" color:white; font-size:0.8em"></span>
            </div>
            <?php } ?>
            <?php if ($blnECand) { ?>
            <div style="float:right" id="archive_a1_legacy_holder">
            	<a href="#archives_ecand/<%=kase.case_id %>" style="cursor:pointer" class="white_text">Archives</a>&nbsp;<span id="archive_count" style=" color:white; font-size:0.8em"></span>
            </div>
            <?php } ?>
        	<span style="font-size:1.2em; color:#FFFFFF" id="document_form_title">Kase Documents</span>&nbsp;<div style="position: relative;left: 135px; padding-left:3px; margin-top:-25px; color:white; font-size:0.8em">(<%=kase_documents.length %>)</div>
        </div>
    </div>
    <div id="preview_panel" style="position:absolute; width:800px; display:none; z-index:2"></div>
    <div id="view_document" style="position:absolute; width:950px; height:600px; display:none; z-index:2; border:1px solid black; background:black">
        <div style="float:right"><a href="javascript:closeDocument()" title="Click to close preview" style="color:white; text-decoration:none">close</a></div>
    </div>
    <div id="upload_documents" style="border:0px solid yellow"></div>
    <% if (kase_documents.length == 0) {
    	if (customer_id == 1104) { %>
        <div class="large_white_text" style="margin-top:20px">Search for Documents by Name</div>
        <% } else { %>
    	<div class="large_white_text" style="margin-top:20px">No Documents</div>
    <% 	}
    } %>
    <div id="document_right_pane" style="float:right; display:none; margin-right:10px">
    	<iframe id="document_preview_holder" width="100%" allowtransparency="1" frameborder="0" scrolling="no"></iframe>
    </div>
    <div id="document_listing_holder" style="overflow-y:scroll;">
    <table id="document_listing" class="tablesorter document_listing" border="0" cellpadding="0" cellspacing="0">
        <thead>
        <% if (kase_documents.length > 0) { %>
        <tr>
        	<th>
            	<div style="float:right;" id="send_this_holder">
                	<button class="btn btn-primary btn-xs" id="send_documents" title="Click to Send checked Documents via Email/Interoffice" disabled="disabled">Send Checked</button>
                </div>
            	<i class="glyphicon glyphicon-pencil" style="color:#00FFFF; display:none" id="senddocuments_link">&nbsp;</i>
               <input type="checkbox" id="select_all_documents" title="Click to Select All Documents to Send" /> Select All
            </th>
            <th align="left">
                Document Info
            </th>
            <th>
                Upload&nbsp;Date
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
       <% 
       _.each( kase_documents, function(kase_document) {
       	%>
       	<tr class="kase_document_data_row kase_document_row_<%=kase_document.document_id%>" style="display:">
        	<td nowrap="nowrap">
            <input type="checkbox" style="margin-right:10px" id="check_assign_<%=kase_document.document_id %>" class="check_thisone check_thisone_<%=kase_document.document_id %>" title="Check to select for Sending"  /><a name="document_bill_<%=kase_document.document_id%>" id="document_bill_<%=kase_document.document_id%>" class="bill_icon" style="cursor:pointer" title="Bill this"><i class="glyphicon glyphicon-time" style="color:#F90">&nbsp;</i></a>
                <div style="display:inline-block">
                    <a name="document_save_<%=kase_document.document_id%>" id="document_save_<%=kase_document.document_id%>" class="save_icon" style="display:none; cursor:pointer" title="Click to Save"><i class="glyphicon glyphicon-saved" style="color:#00FF00">&nbsp;</i></a>
                    <i class="glyphicon glyphicon-saved" style="color:#CCCCCC" id="disabled_save_<%=kase_document.document_id%>">&nbsp;</i>
                    &nbsp;<%=kase_document.download_link %>
                </div>
                <div style="display:inline-block">
                	<a class="send_icon" id="senddocument_<%=kase_document.document_id%>" title="Click to send document" style="cursor:pointer" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false"><i class="glyphicon glyphicon-pencil" style="color:#00FFFF">&nbsp;</i></a>
                </div>
                <div style="display:inline-block">
                	<?php //per steve at dordulian 3/31/32017
						if ($blnDeletePermission) { ?>
                	<a title="Click to delete Document" class="list_edit delete_document" id="deletedocument_<%=kase_document.document_id%>" onClick="javascript:composeDelete(<%=kase_document.document_id%>, 'document');" data-toggle="modal" data-target="#deleteModal" style="cursor:pointer">
					<i style="font-size:15px; color:#FF3737; cursor:pointer" id="delete_document" class="glyphicon glyphicon-trash delete_document"></i></a>
                    <?php } ?>
                </div>
                <br /><br />
                <input id="document_id_<%=kase_document.document_id%>" name="document_id_<%=kase_document.document_id%>" type="hidden" class="document_input" value="<%=kase_document.id%>" />
                <input id="document_filename_<%=kase_document.document_id%>" type="hidden" value="<%=kase_document.document_filename %>" />
                <a id="thumbnail_<%=kase_document.document_id%>" class="list_link" title="Click to preview document" style="cursor:pointer">
                    <%=kase_document.preview %>
                </a>
                <div id="window_link_<%=kase_document.document_id%>" class="window_link_holder" style="display:none; margin-top:5px">
                	<a id="window_thumbnail_<%=kase_document.document_id%>" class="window_link white_text" title="Click to open document in new window" style="cursor:pointer">
                        Open in new window
                    </a>
                </div>
                <input type="hidden" id="preview_document_<%=kase_document.document_id%>" value="<%=kase_document.preview_href %>" />
            </td>
            <td align="left" valign="top" nowrap="nowrap">
            	<div>
                    <label style="width:50px">Name:</label>
                    <input id="document_name_<%=kase_document.document_id%>" name="document_name_<%=kase_document.document_id%>" type="text" class="document_input" value="<%=kase_document.document_name%>" style="width:300px" />
                </div>
                <div style="padding-top:2px">
                    <label style="width:50px">Source:</label>
                    <input id="document_source_<%=kase_document.document_id%>" name="document_source_<%=kase_document.document_id%>" type="text" class="document_input" value="<%=kase_document.source%>" style="width:300px" />
                </div>
                <div style="padding-top:2px">
                    <label style="width:50px">Received:</label>
                    <input id="document_received_<%=kase_document.document_id%>" name="document_received_<%=kase_document.document_id%>" type="text" class="document_input date_input" value="<%=kase_document.received_date%>" style="width:300px" />
                </div>
                <div style="padding-top:2px">
                    <label style="width:50px">DOI:</label>
                    <select id="doi_id_<%=kase_document.document_id %>" style="width:300px; height:28px" class="doi_id document_input"></select>
                </div>
                <div>
                	<div style="padding-top:2px">
                        <label style="width:50px">Doc ID:</label>
                        <%=kase_document.document_id%>&nbsp;|&nbsp;<a href="<%=kase_document.preview_href %>" target="_blank" title="Click to open document in a new window" class="white_text" style="background:orange; font-weight:bold; padding:2px; color:black">Preview in Browser</a>&nbsp;|&nbsp;<a href="<%=kase_document.preview_href %>&download="  title="Click to download document to your PC" class="white_text" style="background:cyan; font-weight:bold; padding:2px; color:black" target="_blank">Download to PC</a>
	                </div>
                </div>
                <div style="display:none">
                <%=kase_document.source%> <%=kase_document.received_date%>
                </div>
    	    </td>
	        <td>
            	<%= kase_document.document_date %>
				<?php if ($_SESSION["user_customer_id"] == 1121) { ?>
                	<% if (kase_document.document_id == "131189") { %>
                    	<br/><br/>Import Assigned by JOANA REYES
                    <% } else { %>
                    	<%= kase_document.user_name %>
                    <% } %>
				<?php } else { ?>
                	<%= kase_document.user_name %>
				<?php } ?>
            </td>
            <td style="display:none"><%=kase_document.document_name%></td>
            <td class="note_type_cell" style="display:none"><%=kase_document.type%></td>
            <td class="note_category_cell" style="display:none"><%=kase_document.document_extension%></td>
            <td class="note_sub_category_cell" style="display:none"><%=kase_document.description%></td>
            <td colspan="3">
            	<table border="0" cellpadding="0" cellspacing="0">
                    <tr class="kase_document_row_<%=kase_document.document_id%>">
                      <td>
                      <?php if ($_SESSION["user_customer_id"]=='1049') { ?>
                        <select class="document_input document_type" name="document_type_<%=kase_document.document_id%>" id="document_type_<%=kase_document.document_id%>">
                        	<option value="" <% if (kase_document.type=="") { %>selected<% } %>>Select Type</option>
                            <option value="132A_SW" <% if (kase_document.type=="132A_SW") { %>selected<% } %>>132A &amp; S&amp;W</option>
                            <option value="third_party referrals" <% if (kase_document.type=="third_party referrals") { %>selected<% } %>>3rd Party Referrals</option>
                            <option value="AME Report" <% if (kase_document.type=="AME Report") { %>selected<% } %>>AME Report</option>
                            <option value="AME/QME Prep" <% if (kase_document.type=="AME/QME Prep") { %>selected<% } %>>AME/QME Prep</option>
                            <option value="Calendar - Notes and Orange Slips" <% if (kase_document.type=="Calendar - Notes and Orange Slips") { %>selected<% } %>>Calendar - Notes and Orange Slips</option>
                            <option value="Copy Service Request" <% if (kase_document.type=="Copy Service Request") { %>selected<% } %>>Copy Service Request</option>
                            <option value="Correspondence and Emails" <% if (kase_document.type=="Correspondence and Emails") { %>selected<% } %>>Correspondence and Emails</option>
                            <option value="Cross X Summary" <% if (kase_document.type=="Cross X Summary") { %>selected<% } %>>Cross X Summary</option>
                            <option value="Defense Meds" <% if (kase_document.type=="Defense Meds") { %>selected<% } %>>Defense Meds</option>
                            <option value="Depo Trans" <% if (kase_document.type=="Depo Trans") { %>selected<% } %>>Depo Trans</option>
                            <option value="Fax Confirmation" <% if (kase_document.type=="Fax Confirmation") { %>selected<% } %>>Fax Confirmation</option>
                            <option value="General" <% if (kase_document.type=="General") { %>selected<% } %>>General</option>
                            <option value="HomeCare" <% if (kase_document.type=="HomeCare") { %>selected<% } %>>HomeCare</option>
                            <option value="Internal" <% if (kase_document.type=="Internal") { %>selected<% } %>>Internal</option>
                            <option value="Legal" <% if (kase_document.type=="Legal") { %>selected<% } %>>Legal</option>
                            <option value="Liens" <% if (kase_document.type=="Liens") { %>selected<% } %>>Liens</option>
                            <option value="Misc. App. Meds" <% if (kase_document.type=="Misc. App. Meds") { %>selected<% } %>>Misc. App. Meds</option>
                            <option value="Monthly Status" <% if (kase_document.type=="Monthly Status") { %>selected<% } %>>Monthly Status</option>
                            <option value="MPN Correspondence" <% if (kase_document.type=="MPN Correspondence") { %>selected<% } %>>MPN Correspondence</option>
                            <option value="Neuro" <% if (kase_document.type=="Neuro") { %>selected<% } %>>Neur</option>
                            <option value="Ortho" <% if (kase_document.type=="Ortho") { %>selected<% } %>>Ortho</option>
                            <option value="Out of Pocket/Transportation" <% if (kase_document.type=="Out of Pocket/Transportation") { %>selected<% } %>>Out of Pocket/Transportation</option>
                            <option value="POA/Attorney Meeting" <% if (kase_document.type=="POA/Attorney Meeting") { %>selected<% } %>>POA/Attorney Meeting</option>
                            <option value="Proof of Services" <% if (kase_document.type=="Proof of Services") { %>selected<% } %>>Proof of Services</option>
                            <option value="Psych" <% if (kase_document.type=="Psych") { %>selected<% } %>>Psych</option>
                            <option value="QME Objection Request (Correspondence Only)" <% if (kase_document.type=="QME Objection Request (Correspondence Only)") { %>selected<% } %>>QME Objection Request (Correspondence Only)</option>
                            <option value="Rating Chart" <% if (kase_document.type=="Rating Chart") { %>selected<% } %>>Rating Chart</option>
                            <option value="Scanned Mail" <% if (kase_document.type=="Scanned Mail") { %>selected<% } %>>Scanned Mail</option>
                            <option value="SDT Records" <% if (kase_document.type=="SDT Records") { %>selected<% } %>>SDT Records</option>
                            <option value="Settlement/Calls" <% if (kase_document.type=="Settlement/Calls") { %>selected<% } %>>Settlement/Calls</option>
                             <option value="QME OBJ/REQ" <% if (kase_document.type=="QME OBJ/REQ") { %>selected<% } %>>QME OBJ/REQ</option>
                            <option value="UEF Docs" <% if (kase_document.type=="UEF Docs") { %>selected<% } %>>UEF Docs</option>
                            <option value="UR/IMR" <% if (kase_document.type=="UR/IMR") { %>selected<% } %>>UR/IMR</option>
                            <option value="Vocational Rehab Expert" <% if (kase_document.type=="Vocational Rehab Expert") { %>selected<% } %>>Vocational Rehab Expert</option>
                            <option value="W2 Forms / Earnings" <% if (kase_document.type=="W2 Forms / Earnings") { %>selected<% } %>>W2 Forms / Earnings</option>
                        </select>
                        <?php } else {
								
								if (count($arrOptions["types"]) > 0 && $customer_name != "goldberg2") { ?>
                                	<select class="document_input document_type doc" name="document_type_<%=kase_document.document_id%>" id="document_type_<%=kase_document.document_id%>">
                                    <option value="">Select Type</option>
									<?php 
									echo $select_types; ?>
                                    </select>
                                    
                                    <% if (customer_id == "1121") { //console.log(kase_documents.type); //alert("here"); %>
            	<select class="document_input document_type" name="document_type_<%=kase_document.document_id%>" id="document_type_<%=kase_document.document_id%>">
                              <option value="" <% if (kase_document.type=="" && kase_document.type!="unassigned") { %>selected<% } %>>Select Type</option>
                              <option style="display:none" disabled="disabled" value="AME Report" <% if (kase_document.type=="AME Report") { %>selected<% } %>>AME Report</option>
                              <option style="display:none" disabled="disabled" value="Copy Service Request" <% if (kase_document.type=="Copy Service Request") { %>selected<% } %>>Copy Service Request</option>
                              <option style="display:none" disabled="disabled" value="COR" <% if (kase_document.type=="COR") { %>selected<% } %>>COR</option>
                              <option style="display:none" disabled="disabled" value="COR - C" <% if (kase_document.type=="COR - C") { %>selected<% } %>>COR - C</option>
                              <option value="COR - DA" <% if (kase_document.type=="COR - DA") { %>selected<% } %>>COR - DA</option>
                              <option value="COR - CLT" <% if (kase_document.type=="COR - CLT") { %>selected<% } %>>COR - CLT</option>
                              <option style="display:none" disabled="disabled" value="COR - IMR" <% if (kase_document.type=="COR - IMR") { %>selected<% } %>>COR - IMR</option>
                              <option value="COR - INS" <% if (kase_document.type=="COR - INS") { %>selected<% } %>>COR - INS</option>
                              <option value="COR - UR" <% if (kase_document.type=="COR - UR") { %>selected<% } %>>COR - UR</option>
                              <option value="COR - LIEN" <% if (kase_document.type=="COR - LIEN") { %>selected<% } %>>COR - LIEN</option>
                              <option value="Depo Transcript" <% if (kase_document.type=="Depo Transcript") { %>selected<% } %>>Depo Transcript</option>
                              <option value="Fee Received" <% if (kase_document.type=="Fee Received") { %>selected<% } %>>Fee Received</option>
                              <option value="Email Received" <% if (kase_document.type=="Email Received") { %>selected<% } %>>Email Received</option>
                              <option value="Email Sent" <% if (kase_document.type=="Email Sent") { %>selected<% } %>>Email Sent</option>
                              <option style="display:none" disabled="disabled" value="Fax Received" <% if (kase_document.type=="Fax Received") { %>selected<% } %>>Fax Received</option>
                              <option style="display:none" disabled="disabled" value="Fax Sent" <% if (kase_document.type=="Fax Sent") { %>selected<% } %>>Fax Sent</option>
                              <option style="display:none" disabled="disabled" value="Fee" <% if (kase_document.type=="Fee") { %>selected<% } %>>Fee</option>
                              <option style="display:none" disabled="disabled" value="Letter Received" <% if (kase_document.type=="Letter Received") { %>selected<% } %>>Letter Received</option>
                              <option value="Letter Sent" <% if (kase_document.type=="Letter Sent") { %>selected<% } %>>Letter Sent</option>
                              <option style="display:none" disabled="disabled" value="Manual Entry" <% if (kase_document.type=="Manual Entry") { %>selected<% } %>>Manual Entry</option>
                              <option value="Medical Report" <% if (kase_document.type=="Medical Report") { %>selected<% } %>>Medical Report</option>
                              <option value="Medical" <% if (kase_document.type=="Medical") { %>selected<% } %>>Medical</option>
                              <option style="display:none" disabled="disabled" value="Misc" <% if (kase_document.type=="Misc") { %>selected<% } %>>Misc</option>
                              <option style="display:none" disabled="disabled" value="MPN" <% if (kase_document.type=="MPN") { %>selected<% } %>>MPN</option>
                              <option style="display:none" disabled="disabled" value="Note" <% if (kase_document.type=="Note") { %>selected<% } %>>Note</option>
                              <option style="display:none" disabled="disabled" value="P &amp; S Report" <% if (kase_document.type=="P & S Report") { %>selected<% } %>>P &amp; S Report</option>
                              <option style="display:none" disabled="disabled" value="Payment" <% if (kase_document.type=="Payment") { %>selected<% } %>>Payment</option>
                              <option value="Pleadings" <% if (kase_document.type=="Pleadings") { %>selected<% } %>>Pleadings</option>
                              <option style="display:none" disabled="disabled" value="POA/Attorney Meeting" <% if (kase_document.type=="POA/Attorney Meeting") { %>selected<% } %>>POA/Attorney Meeting</option>
                              <option style="display:none" disabled="disabled" value="PQME Report" <% if (kase_document.type=="PQME Report") { %>selected<% } %>>PQME Report</option>
                              <option style="display:none" disabled="disabled" value="Proof Sent" <% if (kase_document.type=="Proof Sent") { %>selected<% } %>>Proof Sent</option>
                              <option style="display:none" disabled="disabled" value="Reviewed" <% if (kase_document.type=="Reviewed") { %>selected<% } %>>Reviewed</option>
                              <option style="display:none" disabled="disabled" value="Scanned Mail" <% if (kase_document.type=="Scanned Mail" || kase_document.type=="unassigned") { %>selected<% } %>>Scanned Mail</option>
                              <option value="SDT Records" <% if (kase_document.type=="SDT Records") { %>selected<% } %>>SDT Records</option>
                              <option value="WCAB" <% if (kase_document.type=="WCAB") { %>selected<% } %>>WCAB</option>
                              <option value="APPT Notice" <% if (kase_document.type=="APPT Notice") { %>selected<% } %>>APPT Notice</option>
                              <option value="Pleadings" <% if (kase_document.type=="Pleadings") { %>selected<% } %>>Pleadings</option>
                              <option value="Settlement Docs" <% if (kase_document.type=="Settlement Docs") { %>selected<% } %>>Settlement Docs</option>
                              <option style="display:none" disabled="disabled" value="Telephone Call" <% if (kase_document.type=="Telephone Call") { %>selected<% } %>>Telephone Call</option>
                            </select>
            <% } %>
                                    <?php
								} else { ?>
                                
                      <select class="document_input document_type" name="document_type_<%=kase_document.document_id%>" id="document_type_<%=kase_document.document_id%>">
                        <option value="" <% if (kase_document.type=="") { %>selected<% } %>>Select Type</option>
                        <option value="AME Report" <% if (kase_document.type=="AME Report") { %>selected<% } %>>AME Report</option>
                        <option value="Copy Service Request" <% if (kase_document.type=="Copy Service Request") { %>selected<% } %>>Copy Service Request</option>
                        <option value="COR" <% if (kase_document.type=="COR") { %>selected<% } %>>COR</option>
                        <option value="COR - C" <% if (kase_document.type=="COR - C") { %>selected<% } %>>COR - C</option>
                        <option value="COR - DA" <% if (kase_document.type=="COR - DA") { %>selected<% } %>>COR - DA</option>
                        <option value="COR - IMR" <% if (kase_document.type=="COR - IMR") { %>selected<% } %>>COR - IMR</option>
                        <option value="COR - INS" <% if (kase_document.type=="COR - INS") { %>selected<% } %>>COR - INS</option>
                        <option value="COR - UR" <% if (kase_document.type=="COR - UR") { %>selected<% } %>>COR - UR</option>
                        <option value="Depo Transcript" <% if (kase_document.type=="Depo Transcript") { %>selected<% } %>>Depo Transcript</option>
                        <option value="Email Received" <% if (kase_document.type=="Email Received") { %>selected<% } %>>Email Received</option>
                        <option value="Email Sent" <% if (kase_document.type=="Email Sent") { %>selected<% } %>>Email Sent</option>
                        <option value="Fax Received" <% if (kase_document.type=="Fax Received") { %>selected<% } %>>Fax Received</option>
                        <option value="Fax Sent" <% if (kase_document.type=="Fax Sent") { %>selected<% } %>>Fax Sent</option>
                        <option value="Fee" <% if (kase_document.type=="Fee") { %>selected<% } %>>Fee</option>
                        <option value="Letter Received" <% if (kase_document.type=="Letter Received") { %>selected<% } %>>Letter Received</option>
                        <option value="Letter Sent" <% if (kase_document.type=="Letter Sent") { %>selected<% } %>>Letter Sent</option>
                        <option value="Manual Entry" <% if (kase_document.type=="Manual Entry") { %>selected<% } %>>Manual Entry</option>
                        <option value="Medical Report" <% if (kase_document.type=="Medical Report") { %>selected<% } %>>Medical Report</option>
                        <option value="Misc" <% if (kase_document.type=="Misc") { %>selected<% } %>>Misc</option>
                        <option value="MPN" <% if (kase_document.type=="MPN") { %>selected<% } %>>MPN</option>
                        <option value="Monthly Status" <% if (kase_document.type=="Monthly Status") { %>selected<% } %>>Monthly Status</option>
                        <option value="Note" <% if (kase_document.type=="Note") { %>selected<% } %>>Note</option>
                        <option value="P &amp; S Report" <% if (kase_document.type=="P & S Report") { %>selected<% } %>>P &amp; S Report</option>
                        <option value="Payment" <% if (kase_document.type=="Payment") { %>selected<% } %>>Payment</option>
                        <option value="Pleadings" <% if (kase_document.type=="Pleadings") { %>selected<% } %>>Pleadings</option>
                        <option value="POA/Attorney Meeting" <% if (kase_document.type=="POA/Attorney Meeting") { %>selected<% } %>>POA/Attorney Meeting</option>
                        <option value="PQME Report" <% if (kase_document.type=="PQME Report") { %>selected<% } %>>PQME Report</option>
                        <option value="Proof Sent" <% if (kase_document.type=="Proof Sent") { %>selected<% } %>>Proof Sent</option>
                        <option value="Rating Chart" <% if (kase_document.type=="Rating Chart") { %>selected<% } %>>Rating Chart</option>
                        <option value="Reviewed" <% if (kase_document.type=="Reviewed") { %>selected<% } %>>Reviewed</option>
                        <option value="Scanned Mail" <% if (kase_document.type=="Scanned Mail") { %>selected<% } %>>Scanned Mail</option>
                        <option value="SDT Records" <% if (kase_document.type=="SDT Records") { %>selected<% } %>>SDT Records</option>
                        <option value="Settlement Docs" <% if (kase_document.type=="Settlement Docs") { %>selected<% } %>>Settlement Docs</option>
                        <option value="Telephone Call" <% if (kase_document.type=="Telephone Call") { %>selected<% } %>>Telephone Call</option>
                      </select>
                      
						<?php }
						} ?>
                      </td>
                      <td>
                      <?php if (count($arrOptions["types"]) > 0) { ?>
                                <select class="document_input" name="document_category_<%=kase_document.document_id%>" id="document_category_<%=kase_document.document_id%>">
                                <option value="">Select Category</option>
                                <?php 
                                echo $select_categories; ?>
                                </select>
                                <?php
                            } else { ?>
                      <select class="document_input" name="document_category_<%=kase_document.document_id%>" id="document_category_<%=kase_document.document_id%>">
                        <option value="">Select Category</option>
                        <option value="Client" <% if (kase_document.document_extension=="Client") { %>selected<% } %>>Client</option>
                        <option value="Carrier Document" <% if (kase_document.document_extension=="Carrier Document") { %>selected<% } %>>Carrier Document</option>
                        <option value="Correspondence" <% if (kase_document.document_extension=="Correspondence") { %>selected<% } %>>Correspondence</option>
                        <option value="Defense Attorney" <% if (kase_document.document_extension=="Defense Attorney") { %>selected<% } %>>Defense Attorney</option>
                        <option value="Document" <% if (kase_document.document_extension=="Document" || kase_document.document_extension=="document" || kase_document.document_extension=="") { %>selected<% } %>>Document</option>
                        <option value="Employment" <% if (kase_document.document_extension=="Employment") { %>selected<% } %>>Employment</option>
                        <option value="Notes" <% if (kase_document.document_extension=="Notes") { %>selected<% } %>>Notes</option>
                        <option value="Medical" <% if (kase_document.document_extension=="Medical") { %>selected<% } %>>Medical</option>
                      </select>
                      <?php } ?>
                      </td>
                      <td>
                      <?php if (count($arrOptions["subcategories"]) > 0) { ?>
                            <select class="document_input" name="document_subcategory_<%=kase_document.document_id%>" id="document_subcategory_<%=kase_document.document_id%>">
                        <option value="">Select Sub Category</option>
                            <?php 
                            echo $select_subcategories; ?>
                            </select>
                            <?php
                        } else { ?>
                      <select class="document_input" name="document_subcategory_<%=kase_document.document_id%>" id="document_subcategory_<%=kase_document.document_id%>">
                        <option value="">Select Sub Category</option>
                        <option value="doctor" <% if (kase_document.description=="doctor") { %>selected<% } %>>Doctor</option>
                        <option value="attorney" <% if (kase_document.description=="attorney") { %>selected<% } %>>Attorney</option>
                      </select>
                      <?php } ?>
                      </td>
                    </tr>
                    <tr>
                      <td colspan="3">
                      	<label id="document_note_label_<%=kase_document.document_id%>">Note</label>:<br />
                        <textarea name="document_note_<%=kase_document.document_id%>" id="document_note_<%=kase_document.document_id%>" class="document_input" rows="3" style="width:392px"><%=kase_document.description_html%></textarea>
                        <div style="display:block" class="document_additional_medindex" id="document_additional_medindex_<%=kase_document.document_id%>">
                        	<% if (kase_document.exam_uuid=="") { %>
                            <input type="checkbox" id="document_medindex_<%=kase_document.document_id%>" class="document_input document_medindex" value="Y" />&nbsp;
                            <label for="document_medindex_<%=kase_document.document_id%>">Apply to Med Index?</label>                    	
                            <% } else { %>
                            <input type="hidden" id="document_medindex_<%=kase_document.document_id%>" class="document_input document_medindex" value="Y" />
                            &nbsp;
                            <label for="document_medindex_<%=kase_document.document_id%>" style="background:yellow; color:black">Med Index Document</label>                    	
                            <% } %>
                        </div>
                      </td>
                    </tr>
                </table>
            </td>
          </tr>
       	<% }); %>
        </tbody>
    </table>
    </div>
</div>
<div id="document_listing_all_done"></div>
<script language="javascript">
$( "#document_listing_all_done" ).trigger( "click" );
if (customer_id == "1121") { 
	$(".doc").hide();
}
</script>

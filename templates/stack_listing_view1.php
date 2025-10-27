<?php
require_once('../shared/legacy_session.php');
session_write_close();

include("../api/connection.php");
$db = getConnection();

//see if there is a "data_source"_docs database
//lookup the customer name
/* $sql_customer = "SELECT data_source, data_path
FROM  `cse_customer` 
WHERE customer_id = :customer_id";

$stmt = $db->prepare($sql_customer);
$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
$stmt->execute();
$customer = $stmt->fetchObject();
//die(print_r($customer));
$data_source = $customer->data_source;

$blnA1 = ($customer->data_path == "A1");
$blnPerfect = ($customer->data_path == "perfect");
$blnMerus = ($customer->data_path == "merus");
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

$blnECand = ($_SESSION["user_data_path"]=="ecand"); */

$arrOptions = array("types"=>array(), "categories"=>array(), "subcategories"=>array());

$sqlfilters = "SELECT * 
FROM cse_customer_document_filters 
WHERE customer_id = :customer_id";
$stmt = $db->prepare($sqlfilters);
$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
$stmt->execute();
$filter = $stmt->fetchObject();

$filter = json_decode($filter->document_filters);
$filter_types = $filter->types;
$filter_categories = $filter->categories;
$filter_subcategories = $filter->subcategories;
$arrOptions["types"] = [];
foreach($filter_types as $filter_type) {
	if (strpos($filter_type, "|deleted")!==false || strpos($filter_type, "jk|deleted")==false) {
		continue;
	}
	$filter_name = $filter_type;
	if (strpos($filter_type, "COR") === false) {
		$filter_name = ucwords(strtolower($filter_type));
	}
	$filter_name = str_replace("Mpn", "MPN", $filter_name);
	$filter_name = str_replace("Ame Report", "AME Report", $filter_name);
	$filter_name = str_replace("SDT RECORDS", "SDT Records", $filter_name);
	
    
    $display_name = strtoupper($filter_type);
    $jsCompare = addslashes(trim($filter_name));      // for comparing inside stack.type=="..."
    $valueAttr = htmlspecialchars(trim($filter_name)); // for HTML value
    $text = htmlspecialchars(trim($display_name));

    // special-case "Scanned Mail" to also consider 'unassigned'
    if (strcasecmp(trim($filter_name), 'Scanned Mail') === 0) {
        $option = '<option value="' . $valueAttr . '" <% if (stack.type=="' . $jsCompare . '" || stack.type=="unassigned") { %>selected<% } %>>' . $text . '</option>';
    } else {
        $option = '<option value="' . $valueAttr . '" <% if (stack.type=="' . $jsCompare . '") { %>selected<% } %>>' . $text . '</option>';
    }

    $arrOptions["types"][] = $option;
}

/* foreach($filter_categories as $filter_category) {
	$filter_name = $filter_category;
	if (strpos($filter_category, "EAMS") === false) {
		$filter_name = ucwords(strtolower($filter_category));
	}
	$option = '<option value="' . trim($filter_name) . '">' . trim($filter_name) . '</option>';
	$arrOptions["categories"][] = $option;
} */

$arrOptions["categories"] = [];

/* foreach ($filter_categories as $filter_category) {
    // Normalize the display name
    $filter_name = $filter_category;
    if (strpos($filter_category, "EAMS") === false) {
        $filter_name = ucwords(strtolower($filter_category));
    }

    // Escape safely for HTML/EJS embedding
    $valueAttr = htmlspecialchars(trim($filter_name));
    $jsCompare = addslashes(trim($filter_name)); // used inside stack.document_extension==...

    // Special handling for “Document” option (to match your original multiple conditions)
    if (strcasecmp(trim($filter_name), 'Document') === 0) {
        $option = '<option value="' . $valueAttr . '" <% if (stack.document_extension=="' . $jsCompare . '" || stack.document_extension=="document" || stack.document_extension=="" || stack.document_extension=="unassigned" || stack.type=="unassigned") { %>selected<% } %>>' . $valueAttr . '</option>';
    } else {
        $option = '<option value="' . $valueAttr . '" <% if (stack.document_extension=="' . $jsCompare . '") { %>selected<% } %>>' . $valueAttr . '</option>';
    }

    $arrOptions["categories"][] = $option;
} */

/* foreach($filter_subcategories as $filter_subcategory) {
	$filter_name = $filter_subcategory;
	//if (strpos($filter_subcategory, "EAMS") === false) {
		$filter_name = ucwords(strtolower($filter_subcategory));
	//}
	$option = '<option value="' . trim($filter_name) . '">' . trim($filter_name) . '</option>';
	$arrOptions["subcategories"][] = $option;
} */

$arrOptions["subcategories"] = [];

foreach ($filter_subcategories as $filter_subcategory) {
    // Format display name
    $filter_name = ucwords(strtolower($filter_subcategory));

    // Escape for HTML + EJS safely
    $valueAttr = htmlspecialchars(trim($filter_name));
    $jsCompare = addslashes(strtolower(trim($filter_name))); // compare lowercase like stack.description=="doctor"

    // Build EJS-aware <option>
    $option = '<option value="' . strtolower($valueAttr) . '" <% if (stack.description=="' . $jsCompare . '") { %>selected<% } %>>' . $valueAttr . '</option>';
    
    $arrOptions["subcategories"][] = $option;
} 


/* if (strpos($_SESSION['user_role'], "admin") !== false) {
	//$option = '<option style="font-size: 1pt; background-color: #000000;" disabled>&nbsp;</option>
	//<option value="new_filter" style="background:lime">Manage List</option>';
	if (count($arrOptions["types"]) > 0) {
		$arrOptions["types"][] = $option;
	}
	if (count($arrOptions["categories"]) > 0) {
		$arrOptions["categories"][] = $option;
	}
	if (count($arrOptions["subcategories"]) > 0) {
		$arrOptions["subcategories"][] = $option;
	}
} */
//die(print_r($arrOptions));

$select_types = implode("\r\n", $arrOptions["types"]);
$select_categories = implode("\r\n", $arrOptions["categories"]);
$select_subcategories = implode("\r\n", $arrOptions["subcategories"]);

//special cases
$blnGlauber = ($_SESSION["user_customer_id"]=='1049');
?>
<div>
	<div class="glass_header">
    	<div style="float:right">
        	<div class="btn-group">
            
            	 <label for="import_searchList" id="label_search_imports" style="width:120px; font-size:1em; cursor:text; position:relative; top:0px; left:125px; color:#999; margin-left:0px; margin-top:0px; border:#00FF00 0px solid;">Search Imports</label>
            	
				<input id="import_searchList" type="text" class="search-field" placeholder="" autocomplete="off" onkeyup="findIt(this, 'stack_listing', 'stack')">
				<a id="import_clear_search" style="position: absolute;
				right: 2px;
				top: 0;
				bottom: 2px;
				height: 14px;
				margin: auto;
				cursor: pointer;
				"><i class="glyphicon glyphicon-remove-circle" style='font-size:1em; color:#cccccc'></i></a>
			</div>
        </div>
        <span style="font-size:1.2em; color:#FFFFFF"><%=form_title %></span>&nbsp;<span class="white_text">(<%=stacks.length %>)</span>&nbsp;&nbsp;
        <select name="mass_change" id="mass_change" class="kase_input_select" style="width:200px; visibility:hidden">
        	<option value="" selected="selected">Choose Action</option>
            <option value="assign_to_kase">Assign</option>
            <option value="mark_completed">Mark as Completed</option>
            <option value="delete_import">Delete</option>
        </select>
    </div>
    <div id="preview_panel" style="position:absolute; width:800px; display:none; z-index:2"></div>
  <div id="view_document" style="position:absolute; width:950px; height:600px; display:none; z-index:2; border:1px solid black; background:black">
        <div style="float:right"><a href="javascript:closeDocument()" title="Click to close preview" style="color:white; text-decoration:none">close</a></div>
    </div>
    
    <table id="stack_listing" class="tablesorter stack_listing" border="1" cellpadding="0" cellspacing="0">
        <thead>
        <tr>
        	<th>
            	<input type="checkbox" id="check_all_assign" class="check_all"  />
            </th>
            <th>
                Scan
            </th>
            <th>
                Info
            </th>
            <th>
                Document
            </th>
            <th>
                Kase
            </th>
            <th>Type</th>
            <th>Category</th>
            <th>Sub Category</th>
        </tr>
        </thead>
        <tbody>
       <% var current_indicator = "";
       var current_day = '';
       var current_status = '';
       _.each( stacks, function(stack) {
       		current_status = stack.read_status.replace(" ", "");
            
       		//we might have a new day
            var the_day = moment(stack.document_date).format("MMDDYY");
            var date_string = moment(stack.document_date).valueOf();
            if (current_day != the_day) {
                current_day = the_day;
            
            %>
            <tr class="date_row row_<%= the_day %>">
                <td colspan="8">
                	<div style="width:100%; 
                        text-align:left; 
                        font-size:1.8em; 
                        background:#CFF; 
                        color:red;">
                        <?php if ($_SERVER['REMOTE_ADDR']=='47.153.50.152') { ?>
                        <div style="float:right">
                            <button class="btn btn-xs btn-primary select_day" id="select_day_<%= the_day %>">Select Day</button>
                            <button class="btn btn-xs btn-success confirm_action" id="confirm_action_<%= the_day %>" style="display:none">Confirm Selection</button>
                        </div>
                        <?php } ?>
                        <%= moment(stack.document_date).format("dddd, MMMM Do YYYY") %>
                     </div>
                </td>
            </tr>
            <% } %>
       		<% //we might have a new indicator
	        if (stack.separator) {
        	%>
        	<tr class="stack_status_row row_<%=current_status %>">
                <td colspan="8">
                    <div style="width:100%; 
                    text-align:left; 
                    font-size:1.8em; 
                    background:#CFF; 
                    color:red;">
                    	<?php if ($_SERVER['REMOTE_ADDR']=='47.153.50.152') { ?>
                        <div style="float:right">
                            <button class="btn btn-xs select_status select_section" id="select_status_<%=current_status %>">Select Section</button>
                            &nbsp;&nbsp;
                            <button class="btn btn-xs btn-primary select_attached select_section" id="select_attached_<%=current_status %>">Select Scans Assigned to Cases</button>
                            <button class="btn btn-xs btn-success confirm_action" id="confirm_action_<%=current_status %>" style="display:none">Confirm Selection</button>
                        </div>
                        <?php } ?>
                    	<%= stack.read_status.capitalizeWords() %>
                    </div>
                </td>
            </tr>
        	<% } %>
       	<tr class="stack_data_row document_row_<%=stack.document_id%> row_<%=current_status %>">
            <td align="left" valign="top">
            <input type="hidden" id="document_message_id_<%=stack.document_id %>" value="<%=stack.document_id %>" />
            <input type="checkbox" id="check_assign_<%=stack.document_id%>" class="check_thisone check_thisone_<%= the_day %> check_thisone_<%=current_status %>"  />
            </td>
            <td align="left" valign="top">
        <%=stack.preview %>
                <input type="hidden" id="preview_document_<%=stack.document_id%>" value="<%=stack.preview_href %>" />
        	</td>
	        <td align="left" valign="top">
            	<%= stack.document_date + "<br />" + stack.type %>
                <%=stack.notified_by %>
                
                <div class="notification_list_holder" style="display:none">
					<button class="btn btn-xs btn-primary notification_history" id="notification_history_<%=stack.document_id %>" role="button">Notification History</button>
                </div>
                <div style="position:relative; z-index:9999; display:none" id="notifications_list_<%=stack.document_id %>"></div>
            </td>
            <td align="left" valign="top">
           	  <label style="width:55px">Name:</label>
              <input type="text" id="stack_name_<%=stack.document_id%>" value="<%=stack.thumb_description %>" class="stack_data" style="width:290px" />&nbsp;<a class="send_icon" id="sendstack_<%=stack.document_id%>" title="Click to send this scan via interoffice" style="cursor:pointer" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false"><i class="glyphicon glyphicon-pencil" style="color:#00FFFF">&nbsp;</i></a>&nbsp;<span id="notify_attorney_<%=stack.document_id%>" class="notify_attorney" style="display:<%=stack.notify_attorney %>;"></span>
                <div style="padding-top:2px">
                    <label style="width:55px">Source:</label>
                    <input id="document_source_<%=stack.document_id%>" name="document_source_<%=stack.document_id%>" type="text" class="document_input" value="<%=stack.source %>" style="width:220px" />
                </div>
                <div style="padding-top:2px">
                    <label style="width:55px">Received:</label>
                    <input id="document_received_<%=stack.document_id%>" name="document_received_<%=stack.document_id%>" type="text" class="document_input date_input" value="<%=stack.received_date %>" style="width:220px" />
                </div>
                <% if (stack.uploaded_by != '') { %>
                <div style="padding-top:2px">
                    <label style="width:60px">Uploaded</label>&nbsp;
                    <%=stack.uploaded_by %>
              </div>
        <% } %>
                
                <div>
                    <div style="padding-top:2px">
                    	<% //per steve at dordulian 3/31/32017
                        if (customer_id!=1075) { %>
                    	<div style="float:right">
                        	<a title="Click to delete Scan" class="list_edit delete_document" id="deletedocument_<%=stack.document_id%>" onClick="javascript:composeDelete(<%=stack.document_id%>, 'document');" data-toggle="modal" data-target="#deleteModal" style="cursor:pointer">
					<i style="font-size:15px; color:#FF3737; cursor:pointer" id="delete_document" class="glyphicon glyphicon-trash delete_document"></i></a>
                        </div>
                        <% } %>
                        <label style="width:55px">Doc ID:</label>
                        <%=stack.document_id%>&nbsp;&nbsp;<%=stack.read_indicator %>
	                </div>
                </div>
                <div style="display:none">
                <%=stack.source%> <%=stack.received_date%>
                </div>
            </td>
            <td align="left" valign="top">
           	  <div>
              		<div style="display:inline-block">
                    	<a name="stack_save_<%=stack.id %>" id="stack_save_<%=stack.document_id %>" class="save_icon" style="display:none; cursor:pointer" title="Click to Save"><i class="glyphicon glyphicon-saved" style="color:#00FF00">&nbsp;</i></a>
            			<i class="glyphicon glyphicon-saved" style="color:#CCCCCC" id="disabled_stack_save_<%=stack.document_id %>">&nbsp;</i>
                    </div>
                    
                    <div style="display:inline-block">
                        <input type="hidden" id="stack_case_id_<%=stack.document_id%>" value="<%=stack.case_id %>" />
                        <input type="text" id="stack_case_<%=stack.document_id%>" class="kase_input" placeholder="Type here to assign scan to a kase" value="<%=stack.case_name %>" /><%=stack.case_link %>
                    </div>
                    <div style="margin-left:30px; display:none" class="stack_additional_medindex" id="stack_additional_medindex_<%=stack.document_id%>">
                        <input type="checkbox" id="stack_medindex_<%=stack.document_id%>" class="stack_input stack_medindex" value="Y" />&nbsp;
                        <label for="stack_medindex_<%=stack.document_id%>">Apply to Med Index?</label>                    	
                    </div>
                    <div class="stack_input" id="stack_notify_holder_<%=stack.document_id%>">
                    	Notify:<br />
                        <input class="stack_input stack_notify" type="text" name="stack_notify_<%=stack.document_id%>" id="stack_notify_<%=stack.document_id%>" style="width:362px" />
                        <div id="notify_employees_<%=stack.document_id%>"></div>
                        <div id="instructions_holder_<%=stack.document_id%>" style="display:none">
                        	<textarea id="notify_instructions_<%=stack.document_id%>" class="notify_message" rows="3" style="width:510px" placeholder="Notification message to assignees"></textarea>
                        </div>
                    </div>
                </div>
                <% if (stack.instructions!="") { %>
                <div style="background:black; width:510px" id="stack_instructions_holder_<%=stack.document_id%>">
                	<span style="font-weight:bold">INSTRUCTIONS:</span>&nbsp;<%=stack.instructions %>
                </div>
                <% } %>
            </td>
            <td colspan="3" align="left" valign="top">
            <table cellpadding="2" cellspacing="0" id="stack_info_holder_<%=stack.document_id%>" style="display:none">
              <tbody>
                <tr>
                  <td colspan="3" align="left" valign="top" style="font-weight:bold">Document Information</td>
                </tr>
                <tr>
                  <td align="left" valign="top">
                    <?php if ($blnGlauber) { ?>
                    <select class="stack_input stack_type" name="stack_type_<%=stack.document_id%>" id="stack_type_<%=stack.document_id%>">
                      <option value="" <% if (stack.type=="" && stack.type!="unassigned") { %>selected<% } %>>Select Type</option>
                      <option value="132A_SW" <% if (stack.type=="132A_SW") { %>selected<% } %>>132A &amp; S&amp;W</option>
                            <option value="third_party referrals" <% if (stack.type=="third_party referrals") { %>selected<% } %>>3rd Party Referrals</option>
                            <option value="AME Report" <% if (stack.type=="AME Report") { %>selected<% } %>>AME Report</option>
                            <option value="AME/QME Prep" <% if (stack.type=="AME/QME Prep") { %>selected<% } %>>AME/QME Prep</option>
                            <option value="Calendar - Notes and Orange Slips" <% if (stack.type=="Calendar - Notes and Orange Slips") { %>selected<% } %>>Calendar - Notes and Orange Slips</option>
                            <option value="Copy Service Request" <% if (stack.type=="Copy Service Request") { %>selected<% } %>>Copy Service Request</option>
                            <option value="Correspondence and Emails" <% if (stack.type=="Correspondence and Emails") { %>selected<% } %>>Correspondence and Emails</option>
                            <option value="Cross X Summary" <% if (stack.type=="Cross X Summary") { %>selected<% } %>>Cross X Summary</option>
                            <option value="Defense Meds" <% if (stack.type=="Defense Meds") { %>selected<% } %>>Defense Meds</option>
                            <option value="Depo Trans" <% if (stack.type=="Depo Trans") { %>selected<% } %>>Depo Trans</option>
                            <option value="Fax Confirmation" <% if (stack.type=="Fax Confirmation") { %>selected<% } %>>Fax Confirmation</option>
                            <option value="General" <% if (stack.type=="General") { %>selected<% } %>>General</option>
                            <option value="HomeCare" <% if (stack.type=="HomeCare") { %>selected<% } %>>HomeCare</option>
                            <option value="Internal" <% if (stack.type=="Internal") { %>selected<% } %>>Internal</option>
                            <option value="Legal" <% if (stack.type=="Legal") { %>selected<% } %>>Legal</option>
                            <option value="Liens" <% if (stack.type=="Liens") { %>selected<% } %>>Liens</option>
                            <option value="Misc. App. Meds" <% if (stack.type=="Misc. App. Meds") { %>selected<% } %>>Misc. App. Meds</option>
                            <option value="MPN Correspondence" <% if (stack.type=="MPN Correspondence") { %>selected<% } %>>MPN Correspondence</option>
                            <option value="Neuro" <% if (stack.type=="Neuro") { %>selected<% } %>>Neur</option>
                            <option value="Ortho" <% if (stack.type=="Ortho") { %>selected<% } %>>Ortho</option>
                            <option value="Out of Pocket/Transportation" <% if (stack.type=="Out of Pocket/Transportation") { %>selected<% } %>>Out of Pocket/Transportation</option>
                            <option value="POA/Attorney Meeting" <% if (stack.type=="POA/Attorney Meeting") { %>selected<% } %>>POA/Attorney Meeting</option>
                            <option value="Proof of Services" <% if (stack.type=="Proof of Services") { %>selected<% } %>>Proof of Services</option>
                            <option value="Psych" <% if (stack.type=="Psych") { %>selected<% } %>>Psych</option>
                            <option value="QME Objection Request (Correspondence Only)" <% if (stack.type=="QME Objection Request (Correspondence Only)") { %>selected<% } %>>QME Objection Request (Correspondence Only)</option>
                            <option value="Rating Chart" <% if (stack.type=="Rating Chart") { %>selected<% } %>>Rating Chart</option>
                            <option value="Scanned Mail" <% if (stack.type=="Scanned Mail") { %>selected<% } %>>Scanned Mail</option>
                            <option value="SDT Records" <% if (stack.type=="SDT Records") { %>selected<% } %>>SDT Records</option>
                            <option value="Settlement/Calls" <% if (stack.type=="Settlement/Calls") { %>selected<% } %>>Settlement/Calls</option>
                             <option value="QME OBJ/REQ" <% if (stack.type=="QME OBJ/REQ") { %>selected<% } %>>QME OBJ/REQ</option>
                            <option value="UEF Docs" <% if (stack.type=="UEF Docs") { %>selected<% } %>>UEF Docs</option>
                            <option value="UR/IMR" <% if (stack.type=="UR/IMR") { %>selected<% } %>>UR/IMR</option>
                            <option value="Vocational Rehab Expert" <% if (stack.type=="Vocational Rehab Expert") { %>selected<% } %>>Vocational Rehab Expert</option>
                            <option value="W2 Forms / Earnings" <% if (stack.type=="W2 Forms / Earnings") { %>selected<% } %>>W2 Forms / Earnings</option>
                    </select>
                    <?php } else { ?>
                       <?php 
                       if (count($arrOptions["types"]) > 0) {
                    $firstOption = '<option value="" <% if (stack.type=="" && stack.type!="unassigned") { %>selected<% } %>>Select Type</option>';

                    // compose the full select (use your stack.document_id where appropriate in your template)
                    $select_html  = '<select class="stack_input stack_type" name="stack_type_<%=stack.document_id%>" id="stack_type_<%=stack.document_id%>">' . "\n";
                    $select_html .= $firstOption . "\n";
                    $select_html .= implode("\n", $arrOptions["types"]) . "\n";
                    $select_html .= '</select>';

                    // echo the HTML into the page where your EJS template can process stack.type
                    echo $select_html;

                       }else{
                        ?>
                    <select class="stack_input stack_type" name="stack_type_<%=stack.document_id%>2" id="stack_type_<%=stack.document_id%>">
                      <option value="" <% if (stack.type=="" && stack.type!="unassigned") { %>selected<% } %>>Select Type</option>
                      <option value="AME Report" <% if (stack.type=="AME Report") { %>selected<% } %>>AME Report</option>
                      <option value="Copy Service Request" <% if (stack.type=="Copy Service Request") { %>selected<% } %>>Copy Service Request</option>
                      <option value="COR" <% if (stack.type=="COR") { %>selected<% } %>>COR</option>
                      <option value="COR - C" <% if (stack.type=="COR - C") { %>selected<% } %>>COR - C</option>
                      <option value="COR - DA" <% if (stack.type=="COR - DA") { %>selected<% } %>>COR - DA</option>
                      <option value="COR - IMR" <% if (stack.type=="COR - IMR") { %>selected<% } %>>COR - IMR</option>
                      <option value="COR - INS" <% if (stack.type=="COR - INS") { %>selected<% } %>>COR - INS</option>
                      <option value="COR - UR" <% if (stack.type=="COR - UR") { %>selected<% } %>>COR - UR</option>
                      <option value="Depo Transcript" <% if (stack.type=="Depo Transcript") { %>selected<% } %>>Depo Transcript</option>
                      <option value="Email Received" <% if (stack.type=="Email Received") { %>selected<% } %>>Email Received</option>
                      <option value="Email Sent" <% if (stack.type=="Email Sent") { %>selected<% } %>>Email Sent</option>
                      <option value="Fax Received" <% if (stack.type=="Fax Received") { %>selected<% } %>>Fax Received</option>
                      <option value="Fax Sent" <% if (stack.type=="Fax Sent") { %>selected<% } %>>Fax Sent</option>
                      <option value="Fee" <% if (stack.type=="Fee") { %>selected<% } %>>Fee</option>
                      <option value="Letter Received" <% if (stack.type=="Letter Received") { %>selected<% } %>>Letter Received</option>
                      <option value="Letter Sent" <% if (stack.type=="Letter Sent") { %>selected<% } %>>Letter Sent</option>
                      <option value="Manual Entry" <% if (stack.type=="Manual Entry") { %>selected<% } %>>Manual Entry</option>
                      <option value="Medical Report" <% if (stack.type=="Medical Report") { %>selected<% } %>>Medical Report</option>
                      <option value="Misc" <% if (stack.type=="Misc") { %>selected<% } %>>Misc</option>
                      <option value="MPN" <% if (stack.type=="MPN") { %>selected<% } %>>MPN</option>
                      <option value="Note" <% if (stack.type=="Note") { %>selected<% } %>>Note</option>
                      <option value="P &amp; S Report" <% if (stack.type=="P & S Report") { %>selected<% } %>>P &amp; S Report</option>
                      <option value="Payment" <% if (stack.type=="Payment") { %>selected<% } %>>Payment</option>
                      <option value="Pleadings" <% if (stack.type=="Pleadings") { %>selected<% } %>>Pleadings</option>
                      <option value="POA/Attorney Meeting" <% if (stack.type=="POA/Attorney Meeting") { %>selected<% } %>>POA/Attorney Meeting</option>
                      <option value="PQME Report" <% if (stack.type=="PQME Report") { %>selected<% } %>>PQME Report</option>
                      <option value="Proof Sent" <% if (stack.type=="Proof Sent") { %>selected<% } %>>Proof Sent</option>
                      <option value="Reviewed" <% if (stack.type=="Reviewed") { %>selected<% } %>>Reviewed</option>
                      <option value="Scanned Mail" <% if (stack.type=="Scanned Mail" || stack.type=="unassigned") { %>selected<% } %>>Scanned Mail</option>
                      <option value="SDT Records" <% if (stack.type=="SDT Records") { %>selected<% } %>>SDT Records</option>
                      <option value="Settlement Docs" <% if (stack.type=="Settlement Docs") { %>selected<% } %>>Settlement Docs</option>
                      <option value="Telephone Call" <% if (stack.type=="Telephone Call") { %>selected<% } %>>Telephone Call</option>
                    </select>                    
                           
                    <?php }
        } ?></td>
                  <td align="left" valign="top">
                    <?php
                    if (count($arrOptions["categories"]) > 0) {
                        $firstOption = '<option value="">Select Category</option>';

                        // Combine into <select> block
                        $select_categories  = '<select class="stack_input stack_category" name="stack_category_<%=stack.document_id%>" id="stack_category_<%=stack.document_id%>">' . "\n";
                        $select_categories .= $firstOption . "\n";
                        $select_categories .= implode("\n", $arrOptions["categories"]);
                        $select_categories .= "\n</select>";

                        // Output for your EJS template
                        echo $select_categories;
                      }else{ ?>
                    <select class="stack_input stack_category" name="stack_category_<%=stack.document_id%>" id="stack_category_<%=stack.document_id%>">
                      <option value="">Select Category</option>
                      <option value="Client" <% if (stack.document_extension=="Client") { %>selected<% } %>>Client</option>
                      <option value="Carrier Document" <% if (stack.document_extension=="Carrier Document") { %>selected<% } %>>Carrier Document</option>
                      <option value="Correspondence" <% if (stack.document_extension=="Correspondence") { %>selected<% } %>>Correspondence</option>
                      <option value="Defense Attorney" <% if (stack.document_extension=="Defense Attorney") { %>selected<% } %>>Defense Attorney</option>
                      <option value="Document" <% if (stack.document_extension=="Document" || stack.document_extension=="document" || stack.document_extension=="" || stack.document_extension=="unassigned" || stack.type=="unassigned") { %>selected<% } %>>Document</option>
                      <option value="Employment" <% if (stack.document_extension=="Employment") { %>selected<% } %>>Employment</option>
                      <option value="Notes" <% if (stack.document_extension=="Notes") { %>selected<% } %>>Notes</option>
                      <option value="Medical" <% if (stack.document_extension=="Medical") { %>selected<% } %>>Medical</option>
                    </select>
                    <?php } ?>
                </td>
                  <td align="left" valign="top">
                    <?php
                    if (count($arrOptions["subcategories"]) > 0) {
                        $firstOption = '<option value="">Select Sub Category</option>';

                        // Combine into full <select> block
                        $select_subcategories  = '<select class="stack_input" name="stack_subcategory_<%=stack.document_id%>" id="stack_subcategory_<%=stack.document_id%>">' . "\n";
                        $select_subcategories .= $firstOption . "\n";
                        $select_subcategories .= implode("\n", $arrOptions["subcategories"]);
                        $select_subcategories .= "\n</select>";

                        // Output for your EJS template
                        echo $select_subcategories;
                      }else{ ?>
                      <select class="stack_input" name="stack_subcategory_<%=stack.document_id%>" id="stack_subcategory_<%=stack.document_id%>">
                        <option value="">Select Sub Category</option>
                        <option value="doctor" <% if (stack.description=="doctor") { %>selected<% } %>>Doctor</option>
                        <option value="attorney" <% if (stack.description=="attorney") { %>selected<% } %>>Attorney</option>
                      </select>
                      <?php } ?>
                  </td>
                </tr>
                <tr>
                  <td colspan="3" align="left" valign="top">
                  	<div class="stack_input" id="stack_note_holder_<%=stack.document_id%>">
                   	  <label id="stack_note_label_<%=stack.document_id%>">Details</label>:<br />
                        <textarea name="stack_note_<%=stack.document_id%>" id="stack_note_<%=stack.document_id%>" class="stack_note" rows="3" style="width:430px"><%=stack.description_html %></textarea>
                    </div>
                    <div class="stack_additional_commands" id="stack_additional_commands_<%=stack.document_id%>" style="display:none">
                    <input type="checkbox" id="stack_attachnote_<%=stack.document_id%>" value="Y" />&nbsp;
                    <label for="stack_attachnote_<%=stack.document_id%>">Apply to Case Notes?</label>
                    </div>
                  </td>
                </tr>                
              </tbody>
            </table>
            </td>
          </tr>
       	<tr class="stack_input document_row_<%=stack.document_id%>" id="stack_secondrow_<%=stack.document_id%>">
        	<td colspan="4" align="left" valign="top"></td>
        <td colspan="3" align="left" valign="top">&nbsp;</td>
        </tr>
        <% }); %>
        </tbody>
    </table>
</div>
<!--
<div id="stack_listing_view_done"></div>
<script language="javascript">
$( "#stack_listing_view_done" ).trigger( "click" );
</script>
-->

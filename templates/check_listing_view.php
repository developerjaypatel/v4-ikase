<?php require_once('../shared/legacy_session.php');
session_write_close();

include ("../api/connection.php");

$blnCheckRequestAsk = ($_SESSION['user_role']=="masteradmin" && !$blnIPad);	//$_SESSION['user_role']=="admin" || 

$customer_id = $_SESSION['user_customer_id'];
$user_id = $_SESSION['user_plain_id'];
	
if (!$blnCheckRequestAsk) {
	//let's notify the check request authorizer
	$sql = "SELECT COUNT(user_id) user_count
	FROM ikase.cse_user
	WHERE INSTR(adhoc, '\"checkrequest_ask\":\"Y\"') > 0
	AND customer_id = :customer_id
    AND user_id = :user_id";
	
	$db = getConnection();
	$stmt = $db->prepare($sql);  	
	$stmt->bindParam("user_id", $user_id);
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->execute();
	$check_user =  $stmt->fetchObject();
	
	$blnCheckRequestAsk = ($check_user->user_count == 1 && !$blnIPad);
}

$blnCheckApproval = ($_SESSION['user_role']=="masteradmin" && !$blnIPad);
if (!$blnCheckApproval) {
	//let's notify the check request authorizer
	$sql = "SELECT COUNT(user_id) user_count
	FROM ikase.cse_user
	WHERE INSTR(adhoc, '\"checkrequest\":\"Y\"') > 0
	AND user_id = :user_id
	AND customer_id = :customer_id";
	
	$db = getConnection();
	$stmt = $db->prepare($sql);  	
	$stmt->bindParam("user_id", $user_id);
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->execute();
	$check_user =  $stmt->fetchObject();
	
	$blnCheckApproval = ($check_user->user_count == 1 && !$blnIPad);
}
?>
<% var blnShowClears = false; %>
<?php if ($blnCheckApproval) { ?>
<% 
blnShowClears = true; 
%>
<?php } ?>
<%
var display = "";
var margin = "";
if (checks.length==0) {
    display = "display:none";
    margin = "margin-bottom:10px";
}
var display_clears = "none";
var display_unclears = "";
var blnClearListing = false;
/*
if (document.location.hash=="#checks/uncleared" || page_title.indexOf("Uncleared Check") > -1) {
	display_clears = "";
    display_unclears = "none";
    blnClearListing = true;
}
if (!blnClearListing) {
	if (document.location.hash=="#checks/cleared" || page_title.indexOf("Cleared Check") > -1) {
    	blnClearListing = true;
    }
}
if (document.location.hash.indexOf("#payments/")==0) {
	display_clears = "";
    display_unclears = "";
    blnClearListing = true;
}
if (!blnClearListing) {
	//no clear buttons
	display_clears = "none";
    display_unclears = "none";
}
*/
//permissions and check status will dictate whether or not these buttons show up
display_clears = "";
display_unclears = "";
blnClearListing = true;
%>
<div>
	<% if (!embedded) { %>
    <div id="check_listing_header" class="glass_header" style="<%=margin %>">
    	<% if (blnShowBoxes) { %>
        <div style="float:right">
        	<button class="btn btn-sm btn-primary" id="print_selected" style="display:none; margin-top:-3px">Print Selected Checks</button>
        </div>
        <% } %>
        <!--
        <div style="float:right;">
        <input id="case_id" name="case_id" type="hidden" value="<%=current_case_id %>" />
            
            <div class="btn-group">
                
                 <label for="checks_searchList" id="label_search_check" style="width:120px; font-size:1em; cursor:text; position:relative; top:0px; left:125px; color:#999; margin-left:0px; margin-top:0px; border:#00FF00 0px solid;">Search</label>
                
                <input id="checks_searchList" type="text" class="search-field" placeholder="" autocomplete="off" onkeyup="findIt(this, 'check_listing', 'check')">
                <a id="checks_clear_search" style="position: absolute;
                right: 2px;
                top: 0px;
                bottom: 2px;
                height: 14px;
                margin: auto;
                cursor: pointer;
                border: 0px solid green;
                "><i class="glyphicon glyphicon-remove-circle" style='font-size:1em; color:#cccccc'></i></a>
            </div>
        </div>
        -->
        <span style="font-size:1.2em; color:#FFFFFF"><%=page_title.replace("Disbursement", "Client Costs Paid") %>s</span>
        &nbsp;&nbsp;<span class="white_text">(<%=checks.length %>)</span>
        <% if (page_title=="Receipt") { %>
        &nbsp;
        <button id="new_check_<%=page_title %>" class="btn btn-sm btn-primary btn_<%=page_title %>" title="Click to create a new <%=page_title %>" style="margin-top:-5px">New Received Payment</button> 
        <% } %>  
        <?php if ($blnCheckApproval || $_SESSION["user_customer_id"]!=1121) { ?>
        <% if (page_title=="Disbursement" || page_title=="Payment") { %>
        &nbsp;
        <button id="new_check_<%=page_title %>" class="btn btn-sm btn-primary btn_<%=page_title %>" title="Click to create a new <%=page_title %>" style="margin-top:-5px">New Outgoing Check</button> 
        <% } %>  
        <?php } ?>
        
        <?php if ($blnCheckRequestAsk) { ?>
        &nbsp;
        <button id="general_checkrequest" class="btn btn-sm btn-primary btn_general_checkrequest" title="Click to create a new Check Request" style="margin-top:-5px; display:none">Request General Check</button>
        <?php } ?>
    </div>
    <% } %>
    <% if (blnBankAccount) { %>
    <div style="margin-top:20px; background:white; padding:3px">
    	<div style="font-size:1.6em; color:black; border-bottom: 1px solid black">
        	Filter by
            &nbsp;
            &nbsp;
            <button class="btn btn-sm btn-primary" id="clear_filters" style="display:none">Clear Filters</button>
            &nbsp;
            <button class="btn btn-sm btn-primary" id="print_deposits" style="background:darkseagreen; color:black" disabled="disabled">Print Selected Deposits</button>
        </div>
        <% if (blnShowBoxes) { %>
        <div style="display:inline-block; border-right:1px solid black; color:black; padding:5px; background:white; vertical-align:top; height:63px">		Select All:<br />
        	<input type="checkbox" id="select_all_checks" value="Y" />
        </div>
        <% } %>
    	<div style="display:inline-block; border-right:1px solid black; color:black; padding:5px; background:white; vertical-align:top; height:63px">
        	Payee: 
            <div>
            <select id="filter_payee" style="height:29px" class="register_filter">
            <%=payee_options %>
            </select>
            </div>
        </div>
    	<% if (case_id=="") { %>
    	<div style="display:inline-block; border-right:1px solid black; color:black; padding:5px; background:white">
        	Case: <input type="text" id="case_nameInput" class="register_filter" value="" />
            <input type="hidden" id="case_filter" />
        </div>
        <% } %>
        <div style="display:inline-block; border-right:1px solid black; color:black; padding:5px; background:white; vertical-align:top; height:63px">
        	Ledger: 
            <div>
            <select id="filter_ledger" style="height:29px" class="register_filter">
            	<option value="">IN or OUT</option>
                <option value="ledger_in">Deposits</option>
                <option value="ledger_out">Withdrawals</option>
            </select>
            </div>
        </div>
        <div style="display:inline-block; border-right:1px solid black; color:black; padding:5px; background:white; vertical-align:top; height:63px">
        	Check Status: 
            <div>
            <select id="filter_status" style="height:29px" class="register_filter">
            	<option value="">Select from List</option>
                <option value="check_cleared">Cleared</option>
                <option value="check_pending">Pending</option>
                <option value="check_void">Voids</option>
                <option value="check_received">Received</option>
                <option value="check_sent">Sent</option>
            </select>
            </div>
        </div>
        <div style="display:inline-block; border-right:1px solid black; color:black; padding:5px; background:white; vertical-align:top; height:63px">
        	Date: 
            <div>
            <input type="date" id="filter_start_date" placeholder="Starting Check Date" class="register_filter" style="width:147px" />
            &nbsp;-&nbsp;
            <input type="date" id="filter_end_date" placeholder="Ending Check Date" class="register_filter" style="width:147px" />
            </div>
        </div>
        <div style="display:inline-block; color:black; padding:5px; background:white; vertical-align:top; height:63px">
        	Check Number: 
            <div>
            <input type="text" id="filter_start_number" placeholder="Starting #" class="register_filter" style="width:70px" />
            &nbsp;-&nbsp;
            <input type="text" id="filter_end_number" placeholder="Ending #" class="register_filter" style="width:70px" />
            </div>
        </div>
    </div>
    <% } %>
    <table id="check_listing" class="tablesorter check_listing check_listing_<%=page_title %>" border="0" cellpadding="0" cellspacing="1" style="<%=display %>;<%=table_width %>">
        <thead>
        <tr>
        	<% if (page_title=="Payment" && !embedded) { %>
            <th width="100">
                Inv&nbsp;#
            </th>
            <% } %>
            <% if (blnBankAccount && account_type=="trust") { %>
            <th>
                Payee
            </th>
            <th>
                Case
            </th>
            
            <% } %>
            <th>
            	Payment&nbsp;#
            </th>
            <?php if ($blnCheckApproval) { ?>
            <th>&nbsp;
                
            </th>
            <?php } ?>
            <% if (blnShowInfo) { %>
            <th width="1%">
                Ledger
            </th>
            <% } %>
            <th width="1%">
                Method
            </th>
            <th width="1%">
                Date
            </th>
            <% if (!embedded) { %>
            <th width="150">
                Category
            </th>
            <% } %>
            <th width="1%">
                Amount
            </th>
            
            <th width="1%">
                Payment
            </th>
            <th width="1%">
                Adjustment
            </th>
            
            <% if (blnShowMemo && !blnAccountListing) { %>
            <th align="left" width="350px">
                Memo
            </th>
            <% } %>
            <th width="1%">&nbsp;</th>
        </tr>
        </thead>
        <tbody>
        <% 
        _.each( checks, function(check) {
        %>
        <tr class="check_data_row check_data_row_<%= check.id %>">
        	<% if (page_title=="Payment" && !embedded) { %>
            <td align="left" valign="top" nowrap="nowrap">
            	<a title="Click to edit invoice" class="edit_invoice_full white_text" id="editinvoice_<%= check.check_id %>" style="cursor:pointer;"><%= check.kinvoice_number %></a>   
            </td>
            <% } %>
            <% if (blnBankAccount && account_type=="trust") { %>
            <td align="left" valign="top" nowrap="nowrap">
            	<%=check.payable_full_name %>
            </td>
            <td align="left" valign="top" nowrap="nowrap">
            	<!--
                <div style="float:right;">
                	<% if (case_id=="") { %>
                    &nbsp;
                	<button type="button" role="button" class="btn btn-primary btn-xs filter_case" id="filter_case_<%= check.check_id %>">Filter</button>
                    <% } %>
                </div>
                -->
            	<a href="#kase/<%= check.case_id %>" class="white_text" title="Click to Review Kase"><%= check.case_name %></a>
                <% if (check.file_number!="") { %>
	                &nbsp;&nbsp;<%= check.file_number %>
                <% } else { %>
    	            &nbsp;&nbsp;<%= check.case_number %>
                <% } %>
                <input type="hidden" id="case_name_<%= check.check_id %>" value="<%= check.case_name %>" />
            </td>
            <% } %>
            <td align="left" valign="top" nowrap="nowrap" style="position:relative">
            	<% if (blnShowBoxes && check.check_number.indexOf("Click to print") > -1) { %>
                <input type="checkbox" id="print_checkbox_<%= check.check_id %>" value="Y" class="print_checkbox" />
                <% } %>
            	<div style="float:right;">
                    <span onmouseover="showAttachmentPreview('check', event, '<%=check.attachments%>', '<%=check.case_id%>', '<%=check.customer_id%>', 'normal')">
                        <span style="display:<%=check.attach_indicator%>">
                        <button type="button" role="button" class="btn btn-primary btn-xs" style="display:<%=check.word_indicator%>">Word<%=check.word_count %></button>
                        <button type="button" role="button" class="btn btn-info btn-xs" style="display:<%=check.excel_indicator%>">XL<%=check.excel_count %></button>
                        <button type="button" role="button" class="btn btn-danger btn-xs" style="display:<%=check.pdf_indicator%>">PDF<%=check.pdf_count %></button>
                        </span>
                    </span>
                </div>
                <% if (page_title=="Disbursement") { %>
                	<div style="float:right">
                    	<% if (check.check_status!="V") {
                        	if (check.amount_due != check.payment) { %>
                    			<i class="payback glyphicon glyphicon-log-in" style='font-size:1em; color:#FFF; cursor:pointer' title="Click to record reimbursement of this advanced payment" id="payback_<%= check.check_id %>"></i>
                        <% 	}
                        } %>
                    </div>
                <% } %>
                
            	<%= check.check_number.replace("TRNSFR:", "") %>
                <input type="hidden" id="caseid_<%= check.check_id %>" value="<%= check.case_id %>" />
                <input type="hidden" id="kinvoiceid_<%= check.check_id %>" value="<%= check.kinvoice_id %>" />
                <input type="hidden" id="kinvoice_number_<%= check.check_id %>" value="<%= check.kinvoice_number %>" />
                <input type="hidden" id="corporation_<%=check.check_id %>" value="<%=check.company_name %>" />
                <input type="hidden" id="corporationid_<%=check.check_id %>" value="<%=check.corporation_id %>" />
                <input type="hidden" id="check_amount_<%=check.check_id %>" value="<%=check.amount_due %>" />
                <input type="hidden" id="check_number_<%=check.check_id %>" class="check_number" value="<%=check.actual_check_number %>" />
                <input type="hidden" id="check_date_<%=check.check_id %>" class="check_date" value="<%=moment(check.check_date).format('YYYY-MM-DD') %>" />
            </td>
            <?php if ($blnCheckApproval) { ?>
            <td>
            	<% if (!embedded || blnShowClears) { %>
                <div style="float:right" id="clear_holder_<%= check.check_id %>">
                	<% if (check.check_status!="C" && check.check_status!="V") { %>
                	<button id="clear_check_<%= check.check_id %>" type="button" role="button" class="btn btn-primary clear_check btn-xs" style="display:<%=display_clears %>">Clear</button>
                    <% } %>
                    <% if (check.check_status=="C") { %>
                    <button id="unclear_check_<%= check.check_id %>" type="button" role="button" class="btn btn-primary unclear_check btn-xs" style="display:<%=display_unclears %>">Unclear</button>
                    <% } %>
                </div>
                <% } else { %>
                &nbsp;
                <% } %>
            </td>
            <?php } ?>
            <% if (blnShowInfo) { %>
            <td align="left" valign="top">
            	<%= check.ledger %>
                <span id="ledger_<%=check.check_id %>" style="display:none">ledger_<%=check.ledger.toLowerCase() %></span>
            </td>
            <% } %>
            <td align="left" valign="top" nowrap="nowrap"><%= check.method.replace("_", " ") %></td>
            <td align="left" valign="top"><%= check.check_date %></td>
            <% if (!embedded) { %>
            <td align="left" valign="top"><%= check.check_type.replaceTout("Withdrawal", "").capitalizeWords() %></td>
            <% } %>
            <td align="right" valign="top" nowrap="nowrap" style="<%=check.void_indicator %>">
            	$<%= formatDollar(check.amount_due) %>
            </td>
            
            <td align="right" valign="top" nowrap="nowrap" style="<%=check.void_indicator %>">
    	        $<%= formatDollar(check.payment) %>
            </td>
            <td align="right" valign="top" nowrap="nowrap" style="<%=check.void_indicator %>">
	            $<%= formatDollar(check.adjustment) %>
            </td>
            
            <% if (blnShowMemo && !blnAccountListing) { %>
            <td align="left" valign="top"><%= check.memo %></td>
            <% } %>
            <td>
            <?php if (strpos($_SESSION['user_role'], "admin") !== false) { ?>
            	<% if (check.check_status!="V") { %>
                <i style="font-size:15px; color:#FF3737; cursor:pointer" class="glyphicon glyphicon-trash delete_check" id="delete_<%= check.id %>" title="Click to delete"></i>
                <% } %>
            <?php } ?>
            </td>
        </tr>
        <% }); %>
        <% if (checks.length > 0 && !embedded) { %>
        <tr style="background:black">
        	<% if (page_title=="Payment" && !embedded) { %>
            <td>&nbsp;</td>
            <% } %>
            <% if (blnShowInfo) { %>
            <td>&nbsp;</td>
            <% } %>
            <% if (!embedded) { %>
            <td align="left" valign="top">&nbsp;</td>
            <% } %>
            <td align="left" valign="top">&nbsp;</td>
            <td align="left" valign="top">&nbsp;</td>
            <td align="left" valign="top">&nbsp;</td>
            <td align="left" valign="top">Totals</td>
            <td align="right" valign="top">$<%=formatDollar(totals_due) %></td>
            <td align="right" valign="top">$<%=formatDollar(totals_payment) %></td>
            <td align="right" valign="top">$<%=formatDollar(totals_adjustment) %></td>
            <% if (blnShowMemo) { %>
            <td align="left" valign="top">&nbsp;</td>
            <% } %>
            <td align="left" valign="top">&nbsp;</td>
        </tr>
        <% } %>
        </tbody>
    </table>
</div>
<div id="check_preview_panel" style="position:absolute; padding:5px; display:none; z-index:2; background:white" class="attach_preview_panel"></div>
<% //if (embedded) { %>
<div id="check_listing_all_done"></div>
<script language="javascript">
$( "#check_listing_all_done" ).trigger( "click" );
</script>
<% //} %>

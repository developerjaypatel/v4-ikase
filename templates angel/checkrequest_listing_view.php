<?php
include("../api/manage_session.php");
if (!isset($_SESSION['user_data_path'])) {
	$_SESSION['user_data_path'] = '';
}
session_write_close();

include ("../api/connection.php");
include ("../browser_detect.php");

$blnIPad = isPad();

$blnCheckApproval = ($_SESSION['user_role']=="masteradmin" && !$blnIPad);	//$_SESSION['user_role']=="admin" || 

if (!$blnCheckApproval) {
	//let's notify the check request authorizer
	$sql = "SELECT COUNT(user_id) user_count
	FROM ikase.cse_user
	WHERE INSTR(adhoc, '\"checkrequest\":\"Y\"') > 0
	AND customer_id = :customer_id
    AND user_id = :user_id";
	
	$customer_id = $_SESSION['user_customer_id'];
	$user_id = $_SESSION['user_plain_id'];
	
	$db = getConnection();
	$stmt = $db->prepare($sql);  	
	$stmt->bindParam("user_id", $user_id);
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->execute();
	$check_user =  $stmt->fetchObject();
	
	$stmt->closeCursor(); $stmt = null; $db = null;
	
	$blnCheckApproval = ($check_user->user_count == 1 && !$blnIPad);
}

$blnCheckRequestAsk = ($_SESSION['user_role']=="masteradmin" && !$blnIPad);	//$_SESSION['user_role']=="admin" || 

if (!$blnCheckRequestAsk) {
	//let's notify the check request authorizer
	$sql = "SELECT COUNT(user_id) user_count
	FROM ikase.cse_user
	WHERE INSTR(adhoc, '\"checkrequest_ask\":\"Y\"') > 0
	AND customer_id = :customer_id
    AND user_id = :user_id";
	
	$customer_id = $_SESSION['user_customer_id'];
	$user_id = $_SESSION['user_plain_id'];
	
	$db = getConnection();
	$stmt = $db->prepare($sql);  	
	$stmt->bindParam("user_id", $user_id);
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->execute();
	$check_user =  $stmt->fetchObject();
	
	$stmt->closeCursor(); $stmt = null; $db = null;
	
	$blnCheckRequestAsk = ($check_user->user_count == 1 && !$blnIPad);
}

$blnCheckRequestSettlement = ($_SESSION['user_role']=="masteradmin" && !$blnIPad);	//$_SESSION['user_role']=="admin" || 

if (!$blnCheckRequestSettlement) {
	//let's notify the check request authorizer
	$sql = "SELECT COUNT(user_id) user_count
	FROM ikase.cse_user
	WHERE INSTR(adhoc, '\"checkrequest_settlement\":\"Y\"') > 0
	AND customer_id = :customer_id
    AND user_id = :user_id";
	
	$customer_id = $_SESSION['user_customer_id'];
	$user_id = $_SESSION['user_plain_id'];
	
	$db = getConnection();
	$stmt = $db->prepare($sql);  	
	$stmt->bindParam("user_id", $user_id);
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->execute();
	$check_user =  $stmt->fetchObject();
	
	$stmt->closeCursor(); $stmt = null; $db = null;
	
	$blnCheckRequestSettlement = ($check_user->user_count == 1 && !$blnIPad);
}
?>
<%
var display = "";
var margin = "";
checkrequests_count = "<span class='white_text'>(" + checkrequests.length + ")</span>";
if (checkrequests.length==0) {
    display = "display:none";
    margin = "margin-bottom:10px";
}
%>
<div class="checkrequest" style="">
    <div id="checkrequest_listing_header" class="glass_header" style="<%=margin %>">
    	<div style="float:right; display:">  
        	
			<div role="button" class="btn-group" style="position:relative">
            	<% if (!embedded) { %>
            	<div style="position:absolute;left: -150px;font-size: 1.3em;top: 2px;" id="total_requested" class="white_text">
                    Total:&nbsp;$<%=formatDollar(total_requested) %>&nbsp;&nbsp;
                </div>   
                <% } %>
            	 <label for="checkrequests_searchList" id="label_search_checkrequest" style="width:120px; font-size:1em; cursor:text; position:relative; top:0px; left:125px; color:#999; margin-left:0px; margin-top:0px; border:#00FF00 0px solid;">Search</label>
            	
				<input id="checkrequests_searchList" type="text" class="search-field" placeholder="" autocomplete="off" onkeyup="findIt(this, 'checkrequest_listing', 'checkrequest')">
				<a id="checkrequests_clear_search" style="position: absolute;
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
        <div style="position:absolute; left:470px" id="checkrequest_feedback"></div>
        <span style="font-size:1.2em; color:#FFFFFF"><%=page_title %></span>&nbsp;<%= checkrequests_count %>&nbsp;&nbsp;
        <% if (embedded) { %>
        <?php if ($blnCheckRequestSettlement) { ?>
        <button id="new_checkrequest" role="button" class="btn btn-sm btn-primary btn_new_checkrequest" title="Click to create a new Check Settlement Request" style="margin-top:-5px; display:">Request Settle &#10003;</button>
        <?php } ?>
        &nbsp;
        <?php if ($blnCheckRequestAsk) { ?>
         <button id="general_checkrequest" role="button" class="btn btn-sm btn-primary btn_general_checkrequest" title="Click to create a new Check Request" style="margin-top:-5px">Request Common &#10003;</button>
         <?php } ?>
        <% } else { %>
        	<?php if ($blnCheckRequestSettlement) { ?>
        <button id="new_checkrequest" role="button" class="btn btn-sm btn-primary btn_new_checkrequest" title="Click to create a new Check Settlement Request" style="margin-top:-5px; display:">Request Settle &#10003;</button>
        <?php } ?>
        	<?php if ($blnCheckApproval) { ?>
         <button id="general_checkrequest" role="button" class="btn btn-sm btn-primary btn_general_checkrequest" title="Click to create a new Check Request" style="margin-top:-5px">Request Common &#10003;</button>
         &nbsp;
         <button id="account_register" role="button" class="btn btn-sm btn_account_register" title="Click to view Account Register" style="margin-top:-5px; display:none">Register</button>
         <% if(login_user_id=="1568") { %>
         &nbsp;
         <button id="add_operating_check_<%=account_id %>" class="btn btn-primary btn-sm add_check" title="Click to deposit a Check into Account" style="margin=top:-5px">Make Deposit</button>
         <% } %>
         <?php } ?>
        <% } %>
    </div>

    <div id="checkrequest_preview_panel" style="position:absolute; width:35vw; display:none;"></div>
    <%
    var id_width = 150; 
    if (!embedded) { 
    	id_width = 290;
    }
    %>
    <table id="checkrequest_listing" class="tablesorter checkrequest_listing" border="0" cellpadding="0" cellspacing="1" style="<%=display %>">
        <thead>
        <tr>
        	<th valign="top" align="left">
            	Request&nbsp;ID
            </th>
            <th valign="top" align="left">
            	By
            </th>
            <% if (!embedded) { %>
            <th valign="top" align="left" width="550px">
            	Case
            </th>
            <% } %>
            <th valign="top" align="left">
            	Requested
            </th>
            <th valign="top" align="left" width="50px">
            	Needed
            </th>
            <th valign="top" align="left" width="250px">
            	Payable&nbsp;To
            </th>
            <th valign="top" align="left" width="1%">
            	Amount
            </th>
            <% if (!embedded) { %>
            <th valign="top" align="left">
            	Details
            </th>
            <% } %>
        </tr>
        </thead>
        <tbody>
        <% 
        var current_reason = "";
        var first_width = "180px";
        if (!embedded) {
	        first_width = "260px";
        }
        _.each( checkrequests, function(checkrequest) {
        	var colspan = 6;
            
            if (checkrequest.case_name == "") {
                checkrequest.case_name = checkrequest.case_number;
            }
            if (checkrequest.case_name == "") {
            	checkrequest.case_number = checkrequest.file_number;
                checkrequest.case_name = checkrequest.file_number;
            }
            if (current_reason!=checkrequest.reason) {
	            current_reason = checkrequest.reason;
            } else {
	            checkrequest.reason = "";
            }
            
            if (checkrequest.payable_full_name!="" && checkrequest.payable_type == "records") {
                checkrequest.payable_to = checkrequest.payable_full_name + " (" + checkrequest.payable_to + ")";
            }
        %>
        <tr class="checkrequest_data_row checkrequest_data_row_<%=checkrequest.id %>">
        	<td valign="top" align="left" nowrap="nowrap" width="<%=first_width %>">
            	<input type="hidden" id="request_case_id_<%= checkrequest.id %>" value="<%= checkrequest.case_id %>" />
                <input type="hidden" id="request_case_number_<%= checkrequest.id %>" value="<%= checkrequest.case_number %>" />
                <input type="hidden" id="request_case_name_<%= checkrequest.id %>" value="<%= checkrequest.case_name %>" />
                <input type="hidden" id="request_date_<%= checkrequest.id %>" value="<%= checkrequest.request_date %>" />
                <input type="hidden" id="request_nickname_<%= checkrequest.id %>" value="<%= checkrequest.nickname.toUpperCase() %>" />
                <input type="hidden" id="account_id_<%= checkrequest.id %>" value="" />
                <input type="hidden" id="payable_id_<%= checkrequest.id %>" value="<%= checkrequest.payable_id %>" />
                <input type="hidden" id="payable_table_<%= checkrequest.id %>" value="<%= checkrequest.payable_table %>" />
                <input type="hidden" id="payable_to_<%= checkrequest.id %>" value="<%= checkrequest.payable_to %>" />
                
                <input type="hidden" id="request_amount_<%= checkrequest.id %>" value="<%= checkrequest.amount %>" />
				<div style="float:right" id="request_buttons_<%= checkrequest.id %>">
                	<% if (!embedded) { %>
                    <button role="button" class="btn btn-xs btn-primary review_request" id="button_review_<%= checkrequest.id %>">Review</button>
                     &nbsp;|&nbsp;
                    <% } %>
                	<% if (checkrequest.approved == "P") { %>
					<?php if ($blnCheckApproval) { ?>
                	<div class="approve_buttons" id="approve_buttons_<%= checkrequest.id %>" style="display:inline-block; margin-left:5px">
                        <button role="button" class="btn btn-xs btn-success approve_request" id="approve_request_<%= checkrequest.id %>" style="display:none">Approve</button>
                       <!--
                    &nbsp;|&nbsp;-->
                        <button role="button" class="btn btn-xs btn-danger reject_request" id="reject_request_<%= checkrequest.id %>" style="display:none">Reject</button>
                        <button role="button" class="btn btn-xs btn-danger void_request" id="void_request_<%= checkrequest.id %>" style="display:none">Void</button>
                        <button role="button" class="btn btn-xs btn-danger delete_request" id="delete_request_<%= checkrequest.id %>" style="display:none">Delete</button>
                        <select id="request_reaction_<%= checkrequest.id %>" class="request_reaction">
                            <option value="" selected="selected">Pending</option>
                            <?php if ($blnCheckApproval) { ?>
                            <option value="approve">Approve</option>
                            <option value="reject">Reject</option>
                            <option value="void">Void</option>
                            <option value="delete">Delete</option>
                            <?php } ?>
                        </select>
                    </div>
                    
                    <?php } else { ?>
                    	<span style="background:orange; color:black; padding:2px">PENDING</span>
                    <?php } ?>
                    <% } %>
                    
                    <% if (checkrequest.approved == "Y") { %>
                    	<span style="background:green; padding:2px" title="Approved by <%=checkrequest.reviewer_name %> on <%=moment(checkrequest.review_date).format("MM/DD/YYYY") %>">APPROVED</span>
                        <% if (checkrequest.check_id!=-1) { %>
                        &nbsp;<a title="Click to print a copy of check." class="print_copy white_text" id="print_copy_<%=checkrequest.check_id %>" style="cursor:pointer"><i class="glyphicon glyphicon-print" style="color:antiquewhite">&nbsp;</i></a>
                    	<% } else { %>    
                        &nbsp;<a title="Click to print a copy of check." class="print_copy white_text" id="print_copy_<%=checkrequest.check_id %>" style="cursor:pointer; visibility:hidden"><i class="glyphicon glyphicon-print" style="color:antiquewhite">&nbsp;</i></a>
                        <% } %>    
                    <% } %>
                    <% if (checkrequest.approved == "N") { %>
                    	<span style="background:red; padding:2px" title="Denied by <%=checkrequest.reviewer_name %> on <%=moment(checkrequest.review_date).format("MM/DD/YYYY") %>">DENIED</span>
                    <% } %>
                </div>
                <div class="approve_dialog_holder" id="approve_dialog_holder_<%= checkrequest.id %>" style="display:none; background:black; position:absolute">
                    <div id="check_number_holder_<%= checkrequest.id %>">
                        <input type="text" id="check_number_<%= checkrequest.id %>" style="width:200px" placeholder="Check # (Optional)" />
                        &nbsp;
                        <span id="approve_complete_holder_<%= checkrequest.id %>">
                        	<button role="button" class="btn btn-xs btn-primary approve_complete" id="approve_complete_<%= checkrequest.id %>">Complete</button>
                            &nbsp;
                 			<button role="button" class="btn btn-xs approve_cancel" id="approve_cancel_<%= checkrequest.id %>">&times;</button>           
                        </span>
                        <span id="print_holder_<%= checkrequest.id %>"></span>
                    </div>
                </div>
                <div class="reject_dialog_holder" id="reject_dialog_holder_<%= checkrequest.id %>" style="display:none; background:black;position:absolute">
                    <div id="reject_reason_holder_<%= checkrequest.id %>">
                        <input type="text" id="reject_reason_<%= checkrequest.id %>" style="width:400px" placeholder="Reject Reason" />
                        &nbsp;
                        <span id="reject_complete_holder_<%= checkrequest.id %>">
	                        <button role="button" class="btn btn-xs btn-primary reject_complete" id="reject_complete_<%= checkrequest.id %>">Complete</button>&nbsp;
                 			<button role="button" class="btn btn-xs reject_cancel" id="reject_cancel_<%= checkrequest.id %>">&times;</button>
                        </span>
                    </div>
                </div>
                <?php if ($blnCheckApproval) { ?>
            		<span id="checkrequest_edit_<%= checkrequest.id %>" class="edit_checkrequest" style="cursor:pointer; text-decoration:underline" title="Click to edit Check Request Details"><%= checkrequest.id %></span>
                <?php } else { ?>
                    <span id="checkrequest_review_<%= checkrequest.id %>" class="review_request" style="cursor:pointer; text-decoration:underline" title="Click to review Check Request Details"><%= checkrequest.id %></span>
                <?php } ?>
                &nbsp;<%=checkrequest.import_indicator %>
            </td>
            <td valign="top" align="left" width="1%">
            	<%= checkrequest.nickname.toUpperCase() %>
            </td>
             <% if (!embedded) { %>
            <td valign="top" align="left" nowrap="nowrap">
            	<div style="float:right">
                	<%=checkrequest.case_type %>&nbsp;&nbsp;
                	<button role="button" class="btn btn-xs btn-primary review_books" id="review_books_<%=checkrequest.case_id %>">Books</button>
                </div>
                <% if (checkrequest.case_settled=="Y") { %>
                <div style="float:right; margin-right:5px">
                	<i class="glyphicon glyphicon-usd" style="color:#0F9"></i>
                </div>
                <% } %>
                <% if ((login_user_id=="1568" || login_user_id=="1656") && document.location.hash=="#pendingrequests/trust") { %>
                <a id="unattach_<%= checkrequest.id %>" class="unattach" style="cursor:pointer; color:pink" title="Click to detach this check request from the Trust Account">Detach</a>
                <% } %>
                <a href="#kase/<%=checkrequest.case_id %>" title="Click to review Kase" class="white_text"><%=checkrequest.case_name %></a>
            </td>
            <% } %>
            <td valign="top" align="left" width="1%">
            	<%= checkrequest.request_date %>
            </td>
            <td valign="top" align="left" nowrap="nowrap">
            	<%= checkrequest.needed_date %>
                <% 
                if (checkrequest.approved == "P") {
                	if (checkrequest.late) { %>
                		&nbsp;<span style="cursor:pointer; background:orange; color: black; padding:2px">LATE</span>
                <% 	} %>
                <% 	if (checkrequest.rush_request=="Y") { %>
                		&nbsp;<span style="cursor:pointer; background:red; color: white; padding:2px">RUSH</span>
                <% 	}
                } %>
            </td>
            <td valign="top" align="left">
            	<%= checkrequest.payable_to %>
            </td>
            <td valign="top" align="right" width="1%">
            	$<span id="checkrequest_amount_<%=checkrequest.id %>"><%= numberWithCommas(Number(checkrequest.amount).toFixed(2)) %></span>
            </td>
            <% if (!embedded) { %>
        	<td valign="top" align="left">
            	<%=checkrequest.reason %>
            </td>
	        <% } %>
        </tr>    
        <% if (embedded && checkrequest.reason!="") { %>
        <tr class="checkrequest_data_row checkrequest_data_row_<%=checkrequest.id %>">
        	<td valign="top" align="left" colspan="7">
            	Details: <%=checkrequest.reason %>
            </td>
        </tr>
        <% } %>
        <% }); %>    
        </tbody>
    </table>
</div>
<div id="checkrequest_listing_view_done"></div>
<script language="javascript">
$( "#checkrequest_listing_view_done" ).trigger( "click" );
</script>
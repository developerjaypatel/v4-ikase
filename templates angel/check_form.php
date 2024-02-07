<?php 
include("../api/manage_session.php");
session_write_close();

$blnMinScreen = ($_SESSION["user_customer_id"]==1121 || $_SESSION["user_customer_id"]==1033);

include("../api/connection.php");

$sql = "SELECT * 
FROM `cse_cost_type` 
WHERE 1
AND deleted = 'N'
ORDER BY cost_type ASC";

try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$check_categories =  $stmt->fetchAll(PDO::FETCH_OBJ);
	
	$stmt->closeCursor(); $stmt = null; $db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage(), "sql"=>$sql));
		echo json_encode($error);
}

$arrOptions = array();
$option = "<option value=''>Select from List</option>";
$arrOptions[] = $option;
foreach($check_categories as $cat) {
	$cost_type_id = $cat->cost_type_id;
	$cost_type = $cat->cost_type;
	$option = "<option value='" . strtolower($cost_type) . "'>" . $cost_type . "</option>";
	$arrOptions[] = $option;
}

$sql = "SELECT * 
FROM `cse_checkrequest_type` 
WHERE 1
AND deleted = 'N'
ORDER BY checkrequest_type ASC";

try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$request_categories =  $stmt->fetchAll(PDO::FETCH_OBJ);
	
	$stmt->closeCursor(); $stmt = null; $db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage(), "sql"=>$sql));
		echo json_encode($error);
}



$arrPayeeOptions = array();
$option = "<option value=''>Select from List</option>";
$arrPayeeOptions[] = $option;
foreach($request_categories as $cat) {
	$checkrequest_type_id = $cat->checkrequest_type_id;
	$checkrequest_type = $cat->checkrequest_type;
	$option = "<option value='" . strtolower($checkrequest_type) . "'>" . $checkrequest_type . "</option>";
	$arrPayeeOptions[] = $option;
}
?>
<div style="float:right">
	<div id="manage_categories_holder"></div>
</div>
<div class="check" style="margin-left:10px">
	<div style="float:right; display:none" id="payee_holder">
        <table align="left" width="450" border="0" bordercolor="#dddddd" cellspacing="0" cellpadding="3" class="payable_other_table">
            <tr>
                <td align="left" valign="top" nowrap="">
                    <span style="font-weight:bold">Check Recipient</span>
                </td>
                <td align="left" valign="top" nowrap="">
                	<input type="hidden" id="check_recipient_id" name="check_recipient_id" value="" />
                    <input type="text" id="check_recipient" name="check_recipient" class="payable_other" style="width:275px" />
                    <div id="check_recipient_list" style="position: absolute; z-index: 9999; background: white; border:1px solid black; width: 273px; padding: 3px; display:none"></div>
                </td>
            </tr>
            <tr>
                <td align="left" valign="top" nowrap="">
                    <span style="font-weight:bold">Address</span>
                </td>
                <td align="left" valign="top" nowrap="">
                    <input type="text" id="full_addressInput" name="recipient_address" class="payable_other" style="width:275px" />
                    <div style="display:none">
                    	<input class="field" id="street_payable_other_table" disabled="true" />
                        <input class="field" id="street_number_payable_other_table" disabled="true" />
                        <input class="field" id="city_payable_other_table" disabled="true" />
                        <input class="field" id="administrative_area_level_1_payable_other_table" disabled="true" />
                        <input class="field" id="postal_code_payable_other_table" disabled="true" />
                    </div>
                </td>
            </tr>
            <tr>
                <td align="left" valign="top" nowrap="">
                    <span style="font-weight:bold">Suite/Apt</span>
                </td>
                <td align="left" valign="top" nowrap="">
                    <input class="field" id="suiteInput" name="suiteInput"  />
                </td>
            </tr>
        </table>
        <div id="addressGrid" style="display:none" class="payable_other_table">
            <table id="address">
              <tr style="display:none">
                <td class="label">Street address</td>
                <td class="slimField"><input class="field" id="street_number_payable_other_table" disabled="true" />
                </td>
                <td class="wideField" colspan="2"><input class="field" id="route_payable_other_table" disabled="true" />
                </td>
              </tr>
              <tr style="display:none">
                <td class="label">City</td>
                <td class="wideField" colspan="3">
                    <input class="field" id="locality_payable_other_table" disabled="true" />
                    <input class="field" id="sublocality_payable_other_table" disabled="true" />
                    <input class="field" id="neighborhood_payable_other_table" disabled="true" />
                </td>
              </tr>
              <tr style="display:none">
                <td class="label">Country</td>
                <td class="wideField" colspan="3">
                    <input class="field" id="country_payable_other_table" disabled="true"></input>
                </td>
              </tr>
            </table>
        </div>
    </div>
    <form id="check_form" method="post" action="">
        <input id="table_name" name="table_name" type="hidden" value="check" />
        <input id="table_id" name="table_id" type="hidden" value="<%=id %>" />
        <input id="case_id" name="case_id" type="hidden" value="<%=case_id %>" />
        <input id="corp_id" name="corp_id" type="hidden" value="<%= corp_id %>" />
        <input id="fee_id" name="fee_id" type="hidden" value="" />
        <input id="recipient" name="recipient" type="hidden" value="<%=recipient %>" />
        <input id="payback_id" name="payback_id" type="hidden" value="<%=payback_id %>" />
        <input id="account_id" name="account_id" type="hidden" value="<%=account_id %>" />
        <input id="account_type" type="hidden" value="<%=account_type %>" />
        <input id="kinvoice_id" name="kinvoice_id" type="hidden" value="<%=kinvoice_id %>" />
        <input id="invoice_number" name="invoice_number" type="hidden" value="<%=invoice_number %>" />
        <div>
        	<div style="float:right; width:167px; <?php if ($blnMinScreen) { ?>display:none<?php } ?>">
                <strong>Check Status</strong>
                <select id='check_statusInput' name='check_statusInput'>
                    <option value='' <%if(check_status=="") { %>selected<% } %>>Select ...</option>
                    <option value='R' <%if(check_status=="R") { %>selected<% } %>>Received</option>
                    <option value='S' <%if(check_status=="S") { %>selected<% } %>>Sent</option>
                    <option value='P' <%if(check_status=="P") { %>selected<% } %>>Pending</option>
                    <option value='P' <%if(check_status=="V") { %>selected<% } %>>Void</option>
                </select>
                <div id="check_status_description" style="color:white; font-style:italic;"></div>
            </div>
	        <div id="check_case_label" style="display:none; width:132px; font-weight:bold; vertical-align:top">Case</div>
            <div id="case_input_holder" style="display:none; vertical-align:top; ">
                <input type="text" id="case_nameInput" class="modal_input" style="width:540px" value="" />
            </div>
            <div id="check_case_name" style="display:inline-block"></div>
        </div>
        <table align="left" width="470px" border="0" bordercolor="#dddddd" cellspacing="0" cellpadding="3">
          <tbody>
            <tr height="30" valign="middle">
              <td width="112" align="left" valign="top" nowrap=""><strong>Transaction Date</strong></td>
              <td width="540" align="left" nowrap="">
                <div style="float:right" id="type_span_holder">
                </div>
                <div style="float:right" id="type_holder">
                	<strong>Type</strong>
                    <% if ((payback_id == "" || payback_id == "-1") && !blnFee) { %>
                    	<%if (ledger=="IN") { %>
                        <input type="radio" class="ledger" name="ledger" id="ledger_in" value="IN" checked />
                        &nbsp;<span id="ledger_in_label">Receipt</span>
                        <% } else { %>
                        <input type="radio" class="ledger" name="ledger" id="ledger_out" value="OUT"  checked />
                        &nbsp;<span id="ledger_out_label">Disbursement</span>
                        <% } %>
                    <% } else { %>
                        <input type="radio" class="ledger" name="ledger" id="ledger_in" value="IN" checked />
                        <% if (!blnFee) { %>
                        &nbsp;<span id="ledger_in_label">Reimbursement</span>
                        <% } else { %>
                        &nbsp;<span id="ledger_out_label">Payment</span>
                        <% } %>
                    <% } %>
                </div>
              <input type="text" name="transaction_dateInput" id="transaction_dateInput" style="width:133px" class="modalInput check input_class" value="<%=transaction_date %>" autocomplete="off" tabindex="1"></td>
            </tr>
            <%if (ledger=="IN" && account_type=="trust") {	
            	//&& case_id!=-2  
            %>
            <tr>
            	<td align="left" valign="top" nowrap="" style="border-top:1px solid white">
              	<span style="font-weight:bold">From</span>
              </td>
              <td align="left" nowrap="" style="border-top:1px solid white">
              		<select class="modalInput" id="check_from" name="check_from" style="width:325px" tabindex="2" required>
                    	<option value="">Select a Carrier from List</option>
                        <%=payings %>
                    </select>
                    <span id="check_from_span"></span>
               </td>
            </tr>
            <% } %>
            <%if (ledger=="IN" && case_id==-2 && account_type=="operating") { %>
            <tr>
            	<td align="left" valign="top" nowrap="" style="border-top:1px solid white">
              	<span style="font-weight:bold">From</span>
              </td>
              <td align="left" nowrap="" style="border-top:1px solid white">
              		<input type="text" class="modalInput" id="check_from" name="check_from" style="width:325px" tabindex="2" />
                    <span id="check_from_span"></span>
               </td>
            </tr>
            <% } %>
            <% if (blnSavePayableTo) { %>
            <tr height="30" valign="middle" id="payable_to_mainrow">
              <td align="left" valign="top" nowrap="" style="border-top:1px solid white">
              	<span style="font-weight:bold">Payable To</span>
              </td>
              <td align="left" nowrap="" style="border-top:1px solid white">
              		<select class="modalInput" id="payable_to" name="payable_to" style="width:325px" tabindex="3">
                    	<option value="">Select a Partie from List</option>
                        <%=parties %>
                    </select>
                    <span id="payable_to_span"></span>
               </td>
            </tr>
            <tr height="30" valign="middle" id="payable_to_row">
              <td align="left" valign="top" nowrap="" style="border-bottom:1px solid white">
              	<span style="font-weight:bold">Or </span>
              </td>
              <td align="left" valign="top" style="border-bottom:1px solid white">
              	<div style="float:right">
                    &nbsp;<button id="manage_payableto" class="btn btn-xs btn-primary manage_payableto">manage</button>
                </div>
              	<select id="other_payable_to">
                    <?php echo implode("\r\n", $arrPayeeOptions); ?>
                </select>
              </td>
            </tr>
            <% } %>
            <tr height="30" valign="middle">
              <td align="left" valign="top" nowrap=""><strong>Category</strong></td>
              <td align="left" nowrap="">
              	<div style="float:right">
                	<span id="payment_label" style="font-weight:bold"><%=payment_label %></span>
                    $
                    <input name="amount_dueInput" type="number" step="0.01" min="0" id="amount_dueInput" style="width:75px" class="modalInput check input_class payment_calc" tabindex="3" value="<%=Number(amount_due).toFixed(2) %>" autocomplete="off" required >
                </div>
              	<div style="display:inline-block">
                	
                	<div style="float:right">
                    	&nbsp;<button id="manage_category" class="btn btn-xs btn-primary manage_category">manage</button>
                    </div>
                    
                    <select id="check_typeInput" name="check_typeInput" style="width:325px" class="modalInput check input_class" tabindex="3">
                    <?php echo implode("\r\n", $arrOptions); ?>
                    </select>
                </div>
              </td>
            </tr>
            <tr height="30" valign="middle">
              <td align="left" valign="top" nowrap="">
              	<select id="methodInput" name="methodInput">
                	<option value="check"  <%if(method=="check" || method=="") { %>selected<% } %>>Check</option>
                    <option value="cc" <%if(method=="cc") { %>selected<% } %>>CC</option>
                    <option value="money_order" <%if(method=="money_order") { %>selected<% } %>>Money Order</option>
                    <option value="money_transfer" <%if(method=="money_transfer") { %>selected<% } %>>Money Transfer</option>
                    <option value="transfer" <%if(method=="transfer") { %>selected<% } %>>Invoice Transfer</option>
                </select>
              	<strong> #</strong>
              </td>
              <td align="left" nowrap="">
              	<div style="float:right; display:none" id="apply_payment_holder">
                	<button class="btn btn-sm btn-primary" id="apply_payment" role="button">Apply Payment</button>
                    &nbsp;
                    <a id="question_apply_payment" class="question_button" style="color:white">
	                    <i class="glyphicon glyphicon-question-sign">&nbsp;</i>
                    </a>
                    <div id="answer_apply_payment" class="answer_box" style="font-style:italic;
	color:black;
	background:aliceblue;
	padding: 5px;
	border:1px solid black;
	position:absolute;
	z-index:9999;
	display:none;
    margin-top:5px;
    margin-left:-150px;
    ">
                    	Click the Apply Payment button if you wish 
                        <br />
                        to enter the matching Payment to this Disbursment
                    </div>
                </div>
              	<div style="float:right; display:none" id="check_payment_holder">
                	<strong>Payment</strong>
                    $
                    <input type="number" step="0.01" min="0" id="paymentInput" name="paymentInput" style="width:75px" class="modalInput check input_class payment_calc" tabindex="4" value="<%=Number(payment).toFixed(2) %>" autocomplete="off">
                </div>
              	<input name="check_numberInput" type="text" id="check_numberInput" style="width:133px" class="modalInput check input_class" tabindex="3" value="<%=check_number %>" autocomplete="off" required ></td>
            </tr>
            <tr height="30" valign="middle" align="left">
              <td align="left" valign="top"><span style="font-weight:bold; <?php if ($blnMinScreen) { ?>display:none<?php } ?>">Date</span></td>
              <td align="left" valign="top">
              	<div style="float:right; display:none" id="check_adjustment_holder">
                	<strong>Adjustment</strong>
                    $ 
                    <input type="number" step="0.01" min="0" id="adjustmentInput" name="adjustmentInput" style="width:75px" class="modalInput check input_class payment_calc" tabindex="4" value="<%=Math.abs(Number(adjustment).toFixed(2)) %>" autocomplete="off">
                </div>
                <input name="check_dateInput" type="text" id="check_dateInput" style="width:133px; <?php if ($blnMinScreen) { ?>display:none<?php } ?>" class="modalInput check input_class" tabindex="2" value="<%=check_date %>" autocomplete="off" required />
              </td>
            </tr>
            <tr height="30" valign="middle" align="left">
              <td align="left" valign="top" colspan="2"><hr />
              </td>
            </tr>
            <tr height="30" valign="middle" align="left">
              <td align="left" valign="top"><strong>Memo</strong></td>
              <td align="left" valign="top"><textarea name="memoInput" id="memoInput" cols="30" rows="2" style="width:433px" class="modalInput check input_class" tabindex="5"><%=memo %></textarea></td>
            </tr>
            <% if (kinvoice_id!="") { %>
            <tr align="left" valign="middle" height="30">
              <td align="left" valign="top" nowrap="nowrap"><span id="invoiced_label" style="font-weight:bold">Invoiced</span></td>
              <td align="left" nowrap="nowrap">
              	<select style="width:433px" class="modalInput" id="carrier" name="carrier" multiple="multiple" tabindex="6">
                    <%=carriers %>
                </select>
             </td>
            </tr>
            <% } %>
            <tr align="left" valign="middle" height="30">
              <td align="left" valign="top" nowrap="nowrap">&nbsp;</td>
            <td align="left" nowrap="nowrap">
            	<input type='hidden' id='send_document_id' name='send_document_id' value="" />
	            <div id="message_attachments" style="width:90%"></div>
            </td>
            </tr>
          </tbody>
        </table>
    </form>
    <!--<div style="font-size:0.7em; color:black"><%=account_type %></div>-->
</div>
<div id="check_all_done"></div>
<script language="javascript">
$( "#check_all_done" ).trigger( "click" );
</script>
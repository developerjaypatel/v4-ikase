<?php 
include("../api/manage_session.php");
session_write_close();

include("../api/connection.php");

$sql = "SELECT * 
FROM `cse_checkrequest_type` 
WHERE 1
AND deleted = 'N'
ORDER BY checkrequest_type ASC";

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
	$checkrequest_type_id = $cat->checkrequest_type_id;
	$checkrequest_type = $cat->checkrequest_type;
	$option = "<option value='" . strtolower($checkrequest_type) . "'>" . $checkrequest_type . "</option>";
	$arrOptions[] = $option;
}
?>
<% if (blnSettlementRequestsPending) { %>
<div id="settlement_request_completed" style="background:orange;padding:2px;color: black;font-size: 1.3em;">
	Settlement Checks are Pending on this case.  Please make sure all Pending Requests are processed before requesting more checks for this settlement.
</div>
<% } else { %>
<div style="float:right">
	<div id="manage_categories_holder"></div>
</div>
<div class="checkrequest" style="margin-left:10px">
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
    <form id="checkrequest_form" method="post" action="">
        <input id="table_name" name="table_name" type="hidden" value="checkrequest" />
        <input id="table_id" name="table_id" type="hidden" value="<%=id %>" />
        <input id="case_id" name="case_id" type="hidden" value="<%=case_id %>" />
        <input id="corp_id" name="corp_id" type="hidden" value="<%= corp_id %>" />
        <input id="account_id" name="account_id" type="hidden" value="" />
        <div style="display:none" id="account_name_holder">
            <div id="account_label" style="display:inline-block; width:110px; font-weight:bold">Account</div>
            <div id="account_name" style="display:inline-block;"></div>
        </div>
      <div style="display:none;" id="check_case_holder">
	        <div id="check_case_label" style="display:; width:125px; font-weight:bold">Case</div>
            <div id="check_case_name" style="display:inline-block"></div>
        </div>
        <table align="left" width="350" border="0" bordercolor="#dddddd" cellspacing="0" cellpadding="3">
          <tbody>
            <tr height="30" valign="middle">
              <td align="left" valign="top" nowrap=""><span style="font-weight:bold">Case</span></td>
              <td align="left" nowrap="">
              		<div id="case_input_holder" style="display:none; ">
                    	<input type="text" id="case_nameInput" class="modal_input" style="width:440px" value="" />
                    </div>
              		<span id="case_nameSpan"><%=case_name %></span>
                    <a id="use_current_case" style="cursor:pointer; display:none" class="white_text">Use current case</a>
              </td>
            </tr>
            <tr height="30" valign="middle">
              <td align="left" valign="top" nowrap="" style="border-top:1px solid white">
              	<span style="font-weight:bold">Payable To</span>
              </td>
              <td align="left" nowrap="" style="border-top:1px solid white">
              		<select class="modalInput" id="payable_to" name="payable_to" tabindex="1" required <% if (blnBulk) { %>multiple="multiple"<% } %> style="width:440px; display:<% if (blnBulk) { %>none<% } %>">
                        <option value="">Select a Partie from List</option>
                        <%=parties %>
                    </select>
                    <span id="payable_to_span"></span>
                    <table id="payable_to_table" width="440px" style="display:<% if (!blnBulk) { %>none<% } %>">
                    	<tbody id="payable_to_rows">
                        </tbody>
                    </table>
               </td>
            </tr>
            <tr height="30" valign="middle" id="payable_to_row" style="display:<% if (blnSettlement) { %>none<% } %>">
              <td align="left" valign="top" nowrap="" style="border-bottom:1px solid white">
              	<span style="font-weight:bold">Or </span>
              </td>
              <td align="left" valign="top" style="border-bottom:1px solid white">
              	<div style="float:right">
                    &nbsp;<button id="manage_payableto" class="btn btn-xs btn-primary manage_payableto">manage</button>
                </div>
              	<select id="other_payable_to">
                    <?php echo implode("\r\n", $arrOptions); ?>
                </select>
              </td>
            </tr>
            
            <tr height="30" valign="middle">
              <td width="112" align="left" valign="top" nowrap=""><span style="font-weight:bold">Requested</span></td>
              <td width="420" align="left" nowrap="">
              		<div style="float:right">
                    	<input type="checkbox" name="rush_request" id="rush_request" value="Y" tabindex="3" />
                    	<label>RUSH</label>
                    </div>
                    <input name="request_dateInput" type="text" id="request_dateInput" style="width:133px" class="modalInput check input_class" tabindex="2" value="<%=request_date %>" autocomplete="off" required />
              </td>
            </tr>
            <tr height="30" valign="middle">
              <td align="left" valign="top" nowrap="">
	              <span style="font-weight:bold">Needed</span>
              </td>
              <td align="left" nowrap="">
              	<div style="float:right; display:<% if (blnBulk) { %>none<% } %>">
                	<span id="amount_label" style="font-weight:bold">Amount</span>
                    $
                    <input name="amountInput" type="number" step="0.01" min="0" id="amountInput" style="width:75px" class="modalInput check input_class amount_calc" value="<%=amount %>" autocomplete="off" tabindex="5" required >
                </div>
                
              	<input type="text" name="needed_dateInput" id="needed_dateInput" style="width:133px" class="modalInput check input_class" value="<%=needed_date %>" autocomplete="off" tabindex="4" required />
              </td>
            </tr>
            <tr height="30" valign="middle" align="left">
              <td align="left" valign="top" colspan="2"><hr />
              </td>
            </tr>
            <tr height="30" valign="middle" align="left">
              <td align="left" valign="top"><span style="font-weight:bold">Details</span></td>
              <td align="left" valign="top"><textarea name="reasonInput" id="reasonInput" cols="30" rows="2" style="width:433px" class="modalInput check input_class" tabindex="6" required><%=reason %></textarea></td>
            </tr>
          </tbody>
        </table>
    </form>
</div>
<div id="checkrequest_all_done"></div>
<script language="javascript">
$( "#checkrequest_all_done" ).trigger( "click" );
</script>
<% } %>
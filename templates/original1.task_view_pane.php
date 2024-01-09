<?php
include("../api/manage_session.php");
session_write_close();

//special cases
$blnGlauber = ($_SESSION["user_customer_id"]=='1049');
include ("../browser_detect.php"); 

include("../api/connection.php");

$sql = "SELECT * 
FROM `cse_task_type` 
WHERE 1
AND deleted = 'N'
ORDER BY task_type ASC";

try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$task_types =  $stmt->fetchAll(PDO::FETCH_OBJ);
	
	$stmt->closeCursor(); $stmt = null; $db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage(), "sql"=>$sql));
		echo json_encode($error);
}

$arrOptions = array();
$option = "<option value=''>Select from List</option>";
$arrOptions[] = $option;
foreach($task_types as $typ) {
	$task_type_id = $typ->task_type_id;
	$task_type = $typ->task_type;
	$option = "<option value='" . strtolower($task_type) . "'>" . ucwords($task_type) . "</option>";
	$arrOptions[] = $option;
}
?>
<% if (blnShowBilling) { %>
<div style="margin-top:-10px; padding-bottom:5px">
	<input type="button" class="btn btn-xs" id="view_task" value="Task">
    <input type="button" class="btn btn-xs" id="view_billable" value="Bill This" style="display:none">
    <div style="display:none" id="cancel_billable_holder">
    	&nbsp;
    	<input type="button" class="btn btn-xs btn-warning" id="cancel_billable" value="Cancel Bill" title="Click to Clear Billing related to this Event">
    </div>
</div>
<% } %>
<div class="task" id="task_screen">
<form id="task_form" method="post" action="">
    <input id="table_name" name="table_name" type="hidden" value="task" />
    <input id="table_id" name="table_id" type="hidden" value="<%=id %>" />
    <input id="case_id" name="case_id" type="hidden" value="<%=case_id %>" />
    <input id="billing_time" name="billing_time" type="hidden" value="" />

<table width="100%" border="0" align="center" cellpadding="3" cellspacing="0">
    <tr>
        <th width="5%" align="left" valign="top" scope="row">Case:</th>
        <td width="530" valign="top" id="case_id_holder"><input name="case_fileInput" type="text" id="case_fileInput" size="30" class="modal_input" />
          <span id="case_idSpan" style="display:"></span>
        </td>
    </tr>
    <tr style="display:" id="doi_row">
        <th align="right" valign="top" scope="row">DOI:</th>
        <td colspan="2" valign="top">
            <select id="doi_id" style="margin-top:-2px; width:150px; height:28px"></select>
        </td>
	</tr>
    <tr>
    <th width="5%" align="left" valign="top" scope="row">Subject:</th>
    <td width="530" valign="top">
      <textarea name="task_titleInput" id="task_titleInput" style="width:433px;resize: none;" rows="2" class="modalInput task input_class" parsley-error-message="" required><%=task_title %></textarea>   <!-- Solulab code change -  22-04-2019-->
    </td>
    </tr>
  <tr>
    <th align="left" valign="top" scope="row">Assignee:</th>
    <td valign="top">
    	<div style="width:450px; text-align:left">
            <div style="float:right; width:205px; margin-right:5px; vertical-align:top; display:">
                <div style="font-weight:bold; vertical-align:top; display:inline-block">CC:</div>
                    <div style="vertical-align:top; display:inline-block">
                        <input name="ccInput" type="text" id="ccInput" style="width:150px" value="" class="modalInput task input_class" parsley-error-message="" />
                    </div>
            </div>
          <input name="assigneeInput" type="text" id="assigneeInput" style="width:150px; margin-top:-30px; margin-left:0px" value="<%=assignee %>" class="modalInput task input_class" required />
      </div>
    </td>
    </tr>
  <tr style="display:none">
    <th align="left" valign="top" scope="row">Location:</th>
    <td valign="top"><input name="full_addressInput" type="text" id="full_addressInput" style="width:433px" class="modalInput task input_class" placeholder="Enter task address" value="<%=full_address %>" /></td>
    </tr>
  <tr>
    <th align="left" valign="top" scope="row">Due:</th>
    <td valign="top">
      <div style="vertical-align:top; float:right; margin-right:17px" id="reschedule_section">
        Or calculate&nbsp;days&nbsp;           
        <input type="text" value="" name="number_of_days" id="number_of_days" style="width:40px;" onkeyup="noAlpha(this, '0')" onkeypress="noAlpha(this, '0')" />
        <span id="calculated_dateSpan" class="span_class form_span_vert" style="position:relative; width:20px; font-size:1em" ></span>
        </div>
      <input name="task_dateandtimeInput" class="modalInput task input_class" id="task_dateandtimeInput" style="width:75px" placeholder="12/12/2012" value="<%=task_dateandtime %>" />
      <input class="modalInput task input_class original_date" type="hidden" value="<%=task_dateandtime %>" />
    </td>
    </tr>
  <tr>
    <th align="left" valign="top" scope="row">Status:</th>
    <td valign="top">
      <div style="float:right; margin-right:17px">
        <span style="font-weight:bold">
          Priority:&nbsp;
          </span>
        <select name="task_priorityInput" id="task_priorityInput" class="modalInput task input_class" style="width:75px">
          <option value="" <% if (task_priority=="") { %>selected<% } %>>Select from List</option>
          <option value="high" <% if (task_priority=="high") { %>selected<% } %>>High</option>
          <option value="normal" <% if (task_priority=="normal" || task_priority=="") { %>selected<% } %>>Normal</option>
          <option value="low" <% if (task_priority=="low") { %>selected<% } %>>Low</option>
          </select>
        </div>
      <select name="task_typeInput" id="task_typeInput" class="modalInput task input_class" style="height:25px; width:75px; margin-top:-30px; margin-left:0px">
        <option value="open" <% if (task_type=="" || task_type=="open") { %>selected<% } %>>Open</option>
        <option value="progress" <% if (task_type=="progress") { %>selected<% } %>>In Progress</option>
        <option value="closed" <% if (task_type=="closed") { %>selected<% } %>>Closed</option>
        </select>
    </td>
    </tr>
  <tr>
    <th align="left" valign="top" scope="row">Entered&nbsp;By:</th>
    <td valign="top">
    <div style="float:right">
        <div style="float:right; display:none">
        	&nbsp;<button id="manage_type" class="btn btn-xs btn-primary manage_type">manage</button>
        </div>
        <span style="font-weight:bold">Type:</span>
        <select id="type_of_taskInput" name="type_of_taskInput" style="width:133px" class="modalInput check input_class">
			<?php echo implode("\r\n", $arrOptions); ?>
            <option style="font-size: 1pt; background-color: #000000;" disabled="">&nbsp;</option>
            <option value="manage_task_types">Manage List</option>
        </select>
      </div>
      <input name="task_fromInput" type="text" id="task_fromInput" style="width:433px" class="modalInput task input_class hidden"  value="<%=from %>" />
      <span class="span_class"><%=from %></span>
    </td>
  </tr>
  <tr>
    <th align="left" valign="top" scope="row" colspan="2">
    	Description:
    </th>
  </tr>
  <tr>
    <td colspan="2" align="left" valign="top" scope="row">
    <textarea name="task_descriptionInput" id="task_descriptionInput" class="modalInput task input_class"><%=task_description %></textarea></td>
  </tr>
  <tr>
    <th align="left" valign="top" scope="row">
    	Attachments:
    </th>
    <td valign="top">
      <input type='hidden' id='send_document_id' name='send_document_id' value="" />
      <div id="message_attachments" style="width:90%"></div>
    </td>
  </tr>
  <tr>
      <th align="left" valign="top" scope="row">&nbsp;</th>
      <td valign="top">
        
      </td>
      </tr>
    <tr style="display:none">
      <th align="left" valign="top" scope="row">End Date:</th>
      <td valign="top">
          <input name="end_dateInput" class="modalInput task input_class" id="end_dateInput" style="width:150px" placeholder="12/12/2030" value="<%=end_date %>" />
	</td>
      </tr>
    <tr style="display:none">
      <th align="left" valign="top" scope="row" nowrap="nowrap">Follow Up:</th>
      <td valign="top"><input name="callback_dateInput" class="modalInput task input_class" id="callback_dateInput" style="width:150px" placeholder="12/12/2030 10:15am" value="<%=callback_date %>" />
      </td>
      </tr>
</table>
</form>
<div id="task_history" style="text-align:right; margin-top:-50px"><button class="btn btn-primary btn-sm" role="button" id="show_history" title="Show task track history">Task History</button></div>
</div>
<?php if ($_SESSION["user_customer_id"]==1033) { ?>
<div id="billing_holder" style="display:none; padding-right:15px"></div>
<?php } ?>
<div id="addressGrid" style="display:none">
    <table id="address">
      <tr style="display:none">
        <td class="label">Street address</td>
        <td class="slimField"><input class="field" id="street_number_task"
              disabled="true"></input></td>
        <td class="wideField" colspan="2"><input class="field" id="route_task"
              disabled="true"></input></td>
      </tr>
      <tr>
        <td class="wideField" colspan="4">
            <input class="field" id="street_task"></input>&nbsp;<input class="field" id="city_task"style="width:100px"></input>&nbsp;<input class="field"
              id="administrative_area_level_1_task" disabled="true" style="width:30px"></input>&nbsp;<input class="field" id="postal_code_task"
              disabled="true" style="width:50px"></input>
        </td>
      </tr>
      <tr style="display:none">
        <td class="label">City</td>
        <td class="wideField" colspan="3">
            <input class="field" id="locality_task"
              disabled="true"></input>
            <input class="field" id="sublocality_task"
              disabled="true"></input>
              <input class="field" id="neighborhood_task"
              disabled="true"></input>
        </td>
      </tr>
      <tr style="display:none">
        <td class="label">Country</td>
        <td class="wideField" colspan="3"><input class="field"
              id="country_task" disabled="true"></input></td>
      </tr>
    </table>
</div>
<div id="task_all_done"></div>
<script language="javascript">
$( "#task_all_done" ).trigger( "click" );
</script>
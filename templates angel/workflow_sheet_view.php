<?php
include("../api/manage_session.php");
session_write_close();

include("../api/connection.php");

$customer_id = $_SESSION['user_customer_id'];

$sql = "SELECT data_source 
FROM ikase.cse_customer
WHERE customer_id = $customer_id";

try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	//$stmt->bindParam("customer_id", $customer_id);
	$stmt->execute();
	$cus =  $stmt->fetchObject();
	
	$stmt->closeCursor(); $stmt = null; $db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage(), "sql"=>$sql));
	echo json_encode($error);
}

$data_source = $cus->data_source;

$db_name = "ikase";
if ($data_source!="") {
	$db_name .= "_" . $data_source;
}
$order_by = "ORDER BY casestatus";
$sql = "SELECT casestatus_id,  casestatus_uuid, casestatus, law, deleted
	FROM `" . $db_name . "`.cse_casestatus cstat
	WHERE 1
	AND deleted = 'N'
	" . $order_by;

try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$statuses =  $stmt->fetchAll(PDO::FETCH_OBJ);
	
	$stmt->closeCursor(); $stmt = null; $db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage(), "sql"=>$sql));
	echo json_encode($error);
}

$casestatus_options = "<option value=''>Select from List</option>";
$option_intake = "<option value='Intake'>Intake</option>";
$casestatus_options .= "" . $option_intake;

$arrDeletedKaseStatus = array();
foreach($statuses as $stat) {
	$casestatus_id = $stat->casestatus_id;
	$casestatus_uuid = $stat->casestatus_uuid;
	$casestatus = $stat->casestatus;
	$law = $stat->law;
	$deleted = $stat->deleted;
	
	$option = "<option value='" . $casestatus . "' class='" . $law . "_status_option'>" . $casestatus . "</option>";
	$casestatus_options .= "" . $option;
	if ($deleted=="Y") {
		$arrDeletedKaseStatus[] = $casestatus;
	}
}

$option_intake = "<option value='REJECTED'>Rejected</option>";
$casestatus_options .= "" . $option_intake;

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

$arrTaskTypeOptions = array();
foreach($task_types as $typ) {
	$task_type_id = $typ->task_type_id;
	$task_type = $typ->task_type;
	$option = "<option value='" . strtolower($task_type) . "'>" . ucwords($task_type) . "</option>";
	$arrTaskTypeOptions[] = $option;
}
$task_type_options = implode("", $arrTaskTypeOptions);
?>
<div class="white_text" style="background: url(img/glass_edit_header_new.png); padding:5px">
	<div style="font-size:1.6em; margin-bottom:20px" id="workflow_title">New Workflow</div>
    <table width="1000px" cellpadding="2" cellspacing="0" id="workflow_table">
        <tr>
          <th align="left" valign="top">Case Type</th>
          <th colspan="4" align="left" valign="top">
          	<div style="float:right; display:none" id="workflow_number_holder">
				<label>Workflow #:&nbsp;</label>
                <span id="workflow_prefix" class="white_text"></span>
                <input type="text" id="workflow_number" name="workflow_number" value="<%=workflow_number %>" class="workflow_value">            	
            </div>
            
            <select name="case_typeInput" id="case_typeInput" class="workflow_value input_class" style="width:300px" parsley-error-message="" required="">
                <option value="" selected>Select from List</option>
                <option value="WCAB">WCAB</option>
                <option value="NewPI">Personal Injury</option>
                <option value="social_security">Social Security</option>
                <option value="class_action">Class Action</option>
                <option value="civil">Civil</option>
                <option value="employment_law">Employment</option>
                <option value="immigration">Immigration</option>
                <option value="WCAB_Defense">WCAB Defense</option>
            </select>
            <span id="case_typeSpan" class="white_text"></span>
            <input type="hidden" id="workflow_id" name="workflow_id"  value="<%=workflow_id %>">
          </th>
      </tr>
      <tr id="workflow_buttons_holder" style=" display:none; ">
          <th align="left" valign="top">Description</th>
          <td colspan="4" align="left" valign="top">
          	<div style="float:right;margin-top:20px">
                <div>
                    <button class="btn btn-primary action_button" id="action_date">Add Date Milestone</button>
                    &nbsp;
                    <button class="btn btn-primary action_button" id="action_status">Add Status Change</button>
                     &nbsp;
                    <button class="btn btn-primary action_button" id="action_event">Event Scheduled</button>
                     &nbsp;
                    <button class="btn btn-primary action_button" id="action_task">Task Assigned</button>
                    <button class="btn" id="reset_action_button" style="display:none" role="button">Reset</button>
                    <!--
                    &nbsp;
                    <button class="btn btn-primary action_button" id="action_letter">Letter Sent</button>
                    &nbsp;
                    <button class="btn btn-primary action_button" id="action_form">Form Filled</button>
                    -->
                </div>
            </div>
          	<textarea name="workflow_description" id="workflow_description"  style="width:300px; height:75px" class="workflow_value"><%=description %></textarea>
          </td>
      </tr>
      <tr>
          <th colspan="5" align="left" valign="top"><hr /></th>
      </tr>
      <tr>
          <td colspan="5" align="left" valign="top" style="font-style:italic" class="white_text">
          	iKase calculates only <strong>Weekday</strong> and <strong>Non-Holiday</strong> future dates.
          </th>
      </tr>
      <tr>
          <th colspan="5" align="left" valign="top"><hr /></th>
      </tr>
	</table>
    <table width="1000px" cellpadding="2" cellspacing="0" id="triggers_table">
      <tr id="triggers_headers" style="display:none">
          <th align="left" valign="top" style="border-bottom:1px solid white">Trigger</th>
          <th align="left" valign="top" style="border-bottom:1px solid white">Create</th>
          <th align="left" valign="top" style="border-bottom:1px solid white">
          &nbsp;Days/Months/Years&nbsp;&nbsp;
          </th>
          <th align="left" valign="top" style="border-bottom:1px solid white">Interval</th>
          <th align="left" valign="top" style="border-bottom:1px solid white">When</th>
          <th align="left" valign="top" style="border-bottom:1px solid white">&nbsp;</th>
          <th align="left" valign="top" style="border-bottom:1px solid white">Assign</th>
          <th align="left" valign="top" style="border-bottom:1px solid white">Instructions</th>
          <!--<th align="left" valign="top">Action</th>-->
      </tr>
      <tr class="trigger_row_date" id="trigger_row_date_0" style="display:none">
      		<td align="left" valign="top" style="padding-top:15px; border-bottom:1px solid white; padding-bottom:5px" nowrap="nowrap">
            	<span id="trigger_action_date_0">Date</span>
            </td>
      		<td align="left" valign="top" style="padding-top:15px; border-bottom:1px solid white; padding-bottom:5px">
            	<select id="operation_date_0">
                	<option value="" selected>Select from List</option>
                    <option value="event">Event</option>
                    <option value="message">Notification</option>
                    <option value="task" selected="selected">Task</option>
                </select>
            </td>
            <td align="left" valign="top" style="padding-top:15px; border-bottom:1px solid white; padding-bottom:5px">
                <input type="number" style="width:100px" step="0.1" min="0" name="trigger_time_date_0" id="trigger_time_date_0" value="0">
                <input type="hidden" id="trigger_id_date_0" name="trigger_id_date_0" value="-1">
            </td>
            <td align="left" valign="top" style="padding-top:15px; border-bottom:1px solid white; padding-bottom:5px">
                <select id="trigger_interval_date_0" name="trigger_interval_date_0">
                    <option value="" selected>Select from List</option>
                    <option value="days">Days</option>
                    <option value="months">Months</option>
                    <option value="years">Years</option>
                </select>
            </td>
            <td align="left" valign="top" style="padding-top:15px; border-bottom:1px solid white; padding-bottom:5px">
                <select id="trigger_date_0" name="trigger_date_0">
                    <option value="" selected>Select from List</option>
                    <option value="B">Before</option>
                    <option value="A">After</option>
                </select>
            </td>
            <td align="left" valign="top" style="padding-top:15px; border-bottom:1px solid white; padding-bottom:5px">
                <select id="trigger_actual_date_0" name="trigger_date_0" style="width:200px">
                    <option value="" selected>Select Trigger Date List</option>
                    <option value="injury_date">DOI/DOL</option>
                    <option value="statute_date">SOL</option>
                    <option value="intake_date">Intake</option>
                    <option value="complaint_date">Complaint</option>
                </select>
            </td>
            <td align="left" valign="top" nowrap="nowrap" style="padding-top:15px; border-bottom:1px solid white; padding-bottom:5px">
            	<div>
                    <input type="checkbox" id="trigger_assign_date_satty_0" checked="checked" />&nbsp;Supv Atty
                    &nbsp;|&nbsp;
                    <input type="checkbox" id="trigger_assign_date_atty_0" checked="checked" />&nbsp;Atty
                    &nbsp;|&nbsp;
                    <input type="checkbox" id="trigger_assign_date_coord_0" checked="checked" />&nbsp;Coord
                    &nbsp;|&nbsp;
                    <input type="checkbox" id="trigger_assign_date_other_0" class="assign_other" />&nbsp;Other
                </div>
                <div id="notify_holder_date_0" style="display:none">
                	<div>Assignee</div>
                  	<input type="text" class="trigger_notify" id="trigger_notify_date_0" style="width:102px" />
                </div>
            </td>
            <td align="left" valign="top" style="padding-top:15px; border-bottom:1px solid white; padding-bottom:5px">
                <textarea id="trigger_description_date_0" name="trigger_description_date_0" style="width:450px; height:81px"></textarea>
           </td>
           <td align="right" valign="top" style="padding-top:15px; border-bottom:1px solid white; padding-bottom:5px">
          	<button class="btn btn-success btn-sm save_trigger" id="save_trigger_date_0" style="width:150px; margin-bottom:10px">Save Date Milestone</button>
            <button class="btn btn-danger btn-sm delete_trigger" id="delete_trigger_date_0" style="display:none">Delete</button>
          </td>
           <!--
            <td align="left" valign="top">
                <select id="trigger_type" name="trigger_type">
                    <option value="" selected>Select from List</option>
                    <option value="task">Task</option>
                    <option value="form">Form</option>
                    <option value="letter">Letter</option>
                    <option value="event">Complaint</option>
                </select>
            </td>
            -->
        </tr>
        
        <tr class="trigger_row_status" id="trigger_row_status_0" style="display:none">
        	<td align="left" valign="top" style="padding-top:15px; border-bottom:1px solid white; padding-bottom:5px" nowrap="nowrap">
            	<span id="trigger_action_status_0">Status</span>
            </td>
        	<td align="left" valign="top" style="padding-top:15px; border-bottom:1px solid white; padding-bottom:5px">
            	<select id="operation_status_0">
                	<option value="" selected>Select from List</option>
                    <option value="event">Event</option>
                    <option value="message">Notification</option>
                    <option value="task" selected="selected">Task</option>
                </select>
            </td>
            <td align="left" valign="top" style="padding-top:15px; border-bottom:1px solid white; padding-bottom:5px">
                <input type="number" step="0.1" min="0" style="width:100px" name="trigger_time_status_0" id="trigger_time_status_0" value="0">
                <input type="hidden" id="trigger_id_status_0" name="trigger_id_status_0" value="-1">
            </td>
            <td align="left" valign="top" style="padding-top:15px; border-bottom:1px solid white; padding-bottom:5px">
                <select id="trigger_interval_status_0" name="trigger_interval_status_0">
                  <option value="" selected>Select from List</option>
                    <option value="days">Days</option>
                    <option value="months">Months</option>
                    <option value="years">Years</option>
              </select>
          </td>
            <td align="left" valign="top" style="padding-top:15px; border-bottom:1px solid white; padding-bottom:5px">
                <select id="trigger_status_0" name="trigger_status_0">
                    <option value="" selected>Select from List</option>
                    <option value="B">Before</option>
                    <option value="A">After</option>
                </select>
            </td>
            <td align="left" valign="top" style="padding-top:15px; border-bottom:1px solid white; padding-bottom:5px">
                <select id="trigger_actual_status_0" name="trigger_status_status_0" style="width:200px">
                    <option value="" selected>Select Trigger Status from List</option>
                    <?php echo $casestatus_options; ?>
                </select>
            </td>
            <td align="left" valign="top" nowrap="nowrap" style="padding-top:15px; border-bottom:1px solid white; padding-bottom:5px">
            	<div>
                    <input type="checkbox" id="trigger_assign_status_satty_0" checked="checked" />&nbsp;Supv Atty
                    &nbsp;|&nbsp;
                    <input type="checkbox" id="trigger_assign_status_atty_0" checked="checked" />&nbsp;Atty
                    &nbsp;|&nbsp;
                    <input type="checkbox" id="trigger_assign_status_coord_0" checked="checked" />&nbsp;Coord
                	&nbsp;|&nbsp;
                    <input type="checkbox" id="trigger_assign_status_other_0" class="assign_other" />&nbsp;Other
                </div>
                <div id="notify_holder_status_0" style="display:none">
                	<div>Assignee</div>
                   	<input type="text" class="trigger_notify" id="trigger_notify_status_0" style="width:62px" />
                </div>
            </td>
            <td align="left" valign="top" style="padding-top:15px; border-bottom:1px solid white; padding-bottom:5px">
                <textarea id="trigger_description_status_0" name="trigger_description_status_0" style="width:450px; height:81px"></textarea>
           </td>
           <td align="right" valign="top" style="padding-top:15px; border-bottom:1px solid white; padding-bottom:5px">
          	<button class="btn btn-success btn-sm save_trigger" id="save_trigger_status_0" style="width:150px; margin-bottom:10px">Save Status Milestone</button>
            <button class="btn btn-danger btn-sm delete_trigger" id="delete_trigger_status_0" style="display:none">Delete</button>
          </td>
        </tr>
        
        <tr class="trigger_row_task" id="trigger_row_task_0" style="display:none">
        	<td align="left" valign="top" style="padding-top:15px; border-bottom:1px solid white; padding-bottom:5px" nowrap="nowrap">
            	<span id="trigger_action_task_0">Task</span>
            </td>
        	<td align="left" valign="top" style="padding-top:15px; border-bottom:1px solid white; padding-bottom:5px">
            	<select id="operation_task_0">
                	<option value="" selected>Select from List</option>
                    <option value="event">Event</option>
                    <option value="message">Notification</option>
                    <option value="task" selected="selected">Task</option>
                </select>
            </td>
            <td align="left" valign="top" style="padding-top:15px; border-bottom:1px solid white; padding-bottom:5px">
                <select style="width:200px" name="trigger_time_task_0" id="trigger_time_task_0" class="trigger_time">
                	<option value="-0.1">Same Day as Task Created</option>
                    <option value="-1">Day after Task Created</option>
                    <option value="-7">Week after Task Created</option>
                    <option value="-31">Month after Task Created</option>
                    <option value="-365">Year after Task Created</option>
                	<option value="0" selected="selected">0</option>
                    <option value="1">1</option>
                    <option value="1.5">1.5</option>
                    <option value="2">2</option>
                    <option value="2.5">2.5</option>
                    <?php for ($int=3; $int < 32; $int++) {
						echo '<option value="' . $int . '">' . $int . '</option>';
					}
					?>
                    
                </select>
                <input type="hidden" id="trigger_id_task_0" name="trigger_id_task_0" value="-1">
            </td>
            <td align="left" valign="top" style="padding-top:15px; border-bottom:1px solid white; padding-bottom:5px">
                <select id="trigger_interval_task_0" name="trigger_interval_task_0">
                  <option value="" selected>Select from List</option>
                    <option value="days">Days</option>
                    <option value="months">Months</option>
                    <option value="years">Years</option>
              </select>
          </td>
            <td align="left" valign="top" style="padding-top:15px; border-bottom:1px solid white; padding-bottom:5px">
                <select id="trigger_task_0" name="trigger_task_0">
                    <option value="" selected>Select from List</option>
                    <option value="B">Before</option>
                    <option value="A">After</option>
                </select>
            </td>
            <td align="left" valign="top" style="padding-top:15px; border-bottom:1px solid white; padding-bottom:5px">
                <select id="trigger_actual_task_0" name="trigger_task_task_0" style="width:200px">
                    <option value="" selected>Select Trigger Task from List</option>
                    <?php echo $task_type_options; ?>
                </select>
            </td>
            <td align="left" valign="top" nowrap="nowrap" style="padding-top:15px; border-bottom:1px solid white; padding-bottom:5px">
            	<div>
                    <input type="checkbox" id="trigger_assign_task_satty_0" checked="checked" />&nbsp;Supv Atty
                    &nbsp;|&nbsp;
                    <input type="checkbox" id="trigger_assign_task_atty_0" checked="checked" />&nbsp;Atty
                    &nbsp;|&nbsp;
                    <input type="checkbox" id="trigger_assign_task_coord_0" checked="checked" />&nbsp;Coord
                	&nbsp;|&nbsp;
                    <input type="checkbox" id="trigger_assign_task_other_0" class="assign_other" />&nbsp;Other
                </div>
                <div id="notify_holder_task_0" style="display:none">
                	<div>Assignee</div>
                   	<input type="text" class="trigger_notify" id="trigger_notify_task_0" style="width:62px" />
                </div>
            </td>
            <td align="left" valign="top" style="padding-top:15px; border-bottom:1px solid white; padding-bottom:5px">
                <textarea id="trigger_description_task_0" name="trigger_description_task_0" style="width:450px; height:81px"></textarea>
           </td>
           <td align="right" valign="top" style="padding-top:15px; border-bottom:1px solid white; padding-bottom:5px">
          	<button class="btn btn-success btn-sm save_trigger" id="save_trigger_task_0" style="width:150px; margin-bottom:10px">Save Task Trigger</button>
            <button class="btn btn-danger btn-sm delete_trigger" id="delete_trigger_task_0" style="display:none">Delete</button>
          </td>
        </tr>
        
        <tr class="trigger_row_event" id="trigger_row_event_0" style="display:none">
        	<td align="left" valign="top" style="padding-top:15px; border-bottom:1px solid white; padding-bottom:5px" nowrap="nowrap">
            	<span id="trigger_action_event_0">Event</span>
            </td>
        	<td align="left" valign="top" style="padding-top:15px; border-bottom:1px solid white; padding-bottom:5px">
            	<select id="operation_event_0">
                	<option value="" selected>Select from List</option>
                    <option value="event">Event</option>
                    <option value="message">Notification</option>
                    <option value="task" selected="selected">Task</option>
                </select>
            </td>
            <td align="left" valign="top" style="padding-top:15px; border-bottom:1px solid white; padding-bottom:5px">
                <select style="width:200px" name="trigger_time_event_0" id="trigger_time_event_0" class="trigger_time">
                	<option value="-0.1">Same Day as Event Created</option>
                    <option value="-1">Day after Event Created</option>
                    <option value="-7">Week after Event Created</option>
                    <option value="-31">Month after Event Created</option>
                    <option value="-365">Year after Event Created</option>
                	<option value="0" selected="selected">0</option>
                    <option value="1">1</option>
                    <option value="1.5">1.5</option>
                    <option value="2">2</option>
                    <option value="2.5">2.5</option>
                    <?php for ($int=3; $int < 32; $int++) {
						echo '<option value="' . $int . '">' . $int . '</option>';
					}
					?>
                    
                </select>
                <input type="hidden" id="trigger_id_event_0" name="trigger_id_event_0" value="-1">
            </td>
            <td align="left" valign="top" style="padding-top:15px; border-bottom:1px solid white; padding-bottom:5px">
                <select id="trigger_interval_event_0" name="trigger_interval_event_0">
                  <option value="" selected>Select from List</option>
                    <option value="days">Days</option>
                    <option value="months">Months</option>
                    <option value="years">Years</option>
              </select>
          </td>
            <td align="left" valign="top" style="padding-top:15px; border-bottom:1px solid white; padding-bottom:5px">
                <select id="trigger_event_0" name="trigger_event_0">
                    <option value="" selected>Select from List</option>
                    <option value="B">Before</option>
                    <option value="A">After</option>
                </select>
            </td>
            <td align="left" valign="top" style="padding-top:15px; border-bottom:1px solid white; padding-bottom:5px">
                <select id="trigger_actual_event_0" name="trigger_event_event_0" style="width:200px">               
                    <%= select_options %>
                </select>
            </td>
            <td align="left" valign="top" nowrap="nowrap" style="padding-top:15px; border-bottom:1px solid white; padding-bottom:5px">
            	<div>
                    <input type="checkbox" id="trigger_assign_event_satty_0" checked="checked" />&nbsp;Supv Atty
                    &nbsp;|&nbsp;
                    <input type="checkbox" id="trigger_assign_event_atty_0" checked="checked" />&nbsp;Atty
                    &nbsp;|&nbsp;
                    <input type="checkbox" id="trigger_assign_event_coord_0" checked="checked" />&nbsp;Coord
                	&nbsp;|&nbsp;
                    <input type="checkbox" id="trigger_assign_event_other_0" class="assign_other" />&nbsp;Other
                </div>
                <div id="notify_holder_event_0" style="display:none">
                	<div>Assignee</div>
                   	<input type="text" class="trigger_notify" id="trigger_notify_event_0" style="width:62px" />
                </div>
            </td>
            <td align="left" valign="top" style="padding-top:15px; border-bottom:1px solid white; padding-bottom:5px">
                <textarea id="trigger_description_event_0" name="trigger_description_event_0" style="width:450px; height:81px"></textarea>
           </td>
           <td align="right" valign="top" style="padding-top:15px; border-bottom:1px solid white; padding-bottom:5px">
          	<button class="btn btn-success btn-sm save_trigger" id="save_trigger_event_0" style="width:150px; margin-bottom:10px">Save Event Trigger</button>
            <button class="btn btn-danger btn-sm delete_trigger" id="delete_trigger_event_0" style="display:none">Delete</button>
          </td>
        </tr>
        <!--
        <tr class="trigger_row trigger_row_date" id="trigger_row_date_1" style="display:none">
            <td align="left" valign="top">
                <input type="number" style="width:100px" name="trigger_interval_1" id="trigger_interval_1" value="0">
            </td>
            <td align="left" valign="top">
                <select id="trigger_interval_1" name="trigger_interval_1">
                  <option value="" selected>Select from List</option>
                    <option value="days">Days</option>
                    <option value="months">Months</option>
                    <option value="years">Years</option>
              </select>
          </td>
            <td align="left" valign="top">
                <select id="trigger_1" name="trigger_1">
                    <option value="" selected>Select from List</option>
                    <option value="before">Before</option>
                    <option value="after">After</option>
                </select>
            </td>
            <td align="left" valign="top">
                <select id="trigger_date_1" name="trigger_date_1">
                    <option value="" selected>Select from List</option>
                    <option value="injury_date">DOI/DOL</option>
                    <option value="statute_date" selected="selected">SOL</option>
                    <option value="intake_date">Intake</option>
                    <option value="complaint_date">Complaint</option>
                </select>
            </td>
            <td align="left" valign="top">
                <textarea id="trigger_description_1" name="trigger_description_1" style="width:450px"></textarea>
           </td>
           <td align="right" valign="top" style="padding-top:15px">
          	<button class="btn btn-success btn-sm save_trigger" id="save_trigger_1">Save</button>
          </td>
        </tr>
        <tr class="trigger_row trigger_row_status" id="trigger_row_status_1" style="display:none">
            <td align="left" valign="top">
                <input type="number" style="width:100px" name="trigger_interval_1" id="trigger_interval_1" value="0">
            </td>
            <td align="left" valign="top">
                <select id="trigger_interval_1" name="trigger_interval_1">
                  <option value="" selected>Select from List</option>
                    <option value="days">Days</option>
                    <option value="months">Months</option>
                    <option value="years">Years</option>
              </select>
          </td>
            <td align="left" valign="top">
                <select id="trigger_1" name="trigger_1">
                    <option value="" selected>Select from List</option>
                    <option value="before">Before</option>
                    <option value="after">After</option>
                </select>
            </td>
            <td align="left" valign="top">
                <select id="trigger_status_1" name="trigger_status_1">
                    <option value="" selected>Select from List</option>
                    <?php echo $casestatus_options; ?>
                </select>
            </td>
            <td align="left" valign="top">
                <textarea id="trigger_description_1" name="trigger_description_1" style="width:450px"></textarea>
           </td>
           <td align="right" valign="top" style="padding-top:15px">
          	<button class="btn btn-success btn-sm save_trigger" id="save_trigger_1">Save</button>
          </td>
        </tr>
        -->
    </table>
</div>
<div id="workflow_sheet_all_done"></div>
<script language="javascript">
$("#workflow_sheet_all_done").trigger( "click" );
</script>
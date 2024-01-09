<?php
require_once('../shared/legacy_session.php');
if (!isset($_SESSION['user_data_path'])) {
	$_SESSION['user_data_path'] = '';
}
session_write_close();

include ("../api/connection.php");
include ("../browser_detect.php");

$blnIPad = isPad();

$arrEventCalendars = array();

$sql = "SELECT *
		FROM cse_calendar 
		WHERE 1
		AND customer_id = '" . $_SESSION['user_customer_id'] . "'
		ORDER by sort_order";
		
$db = getConnection();

try {
	$stmt = $db->query($sql);
	$customer_calendars = $stmt->fetchAll(PDO::FETCH_OBJ);
} catch(PDOException $e) {
	$error = array("error nav"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}

$blnCustomCalendars = false;
foreach($customer_calendars as $customer_calendar) {
	if ($customer_calendar->sort_order != 4 && $customer_calendar->sort_order != 0 && $customer_calendar->sort_order != 1 && $customer_calendar->sort_order != 5) {
		continue;
	}
	//may have to skip the personal kalendar
	if (isset($_SESSION['personal_calendar'])) {
		if ($_SESSION['personal_calendar']!="Y") {
			if ($customer_calendar->sort_order==5) {
				continue;
			}
		} else {
			//rename the calendar with their initials
			//$calendar_original = $customer_calendar->calendar;
			//$customer_calendar->calendar = str_replace("Employee", strtoupper($_SESSION['user_nickname']), $customer_calendar->calendar);
		}
	} else {
		if ($customer_calendar->sort_order==5) {
			continue;
		}
	}
	if ($customer_calendar->sort_order!=4) {
		$calendar_original = str_replace(" ", "_", $customer_calendar->calendar);
		//echo $calendar_original;
	} else {
		$calendar_original = $customer_calendar->calendar;
		//echo $calendar_original . " 1";
	}
	$menu_item = "<option value='" . $calendar_original . "' id='" . $customer_calendar->calendar_id . "' class='calendar_drop_down_option " . $calendar_original . "'>" . $customer_calendar->calendar . "</option>";
	$arrEventCalendars[] = $menu_item;
}

//die($menu_item);
?>
<div style="float:right">
	<div id="trigger_event_holder">
        <table width="550px" border="0" align="left" cellpadding="3" cellspacing="0" class="event_stuff" id="event_table_screen">
            <tr>
                <th align="left" valign="top" scope="row">Calendar:</th>
                <td colspan="2" valign="top" scope="row">
                    <?php if (count($arrEventCalendars) == 0) { ?>  
                                            Calendars                               
                    <?php } else { ?>
                        <select style="width:200px" id="calendar_drop_down">
                            <?php echo implode("", $arrEventCalendars); ?>
                        </select>
                    <?php } ?>
                    <div id="type_select_holder" style="margin-left:275px; margin-top:-20px">
                        <span style="font-weight:bold">Type:&nbsp;&nbsp;</span><div id="event_type_drop" style="display: inline-block;">
                            <select name="event_typeInput" id="event_typeInput" class="modalInput event input_class" style="height:25px; width:160px; margin-top:-30px; margin-left:0px">
                            <% var select_options = setting_options;
                            select_options = select_options.replace("<option value=''>Filter By Type</option>", "<option value=''>Select from List</option>");
                            select_options = select_options.replace("value='" + occurrence.event_type + "'",  "value='" + occurrence.event_type + "' selected");
                            %>
                                <%= select_options %>
                            </select>
                        </div>
                    </div>
                </td>			
            </tr> 
            <tr>
                <th align="left" valign="top" scope="row">Subject:</th>
                <td valign="top"><input name="event_titleInput" type="text" id="event_titleInput" style="width:470px" class="modalInput event input_class" value="" autocomplete="off" />    </td>
            </tr>
            <tr>
                <th align="left" valign="top" scope="row">Assignee:</th>
                <td>
                    <input name="assigneeInput" type="text" placeholder="off" id="assigneeInput" style="width:433px" class="modalInput event input_class" value="" />
                </td>
                </tr>
            <tr>
                <th align="left" valign="top" scope="row">Location:</th>
                <td><input name="full_addressInput" type="text" id="full_addressInput" style="width:470px" class="modalInput event input_class" placeholder="Enter event address" autocomplete"off" value="" />
                </td>
            </tr>
            
            <tr>
                <th align="left" valign="top" scope="row">&nbsp;</th>
                <td valign="top">                
                	<div style="float:right; margin-right:0px">
                    	<span style="font-weight:bold">Priority:</span>&nbsp;
                        <select name="event_priorityInput" id="event_priorityInput" class="modalInput event input_class" style="width:150px">
                            <option value="high">High</option>
                            <option value="normal" selected>Normal</option>
                            <option value="low">Low</option>
                        </select>
                  	</div>
                    <table>
                        <tr>
                        <td style="font-weight:bold;">Off Calendar:</td>
                        <td>
                            <input class="modalInput event" id="off_calendarInput" name="off_calendarInput" value="Y" type="checkbox" />
                        </td>
                        </tr>
                    </table>
               </td>
          </tr>
            <tr>
                <th align="left" valign="top" scope="row">Duration:</th>
                <td valign="top">
                    <select name="event_durationInput" class="modalInput event input_class" id="event_durationInput" style="width:75px;">
                        <option value="15">15 min</option>
                        <option value="30">30 min</option>
                        <option value="45">45 min</option>
                        <option value="60" selected="selected">1h</option>
                        <option value="75">1h 15 min</option>
                        <option value="90">1h 30 min</option>
                        <option value="105">1h 45 min</option>
                        <option value="120">2h</option>
                        <option value="240">4h</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th align="left" valign="top" scope="row">Details:</th>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td colspan="2" align="left" valign="top" scope="row" id="description_holder">
                <textarea name="event_descriptionInput" id="event_descriptionInput" class="modalInput event input_class"></textarea></td>
            </tr>
        </table>
    </div>
</div>
<table>
    <tr>
      <th align="left" valign="top">Interval</th>
      <td align="left" valign="top"><label for="trigger_interval"></label>
          <input type="number" style="width:100px" name="trigger_interval" id="trigger_interval" value="6">
          &nbsp;
          <select id="trigger_datetype" name="trigger_datetype">
            <option value="">Select from List</option>
            <option value="hours">Hours</option>
            <option value="days">Days</option>
            <option value="months" selected>Months</option>
            <option value="years">Years</option>
          </select>
      </td>
    </tr>
    <tr>
        <th align="left" valign="top">
            Trigger
        </th>
        <td align="left" valign="top">
            <input type="radio" name="radio_trigger" id="trigger_before" value="B" />&nbsp;Before
            &nbsp;|&nbsp;
            <input type="radio" name="radio_trigger" id="trigger_before" value="A" checked="checked" />&nbsp;After
        </td>
    </tr>
	<tr>
	  <th align="left" valign="top">&nbsp;</th>
	  <td align="left" valign="top">1 month&nbsp;|&nbsp;6 months&nbsp;|&nbsp;1 year&nbsp;|&nbsp;1.5 year&nbsp;|&nbsp;2.5 years</td>
  </tr>
	<tr>
	  <th align="left" valign="top">Date</th>
	  <td align="left" valign="top">
      	<select id="trigger_date" name="trigger_date" size="4">
        	<option value="injury_date">DOI/DOL</option>
            <option value="statute_date" selected="selected">SOL</option>
            <option value="intake_date">Intake</option>
            <option value="complaint_date">Complaint</option>
        </select>
      </td>
  </tr>
	<tr>
	  <th align="left" valign="top">Type</th>
	  <td align="left" valign="top">
      	<div>
        	<div style="float:right">
            	<a id="add_trigger_task" class="add_trigger">+</a>&nbsp;/&nbsp;<a id="subtract_trigger_task" class="subtract_trigger">-</a>
            </div>
            <input type="number" style="width:100px" id="trigger_task" value="0">&nbsp;Task(s)</div>
        <div>
        	<div style="float:right">
            	<a id="add_trigger_event" class="add_trigger">+</a>&nbsp;/&nbsp;<a id="subtract_trigger_event" class="subtract_trigger">-</a>
            </div>
            <input type="number" style="width:100px" id="trigger_event" value="0">&nbsp;Calendar Event(s)
        </div>
        <div>
        	<div style="float:right">
            	<a id="add_trigger_message" class="add_trigger">+</a>&nbsp;/&nbsp;<a id="subtract_trigger_message" class="subtract_trigger">-</a>
            </div>
            <input type="number" style="width:100px" id="trigger_message" value="0">&nbsp;Send Message(s)
        </div>
        <div>
        	<div style="float:right">
            	<a id="add_trigger_letter" class="add_trigger">+</a>&nbsp;/&nbsp;<a id="subtract_trigger_letter" class="subtract_trigger">-</a>
            </div>
            <input type="number" style="width:100px" id="trigger_letter" value="0">&nbsp;Generate Letter(s)
        </div>
        <div>
        	<div style="float:right">
            	<a id="add_trigger_form" class="add_trigger">+</a>&nbsp;/&nbsp;<a id="subtract_trigger_form" class="subtract_trigger">-</a>
            </div>
            <input type="number" style="width:100px" id="trigger_form" value="0">&nbsp;Generate Form(s)
        </div>
      </td>
  </tr>
</table>

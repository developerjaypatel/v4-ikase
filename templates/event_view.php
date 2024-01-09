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
<% if (blnShowBilling) { %>
<div style="margin-top:-10px; padding-bottom:5px">
	<input type="button" class="btn btn-xs" id="view_event" value="Event">
	<input type="button" class="btn btn-xs" id="view_reminders" value="Reminders"><span id="reminders_warning" style="color:red;display:none">Please enter Assignees before setting reminders.</span>
	<% if(occurrence.reminder_count > 0){	 %>
	 	<span class="glyphicon glyphicon-bell" style="color:green"></span>
	<% } %>
    <input type="button" class="btn btn-xs" id="view_billable" value="Bill This" title="Click to Create/Update Billing related to this Event" style="display:none">
   <div style="display:none" id="cancel_billable_holder">
    	&nbsp;
    	<input type="button" class="btn btn-xs btn-warning" id="cancel_billable" value="Cancel Bill" title="Click to Clear Billing related to this Event">
    </div>
</div>
<% } %>
<div id="event_screen" class="event" style="margin-left:10px;">
<form id="event_form" name="event_form" method="post" action="">
<input id="table_name" name="table_name" type="hidden" value="event" />
<input id="table_id" name="table_id" type="hidden" value="<%=occurrence.id %>" />
<input id="injury_id" name="injury_id" type="hidden" value="<%=occurrence.injury_id %>" />
<input id="calendar_id" name="calendar_id" type="hidden" value="<%=current_calendar_id %>" />
<input id="case_id" name="case_id" type="hidden" value="" />
<input id="user_id" name="user_id" type="hidden" value="<%=occurrence.user_id %>" />
<input id="event_kind" name="event_kind" type="hidden" value="<%=occurrence.event_kind %>" />
<table width="600px" border="0" align="left" cellpadding="3" cellspacing="0" class="recurrent_stuff" style="display:none" id="recurrent_table_screen">
	<tr id="case_id_row">
        <th align="left" valign="top" scope="row">Repeats:</th>
        <td colspan="2" valign="top">
            <select name="recurrent_repeatInput" id="recurrent_repeatInput" class="modalInput event input_class" style="width:150px">
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="weekdays">Every Weekday (Mon-Fri)</option>
                <option value="weekday_odd">Every Monday, Wednesday, and Friday</option>
                <option value="weekday_even">Every Tuesday, and Thursday</option>
                <option value="monthly">Monthly</option>
                <option value="yearly">Yearly</option>
            </select>        
        </td>
	</tr>
	<tr id="repeat_row">
		<th align="left" valign="top" scope="row">Repeat Every:</th>
		<td>
			<select name="recurrent_interval" id="recurrent_interval">
				<option value="1">1</option>
				<option value="2">2</option>
				<option value="3">3</option>
				<option value="4">4</option>
				<option value="5">5</option>
				<option value="6">6</option>
				<option value="7">7</option>
				<option value="8">8</option>
				<option value="9">9</option>
				<option value="10">10</option>
				<option value="11">11</option>
				<option value="12">12</option>
				<option value="13">13</option>
				<option value="14">14</option>
				<option value="15">15</option>
				<option value="16">16</option>
				<option value="17">17</option>
				<option value="18">18</option>
				<option value="19">19</option>
				<option value="20">20</option>
				<option value="21">21</option>
				<option value="22">22</option>
				<option value="23">23</option>
				<option value="24">24</option>
				<option value="25">25</option>
				<option value="26">26</option>
				<option value="27">27</option>
				<option value="28">28</option>
				<option value="29">29</option>
				<option value="30">30</option>
			</select>    
        </td>
    </tr>
    <tr id="repeat_row_by" style="display:none">
		<th align="left" valign="top" scope="row">Repeat By:</th>
		<td><input type="radio" name="repeat_by_radio" id="recurrent_month_day" value="" />&nbsp;&nbsp;day of the month&nbsp;&nbsp;<input type="radio" name="repeat_by_radio" id="recurrent_week_day" value="" />&nbsp;&nbsp;day of the week</td>
    </tr>
	<tr id="repeat_row_days">
		<th align="left" valign="top" scope="row">Repeat On:</th>
		<td><input type="checkbox" name="recurrent_mon" id="recurrent_mon" />&nbsp;&nbsp;M&nbsp;&nbsp;<input type="checkbox" name="recurrent_tue" id="recurrent_tue" />&nbsp;&nbsp;T&nbsp;&nbsp;<input type="checkbox" name="recurrent_wed" id="recurrent_wed" />&nbsp;&nbsp;W&nbsp;&nbsp;<input type="checkbox" name="recurrent_thurs" id="recurrent_thurs" />&nbsp;&nbsp;Th&nbsp;&nbsp;<input type="checkbox" name="recurrent_fri" id="recurrent_fri" />&nbsp;&nbsp;F&nbsp;&nbsp;<input type="checkbox" name="recurrent_sat" id="recurrent_sat" />&nbsp;&nbsp;Sat&nbsp;&nbsp;<input type="checkbox" name="recurrent_sun" id="recurrent_sun" />&nbsp;&nbsp;Sun</td>
		</tr>
	<tr id="repeat_row_days_start">
		<th align="left" valign="top" scope="row">Starts On:</th>
		<td valign="top"><input name="recurrent_dateandtimeInput" class="modalInput event input_class" id="recurrent_dateandtimeInput" style="width:150px;display:" placeholder="12/12/2012" value="" /></td>
		</tr>
	<tr id="repeat_row_days_end">
		<th align="left" valign="top" scope="row">Ends:</th>
		<td><input type="radio" name="recurrent_end_radio" id="never" value="" class="end_radio" /> 
		Never</td>
	</tr>
	<tr>
		<th align="left" valign="top" scope="row">&nbsp;</th>
		<td>
			<input type="radio" name="recurrent_end_radio" id="after_date" value="radio" class="end_radio" />
			After 
		
			<input type="text" name="recurrent_end_after_dateInput" id="end_after_dateInput" style="width:30px; height:23px" value="1" onkeyup="noAlpha(this, '0')" onkeypress="noAlpha(this, '0')" class="end_radio" /> 
			occurences
		</td>
	</tr>
	
		<tr>
			<th align="left" valign="top" scope="row">&nbsp;</th>
			<td valign="top">
				<input type="radio" name="recurrent_end_radio" id="on_date" value="radio" class="end_radio" />
				On 
				<input type="text" name="recurrent_end_on_dateInput" id="end_on_dateInput" style="width:100px; height:23px" value="" onkeyup="noAlpha(this, '0')" onkeypress="noAlpha(this, '0')" class="end_radio" />
			</td>
		</tr>
		<tr>
			<th align="left" valign="top" scope="row">&nbsp;</th>
			<td valign="top">&nbsp;</td>
		</tr>
		<tr>
			<th align="left" valign="top" nowrap="nowrap" scope="row">Summary:</th>
			<td valign="top"><span id="summary_span"></span></td>
		</tr>
</table>
<div style="width:350px; display:none; float:right; border:1px solid white;height:550px;overflow-y:scroll;margin-bottom : 20px;" id="parties_list">
</div>
 <table width="550px" border="0" align="left" cellpadding="3" cellspacing="0" class="event_stuff" style="display:" id="event_table_screen">
	<tr style="display:none" id="uneditable_row">
			<td colspan="3">
					<div id="uneditable"></div>
				</td>
		</tr>
		
	<% //if (occurrence.id == "-1") { %>
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
                    	<!-- && occurrence.event_kind!="employee"-->
                        <% if (occurrence.event_kind!="phone_call" && occurrence.event_kind!="intake") { %>
                            <select name="event_typeInput" id="event_typeInput" class="modalInput event input_class" style="height:25px; width:160px; margin-top:-30px; margin-left:0px">
                            <% var select_options = setting_options;
                            select_options = select_options.replace("<option value=''>Filter By Type</option>", "<option value=''>Select from List</option>");
                            select_options = select_options.replace("value='" + occurrence.event_type + "'",  "value='" + occurrence.event_type + "' selected");
                            %>
                                <%= select_options %>
                            </select>
                        <% } %>
                        <% if (occurrence.event_kind=="phone_call") { %>
                                <select name="event_typeInput" id="event_typeInput" class="event input_class hidden" style="height:25px; width:160px; margin-top:-30px; margin-left:0px">
                                    <option value="phone_call" <% if(occurrence.event_kind=="phone_call" || occurrence.event_type=="phone_call") {%>selected<% } %>>Phone Call</option>
                                </select>
                            Phone Call
                        <% } %>
                        <%  if (occurrence.event_kind=="intake") { %>
                                <select name="event_typeInput" id="event_typeInput" class="event input_class hidden" style="height:25px; width:160px; margin-top:-30px; margin-left:0px">
                                <option value="intake" <% if(occurrence.event_kind=="intake" || occurrence.event_type=="intake") {%>selected<% } %>>Intake</option>
                                </select>
                                Intake
                        <% } %>
                        <!--
                        <% if (occurrence.event_kind=="employee") { %>
                                <select name="event_typeInput" id="event_typeInput" class="event input_class hidden" style="height:25px; width:160px; margin-top:-30px; margin-left:0px">
                                <option value="Employee Attendance" <% if(occurrence.event_kind=="employee" || occurrence.event_type=="Employee Attendance") {%>selected<% } %>>Employee Attendance</option>
                                </select>
                                Employee Attendance
                        <% } %>
                        -->
                    </div>
                </div>
			</td>			
		</tr> 
	<% //} %>
	<tr id="case_id_row">
        <th align="left" valign="top" scope="row">Case:</th>
        <td colspan="2" valign="top" id="case_id_holder">
        	<div style="float:right; display:none" id="open_case_holder">
            	<button id="review_case" title="Click to review case in new window" class="btn btn-xs btn-primary">Review Case</button>
            </div>
            <div class="case_input">
                <input name="case_idInput" type="text" id="case_idInput" size="30" class="modal_input event_<%=occurrence.id %>" value="" />
            </div>
            <span id="case_idSpan" style=""></span>
        </td>
	</tr>
	<tr>
		<th align="left" valign="top" scope="row">Subject:</th>
		<td valign="top"><input name="event_titleInput" type="text" id="event_titleInput" style="width:470px" class="modalInput event input_class" value="<%=occurrence.event_title %>" autocomplete="off" />    </td>
	</tr>
	<tr>
		<th align="left" valign="top" scope="row"><% if (occurrence.event_kind=='phone_call') { %>For:<% } else { %>Assignee:<% } %></th>
		<td>
        	<input name="assigneeInput" type="text" placeholder="off" id="assigneeInput" style="width:433px" class="modalInput event input_class" value="<%=occurrence.assignee %>" <% if (occurrence.event_kind=='phone_call') { %>required<% } %> />
            <span id="assigneeSpan"></span>
        </td>
		</tr>
	<tr style="display:<% if (occurrence.event_kind=='phone_call') { %>none<% } %>">
		<th align="left" valign="top" scope="row">Location:</th>
		<td align="left" valign="top" >
        	<input name="full_addressInput" type="text" id="full_addressInput" style="width:470px" class="modalInput event input_class" placeholder="Enter address for Bing lookup" autocomplete"off" value="<%=occurrence.full_address %>" /><div id="google_map" style="display:inline; cursor:pointer; margin-left:5px; opacity:0"><i style="font-size:20px; color:#0FF; cursor:pointer" class="glyphicon glyphicon-map-marker" title="Click to show Map"></i></div>
        	<div id="bing_results" style="position: absolute;z-index: 9999;background: aliceblue;border: 1px solid black;padding: 5px;color: black;left: 105px; display:none; margin-top:-20px"></div>
		</td>
	</tr>
	
	<tr>
		<th align="left" valign="top" scope="row">When:</th>
		<td valign="top">
		<div style="float:right; margin-right:0px">
            <table>
                <tr>
                <td style="font-weight:bold;">Off Calendar:</td>
                <td>
                	<input class="modalInput event" id="off_calendarInput" name="off_calendarInput" value="Y" type="checkbox" <%=occurrence.off_calendar_checked %> />
                </td>
                </tr>
            </table>
		<!--
        <span style="font-weight:bold">Close:</span>&nbsp;
			<input class="modalInput event input_class" id="event_closedateInput" style="width:125px;" placeholder="mm/dd/yyyy" value="" />
            -->
		</div>
		
		<input name="event_dateandtimeInput" class="modalInput event input_class" id="event_dateandtimeInput" style="width:125px;display:<% if (occurrence.event_kind=='phone_call') { %>none<% } %>" placeholder="mm/dd/yyyy" value="<%=occurrence.event_dateandtime %>" />
		<input class="modalInput event input_class original_date" style="width:125px;" placeholder="mm/dd/yyyy" type="hidden" value="<%=occurrence.event_dateandtime %>" />
	 
		<div style="display:inline-block; border:0px solid red" id="reschedule_section">
			&nbsp; Or calculate &nbsp;
				
		<input type="text" value="" name="number_of_days" id="number_of_days" style="width:40px;" onkeyup="noAlpha(this, '0')" onkeypress="noAlpha(this, '0')" />&nbsp;&nbsp; days
				&nbsp;&nbsp;<span id="calculated_dateSpan" class="span_class form_span_vert" style="position:relative; width:20px" ></span>
		</div>
		
		<% if (occurrence.event_kind=='phone_call') { %><%=occurrence.event_dateandtime %><% %><% } %>	
		
				</td>
		</tr>
		<tr>
		<th align="left" valign="top" scope="row">Duration:</th>
		<td valign="top">
		<select name="event_durationInput" class="modalInput event input_class" id="event_durationInput" style="width:75px;display:<% if (occurrence.event_kind=='phone_call') { %>none<% } %>">
				<option value="15" <% if(occurrence.event_duration=="15") {%>selected<% } %>>15 min</option>
				<option value="30" <% if(occurrence.event_duration=="30") {%>selected<% } %>>30 min</option>
				<option value="45" <% if(occurrence.event_duration=="45") {%>selected<% } %>>45 min</option>
				<option value="60" <% if(occurrence.event_duration=="60" || occurrence.event_duration=="") {%>selected<% } %>>1h</option>
				<option value="75" <% if(occurrence.event_duration=="75") {%>selected<% } %>>1h 15 min</option>
				<option value="90" <% if(occurrence.event_duration=="90") {%>selected<% } %>>1h 30 min</option>
				<option value="105" <% if(occurrence.event_duration=="105") {%>selected<% } %>>1h 45 min</option>
				<option value="120" <% if(occurrence.event_duration=="120") {%>selected<% } %>>2h</option>
								<option value="240" <% if(occurrence.event_duration=="240") {%>selected<% } %>>4h</option>
				</select>
		<% if (occurrence.event_kind=='phone_call') { %><%=occurrence.event_dateandtime %><% %><% } %>	
		
				</td>
		</tr>
		<% if (occurrence.event_kind!='phone_call') { %>
	<tr style="display:<% if (occurrence.reminder_id1=="-1") { %>none<% } %>" id="first_reminder_row">
		<th align="left" valign="top" scope="row" nowrap="nowrap">1st Reminder:</th>
		<td class="reminder_stuff">
			<input type="hidden" name="reminder_id1" id="reminder_id1" value="<%=occurrence.reminder_id1 %>" />
			<select name="reminder_type1" id="reminder_type1" class="reminder_field">
					<!--<option value="text">Text</option>-->
					<!--<option value="email" <% if (occurrence.reminder_type1=="email") { %>selected="selected"<% } %>>Email</option>-->
					<option value="interoffice" <% if (occurrence.reminder_type1=="interoffice") { %>selected="selected"<% } %>>Interoffice</option>
				</select>
				<input type="text" name="reminder_interval1" id="reminder_interval1" style="width:30px; height:23px" value="<%=occurrence.reminder_interval1 %>" onkeyup="noAlpha(this, '0')" onkeypress="noAlpha(this, '0')" class="reminder_field" />
		<select name="reminder_span1" id="reminder_span1" class="reminder_field">
				<option value="minutes" <% if (occurrence.reminder_span1=="minutes") { %>selected="selected"<% } %>>Minutes</option>
				<option value="hours" <% if (occurrence.reminder_span1=="hours") { %>selected="selected"<% } %>>Hours</option>
				<option value="days" <% if (occurrence.reminder_span1=="days") { %>selected="selected"<% } %>>Days</option>
				<option value="weeks" <% if (occurrence.reminder_span1=="weeks") { %>selected="selected"<% } %>>Weeks</option>
		</select>
		<span id="reminder_datetime1" style="text-align:left"><%=occurrence.reminder_datetime1 %></span>
		</td>
	</tr>
	<tr style="display:<% if (occurrence.reminder_id2=="-1") { %>none<% } %>" id="second_reminder_row">
		<th align="left" valign="top" scope="row" nowrap="nowrap">2nd Reminder:</th>
		<td class="reminder_stuff">
			<input type="hidden" name="reminder_id2" id="reminder_id2" value="<%=occurrence.reminder_id2 %>" />
				<select name="reminder_type2" id="reminder_type2" class="reminder_field">
					<!--<option value="text">Text</option>
					<option value="email" <% if (occurrence.reminder_type2=="email") { %>selected="selected"<% } %>>Email</option>-->
					<option value="interoffice" <% if (occurrence.reminder_type2=="interoffice") { %>selected="selected"<% } %>>Interoffice</option>
				</select>      
				<input type="text" name="reminder_interval2" id="reminder_interval2" style="width:30px; height:23px" value="<%=occurrence.reminder_interval2 %>" onkeyup="noAlpha(this, '0')" onkeypress="noAlpha(this, '0')" class="reminder_field" />
		<select name="reminder_span2" id="reminder_span2" class="reminder_field">
				<option value="minutes" <% if (occurrence.reminder_span2=="minutes") { %>selected="selected"<% } %>>Minutes</option>
				<option value="hours" <% if (occurrence.reminder_span2=="hours") { %>selected="selected"<% } %>>Hours</option>
				<option value="days" <% if (occurrence.reminder_span2=="days") { %>selected="selected"<% } %>>Days</option>
				<option value="weeks" <% if (occurrence.reminder_span2=="weeks") { %>selected="selected"<% } %>>Weeks</option>
		</select>
		<span id="reminder_datetime2" style="text-align:left"><%=occurrence.reminder_datetime2 %></span>
		</td>
	</tr>
	<% } %>
	<tr>
		<th align="left" valign="top" scope="row">Details:</th>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2" align="left" valign="top" scope="row" id="description_holder">
		<textarea name="event_descriptionInput" id="event_descriptionInput" class="modalInput event input_class"><%=occurrence.event_description%></textarea></td>
		</tr>
		<tr>
			<th align="left" valign="top" nowrap="nowrap" scope="row">Entered By:</th>
			<td valign="top"><div style="float:right; margin-right:0px"><span style="font-weight:bold">Priority:</span>&nbsp;
				<select name="event_priorityInput" id="event_priorityInput" class="modalInput event input_class" style="width:150px">
					<option value="high" <% if (occurrence.event_priority=="high") { %>selected<% } %>>High</option>
					<option value="normal" <% if (occurrence.event_priority=="normal" || occurrence.event_priority=="") { %>selected<% } %>>Normal</option>
					<option value="low" <% if (occurrence.event_priority=="low") { %>selected<% } %>>Low</option>
					</select>
				</div>
				<input name="event_fromInput" type="text" id="event_fromInput" style="width:433px" class="modalInput event input_class hidden"  value="<%=occurrence.event_from %>" />
				<span class="span_class"><%=occurrence.event_from %></span></td>
		</tr>
		<tr style="display:none">
			<th align="left" valign="top" scope="row">End Date:</th>
			<td valign="top">
				<input name="end_dateInput" class="modalInput event input_class" id="end_dateInput" value="<%=occurrence.end_date%>" style="width:150px" placeholder="12/12/2030 10:15am" />	</td>
		</tr>
		<tr style="display:none" id="follow_up_row">
			<th colspan="2" align="left" valign="top" nowrap="nowrap" scope="row">
				<div id="follow_up_holder"><span id="follow_up_label">Follow Up Date:</span>        <input name="callback_dateInput" class="modalInput event input_class" id="callback_dateInput" value="<%=occurrence.callback_date%>" style="width:150px" placeholder="12/12/2030 10:15am" />	
				</div>
				</th>
			</tr>
		<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		</tr>
	<tr>
		<td colspan="2">
			<input type='hidden' id='send_document_id' name='send_document_id' value="" />
			<div id="message_attachments" style="width:90%"></div>
        </td>
	</tr>
</table>
</form>
</div>
<div id="blocked_dates_holder" class="white_text" style="margin-top:5px;"></div>
<?php if ($_SESSION["user_customer_id"]==1033 || $_SESSION["user_customer_id"]==1096) { ?>
<div id="iframe_holder" style="display:none; padding-right:15px">
	<iframe id="reminder_holder" src="" height="1000px" width="100%" frameborder="0" allowtransparency="1"></iframe>
	<textarea id="reminder_set" rows="2" cols="50" style="display:none"></textarea>
	<input type="hidden" id="store_users" value="" />
</div>
<?php } ?>
<div id="billing_holder" style="display:none; padding-right:15px"></div>
<div id="event_view_all_done"></div>   


<div id="addressGrid" style="display:none">
		<table id="address">
			<tr style="display:none">
				<td class="label">Street address</td>
				<td class="slimField"><input class="field" id="street_number_event"
							disabled="true"></input></td>
				<td class="wideField" colspan="2"><input class="field" id="route_event"
							disabled="true"></input></td>
			</tr>
			<tr>
				<td class="wideField" colspan="4">
						<input class="field" id="street_event"></input>&nbsp;<input class="field" id="city_event"style="width:100px"></input>&nbsp;<input class="field"
							id="administrative_area_level_1_event" disabled="true" style="width:30px"></input>&nbsp;<input class="field" id="postal_code_event"
							disabled="true" style="width:50px"></input>
				</td>
			</tr>
			<tr style="display:none">
				<td class="label">City</td>
				<td class="wideField" colspan="3">
						<input class="field" id="locality_event"
							disabled="true"></input>
						<input class="field" id="sublocality_event"
							disabled="true"></input>
							<input class="field" id="neighborhood_event"
							disabled="true"></input>
				</td>
			</tr>
			<tr style="display:none">
				<td class="label">Country</td>
				<td class="wideField" colspan="3"><input class="field"
							id="country_event" disabled="true"></input></td>
			</tr>
		</table>
</div>

<script language="javascript">
$("#event_view_all_done").trigger( "click" );
$(".xdsoft_time ").click(function() {
	var cal_date_val = $(".xdsoft_date").val();
	//console.log(cal_date_val);
	return;
});
if (customer_id == 1033) {

}
</script>
<?php
//die(print_r($arrEventCalendars));
?>

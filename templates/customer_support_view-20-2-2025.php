<?php
include("../api/manage_session.php");
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
$stmt = null; $db = null;
?>
<div id="event_screen" class="event" style="margin-left:10px;">
<form id="report_issue_form" name="report_issue_form" method="post" enctype="multipart/form-data">
<input id="table_name" name="table_name" type="hidden" value="event" />
<input id="table_id" name="table_id" type="hidden" value="<%=occurrence.id %>" />
<input id="injury_id" name="injury_id" type="hidden" value="<%=occurrence.injury_id %>" />
<input id="calendar_id" name="calendar_id" type="hidden" value="<%=current_calendar_id %>" />
<input id="case_id" name="case_id" type="hidden" value="" />
<input id="user_id" name="user_id" type="hidden" value="<%=occurrence.user_id %>" />
<input id="event_kind" name="event_kind" type="hidden" value="<%=occurrence.event_kind %>" />

<div style="width:350px; display:none; float:right; border:1px solid white" id="parties_list1">
</div>
 <table width="550px" border="0" align="left" cellpadding="3" cellspacing="0" class="event_stuff" style="display:" id="event_table_screen">
	<tr style="display:none" id="uneditable_row">
			<td colspan="3">
					<div id="uneditable"></div>
				</td>
		</tr>
	<tr id="case_id_row">
        <th align="left" valign="top" scope="row">Case:</th>
        <td colspan="2" valign="top" id="case_id_holder">
        	<div style="float:right; display:none" id="open_case_holder">
            	<button id="review_case" title="Click to review case in new window" class="btn btn-xs btn-primary">Review Case</button>
            </div>
            <div class="case_input">
                <input name="case_idInput" type="text" id="case_idInput" size="30" class="modal_input event_<%=occurrence.id %>" value="0" />
            </div>
            <span id="case_idSpan" style=""></span>
        </td>
	</tr>
	<tr>
		<th align="left" valign="top" scope="row">Subject:</th>
		<td valign="top"><input name="event_titleInput" type="text" id="event_titleInput" style="width:450px" class="modalInput event input_class" autocomplete="off"><div style="color:#ff0000" id="msg_subject_error"></div>    </td>
	</tr>
	
	<tr>
		<th align="left" valign="top" scope="row">When:</th>
		<td valign="top">		
			<input name="event_dateandtimeInput" class="modalInput event input_class" id="event_dateandtimeInput" style="width:128px;display:<% if (occurrence.event_kind=='phone_call') { %>none<% } %>" placeholder="mm/dd/yyyy" value="<%=occurrence.event_dateandtime %>" />
			<input class="modalInput event input_class original_date" style="width:125px;" placeholder="mm/dd/yyyy" type="hidden" value="<%=occurrence.event_dateandtime %>" />
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
		<th align="left" valign="top" scope="row">Attachments:</th>
		<td>
			<input type="file" id="send_document_id" name="send_document_id" />
			<div id="message_attachments" style="width:90%"></div>
        </td>
	</tr>
	<tr>
		<th align="left" valign="top" scope="row">Details:</th>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2" align="left" valign="top" scope="row" id="description_holder">
		<textarea name="event_descriptionInput" id="event_descriptionInput" class="modalInput event input_class"><%=occurrence.event_description%></textarea>
		<div style="color:#ff0000" id="msg_details_error"></div>
		</td>
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
		<tr>
			<td id="upload_data_message" colspan="2" style="font-size:20px"></td>
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
	
</table>
</form>
<div id="complaint_message_div" style="display:none;">
	<p id="complaint_message" style="font-size:22px"></p>
	<input type="button" value="OK" onclick='$(".close").trigger("click");' />
</div>
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
<div id="customer_support_view_all_done"></div>   


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
<style>
	ul.token-input-list-event{
		width: 450px !important;
	}
</style>
<script language="javascript">
$("#customer_support_view_all_done").trigger( "click" );
$(".xdsoft_time ").click(function() {
	var cal_date_val = $(".xdsoft_date").val();
	//console.log(cal_date_val);
	return;
});
if (customer_id == 1033) {

}

$("#event_dateandtimeInput").focus(function(){
	$("td").each(function() {
  	$(this).removeClass("xdsoft_disabled");
	});	
})

</script>
<?php
//die(print_r($arrEventCalendars));
?>

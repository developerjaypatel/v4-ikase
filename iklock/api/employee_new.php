<?php 
error_reporting(E_ALL);
ini_set('display_errors', '1');	

require_once('../../shared/legacy_session.php');
date_default_timezone_set('America/Los_Angeles');

include("connection.php");

if (!isset($_SESSION["user_customer_id"])) {
	die("no No NO");
}
$user_id = passed_var("user_id", "post");

include ("../classes/cls_address.php");
include ("../classes/cls_comm.php");
include ("../classes/cls_department.php");
include ("../classes/cls_document_matrix.php");
include ("../classes/cls_events.php");
include("../classes/cls_eventscalendar.php");
include ("../classes/cls_notes.php");
include ("../classes/cls_person.php");
include("../classes/cls_user.php");

$expiration_date = mktime(0, 0, 0, date("m"),   date("d"),   date("Y")+1);
$hide_class = "";

// Construct a calendar to show the current month 
if (isset($_GET["month"])) {
	$month = passed_var("month", "get");
}
if (!isset($month)) {
	$month = "";
}
if (isset($_GET["year"])) {
	$year = passed_var("year", "get");
}
if (!isset($year)) {
	$year = "";
}
if ($month=="" && $year=="") {
	$month = date("n");
	$year = date("Y");
}
$cal = new Calendar; //FIXME: doesn't seem to be used at all?
$cal->standard_report = "true";

//user, but show all anyway
$cal->showall = true;
$cal->hidechecks = true;
$cal->admin = true;

//second month
$nextdate = $month . "/01/" . $year;
$nextdate = date("F", strtotime($nextdate . "+ 1 months"))." ".date("d", strtotime($nextdate . "+ 1 months"))." ".date("Y", strtotime($nextdate . "+ 1 months"));
$nextmonth = date("m", strtotime($nextdate));
$nextyear = date("Y", strtotime($nextdate));
if ($nextmonth=="1") {
	$nextyear = date("Y") + 1;
}

function americandate($unixdate) {
	$dateArray=explode('-', $unixdate);
	return date('m/d/Y', mktime(0, 0, 0, $dateArray[1], $dateArray[2], $dateArray[0]));
}
$group = "";
if (isset($_GET["group"])) {
	$group = passed_var("group", "get");
}
if ($group=="") {
	$group = "employees";
}
$my_user = new systemuser();
if (is_numeric($user_id)) {
	$my_user->id = $user_id;
	$my_user->fetch();
	
	$user_logon = $my_user->user_logon;
} else {
	$user_id = -1;
	$user_logon = "";
}

$my_document = new document();
$my_notes = new notes();
$my_events = new events();
$my_address = new address();
$my_phone = new comm();
$my_cell_phone = new comm();
$my_provider = new comm();
$my_notification_email = new comm();
$my_notification_sms = new comm();
$my_notification_personnel = new comm();
$my_emergency_phone = new comm();
$my_emergency_contact = new person();
$my_email = new comm();
$my_department = new department();

$document_employee_cells = "";

$user_name = ""; 
$user_pd = ""; 
$user_group = ""; 
$ssn = "";
$nickname = "";
$dob = "";
$ein = "";
$gender = "";
$data = "";

$group = "employees";
$group_select = $my_department->make_checkboxes("", $group);

$arrDepts = array();

if ($user_id > -1) {
	$resultdepartment = $my_department->getuser($my_user->uuid, "main");
	$arrGroup = array();
	foreach($resultdepartment as $dept) {
		$arrGroup[] = $dept->name;
	}
	
	//documents
	$resultdocument = $my_document->getuser($my_user->uuid, "employee");
	$intD = 0;
	foreach($resultdocument as $doc){
		if (($intD%2)==0) { $bgcolor="#ededed"; } else { $bgcolor="#FFFFFF"; }
		
		$document_id = $doc->document_id;
		$document_uuid = $doc->document_uuid;
		$document = $doc->document_filename;
		$document_type = $doc->type;
		$expiration_date = $doc->expiration_date;
		if ($expiration_date!="" && $expiration_date!="0000-00-00") {
			$expiration_date = date("m/d/Y", strtotime($expiration_date));
		} else {
			$expiration_date = "&nbsp;";
		}
		$dateandtime = $doc->document_date;
		$dateandtime = date("m/d/Y h:iA", strtotime($dateandtime));
		$document_employee_cells .= "<tr bgcolor='" . $bgcolor . "'>";
		$document_employee_cells .= "<td align='left'><a href='" . $document . "' target='_blank'>" . str_replace("http://24.43.66.213/", "", $document) . "</a></td>";
		$document_employee_cells .= "<td nowrap align='left'>" . $expiration_date . "</td>";
		$document_employee_cells .= "<td nowrap align='left'>" . ucwords(str_replace("_", " ", $document_type)) . "</td>";
		$document_employee_cells .= "<td align='right'><a href='document_delete.php?user_uuid=" . $my_user->uuid . "&document_id=" . $document_uuid . "&user=" . $user_logon . "' style='color:red' title='Click here to delete the document'>Delete</a></td>";
		$document_employee_cells .= "</tr>";
		$intD++;
	}
	//payrate
	$querypayrate = "select `user`.* , `payrate`.base_payrate, `overtime`.hours_day, `overtime`.hours_week, `overtime`.start_date overtime_start_date, `overtime`.end_date overtime_end_date
	from `user` 
	left outer join `payrate`
	on (`user`.`user_id` = `payrate`.`user_id` AND `payrate`.type = 'standard')
	left outer join `overtime`
	ON `user`.`user_id` = `overtime`.`user_id`
	where user_logon = '$user_logon'
	order by user_id desc";

	try {
		$sql = $querypayrate;
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		//$stmt->bindParam("user", $user_logon);
		$stmt->execute();
		$users_info = $stmt->fetchObject();
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
	//die(print_r($users_info));
	if (!is_object($users_info)) {
		
		$reroute="delete.php?user=" . $user_logon;
		die("location:" . $reroute);
	}
	
	//commission rate
	$querycommission = "select `user`.* , `payrate`.base_payrate
	from `user` 
	left outer join `payrate`
	on (`user`.`user_id` = `payrate`.`user_id` AND `payrate`.type = 'commission')
	where user_logon = '$user_logon'
	order by user_id desc";
	try {
		$sql = $querypayrate;
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("user", $user_logon);
		$stmt->execute();
		$commission = $stmt->fetchObject();
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
	if (is_object($commission)) {
		$commissionrate = $commission->base_payrate;
	}
	//if ($numberupdate>0) {
		//get the info, and then display it
		$x=0;
		//$user_id = $users_info->user_id');
		//$user_uuid = $users_info->user_uuid');
		$user_id = $my_user->id;
		$user_uuid = $my_user->uuid;
		//get notes
		
		$resultnotes = $my_notes->fetch_employee_notes($user_id);
		$numbernotes = count($resultnotes);
		//echo $numbernotes;
		if ($numbernotes>0) {
			$notes_href = "#notes";
		} else {
			$notes_href = "employee_notes.php?status=note&group=" . $group . "&user=" .$user_logon;
		}
		//look up time-off
		//get events
		$resultevents = $my_events->fetch_user_events($user_uuid);
		//$numberevents = $resultevents->rowCount();
		
		$shift = $users_info->shift;
		$work_location = $users_info->work_location;
		$no_lunch = $users_info->no_lunch;
		$noearly_entry = $users_info->noearly_entry;
		$early_entry_window = $users_info->early_entry_window;
		
		$pay_rate = $users_info->pay_rate;
		$pay_period = $users_info->pay_period;
		$pay_schedule = $users_info->pay_schedule;
		$pay_method = $users_info->pay_method;
		//overtime
		$overtime_hours_day = $users_info->hours_day;
		$overtime_hours_week = $users_info->hours_week;
		$overtime_start_date = $users_info->overtime_start_date;
		if ($overtime_start_date!="" && $overtime_start_date!="0000-00-00") {
			$overtime_start_date = date("m/d/Y", strtotime($overtime_start_date));
		} else {
			$overtime_start_date = "";
		}
		$overtime_end_date = $users_info->overtime_end_date;
		if ($overtime_end_date!="" && $overtime_end_date!="0000-00-00") {
			$overtime_end_date = date("m/d/Y", strtotime($overtime_end_date));
		} else {
			$overtime_end_date = "";
		}
		$ssn = $users_info->ssn;
		$nickname = $users_info->nickname;
		$ein = $users_info->ein;
		$employee_number = $users_info->employee_number;
		$dob = $users_info->dob;
		if ($dob!="0000-00-00") {
			$dob = date("m/d/Y", strtotime($dob));
		} else {
			$dob = "";
		}
		$gender = $users_info->gender;
		$data = $users_info->data;
		$arrData = array();
		$tax_federal_info = new stdClass();
		if ($data!="") {
			$arrData = json_decode($data);
			if (isset($arrData->tax_federal_info)) {
				$tax_federal_info = $arrData->tax_federal_info;
			}
		}
		$tax_state_info = new stdClass();
		if ($data!="") {
			$arrData = json_decode($data);
			if (isset($arrData->tax_state_info)) {
				$tax_state_info = $arrData->tax_state_info;
			}
		}
		
		//die(print_r($tax_info));
		
		$hired_date = $users_info->hired_date;
		$inine_filed = $users_info->inine_filed;
		$clock_in_time = $users_info->clock_in_time;
		if ($clock_in_time=="00:00:00") {
			$clock_in_time="08:00:00";
		}
		$arrIn = explode(":", $clock_in_time);
		$arrInAM = true;
		if ($arrIn[0] == 12) {
			$arrInAM = false;
		}
		if ($arrIn[0] > 12) {
			$arrIn[0] = $arrIn[0] - 12;
			$arrIn[0] = "0" . $arrIn[0];
			$arrInAM = false;
		}
		$clock_out_time = $users_info->clock_out_time;
		if ($clock_out_time=="00:00:00") {
			$clock_out_time="17:00:00";
		}
		$arrOut = explode(":", $clock_out_time);
		$arrOutAM = true;
		if ($arrOut[0] == 12) {
			$arrOutAM = false;
		}
		if ($arrOut[0] > 12) {
			$arrOut[0] = $arrOut[0] - 12;
			$arrOut[0] = "0" . $arrOut[0];
			$arrOutAM = false;
		}
		$lunch_out_time = $users_info->lunch_out_time;
		if ($lunch_out_time=="00:00:00") {
			$lunch_out_time="11:45:00";
		}
		$arrLunch = explode(":", $lunch_out_time);
		$arrLunchAM = true;
		if ($arrLunch[0] == 12) {
			$arrLunchAM = false;
		}
		if ($arrLunch[0] > 12) {
			$arrLunch[0] = $arrLunch[0] - 12;
			$arrLunch[0] = "0" . $arrLunch[0];
			$arrLunchAM = false;
		}
		if ($hired_date!="0000-00-00") {
			$hired_date = date("m/d/Y", strtotime($hired_date));
		} else {
			$hired_date = "";
		}
		$call_center = $users_info->call_center;
		$call_center_checked = "";
		if ($call_center=="Y"){
			$call_center_checked = " checked";
		}
		$reminder_90 = $users_info->reminder_90;
		$reminder_90_checked = "";
		if ($reminder_90=="Y"){
			$reminder_90_checked = " checked";
		}
		$reminder_365 = $users_info->reminder_365;
		$reminder_365_checked = "";
		if ($reminder_365=="Y"){
			$reminder_365_checked = " checked";
		}
		$no_lunch_checked = "";
		if ($no_lunch=="Y") {
			$no_lunch_checked = " checked";
		}
		$noearly_entry_checked = "";
		if ($noearly_entry=="Y") {
			$noearly_entry_checked = " checked";
		}
		//now, let's try and get the address
		//echo $my_user->uuid . "<BR>";
		$address_uuid = $my_user->getattribute("address","home");
		
		//die("add:" . $address_uuid);
		if ($address_uuid!="") {
			$my_address->uuid = $address_uuid;
			$my_address->fetch();
		}
		//phone	
		$phone_uuid = $my_user->getattribute("comm","home_phone");
	//	echo "Phone: " . $phone_uuid . "<BR>";
		
		$my_phone->uuid = $phone_uuid;
		$my_phone->fetch_empire();
		//cell
		$cell_phone_uuid = $my_user->getattribute("comm","cell_phone");
		
		$my_cell_phone->uuid = $cell_phone_uuid;
		$my_cell_phone->fetch_empire();
		
		//provider
		$provider_uuid = $my_user->getattribute("comm","provider");
		
		$my_provider->uuid = $provider_uuid;
		$my_provider->fetch_empire();
		
		//notification_email
		$notification_email_uuid = $my_user->getattribute("comm","notification_email");
		
		$my_notification_email->uuid = $notification_email_uuid;
		$my_notification_email->fetch_empire();
		
		//notification_sms
		$notification_sms_uuid = $my_user->getattribute("comm","notification_sms");
		
		$my_notification_sms->uuid = $notification_sms_uuid;
		$my_notification_sms->fetch_empire();
		//echo $notification_sms_uuid . "SMS:" . $my_notification_sms->comm . "<BR>";
		//notification_personnel
		$notification_personnel_uuid = $my_user->getattribute("comm","notification_personnel");
		
		$my_notification_personnel->uuid = $notification_personnel_uuid;
		$my_notification_personnel->fetch_empire();
		
		//emergency
		$emergency_phone_uuid = $my_user->getattribute("comm","emergency_phone");
		
		$my_emergency_phone->uuid = $emergency_phone_uuid;
		$my_emergency_phone->fetch_empire();
		
		$emergency_contact_uuid = $my_user->getattribute("person","emergency_contact");
		
		$my_emergency_contact->uuid = $emergency_contact_uuid;
		$my_emergency_contact->fetch_empire();
		//email
		$email_uuid = $my_user->getattribute("comm","email");
		
		$my_email->uuid = $email_uuid;
		$my_email->fetch_empire();
		
		$user_name = $users_info->user_name; 
		$user_pd = $users_info->user_pd; 
		$user_logon = $users_info->user_logon; 
			
		//get the groups select box
		//$my_department->name = $group;
		//die(print_r($arrGroup));
		$group = implode(",", $arrGroup);
		$group_select = $my_department->make_checkboxes("", $group);
		
		//days off
		$query_days = "SELECT days_available, seconds_available, days_assigned, seconds_assigned 
		FROM timeoff
		WHERE user_id = :user_id";
        $days_available = 0;

		try {
			$sql = $query_days;
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->bindParam("user_id", $user_id);

			$stmt->execute();
			$days = $stmt->fetchObject();
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}
		$seconds_assigned = 0;
		$actual_days_available = 0;
		$full_days_available = 0;
		if (is_object($days)) {
			$days_available = $days->days_available;
			$actual_days_available = $days_available;
			$days_assigned = $days->days_assigned;
			$seconds_assigned = $days->seconds_assigned;
			$seconds_available = $days->seconds_available;
			$full_days_available = ($seconds_available / 24 / 3600);
			//$days_available = number_format($full_days_available, 0);
			$arrNumber = explode(".", $full_days_available);
			$remainder = 0;
			if (count($arrNumber)>1) {
				$remainder = $arrNumber[1];
				//echo "rem:" . $remainder . "<BR>";
				$hours_available = (0 . "." . $remainder) * 24;
				$actual_hours_available = 8 - (24 - $hours_available);
				$actual_days_available = $arrNumber[0] . " days " . number_format($actual_hours_available, 0) . "hours or<br>";
				$actual_days_available .= ($arrNumber[0] * 8) + number_format($actual_hours_available, 0) . " vacation hours";
			} else {
				$actual_days_available .= " days ";
			}
		}
		//look up the payrate history
		$queryrates = "select distinct `payrate_track`.`base_payrate`,
		`payrate_track`.`time_stamp`, `payrate_track`.`user_name` as admin_name
		from `payrate_track` 
		inner join `user`
		on `user`.`user_id` = `payrate_track`.`user_id` AND `payrate_track`.type = 'standard'
		where `user`.`user_id` = :user_id
		order by payrate_id desc";

		try {
			$sql = $queryrates;
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->bindParam("user_id", $user_id);
			$stmt->execute();
			$resultrates = $stmt->fetchAll(PDO::FETCH_OBJ);
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}
		
		$pay_rate_table = "";
		if (count($resultrates) > 0) {
			foreach($resultrates as $rate) {
				$admin_name = $rate->admin_name;
				$the_payrate = $rate->base_payrate;
				$the_time_stamp = $rate->time_stamp;
				$pay_rate_table .= "<tr><td align='left'>" . $admin_name . "</td><td align='left'>" . $the_payrate . "</td><td align='left'>" . date("m/d/Y H:iA", strtotime($the_time_stamp)) . "</td></tr>";
			}
			$pay_rate_table = "<tr><td align='center' bgcolor='#ededed' colspan='3'><strong>PAY RATE HISTORY</strong></td></tr><tr><td align='left' width='33%'><strong>Pay Rate Entered by</strong></td><td align='left' width='33%'><strong>Rate</strong></td><td align='left' width='33%'><strong>Date</strong></td></tr>" . $pay_rate_table;
			$pay_rate_table = "<a name='payrates'></a><table border='1' cellpadding='0' cellspacing='0' width='500' align='center'>" . $pay_rate_table . "</table>";
		}
		//commission pay rate
		//look up the payrate history
		$queryrates = "select distinct `payrate_track`.`base_payrate`,
		`payrate_track`.`time_stamp`, `payrate_track`.`user_name` as admin_name
		from `payrate_track` 
		inner join `user`
		on `user`.`user_id` = `payrate_track`.`user_id` AND `payrate_track`.type = 'commission'
		where `user`.`user_id` = :user_id
		order by payrate_id desc";

		try {
			$sql = $queryrates;
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->bindParam("user_id", $user_id);
			$stmt->execute();
			$resultrates = $stmt->fetchAll(PDO::FETCH_OBJ);
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}
		$commissionrate_table = "";
		if (count($resultrates) > 0) {
			foreach($resultrates as $rate) {
				$admin_name = $rate->admin_name;
				$the_commissionrate = $rate->base_payrate;
				$the_time_stamp = $rate->time_stamp;
				$commissionrate_table .= "<tr><td align='left'>" . $admin_name . "</td><td align='left'>" . $the_commissionrate . "</td><td align='left'>" . date("m/d/Y H:iA", strtotime($the_time_stamp)) . "</td></tr>";
			}
			$commissionrate_table = "<tr><td align='center' bgcolor='#ededed' colspan='3'><strong>COMMISSION RATE HISTORY</strong></td></tr><tr><td align='left' width='33%'><strong>Pay Rate Entered by</strong></td><td align='left' width='33%'><strong>Rate</strong></td><td align='left' width='33%'><strong>Date</strong></td></tr>" . $commissionrate_table;
			$commissionrate_table = "<a name='commissionrates'></a><table border='1' cellpadding='0' cellspacing='0' width='500' align='center'>" . $commissionrate_table . "</table>";
		}
		
		//look up the overtime history
		$queryrates = "select distinct `overtime_track`.`hours_day`, `hours_week`, `start_date`, `end_date`,
		`overtime_track`.`time_stamp`, `overtime_track`.`user_name` as employee_name
		from `overtime_track` 
		inner join `user`
		on `user`.`user_id` = `overtime_track`.`user_id`
		where `user`.`user_id` = :user_id
		order by overtime_id desc";

		try {
			$sql = $queryrates;
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->bindParam("user_id", $user_id);
			$stmt->execute();
			$resultrates = $stmt->fetchAll(PDO::FETCH_OBJ);
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}
		$overtime_table = "";
		if (count($resultrates) > 0) {
			foreach($resultrates as $rate) {
				$employee_name = $rate->employee_name;
				$the_overtime = $rate->hours_day;
				$the_overtime_week = $rate->hours_week;
				$the_start_date = $rate->start_date;
				$the_end_date = $rate->end_date;
				$the_time_stamp = $rate->time_stamp;
				$overtime_table .= "<tr><td align='left'>" . $employee_name . "</td><td align='left'>" . $the_overtime . "</td><td align='left'>" . $the_overtime_week . "</td><td align='left'>" . date("m/d/Y", strtotime($the_start_date)) . "</td><td align='left'>" . date("m/d/Y", strtotime($the_end_date)) . "</td><td align='left' nowrap>" . date("m/d/Y H:iA", strtotime($the_time_stamp)) . "</td></tr>";
			}
			$overtime_table = "<tr><td align='center' bgcolor='#ededed' colspan='6'><strong>AUTHORIZED OVERTIME HISTORY</strong></td></tr><tr><td align='left' width='33%'><strong>Authorized by</strong></td><td align='left' width='33%'><strong>Hours/Day</strong></td><td align='left' width='33%'><strong>Hours/Week</strong></td><td align='left' width='33%'><strong>Start Date</strong></td><td align='left' width='33%'><strong>End Date</strong></td><td align='left' width='33%'><strong>Date</strong></td></tr>" . $overtime_table;
			$overtime_table = "<a name='overtimes'></a><table border='1' cellpadding='0' cellspacing='0' width='500' align='center'>" . $overtime_table . "</table>";
		}
	}
?>
<style type="text/css">
<!--
.headwarning {color: #FF0000;
	font-weight: bold;
	background-color:#FFFFFF;
}
-->
</style>
<style type="text/css">
<!--
.admintitle {	color: #FFFFFF;
	font-size: 18px;
	font-weight: bold;
}
.hide_me {
	display:none;
}
.info_holder td {
	height:35px;
}
-->
</style>

<input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
<input type="hidden" name="group" value="<?php echo $group; ?>">
  <table width="700" border="0" align="center" cellpadding="0" cellspacing="0" bordercolor="#000000">
    <tr>
      <td colspan="7" align="left" valign="top" bgcolor="#000033"><span class="admintitle">NEW EMPLOYEE</span></td>
    </tr>
    <!--
    <tr>
      <td colspan="5" align="center" valign="top" bgcolor="#FFFFFF"><a href="<?php echo $notes_href; ?>">notes</a> &nbsp;|&nbsp;<a href="#timeoff">timeoff</a>&nbsp;|&nbsp;<a href="hours_edit.php?user=<?php echo $user; ?>">edit hours</a> &nbsp;|&nbsp;<a href="logins.php?user=<?php echo $user; ?>">logins</a> &nbsp;|&nbsp;<a href="hours_span_totals.php?user=<?php echo $user; ?>">payroll hours</a>&nbsp;|&nbsp;<a href="#payrates">pay rate history</a>&nbsp;|&nbsp;<a href="#commissionrates">commission rate history</a>&nbsp;|&nbsp;<a href="#carriers">pay rate history</a>&nbsp;|&nbsp;<a href="#assigned_carriers">assigned carriers</a></td>
    </tr>
    -->
    <tr>
      <td width="40%" align="center" valign="top" bgcolor="#FFFFFF"><table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF" bordercolor="#000000">
        
        <tr>
          <td style="background:url(images/ui/answer_sides_r1_c1.jpg) left repeat-y #FFFFFF">&nbsp;</td>
          <td align="left" valign="top" bgcolor="#FFFFFF">
              <form id="personal_info_form">
              	<input type="hidden" name="user_id" value="<?php echo $user_id; ?>" />
                  <table border="1" cellpadding="3" cellspacing="0" bordercolor="#ededed" align="center" class="info_holder">
                      <tr>
                        <td colspan="5" style="background:darkslategray; color:white; font-weight:bold" id="header_personal">
                            <div style="float:right">
                                <button class="btn btn-xs btn-primary" id="edit_personal">Edit</button>
                                <button class="btn btn-xs btn-primary hide_me" id="save_personal">Save</button>
                            </div>
                            <strong>Personal Information </strong>
                        </td>
                      </tr>
                      <tr style="visibility:hidden">
                        <td align="right" bgcolor="#FFFFFF" style="height:1px"><img src="images/spacer.gif" width="100" height="1" /></td>
                        <td colspan="2" bgcolor="#FFFFFF" style="height:1px"><img src="images/spacer.gif" width="330" height="1" /></td>
                      </tr>
                      <tr>
                        <td width="108" align="left" bgcolor="#CCCCCC"><span style="font-weight:bold">Name :</span></td>
                        <td colspan="2" align="left">
                            <input name="user_nameField" type="text" id="user_nameField" value="<?php echo $user_name; ?>" class="personal edit_field hide_me" autocomplete="off" />   
                            <span id="user_nameSpan" class="personal edit_span"><?php echo $user_name; ?></span>             
                        </td>
                       
                      </tr>
                      <tr>
                        <td align="left" bgcolor="#CCCCCC"><span style="font-weight:bold">Logon :</span></td>
                        <td colspan="2" align="left">
                            <input type="text" name="user_logonField" id="user_logonField" value="<?php echo $user_logon; ?>" class="personal edit_field hide_me" autocomplete="off" />
                            <span id="user_logonSpan" class="personal edit_span" ><?php echo $user_logon; ?></span>
                        </td>
                      </tr>
                      <tr>
                        <td align="left" bgcolor="#CCCCCC"><span style="font-weight:bold">Nickname :</span></td>
                        <td colspan="2" align="left">
                        	<input type="text" name="nicknameField" value="<?php echo $nickname; ?>" class="personal edit_field hide_me" autocomplete="off" />
                            <span id="nicknameSpan" class="personal edit_span" ><?php echo $nickname; ?></span>
                        </td>
                      </tr>
                      <tr<?php echo $hide_class; ?>>
                        <td align="left" bgcolor="#CCCCCC"><span style="font-weight:bold">Password :</span></td>
                        <td colspan="2" align="left">
                            <input name="passwordField" type="text" id="passwordField" value="<?php echo $user_pd; ?>" class="personal edit_field hide_me" autocomplete="off" />
                        </td>
                      </tr>
                      <tr<?php echo $hide_class; ?>>
                        <td align="left" bgcolor="#CCCCCC"><span style="font-weight:bold">SSN #  :</span></td>
                        <td colspan="2" align="left">
                        	<div style="float:right" id="ssn_clear_holder" class="clear_holder hide_me">
                            	<a id="clear_ssn_link" class="clear_link">clear</a>
                            </div>
                            <input name="ssnField" type="text" id="ssnField" value="<?php echo $ssn; ?>" class="personal edit_field hide_me" onkeyup="mask(this, mssn);" onblur="mask(this, mssn);" autocomplete="off" />
                            
                            <span id="ssnSpan" class="personal edit_span"><?php echo $ssn; ?></span>
                        </td>
                      </tr>
                      <tr<?php echo $hide_class; ?>>
                        <td align="left" bgcolor="#CCCCCC"><span style="font-weight:bold">EIN :</span></td>
                        <td colspan="2" align="left">
                        	<div style="float:right" id="ein_clear_holder" class="clear_holder hide_me">
                            	<a id="clear_ein_link" class="clear_link">clear</a>
                            </div>
                            <input name="einField" type="text" id="einField" value="<?php echo $ein; ?>" class="personal edit_field hide_me" onkeyup="mask(this, mein);" onblur="mask(this, mein);" autocomplete="off" />
                          <span id="einSpan" class="personal edit_span"><?php echo $ein; ?></span>
                        </td>
                      </tr>
                     <tr<?php echo $hide_class; ?>>
                        <td align="left" bgcolor="#CCCCCC"><span style="font-weight:bold">DOB :</span></td>
                        <td colspan="2" align="left">
                        	<div style="float:right" id="dob_clear_holder" class="clear_holder hide_me">
                            	<a id="clear_dob_link" class="clear_link">clear</a>
                          </div>
                            <input name="dobField" type="text" id="dobField" value="<?php echo $dob; ?>" class="personal edit_field hide_me" onkeyup="mask(this, mdate);" onblur="mask(this, mdate);" autocomplete="off" />
                          <span id="dobSpan" class="personal edit_span"><?php echo $dob; ?></span>
                        </td>
                    </tr>
                    <tr<?php echo $hide_class; ?>>
                        <td align="left" bgcolor="#CCCCCC"><span style="font-weight:bold">Gender :</span></td>
                        <td colspan="2" align="left">
                            <select name="genderField" id="genderField" class="personal edit_field hide_me">
                            	<option value="" <?php if ($gender=="") { echo "selected"; } ?>>Select</option>
                                <option value="F" <?php if ($gender=="F") { echo "selected"; } ?>>Female</option>
                                <option value="M" <?php if ($gender=="M") { echo "selected"; } ?>>Male</option>
                                <option value="GN" <?php if ($gender=="GN") { echo "selected"; } ?>>Gender Non-Conforming</option>
                            </select>
                          <span id="genderSpan" class="personal edit_span"><?php echo $gender; ?></span>
                        </td>
                    </tr>
                  </table>
              </form>
          </td>
          <td  style="background:url(images/ui/answer_sides_r1_c2.jpg) right repeat-y #FFFFFF">&nbsp;</td>
        </tr>
      </table></td>
      <td width="98%" align="center" valign="top"><img src="images/spacer.gif" width="15" height="1" /></td>
      <td width="55%" align="center" valign="top"><table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF" bordercolor="#000000"<?php echo $hide_class; ?>>
       
        <tr>
          <td width="3%" style="background:url(images/ui/answer_sides_r1_c1.jpg) left repeat-y #FFFFFF">&nbsp;</td>
          <td width="73%" align="left" valign="top" bgcolor="#FFFFFF">&nbsp;</td>
          <td width="3%"  style="background:url(images/ui/answer_sides_r1_c2.jpg) right repeat-y #FFFFFF">&nbsp;</td>
        </tr>
      </table></td>
      <td width="5%" align="center" valign="top"><img src="images/spacer.gif" width="15" height="1" /></td>
      <td width="55%" align="center" valign="top"><table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF" bordercolor="#000000">
        <tr>
          <td width="3%" style="background:url(images/ui/answer_sides_r1_c1.jpg) left repeat-y #FFFFFF">&nbsp;</td>
          <td width="73%" align="left" valign="top" bgcolor="#FFFFFF">&nbsp;</td>
          <td width="3%"  style="background:url(images/ui/answer_sides_r1_c2.jpg) right repeat-y #FFFFFF">&nbsp;</td>
        </tr>
      </table></td>
    </tr>
    <tr>
      <td colspan="5" align="left" valign="top" style=""><hr /> </td>
    </tr>
  </table>
  </td>
    </tr>
  </table>


<div id="displaybox" style="display:none; background-color:#CCCC99; width:150px"></div>
<script language="javascript">
<?php if (strpos($user_pd, "temporary") > -1) { ?>
newPassword();
function newPassword() {
	var pd = prompt("Please enter a new password for this user, 6 characters minimum", "");
	if (pd == null || pd.length < 6) {
		 newPassword();
	} else {
		document.getElementById("passwordField").value = pd;
	}
}
<?php } ?>
</script>

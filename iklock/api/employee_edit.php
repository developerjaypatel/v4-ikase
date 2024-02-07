<?php 
error_reporting(E_ALL);
ini_set('display_errors', '1');	

require_once('../../shared/legacy_session.php');

if (!isset($_SESSION["user_customer_id"])) {
	die("no No NO");
}
date_default_timezone_set('America/Los_Angeles');

include("connection.php");

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
$cal = new Calendar; 
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
	$user_logon = "new";
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
$user_status = "";
$nickname = "";
$ssn = "";
$dob = "";
$ein = "";
$gender = "";
$shift = "1";
$user_type = "1";
$screen_subtitle = "EMPLOYEE";

if ($user_id > -1) {
	$resultdepartment = $my_department->getuser($my_user->uuid, "main");
	$arrGroup = array();
	foreach($resultdepartment as $dept) {
		$arrGroup[] = $dept->name;
	}
	if (count($arrGroup)==0) {
		$group = "employees";
	} else {
		$group = implode(",", $arrGroup);
	}
	$group_select = $my_department->make_checkboxes("", $group);
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
		$user_type = $users_info->user_type;
		if ($user_type=="2") {
			$screen_subtitle = "CONTRACTOR";
		}
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
		$ein = $users_info->ein;
		$nickname = $users_info->nickname;
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
		if ($data!="") {
			$arrData = json_decode($data);
		}
		//die(print_r($arrData));
		$tax_federal_info = new stdClass();
		if ($data!="") {
			if (isset($arrData->tax_federal_info)) {
				$tax_federal_info = $arrData->tax_federal_info;
			}
		}
		$tax_state_info = new stdClass();
		if ($data!="") {
			if (isset($arrData->tax_state_info)) {
				$tax_state_info = $arrData->tax_state_info;
			}
		}
		
		$contractor_info = new stdClass();
		if ($data!="") {
			if (isset($arrData->contractor_info)) {
				$contractor_info = $arrData->contractor_info;
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
		$user_status = $users_info->user_status; 
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
			$pay_rate_table = "<tr><td align='center' bgcolor='#ededed' colspan='3'><span style='font-weight:bold'>PAY RATE HISTORY</span></td></tr><tr><td align='left' width='33%'><span style='font-weight:bold'>Pay Rate Entered by</span></td><td align='left' width='33%'><span style='font-weight:bold'>Rate</span></td><td align='left' width='33%'><span style='font-weight:bold'>Date</span></td></tr>" . $pay_rate_table;
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
			$commissionrate_table = "<tr><td align='center' bgcolor='#ededed' colspan='3'><span style='font-weight:bold'>COMMISSION RATE HISTORY</span></td></tr><tr><td align='left' width='33%'><span style='font-weight:bold'>Pay Rate Entered by</span></td><td align='left' width='33%'><span style='font-weight:bold'>Rate</span></td><td align='left' width='33%'><span style='font-weight:bold'>Date</span></td></tr>" . $commissionrate_table;
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
			$overtime_table = "<tr><td align='center' bgcolor='#ededed' colspan='6'><span style='font-weight:bold'>AUTHORIZED OVERTIME HISTORY</span></td></tr><tr><td align='left' width='33%'><span style='font-weight:bold'>Authorized by</span></td><td align='left' width='33%'><span style='font-weight:bold'>Hours/Day</span></td><td align='left' width='33%'><span style='font-weight:bold'>Hours/Week</span></td><td align='left' width='33%'><span style='font-weight:bold'>Start Date</span></td><td align='left' width='33%'><span style='font-weight:bold'>End Date</span></td><td align='left' width='33%'><span style='font-weight:bold'>Date</span></td></tr>" . $overtime_table;
			$overtime_table = "<a name='overtimes'></a><table border='1' cellpadding='0' cellspacing='0' width='500' align='center'>" . $overtime_table . "</table>";
		}
	}
?>
<input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
<input type="hidden" name="group" value="<?php echo $group; ?>">
  <table width="700" border="0" align="center" cellpadding="0" cellspacing="0" bordercolor="#000000">
    <tr>
      <td colspan="7" align="left" valign="top" bgcolor="#000033"><span class="admintitle">EDIT <?php echo $screen_subtitle; ?></span> <?php if ($user_name != "") { echo "<span style='color:white'> - " . $user_name . "</span>"; } ?></td>
    </tr>
    <!--
    <tr>
      <td colspan="5" align="center" valign="top" bgcolor="#FFFFFF"><a href="<?php echo $notes_href; ?>">notes</a> &nbsp;|&nbsp;<a href="#timeoff">timeoff</a>&nbsp;|&nbsp;<a href="hours_edit.php?user=<?php echo $user; ?>">edit hours</a> &nbsp;|&nbsp;<a href="logins.php?user=<?php echo $user; ?>">logins</a> &nbsp;|&nbsp;<a href="hours_span_totals.php?user=<?php echo $user; ?>">payroll hours</a>&nbsp;|&nbsp;<a href="#payrates">pay rate history</a>&nbsp;|&nbsp;<a href="#commissionrates">commission rate history</a>&nbsp;|&nbsp;<a href="#carriers">pay rate history</a>&nbsp;|&nbsp;<a href="#assigned_carriers">assigned carriers</a></td>
    </tr>
    -->
    <tr>
      <td width="40%" align="center" valign="top" bgcolor="#FFFFFF">
      	<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF" bordercolor="#000000">
        
        <tr>
          <td style="background:#FFFFFF">&nbsp;</td>
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
                            <span style='font-weight:bold'>Personal Information</span>
                        </td>
                      </tr>
                      <tr style="visibility:hidden">
                        <td align="right" bgcolor="#FFFFFF" style="height:1px"><img src="images/spacer.gif" width="100" height="1" /></td>
                        <td colspan="2" bgcolor="#FFFFFF" style="height:1px"><img src="images/spacer.gif" width="330" height="1" /></td>
                      </tr>
                      <tr>
                        <td width="108" align="left" class="td_label"><span style='font-weight:bold'>Status :</span></td>
                        <td colspan="2" align="left">
                            <select name="user_statusField" id="user_statusField" class="personal edit_field hide_me">  
                            	<option value="ACTIVE" <?php if ($user_status=="" || $user_status=="ACTIVE") { echo "selected"; } ?>>Active</option>
                                <option value="SUSPENDED" <?php if ($user_status=="SUSPENDED") { echo "selected"; } ?>>Suspended</option>
                                <option value="FIRED" <?php if ($user_status=="FIRED") { echo "selected"; } ?>>Terminated</option>
                            </select> 
                            <span id="user_statusSpan" class="personal edit_span"><?php echo str_replace("FIRED", "TERMINATED", $user_status); ?></span>             
                        </td>
                       
                      </tr>
                      <tr>
                        <td width="108" align="left" class="td_label"><span style='font-weight:bold'>Name :</span></td>
                        <td colspan="2" align="left">
                            <input name="user_nameField" type="text" id="user_nameField" value="<?php echo $user_name; ?>" class="personal edit_field hide_me" autocomplete="off" />   
                            <span id="user_nameSpan" class="personal edit_span"><?php echo $user_name; ?></span>             
                        </td>
                       
                      </tr>
                      <tr>
                        <td align="left" class="td_label"><span style='font-weight:bold'>Logon :</span></td>
                        <td colspan="2" align="left">
                            <input type="text" name="user_logon" value="<?php echo $user_logon; ?>" class="personal edit_field hide_me" autocomplete="off" />
                            <span id="user_logonSpan" class="personal edit_span" ><?php echo $user_logon; ?></span>
                        </td>
                      </tr>
                      <tr>
                        <td align="left" class="td_label"><span style='font-weight:bold'>Nickname :</span></td>
                        <td colspan="2" align="left">
                        	<input type="text" name="nicknameField" value="<?php echo $nickname; ?>" class="personal edit_field hide_me" autocomplete="off" />
                            <span id="nicknameSpan" class="personal edit_span" ><?php echo $nickname; ?></span>
                        </td>
                      </tr>
                      <tr<?php echo $hide_class; ?>>
                        <td align="left" class="td_label"><span style='font-weight:bold'>Password :</span></td>
                        <td colspan="2" align="left">
                            <input name="passwordField" type="text" id="passwordField" value="<?php echo $user_pd; ?>" class="personal edit_field hide_me" autocomplete="off" />
                        </td>
                      </tr>
                      <tr<?php echo $hide_class; ?>>
                        <td align="left" class="td_label"><span style='font-weight:bold'>SSN #  :</span></td>
                        <td colspan="2" align="left">
                        	<div style="float:right" id="ssn_clear_holder" class="clear_holder hide_me">
                            	<a id="clear_ssn_link" class="clear_link">clear</a>
                            </div>
                            <input name="ssnField" type="text" id="ssnField" value="<?php echo $ssn; ?>" class="personal edit_field hide_me" onkeyup="mask(this, mssn);" onblur="mask(this, mssn);" autocomplete="off" />
                            
                            <span id="ssnSpan" class="personal edit_span"><?php echo $ssn; ?></span>
                        </td>
                      </tr>
                      <tr<?php echo $hide_class; ?>>
                        <td align="left" class="td_label"><span style='font-weight:bold'>EIN :</span></td>
                        <td colspan="2" align="left">
                        	<div style="float:right" id="ein_clear_holder" class="clear_holder hide_me">
                            	<a id="clear_ein_link" class="clear_link">clear</a>
                            </div>
                            <input name="einField" type="text" id="einField" value="<?php echo $ein; ?>" class="personal edit_field hide_me" onkeyup="mask(this, mein);" onblur="mask(this, mein);" autocomplete="off" />
                          <span id="einSpan" class="personal edit_span"><?php echo $ein; ?></span>
                        </td>
                      </tr>
                     <tr<?php echo $hide_class; ?>>
                        <td align="left" class="td_label"><span style='font-weight:bold'>DOB :</span></td>
                        <td colspan="2" align="left">
                        	<div style="float:right" id="dob_clear_holder" class="clear_holder hide_me">
                            	<a id="clear_dob_link" class="clear_link">clear</a>
                          </div>
                            <input name="dobField" type="text" id="dobField" value="<?php echo $dob; ?>" class="personal edit_field hide_me" onkeyup="mask(this, mdate);" onblur="mask(this, mdate);" autocomplete="off" />
                          <span id="dobSpan" class="personal edit_span"><?php echo $dob; ?></span>
                        </td>
                    </tr>
                    <tr<?php echo $hide_class; ?>>
                        <td align="left" class="td_label"><span style='font-weight:bold'>Gender :</span></td>
                        <td colspan="2" align="left">
                            <select name="genderField" id="genderField" class="personal edit_field hide_me">
                            	<option value="" <?php if ($gender=="") { echo "selected"; } ?>>Select</option>
                                <option value="F" <?php if ($gender=="F") { echo "selected"; } ?>>Female</option>
                                <option value="M" <?php if ($gender=="M") { echo "selected"; } ?>>Male</option>
                                <option value="GN" <?php if ($gender=="GN") { echo "selected"; } ?>>Gender Non-Conforming</option>
                            </select>
                          <span id="genderSpan" class="personal edit_span"><?php 
						  switch($gender) {
							  case "M":
							  	$gender = "Male";
								break;
							case "F":
							  	$gender = "Female";
								break;
							case "GN":
							  	$gender = "Gender Non-Conforming";
								break;
						  }
						  echo $gender; 
						  ?></span>
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
          <td width="3%" style="background:#FFFFFF">&nbsp;</td>
          <td width="73%" align="left" valign="top" bgcolor="#FFFFFF">
          	<form id="contact_info_form">
              	<input type="hidden" name="user_id" value="<?php echo $user_id; ?>" />
              <table border="1" align="center" cellpadding="3" cellspacing="0" bordercolor="#ededed" class="info_holder">
                <tr >
                  <td colspan="2" style="background:darkslategray; color:white; font-weight:bold" id="header_contact">
                    <div style="float:right">
                        <button class="btn btn-xs btn-primary" id="edit_contact">Edit</button>
                        <button class="btn btn-xs btn-primary hide_me" id="save_contact">Save</button>
                    </div>
                    <span style='font-weight:bold'>Contact Information </span>
                  </td>
                </tr>
                <tr style="visibility:hidden">
                    <td align="right" bgcolor="#FFFFFF" style="height:1px"><img src="images/spacer.gif" width="100" height="1" /></td>
                  <td bgcolor="#FFFFFF" style="height:1px"><img src="images/spacer.gif" width="330" height="1" /></td>
                </tr>
                <tr >
                  <td align="left" nowrap="nowrap" class="td_label"><input type="hidden" name="address_uuid" value="<?php echo $my_address->uuid; ?>" />
                      <span style='font-weight:bold'>Home Address  :</span></td>
                  <td align="left">
                  	<textarea name="streetField" style="width:260px" rows="2" id="streetField" class="contact edit_field hide_me"><?php echo $my_address->street; ?></textarea>
                    <span id="streetSpan" class="contact edit_span"><?php echo $my_address->street; ?></span>
                  </td>
                </tr>
                <tr >
                  <td align="left" nowrap="nowrap" class="td_label"><span style='font-weight:bold'>City, State Zip   :</span></td>
                  <td align="left" nowrap="nowrap"><input value="<?php echo $my_address->city; ?>" name="cityField" type="text" id="cityField" size="20" class="contact edit_field hide_me" />
                      <input name="stateField" type="text" id="stateField"  style="width:25px" value="<?php echo $my_address->state; ?>" class="contact edit_field hide_me" />
                      <input name="zipField" type="text" id="zipField" style="width:50px" value="<?php echo $my_address->zip; ?>" class="contact edit_field hide_me" />
                      <span id="citySpan" class="contact edit_span"><?php echo $my_address->city; ?></span>
                      <span id="stateSpan" class="contact edit_span"><?php echo $my_address->state; ?></span>
                      <span id="zipSpan" class="contact edit_span"><?php echo $my_address->zip; ?></span>
                   </td>
                </tr>
                <tr >
                  <td align="left" nowrap="nowrap" class="td_label"><input type="hidden" name="phone_uuid" value="<?php echo $my_phone->uuid; ?>" />
                      <span style='font-weight:bold'>Phone   :</span></td>
                  <td align="left">
                  	<input name="phoneField" type="text" id="phoneField" value="<?php echo $my_phone->comm; ?>" class="contact edit_field hide_me" onkeyup="mask(this, mphone);" onblur="mask(this, mphone);" placeholder="xxx-xxx-xxxx" />
                    <span id="phoneSpan" class="contact edit_span"><?php echo $my_phone->comm; ?></span>
                  </td>
                </tr>
                <tr >
                  <td align="left" nowrap="nowrap" class="td_label"><input type="hidden" name="cell_phone_uuid" value="<?php echo $my_cell_phone->uuid; ?>" />
                      <span style='font-weight:bold'>Cell Phone   :</span></td>
                  <td align="left">
                  	<input name="cell_phoneField" type="text" id="cell_phoneField" value="<?php echo $my_cell_phone->comm; ?>" class="contact edit_field hide_me" onkeyup="mask(this, mphone);" onblur="mask(this, mphone);" placeholder="xxx-xxx-xxxx" />
                    <span id="cell_phoneSpan" class="contact edit_span"><?php echo $my_cell_phone->comm; ?></span>
                  </td>
                </tr>
                <tr >
                  <td align="left" nowrap="nowrap" class="td_label"><span style='font-weight:bold'>
                    <input name="provider_uuid" type="hidden" id="provider_uuid" value="<?php echo $my_provider->uuid; ?>" class="contact edit_field hide_me" />
                    Cell Provider:</span></td>
                  <td align="left"><select name="providerField" id="providerField" class="contact edit_field hide_me">
                    <option value="" <?php if ( $my_provider->comm=="") { echo " selected"; } ?>>Select from List</option>
                    <option value="att" <?php if ( $my_provider->comm=="att") { echo " selected"; } ?>>ATT</option>
                    <option value="alltel" <?php if ( $my_provider->comm=="alltel") { echo " selected"; } ?>>Alltel</option>
                    <option value="boost"<?php if ( $my_provider->comm=="boost") { echo " selected"; } ?>>Boost</option>
                    <option value="metropcs"<?php if ( $my_provider->comm=="metropcs") { echo " selected"; } ?>>MetroPCS</option>
                    <option value="nextel"<?php if ( $my_provider->comm=="nextel") { echo " selected"; } ?>>Nextel</option>
                    <option value="tmobile"<?php if ( $my_provider->comm=="tmobile") { echo " selected"; } ?>>TMobile</option>
                    <option value="sprint"<?php if ( $my_provider->comm=="sprint") { echo " selected"; } ?>>Sprint PCS</option>
                    <option value="verizon"<?php if ( $my_provider->comm=="verizon") { echo " selected"; } ?>>Verizon</option>
                    <option value="virgin"<?php if ( $my_provider->comm=="virgin") { echo " selected"; } ?>>Virgin Mobile</option>
                  </select>
                  <span id="providerSpan" class="contact edit_span"><?php echo $my_provider->comm; ?></span>
                  </td>
                </tr>
                
                <tr >
                  <td align="left" nowrap="nowrap" class="td_label"><input type="hidden" name="email_uuid" value="<?php echo $my_email->uuid; ?>" />
                      <span style='font-weight:bold'>Email   :</span></td>
                  <td align="left"><input name="emailField" type="text" id="emailField" value="<?php echo $my_email->comm; ?>" style="width:260px" class="contact edit_field hide_me" />
                  <span id="emailSpan" class="contact edit_span"><?php echo $my_email->comm; ?></span>
                  </td>
                </tr>
                <tr >
                  <td align="left" nowrap="nowrap" class="td_label">
                    <input type="hidden" name="notification_email_uuid" value="<?php echo $my_notification_email->uuid; ?>" />
                    <input name="notification_sms_uuid" type="hidden" id="notification_sms_uuid" value="<?php echo $my_notification_sms->uuid; ?>" />
                    <input name="notification_personnel_uuid" type="hidden" id="notification_personnel_uuid" value="<?php echo $my_notification_personnel->uuid; ?>" />
                    <span style='font-weight:bold'>Notifications:</span></td>
                  <td align="left"><input type="checkbox" name="notification_email" id="notification_email" value="Y" <?php if ($my_notification_email->comm=="Y") { echo " checked"; } ?> />
                    Email&nbsp;&nbsp;&nbsp;&nbsp;<?php //echo "SMS:" . $my_notification_sms->comm; ?>
                    <input type="checkbox" name="notification_sms" id="notification_sms" value="Y" <?php if ($my_notification_sms->comm=="Y") { echo " checked"; } ?> />
                    Text&nbsp;&nbsp;&nbsp;&nbsp;
                    <input type="checkbox" name="notification_personnel" id="notification_personnel" value="Y" <?php if ($my_notification_personnel->comm=="Y") { echo " checked"; } ?> /> 
                    Personnel
    </td>
                </tr>
                <tr >
                  <td align="left" valign="top" nowrap="nowrap" class="td_label"><input type="hidden" name="emergency_phone_uuid" value="<?php echo $my_emergency_phone->uuid; ?>" />
                    <span style='font-weight:bold'>
                    <input name="emergency_contact_uuid" type="hidden" id="emergency_contact_uuid" value="<?php echo $my_emergency_contact->uuid; ?>" />
                    Emergency </span><span style='font-weight:bold'>   :</span></td>
                  <td align="left">
                      <table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                          <td align="left" valign="top" bgcolor="#ededed" style="height:24px">Contact</td>
                          <td align="left" valign="top" bgcolor="#ededed" style="height:24px">Phone</td>
                        </tr>
                        <tr>
                          <td align="left" valign="top" style="height:24px">
                            <input name="emergency_contactField" type="text" id="emergency_contactField" style="width:125px" value="<?php echo $my_emergency_contact->full_name; ?>" class="contact edit_field hide_me" />
                            <span id="emergency_contactSpan" class="contact edit_span"><?php echo  $my_emergency_contact->full_name; ?></span>
                          </td>
                          <td align="left" valign="top" style="height:24px">
                            <input name="emergency_phoneField" type="text" id="emergency_phoneField" style="width:125px" value="<?php echo $my_emergency_phone->comm; ?>" class="contact edit_field hide_me" onkeyup="mask(this, mphone);" onblur="mask(this, mphone);" placeholder="xxx-xxx-xxxx" />
                            <span id="emergency_phoneSpan" class="contact edit_span"><?php echo  $my_emergency_phone->comm; ?></span>
                          </td>
                        </tr>
                      </table>
                  </td>
                </tr>
              </table>
            </form>
          </td>
          <td width="3%"  style="background:url(images/ui/answer_sides_r1_c2.jpg) right repeat-y #FFFFFF">&nbsp;</td>
        </tr>
      </table></td>
      <td width="5%" align="center" valign="top"><img src="images/spacer.gif" width="15" height="1" /></td>
      <td width="55%" align="center" valign="top"><table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF" bordercolor="#000000">
        <tr>
          <td width="3%" style="background:#FFFFFF">&nbsp;</td>
          <td width="73%" align="left" valign="top" bgcolor="#FFFFFF"><form id="employment_info_form">
            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>" />
            <table border="1" align="center" cellpadding="3" cellspacing="0" bordercolor="#ededed" class="info_holder">
              <tr>
                <td colspan="5" style="background:darkslategray; color:white; font-weight:bold" id="header_employment">
                <div style="float:right">
                  <button class="btn btn-xs btn-primary" id="edit_employment">Edit</button>
                  <button class="btn btn-xs btn-primary hide_me" id="save_employment">Save</button>
                </div>
                  <span style='font-weight:bold'>Employment Information </span> - <a href="https://www.uscis.gov/system/files_force/files/form/i-9-paper-version.pdf" target="_blank" style="font-size:0.8em; color:white">I9 USCIS Form</a></td>
              </tr>
              <tr style="visibility:hidden">
                <td align="right" bgcolor="#FFFFFF" style="height:1px"><img src="images/spacer.gif" width="100" height="1" /></td>
                <td colspan="2" bgcolor="#FFFFFF" style="height:1px"><img src="images/spacer.gif" width="330" height="1" /></td>
              </tr>
              <tr>
                <td align="left" class="td_label"><span style='font-weight:bold'>Type :</span></td>
                <td colspan="2" align="left">
                	<input type="radio" name="user_typeField" id="type_employee" value="1" <?php if ($user_type=="1") { echo 'checked="checked"'; } ?> class="user_type" />&nbsp;Employee
                    &nbsp;|&nbsp;
                    <input type="radio" name="user_typeField" id="type_contractor" value="2" <?php if ($user_type=="2") { echo 'checked="checked"'; } ?> class="user_type" />&nbsp;Contractor
                    <?php if ($_SESSION['user_type']=="3") { ?>
                    &nbsp;|&nbsp;
                    <input type="radio" name="user_typeField" id="type_employee" value="3" <?php if ($user_type=="3") { echo 'checked="checked"'; } ?> class="user_type" />&nbsp;Admin
                    <?php } ?>
                </td>
              </tr>
              <tr>
                <td align="left" class="td_label"><span style='font-weight:bold'>Department :</span></td>
                <td colspan="2" align="left"><?php echo $group_select; ?></td>
              </tr>
              <tr>
                <td align="left" class="td_label"><span style='font-weight:bold'>Shift :</span></td>
                <td colspan="2" align="left"><input name="shiftField" type="radio" value="1" <?php if ($shift=="1") { echo 'checked="checked"'; } ?> class="shift" />
                  1st&nbsp;
                  <input name="shiftField" type="radio" value="2" <?php if ($shift=="2") { echo 'checked="checked"'; } ?> class="shift" />
                  2nd&nbsp;
                  <input name="shiftField" type="radio" value="3" <?php if ($shift=="3") { echo 'checked="checked"'; } ?> class="shift" />
                  3rd </td>
              </tr>
              <tr>
                <td align="left" class="td_label"><span style='font-weight:bold'>Location  :</span></td>
                <td colspan="2" align="left"><select name="work_locationField" id="work_locationField" class="employment edit_field hide_me">
                  <option value="main" <?php if ($work_location=="main") { echo " selected"; } ?>>Main Office</option>
                </select>
                  <span id="work_locationSpan" class="employment edit_span"><?php echo $work_location; ?></span></td>
              </tr>
              <tr>
                <td align="left" class="td_label"><span style='font-weight:bold'>Employee #  :</span></td>
                <td colspan="2" align="left"><input name="employee_numberField" type="text" id="employee_numberField" value="<?php echo $employee_number; ?>" class="employment edit_field hide_me" />
                  <span id="employee_numberSpan" class="employment edit_span"><?php echo $employee_number; ?></span></td>
              </tr>
              <tr>
                <td align="left" class="td_label"><span style='font-weight:bold'>Hired Date  :</span></td>
                <td colspan="2" align="left"><input name="hired_dateField" type="text" id="hired_dateField" value="<?php echo $hired_date; ?>" class="employment edit_field hide_me" />
                  <span id="hired_dateSpan" class="employment edit_span"><?php echo $hired_date; ?></span></td>
              </tr>
              <tr>
                <td align="left" class="td_label"><span style='font-weight:bold'>I9 Filed  :</span></td>
                <td colspan="2" align="left"><input name="inine_filedField" type="checkbox" id="inine_filedField" value="Y" <?php if( $inine_filed=="Y") { echo "checked"; } ?> class="employment" />
                  </td>
              </tr>
              <tr<?php echo $hide_class; ?>>
                <td align="left" class="td_label"><a href="#payrates"></a> <span style='font-weight:bold'> Pay Rate  :</span></td>
                <td colspan="2" align="left"><input name="pay_rateField" type="text" id="pay_rateField" value="<?php echo $pay_rate; ?>" class="employment edit_field hide_me" style="width:100px" />
                  <input name="old_payrate" type="hidden" id="old_payrate" value="<?php echo $pay_rate; ?>" />
                  <span id="pay_rateSpan" class="employment edit_span">$<?php echo $pay_rate; ?></span> <a href="#payrates"></a>
                  <select name="pay_periodField" id="pay_periodField" class="employment edit_field hide_me">
                    <option value="" <?php if ($pay_period=="") { echo "selected"; } ?>>Per ...</option>
                    <option value="D" <?php if ($pay_period=="D") { echo "selected"; } ?>>Day</option>
                    <option value="H" <?php if ($pay_period=="H") { echo "selected"; } ?>>Hour</option>
                    <option value="M" <?php if ($pay_period=="M") { echo "selected"; } ?>>Month</option>
                  </select>
                  <span id="pay_periodSpan" class="employment edit_span">
                    <?php 
                        switch($pay_period) {
                        case "H":
                            $pay_period = " per hour";
                            break;
                        case "D":
                            $pay_period = " per day";
                            break;
                        case "M":
                            $pay_period = " per month";
                            break;
                        }
                        echo $pay_period; ?>
                  </span></td>
              </tr>
              <tr>
                <td align="left" class="td_label"><span style='font-weight:bold'>Pay   :</span></td>
                <td colspan="2" align="left"><select name="pay_scheduleField" id="pay_scheduleField" class="employment edit_field hide_me">
                  <option value="" <?php if ($pay_schedule=="") { echo "selected"; } ?>>Every ...</option>
                  <option value="D" <?php if ($pay_schedule=="D") { echo "selected"; } ?>>Day</option>
                  <option value="W" <?php if ($pay_schedule=="W") { echo "selected"; } ?>>Week</option>
                  <option value="BW" <?php if ($pay_schedule=="BW") { echo "selected"; } ?>>Bi-Weekly</option>
                  <option value="M" <?php if ($pay_schedule=="M") { echo "selected"; } ?>>Month</option>
                  <option value="TM" <?php if ($pay_schedule=="TM") { echo "selected"; } ?>>Twice-a-Month</option>
                </select>
                  <span id="pay_scheduleSpan" class="employment edit_span">
                    <?php 
                        switch($pay_schedule) {
							case "D":
								$pay_schedule = " daily";
								break;
							case "W":
								$pay_schedule = " weekly";
								break;
							case "BW":
								$pay_schedule = " bi-weekly";
								break;
							case "M":
								$pay_schedule = " monthly";
								break;
							case "TM":
								$pay_schedule = " twice monthly";
								break;
                        }
                        echo $pay_schedule; ?>
                    </span>
                  <select name="pay_methodField" id="pay_methodField" class="employment edit_field hide_me">
                    <option value="" <?php if ($pay_method=="") { echo "selected"; } ?>>By ...</option>
                    <option value="DD" <?php if ($pay_method=="DD") { echo "selected"; } ?>>Direct Deposit</option>
                    <option value="CK" <?php if ($pay_method=="CK") { echo "selected"; } ?>>Check</option>
                    <option value="CS" <?php if ($pay_method=="CS") { echo "selected"; } ?>>Cash</option>
                  </select>
                  <span id="pay_methodSpan" class="employment edit_span">
                    <?php 
                        switch($pay_method) {
							case "DD":
								$pay_method = " via direct deposit";
								break;
							case "CK":
								$pay_method = " by check";
								break;
							case "CS":
								$pay_method = " cash";
								break;
						}
                        echo $pay_method; ?>
                  </span></td>
              </tr>
              <?php if (in_array("account_manager", $arrGroup)) { ?>
              <tr>
                <td align="left" class="td_label"><span style='font-weight:bold'>Commissions:</span></td>
                <td colspan="2" align="left"><?php echo $commission_link; ?>&nbsp;</td>
              </tr>
              <tr<?php echo $hide_class; ?>>
                <td align="left" class="td_label"><span style='font-weight:bold'><span style='font-weight:bold'>Comm.</span> Rate  :</span></td>
                <td width="144" align="left"><input name="commissionrateField" type="text" id="commissionrateField" value="<?php echo $commissionrate; ?>" class="employment edit_field hide_me" />
                  <input name="old_commissionrate" type="hidden" id="old_commissionrate" value="<?php echo $commissionrate; ?>" />
                  <span id="commissionrateSpan" class="employment edit_span"><?php echo $commissionrate; ?></span></td>
                <td width="168" align="left"><a href="#commissionrates"></a></td>
              </tr>
              <?php } ?>
            </table>
          </form></td>
          <td width="3%"  style="background:url(images/ui/answer_sides_r1_c2.jpg) right repeat-y #FFFFFF">&nbsp;</td>
        </tr>
      </table></td>
    </tr>
    <!--
    <tr>
      <td align="center" valign="top"><table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF" bordercolor="#000000">

        <tr>
          <td style="background:#FFFFFF">&nbsp;</td>
          <td align="left" valign="top" bgcolor="#FFFFFF"><table border="1" align="center" cellpadding="3" cellspacing="0" bordercolor="#ededed">
            <tr>
              <td colspan="5" style="background:darkslategray; color:white; font-weight:bold"><span style='font-weight:bold'>Employee Information </span></td>
            </tr>
            <tr>
              <td align="right" bgcolor="#FFFFFF"><img src="images/spacer.gif" width="100" height="1" /></td>
              <td colspan="2" bgcolor="#FFFFFF"><img src="images/spacer.gif" width="250" height="1" /></td>
            </tr>
            <tr>
              <td align="right" class="td_label"><span style='font-weight:bold'>Department :</span></td>
              <td colspan="2" align="left"><?php echo $group_select; ?> </td>
            </tr>
            <tr>
              <td align="right" class="td_label"><span style='font-weight:bold'>Shift :</span></td>
              <td width="144" align="left"><input name="shift" type="radio" value="1" <?php if ($shift=="1") { echo 'checked="checked"'; } ?> />
                1st&nbsp;
                <input name="shift" type="radio" value="2" <?php if ($shift=="2") { echo 'checked="checked"'; } ?> />
                2nd</td>
              <td width="168" align="left">&nbsp;</td>
            </tr>
            <tr>
              <td align="right" class="td_label"><span style='font-weight:bold'>Location  :</span></td>
              <td colspan="2" align="left"><select name="work_location" id="work_location">
                <option value="main" <?php if ($work_location=="main") { echo " selected"; } ?>>Main Office</option>
              </select>              </td>
            </tr>
            <tr>
              <td align="right" class="td_label"><span style='font-weight:bold'>Employee #  :</span></td>
              <td colspan="2" align="left"><input name="employee_numberField" type="text" id="employee_numberField" value="<?php echo $employee_number; ?>" /></td>
            </tr>
            <tr>
              <td align="right" class="td_label"><span style='font-weight:bold'>Hired Date  :</span></td>
              <td align="left"><input name="hired_dateField" type="text" id="hired_dateField" value="<?php echo $hired_date; ?>" /></td>
              <td align="left"><a href="#" onclick="window.open('../calendar/popup.php?datefield=hired_dateField&amp;month=<?php echo date("m"); ?>&amp;year=<?php echo date("Y"); ?>','Calendar','toolbar=no,width=350,height=650,left=350,top=0,screenX=350,screenY=200,status=no,scrollbars=yes,resize=yes');return false"><img src="images/calendar.jpg" alt="Click here for calendar" width="31" height="31" border="0" /></a></td>
            </tr>
            <tr<?php echo $hide_class; ?>>
              <td align="right" class="td_label"><span style='font-weight:bold'> Reminders</span></td>
              <td align="left"><input name="reminder_90" type="checkbox" id="reminder_90" value="Y"<?php echo $reminder_90_checked; ?>  />
                90-Day Review</td>
              <td align="left"><input name="reminder_365" type="checkbox" id="reminder_365" value="Y"<?php echo $reminder_365_checked; ?> />
1-Year Review</td>
            </tr>
            <tr<?php echo $hide_class; ?>>
              <td align="right" class="td_label"><span style='font-weight:bold'>Current Pay Rate  :</span></td>
              <td align="left"><input name="payrate" type="text" id="payrate" value="<?php echo $pay_rate; ?>" /><input name="old_payrate" type="hidden" id="old_payrate" value="<?php echo $pay_rate; ?>" /></td>
              <td align="left"><a href="#payrates"></a></td>
            </tr>
            <?php if (in_array("account_manager", $arrGroup)) { ?>
            <tr>
              <td align="right" class="td_label"><span style='font-weight:bold'>Commissions:</span></td>
              <td colspan="2" align="left"><?php echo $commission_link; ?>&nbsp;</td>
            </tr>
            <tr<?php echo $hide_class; ?>>
              <td align="right" class="td_label"><span style='font-weight:bold'>Current <span style='font-weight:bold'>Commission</span> Rate  :</span></td>
              <td align="left"><input name="commissionrate" type="text" id="commissionrate" value="<?php echo $commissionrate; ?>" />
                <input name="old_commissionrate" type="hidden" id="old_commissionrate" value="<?php echo $commissionrate; ?>" /></td>
              <td align="left"><a href="#commissionrates"></a></td>
            </tr>
            <?php } ?>
            <tr<?php echo $hide_class; ?>>
              <td align="right" class="td_label"><span style='font-weight:bold'> Manage Days&nbsp;Off :</span></td>
              <td colspan="2" align="left"><table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td bgcolor="#ededed"><em><span style='font-weight:bold'>Assigned</span></em></td>
                  <td align="right" nowrap="nowrap" bgcolor="#ededed"><em><span style='font-weight:bold'>Available</span></em></td>
                  <td align="right" nowrap="nowrap" bgcolor="#9999FF"><em><span style='font-weight:bold'>Add/Remove Time </span></em></td>
                </tr>
                <tr>
                  <td align="left" valign="top"><em><?php echo number_format(($seconds_assigned /24 /3600), 2); ?></em></td>
                  <td align="left" valign="top" nowrap="nowrap"><input type="hidden" name="old_days_available" value="<?php echo round($full_days_available, 2); ?>" /><em><input type="text" size="5" name="days_available" value="<?php echo round($full_days_available, 2); ?>" /></em></td>
                  <td align="right" valign="top" nowrap="nowrap" bgcolor="#9999FF"><em>
                    Days or Hours
                    <input name="days_off" type="text" id="days_off" size="5" />
                    <br />
                    <select name="days_off_action" id="days_off_action">
  <option value="" selected="selected">Select action</option>
  <option value="add_hours">Add Hours</option>
  <option value="remove_hours">Remove Hours</option>
  <option value="add">Add Days</option>
  <option value="remove">Remove Days</option>
</select>
                  </em></td>
                </tr>
              </table></td>
              </tr>
            <tr<?php echo $hide_class; ?>>
              <td align="right" class="td_label"><span style='font-weight:bold'>Timeoff Available:</span></td>
              <td colspan="2" align="left"><?php echo $actual_days_available; ?>&nbsp;</td>
            </tr>
          </table></td>
          <td  style="background:url(images/ui/answer_sides_r1_c2.jpg) right repeat-y #FFFFFF">&nbsp;</td>
        </tr>
        <tr>
          <td width="3%" align="left" valign="top" style=""><img src="images/spacer.gif" width="1" height="2" /></td>
          <td width="73%" align="left" valign="top" style="background:url(images/ui/answer_bottom_r1.jpg) top center repeat-x">&nbsp;</td>
          <td width="3%" align="left" valign="top" style="background: url(images/ui/answer_bottom_r1_c2.jpg) top right no-repeat"><img src="images/spacer.gif" width="10" height="2" /></td>
        </tr>
      </table></td>
      <td width="5%" align="center" valign="top"><img src="images/spacer.gif" width="1" height="1" /></td>
      <td align="center" valign="top"><table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF" bordercolor="#000000">
        <tr>
          <td style="background:#FFFFFF">&nbsp;</td>
          <td align="left" valign="top" bgcolor="#FFFFFF"><table border="1" align="center" cellpadding="3" cellspacing="0" bordercolor="#ededed">
            <tr>
              <td colspan="5" style="background:darkslategray; color:white; font-weight:bold"><span style='font-weight:bold'>Work Information </span></td>
            </tr>
            <tr>
              <td width="112" align="right" bgcolor="#FFFFFF"><img src="images/spacer.gif" width="100" height="1" /></td>
              <td colspan="2" bgcolor="#FFFFFF"><img src="images/spacer.gif" width="275" height="1" /></td>
            </tr>
            <tr>
              <td colspan="5" align="center" nowrap="nowrap" bgcolor="#FFFFFF">&nbsp;</td>
              </tr>
            <tr>
              <td align="right" nowrap="nowrap" class="td_label"><span style='font-weight:bold'>Clock-in  Time :</span></td>
              <td colspan="2" align="left"><span style='font-weight:bold'>
                <select name="hourField_in" id="hourField_in">
				<?php for ($intHour=0;$intHour<13;$intHour++) { 
					$intValue = $intHour;
					if ($intHour<10) {
						$intValue = "0" . $intHour;
					}
					if ($intHour > 12) {
						$intValue = $intHour - 12;
						$intValue = "0" . $intValue;
					}
					?>
                  <option value="<?php echo $intHour; ?>"<?php if ($arrIn[0]==$intValue) { echo " selected"; } ?>><?php echo $intValue; ?></option>
				  <?php }	//end of for loop ?>
                </select>
                : </span>
                  <select name="minuteField_in" id="minuteField_in">
                    <?php for ($intMinute=0;$intMinute<60;$intMinute=$intMinute+15) { 
					$intValue = $intMinute;
					if ($intMinute<10) {
						$intValue = "0" . $intMinute;
					}
					?>
					<option value="<?php echo $intMinute; ?>"<?php if ($arrIn[1]==$intValue) { echo " selected"; } ?>><?php echo $intValue; ?></option>
					<?php }	//end of for loop ?>
                  </select>
                &nbsp;
                <input name="ampmField_in" type="radio" value="AM" <?php if ($arrInAM) { echo " checked"; } ?> />
                AM
                <input name="ampmField_in" type="radio" value="PM" <?php if (!$arrInAM) { echo " checked"; } ?> />
                PM</td>
            </tr>
            <tr>
              <td colspan="5" align="center" nowrap="nowrap" bgcolor="#ededed"><span style='font-weight:bold'>
                <input name="noearly_entry" type="checkbox" id="noearly_entry" value="Y" <?php echo $noearly_entry_checked; ?> />
No Early Entry&nbsp;|&nbsp;Early Entry Window:&nbsp;
<input name="early_entry_window" type="text" id="early_entry_window" style="width:15px" value="<?php echo $early_entry_window; ?>" />
minutes</span></td>
              </tr>
            <tr>
              <td align="right" nowrap="nowrap" class="td_label"><span style='font-weight:bold'>Clock-out  Time :</span></td>
              <td colspan="2" align="left"><span style='font-weight:bold'>
                <select name="hourField_out" id="hourField_out">
                  <?php for ($intHour=0;$intHour<13;$intHour++) { 
					$intValue = $intHour;
					if ($intHour<10) {
						$intValue = "0" . $intHour;
					}
					if ($intHour > 12) {
						$intValue = $intHour - 12;
						$intValue = "0" . $intValue;
					}
					?>
                  <option value="<?php echo $intHour; ?>"<?php if ($arrOut[0]==$intValue) { echo " selected"; } ?>><?php echo $intValue; ?></option>
				  <?php }	//end of for loop ?>
                </select>
                : </span>
                 <select name="minuteField_out" id="minuteField_out">
<?php for ($intMinute=0;$intMinute<60;$intMinute=$intMinute+15) { 
					$intValue = $intMinute;
					if ($intMinute<10) {
						$intValue = "0" . $intMinute;
					}
					?>
					<option value="<?php echo $intMinute; ?>"<?php if ($arrOut[1]==$intValue) { echo " selected"; } ?>><?php echo $intValue; ?></option>
					<?php }	//end of for loop ?>
				</select>
                &nbsp;
                <input name="ampmField_out" type="radio" value="AM" <?php if ($arrOutAM) { echo " checked"; } ?> />
                AM
                <input name="ampmField_out" type="radio" value="PM"<?php if (!$arrOutAM) { echo " checked"; } ?> />
                PM</td>
            </tr>
            <tr>
              <td align="right" nowrap="nowrap" class="td_label"><span style='font-weight:bold'>Lunch  Time :<br />
                <input type="checkbox" name="no_lunch" id="no_lunch" value="Y"<?php echo $no_lunch_checked; ?> />
                    <label for="no_lunch"></label>
                    <span style='font-weight:bold'>No Lunch</span></span></td>
              <td colspan="2" align="left"><span style='font-weight:bold'>
                <select name="hourField_lunch" id="hourField_lunch">
                  <?php for ($intHour=0;$intHour<13;$intHour++) { 
					$intValue = $intHour;
					if ($intHour<10) {
						$intValue = "0" . $intHour;
					}
					if ($intHour > 12) {
						$intValue = $intHour - 12;
						$intValue = "0" . $intValue;
					}
					?>
                  <option value="<?php echo $intHour; ?>"<?php if ($arrLunch[0]==$intValue) { echo " selected"; } ?>><?php echo $intValue; ?></option>
                  <?php }	//end of for loop ?>
                </select>
                : </span>
                  <select name="minuteField_lunch" id="minuteField_lunch">
                    <?php for ($intMinute=0;$intMinute<60;$intMinute=$intMinute+15) { 
					$intValue = $intMinute;
					if ($intMinute<10) {
						$intValue = "0" . $intMinute;
					}
					?>
                    <option value="<?php echo $intMinute; ?>"<?php if ($arrLunch[1]==$intValue) { echo " selected"; } ?>><?php echo $intValue; ?></option>
                    <?php }	//end of for loop ?>
                  </select>
                &nbsp;
                <input name="ampmField_lunch" type="radio" value="AM" <?php if ($arrLunchAM) { echo " checked"; } ?> />
                AM
                <input name="ampmField_lunch" type="radio" value="PM"<?php if (!$arrLunchAM) { echo " checked"; } ?> />
                PM</td>
            </tr>
            <tr>
              <td colspan="5" align="left" nowrap="nowrap" bgcolor="#FFFFFF">&nbsp;</td>
              </tr>
            <tr>
              <td colspan="5" style="background:darkslategray; color:white; font-weight:bold" nowrap="nowrap" bgcolor="darkslategray">Overtime</td>
              </tr>
            <tr>
              <td align="right" nowrap="nowrap" class="td_label"><span style='font-weight:bold'>Hours Per Day :</span></td>
              <td colspan="2" align="left"><input name="overtime_hours_day" type="text" id="overtime_hours_day" value="<?php echo $overtime_hours_day; ?>" size="2" />
                <input name="old_overtime_hours_day" type="hidden" id="old_overtime_hours_day" value="<?php echo $overtime_hours_day; ?>" /></td>
            </tr>
            <tr>
              <td align="right" nowrap="nowrap" class="td_label"><span style='font-weight:bold'>Hours Per Week :</span></td>
              <td colspan="2" align="left">
              <input name="overtime_hours_week" type="text" id="overtime_hours_week" value="<?php echo $overtime_hours_week; ?>" size="2" />
              <input name="old_overtime_hours_week" type="hidden" id="old_overtime_hours_week" value="<?php echo $overtime_hours_week; ?>" /></td>
            </tr>
            <tr>
              <td align="right" nowrap="nowrap" class="td_label"><span style='font-weight:bold'>From :</span></td>
              <td align="left" bgcolor="#FFFFFF"><input name="overtime_start_dateField" type="text" id="overtime_start_dateField" value="<?php echo $overtime_start_date; ?>" />
              <input name="old_overtime_start_date" type="hidden" id="old_overtime_start_date" value="<?php echo $overtime_start_date; ?>" /></td>
              <td align="left" bgcolor="#FFFFFF"><a href="#" onclick="window.open('../calendar/popup.php?datefield=overtime_start_dateField&amp;month=<?php echo date("m"); ?>&amp;year=<?php echo date("Y"); ?>','Calendar','toolbar=no,width=350,height=650,left=350,top=0,screenX=350,screenY=200,status=no,scrollbars=yes,resize=yes');return false"><img src="images/calendar.jpg" alt="Click here for calendar" width="31" height="31" border="0" /></a></td>
            </tr>
            <tr>
              <td align="right" nowrap="nowrap" class="td_label"><span style='font-weight:bold'>Through :</span></td>
              <td align="left" bgcolor="#FFFFFF"><input name="overtime_end_dateField" type="text" id="overtime_end_dateField" value="<?php echo $overtime_end_date; ?>" />
              <input name="old_overtime_end_date" type="hidden" id="old_overtime_end_date" value="<?php echo $overtime_end_date; ?>" /></td>
              <td align="left" bgcolor="#FFFFFF"><a href="#" onclick="window.open('../calendar/popup.php?datefield=overtime_end_dateField&amp;month=<?php echo date("m"); ?>&amp;year=<?php echo date("Y"); ?>','Calendar','toolbar=no,width=350,height=650,left=350,top=0,screenX=350,screenY=200,status=no,scrollbars=yes,resize=yes');return false"><img src="images/calendar.jpg" alt="Click here for calendar" width="31" height="31" border="0" /></a></td>
            </tr>
			<?php if ($days_available>0) { ?>
            <tr>
              <td colspan="5" style="background:darkslategray; color:white; font-weight:bold"><span style='font-weight:bold'>Time Off</span></td>
              </tr>
            <tr>
              <td align="right" class="td_label"><span style='font-weight:bold'>Time Off    :</span></td>
              <td colspan="2" align="left" class="headwarning">
                <select name="vacation_type">
                    <option value="" selected="selected">Select Type of Absence</option>
                    <option value="bereavement">Bereavement</option>
                    <option value="dayoff">Day Off</option>
                    <option value="jury">Jury Duty</option>
                    <option value="maternity_paternity">Maternity/Paternity</option>
                    <option value="military">Military</option>
                    <option value="other">Other</option>
                    <option value="personal">Personal Day</option>
                    <option value="vacation">Vacation</option>
                </select>              </td>
            </tr>
            <tr>
              <td align="right" nowrap="nowrap" class="td_label"><span style='font-weight:bold'>Time Off  Start  :</span></td>
              <td width="144" align="left" bgcolor="#FFFFFF"><input name="vacation_start_dateField" type="text" id="vacation_start_dateField" /></td>
              <td width="148" align="left" bgcolor="#FFFFFF"><a href="#" onclick="window.open('../calendar/popup.php?datefield=vacation_start_dateField&amp;month=<?php echo date("m"); ?>&amp;year=<?php echo date("Y"); ?>','Calendar','toolbar=no,width=350,height=650,left=350,top=0,screenX=350,screenY=200,status=no,scrollbars=yes,resize=yes');return false"><img src="images/calendar.jpg" alt="Click here for calendar" width="31" height="31" border="0" /></a></td>
            </tr>
            <tr>
              <td align="right" nowrap="nowrap" class="td_label"><span style='font-weight:bold'>Time Off End  :</span></td>
              <td align="left" bgcolor="#FFFFFF"><input name="vacation_end_dateField" type="text" id="vacation_end_dateField" /></td>
              <td align="left" bgcolor="#FFFFFF"><a href="#" onclick="window.open('../calendar/popup.php?datefield=vacation_end_dateField&amp;month=<?php echo date("m"); ?>&amp;year=<?php echo date("Y"); ?>','Calendar','toolbar=no,width=350,height=650,left=350,top=0,screenX=350,screenY=200,status=no,scrollbars=yes,resize=yes');return false"><img src="images/calendar.jpg" alt="Click here for calendar" width="31" height="31" border="0" /></a></td>
            </tr>
            <tr>
              <td align="right" nowrap="nowrap" class="td_label"><span style='font-weight:bold'>Time Off Desc : </span></td>
              <td colspan="2" align="left" bgcolor="#FFFFFF"><textarea name="vacation_descriptionField" style="width:260px" rows="2" id="vacation_descriptionField">
</textarea></td>
              </tr>
            <tr>
              <td colspan="5" align="center" nowrap="nowrap" bgcolor="darkslategray"><span style='font-weight:bold'>Upload Documents</span></td>
              </tr>
			  <?php } ?>
          </table></td>
          <td  style="background:url(images/ui/answer_sides_r1_c2.jpg) right repeat-y #FFFFFF">&nbsp;</td>
        </tr>
        <tr>
          <td width="3%" align="left" valign="top" style=""><img src="images/spacer.gif" width="1" height="2" /></td>
          <td width="73%" align="left" valign="top" style="background:url(images/ui/answer_bottom_r1.jpg) top center repeat-x">&nbsp;</td>
          <td width="3%" align="left" valign="top" style="background: url(images/ui/answer_bottom_r1_c2.jpg) top right no-repeat"><img src="images/spacer.gif" width="10" height="2" /></td>
        </tr>
      </table></td>
    </tr>
    <tr>
      <td align="center" valign="top">&nbsp;</td>
      <td align="center" valign="top">&nbsp;</td>
      <td align="center" valign="top">&nbsp;</td>
    </tr>
    
    <tr>
      <td colspan="5" align="center" valign="top"><table width="600" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF" bordercolor="#000000">
        
        <tr>
          <td style="background:#FFFFFF">&nbsp;</td>
          <td align="left" valign="top" bgcolor="#FFFFFF"><table border="1" cellpadding="3" cellspacing="0" bordercolor="#ededed" align="center">
              <tr>
                <td colspan="5" style="background:darkslategray; color:white; font-weight:bold"><span style='font-weight:bold'>Uploaded Documents</span></td>
              </tr>
              <tr>
                <td width="147" align="right" nowrap="nowrap" class="td_label"><span style='font-weight:bold'>Document Type:</span></td>
                <td colspan="2" align="left" bgcolor="#FFFFFF"><select name="document_type">
                    <option value="">Select Document Type</option>
                    <option value="application">Application</option>
                    <option value="contracts">Contracts</option>
                    <option value="employee_agreement">Employee Agreement</option>
                    <option value="evaluations">Evaluations</option>
                    <option value="expense_sheets">Expense Sheets</option>
                    <option value="identification">Identification</option>
                    <option value="insurance_document">Insurance Documents</option>
                    <option value="payroll_hours">Payroll Hours</option>
                    <option value="resignation">Resignation</option>
                    <option value="tax_information">Tax Information</option>
                    <option value="timeoff_request">Vacation/Sick/Personal Time off Request</option>
                    <option value="workers_comp_claim">Workers Comp Claim</option>
                    <option value="write_up">Write-Ups/Warnings</option>
                  </select>                </td>
              </tr>
              <tr>
                <td align="right" nowrap="nowrap" class="td_label"><span style='font-weight:bold'>Document Name:</span></td>
                <td colspan="2" align="left" bgcolor="#FFFFFF"><input type="text" name="document_name" id="document_name" /></td>
              </tr>
              <tr>
                <td align="right" nowrap="nowrap" class="td_label"><span style='font-weight:bold'>Description:</span></td>
                <td colspan="2" align="left" bgcolor="#FFFFFF"><textarea name="document_description" id="document_description" cols="45" rows="3"></textarea></td>
              </tr>
              <tr>
                <td align="right" nowrap="nowrap" class="td_label"><span style='font-weight:bold'>Expiration Date:</span></td>
                <td width="144" align="left" bgcolor="#FFFFFF"><input name="expiration_dateField" type="text" id="expiration_dateField" value="<?php 
				if ($expiration_date!="" && $expiration_date!="&nbsp;") {
					//echo $expiration_date;
					echo date("m/d/Y", $expiration_date);
				}
				?>" /></td>
                <td width="160" align="left" bgcolor="#FFFFFF"><a href="#" onclick="window.open('../calendar/popup.php?datefield=expiration_dateField&amp;month=<?php echo date("m"); ?>&amp;year=<?php echo date("Y"); ?>','Calendar','toolbar=no,width=350,height=650,left=350,top=0,screenX=350,screenY=200,status=no,scrollbars=yes,resize=yes');return false"><img src="images/calendar.jpg" alt="Click here for calendar" width="31" height="31" border="0" /></a></td>
              </tr>
              <tr>
                <td align="right" nowrap="nowrap" class="td_label"><span style='font-weight:bold'>Reminder :</span></td>
                <td colspan="2" align="left" bgcolor="#FFFFFF"><label for="reminder_days"></label>
                  <input name="reminder_days" type="text" id="reminder_days" size="3" value="28" />
                  &nbsp;days</td>
              </tr>
              <tr>
                <td align="right" nowrap="nowrap" class="td_label"><span style='font-weight:bold'>File:</span></td>
                <td colspan="2" align="left" bgcolor="#FFFFFF"><input type="file" name="user_filename" id="user_filename" /></td>
              </tr>
              <tr>
                <td align="right" nowrap="nowrap" class="td_label"><span style='font-weight:bold'>Or Choose Local File:</span></td>
                <td colspan="2" align="left" bgcolor="#FFFFFF">personnelfiles/
                  <input type="text" name="personnelfile" id="personnelfile" /></td>
              </tr>
              <tr>
                <td align="right" nowrap="nowrap" class="td_label">&nbsp;</td>
                <td colspan="2" align="left" bgcolor="#FFFFFF"><em>Press the Submit Button to Upload</em></td>
              </tr>
              <tr>
                <td colspan="5" align="left" nowrap="nowrap" bgcolor="#FFFFFF">&nbsp;</td>
            </tr>
			<?php if ($document_employee_cells!="") { ?>
            <tr>
            <td colspan="5" align="left" nowrap="nowrap">
            <table width="100%" border="0" cellspacing="0" cellpadding="2">
            <tr>
              <td width="66%" align="left" class="td_label"><span style='font-weight:bold'>Employee Document</span></td>
              <td width="16%" align="left" nowrap="nowrap" class="td_label"><span style='font-weight:bold'>Expiration Date</span></td>
              <td width="8%" align="center" class="td_label"><span style='font-weight:bold'>Type</span></td>
              <td width="10%" align="center" class="td_label"><span style='font-weight:bold'>Delete</span></td>
            </tr>
            <?php echo $document_employee_cells; ?>
            </table>            </td>
            </tr>
            <?php } ?>
          </table></td>
          <td  style="background:url(images/ui/answer_sides_r1_c2.jpg) right repeat-y #FFFFFF">&nbsp;</td>
        </tr>
        -->
        <tr>
          <td colspan="5" align="left" valign="top" style="">
          	<hr />
          </td>
        </tr>
        <tr>
          <td align="left" valign="top" style="">
          	<form id="tax_federal_info_form">
              	<input type="hidden" name="user_id" value="<?php echo $user_id; ?>" />
                <table border="1" cellpadding="3" cellspacing="0" bordercolor="#ededed" align="center" class="info_holder" width="100%">
                  <tr>
                    <td colspan="2" style="background:darkslategray; color:white; font-weight:bold" id="header_tax_federal">
                        <div style="float:right">
                          <button class="btn btn-xs btn-primary" id="edit_tax_federal">Edit</button>
                          <button class="btn btn-xs btn-primary hide_me" id="save_tax_federal">Save</button>
                        </div>
                        <span style='font-weight:bold'>Tax Information</span>
                    - Federal - <a href="https://www.irs.gov/pub/irs-pdf/fw4.pdf" target="_blank" style="color:white; font-size:0.8em">W4 IRS Form</a></td>
                  </tr>
                  <tr style="visibility:hidden">
                    <td align="right" bgcolor="#FFFFFF" style="height:1px"><img src="images/spacer.gif" width="100" height="1" /></td>
                    <td bgcolor="#FFFFFF" style="height:1px"><img src="images/spacer.gif" width="330" height="1" /></td>
                  </tr>
                  <tr >
                      <td align="left" class="td_label" nowrap="nowrap">
                          <span style='font-weight:bold'>Filing Status :</span></td>
                      <td align="left">
                      	<?php
						$filing_status_federal = "";
						if (isset($tax_federal_info->filing_status_federalField)) {
							$filing_status_federal = $tax_federal_info->filing_status_federalField;
						}
						?>
                        <select name="filing_status_federalField" type="text" id="filing_status_federalField" value="" class="tax_federal edit_field hide_me">
                        	<option value="" <?php if ($filing_status_federal=="") { echo "selected"; } ?>>Select from List...</option>
                            <option value="D" <?php if ($filing_status_federal=="D") { echo "selected"; } ?>>No Withholding</option>
                            <option value="M" <?php if ($filing_status_federal=="M") { echo "selected"; } ?>>Married</option>
                            <option value="MW" <?php if ($filing_status_federal=="MW") { echo "selected"; } ?>>Married / Withhold Higher Rate</option>
                            <option value="S" <?php if ($filing_status_federal=="S") { echo "selected"; } ?>>Single</option>
                        </select>
                        <span id="filing_status_federalSpan" class="tax_federal edit_span">
                        <?php
						//show the full text						
						switch($filing_status_federal) {
							case "D":
								echo "No Withholding";
								break;
							case "M":
								echo "Married";
								break;
							case "MW":
								echo "Married / Withhold Higher Rate";
								break;
							case "S":
								echo "Single";
								break;
						}
						?>
                        </span>
                      </td>
                    </tr>
                    <tr >
                      <td align="left" class="td_label" nowrap="nowrap">
                          <span style='font-weight:bold'>Allowances :</span></td>
                      <td align="left">
                      	<?php
						$allowances_federal = "";
						if (isset($tax_federal_info->allowances_federalField)) {
							$allowances_federal = $tax_federal_info->allowances_federalField;
						}
						?>
                        <input name="allowances_federalField" type="number" id="allowances_federalField" value="<?php echo $allowances_federal; ?>" class="tax_federal edit_field hide_me" width="50px" />
                        <span id="allowances_federalSpan" class="tax_federal edit_span">
                        <?php
						echo $allowances_federal;
						?>
                        </span>
                      </td>
                    </tr>
                    <tr >
                      <td align="left" class="td_label" nowrap="nowrap">
                          <span style='font-weight:bold'>Withholdings :</span></td>
                      <td align="left">
                      	<?php
						$withholdings_federal = "";
						if (isset($tax_federal_info->withholdings_federalField)) {
							$withholdings_federal = $tax_federal_info->withholdings_federalField;
						}
						?>
                        <input name="withholdings_federalField" type="number" id="withholdings_federalField" value="<?php echo $withholdings_federal; ?>" class="tax_federal edit_field hide_me" width="50px" />
                        <span id="withholdings_federalSpan" class="tax_federal edit_span">
                        <?php
						echo $withholdings_federal;
						?>
                        </span>
                      </td>
                    </tr>
              	</table>	
             </form>
          </td>
          <td align="left" valign="top" style="">&nbsp;</td>
          <td align="left" valign="top" style="">
          	<form id="tax_state_info_form">
              	<input type="hidden" name="user_id" value="<?php echo $user_id; ?>" />
                <table border="1" cellpadding="3" cellspacing="0" bordercolor="#ededed" align="center" class="info_holder" width="100%">
                  <tr>
                    <td colspan="2" style="background:darkslategray; color:white; font-weight:bold" id="header_tax_state">
                        <div style="float:right">
                          <button class="btn btn-xs btn-primary" id="edit_tax_state">Edit</button>
                          <button class="btn btn-xs btn-primary hide_me" id="save_tax_state">Save</button>
                        </div>
                        <span style='font-weight:bold'>Tax Information</span>
                    - State</td>
                  </tr>
                  <tr style="visibility:hidden">
                    <td align="right" bgcolor="#FFFFFF" style="height:1px"><img src="images/spacer.gif" width="100" height="1" /></td>
                    <td bgcolor="#FFFFFF" style="height:1px"><img src="images/spacer.gif" width="330" height="1" /></td>
                  </tr>
                  <tr >
                      <td align="left" class="td_label" nowrap="nowrap">
                          <span style='font-weight:bold'>Filing Status :</span></td>
                      <td align="left">
                      	<?php
						$filing_status_state = "";
						if (isset($tax_state_info->filing_status_stateField)) {
							$filing_status_state = $tax_state_info->filing_status_stateField;
						}
						?>
                        <select name="filing_status_stateField" type="text" id="filing_status_stateField" value="" class="tax_state edit_field hide_me">
                        	<option value="" <?php if ($filing_status_state=="") { echo "selected"; } ?>>Select from List...</option>
                            <option value="D" <?php if ($filing_status_state=="D") { echo "selected"; } ?>>No Withholding</option>
                            <option value="M" <?php if ($filing_status_state=="M") { echo "selected"; } ?>>Married (1 income)</option>
                            <option value="MS" <?php if ($filing_status_state=="MS") { echo "selected"; } ?>>Married/Single (2+ incomes)</option>
                            <option value="S" <?php if ($filing_status_state=="H") { echo "selected"; } ?>>Head of Household</option>
                        </select>
                        <span id="filing_status_stateSpan" class="tax_state edit_span">
                        <?php
						//show the full text						
						switch($filing_status_state) {
							case "D":
								echo "No Withholding";
								break;
							case "M":
								echo "Married (1 income)";
								break;
							case "MS":
								echo "Married/Single (2+ incomes)";
								break;
							case "H":
								echo "Head of Household";
								break;
						}
						?>
                        </span>
                      </td>
                    </tr>
                    <tr >
                      <td align="left" class="td_label" nowrap="nowrap">
                          <span style='font-weight:bold'>Allowances :</span></td>
                      <td align="left">
                      	<?php
						$allowances_state = "";
						if (isset($tax_state_info->allowances_stateField)) {
							$allowances_state = $tax_state_info->allowances_stateField;
						}
						?>
                        <input name="allowances_stateField" type="number" id="allowances_stateField" value="<?php echo $allowances_state; ?>" class="tax_state edit_field hide_me" width="50px" />
                        <span id="allowances_stateSpan" class="tax_state edit_span">
                        <?php
						echo $allowances_state;
						?>
                        </span>
                      </td>
                    </tr>
                    <tr >
                      <td align="left" class="td_label" nowrap="nowrap">
                          <span style='font-weight:bold'>Withholdings :</span></td>
                      <td align="left">
                      	<?php
						$withholdings_state = "";
						if (isset($tax_state_info->withholdings_stateField)) {
							$withholdings_state = $tax_state_info->withholdings_stateField;
						}
						?>
                        <input name="withholdings_stateField" type="number" id="withholdings_stateField" value="<?php echo $withholdings_state; ?>" class="tax_state edit_field hide_me" width="50px" />
                        <span id="withholdings_stateSpan" class="tax_state edit_span">
                        <?php
						echo $withholdings_state;
						?>
                        </span>
                      </td>
                    </tr>
                    
              	</table>	
             </form>
          </td>
          <td align="left" valign="top" style="">&nbsp;</td>
          <?php 
		  $contractor_display = 'style="display:none"';
		  if ($user_type=="2") {
			  $contractor_display = '';
		  }
		  ?>
          <td align="left" valign="top" <?php echo $contractor_display; ?> id="contractor_info_holder">
          	<form id="contractor_info_form">
              	<input type="hidden" name="user_id" value="<?php echo $user_id; ?>" />
                <table border="1" cellpadding="3" cellspacing="0" bordercolor="#ededed" align="center" class="info_holder" width="100%">
                  <tr>
                    <td colspan="2" style="background:darkslategray; color:white; font-weight:bold" id="header_contractor">
                        <div style="float:right">
                          <button class="btn btn-xs btn-primary" id="edit_contractor">Edit</button>
                          <button class="btn btn-xs btn-primary hide_me" id="save_contractor">Save</button>
                        </div>
                        <span style='font-weight:bold'>Contractor Information</span></td>
                  </tr>
                  <tr style="visibility:hidden">
                    <td align="right" bgcolor="#FFFFFF" style="height:1px"><img src="images/spacer.gif" width="100" height="1" /></td>
                    <td bgcolor="#FFFFFF" style="height:1px"><img src="images/spacer.gif" width="330" height="1" /></td>
                  </tr>
                  <tr >
                      <td align="left" nowrap="nowrap" class="td_label">
                          <span style='font-weight:bold'>Type  :</span></td>
                      <td align="left">
                      	<?php
						$contractor_type = "";
						if (isset($contractor_info->contractor_typeField)) {
							$contractor_type = $contractor_info->contractor_typeField;
						}
						?>
                        <input type="radio" name="contractor_typeField" id="contractor_type_business" class="contractor" value="Business" <?php if ($contractor_type=="Business") { echo "checked"; }?> />&nbsp;Business
                        &nbsp;|&nbsp;
                        <input type="radio" name="contractor_typeField" id="contractor_type_individual" class="contractor" value="Individual" <?php if ($contractor_type=="Individual") { echo "checked"; }?> />&nbsp;Individual
                        <span id="contractor_typeSpan" class="contractor edit_span"><?php echo $contractor_type; ?></span>
                      </td>
                  </tr>
                  <tr >
                      <td align="left" nowrap="nowrap" class="td_label">
                          <span style='font-weight:bold'>Business Name  :</span></td>
                      <td align="left">
                      	<?php
						$business_name = "";
						if (isset($contractor_info->business_nameField)) {
							$business_name = $contractor_info->business_nameField;
						}
						?>
                        <input type="text" name="business_nameField" style="width:260px" id="business_nameField" class="contractor edit_field hide_me" value="<?php echo $business_name; ?>" />
                        <span id="business_nameSpan" class="contractor edit_span"><?php echo $business_name; ?></span>
                      </td>
                  </tr>
                  <tr >
                      <td align="left" nowrap="nowrap" class="td_label"><input type="hidden" name="address_uuid" value="<?php echo $my_address->uuid; ?>" />
                          <span style='font-weight:bold'>Address  :</span></td>
                      <td align="left">
                      <?php
						$business_street = "";
						$business_city = "";
						$business_state = "";
						$business_zip = "";
						if (isset($contractor_info->streetField)) {
							$business_street = $contractor_info->streetField;
							$business_city = $contractor_info->cityField;
							$business_state = $contractor_info->stateField;
							$business_zip = $contractor_info->zipField;
						}
						?>
                        <textarea name="streetField" style="width:260px" rows="2" id="streetField" class="contractor edit_field hide_me"><?php echo $business_street; ?></textarea>
                        <span id="streetSpan" class="contractor edit_span"><?php echo $business_street; ?></span>
                      </td>
                    </tr>
                    <tr >
                      <td align="left" nowrap="nowrap" class="td_label"><span style='font-weight:bold'>City, State Zip   :</span></td>
                      <td align="left" nowrap="nowrap"><input value="<?php echo $business_city; ?>" name="cityField" type="text" id="cityField" size="20" class="contractor edit_field hide_me" />
                          <input name="stateField" type="text" id="stateField"  style="width:25px" value="<?php echo $business_state; ?>" class="contractor edit_field hide_me" />
                          <input name="zipField" type="text" id="zipField" style="width:50px" value="<?php echo $business_zip; ?>" class="contractor edit_field hide_me" />
                          <span id="citySpan" class="contractor edit_span"><?php echo $business_city; ?></span>
                          <span id="stateSpan" class="contractor edit_span"><?php echo $business_state; ?></span>
                          <span id="zipSpan" class="contractor edit_span"><?php echo $business_zip; ?></span>
                       </td>
                    </tr>
                </table>
            </form>
          </td>
        </tr>
        <tr>
          <td colspan="5" align="left" valign="top" style=""><hr /> </td>
        </tr>
        <tr>
          <td align="left" valign="top" style="">
          	<div>
            	<a href="#employees/reimbursments/<?php echo $user_id; ?>">Manage Reimbursments</a>
            </div>
            <div>
            	<a href="#paycheck/create/<?php echo $user_id; ?>">Create Check</a>
            </div>
            <div>
            	<a href="#employees/checks/<?php echo $user_id; ?>">List Checks</a>
            </div>
          </td>
          <td align="left" valign="top" style="">&nbsp;
          	
          </td>
          <td align="left" valign="top" style="">&nbsp;</td>
          <td align="left" valign="top" style="">&nbsp;</td>
          <td align="left" valign="top" style=""><table border="1" cellpadding="3" cellspacing="0" bordercolor="#ededed" align="center" class="info_holder" width="100%">
            <tr>
              <td colspan="5" style="background:darkslategray; color:white; font-weight:bold" id="header_personal2"><div style="float:right">
                <button class="btn btn-xs btn-primary" id="new_notes">New</button>
              </div>
                <span style='font-weight:bold'>Notes</span></td>
            </tr>
            <tr>
              <td colspan="5"><a name="notes" id="notes"></a>
                <div id="employee_notes_table_holder">
                  <!--
                        <table border=1 cellspacing=0 cellpadding=2 align="center" bordercolor="#dddddd" width="100%" id="employee_notes_table">
                            <form id="new_notes_form">
                            <tr style="display:none" id="new_notes_row">
                                <td align="left" valign="top" colspan="2">
                                    <textarea name="notesField" id="notesField" style="width:100%; height:75px" placeholder="Enter your Employee note here"></textarea>
                                </td>
                                <td align="left" valign="top" colspan="2">
                                    <button class="btn btn-xs btn-primary" id="save_new_notes">Save</button>
                                    <input type="hidden" name="user_id" id="user_id" value="<?php echo $user_id; ?>">
                                </td>
                            </tr>
                            </form>
                          <?php
                            /*
							$x = 0;
                           foreach($resultnotes as $note) {
                            
                                // Changing Background color for each alternate row
                                if (($x%2)==0) { $bgcolor="#FFFFFF"; } else { $bgcolor="#C0C0C0"; }
                            
                                // Retreiving data and putting it in local variables for each row
                                $notes_id = $note->notes_id; 
                                $notes = $note->notes; 
                                $time_stamp = $note->time_stamp; 
                                $time_stamp = date("m/d/y g:ia", strtotime($time_stamp));
                                $user_name = $note->user_name; 
                                $callback_date = $note->callback_date; 
                                $contact = $note->contact; 
                                $status = $note->status; 
                            ?>
                                <tr bgcolor="<?php echo $bgcolor; ?>" class="initial_notes"> 
                                    <td align="left" valign="top" width="1%" nowrap="nowrap"> 
                                      <?php echo $time_stamp; ?>
                                    </td>
                                    <td align="left" valign="top" width="1%" nowrap="nowrap"> 
                                      <?php echo $user_name; ?>
                                    </td><td align="left" valign="top"> 
                                      <?php echo $notes; ?>
                                    </td>
                                    <td align="left" valign="top">
                                        <?php echo $status; ?>
                                    </td>
                                </tr>
                            <?php
                            } // end foreach
                            */
							?>
                        </table>
                        -->
                </div></td>
            </tr>
          </table></td>
        </tr>
      </table>
  </td>
    </tr>
    <!--
    <tr>
      <td colspan="5" align="center" valign="top"><input type="submit" name="Submit" value="Submit" />
      &nbsp;<br />
      <table width="50%" border="0" align="center" cellpadding="5" cellspacing="0">
        <tr>
          <td width="50%" align="center" nowrap="nowrap"><input name="delete_me" type="checkbox" id="delete_me" value="Y" />
Check this box to <font color="#FF0000"><span style='font-weight:bold'>delete</span></font> this employee </td>
          </tr>
        <tr>
          <td align="center" nowrap="nowrap"><input name="fire_me" type="checkbox" id="fire_me" value="Y" />
Check this box to <font color="#FF0000"><span style='font-weight:bold'>fire</span></font> this employee</td>
          </tr>
        <tr>
          <td align="center" nowrap="nowrap"><input name="reinstate" type="checkbox" id="reinstate" value="Y" />
Check this box to <font color="green"><span style='font-weight:bold'>reinstate</span></font> this employee</td>
        </tr>
      </table></td>
    </tr>
    -->
  </table>

  <?php
$total_days = 0;
if (count($resultevents) > 0) {
	foreach($resultevents as $event) {
		$events_uuid = $event->events_uuid;
		$name = $event->name;
		$description = $event->description;
		$type = $event->attribute;
		$date_begin = $event->date_begin;
		$date_completed = $event->date_completed;
		$working_days = $event->working_days;
		if ($type=="other") {
			//$working_days = 0;
		}
		$active = $event->active;
		$verified = $event->verified;
		//echo "act: " . $active . "<BR>";
		if ($active=="N") {
			$active = "Not Approved<br /><a href='approve_dayoff.php?approved=y&id=" . $events_uuid . "&user_uuid=" . $my_user->uuid . "'' title='Click to Approve' style='font-weight:bold;color:green'>Approve All</a>";
			//orange marker
			$color_indicator = "#FFCC00";
			//it might be timeoff request?
			if ($type=="timeoff") { 
				$color_indicator = "#FFFF99";
			}
		} else {
			$active = "Approved<br /><a href='approve_dayoff.php?approved=n&id=" . $events_uuid . "&user_uuid=" . $my_user->uuid . "'' title='Click to Disapprove' style='font-weight:bold;color:red'>Disapprove All</a>";
			//green marker
			$color_indicator = "#66CC66";
			//it might be timeoff request?
			if ($type=="timeoff") { 
				$color_indicator = "#33FF00";
			}
		}
		$events_table .= "<tr><td align='left' nowrap valign='top' bgcolor='" . $color_indicator . "'><a href='events_edit.php?id=" . $events_uuid . "&user_uuid=" . $my_user->uuid . "' title='Click to edit request'>" . $name . "</a></td><td align='left' nowrap valign='top'>" . $type . "</td><td align='left' valign='top'>" . $active . "</td><td align='left' valign='top'>". $description . "&nbsp;</td><td align='left' valign='top'>" . date("D m/d/Y", strtotime($date_begin)) . "</td><td align='left' valign='top'>" . date("D m/d/Y", strtotime($date_completed)) . "</td><td align='center' valign='top'>" . $working_days . "</td></tr>";
		$total_days += $working_days;
	}
?>
  <a name="timeoff"></a>
  <table width="770" border="1" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td colspan="7" align="center" bgcolor="#EDEDED"><span style='font-weight:bold'>TIME OFF REPORT</span></td>
  </tr>
  <tr>
    <td align="left"><span style='font-weight:bold'>Name</span></td>
    <td align="left"><span style='font-weight:bold'>Type</span></td>
	<td align="left"><span style='font-weight:bold'>Approved</span></td>
    <td align="left"><span style='font-weight:bold'>Description</span></td>
    <td align="left"><span style='font-weight:bold'>Start</span></td>
    <td align="left"><span style='font-weight:bold'>End</span></td>
	<td align="left"><span style='font-weight:bold'>Working Days</span></td>
  </tr>
  <?php echo $events_table; ?>
</table>
  <table width="40%" border="0" align="center" cellpadding="2" cellspacing="0">
    <tr>
      <td width="25%" align="center" valign="top" bordercolor="#000000"><table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF" bordercolor="#000000">
          
          <tr>
            <td style="background:#FFFFFF">&nbsp;</td>
            <td align="left" valign="top" bgcolor="#FFFFFF"><table border="1" align="center" cellpadding="3" cellspacing="0" bordercolor="#ededed">
                <tr>
                  <td width="696" colspan="5" align="center" bgcolor="#FFFFFF"><?php 
		echo $cal->getMonthView($month, $year);  
		?></td>
                </tr>
            </table></td>
            <td  style="background:url(images/ui/answer_sides_r1_c2.jpg) right repeat-y #FFFFFF">&nbsp;</td>
          </tr>
          <tr>
            <td width="3%" align="left" valign="top" style=""><img src="images/spacer.gif" width="1" height="2" /></td>
            <td width="94%" align="left" valign="top" style="background:url(images/ui/answer_bottom_r1.jpg) top center repeat-x">&nbsp;</td>
            <td width="3%" align="left" valign="top" style="background: url(images/ui/answer_bottom_r1_c2.jpg) top right no-repeat"><img src="images/spacer.gif" width="10" height="2" /></td>
          </tr>
      </table></td>
    </tr>
    <tr>
      <td align="center" valign="top" bordercolor="#000000"><table width="75" border="0" cellspacing="2" cellpadding="2">
        <tr style="display:">
          <td align="left" bgcolor="#FFFF99">&nbsp;</td>
          <td align="left" nowrap="nowrap">Timeoff w/o Pay Request</td>
          <td align="left" nowrap="nowrap">&nbsp;</td>
          <td align="left" bgcolor="#33FF00">&nbsp;</td>
          <td align="left" nowrap="nowrap">Timeoff w/o Pay Approved</td>
          <td align="left" nowrap="nowrap">&nbsp;</td>
          <td align="left" bgcolor="#CCFF66">&nbsp;</td>
          <td align="left" nowrap="nowrap">Holiday</td>
        </tr>
        <tr style="display:">
          <td align="left" bgcolor="#FFCC00">&nbsp;</td>
          <td align="left" nowrap="nowrap">Dayoff Request</td>
          <td align="left" nowrap="nowrap">&nbsp;</td>
          <td align="left" bgcolor="#006600">&nbsp;</td>
          <td align="left" nowrap="nowrap">Dayoff Approved</td>
          <td align="left" nowrap="nowrap">&nbsp;</td>
          <td align="left" class="td_label">&nbsp;</td>
          <td align="left" nowrap="nowrap">Today</td>
        </tr>
      </table></td>
    </tr>
    <tr>
      <td align="center" valign="top" bordercolor="#000000"><table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF" bordercolor="#000000">
          
          <tr>
            <td style="background:#FFFFFF">&nbsp;</td>
            <td align="left" valign="top" bgcolor="#FFFFFF"><table border="1" align="center" cellpadding="3" cellspacing="0" bordercolor="#ededed">
                <tr>
                  <td width="319" colspan="5" align="center" bgcolor="#FFFFFF"><?php 
		echo $cal->getMonthView($nextmonth, $nextyear);  
		?></td>
                </tr>
            </table></td>
            <td  style="background:url(images/ui/answer_sides_r1_c2.jpg) right repeat-y #FFFFFF">&nbsp;</td>
          </tr>
          <tr>
            <td width="3%" align="left" valign="top" style=""><img src="images/spacer.gif" width="1" height="2" /></td>
            <td width="94%" align="left" valign="top" style="background:url(images/ui/answer_bottom_r1.jpg) top center repeat-x">&nbsp;</td>
            <td width="3%" align="left" valign="top" style="background: url(images/ui/answer_bottom_r1_c2.jpg) top right no-repeat"><img src="images/spacer.gif" width="10" height="2" /></td>
        </tr>
      </table></td>
    </tr>
  </table>
<?php
}

echo "<br>" . $pay_rate_table;
echo "<br>" . $commissionrate_table;
echo "<BR>" . $overtime_table;
?>
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

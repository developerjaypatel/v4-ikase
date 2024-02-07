<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');	

$full_ip = $_SERVER['REMOTE_ADDR'];

//Set no caching
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
header("Cache-Control: no-store, no-cache, must-revalidate"); 
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include("../api/manage_session.php");
session_write_close();

//die(print_r($_SESSION));

include("../api/connection.php");

include("../classes/cls_comm.php");
include("../classes/cls_notes.php");
include("../classes/cls_user.php");

$blnLoggedIn = false;

if (!$blnLoggedIn) {
	if (isset($_SESSION['password'])) {
		if ($_SESSION['password']!="") {
			$blnLoggedIn = true;
		}
	}
}

if (!$blnLoggedIn) {
	//die(print_r($_SESSION));
	if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
		//die(print_r($_SESSION));
		die("no go 1");
	}
	//owners (and administrators?) are redirected
	if ($_SESSION['user_customer_id']==-1 && $_SESSION['user_role']=="owner") {
		die("no go 2");
	}
	//die("login failure");
}
//current status logged in
$login = "y";
$blnDidNotPunchOut = false;
$blnLateCheckin = false;
$currentstatus = "";

include("../session_check.php");
//any late messages?
if ($blnLateCheckin && $latenotesField!="") {
	//add name
	$latenotesField = $my_user->user_name . $latenotesField;
	
	$timestamp = date("Y-m-d G:i:s");
	$late_notes = new notes($link);
	
	$arrEmails = array();
	foreach($users as $user) {
		$to_user_id = $user->user_id;
		$to_user_uuid = $user->user_uuid;
		$to_user_logon = $user->user_logon;		
		
		//echo "logon:" .  $to_user_logon . "<BR>";
		$to_user = new systemuser($link);
		$to_user->uuid = $to_user_uuid;
		$to_user->fetch();
		//echo $to_user->uuid . " - " . $to_user->user_logon . "<BR>";
	
		//$notification_user = $to_user;
		//include("notification_gather.php");
		
		//SEND NOTE
		$late_notes->insert_user_note($latenotesField, $timestamp, $my_user->id, $to_user_id, $my_user->user_logon, $to_user_logon, "standard", "Late Punch-in");
	}
	//die(print_r($arrEmails));
	/*
	if (count($arrEmails)>0) {
		$notesField = $latenotesField;
		include("user_notes_send_sms.php");
	}
	*/
} 
/*
$query_late = "SELECT user.user_id, shift
FROM user 
WHERE user.user_logon = '" . $USERNAME . "'";
//echo $query_late . "<BR>";
$result = mysql_query($query_late, $link) or die(mysql_error());
*/
$shift = $my_user->shift;
$user_id = $my_user->id;

//get the overtime if any
$queryot = "SELECT ot.hours_day, ot.hours_week, ot.start_date, ot.end_date
FROM user 
LEFT OUTER JOIN `overtime_track` ot
ON user.user_id = ot.user_id
WHERE user.user_id = :user_id
AND ot.start_date <= :start_date
AND ot.end_date >= :end_date
ORDER BY ot.overtime_track_id DESC";
//echo $queryot . "<BR>";
//$resultot = mysql_query($queryot, $link) or die(mysql_error());
//$numbertot = mysql_numrows($resultot);
$start_date = date("Y-m-d");
$end_date = date("Y-m-d");

try {
	$sql = $queryot;
	$db = getConnection();
	$stmt = $db->prepare($sql);  
	$stmt->bindParam("user_id", $user_id);
	$stmt->bindParam("start_date", $start_date);
	$stmt->bindParam("end_date", $end_date);
	$stmt->execute();
	$overtimes = $stmt->fetchAll(PDO::FETCH_OBJ);
	$stmt->closeCursor(); $stmt = null; $db = null;
} catch(PDOException $e) {
	echo '{"error":{"text":'. $e->getMessage() .'}}'; 
}
$seconds_day = 0;
$hours_day = 0;
//if ($numbertot>0) {
if (count($overtimes) > 0) {
	$hours_day = $overtimes[0]->hours_day;
	$seconds_day = 3600 * $hours_day;
}

//days off
$query_days = "SELECT 
days_available, seconds_available, days_assigned, current_comp_seconds 
FROM timeoff
WHERE user_id = :user_id";
//$result_days = mysql_query($query_days, $link) or die("unable to get timeoff info<br />" . mysql_error() . "<br />" . $query_days);
$days_available = 0;
$seconds_available = 0;
//$number_days = mysql_numrows($result_days);
try {
	$sql = $query_days;
	$db = getConnection();
	$stmt = $db->prepare($sql);  
	$stmt->bindParam("user_id", $user_id);
	$stmt->execute();
	$timeoff = $stmt->fetchObject();
	$stmt->closeCursor(); $stmt = null; $db = null;
} catch(PDOException $e) {
	echo '{"error":{"text":'. $e->getMessage() .'}}'; 
}
if (is_object($timeoff)) {
	$days_available = $timeoff->days_available;
	$days_assigned = $timeoff->days_assigned;
	$seconds_available = $timeoff->seconds_available;
	$full_days_available = ($seconds_available / 24 / 3600);
	//$days_available = number_format($full_days_available, 0);
	$arrNumber = explode(".", $full_days_available);
	$remainder = 0;
	if (count($arrNumber)>1) {
		$remainder = $arrNumber[1];
		//echo "rem:" . $remainder . "<BR>";
		$hours_available = (0 . "." . $remainder) * 24;
		$actual_hours_available = 8 - (24 - $hours_available);
		$days_available = $arrNumber[0] . " days " . number_format($actual_hours_available, 1) . " hours or<br>";
		 $days_available .= ($arrNumber[0] * 8) + number_format($actual_hours_available, 1) . " vacation hours";
	} else {
		$days_available .= " days ";
	}
	
	$current_comp_seconds = $timeoff->current_comp_seconds;
	$current_comp_seconds = hours_minutes($current_comp_seconds);
}

$ipos = strpos($my_user->user_groups, "admins");
if ($ipos===false) {
	$blnAdmin = false;
} else {
	$blnAdmin = true;
}
$ipos = strpos($my_user->user_groups, "anytime_access");
if ($ipos===false) {
	$blnAnytime = false;
} else {
	$blnAnytime = true;
}

//now let's see when I clocked in
//check current status
$the_start_date = date("Y-m-d") . " 00:00:00";
$the_end_date = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")+1, date("Y"))) . " 00:00:00";
$query = "select 
`username`, `status`, `ip_address`,`dateandtime` 
from userlogin 
where username = '$USERNAME'
AND`userlogin`.`dateandtime` > :the_start_date
AND `userlogin`.`dateandtime` < :the_end_date
order by userlogin_id ASC
limit 0,1";
//$resultcheck = MYSQL_QUERY($query, $link) or die("unable to check user last entry<br>$trackip<br>" . mysql_error());
//$numbercheck = mysql_numrows($resultcheck);
try {
	$sql = $query;
	$db = getConnection();
	$stmt = $db->prepare($sql);  
	$stmt->bindParam("the_start_date", $the_start_date);
	$stmt->bindParam("the_end_date", $the_end_date);
	$stmt->execute();
	$userlogin = $stmt->fetchObject();
	$stmt->closeCursor(); $stmt = null; $db = null;
} catch(PDOException $e) {
	echo '{"error":{"text":'. $e->getMessage() .'}}'; 
}
//if we have nothing, not even in
//if ($numbercheck>0) {
if (is_object($userlogin)) {
	//user has been here before, what is the last status
	//$this_status = mysql_result($resultcheck, 0, "status");
	$punch_dateandtime = $userlogin->dateandtime;
	$punch_dateandtime = date("h:i A", strtotime($punch_dateandtime));
}

//messages
$my_messages = new notes();
$resultmessages = $my_messages->search_user_notes($USERNAME, "to", "N");
$numbermessages = count($resultmessages);
//echo "mess:" . $numbermessages . "<BR>";
$my_timeoff = new notes();

//check if notified
//print_r($arrNotified);
if (in_array($USERNAME, $arrNotified)) {
	//timeoff
	$resulttimeoff = $my_timeoff->search_user_notes("myadmin", "to", "N", "", "requested timeoff");
	$numbertimeoff = count($resulttimeoff);
}
//die(print_r($my_user));

$user = $my_user->user_logon;
$shift = $my_user->shift;

if (!isset($the_date)) {
	$the_date = date("Y-m-d");
} else {
	$the_date = date("Y-m-d", strtotime($the_date));
}
//die("date1:". $the_date);
$query = " SELECT `userlogin`.`userlogin_id` , `userlogin`.`username` , 
`userlogin`.`status` , `userlogin`.`ip_address` , `userlogin`.`dateandtime`,
`user`.`user_name` theuser
FROM `userlogin`
INNER JOIN `user`
on `userlogin`.`username` = `user`.`user_logon`
WHERE cast( `userlogin`.`dateandtime` AS date ) = '" . $the_date . "'
AND `userlogin`.`status`<>'OUTT'";
if ($user!="") {
	$query .= " AND `userlogin`.username = '" . $user . "'";
}
$query .= " ORDER BY cast( `userlogin`.`dateandtime` AS date ) , `userlogin`.username, `userlogin`.`dateandtime`, `userlogin`.`userlogin_id`";

//echo $query;

//$result = mysql_query($query, $link) or die("unable to get timecards");
//$numberusers = mysql_numrows($result);
try {
	$sql = $query;
	$db = getConnection();
	$stmt = $db->prepare($sql);  
	$stmt->execute();
	$userlogins = $stmt->fetchAll(PDO::FETCH_OBJ);
	$stmt->closeCursor(); $stmt = null; $db = null;
} catch(PDOException $e) {
	echo '{"error":{"text":'. $e->getMessage() .'}}'; 
}
$currentuser = "";
if (count($userlogins)==1) {
	//for calculations
	$currentdate = date("Y-m-d H:i:s");
	//echo "start with now";
	$currentstatus = "NOW";
}
//die(print_r($userlogins));

//for ($iDown=0;$iDown<$numberusers;$iDown++) {
$hours = array("hours"=>0, "minutes"=>0);
$user_table = "";
$work_seconds = array();
$break_seconds = array();
$lunch_seconds = array();
foreach($userlogins as $iDown=>$user_login) {
	 if (($iDown%2)==0) { $bgcolor="#FFFFFF"; } else { $bgcolor="#ededed"; }
	$the_userlogin_id =  $user_login->userlogin_id;
	$the_username = $user_login->username;
	$the_user = $user_login->theuser;
	if ($currentuser != $the_username) {
		$currentuser = $the_username;
		$work_seconds[$currentuser] = 0;
		$break_seconds[$currentuser] = 0;
		$lunch_seconds[$currentuser] = 0;
	} else {
		$the_username = "";
	}
	$this_status = $user_login->status;
	$my_dateandtime = $user_login->dateandtime;
	$this_dateandtime = date("m/d/Y g:i A", strtotime($my_dateandtime));
	$the_time = date("h-i-s", strtotime($my_dateandtime));
	$late = "";
	if ($currentstatus != $this_status) {
//		echo $currentstatus . "-" . $this_status . "<Br>";
		//calculate the time difference
		if ($currentstatus!="") {
			if ($currentstatus!="NOW") {
				$hours = get_time_difference( $currentdate, $my_dateandtime );
				$time_lapse = get_time_difference( $currentdate, $my_dateandtime );
			} else {
				$hours = get_time_difference( $my_dateandtime, $currentdate);
				$time_lapse = get_time_difference( $my_dateandtime, $currentdate );
			}
			//$hours = get_time_difference( $currentdate, $my_dateandtime );
			//$time_lapse = get_time_difference( $currentdate, $my_dateandtime );
			$seconds_lapse = ($time_lapse['hours'] * 3600) + ($time_lapse['minutes'] * 60) + $time_lapse['seconds'];
			if ($currentstatus=="IN" || $currentstatus=="NOW") {
				$work_seconds[$currentuser] += $seconds_lapse;
				//echo "work: " . $work_seconds[$currentuser] . "<Br>";
			}
			if ($currentstatus=="BREAK" && $this_status=="IN") {
				if ($hours["minutes"] > 10) {
					//diff
					$amount_late = $hours["minutes"]-10;
					if ($amount_late==1) {
						$amount_late = $amount_late . " minute late)";
					} else {
						$amount_late = $amount_late . " minutes late)";
					}
					//show it late
					$late = "<br>(" . $amount_late;
				}
				$break_seconds[$currentuser] += $seconds_lapse;
				$work_seconds[$currentuser] += $seconds_lapse;
				$time_spent = hours_minutes($break_seconds[$currentuser]);
			}
			if ($currentstatus=="LUNCH" && $this_status=="IN") {
	//			echo "time: " . $hours["hours"] . ":" . $hours["minutes"] . "<BR>";
				if ($hours["hours"] >= 1 && $hours["minutes"]>0) {
					//diff
					$amount_late = (($hours["hours"]*60)-60) + $hours["minutes"];
					if ($amount_late==1) {
						$amount_late = $amount_late . " minute late)";
					} else {
						$amount_late = $amount_late . " minutes late)";
					}
					//show it late
					$late = "<br>(" . $amount_late;
				}
				$lunch_seconds[$currentuser] += $seconds_lapse;
				$time_spent = hours_minutes($lunch_seconds[$currentuser]);
			}
			if ($this_status=="BREAK" && $this_status=="LUNCH") {
				$time_spent = hours_minutes($work_seconds[$currentuser]);
			}
		}
		$currentstatus = $this_status;
		$currentdate = $my_dateandtime;
	}
	//echo "stat:" . $currentstatus . "<BR>";
	
	$hour_minutes = $hours["hours"] . " hrs " . $hours["minutes"] . " mins";
	if (str_replace(" ", "", $hour_minutes)=="hrmn") {
		$hour_minutes = "&nbsp;";
	}
	//" . $the_username . "
	$user_table .= "<tr bgcolor='" . $bgcolor . "'><td nowrap valign='top'>&nbsp;</td><td valign='top' nowrap>" . $this_status . "</td><td nowrap valign='top'>" . date("h:i A", strtotime($my_dateandtime)) . $late . "</td><td nowrap>" . str_replace("0hr", "", $hour_minutes) . "</td>
	<!--<td nowrap><a href='profile/hours_notes.php?loginid=" . $the_userlogin_id . "'>add note</a></td>-->
	</tr>";
	//look up the notes for this login if any
	$bareQuery = "select notes_id, notes, 
	time_stamp,	user_name
	from hour_notes where login_id = '$the_userlogin_id'";
	$querynotes = $bareQuery." order by notes_id desc";
	//$resultnotes = MYSQL_QUERY($querynotes) or die(mysql_error());
	//$numbernotes = mysql_Numrows($resultnotes);
	
	try {
		$sql = $querynotes;
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		$notes = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
	$notes_table = "";
	//for ($intN=0;$intN<$numbernotes;$intN++) {
	foreach($notes as $note) {	
		$the_note = $note->notes;
		$the_note_date = $note->time_stamp;
		$the_note_date = date("m/d/Y g:i A", strtotime($the_note_date));
		$notes_table .= "<tr><td valign='top' align='left'>" . $the_note . "</td><td valign='top' align='left' nowrap>" . $the_note_date . "</td></tr>";
	}
	if ($notes_table!="") {
		$user_table .= "<tr bgcolor='" . $bgcolor . "'><td colspan='5'><table border='0' cellpadding='2'>" . $notes_table . "</table></td></tr>";
	}
}
$numberusers = count($userlogins);
if ($currentstatus =="IN"  && $numberusers>1) {
	$currentdate = date("Y-m-d H:i:s");
	$currentstatus = "NOW";
	$hours = get_time_difference( $my_dateandtime, $currentdate);
	$time_lapse = get_time_difference( $my_dateandtime, $currentdate );		
	$seconds_lapse = ($time_lapse['hours'] * 3600) + ($time_lapse['minutes'] * 60) + $time_lapse['seconds'];
	//echo $work_seconds[$currentuser] . " -> add:" . $seconds_lapse . "<BR>";
	$work_seconds[$currentuser] += $seconds_lapse;
	//echo $work_seconds[$currentuser];
	if ($bgcolor=="#ededed") { 
		$bgcolor="#FFFFFF"; 
	} else { 
		$bgcolor="#ededed"; 
	}
	$hour_minutes = $hours["hours"] . "hr " . $hours["minutes"] . "mn";
	//$notes_table .= "<tr><td nowrap valign='top'>&nbsp;</td><td valign='top' align='left'>NOW</td><td valign='top' align='left' nowrap>" .  date("m-d-Y H:i:s") . "</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
	//$user_table .= "<tr bgcolor='" . $bgcolor . "'><td colspan='5'><table border='0' cellpadding='2'>" . $notes_table . "</table></td></tr>";
	if ($numberusers>1) {
		$user_table .= "<tr bgcolor='" . $bgcolor . "'><td nowrap valign='top'>&nbsp;</td><td valign='top' align='left'>NOW</td><td valign='top' align='left' nowrap>" .  date("m-d-Y H:i A") . "</td><td>" . str_replace("0hr", "", $hour_minutes) . "</td><td>&nbsp;</td></tr>";
	}
}
?>
<div style="padding:5px; background:linear-gradient(rgba(0,0,0,0), rgba(0,0,0,0.0)); border:0px solid #000000; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; box-shadow: inset 0 0px rgba(255,255,255,.0), inset 0 0px 0px rgba(0,0,0,0.0),0 0px 0px rgba(0,0,0,0.0); height:100%;" class="col-md-12">
	<div class="col-md-12" style="margin-top:20px">
    	<div class="col-md-3 data_card">
        	<?php 
			echo "<strong>IP:</strong>" . $full_ip . "<br>";
			include("../employees_clock_search.php");
			?>
        </div>
        <div class="col-md-4 data_card">
        	<?php include("../employees_clock.php"); ?>
        </div>
    </div>
</div>

<?php
$USERNAME = $_SESSION["user_logon"];
include("management_check.php");
//echo "current:" . $current_session_id . "\r\n";
//die(print_r($_SESSION));


$full_ip = $_SERVER['REMOTE_ADDR'];
$logout = "n";
if (isset($_GET["logout"])) {
	$logout = passed_var("logout", "get");
}
$break = "n";
if (isset($_GET["break"])) {
	$break = passed_var("break", "get");
}
$lunch = "n";
if (isset($_GET["lunch"])) {
	$lunch = passed_var("lunch", "get");
}
$status = "";
//echo "ip:" . $full_ip . "<br>";
//$status="IN";	//default
//check current status
$sql = "SELECT  ulog.`username` , ulog.`status` , ulog.`ip_address` , ulog.`dateandtime`, user.*
FROM userlogin ulog
INNER JOIN `user` 
ON ulog.user_uuid = user.user_uuid
WHERE sess_id = :sess_id
ORDER BY userlogin_id DESC
LIMIT 0,1";
//echo $current_session_id . "<br />";
//die($sql);  
//$resultcheck = MYSQL_QUERY($query, $link) or die("unable to check user last entry<br>$trackip<br>" . mysql_error());
//$numbercheck = mysql_numrows($resultcheck);
try {
	$db = getConnection();
	$stmt = $db->prepare($sql);  
	$stmt->bindParam("sess_id", $current_session_id);
	$stmt->execute();
	$user = $stmt->fetchObject(); //$stmt->fetchAll(PDO::FETCH_OBJ);
	$stmt->closeCursor(); $stmt = null; $db = null;
} catch(PDOException $e) {
	echo '{"error":{"text":'. $e->getMessage() .'}}'; 
}
//die(print_r($user));
$this_status = "IN";
$this_shift = $my_user->shift;
$user_id = $my_user->id;
$user_uuid = $my_user->uuid;
$user_logon = $my_user->user_logon;
$user_pd = $my_user->user_pd; 
$dateandtime = date("Y-m-d H:i:s"); 
$clock_in_time = $my_user->clock_in_time;
$clock_out_time = $my_user->clock_out_time;
$work_location = $my_user->work_location;
if (is_object($user)) {
	//what is the last status
	$this_status = $user->status;
	$sid = $current_session_id;
	if ($this_status=="LUNCH") {
		//must come from punch
		$referer = $_SERVER['HTTP_REFERER'];
		$strpos = strpos($referer, "user_timecard_punch.php?lunch=y");
		if ($strpos===false) {
			header("location:user_timecard_punch.php?lunch=y");
			die();
		}
	}
	if ($this_status=="BREAK") {
		//must come from punch
		$referer = $_SERVER['HTTP_REFERER'];
		$strpos = strpos($referer, "user_timecard_punch.php?break=y");
		if ($strpos===false) {
			header("location:user_timecard_punch.php?break=y");
			die();
		}
	}

	$this_dateandtime = $user->dateandtime;
	$this_shift = $user->shift;
	$work_location = $user->work_location;
	$this_user_groups = $user->user_groups;
	$group_pos = strpos($this_user_groups, "anytime_access");
	$blnAnytime = ($group_pos!==false);
	$admin_pos = strpos($this_user_groups, "admins");
	$blnAdmin = ($admin_pos!==false);
	
	if ($blnAnytime || $blnAdmin) {
		$blnManagement = true;
	}
	
	$clock_in_time = $user->clock_in_time;
	$noearly_entry = $user->noearly_entry;
	$early_entry_window = $user->early_entry_window;
	$user_id = $user->user_id;
	$user_uuid = $user->user_uuid;
	$user_logon = $user->user_logon;
	$user_pd = $user->user_pd; 
	$dateandtime = $user->dateandtime; 
	$clock_in_time = $user->clock_in_time;
	$clock_out_time = $user->clock_out_time;
	$work_location = $user->work_location;
	//echo date("Y-m-d") . " " . $clock_in_time . "<BR>";
	
	if ($early_entry_window=="") {
		$early_entry_window = 5;
	}
	
	if (!$blnAnytime && $blnManagement!=true) {
		if ($noearly_entry=="Y") {
			$early_entry_window = $early_entry_window * (-1);
			$early_arrival = DateAdd("n", $early_entry_window, strtotime(date("Y-m-d") . " " . $clock_in_time));
			//echo strtotime(date("Y-m-d G:i:s")) ."<". $early_arrival . "<BR>";
			//echo date("Y-m-d G:i:s") ."<". date("Y-m-d G:i:s", $early_arrival) . "<BR>";
			if (strtotime(date("Y-m-d G:i:s")) < $early_arrival ) {
				echo "<table cellpadding='2' border='1'><tr><td align='left'>Early Arrival</td><td align='left'>" . date("g:i:s A", $early_arrival) . "</td></tr><tr><td align='left'>Clock In</td><td align='left'>" . date("g:i:s A", strtotime(date("Y-m-d") . " " . $clock_in_time)) . "</td></tr><tr><td align='left'>Now</td><td align='left'>" . date("g:i:s A") . "</td></tr></table>";
				die("You cannot login to the system until " . date("g:i:s A", $early_arrival));
			}
		}
		//late arrival warning
		if ($this_status=="OUT") {
			
		}
	}
}

//checkin?
$temp_status = "";

if ($sid!="") {
	//if the user is IN, check on session
	if ($this_status=="IN") {
		$temp_status = "IN";
		if ($this_shift=="2") {
			if (date("D")=="Sun") {
				$clock_out_time = "18:00:00";
			}
		}
		
//		echo $work_location;
//		echo $clock_out_time . "<BR>";
		//now based on this, you might be here after hours?
		if ($blnManagement!=true) {
			//which IP?
			/*
			if ($work_location=="main") {
				$ipos = strpos($full_ip, "76.79.191");
				$kpos = strpos($full_ip, "76.168.192.239");
				if ($ipos===false && $jpos===false) {
				//if ($full_ip != "71.95.170.10" && $full_ip != "74.100.127.37"  && $full_ip != "96.251.26.45"  && $full_ip != "76.79.191") {
					echo "ip: " . $full_ip . "<BR>";
					die("No entry, this ip address is not authorized.  Please contact Nick - " . $work_location);
				}
			}
			*/
			//two hours after clock out
			if ($this_shift=="2") {
				$tomorrow = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")+1, date("Y")));
				$clock_out_time = strtotime($tomorrow . " " . $clock_out_time);
				$delay = DateAdd("h", 2, $clock_out_time);
			} else {
				$clock_out_time = strtotime(date("Y-m-d") . " " . $clock_out_time);
				$delay = DateAdd("h", 2, $clock_out_time);
			}
			//echo date("Y-m-d H:i:s", $clock_out_time) . "\r\n";
			//echo "delay: " . date("Y-m-d H:i:s", $delay) . "\r\n";
			//echo "now: " . date("Y-m-d H:i:s") . "\r\n";
			if ($delay < strtotime(date("Y-m-d H:i:s")) ) {
				die("no entry, too late");
			} else {
				//echo "entry authorized";
			}
		}
		$day_in_time = strtotime(date("Y/m/d") . " " . $clock_in_time);
		$day_out_time = strtotime(date("Y/m/d") . " " . $clock_out_time);
		$curr_time = strtotime(date('Y/m/d H:i:s'));
		//standard is 90 minutes
		//if after hours, 20 minutes
		$interval = 130000;
		if ($curr_time > $day_in_time && $curr_time < $day_out_time) {
//			echo $curr_time." > ".$day_in_time." <> " . $day_out_time . "<Br>";
			$interval = 120000;
		}
		if ($blnManagement == true) {
			$interval = 130000;
		}
//		echo $interval ;
		if ($dateandtime!="" && $interval==20) {
			$curr_time = date('Y-m-d H:i:s');
			$diff=@get_time_difference($dateandtime, $curr_time);
			$tot_minutes = ($diff['hours'] * 60) + $diff['minutes'];
			//minutes must no be greater than interval
			//echo $tot_minutes .">". $interval;
			if ($tot_minutes > $interval) {
				die ("<script language=javascript>alert('This session has been inactive for more than ". $interval . " minutes.'); document.location.href = 'index.php?ina';</script>");
			} else {
				//reset the time on the server
				$queryupdate = "
				UPDATE `user` 
				SET `dateandtime` ='$curr_time' 
				WHERE `sess_id` = :sess_id";
				//$queryresult = mysql_query($queryupdate, $link) or die("unable to update session<br>$queryupdate");
				try {
					$db = getConnection();
					$stmt = $db->prepare($sql);  
					$stmt->bindParam("sess_id", $current_session_id);
					$stmt->execute();
					$stmt = null; $db = null;
				} catch(PDOException $e) {
					echo '{"error":{"text":'. $e->getMessage() .'}}'; 
				}
			}
		}
	}
} else {
	//session invalid or no longer valid
	die ("<script language=javascript>alert('This session is no longer valid.');document.location.href='index.php?nova';</script>");
}
//session
//let's look at the status
//die("mag:" . $blnManagement);


//if ($numbercheck>0) {
if (is_object($user)) {
	$latenotesField = "";
	if (!$blnAdmin && !$blnAnytime) {	//if ($blnManagement!=true) {
		if ($this_status=="OUT") {	//|| $this_status=="OUTT"
			//echo $query . "<BR>";
			//$blnManagement="false";
			$late_arrival = DateAdd("n", 1, strtotime(date("Y-m-d") . " " . $clock_in_time));
			//echo "clock in:" . $clock_in_time . "<BR>";
			//echo "late arri: ". date("Y-m-d G:i:s", $late_arrival). "<BR>";
			if (strtotime(date("Y-m-d G:i:s")) > $late_arrival ) {
				//send a note
				//echo "send a note<BR>";
				
				$blnLateCheckin = true;
				
				$latenotesField = "<br>Date: " . date("m/d/Y") . "<br>";	//
				$latenotesField .= "Clock-in Time: " . date("g:i:s A", strtotime(date("Y-m-d") . " " . $clock_in_time));
				//echo "late1:" . $latenotesField . "<BR>";
				$latenotesField .= "<br>Late Clock-In: " . date("g:i:s A");
				//echo "late2:" . $latenotesField . "<BR>";
			}
		}
	}
	
	//user has been here before, what is the last status
	if ($this_status=="IN") {
		$punchout_diff = get_time_difference(date("m/d/Y h:i:sA", strtotime($this_dateandtime)), date("m/d/Y h:i:sA"));
		//echo print_r($punchout_diff) ."<BR>";
		$hoursdiff = $punchout_diff['hours'] + ($punchout_diff['days']*24);
		//echo "punched in " . $punchout_diff['minutes'] . " minutes ago<br>";
		//die("now:" . $this_dateandtime);
		//if last entry is in, take him out, and relog in
		$blnDidNotPunchOut = false;
		if ($this_shift=="1" && date("m/d/Y") != date("m/d/Y", strtotime($this_dateandtime))){
			$blnDidNotPunchOut = true;
		}
		if ($blnDidNotPunchOut) {
			$sql ="INSERT INTO `userlogin` (`username`,`status`,`ip_address`,`dateandtime`) 
			VALUES('" . $USERNAME . "','OUTT','$full_ip','" . date("Y-m-d H:i") . "')";
			//die("NO PUNCH:" . $trackip . "<BR>");
			//$resultall = MYSQL_QUERY($trackip, $link) or die("unable to track users<br>$trackip<br>" . mysql_error());
			
			try {
				$db = getConnection();
				$stmt = $db->prepare($sql);  
				$stmt->execute();
				$stmt = null; $db = null;
			} catch(PDOException $e) {
				echo '{"error":{"text":'. $e->getMessage() .'}}'; 
			}
			
			//notification
			if (($work_location=="main")) {
				//send an interoffice to management
				
				$blnDidNotPunchOut = true;
				if (!$blnAdmin && !$blnAnytime) {
					/*
					$notesField = $USERNAME . " did not clock out on " . date("m/d/Y", strtotime($this_dateandtime));
					$timestamp = date("Y-m-d G:i:s");
					$my_notes = new notes($link);
					$my_user = new systemuser($link);
					$my_user->user_logon = $USERNAME;
					$my_user->fetchuser();
					
					for ($intU=0;$intU<$numberusers;$intU++) {
						$to_user_id = mysql_result($resultusers, $intU, "user_id");
						$to_user_logon = mysql_result($resultusers, $intU, "user_logon");
						
						//SEND NOTE
						$my_notes->insert_user_note($notesField, $timestamp, $my_user->id, $to_user_id, $my_user->user_logon, $to_user_logon);
					}
					*/
				}
			}
			header("location:user_punchout.php?noou");
		}
		//leave it alone
		//echo "reset";
		//$status="";
		//unless it's a logout specifically
		if ($logout=="y") {
			$msg = "I logged out of the system at ";
			$status="OUT";
		}
		if ($break=="y") {
			$msg =  "I went on 10 minute break at ";
			$status="BREAK";
		}
		if ($lunch=="y") {
			$msg =  "I went out for lunch at ";
			$status="LUNCH";
		}
		if ($status != "") {
			//echo "<span style='font-size:14pt;font-weight:bold'>" . $msg . date("g:i A") . "</span>";
		}
	}
	if ($this_status=="OUT" || $this_status=="OUTT" || 
		$this_status=="BREAK" || $this_status=="LUNCH") {
		//flip it
		$status="IN";
	}
} else {
	//never been in before
	$status="IN";
}
if ($status=="IN") {
	//reset session here
	if ($sid=="") {
		$sid = uniqid('06') ;
	}
	$curr_time = date('Y-m-d H:i:s');
	//reset the time on the server
	$sql = "UPDATE `user` 
	SET `dateandtime` ='$curr_time'
	WHERE `session_id` = :sess_id";
	//echo $queryupdate . "<br />";
	//$queryresult = mysql_query($queryupdate, $link) or die("unable to update session<br>$queryupdate");
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("sess_id", $current_session_id);
		$stmt->execute();
		$stmt = null; $db = null;
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}

if ($status!="") {
	$sql ="INSERT INTO userlogin (`username`,`status`,`ip_address`,`dateandtime`) 
	VALUES ('$USERNAME','$status','$full_ip','" . date("Y-m-d H:i") . "')";
	//die("trackip> " . $trackip);
	//$resultall = MYSQL_QUERY($trackip, $link) or die("unable to track users<br>$trackip<br>" . mysql_error());
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		$loginid = $db->lastInsertId();
		$stmt = null; $db = null;
		
		
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
	
	//track each entry
	//track it			
	$query ="INSERT INTO userlogin_track (`username_track`, `action`, `userlogin_id`, `username`,`status`,`ip_address`,`dateandtime`) 
	VALUES (";
	$query .= "'" . $USERNAME . "'";
	$query .= ",'insert'";
	$query .= ",'" . $loginid . "','$USERNAME','$status','$full_ip','" . date("Y-m-d H:i") . "')";
	try {
		$sql = $query;
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		$loginid = $db->lastInsertId();
		$stmt = null; $db = null;
		
		
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
if ($temp_status!="") {
	$status = $temp_status;
}
echo "<br>Current Status:" . $status;
?>

<?php
include("connection.php");

// set the default timezone to use. Available since PHP 5.1
date_default_timezone_set('America/Los_Angeles');


// Prints something like: Monday


// Prints something like: Monday 8th of August 2005 03:12:46 PM
//echo date('Z -> l jS \of F Y h:i:s A');
//die();

$token = passed_var('token', 'get');

$arrToken = explode(".", $token);
$token = $arrToken[0];
$user_uuid = $arrToken[1];

exportCalendar($token, $user_uuid);

function exportCalendar($token, $user_uuid) {	
	if ($token == "") {
		$error = array("error"=> array("text"=>"no entry"));
        echo json_encode($error);
		die();
	}
	//time_zone concerns
	$rightnow = mktime(date("H") - 2, date("i"), date("s"), date("m"), date("d"),   date("Y"));
	$sql_user = "SELECT data_source 
	FROM ikase.cse_user cu
	INNER JOIN ikase.cse_customer cc
	ON cu.customer_id = cc.customer_id
	WHERE 1 AND cc.pwd = '" . $token . "'";
	$sql_user .=	" AND `cu`.`deleted` ='N'
	AND `cu`.`user_uuid` = '" . $user_uuid . "'";
	
	
    
	//die($sql_user);
	//cc0b3b41871473a9d54a6d87260f47bd
	try {
		$db = getConnection();
		
		$stmt = $db->prepare($sql_user);
		$stmt->execute();
		$customer = $stmt->fetchObject();
		//die(print_r($customer));
		$db_name = "`ikase`";
		if ($customer->data_source!="") {
			$db_name = "`ikase_" . $customer->data_source . "`";
		}
		
		$sql = "SELECT DISTINCT `event_id` id, eve.`event_uuid`, `event_date`, `event_name`, `event_dateandtime` `start`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `event_title` `title`, `event_email`, `event_hour`, `event_type`, eve.`customer_id`, `color`, IF(`color`='yellow','black', 'white') `textColor`, 'eventClass' `className`, '' `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `assignee`, `event_from`, `event_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, ccase.case_number, CONCAT(app.first_name,' ',app.last_name,' vs ', IFNULL(employer.`company_name`, '')) `case_name`,
		csc.cus_city customer_city, csc.cus_state customer_state
				FROM " . $db_name . ".`cse_event` eve
				INNER JOIN ikase.`cse_customer` csc
				ON eve.customer_id = csc.customer_id			
				INNER JOIN ikase.`cse_user` cu
				ON csc.customer_id = cu.customer_id
				LEFT OUTER JOIN " . $db_name . ".`cse_case_event` ceve
				ON eve.event_uuid = ceve.event_uuid
				LEFT OUTER JOIN " . $db_name . ".`cse_case` ccase
				ON ceve.case_uuid = ccase.case_uuid
				LEFT OUTER JOIN " . $db_name . ".`cse_case_person` ccapp ON ccase.case_uuid = ccapp.case_uuid
				LEFT OUTER JOIN ";
		if (($_SESSION['user_customer_id']==1033)) { 
			$sql .= "(" . SQL_PERSONX . ")";
		} else {
			$sql .= "cse_person";
		}
		$sql .= " app ON ccapp.person_uuid = app.person_uuid
				LEFT OUTER JOIN " . $db_name . ".`cse_case_corporation` ccorp
				ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
				LEFT OUTER JOIN " . $db_name . ".`cse_corporation` employer
				ON ccorp.corporation_uuid = employer.corporation_uuid
				WHERE 1";
		$sql .=	" AND `eve`.`deleted` ='N'
		AND csc.pwd = '" . $token . "'";
		$sql .=	" AND `cu`.`deleted` ='N'
		AND `cu`.`user_uuid` = '" . $user_uuid . "'";
		
		$sql .=	" AND `eve`.`event_type` != 'phone_call' ";
		$sql .=	" AND `eve`.`event_type` != 'intake' ";
		$sql .= " ORDER BY eve.event_id ASC
		";
		//die($db_name);
		
		//die($sql);
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$allcusevents = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		$offset = 0;
		if (count($allcusevents) > 0) {
			$customer_city = $allcusevents[0]->customer_city;
			$customer_state = $allcusevents[0]->customer_state;
			
			$sql = "SELECT time_zone FROM `zip_code` WHERE city = '" . addslashes($customer_city) . "'
			AND `state_prefix` = '" . $customer_state . "'";
			$stmt = $db->prepare($sql);
			$stmt->execute();
			$localtime = $stmt->fetchObject();
			$time_zone = $localtime->time_zone;
			//die("TZ:" . $time_zone);
			if ($time_zone!="") {
				switch($time_zone) {
					case "Pacific":
						$offset = 8;
						break;
					case "Mountain":
						$offset = 7;
						break;
					case "Central":
						$offset = 6;
						break;
					case "Eastern":
						$offset = 5;
						break;
					case "Alaska":
						$offset = 9;
						break;
					case "Hawaii":
						$offset = 10;
						break;
				}
				$rightnow = mktime(date("H"), date("i"), date("s") + $offset, date("m"), date("d"),   date("Y"));
				//die(date("m/d/Y H:i:s") . " -- " . date("m/d/Y H:i:s", $rightnow));
			}
		}
		
		$db = null;
		
		// the iCal date format. Note the Z on the end indicates a UTC timestamp.
		define('DATE_ICAL', 'Ymd\THis\Z');
		 
		// max line length is 75 chars. New line is \\n
		 
		$output = "BEGIN:VCALENDAR
METHOD:PUBLISH
VERSION:2.0
PRODID:-//One Stop//iKase//EN\n";
		
        foreach($allcusevents as $ap_intex=>$appointment):
			//put together the actual event details
			$appointment->title = str_replace(" - 00/00/0000", "", $appointment->title);
			//end date
			$event_uuid = $appointment->event_uuid;
			//timezone
			if ($offset != 0) {
				//echo $appointment->event_dateandtime . "<br />";
				//$appointment->event_dateandtime = DateAdd("s", $offset, strtotime($appointment->event_dateandtime));
				$appointment->event_dateandtime = DateAdd("h", $offset, strtotime($appointment->event_dateandtime));
				$appointment->event_dateandtime = date("Y-m-d H:i:s", $appointment->event_dateandtime);
			}
			if ($appointment->end_date = "0000-00-00 00:00:00") {
				$appointment->end_date = $appointment->event_dateandtime;
			}
			
			
			$date = date("Ymd", strtotime($appointment->event_dateandtime));
			$startTime = date("Hi", strtotime($appointment->event_dateandtime));
			$endTime   = date("Hi", strtotime($appointment->event_dateandtime));
			
			$output .= "BEGIN:VEVENT
UID:" . $event_uuid. "ikase.org
DTSTAMP:" . gmdate('Ymd').'T'. gmdate('His') . "Z
DTSTART:".$date."T".$startTime."00Z
FDATE:" . $appointment->event_dateandtime . "
DTEND:".$date."T".$endTime."00Z
SUMMARY:";
$blnTitle = false;
if ($appointment->case_number!="") {
	$output .= $appointment->case_number . " - ";
	$blnTitle = true;
}
if ($appointment->event_first_name!="") {
	$output .= $appointment->event_first_name . " " . $appointment->event_last_name;
	$blnTitle = true;
}
$output .= "\\n";
$output .= " - " . $appointment->title;
/*
if ($appointment->location!="") {
	$output .= " - " . $appointment->location;
	$blnTitle = true;
}

if (!$blnTitle) {
	$output .= $appointment->title;
}
*/
$output .= "
DESCRIPTION:";
$blnDescription = false;
if ($appointment->case_number!="") {
	$output .= "iKase Link: https://www.ikase.org/v7.php#kases/" . $appointment->case_id;
	$output .= "\\n\\n";
	$output .= "Case Number:" . $appointment->case_number . "\\n";
	$blnDescription = true;
}
if ($appointment->event_type!="") {
	if ($appointment->event_type!="import") {
		$output .= "Type:" . $appointment->event_type . "\\n";
		$blnDescription = true;
	}
}
if ($appointment->location!="") {
	$output .= "Location:" . $appointment->location . "\\n";
	$blnDescription = true;
}
/*
if ($blnDescription) {	
	$output .= "\\n";
}
$output .= $appointment->title . "
*/

//add event description per Patel 3/15/2018

$description = $appointment->event_description;
$description = str_replace("</p>", "</p>\\n", $description);
$description = str_replace("<br />", "<br />\\n", $description);
$description = str_replace("<br>", "<br>\\n", $description);
$description = trim(strip_tags($description));
if ($description!="") {
	$output .= "\\n";
	$output .= $description . "\\n";
}
$output .= "
END:VEVENT\n";		
			//die($output);
			//break;
		endforeach;
		
		// close calendar
		$output .= "END:VCALENDAR";
		
		//header('Content-type: text/calendar; charset=utf-8');
		//header('Content-Disposition: inline; filename=calendar.ics');
		//remove &
		
		//$output = str_replace("&", "+", $output);
		echo $output;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
?>
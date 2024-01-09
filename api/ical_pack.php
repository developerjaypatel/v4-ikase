<?php
$app->get('/ical/customer/{token}', 'exportCalendar')->add(Api\Middleware\Authorize::class);

function exportCalendar($token) {
	//$token = passed_var('token', 'get');
	
	if ($token == "") {
		$error = array("error"=> array("text"=>"no entry"));
        echo json_encode($error);
		die();
	}
    $sql = "SELECT DISTINCT `event_id` id, eve.`event_uuid`, `event_date`, `event_name`, `event_dateandtime` `start`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `event_title` `title`, `event_email`, `event_hour`, `event_type`, eve.`customer_id`, `color`, IF(`color`='yellow','black', 'white') `textColor`, 'eventClass' `className`, '' `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `assignee`, `event_from`, `event_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`
			FROM `cse_event` eve
			INNER JOIN `cse_customer` csc
			ON eve.customer_id = csc.customer_id
			LEFT OUTER JOIN `cse_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN cse_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if (($_SESSION['user_customer_id']==1033)) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "cse_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `cse_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			WHERE 1";
	$sql .=	" AND `eve`.`deleted` ='N'
	AND csc.pwd = '" . $token . "'";
	$sql .=	" AND `eve`.`event_type` != 'phone_call' ";
	$sql .= " ORDER BY eve.event_id ASC";
	//die($sql);
	//cc0b3b41871473a9d54a6d87260f47bd
	try {
		$allcusevents = DB::select($sql);
		
		// the iCal date format. Note the Z on the end indicates a UTC timestamp.
		define('DATE_ICAL', 'Ymd\THis\Z');
		 
		// max line length is 75 chars. New line is \\n
		 
		$output = "BEGIN:VCALENDAR
		METHOD:PUBLISH
		VERSION:2.0
		PRODID:-//Thomas Multimedia//Clinic Time//EN\n";

        foreach($allcusevents as $appointment):
			//put together the actual event deteails
			//STATUS:" . strtoupper($appointment->status)
			//LAST-MODIFIED:" . date(DATE_ICAL, strtotime($appointment->last_update)) . "
			$output .=
			"BEGIN:VEVENT
			SUMMARY:$appointment->event_first_name $appointment->event_last_name
			UID:$appointment->id
			STATUS:CONFIRMED
			DTSTART:" . date(DATE_ICAL, strtotime($appointment->start)) . "
			DTEND:" . date(DATE_ICAL, strtotime($appointment->event_end_time)) . "
			LAST-MODIFIED:" . date(DATE_ICAL) . "
			LOCATION:$appointment->location $appointment->title
			END:VEVENT\n";		
		endforeach;
		
		// close calendar
		$output .= "END:VCALENDAR";
		
		header('Content-type: text/calendar; charset=utf-8');
		header('Content-Disposition: inline; filename=calendar.ics');

		echo $output;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

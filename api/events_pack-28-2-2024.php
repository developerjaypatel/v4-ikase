<?php
use Slim\Routing\RouteCollectorProxy;

$app->group('', function (RouteCollectorProxy $app) {
	$app->group('/kase', function (RouteCollectorProxy $app) {
		$app->get('/events/{case_id}', 'getKaseEvents');
		$app->get('/events/dates/{case_id}/{start}/{end}', 'getKaseEventsDates');
		$app->get('/phone_calls/{case_id}', 'getKaseCalls');
	});
	$app->get('/injury/appearances/{injury_id}', 'getInjuryAppearances');
	$app->get('/callsnew', 'newPhoneCalls');
	$app->get('/callsall', 'newPhoneCallsAll');
	$app->get('/callsbydate/{start}/{end}', 'getPhoneCallsByDate');


	$app->group('/customer', function (RouteCollectorProxy $app) {
		$app->group('/events', function (RouteCollectorProxy $app) {
			$app->get('', 'getCustomerEvents');

			$app->get('/attorney/{attorney}', 'getCustomerEventsByAttorney');
			$app->get('/worker/{worker}', 'getCustomerEventsByWorker');
			$app->get('/assignee/{assignee}', 'getCustomerEventsByAssignee');
			$app->get('/assigneebytype/{type}/{assignee}', 'getCustomerEventsByTypeByAssignee');

			$app->get('/assigneebydate/{assignee}/{start}/{end}', 'getCustomerEventsByAssigneeByDate');
			$app->get('/assigneebytypebydate/{type}/{assignee}/{start}/{end}',
				'getCustomerEventsByTypeByAssigneeByDate');

			$app->get('/type/{type}', 'getCustomerEventsByType');
			$app->get('/casetype/{case_type}/{type}/{start}/{end}', 'getCustomerEventsByCaseType');
			$app->get('/typebydate/{type}/{start}/{end}', 'getCustomerEventsByTypeByDate');
			$app->get('/alldates/{start}/{end}', 'getCustomerAllEventsByDate');
			$app->get('/dates/{start}/{end}', 'getCustomerEventsByDate');
		});

		$app->group('/inhouse', function (RouteCollectorProxy $app) {
			$app->get('', 'getCustomerInhouseEvents');
			$app->get('/dates/{start}/{end}', 'getCustomerInhouseEventsByDate');
		});

		$app->group('/intakes', function (RouteCollectorProxy $app) {
			$app->get('', 'getCustomerIntakes');
			$app->get('/dates/{start}/{end}', 'getCustomerIntakesByDate');
		});
	});

	$app->group('/event', function (RouteCollectorProxy $app) {
		$app->post('/delete', 'deleteEvent');
		$app->post('/add', 'addEvent');
		$app->post('/update', 'updateEvent');
		$app->post('/read', 'readEvent');
		$app->post('/move', 'moveEvent');
		$app->post('/update/date', 'updateEventDate');

		$app->post('/transfercc', 'transferCourtCalendarEvent');
		$app->post('/dismisscc', 'dismissCourtCalendarEvent');
	});

	$app->group('/events', function (RouteCollectorProxy $app) {
		$app->get('', 'getEvents');
		$app->get('/recent', 'getEventsRecent');
		$app->get('/upcoming', 'getUpcomingEvents');

		$app->get('/courtcalendar', 'getCourtCalendar');
		$app->get('/courtcalendarpending', 'getCourtCalendarPending');
		$app->get('/courtcalendarpendinguser', 'getCourtCalendarPendingByUser');

		$app->group('/partnerkalendar', function (RouteCollectorProxy $app) {
			$app->get('', 'getPartnerEvents');
			$app->get('/dates/{start}/{end}', 'getPartnerEventsByDate');
		});

		$app->group('/employeekalendar', function (RouteCollectorProxy $app) {
			$app->get('', 'getEmployeeEvents');
			$app->get('/dates/{start}/{end}', 'getEmployeeEventsByDate');
		});

		$app->get('/allkase/{id}', 'getAllKaseEvents');
		$app->get('/futurekase/{id}', 'getFutureKaseEvents');
		$app->get('/ikalendar/{calendar_id}', 'getCalendarEvents');

		$app->group('/userkalendar', function (RouteCollectorProxy $app) {
			$app->get('/{user_id}', 'getUserEvents');
			$app->get('/dates/{user_id}/{start}/{end}', 'getUserEventsByDate');
		});

		$app->get('/count', 'getEventCount');
		$app->get('/related/{relationships}', 'getEvents');

		$app->get('/{id}', 'getEvent');
	});

	$app->get('/popups', 'getPopupReminders');
	$app->get('/popupread/{reminderbuffer_id}', 'updatePopupRead');
	$app->get('/popupsnooze/{reminderbuffer_id}/{interval}', 'snoozePopup');

	$app->get('/lastchange/events', 'getLastEventChange');
	$app->get('/latestchanges/events/{max_track_id}', 'getCustomerEvents');
})->add(Api\Middleware\Authorize::class);

$app->group('/courtcalendar', function (RouteCollectorProxy $app) {
	$app->get('/lookup/{case_number}', 'getCourtCalendarByADJ');
	$app->post('/bulklookup', 'getCourtCalendarByBulkADJ');
});

$app->get('/customer/eventscount', 'getCustomerEventCounts');
$app->post('/remote/events/upcoming', 'getRemoteUpcomingEvents');
$app->post('/popupssent', 'setReminderSent');

function getEventsRecent() {
	getEvents("", 5);
}

function getEventCount() {
	session_write_close();
	$sql = "SELECT  MIN(`event_id`) id, CAST(`event_dateandtime` AS DATE) as `start`, 
	COUNT(`event_id`) `title`, 'true' `allDay`
	FROM `cse_event` 
	WHERE 1
	AND `cse_event`.`deleted` ='N'
	AND YEAR(`event_dateandtime`) > 1969
	AND `cse_event`.event_type != 'intake'
	AND `cse_event`.event_type != 'phone_call'
	AND `cse_event`.customer_id = " . $_SESSION['user_customer_id'] . "
	GROUP BY CAST(`event_dateandtime` AS DATE)";
	try {
		$eventcounts = DB::select($sql);
		echo json_encode($eventcounts);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
	}
}
function getPopupReminders() {
	session_write_close();
	
	$arrReminders = array();
	$query_date = date("Y-m-d H:i");
	//$query_date = '2018-08-15 08:00:00';

	$sql = "SELECT DISTINCT cr.*, crm.`message_uuid`, cm.`message`, cm.subject, 
			cmu.`user_uuid`, crb.`reminderbuffer_id`, 
			ce.`event_id`, ce.full_address location, ce.color, ce.event_dateandtime message_date,
			IFNULL(cc.`case_id`, '') case_id, IFNULL(cc.case_name, '') case_name
			FROM `cse_reminder` cr
			
			INNER JOIN `cse_event_reminder` cer
			ON cr.`reminder_uuid` = cer.`reminder_uuid`
			INNER JOIN `cse_event` ce
			ON cer.`event_uuid` = ce.`event_uuid`
			
			LEFT OUTER JOIN `cse_reminder_message` crm
			ON cr.`reminder_uuid` = crm.`reminder_uuid`
			LEFT OUTER JOIN `cse_message` cm
			ON crm.`message_uuid` = cm.`message_uuid`
			LEFT OUTER JOIN `cse_message_user` cmu
			ON cm.`message_id` = cmu.`message_id` AND cmu.`type` = 'to'
			LEFT OUTER JOIN `cse_reminderbuffer` crb
			ON cr.`reminder_uuid` = crb.`reminder_uuid` AND crb.deleted = 'N'
			
			LEFT OUTER JOIN `cse_case_event` cce
			ON ce.`event_uuid` = cce.`event_uuid`
			LEFT OUTER JOIN `cse_case` cc
			ON cc.`case_uuid` = cce.`case_uuid`
			WHERE 1 
			AND CAST(cr.reminder_datetime AS DATE) = '" . date("Y-m-d", strtotime($query_date)) . "'
			AND DATE_FORMAT(cr.reminder_datetime, '%Y-%m-%d %H:%i') <= '" . $query_date . "'
			AND 
				(cr.reminder_type = 'popup' OR (cr.reminder_type = 'interoffice' AND cce.attribute = 'statute_limitation'))
			AND cm.message_to LIKE '%" . $_SESSION["user_nickname"] . "%'
			AND cr.deleted = 'N'
			AND (cmu.`read_status` = 'N' OR crb.`reminderbuffer_id` IS NULL)
			AND cr.customer_id = '" . $_SESSION["user_customer_id"] . "'";
		
	//die($sql);
	// echo $sql . "\r\n";
	$trackname = "tracking.txt";
	try {
		$buffers = DB::select($sql);
		// $buffer = $stmt->fetchObject();
		// die(print_r($buffers));
		foreach ($buffers as $key => $buffer) {
			//fill the buffer
			$customer_id = $buffer->customer_id;
			$user_uuid = $buffer->user_uuid;
			$message = $buffer->message;
			$reminder_uuid = $buffer->reminder_uuid;
			$reminder_id = $buffer->reminder_id;
			$reminderbuffer_id = $buffer->reminderbuffer_id;
			$event_id = $buffer->event_id;
			$case_id = $buffer->case_id;
			// $buffer_id = $buffer->buffer_id;    
			$message_uuid = $buffer->message_uuid;
			$reminder_datetime = date("Y-m-d H:i", strtotime($buffer->reminder_datetime));

			$str_SQL = "INSERT INTO `cse_reminderbuffer` (`message_uuid`, `reminder_uuid`, `from`, `recipients`, `subject`, `message`, `customer_id`) 
						SELECT '" . $message_uuid . "', '" . $reminder_uuid . "', 'system', '" . $user_uuid . "', '', '" . addslashes($message) . "', '" . $customer_id . "'
						FROM dual
						WHERE NOT EXISTS (
							SELECT * 
							FROM `cse_reminderbuffer` 
							WHERE reminder_uuid = '" . $reminder_uuid . "'
							AND customer_id = '" . $customer_id . "'
						)";

			// echo $str_SQL . ";\r\n";
			DB::run($str_SQL);
	$inserted_id = DB::lastInsertId();
			if ($inserted_id!=0) {
				//not zero, new buffer
				$reminderbuffer_id = $inserted_id;
			}
			
			$strSQL = "UPDATE `cse_reminder` 
					   SET `buffered` = 'Y' 
					   WHERE `reminder_id` = '" . $reminder_id . "'
					   AND `customer_id` = '" . $customer_id . "'";
			// echo $strSQL . ";\r\n";
			$stmt = DB::run($strSQL);
			
			if(($reminder_datetime > date("Y-m-d H:i")) && ($reminder_datetime < date("Y-m-d H:i", strtotime("+15 minutes")))){
				// die("green: " . $reminder_datetime);
				$color = "green";
			} else {
				// die("red: " . $reminder_datetime);
				$color = "red";
			}
			$reminder_datetime_psuedo = date("m/d/y h:iA", strtotime($reminder_datetime));
			$arrReminders[] = array(
			"message"=>$message, 
			"message_date"=>$buffer->message_date,
			"reminderbuffer_id"=>$reminderbuffer_id, 
			"color"=>$color, 
			"event_id"=> $event_id, 
			"case_id"=>$case_id, 
			"reminder_datetime"=>$reminder_datetime_psuedo,
			"location"=>$buffer->location,
			"subject"=>$buffer->subject,
			"case_name"=>$buffer->case_name,
			);
		}	
		die(json_encode(array("success"=>"true", "reminders"=>$arrReminders)));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
			echo json_encode($error);
	}   	
}
function setReminderSent(){
	// die(print_r($_POST));
	$reminderbuffer_ids = passed_var("reminderbuffer_ids", "post");
	$arrReminderBuffers = explode(",", $reminderbuffer_ids);
	// die("remindersent: " . print_r($arrReminderBuffers));
	try{
		for ($i=0; $i < count($arrReminderBuffers); $i++) { 
			$reminderbuffer_id = $arrReminderBuffers[$i];
			$customer_id = $_SESSION['user_customer_id'];
			
			$sql = "SELECT crb.`recipients`, crb.`message`, crb.`message_uuid`, crb.`reminder_uuid`, crb.`customer_id` 
					FROM `cse_reminderbuffer` crb
					WHERE 1 
					AND crb.`reminderbuffer_id` = '" . $reminderbuffer_id . "'
					AND crb.`customer_id` = '" . $_SESSION["user_customer_id"] . "'";
			// echo $sql . ";\r\n";
			// die($sql);
            $stmt = DB::run($sql);
			$reminderbuffer = $stmt->fetchObject();
			// die(print_r($reminderbuffer));

            $query = "INSERT INTO `cse_remindersent` (`reminderbuffer_id`, `recipients`, `subject`, `message`, `message_uuid`, `reminder_uuid`, `customer_id`) 
					  SELECT " . $reminderbuffer_id . ", '" . $reminderbuffer->recipients . "', 'event text message sent' , '" . addslashes($reminderbuffer->message) . "', '" . $reminderbuffer->message_uuid . "', '" . $reminderbuffer->reminder_uuid . "', '" . $customer_id . "'
					  FROM dual
						WHERE NOT EXISTS (
							SELECT * 
							FROM `cse_remindersent` 
							WHERE reminder_uuid = '" . $reminderbuffer->reminder_uuid . "'
							AND customer_id = '" . $reminderbuffer->customer_id . "'
						)";
            // echo $query . ";\r\n";
          	// die();
            $stmt = DB::run($query);
			
		}
		die(json_encode(array("success"=>"true")));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage(), "query"=>$query));
			echo json_encode($error);
	} 
}
function updatePopupRead($reminderbuffer_id) {
	$sql = "SELECT cmu.message_user_id 
			FROM cse_reminderbuffer crb
			LEFT OUTER JOIN cse_message cm
			ON crb.message_uuid = cm.message_uuid
			LEFT OUTER JOIN cse_message_user cmu
			ON cm.message_id = cmu.message_id AND cmu.`type` = 'to'
			WHERE 1
			AND crb.reminderbuffer_id = '" . $reminderbuffer_id . "'
			AND crb.customer_id = '" . $_SESSION["user_customer_id"] . "'";
	// echo $sql . "\r\n";
	try{
		$stmt = DB::run($sql);
		// $buffers = $stmt->fetchAll(PDO::FETCH_OBJ);
		$message_user = $stmt->fetchObject();

		$query = "UPDATE cse_message_user 
				  SET read_status = 'Y', 
				  read_date = '" . date("Y-m-d H:i:s") . "'
				  WHERE message_user_id = '" . $message_user->message_user_id . "'
				  AND `type` = 'to'
				  AND customer_id = '" . $_SESSION["user_customer_id"] . "'";

		//die($query . "\r\n");
		$stmt = DB::run($query);
		die(json_encode(array("success"=> "true")));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
			echo json_encode($error);
	}   
}
function snoozePopup($reminderbuffer_id, $interval){
$sql = "SELECT cr.reminder_id, ce.event_dateandtime 
FROM cse_reminderbuffer crb
		LEFT OUTER JOIN cse_reminder cr
		ON crb.reminder_uuid = cr.reminder_uuid		
		LEFT OUTER JOIN cse_event_reminder cer 
		ON cr.reminder_uuid = cer.reminder_uuid
		LEFT OUTER JOIN cse_event ce 
		ON cer.event_uuid = ce.event_uuid
		WHERE 1 
		AND crb.reminderbuffer_id = '" . $reminderbuffer_id . "' 
		AND crb.customer_id = '" . $_SESSION["user_customer_id"] . "'";
		// die($sql . ";\r\n intervals: " . $interval);
	try{
		$stmt = DB::run($sql);
		// $buffers = $stmt->fetchAll(PDO::FETCH_OBJ);
		$reminder = $stmt->fetchObject();
		if(strpos($interval, "-") !== -1){
			$reminder_datetime = date("Y-m-d H:i:00", strtotime("+" . intval($interval) . " minutes", strtotime($reminder->event_dateandtime)));
		} else {
			$reminder_datetime = date("Y-m-d H:i:00", strtotime("+" . $interval . " minutes"));
		}
		// die("date: " . $reminder_datetime);
		// die(print_r($reminder_datetime));

		$query = "UPDATE cse_reminder 
				  SET reminder_datetime = '" . $reminder_datetime . "',
				  buffered = 'N' 
				  WHERE reminder_id = '" . $reminder->reminder_id . "' 
				  AND customer_id = '" . $_SESSION["user_customer_id"] . "'";
		// echo $query . ";\r\n";
		// die();
		$stmt = DB::run($query);
/*
	 	$strSQL = "UPDATE cse_message_user 
		 		   SET read_status = 'N', read_date = '0000-00-00 00:00:00' 
				   WHERE message_user_id = '" . $reminder->message_user_id . "' 
				   AND customer_id = '" . $_SESSION["user_customer_id"] . "'";
		
		// echo $strSQL . ";\r\n";
		// die();
		$stmt = DB::run($strSQL);
*/
		die(json_encode(array("success"=> "true")));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
			echo json_encode($error);
	}  
}
function getLatestEventChanges($current_max_track_id) {
	session_write_close();
	
	$sql = "SELECT `operation`, `event_id` id, `event_id`, `event_date`, `event_duration`, `event_name`, `event_dateandtime` `start`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `event_title` `title`, `event_title`, `event_email`, `event_hour`, `event_type`,
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`
	FROM `cse_event_track`
	WHERE event_track_id > " . $current_max_track_id . "
	AND customer_id = " . $_SESSION['user_customer_id'] . "
	AND event_type != 'intake'
	AND event_type != 'phone_call'";
	
	try {
		$events = DB::select($sql);
		echo json_encode($events);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
	}
}
function getLastEventChange() {
	session_write_close();
	$sql = "SELECT MAX(event_track_id) max_track_id
	FROM cse_event_track
	WHERE 1
	AND customer_id = " . $_SESSION['user_customer_id'];
	//die($sql);
	try {
		$stmt = DB::run($sql);
		$event = $stmt->fetchObject();

		echo json_encode($event);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getEvents($relationships = "", $limit = "") {
	session_write_close();
	
	$arrRelationships = array();
	$arrJoins = array();
	if ($relationships!="") {
		$arrRelationships = explode("|", $relationships);
	}
	foreach($arrRelationships as $relationship) {
		$arrRelationship  = explode("~", $relationship);

		//defaults
		$arrJoins[] = array ("table_name"=>$arrRelationship[0], "table_id"=>$arrRelationship[1]);
	}
	
	$default_color = "white";
	$other_color = "black";
	if ($_SESSION['user_customer_id']==1049 || $_SESSION['user_customer_id']==1075) {
		$default_color = "black";
		$other_color = "white";
	}
	
    $sql = "SELECT DISTINCT `event_id` id, `cse_event`.`event_uuid`, `event_date`, `event_duration`, `event_name`, `event_dateandtime` `start`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `event_title` `title`, `event_title`, `event_email`, `event_hour`, `event_type`,
    IFNULL(calsett.setting_value, `event_type_abbr`) `event_type_abbr`, `cse_event`.`customer_id`, IFNULL(calsett.default_value, `color`) `color`, `off_calendar`, IFNULL(calsett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(calsett.default_value, '') `backgroundColor`, 'black' `borderColor`, `full_address` `location`, `judge`, `assignee`, IFNULL(venue_abbr, '') venue_abbr
			FROM ";
			if ($limit!="") {
				$sql .= "(SELECT * FROM `cse_event` ORDER BY event_id DESC LIMIT 0, 10000) `cse_event` ";
			} else {
				$sql .= "`cse_event`";
			}
			$sql .= " 
			LEFT OUTER JOIN (
				SELECT * FROM `cse_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) calsett
			ON LOWER(`cse_event`.event_type) = LOWER(calsett.setting)
			
			LEFT OUTER JOIN cse_case_event ccev
			ON `cse_event`.event_uuid = ccev.event_uuid AND ccev.deleted = 'N'
			LEFT OUTER JOIN cse_case ccase
			ON ccev.case_uuid = ccase.case_uuid
			LEFT OUTER JOIN `cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			LEFT OUTER JOIN `cse_setting` sett
			ON `cse_event`.event_type = sett.setting";
	
	if (count($arrJoins)>0) {
		foreach($arrJoins as $join) {
			
			$table_name = $join["table_name"];
			$table_id = $join["table_id"];
			$sql .=	" INNER JOIN cse_event_" . $table_name . "
		ON cse_event.event_uuid = cse_event_" . $table_name . ".event_uuid
		INNER JOIN `cse_" . $table_name . "_complete`
		ON `cse_event_" . $table_name . "`.`" . $table_name . "_uuid` = `cse_" . $table_name . "_complete`.`" . $table_name . "_uuid`";
		}
	}
	$sql .=	" WHERE `cse_event`.`deleted` ='N'
	AND `cse_event`.customer_id = " . $_SESSION['user_customer_id'];
	
	if (count($arrJoins)>0) {
		foreach($arrJoins as $join) {
			$table_name = $join["table_name"];
			$table_id = $join["table_id"];
			$sql .=	" AND `cse_" . $table_name . "_complete`.`" . $table_name . "_id` = :" . $table_name . "_id";
			$sql .=	" AND `cse_" . $table_name . "_complete`.deleted = 'N'";
		}
	}
	if ($limit!="") {
		if (is_numeric($limit)) {
			$sql .= " ORDER BY event_id DESC LIMIT 0, " . $limit;
		}
	} else {
		$sql .= " ORDER BY event_dateandtime ASC";
	}
	if ($_SERVER['REMOTE_ADDR']=='47.153.51.181') {
		//die($sql);
	}
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if (is_array($arrJoins)>0) {
			foreach($arrJoins as $join) {
				$table_name = $join["table_name"];
				$table_id = $join["table_id"];
				$stmt->bindParam("" . $table_name . "_id", $table_id);
			}
		}
		$stmt->execute();
		$events = $stmt->fetchAll(PDO::FETCH_OBJ);

		//die(print_r($events));
        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($events);
        } else {
            echo $_GET['callback'] . '(' . json_encode($events) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKaseEventsDates($case_id, $start, $end) {
	session_write_close();
	
	$default_color = "white";
	$other_color = "black";
	if ($_SESSION['user_customer_id']==1049 || $_SESSION['user_customer_id']==1075) {
		$default_color = "black";
		$other_color = "white";
	}
	$sql = "SELECT DISTINCT `event_id`, eve.`event_uuid`, `event_date`, `event_duration`, `event_name`, `event_dateandtime` `start`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `event_title` `title`, `event_title`, `event_email`, `event_hour`, `event_type`,
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, `off_calendar`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', IFNULL(employer.`company_name`, '')) `case_name`, ccase.case_number, ccase.case_type, ccase.file_number, ccase.case_language, ccase.case_name case_stored_name, `event_id` id, eve.`event_uuid` `uuid`, IFNULL(venue_abbr, '') venue_abbr
			FROM `cse_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `cse_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			INNER JOIN `cse_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			INNER JOIN cse_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			
			LEFT OUTER JOIN `cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "cse_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `cse_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			WHERE ccase.deleted != 'Y'
			AND eve.event_type != 'phone_call'
			AND ccase.case_id = :case_id
			AND CAST(eve.event_dateandtime AS DATE) BETWEEN :start AND :end";
	

	$sql .=	" AND `eve`.`deleted` ='N'
	AND eve.customer_id = " . $_SESSION['user_customer_id'];
	$sql .= " ORDER BY eve.event_dateandtime ASC";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("start", $start);
		$stmt->bindParam("end", $end);
		
		$stmt->execute();
		$events = $stmt->fetchAll(PDO::FETCH_OBJ);
        echo json_encode($events);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKaseEvents($case_id) {
	session_write_close();
	
	$kase = getKaseInfo($case_id);
	$case_uuids = $kase->uuid;
	$related_kases = getRelatedKases($case_id);
	
	if (count($related_kases) > 0) {
		$arrRelatedList = array();
		foreach($related_kases as $related_kase) {
			$arrRelatedList[] = $related_kase->case_uuid;
		}
		$case_uuids = "'" . implode("','", $arrRelatedList) . "'";
	}
	//die($case_uuids);
	
	$default_color = "white";
	$other_color = "black";
	if ($_SESSION['user_customer_id']==1049 || $_SESSION['user_customer_id']==1075) {
		$default_color = "black";
		$other_color = "white";
	}
    $sql = "SELECT DISTINCT `event_id`, `event_id` id, eve.`event_uuid`, `event_date`, `event_duration`, `event_name`, `event_dateandtime` `start`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `event_title` `title`, `event_title`, `event_email`, `event_hour`, `event_type`,
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, `off_calendar`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.case_type, ccase.file_number, ccase.case_language, ccase.case_name case_stored_name, 
	IFNULL(ccase.attorney, '') supervising_attorney, IFNULL(ccase.supervising_attorney, '') attorney, 
	IFNULL(venue_abbr, '') venue_abbr
			FROM `cse_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `cse_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			INNER JOIN `cse_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			INNER JOIN cse_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			
			LEFT OUTER JOIN `cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "cse_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `cse_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			WHERE ccase.deleted != 'Y'
			AND eve.event_type != 'phone_call'";
	$sql .=	" AND ccase.case_uuid IN (" . $case_uuids . ")";
		//$sql .=	" AND ccase.case_id = :case_id";	
	$sql .=	" AND `eve`.`deleted` ='N'
	AND eve.customer_id = " . $_SESSION['user_customer_id'];
	$sql .= " ORDER BY eve.event_dateandtime ASC";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		//$stmt->bindParam("case_id", $case_id);
		
		$stmt->execute();
		$events = $stmt->fetchAll(PDO::FETCH_OBJ);
		
        echo json_encode($events);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function readEvent() {
	session_write_close();
	
	$id = passed_var("id", "post");
	$sql = "UPDATE cse_event mes, cse_event_user ceu
			SET ceu.`read_status` = 'Y',
			ceu.read_date = '" . date("Y-m-d H:i:s") . "'
			WHERE mes.`event_uuid`= ceu.event_uuid
			AND ceu.type = 'to'
			AND mes.event_id = :id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		//track now
		trackEvent("read", $id);
		echo json_encode(array("success"=>"task marked as read"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getInjuryAppearances($injury_id) {
	session_write_close();
	
	$default_color = "white";
	$other_color = "black";
	if ($_SESSION['user_customer_id']==1049 || $_SESSION['user_customer_id']==1075) {
		$default_color = "black";
		$other_color = "white";
	}
	
    $sql = "SELECT DISTINCT `event_id` id, eve.`event_uuid`, `event_date`, `event_duration`, `event_name`, `event_dateandtime` `start`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `event_title` `title`, `event_title`, `event_email`, `event_hour`, `event_type`,
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, `off_calendar`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, eve.`end_date`, `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.case_type, ccase.file_number, ccase.case_language, ccase.case_name case_stored_name, IFNULL(ccase.attorney, '') supervising_attorney, ceu.read_status, ceu.read_date , IFNULL(venue_abbr, '') venue_abbr
			FROM `cse_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `cse_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			INNER JOIN `cse_event_user` ceu
			ON eve.event_uuid = ceu.event_uuid
			INNER JOIN `cse_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			INNER JOIN cse_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			
			LEFT OUTER JOIN `cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
			INNER JOIN cse_case_injury cci
			ON ccase.case_uuid = cci.case_uuid
			INNER JOIN cse_injury ci
			ON cci.injury_uuid = ci.injury_uuid
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "cse_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `cse_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			WHERE ccase.deleted != 'Y'
			AND eve.event_title = 'Lien Appearance'
			AND ci.injury_id = :injury_id";
	

	$sql .=	" AND `eve`.`deleted` ='N'
	AND eve.customer_id = " . $_SESSION['user_customer_id'];
	$sql .= " ORDER BY eve.event_dateandtime DESC";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("injury_id", $injury_id);
		
		$stmt->execute();
		$events = $stmt->fetchAll(PDO::FETCH_OBJ);
        echo json_encode($events);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKaseCalls($case_id) {
	session_write_close();
	$default_color = "white";
	$other_color = "black";
	if ($_SESSION['user_customer_id']==1049 || $_SESSION['user_customer_id']==1075) {
		$default_color = "black";
		$other_color = "white";
	}
	
    $sql = "SELECT DISTINCT `event_id` id, eve.`event_uuid`, `event_date`, `event_duration`, `event_name`, `event_dateandtime` `start`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `event_title` `title`, `event_title`, `event_email`, `event_hour`, `event_type`,
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, `off_calendar`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.case_type, ccase.file_number, ccase.case_language, ccase.case_name case_stored_name, IFNULL(ccase.attorney, '') supervising_attorney, ceu.read_status, ceu.read_date , IFNULL(venue_abbr, '') venue_abbr
			FROM `cse_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `cse_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			INNER JOIN `cse_event_user` ceu
			ON eve.event_uuid = ceu.event_uuid
			INNER JOIN `cse_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			INNER JOIN cse_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			LEFT OUTER JOIN `cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "cse_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `cse_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			WHERE ccase.deleted != 'Y'
			AND eve.event_type = 'phone_call'
			AND ccase.case_id = :case_id";
	

	$sql .=	" AND `eve`.`deleted` ='N'
	AND eve.customer_id = " . $_SESSION['user_customer_id'];
	$sql .= " ORDER BY eve.event_dateandtime DESC";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		
		$stmt->execute();
		$events = $stmt->fetchAll(PDO::FETCH_OBJ);
        echo json_encode($events);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getPhoneCallsByDate($start, $end) {
	newPhoneCalls(true, $start, $end);
}
function newPhoneCallsAll() {
	newPhoneCalls(true);
}
function newPhoneCalls($blnAll = false, $start = "", $end = "") {
	session_write_close();
	$default_color = "white";
	$other_color = "black";
	if ($_SESSION['user_customer_id']==1049 || $_SESSION['user_customer_id']==1075) {
		$default_color = "black";
		$other_color = "white";
	}
	
	if ($start=="6_months") {
		$start = date("Y-m-d", mktime(0, 0, 0, date("m")-6, date("d"),   date("Y")));
	}
	//IF(sett.default_value IS NULL, '" . $other_color ."', '" . $default_color ."') `textColor`
	$sql = "SELECT DISTINCT `event_id` id, eve.`event_uuid`, `event_date`, `event_duration`, `event_name`, `event_dateandtime` `start`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `event_title` `title`, `event_title`, `event_email`, `event_hour`, `event_type`,
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, `off_calendar`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.case_type, ccase.file_number, ccase.case_language, ccase.case_name case_stored_name, IFNULL(ccase.attorney, '') supervising_attorney, ceu.read_status, ceu.read_date , IFNULL(venue_abbr, '') venue_abbr
			FROM `cse_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `cse_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			INNER JOIN `cse_event_user` ceu
			ON eve.event_uuid = ceu.event_uuid
			LEFT OUTER JOIN `cse_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN cse_case ccase
			ON ceve.case_uuid = ccase.case_uuid AND ccase.deleted != 'Y'
			
			LEFT OUTER JOIN `cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "cse_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `cse_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			WHERE 1
			AND eve.event_type = 'phone_call'
			AND `ceu`.`type` = 'to'
			AND `ceu`.deleted = 'N'";
			
	if ($start=="") {
		$sql .=	" 	
			AND `ceu`.read_status = 'N'
			";
	}
	if (!$blnAll) {
		$sql .= " 
		AND `ceu`.user_uuid = '" . $_SESSION['user_id'] . "'";
	}

	if ($start!="") {
		$sql .=	" 
		AND CAST(`eve`.event_dateandtime AS DATE) BETWEEN :start AND :end";
	}
	$sql .=	" 
	AND `eve`.`deleted` ='N'
	AND eve.customer_id = " . $_SESSION['user_customer_id'];
	$sql .= " 
	ORDER BY eve.event_dateandtime DESC";
	
	if ($_SERVER['REMOTE_ADDR']=='47.153.51.181') {
		//die($sql);
	}
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($start!="") {
			$stmt->bindParam("start", $start);
			$stmt->bindParam("end", $end);
		}
		$stmt->execute();
		$events = $stmt->fetchAll(PDO::FETCH_OBJ);
        echo json_encode($events);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getCustomerEventCounts() {
	session_write_close();
	
	$sql = "SELECT YEAR(event_day) event_year, MONTH(event_day) event_month, MAX(event_count) event_counts
FROM (
SELECT CAST(event_dateandtime as DATE) event_day, COUNT(event_id) event_count 
FROM cse_event `eve`
			WHERE 1";
	//AND ccase.case_status NOT LIKE '%close%' 
	$sql .=	" AND `eve`.`deleted` ='N'
	AND eve.customer_id = " . $_SESSION['user_customer_id'];
	
	//custom for reino
	if ($_SESSION['user_customer_id']==1055) {
		$sql .=	" AND (`eve`.`event_type` != 'phone_call' AND `eve`.`event_type` != 'intake')";
	} else {
		$sql .=	" AND (`eve`.`event_type` != 'phone_call' AND `eve`.`event_type` != 'intake' )";	//AND cal.sort_order = 0
	}
	//$sql .=	" AND ((`eve`.`event_type` != 'phone_call' AND `eve`.`event_type` != 'intake') OR cal.sort_order = 0)";
	$six_months_ago = mktime(0, 0, 0, date("m") - 6,   date("d"),   date("Y"));
	$sql .=	" AND (eve.event_date > '" . date("Y-m-d", $six_months_ago) . "'
	 OR eve.`event_dateandtime` > '" . date("Y-m-d", $six_months_ago) . "')";
	$sql .= " GROUP BY CAST(event_dateandtime as DATE)
) max_day
GROUP BY YEAR(event_day), MONTH(event_day)
ORDER BY YEAR(event_day), MONTH(event_day)";
	//die($sql );
	try {
		$event_counts = DB::select($sql);

        echo json_encode($event_counts);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}

}
function getCustomerEventsByCaseType($case_type, $type, $start, $end) {
	getCustomerEvents($case_type, $type, $start, $end);
}
function getCustomerEvents($case_type = "", $type = "", $start = "", $end = "") {
	session_write_close();
	
	if ($type=="_") {
		$type = "";
	}
	if ($start=="_") {
		$start = "";
	}
	if ($end=="_") {
		$end = "";
	}
	$default_color = "white";
	$other_color = "black";
	if ($_SESSION['user_customer_id']==1049 || $_SESSION['user_customer_id']==1075) {
		$default_color = "black";
		$other_color = "white";
	}
	
	$default_color = "black";
	$other_color = "white";
	/*
	`cse_setting` sett
	ON eve.event_type = sett.setting 
		AND sett.deleted = 'N'
		AND sett.customer_id = " . $_SESSION['user_customer_id'] . "
		AND sett.category = 'calendar_colors'
	*/
	$case_joins = "LEFT OUTER JOIN";
	if ($case_type != "") {
		$case_joins = "INNER JOIN";
	}
    $sql = "SELECT DISTINCT `event_id` id, eve.`event_uuid`, `event_date`, `event_duration`, `event_name`, `event_dateandtime` `start`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `event_title` `title`, `event_title`, `event_email`, `event_hour`, `event_type`,
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, `off_calendar`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, 
	IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, 
	`end_date`, `end_date` `end`,  `completed_date`, `callback_date`, `callback_completed` , 
	IFNULL(ccase.case_id, icase.case_id) case_id, 
	IFNULL(ccase.case_type, icase.case_type) case_type, 
	IF (ccase.case_id IS NULL, CONCAT(iapp.first_name,' ',iapp.last_name,' vs ', iemployer.`company_name`), CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`)) `case_name`, 
	IF (ccase.case_id IS NULL, icase.case_number, ccase.case_number) case_number, 
	IF (ccase.case_id IS NULL, icase.file_number, ccase.file_number) file_number,
	IF (ccase.case_type IS NULL, icase.case_type, ccase.case_type) case_type, 
	IF (ccase.case_id IS NULL, icase.case_name, ccase.case_name) case_stored_name,
	IF (ccase.case_id IS NULL, icase.case_language, ccase.case_language) case_language, 
	IFNULL(ccase.attorney, '') supervising_attorney, 
	cal.sort_order cal_sort_order, IFNULL(venue_abbr, '') venue_abbr
			FROM `cse_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `cse_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			" . $case_joins . " `cse_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			
			" . $case_joins . " cse_case ccase
			ON ceve.case_uuid = ccase.case_uuid AND ccase.`case_status` NOT LIKE '%close%' AND ccase.deleted = 'N'
			
			LEFT OUTER JOIN `cse_injury_event` cive
			ON eve.event_uuid = cive.event_uuid
			LEFT OUTER JOIN `cse_case_injury` cci
			ON cive.injury_uuid = cci.injury_uuid
			LEFT OUTER JOIN `cse_case` icase
			ON cci.case_uuid = icase.case_uuid
			
			LEFT OUTER JOIN `cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "cse_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `cse_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			
			LEFT OUTER JOIN cse_case_person icapp ON icase.case_uuid = icapp.case_uuid
			LEFT OUTER JOIN ";
			if ($_SESSION['user_customer_id']==1033) { 
				$sql .= "(" . SQL_PERSONX . ")";
			} else {
				$sql .= "cse_person";
			}
			$sql .= " iapp ON icapp.person_uuid = iapp.person_uuid
			LEFT OUTER JOIN `cse_case_corporation` iccorp
			ON (icase.case_uuid = iccorp.case_uuid AND iccorp.attribute = 'employer' AND iccorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` iemployer
			ON iccorp.corporation_uuid = iemployer.corporation_uuid
			
			LEFT OUTER JOIN cse_calendar_event cec             
			ON eve.event_uuid = cec.event_uuid             
			LEFT OUTER JOIN cse_calendar cal             
			ON cec.calendar_uuid = cal.calendar_uuid
			WHERE 1";
	//AND ccase.case_status NOT LIKE '%close%' 
	$sql .=	" AND `eve`.`deleted` ='N'
	AND eve.customer_id = " . $_SESSION['user_customer_id'];
	
	//custom for reino
	if ($_SESSION['user_customer_id']==1055) {
		$sql .=	" AND (`eve`.`event_type` != 'phone_call' AND `eve`.`event_type` != 'intake')";
	} else {
		$sql .=	" AND (`eve`.`event_type` != 'phone_call' AND `eve`.`event_type` != 'intake' )";	//AND cal.sort_order = 0
	}
	//$sql .=	" AND ((`eve`.`event_type` != 'phone_call' AND `eve`.`event_type` != 'intake') OR cal.sort_order = 0)";
	if ($start=="") {
		$six_months_ago = mktime(0, 0, 0, date("m") - 6,   date("d"),   date("Y"));
		$sql .=	" AND (eve.event_date > '" . date("Y-m-d", $six_months_ago) . "'
	 OR eve.`event_dateandtime` > '" . date("Y-m-d", $six_months_ago) . "')";
	} else {
		if ($end=="") {
			$sql .=	" AND CAST(eve.event_date AS DATE) '" . date("Y-m-d", strtotime($start)) . "'";
		} else {
			$sql .=	" AND CAST(eve.event_date AS DATE) BETWEEN '" . date("Y-m-d", strtotime($start)) . "'
	 AND '" . date("Y-m-d", strtotime($end)) . "'";
		}
	}
	if ($case_type != "") {
		if ($case_type=="pi") {
			$sql .= " AND ccase.case_type NOT LIKE 'WC%' AND ccase.case_type NOT LIKE 'W/C%' AND ccase.case_type NOT LIKE 'Worker%' ";
		}
		if ($case_type=="wc") {
			$sql .= " AND (ccase.case_type LIKE 'WC%' OR ccase.case_type LIKE 'W/C%' OR ccase.case_type LIKE 'Worker%') ";
		}
	}
	
	if ($type != "") {
		$sql .=	" AND `eve`.`event_type` = '" . $type . "'";
	}
	$sql .= " ORDER BY eve.event_id ASC";
	//die($sql);
	try {
		$allcusevents = DB::select($sql);
		
		//some fixes
		$output = json_encode($allcusevents);
		
		if ($_SESSION["user_customer_id"]==1075) {
			$output = str_replace('"case_type":"WCAB"', '"case_type":"WC"', $output);
			$output = str_replace('"case_type":""', '"case_type":"PI"', $output);
			$output = str_replace('"case_type":null', '"case_type":"PI"', $output);
			$output = str_replace('"case_type":"OTHER"', '"case_type":"PI"', $output);
			$output = str_replace('"case_type":"SS"', '"case_type":"PI"', $output);
			$output = str_replace('"case_type":"CRIMINAL"', '"case_type":"PI"', $output);
			$output = str_replace('"case_type":"CIVIL"', '"case_type":"PI"', $output);
			$output = str_replace('"case_type":"FAMILY"', '"case_type":"PI"', $output);
			$output = str_replace('"case_type":"IMM"', '"case_type":"PI"', $output);
		}
        echo $output;

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getCustomerEventsByAttorney($attorney) {
	session_write_close();
	$default_color = "white";
	$other_color = "black";
	if ($_SESSION['user_customer_id']==1049 || $_SESSION['user_customer_id']==1075) {
		$default_color = "black";
		$other_color = "white";
	}
    $sql = "SELECT DISTINCT `event_id` id, eve.`event_uuid`, `event_date`, `event_duration`, `event_name`, `event_dateandtime` `start`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `event_title` `title`, `event_email`, `event_hour`, `event_type`,
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, `off_calendar`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, 
	`end_date`, `end_date` `end`,  `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.case_type, ccase.file_number, ccase.case_language, ccase.case_name case_stored_name, IFNULL(ccase.attorney, '') supervising_attorney , IFNULL(venue_abbr, '') venue_abbr
			FROM `cse_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `cse_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			LEFT OUTER JOIN `cse_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN cse_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			LEFT OUTER JOIN `cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "cse_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `cse_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			LEFT OUTER JOIN cse_calendar_event cec             
			ON eve.event_uuid = cec.event_uuid             
			LEFT OUTER JOIN cse_calendar cal             
			ON cec.calendar_uuid = cal.calendar_uuid
			WHERE 1";
	$sql .=	" AND ccase.case_status NOT LIKE '%close%' AND `eve`.`deleted` ='N'
	AND eve.customer_id = " . $_SESSION['user_customer_id'];
	$sql .=	" AND `ccase`.`attorney` = '" . $attorney . "'";
	$six_months_ago = mktime(0, 0, 0, date("m") - 6,   date("d"),   date("Y"));
	$sql .=	" AND (eve.event_date > '" . date("Y-m-d", $six_months_ago) . "'
	 OR eve.`event_dateandtime` > '" . date("Y-m-d", $six_months_ago) . "')";
	//$sql .=	" AND ((`eve`.`event_type` != 'phone_call' AND `eve`.`event_type` != 'intake') OR cal.sort_order = 0)";
	$sql .= " ORDER BY eve.event_id ASC";
	//die($sql);
	try {
		$allcusevents = DB::select($sql);
        echo json_encode($allcusevents);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getCustomerEventsByTypeByDate($type, $start, $end) {
	session_write_close();
	$default_color = "white";
	$other_color = "black";
	if ($_SESSION['user_customer_id']==1049 || $_SESSION['user_customer_id']==1075) {
		$default_color = "black";
		$other_color = "white";
	}
	$type = trim(str_replace("_", " ", $type));

    $sql = "SELECT DISTINCT `event_id` id, eve.`event_uuid`, `event_date`, `event_duration`, `event_name`, `event_dateandtime` `start`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `event_title` `title`, `event_email`, `event_hour`, `event_type`,
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, `off_calendar`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, 
	`end_date`, `end_date` `end`,  `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.case_type, ccase.file_number, ccase.case_language, ccase.case_name case_stored_name, IFNULL(ccase.attorney, '') supervising_attorney , IFNULL(venue_abbr, '') venue_abbr
			FROM `cse_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `cse_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			LEFT OUTER JOIN `cse_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN cse_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			LEFT OUTER JOIN `cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "cse_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `cse_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			LEFT OUTER JOIN cse_calendar_event cec             
			ON eve.event_uuid = cec.event_uuid             
			LEFT OUTER JOIN cse_calendar cal             
			ON cec.calendar_uuid = cal.calendar_uuid
			WHERE 1";
	$sql .=	" AND ccase.case_status NOT LIKE '%close%' AND `eve`.`deleted` ='N'
	AND eve.customer_id = " . $_SESSION['user_customer_id'];
	$sql .=	" AND `eve`.`event_type` = '" . $type . "'";
	
	if ($start != $end) {
		$sql .=	" AND CAST(eve.event_dateandtime AS DATE) BETWEEN :start AND :end";
	} else {
		$sql .=	" AND CAST(eve.event_dateandtime AS DATE) = :start";
	}
	$sql .= " ORDER BY eve.event_id ASC";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("start", $start);
		if ($start != $end) {
			$stmt->bindParam("end", $end);
		}	
		$stmt->execute();
		$allcusevents = $stmt->fetchAll(PDO::FETCH_OBJ);
        echo json_encode($allcusevents);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getCustomerEventsByTypeByAssignee($type, $assignee){
	session_write_close();
	$default_color = "white";
	$other_color = "black";
	if ($_SESSION['user_customer_id']==1049 || $_SESSION['user_customer_id']==1075) {
		$default_color = "black";
		$other_color = "white";
	}
	$type = trim(str_replace("_", " ", $type));
	$assignee = trim(str_replace("_", " ", $assignee));
	
    $sql = "SELECT DISTINCT `event_id` id, eve.`event_uuid`, `event_date`, `event_duration`, `event_name`, `event_dateandtime` `start`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `event_title` `title`, `event_email`, `event_hour`, `event_type`,
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, `off_calendar`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, 
	`end_date`, `end_date` `end`,  `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.case_type, ccase.file_number, ccase.case_language, ccase.case_name case_stored_name, IFNULL(ccase.attorney, '') supervising_attorney , IFNULL(venue_abbr, '') venue_abbr
			FROM `cse_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `cse_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			LEFT OUTER JOIN `cse_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN cse_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			LEFT OUTER JOIN `cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "cse_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `cse_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			LEFT OUTER JOIN cse_calendar_event cec             
			ON eve.event_uuid = cec.event_uuid             
			LEFT OUTER JOIN cse_calendar cal             
			ON cec.calendar_uuid = cal.calendar_uuid
			WHERE 1";
	$sql .=	" AND ccase.case_status NOT LIKE '%close%' AND `eve`.`deleted` ='N'
	AND eve.customer_id = " . $_SESSION['user_customer_id'];
	$sql .=	" AND `eve`.`assignee` LIKE '%" . $assignee . "%'";
	$sql .=	" AND `eve`.`event_type` = '" . $type . "'";
	$six_months_ago = mktime(0, 0, 0, date("m") - 6,   date("d"),   date("Y"));
	$sql .=	" AND (eve.event_date > '" . date("Y-m-d", $six_months_ago) . "'
	 OR eve.`event_dateandtime` > '" . date("Y-m-d", $six_months_ago) . "')";
	//$sql .=	" AND ((`eve`.`event_type` != 'phone_call' AND `eve`.`event_type` != 'intake') OR cal.sort_order = 0)";
	$sql .= " ORDER BY eve.event_id ASC";
	//die($sql);
	try {
		$allcusevents = DB::select($sql);
        echo json_encode($allcusevents);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getCustomerEventsByTypeByAssigneeByDate($type, $assignee, $start, $end){
	session_write_close();
	$default_color = "white";
	$other_color = "black";
	if ($_SESSION['user_customer_id']==1049 || $_SESSION['user_customer_id']==1075) {
		$default_color = "black";
		$other_color = "white";
	}
	$type = trim(str_replace("_", " ", $type));
	$assignee = trim(str_replace("_", " ", $assignee));
	
    $sql = "SELECT DISTINCT `event_id` id, eve.`event_uuid`, `event_date`, `event_duration`, `event_name`, `event_dateandtime` `start`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `event_title` `title`, `event_email`, `event_hour`, `event_type`,
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, `off_calendar`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, 
	`end_date`, `end_date` `end`,  `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.case_type, ccase.file_number, ccase.case_language, ccase.case_name case_stored_name, IFNULL(ccase.attorney, '') supervising_attorney , IFNULL(venue_abbr, '') venue_abbr
			FROM `cse_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `cse_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			LEFT OUTER JOIN `cse_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN cse_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			LEFT OUTER JOIN `cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "cse_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `cse_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			LEFT OUTER JOIN cse_calendar_event cec             
			ON eve.event_uuid = cec.event_uuid             
			LEFT OUTER JOIN cse_calendar cal             
			ON cec.calendar_uuid = cal.calendar_uuid
			WHERE 1";
	$sql .=	" AND ccase.case_status NOT LIKE '%close%' AND `eve`.`deleted` ='N'
	AND eve.customer_id = " . $_SESSION['user_customer_id'];
	if ($assignee!="") {
		$sql .=	" AND `eve`.`assignee` LIKE '%" . $assignee . "%'";
	}
	if ($type!="") {
		$sql .=	" AND `eve`.`event_type` = '" . $type . "'";
	}
	if ($start != $end) {
		$sql .=	" AND CAST(eve.event_dateandtime AS DATE) BETWEEN :start AND :end";
	} else {
		$sql .=	" AND CAST(eve.event_dateandtime AS DATE) = :start";
	}
	//$sql .=	" AND ((`eve`.`event_type` != 'phone_call' AND `eve`.`event_type` != 'intake') OR cal.sort_order = 0)";
	//$sql .= " ORDER BY eve.event_id ASC";
	$sql .= " ORDER BY eve.event_dateandtime ASC";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("start", $start);
		if ($start != $end) {
			$stmt->bindParam("end", $end);
		}	
		$stmt->execute();
		$allcusevents = $stmt->fetchAll(PDO::FETCH_OBJ);
        echo json_encode($allcusevents);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getCustomerEventsByAssigneeByDate($assignee, $start, $end){
	session_write_close();
	$default_color = "white";
	$other_color = "black";
	if ($_SESSION['user_customer_id']==1049 || $_SESSION['user_customer_id']==1075) {
		$default_color = "black";
		$other_color = "white";
	}
    $sql = "SELECT DISTINCT `event_id` id, eve.`event_uuid`, `event_date`, `event_duration`, `event_name`, `event_dateandtime` `start`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `event_title` `title`, `event_email`, `event_hour`, `event_type`,
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, `off_calendar`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, 
	`end_date`, `end_date` `end`,  `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.case_type, ccase.file_number, ccase.case_language, ccase.case_name case_stored_name, IFNULL(ccase.attorney, '') supervising_attorney , IFNULL(venue_abbr, '') venue_abbr
			FROM `cse_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `cse_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			LEFT OUTER JOIN `cse_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN cse_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			LEFT OUTER JOIN `cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "cse_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `cse_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			LEFT OUTER JOIN cse_calendar_event cec             
			ON eve.event_uuid = cec.event_uuid             
			LEFT OUTER JOIN cse_calendar cal             
			ON cec.calendar_uuid = cal.calendar_uuid
			WHERE 1";
	$sql .=	" AND ccase.case_status NOT LIKE '%close%' AND `eve`.`deleted` ='N'
	AND eve.customer_id = " . $_SESSION['user_customer_id'];
	$sql .=	" AND `eve`.`assignee` LIKE '%" . $assignee . "%'";
	if ($start != $end) {
		$sql .=	" AND CAST(eve.event_dateandtime AS DATE) BETWEEN :start AND :end";
	} else {
		$sql .=	" AND CAST(eve.event_dateandtime AS DATE) = :start";
	}
	//$sql .=	" AND ((`eve`.`event_type` != 'phone_call' AND `eve`.`event_type` != 'intake') OR cal.sort_order = 0)";
	$sql .= " ORDER BY eve.event_id ASC";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("start", $start);
		if ($start != $end) {
			$stmt->bindParam("end", $end);
		}	
		$stmt->execute();
		$allcusevents = $stmt->fetchAll(PDO::FETCH_OBJ);
        echo json_encode($allcusevents);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getCustomerEventsByAssignee($assignee){
	session_write_close();
	$default_color = "white";
	$other_color = "black";
	if ($_SESSION['user_customer_id']==1049 || $_SESSION['user_customer_id']==1075) {
		$default_color = "black";
		$other_color = "white";
	}
    $sql = "SELECT DISTINCT `event_id` id, eve.`event_uuid`, `event_date`, `event_duration`, `event_name`, `event_dateandtime` `start`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `event_title` `title`, `event_email`, `event_hour`, `event_type`,
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, `off_calendar`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, 
	`end_date`, `end_date` `end`,  `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.case_type, ccase.file_number, ccase.case_language, ccase.case_name case_stored_name, IFNULL(ccase.attorney, '') supervising_attorney , IFNULL(venue_abbr, '') venue_abbr
			FROM `cse_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `cse_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			LEFT OUTER JOIN `cse_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN cse_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			LEFT OUTER JOIN `cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "cse_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `cse_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			LEFT OUTER JOIN cse_calendar_event cec             
			ON eve.event_uuid = cec.event_uuid             
			LEFT OUTER JOIN cse_calendar cal             
			ON cec.calendar_uuid = cal.calendar_uuid
			WHERE 1";
	$sql .=	" AND ccase.case_status NOT LIKE '%close%' AND `eve`.`deleted` ='N'
	AND eve.customer_id = " . $_SESSION['user_customer_id'];
	$sql .=	" AND `eve`.`assignee` LIKE '%" . $assignee . "%'";
	$six_months_ago = mktime(0, 0, 0, date("m") - 1,   date("d"),   date("Y"));
	$sql .=	" AND (eve.event_date > '" . date("Y-m-d", $six_months_ago) . "'
	 OR eve.`event_dateandtime` > '" . date("Y-m-d", $six_months_ago) . "')";
	//$sql .=	" AND ((`eve`.`event_type` != 'phone_call' AND `eve`.`event_type` != 'intake') OR cal.sort_order = 0)";
	$sql .= " ORDER BY eve.event_id ASC";
	//die($sql);
	try {
		$allcusevents = DB::select($sql);
        echo json_encode($allcusevents);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
} 
function getCustomerEventsByWorker($worker) {
	session_write_close();
	$default_color = "white";
	$other_color = "black";
	if ($_SESSION['user_customer_id']==1049 || $_SESSION['user_customer_id']==1075) {
		$default_color = "black";
		$other_color = "white";
	}
    $sql = "SELECT DISTINCT `event_id` id, eve.`event_uuid`, `event_date`, `event_duration`, `event_name`, `event_dateandtime` `start`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `event_title` `title`, `event_email`, `event_hour`, `event_type`,
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, `off_calendar`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, 
	`end_date`, `end_date` `end`,  `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.case_type, ccase.file_number, ccase.case_language, ccase.case_name case_stored_name, IFNULL(ccase.attorney, '') supervising_attorney , IFNULL(venue_abbr, '') venue_abbr
			FROM `cse_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `cse_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			LEFT OUTER JOIN `cse_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN cse_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			LEFT OUTER JOIN `cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "cse_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `cse_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			LEFT OUTER JOIN cse_calendar_event cec             
			ON eve.event_uuid = cec.event_uuid             
			LEFT OUTER JOIN cse_calendar cal             
			ON cec.calendar_uuid = cal.calendar_uuid
			WHERE 1";
	$sql .=	" AND ccase.case_status NOT LIKE '%close%' AND `eve`.`deleted` ='N'
	AND eve.customer_id = " . $_SESSION['user_customer_id'];
	$sql .=	" AND `ccase`.`worker` = '" . $worker . "'";
	$six_months_ago = mktime(0, 0, 0, date("m") - 1,   date("d"),   date("Y"));
	$sql .=	" AND (eve.event_date > '" . date("Y-m-d", $six_months_ago) . "'
	 OR eve.`event_dateandtime` > '" . date("Y-m-d", $six_months_ago) . "')";
	//$sql .=	" AND ((`eve`.`event_type` != 'phone_call' AND `eve`.`event_type` != 'intake') OR cal.sort_order = 0)";
	//$sql .= " ORDER BY eve.event_id ASC";
	$sql .= " ORDER BY eve.event_dateandtime ASC";
	//die($sql);
	try {
		$allcusevents = DB::select($sql);
        echo json_encode($allcusevents);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getCustomerEventsByType($type) {
	session_write_close();
	$default_color = "white";
	$other_color = "black";
	if ($_SESSION['user_customer_id']==1049 || $_SESSION['user_customer_id']==1075) {
		$default_color = "black";
		$other_color = "white";
	}
	$type = trim(str_replace("_", " ", $type));
	
    $sql = "SELECT DISTINCT `event_id` id, eve.`event_uuid`, `event_date`, `event_duration`, `event_name`, `event_dateandtime` `start`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `event_title` `title`, `event_email`, `event_hour`, `event_type`,
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, `off_calendar`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, 
	`end_date`, `end_date` `end`,  `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.case_type, ccase.file_number, ccase.case_language, ccase.case_name case_stored_name, IFNULL(ccase.attorney, '') supervising_attorney , IFNULL(venue_abbr, '') venue_abbr
			FROM `cse_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `cse_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			LEFT OUTER JOIN `cse_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN cse_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			LEFT OUTER JOIN `cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "cse_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `cse_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			LEFT OUTER JOIN cse_calendar_event cec             
			ON eve.event_uuid = cec.event_uuid             
			LEFT OUTER JOIN cse_calendar cal             
			ON cec.calendar_uuid = cal.calendar_uuid
			WHERE 1";
	$sql .=	" AND ccase.case_status NOT LIKE '%close%' AND `eve`.`deleted` ='N'
	AND eve.customer_id = " . $_SESSION['user_customer_id'];
	$sql .=	" AND `eve`.`event_type` = '" . $type . "'";
	$six_months_ago = mktime(0, 0, 0, date("m") - 1,   date("d"),   date("Y"));
	$sql .=	" AND (eve.event_date > '" . date("Y-m-d", $six_months_ago) . "'
	 OR eve.`event_dateandtime` > '" . date("Y-m-d", $six_months_ago) . "')";
	//$sql .=	" AND ((`eve`.`event_type` != 'phone_call' AND `eve`.`event_type` != 'intake') OR cal.sort_order = 0)";
	//$sql .= " ORDER BY eve.event_id ASC";
	$sql .= " ORDER BY eve.event_dateandtime ASC";
	//die($sql);
	try {
		$allcusevents = DB::select($sql);
        echo json_encode($allcusevents);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getCustomerAllEventsByDate($start, $end) {
	getCustomerEventsByDate($start, $end, true);
}
function getCustomerEventsByDate($start, $end, $blnAllEvents = false) {
	session_write_close();
	$default_color = "white";
	$other_color = "black";
	if ($_SESSION['user_customer_id']==1049 || $_SESSION['user_customer_id']==1075) {
		$default_color = "black";
		$other_color = "white";
	}
	
	$sql = "SELECT DISTINCT `event_id` id, eve.`event_uuid`, `event_date`, `event_duration`, `event_name`, `event_dateandtime` `start`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `event_title` `title`, `event_title`, `event_email`, `event_hour`, `event_type`,
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, `off_calendar`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, 
	IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, 
	`end_date`, `end_date` `end`,  `completed_date`, `callback_date`, `callback_completed` , 
	IFNULL(ccase.case_id, icase.case_id) case_id, 
	IF (ccase.case_id IS NULL, CONCAT(iapp.first_name,' ',iapp.last_name,' vs ', iemployer.`company_name`), CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`)) `case_name`, 
	IFNULL(app.phone, '') applicant_phone, IFNULL(app.work_phone, '') applicant_work, IFNULL(app.cell_phone, '') applicant_cell,
	IF (ccase.case_id IS NULL, icase.case_number, ccase.case_number) case_number, 
	IF (ccase.case_id IS NULL, icase.file_number, ccase.file_number) file_number,
	IF (ccase.case_type IS NULL, icase.case_type, ccase.case_type) case_type, 
	IF (ccase.case_id IS NULL, icase.case_name, ccase.case_name) case_stored_name,
	IFNULL(IF (ccase.case_id IS NULL, icase.case_language, ccase.case_language), '') case_language,
	IFNULL(ccase.attorney, '') supervising_attorney, 
	IFNULL(ccase.worker, '') worker, 
	cal.sort_order cal_sort_order, IFNULL(venue_abbr, '') venue_abbr
			FROM `cse_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `cse_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			LEFT OUTER JOIN `cse_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN cse_case ccase
			ON ceve.case_uuid = ccase.case_uuid AND ccase.`case_status` NOT LIKE '%close%'
			
			LEFT OUTER JOIN `cse_injury_event` cive
			ON eve.event_uuid = cive.event_uuid
			LEFT OUTER JOIN `cse_case_injury` cci
			ON cive.injury_uuid = cci.injury_uuid
			LEFT OUTER JOIN `cse_case` icase
			ON cci.case_uuid = icase.case_uuid
			
			LEFT OUTER JOIN `cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "cse_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `cse_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			
			LEFT OUTER JOIN cse_case_person icapp ON icase.case_uuid = icapp.case_uuid
			LEFT OUTER JOIN ";
			if ($_SESSION['user_customer_id']==1033) { 
				$sql .= "(" . SQL_PERSONX . ")";
			} else {
				$sql .= "cse_person";
			}
			$sql .= " iapp ON icapp.person_uuid = iapp.person_uuid
			LEFT OUTER JOIN `cse_case_corporation` iccorp
			ON (icase.case_uuid = iccorp.case_uuid AND iccorp.attribute = 'employer' AND iccorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` iemployer
			ON iccorp.corporation_uuid = iemployer.corporation_uuid
			
			LEFT OUTER JOIN cse_calendar_event cec             
			ON eve.event_uuid = cec.event_uuid             
			LEFT OUTER JOIN cse_calendar cal             
			ON cec.calendar_uuid = cal.calendar_uuid
			WHERE 1";
	//AND ccase.case_status NOT LIKE '%close%' 
	$sql .=	" AND `eve`.`deleted` ='N'
	AND eve.customer_id = " . $_SESSION['user_customer_id'];
	
	if ($start != $end) {
		$sql .=	" AND CAST(eve.event_dateandtime AS DATE) BETWEEN :start AND :end";
	} else {
		$sql .=	" AND CAST(eve.event_dateandtime AS DATE) = :start";
	}
	$sql .=	" AND eve.customer_id = " . $_SESSION['user_customer_id'];
	if (!$blnAllEvents) {
		$sql .=	" AND (`eve`.`event_type` != 'phone_call' AND `eve`.`event_type` != 'intake')";
	}
	//$sql .=	" AND ((`eve`.`event_type` != 'phone_call' AND `eve`.`event_type` != 'intake') OR cal.sort_order = 0)";
	$sql .= " ORDER BY eve.event_dateandtime ASC";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("start", $start);
		if ($start != $end) {
			$stmt->bindParam("end", $end);
		}
		$stmt->execute();
		$allcusevents = $stmt->fetchAll(PDO::FETCH_OBJ);
        echo json_encode($allcusevents);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getCustomerInhouseEvents() {
	session_write_close();
	$default_color = "white";
	$other_color = "black";
	if ($_SESSION['user_customer_id']==1049 || $_SESSION['user_customer_id']==1075) {
		$default_color = "black";
		$other_color = "white";
	}
	
    $sql = "SELECT DISTINCT `event_id` id, eve.`event_uuid`, `event_date`, `event_duration`, `event_name`, `event_dateandtime` `start`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `event_title` `title`, `event_email`, `event_hour`, `event_type`,
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, `off_calendar`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, 
	`end_date`, `end_date` `end`,  `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.case_type, ccase.file_number, ccase.case_language, ccase.case_name case_stored_name, IFNULL(ccase.attorney, '') supervising_attorney , IFNULL(venue_abbr, '') venue_abbr
			FROM `cse_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `cse_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			INNER JOIN cse_calendar_event cec             
			ON eve.event_uuid = cec.event_uuid             
			INNER JOIN cse_calendar cal             
			ON cec.calendar_uuid = cal.calendar_uuid
			LEFT OUTER JOIN `cse_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN cse_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			
			LEFT OUTER JOIN `cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
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
	AND eve.customer_id = " . $_SESSION['user_customer_id'];
	$sql .=	" AND (cal.sort_order = 1)";
	//$sql .=	" AND ((`eve`.`event_type` != 'phone_call' AND `eve`.`event_type` != 'intake') OR cal.sort_order = 0)";
	$sql .= " ORDER BY eve.event_id ASC";
	//die($sql);
	try {
		$allcusevents = DB::select($sql);
        echo json_encode($allcusevents);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getCustomerInhouseEventsByDate($start, $end) {
	session_write_close();
	$default_color = "white";
	$other_color = "black";
	if ($_SESSION['user_customer_id']==1049 || $_SESSION['user_customer_id']==1075) {
		$default_color = "black";
		$other_color = "white";
	}
	
    $sql = "SELECT DISTINCT `event_id` id, eve.`event_uuid`, `event_date`, `event_duration`, `event_name`, `event_dateandtime` `start`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `event_title` `title`, `event_email`, `event_hour`, `event_type`,
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, `off_calendar`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, 
	`end_date`, `end_date` `end`,  `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.case_type, ccase.file_number, ccase.case_language, ccase.case_name case_stored_name, IFNULL(ccase.attorney, '') supervising_attorney , IFNULL(venue_abbr, '') venue_abbr
			FROM `cse_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `cse_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			INNER JOIN cse_calendar_event cec             
			ON eve.event_uuid = cec.event_uuid             
			INNER JOIN cse_calendar cal             
			ON cec.calendar_uuid = cal.calendar_uuid
			LEFT OUTER JOIN `cse_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN cse_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			
			LEFT OUTER JOIN `cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
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
	AND CAST(eve.event_dateandtime AS DATE) BETWEEN :start AND :end
	AND eve.customer_id = " . $_SESSION['user_customer_id'];
	$sql .=	" AND (cal.sort_order = 1)";
	//$sql .=	" AND ((`eve`.`event_type` != 'phone_call' AND `eve`.`event_type` != 'intake') OR cal.sort_order = 0)";
	$sql .= " ORDER BY eve.event_dateandtime ASC";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("start", $start);
		$stmt->bindParam("end", $end);
		
		$stmt->execute();
		$allcusevents = $stmt->fetchAll(PDO::FETCH_OBJ);
        echo json_encode($allcusevents);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getCustomerIntakes() {
	session_write_close();
	$default_color = "white";
	$other_color = "black";
	if ($_SESSION['user_customer_id']==1049 || $_SESSION['user_customer_id']==1075) {
		$default_color = "black";
		$other_color = "white";
	}
	
    $sql = "SELECT DISTINCT `event_id` id, eve.`event_uuid`, `event_date`, `event_duration`, `event_name`, `event_dateandtime` `start`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `event_title` `title`, `event_email`, `event_hour`, `event_type`,
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, `off_calendar`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.case_type, ccase.file_number, ccase.case_language, ccase.case_name case_stored_name, IFNULL(ccase.attorney, '') supervising_attorney , IFNULL(venue_abbr, '') venue_abbr
			FROM `cse_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `cse_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			LEFT OUTER JOIN `cse_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN cse_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			
			LEFT OUTER JOIN `cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
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
	AND eve.customer_id = " . $_SESSION['user_customer_id'];
	$sql .=	" AND `eve`.`event_type` = 'intake' ";
	$six_months_ago = mktime(0, 0, 0, date("m") - 1,   date("d"),   date("Y"));
	$sql .=	" AND (eve.event_date > '" . date("Y-m-d", $six_months_ago) . "'
	 OR eve.`event_dateandtime` > '" . date("Y-m-d", $six_months_ago) . "')";
	$sql .= " ORDER BY eve.event_id ASC";
	//die($sql);
	try {
		$allcusevents = DB::select($sql);
        echo json_encode($allcusevents);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getCustomerIntakesByDate($start, $end) {
	session_write_close();
	$default_color = "white";
	$other_color = "black";
	if ($_SESSION['user_customer_id']==1049 || $_SESSION['user_customer_id']==1075) {
		$default_color = "black";
		$other_color = "white";
	}
	
    $sql = "SELECT DISTINCT `event_id` id, eve.`event_uuid`, `event_date`, `event_duration`, `event_name`, `event_dateandtime` `start`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `event_title` `title`, `event_email`, `event_hour`, `event_type`,
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, `off_calendar`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.case_type, ccase.file_number, ccase.case_language, ccase.case_name case_stored_name, IFNULL(ccase.attorney, '') supervising_attorney , IFNULL(venue_abbr, '') venue_abbr
			FROM `cse_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `cse_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			LEFT OUTER JOIN `cse_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN cse_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			
			LEFT OUTER JOIN `cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
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
	AND eve.customer_id = " . $_SESSION['user_customer_id'];
	$sql .=	" AND `eve`.`event_type` = 'intake' 
	AND CAST(eve.event_dateandtime AS DATE) BETWEEN :start AND :end";
	
	$sql .= " ORDER BY eve.event_dateandtime ASC";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("start", $start);
		$stmt->bindParam("end", $end);	
		$stmt->execute();
		$intakeevents = $stmt->fetchAll(PDO::FETCH_OBJ);
        echo json_encode($intakeevents);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getRemoteUpcomingEvents() {
	$_SESSION["user_id"] = passed_var("user_id", "post");
	$_SESSION["user_customer_id"] = passed_var("customer_id", "post");
	getUpcomingEvents();
}
function getUpcomingEvents() {
	session_write_close();
	$default_color = "white";
	$other_color = "black";
	if ($_SESSION['user_customer_id']==1049 || $_SESSION['user_customer_id']==1075) {
		$default_color = "black";
		$other_color = "white";
	}
	
	$two_days = mktime(0, 0, 0, date("m"),   date("d") + 2,   date("Y"));
	
	$arrDay = firstAvailableDay( date("Y-m-d", $two_days));
	$two_days = $arrDay["linux_date"];
	
	$sql = "SELECT DISTINCT cse.*, cse.`full_address` `location`, cse.`event_id` `id` , cse.`event_uuid` `uuid`, 
	IFNULL(ccase.case_id, icase.case_id) case_id, 
	IFNULL(ccase.case_uuid, icase.case_uuid) case_uuid,
	IF (ccase.case_id IS NULL, CONCAT(iapp.first_name,' ',iapp.last_name,' vs ', iemployer.`company_name`), CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`)) `case_name`, 
	IF (ccase.case_id IS NULL, icase.case_number, ccase.case_number) case_number, 
	IF (ccase.case_id IS NULL, icase.file_number, ccase.file_number) file_number,
	IF (ccase.case_type IS NULL, icase.case_type, ccase.case_type) case_type, 
	IF (ccase.case_id IS NULL, icase.case_name, ccase.case_name) case_stored_name, 
	IF (ccase.case_id IS NULL, icase.case_language, ccase.case_language) case_language, 
	IF (ccase.case_id IS NULL, IFNULL(icase.attorney, ''), IFNULL(ccase.attorney, '')) supervising_attorney, 
	IFNULL(venue_abbr, '') venue_abbr
	FROM `cse_event` cse  
	
	LEFT OUTER JOIN `cse_case_event` ceve
	ON cse.event_uuid = ceve.event_uuid
	LEFT OUTER JOIN cse_case ccase
	ON ceve.case_uuid = ccase.case_uuid
	
	LEFT OUTER JOIN `cse_injury_event` cive
	ON cse.event_uuid = cive.event_uuid
	LEFT OUTER JOIN `cse_case_injury` cci
	ON cive.injury_uuid = cci.injury_uuid
	LEFT OUTER JOIN `cse_case` icase
	ON cci.case_uuid = icase.case_uuid
	
	LEFT OUTER JOIN `cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
	LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
	LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "cse_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
	LEFT OUTER JOIN `cse_case_corporation` ccorp
	ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
	LEFT OUTER JOIN `cse_corporation` employer
	ON ccorp.corporation_uuid = employer.corporation_uuid
	
	LEFT OUTER JOIN cse_case_person icapp ON icase.case_uuid = icapp.case_uuid
	LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "cse_person";
	}
	$sql .= " iapp ON icapp.person_uuid = iapp.person_uuid
	LEFT OUTER JOIN `cse_case_corporation` iccorp
	ON (icase.case_uuid = iccorp.case_uuid AND iccorp.attribute = 'employer' AND iccorp.deleted = 'N')
	LEFT OUTER JOIN `cse_corporation` iemployer
	ON iccorp.corporation_uuid = iemployer.corporation_uuid
	
	WHERE 1
	AND cse.event_type != 'phone_call' 
	AND cse.event_type != 'intake' 
	AND cse.event_type != 'Employee Attendance'
	AND cse.event_type != 'Partner Calendar' 
	AND CAST( cse.event_dateandtime AS DATE ) >=  '" . date("Y-m-d") . "'
	AND CAST( cse.event_dateandtime AS DATE ) <=  '" . $two_days . "'";
	$sql .=	" AND `cse`.`deleted` ='N'
	AND cse.customer_id = " . $_SESSION['user_customer_id'];
	$sql .= " ORDER BY cse.event_dateandtime ASC";
	//AND (cse.assignee LIKE '%" . $_SESSION['user_nickname'] . "%')
	//cse.event_from =  '" . addslashes($_SESSION['user_name']) . "' OR 
	if ($_SERVER['REMOTE_ADDR']=='47.153.51.181') {	
	//die($sql);
	}
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		
		$stmt->execute();
		$events = $stmt->fetchAll(PDO::FETCH_OBJ);
        echo json_encode($events);

	} catch(PDOException $e) {
		die($sql);
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKaseUpcomingEvents($case_id) {
	session_write_close();
	
	$sql = "SELECT cse . *, cse.`event_id` `id` , cse.`event_uuid` `uuid`, 
	ccase.case_id, ccase.case_uuid
	FROM  `cse_event` cse ON ( csu.event_uuid = cse.event_uuid
	AND csu.type =  'to' ) 
	INNER JOIN `cse_case_event` ceve
	ON cse.event_uuid = ceve.event_uuid
	INNER JOIN cse_case ccase
	ON ceve.case_uuid = ccase.case_uuid
	
	LEFT OUTER JOIN `cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
	WHERE 1
	AND cse.event_type != 'phone_call' 
	AND CAST( cse.event_dateandtime AS DATE ) >=  '" . date("Y-m-d") . "'";
	$sql .=	" AND `cse`.`deleted` ='N'
	AND cse.customer_id = :case_id
	AND cse.customer_id = " . $_SESSION['user_customer_id'];
	$sql .= " ORDER BY cse.event_dateandtime ASC";

	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		
		$stmt->execute();
		$events = $stmt->fetchAll(PDO::FETCH_OBJ);
        echo json_encode($events);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getCalendarEvents($calendar_id) {
	session_write_close();
	$default_color = "white";
	$other_color = "black";
	if ($_SESSION['user_customer_id']==1049 || $_SESSION['user_customer_id']==1075) {
		$default_color = "black";
		$other_color = "white";
	}
	
	$sql = "SELECT DISTINCT `event_id` id, eve.`event_uuid`, `event_date`, `event_duration`, `event_name`, `event_dateandtime` `start`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `event_title` `title`, `event_email`, `event_hour`, `event_type`,
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, `off_calendar`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.case_type, ccase.file_number, ccase.case_language, ccase.case_name case_stored_name, IFNULL(ccase.attorney, '') supervising_attorney , IFNULL(venue_abbr, '') venue_abbr
			FROM `cse_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `cse_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			INNER JOIN `cse_calendar_event` calev
			ON eve.event_uuid = calev.event_uuid
			INNER JOIN `cse_calendar` cal
			ON calev.calendar_uuid = cal.calendar_uuid
			LEFT OUTER JOIN `cse_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN cse_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			
			LEFT OUTER JOIN `cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
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
	AND eve.customer_id = " . $_SESSION['user_customer_id'];
	$sql .=	" AND `cal`.`calendar_id` = " . $calendar_id;
	$sql .= " ORDER BY eve.event_id ASC";
	//die($sql);
	try {
		$calevents = DB::select($sql);
        echo json_encode($calevents);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getPartnerEvents() {
	session_write_close();
	
	$default_color = "white";
	$other_color = "black";
	if ($_SESSION['user_customer_id']==1049 || $_SESSION['user_customer_id']==1075) {
		$default_color = "black";
		$other_color = "white";
	}
	
	$sql = "SELECT DISTINCT `event_id` id, eve.`event_uuid`, `event_date`, `event_duration`, `event_name`, `event_dateandtime` `start`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `event_title` `title`, `event_email`, `event_hour`, `event_type`,
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, `off_calendar`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.case_type, ccase.file_number, ccase.case_language, ccase.case_name case_stored_name, IFNULL(ccase.attorney, '') supervising_attorney , IFNULL(venue_abbr, '') venue_abbr
			FROM `cse_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `cse_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			INNER JOIN `cse_calendar_event` calev
			ON (eve.event_uuid = calev.event_uuid)
			INNER JOIN `cse_calendar` cal
			ON (calev.calendar_uuid = cal.calendar_uuid)
			LEFT OUTER JOIN `cse_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN cse_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			
			LEFT OUTER JOIN `cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
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
	AND eve.customer_id = " . $_SESSION['user_customer_id'];
	$sql .=	" AND `eve`.`event_type` = 'Partner Calendar'";
	
	$six_months_ago = mktime(0, 0, 0, date("m") - 12,   date("d"),   date("Y"));
	$sql .=	" AND (eve.event_date > '" . date("Y-m-d", $six_months_ago) . "'
	 OR eve.`event_dateandtime` > '" . date("Y-m-d", $six_months_ago) . "')";
	 
	//die($sql);
	try {
		$calevents = DB::select($sql);
        echo json_encode($calevents);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function getPartnerEventsByDate($start, $end) {
	session_write_close();
	
	$default_color = "white";
	$other_color = "black";
	if ($_SESSION['user_customer_id']==1049 || $_SESSION['user_customer_id']==1075) {
		$default_color = "black";
		$other_color = "white";
	}
	
	$sql = "SELECT DISTINCT `event_id` id, eve.`event_uuid`, `event_date`, `event_duration`, `event_name`, `event_dateandtime` `start`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `event_title` `title`, `event_email`, `event_hour`, `event_type`,
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, `off_calendar`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.case_type, ccase.file_number, ccase.case_language, ccase.case_name case_stored_name, IFNULL(ccase.attorney, '') supervising_attorney , IFNULL(venue_abbr, '') venue_abbr
			FROM `cse_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `cse_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			INNER JOIN `cse_calendar_event` calev
			ON (eve.event_uuid = calev.event_uuid)
			INNER JOIN `cse_calendar` cal
			ON (calev.calendar_uuid = cal.calendar_uuid)
			LEFT OUTER JOIN `cse_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN cse_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			
			LEFT OUTER JOIN `cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
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
	AND eve.customer_id = " . $_SESSION['user_customer_id'];
	$sql .=	" AND `eve`.`event_type` = 'Partner Calendar'";
	
	if ($start != $end) {
		$sql .=	" AND CAST(eve.event_dateandtime AS DATE) BETWEEN :start AND :end";
	} else {
		$sql .=	" AND CAST(eve.event_dateandtime AS DATE) = :start";
	}
	
	$six_months_ago = mktime(0, 0, 0, date("m") - 2,   date("d"),   date("Y"));
	$sql .=	" AND (eve.event_date > '" . date("Y-m-d", $six_months_ago) . "'
	 OR eve.`event_dateandtime` > '" . date("Y-m-d", $six_months_ago) . "')";
	 
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("start", $start);
		if ($start != $end) {
			$stmt->bindParam("end", $end);
		}	
		$stmt->execute();
		$calevents = $stmt->fetchAll(PDO::FETCH_OBJ);
        echo json_encode($calevents);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function getEmployeeEvents() {
	session_write_close();
	
	$default_color = "white";
	$other_color = "black";
	if ($_SESSION['user_customer_id']==1049 || $_SESSION['user_customer_id']==1075) {
		$default_color = "black";
		$other_color = "white";
	}
	
	$sql = "SELECT DISTINCT `event_id` id, eve.`event_uuid`, `event_date`, `event_duration`, `event_name`, `event_dateandtime` `start`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `event_title` `title`, `event_email`, `event_hour`, `event_type`,
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, `off_calendar`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.case_type, ccase.file_number, ccase.case_language, ccase.case_name case_stored_name, IFNULL(ccase.attorney, '') supervising_attorney , IFNULL(venue_abbr, '') venue_abbr
			FROM `cse_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `cse_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			INNER JOIN `cse_calendar_event` calev
			ON (eve.event_uuid = calev.event_uuid)
			INNER JOIN `cse_calendar` cal
			ON (calev.calendar_uuid = cal.calendar_uuid)
			LEFT OUTER JOIN `cse_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN cse_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			
			LEFT OUTER JOIN `cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
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
	AND eve.customer_id = " . $_SESSION['user_customer_id'];
	$sql .=	" AND `eve`.`event_type` = 'Employee Attendance'";
	
	$six_months_ago = mktime(0, 0, 0, date("m") - 2,   date("d"),   date("Y"));
	$sql .=	" AND (eve.event_date > '" . date("Y-m-d", $six_months_ago) . "'
	 OR eve.`event_dateandtime` > '" . date("Y-m-d", $six_months_ago) . "')";
	 
	//die($sql);
	try {
		$calevents = DB::select($sql);
        echo json_encode($calevents);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function getEmployeeEventsByDate($start, $end) {
	session_write_close();
	
	$default_color = "white";
	$other_color = "black";
	if ($_SESSION['user_customer_id']==1049 || $_SESSION['user_customer_id']==1075) {
		$default_color = "black";
		$other_color = "white";
	}
	
	$sql = "SELECT DISTINCT `event_id` id, eve.`event_uuid`, `event_date`, `event_duration`, `event_name`, `event_dateandtime` `start`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `event_title` `title`, `event_email`, `event_hour`, `event_type`,
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, `off_calendar`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.case_type, ccase.file_number, ccase.case_language, ccase.case_name case_stored_name, IFNULL(ccase.attorney, '') supervising_attorney , IFNULL(venue_abbr, '') venue_abbr
			FROM `cse_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `cse_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			INNER JOIN `cse_calendar_event` calev
			ON (eve.event_uuid = calev.event_uuid)
			INNER JOIN `cse_calendar` cal
			ON (calev.calendar_uuid = cal.calendar_uuid)
			LEFT OUTER JOIN `cse_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN cse_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			
			LEFT OUTER JOIN `cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
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
	AND eve.customer_id = " . $_SESSION['user_customer_id'];
	$sql .=	" AND `eve`.`event_type` = 'Employee Attendance'";
	
	if ($start != $end) {
		$sql .=	" AND CAST(eve.event_dateandtime AS DATE) BETWEEN :start AND :end";
	} else {
		$sql .=	" AND CAST(eve.event_dateandtime AS DATE) = :start";
	}
	
	$six_months_ago = mktime(0, 0, 0, date("m") - 12,   date("d"),   date("Y"));
	$sql .=	" AND (eve.event_date > '" . date("Y-m-d", $six_months_ago) . "'
	 OR eve.`event_dateandtime` > '" . date("Y-m-d", $six_months_ago) . "')";
	 
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("start", $start);
		if ($start != $end) {
			$stmt->bindParam("end", $end);
		}	
		$stmt->execute();
		$calevents = $stmt->fetchAll(PDO::FETCH_OBJ);
        echo json_encode($calevents);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getUserEventsByDate($user_id, $start_date, $end_date) {
	getUserEvents($user_id, $start_date, $end_date);
}
function getUserEvents($user_id, $start_date = "", $end_date = "") {
	$user = getUserInfo($user_id);
	if (!is_object($user)) {
		return false;
	}
	session_write_close();
	$default_color = "white";
	$other_color = "black";
	if ($_SESSION['user_customer_id']==1049 || $_SESSION['user_customer_id']==1075) {
		$default_color = "black";
		$other_color = "white";
	}
	
	$sql = "SELECT DISTINCT `event_id` id, eve.`event_uuid`, `event_date`, `event_duration`, `event_name`, `event_dateandtime` `start`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `event_title` `title`, `event_email`, `event_hour`, `event_type`,
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, `off_calendar`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.case_type, ccase.file_number, ccase.case_language, ccase.case_name case_stored_name, IFNULL(ccase.attorney, '') supervising_attorney , IFNULL(venue_abbr, '') venue_abbr
			FROM `cse_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `cse_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			INNER JOIN `cse_calendar_event` calev
			ON (eve.event_uuid = calev.event_uuid AND calev.user_uuid = '" . $user->uuid . "')
			INNER JOIN `cse_calendar` cal
			ON (calev.calendar_uuid = cal.calendar_uuid AND cal.sort_order = 5)
			LEFT OUTER JOIN `cse_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN cse_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			
			LEFT OUTER JOIN `cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
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
	AND eve.customer_id = " . $_SESSION['user_customer_id'];
	$sql .=	" AND ((`eve`.`event_type` != 'phone_call' AND `eve`.`event_type` != 'intake' AND cal.sort_order = 5)";
	$sql .=	")";	//employee calendar
	
	if ($start_date!="") {
		$sql .=	" AND CAST(eve.event_dateandtime AS DATE) BETWEEN :start AND :end";
	}
	$sql .= " UNION
	SELECT DISTINCT `event_id` id, eve.`event_uuid`, `event_date`, `event_duration`, `event_name`, `event_dateandtime` `start`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `event_title` `title`, `event_email`, `event_hour`, `event_type`,
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, `off_calendar`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.case_type, ccase.file_number, ccase.case_language, ccase.case_name case_stored_name, IFNULL(ccase.attorney, '') supervising_attorney , IFNULL(venue_abbr, '') venue_abbr
			FROM `cse_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `cse_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			LEFT OUTER JOIN `cse_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN cse_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			
			LEFT OUTER JOIN `cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
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
	AND eve.customer_id = " . $_SESSION['user_customer_id'];
	$sql .= " AND (eve.event_from = '" . addslashes($_SESSION['user_name']) . "'";
	$sql .= " OR eve.assignee LIKE '%" . $_SESSION['user_nickname'] . "%')";
	
	if ($start_date!="") {
		$sql .=	" AND CAST(eve.event_dateandtime AS DATE) BETWEEN :start AND :end";
	}
	/*
	event_from==login_username
	customer_event.assignee.indexOf(login_nickname) > -1
	*/
	$sql .= " ORDER BY id ASC";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($start_date!="") {
			$stmt->bindParam("start", $start_date);
			$stmt->bindParam("end", $end_date);
		}
		$stmt->execute();
		$calevents = $stmt->fetchAll(PDO::FETCH_OBJ);
        echo json_encode($calevents);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getAllKaseEvents($case_id) {
	session_write_close();
	
	$customer_id = $_SESSION['user_customer_id'];
	$today = date("Y-m-d");
	$sql = "SELECT cse . *, cse.`event_id` `id` , cse.`event_uuid` `uuid`, 
	ccase.case_id, ccase.case_uuid, IFNULL(ccase.attorney, '') supervising_attorney, IFNULL(venue_abbr, '') venue_abbr
	FROM  `cse_event` cse 
	LEFT OUTER JOIN `cse_case_event` ceve
	ON cse.event_uuid = ceve.event_uuid
	LEFT OUTER JOIN cse_case ccase
	ON ceve.case_uuid = ccase.case_uuid
	
	LEFT OUTER JOIN `cse_case_venue` cvenue
	ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
	LEFT OUTER JOIN `cse_venue` venue
	ON cvenue.venue_uuid = venue.venue_uuid
	
	WHERE 1 ";
	$sql .=	" 
	AND `cse`.`deleted` ='N'
	AND cse.event_type != 'phone_call'
	AND CAST(cse.event_dateandtime AS DATE) >= :today
	AND cse.customer_id = :customer_id";
	$sql .= " 
	AND ccase.case_id = :case_id";
	$sql .= " 
	ORDER BY cse.event_dateandtime ASC";
	//cse.event_from =  '" . addslashes($_SESSION['user_name']) . "' OR 
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("today", $today);
		$stmt->bindParam("customer_id", $customer_id);
		
		$stmt->execute();
		$all_events = $stmt->fetchAll(PDO::FETCH_OBJ);
        echo json_encode($all_events);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getFutureKaseEvents($case_id) {
	session_write_close();
	
	$sql = "SELECT cse . *, cse.`event_id` `id` , cse.`event_uuid` `uuid`, 
	ccase.case_id, ccase.case_uuid
	FROM  `cse_event` cse 
	LEFT OUTER JOIN `cse_case_event` ceve
	ON cse.event_uuid = ceve.event_uuid
	LEFT OUTER JOIN cse_case ccase
	ON ceve.case_uuid = ccase.case_uuid
	
	LEFT OUTER JOIN `cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
	WHERE 1 
	AND CAST( cse.event_dateandtime AS DATE ) >=  '" . date("Y-m-d") . "'";
	$sql .=	" AND `cse`.`deleted` ='N'
	AND cse.customer_id = " . $_SESSION['user_customer_id'];
	$sql .= " AND ccase.case_id = :case_id";
	$sql .= " ORDER BY cse.event_date ASC";
	//cse.event_from =  '" . addslashes($_SESSION['user_name']) . "' OR 
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		
		$stmt->execute();
		$all_events = $stmt->fetchAll(PDO::FETCH_OBJ);
        echo json_encode($all_events);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getEventOld($id) {
	//OBSOLETE, USED BEFORE REMINDERS IFRAME
	session_write_close();
	$default_color = "white";
	$other_color = "black";
	if ($_SESSION['user_customer_id']==1049 || $_SESSION['user_customer_id']==1075) {
		$default_color = "black";
		$other_color = "white";
	}
	
	//get the event and the reminders too    
	$sql = "SELECT `event_id`, ev.`event_uuid`, `event_name`, `event_date`, `event_duration`, 
	`event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`,
	 `event_end_time`, `full_address`, `full_address` `location`, `judge`, `assignee`, 
	 `event_title` `title`, `event_title`, `event_email`, `event_hour`, `event_type`,
	`event_type_abbr`, `event_from`, `event_priority`, `end_date`, `completed_date`, 
	`callback_date`, `callback_completed`, `color`, `off_calendar`, ev.`customer_id`, ev.`deleted`,  
	ev.`event_id` `id`, ev.`event_uuid` `uuid`, cse.case_id,
	
	IFNULL(cr1.reminder_id, '-1') reminder_id1,
    cr1.`reminder_uuid` reminder_uuid1, cr1.`reminder_type` reminder_type1, cr1.`reminder_interval` reminder_interval1, 
    cr1.`reminder_span` reminder_span1, cr1.`reminder_datetime` reminder_datetime1, cr1.`buffered` buffered1,
	
	IFNULL(cr2.reminder_id, '-1') reminder_id2,
    cr2.`reminder_uuid` reminder_uuid2, cr2.`reminder_type` reminder_type2, cr2.`reminder_interval` reminder_interval2, 
    cr2.`reminder_span` reminder_span2, cr2.`reminder_datetime` reminder_datetime2, cr2.`buffered` buffered2
	
	FROM `cse_event` ev
	
	LEFT OUTER JOIN cse_event_reminder cer1
	ON ev.event_uuid = cer1.event_uuid AND cer1.attribute_1 = 1 AND cer1.deleted = 'N'
	LEFT OUTER JOIN cse_reminder cr1
	ON cer1.reminder_uuid = cr1.reminder_uuid AND cr1.deleted = 'N' AND cr1.reminder_number = 1
	
	LEFT OUTER JOIN cse_event_reminder cer2
	ON ev.event_uuid = cer2.event_uuid AND cer2.attribute_1 = 2 AND cer2.deleted = 'N'
	LEFT OUTER JOIN cse_reminder cr2
	ON cer2.reminder_uuid = cr2.reminder_uuid AND cr2.deleted = 'N' AND cr2.reminder_number = 2
	
	LEFT OUTER JOIN cse_case_event ccm
	ON ev.event_uuid = ccm.event_uuid
	LEFT OUTER JOIN cse_case cse
	ON ccm.case_uuid = cse.case_uuid
	
	WHERE event_id= :id
	AND ev.customer_id = " . $_SESSION['user_customer_id'] . "
	AND ev.deleted = 'N'";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$event = $stmt->fetchObject();

        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($event);
        } else {
            echo $_GET['callback'] . '(' . json_encode($event) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getEvent($id) {
	session_write_close();
	$default_color = "white";
	$other_color = "black";
	if ($_SESSION['user_customer_id']==1049 || $_SESSION['user_customer_id']==1075) {
		$default_color = "black";
		$other_color = "white";
	}
	
	//get the event and the reminders too    
	$sql = "SELECT ev.`event_id`, ev.`event_uuid`, `event_name`, `event_date`, `event_duration`, 
	`event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`,
	 `event_end_time`, `full_address`, `full_address` `location`, `judge`, `assignee`, 
	 `event_title` `title`, `event_title`, `event_email`, `event_hour`, `event_type`,
	`event_type_abbr`, `event_from`, `event_priority`, `end_date`, `completed_date`, 
	`callback_date`, `callback_completed`, `color`, `off_calendar`, ev.`customer_id`, ev.`deleted`,  
	ev.`event_id` `id`, ev.`event_uuid` `uuid`, cse.case_id,
	IFNULL(reminder_count, 0) reminder_count, '-1' reminder_id1, '-1' reminder_id2
	FROM `cse_event` ev
	
	LEFT OUTER JOIN (
		SELECT event_id, COUNT(reminder_id) reminder_count
		FROM cse_reminder rem
		INNER JOIN cse_event_reminder er
		ON rem.reminder_uuid = er.reminder_uuid
		INNER JOIN cse_event eve
		ON er.event_uuid = eve.event_uuid
		WHERE rem.deleted = 'N'
		GROUP BY event_id
	) reminders
	ON ev.event_id = reminders.event_id

	LEFT OUTER JOIN cse_case_event ccm
	ON ev.event_uuid = ccm.event_uuid
	LEFT OUTER JOIN cse_case cse
	ON ccm.case_uuid = cse.case_uuid
	
	WHERE ev.event_id= :id
	AND ev.customer_id = " . $_SESSION['user_customer_id'] . "
	AND ev.deleted = 'N'";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$event = $stmt->fetchObject();

        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($event);
        } else {
            echo $_GET['callback'] . '(' . json_encode($event) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getEventInfo($id) {
	session_write_close();
	$default_color = "white";
	$other_color = "black";
	if ($_SESSION['user_customer_id']==1049 || $_SESSION['user_customer_id']==1075) {
		$default_color = "black";
		$other_color = "white";
	}
	
    $sql = "SELECT `event_id`, `event_uuid`, `event_name`, `event_date`, `event_duration`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `full_address`, `full_address` `location`, `judge`, `assignee`, `event_title` `title`, `event_title`, `event_email`, `event_hour`, `event_type`,
    `event_type_abbr`, `event_from`, `event_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed`, `color`, `off_calendar`, `customer_id`, `deleted`,  `event_id` `id`, `event_uuid` `uuid` 
			FROM `cse_event`
			WHERE event_id=:id
			AND customer_id = " . $_SESSION['user_customer_id'];
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$event = $stmt->fetchObject();

        // Include support for JSONP requests
        return $event;

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function deleteEvent() {
	$id = passed_var("id", "post");
	$sql = "UPDATE cse_event 
			SET deleted = 'Y'
			WHERE event_id=:id
			AND customer_id = " . $_SESSION['user_customer_id'];
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		
		echo json_encode(array("success"=>"event marked as deleted"));
		
		trackEvent("delete", $id);
		
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function addEvent() {
	session_write_close();
	$db = getConnection();
	// die(print_r($_POST));
	$arrFields = array();
	$arrSet = array();
	$table_name = "";
	$injury_id = "";
	$injury_uuid = "";
	$case_id = "";
	$event_kind = "";
	$calendar_id = -1;
	$event_calendar_sort_order = 0;	//default calendar
	$arrTo = array();
	$arrToID = array();
	$event_dateandtime = passed_var("event_dateandtime", "post");
	$duration = passed_var("event_duration", "post");
	$end_date = "";
	$attorney_color = "";
	$user_uuid = "";	//for personal calendars
	if ($duration != "") {
		//there is no end date box on the calendar, so we calculate from duration
		$end_date = DateAdd("n", $duration, strtotime($event_dateandtime));
		$end_date = date("Y-m-d H:i:s", $end_date);
	}
	$reminder_set = "";
	
	
	foreach($_POST as $fieldname=>$value) {
		//no reminders, no recurrence here, not yet
		//echo $fieldname . "\r\n";
		if ((strpos($fieldname, "reminder_") > -1 || strpos($fieldname, "recurrent_") > -1)  && $fieldname!="reminder_set" ) {
			continue;
		}
		if ($fieldname=="reminder_set") {
			$reminder_set = $value;
			if ($reminder_set=="undefined") {
				$reminder_set = "";
			}
			if ($reminder_set!="") {
				$reminder_set = json_decode($reminder_set);
			}
			continue;
		}
		if ($fieldname!="event_descriptionInput") {
			$value = passed_var($fieldname, "post");
		} else {
			$value = @processHTML($_POST["event_descriptionInput"]);
		}
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="calendar_drop_down") {
			//$table_name = $value;
			continue;
		}
		if ($fieldname=="billing_time") {
			continue;
		}
		if ($fieldname=="billing_time_dropdown") {
			continue;
		}
		if ($fieldname=="user_id") {
			if ($value!="") {
				//look up the user_uuid
				$user = getUserInfo($value);
				
				if (is_object($user)) {
					$user_uuid = $user->uuid;
				}
			}
			continue;
		}
		if ($fieldname=="case_id") {
			$case_id = $value;
			if ($case_id==-1) {
				$case_id = "";
			}
			continue;
		}
		if ($fieldname=="event_kind") {
			$event_kind = $value;
			continue;
		}
		if ($fieldname=="injury_id") {
			$injury_id = $value;
			if ($injury_id=="undefined") {
				$injury_id = "";
			}
			if($injury_id!="") {
				$injury = getInjuryInfo($injury_id);
				$injury_uuid = $injury->uuid;
			}
			continue;
		}
		if ($fieldname=="case_uuid" || $fieldname=="table_id" || $fieldname=="table_uuid" || $fieldname=="injury_uuid" || $fieldname=="send_document_id" || $fieldname=="street" || $fieldname=="city" || $fieldname=="state" || $fieldname=="zip" || $fieldname=="event_partie") {
			continue;
		}
		if ($fieldname=="start_date") {
			if ($value!="") {
				$value = date("Y-m-d", strtotime($value));
			} else {
				$value = "0000-00-00";
			}
		}
		if ($fieldname=="calendar_id") {
			$calendar_id = $value;
			continue;
		}
		if ($fieldname=="end_date") {
			if ($end_date=="") {
				if ($value!="") {
					$value = date("Y-m-d", strtotime($value));
				} else {
					$value = "0000-00-00";
				}
				$end_date = $value;
			}
			continue;
		}
		if ($fieldname=="event_dateandtime" || $fieldname=="callback_date") {
			if ($value!="") {
				$value = date("Y-m-d H:i:s", strtotime($value));
			} else {
				$value = "0000-00-00 00:00:00";
			}
		}
		if ($fieldname=="assignee") {
			explodeRecipient($value, $arrTo, $arrToID, $db);
			$to = implode(";", $arrTo);
			$value = $to;
		}
		if ($fieldname=="off_calendar") {
			if ($value=="" || $value!="Y"){
				$value = "N";
			}
		}
		$db = getConnection();
		
		if ($fieldname=="number_of_days") {
			continue;
		}
		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "'" . addslashes($value) . "'";
	}
	
	if ($case_id!="") {
		$kase = getKaseInfo($case_id);
		
		$arrFields[] = "`event_first_name`";
		$arrSet[] = "'" . addslashes($kase->first_name) . "'";
		
		$arrFields[] = "`event_last_name`";
		$arrSet[] = "'" . addslashes($kase->last_name) . "'";
		
		//for mahoney for now
		if ($_SESSION['user_customer_id']=="1088") {	
			if ($kase->supervising_attorney!="" || $kase->attorney!="") {
				$case_attorney = $kase->supervising_attorney;
				if ($case_attorney=="") {
					$case_attorney = $kase->attorney;
				}
				
				if (is_numeric($case_attorney)) {
					$attorney = getUserInfo($case_attorney);
				} else {
					$attorney = getUserByNickname($case_attorney);
				}
				//die(print_r($attorney));		
				$attorney_color = $attorney->calendar_color;
				$arrFields[] = "`color`";
				$arrSet[] = "'" . $attorney_color . "'";
			}
		}
	}
	
	if ($fieldname=="event_type") {
		$color = "blue";
		$event_type_abbr = $value;
		
		$sql = "SELECT setting_value, default_value
		FROM cse_setting
		where category = 'calendar_type'
		AND setting = '" . $value . "'
		AND customer_id = " . $_SESSION['user_customer_id'];
		
		try {
			$stmt = DB::run($sql);
			$calendar_setting = $stmt->fetchObject();
			if (count($calendar_setting) > 0  && is_object($calendar_setting)) {
				$color = $calendar_setting->default_value;
				$event_type_abbr = $calendar_setting->setting_value;
			}
		} catch(PDOException $e) {
			$error = array("error"=> array("text"=>$e->getMessage()));
			echo json_encode($error);
		}
		
		//color might be based on attorney
		if ($attorney_color=="") {
			$arrFields[] = "`color`";
			$arrSet[] = "'" . $color . "'";
		}
		
		$arrFields[] = "`event_type_abbr`";
		$arrSet[] = "'" . addslashes($event_type_abbr) . "'";			
	}
	$arrFields[] = "`customer_id`";
	$arrSet[] = $_SESSION['user_customer_id'];
	
	if ($end_date!="") {
		$arrFields[] = "`end_date`";
		$arrSet[] = "'" . $end_date . "'";
	}
	
	//calendar
	if ($calendar_id < 0) {
		//intake calendar
		if ($event_kind=="phone_call") {
			//hard coded 0, ALREADY DEFAULT DON'T REALLY NEED NEXT LINE...
			$event_calendar_sort_order = 0;
		}
		if ($event_kind=="intake") {
			//hard coded 4
			$event_calendar_sort_order = 4;
		}
		//get the id based on the sort order
		$customer_calendar = getCalendarBySortOrder($event_calendar_sort_order);
		if (is_object($customer_calendar)) {
			$calendar_id = $customer_calendar->calendar_id;
		}
	}
	
	
	$table_uuid = uniqid("KS", false);	
	$last_updated_date = date("Y-m-d H:i:s");
	//reminders
	if (isset($_POST["reminder_set"]) && $reminder_set!="") {
		if (is_array($reminder_set)) {
			// die(print_r($reminder_set));
			foreach($reminder_set as $key=>$item){
				$message_object = getMessageInfo($item->message_id);
				// die(print_r($message_object));
				$message_uuid = $message_object->message_uuid;
				$sender_uuid = $_SESSION['user_id'];
				
				$reminder_uuid = uniqid("RM", false);   
				$reminder_message_uuid = uniqid("RM", false);
				$case_table_uuid = uniqid("ER", false);
				
				$strSQL = "INSERT INTO cse_reminder 
						(`reminder_uuid`, `reminder_number`, `reminder_type`, `reminder_interval`, `reminder_span`, `reminder_datetime`, `buffered`, `customer_id`) 
						VALUES ('" . $reminder_uuid . "', '" . $item->reminder_number . "', '" . $item->reminder_type . "', " . $item->reminder_interval . ", '" . $item->reminder_span . "', '" . $item->reminder_datetime . "', 'N', '" . $_SESSION['user_customer_id'] . "')";
	
	
				$query = "INSERT INTO cse_reminder_message 
						(`reminder_message_uuid`, `reminder_uuid`, `message_uuid`, `attribute`, `last_update_user`, `customer_id`)
						VALUES ('" . $reminder_message_uuid . "', '" . $reminder_uuid . "', '" . $message_uuid . "', 'main', '" . $sender_uuid . "', '" . $_SESSION['user_customer_id'] . "')";
	
				//attach each one to the event
				$sql = "INSERT INTO cse_event_reminder 
					 (`event_reminder_uuid`, `event_uuid`, `reminder_uuid`, `attribute_1`, `last_updated_date`, `last_update_user`, `customer_id`)
					 VALUES ('" . $case_table_uuid  ."', '" . $table_uuid . "', '" . $reminder_uuid . "', '" . $item->reminder_number . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
				//echo $sql . "<br />";
	
				// cse_reminder						
				DB::run($strSQL);
	$reminder_id = DB::lastInsertId();
				
				// cse_reminder_message
				$stmt = DB::run($query);
	
				// cse_event_reminder
				$stmt = DB::run($sql);
			}
		}
	}
	/*
	if ($_SERVER['REMOTE_ADDR']=='71.106.134.58') {	
		//reminders
		$arrReminder = array();
		foreach($_POST as $fieldname=>$value) {
			//no reminders, no recurrence here, not yet
			if (strpos($fieldname, "reminder_") === false) {
				continue;
			}
			if (strpos($fieldname, "reminder_id") !== false) {
				//no  id for insert
				continue;
			}
			
			$reminder_number = substr($fieldname, strlen($fieldname) -1, 1);
			if (!is_numeric($reminder_number)) {
				continue;
			}
			$value = passed_var($fieldname, "post");
			$arrReminder[$reminder_number][substr($fieldname, 0, strlen($fieldname) -1)] = "'" . addslashes($value) . "'";
		}
		//die(print_r($arrReminder));
		foreach($arrReminder as $reminder_number=>$set) {
			$fields = "`reminder_uuid`";
			$reminder_uuid = uniqid("RM", false);
			$values = "'" . $reminder_uuid . "'";
			$reminder_interval = "";
			$reminder_span = "";
			//die(print_r($set));
			foreach($set as $field_name=>$value) {
				$fields .= ", `" . $field_name . "`";
				if ($field_name=="reminder_type") {
					if ($value=="") {
						$value = "''";
					}
				}
				if ($field_name=="reminder_interval") {
					if ($value=="" || $value=="''") {
						$value = "0";
					}
					$reminder_interval = str_replace("'", "", $value);
					$value = $reminder_interval;
				}
				if ($field_name=="reminder_span") {
					$reminder_span = str_replace("'", "", $value);
					//$value = $reminder_span;
				}
				
				$values .= ", " . $value;
			}
			//attach the reminder_number
			$fields .=  ", `reminder_number`";
			$values .=  ", " . $reminder_number;
			if ($reminder_interval > 0  && $reminder_span!="") {
				//calculate the reminder date
				//echo $event_dateandtime . " -> " . $reminder_interval . " -- " . $reminder_span . "\r\n";
				$reminder_datetime = date("Y-m-d H:i:s", strtotime($event_dateandtime . " - " . $reminder_interval . " " . $reminder_span));
				$fields .=  ", `reminder_datetime`, `customer_id`";
				$values .=  ", '" . $reminder_datetime . "', '" . $_SESSION['user_customer_id'] . "'";
				
				//insert the reminders
				$sql = "INSERT cse_reminder (" . $fields . ") VALUES(" . $values . ")";
				//echo $sql . "<br />";
				$stmt = DB::run($sql);
			}

			$case_table_uuid = uniqid("ER", false);
			//attach each one to the event
			$sql = "INSERT INTO cse_event_reminder (`event_reminder_uuid`, `event_uuid`, `reminder_uuid`, `attribute_1`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_table_uuid  ."', '" . $table_uuid . "', '" . $reminder_uuid . "', '" . $reminder_number . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			//echo $sql . "<br />";
			$stmt = DB::run($sql);
		}
	}
	*/
	$sql = "INSERT INTO `cse_" . $table_name ."` (`" . $table_name . "_uuid`, " . implode(",", $arrFields) . ") 
			VALUES('" . $table_uuid . "', " . implode(",", $arrSet) . ")";
	
	try { 
		DB::run($sql);
	$new_id = DB::lastInsertId();
		
		if ($case_id!="") {
			$case_table_uuid = uniqid("KA", false);
			$attribute_1 = "main";
			//now we have to attach the event to the case 
			$sql = "INSERT INTO cse_case_" . $table_name . " (`case_" . $table_name . "_uuid`, `case_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_table_uuid  ."', '" . $kase->uuid . "', '" . $table_uuid . "', 'main', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			$stmt = DB::run($sql);
		}
		if ($injury_id!="") {
			$injury_table_uuid = uniqid("KA", false);
			$attribute_1 = "main";
			//now we have to attach the event to the injury 
			$sql = "INSERT INTO cse_injury_" . $table_name . " (`injury_" . $table_name . "_uuid`, `injury_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $injury_table_uuid  ."', '" . $injury_uuid . "', '" . $table_uuid . "', 'main', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			$stmt = DB::run($sql);
		}
		if (count($arrToID) > 0) {
			attachRecipients('event', $table_uuid, $last_updated_date, $arrToID, 'to', $db);
		}
		//echo json_encode(array("success"=>$new_id)); 
		
		if ($calendar_id > 0) {
			//now get the calendar uuid for bind to event
			$customer_calendar = getCalendarInfo($calendar_id);
			//die(print_r($customer_calendar));
			if (is_object($customer_calendar)) {
				$calendar_event_uuid = uniqid("KE", false);
				$sql = "INSERT INTO cse_calendar_event (`calendar_event_uuid`, `calendar_uuid`, `user_uuid`, `event_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $calendar_event_uuid  ."', '" . $customer_calendar->uuid . "', '" . $user_uuid . "', '" . $table_uuid . "', '" . $customer_calendar->calendar . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
				$stmt = DB::run($sql);
				
				//echo $sql . "\r\n";
			}
		}
		
		//track now
		$activity_id = trackEvent("insert", $new_id);
		
		echo json_encode(array("id"=>$new_id, "uuid"=>$table_uuid, "activity_id"=>$activity_id)); 
		
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
	//trackEvent("insert", $new_id);
}
function updateEvent() {
	session_write_close();
	
	$db = getConnection();
	$arrSet = array();
	$where_clause = "";
	$table_name = "";
	$table_id = "";
	$end_date = "";
	$event_dateandtime = passed_var("event_dateandtime", "post");
	$duration = passed_var("event_duration", "post");
	if ($duration != "") {
		$end_date = DateAdd("n", $duration, strtotime($event_dateandtime));

		$end_date = date("Y-m-d H:i:s", $end_date);
	}
			
	foreach($_POST as $fieldname=>$value) {
		//no reminders, no recurrence here, not yet
		if (strpos($fieldname, "reminder_") > -1 || strpos($fieldname, "recurrent_") > -1 ) {
			continue;
		}
		if ($fieldname!="event_description") {
			$value = passed_var($fieldname, "post");
		} else {
			$value = @processHTML($_POST["event_description"]);
		}
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="user_id" || $fieldname=="number_of_days") {
			continue;
		}
		if ($fieldname=="billing_time") {
			continue;
		}
		if ($fieldname=="billing_time_dropdown") {
			continue;
		}
		if ($fieldname=="injury_id") {
			$injury_id = $value;
			continue;
		}
		if ($fieldname=="event_kind") {
			$event_kind = $value;
			continue;
		}
		//skip fields in update
		if ($fieldname=="case_id" || $fieldname=="calendar_id" || $fieldname=="case_uuid" || $fieldname=="table_uuid" || $fieldname=="send_document_id" || $fieldname=="street" || $fieldname=="city" || $fieldname=="state" || $fieldname=="zip" || $fieldname=="event_partie") {
			continue;
		}
		if ($fieldname=="off_calendar") {
			if ($value=="" || $value!="Y"){
				$value = "N";
			}
		}
		if ($fieldname=="assignee") {
			$arrTo = array();
			$arrToID = array();
			$userID_for_nextcode = $value;
			explodeRecipient($value, $arrTo, $arrToID, $db);
			$to = implode(";", $arrTo);
			$value = $to;
		}
		if ($fieldname=="event_type") {
			$color = "blue";
			$event_type_abbr = $value;
			if ($value!="") {
				$sql = "SELECT setting_value, default_value
				FROM cse_setting
				where category = 'calendar_type'
				AND setting = '" . $value . "'
				AND customer_id = " . $_SESSION['user_customer_id'];
				
				try {
					$stmt = DB::run($sql);
					$calendar_setting = $stmt->fetchObject();
					if (is_object($calendar_setting)) {
						$color = $calendar_setting->default_value;
						$event_type_abbr = $calendar_setting->setting_value;
					}
				} catch(PDOException $e) {
					$error = array("error"=> array("text"=>$e->getMessage()));
					echo json_encode($error);
				}
			}
			$arrSet[] = "`color` = '" . $color . "'";
			$arrSet[] = "`event_type_abbr` = '" . addslashes($event_type_abbr) . "'";
		}
		
		if ($fieldname=="start_date") {
			if ($value!="") {
				$value = date("Y-m-d", strtotime($value));
			} else {
				$value = "0000-00-00";
			}
		}		
		if ($fieldname=="end_date") {
			if ($end_date=="") {
				if ($value!="") {
					$value = date("Y-m-d", strtotime($value));
				} else {
					$value = "0000-00-00";
				}
				$end_date = $value;
			}
			continue;
		}
		
		if ($fieldname=="event_dateandtime" || $fieldname=="callback_date") {
			if ($value!="") {
				$value = date("Y-m-d H:i:s", strtotime($value));
			} else {
				$value = "0000-00-00 00:00:00";
			}
			if ($event_dateandtime == "") {
				$event_dateandtime = $value;
			}
		}
		if ($fieldname=="table_id" || $fieldname=="id") {
			//get uuid
			$event = getEventInfo($value);
			$event_uuid = $event->uuid;
			$table_id = $value;
			$where_clause = " = " . $value;
		} else {
			$arrSet[] = "`" . $fieldname . "` = '" . addslashes($value) . "'";
		}
	}
	
	if ($end_date!="") {
		$arrSet[] = "`end_date` = '" . $end_date . "'";
	}
	$where_clause = "`" . $table_name . "_id`" . $where_clause;
	$sql = "
	UPDATE `cse_" . $table_name . "`
	SET " . implode(", ", $arrSet) . "
	WHERE " . $where_clause;
	$sql .= " AND `cse_" . $table_name . "`.customer_id = " . $_SESSION['user_customer_id'];

	try {		
		$stmt = DB::run($sql);
		
		$my_event = getEventInfo($table_id);
		$last_updated_date = date("Y-m-d H:i:s");
		
		if (count($arrToID) > 0) {
			attachRecipients('event', $my_event->uuid, $last_updated_date, $arrToID, 'to', $db);
		}
		
		
		echo json_encode(array("success"=>$table_id)); 
		
		trackEvent("update", $table_id);

		//delete from cse_event_user for correct value of assignee input in edit popup
		$arrToID = array_values($arrToID);
		$sql = "DELETE FROM cse_event_user
		WHERE event_uuid = '".$my_event->uuid."'
		AND user_uuid NOT IN ( '" . implode( "', '" , $arrToID ) . "' )";
		$stmt = DB::run($sql);
		// print_r($arrToID);
		// print_r($my_event->uuid);
		// die();


		
		//reminders
		if ($_SERVER['REMOTE_ADDR']=='71.106.134.58') {	
			//reminders
			$arrReminder = array();
			foreach($_POST as $fieldname=>$value) {
				//no reminders, no recurrence here, not yet
				if (strpos($fieldname, "reminder_") === false) {
					continue;
				}
				$reminder_number = substr($fieldname, strlen($fieldname) -1, 1);
				if (!is_numeric($reminder_number)) {
					continue;
				}
				$value = passed_var($fieldname, "post");
				$arrReminder[$reminder_number][substr($fieldname, 0, strlen($fieldname) -1)] = "'" . addslashes($value) . "'";
			}
			//die(print_r($arrReminder));
			foreach($arrReminder as $reminder_number=>$set) {
				$reminder_interval = "";
				$reminder_span = "";
				if ($set["reminder_id"]=="") {
					$set["reminder_id"] = "-1";
				}
				$reminder_id = str_replace("'", "", $set["reminder_id"]);
				//echo $reminder_id . " = " . ($reminder_id + 2) . "\r\n";
				if ($reminder_id > 0) {
					//update
					$event_dateandtime = passed_var("event_dateandtime", "post");
					$event_dateandtime = date("Y-m-d H:i:s", strtotime($event_dateandtime));
					$reminder_datetime = date("Y-m-d H:i:s", strtotime($event_dateandtime . " - " . str_replace("'", "", $set["reminder_interval"]) . " " . str_replace("'", "", $set["reminder_span"])));
					$sql = "UPDATE cse_reminder
					SET reminder_type = " . $set["reminder_type"] . ",
					reminder_interval = " . $set["reminder_interval"] . ",
					reminder_span = " . $set["reminder_span"] . ",
					reminder_datetime = '" . $reminder_datetime . "'
					WHERE reminder_id = " . $set["reminder_id"] . "
					AND customer_id = " . $_SESSION["user_customer_id"];
					//echo $sql . "\r\n";
					$stmt = DB::run($sql);
				}
				//die(print_r($set));
				
				if ($reminder_id < 0) {
					$fields = "`reminder_uuid`";
					$reminder_uuid = uniqid("RM", false);
					$values = "'" . $reminder_uuid . "'";
					//insert
					foreach($set as $field_name=>$value) {
						if ($field_name=="reminder_id") {
							continue;
						}
						$fields .= ", `" . $field_name . "`";
						if ($field_name=="reminder_type") {
							if ($value=="") {
								$value = "''";
							}
						}
						if ($field_name=="reminder_interval") {
							if ($value=="" || $value=="''") {
								$value = "0";
							}
							$reminder_interval = str_replace("'", "", $value);
							$value = $reminder_interval;
						}
						if ($field_name=="reminder_span") {
							$reminder_span = str_replace("'", "", $value);
							//$value = $reminder_span;
						}
						
						$values .= ", " . $value;
					}
					//attach the reminder_number
					$fields .=  ", `reminder_number`";
					$values .=  ", " . $reminder_number;
					if ($reminder_interval > 0  && $reminder_span!="") {
						//calculate the reminder date
						//echo $event_dateandtime . " -> " . $reminder_interval . " -- " . $reminder_span . "\r\n";
						$reminder_datetime = date("Y-m-d H:i:s", strtotime($event_dateandtime . " - " . $reminder_interval . " " . $reminder_span));
						$fields .=  ", `reminder_datetime`, `customer_id`";
						$values .=  ", '" . $reminder_datetime . "', '" . $_SESSION['user_customer_id'] . "'";
						
						//insert the reminder
						$sql = "INSERT cse_reminder (" . $fields . ") VALUES(" . $values . ")";
						//echo $sql . "<br />";
						$stmt = DB::run($sql);
						
						$case_table_uuid = uniqid("ER", false);
						//attach each one to the event
						$sql = "INSERT INTO cse_event_reminder (`event_reminder_uuid`, `event_uuid`, `reminder_uuid`, `attribute_1`, `last_updated_date`, `last_update_user`, `customer_id`)
						VALUES ('" . $case_table_uuid  ."', '" . $event_uuid . "', '" . $reminder_uuid . "', '" . $reminder_number . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
						//echo $sql . "<br />";
						$stmt = DB::run($sql);
					}
				}
			}
		}
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function readPhoneMessage($id) {
	session_write_close();
	$sql = "
	UPDATE `cse_event`
	SET completed_date = '" . date("Y-m-d H:i:s") . "'
	WHERE event_id = :id";
	$sql .= " AND `cse_event`.customer_id = " . $_SESSION['user_customer_id'];

	if ($_SERVER['REMOTE_ADDR']=='71.116.242.3') {
		//die(print_r($arrSet));
	}
	try {		
		$stmt = DB::run($sql);
		
		$my_event = getEventInfo($table_id);
		$last_updated_date = date("Y-m-d H:i:s");
		
		if (count($arrToID) > 0) {
			attachRecipients('event', $my_event->uuid, $last_updated_date, $arrToID, 'to', $db);
		}
		
		echo json_encode(array("success"=>$table_id)); 
		
		trackEvent("update", $table_id);
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function moveEvent() {
	session_write_close();
	$id = $_POST["id"];
	
	if (!is_numeric($id)) {
		die();
	}
	$new_start = $_POST["start"];
	
	$event = getEventInfo($id);
	//get initial start date to recalc end date
	//die(print_r($event));
	$start_date = $event->event_dateandtime;
	$end_date = $event->end_date;
	$diff = days_diff($new_start, $start_date);
	//echo "orig: " . $start_date . ", new: " . $new_start . " -- end:" . $end_date . "\r\n";
	//die("diff:" . $diff);
	$end_date = date("Y-m-d H:i", DateAdd("d", $diff, strtotime($end_date)));
	
	
	//die($new_start . " = " . $end_date);
	
	$sql = "UPDATE cse_event 
	SET  
	`event_dateandtime` = :start,
	`end_date` = :end
	WHERE `event_id` = :id
	AND customer_id = " . $_SESSION['user_customer_id'];
	
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("start", $new_start);
		$stmt->bindParam("end", $end_date);
		$stmt->bindParam("id", $id);

		$stmt->execute();
		echo json_encode(array("success"=>$_POST["id"], "end_date"=>$end_date)); 
		
		trackEvent("move", $_POST["id"]);
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}

function trackEvent($operation, $event_id, $time_stamp = "") {
	$time_stamp_field = "";
	$time_stamp_value = "";
	if ($time_stamp != "") {
		$time_stamp_field = ", `time_stamp`";
		$time_stamp_value = ", '" . $time_stamp . "'";
	}
	$sql = "INSERT INTO cse_event_track (`user_uuid`, `user_logon`, `operation`" . $time_stamp_field . ", `event_id`, `event_uuid`, `event_name`, `event_date`, `event_duration`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `full_address`, `assignee`, `event_title`, `event_email`, `event_hour`, `event_type`, `event_type_abbr`, `event_from`, `event_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed`, `color`, `customer_id`, `deleted`)
	SELECT '" . $_SESSION['user_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $operation . "'" . $time_stamp_value . ", `event_id`, `event_uuid`, `event_name`, `event_date`, `event_duration`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `full_address`, `assignee`, `event_title`, `event_email`, `event_hour`, `event_type`, `event_type_abbr`, `event_from`, `event_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed`, `color`, `customer_id`, `deleted`
	FROM cse_event
	WHERE 1
	AND event_id = " . $event_id . "
	AND customer_id = " . $_SESSION['user_customer_id'] . "
	LIMIT 0, 1";
	//die($sql);
	try {
		DB::run($sql);
	$new_id = DB::lastInsertId();
	
		$event = getEventInfo($event_id);
		//new the case_uuid
		$kase = getKaseInfoByEvent($event_id);
		$case_uuid = "";
		if (is_object($kase)) {
			$case_uuid = $kase->uuid;
		} else {
			return false;
			//die(json_encode(array("success"=>true)));
			//die("no kase no activity");
		}
		$activity_category = "Kalendar";
		switch($operation) {
			case "insert":
				$operation .= "ed";
				break;
			case "move":
				$operation .= "d";
				break;
			case "update":
				$operation .= "d";
				break;
			case "delete":
				$operation .= "d";
				break;
		}
		$activity_uuid = uniqid("KS", false);
		$event_kind = "Event";
		$activity = $event_kind . " [<a title='Click to edit event' class='white_text edit_event' id='" . $event_id . "_" . $kase->id . "' data-toggle='modal' data-target='#myModal4' style='cursor:pointer'>" . $event->event_title . " @ " . date("m/d/y h:iA", strtotime($event->event_dateandtime)) . "</a>] was " . $operation . "  by " . $_SESSION['user_name'];
		if ($event->event_title=="Phone Call") {
			$event->event_title = "Phone Message";
			$activity_category = "Phone Message";
			$activity = $event->event_title . " @ " . date("m/d/y h:iA", strtotime($event->event_dateandtime)) . " was recorded by " . $_SESSION['user_name'];
		} else {
			if ($event->assignee!="") {
				$activity .= "<br />Assigned To:" . $event->assignee;
			}
			
			if ($event->full_address!="") {
				$activity .= "<br />Location:" . $event->full_address;
			}
			if ($event->event_description!="") {
				$activity .= "<br />" . $event->event_description;
			}
		}
		
		$billing_time = 0;
		$activity_id = recordActivity($operation, $activity, $case_uuid, $new_id, $activity_category, $billing_time);
		
		return $activity_id;
		
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function updateEventDate() {
	session_write_close();
	$id = passed_var("id", "post");
	$dateandtime = passed_var("dateandtime", "post");
	$dateandtime = date("Y-m-d H:i:s", strtotime($dateandtime));
	$sql = "UPDATE cse_event eve
			SET eve.`event_dateandtime` = '" . $dateandtime . "'
			WHERE `event_id`=:id";
	
	try {
		//die("me");
		//$event = getEventInfo($id);
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		//track now
		trackEvent("update", $id);
		echo json_encode(array("success"=>"Event date updated", "id"=>$id));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getCourtCalendarByBulkADJ() {
	$case_numbers = passed_var("adjs", "post");
	$start_date = passed_var("start_date", "post");
	$arrCases = json_decode($case_numbers);
	getCourtCalendarByADJ($arrCases, $start_date);
}
function getCourtCalendarByADJ($case_number, $start_date = "") {
	session_write_close();
	/*
	$fp = fopen('scrape_data.txt', 'a+');
	fwrite($fp, 'ping  @ ' . date('m/d/y H:i:s') . chr(10));
	fwrite($fp, '\r\n');
	fwrite($fp, json_encode($_SERVER));
	fclose($fp); 
	*/
	try {
		$sql = "SELECT DISTINCT ven.venue_abbr, office, judge_name, case_number, hearing_type, 
		applicant_law_firm, defense_law_firm, hearing_time  
		FROM ikase.cse_courtcalendar cc
		INNER JOIN ikase.cse_venue ven
		ON cc.office = ven.venue
		WHERE 1
		";
		
		if (!is_object($case_number)) { 
			$sql .= "
			AND case_number = :case_number";
		} else {
			$array =  (array) $case_number;
			$sql .= "
			AND case_number IN ('" . implode("','", $array) . "')";
		}
		
		if ($start_date != "") {
			$sql .= "
			AND hearing_time >= :start_date";
		}
		/*
		$sql .= "
			LIMIT 0, 300";
		*/
		//die($sql);
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if (!is_object($case_number)) { 
			$stmt->bindParam("case_number", $case_number);
		}
		if ($start_date != "") {
			$stmt->bindParam("start_date", $start_date);
		}
		$stmt->execute();
		$events = $stmt->fetchAll(PDO::FETCH_OBJ);
		// $buffer = $stmt->fetchObject();
		//die($sql);
		echo json_encode($events);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}	
}
function getCourtCalendarPending() {
	getCourtCalendar("pending");
	//die("pending");
}
function getCourtCalendarPendingByUser() {
	getCourtCalendar("pending", true);
	//die("pending");
}
function getCourtCalendar($transfer_status = "", $blnMyCases = false) {
	session_write_close();
	//return;
	try {
		//get the max import date
		$sql = "SELECT MAX(import_date) import_date
		FROM ikase.cse_courtcalendar ccc";
		$stmt = DB::run($sql);
		$import = $stmt->fetchObject();
		
		$yesterday = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d") - 1, date("Y")));
		$customer_id = $_SESSION["user_customer_id"];
		//echo $customer_id . "\r\n";
		//echo $sql;
		$sql = "SELECT DISTINCT IFNULL(customer_case.case_id, ccase.case_id) case_id, 
		IFNULL(customer_case.case_uuid, ccase.case_uuid) case_uuid, 
		IFNULL(customer_case.case_number, IFNULL(ccase.case_number, '')) case_number, 
		IFNULL(customer_case.file_number, IFNULL(ccase.file_number, '')) file_number, 
		IFNULL(customer_case.case_name, ccase.case_name) case_name, 
		IFNULL(customer_case.case_language, ccase.case_language) case_language, 
		eve.`event_id` id, `eve`.`event_uuid`, `event_date`, `event_duration`, 
		`event_name`, `event_dateandtime` `start`, `event_description`, `event_first_name`, 
		`event_last_name`, 
		`event_dateandtime`, `event_end_time`, `event_title` `title`, `event_title`, `event_email`, 
		`event_hour`, `event_type`,
		`event_type_abbr`, `eve`.`customer_id`, `color`, `off_calendar`, 'white' `textColor`, 
		'eventClass' `className`, '' `backgroundColor`, 'black' `borderColor`, 
		`full_address` `location`, `judge`, 
		`assignee`, `full_address` venue_abbr, ccev.transfer_status, ccourt.courtcalendar_id,
		ccourt.import_date,
		IFNULL(customer_case.supervising_attorney, '') supervising_attorney, 
		IFNULL(customer_case.attorney, '') attorney, 
		IFNULL(customer_case.worker, '') worker
		
		FROM `court_calendar`.`cse_event` eve
		INNER JOIN `court_calendar`.cse_case_event ccev
		ON `eve`.event_uuid = ccev.event_uuid AND ccev.deleted = 'N'
		
		INNER JOIN `court_calendar`.cse_case ccase
		ON ccev.case_uuid = ccase.case_uuid
		
		INNER JOIN `ikase`.cse_courtcalendar ccourt
		ON ccase.case_uuid = ccourt.case_uuid
		
		LEFT OUTER JOIN cse_case customer_case
		ON ccase.case_uuid = customer_case.case_uuid
        
        LEFT OUTER JOIN (
			SELECT courtevents.event_id

			FROM (
				SELECT ccase.case_uuid, eve.*
				FROM cse_event eve
				INNER JOIN cse_case_event ccev
				ON eve.event_uuid = ccev.event_uuid
				INNER JOIN cse_case ccase
				ON ccev.case_uuid = ccase.case_uuid
				WHERE 1
				AND CAST(event_dateandtime AS DATE) > :yesterday
				AND `eve`.customer_id = :customer_id
			) kaseevents

			INNER JOIN 
			(
				SELECT ccase.case_uuid, `cse_event`.*
				FROM `court_calendar`.`cse_event`  
				INNER JOIN `court_calendar`.cse_case_event ccev
				ON `cse_event`.event_uuid = ccev.event_uuid AND ccev.deleted = 'N'
				
				INNER JOIN `court_calendar`.cse_case ccase
				ON ccev.case_uuid = ccase.case_uuid
				
				INNER JOIN `ikase`.cse_courtcalendar ccourt
				ON ccase.case_uuid = ccourt.case_uuid
				
				WHERE 1
				AND `cse_event`.customer_id = :customer_id
				AND `ccourt`.customer_id = :customer_id";
			if ($transfer_status!="") {
				$sql .= "
				AND ccev.transfer_status = :transfer_status
				";
			}
			$sql .= "
				AND CAST(event_dateandtime AS DATE) > :yesterday
			) courtevents
			ON kaseevents.event_dateandtime = courtevents.event_dateandtime
			AND kaseevents.event_title = courtevents.event_title
			AND kaseevents.case_uuid = courtevents.case_uuid
        ) alreadys
        ON `eve`.event_id = alreadys.event_id
		
		WHERE `eve`.deleted = 'N' 
		AND `eve`.customer_id = :customer_id
		AND `ccourt`.customer_id = :customer_id
		AND event_date > :yesterday
		AND alreadys.event_id IS NULL";
		if ($transfer_status!="") {
			$sql .= "
			AND ccev.transfer_status = :transfer_status
			";
		}
		if ($blnMyCases) {
			$sql .= "
			AND (
				(customer_case.supervising_attorney = :nickname OR customer_case.supervising_attorney = :user_id)
                OR
                (customer_case.attorney = :nickname OR customer_case.attorney = :user_id)
                OR
                (customer_case.worker = :nickname OR customer_case.worker = :user_id)
			)";
		}
		$sql .= "
		ORDER BY event_date ASC ";
		//die($yesterday);
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("yesterday",  $yesterday);
		if ($transfer_status!="") {
			$stmt->bindParam("transfer_status", $transfer_status);
		}
		if ($blnMyCases) {
			$nickname = $_SESSION["user_nickname"];
			$user_id = $_SESSION["user_plain_id"];
			$stmt->bindParam("nickname", $nickname);
			$stmt->bindParam("user_id", $user_id);
		}
		$stmt->execute();
		$events = $stmt->fetchAll(PDO::FETCH_OBJ);
		// $buffer = $stmt->fetchObject();
		
		echo json_encode($events);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}	
}
function dismissCourtCalendarEvent() {
	session_write_close();
	$customer_id = $_SESSION["user_customer_id"];
	$event_id = passed_var("event_id", "post");
	$courtcalendar_id = passed_var("courtcalendar_id", "post");
	
	try {
		//get the uuid
		$sql = "SELECT event_uuid
		FROM `court_calendar`.cse_event eve
		WHERE event_id = :event_id";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("event_id", $event_id);
		$stmt->execute();
		$event = $stmt->fetchObject();
		
		$event_uuid = $event->event_uuid;
		
		//mark the event as deleted
		$sql = "UPDATE `court_calendar`.cse_event eve
		SET deleted = 'Y'
		WHERE event_id = :event_id";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("event_id", $event_id);
		$stmt->execute();
		
		//update the status
		$transfer_status = "dismissed";
		$sql = "UPDATE `court_calendar`.cse_case_event
		SET transfer_status = :transfer_status
		WHERE event_uuid = :event_uuid ";
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("event_uuid", $event_uuid);
		$stmt->bindParam("transfer_status", $transfer_status);
		$stmt->execute();
		
		echo json_encode(array("success"=>true, "event_id"=>$event_id, "courtcalendar_id"=>$courtcalendar_id));
	} catch(PDOException $e) {
		//die($sql);
		$error = array("error"=> array("sql"=>$sql, "text"=>$e->getMessage()));
        echo json_encode($error);
	}	
}
function transferCourtCalendarEvent() {
	session_write_close();
	$customer_id = $_SESSION["user_customer_id"];
	$event_id = passed_var("event_id", "post");
	$courtcalendar_id = passed_var("courtcalendar_id", "post");
	$assignee = passed_var("assignee", "post");
	$arrTo = array();
	$arrToID = array();
	
	if ($assignee!="") {
		$db = getConnection();
		explodeRecipient($assignee, $arrTo, $arrToID, $db);
		$to = implode(";", $arrTo);
		$assignee = $to;
	}
	try {
		//get the uuid
		$sql = "SELECT event_uuid
		FROM `court_calendar`.cse_event eve
		WHERE event_id = :event_id";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("event_id", $event_id);
		$stmt->execute();
		$event = $stmt->fetchObject();
		
		$event_uuid = $event->event_uuid;
		
		//now import
		//insert a new event
		$sql = "INSERT INTO cse_event (`event_uuid`, `event_dateandtime`, `event_type`, `assignee`, `judge`, `full_address`, `customer_id`, `event_description`, `event_title`)
		SELECT `event_uuid`, `event_dateandtime`, `event_type`, '" . $assignee . "', `judge`, `full_address`, `customer_id`, `event_description`, `event_title`
		FROM `court_calendar`.cse_event
		WHERE event_uuid = :event_uuid";
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("event_uuid", $event_uuid);
		$stmt->execute();
		
		$new_id = $db->lastInsertId();
		
		$sql = "INSERT INTO cse_case_event 
		(`case_event_uuid`, `case_uuid`, `event_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
		SELECT `case_event_uuid`, `case_uuid`, `event_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`
		FROM `court_calendar`.cse_case_event
		WHERE event_uuid = :event_uuid ";
		//echo $sql . "\r\n";
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("event_uuid", $event_uuid);
		$stmt->execute();
		
		//update the status
		$transfer_status = "approved";
		$sql = "UPDATE `court_calendar`.cse_case_event
		SET transfer_status = :transfer_status
		WHERE event_uuid = :event_uuid ";
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("event_uuid", $event_uuid);
		$stmt->bindParam("transfer_status", $transfer_status);
		$stmt->execute();
		
		//update the court calendar
		$sql = "UPDATE `ikase`.`cse_courtcalendar` 
		SET `event_uuid`=:event_uuid, 
		`customer_id`=:customer_id
		WHERE `courtcalendar_id`=:courtcalendar_id";
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("event_uuid", $event_uuid);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("courtcalendar_id", $courtcalendar_id);
		$stmt->execute();
		
		trackEvent("insert", $new_id);
		
		echo json_encode(array("id"=>$new_id, "uuid"=>$event_uuid)); 
	} catch(PDOException $e) {
		//die($sql);
		$error = array("error"=> array("sql"=>$sql, "text"=>$e->getMessage()));
        echo json_encode($error);
	}	
}


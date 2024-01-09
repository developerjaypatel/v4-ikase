<?php
use Slim\Routing\RouteCollectorProxy;

$app->group('', function (RouteCollectorProxy $app) {
	$app->group('/events', function (RouteCollectorProxy $app) {
		$app->get('', 'getEvents');
		$app->get('/today', 'getTodayEvents');
		$app->get('/{id}', 'getEvent');
	});

	$app->get('/eventplus/{id}', 'getEventWithReminders');
	$app->get('/popups', 'getPopupReminders');
	$app->get('/popupread/{remindersent_id}', 'updatePopupRead');

	$app->get('/lastchange/events', 'getLastEventChange');
	$app->get('/latestchanges/events/{max_track_id}', 'getCustomerEvents');
	$app->get('/debtors/events', 'getDebtorEvents');
})->add(Api\Middleware\Authorize::class);

$app->post('/popupssent', 'setReminderSent');

$app->group('/events', function (RouteCollectorProxy $app) {
	$app->post('/event/delete', 'deleteEvent');
	$app->post('/event/add', 'addEvent');
	$app->post('/event/update', 'updateEvent');
	$app->post('/event/read', 'readEvent');
	$app->post('/event/move', 'moveEvent');
	$app->post('/event/update/date', 'updateEventDate');
});

function getDebtorEvents() {
	session_write_close();
	
	$default_color = "white";
	$other_color = "black";
	
    $sql = "SELECT DISTINCT `event_id` id, eve.`event_uuid`, `event_date`, `event_duration`, `event_name`, `event_dateandtime` `start`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `event_title` `title`, `event_title`, `event_email`, `event_hour`, `event_type`,
    '' `event_type_abbr`, eve.`customer_id`, 'white' `color`, 'black' `textColor`, 'eventClass' `className`, 
	'red' `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, 
	`end_date`, `end_date` `end`,  `completed_date`, `callback_date`, `callback_completed` , 
	debt.debtor_id case_id, 
	CONCAT(debt.first_name, ' ', debt.last_name) `case_name`
			FROM `tbl_event` eve
			
			LEFT OUTER JOIN `tbl_event_debtor` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN tbl_debtor debt
			ON ceve.debtor_uuid = debt.debtor_uuid AND debt.`case_status` != 'closed'
			
			WHERE 1";
	//AND ccase.case_status NOT LIKE '%close%' 
	$sql .=	" AND `eve`.`deleted` ='N'
	AND eve.customer_id = " . $_SESSION['user_customer_id'];
	
	
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
function getEventsRecent() {
	getEvents("", 5);
}

function getEventCount() {
	session_write_close();
	$sql = "SELECT  MIN(`event_id`) id, CAST(`event_dateandtime` AS DATE) as `start`, 
	COUNT(`event_id`) `title`, 'true' `allDay`
	FROM `tbl_event` 
	WHERE 1
	AND `tbl_event`.`deleted` ='N'
	AND YEAR(`event_dateandtime`) > 1969
	AND `tbl_event`.event_type != 'intake'
	AND `tbl_event`.event_type != 'phone_call'
	AND `tbl_event`.customer_id = " . $_SESSION['user_customer_id'] . "
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
	// $query_date = "2017-02-14 13:25";

	$sql = "SELECT cr.*, crm.`message_uuid`, cm.`message`, cmu.`user_uuid`
			FROM `tbl_reminder` cr
			LEFT OUTER JOIN `tbl_reminder_message` crm
			ON cr.`reminder_uuid` = crm.`reminder_uuid`
			LEFT OUTER JOIN `tbl_message` cm
			ON crm.`message_uuid` = cm.`message_uuid`
			LEFT OUTER JOIN `tbl_message_user` cmu
			ON cm.`message_uuid` = cmu.`message_uuid`
			LEFT OUTER JOIN `tbl_reminderbuffer` crb
			ON cr.`reminder_uuid` = crb.`reminder_uuid` AND crb.deleted = 'N'
			WHERE 1 
			AND CAST(cr.reminder_datetime AS DATE) = '" . date("Y-m-d", strtotime($query_date)) . "'
			AND DATE_FORMAT(cr.reminder_datetime, '%Y-%m-%d %H:%i') <= '" . $query_date . "'
			AND cr.reminder_type = 'popup'
			AND cm.message_to LIKE '%" . $_SESSION["user_nickname"] . "%'
			AND cr.deleted = 'N'
			AND (cmu.`read_status` = 'N' OR crb.`reminderbuffer_id` IS NULL)
			AND cr.customer_id = '" . $_SESSION["user_customer_id"] . "'";
		
	// die($sql);
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
			// $buffer_id = $buffer->buffer_id;    
			$message_uuid = $buffer->message_uuid;
			$reminder_datetime = $buffer->reminder_datetime;

			$str_SQL = "INSERT INTO `tbl_reminderbuffer` (`message_uuid`, `reminder_uuid`, `from`, `recipients`, `subject`, `message`, `customer_id`) 
						SELECT '" . $message_uuid . "', '" . $reminder_uuid . "', 'system', '" . $user_uuid . "', '', '" . addslashes($message) . "', '" . $customer_id . "'
						FROM dual
						WHERE NOT EXISTS (
							SELECT * 
							FROM `tbl_reminderbuffer` 
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
			
			$strSQL = "UPDATE `tbl_reminder` 
					   SET `buffered` = 'Y' 
					   WHERE `reminder_id` = '" . $reminder_id . "'
					   AND `customer_id` = '" . $customer_id . "'";
			// echo $strSQL . ";\r\n";
			$stmt = DB::run($strSQL);

			if($reminder_datetime == date("Y-m-d H:i") || $reminder_datetime <= date("Y-m-d H:i", strtotime("+15 minutes"))){
				$color = "green";
			} else {
				$color = "red";
			}

			$arrReminders[] = array("message"=>$message, "reminderbuffer_id"=>$reminderbuffer_id, "color"=>$color);
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
			
			$sql = "SELECT crb.`recipients`, crb.`message`, crb.`message_uuid`, crb.`reminder_uuid` 
					FROM `tbl_reminderbuffer` crb
					WHERE 1 
					AND crb.`reminderbuffer_id` = '" . $reminderbuffer_id . "'
					AND crb.`customer_id` = '" . $_SESSION["user_customer_id"] . "'";
			// die($sql);
            $stmt = DB::run($sql);
			$reminderbuffer = $stmt->fetchObject();
			// die(print_r($reminderbuffer));

            $query = "INSERT INTO `tbl_remindersent` (`reminderbuffer_id`, `recipients`, `subject`, `message`, `message_uuid`, `reminder_uuid`, `customer_id`) 
					  SELECT (" . $reminderbuffer_id . ", '" . $reminderbuffer->recipients . "', 'event text message sent' , '" . addslashes($reminderbuffer->message) . "', '" . $reminderbuffer->message_uuid . "', '" . $reminderbuffer->reminder_uuid . "', '" . $customer_id . "'
					  FROM dual
						WHERE NOT EXISTS (
							SELECT * 
							FROM `tbl_remindersent` 
							WHERE reminder_uuid = '" . $reminder_uuid . "'
							AND customer_id = '" . $customer_id . "'
						)";
            // echo $query . ";\r\n";
          
            $stmt = DB::run($query);
			
		}
		die(json_encode(array("success"=>"true", "query"=>$query)));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage(), "query"=>$query));
			echo json_encode($error);
	} 
}
function updatePopupRead($reminderbuffer_id) {
	$sql = "SELECT cmu.message_user_id 
			FROM tbl_reminderbuffer crb
			LEFT OUTER JOIN tbl_message cm
			ON crb.message_uuid = cm.message_uuid
			LEFT OUTER JOIN tbl_message_user cmu
			ON cm.message_uuid = cmu.message_uuid
			WHERE 1
			AND crb.reminderbuffer_id = '" . $reminderbuffer_id . "'
			AND crb.customer_id = '" . $_SESSION["user_customer_id"] . "'";
	// echo $sql . "\r\n";
	try{
		$stmt = DB::run($sql);
		// $buffers = $stmt->fetchAll(PDO::FETCH_OBJ);
		$message_user = $stmt->fetchObject();

		$query = "UPDATE tbl_message_user 
				  SET read_status = 'Y', 
				  read_date = '" . date("Y-m-d H:i:s") . "'
				  WHERE message_user_id = '" . $message_user->message_user_id . "'
				  AND `type` = 'to'
				  AND customer_id = '" . $_SESSION["user_customer_id"] . "'";

		// die($query . "\r\n");
		$stmt = DB::run($query);
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
	FROM `tbl_event_track`
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
	FROM tbl_event_track
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
	
    $sql = "SELECT DISTINCT `event_id` id, `tbl_event`.`event_uuid`, `event_date`, `event_duration`, `event_name`, `event_dateandtime` `start`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `event_title` `title`, `event_title`, `event_email`, `event_hour`, `event_type`,
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, `tbl_event`.`customer_id`, IFNULL(sett.default_value, `color`) `color`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, `full_address` `location`, `judge`, `assignee`, IFNULL(venue_abbr, '') venue_abbr
			FROM ";
			if ($limit!="") {
				$sql .= "(SELECT * FROM `tbl_event` ORDER BY event_id DESC LIMIT 0, 10000) `tbl_event` ";
			} else {
				$sql .= "`tbl_event`";
			}
			$sql .= " 
			LEFT OUTER JOIN (
				SELECT * FROM `tbl_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(`tbl_event`.event_type) = LOWER(sett.setting)
			
			LEFT OUTER JOIN tbl_case_event ccev
			ON `tbl_event`.event_uuid = ccev.event_uuid AND ccev.deleted = 'N'
			LEFT OUTER JOIN tbl_case ccase
			ON ccev.case_uuid = ccase.case_uuid
			LEFT OUTER JOIN `tbl_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `tbl_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			LEFT OUTER JOIN `tbl_setting` sett
			ON `tbl_event`.event_type = sett.setting";
	
	if (count($arrJoins)>0) {
		foreach($arrJoins as $join) {
			
			$table_name = $join["table_name"];
			$table_id = $join["table_id"];
			$sql .=	" INNER JOIN tbl_event_" . $table_name . "
		ON tbl_event.event_uuid = tbl_event_" . $table_name . ".event_uuid
		INNER JOIN `tbl_" . $table_name . "_complete`
		ON `tbl_event_" . $table_name . "`.`" . $table_name . "_uuid` = `tbl_" . $table_name . "_complete`.`" . $table_name . "_uuid`";
		}
	}
	$sql .=	" WHERE `tbl_event`.`deleted` ='N'
	AND `tbl_event`.customer_id = " . $_SESSION['user_customer_id'];
	
	if (count($arrJoins)>0) {
		foreach($arrJoins as $join) {
			$table_name = $join["table_name"];
			$table_id = $join["table_id"];
			$sql .=	" AND `tbl_" . $table_name . "_complete`.`" . $table_name . "_id` = :" . $table_name . "_id";
			$sql .=	" AND `tbl_" . $table_name . "_complete`.deleted = 'N'";
		}
	}
	if ($limit!="") {
		if (is_numeric($limit)) {
			$sql .= " ORDER BY event_id DESC LIMIT 0, " . $limit;
		}
	} else {
		$sql .= " ORDER BY event_dateandtime ASC";
	}
	die($sql);
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
function getTodayEvents(){

	$sql = "SELECT * FROM `tbl_event` WHERE `event_date` = '2017-03-16'";

	try {
		$events = DB::select($sql);
		echo json_encode($events);

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
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', IFNULL(employer.`company_name`, '')) `case_name`, ccase.case_number, ccase.file_number, ccase.case_name case_stored_name, `event_id` id, eve.`event_uuid` `uuid`, IFNULL(venue_abbr, '') venue_abbr
			FROM `tbl_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `tbl_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			INNER JOIN `tbl_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			INNER JOIN tbl_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			
			LEFT OUTER JOIN `tbl_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `tbl_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
			LEFT OUTER JOIN tbl_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "tbl_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `tbl_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `tbl_corporation` employer
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
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.file_number, ccase.case_name case_stored_name, 
	IFNULL(ccase.attorney, '') supervising_attorney, IFNULL(ccase.supervising_attorney, '') attorney, 
	IFNULL(venue_abbr, '') venue_abbr
			FROM `tbl_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `tbl_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			INNER JOIN `tbl_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			INNER JOIN tbl_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			
			LEFT OUTER JOIN `tbl_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `tbl_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
			LEFT OUTER JOIN tbl_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "tbl_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `tbl_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `tbl_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			WHERE ccase.deleted != 'Y'
			AND eve.event_type != 'phone_call'";
	$sql .=	" AND ccase.case_uuid IN (" . $case_uuids . ")";
		//$sql .=	" AND ccase.case_id = :case_id";	
	$sql .=	" AND `eve`.`deleted` ='N'
	AND eve.customer_id = " . $_SESSION['user_customer_id'];
	$sql .= " ORDER BY eve.event_id ASC";
	
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
	$sql = "UPDATE tbl_event mes, tbl_event_user ceu
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
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, eve.`end_date`, `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.file_number, ccase.case_name case_stored_name, IFNULL(ccase.attorney, '') supervising_attorney, ceu.read_status, ceu.read_date , IFNULL(venue_abbr, '') venue_abbr
			FROM `tbl_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `tbl_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			INNER JOIN `tbl_event_user` ceu
			ON eve.event_uuid = ceu.event_uuid
			INNER JOIN `tbl_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			INNER JOIN tbl_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			
			LEFT OUTER JOIN `tbl_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `tbl_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
			INNER JOIN tbl_case_injury cci
			ON ccase.case_uuid = cci.case_uuid
			INNER JOIN tbl_injury ci
			ON cci.injury_uuid = ci.injury_uuid
			LEFT OUTER JOIN tbl_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "tbl_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `tbl_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `tbl_corporation` employer
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
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.file_number, ccase.case_name case_stored_name, IFNULL(ccase.attorney, '') supervising_attorney, ceu.read_status, ceu.read_date , IFNULL(venue_abbr, '') venue_abbr
			FROM `tbl_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `tbl_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			INNER JOIN `tbl_event_user` ceu
			ON eve.event_uuid = ceu.event_uuid
			INNER JOIN `tbl_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			INNER JOIN tbl_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			LEFT OUTER JOIN `tbl_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `tbl_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
			LEFT OUTER JOIN tbl_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "tbl_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `tbl_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `tbl_corporation` employer
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
function newPhoneCalls() {
	session_write_close();
	$default_color = "white";
	$other_color = "black";
	if ($_SESSION['user_customer_id']==1049 || $_SESSION['user_customer_id']==1075) {
		$default_color = "black";
		$other_color = "white";
	}
	//IF(sett.default_value IS NULL, '" . $other_color ."', '" . $default_color ."') `textColor`
	$sql = "SELECT DISTINCT `event_id` id, eve.`event_uuid`, `event_date`, `event_duration`, `event_name`, `event_dateandtime` `start`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `event_title` `title`, `event_title`, `event_email`, `event_hour`, `event_type`,
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.file_number, ccase.case_name case_stored_name, IFNULL(ccase.attorney, '') supervising_attorney, ceu.read_status, ceu.read_date , IFNULL(venue_abbr, '') venue_abbr
			FROM `tbl_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `tbl_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			INNER JOIN `tbl_event_user` ceu
			ON eve.event_uuid = ceu.event_uuid
			LEFT OUTER JOIN `tbl_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN tbl_case ccase
			ON ceve.case_uuid = ccase.case_uuid AND ccase.deleted != 'Y'
			
			LEFT OUTER JOIN `tbl_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `tbl_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
			LEFT OUTER JOIN tbl_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "tbl_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `tbl_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `tbl_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			WHERE 1
			AND eve.event_type = 'phone_call'
			AND `ceu`.`type` = 'to'
			AND `ceu`.read_status = 'N'
			AND `ceu`.deleted = 'N'
			AND `ceu`.user_uuid = '" . $_SESSION['user_id'] . "'";
	

	$sql .=	" AND `eve`.`deleted` ='N'
	AND eve.customer_id = " . $_SESSION['user_customer_id'];
	$sql .= " ORDER BY eve.event_dateandtime DESC";
	//die($sql);
	try {
		$events = DB::select($sql);
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
FROM tbl_event `eve`
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
	$six_months_ago = mktime(0, 0, 0, date("m") - 1,   date("d"),   date("Y"));
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
function getCustomerEvents() {
	session_write_close();
	
	$default_color = "white";
	$other_color = "black";
	if ($_SESSION['user_customer_id']==1049 || $_SESSION['user_customer_id']==1075) {
		$default_color = "black";
		$other_color = "white";
	}
	
	$default_color = "black";
	$other_color = "white";
	/*
	`tbl_setting` sett
	ON eve.event_type = sett.setting 
		AND sett.deleted = 'N'
		AND sett.customer_id = " . $_SESSION['user_customer_id'] . "
		AND sett.category = 'calendar_colors'
	*/
	
    $sql = "SELECT DISTINCT `event_id` id, eve.`event_uuid`, `event_date`, `event_duration`, `event_name`, `event_dateandtime` `start`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `event_title` `title`, `event_title`, `event_email`, `event_hour`, `event_type`,
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, 
	IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, 
	`end_date`, `end_date` `end`,  `completed_date`, `callback_date`, `callback_completed` , 
	IFNULL(ccase.case_id, icase.case_id) case_id, 
	IF (ccase.case_id IS NULL, CONCAT(iapp.first_name,' ',iapp.last_name,' vs ', iemployer.`company_name`), CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`)) `case_name`, 
	IF (ccase.case_id IS NULL, icase.case_number, ccase.case_number) case_number, 
	IF (ccase.case_id IS NULL, icase.file_number, ccase.file_number) file_number, 
	IF (ccase.case_id IS NULL, icase.case_name, ccase.case_name) case_stored_name,
	 
	IFNULL(ccase.attorney, '') supervising_attorney, 
	cal.sort_order cal_sort_order, IFNULL(venue_abbr, '') venue_abbr
			FROM `tbl_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `tbl_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			LEFT OUTER JOIN `tbl_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN tbl_case ccase
			ON ceve.case_uuid = ccase.case_uuid AND ccase.`case_status` NOT LIKE '%close%'
			
			LEFT OUTER JOIN `tbl_injury_event` cive
			ON eve.event_uuid = cive.event_uuid
			LEFT OUTER JOIN `tbl_case_injury` cci
			ON cive.injury_uuid = cci.injury_uuid
			LEFT OUTER JOIN `tbl_case` icase
			ON cci.case_uuid = icase.case_uuid
			
			LEFT OUTER JOIN `tbl_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `tbl_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
			LEFT OUTER JOIN tbl_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "tbl_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `tbl_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `tbl_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			
			LEFT OUTER JOIN tbl_case_person icapp ON icase.case_uuid = icapp.case_uuid
			LEFT OUTER JOIN ";
			if ($_SESSION['user_customer_id']==1033) { 
				$sql .= "(" . SQL_PERSONX . ")";
			} else {
				$sql .= "tbl_person";
			}
			$sql .= " iapp ON icapp.person_uuid = iapp.person_uuid
			LEFT OUTER JOIN `tbl_case_corporation` iccorp
			ON (icase.case_uuid = iccorp.case_uuid AND iccorp.attribute = 'employer' AND iccorp.deleted = 'N')
			LEFT OUTER JOIN `tbl_corporation` iemployer
			ON iccorp.corporation_uuid = iemployer.corporation_uuid
			
			LEFT OUTER JOIN tbl_calendar_event cec             
			ON eve.event_uuid = cec.event_uuid             
			LEFT OUTER JOIN tbl_calendar cal             
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
	$six_months_ago = mktime(0, 0, 0, date("m") - 1,   date("d"),   date("Y"));
	$sql .=	" AND (eve.event_date > '" . date("Y-m-d", $six_months_ago) . "'
	 OR eve.`event_dateandtime` > '" . date("Y-m-d", $six_months_ago) . "')";
	$sql .= " ORDER BY eve.event_id ASC";
	
	if (($_SERVER['REMOTE_ADDR']=='71.254.171.237' )) {
		//die($sql);
	}
	
	try {
		$allcusevents = DB::select($sql);
        echo json_encode($allcusevents);

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
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, 
	`end_date`, `end_date` `end`,  `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.file_number, ccase.case_name case_stored_name, IFNULL(ccase.attorney, '') supervising_attorney , IFNULL(venue_abbr, '') venue_abbr
			FROM `tbl_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `tbl_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			LEFT OUTER JOIN `tbl_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN tbl_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			LEFT OUTER JOIN `tbl_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `tbl_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			LEFT OUTER JOIN tbl_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "tbl_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `tbl_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `tbl_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			LEFT OUTER JOIN tbl_calendar_event cec             
			ON eve.event_uuid = cec.event_uuid             
			LEFT OUTER JOIN tbl_calendar cal             
			ON cec.calendar_uuid = cal.calendar_uuid
			WHERE 1";
	$sql .=	" AND ccase.case_status NOT LIKE '%close%' AND `eve`.`deleted` ='N'
	AND eve.customer_id = " . $_SESSION['user_customer_id'];
	$sql .=	" AND `ccase`.`attorney` = '" . $attorney . "'";
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
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, 
	`end_date`, `end_date` `end`,  `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.file_number, ccase.case_name case_stored_name, IFNULL(ccase.attorney, '') supervising_attorney , IFNULL(venue_abbr, '') venue_abbr
			FROM `tbl_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `tbl_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			LEFT OUTER JOIN `tbl_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN tbl_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			LEFT OUTER JOIN `tbl_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `tbl_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			LEFT OUTER JOIN tbl_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "tbl_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `tbl_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `tbl_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			LEFT OUTER JOIN tbl_calendar_event cec             
			ON eve.event_uuid = cec.event_uuid             
			LEFT OUTER JOIN tbl_calendar cal             
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
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, 
	`end_date`, `end_date` `end`,  `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.file_number, ccase.case_name case_stored_name, IFNULL(ccase.attorney, '') supervising_attorney , IFNULL(venue_abbr, '') venue_abbr
			FROM `tbl_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `tbl_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			LEFT OUTER JOIN `tbl_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN tbl_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			LEFT OUTER JOIN `tbl_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `tbl_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			LEFT OUTER JOIN tbl_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "tbl_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `tbl_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `tbl_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			LEFT OUTER JOIN tbl_calendar_event cec             
			ON eve.event_uuid = cec.event_uuid             
			LEFT OUTER JOIN tbl_calendar cal             
			ON cec.calendar_uuid = cal.calendar_uuid
			WHERE 1";
	$sql .=	" AND ccase.case_status NOT LIKE '%close%' AND `eve`.`deleted` ='N'
	AND eve.customer_id = " . $_SESSION['user_customer_id'];
	$sql .=	" AND `eve`.`assignee` LIKE '%" . $assignee . "%'";
	$sql .=	" AND `eve`.`event_type` = '" . $type . "'";
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
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, 
	`end_date`, `end_date` `end`,  `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.file_number, ccase.case_name case_stored_name, IFNULL(ccase.attorney, '') supervising_attorney , IFNULL(venue_abbr, '') venue_abbr
			FROM `tbl_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `tbl_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			LEFT OUTER JOIN `tbl_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN tbl_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			LEFT OUTER JOIN `tbl_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `tbl_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			LEFT OUTER JOIN tbl_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "tbl_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `tbl_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `tbl_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			LEFT OUTER JOIN tbl_calendar_event cec             
			ON eve.event_uuid = cec.event_uuid             
			LEFT OUTER JOIN tbl_calendar cal             
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
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, 
	`end_date`, `end_date` `end`,  `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.file_number, ccase.case_name case_stored_name, IFNULL(ccase.attorney, '') supervising_attorney , IFNULL(venue_abbr, '') venue_abbr
			FROM `tbl_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `tbl_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			LEFT OUTER JOIN `tbl_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN tbl_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			LEFT OUTER JOIN `tbl_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `tbl_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			LEFT OUTER JOIN tbl_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "tbl_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `tbl_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `tbl_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			LEFT OUTER JOIN tbl_calendar_event cec             
			ON eve.event_uuid = cec.event_uuid             
			LEFT OUTER JOIN tbl_calendar cal             
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
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, 
	`end_date`, `end_date` `end`,  `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.file_number, ccase.case_name case_stored_name, IFNULL(ccase.attorney, '') supervising_attorney , IFNULL(venue_abbr, '') venue_abbr
			FROM `tbl_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `tbl_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			LEFT OUTER JOIN `tbl_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN tbl_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			LEFT OUTER JOIN `tbl_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `tbl_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			LEFT OUTER JOIN tbl_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "tbl_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `tbl_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `tbl_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			LEFT OUTER JOIN tbl_calendar_event cec             
			ON eve.event_uuid = cec.event_uuid             
			LEFT OUTER JOIN tbl_calendar cal             
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
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, 
	`end_date`, `end_date` `end`,  `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.file_number, ccase.case_name case_stored_name, IFNULL(ccase.attorney, '') supervising_attorney , IFNULL(venue_abbr, '') venue_abbr
			FROM `tbl_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `tbl_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			LEFT OUTER JOIN `tbl_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN tbl_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			LEFT OUTER JOIN `tbl_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `tbl_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			LEFT OUTER JOIN tbl_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "tbl_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `tbl_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `tbl_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			LEFT OUTER JOIN tbl_calendar_event cec             
			ON eve.event_uuid = cec.event_uuid             
			LEFT OUTER JOIN tbl_calendar cal             
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
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, 
	`end_date`, `end_date` `end`,  `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.file_number, ccase.case_name case_stored_name, IFNULL(ccase.attorney, '') supervising_attorney , IFNULL(venue_abbr, '') venue_abbr
			FROM `tbl_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `tbl_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			LEFT OUTER JOIN `tbl_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN tbl_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			LEFT OUTER JOIN `tbl_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `tbl_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			LEFT OUTER JOIN tbl_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "tbl_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `tbl_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `tbl_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			LEFT OUTER JOIN tbl_calendar_event cec             
			ON eve.event_uuid = cec.event_uuid             
			LEFT OUTER JOIN tbl_calendar cal             
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
	/*
    $sql = "SELECT DISTINCT `event_id` id, eve.`event_uuid`, `event_date`, 
	`event_duration`, `event_name`, `event_dateandtime` `start`, 
	`event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, 
	`event_end_time`, `event_title` `title`, `event_title`, 
	`event_email`, `event_hour`, `event_type`,
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, `color`, 
	IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, 
	IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, 
	eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, 
	`end_date`, `end_date` `end`,  `completed_date`, `callback_date`, `callback_completed`, 
	IFNULL(ccase.case_id, -1) case_id,
	IFNULL(CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`), '') `case_name`, 
	IFNULL(ccase.case_number, '') case_number, 
	IFNULL(ccase.file_number, '') file_number, 
	IFNULL(ccase.case_name, '') case_stored_name, 
	IFNULL(ccase.attorney, '') supervising_attorney, 
	IFNULL(venue_abbr, '') venue_abbr
	
	FROM `tbl_event` eve
	
	LEFT OUTER JOIN (
		SELECT * FROM `tbl_setting` 
		WHERE deleted = 'N'
		AND customer_id = " . $_SESSION['user_customer_id'] . "
		AND category = 'calendar_type'
	) sett
	ON LOWER(eve.event_type) = LOWER(sett.setting)
			
	LEFT OUTER JOIN `tbl_case_event` ceve
	ON eve.event_uuid = ceve.event_uuid
	LEFT OUTER JOIN tbl_case ccase
	ON ceve.case_uuid = ccase.case_uuid AND ccase.case_status NOT LIKE '%close%' 
	LEFT OUTER JOIN `tbl_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `tbl_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
	LEFT OUTER JOIN tbl_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
	LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "tbl_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
	LEFT OUTER JOIN `tbl_case_corporation` ccorp
	ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
	LEFT OUTER JOIN `tbl_corporation` employer
	ON ccorp.corporation_uuid = employer.corporation_uuid
	LEFT OUTER JOIN tbl_calendar_event cec             
	ON eve.event_uuid = cec.event_uuid             
	LEFT OUTER JOIN tbl_calendar cal             
	ON cec.calendar_uuid = cal.calendar_uuid AND cal.sort_order = 0
	WHERE 1";
	$sql .=	" AND `eve`.`deleted` ='N'";
	*/
	$sql = "SELECT DISTINCT `event_id` id, eve.`event_uuid`, `event_date`, `event_duration`, `event_name`, `event_dateandtime` `start`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `event_title` `title`, `event_title`, `event_email`, `event_hour`, `event_type`,
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, 
	IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, 
	`end_date`, `end_date` `end`,  `completed_date`, `callback_date`, `callback_completed` , 
	IFNULL(ccase.case_id, icase.case_id) case_id, 
	IF (ccase.case_id IS NULL, CONCAT(iapp.first_name,' ',iapp.last_name,' vs ', iemployer.`company_name`), CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`)) `case_name`, 
	IF (ccase.case_id IS NULL, icase.case_number, ccase.case_number) case_number, 
	IF (ccase.case_id IS NULL, icase.file_number, ccase.file_number) file_number, 
	IF (ccase.case_id IS NULL, icase.case_name, ccase.case_name) case_stored_name,
	 
	IFNULL(ccase.attorney, '') supervising_attorney, 
	cal.sort_order cal_sort_order, IFNULL(venue_abbr, '') venue_abbr
			FROM `tbl_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `tbl_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			LEFT OUTER JOIN `tbl_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN tbl_case ccase
			ON ceve.case_uuid = ccase.case_uuid AND ccase.`case_status` NOT LIKE '%close%'
			
			LEFT OUTER JOIN `tbl_injury_event` cive
			ON eve.event_uuid = cive.event_uuid
			LEFT OUTER JOIN `tbl_case_injury` cci
			ON cive.injury_uuid = cci.injury_uuid
			LEFT OUTER JOIN `tbl_case` icase
			ON cci.case_uuid = icase.case_uuid
			
			LEFT OUTER JOIN `tbl_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `tbl_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
			LEFT OUTER JOIN tbl_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "tbl_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `tbl_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `tbl_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			
			LEFT OUTER JOIN tbl_case_person icapp ON icase.case_uuid = icapp.case_uuid
			LEFT OUTER JOIN ";
			if ($_SESSION['user_customer_id']==1033) { 
				$sql .= "(" . SQL_PERSONX . ")";
			} else {
				$sql .= "tbl_person";
			}
			$sql .= " iapp ON icapp.person_uuid = iapp.person_uuid
			LEFT OUTER JOIN `tbl_case_corporation` iccorp
			ON (icase.case_uuid = iccorp.case_uuid AND iccorp.attribute = 'employer' AND iccorp.deleted = 'N')
			LEFT OUTER JOIN `tbl_corporation` iemployer
			ON iccorp.corporation_uuid = iemployer.corporation_uuid
			
			LEFT OUTER JOIN tbl_calendar_event cec             
			ON eve.event_uuid = cec.event_uuid             
			LEFT OUTER JOIN tbl_calendar cal             
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
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, 
	`end_date`, `end_date` `end`,  `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.file_number, ccase.case_name case_stored_name, IFNULL(ccase.attorney, '') supervising_attorney , IFNULL(venue_abbr, '') venue_abbr
			FROM `tbl_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `tbl_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			INNER JOIN tbl_calendar_event cec             
			ON eve.event_uuid = cec.event_uuid             
			INNER JOIN tbl_calendar cal             
			ON cec.calendar_uuid = cal.calendar_uuid
			LEFT OUTER JOIN `tbl_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN tbl_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			
			LEFT OUTER JOIN `tbl_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `tbl_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
			LEFT OUTER JOIN tbl_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "tbl_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `tbl_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `tbl_corporation` employer
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
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, 
	`end_date`, `end_date` `end`,  `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.file_number, ccase.case_name case_stored_name, IFNULL(ccase.attorney, '') supervising_attorney , IFNULL(venue_abbr, '') venue_abbr
			FROM `tbl_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `tbl_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			INNER JOIN tbl_calendar_event cec             
			ON eve.event_uuid = cec.event_uuid             
			INNER JOIN tbl_calendar cal             
			ON cec.calendar_uuid = cal.calendar_uuid
			LEFT OUTER JOIN `tbl_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN tbl_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			
			LEFT OUTER JOIN `tbl_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `tbl_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
			LEFT OUTER JOIN tbl_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "tbl_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `tbl_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `tbl_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			WHERE 1";
	$sql .=	" AND `eve`.`deleted` ='N'
	AND CAST(eve.event_dateandtime AS DATE) BETWEEN :start AND :end
	AND eve.customer_id = " . $_SESSION['user_customer_id'];
	$sql .=	" AND (cal.sort_order = 1)";
	//$sql .=	" AND ((`eve`.`event_type` != 'phone_call' AND `eve`.`event_type` != 'intake') OR cal.sort_order = 0)";
	$sql .= " ORDER BY eve.event_id ASC";
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
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.file_number, ccase.case_name case_stored_name, IFNULL(ccase.attorney, '') supervising_attorney , IFNULL(venue_abbr, '') venue_abbr
			FROM `tbl_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `tbl_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			LEFT OUTER JOIN `tbl_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN tbl_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			
			LEFT OUTER JOIN `tbl_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `tbl_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
			LEFT OUTER JOIN tbl_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "tbl_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `tbl_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `tbl_corporation` employer
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
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.file_number, ccase.case_name case_stored_name, IFNULL(ccase.attorney, '') supervising_attorney , IFNULL(venue_abbr, '') venue_abbr
			FROM `tbl_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `tbl_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			LEFT OUTER JOIN `tbl_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN tbl_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			
			LEFT OUTER JOIN `tbl_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `tbl_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
			LEFT OUTER JOIN tbl_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "tbl_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `tbl_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `tbl_corporation` employer
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
	IF (ccase.case_id IS NULL, icase.case_name, ccase.case_name) case_stored_name, 
	IF (ccase.case_id IS NULL, IFNULL(icase.attorney, ''), IFNULL(ccase.attorney, '')) supervising_attorney, 
	IFNULL(venue_abbr, '') venue_abbr
	FROM `tbl_event` cse  
	
	LEFT OUTER JOIN `tbl_case_event` ceve
	ON cse.event_uuid = ceve.event_uuid
	LEFT OUTER JOIN tbl_case ccase
	ON ceve.case_uuid = ccase.case_uuid
	
	LEFT OUTER JOIN `tbl_injury_event` cive
	ON cse.event_uuid = cive.event_uuid
	LEFT OUTER JOIN `tbl_case_injury` cci
	ON cive.injury_uuid = cci.injury_uuid
	LEFT OUTER JOIN `tbl_case` icase
	ON cci.case_uuid = icase.case_uuid
	
	LEFT OUTER JOIN `tbl_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `tbl_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
	LEFT OUTER JOIN tbl_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
	LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "tbl_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
	LEFT OUTER JOIN `tbl_case_corporation` ccorp
	ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
	LEFT OUTER JOIN `tbl_corporation` employer
	ON ccorp.corporation_uuid = employer.corporation_uuid
	
	LEFT OUTER JOIN tbl_case_person icapp ON icase.case_uuid = icapp.case_uuid
	LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "tbl_person";
	}
	$sql .= " iapp ON icapp.person_uuid = iapp.person_uuid
	LEFT OUTER JOIN `tbl_case_corporation` iccorp
	ON (icase.case_uuid = iccorp.case_uuid AND iccorp.attribute = 'employer' AND iccorp.deleted = 'N')
	LEFT OUTER JOIN `tbl_corporation` iemployer
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
	//die($sql);
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
	FROM  `tbl_event` cse ON ( csu.event_uuid = cse.event_uuid
	AND csu.type =  'to' ) 
	INNER JOIN `tbl_case_event` ceve
	ON cse.event_uuid = ceve.event_uuid
	INNER JOIN tbl_case ccase
	ON ceve.case_uuid = ccase.case_uuid
	
	LEFT OUTER JOIN `tbl_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `tbl_venue` venue
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
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.file_number, ccase.case_name case_stored_name, IFNULL(ccase.attorney, '') supervising_attorney , IFNULL(venue_abbr, '') venue_abbr
			FROM `tbl_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `tbl_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			INNER JOIN `tbl_calendar_event` calev
			ON eve.event_uuid = calev.event_uuid
			INNER JOIN `tbl_calendar` cal
			ON calev.calendar_uuid = cal.calendar_uuid
			LEFT OUTER JOIN `tbl_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN tbl_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			
			LEFT OUTER JOIN `tbl_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `tbl_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
			LEFT OUTER JOIN tbl_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "tbl_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `tbl_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `tbl_corporation` employer
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
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.file_number, ccase.case_name case_stored_name, IFNULL(ccase.attorney, '') supervising_attorney , IFNULL(venue_abbr, '') venue_abbr
			FROM `tbl_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `tbl_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			INNER JOIN `tbl_calendar_event` calev
			ON (eve.event_uuid = calev.event_uuid)
			INNER JOIN `tbl_calendar` cal
			ON (calev.calendar_uuid = cal.calendar_uuid)
			LEFT OUTER JOIN `tbl_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN tbl_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			
			LEFT OUTER JOIN `tbl_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `tbl_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
			LEFT OUTER JOIN tbl_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "tbl_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `tbl_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `tbl_corporation` employer
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
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.file_number, ccase.case_name case_stored_name, IFNULL(ccase.attorney, '') supervising_attorney , IFNULL(venue_abbr, '') venue_abbr
			FROM `tbl_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `tbl_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			INNER JOIN `tbl_calendar_event` calev
			ON (eve.event_uuid = calev.event_uuid)
			INNER JOIN `tbl_calendar` cal
			ON (calev.calendar_uuid = cal.calendar_uuid)
			LEFT OUTER JOIN `tbl_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN tbl_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			
			LEFT OUTER JOIN `tbl_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `tbl_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
			LEFT OUTER JOIN tbl_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "tbl_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `tbl_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `tbl_corporation` employer
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
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.file_number, ccase.case_name case_stored_name, IFNULL(ccase.attorney, '') supervising_attorney , IFNULL(venue_abbr, '') venue_abbr
			FROM `tbl_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `tbl_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			INNER JOIN `tbl_calendar_event` calev
			ON (eve.event_uuid = calev.event_uuid)
			INNER JOIN `tbl_calendar` cal
			ON (calev.calendar_uuid = cal.calendar_uuid)
			LEFT OUTER JOIN `tbl_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN tbl_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			
			LEFT OUTER JOIN `tbl_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `tbl_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
			LEFT OUTER JOIN tbl_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "tbl_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `tbl_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `tbl_corporation` employer
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
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.file_number, ccase.case_name case_stored_name, IFNULL(ccase.attorney, '') supervising_attorney , IFNULL(venue_abbr, '') venue_abbr
			FROM `tbl_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `tbl_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			INNER JOIN `tbl_calendar_event` calev
			ON (eve.event_uuid = calev.event_uuid)
			INNER JOIN `tbl_calendar` cal
			ON (calev.calendar_uuid = cal.calendar_uuid)
			LEFT OUTER JOIN `tbl_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN tbl_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			
			LEFT OUTER JOIN `tbl_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `tbl_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
			LEFT OUTER JOIN tbl_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "tbl_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `tbl_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `tbl_corporation` employer
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

function getUserEvents($user_id) {
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
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.file_number, ccase.case_name case_stored_name, IFNULL(ccase.attorney, '') supervising_attorney , IFNULL(venue_abbr, '') venue_abbr
			FROM `tbl_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `tbl_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			INNER JOIN `tbl_calendar_event` calev
			ON (eve.event_uuid = calev.event_uuid AND calev.user_uuid = '" . $user->uuid . "')
			INNER JOIN `tbl_calendar` cal
			ON (calev.calendar_uuid = cal.calendar_uuid AND cal.sort_order = 5)
			LEFT OUTER JOIN `tbl_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN tbl_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			
			LEFT OUTER JOIN `tbl_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `tbl_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
			LEFT OUTER JOIN tbl_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "tbl_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `tbl_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `tbl_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			WHERE 1";
	$sql .=	" AND `eve`.`deleted` ='N'
	AND eve.customer_id = " . $_SESSION['user_customer_id'];
	$sql .=	" AND ((`eve`.`event_type` != 'phone_call' AND `eve`.`event_type` != 'intake' AND cal.sort_order = 5)";
	$sql .=	")";	//employee calendar
	
	$sql .= " UNION
	SELECT DISTINCT `event_id` id, eve.`event_uuid`, `event_date`, `event_duration`, `event_name`, `event_dateandtime` `start`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `event_title` `title`, `event_email`, `event_hour`, `event_type`,
    IFNULL(sett.setting_value, `event_type_abbr`) `event_type_abbr`, eve.`customer_id`, IFNULL(sett.default_value, `color`) `color`, IFNULL(sett.default_value IS NULL, 'white') `textColor`, 'eventClass' `className`, IFNULL(sett.default_value, '') `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed` , ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.file_number, ccase.case_name case_stored_name, IFNULL(ccase.attorney, '') supervising_attorney , IFNULL(venue_abbr, '') venue_abbr
			FROM `tbl_event` eve
			LEFT OUTER JOIN (
				SELECT * FROM `tbl_setting` 
				WHERE deleted = 'N'
				AND customer_id = " . $_SESSION['user_customer_id'] . "
				AND category = 'calendar_type'
			) sett
			ON LOWER(eve.event_type) = LOWER(sett.setting)
			
			LEFT OUTER JOIN `tbl_case_event` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN tbl_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			
			LEFT OUTER JOIN `tbl_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `tbl_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
			LEFT OUTER JOIN tbl_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "tbl_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `tbl_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `tbl_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			WHERE 1";
	$sql .=	" AND `eve`.`deleted` ='N'
	AND eve.customer_id = " . $_SESSION['user_customer_id'];
	$sql .= " AND (eve.event_from = '" . addslashes($_SESSION['user_name']) . "'";
	$sql .= " OR eve.assignee LIKE '%" . $_SESSION['user_nickname'] . "%')";
	/*
	event_from==login_username
	customer_event.assignee.indexOf(login_nickname) > -1
	*/
	$sql .= " ORDER BY id ASC";
	//die($sql);
	try {
		$calevents = DB::select($sql);
        echo json_encode($calevents);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getAllKaseEvents($case_id) {
	session_write_close();
	
	$sql = "SELECT cse . *, cse.`event_id` `id` , cse.`event_uuid` `uuid`, 
	ccase.case_id, ccase.case_uuid, IFNULL(ccase.attorney, '') supervising_attorney, IFNULL(venue_abbr, '') venue_abbr
	FROM  `tbl_event` cse 
	LEFT OUTER JOIN `tbl_case_event` ceve
	ON cse.event_uuid = ceve.event_uuid
	LEFT OUTER JOIN tbl_case ccase
	ON ceve.case_uuid = ccase.case_uuid
	
	LEFT OUTER JOIN `tbl_case_venue` cvenue
	ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
	LEFT OUTER JOIN `tbl_venue` venue
	ON cvenue.venue_uuid = venue.venue_uuid
	
	WHERE 1 ";
	$sql .=	" AND `cse`.`deleted` ='N'
	AND cse.event_type != 'phone_call'
	AND cse.customer_id = " . $_SESSION['user_customer_id'];
	$sql .= " AND ccase.case_id = :case_id";
	$sql .= " ORDER BY cse.event_dateandtime DESC";
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
function getFutureKaseEvents($case_id) {
	session_write_close();
	
	$sql = "SELECT cse . *, cse.`event_id` `id` , cse.`event_uuid` `uuid`, 
	ccase.case_id, ccase.case_uuid
	FROM  `tbl_event` cse 
	LEFT OUTER JOIN `tbl_case_event` ceve
	ON cse.event_uuid = ceve.event_uuid
	LEFT OUTER JOIN tbl_case ccase
	ON ceve.case_uuid = ccase.case_uuid
	
	LEFT OUTER JOIN `tbl_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `tbl_venue` venue
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
	`callback_date`, `callback_completed`, `color`, ev.`customer_id`, ev.`deleted`,  
	ev.`event_id` `id`, ev.`event_uuid` `uuid`, cse.case_id,
	
	IFNULL(cr1.reminder_id, '-1') reminder_id1,
    cr1.`reminder_uuid` reminder_uuid1, cr1.`reminder_type` reminder_type1, cr1.`reminder_interval` reminder_interval1, 
    cr1.`reminder_span` reminder_span1, cr1.`reminder_datetime` reminder_datetime1, cr1.`buffered` buffered1,
	
	IFNULL(cr2.reminder_id, '-1') reminder_id2,
    cr2.`reminder_uuid` reminder_uuid2, cr2.`reminder_type` reminder_type2, cr2.`reminder_interval` reminder_interval2, 
    cr2.`reminder_span` reminder_span2, cr2.`reminder_datetime` reminder_datetime2, cr2.`buffered` buffered2
	
	FROM `tbl_event` ev
	
	LEFT OUTER JOIN tbl_event_reminder cer1
	ON ev.event_uuid = cer1.event_uuid AND cer1.attribute_1 = 1 AND cer1.deleted = 'N'
	LEFT OUTER JOIN tbl_reminder cr1
	ON cer1.reminder_uuid = cr1.reminder_uuid AND cr1.deleted = 'N' AND cr1.reminder_number = 1
	
	LEFT OUTER JOIN tbl_event_reminder cer2
	ON ev.event_uuid = cer2.event_uuid AND cer2.attribute_1 = 2 AND cer2.deleted = 'N'
	LEFT OUTER JOIN tbl_reminder cr2
	ON cer2.reminder_uuid = cr2.reminder_uuid AND cr2.deleted = 'N' AND cr2.reminder_number = 2
	
	LEFT OUTER JOIN tbl_case_event ccm
	ON ev.event_uuid = ccm.event_uuid
	LEFT OUTER JOIN tbl_case cse
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
	 $sql = "SELECT DISTINCT `event_id` id, eve.`event_uuid`, `event_date`, `event_duration`, `event_name`, `event_dateandtime` `start`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `event_title` `title`, `event_title`, `event_email`, `event_hour`, `event_type`,
    '' `event_type_abbr`, eve.`customer_id`, 'white' `color`, 'black' `textColor`, 'eventClass' `className`, 
	'red' `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, 
	`end_date`, `end_date` `end`,  `completed_date`, `callback_date`, `callback_completed` , 
	debt.debtor_id case_id, 
	CONCAT(debt.first_name, ' ', debt.last_name) `case_name`
			FROM `tbl_event` eve
			
			LEFT OUTER JOIN `tbl_event_debtor` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN tbl_debtor debt
			ON ceve.debtor_uuid = debt.debtor_uuid AND debt.`case_status` != 'closed'
			
			WHERE ev.event_id= :id";
	//AND ccase.case_status NOT LIKE '%close%' 
	$sql .=	" AND `eve`.`deleted` ='N'
	AND eve.customer_id = " . $_SESSION['user_customer_id'];
	
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
    `event_type_abbr`, `event_from`, `event_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed`, `color`, `customer_id`, `deleted`,  `event_id` `id`, `event_uuid` `uuid` 
			FROM `tbl_event`
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
	$sql = "UPDATE tbl_event 
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
	$debtor_id = "";
	$arrTo = array();
	$arrToID = array();
	$start_date = passed_var("start_date", "post");
	$event_time = passed_var("event_time", "post");
	$event_dateandtime = $start_date . " " . $event_time;
	$duration = passed_var("event_duration", "post");
	if ($duration != "") {
		//there is no end date box on the calendar, so we calculate from duration
		$end_date = DateAdd("n", $duration, strtotime($event_dateandtime));
		$end_date = date("Y-m-d H:i:s", $end_date);
	}

	foreach($_POST as $fieldname=>$value) {
		//no reminders, no recurrence here, not yet
		//echo $fieldname . "\r\n";
		if ((strpos($fieldname, "reminder_") > -1 || strpos($fieldname, "recurrent_") > -1)  && $fieldname!="reminder_set" ) {
			continue;
		}
		if ($fieldname=="reminder_set") {
			$reminder_set = json_decode($_POST["reminder_set"]);
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
		if ($fieldname=="event_id") {
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
		if ($fieldname=="debtor_id") {
			$debtor_id = $value;
			if ($debtor_id==-1) {
				$debtor_id = "";
			}
			continue;
		}
		
		if ($fieldname=="start_date") {
			if ($value!="") {
				$value = date("Y-m-d", strtotime($value));
			} else {
				$value = "0000-00-00";
			}
			continue;
		}
		if ($fieldname=="event_time") {
			$event_time = $value;
			continue;
		}
		
		if ($fieldname=="assignee") {
			explodeRecipient($value, $arrTo, $arrToID, $db);
			$to = implode(";", $arrTo);
			$value = $to;
		}
		$db = getConnection();
		
		if ($fieldname=="number_of_days") {
			continue;
		}
		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "'" . addslashes($value) . "'";
	}
	
	if ($debtor_id!="") {
		$debtor = getDebtorInfo($debtor_id);
		
		$arrFields[] = "`event_first_name`";
		$arrSet[] = "'" . addslashes($debtor->first_name) . "'";
		
		$arrFields[] = "`event_last_name`";
		$arrSet[] = "'" . addslashes($debtor->last_name) . "'";
	}

	if ($fieldname=="event_type") {
		$color = "blue";
		$event_type_abbr = $value;
		
		$sql = "SELECT setting_value, default_value
		FROM tbl_setting
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
	}
	$arrFields[] = "`customer_id`";
	$arrSet[] = $_SESSION['user_customer_id'];
	
	if ($start_date!="") {
		$arrFields[] = "`event_dateandtime`";
		$arrSet[] = "'" . $start_date . " " . $event_time . "'";
	}
	
	
	$table_uuid = uniqid("KS", false);	
	$last_updated_date = date("Y-m-d H:i:s");
	
	//reminders
	if (isset($_POST["reminder_set"])) {
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
				
				$strSQL = "INSERT INTO tbl_reminder 
						(`reminder_uuid`, `reminder_number`, `reminder_type`, `reminder_interval`, `reminder_span`, `reminder_datetime`, `buffered`, `customer_id`) 
						VALUES ('" . $reminder_uuid . "', '" . $item->reminder_number . "', '" . $item->reminder_type . "', " . $item->reminder_interval . ", '" . $item->reminder_span . "', '" . $item->reminder_datetime . "', 'N', '" . $_SESSION['user_customer_id'] . "')";
	
	
				$query = "INSERT INTO tbl_reminder_message 
						(`reminder_message_uuid`, `reminder_uuid`, `message_uuid`, `attribute`, `last_update_user`, `customer_id`)
						VALUES ('" . $reminder_message_uuid . "', '" . $reminder_uuid . "', '" . $message_uuid . "', 'main', '" . $sender_uuid . "', '" . $_SESSION['user_customer_id'] . "')";
	
				//attach each one to the event
				$sql = "INSERT INTO tbl_event_reminder 
					 (`event_reminder_uuid`, `event_uuid`, `reminder_uuid`, `attribute_1`, `last_updated_date`, `last_update_user`, `customer_id`)
					 VALUES ('" . $case_table_uuid  ."', '" . $table_uuid . "', '" . $reminder_uuid . "', '" . $item->reminder_number . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
				//echo $sql . "<br />";
	
				// tbl_reminder						
				DB::run($strSQL);
	$reminder_id = DB::lastInsertId();
				
				// tbl_reminder_message
				$stmt = DB::run($query);
	
				// tbl_event_reminder
				$stmt = DB::run($sql);
			}
		}
	}

	$sql = "INSERT INTO `tbl_event` (`event_uuid`, " . implode(",", $arrFields) . ") 
			VALUES('" . $table_uuid . "', " . implode(",", $arrSet) . ")";
	//die($sql);
	try { 
		DB::run($sql);
	$new_id = DB::lastInsertId();
		
		if ($debtor_id!="") {
			$event_table_uuid = uniqid("KA", false);
			$attribute_1 = "main";
			//now we have to attach the event to the case 
			$sql = "INSERT INTO tbl_event_debtor (`event_debtor_uuid`, `event_uuid`, `debtor_uuid`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $event_table_uuid  ."', '" . $table_uuid . "', '" . $debtor->uuid . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			$stmt = DB::run($sql);
		}
		
		if (count($arrToID) > 0) {
			attachRecipients('event', $table_uuid, $last_updated_date, $arrToID, 'to', $db);
		}
		
		//track now
		echo json_encode(array("id"=>$new_id, "uuid"=>$table_uuid)); 
		
		trackEvent("insert", $new_id);
	} catch(PDOException $e) {	
		echo $sql  . "<br />";
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
		if ($fieldname=="assignee") {
			$arrTo = array();
			$arrToID = array();
			explodeRecipient($value, $arrTo, $arrToID, $db);
			$to = implode(";", $arrTo);
			$value = $to;
		}
		if ($fieldname=="event_type") {
			$color = "blue";
			$event_type_abbr = $value;
			if ($value!="") {
				$sql = "SELECT setting_value, default_value
				FROM tbl_setting
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
	UPDATE `tbl_" . $table_name . "`
	SET " . implode(", ", $arrSet) . "
	WHERE " . $where_clause;
	$sql .= " AND `tbl_" . $table_name . "`.customer_id = " . $_SESSION['user_customer_id'];

	try {		
		$stmt = DB::run($sql);
		
		$my_event = getEventInfo($table_id);
		$last_updated_date = date("Y-m-d H:i:s");
		
		if (count($arrToID) > 0) {
			attachRecipients('event', $my_event->uuid, $last_updated_date, $arrToID, 'to', $db);
		}
		
		echo json_encode(array("success"=>$table_id)); 
		
		trackEvent("update", $table_id);
		
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
					$sql = "UPDATE tbl_reminder
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
						$sql = "INSERT tbl_reminder (" . $fields . ") VALUES(" . $values . ")";
						//echo $sql . "<br />";
						$stmt = DB::run($sql);
						
						$case_table_uuid = uniqid("ER", false);
						//attach each one to the event
						$sql = "INSERT INTO tbl_event_reminder (`event_reminder_uuid`, `event_uuid`, `reminder_uuid`, `attribute_1`, `last_updated_date`, `last_update_user`, `customer_id`)
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
	$sql = "
	UPDATE `tbl_event`
	SET completed_date = '" . date("Y-m-d H:i:s") . "'
	WHERE event_id = :id";
	$sql .= " AND `tbl_event`.customer_id = " . $_SESSION['user_customer_id'];

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
	
	$sql = "UPDATE tbl_event 
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

function trackEvent($operation, $event_id) {
	$sql = "INSERT INTO tbl_event_track (`user_uuid`, `user_logon`, `operation`, `event_id`, `event_uuid`, `event_name`, `event_date`, `event_duration`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `full_address`, `assignee`, `event_title`, `event_email`, `event_hour`, `event_type`, `event_type_abbr`, `event_from`, `event_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed`, `color`, `customer_id`, `deleted`)
	SELECT '" . $_SESSION['user_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $operation . "', `event_id`, `event_uuid`, `event_name`, `event_date`, `event_duration`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `full_address`, `assignee`, `event_title`, `event_email`, `event_hour`, `event_type`, `event_type_abbr`, `event_from`, `event_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed`, `color`, `customer_id`, `deleted`
	FROM tbl_event
	WHERE 1
	AND event_id = " . $event_id . "
	AND customer_id = " . $_SESSION['user_customer_id'] . "
	LIMIT 0, 1";
	//die($sql);
	try {
		DB::run($sql);
	$new_id = DB::lastInsertId();
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function updateEventDate() {
	$id = passed_var("id", "post");
	$dateandtime = passed_var("dateandtime", "post");
	$dateandtime = date("Y-m-d H:i:s", strtotime($dateandtime));
	$sql = "UPDATE tbl_event eve
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

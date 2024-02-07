<?php
$app->get('/events', authorize('user'),	'getEvents');
$app->get('/events/today', authorize('user'), 'getTodayEvents');
$app->get('/events/nextday/:start', authorize('user'), 'getNextDayEvents');
$app->get('/events/previousday/:start', authorize('user'), 'getPreviousDayEvents');
$app->get('/events/dates/:start/:end', authorize('user'), 'getEventsByDate');
$app->get('/events/broadcasts', authorize('user'), 'getBroadcasts');
$app->get('/events/:id', authorize('user'),	'getEvent');

$app->get('/eventplus/:id', authorize('user'),	'getEventWithReminders');
$app->get('/popups', authorize('user'),	'getPopupReminders');
$app->get('/popupread/:remindersent_id', authorize('user'),	'updatePopupRead');
$app->post('/popupssent', 'setReminderSent');


$app->get('/lastchange/events', authorize('user'),	'getLastEventChange');
$app->get('/latestchanges/events/:max_track_id', authorize('user'),	'getCustomerEvents');

$app->post('/event/cancel', authorize('user'), 'cancelAppt');
$app->post('/event/cancelbyjudge', authorize('user'), 'cancelApptByJudge');
$app->post('/event/delete', authorize('user'), 'deleteEvent');
$app->post('/event/add', authorize('user'), 'addEvent');
$app->post('/event/update', authorize('user'), 'updateEvent');
$app->post('/event/read', authorize('user'), 'readEvent');
$app->post('/event/move', authorize('user'), 'moveEvent');
$app->post('/event/update/date', authorize('user'), 'updateEventDate');

$app->post('/reminder/cancel', authorize('user'), 'cancelReminder');
$app->post('/reminder/verify', authorize('user'), 'verifyReminder');
$app->post('/reminders/verify', authorize('user'), 'verifyReminders');
$app->post('/reminders/verifybyid', authorize('user'), 'verifyRemindersByID');
$app->post('/reminders/verifybyjudge', authorize('user'), 'verifyRemindersByJudge');

$app->post('/reminders/changebyid', authorize('user'), 'changeRemindersByID');

$app->post('/reminder/sent', authorize('user'), 'sentReminder');

$app->get('/customer/events/:debtor_id', authorize('user'), 'getDebtorEvents');
$app->get('/customers/events', authorize('user'), 'getDebtorsEvents');

$app->get('/contact/reminders/:debtor_id', authorize('user'), 'getContactReminders');

$app->get('/confirm/:debtor_id', authorize('user'), 'confirmEvent');
$app->get('/cancel/:debtor_id', authorize('user'), 'cancelEvent');

$app->post("/reminders/newtime", authorize('user'), "getReminderDateTime");

$app->get('/reminder/voice/:reminder_id/:message_id', authorize('user'), 'voiceReminder');

function getDebtorEvents($debtor_id) {
	$_SESSION["search_debtor_id"]= $debtor_id;
	getDebtorsEvents();
}
function getContactReminders($debtor_id) {
	session_write_close();
	$customer_id = $_SESSION['user_customer_id'];
    $sql = "SELECT rem.*, rem.reminder_id id, debt.debtor_id, debt.first_name, debt.last_name, debt.chart_number
			FROM md_reminder.tbl_reminder rem
			INNER JOIN md_reminder.tbl_debtor debt
			ON rem.reminder_debtor_uuid = debt.debtor_uuid
			INNER JOIN md_reminder.tbl_event_reminder ter
            ON rem.reminder_uuid = ter.reminder_uuid
            INNER JOIN md_reminder.tbl_event eve
            ON ter.event_uuid = eve.event_uuid
			WHERE 1";
	//AND ccase.case_status NOT LIKE '%close%' 
	$sql .=	" 
	AND `rem`.`deleted` ='N'
	AND eve.deleted = 'N'
	AND rem.customer_id = :customer_id
	AND eve.customer_id = :customer_id
	AND debt.customer_id = :customer_id
	AND `debt`.`debtor_id` = :debtor_id";
	
	$sql .= " ORDER BY rem.reminder_datetime ASC";
	
	//die($sql);
	try {
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("debtor_id", $debtor_id);
		$stmt->bindParam("customer_id", $customer_id);
		
		$stmt->execute();
		$reminders = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
        echo json_encode($reminders);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getDebtorsEvents() {
	session_write_close();
	//specific debtor?
	$search_debtor_id = "";
	$search_joins = "LEFT OUTER";
	if (isset($_SESSION["search_debtor_id"])) {
		if ($_SESSION["search_debtor_id"]!="") {
			$search_debtor_id = $_SESSION["search_debtor_id"];
		}
	}
	//reset
	$_SESSION["search_debtor_id"] = "";
	
	if ($search_debtor_id!="") {
		$search_joins = "INNER";
	}
	session_write_close();
	
	$default_color = "white";
	$other_color = "black";
	
    $sql = "SELECT DISTINCT `event_id` id, eve.`event_uuid`, `event_date`, `event_duration`, `event_name`, `event_dateandtime` `start`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `event_title` `title`, `event_title`, `event_email`, `event_hour`, `event_type`,
    '' `event_type_abbr`, eve.`customer_id`, 'white' `color`, 'black' `textColor`, 'eventClass' `className`, 
	'red' `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, 
	`end_date`, `end_date` `end`,  `completed_date`, 
	debt.debtor_id case_id, 
	CONCAT(debt.first_name, ' ', debt.last_name) `case_name`
			FROM `tbl_event` eve
			
			" . $search_joins . " JOIN `tbl_event_debtor` ceve
			ON eve.event_uuid = ceve.event_uuid
			" . $search_joins . " JOIN tbl_debtor debt
			ON ceve.debtor_uuid = debt.debtor_uuid AND debt.`case_status` != 'closed'
			
			WHERE 1";
	//AND ccase.case_status NOT LIKE '%close%' 
	$sql .=	" AND `eve`.`deleted` ='N'
	AND eve.customer_id = " . $_SESSION['user_customer_id'];
	
	if ($search_debtor_id!="") {
		$sql .=	" AND `debt`.`debtor_id` = :debtor_id";
	}
	$six_months_ago = mktime(0, 0, 0, date("m") - 1,   date("d"),   date("Y"));
	$sql .=	" AND (eve.event_date > '" . date("Y-m-d", $six_months_ago) . "'
	 OR eve.`event_dateandtime` > '" . date("Y-m-d", $six_months_ago) . "')";
	$sql .= " ORDER BY eve.event_id ASC";
	
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($search_debtor_id!="") {
			$stmt->bindParam("debtor_id", $search_debtor_id);
		}
		$stmt->execute();
		$allcusevents = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
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
		$db = getConnection();
		$stmt = $db->prepare($sql);
		
		$stmt->execute();
		$eventcounts = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
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
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$buffers = $stmt->fetchAll(PDO::FETCH_OBJ);
		// $buffer = $stmt->fetchObject();
		$stmt->closeCursor(); 
		$db = null; $stmt = null;
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
			$db = getConnection();
			$stmt = $db->prepare($str_SQL);
			$stmt->execute();
			$inserted_id = $db->lastInsertId();
			if ($inserted_id!=0) {
				//not zero, new buffer
				$reminderbuffer_id = $inserted_id;
			}
			
			$db = null; $stmt = null;
			
			$strSQL = "UPDATE `tbl_reminder` 
					   SET `buffered` = 'Y' 
					   WHERE `reminder_id` = '" . $reminder_id . "'
					   AND `customer_id` = '" . $customer_id . "'";
			// echo $strSQL . ";\r\n";
			$db = getConnection();
			$stmt = $db->prepare($strSQL);
			$stmt->execute();
			$db = null; $stmt = null;

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
	session_write_close();
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
            $db = getConnection();
            $stmt = $db->prepare($sql);
            $stmt->execute();
			$reminderbuffer = $stmt->fetchObject();
			$stmt->closeCursor();
            $db = null; $stmt = null;
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
          
            $db = getConnection();
            $stmt = $db->prepare($query);
            $stmt->execute();
            $db = null; $stmt = null;
			
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
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		// $buffers = $stmt->fetchAll(PDO::FETCH_OBJ);
		$message_user = $stmt->fetchObject();
		$stmt->closeCursor(); 
		$db = null; $stmt = null;

		$query = "UPDATE tbl_message_user 
				  SET read_status = 'Y', 
				  read_date = '" . date("Y-m-d H:i:s") . "'
				  WHERE message_user_id = '" . $message_user->message_user_id . "'
				  AND `type` = 'to'
				  AND customer_id = '" . $_SESSION["user_customer_id"] . "'";

		// die($query . "\r\n");
		$db = getConnection();
		$stmt = $db->prepare($query);
		$stmt->execute();
		$stmt->closeCursor(); 
		$db = null; $stmt = null;
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
		$db = getConnection();
		$stmt = $db->prepare($sql);
		
		$stmt->execute();
		$events = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
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
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$event = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;

		echo json_encode($event);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getBroadcasts() {
	$_SESSION["broadcast"] = true;
	getEvents();
}
function getEvents($relationships = "", $limit = "") {
	$blnBroadcast = (isset($_SESSION["broadcast"]));
	if ($blnBroadcast) {
		unset($_SESSION["broadcast"]);
	}
	session_write_close();
	
	$customer_id = $_SESSION['user_customer_id'];
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
	
    $sql = "SELECT DISTINCT `event_id` id, `tbl_event`.*";
	if ($blnBroadcast) {
		$sql .= ", IFNULL(broadcasts.message_count, 0) message_count,
		IFNULL(responses.response_count, 0) response_count,
		IFNULL(unsubs.unsub_count, 0) unsub_count,
		IFNULL(delivereds.delivered_count, 0) delivered_count,
		IFNULL(faileds.failed_count, 0) failed_count ";
	}
	$sql .= "
	FROM ";
	if ($limit!="") {
		$sql .= "(SELECT * FROM `tbl_event` ORDER BY event_id DESC LIMIT 0, 10000) `tbl_event` ";
	} else {
		$sql .= "`tbl_event`";
	}
	
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
	if ($blnBroadcast) {
		$sql .= "
		INNER JOIN (
			SELECT eve.event_uuid, COUNT(mess.message_id) message_count
			FROM tbl_event eve
			INNER JOIN tbl_event_reminder ter
			ON ter.event_uuid = `eve`.event_uuid
			INNER JOIN tbl_reminder_message trm
			on `ter`.reminder_uuid = trm.reminder_uuid
			INNER JOIN tbl_message mess
			ON trm.message_uuid = mess.message_uuid AND mess.message_type = 'broadcast'
			WHERE eve.customer_id = :customer_id
			GROUP BY eve.event_uuid
		) broadcasts
		ON `tbl_event`.event_uuid = broadcasts.event_uuid
		
		LEFT OUTER JOIN (
			SELECT eve.event_uuid, COUNT(res.response_id) response_count
			FROM tbl_event eve
			INNER JOIN tbl_event_reminder ter
			ON eve.event_uuid = ter.event_uuid
			INNER JOIN tbl_remindersent trs
			ON ter.reminder_uuid = trs.reminder_uuid
			INNER JOIN tbl_response res
			ON trs.reply_response_id = res.response_id
			WHERE eve.customer_id = :customer_id
			GROUP BY eve.event_uuid
		) responses
		ON `tbl_event`.event_uuid = responses.event_uuid
		
		
		LEFT OUTER JOIN (
			SELECT eve.event_uuid, COUNT(res.response_id) unsub_count
			FROM tbl_event eve
			INNER JOIN tbl_event_reminder ter
			ON eve.event_uuid = ter.event_uuid
			INNER JOIN tbl_remindersent trs
			ON ter.reminder_uuid = trs.reminder_uuid
			INNER JOIN tbl_response res
			ON trs.reply_response_id = res.response_id
			WHERE eve.customer_id = :customer_id
			AND LOWER(res.response) = 'n' OR LOWER(res.response) = 'no' OR LOWER(res.response) = 'unsubscribe'
			GROUP BY eve.event_uuid
		) unsubs
		ON `tbl_event`.event_uuid = unsubs.event_uuid
		
		LEFT OUTER JOIN (
			SELECT eve.event_uuid, COUNT(trs.remindersent_id) delivered_count
			FROM tbl_event eve
			INNER JOIN tbl_event_reminder ter
			ON eve.event_uuid = ter.event_uuid
			INNER JOIN tbl_remindersent trs
			ON ter.reminder_uuid = trs.reminder_uuid
			
			WHERE eve.customer_id = :customer_id
			AND (`response` LIKE '%\"status\": \"0\"%'
			OR `response` LIKE '%\"status\":201%'
			OR `response` LIKE '%\"status\":202%')
			GROUP BY eve.event_uuid
		) delivereds
		ON `tbl_event`.event_uuid = delivereds.event_uuid
		
		LEFT OUTER JOIN (
			SELECT eve.event_uuid, COUNT(trs.remindersent_id) failed_count
			FROM tbl_event eve
			INNER JOIN tbl_event_reminder ter
			ON eve.event_uuid = ter.event_uuid
			INNER JOIN tbl_remindersent trs
			ON ter.reminder_uuid = trs.reminder_uuid
			
			WHERE eve.customer_id = 1
			AND (`response` NOT LIKE '%\"status\": \"0\"%'
			AND `response` NOT LIKE '%\"status\":201%'
			AND `response` NOT LIKE '%\"status\":202%')
			GROUP BY eve.event_uuid
		) faileds
		ON `tbl_event`.event_uuid = faileds.event_uuid";
	}
	$sql .=	"
	WHERE `tbl_event`.`deleted` ='N'
	AND `tbl_event`.customer_id = :customer_id";
	
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
		if ($blnBroadcast) {
			$sql .= " ORDER BY event_dateandtime DESC";
		} else {
			$sql .= " ORDER BY event_dateandtime ASC";
		}
	}
	//die($sql);
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
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$events = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;

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
function getNextDayEvents($start) {
	$start = DateAdd("d", 1, strtotime($start));
	$start = date("Y-m-d", $start);
	getEventsByDate($start, $start);
}
function getPreviousDayEvents($start) {
	$start = DateAdd("d", -1, strtotime($start));
	$start = date("Y-m-d", $start);
	getEventsByDate($start, $start);
}
function getTodayEvents(){
	$event_date = date("Y-m-d");
	getEventsByDate($event_date, $event_date);
}
function getEventsByDate($start, $end) {
	session_write_close();
	
	$customer_id = $_SESSION["user_customer_id"];
	
	if ($start==$end) {
		$sql = " WHERE `event_date` = :start"; 
	} else {
		$start .= " 00:00:00";
		$end .= " 23:59:59";
		$sql = " WHERE `event_date` BETWEEN :start AND :end"; 
	}
	$sql .= " AND eve.customer_id = :customer_id";
	$sql = "SELECT DISTINCT `eve`.*,
	ccase.debtor_id, ccase.first_name, ccase.last_name, ccase.full_name, ccase.phone, ccase.cellphone, ccase.language, eve.`event_id` id, eve.`event_uuid` `uuid`
			FROM `tbl_event` `eve`
			INNER JOIN tbl_event_debtor ccev
			ON `eve`.event_uuid = ccev.event_uuid AND ccev.deleted = 'N'
			INNER JOIN tbl_debtor ccase
			ON ccev.debtor_uuid = ccase.debtor_uuid
			" . $sql . "
			ORDER BY eve.event_dateandtime ASC";
	//'2017-03-31'
	// AND `event_duration` != '15'
	
	//die($sql);
	$event_date = date("Y-m-d");
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("start", $start);
		$stmt->bindParam("customer_id", $customer_id);
		if ($start!=$end) {
			$stmt->bindParam("end", $end);
		}
		$stmt->execute();
		$events = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); 
		$stmt = null; $db = null;
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
		$stmt->closeCursor(); $stmt = null; $db = null;
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
		$stmt->closeCursor(); $stmt = null; $db = null;
		
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
		
		$stmt = null; $db = null;
		echo json_encode(array("success"=>"task marked as read"));
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
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$event_counts = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;

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
		$db = getConnection();
		$stmt = $db->prepare($sql);
		
		$stmt->execute();
		$allcusevents = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
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
		$db = getConnection();
		$stmt = $db->prepare($sql);
		
		$stmt->execute();
		$allcusevents = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
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
		$stmt->closeCursor(); $stmt = null; $db = null;
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
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$allcusevents = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
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
		$stmt->closeCursor(); $stmt = null; $db = null;
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
		$stmt->closeCursor(); $stmt = null; $db = null;
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
		$db = getConnection();
		$stmt = $db->prepare($sql);
		
		$stmt->execute();
		$allcusevents = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
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
		$db = getConnection();
		$stmt = $db->prepare($sql);
		
		$stmt->execute();
		$allcusevents = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
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
		$db = getConnection();
		$stmt = $db->prepare($sql);
		
		$stmt->execute();
		$allcusevents = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
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
		$stmt->closeCursor(); $stmt = null; $db = null;
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
		$db = getConnection();
		$stmt = $db->prepare($sql);
		
		$stmt->execute();
		$allcusevents = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
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
		$stmt->closeCursor(); $stmt = null; $db = null;
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
		$db = getConnection();
		$stmt = $db->prepare($sql);
		
		$stmt->execute();
		$allcusevents = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
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
		$stmt->closeCursor(); $stmt = null; $db = null;
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
		$stmt->closeCursor(); $stmt = null; $db = null;
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
		$stmt->closeCursor(); $stmt = null; $db = null;
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
		$db = getConnection();
		$stmt = $db->prepare($sql);
		
		$stmt->execute();
		$calevents = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
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
		$db = getConnection();
		$stmt = $db->prepare($sql);
		
		$stmt->execute();
		$calevents = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
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
		$stmt->closeCursor(); $stmt = null; $db = null;
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
		$db = getConnection();
		$stmt = $db->prepare($sql);
		
		$stmt->execute();
		$calevents = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
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
		$stmt->closeCursor(); $stmt = null; $db = null;
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
		$db = getConnection();
		$stmt = $db->prepare($sql);
		
		$stmt->execute();
		$calevents = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
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
		$stmt->closeCursor(); $stmt = null; $db = null;
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
		$stmt->closeCursor(); $stmt = null; $db = null;
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
		$stmt->closeCursor(); $stmt = null; $db = null;

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
	
	//get the event and the reminders too    
	 $sql = "SELECT DISTINCT `event_id` id, eve.`event_uuid`, `event_date`, `event_duration`, `event_name`, `event_dateandtime` `start`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `event_title` `title`, `event_title`, `event_email`, `event_hour`, `event_type`,
    '' `event_type_abbr`, eve.`customer_id`, 'white' `color`, 'black' `textColor`, 'eventClass' `className`, 
	'red' `backgroundColor`, 'black' `borderColor`, eve.`full_address`, eve.`full_address` `location`, `judge`, `assignee`, `event_from`, `event_priority`, 
	`end_date`, `end_date` `end`,  `completed_date`,
	debt.debtor_id, debt.first_name, debt.last_name, 
	IFNULL(ter.attribute_2, '') use_attribute,
	IFNULL(rem.reminder_id, '-1') reminder_id1,
	IFNULL(rem.reminder_type, '-1') reminder_type1,
	IFNULL(rem.reminder_interval, '-1') reminder_interval1,
	IFNULL(rem.reminder_span, '-1') reminder_span1
			FROM `tbl_event` eve
			
			LEFT OUTER JOIN `tbl_event_debtor` ceve
			ON eve.event_uuid = ceve.event_uuid
			LEFT OUTER JOIN tbl_debtor debt
			ON ceve.debtor_uuid = debt.debtor_uuid AND debt.`case_status` != 'closed'
			
			LEFT OUTER JOIN `tbl_event_reminder` ter
			ON eve.event_uuid = ter.event_uuid
			
			LEFT OUTER JOIN `tbl_reminder` rem
			ON ter.reminder_uuid = rem.reminder_uuid
			
			WHERE eve.event_id= :id";
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
		$stmt->closeCursor(); $stmt = null; $db = null;

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
function getProviderInfo($value) {
	try {
		$sql = 'SELECT DISTINCT `prov`.`Code`, TRIM(SUBSTRING_INDEX(`prov`.`Name`, ",", -1)) first_name, 
				TRIM(SUBSTRING_INDEX(`prov`.`Name`, ",", 1)) last_name
				FROM remind.ohproviders prov
				WHERE  `prov`.`Code` = "' . $value . '"
				ORDER BY TRIM(SUBSTRING_INDEX(`prov`.`Name`, ",", 1))';
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$code = $stmt->fetchObject();
		$db = null;
		return $code;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getReminderInfo($id) {
	session_write_close();
	
    $sql = "SELECT rem.*,  `reminder_id` `id`, `reminder_uuid` `uuid` 
			FROM `tbl_reminder` rem
			WHERE reminder_id = :id
			AND customer_id = :customer_id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
		$stmt->execute();
		$reminder = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;

        return $reminder;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getEventInfo($id) {
	session_write_close();
	$default_color = "white";
	$other_color = "black";
	
    $sql = "SELECT eve.*,  eve.`event_id` `id`, eve.`event_uuid` `uuid`,
		IFNULL(mess.message_id, -1) message_id 
		FROM `tbl_event` eve
		LEFT OUTER JOIN `tbl_event_reminder` ter
		ON eve.event_uuid = ter.event_uuid
		LEFT OUTER JOIN `tbl_reminder_message` trm
		ON ter.reminder_uuid = trm.reminder_uuid
		LEFT OUTER JOIN `tbl_message` mess
		ON trm.message_uuid = mess.message_uuid
		WHERE event_id = :id
		AND eve.customer_id = :customer_id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
		$stmt->execute();
		$event = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;
		//die(print_r($event));
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
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		echo json_encode(array("success"=>"event marked as deleted"));
		
		trackEvent("delete", $id);
		
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function addEvent() {
	session_write_close();
	$request = Slim::getInstance()->request();
	$db = getConnection();
	//die(print_r($_POST));
	$arrFields = array();
	$arrSet = array();
	$table_name = "";
	$table_id = "";
	$debtor_id = "";
	$doctor_name = "";
	$event_kind = "";
	$event_time = "";
	$calendar_id = -1;
	$customer_id = $_SESSION["user_customer_id"];
	$event_calendar_sort_order = 0;	//default calendar
	$arrTo = array();
	$arrToID = array();
	$start_date = passed_var("start_date", "post");
	$event_time = passed_var("event_time", "post");
	$do_not_use_basic = "";
	if (isset($_POST["do_not_use_basic"])) {
		$do_not_use_basic = passed_var("do_not_use_basic", "post");
	}
	$reminder_uuid = uniqid("RE", false);
	$reminder_number = passed_var("reminder_number_0_first", "post");
	$reminder_type = passed_var("reminder_type_0_first", "post");
	$reminder_interval = passed_var("reminder_interval_0_first", "post");
	$reminder_datetime = passed_var("reminder_datetime_0_first", "post");
	$event_date = "";
	$event_dateandtime = $start_date . " " . $event_time . ":00";
	$duration = "";
	if (isset($_POST["duration"])) {
		$duration = passed_var("event_duration", "post");
	}
	$group_id = "";
	$arrGroups = array();
	if (isset($_POST["group_id"])) {
		$group_id = $_POST["group_id"];
		$arrGroups = $group_id;
	}
	$end_date = "";
	$attorney_color = "";
	$event_description = "";
	$reason = "";
	$ten_am = "10:00:00";
	$user_uuid = "";	//for personal calendars
	if ($duration != "") {
		//there is no end date box on the calendar, so we calculate from duration
		$end_date = DateAdd("n", $duration, strtotime($event_dateandtime));
		$end_date = date("Y-m-d H:i:s", $end_date);
	}

	foreach($_POST as $fieldname=>$value) {
		if ($fieldname=="group_id" || $fieldname=="basic_sms_message" || $fieldname=="basic_voice_message") {
			continue;
		}
		//no reminders, no recurrence here, not yet
		//echo $fieldname . "\r\n";
		if ((strpos($fieldname, "reminder_") > -1 || strpos($fieldname, "recurrent_") > -1)  && $fieldname!="reminder_set" ) {
			continue;
		}
		if ($fieldname!="event_description") {
			$value = passed_var($fieldname, "post");
		} else {
			$value = @processHTML($_POST["event_description"]);
			$event_description = $value;
			//clean up placeholders
			$event_description = str_replace("]][[", "]] [[", $event_description);
		}
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="event_id") {
			continue;
		}
		if ($fieldname=="do_not_use_basic") {
			continue;
		}
		if ($fieldname=="assignee" && $value!="") {
			//look up the doctor info
			$code = getProviderInfo($value);
			
			$provider_first_name = trim($code->first_name);
			$provider_last_name = trim($code->last_name);
			$provider_last_name = str_replace("M.D.", "", $provider_last_name);
			$provider_last_name = str_replace("D.C.", "", $provider_last_name);
			$provider_last_name = str_replace("DAOM", "", $provider_last_name);
			$provider_last_name = str_replace("LAC", "", $provider_last_name);
			$provider_last_name = str_replace("L.A.C.", "", $provider_last_name);
			$provider_last_name = str_replace("PH.D.", "", $provider_last_name);
			$provider_last_name = str_replace("P.T.", "", $provider_last_name);
			
			$doctor_name = trim($provider_first_name . " " . $provider_last_name);
			
			if ($reminder_type == "voice") {
				$doctor_name = " Doctor " . ucwords(strtolower(trim($provider_first_name))) . " " . ucwords(strtolower(trim($provider_last_name)));
			}
			if ($reminder_type == "sms") {
				$doctor_name = " Dr " . ucwords(strtolower(trim($provider_first_name))) . " " . ucwords(strtolower(trim($provider_last_name)));
				//die($doctor_name);
			}
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
			$event_date = date("Y-m-d", strtotime($value));
			continue;
		}
		if ($fieldname=="event_time") {
			$event_time = $value;
			continue;
		}
		if ($fieldname=="event_type" && $value!="") {	
			$sql = "SELECT DISTINCT `Code`, `Description` 
			FROM `remind`.`ohrsn`
			WHERE `Code` = '" . $value . "'
			ORDER BY `Description`;";
			
			try {
				$db = getConnection();
				$stmt = $db->prepare($sql);
				$stmt->execute();
				$reason_object = $stmt->fetchObject();
				//die(print_r($reason_object));
				$stmt->closeCursor(); $stmt = null; $db = null;
				$reason = $reason_object->Description;
			} catch(PDOException $e) {
				$error = array("error"=> array("text"=>$e->getMessage()));
				echo json_encode($error);
			}	
			continue;	
		}
		
		if ($fieldname=="number_of_days") {
			continue;
		}
		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "'" . addslashes($value) . "'";
	}
	$language = "English";
	if ($debtor_id!="") {
		if ($debtor_id != "all") {
			$debtor = getDebtorInfo($debtor_id);
			
			$language = $debtor->language;
			$customer_id = $debtor->customer_id;
			$arrFields[] = "`event_first_name`";
			$arrSet[] = "'" . addslashes($debtor->first_name) . "'";
			
			$arrFields[] = "`event_last_name`";
			$arrSet[] = "'" . addslashes($debtor->last_name) . "'";
			
			$phone = $debtor->phone;
			$phone = preg_replace("/[^0-9]/", "", $phone);
			$cellphone = $debtor->cellphone;
			$cellphone = preg_replace("/[^0-9]/", "", $cellphone);
			
			if ($phone=="" && $cellphone!="") {
				$phone = $cellphone;
			}
			
			$arrFields[] = "`event_email`";
			$arrSet[] = "'" . $phone . "'";
		} else {
			$arrFields[] = "`event_first_name`";
			$arrSet[] = "'all'";
			
			$arrFields[] = "`event_last_name`";
			$arrSet[] = "'customers'";
			$arrFields[] = "`event_email`";
			$phone = "all";
			$arrSet[] = "'" . $phone . "'";
		}
	}

	
	$arrFields[] = "`customer_id`";
	$arrSet[] = $customer_id;
	
	if ($start_date!="") {
		$arrFields[] = "`event_dateandtime`";
		$arrSet[] = "'" . $event_dateandtime . "'";
	}
	if ($end_date=="") {
		if ($duration!="") {
			$end_date = DateAdd("n", $duration, $start_date . " " . $event_time);
		}
	}
	if ($end_date!="") {
		$arrFields[] = "`end_date`";
		$arrSet[] = "'" . $end_date . "'";
		$arrFields[] = "`event_end_time`";
		$arrSet[] = "'" . $end_date . "'";
	}
	if ($event_date!="") {
		$arrFields[] = "`event_date`";
		$arrSet[] = "'" . $event_date . "'";
	}
	
	if ($reminder_datetime=="") {
		$date_reminder = date("Y-m-d", strtotime("-1 day", strtotime($event_dateandtime)));
		$reminder_datetime = $date_reminder . " " . $ten_am;
	}
	$table_uuid = uniqid("EV", false);
	$event_uuid = $table_uuid;
	
	$last_updated_date = date("Y-m-d H:i:s");
	
	if ($debtor_id != "all") {
		//message for a single recipient
		//the message is different for voice or text
		$sql = "SELECT *
		FROM `md_reminder`.`tbl_customer`
		WHERE customer_id = :customer_id";
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$cus = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		if ($reminder_type == "voice") {
			$basic_voice_message = $cus->basic_voice_message;
			
			$basic_description = str_replace("[[Contact First Name]]", $debtor->first_name, $basic_voice_message);
			$basic_description = str_replace("[[Contact Last Name]]", $debtor->last_name, $basic_description);
			if (trim($debtor->full_name)=="") {
				$debtor->full_name = trim(trim($debtor->first_name) . " " . trim($debtor->last_name));
			}
			$basic_description = str_replace("[[Contact Full Name]]", $debtor->full_name, $basic_description);
			$basic_description = str_replace("[[Appt Date and Time]] ", date("l", strtotime($start_date)) . " " . date("m/d/Y", strtotime($start_date)) . " at " . $event_time, $basic_description);
			$basic_description = str_replace("[[Company Number]]", $cus->cus_phone, $basic_description);
			$basic_description = str_replace("[[Company Email]]", $cus->cus_email, $basic_description);
			$basic_description = str_replace("[[Company Name]]", $cus->cus_name, $basic_description);
			$basic_description = str_replace("[[Doctor Name]]", $doctor_name, $basic_description);
			
			$message = $basic_description;
			//for now
			//if ($language=="English" || $language=="0") {
			/*
				$message = "This is a reminder message for " . ucwords(strtolower($debtor->first_name . " " . $debtor->last_name)) . ". You have an appointment";
				if ($doctor_name!="") {
					$message .= " with " . $doctor_name;
				}
				$message .= " on " . date("l", strtotime($start_date)) . " " . date("m/d/Y", strtotime($start_date)) . " at " . $event_time;
				$message .= ". To confirm the appointment, please press 1.";
				$message .= "  To cancel the appointment, please press 2.";
				if ($_SESSION["provider_contact_number"]!="") {
					$message .= ".  For Questions please call " . $_SESSION["provider_contact_number"];
				}
			*/
		}
		if ($reminder_type == "sms") {
			//we need a token first
			$prefix1 = chr(rand(65,90));
			$prefix2 = chr(rand(65,90));
			$token = uniqid($prefix1 . $prefix2, true);
			$expiration_date = date("Y-m-d", mktime(0, 0, 0, date("m", strtotime($reminder_datetime))  , date("d", strtotime($reminder_datetime)) + 1, date("Y", strtotime($reminder_datetime))));
			
			$sql = "INSERT INTO md_reminder.tbl_token (`reminder_uuid`, `token`, `expiration_date`, `debtor_id`, `customer_id`)
			VALUES ('" . $reminder_uuid . "', '" . $token . "', '" . $expiration_date . "', '" . $debtor_id . "', '" . $customer_id . "')";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->execute();
			$token_id = $db->lastInsertId();
			$db = null; $stmt = null; 
			
			//$confirm_url = make_gl_url("https://www.ikase.org/remind/api/confirm/" . $token);
			//$cancel_url = make_gl_url("https://www.ikase.org/remind/api/cancel/" . $token);
			
			if (strpos($language, "English")==0 || $language=="0") {
				/*
				$confirm_url = "pls reply Y";
				$cancel_url = "pls reply N";
				
				$message =  "Appt Reminder ";
				if ($doctor_name!="") {
					$message .= "w/ " . $doctor_name;
				}
				$message .= " on " . date("m/d", strtotime($event_dateandtime)) . " " . date("g:ia", strtotime($event_dateandtime));
				if ($_SESSION["provider_contact_number"]!="") {
					$message .= chr(10) . "Questions? " . $_SESSION["provider_contact_number"];
				}
				//$confirm_url = make_bitly_url("https://www.ikase.org/remind/api/confirm/" . $debtor_id);
				$message .= chr(10) . "To confirm," . $confirm_url;
				//$cancel_url = make_bitly_url("https://www.ikase.org/remind/api/cancel/" . $debtor_id);
				$message .= chr(10) . "To cancel," . $cancel_url;
				*/
				$basic_sms_message = $cus->basic_sms_message;
			
				if ($debtor->first_name!="") {
					$basic_description = str_replace("[[Contact First Name]]", $debtor->first_name, $basic_sms_message);
				} else {
					$the_phone = $debtor->phone;
					$the_phone = preg_replace("/[^0-9]/", "", $the_phone);
					$the_cellphone = $debtor->cellphone;
					$the_cellphone = preg_replace("/[^0-9]/", "", $the_cellphone);
					
					if ($the_phone!="" && $the_cellphone=="") {
						$the_cellphone = $the_phone;
					}
					$basic_description = str_replace("[[Contact First Name]]", $the_cellphone, $basic_sms_message);
				}
				$basic_description = str_replace("[[Contact Last Name]]", $debtor->last_name, $basic_description);
				if (trim($debtor->full_name)=="") {
					$debtor->full_name = trim(trim($debtor->first_name) . " " . trim($debtor->last_name));
				}
				$basic_description = str_replace("[[Contact Full Name]]", $debtor->full_name, $basic_description);
				$basic_description = str_replace("[[Appt Date and Time]] ", date("l", strtotime($start_date)) . " " . date("m/d/Y", strtotime($start_date)) . " at " . $event_time, $basic_description);
				$basic_description = str_replace("[[Company Number]]", $cus->cus_phone, $basic_description);
				$basic_description = str_replace("[[Company Email]]", $cus->cus_email, $basic_description);
				$basic_description = str_replace("[[Company Name]]", $cus->cus_name, $basic_description);
				$basic_description = str_replace("[[Doctor Name]]", $doctor_name, $basic_description);
				
				$message = $basic_description;
			} else {
				//echo "lan:<" . $language . ">\r\n";
				//die("Span");
				$confirm_url = "responde Y";
				$cancel_url = "responde N";
			
				$message =  "Recordatorio para la cita ";
				if ($doctor_name!="") {
					$message .= "con el/la " . $doctor_name;
				}
				$message .= " el " . date("m/d", strtotime($event_dateandtime)) . " a las" . date("g:ia", strtotime($event_dateandtime));
				if ($_SESSION["provider_contact_number"]!="") {
					$message .= chr(10) . "Preguntas? Por favor llama " . $_SESSION["provider_contact_number"];
				}
				$message .= chr(10) . "Para confirmar, " . $confirm_url;
				$message .= chr(10) . "Para cancelar, " . $cancel_url;
			}
		}
	}
	$event_name = "Appt " . date("mdY Hi", strtotime($event_dateandtime));
	if ($debtor_id != "all") {
		$event_name = $message;
	}
	$arrFields[] = "`event_name`";
	$arrSet[] = "'" . addslashes($event_name) . "'";
		
	try {
		//single event, even if broadcast
		$sql = "INSERT INTO `tbl_event` (`event_uuid`, " . implode(",", $arrFields) . ") 
			VALUES('" . $table_uuid . "', " . implode(",", $arrSet) . ")";
			 
		//save event
		$db = getConnection();	
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		$new_id = $db->lastInsertId();
		$stmt = null; $db = null;
		
		if ($debtor_id != "all") {
			//insert the personalized message for the debtor
			//clean up placeholders
			$event_description = str_replace("[[Contact First Name]]", $debtor->first_name, $event_description);
			$event_description = str_replace("[[Contact Last Name]]", $debtor->last_name, $event_description);
			if (trim($debtor->full_name)=="") {
				$debtor->full_name = trim(trim($debtor->first_name) . " " . trim($debtor->last_name));
			}
			$event_description = str_replace("[[Contact Full Name]]", $debtor->full_name, $event_description);
			
			if ($do_not_use_basic!="Y"){
				if ($event_description!="") {
					$message .= chr(10) . $event_description;
				}
			} else {
				//do not use basic
				$message = $event_description;
				$confirm_url = "pls reply Y";
				$cancel_url = "pls reply N";
				//$message .= chr(10) . "To confirm," . $confirm_url;
				//$cancel_url = make_bitly_url("https://www.ikase.org/remind/api/cancel/" . $debtor_id);
				$message .= chr(10) . "To unsubscribe," . $cancel_url;
			}
			
			$message_type = "reminder";
			$message_uuid = uniqid("ME", false);
			//message
			$message_SQL = "INSERT INTO `tbl_message` 
			(`message_uuid`, `message_type`, `dateandtime`, `from`, `message_to`, `message`, `original_message`, `customer_id`)
			VALUES('" . $message_uuid . "', '" . $message_type . "', '" . date("Y-m-d H:i:s") . "', 'system', '" . $phone . "', '" . addslashes($message) . "', '" . addslashes($event_description) . "', '" . $customer_id . "')";
			//echo $message_SQL . ";<br />";
			// die($message_SQL);
			$db = getConnection();
			$stmt = $db->prepare($message_SQL);
			$stmt->execute();
			$message_id = $db->lastInsertId();
			$stmt = null; $db = null; 
			
			$reminder_uuid = uniqid("RE", false);
			$event_debtor_uuid = uniqid("ED", false);
			$event_reminder_uuid = uniqid("ER", false);
			$reminder_message_uuid = uniqid("RM", false);
			$event_table_uuid = uniqid("KA", false);
			
			//reminder
			$reminder_SQL = "INSERT INTO `tbl_reminder`
			(`reminder_uuid`, `reminder_debtor_uuid`, `reminder_number`, `reminder_type`, `reminder_interval`, `reminder_span`, `reminder_datetime`, `verified`, `customer_id`) 
			VALUES('" . $reminder_uuid . "', '" . $debtor->uuid . "', '" . $reminder_number . "', '" . $reminder_type . "', '" . $reminder_interval . "', 'minutes', '" . date("Y-m-d H:i:s", strtotime($reminder_datetime)) . "', 'Y', '" . $customer_id . "')";
			//echo $reminder_SQL . ";<br />";
			// die($reminder_SQL);
			$db = getConnection();
			$stmt = $db->prepare($reminder_SQL);
			$stmt->execute();
			$reminder_id = $db->lastInsertId();
			$stmt = null; $db = null; 
			
			if ($reminder_type == "voice") {
				//generate the voice
				voiceReminder($reminder_id, $message_id, "return");
			}
			
			$attribute_2 = "";
			if ($do_not_use_basic=="Y"){
				$attribute_2 = "no_basic_message";
			}
			//event reminder
			$event_reminder_SQL = "INSERT INTO `tbl_event_reminder`
			(`event_reminder_uuid`, `event_uuid`, `reminder_uuid`, `attribute_1`, `attribute_2`, `customer_id`)
			VALUES ('" . $event_reminder_uuid . "', '" . $event_uuid . "', '" . $reminder_uuid . "', '" . $reminder_type . "', '" . $attribute_2 . "', '" . $customer_id . "')";
			//echo $event_reminder_SQL . ";<br />";
			// die($event_reminder_SQL);
			$db = getConnection();
			$stmt = $db->prepare($event_reminder_SQL);
			$stmt->execute();
			$stmt = null; $db = null; 
	
			//reminder message
			$reminder_message_SQL = "INSERT INTO `tbl_reminder_message`
			(`reminder_message_uuid`, `message_uuid`, `reminder_uuid`, `last_update_user`, `customer_id`)
			VALUES ('" . $reminder_message_uuid . "', '" . $message_uuid . "', '" . $reminder_uuid . "', 'system', '" . $customer_id . "')";
			//echo $reminder_message_SQL . ";<br />";
			// die($reminder_message_SQL);
			$db = getConnection();
			$stmt = $db->prepare($reminder_message_SQL);
			$stmt->execute();
			$stmt = null;  $db = null; 
			
			$attribute_1 = "main";
			//now we have to attach the event to the case 
			$sql = "INSERT INTO tbl_event_debtor (`event_debtor_uuid`, `event_uuid`, `debtor_uuid`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $event_table_uuid  ."', '" . $table_uuid . "', '" . $debtor->uuid . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $customer_id . "')";
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			$stmt = null;  $db = null; 
		} else {
			//loop through the debtors
			
			//attach the event to the destination groups
			foreach($arrGroups as $group_id) {
				$sql = "INSERT INTO tbl_event_group 
				(`event_group_uuid`, `event_uuid`, `group_uuid`,  `last_updated_date`, `last_update_user`, `customer_id`)
				SELECT :event_group_uuid, :event_uuid, `group_uuid`, :last_updated_date, :last_update_user, :customer_id
				FROM `tbl_group`
				WHERE group_id = :group_id";
				
				$last_updated_date = date("Y-m-d H:i:s");
				$last_update_user = $_SESSION["user_id"];
				
				$db = getConnection();
				$stmt = $db->prepare($sql); 
				$stmt->bindParam("event_group_uuid", $table_uuid); 
				$stmt->bindParam("event_uuid", $table_uuid); 
				$stmt->bindParam("last_updated_date", $last_updated_date); 
				$stmt->bindParam("last_update_user", $last_update_user); 
				$stmt->bindParam("customer_id", $customer_id); 
				$stmt->bindParam("group_id", $group_id); 
				$stmt->execute();
				$db = null; $stmt = null;
			}
			//get all the debtors for this customer and group
			$sql = "SELECT DISTINCT debt.*
			FROM `tbl_debtor` `debt` ";
			//maybe restricting to certain groups
			if (count($arrGroups) > 0) {
				$sql .= "
				INNER JOIN tbl_debtor_group tdg
				ON debt.debtor_uuid = tdg.debtor_uuid AND tdb.deleted = 'N'
				INNER JOIN tbl_group tg
				ON tdg.group_uuid = tg.group_uuid";
			}
			$sql .= "
			WHERE debt.customer_id = :customer_id
			AND debt.deleted = 'N'
			AND debt.subscribe = 'Y'";
			if (count($arrGroups) > 0) {
				$sql .= "
				AND tg.group_id IN (" . implode(",", $arrGroups) . ")";
			}
			
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("customer_id", $customer_id);
			
			$stmt->execute();
			$debtors = $stmt->fetchAll(PDO::FETCH_OBJ);
			$stmt->closeCursor(); $stmt = null; $db = null;
			
			foreach($debtors as $contact) {
				//each debtor gets personalized message
				$message_description = $event_description;
				//clean up placeholders
				if ($contact->first_name!="") {
					$message_description = str_replace("[[Contact First Name]]", $contact->first_name, $event_description);
				} else {
					$the_phone = $contact->phone;
					$the_phone = preg_replace("/[^0-9]/", "", $the_phone);
					$the_cellphone = $contact->cellphone;
					$the_cellphone = preg_replace("/[^0-9]/", "", $the_cellphone);
					
					if ($the_phone!="" && $the_cellphone=="") {
						$the_cellphone = $the_phone;
					}
					$message_description = str_replace("[[Contact First Name]]", $the_cellphone, $message_description);
				}
				$message_description = str_replace("[[Contact Last Name]]", $contact->last_name, $message_description);
				if (trim($contact->full_name)=="") {
					$contact->full_name = trim(trim($contact->first_name) . " " . trim($contact->last_name));
				}
				$message_description = str_replace("[[Contact Full Name]]", $contact->full_name, $message_description);
				
				//do not use basic
				$message = $message_description;
				$confirm_url = "pls reply Y";
				$cancel_url = "pls reply N";
				//$message .= chr(10) . "To confirm," . $confirm_url;
				//$cancel_url = make_bitly_url("https://www.ikase.org/remind/api/cancel/" . $debtor_id);
				$message .= chr(10) . "To unsubscribe, " . $cancel_url;
			
				$message_uuid = uniqid("ME", false);
				$message_type = "broadcast";

				//message
				$message_SQL = "INSERT INTO `tbl_message` 
				(`message_uuid`, `message_type`, `dateandtime`, `from`, `message_to`, `message`, `original_message`, `customer_id`)
				VALUES('" . $message_uuid . "', '" . $message_type . "', '" . date("Y-m-d H:i:s") . "', 'system', '" . $phone . "', '" . addslashes($message) . "', '" . addslashes($event_description) . "', '" . $customer_id . "')";
				//echo $message_SQL . ";<br />";
				// die($message_SQL);
				$db = getConnection();
				$stmt = $db->prepare($message_SQL);
				$stmt->execute();
				$message_id = $db->lastInsertId();
				$stmt = null; $db = null; 
				
				$reminder_uuid = uniqid("RE", false);
				$event_debtor_uuid = uniqid("ED", false);
				$event_reminder_uuid = uniqid("ER", false);
				$reminder_message_uuid = uniqid("RM", false);
				$event_table_uuid = uniqid("KA", false);
				//if we have their provider, then it's an email
				if ($reminder_type=="sms") {
					if ($contact->provider!="") {
						if ($contact->cellphone!="") {
							if ($contact->employee=="Y") {
								//we can use the gateway instead of nexmo
								$reminder_type = "gateway";
							}
						}
					}
				}
				
				//reminder
				$reminder_SQL = "INSERT INTO `tbl_reminder`
				(`reminder_uuid`, `reminder_debtor_uuid`, `reminder_number`, `reminder_type`, `reminder_interval`, `reminder_span`, `reminder_datetime`, `verified`, `customer_id`) 
				VALUES('" . $reminder_uuid . "', '" . $contact->debtor_uuid . "', '" . $reminder_number . "', '" . $reminder_type . "', '" . $reminder_interval . "', 'minutes', '" . date("Y-m-d H:i:s", strtotime($reminder_datetime)) . "', 'Y', '" . $customer_id . "')";
				//echo $reminder_SQL . ";<br />";
				// die($reminder_SQL);
				$db = getConnection();
				$stmt = $db->prepare($reminder_SQL);
				$stmt->execute();
				$reminder_id = $db->lastInsertId();
				$stmt = null; $db = null; 
				
				if ($reminder_type == "voice") {
					//generate the voice
					voiceReminder($reminder_id, $message_id, "return");
				}
				
				$attribute_2 = "";
				if ($do_not_use_basic=="Y"){
					$attribute_2 = "no_basic_message";
				}
				//event reminder
				$event_reminder_SQL = "INSERT INTO `tbl_event_reminder`
				(`event_reminder_uuid`, `event_uuid`, `reminder_uuid`, `attribute_1`, `attribute_2`, `customer_id`)
				VALUES ('" . $event_reminder_uuid . "', '" . $event_uuid . "', '" . $reminder_uuid . "', '" . $reminder_type . "', '" . $attribute_2 . "', '" . $customer_id . "')";
				//echo $event_reminder_SQL . ";<br />";
				// die($event_reminder_SQL);
				$db = getConnection();
				$stmt = $db->prepare($event_reminder_SQL);
				$stmt->execute();
				$stmt = null; $db = null; 
		
				//reminder message
				$reminder_message_SQL = "INSERT INTO `tbl_reminder_message`
				(`reminder_message_uuid`, `message_uuid`, `reminder_uuid`, `last_update_user`, `customer_id`)
				VALUES ('" . $reminder_message_uuid . "', '" . $message_uuid . "', '" . $reminder_uuid . "', 'system', '" . $customer_id . "')";
				//echo $reminder_message_SQL . ";<br />";
				// die($reminder_message_SQL);
				$db = getConnection();
				$stmt = $db->prepare($reminder_message_SQL);
				$stmt->execute();
				$stmt = null;  $db = null; 
				
				
				$attribute_1 = "main";
				//now we have to attach the event to the debtor 
				$sql = "INSERT INTO tbl_event_debtor (`event_debtor_uuid`, `event_uuid`, `debtor_uuid`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $table_uuid  ."', '" . $table_uuid . "', '" . $contact->debtor_uuid . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $customer_id . "')";
				$db = getConnection();
				$stmt = $db->prepare($sql);  
				$stmt->execute();
				$stmt = null;  $db = null; 
				
				//now we have to attach the reminder to the debtor 
				$sql = "INSERT INTO tbl_event_debtor (`event_debtor_uuid`, `event_uuid`, `debtor_uuid`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $table_uuid  ."', '" . $table_uuid . "', '" . $contact->debtor_uuid . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $customer_id . "')";
				$db = getConnection();
				$stmt = $db->prepare($sql);  
				$stmt->execute();
				$stmt = null;  $db = null; 
			}
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
	
	$request = Slim::getInstance()->request();
	$db = getConnection();
	$arrSet = array();
	$where_clause = "";
	$table_id = "";
	$injury_id = "";
	$event_kind = "";
	$event_description = "";
	$end_date = "";
	$do_not_use_basic = "";
	$doctor_name = "";
	$customer_id = $_SESSION['user_customer_id'];
	if (isset($_POST["do_not_use_basic"])) {
		$do_not_use_basic = passed_var("do_not_use_basic", "post");
	}
	$reminder_id = passed_var("reminder_id_0_first", "post");
	$reminder_number = passed_var("reminder_number_0_first", "post");
	$reminder_type = passed_var("reminder_type_0_first", "post");
	$reminder_interval = passed_var("reminder_interval_0_first", "post");
	$reminder_datetime = passed_var("reminder_datetime_0_first", "post");
	$event_time = passed_var("event_time", "post");
	$event_dateandtime = passed_var("start_date", "post") . " " . $event_time;
	$duration = passed_var("event_duration", "post");
	if ($duration != "") {
		$end_date = DateAdd("n", $duration, strtotime($event_dateandtime));

		$end_date = date("Y-m-d H:i:s", $end_date);
	}
	$table_name = "event";		
	foreach($_POST as $fieldname=>$value) {
		//no reminders, no recurrence here, not yet
		if (strpos($fieldname, "reminder_") > -1 || strpos($fieldname, "recurrent_") > -1 ) {
			continue;
		}
		if ($fieldname!="event_description") {
			$value = passed_var($fieldname, "post");
		} else {
			$value = @processHTML($_POST["event_description"]);
			$event_description = $value;
		}
		$fieldname = str_replace("Input", "", $fieldname);
		
		if ($fieldname=="do_not_use_basic" || $fieldname=="basic_sms_message" || $fieldname=="basic_voice_message") {
			continue;
		}
		if ($fieldname=="user_id" || $fieldname=="number_of_days") {
			continue;
		}
		if ($fieldname=="start_date" || $fieldname=="event_time") {
			continue;
		}
		if ($fieldname=="debtor_id") {
			$debtor_id = $value;
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
		if ($fieldname=="assignee" && $value!="") {
			//look up the doctor info
			$code = getProviderInfo($value);
			
			$provider_first_name = trim($code->first_name);
			$provider_last_name = trim($code->last_name);
			$provider_last_name = str_replace("M.D.", "", $provider_last_name);
			$provider_last_name = str_replace("D.C.", "", $provider_last_name);
			$provider_last_name = str_replace("DAOM", "", $provider_last_name);
			$provider_last_name = str_replace("LAC", "", $provider_last_name);
			$provider_last_name = str_replace("L.A.C.", "", $provider_last_name);
			$provider_last_name = str_replace("PH.D.", "", $provider_last_name);
			$provider_last_name = str_replace("P.T.", "", $provider_last_name);
			
			$doctor_name = trim($provider_first_name . " " . $provider_last_name);
			
			if ($reminder_type == "voice") {
				$doctor_name = " Doctor " . ucwords(strtolower(trim($provider_first_name))) . " " . ucwords(strtolower(trim($provider_last_name)));
			}
			if ($reminder_type == "sms") {
				$doctor_name = " Dr " . ucwords(strtolower(trim($provider_first_name))) . " " . ucwords(strtolower(trim($provider_last_name)));
				//die($doctor_name);
			}
			continue;
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
					$stmt = $db->prepare($sql);
					$stmt->execute();
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
		if ($fieldname=="event_id") {
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
	$sql .= " AND `tbl_" . $table_name . "`.customer_id = '" . $customer_id . "'";

	try {		
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		$stmt = null; $db = null;
		
		$my_event = getEventInfo($table_id);
		$last_updated_date = date("Y-m-d H:i:s");
		
		echo json_encode(array("success"=>$table_id)); 
		
		trackEvent("update", $table_id);
		
		//get current reminder information to check if the reminder type changed
		//every event in this system WILL have a reminder by definition
		
		$reminder = getReminderInfo($reminder_id);
		$debtor = getDebtorInfo($debtor_id);
		
		$reminder_datetime = date("Y-m-d H:i:s", strtotime($reminder_datetime));
		$sql = "UPDATE `md_reminder`.`tbl_reminder`
		SET reminder_type = :reminder_type,
		reminder_interval = :reminder_interval,
		reminder_datetime = :reminder_datetime
		WHERE reminder_id = :reminder_id
		AND customer_id = :customer_id";
		//echo $sql . "\r\n";
		$db = getConnection();
		$stmt = $db->prepare($sql); 
		$stmt->bindParam("reminder_type", $reminder_type);
		$stmt->bindParam("reminder_interval", $reminder_interval);
		$stmt->bindParam("reminder_datetime", $reminder_datetime);
		$stmt->bindParam("reminder_id", $reminder_id);
		$stmt->bindParam("customer_id", $customer_id); 
		$stmt->execute();
		$stmt = null; $db = null;
		
		//do we need a new voice generated?
		//if ($reminder->reminder_type != $reminder_type) {
			//rebuild the message
			//get the current message id
			$message_id = $my_event->message_id;
			//update the message
			if ($reminder_type == "voice") {
				//for now
				//if ($language=="English" || $language=="0") {
					$message = "This is a reminder message for " . ucwords(strtolower($debtor->first_name . " " . $debtor->last_name)) . ". You have an appointment";
					if ($doctor_name!="") {
						$message .= " with " . $doctor_name;
					}
					$message .= " on " . date("l", strtotime($event_dateandtime)) . " " . date("m/d/Y", strtotime($event_dateandtime)) . " at " . date("g:ia", strtotime($event_dateandtime));
					$message .= ". To confirm the appointment, please press 1.";
					$message .= "  To cancel the appointment, please press 2.";
					if ($_SESSION["provider_contact_number"]!="") {
						$message .= ".  For Questions please call " . $_SESSION["provider_contact_number"];
					}
			}
			if ($reminder_type == "sms") {
				//we need a token first
				$prefix1 = chr(rand(65,90));
				$prefix2 = chr(rand(65,90));
				$token = uniqid($prefix1 . $prefix2, true);
				$expiration_date = date("Y-m-d", mktime(0, 0, 0, date("m", strtotime($reminder_datetime))  , date("d", strtotime($reminder_datetime)) + 1, date("Y", strtotime($reminder_datetime))));
				
				$sql = "INSERT INTO `md_reminder`.`tbl_token` 
				(`reminder_uuid`, `token`, `expiration_date`, `debtor_id`, `customer_id`)
				VALUES ('" . $reminder_uuid . "', '" . $token . "', '" . $expiration_date . "', '" . $debtor_id . "', '" . $customer_id . "')";
				
				$db = getConnection();
				$stmt = $db->prepare($sql);
				$stmt->execute();
				$token_id = $db->lastInsertId();
				$db = null; $stmt = null; 
				
				//$confirm_url = make_gl_url("https://www.ikase.org/remind/api/confirm/" . $token);
				//$cancel_url = make_gl_url("https://www.ikase.org/remind/api/cancel/" . $token);
				
				if (strpos($language, "English")==0 || $language=="0") {
					$confirm_url = "pls reply Y";
					$cancel_url = "pls reply N";
					
					$message =  "Appt Reminder ";
					if ($doctor_name!="") {
						$message .= "w/ " . $doctor_name;
					}
					$message .= " on " . date("m/d", strtotime($event_dateandtime)) . " " . date("g:ia", strtotime($event_dateandtime));
					if ($_SESSION["provider_contact_number"]!="") {
						$message .= chr(10) . "Questions? " . $_SESSION["provider_contact_number"];
					}
					//$confirm_url = make_bitly_url("https://www.ikase.org/remind/api/confirm/" . $debtor_id);
					$message .= chr(10) . "To confirm," . $confirm_url;
					//$cancel_url = make_bitly_url("https://www.ikase.org/remind/api/cancel/" . $debtor_id);
					$message .= chr(10) . "To cancel," . $cancel_url;
				} else {
					//echo "lan:<" . $language . ">\r\n";
					//die("Span");
					$confirm_url = "responde Y";
					$cancel_url = "responde N";
				
					$message =  "Recordatorio para la cita ";
					if ($doctor_name!="") {
						$message .= "con el/la " . $doctor_name;
					}
					$message .= " el " . date("m/d", strtotime($event_dateandtime)) . " a las" . date("g:ia", strtotime($event_dateandtime));
					if ($_SESSION["provider_contact_number"]!="") {
						$message .= chr(10) . "Preguntas? Por favor llama " . $_SESSION["provider_contact_number"];
					}
					$message .= chr(10) . "Para confirmar, " . $confirm_url;
					$message .= chr(10) . "Para cancelar, " . $cancel_url;
				}
				//
			}
			
			if ($do_not_use_basic!="Y"){
				$event_name = $message;	//"Appt " . date("mdY", strtotime($event_dateandtime));
				if ($event_description!="") {
					$message .= chr(10) . strip_tags($event_description);
				}
			} else {
				$event_name = "Appt " . date("mdY Hi", strtotime($event_dateandtime));
				//do not use basic
				$message = strip_tags($event_description);
			}
			
			$sql = "UPDATE `md_reminder`.`tbl_message`
			SET message = :message
			WHERE message_id = :message_id
			AND customer_id = :customer_id";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("message", $message);
			$stmt->bindParam("message_id", $message_id);
			$stmt->bindParam("customer_id", $customer_id);
			$stmt->execute();
			$db = null; $stmt = null; 
			
			if ($reminder_type == "voice") {
				//generate the voice
				voiceReminder($reminder_id, $message_id, "return");			
			}
		//}
	} catch(PDOException $e) {	
		echo $sql . "<br />";
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
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		$my_event = getEventInfo($table_id);
		$last_updated_date = date("Y-m-d H:i:s");
		
		if (count($arrToID) > 0) {
			attachRecipients('event', $my_event->uuid, $last_updated_date, $arrToID, 'to', $db);
		}
		$db = null;
		
		echo json_encode(array("success"=>$table_id)); 
		
		trackEvent("update", $table_id);
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function confirmEvent($token) {
	//this is called by the user when replying
	$status = "confirmed";
	statusEvent($token, $status);
}
function cancelReminder() {
	session_write_close();
	$reminder_id = passed_var("reminder_id", "post");
	$customer_id = $_SESSION["user_customer_id"];
	try {
		$sql = "UPDATE tbl_reminder
			SET cancelled = 'Y',
			deleted = 'Y'
			WHERE 1
			AND reminder_id = :reminder_id
			AND customer_id = :customer_id";
			
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("reminder_id", $reminder_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$stmt = null; $db = null;
		
		echo "<h1>Reminder cancelled.  Thank you.</h1>";
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function cancelEvent($token) {
	session_write_close();
	//this is called by the user when replying
	$status = "cancelled";
	statusEvent($token, $status);
}
function cancelAppt() {
	session_write_close();
	$event_id = passed_var("event_id", "post");
	$reminder_id = passed_var("reminder_id", "post");
	$customer_id = $_SESSION["user_customer_id"];
	try {
		$sql = "UPDATE tbl_event eve
			SET event_status = 'cancelled'
			WHERE 1
			AND eve.event_id = :event_id
			AND eve.customer_id = :customer_id";
			
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("event_id", $event_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$stmt = null; $db = null;
		
		$sql = "UPDATE tbl_reminder
			SET cancelled = 'Y',
			deleted = 'Y'
			WHERE 1
			AND reminder_id = :reminder_id
			AND customer_id = :customer_id";
			
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("reminder_id", $reminder_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$stmt = null; $db = null;
		
		echo "<h1>Appointment cancelled.  Thank you.</h1>";
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function cancelApptByJudge() {
	$judge = passed_var("doctor", "post");
	$event_date = passed_var("event_date", "post");
	$customer_id = $_SESSION["user_customer_id"];
	try {
		$sql = "UPDATE tbl_event eve
			SET event_status = 'cancelled'
			WHERE 1
			AND eve.judge = :judge
			AND CAST(eve.event_dateandtime AS DATE) = :event_date
			AND eve.customer_id = :customer_id";
			
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("judge", $judge);
		$stmt->bindParam("event_date", $event_date);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$stmt = null; $db = null;
		
		$sql = "UPDATE tbl_reminder
			SET cancelled = 'Y',
			deleted = 'Y'
			WHERE reminder_uuid IN (
				SELECT reminder_uuid
				FROM md_reminder.tbl_event_reminder ter
				INNER JOIN md_reminder.tbl_event eve
				ON ter.event_uuid = eve.event_uuid
				WHERE 1
				AND eve.judge = :judge
				AND CAST(eve.event_dateandtime AS DATE) = :event_date
				AND eve.customer_id = :customer_id
			)";
			
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("judge", $judge);
		$stmt->bindParam("event_date", $event_date);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$stmt = null; $db = null;
		
		echo "<h1>" . $judge . " - Appointments cancelled.  Thank you.</h1>";
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function statusEvent($token, $status) {
	
	$tomorrow = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d") + 1, date("Y")));
	//echo $tomorrow;
	try {
		//check the token
		$sql = "SELECT token_id, debtor_id, customer_id
		FROM `md_reminder`.`tbl_token`
		WHERE `token` = :token
		AND `used_date` = '0000-00-00 00:00:00'
		AND `expiration_date` >= :tomorrow";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("token", $token);
		$stmt->bindParam("tomorrow", $tomorrow);
		$stmt->execute();
		$token = $stmt->fetchObject();
		
		$stmt->closeCursor();  $stmt = null; $db = null;
		//die(print_r($token));
		//if it's valid,,we have debtor_id, customer_id
		if (!is_object($token)) {
			echo '{"error":{"text":"no token"}}'; 
			echo "<H1>Link has expired</H1>";
			die();
		}
		
		$token_id = $token->token_id;
		$debtor_id = $token->debtor_id;
		$customer_id = $token->customer_id;
		
		//we've open the token, mark it
		$sql = "UPDATE tbl_token
		SET used_date = :used_date
		WHERE token_id = :token_id";
		
		$used_date = date("Y-m-d H:i:s");
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("used_date", $used_date);
		$stmt->bindParam("token_id", $token_id);
		$stmt->execute();
		
		$sql = "UPDATE `md_reminder`.`tbl_debtor` debt, `tbl_event_debtor` ted, tbl_event eve
		SET event_status = :event_status
		WHERE debtor_id = :debtor_id
		AND debt.debtor_uuid = ted.debtor_uuid
		AND ted.event_uuid = eve.event_uuid
		AND debt.customer_id = :customer_id";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("event_status", $status);
		$stmt->bindParam("debtor_id", $debtor_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		
		$stmt = null; $db = null;
		
		//echo json_encode(array("success"=>true, "status"=>$status)); 
		echo "<h1>Appointment " . $status . ".  Thank you.</h1>";
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function moveEvent() {
	$request = Slim::getInstance()->request();
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
		
		$stmt = null; $db = null;
		echo json_encode(array("success"=>$_POST["id"], "end_date"=>$end_date)); 
		
		trackEvent("move", $_POST["id"]);
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}

function trackEvent($operation, $event_id) {
	$sql = "INSERT INTO tbl_event_track (`user_uuid`, `user_logon`, `operation`, `event_id`, `event_uuid`, `event_name`, `event_date`, `event_duration`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `full_address`, `assignee`, `event_title`, `event_email`, `event_hour`, `event_type`, `event_type_abbr`, `event_from`, `event_priority`, `end_date`, `completed_date`, `customer_id`, `deleted`)
	SELECT '" . $_SESSION['user_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $operation . "', `event_id`, `event_uuid`, `event_name`, `event_date`, `event_duration`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `full_address`, `assignee`, `event_title`, `event_email`, `event_hour`, `event_type`, `event_type_abbr`, `event_from`, `event_priority`, `end_date`, `completed_date`, `customer_id`, `deleted`
	FROM tbl_event
	WHERE 1
	AND event_id = " . $event_id . "
	AND customer_id = " . $_SESSION['user_customer_id'] . "
	LIMIT 0, 1";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
	
		$stmt->execute();
		
		$new_id = $db->lastInsertId();
		
		$stmt = null; $db = null;
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function voiceReminder($reminder_id, $message_id, $return_die = "die") {
	session_write_close();
	$customer_id = $_SESSION["user_customer_id"];
	
	$url = "http://kustomweb.xyz/ikase_voice/spoken/remind/" . $customer_id ."/mp3/output_reminder_" . $reminder_id . "_" . $message_id . ".mp3";
	
	//die($url);
	if (!url_exists($url) || $return_die == "return") {
		//die("not exists");
		//get the message
		$mess = getMessageInfo($message_id);
		$message = strip_tags($mess->message);
		//generate it
		$url_voice = "http://kustomweb.xyz/ikase_voice/make_mp3.php?folder=remind&customer_id=" . $customer_id . "&reminder_id=" . $reminder_id . "&message_id=" . $message_id . "&message=" . urlencode($message);
		//die(json_encode(array("url"=>$url_voice)));
		$fields_string = array();

		//open connection
		$ch = curl_init();
		//set the url, number of POST vars, POST data
		curl_setopt($ch, CURLOPT_URL, $url_voice);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, false); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_COOKIEFILE, "cookies.txt");
		curl_setopt($ch, CURLOPT_POST, count($fields_string));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
		$result = curl_exec($ch);
		
		usleep(800);
	}
	
	if ($return_die=="die") {
		die(json_encode(array("success"=>true, "url"=>$url)));
	} else {
		//echo $url;
		return json_encode(array("success"=>true, "url"=>$url));
	}
}
function sentReminder() {
	$event_id = passed_var("event_id", "post");
	$customer_id = $_SESSION["user_customer_id"];
	
	try {
		$sql = "SELECT rem.reminder_id, rem.reminder_uuid, mess.* 
		FROM md_reminder.tbl_event eve
		INNER JOIN md_reminder.tbl_event_reminder ter
		ON eve.event_uuid = ter.event_uuid
		INNER JOIN md_reminder.tbl_reminder rem
		ON ter.reminder_uuid = rem.reminder_uuid
		INNER JOIN md_reminder.tbl_reminder_message trm
		ON rem.reminder_uuid = trm.reminder_uuid
		INNER JOIN md_reminder.tbl_message mess
		ON trm.message_uuid = mess.message_uuid 
		WHERE eve.event_id = :event_id
		AND eve.customer_id = :customer_id";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("event_id", $event_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$rem = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		$reminder_id = $rem->reminder_id;
		$reminder_uuid = $rem->reminder_uuid;
		$message_uuid = $rem->message_uuid;
		$message_to = $rem->message_to;
		$message = $rem->message;
		
		//update the statuses
		$sql = "UPDATE tbl_reminder
		SET verified = 'Y',
		sent = 'Y',
		buffered = 'Y'
		WHERE reminder_id = :reminder_id
		AND customer_id = :customer_id";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("reminder_id", $reminder_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$stmt = null; $db = null;
		
		//insert a remindersent
		$sql = "INSERT INTO `md_reminder`.`tbl_remindersent`
		(`reminderbuffer_id`, `recipients`, `subject`, `message`, `message_uuid`, `reminder_uuid`, `timestamp`, `customer_id`)
		VALUES ('-99', '" . $message_to . "', 'sent sms', '" . addslashes($message) . "', '" . $message_uuid . "', '" . $reminder_uuid . "', '" . date("Y-m-d H:i:s") . "', '" . $customer_id . "')";
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>true, "reminder_id"=>$reminder_id));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	//update the reminder
	//insert reminder_sent
}
function verifyReminders() {
	$new_verified = passed_var("new_verified", "post");
	if (isset($_POST["event_date"])) {
		$date = passed_var("event_date", "post");
	} else {
		$date = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d") + 1, date("Y")));
	}
	
	$reminder_interval = 1;
	if (date("N", strtotime($date))=="1") {
		//monday, remind on friday
		$reminder_interval = 3;
	}
	$reminder_date = date("Y-m-d", strtotime("-" . $reminder_interval . " day", strtotime($date)));
	$ten_am = "14:00:00";
	
	$sql = "UPDATE tbl_reminder rem
			SET rem.`verified` = :new_verified
			WHERE CAST(`reminder_datetime` AS DATE) =:reminder_date";
	
	try {
		//die("me");
		//$event = getEventInfo($id);
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("reminder_date", $reminder_date);
		$stmt->bindParam("new_verified", $new_verified);
		$stmt->execute();
		//track now
		//trackEvent("update", $id);
		
		$stmt = null; $db = null;
		echo json_encode(array("success"=>true, "new_verified"=>$new_verified, "reminder_date"=>$reminder_date));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function verifyRemindersByJudge() {
	$new_verified = passed_var("new_verified", "post");
	$judge = passed_var("doctor", "post");
	$event_date = passed_var("event_date", "post");
	$customer_id = $_SESSION["user_customer_id"];
	
	$sql = "UPDATE tbl_reminder 
			SET `verified` = :new_verified
			WHERE reminder_uuid IN (
				SELECT ter.reminder_uuid
				FROM md_reminder.tbl_event_reminder ter
				INNER JOIN md_reminder.tbl_event eve
				ON ter.event_uuid = eve.event_uuid
				WHERE 1
				AND eve.judge = :judge
				AND CAST(eve.event_dateandtime AS DATE) = :event_date
				AND eve.customer_id = :customer_id
			)";
	
	try {
		//die($sql);
		//$event = getEventInfo($id);
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("new_verified", $new_verified);
		$stmt->bindParam("judge", $judge);
		$stmt->bindParam("event_date", $event_date);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		//track now
		//trackEvent("update", $id);
		
		$stmt = null; $db = null;
		echo json_encode(array("success"=>true, "new_verified"=>$new_verified));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function verifyRemindersByID() {
	$new_verified = passed_var("new_verified", "post");
	$ids = passed_var("ids", "post");
	$customer_id = $_SESSION["user_customer_id"];
	$sql = "UPDATE tbl_reminder rem
			SET rem.`verified` = :new_verified
			WHERE rem.reminder_id IN (" . $ids . ")
			AND rem.customer_id = :customer_id";
	
	try {
		//die($sql);
		//$event = getEventInfo($id);
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("new_verified", $new_verified);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		//track now
		//trackEvent("update", $id);
		
		$stmt = null; $db = null;
		echo json_encode(array("success"=>true, "new_verified"=>$new_verified));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function verifyReminder() {
	$id = passed_var("reminder_id", "post");
	$new_verified = passed_var("new_verified", "post");
	$customer_id = $_SESSION["user_customer_id"];
	$sql = "UPDATE tbl_reminder rem
			SET rem.`verified` = :new_verified
			WHERE `reminder_id`=:id
			AND rem.customer_id = :customer_id";
	
	try {
		//die("me");
		//$event = getEventInfo($id);
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->bindParam("new_verified", $new_verified);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		//track now
		//trackEvent("update", $id);
		
		$stmt = null; $db = null;
		echo json_encode(array("success"=>true, "new_verified"=>$new_verified, "id"=>$id));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function changeRemindersByID() {
	$new_reminder_date = passed_var("new_reminder_date", "post");
	$new_reminder_date = date("Y-m-d", strtotime($new_reminder_date)) . " 14:00:00";
	$ids = passed_var("ids", "post");
	$customer_id = $_SESSION["user_customer_id"];
	$sql = "UPDATE tbl_reminder rem
			SET rem.`reminder_datetime` = :new_reminder_date
			WHERE rem.reminder_id IN (" . $ids . ")
			AND rem.customer_id = :customer_id";
	
	try {
		//die($sql);
		//$event = getEventInfo($id);
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("new_reminder_date", $new_reminder_date);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		//track now
		//trackEvent("update", $id);
		
		$stmt = null; $db = null;
		echo json_encode(array("success"=>true, "new_reminder_date"=>date("Y-m-d H:iA", strtotime($new_reminder_date))));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
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
		
		$stmt = null; $db = null;
		echo json_encode(array("success"=>"Event date updated", "id"=>$id));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getReminderDateTime() {
	session_write_close();
	
	$event_dateandtime = passed_var("date", "post");
	$reminder_interval = passed_var("interval", "post");
	$reminder_span = passed_var("span", "post");
	
	$reminder_datetime = date("D M jS, Y g:iA", strtotime($event_dateandtime . " - " . $reminder_interval . " " . $reminder_span));
	
	echo $reminder_datetime;
}
function getEventProvider($code) {
	session_write_close();
	
	$sql = "SELECT * FROM remind.ohproviders
	WHERE `Code` = :code";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("code", $code);
		$stmt->execute();
		$provider = $stmt->fetchObject();
		$db = null;
		
		return $provider;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getMessageInfo($message_id) {
	$sql = "SELECT * FROM md_reminder.tbl_message
	WHERE `message_id` = :message_id";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("message_id", $message_id);
		$stmt->execute();
		$message = $stmt->fetchObject();
		$db = null;
		
		return $message;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
?>
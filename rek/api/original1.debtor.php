<?php
$app->get('/debtors', authorize('user'), 'getDebtors');
$app->get('/debtor/:debtor_id', authorize('user'), 'getDebtor');

$app->get('/scheduleds', authorize('user'), 'getScheduledDebtors');
$app->get('/tomorrows', authorize('user'), 'getTomorrowDebtors');
$app->get('/debtors/nextday/:start', authorize('user'), 'getNextDayDebtors');
$app->get('/debtors/previousday/:start', authorize('user'), 'getPreviousDayDebtors');
$app->get('/scheduledbydate/:start', authorize('user'), 'getDebtorsByDate');
$app->get('/debtors/bygroup/:group_id', authorize('user'), 'getGroupDebtors');
$app->get('/debtors/groupcount/:group_id', authorize('user'), 'getGroupDebtorsCount');

$app->get('/appointmentsbydate/:start', authorize('user'), 'getAppointmentsByDate');
$app->get('/debtors/summary', authorize('user'), 'summaryDebtors');
$app->get('/debtors/search/:search_term', authorize('user'), 'searchDebtors');
$app->get('/appointments/search/:search_term', authorize('user'), 'searchAppointments');
$app->get('/debtors/lookup/:search_term', authorize('user'), 'lookupDebtors');
$app->get('/debtors/providers/:search_term', authorize('user'), 'searchProviders');

//reports
$app->get('/debtors/attempts', authorize('user'), 'reportDebtorsAttempts');
$app->get('/debtors/searchattempts/:search_term', authorize('user'), 'searchAttempts');
$app->get('/debtors/attemptsbydate/:report_date', authorize('user'), 'reportDebtorsAttemptsByDate');

//crud
$app->post('/debtor/add', authorize('user'), 'addDebtor');
$app->post('/debtor/update', authorize('user'), 'updateDebtor');
$app->post('/debtor/delete', authorize('user'), 'deleteDebtor');
$app->post('/debtor/unsubscribe', authorize('user'), 'unsubscribeDebtor');
$app->post('/debtor/resubscribe', authorize('user'), 'resubscribeDebtor');
$app->post('/debtor/switchlanguage', authorize('user'), 'switchDebtorLanguage');

function reportDebtorsAttemptsByDate($report_date) {
	reportDebtorsAttempts($report_date);
}
function searchAttempts($search_term) {
	reportDebtorsAttempts("", $search_term);
}
function reportDebtorsAttempts($report_date = "", $search_term = "") {
	session_write_close();

	$search_term = str_replace("_", "", $search_term);
	//Full name search
	$full_name_search = str_replace("_", "", $search_term);
	$full_name_search = str_replace(" ", "", $full_name_search);
	$arrSearch = explode("-", $full_name_search);
	if (count($arrSearch)==3 && strpos($search_term, "-") > 0) {
		$search_term = str_replace("-", "/", $search_term);
	}
	
	$sql = "SELECT debt.*, debt.`debtor_id` `id`, debt.`debtor_uuid` `uuid`,
	IFNULL(rems.attempt_date, '') attempt_date,
	IFNULL(rems.sent_count, '') sent_count,
	IFNULL(events_count, '0') scheduled_events,
	IFNULL(next_appt, '') next_appt,
	IFNULL(next_rem.next_attempt_date, '') next_attempt_date
	FROM `tbl_debtor` debt 
	
	LEFT OUTER JOIN (
		SELECT debtor_uuid, COUNT(tb.event_id) events_count, MIN(tb.event_dateandtime) next_appt
		FROM `tbl_event_debtor` tbd
		INNER JOIN `tbl_event` tb
		ON tbd.event_uuid = tb.event_uuid
		WHERE CAST(tb.event_dateandtime AS DATE) > '" . date("Y-m-d") . "'
		AND tb.deleted = 'N'
		GROUP BY debtor_uuid
	) `debtor_events`
	ON debt.debtor_uuid = debtor_events.debtor_uuid
	
	LEFT OUTER JOIN (
		SELECT ted.debtor_uuid, MIN(tr.`reminder_datetime`) next_attempt_date
		FROM `tbl_event_debtor` ted
		
		INNER JOIN tbl_event_reminder ter
		ON ted.event_uuid = ter.event_uuid
		
		INNER JOIN `tbl_event` tb
		ON tbd.event_uuid = tb.event_uuid
		
		INNER JOIN tbl_reminder tr
		ON ter.reminder_uuid = tr.reminder_uuid
		WHERE CAST(tr.`reminder_datetime` AS DATE) > '" . date("Y-m-d") . "'
		AND tb.deleted = 'N'
		GROUP BY ted.debtor_uuid
	) next_rem
	ON debtor.debtor_uuid = next_rem.debtor_uuid
	";
	if ($report_date!="") {
		$sql .= "
		INNER JOIN `tbl_event_debtor` ted
		ON debt.debtor_uuid = ted.debtor_uuid
		
		INNER JOIN tbl_event_reminder ter
		ON ted.event_uuid = ter.event_uuid
		
		INNER JOIN tbl_event eve
		ON ted.event_uuid = eve.event_uuid
		
		INNER JOIN tbl_remindersent trs
		ON ter.reminder_uuid = trs.reminder_uuid
		
		INNER JOIN tbl_reminder tr
		ON ter.reminder_uuid = tr.reminder_uuid";
	}
	$sql .= "
	INNER JOIN (
		
		SELECT ted.debtor_uuid, MAX(trs.`timestamp`) attempt_date, COUNT(trs.remindersent_id) sent_count, GROUP_CONCAT(DISTINCT tr.reminder_type) attempt_method
		
		FROM `tbl_event_debtor` ted
		INNER JOIN tbl_event_reminder ter
		ON ted.event_uuid = ter.event_uuid
		INNER JOIN tbl_event eve
		ON ted.event_uuid = eve.event_uuid
		INNER JOIN tbl_remindersent trs
		ON ter.reminder_uuid = trs.reminder_uuid
		INNER JOIN tbl_reminder tr
		ON ter.reminder_uuid = tr.reminder_uuid
		WHERE eve.deleted = 'N'
		GROUP BY ted.debtor_uuid
	) rems
	ON debt.debtor_uuid = rems.debtor_uuid
	WHERE 1 
	AND debt.deleted = 'N'
	AND eve.deleted = 'N'
	AND debt.customer_id = 6";
	
	if ($report_date!="") {
		$sql .= "
		AND CAST(trs.`timestamp` AS DATE) = '" . $report_date . "'";
	}
	if ($search_term!="") {
		$sql .= " AND (
			debt.first_name LIKE '%" . addslashes($search_term) . "%'
			OR debt.last_name LIKE '%" . addslashes($search_term) . "%'
			OR REPLACE(CONCAT(TRIM(debt.`first_name`), TRIM(debt.`last_name`)), ' ', '') LIKE '" . addslashes($full_name_search) . "%'";
		$sql .= ")";
	}
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$debtors = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;

		echo json_encode($debtors);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function lookupDebtors($search_term) {
	searchDebtors($search_term, false);
}
function searchAppointments($search_term, $blnFullSearch = true) {
	$reminder_date = "";
	$event_date = "";
	$scheduled_join = "LEFT OUTER JOIN";
	if (isset($_SESSION["reminder_date"])) {
		$scheduled_join = "INNER JOIN";
		$reminder_date = $_SESSION["reminder_date"];
		$_SESSION["reminder_date"] = "";
		unset($_SESSION["reminder_date"]);
	}
	if (isset($_SESSION["event_date"])) {
		$scheduled_join = "INNER JOIN";
		$event_date = $_SESSION["event_date"];
		$_SESSION["event_date"] = "";
		unset($_SESSION["event_date"]);
	}
//die(print_r($_SESSION));

	session_write_close();
	$customer_id = $_SESSION["user_customer_id"];
	$search_term = str_replace("_", "", $search_term);
	//die("date: " . strtotime($search_term));
	//Full name search
	$full_name_search = str_replace("_", "", $search_term);
	$full_name_search = str_replace(" ", "", $full_name_search);
	//die($full_name_search);
	$arrSearch = explode("-", $full_name_search);
	//die(print_r($arrSearch));
	//die("position : " . strpos($full_name_search, "-"));
	if (count($arrSearch)==3 && strpos($search_term, "-") > 0) {
		$search_term = str_replace("-", "/", $search_term);
		//die($full_name_search);
	}
	/*
	$sql = "SELECT DISTINCT debtor.*,
		debtor.`debtor_id` `id`, debtor.`debtor_uuid` `uuid`,
		#IFNULL(events_count, '0') scheduled_events,
		'0' scheduled_events,
		IFNULL(rem.reminder_id, '0') next_appt,
		IFNULL(rem.reminder_datetime, '') attempt_date,
		'TBD' sent_count,
		IFNULL(rem.reminder_datetime, '') next_attempt_date,
		IFNULL(rem.reminder_id, '0') next_attempt_id,
		IFNULL(rem.verified, 'N') next_attempt_verified,
		IFNULL(rem.sent, 'N') next_attempt_sent,
        IFNULL(rem.`reminder_type`, '') `next_reminder_type`,
        IFNULL(mess.`message_id`, '') `next_message_id`,
		IFNULL(mess.`message`, '') `next_message`,
		IFNULL(eve.event_id, '') next_appt_id,
		IFNULL(eve.judge, '') next_appt_doctor,
		IFNULL(eve.event_status, '') next_appt_status,
		IFNULL(eve.event_description, '') next_appt_description,
		'TBD' recipient_response,
        'TBD' delivery_status,
		'TBD' last_hang
	FROM tbl_debtor debtor 
	" . $scheduled_join . " tbl_reminder rem
	ON `debtor`.debtor_uuid = rem.reminder_debtor_uuid
	" . $scheduled_join . " tbl_event_reminder ter
	ON `rem`.reminder_uuid = ter.reminder_uuid
	" . $scheduled_join . " tbl_event eve
	ON `ter`.event_uuid = eve.event_uuid
	" . $scheduled_join . " tbl_reminder_message trm
	ON `rem`.reminder_uuid = trm.reminder_uuid
	" . $scheduled_join . " tbl_message mess
	ON `trm`.message_uuid = mess.message_uuid
	WHERE debtor.deleted = 'N'
	AND debtor.customer_id = :customer_id";
	if ($event_date!="") {
		$sql .= " AND CAST(eve.event_dateandtime AS DATE) = :event_date";
	}
	if ($search_term!="") {
		$sql .= " AND (
			debtor.first_name LIKE '%" . addslashes($search_term) . "%'
			OR debtor.last_name LIKE '%" . addslashes($search_term) . "%'
			OR REPLACE(CONCAT(TRIM(debtor.`first_name`), TRIM(debtor.`last_name`)), ' ', '') LIKE '" . addslashes($full_name_search) . "%'";
		$sql .= ")";
	}
	$sql .= "
	ORDER BY debtor.last_name ASC, debtor.first_name ASC";
	*/
	$sql = "SELECT debtor.*,
		debtor.`debtor_id` `id`, debtor.`debtor_uuid` `uuid`,
		'0' scheduled_events,
		IFNULL(rem.reminder_id, '0') next_appt,
		IFNULL(rem.reminder_datetime, '') attempt_date,
		'TBD' sent_count,
		IFNULL(rem.reminder_datetime, '') next_attempt_date,
		IFNULL(rem.reminder_id, '0') next_attempt_id,
		IFNULL(rem.verified, 'N') next_attempt_verified,
		IFNULL(rem.sent, 'N') next_attempt_sent,
        IFNULL(rem.`reminder_type`, '') `next_reminder_type`,
        IFNULL(rem.`message_id`, '') `next_message_id`,
		IFNULL(rem.`message`, '') `next_message`,
		IFNULL(rem.event_id, '') next_appt_id,
		IFNULL(rem.judge, '') next_appt_doctor,
		IFNULL(rem.event_status, '') next_appt_status,
		IFNULL(rem.event_description, '') next_appt_description,
		'TBD' recipient_response,
        'TBD' delivery_status,
		'TBD' last_hang

	FROM tbl_debtor debtor
LEFT OUTER JOIN (
	SELECT rem.reminder_debtor_uuid, 
		rem.reminder_id,
		rem.reminder_datetime,
		'TBD' sent_count,
		rem.verified,
		rem.sent,
        rem.`reminder_type`,
        mess.`message_id`,
		mess.`message`,
		eve.event_id,
		eve.judge,
		eve.event_status,
		eve.event_description,
		'TBD' recipient_response,
        'TBD' delivery_status,
		'TBD' last_hang
	FROM  tbl_reminder rem
	INNER JOIN tbl_event_reminder ter
	ON `rem`.reminder_uuid = ter.reminder_uuid
	INNER JOIN tbl_event eve
	ON `ter`.event_uuid = eve.event_uuid
	INNER JOIN tbl_reminder_message trm
	ON `rem`.reminder_uuid = trm.reminder_uuid
	INNER JOIN tbl_message mess
	ON `trm`.message_uuid = mess.message_uuid
    WHERE rem.customer_id = :customer_id
    AND rem.reminder_debtor_uuid !=''
	";
	if ($event_date!="") {
		$sql .= " AND CAST(eve.event_dateandtime AS DATE) = :event_date";
	}
	$sql .= "
	) rem
    ON debtor.debtor_uuid = rem.reminder_debtor_uuid
	WHERE 1 ";
	if ($search_term!="") {
		$sql .= " AND (
			debtor.first_name LIKE '%" . addslashes($search_term) . "%'
			OR debtor.last_name LIKE '%" . addslashes($search_term) . "%'
			OR REPLACE(CONCAT(TRIM(debtor.`first_name`), TRIM(debtor.`last_name`)), ' ', '') LIKE '" . addslashes($full_name_search) . "%'";
		$sql .= ")";
	}
	$sql .= "
    ORDER BY last_name ASC, first_name ASC
    ";
	try {
		//die($sql);
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$debtors = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;

		echo json_encode($debtors);
	} catch(PDOException $e) {
		//die($sql);
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function searchDebtors($search_term, $blnFullSearch = true) {
	$reminder_date = "";
	$event_date = "";
	if (isset($_SESSION["reminder_date"])) {
		$reminder_date = $_SESSION["reminder_date"];
		$_SESSION["reminder_date"] = "";
		unset($_SESSION["reminder_date"]);
	}
	if (isset($_SESSION["event_date"])) {
		$event_date = $_SESSION["event_date"];
		$_SESSION["event_date"] = "";
		unset($_SESSION["event_date"]);
	}
//die(print_r($_SESSION));

	session_write_close();
	$search_term = str_replace("_", "", $search_term);
	//die("date: " . strtotime($search_term));
	//Full name search
	$full_name_search = str_replace("_", "", $search_term);
	$full_name_search = str_replace(" ", "", $full_name_search);
	//die($full_name_search);
	$arrSearch = explode("-", $full_name_search);
	//die(print_r($arrSearch));
	//die("position : " . strpos($full_name_search, "-"));
	if (count($arrSearch)==3 && strpos($search_term, "-") > 0) {
		$search_term = str_replace("-", "/", $search_term);
		//die($full_name_search);
	}
	//die($full_name_search . "\r\n" . date("Y-m-d", strtotime($full_name_search)));
	$sql = "SELECT debt.*,
	debt.`debtor_id` `id`, debt.`debtor_uuid` `uuid`";
	if ($blnFullSearch) {
		$sql .= ",
				IFNULL(rems.sent_last_date, '') attempt_date,
				IFNULL(rems.sent_count, '0') sent_count,
				IFNULL(rems.attempt_method, '') attempt_method,
				IFNULL(events_count, '0') scheduled_events,
				IFNULL(next_appt, '') next_appt,
				IFNULL(rems.attempt_date, '') attempt_date,
				IFNULL(rems.sent_count, '') sent_count,
				IFNULL(next_rem.next_attempt_date, '') next_attempt_date,
				IFNULL(rem.reminder_id, '0') next_attempt_id,
				IFNULL(rem.verified, 'N') next_attempt_verified,
				IFNULL(rem.`reminder_type`, '') `next_reminder_type`,
				IFNULL(rem.`message_id`, '') `next_message_id`,
				IFNULL(rem.`message`, '') `next_message`,
				IFNULL(eve.event_id, '') next_appt_id,
				IFNULL(eve.judge, '') next_appt_doctor,
				IFNULL(eve.event_status, '') next_appt_status,
				IFNULL(eve.event_description, '') next_appt_description,
				IFNULL(last_incoming.recipient_response, IFNULL(response, '')) recipient_response,
				IFNULL(deliveries.`status`, '') delivery_status,
				IF(last_hangs.recipient_response IS NULL, '0', '1') last_hang";
	}
	
	$sql .= "
	FROM `tbl_debtor` debt";
	if ($blnFullSearch) {
		$sql .= "
	LEFT OUTER JOIN (
		SELECT debtor_uuid, COUNT(tb.event_id) events_count, 
		MIN(tb.event_id) next_appt_id, MIN(tb.event_dateandtime) next_appt
		FROM `tbl_event_debtor` tbd
		INNER JOIN `tbl_event` tb
		ON tbd.event_uuid = tb.event_uuid
		WHERE tb.deleted = 'N'";
		if ($event_date!="") {
			$sql .= " 
			AND CAST(tb.event_dateandtime AS DATE) = '" . $event_date . "'";
		}
		$sql .= " 
		GROUP BY debtor_uuid
		) `debtor_events`
		ON debt.debtor_uuid = debtor_events.debtor_uuid
		
		LEFT OUTER JOIN tbl_event eve
		ON `debtor_events`.next_appt_id = eve.event_id
		
		LEFT OUTER JOIN (
		
			SELECT ted.debtor_uuid, MAX(trs.`timestamp`) sent_last_date, MAX(trs.`timestamp`) attempt_date, 
			COUNT(trs.remindersent_id) sent_count, GROUP_CONCAT(DISTINCT tr.reminder_type) attempt_method
			FROM `tbl_event_debtor` ted
			
			INNER JOIN tbl_event_reminder ter
			ON ted.event_uuid = ter.event_uuid
			
			INNER JOIN `tbl_event` tb
			ON ted.event_uuid = tb.event_uuid
			
			INNER JOIN tbl_remindersent trs
			ON ter.reminder_uuid = trs.reminder_uuid
			
			INNER JOIN tbl_reminder tr
			ON ter.reminder_uuid = tr.reminder_uuid
			WHERE tb.deleted = 'N'
			GROUP BY ted.debtor_uuid
		) rems
		ON debt.debtor_uuid = rems.debtor_uuid
		
		LEFT OUTER JOIN (
			SELECT ted.debtor_uuid, MIN(tr.`reminder_id`) next_attempt_id, MIN(tr.`reminder_datetime`) next_attempt_date
			FROM `tbl_event_debtor` ted
			
			INNER JOIN tbl_event_reminder ter
			ON ted.event_uuid = ter.event_uuid
			
			INNER JOIN tbl_reminder tr
			ON ter.reminder_uuid = tr.reminder_uuid
			
			INNER JOIN tbl_event eve
			ON ted.event_uuid = eve.event_uuid
			
			WHERE 1";
		if ($reminder_date!="") {
			$sql .= " 
			AND CAST(tr.`reminder_datetime` AS DATE) = '" . $reminder_date . "'";
		}
		$sql .= " AND eve.deleted = 'N'
			GROUP BY ted.debtor_uuid
		) next_rem
		ON debt.debtor_uuid = next_rem.debtor_uuid
		
		LEFT OUTER JOIN (
			SELECT cr.*, cm.`message_id`, crm.`message_uuid`, cm.`message_to`, cm.`message`
			FROM `md_reminder`.`tbl_reminder` cr
			INNER JOIN `md_reminder`.`tbl_reminder_message` crm
			ON cr.`reminder_uuid` = crm.`reminder_uuid`
			INNER JOIN `md_reminder`.`tbl_message` cm
			ON crm.`message_uuid` = cm.`message_uuid`
		) rem
		ON next_rem.next_attempt_id = rem.reminder_id
		
		LEFT OUTER JOIN (
			SELECT debtor_uuid, recipient_response 
			FROM md_reminder.tbl_incoming
			WHERE file_name = 'get_digits'";
		if ($reminder_date!="") {
			$sql .= " 
			AND CAST(plivo_date AS DATE) = '" . $reminder_date . "'";
		}
		$sql .= ") last_incoming
		ON debt.debtor_uuid = last_incoming.debtor_uuid
		
		LEFT OUTER JOIN (
			SELECT debtor_uuid, recipient_response 
			FROM md_reminder.tbl_incoming
			WHERE file_name = 'hangup'";
		if ($reminder_date!="") {
			$sql .= " 
			AND CAST(plivo_date AS DATE) = '" . $reminder_date . "'";
		}
		$sql .= " ) last_hangs
		ON debt.debtor_uuid = last_hangs.debtor_uuid
		
		LEFT OUTER JOIN (
			SELECT cells.debtor_uuid, res.response 
			FROM md_reminder.tbl_response res
			INNER JOIN (
				SELECT debtor_uuid, CONCAT('1', REPLACE(REPLACE(REPLACE(cellphone, '(', ''), ')', ''), '-', '')) cellphone
				FROM md_reminder.tbl_debtor
			) cells
			ON res.response_from = cells.cellphone
			WHERE 1";
		if ($reminder_date!="") {
			$sql .= "
			AND CAST(dateandtime AS DATE) = '" . $reminder_date . "'";
		}
		$sql .= ") last_response
		ON debt.debtor_uuid = last_response.debtor_uuid 
		
		LEFT OUTER JOIN (
			SELECT del.`status`, rem.reminder_uuid
			FROM md_reminder.tbl_delivery del
			INNER JOIN md_reminder.tbl_remindersent sent
			ON del.nexmo_id = sent.response_id
			INNER JOIN md_reminder.tbl_reminder rem
			ON sent.reminder_uuid = rem.reminder_uuid
			INNER JOIN md_reminder.tbl_event_reminder ter
			ON rem.reminder_uuid = ter.reminder_uuid
			INNER JOIN md_reminder.tbl_event eve
			ON ter.event_uuid = eve.event_uuid
			WHERE rem.sent = 'Y'
			AND rem.deleted = 'N'";
		if ($reminder_date!="") {
			$sql .= "
			AND CAST(sent.`timestamp` AS DATE) = '" . $reminder_date . "'";
		}
		$sql .= ") deliveries
		ON rem.reminder_uuid = deliveries.reminder_uuid";
	}
	
 	$sql .="
	WHERE 1";
	if ($search_term!="") {
		$sql .= " AND (
			debt.first_name LIKE '%" . addslashes($search_term) . "%'
			OR debt.last_name LIKE '%" . addslashes($search_term) . "%'
			OR REPLACE(CONCAT(TRIM(debt.`first_name`), TRIM(debt.`last_name`)), ' ', '') LIKE '" . addslashes($full_name_search) . "%'";
		$sql .= ")";
	}
	if ($event_date!="") {
		$sql .= " 
		AND CAST(eve.event_dateandtime AS DATE) = '" . $event_date . "'";
	}
	$sql .= " AND debt.deleted = 'N'
	AND debt.customer_id = " . $_SESSION['user_customer_id'] . "
	";
	
	if ($reminder_date!="") {
		$sql .= " AND CAST(next_rem.next_attempt_date AS DATE) = '" . $reminder_date . "'
		AND 
		" . DOCTORS_FILTER;
		
		$sql .= " 
	ORDER BY IFNULL(eve.judge, '') ASC, IFNULL(next_appt, '') ASC";
	} else {
		$sql .= " 
	ORDER BY debt.`last_name` ASC, debt.`first_name` ASC";
	}
	
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$debtors = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;

		echo json_encode($debtors);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function summaryDebtors() {
	session_write_close();
	
	$sql = "SELECT IFNULL(tbd.attribute, 'available') debtor_status, COUNT(debtor.`debtor_id`) debtor_count
	FROM `tbl_debtor` debtor 
	LEFT OUTER JOIN `tbl_batch_debtor` tbd
	ON debtor.debtor_uuid = tbd.debtor_uuid AND tbd.deleted = 'N'
	LEFT OUTER JOIN `tbl_batch` tb
	ON tbd.batch_uuid = tb.batch_uuid AND tb.deleted = 'N'
	WHERE 1
	AND debtor.deleted = 'N'
	AND debtor.customer_id = " . $_SESSION['user_customer_id'] . "
	GROUP BY tbd.attribute";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$debtors = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;

		echo json_encode($debtors);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getNextDayDebtors($start) {
	$interval = 1;
	//if it's a saturday, next day is a monday
	if (date("N", strtotime($start))==6) {
		$interval = 2;
	}
	$start = DateAdd("d", $interval, strtotime($start));
	$start = date("Y-m-d", $start);
	getDebtorsByDate($start);
}
function getPreviousDayDebtors($start) {
	$interval = -1;
	//if it's a monday, previous day is saturday
	if (date("N", strtotime($start))==1) {
		$interval = -2;
	}
	$start = DateAdd("d", $interval, strtotime($start));

	$start = date("Y-m-d", $start);
	getDebtorsByDate($start);
}
function getTomorrowDebtors() {
	$event_date = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d") + 3, date("Y")));
	if (date("N", strtotime($event_date))==7) {
		$event_date = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d") + 4, date("Y")));
	}
	$reminder_date = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d") + 1, date("Y")));

	getDebtors(true, $event_date, $reminder_date);
}
function getScheduledDebtors() {
	getDebtors(true, "", "");
}
function getGroupDebtors($group_id) {
	session_write_close();
	if ($group_id=="_") {
		$group_id = "";
	}
	$arrGroups = explode(",", $group_id);
	foreach($arrGroups as $gindex=>$group_id) {
		if ($group_id == "") {
			unset($arrGroups[$gindex]);
		}
	}
	$customer_id = $_SESSION["user_customer_id"];
	
	//get all the debtors for this customer and group
	$sql = "SELECT debt.debtor_uuid
	FROM tbl_debtor debt";
	//maybe restricting to certain groups
	if (count($arrGroups) > 0) {
		$sql .= "
		INNER JOIN tbl_debtor_group tdg
		ON debt.debtor_uuid = tdg.debtor_uuid AND tdg.deleted = 'N'
		INNER JOIN tbl_group tg
		ON tdg.group_uuid = tg.group_uuid";
	}
	$sql .= "
	WHERE debt.customer_id = :customer_id
	AND debt.deleted = 'N'
	AND subscribe = 'Y'";
	if (count($arrGroups) > 0) {
		$sql .= "
		AND tg.group_id IN (" . implode(",", $arrGroups) . ")";
	}
	try {
		//die($sql);
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		
		$stmt->execute();
		$debtors = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		echo json_encode($debtors);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
	}
}
function getGroupDebtorsCount($group_id) {
	session_write_close();
	if ($group_id=="_") {
		$group_id = "";
	}
	$arrGroups = explode(",", $group_id);
	foreach($arrGroups as $gindex=>$group_id) {
		if ($group_id == "") {
			unset($arrGroups[$gindex]);
		}
	}
	$customer_id = $_SESSION["user_customer_id"];
	
	//get all the debtors for this customer and group
	$sql = "SELECT COUNT(DISTINCT debt.debtor_id) debtor_count
	FROM tbl_debtor debt";
	//maybe restricting to certain groups
	if (count($arrGroups) > 0) {
		$sql .= "
		INNER JOIN tbl_debtor_group tdg
		ON debt.debtor_uuid = tdg.debtor_uuid AND tdg.deleted = 'N'
		INNER JOIN tbl_group tg
		ON tdg.group_uuid = tg.group_uuid";
	}
	$sql .= "
	WHERE debt.customer_id = :customer_id
	AND debt.deleted = 'N'
	AND subscribe = 'Y'";
	if (count($arrGroups) > 0) {
		$sql .= "
		AND tg.group_id IN (" . implode(",", $arrGroups) . ")";
	}
	try {
		//die($sql);
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		
		$stmt->execute();
		$count = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		echo json_encode(array("success"=>true, "count"=>$count->debtor_count));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
	}
}
function getDebtorsByDate($start) {
	$startdatetime = strtotime($start);
	$interval = 1;
	//if it's a monday
	if (date("N", $startdatetime)==1) {
		$interval = 3;
	}
	$reminder_date = date("Y-m-d", mktime(0, 0, 0, date("m", $startdatetime)  , date("d", $startdatetime) - $interval, date("Y", $startdatetime)));
	//die($reminder_date);
	getDebtors(true, $start, $reminder_date);
}
function getAppointmentsByDate($start) {
	$startdatetime = strtotime($start);
	getAppointments(false, $start);
}
function getAppointments($blnScheduleds = false, $event_date = "") {
	$_SESSION["event_date"] = $event_date;
	session_write_close();
	$customer_id = $_SESSION["user_customer_id"];
	$scheduled_join = "LEFT OUTER JOIN ";
	if ($event_date!="") {
		$scheduled_join = "INNER JOIN ";
	}
	
	$sql = "SELECT DISTINCT debtor.*,
		debtor.`debtor_id` `id`, debtor.`debtor_uuid` `uuid`,
		'TBD' scheduled_events,
		IFNULL(eve.event_dateandtime, '0') next_appt,
		IFNULL(rems.attempt_date, '') attempt_date,
		IFNULL(rems.sent_count, '0') sent_count,
		IFNULL(rem.reminder_datetime, '') next_attempt_date,
		IFNULL(rem.reminder_id, '0') next_attempt_id,
		IFNULL(rem.verified, 'N') next_attempt_verified,
		IFNULL(rem.sent, 'N') next_attempt_sent,
        IFNULL(rem.`reminder_type`, '') `next_reminder_type`,
        IFNULL(mess.`message_id`, '') `next_message_id`,
		IFNULL(mess.`message`, '') `next_message`,
		IFNULL(eve.event_id, '') next_appt_id,
		IFNULL(eve.judge, '') next_appt_doctor,
		IFNULL(eve.event_status, '') next_appt_status,
		IFNULL(eve.event_description, '') next_appt_description,
		IFNULL(responses.response, IFNULL(plivo_responses.plivo_response, '')) recipient_response,
        IFNULL(deliveries.`status`, '') delivery_status,
		'TBD' last_hang
	FROM tbl_debtor debtor 
	" . $scheduled_join . " tbl_reminder rem
	ON `debtor`.debtor_uuid = rem.reminder_debtor_uuid
	" . $scheduled_join . " tbl_event_reminder ter
	ON `rem`.reminder_uuid = ter.reminder_uuid
	" . $scheduled_join . " tbl_event eve
	ON ter.event_uuid = eve.event_uuid
	" . $scheduled_join . " tbl_reminder_message trm
	ON `rem`.reminder_uuid = trm.reminder_uuid
	" . $scheduled_join . " tbl_message mess
	ON `trm`.message_uuid = mess.message_uuid
	
	LEFT OUTER JOIN (
		SELECT debt.debtor_uuid, MAX(trs.`timestamp`) attempt_date, 
		COUNT(trs.remindersent_id) sent_count, GROUP_CONCAT(DISTINCT rem.reminder_type) attempt_method
		
		FROM md_reminder.tbl_debtor debt

		INNER JOIN md_reminder.tbl_reminder rem
		ON debt.debtor_uuid = rem.reminder_debtor_uuid
		
		INNER JOIN tbl_remindersent trs
		ON rem.reminder_uuid = trs.reminder_uuid
				
		INNER JOIN tbl_event_reminder ter
		ON rem.reminder_uuid = ter.reminder_uuid
				
		INNER JOIN tbl_event eve
		ON ter.event_uuid = eve.event_uuid        
				
		WHERE debt.customer_id = :customer_id
		AND eve.deleted = 'N'
		AND eve.customer_id = :customer_id
		AND CAST(eve.event_dateandtime AS DATE) = :event_date
		GROUP BY debt.debtor_uuid
	) rems
	ON debtor.debtor_uuid = rems.debtor_uuid
	
	LEFT OUTER JOIN (
		SELECT del.`status`, rem.reminder_uuid
		FROM md_reminder.tbl_delivery del
		INNER JOIN md_reminder.tbl_remindersent sent
		ON del.nexmo_id = sent.response_id
		INNER JOIN md_reminder.tbl_reminder rem
		ON sent.reminder_uuid = rem.reminder_uuid
		INNER JOIN md_reminder.tbl_event_reminder ter
		ON rem.reminder_uuid = ter.reminder_uuid
		INNER JOIN md_reminder.tbl_event eve
		ON ter.event_uuid = eve.event_uuid
		WHERE rem.sent = 'Y'
		AND rem.deleted = 'N'
		AND eve.deleted = 'N'";
		if ($event_date!="") {
			$sql .= " 
			AND CAST(eve.event_dateandtime AS DATE) = :event_date";
		}
		$sql .= "
		AND rem.customer_id = :customer_id
    ) deliveries
	ON rem.reminder_uuid = deliveries.reminder_uuid
	
	LEFT OUTER JOIN (
		SELECT rem.reminder_uuid, res.response
		FROM `md_reminder`.tbl_remindersent trs
		INNER JOIN `md_reminder`.tbl_reminder rem
		ON trs.reminder_uuid = rem.reminder_uuid
		
		INNER JOIN `md_reminder`.tbl_response res
		ON trs.reply_response_id = res.response_id
		
		INNER JOIN md_reminder.tbl_event_reminder ter
		ON rem.reminder_uuid = ter.reminder_uuid
		INNER JOIN md_reminder.tbl_event eve
		ON ter.event_uuid = eve.event_uuid
		
		WHERE 1
		AND rem.customer_id = :customer_id
		AND eve.deleted = 'N'
		AND CAST(eve.event_dateandtime AS DATE) = :event_date
	) responses
	ON rem.reminder_uuid = responses.reminder_uuid
	
	LEFT OUTER JOIN (
		SELECT rem.reminder_uuid, res.message plivo_response
		FROM `md_reminder`.tbl_remindersent trs
		INNER JOIN `md_reminder`.tbl_reminder rem
		ON trs.reminder_uuid = rem.reminder_uuid
		
		INNER JOIN `md_reminder`.tbl_plivoresponse res
		ON trs.reply_response_id = res.response_id
		
		INNER JOIN md_reminder.tbl_event_reminder ter
		ON rem.reminder_uuid = ter.reminder_uuid
		INNER JOIN md_reminder.tbl_event eve
		ON ter.event_uuid = eve.event_uuid
		
		WHERE 1
		AND rem.customer_id = :customer_id
		AND eve.deleted = 'N'
		AND CAST(eve.event_dateandtime AS DATE) = :event_date
	) plivo_responses
	ON rem.reminder_uuid = plivo_responses.reminder_uuid
	
	WHERE debtor.deleted = 'N'
	AND eve.customer_id = :customer_id";
	if ($event_date!="") {
		$sql .= " AND CAST(eve.event_dateandtime AS DATE) = :event_date";
	}
	if ($_SESSION['user_customer_id']!='7') {
		//list by event
		$sql = "SELECT DISTINCT 
			eve.event_dateandtime next_appt,
			eve.event_id next_appt_id,
			eve.judge next_appt_doctor,
			IF(rem.cancelled = 'Y', 'cancelled', eve.event_status) next_appt_status,
			eve.event_description next_appt_description,
			debtor.*,
			rem.`reminder_id` `id`, rem.`reminder_uuid` `uuid`,
			'TBD' scheduled_events,
			rem.reminder_datetime attempt_date,
			IFNULL(rems.sent_count, '0') sent_count,
			rem.reminder_datetime next_attempt_date,
			rem.reminder_id next_attempt_id,
			rem.verified next_attempt_verified,
			rem.sent next_attempt_sent,
			rem.`reminder_type` `next_reminder_type`,
			mess.`message_id` `next_message_id`,
			mess.`message` `next_message`,
			IFNULL(responses.response, IFNULL(plivo_responses.plivo_response, '')) recipient_response,
			IFNULL(deliveries.`status`, IFNULL(plivo_deliveries.`status`, '')) delivery_status,
			'TBD' last_hang
		FROM tbl_event eve
		INNER JOIN  tbl_event_reminder ter
		ON eve.event_uuid = ter.event_uuid
		
		INNER JOIN  tbl_reminder rem
		ON ter.reminder_uuid = `rem`.reminder_uuid
		
		INNER JOIN tbl_debtor debtor 
		ON `debtor`.debtor_uuid = rem.reminder_debtor_uuid
		
		INNER JOIN  tbl_reminder_message trm
		ON `rem`.reminder_uuid = trm.reminder_uuid
		INNER JOIN  tbl_message mess
		ON `trm`.message_uuid = mess.message_uuid
		
		LEFT OUTER JOIN (
			SELECT debt.debtor_uuid, MAX(trs.`timestamp`) attempt_date, 
			COUNT(trs.remindersent_id) sent_count, GROUP_CONCAT(DISTINCT rem.reminder_type) attempt_method
			
			FROM md_reminder.tbl_debtor debt
	
			INNER JOIN md_reminder.tbl_reminder rem
			ON debt.debtor_uuid = rem.reminder_debtor_uuid
			
			INNER JOIN tbl_remindersent trs
			ON rem.reminder_uuid = trs.reminder_uuid
					
			INNER JOIN tbl_event_reminder ter
			ON rem.reminder_uuid = ter.reminder_uuid
					
			INNER JOIN tbl_event eve
			ON ter.event_uuid = eve.event_uuid        
					
			WHERE debt.customer_id = :customer_id
			AND eve.deleted = 'N'
			AND eve.customer_id = :customer_id
			
			GROUP BY debt.debtor_uuid
		) rems
		ON debtor.debtor_uuid = rems.debtor_uuid
		
		LEFT OUTER JOIN (
			SELECT del.`status`, rem.reminder_uuid
			FROM md_reminder.tbl_delivery del
			INNER JOIN md_reminder.tbl_remindersent sent
			ON del.nexmo_id = sent.response_id
			INNER JOIN md_reminder.tbl_reminder rem
			ON sent.reminder_uuid = rem.reminder_uuid
			INNER JOIN md_reminder.tbl_event_reminder ter
			ON rem.reminder_uuid = ter.reminder_uuid
			INNER JOIN md_reminder.tbl_event eve
			ON ter.event_uuid = eve.event_uuid
			WHERE rem.sent = 'Y'
			AND rem.deleted = 'N' 
				AND CAST(eve.event_dateandtime AS DATE) = :event_date AND rem.customer_id = :customer_id
		) deliveries
		ON rem.reminder_uuid = deliveries.reminder_uuid
		
		LEFT OUTER JOIN (
			SELECT del.plivo_status `status`, rem.reminder_uuid
			FROM (
				SELECT DISTINCT 'delivered' plivo_status, plivo_message_uuid
                FROM md_reminder.tbl_plivoresponse 
                WHERE CAST(dateandtime AS DATE) = :event_date
                AND (plivo_status = 'sent' || plivo_status = 'delivered')
            ) `del`
			INNER JOIN md_reminder.tbl_remindersent sent
			ON del.plivo_message_uuid = sent.response_id
			INNER JOIN md_reminder.tbl_reminder rem
			ON sent.reminder_uuid = rem.reminder_uuid
			INNER JOIN md_reminder.tbl_event_reminder ter
			ON rem.reminder_uuid = ter.reminder_uuid
			INNER JOIN md_reminder.tbl_event eve
			ON ter.event_uuid = eve.event_uuid
			WHERE rem.sent = 'Y'
			AND rem.deleted = 'N' 
				AND CAST(eve.event_dateandtime AS DATE) = :event_date AND rem.customer_id = :customer_id
		) plivo_deliveries
		ON rem.reminder_uuid = plivo_deliveries.reminder_uuid
		
		LEFT OUTER JOIN (
			SELECT rem.reminder_uuid, res.response
			FROM `md_reminder`.tbl_remindersent trs
			INNER JOIN `md_reminder`.tbl_reminder rem
			ON trs.reminder_uuid = rem.reminder_uuid
			
			INNER JOIN `md_reminder`.tbl_response res
			ON trs.reply_response_id = res.response_id
			
			INNER JOIN md_reminder.tbl_event_reminder ter
			ON rem.reminder_uuid = ter.reminder_uuid
			INNER JOIN md_reminder.tbl_event eve
			ON ter.event_uuid = eve.event_uuid
			
			WHERE 1
			AND rem.customer_id = :customer_id
			AND CAST(eve.event_dateandtime AS DATE) = :event_date
		) responses
		ON rem.reminder_uuid = responses.reminder_uuid
				
		LEFT OUTER JOIN (
			SELECT rem.reminder_uuid, res.message plivo_response
			FROM `md_reminder`.tbl_remindersent trs
			INNER JOIN `md_reminder`.tbl_reminder rem
			ON trs.reminder_uuid = rem.reminder_uuid
			
			INNER JOIN `md_reminder`.tbl_plivoresponse res
			ON trs.reply_response_id = res.response_id
			
			INNER JOIN md_reminder.tbl_event_reminder ter
			ON rem.reminder_uuid = ter.reminder_uuid
			INNER JOIN md_reminder.tbl_event eve
			ON ter.event_uuid = eve.event_uuid
			
			WHERE 1
			AND rem.customer_id = :customer_id
			AND eve.deleted = 'N'
			AND CAST(eve.event_dateandtime AS DATE) = :event_date
		) plivo_responses
		ON rem.reminder_uuid = plivo_responses.reminder_uuid
		
		WHERE debtor.deleted = 'N'
		AND eve.deleted = 'N'
		AND debtor.customer_id = :customer_id
		AND eve.customer_id = :customer_id 
		AND CAST(eve.event_dateandtime AS DATE) = :event_date";
		
		//die($sql);
	}
	try {
		//
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("event_date", $event_date);
		$stmt->execute();
		$debtors = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;

		echo json_encode($debtors);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getDebtors($blnScheduleds = false, $event_date = "", $reminder_date = "") {
	$_SESSION["reminder_date"] = $reminder_date;
	$_SESSION["event_date"] = $event_date;
	session_write_close();
	$customer_id = $_SESSION["user_customer_id"];
	
	$scheduled_join = "LEFT OUTER JOIN ";
	if ($blnScheduleds) {
		$scheduled_join = "INNER JOIN ";
	}
	/*
	SELECT debtor_id, debtor_uuid, `language`, first_name, last_name, 
        MAX(email) email, MAX(phone) phone, MAX(cellphone) cellphone,
        customer_id
        FROM `tbl_debtor`
		WHERE 1
		AND debtor.deleted = 'N'
		AND debtor.customer_id = '" . $_SESSION['user_customer_id'] . "'
		GROUP BY debtor_id
		
	SELECT ted.debtor_uuid, MAX(trs.`timestamp`) attempt_date, 
		COUNT(trs.remindersent_id) sent_count, GROUP_CONCAT(DISTINCT tr.reminder_type) attempt_method
		FROM `tbl_event_debtor` ted
		
		INNER JOIN tbl_event_reminder ter
		ON ted.event_uuid = ter.event_uuid
	
		INNER JOIN tbl_remindersent trs
		ON ter.reminder_uuid = trs.reminder_uuid
		
		INNER JOIN tbl_reminder tr
		ON ter.reminder_uuid = tr.reminder_uuid
		
		INNER JOIN tbl_event eve
		ON ted.event_uuid = eve.event_uuid
		WHERE eve.deleted = 'N'
		AND eve.customer_id = :customer_id
		GROUP BY ted.debtor_uuid
	*/
	$sql = "SELECT DISTINCT debtor.*,
		debtor.`debtor_id` `id`, debtor.`debtor_uuid` `uuid`,
		IFNULL(events_count, '0') scheduled_events,
		IFNULL(next_appt, '') next_appt,
		IFNULL(rems.attempt_date, '') attempt_date,
		IFNULL(rems.sent_count, '') sent_count,
		IFNULL(next_rem.next_attempt_date, '') next_attempt_date,
		IFNULL(rem.reminder_id, '0') next_attempt_id,
		IFNULL(rem.verified, 'N') next_attempt_verified,
		IFNULL(rem.sent, 'N') next_attempt_sent,
        IFNULL(rem.`reminder_type`, '') `next_reminder_type`,
        IFNULL(rem.`message_id`, '') `next_message_id`,
		IFNULL(rem.`message`, '') `next_message`,
		IFNULL(eve.event_id, '') next_appt_id,
		IFNULL(eve.judge, '') next_appt_doctor,
		IFNULL(eve.event_status, '') next_appt_status,
		IFNULL(eve.event_description, '') next_appt_description,
		IFNULL(last_incoming.recipient_response, IFNULL(response, '')) recipient_response,
        IFNULL(deliveries.`status`, '') delivery_status,
		IF(last_hangs.recipient_response IS NULL, '0', '1') last_hang
	FROM 
		tbl_debtor debtor 
	
	" . $scheduled_join . " (
		SELECT debtor_uuid, COUNT(tb.event_id) events_count, 
		MIN(tb.event_id) next_appt_id, MIN(tb.event_dateandtime) next_appt
		FROM `tbl_event_debtor` tbd
		INNER JOIN `tbl_event` tb
		ON tbd.event_uuid = tb.event_uuid
		WHERE tb.deleted = 'N'
		AND tb.customer_id = :customer_id";
		if ($event_date!="") {
			$sql .= " 
			AND CAST(tb.event_dateandtime AS DATE) = '" . $event_date . "'";
		}
		$sql .= "
		GROUP BY debtor_uuid
	) `debtor_events`
	ON debtor.debtor_uuid = debtor_events.debtor_uuid
	
	LEFT OUTER JOIN tbl_event eve
	ON `debtor_events`.next_appt_id = eve.event_id
	
	LEFT OUTER JOIN (
		SELECT debt.debtor_uuid, MAX(trs.`timestamp`) attempt_date, 
		COUNT(trs.remindersent_id) sent_count, GROUP_CONCAT(DISTINCT rem.reminder_type) attempt_method        
		FROM md_reminder.tbl_reminder rem
		INNER JOIN md_reminder.tbl_debtor debt
		ON rem.reminder_debtor_uuid = debt.debtor_uuid 
		INNER JOIN md_reminder.tbl_remindersent trs
		ON rem.reminder_uuid = trs.reminder_uuid
		INNER JOIN tbl_event_reminder ter
		ON rem.reminder_uuid = ter.reminder_uuid				
		INNER JOIN tbl_event eve
		ON ter.event_uuid = eve.event_uuid        
		WHERE 1
		AND rem.sent = 'Y'
		AND `rem`.`deleted` = 'N'
		AND rem.customer_id = :customer_id
		AND debt.customer_id = :customer_id";
		if ($event_date!="") {
			$sql .= " 
			AND CAST(eve.event_dateandtime AS DATE) = '" . $event_date . "'";
		}
		$sql .= "
		GROUP BY debt.debtor_uuid
	) rems
	ON debtor.debtor_uuid = rems.debtor_uuid
	
	LEFT OUTER JOIN (
		SELECT debt.debtor_uuid, MIN(tr.`reminder_id`) next_attempt_id, MIN(tr.`reminder_datetime`) next_attempt_date
		FROM tbl_debtor debt
		
		INNER JOIN tbl_reminder tr
		ON debt.debtor_uuid = tr.reminder_debtor_uuid
		
		INNER JOIN tbl_event_reminder ter
        ON tr.reminder_uuid = ter.reminder_uuid
        
        INNER JOIN tbl_event eve
        ON ter.event_uuid = eve.event_uuid
		
		WHERE 1 
        ";
		if ($event_date!="") {
			$sql .= " 
			AND CAST(eve.event_dateandtime AS DATE) = '" . $event_date . "'";
		} else {
			$sql .= "
			AND CAST(tr.`reminder_datetime` AS DATE) = '" . $reminder_date . "'";
		}
		$sql .= "
		AND eve.deleted = 'N'
		AND debt.deleted = 'N'
		AND debt.customer_id = :customer_id
		AND eve.customer_id = :customer_id
		GROUP BY debt.debtor_uuid
	) next_rem
	ON debtor.debtor_uuid = next_rem.debtor_uuid
	
	LEFT OUTER JOIN (
		SELECT cr.*, cm.`message_id`, crm.`message_uuid`, cm.`message_to`, cm.`message`
		FROM `md_reminder`.`tbl_reminder` cr
		INNER JOIN `md_reminder`.`tbl_reminder_message` crm
		ON cr.`reminder_uuid` = crm.`reminder_uuid`
		INNER JOIN `md_reminder`.`tbl_message` cm
		ON crm.`message_uuid` = cm.`message_uuid`
        INNER JOIN `md_reminder`.`tbl_event_reminder` ter
		ON cr.`reminder_uuid` = ter.`reminder_uuid`
        INNER JOIN `md_reminder`.`tbl_event` eve
		ON ter.`event_uuid` = eve.`event_uuid`
		WHERE 1
		AND cr.customer_id = :customer_id
		";
		if ($event_date!="") {
			$sql .= " 
			AND CAST(eve.event_dateandtime AS DATE) = '" . $event_date . "'";
		}
		$sql .= "
        AND eve.deleted = 'N'
    ) rem
	ON next_rem.next_attempt_id = rem.reminder_id
	
	LEFT OUTER JOIN (
		SELECT debtor_uuid, recipient_response 
        FROM md_reminder.tbl_incoming
		WHERE file_name = 'get_digits'
        AND CAST(plivo_date AS DATE) = '" . $reminder_date . "'
		AND customer_id = :customer_id
    ) last_incoming
	ON debtor.debtor_uuid = last_incoming.debtor_uuid
	
	LEFT OUTER JOIN (
		SELECT debtor_uuid, recipient_response 
        FROM md_reminder.tbl_incoming
		WHERE file_name = 'hangup'
        AND CAST(plivo_date AS DATE) = '" . $reminder_date . "'
		AND customer_id = :customer_id
    ) last_hangs
	ON debtor.debtor_uuid = last_hangs.debtor_uuid
	
	LEFT OUTER JOIN (
		/*
		SELECT cells.debtor_uuid, res.response 
		FROM md_reminder.tbl_response res
		INNER JOIN (
			SELECT debtor_uuid, CONCAT('1', REPLACE(REPLACE(REPLACE(cellphone, '(', ''), ')', ''), '-', '')) cellphone
			FROM md_reminder.tbl_debtor
			WHERE 1
			AND customer_id = :customer_id
		) cells
		ON res.response_from = cells.cellphone
		WHERE CAST(dateandtime AS DATE) = '" . $reminder_date . "'
		*/
		SELECT debt.debtor_uuid, rem.reminder_uuid, res.response
		FROM `md_reminder`.tbl_remindersent trs
		INNER JOIN `md_reminder`.tbl_reminder rem
		ON trs.reminder_uuid = rem.reminder_uuid
		INNER JOIN tbl_debtor debt
        ON rem.reminder_debtor_uuid = debt.debtor_uuid
		INNER JOIN `md_reminder`.tbl_response res
		ON trs.reply_response_id = res.response_id
		
		INNER JOIN md_reminder.tbl_event_reminder ter
		ON rem.reminder_uuid = ter.reminder_uuid
		INNER JOIN md_reminder.tbl_event eve
		ON ter.event_uuid = eve.event_uuid
		WHERE 1
		AND rem.customer_id = 13
		AND eve.deleted = 'N'
		AND CAST(eve.event_dateandtime AS DATE) = '" . $event_date . "'
    ) last_response
	ON debtor.debtor_uuid = last_response.debtor_uuid 
	
    LEFT OUTER JOIN (
		SELECT del.`status`, rem.reminder_uuid
		FROM md_reminder.tbl_delivery del
		INNER JOIN md_reminder.tbl_remindersent sent
		ON del.nexmo_id = sent.response_id
		INNER JOIN md_reminder.tbl_reminder rem
		ON sent.reminder_uuid = rem.reminder_uuid
		INNER JOIN md_reminder.tbl_event_reminder ter
		ON rem.reminder_uuid = ter.reminder_uuid
		INNER JOIN md_reminder.tbl_event eve
		ON ter.event_uuid = eve.event_uuid
		WHERE rem.sent = 'Y'
		AND rem.deleted = 'N'";
		if ($event_date!="") {
			$sql .= " 
			AND CAST(eve.event_dateandtime AS DATE) = '" . $event_date . "'";
		} else {
			$sql .= "
        	AND CAST(sent.`timestamp` AS DATE) = '" . $reminder_date . "'";
		}
		$sql .= "AND rem.customer_id = :customer_id
    ) deliveries
	ON rem.reminder_uuid = deliveries.reminder_uuid
	
	WHERE 1 
	AND eve.customer_id = :customer_id
	AND debtor.customer_id = :customer_id";
	if ($reminder_date!="") {
		if ($event_date=="") {
			$sql .= " AND CAST(next_rem.next_attempt_date AS DATE) = '" . $reminder_date . "'";
		} else {
			$sql .= " AND CAST(eve.event_dateandtime AS DATE) = '" . $event_date . "'";
		}
		$sql .= " AND
		" . DOCTORS_FILTER;
		
		$sql .= " 
	ORDER BY IFNULL(eve.judge, '') ASC, IFNULL(next_appt, '') ASC";
		
	} else {
		$sql .= " 
	ORDER BY debtor.`last_name` ASC, debtor.`first_name` ASC";
	}
	try {
		//die($sql);
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$debtors = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;

		echo json_encode($debtors);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getDebtor($debtor_id) {
	session_write_close();
	$sql = "SELECT `debt`.*,
	debt.`debtor_id` `id`, debt.`debtor_uuid` `uuid`,
	IFNULL(tdg.group_ids, '') group_ids
	FROM `md_reminder`.`tbl_debtor` debt
	LEFT OUTER JOIN (
		SELECT debtor_uuid, GROUP_CONCAT(grp.group_id) group_ids
		FROM `md_reminder`.`tbl_debtor_group` deg
		INNER JOIN `md_reminder`.`tbl_group` grp
		ON deg.group_uuid = grp.group_uuid
		WHERE grp.deleted = 'N'
		AND deg.deleted = 'N'
		AND grp.customer_id = :customer_id
	) tdg
	ON debt.debtor_uuid = tdg.debtor_uuid
	WHERE 1
	AND `debt`.deleted = 'N'
	AND `debt`.debtor_id = :debtor_id
	AND `debt`.customer_id = :customer_id";
	
	//REPLACE(REPLACE(`tbl_debtor`.`content`, '{', '`'), '}', '~') `content`,
	//echo $sql;
	$customer_id = $_SESSION["user_customer_id"];
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("debtor_id", $debtor_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$debtor = $stmt->fetchObject();
		$db = null;
		
		echo json_encode($debtor);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getDebtorInfo($debtor_id) {
	session_write_close();
	$sql = "SELECT `tbl_debtor`.*,
	`debtor_id` `id`, `debtor_uuid` `uuid`
	FROM `tbl_debtor` 
	WHERE 1
	AND debtor_id = :debtor_id
	AND customer_id = :customer_id";
	
	//REPLACE(REPLACE(`tbl_debtor`.`content`, '{', '`'), '}', '~') `content`,
	//echo $sql;
	$customer_id = $_SESSION["user_customer_id"];
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("debtor_id", $debtor_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$debtor = $stmt->fetchObject();
		$db = null;
		
		return $debtor;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getInvoiceDates() {
	session_write_close();
	
	$sql = "SELECT min(`invoice_date`) min_invoice_date, max (`invoice_date`) min_invoice_date";
}
function addDebtor() {
	session_write_close();
	
	$request = Slim::getInstance()->request();
	$arrFields = array();
	$arrSet = array();
	$table_name = "debtor";
	$table_id = "";
	//maybe there is no name, just a comm
	$first_name = passed_var("first_name", "post");
	if ($first_name=="") {
		$full_name = "";
		//get all the comms
		$email = passed_var("email", "post");
		$phone = passed_var("phone", "post");
		$cellphone = passed_var("cellphone", "post");
		
		//first is email, at least some kind of name
		if ($email!="") {
			$full_name = $email;
		}
		if ($full_name=="") {
			if ($cellphone!="") {
				$full_name = $cellphone;
			}
		}
		if ($full_name=="") {
			if ($phone!="") {
				$full_name = $phone;
			}
		}
	} else {
		$last_name = passed_var("last_name", "post");
		//no empties
		$full_name = trim(trim($first_name) . " " . trim($last_name));
	}
	$employee = "N";
	if (isset($_POST["employee"])) {
		$employee = passed_var("employee", "post");
	}
	
	$group_id = "";
	if (isset($_POST["group_id"])) {
		$group_id = $_POST["group_id"];
	}
	$arrGroups = $group_id;
	//default attribute
	$table_attribute = "main";
	foreach($_POST as $fieldname=>$value) {
		if ($fieldname=="group_id" || $fieldname=="employee") {
			continue;
		}
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="table_attribute") {
			$table_attribute = $value;
			continue;
		}
		if ($fieldname=="table_id" || $fieldname=="debtor_id") {
			continue;
		}
		$arrFields[] = "`" . $fieldname . "`";
		if ($fieldname=="consent_date") {
			if ($value=="" || $value=="__/__/____") {
				$value = "";
			} else {
				$value = date("Y-m-d H:i:s", strtotime($value));
			}
		}
		$arrSet[] = "'" . addslashes($value) . "'";
	}
	$customer_id = $_SESSION['user_customer_id'];
	$arrFields[] = "`customer_id`";
	$arrSet[] = $customer_id;
	
	if ($full_name!="") {
		$arrFields[] = "`full_name`";
		$arrSet[] = "'" . addslashes($full_name) . "'";
	}
	if ($employee!="") {
		$arrFields[] = "`employee`";
		$arrSet[] = "'" . $employee . "'";
	}
	$table_uuid = uniqid("DR", false);
	$sql = "INSERT INTO `tbl_" . $table_name ."` (`" . $table_name . "_uuid`, " . implode(",", $arrFields) . ") 
			VALUES ('" . $table_uuid . "', " . implode(",", $arrSet) . ")";
	//die($sql);
	$db = getConnection();
	try { 
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		$new_id = $db->lastInsertId();
		//die("Hello");
		echo json_encode(array("id"=>$new_id, "uuid"=>$table_uuid)); 
		
		$db = null;
		if (is_array($arrGroups)) {
			foreach($arrGroups as $group_id) {
				if (!is_numeric($group_id)) {
					continue;
				}
				$sql = "INSERT INTO tbl_debtor_group (`debtor_group_uuid`, `debtor_uuid`, `group_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				SELECT :debtor_group_uuid, :debtor_uuid, `group_uuid`, 'main', :last_updated_date, :last_update_user, :customer_id
				FROM `tbl_group`
				WHERE group_id = :group_id";
				
				$last_updated_date = date("Y-m-d H:i:s");
				$last_update_user = $_SESSION["user_id"];
				
				$db = getConnection();
				$stmt = $db->prepare($sql); 
				$stmt->bindParam("debtor_group_uuid", $table_uuid); 
				$stmt->bindParam("debtor_uuid", $table_uuid); 
				$stmt->bindParam("last_updated_date", $last_updated_date); 
				$stmt->bindParam("last_update_user", $last_update_user); 
				$stmt->bindParam("customer_id", $customer_id); 
				$stmt->bindParam("group_id", $group_id); 
				$stmt->execute();
				$db = null; $stmt = null;
			}
		}
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}
function switchDebtorLanguage() {
	$debtor_id = passed_var("id", "post");
	
	$debtor = getDebtorInfo($debtor_id);
	
	//die(print_r($debtor));
	
	$language = $debtor->language;
	if ($language=="English") {
		$language = "Spanish";
	} else {
		$language = "English";
	}
	
	$customer_id = $_SESSION['user_customer_id'];
	
	$sql = "UPDATE tbl_debtor
	SET language = :language
	WHERE debtor_id = :debtor_id
	AND customer_id = :customer_id";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("language", $language);
		$stmt->bindParam("debtor_id", $debtor_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>$debtor_id, "language"=>$language)); 
		$db = null;
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function updateDebtor() {
	session_write_close();
	
	$request = Slim::getInstance()->request();
	$debtor_id = passed_var("debtor_id", "post");
	$customer_id = $_SESSION["user_customer_id"];
	if (!is_numeric($debtor_id)) {
		die();
	}
	if ($debtor_id < 0) {
		addDebtor();
		return;
	}
	$debtor = getDebtorInfo($debtor_id);
	$arrSet = array();
	$where_clause = "";
	$table_name = "debtor";
	$table_attribute = "";
	$batch_time = "";
	$drip_id = "";
	$arrFilter = array();
	$employee = "N";
	if (isset($_POST["employee"])) {
		$employee = passed_var("employee", "post");
	}
	$group_id = "";
	if (isset($_POST["group_id"])) {
		$group_id = $_POST["group_id"];
	}
	$arrGroups = $group_id;
	//die(print_r($arrGroups));
	foreach($_POST as $fieldname=>$value) {
		if ($fieldname=="group_id" || $fieldname=="employee") {
			continue;
		}
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		
		if ($fieldname=="debtor_id") {
			$table_id = $value;
			$where_clause = " = " . $value;
			continue;
		}
		if ($fieldname=="consent_date") {
			if ($value!="") {
				$value = date("Y-m-d H:i:s", strtotime($value));
			}
		}
		$arrSet[] = "`" . $fieldname . "` = '" . addslashes($value) . "'";
	}
	if ($employee!="") {
		$arrSet[] = "`employee` = '" . $employee . "'";
	}
	$where_clause = "`" . $table_name . "_id`" . $where_clause;
	$sql = "
	UPDATE `tbl_" . $table_name . "`
	SET " . implode(", ", $arrSet) . "
	WHERE " . $where_clause . " 
	AND customer_id = :customer_id";
	// die($sql . "\r\n");
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("customer_id", $customer_id); 
		$stmt->execute();
		$db = null; $stmt = null;
		echo json_encode(array("success"=>$table_id)); 
		
		//clear out any previous groups
		$sql = "UPDATE tbl_debtor_group
		SET deleted = 'Y'
		WHERE debtor_uuid = :debtor_uuid
		AND customer_id = :customer_id";
		$db = getConnection();
		$stmt = $db->prepare($sql); 
		$stmt->bindParam("debtor_uuid", $debtor->debtor_uuid); 
		$stmt->bindParam("customer_id", $customer_id); 
		$stmt->execute();
		$db = null; $stmt = null;
		
		if (is_array($arrGroups)) {
			foreach($arrGroups as $group_id) {
				if (!is_numeric($group_id)) {
					continue;
				}
				$sql = "INSERT INTO tbl_debtor_group (`debtor_group_uuid`, `debtor_uuid`, `group_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				SELECT :debtor_group_uuid, :debtor_uuid, `group_uuid`, 'main', :last_updated_date, :last_update_user, :customer_id
				FROM `tbl_group`
				WHERE group_id = :group_id";
				
				$last_updated_date = date("Y-m-d H:i:s");
				$last_update_user = $_SESSION["user_id"];
				
				$db = getConnection();
				$stmt = $db->prepare($sql); 
				$stmt->bindParam("debtor_group_uuid", $debtor->debtor_uuid); 
				$stmt->bindParam("debtor_uuid", $debtor->debtor_uuid); 
				$stmt->bindParam("last_updated_date", $last_updated_date); 
				$stmt->bindParam("last_update_user", $last_update_user); 
				$stmt->bindParam("customer_id", $customer_id); 
				$stmt->bindParam("group_id", $group_id); 
				$stmt->execute();
				$db = null; $stmt = null;
			}
		}
		$db = null; $stmt = null;
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function resubscribeDebtor() {
	session_write_close();
	$id = passed_var("id", "post");
	
	try {
		$sql = "UPDATE tbl_debtor
				SET `subscribe` = 'Y'
				WHERE `debtor_id`=:id";
				
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		
		$db = null; $stmt = null;
		echo json_encode(array("success"=>"debtor marked as subscribed", "debtor_id"=>$id));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	//trackDebtor("resubscribe", $id);	
}
function unsubscribeDebtor() {
	session_write_close();
	$id = passed_var("id", "post");
	
	try {
		$sql = "UPDATE tbl_debtor
				SET `subscribe` = 'N'
				WHERE `debtor_id`=:id";
				
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		
		$db = null; $stmt = null;
		echo json_encode(array("success"=>"debtor marked as unsubscribed", "debtor_id"=>$id));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	//trackDebtor("unsubscribe", $id);	
}

function deleteDebtor() {
	session_write_close();
	
	$id = passed_var("id", "post");
	$selectSQL = "SELECT `debtor_uuid` 
				  FROM `tbl_debtor` 
				  WHERE `debtor_id`= " . $id;
	// die($selectSQL);
	try {
		$db = getConnection();
		$selectstmt = $db->prepare($selectSQL);
		// $selectstmt->bindParam("id", $id);
		$selectstmt->execute();
		$debtor_uuid_object = $selectstmt->fetchObject();
		$debtor_uuid = $debtor_uuid_object->debtor_uuid;
		// die($debtor_uuid);
				  
		$sql = "UPDATE tbl_debtor
				SET `deleted` = 'Y'
				WHERE `debtor_id`=:id";
		
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		
		$db = null; $stmt = null;
		echo json_encode(array("success"=>"debtor marked as deleted"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	//trackDebtor("delete", $id);	
}
?>
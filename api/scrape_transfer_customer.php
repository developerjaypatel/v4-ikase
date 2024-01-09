<?php
//import csv contents as events in ikase
set_time_limit(240);

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

//die(print_r($_SERVER));

//die(date("Y-m-d H:i:s", strtotime("Jun 15, 2017 1:30 PM")));
include("connection.php");

if (!isset($_GET["cus_id"])) {
	die("no no no?");
}
/*
$customer_id = passed_var("cus_id", "get");

if (!is_numeric($customer_id)) {
	die("no no no!");
}
*/
$sql = "SELECT customer_id, GROUP_CONCAT(nickname SEPARATOR ';') assignee
FROM ikase.cse_user
WHERE INSTR(adhoc, '\"courtcalendar\":\"Y\"') > 0
GROUP BY customer_id
ORDER BY customer_id";

$users = DB::select($sql);

function getCustomerCourtCalendar($transfer_status, $customer_id, $db_name) {
	session_write_close();
	//return;
	try {
		//get the max import date
		$sql = "SELECT MAX(import_date) import_date
		FROM ikase.cse_courtcalendar ccc";
		$stmt = DB::run($sql);
		$import = $stmt->fetchObject();
		
		$yesterday = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d") - 1, date("Y")));
		
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
		
		LEFT OUTER JOIN `" . $db_name . "`.cse_case customer_case
		ON ccase.case_uuid = customer_case.case_uuid
        
        LEFT OUTER JOIN (
			SELECT courtevents.event_id

			FROM (
				SELECT ccase.case_uuid, eve.*
				FROM `" . $db_name . "`.cse_event eve
				INNER JOIN `" . $db_name . "`.cse_case_event ccev
				ON eve.event_uuid = ccev.event_uuid
				INNER JOIN `" . $db_name . "`.cse_case ccase
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
		
		$sql .= "
		ORDER BY event_date ASC ";
		//die($sql);
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("yesterday",  $yesterday);
		$stmt->bindParam("transfer_status", $transfer_status);
		
		$stmt->execute();
		$events = $stmt->fetchAll(PDO::FETCH_OBJ);
		// $buffer = $stmt->fetchObject();
		//die(print_r($events));
		return $events;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}	
}
function transferCustomerCourtCalendarEvent($customer_id, $db_name, $event_id, $courtcalendar_id, $assignee) {
	session_write_close();
	
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
		$sql = "INSERT INTO `" . $db_name . "`.cse_event (`event_uuid`, `event_dateandtime`, `event_type`, `assignee`, `judge`, `full_address`, `customer_id`, `event_description`, `event_title`)
		SELECT `event_uuid`, `event_dateandtime`, `event_type`, '" . $assignee . "', `judge`, `full_address`, `customer_id`, `event_description`, `event_title`
		FROM `court_calendar`.cse_event
		WHERE event_uuid = :event_uuid";
		//die($sql);
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("event_uuid", $event_uuid);
		$stmt->execute();
		
		$new_id = $db->lastInsertId();
		
		$sql = "INSERT INTO `" . $db_name . "`.cse_case_event 
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
		WHERE `courtcalendar_id` = :courtcalendar_id";
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("event_uuid", $event_uuid);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("courtcalendar_id", $courtcalendar_id);
		$stmt->execute();
		
		//trackEvent("insert", $new_id);
		
		echo json_encode(array("id"=>$new_id, "uuid"=>$event_uuid)); 
	} catch(PDOException $e) {
		//die($sql);
		$error = array("error"=> array("sql"=>$sql, "text"=>$e->getMessage()));
        echo json_encode($error);
	}	
}

foreach($users as $user) {
	//die(print_r($user));
	$sql_customer = "SELECT cus_name, data_source, permissions
	FROM  `ikase`.`cse_customer` 
	WHERE customer_id = :customer_id";
	
	$customer_id = $user->customer_id;
	$assignee = $user->assignee;
	
	$db = getConnection();
	$stmt = $db->prepare($sql_customer);
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->execute();
	$customer = $stmt->fetchObject();
	//die(print_r($customer));
	
	$db_name = "ikase";
	if ($customer->data_source!="") {
		$db_name .= "_" . $customer->data_source;
	}
	$pendings = getCustomerCourtCalendar("pending", $customer_id, $db_name);
	foreach ($pendings as $pending) {
		echo "running " . $pending->id . "<br>";
		transferCustomerCourtCalendarEvent($customer_id, $db_name, $pending->id, $pending->courtcalendar_id, $assignee);
	}
}

$fp = fopen('scrape_data.txt', 'a+');
fwrite($fp, 'transfer customer  @ ' . date('m/d/y H:i:s') . chr(10));
fclose($fp); 

echo "done at " . date("m/d/Y H:i:s");

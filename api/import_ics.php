<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once('../shared/legacy_session.php');
set_time_limit(3000);

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;
$last_updated_date = date("Y-m-d H:i:s");

include("connection.php");
include("cls_ics.php");

/* Replace the URL / file path with the .ics url */
$file = "https://www.ikase.org/uploads/1042/rajpatelapc@gmail.com.ics";
/* Getting events from isc file */
$obj = new ics();
$icsEvents = $obj->getIcsEventsAsArray( $file );

//die(print_r($icsEvents));

foreach($icsEvents as $event) {
	if (strpos($event["BEGIN"], "VEVENT") !== false) {
		//print_r($event);
		//die();
		$start_date = date("Y-m-d H:i:s", strtotime($event["DTSTART"]));
		$end_date = date("Y-m-d H:i:s", strtotime($event["DTEND"]));
		$last_modified = date("Y-m-d H:i:s", strtotime($event["LAST-MODIFIED"]));
		$event_uuid = $event["UID"];
		$event_description = $event["DESCRIPTION"];
		$event_title = $event["SUMMARY"];
		
		//$event_uuid = uniqid("KS", false);	
		
		try { 		
			$sql = "INSERT INTO `ikase_patel`.`cse_event` (`event_uuid`, `event_dateandtime`, `event_date`, `end_date`, `event_hour`, `event_title`, `full_address`, `event_type`, `color`, `event_description`, `customer_id`)
			SELECT '" . $event_uuid . "', '" . $start_date . "', '" . $start_date . "', '" . $end_date . "' , '" . date("H:i", strtotime($start_date)) . "', '" . addslashes($event_title) . "' , '' , 'import', 'blue', '" . addslashes($event_description) . "', 1042
			FROM dual
			WHERE NOT EXISTS (
							SELECT * 
							FROM `ikase_patel`.`cse_event`
							WHERE event_uuid = '" . $event_uuid . "'
							AND customer_id = '1042'
						)";
			
			$stmt = DB::run($sql);
			
			$sql = "DELETE FROM `ikase_patel`.`cse_event_user`
			WHERE event_uuid = '" . $event_uuid . "'";
			
			$stmt = DB::run($sql);
			
			$event_user_uuid = uniqid("IM");
			
			$sql = "INSERT INTO `ikase_patel`.`cse_event_user`
			(`event_user_uuid`, `event_uuid`, `thread_uuid`, `user_uuid`, `type`, `read_status`, `read_date`, `action`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
					";
			$sql .= "VALUES('" . $event_user_uuid . "', '" . $event_uuid . "', '', 'TS54a2e82d68658', 'from', 'N', '0000-00-00', 'forward', '" . $last_modified . "', 'TS54a2e82d68658', 'N', 1042)";
			
			$stmt = DB::run($sql);
			
			echo $event_uuid . "<br>";
			
		} catch(PDOException $e) {	
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
			die("
			" . $sql);
		}
	}
}

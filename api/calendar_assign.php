<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
include("connection.php");
function getCalendarBySortOrder($customer_id, $sort_order) {
    $sql = "SELECT cal.*, cal.calendar_id id, cal.calendar_uuid uuid
			FROM  `cse_calendar` cal
			WHERE `cal`.customer_id = " . $customer_id . "
			AND `cal`.deleted = 'N'
			AND `cal`.sort_order = " . $sort_order;
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $calendar_id);
		$stmt->execute();
		$the_customer_calendar = $stmt->fetchObject();

         return $the_customer_calendar;        

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

$sql = "SELECT DISTINCT `event_id` id, eve.`event_uuid`, eve.customer_id, eve.event_type
		FROM `cse_event` eve
		INNER JOIN `cse_customer` csc
		ON eve.customer_id = csc.customer_id			
		LEFT OUTER JOIN `cse_calendar_event` ceve
		ON eve.event_uuid = ceve.event_uuid
		
		WHERE 1
		AND ceve.calendar_uuid IS NULL 
		ORDER BY eve.event_id ASC";
//die($sql);
//cc0b3b41871473a9d54a6d87260f47bd
try {
	$allcusevents = DB::select($sql);
	$last_updated_date = date("Y-m-d H:i:s");
	foreach($allcusevents as $cusevent) {
		switch($cusevent->event_type) {
			case "intake":
				$calendar_sort_order = 4;
				break;
			default:
				$calendar_sort_order = 0;
		}
		$table_uuid = $cusevent->event_uuid;
		$customer_calendar = getCalendarBySortOrder($cusevent->customer_id, $calendar_sort_order);
		if (is_object($customer_calendar)) {
			//attach the event to the calendar
			$calendar_event_uuid = uniqid("KE", false);
			$sql = "INSERT INTO cse_calendar_event (`calendar_event_uuid`, `calendar_uuid`, `user_uuid`, `event_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $calendar_event_uuid  ."', '" . $customer_calendar->uuid . "', '', '" . $table_uuid . "', '" . $customer_calendar->calendar . "', '" . $last_updated_date . "', 'system', '" . $cusevent->customer_id . "')";
			//die($sql);  
			$stmt = DB::run($sql);
		}
	}
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
}

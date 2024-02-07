<?php
include("manage_session.php");

include("connection.php");

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_calendar` ";
	echo $sql . "\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_case_event`";
	
	echo $sql . "\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_event`";


	echo $sql . "\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_event`
	(`case_event_uuid`, `case_uuid`, `event_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id`)
	SELECT `case_event_uuid`, `case_uuid`, `event_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id` 
	FROM `" . $data_source . "`.`" . $data_source . "_case_event` 
	WHERE 1 AND customer_id = " . $customer_id;

	echo $sql . "\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_event` (`event_uuid`, `event_name`, `event_date`, `event_duration`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `full_address`, `assignee`, `event_title`, `event_email`, `event_hour`, `event_type`, `event_type_abbr`, `event_from`, `event_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed`, `color`, `customer_id`, `deleted`)
	SELECT `event_uuid`, `event_name`, `event_date`, `event_duration`, `event_description`, `event_first_name`, `event_last_name`, 
	`event_dateandtime`, 
	`event_end_time`, 
	`full_address`, `assignee`, `event_title`, `event_email`, `event_hour`, `event_type`, `event_type_abbr`, `event_from`, `event_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed`, `color`, `customer_id`, `deleted` 
	FROM `" . $data_source . "`.`" . $data_source . "_event` 
	WHERE 1 AND customer_id = " . $customer_id;
	
	echo $sql . "\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	//FIRST two letters
	$first_two = substr($data_source, 0, 2);

	$sql_calendar = "INSERT INTO `ikase_" . $data_source . "`.`cse_calendar`
	(`calendar_uuid`, `calendar`, `sort_order`, `customer_id`, `mandatory`, `active`)
	SELECT CONCAT('" . $first_two . "', SUBSTRING(`calendar_uuid`, 3)), `calendar`, `sort_order`, 
	'" . $customer_id . "', `mandatory`, `active`
	FROM `ikase`.`cse_calendar`
	WHERE `customer_id` = 1033
	ORDER BY `sort_order`";
	echo $sql_calendar . "\r\n\r\n";
	$stmt = $db->prepare($sql_calendar);  
	$stmt->execute();
	
	//get the main calendar uuid
	$sql = "SELECT calendar_uuid FROM `ikase_" . $data_source . "`.`cse_calendar` WHERE sort_order = 0";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$calendar = $stmt->fetchObject();
	$calendar_uuid = $calendar->calendar_uuid;
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_calendar_event` 
	(`calendar_event_uuid`, `calendar_uuid`, `event_uuid`,
	`user_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
	SELECT `cse_event`.`event_uuid`, '" . $calendar_uuid . "', `event_uuid`, 'system', 'main', 
	'" . date("Y-m-d g:i:s") . "', 'system', '" . $customer_id . "'
	FROM `ikase_" . $data_source . "`.`cse_event`
	WHERE 1";
	//AND event_uuid NOT IN (SELECT DISTINCT event_uuid FROM ikase_reino.cse_event_track)";

	echo $sql . "\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$db = null;
	
	$success = array("success"=> array("text"=>"done @" . date("H:i:s")));
	echo json_encode($success);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}	
//, `notedesc`, , `notepoint`, `notelock`, `lockedby`, `xsoundex`, `locator`, `enteredby`, `mailpoint`, `lockloc`, `docpointer`, `doctype`
include("cls_logging.php");
?>
<script language="javascript">
parent.setFeedback("events transfer completed");
</script>
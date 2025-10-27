<?php
include("manage_session.php");

include("connection.php");

$customer_id = passed_var("customer_id", "get");
if (!is_numeric($customer_id)) {
	die();
}

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_calendar` ";
	echo $sql . "\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_case_event`";
	echo $sql . "<br /><br />\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_event`";
	echo $sql . "<br /><br />\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_calendar_event` ";
	echo $sql . "<br /><br />\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "
	INSERT INTO `ikase_" . $data_source . "`.`cse_event` (`event_uuid`, `event_title`, `event_name`, `event_duration`, `event_description`, `event_dateandtime`, `full_address`, `judge`, `assignee`, 
	`event_first_name`, `event_last_name`,
	`event_type`, `customer_id`)
	SELECT DISTINCT CONCAT(cal1.`EVENTNO`,
            '_fr_',
            IFNULL(UNIX_TIMESTAMP(CHGLAST), UNIX_TIMESTAMP(`DATE`))) AS `event_uuid`, 
	IFNULL(CONCAT(`FIRST`, ' ', `LAST`, ' vs ', `DEFENDANT`), IFNULL(`EVENT`, '')) `event_title`,
	IFNULL(`EVENT`, '') `EVENT`, 
	'30',  
	`NOTES` `evmemo`, 
	cal1.`DATE` event_dateandtime, 
	IFNULL(`VENUE`, '') `location`, IFNULL(`JUDGE`, '') `judge`, IFNULL(ATTYASS, '')  `assignee`, 
	IFNULL(`FIRST`, '') `FIRST`, IFNULL(`LAST`, '') `LAST`,
	`CALENDAR` `event_type`,
	'" . $customer_id . "' 
	FROM `" . $data_source . "`.cal1
	LEFT OUTER JOIN `" . $data_source . "`.cal2
	ON cal1.EVENTNO = cal2.EVENTNO
	WHERE 1
	AND cal1.`EVENTNO` != '0'
	AND `CASENO` = '0'";
	
	echo $sql . "<br /><br />\r\n\r\n";
	//die();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_event`
	(`case_event_uuid`, `case_uuid`, `event_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id`)
	SELECT `case_event_uuid`, `case_uuid`, `event_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id` 
	FROM `" . $data_source . "`.`" . $data_source . "_case_event` 
	WHERE 1 AND customer_id = " . $customer_id;

	echo $sql . "<br /><br />\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_event` (`event_uuid`, `event_name`, `event_date`, `event_duration`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `full_address`, `judge`, `assignee`, `event_title`, `event_email`, `event_hour`, `event_type`, `event_type_abbr`, `event_from`, `event_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed`, `color`, `customer_id`, `deleted`)
	SELECT `event_uuid`, `event_name`, `event_date`, `event_duration`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `full_address`, `judge`, `assignee`, `event_title`, `event_email`, `event_hour`, `event_type`, `event_type_abbr`, `event_from`, `event_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed`, `color`, `customer_id`, `deleted` 
	FROM `" . $data_source . "`.`" . $data_source . "_event` 
	WHERE 1 
	AND event_uuid NOT IN (SELECT event_uuid FROM `ikase_" . $data_source . "`.`cse_event`)
	AND customer_id = " . $customer_id;
	
	echo $sql . "<br /><br />\r\n\r\n";
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
	echo $sql_calendar . "<br /><br />\r\n\r\n";
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
	'" . date("Y-m-d g:i:s") . "', 'system', '1055'
	FROM `ikase_" . $data_source . "`.`cse_event`
	WHERE 1
	AND (`event_type` = 1 OR `event_type` = 3 OR `event_type` = 5)";
	
	echo $sql . "<br /><br />r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	//get the in office calendar uuid
	$sql = "SELECT calendar_uuid FROM `ikase_" . $data_source . "`.`cse_calendar` WHERE sort_order = 1";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$calendar = $stmt->fetchObject();
	$calendar_uuid = $calendar->calendar_uuid;
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_calendar_event` 
	(`calendar_event_uuid`, `calendar_uuid`, `event_uuid`,
	`user_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
	SELECT `cse_event`.`event_uuid`, '" . $calendar_uuid . "', `event_uuid`, 'system', 'main', 
	'" . date("Y-m-d g:i:s") . "', 'system', '1055'
	FROM `ikase_" . $data_source . "`.`cse_event`
	WHERE 1
	AND (`event_type` = 2)";
	
	echo $sql . "<br /><br />\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	//get the intake calendar uuid
	$sql = "SELECT calendar_uuid FROM `ikase_" . $data_source . "`.`cse_calendar` WHERE sort_order = 4";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$calendar = $stmt->fetchObject();
	$calendar_uuid = $calendar->calendar_uuid;
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_calendar_event` 
	(`calendar_event_uuid`, `calendar_uuid`, `event_uuid`,
	`user_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
	SELECT `cse_event`.`event_uuid`, '" . $calendar_uuid . "', `event_uuid`, 'system', 'main', 
	'" . date("Y-m-d g:i:s") . "', 'system', '1055'
	FROM `ikase_" . $data_source . "`.`cse_event`
	WHERE 1
	AND (`event_type` = 4)";

	echo $sql . "<br /><br />\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "UPDATE `ikase_" . $data_source . "`.`cse_event`
	SET event_type = 'Appearances'
	WHERE event_type = '1'";
	
	echo $sql . "<br /><br />\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "UPDATE `ikase_" . $data_source . "`.`cse_event`
	SET event_type = 'In Office Appearances'
	WHERE event_type = '2'";
	
	echo $sql . "<br /><br />\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "UPDATE `ikase_" . $data_source . "`.`cse_event`
	SET event_type = 'Employee Attendance'
	WHERE event_type = '3'";
	
	echo $sql . "<br /><br />\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	
	$sql = "UPDATE `ikase_" . $data_source . "`.`cse_event`
	SET event_type = 'Intake'
	WHERE event_type = '4'";
	
	echo $sql . "<br /><br />\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "UPDATE `ikase_" . $data_source . "`.`cse_event`
	SET event_type = 'Partner Calendar'
	WHERE event_type = '5'";
	
	echo $sql . "<br /><br />\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "UPDATE ikase_" . $data_source . ".cse_event 
	SET event_type = 'Lien Conference' 
	WHERE event_type = 'Appearances' 
	AND event_name LIKE 'Lien Conference%';
	
	UPDATE ikase_" . $data_source . ".cse_event
	SET event_type = 'Depostion'
	WHERE event_type = 'Appearances'
	AND event_name LIKE 'DEPO%';
	
	UPDATE ikase_" . $data_source . ".cse_event
	SET event_type = 'Trial'
	WHERE event_type = 'Appearances'
	AND event_name LIKE 'TRIAL%';
	
	UPDATE ikase_" . $data_source . ".cse_event
	SET event_type = 'MSC'
	WHERE event_type = 'Appearances'
	AND event_name LIKE 'MSC%';
	
	UPDATE ikase_" . $data_source . ".cse_event
	SET event_type = 'Status Conference'
	WHERE event_type = 'Appearances'
	AND event_name LIKE 'SC%';
	
	UPDATE ikase_" . $data_source . ".cse_event
	SET event_type = 'Expedited Hearing'
	WHERE event_type = 'Appearances'
	AND event_name LIKE 'Exp Hrg%';
	
	UPDATE ikase_" . $data_source . ".cse_event
	SET event_type = 'Deposition'
	WHERE event_type = 'Appearances'
	AND event_name LIKE '%s Deposition';
	
	UPDATE ikase_" . $data_source . ".cse_event
	SET event_type = 'Priority Conference'
	WHERE event_type = 'Appearances'
	AND (event_name = 'priority conf' OR event_name = 'PC');
	
	UPDATE ikase_" . $data_source . ".cse_event
	SET event_type = 'Lien Conference'
	WHERE event_type = 'Appearances'
	AND event_name LIKE '%Lien Conference%';
	
	UPDATE ikase_" . $data_source . ".cse_event
	SET event_type = 'Phone Conference'
	WHERE event_type = 'Appearances'
	AND event_name LIKE '%phone conference%';
	
	UPDATE ikase_" . $data_source . ".cse_event
	SET event_type = 'Lien Conference'
	WHERE event_type = 'Appearances'
	AND event_name LIKE 'Lien Trial%';
";
	
	echo $sql . "<br /><br />\r\n\r\n";
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
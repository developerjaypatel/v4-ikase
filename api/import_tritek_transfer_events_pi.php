<?php
require_once('../shared/legacy_session.php');

include("connection.php");

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_event`
	(`case_event_uuid`, `case_uuid`, `event_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id`)
	SELECT `case_event_uuid`, ccn.`case_uuid`, `event_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id` 
	FROM `" . $data_source . "`.`" . $data_source . "_case_event` ccn
	INNER JOIN " . $data_source . ".badcases
	ON ccn.case_uuid = badcases.case_uuid
	WHERE 1";

	echo $sql . "\r\n\r\n";
	//$stmt = $db->prepare($sql);
	//$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_event` (`event_uuid`, `event_name`, `event_date`, `event_duration`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `full_address`, `assignee`, `event_title`, `event_email`, `event_hour`, `event_type`, `event_type_abbr`, `event_from`, `event_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed`, `color`, `customer_id`, `deleted`)
	SELECT ev.`event_uuid`, ev.`event_name`, ev.`event_date`, ev.`event_duration`, ev.`event_description`, 
	ev.`event_first_name`, ev.`event_last_name`, 
	IF (amflag='PM', DATE_ADD(ev.`event_dateandtime`, INTERVAL 12 HOUR), ev.`event_dateandtime`) `event_dateandtime`, 
	ev.`event_end_time`, 
	ev.`full_address`, ev.`assignee`, ev.`event_title`, ev.`event_email`, ev.`event_hour`, ev.`event_type`, ev.`event_type_abbr`, ev.`event_from`, 
	ev.`event_priority`, ev.`end_date`, ev.`completed_date`, ev.`callback_date`, ev.`callback_completed`, ev.`color`, ev.`customer_id`, ev.`deleted` 
	FROM `" . $data_source . "`.`" . $data_source . "_event` ev 
	
    LEFT OUTER JOIN ikase_" . $data_source . ".cse_event gca
    ON ev.event_uuid = gca.event_uuid
	
	INNER JOIN `ikase_" . $data_source . "`.`cse_case_event` cce
	ON ev.event_uuid = cce.event_uuid
	INNER JOIN " . $data_source . ".badcases
	ON cce.case_uuid = badcases.case_uuid
	WHERE 1
	AND gca.event_uuid IS NULL";
	
	echo $sql . "\r\n\r\n";
	//$stmt = $db->prepare($sql);
	//$stmt->execute();
	
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_calendar_event` 	(`calendar_event_uuid`, `calendar_uuid`, `event_uuid`,	`user_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
	SELECT `ev`.`event_uuid`, '', ev.`event_uuid`, 'system', 'main', 
	'2018-10-28 9:22:04', 'system', '1121'
	FROM `ikase_" . $data_source . "`.`cse_event` ev
    
    LEFT OUTER JOIN (
		SELECT event_uuid
        FROM `ikase_" . $data_source . "`.`cse_calendar_event`
    ) alread
    ON ev.event_uuid = alread.event_uuid
    
	INNER JOIN `ikase_" . $data_source . "`.`cse_case_event` cce
	ON ev.event_uuid = cce.event_uuid
	INNER JOIN " . $data_source . ".badcases
	ON cce.case_uuid = badcases.case_uuid
	WHERE 1
    AND alread.event_uuid IS NULL";
	//AND event_uuid NOT IN (SELECT DISTINCT event_uuid FROM ikase_reino.cse_event_track)";

	echo $sql . "\r\n\r\n";
	//$stmt = $db->prepare($sql);
	//$stmt->execute();
	
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

<?php
include("manage_session.php");
set_time_limit(3000);

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;

include("connection.php");
$customer_id = passed_var("customer_id", "get");
if (!is_numeric($customer_id)) {
	die();
}

try {
	$db = getConnection();
	
	include("customer_lookup.php");
	
	$sql_truncate = "TRUNCATE `" . $data_source . "`.`" . $data_source . "_event`; TRUNCATE `" . $data_source . "`.`" . $data_source . "_case_event`; ";
	$stmt = $db->prepare($sql_truncate);
	$stmt->execute();
	//die($sql_truncate);
	
	$sql = "SELECT DISTINCT mc.case_id, mc.case_uuid, mc.cpointer
	FROM `" . $data_source . "`.`" . $data_source . "_case` mc
	INNER JOIN `" . $data_source . "`.`events`
	ON mc.cpointer = `events`.`evpointer`
	WHERE 1 
	#AND mc.cpointer = 209750
	ORDER BY mc.cpointer";

	$stmt = $db->prepare($sql);
	//echo $sql . "\r\n\r\n";
	$stmt->execute();
	
	$cases = $stmt->fetchAll(PDO::FETCH_OBJ);
	$note_number = "10";
	
	foreach($cases as $key=>$case){
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$row_start_time = $time;
		
		echo "<br>Processing -> " . $key. " == " . $case->cpointer . "<br>";
		
		$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_event` (`case_event_uuid`, `case_uuid`,  `event_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
		SELECT '" . $case->case_uuid . "', '" . $case->case_uuid . "', CONCAT('" . $customer_id . "', `evpointer`, REPLACE(evdate, '/', ''), @curRow := @curRow + 1) AS `event_uuid`, 
		'main', STR_TO_DATE(  `evdate` ,  '%m/%d/%Y' ) evdate, 'system', 'N', " . $customer_id . "
		FROM `" . $data_source . "`.`events`
		JOIN    (SELECT @curRow := 0) r
		WHERE `evpointer` = '" . $case->cpointer . "'";
		echo $sql . "\r\n\r\n";
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		$sql = "
		INSERT INTO `" . $data_source . "`.`" . $data_source . "_event` (`event_uuid`, `event_title`, `event_name`, `event_date`, `event_duration`, `event_description`, `event_dateandtime`, `full_address`, `assignee`, `event_type`, `customer_id`)
		SELECT CONCAT('" . $customer_id . "', `evpointer`, REPLACE(evdate, '/', ''), @curRow := @curRow + 1) AS `event_uuid`, IF(LENGTH(`evmemo`) > 254, SUBSTRING(`evmemo`, 1, 254), `evmemo`) `event_title`,
		'evdesc', 
		STR_TO_DATE(  `evdate` ,  '%m/%d/%Y' ) `date`, 
		'30',  
		`evmemo`, 
		CONCAT(STR_TO_DATE( evdate,  '%m/%d/%Y' ), ' ', IF(evam=1, `evtime`, '13:30')) event_dateandtime, 
		`location`, IFNULL(wcatty.recno, 0) `assignee`, 
		IFNULL(`evdesc`, '') `event_type`,
		'" . $customer_id . "' 
		FROM `" . $data_source . "`.`events` ev
		JOIN    (SELECT @curRow := 0) r
		LEFT OUTER JOIN `" . $data_source . "`.evcode evc ON ev.evcode = evc.evcode 
        LEFT OUTER JOIN `" . $data_source . "`.workcode wc ON ev.evworkcode = wc.workcode 
        LEFT OUTER JOIN `" . $data_source . "`.workcode wcatty ON ev.evattycode = wcatty.workcode 
        LEFT OUTER JOIN `" . $data_source . "`.workcode entered ON ev.enteredby = entered.workcode
		WHERE 1
		AND `evpointer` = '" . $case->cpointer . "'";
		
		echo $sql . "\r\n\r\n";
		//die();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $row_start_time), 4);
		echo "Time spent:" . $total_time . "\r\n\r\n";
	}
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	$success = array("success"=> array("text"=>"done", "duration"=>$total_time));
	echo json_encode($success);
	
	$db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
?>
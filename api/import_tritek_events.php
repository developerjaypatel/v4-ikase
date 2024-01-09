<?php
require_once('../shared/legacy_session.php');
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
	
	$sql = "SELECT DISTINCT mc.case_id, mc.case_uuid, mc.cpointer
	FROM `" . $data_source . "`.`" . $data_source . "_case` mc
	INNER JOIN `" . $data_source . "`.`events`
	ON mc.cpointer = `events`.`evpointer`
	WHERE 1 
	AND mc.case_uuid NOT IN (SELECT DISTINCT case_uuid FROM `" . $data_source . "`.`" . $data_source . "_case_event`)
	ORDER BY mc.cpointer DESC
	LIMIT 0, 1";
	//echo $sql . "\r\n\r\n";
	//die();
	$cases = DB::select($sql);
	$note_number = "10";
	if(count($cases)==0) {
		die("done");
	}
	foreach($cases as $key=>$case){
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$row_start_time = $time;
		
		echo "<br>Processing -> " . $key. " == " . $case->cpointer . " - ";
		
		
		$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_event` (`case_event_uuid`, `case_uuid`,  `event_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
		SELECT '" . $case->case_uuid . "', '" . $case->case_uuid . "', CONCAT('" . $customer_id . "', `evpointer`, REPLACE(evdate, '/', ''), @curRow := @curRow + 1) AS `event_uuid`, 
		'main', STR_TO_DATE(  `evdate` ,  '%m/%d/%Y' ) evdate, 'system', 'N', " . $customer_id . "
		FROM `" . $data_source . "`.`events`
		JOIN    (SELECT @curRow := 0) r
		WHERE `evpointer` = '" . $case->cpointer . "'";
		echo $sql . "\r\n\r\n<br /><br />";
		$stmt = DB::run($sql);
		
		$sql = "
		INSERT INTO `" . $data_source . "`.`" . $data_source . "_event` (`event_uuid`, `event_title`, `event_name`, `event_date`, `event_duration`, `event_description`, `event_dateandtime`, `amflag`, `full_address`, `assignee`, `event_type`, `customer_id`)
		SELECT CONCAT('" . $customer_id . "', `evpointer`, REPLACE(evdate, '/', ''), @curRow := @curRow + 1) AS `event_uuid`, IF(LENGTH(`evmemo`) > 254, SUBSTRING(`evmemo`, 1, 254), `evmemo`) `event_title`,
		IFNULL(`evdesc`, '') `evdesc`, 
		STR_TO_DATE(  `evdate` ,  '%m/%d/%Y' ) `date`, 
		'30',  
		`evmemo`, 
		CONCAT(STR_TO_DATE( evdate,  '%m/%d/%Y' ), ' ', `evtime`) event_dateandtime, 
		IF (ev.evam=1, 'AM', 'PM') `evamflag`,
		REPLACE(`location`, '<BR><BR>', ''), IFNULL(IFNULL(wcatty.`workcode`,  ev.evattycode),'') `assignee`, 
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
		
		echo $sql . "\r\n\r\n<br /><br />";
		//die();
		$stmt = DB::run($sql);
	}
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	//$success = array("success"=> array("text"=>"done", "duration"=>$total_time));
	//echo json_encode($success);
	
	//completeds
	$sql = "SELECT COUNT(`evpointer`) `case_count`
	FROM `" . $data_source . "`.`events` gcase
	WHERE 1";
	//echo $sql . "\r\n<br>";
	//die();
	$stmt = DB::run($sql);
	$cases = $stmt->fetchObject();
	
	$case_count = $cases->case_count;
	
	//completeds
	$sql = "SELECT COUNT(case_uuid) case_count
	FROM `" . $data_source . "`.`" . $data_source . "_case_event` ggc
	WHERE 1";
	//echo $sql . "\r\n<br>";
	//die();
	$stmt = DB::run($sql);
	$cases = $stmt->fetchObject();
	
	$completed_count = $cases->case_count;
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	echo "Time spent:" . $total_time . "<br />
<br />
";

	$success = array("success"=> array("text"=>"done", "duration"=>$total_time, "completed_count"=>$completed_count, "case_count"=>$case_count));
	
	if (count($cases) > 0) {
		//die("script language='javascript'>parent.runMain(" . $completed_count . "," . $case_count . ")</script");
		echo "<script language='javascript'>parent.runEvents(" . $completed_count . "," . $case_count . ")</script>";
	}
} catch(PDOException $e) {
	echo $sql;
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}

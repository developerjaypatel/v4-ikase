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
	
	if (isset($_GET["reset"])) {
		$sql_truncate = "TRUNCATE `cse_event`; 
		TRUNCATE `cse_case_event`; 
		TRUNCATE `cse_event_user`;";
		//echo $sql_truncate . "\r\n\r\n";
		$stmt = $db->prepare($sql_truncate);
		$stmt->execute();		
	}
	
	$sql = "SELECT DISTINCT mc.case_id, mc.case_uuid, mc.cpointer
	FROM `cse_case` mc
	INNER JOIN `cse_cal1`
	ON mc.cpointer = `cal1`.`CASENO`
	WHERE 1
	AND mc.case_uuid NOT IN (SELECT DISTINCT case_uuid FROM `cse_case_event`)
	
	LIMIT 0, 1";

	echo $sql . "\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$cases = $stmt->fetchAll(PDO::FETCH_OBJ);
	/*
	$cases = new stdClass;
	$case = new stdClass;
	$case->cpointer = 12455;
	$case->case_uuid = "CASEUUID";
	$cases->case = $case;
	*/
	//die(print_r($cases));
	foreach($cases as $key=>$case){
		/*
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$row_start_time = $time;
		//die(print_r($case));
		*/
		echo "Processing -> " . $case->cpointer . "<br>";
		
		$sql = "INSERT INTO `cse_case_event` (`case_event_uuid`, `case_uuid`,  `event_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
		SELECT DISTINCT '" . $case->case_uuid . "', '" . $case->case_uuid . "', 
		CONCAT(cal1.`EVENTNO`, '_', cal1.`CASENO`) AS `event_uuid`, 
		'main', `DATE` evdate, IFNULL(cal1.`INITIALS`, 'system'), 'N', " . $customer_id . "
		FROM `" . $data_source . "`.cal1
		LEFT OUTER JOIN `" . $data_source . "`.cal2
		ON cal1.EVENTNO = cal2.EVENTNO
		WHERE `CASENO` = '" . $case->cpointer . "'
		AND cal1.`EVENTNO` != '0'";
		echo $sql . "<br /><br />\r\n\r\n";
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		$sql = "
		INSERT INTO `cse_event` (`event_uuid`, `event_title`, `event_name`, `event_duration`, `event_description`, `event_dateandtime`, `full_address`, `judge`, `assignee`, 
		`event_first_name`, `event_last_name`,
		`event_type`, `customer_id`)
		SELECT DISTINCT CONCAT(cal1.`EVENTNO`, '_', cal1.`CASENO`) AS `event_uuid`, 
		IFNULL(CONCAT(`FIRST`, ' ', `LAST`, ' vs ', `DEFENDANT`), IFNULL(`EVENT`, '')) `event_title`,
		IFNULL(`EVENT`, '') `EVENT`, 
		'30',  
		`NOTES` `evmemo`, 
		cal1.`DATE` event_dateandtime, 
		IFNULL(`VENUE`, '') `location`, IFNULL(`JUDGE`, '') `judge`, 
		IFNULL(ATTYASS, '')  `assignee`, 
		IFNULL(`FIRST`, '') `FIRST`, IFNULL(`LAST`, '') `LAST`,
		`CALENDAR` `event_type`,
		'" . $customer_id . "' 
		FROM `" . $data_source . "`.cal1
		LEFT OUTER JOIN `" . $data_source . "`.cal2
		ON cal1.EVENTNO = cal2.EVENTNO
		WHERE 1
		AND cal1.`EVENTNO` != '0'
		AND `CASENO` = '" . $case->cpointer . "'";
		
		echo $sql . "<br /><br />\r\n\r\n";
		$stmt = $db->prepare($sql);
		$stmt->execute();
		/*
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $row_start_time), 4);
		echo "Time spent:" . $total_time . "\r\n\r\n";
		*/
	}
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	//$success = array("success"=> array("text"=>"done", "duration"=>$total_time));
	//echo json_encode($success);
	
	//completeds
	$sql = "SELECT COUNT(DISTINCT `CASENO`) `case_count`
	FROM `cse_cal1` gcase
	WHERE 1";
	echo $sql . "\r\n<br>";
	//die();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$cases = $stmt->fetchObject();
	
	$case_count = $cases->case_count;
	
	//completeds
	$sql = "SELECT COUNT(DISTINCT case_uuid) case_count
	FROM `cse_case_event` ggc
	WHERE 1";
	echo $sql . "\r\n<br>";
	//die();
	$stmt = $db->prepare($sql);
	$stmt->execute();
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
	
	$db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
	?>
    <script language="javascript">
parent.setFeedback("events import error");
</script>
    <?php
	die();
}
?>
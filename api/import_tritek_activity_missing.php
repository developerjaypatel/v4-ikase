<?php
require_once('../shared/legacy_session.php');
set_time_limit(3000);

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;

include("connection.php");
?>
<script language="javascript">
parent.setFeedback("activity import started");
</script>
<?php
try {
	$db = getConnection();
	
	include("customer_lookup.php");
	
	/*
	TRUNCATE leyva.leyva_activity;
	TRUNCATE leyva.leyva_case_activity;
	*/
	
	$sql = "SELECT DISTINCT mc.case_id, mc.case_uuid, mc.cpointer
	FROM `" . $data_source . "`.`" . $data_source . "_case` mc
	INNER JOIN `" . $data_source . "`.missings mis
	ON mc.cpointer = mis.cpointer AND mis.activity_done = 'N'
	INNER JOIN `" . $data_source . "`.`activity`
	ON mc.cpointer = `activity`.`cpointer`
	LEFT OUTER JOIN `" . $data_source . "`.`" . $data_source . "_case_activity` cact
	ON mc.case_uuid = cact.case_uuid
	WHERE 1
	AND cact.case_uuid IS NULL
	ORDER BY mc.cpointer DESC
	LIMIT 0, 1";

	//die($sql);
	$stmt = $db->prepare($sql);
	//echo $sql . "\r\n\r\n";
	$stmt->execute();
	
	$cases = $stmt->fetchAll(PDO::FETCH_OBJ);
	
	foreach($cases as $key=>$case){
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$row_start_time = $time;
		
		$processing = "<br>Processing -> " . $key. " == " . $case->cpointer . "<br /><br />\r\n";
		echo $processing . "  ";
		
		$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_activity` (`case_activity_uuid`, `case_uuid`,  `activity_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
		SELECT '" . $case->case_uuid . "', '" . $case->case_uuid . "', 
		CONCAT('" . $customer_id . "', '_', `cpointer`, '_', @curRow := @curRow + 1) AS `activity_uuid`, 
		'main', 
		CAST(CONCAT(STR_TO_DATE(  `date` ,  '%m/%d/%Y' ), ' ', CONCAT(IF(`time` IS NULL, IF (`ampm` = '      AM', '00:00', `ampm`), `time`), IF(`am` IS NULL, ':00', ':00'))) AS DATETIME) activitydate, 
		'system', 'N', " . $customer_id . "
		FROM `" . $data_source . "`.`activity`
		JOIN    (SELECT @curRow := 0) r
		WHERE `cpointer` = '" . $case->cpointer . "'
		AND `time` != 'm'
		AND `date` != '1 /  /'
		AND `date` != '  /  /'";
		
		echo $sql . "<br /><br />\r\n\r\n";
		$stmt = DB::run($sql);
		
		$sql = "
		INSERT INTO `" . $data_source . "`.`" . $data_source . "_activity` 
		(`activity_uuid`, `activity`, `activity_date`, `hours`, `timekeeper`, `activity_user_id`, `customer_id`)
		SELECT CONCAT('" . $customer_id . "', '_', `cpointer`, '_', @curRow := @curRow + 1) as activity_uuid, 
		SUBSTRING(IF(`comments` != `comments2`, CONCAT(`comments`,'\r\n', `comments2`), `comments`), 1, 255) activity, 
		CAST(CONCAT(STR_TO_DATE(  `date` ,  '%m/%d/%Y' ), ' ', CONCAT(IF(`time` IS NULL, IF (`ampm` = '      AM', '00:00', `ampm`), `time`), IF(`am` IS NULL, ':00', ':00'))) AS DATETIME) activitydate, 
		ROUND(IFNULL(`hours`, 0), 2) `hours`, 
		IFNULL(`timekeeper`, '') `timekeeper`, 0, '" . $customer_id . "' 
		FROM `" . $data_source . "`.`activity`
		JOIN    (SELECT @curRow := 0) r
		WHERE 1
		AND `cpointer` = '" . $case->cpointer . "'
		AND `time` != 'm'
		AND `date` != '1 /  /'
		AND `date` != '  /  /'";
		
		echo $sql . "<br /><br />\r\n\r\n";
		//die();
		$stmt = DB::run($sql);
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $row_start_time), 4);
		echo " => Time spent:" . $total_time . "
		
		<br /><br />";
		
		$sql = "UPDATE `" . $data_source . "`.`missings` 
		SET activity_done = 'Y'
		WHERE cpointer = '" . $case->cpointer . "'";
		echo $sql . "\r\n\r\n";
		$stmt = DB::run($sql);
	}
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	//completeds
	$sql = "SELECT COUNT(*) case_count
		FROM `" . $data_source . "`.`missings` gcase
		WHERE 1";
		echo $sql . "\r\n<br>";
		//die();
		$stmt = DB::run($sql);
		$cases = $stmt->fetchObject();
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $header_start_time), 4);
		echo " => QUERY completed in " . $total_time . "<br /><br />"; 
		
		$case_count = $cases->case_count;
		
		//completeds
		$sql = "SELECT COUNT(cpointer) case_count
		FROM `" . $data_source . "`.`missings` ggc
		WHERE activity_done = 'Y'";
		echo $sql . "\r\n<br>";
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
	
	$success = array("success"=> array("text"=>"done", "duration"=>$total_time, "completed_count"=>$completed_count, "case_count"=>$case_count));
	
	if (count($cases) > 0) {
		//die("script language='javascript'>parent.runMain(" . $completed_count . "," . $case_count . ")</script");
		echo "<script language='javascript'>parent.runActivityMissing(" . $completed_count . "," . $case_count . ")</script>";
	}
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}

//include("cls_logging.php");
?>

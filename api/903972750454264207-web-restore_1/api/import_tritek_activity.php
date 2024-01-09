<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include("manage_session.php");
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
	UPDATE " . $data_source . ".activity act, " . $data_source . ".`client` cli
	SET act.`date` = cli.intakedate 
	WHERE act.cpointer = cli.cpointer
	AND act.`date` = '';
	
	UPDATE " . $data_source . ".activity act, " . $data_source . ".`client` cli
	SET act.`time` = '08:00'
	WHERE act.cpointer = cli.cpointer
	AND act.`time` = '  .';
	
	
	UPDATE " . $data_source . ".activity act, " . $data_source . ".`client` cli
	SET act.`time` = REPLACE(act.`time`, '.', ':')
	WHERE act.cpointer = cli.cpointer
	AND INSTR(`time`, '.') > 0
	
	UPDATE " . $data_source . ".activity act, " . $data_source . ".`client` cli
	SET `time` = ''
	WHERE act.cpointer = cli.cpointer
    AND LENGTH(`time`) < 5
    AND `time` != ''
	
	UPDATE `" . $data_source . "`.`activity`
    SET `time` = ''
	WHERE 1
    AND INSTR(`time`, ' ') > 0
    AND `time` != ' '
	*/
	
	$sql = "SELECT DISTINCT mc.case_id, mc.case_uuid, mc.cpointer
	FROM `" . $data_source . "`.`" . $data_source . "_case` mc
	INNER JOIN `" . $data_source . "`.`activity`
	ON mc.cpointer = `activity`.`cpointer`
	LEFT OUTER JOIN `" . $data_source . "`.`" . $data_source . "_case_activity` cact
	ON mc.case_uuid = cact.case_uuid
	WHERE 1
	AND cact.case_uuid IS NULL
	LIMIT 0, 1";

	echo $sql . "<br />";
	//die();
	
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$cases = $stmt->fetchAll(PDO::FETCH_OBJ);
	$rand = rand(100,200);
	
	//die(print_r($cases));
	foreach($cases as $key=>$case){
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$row_start_time = $time;
		
		$total_time = round(($row_start_time - $header_start_time), 4); 
		
		echo " => initial:" . $total_time . "
		
		<br /><br />";
		
		
		
		$processing = "<br>Processing -> " . $key. " == " . $case->cpointer . "<br /><br />\r\n";
		echo $processing . "  ";
		
		
		$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_activity` (`case_activity_uuid`, `case_uuid`,  `activity_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
		SELECT '" . $case->case_uuid . "', '" . $case->case_uuid . "', 
		CONCAT(TRIM(cpointer), '_" . $rand . "_', @curRow := @curRow + 1) AS `activity_uuid`, 
		'main', 
		CAST(CONCAT(STR_TO_DATE(  `date` ,  '%m/%d/%Y' ), ' ', CONCAT(IF(`time` IS NULL, IF (`ampm` = '      AM', '00:00', `ampm`), `time`), IF(`am` IS NULL, ':00', ':00'))) AS DATETIME) activitydate, 
		'system', 'N', " . $customer_id . "
		FROM `" . $data_source . "`.`activity`
		JOIN    (SELECT @curRow := 0) r
		WHERE cpointer = '" . $case->cpointer . "'
		AND `date` != ''";
		
		echo $sql . "<br /><br />\r\n\r\n";
		//die($sql);
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $row_start_time), 4);
		echo " => first insert spent:" . $total_time . "
		
		<br /><br />";
		
		$sql = "
		INSERT INTO `" . $data_source . "`.`" . $data_source . "_activity` 
		(`activity_uuid`, `activity`, `activity_date`, `hours`, `timekeeper`, `activity_user_id`, `customer_id`)
		SELECT CONCAT(TRIM(cpointer), '_" . $rand . "_', @curRow := @curRow + 1) as activity_uuid, 
		SUBSTRING(IF(`comments` != `comments2`, CONCAT(`comments`,'\r\n', `comments2`), `comments`), 1, 255) activity, 
		CAST(CONCAT(STR_TO_DATE(  `date` ,  '%m/%d/%Y' ), ' ', CONCAT(IF(`time` IS NULL, IF (`ampm` = '      AM', '00:00', `ampm`), `time`), IF(`am` IS NULL, ':00', ':00'))) AS DATETIME) activitydate, 
		ROUND(IFNULL(`hours`, 0), 2) `hours`, 
		IFNULL(`timekeeper`, '') `timekeeper`, 0, '" . $customer_id . "' 
		FROM `" . $data_source . "`.`activity`
		JOIN    (SELECT @curRow := 0) r
		WHERE 1
		AND cpointer = '" . $case->cpointer . "'
		AND `date` != ''";
		
		echo $sql . "<br /><br />\r\n\r\n";
		//die();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$count = $stmt->rowCount();
		
		echo $count . " rows affected<br />
		";
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $row_start_time), 4);
		echo " => Time spent:" . $total_time . "
		
		<br /><br />";
		
		//die();
	}
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	echo " => Total spent:" . $total_time . "
		
		<br /><br />";
		
	//$success = array("success"=> array("text"=>"done", "duration"=>$total_time));
	//echo json_encode($success);
	
	//$success = array("success"=> array("text"=>"done", "duration"=>$total_time));
	//completeds
	$sql = "SELECT COUNT(cpointer) case_count
	FROM `" . $data_source . "`.`activity` gcase
	WHERE 1";
	//echo $sql . "\r\n<br>";
	//die();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$cases = $stmt->fetchObject();
	
	$case_count = $cases->case_count;
	
	//completeds
	$sql = "SELECT COUNT(case_uuid) case_count
	FROM `" . $data_source . "`.`" . $data_source . "_case_activity` ggc
	WHERE 1";
	//echo $sql . "\r\n<br>";
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
	
	$success = array("success"=> array("text"=>"done", "duration"=>$total_time, "completed_count"=>$completed_count, "case_count"=>$case_count));
	
	if (count($cases) > 0) {
		//die("script language='javascript'>parent.runMain(" . $completed_count . "," . $case_count . ")</script");
		echo "<script language='javascript'>parent.runActivity(" . $completed_count . "," . $case_count . ")</script>";
	}
		
	$db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}

//include("cls_logging.php");
?>
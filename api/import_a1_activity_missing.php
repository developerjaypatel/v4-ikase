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
	//$data_source = "glauber";
	
	if (isset($_GET["reset"])) {
		$sql_truncate = "TRUNCATE `" . $data_source . "`.`" . $data_source . "_activity`; 
		TRUNCATE `" . $data_source . "`.`" . $data_source . "_case_activity`; ";
		echo $sql_truncate . "<br />";
		//die();
		$stmt = DB::run($sql_truncate);
		?>
        <script language="javascript">
parent.setFeedback("activity reset completed");
</script>
        <?php
		die();
	}
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$row_start_time = $time;
	
	$sql = "SELECT mc.case_id, mc.case_uuid, mc.cpointer
	FROM `" . $data_source . "`.`" . $data_source . "_case` mc
	INNER JOIN `" . $data_source . "`.`caseact`
	ON mc.cpointer = `caseact`.`CASENO`
	WHERE 1
	AND `EVENT` != ''
	
	AND mc.cpointer > 19752
	ORDER BY mc.cpointer DESC
	LIMIT 0, 1";
	//AND mc.case_uuid NOT IN (SELECT case_uuid FROM `" . $data_source . "`.`" . $data_source . "_case_activity`)
	
	echo $sql . "<br><br>";
	die();
	$cases = DB::select($sql);
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $row_start_time), 4);
	echo " => INITIAL SQL spent:" . $total_time . "<br><br>";
	
	//$cases = array(array("cpointer"=>19001, "case_uuid"=>"CASEUUID"));
	/*
	$cases = new stdClass;
	$case = new stdClass;
	$case->cpointer = 19001;
	$case->case_uuid = "CASEUUID";
	$cases->case = $case;
	*/
	foreach($cases as $key=>$case){
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$row_start_time = $time;
		
		$processing = "<br>Processing -> " . $key. " == " . $case->cpointer;
		echo $processing . "  ";
		
		$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_activity` 
		(`case_activity_uuid`, `case_uuid`,  `activity_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
		SELECT '" . $case->case_uuid . "', '" . $case->case_uuid . "', 
		CONCAT(`CASENO`, '_', `ACTNO`, '_', @curRow := @curRow + 1) AS `activity_uuid`, 
		'main', 
		`DATE` activitydate, 
		'system', 'N', " . $customer_id . "
		FROM `" . $data_source . "`.`caseact`
		JOIN    (SELECT @curRow := 0) r
		WHERE `CASENO` = '" . $case->cpointer . "'
		AND `EVENT` != ''";
		
		echo $sql . "<br><br><br /><br />";
		$stmt = DB::run($sql);
		
		$sql = "
		INSERT INTO `" . $data_source . "`.`" . $data_source . "_activity` 
		(`activity_uuid`, `activity`, `activity_date`, `flag`, `hours`, `timekeeper`, `initials`, `attorney`, `activity_user_id`, `customer_id`)
		SELECT DISTINCT CONCAT(`CASENO`, '_', `ACTNO`, '_', @curRow := @curRow + 1) AS `activity_uuid`,  
		`EVENT` activity, 
		`DATE` activitydate, IF(`REDALERT`=1, 'red', '') flag,
		(`MINUTES` / 60) `hours`, 
		`INITIALS0` `timekeeper`, `INITIALS` `initials`, `ATTY` `attorney`, 0, '" . $customer_id . "' 
		FROM `" . $data_source . "`.`caseact`
		JOIN    (SELECT @curRow := 0) r
		WHERE 1
		AND `CASENO` = '" . $case->cpointer . "'
		AND `EVENT` != ''";
		
		echo $sql . "<br><br><br /><br />";
		//die();
		$stmt = DB::run($sql);
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $row_start_time), 4);
		echo " => Time spent:" . $total_time . "<br><br>";
		
		//die();
	}
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	$success = array("success"=> array("text"=>"done", "duration"=>$total_time));
	//echo json_encode($success);
	
	//completeds
	$sql = "SELECT COUNT(DISTINCT `CASENO`) `case_count`
	FROM `" . $data_source . "`.`caseact` gcase
	WHERE 1";
	echo $sql . "<br><br>";
	//die();
	$stmt = DB::run($sql);
	$cases = $stmt->fetchObject();
	
	$case_count = $cases->case_count;
	
	//completeds
	$sql = "SELECT COUNT(DISTINCT case_uuid) case_count
	FROM `" . $data_source . "`.`" . $data_source . "_case_activity` ggc
	WHERE 1";
	echo $sql . "<br><br>";
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
		echo "<script language='javascript'>parent.runActivity(" . $completed_count . "," . $case_count . ")</script>";
	}
} catch(PDOException $e) {
	echo $sql . "<br><br><br /><br />";
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
	?>
    <script language="javascript">
parent.setFeedback("activity import error");
</script>
    <?php
	die();
}
?>

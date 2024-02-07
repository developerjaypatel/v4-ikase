<?php
include("../api/manage_session.php");
set_time_limit(3000);

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;

include("../api/connection.php");
?>
<script language="javascript">
parent.setFeedback("activity import started");
</script>
<?php
try {
	$db = getConnection();
	
	include("../api/customer_lookup.php");
	//$data_source = "glauber";
	
	if (isset($_GET["reset"])) {
		$sql_truncate = "TRUNCATE `cse_activity`; 
		TRUNCATE `cse_case_activity`; ";
		echo $sql_truncate . "<br />";
		//die();
		$stmt = $db->prepare($sql_truncate);
		$stmt->execute();
		
		$db = null;
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
	FROM `ikase_" . $data_source . "`.`cse_case` mc
	INNER JOIN " . $data_source . ".`caseact`
	ON mc.cpointer = `caseact`.`caseno`
	WHERE 1
	AND `EVENT` != ''
	AND mc.case_uuid NOT IN (SELECT case_uuid FROM `cse_case_activity`)
	LIMIT 0, 1";

	echo $sql . "<br><br>";
	//die();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	//die(print_r($cases) . " - cases");
	$cases = $stmt->fetchAll(PDO::FETCH_OBJ);
	
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
		
		$sql = "INSERT INTO `cse_case_activity` 
		(`case_activity_uuid`, `case_uuid`,  `activity_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
		SELECT '" . $case->case_uuid . "', '" . $case->case_uuid . "', 
		CONCAT(`caseno`, '_', `actno`, '_', @curRow := @curRow + 1) AS `activity_uuid`, 
		'main', 
		`DATE` activitydate, 
		'system', 'N', " . $customer_id . "
		FROM " . $data_source . ".`caseact`
		JOIN    (SELECT @curRow := 0) r
		WHERE `caseno` = '" . $case->cpointer . "'
		AND `EVENT` != ''";
		
		echo $sql . "<br><br><br /><br />";
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		$sql = "
		INSERT INTO `cse_activity` 
		(`activity_uuid`, `activity`, `activity_category`, `activity_date`, `flag`, `hours`, `timekeeper`, `initials`, `attorney`, `activity_user_id`, `customer_id`)
		SELECT DISTINCT CONCAT(`caseno`, '_', `actno`, '_', @curRow := @curRow + 1) AS `activity_uuid`,  
		`EVENT` activity, IFNULL(actdeflt.actname, '') `activity_category`,
		IF(`DATE`< '1970-01-01 00:00:00','1970-01-01 00:00:00',`DATE`) activitydate, IF(`REDALERT`=1, 'red', '') flag,
		(`MINUTES` / 60) `hours`, 
		`INITIALS0` `timekeeper`, `INITIALS` `initials`, `ATTY` `attorney`, 0, '" . $customer_id . "' 
		FROM " . $data_source . ".`caseact`
		LEFT OUTER JOIN " . $data_source . ".`actdeflt`
		ON caseact.CATEGORY = actdeflt.CATEGORY
		JOIN    (SELECT @curRow := 0) r
		WHERE 1
		AND `caseno` = '" . $case->cpointer . "'
		AND `EVENT` != ''";
		
		echo $sql . "<br><br><br /><br />";
		//die();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
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
	FROM `cse_caseact` gcase
	WHERE 1";
	echo $sql . "<br><br>";
	//die();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$cases = $stmt->fetchObject();
	
	$case_count = $cases->case_count;
	
	//completeds
	$sql = "SELECT COUNT(DISTINCT case_uuid) case_count
	FROM `cse_case_activity` ggc
	WHERE 1";
	echo $sql . "<br><br>";
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
		echo "<script language='javascript'>parent.runActivity(" . $completed_count . "," . $case_count . ")</script>";
	}
	$db = null;
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
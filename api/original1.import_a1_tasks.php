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
		$sql_truncate = "TRUNCATE `ikase_" . $data_source . "`.`cse_task`; 
		TRUNCATE `ikase_" . $data_source . "`.`cse_case_task`; 
		TRUNCATE `ikase_" . $data_source . "`.`cse_task_user`;";
		//echo $sql_truncate . "\r\n\r\n";
		$stmt = $db->prepare($sql_truncate);
		$stmt->execute();		
	}
	
	$sql = "SELECT DISTINCT mc.case_id, mc.case_uuid, mc.cpointer
	FROM `ikase_" . $data_source . "`.`cse_case` mc
	INNER JOIN `" . $GLOBALS['GEN_DB_NAME'] . "`.`tasks`
	ON mc.cpointer = `tasks`.`CASENO`
	WHERE 1
	AND mc.case_uuid NOT IN (SELECT DISTINCT case_uuid FROM `ikase_" . $data_source . "`.`cse_case_task`)
	AND IFNULL(`DATEREQ`, `COMPLETED`) IS NOT NULL
	LIMIT 0, 1";
	// AND mc.cpointer = '17998'
	echo $sql . "<br />\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$cases = $stmt->fetchAll(PDO::FETCH_OBJ);
	/*
	$cases = new stdClass;
	$case = new stdClass;
	$case->cpointer = 18821;
	$case->case_uuid = "CASEUUID";
	$cases->case = $case;
	*/
	foreach($cases as $key=>$case){
		echo "Processing -> " . $case->cpointer . "<br>";
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$row_start_time = $time;
		
		$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_task` (`case_task_uuid`, `case_uuid`,  `task_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
		SELECT '" . $case->case_uuid . "', '" . $case->case_uuid . "', 
		`MAINKEY` AS `task_uuid`, 'main', IFNULL(`DATEREQ`, `DATEASS`), IFNULL(`WHOFROM`, 'system') `WHOFROM`, 'N', " . $customer_id . "
		FROM `" . $GLOBALS['GEN_DB_NAME'] . "`.`tasks`
		WHERE `CASENO` = '" . $case->cpointer . "'
		AND IFNULL(`DATEREQ`, `COMPLETED`) IS NOT NULL
		";
		//die( $sql . "<br /><br />\r\n\r\n");
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		//IFNULL(`DATEREQ`, `COMPLETED`)
		

		//Solulab Code change start
		$sql = "
		INSERT INTO `ikase_" . $data_source . "`.`cse_task` (`task_uuid`, `task_name`, `task_description`, `task_dateandtime`, `completed_date`, `task_from`, `from`, `assignee`, `task_priority`, `task_type`,`customer_id`, `deleted`)
		SELECT 
		`MAINKEY` AS `task_uuid`, `EVENT`, '', 
		IFNULL(`DATEREQ`, `DATEASS`) task_dateandtime, IFNULL(`COMPLETED`, '0000-00-00') `completed_date`, 
		(SELECT CONCAT(FNAME,' ',LNAME) FROM `" . $GLOBALS['GEN_DB_NAME'] . "`.`staff` WHERE username = `" . $GLOBALS['GEN_DB_NAME'] . "`.`tasks`.WHOFROM) `task_from`,
		(SELECT CONCAT(FNAME,' ',LNAME) FROM `" . $GLOBALS['GEN_DB_NAME'] . "`.`staff` WHERE username = `" . $GLOBALS['GEN_DB_NAME'] . "`.`tasks`.WHOFROM) `from`, IFNULL(`WHOTO`, '') `assignee`, IFNULL(`PRIORITY`, '') task_priority,
		IF (`COMPLETED` IS NULL, 'open', 'closed') `task_type`,
		'" . $customer_id . "',
		'N' `deleted` 
		FROM `" . $GLOBALS['GEN_DB_NAME'] . "`.`tasks`
		WHERE 1
		AND IFNULL(`DATEREQ`, `COMPLETED`) IS NOT NULL
		AND `CASENO` = '" . $case->cpointer . "'
		";
		//Solulab code change end

		echo $sql . "<br /><br />\r\n\r\n";
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		$last_updated_date = date("Y-m-d H:i:s");
		$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_task_user` (`task_user_uuid`, `task_uuid`, `user_uuid`, `type`, `read_status`, `read_date`, `action`, `last_updated_date`, `last_update_user`, `customer_id`)
		SELECT ctask.`task_uuid`, 
		ctask.`task_uuid`, 
		usr.`user_uuid`,
		'to', 'Y', '" . $last_updated_date . "', 'reply', '" . $last_updated_date . "','system', " . $customer_id . "
		FROM `ikase_" . $data_source . "`.`cse_task` task
		INNER JOIN `ikase_" . $data_source . "`.`cse_case_task` ctask
		ON task.task_uuid = ctask.task_uuid
		INNER JOIN `ikase`.`cse_user` usr
		ON task.assignee = usr.nickname
		WHERE ctask.`case_uuid` = '" . $case->case_uuid . "'";
		
		echo $sql . "<br /><br />\r\n\r\n";
		// die();
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
	//echo json_encode($success);
	
	//completeds
	$sql = "SELECT COUNT(DISTINCT `CASENO`) `case_count`
	FROM `" . $GLOBALS['GEN_DB_NAME'] . "`.`tasks`
	WHERE 1";
	echo $sql . "\r\n<br>";
	//die();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$cases = $stmt->fetchObject();
	
	$case_count = $cases->case_count;
	
	//completeds
	$sql = "SELECT COUNT(DISTINCT case_uuid) case_count
	FROM `ikase_" . $data_source . "`.`cse_case_task` ggc
	WHERE 1";
	echo $sql . "\r\n<br>";
	//die();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$cases = $stmt->fetchObject();
	
	$completed_count = $cases->case_count;
	
	if (count($cases) > 0) {
		//die("script language='javascript'>parent.runMain(" . $completed_count . "," . $case_count . ")</script");
		echo "<script language='javascript'>parent.runTasks(" . $completed_count . "," . $case_count . ")</script>";
	}
	
	$db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
	?>
    <script language="javascript">
parent.setFeedback("task import error");
</script>
    <?php
	die();
}

//include("cls_logging.php");
?>
<script language="javascript">
parent.setFeedback("tasks import completed");
</script>
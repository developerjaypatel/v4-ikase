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
	
	
	$sql = "SELECT DISTINCT mc.case_id, mc.case_uuid, mc.cpointer
	FROM `" . $data_source . "`.`" . $data_source . "_case` mc
	INNER JOIN `" . $data_source . "`.`todo`
	ON mc.cpointer = `todo`.`evpointer`
	WHERE 1
	#AND mc.cpointer = 2179322
	AND mc.deleted = 'N'
	AND mc.cpointer != 0
	AND evdate != '  /  /'
	AND evdate != '/  /'
	AND `evcompdate` != '  /  /'
	AND evcompdate != '/  /'
	AND evtime != '83:0'
	AND `evtime` != '90:0'
	AND `evcompdate` != '01/  /2005'
	AND `evdate` != '02/29/2010'
	AND mc.case_uuid NOT IN (SELECT DISTINCT case_uuid FROM `" . $data_source . "`.`" . $data_source . "_case_task`)
	LIMIT 0, 10";
	echo $sql . "<br /><br />\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$cases = $stmt->fetchAll(PDO::FETCH_OBJ);
	if(count($cases)==0) {
		die("done");
	}
	
	//die(print_r($cases));
	foreach($cases as $key=>$case){
		echo "<br>Processing -> " . $key. " == " . $case->cpointer . " - <br />";
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$row_start_time = $time;
		
		$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_task` (`case_task_uuid`, `case_uuid`,  `task_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
		SELECT '" . $case->case_uuid . "', '" . $case->case_uuid . "', 
		CONCAT('" . $customer_id . "', `evpointer`, REPLACE(evdate, '/', ''), @curRow := @curRow + 1) AS `task_uuid`, 'main', STR_TO_DATE(  `evdate` ,  '%m/%d/%Y' ) evdate, 'system', 'N', " . $customer_id . "
		FROM `" . $data_source . "`.`todo`
		JOIN    (SELECT @curRow := 0) r
		WHERE `evpointer` = '" . $case->cpointer . "'
		AND evdate != '  /  /'
		AND evdate != '/  /'
		AND `evcompdate` != '  /  /'
		AND evcompdate != '/  /'
		AND evtime != '83:0'
		AND `evtime` != '90:0'
		AND `evcompdate` != '01/  /2005'
		AND `evdate` != '02/29/2010'";
		echo $sql . "<br /><br />\r\n\r\n";
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		$sql = "
		INSERT INTO `" . $data_source . "`.`" . $data_source . "_task` (`task_uuid`, `task_name`, `task_date`, `task_description`, `task_dateandtime`, amflag, `completed_date`, `full_address`, `assignee`, `task_title`, `task_type`, `customer_id`)
		SELECT 
		CONCAT('" . $customer_id . "', `evpointer`, REPLACE(evdate, '/', ''), @curRow := @curRow + 1) AS `task_uuid`, 
		IFNULL(`evdesc`, '') `evdesc`, 
		STR_TO_DATE(  `evdate` ,  '%m/%d/%Y' ) `date`, `evmemo`, 
		CONCAT(STR_TO_DATE( evdate,  '%m/%d/%Y' ), ' ', IF (`evtime`='  :', '08:01', REPLACE(`evtime`, ' ', ''))) task_dateandtime, 
		evamflag,
		IFNULL(STR_TO_DATE( ev.evcompdate,  '%m/%d/%Y' ), '0000-00-00') `completed_date`, 
		`location`, 
		#IFNULL(wcatty.workcode, '') `assignee`, 
		CONCAT(IFNULL(wcatty.workcode, ''),';', IFNULL(wc.workcode, '')) `assignee`,
		SUBSTRING(`evmemo`, 1, 255) `evmemo`, IFNULL(`evdesc`, '') `task_type`,
		'" . $customer_id . "'
		FROM `" . $data_source . "`.`todo` ev
		JOIN    (SELECT @curRow := 0) r
		LEFT OUTER JOIN `" . $data_source . "`.evcode evc ON ev.evcode = evc.evcode 
        LEFT OUTER JOIN `" . $data_source . "`.workcode wc ON ev.evworkcode = wc.workcode 
        LEFT OUTER JOIN `" . $data_source . "`.workcode wcatty ON IF(ev.evattycode = 'MG2', 'MG', ev.evattycode)  = wcatty.workcode
		LEFT OUTER JOIN `" . $data_source . "`.workcode completed ON ev.evcompby = completed.workcode
        LEFT OUTER JOIN `" . $data_source . "`.workcode entered ON ev.enteredby = entered.workcode
		WHERE 1
		AND `evpointer` = '" . $case->cpointer . "'
		AND evdate != '  /  /'
		AND evdate != '/  /'
		AND evtime != '83:0'
		AND `evtime` != '90:0'
		AND `evcompdate` != '01/  /2005'
		AND `evcompdate` != '  /  /'
		AND evcompdate != '/  /'
		AND `evdate` != '02/29/2010'";
		
		echo $sql . "<br /><br />\r\n\r\n";
		//die();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		//now go through all the tasks
		//break up the assignee
		//cycle through assignee
		$sql = "SELECT task_uuid, assignee
		FROM `" . $data_source . "`.`" . $data_source . "_task`
		WHERE task_uuid LIKE '" . $customer_id . "" . $case->cpointer . "%'";
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$tasks = $stmt->fetchAll(PDO::FETCH_OBJ);
	
		foreach ($tasks as $task) {
			$assignee = $task->assignee;
			$arrAssign = explode(";", $assignee);
			foreach($arrAssign as $assignee) {
				$last_updated_date = date("Y-m-d H:i:s");
				$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_task_user` (`task_user_uuid`, `task_uuid`, `user_uuid`, `type`, `read_status`, `read_date`, `action`, `last_updated_date`, `last_update_user`, `customer_id`)
				SELECT '" . $task->task_uuid . "', 
				'" . $task->task_uuid . "', 
				usr.`user_uuid`,
				'to', 'Y', '" . $last_updated_date . "', 'reply', '" . $last_updated_date . "','system', " . $customer_id . "
				FROM `ikase`.cse_user usr
				
				WHERE usr.`nickname` = '" . $assignee . "'
				AND usr.customer_id = '" . $customer_id . "'";
				echo $sql . "\r\n\r\n";
				$db = getConnection();
				$stmt = $db->prepare($sql);
				$stmt->execute();
			}
		}
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $row_start_time), 4);
		//echo "Time spent:" . $total_time . "<br /><br />";
	}
	//die("stop");
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	//completeds
	$sql = "SELECT COUNT(`evpointer`) `case_count`
	FROM `" . $data_source . "`.`todo` gcase
	WHERE 1";
	echo $sql . "\r\n<br>";
	//die();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$cases = $stmt->fetchObject();
	
	$case_count = $cases->case_count;
	
	//completeds
	$sql = "SELECT COUNT(ggc.case_uuid) case_count
	FROM `" . $data_source . "`.`" . $data_source . "_case_task` ggc
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
	echo "Time spent:" . $total_time . "<br /><br />";
	
	$success = array("success"=> array("text"=>"done", "duration"=>$total_time, "completed_count"=>$completed_count, "case_count"=>$case_count));
	
	if (count($cases) > 0) {
		//die("script language='javascript'>parent.runMain(" . $completed_count . "," . $case_count . ")</script");
		echo "<script language='javascript'>parent.runTasks(" . $completed_count . "," . $case_count . ")</script>";
	} else {
		die("all done");
	}
	
	$db = null;
} catch(PDOException $e) {
	echo $sql . "<br />";
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
?>
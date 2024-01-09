<?php
include("manage_session.php");
set_time_limit(3000);

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;

include("connection.php");
$customer_id = 1121;
if (!is_numeric($customer_id)) {
	die();
}

try {
	$db = getConnection();
	
	include("customer_lookup.php");
	
	
	$sql = "SELECT gcase.* 
	FROM `ikase_" . $data_source . "`.`casetasks` gcase
	WHERE processed = 'N'
	#AND gcase.task_uuid = 'KS5b72f27be828f'
	LIMIT 0, 1";
	
	echo $sql . "<br /><br />\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$tasks = $stmt->fetchAll(PDO::FETCH_OBJ);
	if(count($tasks)==0) {
		die("done");
	}
	foreach($tasks as $key=>$task){
		echo "<br>Processing -> " . $key. " == " . $task->task_uuid . "<br />";
		
		//now go through all the tasks
		//break up the assignee
		//cycle through assignee
		$sql = "SELECT MAX(task_track_id) max_id 
		FROM ikase_goldberg2.cse_task_track
		WHERE task_uuid = '" . $task->task_uuid . "'";
		
		echo $sql . "\r\n<br />";
		
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$max = $stmt->fetchObject();
		
		if (is_object($max)) {
			if ($max->max_id!="") {
				$sql = "UPDATE ikase_goldberg2.cse_task_track ctt, 
				ikase_goldberg2.cse_task ct
				#,ikase_goldberg2.cse_case_task cct
				SET ct.task_type = ctt.task_type,
				ct.assignee = ctt.assignee,
				ct.deleted = ctt.deleted,
				ct.cc = ctt.cc,
				ct.task_dateandtime = ctt.task_dateandtime,
				ct.task_date = ctt.task_date
				WHERE 1
				AND ctt.task_uuid = ct.task_uuid
				#AND ct.task_uuid = cct.task_uuid
				AND ct.task_uuid = '" . $task->task_uuid . "'
				AND ctt.task_track_id = " . $max->max_id;
				echo $sql . "\r\n<br />";
				//die();
				$stmt = $db->prepare($sql);
				$stmt->execute();
			}
		}
	}
	//die("stop");
	$sql = "UPDATE `ikase_" . $data_source . "`.`casetasks` 
	SET processed = 'Y'
	WHERE task_uuid = '" . $task->task_uuid . "'";
	echo $sql . "\r\n\r\n<br><br>";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	//die("done");
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	//$success = array("success"=> array("text"=>"done", "duration"=>$total_time));
	//completeds
	/*
	$sql = "SELECT COUNT(task_uuid) case_count
	FROM `ikase_" . $data_source . "`.`casetasks` gcase
	WHERE 1";
	//echo $sql . "\r\n<br>";
	//die();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$cases = $stmt->fetchObject();
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	echo " => QUERY completed in " . $total_time . "<br /><br />"; 
	
	$case_count = $cases->case_count;
	*/
	$case_count = 175;
	//completeds
	$sql = "SELECT COUNT(task_uuid) case_count
	FROM `ikase_" . $data_source . "`.`casetasks` ggc
	WHERE processed = 'Y'";
	//echo $sql . "\r\n<br>";
	//die();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$cases = $stmt->fetchObject();
	
	$completed_count = $cases->case_count;
	
	$success = array("success"=> array("text"=>"done", "duration"=>$total_time, "completed_count"=>$completed_count, "case_count"=>$case_count));
	
	if (count($cases) > 0) {
		//die("script language='javascript'>parent.runMain(" . $completed_count . "," . $case_count . ")</script");
		echo "<script language='javascript'>parent.runTaskType(" . $completed_count . "," . $case_count . ")</script>";
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
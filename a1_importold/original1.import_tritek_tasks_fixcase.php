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
	#AND gcase.task_uuid = 'KS5ba42cfd7aff2'
	LIMIT 0, 1";
	
	//echo $sql . "<br /><br />\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$tasks = $stmt->fetchAll(PDO::FETCH_OBJ);
	if(count($tasks)==0) {
		die("done");
	}
	foreach($tasks as $key=>$task){
		echo "<br>Processing -> " . $key. " == " . $task->task_uuid . "\r\n";
		
		//now go through all the tasks
		
		$sql = "SELECT MIN(task_track_id) min_id 
		FROM ikase_" . $data_source . ".cse_task_track
		WHERE task_uuid = '" . $task->task_uuid . "'";
		
		//echo $sql . "\r\n<br />";
		
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$min = $stmt->fetchObject();
		
		//die(print_r($min));
		$blnLost = false;
		if (!is_object($min)) {
			$blnLost = true;
		}
		if (is_object($min)) {
			if ($min->min_id=="") {
				$blnLost = true;
			}
			if ($min->min_id!="") {
				$sql = "SELECT * 
				FROM ikase_" . $data_source . ".cse_task_track
				WHERE task_track_id = " . $min->min_id;
				$stmt = $db->prepare($sql);
				$stmt->execute();
				$track = $stmt->fetchObject();
				//die(print_r($track));
				$track_description = trim(strip_tags($track->task_description));
				$track_date = $track->time_stamp;
				$task_type = $track->task_type;
				
				echo "Task is " . $task_type . "\r\n";
				//break up the assignee
				//cycle through assignee
				//is there a user
				$sql = "SELECT COUNT(task_user_id) task_count
				FROM ikase_" . $data_source . ".cse_task_user
				WHERE task_uuid = '" . $task->task_uuid . "'";
				$stmt = $db->prepare($sql);
				$stmt->execute();
				
				$taskuser = $stmt->fetchObject();
				
				if ($taskuser->task_count == 0) {
					
					$assignee = $track->assignee;
					$arrAssign = explode(";", $assignee);
					foreach($arrAssign as $assignee) {
						$last_updated_date = date("Y-m-d H:i:s");
						$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_task_user` (`task_user_uuid`, `task_uuid`, `user_uuid`, `type`, `read_status`, `read_date`, `action`, `last_updated_date`, `last_update_user`, `customer_id`)
						SELECT '" . $task->task_uuid . "', 
						'" . $task->task_uuid . "', 
						usr.`user_uuid`,
						'to', 'Y', '" . $track_date . "', 'reply', '" . $track_date . "','system', " . $customer_id . "
						FROM `ikase`.cse_user usr
						
						WHERE usr.`nickname` = '" . $assignee . "'
						AND usr.customer_id = '" . $customer_id . "';";
						echo $sql . "\r\n\r\n<br><br>";
						
						$stmt = $db->prepare($sql);
						$stmt->execute();
					}
				}
				$sql = "SELECT cca.case_uuid, ca.* 
				FROM ikase_" . $data_source . ".cse_activity ca
				INNER JOIN ikase_" . $data_source . ".cse_case_activity cca
				ON ca.activity_uuid = cca.activity_uuid
				WHERE 1
				AND activity_category = 'Task'
				AND INSTR(ca.activity , '" . addslashes($track_description) . "') > 0
				AND activity_date = '" . $track->time_stamp . "'";
				
				$sql = "SELECT cca.case_uuid, ca.* 
				FROM ikase_" . $data_source . ".cse_activity ca
				INNER JOIN ikase_" . $data_source . ".cse_case_activity cca
				ON ca.activity_uuid = cca.activity_uuid
				WHERE 1
				AND activity_category = 'Task'
				AND CAST(activity_date AS DATE) = '" .date("Y-m-d", strtotime($track->time_stamp)) . "'
				AND HOUR(activity_date) = '" .date("H", strtotime($track->time_stamp)) . "'
				AND MINUTE(activity_date) = '" .date("i", strtotime($track->time_stamp)) . "'
				AND INSTR(ca.activity , '" . addslashes($track_description) . "') > 0;
";
				//echo $sql . "\r\n\r\n";
				
				$stmt = $db->prepare($sql);
				$stmt->execute();
				$act = $stmt->fetchObject();
				//die(print_r($act));
				if (is_object($act)) {
					if ($act->case_uuid != "") {
						$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_task` (`case_task_uuid`, `case_uuid`,  `task_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
						VALUES ('" . $act->case_uuid . "', '" . $act->case_uuid . "','" . $task->task_uuid . "','main', '" . $track_date . "','" . $track->user_logon . "','N', '" . $customer_id . "');";
						echo $sql . "\r\n<br><br>";
						//die($sql);
						$stmt = $db->prepare($sql);
						$stmt->execute();
					}
				}
			}
		}
	}
	if ($blnLost ) {
		$sql = "UPDATE `ikase_" . $data_source . "`.`cse_task` 
		SET deleted = 'L'
		WHERE task_uuid = '" . $task->task_uuid . "'";
		echo $sql . "\r\n\r\n<br><br>";
		$stmt = $db->prepare($sql);
		$stmt->execute();
	}
	//die("stop");
	$sql = "UPDATE `ikase_" . $data_source . "`.`casetasks` 
	SET processed = 'Y'
	WHERE task_uuid = '" . $task->task_uuid . "'";
	//echo $sql . "\r\n\r\n<br><br>";
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
	$case_count = 23704;
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
		echo "<script language='javascript'>parent.runTaskCase(" . $completed_count . "," . $case_count . ")</script>";
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
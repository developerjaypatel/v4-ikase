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
	
	
	$sql = "SELECT ccase.case_id, gcase.* 
	FROM `" . $data_source . "`.`badtaskuser` gcase
	INNER JOIN `ikase_" . $data_source . "`.`CSE_CASE` Ccase
	ON gcase.case_uuid = ccase.case_uuid
	WHERE processed = 'N'
	#AND gcase.case_uuid = 'KS5b6e1cb9832ba'
	LIMIT 0, 1";
	
	echo $sql . "<br /><br />\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$cases = $stmt->fetchAll(PDO::FETCH_OBJ);
	if(count($cases)==0) {
		die("done");
	}
	foreach($cases as $key=>$case){
		echo "<br>Processing -> " . $key. " == " . $case->case_id. " ==> " . $case->case_uuid . "<br />";
		
		//now go through all the tasks
		//break up the assignee
		//cycle through assignee
		$sql = "SELECT tsk.task_uuid, tsk.assignee, tsk.cc
		FROM `ikase_" . $data_source . "`.`cse_task` tsk
		INNER JOIN `ikase_" . $data_source . "`.`cse_case_task` cct
		ON tsk.task_uuid = cct.task_uuid
		WHERE cct.case_uuid = '" . $case->case_uuid . "'
		AND tsk.deleted = 'N'
		AND tsk.assignee != ''";
		
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$tasks = $stmt->fetchAll(PDO::FETCH_OBJ);
	
		//die(print_r($tasks));
		foreach ($tasks as $task) {
			//print_r($task);
			$assignee = $task->assignee;
			$arrAssign = explode(";", $assignee);
			
			//worth doing?
			$sql = "SELECT COUNT(task_user_id) task_count 
			FROM ikase_goldberg2.cse_task_user ctu
			INNER JOIN ikase.cse_user usr
			ON ctu.user_uuid = usr.user_uuid
			WHERE task_uuid = '" . $task->task_uuid . "'
			AND ctu.deleted = 'N'
			AND `type` = 'to'";
			
			//echo $sql . "\r\n";
			
			$stmt = $db->prepare($sql);
			$stmt->execute();
			$chk = $stmt->fetchObject();
			
			//die(print_r($chk));
			if ($chk->task_count != count($arrAssign)) { 
				$sql = "UPDATE `ikase_" . $data_source . "`.`cse_task_user`
				SET deleted = 'Y'
				WHERE task_uuid = '" . $task->task_uuid . "'
				AND `type` = 'to'";
				echo $sql . "\r\n\r\n<br /><br />";
				$stmt = $db->prepare($sql);
				$stmt->execute();
			
				
				foreach($arrAssign as $assignee) {
					if ($assignee=="") {
						continue;
					}
					if ($assignee=="MG") {
						$assignee = "DI2";
					}
					$last_updated_date = date("Y-m-d H:i:s");
					$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_task_user` (`task_user_uuid`, `task_uuid`, `user_uuid`, `type`, `read_status`, `read_date`, `action`, `last_updated_date`, `last_update_user`, `customer_id`)
					SELECT '" . $task->task_uuid . "', 
					'" . $task->task_uuid . "', 
					usr.`user_uuid`,
					'to', 'Y', '" . $last_updated_date . "', 'reply', '" . $last_updated_date . "','system', " . $customer_id . "
					FROM `ikase`.cse_user usr
					
					WHERE usr.`nickname` = '" . $assignee . "'
					AND usr.customer_id = '" . $customer_id . "'";
					echo $sql . "\r\n\r\n<br /><br />";
					
					$stmt = $db->prepare($sql);
					$stmt->execute();
					
				}
				
				echo $task->task_uuid . " main done<br /><br />";
				//die();
			} else {
			 	echo $task->task_uuid . " main skipped<br />";
			}
			
			
			$cc = trim($task->cc);
			$arrCC = array();
			if ($cc!="") {
				$arrCC = explode(";", $cc);
			}
			//worth doing?
			$sql = "SELECT COUNT(task_user_id) task_count 
			FROM ikase_goldberg2.cse_task_user ctu
			INNER JOIN ikase.cse_user usr
			ON ctu.user_uuid = usr.user_uuid
			WHERE task_uuid = '" . $task->task_uuid . "'
			AND ctu.deleted = 'N'
			AND `type` = 'cc'";
			
			//echo $sql . "\r\n";
			
			$stmt = $db->prepare($sql);
			$stmt->execute();
			$chk = $stmt->fetchObject();
			
			//die(print_r($chk));
			if ($chk->task_count != count($arrCC) || count($arrCC)==0) { 
				$sql = "UPDATE `ikase_" . $data_source . "`.`cse_task_user`
				SET deleted = 'Y'
				WHERE task_uuid = '" . $task->task_uuid . "'
				AND `type` = 'cc'";
				echo $sql . "\r\n\r\n<br /><br />";
				$stmt = $db->prepare($sql);
				$stmt->execute();
			
				foreach($arrCC as $assignee) {
					if ($assignee=="") {
						continue;
					}
					if ($assignee=="MG") {
						$assignee = "DI2";
					}
					$last_updated_date = date("Y-m-d H:i:s");
					$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_task_user` (`task_user_uuid`, `task_uuid`, `user_uuid`, `type`, `read_status`, `read_date`, `action`, `last_updated_date`, `last_update_user`, `customer_id`)
					SELECT '" . $task->task_uuid . "', 
					'" . $task->task_uuid . "', 
					usr.`user_uuid`,
					'cc', 'Y', '" . $last_updated_date . "', 'reply', '" . $last_updated_date . "','system', " . $customer_id . "
					FROM `ikase`.cse_user usr
					
					WHERE usr.`nickname` = '" . $assignee . "'
					AND usr.customer_id = '" . $customer_id . "'";
					echo $sql . "\r\n\r\n<br /><br />";
					
					$stmt = $db->prepare($sql);
					$stmt->execute();
					
				}
				
				echo $task->task_uuid . " cc done<br /><br />";
				//die();
			} else {
			 	echo $task->task_uuid . " cc skipped<br />";
			}
		}
	}
	//die("stop");
	$sql = "UPDATE `" . $data_source . "`.`badtaskuser` 
	SET processed = 'Y'
	WHERE case_uuid = '" . $case->case_uuid . "'";
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
	$sql = "SELECT COUNT(*) case_count
	FROM `" . $data_source . "`.`badtaskuser` gcase
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
	
	//completeds
	$sql = "SELECT COUNT(cpointer) case_count
	FROM `" . $data_source . "`.`badtaskuser` ggc
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
		echo "<script language='javascript'>parent.runTaskUsers(" . $completed_count . "," . $case_count . ")</script>";
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
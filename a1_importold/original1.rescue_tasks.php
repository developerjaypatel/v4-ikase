<?php
include("manage_session.php");

include("connection.php");
$customer_id = 1121;

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	$sql = "
	SELECT ctt.*
	FROM ikase_goldberg2.cse_task_track ctt
	INNER JOIN (
		SELECT task_id, MAX(task_track_id) max_id
		FROM ikase_goldberg2.cse_task_track
		WHERE operation = 'insert'
        AND  time_stamp > '2018-08-08'
		GROUP BY task_id
	) maxtrack
	ON ctt.task_track_id = maxtrack.max_id
    
    
    LEFT OUTER JOIN ikase_goldberg2.cse_task task
    ON ctt.task_uuid = task.task_uuid
    
	WHERE 1
    
    AND task.task_uuid IS NULL";
	
	$stmt = $db->prepare($sql);
	$stmt = $db->query($sql);
	$tasks = $stmt->fetchAll(PDO::FETCH_OBJ);
	
	$table_name = "task";
	$arrSkipps = array();
	//die("c:" . count($tasks));
	foreach($tasks as $task) {
		//die(print_r($task));
		$operation = $task->operation;
		
		if ($operation=="insert") {
			//GET THE CASE THROUGH ACTIVITY
			$sql = "SELECT * 
			FROM  `ikase_goldberg2`.`cse_activity` ca
			INNER JOIN ikase_goldberg2.cse_case_activity cca
			ON ca.activity_uuid = cca.activity_uuid
			INNER JOIN ikase_goldberg2.cse_case ccase
			ON cca.case_uuid = ccase.case_uuid AND ccase.deleted = 'N'
			WHERE activity_date = '" . $task->time_stamp . "'
			AND activity_category = 'Task'";
			
			$stmt = $db->prepare($sql);
			$stmt = $db->query($sql);
			$activities = $stmt->fetchAll(PDO::FETCH_OBJ);
			//die(print_r($activities));
			
			if (count($activities)==0) {
				//lost?
				continue;
			}
			if (count($activities)==1) {
				$sql = "SELECT case_uuid 
				FROM ikase_goldberg2.cse_case_activity
				WHERE activity_uuid = '" . $activities[0]->activity_uuid . "'";
				//die($sql);
				$stmt = $db->prepare($sql);
				$stmt = $db->query($sql);
				$kase = $stmt->fetchObject();
				
				$case_uuid = $kase->case_uuid;
				
				//now we need the assignee
				$sql = "SELECT user_uuid 
				FROM ikase.cse_user
				WHERE nickname = '" . $task->assignee . "'
				AND customer_id = 1121";
				//echo $sql;
				$stmt = $db->prepare($sql);
				$stmt = $db->query($sql);
				$user = $stmt->fetchObject();
				//die(print_r($user));
				$arrAssignee[] = $user->user_uuid;
				
				$cc_uuid = "";
				if ($task->cc!="") {
					$sql = "SELECT user_uuid 
					FROM ikase.cse_user
					WHERE nickname = '" . $task->cc . "'";
					
					$stmt = $db->prepare($sql);
					$stmt = $db->query($sql);
					$user= $stmt->fetchObject();
					
					$arrAssignee[] = $user->user_uuid;
				}
								
				$case_table_uuid = uniqid("KA", false);
				$attribute_1 = "main";
				//now we have to attach the task to the case 
				$sql = "INSERT INTO cse_case_" . $table_name . " (`case_" . $table_name . "_uuid`, `case_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $task->task_uuid . "', 'main', '" . $task->time_stamp . "', '" . $task->user_uuid . "', '1121')";
				//echo $sql . "\r\n\r\n";  
				$stmt = $db->prepare($sql);  	
				$stmt->execute();

				//assigner
				//attach the from
				$task_user_uuid = uniqid("TD", false);
				$sql = "INSERT INTO cse_task_user (`task_user_uuid`, `task_uuid`, `user_uuid`, `type`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $task_user_uuid  ."', '" . $task->task_uuid . "', '" . $task->user_uuid . "', 'from', '" . $task->time_stamp . "', '" . $task->user_uuid . "', '1121')";
				
				//echo $sql . "\r\n\r\n";  
				$stmt = $db->prepare($sql);  	
				$stmt->execute();
				
				foreach($arrAssignee as $user_uuid) {
					$task_user_uuid = uniqid("TD", false);
					$sql = "INSERT INTO cse_task_user (`task_user_uuid`, `task_uuid`, `user_uuid`, `type`, `last_updated_date`, `last_update_user`, `customer_id`)
					VALUES ('" . $task_user_uuid  ."', '" . $task->task_uuid . "', '" . $user_uuid . "', 'to', '" . $task->time_stamp . "', '" . $task->user_uuid . "', '1121')";
					
					//echo $sql . "\r\n\r\n";  
					$stmt = $db->prepare($sql);  	
					$stmt->execute();
				}
				
				//die();
			
				$sql = "INSERT INTO `ikase_goldberg2`.`cse_task`
	(`task_uuid`, `task_name`, `from`, `task_date`, `task_description`, `task_first_name`, `task_last_name`, `task_dateandtime`, `task_end_time`, `full_address`, `assignee`, `cc`, `task_title`, `attachments`, `task_email`, `task_hour`, `task_type`, `task_from`, `task_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed`, `color`, `customer_id`, `deleted`)
	VALUES
	('" . $task->task_uuid . "', '" . addslashes($task->task_name) . "', '" . $task->from . "', '" . $task->task_date . "', '" . addslashes($task->task_description) . "', '" . addslashes($task->task_first_name) . "', '" . addslashes($task->task_last_name) . "', '" . $task->task_dateandtime . "', '" . $task->task_end_time . "', '" . addslashes($task->full_address) . "', '" . $task->assignee . "', '" . $task->cc . "', '" . addslashes($task->task_title) . "', '" . $task->attachments . "', '" . $task->task_email . "', '" . $task->task_hour . "', '" . $task->task_type . "', '" . $task->task_from . "', '" . $task->task_priority . "', '" . $task->end_date . "', '" . $task->completed_date . "', '" . $task->callback_date . "', '" . $task->callback_completed . "', '" . $task->color . "', '" . $task->customer_id . "','" . $task->deleted . "')";
	
				//echo $sql . "\r\n\r\n";  
				$stmt = $db->prepare($sql);
				$stmt = $db->query($sql);
				
				echo $task->task_uuid . " done<br />";
				//die();
			} else {
				$arrSkipps[] = $task->task_uuid;
			}
			//die();
		}
		/*
		if ($operation=="update") {
			$sql = "UPDATE ikase_goldberg2.cse_task
			SET task_name = '" . addslashes($task->task_name) . "',
			task_description = '" . addslashes($task->task_description) . "',
			task_first_name = '" . addslashes($task->task_first_name) . "',
			task_last_name = '" . addslashes($task->task_last_name) . "',
			task_last_name = '" . addslashes($task->task_last_name) . "',
			`task_dateandtime` = '" . $task->task_dateandtime . "',
			`task_end_time` = '" . $task->task_end_time . "',
			`full_address` = '" . addslashes($task->full_address) . "',
			`assignee` = '" . $task->assignee . "',
			`cc` = '" . $task->cc . "',
			`task_title` = '" . addslashes($task->task_title) . "',
			`attachments` = '" . $task->attachments . "',
			`task_email` = '" . $task->task_email . "',
			`task_hour` = '" . $task->task_hour . "',
			`task_type` = '" . $task->task_type . "',
			`task_from` = '" . $task->task_from . "',
			`task_priority` = '" . $task->task_priority . "',
			`end_date` = '" . $task->end_date . "',
			`completed_date` = '" . $task->completed_date . "',
			`callback_date` = '" . $task->callback_date . "',
			`callback_completed` = '" . $task->callback_completed . "'
			WHERE task_uuid = '" . $task->task_uuid . "'";
		
			//die($sql);
			$stmt = $db->prepare($sql);
			$stmt = $db->query($sql);
			
			echo $task->task_uuid . " done<br />";
		}
		*/
	}
	$db = null;
	
	$fp = fopen('rescue_data.txt', 'a');
	foreach($arrSkipps as $skip) {
		fwrite($fp, $skip);
		fwrite($fp, '\r\n');
	}
	fclose($fp);
	die("done");
} catch(PDOException $e) {
	echo $sql . "<br />";
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
incl
?>
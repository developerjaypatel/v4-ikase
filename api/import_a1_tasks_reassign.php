<?php
require_once('../shared/legacy_session.php');
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

$last_updated_date = date("Y-m-d H:i:s");
$assignee = 'CXM';
$new_assignee = 'CM';
$new_user_uuid = 'TS58d0010ac7df6';
try {
	$db = getConnection();
	
	include("customer_lookup.php");
	
	
	$sql = "SELECT ct.task_id, ct.task_uuid, ct.assignee
	FROM `ikase_" . $data_source . "`.`cse_task` ct
	WHERE ct.assignee = '" . $assignee . "'";
	// AND mc.cpointer = '17998'
	//echo $sql . "<br />\r\n\r\n";
	$tasks = DB::select($sql);
	foreach($tasks as $key=>$task) {
		echo "<br /><br />Processing -> " . $task->task_id . "<br>";
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$row_start_time = $time;
		
		//check if there is a record in task_user
		$sql = "SELECT COUNT(ctu.task_user_id) user_count
		FROM `ikase_" . $data_source . "`.`cse_task_user` ctu
		WHERE ctu.user_uuid = '" . $new_user_uuid . "'
		AND ctu.task_uuid = '" . $task->task_uuid . "'";
		// AND mc.cpointer = '17998'
		//echo $sql . "<br />\r\n\r\n";
		$stmt = DB::run($sql);
		$task_user = $stmt->fetchObject();
		
		echo "tu:" . $task_user->user_count . "<br />";
		
		if ($task_user->user_count==0) {
			$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_task_user` (`task_user_uuid`,
`task_uuid`, `user_uuid`, `thread_uuid`, `type`, `read_status`, `read_date`, `action`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES('" . $task->task_uuid . "', '" . $task->task_uuid . "', '" . $new_user_uuid . "', '', 'to', 'N', '0000-00-00', 'reply', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
		
			//die($sql);
			$stmt = DB::run($sql);
			echo "task was updated<br />";
		} else {
			echo "task was recorded ok<br />";
		}
		
		//update the task with correct nickname
		$sql = "UPDATE `ikase_" . $data_source . "`.`cse_task`
		SET assignee = '" . $new_assignee . "'
		WHERE task_id = '" . $task->task_id . "'";
		echo "nickname updated<br />";
		//die($sql);		
		$stmt = DB::run($sql);
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $row_start_time), 4);
		echo "Time spent:" . $total_time . "<br />\r\n\r\n";
	}
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	$success = array("success"=> array("text"=>"done", "duration"=>$total_time));
	//echo json_encode($success);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
	die();
}

//include("cls_logging.php");

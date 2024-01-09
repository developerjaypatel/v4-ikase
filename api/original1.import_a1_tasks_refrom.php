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

$last_updated_date = date("Y-m-d H:i:s");
$assigner = 'TXS';
$new_assigner = 'TXS';
$new_user_uuid = 'TXS_1075';
try {
	$db = getConnection();
	
	echo "From:" . $assigner . "<br />";
	include("customer_lookup.php");
	
	$sql = "SELECT tsk.WHOFROM, ct.* 
	FROM ikase_" . $data_source . ".cse_task ct
	INNER JOIN " . str_replace("2", "", $data_source) . ".tasks tsk
	ON ct.task_uuid = tsk.MAINKEY
	LEFT OUTER JOIN ikase_" . $data_source . ".cse_task_user ctu
	ON ct.task_uuid = ctu.task_uuid AND ctu.`type` = 'from'
	WHERE 1
	AND ctu.task_user_id IS NULL
	AND tsk.WHOFROM = '" . $assigner . "'";
	
	//echo $sql . "<br />\r\n\r\n";
	//die();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$tasks = $stmt->fetchAll(PDO::FETCH_OBJ);
	$stmt->closeCursor(); $stmt = null; $db = null;
	foreach($tasks as $key=>$task) {
		echo "<br /><br />Processing -> " . $task->task_id . "<br>";
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$row_start_time = $time;
		
		
		if ($task_user->user_count==0) {
			$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_task_user` (`task_user_uuid`,
`task_uuid`, `user_uuid`, `thread_uuid`, `type`, `read_status`, `read_date`, `action`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES('" . $task->task_uuid . "', '" . $task->task_uuid . "', '" . $new_user_uuid . "', '', 'from', 'N', '0000-00-00', 'reply', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
		
			//die($sql);
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->execute();
			$stmt = null; $db = null;
			echo "task was updated<br />";
		} else {
			echo "task was recorded ok<br />";
		}
		
		//update the task with correct nickname
		$sql = "UPDATE `ikase_" . $data_source . "`.`cse_task`
		SET `from` = '" . $new_assigner . "'
		WHERE task_id = '" . $task->task_id . "'";
		echo "nickname updated<br />";
		//die($sql);		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$stmt = null; $db = null;
		
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
	
	$db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
	die();
}

//include("cls_logging.php");
?>
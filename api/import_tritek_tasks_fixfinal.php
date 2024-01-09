<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once('../shared/legacy_session.php');
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
	LIMIT 0, 5000";
	
	//echo $sql . "<br /><br />\r\n\r\n";
	$tasks = DB::select($sql);
	if(count($tasks)==0) {
		die("done");
	}
	foreach($tasks as $key=>$thetask){
		echo "<br>Processing -> " . $key. " == " . $thetask->task_uuid . "\r\n";
		
		//now go through all the tasks
		
		$sql = "SELECT DISTINCT gt.assignee 
		FROM ikase_goldberg2.cse_task ct
		INNER JOIN goldberg2.goldberg2_task gt
		ON ct.task_uuid = gt.task_uuid
		WHERE ct.task_uuid = '" . $thetask->task_uuid . "'";
		
		$stmt = DB::run($sql);
		$task = $stmt->fetchObject();
		
		$assignee = $task->assignee;
		$arrAssign = explode(";", $assignee);
		for($int = 0; $int < count($arrAssign); $int++) {
			if ($arrAssign[$int]=="MG") {
				$arrAssign[$int] = "DI2";
			}
		}
		$sql = "UPDATE `ikase_" . $data_source . "`.`cse_task`
		SET assignee = '" . implode(";", $arrAssign) . "'
		WHERE task_uuid = '" . $thetask->task_uuid . "'";
		//echo $sql . "\r\n\r\n<br /><br />";
		$stmt = DB::run($sql);
		
		$sql = "UPDATE `ikase_" . $data_source . "`.`cse_task_user`
		SET deleted = 'Y'
		WHERE task_uuid = '" . $thetask->task_uuid . "'
		AND `type` = 'to'";
		//echo $sql . "\r\n\r\n<br /><br />";
		$stmt = DB::run($sql);
	
		
		foreach($arrAssign as $assignee) {
			if ($assignee=="") {
				continue;
			}
			$last_updated_date = date("Y-m-d H:i:s");
			$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_task_user` (`task_user_uuid`, `task_uuid`, `user_uuid`, `type`, `read_status`, `read_date`, `action`, `last_updated_date`, `last_update_user`, `customer_id`)
			SELECT '" . $thetask->task_uuid . "', 
			'" . $thetask->task_uuid . "', 
			usr.`user_uuid`,
			'to', 'Y', '" . $last_updated_date . "', 'reply', '" . $last_updated_date . "','system', " . $customer_id . "
			FROM `ikase`.cse_user usr
			
			WHERE usr.`nickname` = '" . $assignee . "'
			AND usr.customer_id = '" . $customer_id . "'";
			//echo $sql . "\r\n\r\n<br /><br />";
			
			$stmt = DB::run($sql);
			
		}
		
		//echo $thetask->task_uuid . " main done<br /><br />";
		
		$sql = "UPDATE `ikase_" . $data_source . "`.`casetasks` 
		SET processed = 'Y'
		WHERE task_uuid = '" . $thetask->task_uuid . "'";
		//echo $sql . "\r\n\r\n<br><br>";
		$stmt = DB::run($sql);
		
	}
	
	//completeds

	$sql = "SELECT COUNT(task_uuid) case_count
	FROM `ikase_" . $data_source . "`.`casetasks` gcase
	WHERE 1";
	//echo $sql . "\r\n<br>";
	//die();
	$stmt = DB::run($sql);
	$cases = $stmt->fetchObject();
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	echo " => QUERY completed in " . $total_time . "<br /><br />"; 
	
	$case_count = $cases->case_count;
	
	//$case_count = 23704;
	//completeds
	$sql = "SELECT COUNT(task_uuid) case_count
	FROM `ikase_" . $data_source . "`.`casetasks` ggc
	WHERE processed = 'Y'";
	//echo $sql . "\r\n<br>";
	//die();
	$stmt = DB::run($sql);
	$cases = $stmt->fetchObject();
	
	$completed_count = $cases->case_count;
	
	$success = array("success"=> array("text"=>"done", "duration"=>$total_time, "completed_count"=>$completed_count, "case_count"=>$case_count));
	
	die(print_r($success));
} catch(PDOException $e) {
	echo $sql . "<br />";
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}

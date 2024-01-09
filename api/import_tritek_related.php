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

try {
	$db = getConnection();
	
	include("customer_lookup.php");
	
	
	$sql = "SELECT DISTINCT
        badtasks.*
    FROM
        `" . $data_source . "`.`" . $data_source . "_case` mc
    INNER JOIN `" . $data_source . "`.`badtasks` ON mc.cpointer = badtasks.cpointer
    WHERE
        1 and badtasks.deleted = 'N'
    ORDER BY mc.cpointer DESC
    LIMIT 0,1";
	
	echo $sql . "<br /><br />\r\n\r\n";
	$cases = DB::select($sql);
	
	//foreach($cases as $key=>$case){
		$case = $cases[0];
		$cpointer = $case->cpointer;
		echo "Processing -> " . $cpointer . "<br /><br />\r\n";
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$row_start_time = $time;
		//list all the tasks for this cpointer
		$sql = "SELECT CONCAT('1070', `evpointer`, REPLACE(evdate, '/', ''), @curRow := @curRow + 1) AS `task_uuid`,
		evpointer, evworkcode, evdate, evtime 
		FROM leyva.todo ev
		JOIN    (SELECT @curRow := 0) r
		WHERE evpointer = '" . $cpointer . "'
		AND evdate != '  /  /'
		AND evdate != '/  /'
		AND evtime != '83:0'
		AND `evtime` != '90:0'
		AND `evcompdate` != '01/  /2005'
		AND `evcompdate` != '  /  /'
		AND evcompdate != '/  /'
		AND `evdate` != '02/29/2010'";
		
		echo $sql . "<br /><br />\r\n\r\n";
		$tasks = DB::select($sql);
		echo "found:" . count($tasks) . "\r\n<br />";
		foreach($tasks as $tkey=>$task) {
			$sql = "
			UPDATE `" . $data_source . "`.`" . $data_source . "_task` 
			SET `assignee` = IF(`assignee`='', '" . $task->evworkcode . "', CONCAT(`assignee`,';" . $task->evworkcode . "'))
			WHERE task_uuid = '" . $task->task_uuid . "'";
			
			//echo $sql . "<br /><br />\r\n\r\n";
			//die();
			$stmt = DB::run($sql);
			
			$last_updated_date = date("Y-m-d H:i:s");
			$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_task_user` (`task_user_uuid`, `task_uuid`, `user_uuid`, `type`, `read_status`, `read_date`, `action`, `last_updated_date`, `last_update_user`, `customer_id`)

			SELECT '" . str_replace("1070", "9970", $task->task_uuid) . "' `task_user_uuid`, '" . $task->task_uuid . "' `task_uuid`, 
			usr.`user_uuid`,
			'to', 'Y', '" . $last_updated_date . "', 'reply', '" . $last_updated_date . "','system', " . $customer_id . "
			FROM `" . $data_source . "`.`" . $data_source . "_user` usr
			WHERE usr.nickname = '" . $task->evworkcode . "'";
			//echo $sql . "\r\n\r\n<br /><br />";
			//die();
			$stmt = DB::run($sql);
		}
		
		$sql = "UPDATE `" . $data_source . "`.`badtasks` 
		SET deleted = 'Y'
		WHERE cpointer = '" . $cpointer . "'";
		//echo $sql . "\r\n\r\n<br><br>";
		$stmt = DB::run($sql);
	//}
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	//completeds
	$sql = "SELECT COUNT(cpointer) case_count
	FROM `" . $data_source . "`.`badtasks` ggc
	WHERE 1";
	echo $sql . "\r\n<br>";
	//die();
	$stmt = DB::run($sql);
	$cases = $stmt->fetchObject();
	
	$case_count = $cases->case_count;
	
	//completeds
	$sql = "SELECT COUNT(cpointer) case_count
	FROM `" . $data_source . "`.`badtasks` ggc
	WHERE 1
	AND `deleted` = 'Y'";
	
	echo $sql . "\r\n<br>";
	//die();
	$stmt = DB::run($sql);
	$cases = $stmt->fetchObject();
	
	$completed_count = $cases->case_count;
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	echo "Time spent:" . $total_time . "<br /><br />";
	
	$success = array("success"=> array("text"=>"done", "duration"=>$total_time, "completed_count"=>$completed_count, "case_count"=>$case_count));
	//die(print_r($success));
	if (count($cases) > 0) {
		//die("script language='javascript'>parent.runMain(" . $completed_count . "," . $case_count . ")</script");
		echo "<script language='javascript'>parent.runRelateds(" . $completed_count . "," . $case_count . ")</script>";
	} else {
		die("all done");
	}
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}

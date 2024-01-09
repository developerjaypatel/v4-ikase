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
die("obsolete? 10/28/2018"); //FIXME: please remove lol
try {
	$db = getConnection();
	
	include("customer_lookup.php");
	
	
	$sql = "SELECT DISTINCT
        mc.case_uuid, badtasks.*
    FROM
        `" . $data_source . "`.`" . $data_source . "_case` mc
    INNER JOIN `" . $data_source . "`.`badtasks` ON mc.cpointer = badtasks.cpointer
    WHERE
        1 and badtasks.deleted = 'N'
    LIMIT 0,1";
	
	//echo $sql . "<br /><br />\r\n\r\n";
	$cases = DB::select($sql);
	//die(print_r($cases));
	//foreach($cases as $key=>$case){
		$case = $cases[0];
		$cpointer = $case->cpointer;
		echo "Processing -> " . $cpointer . "<br /><br />\r\n";
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$row_start_time = $time;
		//list all the tasks for this cpointer
		$sql = "SELECT 
		CONCAT('" . $customer_id . "', `evpointer`, REPLACE(evdate, '/', ''), @curRow := @curRow + 1) AS `task_uuid`,
		evpointer, evworkcode, evdate, evtime, @curRow 
		FROM " . $data_source . ".todo ev
		INNER JOIN `" . $data_source . "`.`" . $data_source . "_user` usr
		ON ev.evworkcode = usr.nickname
		JOIN    (SELECT @curRow := 0) r
		WHERE evpointer = '" . $cpointer . "'
		AND evdate != '  /  /'
		AND evdate != '/  /'
		AND evtime != '83:0'
		AND `evtime` != '90:0'
		AND evtime != '08:60'
		AND `evcompdate` != '01/  /2005'
		AND `evcompdate` != '  /  /'
		AND evcompdate != '/  /'
		AND evcompdate != '03/  /2011'
		AND `evdate` != '02/29/2011'
		AND `evdate` != '02/29/2014'
		AND `evdate` != '02/29/2013'
		AND `evdate` != '02/29/2010'";
		
		echo $sql . "<br /><br />\r\n\r\n";
		
		//die($sql);
		$tasks = DB::select($sql);
		echo "found:" . count($tasks) . "\r\n<br />";
		//die(print_r($tasks));
		foreach($tasks as $tkey=>$task) {
			echo "Processing Task " . $task->task_uuid . "<br /><br />";
			//die();
			//does it exist?
			$sql = "SELECT *
			FROM `" . $data_source . "`.`" . $data_source . "_task` 
			WHERE task_uuid = '" . $task->task_uuid . "'";
			
			$stmt = DB::run($sql);
			
			$task_info = $stmt->fetchObject();
			if (!is_object($task_info)) {
				//delete any remnants
				$sql = "DELETE FROM `" . $data_source . "`.`" . $data_source . "_case_task`
				WHERE task_uuid = '" . $task->task_uuid . "'";
				$stmt = DB::run($sql);
				
				$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_task` (`case_task_uuid`, `case_uuid`,  `task_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
				SELECT DISTINCT '" . $case->case_uuid . "', '" . $case->case_uuid . "', 
				'" . $task->task_uuid . "' AS `task_uuid`, 'main', STR_TO_DATE(  `evdate` ,  '%m/%d/%Y' ) evdate, 'system', 'N', " . $customer_id . "
				FROM `" . $data_source . "`.`todo`
				JOIN    (SELECT @curRow := 0) r
				WHERE `evpointer` = '" . $case->cpointer . "'
				AND evdate = '" . $task->evdate . "'
				AND evtime = '" . $task->evtime . "'
				AND evworkcode = '" . $task->evworkcode . "'
				";
				echo $sql . "<br /><br />\r\n\r\n";
				//die();
				
				$stmt = DB::run($sql);
				
				$sql = "
				INSERT INTO `" . $data_source . "`.`" . $data_source . "_task` (`task_uuid`, `task_name`, `task_date`, `task_description`, `task_dateandtime`, amflag, `completed_date`, `full_address`, `assignee`, `task_title`, `task_type`, `customer_id`)
				SELECT DISTINCT
				'" . $task->task_uuid . "' AS `task_uuid`, 
				IFNULL(`evdesc`, '') `evdesc`, 
				STR_TO_DATE(  `evdate` ,  '%m/%d/%Y' ) `date`, `evmemo`, 
				CONCAT(STR_TO_DATE( evdate,  '%m/%d/%Y' ), ' ', IF (`evtime`='  :', '08:01', REPLACE(`evtime`, ' ', ''))) task_dateandtime, 
				evamflag,
				IFNULL(STR_TO_DATE( ev.evcompdate,  '%m/%d/%Y' ), '0000-00-00') `completed_date`, 
				`location`, IFNULL(wcatty.workcode, '') `assignee`, SUBSTRING(`evmemo`, 1, 255) `evmemo`, IFNULL(`evdesc`, '') `task_type`,
				'" . $customer_id . "'
				FROM `" . $data_source . "`.`todo` ev
				JOIN    (SELECT @curRow := 0) r
				LEFT OUTER JOIN `" . $data_source . "`.evcode evc ON ev.evcode = evc.evcode 
				LEFT OUTER JOIN `" . $data_source . "`.workcode wc ON ev.evworkcode = wc.workcode 
				LEFT OUTER JOIN `" . $data_source . "`.workcode wcatty ON ev.evattycode = wcatty.workcode 
				LEFT OUTER JOIN `" . $data_source . "`.workcode completed ON ev.evcompby = completed.workcode
				LEFT OUTER JOIN `" . $data_source . "`.workcode entered ON ev.enteredby = entered.workcode
				WHERE 1
				AND `evpointer` = '" . $case->cpointer . "'
				AND evdate = '" . $task->evdate . "'
				AND evtime = '" . $task->evtime . "'
				AND evworkcode = '" . $task->evworkcode . "'
				ORDER BY ev.lastchange DESC
        		LIMIT 0, 1";
				
				echo $sql . "<br /><br />\r\n\r\n";
				//die();
				$stmt = DB::run($sql);
				/*
				$new_task_id = $db->lastInsertId();
				
				$sql = "SELECT task_uuid
				FROM `" . $data_source . "`.`" . $data_source . "_task`
				WHERE task_id = '" . $new_task_id . "'";
				$stmt = DB::run($sql);
				$task_info = $stmt->fetchObject();
				$task->task_uuid = $task_info->task_uuid;
				*/
				/*
				die(print_r($task));
				echo "not found:" . $task->task_uuid . "<br />";
				die($sql);
				*/
			}
		
			$sql = "
			UPDATE `" . $data_source . "`.`" . $data_source . "_task` 
			SET `assignee` = IF(`assignee`='', '" . $task->evworkcode . "', CONCAT(`assignee`,';" . $task->evworkcode . "'))
			WHERE task_uuid = '" . $task->task_uuid . "'";
			
			echo $sql . "<br /><br />\r\n\r\n";
			//die();
			$stmt = DB::run($sql);
			
			//clear any prior relationship
			$sql = "DELETE FROM `" . $data_source . "`.`" . $data_source . "_task_user`
			WHERE task_uuid = '" . $task->task_uuid . "'
			AND `type` = 'to'";
			
			$last_updated_date = date("Y-m-d H:i:s");
			$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_task_user` (`task_user_uuid`, `task_uuid`, `user_uuid`, `type`, `read_status`, `read_date`, `action`, `last_updated_date`, `last_update_user`, `customer_id`)

			SELECT '" . str_replace($customer_id, str_replace("10", "99", $customer_id), $task->task_uuid) . "' `task_user_uuid`, '" . $task->task_uuid . "' `task_uuid`, 
			usr.`user_uuid`,
			'to', 'Y', '" . $last_updated_date . "', 'reply', '" . $last_updated_date . "','system', " . $customer_id . "
			FROM `" . $data_source . "`.`" . $data_source . "_user` usr
			WHERE usr.nickname = '" . $task->evworkcode . "'";
			echo $sql . "\r\n\r\n<br /><br />";
			//die();
			
			//echo $sql . "<br /><br />";
			
			$stmt = DB::run($sql);
		}
		
		$sql = "UPDATE `" . $data_source . "`.`badtasks` 
		SET deleted = 'Y'
		WHERE cpointer = '" . $cpointer . "'";
		echo $sql . "\r\n\r\n<br><br>";
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
	//echo $sql . "\r\n<br>";
	//die();
	$stmt = DB::run($sql);
	$cases = $stmt->fetchObject();
	
	$case_count = $cases->case_count;
	
	//completeds
	$sql = "SELECT COUNT(cpointer) case_count
	FROM `" . $data_source . "`.`badtasks` ggc
	WHERE 1
	AND `deleted` = 'Y'";
	
	//echo $sql . "\r\n<br>";
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
		echo "<script language='javascript'>parent.runTaskWorkers(" . $completed_count . "," . $case_count . ")</script>";
	} else {
		die("all done");
	}
} catch(PDOException $e) {
	echo "SQL:" . $sql . "<br />";
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}

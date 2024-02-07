<?php
include("connection.php");
die();
$last_updated_date = date("Y-m-d H:i:s");
$customer_id = 1121;
$data_source = "goldberg2";


try {
	$db = getConnection();

	$sql = "
	SELECT cpointer FROM goldberg2.badtasks
	WHERE badtasks.deleted = 'N'
	LIMIT 0, 1";
	
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$bad = $stmt->fetchObject();
	
	$sql = "SELECT ccase.case_uuid, td.*,
	CONCAT('B" . $customer_id . "', `evpointer`, REPLACE(evdate, '/', ''), @curRow := @curRow + 1) AS `task_uuid` 
	FROM goldberg2.todo td
	JOIN    (SELECT @curRow := 0) r
	INNER JOIN goldberg2.`client` cli
	ON td.evpointer = cli.cpointer
	INNER JOIN ikase_goldberg2.cse_case ccase
	ON cli.cpointer = ccase.cpointer
	
	WHERE cli.cpointer = '" . $bad->cpointer  . "'
	AND evdate != '  /  /'
	AND evdate != '/  /'
	AND `evcompdate` != '  /  /'
	AND evcompdate != '/  /'
	AND evtime != '83:0'
	AND `evtime` != '90:0'
	AND `evcompdate` != '01/  /2005'
	AND `evdate` != '02/29/2010'";
	
	echo "Processing " . $bad->cpointer . "<br /><br />";
	//die($sql);
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$tasks = $stmt->fetchAll(PDO::FETCH_OBJ);
	
	$arrTasks = array();
	foreach($tasks as $task) {
		$task_uuid = $task->task_uuid;
		
		$sqlbytask = "SELECT task_uuid
		FROM ikase_goldberg2.cse_task
		WHERE task_uuid = '" . $task_uuid . "'";
		
		$stmt = $db->prepare($sqlbytask);
		$stmt->execute();
		$checkbytask = $stmt->fetchObject();
		
		if (!is_object($checkbytask)) {
			$arrTasks[] = "'" . $task_uuid . "'";
		}
	}
	//die(print_r($arrTasks));
	$task_counter = rand(10, 50);
	foreach($tasks as $task) {
		if (strpos($task->evdate, "02/29")!==false) {
			continue;
		} 
		//die(print_r($task));
		$task_counter++;
		$case_uuid = $task->case_uuid;
		$evmemo = $task->evmemo;
		$evdate = date("Y-m-d", strtotime($task->evdate));
		
		$sqlbydate = "SELECT task_uuid
		FROM ikase_goldberg2.cse_task
		WHERE task_description = '" . addslashes($evmemo) . "'
		AND task_date = '" . $evdate . "'";
		
		$stmt = $db->prepare($sqlbydate);
		$stmt->execute();
		$check = $stmt->fetchObject();
		
		if (!is_object($check)) {
			//die($sql);
			//die($sqlbydate . "\r\n" . $sqlbytask);
			//insert it
			$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_task` (`case_task_uuid`, `case_uuid`,  `task_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
			SELECT '" . $case_uuid . "', '" . $case_uuid . "', 
			CONCAT('B" . $customer_id . "', `evpointer`, REPLACE(evdate, '/', ''), @curRow := @curRow + 1, '_" . $task_counter . "') AS `task_uuid`, 'main', '" . $last_updated_date . "', 'system3', 'N', " . $customer_id . "
			FROM `" . $data_source . "`.`todo`
			JOIN    (SELECT @curRow := 0) r
			WHERE `evpointer` = '" . $bad->cpointer . "'
			AND evmemo = '" . addslashes($evmemo) . "'
			AND evdate = '" . $task->evdate . "'";
			//AND CONCAT('B" . $customer_id . "', `evpointer`, REPLACE(evdate, '/', ''), @curRow := @curRow + 1) NOT IN (" . implode(",", $arrTasks) . ")";
			echo $sql . "<br /><br />\r\n\r\n";
			//die();
			$stmt = $db->prepare($sql);
			$stmt->execute();
			
			$sql = "
			INSERT INTO `ikase_" . $data_source . "`.`cse_task` (`task_uuid`, `task_name`, `task_date`, `task_description`, `task_dateandtime`, `completed_date`, `full_address`, `assignee`, `task_title`, `task_type`, `color`, `customer_id`)
			SELECT 
			CONCAT('B" . $customer_id . "', `evpointer`, REPLACE(evdate, '/', ''), @curRow := @curRow + 1, '_" . $task_counter . "') AS `task_uuid`, 
			IFNULL(`evdesc`, '') `evdesc`, 
			STR_TO_DATE(  `evdate` ,  '%m/%d/%Y' ) `date`, `evmemo`, 
			CONCAT(STR_TO_DATE( evdate,  '%m/%d/%Y' ), ' ', IF (`evtime`='  :', '08:01', REPLACE(`evtime`, ' ', ''))) task_dateandtime, 
			IFNULL(STR_TO_DATE( ev.evcompdate,  '%m/%d/%Y' ), '0000-00-00') `completed_date`, 
			`location`, IFNULL(wcatty.workcode, '') `assignee`, SUBSTRING(`evmemo`, 1, 255) `evmemo`, IFNULL(`evdesc`, '') `task_type`,
			'system3', '" . $customer_id . "'
			FROM `" . $data_source . "`.`todo` ev
			JOIN    (SELECT @curRow := 0) r
			LEFT OUTER JOIN `" . $data_source . "`.evcode evc ON ev.evcode = evc.evcode 
			LEFT OUTER JOIN `" . $data_source . "`.workcode wc ON ev.evworkcode = wc.workcode 
			LEFT OUTER JOIN `" . $data_source . "`.workcode wcatty ON ev.evattycode = wcatty.workcode 
			LEFT OUTER JOIN `" . $data_source . "`.workcode completed ON ev.evcompby = completed.workcode
			LEFT OUTER JOIN `" . $data_source . "`.workcode entered ON ev.enteredby = entered.workcode
			WHERE 1
			AND `evpointer` = '" . $bad->cpointer . "'
			AND evmemo = '" . addslashes($evmemo) . "'
			AND evdate = '" . $task->evdate . "'";
			//AND CONCAT('B" . $customer_id . "', `evpointer`, REPLACE(evdate, '/', ''), @curRow := @curRow + 1) NOT IN (" . implode(",", $arrTasks) . ")";
			
			echo $sql . "<br /><br />\r\n\r\n";
			//die();
			$stmt = $db->prepare($sql);
			$stmt->execute();
			
			$count = $stmt->rowCount();
			
			if ($count==1) {
				$task_id = $db->lastInsertId();
				/*
				$sql = "SELECT task_uuid 
				FROM `ikase_" . $data_source . "`.`cse_task`
				WHERE task_id = '" . $task_id . "'";
				$stmt = $db->prepare($sqlbydate);
				$stmt->execute();
				$the_task = $stmt->fetchObject();
			
				//$arrTasks[] = "'" . $the_task->task_uuid . "'";
				*/
				$last_updated_date = date("Y-m-d H:i:s");
				$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_task_user` (`task_user_uuid`, `task_uuid`, `user_uuid`, `type`, `read_status`, `read_date`, `action`, `last_updated_date`, `last_update_user`, `customer_id`)
				SELECT task.`task_uuid`, 
				task.`task_uuid`, 
				usr.`user_uuid`,
				'to', 'Y', '" . $last_updated_date . "', 'reply', '" . $last_updated_date . "','system3', " . $customer_id . "
				FROM `ikase_" . $data_source . "`.`cse_task` task
				INNER JOIN `ikase`.`cse_user` usr
				ON task.assignee = usr.nickname AND usr.customer_id = " . $customer_id . "
				WHERE task.`task_id` = '" . $task_id . "'";
				
				echo $sql . "\r\n\r\n";
				
				$stmt = $db->prepare($sql);
				$stmt->execute();
			}
			
			//die();
		}
		$blnAttached = false;
		if (is_object($check)) {
			//make sure it's attached to the case
			$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_task` (`case_task_uuid`, `case_uuid`,  `task_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
			SELECT '" . $case_uuid . "', '" . $case_uuid . "', '" . $check->task_uuid . "', 'main', '" . $last_updated_date . "', 'system2', 'N', '1121'
			FROM dual
			WHERE NOT EXISTS (
				SELECT * 
				FROM `ikase_" . $data_source . "`.`cse_case_task`
				WHERE customer_id = " . $customer_id . "
				AND case_uuid = '" . $case_uuid . "'
				AND task_uuid = '" . $check->task_uuid . "'
			);";
			
			echo $check->task_uuid . ", ";
			//echo $sql . "\r\n\r\n";
			$stmt = $db->prepare($sql);
			$stmt->execute();
			
			$blnAttached = true;
		}
		/*
		if (!$blnAttached) {
			if (is_object($checkbytask)) {
				//make sure it's attached to the case
				$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_task` (`case_task_uuid`, `case_uuid`,  `task_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
				SELECT '" . $case_uuid . "', '" . $case_uuid . "', '" . $task_uuid . "', 'main', '" . $last_updated_date . "', 'system2', 'N', '1121'
				FROM dual
				WHERE NOT EXISTS (
					SELECT * 
					FROM `ikase_" . $data_source . "`.`cse_case_task`
					WHERE customer_id = " . $customer_id . "
					AND case_uuid = '" . $case_uuid . "'
					AND task_uuid = '" . $task_uuid . "'
				);";
				
				echo "By Task:" . $task_uuid . ", ";
				//echo $sql . "\r\n\r\n";
				$stmt = $db->prepare($sql);
				$stmt->execute();
			}
		}
		*/
	}
	
	$sql = "UPDATE goldberg2.badtasks
	SET deleted = 'Y'
	WHERE cpointer = '" . $bad->cpointer  . "'";
	//die($sql);
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "SELECT COUNT(*) case_count
	FROM `" . $data_source . "`.`badtasks` gcase
	WHERE deleted = 'Y'";
	//echo $sql . "\r\n<br>";
	//die();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$cases = $stmt->fetchObject();
	
	if ($cases->case_count > 0) {
		//die("script language='javascript'>parent.runMain(" . $completed_count . "," . $case_count . ")</script");
		echo "<script language='javascript'>parent.runTaskReconcile(" . $cases->case_count . ",5531)</script>";
	} else {
		die("all done");
	}
	
	$db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	print_r($error);
	
	die($sql);
}
?>
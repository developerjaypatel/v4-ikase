<?php
include("connection.php");

$last_updated_date = date("Y-m-d H:i:s");
$customer_id = 1121;
$data_source = "goldberg2";


try {
	$db = getConnection();

	$sql = "
	SELECT cpointer FROM goldberg2.badtasks
	WHERE badtasks.deleted = 'N'
	#AND cpointer = '2101276'
	LIMIT 0, 1";
	
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$bad = $stmt->fetchObject();
	
	$sql = "SELECT ccase.case_uuid, td.*,
	CONCAT('" . $customer_id . "', `evpointer`, REPLACE(evdate, '/', ''), @curRow := @curRow + 1) AS `task_uuid` 
	FROM goldberg2.todo td
	JOIN    (SELECT @curRow := 0) r
	INNER JOIN goldberg2.`client` cli
	ON td.evpointer = cli.cpointer
	INNER JOIN ikase_goldberg2.cse_case ccase
	ON cli.cpointer = ccase.cpointer
	
	WHERE cli.cpointer = '" . $bad->cpointer  . "'";
	
	echo "Processing " . $bad->cpointer . "<br /><br />\r\n\r\n";
	//die($sql);
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$tasks = $stmt->fetchAll(PDO::FETCH_OBJ);
	//die(print_r($tasks));
	$arrTasks = array();
	
	foreach($tasks as $task) {
		if (strpos($task->evdate, "02/29")!==false) {
			continue;
		} 
		//die(print_r($task));
		$task_counter++;
		$case_uuid = $task->case_uuid;
		$evmemo = $task->evmemo;
		$evdate = date("Y-m-d", strtotime($task->evdate));
		
		$sqlbydate = "SELECT ccase.cpointer, ct.* 
		FROM ikase_goldberg2.cse_task ct
		INNER JOIN ikase_goldberg2.cse_case_task cct
		ON ct.task_uuid = cct.task_uuid
		INNER JOIN ikase_goldberg2.cse_case ccase
		ON cct.case_uuid = ccase.case_uuid
		WHERE ccase.cpointer = '" . $bad->cpointer  . "'
		AND task_title = '" . addslashes($evmemo) . "'
		AND task_date = '" . $evdate . "'";
		
		//die($sqlbydate);
		$stmt = $db->prepare($sqlbydate);
		$stmt->execute();
		$check = $stmt->fetchObject();
		
		
		if (is_object($check)) {
			if ($task->evcomplete==1 && $check->task_type!="closed") {
				$sql = "UPDATE ikase_goldberg2.cse_task
				SET task_type = 'closed', color='system4'
				WHERE task_uuid = '" . $check->task_uuid . "'";
				
				echo $sql . "<br />\r\n\r\n";
				//die($sql);
				$stmt = $db->prepare($sql);
				$stmt->execute();
			}
		}
	}
	//die("stop");
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
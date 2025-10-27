<?php
include("manage_session.php");

include("connection.php");

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	$sql = "
	UPDATE " . $data_source . "." . $data_source . "_task task, 
	(
		SELECT tsk.task_uuid
		FROM " . $data_source . "." . $data_source . "_task tsk
		
		INNER JOIN ikase_" . $data_source . ".cse_task ctsk
		ON tsk.task_uuid = ctsk.task_uuid	
		LEFT OUTER JOIN (
			SELECT DISTINCT task_uuid FROM ikase_" . $data_source . ".cse_task_track
			WHERE task_uuid NOT LIKE 'KS%'
			AND operation = 'update'
			AND task_type != 'closed'
		) updates
		
		ON tsk.task_uuid = updates.task_uuid
		
		WHERE updates.task_uuid IS NOT NULL
	) updated
	
    SET task.deleted = 'Y'
    WHERE task.task_uuid =  updated.task_uuid";
	
	echo $sql . "\r\n\r\n";
	//$stmt = $db->prepare($sql);
	//$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_task`
	(`case_task_uuid`, `case_uuid`, `task_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id`)
	SELECT `case_task_uuid`, ccn.`case_uuid`, ccn.`task_uuid`, ccn.`attribute`, ccn.`last_updated_date`, 
	ccn.`last_update_user`, ccn.`deleted`, ccn.`customer_id` 
	FROM `" . $data_source . "`.`" . $data_source . "_case_task` ccn
	INNER JOIN " . $data_source . ".badcases
	ON ccn.case_uuid = badcases.case_uuid
	
	INNER JOIN `" . $data_source . "`.`" . $data_source . "_task` tsk
	ON ccn.task_uuid = tsk.task_uuid AND tsk.deleted = 'N'
	
	WHERE 1
	";
	
	echo $sql . "\r\n\r\n";
	//$stmt = $db->prepare($sql);
	//$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_task` (`task_uuid`, `task_name`, `from`, `task_date`, `task_description`, `task_first_name`, `task_last_name`, `task_dateandtime`, `task_end_time`, `full_address`, `assignee`, `task_title`, `attachments`, `task_email`, `task_hour`, `task_type`, `task_from`, `task_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed`, `color`, `customer_id`, `deleted`)
	SELECT ev.`task_uuid`, ev.`task_name`, ev.`from`, ev.`task_date`, ev.`task_description`, ev.`task_first_name`, ev.`task_last_name`, ev.`task_dateandtime`, ev.`task_end_time`, ev.`full_address`, ev.`assignee`, ev.`task_title`, ev.`attachments`, ev.`task_email`, 
	ev.`task_hour`, ev.`task_type`, ev.`task_from`, ev.`task_priority`, ev.`end_date`, ev.`completed_date`, 
	ev.`callback_date`, ev.`callback_completed`, ev.`color`, ev.`customer_id`, ev.`deleted` 
	FROM `" . $data_source . "`.`" . $data_source . "_task` ev
	
    LEFT OUTER JOIN ikase_" . $data_source . ".cse_task gca
    ON ev.task_uuid = gca.task_uuid
	
	INNER JOIN `ikase_" . $data_source . "`.`cse_case_task` cce
	ON ev.task_uuid = cce.task_uuid
	INNER JOIN " . $data_source . ".badcases
	ON cce.case_uuid = badcases.case_uuid
	WHERE 1
	AND gca.task_uuid IS NULL
	AND ev.deleted = 'N'";
	
	echo $sql . "\r\n\r\n";
	//$stmt = $db->prepare($sql);
	//$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_task_user`
	(`task_user_uuid`, `task_uuid`, `user_uuid`, `type`, `read_status`, `read_date`, `action`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
	SELECT `task_user_uuid`, tu.`task_uuid`, `user_uuid`, `type`, `read_status`, `read_date`, `action`, tu.`last_updated_date`, tu.`last_update_user`, tu.`deleted`, tu.`customer_id`
	FROM `" . $data_source . "`.`" . $data_source . "_task_user` tu
	INNER JOIN `ikase_" . $data_source . "`.`cse_case_task` cce
	ON tu.task_uuid = cce.task_uuid
	INNER JOIN " . $data_source . ".badcases
	ON cce.case_uuid = badcases.case_uuid
	
	INNER JOIN `" . $data_source . "`.`" . $data_source . "_task` tsk
	ON cce.task_uuid = tsk.task_uuid AND tsk.deleted = 'N'
	
	WHERE 1";

	echo $sql . "\r\n\r\n";
	//$stmt = $db->prepare($sql);
	//$stmt->execute();
	
	$db = null;
	
	$success = array("success"=> array("text"=>"done @" . date("H:i:s")));
	echo json_encode($success);
} catch(PDOException $e) {
	echo $sql . "<br />";
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
include("cls_logging.php");	
?>
<script language="javascript">
parent.setFeedback("tasks transfer completed");
</script>
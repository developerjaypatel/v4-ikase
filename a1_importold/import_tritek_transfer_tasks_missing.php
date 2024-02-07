<?php
include("manage_session.php");

include("connection.php");

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_task`
	(`case_task_uuid`, `case_uuid`, `task_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id`)
	SELECT `case_task_uuid`, `case_uuid`, `task_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id` 
	FROM `" . $data_source . "`.`" . $data_source . "_case_task` ct
	WHERE 1 
	AND task_uuid NOT IN (select task_uuid FROM `ikase_" . $data_source . "`.`cse_task`)
	AND customer_id = " . $customer_id;

	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_task` (`task_uuid`, `task_name`, `from`, `task_date`, `task_description`, `task_first_name`, `task_last_name`, `task_dateandtime`, `task_end_time`, `full_address`, `assignee`, `task_title`, `attachments`, `task_email`, `task_hour`, `task_type`, `task_from`, `task_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed`, `color`, `customer_id`, `deleted`)
	SELECT `task_uuid`, `task_name`, `from`, `task_date`, `task_description`, `task_first_name`, `task_last_name`, `task_dateandtime`, `task_end_time`, `full_address`, `assignee`, `task_title`, `attachments`, `task_email`, `task_hour`, `task_type`, `task_from`, `task_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed`, `color`, `customer_id`, `deleted` 
	FROM `" . $data_source . "`.`" . $data_source . "_task` 
	WHERE 1 
	AND task_uuid NOT IN (select task_uuid FROM `ikase_" . $data_source . "`.`cse_task`)
	AND customer_id = " . $customer_id;
	
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_task_user`
	(`task_user_uuid`, `task_uuid`, `user_uuid`, `type`, `read_status`, `read_date`, `action`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
	SELECT `task_user_uuid`, `task_uuid`, `user_uuid`, `type`, `read_status`, `read_date`, `action`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`
	FROM `" . $data_source . "`.`" . $data_source . "_task_user` 
	WHERE 1 
	AND task_uuid NOT IN (select task_uuid FROM `ikase_" . $data_source . "`.`cse_task`)
	AND customer_id = " . $customer_id;

	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$db = null;
	
	$success = array("success"=> array("text"=>"done @" . date("H:i:s")));
	echo json_encode($success);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
include("cls_logging.php");	
?>
<script language="javascript">
parent.setFeedback("tasks transfer completed");
</script>
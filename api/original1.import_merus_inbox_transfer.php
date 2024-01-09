<?php
include("manage_session.php");

include("connection.php");

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_message`
	(`case_message_uuid`,
	`case_uuid`,
	`message_uuid`,
	`attribute`,
	`last_updated_date`,
	`last_update_user`,
	`deleted`,
	`customer_id`)
	
	SELECT `case_message_uuid`,
	`case_uuid`,
	`message_uuid`,
	`attribute`,
	`last_updated_date`,
	`last_update_user`,
	`deleted`,
	`customer_id` FROM `" . $data_source . "`.`" . $data_source . "_case_message`;
	
	INSERT INTO `ikase_" . $data_source . "`.`cse_message`
	(`message_id`,
	`message_uuid`,
	`message_type`,
	`dateandtime`,
	`from`,
	`message_to`,
	`message_cc`,
	`message_bcc`,
	`message`,
	`subject`,
	`snippet`,
	`attachments`,
	`priority`,
	`callback_date`,
	`customer_id`,
	`status`,
	`deleted`)
	
	SELECT `message_id`,
	`message_uuid`,
	`message_type`,
	`dateandtime`,
	`from`,
	`message_to`,
	`message_cc`,
	`message_bcc`,
	`message`,
	`subject`,
	`snippet`,
	`attachments`,
	`priority`,
	`callback_date`,
	`customer_id`,
	`status`,
	`deleted`
	FROM  `" . $data_source . "`.`" . $data_source . "_message`;
	
	INSERT INTO `ikase_" . $data_source . "`.`cse_message_user`
	(`message_user_id`,
	`message_user_uuid`,
	`message_uuid`,
	`user_uuid`,
	`message_id`,
	`user_id`,
	`type`,
	`thread_uuid`,
	`read_status`,
	`read_date`,
	`action`,
	`last_updated_date`,
	`last_update_user`,
	`deleted`,
	`customer_id`,
	`user_type`)
	SELECT `message_user_id`,
	`message_user_uuid`,
	`message_uuid`,
	`user_uuid`,
	`message_id`,
	`user_id`,
	`type`,
	`thread_uuid`,
	`read_status`,
	`read_date`,
	`action`,
	`last_updated_date`,
	`last_update_user`,
	`deleted`,
	`customer_id`,
	`user_type`
	FROM `" . $data_source . "`.`" . $data_source . "_message_user`;
	
	INSERT INTO `ikase_" . $data_source . "`.`cse_thread`
	(`thread_id`,
	`thread_uuid`,
	`dateandtime`,
	`from`,
	`subject`,
	`customer_id`,
	`deleted`)
	SELECT `thread_id`,
	`thread_uuid`,
	`dateandtime`,
	`from`,
	`subject`,
	`customer_id`,
	`deleted`
	FROM `" . $data_source . "`.`" . $data_source . "_thread`;
	
	INSERT INTO `ikase_" . $data_source . "`.`cse_thread_message`
	(`thread_message_id`,
	`thread_message_uuid`,
	`thread_uuid`,
	`message_uuid`,
	`message_id`,
	`attribute`,
	`last_updated_date`,
	`last_update_user`,
	`deleted`,
	`customer_id`)
	SELECT `thread_message_id`,
	`thread_message_uuid`,
	`thread_uuid`,
	`message_uuid`,
	`message_id`,
	`attribute`,
	`last_updated_date`,
	`last_update_user`,
	`deleted`,
	`customer_id`
	FROM `" . $data_source . "`.`" . $data_source . "_thread_message`;
	
	";
$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$db = null;
	
	$success = array("success"=> array("text"=>"transfer notes done @" . date("H:i:s")));
	echo json_encode($success);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}	
?>
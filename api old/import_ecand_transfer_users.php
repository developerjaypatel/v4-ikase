<?php
include("manage_session.php");

include("connection.php");
$customer_id = passed_var("customer_id", "get");
if (!is_numeric($customer_id)) {
	die();
}

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	$sql = "TRUNCATE `" . $data_source . "`.`" . $data_source . "_user`
	";
	//die($sql);
	
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_user`
	(`user_uuid`,`customer_id`, `user_type`,`user_name`,`user_logon`,`nickname`,`pwd`,`level`,`job`,`status`)
	SELECT original_id, 1136 customer_id, 2 user_type, `name` user_name, 
	LOWER(nickname) user_logon, SUBSTRING(REPLACE(nickname, 'USER', 'USR'), 1, 4) nickname, '', 'User', `type`, `status`  
	FROM " . $data_source . ".interim_user;
	";
	
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase`.`cse_user` (`user_uuid`, `customer_id`, `cis_id`, `cis_uid`, `user_type`, `user_name`, `user_logon`, `user_first_name`, `user_last_name`, `user_email`, `nickname`, `pwd`, `level`, `job`, `status`, `personal_calendar`, `day_start`, `day_end`, `days_of_week`, `dow_times`, `sess_id`, `dateandtime`, `ip_address`, `deleted`)
	SELECT `user_uuid`, `customer_id`, `cis_id`, `cis_uid`, `user_type`, `user_name`, `user_logon`, `user_first_name`, `user_last_name`, `user_email`, `nickname`, `pwd`, `level`, `job`, `status`, `personal_calendar`, `day_start`, `day_end`, `days_of_week`, `dow_times`, `sess_id`, `dateandtime`, `ip_address`, `deleted` 
	FROM `" . $data_source . "`.`" . $data_source . "_user` 
	WHERE 1 AND level != 'masteradmin' AND customer_id = " . $customer_id;

	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$db = null;
	
	$success = array("success"=> array("text"=>"done"));
	echo json_encode($success);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}	

include("cls_logging.php");
?>
<script language="javascript">
parent.setFeedback("users transfer completed");
</script>
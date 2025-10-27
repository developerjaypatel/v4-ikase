<?php
include("manage_session.php");
set_time_limit(3000);

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;

include("connection.php");

try {
	$db = getConnection();
	
	include("customer_lookup.php");
	
	$sql_truncate = "DELETE FROM `cse_user` 
	WHERE `level` != 'masteradmin'
	AND customer_id = " . $customer_id;
	
	echo $sql_truncate . "\r\n\r\n";
	$stmt = $db->prepare($sql_truncate);
	$stmt->execute();
	
	$sql_truncate = "TRUNCATE  `cse_job` ";
	
	echo $sql_truncate . "\r\n\r\n";
	$stmt = $db->prepare($sql_truncate);
	$stmt->execute();
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$row_start_time = $time;
	
	$sql =  "INSERT INTO `cse_job` (`job_uuid`, `job`, `blurb`, `color`)";
	$sql .= " SELECT DISTINCT `TITLE`, `TITLE`, LOWER(REPLACE(`TITLE`, ' ', '_')) blurb, '' color
	FROM `cse_staff`
	WHERE `TITLE` IS NOT NULL";
	
	echo $sql . "\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "
	INSERT INTO `cse_user` (
	`user_uuid`,
	`customer_id`,
	`user_name`,
	`user_first_name`,
	`user_last_name`,
	`user_logon`,
	`nickname`,
	`pwd`, `job`)
	SELECT CONCAT(`INITIALS`, '_', '" . $customer_id . "') user_uuid,
	'" . $customer_id . "' customer_id, 
	CONCAT(`FNAME`, ' ',`LNAME`) `user_name`, 
	`FNAME` `user_first_name`, `LNAME` `user_last_name`,
	`USERNAME` user_logon, 
	`INITIALS` `nickname`, 
	'' pwd, IFNULL(`TITLE`, '') `job`
	FROM `cse_staff`
	WHERE `INITIALS` IS NOT NULL";
	
	echo $sql . "\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "INSERT INTO `cse_user_job` (
	`user_job_uuid`, `user_uuid`, `job_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
	SELECT user_uuid, user_uuid, job, 'main', '" . date("Y-m-d H:i:s") . "', 'system', '" . $customer_id . "'
	FROM `cse_user`";
	
	echo $sql . "\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $row_start_time), 4);
	echo "Time spent:" . $total_time . "\r\n\r\n";

	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	$success = array("success"=> array("text"=>"done", "duration"=>$total_time));
	echo json_encode($success);
	
	$db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
include("cls_logging.php");
?>
<script language="javascript">
parent.setFeedback("users import completed");
</script>
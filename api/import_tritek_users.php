<?php
require_once('../shared/legacy_session.php');
set_time_limit(3000);

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;

include("connection.php");

try {
	$db = getConnection();
	
	include("customer_lookup.php");
	
	$sql_access = "UPDATE `" . $data_source . "`.`" . $data_source . "_notes` 
	SET `type` = 'access'
	WHERE `note` LIKE '%Folder accessed%' OR `note` LIKE '%Ltr%'";
	$stmt = $db->prepare($sql_access);
	echo $sql . "<br /><br />
\r\n\r\n";
	$stmt->execute();
	
	$sql_truncate = "DELETE FROM `" . $data_source . "`.`" . $data_source . "_user` 
	WHERE `level` != 'masteradmin'
	AND customer_id = " . $customer_id;
	$stmt = DB::run($sql_truncate);
	//die($sql_truncate);
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$row_start_time = $time;
	
	//`locator` not on older dbs
	$sql = "
	INSERT INTO `" . $data_source . "`.`" . $data_source . "_user` (
	`user_uuid`,
	`customer_id`,
	`user_name`,
	`user_logon`,
	`nickname`,
	`pwd`,
	`user_type`,
	`level`)
	SELECT CONCAT(LOWER(REPLACE(workname, ' ', '_')),
            @curRow:=@curRow + 1) user_uuid, 
	'" . $customer_id . "' customer_id, 
	workname `user_name`, 
	LOWER(REPLACE(workname, ' ', '_') ) user_logon, 
	workcode `nickname`, 
	CONCAT(LOWER(REPLACE(workname, ' ', '_') ), @curRow := @curRow + 1) pwd,
	'2', 'User'
	FROM `" . $data_source . "`.workcode
	JOIN    (SELECT @curRow := 0) r";
	//SUBSTRING(CONCAT('" . $customer_id . "', LOWER(REPLACE(workname, ' ', '_') )), 1, 15)
	//IFNULL(`timeslipid`, SUBSTRING(CONCAT('" . $customer_id . "', LOWER(REPLACE(workname, ' ', '_') )), 1, 15)) user_uuid, 
	echo $sql . "\r\n\r\n";
	//die();
	$stmt = DB::run($sql);
	
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
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
include("cls_logging.php");
?>
<script language="javascript">
parent.setFeedback("users import completed");
</script>

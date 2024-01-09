<?php
include("manage_session.php");
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
	
	$sql_truncate = "TRUNCATE `" . $data_source . "`.`" . $data_source . "_setting`";
	//die($sql_truncate);
	$stmt = $db->prepare($sql_truncate);
	$stmt->execute();
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$row_start_time = $time;
	
		
	$sql = "
	INSERT INTO `" . $data_source . "`.`" . $data_source . "_setting`
	(`setting_uuid`, `customer_id`, `category`, `setting`, `setting_value`, `setting_type`, `default_value`)
	SELECT `setting_uuid`, '" . $customer_id . "', `category`, `setting`, `setting_value`, `setting_type`, `default_value`
	FROM `ikase`.`cse_setting`
	WHERE (category = 'calendar_type' OR category = 'delay')
	AND customer_id = 1033 ;";
	
	echo $sql . "<br><br>\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	
	$sql = "
	INSERT INTO `" . $data_source . "`.`" . $data_source . "_setting`
	(`setting_uuid`, `customer_id`, `category`, `setting`, `setting_value`, `setting_type`, `default_value`)
	VALUES('appearances',  '" . $customer_id . "', 'calendar_type', 'Appearances', 'Appr', '', '#FFFF66');";
	
	echo $sql . "<br><br>\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "
	INSERT INTO `" . $data_source . "`.`" . $data_source . "_setting`
	(`setting_uuid`, `customer_id`, `category`, `setting`, `setting_value`, `setting_type`, `default_value`)
	VALUES('appearances',  '" . $customer_id . "', 'calendar_type', 'In Office Appearances', 'InOff', '', '#66FF66');";
	
	
	echo $sql . "<br><br>\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "
	INSERT INTO `" . $data_source . "`.`" . $data_source . "_setting`
	(`setting_uuid`, `customer_id`, `category`, `setting`, `setting_value`, `setting_type`, `default_value`)
	VALUES('appearances',  '" . $customer_id . "', 'calendar_type', 'Employee Attendance', 'Empl', '', '#FF9966');";
	
	echo $sql . "<br><br>\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "
	INSERT INTO `" . $data_source . "`.`" . $data_source . "_setting`
	(`setting_uuid`, `customer_id`, `category`, `setting`, `setting_value`, `setting_type`, `default_value`)
	VALUES('appearances',  '" . $customer_id . "', 'calendar_type', 'Intake', 'Intk', '', '#EEEE66');";
	
	echo $sql . "<br><br>\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "
	INSERT INTO `" . $data_source . "`.`" . $data_source . "_setting`
	(`setting_uuid`, `customer_id`, `category`, `setting`, `setting_value`, `setting_type`, `default_value`)
	VALUES('appearances',  '" . $customer_id . "', 'calendar_type', 'Partner Calendar', 'Partner', '', '#99FF66');";
	
	echo $sql . "<br><br>\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $row_start_time), 4);
	echo "Time spent:" . $total_time . "<br />
<br />
";
	
	$success = array("success"=> array("text"=>"done", "duration"=>$total_time));
	echo json_encode($success);
	
	$db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
?>
<script language="javascript">
parent.setFeedback("calendar types import completed");
</script>
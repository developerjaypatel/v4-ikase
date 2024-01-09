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

try {
	$db = getConnection();
	
	include("customer_lookup.php");
	
	$sql_truncate = "TRUNCATE `ikase_" . $data_source . "`.`cse_setting`";
	//die($sql_truncate);
	$stmt = DB::run($sql_truncate);
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$row_start_time = $time;
	echo "Truncated cse_setting table <br>";
	
		
	$sql = "
	INSERT INTO `ikase_" . $data_source . "`.`cse_setting`
	(`setting_uuid`, `customer_id`, `category`, `setting`, `setting_value`, `setting_type`, `default_value`)
	SELECT `setting_uuid`, '" . $customer_id . "', `category`, `setting`, `setting_value`, `setting_type`, `default_value`
	FROM `ikase`.`cse_setting`
	WHERE (category = 'calendar_type' OR category = 'delay')
	AND customer_id = 1033 ;";
	
	echo $sql . "<br><br>\r\n\r\n";
	$stmt = DB::run($sql);
	
	
	$sql = "
	INSERT INTO `ikase_" . $data_source . "`.`cse_setting`
	(`setting_uuid`, `customer_id`, `category`, `setting`, `setting_value`, `setting_type`, `default_value`)
	VALUES('appearances',  '" . $customer_id . "', 'calendar_type', 'Appearances', 'Appr', '', '#FFFF66');";
	
	echo $sql . "<br><br>\r\n\r\n";
	$stmt = DB::run($sql);
	
	$sql = "
	INSERT INTO `ikase_" . $data_source . "`.`cse_setting`
	(`setting_uuid`, `customer_id`, `category`, `setting`, `setting_value`, `setting_type`, `default_value`)
	VALUES('appearances',  '" . $customer_id . "', 'calendar_type', 'In Office Appearances', 'InOff', '', '#66FF66');";
	
	
	echo $sql . "<br><br>\r\n\r\n";
	$stmt = DB::run($sql);
	
	$sql = "
	INSERT INTO `ikase_" . $data_source . "`.`cse_setting`
	(`setting_uuid`, `customer_id`, `category`, `setting`, `setting_value`, `setting_type`, `default_value`)
	VALUES('appearances',  '" . $customer_id . "', 'calendar_type', 'Employee Attendance', 'Empl', '', '#FF9966');";
	
	echo $sql . "<br><br>\r\n\r\n";
	$stmt = DB::run($sql);
	
	$sql = "
	INSERT INTO `ikase_" . $data_source . "`.`cse_setting`
	(`setting_uuid`, `customer_id`, `category`, `setting`, `setting_value`, `setting_type`, `default_value`)
	VALUES('appearances',  '" . $customer_id . "', 'calendar_type', 'Intake', 'Intk', '', '#EEEE66');";
	
	echo $sql . "<br><br>\r\n\r\n";
	$stmt = DB::run($sql);
	
	$sql = "
	INSERT INTO `ikase_" . $data_source . "`.`cse_setting`
	(`setting_uuid`, `customer_id`, `category`, `setting`, `setting_value`, `setting_type`, `default_value`)
	VALUES('appearances',  '" . $customer_id . "', 'calendar_type', 'Partner Calendar', 'Partner', '', '#99FF66');";
	
	echo $sql . "<br><br>\r\n\r\n";
	$stmt = DB::run($sql);
	
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
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
?>
<script language="javascript">
parent.setFeedback("calendar types import completed");
</script>

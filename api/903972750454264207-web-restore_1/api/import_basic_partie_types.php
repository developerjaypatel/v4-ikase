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
	
	$sql_truncate = "TRUNCATE `ikase_" . $data_source . "`.`cse_partie_type`";
	echo $sql_truncate . "\r\n";
	$stmt = $db->prepare($sql_truncate);
	$stmt->execute();
	
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$row_start_time = $time;
	
		
	$sql = "
	INSERT INTO `ikase_" . $data_source . "`.`cse_partie_type`
	(`partie_type_id`, `partie_type`, `employee_title`, `blurb`, `color`, `show_employee`, `adhoc_fields`, 
	`sort_order`)
	SELECT `partie_type_id`, `partie_type`, `employee_title`, `blurb`, `color`, `show_employee`, 
	`adhoc_fields`, `sort_order`
	FROM `ikase`.`cse_partie_type`;";
	
	echo $sql . "\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $row_start_time), 4);
	echo "Time spent:" . $total_time . "\r\n\r\n";
	
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
parent.setFeedback("partie types import completed");
</script>
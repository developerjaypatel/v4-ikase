<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include("manage_session.php");
set_time_limit(3000);

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;
$last_updated_date = date("Y-m-d H:i:s");

include("connection.php");
?>
<html>
<body style="font-size:0.7em">
<?php
try {
	$db = getConnection();
	
	include("customer_lookup.php");
	
	$sql_truncate = "TRUNCATE `cse_injury`; 
	TRUNCATE `cse_case_injury`; 
	TRUNCATE `cse_injury`; 
	TRUNCATE `cse_injury_number`; 
	TRUNCATE `cse_injury_injury_number`; 
	TRUNCATE `cse_case`; 
	TRUNCATE `cse_corporation`; 
	TRUNCATE `cse_case_corporation`; 
	TRUNCATE `cse_corporation_adhoc`; 
	TRUNCATE `cse_person`; 
	TRUNCATE `cse_case_person`; 
	TRUNCATE `cse_notes`; 
	TRUNCATE `cse_case_notes`;
	TRUNCATE `cse_exam`; 
	TRUNCATE `cse_corporation_exam`;";
	
	//die($sql_truncate);
	$stmt = $db->prepare($sql_truncate);
	$stmt->execute();	
	
	$sql_truncate = "TRUNCATE `cse_event`; 
	TRUNCATE `cse_case_event`; ";
	//echo $sql_truncate . "\r\n\r\n";
	$stmt = $db->prepare($sql_truncate);
	$stmt->execute();
	
	$sql_truncate = "TRUNCATE `cse_task`; 
	TRUNCATE `cse_case_task`; ";
	//echo $sql_truncate . "\r\n\r\n";
	$stmt = $db->prepare($sql_truncate);
	$stmt->execute();
	
	echo "truncated";
	$db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
include("cls_logging.php");
?>
</body>
</html>
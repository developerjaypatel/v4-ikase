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
	
	$sql_truncate = "TRUNCATE `ikase_" . $data_source . "`.`cse_injury`; 
	TRUNCATE `ikase_" . $data_source . "`.`cse_case_injury`; 
	TRUNCATE `ikase_" . $data_source . "`.`cse_injury`; 
	TRUNCATE `ikase_" . $data_source . "`.`cse_injury_number`; 
	TRUNCATE `ikase_" . $data_source . "`.`cse_injury_injury_number`; 
	TRUNCATE `ikase_" . $data_source . "`.`cse_case`; 
	TRUNCATE `ikase_" . $data_source . "`.`cse_corporation`; 
	TRUNCATE `ikase_" . $data_source . "`.`cse_case_corporation`; 
	TRUNCATE `ikase_" . $data_source . "`.`cse_corporation_adhoc`; 
	TRUNCATE `ikase_" . $data_source . "`.`cse_person`; 
	TRUNCATE `ikase_" . $data_source . "`.`cse_case_person`; 
	TRUNCATE `ikase_" . $data_source . "`.`cse_notes`; 
	TRUNCATE `ikase_" . $data_source . "`.`cse_case_notes`;
	TRUNCATE `ikase_" . $data_source . "`.`cse_exam`; 
	TRUNCATE `ikase_" . $data_source . "`.`cse_corporation_exam`;
	TRUNCATE `ikase_" . $data_source . "`.`cse_case_track`;
	TRUNCATE `ikase_" . $data_source . "`.`cse_user`;
	TRUNCATE `ikase_" . $data_source . "`.`cse_user_job`;
	TRUNCATE `ikase_" . $data_source . "`.`cse_corporation_track`;
	TRUNCATE `ikase_" . $data_source . "`.`cse_event_track`;
	TRUNCATE `ikase_" . $data_source . "`.`cse_injury_bodyparts`;
	TRUNCATE `ikase_" . $data_source . "`.`cse_injury_number_track`;
	TRUNCATE `ikase_" . $data_source . "`.`cse_injury_track`;
	TRUNCATE `ikase_" . $data_source . "`.`cse_person_track`; 
	TRUNCATE `ikase_" . $data_source . "`.`cse_bodyparts`;
	TRUNCATE `ikase_" . $data_source . "`.`cse_task_user`; ";
	
	//die($sql_truncate);
	$stmt = $db->prepare($sql_truncate);
	$stmt->execute();	
	
	$sql_truncate = "TRUNCATE `ikase_" . $data_source . "`.`cse_event`; 
	TRUNCATE `ikase_" . $data_source . "`.`cse_case_event`; ";
	//echo $sql_truncate . "\r\n\r\n";
	$stmt = $db->prepare($sql_truncate);
	$stmt->execute();
	
	$sql_truncate = "TRUNCATE `ikase_" . $data_source . "`.`cse_task`; 
	TRUNCATE `ikase_" . $data_source . "`.`cse_case_task`; ";
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
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
	
	$sql_truncate = "TRUNCATE `" . $data_source . "`.`" . $data_source . "_injury`; 
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_case_injury`; 
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_injury`; 
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_injury_number`; 
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_injury_injury_number`; 
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_case`; 
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_corporation`; 
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_case_corporation`; 
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_corporation_adhoc`; 
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_person`; 
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_case_person`; 
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_notes`; 
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_case_notes`;
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_exam`; 
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_corporation_exam`;";
	
	//die($sql_truncate);
	$stmt = $db->prepare($sql_truncate);
	$stmt->execute();	
	
	$sql_truncate = "TRUNCATE `" . $data_source . "`.`" . $data_source . "_event`; 
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_case_event`; ";
	//echo $sql_truncate . "\r\n\r\n";
	$stmt = $db->prepare($sql_truncate);
	$stmt->execute();
	
	$sql_truncate = "TRUNCATE `" . $data_source . "`.`" . $data_source . "_task`; 
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_case_task`; ";
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
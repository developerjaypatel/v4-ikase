<?php
include("manage_session.php");
set_time_limit(3000);

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;

include("connection.php");
$customer_id = 1121;


try {
	$db = getConnection();
	
	include("customer_lookup.php");
	$sql = "SELECT DISTINCT gt.case_uuid, gt.task_uuid
	FROM " . $data_source . "." . $data_source . "_case_task gt
	LEFT OUTER JOIN ikase_" . $data_source . ".cse_case_task ct
	ON gt.task_uuid = ct.task_uuid AND gt.case_uuid = ct.case_uuid
	WHERE 1
	AND ct.case_task_uuid IS NULL
	LIMIT 0, 5000";
	//echo $sql . "\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$cases = $stmt->fetchAll(PDO::FETCH_OBJ);
	
	foreach($cases as $case) {
		$sql = "UPDATE ikase_" . $data_source . ".cse_case_task
		SET case_uuid = '" . $case->case_uuid . "'
		WHERE task_uuid = '" . $case->task_uuid . "';
		";
		//echo $sql . "\r\n";
		$stmt = $db->prepare($sql);
		$stmt->execute();
		//die();
	}
	
	$db = null;
	die("done " . date("H:i:s"));
} catch(PDOException $e) {
	echo $sql . "<br />";
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
?>
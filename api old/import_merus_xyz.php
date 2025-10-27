<?php
set_time_limit(12000);
ini_set('memory_limit','256M');

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', '1');

include("connection.php");

$data_source = passed_var("data_source", "get");
$customer_id = passed_var("customer_id", "get");

if (!is_numeric($customer_id)) {
	die("no id");
}
$dir = "F:\\" . $data_source  . "\\";

try{
	$sql = "SELECT gcase.* 
	FROM `" . $data_source . "`.`cases` gcase
	WHERE processed = 'N'
	LIMIT 0, 1
	";
	//echo $sql . "\r\n<br>";
	
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$kase = $stmt->fetchObject();
	$stmt->closeCursor(); $stmt = null; $db = null;
	
	echo "Requesting: " . $kase->case_id . "\r\n\r\n";
	
	$url = "http://kustomweb.xyz/cloud/transfer_request.php?customer_id=" . $customer_id . "&case_id=" . $kase->case_id . "&data_source=" . $data_source . "";
	$contents = file_get_contents($url, true);
	
	//die($contents);
	
	echo "\r\ndone!\r\n";
	
	$sql = "UPDATE `" . $data_source . "`.`cases` gcase
	SET processed = 'Y'
	WHERE case_id = '" . $kase->case_id . "'";
	
	echo $sql . "\r\n";
	
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$db = null; $stmt = null;
	
	
	//all cases
	$sql = "SELECT COUNT(case_id) case_count
	FROM `" . $data_source . "`.`cases` gcase";
	//echo $sql . "\r\n<br>";
	//die();
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$cases = $stmt->fetchObject();
	
	$case_count = $cases->case_count;
	$db = null; $stmt = null;
	
	//completeds
	$sql = "SELECT COUNT(case_id) case_count
	FROM `" . $data_source . "`.`cases` ggc
	WHERE processed = 'Y'";
	echo $sql . "\r\n<br>";
	//die();
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$cases = $stmt->fetchObject();
	
	$completed_count = $cases->case_count;
	$db = null; $stmt = null;
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	$success = array("success"=> array("text"=>"done", "duration"=>$total_time, "completed_count"=>$completed_count, "case_count"=>$case_count));
	
	if ($completed_count < $case_count) {
		
		echo "<script language='javascript'>parent.runMain(" . $completed_count . "," . $case_count . ")</script>";

	} else {
		die("done");
	}
	
} catch (PDOException $e) {
	echo $e->getMessage();
	die("
	ERROR:
	$sql");
	$error = array("error"=> array("text"=>$e->getMessage(), "sql"=>$sql, "error"=>$arrErrorCatch));
	echo json_encode($error);
}
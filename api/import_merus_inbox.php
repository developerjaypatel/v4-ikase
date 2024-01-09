<?php
set_time_limit(200*MIN);
ini_set('memory_limit','256M');

include("connection.php");

$data_source = passed_var("data_source", "get");
$customer_id = passed_var("customer_id", "get");
$blnInitial = (isset($_GET["init"]));

if (!is_numeric($customer_id)) {
	die("no id");
}
$dir = "F:\\" . $data_source  . DC;

try{
	/*
	if ($blnInitial) {
		$sql = "UPDATE ramirez.cases
SET processed = 'N'";
		$stmt = DB::run($sql);
		
	}
	*/
	$sql = "SELECT gcase.* 
	FROM `" . $data_source . "`.`cases` gcase
	WHERE processed = 'N'
	LIMIT 0, 1
	";
	
	$stmt = DB::run($sql);
	$kase = $stmt->fetchObject();
	//die(print_r($kase));
	echo "Requesting: " . $kase->case_id . "\r\n\r\n";
	
	$url = "https://kustomweb.xyz/cloud/transfer_inbox.php?customer_id=" . $customer_id . "&case_id=" . $kase->case_id . "&data_source=" . $data_source . "";
	$contents = get_curl($url);
	
	// die($contents);
	
	echo "\r\ndone!\r\n";
	
	$sql = "UPDATE `" . $data_source . "`.`cases` gcase
	SET processed = 'Y'
	WHERE case_id = '" . $kase->case_id . "'";
	
	echo $sql . "\r\n";
	
	$stmt = DB::run($sql);
	
	
	//all cases
	$sql = "SELECT COUNT(case_id) case_count
	FROM `" . $data_source . "`.`cases` gcase";
	//echo $sql . "\r\n<br>";
	//die();
	$stmt = DB::run($sql);
	$cases = $stmt->fetchObject();
	
	$case_count = $cases->case_count;
	
	//completeds
	$sql = "SELECT COUNT(case_id) case_count
	FROM `" . $data_source . "`.`cases` ggc
	WHERE processed = 'Y'";
	echo $sql . "\r\n<br>";
	//die();
	$stmt = DB::run($sql);
	$cases = $stmt->fetchObject();
	
	$completed_count = $cases->case_count;
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
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

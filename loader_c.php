<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;

include("api/manage_session.php");
session_write_close();

include("api/connection.php");

$sql = "SELECT * FROM ikase.cse_cost_type
WHERE cost_type_id > 5";

$db = getConnection(); $stmt = $db->prepare($sql);
$stmt->execute();
$types = $stmt->fetchAll(PDO::FETCH_OBJ); 
$stmt->closeCursor(); $stmt = null; $db = null;

//die("len:" . strlen("System Idle Process              0"));
exec('tasklist.exe', $outputLines);
$max = 0;
$max_item = "";
//print_r($outputLines);
foreach($outputLines as $lcounter=>$line) {
	if ($lcounter < 4) {
		continue;
	}
	
	//echo $line . "\r\n";
	$memory = str_replace(" K", "", trim(substr($line, 68)));
	$memory = str_replace(",", "" , $memory);
	
	if ($memory > $max) {
		$max = $memory;
		$max_item = explode(" ", $line);
		$max_item = $max_item[0];
	}
}

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$finish_time = $time;
$total_time = round(($finish_time - $header_start_time), 6);

die(json_encode(array("count"=>count($outputLines), "max_item"=>$max_item, "max"=>$max, "total_time"=>$total_time)));
?>
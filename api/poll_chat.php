<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');	
session_write_close();

function checkChat($timestamp, $chat_id, $customer_id) {
	//$timestamp = passed_var("timestamp", "post");
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$header_start_time = $time;
	
	$chat_dir = $_SERVER['DOCUMENT_ROOT'] . '\\chats\\' . $customer_id;
	//die($_SERVER['DOCUMENT_ROOT'] . $chat_dir);
	if (!is_dir($chat_dir)) {
		mkdir($chat_dir, 0755, true);
	}
	$filename = $chat_dir . '\\chat_' . $chat_id . '.txt';
	//die($filename);
	if (!file_exists($filename)) {
		$new_content = "";
		if (!$handle = fopen($filename, 'w')) {
			$error = "Cannot open file ($filename)";
			echo json_encode($error);
			exit;
		}
		if (fwrite($handle, $new_content) === FALSE) {
		   $error = "Cannot write to file ($filename)";
		   echo json_encode($error);
		   exit;
		}
	}
	if ($timestamp=="") {
		$lastmodif = 0;
	} else {
		$lastmodif = $timestamp;
	}
	$currentmodif = filemtime($filename);
	
	//die(json_encode(array("curr"=>$currentmodif, "timestamp"=>$timestamp)));
	
	while($currentmodif <= $lastmodif) {
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $header_start_time), 4);
		
		//echo "time:" . $total_time . "<br>";
		//i don't want to have this run forever
		if ($total_time > 15) {
			$response = array();
			$response['messages'] = "";
			$response['timestamp'] = $lastmodif;
			
			echo json_encode($response);
			die();
		}
		usleep(2000);

		clearstatcache();
		$currentmodif = filemtime($filename);
	}
	
	$response = array();
	
	//die($currentmodif . " <= " . $lastmodif);
	if ($currentmodif <= $lastmodif) {
		$response['messages'] = "";
		$response['timestamp'] = $lastmodif;
		//$response['diff'] = 0;
	} else {
		$contents = file_get_contents($filename);
		if ($contents=="") {
			$response['messages'] = "";
			$response['timestamp'] = $lastmodif;
			//$response['diff'] = 0;
		} else {
			$response['messages'] = $contents;
			$response['timestamp'] = $currentmodif;
			$response['diff'] = $currentmodif - $lastmodif;
		}
	}
	echo json_encode($response);
}
$timestamp = $_GET["timestamp"];
$chat_id = $_GET["chat_id"];
$customer_id = $_GET["customer_id"];
	
checkChat($timestamp, $chat_id, $customer_id);

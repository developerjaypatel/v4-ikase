<?php
error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
ini_set('display_errors', '1');

include("manage_session.php");
session_write_close();

if ($_SERVER['REMOTE_ADDR']=='47.153.51.181') {
	//echo "session_name:" . session_name();
	//die(print_r($_COOKIE));
		
	$today  = mktime(0, 0, 0, date("m")  , date("d"), date("Y"));
	//check on session files
	$dir = 'C:\\inetpub\\wwwroot\\ikase.org\\sessions\\';
	$session_files = scandir($dir);
	
	foreach($session_files as $filename) {
		if ($filename=="." || $filename=="..") {
			continue;
		}
		if (strpos($filename, "data")===false) {
			continue;
		}
		$file_time = filemtime($dir . $filename);
		if ($file_time < $today) {
			//echo $filename . " was modified on " . date("m/d/Y", $file_time) . "  and must be deleted<br />";
			unlink($dir . $filename);
		}
	}
}

function pollChat($timestamp, $user_id, $customer_id) {
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
	$filename = $chat_dir . '\\changed_' . $user_id . '.txt';
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
	/*
	$total_time = 0;
	$response = array();
	$response['chat_id'] = "";
	$response['from'] = "";
	$response['from_id'] = "";
	$response['total_time'] = $total_time;
	$response['timestamp'] = $lastmodif;
	
	echo json_encode($response);
	die();
	*/
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
		if ($total_time > 20) {
			$response = array();
			$response['chat_id'] = "";
			$response['from'] = "";
			$response['from_id'] = "";
			$response['total_time'] = $total_time;
			$response['timestamp'] = $lastmodif;
			
			echo json_encode($response);
			die();
		}
		usleep(5000);

		clearstatcache();
		$currentmodif = filemtime($filename);
	}
	
	$response = array();
	
	//die($currentmodif . " <= " . $lastmodif);
	if ($currentmodif <= $lastmodif) {
		$response['chat_id'] = "";
		$response['from'] = "";
		$response['from_id'] = "";
		$response['timestamp'] = $lastmodif;
		$response['diff'] = 0;
	} else {
		$contents = file_get_contents($filename);
		if ($contents=="") {
			$response['chat_id'] = "";
			$response['from'] = "";
			$response['from_id'] = "";
			$response['timestamp'] = $lastmodif;
			$response['diff'] = 0;
		} else {
			$arrContents = explode("\r\n", $contents);
			//die(print_r($arrContents));
			if (count($arrContents) > 1) {
				$contents = $arrContents[count($arrContents) - 2];
				$arrValues = explode("|", $contents);
				$chat_id = $arrValues[0];
				$from_id = $arrValues[1];
				$from = $arrValues[2];
			} else {
				$chat_id = "";
				$from = "";
			}
			$response['chat_id'] = $chat_id;
			$response['from'] = $from;
			$response['from_id'] = $from_id;
			$response['timestamp'] = $currentmodif;
			$response['diff'] = $currentmodif - $lastmodif;
		}
	}
	$response["user"] = $_SESSION["user"];
	$response["CREATED"] = $_SESSION["CREATED"];
	$response["LAST_ACTIVITY"] = $_SESSION["LAST_ACTIVITY"];
	$response["ACTIVITY_INTERVAL"] = time() - $_SESSION['CREATED'];
	$response["ACTIVITY_MINUTES"] = $response["ACTIVITY_INTERVAL"] / 60;
	//$response["session"] = $_SESSION;
	echo json_encode($response);
}

include("connection.php");

$timestamp = passed_var("timestamp", "get");
$user_id = passed_var("user_id", "get");
$customer_id = passed_var("customer_id", "get");

pollChat($timestamp, $user_id, $customer_id);

$dateandtime = date("Y-m-d H:i:s") ;
$sql = "UPDATE ikase.cse_user
SET dateandtime = :dateandtime
WHERE user_id = :user_id
AND customer_id = :customer_id";

try {
	$db = getConnection();
	$stmt = $db->prepare($sql);  
	$stmt->bindParam("dateandtime", $dateandtime);
	$stmt->bindParam("user_id", $user_id);
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->execute();
	
	$stmt = null; $db = null;
} catch(PDOException $e) {	
	//echo '{"error":{"text":'. $e->getMessage() .'}}'; 
}
?>
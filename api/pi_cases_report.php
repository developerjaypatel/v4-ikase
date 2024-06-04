<?php
include("connection.php");

$sql = "SELECT usr.user_name, usr.user_uuid, usr.nickname, 
cus.customer_id, cus.data_source, cus_name, report, api 
FROM ikase.cse_customer_reports ccr
INNER JOIN ikase.cse_customer cus
ON ccr.customer_id = cus.customer_id
INNER JOIN ikase.cse_user usr
ON ccr.user_id = usr.user_id
WHERE 1
AND ccr.deleted = 'N'";

try {
	$reports = DB::select($sql);
	
	//die(print_r($reports));
	
	foreach($reports as $sindex=>$report) {
		$source = $report->data_source;
		$customer_id = $report->customer_id;
		
		//$url = "https://". $_SERVER['SERVER_NAME'] ."/api/personalinjuryweekly";
		$url = "https://". $_SERVER['SERVER_NAME'] ."/api/" . $report->api;
		$fields = array("source"=>$source, "customer_id"=>$customer_id);
		//die(print_r($fields));
		$result = post_curl($url, $fields);
		//die($result);
		if ($source != "") {
			$source = "`ikase_" . $source . "`.";
		} else {
			$source = "`ikase`.";
		}
		$arrResult = explode("|", $result);
		$first_day = $arrResult[0];
		$last_day = $arrResult[1];
		$result = $arrResult[2];
		$message = $result;
		//send it
		$subject = $report->report . " (" . date("m/d/Y", strtotime($first_day)) .  " through " . date("m/d/Y", strtotime($last_day)) . " - " . $report->cus_name;
		$from = "system";
		$message_type = "reminder";	
		$priority = "";
		$dateandtime = date("Y-m-d H:i:s");
		$message_uuid = uniqid("MS", false);
		$thread_uuid = uniqid("TD", false);
		$user_uuid = $report->user_uuid;
		$case_worker = $report->nickname;
		
		$sql = "INSERT INTO `cse_thread` (`customer_id`, `dateandtime`, `thread_uuid`, `from`, `subject`) 
		VALUES('" . $customer_id . "', '" . $dateandtime . "','" . $thread_uuid . "', '" . $from . "', '" . $subject . "')";
		//echo $sql . "<br />";
		
		DB::run($sql);
		
		$sql = "INSERT INTO " . $source . "`cse_message`
		(`message_uuid`, `message_type`, `dateandtime`, `from`, `message_to`, `message`, `subject`, `priority`, `customer_id`)
		VALUES ('" . $message_uuid . "', '" . $message_type . "', '" . $dateandtime .  "', '" . $from . "', '" . $case_worker . "', '" . addslashes($message) . "', '" . addslashes($subject) . "', '" . $priority . "', '" . $customer_id . "')";
		//echo $sql . "<br />";
		
		DB::run($sql);
		
		$case_message_uuid = uniqid("TD", false);
		
		$sql = "INSERT INTO " . $source . "cse_thread_message 
		(`thread_message_uuid`, `thread_uuid`, `message_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $case_message_uuid  ."', '" . $thread_uuid . "', '" . $message_uuid . "', 'main', '" . $dateandtime . "', 'system', '" . $customer_id . "')";
		//echo $sql . "<br />";
		
		DB::run($sql);
		
		$sql = "INSERT INTO " . $source . "cse_message_user 
		(`message_user_uuid`, `message_uuid`, `user_uuid`, `thread_uuid`, `type`, `last_updated_date`, `last_update_user`, `customer_id`";
		$sql .= ")";
		$sql .= " VALUES ('" . $case_message_uuid  ."', '" . $message_uuid . "', '" . $user_uuid . "', '" . $thread_uuid . "', 'to', '" . $dateandtime . "', 'system', '" . $customer_id . "')";
		//echo $sql . "<br />";
		
		DB::run($sql);
		
		//attach the from
		$message_user_uuid = uniqid("TD", false);
		$sql = "INSERT INTO " . $source . "cse_message_user 
		(`message_user_uuid`, `message_uuid`, `user_uuid`, `type`, `last_updated_date`, `last_update_user`, `customer_id`, `thread_uuid`)
		VALUES ('" . $message_user_uuid  ."', '" . $message_uuid . "', 'system', 'from', '" . $dateandtime . "', 'system', '" . $customer_id . "', '". $thread_uuid . "')";
		//echo $sql . "<br />";	

		DB::run($sql);
	}

    echo json_encode(["success" => ["text" => "done"]]);
}
catch (PDOException $e) {
    echo json_encode(["error" => ["text" => $e->getMessage()]]);
}

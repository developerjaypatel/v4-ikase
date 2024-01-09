<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include("connection.php");

$db = getConnection();
$customer_id = 1075;
include("customer_lookup.php");


$sql = "SELECT * 
FROM ikase.cse_customer 
WHERE data_source != ''
AND customer_id = '" . $customer_id . "'
ORDER BY customer_id";

$customers = DB::select($sql);

foreach ($customers as $customer) {
	$customer_id = $customer->customer_id;
	$data_source = $customer->data_source;
	$sql = "SELECT * 
	FROM ikase.cse_user 
	WHERE customer_id = " . $customer_id;
	
	$users = DB::select($sql);
	
	//die(print_r($users));
	$arrUsers = array();
	$arrUsersByName = array();
	foreach ($users as $user) {
		$arrUsers[$user->nickname] = $user->user_uuid;
		$arrUsersByName[$user->user_name] = $user->user_uuid;
	}
	//die(print_r($arrUsers));
	$sql = "SELECT IFNULL(ctm.thread_uuid, '') thread_uuid, mess.* 
	FROM ikase_" . $data_source . ".cse_message mess
	
	LEFT OUTER JOIN ikase_" . $data_source . ".cse_thread_message ctm
	ON mess.message_id = ctm.message_id

	LEFT OUTER JOIN ikase_" . $data_source . ".cse_message_user cmu
	ON mess.message_uuid = cmu.message_uuid
	
	WHERE cmu.message_id IS NULL
	ORDER BY mess.message_id DESC";
	
	echo $sql . "\r\n\r\n";
	//die();
	$msgs = DB::select($sql);
	
	//echo $customer_id . " -> " . count($msgs) . "<br>";
	//die(print_r($msgs));
	foreach($msgs as $msg) {
		//die(print_r($msg));
		$to = $msg->message_to;
		$sender = $msg->from;
		$subject = $msg->subject;
		
		$message_id = $msg->message_id;
		$message_uuid = $msg->message_uuid;
		$thread_uuid = $msg->thread_uuid;
		
		try {
			//create thread if needed
			if ($thread_uuid=="") {
				$thread_uuid = uniqid("TD", false);
				//insert thread
				$sql = "INSERT INTO ikase_" . $data_source . ".cse_thread (`thread_uuid`, `dateandtime`, `from`, `subject`, `customer_id`) 
						VALUES ('". $thread_uuid . "', '" . date("Y-m-d H:i:s") . "', '" . $sender . "', '" . addslashes($subject) . "', '" . $customer_id . "')";
				echo $sql . "\r\n\r\n";
				
				$db= getConnection();
				$stmt = DB::run($sql);
				
				//attach to thread
				$thread_message_uuid = uniqid("TM", false);
			
				$last_updated_date = date("Y-m-d H:i:s");
				$sql = "INSERT INTO ikase_" . $data_source . ".cse_thread_message (`thread_message_uuid`, `thread_uuid`, `message_id`, `message_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $thread_message_uuid  ."', '" . $thread_uuid . "', '" . $message_id . "', '" . $message_uuid . "', 'main', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
				
				echo $sql . "\r\n";
				$stmt = DB::run($sql);
			}
			
			$arrTo = explode(";", $to);
			//die(print_r($arrTo));
			$table_name = "message";
			$last_updated_date = $msg->dateandtime;
			$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_message_user`
				(`" . $table_name . "_user_uuid`, `" . $table_name . "_uuid`, `" . $table_name . "_id`, `user_uuid`, `thread_uuid`, `type`, `last_updated_date`, `last_update_user`, `customer_id`) VALUES 
				";
			$arrInserts = array();
			foreach($arrTo as $nickname) {
				$nickname = trim($nickname);
				$user_uuid = $arrUsers[$nickname];
				$message_user_uuid = uniqid("MU", false);
				$arrInserts[] = "  ('" . $message_user_uuid  ."', '" . $message_uuid  ."', '" . $message_id . "', '" . $user_uuid . "', '" . $thread_uuid . "', 'to', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
			}
			
			$sql .= implode(", ", $arrInserts);
			echo $sql . "\r\n";
			$stmt = DB::run($sql);
		} catch(PDOException $e) {	
			echo "ERROR:" . $e->getMessage() .'\r\n'; 
			echo $sql;
			die();
		}		
	}
	die("done");
}

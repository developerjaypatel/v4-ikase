<?php
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
	$sql = "SELECT * FROM ikase.cse_user 
	WHERE customer_id = " . $customer_id;
	
	$users = DB::select($sql);
	$arrUsers = array();
	foreach ($users as $user) {
		$arrUsers[$user->nickname] = $user->user_uuid;
	}
	
	$sql = "SELECT msg.message_id, msg.message_uuid, message_from.user_uuid from_user_uuid, message_from.thread_uuid, msg.message_to, msg.dateandtime
	FROM ikase_" . $data_source . ".cse_message msg
	INNER JOIN `ikase_" . $data_source . "`.`cse_message_user` message_from ON msg.message_uuid = message_from.message_uuid
	AND message_from.`type` = 'from'
	LEFT OUTER JOIN `ikase_" . $data_source . "`.`cse_message_user` cmu 
	ON msg.message_uuid = cmu.message_uuid
	AND cmu.`type` = 'to'
	where cmu.message_uuid IS NULL
	AND message_from.thread_uuid != ''";
	
	echo $sql . "<br>";
	//die();
	$msgs = DB::select($sql);
	
	//echo $customer_id . " -> " . count($msgs) . "<br>";
	
	foreach($msgs as $msg) {
		$to = $msg->message_to;
		$from_uuid = $msg->from_user_uuid;
		$table_id = $msg->message_id;
		$table_uuid = $msg->message_uuid;
		$thread_uuid = $msg->thread_uuid;
		$arrTo = explode(";", $to);
		$table_name = "message";
		$last_updated_date = $msg->dateandtime;
		$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_message_user`
			(`" . $table_name . "_user_uuid`, `" . $table_name . "_uuid`, `" . $table_name . "_id`, `user_uuid`, `thread_uuid`, `type`, `last_updated_date`, `last_update_user`, `customer_id`) VALUES 
			";
		$arrInserts = array();
		foreach($arrTo as $nickname) {
			$nickname = trim($nickname);
			$user_uuid = $arrUsers[$nickname];
			
			$arrInserts[] = "  ('" . $table_uuid  ."', '" . $table_uuid  ."', '" . $table_id . "', '" . $user_uuid . "', '" . $thread_uuid . "', 'to', '" . $last_updated_date . "', '" . $from_uuid . "', '" . $customer_id . "')";
		}
		
		$sql .= implode(", ", $arrInserts);
		//echo $sql . "<br>";
		//die($sql);
		$stmt = DB::run($sql);
	}
}

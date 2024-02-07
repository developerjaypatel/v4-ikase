<?php
//die();
$app->post('/sms/send', 'sendSMS');
$app->post('/sms/assign', 'assembleSMSMessage');

function sendSMS(){
    $cellphone = passed_var("cellphone", "post");
    $message = passed_var("message", "post");

    $url = "https://rest.nexmo.com/sms/json?api_key=1623e20b&api_secret=4aad68ee8c2ca1d4&from=12046743938&to=" . $cellphone . "&text=" . urlencode($message);
	
	$response = file_get_contents($url);

    die(json_encode(array("success"=>"true", "response"=>$response)));
}
function assembleSMSMessage() {
	$cellphone = passed_var("cellphone", "post");
	$cellphone_submitted = $cellphone;
	
	$length = strlen($cellphone);
	if($length == 11){
		$cellphone = preg_replace('/([0-9]{1})([0-9]{3})([0-9]{3})([0-9]{4})/', '$2-$3-$4', $cellphone);
	} else {
		$cellphone = preg_replace('/([0-9]{3})([0-9]{3})([0-9]{4})/', '$1-$2-$3', $cellphone);
	}	
	
	$cellphone_search = $cellphone;
	
	$sql = "SELECT mess.`message`, mess.`message_uuid`, rem.`reminder_id`, buf.`buffer_id` 
			FROM ikase.cse_message mess 
			LEFT OUTER JOIN ikase.cse_buffer buf
			ON mess.`message_uuid` = buf.`message_uuid`
			LEFT OUTER JOIN ikase.cse_reminder_message rem_mess 
			ON mess.`message_uuid` = rem_mess.`message_uuid` 
			LEFT OUTER JOIN ikase.cse_reminder rem 
			ON rem_mess.`reminder_uuid` = rem.`reminder_uuid` AND rem.`buffered` = 'N'
			WHERE mess.`message_to` = :cellphone";
			// die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("cellphone", $cellphone);
		$stmt->execute();
		// $message_db = $stmt->fetchAll(PDO::FETCH_OBJ);
		$message_db = $stmt->fetchObject();
		$db = null; $stmt = null;
		
		// die(print_r($message_db));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}

	$message = $message_db->message;
	$reminder_id = $message_db->reminder_id;
	$buffer_id = $message_db->buffer_id;
	$message_uuid = $message_db->message_uuid;
	$cellphone = str_replace("-", "", $cellphone);
	$cellphone = "1" . $cellphone;
	$cellphone_nexmo = $cellphone;
	$url = "https://rest.nexmo.com/sms/json?api_key=1623e20b&api_secret=4aad68ee8c2ca1d4&from=12046743938&to=" . $cellphone . "&text=" . urlencode($message);	
	$response = file_get_contents($url);

	$strSQL = "UPDATE ikase.cse_reminder SET `buffered` = 'Y' WHERE `reminder_id` = '" . $reminder_id . "'";

	$query = "INSERT INTO ikase.`cse_sent` (`buffer_id`, `recipients`, `subject`, `message`, `message_uuid`)
			  VALUES ('" . $buffer_id . "', '" . $cellphone_search . "', 'event text message sent' , '" . addslashes($message) . "', '" . $message_uuid . "')";

	try {
		$db = getConnection();
		$stmt = $db->prepare($strSQL);
		$stmt->execute();
		$db = null; $stmt = null;

		$db = getConnection();
		$stmt = $db->prepare($query);
		$stmt->execute();
		$db = null; $stmt = null;

		// die(print_r($message_db));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}


	die(json_encode(array("success"=>"true", "response"=>$response)));
	/*
	find the message
	*/
}
function getSMSReminders() {
	/*
	loop it
	find the message
	send the message
	update the reminder as buffered
	*/
}
?>
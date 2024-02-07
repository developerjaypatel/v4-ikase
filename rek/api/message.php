<?php
$app->post('/reminder/updatemessage', authorize('user'), 'updateReminderMessage');
$app->post('/basic/save/voice', authorize('user'), 'saveBasicVoice');
$app->post('/basic/save/sms', authorize('user'), 'saveBasicSMS');

function updateReminderMessage() {
	session_write_close();
	
	$reminder_id = passed_var("reminder_id", "post");
	
	$reminder = getReminderInfo($reminder_id);
	
	//get the message id
	$sql = "SELECT message_id
	FROM md_reminder.tbl_message mess
	INNER JOIN md_reminder.tbl_reminder_message trm
	ON mess.message_uuid = trm.message_uuid
	INNER JOIN md_reminder.tbl_reminder rem
	ON trm.reminder_uuid = rem.reminder_uuid
	WHERE rem.reminder_id = :reminder_id
	AND rem.customer_id = :customer_id";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("reminder_id",  $reminder_id);
		$stmt->bindParam("customer_id",  $_SESSION['user_customer_id']);
		$stmt->execute();
		$message = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		//now update the message itself
		$message_text = @processHTML($_POST["message"]);
		$message_id = $message->message_id;
		
		$sql = "UPDATE tbl_message
		SET `message` = :message
		WHERE message_id = :message_id
		AND customer_id = :customer_id";
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("message_id",  $message_id);
		$stmt->bindParam("message",  $message_text);
		$stmt->bindParam("customer_id",  $_SESSION['user_customer_id']);
		
		$stmt->execute();
		$stmt = null; $db = null;
		
		$sql = "INSERT INTO tbl_message_track 
		(`user_uuid`, `user_name`, `operation`, `message_id`, `message_uuid`, `message_type`, `dateandtime`, `from`, `message_to`, `message_cc`, `message_bcc`, `message`, `original_message`, `subject`, `snippet`, `attachments`, `priority`, `callback_date`, `customer_id`, `status`, `deleted`)
		SELECT :user_uuid, :user_name, 'update', mess.*
		FROM tbl_message mess
		WHERE message_id = :message_id
		AND customer_id = :customer_id";
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("message_id",  $message_id);
		$stmt->bindParam("customer_id",  $_SESSION['user_customer_id']);
		$stmt->bindParam("user_uuid",  $_SESSION['user_id']);
		$stmt->bindParam("user_name",  $_SESSION['user_name']);
		
		$stmt->execute();
		$stmt = null; $db = null;
		
		if ($reminder->reminder_type == "voice") {
			//generate the voice
			$result = voiceReminder($reminder_id, $message_id, "return");
			$result = json_decode($result);
		}
		echo json_encode(array("success"=>true, "reminder_id"=>$reminder_id, "type"=>$reminder->reminder_type, "message_id"=>$message_id, "voice"=>$result));
	} catch(PDOException $e) {
		$error = array("sql"=>$sql, "error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
	}
}
function saveBasicVoice() {
	session_write_close();
	try {
		$basic_voice_message = passed_var("basic_voice_message", "post");
		$basic_voice_message = addslashes($basic_voice_message);
		$sql = "UPDATE tbl_customer
		SET `basic_voice_message` = :basic_voice_message
		WHERE 1
		AND customer_id = :customer_id";
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("basic_voice_message", $basic_voice_message);
		$stmt->bindParam("customer_id",  $_SESSION['user_customer_id']);
		
		$stmt->execute();
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>true));
	} catch(PDOException $e) {
		$error = array("sql"=>$sql, "error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
	}
}
function saveBasicSMS() {
	session_write_close();
	try {
		$basic_sms_message = passed_var("basic_sms_message", "post");
		$basic_sms_message = addslashes($basic_sms_message);
		$sql = "UPDATE tbl_customer
		SET `basic_sms_message` = :basic_sms_message
		WHERE 1
		AND customer_id = :customer_id";
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("basic_sms_message",  $basic_sms_message);
		$stmt->bindParam("customer_id",  $_SESSION['user_customer_id']);
		
		$stmt->execute();
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>true));
	} catch(PDOException $e) {
		$error = array("sql"=>$sql, "error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
	}
}
?>
<?php
include("manage_session.php");
set_time_limit(10000);
if (ob_get_level() == 0) ob_start();

include("connection.php");

$customer_id = passed_var("customer_id", "get");
if (!is_numeric($customer_id)) {
	die();
}

try {
	$db = getConnection();
	
	include("customer_lookup.php");
	
	$sql_truncate = "TRUNCATE `" . $data_source . "`.`" . $data_source . "_message`; 
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_case_message`; ";
	
	echo $sql_truncate . "\r\n\r\n";
	$stmt = $db->prepare($sql_truncate);
	$stmt->execute();
	
	$last_updated_date = date("Y-m-d H:i:s");
	
	$sql = "SELECT DISTINCT mc.case_id, mc.case_uuid, mc.cpointer
	FROM `" . $data_source . "`.`" . $data_source . "_case` mc
	INNER JOIN `" . $data_source . "`.`email` `email`
	ON mc.cpointer = `email`.CASENO
	ORDER BY mc.cpointer";
	
	echo $sql . "\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$header_start_time = $time;

	$cases = $stmt->fetchAll(PDO::FETCH_OBJ);
	
	foreach($cases as $key=>$case){
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$row_start_time = $time;
		
		echo "<br>Processing -> " . $key. " == " . $case->cpointer . "\r\n\r\n";
		
		$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_message` (`case_message_uuid`, `case_uuid`, `message_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
		SELECT '" . $case->case_uuid . "', '" . $case->case_uuid . "', 
		CONCAT(`CASENO`,'_DOC_', `ACTNO`) AS `message_uuid`, 
		'message', `DATE` notedate, `INITIALS`, 'N', " . $customer_id . "
		FROM `" . $data_source . "`.`doctrk1`
		WHERE `CASENO` = '" . $case->cpointer . "'";
		echo $sql . "\r\n\r\n";
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_message` (`message_uuid`, `message_type`, `dateandtime`, `from`, `message_to`, `subject`, `message`, `customer_id`) 
		SELECT CONCAT(email.CASENO, '_MSG_', email.FILENAME) `message_uuid`, 
		'email', email.DATECREATE, email.WHOFROM, email2.WHOTO, email.SUBJECT, '', " . $customer_id . "
		FROM `" . $data_source . "`.email
		LEFT OUTER JOIN `" . $data_source . "`.email2
		ON email.FILENAME = email2.FILENAME
		WHERE 1
		AND `CASENO` = '" . $case->cpointer . "'";
		
		echo $sql . "\r\n\r\n";
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		//attach to users
		$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_message_user` (`message_user_uuid`, `message_uuid`, `user_uuid`, `type`, 
		`last_updated_date`, `last_update_user`,`customer_id`)
		SELECT CONCAT(email.CASENO, '_MU_', email.FILENAME), CONCAT(email.CASENO, '_MSG_', email.FILENAME), 
		`WHOTO`, 'to', '" . $last_updated_date . "', 'system', " . $customer_id . "
		FROM `" . $data_source . "`.email
		LEFT OUTER JOIN `" . $data_source . "`.email2
		ON email.FILENAME = email2.FILENAME
		WHERE 1
		AND `CASENO` = '" . $case->cpointer . "'";
		
		echo $sql . "\r\n\r\n";
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		//attach to users
		$sql = "INSERT INTO cse_message_user (`message_user_uuid`, `message_uuid`, `user_uuid`, `type`, `last_updated_date`, `last_update_user`, `customer_id`)
		SELECT CONCAT(email.CASENO, '_MU_', email.FILENAME), CONCAT(email.CASENO, '_MSG_', email.FILENAME), 
		`WHOFROM`, 'from', '" . $last_updated_date . "', 'system', " . $customer_id . "
		FROM `" . $data_source . "`.email
		LEFT OUTER JOIN `" . $data_source . "`.email2
		ON email.FILENAME = email2.FILENAME
		WHERE 1
		AND `CASENO` = '" . $case->cpointer . "'";
		
		echo $sql . "\r\n\r\n";
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $row_start_time), 4);
		echo "Time spent:" . $total_time . "\r\n\r\n";

	}
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	$success = array("success"=> array("text"=>"done", "duration"=>$total_time));
	echo json_encode($success);
	
	$db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}

include("cls_logging.php");
?>
<script language="javascript">
parent.setFeedback("messages import completed");
</script>
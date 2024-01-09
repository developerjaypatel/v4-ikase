<?php
include("manage_session.php");
set_time_limit(3000);

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;

include("connection.php");
$customer_id = passed_var("customer_id", "get");
if (!is_numeric($customer_id)) {
	die();
}

try {
	$db = getConnection();
	
	//lookup the customer name
	$sql_customer = "SELECT data_source
	FROM  `ikase`.`cse_customer` 
	WHERE customer_id = :customer_id";
	
	$stmt = $db->prepare($sql_customer);
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->execute();
	$customer = $stmt->fetchObject();
	$data_source = $customer->data_source;
	
	$sql_truncate = "TRUNCATE `ikase_" . $data_source . "`.`cse_setting`";
	//die($sql_truncate);
	
	$stmt = $db->prepare($sql_truncate);
	$stmt->execute();
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$row_start_time = $time;
	
		
	$sql = "
	INSERT INTO `ikase_" . $data_source . "`.`cse_setting`
	(`setting_uuid`, `customer_id`, `category`, `setting`, `setting_value`, `setting_type`, `default_value`)
	SELECT `setting_uuid`, '" . $customer_id . "', `category`, `setting`, `setting_value`, `setting_type`, `default_value`
	FROM `ikase`.`cse_setting`
	WHERE (category = 'calendar_type' OR category = 'delay')
	AND customer_id = 1033 ;";
	
	//echo $sql . "\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	
	//FIRST two letters
	$first_two = substr($data_source, 0, 2);
	
	//case number next
	$sql = "
	INSERT INTO `ikase_" . $data_source . "`.`cse_setting`
	(`setting_uuid`, `customer_id`, `category`, `setting`, `setting_value`, `setting_type`, `default_value`)
	SELECT `setting_uuid`, '" . $customer_id . "', `category`, `setting`, '1000', `setting_type`, `default_value`
	FROM `ikase`.`cse_setting`
	WHERE (`setting` = 'case_number_next')
	AND customer_id = 1033 ;";
	
	//echo $sql . "\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	//FIRST two letters
	$first_two = substr($data_source, 0, 2);
	
	//prefix
	$sql = "
	INSERT INTO `ikase_" . $data_source . "`.`cse_setting`
	(`setting_uuid`, `customer_id`, `category`, `setting`, `setting_value`, `setting_type`, `default_value`)
	SELECT `setting_uuid`, '" . $customer_id . "', `category`, `setting`, '" . strtoupper($first_two) . "', `setting_type`, `default_value`
	FROM `ikase`.`cse_setting`
	WHERE (`setting` = 'case_number_prefix')
	AND customer_id = 1033 ;";
	
	//echo $sql . "\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	/*
	$sql = "ALTER TABLE `ikase_" . $data_source . "`.`cse_venue` 
	CHANGE COLUMN `venue_id` `venue_id` INT(11) NOT NULL AUTO_INCREMENT ;";
	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	*/
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_venue`";
	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_venue` (`venue_id`, `venue_uuid`, `venue`, `venue_abbr`, `address1`, `address2`, `city`, `zip`, `phone`, `presiding`)
SELECT `venue_id`, `venue_uuid`, `venue`, `venue_abbr`, `address1`, `address2`, `city`, `zip`, `phone`, `presiding` 
FROM `ikase`.`cse_venue` WHERE 1";

	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_bodyparts`";
	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_bodyparts` 
	SELECT * FROM ikase.cse_bodyparts";
	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n<BR><BR>";
	$stmt->execute();
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $row_start_time), 4);
	echo "Time spent:" . $total_time . "<br />
<br />
";
	
	$success = array("success"=> array("text"=>"done", "duration"=>$total_time));
	echo json_encode($success);
	
	$db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
?>
<script language="javascript">
parent.setFeedback("calendar types import completed");
</script>
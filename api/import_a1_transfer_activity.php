<?php
require_once('../shared/legacy_session.php');

include("connection.php");

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_activity`;
	TRUNCATE `ikase_" . $data_source . "`.`cse_case_activity`";
	echo $sql . "\r\n\r\n";
	$stmt = DB::run($sql);
	
	$sql = "UPDATE `" . $data_source . "`.`" . $data_source . "_activity`
	SET activity_category = 'REDFLAG', 
	flag = 'red'
	where activity_category = ''
	AND (activity_uuid LIKE '%_1' OR activity_uuid LIKE '%_2')";
	echo $sql . "<br><br><br /><br />";
	//die();
	$stmt = DB::run($sql);
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_activity` (`activity_uuid`, `activity`, `activity_category`, `activity_date`, `hours`, `timekeeper`, `activity_user_id`, `customer_id`, `deleted`)
	SELECT `activity_uuid`, `activity`, IF(`flag`='red', 'REDFLAG', `activity_category`) `activity_category`, `activity_date`, `hours`, `timekeeper`, `activity_user_id`, `customer_id`, `deleted`
	FROM `" . $data_source . "`.`" . $data_source . "_activity` 
	WHERE 1 AND customer_id = " . $customer_id;
	echo $sql . "\r\n\r\n";
	$stmt = DB::run($sql);
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_activity`
	(`case_activity_uuid`, `case_uuid`, `activity_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id`)
	SELECT `case_activity_uuid`, `case_uuid`, `activity_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id` 
	FROM `" . $data_source . "`.`" . $data_source . "_case_activity` 
	WHERE 1 AND customer_id = " . $customer_id;
	
	echo $sql . "\r\n\r\n";
	$stmt = DB::run($sql);
	
	$success = array("success"=> array("text"=>"done"));
	echo json_encode($success);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}	
include("cls_logging.php");
?>
<script language="javascript">
parent.setFeedback("activity transfer completed");
</script>

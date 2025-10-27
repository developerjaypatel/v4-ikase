<?php
include("manage_session.php");

include("connection.php");

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_activity`
	(`case_activity_uuid`, `case_uuid`, `activity_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id`)
	SELECT `case_activity_uuid`, cca.`case_uuid`, `activity_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id` 
	FROM `" . $data_source . "`.`" . $data_source . "_case_activity` cca
	INNER JOIN " . $data_source . ".badcases
	ON cca.case_uuid = badcases.case_uuid
	WHERE 1";
	
	echo $sql . "\r\n\r\n";
	//$stmt = $db->prepare($sql);
	//$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_activity` (`activity_uuid`, `activity`, `activity_category`, `activity_date`, `hours`, `timekeeper`, `activity_user_id`, `customer_id`, `deleted`)
	SELECT ca.`activity_uuid`, ca.`activity`, ca.`activity_category`, ca.`activity_date`, ca.`hours`, ca.`timekeeper`, ca.`activity_user_id`, ca.`customer_id`, ca.`deleted`
	FROM `" . $data_source . "`.`" . $data_source . "_activity` ca
    
    LEFT OUTER JOIN ikase_" . $data_source . ".cse_activity gca
    ON ca.activity_uuid = gca.activity_uuid
	
	INNER JOIN `ikase_" . $data_source . "`.`cse_case_activity` cca
	ON ca.activity_uuid = cca.activity_uuid
	INNER JOIN " . $data_source . ".badcases
	ON cca.case_uuid = badcases.case_uuid
	WHERE 1
	AND gca.activity_uuid IS NULL";
	echo $sql . "\r\n\r\n";
	//$stmt = $db->prepare($sql);
	//$stmt->execute();
	
	
	$db = null;
	
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
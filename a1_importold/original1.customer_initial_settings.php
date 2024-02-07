<?php
include("manage_session.php");

include("connection.php");
$customer_id = passed_var("customer_id", "get");
if (!is_numeric($customer_id)) {
	die();
} 

$sql = "INSERT INTO `cse_customer_setting` (`setting_uuid`, `customer_uuid`, `category`, `setting`, `setting_value`, `setting_type`, `default_value`)
SELECT REPLACE( setting_uuid,  'MO',  'SO' ) setting_uuid, '" . $customer_id . "', `category`, `setting`, `setting_value`, `setting_type`, `default_value` 
FROM  `cse_customer_setting` 
WHERE  `customer_uuid` LIKE  '1040'";
try {
	$db = getConnection();
	
	$stmt = $db->prepare($sql);
	$stmt->execute();
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}	
?>
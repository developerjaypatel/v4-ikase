<?php
if (!isset($customer_id)) {
	$customer_id = passed_var("customer_id", "get");
	if (!is_numeric($customer_id)) {
		die("no id");
	}
}
$sql_customer = "SELECT cus_name, data_source, permissions
FROM  `ikase`.`cse_customer` 
WHERE customer_id = :customer_id AND deleted = 'N'";

$stmt = $db->prepare($sql_customer);
$stmt->bindParam("customer_id", $customer_id);
$stmt->execute();
$customer = $stmt->fetchObject();

//die(print_r($customer));
$cus_name = $customer->cus_name;
$data_source = $customer->data_source;
$permissions = $customer->permissions;

$_SESSION['user_data_source'] = $data_source;

if (!isset($batchscan_id)) {
	if (strpos($permissions, "i")===false) {
		die("no permissions");
	}
}

session_write_close();
?>
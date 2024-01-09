<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
ini_set('display_errors', '1');

include("manage_session.php");
//die(print_r($_SESSION));
if ($_SESSION['user_role']!="admin" && $_SESSION['user_role']!="owner") {
	die("no updates right now.");
}
include("connection.php");

$sql = "SELECT data_source 
FROM ikase.cse_customer
WHERE data_source != ''
ORDER BY data_source ASC";

$table_name = $_POST['table_name'];
$column = $_POST['column'];
$data_type = $_POST['data_type'];

if ($column=="") {
	die("no column");
}
try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$customers = $stmt->fetchAll(PDO::FETCH_OBJ);
	
	//die(print_r($customers));
	
	foreach($customers as $customer) {
		//does it have a customer id
		$sql = "SELECT COUNT(*) col_count FROM INFORMATION_SCHEMA.COLUMNS 
		WHERE TABLE_SCHEMA = 'ikase_" . $customer->data_source . "' 
		AND TABLE_NAME = 'cse_" . $table_name . "' 
		AND `COLUMN_NAME` = '" . $column . "'";
		
		//echo $sql . "\r\n";
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$has_column = $stmt->fetchObject();
			
		//print_r($has_column);
		if ($has_column->col_count==0) {
			$sql = "ALTER TABLE `ikase_" . $customer->data_source . "`.`cse_" . $table_name . "` 
			ADD COLUMN `" . $column . "` " . $data_type;
			
			echo $sql . "<br />";
			//echo $customer->data_source . " done<br />";
			
			//die();
			$stmt = $db->prepare($sql);
			$stmt->execute();	
		}	
	}
	$success = array("success"=> array("text"=>"done"));
	echo json_encode($success);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
?>
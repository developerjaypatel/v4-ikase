<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
ini_set('display_errors', '1');

require_once('../shared/legacy_session.php');
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
	$customers = DB::select($sql);
	
	//die(print_r($customers));
	
	foreach($customers as $customer) {
		//does it have a customer id
		$sql = "SELECT COUNT(*) col_count FROM INFORMATION_SCHEMA.COLUMNS 
		WHERE TABLE_SCHEMA = 'ikase_" . $customer->data_source . "' 
		AND TABLE_NAME = 'cse_" . $table_name . "' 
		AND `COLUMN_NAME` = '" . $column . "'";
		
		//echo $sql . "\r\n";
		$stmt = DB::run($sql);
		$has_column = $stmt->fetchObject();
			
		//print_r($has_column);
		if ($has_column->col_count==0) {
			$sql = "ALTER TABLE `ikase_" . $customer->data_source . "`.`cse_" . $table_name . "` 
			ADD COLUMN `" . $column . "` " . $data_type;
			
//			echo $sql . "<br />";
			//echo $customer->data_source . " done<br />";
			//die();
			DB::run($sql);
		}	
	}
    echo json_encode(["success" => ["text" => "done"]]);
}
catch (PDOException $e) {
    echo json_encode(["error" => ["text" => $e->getMessage()]]);
}

<?php
require_once('../shared/legacy_session.php');
//die(print_r($_SESSION));
if ($_SESSION['user_role']!="admin" && $_SESSION['user_role']!="owner") {
	die("no updates right now.");
}
include("connection.php");

	
$sql = "SELECT `schema_name`
FROM `information_schema`.schemata 
WHERE schema_name LIKE 'ikase%'";

try {
	$schemas = DB::select($sql);
	
	//die(print_r($schemas));
	
	foreach($schemas as $sindex=>$schema) {
		//skip
		// || $schema->schema_name=="ikase_gonzalez"
		if ($schema->schema_name=="ikase_glauber" || $schema->schema_name=="ikase" || $schema->schema_name=="ikase_glauber2") {
			continue;
		}
		$datasource = str_replace("ikase_", "", $schema->schema_name);
		
		$sql = "SELECT customer_id 
		FROM ikase.cse_customer 
		WHERE data_source = '" . $datasource . "'";
		$stmt = DB::run($sql);
		$customer = $stmt->fetchObject();
		//die(print_r($tables));
		
		if (!is_object($customer) || $customer->customer_id == "") {
			continue;
		}
		$sql = "DELETE FROM `" .$schema->schema_name . "`.cse_case WHERE customer_id != " . $customer->customer_id;
		echo $sql . "\r\n"; //FIXME
	}
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}

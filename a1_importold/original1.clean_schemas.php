<?php
include("manage_session.php");
//die(print_r($_SESSION));
if ($_SESSION['user_role']!="admin" && $_SESSION['user_role']!="owner") {
	die("no updates right now.");
}
include("connection.php");

	
$sql = "SELECT `schema_name`
FROM `information_schema`.schemata 
WHERE schema_name LIKE 'ikase%'";

try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$schemas = $stmt->fetchAll(PDO::FETCH_OBJ);
	
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
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$customer = $stmt->fetchObject();
		//die(print_r($tables));
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		if (!is_object($customer) || $customer->customer_id == "") {
			continue;
		}
		$sql = "DELETE FROM `" .$schema->schema_name . "`.cse_case
		WHERE customer_id != " . $customer->customer_id . ";";

		echo $sql . "\r\n";
	}
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
?>
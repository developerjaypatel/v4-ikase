<?php
include("manage_session.php");
//die(print_r($_SESSION));
if ($_SESSION['user_role']!="admin" && $_SESSION['user_role']!="owner") {
	die("no updates right now.");
}
die();
include("connection.php");

$sql_statement = $_POST["sql"];

if (strpos(strtoupper($sql_statement), "DROP") !== false) {
	die("no biggie");
}
if (strpos(strtoupper($sql_statement), "TRUNCATE") !== false) {
	die("no biggieS");
}
	
$sql = "SELECT `schema_name`
FROM `information_schema`.schemata 
WHERE schema_name LIKE 'ikase%'";

try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$schemas = $stmt->fetchAll(PDO::FETCH_OBJ);
	$stmt->closeCursor(); $stmt = null; $db = null;
	//die(print_r($schemas));
	
	foreach($schemas as $sindex=>$schema) {
		//skip
		// || $schema->schema_name=="ikase_gonzalez"
		if ($schema->schema_name=="ikase_glauber" || $schema->schema_name=="ikase_dordulian" || $schema->schema_name=="ikase_dordulian2" || $schema->schema_name=="ikase_glauber2") {
			continue;
		}
		
		$sqlcount = "SELECT table_schema, COUNT(*) columns
		FROM INFORMATION_SCHEMA.COLUMNS
		WHERE table_name = 'cse_activity'
		AND table_schema = '" . $schema->schema_name . "'
		AND column_name = 'billing_amount'";
		
		$db = getConnection();
		$stmt = $db->prepare($sqlcount);
		$stmt->execute();
		$count = $stmt->fetchObject();
		//die(print_r($count));
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		if ($count->columns == 0) {
			$sql = str_replace("`ikase`", "`" . $schema->schema_name . "`", $sql_statement);
			
			echo $sql . "\r\n\r\n";
			//die();
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->execute();	
			$stmt = null; $db = null;	
		}
		
	}
	$success = array("success"=> array("text"=>"done"));
	echo json_encode($success);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
?>
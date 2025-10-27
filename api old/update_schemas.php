<?php
include("manage_session.php");
//die(print_r($_SESSION));
if ($_SESSION['user_role']!="admin" && $_SESSION['user_role']!="owner") {
	die("no updates right now.");
}
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
	
	//die(print_r($schemas));
	
	foreach($schemas as $sindex=>$schema) {
		//skip
		// || $schema->schema_name=="ikase_gonzalez"
		if ($schema->schema_name=="ikase_glauber" || $schema->schema_name=="ikase_glauber2") {
			continue;
		}
		
		$sql = str_replace("`ikase`", "`" . $schema->schema_name . "`", $sql_statement);
		
		echo $sql . "\r\n\r\n";
		//die();
		$stmt = $db->prepare($sql);
		$stmt->execute();	
		/*
		$list = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		if (count($list) > 0) {
			echo $sql . "\r\n\r\n";
			print_r($list);
		}
		*/
	}
	$success = array("success"=> array("text"=>"done"));
	echo json_encode($success);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
?>
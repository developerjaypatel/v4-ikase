<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include("connection.php");

$schema = $_GET["schema"];
/*	
$sql = "SELECT `schema_name`
FROM `information_schema`.schemata 
WHERE schema_name LIKE 'ikase%'";
*/
try {
	/*
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$schemas = $stmt->fetchAll(PDO::FETCH_OBJ);
	$stmt->closeCursor(); $stmt = null; $db = null;
	//die(print_r($schemas));
	
	foreach($schemas as $schema) {
	
		//skip
		if ($schema->schema_name=="ikase_glauber" || $schema->schema_name=="ikase_glauber2") {
			continue;
		}
		*/
		//get all the tables for the schema
		$sql = "SHOW TABLES IN " . $schema;
		$db = getConnection();
		
		$stmt = $db->prepare($sql);
		$stmt->execute();		
		$tables = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		foreach($tables as $listing) {
			$table = $listing->{"Tables_in_" . $schema};
			$sql = "SELECT COUNT(*) `rowcount` FROM `" . $schema . "`.`" . $table . "`";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("schema", $schema);
			$stmt->execute();		
			$count = $stmt->fetchObject();
			
			$stmt->closeCursor(); $stmt = null; $db = null;
			
			$db = getAWSConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("schema", $schema);
			$stmt->execute();		
			$count_aws = $stmt->fetchObject();
			
			$stmt->closeCursor(); $stmt = null; $db = null;
			
			if ($count_aws->row_count != $count->row_count) {
				echo "AZ Table:" . $table .  " --> " . $count->rowcount . "<br />";
				echo "AWS Table:" . $table .  " --> " . $count_aws->rowcount . "<br />";
				die("\r\nMust Fix" . $schema . " -> " . $table);
			}
		}
	//}
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
?>
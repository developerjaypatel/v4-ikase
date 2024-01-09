<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include("connection.php");

//$schema = $_GET["schema"];
	
$sql = "SELECT `schema_name`
FROM `information_schema`.schemata 
WHERE schema_name LIKE 'ikase%'";

try {
	/*
	
	$schemas = DB::select($sql);
	//die(print_r($schemas));
	*/
	//foreach($schemas as $schema) {
		$schema = "ikase";
		$sql = "SELECT activity_category cat, COUNT(*) rowcount 
		FROM " . $schema . ".cse_activity
		WHERE activity_date > '2017-11-05'
		GROUP BY activity_category";
		$db = getConnection();
		$stmt = $db->prepare($sql);
		//$stmt->bindParam("schema", $schema);
		$stmt->execute();		
		$counts = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		foreach($counts as $count) {
			if ($count->rowcount > 0) {
				echo $schema . " -> " . $count->cat . ": " . $count->rowcount . "<br>";
			}
		}
	//}
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}

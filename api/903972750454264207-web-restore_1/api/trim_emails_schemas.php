<?php
die();
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
		//let's get the duplicated emails
		$sql = "SELECT message_uuid, MIN(message_id) min_id
		FROM `" . $schema->schema_name . "`.cse_message
		WHERE message_type = 'email'
		AND `status` = 'created'
		GROUP BY message_uuid
		HAVING COUNT(message_id) > 1";

		//echo $sql . "\r\n";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$messages = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		foreach($messages as $message) {
			//delete all messages with that uuid but not that id
			$message_id = $message->min_id;
			$message_uuid = $message->message_uuid;
			
			$sql = "DELETE FROM `" . $schema->schema_name . "`.cse_message
			WHERE message_uuid = '" . $message_uuid . "'
			AND message_id != '" . $message_id . "'";
			
			//die($sql);
			
			$db = getConnection();
			$stmt = $db->prepare($sql);
			//$stmt->bindParam("message_uuid", $message_uuid);
			//$stmt->bindParam("message_id", $message_id);
			$stmt->execute();
			$stmt = null; $db = null;
		}
		
		echo  $schema->schema_name . " done " . date("H:i:s") . "\r\n";
		//die();
	}
	$success = array("success"=> array("text"=>"done"));
	echo json_encode($success);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
?>
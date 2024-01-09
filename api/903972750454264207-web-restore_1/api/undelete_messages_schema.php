<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
ini_set('display_errors', '1');

include("manage_session.php");
//die(print_r($_SERVER));
if ($_SESSION['user_role']!="admin" && $_SESSION['user_role']!="owner") {
	die("no updates right now.");
}
include("connection.php");

$sql = "SELECT `schema_name`
FROM `information_schema`.schemata 
WHERE schema_name LIKE 'ikase%'";

$sql_statement = "SELECT summary.message_uuid, dateandtime, summary.records, summary.deleteds, 
summary.records - summary.deleteds remaining
FROM (
	SELECT message_uuid, SUM(IF(deleted='Y', 1, 0)) deleteds, COUNT(message_user_id) records
	FROM `ikase`.cse_message_user
	WHERE 1
	GROUP BY message_uuid
) summary
INNER JOIN `ikase`.cse_message mes
ON summary.message_uuid = mes.message_uuid
WHERE records - deleteds > 0
AND deleteds > 0
AND mes.deleted = 'Y'";

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
		if ($schema->schema_name=="ikase_glauber" || $schema->schema_name=="ikase_glauber2" || $schema->schema_name=="ikase_leyva") {
			continue;
		}
		
		$sql = str_replace("`ikase`", "`" . $schema->schema_name . "`", $sql_statement);
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$messages = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		if (count($messages) > 1) {
			//echo $sql . "\r\n\r\n";
			//die($schema->schema_name . " ready with " . count($messages) . " messages " . date("H:i:s"));
		}
		//die();
		foreach($messages as $message) {
			
			
			$sql = "UPDATE `" . $schema->schema_name . "`.cse_message
			SET deleted = 'N'
			WHERE message_uuid = '" . $message->message_uuid . "'";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->execute();	
			$stmt = null; $db = null;	
			
			//die($sql);
		}
		
		if (count($messages) > 1) {
			die($schema->schema_name . " done with " . count($messages) . " messages " . date("H:i:s"));
		}
	}
	$success = array("success"=> array("text"=>"done"));
	echo json_encode($success);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
?>
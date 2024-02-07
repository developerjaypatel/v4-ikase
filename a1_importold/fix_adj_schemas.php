<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include("connection.php");
	
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
	
	foreach($schemas as $schema) {
	
		//skip
		if ($schema->schema_name=="ikase_glauber" || $schema->schema_name=="ikase_glauber2") {
			continue;
		}
		
		//get all the tables for the schema
		$sql = "SELECT cca.case_uuid, MIN(activity_id) min_activity_id, COUNT(activity_id) activity_count
		FROM " . $schema->schema_name . ".cse_activity act
		INNER JOIN " . $schema->schema_name . ".cse_case_activity cca
		ON act.activity_uuid = cca.activity_uuid
		WHERE activity = 'ADJ Generated'
		AND attribute = 'EAMS Submission'
		AND act.deleted = 'N'
		GROUP BY cca.case_uuid
		HAVING COUNT(activity_id) > 1";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();		
		$mins = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		foreach($mins as $min) {
			$min_activity_id = $min->min_activity_id;
			$case_uuid = $min->case_uuid;
			
			$sql = "UPDATE " . $schema->schema_name . ".cse_activity act, 
			" . $schema->schema_name . ".cse_case_activity cca
			SET act.deleted = 'Y'
			WHERE act.activity_uuid = cca.activity_uuid
			AND cca.attribute = 'EAMS Submission'
			AND  activity = 'ADJ Generated'
			AND cca.case_uuid = '" . $case_uuid . "'
			AND act.activity_id != '" . $min_activity_id . "'";
			
			echo $sql . "<br />";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->execute();		
			$stmt = null; $db = null;
		}
		
		echo "Schema " . $schema->schema_name . " done<br />";
	}
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
?>
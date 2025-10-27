<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
die();
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
	$stmt->closeCursor(); $stmt = null; $db = null;
	//die(print_r($schemas));
	
	foreach($schemas as $sindex=>$schema) {
		//skip
		// || $schema->schema_name=="ikase_gonzalez"
		if ($schema->schema_name=="ikase_glauber" || $schema->schema_name=="ikase3"  || $schema->schema_name=="ikase_basictemplate" || $schema->schema_name=="ikase_dordulian" || $schema->schema_name=="ikase_dordulian2" || $schema->schema_name=="ikase_glauber2") {
			continue;
		}
		
		echo "<a href='update_increment.php?sc=" . $schema->schema_name . "' target='_blank'>" . $schema->schema_name . "</a><br />";
	}
	$success = array("success"=> array("text"=>"done"));
	echo json_encode($success);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
?>
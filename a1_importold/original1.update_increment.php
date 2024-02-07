<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
set_time_limit(3000);
die();
include("manage_session.php");
//die(print_r($_SESSION));
if ($_SESSION['user_role']!="admin" && $_SESSION['user_role']!="owner") {
	die("no updates right now.");
}

include("connection.php");

	
$schema_name = $_GET["sc"];

if ($schema_name=="") {
	die();
}
try {		
	echo  "<br /><br />Processing " . $schema_name . "<br />";
	$sqlcount = "SHOW TABLES IN " . $schema_name;
	
	$db = getConnection();
	$stmt = $db->prepare($sqlcount);
	$stmt->execute();
	$tables = $stmt->fetchAll(PDO::FETCH_OBJ);
	//die(print_r($count));
	$stmt->closeCursor(); $stmt = null; $db = null;
	//echo $schema_name;
	//die(print_r($tables));
	foreach ($tables as $table) {
		$table_name = $table->{"Tables_in_" . $schema_name};
		//die($table_name );
		if (strpos($table_name, "cse_")!==false) {
			//die($table_name);
			//auto increment
			$sql = "SELECT `AUTO_INCREMENT` increment
			FROM  INFORMATION_SCHEMA.TABLES
			WHERE TABLE_SCHEMA = '" . $schema_name . "'
			AND   TABLE_NAME   = '" . $table_name . "';";
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->execute();
			$info = $stmt->fetchObject();
			
			$stmt->closeCursor(); $stmt = null; $db = null;
			
			$inc = $info->increment + 1;
			
			echo "<a href='update_table_increment.php?sc=" . $schema_name . "&tb=" . $table_name . "&prev=" . $info->increment . "&inc=" . $inc . "' target='_blank'>" . $table_name . "</a><br />";
			//die();
		}
	}

	$success = array("success"=> array("text"=>"done"));
	echo json_encode($success);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
?>
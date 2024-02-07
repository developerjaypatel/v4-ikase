<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
ini_set('display_errors', '1');

include("manage_session.php");
//die(print_r($_SESSION));
if ($_SESSION['user_role']!="admin" && $_SESSION['user_role']!="owner") {
	die("no updates right now.");
}
include("connection.php");

$sql = "SELECT `TABLE_NAME` FROM `INFORMATION_SCHEMA`.`TABLES` 
        WHERE `TABLE_SCHEMA` = 'ikase' 
		AND `TABLE_NAME` LIKE 'cse_%'
		AND `TABLE_NAME` NOT LIKE '%_old'
		AND `TABLE_NAME` != 'cse_user'
		AND `TABLE_NAME` != 'cse_customer'";
$destination = $_POST['destination'];
$destination_customer_id = $_POST['destination_customer_id'];

if ($destination=="" || $destination_customer_id=="") {
	die("no dest");
}
try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$tables = $stmt->fetchAll(PDO::FETCH_OBJ);
	
	//die(print_r($tables));
	
	foreach($tables as $table) {
		if ($table->TABLE_NAME=="cse_personx") {
			continue;
		}
		if ($table->TABLE_NAME=="cse_personx_track") {
			continue;
		}
		//does the table exist in the destination?
		$sql = "SELECT COUNT(`TABLE_NAME`) table_count FROM `INFORMATION_SCHEMA`.`TABLES` 
        WHERE `TABLE_SCHEMA` = 'ikase_" . $destination . "' 
		AND `TABLE_NAME` = '" . $table->TABLE_NAME . "'";
		
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$has_table = $stmt->fetchObject();
		//echo $sql . "<br />";
		//die(print_r($has_table));
		if ($has_table->table_count > 0) {
			//does it have a customer id
			$sql = "SELECT COUNT(*) col_count FROM INFORMATION_SCHEMA.COLUMNS 
			WHERE TABLE_SCHEMA = 'ikase' 
			AND TABLE_NAME = '" . $table->TABLE_NAME . "' 
			AND `COLUMN_NAME` = 'customer_id'";
			
			$stmt = $db->prepare($sql);
			$stmt->execute();
			$has_customer = $stmt->fetchObject();
			//print_r($has_customer);
			$sql = "TRUNCATE `ikase_" . $destination . "`.`" . $table->TABLE_NAME . "`";
			echo $sql . "<br />";
			//die($sql);
			$stmt = $db->prepare($sql);
			$stmt->execute();		
			
			if ($has_customer->col_count) {
				$sql = "INSERT INTO `ikase_" . $destination . "`.`" . $table->TABLE_NAME . "`
				SELECT * FROM `ikase`.`" . $table->TABLE_NAME . "` WHERE customer_id = '" . $destination_customer_id . "'";
			} else {
				$sql = "INSERT INTO `ikase_" . $destination . "`.`" . $table->TABLE_NAME . "`
				SELECT * FROM `ikase`.`" . $table->TABLE_NAME . "` WHERE 1";
			}
			echo $sql . "<br />";
			echo $table->TABLE_NAME . " done<br />";
			//die();
			$stmt = $db->prepare($sql);
			$stmt->execute();	
		}	
	}
	$success = array("success"=> array("text"=>"done"));
	echo json_encode($success);
} catch(PDOException $e) {
	echo "<br />ERR:<br />" . $sql . "<br />";
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
?>
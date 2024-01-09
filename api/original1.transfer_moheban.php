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
        WHERE `TABLE_SCHEMA` = 'ikase_moheban' 
		AND `TABLE_NAME` LIKE 'cse_%'
		AND `TABLE_NAME` NOT LIKE '%_track'
		AND `TABLE_NAME` NOT LIKE '%_old'
		AND `TABLE_NAME` NOT LIKE '%buffer'
		AND `TABLE_NAME` != 'cse_user'
		AND `TABLE_NAME` != 'cse_customer'";

try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$tables = $stmt->fetchAll(PDO::FETCH_OBJ);
	
	//die(print_r($tables));
	
	foreach($tables as $table) {
		//does the table exist in the destination?
		$sql = "SELECT * 
		FROM INFORMATION_SCHEMA.COLUMNS 
		WHERE TABLE_SCHEMA = 'ikase_moheban' 
		AND `TABLE_NAME` = '" . $table->TABLE_NAME . "'";
		
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$columns = $stmt->fetchAll(PDO::FETCH_OBJ);
		//echo $sql . "<br />";
		//die(print_r($has_table));
		$arrColumns = array();
		$blnUUID = false;
		foreach($columns as $cindex=>$column) {
			if ($cindex == 0) {
				continue;
			}
			$column_name = $column->COLUMN_NAME;
			if (strpos($column_name, "_uuid")!==false) {
				$blnUUID = true;
			}
			$arrColumns[] = $column_name;
		}
		
		if (!$blnUUID) {
			continue;
		}
		//die(print_r($arrColumns));
		$basic = str_replace("cse_", "", $table->TABLE_NAME);
		
		$sql = "INSERT INTO `ikase_moheban`.`" . $table->TABLE_NAME . "`";
		$sql .= "
		(`" . implode("`, `", $arrColumns) . "`)";
		$sql .= "
		SELECT aa.`" . implode("`, aa.`", $arrColumns) . "`
		FROM `az_moheban`.`" . $table->TABLE_NAME . "` aa
		LEFT OUTER JOIN `ikase_moheban`.`" . $table->TABLE_NAME . "` ia
		ON aa." . $basic . "_uuid = ia." . $basic . "_uuid
		WHERE ia." . $basic . "_uuid IS NULL;";
		
		echo $sql . "\r\n\r\n";
		
		//die();
		//$stmt = $db->prepare($sql);
		//$stmt->execute();	
		
		//echo $table->TABLE_NAME . " done<br />";
	}
	$success = array("success"=> array("text"=>"done"));
	echo json_encode($success);
	
	$stmt = null; $db = null;
} catch(PDOException $e) {
	echo "<br />ERR:<br />" . $sql . "<br />";
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
?>
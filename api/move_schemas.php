<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once('../shared/legacy_session.php');
//die(print_r($_SESSION));
if ($_SESSION['user_role']!="admin" && $_SESSION['user_role']!="owner") {
	die("no updates right now.");
}
include("connection.php");

$customer_id = passed_var("cus_id", "post");
$cus_id = $customer_id;

$db = getConnection();
//needed
include("customer_lookup.php");

if ($cus_id==1069) {
	$data_source = "dantin";
}
$sql = "SHOW TABLES FROM `ikase`";

try {
	$tables = DB::select($sql);
	//die(print_r($tables));
	$arrSQL = array();
	foreach($tables as $table) {
		//skip
		//echo $table->Tables_in_ikase . " -- " . (strpos($table->Tables_in_ikase, "cse") + 1) . "\r\n";
		if (strpos($table->Tables_in_ikase, "cse") === false) {
			continue;
		}
		$sql = "
		ALTER TABLE `ikase_" . $data_source . "`.`" . $table->Tables_in_ikase . "` AUTO_INCREMENT = 0;
		INSERT INTO `ikase_" . $data_source . "`.`" . $table->Tables_in_ikase . "` 
		SELECT * FROM ikase.`" . $table->Tables_in_ikase . "` 
		WHERE customer_id = '" . $cus_id . "'";
		//die($sql);	
		$arrSQL[] = $sql;
	}
	
	$sql = implode(";\r\n", $arrSQL);
	//die($sql);
	$stmt = DB::run($sql);
	
	$success = array("success"=> array("text"=>"done"));
	echo json_encode($success);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}

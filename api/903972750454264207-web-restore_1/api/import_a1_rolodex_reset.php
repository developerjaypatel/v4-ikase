<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include("manage_session.php");
set_time_limit(30000);

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;
$last_updated_date = date("Y-m-d H:i:s");

include("connection.php");
		
try {
	$db = getConnection();
	
	include("customer_lookup.php");
	
	$sql = "DELETE FROM `" . $data_source . "`.`" . $data_source . "_person`
	WHERE last_update_user = 'import'";
	//die($sql);
	$db = getConnection(); 
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$stmt = null; $db = null;
	
	$sql = "DELETE FROM `" . $data_source . "`.`" . $data_source . "_corporation`
	WHERE last_update_user = 'import'";
	$db = getConnection(); 
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$stmt = null; $db = null;
				
	$sql = "UPDATE `" . $data_source . "`.card
	SET ikase_table = '',
	ikase_uuid = ''
	WHERE ikase_uuid != ''";
	
	echo $sql . "\r\n<br>"; 		
	//die();
	$db = getConnection(); $stmt = $db->prepare($sql);  
	$stmt->execute();

	echo "done at " . date("H:i:s");
} catch(PDOException $e) {
	echo $sql . "\r\n<br>";
	$error = array("error"=> array("text"=>$e->getMessage()));
	die( json_encode($error));
}
?>
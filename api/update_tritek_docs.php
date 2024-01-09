<?php
require_once('../shared/legacy_session.php');
set_time_limit(3000);

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;

include("connection.php");

function getNickConnection() {
	//$dbhost="54.149.211.191";
$dbhost="52.24.207.176";
	$dbuser="root";
	$dbpass="admin527#";
	$dbname="ikase";
	
	$dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);            
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbh;
}
	

try {
	$db = getNickConnection();
	
	include("customer_lookup.php");
	
	$sql = "SELECT DISTINCT `TABLE_SCHEMA`, `TABLE_NAME`
	FROM `INFORMATION_SCHEMA`.`COLUMNS`
	WHERE `TABLE_SCHEMA` = '" . $data_source . "_docs'
	AND `TABLE_NAME` LIKE 'docs%'
	AND `TABLE_NAME` > 'docs39'
	ORDER BY `TABLE_NAME`";
	
	$tables = DB::select($sql);
	
	//die(print_r($tables));
	
	foreach($tables as $table) {
		$sql = "ALTER TABLE `" . $data_source . "_docs`.`" . $table->TABLE_NAME . "` 
		CHANGE COLUMN `DOCUMENT` `document` LONGBLOB NULL DEFAULT NULL ,
		ADD INDEX `cpointer` (`CPOINTER` ASC),
		ADD INDEX `recno` (`RECNO` ASC);";
		
		//echo $sql . "\r\n\r\n";
		$stmt = DB::run($sql);
	}
	$success = array("success"=> array("text"=>"done"));
	echo json_encode($success);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$finish_time = $time;
$total_time = round(($finish_time - $header_start_time), 4);

echo "Time spent:" . $total_time . "<br />";

<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once('../shared/legacy_session.php');
set_time_limit(3000);

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;
$last_updated_date = date("Y-m-d H:i:s");

include("connection.php");

try {
	include("customer_lookup.php");
	
	/*
	ALTER TABLE `perfect`.`docfolder` 
	CHARACTER SET = utf8 , COLLATE = utf8_unicode_ci ,
	CHANGE COLUMN `DF_ID` `DF_ID` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL ;
	*/
	
	$sql = "TRUNCATE `" . $data_source . "`.`" . $data_source . "_case`; ";
	$sql .= "TRUNCATE `" . $data_source . "`.`" . $data_source . "_case_injury`; ";
	$sql .= "TRUNCATE `" . $data_source . "`.`" . $data_source . "_injury`; ";
	$sql .= "TRUNCATE `" . $data_source . "`.`" . $data_source . "_document`; ";
	$sql .= "TRUNCATE `" . $data_source . "`.`" . $data_source . "_case_document`; ";
	$sql .= "TRUNCATE `" . $data_source . "`.`" . $data_source . "_person`; ";
	$sql .= "TRUNCATE `" . $data_source . "`.`" . $data_source . "_case_person`; ";
	$sql .= "TRUNCATE `" . $data_source . "`.`" . $data_source . "_corporation`; ";
	$sql .= "TRUNCATE `" . $data_source . "`.`" . $data_source . "_case_corporation`; ";
	
	$stmt = DB::run($sql);

    echo $customer_id." prepped";
}
catch (PDOException $e) {
    echo $sql."\r\n<br>";
    die(json_encode(["error" => ["text" => $e->getMessage()]]));
}

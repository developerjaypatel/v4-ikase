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
	$db = getConnection();
	
	include("customer_lookup.php");
	
	//maybe don't need it
	$sql = "SELECT COUNT(injury_id) injury_count
	FROM `ikase_" . $data_source . "`.`cse_injury`";
	$stmt = DB::run($sql);
	$injuries = $stmt->fetchObject();
	
	//die(print_r($injuries));
	$found = $injuries->injury_count;
	
	if ($found > 0) {
		die("injury table is already populated.");
	}
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_injury`;
	TRUNCATE `ikase_" . $data_source . "`.`cse_case_injury`";
	echo $sql . "\r\n\r\n<br>";
	//die();
	$stmt = DB::run($sql);
	
	$sql = "SELECT case_id, case_uuid 
	FROM `ikase_" . $data_source . "`.`cse_case`
	WHERE customer_id = '" . $customer_id . "'
	ORDER BY case_id ASC";
	echo $sql . "\r\n\r\n<br>";
	
	$cases = DB::select($sql);
	
	//die(print_r($cases));
	$found = count($cases);
	
	foreach($cases as $case_key=>$case){
		$injury_uuid = $case->case_uuid;
		$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_injury` 
		(`injury_uuid`, `injury_number`, `adj_number`, `start_date`, `end_date`, `type`, `occupation`, `body_parts`, `ct_dates_note`,
		`full_address`, `street`, `suite`, `city`, `state`, `zip`, `customer_id`, `explanation`, `deleted`)
		VALUES ('" . $injury_uuid . "', 1, '', '0000-00-00', '0000-00-00', '', '','','','', '','','', '', '', " . $customer_id . ", '', 'N')";
		echo $sql . "\r\n<br>"; 
		//die();
		$stmt = DB::run($sql);

		$case_table_uuid = $injury_uuid;
		$attribute_1 = "main";
		
		//now we have to attach the injury to the case 
		$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_injury` (`case_injury_uuid`, `case_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $case_table_uuid  ."', '" . $injury_uuid . "', '" . $injury_uuid . "', 'main', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
	
		echo $sql . "\r\n<br>";  
		$stmt = DB::run($sql);
	}
} catch(PDOException $e) {
	echo $sql . "\r\n<br>";
	$error = array("error"=> array("text"=>$e->getMessage()));
	die( json_encode($error));
}

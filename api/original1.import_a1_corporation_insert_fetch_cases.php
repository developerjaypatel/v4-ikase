<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include("manage_session.php");
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
	$data_source = str_replace("2", "", $data_source);
	
	$sql = "SELECT ggc.case_number CASENO
	FROM `" . $GLOBALS['GEN_DB_NAME'] . "`.`case` gcase
	LEFT OUTER JOIN `ikase_" . $data_source . "`.`cse_case` ggc
	ON gcase.CASENO = ggc.cpointer
	LEFT OUTER JOIN `ikase_" . $data_source . "`.`cse_case_corporation` ccorp
	ON ggc.case_uuid = ccorp.case_uuid
	WHERE 1
	AND ccorp.case_corporation_id IS NULL
	ORDER BY ggc.case_number DESC";
	// echo $sql . "\r\n<br>";
	$db = getConnection(); $stmt = $db->prepare($sql);
	$stmt->execute();
	$cases = $stmt->fetchAll(PDO::FETCH_OBJ); $stmt->closeCursor(); $stmt = null; $db = null;
	
	// die(print_r($cases));
    $found = count($cases);
    echo $found;
    $db = null;
} catch(PDOException $e) {
	echo $sql . "\r\n<br>";
	$error = array("error"=> array("text"=>$e->getMessage()));
	die( json_encode($error));
}
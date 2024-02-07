<?php
set_time_limit(12000);
ini_set('memory_limit','256M');

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', '1');

include("connection.php");

$data_source = passed_var("data_source", "get");
$customer_id = passed_var("customer_id", "get");
$blnInitial = (isset($_GET["init"]));

if (!is_numeric($customer_id)) {
	die("no id");
}
$dir = "F:\\" . $data_source  . "\\";

try{
	$sql = "
	
	UPDATE `" . $data_source . "`.`cases`
	SET processed = 'N';

	TRUNCATE `" . $data_source . "`.`" . $data_source . "_case_message`;
	
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_thread`;
	
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_thread_message`;
	
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_message`;
	
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_message_user`;
    
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_contact`;
	";
	
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$stmt = null; $db = null;
	
	die("done");
	
} catch (PDOException $e) {
	echo $e->getMessage();
	die("
	ERROR:
	$sql");
	$error = array("error"=> array("text"=>$e->getMessage(), "sql"=>$sql, "error"=>$arrErrorCatch));
	echo json_encode($error);
}
<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include("manage_session.php");
set_time_limit(3000);

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;

include("connection.php");

function getNickConnection() {
	//$dbhost="54.149.211.191";
	$dbhost="ikase.org";
	$dbuser="root";
	$dbpass="admin527#";
	$dbname="ikase";
	
	$dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);            
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbh;
}

$customer_id = passed_var("customer_id", "get");

$db = getConnection();
include("customer_lookup.php");

try {
	$db = getNickConnection();
	$sql = "
	UPDATE `" . $data_source . "`.`cases` SET processed = 'N';
	
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_case`;
	
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_case_corporation`;
	
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_case_injury`;
	
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_corporation`;
	
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_corporation_adhoc`;
	
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_injury`;
	
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_activity`;
	
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_case_notes`;
	
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_notes`;
	
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_case_task`;
	
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_task`;
	
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_task_user`;
	
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_case_event`;
	
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_event`;
	
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_event_user`;
	
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_case_message`;
	
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_thread`;
	
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_thread_message`;
	
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_message`;
	
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_message_user`;
	
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_person`;
	
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_case_person`;
	
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_document`;
	
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_case_document`;";

	
	//die($sql);
	
	$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	$stmt->execute();
	
	die("done");
} catch(PDOException $e) {
	echo "<br />" . $sql . "<br />";
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
	die();
}		

<?php
function getConnection() {
	$dbhost="54.149.211.191";
	$dbuser="root";
	$dbpass="admin527#";
	$dbname="ikase";
	$dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);	
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbh;
}

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$performance_start_time = $time;

$db = getConnection();
$sql = "SELECT * FROM `cse_user`";	
$sql .= " WHERE 1";

$sql2 = "SELECT * FROM `cse_eams_reps`";	
$sql2 .= " WHERE 1";

try {
	
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$users = $stmt->fetchAll(PDO::FETCH_OBJ);
	
	
	$stmt = $db->prepare($sql2);
	$stmt->execute();
	$reps = $stmt->fetchAll(PDO::FETCH_OBJ);
	
	/*
	foreach($users as $user){
		echo $user->user_name . "<br />";
	}
	*/
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
$db = null;

if ($performance_start_time!="") {
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $performance_start_time), 4);
	echo '<div style="font-size:1.2em; color:black; font-family:Arial">AWS generated in '.$total_time.' seconds.'."</div>";
}
die();
?>
<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
set_time_limit(3000);
die();
include("manage_session.php");
//die(print_r($_SESSION));
if ($_SESSION['user_role']!="admin" && $_SESSION['user_role']!="owner") {
	die("no updates right now.");
}

$schema_name = $_GET["sc"];
$table_name = $_GET["tb"];
$inc = $_GET["inc"];
$prev = $_GET["prev"];

include("connection.php");

$sql = "ALTER TABLE `" . $schema_name . "`.`" . $table_name . "` AUTO_INCREMENT = $inc;";
			
//die($sql);
//die($table_name . " -> inc -> " . $info->increment);

$db = getConnection();
$stmt = $db->prepare($sql);
$stmt->execute();
$stmt = null; $db = null;

echo  "`" . $schema_name . "`.`" . $table_name . "`	increment changed from " . $prev . " to " . $inc . "<br />"; 
?>
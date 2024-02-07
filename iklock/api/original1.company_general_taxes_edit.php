<?php 
error_reporting(E_ALL);
ini_set('display_errors', '1');	

include("manage_session.php");

if (!isset($_SESSION["user_customer_id"])) {
	die("no No NO");
}
date_default_timezone_set('America/Los_Angeles');

include("connection.php");

echo "edit taxes";

?>
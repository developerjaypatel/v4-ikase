<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');	

include("../../api/connection.php");
include_once "templates/base.php";

//die($_SERVER['PHP_SELF']);
include("../../api/manage_session.php");

if (isset($_SESSION['access_token'])) {
	echo json_encode(array("access_token"=>$_SESSION['access_token']));
} else {
	echo json_encode(array("access_token"=>""));
}
?>
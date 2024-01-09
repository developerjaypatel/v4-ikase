<?php 
require_once('shared/legacy_session.php');
session_write_close();
error_reporting(0);

if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
	header("location:index.html");
	die();
}
echo 123123;
die;
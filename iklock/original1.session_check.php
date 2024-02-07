<?php

if (!isset($_SESSION["user"])) {
	header("location:index.php?nose");
	die();
}
if ($_SESSION["user"]=="") {
	header("location:index.php?ose");
	die();
}
//security
$sid = $_SESSION["user"];
$USERNAME = $_SESSION["user_logon"];

//die(print_r($_SESSION));
//now get t
//the list of personnel notification users
$arrNotified = array();
$to_user = new systemuser();
$resultusers = $to_user->search_notification("notification_personnel");
//$numberusers = mysql_numrows($resultusers);

//for ($intU=0;$intU<$numberusers;$intU++) {
foreach($resultusers as $user) {
	$to_user_id = $user->user_id;
	$to_user_uuid = $user->user_uuid;
	$to_user_logon = $user->user_logon;		
	
	$arrNotified[] = $to_user_logon;
}

$USERNAME = $_SESSION["user_logon"];
$my_user = new systemuser();
$my_user->user_logon = $USERNAME;
$my_user->fetchuser();

include("user_timecard_late.php");
?>
<?php
include("../eamsjetfiler/datacon.php");
include("../eamsjetfiler/functions.php");

$cus_id = passed_var("cus_id");
//die("here");
include("../logon_check.php");

$user_id = passed_var("user_id");
$user_name = passed_var("user_name");

$user_logon = passed_var("user_logon");
$user_first_name = passed_var("user_first_name");
$user_last_name = passed_var("user_last_name");

//die("cus:" . $user_name);
$user_email = passed_var("user_email");
$password = passed_var("password");
$pwd = "";
if ($password!="") {
	$pwd = encrypt($password, $crypt_key);
}

$arrDOW = array();
for ($int=1;$int<7;$int++) {
	$arrDOW[] = passed_var("days_of_week" . $int);
	$arrDOWTimes[] = passed_var("dow_times" . $int);
}
$days_of_week = implode("|", $arrDOW);
$dow_times = implode("|", $arrDOWTimes);

if ($user_logon=="") {
	$user_logon = $user_email;
}
$level = passed_var("level");
$job = passed_var("job");
$day_start = passed_var("start_time");
$day_end = passed_var("end_time");
if ($level=="admin") {
	$day_start = "00:01AM";
	$day_end = "11:59PM";
}
if ($user_name=="") {
	$user_name = $user_first_name . " " . $user_last_name;
}
$query = "";
if ($user_id=="") {
	$uuid = uniqid("TS");
	$query = "INSERT INTO tbl_user (`user_uuid`, `cus_id`, `user_name`,`user_email`, `password`, `pwd`, `level`, `job`, `day_start`, `day_end`, `days_of_week`, `dow_times`, `user_logon`, `user_first_name`, `user_last_name`)
	VALUES ('" . $uuid . "','" . $cus_id . "', '" . addslashes($user_name) . "', '" . addslashes($user_email) . "', '', '" . addslashes($pwd) . "','" . addslashes($level) . "','" . addslashes($job) . "','" . addslashes($day_start) . "','" . addslashes($day_end) . "','" . addslashes($days_of_week) . "','" . addslashes($dow_times) . "','" . addslashes($user_logon) . "','" . addslashes($user_first_name) . "','" . addslashes($user_last_name) . "')";
} else {
	$query = "UPDATE tbl_user
	SET user_name = '" . addslashes($user_name) . "',
	user_email= '" . addslashes($user_email) . "',";
	if($pwd!="") {
		$query .= "pwd = '" . addslashes($pwd) . "',";
	}
	$query .= "level = '" . addslashes($level) . "',
	job = '" . addslashes($job) . "',
	day_start = '" . addslashes($day_start) . "',
	day_end = '" . addslashes($day_end) . "',
	days_of_week = '" . addslashes($days_of_week) . "',
	dow_times = '" . addslashes($dow_times) . "',
	user_logon= '" . addslashes($user_logon) . "',
	user_first_name= '" . addslashes($user_first_name) . "',
	user_last_name= '" . addslashes($user_last_name) . "'
	WHERE user_id = '" . $user_id . "'
	AND cus_id = '" . $cus_id . "'";
}
$result = mysql_query($query, $r_link) or die("unable to update user<br>" . $query . "<br />" . mysql_error());
if ($ip_address=="72.87.128.38") {
	//die($query);
}
header("location:index.php?cus_id=" . $cus_id . "&suid=" . $suid);
?>
<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once('../../../shared/legacy_session.php');
session_write_close();

include ("../../api/connection.php");

$cus_id = passed_var("cus_id", "post");
if(!is_numeric($cus_id)) {
	die("oh no");
}

$user_id = passed_var("user_id", "post");
if ($user_id!="") {
	if(!is_numeric($cus_id)) {
		die("no no");
	}
}
$user_name = passed_var("user_name", "post");

$user_logon = passed_var("user_logon", "post");
$user_first_name = passed_var("user_first_name", "post");
$user_last_name = passed_var("user_last_name", "post");

//die("cus:" . $user_name);
$user_email = passed_var("user_email", "post");
$nickname = passed_var("nickname", "post");
$password = passed_var("password", "post");
$pwd = "";
if ($password!="") {
	$pwd = encrypt($password, CRYPT_KEY);
}

$arrDOW = array();
for ($int=1;$int<7;$int++) {
	$arrDOW[] = passed_var("days_of_week" . $int, "post");
	$arrDOWTimes[] = passed_var("dow_times" . $int, "post");
}
$days_of_week = implode("|", $arrDOW);
$dow_times = implode("|", $arrDOWTimes);

if ($user_logon=="") {
	$user_logon = $user_email;
}
$level = passed_var("level", "post");
$user_type = $level;
if ($level == 1) {
	$level = "admin";
}
if ($level == 2) {
	$level = "User";
}
if ($level == 3) {
	$level = "masteradmin";
}
$job = passed_var("job", "post");
$day_start = passed_var("start_time", "post");
$day_end = passed_var("end_time", "post");
if ($level=="admin" || $level=="masteradmin") {
	$day_start = "00:01AM";
	$day_end = "11:59PM";
}
$clock_in_time = str_replace("AM", ":00", $day_start);
$clock_out_time = str_replace("AM", ":00", $day_end);
$clock_in_time = str_replace("PM", ":00PM", $clock_in_time);
$clock_out_time = str_replace("PM", ":00PM", $clock_out_time);

//get rid of the pms
if (strpos($clock_in_time, "PM") !==false) {
	$clock_in_time = str_replace("PM", "", $clock_in_time);
	$arrTime = explode(":", $clock_in_time);
	$arrTime[0] = $arrTime[0] + 12;
	$clock_in_time = implode(":", $arrTime);
}
if (strpos($clock_out_time, "PM") !==false) {
	$clock_out_time = str_replace("PM", "", $clock_out_time);
	$arrTime = explode(":", $clock_out_time);
	$arrTime[0] = $arrTime[0] + 12;
	$clock_out_time = implode(":", $arrTime);
}
$user_name = $user_first_name . " " . $user_last_name;

$query = "";
if ($user_id=="") {
	$uuid = uniqid("TS");
	$query = "INSERT INTO `iklock`.`user` (`zip_list`, `user_groups`, `user_uuid`, `customer_id`, `user_name`,`user_email`, `nickname`, `pwd`, `level`, `job`, `day_start`, `day_end`, `days_of_week`, `dow_times`, `user_logon`, `user_first_name`, `user_last_name`, `user_type`, `clock_in_time`, `clock_out_time`)
	VALUES ('', '', '" . $uuid . "','" . $cus_id . "', '" . addslashes($user_name) . "', '" . addslashes($user_email) . "', '" . addslashes($nickname) . "', '" . addslashes($pwd) . "','" . addslashes($level) . "','" . addslashes($job) . "','" . addslashes($day_start) . "','" . addslashes($day_end) . "','" . addslashes($days_of_week) . "','" . addslashes($dow_times) . "','" . addslashes($user_logon) . "','" . addslashes($user_first_name) . "','" . addslashes($user_last_name) . "', " . $user_type . ", '" . $clock_in_time . "', '" . $clock_out_time . "')";
} else {
	$query = "UPDATE `iklock`.`user`
	SET user_name = '" . addslashes($user_name) . "',
	user_email= '" . addslashes($user_email) . "',
	nickname= '" . addslashes($nickname) . "',";
	if($pwd!="") {
		$query .= "pwd = '" . addslashes($pwd) . "',";
	}
	$query .= "`level` = '" . addslashes($level) . "',
	`user_type` = " . $user_type . ",
	`job` = '" . addslashes($job) . "',
	day_start = '" . addslashes($day_start) . "',
	day_end = '" . addslashes($day_end) . "',
	days_of_week = '" . addslashes($days_of_week) . "',
	dow_times = '" . addslashes($dow_times) . "',
	clock_in_time = '" . $clock_in_time . "',
	clock_out_time = '" . $clock_out_time . "',
	user_logon= '" . addslashes($user_logon) . "',
	user_first_name= '" . addslashes($user_first_name) . "',
	user_last_name= '" . addslashes($user_last_name) . "'
	WHERE `user_id` = " . $user_id . "
	AND `customer_id` = " . $cus_id;
}
//$result = DB::runOrDie($query);
//die($query);

try {
	$stmt = DB::run($query);
} catch(PDOException $e) {
	echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	die();
}

header("location:index.php?cus_id=" . $cus_id . "&suid=" . $suid);
?>

<?php
require_once('../shared/legacy_session.php');
set_time_limit(3000);

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;

include("connection.php");

try {
	$db = getConnection();
	
	include("customer_lookup.php");
	
	$sql_truncate = "DELETE FROM `ikase`.`cse_user` 
	WHERE `level` != 'masteradmin'
	AND customer_id = " . $customer_id;
	
	echo $sql_truncate . "\r\n\r\n";
	$stmt = DB::run($sql_truncate);

	$sql_truncate = "DELETE FROM `ikase`.`cse_user_job` 
	WHERE customer_id = " . $customer_id;
	
	echo $sql_truncate . "\r\n\r\n";
	$stmt = DB::run($sql_truncate);
	
	$sql_truncate = "TRUNCATE `ikase_".$data_source."`.`cse_job` ";
	
	echo $sql_truncate . "\r\n\r\n";
	$stmt = DB::run($sql_truncate);
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$row_start_time = $time;
	// $sql =  "INSERT INTO `ikase`.`cse_job` (`job_uuid`, `job`, `blurb`, `color`)";
	// $sql .= " SELECT DISTINCT `TITLE`, `TITLE`, LOWER(REPLACE(`TITLE`, ' ', '_')) blurb, '' color
	// FROM `" . $GLOBALS['GEN_DB_NAME'] . "`.`staff`
	// WHERE `TITLE` IS NOT NULL AND `TITLE` COLLATE utf8_unicode_ci NOT IN (SELECT job_uuid COLLATE utf8_unicode_ci FROM `ikase`.`cse_job`)";
	$sql="INSERT INTO `ikase`.`cse_job` (`job_uuid`, `job`, `blurb`, `color`)
SELECT DISTINCT 
    s.`TITLE`,
    s.`TITLE`,
    LOWER(REPLACE(s.`TITLE`, ' ', '_')) AS blurb,
    '' AS color
FROM `".$GLOBALS['GEN_DB_NAME']."`.`staff` s
WHERE s.`TITLE` IS NOT NULL
  AND s.`TITLE` COLLATE utf8_unicode_ci NOT IN (
      SELECT j.job_uuid COLLATE utf8_unicode_ci
      FROM `ikase`.`cse_job` j
      WHERE j.job_uuid IS NOT NULL
  )
  AND LOWER(REPLACE(s.`TITLE`, ' ', '_')) COLLATE utf8_unicode_ci NOT IN (
      SELECT j.blurb COLLATE utf8_unicode_ci
      FROM `ikase`.`cse_job` j
      WHERE j.blurb IS NOT NULL
  );";
	
	echo $sql . "\r\n\r\n";
	$stmt = DB::run($sql);
	
	// $sql = "
	// ALTER TABLE `ikase_" . $data_source . "`.`cse_user` 
	// CHANGE COLUMN `nickname` `nickname` VARCHAR(255) NULL DEFAULT NULL ;
	// ALTER TABLE `ikase_" . $data_source . "`.`cse_user` 
	// CHANGE COLUMN `user_name` `user_name` VARCHAR(255) NULL DEFAULT NULL ;
	// ALTER TABLE `ikase_" . $data_source . "`.`cse_user` 
	// CHANGE COLUMN `user_first_name` `user_first_name` VARCHAR(255) NULL DEFAULT NULL ;
	// ALTER TABLE `ikase_" . $data_source . "`.`cse_user` 
	// CHANGE COLUMN `user_last_name` `user_last_name` VARCHAR(255) NULL DEFAULT NULL ;
	// INSERT INTO `ikase_" . $data_source . "`.`cse_user` (
	// `user_uuid`,
	// `customer_id`,
	// `user_name`,
	// `user_first_name`,
	// `user_last_name`,
	// `user_logon`,
	// `nickname`,
	// `pwd`, `job`)
	// SELECT CONCAT(`INITIALS`, '_', '" . $customer_id . "') user_uuid,
	// '" . $customer_id . "' customer_id, 
	// CONCAT(`FNAME`, ' ',`LNAME`) `user_name`, 
	// `FNAME` `user_first_name`, `LNAME` `user_last_name`,
	// `USERNAME` user_logon, 
	// `INITIALS` `nickname`, 
	// '' pwd, IFNULL(`TITLE`, '') `job`
	// FROM `" . $GLOBALS['GEN_DB_NAME'] . "`.`staff`
	// WHERE `INITIALS` IS NOT NULL";

	$sql = "
	ALTER TABLE `ikase`.`cse_user` 
	CHANGE COLUMN `nickname` `nickname` VARCHAR(255) NULL DEFAULT NULL ;
	ALTER TABLE `ikase`.`cse_user` 
	CHANGE COLUMN `user_name` `user_name` VARCHAR(255) NULL DEFAULT NULL ;
	ALTER TABLE `ikase`.`cse_user` 
	CHANGE COLUMN `user_first_name` `user_first_name` VARCHAR(255) NULL DEFAULT NULL ;
	ALTER TABLE `ikase`.`cse_user` 
	CHANGE COLUMN `user_last_name` `user_last_name` VARCHAR(255) NULL DEFAULT NULL ;
	INSERT INTO `ikase`.`cse_user` (
	`user_uuid`,
	`customer_id`,
	`user_name`,
	`user_first_name`,
	`user_last_name`,
	`user_logon`,
	`nickname`,
	`pwd`, `job`, `level`)
	SELECT CONCAT(`INITIALS`, '_', '" . $customer_id . "') user_uuid,
	'" . $customer_id . "' customer_id, 
	CONCAT(`FNAME`, ' ',`LNAME`) `user_name`, 
	`FNAME` `user_first_name`, `LNAME` `user_last_name`,
	`USERNAME` user_logon, 
	`INITIALS` `nickname`, 
	'' pwd, IFNULL(`TITLE`, '') `job`, IFNULL(`admin`, '') `level`
	FROM `" . $GLOBALS['GEN_DB_NAME'] . "`.`staff`
	WHERE `INITIALS` IS NOT NULL";
	
	echo $sql . "\r\n\r\n";
	$stmt = DB::run($sql);
	
	$sql="UPDATE `ikase`.`cse_user`
	SET `level` = 'User', user_type = '2'
	WHERE `level` = '0' AND customer_id = " . $customer_id;
	$stmt = DB::run($sql);

	$sql="UPDATE `ikase`.`cse_user`
	SET `level` = 'admin', user_type = '1'
	WHERE `level` = '1' AND customer_id = " . $customer_id;
	$stmt = DB::run($sql);

	// BEFORE 
	// $sql = "INSERT INTO `ikase`.`cse_user_job` (
	// `user_job_uuid`, `user_uuid`, `job_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
	// SELECT user_uuid, user_uuid, job, 'main', '" . date("Y-m-d H:i:s") . "', 'system', '" . $customer_id . "'
	// FROM `ikase`.`cse_user`";

	// UPDATED BECAUSE DUPLICATE user_job_uuid - 11 March 2021
	$sql = "INSERT INTO `ikase`.`cse_user_job` (
	`user_job_uuid`, `user_uuid`, `job_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
	SELECT CONCAT(`user_uuid`,`user_id`), user_uuid, job, 'main', '" . date("Y-m-d H:i:s") . "', 'system', '" . $customer_id . "'
	FROM `ikase`.`cse_user` where customer_id = '".$customer_id."'";
	
	echo $sql . "\r\n\r\n";
	$stmt = DB::run($sql);
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $row_start_time), 4);
	echo "Time spent:" . $total_time . "\r\n\r\n";

	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	$success = array("success"=> array("text"=>"done", "duration"=>$total_time));
	echo json_encode($success);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
include("cls_logging.php");
?>
<script language="javascript">
parent.setFeedback("users import completed");
</script>

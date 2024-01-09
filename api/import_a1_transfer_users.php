<?php
require_once('../shared/legacy_session.php');

include("connection.php");
$customer_id = passed_var("customer_id", "get");
if (!is_numeric($customer_id)) {
	die();
}

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	$sql = "DELETE FROM `ikase`.`cse_user` 
	WHERE customer_id = " . $customer_id . "
	AND level != 'masteradmin'";
	//die($sql);
	$stmt = DB::run($sql);
	
	$sql = "DELETE FROM `ikase_" . $data_source . "`.`cse_user` 
	WHERE customer_id = " . $customer_id . "
	AND level != 'masteradmin'";
	$stmt = DB::run($sql);
	
	$sql = "DELETE FROM `ikase`.`cse_user_job`
	WHERE customer_id = " . $customer_id;
	$stmt = DB::run($sql);
	
	$sql = "DELETE FROM `ikase_" . $data_source . "`.`cse_user_job`
	WHERE customer_id = " . $customer_id;
	$stmt = DB::run($sql);
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_job`";
	$stmt = DB::run($sql);
	
	$sql =  "INSERT INTO `ikase_" . $data_source . "`.`cse_job` 
	(`job_uuid`, `job`, `blurb`, `color`)
	SELECT `job_uuid`, `job`, `blurb`, `color` 
	FROM `" . $data_source . "`.`" . $data_source . "_job`";
	
	echo $sql . "<br />";
	
	$stmt = DB::run($sql);
	
	//no duplicate blurbs
	$sql = "UPDATE ikase_" . $data_source . ".cse_job djob, ikase.cse_job ijob
	SET djob.job_uuid = ijob.job_uuid
	WHERE djob.blurb = ijob.blurb";
	$stmt = DB::run($sql);
	
	//missing jobs
	$sql = "INSERT INTO ikase.cse_job (job_uuid, job, blurb, color)
	SELECT job_uuid, job, blurb, color 
	FROM ikase_" . $data_source . ".cse_job 
	WHERE job_uuid NOT IN (SELECT job_uuid FROM ikase.cse_job)";
	$stmt = DB::run($sql);
	
	//, `mru_number`
	$sql = "INSERT INTO `ikase`.`cse_user` (`user_uuid`, `customer_id`, `cis_id`, `cis_uid`, `user_type`, `user_name`, `user_logon`, `user_first_name`, `user_last_name`, `user_email`, `nickname`, `pwd`, 
	`level`, `job`, `status`, `personal_calendar`, `day_start`, `day_end`, `days_of_week`, `dow_times`, `sess_id`, `dateandtime`, `ip_address`, `deleted`)
	SELECT `user_uuid`, `customer_id`, `cis_id`, `cis_uid`, IF(`job`='Attorney', '1', '2') `user_type`, `user_name`, `user_logon`, `user_first_name`, `user_last_name`, `user_email`, `nickname`, `pwd`, IF(`job`='Attorney', 'admin', 'user') `level`, `job`, `status`, `personal_calendar`, `day_start`, `day_end`, `days_of_week`, `dow_times`, `sess_id`, `dateandtime`, `ip_address`, `deleted` 
	FROM `" . $data_source . "`.`" . $data_source . "_user` 
	WHERE 1 AND level != 'masteradmin' AND customer_id = " . $customer_id;
	echo $sql . "<br />";
	$stmt = DB::run($sql);
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_user` (`user_uuid`, `customer_id`, `cis_id`, `cis_uid`, `user_type`, `user_name`, `user_logon`, `user_first_name`, `user_last_name`, `user_email`, `nickname`, `pwd`, `level`, `job`, `status`, `personal_calendar`, `day_start`, `day_end`, `days_of_week`, `dow_times`, `sess_id`, `dateandtime`, `ip_address`, `deleted`)
	SELECT `user_uuid`, `customer_id`, `cis_id`, `cis_uid`, IF(`job`='Attorney', '1', '2') user_type, `user_name`, `user_logon`, `user_first_name`, `user_last_name`, `user_email`, `nickname`, `pwd`, IF(`job`='Attorney', 'admin', 'user')  `level`, `job`, `status`, `personal_calendar`, 
	`day_start`, `day_end`, `days_of_week`, `dow_times`, `sess_id`, `dateandtime`, `ip_address`, `deleted` 
	FROM `" . $data_source . "`.`" . $data_source . "_user` 
	WHERE 1 AND level != 'masteradmin' AND customer_id = " . $customer_id;
	
	echo $sql . "<br />";
	
	$stmt = DB::run($sql);
	
	$sql = "INSERT INTO `ikase`.`cse_user_job` (user_job_uuid, user_uuid, job_uuid, last_updated_date, last_update_user, `customer_id`, `attribute`)
	SELECT user_job_uuid, user_uuid, job_uuid, last_updated_date, last_update_user, `customer_id`, `attribute` 
	FROM `" . $data_source . "`.`" . $data_source . "_user_job` ";
	
	echo $sql . "<br />";
	
	$stmt = DB::run($sql);
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_user_job` (user_job_uuid, user_uuid, job_uuid, last_updated_date, last_update_user, `customer_id`, `attribute`)
	SELECT user_job_uuid, user_uuid, job_uuid, last_updated_date, last_update_user, `customer_id`, `attribute` 
	FROM `" . $data_source . "`.`" . $data_source . "_user_job` ";
	
	echo $sql . "<br />";
	
	$stmt = DB::run($sql);
	
	$success = array("success"=> array("text"=>"done"));
	echo json_encode($success);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}	

include("cls_logging.php");
?>
<script language="javascript">
parent.setFeedback("users transfer completed");
</script>

<?php
require_once('../shared/legacy_session.php');

include("connection.php");

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	$last_updated_date = date("Y-m-d H:i:s");
	$last_update_user = "system";
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_notes`;
	TRUNCATE `ikase_" . $data_source . "`.`cse_case_notes`";
	echo $sql . "\r\n\r\n";
	$stmt = DB::run($sql);
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.cse_notes (`notes_uuid`, `note`, `type`, `dateandtime`, `entered_by`, `customer_id`)
	SELECT activity_uuid, activity, 'quick', activity_date, timekeeper, `customer_id`
	FROM `ikase_" . $data_source . "`.cse_activity ca
	WHERE `activity_category` = 'REDFLAG' ";
	
	echo $sql . "\r\n\r\n";
	$stmt = DB::run($sql);
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.cse_case_notes (`case_notes_uuid`, `case_uuid`, `notes_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
	SELECT ca.activity_uuid, cca.case_uuid, ca.activity_uuid, 'quick', '" . $last_updated_date . "', '" . $last_update_user . "', ca.`customer_id`
	FROM `ikase_" . $data_source . "`.cse_activity ca
	INNER JOIN `ikase_" . $data_source . "`.cse_case_activity cca
	ON ca.activity_uuid = cca.activity_uuid
	WHERE ca.`activity_category` = 'REDFLAG'";
	
	echo $sql . "\r\n\r\n";
	$stmt = DB::run($sql);
	
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.cse_notes (`notes_uuid`, `note`, `type`, `dateandtime`, `entered_by`, `customer_id`)
	SELECT activity_uuid, activity, 'standard', activity_date, timekeeper, `customer_id`
	FROM `ikase_" . $data_source . "`.cse_activity ca
	INNER JOIN (
	SELECT cca.case_uuid, MIN(activity_id) activity_id 
	FROM `ikase_" . $data_source . "`.cse_activity ca
	INNER JOIN `ikase_" . $data_source . "`.cse_case_activity cca
	ON ca.activity_uuid = cca.activity_uuid
	WHERE activity != 'Applicant Entered Into Computer'
	AND activity NOT LIKE 'File Accessed%'
	GROUP BY cca.case_uuid) min_act
	ON ca.activity_id = min_act.activity_id
	WHERE activity_category != 'REDFLAG'";
	
	echo $sql . "\r\n\r\n";
	$stmt = DB::run($sql);
	
	
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.cse_case_notes (`case_notes_uuid`, `case_uuid`, `notes_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
	SELECT activity_uuid, case_uuid, activity_uuid, 'standard', '" . $last_updated_date . "', '" . $last_update_user . "', `customer_id`
	FROM `ikase_" . $data_source . "`.cse_activity ca
	INNER JOIN (
	SELECT cca.case_uuid, MIN(activity_id) activity_id 
	FROM `ikase_" . $data_source . "`.cse_activity ca
	INNER JOIN `ikase_" . $data_source . "`.cse_case_activity cca
	ON ca.activity_uuid = cca.activity_uuid
	WHERE activity != 'Applicant Entered Into Computer'
	AND activity NOT LIKE 'File Accessed%'
	GROUP BY cca.case_uuid) min_act
	ON ca.activity_id = min_act.activity_id
	WHERE activity_category != 'REDFLAG'";
	
	echo $sql . "\r\n\r\n";
	$stmt = DB::run($sql);
	
	$success = array("success"=> array("text"=>"quicknotes done"));
	echo json_encode($success);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}	
include("cls_logging.php");
?>
<script language="javascript">
parent.setFeedback("activity transfer completed");
</script>

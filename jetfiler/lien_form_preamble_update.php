<?php
include("eamsjetfiler/datacon.php");
include("eamsjetfiler/functions.php");
include ("classes/cls_tracker.php");

$cus_id = passed_var("cus_id");
include("logon_check.php");


$lien_id = passed_var("lien_id");
$client_id = passed_var("client_id");
$case_id = passed_var("case_id");

$original_lien_date = sqlDates(passed_var("original_lien_date"));

$lien_type = passed_var("lien_type");
$exempt = passed_var("exempt");
if ($exempt!="Y") {
	$exempt = "N";
}
$exempt_signature = passed_var("exempt_signature");
$lien_organization = passed_var("lien_organization");
$lien_first_name = passed_var("lien_first_name");
$lien_last_name = passed_var("lien_last_name");
$lien_street = passed_var("lien_street");
$lien_city = passed_var("lien_city");
$lien_state = passed_var("lien_state");
$lien_zip = passed_var("lien_zip");
$lien_phone = passed_var("lien_phone");
if ($lien_phone=="(___) ___-____" || $lien_phone=="(###) ###-####") {
	$lien_phone = "";
}

$attorney = passed_var("attorney_name");
$attorney_eams_no = passed_var("attorney_eams_number");
$attorney_street = passed_var("attorney_street");
$attorney_city = passed_var("attorney_city");
$attorney_state = passed_var("attorney_state");
$attorney_zip = passed_var("attorney_zip");
//die("lien_phone:" . $lien_phone);

$claimant_representative = passed_var("claimant_representative");

$lien_attorney = passed_var("lien_attorney_name");
$lien_attorney_first_name = passed_var("lien_attorney_first_name");
$lien_attorney_last_name = passed_var("lien_attorney_last_name");

$lien_attorney_eams_no = passed_var("lien_attorney_eams_number");
$lien_attorney_street = passed_var("lien_attorney_street");
$lien_attorney_city = passed_var("lien_attorney_city");
$lien_attorney_state = passed_var("lien_attorney_state");
$lien_attorney_zip = passed_var("lien_attorney_zip");
$lien_attorney_phone = passed_var("lien_attorney_phone");

$lien_sum = passed_var("lien_sum");
$lien_sum = str_replace(",", "", $lien_sum);
$lien_reason = passed_var("lien_reason");
$lien_reason_other_text = passed_var("lien_reason_other_text");
$interpreter_date = passed_var("interpreter_date");
$interpreter_date = sqlDates(("interpreter_date"));
$interpreter_date = sqlDates(passed_var("interpreter_date"));

$lien_copy = passed_var("lien_copy");
if ($lien_copy!="Y") {
	$lien_copy = "N";
}
if ($lien_id=="") {	
	$dow = date("w");
	$doy = date("z");
	if (strlen($doy) == 1) {
		$doy = "00" . $doy;
	}
	if (strlen($doy) == 2) {
		$doy = "0" . $doy;
	}
	$week = date("W");
	if (strlen($week) == 1) {
		$doy = "0" . $week;
	}
	$month_days = date("t");

	//external transaction id
	$external_transaction_id = $admin_client . date("ymdHis");	
	
	$query = "INSERT INTO tbl_lien (`case_id`, `external_transaction_id`, `lien_type`, `original_lien_date`, `lien_organization`, `lien_first_name`, `lien_last_name`, `lien_street`, `lien_city`, `lien_state`, `lien_zip`, `attorney_eams_no`, `attorney`, `attorney_street`, `attorney_city`, `attorney_state`, `attorney_zip`, `lien_phone`, `claimant_representative`, `lien_attorney_eams_no`, `lien_attorney`, `lien_attorney_first_name`, `lien_attorney_last_name`, `lien_attorney_street`, `lien_attorney_city`, `lien_attorney_state`, `lien_attorney_zip`, `lien_attorney_phone`, `lien_sum`, `lien_reason`, `interpreter_date`, `lien_reason_other_text`, `lien_copy`, `lien_signature`, `exempt`, `exempt_signature`) ";
	$query .=  "VALUES (";
	$query .=  "'" . $case_id . "', ";
	$query .=  "'" . $external_transaction_id . "', ";
	$query .=  "'" . $lien_type . "', ";
	$query .=  "'" . $original_lien_date . "', ";
	$query .= "'" . addslashes($lien_organization) . "', ";
	$query .= "'" . addslashes($lien_first_name) . "', ";
	$query .= "'" . addslashes($lien_last_name) . "', ";
	$query .= "'" . addslashes($lien_street) . "', ";
	$query .= "'" . addslashes($lien_city) . "', ";
	$query .= "'" . addslashes($lien_state) . "', ";
	$query .= "'" . addslashes($lien_zip) . "', ";
	$query .= "'" . addslashes($attorney_eams_no) . "', ";
	$query .= "'" . addslashes($attorney) . "', ";
	$query .= "'" . addslashes($attorney_street) . "', ";
	$query .= "'" . addslashes($attorney_city) . "', ";
	$query .= "'" . addslashes($attorney_state) . "', ";
	$query .= "'" . addslashes($attorney_zip) . "', ";
	$query .= "'" . $lien_phone . "', ";
	$query .= "'" . addslashes($claimant_representative) . "', ";	
	$query .= "'" . addslashes($lien_attorney_eams_no) . "', ";
	$query .= "'" . addslashes($lien_attorney) . "', ";
	$query .= "'" . addslashes($lien_attorney_first_name) . "', ";
	$query .= "'" . addslashes($lien_attorney_last_name) . "', ";
	$query .= "'" . addslashes($lien_attorney_street) . "', ";
	$query .= "'" . addslashes($lien_attorney_city) . "', ";
	$query .= "'" . addslashes($lien_attorney_state) . "', ";
	$query .= "'" . addslashes($lien_attorney_zip) . "', ";
	$query .= "'" . addslashes($lien_attorney_phone) . "', ";
	$query .= "'" . $lien_sum . "', ";
	$query .= "'" . $lien_reason . "', ";
	$query .= "'" . $interpreter_date . "', ";
	$query .= "'" . addslashes($lien_reason_other_text) . "', ";
	$query .= "'" . $lien_copy . "', ";
	$query .= "'" . $lien_signature . "', ";
	$query .= "'" . $exempt . "', ";
	$query .= "'" . $exempt_signature . "' ";
	$query .= "); ";
	
	//echo $query . "<br />";
	
	DB::runOrDie($query);
//get the last dor id for this case
	$query = "SELECT lien_id FROM tbl_lien WHERE case_id =" . $case_id . " 
	ORDER BY lien_id DESC 
	LIMIT 0,1";
	$result = DB::runOrDie($query);
	if ($result->rowCount() >0) {
		$lien_id = $result->fetchColumn();
		$mytrack = new tracker($r_link);
		$mytrack->table_id = $lien_id;
		$mytrack->trackIt("lien", "insert", $cus_id, $user_id);
	}
} else {
	$query = "UPDATE tbl_lien
	SET `lien_type` = '" . $lien_type . "', 
	`original_lien_date` = '" . $original_lien_date . "', 
	`lien_organization` = '" . addslashes($lien_organization) . "',
	`lien_first_name` = '" . addslashes($lien_first_name) . "',
	`lien_last_name` = '" . addslashes($lien_last_name) . "',
	`lien_street` = '" . addslashes($lien_street) . "',	
	`lien_city` = '" . addslashes($lien_city) . "',
	`lien_state` = '" . addslashes($lien_state) . "',
	`lien_zip` = '" . addslashes($lien_zip) . "',
	`lien_phone` = '" . $lien_phone . "',
	`attorney` = '" . addslashes($attorney) . "',
	`attorney_eams_no` = '" . addslashes($attorney_eams_no) . "',
	`attorney_city` = '" . addslashes($attorney_city) . "',
	`attorney_street` = '" . addslashes($attorney_street) . "',
	`attorney_state` = '" . addslashes($attorney_state) . "',
	`attorney_zip` = '" . addslashes($attorney_zip) . "',
	`claimant_representative` = '" . addslashes($claimant_representative) . "',
	`lien_attorney` = '" . addslashes($lien_attorney) . "',
	`lien_attorney_first_name` = '" . addslashes($lien_attorney_first_name) . "',
	`lien_attorney_last_name` = '" . addslashes($lien_attorney_last_name) . "',
	`lien_attorney_eams_no` = '" . addslashes($lien_attorney_eams_no) . "',
	`lien_attorney_street` = '" . addslashes($lien_attorney_street) . "',
	`lien_attorney_city` = '" . addslashes($lien_attorney_city) . "',
	`lien_attorney_state` = '" . addslashes($lien_attorney_state) . "',
	`lien_attorney_zip` = '" . addslashes($lien_attorney_zip) . "',
	`lien_attorney_phone` = '" . addslashes($lien_attorney_phone) . "',
	`lien_sum` = '" . $lien_sum . "',
	`lien_reason` = '" . $lien_reason . "',
	`interpreter_date` = '" . $interpreter_date . "',
	`lien_reason_other_text` = '" . addslashes($lien_reason_other_text) . "',
	`lien_copy` = '" . $lien_copy . "',
	`exempt` = '" . $exempt . "',
	`exempt_signature` = '" . $exempt_signature . "',
	`lien_signature` = '" . $lien_signature . "'";
	$query .= " WHERE `lien_id` = " . $lien_id;
	
	DB::runOrDie($query);
    $mytrack = new tracker($r_link);
	$mytrack->table_id = $lien_id;
	$mytrack->trackIt("lien", "update", $cus_id, $user_id);
}

$adj_number = passed_var("adj_number");
if ($adj_number!="") {
	//now we must update the case
	$query = "UPDATE tbl_case
	SET adj_number = '" . $adj_number . "'";
	$query .= " WHERE `case_id` =" . $case_id;
	
	DB::runOrDie($query);
    $mytrack = new tracker($r_link);
	$mytrack->table_id = $case_id;
	$mytrack->trackIt("case", "update", $cus_id, $user_id);
}
//die($query);
header("location:upload_lien_preamble.php?cus_id=" . $cus_id . "&case_id=" . $case_id . "&lien_id=" . $lien_id . "&suid=" . $suid);

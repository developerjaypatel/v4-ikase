<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

include ("../../text_editor/ed/functions.php");
include ("../../text_editor/ed/datacon.php");

$cus_id = passed_var("cus_id");
$attorney_id  = passed_var("attorney_id");

$query = "SELECT  csa.`attorney_id`, csa.`user_id`, csa.`firm_name`, csa.`first_name`, csa.`middle_initial`, csa.`last_name`, csa.`phone`, csa.`fax`, csa.`email`, csa.`active`, csa.`default_attorney`, cse_user.user_logon
FROM cse_attorney csa
LEFT OUTER JOIN cse_user
ON csa.user_id = cse_user.user_id
WHERE 1
AND csa.deleted = 'N' 
AND csa.customer_id = '" . $cus_id . "'";
if ($attorney_id!="") {
	$query .= " AND csa.attorney_id = " . $attorney_id;
}
$query .= " ORDER BY csa.`first_name`";
$result = DB::runOrDie($query);

$arrRows = array();
while ($row = $result->fetch()) {
	$attorney_id = $row->attorney_id;
	$user_id = $row->user_id;
	$firm_name = $row->firm_name;
	$first_name = $row->first_name;
	$middle_initial = $row->middle_initial;
	if ($middle_initial!="") {
		$middle_initial .= ".";
	}
	$last_name = $row->last_name;
	
	if ($firm_name==$first_name) {
		$first_name = "";
	}
	$phone = $row->phone;
	$fax = $row->fax;
	$email = $row->email;
	$user_logon = $row->user_logon;
	
	$active = $row->active;
	$default_attorney = $row->default_attorney;
	
	$the_row = $attorney_id . "|". $firm_name . "|". $first_name . " " . $middle_initial . " " . $last_name . "|" . $phone . "|" . $fax . "|" . $email . "|" . $active . "|" . $default_attorney . "|". $first_name . "|" . $middle_initial . "|" . $last_name . "|" . $user_id . "|" . $user_logon;
	
	//die($the_row);
	//$the_row = str_replace(" ", "&nbsp;", $the_row);
	$arrRows[] = $the_row;
}
echo implode("\n", $arrRows);
exit();

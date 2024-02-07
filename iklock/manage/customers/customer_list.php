<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once('../../../shared/legacy_session.php');
session_write_close();

include("sec.php");

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

include ("../../api/connection.php");

$lastmonth = mktime(0, 0, 0, date("m")-1, date("d"),   date("Y"));

$sql = "SELECT  cus.*
FROM `iklock`.`customer` cus
INNER JOIN `iklock`.`customer` parent
ON cus.parent_customer_id = parent.customer_id
WHERE 1 AND cus.deleted = 'N'";
$sql .= " ORDER BY `cus_name`";
try {
	$customers = DB::select($sql);
} catch(PDOException $e) {
	echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	die();
}
$arrRows = array();
foreach($customers as $cus) {
	$cus_id = $cus->customer_id;
	$eams_no = $cus->eams_no;
	$start_date = $cus->start_date;
	$cus_name = $cus->cus_name;
	$cus_name = strtolower($cus_name);
	$cus_name = ucwords($cus_name);
	$cus_name = str_replace(" ", "&nbsp;", $cus_name);
	
	$data_source = $cus->data_source;
	/*
	$parent_id = $cus->parent_id;
	$parent_name = $cus->parent_name;
	$parent_name = strtolower($parent_name);
	$parent_name = ucwords($parent_name);
	$parent_name = str_replace(" ", "&nbsp;", $parent_name);
	
	$cus_name_first = $cus->cus_name_first;
	$cus_name_middle = $cus->cus_name_middle;
	$cus_name_last = $cus->cus_name_last;
	$cus_street = $cus->cus_street;
	$cus_city = $cus->cus_city;
	$cus_city = strtolower($cus_city);
	$cus_city = ucwords($cus_city);
	$cus_city = str_replace(" ", "&nbsp;", $cus_city);
	$cus_state = $cus->cus_state;
	$cus_zip = $cus->cus_zip;
	$admin_client_id = $cus->admin_client;
	$password = $cus->password;
	$xl_filed = $cus->xl_filed;
	$pwd = $cus->pwd;
	$data_path = $cus->data_path;
	$user_count = $cus->user_count;
	$inhouse_id = $cus->inhouse_id;
	$permissions = $cus->permissions;
	
	$invoiced = $cus->invoiced;
	$invoiced_amount = $cus->invoiced_amount;
	$paids = $cus->paids;
	$user_rate = $cus->user_rate;
	*/
	$data_source = "iklock";
	if ($data_source=="") {
		$data_source .= "_" . $data_source;
	}
	
	
	//$the_row = $cus_id . "|". $eams_no . "|". $cus_name_first . "&nbsp;". $cus_name_last . "|". $cus_name . "|" . $cus_street . "|" . $cus_city . "|" . $cus_state . "|" . $cus_zip . "|" . $admin_client_id . "|" . $password . "|" . $xl_filed . "|" . $pwd . "|" . $inhouse_id . "|" . $permissions . "|" . $parent_id . "|" . $parent_name . "|" . $data_path . "|" . $user_count . "|" . $json_counts . "|" . $invoiced . "|" . $invoiced_amount . "|" . $paids . "|" . $user_rate . "|" . date("m/d/y", strtotime($start_date));
	
	
	$the_row = "
	<tr>
		<td align='left' valign='top'>
			<a href='#' id='edit_customer_" . $cus_id . "' class='edit_customer'>" . $cus_name . "</a>
		</td>
	</tr>
	";
	$arrRows[] = $the_row;
}
$maincontent = implode("\n", $arrRows);
echo "
<table>
	<tr>
		<th align='left' valign='top'>Customer</th>
	</tr>
	" . $maincontent . "
</table>";
exit();
?>

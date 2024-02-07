<?php
include("../../api/manage_session.php");
session_write_close();

include("sec.php");

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

include ("../../text_editor/ed/functions.php");
include ("../../text_editor/ed/datacon.php");

$admin_client = passed_var("admin_client");
$owner_id = passed_var("owner_id");
$lastmonth = mktime(0, 0, 0, date("m")-1, date("d"),   date("Y"));

$query = "SELECT  cus.`customer_id` cus_id, cus.`eams_no`, cus.start_date, cus.`cus_name`, cus.`cus_name_first`, cus.`cus_name_middle`, cus.`cus_name_last`, cus.`cus_street`, cus.`cus_city`, cus.`cus_state`, cus.`cus_zip`,  cus.`admin_client`,  cus.data_source, cus.`password`, cus.`xl_filed`,  cus.`pwd`, cus.`inhouse_id`, cus.`permissions`, cus.`data_path`, parent.customer_id parent_id, parent.cus_name parent_name,
IFNULL(`user_count`, 0) `user_count`,
IFNULL(`invoice_id`, 0) invoiced, IFNULL(`total`, 0) invoiced_amount, 
IFNULL(`paids`, 0) paids,
cus.user_rate
FROM `ikase`.cse_customer cus
INNER JOIN `ikase`.cse_customer parent
ON cus.parent_customer_id = parent.customer_id
LEFT OUTER JOIN (
	SELECT customer_id, COUNT(user_id)  user_count
	FROM ikase.cse_user
	WHERE 1
	AND activated = 'Y'
	AND user_name != 'Matrix Admin'
	GROUP BY customer_id
) user_count
ON cus.customer_id = user_count.customer_id
LEFT OUTER JOIN (
	SELECT inv.customer_id, `invoice_id`, `total`, IFNULL(payments.paids, 0) paids
	FROM `ikase`.cse_invoice inv
	LEFT OUTER JOIN (
		SELECT invoice_uuid, SUM(payment) paids
		FROM ikase.cse_check chk
		INNER JOIN ikase.cse_invoice_check ich
		ON chk.check_uuid = ich.check_uuid
		GROUP BY invoice_uuid
	) payments
	ON inv.invoice_uuid = payments.invoice_uuid
	WHERE MONTH(invoice_date) = '" . date("m", $lastmonth) . "'
	AND YEAR(invoice_date) = '" . date("Y", $lastmonth) . "'
	AND inv.deleted = 'N'
	) inv
ON cus.customer_id = inv.customer_id
WHERE 1 AND cus.deleted = 'N'";
/*
SELECT customer_id, `user_count`
	FROM `ikase`.cse_active_users
	WHERE active_month = '" . date("m", $lastmonth) . "'
	AND active_year = '" . date("Y", $lastmonth) . "'
*/
/*
if ($host=="dmsroi.com") {
	if ($owner_id > 4) {
		$query .= " AND cadm.administrator_id = " . $owner_id;
	}
}
*/
$query .= " ORDER BY `parent_name`, IF( parent.`cus_name` = cus.`cus_name` , 0, 1 ), `cus_name`";
//
$result = mysql_query($query, $r_link) or die("unable to run query<br />" .$sql . "<br>" .  mysql_error());
$numbs = mysql_numrows($result);
//die($query . "<br />" . $numbs);

for ($int=0;$int<$numbs;$int++) {
	$cus_id = mysql_result($result, $int, "cus_id");
	$eams_no = mysql_result($result, $int, "eams_no");
	$start_date = mysql_result($result, $int, "start_date");
	$cus_name = mysql_result($result, $int, "cus_name");
	$cus_name = strtolower($cus_name);
	$cus_name = ucwords($cus_name);
	$cus_name = str_replace(" ", "&nbsp;", $cus_name);
	
	$data_source = mysql_result($result, $int, "data_source");
	$parent_id = mysql_result($result, $int, "parent_id");
	$parent_name = mysql_result($result, $int, "parent_name");
	$parent_name = strtolower($parent_name);
	$parent_name = ucwords($parent_name);
	$parent_name = str_replace(" ", "&nbsp;", $parent_name);
	
	$cus_name_first = mysql_result($result, $int, "cus_name_first");
	$cus_name_middle = mysql_result($result, $int, "cus_name_middle");
	$cus_name_last = mysql_result($result, $int, "cus_name_last");
	$cus_street = mysql_result($result, $int, "cus_street");
	$cus_city = mysql_result($result, $int, "cus_city");
	$cus_city = strtolower($cus_city);
	$cus_city = ucwords($cus_city);
	$cus_city = str_replace(" ", "&nbsp;", $cus_city);
	$cus_state = mysql_result($result, $int, "cus_state");
	$cus_zip = mysql_result($result, $int, "cus_zip");
	$admin_client_id = mysql_result($result, $int, "admin_client");
	$password = mysql_result($result, $int, "password");
	$xl_filed = mysql_result($result, $int, "xl_filed");
	$pwd = mysql_result($result, $int, "pwd");
	$data_path = mysql_result($result, $int, "data_path");
	$data_source = mysql_result($result, $int, "data_source");
	$user_count = mysql_result($result, $int, "user_count");
	$inhouse_id = mysql_result($result, $int, "inhouse_id");
	$permissions = mysql_result($result, $int, "permissions");
	$invoiced = mysql_result($result, $int, "invoiced");
	$invoiced_amount = mysql_result($result, $int, "invoiced_amount");
	$paids = mysql_result($result, $int, "paids");
	$user_rate = mysql_result($result, $int, "user_rate");
	
	if ($pwd=="") {
		$query = "UPDATE cse_customer
		SET pwd = '" . encrypt($cus_id, $crypt_key) . "'
		WHERE customer_id = " . $cus_id;
		
		//die($query);
		$result_update = mysql_query($query, $r_link) or die("unable to update pwd<br />" .$query . "<br>" .  mysql_error());
	}
	if ($data_source!="") {
		$data_source = "ikase_" . $data_source;
	} else {
		$data_source = "ikase";
	}
	//let's query for batchscans and sends
	$query = "
	SELECT customer_id, 'batchscan' `type`, COUNT(batchscan_id) action_count, MAX(dateandtime) action_last
	FROM `" . $data_source . "`.cse_batchscan
	WHERE customer_id = '" . $cus_id . "'
	GROUP BY customer_id
	UNION
	SELECT cm.customer_id, 'sent' `type`, COUNT(cs.sent_id) action_count, MAX(cs.timestamp) action_last
	FROM `" . $data_source . "`.cse_sent cs
	INNER JOIN ikase.cse_message cm
	ON cs.message_uuid = cm.message_uuid
	WHERE cm.customer_id = '" . $cus_id . "'
	GROUP BY cm.customer_id";
	$result_counts = mysql_query($query, $r_link) or die("unable to get batchscans and emails<br />" .$query . "<br>" .  mysql_error());
	$numbs_count = mysql_numrows($result_counts);
	$arrCounts = array();
	for($i = 0; $i < $numbs_count; $i++) {
		$type = mysql_result($result_counts, $i, "type");
		$action_count = mysql_result($result_counts, $i, "action_count");
		$action_last = mysql_result($result_counts, $i, "action_last");
		$action_last = date("m/d/Y", strtotime($action_last));
		$arrCounts[$type] = array("count"=>$action_count, "last"=>$action_last);
	}
	$json_counts = json_encode($arrCounts);
	if ($invoiced==0) {
		$url = "https://www.ikase.org/manage/customers/invoice.php";
		$params = array("cus_id"=>$cus_id);
		curl_post_async($url, $params);
	}
	
	$the_row = $cus_id . "|". $eams_no . "|". $cus_name_first . "&nbsp;". $cus_name_last . "|". $cus_name . "|" . $cus_street . "|" . $cus_city . "|" . $cus_state . "|" . $cus_zip . "|" . $admin_client_id . "|" . $password . "|" . $xl_filed . "|" . $pwd . "|" . $inhouse_id . "|" . $permissions . "|" . $parent_id . "|" . $parent_name . "|" . $data_path . "|" . $user_count . "|" . $json_counts . "|" . $invoiced . "|" . $invoiced_amount . "|" . $paids . "|" . $user_rate . "|" . date("m/d/y", strtotime($start_date)) . "|" . $data_source;
	
	//die($the_row);
	//$the_row = str_replace(" ", "&nbsp;", $the_row);
	$arrRows[] = $the_row;
}
mysql_close($r_link);
$maincontent = implode("\n", $arrRows);
echo $maincontent;
exit();
?>
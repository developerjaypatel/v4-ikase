<?php
require_once('../../shared/legacy_session.php');
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
$result = DB::runOrDie($query);

while ($row = $result->fetch()) {
	$cus_id = $row->cus_id;
	$eams_no = $row->eams_no;
	$start_date = $row->start_date;
	$cus_name = $row->cus_name;
	$cus_name = strtolower($cus_name);
	$cus_name = ucwords($cus_name);
	$cus_name = str_replace(" ", "&nbsp;", $cus_name);
	
	$data_source = $row->data_source;
	$parent_id = $row->parent_id;
	$parent_name = $row->parent_name;
	$parent_name = strtolower($parent_name);
	$parent_name = ucwords($parent_name);
	$parent_name = str_replace(" ", "&nbsp;", $parent_name);
	
	$cus_name_first = $row->cus_name_first;
	$cus_name_middle = $row->cus_name_middle;
	$cus_name_last = $row->cus_name_last;
	$cus_street = $row->cus_street;
	$cus_city = $row->cus_city;
	$cus_city = strtolower($cus_city);
	$cus_city = ucwords($cus_city);
	$cus_city = str_replace(" ", "&nbsp;", $cus_city);
	$cus_state = $row->cus_state;
	$cus_zip = $row->cus_zip;
	$admin_client_id = $row->admin_client;
	$password = $row->password;
	$xl_filed = $row->xl_filed;
	$pwd = $row->pwd;
	$data_path = $row->data_path;
	$data_source = $row->data_source;
	$user_count = $row->user_count;
	$inhouse_id = $row->inhouse_id;
	$permissions = $row->permissions;
	$invoiced = $row->invoiced;
	$invoiced_amount = $row->invoiced_amount;
	$paids = $row->paids;
	$user_rate = $row->user_rate;
	
	if ($pwd=="") {
		DB::runOrDie("UPDATE cse_customer SET pwd = '".encrypt($cus_id, $crypt_key)."' WHERE customer_id = ".$cus_id);
}
	if ($data_source!="") {
		$data_source = "ikase_" . $data_source;
	} else {
		$data_source = "ikase";
	}
	//let's query for batchscans and sends
	$result_counts = DB::runOrDie("
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
	GROUP BY cm.customer_id");
	$arrCounts = array();
	while ($row = $result_counts->fetch()) {
		$type = $row->type;
		$action_count = $row->action_count;
		$action_last = $row->action_last;
		$action_last = date("m/d/Y", strtotime($action_last));
		$arrCounts[$type] = ["count" => $action_count, "last" => $action_last];
	}
	$json_counts = json_encode($arrCounts);
	if ($invoiced==0) {
		$url = "https://www.ikase.org/manage/customers/invoice.php";
		$params = array("cus_id"=>$cus_id);
		curl_post_async($url, $params);
	}

	$arrRows[] = $cus_id . "|". $eams_no . "|". $cus_name_first . "&nbsp;". $cus_name_last . "|". $cus_name . "|" . $cus_street . "|" . $cus_city . "|" . $cus_state . "|" . $cus_zip . "|" . $admin_client_id . "|" . $password . "|" . $xl_filed . "|" . $pwd . "|" . $inhouse_id . "|" . $permissions . "|" . $parent_id . "|" . $parent_name . "|" . $data_path . "|" . $user_count . "|" . $json_counts . "|" . $invoiced . "|" . $invoiced_amount . "|" . $paids . "|" . $user_rate . "|" . date("m/d/y", strtotime($start_date)) . "|" . $data_source;
}
echo implode("\n", $arrRows);
exit();

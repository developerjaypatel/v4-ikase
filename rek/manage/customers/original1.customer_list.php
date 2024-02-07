<?php
include("../../../api/manage_session.php");
session_write_close();

//include("sec.php");

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

include ("../../../text_editor/ed/functions.php");
include ("../../../text_editor/ed/datacon_rek.php");

$admin_client = passed_var("admin_client");
$owner_id = passed_var("owner_id");
$lastmonth = mktime(0, 0, 0, date("m")-1, date("d"),   date("Y"));

$query = "SELECT  cus.`customer_id` cus_id, cus.`eams_no`, cus.`cus_name`, cus.`cus_name_first`, cus.`cus_name_middle`, cus.`cus_name_last`, cus.`cus_street`, cus.`cus_city`, cus.`cus_state`, cus.`cus_zip`,  cus.`admin_client`,  cus.data_source, cus.`password`, cus.`xl_filed`,  cus.`pwd`, cus.`inhouse_id`, cus.`permissions`, cus.`data_path`, cus.user_rate, parent.customer_id parent_id, parent.cus_name parent_name
FROM `rek`.rek_customer cus
INNER JOIN `rek`.rek_customer parent
ON cus.parent_customer_id = parent.customer_id
WHERE 1 AND cus.deleted = 'N'";

$result = mysql_query($query, $r_link) or die("unable to run query<br />" .$sql . "<br>" .  mysql_error());
$numbs = mysql_numrows($result);
//die($query . "<br />" . $numbs);

for ($int=0;$int<$numbs;$int++) {
	$cus_id = mysql_result($result, $int, "cus_id");
	$eams_no = mysql_result($result, $int, "eams_no");
	$cus_name = mysql_result($result, $int, "cus_name");
	$cus_name = strtolower($cus_name);
	$cus_name = ucwords($cus_name);
	$cus_name = str_replace(" ", "&nbsp;", $cus_name);
	
	$data_source = mysql_result($result, $int, "data_source");
	$parent_id = mysql_result($result, $int, "parent_id");
	$parent_name = mysql_result($result, $int, "parent_name");
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
	
	
	if ($pwd=="") {
		$query = "UPDATE rek_customer
		SET pwd = '" . encrypt($cus_id, $crypt_key) . "'
		WHERE customer_id = " . $cus_id;
		
		//die($query);
		$result_update = mysql_query($query, $r_link) or die("unable to update pwd<br />" .$query . "<br>" .  mysql_error());
	}
	$data_source = "rek";
	if ($data_source=="") {
		$data_source .= "_" . $data_source;
	}
	
	$the_row = $cus_id . "|". $eams_no . "|". $cus_name_first . "&nbsp;". $cus_name_last . "|". $cus_name . "|" . $cus_street . "|" . $cus_city . "|" . $cus_state . "|" . $cus_zip . "|" . $admin_client_id . "|" . $password . "|" . $xl_filed . "|" . $pwd . "|" . $inhouse_id . "|" . $permissions . "|" . $parent_id . "|" . $parent_name . "|" . $data_path . "|" . $user_count . "|" . $json_counts . "|" . $invoiced . "|" . $invoiced_amount . "|" . $paids . "|" . $user_rate . "|" . date("m/d/y", strtotime($start_date));
	
	//die($the_row);
	//$the_row = str_replace(" ", "&nbsp;", $the_row);
	$arrRows[] = $the_row;
}
mysql_close($r_link);
$maincontent = implode("\n", $arrRows);
echo $maincontent;
exit();
?>
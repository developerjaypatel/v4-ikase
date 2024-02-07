<?php
include("../../eamsjetfiler/datacon.php");
include("../../eamsjetfiler/functions.php");

$cus_id = passed_var("cus_id");
$status = passed_var("status");
if ($status=="N") {
	$newstatus = "Y";
} else {
	$newstatus = "N";
}
$id = passed_var("id");

$query = "UPDATE tbl_attorney
SET default_attorney = 'N'
WHERE 1 ";
$query .= " AND cus_id = '" . $cus_id  . "'";

$result = mysql_query($query, $r_link)  or die("unable to change active");

$query = "UPDATE tbl_attorney
SET default_attorney = 'Y'
WHERE 1 ";
if ($cus_id>0) {
	$query .= " AND cus_id = '" . $cus_id  . "'";
}
$query .= " AND `attorney_id` = '" . $id . "'";
$result = mysql_query($query, $r_link)  or die("unable to change active");

echo "default attorney changed from " . $status . " to " . $newstatus;

mysql_close($r_link);
exit();
?>
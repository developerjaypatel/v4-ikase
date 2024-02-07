<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

include("../../eamsjetfiler/datacon.php");
include("../../eamsjetfiler/functions.php");

$the_cus_id = passed_var("the_cus_id");
$attorney_id  = passed_var("attorney_id");

$query = "SELECT  customer_upload_id, `filepath`, `name`, `upload_date`
FROM tbl_customer_uploads
WHERE 1
AND deleted = 'N' 
AND cus_id = '" . $the_cus_id . "'";
if ($upload_id!="") { //FIXME: non-existent variables
	$query .= " AND customer_upload_id = " . $upload_id;
}
$query .= " ORDER BY `name`, `upload_date`";
$result = DB::runOrDie($query);

$arrRows = array();
while ($row = $result->fetch()) {
	$upload_date = date("m/d/y h:iA", strtotime($row->upload_date));
	$arrRows[] = $row->filepath. "|".$row->customer_upload_id. "|" . $upload_date . "|" .$row->name;
}
echo implode("\n", $arrRows);
exit();

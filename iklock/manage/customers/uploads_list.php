<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
?>
<?php
include("../../eamsjetfiler/datacon.php");
include("../../eamsjetfiler/functions.php");

$the_cus_id = passed_var("the_cus_id");
$attorney_id  = passed_var("attorney_id");

$query = "SELECT  customer_upload_id, `filepath`, `name`, `upload_date`
FROM tbl_customer_uploads
WHERE 1
AND deleted = 'N' 
AND cus_id = '" . $the_cus_id . "'";
if ($upload_id!="") { //FIXME: wrong var?
	$query .= " AND customer_upload_id = " . $upload_id;
}
$query .= " ORDER BY `name`, `upload_date`";
$result = DB::runOrDie($query);

$arrRows = array();
while ($row = $result->fetch()) {
	$upload_id = $row->customer_upload_id;
	$upload = $row->filepath;
	$upload_type = $row->name;
	$upload_date = $row->upload_date;
	$upload_date = date("m/d/y h:iA", strtotime($upload_date));
	$the_row = $upload . "|". $upload_id . "|" . $upload_date . "|" . $upload_type;
	
	//die($the_row);
	//$the_row = str_replace(" ", "&nbsp;", $the_row);
	$arrRows[] = $the_row;
}
echo implode("\n", $arrRows);
exit();

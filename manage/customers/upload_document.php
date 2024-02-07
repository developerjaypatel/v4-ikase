<?php
include("../../eamsjetfiler/datacon.php");
include("../../eamsjetfiler/functions.php");

$cus_id = passed_var("cus_id");
include("../../logon_check.php");

$the_cus_id = passed_var("the_cus_id");
$cus_document = passed_var("cus_document");

if ($cus_document!="") {
	//now associate each upload with the case
	$query = "INSERT INTO tbl_customer_uploads (`cus_id`, `filepath`, `name`, `upload_date`) 
	VALUES ('" . $the_cus_id . "','" . $cus_document . "','record', '" . date("Y-m-d") . "')";
	DB::runOrDie($query);
}
echo "record ready";
exit(); 

<?php
include("../../eamsjetfiler/datacon.php");
include("../../eamsjetfiler/functions.php");

include("../../logon_check.php");

$the_cus_id = passed_var("the_cus_id");
$cus_document = passed_var("cus_document");
$folder_name = passed_var("folder_name");

if ($cus_document!="") {
	//now associate each upload with the case
	DB::runOrDie('INSERT INTO tbl_customer_uploads (`cus_id`, `filepath`, `name`) VALUES (?, ?, ?)', [$the_cus_id, $cus_document, $folder_name]);
}

echo "record ready";
exit(); 

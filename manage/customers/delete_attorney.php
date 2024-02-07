<?php
include("../../eamsjetfiler/datacon.php");
include("../../eamsjetfiler/functions.php");

$cus_id = passed_var("cus_id");
$status = passed_var("status");
$id = passed_var("id");

$query = "UPDATE tbl_attorney
SET deleted = '" . $status . "'
WHERE 1 ";
if ($cus_id>0) {
	$query .= " AND cus_id = '" . $cus_id  . "'";
}
$query .= " AND `attorney_id` = '" . $id . "'";

DB::runOrDie($query);
echo "deleted";
exit();

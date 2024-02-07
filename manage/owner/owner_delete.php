<?php
include("../../eamsjetfiler/datacon.php");
include("../../eamsjetfiler/functions.php");

$admin_id = passed_var("admin_id");
include("../../logon_check.php");

$query = "UPDATE tbl_owner
SET deleted = 'Y'
WHERE owner_id = " . $admin_id;
DB::runOrDie($query);
echo $admin_id . " deleted";
exit(); 

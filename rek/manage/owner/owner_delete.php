<?php
include("../../../eamsjetfiler/datacon_rek.php");
include("../../../eamsjetfiler/functions.php");

$admin_id = passed_var("admin_id");
include("../../../logon_check.php");

$query = "UPDATE rek_owner
SET deleted = 'Y'
WHERE owner_id = " . $admin_id;
$result = mysql_query($query, $r_link) or die("unable to clear owner");
echo $admin_id . " deleted";
exit(); 
?>
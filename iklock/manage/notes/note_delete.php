<?php
include("../eamsjetfiler/datacon.php");
include("../eamsjetfiler/functions.php");

$user_id = passed_var("user_id");
$query = "DELETE FROM tbl_user 
WHERE user_id = " . $user_id;
DB::runOrDie($query);
echo $user_id . " deleted";
exit(); 

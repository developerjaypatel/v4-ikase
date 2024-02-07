<?php
include("../eamsjetfiler/datacon.php");
include("../eamsjetfiler/functions.php");

$user_id = passed_var("user_id");
$query = "DELETE FROM tbl_user 
WHERE user_id = " . $user_id;
$result = mysql_query($query, $r_link) or die("unable to clear user");
echo $user_id . " deleted";
exit(); 
?>
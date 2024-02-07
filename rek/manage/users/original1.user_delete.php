<?php
include ("../../../text_editor/ed/functions.php");
include ("../../../text_editor/ed/datacon_rek.php");

$user_id = passed_var("user_id");
$query = "UPDATE `rek`.rek_user  
SET deleted = 'Y'
WHERE user_id = " . $user_id;
$result = mysql_query($query, $r_link) or die("unable to clear user");
echo $user_id . " deleted";
exit(); 
?>
<?php
include ("../../text_editor/ed/functions.php");
include ("../../text_editor/ed/datacon.php");

$user_id = passed_var("user_id");

$query = "UPDATE `ikase`.cse_user 
SET activated = 'Y'
WHERE user_id = " . $user_id;
DB::runOrDie($query);
echo $user_id . " activated";
exit(); 
?>

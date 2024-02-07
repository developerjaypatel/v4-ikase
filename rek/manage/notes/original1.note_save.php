<?php
include("../../eamsjetfiler/datacon.php");
include("../../eamsjetfiler/functions.php");

include("../classes/cls_note.php");

$suid = passed_var("suid");
//make sure we have the right logon
include("../../logon_check.php");

//get the id
$cus_id = passed_var("the_cus_id");
if (!is_numeric($the_cus_id)) {
	die();
}
$note_id = passed_var("note_id");

$my_note = new note($r_link);
$my_note->uuid = $note_id;
$my_note->fetch();

//update the case, both referral and main
$my_note->entered_by = $current_user_name;
$my_note->note = addslashes($note);
$my_note->update();

//join note to case
joinTables("customer", "note", $cus_id, $my_note->id, "admin_note", $r_link);

//output ids
echo $my_note->id;
?>
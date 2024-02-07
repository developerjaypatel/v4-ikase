<?php
include ("../../text_editor/ed/functions.php");
include ("../../text_editor/ed/datacon.php");

//die(print_r($_POST));

$cus_id = passed_var("cus_id", "post");
$note_id = passed_var("note_id", "post");
$subject = passed_var("subject", "post");
$type = passed_var("type", "post");
$note = passed_var("note", "post");

//put them together to store
$query = "";
if ($cus_id!="" || $note_id==-1) {
	$uuid = uniqid("CN");
	$query = "INSERT INTO cse_notes (`notes_uuid`, `subject`, `type`, `note`, `customer_id`)
	VALUES ('" . $uuid . "', '" . $subject . "', '" . $type . "', '" . addslashes($note) . "', '" . $cus_id . "')";
	//die($query); 
	
	DB::runOrDie($query);
	$note_id = DB::lastInsertId();
	
	$last_updated_date = date("Y-m-d H:i:s");
	$last_update_user = "Admin";
	
	$query_relationship = "INSERT INTO cse_notes_customer (`customer_id`, `notes_id`, `notes_customer_uuid`, `notes_uuid`, `attribute_1`, `attribute_2`, `last_updated_date`, `last_update_user`)
	VALUES ('" . $cus_id . "', '" . $note_id . "', '" . $uuid . "', '" . $uuid . "', 'customer_note', '" . $type . "', '" . $last_updated_date . "', '" . $last_update_user . "')";
	
	DB::runOrDie($query_relationship);
} else {
	$query = "UPDATE cse_notes
	SET subject = '" . $subject . "',
	type = '" . $type . "',
	note = '" . $note . "'
	WHERE note_id = '" . $note_id . "' AND customer_id = " . $cus_id;
	
	//die($query);
	DB::runOrDie($query);
}
//default for now
$blnReturnEditor = true;

if ($blnReturnEditor) {
	header("location:editor.php?admin_client=" . $admin_client . "&cus_id=" . $cus_id. "&suid=" . $suid);
} else {
	header("location:index.php?admin_client=" . $admin_client . "&suid=" . $suid);
}

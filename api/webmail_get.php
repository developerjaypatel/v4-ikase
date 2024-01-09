<?php
require_once('../shared/legacy_session.php');
if (!isset($_SESSION["user_customer_id"])) {
	die("no id");
}
session_write_close();

include("connection.php");

$notes_id = passed_var("notes_id", "get");
if (!is_numeric($notes_id)) {
	die("invalid id");
}
$sql = "SELECT `note`
		FROM `cse_notes` 
		INNER JOIN  `cse_case_notes` ON  `cse_notes`.`notes_uuid` =  `cse_case_notes`.`notes_uuid` 
		INNER JOIN `cse_case` ON  (`cse_case_notes`.`case_uuid` = `cse_case`.`case_uuid`)
		WHERE `cse_notes`.`deleted` = 'N'
		AND `cse_notes`.`notes_id` = :notes_id
		AND `cse_notes`.customer_id = " . $_SESSION['user_customer_id'];

try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("notes_id", $notes_id);
	$stmt->execute();
	$note = $stmt->fetchObject();

	echo $note->note;

} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
}

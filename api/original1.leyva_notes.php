<?php
include("connection.php");

echo "start @ " . date("H:i:s") . "<br>";
$sql = "SELECT ccn.* 
FROM `ikase_leyva`.`cse_case_notes` ccn
LEFT OUTER JOIN `ikase_leyva`.`cse_case_notes_new` cnn
ON ccn.case_notes_id = cnn.case_notes_id
WHERE cnn.case_notes_id IS NULL
ORDER BY ccn.case_notes_id ASC
LIMIT 0, 170";

try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$case_notes = $stmt->fetchAll(PDO::FETCH_OBJ);
	$stmt->closeCursor(); $stmt = null; $db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
}
$sql_base = "INSERT INTO `ikase_leyva`.`cse_case_notes_new`
	(`case_notes_id`,
	`case_notes_uuid`,
	`case_uuid`,
	`notes_uuid`,
	`attribute`,
	`last_updated_date`,
	`last_update_user`,
	`deleted`,
	`customer_id`)
	VALUES (";
$db = getConnection();
foreach($case_notes as $case_note) {
	$sql = $sql_base . "
	'" . $case_note->case_notes_id . "', 
	'" . $case_note->case_notes_uuid . "', 
	'" . $case_note->case_uuid . "', 
	'" . $case_note->notes_uuid . "', 
	'" . $case_note->attribute . "', 
	'" . $case_note->last_updated_date . "', 
	'" . $case_note->last_update_user . "', 
	'" . $case_note->deleted . "', 
	'" . $case_note->customer_id . "'
	)";
	
	//die($sql);
	
	try {
		$stmt = $db->prepare($sql);
		$stmt->execute();
	} catch(PDOException $e) {
		echo $e->getMessage();
		die($sql);
	}
}
$stmt = null; $db = null;    
die("done @ " . date("H:i:s"));

?>
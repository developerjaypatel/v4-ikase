<?php
include("connection.php");

$customer_id = 1075;
$new_nick = 'AXI';
$old_nick = 'ai';

$sql = "SELECT usr.user_id, usr.nickname, cm.message_id, cm.message_to
FROM ikase_dordulian2.cse_message_user cmu
INNER JOIN ikase_dordulian2.cse_message cm
ON cmu.message_uuid = cm.message_uuid
INNER JOIN ikase.cse_user usr
ON cmu.user_uuid = usr.user_uuid
AND usr.nickname = '" . $new_nick . "'
AND `type` = 'TO'
AND message_to = '" . $old_nick . "'";

try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	
	$stmt->execute();
	$messages = $stmt->fetchAll(PDO::FETCH_OBJ);
	$stmt->closeCursor(); $stmt = null; $db = null;
	
	foreach($messages as $message) {
		$sql_fix = "UPDATE ikase_dordulian2.cse_message
		SET message_to = '" . $new_nick . "'
		WHERE message_id = " . $message->message_id;
		
		echo $message->message_id . " done<br>";
		//die($sql_fix);
		
		$db = getConnection();
		$stmt = $db->prepare($sql_fix);
		$stmt->execute();
		$stmt = null; $db = null;
	}
	
	echo "all done";
} catch(PDOException $e) {
	$error = array("error"=> array("sql"=>$sql, "text"=>$e->getMessage()));
	echo json_encode($error);
}
?>
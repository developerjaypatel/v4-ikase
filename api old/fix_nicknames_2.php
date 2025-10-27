<?php
include("connection.php");

$customer_id = 1075;
$new_nick = 'AXI';
$old_nick = 'ai';

$sql = "SELECT DISTINCT cm.message_id, cm.message_to, cm.message_cc, cm.message_bcc
FROM ikase_dordulian2.cse_message_user cmu
INNER JOIN ikase_dordulian2.cse_message_track cm
ON cmu.message_uuid = cm.message_uuid AND cm.operation = 'insert'
INNER JOIN ikase.cse_user usr
ON cmu.user_uuid = usr.user_uuid
AND usr.customer_id = 1075
AND `type` = 'to'
AND message_cc LIKE '%;%'";

try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	
	$stmt->execute();
	$messages = $stmt->fetchAll(PDO::FETCH_OBJ);
	$stmt->closeCursor(); $stmt = null; $db = null;
	
	foreach($messages as $message) {
		$nicks = $message->message_cc;
		echo $nicks . "<br />\r\n";
		$arrNicks = explode(";", $nicks);
		$arrNewNicks = array();
		foreach($arrNicks as $nick) {
			$new_nick = $nick;
			switch($nick) {
				case "AY":
					$new_nick = "AXY";
					break;
				case "ai":
					$new_nick = "AXI";
					break;
				case "DR":
					$new_nick = "DXR";
					break;
				case "CM":
					$new_nick = "CXM";
					break;
				case "AN":
					$new_nick = "ALM";
					break;
				case "rq":
					$new_nick = "RXQ";
					break;
			}
			$arrNewNicks[] = $new_nick;
		}
		array_unique($arrNewNicks);
		$new_nicks = implode(";", $arrNewNicks);
		
		echo strlen($new_nicks) . "<br />" . $new_nicks . "<br /><br />";
		$sql_fix = "UPDATE ikase_dordulian2.cse_message
		SET message_cc = '" . $new_nicks . "'
		WHERE message_id = " . $message->message_id;
		//die($sql_fix);
		echo $message->message_id . " done<br />\r\n";
		
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
<?php
include("connection.php");

$sql = "SELECT * FROM ikase_dordulian2.cse_task
WHERE 1
AND `assignee` IN ('AY', 'rq', 'AN', 'CM', 'DR', 'ai')";

try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	
	$stmt->execute();
	$cases = $stmt->fetchAll(PDO::FETCH_OBJ);
	$stmt->closeCursor(); $stmt = null; $db = null;
	
	foreach($cases as $case) {
		$nicks = $case->assignee;
		$new_nick = $nicks;
		
		switch($nicks) {
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

		$sql_fix = "UPDATE ikase_dordulian2.cse_task
		SET `assignee` = '" . $new_nick . "'
		WHERE task_id = " . $case->task_id;
		//die($sql_fix);
		echo $case->task_id . " done<br />\r\n";
		
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
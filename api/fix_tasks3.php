<?php
include("connection.php");

$customer_id = 1075;
$new_nick = 'AXI';
$old_nick = 'ai';

$sql = "SELECT * FROM ikase_dordulian2.cse_task
WHERE 1
AND assignee LIKE '%;%'";

try {
	$messages = DB::select($sql);
	
	foreach($messages as $message) {
		$nicks = $message->assignee;
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
		
		if ($nicks == $new_nicks) {
			continue;
		}
		echo $nicks . "<br />\r\n";
		echo $new_nicks . "<br /><br />";
		$sql_fix = "UPDATE ikase_dordulian2.cse_task
		SET assignee = '" . $new_nicks . "'
		WHERE task_id = " . $message->task_id;
		//die($sql_fix);
		echo $message->task_id . " done<br />\r\n";
		
		$stmt = DB::run($sql_fix);
	}
	
	echo "all done";
} catch(PDOException $e) {
	$error = array("error"=> array("sql"=>$sql, "text"=>$e->getMessage()));
	echo json_encode($error);
}

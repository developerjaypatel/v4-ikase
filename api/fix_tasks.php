<?php
include("connection.php");

$sql = "SELECT * FROM ikase_dordulian2.cse_task
WHERE `from` IN ('AY', 'rq', 'AN', 'CM', 'DR', 'ai')";

try {
	$cases = DB::select($sql);
	
	foreach($cases as $case) {
		$nicks = $case->from;
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
		SET `from` = '" . $new_nick . "'
		WHERE task_id = " . $case->task_id;
		//die($sql_fix);
		echo $case->task_id . " done<br />\r\n";
		
		$stmt = DB::run($sql_fix);
	}
	
	echo "all done";
} catch(PDOException $e) {
	$error = array("error"=> array("sql"=>$sql, "text"=>$e->getMessage()));
	echo json_encode($error);
}

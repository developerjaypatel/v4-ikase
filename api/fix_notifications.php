<?php
include("connection.php");

$sql = "SELECT * FROM ikase_dordulian2.cse_notification
WHERE notifier IN ('AY', 'rq', 'AN', 'CM', 'DR', 'ai')";

try {
	$cases = DB::select($sql);
	
	foreach($cases as $case) {
		$nicks = $case->notifier;
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

		$sql_fix = "UPDATE ikase_dordulian2.cse_notification
		SET notifier = '" . $new_nick . "'
		WHERE notification_id = " . $case->notification_id;
		//die($sql_fix);
		echo $case->notification_id . " done<br />\r\n";
		
		$stmt = DB::run($sql_fix);
	}
	
	echo "all done";
} catch(PDOException $e) {
	$error = array("error"=> array("sql"=>$sql, "text"=>$e->getMessage()));
	echo json_encode($error);
}

<?php
require_once('../shared/legacy_session.php');
set_time_limit(3000);

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;

include("connection.php");

try {
	$db = getConnection();
	
	include("customer_lookup.php");
	
	$sql_truncate = "DELETE FROM `" . $data_source . "`.`" . $data_source . "_notes` WHERE `type` = 'audit'; DELETE FROM `" . $data_source . "`.`" . $data_source . "_case_notes` WHERE `attribute` = 'audit'; ";
	$stmt = DB::run($sql_truncate);
	
	$sql = "SELECT mc.case_id, mc.case_uuid, mc.cpointer
	FROM `" . $data_source . "`.`" . $data_source . "_case` mc
	ORDER BY mc.cpointer";
	$stmt = $db->prepare($sql);
	//echo $sql . "\r\n\r\n";
	$stmt->execute();
	
	$cases = $stmt->fetchAll(PDO::FETCH_OBJ);
	foreach($cases as $key=>$case){
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$row_start_time = $time;
		
		echo "<br>Processing -> " . $key. " == " . $case->cpointer . "  ";
		//older dbs don't have audit2
		$note_number=2;
		//for($note_number=1; $note_number<7; $note_number++) {
			echo "Note # " . $note_number . "\r\n\r\n";
			
			$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_notes` (`case_notes_uuid`, `case_uuid`,  `notes_counter`, `notes_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
			SELECT '" . $case->case_uuid . "', '" . $case->case_uuid . "', @curRow := @curRow + 1, 
			CONCAT('a_" . $customer_id . "', '_" . $note_number . "', '_A_', @curRow, '_', `cpointer`) AS `notes_uuid`, 
			'audit', `xdatetime`, 'system', 'N', " . $customer_id . "
			FROM `" . $data_source . "`.audit" . $note_number . "
			JOIN    (SELECT @curRow := 0) r
			WHERE `cpointer` = '" . $case->cpointer . "'
			AND `oldvalue` NOT LIKE 'Open Active%'
			AND `newvalue` != ''";
			
			$stmt = $db->prepare($sql);
			//die($sql . "\r\n\r\n");
			$stmt->execute();
			
			$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_notes` (`notes_counter`, `notes_uuid`, `note`, `dateandtime`, `entered_by`, `customer_id`, `type`)
			SELECT @curRow := @curRow + 1, 
			CONCAT('a_" . $customer_id . "', '_" . $note_number . "', '_A_', @curRow, '_', `cpointer`) AS `notes_uuid`, 
			`newvalue`,  `xdatetime`, IFNULL(`workcode`, '') `workcode`, 
			'" . $customer_id . "', 'audit'
			FROM `" . $data_source . "`.audit" . $note_number . "
			JOIN    (SELECT @curRow := 0) r
			WHERE cpointer = '" . $case->cpointer . "'
			AND `oldvalue` NOT LIKE 'Open Active%'
			AND `newvalue` != ''";
			
			$stmt = $db->prepare($sql);
			//echo $sql . "\r\n\r\n";
			$stmt->execute();
			
			$time = microtime();
			$time = explode(' ', $time);
			$time = $time[1] + $time[0];
			$finish_time = $time;
			$total_time = round(($finish_time - $row_start_time), 4);
			echo " => Time spent:" . $total_time . "<br />
<br />
";
		//}
	}
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	$success = array("success"=> array("text"=>"done", "duration"=>$total_time));

	echo json_encode($success);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}	

include("cls_logging.php");
?>
<script language="javascript">
parent.setFeedback("audit import completed");
</script>

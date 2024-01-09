<?php
require_once('../shared/legacy_session.php');
set_time_limit(3000);

error_reporting(E_ALL);
ini_set('display_errors', '1');

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;

include("connection.php");

?>
<script language="javascript">
parent.setFeedback("notes import started");
</script>
<?php
$db = getConnection();
try {
	include("customer_lookup.php");
	
	/*
	$sql = "SELECT mc.case_id, mc.case_uuid, mc.cpointer
	FROM `" . $data_source . "`.`" . $data_source . "_case` mc 
	LEFT OUTER JOIN `" . $data_source . "`.`" . $data_source . "_case_notes` cact
	ON mc.case_uuid = cact.case_uuid
	WHERE 1
	AND mc.cpointer IN (SELECT DISTINCT notepoint FROM `" . $data_source . "`.`note1`)
	AND cact.case_uuid IS NULL
	ORDER BY mc.cpointer DESC
	LIMIT 0, 1";

	$sql = "SELECT 
    cpointer, subtable.case_uuid, case_notes.case_uuid notes_case_uuid
FROM
    (SELECT DISTINCT
        mc.cpointer, mc.case_uuid
    FROM
        `" . $data_source . "`.`" . $data_source . "_case` mc
    INNER JOIN `" . $data_source . "`.`note1` ON mc.cpointer = note1.notepoint
    WHERE
        1 
			AND notedesc != ''
			AND notedesc NOT LIKE 'Folder accessed%'
			AND notedate != '  /  /'
			AND notedate != '/  /'
			AND notedate != ''
			AND notedate != '02/25/1902'
			AND notedate NOT LIKE '00%'
AND LENGTH(notedate) = 10
			LIMIT 0 , 1     
    ) subtable
    LEFT OUTER JOIN (SELECT DISTINCT
            case_uuid
        FROM
            " . $data_source . "." . $data_source . "_case_notes
            WHERE `attribute` != 'document' AND `attribute` != 'quick') case_notes
	ON subtable.case_uuid = case_notes.case_uuid
WHERE
    1
       AND case_notes.case_uuid IS NULL ";


$sql = "SELECT DISTINCT
    mc.cpointer, note1.notepoint, mc.case_uuid
FROM
    `" . $data_source . "`.`" . $data_source . "_case` mc
        INNER JOIN
    `" . $data_source . "`.`note1` ON mc.cpointer = note1.notepoint
        LEFT OUTER JOIN
    " . $data_source . "." . $data_source . "_case_notes case_notes ON mc.case_uuid = case_notes.case_uuid
        
        AND `attribute` != 'document'
        AND `attribute` != 'quick'
WHERE
    1
    AND case_notes.case_uuid IS NULL
	#AND notedesc != ''
			#AND notedesc NOT LIKE 'Folder accessed%'
			#AND notedate != '  /  /'
			#AND notedate != '/  /'
			#AND notedate != ''
			#AND notedate != '02/25/1902'
			#AND notedate NOT LIKE '00%'
AND LENGTH(notedate) = 10
LIMIT 0 , 1";

	*/
	$sql = "SELECT cpointer, case_uuid  
FROM " . $data_source . ".note_available
WHERE notes_case_uuid IS NULL
LIMIT 0, 1";
	
	echo $sql . "<br /><br />\r\n\r\n";
	//die();
	
	$stmt = DB::run($sql);
	
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	echo " => initial spent:" . $total_time . "<br /><br />";

	$cases = $stmt->fetchAll(PDO::FETCH_OBJ);
	$arrCaseUUID =  array();
	$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$row_start_time = $time;
		
	if(count($cases)==0) {
		die("done");
	}
	$rand = rand(100,200);
	//die(print_r($cases));
	foreach($cases as $key=>$case){
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$row_start_time = $time;
		
		$cpointer = $case->cpointer;
		echo "Processing -> " . $key. " == " . $cpointer . "<br /><br />\r\n";
		if (in_array($cpointer, $arrCaseUUID)) {
			//one time per pointer
			continue;
		} 
		$arrCaseUUID[] = $cpointer;
		
		$note_number=1;
		//for($note_number=1; $note_number<7; $note_number++) {
			echo "Note # " . $note_number . "\r\n\r\n";
			
			$db->beginTransaction();
			
			$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_notes` (`case_notes_uuid`, `case_uuid`,  `notes_counter`, `notes_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
			
			SELECT '', '" . $case->case_uuid . "', @curRow := @curRow + 1, 
			CONCAT('" . $customer_id . "', 'n" . $note_number . "','_" . $rand . "_', @curRow,'_',`notepoint`) AS `notes_uuid`, 'general', 
			STR_TO_DATE(  `notedate` ,  '%m/%d/%Y' ) notedate, 
			'system', 'N', " . $customer_id . "
			FROM `" . $data_source . "`.`note" . $note_number . "`
			JOIN    (SELECT @curRow := 0) r
			WHERE `notepoint` = '" . $cpointer . "'
			#AND notedesc != ''
			#AND notedesc NOT LIKE 'Folder accessed%'
			#AND notedate != '  /  /'
			#AND notedate != '/  /'
			#AND notedate != ''
			#AND notedate != '02/25/1902'
			#AND notedate NOT LIKE '00%'
AND LENGTH(notedate) = 10
			";
			//die($sql);
			/*
			#AND LENGTH(STR_TO_DATE(  `notedate` ,  '%m/%d/%Y' )) = 10
			#AND YEAR(STR_TO_DATE(  `notedate` ,  '%m/%d/%Y' )) > 1970
			*/
			
			$stmt = $db->prepare($sql);
			echo "\r\n<br />" . $sql . "<br /><br />\r\n\r\n";
			
			$stmt->execute();
			
			$time = microtime();
			$time = explode(' ', $time);
			$time = $time[1] + $time[0];
			$finish_time = $time;
			$total_time = round(($finish_time - $row_start_time), 4);
			
			echo " secondary spent:" . $total_time . "<br /><br />";
			//older dbs don't have this field
			//IFNULL(enteredby, '') enteredby
			//STR_TO_DATE(  `notedate` ,  '%m/%d/%Y' ) notedate, 
			$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_notes` (`notes_counter`, `notes_uuid`, `note`, 
			`dateandtime`, `entered_by`, `customer_id`, `type`)
			SELECT @curRow := @curRow + 1, 
			CONCAT('" . $customer_id . "', 'n" . $note_number . "','_" . $rand . "_', @curRow,'_',`notepoint`) AS `notes_uuid`, `notedesc`,  
			STR_TO_DATE(  `notedate` ,  '%m/%d/%Y' ) notedate,
			'system' enteredby, 
			'" . $customer_id . "', 'general'
			FROM `" . $data_source . "`.`note" . $note_number . "`
			JOIN    (SELECT @curRow := 0) r
			WHERE notepoint = '" . $cpointer . "'
			#AND notedesc != ''
			#AND notedesc NOT LIKE 'Folder accessed%'
			#AND notedate != '  /  /'
			#AND notedate != '/  /'
			#AND notedate != ''
			#AND notedate != '02/25/1902'
			#AND notedate NOT LIKE '00%'
AND LENGTH(notedate) = 10
			";
			//AND LENGTH(STR_TO_DATE(  `notedate` ,  '%m/%d/%Y' )) = 10
			//AND YEAR(STR_TO_DATE(  `notedate` ,  '%m/%d/%Y' )) > 1970
			
			echo $sql . "<br /><br />\r\n\r\n";
			$stmt = DB::run($sql);

			$time = microtime();
			$time = explode(' ', $time);
			$time = $time[1] + $time[0];
			$finish_time = $time;
			$total_time = round(($finish_time - $row_start_time), 4);
			
			//die("list done");
			//label the 'access' notes	
			$sql = "UPDATE `" . $data_source . "`.`note_available` 
			SET notes_case_uuid = case_uuid
			WHERE cpointer = '" . $cpointer . "'";
			$stmt = DB::run($sql);
			
			 $db->commit();
			 
			echo "last spent:" . $total_time . "<br /><br />";		
			
		//}
	}
} catch(PDOException $e) {
	$db->rollback();
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
try {	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $row_start_time), 4);
	
	//echo "Time3 spent:" . $total_time . "<br /><br />";		
	//$success = array("success"=> array("text"=>"done", "duration"=>$total_time));
	//echo json_encode($success);
	//completeds
	/*
	$sql = "SELECT COUNT(DISTINCT `notepoint`) `case_count`
	FROM `" . $data_source . "`.`note1` gcase
	WHERE 1";
	echo $sql . "\r\n<br>";
	//die();
	$stmt = DB::run($sql);
	$cases = $stmt->fetchObject();
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	echo " => fourth spent:" . $total_time . "<br /><br />";
	
	$case_count = $cases->case_count;
	*/
	$case_count = 7935;
	//completeds
	$sql = "SELECT COUNT(case_uuid) case_count
	FROM `" . $data_source . "`.`note_available`
    WHERE notes_case_uuid IS NOT NULL";
	//echo $sql . "\r\n<br>";
	//die();
	$stmt = DB::run($sql);
	$cases = $stmt->fetchObject();
	
	$completed_count = $cases->case_count;

	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	echo " => Time spent:" . $total_time . "<br />
<br />
";
	$success = array("success"=> array("text"=>"done", "duration"=>$total_time, "completed_count"=>$completed_count, "case_count"=>$case_count));
	
	if (count($cases) > 0) {
		//die("script language='javascript'>parent.runMain(" . $completed_count . "," . $case_count . ")</script");
		echo "<script language='javascript'>parent.runNotes(" . $completed_count . "," . $case_count . ")</script>";
	}
	
//, `notedesc`, , `notepoint`, `notelock`, `lockedby`, `xsoundex`, `locator`, `enteredby`, `mailpoint`, `lockloc`, `docpointer`, `doctype`
} catch(PDOException $e) {
	$db->rollback();
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
?>

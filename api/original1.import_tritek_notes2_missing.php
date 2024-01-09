<?php
include("manage_session.php");
set_time_limit(3000);

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;

include("connection.php");

?>
<script language="javascript">
parent.setFeedback("notes2 import started");
</script>
<?php
$db = getConnection();
try {
	include("customer_lookup.php");
	
	$sql_truncate = "TRUNCATE `" . $data_source . "`.`" . $data_source . "_notes2`; TRUNCATE `" . $data_source . "`.`" . $data_source . "_case_notes2`; ";
	
	echo $sql_truncate . "\r\n\r\n";

	$stmt = $db->prepare($sql_truncate);
	$stmt->execute();

	$sql = "SELECT mc.case_id, iccc.case_uuid, mc.cpointer
	FROM `" . $data_source . "`.`" . $data_source . "_case` mc 
	INNER JOIN " . $data_source . "." . $data_source . "_case iccc
	ON mc.cpointer = iccc.cpointer
	INNER JOIN `" . $data_source . "`.missings mis
	ON iccc.cpointer = mis.cpointer AND mis.notes2_done = 'N'
	WHERE 1 
	ORDER BY mc.cpointer";
	//AND mc.cpointer = 221202
	$stmt = $db->prepare($sql);
	echo $sql . "\r\n\r\n";
	$stmt->execute();
	
	
	$cases = $stmt->fetchAll(PDO::FETCH_OBJ);
	$arrCaseUUID =  array();
	//die("count:". count($cases));
	foreach($cases as $key=>$case){
		//die(print_r($case));
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$row_start_time = $time;
		
		$cpointer = $case->cpointer;
		echo "Processing -> " . $key. " == " . $cpointer . "  ";
		if (in_array($cpointer, $arrCaseUUID)) {
			//one time per pointer
			continue;
		} 
		$arrCaseUUID[] = $cpointer;
		
		$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_notes2` (`case_notes_uuid`, `case_uuid`,  `notes_counter`, `notes_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
		SELECT '', '" . $case->case_uuid . "', @curRow := @curRow + 1, 
		CONCAT('" . $customer_id . "', 'n_', @curRow,'_', '_" . $cpointer . "_', `notepoint`) AS `notes_uuid`, 'general2', STR_TO_DATE(  `notedate` ,  '%m/%d/%Y' ) notedate, 'system', 'N', " . $customer_id . "
		FROM 
		
		 (
SELECT * 
FROM `" . $data_source . "`.note2 
where notepoint = '" . $cpointer . "'
UNION
SELECT * 
FROM `" . $data_source . "`.note3 
where notepoint = '" . $cpointer . "'
UNION
SELECT * 
FROM `" . $data_source . "`.note4 
where notepoint = '" . $cpointer . "'
UNION
SELECT * 
FROM `" . $data_source . "`.note5 
where notepoint = '" . $cpointer . "'
UNION
SELECT * 
FROM `" . $data_source . "`.note6
where notepoint = '" . $cpointer . "'
) allnotes
		
		JOIN    (SELECT @curRow := 0) r
		WHERE `notepoint` = '" . $cpointer . "'
		AND notedesc != ''
		AND notedesc NOT LIKE 'Folder accessed%'
		AND notedate != '  /  /'
		AND notedate != ''
		AND LENGTH(STR_TO_DATE(  `notedate` ,  '%m/%d/%Y' )) = 10
		AND YEAR(STR_TO_DATE(  `notedate` ,  '%m/%d/%Y' )) > 1970";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		//echo $sql . "\r\n\r\n";
		$stmt->execute();
		
		//older dbs don't have this field
		//IFNULL(enteredby, '') enteredby
		
		$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_notes2` (`notes_counter`, `notes_uuid`, `note`, `dateandtime`, `entered_by`, `customer_id`, `type`)
		SELECT @curRow := @curRow + 1, 
		CONCAT('" . $customer_id . "', 'n_', @curRow,'_', '_" . $cpointer . "_', `notepoint`) AS `notes_uuid`, `notedesc`,  STR_TO_DATE(  `notedate` ,  '%m/%d/%Y' ) notedate, 'system' enteredby, 
		'" . $customer_id . "', 'general2'
		FROM 
		
		 (
SELECT * 
FROM `" . $data_source . "`.note2 
where notepoint = '" . $cpointer . "'
UNION
SELECT * 
FROM `" . $data_source . "`.note3 
where notepoint = '" . $cpointer . "'
UNION
SELECT * 
FROM `" . $data_source . "`.note4 
where notepoint = '" . $cpointer . "'
UNION
SELECT * 
FROM `" . $data_source . "`.note5 
where notepoint = '" . $cpointer . "'
UNION
SELECT * 
FROM `" . $data_source . "`.note6
where notepoint = '" . $cpointer . "'
) allnotes
		
		JOIN    (SELECT @curRow := 0) r
		WHERE notepoint = '" . $cpointer . "'
		AND notedesc != ''
		AND notedesc NOT LIKE 'Folder accessed%'
		AND notedate != '  /  /'
		AND notedate != ''
		AND LENGTH(STR_TO_DATE(  `notedate` ,  '%m/%d/%Y' )) = 10
		AND YEAR(STR_TO_DATE(  `notedate` ,  '%m/%d/%Y' )) > 1970";
		
		echo $sql . "\r\n\r\n";
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $row_start_time), 4);
		echo " => Time spent:" . $total_time . "<br />
<br />
";
	}
	
	//die("list done");
	//label the 'access' notes
	$sql_access = "UPDATE `" . $data_source . "`.`" . $data_source . "_notes2` 
	SET `type` = 'access2'
	WHERE `note` LIKE '%Folder accessed%' OR `note` LIKE '%Ltr%'";
	$stmt = $db->prepare($sql_access);
	//echo $sql . "\r\n\r\n";
	$stmt->execute();
	
	$db = null;
	
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
?>
<script language="javascript">
parent.setFeedback("notes 2 import completed");
</script>
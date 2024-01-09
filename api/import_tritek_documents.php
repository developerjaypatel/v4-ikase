<?php
require_once('../shared/legacy_session.php');
set_time_limit(10000);
if (ob_get_level() == 0) ob_start();

include("connection.php");

$customer_id = passed_var("customer_id", "get");
if (!is_numeric($customer_id)) {
	die();
}

try {
	$db = getConnection();
	
	include("customer_lookup.php");
	
	$sql = "SELECT 
    cpointer, subtable.case_uuid, case_notes.case_uuid notes_case_uuid
	FROM
    
	(SELECT DISTINCT
        mc.cpointer, mc.case_uuid
    FROM
        `" . $data_source . "`.`" . $data_source . "_case` mc
    INNER JOIN `" . $data_source . "`.`document` ON mc.cpointer = document.cpointer
    WHERE
        1 
		AND document.cpointer regexp '^[0-9]'
	) subtable
    
	LEFT OUTER JOIN (
		SELECT DISTINCT
            case_uuid
        FROM
            " . $data_source . "." . $data_source . "_case_notes
            WHERE `attribute` = 'document'
	) case_notes
	
	ON subtable.case_uuid = case_notes.case_uuid

WHERE
    1
       AND case_notes.case_uuid IS NULL 
LIMIT 0 , 1";
	
	//echo $sql . "<br /><br />\r\n\r\n";
	//die();
	$stmt = DB::run($sql);
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$header_start_time = $time;

	$cases = $stmt->fetchAll(PDO::FETCH_OBJ);
	$note_number = "10";
	if(count($cases)==0) {
		die("done");
	}
	foreach($cases as $key=>$case){
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$row_start_time = $time;
		
		echo "<br>Processing -> " . $key. " == " . $case->cpointer . " - ";
		
		$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_notes` (`case_notes_uuid`, `case_uuid`,  `notes_counter`, `notes_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
		SELECT '" . $case->case_uuid . "', '" . $case->case_uuid . "', @curRow := @curRow + 1, 
		CONCAT('doc_', @curRow,'_',cpointer, '_', '" . $customer_id . "','_', IFNULL(docpointer, REPLACE(`date`,'/',''))) AS `notes_uuid`, 'document', STR_TO_DATE(  `date` ,  '%m/%d/%Y' ) notedate, 'system', 'N', " . $customer_id . "
		FROM `" . $data_source . "`.`document`
		JOIN    (SELECT @curRow := 0) r
		WHERE `cpointer` = '" . $case->cpointer . "'";
		//echo $sql . "<br /><br />\r\n\r\n";
		//die();
		$stmt = DB::run($sql);
		
		$sql = "
		INSERT INTO `" . $data_source . "`.`" . $data_source . "_notes` (`notes_counter`, `notes_uuid`, `type`, `subject`, `note`, `title`, 
		`attachments`, `entered_by`, `dateandtime`, `customer_id`)
		SELECT @curRow := @curRow + 1,
		CONCAT('doc_', @curRow,'_',cpointer, '_', '" . $customer_id . "','_', IFNULL(docpointer, REPLACE(`date`,'/',''))) AS `notes_uuid`, 
		'document', IFNULL(`desc`, '') `subject`, 
		CONCAT(IFNULL(`path`,''), '\r\n', IFNULL(`desc1`,'')) `note`, 
		IFNULL(`path`,'') `path`, IFNULL(`path`,'') `path`, IFNULL(`author`,'') `author`, STR_TO_DATE(  `date` ,  '%m/%d/%Y' ) `date`, 
		'" . $customer_id . "' 
		FROM `" . $data_source . "`.`document` 
		JOIN    (SELECT @curRow := 0) r
		WHERE 1
		AND `cpointer` = '" . $case->cpointer . "'";
		
		//echo $sql . "<br />\r\n\r\n";
		$stmt = DB::run($sql);
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $row_start_time), 4);
		
		/*
		echo "<script language='javascript'>parent.updateCounter(" . count($cases) . "," . ($key+1) . ")</script>";
		*/

	}
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	//completeds
	$sql = "SELECT COUNT(DISTINCT `cpointer`) `case_count`
	FROM `" . $data_source . "`.`document` gcase
	WHERE 1";
	//echo $sql . "\r\n<br>";
	//die();
	$stmt = DB::run($sql);
	$cases = $stmt->fetchObject();
	
	$case_count = $cases->case_count;
	
	//completeds
	$sql = "SELECT COUNT(DISTINCT case_uuid) case_count
	FROM `" . $data_source . "`.`" . $data_source . "_case_notes` ggc
	WHERE 1
	AND attribute = 'document'";
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
	
	$success = array("success"=> array("text"=>"done", "duration"=>$total_time, "completed_count"=>$completed_count, "case_count"=>$case_count));
	
	echo "Time spent:" . $total_time . "<br />
<br />
";

	if (count($cases) > 0) {
		//die("script language='javascript'>parent.runMain(" . $completed_count . "," . $case_count . ")</script");
		echo "<script language='javascript'>parent.runDocuments(" . $completed_count . "," . $case_count . ")</script>";
	}
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}

//include("cls_logging.php");

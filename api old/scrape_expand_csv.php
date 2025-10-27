<?php
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

//expand multiple applicant law firms into multiple court calendar with unique applicant law firm
//do for defense
include("connection.php");

try {
	//get the max import date
	$sql = "SELECT MAX(import_date) import_date
	FROM ikase.cse_courtcalendar ccc";
	$db = getConnection();
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $import = $stmt->fetchObject();
    $stmt->closeCursor();    
    $db = null; $stmt = null;
//	die(print_r($import));
	//now let's deal with the multi law firms
	$sql = "SELECT ccc.*
	FROM ikase.cse_courtcalendar ccc
	WHERE 1
	AND applicant_law_firm LIKE '%\n%'
	AND customer_id = 0
	AND import_date = '" . $import->import_date . "'
	LIMIT 0, 5500";

	$db = getConnection();
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $applicants = $stmt->fetchAll(PDO::FETCH_OBJ);
    $stmt->closeCursor();    
    $db = null; $stmt = null;
	//die(print_r($applicants));
	$sql = "INSERT INTO ikase.cse_courtcalendar 
(`office`, `judge_name`, `worker_name`, `legacy_case_number`, `case_number`, `hearing_type`, `applicant_law_firm`, `defense_law_firm`, `hearing_time`, `hearing_location`, `import_date`)
	VALUES ";

	$arrValues = array();
	$arrCourtId = array();
	$counter = 0;
	foreach($applicants as $calendar) {
		//update the customer id to indicate that it was expanded
		/*
		$sql_update = "UPDATE ikase.cse_courtcalendar
		SET customer_id = -99
		WHERE courtcalendar_id = " . $calendar->courtcalendar_id;
		$db = getConnection();
		$stmt = $db->prepare($sql_update);
		$stmt->execute();
		$db = null; $stmt = null;
		*/
		$arrCourtId[] = $calendar->courtcalendar_id;
		//print_r($calendar);
		$firms = $calendar->applicant_law_firm;
		$arrFirms = explode("\n", $firms);
		foreach($arrFirms as $firm) {
			//insert new rows 1 for each firm
			$values = "
			('" . $calendar->office . "', '" . addslashes($calendar->judge_name) . "', '" . addslashes($calendar->worker_name) . "', '" . $calendar->legacy_case_number . "', '" . $calendar->case_number . "', '" . $calendar->hearing_type . "', '" . addslashes($firm) . "', '" . addslashes($calendar->defense_law_firm) . "', '" . $calendar->hearing_time . "', '" . $calendar->hearing_location . "', '" . $import->import_date . "')"; 
			
			$arrValues[] = $values;
			
		}
		$counter++;
	}
	if (count($arrValues) > 0) {
		$sql .= implode(",\r\n", $arrValues);
		//die($sql);
		$db = getConnection(); 		
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		$stmt = null; $db = null;
	}
	if (count($arrCourtId) > 0) {
		$sql_update = "UPDATE ikase.cse_courtcalendar
		SET customer_id = -99
		WHERE courtcalendar_id IN (" . implode(", ", $arrCourtId) . ")
		AND import_date = '" . $import->import_date . "'";
		$db = getConnection();
		$stmt = $db->prepare($sql_update);
		$stmt->execute();
		$db = null; $stmt = null;
	}
	//die($sql);
} catch(PDOException $e) {
	echo $sql . "\r\n";
	echo '{"error":{"text":'. $e->getMessage() .'}}'; 
}

//die();
//now let's deal with the multi law firms
$sql = "SELECT ccc.*
FROM ikase.cse_courtcalendar ccc
WHERE 1
AND defense_law_firm LIKE '%\n%'
AND applicant_law_firm NOT LIKE '%\n%' 
AND customer_id = 0
AND import_date = '" . $import->import_date . "'
LIMIT 0, 5500";

try {
	$db = getConnection();
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $defenses = $stmt->fetchAll(PDO::FETCH_OBJ);
    $stmt->closeCursor();    
    $db = null; $stmt = null;
	
	$sql = "INSERT INTO ikase.cse_courtcalendar 
(`office`, `judge_name`, `worker_name`, `legacy_case_number`, `case_number`, `hearing_type`, `applicant_law_firm`, `defense_law_firm`, `hearing_time`, `hearing_location`, `import_date`)
	VALUES ";
	$arrValues = array();
	$arrCourtId = array();
	$counter = 0;
	foreach($defenses as $calendar) {
		//print_r($calendar);
		//update the customer id to indicate that it was expanded
		$arrCourtId[] = $calendar->courtcalendar_id;
		
		$firms = $calendar->defense_law_firm;
		$arrFirms = explode("\n", $firms);
		foreach($arrFirms as $firm) {
			//insert new rows 1 for each firm
			$values = "
			 ('" . $calendar->office . "', '" .addslashes( $calendar->judge_name) . "', '" . addslashes($calendar->worker_name) . "', '" . $calendar->legacy_case_number . "', '" . $calendar->case_number . "', '" . $calendar->hearing_type . "', '" . addslashes($calendar->applicant_law_firm) . "', '" . addslashes($firm) . "', '" . $calendar->hearing_time . "', '" . $calendar->hearing_location . "', '" . $import->import_date . "')"; 
			
			$arrValues[] = $values;
		}
		$counter++;
		if ($counter > 3) {
			//break;
		}
	}
	if (count($arrValues) > 0) {
		$sql .= implode(",\r\n", $arrValues);
		//die($sql);
		$db = getConnection(); 		
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		$stmt = null; $db = null;
	}
	if (count($arrCourtId) > 0) {
		$sql_update = "UPDATE ikase.cse_courtcalendar
		SET customer_id = -99
		WHERE courtcalendar_id IN (" . implode(", ", $arrCourtId) . ")
		AND import_date = '" . $import->import_date . "'";
		$db = getConnection();
		$stmt = $db->prepare($sql_update);
		$stmt->execute();
		$db = null; $stmt = null;
	}
	/*
	$fp = fopen('scrape_data.txt', 'a+');
	fwrite($fp, 'expand csv  @ ' . date('m/d/y H:i:s') . chr(10));
	fclose($fp); 
	*/
	$found = count($applicants) + count($defenses);
	if ($found==0) {
		/*
		//stop 9/18/2018, setcronjob is awesome
		$fp = fopen('scrape_data.txt', 'a+');
		fwrite($fp, 'expand csv DONE  @ ' . date('m/d/y H:i:s') . chr(10));
		fclose($fp);
		*/
		//next is scrape_transfer_csv
		$params = array();
		$url = "https://v2.ikase.org/api/scrape_transfer_csv.php";
		//echo $url;
		curl_post_async($url, $params);
		//die("all done");
	} else {
		$params = array();
		$url = "https://v2.ikase.org/api/scrape_expand_csv.php";
		//echo $url;
		curl_post_async($url, $params);
	}
	
	echo "processed: " . count($applicants) . ", " . count($defenses) . "\r\n";
} catch(PDOException $e) {
	echo $sql . "\r\n";
	echo '{"error":{"text":'. $e->getMessage() .'}}'; 
}
?>
<?php
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

//die(date("Y-m-d H:i:s", strtotime("Jun 15, 2017 1:30 PM")));
include("connection.php");

$targetFile = "../uploads/courtcalendar/courtcalendar.csv";


/*
$sql = "DELETE FROM ikase.cse_courtcalendar
WHERE event_uuid = ''";
*/
/*
try {
	$sql = "TRUNCATE ikase.cse_courtcalendar";
	
	$db = getConnection(); 		
	$stmt = $db->prepare($sql);  
	$stmt->execute();
	$stmt = null; $db = null;
	
	echo "TRUNCATED\r\n";
} catch(PDOException $e) {
	echo '{"error":{"text":'. $e->getMessage() .'}}'; 
}
*/
$import_date = date("Y-m-d H:i:s");
$sql = "INSERT INTO ikase.cse_courtcalendar 
(`office`, `judge_name`, `worker_name`, `legacy_case_number`, `case_number`, `hearing_type`, `applicant_law_firm`, `defense_law_firm`, `hearing_time`, `hearing_location`, `import_date`)
VALUES ";
//die($sql);
if (($handle = fopen($targetFile, "r")) !== FALSE) {
	$arrFields = array();
	$counter = 0;
	$arrValues = array();
	while (($data = fgetcsv($handle, 5000, ",")) !== FALSE) {
		$counter++;
		if ($counter < 6) {
			continue;
		}
		
		//if ($counter==37) {
		//fix 13:00PM to 1:00PM
		$arrDate = explode(" ", $data[8]);
		if ($arrDate[4]=="PM") {
			//are we over
			$arrTime = explode(":", $arrDate[3]);
			if ($arrTime[0] > 12) {
				$arrTime[0] = $arrTime[0] - 12;
			}
			$arrDate[3] = implode(":", $arrTime);
			$data[8] = implode(" ", $arrDate);
		}
		//die(print_r($data));
		//}
		$values = "";
		$arrValueRow = array();
		foreach($data as $dindex=>$datum) {
			//$value =  "'" . addslashes($datum) . "'";
			if ($dindex!=8) {
				if ($dindex==2) {
					if (strlen($datum) >45) {
						$datum = substr($datum, 0, 45);
					}
				}
				$arrValueRow[] = addslashes($datum);
			} else {
				$arrValueRow[] = date("Y-m-d H:i:s", strtotime($datum));
			}
		}
		$values =  "('" . implode("', '", $arrValueRow) . "', '" . $import_date . "')";
		//die($values);
		//echo $counter . " =>" . $values . "\r\n";
		//die($values);
		$arrValues[] = $values;
		/*
		if ($counter >50) {
			break;
		}
		*/
	}
	$sql .= implode(",\r\n", $arrValues);
	//die($sql);
	try {
		$db = getConnection(); 		
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		$stmt = null; $db = null;
		
		echo "imported at" . date("m/d/y H:i:s") . "\r\n";
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		die("error:
		" . $e->getMessage());
	}
	/*
	$fp = fopen('scrape_data.txt', 'a+');
	fwrite($fp, 'read csv  @ ' . date('m/d/y H:i:s') . chr(10));
	fclose($fp); 
	*/
	//die("read");
	//next is scrape_expand_csv
	$params = array();
	curl_post_async("https://www.ikase.org/api/scrape_expand_csv.php", $params);
}
?>
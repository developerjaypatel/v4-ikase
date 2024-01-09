<?php
set_time_limit(300);
//error_reporting(E_ALL);
//ini_set('display_errors', '1');

$msg = "\r\nreps update started:" . date("m/d/Y H:i:s");
$fp = fopen('eams_update.txt', 'a+');
fwrite($fp, $msg);
fclose($fp);

include("connection.php");


$filename = "http://www.dir.ca.gov/ftproot/EAMSReps.txt";
$somecontent = file_get_contents($filename);

$arrRows = explode("\r\n", $somecontent);
$db = getConnection();

try {
	//change them all to inactive
	$query_update = "UPDATE ikase.`cse_eams_reps` SET active = 'N'";
	$stmt = $db->prepare($query_update);
	$stmt->execute();
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}

foreach($arrRows as $key=> $row) {
	$row = addslashes($row);
	$row = str_replace(chr(13), "", $row);	
	$arrRow = explode("	", $row);
	if (is_numeric($arrRow[0])) {
		$search_eams = $arrRow[0];
		
		//first let's check if it's already here
		$query = "SELECT `rep_id`, `last_update` FROM ikase.`cse_eams_reps` WHERE `eams_ref_number` = '" . $search_eams . "'";
		try {
			$stmt = $db->prepare($query);
			$stmt->execute();
			
			$eams_rep = $stmt->fetchObject();
			$query_add = "";
			if (!is_object($eams_rep)) {
				$query_add = "INSERT INTO ikase.`cse_eams_reps` (`rep_uuid`, `eams_ref_number`, `firm_name`, `street_1`, `city`, `state`, `zip_code`, `phone`, `service_method`, `last_update`, `last_import_date`)
	VALUES ";
				if ($arrRow[0]!="") {
					$query_add .= "('" . $search_eams . "', '" . implode("','", $arrRow) . "', '" . date("Y-m-d H:i:s") . "')";
				}
				//die($query_add);
			} else {
				$rep_id = $eams_rep->rep_id;
				$last_update = $eams_rep->last_update;
				
				if ($last_update!=$arrRow[8]) {
					$query_add = "UPDATE ikase.`cse_eams_reps` 
					SET `firm_name` = '" . $arrRow[1] . "', 
					`street_1` = '" . $arrRow[2] . "', 
					`street_2` = '', 
					`city` = '" . $arrRow[3] . "', 
					`state` = '" . $arrRow[4] . "', 
					`zip_code` = '" . $arrRow[5] . "', 
					`phone` = '" . $arrRow[6] . "', 
					`service_method` = '" . $arrRow[7] . "', 
					`last_update` = '" . $arrRow[8] . "'
					WHERE `rep_id` = '" . $rep_id . "'";
				}
			}
			if ($query_add!="") {
				echo $query_add . "<br>";
				$stmt = $db->prepare($query_add);
				$stmt->execute();
			}
			
			$query_add = "UPDATE ikase.`cse_eams_reps` 
			SET `active` = 'Y',
			`last_import_date` = '" . date("Y-m-d H:i:s") . "'
			WHERE `rep_id` = '" . $rep_id . "'";
			echo $query_add . "<br>";
			$stmt = $db->prepare($query_add);
			$stmt->execute();
		} catch(PDOException $e) {
			$error = array("error"=> array("text"=>$e->getMessage()));
				echo json_encode($error);
		}
	}
}

$db = null;
set_time_limit(30);
set_time_limit(30);

$msg = "\r\nreps update done:" . date("m/d/Y H:i:s");
$fp = fopen('eams_update.txt', 'a+');
fwrite($fp, $msg);
fclose($fp);
?>

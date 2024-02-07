<?php
set_time_limit(300);
//error_reporting(E_ALL);
//ini_set('display_errors', '1');

$fp = fopen('eams_update.txt', 'a+');
fwrite($fp, '\r\nclaimants start @ ' . date('m/d/y H:i:s'));
fclose($fp);

include("connection.php");

try {
	//change them all to inactive
	$query = "UPDATE `ikase`.`cse_eams_claimants` 
	SET active = 'N'
	WHERE `last_import_date` < '" . date("Y-m-d") . "'";
	//$result = mysql_query($query, $r_link) or die("unable to deactivate");

	$db = getConnection();
	$stmt = $db->prepare($query);
	$stmt->execute();
	$stmt->closeCursor(); 
	$stmt = null; $db = null;

	$filename = "http://www.dir.ca.gov/ftproot/EAMSLienClaimants.txt";
	$somecontent = file_get_contents($filename);

	$arrRows = explode("\r\n", $somecontent);
	foreach($arrRows as $key=> $row) {
		$row = addslashes($row);
		$row = str_replace(chr(13), "", $row);	
		$arrRow = explode("	", $row);
		
		if (is_numeric($arrRow[0])) {
			//die(print_r($arrRow));
			
			$search_eams = $arrRow[0];
			//die("[" . $search_eams . "]");
			//first let's check if it's already here
			$query = "SELECT `claimant_id`, `last_update` FROM `ikase`.`cse_eams_claimants` WHERE `eams_ref_number` = '" . $search_eams . "'";
			
			$db = getConnection();
			$stmt = $db->prepare($query);
			$stmt->execute();
			$result_claimant = $stmt->fetchObject();
			$stmt->closeCursor(); 
			$stmt = null; $db = null;
			
			// $result = mysql_query($query, $r_link) or die("unable to find ref number");
			if (is_object($result_claimant)) {
				$numbs = count($result_claimant);
			} else {
				$numbs = 0;
			}
			if ($numbs==0) {
				//print_r($arrRow);
				//no street_2 6/19/2017
				$query_add = "INSERT INTO `ikase`.`cse_eams_claimants` (`eams_ref_number`, `firm_name`, `street_1`, `city`, `state`, `zip_code`, `phone`, `service_method`, `last_update`, `last_import_date`, `street_2`)
		VALUES ";
				if ($arrRow[0]!="") {
					$query_add .= "('" . implode("','", $arrRow) . "', '" . date("Y-m-d H:i:s") . "', '')";
				}
				//add it
				echo $query_add . "<br />";
				
				$db = getConnection();
				$stmt = $db->prepare($query_add);
				$stmt->execute();
				// $result = $stmt->fetchObject();
				$stmt->closeCursor(); 
				$stmt = null; $db = null;
			} else {
				$claimant_id = $result_claimant->claimant_id;
				$last_update = $result_claimant->last_update;
				//die(print_r($arrRow));
				if ($last_update!=$arrRow[8]) {
					$query_add = "UPDATE `ikase`.`cse_eams_claimants` 
					SET `firm_name` = '" . $arrRow[1] . "', 
					`street_1` = '" . $arrRow[2] . "', 
					`street_2` = '', 
					`city` = '" . $arrRow[3] . "', 
					`state` = '" . $arrRow[4] . "', 
					`zip_code` = '" . $arrRow[5] . "', 
					`phone` = '" . $arrRow[6] . "', 
					`last_update` = '" . $arrRow[8] . "'
					WHERE `claimant_id` = '" . $claimant_id . "'";
					//die($query_add . "<br />");
					$db = getConnection();
					$stmt = $db->prepare($query_add);
					$stmt->execute();
					// $result = $stmt->fetchObject();
					$stmt->closeCursor(); 
					$stmt = null; $db = null;
				}
				
				$query_add = "UPDATE `ikase`.`cse_eams_claimants` 
				SET `active` = 'Y',
				`last_import_date` = '" . date("Y-m-d H:i:s") . "'
				WHERE `claimant_id` = '" . $claimant_id . "'";
				echo $query_add . "<br />";
				//die();
				$db = getConnection();
				$stmt = $db->prepare($query_add);
				$stmt->execute();
				// $result = $stmt->fetchObject();
				$stmt->closeCursor(); 
				$stmt = null; $db = null;
			}
		}
	} 
} catch(PDOException $e) {
    $error = array("error"=> array("text"=>$e->getMessage()));
    die(json_encode($error));
}

set_time_limit(30);
$msg = "\r\nclaimants update done:" . date("m/d/Y H:i:s");
//include("classes/cls_logging.php");

$fp = fopen('eams_update.txt', 'a+');
fwrite($fp, $msg);
fclose($fp);
?>

<?php
set_time_limit(300);
error_reporting(E_ALL);
ini_set('display_errors', '1');

$fp = fopen('eams_update.txt', 'a+');
fwrite($fp, '\r\ncarriers start @ ' . date('m/d/y H:i:s'));
fclose($fp);

include("connection.php");

$filename = "http://www.dir.ca.gov/ftproot/EAMSClaimsAdmins.txt";
$somecontent = file_get_contents($filename);

$arrRows = explode("\r\n", $somecontent);
$db = getConnection();
try {
	//change them all to inactive
	$query_update = "UPDATE ikase.`cse_eams_carriers` SET active = 'N'";
	$stmt = DB::run($query_update);
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
		//die(print_r($arrRow));
		//first let's check if it's already here
		$query = "SELECT `carrier_id`, `last_update` FROM ikase.`cse_eams_carriers` WHERE `eams_ref_number` = '" . $search_eams . "'";
		try {
			$stmt = $db->prepare($query);
			//$stmt->bindParam("search_term", $search_term);
			$stmt->execute();
			$eams_carrier = $stmt->fetchObject();
			
			//initialize
			$query_add = "";
			if (!is_object($eams_carrier)) {
				$query_add = "INSERT INTO ikase.`cse_eams_carriers` (`carrier_uuid`, `eams_ref_number`, `firm_name`, `street_1`, `city`, `state`, `zip_code`, `phone`, `service_method`, `last_update`, `last_import_date`)
	VALUES ";
				if ($arrRow[0]!="") {
					$query_add .= "('" . $search_eams . "', '" . implode("','", $arrRow) . "', '" . date("Y-m-d H:i:s") . "')";
				}
			} else {
				$carrier_id = $eams_carrier->carrier_id;
				$last_update = $eams_carrier->last_update;
				
				if ($last_update!=$arrRow[8]) {
					$query_add = "UPDATE ikase.`cse_eams_carriers` 
					SET `firm_name` = '" . $arrRow[1] . "', 
					`street_1` = '" . $arrRow[2] . "', 
					`street_2` = '', 
					`city` = '" . $arrRow[3] . "', 
					`state` = '" . $arrRow[4] . "', 
					`zip_code` = '" . $arrRow[5] . "', 
					`phone` = '" . $arrRow[6] . "', 
					`service_method` = '" . $arrRow[7] . "', 
					`last_update` = '" . $arrRow[8] . "'
					WHERE `carrier_id` = '" . $carrier_id . "'";
				}
				$query_update = "UPDATE ikase.`cse_eams_carriers` 
				SET `active` = 'Y',
				`last_import_date` = '" . date("Y-m-d H:i:s") . "'
				WHERE `carrier_id` = '" . $carrier_id . "'";
				
				echo $query_update . "<br>";
				$stmt = DB::run($query_update);
			}
			if ($query_add!="") {
				echo $query_add . "<br />";
				$stmt = DB::run($query_add);
			}
			
			
		} catch(PDOException $e) {
			$error = array("error"=> array("text"=>$e->getMessage()));
				echo json_encode($error);
		}
	}
}
set_time_limit(30);

$msg = "\r\ncarrier update done:" . date("m/d/Y H:i:s");
$fp = fopen('eams_update.txt', 'a+');
fwrite($fp, $msg);
fclose($fp);


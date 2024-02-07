<?php
set_time_limit(300);
//error_reporting(E_ALL);
//ini_set('display_errors', '1');

include("../../eamsjetfiler/datacon.php");
include("../../eamsjetfiler/functions.php");

$filename = "http://www.dir.ca.gov/ftproot/EAMSClaimsAdmins.txt";
$somecontent = file_get_contents($filename);

$arrRows = explode("\r\n", $somecontent);
foreach($arrRows as $key=> $row) {
	$row = addslashes($row);
	$row = str_replace(chr(13), "", $row);	
	$arrRow = explode("	", $row);
	if (is_numeric($arrRow[0])) {
		$search_eams = $arrRow[0];
		
		//first let's check if it's already here
		$query = "SELECT `carrier_id` FROM `cse_eams_carriers` WHERE `eams_ref_number` = '" . $search_eams . "'";
		$result = mysql_query($query, $r_link) or die("unable to find ref number");
		$numbs = mysql_numrows($result);
		if ($numbs==0) {
			$query_add = "INSERT INTO ikase.`cse_eams_carriers` (`eams_ref_number`, `firm_name`, `street_1`, `street_2`, `city`, `state`, `zip_code`, `phone`, `service_method`, `last_update`)
	VALUES ";
			if ($arrRow[0]!="") {
				$query_add .= "('" . implode("','", $arrRow) . "')";
			}
			//add it
			//die($query_add);
			$result_add = mysql_query($query_add, $r_link) or die("unable to insert into carriers");
		} else {
			$carrier_id = mysql_result($result, 0, "carrier_id");
			
			$query_add = "UPDATE ikase.`cse_eams_carriers` 
			SET `firm_name` = '" . $arrRow[1] . "', 
			`street_1` = '" . $arrRow[2] . "', 
			`street_2` = '" . $arrRow[3] . "', 
			`city` = '" . $arrRow[4] . "', 
			`state` = '" . $arrRow[5] . "', 
			`zip_code` = '" . $arrRow[6] . "', 
			`phone` = '" . $arrRow[7] . "', 
			`service_method` = '" . $arrRow[8] . "', 
			`last_update` = '" . $arrRow[9] . "'
			WHERE `carrier_id` = '" . $carrier_id . "'";
			//i'm not doing updates yet, just add
			/*
			if ($search_eams=="5017387"){
				die($query_add);
			}
			*/
			$result_add = mysql_query($query_add, $r_link) or die("unable to insert into carriers");
		}
	}
}

set_time_limit(30);
die("carrier update done:" . date("m/d/Y H:i:s"));
?>

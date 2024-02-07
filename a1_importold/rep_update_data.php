<?php
set_time_limit(300);
//error_reporting(E_ALL);
//ini_set('display_errors', '1');

include("../../eamsjetfiler/datacon.php");
include("../../eamsjetfiler/functions.php");

$filename = "http://www.dir.ca.gov/ftproot/EAMSReps.txt";
$somecontent = file_get_contents($filename);

$arrRows = explode("\r\n", $somecontent);
foreach($arrRows as $key=> $row) {
	$row = addslashes($row);
	$row = str_replace(chr(13), "", $row);	
	$arrRow = explode("	", $row);
	if (is_numeric($arrRow[0])) {
		$search_eams = $arrRow[0];
		
		//first let's check if it's already here
		$query = "SELECT `rep_id` FROM `cse_eams_reps` WHERE `eams_ref_number` = '" . $search_eams . "'";
		$result = mysql_query($query, $r_link) or die("unable to find ref number");
		$numbs = mysql_numrows($result);
		if ($numbs==0) {
			$query_add = "INSERT INTO `cse_eams_reps` (`eams_ref_number`, `firm_name`, `street_1`, `street_2`, `city`, `state`, `zip_code`, `phone`, `service_method`, `last_update`)
	VALUES ";
			if ($arrRow[0]!="") {
				$query_add .= "('" . implode("','", $arrRow) . "')";
			}
			//add it
			//die($query_add);
			$result_add = mysql_query($query_add, $r_link) or die("unable to insert into carriers");
		} else {
			$rep_id = mysql_result($result, 0, "rep_id");
			
			$query_add = "UPDATE `cse_eams_reps` 
			SET `firm_name` = '" . $arrRow[1] . "', 
			`street_1` = '" . $arrRow[2] . "', 
			`street_2` = '" . $arrRow[3] . "', 
			`city` = '" . $arrRow[4] . "', 
			`state` = '" . $arrRow[5] . "', 
			`zip_code` = '" . $arrRow[6] . "', 
			`phone` = '" . $arrRow[7] . "', 
			`service_method` = '" . $arrRow[8] . "', 
			`last_update` = '" . $arrRow[9] . "'
			WHERE `rep_id` = '" . $rep_id . "'";
			//i'm not doing updates yet, just add
			//die($query_add);
			$result_add = mysql_query($query_add, $r_link) or die("unable to insert into reps");
		}
	}
}

set_time_limit(30);
die("rep update done:" . date("m/d/Y H:i:s"));
?>

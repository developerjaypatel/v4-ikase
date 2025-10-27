<?php
include("manage_session.php");
set_time_limit(3000);
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', '1');

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;

include("connection.php");
include("cls_dbf.php");

$db = getConnection();
try {
	include("customer_lookup.php");
	$data_source = "legacy";
	
	$dir = "C:\\Users\\Gerry Asher\\My Backup Files\\" . $data_source . "\\CLIENTS";
	//let's open the case folders, and then cycle through them
	$files1 = scandir($dir);
	$arrFiles = array();
	foreach($files1 as $file1) {
		if (is_numeric($file1)) {
			$files2 = scandir($dir. "\\" . $file1);
			foreach($files2 as $file2) {
				if (is_numeric($file2)) {
					$files3 = scandir($dir. "\\" . $file1. "\\" . $file2);
					foreach($files3 as $file3) {
						$strpos = strpos(strtolower($file3), "caseact.dbf");
						if ($strpos!==false) {
							$arrFiles[] = $dir. "\\" . $file1. "\\" . $file2 . "\\" . $file3;
						}
					}
				}
			}
		}
	}
	//die(print_r($arrFiles));
	foreach($arrFiles as $file) {
		$dbf = $file;
		$fpt = str_replace(".dbf", ".fpt", $dbf);
		echo "Processing " . $dbf . "\r\n";
		if (file_exists($fpt)) {
			//$Test = new Prodigy_DBF($dir . "caseact.dbf", $dir . "caseact.fpt");
			$Test = new Prodigy_DBF($dbf, $fpt);
			while(($Record = $Test->GetNextRecord(true)) and !empty($Record)) {
				//print_r($Record);
				if ($Record["EVENT"]!="") {
					$sql = "UPDATE `" . $db . "`.caseact
					SET `EVENT` = '" . addslashes($Record["EVENT"]) . "'
					WHERE ACTNO = " . $Record["ACTNO"] . "
					AND CASENO = " . $Record["CASENO"];
					echo $sql . "\r\n";
					$stmt = $db->prepare($sql_truncate);
					$stmt->execute();	
				}
			}
		}
	}
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
$db = null;
include("cls_logging.php");
?>
<?php
include("manage_session.php");
set_time_limit(3000);

/**
* ----------------------------------------------------------------
*			XBase
*			test.php	
* 
*  Developer        : Erwin Kooi
*  released at      : Nov 2005
*  last modified by : Erwin Kooi
*  date modified    : Jan 2005
*                                                               
*  Info? Mail to info@cyane.nl
* 
* --------------------------------------------------------------
*
* Basic demonstration
* download the sample tables from:
* http://www.cyane.nl/phpxbase.zip
*
**/
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', '1');

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;

include("connection.php");

include("cls_dbf.php");
/* load the required classes */
require_once "Column.class.php";
require_once "Record.class.php";
require_once "Table.class.php";

$db = getConnection();
try {
	include("customer_lookup.php");
	$data_source = "legacy";

	$sql_truncate = "TRUNCATE `" . $data_source . "`.`caseact`;
	TRUNCATE `" . $data_source . "`.`injury`;
	TRUNCATE `" . $data_source . "`.`user1`;";
	echo $sql_truncate . "\r\n";
	//$stmt = $db->prepare($sql_truncate);
	//$stmt->execute();	

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
						$strpos = strpos(strtolower($file3), ".dbf");
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
		/* create a table object and open it */
		$table = new XBaseTable($file);
		$table->open();
		
		//table name
		$arrName = explode("\\", $file);
		$tablename = $arrName[count($arrName)-1];
		$tablename = str_replace(".DBF", "", $tablename);
		$tablename = str_replace(".dbf", "", $tablename);
		
		echo "Processing " . $file . "\r\n";
		
		//prep the insert
		$sql = "INSERT INTO `" . $db . "`.`" . strtolower($tablename) . "` 
		(";
		$arrColumns["name"] = array();
		$arrColumns["type"] = array();
		foreach ($table->getColumns() as $i=>$c) {
			$column_name = $c->getName();
			if (strpos($column_name, "FIELD")!==false) {
				$column_name .= "_";
			}
			$arrColumns["name"][] = "`" . $column_name . "`";
			$arrColumns["type"][] = $c->getType();
		}
		$sql .= implode(", ", $arrColumns["name"]);
		$sql .= ")";
		
		$table_fields = $sql;
		/* print records */
		$arrCaseNo = array();
		while ($record=$table->nextRecord()) {
			$arrValues = array();
			//die(print_r($table->getColumns()));
			foreach ($table->getColumns() as $i=>$c) {
				//echo "<td>".$record->getString($c)."</td>";
				$value = $record->getString($c);
				$type = $arrColumns["type"][$i];
				if ($type=="T") {
					$value = date("Y-m-d H:i:s", strtotime($value));
				}
				$arrValues[] = addslashes($value);
			}
			$sql = "
			VALUES ('" . implode("', '", $arrValues) . "')";
			$sql = $table_fields . $sql;
			echo $sql . "\r\n";
			$stmt = $db->prepare($sql_truncate);
			$stmt->execute();	
		}
		//echo "</table>";
	
		/* close the table */
		$table->close();
	}
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
$db = null;
include("cls_logging.php");
?>
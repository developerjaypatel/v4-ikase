<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once('../shared/legacy_session.php');
set_time_limit(3000);

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;
$last_updated_date = date("Y-m-d H:i:s");

include("connection.php");
?>
<html>
<body style="font-size:0.7em">
<?php
try {
	$db = getConnection();
	
	include("customer_lookup.php");
	
	
	$sql = "
	CREATE TABLE IF NOT EXISTS `ikase_" . $data_source . "`.`cse_card` (
		`ikase_uuid` varchar(20) DEFAULT NULL,
		`ikase_table` varchar(100) DEFAULT NULL,
		`CARDCODE` int(11) DEFAULT NULL,
		`FIRMCODE` int(11) DEFAULT NULL,
		`LETSAL` longtext,
		`SALUTATION` varchar(5) DEFAULT NULL,
		`FIRST` varchar(30) DEFAULT NULL,
		`MIDDLE` varchar(20) DEFAULT NULL,
		`LAST` varchar(40) DEFAULT NULL,
		`SUFFIX` varchar(8) DEFAULT NULL,
		`SOCIAL_SEC` varchar(15) DEFAULT NULL,
		`TYPE` varchar(25) DEFAULT NULL,
		`TITLE` varchar(30) DEFAULT NULL,
		`HOME` varchar(20) DEFAULT NULL,
		`BUSINESS` varchar(30) DEFAULT NULL,
		`FAX` varchar(20) DEFAULT NULL,
		`CAR` varchar(30) DEFAULT NULL,
		`BEEPER` varchar(20) DEFAULT NULL,
		`EMAIL` varchar(50) DEFAULT NULL,
		`BIRTH_DATE` date DEFAULT NULL,
		`INTERPRET` varchar(1) DEFAULT NULL,
		`LANGUAGE` varchar(10) DEFAULT NULL,
		`LICENSENO` varchar(15) DEFAULT NULL,
		`SPECIALTY` varchar(40) DEFAULT NULL,
		`MOTHERMAID` varchar(15) DEFAULT NULL,
		`PROTECTED` tinyint(1) DEFAULT NULL,
		KEY `ix_card1` (`CARDCODE`),
		KEY `ix_card2` (`SOCIAL_SEC`),
		KEY `ix_card3` (`FIRMCODE`)
	  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;  
	  
	CREATE TABLE IF NOT EXISTS `ikase_" . $data_source . "`.`cse_card2` (
		`FIRMCODE` int(11) DEFAULT NULL,
		`FIRM` longtext,
		`VENUE` varchar(15) DEFAULT NULL,
		`TAX_ID` varchar(15) DEFAULT NULL,
		`ADDRESS1` longtext,
		`ADDRESS2` longtext,
		`CITY` varchar(30) DEFAULT NULL,
		`STATE` varchar(5) DEFAULT NULL,
		`ZIP` varchar(10) DEFAULT NULL,
		`PHONE1` varchar(25) DEFAULT NULL,
		`PHONE2` varchar(25) DEFAULT NULL,
		`FAX` varchar(15) DEFAULT NULL,
		`FAX2` varchar(15) DEFAULT NULL,
		`FIRMKEY` varchar(10) DEFAULT NULL,
		`COLOR` float DEFAULT NULL,
		`EAMSREF` int(11) DEFAULT NULL,
		KEY `ix_card21` (`FIRMCODE`),
		KEY `ix_card22` (`FIRMKEY`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8; 
	
	CREATE TABLE IF NOT EXISTS `ikase_" . $data_source . "`.`cse_casecard` (
		`CASENO` int(11) DEFAULT NULL,
		`CARDCODE` int(11) DEFAULT NULL,
		`ORDERNO` int(11) DEFAULT NULL,
		`OFFICENO` varchar(25) DEFAULT NULL,
		`SIDE` varchar(15) DEFAULT NULL,
		`TYPE` varchar(25) DEFAULT NULL,
		`FLAGS` int(11) DEFAULT NULL,
		`NOTES` longtext,
		KEY `ix_casecard1` (`CARDCODE`),
		KEY `ix_casecard2` (`CASENO`),
		KEY `ix_casecard3` (`OFFICENO`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;  
	
	CREATE TABLE IF NOT EXISTS `ikase_" . $data_source . "`.`cse_card3` (
		`EAMSREF` int(11) DEFAULT NULL,
		`CATEGORY` varchar(2) DEFAULT NULL,
		`NAME` varchar(60) DEFAULT NULL,
		`ADDRESS1` varchar(60) DEFAULT NULL,
		`ADDRESS2` varchar(30) DEFAULT NULL,
		`CITY` varchar(30) DEFAULT NULL,
		`STATE` varchar(2) DEFAULT NULL,
		`ZIP` varchar(10) DEFAULT NULL,
		`PHONE` varchar(25) DEFAULT NULL,
		`SERVICE` varchar(1) DEFAULT NULL,
		KEY `ix_card31` (`EAMSREF`),
		KEY `ix_card32` (`NAME`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

	CREATE TABLE IF NOT EXISTS `ikase_" . $data_source . "`.`imports` (
	  `import_id` int(11) NOT NULL AUTO_INCREMENT,
	  `dir` varchar(255) DEFAULT '',
	  `folder` varchar(45) DEFAULT NULL,
	  `sub_folder` varchar(45) DEFAULT NULL,
	  `filename` varchar(45) DEFAULT NULL,
	  `full_filename` varchar(255) DEFAULT '',
	  `processed` datetime DEFAULT '0000-00-00 00:00:00',
	  PRIMARY KEY (`import_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	
	CREATE TABLE IF NOT EXISTS `ikase_" . $data_source . "`.`folders` (
	  `folders` longtext NOT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	
	ALTER TABLE `ikase_" . $data_source . "`.`caseact` 
	CHANGE COLUMN `TITLE` `TITLE` VARCHAR(255) NULL DEFAULT NULL ;
	ALTER TABLE `ikase_" . $data_source . "`.`caseact` 
	CHANGE COLUMN `COST` `COST` VARCHAR(255) NULL DEFAULT NULL ;
	ALTER TABLE `ikase_" . $data_source . "`.`caseact` 
	CHANGE COLUMN `ACCESS` `ACCESS` VARCHAR(255) NULL DEFAULT NULL ;
	ALTER TABLE `ikase_" . $data_source . "`.`caseact` 
	CHANGE COLUMN `ARAP` `ARAP` VARCHAR(255) NULL DEFAULT NULL ;
	ALTER TABLE `ikase_" . $data_source . "`.`caseact` 
	CHANGE COLUMN `OLE3STYLE` `OLE3STYLE` VARCHAR(255) NULL DEFAULT NULL ,
	CHANGE COLUMN `WORDSTYLE` `WORDSTYLE` VARCHAR(255) NULL DEFAULT NULL ,
	CHANGE COLUMN `FRMWIDTH` `FRMWIDTH` VARCHAR(255) NULL DEFAULT NULL ,
	CHANGE COLUMN `FRMHEIGHT` `FRMHEIGHT` VARCHAR(255) NULL DEFAULT NULL ,
	CHANGE COLUMN `FRMTM` `FRMTM` VARCHAR(255) NULL DEFAULT NULL ,
	CHANGE COLUMN `FRMBM` `FRMBM` VARCHAR(255) NULL DEFAULT NULL ,
	CHANGE COLUMN `FRMLM` `FRMLM` VARCHAR(255) NULL DEFAULT NULL ,
	CHANGE COLUMN `FRMRM` `FRMRM` VARCHAR(255) NULL DEFAULT NULL ,
	CHANGE COLUMN `ORIENT` `ORIENT` VARCHAR(255) NULL DEFAULT NULL ,
	CHANGE COLUMN `COPIES` `COPIES` VARCHAR(255) NULL DEFAULT NULL ,
	CHANGE COLUMN `TYPEACT` `TYPEACT` VARCHAR(255) NULL DEFAULT NULL ,
	CHANGE COLUMN `BISTYLE` `BISTYLE` VARCHAR(255) NULL DEFAULT NULL ,
	CHANGE COLUMN `BIFEE` `BIFEE` VARCHAR(255) NULL DEFAULT NULL ,
	CHANGE COLUMN `BICOST` `BICOST` VARCHAR(255) NULL DEFAULT NULL ,
	CHANGE COLUMN `BIPMT` `BIPMT` VARCHAR(255) NULL DEFAULT NULL ,
	CHANGE COLUMN `BILATEFEE` `BILATEFEE` VARCHAR(255) NULL DEFAULT NULL ;
	ALTER TABLE `ikase_" . $data_source . "`.`caseact` 
	CHANGE COLUMN `BICYCLE` `BICYCLE` VARCHAR(255) NULL DEFAULT NULL ,
	CHANGE COLUMN `BITIME` `BITIME` VARCHAR(255) NULL DEFAULT NULL ,
	CHANGE COLUMN `BIHOURRATE` `BIHOURRATE` VARCHAR(255) NULL DEFAULT NULL ;
	ALTER TABLE `ikase_" . $data_source . "`.`caseact` 
	CHANGE COLUMN `BIPMTDATE` `BIPMTDATE` VARCHAR(255) NULL DEFAULT NULL ,
	CHANGE COLUMN `BIPMTDUEDT` `BIPMTDUEDT` VARCHAR(255) NULL DEFAULT NULL ,
	CHANGE COLUMN `OLDNO` `OLDNO` VARCHAR(255) NULL DEFAULT NULL ;
	ALTER TABLE `ikase_" . $data_source . "`.`caseact` 
	CHANGE COLUMN `COLOR` `COLOR` VARCHAR(255) NULL DEFAULT NULL ;
	ALTER TABLE ` " . $data_source . " `.`caseact` 
	CHANGE COLUMN `CARDCODE` `CARDCODE` VARCHAR(255) NULL DEFAULT '' ;
	ALTER TABLE `ikase_" . $data_source . "`.`caseact` 
	CHANGE COLUMN `CARDCODE` `CARDCODE` VARCHAR(255) NULL DEFAULT NULL ;
	ALTER TABLE `ikase_" . $data_source . "`.`injury` 
	CHANGE COLUMN `DOR_DATE` `DOR_DATE` VARCHAR(255) NULL DEFAULT NULL ,
	CHANGE COLUMN `ADJ1A` `ADJ1A` VARCHAR(255) NULL DEFAULT NULL ,
	CHANGE COLUMN `DOI` `DOI` VARCHAR(255) NULL DEFAULT NULL ,
	CHANGE COLUMN `DOI2` `DOI2` VARCHAR(255) NULL DEFAULT NULL ,
	CHANGE COLUMN `ADJ10A` `ADJ10A` VARCHAR(255) NULL DEFAULT NULL ,
	CHANGE COLUMN `S_W` `S_W` VARCHAR(255) NULL DEFAULT NULL ,
	CHANGE COLUMN `OTHER_SOL` `OTHER_SOL` VARCHAR(255) NULL DEFAULT NULL ,
	CHANGE COLUMN `FOLLOW_UP` `FOLLOW_UP` VARCHAR(255) NULL DEFAULT NULL ,
	CHANGE COLUMN `LIAB` `LIAB` VARCHAR(255) NULL DEFAULT NULL ;
	
	ALTER TABLE `ikase_" . $data_source . "`.`cse_event` 
	CHANGE COLUMN `event_type` `event_type` VARCHAR(100) NULL DEFAULT NULL ;
	
	ALTER TABLE `ikase_" . $data_source . "`.`imports` 
	ADD COLUMN `processed` DATETIME NULL DEFAULT '0000-00-00 00:00:00' AFTER `filename`;
	ALTER TABLE `ikase_" . $data_source . "`.`imports` 
	ADD COLUMN `full_filename` VARCHAR(255) NULL DEFAULT '' AFTER `filename`;
	ALTER TABLE `ikase_" . $data_source . "`.`imports` 
	ADD COLUMN `dir` VARCHAR(255) NULL DEFAULT '' AFTER `import_id`;
	
	ALTER TABLE `ikase_" . $data_source . "`.`tasks` 
	ADD INDEX `CASENO` (`CASENO` ASC);
	ALTER TABLE `ikase_" . $data_source . "`.`injury` 
	CHANGE COLUMN `ADJ1E` `ADJ1E` VARCHAR(1050) NULL DEFAULT NULL ;

	ALTER TABLE `ikase_" . $data_source . "`.`cse_task` 
	CHANGE COLUMN `task_from` `task_from` VARCHAR(250) NULL DEFAULT NULL ;
	ALTER TABLE `ikase_" . $data_source . "`.`cse_task` 
	CHANGE COLUMN `from` `from` VARCHAR(255) NULL DEFAULT NULL ;

	ALTER TABLE `ikase_" . $data_source . "`.`cse_person` 
	CHANGE COLUMN `full_name` `full_name` VARCHAR(1050) NOT NULL DEFAULT '';

	ALTER TABLE `ikase_" . $data_source . "`.`doctrk1` 
	ADD INDEX `CASENO` (`CASENO` ASC),
	ADD INDEX `ACTNO` (`ACTNO` ASC);
";
	$stmt = DB::run($sql);

	$sql="SELECT column_name FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='ikase_".$data_source."' AND TABLE_NAME='cse_activity'";
	$stmt = DB::run($sql);
	$stmt = $stmt->fetchAll(PDO::FETCH_NAMED);
	$stmt = array_column($stmt,'column_name');
	// echo "<pre>"; print_r($stmt); echo "</pre>"; die();
	if(!in_array("flag", $stmt)){
		$sql ="ALTER TABLE `ikase_".$data_source."`.`cse_activity` 
		ADD COLUMN `flag` VARCHAR(100) NULL AFTER `activity_category`";
		$stmt = DB::run($sql);
	}
	
	//Increase leangth
	$sql ="ALTER TABLE `ikase_alvandi`.`cse_activity`   
	CHANGE `activity_uuid` `activity_uuid` VARCHAR(50) CHARSET utf8 COLLATE utf8_unicode_ci DEFAULT ''  NOT NULL";
	$stmt = DB::run($sql);
	$sql ="ALTER TABLE `ikase_alvandi`.`cse_case_activity`   
	CHANGE `activity_uuid` `activity_uuid` VARCHAR(50) CHARSET utf8 COLLATE utf8_unicode_ci NOT NULL";
	$stmt = DB::run($sql);

	//Column name convert in uppercase code start
	$db = getConnection();
	// die($GLOBALS['GEN_DB_NAME']);
	$sql = "SHOW TABLES FROM ".$GLOBALS['GEN_DB_NAME'];
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$table_list = $stmt->fetchAll(PDO::FETCH_NAMED);
	foreach ($table_list as $key => $value) {
		// var_dump($value);
		// echo $key."->".reset($value)."<br>";
		$sql1 = 'SELECT CONCAT_WS(" ",`COLUMN_NAME`,"-,-", `COLUMN_TYPE`,IF(STRCMP(`CHARACTER_SET_NAME`,IFNULL(NULL,`CHARACTER_SET_NAME`)) = 0, "CHARSET",""), `CHARACTER_SET_NAME`,IF(STRCMP(`COLLATION_NAME`,IFNULL(NULL,`COLLATION_NAME`)) = 0, "COLLATE",""),`COLLATION_NAME`, IF(STRCMP(`IS_NULLABLE`,"YES") = 0, "NULL", "NOT NULL")) AS NAME1
	FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE `TABLE_SCHEMA`="'.$GLOBALS['GEN_DB_NAME'].'" AND `TABLE_NAME`="'.reset($value).'"';
	// die($sql1);
		$stmt1 = $db->prepare($sql1);
		$stmt1->execute();
		$table_list1 = $stmt1->fetchAll(PDO::FETCH_NAMED);
		foreach ($table_list1 as $key1 => $value1) {
			// var_dump($value1);
			// echo $value1['NAME1']."<br>";
			$alter_part = explode("-,-",$value1['NAME1']);
			// var_dump($alter_part);
			$column_org_name = substr($alter_part[0], 0, -1);
			$column_new_name = substr($alter_part[1], 1);

			$sql2="  ALTER TABLE `".$GLOBALS['GEN_DB_NAME']."`.`".reset($value)."` CHANGE `".$column_org_name."` `".strtoupper($column_org_name)."` ".$column_new_name;
			// echo $sql2."<br>";
			$stmt2 = $db->prepare($sql2);
			$stmt2->execute();
			// die();
		}
		// echo "Done ".reset($value)."<br>";
	}
	// die();
		// THIS CODE ADDED TO MAKE SURE INDEXES ARE APPLIED
		// INDEX CODE START
		$sql3 = "SHOW INDEX FROM `".$GLOBALS['GEN_DB_NAME']."`.casecard WHERE Key_name = 'CARDCODE';";
		$stmt3 = $db->prepare($sql3);
		$stmt3->execute();
		$table_list3 = $stmt3->fetchAll(PDO::FETCH_NAMED);
		$run_index3 = true;
		if(!empty($table_list3)) {
			$run_index3 = false;
		}
		
		if($run_index3){
			$sql3=" CREATE INDEX CARDCODE ON  `".$GLOBALS['GEN_DB_NAME']."`.casecard (CARDCODE);";
			$stmt3 = $db->prepare($sql3);
			$stmt3->execute();
		}
		
		$sql3 = "SHOW INDEX FROM `".$GLOBALS['GEN_DB_NAME']."`.card WHERE Key_name = 'CARDCODE';";
		$stmt3 = $db->prepare($sql3);
		$stmt3->execute();
		$table_list3 = $stmt3->fetchAll(PDO::FETCH_NAMED);
		$run_index3 = true;
		if(!empty($table_list3)) {
			$run_index3 = false;
		}
		
		if($run_index3){
			$sql3="CREATE INDEX CARDCODE ON `".$GLOBALS['GEN_DB_NAME']."`.card (CARDCODE);";
			$stmt3 = $db->prepare($sql3);
			$stmt3->execute();
		}

		$sql3 = "SHOW INDEX FROM `".$GLOBALS['GEN_DB_NAME']."`.card WHERE Key_name = 'FIRMCODE';";
		$stmt3 = $db->prepare($sql3);
		$stmt3->execute();
		$table_list3 = $stmt3->fetchAll(PDO::FETCH_NAMED);
		$run_index3 = true;
		if(!empty($table_list3)) {
			$run_index3 = false;
		}
		
		if($run_index3){
			$sql3="CREATE INDEX FIRMCODE ON `".$GLOBALS['GEN_DB_NAME']."`.card (FIRMCODE);";
			$stmt3 = $db->prepare($sql3);
			$stmt3->execute();
		}

		$sql3 = "SHOW INDEX FROM `".$GLOBALS['GEN_DB_NAME']."`.card2 WHERE Key_name = 'FIRMCODE';";
		$stmt3 = $db->prepare($sql3);
		$stmt3->execute();
		$table_list3 = $stmt3->fetchAll(PDO::FETCH_NAMED);
		$run_index3 = true;
		if(!empty($table_list3)) {
			$run_index3 = false;
		}
		
		if($run_index3){
			$sql3="CREATE INDEX FIRMCODE ON `".$GLOBALS['GEN_DB_NAME']."`.card2 (FIRMCODE);";
			$stmt3 = $db->prepare($sql3);
			$stmt3->execute();
		}

		$sql3 = "SHOW INDEX FROM `".$GLOBALS['GEN_DB_NAME']."`.card2 WHERE Key_name = 'EAMSREF';";
		$stmt3 = $db->prepare($sql3);
		$stmt3->execute();
		$table_list3 = $stmt3->fetchAll(PDO::FETCH_NAMED);
		$run_index3 = true;
		if(!empty($table_list3)) {
			$run_index3 = false;
		}
		
		if($run_index3){
			$sql3="CREATE INDEX EAMSREF ON `".$GLOBALS['GEN_DB_NAME']."`.card2 (EAMSREF);";
			$stmt3 = $db->prepare($sql3);
			$stmt3->execute();
		}

		$sql3 = "SHOW INDEX FROM `".$GLOBALS['GEN_DB_NAME']."`.card3 WHERE Key_name = 'EAMSREF';";
		$stmt3 = $db->prepare($sql3);
		$stmt3->execute();
		$table_list3 = $stmt3->fetchAll(PDO::FETCH_NAMED);
		$run_index3 = true;
		if(!empty($table_list3)) {
			$run_index3 = false;
		}
		
		if($run_index3){
			$sql3="CREATE INDEX EAMSREF ON `".$GLOBALS['GEN_DB_NAME']."`.card3 (EAMSREF);";
			$stmt3 = $db->prepare($sql3);
			$stmt3->execute();
		}

		$sql3 = "SHOW INDEX FROM `".$GLOBALS['GEN_DB_NAME']."`.case WHERE Key_name = 'CASENO';";
		$stmt3 = $db->prepare($sql3);
		$stmt3->execute();
		$table_list3 = $stmt3->fetchAll(PDO::FETCH_NAMED);
		$run_index3 = true;
		if(!empty($table_list3)) {
			$run_index3 = false;
		}
		
		if($run_index3){
			$sql3="CREATE INDEX CASENO ON `".$GLOBALS['GEN_DB_NAME']."`.case (CASENO);";
			$stmt3 = $db->prepare($sql3);
			$stmt3->execute();
		}

		$sql3 = "SHOW INDEX FROM `".$GLOBALS['GEN_DB_NAME']."`.caseact WHERE Key_name = 'CASENO';";
		$stmt3 = $db->prepare($sql3);
		$stmt3->execute();
		$table_list3 = $stmt3->fetchAll(PDO::FETCH_NAMED);
		$run_index3 = true;
		if(!empty($table_list3)) {
			$run_index3 = false;
		}
		
		if($run_index3){
			$sql3="CREATE INDEX CASENO ON `".$GLOBALS['GEN_DB_NAME']."`.caseact (CASENO);";
			$stmt3 = $db->prepare($sql3);
			$stmt3->execute();
		}

		$sql3 = "SHOW INDEX FROM `".$GLOBALS['GEN_DB_NAME']."`.cal1 WHERE Key_name = 'CASENO';";
		$stmt3 = $db->prepare($sql3);
		$stmt3->execute();
		$table_list3 = $stmt3->fetchAll(PDO::FETCH_NAMED);
		$run_index3 = true;
		if(!empty($table_list3)) {
			$run_index3 = false;
		}
		
		if($run_index3){
			$sql3="CREATE INDEX CASENO ON `".$GLOBALS['GEN_DB_NAME']."`.cal1  (CASENO);";
			$stmt3 = $db->prepare($sql3);
			$stmt3->execute();
		}

		$sql3="ALTER TABLE `".$GLOBALS['GEN_DB_NAME']."`.cal1 ENGINE = MyISAM;";
		$stmt3 = $db->prepare($sql3);
		$stmt3->execute();

		$sql3 = "SHOW INDEX FROM `".$GLOBALS['GEN_DB_NAME']."`.injury WHERE Key_name = 'CASENO';";
		$stmt3 = $db->prepare($sql3);
		$stmt3->execute();
		$table_list3 = $stmt3->fetchAll(PDO::FETCH_NAMED);
		$run_index3 = true;
		if(!empty($table_list3)) {
			$run_index3 = false;
		}
		
		if($run_index3){
			$sql3="CREATE INDEX CASENO ON `".$GLOBALS['GEN_DB_NAME']."`.injury (CASENO);";
			$stmt3 = $db->prepare($sql3);
			$stmt3->execute();
		}

		$sql3 = "SHOW INDEX FROM `".$GLOBALS['GEN_DB_NAME']."`.tasks WHERE Key_name = 'CASENO';";
		$stmt3 = $db->prepare($sql3);
		$stmt3->execute();
		$table_list3 = $stmt3->fetchAll(PDO::FETCH_NAMED);
		$run_index3 = true;
		if(!empty($table_list3)) {
			$run_index3 = false;
		}
		
		if($run_index3){
			$sql3="CREATE INDEX CASENO ON `".$GLOBALS['GEN_DB_NAME']."`.tasks (CASENO);";
			$stmt3 = $db->prepare($sql3);
			$stmt3->execute();
		}

		$sql3 = "SHOW INDEX FROM `".$GLOBALS['GEN_DB_NAME']."`.tasks WHERE Key_name = 'WHOFROM';";
		$stmt3 = $db->prepare($sql3);
		$stmt3->execute();
		$table_list3 = $stmt3->fetchAll(PDO::FETCH_NAMED);
		$run_index3 = true;
		if(!empty($table_list3)) {
			$run_index3 = false;
		}
		
		if($run_index3){
			$sql3="CREATE INDEX WHOFROM ON `".$GLOBALS['GEN_DB_NAME']."`.tasks (WHOFROM);";
			$stmt3 = $db->prepare($sql3);
			$stmt3->execute();
		}

		$sql3="ALTER TABLE `".$GLOBALS['GEN_DB_NAME']."`.tasks ENGINE = MyISAM;";
		$stmt3 = $db->prepare($sql3);
		$stmt3->execute();
		
		// INDEX CODE ENDS
	// $stmt = DB::run($sql);
	//Column name convert in uppercase code end

	echo "prepped";
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
include("cls_logging.php");
?>
</body>
</html>

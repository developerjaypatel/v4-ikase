<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include("manage_session.php");
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
	
	ALTER TABLE `ikase_" . $data_source . "`.`cse_activity` 
	ADD COLUMN `flag` VARCHAR(100) NULL DEFAULT '' AFTER `activity_category`;

	ALTER TABLE `ikase_" . $data_source . "`.`cse_person` 
	CHANGE COLUMN `full_name` `full_name` VARCHAR(1050) NOT NULL DEFAULT '';

	ALTER TABLE `ikase_" . $data_source . "`.`doctrk1` 
	ADD INDEX `CASENO` (`CASENO` ASC),
	ADD INDEX `ACTNO` (`ACTNO` ASC);
";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	echo "prepped";
	$stmt = null; $db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
include("cls_logging.php");
?>
</body>
</html>
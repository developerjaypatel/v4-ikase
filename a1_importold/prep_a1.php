<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include("../api/manage_session.php");
set_time_limit(3000);

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;
$last_updated_date = date("Y-m-d H:i:s");

include("../api/connection.php");
?>
<html>
<body style="font-size:0.7em">
<?php
try {
	$db = getConnection();
	
	include("../api/customer_lookup.php");
	
	
	$sql = "
	DROP TABLE `cse_card2`;
	CREATE TABLE IF NOT EXISTS `cse_card` (
		`card_id` int(11) NOT NULL AUTO_INCREMENT,
		`ikase_uuid` varchar(255) DEFAULT '',
		`ikase_table` varchar(255) DEFAULT '',
		`CARDCODE` varchar(255) DEFAULT '',
		`FIRMCODE` varchar(1055) DEFAULT '',
		`LETSAL` varchar(255) DEFAULT '',	
		`SALUTATION` varchar(255) DEFAULT '',
		`FIRST` varchar(255) DEFAULT '',
		`MIDDLE` varchar(255) DEFAULT '',
		`LAST` varchar(255) DEFAULT '',
		`SUFFIX` varchar(255) DEFAULT '',
		`SOCIAL_SEC` varchar(255) DEFAULT '',
		`TYPE` varchar(255) DEFAULT '',
		`TITLE` varchar(255) DEFAULT '',
		`HOME` varchar(255) DEFAULT '',
		`BUSINESS` varchar(255) DEFAULT '',
		`FAX` varchar(255) DEFAULT '',
		`CAR` varchar(255) DEFAULT '',
		`BEEPER` varchar(255) DEFAULT '',
		`EMAIL` varchar(255) DEFAULT '',
		`BIRTH_DATE` datetime DEFAULT '0000-00-00 00:00:00',
		`INTERPRET` varchar(255) DEFAULT '',
		`LANGUAGE` varchar(255) DEFAULT '',
		`LICENSENO` varchar(255) DEFAULT '',
		`SPECIALTY` varchar(255) DEFAULT '',
		`MOTHERMAID` varchar(255) DEFAULT '',
		`PROTECTED` varchar(255) DEFAULT '',
		PRIMARY KEY (`card_id`)
	  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

	  CREATE TABLE IF NOT EXISTS `cse_card2` (
		`card2_id` int(11) NOT NULL AUTO_INCREMENT,
		`FIRMCODE` varchar(255) DEFAULT '',
		`FIRM` varchar(255) DEFAULT '',
		`VENUE` varchar(255) DEFAULT '',
		`TAX_ID` varchar(1055) DEFAULT '',
		`ADDRESS1` varchar(255) DEFAULT '',	
		`ADDRESS2` varchar(255) DEFAULT '',
		`CITY` varchar(255) DEFAULT '',
		`STATE` varchar(255) DEFAULT '',
		`ZIP` varchar(255) DEFAULT '',
		`PHONE1` varchar(255) DEFAULT '',
		`PHONE2` varchar(255) DEFAULT '',
		`FAX` varchar(255) DEFAULT '',
		`FIRMKEY` varchar(255) DEFAULT '',
		`COLOR` varchar(255) DEFAULT '',
		`EAMSREF` varchar(255) DEFAULT '',
		PRIMARY KEY (`card2_id`)
	  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

	  CREATE TABLE IF NOT EXISTS `cse_card3` (
		`card3_id` int(11) NOT NULL AUTO_INCREMENT,
		`NAME` varchar(255) DEFAULT '',
		`ADDRESS1` varchar(255) DEFAULT '',	
		`ADDRESS2` varchar(255) DEFAULT '',
		`CITY` varchar(255) DEFAULT '',
		`STATE` varchar(255) DEFAULT '',
		`ZIP` varchar(255) DEFAULT '',
		`PHONE` varchar(255) DEFAULT '',
		`EAMSREF` varchar(255) DEFAULT '',
		PRIMARY KEY (`card3_id`)
	  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

	  CREATE TABLE IF NOT EXISTS `cse_casecard` (
		`casecard_id` int(11) NOT NULL AUTO_INCREMENT,
		`CARDCODE` varchar(255) DEFAULT '',
		`CASENO` varchar(255) DEFAULT 0,
		`TYPE` varchar(255) DEFAULT '',
		PRIMARY KEY (`casecard_id`)
	  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

	CREATE TABLE IF NOT EXISTS `cse_caseact` (
		`caseact_id` int(11) NOT NULL AUTO_INCREMENT,
		`TITLE` varchar(255) DEFAULT '',	
		`CASENO` varchar(255) DEFAULT '0',
		`EVENT` varchar(255) DEFAULT NULL,
		`COST` varchar(255) DEFAULT NULL,
		`ACCESS` varchar(255) DEFAULT NULL,
		`ARAP` varchar(255) DEFAULT NULL,
		`OLE3STYLE` varchar(255) DEFAULT '',
		`WORDSTYLE` varchar(255) DEFAULT '',
		`FRMWIDTH` varchar(255) DEFAULT '',
		`FRMHEIGHT` varchar(255) DEFAULT '',
		`FRMTM` varchar(255) DEFAULT '',
		`FRMBM` varchar(255) DEFAULT '',
		`FRMLM` varchar(255) DEFAULT '',
		`FRMRM` varchar(255) DEFAULT '',
		`ORIENT` varchar(255) DEFAULT '',
		`COPIES` varchar(255) DEFAULT '',
		`TYPEACT` varchar(255) DEFAULT '',
		`BISTYLE` varchar(255) DEFAULT '',
		`BIFEE` varchar(255) DEFAULT '',
		`BICOST` varchar(255) DEFAULT '',
		`BIPMT` varchar(255) DEFAULT '',
		`BILATEFEE` varchar(255) DEFAULT '',
		`BICYCLE` varchar(255) DEFAULT '',
		`BITIME` varchar(255) DEFAULT '',
		`BIHOURRATE` varchar(255) DEFAULT '',
		`BIPMTDATE` varchar(255) DEFAULT '',
		`BIPMTDUEDT` varchar(255) DEFAULT '',
		`OLDNO` varchar(255) DEFAULT '',
		`COLOR` varchar(255) DEFAULT '',
		`CARDCODE` varchar(255) DEFAULT '',
		PRIMARY KEY (`caseact_id`)
	  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

	  CREATE TABLE IF NOT EXISTS `cse_doctrk1` (
		`doctrk1_id` int(11) NOT NULL AUTO_INCREMENT,
		`CASENO` varchar(255) DEFAULT '0',
		`_DOC_` varchar(255) DEFAULT '',	
		`ACTNO` varchar(255) DEFAULT NULL,
		`EVENT` varchar(255) DEFAULT NULL,
		`DATE` datetime DEFAULT '0000-00-00 00:00:00',
		PRIMARY KEY (`doctrk1_id`)
	  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

	  CREATE TABLE IF NOT EXISTS `cse_staff` (
		`staff_id` int(11) NOT NULL AUTO_INCREMENT,
		`INITIALS` varchar(255) DEFAULT NULL,
		`FNAME` varchar(255) DEFAULT NULL,	
		`LNAME` varchar(255) DEFAULT NULL,
		`USERNAME` varchar(255) DEFAULT NULL,
		`TITLE` varchar(255) DEFAULT NULL,
		PRIMARY KEY (`staff_id`)
	  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

	  CREATE TABLE IF NOT EXISTS `cse_cal1` (
		`cal1_id` int(11) NOT NULL AUTO_INCREMENT,
		`CASENO` varchar(255) DEFAULT 0,
		PRIMARY KEY (`cal1_id`)
	  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

	  CREATE TABLE IF NOT EXISTS `cse_task` (
		`task_id` int(11) NOT NULL AUTO_INCREMENT,
		`task_uuid` varchar(55) DEFAULT '' NOT NULL,
		`task_name` varchar(1055) DEFAULT '' NOT NULL,
		`CASENO` varchar(255) DEFAULT 0 NOT NULL,	
		`from` varchar(255) DEFAULT '' NOT NULL,
		`task_date` varchar(255) DEFAULT '' NOT NULL,
		`task_description` text NOT NULL,
		`task_first_name` varchar(255) DEFAULT '' NOT NULL,
		`task_last_name` varchar(255) DEFAULT '' NOT NULL,
		`task_dateandtime` varchar(255) DEFAULT '0000-00-00 00:00:00' NOT NULL,
		`task_end_time` varchar(255) DEFAULT '0000-00-00 00:00:00' NOT NULL,
		`full_address` varchar(255) DEFAULT '' NOT NULL,
		`assignee` varchar(255) DEFAULT '' NOT NULL,
		`cc` varchar(255) DEFAULT '',
		`task_title` varchar(1055) DEFAULT '' NOT NULL,
		`attachment` varchar(255) DEFAULT '' NOT NULL,
		`task_email` varchar(255) DEFAULT '' NOT NULL,
		`task_hour` varchar(255) DEFAULT '' NOT NULL,
		`task_type` varchar(255) DEFAULT '' NOT NULL,
		`type_of_task` varchar(255) DEFAULT '',
		`task_from` varchar(255) DEFAULT '' NOT NULL,
		`task_priority` varchar(255) DEFAULT '' NOT NULL,
		`end_date` datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		`completed_date` datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		`callback_date` datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		`callback_completed` datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		`color` varchar(255) DEFAULT 'blue' NOT NULL,
		`customer_id` int(11) DEFAULT 0 NOT NULL,
		`deleted` ENUM('Y','N') DEFAULT 'N',
		`DATEREQ` datetime DEFAULT '0000-00-00 00:00:00',
		`COMPLETED` datetime DEFAULT '0000-00-00 00:00:00',
		PRIMARY KEY (`task_id`)
	  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

	  CREATE TABLE IF NOT EXISTS `cse_injury` (
		`injury_id` int(11) NOT NULL AUTO_INCREMENT,
		`injury_uuid` varchar(15) DEFAULT '' NOT NULL,
		`injury_number` smallint(6) DEFAULT '1' NOT NULL,
		`CASENO` varchar(255) DEFAULT 0 NOT NULL,	
		`adj_number` varchar(255) DEFAULT '' NOT NULL,
		`type` varchar(50) DEFAULT '' NOT NULL,
		`injury_status` varchar(20),
		`occupation` varchar(255) NOT NULL,
		`occupation_group` varchar(50) DEFAULT '',
		`start_date` varchar(255) DEFAULT '0000-00-00 00:00:00' NOT NULL,
		`end_time` varchar(255) DEFAULT '0000-00-00 00:00:00' NOT NULL,
		`ct_dates_not` varchar(255) DEFAULT '' NOT NULL,
		`body_parts` varchar(255) DEFAULT '' NOT NULL,
		`statute_limitation` date DEFAULT '0000-00-00 00:00:00' NOT NULL,
		`statute_interval` int(11) DEFAULT '730',
		`explanation` text NOT NULL,
		`deu` ENUM('Y','N') DEFAULT 'N' NOT NULL,
		`full_address` varchar(255) DEFAULT '' NOT NULL,
		`street` varchar(255) DEFAULT '' NOT NULL,
		`city` varchar(255) DEFAULT '' NOT NULL,
		`state` varchar(20) DEFAULT '' NOT NULL,
		`zip` varchar(15) DEFAULT '' NOT NULL,
		`suite` varchar(100) DEFAULT '',
		`customer_id` int(11) DEFAULT '0' NOT NULL,
		`deleted` ENUM('Y','N') DEFAULT 'N' NOT NULL,
		`E_NAME` varchar(255) DEFAULT '',
		`E_ADDRESS` varchar(255) DEFAULT '',
		`E_CITY` varchar(255) DEFAULT '',
		`E_STATE` varchar(255) DEFAULT '',
		`E_ZIP` varchar(255) DEFAULT '',
		`E_PHONE` varchar(255) DEFAULT '',
		`E_FAX` varchar(255) DEFAULT '',
		`E2_NAME` varchar(255) DEFAULT '',
		`E2_ADDRESS` varchar(255) DEFAULT '',
		`E2_CITY` varchar(255) DEFAULT '',
		`E2_STATE` varchar(255) DEFAULT '',
		`E2_ZIP` varchar(255) DEFAULT '',
		`E2_PHONE` varchar(255) DEFAULT '',
		`E2_FAX` varchar(255) DEFAULT '',
		PRIMARY KEY (`injury_id`)
	  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
	  
	CREATE TABLE IF NOT EXISTS `cse_imports` (
	  `import_id` int(11) NOT NULL AUTO_INCREMENT,
	  `dir` varchar(255) DEFAULT '',
	  `folder` varchar(45) DEFAULT NULL,
	  `sub_folder` varchar(45) DEFAULT NULL,
	  `filename` varchar(45) DEFAULT NULL,
	  `full_filename` varchar(255) DEFAULT '',
	  `processed` datetime DEFAULT '0000-00-00 00:00:00',
	  PRIMARY KEY (`import_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

		CREATE TABLE `cse_folders` (
	  `folders` longtext NOT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	
	ALTER TABLE `cse_caseact` 
	CHANGE COLUMN `TITLE` `TITLE` VARCHAR(255) NULL DEFAULT NULL ;
	ALTER TABLE `cse_caseact` 
	CHANGE COLUMN `COST` `COST` VARCHAR(255) NULL DEFAULT NULL ;
	ALTER TABLE `cse_caseact` 
	CHANGE COLUMN `ACCESS` `ACCESS` VARCHAR(255) NULL DEFAULT NULL ;
	ALTER TABLE `cse_caseact` 
	CHANGE COLUMN `ARAP` `ARAP` VARCHAR(255) NULL DEFAULT NULL ;
	ALTER TABLE `cse_caseact` 
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
	ALTER TABLE `cse_caseact` 
	CHANGE COLUMN `BICYCLE` `BICYCLE` VARCHAR(255) NULL DEFAULT NULL ,
	CHANGE COLUMN `BITIME` `BITIME` VARCHAR(255) NULL DEFAULT NULL ,
	CHANGE COLUMN `BIHOURRATE` `BIHOURRATE` VARCHAR(255) NULL DEFAULT NULL ;
	ALTER TABLE `cse_caseact` 
	CHANGE COLUMN `BIPMTDATE` `BIPMTDATE` VARCHAR(255) NULL DEFAULT NULL ,
	CHANGE COLUMN `BIPMTDUEDT` `BIPMTDUEDT` VARCHAR(255) NULL DEFAULT NULL ,
	CHANGE COLUMN `OLDNO` `OLDNO` VARCHAR(255) NULL DEFAULT NULL ;
	ALTER TABLE `cse_caseact` 
	CHANGE COLUMN `COLOR` `COLOR` VARCHAR(255) NULL DEFAULT NULL ;
	ALTER TABLE `cse_caseact` 
	CHANGE COLUMN `CARDCODE` `CARDCODE` VARCHAR(255) NULL DEFAULT '' ;
	ALTER TABLE `cse_caseact` 
	CHANGE COLUMN `CARDCODE` `CARDCODE` VARCHAR(255) NULL DEFAULT NULL ;
	ALTER TABLE `cse_injury` 
	CHANGE COLUMN `DOR_DATE` `DOR_DATE` VARCHAR(255) NULL DEFAULT NULL ,
	CHANGE COLUMN `ADJ1A` `ADJ1A` VARCHAR(255) NULL DEFAULT NULL ,
	CHANGE COLUMN `DOI` `DOI` VARCHAR(255) NULL DEFAULT NULL ,
	CHANGE COLUMN `DOI2` `DOI2` VARCHAR(255) NULL DEFAULT NULL ,
	CHANGE COLUMN `ADJ10A` `ADJ10A` VARCHAR(255) NULL DEFAULT NULL ,
	CHANGE COLUMN `S_W` `S_W` VARCHAR(255) NULL DEFAULT NULL ,
	CHANGE COLUMN `OTHER_SOL` `OTHER_SOL` VARCHAR(255) NULL DEFAULT NULL ,
	CHANGE COLUMN `FOLLOW_UP` `FOLLOW_UP` VARCHAR(255) NULL DEFAULT NULL ,
	CHANGE COLUMN `LIAB` `LIAB` VARCHAR(255) NULL DEFAULT NULL ;
	ADD COLUMN `CASENO` varchar(255) NULL DEFAULT '0' AFTER `deleted`;
	
	ALTER TABLE `cse_imports` 
	ADD COLUMN `processed` DATETIME NULL DEFAULT '0000-00-00 00:00:00' AFTER `filename`;
	ALTER TABLE `cse_imports` 
	ADD COLUMN `full_filename` VARCHAR(255) NULL DEFAULT '' AFTER `filename`;
	ALTER TABLE `cse_imports` 
	ADD COLUMN `dir` VARCHAR(255) NULL DEFAULT '' AFTER `import_id`;
	
	ALTER TABLE `cse_tasks` 
	ADD INDEX `CASENO` (`CASENO` ASC);
	ALTER TABLE `cse_task` 
	ADD COLUMN `DATEREQ` DATETIME NULL DEFAULT '0000-00-00 00:00:00' AFTER `customer_id`,
	ADD INDEX `CASENO` (`CASENO` ASC);
	ALTER TABLE `cse_injury` 
	CHANGE COLUMN `ADJ1E` `ADJ1E` VARCHAR(1050) NULL DEFAULT NULL ;
	
	ALTER TABLE `cse_tivity` 
	ADD COLUMN `flag` VARCHAR(100) NULL DEFAULT '' AFTER `activity_category`;

	ALTER TABLE `cse_doctrk1` 
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
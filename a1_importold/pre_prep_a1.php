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
	ALTER TABLE " . $data_source . ".`doctrk1` ENGINE=MyISAM;
ALTER TABLE " . $data_source . ".`doctrk1`
ADD INDEX `CASENO` (`CASENO` ASC),
ADD INDEX `ACTNO` (`ACTNO` ASC);
ALTER TABLE " . $data_source . ".`card2` ENGINE=MyISAM;
ALTER TABLE " . $data_source . ".`card2`
ADD INDEX `FIRMCODE` (`FIRMCODE` ASC),
ALTER TABLE " . $data_source . ".`card` ENGINE=MyISAM;
ALTER TABLE " . $data_source . ".`card`
ADD INDEX `CARDCODE` (`CARDCODE` ASC),
ADD INDEX `FIRMCODE` (`FIRMCODE` ASC);
ALTER TABLE " . $data_source . ".`caseact` ENGINE=MyISAM;
ALTER TABLE " . $data_source . ".`caseact`
ADD INDEX `CATEGORY` (`CATEGORY` ASC);
ALTER TABLE " . $data_source . ".`actdeflt` ENGINE=MyISAM;
ALTER TABLE " . $data_source . ".`actdeflt`
ADD INDEX `CATEGORY` (`CATEGORY` ASC);
ALTER TABLE " . $data_source . ".`cal1` ENGINE=MyISAM;
ALTER TABLE " . $data_source . ".`cal1`
ADD INDEX `EVENTNO` (`EVENTNO` ASC),
ADD INDEX `CASENO` (`CASENO` ASC);
ALTER TABLE " . $data_source . ".`cal2` ENGINE=MyISAM;
ALTER TABLE " . $data_source . ".`cal2`
ADD INDEX `EVENTNO` (`EVENTNO` ASC);

ALTER TABLE " . $data_source . ".`case` ENGINE=MyISAM;
ALTER TABLE " . $data_source . ".`case`
ADD INDEX `CASENO` (`CASENO` ASC);


ALTER TABLE " . $data_source . ".`casecard` ENGINE=MyISAM;
ALTER TABLE " . $data_source . ".`casecard`
ADD INDEX `CASENO` (`CASENO` ASC),
ADD INDEX `CARDCODE` (`CARDCODE` ASC);

ALTER TABLE " . $data_source . ".`card2`
ADD INDEX `EAMSREF` (`EAMSREF` ASC);

ALTER TABLE " . $data_source . ".`card`
ADD INDEX `FIRST` (`FIRST` ASC),
ADD INDEX `LAST` (`LAST` ASC),
ADD INDEX `SOCIAL_SEC` (`SOCIAL_SEC` ASC),
ADD INDEX `BIRTH_DATE` (`BIRTH_DATE` ASC);

	ALTER TABLE " . $data_source . ".`caseact`
CHANGE COLUMN `TITLE` `TITLE` VARCHAR(255) NULL DEFAULT NULL ;

ALTER TABLE " . $data_source . ".`caseact`
CHANGE COLUMN `COST` `COST` VARCHAR(255) NULL DEFAULT NULL ;

ALTER TABLE " . $data_source . ".`caseact`
CHANGE COLUMN `ACCESS` `ACCESS` VARCHAR(255) NULL DEFAULT NULL ;

ALTER TABLE " . $data_source . ".`caseact`
CHANGE COLUMN `ARAP` `ARAP` VARCHAR(255) NULL DEFAULT NULL ;

ALTER TABLE " . $data_source . ".`caseact`
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

ALTER TABLE " . $data_source . ".`caseact`
CHANGE COLUMN `BICYCLE` `BICYCLE` VARCHAR(255) NULL DEFAULT NULL ,
CHANGE COLUMN `BITIME` `BITIME` VARCHAR(255) NULL DEFAULT NULL ,
CHANGE COLUMN `BIHOURRATE` `BIHOURRATE` VARCHAR(255) NULL DEFAULT NULL ;

ALTER TABLE " . $data_source . ".`caseact`
CHANGE COLUMN `BIPMTDATE` `BIPMTDATE` VARCHAR(255) NULL DEFAULT NULL ,
CHANGE COLUMN `BIPMTDUEDT` `BIPMTDUEDT` VARCHAR(255) NULL DEFAULT NULL ,
CHANGE COLUMN `OLDNO` `OLDNO` VARCHAR(255) NULL DEFAULT NULL ;

ALTER TABLE " . $data_source . ".`caseact`
CHANGE COLUMN `COLOR` `COLOR` VARCHAR(255) NULL DEFAULT NULL ;

ALTER TABLE ` barsoum`.`caseact`
CHANGE COLUMN `CARDCODE` `CARDCODE` VARCHAR(255) NULL DEFAULT '' ;

ALTER TABLE " . $data_source . ".`caseact`

CHANGE COLUMN `CARDCODE` `CARDCODE` VARCHAR(255) NULL DEFAULT NULL ;

ALTER TABLE " . $data_source . ".`injury`
CHANGE COLUMN `DOR_DATE` `DOR_DATE` VARCHAR(255) NULL DEFAULT NULL ,
CHANGE COLUMN `ADJ1A` `ADJ1A` VARCHAR(255) NULL DEFAULT NULL ,
CHANGE COLUMN `DOI` `DOI` VARCHAR(255) NULL DEFAULT NULL ,
CHANGE COLUMN `DOI2` `DOI2` VARCHAR(255) NULL DEFAULT NULL ,
CHANGE COLUMN `ADJ10A` `ADJ10A` VARCHAR(255) NULL DEFAULT NULL ,
CHANGE COLUMN `S_W` `S_W` VARCHAR(255) NULL DEFAULT NULL ,
CHANGE COLUMN `OTHER_SOL` `OTHER_SOL` VARCHAR(255) NULL DEFAULT NULL ,
CHANGE COLUMN `FOLLOW_UP` `FOLLOW_UP` VARCHAR(255) NULL DEFAULT NULL ,
CHANGE COLUMN `LIAB` `LIAB` VARCHAR(255) NULL DEFAULT NULL ;

CREATE TABLE " . $data_source . ".`imports` (
  `import_id` int(11) NOT NULL AUTO_INCREMENT,
  `folder` varchar(45) DEFAULT NULL,
  `sub_folder` varchar(45) DEFAULT NULL,
  `filename` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`import_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE " . $data_source . ".`imports`
ADD COLUMN `processed` DATETIME NULL DEFAULT '0000-00-00 00:00:00' AFTER `filename`;

ALTER TABLE " . $data_source . ".`imports`
ADD COLUMN `full_filename` VARCHAR(255) NULL DEFAULT '' AFTER `filename`;

ALTER TABLE " . $data_source . ".`imports`
ADD COLUMN `dir` VARCHAR(255) NULL DEFAULT '' AFTER `import_id`;

 
ALTER TABLE " . $data_source . ".`tasks` ENGINE=MyISAM;
ALTER TABLE " . $data_source . ".`tasks`
ADD INDEX `CASENO` (`CASENO` ASC);

ALTER TABLE " . $data_source . ".`injury`
CHANGE COLUMN `ADJ1E` `ADJ1E` VARCHAR(1050) NULL DEFAULT NULL ;

CREATE TABLE " . $data_source . ".`folders` (
  `folders` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE " . $data_source . ".`tasks` ENGINE=InnoDB;
ALTER TABLE " . $data_source . ".`doctrk1` ENGINE=InnoDB;
ALTER TABLE " . $data_source . ".`card2` ENGINE=InnoDB;
ALTER TABLE " . $data_source . ".`card` ENGINE=InnoDB;
ALTER TABLE " . $data_source . ".`caseact` ENGINE=InnoDB;
ALTER TABLE " . $data_source . ".`actdeflt` ENGINE=InnoDB;
ALTER TABLE " . $data_source . ".`cal1` ENGINE=InnoDB;

ALTER TABLE " . $data_source . ".`case` ENGINE=InnoDB;
ALTER TABLE " . $data_source . ".`casecard` ENGINE=InnoDB;
";

/*
ALTER TABLE " . $data_source . ".`" . $data_source . "_case`
CHANGE COLUMN `cpointer` ` cpointer ` INT(11) NULL DEFAULT NULL ;

ALTER TABLE " . $data_source . ".`" . $data_source . "_activity`
ADD COLUMN `flag` VARCHAR(45) NULL DEFAULT '' AFTER `activity_date`;

ALTER TABLE " . $data_source . ".`" . $data_source . "_case` ENGINE=MyISAM;
ALTER TABLE " . $data_source . ".`" . $data_source . "_case`
ADD INDEX `cpointer` (`cpointer` ASC);

ALTER TABLE " . $data_source . ".`cal2` ENGINE=InnoDB;
ALTER TABLE " . $data_source . ".`" . $data_source . "_case` ENGINE=InnoDB;
*/
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	echo "pre prepped";
	$stmt = null; $db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
include("../api/cls_logging.php");
?>
</body>
</html>
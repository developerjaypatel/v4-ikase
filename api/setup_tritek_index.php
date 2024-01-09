<?php
require_once('../shared/legacy_session.php');
set_time_limit(3000);

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;

include("connection.php");

function getNickConnection() {
	//$dbhost="54.149.211.191";
	$dbhost="ikase.org";
	$dbuser="root";
	$dbpass="admin527#";
	$dbname="ikase";
	
	$dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);            
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbh;
}


try {
	$db = getNickConnection();
	
	include("customer_lookup.php");
	/*
	INSERT INTO ikase_" . $data_source . ".cse_eams_forms
SELECT * FROM ikase.cse_eams_forms WHERE deleted = 'N'
AND name != '';
*/
	die($data_source);
	$sql_index = "
CREATE TABLE IF NOT EXISTS `" . $data_source . "`.`note_available` (
  `cpointer` int(11) NOT NULL,
  `case_uuid` varchar(45) DEFAULT NULL,
  `notes_case_uuid` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`cpointer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `" . $data_source . "`.note_available (cpointer, case_uuid)
SELECT cpointer, case_uuid 
FROM `" . $data_source . "`." . $data_source . "_case

ALTER TABLE `" . $data_source . "`.`compinj` 
ENGINE = MyISAM ;
ALTER TABLE `" . $data_source . "`.`compinj` 
ADD INDEX ` compinjpnt` (`compinjpnt` ASC);
ALTER TABLE `" . $data_source . "`.`compinj` 
ADD INDEX `recno` (`recno` ASC);

ALTER TABLE `" . $data_source . "`.`employer` 
ENGINE = MyISAM ;
ALTER TABLE `" . $data_source . "`.`employer` 
ADD INDEX `epointer` (`epointer` ASC);

ALTER TABLE `" . $data_source . "`.`ccourts` 
ENGINE = MyISAM ;
ALTER TABLE `" . $data_source . "`.`ccourts` 
ADD INDEX `courtpoint` (`courtpoint` ASC),
ADD INDEX `courtpnt` (`courtpnt` ASC);

ALTER TABLE `" . $data_source . "`.`courts` 
ENGINE = MyISAM ;
ALTER TABLE `" . $data_source . "`.`courts` 
ADD INDEX `courtpnt` (`courtpnt` ASC),
ADD INDEX `courtpoint` (`courtpoint` ASC),
ADD INDEX `recno` (`recno` ASC);

ALTER TABLE `" . $data_source . "`.`contacts` 
ENGINE = MyISAM ;
ALTER TABLE `" . $data_source . "`.`contacts` 
ADD INDEX `cpointer` (`cpointer` ASC);

ALTER TABLE `" . $data_source . "`.`clinics` 
ENGINE = MyISAM ;
ALTER TABLE `" . $data_source . "`.`clinics` 
ADD INDEX `clinicpnt` (`clinicpnt` ASC);
ALTER TABLE `" . $data_source . "`.`clinics` 
ADD INDEX `clinicname` (`clinicname` ASC);


ALTER TABLE `" . $data_source . "`.`opposing` 
ENGINE = MyISAM ;
ALTER TABLE `" . $data_source . "`.`opposing` 
ADD INDEX `opppointer` (`opppointer` ASC),
ADD INDEX `recno` (`recno` ASC),
ADD INDEX `datapoint` (`datapoint` ASC);

ALTER TABLE `" . $data_source . "`.`ins` 
ENGINE = MyISAM ;
ALTER TABLE `" . $data_source . "`.`ins` 
ADD INDEX `ipointer` (`ipointer` ASC),
ADD INDEX `inspointer` (`inspointer` ASC);

ALTER TABLE `" . $data_source . "`.`note1` 
ENGINE = MyISAM ;
ALTER TABLE `" . $data_source . "`.`note1` 
ADD INDEX `notepoint` (`notepoint` ASC),
ADD INDEX `docpointer` (`docpointer` ASC),
ADD INDEX `mailpoint` (`mailpoint` ASC);

ALTER TABLE `" . $data_source . "`.`note2` 
ENGINE = MyISAM ;
ALTER TABLE `" . $data_source . "`.`note2` 
ADD INDEX `notepoint` (`notepoint` ASC),
ADD INDEX `docpointer` (`docpointer` ASC),
ADD INDEX `mailpoint` (`mailpoint` ASC);

ALTER TABLE `" . $data_source . "`.`note3` 
ENGINE = MyISAM ;
ALTER TABLE `" . $data_source . "`.`note3` 
ADD INDEX `notepoint` (`notepoint` ASC),
ADD INDEX `docpointer` (`docpointer` ASC),
ADD INDEX `mailpoint` (`mailpoint` ASC);

ALTER TABLE `" . $data_source . "`.`note4` 
ENGINE = MyISAM ;
ALTER TABLE `" . $data_source . "`.`note4` 
ADD INDEX `notepoint` (`notepoint` ASC),
ADD INDEX `docpointer` (`docpointer` ASC),
ADD INDEX `mailpoint` (`mailpoint` ASC);

ALTER TABLE `" . $data_source . "`.`note5` 
ENGINE = MyISAM ;
ALTER TABLE `" . $data_source . "`.`note5` 
ADD INDEX `notepoint` (`notepoint` ASC),
ADD INDEX `docpointer` (`docpointer` ASC),
ADD INDEX `mailpoint` (`mailpoint` ASC);

ALTER TABLE `" . $data_source . "`.`note6` 
ENGINE = MyISAM ;
ALTER TABLE `" . $data_source . "`.`note6` 
ADD INDEX `notepoint` (`notepoint` ASC),
ADD INDEX `docpointer` (`docpointer` ASC),
ADD INDEX `mailpoint` (`mailpoint` ASC);

ALTER TABLE `" . $data_source . "`.`audit` 
ENGINE = MyISAM ;
ALTER TABLE `" . $data_source . "`.`audit` 
ADD INDEX `cpointer` (`cpointer` ASC);

ALTER TABLE `" . $data_source . "`.`audit2` 
ENGINE = MyISAM ;
ALTER TABLE `" . $data_source . "`.`audit2` 
ADD INDEX `cpointer` (`cpointer` ASC);

ALTER TABLE `" . $data_source . "`.`audit3` 
ENGINE = MyISAM ;
ALTER TABLE `" . $data_source . "`.`audit3` 
ADD INDEX `cpointer` (`cpointer` ASC);

ALTER TABLE `" . $data_source . "`.`events` 
ENGINE = MyISAM ;
ALTER TABLE `" . $data_source . "`.`events` 
ADD INDEX `evpointer` (`evpointer` ASC),
ADD INDEX `recno` (`recno` ASC);

ALTER TABLE `" . $data_source . "`.`todo` 
ENGINE = MyISAM ;
ALTER TABLE `" . $data_source . "`.`todo` 
ADD INDEX `evpointer` (`evpointer` ASC),
ADD INDEX `evcode` (`evcode` ASC);
ALTER TABLE `" . $data_source . "`.`todo` 
ADD INDEX `evworkcode` (`evworkcode` ASC),
ADD INDEX `evattycode` (`evattycode` ASC),
ADD INDEX `evcompby` (`evcompby` ASC),
ADD INDEX `enteredby` (`enteredby` ASC),
ADD INDEX `recno` (`recno` ASC);

ALTER TABLE `" . $data_source . "`.`workcode` 
ENGINE = MyISAM ;
ALTER TABLE `" . $data_source . "`.`workcode` 
ADD INDEX `workcode` (`workcode` ASC),
ADD INDEX `recno` (`recno` ASC);

ALTER TABLE `" . $data_source . "`.`medsum` 
ENGINE = MyISAM ;
ALTER TABLE `" . $data_source . "`.`medsum` 
ADD INDEX `medsumpnt` (`medsumpnt` ASC),
ADD INDEX `clinicpnt` (`clinicpnt` ASC);
ALTER TABLE `" . $data_source . "`.`medsum` 
ADD INDEX `provider` (`provider` ASC);

ALTER TABLE `" . $data_source . "`.`medicals` 
ENGINE = MyISAM ;
ALTER TABLE `" . $data_source . "`.`medicals` 
ADD INDEX `mpointer` (`mpointer` ASC),
ADD INDEX `medpnt` (`medpnt` ASC),
ADD INDEX `drname` (`drname` ASC);

ALTER TABLE `" . $data_source . "`.`evcode`  
CHANGE COLUMN `evdesc` `evdesc` CHAR(60) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL

ALTER TABLE `" . $data_source . "`.`client` 
ADD INDEX `accipoint` (`accipoint` ASC);

";
	
	//die($sql_index);
	$stmt = DB::run($sql_index);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
include("cls_logging.php");
?>
<script language="javascript">
parent.setFeedback("setup index completed");
</script>

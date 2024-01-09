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

//WHERE cli.fileno = 1061
//die($sql);
try {
	$db = getNickConnection();
	
	include("customer_lookup.php");
/*	
	$sql = "ALTER TABLE `" . $data_source . "`.`client` 
ADD INDEX `cpointer` (`cpointer` ASC),
ADD INDEX `accipoint` (`accipoint` ASC),
ADD INDEX `ctpnt` (`ctpnt` ASC);
ALTER TABLE `" . $data_source . "`.`client` 
ADD INDEX `oppospnt` (`oppospnt` ASC);
ALTER TABLE `" . $data_source . "`.`accident` 
ADD INDEX `apointer` (`apointer` ASC);
ALTER TABLE `" . $data_source . "`.`compinj` 
ADD INDEX ` compinjpnt` (`compinjpnt` ASC);
ALTER TABLE `" . $data_source . "`.`compinj` 
ADD INDEX `recno` (`recno` ASC);
ALTER TABLE `" . $data_source . "`.`employer` 
ADD INDEX `epointer` (`epointer` ASC);
ALTER TABLE `" . $data_source . "`.`ccourts` 
ADD INDEX `courtpoint` (`courtpoint` ASC),
ADD INDEX `courtpnt` (`courtpnt` ASC);
ALTER TABLE `" . $data_source . "`.`courts` 
ADD INDEX `courtpnt` (`courtpnt` ASC),
ADD INDEX `courtpoint` (`courtpoint` ASC),
ADD INDEX `recno` (`recno` ASC);
ALTER TABLE `" . $data_source . "`.`contacts` 
ADD INDEX `cpointer` (`cpointer` ASC);
ALTER TABLE `" . $data_source . "`.`clinics` 
ADD INDEX `clinicpnt` (`clinicpnt` ASC);
ALTER TABLE `" . $data_source . "`.`opposing` 
ADD INDEX `opppointer` (`opppointer` ASC),
ADD INDEX `recno` (`recno` ASC),
ADD INDEX `datapoint` (`datapoint` ASC);
ALTER TABLE `" . $data_source . "`.`ins` 
ADD INDEX `ipointer` (`ipointer` ASC),
ADD INDEX `inspointer` (`inspointer` ASC);
ALTER TABLE `" . $data_source . "`.`note1` 
ADD INDEX `notepoint` (`notepoint` ASC),
ADD INDEX `docpointer` (`docpointer` ASC),
ADD INDEX `mailpoint` (`mailpoint` ASC);

ALTER TABLE `" . $data_source . "`.`note2` 
ADD INDEX `notepoint` (`notepoint` ASC),
ADD INDEX `docpointer` (`docpointer` ASC),
ADD INDEX `mailpoint` (`mailpoint` ASC);
ALTER TABLE `" . $data_source . "`.`note3` 
ADD INDEX `notepoint` (`notepoint` ASC),
ADD INDEX `docpointer` (`docpointer` ASC),
ADD INDEX `mailpoint` (`mailpoint` ASC);
ALTER TABLE `" . $data_source . "`.`note4` 
ADD INDEX `notepoint` (`notepoint` ASC),
ADD INDEX `docpointer` (`docpointer` ASC),
ADD INDEX `mailpoint` (`mailpoint` ASC);
ALTER TABLE `" . $data_source . "`.`note5` 
ADD INDEX `notepoint` (`notepoint` ASC),
ADD INDEX `docpointer` (`docpointer` ASC),
ADD INDEX `mailpoint` (`mailpoint` ASC);
ALTER TABLE `" . $data_source . "`.`note6` 
ADD INDEX `notepoint` (`notepoint` ASC),
ADD INDEX `docpointer` (`docpointer` ASC),
ADD INDEX `mailpoint` (`mailpoint` ASC);
ALTER TABLE `" . $data_source . "`.`audit` 
ADD INDEX `cpointer` (`cpointer` ASC);
ALTER TABLE `" . $data_source . "`.`audit2` 
ADD INDEX `cpointer` (`cpointer` ASC);
ALTER TABLE `" . $data_source . "`.`audit3` 
ADD INDEX `cpointer` (`cpointer` ASC);
ALTER TABLE `" . $data_source . "`.`audit4` 
ADD INDEX `cpointer` (`cpointer` ASC);
ALTER TABLE `" . $data_source . "`.`document` 
ADD INDEX `cpointer` (`cpointer` ASC),
ADD INDEX `recno` (`recno` ASC);
ALTER TABLE `" . $data_source . "`.`events` 
ADD INDEX `evpointer` (`evpointer` ASC),
ADD INDEX `recno` (`recno` ASC);
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
ADD INDEX `workcode` (`workcode` ASC),
ADD INDEX `recno` (`recno` ASC);
ALTER TABLE `costs` 
ADD INDEX `costpnt` (`costpnt` ASC),
ADD INDEX `date` (`date` ASC);
";
	//ONE TIME
	//die($sql);
	$stmt = DB::run($sql);
	*/

	$sql_truncate = "TRUNCATE `" . $data_source . "`." . $data_source . "_injury; 
TRUNCATE `" . $data_source . "`." . $data_source . "_case_injury; 
TRUNCATE `" . $data_source . "`." . $data_source . "_injury; 
TRUNCATE `" . $data_source . "`." . $data_source . "_injury_number; 
TRUNCATE `" . $data_source . "`." . $data_source . "_injury_injury_number; 
TRUNCATE `" . $data_source . "`." . $data_source . "_case; 
TRUNCATE `" . $data_source . "`." . $data_source . "_corporation; 
TRUNCATE `" . $data_source . "`." . $data_source . "_case_corporation; 
TRUNCATE `" . $data_source . "`." . $data_source . "_negotiation; 
TRUNCATE `" . $data_source . "`." . $data_source . "_case_negotiation; 
TRUNCATE `" . $data_source . "`." . $data_source . "_lostincome; 
TRUNCATE `" . $data_source . "`." . $data_source . "_case_lostincome; 
TRUNCATE `" . $data_source . "`." . $data_source . "_corporation_adhoc; 
TRUNCATE `" . $data_source . "`." . $data_source . "_person; 
TRUNCATE `" . $data_source . "`." . $data_source . "_case_person; 
TRUNCATE `" . $data_source . "`.`" . $data_source . "_notes`; 
TRUNCATE `" . $data_source . "`.`" . $data_source . "_case_notes`;

TRUNCATE `" . $data_source . "`.`" . $data_source . "_fee`; 
TRUNCATE `" . $data_source . "`.`" . $data_source . "_settlement`;
TRUNCATE `" . $data_source . "`.`" . $data_source . "_settlement_fee`;
TRUNCATE `" . $data_source . "`.`" . $data_source . "_injury_settlement`;

TRUNCATE `" . $data_source . "`.`" . $data_source . "_exam`; 
TRUNCATE `" . $data_source . "`.`" . $data_source . "_corporation_exam`; 

TRUNCATE `" . $data_source . "`.`" . $data_source . "_exam`; 
TRUNCATE `" . $data_source . "`.`" . $data_source . "_corporation_exam`;
TRUNCATE `" . $data_source . "`.`" . $data_source . "_activity`; 
TRUNCATE `" . $data_source . "`.`" . $data_source . "_case_activity`;
TRUNCATE `" . $data_source . "`.`" . $data_source . "_medicalbilling`; 
TRUNCATE `" . $data_source . "`.`" . $data_source . "_case_medicalbilling`;
TRUNCATE `" . $data_source . "`.`" . $data_source . "_notes`; 
TRUNCATE `" . $data_source . "`.`" . $data_source . "_case_notes`;
#DELETE FROM `" . $data_source . "`.`" . $data_source . "_notes` WHERE `type` = 'document'; 
#DELETE FROM `" . $data_source . "`.`" . $data_source . "_case_notes` WHERE `attribute` = 'document';
TRUNCATE `" . $data_source . "`.`" . $data_source . "_event`; 
TRUNCATE `" . $data_source . "`.`" . $data_source . "_case_event`;
TRUNCATE `" . $data_source . "`.`" . $data_source . "_task`; 
TRUNCATE `" . $data_source . "`.`" . $data_source . "_case_task`; 
TRUNCATE `" . $data_source . "`.`" . $data_source . "_task_user`;
TRUNCATE `" . $data_source . "`.`" . $data_source . "_check`; 
TRUNCATE `" . $data_source . "`.`" . $data_source . "_case_check`; 
    
TRUNCATE `" . $data_source . "`.`" . $data_source . "_claim`

TRUNCATE `" . $data_source . "`.`" . $data_source . "_financial`;

TRUNCATE `" . $data_source . "`.`" . $data_source . "_corporation_financial`;
";
//die($sql_truncate);

	$stmt = DB::run($sql_truncate);
	//die();
	/*	
	$sql_index = "
ALTER TABLE `" . $data_source . "`.`client` 
ADD INDEX `cpointer` (`cpointer` ASC),
ADD INDEX `accipoint` (`accipoint` ASC),
ADD INDEX `ctpnt` (`ctpnt` ASC);
ALTER TABLE `" . $data_source . "`.`client` 
ADD INDEX `oppospnt` (`oppospnt` ASC);
ALTER TABLE `" . $data_source . "`.`accident` 
ADD INDEX `apointer` (`apointer` ASC);
ALTER TABLE `" . $data_source . "`.`compinj` 
ADD INDEX ` compinjpnt` (`compinjpnt` ASC);
ALTER TABLE `" . $data_source . "`.`compinj` 
ADD INDEX `recno` (`recno` ASC);
ALTER TABLE `" . $data_source . "`.`employer` 
ADD INDEX `epointer` (`epointer` ASC);
ALTER TABLE `" . $data_source . "`.`ccourts` 
ADD INDEX `courtpoint` (`courtpoint` ASC),
ADD INDEX `courtpnt` (`courtpnt` ASC);
ALTER TABLE `" . $data_source . "`.`courts` 
ADD INDEX `courtpnt` (`courtpnt` ASC),
ADD INDEX `courtpoint` (`courtpoint` ASC),
ADD INDEX `recno` (`recno` ASC);
ALTER TABLE `" . $data_source . "`.`contacts` 
ADD INDEX `cpointer` (`cpointer` ASC);
ALTER TABLE `" . $data_source . "`.`clinics` 
ADD INDEX `clinicpnt` (`clinicpnt` ASC);
ALTER TABLE `" . $data_source . "`.`opposing` 
ADD INDEX `opppointer` (`opppointer` ASC),
ADD INDEX `recno` (`recno` ASC),
ADD INDEX `datapoint` (`datapoint` ASC);
ALTER TABLE `" . $data_source . "`.`ins` 
ADD INDEX `ipointer` (`ipointer` ASC),
ADD INDEX `inspointer` (`inspointer` ASC);
ALTER TABLE `" . $data_source . "`.`note1` 
ADD INDEX `notepoint` (`notepoint` ASC),
ADD INDEX `docpointer` (`docpointer` ASC),
ADD INDEX `mailpoint` (`mailpoint` ASC);

ALTER TABLE `" . $data_source . "`.`note2` 
ADD INDEX `notepoint` (`notepoint` ASC),
ADD INDEX `docpointer` (`docpointer` ASC),
ADD INDEX `mailpoint` (`mailpoint` ASC);
ALTER TABLE `" . $data_source . "`.`note3` 
ADD INDEX `notepoint` (`notepoint` ASC),
ADD INDEX `docpointer` (`docpointer` ASC),
ADD INDEX `mailpoint` (`mailpoint` ASC);
ALTER TABLE `" . $data_source . "`.`note4` 
ADD INDEX `notepoint` (`notepoint` ASC),
ADD INDEX `docpointer` (`docpointer` ASC),
ADD INDEX `mailpoint` (`mailpoint` ASC);
ALTER TABLE `" . $data_source . "`.`note5` 
ADD INDEX `notepoint` (`notepoint` ASC),
ADD INDEX `docpointer` (`docpointer` ASC),
ADD INDEX `mailpoint` (`mailpoint` ASC);
ALTER TABLE `" . $data_source . "`.`note6` 
ADD INDEX `notepoint` (`notepoint` ASC),
ADD INDEX `docpointer` (`docpointer` ASC),
ADD INDEX `mailpoint` (`mailpoint` ASC);
ALTER TABLE `" . $data_source . "`.`audit` 
ADD INDEX `cpointer` (`cpointer` ASC);
ALTER TABLE `" . $data_source . "`.`audit2` 
ADD INDEX `cpointer` (`cpointer` ASC);
ALTER TABLE `" . $data_source . "`.`audit3` 
ADD INDEX `cpointer` (`cpointer` ASC);
ALTER TABLE `" . $data_source . "`.`audit4` 
ADD INDEX `cpointer` (`cpointer` ASC);
ALTER TABLE `" . $data_source . "`.`document` 
ADD INDEX `cpointer` (`cpointer` ASC),
ADD INDEX `recno` (`recno` ASC);
ALTER TABLE `" . $data_source . "`.`events` 
ADD INDEX `evpointer` (`evpointer` ASC),
ADD INDEX `recno` (`recno` ASC);
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
ADD INDEX `workcode` (`workcode` ASC),
ADD INDEX `recno` (`recno` ASC);

ALTER TABLE `" . $data_source . "`.`" . $data_source . "_case_check` 
ADD COLUMN `check_counter` INT NULL DEFAULT 0 AFTER `case_check_id`;

ALTER TABLE `" . $data_source . "`.`" . $data_source . "_check` 
ADD COLUMN `check_counter` INT NULL DEFAULT 0 AFTER `check_uuid`;

ALTER TABLE `" . $data_source . "`.`" . $data_source . "_check` 
ADD COLUMN `check_number` VARCHAR(45) NULL DEFAULT '' AFTER `check_uuid`;

ALTER TABLE `" . $data_source . "`.`" . $data_source . "_check` 
CHANGE COLUMN `check_name` `check_name` VARCHAR(155) NOT NULL DEFAULT '',
CHANGE COLUMN `check_address` `check_address` VARCHAR(155) NOT NULL DEFAULT '',
CHANGE COLUMN `check_phone` `check_phone` VARCHAR(50) NOT NULL DEFAULT '',
CHANGE COLUMN `cell_carrier` `cell_carrier` VARCHAR(50) NOT NULL DEFAULT '' ;

ALTER TABLE ``" . $data_source . "`.``" . $data_source . "_check` 
ADD COLUMN `check_date` DATE NULL DEFAULT '0000-00-00' AFTER `deleted`,
ADD COLUMN `check_type` VARCHAR(45) NULL DEFAULT '' AFTER `check_date`,
ADD COLUMN `amount_due` DECIMAL(7,2) NULL DEFAULT 0 AFTER `check_type`,
ADD COLUMN `payment` DECIMAL(7,2) NULL DEFAULT 0 AFTER `amount_due`,
ADD COLUMN `balance` DECIMAL(7,2) NULL DEFAULT 0 AFTER `payment`,
ADD COLUMN `transaction_date` DATE NULL DEFAULT '0000-00-00' AFTER `balance`,
ADD COLUMN `memo` VARCHAR(1055) NULL DEFAULT '' AFTER `transaction_date`;

ALTER TABLE `" . $data_source . "`.`" . $data_source . "_check` 
CHANGE COLUMN `customer_id` `customer_id` INT(11) NOT NULL DEFAULT 0 ;

ALTER TABLE `ikase_" . $data_source . "`.`cse_case` 
ADD COLUMN `file_number` VARCHAR(255) NULL DEFAULT '' AFTER `case_number`;

ALTER TABLE `ikase_" . $data_source . "`.`cse_case_track` 
ADD COLUMN `file_number` VARCHAR(255) NULL DEFAULT '' AFTER `case_number`;

ALTER TABLE `" . $data_source . "`.`medsum` 
ADD INDEX `medsumpnt` (`medsumpnt` ASC),
ADD INDEX `clinicpnt` (`clinicpnt` ASC);
ALTER TABLE `" . $data_source . "`.`medsum` 
ADD INDEX `provider` (`provider` ASC);
ALTER TABLE `" . $data_source . "`.`medicals` 
ADD INDEX `mpointer` (`mpointer` ASC),
ADD INDEX `medpnt` (`medpnt` ASC),
ADD INDEX `drname` (`drname` ASC);
ALTER TABLE `" . $data_source . "`.`clinics` 
ADD INDEX `clinicname` (`clinicname` ASC);
";
	
	//die($sql_index);
	$stmt = DB::run($sql_index);
		
	$query = "SELECT DISTINCT `TABLE_NAME`, `COLUMN_NAME`, `COLUMN_TYPE`
    FROM INFORMATION_SCHEMA.COLUMNS
    where TABLE_SCHEMA = '" . $data_source . "'
   AND TABLE_SCHEMA NOT LIKE '" . $data_source . "_%'
   ORDER BY `TABLE_NAME`, `COLUMN_NAME`";
	$tables = DB::select($query);
	foreach($tables as $table) {
		//echo $cname . "<br />";
		$cname = $table->COLUMN_NAME;
		
		if ($cname != strtolower($cname)) {
			//$sql = " ALTER TABLE `" . $data_source . "`.`" . $table->TABLE_NAME . "` RENAME COLUMN `" . $cname . "` to  `" . strtolower($cname) . "`";
			$sql = "ALTER TABLE `" . $data_source . "`.`" . $table->TABLE_NAME . "` CHANGE COLUMN `" . $cname . "` `" . strtolower($cname) . "` " . $table->COLUMN_TYPE;
			echo $sql . "<br />";
			
			
			$stmt = DB::run($sql);
			
		}
	}
	*/
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
include("cls_logging.php");
?>
<script language="javascript">
parent.setFeedback("setup completed");
</script>

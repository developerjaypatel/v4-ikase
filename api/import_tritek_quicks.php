<?php
require_once('../shared/legacy_session.php');
set_time_limit(3000);

error_reporting(E_ALL);
ini_set('display_errors', '1');

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;

include("connection.php");

?>
<script language="javascript">
parent.setFeedback("notes import started");
</script>
<?php
$db = getConnection();
try {
	include("customer_lookup.php");
	
	$sql = " CREATE TABLE IF NOT EXISTS `" . $data_source . "`.`badquicks` (
  `badquicks_id` INT NOT NULL AUTO_INCREMENT,
  `cpointer` VARCHAR(15) NULL DEFAULT '',
  `deleted` VARCHAR(45) NULL DEFAULT 'N',
  PRIMARY KEY (`badquicks_id`))
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;";
	
	$sql .= "
	TRUNCATE `" . $data_source . "`.badquicks;
	
	INSERT INTO `" . $data_source . "`.`badquicks` (cpointer)
	SELECT DISTINCT cli.cpointer
	FROM `" . $data_source . "`.`client` cli;";
	
	$sql .= "
	DELETE FROM `" . $data_source . "`.`" . $data_source . "_case_notes`
	WHERE attribute = 'quick';";
	
	$sql .= "
	DELETE FROM `" . $data_source . "`.`" . $data_source . "_notes`
	WHERE `type` = 'quick;'";
	
	//echo $sql . "<br /><br />\r\n\r\n";
	//die();
	//$stmt = $db->prepare($sql);
	//$stmt->execute();
	//die();
			
	$sql = "SELECT DISTINCT
        badquicks.*, mc.case_uuid, cli.opendate, cli.cmemo
    FROM
        `" . $data_source . "`.`" . $data_source . "_case` mc
    INNER JOIN `" . $data_source . "`.`badquicks` ON mc.cpointer = badquicks.cpointer
	INNER JOIN `" . $data_source . "`.`client` cli ON badquicks.cpointer = cli.cpointer
    WHERE
        1 and badquicks.deleted = 'N'
    LIMIT 0,1";
	$stmt = $db->prepare($sql);
	echo $sql . "<br /><br />\r\n\r\n";
	//die();
	$stmt->execute();
	
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	echo " => initial spent:" . $total_time . "<br /><br />";

	$cases = $stmt->fetchAll(PDO::FETCH_OBJ);
	$arrCaseUUID =  array();
	$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$row_start_time = $time;
		
	//die(print_r($cases));
	foreach($cases as $key=>$case){
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$row_start_time = $time;
		
		$cpointer = $case->cpointer;
		$case_uuid = $case->case_uuid;
		echo "Processing -> " . $key. " == " . $cpointer . "<br /><br />\r\n";
		if (in_array($cpointer, $arrCaseUUID)) {
			//one time per pointer
			continue;
		} 
		$arrCaseUUID[] = $cpointer;
		

		$last_updated_date = date("Y-m-d H:i:s");
		$case_notes_uuid = uniqid("CN", false);
		$notes_uuid = uniqid("NT", false);
		
		$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_notes` (`case_notes_uuid`, `case_uuid`,  `notes_counter`, `notes_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
		VALUES ('" . $case_notes_uuid . "', '" . $case_uuid . "', 0, '" . $notes_uuid . "', 'quick', '" . $last_updated_date . "', 'system', 'N', '" . $customer_id . "')";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		echo $sql . "\r\n\r\n<br><br>";
		//die();
		$stmt->execute();
		if (date("Y", strtotime($case->opendate)) < 1996) {
			$case->opendate = date("Y-m-d");
		}
		$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_notes` (`notes_counter`, `notes_uuid`, `note`, `dateandtime`, `entered_by`, `customer_id`, `type`)
		VALUES (0, '" . $notes_uuid . "', '" . addslashes($case->cmemo) . "', '" . date("Y-m-d", strtotime($case->opendate)) . "', 'system', '" . $customer_id . "', 'quick')";
		
		echo $sql . "\r\n\r\n<br><br>";
		$stmt = DB::run($sql);
		
		$sql = "UPDATE `" . $data_source . "`.`badquicks` 
		SET deleted = 'Y'
		WHERE cpointer = '" . $cpointer . "'";
		echo $sql . "\r\n\r\n<br><br>";
		$stmt = DB::run($sql);
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $row_start_time), 4);
		
		//echo "Time3 spent:" . $total_time . "<br /><br />";		
	}
		
	//completeds
	$sql = "SELECT COUNT(cpointer) case_count
	FROM `" . $data_source . "`.`badquicks` ggc
	WHERE 1";
	//echo $sql . "\r\n<br>";
	//die();
	$stmt = DB::run($sql);
	$cases = $stmt->fetchObject();
	
	$case_count = $cases->case_count;
	

	//completeds
	$sql = "SELECT COUNT(cpointer) case_count
	FROM `" . $data_source . "`.`badquicks` ggc
	WHERE 1
	AND `deleted` = 'Y'";
	//echo $sql . "\r\n<br>";
	//die();
	$stmt = DB::run($sql);
	$cases = $stmt->fetchObject();
	
	$completed_count = $cases->case_count;

	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	echo " => Time spent:" . $total_time . "<br />
<br />
";
	$success = array("success"=> array("text"=>"done", "duration"=>$total_time, "completed_count"=>$completed_count, "case_count"=>$case_count));
	
	if (count($cases) > 0) {
		//die("script language='javascript'>parent.runMain(" . $completed_count . "," . $case_count . ")</script");
		echo "<script language='javascript'>parent.runQuicks(" . $completed_count . "," . $case_count . ")</script>";
	}
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}	
//, `notedesc`, , `notepoint`, `notelock`, `lockedby`, `xsoundex`, `locator`, `enteredby`, `mailpoint`, `lockloc`, `docpointer`, `doctype`

?>

<?php
include("manage_session.php");
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
	
	$sql = "SELECT DISTINCT
        badvenues.*, mc.case_uuid
    FROM
        `" . $data_source . "`.`" . $data_source . "_case` mc
    INNER JOIN `" . $data_source . "`.`badvenues` ON mc.cpointer = badvenues.cpointer
    WHERE
        1 and badvenues.deleted = 'N'
    #ORDER BY mc.cpointer DESC
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
		echo "Processing -> " . $key. " == " . $cpointer . "<br /><br />\r\n";
		if (in_array($cpointer, $arrCaseUUID)) {
			//one time per pointer
			continue;
		} 
		$arrCaseUUID[] = $cpointer;
		
		
		$sql = "UPDATE " . $data_source . "." . $data_source . "_case
		SET venue = '" . $case->venue_uuid . "'
		
		WHERE cpointer = '" . $cpointer . "'";
		echo $sql . "<br /><br />\r\n\r\n";
		//die();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $row_start_time), 4);
		
		echo "last spent:" . $total_time . "<br /><br />";		
		
		$parent_table_uuid = $case->venue_uuid;
		$case_venue_uuid = uniqid("KS", false);
		$last_updated_date = date("Y-m-d H:i:s");
				
		$sql = "UPDATE `" . $data_source . "`.`" . $data_source . "_case_venue` 
		SET `venue_uuid` = '" . $parent_table_uuid . "'
		WHERE case_uuid = '" . $case->case_uuid . "'";
		echo $sql . "\r\n\r\n<br><br>";
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		$table_uuid = uniqid("VN", false);
		//now save the venue as corporation for parties
		$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation`(`corporation_uuid`, `parent_corporation_uuid`, `company_name`, `type`, `aka`, `employee_phone`, `full_name`, `full_address`, `street`, `city`, `state`, `zip`, `salutation`, `copying_instructions`, `customer_id`) 
		SELECT '" . $table_uuid . "', '" . $parent_table_uuid . "', `venue`, 'venue', badvenues.`venue_abbr`, `phone`, badvenues.judge, CONCAT(`address1`, ',', `address2`,',', `city`,' ', `zip`) full_address, CONCAT(`address1`,',', `address2`) street, `city`,'CA', `zip`, 'Your Honor', '', " . $customer_id . " 
		FROM `ikase`.`cse_venue`
		INNER JOIN `" . $data_source . "`.`badvenues` ON cse_venue.venue_uuid = badvenues.venue_uuid
		WHERE cse_venue.venue_uuid = '" . $parent_table_uuid . "'
		AND badvenues.cpointer = '" . $cpointer . "'";
		echo $sql . "\r\n\r\n<br><br>";
		//die();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		$table_name = "corporation";
		$case_table_uuid = uniqid("KA", false);
		$attribute_1 = "main";
		$last_updated_date = date("Y-m-d H:i:s");
		$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_" . $table_name . "` (`case_" . $table_name . "_uuid`, `case_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
	VALUES ('" . $case_table_uuid  ."', '" . $case->case_uuid . "', '" . $table_uuid . "', 'venue', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";						
		echo $sql . "\r\n\r\n<br><br>";
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		$sql = "UPDATE `" . $data_source . "`.`badvenues` 
		SET deleted = 'Y'
		WHERE cpointer = '" . $cpointer . "'";
		echo $sql . "\r\n\r\n<br><br>";
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $row_start_time), 4);
		
		//echo "Time3 spent:" . $total_time . "<br /><br />";		
	}
		
	//completeds
	$sql = "SELECT COUNT(cpointer) case_count
	FROM `" . $data_source . "`.`badvenues` ggc
	WHERE 1";
	//echo $sql . "\r\n<br>";
	//die();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$cases = $stmt->fetchObject();
	
	$case_count = $cases->case_count;
	

	//completeds
	$sql = "SELECT COUNT(cpointer) case_count
	FROM `" . $data_source . "`.`badvenues` ggc
	WHERE 1
	AND `deleted` = 'Y'";
	//echo $sql . "\r\n<br>";
	//die();
	$stmt = $db->prepare($sql);
	$stmt->execute();
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
		echo "<script language='javascript'>parent.runVenues(" . $completed_count . "," . $case_count . ")</script>";
	}
	
	$db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}	
//, `notedesc`, , `notepoint`, `notelock`, `lockedby`, `xsoundex`, `locator`, `enteredby`, `mailpoint`, `lockloc`, `docpointer`, `doctype`

?>
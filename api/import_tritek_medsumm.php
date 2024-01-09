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
	
	$sql = "SELECT gcase.* 
	FROM `" . $data_source . "`.`badmeds` gcase
	
	INNER JOIN `" . $data_source . "`.`medicals` med
	ON gcase.cpointer = med.mpointer
	
	INNER JOIN `" . $data_source . "`.`badcases` bcase
	ON gcase.case_uuid = bcase.case_uuid
	
	WHERE gcase.processed = 'N'
	#AND cpointer = '1001801'
	LIMIT 0, 1";
	/*
	$sql = "SELECT DISTINCT
        badmeds.cpointer, mc.case_uuid
    FROM
        `" . $data_source . "`.`" . $data_source . "_case` mc
    
	INNER JOIN `" . $data_source . "`.`badmeds` 
	ON mc.cpointer = badmeds.cpointer
	
	INNER JOIN " . $data_source . ".medicals med	
	ON badmeds.cpointer = med.mpointer
    
    INNER JOIN `goldberg2`.medbill
	ON med.medbpnt = medbill.medbpnt
	
	WHERE
        1 and badmeds.deleted = 'N'
    LIMIT 0,1";
	*/
	$stmt = $db->prepare($sql);
	//echo $sql . "<br /><br />\r\n\r\n";
	//die();
	$stmt->execute();
	
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	//echo " => initial spent:" . $total_time . "<br /><br />";

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
		
		//doctors
		$sql_medical = "SELECT DISTINCT corp.corporation_uuid, corp.company_name, med.medpnt
		FROM " . $data_source . ".medicals med
		
		INNER JOIN " . $data_source . "." . $data_source . "_corporation corp
		ON med.medpnt = corp.parent_corporation_uuid
		
		INNER JOIN " . $data_source . "." . $data_source . "_case_corporation ccorp
		ON corp.corporation_uuid = ccorp.corporation_uuid
		
		INNER JOIN " . $data_source . "." . $data_source . "_case ccase
		ON ccorp.case_uuid = ccase.case_uuid AND ccase.cpointer = '" . $case->cpointer . "'
		
		INNER JOIN `" . $data_source . "`.medbill
		ON med.medbpnt = medbill.medbpnt
		
		WHERE 1
		AND corp.corporation_uuid != corp.parent_corporation_uuid
		#AND med.mpointer = '" . $case->cpointer . "'";

		$stmt = $db->prepare($sql_medical);
		//echo $sql_medical . "\r\n\r\n<br><br>";
		//die();
		$stmt->execute();
		$medicals = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $header_start_time), 4);
		//echo " => QUERY completed in " . $total_time . "<br /><br />";
		//print_r($medicals);
		//die();
		foreach ($medicals as $medical) {
			$table_uuid = $medical->corporation_uuid;
			
			//medical billing
			$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_medicalbilling`
	(`medicalbilling_uuid`, `corporation_uuid`, `user_uuid`, `bill_date`, `billed`, `paid`, `adjusted`, `balance`, `customer_id`) ";
			$sql .= "
			SELECT DISTINCT 
			CONCAT('" . $medical->medpnt . "', '_', '" . $cpointer . "', '_', @curRow := @curRow + 1) AS `medicalbilling_uuid`,
			'" . $table_uuid . "', 'system', STR_TO_DATE(medbill.`date`,  '%m/%d/%Y' ) bill_date, medbill.amount, medbill.payment, medbill.adjustment,  
			(medbill.amount - medbill.payment - medbill.adjustment) balance, '" . $customer_id . "'
			FROM `" . $data_source . "`.medicals med
			
			INNER JOIN `" . $data_source . "`.`clinics` `clin` 
			ON `med`.medpnt = clin.clinicpnt
			
			INNER JOIN `" . $data_source . "`.medbill
			ON med.medbpnt = medbill.medbpnt
			
			JOIN    (SELECT @curRow := 0) r
	
			WHERE 1
			AND `med`.medpnt = '" . $medical->medpnt . "'
			AND med.mpointer = '" . $case->cpointer . "'
			AND INSTR(medbill.`date`, ' ') = 0
			AND LENGTH(medbill.`date`) = 10
			AND medbill.`date` != '25/28/2016'
			AND medbill.`date` != '10/92/1992'
			AND medbill.`date` != '21/28/1992'
			AND STR_TO_DATE(medbill.`date`, '%m/%d/%Y') IS NOT NULL
			
			
			
			AND medbill.`date`  != ''
			AND medbill.`date` != '  /  /'
			AND medbill.`date` != '20/92/011'
			AND medbill.`date` != '11/42/13'
			AND medbill.`date` != '99/08/1992'
			AND medbill.`date` != '99/8/1992'
			AND medbill.`date` != '32/62/018'
			AND medbill.`date` != '09/ /2013' 
			AND medbill.`date` != '32/11/1'
			AND medbill.`date` != '06/31/2013'
			
			ORDER BY medbill.medbpnt, `date`";
			
			//echo $sql . "\r\n\r\n<br><br>";  
			
			//continue;
			//die();
			//die();
			$stmt = DB::run($sql);
			
			//attach to case
			$last_updated_date = date("Y-m-d H:i:s");
			$case_medicalbilling_uuid = uniqid("KA", false);
			$attribute = "main";
			
			$sql_billing = "INSERT INTO `" . $data_source . "`." . $data_source . "_case_medicalbilling (`case_medicalbilling_uuid`, `case_uuid`, `medicalbilling_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			SELECT '" . $case_medicalbilling_uuid . "', '" . $case_uuid . "', 
			CONCAT('" . $medical->medpnt . "', '_', '" . $cpointer . "', '_', @curRow := @curRow + 1) AS `medicalbilling_uuid`,
			'" . $attribute . "', '" . $last_updated_date . "', 'system', '" . $customer_id . "'
			FROM `" . $data_source . "`.medicals med
			
			INNER JOIN `" . $data_source . "`.`clinics` `clin` 
			ON `med`.medpnt = clin.clinicpnt
			
			INNER JOIN `" . $data_source . "`.medbill
			ON med.medbpnt = medbill.medbpnt
			
			JOIN    (SELECT @curRow := 0) r
	
			WHERE 1
			AND `med`.medpnt = '" . $medical->medpnt . "'
			AND med.mpointer = '" . $cpointer . "'
			AND INSTR(medbill.`date`, ' ') = 0
			AND LENGTH(medbill.`date`) = 10
			AND medbill.`date` != '25/28/2016'
			AND medbill.`date`  != ''
			AND medbill.`date` != '  /  /'
			AND medbill.`date` != '20/92/011'
			AND medbill.`date` != '11/42/13'
			AND medbill.`date` != '32/62/018'
			AND medbill.`date` != '09/ /2013' 
			AND medbill.`date` != '32/11/1'
			AND medbill.`date` != '21/28/1992'
			AND medbill.`date` != '99/08/1992'
			AND medbill.`date` != '99/8/1992'
			AND medbill.`date` != '10/92/1992'
			AND medbill.`date` != '06/31/2013'
			
			AND STR_TO_DATE(medbill.`date`, '%m/%d/%Y') IS NOT NULL 
			
			ORDER BY medbill.medbpnt, `date`";
			//echo $sql_billing . "\r\n";
	
			$stmt = DB::run($sql_billing);
			
			$time = microtime();
			$time = explode(' ', $time);
			$time = $time[1] + $time[0];
			$finish_time = $time;
			$total_time = round(($finish_time - $row_start_time), 4);
			
			//echo "Time3 spent:" . $total_time . "<br /><br />";	
		}
		
		$sql = "UPDATE `" . $data_source . "`.`badmeds` 
		SET processed = 'Y'
		WHERE cpointer = '" . $cpointer . "'";
		//echo $sql . "\r\n\r\n<br><br>";
		$stmt = DB::run($sql);
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $row_start_time), 4);	
	}
	
	//die("stop");
	
	//completeds
	$sql = "SELECT COUNT(badmeds_id) case_count
	FROM `" . $data_source . "`.`badmeds` ggc
	#REMOVE THIS ON NEXT IMPORT
	#INNER JOIN `" . $data_source . "`.`badcases` bcase
	#ON ggc.case_uuid = bcase.case_uuid
	#
	WHERE 1";
	//echo $sql . "\r\n<br>";
	//die();
	$stmt = DB::run($sql);
	$cases = $stmt->fetchObject();
	
	$case_count = $cases->case_count;
	

	//completeds
	$sql = "SELECT COUNT(badmeds_id) case_count
	FROM `" . $data_source . "`.`badmeds` ggc
	#REMOVE THIS ON NEXT IMPORT
	#INNER JOIN `" . $data_source . "`.`badcases` bcase
	#ON ggc.case_uuid = bcase.case_uuid
	#
	WHERE 1
	AND ggc.`processed` = 'Y'";
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
		echo "<script language='javascript'>parent.runMeds(" . $completed_count . "," . $case_count . ")</script>";
	}
} catch(PDOException $e) {
	echo $sql . "<br />";
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}	
//, `notedesc`, , `notepoint`, `notelock`, `lockedby`, `xsoundex`, `locator`, `enteredby`, `mailpoint`, `lockloc`, `docpointer`, `doctype`

?>

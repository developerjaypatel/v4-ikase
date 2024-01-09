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
	
	$sql = "SELECT gcase.*, ccase.case_number, cci.injury_uuid
	FROM `" . $data_source . "`.`badfees` gcase
	INNER JOIN `" . $data_source . "`.`" . $data_source . "_case` ccase
	ON gcase.case_uuid = ccase.case_uuid
	INNER JOIN `" . $data_source . "`.`" . $data_source . "_case_injury` cci
	ON ccase.case_uuid = cci.case_uuid
	
	WHERE processed = 'N'
	#AND gcase.cpointer = '1755649'
	LIMIT 0, 1";
	
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
		$injury_uuid = $case->injury_uuid;
		
		echo "Processing -> " . $key. " == " . $cpointer . "<br /><br />\r\n";
		
		
		//first get settlement
		$sql = "SELECT * 
		FROM `" . $data_source . "`.`compsetl`
		WHERE INSTR(c,'" . $cpointer . "') > 0";
		
		$stmt = $db->prepare($sql);
		echo $sql . "<br /><br />\r\n\r\n";
		$stmt->execute();
		$settle = $stmt->fetchObject();
		
		$last_updated_date = date("Y-m-d H:i:s");
		//die(print_r($settle));
		if (is_object($settle)) {
			$legacy_info = addslashes(json_encode($settle));
			$sql = "
			INSERT INTO `" . $data_source . "`.`" . $data_source . "_settlement`
			(`settlement_uuid`, `date_submitted`, `date_approved`, 
			`amount_of_settlement`, `future_medical`, `amount_of_fee`, 
			`pd_percent`, `attorney`, `legacy_info`, `customer_id`)
			SELECT CONCAT('SE" . $cpointer . "_', recno), STR_TO_DATE(`submitted` ,  '%m/%d/%Y' ), STR_TO_DATE(`approved` ,  '%m/%d/%Y' ),
			pdamount, IF(futmed=1, 'Y', 'N'), (attyfees + rehabfees + depofees + socsecfees + referfees + otherfees), 
			`pd`, `settledby`, '" . $legacy_info . "', '" . $customer_id . "'
			FROM `" . $data_source . "`.compsetl
			WHERE INSTR(c,'" . $cpointer . "') > 0
			";
			echo $sql . "<br /><br />\r\n\r\n";
			//die();
			$stmt = $db->prepare($sql); 	
			$stmt->execute();
			
			$settlement_id = $db->lastInsertId();
			
			//get the uuid
			$sql = "SELECT settlement_uuid
			FROM `" . $data_source . "`.`" . $data_source . "_settlement`
			WHERE settlement_id = " . $settlement_id;
			$stmt = $db->prepare($sql);
			echo $sql . "<br /><br />\r\n\r\n";
			$stmt->execute();
			$settle = $stmt->fetchObject();
			
			$settlement_uuid = $settle->settlement_uuid;
			
			//attach to injury
			$injury_table_uuid = uniqid("IS", false);
			$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_injury_settlement` (`injury_settlement_uuid`, `injury_uuid`, `settlement_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $injury_table_uuid  ."', '" . $injury_uuid . "', '" . $settlement_uuid . "', 'main', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
			echo $sql . "<br /><br />\r\n\r\n";
			$stmt = $db->prepare($sql);  
			$stmt->execute();
		} else {
			$settlement_uuid = uniqid("NO", false);
		}
		
		//fees
		$sql = "
		INSERT INTO `" . $data_source . "`.`" . $data_source . "_fee`
		(`fee_uuid`, `fee_type`, `fee_date`, `fee_memo`, 
		`fee_billed`, `fee_paid`, `paid_fee`, `fee_by`, 
		`fee_check_number`, 
		`customer_id`) ";
		$sql .= "
		SELECT DISTINCT 
		CONCAT(feepointer, '_',  @curRow := @curRow + 1, '_', invoiceno) AS `fee_uuid`, `feetype`, 
		 STR_TO_DATE(`date`, '%m/%d/%Y' ) `date`, `desc`, 
		`billed`, `paid`, `paid`, `workcode`,
		CONCAT('INV " . $case->case_number . "-', invoiceno),
		'" . $customer_id . "'
		FROM `" . $data_source . "`.compfees fee
		JOIN    (SELECT @curRow := 0) r
		WHERE 1
		AND fee.feepointer = '" . $case->cpointer . "'
		ORDER BY fee.invoiceno";
		
		echo $sql . "\r\n\r\n<br><br>";  
		
		//continue;
		//die();
		$stmt = $db->prepare($sql); 
		
		$stmt->execute();
		
		//attach to case
		$settlement_fee_uuid = uniqid("KA", false);
		$attribute = "main";
		
		$sql = "INSERT INTO `" . $data_source . "`." . $data_source . "_settlement_fee (`settlement_fee_uuid`, `settlement_uuid`, `fee_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		SELECT '" . $settlement_fee_uuid . "', '" . $settlement_uuid . "', 
		CONCAT(feepointer, '_',  @curRow := @curRow + 1, '_', invoiceno) AS `fee_uuid`,
		'" . $attribute . "', '" . $last_updated_date . "', 'system', '" . $customer_id . "'
		FROM `" . $data_source . "`.compfees fee
		
		JOIN    (SELECT @curRow := 0) r

		WHERE 1
		AND fee.feepointer = '" . $case->cpointer . "'
		
		ORDER BY fee.invoiceno";
		echo $sql . "\r\n\r\n<br><br>";  

		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $row_start_time), 4);
		
		//echo "Time3 spent:" . $total_time . "<br /><br />";	
	
		$sql = "UPDATE `" . $data_source . "`.`badfees` 
		SET processed = 'Y'
		WHERE cpointer = '" . $cpointer . "'";
		echo $sql . "\r\n\r\n<br><br>";
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $row_start_time), 4);	
	}
	
	//die("stop");
	
	//completeds
	$sql = "SELECT COUNT(badfees_id) case_count
	FROM `" . $data_source . "`.`badfees` ggc
	WHERE 1";
	//echo $sql . "\r\n<br>";
	//die();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$cases = $stmt->fetchObject();
	
	$case_count = $cases->case_count;
	

	//completeds
	$sql = "SELECT COUNT(badfees_id) case_count
	FROM `" . $data_source . "`.`badfees` ggc
	WHERE 1
	AND `processed` = 'Y'";
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
		echo "<script language='javascript'>parent.runFees(" . $completed_count . "," . $case_count . ")</script>";
	}
	
	$db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}	
//, `notedesc`, , `notepoint`, `notelock`, `lockedby`, `xsoundex`, `locator`, `enteredby`, `mailpoint`, `lockloc`, `docpointer`, `doctype`

?>
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
parent.setFeedback("check import started");
</script>
<?php
$db = getConnection();
try {
	include("customer_lookup.php");
	
	$sql = "SELECT mc.case_id, mc.case_uuid, mc.cpointer
	FROM `" . $data_source . "`.`" . $data_source . "_case` mc 
	INNER JOIN `" . $data_source . "`.`costs`
	ON costs.`costpnt` =  mc.cpointer
	WHERE 1 
	AND mc.case_uuid NOT IN (SELECT DISTINCT case_uuid FROM `" . $data_source . "`.`" . $data_source . "_case_check`)
	AND `date` IS NOT NULL
	AND `date` NOT LIKE '%/010'
	AND `date` NOT LIKE '%5200'
	AND `date` != '06/31/2013'
	AND `date` NOT LIKE '  /  /'
	AND `date` != '/  /'
	AND `date` NOT LIKE '20/07/'
	AND INSTR(`date`, '/') > 0
	
	LIMIT 0, 1";
	//AND mc.cpointer = '2031108'
	
	$stmt = $db->prepare($sql);
	echo $sql . "<br /><br />\r\n\r\n";
	$stmt->execute();
	
	
	$cases = $stmt->fetchAll(PDO::FETCH_OBJ);
	$arrCaseUUID =  array();
	if (count($cases)==0) {
		die("done");
	}
	foreach($cases as $key=>$case){
		//die(print_r($case));
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$row_start_time = $time;
		
		$cpointer = $case->cpointer;
		echo "Processing -> " . $key. " == " . $cpointer . "<br /><br />\r\n\r\n";
		if (in_array($cpointer, $arrCaseUUID)) {
			//one time per pointer
			continue;
		} 
		$arrCaseUUID[] = $cpointer;
		
		$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_check` 
		(`check_counter`, `case_check_uuid`, `case_uuid`,  `check_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
		SELECT @curRow := @curRow + 1, '', '" . $case->case_uuid . "',
		CONCAT(@curRow, '_" . $cpointer . "') AS `check_uuid`,  
		'main', 
		STR_TO_DATE(REPLACE(`date`, '/  /', '/01/'), '%m/%d/%Y' ) check_date, 'system', 'N', " . $customer_id . "
		FROM 
		`" . $data_source . "`.costs
		JOIN    (SELECT @curRow := 0) r
		WHERE `costpnt` = '" . $cpointer . "'
		AND `date` IS NOT NULL
		AND `date` NOT LIKE '%/010'
		AND `date` != '/  /'
		AND `date` NOT LIKE '%5200'
		AND `date` != '06/31/2013'
		AND `date` NOT LIKE '  /  /'
		AND `date` NOT LIKE '20/07/'
		AND INSTR(`date`, '/') > 0
		ORDER BY `date` ASC";
		echo $sql . "<br /><br />\r\n\r\n";
		
		/*
		die();
		*/
		
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		//tritek balance is running total, 
		$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_check` 
		(`check_counter`, `check_uuid`, `check_number`, `check_date`, `check_type`, 
		`amount_due`, `payment`, `adjustment`, `balance`, 
		`transaction_date`, `memo`, `customer_id`)
		SELECT @curRow := @curRow + 1, CONCAT(@curRow, '_" . $cpointer . "') AS `check_uuid`, 
		IFNULL(checkno, '') `checkno`, 
		STR_TO_DATE(REPLACE(`date`, '/  /', '/01/'), '%m/%d/%Y' ) check_date,
		 'standard', 
		 `amount`, `payment`, `adjustment`, (`amount` + `adjustment` - `payment`) `balance`, 
		STR_TO_DATE(REPLACE(`date`, '/  /', '/01/'), '%m/%d/%Y' ) check_date, `descriptio`, '" . $customer_id . "'
		FROM `" . $data_source . "`.costs
		JOIN    (SELECT @curRow := 0) r
		WHERE `costpnt` = '" . $cpointer . "'
		AND `date` IS NOT NULL
		AND `date` NOT LIKE '%/010'
		AND `date` != '/  /'
		AND `date` NOT LIKE '%5200'
		AND `date` NOT LIKE '  /  /'
		AND `date` NOT LIKE '20/07/'
		AND `date` != '06/31/2013'
		AND INSTR(`date`, '/') > 0
		ORDER BY `date` ASC";
		
		echo $sql . "<br /><br />\r\n\r\n";
		//die();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $row_start_time), 4);
		echo " => Time spent:" . $total_time . "<br />
<br />
";
	}
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	//$success = array("success"=> array("text"=>"done", "duration"=>$total_time));
	//echo json_encode($success);
	//completeds
	$sql = "SELECT COUNT(`costpnt`) `case_count`
	FROM `" . $data_source . "`.`costs` gcase
	WHERE 1";
	echo $sql . "\r\n<br>";
	//die();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$cases = $stmt->fetchObject();
	
	$case_count = $cases->case_count;
	
	//completeds
	$sql = "SELECT COUNT(DISTINCT case_uuid) case_count
	FROM `" . $data_source . "`.`" . $data_source . "_case_check` ggc
	WHERE 1";
	echo $sql . "\r\n<br>";
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
	
	echo "Time spent:" . $total_time . "<br />
<br />
";

	$success = array("success"=> array("text"=>"done", "duration"=>$total_time, "completed_count"=>$completed_count, "case_count"=>$case_count));
	
	print_r($success);
	
	if (count($cases) > 0) {
		//die("script language='javascript'>parent.runMain(" . $completed_count . "," . $case_count . ")</script");
		echo "<script language='javascript'>parent.runCosts(" . $completed_count . "," . $case_count . ")</script>";
	}
	
	$db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
	die();
}	
?>
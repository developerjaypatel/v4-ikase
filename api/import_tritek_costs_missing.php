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
parent.setFeedback("check import started");
</script>
<?php
$db = getConnection();
/*
TRUNCATE leyva.leyva_check;
TRUNCATE leyva.leyva_case_check;
*/

try {
	include("customer_lookup.php");
	
	$sql = "SELECT mc.case_id, mc.case_uuid, mc.cpointer
	FROM `" . $data_source . "`.`" . $data_source . "_case` mc 
	INNER JOIN `" . $data_source . "`.missings mis
	ON mc.cpointer = mis.cpointer AND mis.costs_done = 'N'
	INNER JOIN `" . $data_source . "`.`costs`
	ON costs.`costpnt` =  mc.cpointer
	WHERE 1 
	AND `date` IS NOT NULL
	AND `date` NOT LIKE '%/010'
	AND `date` NOT LIKE '%5200'
	AND `date` NOT LIKE '  /  /'
	AND `date` != '/  /'
	AND `date` NOT LIKE '20/07/'
	ORDER BY mc.cpointer DESC
	LIMIT 0, 1";
	//
	$stmt = $db->prepare($sql);
	echo $sql . "<br /><br />\r\n\r\n";
	$stmt->execute();
	
	
	$cases = $stmt->fetchAll(PDO::FETCH_OBJ);
	$arrCaseUUID =  array();
	if (count($cases)==0) {
		die("done");
	}
	die(print_r($cases));
	foreach($cases as $key=>$case){
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
		AND `date` NOT LIKE '  /  /'
		AND `date` NOT LIKE '20/07/'
		ORDER BY `date` ASC";
		echo $sql . "<br /><br />\r\n\r\n";
		$stmt = DB::run($sql);
		
		$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_check` 
		(`check_counter`, `check_uuid`, `check_number`, `check_date`, `check_type`, `amount_due`, `payment`, `balance`, 
		`transaction_date`, `memo`, `customer_id`)
		SELECT @curRow := @curRow + 1, CONCAT(@curRow, '_" . $cpointer . "') AS `check_uuid`, 
		IFNULL(checkno, '') `checkno`, 
		STR_TO_DATE(REPLACE(`date`, '/  /', '/01/'), '%m/%d/%Y' ) check_date,
		 'standard', `amount`, `payment`, `balance`, 
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
		ORDER BY `date` ASC";
		
		echo $sql . "<br /><br />\r\n\r\n";
		$stmt = DB::run($sql);
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $row_start_time), 4);
		echo " => Time spent:" . $total_time . "<br />
<br />
";

	$sql = "UPDATE `" . $data_source . "`.`missings` 
	SET costs_done = 'Y'
	WHERE cpointer = '" . $cpointer . "'";
	echo $sql . "\r\n\r\n";
	$stmt = DB::run($sql);
	
	}
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	//$success = array("success"=> array("text"=>"done", "duration"=>$total_time));
	//echo json_encode($success);
	//completeds
	$sql = "SELECT COUNT(*) case_count
		FROM `" . $data_source . "`.`missings` gcase
		WHERE 1";
		echo $sql . "\r\n<br>";
		//die();
		$stmt = DB::run($sql);
		$cases = $stmt->fetchObject();
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $header_start_time), 4);
		echo " => QUERY completed in " . $total_time . "<br /><br />"; 
		
		$case_count = $cases->case_count;
		
		//completeds
		$sql = "SELECT COUNT(cpointer) case_count
		FROM `" . $data_source . "`.`missings` ggc
		WHERE costs_done = 'Y'";
		echo $sql . "\r\n<br>";
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
	
	echo "Time spent:" . $total_time . "<br />
<br />
";

	$success = array("success"=> array("text"=>"done", "duration"=>$total_time, "completed_count"=>$completed_count, "case_count"=>$case_count));
	
	//print_r($success);
	
	if (count($cases) > 0) {
		//die("script language='javascript'>parent.runMain(" . $completed_count . "," . $case_count . ")</script");
		echo "<script language='javascript'>parent.runCostsMissing(" . $completed_count . "," . $case_count . ")</script>";
	}
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
	die();
}	
?>

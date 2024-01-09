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
	
	$sql = "SELECT cli.`settled` `date_settled`, gc.case_uuid, gi.injury_uuid, dist1.* 
	FROM " . $data_source . ".dist1

	INNER JOIN " . $data_source . ".`client` cli
	ON dist1.distpnt = cli.cpointer
	
	INNER JOIN " . $data_source . "." . $data_source . "_case gc
	ON cli.cpointer = gc.cpointer
	
	INNER JOIN " . $data_source . "." . $data_source . "_case_injury gci
	ON gc.case_uuid = gci.case_uuid
	
	INNER JOIN " . $data_source . "." . $data_source . "_injury gi
	ON gci.injury_uuid = gi.injury_uuid
	
	LEFT OUTER JOIN " . $data_source . ".`" . $data_source . "_settlementsheet` gs
	ON cli.cpointer = gs.settlementsheet_uuid
	WHERE 1
	AND gs.settlementsheet_uuid IS NULL
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
		
		$cpointer = $case->distpnt;
		echo "Processing -> " . $key. " == " . $cpointer . "<br /><br />\r\n\r\n";
		if (in_array($cpointer, $arrCaseUUID)) {
			//one time per pointer
			continue;
		} 
		$arrCaseUUID[] = $cpointer;
		
		$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_settlementsheet`
(`settlementsheet_uuid`, `date_settled`, `data`, `customer_id`)
		VALUES( '" . $case->distpnt . "', '" . $case->date_settled . "', '" . addslashes(json_encode($case)) . "', '" . $customer_id . "')";
		echo $sql . "<br /><br />\r\n\r\n";
		//die();
		
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		//tritek balance is running total, 
		$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_injury_settlement` 
		(`injury_settlement_uuid`, `settlement_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $case->distpnt . "', '" . $case->distpnt . "',  '" . $case->injury_uuid . "', 'main', '" . date("Y-m-d H:i:s") . "', 'system', '" . $customer_id . "')";
		
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
	$sql = "SELECT COUNT(`distpnt`) `case_count`
	FROM `" . $data_source . "`.`dist1` gcase
	WHERE 1";
	echo $sql . "\r\n<br>";
	//die();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$cases = $stmt->fetchObject();
	
	$case_count = $cases->case_count;
	
	//completeds
	$sql = "SELECT COUNT(DISTINCT injury_uuid) case_count
	FROM `" . $data_source . "`.`" . $data_source . "_injury_settlement` ggc
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
		echo "<script language='javascript'>parent.runSettlement(" . $completed_count . "," . $case_count . ")</script>";
	}
	
	$db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
	echo "<br />" . $sql;
	die();
}	
?>
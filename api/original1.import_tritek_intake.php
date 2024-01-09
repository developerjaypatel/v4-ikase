<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include("manage_session.php");
set_time_limit(3000);

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;

include("connection.php");
?>
<script language="javascript">
parent.setFeedback("claim import started");
</script>
<?php
try {
	$db = getConnection();
	
	include("customer_lookup.php");
	
	
	$sql = "SELECT DISTINCT mc.case_id, mc.case_uuid, mc.cpointer
	FROM `" . $data_source . "`.`" . $data_source . "_case` mc
	INNER JOIN `" . $data_source . "`.`intake`
	ON mc.cpointer = `intake`.`cpointer`
	LEFT OUTER JOIN `" . $data_source . "`.`" . $data_source . "_claim` clm
	ON mc.case_uuid = clm.case_uuid
	WHERE 1
	AND clm.case_uuid IS NULL
	LIMIT 0, 1";

	echo $sql . "<br />";
	//die();
	
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$cases = $stmt->fetchAll(PDO::FETCH_OBJ);
	$rand = rand(100,200);
	
	//die(print_r($cases));
	foreach($cases as $key=>$case){
		$case_uuid = $case->case_uuid;
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$row_start_time = $time;
		
		$processing = "<br>Processing -> " . $key. " == " . $case->cpointer . "<br /><br />\r\n";
		echo $processing . "  ";
		
		$sql = "SELECT ink.`body` claim_disability, ink.occup occupation, STR_TO_DATE(  `ink`.`intakedate` ,  '%m/%d/%Y' ) intake_date,
            ink.lastworked last_date_worked, IF (ink.fivetenyes = 1, 'on', '') five_ten,
            ink.typeofclm claim_type, ink.typeother claim_type_other,
            IF (ink.priorapyes = 1, 'on', '') prior_app, ink.priorapyr prior_year, ink.priorapres claim_outcome,
            ink.stage claim_stage, ink.stageother claim_stage_other, ink.benefits claim_benefits, ink.benother claim_benefits_other,
            ink.benamount claim_benefits_amount, ink.bendate claim_benefits_date, ink.denialdate claim_benefits_denial,
            IF (ink.workrelyes = 1, 'on', '') work_related, IF (ink.compyes = 1, 'on', '') wc_claim, 
            IF (ink.wcsetlyes = 1, 'on', '') wc_settled, ink.wcamount claim_benefits_amount,
            ink.doctype claim_specialties, ink.docvisits claim_frequency, ink.surgerycnt claim_surgeries_number, ink.surgerydat claim_surgeries_when,
            ink.medication claim_medications, ink.cmemo claim_comments, ink.pdpct claim_pdpct
			FROM `" . $data_source . "`.`intake` `ink`
			INNER JOIN `" . $data_source . "`.`client` cli
			ON `ink`.cpointer = cli.cpointer
			WHERE 1
			AND cli.cpointer = '" . $case->cpointer . "'
			";
			
		$stmt = $db->prepare($sql);
		echo $sql . "\r\n\r\n<br><br>";
		//die();
		$stmt->execute();
		$ink = $stmt->fetchObject();
		$claim_info = json_encode($ink);
		
		$table_uuid = uniqid("NG", false);
		
		$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_claim` 
		(`case_uuid`,`claim_info`, `customer_id`)
		VALUES ('" . $case_uuid . "', '" . addslashes($claim_info) . "', '" . $customer_id . "')";
		$stmt = $db->prepare($sql); 
		echo $sql . "\r\n\r\n<br><br>"; 
		//die();
		$stmt->execute();
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $row_start_time), 4);
		echo " => Time spent:" . $total_time . "
		
		<br /><br />";
		
		//die();
	}
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	//$success = array("success"=> array("text"=>"done", "duration"=>$total_time));
	//echo json_encode($success);
	
	//$success = array("success"=> array("text"=>"done", "duration"=>$total_time));
	//completeds
	$sql = "SELECT COUNT(cli.cpointer) case_count
	FROM `" . $data_source . "`.`client` cli
	INNER JOIN `" . $data_source . "`.intake ink
    ON cli.cpointer = ink.cpointer
	WHERE 1";
	//echo $sql . "\r\n<br>";
	//die();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$cases = $stmt->fetchObject();
	
	$case_count = $cases->case_count;
	
	//completeds
	$sql = "SELECT COUNT(case_uuid) case_count
	FROM `" . $data_source . "`.`" . $data_source . "_claim` ggc
	WHERE 1";
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
	
	$success = array("success"=> array("text"=>"done", "duration"=>$total_time, "completed_count"=>$completed_count, "case_count"=>$case_count));
	
	if (count($cases) > 0) {
		//die("script language='javascript'>parent.runMain(" . $completed_count . "," . $case_count . ")</script");
		echo "<script language='javascript'>parent.runIntake(" . $completed_count . "," . $case_count . ")</script>";
	}
		
	$db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}

//include("cls_logging.php");
?>
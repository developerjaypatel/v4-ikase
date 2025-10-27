<?php
require_once('../shared/legacy_session.php');
set_time_limit(3000);

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;

include("connection.php");
$customer_id = passed_var("customer_id", "get");
if (!is_numeric($customer_id)) {
	die();
}

try {
	$db = getConnection();
	
	include("customer_lookup.php");
	
	$sql = "SELECT DISTINCT mc.case_id, mc.case_uuid, mc.cpointer
	FROM `ikase_" . $data_source . "`.`cse_case` mc
	INNER JOIN `" . $GLOBALS['GEN_DB_NAME'] . "`.`caseact`
	ON mc.cpointer = `caseact`.`CASENO`
	WHERE 1
    AND caseact.EVENT_FLAG=1
	AND mc.case_uuid NOT IN (SELECT DISTINCT case_uuid FROM `ikase_" . $data_source . "`.`cse_case_notes`)
	LIMIT 0, 1";
	echo $sql . "\r\n\r\n";
	//die;
	$cases = DB::select($sql);
	/*
	$cases = new stdClass;
	$case = new stdClass;
	$case->cpointer = 12455;
	$case->case_uuid = "CASEUUID";
	$cases->case = $case;
	*/
	// die(print_r($cases));
	foreach($cases as $key=>$case){
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$row_start_time = $time;
		//die(print_r($case));
		
        echo "Processing -> " . $key. " == " . $case->cpointer . "<br>";
        $last_updated_date = date("Y-m-d H:i:s");

        $select_query = "SELECT caseact.EVENT as `note`, caseact.DATE, caseact.TITLE as `title`,caseact.INITIALS0, caseact.CATEGORY,
		#CONCAT(`CASENO`, '_', `ACTNO`, '_', @curRow := @curRow + 1) AS `notes_uuid`, 
		CONCAT(`CASENO`, '_', `ACTNO`) AS `notes_uuid`
		FROM `" . $GLOBALS['GEN_DB_NAME'] . "`.caseact
        WHERE `CASENO` = '" . $case->cpointer . "' AND caseact.EVENT_FLAG=1";
        $select_res = DB::select($select_query);

        foreach($select_res as $key1=>$res){
            $attribute = '';
            if($res->CATEGORY == '0' || $res->CATEGORY == '24641' || $res->CATEGORY == '24642' || $res->CATEGORY == '24645' || $res->CATEGORY == '24646' || $res->CATEGORY == '73921') {
                $attribute = 'quick';
            } 
            // elseif($res->CATEGORY == '1') {
            //     $attribute = 'standard';
            // } 
            elseif($res->CATEGORY == '24645') {
                $attribute = 'general';
            }
            if($attribute != '') {
                $sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_notes` (`case_notes_uuid`, `case_uuid`,  `notes_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`) VALUES ('".$case->case_uuid."', '".$case->case_uuid."', '".$res->notes_uuid."', '".$attribute."', '".$last_updated_date."', 'system', 'N', ".$customer_id.")";
                echo $sql.'<br><br>';
                $stmt = DB::run($sql);
                
                $sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_notes` (`notes_uuid`, `type`, `subject`, `note`, `title`, `attachments`, `entered_by`, `status`, `dateandtime`, `callback_date`, `verified`, `deleted`, `customer_id`) VALUES ('".$res->notes_uuid."', '".$attribute."', '', '".addslashes($res->note)."', '".addslashes($res->title)."', '', '".$res->INITIALS0."', 'STANDARD', '".$res->DATE."', '0000-00-00 00:00:00', 'N', 'N', ".$customer_id.")";   
                echo $sql.'<br><br>'; 
                $stmt = DB::run($sql);
            } else {
				$tmp_sql = "SELECT case_notes_id FROM `ikase_" . $data_source . "`.`cse_case_notes` WHERE case_uuid LIKE '".$case->case_uuid."'";
				$tmp_res = DB::select($tmp_sql);
				if(count($tmp_res) == 0) {
					$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_notes` (`case_notes_uuid`, `case_uuid`,  `notes_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`) VALUES ('".$case->case_uuid."', '".$case->case_uuid."', '".$res->notes_uuid."', '".$attribute."', '".$last_updated_date."', 'system', 'N', ".$customer_id.")";
					echo $sql.'<br><br>';
					$stmt = DB::run($sql);
				}
            }
        }
		/*
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $row_start_time), 4);
		echo "Time spent:" . $total_time . "\r\n\r\n";
		*/
	}
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	//$success = array("success"=> array("text"=>"done", "duration"=>$total_time));
	//echo json_encode($success);
	
	//completeds
	$sql = "SELECT COUNT(DISTINCT `CASENO`) `case_count`
	FROM `" . $GLOBALS['GEN_DB_NAME'] . "`.`caseact` gcase
	WHERE 1";
	echo $sql . "\r\n<br>";
	//die();
	$stmt = DB::run($sql);
	$cases = $stmt->fetchObject();
	
	$case_count = $cases->case_count;
	
	//completeds
	$sql = "SELECT COUNT(DISTINCT case_uuid) case_count
	FROM `ikase_" . $data_source . "`.`cse_case_notes` ggc
	WHERE 1";
	echo $sql . "\r\n<br>";
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
	
	if (count(json_decode(json_encode($cases), true)) > 0) {
		//die("script language='javascript'>parent.runMain(" . $completed_count . "," . $case_count . ")</script");
		echo "<script language='javascript'>parent.runNotes(" . $completed_count . "," . $case_count . ")</script>";
	}
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
	?>
    <script language="javascript">
parent.setFeedback("notes import error");
</script>
    <?php
	die();
}
?>

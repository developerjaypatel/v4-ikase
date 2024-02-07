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
parent.setFeedback("corporation import started");
</script>
<?php
$db = getConnection();
try {
	include("customer_lookup.php");
	
	$sql = "SELECT mc.case_id, mc.case_uuid, mc.cpointer, dfnt.dpointer
	FROM `" . $data_source . "`.`" . $data_source . "_case` mc 
    INNER JOIN `" . $data_source . "`.`client` cli
    ON mc.cpointer = cli.cpointer
	INNER JOIN `" . $data_source . "`.`dfnt`
	ON dfnt.`dpointer` = cli.dpnt
	WHERE 1 
	AND (dfnt.dfntlast != '' OR dfnt.dfntcompan != '')
	AND mc.case_uuid NOT IN (
		SELECT DISTINCT case_uuid 
		FROM `" . $data_source . "`.`" . $data_source . "_case_corporation` 
        WHERE attribute = 'defendant'
	)
	LIMIT 0, 500";
	//
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
		$dpointer = $case->dpointer;
		$case_uuid = $case->case_uuid;
		
		echo "Processing -> " . $key. " == " . $cpointer . "<br /><br />\r\n\r\n";
				
		//defendants
		$sql = "SELECT dfnt.*
		FROM `" . $data_source . "`.`dfnt` dfnt
		WHERE dfnt.dpointer = " . $case->dpointer;
		$stmt = $db->prepare($sql);
		//echo $sql . "\r\n\r\n<br><br>";
		//die();
		$stmt->execute();
		$defendants = $stmt->fetchAll(PDO::FETCH_OBJ);
		//die(print_r($defendants));
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $header_start_time), 4);
		echo " => QUERY completed in " . $total_time . "<br /><br />";
	
		foreach ($defendants as $defendant) {
			$last_updated_date = date("Y-m-d H:i:s");
			$table_uuid = uniqid("DR", false);
			//$parent_table_uuid = uniqid("PD", false);
			$parent_table_uuid = $defendant->dfntpnt;
			if ($defendant->dfntcompan!="" || $defendant->dfntlast!=""){
				$arrSet = array();
				$arrSet[] = addslashes($defendant->dfntlast);
				$arrSet[] = addslashes($defendant->dfntfirst);
				$full_name = $defendant->dfntfirst . " ";
				if ($defendant->dfntmid!="") {
					$full_name .= $defendant->dfntmid . " ";
				}
				$full_name .= addslashes($defendant->dfntlast);
				$arrSet[] = addslashes($full_name);
				$arrSet[] = addslashes($defendant->dfntcompan);
				$arrSet[] = "defendant";
				$full_address_defendant = $defendant->dfntadd1;
				if ($defendant->dfntadd2!="") {
					$full_address_defendant .= ", " . $defendant->dfntadd2;
				}
				$full_address_defendant .= ", " . $defendant->dfntcity;
				$full_address_defendant .= ", " . $defendant->dfntst;
				$full_address_defendant .= " " . $defendant->dfntzip;
				
				$arrSet[] = addslashes($full_address_defendant);
				$arrSet[] = addslashes($defendant->dfntadd1);
				$arrSet[] = addslashes($defendant->dfntcity);
				$arrSet[] = $defendant->dfntst;
				$arrSet[] = $defendant->dfntzip;
				$arrSet[] = addslashes($defendant->dfntadd2);
				
				if ($defendant->dfntext=="") {
					$arrSet[] = addslashes($defendant->dfnttel);
				} else {
					$arrSet[] = addslashes($defendant->dfnttel . " " . $defendant->dfntext);
				}
				$arrSet[] = addslashes($defendant->dfntfax);
				$dob = $defendant->dfntdob;
				if ($dob!="") {
					$dob = date("Y-m-d", strtotime($dob));
				}
				$arrSet[] = addslashes($dob);
				$defendant->dfntsalut = str_replace("&#0176;", "", cleanWord($defendant->dfntsalut));
				$arrSet[] = addslashes($defendant->dfntsalut);
				
				//die(print_r($arrSet));
				//look up in case already in
				$sql_check = "SELECT corporation_uuid
				FROM `" . $data_source . "`.`" . $data_source . "_corporation`
				WHERE customer_id = " . $customer_id . "
				AND corporation_uuid = parent_corporation_uuid
				AND type = 'defendant'
				AND deleted = 'N'
				AND corporation_uuid = '" . $defendant->dfntpnt . "'";
				
				//echo "PCheck<br />" . $sql_check . "\r\n\r\n<br><br>";
				
				$stmt = $db->prepare($sql_check);
				$stmt->execute();
				$partie = $stmt->fetchObject();
				
				$time = microtime();
				$time = explode(' ', $time);
				$time = $time[1] + $time[0];
				$finish_time = $time;
				$total_time = round(($finish_time - $header_start_time), 4);
				echo " => QUERY completed in " . $total_time . "<br /><br />";
	
				if (is_object($partie)) {
					//die(print_r($partie));
					$parent_table_uuid = $partie->corporation_uuid;
				}
				if (!is_object($partie)) {
					if (is_numeric($parent_table_uuid)) {
						$parent_table_uuid = "PT" . rand(0, 5000) . $parent_table_uuid;
					}
					//insert the parent record first
					$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation` (`corporation_uuid`, `customer_id`, `last_name`, `first_name` , `full_name`, `company_name`, `type`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `fax`,  `dob`, `salutation`, `last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
					VALUES('" . $parent_table_uuid . "', '" . $customer_id . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N', '" . $parent_table_uuid . "','')";
					
					//echo "<br />--" . $sql_check . "<br />";
					//echo $sql . "<br /><br />";		
					//die();
					$stmt = $db->prepare($sql); 
					$stmt->execute();
				}
				
				$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation` (`corporation_uuid`, `customer_id`, `last_name`, `first_name` , `full_name`, `company_name`, `type`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `fax`, `dob`, `salutation`, `last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
				VALUES('" . $table_uuid . "', '" . $customer_id . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N', '" . $parent_table_uuid . "', '')";
				
				//echo $sql . "\r\n\r\n<br><br>"; 
				//die();		
				$stmt = $db->prepare($sql);  
				$stmt->execute();
				
				$case_table_uuid = uniqid("OA", false);
				//now we have to attach the doctor to the case 
				$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_corporation` (`case_corporation_uuid`, `case_uuid`, `corporation_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', 'defendant', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
						
				$stmt = $db->prepare($sql); 
				//echo $sql . "\r\n\r\n<br><br>";  
				$stmt->execute();
				
				if ($defendant->dfntlic!="") {
					$adhoc_uuid = uniqid("DF", false);
					$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation_adhoc`
						(`adhoc_uuid`, `case_uuid`, `corporation_uuid`, `adhoc`, `adhoc_value`, `customer_id`)
						VALUES ('" . $adhoc_uuid . "','" . $case_uuid . "','" . $table_uuid . "','dl_number', '" . substr(addslashes($defendant->dfntlic), 0, 254) . "', '" . $customer_id . "')";
					$stmt = $db->prepare($sql); 
					//echo $sql . "\r\n\r\n<br><br>";  
					$stmt->execute();
				}
				
				if ($defendant->dfntmemo!="") {
					$adhoc_uuid = uniqid("DG", false);
					$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation_adhoc`
						(`adhoc_uuid`, `case_uuid`, `corporation_uuid`, `adhoc`, `adhoc_value`, `customer_id`)
						VALUES ('" . $adhoc_uuid . "','" . $case_uuid . "','" . $table_uuid . "','memo', '" . substr(addslashes($defendant->dfntmemo), 0, 254) . "', '" . $customer_id . "')";
					$stmt = $db->prepare($sql); 
					//echo $sql . "\r\n\r\n<br><br>";  
					$stmt->execute();
				}
				
				if ($defendant->dfntcont!="") {
					$adhoc_uuid = uniqid("DG", false);
					$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation_adhoc`
						(`adhoc_uuid`, `case_uuid`, `corporation_uuid`, `adhoc`, `adhoc_value`, `customer_id`)
						VALUES ('" . $adhoc_uuid . "','" . $case_uuid . "','" . $table_uuid . "','contact', '" . substr(addslashes($defendant->dfntcont), 0, 254) . "', '" . $customer_id . "')";
					$stmt = $db->prepare($sql); 
					//echo $sql . "\r\n\r\n<br><br>";  
					$stmt->execute();
				}
				
				if ($defendant->dfntcontte!="") {
					$adhoc_uuid = uniqid("DG", false);
					$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation_adhoc`
						(`adhoc_uuid`, `case_uuid`, `corporation_uuid`, `adhoc`, `adhoc_value`, `customer_id`)
						VALUES ('" . $adhoc_uuid . "','" . $case_uuid . "','" . $table_uuid . "','contact_phone', '" . substr(addslashes($defendant->dfntcontte), 0, 254) . "', '" . $customer_id . "')";
					$stmt = $db->prepare($sql); 
					//echo $sql . "\r\n\r\n<br><br>";  
					$stmt->execute();
				}
				
				$govt_claim = $defendant->govtclaim;
				if ($govt_claim==0) {
					$govt_claim = "N";
				} else {
					$govt_claim = "Y";
				}
				
				if ($govt_claim == "Y") {
					$adhoc_uuid = uniqid("DH", false);
					$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation_adhoc`
						(`adhoc_uuid`, `case_uuid`, `corporation_uuid`, `adhoc`, `adhoc_value`, `customer_id`)
						VALUES ('" . $adhoc_uuid . "','" . $case_uuid . "','" . $table_uuid . "','government_claim', '" . $govt_claim . "', '" . $customer_id . "')";
					$stmt = $db->prepare($sql); 
					//echo $sql . "\r\n\r\n<br><br>";  
					$stmt->execute();
				}
			
			}
		}
	}
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	//$success = array("success"=> array("text"=>"done", "duration"=>$total_time));
	//echo json_encode($success);
	//completeds
	/*
	$sql = "SELECT COUNT(`dfntpnt`) `case_count`
	FROM `" . $data_source . "`.`dfnt` gcase
	WHERE 1
	AND (gcase.dfntlast != '' OR gcase.dfntcompan != '')";
	echo $sql . "\r\n<br>";
	//die();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$cases = $stmt->fetchObject();
	
	$case_count = $cases->case_count;
	*/
	$case_count = 1810 ;
	//completeds
	$sql = "SELECT COUNT(DISTINCT case_uuid) case_count
	FROM `" . $data_source . "`.`" . $data_source . "_case_corporation` ggc
	WHERE 1
	AND attribute = 'defendant'";
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
	
	//print_r($success);
	
	if (count($cases) > 0) {
		//die("script language='javascript'>parent.runMain(" . $completed_count . "," . $case_count . ")</script");
		echo "<script language='javascript'>parent.runDefendants(" . $completed_count . "," . $case_count . ")</script>";
	}
	
	$db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
	die();
}	
?>
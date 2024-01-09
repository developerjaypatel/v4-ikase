<?php
include("manage_session.php");
set_time_limit(3000);

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;

include("connection.php");

function getNickConnection() {
	//$dbhost="54.149.211.191";
	$dbhost="ikase.org";
	$dbuser="root";
	$dbpass="admin527#";
	$dbname="ikase";
	
	$dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);            
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbh;
}

//WHERE cli.fileno = 1061
//die($sql);
try {
	$db = getNickConnection();
	
	include("customer_lookup.php");
	
	$sql = "SELECT gcase.* 
	FROM `" . $data_source . "`.`badexams` gcase
	WHERE processed = 'N'
	#AND cpointer = '1020550'
	#ORDER BY badexams_id ASC
	LIMIT 0, 1";

	$stmt = $db->prepare($sql);
	$stmt->execute();
	$cases = $stmt->fetchAll(PDO::FETCH_OBJ);
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
		//echo " => QUERY completed in " . $total_time . "<br /><br />";
	//die(print_r($cases));
	$found = count($cases);
	
	foreach($cases as $case_key=>$case){
		$case_uuid = $case->case_uuid;
		echo "Processing " . $case->cpointer . "\r\n<br />";
		
		$sql_exam = "SELECT DISTINCT `medicals`.mpointer, medicals.medpnt, clin.clinicname,
		`medsum` . provider, 
		`medsum` . specialty, STR_TO_DATE(`medsum`.`report`,  '%m/%d/%Y' ) examdate,
		`medsum` . exam, `medsum` . `status`, `medsum` . `reqby`, 
		`medsum` . `comments`, `medsum` . `examtype`,
		`medsum` . `fandsdate`
		FROM `" . $data_source . "`.`medicals`
		LEFT OUTER JOIN `" . $data_source . "`.`medsum` 
		ON `medicals`.mpointer = `medsum`.medsumpnt
		INNER JOIN `" . $data_source . "`.`clinics` clin
		ON `medicals`.medpnt = clin.clinicpnt
		WHERE `medicals`.mpointer = " . $case->cpointer . "
		#AND `clin`.clinicname = `medsum`.`provider`
		ORDER BY `medsum`.`report` ASC";
		
		$stmt = $db->prepare($sql_exam);
		//echo $sql_exam . "\r\n\r\n<br><br>";  
		//die();
		$stmt = $db->query($sql_exam);
		$exams = $stmt->fetchAll(PDO::FETCH_OBJ);
		//die(print_r($exams));			
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $header_start_time), 4);
		//echo " => QUERY completed in " . $total_time . "<br /><br />";
		
		$last_updated_date = date("Y-m-d H:i:s");
		$arrMedPnt = array();
		foreach($exams as $exam) {
			$arrMedPnt[] = $exam->medpnt;
			
			//find the corporation_uuid
			$sql = "SELECT gc.corporation_uuid 
			FROM goldberg2.goldberg2_corporation gc
			INNER JOIN goldberg2.goldberg2_case_corporation gcc
			ON gc.corporation_uuid = gcc.corporation_uuid
			WHERE parent_corporation_uuid = '" . $exam->medpnt . "'
			AND gcc.case_uuid = '" . $case_uuid . "'";
			
			$stmt = $db->prepare($sql);
			$stmt = $db->query($sql);
			$corp = $stmt->fetchObject();
			
			if ($corp->corporation_uuid=="") {
				//echo $sql . "\r\n";
				//print_r($exam);
				//die("no corp");
				$sql = "SELECT gc.corporation_uuid 
				FROM goldberg2.goldberg2_corporation gc
				INNER JOIN goldberg2.goldberg2_case_corporation gcc
				ON gc.corporation_uuid = gcc.corporation_uuid
				WHERE company_name = '" . addslashes($exam->provider) . "'
				AND gc.corporation_uuid != gc.parent_corporation_uuid
				AND gcc.case_uuid = '" . $case_uuid . "'";
				$stmt = $db->prepare($sql);
				$stmt = $db->query($sql);
				$corp = $stmt->fetchObject();
				
				if ($corp->corporation_uuid=="") {
					//echo $sql . "\r\n";
					//print_r($exam);
					//die("no corp");
					$parent_corporation_uuid = $exam->medpnt;
					$table_uuid = uniqid("DR", false);
					
					//doctor
					$sql_medical = "SELECT med.medpnt, `clinicname` `medname`, IFNULL(`med`.`drname`, '') `drname`, 
					med.medtel, 
					med.medext, med.medfax, med.medsalut, med.medmemo, clin.clinicadd1, clin.clinicadd2, 
					clin.cliniccity, clinicst, cliniczip, clinictel, clinicext, clinicfax, clinicemai clinicemail
					FROM `" . $data_source . "`.`medicals` med
					INNER JOIN `" . $data_source . "`.`clinics` `clin` 
					ON `med`.medpnt = clin.clinicpnt
					WHERE med.medpnt = '" . $parent_corporation_uuid . "'";
					$stmt = $db->prepare($sql_medical);
					$stmt->execute();
					$medical = $stmt->fetchObject();
					
					//die(print_r($medical));
					$arrSet = array();
					$arrSet[] = addslashes($medical->drname);
					$arrSet[] = addslashes($medical->medname);
					$arrSet[] = "medical_provider";
					$full_address_medical = $medical->clinicadd1;
					if ($medical->clinicadd2!="") {
						$full_address_medical .= ", " . $medical->clinicadd2;
					}
					$full_address_medical .= ", " . $medical->cliniccity;
					$full_address_medical .= ", " . $medical->clinicst;
					$full_address_medical .= " " . $medical->cliniczip;
					
					$arrSet[] = addslashes($full_address_medical);
					$arrSet[] = addslashes($medical->clinicadd1);
					$arrSet[] = addslashes($medical->cliniccity);
					$arrSet[] = $medical->clinicst;
					$arrSet[] = $medical->cliniczip;
					$arrSet[] = addslashes($medical->clinicadd2);
					
					if ($medical->clinicext=="") {
						$arrSet[] = addslashes($medical->clinictel);
					} else {
						$arrSet[] = addslashes($medical->clinictel . " " . $medical->clinicext);
					}
					$medical->clinicfax = str_replace("&#0176;", "", cleanWord($medical->clinicfax));
					$arrSet[] = $medical->clinicfax;
					$arrSet[] = addslashes($medical->clinicemail);
					
					if ($medical->medext=="") {
						$arrSet[] = addslashes($medical->medtel);
					} else {
						$arrSet[] = addslashes($medical->medtel . " " . $medical->medext);
					}
					//$arrSet[] = addslashes($medical->medfax);
					$medical->medfax = str_replace("&#0176;", "", cleanWord($medical->medfax));
					$arrSet[] = $medical->medfax;
					$arrSet[] = addslashes($medical->medemail);
					
					$arrSet[] = addslashes($medical->medsalut);
					
					//look up in case already in
					$sql_check = "SELECT corporation_uuid
					FROM `" . $data_source . "`.`" . $data_source . "_corporation`
					WHERE customer_id = " . $customer_id . "
					AND corporation_uuid = parent_corporation_uuid
					AND type = 'medical_provider'
					AND deleted = 'N'
					AND corporation_uuid = '" . $parent_corporation_uuid . "'";
					
					echo "PCheck<br />" . $sql_check . "\r\n\r\n<br><br>";
					
					$stmt = $db->prepare($sql_check);
					$stmt->execute();
					$partie = $stmt->fetchObject();
					
					if (!is_object($partie)) {
						//insert the parent record first
						$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation` (`corporation_uuid`, `customer_id`, `full_name`, `company_name`, `type`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `fax`, `email`, `employee_phone`, `employee_fax`, `employee_email`, `salutation`, `last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
						VALUES('" . $parent_corporation_uuid . "', '" . $customer_id . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N', '" . $parent_corporation_uuid . "','')";
						
						//echo "<br />--" . $sql_check . "<br />";
						echo $sql . "<br /><br />";	
						//die();	
						$stmt = $db->prepare($sql); 
						
						$stmt->execute();
					}
					
					$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation` (`corporation_uuid`, `customer_id`, `full_name`, `company_name`, `type`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `fax`, `email`, `employee_phone`, `employee_fax`, `employee_email`, `salutation`, `last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
					VALUES('" . $table_uuid . "', '" . $customer_id . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N', '" . $parent_corporation_uuid . "', '')";
							
					$stmt = $db->prepare($sql);  
					echo $sql . "\r\n\r\n<br><br>"; 
					//die();
					$stmt->execute();
					
					$case_table_uuid = uniqid("OA", false);
					$attribute_1 = "main";
					//now we have to attach the doctor to the case 
					$sql_medical = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_corporation` (`case_corporation_uuid`, `case_uuid`, `corporation_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
					VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', 'medical_provider', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
					
					$corp->corporation_uuid = $table_uuid;
				}
			}
		
			$medical_table_uuid = uniqid("MS", false);
			$exam_uuid = uniqid("EX", false);
			$attribute_1 = "main";

			//add exam itself
			$examdate = $exam->examdate;
			if ($examdate=="" || $examdate=='  /  /') {
				$examdate = "0000-00-00 00:00:00";
			}
			$fandsdate = $exam->fandsdate;
			if ($fandsdate=="") {
				$fandsdate = "0000-00-00 00:00:00";
			} else {
				$fandsdate = date("Y-m-d");
				if (date("Y", strtotime($fandsdate)) < 1970) {
					$fandsdate = "0000-00-00";
				}
			}
			$pands = 'N';
			if ($exam->pands==1) {
				//rare
				$pands = 'Y';
			}
			/*
			$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_exam`
(`exam_uuid`, `exam_dateandtime`, `exam_status`, `exam_type`, `specialty`, `requestor`, `comments`, `permanent_stationary`, `fs_date`, `customer_id`)
VALUES( '" . $exam_uuid . "', '" . $examdate . "', '" . addslashes($exam->status) . "', '" . addslashes($exam->examtype) . "', '" . addslashes($exam->specialty) . "', '" . addslashes($exam->reqby) . "', '" . addslashes($exam->comments) . "', '" . $pands . "', '" . $fandsdate . "', " . $customer_id . ");";
			$stmt = $db->prepare($sql); 
			//echo $sql . "\r\n\r\n<br><br>";  
			//die();
			$stmt->execute();
						
			$time = microtime();
			$time = explode(' ', $time);
			$time = $time[1] + $time[0];
			$finish_time = $time;
			$total_time = round(($finish_time - $header_start_time), 4);
			//echo " => first insert completed in " . $total_time . "<br /><br />";
						
			$last_updated_date = date("Y-m-d H:i:s");
			//now we have to attach the doctor to the case 
			$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation_exam` (`corporation_exam_uuid`, `corporation_uuid`, `exam_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $medical_table_uuid  ."', '" . $corp->corporation_uuid . "', '" . $exam_uuid . "', 'main', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
					
			$stmt = $db->prepare($sql); 
			//echo $sql . "\r\n\r\n<br><br>";  
			$stmt->execute();
			
			*/
		}
		$arrMedPnt = array_unique($arrMedPnt);
		//die(print_r($arrMedPnt));
		
		foreach($arrMedPnt as $medpnt) {	
			//medical billing
			$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_medicalbilling`
	(`medicalbilling_uuid`, `corporation_uuid`, `user_uuid`, `billed`, `paid`, `adjusted`, `balance`, `customer_id`) ";
			$sql .= "
			SELECT DISTINCT 
			CONCAT(med.medpnt, '_', @curRow := @curRow + 1) AS `medicalbilling_uuid`,
			'" . $corp->corporation_uuid . "', 'system', medbill.amount, medbill.payment, medbill.adjustment,  
			(medbill.amount - medbill.payment - medbill.adjustment) balance, '" . $customer_id . "'
			FROM `" . $data_source . "`.medicals med
			
			INNER JOIN `" . $data_source . "`.`clinics` `clin` 
			ON `med`.medpnt = clin.clinicpnt
			
			INNER JOIN `" . $data_source . "`.medbill
			ON med.medbpnt = medbill.medbpnt
			
			JOIN    (SELECT @curRow := 0) r
	
			WHERE 1
			AND med.mpointer = '" . $case->cpointer . "'
			AND `med`.medpnt = '" . $medpnt . "'
			ORDER BY medbill.medbpnt, `date`";
			
			//die($sql); 
			//#AND med.mpointer = '2031108'
			$stmt = $db->prepare($sql); 
			echo $sql . "\r\n\r\n<br><br>";  
			$stmt->execute();
			
			//attach to case
			
			$case_medicalbilling_uuid = uniqid("KA", false);
			$attribute = "main";
			
			$sql_billing = "INSERT INTO `" . $data_source . "`." . $data_source . "_case_medicalbilling (`case_medicalbilling_uuid`, `case_uuid`, `medicalbilling_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			SELECT '" . $case_medicalbilling_uuid . "', '" . $case_uuid . "', 
			CONCAT(med.medpnt, '_', @curRow := @curRow + 1) AS `medicalbilling_uuid`,
			'" . $attribute . "', '" . $last_updated_date . "', 'system', '" . $customer_id . "'
			FROM `" . $data_source . "`.medicals med
			
			INNER JOIN `" . $data_source . "`.`clinics` `clin` 
			ON `med`.medpnt = clin.clinicpnt
			
			INNER JOIN `" . $data_source . "`.medbill
			ON med.medbpnt = medbill.medbpnt
			
			JOIN    (SELECT @curRow := 0) r
	
			WHERE 1
			AND med.mpointer = '" . $case->cpointer . "'
			AND `med`.medpnt = '" . $medpnt . "'
			ORDER BY medbill.medbpnt, `date`";
			echo $sql_billing . "\r\n\r\n<br /><br />";
	
			$stmt = $db->prepare($sql_billing);  
			$stmt->execute();
			
			$time = microtime();
			$time = explode(' ', $time);
			$time = $time[1] + $time[0];
			$finish_time = $time;
			$total_time = round(($finish_time - $header_start_time), 4);
			//echo " => 2nd query completed in " . $total_time . "<br /><br />";
		}
		
		
		$db = null;
	}
	$db = getNickConnection();
	
	$sql = "UPDATE `" . $data_source . "`.`badexams` 
	SET processed = 'Y'
	WHERE cpointer = '" . $case->cpointer . "'";
	echo $sql . "\r\n\r\n<br><br>";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	//die("done");
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	//$success = array("success"=> array("text"=>"done", "duration"=>$total_time));
	//completeds
	$sql = "SELECT COUNT(*) case_count
	FROM `" . $data_source . "`.`badexams` gcase
	WHERE 1";
	//echo $sql . "\r\n<br>";
	//die();
	$stmt = $db->prepare($sql);
	$stmt->execute();
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
	FROM `" . $data_source . "`.`badexams` ggc
	WHERE processed = 'Y'";
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
	
	echo $total_time . "<br />";
	//echo json_encode($success);
	if ($total_time > 5) {
		//die("too long");
	}
	if (count($cases) > 0) {
		//die("script language='javascript'>parent.runMain(" . $completed_count . "," . $case_count . ")</script");
		echo "<script language='javascript'>parent.runExams(" . $completed_count . "," . $case_count . ")</script>";
	}

	$db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage(), "sql"=>$sql));
	echo json_encode($error);
}

//include("cls_logging.php");
?>

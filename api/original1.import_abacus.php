<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include("manage_session.php");
set_time_limit(3000);

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;
$last_updated_date = date("Y-m-d H:i:s");

include("connection.php");
?>
<html>
<body style="font-size:0.95em">
<?php
try {
	$db = getConnection();
	
	include("customer_lookup.php");
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_case`;
	TRUNCATE `ikase_" . $data_source . "`.`cse_injury`;
	TRUNCATE `ikase_" . $data_source . "`.`cse_person`;
	TRUNCATE `ikase_" . $data_source . "`.`cse_case_person`;
	TRUNCATE `ikase_" . $data_source . "`.`cse_corporation`;
	TRUNCATE `ikase_" . $data_source . "`.`cse_case_corporation`;
	TRUNCATE `ikase_" . $data_source . "`.`cse_case_injury`;
	TRUNCATE `ikase_" . $data_source . "`.`cse_injury_number`;
	TRUNCATE `ikase_" . $data_source . "`.`cse_injury_injury_number`;
	";
	$stmt = $db->prepare($sql); 		
	$stmt->execute();
		
	//venues
	$sql = "SELECT * 
	FROM `ikase`.`cse_venue` 
	WHERE 1
	ORDER BY venue ASC";
	$stmt = $db->prepare($sql);
	$stmt = $db->query($sql);
	$venues = $stmt->fetchAll(PDO::FETCH_OBJ);
	$arrVenues = array();
	foreach($venues as $venue){
		$arrVenues[$venue->venue_uuid] = $venue->venue_abbr;
	}
	
	$sql = "SELECT DISTINCT law3.*, law1.*
	FROM `" . $data_source . "`.law3
	INNER JOIN `" . $data_source . "`.law9
	ON law3.CASENUM = law9.CASENUM
	INNER JOIN `" . $data_source . "`.law1
	ON law9.ID = law1.ID
	WHERE law1.`CLASS` = 'APPLICNT'
	#AND law3.CASENUM = 1296
	ORDER BY law3.CASENUM";
	//echo $sql . "\r\n<br>";

	//die();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$cases = $stmt->fetchAll(PDO::FETCH_OBJ);
	
	//die(print_r($cases));
	$found = count($cases);
	$arrCases = array();
	foreach($cases as $case_key=>$case){
		
		echo "Processing -> " . $case_key. " == " . $case->CASENUM . "  ";
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$process_start_time = $time;
		
		$case_no = $case->CASENUM;
		//insert the case
		$case_uuid = $case->CASENUM;
		
		if (in_array($case_uuid, $arrCases)) {
			continue;
		}
		$arrCases[] = $case_uuid;
		
		$case_number = $case->CASENUM;
		$case_name = $case->MATTER;
		$arrCase = explode("-", $case_name);
		$case_status = "Open";
		if (count($arrCase) > 1) {
			$case_status = $arrCase[0];
			$case_name = $arrCase[1];
		}
		$case_date = $case->OPENED;
		if ($case_date=="") {
			$case_date = $case->ENTRY;
		}
		//now the kase
		$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case` (case_uuid, cpointer, case_number, case_name, case_date, case_type, venue, case_status, submittedOn, supervising_attorney, customer_id) 
VALUES ('" . $case_uuid . "', '" . $case->CASENUM . "', '" . $case_number . "', '" . addslashes($case_name) . "', '" . date("Y-m-d", strtotime($case_date)) . "', '" . $case->CASECODE. "', '" . $case->COURT . "', '" . $case_status . "', '" . date("Y-m-d", strtotime($case->ENTRY)) . "', '" . $case->ATTORNEY . "', " . $customer_id . ")";
		//addslashes($case->CAPTION1) . "', '" . 
		//echo $sql . "\r\n<br>"; 
		$stmt = $db->prepare($sql); 		
		$stmt->execute();
		$injuries = array();
		$arrADJs = array();
		if ($case->DOI1!="") {
			$injuries[] = array("DOI"=>$case->DOI1, "ADJ"=>$case->WCABNO1, "HOW"=>$case->HOWHAP, "CLAIM"=>$case->CLAIMNO1);
		}
		if ($case->DOI2!="") {
			$injuries[] = array("DOI"=>$case->DOI2, "ADJ"=>$case->WCABNO2, "HOW"=>$case->HOWHAP2, "CLAIM"=>$case->CLAIMNO2);
		}
		if ($case->DOI3!="") {
			$injuries[] = array("DOI"=>$case->DOI3, "ADJ"=>$case->WCABNO3, "HOW"=>$case->HOWHAP3, "CLAIM"=>$case->CLAIMNO3);
		}

		$blnApplicantAdded = false;
		$applicant_name = "";
		$employer_name = "";
		$parent_applicant_uuid = "";
		$applicant_table_uuid = uniqid("DR", false);
		
		$arrInjuryID = array();	//keep track of uuids
		foreach($injuries as $injury_index=>$injury) {		
			$applicant_name = "";
			$employer_name = "";
			$parent_applicant_uuid = "";
			
			if (!$blnApplicantAdded) {				
				if (is_array($injury)) {
					$arrSet = array();
					$full_name = $case->FIRST;
					$full_name .= " " . $case->LAST;
					
					if ($applicant_name=="") {
						$applicant_name = $full_name;
					}
					
					$full_address = "";
					if ($case->LABEL3!="") {
						$full_address = $case->LABEL3;
					}
					if ($case->CITY!="") {
						$full_address .= ", " . $case->CITY;
					}
					if ($case!="") {
						$full_address .= ", " . $case->STATE;
					}
					if ($case->ZIP!="") {
						$full_address .= " " . $case->ZIP;
					}
					
					$sql = "SELECT person_uuid
					FROM `ikase_" . $data_source . "`.`cse_person`
					WHERE customer_id = " . $customer_id . "
					AND person_uuid = parent_person_uuid
					AND deleted = 'N'
					AND full_name = '" . addslashes($full_name) . "'
					AND full_address = '" . addslashes($full_address) . "'";
					//echo $sql . "\r\n<br>";
					$stmt = $db->prepare($sql);
					$stmt->execute();
					$rolodex = $stmt->fetchObject();
					$blnRolodex = false;
					if (is_object($rolodex)) {
						$parent_applicant_uuid = $rolodex->person_uuid;
						$blnRolodex = true;
					} else {
						$parent_applicant_uuid = uniqid("PA", false);
					}
					
					$arrSet[] = addslashes($full_name);
					$arrSet[] = addslashes($case->FIRST);
					$arrSet[] = addslashes($case->LAST);
					$arrSet[] = $case->DOB;
					$arrSet[] = "";
					$arrSet[] = addslashes($full_address);
					$street = $case->LABEL3; $arrSet[] = addslashes($street);
					$city = $case->CITY; $arrSet[] = addslashes($city);
					$state = $case->STATE; $arrSet[] = addslashes(substr($state, 0, 2));
					$zip = $case->ZIP; $zip = substr($zip, 0, 10); $arrSet[] = str_replace("\\", "", $zip);
					$case->SSN = str_replace("-", "", $case->SSN);
					$arrSet[] = $case->SSN;
					$arrSet[] = substr($case->SSN, strlen($case->SSN) - 4, 4);
					
					
					if (!$blnRolodex) {
						//insert the parent record first
						$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_person` 
						(`person_uuid`, `customer_id`, `parent_person_uuid`, `full_name`, `first_name`, `last_name`, `dob`,`aka`, `full_address`, `street`, `city`, `state`, `zip`, `ssn`, `ssn_last_four`, `last_updated_date`, `last_update_user`, `deleted`) 
						VALUES('" . $parent_applicant_uuid . "', '" . $customer_id . "', '" . $parent_applicant_uuid . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N')";
						
						//echo $sql . "\r\n<br>"; 
						$stmt = $db->prepare($sql);  
						$stmt->execute();
					}
					$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_person` 
					(`person_uuid`, `customer_id`, `parent_person_uuid`, `full_name`, `first_name`, `last_name`, `dob`, `aka`, `full_address`, `street`, `city`, `state`, `zip`, `ssn`, `ssn_last_four`, `last_updated_date`, `last_update_user`, `deleted`) 
					VALUES('" . $applicant_table_uuid . "', '" . $customer_id . "', '" . $parent_applicant_uuid . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N')";
					
					//echo $sql . "\r\n<br>"; 
					$stmt = $db->prepare($sql);  
					$stmt->execute();
					
					$case_table_uuid = uniqid("CA", false);
					//attach applicant to kase
					$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_person` (`case_person_uuid`, `case_uuid`, `person_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
					VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $applicant_table_uuid . "', 'main', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
					
					//echo $sql . "\r\n<br>"; 
					$stmt = $db->prepare($sql);  
					$stmt->execute();
					
					$blnApplicantAdded = true;
				}
			}
			
			
			$injury_uuid = uniqid("KI", false);
			$arrInjuryID[] = $injury_uuid;
			
			//die(print_r($injury));
			//doi dates
			if ($injury["DOI"]=="") {
				$injury["DOI"] = "0000-00-00";
			} else {
				$injury["DOI"] = date("Y-m-d", strtotime($injury["DOI"])); 
			}
			
			
			//echo "full_address:" . $full_address . "<br />";
			$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_injury` 
			(`injury_uuid`, `injury_number`, `adj_number`, `start_date`, `end_date`, `type`, `occupation`, `body_parts`, `ct_dates_note`,
			`full_address`, `street`, `suite`, `city`, `state`, `zip`, `customer_id`, `explanation`, `deleted`)
			VALUES ('" . $injury_uuid . "', " . ($injury_index + 1) . ", '" . $injury["ADJ"] . "', '" . $injury["DOI"] . "', '0000-00-00 00:00:00', 'IMPORT', '" . addslashes($case->OCCUPATION) . "','" .  addslashes($case->BODYPARTS) . "','',
			'', '','','', '', '', " . $customer_id . ", '" . addslashes($injury["HOW"]) . "', 'N')";
			//echo $sql . "\r\n<br>"; 
			//die();
			$stmt = $db->prepare($sql); 
			$stmt->execute();
		
			$case_table_uuid = uniqid("KA", false);
			$attribute_1 = "main";
			
			//now we have to attach the injury to the case 
			$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_injury` (`case_injury_uuid`, `case_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $injury_uuid . "', 'main', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
	
			//echo $sql . "\r\n<br>";  
			$stmt = $db->prepare($sql); 
			$stmt->execute();
			
			//add the claim number
			$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_injury_number`
			(`injury_number_uuid`, `alternate_policy_number`, `customer_id`, `deleted`)
			VALUES ('" . $case_table_uuid  ."', '" . $injury["CLAIM"] . "', '" . $customer_id . "', 'N')";
			//echo $sql . "\r\n<br>"; 
			//die();
			$stmt = $db->prepare($sql); 
			$stmt->execute();
		
			//$case_table_uuid = uniqid("KA", false);
			$attribute_1 = "main";
			
			//now we have to attach the injury to the case 
			$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_injury_injury_number` (`injury_injury_number_uuid`, `injury_uuid`, `injury_number_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_table_uuid  ."', '" . $injury_uuid . "', '" . $case_table_uuid . "', 'main', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
	
			//echo $sql . "\r\n<br>";  
			$stmt = $db->prepare($sql); 
			$stmt->execute();
			//die();
		}
		
		
		//parties
		$sql = "SELECT DISTINCT law1.*
		FROM `" . $data_source . "`.law3
		INNER JOIN `" . $data_source . "`.law9
		ON law3.CASENUM = law9.CASENUM
		INNER JOIN `" . $data_source . "`.law1
		ON law9.ID = law1.ID
		WHERE 1
		AND law1.`CLASS` != 'APPLICNT'
		AND law3.CASENUM = '" . $case_number . "'
		ORDER BY law3.CASENUM";
		
		$stmt = $db->prepare($sql);
		$stmt = $db->query($sql);
		$parties = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		foreach($parties as $partie) {
			$partie_address = $partie->LABEL3 . ", " . $partie->CITY . ", " . $partie->STATE . " " . $partie->ZIP;
			$table_uuid = uniqid("TU");
			$type = "";
			
			switch($partie->CLASS) {
				case "ER":
					$type = "employer";
					break;
				case "INS":
				case "CARRIER":
					$type = "carrier";
					break;
				case "APPATTY":
					$type = "applicant_attorney";
					break;
				case "CLIENT":
					$type = "client";
					break;
				case "DEF FIRM":
				case "DEF ATTY":
					$type = "defense";
					break;
				case "DR ORTHO":
				case "PTP":
					$type = "medical_provider";
					break;
			}
			
			if ($type=="") {
				continue;
			}
			$sql = "SELECT corporation_uuid
			FROM `ikase_" . $data_source . "`.`cse_corporation`
			WHERE customer_id = " . $customer_id . "
			AND corporation_uuid = parent_corporation_uuid
			AND type = '" . $type . "'
			AND deleted = 'N'
			AND company_name = '" . addslashes($partie->LAST) . "'
			AND full_address = '" . addslashes($partie_address) . "'";
			//echo $sql . "\r\n<br>";
			$stmt = $db->prepare($sql);
			$stmt->execute();
			$rolodex = $stmt->fetchObject();
			$blnRolodex = false;
			if (is_object($rolodex)) {
				$parent_table_uuid = $rolodex->corporation_uuid;
				$blnRolodex = true;
			}
			$arrSet = array();
			//$full_name = $injury->I_ADJFST . " " . $injury->I_ADJUSTER; $arrSet[] = addslashes($full_name);
			$full_name = ""; $arrSet[] = addslashes($full_name);
			$company_name = $partie->LAST; $arrSet[] = addslashes($company_name);	
			$arrSet[] = $type;
			$full_address = $partie_address; $arrSet[] = addslashes($partie_address);
			$street = $partie->LABEL3; $arrSet[] = addslashes($street);
			$city = $partie->CITY; $arrSet[] = addslashes($city);
			$state = $partie->STATE; $arrSet[] = addslashes(substr($state, 0, 2));
			$zip = $partie->ZIP; $zip = substr($zip, 0, 10); $arrSet[] = str_replace("\\", "", $zip);
			$phone = $partie->DAYPHONE; $arrSet[] = addslashes($phone);
			$fax = $partie->PHONE3; $arrSet[] = addslashes($fax);
			$salutation = $partie->DEAR; $arrSet[] = addslashes($salutation);
				
			if (!$blnRolodex) {
				$table_uuid = uniqid("DR", false);
				$parent_table_uuid = uniqid("PD", false);
				$last_updated_date = date("Y-m-d H:i:s");
				
				//insert the parent record first
				$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_corporation` 
				(`corporation_uuid`, `customer_id`, `full_name`, `company_name`, `type`, `full_address`, 
				`street`, `city`, `state`, `zip`, `phone`, `fax`, `salutation`, 
				`last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
				VALUES('" . $parent_table_uuid . "', '" . $customer_id . "', 
				'" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 
				'N', '" . $parent_table_uuid . "','')";
				//echo $sql . "\r\n"; 	
				//die($sql); 	
				$stmt = $db->prepare($sql); 
				$stmt->execute();
			}
			//actual record now
			$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_corporation` 
			(`corporation_uuid`, `customer_id`, `full_name`, `company_name`, `type`, `full_address`, 
				`street`, `city`, `state`, `zip`, `phone`, `fax`, `salutation`,  
				`last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
			VALUES('" . $table_uuid . "', '" . $customer_id . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N', '" . $parent_table_uuid . "','')";
			//echo $sql . "\r\n"; 		
			$stmt = $db->prepare($sql); 
			$stmt->execute();
			
			foreach($arrInjuryID as $injury_uuid) {
				//attach to injury
				$injury_table_uuid = uniqid("KA", false);
				//now we have to attach the partie to the case 
				$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_corporation` (`case_corporation_uuid`, `case_uuid`, `corporation_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $injury_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', '" . $injury_uuid . "', '" . $type . "', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
				//echo $sql . "\r\n<br>";   		
				$stmt = $db->prepare($sql);
				$stmt->execute();
				//only firs one
				break;
			}
			//die(print_r($arrInjuryID));
			echo "\r\n";
		}
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $process_start_time), 4);
		echo " => row completed in " . $total_time . "\r\n<br>"; 
	}
	
	$db = null;
} catch(PDOException $e) {
	echo $case_number . "\r\n";
	print_r($partie);
	echo $sql . "\r\n";	// . "\r\n<br>";
	die();
	$error = array("error"=> array("sql"=>$sql, "text"=>$e->getMessage()));
	die( json_encode($error));
}


?>
</body>
</html>
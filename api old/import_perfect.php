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

try {
	$db = getConnection();
	
	include("customer_lookup.php");
	
	if (isset($_GET["id"])) {
		$sql = "DELETE FROM  `" . $data_source . "`.`" . $data_source . "_case`
		WHERE case_uuid = '" . $_GET["id"] . "'";
		$db = getConnection(); $stmt = $db->prepare($sql); 		
		$stmt->execute(); $stmt = null; $db = null;
	}
	$sql = "SELECT df.*
	FROM `" . $data_source . "`.docfolder df
	LEFT OUTER JOIN `" . $data_source . "`.`" . $data_source . "_case` ccase
	ON df.DF_ID = ccase.case_uuid
	WHERE 1
	";
	if (isset($_GET["id"])) {
		$sql .= " AND df.DF_ID = '" . $_GET["id"] . "'";
	}
	$sql .= "
	AND ccase.case_id IS NULL
	ORDER BY df.DF_ID DESC
	LIMIT 0, 1";
	//#AND df.DF_ID = '91.'
	//die($sql);
	$db = getConnection(); 
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$cases = $stmt->fetchAll(PDO::FETCH_OBJ); 
	$stmt->closeCursor(); $stmt = null; $db = null;
	
	//venues
	$sql = "SELECT * 
	FROM `ikase`.`cse_venue` 
	WHERE 1
	ORDER BY venue ASC";
	$db = getConnection(); 
	$stmt = $db->prepare($sql);
	$stmt = $db->query($sql);
	$venues = $stmt->fetchAll(PDO::FETCH_OBJ); $stmt->closeCursor(); $stmt = null; $db = null;
	$arrVenues = array();
	foreach($venues as $venue){
		$arrVenues[$venue->venue_uuid] = $venue->venue_abbr;
	}
	
	foreach($cases as $case_key=>$case){
		//$case_uuid = uniqid("KS", false);
		$case_uuid = $case->DF_ID;
		$venue_abbr = "";
		$blnApplicantAdded = false;
		$employer = "";
		$arrBodyparts = array();
		$arrBodyparts2 = array();
				
		$CaseStatus = $case->CaseStatus;
		$CaseStatus = "Closed";
		if ($case->CaseStatus=="1") {
			$CaseStatus = "Open";
		}
		
		echo "Processing <a href='import_perfect.php?customer_id=" . $customer_id . "&id=" . $case->DF_ID . "'>" . $case->DF_ID . "</a><br />\r\n";
		//die();
		//get the injury info
		$sql = "SELECT cfn.* , cfm.NameST
		FROM `" . $data_source . "`.csfillnew cfn
		INNER JOIN `" . $data_source . "`.csfillmaster cfm
		ON cfn.FieldID = cfm.ID
		WHERE cfn.DF_ID = '" . $case->DF_ID . "'";
		
		//die($sql);
		$db = getConnection(); $stmt = $db->prepare($sql);
		$stmt->execute();
		$injury_info = $stmt->fetchAll(PDO::FETCH_OBJ); 
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		if (count($injury_info)==0) {
			$attorney_nickname ="";
			$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case` 
		(case_uuid, cpointer, case_number, case_name, case_date, case_type, case_status, submittedOn, supervising_attorney, customer_id) 
VALUES ('" . $case_uuid . "', '" . str_replace(".", "", $case->DF_ID) . "', '" . $case->DF_ID . "', '" . addslashes($case->DF_NAME) . "', '" . date("Y-m-d", strtotime($case->DF_CREATEDATETIME)) . "', 'WCAB', '" . $CaseStatus . "', '" . date("Y-m-d", strtotime($case->DF_CREATEDATETIME)) . "', '" . $attorney_nickname . "', '" . $customer_id . "')";
			//addslashes($case->CAPTION1) . "', '" . 
			echo $sql . "\r\n<br>"; 
			
			$db = getConnection(); $stmt = $db->prepare($sql); 		
			$stmt->execute(); $stmt = null; $db = null;
			
			echo "no go on " .  $case->DF_ID;
		} else {
			$attorney_first_name = "";
			$attorney_last_name = "";
			
			foreach($injury_info as $inj) {
				$field_name = $inj->NameST;
				$field_value = $inj->FieldValue;
				
				//ikase fieldname mapping
				switch($field_name) {
					case 'Applicant SSN':
						$field_name = "ssn";
						break;
					case 'Applicant Case Number':
						$field_name = "adj_number";
						break;
					case 'Applicant DOB':
						$field_name = "dob";
						break;
					case 'Injured Worker First Name':
					case 'Applicant First Name':
						$field_name = "first_name";
						break;
					case 'Applicant Injured Worker Middle Initial':
						$field_name = "middle_name";
						break;
					case 'Injured Worker Last Name':
					case 'Applicant Last Name':
						$field_name = "last_name";
						break;
					case 'Applicant Occupation':
						$field_name = "occupation";
						break;
					case 'Applicant Name':
						$field_name = "full_name";
						break;
					case 'Applicant Injured Worker Address':
					case 'Applicant Address':
						$field_name = "street";
						break;
					case 'Applicant Injured Worker City':
					case 'Applicant City':
						$field_name = "city";
						break;
					case 'Applicant Injured Worker State':
					case 'Applicant State':
						$field_name = "state";
						break;
					case 'Applicant Injured Worker Zip':
					case 'Applicant Zip':
						$field_name = "zip";
						break;
					case 'Attorney First Name':
						$attorney_first_name = $field_value;
						break;
					case 'Attorney Last Name':
						$attorney_last_name = $field_value;
						break;
					case 'How Injury Occurred':
						$field_name = "explanation";
						break;
					case 'Injured Occurred Address':
						$field_name = "injury_full_address";
						break;
					case 'Injured Occurred City':
						$field_name = "injury_city";
						break;
					case 'Injured Occurred State':
						$field_name = "injury_state";
						break;
					case 'Injured Occurred Zip':
						$field_name = "injury_zip";
						break;
					case 'Applicant Specific Injury DOI':
						$field_name = "start_date";
						break;
					//employer
					case 'Defendant Firm':
						$field_name = "employer_company_name";
						$employer = $field_value;
						break;
					case 'Defendant Address':
						$field_name = "employer_street";
						break;
					case 'Defendant City':
						$field_name = "employer_city";
						break;
					case 'Defendant State':
						$field_name = "employer_state";
						break;
					case 'Defendant Zip':
						$field_name = "employer_zip";
						break;
					//carrier
					case 'Insurance Carrier Firm':
						$field_name = "carrier_company_name";
						break;
					case 'Insurance Carrier Address':
						$field_name = "carrier_street";
						break;
					case 'Insurance Carrier City':
						$field_name = "carrier_city";
						break;
					case 'Insurance Carrier State':
						$field_name = "carrier_state";
						break;
					case 'Insurance Carrier Zip':
						$field_name = "carrier_zip";
						break;
					//claims
					case 'Claims Admin Firm':
						$field_name = "claimsadmin_company_name";
						break;
					case 'Claims Admin Address':
						$field_name = "claimsadmin_street";
						break;
					case 'Claims Admin City':
						$field_name = "claimsadmin_city";
						break;
					case 'Claims Admin State':
						$field_name = "claimsadmin_state";
						break;
					case 'Claims Admin Zip':
						$field_name = "claimsadmin_zip";
						break;
					case 'Applicant Case Number 2':
						$field_name = "adj_number_2";
						break;
					case 'M4_CaseNum2AppSpDOI':
						$field_name = "start_date_2";
						break;
					case 'M4_CaseNum2AppCmDOIEnd':
						$field_name = "end_date_2";
						break;
					case 'Venue':
						$venue_abbr = $field_value;
						break;
				}
				
				
				
				if (strpos($field_name, "M3_CaseNumBodyPart") === 0 && trim($field_value)!="") {
					//echo $field_name. "=" . $field_value . " // " . strpos($field_name, "M3_CaseNumBodyPart") . "<br />";
					//body parts
					$bodypart_number = str_replace("M3_CaseNumBodyPart", "", $field_name);
					$arrBodyparts[] = $field_value;
				}
				
				if (strpos($field_name, 'M3_CaseNum2BodyPart') === 0 && trim($field_value)!="") {
					//body parts
					$bodypart_number = str_replace("M3_CaseNum2BodyPart", "", $field_name);
					$arrBodyparts2[] = $field_value;
				}
				
				//all values array
				if (!isset($arrValues[$field_name])) {
					$arrValues[$field_name] = $field_value;
				}
			}
			
			//die(print_r($arrBodyparts));
			
			//default
			if (!isset($arrValues["explanation"])) {
				$arrValues["explanation"] = "";
			}
			
			$arrAttorneyNickname = array();
			if ($attorney_first_name!="") {
				$arrAttorneyNickname[] = $attorney_first_name;
			}
			if ($attorney_last_name!="") {
				$arrAttorneyNickname[] = $attorney_last_name;
			}
			$attorney_nickname = implode("", $arrAttorneyNickname);
			
			$case_name = $case->DF_NAME;
			if ($employer!="" && strpos($case_name, $employer)===false) {
				$case_name .= " vs " . $employer;
			}
			//insert the case stuff
			$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case` 
			(case_uuid, cpointer, case_number, case_name, case_date, case_type, case_status, submittedOn, supervising_attorney, customer_id) 
	VALUES ('" . $case_uuid . "', '" . str_replace(".", "", $case->DF_ID) . "', '" . $case->DF_ID . "', '" . addslashes($case_name) . "', '" . date("Y-m-d", strtotime($case->DF_CREATEDATETIME)) . "', 'WCAB', '" . $CaseStatus . "', '" . date("Y-m-d", strtotime($case->DF_CREATEDATETIME)) . "', '" . $attorney_nickname . "', '" . $customer_id . "')";
			//addslashes($case->CAPTION1) . "', '" . 
			echo $sql . "\r\n<br>"; 
			
			$db = getConnection(); $stmt = $db->prepare($sql); 		
			$stmt->execute(); $stmt = null; $db = null;
			
			if ($venue_abbr!="") {
				$parent_table_uuid = array_search($venue_abbr, $arrVenues);
				$blnRolodex = true;
				
				$sql = "UPDATE `" . $data_source . "`.`" . $data_source . "_case`
				SET `venue` = '" . $parent_table_uuid . "'
				WHERE case_uuid = '" . $case_uuid . "'";
				echo $sql . "\r\n<br>";
				$db = getConnection(); $stmt = $db->prepare($sql);
				$stmt->execute();
				
				$table_uuid = uniqid("VN", false);
				//now save the venue as corporation for parties
				$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation`(`corporation_uuid`, `parent_corporation_uuid`, `company_name`, `type`, `aka`, `employee_phone`, `full_name`, `full_address`, `street`, `city`, `state`, `zip`, `salutation`, `copying_instructions`, `customer_id`) 
				SELECT '" . $table_uuid . "', '" . $parent_table_uuid . "', `venue`, 'venue', `venue_abbr`, `phone`, '', CONCAT(`address1`, ',', `address2`,',', `city`,' ', `zip`) full_address, CONCAT(`address1`,',', `address2`) street, `city`,'CA', `zip`, 'Your Honor', '', " . $customer_id . " 
				FROM `ikase`.`cse_venue`
				WHERE venue_uuid = '" . $parent_table_uuid . "'";
				//echo $sql . "\r\n\r\n<br><br>";
				$stmt = $db->prepare($sql);  
				$stmt->execute();
				
				$table_name = "corporation";
				$case_table_uuid = uniqid("KA", false);
				$attribute_1 = "main";
				$last_updated_date = date("Y-m-d H:i:s");
				$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_" . $table_name . "` (`case_" . $table_name . "_uuid`, `case_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', 'venue', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";						
				//echo $sql . "\r\n\r\n<br><br>";
				$stmt = $db->prepare($sql);  
				$stmt->execute();
			}
			
			$arrPersonFields = array("first_name", "last_name", "full_name", "street", "city", "state", "zip", "dob", "ssn");
			
			$arrSet = array();
			$arrInserts = array();
			$inserts = "";
			foreach($arrPersonFields as $person_field) {
				if (isset($arrValues[$person_field])) {
					$arrInserts[] = "`" . $person_field . "`";
					$arrSet[] = "'" . addslashes($arrValues[$person_field]) . "'";
				}
			}
			
			$full_name = "";
			if (isset($arrValues["first_name"])) {
				$full_name = $arrValues["first_name"] . " " . $arrValues["last_name"];
			}
			//case def name
			if ($full_name=="") {
				if ($case->DF_NAME!="") {
					if (strpos($case->DF_NAME, ",") > 0) {
						$arrName = explode(",", $case->DF_NAME);
						$first_name = trim($arrName[1]);
						$last_name = trim($arrName[0]);
					} else {
						$arrName = explode(" ", $case->DF_NAME);
						$first_name = trim($arrName[0]);
						$last_name = "";
						if (count($arrName) > 1) {
							$last_name = trim($arrName[1]);
						}
					}
					$full_name = trim($first_name . " " . $last_name);
					
					if (!in_array("`first_name`", $arrInserts)) {
						$arrInserts[] = "`first_name`";
						$arrSet[] = "'" . addslashes($first_name) . "'";
					}
					if (!in_array("`last_name`", $arrInserts)) {
						$arrInserts[] = "`last_name`";
						$arrSet[] = "'" . addslashes($last_name) . "'";
					}
				}
			}
			
			if (!in_array("`full_name`", $arrInserts)) {
				$arrInserts[] = "`full_name`";
				$arrSet[] = "'" . addslashes($full_name) . "'";
			}
			$full_address = "";
			if (isset($arrValues["city"])) {
				$full_address = $arrValues["street"] . ", " . $arrValues["city"] . ", " . $arrValues["state"] . " " . $arrValues["zip"];
			}
			
			$arrInserts[] = "`full_address`";
			$arrSet[] = "'" . addslashes($full_address) . "'";
					
			$sql = "SELECT person_uuid
			FROM `" . $data_source . "`.`" . $data_source . "_person`
			WHERE customer_id = " . $customer_id . "
			AND person_uuid = parent_person_uuid
			AND deleted = 'N'
			AND full_name = '" . addslashes($full_name) . "'
			AND full_address = '" . addslashes($full_address) . "'";
			echo $sql . "\r\n<br>";
			$db = getConnection(); $stmt = $db->prepare($sql);
			$stmt->execute();
			$rolodex = $stmt->fetchObject(); $stmt->closeCursor(); $stmt = null; $db = null;
			
			$parent_applicant_uuid = "";
			$applicant_table_uuid = uniqid("DR", false);
			$blnRolodex = false;
			if (is_object($rolodex)) {
				$parent_applicant_uuid = $rolodex->person_uuid;
				$blnRolodex = true;
			} else {
				$parent_applicant_uuid = uniqid("PA", false);
			}
			
			if (!$blnRolodex) {
				$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_person` 
				(`person_uuid`, `customer_id`, `parent_person_uuid`, " . implode(", ", $arrInserts) . ")
				VALUES ('" . $parent_applicant_uuid . "', '" . $customer_id . "', '" . $parent_applicant_uuid . "', " . implode(", ", $arrSet) . ")";
				
				echo $sql . "\r\n<br>"; 
				
				$db = getConnection(); $stmt = $db->prepare($sql);  
				$stmt->execute(); $stmt = null; $db = null;
				
			}
			$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_person` 
			(`person_uuid`, `customer_id`, `parent_person_uuid`, " . implode(", ", $arrInserts) . ")
			VALUES ('" . $applicant_table_uuid . "', '" . $customer_id . "', '" . $parent_applicant_uuid . "', " . implode(", ", $arrSet) . ")";
			
			echo $sql . "\r\n<br>"; 
			
			$db = getConnection(); $stmt = $db->prepare($sql);  
			$stmt->execute(); $stmt = null; $db = null;
			
			
			$case_table_uuid = uniqid("CA", false);
			//attach applicant to kase
			$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_person` (`case_person_uuid`, `case_uuid`, `person_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $applicant_table_uuid . "', 'main', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
			
			echo $sql . "\r\n<br>"; 
			
			$db = getConnection(); $stmt = $db->prepare($sql);  
			$stmt->execute(); $stmt = null; $db = null;
			
			
			$blnApplicantAdded = true;
			
			//injury 1
			$arrInjuryFields = array("adj_number", "explanation", "injury_full_address", "occupation", "injury_street", "injury_city", "injury_state", "injury_zip", "start_date");
			
			$arrSet = array();
			$arrInserts = array();
			
			foreach($arrInjuryFields as $Injury_field) {
				if (isset($arrValues[$Injury_field])) {
					$arrInserts[] = "`" . str_replace("injury_", "", $Injury_field) . "`";
					if ($Injury_field!="start_date") {
						$arrSet[] = "'" . addslashes($arrValues[$Injury_field]) . "'";
					} else {
						$arrSet[] = "'" . date("Y-m-d H:i:s", strtotime($arrValues[$Injury_field])) . "'";
					}
				}
			}
			
			if (count($arrSet) > 0) {
				$injury_uuid = uniqid("KI", false);
					
				if (count($arrBodyparts) > 0) {
					$arrInserts[] = "`body_parts`";
					$arrSet[] = "'" . implode("; ", $arrBodyparts) . "'";
				}
				$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_injury` 
				(`injury_uuid`, `injury_number`, `customer_id`, `deleted`, " . implode(",", $arrInserts) . ")
				VALUES ('" . $injury_uuid . "', 1, " . $customer_id . ", 'N', " . implode(",", $arrSet) . ")";
				echo $sql . "\r\n<br>"; 
				
				$db = getConnection(); $stmt = $db->prepare($sql); 
				$stmt->execute(); $stmt = null; $db = null;
				
				
				$case_table_uuid = uniqid("KA", false);
				$attribute_1 = "main";
				
				//now we have to attach the injury to the case 
				$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_injury` (`case_injury_uuid`, `case_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $injury_uuid . "', 'main', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
		
				echo $sql . "\r\n<br>";  
				
				$db = getConnection(); $stmt = $db->prepare($sql); 
				$stmt->execute(); $stmt = null; $db = null;
				
			}
			
			
			//injury 2
			$arrInjuryFields = array("adj_number_2", "start_date_2", "end_date_2");
			
			$arrSet = array();
			$arrInserts = array();
			
			foreach($arrInjuryFields as $Injury_field) {
				if (isset($arrValues[$Injury_field])) {
					$field_name = str_replace("injury_", "", $Injury_field);
					$field_name = str_replace("_2", "", $field_name);
					$arrInserts[] = "`" . $field_name . "`";
					if ($field_name!="start_date" && $field_name!="end_date") {
						$arrSet[] = "'" . addslashes($arrValues[$Injury_field]) . "'";
					} else {
						$arrSet[] = "'" . date("Y-m-d H:i:s", strtotime($arrValues[$Injury_field])) . "'";
					}
				}
			}
			
			if (count($arrSet) > 0) {
				$arrInserts[] = "`explanation`";
				$arrSet[] = "''";
				
				if (count($arrBodyparts2) > 0) {
					$arrInserts[] = "`body_parts`";
					$arrSet[] = "'" . implode("; ", $arrBodyparts2) . "'";
				}
				
				$injury_uuid = uniqid("KI", false);
				$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_injury` 
				(`injury_uuid`, `injury_number`, " . implode(",", $arrInserts) . ", `customer_id`, `deleted`)
				VALUES ('" . $injury_uuid . "', 2, " . implode(",", $arrSet) . ", " . $customer_id . ", 'N')";
				echo $sql . "\r\n<br>"; 
				$db = getConnection(); $stmt = $db->prepare($sql); 
				$stmt->execute(); $stmt = null; $db = null;
				
			
				$case_table_uuid = uniqid("KA", false);
				$attribute_1 = "main";
				
				//now we have to attach the injury to the case 
				$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_injury` (`case_injury_uuid`, `case_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $injury_uuid . "', 'main', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
		
				echo $sql . "\r\n<br>";  
				$db = getConnection(); $stmt = $db->prepare($sql); 
				$stmt->execute(); $stmt = null; $db = null;
				
			}
			
			//employer
			$type = "employer";
			$arrEmployerFields = array("employer_company_name", "employer_street", "employer_city", "employer_state", "employer_zip");
			$arrSet = array();
			$arrInserts = array();
			
			foreach($arrEmployerFields as $Employer_field) {
				if (isset($arrValues[$Employer_field])) {
					$arrInserts[] = "`" . str_replace("employer_", "", $Employer_field) . "`";
					$arrSet[] = "'" . addslashes($arrValues[$Employer_field]) . "'";
				}
			}
			if (count($arrSet) > 0) {
				$employer_address = "";
				$arrInserts[] = "`type`";
				$arrSet[] = "'employer'";
				if (isset($arrValues["employer_street"])) {
					$employer_address = $arrValues["employer_street"] . ", " . $arrValues["employer_city"] . ", " . $arrValues["employer_state"] . " " . $arrValues["employer_zip"];
				}
				
				$table_uuid = uniqid("DR", false);
				$parent_table_uuid = uniqid("PD", false);
				$last_updated_date = date("Y-m-d H:i:s");
				
				if ($employer_address!="") {
					$arrInserts[] = "`full_address`";
					$arrSet[] = "'" . $employer_address . "'";
					
					//look up in case already in
					$sql = "SELECT corporation_uuid
					FROM `" . $data_source . "`.`" . $data_source . "_corporation`
					WHERE customer_id = " . $customer_id . "
					AND corporation_uuid = parent_corporation_uuid
					AND type = 'employer'
					AND deleted = 'N'
					AND company_name = '" . addslashes($employer) . "'
					AND full_address = '" . addslashes($employer_address) . "'";
					echo $sql . "\r\n<br>";
					$db = getConnection(); $stmt = $db->prepare($sql);
					$stmt->execute();
					$rolodex = $stmt->fetchObject(); $stmt->closeCursor(); $stmt = null; $db = null;
					if (is_object($rolodex)) {
						$parent_table_uuid = $rolodex->corporation_uuid;
						$blnRolodex = true;
					}
					if (!$blnRolodex) {
						//insert the parent record first
						$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation` 
						(`corporation_uuid`, `parent_corporation_uuid`, `customer_id`, `deleted`, `copying_instructions`, " . implode(",", $arrInserts) . ") 
						VALUES('" . $parent_table_uuid . "', '" . $parent_table_uuid . "', '" . $customer_id . "', 
						'N', ''," . implode(",", $arrSet) . ")";
						echo $sql . "\r\n<br>";
						
						$db = getConnection(); $stmt = $db->prepare($sql); 
						$stmt->execute(); $stmt = null; $db = null;
						
					}
				}
				//actual record now
				$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation` 
				(`corporation_uuid`, `parent_corporation_uuid`, `customer_id`, `deleted`, `copying_instructions`, " . implode(",", $arrInserts) . ") 
				VALUES('" . $table_uuid . "', '" . $parent_table_uuid . "', '" . $customer_id . "', 
				'N', '', " . implode(",", $arrSet) . ")";
				echo $sql . "\r\n<br>";
					
				$db = getConnection(); $stmt = $db->prepare($sql);
				$stmt->execute(); $stmt = null; $db = null;
				
				
				//attach to injury
				$injury_table_uuid = uniqid("KA", false);
				//now we have to attach the partie to the case 
				$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_corporation` (`case_corporation_uuid`, `case_uuid`, `corporation_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $injury_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', '" . $injury_uuid . "', '" . $type . "', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
				echo $sql . "\r\n<br>";   		
				$db = getConnection(); $stmt = $db->prepare($sql);
				$stmt->execute(); $stmt = null; $db = null;
			}
			
			//carrier
			$type = "carrier";
			$arrCarrierFields = array("carrier_company_name", "carrier_street", "carrier_city", "carrier_state", "carrier_zip");
			$arrSet = array();
			$arrInserts = array();
			
			foreach($arrCarrierFields as $Carrier_field) {
				if (isset($arrValues[$Carrier_field])) {
					$arrInserts[] = "`" . str_replace("carrier_", "", $Carrier_field) . "`";
					$arrSet[] = "'" . addslashes($arrValues[$Carrier_field]) . "'";
				}
			}
			if (count($arrSet) > 0) {
				$carrier_address = "";
				$arrInserts[] = "`type`";
				$arrSet[] = "'carrier'";
				
				if (isset($arrValues["carrier_street"])) {
					$carrier_address = $arrValues["carrier_street"] . ", " . $arrValues["carrier_city"] . ", " . $arrValues["carrier_state"] . " " . $arrValues["carrier_zip"];
				}
				
				$table_uuid = uniqid("DR", false);
				$parent_table_uuid = uniqid("PD", false);
				$last_updated_date = date("Y-m-d H:i:s");
				
				if ($carrier_address!="") {
					$arrInserts[] = "`full_address`";
					$arrSet[] = "'" . $carrier_address . "'";
					
					//look up in case already in
					$sql = "SELECT corporation_uuid
					FROM `" . $data_source . "`.`" . $data_source . "_corporation`
					WHERE customer_id = " . $customer_id . "
					AND corporation_uuid = parent_corporation_uuid
					AND type = 'carrier'
					AND deleted = 'N'
					AND company_name = '" . addslashes($carrier) . "'
					AND full_address = '" . addslashes($carrier_address) . "'";
					echo $sql . "\r\n<br>";
					$db = getConnection(); $stmt = $db->prepare($sql);
					$stmt->execute();
					$rolodex = $stmt->fetchObject(); $stmt->closeCursor(); $stmt = null; $db = null;
					if (is_object($rolodex)) {
						$parent_table_uuid = $rolodex->corporation_uuid;
						$blnRolodex = true;
					}
				}
				if (!$blnRolodex) {
					//insert the parent record first
					$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation` 
					(`corporation_uuid`, `parent_corporation_uuid`, `customer_id`, `deleted`, `copying_instructions`, " . implode(",", $arrInserts) . ") 
					VALUES('" . $parent_table_uuid . "', '" . $parent_table_uuid . "', '" . $customer_id . "', 
					'N', '', " . implode(",", $arrSet) . ")";
					echo $sql . "\r\n<br>";
					$db = getConnection(); $stmt = $db->prepare($sql); 
					$stmt->execute(); $stmt = null; $db = null;
					
				}
				//actual record now
				$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation` 
				(`corporation_uuid`, `parent_corporation_uuid`, `customer_id`, `deleted`, `copying_instructions`, " . implode(",", $arrInserts) . ") 
				VALUES('" . $table_uuid . "', '" . $parent_table_uuid . "', '" . $customer_id . "', 
				'N', '', " . implode(",", $arrSet) . ")";
				echo $sql . "\r\n<br>";
					
				$db = getConnection(); $stmt = $db->prepare($sql);
				$stmt->execute(); $stmt = null; $db = null;
				
				
				//attach to injury
				$injury_table_uuid = uniqid("KA", false);
				//now we have to attach the partie to the case 
				$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_corporation` (`case_corporation_uuid`, `case_uuid`, `corporation_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $injury_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', '" . $injury_uuid . "', '" . $type . "', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
				echo $sql . "\r\n<br>";   		
				$db = getConnection(); $stmt = $db->prepare($sql);
				$stmt->execute(); $stmt = null; $db = null;
			}
			
			//claim
			$type = "claim";
			$arrClaimFields = array("claim_company_name", "claim_street", "claim_city", "claim_state", "claim_zip");
			$arrSet = array();
			$arrInserts = array();
			
			foreach($arrClaimFields as $Claim_field) {
				if (isset($arrValues[$Claim_field])) {
					$arrInserts[] = "`" . str_replace("claim_", "", $Claim_field) . "`";
					$arrSet[] = "'" . addslashes($arrValues[$Claim_field]) . "'";
				}
			}
			if (count($arrSet) > 0) {
				$arrInserts[] = "`type`";
				$arrSet[] = "'claim'";
				$claim_address = "";
				if (isset($arrValues["claim_street"])) {
					$claim_address = $arrValues["claim_street"] . ", " . $arrValues["claim_city"] . ", " . $arrValues["claim_state"] . " " . $arrValues["claim_zip"];
				}
				
				$table_uuid = uniqid("DR", false);
				$parent_table_uuid = uniqid("PD", false);
				$last_updated_date = date("Y-m-d H:i:s");
				
				if ($claim_address != "") {
					$arrInserts[] = "`full_address`";
					$arrSet[] = "'" . $claim_address . "'";
					//look up in case already in
					$sql = "SELECT corporation_uuid
					FROM `" . $data_source . "`.`" . $data_source . "_corporation`
					WHERE customer_id = " . $customer_id . "
					AND corporation_uuid = parent_corporation_uuid
					AND type = 'claim'
					AND deleted = 'N'
					AND company_name = '" . addslashes($claim) . "'
					AND full_address = '" . addslashes($claim_address) . "'";
					echo $sql . "\r\n<br>";
					$db = getConnection(); $stmt = $db->prepare($sql);
					$stmt->execute();
					$rolodex = $stmt->fetchObject(); $stmt->closeCursor(); $stmt = null; $db = null;
					if (is_object($rolodex)) {
						$parent_table_uuid = $rolodex->corporation_uuid;
						$blnRolodex = true;
					}
					if (!$blnRolodex) {
						//insert the parent record first
						$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation` 
						(`corporation_uuid`, `parent_corporation_uuid`, `customer_id`, `deleted`, `copying_instructions`, " . implode(",", $arrInserts) . ") 
						VALUES('" . $parent_table_uuid . "', '" . $parent_table_uuid . "', '" . $customer_id . "', 
						'N', '', " . implode(",", $arrSet) . ")";
						echo $sql . "\r\n<br>";
						
						$db = getConnection(); $stmt = $db->prepare($sql); 
						$stmt->execute(); $stmt = null; $db = null;
						
					}
				}
				//actual record now
				$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation` 
				(`corporation_uuid`, `parent_corporation_uuid`, `customer_id`, `deleted`, `copying_instructions`, " . implode(",", $arrInserts) . ") 
				VALUES('" . $table_uuid . "', '" . $parent_table_uuid . "', '" . $customer_id . "', 
				'N', '',  " . implode(",", $arrSet) . ")";
				echo $sql . "\r\n<br>";
					
				$db = getConnection(); $stmt = $db->prepare($sql);
				$stmt->execute(); $stmt = null; $db = null;
				
				
				//attach to injury
				$injury_table_uuid = uniqid("KA", false);
				//now we have to attach the partie to the case 
				$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_corporation` (`case_corporation_uuid`, `case_uuid`, `corporation_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $injury_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', '" . $injury_uuid . "', '" . $type . "', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
				echo $sql . "\r\n<br>";   
					
				$db = getConnection(); $stmt = $db->prepare($sql);
				$stmt->execute(); $stmt = null; $db = null;
				
			}
			//documents
			$sql = "SELECT *
			FROM `" . $data_source . "`.`doclist`
			WHERE DF_ID = '" . $case->DF_ID . "'";
			
			$db = getConnection(); 
			$stmt = $db->prepare($sql);
			$stmt->execute();
			$documents = $stmt->fetchAll(PDO::FETCH_OBJ); 
			$stmt->closeCursor(); $stmt = null; $db = null;
			
			foreach($documents as $doc) {
				$document_uuid = uniqid("PD");
				$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_document`
				(document_uuid, document_date, document_name, document_filename, document_extension, source, description, description_html, customer_id)
				VALUES ('" . $document_uuid . "', '" . $doc->DL_UPLOADDATETIME . "', '" . addslashes($doc->DL_FILENAME) . "', '" . addslashes($doc->DL_FILENAME) . "', '" . $doc->DL_FILETYPE . "', 'perfect', '', '', '" . $customer_id . "')";
				echo $sql . "\r\n<br>";   
				$db = getConnection(); $stmt = $db->prepare($sql);
				$stmt->execute(); $stmt = null; $db = null;
				
				
				//attach to injury
				$document_table_uuid = uniqid("KA", false);
				$last_updated_date = date("Y-m-d H:i:s");
				//now we have to attach the partie to the case 
				$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_document` (`case_document_uuid`, `case_uuid`, `document_uuid`, `attribute_1`, `attribute_2`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $document_table_uuid  ."', '" . $case_uuid . "', '" . $document_uuid . "', 'main', '', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
				echo $sql . "\r\n<br>";   
				
				$db = getConnection(); $stmt = $db->prepare($sql);
				$stmt->execute(); $stmt = null; $db = null;
				
			}
		}
		
		//employer catch
		if ($employer=="") {
			echo "no employer protocol<br />\r\n";
			
			$sql = "SELECT * 
			FROM perfect.foldercontacts fc
			INNER JOIN perfect.contactperson cp
			ON fc.ContactId = cp.PersonId
			INNER JOIN perfect.contactfirm cf
			ON cp.FirmId= cf.FirmID
			INNER JOIN perfect.contacttype ct
			ON cp.ContactType = ct.ContactTypeID
			WHERE FolderId = '" . $case->DF_ID . "'";
			
			$db = getConnection(); 
			$stmt = $db->prepare($sql);
			$stmt = $db->query($sql);
			$parties = $stmt->fetchAll(PDO::FETCH_OBJ); $stmt->closeCursor(); $stmt = null; $db = null;
			
			//die(count($parties) . " parties found<br />\r\n");
			
			foreach($parties as $partie) {
				$type = $partie->ContactType;
				if ($type!="Injured Worker") {
					$employer_address = "";
					$arrInserts[] = "`type`";
					$arrSet[] = "'" . strtolower($type) . "'";
					
					$company = $partie->FirmName;
					$company_address = $partie->MailingAddressLine1 . ", " . $partie->MailingCity . ", " . $partie->State . " " . $partie->Zip5;
					
					$table_uuid = uniqid("DR", false);
					$parent_table_uuid = uniqid("PD", false);
					$last_updated_date = date("Y-m-d H:i:s");
					
					if ($company_address!="") {
						$arrInserts[] = "`full_address`";
						$arrSet[] = "'" . $employer_address . "'";
						
						$arrInserts[] = "`company_name`";
						$arrSet[] = "'" . addslashes($partie->FirmName) . "'";
						$arrInserts[] = "`street`";
						$arrSet[] = "'" . addslashes($partie->MailingAddressLine1) . "'";
						$arrInserts[] = "`city`";
						$arrSet[] = "'" . addslashes($partie->MailingCity) . "'";
						$arrInserts[] = "`state`";
						$arrSet[] = "'" . addslashes($partie->State) . "'";
						$arrInserts[] = "`zip`";
						$arrSet[] = "'" . $partie->Zip5 . "'";
						
						//look up in case already in
						$sql = "SELECT corporation_uuid
						FROM `" . $data_source . "`.`" . $data_source . "_corporation`
						WHERE customer_id = " . $customer_id . "
						AND corporation_uuid = parent_corporation_uuid
						AND type = 'employer'
						AND deleted = 'N'
						AND company_name = '" . addslashes($company) . "'
						AND full_address = '" . addslashes($company_address) . "'";
						echo $sql . "\r\n<br>";
						$db = getConnection(); $stmt = $db->prepare($sql);
						$stmt->execute();
						$rolodex = $stmt->fetchObject(); $stmt->closeCursor(); $stmt = null; $db = null;
						if (is_object($rolodex)) {
							$parent_table_uuid = $rolodex->corporation_uuid;
							$blnRolodex = true;
						}
						if (!$blnRolodex) {
							//insert the parent record first
							$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation` 
							(`corporation_uuid`, `parent_corporation_uuid`, `customer_id`, `deleted`, `copying_instructions`, " . implode(",", $arrInserts) . ") 
							VALUES('" . $parent_table_uuid . "', '" . $parent_table_uuid . "', '" . $customer_id . "', 
							'N', ''," . implode(",", $arrSet) . ")";
							echo $sql . "\r\n<br>";
							
							$db = getConnection(); $stmt = $db->prepare($sql); 
							$stmt->execute(); $stmt = null; $db = null;
							
						}
					}
					//actual record now
					$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation` 
					(`corporation_uuid`, `parent_corporation_uuid`, `customer_id`, `deleted`, `copying_instructions`, " . implode(",", $arrInserts) . ") 
					VALUES('" . $table_uuid . "', '" . $parent_table_uuid . "', '" . $customer_id . "', 
					'N', '', " . implode(",", $arrSet) . ")";
					echo $sql . "\r\n<br>";
						
					$db = getConnection(); $stmt = $db->prepare($sql);
					$stmt->execute(); $stmt = null; $db = null;
					
					
					//attach to injury
					$injury_table_uuid = uniqid("KA", false);
					//now we have to attach the partie to the case 
					$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_corporation` (`case_corporation_uuid`, `case_uuid`, `corporation_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
					VALUES ('" . $injury_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', '" . $injury_uuid . "', '" . $type . "', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
					echo $sql . "\r\n<br>";   		
					$db = getConnection(); $stmt = $db->prepare($sql);
					$stmt->execute(); $stmt = null; $db = null;
				}
			}
			
		}
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $header_start_time), 4);
		
		$sql = "SELECT COUNT(*) case_count
		FROM `" . $data_source . "`.`docfolder` gcase
		WHERE 1";
		echo $sql . "\r\n<br>";
		//die();
		$db = getConnection(); $stmt = $db->prepare($sql);
		$stmt->execute();
		$cases = $stmt->fetchObject(); $stmt->closeCursor(); $stmt = null; $db = null;
		
		$case_count = $cases->case_count;
		
		//completeds
		$sql = "SELECT COUNT(cpointer) case_count
		FROM `" . $data_source . "`.`" . $data_source . "_case` ggc
		WHERE 1";
		echo $sql . "\r\n<br>";
		//die();
		$db = getConnection(); $stmt = $db->prepare($sql);
		$stmt->execute();
		$cases = $stmt->fetchObject(); $stmt->closeCursor(); $stmt = null; $db = null;
		
		$completed_count = $cases->case_count;
		
		$success = array("success"=> array("text"=>"done", "duration"=>$total_time, "completed_count"=>$completed_count, "case_count"=>$case_count));
		//echo json_encode($success);
		
		echo "<br />\r\n";
		echo "<br />\r\n";
		
		$sql = "SELECT gcase.* 
		FROM `" . $data_source . "`.`docfolder` gcase
		LEFT OUTER JOIN `" . $data_source . "`.`" . $data_source . "_case` ggc
		ON gcase.DF_ID = ggc.case_uuid
		WHERE 1
		AND ggc.case_id IS NULL
		ORDER BY DF_ID DESC
		LIMIT 0, 1";
		echo $sql . "\r\n<br>";
		//	#AND CASENO = 19493 OR CASENO = 19490 OR CASENO = 19454
		//die();
		$db = getConnection(); $stmt = $db->prepare($sql);
		$stmt->execute();
		$cases = $stmt->fetchAll(PDO::FETCH_OBJ); $stmt->closeCursor(); $stmt = null; $db = null;
		/*
		if ($venue_abbr!="") {
			die(" --> venue");
		}
		*/
		if (count($cases) > 0) {
			echo "<script language='javascript'>parent.runMain(" . $completed_count . "," . $case_count . ")</script>";
		}
	}
} catch(PDOException $e) {
	echo $sql . "\r\n<br>";
	$error = array("error"=> array("text"=>$e->getMessage()));
	die( json_encode($error));
}
?>
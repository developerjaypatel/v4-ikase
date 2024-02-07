<?php
set_time_limit(12000);
ini_set('memory_limit','256M');

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', '1');

include("../tritek/connection.php");

$data_source = passed_var("data_source", "get");
$customer_id = passed_var("customer_id", "get");

if (!is_numeric($customer_id)) {
	die("no id");
}
$dir = "F:\\" . $data_source  . "\\";
$last_updated_date = date("Y-m-d H:i:s");

$arrSQL = array();

try{
	$sql = "SELECT user_id, user_uuid, nickname
	FROM ikase.cse_user
	WHERE deleted ='N' 
	AND customer_id = " . $customer_id;
	
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$users = $stmt->fetchAll(PDO::FETCH_OBJ);
	$stmt->closeCursor(); $stmt = null; $db = null;
	$arrUsers = array();
	foreach($users as $user) {
		$arrUsers[$user->nickname]["id"] = $user->user_id;
		$arrUsers[$user->nickname]["uuid"] = $user->user_uuid;
	}
	
	//die(print_r($arrUsers));
	
	$sql = "SELECT gcase.* 
	FROM `" . $data_source . "`.`cases` gcase
	WHERE processed = 'N'
	LIMIT 0, 1
	";
	//echo $sql . "\r\n<br>";
	
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$kase = $stmt->fetchObject();
	$stmt->closeCursor(); $stmt = null; $db = null;
	
	echo "Processing: " . $kase->folder . "\r\n\r\n";
	
	$folder = $kase->folder;
	
	$filename = $dir . $folder . "\\case_file.json";
	$handle = fopen($filename, "r");
	$contents = fread($handle, filesize($filename));
	fclose($handle);
	
	$the_kase = json_decode($contents);
	//die(print_r($the_kase));
	
	$case_uuid = uniqid("MS", false);
	$cpointer = $the_kase->file_number;
	$file_number = $the_kase->file_number;
	$case_number = $the_kase->case_number;
	$case_name = $the_kase->case_name;
	$case_date = $the_kase->case_date;
	$case_type = $the_kase->case_type;
	$venue = $the_kase->venue;
	$case_status = $the_kase->case_status;
	$supervising_attorney = $the_kase->supervising_attorney;
	if ($supervising_attorney=="JR") {
		$supervising_attorney = "JRR";
	}
	$attorney = $the_kase->attorney;
	if ($attorney=="JR") {
		$attorney = "JRR";
	}
	$worker = $the_kase->worker;
	
	//die(print_r($case));
	//the case itself
	$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case` (case_uuid, cpointer, file_number, case_number, case_name, case_date, case_type, venue, case_status, supervising_attorney, attorney, worker, customer_id) 
	VALUES ('" . $case_uuid . "', '". $cpointer . "', '" . $file_number . "', '". $case_number . "', '" . addslashes($case_name) . "', '" . date("Y-m-d", strtotime($case_date)) . "', '" . addslashes($case_type) . "', '" . addslashes($venue) . "', '" . $case_status . "', '" . addslashes($supervising_attorney) . "', '" . addslashes($attorney) . "', '" . addslashes($worker) . "', '" . $customer_id . "')";
	
echo $sql . ";<br />\r\n\r\n";

$db = getConnection();
$stmt = $db->prepare($sql);
$stmt->execute();
	
	$case_id = $db->lastInsertId();
	
	$db = null; $stmt = null;
	
	
	
	$case_notes_uuid = uniqid("CN", false);
	$notes_uuid = uniqid("NT", false);
	$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_notes` (`case_notes_uuid`, `case_uuid`, `notes_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
	VALUES ('" . $case_notes_uuid . "', '" . $case_uuid . "', '" . $notes_uuid . "', 'quick', '" . $last_updated_date . "', 'system', 'N', '" . $customer_id . "')";
	
/*echo $sql . ";\r\n\r\n"; */
$arrSQL[] = $sql;

$db = getConnection();
$stmt = $db->prepare($sql);
$stmt->execute();
$db = null; $stmt = null;

	
	
	$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_notes` (`notes_uuid`, `note`, `dateandtime`, `entered_by`, `customer_id`, `type`)
	VALUES ('" . $notes_uuid . "', '" . addslashes($the_kase->case_description) . "', '" . date("Y-m-d", strtotime($the_kase->case_date)) . "', 'system', '" . $customer_id . "', 'quick')";
	
/*echo $sql . ";\r\n\r\n"; */
$arrSQL[] = $sql;

$db = getConnection();
$stmt = $db->prepare($sql);
$stmt->execute();
$db = null; $stmt = null;

	
	
	//now the injury
	//die(print_r($the_kase->injuries));
	
	$injury_uuid = uniqid("KI", false);
	$body_parts = "";
	$adj_number = "";
	if (count($the_kase->injuries) == 1) {
		$injury = $the_kase->injuries;
		//die(print_r($injury ));
		$start_date = $injury->start_date;
		$end_date = $injury->end_date;
		
		if ($start_date!="0000-00-00" && $start_date!="") {
			$start_date = date("Y-m-d", strtotime($start_date));
			$ct_dates = "";
		} else {
			$start_date = "0000-00-00";
		}
		if ($end_date!="0000-00-00" && $end_date!="") {
			$end_date = date("Y-m-d", strtotime($end_date));
			$ct_dates = date("m/d/Y", strtotime($start_date)) . " - " . date("m/d/Y", strtotime($end_date));
		} else {
			$end_date = "0000-00-00";
		}
		if (isset($injury->body_parts)) {
			//die(print_r(json_decode($injury->body_parts)));
			$body_parts = implode(",", json_decode($injury->body_parts));
		}
		$adj_number = $injury->adj_number;
	} else {
		$start_date = "0000-00-00";
		$end_date = "0000-00-00";
	}
	
	$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_injury` (`injury_uuid`, `injury_number`, `adj_number`, `type`, `occupation`, `start_date`, `end_date`, `body_parts`, `explanation`, `full_address`, `customer_id`, `deleted`)
	VALUES('" . $injury_uuid . "', '1', '" . addslashes($adj_number) . "', '', '" . addslashes($injury->occupation) . "','" . $start_date . "','" . $end_date . "','" . addslashes($body_parts) . "','" . addslashes($injury->explanation) . "','" . addslashes($injury->full_address) . "', '" . $customer_id . "', 'N')";
	
echo $sql . ";<br />\r\n\r\n"; $arrSQL[] = $sql;

$db = getConnection();
$stmt = $db->prepare($sql);
$stmt->execute();
	
	$injury_id = $db->lastInsertId();
	
	$db = null; $stmt = null;
	
	
	if ($start_date != "0000-00-00") {
		$interval = 2;
		if ($case_type=="WCAB") {
			$interval = 5;
		}
		//update `statute_limitation`
		$sql = "UPDATE `" . $data_source . "`.`" . $data_source . "_injury` 
		SET statute_limitation = DATE_ADD(`start_date`, INTERVAL " . $interval . " YEAR)
		WHERE injury_uuid = '" . $injury_uuid . "'";
		
/*echo $sql . ";\r\n\r\n"; */
$arrSQL[] = $sql;

$db = getConnection();
$stmt = $db->prepare($sql);
$stmt->execute();
$db = null; $stmt = null;

		
	}
	//now attach to case, even before I create case
	$case_table_uuid = uniqid("KA", false);
	$attribute_1 = "main";
	
	//now we have to attach the injury to the case 
	$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_injury` (`case_injury_uuid`, `case_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
	VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $injury_uuid . "', 'main', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
	
/*echo $sql . ";\r\n\r\n"; */
$arrSQL[] = $sql;

$db = getConnection();
$stmt = $db->prepare($sql);
$stmt->execute();
$db = null; $stmt = null;

		
	if ($case_type!="WCAB") {
		//insert a personal injury
		$personal_injury_uuid = uniqid("KA", false);
		$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_personal_injury`
(`personal_injury_uuid`, `case_id`, `personal_injury_date`, `loss_date`, `personal_injury_description`, `customer_id`)
VALUES('" . $personal_injury_uuid . "', '" . $case_id . "', '" . $start_date . "', '" . $start_date . "', '" . addslashes($injury->explanation) . "', '" . $customer_id . "')";

/*echo $sql . ";\r\n\r\n"; */
$arrSQL[] = $sql;

$db = getConnection();
$stmt = $db->prepare($sql);
$stmt->execute();
$db = null; $stmt = null;

		if ($start_date!="" && $start_date!="0000-00-00") {
		//update `statute_limitation`
		$sql = "UPDATE `" . $data_source . "`.`" . $data_source . "_personal_injury` 
		SET statute_limitation = DATE_ADD(`personal_injury_date`, INTERVAL 2 YEAR)
		WHERE personal_injury_uuid = '" . $personal_injury_uuid . "'";
		
/*echo $sql . ";\r\n\r\n"; */
$arrSQL[] = $sql;

$db = getConnection();
$stmt = $db->prepare($sql);
$stmt->execute();
$db = null; $stmt = null;
		}
	}
	if (!isset($the_kase->parties)) {
		die("no parties");
	}
	if (isset($the_kase->parties)) {
		$parties = $the_kase->parties;
		//die(print_r($parties));
		foreach($parties as $key=>$partie){
			$table_uuid = uniqid("DR", false);
			$parent_table_uuid = uniqid("PD", false);
			
			$blnRolodex = false;
			
			//address
			$full_address_partie = $partie->street;
			if ($partie->suite!="") {
				$full_address_partie .= ", " . $partie->suite;
			}
			$full_address_partie .= ", " . $partie->city;
			$full_address_partie .= ", " . $partie->state;
			$full_address_partie .= " " . $partie->zip;
			
			$partial_address = $partie->street . ", " . $partie->city . ", " . $partie->state . " " . $partie->zip;
			$arrSet = array();
			$full_name = $partie->first_name;
			if ($partie->middle_name!="") {
				$full_name .= " " . $partie->middle_name;
			}
			$full_name .= " " . $partie->last_name;
			
			$arrSet[] = addslashes(trim($full_name));
			$arrSet[] = addslashes($partie->first_name);
			$arrSet[] = addslashes($partie->last_name);
			
			$type = trim(strtolower($partie->partie_type)); 
			$type = str_replace(" ", "_", $type);
			//if blnContinue is true, we will skip this partie
			$blnContinue = false;
			switch($type){
				case "employer":
					break;
				case "court":
					$type = "venue";
					break;
				case "insurance":
					$type = "carrier";
					break;
			}
			
			//medical providers
			if (strpos($type, "physician") !== false) {
				$type = "medical_provider";
			}
			if ($blnContinue) {
				continue;
			}
			if ($type!="applicant") {
				$company_name = $partie->company_name; $arrSet[] = addslashes($company_name);
				$arrSet[] = addslashes($type);
			}
			//echo $type;
			//
			
			$full_address = $full_address_partie; $arrSet[] = addslashes($full_address);
			$street = $partie->street; $arrSet[] = addslashes($street);
			$city = $partie->city; $arrSet[] = addslashes($city);
			$state = $partie->state; $arrSet[] = addslashes(substr($state, 0, 2));
			$zip = $partie->zip; $zip = substr($zip, 0, 10); $arrSet[] = str_replace("\\", "", $zip);
			$suite = $partie->suite; $arrSet[] = addslashes($suite);
			//$phone = $partie->phone; $arrSet[] = addslashes($phone);
			$phone = $partie->phone;
			$arrSet[] = addslashes($phone);
			$fax = $partie->fax; $arrSet[] = addslashes($fax);
			$email = $partie->email; $arrSet[] = addslashes($email);
			$employee_phone = $partie->other_phone; $arrSet[] = addslashes($employee_phone);
			$salutation = $partie->salutation; $arrSet[] = addslashes($salutation);
			//$language = $partie->salutation; $arrSet[] = addslashes($language);
			if ($type=="applicant") {
				/*
				birth_city
				birth_country
				gender
				ssn
				marital_status
				*/
				//nationality
			}
			//interpreter_needed, case_language
			if (!$blnRolodex) {
				if ($type!="venue" && $type!="applicant" && $type!="client") {
					//look up in case already in
					$sql = "SELECT corporation_uuid
					FROM `" . $data_source . "`.`" . $data_source . "_corporation`
					WHERE customer_id = " . $customer_id . "
					AND corporation_uuid = parent_corporation_uuid
					AND type = '" . addslashes(strtolower($partie->partie_type)) . "'
					AND deleted = 'N'
					AND company_name = '" . addslashes($partie->company_name) . "'
					AND full_address = '" . addslashes($full_address_partie) . "'";
					//echo $sql . "\r\n<br>";
					$db = getConnection(); 
					$stmt = $db->prepare($sql);
					$stmt->execute();
					$rolodex = $stmt->fetchObject(); 
					$stmt->closeCursor(); $stmt = null; $db = null;
					if (is_object($rolodex)) {
						$parent_table_uuid = $rolodex->corporation_uuid;
						$blnRolodex = true;
					} else {
						$parent_table_uuid = uniqid("PA", false);
					}
				}
			}
			if ($type=="venue") {
				$venue_abbr = $partie->VENUE;
				$parent_table_uuid = array_search($venue_abbr, $arrVenues);
				$blnRolodex = true;
				
				$sql = "UPDATE `" . $data_source . "`.`" . $data_source . "_case`
				SET `venue` = '" . $parent_table_uuid . "'
				WHERE case_uuid = '" . $case_uuid . "'";
				
/*echo $sql . ";\r\n\r\n"; */
$arrSQL[] = $sql;

$db = getConnection();
$stmt = $db->prepare($sql);
$stmt->execute();
$db = null; $stmt = null;

				
			}
			
			if ($type=="applicant" || $type=="client") {
				//die(print_r($partie));
				if ($partie->interpreter=="" || $partie->interpreter=="0") {
					$partie->interpreter = "N";
				} else {
					$partie->interpreter = "Y";
				}
				//need to update interpreter and language
				$sql = "UPDATE `" . $data_source . "`.`" . $data_source . "_case`
				SET `interpreter_needed` = '" . addslashes($partie->interpreter) . "',
				`case_language` = '" . addslashes(str_replace("\\", "", $partie->language)) . "'
				WHERE case_uuid = '" . $case_uuid . "'";
				
echo $sql . ";\r\n\r\n";
$arrSQL[] = $sql;

$db = getConnection();
$stmt = $db->prepare($sql);
$stmt->execute();
$db = null; $stmt = null;

				
				
				$full_name = $partie->first_name;
				if ($partie->middle_name!="") {
					$full_name .= " " . $partie->middle_name;
				}
				$full_name .= " " . $partie->last_name;
				if ($partie->SUFFIX!="") {
					$full_name .= ", " . $partie->SUFFIX;
				}
				if ($applicant_name=="") {
					$applicant_name = $full_name;
				}
				
				if ($parent_applicant_uuid=="") {
					$sql = "SELECT person_uuid
					FROM `" . $data_source . "`.`" . $data_source . "_person`
					WHERE customer_id = " . $customer_id . "
					AND person_uuid = parent_person_uuid
					AND deleted = 'N'
					AND full_name = '" . addslashes($full_name) . "'
					AND full_address = '" . addslashes($full_address_partie) . "'";
					//echo $sql . "\r\n<br>";
					$db = getConnection(); 
					$stmt = $db->prepare($sql);
					$stmt->execute();
					$rolodex = $stmt->fetchObject(); 
					$stmt->closeCursor(); $stmt = null; $db = null;
					$blnRolodex = false;
					$parent_table_uuid = "";
					if (is_object($rolodex)) {
						$parent_applicant_uuid = $rolodex->person_uuid;
						$blnRolodex = true;
					} else {
						$parent_applicant_uuid = uniqid("PA", false);
					}
				}
				
				$full_address = $full_address_partie;
				$arrSet = array();
				
				$arrSet[] = addslashes($full_name);
				$arrSet[] = addslashes($partie->first_name);
				$arrSet[] = addslashes($partie->last_name);
				
				$arrSet[] = $full_address;
				
				//die(print_r($arrSet));
				$street = $partie->street; $arrSet[] = addslashes($street);
				$city = $partie->city; $arrSet[] = addslashes($city);
				$state = $partie->state; $arrSet[] = addslashes(substr($state, 0, 2));
				$zip = $partie->zip; $zip = substr($zip, 0, 10); $arrSet[] = str_replace("\\", "", $zip);
				$suite = $partie->suite; $arrSet[] = addslashes($suite);
				$phone = $partie->phone; $arrSet[] = addslashes($phone);
				$email = $partie->email; $arrSet[] = $email;
				$employee_fax = $partie->person_fax; $arrSet[] = addslashes($employee_fax);
				$employee_phone = $partie->other_phone; $arrSet[] = addslashes($employee_phone);
				$partie->ssn = str_replace("-", "", $partie->ssn);
				$arrSet[] = $partie->ssn;
				$arrSet[] = substr($partie->ssn, strlen($partie->ssn) - 4, 4);
				$dob = $partie->dob;
				//die($injury->clientss . " // " . $dob);
				$age = 0;
				if ($dob!="") {
					$dob = date("Y-m-d", strtotime($partie->dob));
					$birthDate = explode("-", $partie->dob);
					//get age from date or birthdate
					$age = (date("md", date("U", mktime(0, 0, 0, $birthDate[1], $birthDate[2], $birthDate[0]))) > date("md") ? ((date("Y") - $birthDate[0]) - 1) : (date("Y") - $birthDate[0]));
				}
				
				$arrSet[] = $dob;
				$arrSet[] = $age;
				$arrSet[] = addslashes($partie->license);
				$arrSet[] = addslashes($partie->salutation);
				$arrSet[] = "";
				$arrSet[] = $partie->language;
				
				//die(print_r($arrSet));
				if ($applicant_table_uuid!="") {
					//this is an update!
					$arrUpdateSet = array();
					if ($phone!="") {
						$arrUpdateSet[] = "`phone` = '" . addslashes($phone) . "'";
					}
					if ($fax!="") {
						$arrUpdateSet[] = "`fax` = '" . addslashes($fax) . "'";
					}
					if ($age!="") {
						$arrUpdateSet[] = "`age` = '" . $age . "'";
					}
					if ($dob!="") {
						$arrUpdateSet[] = "`dob` = '" . $dob . "'";
					}
					$set = " SET ";
					if (count($arrUpdateSet) > 0) {
						$set .= implode(", ", $arrUpdateSet) . ",";
					}
					$set .= "
					`license_number` = '" . addslashes($partie->license) . "',
					`language` = '" . addslashes($partie->language) . "',
					`salutation` = '" . addslashes($partie->salutation) . "'";
					
					$sql = "UPDATE `" . $data_source . "`.`" . $data_source . "_person`
					" . $set . "
					WHERE `parent_person_uuid` = '" . $parent_applicant_uuid . "'";
					
/*echo $sql . ";\r\n\r\n"; */
$arrSQL[] = $sql;

$db = getConnection();
$stmt = $db->prepare($sql);
$stmt->execute();
$db = null; $stmt = null;
				
				} else {
					if (!$blnRolodex) {
						//insert the parent record first
						$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_person` (`person_uuid`, `customer_id`, `parent_person_uuid`, `full_name`, `first_name`, `last_name`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `email`, `fax`, `work_phone`, `ssn`, `ssn_last_four`, `dob`, `age`, `license_number`, `salutation`, `gender`, `language`, `last_updated_date`, `last_update_user`, `deleted`) 
						VALUES('" . $parent_applicant_uuid . "', '" . $customer_id . "', '" . $parent_applicant_uuid . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N')";
						
echo $sql . ";\r\n\r\n";
$arrSQL[] = $sql;

$db = getConnection();
$stmt = $db->prepare($sql);
$stmt->execute();
$db = null; $stmt = null;

						
					}
					$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_person` (`person_uuid`, `customer_id`, `parent_person_uuid`, `full_name`, `first_name`, `last_name`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `email`, `fax`, `work_phone`, `ssn`, `ssn_last_four`, `dob`, `age`, `license_number`, `salutation`, `gender`, `language`, `last_updated_date`, `last_update_user`, `deleted`) 
					VALUES('" . $table_uuid . "', '" . $customer_id . "', '" . $parent_applicant_uuid . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N')";
					
echo $sql . ";\r\n\r\n";
$arrSQL[] = $sql;

$db = getConnection();
$stmt = $db->prepare($sql);
$stmt->execute();
$db = null; $stmt = null;

					
					//
					$case_table_uuid = uniqid("CA", false);
					//attach applicant to kase
					$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_person` (`case_person_uuid`, `case_uuid`, `person_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
					VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', 'main', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
					
echo $sql . ";\r\n\r\n";
$arrSQL[] = $sql;

$db = getConnection();
$stmt = $db->prepare($sql);
$stmt->execute();
$db = null; $stmt = null;

				}
			} else {
				//parties
				if (!$blnRolodex) {
					//insert the parent record first
					$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation` 
					(`corporation_uuid`, `customer_id`, `full_name`, `first_name`, `last_name`, `company_name`, `type`, `full_address`, 
					`street`, `city`, `state`, `zip`, `suite`, `phone`, `fax`, `email`, 
					`employee_phone`, `salutation`, 
					`last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
					VALUES('" . $parent_table_uuid . "', '" . $customer_id . "', 
					'" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 
					'N', '" . $parent_table_uuid . "','')";
					
echo $sql . ";\r\n\r\n";
$arrSQL[] = $sql;

$db = getConnection();
$stmt = $db->prepare($sql);
$stmt->execute();
$db = null; $stmt = null;

					
				}
				//actual record now
				$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation` (`corporation_uuid`, `customer_id`, `full_name`, `first_name`, `last_name`, `company_name`, `type`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `fax`, `email`, `employee_phone`, `salutation`, `last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
				VALUES('" . $table_uuid . "', '" . $customer_id . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N', '" . $parent_table_uuid . "','')";
echo $sql . ";\r\n\r\n";
$arrSQL[] = $sql;

$db = getConnection();
$stmt = $db->prepare($sql);
$stmt->execute();
$db = null; $stmt = null;

				
				
							//attach to case
				$case_table_uuid = uniqid("KA", false);
				//now we have to attach the partie to the case 
				$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_corporation` (`case_corporation_uuid`, `case_uuid`, `corporation_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', '" . addslashes($type) . "', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
				
echo $sql . ";\r\n\r\n";
$arrSQL[] = $sql;

$db = getConnection();
$stmt = $db->prepare($sql);
$stmt->execute();
$db = null; $stmt = null;

				
				
								//die();
			}
		}
	}
	//activity
	$sql_case_activity = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_activity` (`case_activity_uuid`, `case_uuid`,  `activity_uuid`, 
			`attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)";
	
	$sql_activity = "
			INSERT INTO `" . $data_source . "`.`" . $data_source . "_activity` 
			(`activity_uuid`, `activity`, `activity_date`, `activity_category`, `activity_user_id`, `customer_id`)";
			
	$arrValuesCaseActivity = array();
	$arrValuesActivity = array();		
	if (isset($the_kase->activities)) {
		$activities = $the_kase->activities;
		foreach($activities as $activity) {
			//die(print_r($activity));
			$initials = $activity->initials;
			if ($initials=="JR") {
				$initials = "JRR";
			}
			if (!isset($arrUsers[$initials])) {
				//die(print_r($activity));
				$activity_user_id = -1;
				$activity_user_uuid = "SYSTEM";
			} else {
				$activity_user_id = $arrUsers[$initials]["id"];
				$activity_user_uuid = $arrUsers[$initials]["uuid"];
			}
			//lookup user
			$activity_uuid = uniqid("MS", false);
			$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_activity` (`case_activity_uuid`, `case_uuid`,  `activity_uuid`, 
			`attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
			VALUES ('" . $case_uuid . "', '" . $case_uuid . "', '" . $activity_uuid  . "', 
			'main', '" . date("Y-m-d", strtotime($activity->activity_date))  . "', '" . $activity_user_uuid . "', 'N', " . $customer_id . ")";
			
			$arrValuesCaseActivity[] = "VALUES ('" . $case_uuid . "', '" . $case_uuid . "', '" . $activity_uuid  . "', 
			'main', '" . date("Y-m-d", strtotime($activity->activity_date))  . "', '" . $activity_user_uuid . "', 'N', " . $customer_id . ")";
/*echo $sql . ";\r\n\r\n"; */
/*
$arrSQL[] = $sql;

$db = getConnection();
$stmt = $db->prepare($sql);
$stmt->execute();
$db = null; $stmt = null;
*/
			
			
			$sql = "
			INSERT INTO `" . $data_source . "`.`" . $data_source . "_activity` 
			(`activity_uuid`, `activity`, `activity_date`, `activity_category`, `activity_user_id`, `customer_id`)
			VALUES ('" . $activity_uuid  . "', '" . addslashes($activity->activity) . "', '" . date("Y-m-d", strtotime($activity->activity_date))  . "', 
			'" . $activity->category . "', '" . $activity_user_id . "', '" . $customer_id . "')";
			$arrValuesActivity[] = "VALUES ('" . $activity_uuid  . "', '" . addslashes($activity->activity) . "', '" . date("Y-m-d", strtotime($activity->activity_date))  . "', 
			'" . $activity->category . "', '" . $activity_user_id . "', '" . $customer_id . "')";
			
/*echo $sql . ";\r\n\r\n"; */
/*
$arrSQL[] = $sql;

$db = getConnection();
$stmt = $db->prepare($sql);
$stmt->execute();
$db = null; $stmt = null;
*/
		}
	}
	
	if (count($arrValuesActivity) > 0) {
		$sql = $sql_case_activity . "\r\n" . implode(",\r\n", $arrValuesCaseActivity);
		echo $sql . ";\r\n\r\n";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$db = null; $stmt = null;
		
		$sql = $sql_activity . "\r\n" . implode(",\r\n", $arrValuesActivity);
		echo $sql . ";\r\n\r\n";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$db = null; $stmt = null;

	}
	//die("stop");
		
	//tasks
	
	//events
	
	//documents
	/*
	$sql = implode(";\r\n\r\n", $arrSQL) . ";";
	
	echo $sql;
	
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$db = null; $stmt = null;
	*/
	
	echo "\r\ndone?\r\n";
	
	$sql = "UPDATE `" . $data_source . "`.`cases` gcase
	SET processed = 'Y'
	WHERE case_id = '" . $kase->case_id . "'";
	
	echo $sql . "\r\n";
	
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$db = null; $stmt = null;
	
	
	//completeds
	$sql = "SELECT COUNT(case_id) case_count
	FROM `" . $data_source . "`.`cases` gcase
	WHERE processed = 'N'";
	//echo $sql . "\r\n<br>";
	//die();
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$cases = $stmt->fetchObject();
	
	$case_count = $cases->case_count;
	$db = null; $stmt = null;
	
	//completeds
	$sql = "SELECT COUNT(case_id) case_count
	FROM `" . $data_source . "`.`cases` ggc
	WHERE processed = 'Y'";
	echo $sql . "\r\n<br>";
	//die();
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$cases = $stmt->fetchObject();
	
	$completed_count = $cases->case_count;
	$db = null; $stmt = null;
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	$success = array("success"=> array("text"=>"done", "duration"=>$total_time, "completed_count"=>$completed_count, "case_count"=>$case_count));
	
	if ($completed_count < $case_count) {
		/*
		echo "<script language='javascript'>parent.runMain(" . $completed_count . "," . $case_count . ")</script>";
        */
		echo "<script language='javascript'>
		var href = 'transfer.php?customer_id=" . $customer_id . "&data_source=" . $data_source . "';
		console.log(href);
		//document.location.href = href;
		</script>";
	} else {
		die("done");
	}
	
} catch (PDOException $e) {
	echo $e->getMessage();
	die("
	ERROR:
	$sql");
	$error = array("error"=> array("text"=>$e->getMessage(), "sql"=>$sql, "error"=>$arrErrorCatch));
	echo json_encode($error);
}
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
	
	//maybe don't need it
	$sql = "SELECT COUNT(person_id) person_count
	FROM `ikase_" . $data_source . "`.`cse_person`";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$injuries = $stmt->fetchObject();
	$stmt->closeCursor(); $stmt = null;
	
	//die(print_r($injuries));
	$found = $injuries->person_count;
	
	if ($found > 0) {
		die("person table is already populated.");
	}
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_person`;
	TRUNCATE `ikase_" . $data_source . "`.`cse_case_person`";
	echo $sql . "\r\n\r\n<br>";
	//die();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "SELECT case_id, case_uuid, case_name, case_number
	FROM `ikase_" . $data_source . "`.`cse_case`
	WHERE customer_id = '" . $customer_id . "'
	ORDER BY case_id ASC";
	echo $sql . "\r\n\r\n<br>";
	
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$cases = $stmt->fetchAll(PDO::FETCH_OBJ);
	$stmt->closeCursor(); $stmt = null;
	
	//die(print_r($cases));
	$found = count($cases);
	
	foreach($cases as $case_key=>$case){
		$case_uuid = $case->case_uuid;
		$case_name = $case->case_name;
		$case_number = $case->case_number;
		
		$sql = "SELECT acc.CASENO, acc.CARDCODE, acc.TYPE partie_type,  `ac`.`CARDCODE`,  `ac`.`FIRMCODE`,  `ac`.`LETSAL`,  
		`ac`.`SALUTATION`,  `ac`.`FIRST`,  `ac`.`MIDDLE`,  `ac`.`LAST`,  `ac`.`SUFFIX`,  `ac`.`SOCIAL_SEC`,  `ac`.`TITLE`,  `ac`.`HOME`,  
		`ac`.`BUSINESS`,  `ac`.`FAX` person_fax,  `ac`.`CAR`,  `ac`.`BEEPER`,  `ac`.`EMAIL`,  `ac`.`BIRTH_DATE`,  `ac`.`INTERPRET`,  
		`ac`.`LANGUAGE`,  `ac`.`LICENSENO`,  `ac`.`SPECIALTY`,  `ac`.`MOTHERMAID`,  `ac`.`PROTECTED`,
		`ac2`.`FIRMCODE`,  `ac2`.`FIRM`,  `ac2`.`VENUE`,  `ac2`.`TAX_ID`,  `ac2`.`ADDRESS1`,  `ac2`.`ADDRESS2`,  `ac2`.`CITY`,  `ac2`.`STATE`,  `ac2`.`ZIP`,  
		`ac2`.`PHONE1`,  `ac2`.`PHONE2`,  `ac2`.`FAX` partie_fax,  `ac2`.`FIRMKEY`,  `ac2`.`COLOR`,  `ac2`.`EAMSREF`,
		card3.NAME eams_name, card3.ADDRESS1 eams_street, card3.ADDRESS2 eams_suite, 
		card3.CITY eams_city, card3.STATE eams_state, card3.ZIP eams_zip, card3.PHONE eams_phone
		FROM `" . $data_source . "`.casecard acc
		INNER JOIN `" . $data_source . "`.card ac
		ON acc.CARDCODE = ac.CARDCODE
		INNER JOIN `" . $data_source . "`.card2 ac2
		ON ac.FIRMCODE = ac2.FIRMCODE
		LEFT OUTER JOIN `" . $data_source . "`.card3
		ON ac2.EAMSREF = card3.EAMSREF
		WHERE acc.CASENO = '" . $case->case_number . "'
		AND (acc.TYPE = 'APPLICANT' OR acc.TYPE = 'CLIENT')
		ORDER BY acc.CARDCODE
		LIMIT 0, 1";
		/*
		$arrCaseName = explode("vs", $case_name);
		$full_name = trim($arrCaseName[0]);
		$arrName = explode(" ", $full_name);
		$first_name = $arrName[0];
		$last_name = $arrName[count($arrName) - 1];
		unset($arrName[0]);
		unset($arrName[1]);
		$middle_name = "";
		if (count($arrName) > 0) {
			$middle_name = implode(" ", $arrName);
		}
		
		
		$parent_applicant_uuid = uniqid("PA", false);
		
		//insert the parent record first
		$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_person` 
		(`person_uuid`, `customer_id`, `parent_person_uuid`, `full_name`, `first_name`, `last_name`, `middle_name`, `last_updated_date`,  `last_update_user`, `deleted`) 
		VALUES('" . $parent_applicant_uuid . "', '" . $customer_id . "', '" . $parent_applicant_uuid . "', '" . addslashes($full_name) . "', '" . addslashes($first_name) . "', '" . addslashes($last_name) . "', '" . addslashes($middle_name) . "', '" . date("Y-m-d H:i:s") . "', 'system', 'N')";
		
		echo $sql . "\r\n<br>"; 
		//die();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		$applicant_table_uuid = uniqid("DR", false);
		
		$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_person` 
		(`person_uuid`, `customer_id`, `parent_person_uuid`, `full_name`, `first_name`, `last_name`, `middle_name`, `last_updated_date`, `last_update_user`, `deleted`) 
		VALUES('" . $applicant_table_uuid . "', '" . $customer_id . "', '" . $parent_applicant_uuid . "', '" . addslashes($full_name) . "', '" . addslashes($first_name) . "', '" . addslashes($last_name) . "', '" . addslashes($middle_name) . "', '" . date("Y-m-d H:i:s") . "', 'system', 'N')";
		
		echo $sql . "\r\n<br>"; 
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		$case_table_uuid = uniqid("CA", false);
		//attach applicant to kase
		$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_person` (`case_person_uuid`, `case_uuid`, `person_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $applicant_table_uuid . "', 'main', '" . date("Y-m-d H:i:s") . "', 'system', '" . $customer_id . "')";
		
		echo $sql . "\r\n<br>"; 
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		*/

		$stmt = $db->prepare($sql);
		$stmt->execute();
		$parties = $stmt->fetchAll(PDO::FETCH_OBJ);
		//die(print_r($parties));
		$arrCpointer = array();
		
		foreach($parties as $key=>$partie){
			$parent_applicant_uuid = uniqid("PA", false);
			$applicant_table_uuid = uniqid("DR", false);
			
			$table_uuid = uniqid("DR", false);
			$parent_table_uuid = uniqid("PD", false);
			$last_updated_date = date("Y-m-d H:i:s");
			$blnRolodex = false;
			
			//address
			$full_address_partie = $partie->ADDRESS1;
			if ($partie->ADDRESS2!="") {
				$full_address_partie .= ", " . $partie->ADDRESS2;
			}
			$full_address_partie .= ", " . $partie->CITY;
			$full_address_partie .= ", " . $partie->STATE;
			$full_address_partie .= " " . $partie->ZIP;
			
			$partial_address = $partie->ADDRESS1 . ", " . $partie->CITY . ", " . $partie->STATE . " " . $partie->ZIP;
			$arrSet = array();
			
			
			$full_address = $full_address_partie;
			$type = strtolower($partie->partie_type); 
			
			if ($type!="applicant" && $type!="client") {
				continue;
			}
			//if blnContinue is true, we will skip this partie
			$blnContinue = false;
			
			if ($type=="applicant" || $type=="client") {
				//die(print_r($partie));
				if ($partie->INTERPRET!="Y") {
					$partie->INTERPRET = "N";
				}
				//need to update interpreter and language
				$sql = "UPDATE `ikase_" . $data_source . "`.`cse_case`
				SET `interpreter_needed` = '" . addslashes($partie->INTERPRET) . "',
				`case_language` = '" . addslashes(str_replace("\\", "", $partie->LANGUAGE)) . "'
				WHERE case_uuid = '" . $case_uuid . "'";
				echo $sql . "\r\n<br>";
				//die();
				$stmt = $db->prepare($sql);
				$stmt->execute();
				
				$full_name = $partie->FIRST;
				if ($partie->MIDDLE!="") {
					$full_name .= " " . $partie->MIDDLE;
				}
				$full_name .= " " . $partie->LAST;
				if ($partie->SUFFIX!="") {
					$full_name .= ", " . $partie->SUFFIX;
				}
				$applicant_name = $full_name;
				
				
				$sql = "SELECT person_uuid
				FROM `ikase_" . $data_source . "`.`cse_person`
				WHERE customer_id = " . $customer_id . "
				AND person_uuid = parent_person_uuid
				AND deleted = 'N'
				AND full_name = '" . addslashes($full_name) . "'
				AND full_address = '" . addslashes($full_address) . "'";
				echo $sql . "\r\n<br>";
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
				//$arrSet[] = addslashes($type);
				$full_address = $full_address_partie;
				
				$arrSet[] = addslashes($full_name);
				$arrSet[] = addslashes($partie->FIRST);
				$arrSet[] = addslashes($partie->LAST);
				$arrSet[] = "";
				$arrSet[] = addslashes($full_address);
				$street = $partie->ADDRESS1; $arrSet[] = addslashes($street);
				$city = $partie->CITY; $arrSet[] = addslashes($city);
				$state = $partie->STATE; $arrSet[] = addslashes(substr($state, 0, 2));
				$zip = $partie->ZIP; $zip = substr($zip, 0, 10); $arrSet[] = str_replace("\\", "", $zip);
				$suite = $partie->ADDRESS2; $arrSet[] = addslashes($suite);
				$phone = $partie->PHONE1; $arrSet[] = addslashes($phone);
				$email = $partie->EMAIL; $arrSet[] = $email;
				$fax = $partie->partie_fax; $arrSet[] = addslashes($fax);
				//$employee_fax = $partie->person_fax; $arrSet[] = addslashes($employee_fax);
				$employee_phone = $partie->BUSINESS; $arrSet[] = addslashes($employee_phone);
				$employee_cellphone = $partie->HOME; $arrSet[] = addslashes($employee_cellphone);
				$partie->SOCIAL_SEC = str_replace("-", "", $partie->SOCIAL_SEC);
				$arrSet[] = $partie->SOCIAL_SEC;
				$arrSet[] = substr($partie->SOCIAL_SEC, strlen($partie->SOCIAL_SEC) - 4, 4);
				$dob = $partie->BIRTH_DATE;
				//die($injury->clientss . " // " . $dob);
				$age = 0;
				if ($dob!="") {
					$dob = date("Y-m-d", strtotime($partie->BIRTH_DATE));
					$birthDate = explode("-", $partie->BIRTH_DATE);
					//get age from date or birthdate
					$age = (date("md", date("U", mktime(0, 0, 0, $birthDate[1], $birthDate[2], $birthDate[0]))) > date("md") ? ((date("Y") - $birthDate[0]) - 1) : (date("Y") - $birthDate[0]));
				}
				
				$arrSet[] = $dob;
				$arrSet[] = $age;
				$arrSet[] = addslashes($partie->LICENSENO);
				$arrSet[] = addslashes($partie->SALUTATION);
				$arrSet[] = "";
				$arrSet[] = $partie->LANGUAGE;
				
				//die(print_r($arrSet));
				if (!$blnRolodex) {
					//insert the parent record first
					$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_person` (`person_uuid`, `customer_id`, `parent_person_uuid`, `full_name`, `first_name`, `last_name`, `aka`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `email`, `fax`, `work_phone`, `cell_phone`, `ssn`, `ssn_last_four`, `dob`, `age`, `license_number`, `salutation`, `gender`, `language`, `last_updated_date`, `last_update_user`, `deleted`) 
					VALUES('" . $parent_applicant_uuid . "', '" . $customer_id . "', '" . $parent_applicant_uuid . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N')";
					
					echo $sql . "\r\n<br>no rol<br />"; 
					//die("no rol");
					$stmt = $db->prepare($sql);  
					$stmt->execute();
				}
				$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_person` (`person_uuid`, `customer_id`, `parent_person_uuid`, `full_name`, `first_name`, `last_name`, `aka`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `email`, `fax`, `work_phone`, `cell_phone`, `ssn`, `ssn_last_four`, `dob`, `age`, `license_number`, `salutation`, `gender`, `language`, `last_updated_date`, `last_update_user`, `deleted`) 
				VALUES('" . $applicant_table_uuid . "', '" . $customer_id . "', '" . $parent_applicant_uuid . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N')";
				
				echo $sql . "\r\n<br>rol<br />"; 
				$stmt = $db->prepare($sql);  
				$stmt->execute();
				
				$case_table_uuid = uniqid("CA", false);
				//attach applicant to kase
				$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_person` (`case_person_uuid`, `case_uuid`, `person_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $applicant_table_uuid . "', 'main', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
				
				echo $sql . "\r\n<br>"; 
				$stmt = $db->prepare($sql);  
				$stmt->execute();
			} 
		}
		$blnApplicantAdded = true;
	}
} catch(PDOException $e) {
	echo $sql . "\r\n<br>";
	$error = array("error"=> array("text"=>$e->getMessage()));
	die( json_encode($error));
}
?>
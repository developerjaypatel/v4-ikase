<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once('../shared/legacy_session.php');
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
	$data_source = str_replace("2", "", $data_source);
	/*
	//venues
	$sql = "SELECT * 
	FROM `ikase`.`cse_venue` 
	WHERE 1
	ORDER BY venue ASC";
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt = $db->query($sql);
	$venues = $stmt->fetchAll(PDO::FETCH_OBJ);
	$arrVenues = array();
	foreach($venues as $venue){
		$arrVenues[$venue->venue_uuid] = $venue->venue_abbr;
	}
	
	$sql = "SELECT ggc.case_number CASENO, ggc.case_uuid, ggc.case_type
	FROM `" . $data_source . "`.`case` gcase
	LEFT OUTER JOIN `" . $data_source . "`.`" . $data_source . "_case` ggc
	ON gcase.CASENO = ggc.cpointer AND ggc.case_number != 'RSA'
	
	LEFT OUTER JOIN `" . $data_source . "`.`" . $data_source . "_case_corporation` ccorp
	ON ggc.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'client'
	
	LEFT OUTER JOIN `" . $data_source . "`.`" . $data_source . "_case_person` cpers
	ON ggc.case_uuid = cpers.case_uuid
	
	WHERE 1
	AND ccorp.case_corporation_id IS NULL
	AND cpers.case_person_id IS NULL
	#AND ggc.cpointer = '3719'
	ORDER BY ggc.cpointer DESC
	#LIMIT 0, 1";
	echo $sql . "\r\n<br>";
	//
	//die();
	$cases = DB::select($sql);
	
	//die(print_r($cases));
	$found = count($cases);
	
	foreach($cases as $case_key=>$case){
		
		echo "Processing -> " . $case_key. " == " . $case->CASENO . " -> ". $case->case_type . "  ";
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$process_start_time = $time;
		
		$case_no = $case->CASENO;
		//insert the case
		$case_uuid = $case->case_uuid;
		$case_number = $case->CASENO;
		
		//insert the injury, if any
		$sql = "SELECT * 
		FROM `" . $data_source . "`.`injury`
		WHERE CASENO = '" . $case_no . "'
		ORDER BY ORDERNO ASC";
		
		echo $sql . "\r\n<br>"; 
		$injuries = DB::select($sql);
		//die(print_r($injuries));
		$blnApplicantAdded = false;
		$blnEmployerAdded = false;
		$blnCarrierOneAdded = false;
		$blnCarrierTwoAdded = false;
		$applicant_name = "";
		$employer_name = "";
		$parent_applicant_uuid = "";
		$applicant_table_uuid = uniqid("DR", false);
		//store the carriers from injury table so we don't enter them twice from parties (card2)
		$arrCarriers = array();
			
		//parties
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
		WHERE acc.CASENO = '" . $case_no . "'
		AND (acc.TYPE = 'APPLICANT' OR acc.TYPE = 'CLIENT')
		ORDER BY acc.CARDCODE";
		
		echo $sql . "\r\n<br>";
		//die();
		$parties = DB::select($sql);
		//die(print_r($parties));
		$arrCpointer = array();
		
		foreach($parties as $key=>$partie) {
			$type = strtolower($partie->partie_type); 
			if ($type=="client") {
				if ($partie->FIRM=="" && $partie->LAST!="") {
					$partie->FIRM = $partie->LAST;
					$partie->LAST = "";
				}
			}
			$table_uuid = uniqid("DR", false);
			$applicant_table_uuid = uniqid("DR", false);
			$parent_table_uuid = uniqid("PD", false);
			$last_updated_date = date("Y-m-d H:i:s");
			$blnRolodex = false;
			//might be an eamsref only
			if ($partie->FIRM == "" && $partie->eams_name!="") {
				$parent_table_uuid = $partie->EAMSREF;
				$blnRolodex = true;
				$partie->FIRM = $partie->eams_name;
				$partie->ADDRESS1 = $partie->eams_street;
				$partie->ADDRESS2 = $partie->eams_suite;
				$partie->CITY = $partie->eams_city;
				$partie->STATE = $partie->eams_state;
				$partie->ZIP = $partie->eams_zip;
			}
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
			$full_name = trim($partie->FIRST);
			if (trim($partie->MIDDLE)!="") {
				$full_name .= " " . trim($partie->MIDDLE);
			}
			$full_name .= " " . trim($partie->LAST);
			
			$arrSet[] = addslashes(trim($full_name));
			$arrSet[] = addslashes(trim($partie->FIRST));
			$arrSet[] = addslashes(trim($partie->LAST));
			$company_name = $partie->FIRM; $arrSet[] = addslashes($company_name);
			
			$arrSet[] = addslashes($type);
			$full_address = $full_address_partie; $arrSet[] = addslashes($full_address);
			$street = $partie->ADDRESS1; $arrSet[] = addslashes($street);
			$city = $partie->CITY; $arrSet[] = addslashes($city);
			$state = $partie->STATE; $arrSet[] = addslashes(substr($state, 0, 2));
			$zip = $partie->ZIP; $zip = substr($zip, 0, 10); $arrSet[] = str_replace("\\", "", $zip);
			$suite = $partie->ADDRESS2; $arrSet[] = addslashes($suite);
			$phone = $partie->PHONE1; $arrSet[] = addslashes($phone);
			$fax = $partie->partie_fax; $arrSet[] = addslashes($fax);
			$email = $partie->EMAIL; $arrSet[] = addslashes($email);
			$employee_phone = $partie->BUSINESS; $arrSet[] = addslashes($employee_phone);
			$employee_fax = $partie->person_fax; $arrSet[] = addslashes($employee_fax);
			$employee_email = $partie->EMAIL; $arrSet[] = addslashes($employee_email);
			$salutation = $partie->SALUTATION; $arrSet[] = addslashes($salutation);
			
			//echo "type:" . $type . "<br>";
			//die();
			//applicant 
			if ($type=="applicant") {
				if ($partie->INTERPRET!="Y") {
					$partie->INTERPRET = "N";
				}
				//need to update interpreter and language
				$sql = "UPDATE `" . $data_source . "`.`" . $data_source . "_case`
				SET `interpreter_needed` = '" . addslashes($partie->INTERPRET) . "',
				`case_language` = '" . addslashes(str_replace("\\", "", $partie->LANGUAGE)) . "'
				WHERE case_uuid = '" . $case_uuid . "'";
				echo $sql . "\r\n<br>";
				$stmt = DB::run($sql);
				
				$full_name = trim($partie->FIRST);
				if (trim($partie->MIDDLE)!="") {
					$full_name .= " " . trim($partie->MIDDLE);
				}
				$full_name .= " " . trim($partie->LAST);
				
				//die("full:" . $full_name);
				if ($partie->SUFFIX!="") {
					$full_name .= ", " . $partie->SUFFIX;
				}
				if ($applicant_name=="") {
					$applicant_name = trim($full_name);
				}
				
				if ($parent_applicant_uuid=="") {
					$sql = "SELECT person_uuid
					FROM `" . $data_source . "`.`" . $data_source . "_person`
					WHERE customer_id = " . $customer_id . "
					AND person_uuid = parent_person_uuid
					AND deleted = 'N'
					AND full_name = '" . addslashes($full_name) . "'
					AND full_address = '" . addslashes($full_address_partie) . "'";
					echo $sql . "\r\n<br>";
					$stmt = DB::run($sql);
					$rolodex = $stmt->fetchObject();
					$blnRolodex = false;
					$parent_table_uuid = "";
					if (is_object($rolodex)) {
						$parent_applicant_uuid = $rolodex->person_uuid;
						$blnRolodex = true;
					}
				}
				$arrSet =  array();
				//$arrSet[] = addslashes($type);
				$full_address = $full_address_partie;
				
				$arrSet[] = addslashes(trim($full_name));
				$arrSet[] = addslashes(trim($partie->FIRST));
				$arrSet[] = addslashes(trim($partie->LAST));
				$arrSet[] = "";
				$arrSet[] = $full_address;
				$street = $partie->ADDRESS1; $arrSet[] = addslashes($street);
				$city = $partie->CITY; $arrSet[] = addslashes($city);
				$state = $partie->STATE; $arrSet[] = addslashes(substr($state, 0, 2));
				$zip = $partie->ZIP; $zip = substr($zip, 0, 10); $arrSet[] = str_replace("\\", "", $zip);
				$suite = $partie->ADDRESS2; $arrSet[] = addslashes($suite);
				$phone = $partie->PHONE1; $arrSet[] = addslashes($phone);
				$fax = $partie->partie_fax; $arrSet[] = addslashes($fax);
				$email = $partie->EMAIL; $arrSet[] = addslashes(str_replace("'", "", $email));
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
				if ($parent_applicant_uuid!="") {
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
					`license_number` = '" . addslashes($partie->LICENSENO) . "',
					`language` = '" . addslashes($partie->LANGUAGE) . "',
					`salutation` = '" . addslashes($partie->SALUTATION) . "'";
					
					$sql = "UPDATE `" . $data_source . "`.`" . $data_source . "_person`
					" . $set . "
					WHERE `parent_person_uuid` = '" . $parent_applicant_uuid . "'";
					
					echo $sql . "\r\n<br>"; 
					//die();
					$stmt = DB::run($sql);					
				} else {
					$parent_applicant_uuid = uniqid("PA", false);
					if (!$blnRolodex) {
						//insert the parent record first
						$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_person` (`person_uuid`, `customer_id`, `parent_person_uuid`, `full_name`, `first_name`, `last_name`, `aka`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `fax`, `email`, `work_phone`, `cell_phone`, `ssn`, `ssn_last_four`, `dob`, `age`, `license_number`, `salutation`, `gender`, `language`, `last_updated_date`, `last_update_user`, `deleted`) 
						VALUES('" . $parent_applicant_uuid . "', '" . $customer_id . "', '" . $parent_applicant_uuid . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N')";
						
						//echo $sql . " << 1 \r\n<br>"; 
						//die();
						$stmt = DB::run($sql);
					}
					$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_person` (`person_uuid`, `customer_id`, `parent_person_uuid`, `full_name`, `first_name`, `last_name`, `aka`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `fax`, `email`, `work_phone`, `cell_phone`, `ssn`, `ssn_last_four`, `dob`, `age`, `license_number`, `salutation`, `gender`, `language`, `last_updated_date`, `last_update_user`, `deleted`) 
					VALUES('" . $table_uuid . "', '" . $customer_id . "', '" . $parent_applicant_uuid . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N')";
					
					echo $sql . "\r\n<br>"; 
					//die();
					$stmt = DB::run($sql);
					
					$case_table_uuid = uniqid("CA", false);
					//attach applicant to kase
					$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_person` (`case_person_uuid`, `case_uuid`, `person_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
					VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', 'main', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
					
					echo $sql . "\r\n<br>"; 
					$stmt = DB::run($sql);
				}
			} 
			if ($type=="client") {
				//parties
				if (!$blnRolodex) {
					//insert the parent record first
					$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation` 
					(`corporation_uuid`, `customer_id`, `full_name`, `first_name`, `last_name`, `company_name`, `type`, `full_address`, 
					`street`, `city`, `state`, `zip`, `suite`, `phone`, `fax`, `email`, 
					`employee_phone`, `employee_fax`, `employee_email`, `salutation`, 
					`last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
					VALUES('" . $parent_table_uuid . "', '" . $customer_id . "', 
					'" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 
					'N', '" . $parent_table_uuid . "','')";
					echo $sql . "\r\n<br>"; 
					//die();		
					$stmt = DB::run($sql);
				}
				//actual record now
				$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation` (`corporation_uuid`, `customer_id`, `full_name`, `first_name`, `last_name`, `company_name`, `type`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `fax`, `email`, `employee_phone`, `employee_fax`, `employee_email`, `salutation`, `last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
				VALUES('" . $table_uuid . "', '" . $customer_id . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N', '" . $parent_table_uuid . "','')";
				echo $sql . "\r\n<br>"; 	
				//die("<br>rol");			
				$stmt = DB::run($sql);
				
				//attach to case
				$case_table_uuid = uniqid("KA", false);
				//now we have to attach the partie to the case 
				$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_corporation` (`case_corporation_uuid`, `case_uuid`, `corporation_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', '" . addslashes($type) . "', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
				echo $sql . "\r\n<br>";   		
				$stmt = DB::run($sql);
			}
		}
	}
	*/
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_corporation` 
	(`case_corporation_uuid`, `case_uuid`, `corporation_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
	SELECT `case_corporation_uuid`, `case_uuid`, `corporation_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id` FROM " . $data_source . "." . $data_source . "_case_corporation 
	WHERE case_uuid NOT IN (SELECT case_uuid FROM `ikase_" . $data_source . "`.`cse_case_corporation`);


	INSERT INTO `ikase_" . $data_source . "`.`cse_case_person` 
	SELECT * 
	FROM " . $data_source . "." . $data_source . "_case_person 
	WHERE case_uuid NOT IN (SELECT case_uuid FROM `ikase_" . $data_source . "`.`cse_case_person`);
	
	
	INSERT INTO `ikase_" . $data_source . "`.`cse_corporation` ( `corporation_uuid`, `parent_corporation_uuid`, `full_name`, `company_name`, `type`, `first_name`, `last_name`, `aka`, `preferred_name`, `employee_phone`, `employee_fax`, `employee_email`, `full_address`, `longitude`, `latitude`, `street`, `city`, `state`, `zip`, `suite`, `company_site`, `phone`, `email`, `fax`, `ssn`, `dob`, `salutation`, `copying_instructions`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id` ) 
	SELECT `corporation_uuid`, `parent_corporation_uuid`, `full_name`, `company_name`, `type`, `first_name`, `last_name`, `aka`, `preferred_name`, `employee_phone`, `employee_fax`, `employee_email`, `full_address`, `longitude`, `latitude`, `street`, `city`, `state`, `zip`, `suite`, `company_site`, `phone`, `email`, `fax`, `ssn`, `dob`, `salutation`, `copying_instructions`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id` 
	FROM " . $data_source . "." . $data_source . "_corporation 
	WHERE corporation_uuid NOT IN (SELECT corporation_uuid FROM `ikase_" . $data_source . "`.`cse_corporation`);
	
	
	INSERT INTO `ikase_" . $data_source . "`.`cse_person`  (`person_uuid`, `parent_person_uuid`, `full_name`, `company_name`, `first_name`, `middle_name`, `last_name`, `aka`, `preferred_name`, `full_address`, `longitude`, `latitude`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `email`, `fax`, `work_phone`, `cell_phone`, `work_email`, `ssn`, `ssn_last_four`, `dob`, `license_number`, `title`, `ref_source`, `salutation`, `age`, `priority_flag`, `gender`, `language`, `birth_state`, `birth_city`, `marital_status`, `legal_status`, `spouse`, `spouse_contact`, `emergency`, `emergency_contact`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id` ) 
	SELECT `person_uuid`, `parent_person_uuid`, `full_name`, `company_name`, `first_name`, `middle_name`, `last_name`, `aka`, `preferred_name`, `full_address`, `longitude`, `latitude`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `email`, `fax`, `work_phone`, `cell_phone`, `work_email`, `ssn`, `ssn_last_four`, `dob`, `license_number`, `title`, `ref_source`, `salutation`, `age`, `priority_flag`, `gender`, `language`, `birth_state`, `birth_city`, `marital_status`, `legal_status`, `spouse`, `spouse_contact`, `emergency`, `emergency_contact`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id` 
	FROM " . $data_source . "." . $data_source . "_person 
	WHERE person_uuid NOT IN (SELECT person_uuid FROM `ikase_" . $data_source . "`.`cse_person`);";
	
	$stmt = DB::run($sql);
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	$success = array("success"=> array("text"=>"done", "duration"=>$total_time));
	echo json_encode($success);
	
	die();
} catch(PDOException $e) {
	echo $sql . "\r\n<br>";
	$error = array("error"=> array("text"=>$e->getMessage()));
	die( json_encode($error));
}

?>
</body>
</html>

<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include("manage_session.php");
set_time_limit(30000);

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;
$last_updated_date = date("Y-m-d H:i:s");

include("connection.php");
		
try {
	$db = getConnection();
	
	include("customer_lookup.php");
	$stmt = null; $db = null;
	$sql = "SELECT `ac`.`CARDCODE`,  `ac`.`FIRMCODE`,  `ac`.`LETSAL`,  
		`ac`.`SALUTATION`,  `ac`.`FIRST`,  `ac`.`MIDDLE`,  `ac`.`LAST`,  `ac`.`SUFFIX`,  `ac`.`SOCIAL_SEC`,  ac.`TYPE` partie_type,`ac`.`TITLE`,  `ac`.`HOME`,  
		`ac`.`BUSINESS`,  `ac`.`FAX` person_fax,  `ac`.`CAR`,  `ac`.`BEEPER`,  `ac`.`EMAIL`,  `ac`.`BIRTH_DATE`,  `ac`.`INTERPRET`,  
		`ac`.`LANGUAGE`,  `ac`.`LICENSENO`,  `ac`.`SPECIALTY`,  `ac`.`MOTHERMAID`,  `ac`.`PROTECTED`,
        `ac2`.`FIRMCODE`,  `ac2`.`FIRM`,  `ac2`.`VENUE`,  `ac2`.`TAX_ID`,  `ac2`.`ADDRESS1`,  `ac2`.`ADDRESS2`,  `ac2`.`CITY`,  `ac2`.`STATE`,  `ac2`.`ZIP`,  
		`ac2`.`PHONE1`,  `ac2`.`PHONE2`,  `ac2`.`FAX` partie_fax,  `ac2`.`FIRMKEY`,  `ac2`.`COLOR`,  `ac2`.`EAMSREF`,
		card3.NAME eams_name, card3.ADDRESS1 eams_street, card3.ADDRESS2 eams_suite, 
		card3.CITY eams_city, card3.STATE eams_state, card3.ZIP eams_zip, card3.PHONE eams_phone
		FROM cse_card ac
        INNER JOIN cse_card2 ac2
		ON ac.FIRMCODE = ac2.FIRMCODE
		LEFT OUTER JOIN cse_casecard acc
		ON ac.CARDCODE = acc.CARDCODE
		LEFT OUTER JOIN cse_card3
		ON ac2.EAMSREF = card3.EAMSREF
		WHERE 1

		AND ac.ikase_uuid = ''
        AND acc.CARDCODE IS NULL
		ORDER BY ac.CARDCODE ASC";
	
	//die($sql);
	//AND ac.LAST = 'Wixen'
	
	$db = getConnection(); 
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$parties = $stmt->fetchAll(PDO::FETCH_OBJ); 
	
	$stmt->closeCursor(); $stmt = null; $db = null;
	//die("FOUND:" . count($parties));
	$arrCpointer = array();
	
	foreach($parties as $key=>$partie){	
		//die(print_r($partie));
		$type = trim(strtolower($partie->partie_type)); 
		$type = str_replace(" ", "_", $type);
		$CARDCODE = $partie->CARDCODE;
		//die($CARDCODE. " - " . $type);
		if ($type=="applicant" || $type=="client" || $type=="mailing_list") {		
			//die(print_r($partie));
			if ($partie->INTERPRET!="Y") {
				$partie->INTERPRET = "N";
			}
			
			$full_name = $partie->FIRST;
			if ($partie->MIDDLE!="") {
				$full_name .= " " . $partie->MIDDLE;
			}
			$full_name .= " " . $partie->LAST;
			if ($partie->SUFFIX!="") {
				$full_name .= ", " . $partie->SUFFIX;
			}
			
			//address
			$full_address_partie = $partie->ADDRESS1;
			if ($partie->ADDRESS2!="") {
				$full_address_partie .= ", " . $partie->ADDRESS2;
			}
			$full_address_partie .= ", " . $partie->CITY;
			$full_address_partie .= ", " . $partie->STATE;
			$full_address_partie .= " " . $partie->ZIP;
			
			$sql = "SELECT person_uuid
			FROM `cse_person`
			WHERE customer_id = " . $customer_id . "
			AND person_uuid = parent_person_uuid
			AND deleted = 'N'
			AND full_name = '" . addslashes($full_name) . "'
			AND full_address = '" . addslashes($full_address_partie) . "'";
			//echo $sql . "\r\n<br>";
			$db = getConnection(); $stmt = $db->prepare($sql);
			$stmt->execute();
			$rolodex = $stmt->fetchObject(); $stmt->closeCursor(); $stmt = null; $db = null;
			$blnRolodex = false;
			$parent_table_uuid = "";
			if (is_object($rolodex)) {
				$parent_applicant_uuid = $rolodex->person_uuid;
				$blnRolodex = true;
				//echo $full_name . " already in<br />";
				
				$sql = "UPDATE cse_card
				SET ikase_table = 'person',
				ikase_uuid = '" . $parent_applicant_uuid . "'
				WHERE `card`.`CARDCODE` = '" . $CARDCODE . "'";
				
				//echo $sql . "\r\n<br>"; 		
				//die();
				$db = getConnection(); $stmt = $db->prepare($sql);  
				$stmt->execute();
				$stmt = null; $db = null;
				
				//skip, it's already in
				continue;
			}
			$arrSet = array();
			$parent_applicant_uuid = uniqid("PA", false);
			
			$sql = "UPDATE cse_card
			SET ikase_table = 'person',
			ikase_uuid = '" . $parent_applicant_uuid . "'
			WHERE `card`.`CARDCODE` = '" . $CARDCODE . "'";
			
			//echo $sql . "\r\n<br>"; 		
			//die();
			$db = getConnection(); $stmt = $db->prepare($sql);  
			$stmt->execute();
			$stmt = null; $db = null;
			
			//$arrSet[] = addslashes($type);
			$full_address = $full_address_partie;
			
			$arrSet[] = addslashes($full_name);
			$arrSet[] = addslashes($partie->FIRST);
			$arrSet[] = addslashes($partie->LAST);
			$arrSet[] = "";
			$arrSet[] = $full_address;
			$street = $partie->ADDRESS1; $arrSet[] = addslashes($street);
			$city = $partie->CITY; $arrSet[] = addslashes($city);
			$state = $partie->STATE; $arrSet[] = addslashes(substr($state, 0, 2));
			$zip = $partie->ZIP; $zip = substr($zip, 0, 10); $arrSet[] = str_replace("\\", "", $zip);
			$suite = $partie->ADDRESS2; $arrSet[] = addslashes($suite);
			
			$phone = $partie->PHONE1; $arrSet[] = addslashes($phone);
			$email = $partie->EMAIL; $arrSet[] = $email;
			$fax = $partie->partie_fax; $arrSet[] = addslashes($fax);
			$employee_phone = $partie->BUSINESS; $arrSet[] = addslashes($employee_phone);
			$employee_cellphone = $partie->HOME; $arrSet[] = addslashes($employee_cellphone);
			
			$partie->SOCIAL_SEC = str_replace("-", "", $partie->SOCIAL_SEC);
			$arrSet[] = $partie->SOCIAL_SEC;
			$arrSet[] = substr($partie->SOCIAL_SEC, strlen($partie->SOCIAL_SEC) - 4, 4);
			$dob = $partie->BIRTH_DATE;
			//die(print_r($arrSet));
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
			
			//insert the parent record first
			$sql = "INSERT INTO `cse_person` (
			`person_uuid`, `customer_id`, `parent_person_uuid`, `full_name`, `first_name`, `last_name`, `aka`, 
			`full_address`, `street`, `city`, `state`, `zip`, `suite`, 
			`phone`, `email`, `fax`, `work_phone`, `cell_phone`, 
			`ssn`, `ssn_last_four`, `dob`, `age`, `license_number`, `salutation`, `gender`, `language`, 
			`last_updated_date`, `last_update_user`, `deleted`) 
			VALUES('" . $parent_applicant_uuid . "', '" . $customer_id . "', '" . $parent_applicant_uuid . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'import', 'N')";
			
			//echo $sql . "\r\n<br>"; 		
			//die();
			$db = getConnection(); $stmt = $db->prepare($sql);  
			$stmt->execute();

		} else {
			$table_uuid = uniqid("DR", false);
			$parent_table_uuid = uniqid("PD", false);
			$last_updated_date = date("Y-m-d H:i:s");
			
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
			$full_name = $partie->FIRST;
			if ($partie->MIDDLE!="") {
				$full_name .= " " . $partie->MIDDLE;
			}
			$full_name .= " " . $partie->LAST;
			
			//look up in case already in
			$sql = "SELECT corporation_uuid
			FROM `cse_corporation`
			WHERE customer_id = " . $customer_id . "
			AND corporation_uuid = parent_corporation_uuid
			AND type = '" . addslashes(strtolower($partie->partie_type)) . "'
			AND deleted = 'N'
			AND company_name = '" . addslashes($partie->FIRM) . "'
			AND full_address = '" . addslashes($full_address_partie) . "'";
			//echo $sql . "\r\n<br>";
			$db = getConnection(); $stmt = $db->prepare($sql);
			$stmt->execute();
			$rolodex = $stmt->fetchObject(); $stmt->closeCursor(); $stmt = null; $db = null;
			$blnRolodex = false;
			if (is_object($rolodex)) {
				$parent_table_uuid = $rolodex->corporation_uuid;
				$blnRolodex = true;
			}
			
			//might be an eamsref only
			if ($partie->FIRM == "" && $partie->eams_name!="") {
				$parent_table_uuid = $partie->EAMSREF;
				$blnRolodex = true;
			}
			if ($blnRolodex) {
				//skip, it's already in
				//echo $full_name . " C already in<br />";
				
				$sql = "UPDATE cse_card
				SET ikase_table = 'corporation',
				ikase_uuid = '" . $parent_table_uuid . "'
				WHERE `card`.`CARDCODE` = '" . $CARDCODE . "'";
				
				//echo $sql . "\r\n<br>"; 		
				//die();
				$db = getConnection(); $stmt = $db->prepare($sql);  
				$stmt->execute();
				$stmt = null; $db = null;
				
				continue;
			}
			
			
			$sql = "UPDATE cse_card
			SET ikase_table = 'corporation',
			ikase_uuid = '" . $parent_table_uuid . "'
			WHERE `card`.`CARDCODE` = '" . $CARDCODE . "'";
			
			//echo $sql . "\r\n<br>"; 		
			//die();
			$db = getConnection(); $stmt = $db->prepare($sql);  
			$stmt->execute();
			$stmt = null; $db = null;
			
			$arrSet[] = addslashes($full_name);
			$arrSet[] = addslashes($partie->FIRST);
			$arrSet[] = addslashes($partie->LAST);
			$company_name = $partie->FIRM; $arrSet[] = addslashes($company_name);
			/*
			if ($partie->partie_type=="ATTORNEY") {
				$partie->partie_type = "DEFENSE";
			}
			*/
			
			//if blnContinue is true, we will skip this partie
			$blnContinue = false;
			switch($type){
				case "court":
					$type = "venue";
					break;
				case "DR":
					$type = "medical_provider";
					break;
				case "insurance":
					$type = "carrier";
					break;
			}
			
			//medical providers
			if (strpos($type, "dr") !== false) {
				$type = "medical_provider";
				//die(print_r($partie));
			}
			if ($blnContinue) {
				continue;
			}
			$arrSet[] = addslashes($type);
			$full_address = $full_address_partie; $arrSet[] = addslashes($full_address);
			$street = $partie->ADDRESS1; $arrSet[] = addslashes($street);
			$city = $partie->CITY; $arrSet[] = addslashes($city);
			$state = $partie->STATE; $arrSet[] = addslashes(substr($state, 0, 2));
			$zip = $partie->ZIP; $zip = substr($zip, 0, 10); $arrSet[] = str_replace("\\", "", $zip);
			$suite = $partie->ADDRESS2; $arrSet[] = addslashes($suite);
			//$phone = $partie->PHONE1; $arrSet[] = addslashes($phone);
			$phone = $partie->PHONE1;
			if ($phone=="") {
				$phone = $partie->HOME; 
			}
			$arrSet[] = addslashes($phone);
			$fax = $partie->partie_fax; $arrSet[] = addslashes($fax);
			$email = $partie->EMAIL; $arrSet[] = addslashes($email);
			$employee_phone = $partie->BUSINESS; $arrSet[] = addslashes($employee_phone);
			$employee_fax = $partie->person_fax; $arrSet[] = addslashes($employee_fax);
			$employee_cell = $partie->CAR; $arrSet[] = addslashes($employee_cell);
			$employee_email = $partie->EMAIL; $arrSet[] = addslashes($employee_email);
			$salutation = $partie->SALUTATION; $arrSet[] = addslashes($salutation);
			
			if ($type=="venue") {
				continue;
			}
			
			//insert the parent record first
			$sql = "INSERT INTO `cse_corporation` 
			(`corporation_uuid`, `customer_id`, `full_name`, `first_name`, `last_name`, `company_name`, `type`, `full_address`, 
			`street`, `city`, `state`, `zip`, `suite`, `phone`, `fax`, `email`, 
			`employee_phone`, `employee_fax`, `employee_cell`, `employee_email`, `salutation`, 
			`last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
			VALUES('" . $parent_table_uuid . "', '" . $customer_id . "', 
			'" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'import', 
			'N', '" . $parent_table_uuid . "','')";
			//echo $sql . "\r\n<br>"; 		
			//die();
			$db = getConnection(); 
			$stmt = $db->prepare($sql); 
			$stmt->execute();
			$stmt = null; $db = null;
		}
	}
	echo "done at " . date("H:i:s");
} catch(PDOException $e) {
	echo $sql . "\r\n<br>";
	$error = array("error"=> array("text"=>$e->getMessage()));
	die( json_encode($error));
}
?>
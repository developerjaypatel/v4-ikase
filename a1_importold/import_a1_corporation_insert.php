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
	$data_source = str_replace("2", "", $data_source);
	
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
	
	$sql = "SELECT ggc.case_number case_number, ggc.case_uuid
	FROM `cse_case` gcase
	LEFT OUTER JOIN `cse_case` ggc
	ON gcase.case_number = ggc.cpointer
	LEFT OUTER JOIN `cse_case_corporation` ccorp
	ON ggc.case_uuid = ccorp.case_uuid
	
	WHERE 1
	AND ccorp.case_corporation_id IS NULL
	ORDER BY ggc.case_number DESC";
	echo $sql . "\r\n<br>";
	//
	//die();
	$db = getConnection(); $stmt = $db->prepare($sql);
	$stmt->execute();
	$cases = $stmt->fetchAll(PDO::FETCH_OBJ); $stmt->closeCursor(); $stmt = null; $db = null;
	
	//die(print_r($cases));
	$found = count($cases);
	
	foreach($cases as $case_key=>$case){
		
		echo "Processing -> " . $case_key. " == " . $case->case_number . "  ";
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$process_start_time = $time;
		
		$case_no = $case->case_number;
		//insert the case
		$case_uuid = $case->case_uuid;
		$case_number = $case->case_number;
		
		//insert the injury, if any
		$sql = "SELECT * 
		FROM `cse_injury`
		WHERE case_number = '" . $case_no . "'
		ORDER BY ORDERNO ASC";
		
		echo $sql . "\r\n<br>"; 
		$db = getConnection(); $stmt = $db->prepare($sql);
		$stmt->execute();
		$injuries = $stmt->fetchAll(PDO::FETCH_OBJ); $stmt->closeCursor(); $stmt = null; $db = null;
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
		$sql = "SELECT acc.case_number, acc.CARDCODE, acc.TYPE partie_type,  `ac`.`CARDCODE`,  `ac`.`FIRMCODE`,  `ac`.`LETSAL`,  
		`ac`.`SALUTATION`,  `ac`.`FIRST`,  `ac`.`MIDDLE`,  `ac`.`LAST`,  `ac`.`SUFFIX`,  `ac`.`SOCIAL_SEC`,  `ac`.`TITLE`,  `ac`.`HOME`,  
		`ac`.`BUSINESS`,  `ac`.`FAX` person_fax,  `ac`.`CAR`,  `ac`.`BEEPER`,  `ac`.`EMAIL`,  `ac`.`BIRTH_DATE`,  `ac`.`INTERPRET`,  
		`ac`.`LANGUAGE`,  `ac`.`LICENSENO`,  `ac`.`SPECIALTY`,  `ac`.`MOTHERMAID`,  `ac`.`PROTECTED`,
		`ac2`.`FIRMCODE`,  `ac2`.`FIRM`,  `ac2`.`VENUE`,  `ac2`.`TAX_ID`,  `ac2`.`ADDRESS1`,  `ac2`.`ADDRESS2`,  `ac2`.`CITY`,  `ac2`.`STATE`,  `ac2`.`ZIP`,  
		`ac2`.`PHONE1`,  `ac2`.`PHONE2`,  `ac2`.`FAX` partie_fax,  `ac2`.`FIRMKEY`,  `ac2`.`COLOR`,  `ac2`.`EAMSREF`,
		card3.NAME eams_name, card3.ADDRESS1 eams_street, card3.ADDRESS2 eams_suite, 
		card3.CITY eams_city, card3.STATE eams_state, card3.ZIP eams_zip, card3.PHONE eams_phone
		FROM cse_casecard acc
		INNER JOIN cse_card ac
		ON acc.CARDCODE = ac.CARDCODE
		INNER JOIN cse_card2 ac2
		ON ac.FIRMCODE = ac2.FIRMCODE
		LEFT OUTER JOIN cse_card3
		ON ac2.EAMSREF = card3.EAMSREF
		WHERE acc.case_number = '" . $case_no . "'
		AND acc.TYPE != 'APPLICANT'
		AND acc.TYPE != 'CLIENT'
		ORDER BY acc.CARDCODE";
		
		echo $sql . "\r\n<br>";
		//die();
		$db = getConnection(); $stmt = $db->prepare($sql);
		$stmt->execute();
		$parties = $stmt->fetchAll(PDO::FETCH_OBJ); $stmt->closeCursor(); $stmt = null; $db = null;
		//die(print_r($parties));
		$arrCpointer = array();
		
		foreach($parties as $key=>$partie) {
			if ($partie->FIRM=="" && $partie->LAST!="") {
				$partie->FIRM = $partie->LAST;
				$partie->LAST = "";
			}
			$table_uuid = uniqid("DR", false);
			$parent_table_uuid = uniqid("PD", false);
			$last_updated_date = date("Y-m-d H:i:s");
			$blnRolodex = false;
			//might be an eamsref only
			if ($partie->FIRM == "") {
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
			$full_name = $partie->FIRST;
			if ($partie->MIDDLE!="") {
				$full_name .= " " . $partie->MIDDLE;
			}
			$full_name .= " " . $partie->LAST;
			
			$arrSet[] = addslashes($full_name);
			$arrSet[] = addslashes($partie->FIRST);
			$arrSet[] = addslashes($partie->LAST);
			$company_name = $partie->FIRM; $arrSet[] = addslashes($company_name);
			$type = strtolower($partie->partie_type); 
			//if blnContinue is true, we will skip this partie
			$blnContinue = false;
			switch($type){
				case "employer":
					if ($employer_name=="") {
						$employer_name = " vs " . $company_name;
					}
					break;
				case "court":
					$type = "venue";
					break;
				case "insurance":
					//let's make sure not in already through injury table
					foreach($arrCarriers as $carrier) {
						$carrier_name = $carrier["name"];
						$carrier_address = $carrier["address"];
						
						if ($carrier_name==$company_name && $carrier_address == $partial_address) {
							$blnContinue = true;
							break;
						}
					}
					$type = "carrier";
					break;
			}
			
			//medical providers
			if (strpos($type, "dr") !== false) {
				$type = "medical_provider";
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
			$phone = $partie->PHONE1; $arrSet[] = addslashes($phone);
			$fax = $partie->partie_fax; $arrSet[] = addslashes($fax);
			$email = $partie->EMAIL; $arrSet[] = addslashes($email);
			$employee_phone = $partie->BUSINESS; $arrSet[] = addslashes($employee_phone);
			$employee_fax = $partie->person_fax; $arrSet[] = addslashes($employee_fax);
			$employee_email = $partie->EMAIL; $arrSet[] = addslashes($employee_email);
			$salutation = $partie->SALUTATION; $arrSet[] = addslashes($salutation);
			
			if (!$blnRolodex) {
				
				if ($type!="venue" && $type!="applicant" && $type!="client") {
					//look up in case already in
					$sql = "SELECT corporation_uuid
					FROM `cse_corporation`
					WHERE customer_id = " . $customer_id . "
					AND corporation_uuid = parent_corporation_uuid
					AND type = '" . addslashes(strtolower($partie->partie_type)) . "'
					AND deleted = 'N'
					AND company_name = '" . addslashes($partie->FIRM) . "'
					AND full_address = '" . addslashes($full_address_partie) . "'";
					echo $sql . "\r\n<br>";
					$db = getConnection(); $stmt = $db->prepare($sql);
					$stmt->execute();
					$rolodex = $stmt->fetchObject(); $stmt->closeCursor(); $stmt = null; $db = null;
					if (is_object($rolodex)) {
						//die(print_r($rolodex));
						$parent_table_uuid = $rolodex->corporation_uuid;
						$blnRolodex = true;
					}
				}
			}
			
			if ($type=="venue") {
				$venue_abbr = $partie->VENUE;
				$parent_table_uuid = array_search($venue_abbr, $arrVenues);
				$blnRolodex = true;
				
				$sql = "UPDATE `cse_case`
				SET `venue` = '" . $parent_table_uuid . "'
				WHERE case_uuid = '" . $case_uuid . "'";
				echo $sql . "\r\n<br>";
				$db = getConnection(); $stmt = $db->prepare($sql);
				$stmt->execute();
				$stmt = null; $db = null;
			}
			
			if ($type=="applicant" || $type=="client") {
				continue;
			} else {
				//parties
				if (!$blnRolodex) {
					//insert the parent record first
					$sql = "INSERT INTO `cse_corporation` 
					(`corporation_uuid`, `customer_id`, `full_name`, `first_name`, `last_name`, `company_name`, `type`, `full_address`, 
					`street`, `city`, `state`, `zip`, `suite`, `phone`, `fax`, `email`, 
					`employee_phone`, `employee_fax`, `employee_email`, `salutation`, 
					`last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
					VALUES('" . $parent_table_uuid . "', '" . $customer_id . "', 
					'" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 
					'N', '" . $parent_table_uuid . "','')";
					echo $sql . "\r\n<br>"; 
					//die("<br>no rol");		
					$db = getConnection(); $stmt = $db->prepare($sql); 
					$stmt->execute();
					$stmt = null; $db = null;
				}
				//actual record now
				$sql = "INSERT INTO `cse_corporation` (`corporation_uuid`, `customer_id`, `full_name`, `first_name`, `last_name`, `company_name`, `type`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `fax`, `email`, `employee_phone`, `employee_fax`, `employee_email`, `salutation`, `last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
				VALUES('" . $table_uuid . "', '" . $customer_id . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N', '" . $parent_table_uuid . "','')";
				echo $sql . "\r\n<br>"; 	
				//die("<br>rol");			
				$db = getConnection(); $stmt = $db->prepare($sql); 
				$stmt->execute();
				$stmt = null; $db = null;
				
				//attach to case
				$case_table_uuid = uniqid("KA", false);
				//now we have to attach the partie to the case 
				$sql = "INSERT INTO `cse_case_corporation` (`case_corporation_uuid`, `case_uuid`, `corporation_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', '" . addslashes($type) . "', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
				echo $sql . "\r\n<br>";   		
				$db = getConnection(); $stmt = $db->prepare($sql);
				$stmt->execute();
				$stmt = null; $db = null;
			}
		}
	}
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	$success = array("success"=> array("text"=>"done", "duration"=>$total_time));
	//echo json_encode($success);
	//die();
	
	//completeds
	$sql = "SELECT COUNT(DISTINCT case_uuid) case_count
	FROM `cse_case_corporation` ggc
	WHERE 1";
	echo $sql . "\r\n<br>";
	//die();
	$db = getConnection(); $stmt = $db->prepare($sql);
	$stmt->execute();
	$cases = $stmt->fetchObject(); $stmt->closeCursor(); $stmt = null; $db = null;
	
	$case_count = $cases->case_count;
	
	$sql = "SELECT DISTINCT gcc.case_uuid
	FROM `cse_case` ggc
	LEFT OUTER JOIN `cse_case_corporation` gcc
	ON ggc.case_uuid = gcc.case_uuid
	WHERE 1
	AND gcc.case_corporation_id IS NOT NULL
	ORDER BY ggc.case_number DESC
	LIMIT 0, 1";
	echo $sql . "\r\n<br>";
	//	#AND case_number = 19493 OR case_number = 19490 OR case_number = 19454
	//die();
	$db = getConnection(); $stmt = $db->prepare($sql);
	$stmt->execute();
	$cases = $stmt->fetchAll(PDO::FETCH_OBJ); $stmt->closeCursor(); $stmt = null; $db = null;
	
	$completed_count = count($cases);
	
	if ($case_count > 0) {
		echo "<script language='javascript'>parent.runCorp(" . $completed_count . "," . $case_count . ")</script>";
	} else {
		die("all done");
	}
	$db = null;
} catch(PDOException $e) {
	echo $sql . "\r\n<br>";
	$error = array("error"=> array("text"=>$e->getMessage()));
	die( json_encode($error));
}

?>
</body>
</html>
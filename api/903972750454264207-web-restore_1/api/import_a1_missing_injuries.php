<?php
include("manage_session.php");
session_write_close();
include("connection.php");

$last_updated_date = date("Y-m-d H:i:s");
try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	//$data_source = str_replace("2", "", $data_source);
	
	$sql = "select ccase.case_uuid, ccase.cpointer
	FROM `" . $data_source . "`." . $data_source . "_case ccase
	LEFT OUTER JOIN `" . $data_source . "`." . $data_source . "_case_injury cci
	ON ccase.case_uuid = cci.case_uuid
	WHERE cci.injury_uuid IS NULL
	ORDER BY ccase.cpointer 
	#LIMIT 0, 1";
	$db = getConnection(); $stmt = $db->prepare($sql);
	$stmt->execute();
	$cases = $stmt->fetchAll(PDO::FETCH_OBJ); $stmt->closeCursor(); $stmt = null; $db = null;
	
	//die(print_r($cases));
	$found = count($cases);
	
	foreach($cases as $case_key=>$case) {
		$case_uuid = $case->case_uuid;
		
		//see if there is injury data
		//insert the injury, if any
		$sql = "SELECT * 
		FROM `" . $data_source . "`.`injury`
		WHERE CASENO = " . $case->cpointer . "
		ORDER BY ORDERNO ASC";
		
		echo $sql . "\r\n<br>"; 
		$db = getConnection(); $stmt = $db->prepare($sql);
		$stmt->execute();
		$injuries = $stmt->fetchAll(PDO::FETCH_OBJ); $stmt->closeCursor(); $stmt = null; $db = null;
		//die(print_r($injuries));
		if (count($injuries)==0) {
			//create a blank injury
			$injury_uuid = uniqid("KI", false);
			$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_injury` 
			(`injury_uuid`, `injury_number`, `adj_number`, `start_date`, `end_date`, `type`, `occupation`, `body_parts`, `ct_dates_note`,
			`full_address`, `street`, `suite`, `city`, `state`, `zip`, `customer_id`, `explanation`, `deleted`)
			VALUES ('" . $injury_uuid . "', 1, '', '0000-00-00', '0000-00-00', '', '','','','', '','','', '', '', " . $customer_id . ", '', 'N')";
			echo $sql . "\r\n<br>"; 
			//die();
			
			$db = getConnection(); $stmt = $db->prepare($sql); 
			$stmt->execute(); $stmt = null; $db = null;
			
			//attach it to the case
			$case_table_uuid = uniqid("KA", false);
			$attribute_1 = "main";
			
			//now we have to attach the injury to the case 
			$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_injury` (`case_injury_uuid`, `case_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $injury_uuid . "', 'main', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
	
			echo $sql . "\r\n<br>";  
			$db = getConnection(); $stmt = $db->prepare($sql); 
			$stmt->execute(); $stmt = null; $db = null;
		} else {
			//we have injury data
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
			//die(print_r($injuries));
			foreach($injuries as $injury_index=>$injury) {		
				$applicant_name = "";
				$employer_name = "";
				$parent_applicant_uuid = "";
				
				if (!$blnApplicantAdded) {				
					if (is_object($injury)) {
						//echo "\r\n" . $injury->DOI . "\r\n<br>";
						//die(print_r($injury));
						
						$arrSet = array();
						$full_name = $injury->FIRST;
						$full_name .= " " . $injury->LAST;
						
						if ($applicant_name=="") {
							$applicant_name = $full_name;
						}
						
						$full_address = "";
						if ($injury->ADDRESS!="") {
							$full_address = $injury->ADDRESS;
						}
						if ($injury->CITY!="") {
							$full_address .= ", " . $injury->CITY;
						}
						if ($injury->STATE!="") {
							$full_address .= ", " . $injury->STATE;
						}
						if ($injury->ZIP_CODE!="") {
							$full_address .= " " . $injury->ZIP_CODE;
						}
						
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
						$blnRolodex = false;
						if (is_object($rolodex)) {
							$parent_applicant_uuid = $rolodex->person_uuid;
							$blnRolodex = true;
						} else {
							$parent_applicant_uuid = uniqid("PA", false);
						}
						
						$arrSet[] = addslashes($full_name);
						$arrSet[] = addslashes($injury->FIRST);
						$arrSet[] = addslashes($injury->LAST);
						$arrSet[] = "";
						$arrSet[] = addslashes($full_address);
						$street = $injury->ADDRESS; $arrSet[] = addslashes($street);
						$city = $injury->CITY; $arrSet[] = addslashes($city);
						$state = $injury->STATE; $arrSet[] = addslashes(substr($state, 0, 2));
						$zip = $injury->ZIP_CODE; $zip = substr($zip, 0, 10); $arrSet[] = str_replace("\\", "", $zip);
						$injury->SOCIAL_SEC = str_replace("-", "", $injury->SOCIAL_SEC);
						$arrSet[] = $injury->SOCIAL_SEC;
						$arrSet[] = substr($injury->SOCIAL_SEC, strlen($injury->SOCIAL_SEC) - 4, 4);
						
						$arrSet[] = $injury->E_PHONE;
						$arrSet[] = $injury->E_FAX;
						
						if (!$blnRolodex) {
							//insert the parent record first
							$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_person` 
							(`person_uuid`, `customer_id`, `parent_person_uuid`, `full_name`, `first_name`, `last_name`, `aka`, `full_address`, `street`, `city`, `state`, `zip`, `ssn`, `ssn_last_four`, `phone`, `fax`, `last_updated_date`, `last_update_user`, `deleted`) 
							VALUES('" . $parent_applicant_uuid . "', '" . $customer_id . "', '" . $parent_applicant_uuid . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N')";
							
							echo $sql . "\r\n<br>"; 
							$db = getConnection(); $stmt = $db->prepare($sql);  
							$stmt->execute(); $stmt = null; $db = null;
						}
						$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_person` 
						(`person_uuid`, `customer_id`, `parent_person_uuid`, `full_name`, `first_name`, `last_name`, `aka`, `full_address`, `street`, `city`, `state`, `zip`, `ssn`, `ssn_last_four`, `phone`, `fax`, `last_updated_date`, `last_update_user`, `deleted`) 
						VALUES('" . $applicant_table_uuid . "', '" . $customer_id . "', '" . $parent_applicant_uuid . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N')";
						
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
					}
				}
				$employer_name = " vs " . $injury->E_NAME;
				
				$injury_uuid = uniqid("KI", false);
				echo $employer_name . "<br>";
				//die(print_r($injury));
				//doi dates
				if ($injury->DOI=="") {
					$injury->DOI = "0000-00-00";
				} else {
					$injury->DOI = date("Y-m-d", strtotime($injury->DOI)); 
				}
				if ($injury->DOI2=="") {
					$injury->DOI2 = "0000-00-00";
				} else {
					$injury->DOI2 = date("Y-m-d", strtotime($injury->DOI2)); 
				}
				$arrPOB = array();
				if ($injury->POB1!="") {
					$arrPOB[] = $injury->POB1;
				}
				if ($injury->POB2!="") {
					$arrPOB[] = $injury->POB2;
				}
				if ($injury->POB3!="") {
					$arrPOB[] = $injury->POB3;
				}
				if ($injury->POB4!="") {
					$arrPOB[] = $injury->POB4;
				}
				if ($injury->POB5!="") {
					$arrPOB[] = $injury->POB5;
				}
				$body_parts = implode("; ", $arrPOB);
				$full_address = "";
				if ($injury->ADJ1D!="") {
					$full_address = $injury->ADJ1D;
				}
				if ($injury->ADJ1D2!="") {
					$full_address .=  ", " . $injury->ADJ1D2;
				}
				if ($injury->ADJ1D3!="") {
					$full_address .=  ", " . $injury->ADJ1D3;
				}
				if ($injury->ADJ1D4!="") {
					$full_address .=  ", " . $injury->ADJ1D4;
				}
				//echo "full_address:" . $full_address . "<br />";
				$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_injury` 
				(`injury_uuid`, `injury_number`, `adj_number`, `start_date`, `end_date`, `type`, `occupation`, `body_parts`, `ct_dates_note`,
				`full_address`, `street`, `suite`, `city`, `state`, `zip`, `customer_id`, `explanation`, `deleted`)
				VALUES ('" . $injury_uuid . "', " . ($injury_index + 1) . ", '" . addslashes($injury->CASE_NO) . "', '" . $injury->DOI . "', '" . $injury->DOI2 . "', '', '" . addslashes($injury->ADJ1B) . "','" . $body_parts . "','" . addslashes($injury->ADJ1C) . "','" . 
				addslashes($full_address) . "', '" . addslashes($injury->ADJ1D) . "','','" . addslashes($injury->ADJ1D2) . "', '" . $injury->ADJ1D3 . "', '" . $injury->ADJ1D4 . "', " . $customer_id . ", '" . addslashes($injury->ADJ1E) . "', 'N')";
				echo $sql . "\r\n<br>"; 
				//die();
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
					
				//if (!$blnCarrierOneAdded) {
					//carrier here, BECAUSE of adjuster and claim no
					if ($injury->I_NAME!="") {
						$carrier_address = $injury->I_ADDRESS . ", " . $injury->I_CITY . ", " . $injury->I_STATE . " " . $injury->I_ZIP;
						//keep track to exclude this one from parties later
						$arrCarriers[] = array("name"=>$injury->I_NAME, "address"=>$carrier_address);
						
						$table_uuid = uniqid("DR", false);
						$parent_table_uuid = uniqid("PD", false);
						$last_updated_date = date("Y-m-d H:i:s");
						
						$arrSet = array();
						$full_name = $injury->I_ADJFST . " " . $injury->I_ADJUSTER; $arrSet[] = addslashes($full_name);
						$company_name = $injury->I_NAME; $arrSet[] = addslashes($company_name);
						$type = "carrier";
						$arrSet[] = $type;
						$full_address = $carrier_address; $arrSet[] = addslashes($carrier_address);
						$street = $injury->I_ADDRESS; $arrSet[] = addslashes($street);
						$city = $injury->I_CITY; $arrSet[] = addslashes($city);
						$state = $injury->I_STATE; $arrSet[] = addslashes(substr($state, 0, 2));
						$zip = $injury->I_ZIP; $zip = substr($zip, 0, 10); $arrSet[] = str_replace("\\", "", $zip);
						$phone = $injury->I_PHONE; $arrSet[] = addslashes($phone);
						$fax = $injury->I_FAX; $arrSet[] = addslashes($fax);
						$salutation = $injury->I_ADJSAL; $arrSet[] = addslashes($salutation);
						
						//look up in case already in
						$sql = "SELECT corporation_uuid
						FROM `" . $data_source . "`.`" . $data_source . "_corporation`
						WHERE customer_id = " . $customer_id . "
						AND corporation_uuid = parent_corporation_uuid
						AND type = 'carrier'
						AND deleted = 'N'
						AND company_name = '" . addslashes($injury->I_NAME) . "'
						AND full_address = '" . addslashes($carrier_address) . "'";
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
							(`corporation_uuid`, `customer_id`, `full_name`, `company_name`, `type`, `full_address`, 
							`street`, `city`, `state`, `zip`, `phone`, `fax`, `salutation`, 
							`last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
							VALUES('" . $parent_table_uuid . "', '" . $customer_id . "', 
							'" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 
							'N', '" . $parent_table_uuid . "','')";
							echo $sql . "\r\n<br>"; 		
							$db = getConnection(); $stmt = $db->prepare($sql); 
							$stmt->execute(); $stmt = null; $db = null;
						}
						//actual record now
						$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation` 
						(`corporation_uuid`, `customer_id`, `full_name`, `company_name`, `type`, `full_address`, 
							`street`, `city`, `state`, `zip`, `phone`, `fax`, `salutation`,  
							`last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
						VALUES('" . $table_uuid . "', '" . $customer_id . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N', '" . $parent_table_uuid . "','')";
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
						
						//die("clim:" . $injury->I_CLAIMNO);
						if ($injury->I_CLAIMNO!="") {
							//add as adhoc
							$arrAdhocSet = array();
							if ($injury->I_CLAIMNO!="") {
								$adhoc_uuid = uniqid("CN", false);
								$arrAdhocSet[] = "'" . $adhoc_uuid . "','" . $case_uuid . "','" . $table_uuid . "','claim_number','" . addslashes($injury->I_CLAIMNO) . "'";
							}
							//die(print_r($arrAdhocSet));
							//add these values as adhoc for the carrier
							$adhoc_where_clause = "`corporation_uuid` = '" . $table_uuid . "'";
							//do we have adhocs
							if (count($arrAdhocSet)>0) {
								//inserts
								$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation_adhoc` (`adhoc_uuid`, `case_uuid`, `corporation_uuid`, `adhoc`, `adhoc_value`, `customer_id`) VALUES ";
								$arrValues = array();
								foreach($arrAdhocSet as $adhoc_set) {		
									$arrValues[] = "(" . $adhoc_set . ", '" . $customer_id . "')"; 
								}
								$sql .= implode(",\r\n<br>", $arrValues);
								echo $sql . "\r\n<br>";
								$db = getConnection(); $stmt = $db->prepare($sql);  
								$stmt->execute(); $stmt = null; $db = null;
								//$track_adhock_id = $db->lastInsertId();
								//trackAdhoc("insert", $track_adhock_id);
							}
						}
					}
					$blnCarrierOneAdded = true;
				//}
				//if (!$blnCarrierTwoAdded) {
					if ($injury->I2_NAME!="") {
						$carrier_address = "";
						if ($injury->I2_ADDRESS != "") {
							$carrier_address .= $injury->I2_ADDRESS;
						}
						if ($injury->I2_CITY != "") {
							$carrier_address .= ", " . $injury->I2_CITY;
						}
						if ($injury->I2_STATE != "") {
							$carrier_address .= ", " . $injury->I2_STATE;
						}
						if ($injury->I2_ZIP != "") {
							$carrier_address .= ", " . $injury->I2_ZIP;
						}
						//keep track to exclude this one from parties later
						$arrCarriers[] = array("name"=>$injury->I2_NAME, "address"=>$carrier_address);
						
						$table_uuid = uniqid("DR", false);
						$parent_table_uuid = uniqid("PD", false);
						$last_updated_date = date("Y-m-d H:i:s");
						
						$arrSet = array();
						$full_name = $injury->I2_ADJFST . " " . $injury->I2_ADJUSTE; $arrSet[] = addslashes($full_name);
						$company_name = $injury->I2_NAME; $arrSet[] = addslashes($company_name);
						$type = "carrier";
						$arrSet[] = $type;
						$arrSet[] = addslashes($carrier_address);
						$street = $injury->I2_ADDRESS; $arrSet[] = addslashes($street);
						$city = $injury->I2_CITY; $arrSet[] = addslashes($city);
						$state = $injury->I2_STATE; $arrSet[] = addslashes(substr($state, 0, 2));
						$zip = $injury->I2_ZIP; $zip = substr($zip, 0, 10); $arrSet[] = str_replace("\\", "", $zip);
						$phone = $injury->I2_PHONE; $arrSet[] = addslashes($phone);
						$fax = $injury->I2_FAX; $arrSet[] = addslashes($fax);
						$salutation = $injury->I2_ADJSAL; $arrSet[] = addslashes($salutation);
						//look up in case already in
						$sql = "SELECT corporation_uuid
						FROM `" . $data_source . "`.`" . $data_source . "_corporation`
						WHERE customer_id = " . $customer_id . "
						AND corporation_uuid = parent_corporation_uuid
						AND type = 'carrier'
						AND deleted = 'N'
						AND company_name = '" . addslashes($injury->I2_NAME) . "'
						AND full_address = '" . addslashes($carrier_address) . "'";
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
							(`corporation_uuid`, `customer_id`, `full_name`, `company_name`, `type`, `full_address`, 
							`street`, `city`, `state`, `zip`, `phone`, `fax`, `salutation`, 
							`last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
							VALUES('" . $parent_table_uuid . "', '" . $customer_id . "', 
							'" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 
							'N', '" . $parent_table_uuid . "','')";
							echo $sql . "\r\n<br>"; 		
							$db = getConnection(); $stmt = $db->prepare($sql); 
							$stmt->execute(); $stmt = null; $db = null;
						}
						//actual record now
						$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation` 
						(`corporation_uuid`, `customer_id`, `full_name`, `company_name`, `type`, `full_address`, 
							`street`, `city`, `state`, `zip`, `phone`, `fax`, `salutation`,  
							`last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
						VALUES('" . $table_uuid . "', '" . $customer_id . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N', '" . $parent_table_uuid . "','')";
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
						
						echo $sql . "\r\n<br>";   		
						//$stmt = $db->prepare($sql);
						//$stmt->execute();
						
						if ($injury->I2_CLAIMNO!="") {
							//add as adhoc
							$arrAdhocSet = array();
							if ($injury->I2_CLAIMNO!="") {
								$adhoc_uuid = uniqid("CN", false);
								$arrAdhocSet[] = "'" . $adhoc_uuid . "','" . $case_uuid . "','" . $table_uuid . "','claim_number','" . addslashes($injury->I2_CLAIMNO) . "'";
							}
							//add these values as adhoc for the carrier
							$adhoc_where_clause = "`corporation_uuid` = '" . $table_uuid . "'";
							//do we have adhocs
							if (count($arrAdhocSet)>0) {
								//inserts
								$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation_adhoc` (`adhoc_uuid`, `case_uuid`, `corporation_uuid`, `adhoc`, `adhoc_value`, `customer_id`) VALUES ";
								$arrValues = array();
								foreach($arrAdhocSet as $adhoc_set) {		
									$arrValues[] = "(" . $adhoc_set . ", '" . $customer_id . "')"; 
								}
								$sql .= implode(",\r\n<br>", $arrValues);
					
								$db = getConnection(); $stmt = $db->prepare($sql);  
								$stmt->execute(); $stmt = null; $db = null;
								//$track_adhock_id = $db->lastInsertId();
								//trackAdhoc("insert", $track_adhock_id);
							}
						}
					}	
					$blnCarrierTwoAdded = true;
				//}
			}
		}
	}
	
	$success = array("success"=> array("text"=>"done"));
	echo json_encode($success);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
	die();
}	
//, `notedesc`, , `notepoint`, `notelock`, `lockedby`, `xsoundex`, `locator`, `enteredby`, `mailpoint`, `lockloc`, `docpointer`, `doctype`

//include("cls_logging.php");
?>
<script language="javascript">
parent.setFeedback("missing injuries transfer completed");
</script>
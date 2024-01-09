<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once('../shared/legacy_session.php');
set_time_limit(0);

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
	
	$sql = "select concat('KILL ',id,';') killswitch from information_schema.processlist 
	WHERE user='root' 
	AND `COMMAND` = 'Sleep'
	AND `TIME` > 2";
	
	try {
		$sleeps = DB::select($sql);
		
		foreach($sleeps as $sleep) {
			$kill_command = $sleep->killswitch;
			//echo $kill_command . "<br />";
			$stmt = DB::run($kill_command);
		}

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
			//echo json_encode($error);
	}
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
	
	if (isset($_GET["id"])) {
	    DB::delete("ikase_{$data_source}.cse_case", ['cpointer' => $_GET["id"]]);
	}
	
	$sql = "SELECT gcase.* 
	FROM `" . $GLOBALS['GEN_DB_NAME'] . "`.`case` gcase
	LEFT OUTER JOIN `ikase_" . $data_source . "`.`cse_case` ggc
	ON gcase.CASENO = ggc.cpointer
	WHERE 1
	AND ggc.case_id IS NULL
	";
	if (isset($_GET["id"])) {
		$sql .= " AND CASENO = '" . $_GET["id"] . "'";
	}
	$sql .= "
	LIMIT 0, 1";
	echo $sql . "\r\n<br>";
	//AND CASENO = 449
	//ORDER BY CASENO DESC
	//die();
	// print "<pre>sql :: ";
	// print_r($sql);
	// print "</pre> ";
	// die();

	$cases = DB::select($sql);
	// die("jay5");	

	// foreach($cases as $case_key1=>$case1){
		// $new_cases = (object)array_change_key_case(json_decode(json_encode($cases[0]), true), CASE_UPPER);
		// $cases[0] = $new_cases;
	// }
	//die(print_r($cases));
	$found = count($cases);

	$sql = "SELECT count(bodyparts_id) AS cnt FROM `ikase_" . $data_source . "`.`cse_bodyparts`";
	$body_parts = DB::select($sql);
	if($body_parts[0]->cnt == 0) {
		$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_bodyparts` SELECT * FROM ikase.cse_bodyparts";
		$stmt = DB::run($sql);
	}
	
	foreach($cases as $case_key=>$case){
		echo "here";
		echo "Processing -> " . $case_key. " == <a href='import_a1.php?customer_id=" . $customer_id . "&id=" .  $case->CASENO . "'>" .  $case->CASENO . "</a><br />\r\n";
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$process_start_time = $time;
		$case_no = $case->CASENO;
		echo "<pre>";
		print_r($case);
		echo $case_no."=caseno";
		echo "</pre>";
		// die("here1");
		//insert the case
		$case_uuid = uniqid("KS", false);
		
		$case_number = $case->YOURFILENO;
		if ($case_number=="") {
			$case_number = $case->CASENO;
		}
		echo "here2";
		if(strpos($case_number, 'ADJ') !== false) {
			$casetype = 'WCAB';
		} else {
			if($case->CASETYPE === "W") {
				$casetype = 'WCAB';
			} else {
				$casetype = $case->CASETYPE;
			}
		}
		echo "here3";
		//now the kase
		if($case->DATEENTER  == "0000-00-00" || $case->DATEENTER  == "0000-00-00 00:00:00") {
			$case_enter_date = "0000-00-00";
		} else {
			$case_enter_date = date("Y-m-d", strtotime($case->DATEENTER));
		}
		$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case` (case_uuid, cpointer, case_number, case_date, case_type, venue, case_status, submittedOn, supervising_attorney, attorney, worker, customer_id) 
VALUES ('" . $case_uuid . "', '" . $case->CASENO . "', '" . $case_number . "', '" .$case_enter_date . "', '" . $casetype . "', 'LAO', '" . addslashes($case->CASESTAT) . "', '" . $case_enter_date . "', '" . $case->ATTY_RESP . "', '" . $case->ATTY_HAND . "', '" . $case->SEC_HAND . "', " . $customer_id . ")";
		//addslashes($case->CAPTION1) . "', '" . 
		echo $sql . "\r\n<br>"; 
		//die("here4");
		$stmt = DB::run($sql);

		print "<pre>stmt 111 :: ";
		// print_r($stmt);
		print "</pre> ";
		// die();

		$table_uuid = uniqid("SF", false);

		//insert in cse_casestatus, if a record for the same does not exist
		if($case->CASESTAT != '' AND $case->CASETYPE != '') {
			$sql = "SELECT casestatus_id 
			FROM `ikase_" . $data_source . "`.`cse_casestatus`
			WHERE casestatus LIKE '" . $case->CASESTAT . "' AND law LIKE '" . $case->CASETYPE . "'";
			$casestatus = DB::select($sql);
			if(count($casestatus) == 0) {
				$right_now = date("Y-m-d H:i:s");
				$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_casestatus` 
				(`casestatus_uuid`, `casestatus`, `law`, `last_change_date`, `deleted`) 
				VALUES('" . $table_uuid . "', '" . $case->CASESTAT . "', '" . $case->CASETYPE . "', '" . $right_now . "', 'N')";
				//echo $sql . "\r\n<br>"; 
				$stmt = DB::run($sql);
			}
		}
		echo "fun start<br>jj2<br>$case_no<br>jj1";
		//insert the injury, if any
		$sql = "SELECT * 
		FROM `" . $GLOBALS['GEN_DB_NAME'] . "`.`injury`
		WHERE CASENO = " . $case_no . "
		ORDER BY ORDERNO ASC";
		// echo $sql . "\r\n<br>"; 
		// die();
		$injuries = DB::select($sql);
		echo "fun end";

		print "<pre>injuries 111 :: ";
		// print_r($injuries);
		print "</pre> ";
		// die();
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
			
		foreach($injuries as $injury_index=>$injury) {	
			echo "In foreach<br>";	
			$applicant_name = "";
			$employer_name = "";
			$parent_applicant_uuid = "";
			
			if (!$blnApplicantAdded) {				
				echo "In foreach 1st if<br>";	
				if (is_object($injury)) {
					echo "In foreach 1st if in 2nd if<br>";	
					//echo "\r\n" . $injury->DOI . "\r\n<br>";
					//die(print_r($injury));

					$sql = "SELECT `ac`.`HOME`, `ac`.`CAR`, `ac`.`EMAIL`, `ac2`.`ADDRESS1`,  `ac2`.`CITY`,  `ac2`.`STATE`,  `ac2`.`ZIP`
					FROM `" . $GLOBALS['GEN_DB_NAME'] . "`.casecard acc
					INNER JOIN `" . $GLOBALS['GEN_DB_NAME'] . "`.card ac
					ON acc.CARDCODE = ac.CARDCODE
					INNER JOIN `" . $GLOBALS['GEN_DB_NAME'] . "`.card2 ac2
					ON ac.FIRMCODE = ac2.FIRMCODE
					LEFT OUTER JOIN `" . $GLOBALS['GEN_DB_NAME'] . "`.card3
					ON ac2.EAMSREF = card3.EAMSREF
					WHERE acc.CASENO = " . $case_no . " AND (acc.TYPE LIKE 'APPLICANT' OR acc.TYPE LIKE 'PLAINTIFF' OR acc.TYPE LIKE NULL)
					ORDER BY acc.CARDCODE";
					echo "7 :-".$sql."<br>";
					$parties = DB::select($sql);
					
					$arrSet = array();
					$full_name = $injury->FIRST;
					$full_name .= " " . $injury->LAST;
					
					if ($applicant_name=="") {
						$applicant_name = $full_name;
					}
					
					$full_address = "";
					// if ($injury->ADDRESS!="") {
					// 	$full_address = $injury->ADDRESS;
					// }
					// if ($injury->CITY!="") {
					// 	$full_address .= ", " . $injury->CITY;
					// }
					// if ($injury->STATE!="") {
					// 	$full_address .= ", " . $injury->STATE;
					// }
					// if ($injury->ZIP_CODE!="") {
					// 	$full_address .= " " . $injury->ZIP_CODE;
					// }
					if ($parties && $parties[0]->ADDRESS1!="") {
						$full_address = $parties[0]->ADDRESS1;
					}
					if ($parties && $parties[0]->CITY!="") {
						$full_address .= ", " . $parties[0]->CITY;
					}
					if ($parties && $parties[0]->STATE!="") {
						$full_address .= ", " . $parties[0]->STATE;
					}
					if ($parties && $parties[0]->ZIP!="") {
						$full_address .= " " . $parties[0]->ZIP;
					}
					
					$sql = "SELECT person_uuid
					FROM `ikase_" . $data_source . "`.`cse_person`
					WHERE customer_id = " . $customer_id . "
					AND person_uuid = parent_person_uuid
					AND deleted = 'N'
					AND full_name = '" . addslashes($full_name) . "'
					AND full_address = '" . addslashes($full_address) . "'";
					echo "8 :-".$sql . "\r\n<br>";
					$stmt = DB::run($sql);
					$rolodex = $stmt->fetchObject();
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
					// $street = $injury->ADDRESS;
					$street = $parties ? str_replace("'","",$parties[0]->ADDRESS1) : '';
					$arrSet[] = addslashes($street);
					// $city = $injury->CITY;
					$city = $parties ? str_replace("'","",$parties[0]->CITY) : '';
					$arrSet[] = addslashes($city);
					// $state = $injury->STATE;
					$state = $parties ? str_replace("'","",$parties[0]->STATE) : '';
					$arrSet[] = addslashes(substr($state, 0, 2));
					// $zip = $injury->ZIP_CODE;
					$zip = $parties ? $parties[0]->ZIP : '';
					$zip = substr($zip, 0, 10); $arrSet[] = str_replace("\\", "", $zip);
					$injury->SOCIAL_SEC = str_replace("-", "", $injury->SOCIAL_SEC);
					$injury->SOCIAL_SEC = str_replace("'", "", $injury->SOCIAL_SEC);
					$arrSet[] = $injury->SOCIAL_SEC;
					$arrSet[] = str_replace("'","",substr($injury->SOCIAL_SEC, strlen($injury->SOCIAL_SEC) - 4, 4));
					
					// $arrSet[] = $injury->E_PHONE;
					$arrSet[] = $parties ?  str_replace("'","",$parties[0]->HOME) : '';
					$temp = $parties ?  str_replace("'","",addslashes($parties[0]->CAR)) : '';
					$arrSet[] = str_replace("\\","",$temp);
					$arrSet[] = $parties ? str_replace("'","",$parties[0]->EMAIL) : '';
					$arrSet[] = str_replace("'","",$injury->E_FAX);
					echo "<br><pre>";
					print_r($arrSet);
					// die();
					echo "</pre><br>";
					if (!$blnRolodex) {
						//insert the parent record first
						$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_person` 
						(`person_uuid`, `customer_id`, `parent_person_uuid`, `full_name`, `first_name`, `last_name`, `aka`, `full_address`, `street`, `city`, `state`, `zip`, `ssn`, `ssn_last_four`, `phone`, 
						`cell_phone`, `email`,
						`fax`, `last_updated_date`, `last_update_user`, `deleted`) 
						VALUES('" . $parent_applicant_uuid . "', '" . $customer_id . "', '" . $parent_applicant_uuid . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N')";
						
						echo "9 :-".$sql . "\r\n<br>"; 
						$stmt = DB::run($sql);
					}
					$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_person` 
					(`person_uuid`, `customer_id`, `parent_person_uuid`, `full_name`, `first_name`, `last_name`, `aka`, `full_address`, `street`, `city`, `state`, `zip`, `ssn`, `ssn_last_four`, `phone`, 
					`cell_phone`, `email`,
					`fax`, `last_updated_date`, `last_update_user`, `deleted`) 
					VALUES('" . $applicant_table_uuid . "', '" . $customer_id . "', '" . $parent_applicant_uuid . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N')";
					
					echo "10 :-".$sql . "\r\n<br>"; 
					$stmt = DB::run($sql);
					//die();
					$case_table_uuid = uniqid("CA", false);
					//attach applicant to kase
					$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_person` (`case_person_uuid`, `case_uuid`, `person_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
					VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $applicant_table_uuid . "', 'main', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
					
					echo "11 :-".$sql . "\r\n<br>"; 
					$stmt = DB::run($sql);
					
					$blnApplicantAdded = true;
				}
			}
			$employer_name = " vs " . $injury->E_NAME;
			
			$injury_uuid = uniqid("KI", false);
			echo $employer_name . "<br>";
			//die(print_r($injury));
			//doi dates
			if ($injury->DOI=="" || $injury->DOI=="0000-00-00" || $injury->DOI=="0000-00-00 00:00:00") {
				$injury->DOI = "0000-00-00";
			} else {
				$injury->DOI = date("Y-m-d", strtotime($injury->DOI)); 
			}
			if ($injury->DOI2=="" || $injury->DOI2=="0000-00-00" || $injury->DOI2=="0000-00-00 00:00:00") {
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
			// For SOL START
			if($injury->DOI2!="0000-00-00" || $injury->DOI2!="0000-00-00 00:00:00") {
				// echo "<br>IF";
				$sol_date = date_create($injury->DOI2);
				$sol_date = date_add($sol_date,date_interval_create_from_date_string("5 Years"));
				$sol_date = (array)$sol_date;
				// var_dump($sol_date['date']);
				$sol_date = explode(' ', $sol_date['date']);
				// echo json_encode($sol_date[0]);
				$statute_limitation = $sol_date[0];
			}elseif($injury->DOI!="0000-00-00" || $injury->DOI!="0000-00-00 00:00:00") {
				// echo "<br>ELSE";
				$sol_date = date_create($injury->DOI);
				$sol_date = date_add($sol_date,date_interval_create_from_date_string("5 Years"));
				$sol_date = (array)$sol_date;
				// var_dump($sol_date['date']);
				$sol_date = explode(' ', $sol_date['date']);
				// echo json_encode($sol_date[0]);
				$statute_limitation = $sol_date[0];
			}else{
				$statute_limitation = "0000-00-00";
			}
			// echo "<br>".$statute_limitation."<br>";
			// For SOL END

			//echo "full_address:" . $full_address . "<br />";
			$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_injury` 
			(`injury_uuid`, `injury_number`, `adj_number`, `start_date`, `end_date`, `type`, `occupation`, `body_parts`, `statute_limitation`, `ct_dates_note`,
			`full_address`, `street`, `suite`, `city`, `state`, `zip`, `customer_id`, `explanation`, `deleted`)
			VALUES ('" . $injury_uuid . "', " . ($injury_index + 1) . ", '" . addslashes($injury->CASE_NO) . "', '" . $injury->DOI . "', '" . $injury->DOI2 . "', '', '" . addslashes($injury->ADJ1B) . "','" . $body_parts . "','".$statute_limitation."','" . addslashes($injury->ADJ1C) . "','" . 
			addslashes($full_address) . "', '" . addslashes($injury->ADJ1D) . "','','" . addslashes($injury->ADJ1D2) . "', '" . $injury->ADJ1D3 . "', '" . $injury->ADJ1D4 . "', " . $customer_id . ", '" . addslashes($injury->ADJ1E) . "', 'N')";
			echo "12 :-".$sql . "\r\n<br>"; 
			//die();
			$stmt = DB::run($sql);
		
			$case_table_uuid = uniqid("KA", false);
			$attribute_1 = "main";
			
			//now we have to attach the injury to the case 
			$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_injury` (`case_injury_uuid`, `case_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $injury_uuid . "', 'main', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
	
			echo "13 :-".$sql . "\r\n<br>";  
			$stmt = DB::run($sql);
				
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
					FROM `ikase_" . $data_source . "`.`cse_corporation`
					WHERE customer_id = " . $customer_id . "
					AND corporation_uuid = parent_corporation_uuid
					AND type = 'carrier'
					AND deleted = 'N'
					AND company_name = '" . addslashes($injury->I_NAME) . "'
					AND full_address = '" . addslashes($carrier_address) . "'";
					echo "14 :-".$sql . "\r\n<br>";
					$stmt = DB::run($sql);
					$rolodex = $stmt->fetchObject();
					if (is_object($rolodex)) {
						$parent_table_uuid = $rolodex->corporation_uuid;
						$blnRolodex = true;
					}
					if (!$blnRolodex) {
						//insert the parent record first
						$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_corporation` 
						(`corporation_uuid`, `customer_id`, `full_name`, `company_name`, `type`, `full_address`, 
						`street`, `city`, `state`, `zip`, `phone`, `fax`, `salutation`, 
						`last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
						VALUES('" . $parent_table_uuid . "', '" . $customer_id . "', 
						'" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 
						'N', '" . $parent_table_uuid . "','')";
						echo "15 :-".$sql . "\r\n<br>"; 		
						$stmt = DB::run($sql);
					}
					//actual record now
					$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_corporation` 
					(`corporation_uuid`, `customer_id`, `full_name`, `company_name`, `type`, `full_address`, 
						`street`, `city`, `state`, `zip`, `phone`, `fax`, `salutation`,  
						`last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
					VALUES('" . $table_uuid . "', '" . $customer_id . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N', '" . $parent_table_uuid . "','')";
					echo "16 :-".$sql . "\r\n<br>"; 		
					$stmt = DB::run($sql);
					
					//attach to injury
					$injury_table_uuid = uniqid("KA", false);
					//now we have to attach the partie to the case 
					$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_corporation` (`case_corporation_uuid`, `case_uuid`, `corporation_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
					VALUES ('" . $injury_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', '" . $injury_uuid . "', '" . $type . "', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
					echo "17 :-".$sql . "\r\n<br>";   		
					$stmt = DB::run($sql);
					
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
							$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_corporation_adhoc` (`adhoc_uuid`, `case_uuid`, `corporation_uuid`, `adhoc`, `adhoc_value`, `customer_id`) VALUES ";
							$arrValues = array();
							foreach($arrAdhocSet as $adhoc_set) {		
								$arrValues[] = "(" . $adhoc_set . ", '" . $customer_id . "')"; 
							}
							$sql .= implode(",\r\n<br>", $arrValues);
							echo "19 :-".$sql . "\r\n<br>";
							DB::run($sql);
							//$track_adhock_id = DB::lastInsertId();
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
					FROM `ikase_" . $data_source . "`.`cse_corporation`
					WHERE customer_id = " . $customer_id . "
					AND corporation_uuid = parent_corporation_uuid
					AND type = 'carrier'
					AND deleted = 'N'
					AND company_name = '" . addslashes($injury->I2_NAME) . "'
					AND full_address = '" . addslashes($carrier_address) . "'";
					echo "20 :-".$sql . "\r\n<br>";
					$stmt = DB::run($sql);
					$rolodex = $stmt->fetchObject();
					if (is_object($rolodex)) {
						$parent_table_uuid = $rolodex->corporation_uuid;
						$blnRolodex = true;
					}
					if (!$blnRolodex) {
						//insert the parent record first
						$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_corporation` 
						(`corporation_uuid`, `customer_id`, `full_name`, `company_name`, `type`, `full_address`, 
						`street`, `city`, `state`, `zip`, `phone`, `fax`, `salutation`, 
						`last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
						VALUES('" . $parent_table_uuid . "', '" . $customer_id . "', 
						'" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 
						'N', '" . $parent_table_uuid . "','')";
						echo "21 :-".$sql . "\r\n<br>"; 		
						$stmt = DB::run($sql);
					}
					//actual record now
					$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_corporation` 
					(`corporation_uuid`, `customer_id`, `full_name`, `company_name`, `type`, `full_address`, 
						`street`, `city`, `state`, `zip`, `phone`, `fax`, `salutation`,  
						`last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
					VALUES('" . $table_uuid . "', '" . $customer_id . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N', '" . $parent_table_uuid . "','')";
					echo "22 :-".$sql . "\r\n<br>"; 		
					$stmt = DB::run($sql);
					
					//attach to injury
					$injury_table_uuid = uniqid("KA", false);
					//now we have to attach the partie to the case 
					$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_corporation` (`case_corporation_uuid`, `case_uuid`, `corporation_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
					VALUES ('" . $injury_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', '" . $injury_uuid . "', '" . $type . "', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
					echo "23 :-".$sql . "\r\n<br>";   		
					$stmt = DB::run($sql);
					
					//echo $sql . "\r\n<br>";   		
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
							$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_corporation_adhoc` (`adhoc_uuid`, `case_uuid`, `corporation_uuid`, `adhoc`, `adhoc_value`, `customer_id`) VALUES ";
							$arrValues = array();
							foreach($arrAdhocSet as $adhoc_set) {		
								$arrValues[] = "(" . $adhoc_set . ", '" . $customer_id . "')"; 
							}
							$sql .= implode(",\r\n<br>", $arrValues);
				
							DB::run($sql);
							//$track_adhock_id = DB::lastInsertId();
							//trackAdhoc("insert", $track_adhock_id);
						}
					}
				}	
				$blnCarrierTwoAdded = true;
			//}

			//bodyparts
			$sql_bp = "SELECT * FROM `cse_bodyparts` WHERE 1 ORDER BY code ASC";
			$db = getConnection(); $stmt = $db->prepare($sql_bp);
			$stmt = $db->query($sql_bp);
			$bodyparts = $stmt->fetchAll(PDO::FETCH_OBJ);
			$arrBodyParts = array();
			foreach($bodyparts as $bodypart){
				$arrBodyParts[$bodypart->code] = $bodypart->bodyparts_uuid;
			}

			$inj_bod_table_uuid = uniqid("KS", false);
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
			for($loop_cnt = 0; $loop_cnt < count($arrPOB); $loop_cnt++) {
				$bodyparts_uuid = $arrBodyParts[$arrPOB[$loop_cnt]];
				$sql = "INSERT INTO cse_injury_bodyparts (`injury_bodyparts_uuid`, `injury_uuid`, `bodyparts_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $inj_bod_table_uuid . "', '" . $injury_uuid . "','" . $bodyparts_uuid . "','" . ($loop_cnt + 1) . "', '" . date("Y-m-d H:i:s") . "', '" . addslashes($_SESSION['user_name']) . "', '" . $customer_id . "')";
				echo $sql . "\r\n\r\n";
				$stmt = DB::run($sql);
			}
		
			$sql_cc = "SELECT case.CASENO, casecard.CARDCODE, card.TYPE
			FROM `".$GLOBALS['GEN_DB_NAME']."`.`case` LEFT JOIN `ikase_" . $data_source . "`.`cse_case` ON case.CASENO = cse_case.cpointer LEFT JOIN `".$GLOBALS['GEN_DB_NAME']."`.`casecard` ON case.CASENO = casecard.CASENO LEFT JOIN `".$GLOBALS['GEN_DB_NAME']."`.`card` ON casecard.CARDCODE = card.CARDCODE WHERE card.TYPE LIKE 'EMPLOYER' AND cse_case.cpointer = ".$case->CASENO;
			$db = getConnection(); $stmt = $db->prepare($sql_cc);
			$stmt = $db->query($sql_cc);
			$cc = $stmt->fetchAll(PDO::FETCH_OBJ);
			if(isset($cc) && count($cc) == 0) {
				if ($injury->E_NAME!="") {
					$case_table_uuid = uniqid("CA", false);
					$table_uuid = uniqid("DR", false);
					$parent_table_uuid = uniqid("PD", false);
					$last_updated_date = date("Y-m-d H:i:s");
					$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_corporation` (`case_corporation_uuid`, `case_uuid`, `corporation_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
					VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', 'employer', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
			
					//echo $sql . "\r\n<br>";  
					$stmt = DB::run($sql);

					$arrSet = array();
					$arrSet[] = addslashes($injury->E_NAME);
					$arrSet[] = "employer";
					$full_address = "";
					if ($injury->E_ADDRESS != "") {
						$full_address .= $injury->E_ADDRESS;
					}
					if ($injury->E_CITY != "") {
						$full_address .= ", " . $injury->E_CITY;
					}
					if ($injury->E_STATE != "") {
						$full_address .= ", " . $injury->E_STATE;
					}
					if ($injury->E_ZIP != "") {
						$full_address .= ", " . $injury->E_ZIP;
					}
					// $full_address = $injury->E_ADDRESS;
					$arrSet[] = addslashes($full_address);
					$street = $injury->E_ADDRESS; $arrSet[] = addslashes($street);
					$city = $injury->E_CITY; $arrSet[] = addslashes($city);
					$state = $injury->E_STATE; $arrSet[] = addslashes(substr($state, 0, 2));
					$zip = $injury->E_ZIP; $zip = substr($zip, 0, 10); $arrSet[] = str_replace("\\", "", $zip);
					$arrSet[] = $injury->E_PHONE;

					if (!$blnRolodex) {
						//insert the parent record first
						$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_corporation` 
						(`corporation_uuid`, `customer_id`, 
						#`first_name`, `last_name`, 
						`company_name`, `type`, `full_address`, 
						`street`, `city`, `state`, `zip`, `phone`, 
						#`suite`, `phone`, `fax`, `email`, `employee_fax`, `employee_email`, `salutation`, 
						`last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
						VALUES('" . $parent_table_uuid . "', '" . $customer_id . "', 
						'" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 
						'N', '" . $parent_table_uuid . "','')";
				
						//echo $sql . "\r\n<br>";  
						$stmt = DB::run($sql);
					}
					//actual record now
					$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_corporation` 
					(`corporation_uuid`, `customer_id`, 
					#`first_name`, `last_name`, 
					`company_name`, `type`, `full_address`, 
					`street`, `city`, `state`, `zip`, `phone`, 
					#`suite`, `phone`, `fax`, `email`, `employee_fax`, `employee_email`, `salutation`, 
					`last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
					VALUES('" . $table_uuid . "', '" . $customer_id . "', 
					'" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 
					'N', '" . $parent_table_uuid . "','')";
			
					echo "24 :-".$sql . "\r\n<br>";  
					$stmt = DB::run($sql);
				}
			}
		}

		print "<pre>TESTY BLOCK :: ";
		print_r("TESTY BLOCK");
		print "</pre> ";
		// die();

		//parties
		$sql = "SELECT acc.CASENO, acc.CARDCODE, acc.TYPE partie_type,  `ac`.`CARDCODE`,  `ac`.`FIRMCODE`,  `ac`.`LETSAL`,  
		`ac`.`SALUTATION`,  `ac`.`FIRST`,  `ac`.`MIDDLE`,  `ac`.`LAST`,  `ac`.`SUFFIX`,  `ac`.`SOCIAL_SEC`,  `ac`.`TITLE`,  `ac`.`HOME`,  
		`ac`.`BUSINESS`,  `ac`.`FAX` person_fax,  `ac`.`CAR`,  `ac`.`BEEPER`,  `ac`.`EMAIL`,  `ac`.`BIRTH_DATE`,  `ac`.`INTERPRET`,  
		`ac`.`LANGUAGE`,  `ac`.`LICENSENO`,  `ac`.`SPECIALTY`,  `ac`.`MOTHERMAID`,  `ac`.`PROTECTED`,
		`ac2`.`FIRMCODE`,  `ac2`.`FIRM`,  `ac2`.`VENUE`,  `ac2`.`TAX_ID`,  `ac2`.`ADDRESS1`,  `ac2`.`ADDRESS2`,  `ac2`.`CITY`,  `ac2`.`STATE`,  `ac2`.`ZIP`,  
		`ac2`.`PHONE1`,  `ac2`.`PHONE2`,  `ac2`.`FAX` partie_fax,  `ac2`.`FIRMKEY`,  `ac2`.`COLOR`,  `ac2`.`EAMSREF`,
		card3.NAME eams_name, card3.ADDRESS1 eams_street, card3.ADDRESS2 eams_suite, 
		card3.CITY eams_city, card3.STATE eams_state, card3.ZIP eams_zip, card3.PHONE eams_phone
		FROM `" . $GLOBALS['GEN_DB_NAME'] . "`.casecard acc
		INNER JOIN `" . $GLOBALS['GEN_DB_NAME'] . "`.card ac
		ON acc.CARDCODE = ac.CARDCODE
		INNER JOIN `" . $GLOBALS['GEN_DB_NAME'] . "`.card2 ac2
		ON ac.FIRMCODE = ac2.FIRMCODE
		LEFT OUTER JOIN `" . $GLOBALS['GEN_DB_NAME'] . "`.card3
		ON ac2.EAMSREF = card3.EAMSREF
		WHERE acc.CASENO = " . $case_no . "
		ORDER BY acc.CARDCODE";

		print "<pre>sql :: ";
		print_r($sql);
		print "</pre> ";
		// die();
		
		$parties = DB::select($sql);
		// echo "<pre>1111111111111";
		// die(print_r($parties));
		// echo "</pre>";
		// die();
		// $sql = "SELECT   acc.CASENO, acc.CARDCODE, acc.TYPE partie_type,  `ac`.`CARDCODE`,  `ac`.`FIRMCODE`,  `ac`.`LETSAL`,  
		// `ac`.`SALUTATION`,  `ac`.`FIRST`,  `ac`.`MIDDLE`,  `ac`.`LAST`,  `ac`.`SUFFIX`,  `ac`.`SOCIAL_SEC`,  `ac`.`TITLE`,  `ac`.`HOME`,  
		// `ac`.`BUSINESS`,  `ac`.`FAX` person_fax,  `ac`.`CAR`,  `ac`.`BEEPER`,  `ac`.`EMAIL`,  `ac`.`BIRTH_DATE`,  `ac`.`INTERPRET`,  
		// `ac`.`LANGUAGE`,  `ac`.`LICENSENO`,  `ac`.`SPECIALTY`,  `ac`.`MOTHERMAID`,  `ac`.`PROTECTED`
		// FROM `reyestest`.casecard acc
		// INNER JOIN `reyestest`.card ac
		// ON acc.CARDCODE = ac.CARDCODE
		// WHERE acc.CASENO = " . $case_no . "
		// ORDER BY acc.CARDCODE";
		
		// $parties = DB::select($sql);
		// echo "<pre>";
		// die(print_r($parties));
		// echo "</pre>";


		// $sql = "SELECT * FROM card2 ac2 
		// LEFT OUTER JOIN `reyestest`.card3
		// ON ac2.EAMSREF = card3.EAMSREF
		// WHERE ac2.FIRMCODE = 11948";
		
		// $parties = DB::select($sql);
		

		
		$arrCpointer = array();
		
		foreach($parties as $key=>$partie){
			$table_uuid = uniqid("DR", false);
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
			$full_name = $partie->FIRST;
			if ($partie->MIDDLE!="") {
				$full_name .= " " . $partie->MIDDLE;
			}
			$full_name .= " " . $partie->LAST;
			
			$arrSet[] = addslashes($full_name);
			$arrSet[] = addslashes($partie->FIRST);
			$arrSet[] = addslashes($partie->LAST);
			$company_name = $partie->FIRM; $arrSet[] = $company_name != '' ? addslashes($company_name) : addslashes($full_name);
			/*
			if ($partie->partie_type=="ATTORNEY") {
				$partie->partie_type = "DEFENSE";
			}
			*/
			$type = trim(strtolower($partie->partie_type)); 
			$type = str_replace(" ", "_", $type);
			//if blnContinue is true, we will skip this partie
			$blnContinue = false;
			switch($type){
				case "employer":
					// if ($employer_name=="") {
						$employer_name = " vs " . $company_name;
					// }
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
			$employee_email = $partie->EMAIL; $arrSet[] = addslashes($employee_email);
			$salutation = $partie->SALUTATION; $arrSet[] = addslashes($salutation);
			
			if (!$blnRolodex) {
				if ($type!="venue" && $type!="applicant" && $type!="client") {
					//look up in case already in
					$sql = "SELECT corporation_uuid
					FROM `ikase_" . $data_source . "`.`cse_corporation`
					WHERE customer_id = " . $customer_id . "
					AND corporation_uuid = parent_corporation_uuid
					AND type = '" . addslashes(strtolower($partie->partie_type)) . "'
					AND deleted = 'N'
					AND company_name = '" . addslashes($partie->FIRM) . "'
					AND full_address = '" . addslashes($full_address_partie) . "'";
					//echo $sql . "\r\n<br>";
					$stmt = DB::run($sql);
					$rolodex = $stmt->fetchObject();
					if (is_object($rolodex)) {
						$parent_table_uuid = $rolodex->corporation_uuid;
						$blnRolodex = true;
					}
				}
			}
			if ($type=="venue") {
				$venue_abbr = $partie->VENUE;
				$parent_table_uuid = array_search($venue_abbr, $arrVenues);
				$blnRolodex = true;
				
				$sql = "UPDATE `ikase_" . $data_source . "`.`cse_case`
				SET `venue` = '" . $parent_table_uuid . "'
				WHERE case_uuid = '" . $case_uuid . "'";
				//echo $sql . "\r\n<br>";
				$stmt = DB::run($sql);
			}
			// echo $type.'<br>';
			// echo '<pre>';print_r($arrSet);
			
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
				//echo $sql . "\r\n<br>";
				$stmt = DB::run($sql);
				
				$full_name = $partie->FIRST;
				if ($partie->MIDDLE!="") {
					$full_name .= " " . $partie->MIDDLE;
				}
				$full_name .= " " . $partie->LAST;
				if ($partie->SUFFIX!="") {
					$full_name .= ", " . $partie->SUFFIX;
				}
				if ($applicant_name=="") {
					$applicant_name = $full_name;
				}
				
				if ($parent_applicant_uuid=="") {
					$sql = "SELECT person_uuid
					FROM `ikase_" . $data_source . "`.`cse_person`
					WHERE customer_id = " . $customer_id . "
					AND person_uuid = parent_person_uuid
					AND deleted = 'N'
					AND full_name = '" . addslashes($full_name) . "'
					AND full_address = '" . addslashes($full_address_partie) . "'";
					//echo $sql . "\r\n<br>";
					$stmt = DB::run($sql);
					$rolodex = $stmt->fetchObject();
					$blnRolodex = false;
					$parent_table_uuid = "";
					if (is_object($rolodex)) {
						$parent_applicant_uuid = $rolodex->person_uuid;
						$blnRolodex = true;
					}
				}
				$arrSet[] = addslashes($type);
				$full_address = $full_address_partie;
				
				$arrSet[] = addslashes($full_name);
				$insertSet[] = addslashes($full_name);
				$arrSet[] = addslashes($partie->FIRST);
				$insertSet[] = addslashes($partie->FIRST);
				$insertSet[] = addslashes($partie->MIDDLE);
				$arrSet[] = addslashes($partie->LAST);
				$insertSet[] = addslashes($partie->LAST);
				$arrSet[] = "";
				$insertSet[] = "";
				$arrSet[] = $full_address;
				$insertSet[] = $full_address;
				$street = $partie->ADDRESS1; $arrSet[] = addslashes($street); $insertSet[] = addslashes($street);
				$city = $partie->CITY; $arrSet[] = addslashes($city); $insertSet[] = addslashes($city);
				$state = $partie->STATE; $arrSet[] = addslashes(substr($state, 0, 2)); $insertSet[] = addslashes(substr($state, 0, 2));
				$zip = $partie->ZIP; $zip = substr($zip, 0, 10); $arrSet[] = str_replace("\\", "", $zip); $insertSet[] = str_replace("\\", "", $zip);
				$suite = $partie->ADDRESS2; $arrSet[] = addslashes($suite); $insertSet[] = addslashes($suite);
				$phone = $partie->PHONE1; $arrSet[] = addslashes($phone); $insertSet[] = addslashes($phone);
				$fax = $partie->partie_fax; $arrSet[] = addslashes($fax);
				$email = $partie->EMAIL; $arrSet[] = $email; $insertSet[] = $email;
				$employee_fax = $partie->person_fax; $arrSet[] = addslashes($employee_fax); $insertSet[] = addslashes($employee_fax);
				$employee_phone = $partie->BUSINESS; $arrSet[] = addslashes($employee_phone); $insertSet[] = addslashes($employee_phone);
				$employee_cellphone = $partie->HOME; $arrSet[] = addslashes($employee_cellphone); $insertSet[] = addslashes($employee_cellphone);
				$partie->SOCIAL_SEC = str_replace("-", "", $partie->SOCIAL_SEC);
				$arrSet[] = $partie->SOCIAL_SEC;
				$insertSet[] = $partie->SOCIAL_SEC;
				$arrSet[] = substr($partie->SOCIAL_SEC, strlen($partie->SOCIAL_SEC) - 4, 4);
				$insertSet[] = substr($partie->SOCIAL_SEC, strlen($partie->SOCIAL_SEC) - 4, 4);
				$dob = $partie->BIRTH_DATE;
				//die($injury->clientss . " // " . $dob);
				$age = 0;
				if ($dob!="") {
					$dob = date("Y-m-d", strtotime($partie->BIRTH_DATE));
					$birthDate = explode("-", $partie->BIRTH_DATE);
					//get age from date or birthdate
					$date_time_explode = explode(" ",$birthDate[2]);
					$age = (date("md", date("U", mktime(0, 0, 0, $birthDate[1], $date_time_explode[0], $birthDate[0]))) > date("md") ? ((date("Y") - $birthDate[0]) - 1) : (date("Y") - $birthDate[0]));
				}
				
				$arrSet[] = $dob;
				$insertSet[] = $dob;
				$arrSet[] = $age;
				$insertSet[] = $age;
				$arrSet[] = addslashes($partie->LICENSENO);
				$insertSet[] = addslashes($partie->LICENSENO);
				$arrSet[] = addslashes($partie->SALUTATION);
				$insertSet[] = addslashes($partie->SALUTATION);
				$arrSet[] = "";
				$insertSet[] = "";
				$arrSet[] = $partie->LANGUAGE;
				$insertSet[] = $partie->LANGUAGE;
				
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
					`license_number` = '" . addslashes($partie->LICENSENO) . "',
					`language` = '" . addslashes($partie->LANGUAGE) . "',
					`salutation` = '" . addslashes($partie->SALUTATION) . "'";
					
					$sql = "UPDATE `ikase_" . $data_source . "`.`cse_person`
					" . $set . "
					WHERE `parent_person_uuid` = '" . $parent_applicant_uuid . "'";
					
					//echo $sql . "\r\n<br>"; 
					$stmt = DB::run($sql);
				} else {
					if (!$blnRolodex) {
						//insert the parent record first
						$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_person` (`person_uuid`, `customer_id`, `parent_person_uuid`, `full_name`, `first_name`, `middle_name`, `last_name`, `aka`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `email`, `fax`, `work_phone`, `cell_phone`, `ssn`, `ssn_last_four`, `dob`, `age`, `license_number`, `salutation`, `gender`, `language`, `last_updated_date`, `last_update_user`, `deleted`) 
						VALUES('" . $parent_applicant_uuid . "', '" . $customer_id . "', '" . $parent_applicant_uuid . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N')";
						
						echo "1 :-". $sql . "\r\n<br>"; 
						$stmt = DB::run($sql);
					}
					$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_person` (`person_uuid`, `customer_id`, `parent_person_uuid`, `full_name`, `first_name`, `middle_name`, `last_name`, `aka`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `email`, `fax`, `work_phone`, `cell_phone`, `ssn`, `ssn_last_four`, `dob`, `age`, `license_number`, `salutation`, `gender`, `language`, `last_updated_date`, `last_update_user`, `deleted`) 
					VALUES('" . $table_uuid . "', '" . $customer_id . "', '" . $parent_applicant_uuid . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N')";
					
					echo "2 :-".$sql . "\r\n<br>"; 
					$stmt = DB::run($sql);
					
					$case_table_uuid = uniqid("CA", false);
					//attach applicant to kase
					$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_person` (`case_person_uuid`, `case_uuid`, `person_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
					VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', 'main', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
					
					echo "3 :-".$sql . "\r\n<br>"; 
					$stmt = DB::run($sql);
				}
			} else {
				//parties
				if (!$blnRolodex) {
					//insert the parent record first
					$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_corporation` 
					(`corporation_uuid`, `customer_id`, `full_name`, `first_name`, `last_name`, `company_name`, `type`, `full_address`, 
					`street`, `city`, `state`, `zip`, `suite`, `phone`, `fax`, `email`, 
					`employee_phone`, `employee_fax`, `employee_email`, `salutation`, 
					`last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
					VALUES('" . $parent_table_uuid . "', '" . $customer_id . "', 
					'" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 
					'N', '" . $parent_table_uuid . "','')";
					echo "4 :-".$sql . "\r\n<br>"; 		
					$stmt = DB::run($sql);
				}
				//actual record now
				$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_corporation` (`corporation_uuid`, `customer_id`, `full_name`, `first_name`, `last_name`, `company_name`, `type`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `fax`, `email`, `employee_phone`, `employee_fax`, `employee_email`, `salutation`, `last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
				VALUES('" . $table_uuid . "', '" . $customer_id . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N', '" . $parent_table_uuid . "','')";
				echo "5 :-".$sql . "\r\n<br>"; 		
				$stmt = DB::run($sql);
				
				// if ($type=="employer" && $employer_name=="") {
				if ($type=="employer") {
					$employer_name = " vs " . $company_name;
				}
				
				//attach to case
				$case_table_uuid = uniqid("KA", false);
				//now we have to attach the partie to the case 
				$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_corporation` (`case_corporation_uuid`, `case_uuid`, `corporation_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', '" . addslashes($type) . "', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
				echo "6 :-".$sql . "\r\n<br>";   		
				$stmt = DB::run($sql);
			}
		}
		
		//update the case name
		//need to update interpreter and language
		$sql = "UPDATE `ikase_" . $data_source . "`.`cse_case`
		SET `case_name` = '" . addslashes($applicant_name . $employer_name) . "'
		WHERE case_uuid = '" . $case_uuid . "'";
		echo "7 :-".$sql . "\r\n<br>";
		$stmt = DB::run($sql);
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $process_start_time), 4);
		echo " => row completed in " . $total_time . "\r\n<br>"; 
	}
	
	$sql = "SELECT COUNT(*) case_count
	FROM `" . $GLOBALS['GEN_DB_NAME'] . "`.`case` gcase
	WHERE 1";
	//echo $sql . "\r\n<br>";
	//die();
	$stmt = DB::run($sql);
	$cases = $stmt->fetchObject();
	
	$case_count = $cases->case_count;
	
	//completeds
	$sql = "SELECT COUNT(cpointer) case_count
	FROM `ikase_" . $data_source . "`.`cse_case` ggc
	WHERE 1";
	//echo $sql . "\r\n<br>";
	//die();
	$stmt = DB::run($sql);
	$cases = $stmt->fetchObject();
	
	$completed_count = $cases->case_count;
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	$success = array("success"=> array("text"=>"done", "duration"=>$total_time, "completed_count"=>$completed_count, "case_count"=>$case_count));
	echo json_encode($success);
	
	$sql = "SELECT gcase.* 
	FROM `" . $GLOBALS['GEN_DB_NAME'] . "`.`case` gcase
	LEFT OUTER JOIN `ikase_" . $data_source . "`.`cse_case` ggc
	ON gcase.CASENO = ggc.cpointer
	WHERE 1
	AND ggc.case_id IS NULL
	LIMIT 0, 1";
	//echo $sql . "\r\n<br>";
	//	#AND CASENO = 19493 OR CASENO = 19490 OR CASENO = 19454
	//die();
	$cases = DB::select($sql);
	
	if (count($cases) > 0) {
		echo "<script language='javascript'>parent.runMain(" . $completed_count . "," . $case_count . ")</script>";
	}
} catch(PDOException $e) {
	//echo $sql . "\r\n<br>";
	$error = array("error"=> array("text"=>$e->getMessage()));
	die( json_encode($error));
}
/*
SELECT * 
FROM a1.casecard acc
INNER JOIN a1.card ac
ON acc.CARDCODE = ac.CARDCODE
INNER JOIN a1.card2 ac2
ON ac.FIRMCODE = ac2.FIRMCODE
WHERE CASENO = 9662;

SELECT DISTINCT * 
FROM a1.injury
WHERE CASENO = 9662

//activity categories?
SELECT * FROM a1.actdeflt;

SELECT * FROM a1.bill1
WHERE CASENO = 9662

SELECT * FROM a1.bill2
WHERE CASENO = 9662

//events
SELECT * FROM a1.cal1;
//event assignee
SELECT * FROM a1.cal2;

//liens
SELECT * FROM a1.cc1;

//document track
SELECT * FROM a1.doctrk1;

//outgoing emails
SELECT * FROM a1.email;

//attachment
SELECT * FROM a1.email2;

//injury and bodyparts
SELECT DISTINCT * FROM a1.injury;


//intake
SELECT DISTINCT * FROM a1.intake;

//list of letters
SELECT DISTINCT * FROM a1.letters;

//deletes
SELECT * FROM a1.logtk;

//scans
SELECT * FROM a1.scanfi1;

//scan directories
SELECT * FROM a1.scanprof;

//users
SELECT * FROM a1.staff;

//tasks
SELECT * FROM a1.tasks;
SELECT * FROM a1.tasksbk;

//adhoc fields for forms
SELECT * FROM a1.user2;

*/

?>
</body>
</html>

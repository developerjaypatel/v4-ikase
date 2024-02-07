<?php
//die("here");
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL ^ E_DEPRECATED);
//die("here");
function getConnection() {
	$dbhost = "kustomweb.xyz";
	$dbuser="root";
	$dbpass="admin527#";
	$dbname="ikase";
	//die(print_r($_SESSION));
	if (isset($_SESSION['user_data_source'])){
		if ($_SESSION['user_data_source']!="") {
			$dbname .= "_" . $_SESSION['user_data_source'];
		}
	}
	
	$dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);            
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbh;
}
function getConnectionSave() {
	$dbhost = "kustomweb.xyz";
	$dbuser="root";
	$dbpass="admin527#";
	$dbname="ikase_nat2";
	//die(print_r($_SESSION));
	if (isset($_SESSION['user_data_source'])){
		if ($_SESSION['user_data_source']!="") {
			$dbname .= "_" . $_SESSION['user_data_source'];
		}
	}
	
	$dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);            
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbh;
}
function scrapeEams($adj_number, $claim_number) {
	//session_write_close();
	
	$search_first_name = "";
	$search_last_name = "";
	$search_dob = "";
	$search_city = "";
	$search_zip_code = "";
	$email="";
	//die("here");
	//need adj
	$first_three = substr($adj_number, 0, 3);
	if ($first_three!="ADJ") {
		die(json_encode(array("error"=>$adj_number . " is not a valid ADJ number.")));
	}
	//get customer info
	try {
		$db =  getConnection();
		//die("here");
		$customer_id = 1227;   //$_SESSION['user_customer_id'];
		//lookup the customer name
		$sql_customer = "SELECT cus_name_first, cus_name_last, cus_email, firm_name
		FROM  `ikase`.`cse_customer` cus
		LEFT OUTER JOIN `ikase`.`cse_eams_reps` cer
		ON cus.eams_no = cer.eams_ref_number
		WHERE customer_id = :customer_id";
		//die("here");
		$stmt = $db->prepare($sql_customer);
		//die("here");
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$customer = $stmt->fetchObject();
		
		
		//die(print_r($customer));
		
		$first_name = "Danny"; //strtoupper($customer->cus_name_first);
		$last_name = "Torres"; //strtoupper($customer->cus_name_last);
		$firm_name = "NATIONAL TRANSLATION SERVICES"; //$customer->firm_name;
		
		//$firm_name = $customer->firm_name;
		if ($email=="") {
			$email = "nick@kustomweb.com";
		}
		$url = 'https://eams.dwc.ca.gov/WebEnhancement/InformationCapture';
		$fields = array("UAN"=>"", "requesterFirstName"=>$first_name, "requesterLastName"=>$last_name, "email"=>$email, "reason"=>"CASESEARCH");
		// die(print_r($fields));
		$fields_string = "";
		foreach($fields as $key=>$value) { 
			$fields_string .= $key.'='.$value.'&'; 
		}
		rtrim($fields_string, '&');
		$timeout = 5;
		
		//open connection
		$ch = curl_init();
		
		//set the url, number of POST vars, POST data
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_HEADER, false); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_COOKIEFILE, "cookies.txt");
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_POST, count($fields_string));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		//curl_setopt($ch,CURLOPT_FOLLOWLOCATION,TRUE);
		
		//execute post
		$result = curl_exec($ch);
		//preg_match_all('|Set-Cookie: (.*);|U', $result, $matches);
		//die($result);
		if($result === false) {
			echo "Error Number:".curl_errno($ch)."<br>";
			echo "Error String:".curl_error($ch);
			die();
		}
		$headers = curl_getinfo($ch);
		// echo "first call out";
		//die(print_r($headers));
		if ($headers["http_code"]==500) {
			$result = json_encode(array("error"=>"EAMS is down"));
			die($result);
		}
		
		/*if ($headers["http_code"]==200) {
			$result = json_encode(array("error"=>"Here"));
			//die($result);
		}*/
		if ($headers["http_code"]==302) {
		   //redirect
			//die($headers["redirect_url"]);
			//$url = $headers["redirect_url"];
			
			$url = "https://eams.dwc.ca.gov/WebEnhancement/InjuredWorkerFinder";
			$fields = array("caseNumber"=>$adj_number, "firstName"=>$search_first_name, "lastName"=>$search_last_name, "dateOfBirth"=>$search_dob, "city"=>$search_city, "zipCode"=>$search_zip_code);
			//die(print_r($fields));
			$fields_string = "";
			foreach($fields as $key=>$value) { 
				$fields_string .= $key.'='.$value.'&'; 
			}
			rtrim($fields_string, '&');
			//$url .= "?" . http_build_query($fields);
			
			curl_setopt($ch,CURLOPT_URL,$url);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
			curl_setopt($ch,CURLOPT_HEADER, false); 
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt ($ch, CURLOPT_COOKIEFILE, "cookies.txt");
			curl_setopt($ch, CURLOPT_POST, count($fields));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
				
			$result = curl_exec($ch);
			
			if($result === false) {
				echo "Error Number:".curl_errno($ch)."<br>";
				echo "Error String:".curl_error($ch);
			}
			$headers = curl_getinfo($ch);
			//die(print_r($headers));
			$doc = new DOMDocument();
			@$html = $doc->loadHTML($result);
		
			$ths = $doc->getElementsByTagName("th");
			$arrFields1 = array();
			foreach($ths as $cell_index=>$th) {
				if (trim($th->nodeValue) != "") {		
					//echo trim($th->nodeValue) . "<br />\r\n";
					$arrFields1[] = trim($th->nodeValue);
				}
			}
			$tds = $doc->getElementsByTagName("td");
			$arrFirstValues = array();
			foreach($tds as $cell_index=>$td) {
				if (trim($td->nodeValue) != "" && $td->nodeValue != "View events") {
					$arrFirstValues[] = trim($td->nodeValue);
					//echo trim($td->nodeValue) . "<br />";
				}
			}
			
			//now let's get into scraping itself
			$url = "";
			$regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
			if(preg_match_all("/$regexp/siU", $result, $matches, PREG_SET_ORDER)) {
				foreach($matches as $match) {
				  // $match[2] = link address
				  if ($match[3] == "View cases") {
					  //that's the one that leads to the actual data
					  $url = "https://eams.dwc.ca.gov/WebEnhancement/" . str_replace("'", "", $match[2]);
					  break;
				  }
				}
			}
			if ($url == "") {
				die(json_encode(array("error"=>"Not Found")));
			}
			
			//now process the data
			//$url = "https://eams.dwc.ca.gov/WebEnhancement/CaseFinder?partyId=-5205632355686940672&firstName=MARISSA&lastName=MORALES&caseNumber=ADJ9881786";
			
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_COOKIE, "cookies.txt");
			$result = curl_exec($ch);
			
			if($result === false) {
				echo "Error Number:".curl_errno($ch)."<br>";
				echo "Error String:".curl_error($ch);
			}
			$number_employers = 0;
			//there might be more than one employer listed			
			$doc = new DOMDocument();
			@$html = $doc->loadHTML($result);
			//die(print_r($arrValues));
			$ths = $doc->getElementsByTagName("tr");
			$arrFields = array();
			foreach($ths as $cell_index=>$th) {
				if (trim($th->nodeValue) != "") {
					$arrFields[] = trim($th->nodeValue);
				}
			}
			if(count($arrFields) > 2) {
				$number_employers += count($arrFields) - 2;
			}
		
			$headers = curl_getinfo($ch);
			
			//get page 1
			$url = "https://eams.dwc.ca.gov/WebEnhancement/CaseDetailFinder?arrayIndex=0&startIndex=0";
			
			$timeout = 5;
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_COOKIE, "cookies.txt");
			$result = curl_exec($ch);
			
			if($result === false) {
				echo "Error Number:".curl_errno($ch)."<br>";
				echo "Error String:".curl_error($ch);
			}
			
			$headers = curl_getinfo($ch);
			$blnBody = true;
			if (strpos($result, "Body Part")===false) {
				$blnBody = false;
			}
			$doc = new DOMDocument();
			@$html = $doc->loadHTML($result);
			
			//deu DEU (Disability Evaluation Unit) ratings are requested
			$deupos = strpos($result, '<input id="deuind"');
			$deu = "N";
			if ($deupos!==false) {
			//if ($customer_id == 1033) {
				$endpos = strpos($result, "></td>", $deupos);
				//echo "pos:" . $deupos . " = " . $endpos . "<br />";
				$deuinput = trim(substr($result, $deupos, ($endpos - $deupos)));
				$arrDEU = explode(" ", $deuinput);
				//die(print_r($arrDEU));
				if (count($arrDEU) > 0) {
					if ($arrDEU[count($arrDEU)-1]=="CHECKED") {
						$deu = "Y";
					}
				}
			//}
			}
			$ths = $doc->getElementsByTagName("th");
			$arrFields = array();
			foreach($ths as $cell_index=>$th) {
				if (trim($th->nodeValue) != "") {
					$arrFields[] = trim($th->nodeValue);
				}
			}
			//do we have next hearing
			$blnHearing = (in_array("Next hearing date/time", $arrFields));
			
			$tds = $doc->getElementsByTagName("td");
			
			$arrValues = array();
			$arrDetails = array();
			$start_index = 0;			
			foreach($tds as $cell_index=>$td) {
				if (strpos(trim($td->nodeValue), "1 2") > -1) {
					//echo "strpos: " . strpos(trim($td->nodeValue), "1 2") . "\r\n";
					$arrDetailIndexes = explode(" ",$td->nodeValue);
					foreach($arrDetailIndexes as $start_index) { 
						$start_index = str_replace("\r\n", "", $start_index);
						if ($start_index!="") {
							$arrDetails[] = ($start_index - 1) * 5;
						}
					}
					$td->nodeValue = "";
					
				}
			
				if (trim($td->nodeValue) != "" && $td->nodeValue != "View events") {
					$arrValues[] = trim($td->nodeValue);
				}
			}
			
			foreach($arrDetails as $startIndex) {
				if ($startIndex==0) {
					continue;
				}
				$url = "https://eams.dwc.ca.gov/WebEnhancement/CaseDetailFinder?arrayIndex=0&startIndex=" . $startIndex;
				
				$timeout = 5;
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($ch, CURLOPT_COOKIE, "cookies.txt");
				$result = curl_exec($ch);
				
				if($result === false) {
					echo "Error Number:".curl_errno($ch)."<br>";
					echo "Error String:".curl_error($ch);
				}
				
				$headers = curl_getinfo($ch);
				
				$doc = new DOMDocument();
				@$html = $doc->loadHTML($result);
				$tds = $doc->getElementsByTagName("td");
		
				foreach($tds as $cell_index=>$td) {
				
					if (strpos(trim($td->nodeValue), "1 2") > -1) {
						$td->nodeValue = "";
					}
					if (trim($td->nodeValue) != "" && $td->nodeValue != "View events") {
						$trim_value = trim($td->nodeValue);
						if (!in_array($trim_value, $arrValues)) {
							$arrValues[] = trim($td->nodeValue);
						}
					}
				}
			}

			$arrData = array();
			//from first scrape
			$client_first_name = $arrFirstValues[6]; $arrData["first_name"] = $client_first_name;
			$client_last_name = $arrFirstValues[7]; $arrData["last_name"] = $client_last_name;
			$client_city  = $arrFirstValues[8]; $arrData["city"] = $client_city;
			$client_zip  = $arrFirstValues[9]; $arrData["zip"] = $client_zip;
			$arrData["deu"] = $deu;
			$person_id = -1;
			//die(print_r($arrFirstValues));
			
			if ($client_first_name!="" && $client_last_name!="" && $client_city!="" && $client_zip!="") {
				//is this person in our rolodex already
				$sql_person = "SELECT person_id, person_uuid FROM `cse_person` WHERE 1 AND `first_name` = :first_name AND `last_name` = :last_name AND `city` = :city AND `zip` = :zip AND `person_uuid` = `parent_person_uuid` AND `customer_id` = " . $customer_id;
				// echo $sql_person; die();
				$stmt = $db->prepare($sql_person);
				
				$stmt->bindParam("first_name", $client_first_name);
				$stmt->bindParam("last_name", $client_last_name);
				$stmt->bindParam("city", $client_city);
				$stmt->bindParam("zip", $client_zip);
				$stmt->execute();
				$person = $stmt->fetchObject();
				
				$arrPreviousCases = array();
				if (isset($person->person_id)) {
					//die(print_r($person));
					$person_id = $person->person_id;
					//let's look up associated cases
					$sql_cases = "SELECT inj.injury_id id, ccase.case_id, inj.injury_number, 
					ccase.case_uuid uuid, ccase.case_number, ccase.source, ccase.case_name,
					inj.injury_number, inj.adj_number, ccase.rating, 
					IF (DATE_FORMAT(ccase.case_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.case_date, '%m/%d/%Y')) case_date , 
					IF (DATE_FORMAT(app.dob, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(app.dob, '%m/%d/%Y')) dob, app.dob app_dob, 
					IF (app.ssn = 'XXXXXXXXX', '', app.ssn) ssn,
					IFNULL(employer.`corporation_id`,-1) employer_id, employer.`corporation_uuid` employer_uuid, employer.`company_name` employer, employer.`full_address` employer_full_address,
					IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')) start_date, 
					IF (DATE_FORMAT(inj.end_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.end_date, '%m/%d/%Y')) end_date, inj.ct_dates_note,
					IF (inj.occupation IS NULL, '' , inj.occupation) occupation,
					CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`,' - ', IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y'))) `name`
					FROM cse_case ccase
					INNER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N'
					INNER JOIN ";
			
if (isset($_SESSION['user_customer_id']) && $_SESSION['user_customer_id']==1033) { 
	$sql_cases .= "(" . SQL_PERSONX . ")";
} else {
	$sql_cases .= "cse_person";
}
$sql_cases .= " app ON ccapp.person_uuid = app.person_uuid
					INNER JOIN  ";
			
if (isset($_SESSION['user_customer_id']) && $_SESSION['user_customer_id']==1033) { 
	$sql_cases .= "(" . SQL_PERSONX . ")";
} else {
	$sql_cases .= "cse_person";
}
$sql_cases .= " rolodex ON (app.parent_person_uuid = rolodex.person_uuid AND rolodex.person_uuid = rolodex.parent_person_uuid)
					LEFT OUTER JOIN `cse_case_corporation` ccorp
					ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
					LEFT OUTER JOIN `cse_corporation` employer
					ON ccorp.corporation_uuid = employer.corporation_uuid
					INNER JOIN `cse_case_injury` cinj
					ON ccase.case_uuid = cinj.case_uuid
					INNER JOIN `cse_injury` inj
					ON cinj.injury_uuid = inj.injury_uuid AND inj.deleted = 'N' AND cinj.deleted = 'N'
					WHERE rolodex.person_id = " . $person->person_id . "
					AND ccase.deleted = 'N'
					AND ccase.customer_id = " . $customer_id;
					
					$stmt = $db->query($sql_cases);
					$kases = $stmt->fetchAll(PDO::FETCH_OBJ);
					//die(print_r($kases));
					foreach ($kases as $kase) {
						
						if ($kase->case_name!="") {
							$kase->name = $kase->case_name;
						} else {
							$kase->name = str_replace(" - 00/00/0000", "", $kase->name);
						}
						
						//$kase->name = str_replace(" - 00/00/0000", "", $kase->name);
						$kase->start_date = str_replace("00/00/0000", "", $kase->start_date);
						$kase->end_date = str_replace("00/00/0000", "", $kase->end_date);
						//might be current one, just an update then
						$same_adj_number = "N";
						if ($kase->adj_number==$adj_number) {
							$same_adj_number = "Y";
						}
						$arrPreviousCases[] = array("case_id"=>$kase->case_id, "case_number"=>$kase->case_number, "injury_number"=>$kase->injury_number, "adj_number"=>$kase->adj_number, "same_adj_number"=>$same_adj_number, "case_name"=>$kase->name, "doi_start"=>$kase->start_date, "doi_end"=>$kase->end_date);
					}
				}
			}
			$arrData["person_id"] = $person_id;
			
			//from details scrape
			$adj_number = $arrValues[0]; $arrData["adj_number"] = $adj_number;
			$venue = substr($arrValues[1], 0, 3); $arrData["venue"] = $venue;
			$doi =  $arrValues[2];
			//ct
			$arrDOI = explode(" - ", $doi);
			$start_date = $arrDOI[0]; $arrData["start_date"] = $start_date;
			$end_date = "0000-00-00";
			if (count($arrDOI) == 2) {
				$end_date = $arrDOI[1];
			}
			$arrData["end_date"] = $end_date;
			 
			$judge = $arrValues[3]; $arrData["judge"] = $judge;
			$employer = $arrValues[4]; $arrData["employer"] = $employer;
			//
			//body parts
			$arrBodyParts = array();
			$arrBodyPartsIndexes = array();
			if ($blnBody) {
				$intCounter = 1;
				for($idx = 0; $idx < count($arrValues); $idx++) {
					//are we dealing with a body part
					if (strpos($arrValues[$idx], "Body Part") === false) {
						continue;
					}
					$arrBodyParts[] = array("name"=>$arrValues[$idx +1]);
					$intCounter++;
					
					$arrBodyPartsIndexes[] = $idx;
					$arrBodyPartsIndexes[] = $idx + 1;
				}
			}
			
			//print_r($arrBodyPartsIndexes);
			//die(print_r($arrValues));
			$arrHearings = array();
			$hearing_start_idx = 0; 
			$party_idx = 5;
			
			//start with the value that has a date'
			if ($blnHearing) {
				if (count($arrBodyPartsIndexes) > 0) {
					$bodyparts_index = $arrBodyPartsIndexes[0];
					if ($bodyparts_index > 5) {
						
						for($intV=3;$intV<count($arrValues);$intV++) {
							$str = $arrValues[$intV];
							if (strtotime($str) !== false) {
								//echo $intV . " - " . $str . " - " . strtotime($str) . "\r\n";
								$hearing_start_idx = $intV;
								break;
							}
						}
					}
				} else {
					$hearing_start_idx = 6;
					$bodyparts_index = 11;
					
					//double check on date
					$hearing_datetime = $arrValues[$hearing_start_idx];
					$hearing_formatted = date("m/d/Y H:i:s", strtotime($hearing_datetime));
					
					if ($hearing_datetime!=$hearing_formatted) {
						//not a date
						for($i = 0; $i < 11; $i++) {
							$hearing_datetime = $arrValues[$i];
							$hearing_formatted = date("m/d/Y H:i:s", strtotime($hearing_datetime));
							
							if ($hearing_datetime==$hearing_formatted) {
								$hearing_start_idx = $i;
								$bodyparts_index = $i + 5;
								break;
							}
						}
					}
				}
				
				//let's get hearings
				for($idx = $hearing_start_idx; $idx < $bodyparts_index; $idx = $idx + 5) { 
					$hearing_datetime = $arrValues[$idx];
					$hearing_type = $arrValues[$idx + 1];
					$hearing_location = $arrValues[$idx + 2];
					$hearing_judge = $arrValues[$idx + 3];
					
					$driver_case = trim($arrValues[$idx + 4]);
					$arrHearings[] = array("date"=>$hearing_datetime, "type"=>$hearing_type, "location"=>$hearing_location, "judge"=>$hearing_judge, "driver_case"=>$driver_case);
				}
				//parties start here
				$party_idx = $idx; 
			}
			$first_body_index = $party_idx;
			$last_body_index = $party_idx;
			if (count($arrBodyPartsIndexes) > 0) {
				//last body part is where we start
				$first_body_index = $arrBodyPartsIndexes[0];
				$last_body_index = $arrBodyPartsIndexes[count($arrBodyPartsIndexes) - 1];	
			}
			
			if (count($arrBodyPartsIndexes) > 0) {
				foreach($arrBodyPartsIndexes as $array_index=>$part_index) {
					unset($arrValues[$part_index]);
				}
				//re index
				$arrValues = array_values($arrValues);
			}

			//there might be more than 1 employer..., display them later as part of parties
			$arrParties = array();
			$arrRoles = array();
			
			if ($first_body_index!= $party_idx && $first_body_index > 5) {
				$party_idx += ($first_body_index - 5); 
			} else {
				//we may have counted them anyway
				if ($number_employers > 1) {
					$party_idx += ($number_employers - 1);
				}
			}
			//reserved words
			$arrReserved = array("CLAIMS ADMINISTRATOR", "EMPLOYER", "LAW FIRM", "LIEN CLAIMANT");
			if (in_array($arrValues[$party_idx], $arrReserved)) {
				//i went too far, go back one
				$party_idx--;
			}
			if (count($arrValues) > $party_idx - 1) {
				//parties
				for($idx = $party_idx; $idx < count($arrValues); $idx = $idx + 3) {
					//die(print_r($arrValues));
					$partie_name = $arrValues[$idx];
					$partie_role = $arrValues[$idx + 1];
					$partie_address = $arrValues[$idx + 2];
					if (!preg_match('~[0-9]+~', $partie_address)) {
						$partie_address = "";
					}
					//die($partie_name . " - " . $partie_role . " - " . $partie_address);
					$state = "";
					$city = "";
					$zip = "";
					
					if (trim($partie_address)!="") {
						$arrAddress = explode("  ", $partie_address);
						if (count($arrAddress) > 1) {
							$arrCity = explode(" ", $arrAddress[count($arrAddress)-1]);
							$zip = $arrCity[count($arrCity)-1];
							unset ($arrCity[count($arrCity)-1]);
							$state = $arrCity[count($arrCity)-1];
							unset ($arrCity[count($arrCity)-1]);
							$city = trim(implode(" ", $arrCity));
						}
					} 
					$case_firm = "N";
					$eams_ref_number = "";
					//echo $partie_role . " - " . $partie_name . " == " . $partie_address . "\r\n";
					//if it's a law firm, 1) is it us, 2) what is it's eams no
					if ($partie_role=="LAW FIRM"){
						if ($partie_name==$firm_name) {
							$case_firm = "Y";
						} else {
							//let's get the eams number for this firm
							$sql_lookup = "SELECT eams_ref_number FROM ikase.cse_eams_reps 
							WHERE firm_name = :partie_name";
							
							$stmt = $db->prepare($sql_lookup);
							$stmt->bindParam("partie_name", $partie_name);
							
							$stmt->execute();
							$eams_lookup = $stmt->fetchObject();
							if (isset($eams_lookup->eams_ref_number)) {
								$eams_ref_number = $eams_lookup->eams_ref_number;
							} else {
								//ADD THE EAMS INFO TO OUR DATABASE
								//OR UPDATE THE LIST FROM EAMS
							}
						}
					}
					if ($partie_role=="INSURANCE COMPANY") {
						$sql_lookup = "SELECT eams_ref_number 
						FROM ikase.cse_eams_carriers
						WHERE firm_name = :partie_name";
							
						$stmt = $db->prepare($sql_lookup);
						$stmt->bindParam("partie_name", $partie_name);
						
						$stmt->execute();
						$eams_lookup = $stmt->fetchObject();
						if (isset($eams_lookup->eams_ref_number)) {
							$eams_ref_number = $eams_lookup->eams_ref_number;
						}
					}
					
					if (isset($arrRoles[$partie_role])) {
						$arrRoles[$partie_role] = $arrRoles[$partie_role] + 1;
					} else {
						$arrRoles[$partie_role] = 1;
					}
					
					// $arrParties[] = array("name"=>$partie_name, "role"=>$partie_role, "address"=>$partie_address, "street"=>$arrAddress[0], "city"=>$city, "state"=>$state, "zip"=>$zip, "case_firm"=>$case_firm, "eams_ref_number"=>$eams_ref_number);
					$arrParties[str_replace(" ", "_", $partie_role)][] = array("name"=>$partie_name, "role"=>$partie_role, "address"=>$partie_address, "street"=>$arrAddress[0], "city"=>$city, "state"=>$state, "zip"=>$zip, "case_firm"=>$case_firm, "eams_ref_number"=>$eams_ref_number);
				}
			}
			//sort by role
			// usort($arrParties, 'sortByOption');
			// die(print_r($arrParties));
			$arrRoleCount = array();
			foreach($arrRoles as $role_name=>$role_count) {
				$arrRoleCount[] = array("name"=>$role_name, "count"=>$role_count);
			}
			//die(print_r($arrRoleCount));
			//events
			$url = "https://eams.dwc.ca.gov/WebEnhancement/CaseEventFinder?arrayIndex=0&startIndex=0";
			
			$timeout = 5;
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_COOKIE, "cookies.txt");
			$result = curl_exec($ch);
			
			if($result === false) {
				echo "Error Number:".curl_errno($ch)."<br>";
				echo "Error String:".curl_error($ch);
			}
			
			$headers = curl_getinfo($ch);
			$doc = new DOMDocument();
			@$html = $doc->loadHTML($result);
		
			$ths = $doc->getElementsByTagName("th");
			$arrFields = array();
			foreach($ths as $cell_index=>$th) {
				if (trim($th->nodeValue) != "") {
					$arrFields[] = trim($th->nodeValue);
				}
			}
			$tds = $doc->getElementsByTagName("td");
			$arrValues = array();
			foreach($tds as $cell_index=>$td) {
				if (trim($td->nodeValue) != "" && $td->nodeValue != "View events") {
					$arrValues[] = trim($td->nodeValue);
				}
			}
			//might be more than 1 employer
			$event_start_idx = 3;
			if ($hearing_start_idx > 5) {
				$event_start_idx = $hearing_start_idx - 2;
			}
			
			if ($event_start_idx < 1) {
				$event_start_idx = 0;
			}
			if ($party_idx > 5) {
				$event_start_idx = $event_start_idx + ($party_idx - 5);
			}
			
			//fix for reality...
			for($i = count($arrValues) - 1; $i > 0; $i = $i - 3) {
				$event_datetime = $arrValues[$i];
				$event_formatted = date("m/d/Y", strtotime($event_datetime));
				
				if ($event_datetime!=$event_formatted) {
					//echo $i . "]" . $event_datetime . " -- " . $event_formatted . "\r\n";
					$event_start_idx = $i + 1;
					break;
				}
			}
			
			$arrEvents = array();			
			if ($event_start_idx > 2) {
				if (isset($arrValues[$event_start_idx])) {
					for($idx = $event_start_idx; $idx < count($arrValues); $idx = $idx + 3) {
						if (isset($arrValues[$idx]) && isset($arrValues[$idx + 1]) && isset($arrValues[$idx + 2])) {
							//echo "index:" . $idx  . "<br />";
							$arrEvents[] = array("type"=>$arrValues[$idx], "description"=>$arrValues[$idx + 1], "date"=>$arrValues[$idx + 2]);
						}
					}
				}
			}
			/*
			if ($_SERVER['REMOTE_ADDR']=='71.106.134.58') {
				echo "event:" . $event_start_idx . "\r\n";
				print_r($arrValues);
				die(print_r($arrEvents));
			}
			*/
			$arrOutput = array();
			$arrOutput["applicant"] = $arrData;
			$arrOutput["previous_cases"] = $arrPreviousCases;
			$arrOutput["hearings"] = $arrHearings;
			$arrOutput["bodyparts"] = $arrBodyParts;
			$arrOutput["roles"] = $arrRoleCount;
			$arrOutput["parties"] = $arrParties;
			$arrOutput["events"] = $arrEvents;
			
			$result = json_encode($arrOutput);
			//$result = json_encode(array("applicant"=>$arrData, "bodyparts"=>$arrBodyParts, "parties"=>$arrParties, "events"=>$arrEvents));
			
			scrapeSave($result, $claim_number);
			//print_r($result);
			/*
			print_r($arrFirstValues);
			print_r($arrData);
			print_r($arrBodyParts);
			print_r($arrParties);
			print_r($arrEvents);
			*/
		}
		//close connection
		curl_close($ch);
		
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
	}
}
function getState($zip) {
	//$zip = "91331";
	$url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . $zip . "&sensor=true&key=AIzaSyATlRmX2YtxkZc5FrUT9i74BZZGiesxkfU";
	//die($url);
	if ($zip=="") {
		return $zip;
	}
	$homepage = file_get_contents($url);
	$json = json_decode($homepage);
	$state = "";
	//if ($_SERVER['REMOTE_ADDR']=='173.55.229.70') {
		//echo $url . "\r\n";
		$blnStateFound = false;
		//die(print_r($json));
		if (isset($json->results[0])) {
			foreach($json->results[0]->address_components as $component_index=>$address_component) {
				$component_types = $address_component->types;
				if ($component_types[0]=="administrative_area_level_1") {
					$state = $address_component->short_name;
					$blnStateFound = true;
					break;
				}
			}
		}
		if(strlen($state)!=2) {
			//something is wrong?
			//die("s:" . substr($zip, 0, 1));
			if (substr($zip, 0, 1)== "9") {
				$state = "CA";
			}
		}
		//die($state);
		
		return $state;
	/*
	} else {
		return trim($json->results[0]->address_components[3]->short_name);
	}
	*/
	
}

function scrapeSave($eams_info, $claim_number) {
	//session_write_close();
	//get next number, add kase, since doing it through api, use tracking
	//die($eams_info . " - save func");
	//die($claim_number);
	$db = getConnectionSave();
	try {
		//lookup the customer name
		$sql_customer = "SELECT data_source
		FROM  `ikase`.`cse_customer` 
		WHERE customer_id = :customer_id";
		
		$customer_id =  1227; //$_SESSION['user_customer_id'];
		$stmt = $db->prepare($sql_customer);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$customer = $stmt->fetchObject();
		//die(print_r($customer));
		$data_source = $customer->data_source;
		
		//bodyparts
		$sql_bp = "SELECT * 
		FROM `cse_bodyparts` 
		WHERE 1
		ORDER BY code ASC";
		$stmt = $db->prepare($sql_bp);
		$stmt = $db->query($sql_bp);
		$bodyparts = $stmt->fetchAll(PDO::FETCH_OBJ);
		$arrBodyParts = array();
		foreach($bodyparts as $bodypart){
			$arrBodyParts[$bodypart->code] = $bodypart->bodyparts_uuid;
		}
		
		//venues
		$sql_venue = "SELECT * 
		FROM `ikase`.`cse_venue` 
		WHERE 1
		ORDER BY venue ASC";
		$stmt = $db->prepare($sql_venue);
		$stmt = $db->query($sql_venue);
		$venues = $stmt->fetchAll(PDO::FETCH_OBJ);
		$arrVenues = array();
		foreach($venues as $venue){
			$arrVenues[$venue->venue_uuid] = $venue->venue_abbr;
		}
		
		//we need settings for the next case number
		$sql = "SELECT cs.*, cs.setting_id id, cs.setting_uuid uuid
		FROM  `cse_setting` cs
		WHERE 1
		AND `cs`.customer_id = " . $customer_id . "
		AND cs.setting LIKE 'case_number_%'
		ORDER BY cs.`category`";
		
		/*
		AND `csc`.customer_uuid = '" . $customer_id . "'
		INNER JOIN `cse_setting_customer` csc
		ON cs.setting_uuid = csc.setting_uuid
		
		*/
		$stmt = $db->query($sql);
		$customer_settings = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		$arrSettingValues = array();
		$arrSettings = array();
		foreach($customer_settings as $setting_info) {
			$category = $setting_info->category;
			$setting = $setting_info->setting;
			$setting_value = $setting_info->setting_value;
			$arrSettings[$setting] = $setting_value;
			$arrSettingValues[$category][$setting] = $setting_value;
		}
		$case_number_prefix = "";
		if (isset($arrSettings["case_number_prefix"])) {
			$case_number_prefix = $arrSettings["case_number_prefix"];
		}
		if (isset($arrSettings["case_number_next"])) {
			$case_number_next = $arrSettings["case_number_next"];
		} else {
			
		}
		$case_number = $case_number_prefix . $case_number_next;
		
		
		$eams_info_json = json_decode($eams_info, true);
	//die(print_r($eams_info_json) . " - save func");
		//passed variables
		//$adj_number = passed_var("scrape_adj_number", "post");
		$adj_number = $eams_info_json["applicant"]["adj_number"];
		//die($adj_number . " - adj");
		
		$venue = $eams_info_json["applicant"]["venue"];
		$deu = $eams_info_json["applicant"]["deu"];
		if ($deu=="") {
			$deu = "N";
		}
		$first_name = addslashes($eams_info_json["applicant"]["first_name"]);
		$last_name = addslashes($eams_info_json["applicant"]["last_name"]);
		$employer_eams = addslashes($eams_info_json["applicant"]["employer"]);
		$case_name = $first_name . " " . $last_name . " vs " . $employer_eams;
		//die($case_name . " - case");
		$city = addslashes($eams_info_json["applicant"]["city"]);
		$zip = $eams_info_json["applicant"]["zip"];
		$arrZip = explode("-", $zip);
		$zip = $arrZip[0];
		
		$state = getState($zip);
		//$partie_count = count(["parties"]);
		//$partie_count_array = $eams_info_json->parties;
		//die(print_r($partie_count_array));
		$partie_count = count($eams_info_json["parties"]);
		//print_r($obj->Result);
		//die($partie_count . " - count");
		//foreach ($eams_info_json->parties as $item) {
		//	$partie_count = count($item["parties"]) . '<br />';
		//}
		//die($city . "," . $state);
		//die($partie_count . " - party count");
		//$partie_count = passed_var("partie_count", "post");
		
		$start_date = $eams_info_json["applicant"]["start_date"];
		$end_date = $eams_info_json["applicant"]["end_date"];
		//bodypart $bodypart = passed_var("bodypart" . $int, "post");
		//partie
		//partie_count
		
		$parent_venue_uuid = array_search($venue, $arrVenues);
		$case_uuid = uniqid("KS", false);
		$case_date = date("Y-m-d");
		$case_status = "open";
		//now the kase
		//die("here");
		$sql = "INSERT INTO `cse_case` (case_uuid, case_number, case_name, adj_number, case_date, case_type, venue, case_status, submittedOn, customer_id) 
	VALUES ('" . $case_uuid . "', '" . $case_number . "', '" . $case_name . "', '" . $adj_number . "', '" . $case_date . "', 'WCAB', '" . $parent_venue_uuid . "', '" . $case_status . "', '" . $case_date . "', " . $customer_id . ")";
		//echo $sql . "\r\n\r\n"; 
		
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		//$case_id  = 99;
		
		$case_id = $db->lastInsertId();
		//trackKase("insert", $case_id);
		
		
		$sql = "UPDATE cse_setting cset
		SET cset.setting_value = cset.setting_value + 1
		WHERE cset.setting = 'case_number_next'
		AND cset.customer_id = " . $customer_id;
		
		//echo $sql . "\r\n\r\n";
		
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		//deu note
		
		
		//venue
		//now we have to attach the venue to the case
		$case_venue_uuid = uniqid("KS", false);
		$last_updated_date = date("Y-m-d H:i:s");
		
		$sql = "INSERT INTO cse_case_venue (`case_venue_uuid`, `case_uuid`, `venue_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $case_venue_uuid  . "', '" . $case_uuid . "', '" . $parent_venue_uuid . "', 'main', '" . $last_updated_date . "', '4700', '" . $customer_id . "')";
				
		//echo $sql . "\r\n\r\n";
		
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		$table_uuid = uniqid("KS", false);
		$sql = "INSERT INTO cse_case_corporation (`case_corporation_uuid`, `case_uuid`, `corporation_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $case_venue_uuid  . "', '" . $case_uuid . "', '" . $table_uuid . "', 'venue', '" . $last_updated_date . "', '4700', '" . $customer_id . "')";
		
		//echo $sql . "\r\n\r\n";
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		//now save the venue as corporation for parties
		$sql = "INSERT INTO cse_corporation (`corporation_uuid`, `parent_corporation_uuid`, `company_name`, `type`, `aka`, `employee_phone`, `full_address`, `street`, `city`, `state`, `zip`, `salutation`, `customer_id`, `copying_instructions`) 
		SELECT '" . $table_uuid . "', '" . $parent_venue_uuid . "', `venue`, 'venue', `venue_abbr`, `phone`, CONCAT(`address1`, ',', `address2`,',', `city`,' ', `zip`) full_address, CONCAT(`address1`,',', `address2`) street, `city`,'CA', `zip`, 'Your Honor', " . $customer_id . ", ''  
		FROM `cse_venue`
		WHERE venue_uuid = '" . $parent_venue_uuid . "'";
		
		$stmt = $db->prepare($sql);  
		$stmt->execute();

		$table_name = "corporation";
		$case_table_uuid = uniqid("KA", false);
		$attribute_1 = "main";
		$last_updated_date = date("Y-m-d H:i:s");
		$sql = "INSERT INTO cse_case_" . $table_name . " (`case_" . $table_name . "_uuid`, `case_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
	VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', '" . $attribute_1 . "', '" . $last_updated_date . "', '4700', '" . $customer_id . "')";
				
		//echo $sql . "\r\n\r\n";
		
		$stmt = $db->prepare($sql);  
		$stmt->execute();

		//applicant
		$sql = "SELECT pers.*, pers.person_id id , pers.person_uuid uuid 
		FROM `cse_person` pers 
		WHERE 1 
		AND pers.first_name = '" . $first_name . "'
		AND pers.last_name = '" . $last_name . "'
		AND pers.`city` = '" . $city . "'
		AND pers.`state` = '" . $state . "'
		AND pers.`zip` = '" . $zip . "'
		AND pers.customer_id = " . $customer_id . "
		AND pers.person_uuid = pers.parent_person_uuid
		AND pers.deleted = 'N'";
		//die($sql);
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$person = $stmt->fetchObject();
		
		//die(print_r($person));
		$table_uuid = uniqid("KA", false);
		
		if (($_SESSION['user_customer_id']==1033)) {
			$arrEncrypted = array("full_name", "company_name", "first_name", "middle_name", "last_name", "aka", "preferred_name", "full_address", "street", "suite", "suite", "email", "fax", "phone", "work_phone", "cell_phone", "other_phone", "work_email", "dob", "ssn", "license_number", "ref_source", "salutation", "birth_state", "birth_city", "spouse", "spouse_contact", "emergency", "emergency_contact");
			
			$arrFields = array("`full_name`", "`first_name`", "`last_name`", "`city`", "`state`", "`zip`", "`full_address`");
			$arrAdditionalFields = array();
			
			foreach($arrEncrypted as $encrypted) {
				if (!in_array("`" . $encrypted . "`", $arrFields)){
					$arrFields[] = "`" . $encrypted . "`";
					$arrAdditionalFields[] = "`" . $encrypted . "`";
					$arrSet[] = " AES_ENCRYPT('', '" . CRYPT_KEY . "')";
				}
			}
		}
		if (is_object($person)) {
			$parent_table_uuid = $person->person_uuid;
		} else {
			$parent_table_uuid = uniqid("RP", false);
			
			if (($_SESSION['user_customer_id']==1033)) {		
				$sql = "INSERT INTO `cse_personx` (`personx_uuid`, `customer_id`, `full_name`, `first_name`, `last_name`, `city`, `state`, `zip`, `full_address`, `parent_personx_uuid`, " . implode(", ", $arrAdditionalFields) . ") 
			VALUES('" . $parent_table_uuid . "', '" . $customer_id . "', AES_ENCRYPT('" . addslashes($first_name . " " . $last_name) . "', '" . CRYPT_KEY . "'), AES_ENCRYPT('" . addslashes($first_name) . "', '" . CRYPT_KEY . "'), AES_ENCRYPT('" . addslashes($last_name) . "', '" . CRYPT_KEY . "'), '" . $city . "', '" . $state . "', '" . $zip . "', AES_ENCRYPT('" . addslashes($city . ", " . $state . " " . $zip) . "', '" . CRYPT_KEY . "'), '" . $parent_table_uuid . "', " . implode(", ", $arrSet) . ")";
			} else {
				$sql = "INSERT INTO `cse_person` (`person_uuid`, `customer_id`, `full_name`, `first_name`, `last_name`, `city`, `state`, `zip`, `full_address`, `parent_person_uuid`) 
			VALUES('" . $parent_table_uuid . "', '" . $customer_id . "', '" . $first_name . " " . $last_name . "', '" . $first_name . "', '" . $last_name . "', '" . $city . "', '" . $state . "', '" . $zip . "', '" . $city . ", " . $state . " " . $zip . "', '" . $parent_table_uuid . "')";
			}
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			
		}
		if (($_SESSION['user_customer_id']==1033)) {
			$sql = "INSERT INTO `cse_personx` (`personx_uuid`, `customer_id`, `full_name`, `first_name`, `last_name`, `city`, `state`, `zip`, `full_address`, `parent_personx_uuid`, " . implode(", ", $arrAdditionalFields) . ") 
			VALUES('" . $table_uuid . "', '" . $customer_id . "', AES_ENCRYPT('" . addslashes($first_name . " " . $last_name) . "', '" . CRYPT_KEY . "'), AES_ENCRYPT('" . addslashes($first_name) . "', '" . CRYPT_KEY . "'), AES_ENCRYPT('" . addslashes($last_name) . "', '" . CRYPT_KEY . "'), '" . $city . "', '" . $state . "', '" . $zip . "', AES_ENCRYPT('" . addslashes($city . ", " . $state . " " . $zip) . "', '" . CRYPT_KEY . "'), '" . $parent_table_uuid . "', " . implode(", ", $arrSet) . ")";
		} else {
			$sql = "INSERT INTO `cse_person` (`person_uuid`, `customer_id`, `full_name`, `first_name`, `last_name`, `city`, `state`, `zip`, `full_address`, `parent_person_uuid`) 
		VALUES('" . $table_uuid . "', '" . $customer_id . "', '" . $first_name . " " . $last_name . "', '" . $first_name . "', '" . $last_name . "', '" . $city . "', '" . $state . "', '" . $zip . "', '" . $city . ", " . $state . " " . $zip . "', '" . $parent_table_uuid . "')";
		}
		//echo $sql . "\r\n\r\n";
		
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		$case_table_uuid = uniqid("KQ", false);
		$attribute_1 = "main";
		$last_updated_date = date("Y-m-d H:i:s");
		//now we have to attach the injury to the case 
		$sql = "INSERT INTO cse_case_person (`case_person_uuid`, `case_uuid`, `person_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', 'main', '" . $last_updated_date . "', '4700', '" . $customer_id . "')";
		//echo $sql . "\r\n\r\n";
		
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		//$table_name = "corporation";
		$adhoc_uuid = uniqid("KS", false);
		//$attribute_1 = "main";
		$adhoc = "claim_number";
		//$last_updated_date = date("Y-m-d H:i:s");
		$sql_adhoc = "INSERT INTO cse_corporation_adhoc (`adhoc_uuid`, `case_uuid`, `corporation_uuid`, `adhoc`, `adhoc_value`, `customer_id`)
	VALUES ('" . $adhoc_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', '" . $adhoc . "', '" . $claim_number . "', '" . $customer_id . "')";
				
		//echo $sql . "\r\n\r\n";
		
		$stmt = $db->prepare($sql_adhoc);  
		$stmt->execute();
		
		$note_uuid = uniqid("KN", false);
		//$attribute_1 = "main";
		//$adhoc = "claim_number";
		//$last_updated_date = date("Y-m-d H:i:s");
		$sql_adhoc = "INSERT INTO cse_notes (`notes_uuid`, `type`, `subject`, `note`, `title`, `customer_id`)
	VALUES ('" . $note_uuid  ."', 'quick', 'Claim Number', '" . $claim_number . "', 'Claim Number Info', '" . $customer_id . "')";
				
		//echo $sql . "\r\n\r\n";
		
		$stmt = $db->prepare($sql_adhoc);  
		$stmt->execute();
		
		$case_note_uuid = uniqid("KN", false);
		$attribute_notes = "general";
		//$adhoc = "claim_number";
		//$last_updated_date = date("Y-m-d H:i:s");
		$sql_adhoc = "INSERT INTO cse_case_notes (`case_notes_uuid`, `case_uuid`, `notes_uuid`, `attribute`, `last_update_user`, `customer_id`)
	VALUES ('" . $case_note_uuid  ."', '" . $case_uuid . "', '" . $note_uuid . "', '" . $attribute_notes . "', 'system', '" . $customer_id . "')";
				
		//echo $sql . "\r\n\r\n";
		
		$stmt = $db->prepare($sql_adhoc);  
		$stmt->execute();
		
		//insert injury
		if ($end_date!="0000-00-00") {
			$end_date = date("Y-m-d", strtotime($end_date));
		}
		$injury_uuid = uniqid("KI", false);
		$sql = "INSERT INTO `cse_injury` (`injury_uuid`, `injury_number`, `explanation`, 
		`start_date`, `end_date`, `deu`, `adj_number`,
		`customer_id`)
		VALUES('" . $injury_uuid . "', 1, '',
		'" . date("Y-m-d", strtotime($start_date)) . "',
		'" . $end_date . "',
		'" . $deu . "', '" . $adj_number . "',
		'" . $customer_id . "')";
		
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		
		$injury_id = $db->lastInsertId();
		
		$case_table_uuid = uniqid("KA", false);
		$attribute_1 = "main";
		$last_updated_date = date("Y-m-d H:i:s");
		//now we have to attach the injury to the case 
		$sql = "INSERT INTO cse_case_injury (`case_injury_uuid`, `case_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $injury_uuid . "', 'main', '" . $last_updated_date . "', '4700', '" . $customer_id . "')";
		//echo $sql . "\r\n\r\n";
		
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		
		$sql = "UPDATE `cse_injury` 
		SET statute_limitation = DATE_ADD(`start_date`, INTERVAL 1 YEAR)
		WHERE injury_id = " . $injury_id;
		//echo $sql . "\r\n\r\n";
		
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		//trackInjury("insert", $injury_id);
		
		
		//bodyparts
		
		$bodyparts_array_eams = $eams_info_json["bodyparts"];
		
		foreach ($bodyparts_array_eams as $part) {
			$table_uuid = uniqid("KS", false);
			//get the uuid from the bodyparts table
			$code = $part["name"];
			$code = substr($code, 0, 3);
			//die($code);
			$bodyparts_uuid = $arrBodyParts[$code];
			$sql = "INSERT INTO cse_injury_bodyparts (`injury_bodyparts_uuid`, `injury_uuid`, `bodyparts_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $table_uuid . "', '" . $injury_uuid . "','" . $bodyparts_uuid . "','" . $int . "', '" . date("Y-m-d H:i:s") . "', '" . "MA" . "', '" . $customer_id . "')";
			
			//echo $sql . "\r\n\r\n";
			
			$stmt = $db->prepare($sql);  
			$stmt->execute();
		}
		/*
		for($int=1;$int<5;$int++) {
			if (isset($_POST["bodypart_"  .$int])) {
				if ($_POST["bodypart_"  .$int]!="") {
					$table_uuid = uniqid("KS", false);
					//get the uuid from the bodyparts table
					$code = passed_var("bodypart_"  .$int, "post");
					$code = substr($code, 0, 3);
					$bodyparts_uuid = $arrBodyParts[$code];
					$sql = "INSERT INTO cse_injury_bodyparts (`injury_bodyparts_uuid`, `injury_uuid`, `bodyparts_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
					VALUES ('" . $table_uuid . "', '" . $injury_uuid . "','" . $bodyparts_uuid . "','" . $int . "', '" . date("Y-m-d H:i:s") . "', '" . "MA" . "', '" . $customer_id . "')";
					
					//echo $sql . "\r\n\r\n";
					
					$stmt = $db->prepare($sql);  
					$stmt->execute();
					
					//$new_id = $db->lastInsertId();
				}
			}
		}*/
		
		//now update the number
		$sql = "SELECT bp.*, cib.injury_bodyparts_id
		FROM `cse_bodyparts` bp
		INNER JOIN cse_injury_bodyparts cib
		ON bp.bodyparts_uuid = cib.bodyparts_uuid
		INNER JOIN cse_injury cinj
		ON (cib.injury_uuid = cinj.injury_uuid
		AND `cinj`.`injury_uuid` = '" . $injury_uuid . "')
		WHERE 1
		AND cib.customer_id = " . $customer_id . "
		AND cib.deleted = 'N'
		ORDER BY `code` ASC";

		//echo $sql . "\r\n\r\n";
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$injury_bodyparts = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		$body_counter = 0;
		foreach ($injury_bodyparts as $injury_bodypart) {
			$body_counter++;
			$sql = "UPDATE cse_injury_bodyparts 
			SET attribute = '" . $body_counter . "'
			WHERE injury_bodyparts_id = '" . $injury_bodypart->injury_bodyparts_id . "'";
			
			//echo $sql . "\r\n\r\n";
			
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			
		}
	
		//parties
		
		$parties_array_eams = $eams_info_json["parties"];
		
		//die(print_r($parties_array_eams));
		
		//for($int=1; $int<$partie_count; $int++) {
	//if (isset($_POST["partie_"  .$int])) {
		//if ($_POST["partie_"  .$int]!="") {
			foreach ($parties_array_eams as $partie_array_eams) {
				foreach ($partie_array_eams as $partie_array_e) {
					//die("here");
					$company_name = addslashes($partie_array_e["name"]);
					//$company_name = addslashes(passed_var("partie_"  .$int, "post"));
					$partie_role = addslashes($partie_array_e["role"]);
					$partie_address = addslashes($partie_array_e["address"]);
					$partie_street = addslashes($partie_array_e["street"]);
					$partie_city = addslashes($partie_array_e["city"]);
					$partie_state = addslashes($partie_array_e["state"]);
					$partie_zip = addslashes($partie_array_e["zip"]);
					//echo $company_name . " - name" . $partie_role . " - role" . $partie_address . " - address" . $partie_street . " - street" . $partie_city . " - city" . $partie_state . " - state" . $partie_zip . " - zip</br></br>";
				}
					//die("here");
					$attorney_type = "";
					//if (isset($_POST["attorney_type_"  .$int])) {
					//$attorney_type = $partie_array_e["attorney_type"];
					//}
					$partie_type = strtolower(str_replace(" ", "_", $partie_role));;
					switch($partie_type) {
						case "law_firm":
							$partie_type = "attorney";
							break;
						case "claims_administrator":
							$partie_type = "carrier";
							break;
						case "carrier":
							$partie_type = "carrier";
							break;
						case "insurance_company":
							$partie_type = "carrier";
							break;
						case "lien_claimant":
							$partie_type = "lien_holder";
							break;
					}
					//however
					if ($partie_type=="attorney") {
						if ($attorney_type!="") {
							if ($attorney_type=="defense") {
								$partie_type = $attorney_type;
							} else {
								$partie_type = $attorney_type . "_" . $partie_type;
							}
						}
					}
					//first look up if this partie is already in our database
					$sql = "SELECT corporation_uuid 
					FROM cse_corporation
					WHERE `full_address` = '" . $partie_address . "'
					AND company_name = '" . $company_name . "'
					AND `type` = '" . $partie_type . "'
					AND customer_id = " . $customer_id . "
					AND corporation_uuid = parent_corporation_uuid
					AND deleted = 'N'";
					
					$stmt = $db->prepare($sql);
					$stmt->execute();
					$partie = $stmt->fetchObject();
					
					if (is_object($partie)) {
						$parent_table_uuid = $partie->corporation_uuid;
					} else {
						$parent_table_uuid = uniqid("RD", false);
						
						//insert the parent record first
						$sql = "INSERT INTO `cse_corporation` (`corporation_uuid`, `customer_id`, `company_name`, 
						`type`, `full_address`, `street`, `city`, `state`, `zip`, `parent_corporation_uuid`, `copying_instructions`) 
						VALUES('" . $parent_table_uuid . "', '" . $customer_id . "', '" . $company_name . 
						"', '" . $partie_type . "', '" . $partie_address . "', '" . $partie_street . 
						"', '" . $partie_city . "', '" . $partie_state . "', '" . $partie_zip . 
						"', '" . $parent_table_uuid . "', '')";
						
						//echo $sql . "\r\n\r\n";
						
						$stmt = $db->prepare($sql);  
						$stmt->execute();
						
					}
					//now insert the partie itself
					$table_uuid = uniqid("KP", false);
					
					$sql = "INSERT INTO `cse_corporation` (`corporation_uuid`, `customer_id`, `company_name`, `type`, `full_address`, `street`, `city`, `state`, `zip`, `parent_corporation_uuid`, `copying_instructions`) 
						VALUES('" . $table_uuid . "', '" . $customer_id . "', '" . $company_name . "', '" . $partie_type . "', '" . $partie_address . "', '" . $partie_street . "', '" . $partie_city . "', '" . $partie_state . "', '" . $partie_zip . "', '" . $parent_table_uuid . "', '')";
					
					//echo $sql . "\r\n\r\n";
					
					$stmt = $db->prepare($sql);  
					$stmt->execute();
					
					$corporation_id = $db->lastInsertId();
					
					$case_table_uuid = uniqid("KA", false);
					$attribute_1 = "main";
					$last_updated_date = date("Y-m-d H:i:s");
					//attach corporation to case
					$sql = "INSERT INTO cse_case_corporation (`case_" . $table_name . "_uuid`, `case_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', '" . $partie_type . "', '" . $last_updated_date . "', '4700', '" . $customer_id . "')";
				
					//echo $sql . "\r\n\r\n";
					
					$stmt = $db->prepare($sql);  
					$stmt->execute();
				
					//trackCorporation("insert", $corporation_id);
				
			}
		//}
		
		//hearings
		
		$hearings_array_eams = $eams_info_json["hearings"];
		
		//echo print_r($hearings_array_eams);
		
		//for($int=1; $int<$partie_count; $int++) {
	//if (isset($_POST["partie_"  .$int])) {
		//if ($_POST["partie_"  .$int]!="") {
			foreach ($hearings_array_eams as $hearing_array_eams) {
				$hearing_date = $hearing_array_eams["date"];
				$hearing_title = $hearing_array_eams["type"];
				$hearing_type = $hearing_array_eams["type"];
				//$hearing_array_e["location"]
				foreach ($hearing_array_eams as $hearing_array_e) {
					
					//color
					$sql = "SELECT setting_value, default_value
					FROM cse_setting
					where category = 'calendar_type'
					AND setting = '" . $hearing_type . "'
					AND customer_id = " . $customer_id;
			
					$stmt = $db->prepare($sql);
					$stmt->execute();
					$calendar_setting = $stmt->fetchObject();
					$color = "blue";
					if (count($calendar_setting) > 0  && is_object($calendar_setting)) {
						$color = $calendar_setting->default_value;
					}
					
					$table_uuid = uniqid("KE", false);
					$sql = "INSERT INTO `cse_event` (`event_uuid`, `event_dateandtime`, `event_date`, `event_hour`, `event_title`, `full_address`, `event_type`, `color`, `event_description`, `customer_id`)
					VALUES('" . $table_uuid . "', '" . date("Y-m-d H:i:s", strtotime($hearing_array_eams["date"])) . "', '" . date("Y-m-d", strtotime($hearing_array_eams["date"])) . "', '" . date("H:i:s", strtotime($hearing_array_eams["date"])) . "' , '" . addslashes($hearing_title) . "' , '" . $hearing_array_eams["location"] . "' , '" . $hearing_array_eams["type"] . "', '" . $color . "', '" . addslashes($hearing_title) . "', " . $customer_id . ")";
					
					//echo $sql . "\r\n\r\n";
					
					$stmt = $db->prepare($sql);  
					$stmt->execute();
					
					$event_id = $db->lastInsertId();
					$user_id = 4700;
					$case_table_uuid = uniqid("CE", false);
					$attribute_1 = "main";
					//now we have to attach the event to the case 
					$sql = "INSERT INTO `cse_case_event` (`case_event_uuid`, `case_uuid`, `event_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
					VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', 'main', '" . $last_updated_date . "', '" . $user_id . "', '" . $customer_id . "')";
					//echo $sql . "\r\n\r\n";
					
					$stmt = $db->prepare($sql);  
					$stmt->execute();
					
					//track now
					//trackEvent("insert", $event_id);
				}
			}
		
		/*for($int=1;$int<5;$int++) {
			if (isset($_POST["hearing_date_"  .$int])) {
				if ($_POST["hearing_date_"  .$int]!="") {
					
					$hearing_type = passed_var("hearing_type_"  .$int, "post");
					//color
					$sql = "SELECT setting_value, default_value
					FROM cse_setting
					where category = 'calendar_type'
					AND setting = '" . $hearing_type . "'
					AND customer_id = " . $customer_id;
			
					$stmt = $db->prepare($sql);
					$stmt->execute();
					$calendar_setting = $stmt->fetchObject();
					$color = "blue";
					if (count($calendar_setting) > 0  && is_object($calendar_setting)) {
						$color = $calendar_setting->default_value;
					}
					
					$table_uuid = uniqid("KE", false);
					$hearing_title = passed_var("hearing_title_" . $int, "post");
					$sql = "INSERT INTO `cse_event` (`event_uuid`, `event_dateandtime`, `event_date`, `event_hour`, `event_title`, `full_address`, `event_type`, `color`, `event_description`, `customer_id`)
					VALUES('" . $table_uuid . "', '" . date("Y-m-d H:i:s", strtotime(passed_var("hearing_date_" . $int, "post"))) . "', '" . date("Y-m-d", strtotime(passed_var("hearing_date_" . $int, "post"))) . "', '" . date("H:i:s", strtotime(passed_var("hearing_date_" . $int, "post"))) . "' , '" . addslashes($hearing_title) . "' , '" . passed_var("hearing_location_" . $int, "post") . "' , '" . passed_var("hearing_type_" . $int, "post") . "', '" . $color . "', '" . addslashes($hearing_title) . "', " . $customer_id . ")";
					
					//echo $sql . "\r\n\r\n";
					
					$stmt = $db->prepare($sql);  
					$stmt->execute();
					
					$event_id = $db->lastInsertId();
					
					$case_table_uuid = uniqid("CE", false);
					$attribute_1 = "main";
					//now we have to attach the event to the case 
					$sql = "INSERT INTO `cse_case_event` (`case_event_uuid`, `case_uuid`, `event_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
					VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', 'main', '" . $last_updated_date . "', '4700', '" . $customer_id . "')";
					//echo $sql . "\r\n\r\n";
					
					$stmt = $db->prepare($sql);  
					$stmt->execute();
					
					//track now
					//trackEvent("insert", $event_id);
				}
			}
		}*/
		
		
		$events_array_eams = $eams_info_json["events"];
		//die(print_r($eams_info_json["events"]));
		//echo print_r($events_array_eams);
		
		//die();
		//events
		foreach ($events_array_eams as $event_array_eams) {
				//$event_date = $event_array_eams["date"];
				//foreach ($event_array_eams as $event_array_e) {
					
					$event_type = $event_array_eams["type"];
					//color
					$sql = "SELECT setting_value, default_value
					FROM cse_setting
					where category = 'calendar_type'
					AND setting = '" . $event_type . "'
					AND customer_id = " . $customer_id;
			
					$stmt = $db->prepare($sql);
					$stmt->execute();
					$calendar_setting = $stmt->fetchObject();
					$color = "blue";
					if (count($calendar_setting) > 0  && is_object($calendar_setting)) {
						$color = $calendar_setting->default_value;
					}
					
					$table_uuid = uniqid("KE", false);
					$event_description = $event_array_eams["description"];
					$sql = "INSERT INTO `cse_event` (`event_uuid`, `event_dateandtime`, `event_date`, `event_title`, 
					`event_type`, `color`, `event_description`, `customer_id`)
					VALUES('" . $table_uuid . "', '" . date("Y-m-d", strtotime($event_array_eams["date"])) . " 00:00:00', '" . date("Y-m-d", strtotime($event_array_eams["date"])) . "' , '" . addslashes($event_description) . "' , '" . $event_array_eams["type"] . "', '" . $color . "', '" . addslashes($event_description) . "', " . $customer_id . ")";
					
					//echo $sql . "\r\n\r\n";
					
					$stmt = $db->prepare($sql);  
					$stmt->execute();
					
					$event_id = $db->lastInsertId();
					
					$case_table_uuid = uniqid("CE", false);
					$attribute_1 = "main";
					//now we have to attach the event to the case 
					$sql = "INSERT INTO `cse_case_event` (`case_event_uuid`, `case_uuid`, `event_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
					VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', 'main', '" . $last_updated_date . "', '4700', '" . $customer_id . "')";
					//echo $sql . "\r\n\r\n";
					
					$stmt = $db->prepare($sql);  
					$stmt->execute();
					
					//track now
					//trackEvent("insert", $event_id);
				}
			//}
		
		
		$success = array("success"=>$case_id);
		echo json_encode($success) . "<br/>";
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
			//return;
	}
	$db = null;
}
?> 
<?php
// error_reporting(E_ERROR | E_PARSE);

$api_key = $_POST["api_key"];
$adj_number = $_POST["adj_number"];
$requesterFirstName = $_POST["requesterFirstName"];
$requesterLastName = $_POST["requesterLastName"];
$email = $_POST["email"];
$cus_id = $_POST["cus_id"];
$ip_address = $_POST["ip_address"];
$auth_id = $_POST["auth_id"];
$auth_key = $_POST["auth_key"];
$auth_cred = $_POST["auth_cred"];
$format = $_POST["format"];
$customer_id = $_POST["cus_id"];
/*
if ($api_key!="z~e8~1X5b1~b20b962d~X02830dz9~6K") {
	$error = array("error"=> array("text"=>"api key error"));
	echo json_encode($error);
	die();
} else {
	$_SESSION['user_customer_id'] = 1033;
}*/
scrapeEams($adj_number);

function sortByOption($a, $b) {
	return strcmp($a['role'], $b['role']);
}

function getConnection() {
	//cstmwb default
	// $dbhost = "Matrixdocuments.com";
	// $dbuser = "matrixdo_77";
	// $dbpass = "CSHKqimk";
	// $dbname = "matrixdo_empire";
	$dbhost = "localhost";
	$dbuser = "newuser";
	$dbpass = "access527";
	$dbname = "ikase";
	try {
		$dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);	
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	} catch (PDOException $e) {
		echo 'Connection failed: ' . $e->getMessage();
	}
	return $dbh;
}
function scrapeEams($adj_number) {
	session_write_close();

	global $api_key;
	global $adj_number;
	global $requesterFirstName;
	global $requesterLastName;
	global $email;
	global $cus_id;
	global $ip_address;
	global $auth_id;
	global $auth_key;
	global $auth_cred;
	global $format;
	global $customer_id;
	
	$search_first_name = "";
	$search_last_name = "";
	$search_dob = "";
	$search_city = "";
	$search_zip_code = "";
	$db = getConnection();

	//need adj
	$first_three = substr($adj_number, 0, 3);
	if ($first_three!="ADJ") {
		die(json_encode(array("error"=>$adj_number . " is not a valid ADJ number.")));
	}
	//get customer info
	try {
		/*$db = getConnection();
		
		//$customer_id = $_SESSION['user_customer_id'];
		//lookup the customer name
		$sql_customer = "SELECT cus_name_first, cus_name_last, cus_email, firm_name
		FROM  `ikase`.`cse_customer` cus
		LEFT OUTER JOIN `ikase`.`cse_eams_reps` cer
		ON cus.eams_no = cer.eams_ref_number
		WHERE customer_id = :customer_id";
		
		$stmt = $db->prepare($sql_customer);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$customer = $stmt->fetchObject();
		
		//die(print_r($customer));
		
		$first_name = strtoupper($customer->cus_name_first);
		$last_name = strtoupper($customer->cus_name_last);
		$firm_name = $customer->firm_name;
		
		//die($firm_name);
		$email = $customer->cus_email;
		*/
		$first_name = $requesterFirstName;
		$last_name = $requesterLastName;
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
		// curl_setopt ($ch, CURLOPT_SSLVERSION, 2);
		
		curl_setopt ($ch, CURLOPT_CAINFO, 'cacert.pem'); 
		curl_setopt($ch, CURLOPT_COOKIEFILE, "cookies.txt");
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_POST, count($fields));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
		//curl_setopt($ch,CURLOPT_FOLLOWLOCATION,TRUE);
		
		//execute post
		$result = curl_exec($ch);
		//preg_match_all('|Set-Cookie: (.*);|U', $result, $matches);
		//die(print_r($matches));
		if($result === false) {
			echo "Error Number:".curl_errno($ch)."<br>";
			echo "Error String:".curl_error($ch);
			die();
		}
		$headers = curl_getinfo($ch);
		
		if ($headers["http_code"]==500) {
			$result = json_encode(array("error"=>"EAMS is down"));
			die($result);
		}
		/*
		if ($headers["http_code"]==200) {
			$result = json_encode(array("error"=>"Here"));
			die($result);
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
			
if ($_SESSION['user_customer_id']==1033) { 
	$sql_cases .= "(" . SQL_PERSONX . ")";
} else {
	$sql_cases .= "cse_person";
}
$sql_cases .= " app ON ccapp.person_uuid = app.person_uuid
					INNER JOIN  ";
			
if ($_SESSION['user_customer_id']==1033) { 
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
					$partie_name = $arrValues[$idx];
					$partie_role = $arrValues[$idx + 1];
					$partie_address = $arrValues[$idx + 2];
					
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
					//echo $partie_role . " - " . $partie_name . " == " . $firm_name . "\r\n";
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
			
			echo $result;
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
?>
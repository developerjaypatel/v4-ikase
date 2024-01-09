<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$adj = $_GET["adj"];
$customer_id = 1033;

function getNickConnection() {
	//$dbhost="54.149.211.191";
$dbhost="52.34.166.217";
	$dbuser="root";
	$dbpass="admin527#";
	$dbname="ikase";
	
	$dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);            
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbh;
}

if ($adj=="") {
	$adj = "ADJ9881786";
}

if (strlen($adj)!=10) {
	die();
}
$url = 'https://eams.dwc.ca.gov/WebEnhancement/InformationCapture';
$fields = array("UAN"=>"", "requesterFirstName"=>"THOMAS", "requesterLastName"=>"SMITH", "email"=>"webmaster@kustomweb.com", "reason"=>"CASESEARCH");

$fields_string = "";
foreach($fields as $key=>$value) { 
	$fields_string .= $key.'='.$value.'&'; 
}
rtrim($fields_string, '&');

//open connection
$ch = curl_init();

//set the url, number of POST vars, POST data
curl_setopt($ch,CURLOPT_URL,$url);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
curl_setopt($ch,CURLOPT_HEADER, false); 
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt ($ch, CURLOPT_COOKIEFILE, "cookies.txt");
curl_setopt($ch, CURLOPT_POST, count($fields_string));
curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
//curl_setopt($ch,CURLOPT_FOLLOWLOCATION,TRUE);

//execute post
$result = curl_exec($ch);
//preg_match_all('|Set-Cookie: (.*);|U', $result, $matches);
//die(print_r($matches));
if($result === false) {
	echo "Error Number:".curl_errno($ch)."<br>";
	echo "Error String:".curl_error($ch);
}
$headers = curl_getinfo($ch);
//die(print_r($headers));
if ($headers["http_code"]==302) {
   //redirect
	//die($headers["redirect_url"]);
	//$url = $headers["redirect_url"];
	
	$url = "https://eams.dwc.ca.gov/WebEnhancement/InjuredWorkerFinder";
	$fields = array("caseNumber"=>$adj, "firstName"=>"", "lastName"=>"", "dateOfBirth"=>"", "city"=>"", "zipCode"=>"");
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
	curl_setopt($ch, CURLOPT_POST, count($fields_string));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
		
	$result = curl_exec($ch);
	
	if($result === false) {
		echo "Error Number:".curl_errno($ch)."<br>";
		echo "Error String:".curl_error($ch);
	}
	$headers = curl_getinfo($ch);
	
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
		die("no url");
	}
	
	//now process the data
	//$url = "https://eams.dwc.ca.gov/WebEnhancement/CaseFinder?partyId=-5205632355686940672&firstName=MARISSA&lastName=MORALES&caseNumber=ADJ9881786";
	
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
	//die(print_r($headers));
	
	//die($result );
	
	
	//$filename = "https://eams.dwc.ca.gov/WebEnhancement/" . str_replace("'", "", $match[2]);
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
	//die(print_r($headers));
	
	//die($result);
	
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
	$arrData = array();
	//from first scrape
	$client_first_name = $arrFirstValues[6]; $arrData[] = $client_first_name;
	$client_last_name = $arrFirstValues[7]; $arrData[] = $client_last_name;
	$client_city  = $arrFirstValues[8]; $arrData[] = $client_city;
	$client_zip  = $arrFirstValues[9]; $arrData[] = $client_zip;
	
	//from details scrape
	$adj_number = $arrValues[0]; $arrData[] = $adj_number;
	$venue = substr($arrValues[1], 0, 3); $arrData[] = $venue;
	$doi =  $arrValues[2];
	//ct
	$arrDOI = explode(" - ", $doi);
	$start_date = $arrDOI[0]; $arrData[] = $start_date;
	$end_date = "0000-00-00";
	if (count($arrDOI) == 2) {
		$end_date = $arrDOI[1];
	}
	$arrData[] = $end_date;
	 
	$judge = $arrValues[3]; $arrData[] = $judge;
	$employer = $arrValues[4]; $arrData[] = $employer;
	
	//body parts
	$arrBodyParts = array();
	for($idx = 5; $idx < count($arrValues); $idx = $idx + 2) {
		//are we dealing with a body part
		if (strpos($arrValues[$idx], "Body Part") === false) {
			break;
		}
		$arrBodyParts[] = $arrValues[$idx +1];
	}
	$party_idx = $idx;
	
	$arrParties = array();
	if (count($arrValues) > $party_idx - 1) {
		//parties
		for($idx = $party_idx; $idx < count($arrValues); $idx = $idx + 3) {
			$partie_name = $arrValues[$idx];
			$partie_role = $arrValues[$idx + 1];
			$partie_address = $arrValues[$idx + 2];
			
			$arrAddress = explode("  ", $partie_address);
			$arrCity = explode(" ", $arrAddress[1]);
			$zip = $arrCity[count($arrCity)-1];
			unset ($arrCity[count($arrCity)-1]);
			$state = $arrCity[count($arrCity)-1];
			unset ($arrCity[count($arrCity)-1]);
			$city = trim(implode(" ", $arrCity));
			
			$arrParties[] = array("name"=>$partie_name, "role"=>$partie_role, "address"=>$partie_address, "street"=>$arrAddress[0], "city"=>$city, "state"=>$state, "zip"=>$zip);
			
		}
		die(print_r($arrParties));
	}
	//die(print_r($arrParties));
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
	$arrEvents = array();
	for($idx = 0; $idx < count($arrValues); $idx = $idx + 6) {
		$arrEvents[] = array("type"=>$arrValues[$idx + 3], "date"=>$arrValues[$idx + 5]);
	}
	
	print_r($arrFirstValues);
	print_r($arrData);
	print_r($arrBodyParts);
	print_r($arrParties);
	print_r($arrEvents);
}
//close connection
curl_close($ch);

if (count($arrData) > 0) {
	try {
		$db = getNickConnection();
		
		//lookup the customer name
		$sql_customer = "SELECT data_source
		FROM  `ikase`.`cse_customer` 
		WHERE customer_id = :customer_id";
		
		$stmt = $db->prepare($sql_customer);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$customer = $stmt->fetchObject();
		//die(print_r($customer));
		$data_source = $customer->data_source;
		if ($data_source=="") {
			$data_source = "ikase";
		}
		//get the order number
		//get customer settings
		$sql = "SELECT cs.*, cs.setting_id id, cs.setting_uuid uuid
		FROM  `cse_setting` cs
		INNER JOIN `cse_setting_customer` csc
		ON cs.setting_uuid = csc.setting_uuid
		WHERE 1
		AND `csc`.customer_uuid = '" . $customer_id . "'
		AND `cs`.customer_id = " . $customer_id . "
		AND `setting` LIKE '%case_number%'
		ORDER BY cs.`category`";
	
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
		
		//basic defaults
		if (!isset($arrSettings["case_number_prefix"])) {
			$arrSettings["case_number_prefix"] = "";
		}
		if (!isset($arrSettings["case_number_next"])) {
			$arrSettings["case_number_next"] = 1000;
		}
		$case_number = $arrSettings["case_number_prefix"] . "-" . $arrSettings["case_number_next"];
		$casename = $arrData[0] . " " . $arrData[1] . " vs " . $arrData[8];
		
		//now the kase
		$case_uuid = uniqid("KS", false);
		$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case` (case_uuid, cpointer, case_number, case_name, case_date, case_type, venue, case_status, submittedOn, customer_id) 
VALUES ('" . $case_uuid . "', -1', '" . $case_number . "', '" . addslashes($casename) . "', '" . date("Y-m-d", strtotime($start_date)) . "', 'WCAB', '" . $venue . "', 'Open', '" . date("Y-m-d") . "', " . $customer_id . ")";
		echo $sql . "\r\n\r\n";
		/*
		$stmt = DB::run($sql);
		*/
		//attach to case
		$case_notes_uuid = uniqid("CN", false);
		$notes_uuid = uniqid("NT", false);
		$last_updated_date = date("Y-m-d H:i:s");
		$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_notes` (`case_notes_uuid`, `case_uuid`,  `notes_counter`, `notes_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
		VALUES ('" . $case_notes_uuid . "', '" . $case_uuid . "', 0, '" . $notes_uuid . "', 'quick', '" . $last_updated_date . "', 'system', 'N', '" . $customer_id . "')";
		echo $sql . "\r\n\r\n";
		/*
		$stmt = DB::run($sql);
		*/
		
		$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_notes` (`notes_counter`, `notes_uuid`, `note`, `dateandtime`, `entered_by`, `customer_id`, `type`)
		VALUES (0, '" . $notes_uuid . "', 'Imported from EAMS', '" . date("Y-m-d") . "', 'system', '" . $customer_id . "', 'quick')";
		
		echo $sql . "\r\n\r\n";
		/*
		$stmt = DB::run($sql);
		*/
		$injury_uuid = uniqid("KI", false);
		$adj_number = $arrData[4];
		
		$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_injury` (`injury_uuid`, `injury_number`, `adj_number`, `type`, `occupation`, `start_date`, `end_date`, `body_parts`, `ctdates`, `customer_id`, `deleted`)
		VALUES('" . $injury_uuid . "', 1, '" . $adj_number . "', '', '','" . $arrData[6] . "','" . $arrData[7] . "','" . addslashes(implode("; ", $arrBodyParts)) . "','" . $doi . "', " . $customer_id . ", 'N')";
		
		echo $sql . "\r\n\r\n";  $injury_id = 99999;
		/*
		DB::run($sql);
	$injury_id = DB::lastInsertId();
		*/
		
		//statute of limitation
		$sql = "UPDATE `ikase_" . $data_source . "`.`cse_injury` 
		SET statute_limitation = DATE_ADD(`start_date`, INTERVAL 1 YEAR)
		WHERE injury_id = " . $injury_id;
		echo $sql . "\r\n\r\n";
		/*
		$stmt = DB::run($sql);
		*/
		
		//now attach to case, even before I create case
		$case_table_uuid = uniqid("KA", false);
		$attribute_1 = "main";
		
		//now we have to attach the injury to the case 
		$sql_injury = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_injury` (`case_injury_uuid`, `case_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $injury_uuid . "', 'main', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";

		echo $sql_injury . "\r\n\r\n";  
		/*
		$stmt = DB::run($sql_injury);
		*/
		
		//add the applicant, parent first
		$table_uuid = uniqid("AP", false);
		$parent_table_uuid = uniqid("PA", false);
		$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_person` 
		(`person_uuid`, `parent_person_uuid`, `full_name`, `first_name`, `last_name`, `full_address`, `city`, `state`)
		VALUES ('" . $parent_table_uuid . "','" . $parent_table_uuid . "','" . addslashes($arrData[0] . " " . $arrData[1]) . "','" . addslashes($arrData[0]) . "','" . addslashes($arrData[2] . "," . $arrData[3]) . "' ','" . addslashes($arrData[2]) . "','" . addslashes($arrData[3]) . "')";
		echo $sql_injury . "\r\n\r\n";  
		/*
		$stmt = DB::run($sql_injury);
		*/
		
		$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_person` 
		(`person_uuid`, `parent_person_uuid`, `full_name`, `first_name`, `last_name`, `full_address`, `city`, `state`)
		VALUES ('" . $table_uuid . "','" . $parent_table_uuid . "','" . addslashes($arrData[0] . " " . $arrData[1]) . "','" . addslashes($arrData[0]) . "','" . addslashes($arrData[0] . " " . $arrData[1]) . "','" . addslashes($arrData[0]) . "','" . addslashes($arrData[2] . "," . $arrData[3]) . "' ','" . addslashes($arrData[2]) . "','" . addslashes($arrData[3]) . "')";
		echo $sql_injury . "\r\n\r\n";  
		/*
		$stmt = DB::run($sql_injury);
		*/
		
		$case_table_uuid = uniqid("CA", false);
		//attach applicant to kase
		$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_person` (`case_person_uuid`, `case_uuid`, `person_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', 'main', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
		
		echo $sql . "\r\n\r\n"; 
		/*
		$stmt = DB::run($sql);
		*/
		
		//insert the parties
		foreach($arrParties as $partie) {
			$table_uuid = uniqid("KS", false);
			$parent_table_uuid = uniqid("RD", false);
			
			$role = $partie["role"];
			switch($role) {
				case "LIEN CLAIMANT":
					$role = "lien_holder";
					break;
				case "LAW FIRM":
					$role = "attorney";
					break;
				case "INSURANCE COMPANY":
					$role = "carrier";
					break;
				case "EMPLOYER":
					$role = "employer";
					break;
				default:
					$role = strtolower($partie["role"]);
			}
			//insert the parent record first
			$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation` (`corporation_uuid`, `parent_corporation_uuid`, `customer_id`, `company_name`, `type`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `email`, `salutation`, `last_updated_date`, `last_update_user`, `deleted`,  `copying_instructions`) 
			VALUES('" . $parent_table_uuid . "', '" . $parent_table_uuid . "', '" . 
			$customer_id . "','" . addslashes($partie["name"]) . "','" . 
			$role . "','" . addslashes($partie["address"]) . "')";

			echo $sql . "\r\n\r\n"; 
			/*
			$stmt = DB::run($sql);
			*/
			
			//insert the actual record second
			$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation` (`corporation_uuid`, `parent_corporation_uuid`, `customer_id`, `company_name`, `type`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `email`, `salutation`, `last_updated_date`, `last_update_user`, `deleted`,  `copying_instructions`) 
			VALUES('" . $table_uuid . "', '" . $parent_table_uuid . "', '" . 
			$customer_id . "','" . addslashes($partie["name"]) . "','" . 
			$role . "','" . addslashes($partie["address"]) . "')";

			echo $sql . "\r\n\r\n"; 
			/*
			$stmt = DB::run($sql);
			*/	
		}
		
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
	}
}

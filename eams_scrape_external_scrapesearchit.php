<?php
//die("hello");
scrapeItSearchEams();

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

function scrapeItSearchEams() {
	//die(print_r($_POST));
	$auth_id = $_POST["auth_id"];
	$auth_key = $_POST["auth_key"];
	$adj_number = $_POST["adj_number"];
	$authorize_key = $_POST["authorize_key"];
	
	$ip_address = $_SERVER['REMOTE_ADDR'];
	
	//record the transaction
	// $sql = "INSERT INTO cse_metric (`action`, `auth_id`, `authorize_key`, `auth_key`, `adj_number`, `ip_address`)
	// VALUES ('search', '" . $auth_id . "', '" . $authorize_key . "', '" . $auth_key .  "', '" . $adj_number . "','" . $ip_address . "')";
	// $db = getConnection();
	
	// $stmt = $db->prepare($sql);
	// $stmt->execute();
	
	$db = null;
	$format = "";
	if (isset($_POST["format"])) {
		$format = $_POST["format"];
	}
	$_SESSION["requesterFirstName"] = $_POST["requesterFirstName"];
	$_SESSION["requesterLastName"] = $_POST["requesterLastName"];
	$_SESSION["email"] = $_POST["email"];
	$_SESSION["auth_cred"] = $_POST["auth_cred"];
	$_SESSION["ip_address"] = $_POST["ip_address"];
	$_SESSION["format"] = $format;
	//die(print_r($_SESSION));
	
	if ($format=="") {
		$sql = "SELECT `customer_id` FROM `cse_authorized` 
		WHERE `auth_id` = '" . $auth_id . "' 
		AND `auth_key` = '" .  $auth_key . "'";
		
		try {
			$db = getConnection();
			$stmt = $db->query($sql);
			$customer = $stmt->fetchObject();
			//die(print_r($customer));
			$db = null;
		} catch(PDOException $e) {
			$error = array("error"=> array("text"=>$e->getMessage()));
			echo json_encode($error);
		}
		if (!is_object($customer) || $customer->customer_id < 0) {
			$error = array("error"=> array("text"=>"api 2 key error"));
			echo json_encode($error);
			die();
		} else {
			$_SESSION['user_customer_id'] = $customer->customer_id;
		}
	}
	
	$_SESSION["cus_name"] = $_POST["cus_name"];
	$adj_number = $_POST["adj_number"];
	$search_first = $_POST["first_name"];
	$search_last = $_POST["last_name"];
	$search_dob = $_POST["dob"];
	$search_doi = $_POST["doi"];
	
	$format = "";
	if (isset($_POST["format"])) {
		$format = $_POST["format"];
	}
	$_SESSION["requesterFirstName"] = $_POST["requesterFirstName"];
	$_SESSION["requesterLastName"] = $_POST["requesterLastName"];
	$_SESSION["email"] = $_POST["email"];
	$_SESSION["auth_cred"] = $_POST["auth_cred"];
	$_SESSION["ip_address"] = $_POST["ip_address"];
	$_SESSION["format"] = $format;
	
	//die(print_r($_SESSION));
	searchEams($adj_number, $search_first, $search_last, $search_dob, $search_doi, $auth_id, $auth_key);
}

function searchEams($adj_number, $search_first_name, $search_last_name, $search_dob, $search_doi, $auth_id, $auth_key) {
	session_write_close();
	
	//die($search_doi . "\r\n" . date("m/d/Y", strtotime($search_doi)));
	
	$adj_number = $_POST["adj_number"];

	$search_city = $_POST["city"];
	$search_zip_code = $_POST["zip_code"];
	$party_id = $_POST["party_id"];

	//get customer info
	try {
		$firm_name = $_SESSION["cus_name"];
		$first_name = $_SESSION["requesterFirstName"];
		$last_name = $_SESSION["requesterLastName"];
		
		$email = $_SESSION["email"];
		
		if ($email=="") {
			$email = "nick@kustomweb.com";
		}
		$url = 'https://eams.dwc.ca.gov/WebEnhancement/InformationCapture';
		$fields = array("UAN"=>"", "requesterFirstName"=>$first_name, "requesterLastName"=>$last_name, "email"=>$email, "reason"=>"CASESEARCH");
		//die(print_r($fields));
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
		curl_setopt($ch, CURLOPT_POST, count($fields));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
		//curl_setopt($ch,CURLOPT_FOLLOWLOCATION,TRUE);
		
		//execute post
		$result = curl_exec($ch);
		//die($result);
		//preg_match_all('|Set-Cookie: (.*);|U', $result, $matches);
		//die(print_r($matches));
		if($result === false) {
			echo "Error Number:".curl_errno($ch)."<br>";
			echo "Error String:".curl_error($ch);
			die();
		}
		$headers = curl_getinfo($ch);
		//die(print_r($headers));
		if ($headers["http_code"]==500) {
			$result = json_encode(array("error"=>"EAMS is down"));
			die($result);
		}
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
			//die($result);
			if (strpos($result,"No results returned") > 0) {
				die(json_encode(array("error"=>"No results returned")));
			}
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
			
			$client_first_name = $arrFirstValues[6]; $arrData["first_name"] = $client_first_name;
			$client_last_name = $arrFirstValues[7]; $arrData["last_name"] = $client_last_name;
			$client_city  = $arrFirstValues[8]; $arrData["city"] = $client_city;
			$client_zip  = $arrFirstValues[9]; $arrData["zip"] = $client_zip;
			
			//now let's get into scraping itself
			$url = "";
			$view_party_index = "";	//if we are searching for a specific one...
			$arrRows = array();
			$regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
			if(preg_match_all("/$regexp/siU", $result, $matches, PREG_SET_ORDER)) {
				//die(print_r($matches));
				foreach($matches as $match_index=>$match) {
					//if ($match_index < 4) {
					if(count($matches) < 4) {
						continue;
					}
				  // $match[2] = link address
				  if ($match[3] == "View cases") {
					//that's the one that leads to the actual data
					$url = "https://eams.dwc.ca.gov/WebEnhancement/" . str_replace("'", "", $match[2]);
					$row_index = $match_index - 3;
					$arrRows[$row_index]["url"] = $url;
					
					//let's get the party id
					$arrDetails = explode("partyId=", $url);
					
					$arrDetails = explode("&amp;", $arrDetails[1]);
					//die(print_r($arrDetails));
					$arrRows[$row_index]["party_id"] = $arrDetails[0];
					//echo $party_id . " == " . $arrDetails[0] . "\r\n";
					if ($party_id == $arrDetails[0]) {
						$view_party_index = $row_index;
					}
					//die(print_r($arrRows[$row_index]));
				  }
				}
			}
			//echo "view_party_index: ". $view_party_index . "\r\n";
			//die(print_r($arrRows));
			$counter = 0;
			for($index = 6; $index < count($arrFirstValues); $index = $index + 5) {
				$first_name = $arrFirstValues[$index];
				$last_name = $arrFirstValues[$index + 1];
				$city = $arrFirstValues[$index + 2];
				$zip = $arrFirstValues[$index + 3];
				$view_details = $arrFirstValues[$index + 4];
				
				$arrRows[$counter]["name"] = $first_name . " " . $last_name;
				$arrRows[$counter]["city"] =  $city . ", " . $zip;
				$counter++;
			}
			if ($party_id=="" && count($arrRows) > 1) {
				//echo "no party\r\n";
				echo json_encode($arrRows);
				die();
			}
			if ($party_id=="" && count($arrRows) == 1) {
				$view_party_index = 0;
			}
			//die(print_r($arrRows));
			//get the url
			$url = $arrRows[$view_party_index]["url"];
			//die($url);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_COOKIE, "cookies.txt");
			$result = curl_exec($ch);
			//die($result);
			if($result === false) {
				echo "Error Number:".curl_errno($ch)."<br>";
				echo "Error String:".curl_error($ch);
			}
			
			$doc = new DOMDocument();
			@$html = $doc->loadHTML($result);
			
			$tables = $doc->getElementsByTagName("table");
			$headers = curl_getinfo($ch);
			
			$ths = $doc->getElementsByTagName("th");
			$arrFields1 = array();
			foreach($ths as $cell_index=>$th) {
				if (trim($th->nodeValue) != "") {		
					//echo trim($th->nodeValue) . "<br />\r\n";
					$arrFields1[] = trim($th->nodeValue);
				}
			}
			//
			$tds = $doc->getElementsByTagName("td");
			//die(print_r($tds));
			$arrFirstValues = array();
			$row_counter = -1;
			// $row_counter = 0;
			foreach($tds as $cell_index=>$td) {
				if ($td->nodeValue[0]=="A" && $td->nodeValue[1]=="D" && $td->nodeValue[2]=="J" || $row_counter==-1) {
					$row_counter++;
				}
				//echo $cell_index . " - " . $td->nodeValue . "\r\n";
				if (trim($td->nodeValue) != "" && $td->nodeValue != "View events") {
					$arrFirstValues[$row_counter][] = trim($td->nodeValue);
					//echo trim($td->nodeValue) . "\r\n";
				}
				// echo $td->nodeValue."<br>";
				// if ($td->nodeValue=="View case detail") {
				// 	$row_counter++;
				// }
			}
			array_splice($arrFirstValues,0,1);
			// die(print_r($arrFirstValues));
			if (count($arrFirstValues) > 0) {
				$arrReturn = array();
				foreach($arrFirstValues as $first_counter=>$first_value) {
					if (count($first_value)==7) {
					$arrReturn[$first_counter] = array("adj_number"=>$first_value[2], "employer"=>$first_value[4], "doi"=>$first_value[5]);
					} else {
						//die(print_r($first_value));
						if (count($first_value) > 3) {
							$arrReturn[$first_counter] = array("adj_number"=>$first_value[0], "employer"=>$first_value[2], "doi"=>$first_value[3]);
						}
					}
				}
				// die(print_r($arrReturn));
				// die(print_r($arrData));
				echo json_encode(array("results"=>$arrReturn, "applicant"=>$arrData));
			} else {
				$error = array("error"=> array("text"=>"adj not found"));
				echo json_encode($error);
			}
		}
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
	}
}

?>
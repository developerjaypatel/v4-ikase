<?php
//die("hello");

$app->get('/state/:zip', authorize('user'),	'getState');

$app->post('/customer/add', 'addEAMSCustomer');
$app->post('/scrapeit', 'scrapeItEams');
$app->post('/scrapesearchit', 'scrapeItSearchEams');
$app->post('/lienit', 'lienItEams');
$app->post('/scrapeliens','scrapeLiens');

function addEAMSCustomer() {
	$authorize_key = passed_var("authorize_key", "post");
	
	if ($authorize_key == "eamsjetfiler.com" || $authorize_key == "cajetfile.com") {
		$cus_id = passed_var("cus_id", "post");
		$auth_id = passed_var("auth_id", "post");
		$auth_key = passed_var("auth_key", "post");
		
		$sql = "SELECT `authorized_id`, `customer_id` FROM `cse_authorized` 
		WHERE `auth_id` = '" . $auth_id . "' 
		AND `auth_key` = '" .  $auth_key . "'";
		
		try {
			$db = getConnection();
			$stmt = $db->query($sql);
			$customer = $stmt->fetchObject();
			
			if (!is_object($customer)) {
				$sql = "INSERT INTO `cse_authorized` (`customer_id`, `auth_id`, `auth_key`)
				VALUES ('" . $cus_id . "', '" . $auth_id . "', '" . $auth_key . "')";
			} else {
				echo json_encode(array("authorized_id"=>$customer->authorized_id)); 
				die();
			}
			//die(print_r($customer));
			$db = null;
		} catch(PDOException $e) {
			$error = array("error"=> array("text"=>$e->getMessage()));
			echo json_encode($error);
		}
		
		//die($sql);
		try {
			$db = getConnection();
			$stmt = $db->prepare($sql);
	        //$stmt->bindParam("id", $id);
			$stmt->execute();
			
			$new_id = $db->lastInsertId();
			$stmt = null; $db = null;
			//trackCustomer("insert", $new_id);
			echo json_encode(array("authorized_id"=>$new_id)); 
			
			$db = null;
		} catch(PDOException $e) {
			$error = array("error"=> array("text"=>$e->getMessage()));
			echo json_encode($error);
		}
	}
}

function getState($zip) {
	$url = "http://maps.googleapis.com/maps/api/geocode/json?address=" . $zip . "&sensor=true";
	
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
		foreach($json->results[0]->address_components as $component_index=>$address_component) {
			$component_types = $address_component->types;
			if ($component_types[0]=="administrative_area_level_1") {
				$state = $address_component->short_name;
				$blnStateFound = true;
				break;
			}
		}
		return $state;
}
function scrapeItEams() {
	//die(print_r($_POST) . " - hello");
	$auth_id = passed_var("auth_id", "post");
	$auth_key = passed_var("auth_key", "post");
	$adj_number = passed_var("adj_number", "post");
	$format = "";
	if (isset($_POST["format"])) {
		$format = passed_var("format", "post");
	}
	$_SESSION["requesterFirstName"] = passed_var("requesterFirstName", "post");
	$_SESSION["requesterLastName"] = passed_var("requesterLastName", "post");
	$_SESSION["email"] = passed_var("email", "post");
	$_SESSION["auth_cred"] = passed_var("auth_cred", "post");
	$_SESSION["ip_address"] = passed_var("ip_address", "post");
	$_SESSION["format"] = $format;
	
	$authorize_key = passed_var("authorize_key", "post");
	
	$ip_address = $_SERVER['REMOTE_ADDR'];
	
	//record the transaction
	$sql = "INSERT INTO cse_metric (`action`, `auth_id`, `authorize_key`, `auth_key`, `adj_number`, `ip_address`)
	VALUES ('adj lookup', '" . $auth_id . "', '" . $authorize_key . "', '" . $auth_key .  "', '" . $adj_number . "','" . $ip_address . "')";
	$db = getConnection();
	
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$db = null;
	
	
	if ($format!="client") {
		//`cus_name`, 
		$sql = "SELECT `customer_id` 
		FROM `cse_authorized` 
		WHERE `auth_id` = '" . $auth_id . "' 
		AND `auth_key` = '" .  $auth_key . "'";
		//echo $sql . "<br />";
		
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
		if ($format!="events") {
			$error = array("error"=> array("text"=>"api 1 key error"));
			if (!is_object($customer)) {
				die(json_encode($error));
			} else {
				if ($customer->customer_id < 0) {
					die(json_encode($error));
				} else {
					$_SESSION['user_customer_id'] = $customer->customer_id;
				}
				//$_SESSION["cus_name"] = $customer->cus_name;
			}
		} else {
			$_SESSION['user_customer_id'] = passed_var("cus_id", "post");	
		}
	} else {
		$_SESSION['user_customer_id'] = passed_var("cus_id", "post");
	}
	
	//die(print_r($_SESSION));
	scrapeEams($adj_number, $auth_id, $auth_key);
}
function lienItEams() {
	//die(print_r($_POST));
	$auth_id = passed_var("auth_id", "post");
	$auth_key = passed_var("auth_key", "post");
	$adj_number = passed_var("adj_number", "post");
	$authorize_key = passed_var("authorize_key", "post");
	
	$ip_address = $_SERVER['REMOTE_ADDR'];
	
	//record the transaction
	$sql = "INSERT INTO cse_metric (`action`, `auth_id`, `authorize_key`, `auth_key`, `adj_number`, `ip_address`)
	VALUES ('lien search', '" . $auth_id . "', '" . $authorize_key . "', '" . $auth_key .  "', '" . $adj_number . "','" . $ip_address . "')";
	$db = getConnection();
	
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$db = null;
	
	$format = "";
	if (isset($_POST["format"])) {
		$format = passed_var("format", "post");
	}
	$_SESSION["requesterFirstName"] = passed_var("requesterFirstName", "post");
	$_SESSION["requesterLastName"] = passed_var("requesterLastName", "post");
	$_SESSION["email"] = passed_var("email", "post");
	$_SESSION["auth_cred"] = passed_var("auth_cred", "post");
	$_SESSION["ip_address"] = passed_var("ip_address", "post");
	$_SESSION["format"] = $format;
	
	if ($format!="client") {
		//`cus_name`, 
		$sql = "SELECT `customer_id` 
		FROM `cse_authorized` 
		WHERE `auth_id` = '" . $auth_id . "' 
		AND `auth_key` = '" .  $auth_key . "'";
		//echo $sql . "<br />";
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
		$error = array("error"=> array("text"=>"api 1 key error"));
		if (!is_object($customer)) {
			die(json_encode($error));
		} else {
			if ($customer->customer_id < 0) {
				die(json_encode($error));
			} else {
				$_SESSION['user_customer_id'] = $customer->customer_id;
			}
			//$_SESSION["cus_name"] = $customer->cus_name;
		}
	} else {
		$_SESSION['user_customer_id'] = passed_var("cus_id", "post");
	}
	
	//die(print_r($_SESSION));
	scrapeLiens($adj_number, $auth_id, $auth_key);
}
function scrapeLiens() {
	session_write_close();
	//die(print_r($_POST));
	$search_first_name = "";
	$search_last_name = "";
	$search_dob = "";
	$search_city = "";
	$search_zip_code = "";
	$blnSent = false;
	$ftp_server = "";
	//need adj
	$adj_number = passed_var("adj_number", "post");
	$lien_claimant = "";
	if (isset($_POST["lien_claimant"])) {
		$lien_claimant = passed_var("lien_claimant", "post");
	}
	
	if ($lien_claimant=="") {
		//die("adj_number:" . $adj_number);
		$first_three = substr($adj_number, 0, 3);
		if ($first_three!="ADJ") {
			die(json_encode(array("error"=>$adj_number . " is not a valid ADJ number.")));
		}
	}
	//get customer info
	try {
		$firm_name = "REQUESTOR";
		if (isset($_SESSION["cus_name"])) {
			$firm_name = $_SESSION["cus_name"];
		}
		$first_name = $_SESSION["requesterFirstName"];
		$last_name = $_SESSION["requesterLastName"];
		$email = $_SESSION["email"];
		
		$auth_cred = $_SESSION["auth_cred"];
		$ip_address = $_SESSION["ip_address"];
		$auth_cred = base64_decode($auth_cred);
		
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
		curl_setopt($ch, CURLOPT_POST, count($fields_string));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
		//curl_setopt($ch,CURLOPT_FOLLOWLOCATION,TRUE);
		
		//execute post
		$result = curl_exec($ch);

		if($result === false) {
			echo "186 Error Number:".curl_errno($ch)."<br>";
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
			$url_2 = "https://eams.dwc.ca.gov/LienSearch/LienSearchCriteria";
			$fields_2 = array("lienClaimant"=>$lien_claimant, "caseReferenceNumber"=>$adj_number, "lienReservationNumber"=>"", "paymentStatus"=>"ALL", "maxResults"=>"200", "injuryDateFrom"=>"", "injuryDateTo"=>"", "lienFileDateFrom"=>"", "lienFileDateTo"=>"", "nextHearingDateFrom"=>"", "nextHearingDateTo"=>"", "externalTransactionID"=>"", "lienFeePaymentDateFrom"=>"", "lienFeePaymentDateTo"=>"");
			//, "pageIdToken"=>"LienSearchCriteriaToken", "pageToken"=>$pageToken	
			//die(print_r($fields_2));
			$fields_string = "";
			foreach($fields_2 as $key=>$value) { 
				$fields_string .= $key.'='.$value.'&'; 
			}
				
			curl_setopt($ch, CURLOPT_URL,$url_2);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
			curl_setopt($ch, CURLOPT_HEADER, false); 
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_COOKIEFILE, "cookies.txt");
			
			$result = curl_exec($ch);
			//die($result);
			$headers = curl_getinfo($ch);
			
			$strpos = strpos($result, 'id="pageToken" value="');  // 
			$pageToken = "";
			if ($strpos > 0) {
				$endpos = strpos($result, ' />', $strpos);
				$pageToken = substr($result, $strpos, ($endpos - $strpos));
			}
			$pageToken = str_replace('id="pageToken" value="', '', $pageToken);
			$pageToken = str_replace('"', '', $pageToken);			
			
			//echo "tok_2:" . $pageToken . "<br />";
			//die();
			$arrName = explode(" ", $lien_claimant);
			$first_name = $arrName[0];
			unset($arrName[0]);
			$last_name = implode(" ", $arrName);
			$url_3 = "https://eams.dwc.ca.gov/LienSearch/LienSearch";
			$fields_3 = array("lienClaimant"=>$lien_claimant, "caseReferenceNumber"=>$adj_number, "lienReservationNumber"=>"", "paymentStatus"=>"ALL", "dwcProceedingStatus"=>"ALL", "maxResults"=>"200", "injuryDateFrom"=>"", "injuryDateTo"=>"", "lienFileDateFrom"=>"", "lienFileDateTo"=>"", "nextHearingDateFrom"=>"", "nextHearingDateTo"=>"", "externalTransactionID"=>"", "lienFeePaymentDateFrom"=>"", "lienFeePaymentDateTo"=>"", "pageIdToken"=>"LienSearchCriteriaToken", "pageToken"=>$pageToken);	
			
			//die(print_r($fields_3));
			$fields_string = "";
			foreach($fields_3 as $key=>$value) { 
				$fields_string .= $key.'='.$value.'&'; 
			}
			
			//echo $url_3 . "?" . $fields_string . "<br />";
			//die();
			curl_setopt($ch,CURLOPT_URL,$url_3);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
			curl_setopt($ch,CURLOPT_HEADER, false); 
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt ($ch, CURLOPT_COOKIEFILE, "cookies.txt");
			curl_setopt($ch, CURLOPT_POST, count($fields_string));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
							
			$result = curl_exec($ch);
			
			//die($result);			
			$headers = curl_getinfo($ch);
			
			//this is for later
			$doc = new DOMDocument();
			@$html = $doc->loadHTML($result);
			$arrFields1 = array();			
			$trs = $doc->getElementsByTagName("tr");			
			//die(print_r($trs));
			foreach($trs as $x=>$tr){
				//echo print_r($tr);
				if (trim($tr->nodeValue) != "") {		
					//echo trim($tr->nodeValue) . "<br />\r\n";
					$arrFields1[] = trim($tr->nodeValue);
				}					
			}
			//die(print_r($arrFields1));
			
			$arrOutput = array();	
			for($i = 0; $i < count($arrFields1); $i++){
				$element = $arrFields1[$i];
				$arrEl = explode("\n", $element);
				if (is_numeric(trim($arrEl[0]))) {
					//die(print_r($arrEl));
					/*
					if (trim($arrEl[12])!="MATRIX DOCUMENT IMAGING COVINA") {
						continue;
					}
					*/
					//die(print_r($arrEl));
					//unset($arrEl[0]);
					foreach($arrEl as $index=>$el) {
						$el = trim($el);	
						if ($index==0) {
							$lien_reservation_number = $el;
						}
						switch($index) {
							case 0:
								$arrOutput[$lien_reservation_number]["lien_reservation_number"] = $el;
								break;
							case 1:
								$arrOutput[$lien_reservation_number]["adj_number"] = $el;
								break;
							case 2:
								$arrOutput[$lien_reservation_number]["lien_filed_date"] = $el;
								break;
							case 3:
								$arrOutput[$lien_reservation_number]["injury_date"] = $el;
								break;
							case 4:
								$arrOutput[$lien_reservation_number]["next_hearing_date"] = $el;
								break;
							case 5:
								$arrOutput[$lien_reservation_number]["lien_amount"] = $el;
								break;
							case 6:
								$arrOutput[$lien_reservation_number]["lien_status"] = $el;
								break;
							case 8:
								$arrOutput[$lien_reservation_number]["lien_payment_date"] = $el;
								break;
							case 10:
								$arrOutput[$lien_reservation_number]["injured_worker_name"] = $el;
								break;
							case 12:
								$arrOutput[$lien_reservation_number]["lien_claimant_name"] = $el;
								break;
						}
						/*
						if (trim($el)!="") {
							$arrEl[$index] = trim($el);						
						} else {
							unset($arrEl[$index]);
						}
						*/
					}
					//die(print_r($arrEl));		
					//$row = "<tr><td align='left' valign='top'>" . implode("</td><td align='left' valign='top'>", $arrEl) . "</td></tr>";
					//$arrOutput[] = $row;
					//$arrOutput[] = $arrEl;
				} else {
					continue;
				}			
			}
		}
		$row = "<table>";
		if (count($arrOutput)>0) {
			$row .= "<tr><thead>";
			foreach($arrOutput as $out) {
				foreach($out as $out_index=>$val) {
					$row .= "<th align='left' valign='top'>" . $out_index . "</th>";
				}
				//only once
				break;
			}
			$row .= "</thead>
			</tr>
			<tbody>";
			$arrRows[] = $row;
			
			foreach($arrOutput as $out) {
				$row .= "<tr>";
				foreach($out as $out_index=>$val) {
					$row .= "<td align='left' valign='top'>" . $val . "</td>";
				}
				$row .= "</tr>";
			}
			$row .= "</tbody>
			</table>";
		}
		
		$html = $row;
		echo json_encode(array("success"=>"true", "html"=>$html, "data"=>$arrOutput));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
	}
}
function scrapeEams($adj_number, $auth_id = "", $auth_key = "") {
	//die("here");
	session_write_close();
	
	if ($auth_id == "" && $auth_key == "") {
		return false;
	}
	
	$search_first_name = "";
	$search_last_name = "";
	$search_dob = "";
	$search_city = "";
	$search_zip_code = "";
	$blnSent = false;
	$ftp_server = "";
	$remote_file = "";		
	$thexml = "";
	$login_result = false;
	//need adj
	$first_three = substr($adj_number, 0, 3);
	if ($first_three!="ADJ") {
		die(json_encode(array("error"=>$adj_number . " is not a valid ADJ number.")));
	}
	//get customer info
	try {
		$db = getConnection();
		
		$firm_name = "REQUESTOR";
		if (isset($_SESSION["cus_name"])) {
			$firm_name = $_SESSION["cus_name"];
		}
		$first_name = $_SESSION["requesterFirstName"];
		$last_name = $_SESSION["requesterLastName"];
		$email = $_SESSION["email"];
		
		$auth_cred = $_SESSION["auth_cred"];
		$ip_address = $_SESSION["ip_address"];
		$auth_cred = base64_decode($auth_cred);
		$arrCred = explode("|", $auth_cred);
		
		if(count($arrCred) > 2) {
			//transfer via ftp
			//include('Net/SFTP.php');
			$ftp_server = $arrCred[0];
			$ftp_username = str_replace("`", "|", $arrCred[1]);
			$ftp_pwd = str_replace("`", "|", $arrCred[2]);
			
			//die("serv:" . $ftp_server  . "\r\n" . $ftp_username . "\r\n" . $ftp_pwd);
			if ($ftp_server!="") {
				//upload to ftp
				$conn_id = ftp_connect($ftp_server); 
		
				// login with username and password 
				$login_result = @ftp_login($conn_id, $ftp_username, $ftp_pwd); 
				if (!$login_result) {
					exit('Login Failed');
				}
			}
		}
		 
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
		curl_setopt($ch, CURLOPT_POST, count($fields_string));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
		//curl_setopt($ch,CURLOPT_FOLLOWLOCATION,TRUE);
		
		//execute post
		$result = curl_exec($ch);
		//preg_match_all('|Set-Cookie: (.*);|U', $result, $matches);
		//die(print_r($matches));
		if($result === false) {
			echo "186 Error Number:".curl_errno($ch)."<br>";
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
			//https://eams.dwc.ca.gov/LienSearch/LienSearch
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
			curl_setopt($ch, CURLOPT_POST, count($fields_string));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
				
			$result = curl_exec($ch);
			if($result === false) {
				echo "223 Error Number:".curl_errno($ch)."<br>";
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
				die(json_encode(array("error"=>"Not Found")));
			}

			//now process the data
			//$url = "https://eams.dwc.ca.gov/WebEnhancement/CaseFinder?partyId=-5205632355686940672&firstName=MARISSA&lastName=MORALES&caseNumber=ADJ9881786";
			//die($url);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_COOKIE, "cookies.txt");
			$result = curl_exec($ch);
			//die("res:" . $result);
			if($result === false) {
				echo "277 Error Number:".curl_errno($ch)."<br>";
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
				echo "296 Error Number:".curl_errno($ch)."<br>";
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
					echo "Case Details Error Number:".curl_errno($ch)."<br>";
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
			//die(print_r( $arrValues));
			
			$arrData = array();
			//from first scrape
			$client_first_name = $arrFirstValues[6]; $arrData["first_name"] = $client_first_name;
			$client_last_name = $arrFirstValues[7]; $arrData["last_name"] = $client_last_name;
			$client_city  = $arrFirstValues[8]; $arrData["city"] = $client_city;
			$client_zip  = $arrFirstValues[9]; $arrData["zip"] = $client_zip;
			$arrData["deu"] = $deu;
			$person_id = -1;
			
			$arrPreviousCases = array();

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
			if ($blnHearing) {
				if (count($arrBodyPartsIndexes) > 0) {
					$bodyparts_index = $arrBodyPartsIndexes[0];
					if ($bodyparts_index > 5) {
						//start with the value that has a date'
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
				//die(print_r($arrValues));
				
				//let's get hearings
				for($idx = $hearing_start_idx; $idx < $bodyparts_index; $idx = $idx + 5) { 
					$hearing_datetime = $arrValues[$idx];
					$hearing_type = $arrValues[$idx + 1];
					//$hearing_type = str_replace("C & R", "C and R", $hearing_type);
					$hearing_location = $arrValues[$idx + 2];
					$hearing_judge = $arrValues[$idx + 3];
					
					$driver_case = trim($arrValues[$idx + 4]);
					$arrHearings[] = array("date"=>$hearing_datetime, "type"=>$hearing_type, "location"=>$hearing_location, "judge"=>$hearing_judge, "driver_case"=>$driver_case);
				}
				//parties start here
				$party_idx = $idx; 
			}
			//die(print_r($arrHearings));
			$first_body_index = $party_idx;
			$last_body_index = $party_idx;
			if (count($arrBodyPartsIndexes) > 0) {
				//last body part is where we start
				$first_body_index = $arrBodyPartsIndexes[0];
				$last_body_index = $arrBodyPartsIndexes[count($arrBodyPartsIndexes) - 1];
				
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
			//echo "pixd:" . $party_idx . " -- " . $first_body_index . "\r\n";
			//die(print_r($arrValues));			
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
							$sql_lookup = "SELECT eams_ref_number FROM cse_eams_reps 
							WHERE firm_name = :partie_name";
							$db = getConnection();
							
							$stmt = $db->prepare($sql_lookup);
							$stmt->bindParam("partie_name", $partie_name);
							$stmt->execute();
							$eams_lookup = $stmt->fetchObject();
							
							$db = null;
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
						FROM cse_eams_carriers
						WHERE firm_name = :partie_name";
						$db = getConnection();
						
						$stmt = $db->prepare($sql_lookup);
						$stmt->bindParam("partie_name", $partie_name);
						$stmt->execute();
						$eams_lookup = $stmt->fetchObject();
						$db = null;
						if (isset($eams_lookup->eams_ref_number)) {
							$eams_ref_number = $eams_lookup->eams_ref_number;
						}
					}
					
					if (isset($arrRoles[$partie_role])) {
						$arrRoles[$partie_role] = $arrRoles[$partie_role] + 1;
					} else {
						$arrRoles[$partie_role] = 1;
					}
					
					$arrParties[str_replace(" ", "_", $partie_role)][] = array("name"=>$partie_name, "role"=>$partie_role, "address"=>$partie_address, "street"=>$arrAddress[0], "city"=>$city, "state"=>$state, "zip"=>$zip, "case_firm"=>$case_firm, "eams_ref_number"=>$eams_ref_number);
					
				}
			}
			//sort by role
			//usort($arrParties, 'sortByOption');
			//die(print_r($arrParties));
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
				echo "WebEnhance Error Number:".curl_errno($ch)."<br>";
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
			/*
			if ($party_idx > 5) {
				$event_start_idx = $event_start_idx + ($party_idx - 5);
			}
			*/
			/*
			if ($adj_number=="ADJ10042458") {
				echo $party_idx . " --> " . $hearing_start_idx . " -- " . $event_start_idx;
				die(print_r($arrValues));
			}
			*/
			$arrEvents = array();			
			if ($event_start_idx > 2) {
				if (isset($arrValues[$event_start_idx])) {
					for($idx = $event_start_idx; $idx < count($arrValues); $idx = $idx + 3) {
						if (isset($arrValues[$idx]) && isset($arrValues[$idx + 1]) && isset($arrValues[$idx + 2])) {
							//echo "index:" . $idx  . "<br />";
							$arrEvents[] = array("type"=>$arrValues[$idx + 1], "date"=>$arrValues[$idx + 2], "description"=>$arrValues[$idx]);
						}
						//die(print_r($arrEvents));
					}
				}
			}
			
			$arrOutput = array();
			$arrOutput["applicant"] = $arrData;
			$arrOutput["previous_cases"] = $arrPreviousCases;
			$arrOutput["hearings"] = $arrHearings;
			$arrOutput["bodyparts"] = $arrBodyParts;
			$arrOutput["roles"] = $arrRoleCount;
			$arrOutput["parties"] = $arrParties;
			$arrOutput["events"] = $arrEvents;
			//die(print_r($arrOutput));
			//close connection
			curl_close($ch);
			
			
			// creating object of SimpleXMLElement
			$xml_data = new SimpleXMLElement('<?xml version="1.0"?><data></data>');
			
			// function call to convert array to xml
			array_to_xml($arrOutput,$xml_data);
			
			//die(print_r($xml_data));
			//saving generated xml file; 
			$upload_dir = "../xml/" . $_SESSION['user_customer_id'];
			if (!file_exists($upload_dir)) {
				mkdir($upload_dir);
			}
			$result = $xml_data->asXML($upload_dir . '/lookup_' . $adj_number . '.xml');
			
			$file = $upload_dir . '/lookup_' . $adj_number . '.xml';
			$remote_file = 'lookup_' . $adj_number . '.xml';
			$thexml = file_get_contents($upload_dir . '/lookup_' . $adj_number . '.xml');
			
			if ($ftp_server=="" || !$login_result) {
				$arrOutput["xml"] = $thexml;
				die(json_encode($arrOutput));
			}
			if ($ftp_server!="" && $login_result) {
				
				//echo $thexml;
				
				 //put it up, content ($verify) will go into file ($filename) on ftp server
				 //$sftp->put($remote_file, $thexml);
				 
				 //$nList = $sftp->nlist();
				 if (ftp_put($conn_id, $remote_file, $file, FTP_ASCII)) {
				 	//echo "successfully uploaded $file to $remote_file\n";
				} else {
					//echo "There was a problem while uploading $file to $remote_file\n";
				}
				 $nList = ftp_nlist($conn_id, ".");
				 //die(print_r($nList));
				 if (in_array($remote_file, $nList)) {
					$blnSent = true;
					//remove original, we don't need to keep it since this is a lookup every time
					unlink($upload_dir . '/lookup_' . $adj_number . '.xml');
					
					//record the transaction
					$sql = "INSERT INTO cse_xml (`filename`, `customer_id`, `adj_number`, `ip_address`, `xml`)
					VALUES ('lookup_" . $adj_number . ".xml', '" . $_SESSION['user_customer_id'] .  "', '" . $adj_number . "','" . $ip_address . "', '" . addslashes($thexml) . "')";
					$db = getConnection();
					
					$stmt = $db->prepare($sql);
					$stmt->execute();
					
					$db = null;
				 }
			}
		}
		
		echo json_encode(array("success"=>$blnSent, "file"=>$remote_file, "xml"=>$thexml));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
	}
}
function searchEams($adj_number, $search_first_name, $search_last_name, $search_dob, $search_doi, $auth_id, $auth_key) {
	session_write_close();
	
	//die($search_doi . "\r\n" . date("m/d/Y", strtotime($search_doi)));
	
	$adj_number = passed_var("adj_number", "post");

	$search_city = passed_var("city", "post");
	$search_zip_code = passed_var("zip_code", "post");
	$party_id = passed_var("party_id", "post");

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
		curl_setopt($ch, CURLOPT_POST, count($fields_string));
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
			curl_setopt($ch, CURLOPT_POST, count($fields_string));
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
			$row_counter = 0;
			foreach($tds as $cell_index=>$td) {
				//echo $cell_index . " - " . $td->nodeValue . "\r\n";
				if (trim($td->nodeValue) != "" && $td->nodeValue != "View events") {
					$arrFirstValues[$row_counter][] = trim($td->nodeValue);
					//echo trim($td->nodeValue) . "\r\n";
				}
				if ($td->nodeValue=="View case detail") {
					$row_counter++;
				}
			}
			//die(print_r($arrFirstValues));
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
function scrapeItSearchEams() {
	//die(print_r($_POST));
	$auth_id = passed_var("auth_id", "post");
	$auth_key = passed_var("auth_key", "post");
	$adj_number = passed_var("adj_number", "post");
	$authorize_key = passed_var("authorize_key", "post");
	
	$ip_address = $_SERVER['REMOTE_ADDR'];
	
	//record the transaction
	$sql = "INSERT INTO cse_metric (`action`, `auth_id`, `authorize_key`, `auth_key`, `adj_number`, `ip_address`)
	VALUES ('search', '" . $auth_id . "', '" . $authorize_key . "', '" . $auth_key .  "', '" . $adj_number . "','" . $ip_address . "')";
	$db = getConnection();
	
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$db = null;
	$format = "";
	if (isset($_POST["format"])) {
		$format = passed_var("format", "post");
	}
	$_SESSION["requesterFirstName"] = passed_var("requesterFirstName", "post");
	$_SESSION["requesterLastName"] = passed_var("requesterLastName", "post");
	$_SESSION["email"] = passed_var("email", "post");
	$_SESSION["auth_cred"] = passed_var("auth_cred", "post");
	$_SESSION["ip_address"] = passed_var("ip_address", "post");
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
	
	$_SESSION["cus_name"] = passed_var("cus_name", "post");
	$adj_number = passed_var("adj_number", "post");
	$search_first = passed_var("first_name", "post");
	$search_last = passed_var("last_name", "post");
	$search_dob = passed_var("dob", "post");
	$search_doi = passed_var("doi", "post");
	
	$format = "";
	if (isset($_POST["format"])) {
		$format = passed_var("format", "post");
	}
	$_SESSION["requesterFirstName"] = passed_var("requesterFirstName", "post");
	$_SESSION["requesterLastName"] = passed_var("requesterLastName", "post");
	$_SESSION["email"] = passed_var("email", "post");
	$_SESSION["auth_cred"] = passed_var("auth_cred", "post");
	$_SESSION["ip_address"] = passed_var("ip_address", "post");
	$_SESSION["format"] = $format;
	
	//die(print_r($_SESSION));
	searchEams($adj_number, $search_first, $search_last, $search_dob, $search_doi, $auth_id, $auth_key);
}
function sortByOption($a, $b) {
	return strcmp($a['role'], $b['role']);
}
function array_to_xml( $data, &$xml_data ) {
    foreach( $data as $key => $value ) {
        if( is_array($value) ) {
            if( is_numeric($key) ){
                $key = 'item'.$key; //dealing with <0/>..<n/> issues
            }
            $subnode = $xml_data->addChild($key);
            array_to_xml($value, $subnode);
        } else {
            $xml_data->addChild("$key",htmlspecialchars("$value"));
        }
     }
}
?>
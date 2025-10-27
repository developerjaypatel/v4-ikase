<?php
$app->post('/envelope/create', authorize('user'), 'createEnvelope');
$app->post('/envelope/html', authorize('user'), 'htmlEnvelope');
$app->post('/pdf/create', authorize('user'),	'generatePDF');
$app->get('/pdf/test', 'testPDF');

function testPDF() {
	//$bat = "C:\\inetpub\\wwwroot\\ikase.org\\uploads\\1064\\4883\\eams_forms\\pdftk.bat";
	$bat = "pdftk C:\\inetpub\\wwwroot\\ikase.org\\eams_forms\\app_cover.pdf fill_form C:\\inetpub\\wwwroot\\ikase.org\\uploads\\1064\\4883\\eams_forms\\app_cover.fdf output C:\\inetpub\\wwwroot\\ikase.org\\uploads\\1064\\4883\\eams_forms\\test_successful.pdf";
	
	echo $bat;
	passthru($bat);
	
	echo "<br /><br />If you run the code above in PowerShell on the server via RDP, you will then find <strong>C:\\inetpub\\wwwroot\\ikase.org\\uploads\\1064\\4883\\eams_forms\\test_successful.pdf</strong> has been created.  Right now, the server is not allowing PHP to passthru the pdftk command, and so it doesn't work.<br /><br />
Directory Listing for `uploads\\1064\\4883\\eams_forms\\`:<br />";
	
	$dir = "C:\\inetpub\\wwwroot\\ikase.org\\uploads\\1064\\4883\\eams_forms\\";
	$files = scandir($dir);
	$blnFound = false;
	foreach ($files as $file) {
		if ($file=="." || $file=="..") {
			continue;
		}
		if ($file=="test_successful.pdf") {
			$file = "<span style='font-weight:bold; font-size:20px; background:lime'>" . $file . "&nbsp;&#10003;</span>";
			$blnFound = true;
		}
		echo $file .  "<br />";
	}
	
	if (!$blnFound) {
		echo "<span style='font-weight:bold; font-size:20px; background:red; color:white'>Not Found X</span>";
	}
}
function partieNameAddress($partie) {
	$return = "";
	if (is_object($partie)) {
		$return = $partie->company_name;
		if ($partie->street!="") {
			$return .= "\r\n" . $partie->street;
			if ($partie->suite!="") {
				$return .= ", " . $partie->suite;
			}
			$return .= "\r\n" . $partie->city . ", " . $partie->state . ", " . $partie->zip;
		}
	}
	return $return;
}
function getCustomerInfo($customer_id = "") {
	if ($customer_id=="") {
		$customer_id = $_SESSION['user_customer_id'];
	}
	$sql = "SELECT cus.*
		FROM `ikase`.`cse_customer` cus 
		WHERE cus.customer_id = :customer_id";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$customer = $stmt->fetchObject();
		$db = null;
		//die($sql);

        return $customer;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function createEnvelope() {
	$output_fileformat = passed_var("output_fileformat", "post");
	if ($output_fileformat=="html") {
		htmlEnvelope();
		die();
	}
	$customer_id =  $_SESSION['user_customer_id'];
	$corporation_id =  passed_var("corporation_id", "post");
	$partie_type = passed_var("partie_type", "post");
	
	$additional = "";
	if (isset($_POST["additional"])) {
		$additional = passed_var("additional", "post");
	}
	if ($partie_type=="applicant") {
		$letter_partie = getPersonInfo($corporation_id);
	} else {
		$letter_partie = getCorporationInfo($corporation_id);
	}
	//die(print_r($letter_partie));
	$customer = getCustomerInfo();
	
	$customer_full_name = $customer->cus_name_first;
	if ($customer->cus_name_middle!="") {
		$customer_full_name .= " " . $customer->cus_name_middle;
	}
	$customer_name = $_SESSION['user_customer_name'];
	if ($customer->letter_name!="") {
		$customer_name = $customer->letter_name;
	}
	$arrReplace = array();
	//fdf

	$fdf_file = "../eams_forms/envelope_plain.fdf";
	//bold for patel
	if ($_SESSION["user_customer_id"]==1042) {
		$fdf_file = "../eams_forms/envelope_bold.fdf";
//		die($somecontent );
	}
	$somecontent = file_get_contents($fdf_file);
	
	if ($_SESSION["user_customer_id"]==1042) {
		$customer_name = strtoupper($customer_name);
		$customer->cus_street = strtoupper($customer->cus_street);
		$customer->cus_city = strtoupper($customer->cus_city);
		$customer->cus_state = strtoupper($customer->cus_state);
		$customer->cus_zip = strtoupper($customer->cus_zip);
	}
	pdfReplacement('FIRMNAME', $customer_name, $somecontent, $arrReplace);
	pdfReplacement('FIRMADDRESS', $customer->cus_street, $somecontent, $arrReplace);
	pdfReplacement('FIRMCITY', $customer->cus_city, $somecontent, $arrReplace);
	pdfReplacement('FIRMSTATE', $customer->cus_state, $somecontent, $arrReplace);
	pdfReplacement('FIRMZIP', $customer->cus_zip, $somecontent, $arrReplace);
	
	
	if ($partie_type!="applicant") {
		if ($letter_partie->type == "carrier" || $letter_partie->type == "defense" || $letter_partie->type == "prior_attorney") {
			$letter_name = getAdhocsInfo("", $corporation_id, "letter_name");
			if (count($letter_name) > 0) {
				if ($letter_name[0]->adhoc_value!="") {
					$letter_partie->company_name = $letter_name[0]->adhoc_value;
				}
			}		
		}
	}
	
	//letter recipient
	$arrRecipient = array();
	if ($partie_type=="applicant") {
		if (trim($letter_partie->full_name)!="") {
			$arrRecipient[] = $letter_partie->full_name;
		}
	}
	if ($partie_type=="applicant") {
		if ($letter_partie->company_name!="") {
			$arrRecipient[] = $letter_partie->company_name;
		}
	} else {
		$arrRecipient[] = $letter_partie->company_name;
	}
	
	if ($partie_type!="applicant") {
		if (trim($letter_partie->full_name)!="") {
			$arrRecipient[] = $letter_partie->employee_title . ": " . $letter_partie->full_name;
		}
	}
	$arrRecipient[] = $letter_partie->street;
	if ($letter_partie->suite!="") {
		$arrRecipient[] = $letter_partie->suite;
	}
	$arrRecipient[] = $letter_partie->city . ", " . $letter_partie->state . " " . $letter_partie->zip;
	
	
	if ($additional=="y" && $letter_partie->additional_addresses!="") {
		$arrRecipient = array();
		$arrRecipient[] = $letter_partie->company_name;
		$additional_addresses = $letter_partie->additional_addresses;
		$letter_partie = json_decode($additional_addresses);
		
		$arrRecipient[] = $letter_partie->address_2[2];
		if ($letter_partie->address_2[1]!="") {
			$arrRecipient[] = $letter_partie->address_2[1];
		}
		$arrRecipient[] = $letter_partie->address_2[3] . ", " . $letter_partie->address_2[4] . " " . $letter_partie->address_2[5];
		
		
	}
	$recipient = implode("\r\n", $arrRecipient);
	//die($recipient);
	//caps for patel
	if ($_SESSION["user_customer_id"]==1042) {
		$recipient = strtoupper($recipient);
	}
	
	pdfReplacement('RECIPIENT', $recipient, $somecontent, $arrReplace);
	//$PARTIENAME$\n$PARTIEFULLNAME$\n$PARTIESTREET$\n$PARTIECITY$, $PARTIESTATE$ $PARTIEZIP$
	
	//output
	$host = $_SERVER['HTTP_HOST'];
	pdfReplacement("DESTINATION", "http://" . $host . "/eams_forms/", $somecontent, $arrReplace);
	/*
	if ($_SESSION["user_customer_id"]==1134) {
		$arrReplace["FIRMNAME"] = ucwords(strtolower($arrReplace["FIRMNAME"]));
		$arrReplace["FIRMADDRESS"] = ucwords(strtolower($arrReplace["FIRMADDRESS"]));
		$arrReplace["FIRMCITY"] = ucwords(strtolower($arrReplace["FIRMCITY"]));
		
		//die(print_r($arrReplace));
	}
	*/
	//special case for shelleygraff
	if ($_SESSION["user_customer_id"]==1057) {
		$somecontent = str_replace("envelope_plain.pdf", "envelope_plain_1057.pdf", $somecontent);
	}
	//special case for torres
	if ($_SESSION["user_customer_id"]==1134) {
		$somecontent = str_replace("envelope_plain.pdf", "envelope_plain_1134.pdf", $somecontent);
	}
	
	$form_name = "envelope";
	//output
	$destination_folder = "D:/uploads/" . $_SESSION['user_customer_id'] . "/envelopes/";
	if (!is_dir($destination_folder)) {
		mkdir($destination_folder, 0755, true);
	}
	$filename = $destination_folder . $form_name . "_plain.fdf";
	if ($_SESSION["user_customer_id"]==1042) {
		$filename = $destination_folder . $form_name . "_bold.fdf";
	}
	$filename_output =  $destination_folder . $form_name . "_" . $corporation_id . ".pdf";
	
	if (file_exists($filename)) {
		unlink($filename);
	}
	if (!$handle = fopen($filename, 'w')) {
		 echo "Cannot open file ($filename)";
		 exit;
	}
	
	// Write $somecontent to our opened file.
	if (fwrite($handle, $somecontent) === FALSE) {
	   echo "Cannot write to file ($filename)";
	   exit;
	}
	$filename = $_SERVER['DOCUMENT_ROOT'] . "\\uploads\\" . $_SESSION['user_customer_id'] . "\\envelopes\\" . $form_name . "_plain.fdf";
	$pdftk_output =  $_SERVER['DOCUMENT_ROOT'] . "\\uploads\\" . $_SESSION['user_customer_id'] . "\\envelopes\\" . $form_name . "_" . $corporation_id . ".pdf";
	$file_counter = 1;
	$output_store_name = $form_name . "_" . $corporation_id . ".pdf";
	
	$source_dir = $_SERVER['DOCUMENT_ROOT'] . '\\eams_forms\\';
	
	
	$nopublish = "y";
	if ($nopublish=="y") {
		system("pdftk " . $source_dir . $form_name . "_plain.pdf fill_form " . $filename. " output " . $pdftk_output . " 2>&1", $retval);
	} else {
		$filename = str_replace("../", "", $filename);
		$filename_output = "http://" . $host . "/" . $filename;
	}
	$activity = "Envelope generated by " . $_SESSION['user_name'];
	
	echo json_encode(array("file"=>$destination_folder . $output_store_name, "activity_id"=>""));
}
function htmlEnvelope() {
	$customer_id =  $_SESSION['user_customer_id'];
	$corporation_id =  passed_var("corporation_id", "post");
	$partie_type = passed_var("partie_type", "post");
	if ($partie_type=="applicant") {
		$letter_partie = getPersonInfo($corporation_id);
	} else {
		$letter_partie = getCorporationInfo($corporation_id);
		
		//die(print_r($letter_partie));
	}
	$customer = getCustomerInfo();
	
	$customer_full_name = $customer->cus_name_first;
	if ($customer->cus_name_middle!="") {
		$customer_full_name .= " " . $customer->cus_name_middle;
	}
	
	$arrReplace = array();
	//template
	$somecontent = file_get_contents("../templates/envelope_standard.htm");
	
	pdfReplacement('FIRMNAME', str_replace("", "", $_SESSION['user_customer_name']), $somecontent, $arrReplace);
	pdfReplacement('FIRMADD1', $customer->cus_street, $somecontent, $arrReplace);
	pdfReplacement('FIRMADD2', "", $somecontent, $arrReplace);
	
	pdfReplacement('FIRMCITY', $customer->cus_city, $somecontent, $arrReplace);
	pdfReplacement('FIRMSTATE', $customer->cus_state, $somecontent, $arrReplace);
	pdfReplacement('FIRMZIP', $customer->cus_zip, $somecontent, $arrReplace);
	
	if ($_SESSION["user_customer_id"]==1057) {
		$somecontent = str_replace("margin-left:4.0in;text-indent:-4.0in", "margin-left:4.0in;text-indent:-4.0in;font-size:12.0pt", $somecontent);
	}
	if ($partie_type!="applicant") {
		if ($letter_partie->type == "carrier" || $letter_partie->type == "defense" || $letter_partie->type == "prior_attorney") {
			$letter_name = getAdhocsInfo("", $corporation_id, "letter_name");
			if (count($letter_name) > 0) {
				$letter_partie->company_name = $letter_name[0]->adhoc_value;
			}		
		}
	}

	//letter recipient
	$arrRecipient = array();
	if ($partie_type=="applicant") {
		if (trim($letter_partie->full_name)!="") {
			$arrRecipient[] = $letter_partie->full_name;
		}
	}
	if ($partie_type=="applicant") {
		if ($letter_partie->company_name!="") {
			$arrRecipient[] = $letter_partie->company_name;
		}
	} else {
		$arrRecipient[] = $letter_partie->company_name;
	}
	$arrRecipient[] = $letter_partie->street;
	if ($letter_partie->suite!="") {
		$arrRecipient[] = $letter_partie->suite;
	}
	$arrRecipient[] = $letter_partie->city . ", " . $letter_partie->state . " " . $letter_partie->zip;
	if ($partie_type!="applicant") {
		if (trim($letter_partie->full_name)!="") {
			$arrRecipient[] = $letter_partie->employee_title . ": " . $letter_partie->full_name;
		}
	}
	$recipient = "<br />" . implode("<br />", $arrRecipient);
	
	pdfReplacement('RECIPIENT', $recipient, $somecontent, $arrReplace);
	
	$form_name = "envelope";
	//output
	$destination_folder = "D:/uploads/" . $_SESSION['user_customer_id'] . "/envelopes/";
	if (!is_dir($destination_folder)) {
		mkdir($destination_folder, 0755, true);
	}
	$filename = $destination_folder . $form_name . "_" . $corporation_id . ".html";
	
	if (file_exists($filename)) {
		unlink($filename);
	}
	if (!$handle = fopen($filename, 'w')) {
		 echo "Cannot open file ($filename)";
		 exit;
	}
	
	// Write $somecontent to our opened file.
	if (fwrite($handle, $somecontent) === FALSE) {
	   echo "Cannot write to file ($filename)";
	   exit;
	}
	
	$activity = "Envelope generated by " . $_SESSION['user_name'];
	
	$activity_id = recordActivity("create", $activity, "", 0, "Forms");
	
	echo json_encode(array("file"=>$filename, "activity_id"=>$activity_id));
}
function generatePDF($form_name = "", $separator_title = "") {
	$case_id =  passed_var("case_id", "post");
	$blnInternalRequest = ($form_name != "");
	if ($form_name=="") {
		$form_name = trim(passed_var("eams_form_name", "post"));
	}
	if ($separator_title=="") {
		$separator_title = passed_var("separator_title", "post");
	}
	
	$blnApp = (strpos($form_name, "app") !== false);
	
	$nopublish = passed_var("nopublish", "post");
	$customer_id =  $_SESSION['user_customer_id'];
	$user_id =  $_SESSION['user_id'];
	$carrier_id = passed_var("carrier", "post");
	$employer_id = passed_var("employer", "post");
	
	$doi_id = passed_var("doi", "post");
	$primary_id = passed_var("primary", "post");
	$letter = passed_var("eamsInput", "post");
	$lien_holder_id = passed_var("lien_holder", "post");
	$defense_id = passed_var("defense", "post");
	
	$arrPartieIDs = array();
	$arrPDFParties = array();
	foreach($_POST as $fieldname=>$value) {
		//looking for parties
		$strpos = strpos($fieldname, "event_partie_");
		if ($strpos!==false) {
			$value = passed_var($fieldname, "post");
			$arrPartieIDs[] = $value;
		}
	}
	$partie_ids = implode(";", $arrPartieIDs);
	if ($partie_ids!="") {
		foreach($_POST as $fieldname=>$value) {
			//looking for parties
			$strpos = strpos($fieldname, "event_partie_");
			if ($strpos!==false) {
				$value = passed_var($fieldname, "post");
				$value = trim($value);
				
				//could be a person, could be a corporation
				$perspos = strpos($value, "P");
				$corppos = strpos($value, "C");
				
				if ($perspos===false && $corppos===false) {
					continue;
				}
				
				$partie = (object) '';
				
				if ($perspos!==false) {
					$person_id = str_replace("P", "", $value);
					$partie = getPersonInfo($person_id);
					$partie->type = "applicant";
					$applicant_email = $partie->email;
					$applicant_cell = $partie->cell_phone;
				}
				if ($corppos!==false) {
					$corporation_id = str_replace("C", "", $value);
					$partie = getCorporationInfo($corporation_id);
				}

				$arrPDFParties[] = partieNameAddress($partie);
			}
		}
	}
	
	
	
	//clean up
	if ($nopublish=="") {
		$nopublish = "n";
	}
	if ($form_name=="") {
		die();
	}
	$customer = getCustomerInfo();
	
	//venue
	$venue = getKaseVenueInfo($case_id);
	
	//get kase info, parties, etc..
	$kase = getKaseInfo($case_id);
	
	$work = getWorkHistory($case_id);
	$work_history_rate = "";
	$work_history_rateinterval = "";
	$disability_percent_total = "";
	$prior_permanent_disabled = "";
	$preexisting_disability = "";
	$previous_claim_number = "";
	$SSD_benefits = "";
	$SSD_payments = "";
	
	$work = json_decode($work);
	if (is_object($work)) {
		$work_historys = json_decode($work->work_history_info);
		
		foreach($work_historys  as $work_history) {
			$name = str_replace("Input", "", $work_history->name);
			$value = $work_history->value;
			
			if ($name=="work_history_rate") {
				$work_history_rate = $value;
			}
			if ($name=="work_history_rateinterval") {
				$work_history_rateinterval = $value;
			}
			if ($name=="disability_percent_total") {
				$disability_percent_total = $value;
			}
			if ($name=="prior_permanent_disabled") {
				$prior_permanent_disabled = $value;
			}
			if ($name=="preexisting_disability") {
				$preexisting_disability = $value;
			}
			if ($name=="previous_claim_number") {
				$previous_claim_number = $value;
			}
			if ($name=="SSD_benefits") {
				$SSD_benefits = $value;
			}
			if ($name=="SSD_payments") {
				$SSD_payments = $value;
			}
		}
	}
	
	$attorney_full_name = $kase->attorney_full_name;
	if ($attorney_full_name=="") {
		$att = getUserByNickname($kase->attorney);
		
		if (is_object($att)) {
			$attorney_full_name = $att->user_name;
		}
	}
	
	if ($attorney_full_name=="" && $kase->supervising_attorney!='') {
		if (!is_numeric($kase->supervising_attorney)) {
			$the_worker = getUserByNickname($kase->supervising_attorney);
		} else {
			$the_worker = getUserInfo($kase->supervising_attorney);
		}
		if (is_object($the_worker)) {
			$attorney_full_name = $the_worker->user_name;
		}
	}
	//break up attorney
	$attorney_first_name = "";
	$attorney_middle_name = "";
	$attorney_last_name = "";
	if ($attorney_full_name!="") {
		$arrAttName = explode(" " , $attorney_full_name);
		$attorney_first_name = $arrAttName[0];
		if (count($arrAttName) > 2) {
			$attorney_middle_name = $arrAttName[1];
			unset($arrAttName[1]);
		}
		unset($arrAttName[0]);
		$attorney_last_name = implode(" ", $arrAttName);
	}
	$attorney_full_name = str_replace(", Esq.", "", $attorney_full_name);
	$attorney_full_name = str_replace(", ESQ.", "", $attorney_full_name);
	//die($attorney_full_name);
	
	$worker_full_name = $kase->worker_full_name;
	$worker_email = "";
	$worker_job = "";
	if (!is_numeric($kase->worker_name)) {
		if ($kase->worker_name!="") {
			$the_worker = getUserByNickname($kase->worker_name);
			$worker_full_name = $the_worker->user_name;
			$worker_email = $the_worker->user_email;
			$worker_job = $the_worker->job;
		}
	}
	if ($kase->worker_name=="" && $kase->worker!="") {
		if (!is_numeric($kase->worker)) {
			$the_worker = getUserByNickname($kase->worker);
		} else {
			$the_worker = getUserInfo($kase->worker);
		}
		if (is_object($the_worker)) {
			$worker_full_name = $the_worker->user_name;
			$worker_email = $the_worker->user_email;
			$worker_job = $the_worker->job;
			
			$kase->worker_name = $kase->worker;
		}
	}
	//injury info
	$arrRelatedCases = array();
	$related_cases = getInjuriesInfo($case_id);
	//related cases
	for ($intD=0; $intD<count($related_cases); $intD++) {
		$related_case = $related_cases[$intD];
		
		if ($related_case->injury_id == $doi_id) {
			unset($related_cases[$intD]);
			continue;
		}
		
		$related_case->start_date = date("m/d/Y", strtotime($related_case->start_date));
		
		$arrRelatedCases[count($arrRelatedCases)] = array("adj_number"=>$related_case->adj_number, "doi"=>$related_case->start_date);
	}
	while (count($arrRelatedCases) < 5) {
		$arrRelatedCases[] = array("adj_number"=>"", "doi"=>"");
	}
	$injury = getInjuryInfo($doi_id);
	
	$arrAllADJ = array();
	if (isset($_POST["show_all_adjs"])) {
		$show_all_adjs = passed_var("show_all_adjs", "post");
		
		foreach($arrRelatedCases as $rel) {
			$arrAllADJ[] = $rel["adj_number"];
		}
		$all_injuries = getKaseInjuriesInfo($case_id);
		foreach($all_injuries as $inj) {
			$arrAllADJ[] = $inj->adj_number;
		}
	}
	
	$doi_start_date = date("m/d/Y", strtotime($injury->start_date));
	$ct_choice = 1;
	if ($injury->end_date != "0000-00-00") {
		$doi_end_date = date("m/d/Y", strtotime($injury->end_date));
		$ct_choice = 2;
	} else {
		$doi_end_date = "";
	}
	$body_parts = getBodypartsInfo($case_id, $doi_id);
	$arrBodyParts = array();
	foreach($body_parts as $body_part) {
		$arrDescription = explode("-", $body_part->description);
		$body_part->description = trim($arrDescription[0]);
		$arrBodyParts[] = $body_part->code . " - " . $body_part->description;
	}
	while (count($arrBodyParts) < 4) {
		$arrBodyParts[] = " ";
	}
	
	$arrAdditionalBodyParts = array();
	if (count($arrBodyParts) > 4) {
		for($intB=5; $intB < count($arrBodyParts); $intB++) {
			$arrAdditionalBodyParts[] = $arrBodyParts[$intB];
		}
	}
	
	//client
	$dob = $kase->dob;
	if (isValidDate($dob, 'Y-m-d')) {
		$dob = date("m/d/Y", strtotime($dob));
	}
	//capture parties for pos
	$arrParties = array();
	
	//employer
	if ($employer_id=="") {
		$employer_id = $kase->employer_id;
	}
	$employer = getCorporationInfo($employer_id);
	
	$employer_salutation = "Sir/Madam";
	$employer_first_name = "";
	$employer_last_name = "";
	$employer_full_name = "";
	if (is_object($employer)) {
		if (count($employer) > 0) {	
			$arrParties[] = partieNameAddress($employer);
			if ($employer->full_name!="") {
				$employer_salutation = $employer->full_name;
				if ($employer->salutation!="") {
					$employer_salutation = 	$employer->salutation . " " . $employer_salutation;
				}
			}
		}
		
		$arrName = explode(" ", trim($employer->full_name));
		$employer_first_name = $arrName[0];
		$employer_last_name = trim(str_replace($employer_first_name, "", $employer->full_name));
		$employer_full_name = $employer->full_name;
	}
	//insurance carrier
	$carrier = getCorporationInfo($carrier_id);
	
	$carrier_adhoc = getAdhocsInfo($kase->id, $carrier_id, "claim_number");
	$claim_number = "";
	if (count($carrier_adhoc) > 0) {
		$claim_number = $carrier_adhoc[0]->adhoc_value;
	}
	//primary medical provider
	$primary = getCorporationInfo($primary_id);
	$primary_adhoc = getAdhocsInfo($kase->id, $primary_id, "date_completed");
	$medical_report_date_completed = " ";
	if (count($primary_adhoc) > 0) {
		$medical_report_date_completed = $primary_adhoc[0]->adhoc_value;
	}
	
	$lien_holder = getCorporationInfo($lien_holder_id);
	
	$primary_salutation = "Sir/Madam";
	$lien_holder_adhoc = getAdhocsInfo($kase->id, $lien_holder_id, "lien_date");
	$lien_date = "";
	if (count($lien_holder_adhoc) > 0) {
		$lien_date = $lien_holder_adhoc[0]->adhoc_value;
	}
	
	if (count($primary) > 0) {	
		if (isset($primary->full_name)) {
			if ($primary->full_name!="") {
				$arrParties[] = partieNameAddress($primary);
				$primary_salutation = $primary->full_name;
				if ($primary->salutation!="") {
					$primary_salutation = 	$primary->salutation . " " . $primary_salutation;
				}
			}
		}
	}
	$defense = getCorporationInfo($defense_id);
	//defense attorney
	$defense_salutation = "Sir/Madam";
	if (count($defense) > 0) {	
		if (isset($defense->full_name)) {
			$arrParties[] = partieNameAddress($defense);
			$defense_salutation = $defense->full_name;
			if ($defense->salutation!="") {
				$defense_salutation = 	$defense->salutation . " " . $defense_salutation;
			}
		}
		$defense_salutation .= ", ESQ";
	}
	
	$prior_attorney = getKasePartiesInfo($case_id, "prior_attorney");
	if (count($prior_attorney) > 0) {
		$prior_attorney = $prior_attorney[0];
	} else {
		$prior_attorney = array();
	}
	//prior attorney
	$prior_attorney_salutation = "Sir/Madam";
	if (count($prior_attorney) > 0) {	
		if (isset($prior_attorney->full_name)) {
			$arrParties[] = partieNameAddress($prior_attorney);
			$prior_attorney_salutation = $prior_attorney->full_name;
			if ($prior_attorney->salutation!="") {
				$prior_attorney_salutation = $prior_attorney->salutation . " " . $prior_attorney_salutation;
			}
			$prior_attorney_salutation .= ", ESQ";
		}
	}
	
	$lien_holder_salutation = "Sir/Madam";
	if (isset($lien_holder->full_name)) {	
		$arrParties[] = partieNameAddress($lien_holder);
		if (trim($lien_holder->full_name)!="") {
			$lien_holder_salutation = $lien_holder->full_name;
			if ($lien_holder->salutation!="") {
				$lien_holder_salutation = 	$lien_holder->salutation . " " . $lien_holder_salutation;
			}
		}
		$lien_holder_salutation .= ", ESQ";
	}
	
	//all the claim numbers
	$sql_claims = "SELECT DISTINCT adhoc_value claim_number
	FROM cse_corporation_adhoc cadhoc
	INNER JOIN cse_corporation corp
	ON cadhoc.corporation_uuid = corp.corporation_uuid AND corp.deleted = 'N'
	INNER JOIN cse_case_corporation ccorp
	ON corp.corporation_uuid = ccorp.corporation_uuid AND ccorp.deleted = 'N'
	INNER JOIN cse_case ccase
	ON ccorp.case_uuid = ccase.case_uuid AND ccase.deleted = 'N'
	WHERE cadhoc.adhoc = 'claim_number'
	AND ccase.case_id = " . $case_id;
	$arrClaimNumbers = array();
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql_claims);
		$stmt->execute();
		$all_claim_numbers = $stmt->fetchAll(PDO::FETCH_OBJ);
		foreach($all_claim_numbers as $the_claim) {
			$arrClaimNumbers[] = $the_claim->claim_number;
		}
		$stmt->closeCursor(); $stmt = null; $db = null;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
	}
	
	//fdf
	$somecontent = file_get_contents("../eams_forms/" . $form_name . ".fdf");
	$arrReplace = array();
	
	//break up ssn, ein
	$arrSSN = str_split($kase->ssn);
	$kase->ein = str_replace("-", "", $kase->ein);
	$arrEIN = str_split($kase->ein);
	for($int=0; $int < 10; $int++) {
		if (!isset($arrSSN[$int])) {
			$arrSSN[$int] = "";
		}
		if (!isset($arrEIN[$int])) {
			$arrEIN[$int] = "";
		}
		pdfReplacement("SSN" . ($int+1), $arrSSN[$int], $somecontent, $arrReplace);
		pdfReplacement("EIN" . ($int+1), $arrSSN[$int], $somecontent, $arrReplace);
	}
	
	pdfReplacement("CASENAME", $kase->name, $somecontent, $arrReplace);
	pdfReplacement("CASENUMBER", $injury->adj_number, $somecontent, $arrReplace);
	
	$intCounter = 2;
	foreach($arrAllADJ as $all_adj) {
		pdfReplacement("CASENUMBER_" . $intCounter, $all_adj, $somecontent, $arrReplace);
		
		$intCounter++;
		if ($intCounter > 5) {
			break;
		}
	}
	if ($intCounter < 6) {
		for($intA =  $intCounter; $intA < 6; $intA++) {
			pdfReplacement("CASENUMBER_" . $intA, "", $somecontent, $arrReplace);
		}
	}
	
	pdfReplacement("CASEID", $case_id, $somecontent, $arrReplace);
	pdfReplacement("CUSTOMERID", $customer_id, $somecontent, $arrReplace);
	pdfReplacement("FORMNAME", $form_name, $somecontent, $arrReplace);
	pdfReplacement('ASSIGNEDATTORNEY', $attorney_full_name . ", Esq.", $somecontent, $arrReplace);
	
	pdfReplacement('ALLCASENUMBER', strtoupper($injury->adj_number), $somecontent, $arrReplace);
	pdfReplacement('TITLE', $separator_title, $somecontent, $arrReplace);
	pdfReplacement('CLIENTSSNO', $kase->ssn, $somecontent, $arrReplace);
	pdfReplacement('AMENDEDAPPLICATION', 1, $somecontent, $arrReplace);
	pdfReplacement('VENUECHOICE', 0, $somecontent, $arrReplace);
	pdfReplacement('COURTNAME', $kase->venue_abbr, $somecontent, $arrReplace);
	
	$appointment_judge = "";
	if (isset($_POST["appointment_judge"])) {
		$appointment_judge = passed_var("appointment_judge", "post");
		if ($appointment_judge!="") {
			pdfReplacement('JUDGE', $appointment_judge, $somecontent, $arrReplace);
		}
	}
	
	if (is_object($venue)) {
		if ($appointment_judge=="") {
			pdfReplacement('JUDGE', $venue->full_name, $somecontent, $arrReplace);
		}
		pdfReplacement('COURTADD11', $venue->street, $somecontent, $arrReplace);
		pdfReplacement('COURTADD12', $venue->suite, $somecontent, $arrReplace);
		pdfReplacement('COURTADD21', $venue->suite, $somecontent, $arrReplace);
		pdfReplacement('COURTADD1', $venue->street, $somecontent, $arrReplace);
		pdfReplacement('COURTADD2', $venue->suite, $somecontent, $arrReplace);
		pdfReplacement('COURTCITY', $venue->city, $somecontent, $arrReplace);
		pdfReplacement('COURTSTATE', $venue->state, $somecontent, $arrReplace);
		pdfReplacement('COURTZIP', $venue->zip, $somecontent, $arrReplace);
		pdfReplacement('COURTCITYSTATEZIP1', $venue->city . ", " . $venue->state . " " . $venue->zip, $somecontent, $arrReplace);
		pdfReplacement('COURTCITYSTZIP', $venue->city . ", " . $venue->state . " " . $venue->zip, $somecontent, $arrReplace);
	}
	pdfReplacement('ALLCLAIMNO', implode("; ", $arrClaimNumbers), $somecontent, $arrReplace);
	pdfReplacement('SENDCLAIMNUMBER', $claim_number, $somecontent, $arrReplace);
	pdfReplacement('SENDCLAIMNUMBER2', " ", $somecontent, $arrReplace);
	pdfReplacement('SENDCLAIMNUMBER3', " ", $somecontent, $arrReplace);
	pdfReplacement('SENDCLAIMNUMBER4', " ", $somecontent, $arrReplace);
	pdfReplacement('SENDCLAIMNUMBER5', " ", $somecontent, $arrReplace);
	if ($letter=="") {
		$letter = " ";
	}
	pdfReplacement('POSDESCRIPTION', $letter, $somecontent, $arrReplace);
	pdfReplacement('OTHERISSUEEXPLANATION', $letter, $somecontent, $arrReplace);
	pdfReplacement('DORSTATEMENT', $letter, $somecontent, $arrReplace);
	
	if ($kase->last_name == "" && $kase->full_name != "") {
		$arrFullName = explode(" ", trim($kase->full_name));
		if (count($arrFullName) > 1) {
			$kase->last_name = $arrFullName[count($arrFullName) - 1];
		}
	}
	
	pdfReplacement('ASSIGNEDWORKER', $worker_full_name, $somecontent, $arrReplace);
	pdfReplacement('WORKERINITIALS', strtolower($kase->worker_name), $somecontent, $arrReplace);
	pdfReplacement('WORKEREMAIL', strtolower($worker_email), $somecontent, $arrReplace);
	pdfReplacement('WORKERUPPERINITIALS', $kase->worker_name, $somecontent, $arrReplace);
	
	//applicant
	pdfReplacement('CLIENTFIRSTNAME', strtr($kase->first_name, array('(' => '', ')' => '')), $somecontent, $arrReplace);
	pdfReplacement('CLIENTLASTNAME', strtr($kase->last_name, array('(' => '', ')' => '')), $somecontent, $arrReplace);
	pdfReplacement('APPLICANTNAME', strtr($kase->first_name . " " . $kase->last_name, array('(' => '', ')' => '')), $somecontent, $arrReplace);

	$kase_middle_name = $kase->middle_name;
	if ($kase->middle_name=="") {
		$kase_middle_name = " ";
	}
	pdfReplacement('CLIENTMIDDLENAME', strtr($kase_middle_name, array('(' => '', ')' => '')), $somecontent, $arrReplace);
	$applicant_full_name = $kase->first_name;
	if ($kase->middle_name!="") {
		$applicant_full_name .= " " . $kase->middle_name;
	}
	$applicant_full_name .= " " . $kase->last_name;
	if ($applicant_full_name=="") {
		$applicant_full_name = " ";
	}
	$applicant_full_name = strtr($applicant_full_name, array('(' => '', ')' => ''));
	pdfReplacement('CLIENTFULLNAME', $applicant_full_name, $somecontent, $arrReplace);
	pdfReplacement('CLIENTMIDINITIAL', $kase->middle_name, $somecontent, $arrReplace);
	pdfReplacement('CLIENTSUFFIX', " ", $somecontent, $arrReplace);
	
	$applicant_full_address = $kase->applicant_street . ", " . $kase->applicant_city . ", " . $kase->applicant_state . " " . $kase->applicant_zip;
	pdfReplacement('CLIENTFULLADD', $applicant_full_address, $somecontent, $arrReplace);
	pdfReplacement('CLIENTADD1', $kase->applicant_street, $somecontent, $arrReplace);
	pdfReplacement('CLIENTADD2', $kase->applicant_suite, $somecontent, $arrReplace);
	pdfReplacement('CLIENTCITY', $kase->applicant_city, $somecontent, $arrReplace);
	pdfReplacement('CLIENTSTATE', $kase->applicant_state, $somecontent, $arrReplace);
	pdfReplacement('CLIENTZIP', $kase->applicant_zip, $somecontent, $arrReplace);
	
	pdfReplacement('CLIENTCITYSTATEZIP', $kase->applicant_city . ", " . $kase->applicant_state . " " . $kase->applicant_zip, $somecontent, $arrReplace);
	
	pdfReplacement('CLIENTINTERNATIONALADD', ' ', $somecontent, $arrReplace);
	pdfReplacement('CLIENTDOB', $dob, $somecontent, $arrReplace);
	pdfReplacement('CLIENTOCCUP', $injury->occupation, $somecontent, $arrReplace);
	
	//other applicant. empty for now
	pdfReplacement('OTHERCLAIMTYPE', 'Off', $somecontent, $arrReplace);
	pdfReplacement('OTHERAPPNAME1', ' ', $somecontent, $arrReplace);
	pdfReplacement('OTHERAPPADD11', ' ', $somecontent, $arrReplace);
	pdfReplacement('OTHERAPPADD21', ' ', $somecontent, $arrReplace);
	pdfReplacement('OTHERAPPCITY1', ' ', $somecontent, $arrReplace);
	pdfReplacement('OTHERAPPSTATE1', ' ', $somecontent, $arrReplace);
	pdfReplacement('OTHERAPPZIP1', ' ', $somecontent, $arrReplace);	

	//employer
	pdfReplacement('EMPLINSURANCESTATUS', '0', $somecontent, $arrReplace);
	pdfReplacement('EMPLSALUT1', $employer_salutation, $somecontent, $arrReplace);
	pdfReplacement('EMPLFIRSTNAME1', $kase->first_name, $somecontent, $arrReplace);
	pdfReplacement('EMPLLASTNAME1', $kase->last_name, $somecontent, $arrReplace);
	pdfReplacement('EMPLMIDDLENAME1', "", $somecontent, $arrReplace);	
	if (is_object($employer)) {
		pdfReplacement('EMPLNAME1', $employer->full_name, $somecontent, $arrReplace);
		pdfReplacement('EMPLFIRM1', ucwords(strtolower($employer->company_name)), $somecontent, $arrReplace);
		pdfReplacement('EMPLADD11', $employer->street, $somecontent, $arrReplace);
		pdfReplacement('EMPLADD21', $employer->suite, $somecontent, $arrReplace);
		pdfReplacement('EMPLCITY1', $employer->city, $somecontent, $arrReplace);
		pdfReplacement('EMPLSTATE1', $employer->state, $somecontent, $arrReplace);
		pdfReplacement('EMPLZIP1', $employer->zip, $somecontent, $arrReplace);
		pdfReplacement('EMPLCITYSTATEZIP1', $employer->city . ", " . $employer->state . " " . $employer->zip, $somecontent, $arrReplace);
		pdfReplacement('EMPLINTERNATIONALADD1', ' ', $somecontent, $arrReplace);
		pdfReplacement('EMPLFULLADD', $employer->street . " " . $employer->suite . ", " . $employer->city . "," . $employer->state . " " . $employer->zip, $somecontent, $arrReplace);
		
		pdfReplacement('DFNTFIRM1', ucwords(strtolower($employer->company_name)), $somecontent, $arrReplace);
	} else {
		pdfReplacement('EMPLNAME1', ' ', $somecontent, $arrReplace);
		pdfReplacement('EMPLFIRM1', ' ', $somecontent, $arrReplace);
		pdfReplacement('EMPLADD11', ' ', $somecontent, $arrReplace);
		pdfReplacement('EMPLADD21', ' ', $somecontent, $arrReplace);
		pdfReplacement('EMPLCITY1', ' ', $somecontent, $arrReplace);
		pdfReplacement('EMPLSTATE1', ' ', $somecontent, $arrReplace);
		pdfReplacement('EMPLZIP1', ' ', $somecontent, $arrReplace);
		pdfReplacement('EMPLCITYSTATEZIP1', ' ', $somecontent, $arrReplace);
		pdfReplacement('EMPLINTERNATIONALADD1', ' ', $somecontent, $arrReplace);
		pdfReplacement('EMPLFULLADD', ' ', $somecontent, $arrReplace);
		
		pdfReplacement('DFNTFIRM1', ' ', $somecontent, $arrReplace);
	}
	pdfReplacement('DFNTNAME1', $kase->first_name . " " . $kase->last_name, $somecontent, $arrReplace);
	
	//insurance carrier, claims administrator
	if (isset($carrier->company_name)) {
		$arrParties[] = partieNameAddress($carrier);
		pdfReplacement('INSFIRM1', ucwords(strtolower($carrier->company_name)), $somecontent, $arrReplace);
		pdfReplacement('INSNAME1', ucwords(strtolower($carrier->company_name)), $somecontent, $arrReplace);
		
		pdfReplacement('INSADD11', $carrier->street, $somecontent, $arrReplace);
		pdfReplacement('INSADD12', $carrier->suite, $somecontent, $arrReplace);
		pdfReplacement('INSCITY1', $carrier->city, $somecontent, $arrReplace);
		pdfReplacement('INSSTATE1', $carrier->state, $somecontent, $arrReplace);
		pdfReplacement('INSZIP1', $carrier->zip, $somecontent, $arrReplace);
		pdfReplacement('INSCITYSTATEZIP1', $carrier->city . ", " . $carrier->state . " " . $carrier->zip, $somecontent, $arrReplace);
	} else {
		pdfReplacement('INSFIRM1', ' ', $somecontent, $arrReplace);
		pdfReplacement('INSNAME1', ' ', $somecontent, $arrReplace);
		pdfReplacement('INSADD11', ' ', $somecontent, $arrReplace);
		pdfReplacement('INSADD12', ' ', $somecontent, $arrReplace);
		pdfReplacement('INSCITY1', ' ', $somecontent, $arrReplace);
		pdfReplacement('INSSTATE1', ' ', $somecontent, $arrReplace);
		pdfReplacement('INSZIP1', ' ', $somecontent, $arrReplace);
		pdfReplacement('INSCITYSTATEZIP1', ' ', $somecontent, $arrReplace);
	}
	//defense attorney
	if (isset($defense->full_name)) {
		//clean up full name
		$defense->full_name = str_replace(", Esq.", "", $defense->full_name);
		$defense->full_name = str_replace(", ESQ", "", $defense->full_name);
		pdfReplacement('OPPCSALUT1', $defense_salutation, $somecontent, $arrReplace);
		pdfReplacement('OPPCNAME1', $defense->full_name . ", ESQ", $somecontent, $arrReplace);
		if ($defense->first_name=="") {
			//break it up
			$arrName = explode(" ", $defense->full_name);
			$defense->first_name = $arrName[0];
			unset($arrName[0]);
			$defense->last_name = implode(" ", $arrName);
		}
		pdfReplacement('OPPCFIRSTNAME', $defense->first_name, $somecontent, $arrReplace);
		pdfReplacement('OPPCLASTNAME', $defense->last_name, $somecontent, $arrReplace);
		pdfReplacement('OPPCFIRM1', $defense->company_name, $somecontent, $arrReplace);
		pdfReplacement('OPPCADD11', $defense->street, $somecontent, $arrReplace);
		pdfReplacement('OPPCCITY1', $defense->city, $somecontent, $arrReplace);
		pdfReplacement('OPPCSTATE1', $defense->state, $somecontent, $arrReplace);
		pdfReplacement('OPPCZIP1', $defense->zip, $somecontent, $arrReplace);
		pdfReplacement('OPPCADD12', $defense->suite, $somecontent, $arrReplace);
		pdfReplacement('OPPCADD21', $defense->suite, $somecontent, $arrReplace);
		pdfReplacement('OPPCPHONE1', $defense->phone, $somecontent, $arrReplace);
		pdfReplacement('OPPCFAX1', $defense->fax, $somecontent, $arrReplace);
		pdfReplacement('OPPCCITYSTATEZIP1', $defense->city . ", " . $defense->state . " " . $defense->zip, $somecontent, $arrReplace);
	} else {
		pdfReplacement('OPPCSALUT1',' ', $somecontent, $arrReplace);
		pdfReplacement('OPPCNAME1', ' ', $somecontent, $arrReplace);
		pdfReplacement('OPPCFIRM1', ' ', $somecontent, $arrReplace);
		pdfReplacement('OPPCADD11', ' ', $somecontent, $arrReplace);
		pdfReplacement('OPPCADD12', ' ', $somecontent, $arrReplace);
		pdfReplacement('OPPCADD21', ' ', $somecontent, $arrReplace);
		pdfReplacement('OPPCCITY1', ' ', $somecontent, $arrReplace);
		pdfReplacement('OPPCSTATE1', ' ', $somecontent, $arrReplace);
		pdfReplacement('OPPCZIP1', ' ', $somecontent, $arrReplace);
		pdfReplacement('OPPCPHONE1', ' ', $somecontent, $arrReplace);
		pdfReplacement('OPPCFAX1', ' ', $somecontent, $arrReplace);
		pdfReplacement('OPPCCITYSTATEZIP1', ' ', $somecontent, $arrReplace);
	}
	
	//prior attorney
	if (isset($prior_attorney->full_name)) {
		pdfReplacement('PRIASALUT1', $prior_attorney_salutation, $somecontent, $arrReplace);
		pdfReplacement('PRIANAME1', $prior_attorney->full_name . ", ESQ", $somecontent, $arrReplace);
		pdfReplacement('PRIAFIRM1', $prior_attorney->company_name, $somecontent, $arrReplace);
		pdfReplacement('PRIAADD11', $prior_attorney->street, $somecontent, $arrReplace);
		pdfReplacement('PRIACITY1', $prior_attorney->city, $somecontent, $arrReplace);
		pdfReplacement('PRIASTATE1', $prior_attorney->state, $somecontent, $arrReplace);
		pdfReplacement('PRIAZIP1', $prior_attorney->zip, $somecontent, $arrReplace);
		pdfReplacement('PRIAADD12', $prior_attorney->suite, $somecontent, $arrReplace);
		pdfReplacement('PRIAADD21', $prior_attorney->suite, $somecontent, $arrReplace);
		pdfReplacement('PRIAPHONE1', $prior_attorney->phone, $somecontent, $arrReplace);
		pdfReplacement('PRIAFAX1', $prior_attorney->fax, $somecontent, $arrReplace);
		pdfReplacement('PRIACITYSTATEZIP1', $prior_attorney->city . ", " . $prior_attorney->state . " " . $prior_attorney->zip, $somecontent, $arrReplace);
		pdfReplacement('ADDTELPRIANAME1', $prior_attorney->city . ", " . $prior_attorney->state . " " . $prior_attorney->zip . " " . $prior_attorney->phone, $somecontent, $arrReplace);
	} else {
		pdfReplacement('PRIASALUT1',' ', $somecontent, $arrReplace);
		pdfReplacement('PRIANAME1', ' ', $somecontent, $arrReplace);
		pdfReplacement('PRIAFIRM1', ' ', $somecontent, $arrReplace);
		pdfReplacement('PRIAADD11', ' ', $somecontent, $arrReplace);
		pdfReplacement('PRIAADD12', ' ', $somecontent, $arrReplace);
		pdfReplacement('PRIAADD21', ' ', $somecontent, $arrReplace);
		pdfReplacement('PRIACITY1', ' ', $somecontent, $arrReplace);
		pdfReplacement('PRIASTATE1', ' ', $somecontent, $arrReplace);
		pdfReplacement('PRIAZIP1', ' ', $somecontent, $arrReplace);
		pdfReplacement('PRIAPHONE1', ' ', $somecontent, $arrReplace);
		pdfReplacement('PRIAFAX1', ' ', $somecontent, $arrReplace);
		pdfReplacement('PRIACITYSTATEZIP1', ' ', $somecontent, $arrReplace);
		pdfReplacement('ADDTELPRIANAME1', ' ', $somecontent, $arrReplace);
	}

	//medical provider
	pdfReplacement('PROVSALUT1', $primary_salutation, $somecontent, $arrReplace);
	if (isset($primary->full_name)) {
		$provider_name = $primary->full_name;
		if ($provider_name=="") {
			$provider_name = $primary->company_name;
		}
		if ($provider_name=="") {
			$provider_name = " ";
		}
		pdfReplacement('PROVNAME1', $provider_name, $somecontent, $arrReplace);
		pdfReplacement('PROVFIRM1', $primary->company_name, $somecontent, $arrReplace);
		pdfReplacement('PROVADD11', $primary->street, $somecontent, $arrReplace);
		pdfReplacement('PROVADD21', $primary->suite, $somecontent, $arrReplace);
		pdfReplacement('PROVCITYSTATEZIP1', $primary->city . ", " . $primary->state . " " . $primary->zip, $somecontent, $arrReplace);
		pdfReplacement('PROVCITY1', $primary->city, $somecontent, $arrReplace);
		pdfReplacement('PROVSTATE1', $primary->state, $somecontent, $arrReplace);
		pdfReplacement('PROVZIP1', $primary->zip, $somecontent, $arrReplace);
		pdfReplacement('PROVTEL', $primary->phone, $somecontent, $arrReplace);
		$full_provider =  "";
		if ($primary->full_name !="") {
			$full_provider =  $primary->full_name . "\r\n";
		}
		$full_provider .=  $primary->company_name . "\r\n" . $primary->street;
		if ($primary->suite!="") {
			$full_provider .=  $primary->suite . "\r\n";
			$full_provider .=  ", ";
		}
		$full_provider .=  $primary->city . ", " . $primary->state . " " . $primary->zip. "\r\n" . $primary->phone;
		pdfReplacement('PROVIDER', $full_provider, $somecontent, $arrReplace);
		
		pdfReplacement('MEDICALREPORTDATE', $medical_report_date_completed, $somecontent, $arrReplace);
	} else {
		pdfReplacement('PROVNAME1', ' ', $somecontent, $arrReplace);
		pdfReplacement('PROVFIRM1', ' ', $somecontent, $arrReplace);
		pdfReplacement('PROVADD11', ' ', $somecontent, $arrReplace);
		pdfReplacement('PROVADD21', ' ', $somecontent, $arrReplace);
		pdfReplacement('PROVCITYSTATEZIP1', ' ', $somecontent, $arrReplace);
		pdfReplacement('PROVTEL', ' ', $somecontent, $arrReplace);
		pdfReplacement('PROVIDER', ' ', $somecontent, $arrReplace);
		pdfReplacement('MEDICALREPORTDATE', ' ', $somecontent, $arrReplace);
	}
	
	//lien hoder
	//medical provider
	pdfReplacement('LIENSALUT1', $lien_holder_salutation, $somecontent, $arrReplace);
	if (isset($lien_holder->full_name)) {
		pdfReplacement('LIENNAME1', $lien_holder->full_name, $somecontent, $arrReplace);
		$arrName = explode(" ", $lien_holder->full_name);
		$lien_holder_last_name = $arrName[count($arrName)-1];
		unset($arrName[count($arrName)-1]);
		$lien_holder_first_name = "";
		if (is_array($arrName)) {
			$lien_holder_first_name = implode(" ", $arrName);
		}
		if ($lien_holder_first_name=="") {
			$lien_holder_first_name = " ";
		}
		if ($lien_holder_last_name=="") {
			$lien_holder_last_name = " ";
		}
		pdfReplacement('LIENFIRSTNAME1', $lien_holder_first_name, $somecontent, $arrReplace);
		pdfReplacement('LIENLASTNAME1', $lien_holder_last_name, $somecontent, $arrReplace);
		pdfReplacement('LIENDATE', $lien_date, $somecontent, $arrReplace);
		pdfReplacement('LIENFIRM1', $lien_holder->company_name, $somecontent, $arrReplace);
		pdfReplacement('LIENADD11', $lien_holder->street, $somecontent, $arrReplace);
		pdfReplacement('LIENADD21', $lien_holder->suite, $somecontent, $arrReplace);
		pdfReplacement('LIENCITY1', $lien_holder->city, $somecontent, $arrReplace);
		pdfReplacement('LIENSTATE1', $lien_holder->state, $somecontent, $arrReplace);
		pdfReplacement('LIENZIP1', $lien_holder->zip, $somecontent, $arrReplace);
		pdfReplacement('LIENCITYSTATEZIP1', $lien_holder->city . ", " . $lien_holder->state . " " . $lien_holder->zip, $somecontent, $arrReplace);
		pdfReplacement('LIENTEL', $lien_holder->phone, $somecontent, $arrReplace);
	} else {
		pdfReplacement('LIENNAME1', ' ', $somecontent, $arrReplace);
		pdfReplacement('LIENFIRSTNAME1', ' ', $somecontent, $arrReplace);
		pdfReplacement('LIENLASTNAME1', ' ', $somecontent, $arrReplace);
		pdfReplacement('LIENDATE', ' ', $somecontent, $arrReplace);
		pdfReplacement('LIENFIRM1', ' ', $somecontent, $arrReplace);
		pdfReplacement('LIENADD11', ' ', $somecontent, $arrReplace);
		pdfReplacement('LIENADD21', ' ', $somecontent, $arrReplace);
		pdfReplacement('LIENCITY1', ' ', $somecontent, $arrReplace);
		pdfReplacement('LIENSTATE1', ' ', $somecontent, $arrReplace);
		pdfReplacement('LIENZIP1', ' ', $somecontent, $arrReplace);
		pdfReplacement('LIENCITYSTATEZIP1', ' ', $somecontent, $arrReplace);
		pdfReplacement('LIENTEL', ' ', $somecontent, $arrReplace);
	}
	//lien form
	pdfReplacement('ORIGINALAMEND', '1', $somecontent, $arrReplace);
	pdfReplacement('APPLICANTREPCHOICE', '0', $somecontent, $arrReplace);
	pdfReplacement('LIENOTHERTEXT', ' ', $somecontent, $arrReplace);
	pdfReplacement('LIENAMOUNT', ' ', $somecontent, $arrReplace);
	pdfReplacement('INTERPRETERMONTHDAY', ' ', $somecontent, $arrReplace);
	pdfReplacement('INTERPRETERYEAR', ' ', $somecontent, $arrReplace);
	//pdfReplacement('DOICHOICE', $ct_choice, $somecontent, $arrReplace);
	
	//die($somecontent);
	if ($doi_end_date!="") {
		pdfReplacement('DOICHOICE', '1', $somecontent, $arrReplace);
		pdfReplacement('CHECKINJURYTYPE', 'C', $somecontent, $arrReplace);
		pdfReplacement('DOISTARTDATE', ' ', $somecontent, $arrReplace);
		pdfReplacement('DOISTARTDATECT', $doi_start_date, $somecontent, $arrReplace);
		pdfReplacement('DOIENDDATE', $doi_end_date, $somecontent, $arrReplace);
		pdfReplacement('ACCIDATE', $doi_start_date . " - " . $doi_end_date . " CT", $somecontent, $arrReplace);
	} else {
		pdfReplacement('DOICHOICE', '0', $somecontent, $arrReplace);
		pdfReplacement('CHECKINJURYTYPE', 'S', $somecontent, $arrReplace);
		pdfReplacement('DOISTARTDATE', $doi_start_date, $somecontent, $arrReplace);
		pdfReplacement('DOISTARTDATECT', ' ', $somecontent, $arrReplace);
		pdfReplacement('DOIENDDATE', ' ', $somecontent, $arrReplace);
		pdfReplacement('ACCIDATE', $doi_start_date, $somecontent, $arrReplace);		
	}
	pdfReplacement('DOISTARTDATEABSOLUTE', $doi_start_date, $somecontent, $arrReplace);
	
	if ($injury->street=="" && is_object($employer)) {
		$injury->street = $employer->street;
		$injury->city = $employer->city;
		$injury->suite = $employer->suite;
		$injury->state = $employer->state;
		$injury->zip = $employer->zip;
	}
	if ($injury->street!="") {
		pdfReplacement('INJURYCITY', $injury->city, $somecontent, $arrReplace);
		pdfReplacement('INJURYSTATE', $injury->state, $somecontent, $arrReplace);
		pdfReplacement('INJURYZIP', $injury->zip, $somecontent, $arrReplace);
		
		$injury_full_address = $injury->street . " " . $injury->suite . ", " . $injury->city . "," . $injury->state . " " . $injury->zip;
		
		if ($form_name=="app_cover") {
			if ($injury->full_address != $injury->street . ", " . $injury->city . "," . $injury->state . " " . $injury->zip) {
				$injury_full_address = $injury->full_address . " " . $injury->suite;
			}
		}
		pdfReplacement('INJURYFULLADDRESS', $injury_full_address, $somecontent, $arrReplace);
		
		$injury_street = $injury->street;
		if ($injury->suite!="") {
			$injury_street .= " " . $injury->suite;
		}
		if ($form_name=="app_cover") {
			//maybe only use the suite number
			if (strpos($injury->full_address, $injury->street)===false) {
				$injury_street = $injury->suite;
			}
		}
		pdfReplacement('INJURYSTREET', $injury_street, $somecontent, $arrReplace);
	} else {
		pdfReplacement('INJURYSTREET', ' ', $somecontent, $arrReplace);
		pdfReplacement('INJURYCITY', ' ', $somecontent, $arrReplace);
		pdfReplacement('INJURYSTATE', ' ', $somecontent, $arrReplace);
		pdfReplacement('INJURYZIP', ' ', $somecontent, $arrReplace);
		pdfReplacement('INJURYFULLADDRESS', ' ', $somecontent, $arrReplace);
	}

	pdfReplacement('BODYPARTS0', $arrBodyParts[0], $somecontent, $arrReplace);
	pdfReplacement('BODYPARTS1', $arrBodyParts[1], $somecontent, $arrReplace);
	pdfReplacement('BODYPARTS2', $arrBodyParts[2], $somecontent, $arrReplace);
	pdfReplacement('BODYPARTS3', $arrBodyParts[3], $somecontent, $arrReplace);
	
	if (trim(implode("", $arrBodyParts))=="") {
		$additional_bodyparts = " ";
	} else {
		unset($arrBodyParts[0]);
		unset($arrBodyParts[1]);
		unset($arrBodyParts[2]);
		unset($arrBodyParts[3]);
		$additional_bodyparts = implode(", ", $arrBodyParts);
	}

	pdfReplacement('BODYPARTS4', $additional_bodyparts, $somecontent, $arrReplace);
	pdfReplacement('BODYPARTSALL', implode(", ", $arrBodyParts), $somecontent, $arrReplace);
	pdfReplacement('INJURYDESCRIPTION', $injury->explanation, $somecontent, $arrReplace);
	
	pdfReplacement('PAYRATE', ' ', $somecontent, $arrReplace);
	pdfReplacement('PAYRATERANGE', 'Off', $somecontent, $arrReplace);
	pdfReplacement('TIPSMEALSLODGING', ' ', $somecontent, $arrReplace);
	pdfReplacement('TIPRATERANGE', 'Off', $somecontent, $arrReplace);
	pdfReplacement('HOURSWORKED', ' ', $somecontent, $arrReplace);
	pdfReplacement('LASTDAYOFF', ' ', $somecontent, $arrReplace);
	pdfReplacement('FIRSTDISABILITYSTARTDATE', ' ', $somecontent, $arrReplace);
	pdfReplacement('FIRSTDISABILITYENDDATE', ' ', $somecontent, $arrReplace);
	pdfReplacement('SECONDDISABILITYSTARTDATE', ' ', $somecontent, $arrReplace);
	pdfReplacement('SECONDDISABILITYENDDATE', ' ', $somecontent, $arrReplace);
	pdfReplacement('COMPENSATIONPAID', 'Off', $somecontent, $arrReplace);
	pdfReplacement('TOTALPAID', ' ', $somecontent, $arrReplace);
	pdfReplacement('WEEKLYRATE', ' ', $somecontent, $arrReplace);
	pdfReplacement('LASTPAYMENT', ' ', $somecontent, $arrReplace);
	pdfReplacement('EDDBENEFITS', 'Off', $somecontent, $arrReplace);

	pdfReplacement('MEDICALTREATMENTRECEIVED', '1', $somecontent, $arrReplace);
	pdfReplacement('TREATMENTFURNISHED', '1', $somecontent, $arrReplace);
	pdfReplacement('LASTDATETREATMENT', ' ', $somecontent, $arrReplace);
	pdfReplacement('OTHERTREATMENTPROVIDEDBY', ' ', $somecontent, $arrReplace);
	pdfReplacement('MEDICALPAY', ' ', $somecontent, $arrReplace);
	pdfReplacement('DOCTORHOSPITAL1', ' ', $somecontent, $arrReplace);
	pdfReplacement('DOCTORHOSPITAL2', ' ', $somecontent, $arrReplace);
	pdfReplacement('OTHERCASE1', $arrRelatedCases[0]["adj_number"], $somecontent, $arrReplace);
	pdfReplacement('OTHERCASE2', $arrRelatedCases[1]["adj_number"], $somecontent, $arrReplace);
	pdfReplacement('OTHERCASE3', $arrRelatedCases[2]["adj_number"], $somecontent, $arrReplace);
	pdfReplacement('OTHERCASE4', $arrRelatedCases[3]["adj_number"], $somecontent, $arrReplace);
	pdfReplacement('OTHERCASE5', $arrRelatedCases[4]["adj_number"], $somecontent, $arrReplace);
	
	pdfReplacement('DOI1', $arrRelatedCases[0]["doi"], $somecontent, $arrReplace);
	pdfReplacement('DOI2', $arrRelatedCases[1]["doi"], $somecontent, $arrReplace);
	pdfReplacement('DOI3', $arrRelatedCases[2]["doi"], $somecontent, $arrReplace);
	pdfReplacement('DOI4', $arrRelatedCases[3]["doi"], $somecontent, $arrReplace);
	pdfReplacement('DOI5', $arrRelatedCases[4]["doi"], $somecontent, $arrReplace);

	pdfReplacement('TEMPORARYDISABILITY', '1', $somecontent, $arrReplace);
	pdfReplacement('PERMANENTDISABILITY', '1', $somecontent, $arrReplace);
	pdfReplacement('MEDICALEXPENSE', '1', $somecontent, $arrReplace);
	pdfReplacement('REHABILITATION', '1', $somecontent, $arrReplace);
	pdfReplacement('MEDICALTREATMENT', '1', $somecontent, $arrReplace);
	pdfReplacement('SJDB', '1', $somecontent, $arrReplace);
	pdfReplacement('COMPENSATIONRATE', '1', $somecontent, $arrReplace);
	pdfReplacement('OTHERFILEREASON', '1', $somecontent, $arrReplace);
	pdfReplacement('OTHERFILEREASONDESCRIPTION', 'ALL BENEFITS PER LC', $somecontent, $arrReplace);
	
	pdfReplacement('APPLICANTREPRESENTED', '1', $somecontent, $arrReplace);
	pdfReplacement('APPLICANTREP', '1', $somecontent, $arrReplace);
	
	pdfReplacement('APPLICANTREPFIRMNAME', $customer->cus_name, $somecontent, $arrReplace);
	pdfReplacement('APPLICANTREPFIRMNUMBER', $customer->eams_no, $somecontent, $arrReplace);
	pdfReplacement('APPLICANTREPFIRSTNAME', $customer->cus_name_first, $somecontent, $arrReplace);
	pdfReplacement('APPLICANTREPLASTNAME', $customer->cus_name_last, $somecontent, $arrReplace);
	pdfReplacement('APPLICANTREPMIDDLENAME', $customer->cus_name_middle, $somecontent, $arrReplace);

	pdfReplacement('APPLICANTREPADDRESS', $customer->cus_street, $somecontent, $arrReplace);
	pdfReplacement('APPLICANTREPCITY', $customer->cus_city, $somecontent, $arrReplace);
	pdfReplacement('APPLICANTREPSTATE', $customer->cus_state, $somecontent, $arrReplace);
	pdfReplacement('APPLICANTREPZIP', $customer->cus_zip, $somecontent, $arrReplace);
	pdfReplacement('CUSCOUNTY', $customer->cus_county, $somecontent, $arrReplace);
	pdfReplacement('APPLICANTREPTEL', $customer->cus_phone, $somecontent, $arrReplace);
	pdfReplacement('FIRMFAX', $customer->cus_fax, $somecontent, $arrReplace);
	pdfReplacement('DOCUMENTCITY', $customer->cus_city, $somecontent, $arrReplace);
	
	pdfReplacement('FIRMNAME', $_SESSION['user_customer_name'], $somecontent, $arrReplace);
	pdfReplacement('FIRMNUMBER', $customer->eams_no, $somecontent, $arrReplace);
	
	if ($blnApp && $attorney_first_name!="") {
		pdfReplacement('FIRMFIRSTNAME', $attorney_first_name, $somecontent, $arrReplace);
		pdfReplacement('FIRMLASTNAME', $attorney_last_name, $somecontent, $arrReplace);
		pdfReplacement('FIRMMIDDLENAME', $attorney_middle_name, $somecontent, $arrReplace);
	} else {
		pdfReplacement('FIRMFIRSTNAME', $customer->cus_name_first, $somecontent, $arrReplace);
		pdfReplacement('FIRMLASTNAME', $customer->cus_name_last, $somecontent, $arrReplace);
		pdfReplacement('FIRMMIDDLENAME', $customer->cus_name_middle, $somecontent, $arrReplace);
	}
	
	pdfReplacement('ASSIGNATTYFIRSTNAME', $attorney_first_name, $somecontent, $arrReplace);
	pdfReplacement('ASSIGNATTYMIDDLENAME', $attorney_middle_name, $somecontent, $arrReplace);
	pdfReplacement('ASSIGNATTYLASTNAME', $attorney_last_name, $somecontent, $arrReplace);
	
	pdfReplacement('ATTORNEYEMAIL', $kase->attorney_email, $somecontent, $arrReplace);
	pdfReplacement('FIRMADDRESS', $customer->cus_street, $somecontent, $arrReplace);
	pdfReplacement('FIRMCITY', $customer->cus_city, $somecontent, $arrReplace);
	pdfReplacement('FIRMSTATE', $customer->cus_state, $somecontent, $arrReplace);
	pdfReplacement('FIRMADD1', $customer->cus_street, $somecontent, $arrReplace);
	pdfReplacement('FIRMADD2', "", $somecontent, $arrReplace);
	pdfReplacement('FIRMZIP', $customer->cus_zip, $somecontent, $arrReplace);
	pdfReplacement('FIRMTEL', $customer->cus_phone, $somecontent, $arrReplace);
	pdfReplacement('ADDTELFIRMNAME', $customer->cus_street . ", " . $customer->cus_city . ", " . $customer->cus_state . " " . $customer->cus_zip . ", " . $customer->cus_phone, $somecontent, $arrReplace);
	pdfReplacement('FIRMFULLADDRESS', $customer->cus_street . ", " . $customer->cus_city . ", " . $customer->cus_state . " " . $customer->cus_zip, $somecontent, $arrReplace);

	pdfReplacement('DOCUMENTCITY', $customer->cus_city, $somecontent, $arrReplace);
	
	//parties
	$arrPartiesLeft = array();
	$arrPartiesRight = array();
	
	//get the parties from the list
	foreach($arrPDFParties as $pdf_partie){
		if (!in_array($pdf_partie, $arrParties)) {
			$arrParties[] = $pdf_partie;
		}
	}
	
	ksort($arrParties);
	foreach($arrParties as $partie_index=>$partie) {
		if (($partie_index%2)==0) {
			$arrPartiesLeft[] = $partie;
		} else {
			$arrPartiesRight[] = $partie;
		}
	}
	
	pdfReplacement('PARTIES1', implode("\r\n\r\n", $arrPartiesLeft), $somecontent, $arrReplace);
	pdfReplacement('PARTIES2', implode("\r\n\r\n", $arrPartiesRight), $somecontent, $arrReplace);
	
	if ($customer->eams_no!="") {
		$customer_eams = getEamsRepByNumber($customer->eams_no);
	}
	//author
	pdfReplacement('AUTHOR', $customer->cus_name_first . " " . $customer->cus_name_last, $somecontent, $arrReplace);
	
	if (isset($customer_eams)) {
		//die(print_r($customer_eams));
		pdfReplacement('EAMSNAME', $customer_eams->firm_name, $somecontent, $arrReplace); 
		pdfReplacement('EAMSSTREET1', $customer_eams->street_1, $somecontent, $arrReplace); 
		pdfReplacement('EAMSSTREET2', $customer_eams->street_2, $somecontent, $arrReplace); 
		pdfReplacement('EAMSCITY', $customer_eams->city, $somecontent, $arrReplace); 
		pdfReplacement('EAMSSTATE', $customer_eams->state, $somecontent, $arrReplace); 
		pdfReplacement('EAMSZIP', $customer_eams->zip_code, $somecontent, $arrReplace); 
		pdfReplacement('EAMSPHONE', $customer_eams->phone, $somecontent, $arrReplace); 
	} else {
		pdfReplacement('EAMSNAME', $customer->cus_name, $somecontent, $arrReplace); 
	}
	if (isset($_POST["appointment_date"])) {
		$appointment_date = passed_var("appointment_date", "post");
		if ($appointment_date!="") {
			$appointment_time = passed_var("appointment_time", "post");
			$appointment_date .= " " . $appointment_time;
			
			pdfReplacement('APPTDATE',  date("m/d/Y", strtotime($appointment_date)), $somecontent, $arrReplace);
			pdfReplacement('APPTTIME',  date("h:iA", strtotime($appointment_date)), $somecontent, $arrReplace);
		}
	}
	pdfReplacement('EARNINGS', $work_history_rate, $somecontent, $arrReplace);
	pdfReplacement('EARNINGS_INTERVAL', $work_history_rateinterval, $somecontent, $arrReplace);
	pdfReplacement('DISABILITY_PERCENT', $disability_percent_total, $somecontent, $arrReplace);
	pdfReplacement('PRIOR_PERMANENT', $prior_permanent_disabled, $somecontent, $arrReplace);
	pdfReplacement('PRE_EXISTING', $preexisting_disability, $somecontent, $arrReplace);
	pdfReplacement('PREVIOUS_CLAIM_NUMBER', $previous_claim_number, $somecontent, $arrReplace);
	pdfReplacement('SSD_BENEFITS', $SSD_benefits, $somecontent, $arrReplace);
	pdfReplacement('SSD_PAYMENTS', $SSD_payments, $somecontent, $arrReplace);
	
	pdfReplacement('SIGNATURE', $_SESSION['user_name'], $somecontent, $arrReplace);
	pdfReplacement('SIGNATURE', $_SESSION['user_name'], $somecontent, $arrReplace);
	
	//the date
	pdfReplacement("DATE", date("m/d/Y"), $somecontent, $arrReplace);
	
	//output
	$host = $_SERVER['HTTP_HOST'];
	pdfReplacement("DESTINATION", "http://" . $host . "/eams_forms/", $somecontent, $arrReplace);
	
	//output
	$destination_folder = "D:/uploads/" . $_SESSION['user_customer_id'] . "/" . $case_id . "/eams_forms/";
	if (!is_dir($destination_folder)) {
		mkdir($destination_folder, 0755, true);
	}
	$form_file_name = $form_name;
	//$form_file_name = str_replace("(", "{", $form_file_name);
	//$form_file_name = str_replace(")", "}", $form_file_name);
	
	$filename = $destination_folder . $form_file_name . ".fdf";
	$filename_output =  $destination_folder . $form_file_name . ".pdf";
	
	$pdftk_output =  $_SERVER['DOCUMENT_ROOT'] . "\\uploads\\" . $_SESSION['user_customer_id'] . "\\" . $case_id . "\\eams_forms\\" . $form_file_name . ".pdf";
	$file_counter = 1;
	$output_store_name = $form_file_name . ".pdf";
	while (file_exists($pdftk_output)) {
		$pdftk_output =  $_SERVER['DOCUMENT_ROOT'] . "\\uploads\\" . $_SESSION['user_customer_id'] . "\\" . $case_id . "\\eams_forms\\" . $form_file_name . "_" . $file_counter . ".pdf";
		$output_store_name = $form_file_name . "_" . $file_counter . ".pdf";
		$file_counter++;
	}
	pdfReplacement("DOCUMENTPATH", $output_store_name, $somecontent, $arrReplace);
	
	if (file_exists($filename)) {
		unlink($filename);
	}
	if (!$handle = fopen($filename, 'w')) {
		 echo "Cannot open file ($filename)";
		 exit;
	}
	
	// Write $somecontent to our opened file.
	if (fwrite($handle, $somecontent) === FALSE) {
	   echo "Cannot write to file ($filename)";
	   exit;
	}
	$filename = $_SERVER['DOCUMENT_ROOT'] . "\\uploads\\" . $_SESSION['user_customer_id'] . "\\" . $case_id . "\\eams_forms\\" . $form_file_name . ".fdf";
	$source_dir = $_SERVER['DOCUMENT_ROOT'] . '\\eams_forms\\';
		
	if ($nopublish=="y") {
		if ($case_id==1) {
			//die("pdftk " . $source_dir . $form_name . ".pdf fill_form " . $filename. " output " . $pdftk_output);
		}
		exec("pdftk \"" . $source_dir . $form_name . ".pdf\" fill_form \"" . $filename. "\" output \"" . $pdftk_output . "\"");
		if ($_SERVER['REMOTE_ADDR']=='47.153.51.181') {
			//echo "nopub:\r\n\r\n";
			//echo "pdftk " . $source_dir . $form_name . ".pdf fill_form " . $filename. " output " . $pdftk_output . "\r\n\r\n";
			//sleep(5);
		}
		if ($form_name=="adjpacket") {
			//sleep(10);
		}
	} else {
		/*
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
		header('Content-type: application/vnd.fdf');
		
		// It will be called downloaded.pdf
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		
		// The PDF source is in original.pdf
		readfile($filename);
		*/
		$filename = str_replace("../", "", $filename);
		$filename_output = "http://" . $host . "/" . $filename;
	}

	if ($form_name!="app_cover" && !$blnInternalRequest) {
		//create thumbnail
		$upload_dir = $_SERVER['DOCUMENT_ROOT'] . "\\uploads\\" . $_SESSION['user_customer_id'];
		if (!is_dir($upload_dir)) {
			mkdir($upload_dir);
		}
		$upload_dir .= "\\" . $case_id;
		if (!is_dir($upload_dir)) {
			mkdir($upload_dir);
		}
		
		$thumbnail_path = $upload_dir . "\\thumbnail\\" . str_replace(".pdf", ".jpg", $output_store_name);
		$file_path = $upload_dir . "\\eams_forms\\" . $output_store_name;			
		if (!is_dir($upload_dir . "\\thumbnail")) {
			mkdir($upload_dir . "\\thumbnail");
		}
		
		//create a thumbnail
		$image_magick = new imagick(); 
		//die("magick:" . $thumbnail_path );
	
		$image_magick->setbackgroundcolor('white');
		$image_magick->readImage($file_path . "[0]");
		
		$image_magick = $image_magick->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
		$image_magick->setResolution(300,300);
		$image_magick->thumbnailImage(102, 102, true);
		$image_magick->setImageFormat('jpg');
		$image_magick->writeImage($thumbnail_path);
		
		if (!is_dir($upload_dir . "\\medium")) {
			mkdir($upload_dir . "\\medium");
		}
		
		$image_magick = new imagick(); 
		$image_magick->readImage($file_path . "[0]");
		$image_magick = $image_magick->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
		$image_magick->setResolution(300,300);
		$image_magick->thumbnailImage(800, 800, true);
		$image_magick->setImageFormat('jpg');
		$thumbnail_path = $upload_dir . "\\medium\\" . str_replace(".pdf", ".jpg", $output_store_name);
		$image_magick->writeImage($thumbnail_path);
		
		try {
			$db = getConnection();
			
			//get the form id
			$sql = "SELECT eams_form_id 
			FROM cse_eams_forms
			WHERE `name` = '" . $form_name . "'";
			//echo $sql . "\r\n";
			$stmt = $db->prepare($sql);  
			//$stmt->bindParam("form_name", $form_name);
			$stmt->execute();
			$eams_form = $stmt->fetchObject();
			$stmt->closeCursor(); $stmt = null; $db = null;
			
			$db = getConnection();
			$sql = "INSERT INTO cse_document (document_uuid, parent_document_uuid, document_name, document_date, document_filename, document_extension, description, description_html, thumbnail_folder, type, verified, customer_id) 
				VALUES (:document_uuid, :parent_document_uuid, :document_name, :document_date, :document_filename, :document_extension, :description, :description_html, :thumbnail_folder, :type, :verified, :customer_id)";
			$document_uuid = uniqid("KS");
			$document_filename = $output_store_name;
			$document_date = date("Y-m-d H:i:s");
			$document_extension = "pdf";
			$description = "";
			$subject = $form_name;
			$description_html = "";
			$thumbnail_folder = $case_id ."/medium";
			$type = "eams_form";
			$verified = "Y";
			$stmt = $db->prepare($sql);  
			$stmt->bindParam("document_uuid", $document_uuid);
			$stmt->bindParam("parent_document_uuid", $eams_form->eams_form_id);
			$stmt->bindParam("document_name", $subject);
			$stmt->bindParam("document_date", $document_date);
			$stmt->bindParam("document_filename", $document_filename);
			$stmt->bindParam("document_extension", $document_extension);
			$stmt->bindParam("description", $description);
			$stmt->bindParam("description_html", $description_html);
			$stmt->bindParam("thumbnail_folder", $thumbnail_folder);
			$stmt->bindParam("type", $type);
			$stmt->bindParam("verified", $verified);
			$stmt->bindParam("customer_id", $customer_id);
			
			$stmt->execute();
			$new_id = $db->lastInsertId();
			
			//attach to case
			$case_uuid = $kase->uuid;
			$case_id = $kase->id;
			$cd_uuid = uniqid("JK");
			$attribute = "eams_form";
			$sql = "INSERT INTO `cse_case_document`
			( `case_document_uuid`, `case_uuid`, `document_uuid`, `attribute_1`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $cd_uuid . "','" . $case_uuid . "','" . $document_uuid . "', '" . $attribute . "', '" . date("Y-m-d H:i:s") . "','" . $user_id . "', '" . $customer_id . "')";
			//echo $sql . "\r\n";
			
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			
			$stmt = null; $db = null;
			trackDocument("insert", $new_id);
			
			$db = null;
		} catch(PDOException $e) {	
			die( '{"error_document":{"text":'. $e->getMessage() .'}}'); 
		}
	}
	if ($form_name!="app_cover" && !$blnInternalRequest) {
		//activity
		$activity = "PDF Form [" . $form_name . "] generated by " . $_SESSION['user_name'];
		$activity .= "<br />";
		$activity .= "<a href='" . $destination_folder . $output_store_name . "' target='_blank' class='white_text'>review " . $form_name . "</a>";
		$activity_id = recordActivity("create", $activity, $kase->uuid, 0, "Forms");
		
		echo json_encode(array("file"=>$destination_folder . $output_store_name, "activity_id"=>$activity_id));
	}
	if ($blnInternalRequest) {
		return array("file"=>$destination_folder . $output_store_name);
		die();
	}
	//packets
	if ($form_name=="app_cover") {
		//generate separators for each doc
		//get the docs
		//get uploads
		/*
		$sql = "SELECT `document_id` id, `description` `name`, `document_filename` `filepath`
		FROM cse_document doc
		INNER JOIN cse_case_document ccd
		ON doc.document_uuid = ccd.document_uuid
		INNER JOIN cse_case ccase
		ON ccd.case_uuid = ccase.case_uuid
		WHERE `type` = 'App_for_ADJ' 
		AND `document_filename` != ''
		AND case_id = :case_id
		AND `doc`.customer_id = :cus_id
		AND `doc`.deleted = 'N'";
		*/
		$sql = "SELECT `document_id` id, `description` `name`, `document_filename` `filepath`
		FROM cse_document doc
		INNER JOIN cse_injury_document ccd
		ON doc.document_uuid = ccd.document_uuid
		INNER JOIN cse_injury cinjury
		ON ccd.injury_uuid = cinjury.injury_uuid
		WHERE doc.`type` = 'App_for_ADJ' 
		AND `document_filename` != ''
		AND injury_id = :injury_id
		AND `doc`.customer_id = :cus_id
		AND `doc`.deleted = 'N'";
		//die($sql);
		try {
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("injury_id", $doi_id);
			$stmt->bindParam("cus_id", $_SESSION["user_customer_id"]);
			$stmt->execute();
			$documents = $stmt->fetchAll(PDO::FETCH_OBJ);
			$stmt->closeCursor(); $stmt = null; $db = null;
		} catch(PDOException $e) {
			$error = array("error"=> array("text"=>$e->getMessage()));
			echo json_encode($error);
		}
		//die(print_r($documents));
		$arrFiles = array($_SERVER['DOCUMENT_ROOT'] . "\\uploads\\" . $_SESSION['user_customer_id'] . "\\" . $case_id . "\\eams_forms\\" . $output_store_name);
		$separator_path = $_SERVER['DOCUMENT_ROOT'] . "\\uploads\\" . $_SESSION['user_customer_id'] . "\\" . $case_id . "\\eams_forms\\";
		$separator_find = "D:/uploads/" . $_SESSION["user_customer_id"] . "/" . $case_id . "/eams_forms/";
		
		$doc_path = $_SERVER['DOCUMENT_ROOT'] . "\\uploads\\" . $_SESSION['user_customer_id'] . "\\" . $case_id . "\\jetfiler\\";
		$doc_path_2 = $_SERVER['DOCUMENT_ROOT'] . "\\uploads\\" . $_SESSION['user_customer_id'] . "\\" . $case_id . "\\";
		
		//in case of duplicates
		$arrFileNames = array();
		$document_count = count($documents);
		foreach($documents as $doc_index=>$doc) {
			if (!in_array($doc->name, $arrFileNames)) {
				//Solulab code change start 26-09-2019
				if($doc->name != "" || $doc->name != NULL){
					$arrFileNames[] = $doc->name;
				}
				//Solulab code change end 26-09-2019
			} else {
				$document_count--;
				continue;
			}
			$blnLastOne = ($doc_index == $document_count - 1);
			$final_output = "";
			if ($blnLastOne) {
				$final_output = "";
			}
			$separator = generatePDF("separator", $doc->name);
			if ($_SERVER['REMOTE_ADDR']=='47.153.51.181') {
				//echo "sep\r\n";
				//sleep(5);
			}
			$arrFiles[] = str_replace($separator_find, $separator_path, $separator["file"]);
			$actual_path = $doc_path . $doc->filepath;
			//echo "considering " . $actual_path . "\r\n\r\n";
			if (!file_exists($actual_path)) {
				$actual_path = $doc_path_2 . $doc->filepath;
				//echo "ending with " . $actual_path . "\r\n\r\n";
			} 
			$arrFiles[] = $actual_path;
			//$_SERVER['DOCUMENT_ROOT'] . "\\uploads\\" . $_SESSION['user_customer_id'] . "\\" . $case_id . "\
		}
		//die(print_r($arrFiles));
		//put together the pdftk string
		$filename_output = $_SERVER['DOCUMENT_ROOT'] . "\\uploads\\" . $_SESSION['user_customer_id'] . "\\" . $case_id . "\jetfiler\\app_cover.pdf";
		
		$file_counter = 1;
		$output_store_name = $form_name . "_final.pdf";
		$pdftk_output =  $_SERVER['DOCUMENT_ROOT'] . "\\uploads\\" . $_SESSION['user_customer_id'] . "\\" . $case_id . "\\eams_forms\\" . $output_store_name;
		
		$upload_dir = $_SERVER['DOCUMENT_ROOT'] . "\\uploads\\" . $_SESSION['user_customer_id'] . "\\" . $case_id;
		
		
		if (!is_dir($upload_dir . "\\thumbnail\\")) {
			mkdir($upload_dir . "\\thumbnail\\");
		}
		/*
		$upload_dir .= "\\" . $case_id;
		if (!is_dir($upload_dir)) {
			mkdir($upload_dir);
		}
		*/
		$thumbnail_path = $upload_dir . "\\thumbnail\\" . str_replace(".pdf", ".jpg", $output_store_name);
		$file_path = $upload_dir . "\\eams_forms\\" . $output_store_name;			
		
		while (file_exists($pdftk_output)) {
			$pdftk_output =  $_SERVER['DOCUMENT_ROOT'] . "\\uploads\\" . $_SESSION['user_customer_id'] . "\\" . $case_id . "\\eams_forms\\" . $form_name . "_" . $file_counter . "_final.pdf";
			$output_store_name = $form_name . "_" . $file_counter . "_final.pdf";
			
			$thumbnail_path = $upload_dir . "\\thumbnail\\" . str_replace(".pdf", ".jpg", $output_store_name);
			
			$file_counter++;
		}
		
		$pdftk = 'pdftk "' . implode('" "', $arrFiles) . '" cat output ' . $pdftk_output;
		if ($_SERVER['REMOTE_ADDR']=='47.153.51.181') {
			//echo "app:\r\n\r\n";
			//echo $pdftk . "\r\n\r\n";
		}
		//pdftk final stack
		passthru($pdftk);
		if ($_SERVER['REMOTE_ADDR']=='47.153.51.181') {
			//sleep(5);
		}
		//, "pdftk"=>$pdftk
		$destination_folder = "D:/uploads/" . $_SESSION['user_customer_id'] . "/" . $case_id  . "/eams_forms/";
		echo json_encode(array("file"=>$destination_folder . $output_store_name, "activity_id"=>""));
		
		try {
			//thumbnail
			//create a thumbnail
			$image_magick = new imagick(); 
			//die("magick:" . $thumbnail_path );
		
			$image_magick->setbackgroundcolor('white');
			$image_magick->readImage($file_path . "[0]");
			
			$image_magick = $image_magick->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
			$image_magick->setResolution(300,300);
			$image_magick->thumbnailImage(102, 102, true);
			$image_magick->setImageFormat('jpg');
			$image_magick->writeImage($thumbnail_path);
			
			if (!is_dir($upload_dir . "\\medium")) {
				mkdir($upload_dir . "\\medium");
			}
			
			$image_magick = new imagick(); 
			$image_magick->readImage($file_path . "[0]");
			$image_magick = $image_magick->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
			$image_magick->setResolution(300,300);
			$image_magick->thumbnailImage(800, 800, true);
			$image_magick->setImageFormat('jpg');
			$thumbnail_path = $upload_dir . "\\medium\\" . str_replace(".pdf", ".jpg", $output_store_name);
			$image_magick->writeImage($thumbnail_path);
			
			$db = getConnection();
			
			//get the form id
			$sql = "SELECT eams_form_id 
			FROM cse_eams_forms
			WHERE `name` = 'adjpacket'
			AND deleted = 'N'";
			//echo $sql . "\r\n";
			$stmt = $db->prepare($sql);  
			//$stmt->bindParam("form_name", $form_name);
			$stmt->execute();
			$eams_form = $stmt->fetchObject();
			$stmt->closeCursor(); $stmt = null; $db = null;
			
			$db = getConnection();
			$sql = "INSERT INTO cse_document (document_uuid, parent_document_uuid, document_name, document_date, document_filename, document_extension, description, description_html, thumbnail_folder, type, verified, customer_id) 
				VALUES (:document_uuid, :parent_document_uuid, :document_name, :document_date, :document_filename, :document_extension, :description, :description_html, :thumbnail_folder, :type, :verified, :customer_id)";
			$document_uuid = uniqid("KS");
			$document_filename = $output_store_name;
			$document_date = date("Y-m-d H:i:s");
			$document_extension = "pdf";
			$description = "";
			$subject = $form_name;
			$description_html = "";
			$thumbnail_folder = $case_id ."/medium";
			$type = "eams_form";
			$verified = "Y";
			$stmt = $db->prepare($sql);  
			$stmt->bindParam("document_uuid", $document_uuid);
			$stmt->bindParam("parent_document_uuid", $eams_form->eams_form_id);
			$stmt->bindParam("document_name", $subject);
			$stmt->bindParam("document_date", $document_date);
			$stmt->bindParam("document_filename", $document_filename);
			$stmt->bindParam("document_extension", $document_extension);
			$stmt->bindParam("description", $description);
			$stmt->bindParam("description_html", $description_html);
			$stmt->bindParam("thumbnail_folder", $thumbnail_folder);
			$stmt->bindParam("type", $type);
			$stmt->bindParam("verified", $verified);
			$stmt->bindParam("customer_id", $customer_id);
			
			$stmt->execute();
			$new_id = $db->lastInsertId();
			
			//attach to case
			$case_uuid = $kase->uuid;
			$case_id = $kase->id;
			$cd_uuid = uniqid("JK");
			$attribute = "eams_form";
			$sql = "INSERT INTO `cse_case_document`
			( `case_document_uuid`, `case_uuid`, `document_uuid`, `attribute_1`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $cd_uuid . "','" . $case_uuid . "','" . $document_uuid . "', '" . $attribute . "', '" . date("Y-m-d H:i:s") . "','" . $user_id . "', '" . $customer_id . "')";
			//echo $sql . "\r\n";
			
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			
			$stmt = null; $db = null;
			trackDocument("insert", $new_id);
			
			$db = null;
		} catch(PDOException $e) {	
			die( '{"error_document":{"text":'. $e->getMessage() .'}}'); 
		}
	}
}
?>
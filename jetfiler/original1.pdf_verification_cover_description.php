<?php
//Set no caching
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
header("Cache-Control: no-store, no-cache, must-revalidate"); 
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

header("strict-transport-security: max-age=600");
header('X-Frame-Options: SAMEORIGIN');
header("X-XSS-Protection: 1; mode=block");

error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
ini_set('display_errors', '1');

include("../api/manage_session.php");
include("../api/connection.php");

if($_SERVER["HTTPS"]=="off") {
	
	die("no go");
}

if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
	die("no go");
}

session_write_close();

$case_id = passed_var("case_id", "post");
$cus_id = passed_var("cus_id", "post");
$nopublish = passed_var("nopublish", "post");
$type = passed_var("type", "post");
$verification_description = passed_var("verification_description", "post");
//die("nopub:" . $nopublish);

$form = "verification";

//based on type, different defaults
if ($verification_description=="") {
	switch($type) {
		case "_app":
			$verification_description = "CASE IN CHIEF RESOLVED VIA APPLICATION FOR ADJ";
			break;
		case "_dor":
			$verification_description = "CASE IN CHIEF RESOLVED VIA DOR";
			break;
		case "_dore":
			$verification_description = "CASE IN CHIEF RESOLVED VIA DORE";
			break;
		case "_lien":
			$verification_description = "CASE IN CHIEF RESOLVED VIA LIEN";
			break;
	}
	//add the date
	$verification_description .= " " . date("m/d/Y");
}

include("jetfile_kase.php");

$page1 = "";
$page2 = "";
if ($kase->jetfile_info!="") {
	$jetfile_info = json_decode($kase->jetfile_info);
	$jetfile_id = $kase->jetfile_id;
	if (is_object($jetfile_info)) {
		if (is_object($jetfile_info->page1)) {
			$page1 = $jetfile_info->page1;
		}
		if (is_object($jetfile_info->page2)) {
			$page2 = $jetfile_info->page2;
		}
	}
}

$eams_no = $customer->eams_no;
$cus_name = $customer->cus_name;
$cus_name_first = $customer->cus_name_first;
$cus_name_middle = $customer->cus_name_middle;
$cus_name_last = $customer->cus_name_last;

$cus_signature = trim($cus_name_first);
if ($cus_name_middle!="") {
	$cus_signature .= " " . trim($cus_name_middle);
}
if ($cus_name_last!="") {
	$cus_signature .= " " . trim($cus_name_last);
}
//however, if we're logged in as user
if ($user_name!="") {
	$cus_signature = $user_name;
}

//die("cus_signature:" . $cus_signature);
$cus_street = $customer->cus_street;
$cus_city = $customer->cus_city;
$cus_state = $customer->cus_state;
$cus_zip = $customer->cus_zip;

//look up the county
$sql = "SELECT `county` 
FROM ikase.zip_code WHERE zip_code = :zip_code";

try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("zip_code", $cus_zip);
	
	$stmt->execute();
	$zip = $stmt->fetchObject();
	$stmt->closeCursor(); $stmt = null; $db = null;
	
	$county = $zip->county;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}

//form 1 info
$letter_office_code = $kase->venue_abbr;
$attorney_id = $kase->attorney_id;

$person_id = $kase->applicant_id;
$first = $kase->first_name;
$middle = $kase->middle_name;
$last = $kase->last_name;
$social_sec = $kase->ssn;
$birth_date = $kase->dob;
$adj_number = $kase->adj_number;

if ($kase->applicant_street=="" && $kase->applicant_full_address!="") {
	$arrApplicantAddress = explode(",", $kase->applicant_full_address);
	$arrStateZip = explode(" ", trim($arrApplicantAddress[count($arrApplicantAddress) - 1]));
	//die(print_r($arrStateZip));
	$kase->applicant_state = $arrStateZip[0];
	if (count($arrStateZip) == 2) {
		$kase->applicant_zip = $arrStateZip[1];
	}
	unset($arrApplicantAddress[count($arrApplicantAddress) - 1]);
	$kase->applicant_city = trim($arrApplicantAddress[count($arrApplicantAddress) - 1]);
	unset($arrApplicantAddress[count($arrApplicantAddress) - 1]);
	$kase->applicant_street = trim(implode(",", $arrApplicantAddress));
}
$address1 = $kase->applicant_street;
$city = $kase->applicant_city;
$state = $kase->applicant_state;
$zip = $kase->applicant_zip;

$case_number = $kase->case_number;
if ($case_number == "") {
	$case_number = $kase->file_number;
}
$address1 = $kase->applicant_street;
$client_city = $kase->applicant_city;
$client_state = $kase->applicant_state;
$client_zip = $kase->applicant_zip;

$arrCaseNumbers = array();
for($i=1; $i < 5; $i++) {
	$other_case_number = $page2->{"case_number_" . $i};
	$arrCaseNumbers[] = $other_case_number;
}

$assigned_first_name = $kase->attorney_first_name;
$assigned_last_name = $kase->attorney_last_name;

//get employer
$emp_name = $kase->employer;
$emp_street = $kase->employer_street;
$emp_city = $kase->employer_city;
$emp_state = $kase->employer_state;
$emp_zip = $kase->employer_zip;

$sql = "SELECT parent_corporation_uuid carrier_eams_number, corp.company_name carrier, corp.street carrier_street, corp.suite carrier_suite,
corp.city carrier_city, corp.state carrier_state, corp.zip carrier_zip, corp.full_address carrier_address
FROM cse_corporation corp
INNER JOIN cse_case_corporation ccorp
ON corp.corporation_uuid = ccorp.corporation_uuid AND ccorp.deleted = 'N'
INNER JOIN cse_case ccase
ON ccorp.case_uuid = ccase.case_uuid
WHERE `type` = 'carrier'
AND corp.deleted = 'N'
AND ccase.case_id = :case_id";

try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("case_id", $case_id);
	
	$stmt->execute();
	$carriers = $stmt->fetchAll(PDO::FETCH_OBJ);
	$stmt->closeCursor(); $stmt = null; $db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}

if (count($carriers) > 0) {
	foreach( $carriers as $carrier) {
		$car_name = $carrier->carrier;
		$car_street = $carrier->carrier_street;
		$car_city = $carrier->carrier_city;
		$car_state = $carrier->carrier_state;
		$car_zip = $carrier->carrier_zip;
		$car_eams_number = $carrier->carrier_eams_number;
		
		//only one
		break;
	}
}

$admin_name = $page1->admin_name;
$admin_street = $page1->admin_street;
$admin_city = $page1->admin_city;
$admin_state = $page1->admin_state;
$admin_zip = $page1->admin_zip;
$admin_eams_number = $page1->admin_eams_number;

$arrParticipants = array();
$arrParticipants[] = $first . " " . $middle . " " . $last . "\r\n" . $address1 . "\r\n" . $client_city . ", " . $client_state . " " . $client_zip;

//first we need the wcab court info
$sql = "SELECT `venue`, `address1`, `address2`, `city`, `zip`, `presiding` 
FROM `ikase`.`cse_venue`
WHERE `venue_abbr` = :letter_office_code";
try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("letter_office_code", $letter_office_code);
	
	$stmt->execute();
	$venue = $stmt->fetchObject();
	$stmt->closeCursor(); $stmt = null; $db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}

$participant_name = $venue->venue;
$presiding = $venue->presiding;
$participant_name .= "\r\n" . $presiding . ", Presiding Judge";

$address1 = $venue->address1;
$address2 = $venue->address2;
if ($address2!="") {
	$address1 .= "\r\n" .  $address2;
}
$participant_street = $address1;
$participant_city = $venue->city;
$participant_state = "CA";
$participant_zip = $venue->zip;

$arrParticipants[] = $participant_name . "\r\n" . $participant_street . "\r\n" . $participant_city . ", " . $participant_state . " " . $participant_zip;

//now get the participants
$sql = "SELECT parent_corporation_uuid carrier_eams_number, corp.`type`, corp.company_name, corp.street, corp.suite,
corp.city, corp.state, corp.zip, corp.full_address
FROM cse_corporation corp
INNER JOIN cse_case_corporation ccorp
ON corp.corporation_uuid = ccorp.corporation_uuid AND ccorp.deleted = 'N'
INNER JOIN cse_case ccase
ON ccorp.case_uuid = ccase.case_uuid
WHERE 1
AND corp.deleted = 'N'
AND ccase.case_id = :case_id";

try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("case_id", $case_id);
	
	$stmt->execute();
	$participants = $stmt->fetchAll(PDO::FETCH_OBJ);
	$stmt->closeCursor(); $stmt = null; $db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}

foreach($participants as $participant) {
	$participant_role = $participant->type;
	$participant_name = $participant->company_name;
	$participant_street = $participant->street;
	$participant_city = $participant->city;
	$participant_state = $participant->state;
	$participant_zip = $participant->zip;
	
	$arrParticipants[] = $participant_name . "\r\n" . $participant_street . "\r\n" . $participant_city . ", " . $participant_state . " " . $participant_zip;
}

if(count($participants)==0){
	//get the participants from form2
	$arrParticipants[] = $cus_name . "\r\n" . $cus_street . "\r\n" . $cus_city . ", " . $cus_state . " " . $cus_zip;
	$arrParticipants[] = $emp_name . "\r\n" . $emp_street . "\r\n" . $emp_city . ", " . $emp_state . " " . $emp_zip;
	
	$admin_info = $admin_name . "\r\n" . $admin_street . "\r\n" . $admin_city . ", " . $admin_state . " " . $admin_zip;
	$carr_info = $car_name . "\r\n" . $car_street . "\r\n" . $car_city . ", " . $car_state . " " . $car_zip;
	if ($car_name!="") {
		$arrParticipants[] = $carr_info;
	}
	if ($admin_name!="" && $admin_info!=$carr_info) {
		$arrParticipants[] = $admin_info;
	}
}
//first colum for participants
$count = count($arrParticipants);
$arrParticipant1 = array();
$arrParticipant2 = array();
if ($count>4) {
	$first_max = round(($count/2), 0) - 1;
	$second_min = $count - $first_max - 1;
	//die($count . " - " . $first_max . " - " . $second_min);
	
	for($int=0;$int<$first_max;$int++) {
		$arrParticipant1[] = $arrParticipants[$int];
	}
	for($int=$second_min;$int<$count;$int++) {
		$arrParticipant2[] = $arrParticipants[$int];
	}
} else {
	//only one column
	$arrParticipant1 = $arrParticipants;
}

//fdf
$filename =  "pdf/10770_6.fdf";
$somecontent = file_get_contents($filename);
//the date
pdfReplacementJetFile("[[TODAYS DATE]]", date("m/d/Y"), $somecontent);

pdfReplacementJetFile("[[S SIGNATURE OPTIONAL]]", "", $somecontent);
pdfReplacementJetFile("[[SIGNATURE DATE OPTIONAL]]", "", $somecontent);
pdfReplacementJetFile("[[S SIGNATURE]]", "S " . $cus_signature, $somecontent);
pdfReplacementJetFile("[[SIGNATURE PRINT]]", $cus_signature, $somecontent);
pdfReplacementJetFile("[[EFFORTS]]", $verification_description, $somecontent);
pdfReplacementJetFile("[[FIRM]]", $cus_name, $somecontent);
pdfReplacementJetFile("[[RESOLVED]]", "Yes", $somecontent);
pdfReplacementJetFile("[[SIX MONTHS]]", "Yes", $somecontent);

//output
$host = $_SERVER['HTTP_HOST'];
$host = str_replace("www.", "", $host);
$somecontent = str_replace("[[DESTINATION]]", "www." . $host . "/jetfiler", $somecontent);

//die($somecontent);
$filename = "verification_out.fdf";
$filename_output = "verification_" . $case_id . ".pdf";

if (file_exists("pdf/" . $filename)) {
	unlink($filename);
}
if (!$handle = fopen("pdf/" . $filename, 'w')) {
	 echo "Cannot open file ($filename)";
	 exit;
}

// Write $somecontent to our opened file.
if (fwrite($handle, $somecontent) === FALSE) {
   echo "Cannot write to file ($filename)";
   exit;
}

if ($nopublish=="y") {
	//header('Content-type: application/pdf');
	//header('Content-Disverificationition: attachment; filename="Download.pdf"');
	//passthru("pdftk file.pdf fill_form " . $filename. " output - ");
	//passthru("pdftk pdf/verification.pdf fill_form " . $filename. " output " . $filename_output);
	//exit;

	//echo $filename_output;
	
	$destination_folder = "../uploads/" . $_SESSION['user_customer_id'] . "/" . $case_id . "/jetfiler/";
	if (!is_dir($destination_folder)) {
		mkdir($destination_folder, 0755, true);
	}
	$filename = $_SERVER['DOCUMENT_ROOT'] . "\\jetfiler\\pdf\\" . $filename;
	$display_filename_output = $filename_output;
	$filename_output = $_SERVER['DOCUMENT_ROOT'] . "\\uploads\\" . $_SESSION['user_customer_id'] . "\\" . $case_id . "\\jetfiler\\" . $filename_output;
	$source_pdf = $_SERVER['DOCUMENT_ROOT'] . "\\jetfiler\\pdf\\10770_6.pdf";
	passthru("pdftk " . $source_pdf . " fill_form " . $filename. " output " . $filename_output);
	//exit;

	echo $display_filename_output;
} else {
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, verificationt-check=0, pre-check=0"); 
	//header('Content-type: application/pdf');
	header('Content-type: application/vnd.fdf');
	
	// It will be called downloaded.pdf
	header('Content-Disverificationition: attachment; filename="' . $filename . '"');
	
	// The PDF source is in original.pdf
	readfile($filename);
}
?>
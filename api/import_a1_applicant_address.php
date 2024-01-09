<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once('../shared/legacy_session.php');
set_time_limit(30000);

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;
$last_updated_date = date("Y-m-d H:i:s");

include("connection.php");
		
try {
	$db = getConnection();
	
	include("customer_lookup.php");
	
	$sql = "SELECT ccase.case_uuid, cpers.person_uuid, acc.CASENO, acc.CARDCODE, acc.TYPE partie_type,  `ac`.`CARDCODE`,  `ac`.`FIRMCODE`,  `ac`.`LETSAL`,  
	`ac`.`SALUTATION`,  `ac`.`FIRST`,  `ac`.`MIDDLE`,  `ac`.`LAST`,  `ac`.`SUFFIX`,  `ac`.`SOCIAL_SEC`,  `ac`.`TITLE`,  `ac`.`HOME`,  
	`ac`.`BUSINESS`,  `ac`.`FAX` person_fax,  `ac`.`CAR`,  `ac`.`BEEPER`,  `ac`.`EMAIL`,  `ac`.`BIRTH_DATE`,  `ac`.`INTERPRET`,  
	`ac`.`LANGUAGE`,  `ac`.`LICENSENO`,  `ac`.`SPECIALTY`,  `ac`.`MOTHERMAID`,  `ac`.`PROTECTED`,
	`ac2`.`FIRMCODE`,  `ac2`.`FIRM`,  `ac2`.`VENUE`,  `ac2`.`TAX_ID`,  `ac2`.`ADDRESS1`,  `ac2`.`ADDRESS2`,  `ac2`.`CITY`,  `ac2`.`STATE`,  `ac2`.`ZIP`,  
	`ac2`.`PHONE1`,  `ac2`.`PHONE2`,  `ac2`.`FAX` partie_fax,  `ac2`.`FIRMKEY`,  `ac2`.`COLOR`,  `ac2`.`EAMSREF`,
	card3.NAME eams_name, card3.ADDRESS1 eams_street, card3.ADDRESS2 eams_suite, 
	card3.CITY eams_city, card3.STATE eams_state, card3.ZIP eams_zip, card3.PHONE eams_phone
	FROM `harmon`.casecard acc
	INNER JOIN `harmon`.card ac
	ON acc.CARDCODE = ac.CARDCODE
	INNER JOIN `harmon`.card2 ac2
	ON ac.FIRMCODE = ac2.FIRMCODE
	
	INNER JOIN harmon.harmon_case ccase
	ON acc.CASENO = ccase.cpointer
	INNER JOIN harmon.harmon_case_person cpers
	ON ccase.case_uuid = cpers.case_uuid
	
	LEFT OUTER JOIN `harmon`.card3
	ON ac2.EAMSREF = card3.EAMSREF
	WHERE 1
	#AND acc.CASENO = 104
	AND acc.`TYPE` = 'APPLICANT'
	ORDER BY acc.CARDCODE";
	
	$cases = DB::select($sql);
	
	foreach($cases as $case_key=>$case){
		//die(print_r($case));
		echo "Processing -> " . $case_key. " == <a href='import_a1.php?customer_id=" . $customer_id . "&id=" .  $case->CASENO . "'>" .  $case->CASENO . "</a><br />\r\n";
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$process_start_time = $time;
		
		$case_no = $case->CASENO;
		$case_uuid = $case->case_uuid;
		$person_uuid = $case->person_uuid;
		
		$full_address = "";
		if ($case->ADDRESS1!="") {
			$full_address = $case->ADDRESS1;
		}
		if ($case->ADDRESS2!="") {
			$full_address .= ", " . $case->ADDRESS2;
		}
		if ($case->CITY!="") {
			$full_address .= ", " . $case->CITY;
		}
		if ($case->STATE!="") {
			$full_address .= ", " . $case->STATE;
		}
		if ($case->ZIP!="") {
			$full_address .= " " . $case->ZIP;
		}
		
		$sql = "UPDATE ikase_" . $data_source . ".cse_person pers
		SET pers.street = '" . addslashes($case->ADDRESS1) . "',
		pers.suite = '" . addslashes($case->ADDRESS2) . "',
		pers.city = '" . addslashes($case->CITY) . "',
		pers.state = '" . addslashes($case->STATE) . "',
		pers.zip = '" . addslashes($case->ZIP) . "',
		pers.full_address = '" . addslashes($full_address) . "',
		pers.`phone` = '" . addslashes($case->HOME) . "',
		pers.`work_phone` = '" . addslashes($case->BUSINESS) . "',
		pers.`fax` = '" . addslashes($case->person_fax) . "',
		pers.`cell_phone` = '" . addslashes($case->CAR) . "',
		 pers.`other_phone` = '" . addslashes($case->BEEPER) . "'
		WHERE pers.person_uuid = '" . $person_uuid . "'";
			
		//die($sql); 
		$stmt = DB::run($sql);
		
	}

	echo "done at " . date("H:i:s");
} catch(PDOException $e) {
	echo $sql . "\r\n<br>";
	$error = array("error"=> array("text"=>$e->getMessage()));
	die( json_encode($error));
}

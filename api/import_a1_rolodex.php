<?php
require_once('../shared/legacy_session.php');
session_write_close();
include("connection.php");

try {
	$db = getConnection();
	
	include("customer_lookup.php");
	/*
	$sql = "SELECT acc.CASENO, acc.CARDCODE, acc.TYPE partie_type,  `ac`.`CARDCODE`,  `ac`.`FIRMCODE`,  `ac`.`LETSAL`,  
		`ac`.`SALUTATION`,  `ac`.`FIRST`,  `ac`.`MIDDLE`,  `ac`.`LAST`,  `ac`.`SUFFIX`,  `ac`.`SOCIAL_SEC`,  `ac`.`TITLE`,  `ac`.`HOME`,  
		`ac`.`BUSINESS`,  `ac`.`FAX` person_fax,  `ac`.`CAR`,  `ac`.`BEEPER`,  `ac`.`EMAIL`,  `ac`.`BIRTH_DATE`,  `ac`.`INTERPRET`,  
		`ac`.`LANGUAGE`,  `ac`.`LICENSENO`,  `ac`.`SPECIALTY`,  `ac`.`MOTHERMAID`,  `ac`.`PROTECTED`,
		`ac2`.`FIRMCODE`,  `ac2`.`FIRM`,  `ac2`.`VENUE`,  `ac2`.`TAX_ID`,  `ac2`.`ADDRESS1`,  `ac2`.`ADDRESS2`,  `ac2`.`CITY`,  `ac2`.`STATE`,  `ac2`.`ZIP`,  
		`ac2`.`PHONE1`,  `ac2`.`PHONE2`,  `ac2`.`FAX` partie_fax,  `ac2`.`FIRMKEY`,  `ac2`.`COLOR`,  `ac2`.`EAMSREF`,
		card3.NAME eams_name, card3.ADDRESS1 eams_street, card3.ADDRESS2 eams_suite, 
		card3.CITY eams_city, card3.STATE eams_state, card3.ZIP eams_zip, card3.PHONE eams_phone
		FROM `" . $data_source . "`.casecard acc
		LEFT OUTER JOIN `" . $data_source . "`.card ac
		ON acc.CARDCODE = ac.CARDCODE
		LEFT OUTER JOIN `" . $data_source . "`.card2 ac2
		ON ac.FIRMCODE = ac2.FIRMCODE
		LEFT OUTER JOIN `" . $data_source . "`.card3
		ON ac2.EAMSREF = card3.EAMSREF
		WHERE 1
		ORDER BY acc.CARDCODE";
	$parties = DB::select($sql);
	die("count:" . count($parties));
	
	foreach($parties as $partie) {		
		if ($type=="applicant" || $type=="client") {
		} else {
			//locate matching record in ikase
			$sql = "SELECT corporation_uuid
			FROM `" . $data_source . "`.`" . $data_source . "_corporation`
			WHERE customer_id = " . $customer_id . "
			AND corporation_uuid = parent_corporation_uuid
			AND type = '" . addslashes(strtolower($partie->partie_type)) . "'
			AND deleted = 'N'
			AND company_name = '" . addslashes($partie->FIRM) . "'
			AND full_address = '" . addslashes($full_address_partie) . "'";
			
			$stmt = DB::run($sql);
			$corp = $stmt->fetchObject(); 
			
			if (!is_object($corp) == 0) {
				die(print_r($partie));
				include("import_a1_rolodex_companies.php");
			}
		}
	}
	die("update cell");
	*/
	$sql = "SELECT acc.CASENO, acc.CARDCODE, acc.TYPE partie_type,  `ac`.`CARDCODE`,  `ac`.`FIRMCODE`,  `ac`.`LETSAL`,  
		`ac`.`SALUTATION`,  `ac`.`FIRST`,  `ac`.`MIDDLE`,  `ac`.`LAST`,  `ac`.`SUFFIX`,  `ac`.`SOCIAL_SEC`,  `ac`.`TITLE`,  `ac`.`HOME`,  
		`ac`.`BUSINESS`,  `ac`.`FAX` person_fax,  `ac`.`CAR`,  `ac`.`BEEPER`,  `ac`.`EMAIL`,  `ac`.`BIRTH_DATE`,  `ac`.`INTERPRET`,  
		`ac`.`LANGUAGE`,  `ac`.`LICENSENO`,  `ac`.`SPECIALTY`,  `ac`.`MOTHERMAID`,  `ac`.`PROTECTED`,
		`ac2`.`FIRMCODE`,  `ac2`.`FIRM`,  `ac2`.`VENUE`,  `ac2`.`TAX_ID`,  `ac2`.`ADDRESS1`,  `ac2`.`ADDRESS2`,  `ac2`.`CITY`,  `ac2`.`STATE`,  `ac2`.`ZIP`,  
		`ac2`.`PHONE1`,  `ac2`.`PHONE2`,  `ac2`.`FAX` partie_fax,  `ac2`.`FIRMKEY`,  `ac2`.`COLOR`,  `ac2`.`EAMSREF`,
		card3.NAME eams_name, card3.ADDRESS1 eams_street, card3.ADDRESS2 eams_suite, 
		card3.CITY eams_city, card3.STATE eams_state, card3.ZIP eams_zip, card3.PHONE eams_phone
		FROM `" . $data_source . "`.casecard acc
		INNER JOIN `" . $data_source . "`.card ac
		ON acc.CARDCODE = ac.CARDCODE
		INNER JOIN `" . $data_source . "`.card2 ac2
		ON ac.FIRMCODE = ac2.FIRMCODE
		LEFT OUTER JOIN `" . $data_source . "`.card3
		ON ac2.EAMSREF = card3.EAMSREF
		WHERE 1
        AND ac.CAR IS NOT NULL
		ORDER BY acc.CARDCODE";
	
	$rolos = DB::select($sql);
	
	foreach($rolos as $rolo) {			
		//locate matching record in ikase
		$sql = "SELECT * 
		FROM ikase_" . $data_source . ".cse_corporation
		WHERE last_name = '" . addslashes($rolo->LAST) . "'
		AND first_name = '" . addslashes($rolo->FIRST) . "'";
		
		if ($rolo->FIRM!="") {
			$sql .= "
			AND `company_name` = '" .  addslashes($rolo->FIRM) . "'
			AND corporation_uuid = parent_corporation_uuid";
		}
		
		$corps = DB::select($sql);
		
		if (count($corps) > 0) {
			foreach($corps as $corp) {
				$employee_cell = $rolo->CAR;
				$employee_cell = noSpecialAtAll($employee_cell);
				$employee_cell = str_replace(" ", "", $employee_cell);	
				$employee_cell = substr($employee_cell, 0, 3) . "-" . substr($employee_cell, 3, 3) . "-" . substr($employee_cell, 6);
				$sql = "UPDATE ikase_" . $data_source . ".cse_corporation
				SET employee_cell = '" . $employee_cell . "'
				WHERE parent_corporation_uuid = '" . $corp->corporation_uuid . "'";
				//die($sql);
				$stmt = DB::run($sql);
			}
		} else {
			
		}
	}
	echo "done at " . date("H:i:s");
} catch(PDOException $e) {
	echo $sql . "\r\n<br>";
	$error = array("error"=> array("text"=>$e->getMessage()));
	die( json_encode($error));
}		

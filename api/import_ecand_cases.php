<?php
require_once('../shared/legacy_session.php');
set_time_limit(3000);

session_write_close();

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;

include("connection.php");

function getNickConnection() {
	//$dbhost="54.149.211.191";
	$dbhost="ikase.org";
	$dbuser="root";
	$dbpass="admin527#";
	$dbname="ikase";
	
	$dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);            
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbh;
}
function returnEntity($entity_id, $data_source = "gaylord") {
	$sql = "SELECT *
	FROM " . $data_source . ".interim_entities ie
	LEFT OUTER JOIN " . $data_source . ".interim_address ia
    ON ie.name_id = ia.original_id
	WHERE ie.original_id = '" . $entity_id . "'";
	
	$db = getNickConnection();
	$stmt = DB::run($sql);
	$entity = $stmt->fetchObject();
	
	return $entity;
}
function addToArray(&$arr, $value) {
	if ($value!="") {
		$arr[] = $value;
	}
}
function insertPerson($case_uuid, $entity, $data_source = "gaylord", $customer_id = 1136) {
	$last_updated_date = date("Y-m-d H:i:s");
	$full_name = $entity->name;
	
	$arrFullAddress = array();
	addToArray($arrFullAddress, $entity->street);
	addToArray($arrFullAddress, $entity->suite);
	addToArray($arrFullAddress, $entity->city);
	addToArray($arrFullAddress, $entity->state);
	addToArray($arrFullAddress, $entity->zipcode);
	
	
	$full_address_applicant = implode(", ", $arrFullAddress);
	$full_address_applicant = str_replace($entity->state . ",", $entity->state, $full_address_applicant);
	
	//die($full_address_applicant);
	//MAKE SURE THAT THIS IS NOT A REPEAT, LOOK UP ID
	$sql = "SELECT person_uuid
	FROM `" . $data_source . "`.`" . $data_source . "_person`
	WHERE customer_id = " . $customer_id . "
	AND person_uuid = parent_person_uuid
	AND deleted = 'N'
	AND full_name = '" . addslashes($full_name) . "'
	AND full_address = '" . addslashes($full_address_applicant) . "'";
	//echo $sql . "\r\n";
	
	$db = getNickConnection();
	$stmt = DB::run($sql);
	$rolodex = $stmt->fetchObject();
	
	$table_uuid = uniqid("AP", false);
	$parent_table_uuid = uniqid("PA", false);
			
	$blnRolodex = false;
	if (is_object($rolodex)) {
		$parent_table_uuid = $rolodex->person_uuid;
		$blnRolodex = true;
	}
	
	$arrSet = array();
	$arrSet[] = addslashes($full_name);
	$arrName = explode(",", $full_name);
	$first_name = $full_name;
	$last_name = "";
	if (count($arrName)  > 0) {
		$first_name = $arrName[1];
		$last_name = $arrName[0];
	}
	$arrSet[] = addslashes($first_name);
	$arrSet[] = "";
	$arrSet[] = addslashes($last_name);
	$arrSet[] = "";
	$arrSet[] = addslashes($full_address_applicant);
	$arrSet[] = addslashes($entity->street);
	$arrSet[] = addslashes($entity->city);
	$arrSet[] = substr($entity->state, 0, 2);
	$arrSet[] = $entity->zipcode;
	$arrSet[] = addslashes($entity->suite);
	$arrSet[] = addslashes($entity->phone);
	$arrSet[] = addslashes($entity->email);
	$arrSet[] = addslashes($entity->ssn);
	$arrSet[] = substr($entity->ssn, strlen($entity->ssn) - 4, 4);
	if ($entity->dob!='0000-00-00 00:00:00') {
		$dob = date("Y-m-d", strtotime($entity->dob));
	} else {
		$dob = '0000-00-00';
	}
	$arrSet[] = addslashes($dob);
	$arrSet[] = addslashes($entity->salutation);
	$arrSet[] = addslashes($entity->language);
	$arrSet[] = addslashes($entity->spouse);
	if (!$blnRolodex) {
		//insert the parent record first
		$sql_applicant = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_person` (`person_uuid`, `customer_id`, `parent_person_uuid`, `full_name`, `first_name`, `middle_name`, `last_name`, `aka`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `email`, `ssn`, `ssn_last_four`, `dob`, `salutation`, `language`, `spouse`, `last_updated_date`, `last_update_user`, `deleted`) 
		VALUES('" . $parent_table_uuid . "', '" . $customer_id . "', '" . $parent_table_uuid . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N');";
		
		$db = getNickConnection();
		$stmt = $db->prepare($sql_applicant);  
		//echo $sql_applicant . "\r\n\r\n"; 
		$stmt->execute();
	}
	
	$sql_applicant = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_person` (`person_uuid`, `customer_id`, `parent_person_uuid`, `full_name`, `first_name`, `middle_name`, `last_name`, `aka`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `email`, `ssn`, `ssn_last_four`, `dob`, `salutation`, `language`, `spouse`, `last_updated_date`, `last_update_user`, `deleted`) 
		VALUES('" . $table_uuid . "', '" . $customer_id . "', '" . $parent_table_uuid . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N');";
	
	$db = getNickConnection();
	$stmt = $db->prepare($sql_applicant);  
	//echo $sql_applicant . "\r\n\r\n"; 
	$stmt->execute();
	
	$case_table_uuid = uniqid("CA", false);
	//attach applicant to kase
	$sql_applicant = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_person` (`case_person_uuid`, `case_uuid`, `person_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
	VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', 'main', '" . $last_updated_date . "', 'system', '" . $customer_id . "');";
	
	$db = getNickConnection();
	$stmt = $db->prepare($sql_applicant);  
	//echo $sql_applicant . "\r\n\r\n"; 
	$stmt->execute();
}

function insertCorporation($case_uuid, $entity, $type, $data_source = "gaylord", $customer_id = 1136) {
	$last_updated_date = date("Y-m-d H:i:s");
	$full_name = $entity->name;
	
	$arrFullAddress = array();
	addToArray($arrFullAddress, $entity->street);
	addToArray($arrFullAddress, $entity->suite);
	addToArray($arrFullAddress, $entity->city);
	addToArray($arrFullAddress, $entity->state);
	addToArray($arrFullAddress, $entity->zipcode);
	
	
	$full_address_applicant = implode(", ", $arrFullAddress);
	$full_address_applicant = str_replace($entity->state . ",", $entity->state, $full_address_applicant);
	
	//die($full_address_applicant);
	//MAKE SURE THAT THIS IS NOT A REPEAT, LOOK UP ID
	$sql = "SELECT corporation_uuid
	FROM `" . $data_source . "`.`" . $data_source . "_corporation`
	WHERE customer_id = " . $customer_id . "
	AND corporation_uuid = parent_corporation_uuid
	AND deleted = 'N'
	AND full_name = '" . addslashes($full_name) . "'
	AND full_address = '" . addslashes($full_address_applicant) . "'";
	//echo $sql . "\r\n";
	
	$db = getNickConnection();
	$stmt = DB::run($sql);
	$rolodex = $stmt->fetchObject();
	
	$table_uuid = uniqid("AC", false);
	$parent_table_uuid = uniqid("CA", false);
			
	$blnRolodex = false;
	if (is_object($rolodex)) {
		$parent_table_uuid = $rolodex->corporation_uuid;
		$blnRolodex = true;
	}
	
	$arrSet = array();
	$arrSet[] = addslashes($full_name);
	$arrSet[] = addslashes($full_name);
	$arrSet[] = $type;
	$arrName = explode(",", $full_name);
	$first_name = $full_name;
	$last_name = "";
	if (count($arrName)  > 0) {
		$first_name = $arrName[1];
		$last_name = $arrName[0];
	}
	$arrSet[] = addslashes($first_name);
	$arrSet[] = addslashes($last_name);
	$arrSet[] = addslashes($full_address_applicant);
	$arrSet[] = addslashes($entity->street);
	$arrSet[] = addslashes($entity->city);
	$arrSet[] = substr($entity->state, 0, 2);
	$arrSet[] = $entity->zipcode;
	$arrSet[] = addslashes($entity->suite);
	$arrSet[] = addslashes($entity->phone);
	$arrSet[] = addslashes($entity->email);
	$arrSet[] = addslashes($entity->ssn);
	
	if ($entity->dob!='0000-00-00 00:00:00') {
		$dob = date("Y-m-d", strtotime($entity->dob));
	} else {
		$dob = '0000-00-00';
	}
	
	$arrSet[] = $dob;
	$arrSet[] = addslashes($entity->salutation);

	if (!$blnRolodex) {
		//insert the parent record first
		$sql_corp = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation` (`corporation_uuid`, `customer_id`, `parent_corporation_uuid`, `full_name`, `company_name`, `type`, `first_name`, `last_name`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `email`, `ssn`, `dob`, `salutation`, `copying_instructions`, `comments`, `kai_info`, `last_updated_date`, `last_update_user`, `deleted`) 
		VALUES('" . $parent_table_uuid . "', '" . $customer_id . "', '" . $parent_table_uuid . "', '" . implode("','", $arrSet) . "', '', '', '', '" . $last_updated_date . "', 'system', 'N');";
		
		$db = getNickConnection();
		$stmt = $db->prepare($sql_corp);  
		//echo $sql_corp . "\r\n\r\n"; 
		$stmt->execute();
	}
	
	$sql_corp = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation` (`corporation_uuid`, `customer_id`, `parent_corporation_uuid`, `full_name`, `company_name`, `type`, `first_name`, `last_name`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `email`, `ssn`, `dob`, `salutation`, `copying_instructions`, `comments`, `kai_info`, `last_updated_date`, `last_update_user`, `deleted`) 
		VALUES('" . $table_uuid . "', '" . $customer_id . "', '" . $parent_table_uuid . "', '" . implode("','", $arrSet) . "', '', '', '', '" . $last_updated_date . "', 'system', 'N');";
	
	$db = getNickConnection();
	$stmt = $db->prepare($sql_corp);  
	//echo $sql_corp . "\r\n\r\n"; 
	$stmt->execute();
	
	$case_table_uuid = uniqid("CA", false);
	//attach applicant to kase
	$sql_corp = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_corporation` (`case_corporation_uuid`, `case_uuid`, `corporation_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
	VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', '" . $type . "', '" . $last_updated_date . "', 'system', '" . $customer_id . "');";
	
	$db = getNickConnection();
	$stmt = $db->prepare($sql_corp);  
	//echo $sql_corp . "\r\n\r\n"; 
	$stmt->execute();
}
//WHERE cli.fileno = 1061
//die($sql);
try {
	$db = getNickConnection();
	
	include("customer_lookup.php");
	
	
	$sql_bp = "SELECT * 
	FROM `ikase`.`cse_bodyparts` 
	WHERE 1
	ORDER BY code ASC";
	
	$db = getNickConnection();
	$stmt = $db->prepare($sql_bp);
	$stmt = $db->query($sql_bp);
	$bodyparts = $stmt->fetchAll(PDO::FETCH_OBJ);
	
	$arrBodyParts = array();
	foreach($bodyparts as $bodypart){
		$arrBodyParts[$bodypart->code] = $bodypart->bodyparts_uuid;
	}
	//die(print_r($arrBodyParts));
	
	$sql = "TRUNCATE `" . $data_source . "`.`" . $data_source . "_case`;

	TRUNCATE `" . $data_source . "`.`" . $data_source . "_case_corporation`;
	
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_corporation`;
	
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_injury`;
	
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_case_injury`;
	
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_notes`;
	
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_case_notes`;
	
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_person`;
	
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_case_person`;
	
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_injury_bodyparts`;
	
	";
	
	//die($sql);
	/*
	$db = getNickConnection();
	$stmt = DB::run($sql);
	*/
	//open the basic case sql
	//insert case, plaintiff, defendant, injury
	//insert other entities
	//get the body parts
	//get the tasks
	//get the documents
	/*
	$sql = "SELECT gwp.*, gwi.*, inj.*, plaintiff.*, defendant.* 
	FROM " . $data_source . ".interim_case gwp
	
	LEFT OUTER JOIN " . $data_source . ".interim_entities plaintiff
	ON gwp.plaintiff_id = plaintiff.original_id
	
	LEFT OUTER JOIN " . $data_source . ".interim_entities defendant
	ON gwp.defendant_id = defendant.original_id
	
	LEFT OUTER JOIN " . $data_source . ".interim_address venue
	ON gwp.venue_id = venue.original_id
	
	LEFT OUTER JOIN " . $data_source . ".interim_case_injury gwi
	ON gwp.original_id = gwi.workproduct_id
	
	LEFT OUTER JOIN " . $data_source . ".interim_injury inj
	ON gwi.inj_id = inj.original_id
	
	LEFT OUTER JOIN " . $data_source . "." . $data_source . "_case ccase
	ON gwp.case_uuid = ccase.case_uuid
	
	WHERE gwp.original_id = '0030D089CA8F49449A0776EDA88DE11A'
	AND ccase.case_uuid IS NULL";
	*/
	$sql = "SELECT gwp.*
	FROM `" . $data_source . "`.`interim_case` gwp
	
	LEFT OUTER JOIN `" . $data_source . "`.`" . $data_source . "_case` ccase
	ON gwp.case_uuid = ccase.case_uuid
	
	WHERE 1
	#AND gwp.original_id = 'A11079BCF451462FA56FA841E8469768'
	AND ccase.case_uuid IS NULL
	LIMIT 0, 1";
	//die($sql);
		
	$last_updated_date = date("Y-m-d H:i:s");
	
	$db = getNickConnection();
	$cases = DB::select($sql);
	
	foreach($cases as $case) {
		echo "Processing -> " . $case->original_id . "\r\n\r\n";
		//die(print_r($case));
		
		$case_uuid = uniqid("KS", false);
		
		if ($case->dol != "0000-00-00 00:00:00") {
			$case_date = date("Y-m-d H:i:s", strtotime($case->dol));
		} else {
			$case_date = $case->dol;
		}
		if ($case->status=="Active") {
			$case->status = "Open";
		}
		$sql = "INSERT INTO " . $data_source . "." . $data_source . "_case (case_uuid, case_number, case_type, cpointer, ecand_id, case_date, case_status, customer_id)
		VALUES (
		'" . $case_uuid . "', '" . $case->wpid . "', '" . addslashes($case->type) . "','" . $case->original_id . "','" . $case->original_id . "', '" . $case_date . "', '" . addslashes($case->status) . "','" . $customer_id . "'
		);";
		
		$db = getNickConnection();
		$stmt = $db->prepare($sql);
		//echo $sql . "\r\n";
		$stmt->execute();
		
		//quick note
		if ($case->quick!="") {
			$case_notes_uuid = uniqid("CN", false);
			$notes_uuid = uniqid("NT", false);
			$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_notes` (`case_notes_uuid`, `case_uuid`,  `notes_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
			VALUES ('" . $case_notes_uuid . "', '" . $case_uuid . "', '" . $notes_uuid . "', 'quick', '" . $last_updated_date . "', 'system', 'N', '" . $customer_id . "');";
			
			$db = getNickConnection();
			$stmt = $db->prepare($sql);
			//echo $sql . "\r\n";
			$stmt->execute();
			
			$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_notes` (`notes_uuid`, `note`, `dateandtime`, `entered_by`, `customer_id`, `type`)
			VALUES ('" . $notes_uuid . "', '" . addslashes($case->quick) . "', '" . $case_date . "', 'system', '" . $customer_id . "', 'quick');";
			
			$db = getNickConnection();
			$stmt = $db->prepare($sql);
			//echo $sql . "\r\n";
			$stmt->execute();
		}
		
		//get injury.  if no injury create one
		$sql = "SELECT inj.* 
		FROM " . $data_source . ".interim_case_injury ici
		INNER JOIN " . $data_source . ".interim_injury inj
		ON ici.inj_id = inj.original_id
		WHERE workproduct_id = '" . $case->original_id . "';";
		
		//echo $sql . "\r\n";
		
		$db = getNickConnection();
		$stmt = $db->prepare($sql);
		$injuries = DB::select($sql);
		
		//die(print_r($injuries));
		$injury_number = 0;
		foreach($injuries as $injury) {
			$injury_uuid = uniqid("KI", false);
			
			$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_injury` (`injury_uuid`, `injury_number`, `adj_number`, `type`, `start_date`, `end_date`, `body_parts`, `explanation`, `ct_dates_note`, `full_address`, `customer_id`, `deleted`)
			VALUES('" . $injury_uuid . "', " . ($injury_number+1) . ", '" . addslashes($injury->adj_number) . "', '', '" . $injury->doi . "','" . $injury->eoi . "', '', '" . addslashes($injury->description) . "','" . addslashes($injury->description_ct) . "','" . addslashes($injury->location) . "', " . $customer_id . ", 'N');";
			
			$db = getNickConnection();
			$stmt = $db->prepare($sql);
			//echo $sql . "\r\n";
			$stmt->execute();
						
			if ($injury->doi != "0000-00-00 00:00:00") {
				//update `statute_limitation`
				$sql = "UPDATE `" . $data_source . "`.`" . $data_source . "_injury` 
				SET statute_limitation = DATE_ADD(`start_date`, INTERVAL 2 YEAR)
				WHERE injury_uuid = '" . $injury_uuid . "';";
				
				$stmt = $db->prepare($sql);  
				//echo $sql . "\r\n\r\n";
				$stmt->execute();
			}
			
			
			$injury_number++;
			
			$case_table_uuid = uniqid("KA", false);
			$attribute_1 = "main";
			
			//now we have to attach the injury to the case 
			$sql_injury = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_injury` (`case_injury_uuid`, `case_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $injury_uuid . "', 'main', '" . $last_updated_date . "', 'system', '" . $customer_id . "');";
	
			$db = getNickConnection();
			$stmt = $db->prepare($sql_injury);
			//echo $sql_injury . "\r\n";
			$stmt->execute();
			
			$sql = "SELECT * 
			FROM " . $data_source . ".interim_injury_bodypart
			WHERE inj_id = '" . $injury->original_id . "'";		
			
			//echo $sql . "\r\n\r\n";
			
			$db = getNickConnection();
			$arrParts = DB::select($sql);
			
			//die(print_r($arrParts));
			
			$int = 1;
			foreach($arrParts as $part){
				$code = trim($part->code);
				$bodyparts_uuid = $arrBodyParts[$code];
				//echo $part . " ==> " . $bodyparts_uuid . "<br>\r\n";
				$table_uuid = uniqid("KS", false);
				
				$sql = "INSERT INTO `" . $data_source . "`." . $data_source . "_injury_bodyparts (`injury_bodyparts_uuid`, `injury_uuid`, `bodyparts_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
					VALUES ('" . $table_uuid . "', '" . $injury_uuid . "','" . $bodyparts_uuid . "','" . $int . "', '" . date("Y-m-d H:i:s") . "', 'system', '" . $customer_id . "')";
					
				//echo $sql . "\r\n\r\n";
				//die();
				$db = getNickConnection();
				$stmt = DB::run($sql);
				
				//increment
				$int++;
			}
		}
		
		//get plaintiff, if wcab -> applicant
		$plaintiff = returnEntity($case->plaintiff_id, $data_source);
		if ($case->	type=='Workers Compensation') {
			$blnInserted = insertPerson($case_uuid, $plaintiff, $data_source, $customer_id);
		} else {
			$blnInserted = insertCorporation($case_uuid, $plaintiff, "plaintiff", $data_source, $customer_id);
		}
		
		$type = "defendant";
		if ($case->	type=='Workers Compensation') {
			$type = "employer";
		}
		//get defendant
		$defendant = returnEntity($case->defendant_id, $data_source);
		$blnInserted = insertCorporation($case_uuid, $defendant, $type, $data_source, $customer_id);
		
		//die();
		//die(print_r($case));
		
		$sql = "UPDATE `gaylord`.`interim_case`
		SET `case_uuid` = '" . $case_uuid . "'
		WHERE `original_id` = :id";
		
		$db = getNickConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $case->original_id);
		$stmt->execute();
		
	}
	
	
	$sql = "SELECT COUNT(`workproduct_id`) case_count
	FROM gaylord.interim_case
	WHERE `case_uuid` IS NOT NULL";
	
	$db = getNickConnection();
	$stmt = DB::run($sql);
	$completed = $stmt->fetchObject();
	
	$case_count = 1207;
	$completed_count = $completed->case_count;
	
	if ($case_count - $completed_count > 0) {	
		echo "<script language='javascript'>parent.runCases(" . $completed_count . "," . $case_count . ")</script>";
	}
	die("all done");
	
} catch(PDOException $e) {
	echo "ERROR:<br />
";
	echo $sql;
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}

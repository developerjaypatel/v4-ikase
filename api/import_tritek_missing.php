<?php
require_once('../shared/legacy_session.php');
set_time_limit(3000);

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;

include("connection.php");

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

//WHERE cli.fileno = 1061
//die($sql);
try {
	$db = getNickConnection();
	
	include("customer_lookup.php");
	
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
	
	$sql = "SELECT gcase.* 
	FROM `" . $data_source . "`.`missings` gcase
	WHERE 1
	AND processed = 'N'
	ORDER BY cpointer DESC
	LIMIT 0, 1";
	echo $sql . "\r\n<br>";
	//#AND (CASENO = 19493 OR CASENO = 19481 OR AND CASENO = 19013) 
	//die();
	$cases = DB::select($sql);
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
		echo " => QUERY completed in " . $total_time . "<br /><br />";
	//die(print_r($cases));
	$found = count($cases);
	
	foreach($cases as $case_key=>$case){
		
		$sql = "SELECT 
		 `cli`.`lastname`,
    `cli`.`firstname`,
    `cli`.`midname`,
    `cli`.`add1`,
    `cli`.`add2`,
    `cli`.`city`,
    `cli`.`clientst`,
    `cli`.`clientzip`,
    `cli`.`clisalut`,
    `cli`.`marriagest`,
    `cli`.`clientdob`,
    `cli`.`clientss`,
    `cli`.`clienttel`,
    `cli`.`clientext`,
    `cli`.`clientoff`,
    `cli`.`offext`,
    `cli`.`clientothe`,
    `cli`.`othext`,
    `cli`.`casestat`,
    `cli`.`casetype`,
    `cli`.`fileno`,
    `cli`.`opendate`,
    `cli`.`filedate`,
    `cli`.`casestate`,
    `cli`.`caseoption`,
    `cli`.`caseno`,
    `cli`.`casename`,
    `cli`.`email`,
    `cli`.`workcode`,
    `cli`.`attycode`,
    `cli`.`priority`,
    `cli`.`language`,
    `cli`.`accipoint`,
    `cli`.`ipoint`,
    `cli`.`otherp`,
    `cli`.`dpnt`,
    `cli`.`proppnt`,
    `cli`.`ctpnt`,
    `cli`.`ppnt`,
    `cli`.`oppospnt`,
    `cli`.`pattypnt`,
    `cli`.`assocpnt`,
    `cli`.`defmedpnt`,
    `cli`.`wpointer`,
    `cli`.`refsource`,
    `cli`.`expertpnt`,
    `cli`.`negpoint`,
    `cli`.`cmemo`,
    `cli`.`cpointer`,
    `cli`.`occup`,
    `cli`.`intakedate`,
    `cli`.`litpoint`,
    `cli`.`drivlic`,
    `cli`.`sex`,
    `cli`.`docpath`,
    `cli`.`closed`,
    `cli`.`lastadate`,
    `cli`.`lastadesc`,
    `cli`.`nextadate`,
    `cli`.`nextadesc`,
    `cli`.`clientdod`,
    `cli`.`persrep`,
    `cli`.`spouse`,
    `cli`.`pifiled`,
    `cli`.`picaseno`,
    `cli`.`wdfiled`,
    `cli`.`wdcaseno`,
    `cli`.`local`,
    `cli`.`relation`,
    `cli`.`smoker`,
    `cli`.`disease`,
    `cli`.`picasetype`,
    `cli`.`wdcasetype`,
    `cli`.`subcat1`,
    `cli`.`subcat2`,
    `cli`.`reffeepct`,
    `cli`.`attyfeepct`,
    `cli`.`demandamt`,
    `cli`.`dodappfile`,
    `cli`.`dodcause`,
    `cli`.`autopsydat`,
    `cli`.`burialexp`,
    `cli`.`ssreq`,
    `cli`.`ssrcvd`,
    `cli`.`sssenta`,
    `cli`.`ssrcvda`,
    `cli`.`packcompl`,
    `cli`.`packsent`,
    `cli`.`packamend`,
    `cli`.`company`,
    `cli`.`asbstatute`,
    `cli`.`asboveride`,
    `cli`.`refpoint`,
    `cli`.`amount`,
    `cli`.`spousetel`,
    `cli`.`spousessno`,
    `cli`.`persreptel`,
    `cli`.`ssprefix`,
    `cli`.`account`,
    `cli`.`tsfileno`,
    `cli`.`settled`,
    `cli`.`statusdate`,
    `cli`.`subindate`,
    `cli`.`key`,
    `cli`.`diskno`,
    `cli`.`pointold`,
    `cli`.`office`,
    `cli`.`expdate`,
    `cli`.`expbatch`,
    `cli`.`archivetxt`,
    con.`category` con_category,
    con.`lastname` con_lastname,
    con.`midname` con_midname,
    con.`firstname` con_firstname,
    con.`company` con_company,
    con.`add1` con_add1,
    con.`add2` con_add2,
    con.`city` con_city,
    con.`state` con_state,
    con.`zip` con_zip,
    con.`county` con_county,
    con.`tel` con_tel,
    con.`ext` con_ext,
    con.`fax` con_fax,
    con.`office` con_office,
    con.`offext` con_offext,
    con.`cell` con_cell,
    con.`other` con_other,
    con.`othtype`,
    con.`mailcontac`,
    con.`salut`,
    con.`cpointers`,
    con.`comments`,
    con.`ssno`,
    con.`age`,
    con.`dob`,
    con.`dobcity`,
    con.`dobstate`,
    con.`dobcountry`,
    con.`ethnicity`,
    con.`married`,
    con.`language` con_language,
    con.`sex` con_sex,
    con.`spouselast`,
    con.`spousefirs`,
    con.`spousemid`,
    con.`spouseadd1`,
    con.`spouseadd2`,
    con.`spousecity`,
    con.`spousest`,
    con.`spousezip`,
    con.`spouseltel`,
    con.`spousecell`,
    con.`spemail`,
    con.`caseinfo`,
    con.`name`,
    con.`citystzip`,
    con.`locator`,
    con.`email` con_email,
    con.`contlast`,
    con.`contfirst`,
    con.`contmid`,
    con.`contsalut`,
    acc.*,
    cij.ctdates,
    cij.body,
    emp.company emp_firm,
    emp.add1 emp_add1,
    emp.add2 emp_add2,
    emp.city emp_city,
    emp.state emp_state,
    emp.zip emp_zip,
    emp.tel emp_tel,
    emp.ext emp_ext,
    emp.email emp_email,
    emp.salutation emp_salutation,
    emp.supervisor,
    `courts`.`courtname`,
    `courts`.`courtadd1`,
    `courts`.`courtadd2`,
    `courts`.`courtcity`,
    `courts`.`courtst`,
    `courts`.`courtzip`,
    `courts`.`courtjudge`,
    `courts`.`courttel`,
    `courts`.`courtext`,
    `courts`.`courtfax`,
    `courts`.`courtemail`,
    courts.searchkey venue_abbr,
    ref.lastname referral_last,
    ref.firstname referral_first,
    ref.midname referral_mid,
    ref.firm referral_firm,
    ref.add1 referral_add1,
    ref.add2 referral_add2,
    ref.city referral_city,
    ref.state referral_state,
    ref.zip referral_zip,
    ref.tel referral_tel,
    ref.ext referral_ext,
    ref.fax referral_fax,
    ref.email referral_email,
    ref.salutation referral_salutation,
    ref.comments referral_comments
		FROM `" . $data_source . "`.`client` cli
		LEFT OUTER JOIN `" . $data_source . "`.`contacts` con ON cli.cpointer = con.cpointer
		LEFT OUTER JOIN `" . $data_source . "`.`accident` acc ON cli.accipoint = acc.apointer
		LEFT OUTER JOIN `" . $data_source . "`.`compinj` cij ON cli.cpointer = cij.compinjpnt
		LEFT OUTER JOIN `" . $data_source . "`.`employer` emp ON cli.cpointer = emp.epointer
		LEFT OUTER JOIN `" . $data_source . "`.`ccourts` ccrt ON cli.ctpnt = ccrt.courtpoint
		LEFT OUTER JOIN `" . $data_source . "`.`courts` courts ON ccrt.courtpnt = courts.courtpnt
		LEFT OUTER JOIN `" . $data_source . "`.`referral` ref ON cli.refpoint = ref.refpnt
		WHERE 1 
		AND cli.cpointer = '" . $case->cpointer . "'
		AND cli.cpointer NOT IN (SELECT cpointer FROM `" . $data_source . "`.`" . $data_source . "_case`)
		ORDER BY cli.cpointer
		LIMIT 0, 1";
		echo $sql . "\r\n\r\n<br><br>";
		//die();
		
		$stmt = $db->prepare($sql);
		
		
		echo "Processing -> " . $case->cpointer . "  ";
		
		$stmt->execute();
		$injuries = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $header_start_time), 4);
		echo " => QUERY completed in " . $total_time . "<br /><br />";
		
		//die("found:" . print_r($injuries));
		$arrCpointer = array();
		foreach($injuries as $key=>$injury){
			$last_updated_date = date("Y-m-d H:i:s");
			
			if ($key==0) {
			//	continue;
			}
			if (in_array($injury->cpointer, $arrCpointer)) {
				continue;
			}
			//echo "Processing -> " . $key. " == " . $injury->cpointer . "  ";
			$time = microtime();
			$time = explode(' ', $time);
			$time = $time[1] + $time[0];
			$process_start_time = $time;
	
	
			$arrCpointer[] = $injury->cpointer;
			//die(print_r($injury));
			////multiple adjs
			$injury->caseno = str_replace(";", ",", $injury->caseno);
			$injury->caseno = str_replace("&", ",", $injury->caseno);
			$injury->caseno = str_replace(" ", ",", $injury->caseno);
			$arrADJ = explode(",", $injury->caseno);
			//clean up the adj numbers
			foreach($arrADJ as $adj_key=>$adj) {
				if (strlen($adj) <11) {
					unset($arrADJ[$adj_key]);
				}
			}
			//die(print_r($injury));
			//1 add injury
			$first_name = $injury->firstname;
			$last_name = $injury->lastname;
			$mid_name = $injury->midname;
			
			$full_address_applicant = $injury->add1;
			if ($injury->con_add2!="") {
				$full_address_applicant .= ", " . $injury->add2;
			}
			$full_address_applicant .= ", " . $injury->city;
			$full_address_applicant .= ", " . $injury->clientst;
			$full_address_applicant .= " " . $injury->clientzip;
			
			//aka
			if (strpos(strtolower($first_name), "aka") > -1) {
				$aka = $first_name;
				//the real name must be in the first name
				$arrName = explode(" ", $last_name);
				$first_name = $arrName[0];
				$last_name = $arrName[count($arrName) - 1];
				$mid_name = "";
			}
			$full_name = $first_name;
			if ($mid_name!="") {
				$full_name .= " " . $mid_name;
			}
			$full_name .= " " . $last_name;
			
			$full_address = $injury->acciadd1;
			if ($injury->acciadd2!="") {
				$full_address .= ", " . $injury->acciadd2;
			}
			$full_address .= ", " . $injury->accicity;
			$full_address .= ", " . $injury->accist;
			$full_address .= " " . $injury->accizip;
			//reset the array in case it was unset
			$arrADJ = array_values($arrADJ); 
			
			$case_uuid = uniqid("KS", false);
			//now the kase
			$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case` (case_uuid, cpointer, case_number, case_name, case_date, case_type, venue, case_status, case_substatus, case_subsubstatus, submittedOn, attorney, worker, customer_id) 
	VALUES ('" . $case_uuid . "', '" . $injury->cpointer . "', '" . $injury->fileno . "', '" . addslashes($injury->casename) . "', '" . date("Y-m-d", strtotime($injury->opendate)) . "', '" . addslashes($injury->casetype) . "', 'LAO', '" . addslashes($injury->casestat) . "', '" . addslashes($injury->subcat1) . "', '" . addslashes($injury->subcat2) . "', '" . date("Y-m-d", strtotime($injury->opendate)) . "', '" . $injury->attycode . "', '" . $injury->workcode . "', " . $customer_id . ")";
			echo $sql . "\r\n\r\n<br><br>"; 
			//die();
			$stmt = DB::run($sql);
			//die();
			//cmemo
			//insert as a quick note
			//attach to case
			$case_notes_uuid = uniqid("CN", false);
			$notes_uuid = uniqid("NT", false);
			$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_notes` (`case_notes_uuid`, `case_uuid`,  `notes_counter`, `notes_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
			VALUES ('" . $case_notes_uuid . "', '" . $case_uuid . "', 0, '" . $notes_uuid . "', 'quick', '" . $last_updated_date . "', 'system', 'N', '" . $customer_id . "')";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);
			echo $sql . "\r\n\r\n<br><br>";
			
			$stmt->execute();
			if (date("Y", strtotime($injury->opendate)) < 1996) {
				$injury->opendate = date("Y-m-d");
			}
			$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_notes` (`notes_counter`, `notes_uuid`, `note`, `dateandtime`, `entered_by`, `customer_id`, `type`)
			VALUES (0, '" . $notes_uuid . "', '" . addslashes($injury->cmemo) . "', '" . date("Y-m-d", strtotime($injury->opendate)) . "', 'system', '" . $customer_id . "', 'quick')";
			
			echo $sql . "\r\n\r\n<br><br>";
			$stmt = DB::run($sql);
			
			
			$injury_uuid = uniqid("KI", false);
			if (isValidDate($injury->accidate, "m/d/Y")) {
				$injury_accidate = date("Y-m-d", strtotime($injury->accidate));
			} else {
				$injury_accidate = "0000-00-00";
			}
			$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_injury` (`injury_uuid`, `injury_number`, `adj_number`, `type`, `occupation`, `start_date`, `body_parts`, `ctdates`, `explanation`, `full_address`, `street`, `suite`, `city`, `state`, `zip`, `customer_id`, `deleted`)
			VALUES('" . $injury_uuid . "', " . ($injury_number+1) . ", '" . addslashes($injury->caseno) . "', '', '" . addslashes($injury->occup) . "','" . $injury_accidate . "','" . addslashes($injury->body) . "','" . addslashes($injury->ctdates) . "','" . addslashes($injury->accidesc) . "','" . addslashes($full_address) . "', '" . addslashes($injury->acciadd1) . "','" . addslashes($injury->acciadd2) . "', '" . $injury->accicity . "', '" . $injury->accist . "', '" . $injury->accizip . "', " . $customer_id . ", 'N')";
			echo $sql . "\r\n\r\n<br><br>"; 
			DB::run($sql);
	$injury_id = DB::lastInsertId();
			
			if ($injury_accidate != "0000-00-00") {
				//update `statute_limitation`
				$sql = "UPDATE `" . $data_source . "`.`" . $data_source . "_injury` 
				SET statute_limitation = DATE_ADD(`start_date`, INTERVAL 1 YEAR)
				WHERE injury_id = " . $injury_id;
				
				$stmt = $db->prepare($sql);  
				echo $sql . "\r\n\r\n<br><br>";
				$stmt->execute();
			}
			//die(print_r($injury));
			
			//now attach to case, even before I create case
			$case_table_uuid = uniqid("KA", false);
			$attribute_1 = "main";
			
			//now we have to attach the injury to the case 
			$sql_injury = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_injury` (`case_injury_uuid`, `case_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $injury_uuid . "', 'main', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
	
			echo $sql_injury . "\r\n\r\n<br><br>";  
			$stmt = DB::run($sql_injury);
			//die();
			//add the applicant
			$table_uuid = uniqid("AP", false);
			$parent_table_uuid = uniqid("PA", false);
			
			$arrSet = array();
			$arrSet[] = addslashes($full_name);
			$arrSet[] = addslashes($first_name);
			$arrSet[] = addslashes($mid_name);
			$arrSet[] = addslashes($last_name);
			$arrSet[] = addslashes($aka);
			$arrSet[] = addslashes($full_address_applicant);
			$arrSet[] = addslashes($injury->add1);
			$arrSet[] = addslashes($injury->city);
			$arrSet[] = $injury->clientst;
			$arrSet[] = $injury->clientzip;
			$arrSet[] = addslashes($injury->add2);
			if ($injury->clientext=="") {
				$arrSet[] = addslashes($injury->clienttel);
			} else {
				$arrSet[] = addslashes($injury->clienttel . " " . $injury->clientext);
			}
			$arrSet[] = addslashes($injury->email);
			$arrSet[] = addslashes($injury->con_fax);
			
			if ($injury->offext=="") {
				$arrSet[] = addslashes($injury->office);
			} else {
				$arrSet[] = addslashes($injury->office . " " . $injury->offext);
			}
			$arrSet[] = addslashes($injury->cell);
			//$arrSet[] = $injury->ssno;
			$arrSet[] = $injury->clientss;
			$arrSet[] = substr($injury->clientss, strlen($injury->clientss) - 4, 4);
			//$dob = $injury->dob;
			$dob = $injury->clientdob;
			//die($injury->clientss . " // " . $dob);
			$age = $injury->age;
			if ($dob!="") {
				$dob = date("Y-m-d", strtotime($injury->clientdob));
				$birthDate = explode("/", $injury->clientdob);
				//get age from date or birthdate
				$age = (date("md", date("U", mktime(0, 0, 0, $birthDate[0], $birthDate[1], $birthDate[2]))) > date("md") ? ((date("Y") - $birthDate[2]) - 1) : (date("Y") - $birthDate[2]));
			}
			
			if ($age=="" || !is_numeric($age)) {
				$age = 0;
			}
			$arrSet[] = $dob;
			$arrSet[] = $age;
			$arrSet[] = addslashes($injury->drivlic);
			$arrSet[] = addslashes($injury->salut);
			$arrSet[] = strtoupper(substr($injury->sex, 0, 1));
			$arrSet[] = $injury->language;
			$arrSet[] = $injury->dbo_state;			
			$arrSet[] = $injury->dbo_city;
			$arrSet[] = $injury->married;
			$arrSet[] = addslashes($injury->spousefirs . " " . $injury->spouselast);
			$arrSet[] = $injury->spousetel;
			$arrSet[] = addslashes($injury->contfirst . " " . $injury->contlast);
			//die(print_r($arrSet));
			
			//MAKE SURE THAT THIS IS NOT A REPEAT, LOOK UP ID
			$sql = "SELECT person_uuid
			FROM `" . $data_source . "`.`" . $data_source . "_person`
			WHERE customer_id = " . $customer_id . "
			AND person_uuid = parent_person_uuid
			AND deleted = 'N'
			AND full_name = '" . addslashes($full_name) . "'
			AND full_address = '" . addslashes($full_address_applicant) . "'";
			echo $sql . "\r\n\r\n<br><br>";
			$stmt = DB::run($sql);
			$rolodex = $stmt->fetchObject();
			
			$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $header_start_time), 4);
		echo " => QUERY completed in " . $total_time . "<br /><br />";
		
			$blnRolodex = false;
			if (is_object($rolodex)) {
				$parent_table_uuid = $rolodex->person_uuid;
				$blnRolodex = true;
			}
			if (!$blnRolodex) {
				//insert the parent record first
				$sql_applicant = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_person` (`person_uuid`, `customer_id`, `parent_person_uuid`, `full_name`, `first_name`, `middle_name`, `last_name`, `aka`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `email`, `fax`, `work_phone`, `cell_phone`, `ssn`, `ssn_last_four`, `dob`, `age`, `license_number`, `salutation`, `gender`, `language`, `birth_state`, `birth_city`, `marital_status`, `spouse`, `spouse_contact`, `emergency`, `last_updated_date`, `last_update_user`, `deleted`) 
				VALUES('" . $parent_table_uuid . "', '" . $customer_id . "', '" . $parent_table_uuid . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N')";
				
				$stmt = $db->prepare($sql_applicant);  
				echo $sql_applicant . "\r\n\r\n<br><br>"; 
				$stmt->execute();
			}
			$sql_applicant = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_person` (`person_uuid`, `customer_id`, `parent_person_uuid`, `full_name`, `first_name`, `middle_name`, `last_name`, `aka`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `email`, `fax`, `work_phone`, `cell_phone`, `ssn`, `ssn_last_four`, `dob`, `age`, `license_number`, `salutation`, `gender`, `language`, `birth_state`, `birth_city`, `marital_status`, `spouse`, `spouse_contact`, `emergency`, `last_updated_date`, `last_update_user`, `deleted`) 
			VALUES('" . $table_uuid . "', '" . $customer_id . "', '" . $parent_table_uuid . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N')";
			
			$stmt = $db->prepare($sql_applicant);  
			echo $sql_applicant . "\r\n\r\n<br><br>"; 
			//die();
			$stmt->execute();
			
			$case_table_uuid = uniqid("CA", false);
			//attach applicant to kase
			$sql_applicant = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_person` (`case_person_uuid`, `case_uuid`, `person_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', 'main', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
			
			$stmt = DB::run($sql_applicant);
			echo $sql_applicant . "\r\n\r\n<br><br>"; 
			
			//insert the employer
			$table_uuid = uniqid("KS", false);
			$parent_table_uuid = uniqid("RD", false);
			
			$full_address = $injury->emp_add1;
			if ($injury->emp_add2!="") {
				$full_address .= ", " . $injury->emp_add2;
			}
			$full_address .= ", " . $injury->emp_city;
			$full_address .= ", " . $injury->emp_state;
			$full_address .= " " . $injury->emp_zip;
			//die(print_r($injury));
			if ($injury->emp_firm!="") {
				$arrSet = array();
				$arrSet[] = addslashes($injury->supervisor);
				$arrSet[] = addslashes($injury->emp_firm);
				$arrSet[] = "employer";
				$arrSet[] = addslashes($full_address);
				$arrSet[] = addslashes($injury->emp_add1);
				$arrSet[] = addslashes($injury->emp_city);
				$arrSet[] = $injury->emp_state;
				$arrSet[] = $injury->emp_zip;
				$arrSet[] = addslashes($injury->emp_add2);
				if ($injury->emp_ext=="") {
					$arrSet[] = addslashes($injury->emp_tel);
				} else {
					$arrSet[] = addslashes($injury->emp_tel . " " . $injury->emp_ext);
				}
				$arrSet[] = addslashes($injury->emp_email);
				$arrSet[] = addslashes($injury->emp_salutation);
				
				//look up in case already in
				$sql_employer = "SELECT corporation_uuid
				FROM `" . $data_source . "`.`" . $data_source . "_corporation`
				WHERE customer_id = " . $customer_id . "
				AND corporation_uuid = parent_corporation_uuid
				AND type = 'employer'
				AND deleted = 'N'
				AND `company_name` = '" . addslashes($injury->emp_firm) . "'
				AND `full_address` = '" . addslashes($full_address) . "'";
				
				$stmt = DB::run($sql_employer);
				$employer = $stmt->fetchObject();
				
				$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $header_start_time), 4);
		echo " => QUERY completed in " . $total_time . "<br /><br />";
		
				if (is_object($employer)) {
					$parent_table_uuid = $employer->corporation_uuid;
				}
				if (!is_object($employer)) {
					//insert the parent record first
					$sql_employer = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation` (`corporation_uuid`, `customer_id`, `full_name`, `company_name`, `type`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `email`, `salutation`, `last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
							VALUES('" . $parent_table_uuid . "', '" . $customer_id . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N', '" . $parent_table_uuid . "', '')";
							
					$stmt = DB::run($sql_employer);
					echo $sql_employer . "\r\n\r\n<br><br>"; 
				}
				$sql_employer = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation` (`corporation_uuid`, `customer_id`, `full_name`, `company_name`, `type`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `email`, `salutation`, `last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`)  
						VALUES('" . $table_uuid . "', '" . $customer_id . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N', '" . $parent_table_uuid . "', '')";
						
				$stmt = DB::run($sql_employer);
				echo $sql_employer . "\r\n\r\n<br><br>";  
				//die();
				$case_table_uuid = uniqid("KA", false);
				//now we have to attach the employer to the case 
				$sql_employer = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_corporation` (`case_corporation_uuid`, `case_uuid`, `corporation_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', 'employer', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
						
				$stmt = $db->prepare($sql_employer);
				echo $sql_employer . "\r\n\r\n<br><br>";   
				$stmt->execute();
			}
			
			//referral source
			//insert the referral
			$table_uuid = uniqid("KS", false);
			$parent_table_uuid = uniqid("RD", false);
			
			$full_address = $injury->referral_add1;
			if ($injury->referral_add2!="") {
				$full_address .= ", " . $injury->referral_add2;
			}
			$full_address .= ", " . $injury->referral_city;
			$full_address .= ", " . $injury->referral_state;
			$full_address .= " " . $injury->referral_zip;
			
			if ($injury->referral_first . $injury->referral_last!="" && $injury->referral_firm=="") {
				$injury->referral_firm = $injury->referral_first . " " . $injury->referral_last;
			}
			if ($injury->referral_firm!="") {
				$arrSet = array();
				$arrSet[] = addslashes($injury->referral_first . " " . $injury->referral_last);
				$arrSet[] = addslashes($injury->referral_firm);
				$arrSet[] = "referring";
				$arrSet[] = addslashes($full_address);
				$arrSet[] = addslashes($injury->referral_add1);
				$arrSet[] = addslashes($injury->referral_city);
				$arrSet[] = $injury->referral_state;
				$arrSet[] = $injury->referral_zip;
				$arrSet[] = addslashes($injury->referral_add2);
				if ($injury->referral_ext=="") {
					$arrSet[] = addslashes($injury->referral_tel);
				} else {
					$arrSet[] = addslashes($injury->referral_tel . " " . $injury->referral_ext);
				}
				$arrSet[] = addslashes($injury->referral_email);
				$arrSet[] = addslashes($injury->referral_salutation);
				
				//look up in case already in
				$sql = "SELECT corporation_uuid
				FROM `" . $data_source . "`.`" . $data_source . "_corporation`
				WHERE customer_id = " . $customer_id . "
				AND corporation_uuid = parent_corporation_uuid
				AND type = 'referring'
				AND deleted = 'N'
				AND company_name = '" . addslashes($injury->referral_firm) . "'
				AND full_address = '" . addslashes($full_address) . "'";
				
				$stmt = DB::run($sql);
				$partie = $stmt->fetchObject();
				
				$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $header_start_time), 4);
		echo " => QUERY completed in " . $total_time . "<br /><br />";
		
				if (is_object($partie)) {
					$parent_table_uuid = $partie->corporation_uuid;
				}
				if (!is_object($partie)) {
					//insert the parent record first
					$sql_referral = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation` (`corporation_uuid`, `customer_id`, `full_name`, `company_name`, `type`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `email`, `salutation`, `last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
							VALUES('" . $parent_table_uuid . "', '" . $customer_id . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N', '" . $parent_table_uuid . "', '')";
							
					$stmt = $db->prepare($sql_referral);  
					echo $sql_referral . "\r\n\r\n<br><br>"; 
					$stmt->execute();
				}
				$sql_referral = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation` (`corporation_uuid`, `customer_id`, `full_name`, `company_name`, `type`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `email`, `salutation`, `last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`)  
						VALUES('" . $table_uuid . "', '" . $customer_id . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N', '" . $parent_table_uuid . "', '')";
						
				$stmt = $db->prepare($sql_referral); 
				echo $sql_referral . "\r\n\r\n<br><br>";  
				$stmt->execute();
				
				$case_table_uuid = uniqid("KA", false);
				$attribute_1 = "main";
				//now we have to attach the referral to the case 
				$sql_referral = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_corporation` (`case_corporation_uuid`, `case_uuid`, `corporation_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', 'referring', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
						
				$stmt = $db->prepare($sql_referral);
				echo $sql_referral . "\r\n\r\n<br><br>";   
				$stmt->execute();
			}
			
			//look up the venue and then add it as a partie
			$venue_abbr = $injury->venue_abbr;
			$courtjudge = $injury->courtjudge;
			if ($venue_abbr!="") {
				//venue
				$parent_table_uuid = array_search($venue_abbr, $arrVenues);
				//die("venue_abbr:" . $venue_abbr . " - " . $parent_table_uuid);
				//now we have to attach the venue to the case
				$case_venue_uuid = uniqid("KS", false);
				$last_updated_date = date("Y-m-d H:i:s");
				
				$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_venue` (`case_venue_uuid`, `case_uuid`, `venue_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $case_venue_uuid  . "', '" . $case_uuid . "', '" . $parent_table_uuid . "', 'main', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
				echo $sql . "\r\n\r\n<br><br>";
				$stmt = DB::run($sql);
				
				$table_uuid = uniqid("VN", false);
				//now save the venue as corporation for parties
				$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation`(`corporation_uuid`, `parent_corporation_uuid`, `company_name`, `type`, `aka`, `employee_phone`, `full_name`, `full_address`, `street`, `city`, `state`, `zip`, `salutation`, `copying_instructions`, `customer_id`) 
				SELECT '" . $table_uuid . "', '" . $parent_table_uuid . "', `venue`, 'venue', `venue_abbr`, `phone`, '" . addslashes($courtjudge) . "', CONCAT(`address1`, ',', `address2`,',', `city`,' ', `zip`) full_address, CONCAT(`address1`,',', `address2`) street, `city`,'CA', `zip`, 'Your Honor', '', " . $customer_id . " 
				FROM `ikase`.`cse_venue`
				WHERE venue_uuid = '" . $parent_table_uuid . "'";
				echo $sql . "\r\n\r\n<br><br>";
				$stmt = DB::run($sql);
				
				$table_name = "corporation";
				$case_table_uuid = uniqid("KA", false);
				$attribute_1 = "main";
				$last_updated_date = date("Y-m-d H:i:s");
				$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_" . $table_name . "` (`case_" . $table_name . "_uuid`, `case_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', 'venue', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";						
				echo $sql . "\r\n\r\n<br><br>";
				$stmt = DB::run($sql);
			}
			
			//carriers
			$sql_carrier = "SELECT
			ins.inspointer, insr.`iname`, insr.`iadd1`, insr.`iadd2`, insr.`icity`, 
			insr.`ist`, insr.`izip`, insr.`itel`, insr.`iext`, insr.`ifax`, 
			insr.`iemail`, insr.`imemo`, insr.`ipointer`, insr.`recno`, 
			insr.`recno2`, insr.`recno3`, insr.`linked`, 
			insr.`searchkey`, insr.`linkpnt`,  
			insr.`clientid`, insr.`billrates`, 
			ins.iadj, ins.itel adjtel, 
			ins.iext adjext, ins.ifax adjfax, ins.iemail adjemail, ins.ipolicyno, 
			ins.iclaimno, ins.isalut
			FROM `" . $data_source . "`.`ins`
			INNER JOIN `" . $data_source . "`.`client` cli
			ON ins.inspointer = cli.ipoint
			LEFT OUTER JOIN `" . $data_source . "`.`insure` insr 
			ON ins.ipointer = insr.ipointer
			WHERE  cli.cpointer = " . $injury->cpointer . "
			AND cli.ipoint > 0";
			//SOME OLD DBS DON'T HAVE THESE FIELDS
			//insr.`eamsno`, `insr`.`comppi`, 
			$stmt = $db->prepare($sql_carrier);
			echo $sql_carrier . "\r\n\r\n<br><br>";
			//die();
			$stmt->execute();
			$carriers = $stmt->fetchAll(PDO::FETCH_OBJ);
			
			$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $header_start_time), 4);
		echo " => QUERY completed in " . $total_time . "<br /><br />";
			
			foreach ($carriers as $carrier) {
				$table_uuid = uniqid("DR", false);
				$parent_table_uuid = uniqid("PD", false);
	
				//carrier
				$table_uuid = uniqid("KR", false);
				$parent_table_uuid = uniqid("CR", false);
				
				$full_address = $carrier->iadd1;
				if ($carrier->iadd2!="") {
					$full_address .= ", " . $carrier->iadd2;
				}
				$full_address .= ", " . $carrier->icity;
				$full_address .= ", " . $carrier->ist;
				$full_address .= " " . $carrier->izip;
				
				if ($carrier->iname!="") {
					$arrSet = array();
					$arrSet[] = addslashes($carrier->iadj);
					$arrSet[] = addslashes($carrier->iname);
					$arrSet[] = "carrier";
					
					$arrSet[] = addslashes($carrier->adjtel);
					$arrSet[] = addslashes($carrier->adjfax);
					$arrSet[] = addslashes($carrier->adjemail);
					$arrSet[] = addslashes($carrier->isalut);
					
					$arrSet[] = addslashes($full_address);
					$arrSet[] = addslashes($carrier->iadd1);
					$arrSet[] = addslashes($carrier->icity);
					$arrSet[] = $carrier->ist;
					$arrSet[] = $carrier->izip;
					$arrSet[] = addslashes($carrier->iadd2);
					if ($carrier->iext=="") {
						$arrSet[] = addslashes($carrier->itel);
					} else {
						$arrSet[] = addslashes($carrier->itel . " " . $carrier->iext);
					}
					$arrSet[] = addslashes($carrier->ifax);
					$arrSet[] = addslashes($carrier->iemail);
					
					//look up in case already in
					$sql = "SELECT corporation_uuid
					FROM `" . $data_source . "`.`" . $data_source . "_corporation`
					WHERE customer_id = " . $customer_id . "
					AND corporation_uuid = parent_corporation_uuid
					AND type = 'carrier'
					AND deleted = 'N'
					AND company_name = '" . addslashes($carrier->iname) . "'
					AND full_address = '" . addslashes($full_address) . "'";
					
					$stmt = DB::run($sql);
					$partie = $stmt->fetchObject();
					
					$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $header_start_time), 4);
		echo " => QUERY completed in " . $total_time . "<br /><br />";
		
					if (is_object($partie)) {
						$parent_table_uuid = $partie->corporation_uuid;
					}
					if (!is_object($partie)) {					
						//insert the parent record first
						$sql_carrier = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation` (`corporation_uuid`, `customer_id`, `full_name`, `company_name`, `type`, `employee_phone`, `employee_fax`, `employee_email`, `salutation`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `fax`, `email`, `last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
						VALUES('" . $parent_table_uuid . "', '" . $customer_id . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N', '" . $parent_table_uuid . "', '')";
						
						$stmt = $db->prepare($sql_carrier);  
						echo $sql_carrier . "\r\n\r\n<br><br>"; 
						$stmt->execute();
					}
					$sql_carrier = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation` (`corporation_uuid`, `customer_id`, `full_name`, `company_name`, `type`, `employee_phone`, `employee_fax`, `employee_email`, `salutation`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `fax`, `email`, `last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
							VALUES('" . $table_uuid . "', '" . $customer_id . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N', '" . $parent_table_uuid . "', '')";
					//`employee_phone`, `employee_fax`, `employee_email`, `salutation`, 
					$stmt = $db->prepare($sql_carrier);  
					echo $sql_carrier . "\r\n\r\n<br><br>"; 
					$stmt->execute();
					
					$case_table_uuid = uniqid("KC", false);
					$attribute_1 = "main";
					//now we have to attach the carrier to the case 
					$sql_carrier = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_corporation` (`case_corporation_uuid`, `case_uuid`, `corporation_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
					VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', 'carrier', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
							
					$stmt = $db->prepare($sql_carrier);  
					echo $sql_carrier . "\r\n\r\n<br><br>"; 
					$stmt->execute();
	
					//die($carrier->ipolicyno . " -- " . $carrier->iclaimno);
					if ($carrier->ipolicyno!="" || $carrier->iclaimno!="") {
						$arrAdhocSet = array();
						if ($carrier->ipolicyno!="") {
							$adhoc_uuid = uniqid("PN", false);
							$arrAdhocSet[] = "'" . $adhoc_uuid . "','" . $case_uuid . "','" . $table_uuid . "','policy_number','" . addslashes($carrier->ipolicyno) . "'";
						}
						if ($carrier->iclaimno!="") {
							$adhoc_uuid = uniqid("CN", false);
							$arrAdhocSet[] = "'" . $adhoc_uuid . "','" . $case_uuid . "','" . $table_uuid . "','claim_number','" . addslashes($carrier->iclaimno) . "'";
						}
						//add these values as adhoc for the carrier
						$adhoc_where_clause = "`corporation_uuid` = '" . $table_uuid . "'";
						//do we have adhocs
						//die(print_r($arrAdhocSet));
						if (count($arrAdhocSet)>0) {
							//inserts
							$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation_adhoc` (`adhoc_uuid`, `case_uuid`, `corporation_uuid`, `adhoc`, `adhoc_value`, `customer_id`) VALUES ";
							$arrValues = array();
							foreach($arrAdhocSet as $adhoc_set) {		
								$arrValues[] = "(" . $adhoc_set . ", '" . $customer_id . "')"; 
							}
							$sql .= implode(",\r\n", $arrValues);
							DB::run($sql);
							//$track_adhock_id = DB::lastInsertId();
							//trackAdhoc("insert", $track_adhock_id);
						}
					}
				}
			}
			//opposing attorney
			$sql_opposing = "SELECT opp.attorney opp_attorney, oppd.firm opp_firm, 
			oppd.add1 opp_add1, oppd.add2 opp_add2, oppd.city opp_city, oppd.state opp_state, 
			oppd.zip opp_zip, opp.telephone opp_tel, opp.fax opp_fax, 
			opp.email opp_email, opp.salutation opp_salutation
			FROM `" . $data_source . "`.`opposing` opp 
			INNER JOIN `" . $data_source . "`.`client` `cli` 
			ON cli.oppospnt = opp.opppointer
			LEFT OUTER JOIN `" . $data_source . "`.`oppdata` oppd 
			ON opp.datapoint = oppd.datapoint
			WHERE `cli`.cpointer = " . $injury->cpointer . "
			AND cli.oppospnt > 0";
			$stmt = $db->prepare($sql_opposing);
			echo $sql_opposing . "\r\n\r\n<br><br>";
			//die();
			$stmt->execute();
			$opposings = $stmt->fetchAll(PDO::FETCH_OBJ);
			
			$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $header_start_time), 4);
		echo " => QUERY completed in " . $total_time . "<br /><br />";
		
			//die(print_r($opposings));
			foreach ($opposings as $opposing) {
				//opposing
				$table_uuid = uniqid("OP", false);
				$parent_table_uuid = uniqid("PR", false);
				
				$full_address = $opposing->opp_add1;
				if ($opposing->opp_add2!="") {
					$full_address .= ", " . $opposing->opp_add2;
				}
				$full_address .= ", " . $opposing->opp_city;
				$full_address .= ", " . $opposing->opp_state;
				$full_address .= " " . $opposing->opp_zip;
				if ($opposing->opp_firm!="") {
					$arrSet = array();
					$arrSet[] = addslashes($opposing->opp_attorney);
					$arrSet[] = addslashes($opposing->opp_firm);
					$arrSet[] = "defense";
					$arrSet[] = addslashes($opposing->adjtel);
					$arrSet[] = addslashes($opposing->adjfax);
					$arrSet[] = addslashes($opposing->adjemail);
					$arrSet[] = addslashes($full_address);
					$arrSet[] = addslashes($opposing->opp_add1);
					$arrSet[] = addslashes($opposing->opp_city);
					$arrSet[] = $opposing->opp_state;
					$arrSet[] = $opposing->opp_zip;
					$arrSet[] = addslashes($opposing->opp_add2);
					if ($opposing->opp_ext=="") {
						$arrSet[] = addslashes($opposing->opp_tel);
					} else {
						$arrSet[] = addslashes($opposing->opp_tel . " " . $opposing->opp_ext);
					}
					$arrSet[] = addslashes($opposing->opp_fax);
					$arrSet[] = addslashes($opposing->opp_email);
					$arrSet[] = addslashes($opposing->opp_salut);
					
					//look up in case already in
					$sql = "SELECT corporation_uuid
					FROM `" . $data_source . "`.`" . $data_source . "_corporation`
					WHERE customer_id = " . $customer_id . "
					AND corporation_uuid = parent_corporation_uuid
					AND type = 'defense'
					AND deleted = 'N'
					AND company_name = '" . addslashes($opposing->opp_firm) . "'
					AND full_address = '" . addslashes($full_address) . "'";
					
					$stmt = DB::run($sql);
					$partie = $stmt->fetchObject();
					
					$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $header_start_time), 4);
		echo " => QUERY completed in " . $total_time . "<br /><br />";
		
					if (is_object($partie)) {
						$parent_table_uuid = $partie->corporation_uuid;
					}
					if (!is_object($partie)) {				
						//insert the parent record first
						$sql_opposing = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation` (`corporation_uuid`, `customer_id`, `full_name`, `company_name`, `type`, `employee_phone`, `employee_fax`, `employee_email`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `fax`, `email`, `salutation`, `last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
						VALUES('" . $parent_table_uuid . "', '" . $customer_id . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N', '" . $parent_table_uuid . "','')";
								
						$stmt = $db->prepare($sql_opposing); 
						echo $sql_opposing . "\r\n\r\n<br><br>";  
						$stmt->execute();
					}
					$sql_opposing = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation` (`corporation_uuid`, `customer_id`, `full_name`, `company_name`, `type`, `employee_phone`, `employee_fax`, `employee_email`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `fax`, `email`, `salutation`, `last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
					VALUES('" . $table_uuid . "', '" . $customer_id . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N', '" . $parent_table_uuid . "','')";
							
					$stmt = $db->prepare($sql_opposing);  
					echo $sql_opposing . "\r\n\r\n<br><br>";
					$stmt->execute();
					
					$case_table_uuid = uniqid("OA", false);
					$attribute_1 = "main";
					//now we have to attach the opposing to the case 
					$sql_opposing = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_corporation` (`case_corporation_uuid`, `case_uuid`, `corporation_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
					VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', 'defense', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
							
					$stmt = $db->prepare($sql_opposing);  
					echo $sql_opposing . "\r\n\r\n<br><br>";
					$stmt->execute();
				}
			}
			
			//doctors
			$sql_medical = "SELECT med.medpnt, `clinicname` `medname`, IFNULL(`med`.`drname`, '') `drname`, 
			med.medtel, 
			med.medext, med.medfax, med.medsalut, med.medmemo, clin.clinicadd1, clin.clinicadd2, 
			clin.cliniccity, clinicst, cliniczip, clinictel, clinicext, clinicfax, clinicemai clinicemail
			FROM `" . $data_source . "`.`medicals` med
			INNER JOIN `" . $data_source . "`.`clinics` `clin` 
			ON `med`.medpnt = clin.clinicpnt
			WHERE med.mpointer = " . $injury->cpointer;
			$stmt = $db->prepare($sql_medical);
			echo $sql_medical . "\r\n\r\n<br><br>";
			//die();
			$stmt->execute();
			$medicals = $stmt->fetchAll(PDO::FETCH_OBJ);
			
			$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $header_start_time), 4);
		echo " => QUERY completed in " . $total_time . "<br /><br />";
		
	
			foreach ($medicals as $medical) {
				$table_uuid = uniqid("DR", false);
				$parent_table_uuid = uniqid("PD", false);
	
				if ($medical->medname!=""){
					$arrSet = array();
					$arrSet[] = addslashes($medical->drname);
					$arrSet[] = addslashes($medical->medname);
					$arrSet[] = "medical_provider";
					$full_address_medical = $medical->clinicadd1;
					if ($medical->clinicadd2!="") {
						$full_address_medical .= ", " . $medical->clinicadd2;
					}
					$full_address_medical .= ", " . $medical->cliniccity;
					$full_address_medical .= ", " . $medical->clinicst;
					$full_address_medical .= " " . $medical->cliniczip;
					
					$arrSet[] = addslashes($full_address_medical);
					$arrSet[] = addslashes($medical->clinicadd1);
					$arrSet[] = addslashes($medical->cliniccity);
					$arrSet[] = $medical->clinicst;
					$arrSet[] = $medical->cliniczip;
					$arrSet[] = addslashes($medical->clinicadd2);
					
					if ($medical->clinicext=="") {
						$arrSet[] = addslashes($medical->clinictel);
					} else {
						$arrSet[] = addslashes($medical->clinictel . " " . $medical->clinicext);
					}
					$arrSet[] = addslashes($medical->clinicfax);
					$arrSet[] = addslashes($medical->clinicemail);
					
					if ($medical->medext=="") {
						$arrSet[] = addslashes($medical->medtel);
					} else {
						$arrSet[] = addslashes($medical->medtel . " " . $medical->medext);
					}
					$arrSet[] = addslashes($medical->medfax);
					$arrSet[] = addslashes($medical->medemail);
					
					$arrSet[] = addslashes($medical->medsalut);
					
					//look up in case already in
					$sql = "SELECT corporation_uuid
					FROM `" . $data_source . "`.`" . $data_source . "_corporation`
					WHERE customer_id = " . $customer_id . "
					AND corporation_uuid = parent_corporation_uuid
					AND type = 'medical_provider'
					AND deleted = 'N'
					AND company_name = '" . addslashes($medical->medname) . "'
					AND full_address = '" . addslashes($full_address) . "'";
					
					echo $sql . "\r\n\r\n<br><br>";
					
					$stmt = DB::run($sql);
					$partie = $stmt->fetchObject();
					
					$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $header_start_time), 4);
		echo " => QUERY completed in " . $total_time . "<br /><br />";
		
					if (is_object($partie)) {
						$parent_table_uuid = $partie->corporation_uuid;
					}
					if (!is_object($partie)) {
						//insert the parent record first
						$sql_medical = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation` (`corporation_uuid`, `customer_id`, `full_name`, `company_name`, `type`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `fax`, `email`, `employee_phone`, `employee_fax`, `employee_email`, `salutation`, `last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
						VALUES('" . $parent_table_uuid . "', '" . $customer_id . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N', '" . $parent_table_uuid . "','')";
								
						$stmt = $db->prepare($sql_medical); 
						echo $sql_medical . "\r\n\r\n<br><br>"; 
						$stmt->execute();
					}
					
					$sql_medical = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation` (`corporation_uuid`, `customer_id`, `full_name`, `company_name`, `type`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `fax`, `email`, `employee_phone`, `employee_fax`, `employee_email`, `salutation`, `last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
					VALUES('" . $table_uuid . "', '" . $customer_id . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N', '" . $parent_table_uuid . "', '')";
							
					$stmt = $db->prepare($sql_medical);  
					echo $sql_medical . "\r\n\r\n<br><br>"; 
					$stmt->execute();
					
					$case_table_uuid = uniqid("OA", false);
					$attribute_1 = "main";
					//now we have to attach the doctor to the case 
					$sql_medical = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_corporation` (`case_corporation_uuid`, `case_uuid`, `corporation_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
					VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', 'medical_provider', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
							
					$stmt = $db->prepare($sql_medical); 
					echo $sql_medical . "\r\n\r\n<br><br>";  
					$stmt->execute();
					
					$sql_exam = "SELECT DISTINCT `medicals`.mpointer, `medsum` . provider, 
					`medsum` . specialty, STR_TO_DATE(`medsum`.`report`,  '%m/%d/%Y' ) examdate,
					`medsum` . exam, `medsum` . `status`, `medsum` . `reqby`, 
					`medsum` . `comments`, `medsum` . `examtype`,
					`medsum` . `fandsdate`
					FROM `" . $data_source . "`.`medicals`
					INNER JOIN `" . $data_source . "`.`medsum` 
					ON `medicals`.mpointer = `medsum`.medsumpnt
					INNER JOIN `" . $data_source . "`.`clinics` clin
					ON `medicals`.medpnt = clin.clinicpnt
					WHERE `medicals`.mpointer = " . $injury->cpointer . "
					AND `clin`.clinicname = `medsum`.`provider`
					AND `medsum`.`provider` = '" . addslashes($medical->medname) . "'
					ORDER BY `medsum`.`exam` ASC";
					
					$stmt = $db->prepare($sql_exam);
					echo $sql_exam . "\r\n\r\n<br><br>";  
					$stmt = $db->query($sql_exam);
					$exams = $stmt->fetchAll(PDO::FETCH_OBJ);
					
					$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $header_start_time), 4);
		echo " => QUERY completed in " . $total_time . "<br /><br />";
		
					
					foreach($exams as $exam) {
						//die(print_r($exam));
						//link medical provider to exam
						$medical_table_uuid = uniqid("MS", false);
						$exam_uuid = uniqid("EX", false);
						$attribute_1 = "main";
						//now we have to attach the doctor to the case 
						$sql_exam = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation_exam` (`corporation_exam_uuid`, `corporation_uuid`, `exam_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
						VALUES ('" . $case_table_uuid  ."', '" . $table_uuid . "', '" . $exam_uuid . "', 'main', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
								
						$stmt = $db->prepare($sql_exam); 
						echo $sql_exam . "\r\n\r\n<br><br>";  
						$stmt->execute();
						//add exam itself
						$examdate = $exam->examdate;
						if ($examdate=="" || $examdate=='  /  /') {
							$examdate = "0000-00-00 00:00:00";
						}
						$fandsdate = $exam->fandsdate;
						if ($fandsdate=="") {
							$fandsdate = "0000-00-00 00:00:00";
						} else {
							$fandsdate = date("Y-m-d");
							if (date("Y", strtotime($fandsdate)) < 1970) {
								$fandsdate = "0000-00-00";
							}
						}
						$pands = 'N';
						if ($exam->pands==1) {
							//rare
							$pands = 'Y';
						}
						$sql_exam = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_exam`
	(`exam_uuid`, `exam_dateandtime`, `exam_status`, `exam_type`, `specialty`, `requestor`, `comments`, `permanent_stationary`, `fs_date`, `customer_id`)
	VALUES( '" . $exam_uuid . "', '" . $examdate . "', '" . addslashes($exam->status) . "', '" . addslashes($exam->examtype) . "', '" . addslashes($exam->specialty) . "', '" . addslashes($exam->reqby) . "', '" . addslashes($exam->comments) . "', '" . $pands . "', '" . $fandsdate . "', " . $customer_id . ");";
						$stmt = $db->prepare($sql_exam); 
						echo $sql_exam . "\r\n\r\n<br><br>";  
						$stmt->execute();
					}
				}
			}
			//court reporting
			//SELECT * FROM reino.parties WHERE cpointer =
			
			/*
			UPDATE reino.parties
			SET partytype = 'Sub-Out Attorney'
			WHERE partytype LIKE 'Sub%'
			
			UPDATE reino.parties
			SET partytype = '132A Attorney'
			WHERE partytype LIKE '132%'
			
			UPDATE reino.parties
			SET partytype = 'Court Reporting'
			WHERE partytype LIKE '%Court%'
			
			UPDATE reino.parties
			SET partytype = 'Interpreter'
			WHERE partytype LIKE '%Interpret%'
			
			UPDATE reino.parties
			SET partytype = 'New Appt Atty'
			WHERE partytype LIKE 'New App%'
			OR partytype LIKE 'New opp%'
			*/
			$time = microtime();
			$time = explode(' ', $time);
			$time = $time[1] + $time[0];
			$finish_time = $time;
			$total_time = round(($finish_time - $process_start_time), 4);
			echo " => row completed in " . $total_time . "
			
			<br /><br />"; 
			
			//forms
			//SELECT * FROM reino.wcab2008 WHERE cpointer = '1850'
		}
		
		$sql = "UPDATE `" . $data_source . "`.`missings` 
		SET processed = 'Y'
		WHERE cpointer = '" . $case->cpointer . "'";
		echo $sql . "\r\n\r\n<br><br>";
		$stmt = DB::run($sql);
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $header_start_time), 4);
		
		//$success = array("success"=> array("text"=>"done", "duration"=>$total_time));
		//completeds
		$sql = "SELECT COUNT(*) case_count
		FROM `" . $data_source . "`.`missings` gcase
		WHERE 1";
		echo $sql . "\r\n<br>";
		//die();
		$stmt = DB::run($sql);
		$cases = $stmt->fetchObject();
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $header_start_time), 4);
		echo " => QUERY completed in " . $total_time . "<br /><br />"; 
		
		$case_count = $cases->case_count;
		
		//completeds
		$sql = "SELECT COUNT(cpointer) case_count
		FROM `" . $data_source . "`.`missings` ggc
		WHERE processed = 'Y'";
		echo $sql . "\r\n<br>";
		//die();
		$stmt = DB::run($sql);
		$cases = $stmt->fetchObject();
		
		$completed_count = $cases->case_count;
	
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $header_start_time), 4);
		
		$success = array("success"=> array("text"=>"done", "duration"=>$total_time, "completed_count"=>$completed_count, "case_count"=>$case_count));
		
		echo $total_time . "<br />";
		//echo json_encode($success);
		if ($total_time > 5) {
			//die("too long");
		}
		if (count($cases) > 0) {
			//die("script language='javascript'>parent.runMain(" . $completed_count . "," . $case_count . ")</script");
			echo "<script language='javascript'>parent.runMissings(" . $completed_count . "," . $case_count . ")</script>";
		}
	}
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}

//include("cls_logging.php");


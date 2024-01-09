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
$customer_id = passed_var("customer_id", "get");
if (!is_numeric($customer_id)) {
	die();
} 

//WHERE cli.fileno = 1061
//die($sql);
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
	
	$sql_truncate = "TRUNCATE `" . $data_source . "`." . $data_source . "_injury; TRUNCATE `" . $data_source . "`." . $data_source . "_case_injury; TRUNCATE `" . $data_source . "`." . $data_source . "_injury; TRUNCATE `" . $data_source . "`." . $data_source . "_injury_number; TRUNCATE `" . $data_source . "`." . $data_source . "_injury_injury_number; TRUNCATE `" . $data_source . "`." . $data_source . "_case; TRUNCATE `" . $data_source . "`." . $data_source . "_corporation; TRUNCATE `" . $data_source . "`." . $data_source . "_case_corporation; TRUNCATE `" . $data_source . "`." . $data_source . "_person; TRUNCATE `" . $data_source . "`." . $data_source . "_case_person; TRUNCATE `" . $data_source . "`.`" . $data_source . "_notes`; TRUNCATE `" . $data_source . "`.`" . $data_source . "_case_notes`;";
	$stmt = DB::run($sql_truncate);
	//die($sql_truncate);
	
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
	
	$sql = "SELECT 
	ins.inspointer, insr.`iname`, insr.`iadd1`, insr.`iadd2`, insr.`icity`, insr.`ist`, insr.`izip`, insr.`itel`, insr.`iext`, insr.`ifax`, insr.`iemail`, insr.`imemo`, insr.`ipointer`, insr.`recno`, insr.`recno2`, insr.`recno3`, insr.`linked`, insr.`visible`, insr.`visundo`, insr.`searchkey`, insr.`linkpnt`, insr.`defclient`, insr.`plclient`, insr.`clientid`, insr.`billrates`, insr.`inactive`, insr.`eamsno`,
    `insr`.`comppi`,
	`cli`.`lastname`, `cli`.`firstname`, `cli`.`midname`, `cli`.`add1`, `cli`.`add2`, `cli`.`city`, `cli`.`clientst`, `cli`.`clientzip`, `cli`.`clisalut`, `cli`.`marriagest`, `cli`.`clientdob`, `cli`.`clientss`, `cli`.`clienttel`, `cli`.`clientext`, `cli`.`clientoff`, `cli`.`offext`, `cli`.`clientothe`, `cli`.`othext`, `cli`.`casestat`, `cli`.`casetype`, `cli`.`fileno`, `cli`.`opendate`, `cli`.`filedate`, `cli`.`casestate`, `cli`.`caseoption`, `cli`.`caseno`, `cli`.`casename`, `cli`.`statoveride`, `cli`.`email`, `cli`.`workcode`, `cli`.`attycode`, `cli`.`priority`, `cli`.`language`, `cli`.`accipoint`, `cli`.`ipoint`, `cli`.`otherp`, `cli`.`dpnt`, `cli`.`proppnt`, `cli`.`ctpnt`, `cli`.`ppnt`, `cli`.`oppospnt`, `cli`.`pattypnt`, `cli`.`assocpnt`, `cli`.`defmedpnt`, `cli`.`wpointer`, `cli`.`refsource`, `cli`.`expertpnt`, `cli`.`negpoint`, `cli`.`cmemo`, `cli`.`cpointer`, `cli`.`occup`, `cli`.`demoflag`, `cli`.`intakedate`, `cli`.`litpoint`, `cli`.`drivlic`, `cli`.`trakker`, `cli`.`sex`, `cli`.`docpath`, `cli`.`closed`, `cli`.`lastadate`, `cli`.`lastadesc`, `cli`.`nextadate`, `cli`.`nextadesc`, `cli`.`clientdod`, `cli`.`persrep`, `cli`.`spouse`, `cli`.`pifiled`, `cli`.`picaseno`, `cli`.`wdfiled`, `cli`.`wdcaseno`, `cli`.`local`, `cli`.`relation`, `cli`.`smoker`, `cli`.`disease`, `cli`.`picasetype`, `cli`.`wdcasetype`, `cli`.`subcat1`, `cli`.`subcat2`, `cli`.`reffeepct`, `cli`.`attyfeepct`, `cli`.`deathcase`, `cli`.`demandamt`, `cli`.`dodappfile`, `cli`.`dodcause`, `cli`.`autopsy`, `cli`.`autopsydat`, `cli`.`burialexp`, `cli`.`ssreq`, `cli`.`ssrcvd`, `cli`.`sssenta`, `cli`.`ssrcvda`, `cli`.`packcompl`, `cli`.`packsent`, `cli`.`packamend`, `cli`.`company`, `cli`.`asbstatute`, `cli`.`asboveride`, `cli`.`refpoint`, `cli`.`imported`, `cli`.`remotei`, `cli`.`amount`, `cli`.`spousetel`, `cli`.`spousessno`, `cli`.`persreptel`, `cli`.`ssprefix`, `cli`.`timeuse`, `cli`.`sendbill`, `cli`.`account`, `cli`.`tsfileno`, `cli`.`settled`, `cli`.`statusdate`, `cli`.`subindate`, `cli`.`distlocked`, `cli`.`key`, `cli`.`archived`, `cli`.`diskno`, `cli`.`pointold`, `cli`.`office`, `cli`.`reopened`, `cli`.`finalbill`, `cli`.`exported`, `cli`.`expdate`, `cli`.`expbatch`, `cli`.`newcomplaw`, `cli`.`archivetxt`, `cli`.`taskbased`, `cli`.`dom`, `cli`.`role`, `cli`.`dos`, `cli`.`pob`, `cli`.`norecovery`, `cli`.`costint`, `cli`.`costrate`, `cli`.`costtype`, `cli`.`restricted`, `cli`.`reportdate`, `cli`.`miscdata` , 
	con.`category` con_category, con.`lastname` con_lastname, con.`midname` con_midname, con.`firstname` con_firstname, con.`company` con_company, con.`add1` con_add1, con.`add2` con_add2, con.`city` con_city, con.`state` con_state, con.`zip` con_zip, con.`county` con_county, con.`tel` con_tel, con.`ext` con_ext, con.`fax` con_fax, con.`office` con_office, con.`offext` con_offext, con.`cell` con_cell, con.`other` con_other, con.`othtype`, con.`mailcontac`, con.`salut`, con.`cpointers`, con.`comments`, con.`ssno`, con.`age`, con.`dob`, con.`dobcity`, con.`dobstate`, con.`dobcountry`, con.`ethnicity`, con.`married`, con.`language` con_language, con.`sex` con_sex, con.`spouselast`, con.`spousefirs`, con.`spousemid`, con.`spouseadd1`, con.`spouseadd2`, con.`spousecity`, con.`spousest`, con.`spousezip`, con.`spouseltel`, con.`spousecell`, con.`spemail`, con.`caseinfo`, con.`name`, con.`maillist`, con.`citystzip`, con.`locator`, con.`email` con_email, con.`contlast`, con.`contfirst`, con.`contmid`, con.`contsalut`,
	acc.*, 
	ins.iadj, ins.itel adjtel, ins.iext adjext, ins.ifax adjfax, ins.iemail adjemail, ins.ipolicyno, ins.iclaimno, ins.isalut,
	cij.ctdates, cij.body,
	emp.company emp_firm, emp.add1 emp_add1, emp.add2 emp_add2, emp.city emp_city, emp.state emp_state, emp.zip emp_zip, emp.tel emp_tel, emp.ext emp_ext, emp.email emp_email, emp.salutation emp_salutation, emp.supervisor, opp.attorney opp_attorney, oppd.firm opp_firm, oppd.add1 opp_add1, oppd.add2 opp_add2, oppd.city opp_city, oppd.state opp_state, oppd.zip opp_zip, opp.telephone opp_tel, opp.fax opp_fax, opp.email opp_email, opp.salutation opp_salutation,
	`courts`.`courtname`, `courts`.`courtadd1`, `courts`.`courtadd2`, `courts`.`courtcity`, `courts`.`courtst`, `courts`.`courtzip`, `courts`.`courtjudge`, `courts`.`courttel`, `courts`.`courtext`, `courts`.`courtfax`, `courts`.`courtemail`, courts.searchkey venue_abbr
	FROM `" . $data_source . "`.`client` cli
	LEFT OUTER JOIN `" . $data_source . "`.`contacts` con ON cli.cpointer = con.cpointer
	LEFT OUTER JOIN `" . $data_source . "`.`accident` acc ON cli.accipoint = acc.apointer
	LEFT OUTER JOIN `" . $data_source . "`.`compinj` cij ON cli.cpointer = cij.compinjpnt
	LEFT OUTER JOIN `" . $data_source . "`.`employer` emp ON cli.cpointer = emp.epointer
	LEFT OUTER JOIN `" . $data_source . "`.`ccourts` ccrt ON cli.ctpnt = ccrt.courtpoint
	LEFT OUTER JOIN `" . $data_source . "`.`courts` courts ON ccrt.courtpnt = courts.courtpnt
	LEFT OUTER JOIN `" . $data_source . "`.`opposing` opp ON cli.oppospnt = opp.opppointer
	LEFT OUTER JOIN `" . $data_source . "`.`oppdata` oppd ON opp.datapoint = oppd.datapoint
	LEFT OUTER JOIN `" . $data_source . "`.`ins` ON cli.cpointer = ins.inspointer
	LEFT OUTER JOIN `" . $data_source . "`.`insure` insr ON ins.ipointer = insr.ipointer
	WHERE 1
	AND cli.cpointer = 217
	ORDER BY cli.cpointer, ins.inspointer DESC";
	//	AND cli.cpointer = 1291	LIMIT 0, 10
	$stmt = $db->prepare($sql);
	echo $sql . "\r\n\r\n";
	//die();
	$stmt->execute();
	
	$injuries = $stmt->fetchAll(PDO::FETCH_OBJ);
	//die(print_r($injuries));
	$arrCpointer = array();
	foreach($injuries as $key=>$injury){
		$last_updated_date = date("Y-m-d H:i:s");
		
		if ($key==0) {
		//	continue;
		}
		if (in_array($injury->cpointer, $arrCpointer)) {
			continue;
		}
		echo "Processing -> " . $key. " == " . $injury->cpointer . "\r\n\r\n";
		
		$arrCpointer[] = $injury->cpointer;
		//die(print_r($injury));
		//multiple adjs
		$injury->caseno = str_replace(";", ",", $injury->caseno);
		$injury->caseno = str_replace("&", ",", $injury->caseno);
		$injury->caseno = str_replace(" ", ",", $injury->caseno);
		$arrADJ = explode(",", $injury->caseno);
		//clean up the adj numbers
		foreach($arrADJ as $adj_key=>$adj) {
			if (strlen($adj) <10) {
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
VALUES ('" . $case_uuid . "', '" . $injury->cpointer . "', '" . $injury->fileno . "', '" . addslashes($injury->casename) . "', '" . date("Y-m-d", strtotime($injury->opendate)) . "', 'WCAB', 'LAO', '" . addslashes($injury->casestat) . "', '" . addslashes($injury->subcat1) . "', '" . addslashes($injury->subcat2) . "', '" . date("Y-m-d", strtotime($injury->opendate)) . "', '" . $injury->attycode . "', '" . $injury->workcode . "', " . $customer_id . ")";
		echo $sql . "\r\n\r\n"; 
		$stmt = DB::run($sql);
		
		//cmemo
		//insert as a quick note
		//attach to case
		$case_notes_uuid = uniqid("CN", false);
		$notes_uuid = uniqid("NT", false);
		$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_notes` (`case_notes_uuid`, `case_uuid`,  `notes_counter`, `notes_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
		VALUES ('" . $case_notes_uuid . "', '" . $case_uuid . "', 0, '" . $notes_uuid . "', 'quick', '" . $last_updated_date . "', 'system', 'N', '" . $customer_id . "')";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		echo $sql . "\r\n\r\n";
		
		$stmt->execute();
		if (date("Y", strtotime($injury->opendate)) < 1996) {
			$injury->opendate = date("Y-m-d");
		}
		$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_notes` (`notes_counter`, `notes_uuid`, `note`, `dateandtime`, `entered_by`, `customer_id`, `type`)
		VALUES (0, '" . $case_uuid . "', '" . addslashes($injury->cmemo) . "', '" . date("Y-m-d", strtotime($injury->opendate)) . "', 'system', '" . $customer_id . "', 'quick')";
		
		echo $sql . "\r\n\r\n";
		$stmt = DB::run($sql);
		
		
		$injury_uuid = uniqid("KI", false);
		if (isValidDate($injury->accidate, "m/d/Y")) {
			$injury_accidate = date("Y-m-d", strtotime($injury->accidate));
		} else {
			$injury_accidate = "0000-00-00";
		}
		$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_injury` (`injury_uuid`, `injury_number`, `adj_number`, `type`, `occupation`, `start_date`, `body_parts`, `ctdates`, `explanation`, `full_address`, `street`, `suite`, `city`, `state`, `zip`, `customer_id`, `deleted`)
		VALUES('" . $injury_uuid . "', " . ($injury_number+1) . ", '" . addslashes($injury->caseno) . "', '', '" . addslashes($injury->occup) . "','" . $injury_accidate . "','" . addslashes($injury->body) . "','" . addslashes($injury->ctdates) . "','" . addslashes($injury->accidesc) . "','" . addslashes($full_address) . "', '" . addslashes($injury->acciadd1) . "','" . addslashes($injury->acciadd2) . "', '" . $injury->accicity . "', '" . $injury->accist . "', '" . $injury->accizip . "', " . $customer_id . ", 'N')";
		echo $sql . "\r\n\r\n"; 
		DB::run($sql);
	$injury_id = DB::lastInsertId();
		
		if ($injury_accidate != "0000-00-00") {
			//update `statute_limitation`
			$sql = "UPDATE `" . $data_source . "`.`" . $data_source . "_injury` 
			SET statute_limitation = DATE_ADD(`start_date`, INTERVAL 1 YEAR)
			WHERE injury_id = " . $injury_id;
			
			$stmt = $db->prepare($sql);  
			echo $sql . "\r\n\r\n";
			$stmt->execute();
		}
		//die(print_r($injury));
		if ($injury->ipolicyno!="" || $injury->iclaimno!="") {
			//we need to insert the policy number and attach them to injury
			$table_uuid = uniqid("IN", false);
			$sql_injury_number = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_injury_number` (`injury_number_uuid`, `insurance_policy_number`, `carrier_claim_number`, `deleted`, `customer_id`) 
			VALUES('" . $table_uuid . "', '" . $injury->ipolicyno . "', '" . $injury->iclaimno . "', 'N', " . $customer_id . ")";
			
			echo $sql_injury_number . "\r\n\r\n"; 
			$stmt = DB::run($sql_injury_number);
			//attach to injury
			$injury_table_uuid = uniqid("KA", false);
			$attribute_1 = "main";
			$sql_injury_number = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_injury_injury_number` (`injury_injury_number_uuid`, `injury_uuid`, `injury_number_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $injury_table_uuid  ."', '" . $injury_uuid . "', '" . $table_uuid . "', 'main', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
			
			echo $sql_injury_number . "\r\n\r\n"; 
			$stmt = DB::run($sql_injury_number);
		}
		//now attach to case, even before I create case
		$case_table_uuid = uniqid("KA", false);
		$attribute_1 = "main";
		
		//now we have to attach the injury to the case 
		$sql_injury = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_injury` (`case_injury_uuid`, `case_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $injury_uuid . "', 'main', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";

		echo $sql_injury . "\r\n\r\n";  
		$stmt = DB::run($sql_injury);
		
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
			$arrSet[] = $injury->clienttel;
		} else {
			$arrSet[] = $injury->clienttel . " " . $injury->clientext;
		}
		$arrSet[] = $injury->email;
		$arrSet[] = $injury->con_fax;
		
		if ($injury->offext=="") {
			$arrSet[] = $injury->office;
		} else {
			$arrSet[] = $injury->office . " " . $injury->offext;
		}
		$arrSet[] = $injury->cell;
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
		$arrSet[] = $injury->drivlic;
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
		
		//insert the parent record first
		$sql_applicant = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_person` (`person_uuid`, `customer_id`, `parent_person_uuid`, `full_name`, `first_name`, `middle_name`, `last_name`, `aka`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `email`, `fax`, `work_phone`, `cell_phone`, `ssn`, `ssn_last_four`, `dob`, `age`, `license_number`, `salutation`, `gender`, `language`, `birth_state`, `birth_city`, `marital_status`, `spouse`, `spouse_contact`, `emergency`, `last_updated_date`, `last_update_user`, `deleted`) 
		VALUES('" . $parent_table_uuid . "', '" . $customer_id . "', '" . $parent_table_uuid . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N')";
		
		$stmt = $db->prepare($sql_applicant);  
		echo $sql_applicant . "\r\n\r\n"; 
		
		$stmt->execute();
		
		$sql_applicant = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_person` (`person_uuid`, `customer_id`, `parent_person_uuid`, `full_name`, `first_name`, `middle_name`, `last_name`, `aka`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `email`, `fax`, `work_phone`, `cell_phone`, `ssn`, `ssn_last_four`, `dob`, `age`, `license_number`, `salutation`, `gender`, `language`, `birth_state`, `birth_city`, `marital_status`, `spouse`, `spouse_contact`, `emergency`, `last_updated_date`, `last_update_user`, `deleted`) 
		VALUES('" . $table_uuid . "', '" . $customer_id . "', '" . $parent_table_uuid . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N')";
		
		$stmt = $db->prepare($sql_applicant);  
		echo $sql_applicant . "\r\n\r\n"; 
		//die();
		$stmt->execute();
		
		$case_table_uuid = uniqid("CA", false);
		//attach applicant to kase
		$sql_applicant = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_person` (`case_person_uuid`, `case_uuid`, `person_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', 'main', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
		
		$stmt = $db->prepare($sql_applicant);  
		echo $sql_applicant . "\r\n\r\n"; 
		$stmt->execute();
		
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
				$arrSet[] = $injury->emp_tel;
			} else {
				$arrSet[] = $injury->emp_tel . " " . $injury->emp_ext;
			}
			$arrSet[] = $injury->emp_email;
			$arrSet[] = addslashes($injury->emp_salutation);
			
			//insert the parent record first
			$sql_employer = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation` (`corporation_uuid`, `customer_id`, `full_name`, `company_name`, `type`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `email`, `salutation`, `last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
					VALUES('" . $parent_table_uuid . "', '" . $customer_id . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N', '" . $parent_table_uuid . "', '')";
					
			$stmt = $db->prepare($sql_employer);  
			echo $sql_employer . "\r\n\r\n"; 
			$stmt->execute();
			
			$sql_employer = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation` (`corporation_uuid`, `customer_id`, `full_name`, `company_name`, `type`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `email`, `salutation`, `last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`)  
					VALUES('" . $table_uuid . "', '" . $customer_id . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N', '" . $parent_table_uuid . "', '')";
					
			$stmt = $db->prepare($sql_employer); 
			echo $sql_employer . "\r\n\r\n";  
			$stmt->execute();
			
			$case_table_uuid = uniqid("KA", false);
			$attribute_1 = "main";
			//now we have to attach the employer to the case 
			$sql_employer = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_corporation` (`case_corporation_uuid`, `case_uuid`, `corporation_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', 'employer', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
					
			$stmt = $db->prepare($sql_employer);
			echo $sql_employer . "\r\n\r\n";   
			$stmt->execute();
		}
		//look up the venue and then add it as a partie
		$venue_abbr = $injury->venue_abbr;
		
		if ($venue_abbr!="") {
			//venue
			$parent_table_uuid = array_search($venue_abbr, $arrVenues);
			//die("venue_abbr:" . $venue_abbr . " - " . $parent_table_uuid);
			//now we have to attach the venue to the case
			$case_venue_uuid = uniqid("KS", false);
			$last_updated_date = date("Y-m-d H:i:s");
			
			$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_venue` (`case_venue_uuid`, `case_uuid`, `venue_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_venue_uuid  . "', '" . $case_uuid . "', '" . $parent_table_uuid . "', 'main', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
			echo $sql . "\r\n\r\n";
			$stmt = DB::run($sql);
			
			$table_uuid = uniqid("VN", false);
			//now save the venue as corporation for parties
			$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation`(`corporation_uuid`, `parent_corporation_uuid`, `company_name`, `type`, `aka`, `employee_phone`, `full_name`, `full_address`, `street`, `city`, `state`, `zip`, `salutation`, `copying_instructions`, `customer_id`) 
			SELECT '" . $table_uuid . "', '" . $parent_table_uuid . "', `venue`, 'venue', `venue_abbr`, `phone`, `presiding`, CONCAT(`address1`, ',', `address2`,',', `city`,' ', `zip`) full_address, CONCAT(`address1`,',', `address2`) street, `city`,'CA', `zip`, 'Your Honor', '', " . $customer_id . " 
			FROM `ikase`.`cse_venue`
			WHERE venue_uuid = '" . $parent_table_uuid . "'";
			echo $sql . "\r\n\r\n";
			$stmt = DB::run($sql);
			
			$table_name = "corporation";
			$case_table_uuid = uniqid("KA", false);
			$attribute_1 = "main";
			$last_updated_date = date("Y-m-d H:i:s");
			$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_" . $table_name . "` (`case_" . $table_name . "_uuid`, `case_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', 'venue', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";						
			echo $sql . "\r\n\r\n";
			$stmt = DB::run($sql);
		}
		//carrier
		$table_uuid = uniqid("KR", false);
		$parent_table_uuid = uniqid("CR", false);
		
		$full_address = $injury->iadd1;
		if ($injury->iadd2!="") {
			$full_address .= ", " . $injury->iadd2;
		}
		$full_address .= ", " . $injury->icity;
		$full_address .= ", " . $injury->ist;
		$full_address .= " " . $injury->izip;
		
		if ($injury->iname!="") {
			$arrSet = array();
			$arrSet[] = addslashes($injury->iadj);
			$arrSet[] = addslashes($injury->iname);
			$arrSet[] = "carrier";
			
			$arrSet[] = $injury->adjtel;
			$arrSet[] = $injury->adjfax;
			$arrSet[] = $injury->adjemail;
			$arrSet[] = addslashes($injury->isalut);
			
			$arrSet[] = addslashes($full_address);
			$arrSet[] = addslashes($injury->iadd1);
			$arrSet[] = addslashes($injury->icity);
			$arrSet[] = $injury->ist;
			$arrSet[] = $injury->izip;
			$arrSet[] = addslashes($injury->iadd2);
			if ($injury->iext=="") {
				$arrSet[] = $injury->itel;
			} else {
				$arrSet[] = $injury->itel . " " . $injury->iext;
			}
			$arrSet[] = $injury->ifax;
			$arrSet[] = $injury->iemail;
			
			
			//insert the parent record first
			$sql_carrier = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation` (`corporation_uuid`, `customer_id`, `full_name`, `company_name`, `type`, `employee_phone`, `employee_fax`, `employee_email`, `salutation`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `fax`, `email`, `last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
			VALUES('" . $parent_table_uuid . "', '" . $customer_id . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N', '" . $parent_table_uuid . "', '')";
			
			$stmt = $db->prepare($sql_carrier);  
			echo $sql_carrier . "\r\n\r\n"; 
			$stmt->execute();
			
			$sql_carrier = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation` (`corporation_uuid`, `customer_id`, `full_name`, `company_name`, `type`, `employee_phone`, `employee_fax`, `employee_email`, `salutation`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `fax`, `email`, `last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
					VALUES('" . $table_uuid . "', '" . $customer_id . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N', '" . $parent_table_uuid . "', '')";
			//`employee_phone`, `employee_fax`, `employee_email`, `salutation`, 
			$stmt = $db->prepare($sql_carrier);  
			echo $sql_carrier . "\r\n\r\n"; 
			$stmt->execute();
			
			$case_table_uuid = uniqid("KC", false);
			$attribute_1 = "main";
			//now we have to attach the carrier to the case 
			$sql_carrier = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_corporation` (`case_corporation_uuid`, `case_uuid`, `corporation_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', 'carrier', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
					
			$stmt = $db->prepare($sql_carrier);  
			echo $sql_carrier . "\r\n\r\n"; 
			$stmt->execute();
		}
		//opposing
		$table_uuid = uniqid("OP", false);
		$parent_table_uuid = uniqid("PR", false);
		
		$full_address = $injury->opp_add1;
		if ($injury->opp_add2!="") {
			$full_address .= ", " . $injury->opp_add2;
		}
		$full_address .= ", " . $injury->opp_city;
		$full_address .= ", " . $injury->opp_state;
		$full_address .= " " . $injury->opp_zip;
		
		if ($injury->opp_firm!="") {
			$arrSet = array();
			$arrSet[] = addslashes($injury->opp_attorney);
			$arrSet[] = addslashes($injury->opp_firm);
			$arrSet[] = "defense";
			$arrSet[] = $injury->adjtel;
			$arrSet[] = $injury->adjfax;
			$arrSet[] = $injury->adjemail;
			$arrSet[] = addslashes($full_address);
			$arrSet[] = addslashes($injury->opp_add1);
			$arrSet[] = addslashes($injury->opp_city);
			$arrSet[] = $injury->opp_state;
			$arrSet[] = $injury->opp_zip;
			$arrSet[] = addslashes($injury->opp_add2);
			if ($injury->opp_ext=="") {
				$arrSet[] = $injury->opp_tel;
			} else {
				$arrSet[] = $injury->opp_tel . " " . $injury->opp_ext;
			}
			$arrSet[] = $injury->opp_fax;
			$arrSet[] = $injury->opp_email;
			$arrSet[] = addslashes($injury->opp_salut);
			
			//insert the parent record first
			$sql_opposing = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation` (`corporation_uuid`, `customer_id`, `full_name`, `company_name`, `type`, `employee_phone`, `employee_fax`, `employee_email`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `fax`, `email`, `salutation`, `last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
			VALUES('" . $parent_table_uuid . "', '" . $customer_id . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N', '" . $parent_table_uuid . "','')";
					
			$stmt = $db->prepare($sql_opposing); 
			echo $sql_opposing . "\r\n\r\n";  
			$stmt->execute();
			
			$sql_opposing = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation` (`corporation_uuid`, `customer_id`, `full_name`, `company_name`, `type`, `employee_phone`, `employee_fax`, `employee_email`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `fax`, `email`, `salutation`, `last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
			VALUES('" . $table_uuid . "', '" . $customer_id . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N', '" . $parent_table_uuid . "','')";
					
			$stmt = $db->prepare($sql_opposing);  
			echo $sql_opposing . "\r\n\r\n";
			$stmt->execute();
			
			$case_table_uuid = uniqid("OA", false);
			$attribute_1 = "main";
			//now we have to attach the opposing to the case 
			$sql_opposing = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_corporation` (`case_corporation_uuid`, `case_uuid`, `corporation_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', 'defense', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
					
			$stmt = $db->prepare($sql_opposing);  
			echo $sql_opposing . "\r\n\r\n";
			$stmt->execute();
		}
		//doctors
		$sql_medical = "SELECT med.medpnt, `clinicname` `medname`, IFNULL(`med`.`drname`, '') `drname`, 
		med.medtel, 
		med.medext, med.medfax, med.medsalut, med.medmemo, clin.clinicadd1, clin.clinicadd2, 
		clin.cliniccity, clinicst, cliniczip, clinictel, clinicext, clinicfax, clinicemail
		FROM `" . $data_source . "`.`medicals` med
		INNER JOIN `" . $data_source . "`.`clinics` `clin` 
		ON `med`.medpnt = clin.clinicpnt
		WHERE med.mpointer = " . $injury->cpointer;
		$stmt = $db->prepare($sql_medical);
		echo $sql_medical . "\r\n\r\n";
		$stmt->execute();
		$medicals = $stmt->fetchAll(PDO::FETCH_OBJ);

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
				
				if ($medical->medext=="") {
					$arrSet[] = $medical->clinictel;
				} else {
					$arrSet[] = $medical->clinictel . " " . $medical->clinicext;
				}
				$arrSet[] = $medical->clinicfax;
				$arrSet[] = $medical->clinicemail;
				
				if ($medical->medext=="") {
					$arrSet[] = $medical->medtel;
				} else {
					$arrSet[] = $medical->medtel . " " . $medical->medext;
				}
				$arrSet[] = $medical->medfax;
				$arrSet[] = $medical->medemail;
				
				$arrSet[] = addslashes($medical->medsalut);
				
				//insert the parent record first
				$sql_medical = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation` (`corporation_uuid`, `customer_id`, `full_name`, `company_name`, `type`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `fax`, `email`, `employee_phone`, `employee_fax`, `employee_email`, `salutation`, `last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
				VALUES('" . $parent_table_uuid . "', '" . $customer_id . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N', '" . $parent_table_uuid . "','')";
						
				$stmt = $db->prepare($sql_medical); 
				echo $sql_medical . "\r\n\r\n"; 
				$stmt->execute();
				
				$sql_medical = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation` (`corporation_uuid`, `customer_id`, `full_name`, `company_name`, `type`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `fax`, `email`, `employee_phone`, `employee_fax`, `employee_email`, `salutation`, `last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
				VALUES('" . $table_uuid . "', '" . $customer_id . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N', '" . $parent_table_uuid . "', '')";
						
				$stmt = $db->prepare($sql_medical);  
				echo $sql_medical . "\r\n\r\n"; 
				$stmt->execute();
				
				$case_table_uuid = uniqid("OA", false);
				$attribute_1 = "main";
				//now we have to attach the doctor to the case 
				$sql_medical = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_corporation` (`case_corporation_uuid`, `case_uuid`, `corporation_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', 'medical_provider', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
						
				$stmt = $db->prepare($sql_medical); 
				echo $sql_medical . "\r\n\r\n";  
				$stmt->execute();
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
		
		
		//forms
		//SELECT * FROM reino.wcab2008 WHERE cpointer = '1850'
		
		//die("done");
	}
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	$success = array("success"=> array("text"=>"done", "duration"=>$total_time));
	echo json_encode($success);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}

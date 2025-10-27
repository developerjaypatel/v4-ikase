<?php
$app->get('/jetfile/adj/check/:adj_number', authorize('user'),	'getADJCount');
$app->get('/jetfile/app/status/:filing_id', authorize('user'),	'checkFiling');
$app->get('/jetfile/fetch/:injury_id', authorize('user'), 'fetchDOIJetfile');
$app->get('/jetfiles', authorize('user'), 'fetchJetfiles');
$app->get('/jetfiles/all', authorize('user'), 'fetchAllJetfiles');
$app->get('/jetfiles/errors', authorize('user'), 'fetchErrorJetfiles');
$app->get('/jetfiles/recent', authorize('user'), 'fetchRecentJetfiles');

$app->get('/jetfiles/search/:search_term', authorize('user'), 'searchJetfiles');


$app->get('/jetfile/app/checkstatus', 'verifyPendingAPP');
$app->get('/jetfile/monitor', 'monitorFilings');

//from ikase
$app->post('/jetfile/getpdf', authorize('user'), 'getPDF');
$app->post('/jetfile/requestpdf', authorize('user'), 'requestPDF');
$app->post('/jetfile/requestcreatepdf', authorize('user'), 'requestCreatePDF');

$app->post('/jetfile/app_packet', authorize('user'), 'createAppPacket');

//from cajetfile
$app->post('/jetfile/acceptpdf', 'acceptPDF');

$app->post('/jetfile/save/app', authorize('user'), 'saveApp');
$app->post('/jetfile/save/dor', authorize('user'), 'saveDOR');
$app->post('/jetfile/save/dore', authorize('user'), 'saveDORE');
$app->post('/jetfile/save/lien', authorize('user'), 'saveLien');
$app->post('/jetfile/save/unstructured', authorize('user'), 'saveUnstructured');

$app->post('/jetfile/check/applicant', authorize('user'), 'checkApplicant');
$app->post('/jetfile/check/dor', authorize('user'), 'checkDOR');
$app->post('/jetfile/check/dore', authorize('user'), 'checkDORE');

$app->post('/jetfile/send', authorize('user'), 'sendAPP');
$app->post('/jetfile/resend', authorize('user'), 'resendAPP');
$app->post('/jetfile/senddor', authorize('user'), 'sendDOR');
$app->post('/jetfile/senddore', authorize('user'), 'sendDORE');
$app->post('/jetfile/sendlien', authorize('user'), 'sendLien');
$app->post('/jetfile/sendunstruc', authorize('user'), 'sendUnstruc');

$app->post('/jetfile/updatecase', authorize('user'), 'updateCase');
$app->post('/jetfile/app/filingid', authorize('user'), 'updateAppFilingId');
$app->post('/jetfile/updateadj', authorize('user'), 'updateCaseADJ');
$app->post('/jetfile/updatedor', authorize('user'), 'updateDOR');
$app->post('/jetfile/dor/filingid', authorize('user'), 'updateDORFilingId');
$app->post('/jetfile/updatedore', authorize('user'), 'updateDORE');
$app->post('/jetfile/updatelien', authorize('user'), 'updateJetLien');
$app->post('/jetfile/updateunstruc', authorize('user'), 'updateUnstruc');
$app->post('/jetfile/unstruc/filingid', authorize('user'), 'updateUnstrucFilingId');

$app->post('/jetfile/file', authorize('user'), 'appFile');
$app->post('/jetfile/updateid', authorize('user'), 'updateJetfileId');
//$jetfile_unstruc_id = passed_var("jetfile_unstruc_id", "post");
$app->post('/jetfile/filedor', authorize('user'), 'dorFile');
$app->post('/jetfile/filedore', authorize('user'), 'doreFile');
$app->post('/jetfile/fileunstruc', authorize('user'), 'unstrucFile');

function fetchJetfiles() {
	$injury_id = "";
	if (isset($_SESSION["search_injury_id"])) {
		$injury_id = $_SESSION["search_injury_id"];
		unset($_SESSION["search_injury_id"]);
	}
	
	$search_term = "";
	if (isset($_SESSION["search_jetfile_term"])) {
		$search_term = trim($_SESSION["search_jetfile_term"]);
		unset($_SESSION["search_jetfile_term"]);
	}
	
	$blnErrors = false;
	if (isset($_SESSION["search_jetfile_errors"])) {
		$blnErrors =  true;
		unset($_SESSION["search_jetfile_errors"]);
	}
	$blnRecent = false;
	if (isset($_SESSION["search_jetfile_recent"])) {
		$blnRecent =  true;
		unset($_SESSION["search_jetfile_recent"]);
	}
	session_write_close();
	
	$sql = "SELECT ccase.case_id,cdocu.billing_code,cdocu.document_submitted_by,cdocu.vendor_submittal_id,cdocu.docucents_upload_date, inj.injury_number, inj.injury_id, inj.start_date, inj.end_date, 
	IFNULL(inj.adj_number, '') adj_number, 
	IF (app.first_name IS NULL, '', TRIM(app.first_name)) first_name , IF(app.last_name IS NULL, '', app.last_name) last_name, IFNULL(app.full_name, '') `full_name`,
	`jet`.`jetfile_id`, `jet`.`injury_uuid`, IFNULL(`jet`.`info`, '') `info`, `jet`.`jetfile_case_id`, `jet`.`app_filing_id`, `jet`.`app_filing_date`, `jet`.`app_status`, IFNULL(`jet`.`dor_info`, '') `dor_info`, `jet`.`jetfile_dor_id`, `jet`.`dor_filing_id`, `jet`.`dor_filing_date`, IFNULL(`jet`.`dore_info`, '') `dore_info`, `jet`.`jetfile_dore_id`, `jet`.`dore_filing_id`, `jet`.`dore_filing_date`, IFNULL(`jet`.`lien_info`, '') `lien_info`, `jet`.`jetfile_lien_id`, `jet`.`lien_filing_id`, `jet`.`lien_filing_date`, IFNULL(`jet`.`unstruc_info`, '') `unstruc_info`,
	IFNULL(app_docs.document_count, 0) app_document_count,
	IFNULL(bp.bodyparts_count, 0) bodyparts_count,
    IFNULL(eams_submissions.user_name, '') submitted_by,
    IFNULL(eams_submissions.activity_date, '') submitted_date
	FROM cse_jetfile jet
	INNER JOIN cse_injury inj
	ON jet.injury_uuid = inj.injury_uuid
	INNER JOIN cse_case_injury cci
	ON inj.injury_uuid = cci.injury_uuid
	INNER JOIN cse_case ccase
	ON cci.case_uuid = ccase.case_uuid
	LEFT OUTER JOIN cse_docucents cdocu
	ON ccase.case_id = cdocu.case_id
	LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N'
	LEFT OUTER JOIN ";	
if (($_SESSION['user_customer_id']==1033)) { 
	$sql .= "(" . SQL_PERSONX . ")";
} else {
	$sql .= "cse_person";
}
$sql .= " app ON ccapp.person_uuid = app.person_uuid
	
	LEFT OUTER JOIN (
		SELECT injury_id, COUNT(`document_id`) document_count
		FROM cse_document doc
		INNER JOIN cse_injury_document ccd
		ON doc.document_uuid = ccd.document_uuid
		INNER JOIN cse_injury inj
		ON ccd.injury_uuid = inj.injury_uuid
		WHERE `doc`.`type` = 'App_for_ADJ' 
		AND `document_filename` != ''
		AND `doc`.customer_id = :customer_id
		AND `doc`.deleted = 'N'
		GROUP BY injury_id
	) app_docs
	ON inj.injury_id = app_docs.injury_id
	
	LEFT OUTER JOIN (
		SELECT `ci`.`injury_id`, COUNT(bp.bodyparts_id) bodyparts_count
		FROM `cse_bodyparts` bp
		INNER JOIN cse_injury_bodyparts cib
		ON bp.bodyparts_uuid = cib.bodyparts_uuid
		INNER JOIN cse_injury ci
		ON cib.injury_uuid = ci.injury_uuid
		GROUP BY `ci`.`injury_id`
	) bp
	ON inj.injury_id = bp.injury_id
	
	LEFT OUTER JOIN (
		SELECT ccase.case_id, cact.case_uuid, usr.user_name, act.*
		FROM cse_activity act
		INNER JOIN ikase.cse_user usr
		ON act.activity_user_id = usr.user_id
		INNER JOIN cse_case_activity cact
		ON act.activity_uuid = cact.activity_uuid
		INNER JOIN cse_case ccase
		ON cact.case_uuid = ccase.case_uuid
		INNER JOIN (
			SELECT case_uuid, MAX(act.activity_id) activity_id 
			FROM cse_case_activity cca
			INNER JOIN cse_activity act
			ON cca.activity_uuid = act.activity_uuid
			WHERE 1
			AND attribute = 'EAMS Submission'
			GROUP BY case_uuid
		) max_activity
		ON act.activity_id = max_activity.activity_id
		WHERE activity_category = 'EAMS Submission'
		AND act.customer_id = :customer_id
    ) eams_submissions
    ON ccase.case_id = eams_submissions.case_id
	
	WHERE inj.customer_id = :customer_id";
	if ($injury_id!="") {
		$sql .= " AND inj.injury_id= :injury_id";
	}
	if ($search_term!="") {
		$jet_term = "%" . $search_term . "%";
		//search applicant name, case number, jetfile_case_id
		$sql .= " AND (jet.info LIKE :jet_term OR jet.dor_info LIKE :jet_term OR jet.dore_info LIKE :jet_term OR jet.lien_info LIKE :jet_term OR jet.unstruc_info LIKE :jet_term";
		if (is_numeric($search_term)) {
			$sql .= " OR jet.jetfile_case_id = :search_term";
		}
		$sql .= ")";
	}
	
	if ($blnErrors) {
		$sql .= " 
		AND jet.app_status NOT LIKE '%\"errors\":null%'
		AND jet.app_status NOT LIKE '%\"errors\":\"\"%'
		AND jet.app_status != ''";
	}
	$sql .= " AND jet.deleted = 'N'";
	$limit = "300";
	if ($blnRecent) {
		$limit = "250";
		$sql .= "
		ORDER BY `jet`.`last_update_date` DESC";
	} else {
		$sql .= "
		ORDER BY `jet`.`jetfile_id` DESC";
	}
	$sql .= "
	LIMIT 0, " . $limit;
	
	//ORDER BY ccase.case_id, inj.injury_number ASC
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $_SESSION["user_customer_id"]);
		if ($injury_id!="") {
			$stmt->bindParam("injury_id", $injury_id);
		}
		if ($search_term!="") {
			$stmt->bindParam("jet_term", $jet_term);
			if (is_numeric($search_term)) {
				$stmt->bindParam("search_term", $search_term);
			}
		}
		$stmt->execute();
		$jetfiles = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		echo json_encode($jetfiles);
	} catch(PDOException $e) {
		$error = array("sql"=>$sql, "error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
	}
}
function searchJetfiles($search_term) {
	$_SESSION["search_jetfile_term"] = $search_term;
	fetchJetfiles();
	die();
}
function fetchRecentJetfiles() {
	$_SESSION["search_jetfile_recent"] = true;
	fetchJetfiles();
	die();
}
function fetchErrorJetfiles() {
	$_SESSION["search_jetfile_errors"] = true;
	fetchJetfiles();
	die();
}
function fetchDOIJetfile($injury_id) {
	$_SESSION["search_injury_id"] = $injury_id;
	fetchJetfiles();
	die();
	
	$sql = "SELECT ccase.case_id, inj.injury_number, inj.injury_id, inj.start_date, inj.end_date, 
	inj.adj_number, jet.* 
	FROM cse_jetfile jet
	INNER JOIN cse_injury inj
	ON jet.injury_uuid = inj.injury_uuid
	INNER JOIN cse_case_injury cci
	ON inj.injury_uuid = cci.injury_uuid
	INNER JOIN cse_case ccase
	ON cci.case_uuid = ccase.case_uuid
	WHERE ccase.customer_id = :customer_id
	AND inj.injury_id = :injury_id";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $_SESSION["user_customer_id"]);
		$stmt->bindParam("injury_id", $injury_id);
		
		$stmt->execute();
		$jetfiles = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		//echo json_encode($jetfiles);
	} catch(PDOException $e) {
		$error = array("sql"=>$sql, "error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
	}
}
function fetchAllJetfiles() {
	if (!isset($_SESSION['owner_id'])) {
		//die("no auth");
	}
	$sql_statement = "SELECT ccase.customer_id, ccase.case_id, inj.injury_number, inj.injury_id, inj.start_date, inj.end_date, 
	IFNULL(inj.adj_number, '') adj_number, 
	IF (app.first_name IS NULL, '', TRIM(app.first_name)) first_name , IF(app.last_name IS NULL, '', app.last_name) last_name, IFNULL(app.full_name, '') `full_name`,
	`jet`.`jetfile_id`, `jet`.`injury_uuid`, IFNULL(`jet`.`info`, '') `info`, `jet`.`jetfile_case_id`, `jet`.`app_filing_id`, `jet`.`app_filing_date`, `jet`.`app_status`, IFNULL(`jet`.`dor_info`, '') `dor_info`, `jet`.`jetfile_dor_id`, `jet`.`dor_filing_id`, `jet`.`dor_filing_date`, IFNULL(`jet`.`dore_info`, '') `dore_info`, `jet`.`jetfile_dore_id`, `jet`.`dore_filing_id`, `jet`.`dore_filing_date`, IFNULL(`jet`.`lien_info`, '') `lien_info`, `jet`.`jetfile_lien_id`, `jet`.`lien_filing_id`, `jet`.`lien_filing_date`, IFNULL(`jet`.`unstruc_info`, '') `unstruc_info`,
	IFNULL(app_docs.document_count, 0) app_document_count
	FROM  ikase.cse_jetfile jet
	INNER JOIN  ikase.cse_injury inj
	ON jet.injury_uuid = inj.injury_uuid
	INNER JOIN  ikase.cse_case_injury cci
	ON inj.injury_uuid = cci.injury_uuid
	INNER JOIN  ikase.cse_case ccase
	ON cci.case_uuid = ccase.case_uuid
	LEFT OUTER JOIN ikase. cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N'
			LEFT OUTER JOIN ";
	$sql_statement .= " ikase.cse_person";
	$sql_statement .= " app ON ccapp.person_uuid = app.person_uuid
	
	LEFT OUTER JOIN (
		SELECT injury_id, COUNT(`document_id`) document_count
		FROM  ikase.cse_document doc
		INNER JOIN  ikase.cse_injury_document ccd
		ON doc.document_uuid = ccd.document_uuid
		INNER JOIN  ikase.cse_injury inj
		ON ccd.injury_uuid = inj.injury_uuid
		WHERE `doc`.`type` = 'App_for_ADJ' 
		AND `document_filename` != ''
		AND `doc`.deleted = 'N'
		GROUP BY injury_id
	) app_docs
	ON inj.injury_id = app_docs.injury_id
	WHERE inj.customer_id != 1033";

	$sql_statement .= " AND jet.deleted = 'N'";
	
	//we need all of the jetfile submissions
	$sql = "SELECT `schema_name`
	FROM `information_schema`.schemata 
	WHERE schema_name LIKE 'ikase%'";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$schemas = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		//die(print_r($schemas));
		$arrUnion = array();
		
		foreach($schemas as $schema) {
			//skip
			if ($schema->schema_name=="ikase_glauber" || $schema->schema_name=="ikase_glauber2") {
				continue;
			}
			$new_sql = str_replace("ikase.", "`" . $schema->schema_name . "`.", $sql_statement);
			$arrUnion[] = $new_sql;
			//echo $sql . "\r\n\r\n";
			
		}
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
		die();
	}
	
	$sql = implode(" 
	UNION 
	", $arrUnion);
	$sql .= " 
	ORDER BY customer_id, case_id";
	
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$jetfiles = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		echo json_encode($jetfiles);
	} catch(PDOException $e) {
		$error = array("sql"=>$sql, "error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
	}
}
function monitorFilings () {
	session_write_close();
	//die();
	/*
	$handle = fopen("monitor_track.txt", "a+");
	fwrite($handle, "\r\nmonitor @ " . date("m/d/y H:i:s"));
    fclose($handle);
	*/
	//ccase.customer_id, `cus`.`eams_no`, `cus`.`jetfile_id`, 
	$sql_statement = "SELECT IFNULL(GROUP_CONCAT(jet.jetfile_case_id), '') ids
	FROM ikase.cse_jetfile jet
	INNER JOIN ikase.cse_injury inj
	ON jet.injury_uuid = inj.injury_uuid
	INNER JOIN ikase.cse_case_injury cci
	ON inj.injury_uuid = cci.injury_uuid
	INNER JOIN ikase.cse_case ccase
	ON cci.case_uuid = ccase.case_uuid
	INNER JOIN `ikase`.`cse_customer` cus
	ON `jet`.`customer_id` = `cus`.`customer_id` AND `cus`.deleted = 'N'
    WHERE 1
	#AND jet.jetfile_case_id = '35758'
    AND jet.deleted = 'N'
	AND jet.jetfile_case_id > 0
    AND (inj.adj_number NOT LIKE 'ADJ%' OR IF(jet.app_status = '', 0, jet.app_status_number) < 5)";
	//ccase.customer_id = :customer_id
	//
	
	//$arrUnion = array($sql_statement);
	
	//we need all of the jetfile submissions
	$sql = "SELECT `schema_name`
	FROM `information_schema`.schemata 
	WHERE schema_name LIKE 'ikase%'";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$schemas = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		//die(print_r($schemas));
		$arrUnion = array();
		
		foreach($schemas as $schema) {
			//skip
			if ($schema->schema_name=="ikase_glauber" || $schema->schema_name=="ikase_glauber2") {
				continue;
			}
			$new_sql = str_replace("ikase.", "`" . $schema->schema_name . "`.", $sql_statement);
			
			try {
				$db = getConnection();
				$stmt = $db->prepare($new_sql);
				//$stmt->bindParam("customer_id", $_SESSION["user_customer_id"]);
				
				$stmt->execute();
				$monitor = $stmt->fetchObject();
				$stmt->closeCursor(); $stmt = null; $db = null;
				
				//die($new_sql);
				
				if ($monitor->ids=="") {
					continue;
				}
				//send to jetfile to check on status
				$url = "https://www.cajetfile.com/ikase/monitor.php";	
				$fields = array("ids"=>$monitor->ids);

				//echo $url; die(print_r($fields));
				$result = post_curl($url, $fields);
				//die($result);
				
				//return the json directly back to the view
				$jets = json_decode($result);
				
				//if updated, must notify
				$arrNotification = array();
				//die(print_r($jets));
				
				foreach($jets as &$jet) {
					//echo $jet->case_id . "\r\n";
					//continue;
								
					$jetfile_case_id = trim($jet->case_id);
					
					$jet_errors = $jet->errors;
					$jet_errors = str_replace('xmlns:eam="http://www.dir.ca.gov/dwc/EAMS/PresentTermSolution/Schemas/Payloads/EAMSPacketValidationResponse"', '', $jet_errors);
					$jet_errors = str_replace('xmlns:pay="http://www.dir.ca.gov/dwc/EAMS/PresentTermSolution/Schemas/Common/PayloadFields"', '', $jet_errors);
					//$jet_errors = str_replace("Error", "E", $jet_errors);
					//die($jet_errors);
					$jet->errors = $jet_errors;
		
					//from cus_id and eams no, work out the customer
					$jetfile_cus_id = $jet->cus_id;
					$jetfile_cus_eams_no = $jet->cus_eams_no;
					
					if ($jetfile_cus_eams_no=="" && $jetfile_cus_id=="") {
						continue;
					}
					
					$arrFilter = array();
					$sql = "SELECT customer_id, data_source 
					FROM ikase.cse_customer
					WHERE 1
					AND deleted = 'N'";
					if ($jetfile_cus_eams_no!="") {
						$arrFilter[] = "eams_no = :jetfile_cus_eams_no";
					}
					if ($jetfile_cus_id!="") {
						$arrFilter[] = "jetfile_id = :jetfile_cus_id";
					}
					$sql .= " AND (" . implode(" OR ", $arrFilter) . ")";
					$db = getConnection();
					$stmt = $db->prepare($sql);
					if ($jetfile_cus_eams_no!="") {
						$stmt->bindParam("jetfile_cus_eams_no", $jetfile_cus_eams_no);
					}
					if ($jetfile_cus_id!="") {
						$stmt->bindParam("jetfile_cus_id", $jetfile_cus_id);
					}
					$stmt->execute();
					$cus = $stmt->fetchObject();
					$stmt->closeCursor(); $stmt = null; $db = null;
					
					//now we have our database
					$db_name = "ikase";
					if ($cus->data_source!="") {
						$db_name .= "_" . $cus->data_source;
					}
					//get the values from jetfiler
					
					$adj_number = $jet->adj_number;
					if ($adj_number=="") {
						$adj_number = $jet->case_number;
						$jet->adj_number = $jet->case_number;
					}
					$app_status_number = $jet->status;
					$app_status = json_encode($jet);
					$the_adj_number = $adj_number;
					
					//does it need to be updated, what is the current status
					$sql = "SELECT jet.app_status, jet.app_status_number, jet.injury_uuid, inj.injury_id, inj.adj_number, cinj.case_uuid
					FROM " . $db_name . ".cse_jetfile jet
					INNER JOIN " . $db_name . ".cse_injury inj
					ON jet.injury_uuid = inj.injury_uuid
					INNER JOIN " . $db_name . ".cse_case_injury cinj
					ON inj.injury_uuid = cinj.injury_uuid
					WHERE jet.jetfile_case_id = " . $jetfile_case_id . "
					AND jet.customer_id = " . $cus->customer_id . "
					AND inj.customer_id = " . $cus->customer_id;
					
					//die($sql);
					$db = getConnection();
					$stmt = $db->prepare($sql);
					$stmt->execute();
					$current = $stmt->fetchObject();
					$stmt->closeCursor(); $stmt = null; $db = null;
					
					
					if (!is_object($current)) {

						continue;
					}
					//print_r($current);
					//die("jet:" . $jet_errors);
					//if ($current->app_status!=$app_status || $current->adj_number!=$adj_number) {
					if ($current->app_status_number!=$app_status_number || $current->adj_number!=$adj_number || $jet_errors!="") {
						
						/*
						$jet_status = $app_status;
						$jet_status = str_replace("pay:Error", "p:E", $jet_status);
						$jet_status = str_replace("eam:Form", "e:F", $jet_status);
						$jet_status = str_replace("eam:ResubmissionID", "e:R", $jet_status);
						$app_status = str_replace("resubmission_id", "r_id", $jet_status);
						*/
						$sql = "UPDATE " . $db_name . ".cse_jetfile jet, " . $db_name . ".cse_injury inj
						SET jet.app_status = '" . addslashes($app_status) . "', jet.app_status_number = '" . $app_status_number . "'";
						if ($adj_number!="") {
							$sql .= ", inj.adj_number = '" . $adj_number . "'";
						}
						$sql .= "
						WHERE jet.injury_uuid = inj.injury_uuid
						AND jet.jetfile_case_id = '" . $jetfile_case_id . "'				
						AND jet.customer_id = '" . $cus->customer_id . "'
						AND inj.customer_id = '" . $cus->customer_id . "'";
						
						$db = getConnection();
						$stmt = $db->prepare($sql);
						$stmt->execute();
						$stmt = null; $db = null;
						
						
						if ($adj_number!="") {
							//has notification been done?
							$sql = "SELECT cca.case_uuid, COUNT(activity_id) activity_count
							FROM " . $db_name . ".cse_activity act
							INNER JOIN " . $db_name . ".cse_case_activity cca
							ON act.activity_uuid = cca.activity_uuid
							WHERE activity = 'ADJ Generated'
							AND act.deleted = 'N'
							AND attribute = 'EAMS Submission'
							AND cca.case_uuid = '" . $current->case_uuid . "'
							GROUP BY cca.case_uuid";
							
							$db = getConnection();
							$stmt = $db->prepare($sql);
							$stmt->execute();
							$act_check = $stmt->fetchObject();
							$stmt->closeCursor(); $stmt = null; $db = null;
							
							$blnNotified = false;
							if (is_object($act_check)) {
								if ($act_check->activity_count > 0) {
									$blnNotified = true;
								}
							}
							if (!$blnNotified) {
								$arrNotification[] = array("customer_id"=>$cus->customer_id, "db_name"=>$db_name, "jetfile_case_id"=>$jetfile_case_id, "injury_id"=>$current->injury_id, "injury_uuid"=>$current->injury_uuid, "adj_number"=>$adj_number);
								//track the update
								//trackInjury("eams_adj_update", $current->injury_id, $cus->customer_id, $db_name);
								$sql = "INSERT INTO " . $db_name . ".cse_injury_track (`user_uuid`, `user_logon`, `operation`, `injury_id`, `injury_uuid`, `adj_number`, `type`, `occupation`, `start_date`, `end_date`, `explanation`, `full_address`, `suite`, `customer_id`, `deleted`)
								SELECT '-2', 'system', 'eams_adj_update', `injury_id`, `injury_uuid`, `adj_number`, `type`, `occupation`, `start_date`, `end_date`, `explanation`, `full_address`, `suite`, `customer_id`, `deleted`
								FROM " . $db_name . ".cse_injury
								WHERE 1
								AND injury_id = " . $current->injury_id . "
								AND customer_id = " . $cus->customer_id . "
								LIMIT 0, 1";
								//echo $sql . "<br />";
								$db = getConnection();
								$stmt = $db->prepare($sql);  
								$stmt->execute();
								
								$operation = "updated via EAMS Jetfile [ADJ: " . $adj_number . "]";
								$activity = "ADJ Generated";
								$case_uuid = $current->case_uuid;
								$activity_category = "EAMS Submission";
								$billing_time = 0;
								recordJetFileActivity($operation, $activity, $case_uuid, -1, $activity_category, $billing_time, $cus->customer_id, $db_name);
							}
						}
						if (strpos($app_status_number, '"DOR","status":"5"') > -1) {
							$operation = "approved";
							$activity = "DOR request approved";
							$case_uuid = $current->case_uuid;
							$activity_category = "EAMS Submission";
							$billing_time = 0;
							recordJetFileActivity($operation, $activity, $case_uuid, -1, $activity_category, $billing_time, $cus_id, $db_name);
						}
					}
				}
				//print_r($arrNotification);
				//continue;
				//die();
				if(count($arrNotification) > 0) {		
					//now we must notify
					$arrDestination = array();
					foreach($arrNotification as $notification) {
						//identify the case worker, if no worker use attorney
						$customer_id = $notification["customer_id"];
						$db_name = $notification["db_name"];
						$adj_number = $notification["adj_number"];
						$jetfile_case_id = $notification["jetfile_case_id"];
						$injury_id = $notification["injury_id"];
						$injury_uuid = $notification["injury_uuid"];
						
						$sql = "SELECT ccase.case_id, ccase.case_uuid, IF (file_number='', case_number, file_number) case_number, 
						worker, attorney, supervising_attorney
						FROM " . $db_name . ".cse_case ccase
						INNER JOIN " . $db_name . ".cse_case_injury cci
						ON ccase.case_uuid = cci.case_uuid
						WHERE cci.injury_uuid = '" . $injury_uuid . "'";
						
						$db = getConnection();
						$stmt = $db->prepare($sql);
						$stmt->execute();
						$kase = $stmt->fetchObject();
						$stmt->closeCursor(); $stmt = null; $db = null;
						
						//notify the worker, if no worker, supervising, if no supervising, atty
						$case_worker = $kase->worker;
						if ($case_worker=="") {
							$case_worker = $kase->supervising_attorney;
						}
						if ($case_worker=="") {
							$case_worker = $kase->attorney;
						}
						//i will need user_uuid so i have to look up either way
						if (is_numeric($case_worker)) {
							//look up by id
							$worker = getUserInfo($case_worker);
						} else {
							//look up by nickname
							$worker = getUserByNickname($case_worker);
						}
						if (is_object($worker)) {
							$case_worker = $worker->nickname;
							$user_uuid = $worker->user_uuid;
							$user_id = $worker->user_id;
							//put all the messages together for a single destination
							$notification["case_number"] = $kase->case_number;
							$notification["case_id"] = $kase->case_id;
							$notification["case_uuid"] = $kase->case_uuid;
							$notification["user_uuid"] = $user_uuid;
							$notification["user_id"] = $user_id;
							
							$arrDestination[$case_worker][] = $notification;
						}
					}
					//print_r($arrDestination);
					
					$from = "system";
					$subject = "EAMS ADJ Update";
					$dateandtime = date("Y-m-d H:i:s");
					$message_type = "reminder";	
					$priority = "";
						
					foreach($arrDestination as $case_worker=>$arrWorkerNotifications) {
						$message_uuid = uniqid("MS", false);
						$thread_uuid = uniqid("TD", false);
						$arrMessages = array();
						foreach($arrWorkerNotifications as $notification) {
							$customer_id = $notification["customer_id"];
							$user_id = $notification["user_id"];
							$user_uuid = $notification["user_uuid"];
							$db_name = $notification["db_name"];
							$adj_number = $notification["adj_number"];
							$jetfile_case_id = $notification["jetfile_case_id"];
							$injury_id = $notification["injury_id"];
							$case_number = $notification["case_number"];
							$case_id = $notification["case_id"];
							$case_uuid = $notification["case_uuid"];
							$injury_uuid = $notification["injury_uuid"];
							
							$arrMessages[] = "Case <a href='#kases/" . $case_id . "' class='white_text'>" . $case_number . "</a> has been assigned ADJ " . $adj_number;
						}
						$message = implode("\r\n\r\n", $arrMessages);
						//i have the worker, i can send an interoffice message
						$sql = "INSERT INTO " . $db_name . ".`cse_thread` (`customer_id`, `dateandtime`, `thread_uuid`, `from`, `subject`) 
						VALUES('" . $customer_id . "', '" . $dateandtime . "','" . $thread_uuid . "', '" . $from . "', '" . $subject . "')";
						echo $sql . "<br />";
						
						$db = getConnection();
						$stmt = $db->prepare($sql);						
						$stmt->execute();
						$stmt = null; $db = null;
						
						
						$sql = "INSERT INTO " . $db_name . ".`cse_message`
						(`message_uuid`, `message_type`, `dateandtime`, `from`, `message_to`, `message`, `subject`, `priority`, `customer_id`)
						VALUES ('" . $message_uuid . "', '" . $message_type . "', '" . $dateandtime .  "', '" . $from . "', '" . $case_worker . "', '" . addslashes($message) . "', '" . addslashes($subject) . "', '" . $priority . "', '" . $customer_id . "')";
						echo $sql . "<br />";
						
						$db = getConnection();
						$stmt = $db->prepare($sql);						
						$stmt->execute();
						$message_id = $db->lastInsertId();
						$stmt = null; $db = null;
						
						$case_message_uuid = uniqid("TD", false);
						
						$sql = "INSERT INTO " . $db_name . ".cse_thread_message (`thread_message_uuid`, `thread_uuid`, `message_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`, message_id)
						VALUES ('" . $case_message_uuid  ."', '" . $thread_uuid . "', '" . $message_uuid . "', 'main', '" . $dateandtime . "', 'system', '" . $customer_id . "', '" . $message_id . "')";
						
						$db = getConnection();
						$stmt = $db->prepare($sql);  
						$stmt->execute();
						$stmt = null; $db = null;
						
						//i need the system user_id
						$sql = "SELECT user_id
						FROM ikase.cse_user
						WHERE user_name = 'system'
						AND customer_id = " . $customer_id;
						
						$db = getConnection();
						$stmt = $db->prepare($sql);
						$stmt->execute();
						$system_user = $stmt->fetchObject();
						$stmt->closeCursor(); $stmt = null; $db = null;
						
						if (!is_object($system_user)) {
							//create it
							$user_uuid = "system_" . $customer_id;
							$sql = "INSERT INTO ikase.cse_user
							SELECT '" . $user_uuid . "', '" . $customer_id . "', `cis_id`, `cis_uid`, `user_type`, `user_name`, `user_logon`, `user_first_name`, `user_last_name`, `user_email`, `user_cell`, `nickname`, `pwd`, `level`, `job`, `status`, `personal_calendar`, `access_token`, `any_time`, `day_start`, `day_end`, `days_of_week`, `dow_times`, `sess_id`, `dateandtime`, `ip_address`, `calendar_color`, `deleted`, `activated`, `imei_number`, `default_attorney`, `rate`, `tax`, `adhoc`
FROM ikase.cse_user
WHERE customer_id = 1033
AND user_name = 'system'";
							$db = getConnection();	
							$stmt = $db->prepare($sql);  
							$stmt->execute();
							$stmt = null; $db = null;
							
							//refetch it
							$sql = "SELECT user_id
							FROM ikase.cse_user
							WHERE user_name = 'system'
							AND user_uuid = '" . $user_uuid . "'";
							
							$db = getConnection();
							$stmt = $db->prepare($sql);
							$stmt->execute();
							$system_user = $stmt->fetchObject();
							$stmt->closeCursor(); $stmt = null; $db = null;
						}
						
						$sql = "INSERT INTO " . $db_name . ".cse_message_user (`message_user_uuid`, `message_uuid`, `user_uuid`, `thread_uuid`, `type`, `last_updated_date`, `last_update_user`, `customer_id`, message_id, user_id";
						$sql .= ")";
						$sql .= " VALUES ('" . $case_message_uuid  ."', '" . $message_uuid . "', '" . $user_uuid . "', '" . $thread_uuid . "', 'to', '" . $dateandtime . "', 'system', '" . $customer_id . "','" . $message_id . "','" . $user_id . "')";
						
						$db = getConnection();	
						$stmt = $db->prepare($sql);  
						$stmt->execute();
						$stmt = null; $db = null;
						
						//attach to case
						$sql = "INSERT INTO " . $db_name . ".cse_case_message (`case_message_uuid`, `case_uuid`, `message_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
						VALUES ('" . $case_message_uuid  ."', '" . $case_uuid . "', '" . $message_uuid . "', 'main', '" . $dateandtime . "', 'system', '" . $customer_id . "')";
						echo $sql . "<br />";	
						
						$db = getConnection();	
						$stmt = $db->prepare($sql);  
						$stmt->execute();
						$stmt = null; $db = null;
						
						//attach the from
						$message_user_uuid = uniqid("TD", false);
						$sql = "INSERT INTO " . $db_name . ".cse_message_user (`message_user_uuid`, `message_uuid`, `user_uuid`, `type`, `last_updated_date`, `last_update_user`, `customer_id`, `thread_uuid`, message_id, user_id)
						VALUES ('" . $message_user_uuid  ."', '" . $message_uuid . "', 'system', 'from', '" . $dateandtime . "', 'system', '" . $customer_id . "', '". $thread_uuid . "','" . $message_id . "','" . $system_user->user_id . "')";
						echo $sql . "<br />";	
		
						$db = getConnection();	
						$stmt = $db->prepare($sql);  
						$stmt->execute();
						$stmt = null; $db = null;
					}
				}
				echo json_encode($jets);
			} catch(PDOException $e) {
				echo $e->getMessage() . "<br />";
				$error = array("sql"=>$sql, "error"=> array("text"=>$e->getMessage()));
				//print_r($error);
				//die($sql);
				echo json_encode($error);
			}
			//$arrUnion[] = $new_sql;
		}
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
		die();
	}
	/*
	$sql = "SELECT GROUP_CONCAT(union_jet.ids) ids FROM (
	" . implode(" 
	UNION 
	", $arrUnion);
	
	$sql .= ") union_jet";
	*/
	//die($sql);
	
}
function updateCase() {
	session_write_close();
	$jetfile_id = passed_var("jetfile_id", "post");
	$jetfile_case_id = passed_var("jetfile_case_id", "post");
	
	$sql = "UPDATE cse_jetfile
	SET `jetfile_case_id` = :jetfile_case_id
	WHERE `jetfile_id` = :jetfile_id
	AND `customer_id` = :customer_id";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $_SESSION["user_customer_id"]);
		$stmt->bindParam("jetfile_case_id", $jetfile_case_id);
		$stmt->bindParam("jetfile_id", $jetfile_id);
		
		$stmt->execute();
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>"true"));
	} catch(PDOException $e) {
		$error = array("sql"=>$sql, "error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
	}
}
function updateCaseADJ() {
	session_write_close();
	$injury_id = passed_var("injury_id", "post");
	$adj_number = passed_var("adj_number", "post");
	
	$sql = "UPDATE cse_injury
	SET `adj_number` = :adj_number
	WHERE `injury_id` = :injury_id
	AND `customer_id` = :customer_id";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $_SESSION["user_customer_id"]);
		$stmt->bindParam("injury_id", $injury_id);
		$stmt->bindParam("adj_number", $adj_number);
		
		$stmt->execute();
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>"true"));
		
		trackInjury("update", $injury_id);
	} catch(PDOException $e) {
		$error = array("sql"=>$sql, "error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
	}
}
function updateAppFilingId() {
	session_write_close();
	$jetfile_id = passed_var("jetfile_id", "post");
	$app_filing_id = passed_var("app_filing_id", "post");
	$app_filing_date = passed_var("app_filing_date", "post");
	$sql = "UPDATE cse_jetfile
	SET `app_filing_id` = :app_filing_id,
	`app_filing_date` = :app_filing_date
	WHERE `jetfile_id` = :jetfile_id
	AND `customer_id` = :customer_id";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $_SESSION["user_customer_id"]);
		$stmt->bindParam("app_filing_id", $app_filing_id);
		$stmt->bindParam("app_filing_date", $app_filing_date);
		$stmt->bindParam("jetfile_id", $jetfile_id);
		
		$stmt->execute();
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>"true"));
	} catch(PDOException $e) {
		$error = array("sql"=>$sql, "error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
	}
}
function updateDOR() {
	session_write_close();
	$jetfile_id = passed_var("jetfile_id", "post");
	$jetfile_case_id = passed_var("jetfile_case_id", "post");
	$jetfile_dor_id = passed_var("jetfile_dor_id", "post");
	
	$sql = "UPDATE cse_jetfile
	SET `jetfile_dor_id` = :jetfile_dor_id,
	`jetfile_case_id` = :jetfile_case_id
	WHERE `jetfile_id` = :jetfile_id
	AND `customer_id` = :customer_id";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("jetfile_case_id", $jetfile_case_id);
		$stmt->bindParam("jetfile_dor_id", $jetfile_dor_id);
		$stmt->bindParam("jetfile_id", $jetfile_id);
		$stmt->bindParam("customer_id", $_SESSION["user_customer_id"]);
		
		$stmt->execute();
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>"true"));
	} catch(PDOException $e) {
		$error = array("sql"=>$sql, "error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
	}
}
function updateDORFilingId() {
	session_write_close();
	$jetfile_id = passed_var("jetfile_id", "post");
	$dor_filing_id = passed_var("dor_filing_id", "post");
	$dor_filing_date = passed_var("dor_filing_date", "post");
	$sql = "UPDATE cse_jetfile
	SET `dor_filing_id` = :dor_filing_id,
	`dor_filing_date` = :dor_filing_date
	WHERE `jetfile_id` = :jetfile_id
	AND `customer_id` = :customer_id";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $_SESSION["user_customer_id"]);
		$stmt->bindParam("dor_filing_id", $dor_filing_id);
		$stmt->bindParam("dor_filing_date", $dor_filing_date);
		$stmt->bindParam("jetfile_id", $jetfile_id);
		
		$stmt->execute();
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>"true"));
	} catch(PDOException $e) {
		$error = array("sql"=>$sql, "error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
	}
}
function updateDORE() {
	session_write_close();
	$jetfile_id = passed_var("jetfile_id", "post");
	$jetfile_case_id = passed_var("jetfile_case_id", "post");
	$jetfile_dore_id = passed_var("jetfile_dore_id", "post");
	
	$sql = "UPDATE cse_jetfile
	SET `jetfile_dore_id` = :jetfile_dore_id,
	`jetfile_case_id` = :jetfile_case_id
	WHERE `jetfile_id` = :jetfile_id
	AND `customer_id` = :customer_id";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("jetfile_case_id", $jetfile_case_id);
		$stmt->bindParam("jetfile_dore_id", $jetfile_dore_id);
		$stmt->bindParam("jetfile_id", $jetfile_id);
		$stmt->bindParam("customer_id", $_SESSION["user_customer_id"]);
		
		$stmt->execute();
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>"true"));
	} catch(PDOException $e) {
		$error = array("sql"=>$sql, "error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
	}
}
function updateJetLien() {
	session_write_close();
	$jetfile_id = passed_var("jetfile_id", "post");
	$jetfile_case_id = passed_var("jetfile_case_id", "post");
	$jetfile_lien_id = passed_var("jetfile_lien_id", "post");
	
	$sql = "UPDATE cse_jetfile
	SET `jetfile_lien_id` = :jetfile_lien_id,
	`jetfile_case_id` = :jetfile_case_id
	WHERE `jetfile_id` = :jetfile_id
	AND `customer_id` = :customer_id";
	
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("jetfile_case_id", $jetfile_case_id);
		$stmt->bindParam("jetfile_lien_id", $jetfile_lien_id);
		$stmt->bindParam("jetfile_id", $jetfile_id);
		$stmt->bindParam("customer_id", $_SESSION["user_customer_id"]);
		
		$stmt->execute();
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>"true"));
	} catch(PDOException $e) {
		$error = array("sql"=>$sql, "error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
	}
}
function updateUnstruc() {
	session_write_close();
	$jetfile_id = passed_var("jetfile_id", "post");
	$case_id = passed_var("case_id", "post");
	$injury_id = passed_var("injury_id", "post");
	$jetfile_case_id = passed_var("jetfile_case_id", "post");
	$jetfile_lien_id = passed_var("jetfile_lien_id", "post");
	$unstruc_number = passed_var("unstruc_number", "post");
	$jetfile_unstruc_id = passed_var("jetfile_unstruc_id", "post");
	
	$kase = jetfileInfo($case_id, $injury_id);
	$unstruc_info = $kase->unstruc_info;
	
	if ($unstruc_info=="") {
		$error = array("error"=> array("text"=>"No info"));
		echo json_encode($error);
		die();
	}
	$arrDocuments = array();
	$unstrucs = json_decode($unstruc_info);
	foreach($unstrucs as &$unstruc) {
		if ($unstruc_number == ($unstruc->unstruc_number + 1)) {
			$unstruc->data->unstruc_id = $jetfile_unstruc_id;
			break;
		}
	}
	
	$unstruc_info = json_encode($unstrucs);
	
	$sql = "UPDATE cse_jetfile
	SET `unstruc_info` = :unstruc_info,
	`jetfile_case_id` = :jetfile_case_id
	WHERE `jetfile_id` = :jetfile_id
	AND `customer_id` = :customer_id";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("unstruc_info", $unstruc_info);
		$stmt->bindParam("jetfile_case_id", $jetfile_case_id);
		$stmt->bindParam("jetfile_id", $jetfile_id);
		$stmt->bindParam("customer_id", $_SESSION["user_customer_id"]);
		
		$stmt->execute();
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>"true"));
	} catch(PDOException $e) {
		$error = array("sql"=>$sql, "error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
	}
}
function updateUnstrucFilingId() {
	session_write_close();
	$jetfile_id = passed_var("jetfile_id", "post");
	$case_id = passed_var("case_id", "post");
	$injury_id = passed_var("injury_id", "post");
	$unstruc_filing_id = passed_var("unstruc_filing_id", "post");
	$unstruc_filing_date = passed_var("unstruc_filing_date", "post");
	$unstruc_number = passed_var("unstruc_number", "post");
	
	
	$kase = jetfileInfo($case_id, $injury_id);
	$unstruc_info = $kase->unstruc_info;
	
	if ($unstruc_info=="") {
		$error = array("error"=> array("text"=>"No info"));
		echo json_encode($error);
		die();
	}
	$arrDocuments = array();
	$unstrucs = json_decode($unstruc_info);
	foreach($unstrucs as &$unstruc) {
		if ($unstruc_number == ($unstruc->unstruc_number + 1)) {
			$unstruc->data->unstruc_filing_id = $unstruc_filing_id;
			$unstruc->data->unstruc_filing_date = $unstruc_filing_date;
			break;
		}
	}
	
	//die(print_r($unstrucs));
	
	$unstruc_info = json_encode($unstrucs);
	
	$sql = "UPDATE cse_jetfile
	SET `unstruc_info` = :unstruc_info,
	`jetfile_case_id` = :jetfile_case_id
	WHERE `jetfile_id` = :jetfile_id
	AND `customer_id` = :customer_id";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("unstruc_info", $unstruc_info);
		$stmt->bindParam("jetfile_case_id", $jetfile_case_id);
		$stmt->bindParam("jetfile_id", $jetfile_id);
		$stmt->bindParam("customer_id", $_SESSION["user_customer_id"]);
		
		$stmt->execute();
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>"true"));
	} catch(PDOException $e) {
		$error = array("sql"=>$sql, "error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
	}
}
function jetfileInfo($case_id, $injury_id) {
	session_write_close();
	$sql = "SELECT ccase.case_id id, ccase.case_uuid uuid, ccase.lien_filed, ccase.case_number, ccase.cpointer,
		inj.injury_id, inj.adj_number, inj.occupation, inj.start_date, inj.end_date, inj.full_address, inj.street, inj.city, inj.state, inj.zip,
		ccase.case_date, ccase.case_type, venue.venue_uuid, ccase.rating, ccase.medical, ccase.td, ccase.rehab,  ccase.edd, ccase.claims, ccase.injury_type,
		
		venue_corporation.corporation_id venue_id, venue.venue_uuid, IF(venue.venue IS NULL, '', venue.venue) venue, venue_abbr, 
		venue_corporation.street venue_street, venue_corporation.city venue_city, 
		venue_corporation.state venue_state, venue_corporation.zip venue_zip,
		
		ccase.case_status, ccase.case_substatus, ccase.case_subsubstatus, ccase.submittedOn, ccase.supervising_attorney,
ccase.attorney, ccase.worker, ccase.interpreter_needed, ccase.case_language `case_language`, 
		app.deleted applicant_deleted, app.person_id applicant_id, app.person_uuid applicant_uuid, app.salutation applicant_salutation, IFNULL(app.full_name, '') `full_name`, app.first_name, app.last_name, app.middle_name, app.`aka`, 
		app.dob, app.gender, app.ssn, app.full_address applicant_full_address, app.street applicant_street, app.suite applicant_suite, app.city applicant_city, app.state applicant_state, app.zip applicant_zip, app.phone applicant_phone, app.fax applicant_fax, app.cell_phone applicant_cell, app.age applicant_age,
		
		IFNULL(employer.`corporation_id`,-1) employer_id, employer.company_name employer, employer.full_name employer_full_name, employer.full_address employer_full_address, employer.street employer_street, employer.city employer_city,
		employer.state employer_state, employer.zip employer_zip,
		
		IFNULL(defendant.`corporation_id`,-1) defendant_id, defendant.company_name defendant, defendant.full_name defendant_full_name, defendant.street defendant_street, defendant.city defendant_city,
		defendant.state defendant_state, defendant.zip defendant_zip,
		
		CONCAT(app.first_name,' ', IF(app.middle_name='','',CONCAT(app.middle_name,' ')),app.last_name,' vs ', IFNULL(employer.`company_name`, '')) `name`, 
		
		IFNULL(att.user_id, '') as attorney_id, 
		IFNULL(att.nickname, '') as attorney_name, 
		IFNULL(att.user_first_name, '') as attorney_first_name, 
		IFNULL(att.user_last_name, '') as attorney_last_name, 
		IFNULL(att.user_name, '') as attorney_full_name, 
		IFNULL(att.user_email, '') as attorney_email, 
		IFNULL(user.nickname, '') as worker_name, IFNULL(user.user_name, '') as worker_full_name, IFNULL(user.user_email, '') as worker_email,
		IFNULL(lien.lien_id, -1) lien_id, 
		IFNULL(settlement.settlement_id, -1) settlement_id,
		IF (cif.fee_uuid IS NOT NULL, '1', '-1') fee_id,
		job.job_id worker_job_id, job.job_uuid worker_job_uuid, if(job.job IS NULL, '', job.job) worker_job,
		IFNULL(jfile.jetfile_id, '') jetfile_id, 
		IFNULL(jfile.jetfile_case_id, '') jetfile_case_id, 
		IFNULL(jfile.app_filing_id, '') app_filing_id, 
		IFNULL(jfile.info, '') jetfile_info, 
		IFNULL(jfile.dor_info, '') dor_info, 
		IFNULL(jfile.jetfile_dor_id, '') jetfile_dor_id, 
		IFNULL(jfile.dor_filing_id, '') dor_filing_id, 
		IFNULL(jfile.dore_info, '') dore_info, 
		IFNULL(jfile.jetfile_dore_id, '') jetfile_dore_id, 
		IFNULL(jfile.dore_filing_id, '') dore_filing_id, 
		IFNULL(jfile.lien_info, '') lien_info,
		IFNULL(jfile.jetfile_lien_id, '') jetfile_lien_id,
		IFNULL(jfile.lien_filing_id, '') lien_filing_id,
		IFNULL(jfile.unstruc_info, '') unstruc_info,
		IFNULL(uploads.document_count, 0) uploads_count
		FROM cse_case ccase ";

		if (isset($_SESSION["restricted_clients"])) {
			$restricted_clients = $_SESSION["restricted_clients"];
			
			if ($restricted_clients!="") {
				//$restricted_clients = "'" . str_replace(",", "','", $restricted_clients) . "'";
				$sql .= " INNER JOIN (
						SELECT DISTINCT ccorp.case_uuid
						FROM cse_case_corporation ccorp
						INNER JOIN cse_corporation corp
						ON ccorp.corporation_uuid = corp.corporation_uuid
						where corp.parent_corporation_uuid IN (" . $restricted_clients . ")
					) restricteds
					ON ccase.case_uuid = restricteds.case_uuid";
			}
		}
		
		$sql .= " 
		LEFT OUTER JOIN cse_case_person ccapp ON (ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N')
		LEFT OUTER JOIN ";
	if (($_SESSION['user_customer_id']==1033)) { 
	$sql .= "(" . SQL_PERSONX . ")";
	} else {
	$sql .= "cse_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
			LEFT OUTER JOIN `cse_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			
			LEFT OUTER JOIN `cse_case_corporation` dcorp
			ON (ccase.case_uuid = dcorp.case_uuid AND ccorp.attribute = 'defendant' AND dcorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` defendant
			ON dcorp.corporation_uuid = defendant.corporation_uuid
			
			LEFT OUTER JOIN `cse_case_corporation` ccorp_venue
			ON (ccase.case_uuid = ccorp_venue.case_uuid AND ccorp_venue.attribute = 'venue' AND ccorp_venue.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` venue_corporation
			ON ccorp_venue.corporation_uuid = venue_corporation.corporation_uuid
			
			INNER JOIN `cse_case_injury` cinj
			ON ccase.case_uuid = cinj.case_uuid
			INNER JOIN `cse_injury` inj
			ON cinj.injury_uuid = inj.injury_uuid
			
			LEFT OUTER JOIN `cse_jetfile` jfile
			ON inj.injury_uuid = jfile.injury_uuid
			
			LEFT OUTER JOIN `cse_injury_lien` cil
			ON inj.injury_uuid = cil.injury_uuid
			LEFT OUTER JOIN `cse_injury_fee` cif ON inj.injury_uuid = cif.injury_uuid AND cif.deleted = 'N'
			LEFT OUTER JOIN `cse_lien` lien
			ON cil.lien_uuid = lien.lien_uuid
			LEFT OUTER JOIN `cse_injury_settlement` cis
			ON inj.injury_uuid = cis.injury_uuid AND cis.deleted = 'N' AND cis.`attribute` = 'main'
			LEFT OUTER JOIN `cse_settlement` settlement
			ON cis.settlement_uuid = settlement.settlement_uuid
			
			LEFT OUTER JOIN ikase.`cse_user` att
			ON ccase.attorney = att.user_id
			LEFT OUTER JOIN ikase.`cse_user` user
			ON ccase.worker = user.user_id
			
			LEFT OUTER JOIN ikase.`cse_user_job` cjob
			ON (user.user_uuid = cjob.user_uuid AND cjob.deleted = 'N')
			LEFT OUTER JOIN ikase.`cse_job` job
			ON cjob.job_uuid = job.job_uuid
			
			LEFT OUTER JOIN (
				SELECT ccase.case_id, COUNT(document_id) document_count
				FROM cse_document cd
				INNER JOIN cse_case_document ccd
				ON cd.document_uuid = ccd.document_uuid
				INNER JOIN cse_case ccase
				ON ccd.case_uuid = ccase.case_uuid
				WHERE 1
				AND `attribute_1` = 'jetfiler'
				AND cd.deleted = 'N'
				AND ccase.case_id = :case_id
				AND ccase.customer_id = :customer_id
			) uploads
			ON ccase.case_id = uploads.case_id
			
			WHERE 1
			AND inj.injury_id=:injury_id
			AND ccase.case_id=:case_id
			AND ccase.customer_id = :customer_id";
	
	
	if ($_SERVER['REMOTE_ADDR']=='47.153.49.248') {
	//die(print_r($_POST));
	}	
	
	$customer_id = $_SESSION['user_customer_id'];
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("injury_id", $injury_id);
		$stmt->bindParam("customer_id", $customer_id);
		
		$stmt->execute();
		$kase = $stmt->fetchObject();
		//die("count:" . print_r($kase));
		$stmt->closeCursor(); $stmt = null; $db = null;

		
		return $kase;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
	}
}
function updateJetfileId() {
	session_write_close();
	
	$jetfile_id = passed_var("jetfile_id", "post");
	
	if (!is_numeric($jetfile_id)) {
		die(json_encode(array("failure"=>"no id")));
	}
	$customer_id = $_SESSION["user_customer_id"];
	
	$sql = "UPDATE ikase.cse_customer
	SET jetfile_id = :jetfile_id
	WHERE customer_id = :customer_id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("jetfile_id", $jetfile_id);
		$stmt->bindParam("customer_id", $customer_id);
		
		$stmt->execute();
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>"true"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
	}
}
function checkDOR() {
	session_write_close();
	
	//before we look on cajetfile, check local
	$case_id = passed_var("case_id", "post");
	$jetfile_id = "-1";
	if ($case_id!="") {
		$injury_id = passed_var("injury_id", "post");
		
		$kase = jetfileInfo($case_id, $injury_id);
		$jetfile_id = $kase->jetfile_id;
		//die(print_r($kase));
		//let's look up the customer to see if , they have a jetfile id
		$customer = getCustomerInfo();
		if ($kase->jetfile_dor_id != "0") {
			die(json_encode(array("case_id"=>$kase->jetfile_case_id, "jetfile_id"=>$jetfile_id, "jetfile_dor_id"=>$kase->jetfile_dor_id, "dor_filing_id"=>$kase->dor_filing_id)));
		}
	}
	$url = "https://www.cajetfile.com/ikase/check_dor.php";
	
	if ($customer->jetfile_id < 1 && $customer->eams_no == "") {
		//die(json_encode(array("failure"=>"no jetfile id")));
		echo json_encode(array("case_id"=>"-1", "jetfile_id"=>$jetfile_id, "adj_number"=>"", "failure"=>"no jetfile id"));
		die();
	}
	$dob = passed_var("dob", "post");
	if ($dob=="") {
		echo json_encode(array("case_id"=>"-1", "jetfile_id"=>"-1", "adj_number"=>"", "failure"=>"dob"));
		die();
	}
	$dob = date("Y-m-d", strtotime($dob));
	$fields = array("cus_id"=>$customer->jetfile_id, "eams_no"=>$customer->eams_no, 'dob'=>$dob, 'ssn'=>passed_var("ssn", "post"), 'start'=>passed_var("start", "post"), 'end'=>passed_var("end", "post"));
	
	$result = post_curl($url, $fields);
	//return the json directly back to the view
	die($result);
}
function checkDORE() {
	session_write_close();
	
	//before we look on cajetfile, check local
	$case_id = passed_var("case_id", "post");
	$jetfile_id = "-1";
	if ($case_id!="") {
		$injury_id = passed_var("injury_id", "post");
		
		$kase = jetfileInfo($case_id, $injury_id);
		$jetfile_id = $kase->jetfile_id;
		//die(print_r($kase));
		//let's look up the customer to see if , they have a jetfile id
		$customer = getCustomerInfo();
		if ($kase->jetfile_dore_id != "0") {
			die(json_encode(array("case_id"=>$kase->jetfile_case_id, "jetfile_id"=>$jetfile_id, "jetfile_dore_id"=>$kase->jetfile_dore_id, "dore_filing_id"=>$kase->dore_filing_id)));
		}
	}
	$url = "https://www.cajetfile.com/ikase/check_dore.php";
	
	if ($customer->jetfile_id < 1 && $customer->eams_no == "") {
		//die(json_encode(array("failure"=>"no jetfile id")));
		echo json_encode(array("case_id"=>"-1", "jetfile_id"=>$jetfile_id, "adj_number"=>"", "failure"=>"no jetfile id"));
		die();
	}
	$dob = passed_var("dob", "post");
	if ($dob=="") {
		echo json_encode(array("case_id"=>"-1", "jetfile_id"=>"-1", "adj_number"=>"", "failure"=>"dob"));
		die();
	}
	$dob = date("Y-m-d", strtotime($dob));
	$fields = array("cus_id"=>$customer->jetfile_id, "eams_no"=>$customer->eams_no, 'dob'=>$dob, 'ssn'=>passed_var("ssn", "post"), 'start'=>passed_var("start", "post"), 'end'=>passed_var("end", "post"));
	
	//die(print_r($fields));
	$result = post_curl($url, $fields);
	//return the json directly back to the view
	die($result);
}
function checkApplicant() {
	session_write_close();

	//before we look on cajetfile, check local
	$case_id = passed_var("case_id", "post");
	if ($case_id!="") {
		$injury_id = passed_var("injury_id", "post");
		
		$kase = jetfileInfo($case_id, $injury_id);
		
		if (!is_object($kase)) {
			return false;
		}
		
		if ($kase->uploads_count < 4) {
			if ($kase->jetfile_case_id=="") {
				$kase->jetfile_case_id = -1;
			}
			die(json_encode(array("case_id"=>$kase->jetfile_case_id, "injury_id"=>$injury_id, "adj_number"=>"", "uploads_count"=>$kase->uploads_count)));
		}
		//let's look up the customer to see if , they have a jetfile id
		$customer = getCustomerInfo();
		if ($kase->app_filing_id != "0") {
			die(json_encode(array("injury_id"=>$injury_id, "case_id"=>$kase->jetfile_case_id, "adj_number"=>$kase->adj_number, "cus_id"=>$customer->jetfile_id, "app_filing_id"=>$kase->app_filing_id)));
		}
	}
	$url = "https://www.cajetfile.com/ikase/check_applicant.php";
	
	if ($customer->jetfile_id < 1 && $customer->eams_no == "") {
		die(json_encode(array("injury_id"=>$injury_id, "adj_number"=>"", "failure"=>"no jetfile id")));
	}
	$dob = passed_var("dob", "post");
	if ($dob=="") {
		die(json_encode(array("failure"=>"dob")));
	}
	$dob = date("Y-m-d", strtotime($dob));
	
	$fields = array("cus_id"=>$customer->jetfile_id, "eams_no"=>$customer->eams_no, 'dob'=>$dob, 'ssn'=>passed_var("ssn", "post"), 'start'=>passed_var("start", "post"), 'end'=>passed_var("end", "post"));
	
	//die(print_r($fields));
	$result = post_curl($url, $fields);
	//return the json directly back to the view
	//die($result);
	if ($result!="") {
		$json = json_decode($result);
		//die(print_r($json));
		$json->injury_id = $injury_id;
		$json->uploads_count = $kase->uploads_count;
		die(json_encode($json));
	}
}
function checkFiling($filing_id, $blnReturn = false) {
	session_write_close();
	$url = "https://www.cajetfile.com/ikase/check_status.php";
	
	//let's look up the customer to see if they have a jetfile id
	$customer = getCustomerInfo();
	
	if ($customer->jetfile_id < 1 && $customer->eams_no == "") {
		die(json_encode(array("failure"=>"no jetfile id")));
	}
	
	$fields = array("cus_id"=>$customer->jetfile_id, "filing_id"=>$filing_id);
	
	//die(print_r($fields));
	$result = post_curl($url, $fields);
	
	if ($blnReturn) {
		return $result;
	} else {
		//return the json directly back to the view
		die($result);
	}
	//$json = json_decode($result);
	//die(print_r($json));
}
function appFile() {
	session_write_close();
	
	$cus_id = $_SESSION['user_customer_id'];
	$jetfile_case_id = passed_var("jetfile_case_id", "post");
	
	$url = "https://www.cajetfile.com/ikase/file.php";
	
	$customer = getCustomerInfo();
	
	$fields = array("cus_id"=>$customer->jetfile_id, 'jetfile_case_id'=>$jetfile_case_id);
	
	//echo $url; die(print_r($fields));
	$result = post_curl($url, $fields);
	//return the json directly back to the view
	die($result);
}
function dorFile() {
	session_write_close();
	
	$cus_id = $_SESSION['user_customer_id'];
	$jetfile_case_id = passed_var("jetfile_case_id", "post");
	$jetfile_dor_id = passed_var("jetfile_dor_id", "post");
	
	$url = "https://www.cajetfile.com/ikase/filedor.php";
	
	$customer = getCustomerInfo();
	
	$fields = array("cus_id"=>$customer->jetfile_id, 'jetfile_case_id'=>$jetfile_case_id, 'jetfile_dor_id'=>$jetfile_dor_id);
	
	//echo $url; die(print_r($fields));
	$result = post_curl($url, $fields);
	//return the json directly back to the view
	die($result);
}
function sendDOR() {
	session_write_close();
	
	$cus_id = $_SESSION['user_customer_id'];
	$case_id = passed_var("case_id", "post");
	$injury_id = passed_var("injury_id", "post");
	$jetfile_id = passed_var("jetfile_id", "post");
	$jetfile_case_id = passed_var("jetfile_case_id", "post");
	
	if ($case_id == "" || $case_id < 0 || !is_numeric($case_id)) {
		die();
	}
	if ($injury_id == "" || $injury_id < 0 || !is_numeric($injury_id)) {
		die();
	}
	//for page 2, we need to have saved page 1
	if ($jetfile_id == "" || $jetfile_id < 0 || !is_numeric($jetfile_id)) {
		die();
	}
	
	//get uploads
	$sql = "SELECT `document_id` id, `document_name` `name`, `document_filename` `filepath`
	FROM cse_document doc
	INNER JOIN cse_case_document ccd
	ON doc.document_uuid = ccd.document_uuid
	INNER JOIN cse_case ccase
	ON ccd.case_uuid = ccase.case_uuid
	WHERE `type` = 'DOR' 
	AND `document_filename` != ''
	AND case_id = :case_id
	AND `doc`.customer_id = :cus_id
	AND `doc`.deleted = 'N'";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("cus_id", $cus_id);
		$stmt->execute();
		$documents = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
	}
	
	//die(print_r($documents));
	
	$uploads = "";
	$minimum_files = 3;
	$number_files = count($documents);
	if ($number_files < $minimum_files) {
		$error = array("error"=> array("text"=>($minimum_files - $number_files) . " Document(s) Required"));
		echo json_encode($error);
		die();
	}
	$arrDocuments = array();
	//die(print_r($documents));
	foreach($documents as $index=>$document) {
		$filepath = $document->filepath;
		$filepath = str_replace("D:/uploads/" . $cus_id . "/" . $case_id . "/jetfiler/", "", $filepath);
		$filepath = str_replace("../" . $cus_id . "/" . $case_id . "/jetfiler/", "", $filepath);
		
		//$path = "D:/uploads/" . $cus_id . "/jetfiler/" . $case_id . "/" . $document->filepath;
		$path = "C:\\inetpub\\wwwroot\\ikase.org\\uploads\\" . $cus_id . "\\" . $case_id . "\\jetfiler\\" . $filepath;
		
		$data = file_get_contents($path);
		$base64 = base64_encode($data);
		
		$arrDocuments[] = array("name"=>$document->name, "base64"=>$base64);
	}
	
	$kase = jetfileInfo($case_id, $injury_id);
	
	//die(print_r($kase));
	//NEED TO SEND APPLICANT AND EMPLOYER STUFF TOO IN CASE THERE IS NO BASIC CASE IN CAJETFILE
	//also injury dates for cover sheet
	$specific_injury_date = "";
	$ct_injury_start_date = "";
	$ct_injury_end_date= "";
	$injury_type = "";
	if ($kase->end_date=="0000-00-00") {
		if ($kase->start_date!="0000-00-00") {
			$injury_type = "S";
			$specific_injury_date = $kase->start_date;
		}
	}
	if ($specific_injury_date == "") {
		if ($kase->end_date!="0000-00-00") {
			$injury_type = "C";
			$ct_injury_start_date = $kase->start_date;
			$ct_injury_end_date= $kase->end_date;
		}
	}
	
	$arrPage = array("page1"=>array("adj_number"=>$kase->adj_number, "first_name"=>$kase->first_name, "last_name"=>$kase->last_name, "middle_name"=>$kase->middle_name, "applicant_street"=>$kase->applicant_street, "applicant_suite"=>$kase->applicant_suite, "applicant_city"=>$kase->applicant_city, "applicant_state"=>$kase->applicant_state, "applicant_zip"=>$kase->applicant_zip, "employer"=>$kase->employer, "employer_street"=>$kase->employer_street, "employer_city"=>$kase->employer_city, "employer_state"=>$kase->employer_state, "employer_zip"=>$kase->employer_zip, "dob"=>$kase->dob, "ssn"=>$kase->ssn, "injury_type"=>$injury_type, "specific_injury_date"=>$specific_injury_date, "ct_injury_start_date"=>$ct_injury_start_date, "ct_injury_end_date"=>$ct_injury_end_date));
	
	//die(print_r($arrPage));
	$kase->jetfile_info = json_encode($arrPage);
	/*
	if ($jetfile_case_id=="" || $jetfile_case_id=="0") {
		$blnNoCase = (!is_object($kase->jetfile_info));
		if ($blnNoCase) {
			$blnNoCase = ($kase->jetfile_info=="");
		}
		if ($blnNoCase) {
			//need to build it up
			//applicant
			//employer
			$kase->jetfile_info = json_encode($arrPage);
		}
	} else {
		//already have a case, do we have a page1?
		//if ($kase->jetfile_info=="") {
			$kase->jetfile_info = json_encode($arrPage);
		//}
	}
	*/
	//die(print_r($kase));
	
	$url = "https://www.cajetfile.com/ikase/receivedor.php";
		
	//let's look up the customer to see if they have a jetfile id
	$customer = getCustomerInfo();
	$fields = array("cus_id"=>$customer->jetfile_id, 'case_id'=>$kase->id, 'jetfile_case_id'=>$jetfile_case_id, 'data'=>$kase->jetfile_info, 'dordata'=>$kase->dor_info,'documents'=>json_encode($arrDocuments));
	
	//die(print_r($fields));
	$result = post_curl($url, $fields);
	//die($result);
	$operation = "send";
	$activity = "Sent to EAMS for DOR request";
	$case_uuid = $kase->uuid;
	$activity_category = "EAMS Submission";
	$billing_time = 0;
	recordActivity($operation, $activity, $case_uuid, -1, $activity_category, $billing_time);
	
	//return the json directly back to the view
	die($result);
}
function sendDORE() {
	session_write_close();
	
	$cus_id = $_SESSION['user_customer_id'];
	$case_id = passed_var("case_id", "post");
	$injury_id = passed_var("injury_id", "post");
	$jetfile_id = passed_var("jetfile_id", "post");
	$jetfile_case_id = passed_var("jetfile_case_id", "post");
	
	if ($case_id == "" || $case_id < 0 || !is_numeric($case_id)) {
		die();
	}
	if ($injury_id == "" || $injury_id < 0 || !is_numeric($injury_id)) {
		die();
	}
	//for page 2, we need to have saved page 1
	if ($jetfile_id == "" || $jetfile_id < 0 || !is_numeric($jetfile_id)) {
		die();
	}
	
	//get uploads
	$sql = "SELECT `document_id` id, `document_name` `name`, `document_filename` `filepath`
	FROM cse_document doc
	INNER JOIN cse_case_document ccd
	ON doc.document_uuid = ccd.document_uuid
	INNER JOIN cse_case ccase
	ON ccd.case_uuid = ccase.case_uuid
	WHERE `type` = 'DORE' 
	AND `document_filename` != ''
	AND case_id = :case_id
	AND `doc`.customer_id = :cus_id
	AND `doc`.deleted = 'N'";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("cus_id", $cus_id);
		$stmt->execute();
		$documents = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
	}
	
	//die(print_r($documents));
	
	$uploads = "";
	$minimum_files = 2;
	$number_files = count($documents);
	if ($number_files < $minimum_files) {
		$error = array("error"=> array("text"=>($minimum_files - $number_files) . " Document(s) Required"));
		echo json_encode($error);
		die();
	}
	$arrDocuments = array();
	//die(print_r($documents));
	foreach($documents as $index=>$document) {
		//$path = "D:/uploads/" . $cus_id . "/jetfiler/" . $case_id . "/" . $document->filepath;
		//$path = "C:\\inetpub\\wwwroot\\ikase.org\\uploads\\" . $cus_id . "\\" . $case_id . "\\jetfiler\\" . $document->filepath;
		
		$filepath = $document->filepath;
		$filepath = str_replace("D:/uploads/" . $cus_id . "/" . $case_id . "/jetfiler/", "", $filepath);
		$filepath = str_replace("../" . $cus_id . "/" . $case_id . "/jetfiler/", "", $filepath);
		
		//$path = "D:/uploads/" . $cus_id . "/jetfiler/" . $case_id . "/" . $document->filepath;
		$path = "C:\\inetpub\\wwwroot\\ikase.org\\uploads\\" . $cus_id . "\\" . $case_id . "\\jetfiler\\" . $filepath;
		
		$data = file_get_contents($path);
		$base64 = base64_encode($data);
		
		$arrDocuments[] = array("name"=>$document->name, "base64"=>$base64);
	}
	
	$kase = jetfileInfo($case_id, $injury_id);
	
	//die(print_r($kase));
	//NEED TO SEND APPLICANT AND EMPLOYER STUFF TOO IN CASE THERE IS NO BASIC CASE IN CAJETFILE
	//also injury dates for cover sheet
	$specific_injury_date = "";
	$ct_injury_start_date = "";
	$ct_injury_end_date= "";
	$injury_type = "";
	if ($kase->end_date=="0000-00-00") {
		if ($kase->start_date!="0000-00-00") {
			$injury_type = "S";
			$specific_injury_date = $kase->start_date;
		}
	}
	if ($specific_injury_date == "") {
		if ($kase->end_date!="0000-00-00") {
			$injury_type = "C";
			$ct_injury_start_date = $kase->start_date;
			$ct_injury_end_date= $kase->end_date;
		}
	}
	
	$arrPage = array("page1"=>array("adj_number"=>$kase->adj_number, "first_name"=>$kase->first_name, "last_name"=>$kase->last_name, "middle_name"=>$kase->middle_name, "applicant_street"=>$kase->applicant_street, "applicant_suite"=>$kase->applicant_suite, "applicant_city"=>$kase->applicant_city, "applicant_state"=>$kase->applicant_state, "applicant_zip"=>$kase->applicant_zip, "employer"=>$kase->employer, "employer_street"=>$kase->employer_street, "employer_city"=>$kase->employer_city, "employer_state"=>$kase->employer_state, "employer_zip"=>$kase->employer_zip, "dob"=>$kase->dob, "ssn"=>$kase->ssn, "injury_type"=>$injury_type, "specific_injury_date"=>$specific_injury_date, "ct_injury_start_date"=>$ct_injury_start_date, "ct_injury_end_date"=>$ct_injury_end_date));
	
	if ($jetfile_case_id=="" || $jetfile_case_id=="0") {
		$blnNoCase = (!is_object($kase->jetfile_info));
		if ($blnNoCase) {
			$blnNoCase = ($kase->jetfile_info=="");
		}
		if ($blnNoCase) {
			//need to build it up
			//applicant
			//employer
			$kase->jetfile_info = json_encode($arrPage);
		}
	} else {
		//already have a case, do we have a page1?
		if ($kase->jetfile_info=="") {
			$kase->jetfile_info = json_encode($arrPage);
		}
	}
	
	//die(print_r($kase));
	
	$url = "https://www.cajetfile.com/ikase/receivedore.php";
		
	//let's look up the customer to see if they have a jetfile id
	$customer = getCustomerInfo();
	$fields = array("cus_id"=>$customer->jetfile_id, 'case_id'=>$kase->id, 'jetfile_case_id'=>$jetfile_case_id, 'data'=>$kase->jetfile_info, 'doredata'=>$kase->dore_info,'documents'=>json_encode($arrDocuments));
	
	//die(print_r($fields));
	$result = post_curl($url, $fields);
	
	$operation = "send";
	$activity = "Sent to EAMS for DORE request";
	$case_uuid = $kase->uuid;
	$activity_category = "EAMS Submission";
	$billing_time = 0;
	recordActivity($operation, $activity, $case_uuid, -1, $activity_category, $billing_time);
	
	//return the json directly back to the view
	die($result);
}
function doreFile() {
	session_write_close();
	
	$cus_id = $_SESSION['user_customer_id'];
	$jetfile_case_id = passed_var("jetfile_case_id", "post");
	$jetfile_dore_id = passed_var("jetfile_dore_id", "post");
	
	$url = "https://www.cajetfile.com/ikase/filedore.php";
	
	$customer = getCustomerInfo();
	
	$fields = array("cus_id"=>$customer->jetfile_id, 'jetfile_case_id'=>$jetfile_case_id, 'jetfile_dore_id'=>$jetfile_dore_id);
	
	//echo $url; die(print_r($fields));
	$result = post_curl($url, $fields);
	//return the json directly back to the view
	die($result);
}
function sendLien() {
	session_write_close();
	
	$cus_id = $_SESSION['user_customer_id'];
	$case_id = passed_var("case_id", "post");
	$injury_id = passed_var("injury_id", "post");
	$jetfile_id = passed_var("jetfile_id", "post");
	$jetfile_case_id = passed_var("jetfile_case_id", "post");
	
	if ($case_id == "" || $case_id < 0 || !is_numeric($case_id)) {
		die();
	}
	if ($injury_id == "" || $injury_id < 0 || !is_numeric($injury_id)) {
		die();
	}
	//for page 2, we need to have saved page 1
	if ($jetfile_id == "" || $jetfile_id < 0 || !is_numeric($jetfile_id)) {
		die();
	}
	
	//get uploads
	$sql = "SELECT `document_id` id, `description` `name`, `document_filename` `filepath`
	FROM cse_document doc
	INNER JOIN cse_case_document ccd
	ON doc.document_uuid = ccd.document_uuid
	INNER JOIN cse_case ccase
	ON ccd.case_uuid = ccase.case_uuid
	WHERE `type` = 'lien' 
	AND `document_filename` != ''
	AND case_id = :case_id
	AND `doc`.customer_id = :cus_id
	AND `doc`.deleted = 'N'";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("cus_id", $cus_id);
		$stmt->execute();
		$documents = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
	}
	
	//die(print_r($documents));
	
	$uploads = "";
	$minimum_files = 3;
	$number_files = count($documents);
	if ($number_files < $minimum_files) {
		$error = array("error"=> array("text"=>($minimum_files - $number_files) . " Document(s) Required"));
		echo json_encode($error);
		die();
	}
	$arrDocuments = array();
	//die(print_r($documents));
	foreach($documents as $index=>$document) {
		//$path = "D:/uploads/" . $cus_id . "/jetfiler/" . $case_id . "/" . $document->filepath;
		//$path = "C:\\inetpub\\wwwroot\\ikase.org\\uploads\\" . $cus_id . "\\" . $case_id . "\\jetfiler\\" . $document->filepath;
		$filepath = $document->filepath;
		$filepath = str_replace("D:/uploads/" . $cus_id . "/" . $case_id . "/jetfiler/", "", $filepath);
		$filepath = str_replace("../" . $cus_id . "/" . $case_id . "/jetfiler/", "", $filepath);
		
		//$path = "D:/uploads/" . $cus_id . "/jetfiler/" . $case_id . "/" . $document->filepath;
		$path = "C:\\inetpub\\wwwroot\\ikase.org\\uploads\\" . $cus_id . "\\" . $case_id . "\\jetfiler\\" . $filepath;
		
		$data = file_get_contents($path);
		$base64 = base64_encode($data);
		
		$arrDocuments[] = array("name"=>$document->name, "base64"=>$base64);
	}
	
	$kase = jetfileInfo($case_id, $injury_id);
	
	//die(print_r($kase));
	//also injury dates for cover sheet
	$specific_injury_date = "";
	$ct_injury_start_date = "";
	$ct_injury_end_date= "";
	$injury_type = "";
	if ($kase->end_date=="0000-00-00") {
		if ($kase->start_date!="0000-00-00") {
			$injury_type = "S";
			$specific_injury_date = $kase->start_date;
		}
	}
	if ($specific_injury_date == "") {
		if ($kase->end_date!="0000-00-00") {
			$injury_type = "C";
			$ct_injury_start_date = $kase->start_date;
			$ct_injury_end_date= $kase->end_date;
		}
	}
	
	//NEED TO SEND APPLICANT AND EMPLOYER STUFF TOO IN CASE THERE IS NO BASIC CASE IN CAJETFILE
	if ($jetfile_case_id=="" || $jetfile_case_id=="0") {
		$blnNoCase = (!is_object($kase->jetfile_info));
		if ($blnNoCase) {
			$blnNoCase = ($kase->jetfile_info=="");
		}
		if ($blnNoCase) {
			//need to build it up
			//applicant
			//employer
			$arrPage = array("page1"=>array("adj_number"=>$kase->adj_number, "first_name"=>$kase->first_name, "last_name"=>$kase->last_name, "middle_name"=>$kase->middle_name, "applicant_street"=>$kase->applicant_street, "applicant_suite"=>$kase->applicant_suite, "applicant_city"=>$kase->applicant_city, "applicant_state"=>$kase->applicant_state, "applicant_zip"=>$kase->applicant_zip, "employer"=>$kase->employer, "employer_street"=>$kase->employer_street, "employer_city"=>$kase->employer_city, "employer_state"=>$kase->employer_state, "employer_zip"=>$kase->employer_zip, "dob"=>$kase->dob, "ssn"=>$kase->ssn, "injury_type"=>$injury_type, "specific_injury_date"=>$specific_injury_date, "ct_injury_start_date"=>$ct_injury_start_date, "ct_injury_end_date"=>$ct_injury_end_date));
			
			$kase->jetfile_info = json_encode($arrPage);
		}
	}
	
	//die(print_r($kase));
	
	$url = "https://www.cajetfile.com/ikase/receivelien.php";
		
	//let's look up the customer to see if they have a jetfile id
	$customer = getCustomerInfo();
	$fields = array("cus_id"=>$customer->jetfile_id, 'case_id'=>$kase->id, 'jetfile_case_id'=>$jetfile_case_id, 'data'=>$kase->jetfile_info, 'liendata'=>$kase->lien_info,'documents'=>json_encode($arrDocuments));
	
	//die(print_r($fields));
	$result = post_curl($url, $fields);
	
	$operation = "send";
	$activity = "Sent to EAMS for LIEN request";
	$case_uuid = $kase->uuid;
	$activity_category = "EAMS Submission";
	$billing_time = 0;
	recordActivity($operation, $activity, $case_uuid, -1, $activity_category, $billing_time);
	//return the json directly back to the view
	die($result);
}
function sendUnstruc() {
	session_write_close();
	
	$cus_id = $_SESSION['user_customer_id'];
	$case_id = passed_var("case_id", "post");
	$injury_id = passed_var("injury_id", "post");
	$jetfile_id = passed_var("jetfile_id", "post");
	
	$unstruc_number = passed_var("unstruc_number", "post");
	
	if ($case_id == "" || $case_id < 0 || !is_numeric($case_id)) {
		die();
	}
	if ($injury_id == "" || $injury_id < 0 || !is_numeric($injury_id)) {
		die();
	}
	//
	if ($jetfile_id == "" || $jetfile_id < 0 || !is_numeric($jetfile_id)) {
		die();
	}
	
	$kase = jetfileInfo($case_id, $injury_id);
	$jetfile_case_id = $kase->jetfile_case_id;	//", "post");
	$unstruc_info = $kase->unstruc_info;
	
	if ($unstruc_info=="") {
		$error = array("error"=> array("text"=>"No info"));
		echo json_encode($error);
		die();
	}
	$arrDocuments = array();
	$unstrucs = json_decode($unstruc_info);
	//die(print_r($unstrucs));
	foreach($unstrucs as $unstruc) {
		//die(print_r($unstruc->data));
		if ($unstruc_number == ($unstruc->data->unstruc_number)) {
			//found it				
			//$path = "C:\\inetpub\\wwwroot\\ikase.org\\uploads\\" . $cus_id . "\\" . $case_id . "\\jetfiler\\" . $unstruc->data->filepath;
			$filepath = $unstruc->data->filepath;
			$filepath = str_replace("D:/uploads/" . $cus_id . "/" . $case_id . "/jetfiler/", "", $filepath);
			$filepath = str_replace("../" . $cus_id . "/" . $case_id . "/jetfiler/", "", $filepath);
			
			//$path = "D:/uploads/" . $cus_id . "/jetfiler/" . $case_id . "/" . $document->filepath;
			$path = "C:\\inetpub\\wwwroot\\ikase.org\\uploads\\" . $cus_id . "\\" . $case_id . "\\jetfiler\\" . $filepath;
			
			//die($path);
			$data = file_get_contents($path);
			$base64 = base64_encode($data);
			
			$arrDocuments[] = array("name"=>$unstruc->data->document_title, "base64"=>$base64);
			
			break;
		}
	}
	//die("wait");
	//die(print_r($arrDocuments));
	
	$specific_injury_date = "";
	$ct_injury_start_date = "";
	$ct_injury_end_date= "";
	$injury_type = "";
	if ($kase->end_date=="0000-00-00") {
		if ($kase->start_date!="0000-00-00") {
			$injury_type = "S";
			$specific_injury_date = $kase->start_date;
		}
	}
	if ($specific_injury_date == "") {
		if ($kase->end_date!="0000-00-00") {
			$injury_type = "C";
			$ct_injury_start_date = $kase->start_date;
			$ct_injury_end_date= $kase->end_date;
		}
	}
	
	//NEED TO SEND APPLICANT AND EMPLOYER STUFF TOO IN CASE THERE IS NO BASIC CASE IN CAJETFILE
	if ($jetfile_case_id=="" || $jetfile_case_id=="0") {
		$blnNoCase = (!is_object($kase->jetfile_info));
		if ($blnNoCase) {
			$blnNoCase = ($kase->jetfile_info=="");
		}
		if ($blnNoCase) {
			//need to build it up
			//applicant
			//employer
			$arrPage = array("page1"=>array("adj_number"=>$kase->adj_number, "first_name"=>$kase->first_name, "last_name"=>$kase->last_name, "middle_name"=>$kase->middle_name, "applicant_street"=>$kase->applicant_street, "applicant_suite"=>$kase->applicant_suite, "applicant_city"=>$kase->applicant_city, "applicant_state"=>$kase->applicant_state, "applicant_zip"=>$kase->applicant_zip, "employer"=>$kase->employer, "employer_street"=>$kase->employer_street, "employer_city"=>$kase->employer_city, "employer_state"=>$kase->employer_state, "employer_zip"=>$kase->employer_zip, "dob"=>$kase->dob, "ssn"=>$kase->ssn, "injury_type"=>$injury_type, "specific_injury_date"=>$specific_injury_date, "ct_injury_start_date"=>$ct_injury_start_date, "ct_injury_end_date"=>$ct_injury_end_date));
			
			$kase->jetfile_info = json_encode($arrPage);
		}
	}
	
	//die(print_r($kase));
	
	$url = "https://www.cajetfile.com/ikase/receiveunstruc.php";
		
	$unstruc_data = json_encode($unstruc->data);
	//let's look up the customer to see if they have a jetfile id
	$customer = getCustomerInfo();
	$fields = array("cus_id"=>$customer->jetfile_id, 'case_id'=>$kase->id, 'jetfile_case_id'=>$jetfile_case_id, 'data'=>$kase->jetfile_info, 'unstrucdata'=>$unstruc_data, 'documents'=>json_encode($arrDocuments));
	
	//die(print_r($fields));
	$result = post_curl($url, $fields);
	//return the json directly back to the view
	die($result);
}
function sendAPP() {
	session_write_close();

	$cus_id = $_SESSION['user_customer_id'];
	$case_id = passed_var("case_id", "post");
	$injury_id = passed_var("injury_id", "post");
	$jetfile_id = passed_var("jetfile_id", "post");
	
	$error = "";
	if ($case_id == "" || $case_id < 0 || !is_numeric($case_id)) {
		$error = "no case";
	}
	if ($injury_id == "" || $injury_id < 0 || !is_numeric($injury_id)) {
		$error = "no injury";
	}
	//for page 2, we need to have saved page 1
	if ($jetfile_id == "" || $jetfile_id < 0 || !is_numeric($jetfile_id)) {
		$error = "no jet";
	}
	
	if ($error != "") {
		die(json_encode(array("error"=>$error)));
	}
	//get uploads
	$sql = "SELECT `document_id` id, `document_name` `name`, `document_filename` `filepath`
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
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("cus_id", $cus_id);
		$stmt->execute();
		$documents = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
	}
	$uploads = "";
	$minimum_files = 4;
	$number_files = count($documents);
	if ($number_files < $minimum_files) {
		$error = array("error"=> array("text"=>($minimum_files - $number_files) . " Document(s) Required"));
		echo json_encode($error);
		die();
	}
	$arrDocuments = array();
	//
	
	foreach($documents as $index=>$document) {
		//$path = "D:/uploads/" . $cus_id . "/jetfiler/" . $case_id . "/" . $document->filepath;
		//$path = "C:\\inetpub\\wwwroot\\ikase.org\\uploads\\" . $cus_id . "\\" . $case_id . "\\jetfiler\\" . $document->filepath;
		$name = $document->name;
		$filepath = $document->filepath;
		$filepath = str_replace("D:/uploads/" . $cus_id . "/" . $case_id . "/jetfiler/", "", $filepath);
		$filepath = str_replace("../" . $cus_id . "/" . $case_id . "/jetfiler/", "", $filepath);
		
		//$path = "D:/uploads/" . $cus_id . "/jetfiler/" . $case_id . "/" . $document->filepath;
		$path = "C:\\inetpub\\wwwroot\\ikase.org\\uploads\\" . $cus_id . "\\" . $case_id . "\\jetfiler\\" . $filepath;
		
		if (!file_exists($path)) {
			$path = "C:\\inetpub\\wwwroot\\ikase.org\\uploads\\" . $cus_id . "\\" . $case_id . "\\" . $document->filepath;
		}
		/*
		if (!file_exists($path)) {
			die(json_encode(array("error"=>array("msg"=>"file was not found", "text"=>$filepath . " was not found. Click to upload"))));
		}
		$data = file_get_contents($path);
		$base64 = base64_encode($data);
		
		$arrDocuments[] = array("name"=>$document->name, "base64"=>$base64);
		*/
		$arrFileNames[$name] = $path;
	}
	
	foreach($arrFileNames as $name=>$path) {
		if (!file_exists($path)) {
			die(json_encode(array("error"=>array("msg"=>"file was not found", "text"=>$filepath . " was not found. Click to upload"))));
		}
		$data = file_get_contents($path);
		$base64 = base64_encode($data);
		
		$arrDocuments[] = array("name"=>$name, "base64"=>$base64);
	}
	/*
	if ($_SERVER['REMOTE_ADDR']=='47.153.51.181') {
		die(print_r($arrDocuments));
	}
	*/
	//we're going to have to look up the bodyparts because they are stored as uuids
	$arrBodyParts = array();
	$sql = "SELECT DISTINCT bp.*, 
			cib.injury_bodyparts_id, cib.attribute bodyparts_number, cib.`status` `bodyparts_status`,
			ccase.case_id, ccase.case_uuid, bp.bodyparts_id id , bp.bodyparts_uuid uuid 
			FROM `cse_bodyparts` bp
			INNER JOIN cse_injury_bodyparts cib
			ON bp.bodyparts_uuid = cib.bodyparts_uuid
			INNER JOIN cse_injury ci
			ON (cib.injury_uuid = ci.injury_uuid
			AND `ci`.`injury_id` = :injury_id)
			INNER JOIN cse_case_injury cci
			ON ci.injury_uuid = cci.injury_uuid
			INNER JOIN cse_case ccase
			ON (cci.case_uuid = ccase.case_uuid
			AND `ccase`.`case_id` = :case_id)
			WHERE 1
			AND cci.customer_id = " . $_SESSION['user_customer_id'] . "
			AND cci.deleted = 'N'
			AND cib.deleted = 'N'
			ORDER BY `code` ASC";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("injury_id", $injury_id);
		$stmt->execute();
		$bodyparts = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		//die(print_r($bodyparts));
		foreach($bodyparts as $bodypart) {
			$arrBodyParts[$bodypart->uuid] = $bodypart->code;
		}
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
			echo json_encode($error);
	}
	
	$kase = jetfileInfo($case_id, $injury_id);
	
	//die(print_r($kase));
	
	$url = "https://www.cajetfile.com/ikase/receive.php";
		
	//let's look up the customer to see if they have a jetfile id
	$customer = getCustomerInfo();
	
	//gotta fix body parts
	$json = json_decode($kase->jetfile_info);
	
	$page2 = $json->page2;
	//echo $page2->{"body_part1"};
	//die(print_r($page2));
	for($i = 1; $i < 11; $i++) {
		$bodypart_uuid = $page2->{"body_part" . $i};
		if ($bodypart_uuid!="") {			
			if (isset($arrBodyParts[$bodypart_uuid])) {
				$code = $arrBodyParts[$bodypart_uuid];
				$page2->{"body_part" . $i} = $code;
			}
		}
	}
	//now put it all back
	$json->page2 = $page2;
	$kase->jetfile_info = json_encode($json);
	
	$fields = array("cus_id"=>$customer->jetfile_id, 'case_id'=>$kase->id, 'data'=>$kase->jetfile_info, 'documents'=>json_encode($arrDocuments));
	if ($_SERVER['REMOTE_ADDR']=='47.153.49.248') {
	//	die(print_r($fields));
	}
	$result = post_curl($url, $fields);
	//return the json directly back to the view
	$operation = "send";
	$activity = "Sent to EAMS for APP for ADJ request";
	$case_uuid = $kase->uuid;
	$activity_category = "EAMS Submission";
	$billing_time = 0;
	recordActivity($operation, $activity, $case_uuid, -1, $activity_category, $billing_time);
	
	//this will return the new case_id from jetfiler
	die($result);
}
function resendAPP() {
	session_write_close();

	$cus_id = $_SESSION['user_customer_id'];
	$case_id = passed_var("case_id", "post");
	$injury_id = passed_var("injury_id", "post");
	$jetfile_id = passed_var("jetfile_id", "post");
	
	$error = "";
	if ($case_id == "" || $case_id < 0 || !is_numeric($case_id)) {
		$error = "no case";
	}
	if ($injury_id == "" || $injury_id < 0 || !is_numeric($injury_id)) {
		$error = "no injury";
	}
	//for page 2, we need to have saved page 1
	if ($jetfile_id == "" || $jetfile_id < 0 || !is_numeric($jetfile_id)) {
		$error = "no jet";
	}
	
	if ($error != "") {
		die(json_encode(array("error"=>$error)));
	}
	//get uploads
	$sql = "SELECT `document_id` id, `document_name` `name`, `document_filename` `filepath`
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
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("cus_id", $cus_id);
		$stmt->execute();
		$documents = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
	}
	$uploads = "";
	$minimum_files = 4;
	$number_files = count($documents);
	if ($number_files < $minimum_files) {
		$error = array("error"=> array("text"=>($minimum_files - $number_files) . " Document(s) Required"));
		echo json_encode($error);
		die();
	}
	$arrDocuments = array();
	//die(print_r($documents));
	foreach($documents as $index=>$document) {
		//$path = "D:/uploads/" . $cus_id . "/jetfiler/" . $case_id . "/" . $document->filepath;
		//$path = "C:\\inetpub\\wwwroot\\ikase.org\\uploads\\" . $cus_id . "\\" . $case_id . "\\jetfiler\\" . $document->filepath;
		$filepath = $document->filepath;
		$filepath = str_replace("D:/uploads/" . $cus_id . "/" . $case_id . "/jetfiler/", "", $filepath);
		$filepath = str_replace("../" . $cus_id . "/" . $case_id . "/jetfiler/", "", $filepath);
		
		//$path = "D:/uploads/" . $cus_id . "/jetfiler/" . $case_id . "/" . $document->filepath;
		$path = "C:\\inetpub\\wwwroot\\ikase.org\\uploads\\" . $cus_id . "\\" . $case_id . "\\jetfiler\\" . $filepath;
		
		if (!file_exists($path)) {
			$path = "C:\\inetpub\\wwwroot\\ikase.org\\uploads\\" . $cus_id . "\\" . $case_id . "\\" . $document->filepath;
		}
		$data = file_get_contents($path);
		$base64 = base64_encode($data);
		
		$arrDocuments[] = array("name"=>$document->name, "base64"=>$base64);
	}
	
	//we're going to have to look up the bodyparts because they are stored as uuids
	$arrBodyParts = array();
	$sql = "SELECT DISTINCT bp.*, 
			cib.injury_bodyparts_id, cib.attribute bodyparts_number, cib.`status` `bodyparts_status`,
			ccase.case_id, ccase.case_uuid, bp.bodyparts_id id , bp.bodyparts_uuid uuid 
			FROM `cse_bodyparts` bp
			INNER JOIN cse_injury_bodyparts cib
			ON bp.bodyparts_uuid = cib.bodyparts_uuid
			INNER JOIN cse_injury ci
			ON (cib.injury_uuid = ci.injury_uuid
			AND `ci`.`injury_id` = :injury_id)
			INNER JOIN cse_case_injury cci
			ON ci.injury_uuid = cci.injury_uuid
			INNER JOIN cse_case ccase
			ON (cci.case_uuid = ccase.case_uuid
			AND `ccase`.`case_id` = :case_id)
			WHERE 1
			AND cci.customer_id = " . $_SESSION['user_customer_id'] . "
			AND cci.deleted = 'N'
			AND cib.deleted = 'N'
			ORDER BY `code` ASC";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("injury_id", $injury_id);
		$stmt->execute();
		$bodyparts = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		//die(print_r($bodyparts));
		foreach($bodyparts as $bodypart) {
			$arrBodyParts[$bodypart->uuid] = $bodypart->code;
		}
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
			echo json_encode($error);
	}
	
	$kase = jetfileInfo($case_id, $injury_id);
	$jetfile_case_id = $kase->jetfile_case_id;
	//die(print_r($kase));
	
	$url = "https://www.cajetfile.com/ikase/receive_update.php";
		
	//let's look up the customer to see if they have a jetfile id
	$customer = getCustomerInfo();
	
	//gotta fix body parts
	$json = json_decode($kase->jetfile_info);
	
	$page2 = $json->page2;
	//echo $page2->{"body_part1"};
	//die(print_r($page2));
	for($i = 1; $i < 11; $i++) {
		$bodypart_uuid = $page2->{"body_part" . $i};
		if ($bodypart_uuid!="") {
			$code = $arrBodyParts[$bodypart_uuid];
			$page2->{"body_part" . $i} = $code;
		}
	}
	//now put it all back
	$json->page2 = $page2;
	$kase->jetfile_info = json_encode($json);
	//die(print_r($json));
	$fields = array("cus_id"=>$customer->jetfile_id, 'case_id'=>$kase->id, 'jetfile_case_id'=>$jetfile_case_id, 'data'=>$kase->jetfile_info, 'documents'=>json_encode($arrDocuments));
	
	//die(print_r($fields));
	$result = post_curl($url, $fields);
	die($result);
	//return the json directly back to the view
	$operation = "send";
	$activity = "Sent to EAMS for APP for ADJ request (Refile)";
	$case_uuid = $kase->uuid;
	$activity_category = "EAMS Submission";
	$billing_time = 0;
	recordActivity($operation, $activity, $case_uuid, -1, $activity_category, $billing_time);
	
	//remove the errors from the record
	try {
		//does it need to be updated, what is the current status
		$sql = "SELECT jet.app_status, jet.app_status_number, jet.injury_uuid, inj.injury_id, inj.adj_number
		FROM cse_jetfile jet
		INNER JOIN cse_injury inj
		ON jet.injury_uuid = inj.injury_uuid
		WHERE jet.jetfile_case_id = " . $jetfile_case_id . "
		AND jet.customer_id = '" . $_SESSION['user_customer_id'] . "'
		AND inj.customer_id = '" . $_SESSION['user_customer_id'] . "'";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$current_info = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		$arrStatus = json_decode($current_info->app_status);
		$arrStatus->message = "Refiled on " . date("m/d/Y g:iA");
		$arrStatus->errors = "";
		
		$current_info->app_status = json_encode($arrStatus);
		
		$sql = "UPDATE cse_jetfile
		SET app_status = :new_status
		WHERE jetfile_id = :jetfile_id
		AND customer_id = :customer_id";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("new_status", $current_info->app_status);
		$stmt->bindParam("jetfile_id", $jetfile_id);
		$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
		$stmt->execute();
		$stmt = null; $db = null;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
	}
	//this will return the new case_id from jetfiler
	die($result);
}
function getADJCount($adj_number) {
	$url = 'http://cajetfile.com/limapi/?adj/check/' . $adj_number;
	// create curl resource 
	$ch = curl_init(); 

	// set url 
	curl_setopt($ch, CURLOPT_URL, $url); 

	//return the transfer as a string 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

	// $output contains the output string 
	$output = curl_exec($ch); 

	// close curl resource to free up system resources 
	curl_close($ch);
	
	die($output);
}
function saveApp() {
	$injury_id = passed_var("injury_id", "post");
	$jetfile_id = passed_var("jetfile_id", "post");
	$page = passed_var("page", "post");
	
	unset($_POST["injury_id"]);
	unset($_POST["jetfile_id"]);
	unset($_POST["page"]);
	
	$injury = getInjuryInfo($injury_id);
	$injury_uuid = $injury->uuid;
	
	$info = json_encode(array("page" . $page=>$_POST));

	if ($jetfile_id=="") {
		$sql = "INSERT INTO `cse_jetfile`
		(`injury_uuid`, `info`, `customer_id`, `last_update_date`)
		VALUES ('" . $injury_uuid . "', '" . addslashes($info) . "', '" . $_SESSION['user_customer_id'] . "','" . date("Y-m-d H:i:s") . "');";
		$operation = "insert";
	} else {
		//first retrieve the existing info
		$sql = "SELECT info 
		FROM `cse_jetfile`
		WHERE jetfile_id = :jetfile_id
		AND customer_id = :customer_id";
		
		try {
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("jetfile_id", $jetfile_id);
			$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
			
			$stmt->execute();
			$jetfile = $stmt->fetchObject();
			$stmt->closeCursor(); $stmt = null; $db = null;
		} catch(PDOException $e) {
			$error = array("error"=> array("text"=>$e->getMessage()));
			echo json_encode($error);
		}
		$current_info = $jetfile->info;
		//make it into an array
		$arrInfo = json_decode($current_info);
		$arrInfo = (array) $arrInfo;
		//replace/add the page# info
		$arrInfo["page" . $page] = $_POST;
		//put back in json format
		$info = json_encode($arrInfo);
		
		//die($info);
		
		$sql = "UPDATE `cse_jetfile`
		SET `info` = '" . addslashes($info) . "',
		`last_update_date` = '" . date("Y-m-d H:i:s") . "'
		WHERE jetfile_id = '" . $jetfile_id . "'
		AND customer_id = '" . $_SESSION['user_customer_id'] . "'";
		$operation = "update";
	}

	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		if ($jetfile_id=="") {
			$jetfile_id = $db->lastInsertId();
		}
		$stmt = null; $db = null;
		echo json_encode(array("success"=>true, "operation"=>$operation, "id"=>$jetfile_id));
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function saveUnstructured() {
	$cus_id = $_SESSION["user_customer_id"];
	$case_id = passed_var("case_id", "post");
	$injury_id = passed_var("injury_id", "post");
	$jetfile_id = passed_var("jetfile_id", "post");
	$page = passed_var("page", "post");
	$form = "unstruc";
	$unstruc_number = passed_var("unstruc_number", "post");
	
	unset($_POST["case_id"]);
	unset($_POST["injury_id"]);
	unset($_POST["jetfile_id"]);
	unset($_POST["page"]);
	
	if (isset($_FILES)) {
		//upload the unstructured doc
		//what did we get
		require("../jetfiler/cls_fileupload.php");
		$uploadDir = '\\uploads\\' . $cus_id . '\\' . $case_id . '\\jetfiler\\';
		
		//die($_SERVER['DOCUMENT_ROOT'] . $uploadDir);
		if (!is_dir($_SERVER['DOCUMENT_ROOT'] . $uploadDir)) {
			mkdir($_SERVER['DOCUMENT_ROOT'] . $uploadDir, 0755, true);
		}
		//$path = "D:/uploads/" . $_SESSION['user_customer_id'] . "/" . $case_id . "/jetfiler/";
		$path = $_SERVER['DOCUMENT_ROOT'] . $uploadDir;
		
		$acceptable_file_types = "application/pdf";
		$arrUploads = array();
		$arrNames = array();
		$arrKaseDocs = array();
		$arrKaseNames = array();
		//die(print_r($_POST));
		
		$my_uploader = new uploader('en');
		$my_uploader->max_filesize(50000000); // 20000 kb
		$my_uploader->max_image_size(50000, 50000); // max_image_size($width, $height)
		$upload_file_browser = "file_up";
		$upload_file_name = passed_var("document_title", "post");
		$stored_file_name = passed_var("filepath", "post");
		
		$default_extension = "";
		$mode = 1;
		if ($my_uploader->upload($upload_file_browser, $acceptable_file_types, $default_extension)) {
			/*
			$my_uploader->save_file($path, $mode);
			//now that the file has been uploaded, shrink it
			$upfilename1 = $path . $case_id . "_" . $my_uploader->file['name'];
			$arrUploads[] = $upfilename1;
			$arrNames[] = $upload_file_name;
			*/
			$arrFile = explode(".", $my_uploader->file['name']);
			$extension = $arrFile[count($arrFile)-1];
			$my_uploader->file['name'] = str_replace("." . $extension, "_" . $case_id . "." . $extension, $my_uploader->file['name']);
			
			$my_uploader->save_file($path, $mode);
			
			$upfilename1 = $my_uploader->file['name'];
			
			$arrUploads[] = $upfilename1;
			$arrNames[] = $upload_file_name;
			
			//echo $upfilename1. " has been uploaded.<br>";
		} else {
			//echo "could not upload ".$upload_file_name . "<br />";
			if (!is_numeric($stored_file_name)) {
				$arrUploads[] = str_replace("D:/uploads/" . $cus_id . "/" . $case_id . "/jetfiler/", "", $stored_file_name);
				$arrNames[] = $upload_file_name;
			} else {
				$arrKaseNames[] = $upload_file_name;
				$arrKaseDocs[] = $stored_file_name;
			}
		}
		//die("uploaded");
		$_POST["filepath"] = $arrUploads[0];
		//die(print_r($_POST));
		
		include("../jetfiler/process_documents.php");
	}
	
	$injury = getInjuryInfo($injury_id);
	$injury_uuid = $injury->uuid;
	
	$info = json_encode(array("page" . $page=>$_POST));

	if ($jetfile_id=="") {
		$sql = "INSERT INTO `cse_jetfile`
		(`injury_uuid`, `unstruc_info`, `customer_id`, `last_update_date`)
		VALUES ('" . $injury_uuid . "', '" . addslashes($info) . "', '" . $cus_id . "','" . date("Y-m-d H:i:s") . "');";
		$operation = "insert";
	} else {
		//first retrieve the existing info
		$sql = "SELECT `unstruc_info` 
		FROM `cse_jetfile`
		WHERE jetfile_id = :jetfile_id
		AND customer_id = :customer_id";
		
		try {
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("jetfile_id", $jetfile_id);
			$stmt->bindParam("customer_id", $cus_id);
			
			$stmt->execute();
			$jetfile = $stmt->fetchObject();
			$stmt->closeCursor(); $stmt = null; $db = null;
		} catch(PDOException $e) {
			$error = array("error"=> array("text"=>$e->getMessage()));
			echo json_encode($error);
		}
		$current_info = $jetfile->unstruc_info;
		//make it into an array
		$arrInfo = json_decode($current_info);
		$arrInfo = (array) $arrInfo;
		
		
		//replace/add the page# info
		$arrInfo[$unstruc_number-1] = array("unstruc_number"=>($unstruc_number-1), "data"=>$_POST);
		
		//put back in json format
		$info = json_encode($arrInfo);
		
		//die($info);
		
		$sql = "UPDATE `cse_jetfile`
		SET `unstruc_info` = '" . addslashes($info) . "',
		`last_update_date` = '" . date("Y-m-d H:i:s") . "'
		WHERE jetfile_id = '" . $jetfile_id . "'
		AND customer_id = '" . $cus_id . "'";
		$operation = "update";

	}

	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		if ($jetfile_id=="") {
			$jetfile_id = $db->lastInsertId();
		}
		$stmt = null; $db = null;
		echo json_encode(array("success"=>true, "operation"=>$operation, "id"=>$jetfile_id));
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}

function unstrucFile() {
	session_write_close();
	
	$cus_id = $_SESSION['user_customer_id'];
	$jetfile_case_id = passed_var("jetfile_case_id", "post");
	$jetfile_unstruc_id = passed_var("jetfile_unstruc_id", "post");
	$unstruc_number = passed_var("unstruc_number", "post");
	
	$url = "https://www.cajetfile.com/ikase/fileunstruc.php";
	
	$customer = getCustomerInfo();
	
	$fields = array("cus_id"=>$customer->jetfile_id, 'jetfile_case_id'=>$jetfile_case_id, 'jetfile_unstruc_id'=>$jetfile_unstruc_id, 'unstruc_number'=>$unstruc_number);
	
	//echo $url; die(print_r($fields));
	$result = post_curl($url, $fields);
	//return the json directly back to the view
	die($result);
}
function saveDOR() {
	$injury_id = passed_var("injury_id", "post");
	$jetfile_id = passed_var("jetfile_id", "post");
	$page = passed_var("page", "post");
	
	unset($_POST["injury_id"]);
	unset($_POST["jetfile_id"]);
	unset($_POST["page"]);
	
	$injury = getInjuryInfo($injury_id);
	$injury_uuid = $injury->uuid;
	
	$info = json_encode(array("page" . $page=>$_POST));

	if ($jetfile_id=="") {
		$sql = "INSERT INTO `cse_jetfile`
		(`injury_uuid`, `dor_info`, `customer_id`, `last_update_date`)
		VALUES ('" . $injury_uuid . "', '" . addslashes($info) . "', '" . $_SESSION['user_customer_id'] . "','" . date("Y-m-d H:i:s") . "');";
		$operation = "insert";
	} else {
		//first retrieve the existing info
		$sql = "SELECT `dor_info` 
		FROM `cse_jetfile`
		WHERE jetfile_id = :jetfile_id
		AND customer_id = :customer_id";
		
		try {
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("jetfile_id", $jetfile_id);
			$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
			
			$stmt->execute();
			$jetfile = $stmt->fetchObject();
			$stmt->closeCursor(); $stmt = null; $db = null;
		} catch(PDOException $e) {
			$error = array("error"=> array("text"=>$e->getMessage()));
			echo json_encode($error);
		}
		$current_info = $jetfile->dor_info;
		//make it into an array
		$arrInfo = json_decode($current_info);
		$arrInfo = (array) $arrInfo;
		//replace/add the page# info
		$arrInfo["page" . $page] = $_POST;
		//put back in json format
		$info = json_encode($arrInfo);
		
		//die($info);
		
		$sql = "UPDATE `cse_jetfile`
		SET `dor_info` = '" . addslashes($info) . "',
		`last_update_date` = '" . date("Y-m-d H:i:s") . "'
		WHERE jetfile_id = '" . $jetfile_id . "'
		AND customer_id = '" . $_SESSION['user_customer_id'] . "'";
		$operation = "update";
	}

	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		if ($jetfile_id=="") {
			$jetfile_id = $db->lastInsertId();
		}
		$stmt = null; $db = null;
		echo json_encode(array("success"=>true, "operation"=>$operation, "id"=>$jetfile_id));
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function saveDORE() {
	$injury_id = passed_var("injury_id", "post");
	$jetfile_id = passed_var("jetfile_id", "post");
	$page = passed_var("page", "post");
	
	unset($_POST["injury_id"]);
	unset($_POST["jetfile_id"]);
	unset($_POST["page"]);
	
	$injury = getInjuryInfo($injury_id);
	$injury_uuid = $injury->uuid;
	
	$info = json_encode(array("page" . $page=>$_POST));

	if ($jetfile_id=="") {
		$sql = "INSERT INTO `cse_jetfile`
		(`injury_uuid`, `dore_info`, `customer_id`, `last_update_date`)
		VALUES ('" . $injury_uuid . "', '" . addslashes($info) . "', '" . $_SESSION['user_customer_id'] . "','" . date("Y-m-d H:i:s") . "');";
		$operation = "insert";
	} else {
		//first retrieve the existing info
		$sql = "SELECT `dore_info` 
		FROM `cse_jetfile`
		WHERE jetfile_id = :jetfile_id
		AND customer_id = :customer_id";
		
		try {
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("jetfile_id", $jetfile_id);
			$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
			
			$stmt->execute();
			$jetfile = $stmt->fetchObject();
			$stmt->closeCursor(); $stmt = null; $db = null;
		} catch(PDOException $e) {
			$error = array("error"=> array("text"=>$e->getMessage()));
			echo json_encode($error);
		}
		$current_info = $jetfile->dore_info;
		//make it into an array
		$arrInfo = json_decode($current_info);
		$arrInfo = (array) $arrInfo;
		//replace/add the page# info
		$arrInfo["page" . $page] = $_POST;
		//put back in json format
		$info = json_encode($arrInfo);
		
		//die($info);
		
		$sql = "UPDATE `cse_jetfile`
		SET `dore_info` = '" . addslashes($info) . "',
		`last_update_date` = '" . date("Y-m-d H:i:s") . "'
		WHERE jetfile_id = '" . $jetfile_id . "'
		AND customer_id = '" . $_SESSION['user_customer_id'] . "'";
		$operation = "update";
	}

	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		if ($jetfile_id=="") {
			$jetfile_id = $db->lastInsertId();
		}
		$stmt = null; $db = null;
		echo json_encode(array("success"=>true, "operation"=>$operation, "id"=>$jetfile_id));
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function saveLien() {
	$injury_id = passed_var("injury_id", "post");
	$jetfile_id = passed_var("jetfile_id", "post");
	$page = passed_var("page", "post");
	
	unset($_POST["injury_id"]);
	unset($_POST["jetfile_id"]);
	unset($_POST["page"]);
	
	$injury = getInjuryInfo($injury_id);
	$injury_uuid = $injury->uuid;
	
	$info = json_encode(array("page" . $page=>$_POST));

	if ($jetfile_id=="") {
		$sql = "INSERT INTO `cse_jetfile`
		(`injury_uuid`, `lien_info`, `customer_id`, `last_update_date`)
		VALUES ('" . $injury_uuid . "', '" . addslashes($info) . "', '" . $_SESSION['user_customer_id'] . "','" . date("Y-m-d H:i:s") . "');";
		$operation = "insert";
	} else {
		//first retrieve the existing info
		$sql = "SELECT `lien_info` 
		FROM `cse_jetfile`
		WHERE jetfile_id = :jetfile_id
		AND customer_id = :customer_id";
		
		try {
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("jetfile_id", $jetfile_id);
			$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
			
			$stmt->execute();
			$jetfile = $stmt->fetchObject();
			$stmt->closeCursor(); $stmt = null; $db = null;
		} catch(PDOException $e) {
			$error = array("error"=> array("text"=>$e->getMessage()));
			echo json_encode($error);
		}
		$current_info = $jetfile->lien_info;
		//make it into an array
		$arrInfo = json_decode($current_info);
		$arrInfo = (array) $arrInfo;
		//replace/add the page# info
		$arrInfo["page" . $page] = $_POST;
		//put back in json format
		$info = json_encode($arrInfo);
		
		//die($info);
		
		$sql = "UPDATE `cse_jetfile`
		SET `lien_info` = '" . addslashes($info) . "',
		`last_update_date` = '" . date("Y-m-d H:i:s") . "'
		WHERE jetfile_id = '" . $jetfile_id . "'
		AND customer_id = '" . $_SESSION['user_customer_id'] . "'";
		$operation = "update";
	}

	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		if ($jetfile_id=="") {
			$jetfile_id = $db->lastInsertId();
		}
		$stmt = null; $db = null;
		echo json_encode(array("success"=>true, "operation"=>$operation, "id"=>$jetfile_id));
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function verifyPendingAPP() {
	//chck on status
	try {
		$sql = "SELECT customer_id, data_source FROM ikase.cse_customer
		WHERE jetfile_id > 0";
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("cus_id", $cus_id);
		$stmt->execute();
		$customers = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		foreach($customers as $customer) {
			$customer_id = $customer->customer_id;
			$_SESSION["user_customer_id"] = $customer_id;
			$data_source = $customer->data_source;
			
			$db_name = "`ikase`";
			if ($data_source!="") {
				$db_name = "`ikase_" . $data_source . "`";
			}
			
			$sql = "SELECT inj.injury_id, jet.jetfile_id, jet.jetfile_case_id, app_filing_id
			FROM " . $db_name . ".cse_jetfile jet
			INNER JOIN " . $db_name . ".cse_injury inj
			ON jet.injury_uuid = inj.injury_uuid
			WHERE app_filing_id > 0
			AND adj_number NOT LIKE 'ADJ%'
			AND jet.customer_id = :customer_id";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("customer_id", $customer_id);
			$stmt->execute();
			$filings = $stmt->fetchAll(PDO::FETCH_OBJ);
			$stmt->closeCursor(); $stmt = null; $db = null;
			
			if(count($filings)==0) {
				continue;
			}
			//die(print_r($filings));
			
			foreach($filings as $filing) {
				$filing_id = $filing->app_filing_id;
				$jetfile_id = $filing->jetfile_id;
				$injury_id = $filing->injury_id;
				
				$status = checkFiling($filing_id, true);
				$status = json_decode($status);
				
				//shorten things up
				$jet_status = $status->status;
				/*
				$jet_status = str_replace('xmlns:pay=\\"http:\\/\\/www.dir.ca.gov\\/dwc\\/EAMS\\/PresentTermSolution\\/Schemas\\/Common\\/PayloadFields\\', '', $jet_status);
				$jet_status = str_replace("pay:Error", "p:E", $jet_status);
				$jet_status = str_replace("eam:Form", "e:F", $jet_status);
				$jet_status = str_replace("eam:ResubmissionID", "e:R", $jet_status);
				$jet_status = str_replace("resubmission_id", "r_id", $jet_status);
				*/
				
				//update adj number
				$sql = "UPDATE " . $db_name . ".cse_jetfile
				SET app_status = :app_status
				WHERE jetfile_id = :jetfile_id
				AND customer_id = :customer_id";
				$db = getConnection();
				$stmt = $db->prepare($sql);
				$stmt->bindParam("app_status", $jet_status);
				$stmt->bindParam("jetfile_id", $jetfile_id);
				$stmt->bindParam("customer_id", $customer_id);
				$stmt->execute();
				
				if ($status->status == "5") {
					//adj
					$sql = "UPDATE " . $db_name . ".cse_injury
					SET adj_number = :adj_number
					WHERE injury_id = :injury_id
					AND customer_id = :customer_id";
					$db = getConnection();
					$stmt = $db->prepare($sql);
					$stmt->bindParam("adj_number", $status->adj_number);
					$stmt->bindParam("injury_id", $injury_id);
					$stmt->bindParam("customer_id", $customer_id);
					$stmt->execute();
				}
			}
		}
		
		echo json_encode(array("success"=>"true"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
	}
}
function createAppPacket() {
	$case_id = passed_var("case_id", "post");
	$injury_id = passed_var("injury_id", "post");
	$kase = getKaseJetFile($case_id, $injury_id);
	/*
	
	$customer = getCustomerInfo();
	$injury = getInjuryInfo($injury_id);
	*/
	$carrier_id = passed_var("carrier", "post");
	$employer_id = $kase->employer_id;
	
	$arrDetails = array(
		"table_name"=>"eams",
		"case_id"=>$case_id,
		"nopublish"=>"n",
		"eams_form_name"=>"app_cover",
		"eams_form_id"=>"",
		"partie_count"=>"4",
		"separator_title"=>"",
		"doi"=>$injury_id,
		"adjuster"=>"Hannah+Chevalier",
		"carrier"=>$carrier_id,
		"employer"=>$employer_id,
		"defense"=>"",
		"primary"=>"",
		"lien_holder"=>"",
		"referral"=>"",
		"eamsInput"=>"",
		"nopublish"=>"y"
	);
	$url = "api/pdf/create";
	$app_cover = post_curl($url, $arrDetails);
	
	//die(print_r($app_cover));
}
function requestCreatePDF() {
	$case_id = passed_var("case_id", "post");
	$injury_id = passed_var("injury_id", "post");
	$kase = getKaseJetFile($case_id, $injury_id);
	$customer = getCustomerInfo();
	//die(print_r($customer));
	$_POST["case_id"] = $kase->jetfile_case_id;
	$_POST["ikase_case_id"] = $case_id;
	$_POST["ikase_injury_id"] = $injury_id;
	$_POST["ikase_cus_id"] = $_SESSION["user_customer_id"];
	$_POST["cus_id"] = $customer->jetfile_id;
	$_POST["ikase_user_id"] = $_SESSION["user_plain_id"];
	$_POST["ikase_user_name"] = $_SESSION["user_name"];
	
	requestPDF();
}
function requestPDF() {
	$form = passed_var("form", "post");
	$stack = passed_var("stack", "post");
	$arrStack = explode("|", $stack);
	$arrFiles = array();
	
	$host = 'https://www.cajetfile.com/';
	/*
	if (count($arrStack) > 1) {
		$url = $host . 'pdf_separator.php';
		$return = post_curl($url, $_POST);
		$arrFiles["separator"] = $return;
	}
	*/
	foreach($arrStack as $stack) {
		$subdocument = "";
		if ($stack=="app_cover") {
			$subdocument = "pos";
		}
		if ($stack=="app") {
			$stack = "app_cover";
			//no pos
		}
		if ($stack=="dor") {
			$stack = "dor_cover";
			//no pos
		}
		
		if ($stack=="cover") {
			$subdocument = " ";
		}
		
		if ($stack=="pos") {
			$stack = "pos_cover_mailing";
		}
		$url = $host . 'pdf_' . $stack . '.php';
		//die($url);
		$return = post_curl($url, $_POST);
		//die($return);
		$return = json_decode($return);
		$filename = $return->filename;
		$filename = str_replace("D:/uploads/", "", $filename);

		//$arrFiles[] = array("document"=>$stack, "filename="=>$filename);
		$arrFiles[] = $filename;
		
		if ($subdocument!="") {
			$_POST["subdocument"] = trim($subdocument);
			$url = $host . 'pdf_separator.php';
			$return = post_curl($url, $_POST);
			$return = json_decode($return);
			$filename = $return->filename;
			$filename = str_replace("D:/uploads/", "", $filename);
			//echo $return . "\r\n";
			//$arrFiles[] = array("document"=>"separator", "filename="=>$filename);
			$arrFiles[] = $filename;
		}
	}
	if (count($arrStack) == 1) {
		die(json_encode(array("success"=>"true", "filename"=>$filename)));
	}
	//die(print_r($arrFiles));
	$arrDetails = array();
	$blnApp = (strpos($stack, "app") > -1);
	if ($form=="cover" && $blnApp) {
		$form = "app_pos";
	}
	if ($form=="cover" && !$blnApp) {
		$form = $arrStack[1];
	}
	
	$arrDetails["type"] = "_" . $form;
	$arrDetails["cus_id"] = $_POST["cus_id"];
	$arrDetails["case_id"] = $_POST["case_id"];
	$arrDetails["ikase_cus_id"] = $_POST["ikase_cus_id"];
	$arrDetails["ikase_case_id"] = $_POST["ikase_case_id"];
	$arrDetails["ikase_injury_id"] = $_POST["ikase_injury_id"];
	$arrDetails["ikase_user_id"] = $_POST["ikase_user_id"];
	$arrDetails["ikase_user_name"] = $_POST["ikase_user_name"];
	$arrDetails["files"] = implode("|", $arrFiles);
	
	//print_r($arrDetails);
	//$arrVariables = array("files"=>json_encode($arrFiles), "details"=>json_encode($arrDetails));
	$url = $host . "pdf_combine_files.php";
	$combined = post_curl($url, $arrDetails);
	
	die($combined);
	/*
	$fields_string = "";
	$fields = $arrDetails;
	
	foreach($fields as $key=>$value) { 
		$fields_string .= $key . '=' . urlencode($value) . '&'; 
	}
	rtrim($fields_string, '&');

	$ch = curl_init();
	//echo $url . "?" . $fields_string . "\r\n";
	//die();
	//set the url, number of POST vars, POST data
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, false); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_COOKIEFILE, "cookies.txt");
	curl_setopt($ch, CURLOPT_POST, count($fields_string));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
	
	$result = curl_exec($ch);
	curl_close($ch);
	
	die($result);
	*/
}
function getPDF() {
	$customer_id = $_SESSION["user_customer_id"];
	$case_id = passed_var("case_id", "post");
	$form = passed_var("form", "post");
	
	$document_root = $form;
	
	switch($form) {
		case "app":
			$document_root = "eams_app";
			$search_description = "APP for ADJ%";
			break;
		case "app_cover":
			$document_root = "eams_combine_app";
			$search_description = "APP for ADJ%";			
			break;
		case "dor":
			$document_root = "eams_dor";
			$search_description = "DOR";			
			break;
		case "dor_cover":
			$document_root = "eams_combine_dor";
			$search_description = "DOR";						
			break;
	}
	$sql = "SELECT cd.* 

	FROM cse_document cd
	INNER JOIN cse_case_document ccd
	ON cd.document_uuid = ccd.document_uuid AND ccd.deleted = 'N'
	INNER JOIN cse_case ccase
	ON ccd.case_uuid = ccase.case_uuid
	WHERE cd.deleted = 'N'
	AND cd.`type` = 'jetfiler'
	AND cd.description LIKE '" . $search_description . "'
	AND cd.customer_id = :customer_id
	AND ccase.case_id = :case_id
	AND cd.document_filename LIKE '" . $document_root . "%'
	ORDER BY cd.document_id DESC
	LIMIT 0, 1";
	
	//die($sql);
	try {				
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("case_id", $case_id);
		$stmt->execute();
		$document = $stmt->fetchObject();
		
		echo json_encode($document);
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function acceptPDF() {
	//we need the original customer_id
	//die(print_r($_POST));
	//and ikase case_id
	$cus_id = passed_var("cus_id", "post");
	$customer_id = passed_var("cus_id", "post");
	$case_id = passed_var("case_id", "post");
	$injury_id = passed_var("injury_id", "post");
	$origin = passed_var("origin", "post");
	$filename = passed_var("filename", "post");
	$filename_output = $filename;
	$contents = passed_var("contents", "post");

	$user_id = passed_var("user_id", "post");
	//need to store in session for add doc
	$_SESSION['user_id'] = $user_id;
	
	$destination_folder = "D:/uploads/" . $cus_id . "/" . $case_id . "/jetfiler/";
	if (!is_dir($destination_folder)) {
		mkdir($destination_folder, 0755, true);
	}
	
	$filename_output = "D:\\uploads\\" . $cus_id . "\\" . $case_id . "\\jetfiler\\" . $filename_output;
	if (file_exists($filename_output)) {
		unlink($filename_output);
		//echo $filename_output . " deleted<br />";
		//die();
	} else {
		//echo $filename_output . " did not exists<br />";
		//die();
	}
	$decoded = base64_decode($contents);

	$success = file_put_contents($filename_output, $decoded);
	
	$sql_customer = "SELECT cus_name, data_source, permissions
	FROM  `ikase`.`cse_customer` 
	WHERE customer_id = :customer_id AND `cse_customer`.deleted = 'N'";
	try {				
		$db = getConnection();
		$stmt = $db->prepare($sql_customer);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$customer = $stmt->fetchObject();
		
		//die(print_r($customer));
		if (!is_object($customer)) {
			die("no go");
		}
		$cus_name = $customer->cus_name;
		$data_source = $customer->data_source;
		if ($data_source=="") {
			$return = "ikase";
		}
		if ($data_source!="") {
			$return = "ikase_" . $data_source;
		}
		$db = null;

		addRemoteDocument($return);
		
		//echo $filename . " saved";
		
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function recordJetFileActivity($operation, $activity, $case_uuid, $track_id, $category = "", $billing_time = 0, $cus_id, $db_name) {
	try {
		$db = getConnection();
		
		$activity_uuid = uniqid("KS", false);
		//fractions of an hour
		if ($billing_time > 0) {
			$billing_time = $billing_time / 60;
		}
		if ($billing_time == "") { 
			$billing_time = "0.00";
		}
		$sql = "INSERT INTO " . $db_name . ".cse_activity (`activity_uuid`, `activity`, `hours`, `activity_category`, `activity_user_id`, `customer_id`)
		VALUES ('" . $activity_uuid . "', '" . addslashes($activity) . "', '" . $billing_time . "', '" . addslashes($category) . "', '-2', " . $cus_id . ")";
		//echo $sql . "\r\n";
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		//if we passed a valid case
		if ($case_uuid!="") {
			$last_updated_date = date("Y-m-d H:i:s");
			$case_activity_uuid = uniqid("KA", false);
			$attribute = "main";
			if ($category != "") {
				$attribute = $category;
			}
			$sql = "INSERT INTO " . $db_name . ".cse_case_activity (`case_activity_uuid`, `case_uuid`, `activity_uuid`, `attribute`, `case_track_id`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_activity_uuid . "', '" . $case_uuid . "', '" . $activity_uuid . "', '" . $attribute . "', " . $track_id . ", '" . $last_updated_date . "', '-2', '" . $cus_id . "')";
			//echo $sql . "\r\n";
			$stmt = $db->prepare($sql);  
			$stmt->execute();
		}
		$stmt = null; $db = null;
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .', "sql":'. $sql .'}}'; 
	}
}
?>
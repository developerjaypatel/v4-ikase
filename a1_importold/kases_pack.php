<?php
$app->get('/inactive/:days', authorize('user'),	'getInactives');

$app->get('/kases', authorize('user'),	'getKases');
$app->get('/sess', authorize('user'),	'getSess');
$app->get('/openkases', authorize('user'),	'getOpenKases');
$app->get('/opensomekases/:start', authorize('user'),	'getOpenKasesLimited');
$app->get('/currentkases/:kases', authorize('user'),	'setCurrentKasesCount');
$app->get('/nextkases', authorize('user'),	'getNextKases');
$app->get('/previouskases', authorize('user'),	'getPreviousKases');
$app->get('/closedkases', authorize('user'),	'getClosedKases');
$app->get('/notaskskases', authorize('user'),	'getNoTasksKases');
$app->get('/noworkerkases', authorize('user'),	'getNoWorkerKases');

$app->get('/allkases', authorize('user'),	'getAllKases');
$app->get('/pikases', authorize('user'),	'getPIKases');
$app->get('/wkases', authorize('user'),	'getWCKases');
$app->get('/emailkases', authorize('user'),	'getEmailKases');
$app->post('/examinerkases', authorize('user'),	'getExaminerKases');
$app->get('/emailcsvkases', authorize('user'),	'csvEmailKases');

$app->get('/kases/tokeninput', authorize('user'), 'getTokenInputKases');
$app->get('/kases/last', authorize('user'), 'runLastQuery');
$app->get('/kases/lastalpha', authorize('user'), 'runLastQueryAlpha');
$app->get('/kases/lastmonth', authorize('user'), 'runLastQueryMonths');
$app->get('/kases/lookup', authorize('user'),	'lookupKases');
$app->get('/kases/recent', authorize('user'),	'getRecentKases');
$app->get('/kases/billables', authorize('user'), 'getKasesBillable');
$app->get('/kases/billablescount', authorize('user'), 'getKasesBillableCount');

$app->get('/kases/report', authorize('user'),	'getKasesReport');
$app->get('/kases/bymonth', authorize('user'),	'getKasesReportByMonth');
$app->get('/referrals/bymonth', authorize('user'),	'getReferralsReportByMonth');
$app->get('/clients/bymonth', authorize('user'),	'getClientsReportByMonth');

$app->get('/kases/listbymonth/:year/:month', authorize('user'),	'getKasesListByMonth');
$app->get('/kases/referralsbymonth/:start/:end/:referring', authorize('user'),	'getReferredKasesListByMonth');
$app->get('/kases/clientsbymonth/:start/:end/:client', authorize('user'),'getClientKasesListByMonth');
$app->get('/kases/:id', authorize('user'),	'getKase');
$app->get('/kaseinfo/:id', authorize('user'),	'getKaseInfo');
$app->get('/intakes', authorize('user'), 'getIntakeKases');
$app->get('/intakesfiltered/:filter/:type', authorize('user'), 'getFilteredIntakeKases');
$app->get('/intakesbyletter/:filter/:type/:letter', authorize('user'), 'getIntakeKasesByLetter');
$app->get('/kases/search/:search_term', authorize('user'), 'searchKases');
$app->post('/kases/byids', authorize('user'), 'getKasesByIDs');

$app->get('/kases/mine/:search_term/:modifier', authorize('user'), 'searchMine');
$app->get('/kases/rolodex/:search_term/:modifier', authorize('user'), 'searchRelatedRolodex');

$app->get('/kases/seek', authorize('user'), 'seekKases');
$app->get('/kases/claim/:id', authorize('user'), 'getKaseSSNClaim');
$app->get('/kases/related/:id', authorize('user'), 'getRelatedKases');
$app->get('/kases/employee/:name/:partie_type', authorize('user'), 'getEmployeeKases');

$app->get('/kases_workersummary', authorize('user'), 'kaseWorkerSummary');
$app->get('/kases_worker/:user_id', authorize('user'), 'workerKases');
$app->post('/kases_worker_status', authorize('user'), 'workerKasesByStatus');
$app->post('/kases_worker_type', authorize('user'), 'workerKasesByType');
$app->get('/kases_workload/:user_id', authorize('user'), 'employeeWorkload');

$app->get('/unattendedcount', authorize('user'), 'getUnattendedCount');
$app->get('/unattendeds', authorize('user'), 'getUnattendedKases');
$app->get('/unattendedcountall', authorize('user'), 'getUnattendedCountAll');
$app->get('/unattendedsall', authorize('user'), 'getUnattendedKasesAll');

$app->get('/inactivecount', authorize('user'), 'getInactiveCount');
$app->get('/inactives', authorize('user'), 'getInactiveKases');

$app->get('/inactivecount/wcab', authorize('user'), 'getInactiveWCABCount');
$app->get('/inactivesuboutcount/wcab', authorize('user'), 'getInactiveWCABSuboutCount');
$app->get('/inactives/wcab', authorize('user'), 'getInactiveWCABKases');
$app->get('/inactivesub/wcab', authorize('user'), 'getInactiveWCABSubOutKases');

$app->get('/inactivecount/pi', authorize('user'), 'getInactivePICount');
$app->get('/inactives/pi', authorize('user'), 'getInactivePIKases');
$app->get('/kases/filelocation/:case_id', authorize('user'), 'getFileLocationInfo');
$app->get('/casecountbyletter', authorize('user'), 'getCaseCountByLastNameLetter');
$app->get('/casecountbyletter/:letter', authorize('user'), 'getCaseIDsbyLastNameLetter');

$app->get('/kases/matrixadj/:id/:adj_number', authorize('user'), 'findMatrixOrderADJ');
$app->get('/kases/matrixorder/:id', authorize('user'), 'findMatrixOrder');
$app->get('/kases/matrixorderinfo/:case_id', authorize('user'), 'getLinkedMatrixOrder');

$app->post('/kases/matrix', authorize('user'), 'getMatrix');
$app->get('/kases/matrixsent/:case_id', authorize('user'), 'getMatrixActivitySent');
$app->post('/kases/matrixlocation', authorize('user'), 'getMatrixLocation');
$app->get('/kases/matrixlinkinfo/:id/:case_id', authorize('user'), 'getLinkedMatrixOrderInfo');
$app->get('/kases/matrixsearch/:applicant', authorize('user'), 'searchMatrixOrder');
$app->post('/kases/matrixlink', authorize('user'), 'linkMatrixOrder');
$app->post('/kases/matrixrequestlocation', authorize('user'), 'getRequestLocation');
$app->post('/kases/matrixsyslocation', authorize('user'), 'getMatrixLocationByReq');
$app->post('/kases/addon', authorize('user'), 'addMatrixLocation');

$app->post('/kase/view', authorize('user'), 'viewKase');
$app->post('/kase/no_note', authorize('user'), 'leaveKase');
$app->post('/kase/add', authorize('user'), 'addKase');
$app->post('/kase/addintake', authorize('user'), 'addIntake');
$app->post('/kase/accept', authorize('user'), 'acceptKase');
$app->post('/kase/reject', authorize('user'), 'rejectKase');
$app->post('/kase/advancesearch', authorize('user'), 'searchAdvanceKase');
$app->post('/kase/delete', authorize('user'), 'deleteKase');

$app->get('/kases/getall/:user_id/:job', authorize('user'), 'getEmployeeKaseCountByJob');
$app->post('/kases/clearall', authorize('user'), 'deleteAttorneyKases');

$app->post('/kases/filters', authorize('user'), 'filterKases');
$app->post('/kase/update', authorize('user'), 'updateKase');
$app->post('/kases/transfer', authorize('user'), 'transferKases');
$app->post('/kase/field/update', authorize('user'), 'updateKaseField');
$app->post('/kase/rename', authorize('user'), 'renameKase');
$app->post('/kase/claim', authorize('user'), 'saveSSNClaim');
$app->post('/kase/filelocation', authorize('user'), 'fileLocation');
$app->post('/kase/relate', authorize('user'), 'relateKase');
$app->post('/kase/unrelate', authorize('user'), 'unrelateKase');
$app->post('/kases/assign/worker', authorize('user'), 'assignWorker');

//JetFile integration
$app->post('/kase/jetfile', authorize('user'), 'jetfileKase');

function getExaminerKases() {
	$_SESSION["examiner_case_id"] = passed_var("case_id", "post");
	$_SESSION["examiner_name"] = passed_var("examiner", "post");
	$_SESSION["examiner_carrier_id"] = passed_var("carrier_id", "post");
	getKases("", "examiner");
	
	//reset
	$_SESSION["examiner_case_id"] = "";
	$_SESSION["examiner_name"] = "";
	$_SESSION["examiner_carrier_id"] = "";
	
	unset($_SESSION["examiner_case_id"]);
	unset($_SESSION["examiner_name"]);
	unset($_SESSION["examiner_carrier_id"]);
}
function getPIKases() {
	getKases("", "pi");
}
function getWCKases() {
	getKases("", "wc");
}
function getOpenKasesLimited ($start, $direction = "next") {
	$limit = " LIMIT " . $start . ", " . KASES_LIMIT;
	
	getKases($limit);
}
function getKasesByPage($start) {
	getOpenKasesLimited($start);
}
function getOpenKases () {
	getOpenKasesLimited(0);
}
function getClosedKases () {
	getKases("", "closed");
}
function getNoTasksKases() {
	getKases("", "no_tasks");
}
function getAllKases() {
	getKases("", "show_all");
}
function getNoWorkerKases() {
	//getKases("", "no_workers");
}
function getIntakeKasesByLetter($filter, $type, $letter) {
	getFilteredIntakeKases($filter, $type, $letter);
}
function getFilteredIntakeKases($filter, $type, $letter = "") {
	if ($filter=="_") {
		$filter = "";
	}
	if ($type=="_") {
		$type = "";
	}
	getIntakeKases($filter, $type, $letter);
}
function getIntakeKases($filter = "", $type = "", $letter = "") {
	//getKases("", "intake");
	session_write_close();
	$customer_id = $_SESSION["user_customer_id"];
	
	$sql = "SELECT cct.time_stamp, cct.user_uuid, cct.user_logon, 
	IFNULL(app.first_name, '*') first_name, IFNULL(app.last_name, '*') last_name, 
	IFNULL(app.full_name, IFNULL(plaintiff.`company_name`, '')) `full_name`, 
	ccase.case_id, ccase.case_name, ccase.case_number, ccase.file_number,  ccase.case_type, ccase.case_language language, ccase.case_date,
	IF (ccase.case_status = 'Intake', 'Pending', ccase.case_status) case_status, ccase.special_instructions,
	inj.explanation, 
	IF(inj.start_date = '0000-00-00 00:00:00', IFNULL(pi.personal_injury_date, '0000-00-00 00:00:00'), inj.start_date) start_date,  
	IFNULL(inj.occupation, '') occupation,
	inj.end_date,
	pi.personal_injury_info,
	IFNULL(plaintiff.`corporation_id`,-1) plaintiff_id, plaintiff.`corporation_uuid` plaintiff_uuid, plaintiff.`company_name` plaintiff, plaintiff.`full_address` plaintiff_full_address
	
	FROM cse_case ccase
	
	LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N'
	LEFT OUTER JOIN ";
			
	if (($_SESSION['user_customer_id']==1033)) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "cse_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
	
	LEFT OUTER JOIN cse_case_injury cci
	ON ccase.case_uuid = cci.case_uuid
	
	LEFT OUTER JOIN cse_injury inj
	ON cci.injury_uuid = inj.injury_uuid
	
	LEFT OUTER JOIN cse_personal_injury pi
	ON ccase.case_id = pi.case_id
	
	LEFT OUTER JOIN `cse_case_corporation` pcorp
	ON (ccase.case_uuid = pcorp.case_uuid AND pcorp.attribute = 'plaintiff' AND pcorp.deleted = 'N')
	LEFT OUTER JOIN `cse_corporation` plaintiff
	ON pcorp.corporation_uuid = plaintiff.corporation_uuid
	
	INNER JOIN (
		SELECT case_uuid, user_uuid, user_logon, time_stamp 
		FROM cse_case_track
		WHERE case_status = 'intake'
		AND operation = 'insert'
	) cct
	ON ccase.case_uuid = cct.case_uuid
	
	WHERE ccase.deleted != 'Y'
	AND ccase.customer_id = :customer_id";
	
	if ($type != "") {
		if ($type=="pi") {
			$sql .= " 
			AND case_type NOT LIKE 'WC%' AND case_type NOT LIKE 'W/C%' AND case_type NOT LIKE 'Worker%' ";
			$sql .= " 
			AND case_type != 'social_security' ";
		}
		if ($type=="wcab") {
			$sql .= " 
			AND (case_type LIKE 'WC%' OR case_type LIKE 'W/C%' OR case_type LIKE 'Worker%') ";
		}
		if ($type=="social_security") {
			$sql .= " 
			AND (case_type = 'social_security') ";
		}
		if ($type=="others") {
			$sql .= " 
			AND case_type != 'social_security' ";
			$sql .= " 
			AND case_type NOT LIKE 'WC%' AND case_type NOT LIKE 'W/C%' AND case_type NOT LIKE 'Worker%' ";
			$sql .= " 
			AND case_type != 'Slip and Fall%' AND case_type != 'NewPI'  AND case_type != 'Other' AND case_type NOT LIKE 'Personal Injury%' ";
		}

	}
	if ($filter != "") {
		switch($filter) {
			case "pending":
				$sql .= "
				AND ccase.case_status = 'Intake'
				";
				break;
			case "rejected":
				$sql .= "
				AND ccase.case_status = 'REJECTED'
				";
				break;
			case "accepted":
				$sql .= "
				AND ccase.case_status != 'REJECTED'
				AND ccase.case_status != 'Intake'
				";
				break;
		}
	}
	
	if ($letter!="") {
		$sql .= "
		AND SUBSTRING(IFNULL(app.last_name, ''), IFNULL(TRIM(app.full_name), ''), 1, 1) = '" . $letter . "'";
	}
	/*
	$sql .= "
	ORDER BY TRIM(app.last_name), TRIM(app.first_name)";
	*/
	//$sql .= " ORDER BY IFNULL(app.last_name, ''), IFNULL(TRIM(app.full_name), ''), ccase.case_name, ccase.case_id, inj.injury_number";
	$sql .= "
	ORDER BY IFNULL(plaintiff.`company_name`, app.first_name), app.full_name
	";
	try {
		
		if ($_SERVER['REMOTE_ADDR']=='47.153.49.248') {
			//die($sql);
		}
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		
		$kases = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		$stmt->closeCursor(); $stmt = null; $db = null;
		foreach($kases as $kase) {
			//die(print_r($kase));
			$arrFields = array();
			$kase->doi = "";
			if ($kase->start_date!="" && $kase->start_date!="0000-00-00"  && $kase->start_date!="0000-00-00 00:00:00") {
				$kase->doi = date("m/d/Y", strtotime($kase->start_date));
			}
			if ($kase->end_date!="" && $kase->end_date!="0000-00-00"  && $kase->end_date!="0000-00-00 00:00:00") {
				$kase->doi .= " - " .  date("m/d/Y", strtotime($kase->end_date)). " CT";
			}
		}
		echo json_encode($kases);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function setCurrentKasesCount($kases) {
	if ($kases < 0) {
		$_SESSION["start_kases"] = KASES_LIMIT;
	} else {
		$_SESSION["start_kases"] = $kases;
	}
	echo json_encode(array("success"=>true));
}
function getSess() {
	die(print_r($_SESSION));
}
function getNextKases() {
	getOpenKasesLimited($_SESSION["start_kases"], "next");
}
function getPreviousKases() {
	getOpenKasesLimited($_SESSION["start_kases"], "previous");
}
function csvEmailKases() {
	getEmailKases("csv");
}
function getEmailKases ($output = "") {
	getKases("", " AND IFNULL(app.email, '') != ''", $output);
}
function getRecentKasesNew() {
	getKases("", "recent");
}
function getKasesByIDs() {
	$ids = passed_var("ids", "post");
	//die($ids);
	$_SESSION["kase_ids"] = $ids;
	getKases();
}
function getKases($limit = " LIMIT 0, 1000", $filter = "", $output = "json") {
	if ($_SESSION['user_customer_id']=="") {
		return false;
	}
	if (!is_numeric($_SESSION['user_customer_id'])) {
		return false;
	}
	
	$script_filename = $_SERVER['SCRIPT_FILENAME'];
	$arrScript = explode("/", $_SERVER['SCRIPT_FILENAME']);
	$host = $arrScript[count($arrScript)-1]; 
	
	//specific ids
	$search_ids = "";
	if (isset($_SESSION["kase_ids"])) {
		$search_ids = $_SESSION["kase_ids"];
		$_SESSION["kase_ids"] = "";
		unset($_SESSION["kase_ids"]);
	}
	
	$wcab_only = false;
	$pi_only = false;
	$subout_equal = "";
	if (isset($_SESSION["inactive_type"])) {
		$subout_equal = " != ";
		$wcab_only = ($_SESSION["inactive_type"] == "wcab");
		$pi_only = ($_SESSION["inactive_type"] == "pi");
		
		unset($_SESSION["inactive_type"]);
		
		if (isset($_SESSION["inactive_subout"])) {
			$subout_equal = " = ";
			unset($_SESSION["inactive_subout"]);
		}
	}
	
	$blnIntake = false;
    $sql = "SELECT DISTINCT 
			inj.injury_id id, '-2' `previous_kases`, '-2' `start_kases`, ccase.case_id, ccase.lien_filed, ccase.special_instructions,ccase.case_description, inj.injury_number, ccase.case_uuid uuid, IF(ccase.case_number='UNKNOWN' AND ccase.file_number!='', '', ccase.case_number) case_number, ccase.file_number, ccase.cpointer,ccase.source, inj.injury_number, inj.adj_number, ccase.rating, ccase.injury_type, ccase.sub_in, inj.`type` main_injury_type,
			IF (DATE_FORMAT(ccase.case_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.case_date, '%m/%d/%Y')) `case_date`, 
			IF (DATE_FORMAT(ccase.terminated_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.terminated_date, '%m/%d/%Y')) `terminated_date`,
ccase.case_type, ccase.medical, ccase.td, ccase.rehab,  ccase.edd, ccase.claims,
			venue.venue_uuid, IF(venue.venue IS NULL, '', venue.venue) venue, venue.address1 venue_street, venue.address2 venue_suite, venue.city venue_city, venue.zip venue_zip, IFNULL(venue_abbr, '') venue_abbr, ccase.case_status, ccase.case_substatus, ccase.case_subsubstatus, ccase.submittedOn, ccase.supervising_attorney,
    ccase.attorney, 
			IFNULL(superatt.nickname, '') as supervising_attorney_name, IFNULL(superatt.user_name, '') as supervising_attorney_full_name,
			ccase.worker, ccase.interpreter_needed, ccase.file_location, ccase.case_language `case_language`, 
			app.person_id applicant_id, app.person_uuid applicant_uuid, IFNULL(app.salutation, '') applicant_salutation,
			IF (app.first_name IS NULL, '', TRIM(app.first_name)) first_name , IF(app.last_name IS NULL, '', app.last_name) last_name, IFNULL(app.full_name, '') `full_name`, IFNULL(app.language, '') language,
			IF (DATE_FORMAT(app.dob, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(app.dob, '%m/%d/%Y')) dob, app.dob app_dob, 
			IF (app.ssn = 'XXXXXXXXX', '', app.ssn) ssn, IFNULL(app.email, '') applicant_email, IFNULL(app.phone, '') applicant_phone,IFNULL(app.full_address, '') applicant_full_address,
			IFNULL(employer.`corporation_id`,-1) employer_id, employer.`corporation_uuid` employer_uuid, IFNULL(employer.`company_name`, '') employer, employer.`full_address` employer_full_address, employer.`suite` `employer_suite`,
			IFNULL(defendant.`corporation_id`,-1) defendant_id, defendant.`corporation_uuid` defendant_uuid, defendant.`company_name` defendant, defendant.`full_address` defendant_full_address,
			IFNULL(plaintiff.`corporation_id`,-1) plaintiff_id, plaintiff.`corporation_uuid` plaintiff_uuid, plaintiff.`company_name` plaintiff, plaintiff.`full_address` plaintiff_full_address,
			IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')) start_date, IF (DATE_FORMAT(inj.statute_limitation, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.statute_limitation, '%m/%d/%Y')) statute_limitation, 
			IF (DATE_FORMAT(inj.end_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.end_date, '%m/%d/%Y')) end_date, inj.ct_dates_note,
			IF (inj.occupation IS NULL, '' , inj.occupation) occupation,
			CONCAT(app.first_name,' ',app.last_name,' vs ', IFNULL(employer.`company_name`, ''),' - ', 
            REPLACE(IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')), '00/00/0000', '')) `name`, TRIM(ccase.case_name) case_name, 
			att.user_id attorney_id, user.user_id, 
			IFNULL(att.nickname, '') as attorney_name, IFNULL(att.user_name, '') as attorney_full_name, 
			IFNULL(user.nickname, '') as worker_name, IFNULL(user.user_name, '') as worker_full_name,
			IFNULL(lien.lien_id, -1) lien_id, 
			IFNULL(settlement.settlement_id, IFNULL(settlementsheet.settlementsheet_id, -1)) settlement_id,
			IF (cif.fee_uuid IS NOT NULL, '1', '-1') fee_id, ccase.injury_type, ccase.sub_in,
			IFNULL(referring.`corporation_id`,-1) referring_id, referring.`corporation_uuid` referring_uuid, referring.`company_name` referring, referring.`full_address` referring_full_address, 			
			IFNULL(REPLACE(`referring`.`company_name`, ' ', ''), '') referring_search,
			IFNULL(closed_cases.closed_date, '') closed_date";
			
			if ($filter=="recent") {
				$sql .= ", recent.time_stamp recent_time_stamp";
			}
			$sql .= "
			FROM cse_case ccase ";
			/*
			
			*/
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
			
			if (isset($_SESSION["unattended_query"])) {
				$sql .= $_SESSION["unattended_query"];
				unset($_SESSION["unattended_query"]);
			}
			if (isset($_SESSION["inactive_query"])) {
				$sql .= $_SESSION["inactive_query"];
				unset($_SESSION["inactive_query"]);
			}
			if ($filter=="examiner") {
				$filter = "";
				if (isset($_SESSION["examiner_case_id"])) {
					$examiner_case_id = $_SESSION["examiner_case_id"];
					
					if ($examiner_case_id!="") {
						//$restricted_clients = "'" . str_replace(",", "','", $restricted_clients) . "'";
						$sql .= " INNER JOIN (
								SELECT 
									ccorp.case_uuid
								FROM
									`cse_corporation` corp
										INNER JOIN
									cse_case_corporation ccorp ON corp.corporation_uuid = ccorp.corporation_uuid
								WHERE
									corp.deleted = 'N'
										AND corp.corporation_id = " . $_SESSION["examiner_carrier_id"] . "
										AND corp.customer_id = '" . $_SESSION['user_customer_id'] . "'
										AND corp.`full_name` = '" . addslashes($_SESSION["examiner_name"]) . "'
							) restricteds
							ON ccase.case_uuid = restricteds.case_uuid";
					}
				}
			}
			if ($search_ids == "") {
				$sql .= " 
				INNER JOIN (
					SELECT case_id 
					FROM cse_case ";
				
				if ($filter!="closed" && $filter!="show_all" && $filter!="recent") {
					$sql .= "WHERE case_status NOT LIKE '%close%' AND case_status NOT LIKE 'CLO%' AND case_status != 'DROPPED' AND case_status != 'REJECTED'";
					
				}
				if ($filter=="intake") {
					$blnIntake = true;
					$sql .= " AND case_status = 'Intake' ";
				}
				if ($filter=="closed") {
					$sql .= "WHERE (case_status LIKE '%close%' OR case_status = 'DROPPED')";
				}
				if ($filter=="pi") {
					$sql .= " AND case_type NOT LIKE 'WC%' AND case_type NOT LIKE 'W/C%' AND case_type NOT LIKE 'Worker%' ";
				}
				if ($filter=="wc") {
					$sql .= " AND (case_type LIKE 'WC%' OR case_type LIKE 'W/C%' OR case_type LIKE 'Worker%') ";
				}
				if ($filter=="show_all") {
					$sql .= "WHERE 1";
				}
				if ($filter=="closed" || $filter=="show_all" || $filter=="pi" || $filter=="wc") {
					//we don't want it going forward
					$filter = "";
				}
				$sql .= "
				) climit
				ON ccase.case_id = climit.case_id
				";
			}
			
			$sql .= "
			LEFT OUTER JOIN (
				SELECT cct.case_id, cct.case_status, MIN(time_stamp) closed_date
				FROM cse_case_track cct
				WHERE (case_status LIKE '%close%' OR case_status LIKE 'CLO%' OR case_status = 'DROPPED') 
				AND operation = 'update'
				GROUP BY cct.case_id
            ) closed_cases
            ON ccase.case_id = closed_cases.case_id
			
			LEFT OUTER JOIN `cse_case_corporation` rcorp
			ON (ccase.case_uuid = rcorp.case_uuid AND rcorp.attribute = 'referring' AND rcorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` referring
			ON rcorp.corporation_uuid = referring.corporation_uuid
			
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N'
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
			LEFT OUTER JOIN `cse_case_corporation` ecorp
			ON (ccase.case_uuid = ecorp.case_uuid AND ecorp.attribute = 'defendant' AND ecorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` defendant
			ON ecorp.corporation_uuid = defendant.corporation_uuid
			
			LEFT OUTER JOIN `cse_case_corporation` pcorp
			ON (ccase.case_uuid = pcorp.case_uuid AND pcorp.attribute = 'plaintiff' AND pcorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` plaintiff
			ON pcorp.corporation_uuid = plaintiff.corporation_uuid
			
			INNER JOIN `cse_case_injury` cinj
			ON ccase.case_uuid = cinj.case_uuid AND cinj.deleted = 'N' AND cinj.`attribute` = 'main'
			INNER JOIN `cse_injury` inj
			ON cinj.injury_uuid = inj.injury_uuid AND inj.deleted = 'N'
			LEFT OUTER JOIN `cse_injury_lien` cil
			ON inj.injury_uuid = cil.injury_uuid
			LEFT OUTER JOIN `cse_injury_fee` cif ON inj.injury_uuid = cif.injury_uuid AND cif.deleted = 'N'
			LEFT OUTER JOIN `cse_lien` lien
			ON cil.lien_uuid = lien.lien_uuid
			LEFT OUTER JOIN `cse_injury_settlement` cis
			ON inj.injury_uuid = cis.injury_uuid AND cis.deleted = 'N' AND cis.`attribute` = 'main'
			LEFT OUTER JOIN `cse_settlement` settlement
			ON cis.settlement_uuid = settlement.settlement_uuid
			LEFT OUTER JOIN `cse_settlementsheet` settlementsheet
			ON cis.settlement_uuid = settlementsheet.settlementsheet_uuid
			LEFT OUTER JOIN ikase.`cse_user` superatt
			ON ccase.supervising_attorney = superatt.user_id
			LEFT OUTER JOIN ikase.`cse_user` att
			ON ccase.attorney = att.user_id
			LEFT OUTER JOIN ikase.`cse_user` user
			ON ccase.worker = user.user_id
			";
	$recent_sort = "";
	$blnRecent = false;
	if ($filter=="recent") {
		$blnRecent = true;
		$sql .= " 
		INNER JOIN (
		SELECT cct.case_id, MAX( time_stamp ) time_stamp
		FROM  `cse_case_track` cct
		INNER JOIN cse_case ccase ON cct.case_id = ccase.case_id
		WHERE operation =  'view'
		AND user_uuid =  '" . $_SESSION['user_id'] . "'
		AND ccase.customer_id = " . $_SESSION['user_customer_id'] . "
		AND ccase.deleted =  'N'
		GROUP BY cct.case_id
		ORDER BY MAX( time_stamp ) DESC 
		LIMIT 0 , 15
		) recent
		ON ccase.case_id = recent.case_id";
		$filter = "";
		$recent_sort = " ORDER BY recent.time_stamp DESC";	
		$limit = " LIMIT 0, 100";
	}
	$additional_where = "";
	if ($filter=="no_tasks") {
		$sql .= " LEFT OUTER JOIN (
			SELECT DISTINCT ccase.case_id
			FROM cse_case ccase
			INNER JOIN cse_case_task cct
			ON ccase.case_uuid = cct.case_uuid
			INNER JOIN cse_task task
			ON cct.task_uuid = task.task_uuid
			WHERE task.deleted = 'N' AND ccase.customer_id = " . $_SESSION['user_customer_id'] . "
			) case_tasks
			ON ccase.case_id = case_tasks.case_id";
			$additional_where = $filter;
			$filter = "";
	}
	$blnWorkers = false;
	if ($filter=="no_workers") {
		$sql .= " INNER JOIN (
				SELECT DISTINCT case_id
				FROM cse_case ccase
				WHERE supervising_attorney = ''
				AND attorney = ''
				AND worker = ''
				AND deleted = 'N'
				AND INSTR(ccase.case_status, 'Closed') = 0 AND INSTR(ccase.case_status, 'CL-') = 0
				AND INSTR(ccase.case_status, 'Dropped') = 0
				AND INSTR(ccase.case_status, 'REJECTED') = 0
				AND INSTR(ccase.case_status, 'Intake') = 0
				AND ccase.customer_id = " . $_SESSION['user_customer_id'] . "
			) case_workers
			ON ccase.case_id = case_workers.case_id";
			$additional_where = "";
			$filter = "";
			$blnWorkers = true;
	}
	$blnIntake = false;
	$sql .= " 
	WHERE ccase.deleted ='N'";
	if ($filter=="intake") {
		$blnIntake = true;
		$sql .= " AND ccase.case_status = 'Intake' ";
		$filter = "";
	}
	$sql .= " AND ccase.customer_id = " . $_SESSION['user_customer_id'] . $filter;
	if ($additional_where=="no_tasks") {
		$sql .= " 
		AND case_tasks.case_id IS NULL
		";	
	}
	
	if ($search_ids != "") {
		$sql .= " 
		AND ccase.case_id IN (" . $search_ids . ")
		";	
	}
	if ($pi_only) {
		$sql .= " AND ccase.case_type NOT LIKE 'WC%' 
		AND ccase.case_type NOT LIKE 'W/C%' 
		AND ccase.case_type NOT LIKE 'Worker%'
		
		AND ccase.case_type != 'social_security'
		 ";
	}
	if ($wcab_only) {
		$sql .= " AND (ccase.case_type LIKE 'WC%' 
		OR ccase.case_type LIKE 'W/C%' 
		OR ccase.case_type LIKE 'Worker%') ";
		
		if ($subout_equal!="") {
			if ($subout_equal == " = ") {
				$sql .= " AND (ccase.case_status ". $subout_equal . " 'OP-SUBOUT'";
				$sql .= " OR ccase.case_status ". $subout_equal . " 'Sub')";
			}
			if ($subout_equal == " != ") {
				$sql .= " AND ccase.case_status ". $subout_equal . " 'OP-SUBOUT'";
				$sql .= " AND ccase.case_status ". $subout_equal . " 'Sub'";
			}
		}
	}
	
	$sort_by = "";
	if (isset($_GET["sort_by"])){
		$sort_by = $_GET["sort_by"];
		if ($sort_by=="last_name") {
			$sql .= " ORDER BY TRIM(app.last_name), TRIM(app.first_name), ccase.case_id, inj.injury_number";
		}
	}

	if ($recent_sort != "") {
		$sql .= $recent_sort;
		//die($sql);
	} else {
		if ($sort_by == "" && $search_ids == "") {
			$sql .= " ORDER BY IFNULL(IF (TRIM(IFNULL(app.first_name, '')) = '', IFNULL(TRIM(app.full_name), ccase.case_name), TRIM(app.first_name)), IFNULL(plaintiff.`company_name`, '')), 
			ccase.case_id, inj.injury_number";
		}
	}
	
	if ($search_ids != "") {
		$sql .= " ORDER BY ccase.case_id DESC";
	}
	
	$sql .= "
	" . $limit;
	//
	$_SESSION["current_kase_search_term"] = "";
	//if (!$blnIntake) {
		$_SESSION["current_kase_query"] = $sql;
	//}
	if ($blnRecent) {
		$_SESSION["recent_query"] = $sql;
	}
	session_write_close();
	if ($_SERVER['REMOTE_ADDR'] == "47.156.103.17") {
		//die($sql);
	}
	writeQuery($sql);
	
	try {
		
		$db = getConnection();
		$stmt = $db->query($sql);
		$kases = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		/*
		if (($_SESSION['user_customer_id']==1049)) { 
			echo "count:" . count($kases). "<br />" . $sql;
			die();
		}
		*/
		if ($output=="json") {
			echo json_encode($kases);
		}
		if ($output=="csv") {
			// Create the csv file
			$csv_dir = 'D:\\uploads\\' . $_SESSION['user_customer_id'] . '\\';
			if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $csv_dir)) {
				//die($_SERVER['DOCUMENT_ROOT'] . $csv_dir);
				mkdir($_SERVER['DOCUMENT_ROOT'] . $csv_dir, 0777, true);
			}
			$csv_dir .= 'csv\\';
			//echo $_SERVER['DOCUMENT_ROOT'] . $csv_dir . "\r\n";
			if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $csv_dir)) {
				//die("2) " . $_SERVER['DOCUMENT_ROOT'] . $csv_dir);
				mkdir($_SERVER['DOCUMENT_ROOT'] . $csv_dir, 0777, true);
			}
			$csv_filename = $_SERVER['DOCUMENT_ROOT'] . $csv_dir . "email_report.csv";
			//die($csv_filename);
			$kases_output = fopen($csv_filename, "w");
			
			$arrHeader = array("Applicant", "Email", "Case Number", "Case Name", "Referrer", "ADJ", "DOI", "Atty", "Case Type	", "Status");
			fputcsv($kases_output, $arrHeader);
			foreach($kases as $kase) {
				//die(print_r($kase));
				$arrFields = array();
				$kase->doi = $kase->start_date;
				if ($kase->end_date!="" && $kase->end_date!="0000-00-00") {
					$kase->doi .= " - " . $kase->end_date . " CT";
				}
				
				array_push($arrFields, $kase->full_name);
				array_push($arrFields, $kase->applicant_email);
				array_push($arrFields, $kase->case_number);
				array_push($arrFields, $kase->name);
				array_push($arrFields, $kase->referring);
				array_push($arrFields, $kase->adj_number);
				array_push($arrFields, $kase->doi);
				array_push($arrFields, strtoupper($kase->attorney_name));
				array_push($arrFields, $kase->case_type);
				array_push($arrFields, $kase->case_status);
				
				// echo print_r($arrFields);
				fputcsv($kases_output, $arrFields, ',' , '"' );
			}
			echo json_encode(array("success"=>"true", "filename"=>"email_report.csv"));
		}
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getClientKasesListByMonth($year, $month, $client) {
	getKasesListByMonth($year, $month, $client, "client");
}
function getReferredKasesListByMonth($year, $month, $referring) {
	getKasesListByMonth($year, $month, $referring);
}
function getKasesListByMonth($year, $month, $corporation_filter = "", $corporation_type = "referring") {
	session_write_close();
	if ($_SESSION['user_customer_id']=="") {
		return false;
	}
	if (!is_numeric($_SESSION['user_customer_id'])) {
		return false;
	}
	
	//passed id
	if (is_numeric($corporation_filter)) {
		$corporation = getCorporationInfo($corporation_filter);
		$corporation_filter = $corporation->company_name;
	}
	$join = " LEFT OUTER JOIN ";
	if ($corporation_filter!="") {
		$join = " INNER JOIN ";
	}
    $sql = "SELECT DISTINCT 
			inj.injury_id id, ccase.case_id, ccase.lien_filed, ccase.special_instructions,ccase.case_description, inj.injury_number, ccase.case_uuid uuid, IF(ccase.case_number='UNKNOWN' AND ccase.file_number!='', '', ccase.case_number) case_number, ccase.file_number, ccase.cpointer,ccase.source, inj.injury_number, inj.adj_number, inj.statute_limitation, 
			ccase.rating, 
			ccase.submittedOn, 
			TIMESTAMPDIFF(DAY, ccase.submittedOn, '" . date("Y-m-d") . "' ) months_diff,
			IF (DATE_FORMAT(ccase.case_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.case_date, '%m/%d/%Y')) `case_date`, 
			IF (DATE_FORMAT(ccase.terminated_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.terminated_date, '%m/%d/%Y')) `terminated_date`,
ccase.case_type, ccase.medical, ccase.td, ccase.rehab,  ccase.edd, ccase.claims,
			venue.venue_uuid, IF(venue.venue IS NULL, '', venue.venue) venue, venue.address1 venue_street, venue.address2 venue_suite, venue.city venue_city, venue.zip venue_zip, venue_abbr, ccase.case_status, ccase.case_substatus, ccase.case_subsubstatus, ccase.submittedOn, ccase.supervising_attorney,
    ccase.attorney, 
			IFNULL(superatt.nickname, '') as supervising_attorney_name, IFNULL(superatt.user_name, '') as supervising_attorney_full_name,
			ccase.worker, ccase.interpreter_needed, ccase.file_location, ccase.case_language `case_language`, 
			app.person_id applicant_id, app.person_uuid applicant_uuid, IFNULL(app.salutation, '') applicant_salutation,
			IF (app.first_name IS NULL, '', TRIM(app.first_name)) first_name , IF(app.last_name IS NULL, '', app.last_name) last_name, IFNULL(app.full_name, '') `full_name`, IFNULL(app.language, '') language,
			IF (DATE_FORMAT(app.dob, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(app.dob, '%m/%d/%Y')) dob, app.dob app_dob, 
			IF (app.ssn = 'XXXXXXXXX', '', app.ssn) ssn, IFNULL(app.email, '') applicant_email, IFNULL(app.phone, '') applicant_phone,IFNULL(app.full_address, '') applicant_full_address, 
			app.street applicant_street, app.suite applicant_suite, app.city applicant_city, app.state applicant_state, app.zip applicant_zip, app.full_address applicant_full_address,
			IFNULL(employer.`corporation_id`,-1) employer_id, employer.`corporation_uuid` employer_uuid, IFNULL(employer.`company_name`, '') employer, employer.`full_address` employer_full_address, employer.`suite` `employer_suite`,
			
			IFNULL(referring.`corporation_id`,-1) referring_id, referring.`corporation_uuid` referring_uuid, referring.`company_name` referring, referring.`full_address` referring_full_address, 			
			IFNULL(REPLACE(`referring`.`company_name`, ' ', ''), '') referring_search,
			
			IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')) start_date, IF (DATE_FORMAT(inj.statute_limitation, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.statute_limitation, '%m/%d/%Y')) statute_limitation, 
			IF (DATE_FORMAT(inj.end_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.end_date, '%m/%d/%Y')) end_date, inj.ct_dates_note,
			IF (inj.occupation IS NULL, '' , inj.occupation) occupation,
			CONCAT(app.first_name,' ',app.last_name,' vs ', IFNULL(employer.`company_name`, ''),' - ', 
            REPLACE(IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')), '00/00/0000', '')) `name`, ccase.case_name, 
			att.user_id attorney_id, user.user_id, 
			IFNULL(att.nickname, '') as attorney_name, IFNULL(att.user_name, '') as attorney_full_name, 
			IFNULL(user.nickname, '') as worker_name, IFNULL(user.user_name, '') as worker_full_name,
			IFNULL(lien.lien_id, -1) lien_id, 
			IFNULL(settlement.settlement_id, IFNULL(settlementsheet.settlementsheet_id, -1)) settlement_id,
			IF (cif.fee_uuid IS NOT NULL, '1', '-1') fee_id
			, ccase.injury_type, ccase.sub_in ,
			IFNULL(closed_cases.closed_date, '') closed_date
			
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
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N'
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
			
			" . $join . " `cse_case_corporation` rcorp
			ON (ccase.case_uuid = rcorp.case_uuid AND rcorp.attribute = '" . $corporation_type . "' AND rcorp.deleted = 'N')
			" . $join . " `cse_corporation` referring
			ON rcorp.corporation_uuid = referring.corporation_uuid
			
			INNER JOIN `cse_case_injury` cinj
			ON ccase.case_uuid = cinj.case_uuid AND cinj.deleted = 'N' AND cinj.`attribute` = 'main'
			INNER JOIN `cse_injury` inj
			ON cinj.injury_uuid = inj.injury_uuid AND inj.deleted = 'N'
			LEFT OUTER JOIN `cse_injury_lien` cil
			ON inj.injury_uuid = cil.injury_uuid
			LEFT OUTER JOIN `cse_injury_fee` cif ON inj.injury_uuid = cif.injury_uuid AND cif.deleted = 'N'
			LEFT OUTER JOIN `cse_lien` lien
			ON cil.lien_uuid = lien.lien_uuid
			LEFT OUTER JOIN `cse_injury_settlement` cis
			ON inj.injury_uuid = cis.injury_uuid AND cis.deleted = 'N' AND cis.`attribute` = 'main'
			 LEFT OUTER JOIN `cse_settlement` settlement
			ON cis.settlement_uuid = settlement.settlement_uuid
			LEFT OUTER JOIN `cse_settlementsheet` settlementsheet
			ON cis.settlement_uuid = settlementsheet.settlementsheet_uuid
			LEFT OUTER JOIN ikase.`cse_user` superatt
			ON ccase.supervising_attorney = superatt.user_id
			LEFT OUTER JOIN ikase.`cse_user` att
			ON ccase.attorney = att.user_id
			LEFT OUTER JOIN ikase.`cse_user` user
			ON ccase.worker = user.user_id
			
			LEFT OUTER JOIN (
				SELECT cct.case_id, cct.case_status, MIN(time_stamp) closed_date
				FROM cse_case_track cct
				WHERE (case_status LIKE '%close%' OR case_status LIKE 'CL%' OR case_status = 'DROPPED') 
				AND operation = 'update'
				GROUP BY cct.case_id
            ) closed_cases
            ON ccase.case_id = closed_cases.case_id
			
			WHERE ccase.deleted ='N'";
	if ($year > -1) {
		$sql .= "
			AND YEAR(ccase.case_date) = '" . $year . "'";
	}
	if ($month > -1) {
		$sql .= " AND MONTH(ccase.case_date) = '" . $month . "'";
	}
	if ($corporation_filter != "") {
		$corporation_filter = str_replace("_", " ", $corporation_filter);
		//$corporation_filter = str_replace("%27", "'", $corporation_filter);
		$sql .= " AND TRIM(`referring`.`company_name`) = '" . addslashes($corporation_filter) . "'";
	}
	$sql .= " AND ccase.case_status != 'Intake' AND ccase.customer_id = " . $_SESSION['user_customer_id'];
	//$sql .= " ORDER BY ccase.case_number, inj.injury_number";
	$sql .= " ORDER BY app.last_name, app.first_name,  ccase.case_number, inj.injury_number";
	
	if ($_SERVER['REMOTE_ADDR']=='47.156.103.17') {
		//die($sql);
	}
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		//$stmt->bindParam("year", $year);
		//$stmt->bindParam("month", $month);
		$stmt = $db->query($sql);
		$kases = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		
		echo json_encode($kases);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
} 
function getInactives($days) {
	session_write_close();
	if ($_SESSION['user_customer_id']=="") {
		return false;
	}
	if (!is_numeric($_SESSION['user_customer_id'])) {
		return false;
	}
	
	$milestone  = mktime(0, 0, 0, date("m")  , date("d") - $days, date("Y"));
	$milestone = date("Y-m-d", $milestone);
	
	$sql = "SELECT 
inj.injury_id id, ccase.case_id, ccase.lien_filed, ccase.special_instructions,ccase.case_description, inj.injury_number, ccase.case_uuid uuid, IF(ccase.case_number='UNKNOWN' AND ccase.file_number!='', '', ccase.case_number) case_number, ccase.file_number, ccase.cpointer,ccase.source, inj.injury_number, inj.adj_number, ccase.rating, 
			IF (DATE_FORMAT(ccase.case_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.case_date, '%m/%d/%Y')) `case_date`, 
			IF (DATE_FORMAT(ccase.terminated_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.terminated_date, '%m/%d/%Y')) `terminated_date`,
ccase.case_type, ccase.medical, ccase.td, ccase.rehab,  ccase.edd, ccase.claims,
			venue.venue_uuid, IF(venue.venue IS NULL, '', venue.venue) venue, venue.address1 venue_street, venue.address2 venue_suite, venue.city venue_city, venue.zip venue_zip, venue_abbr, ccase.case_status, ccase.case_substatus, ccase.case_subsubstatus, ccase.submittedOn, ccase.supervising_attorney,
    ccase.attorney, 
			IFNULL(superatt.nickname, '') as supervising_attorney_name, IFNULL(superatt.user_name, '') as supervising_attorney_full_name,
			ccase.worker, ccase.interpreter_needed, ccase.file_location, ccase.case_language `case_language`, 
			app.person_id applicant_id, app.person_uuid applicant_uuid, IFNULL(app.salutation, '') applicant_salutation,
			IF (app.first_name IS NULL, '', TRIM(app.first_name)) first_name , IF(app.last_name IS NULL, '', app.last_name) last_name, IFNULL(app.full_name, '') `full_name`, IFNULL(app.language, '') language,
			IF (DATE_FORMAT(app.dob, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(app.dob, '%m/%d/%Y')) dob, app.dob app_dob, 
			IF (app.ssn = 'XXXXXXXXX', '', app.ssn) ssn, IFNULL(app.email, '') applicant_email, IFNULL(app.phone, '') applicant_phone,IFNULL(app.full_address, '') applicant_full_address,
			IFNULL(employer.`corporation_id`,-1) employer_id, employer.`corporation_uuid` employer_uuid, IFNULL(employer.`company_name`, '') employer, employer.`full_address` employer_full_address, employer.`suite` `employer_suite`,
			IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')) start_date, IF (DATE_FORMAT(inj.statute_limitation, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.statute_limitation, '%m/%d/%Y')) statute_limitation, 
			IF (DATE_FORMAT(inj.end_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.end_date, '%m/%d/%Y')) end_date, inj.ct_dates_note,
			IF (inj.occupation IS NULL, '' , inj.occupation) occupation,
			CONCAT(app.first_name,' ',app.last_name,' vs ', IFNULL(employer.`company_name`, ''),' - ', 
            REPLACE(IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')), '00/00/0000', '')) `name`, ccase.case_name, 
			att.user_id attorney_id, user.user_id, 
			IFNULL(att.nickname, '') as attorney_name, IFNULL(att.user_name, '') as attorney_full_name, 
			IFNULL(user.nickname, '') as worker_name, IFNULL(user.user_name, '') as worker_full_name,
			IFNULL(lien.lien_id, -1) lien_id, 
			IFNULL(settlement.settlement_id, IFNULL(settlementsheet.settlementsheet_id, -1)) settlement_id,
			IF (cif.fee_uuid IS NOT NULL, '1', '-1') fee_id
			, ccase.injury_type, ccase.sub_in FROM cse_case ccase ";

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
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N'
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
			INNER JOIN `cse_case_injury` cinj
			ON ccase.case_uuid = cinj.case_uuid AND cinj.deleted = 'N' AND cinj.`attribute` = 'main'
			INNER JOIN `cse_injury` inj
			ON cinj.injury_uuid = inj.injury_uuid AND inj.deleted = 'N'
			LEFT OUTER JOIN `cse_injury_lien` cil
			ON inj.injury_uuid = cil.injury_uuid
			LEFT OUTER JOIN `cse_injury_fee` cif ON inj.injury_uuid = cif.injury_uuid AND cif.deleted = 'N'
			LEFT OUTER JOIN `cse_lien` lien
			ON cil.lien_uuid = lien.lien_uuid
			LEFT OUTER JOIN `cse_injury_settlement` cis
			ON inj.injury_uuid = cis.injury_uuid AND cis.deleted = 'N' AND cis.`attribute` = 'main'
			LEFT OUTER JOIN `cse_settlement` settlement
			ON cis.settlement_uuid = settlement.settlement_uuid
			LEFT OUTER JOIN `cse_settlementsheet` settlementsheet
			ON cis.settlement_uuid = settlementsheet.settlementsheet_uuid
			LEFT OUTER JOIN ikase.`cse_user` superatt
			ON ccase.supervising_attorney = superatt.user_id
			LEFT OUTER JOIN ikase.`cse_user` att
			ON ccase.attorney = att.user_id
			LEFT OUTER JOIN ikase.`cse_user` user
			ON ccase.worker = user.user_id
INNER JOIN (
	SELECT ccase.case_id
FROM cse_activity ca
INNER JOIN cse_case_activity cca
ON ca.activity_uuid = cca.activity_uuid
INNER JOIN cse_case ccase
ON cca.case_uuid = ccase.case_uuid ";

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
WHERE 1
AND ccase.deleted = 'N'
AND ccase.case_status != 'Closed'
AND ccase.case_status != 'Closed by C & R'
AND ccase.case_status != 'Closed by Stipulation'
AND ccase.customer_id = " . $_SESSION['user_customer_id'] . "
GROUP BY ccase.case_id
HAVING MAX(ca.activity_date) > '" . $milestone . "') max_activity
ON ccase.case_id = max_activity.case_id
WHERE 1";
	$sql .= " ORDER BY IF (TRIM(app.last_name) = '', TRIM(app.full_name), TRIM(app.last_name)), ccase.case_id, inj.injury_number";
	
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$kases = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		echo json_encode($kases);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKasesBillableCount() {
	getKasesBillable(true);
}
function getKasesBillable($blnReturnCount = false) {
	session_write_close();
	
	//for now
	return false;
	
	$sql = "SELECT DISTINCT ccase.case_id";
	if (!$blnReturnCount) {
		$sql .= ",
		IFNULL(opr.account_id, '')  operating_account_id, 
		IFNULL(trst.account_id, '') trust_account_id,
		IFNULL(opr.account_name, '')  operating_account, 
		IFNULL(trst.account_name, '') trust_account,
		IF (ccase.case_name = '', IF (ccase.case_number = '', ccase.file_number, ccase.case_number), ccase.case_name) case_name,  
		(IFNULL(trust_ins.amounts, 0) - IFNULL(trust_outs.amounts, 0)) trust_balance,
		(IFNULL(operating_ins.amounts, 0) - IFNULL(operating_outs.amounts, 0)) operating_balance, 
		IFNULL(billables.billable, 0) billable,
		ccase.supervising_attorney,
		ccase.attorney,
		ccase.worker
		";
	}
	$sql .= "
	FROM  `cse_activity` act
	
	INNER JOIN  `cse_case_activity` cca
	ON  act.`activity_uuid` = cca.`activity_uuid`
	
	INNER JOIN `cse_case` ccase
	ON cca.`case_uuid` = ccase.`case_uuid`
	
	INNER JOIN (		
		SELECT DISTINCT ccase.case_id,  SUM(( act.hours * IFNULL(user.rate, 0)) + (IFNULL(act.billing_rate, 0) * act.billing_amount)) billable
		FROM cse_case ccase 
		INNER JOIN  `cse_case_activity` cca
		ON ccase.case_uuid = cca.case_uuid
		INNER JOIN cse_activity act
		ON  `cca`.`activity_uuid` = act.`activity_uuid`
		LEFT OUTER JOIN  (
		 SELECT itm.activity_uuid
		 FROM cse_kinvoiceitem itm
		 INNER JOIN cse_kinvoice inv
		 ON itm.kinvoice_uuid = inv.kinvoice_uuid
		 WHERE inv.deleted = 'N'
		 AND itm.activity_uuid != ''
		 AND inv.customer_id = :customer_id
		) ck
		ON  `act`.`activity_uuid` = `ck`.`activity_uuid`
	
		LEFT OUTER JOIN `ikase`.`cse_user` user
		ON act.activity_user_id = user.user_id
			
		WHERE 1
		AND `ck`.`activity_uuid` IS NULL
		AND act.deleted = 'N'
		AND (act.hours + act.billing_amount) > 0 
		AND ccase.deleted = 'N'
		AND ccase.customer_id = :customer_id
		GROUP BY ccase.case_id
	) billables
	ON ccase.case_id = billables.case_id
		
	LEFT OUTER JOIN ikase.`cse_user` superatt
	ON ccase.supervising_attorney = superatt.user_id
	LEFT OUTER JOIN ikase.`cse_user` att
	ON ccase.attorney = att.user_id
	LEFT OUTER JOIN ikase.`cse_user` user
	ON ccase.worker = user.user_id
	
	LEFT OUTER JOIN cse_case_kinvoice cck
	ON ccase.case_uuid = cck.case_uuid
	
	LEFT OUTER JOIN cse_kinvoiceitem ck
	ON  act.`activity_uuid` = `ck`.`activity_uuid` AND cck.kinvoice_uuid = ck.kinvoice_uuid
	
	LEFT OUTER JOIN
	(
		SELECT ccase.case_id, SUM(receipts.payment) amounts
		FROM cse_account acct
	
		INNER JOIN cse_account_check cac
		ON acct.account_uuid = cac.account_uuid AND cac.deleted = 'N'
	
		INNER JOIN cse_check receipts
		ON cac.check_uuid = receipts.check_uuid AND receipts.ledger = 'IN'
	
		INNER JOIN cse_case_check ccc
		ON receipts.check_uuid = ccc.check_uuid
	
		INNER JOIN cse_case ccase
		ON ccc.case_uuid = ccase.case_uuid
	
		WHERE acct.customer_id = :customer_id
		AND acct.deleted = 'N'
		AND acct.account_type = 'trust'
		AND receipts.deleted = 'N'
	
		GROUP BY ccase.case_id
	
	) trust_ins
	ON ccase.case_id = trust_ins.case_id
	
	LEFT OUTER JOIN (
		SELECT ccasew.case_id, SUM(withdraws.payment) amounts
		FROM cse_account acct
	
		INNER JOIN cse_account_check cacw
		ON acct.account_uuid = cacw.account_uuid AND cacw.deleted = 'N'
	
		INNER JOIN cse_check withdraws
		ON cacw.check_uuid = withdraws.check_uuid AND withdraws.ledger = 'OUT'
	
		INNER JOIN cse_case_check ccw
		ON withdraws.check_uuid = ccw.check_uuid AND ccw.deleted = 'N'
	
		INNER JOIN cse_case ccasew
		ON ccw.case_uuid = ccasew.case_uuid
	
		WHERE acct.customer_id = :customer_id
		AND acct.deleted = 'N'
		AND acct.account_type = 'trust'
		AND withdraws.deleted = 'N'
	
		GROUP BY ccasew.case_id
	) trust_outs
	ON ccase.case_id = trust_outs.case_id
	
	LEFT OUTER JOIN
	(
		SELECT ccase.case_id, SUM(receipts.payment) amounts
		FROM cse_account acct
	
		INNER JOIN cse_account_check cac
		ON acct.account_uuid = cac.account_uuid AND cac.deleted = 'N'
	
		INNER JOIN cse_check receipts
		ON cac.check_uuid = receipts.check_uuid AND receipts.ledger = 'IN'
	
		INNER JOIN cse_case_check ccc
		ON receipts.check_uuid = ccc.check_uuid
	
		INNER JOIN cse_case ccase
		ON ccc.case_uuid = ccase.case_uuid
	
		WHERE acct.customer_id = :customer_id
		AND acct.deleted = 'N'
		AND acct.account_type = 'operating'
		AND receipts.deleted = 'N'
	
		GROUP BY ccase.case_id
	
	) operating_ins
	ON ccase.case_id = operating_ins.case_id
	
	
	LEFT OUTER JOIN (
		SELECT ccasew.case_id, SUM(withdraws.payment) amounts
		FROM cse_account acct
	
		INNER JOIN cse_account_check cacw
		ON acct.account_uuid = cacw.account_uuid AND cacw.deleted = 'N'
	
		INNER JOIN cse_check withdraws
		ON cacw.check_uuid = withdraws.check_uuid AND withdraws.ledger = 'OUT'
	
		INNER JOIN cse_case_check ccw
		ON withdraws.check_uuid = ccw.check_uuid AND ccw.deleted = 'N'
	
		INNER JOIN cse_case ccasew
		ON ccw.case_uuid = ccasew.case_uuid
	
		WHERE acct.customer_id = :customer_id
		AND acct.deleted = 'N'
		AND acct.account_type = 'operating'
		AND withdraws.deleted = 'N'
	
		GROUP BY ccasew.case_id
	) operating_outs
	ON ccase.case_id = operating_outs.case_id
	
	LEFT OUTER JOIN cse_case_account cca_opr
	ON ccase.case_uuid = cca_opr.case_uuid AND cca_opr.attribute = 'operating' AND cca_opr.deleted = 'N'
	
	LEFT OUTER JOIN cse_account opr
	ON cca_opr.account_uuid = opr.account_uuid
	
	LEFT OUTER JOIN cse_case_account cca_trst
	ON ccase.case_uuid = cca_trst.case_uuid AND cca_trst.attribute = 'trust' AND cca_trst.deleted = 'N'
	
	LEFT OUTER JOIN cse_account trst
	ON cca_trst.account_uuid = trst.account_uuid
	
	WHERE 1
	AND ccase.customer_id = :customer_id
	ORDER BY  ccase.case_name ASC
		";
	//die($sql);
	$customer_id = $_SESSION["user_customer_id"];
	try {
		$db = getConnection();	
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$billables = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		if (!$blnReturnCount) {
			echo json_encode($billables);
		} else {
			echo json_encode(array("count"=>count($billables)));
		}
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKasesReport() {
	session_write_close();
	if ($_SESSION['user_customer_id']=="") {
		return false;
	}
	if (!is_numeric($_SESSION['user_customer_id'])) {
		return false;
	}
	$script_filename = $_SERVER['SCRIPT_FILENAME'];
	$arrScript = explode("/", $_SERVER['SCRIPT_FILENAME']);
	$host = $arrScript[count($arrScript)-1]; 
	
    $sql = "SELECT DISTINCT 
			inj.injury_id id, ccase.case_id, ccase.lien_filed, ccase.special_instructions,ccase.case_description, inj.injury_number, ccase.case_uuid uuid, IF(ccase.case_number='UNKNOWN' AND ccase.file_number!='', '', ccase.case_number) case_number, ccase.file_number, ccase.cpointer,ccase.source, inj.injury_number, inj.adj_number, ccase.rating, 
			IF (DATE_FORMAT(ccase.case_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.case_date, '%m/%d/%Y')) `case_date`, 
			IF (DATE_FORMAT(ccase.terminated_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.terminated_date, '%m/%d/%Y')) `terminated_date`,
YEAR(case_date) case_year, MONTH(case_date) case_month,
			ccase.case_type, ccase.medical, ccase.td, ccase.rehab,  ccase.edd, ccase.claims,
			venue.venue_uuid, IF(venue.venue IS NULL, '', venue.venue) venue, venue.address1 venue_street, venue.address2 venue_suite, venue.city venue_city, venue.zip venue_zip, venue_abbr, ccase.case_status, ccase.case_substatus, ccase.case_subsubstatus, ccase.submittedOn, ccase.supervising_attorney,
    ccase.attorney, 
			IFNULL(superatt.nickname, '') as supervising_attorney_name, IFNULL(superatt.user_name, '') as supervising_attorney_full_name,
			ccase.worker, ccase.interpreter_needed, ccase.file_location, ccase.case_language `case_language`, 
			app.person_id applicant_id, app.person_uuid applicant_uuid, IFNULL(app.salutation, '') applicant_salutation,
			IF (app.first_name IS NULL, '', TRIM(app.first_name)) first_name , IF(app.last_name IS NULL, '', app.last_name) last_name, IFNULL(app.full_name, '') `full_name`, IFNULL(app.language, '') language,
			IF (DATE_FORMAT(app.dob, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(app.dob, '%m/%d/%Y')) dob, app.dob app_dob, 
			IF (app.ssn = 'XXXXXXXXX', '', app.ssn) ssn, IFNULL(app.email, '') applicant_email, IFNULL(app.phone, '') applicant_phone,IFNULL(app.full_address, '') applicant_full_address,
			IFNULL(employer.`corporation_id`,-1) employer_id, employer.`corporation_uuid` employer_uuid, IFNULL(employer.`company_name`, '') employer, employer.`full_address` employer_full_address, employer.`suite` `employer_suite`,
			IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')) start_date, IF (DATE_FORMAT(inj.statute_limitation, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.statute_limitation, '%m/%d/%Y')) statute_limitation, 
			IF (DATE_FORMAT(inj.end_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.end_date, '%m/%d/%Y')) end_date, inj.ct_dates_note,
			IF (inj.occupation IS NULL, '' , inj.occupation) occupation,
			CONCAT(app.first_name,' ',app.last_name,' vs ', IFNULL(employer.`company_name`, ''),' - ', 
            REPLACE(IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')), '00/00/0000', '')) `name`, ccase.case_name, 
			att.user_id attorney_id, user.user_id, 
			IFNULL(att.nickname, '') as attorney_name, IFNULL(att.user_name, '') as attorney_full_name, 
			IFNULL(user.nickname, '') as worker_name, IFNULL(user.user_name, '') as worker_full_name,
			IFNULL(lien.lien_id, -1) lien_id, 
			IFNULL(settlement.settlement_id, IFNULL(settlementsheet.settlementsheet_id, -1)) settlement_id,
			IF (cif.fee_uuid IS NOT NULL, '1', '-1') fee_id
			, ccase.injury_type, ccase.sub_in FROM cse_case ccase
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N'
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
			INNER JOIN `cse_case_injury` cinj
			ON ccase.case_uuid = cinj.case_uuid AND cinj.deleted = 'N' AND cinj.`attribute` = 'main'
			INNER JOIN `cse_injury` inj
			ON cinj.injury_uuid = inj.injury_uuid AND inj.deleted = 'N'
			LEFT OUTER JOIN `cse_injury_lien` cil
			ON inj.injury_uuid = cil.injury_uuid
			LEFT OUTER JOIN `cse_injury_fee` cif ON inj.injury_uuid = cif.injury_uuid AND cif.deleted = 'N'
			LEFT OUTER JOIN `cse_lien` lien
			ON cil.lien_uuid = lien.lien_uuid
			LEFT OUTER JOIN `cse_injury_settlement` cis
			ON inj.injury_uuid = cis.injury_uuid AND cis.deleted = 'N' AND cis.`attribute` = 'main'
			LEFT OUTER JOIN `cse_settlement` settlement
			ON cis.settlement_uuid = settlement.settlement_uuid
			LEFT OUTER JOIN `cse_settlementsheet` settlementsheet
			ON cis.settlement_uuid = settlementsheet.settlementsheet_uuid
			LEFT OUTER JOIN ikase.`cse_user` superatt
			ON ccase.supervising_attorney = superatt.user_id
			LEFT OUTER JOIN ikase.`cse_user` att
			ON ccase.attorney = att.user_id
			LEFT OUTER JOIN ikase.`cse_user` user
			ON ccase.worker = user.user_id
			WHERE ccase.deleted ='N' 
			AND ccase.customer_id = " . $_SESSION['user_customer_id'];
	$sql .= " ORDER BY ccase.case_date ASC";
	
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$kases = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		echo json_encode($kases);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKasesReportByMonth() {
	session_write_close();
	if ($_SESSION['user_customer_id']=="") {
		return false;
	}
	if (!is_numeric($_SESSION['user_customer_id'])) {
		return false;
	}
	$script_filename = $_SERVER['SCRIPT_FILENAME'];
	$arrScript = explode("/", $_SERVER['SCRIPT_FILENAME']);
	$host = $arrScript[count($arrScript)-1]; 
	
    $sql = "SELECT YEAR(case_date) case_year, MONTH(case_date) case_month,
			MONTHNAME(case_date) case_month_name,
			COUNT(inj.injury_id) injury_count,
			COUNT(DISTINCT ccase.case_id) case_count
			FROM cse_case ccase
			LEFT OUTER JOIN `cse_case_injury` cinj
			ON ccase.case_uuid = cinj.case_uuid
			LEFT OUTER JOIN `cse_injury` inj
			ON cinj.injury_uuid = inj.injury_uuid AND inj.deleted = 'N'
			WHERE ccase.deleted ='N' 
			AND ccase.case_status != 'Intake'
			AND ccase.customer_id = " . $_SESSION['user_customer_id'];
	$sql .= " GROUP BY YEAR(case_date), MONTH(case_date)
	HAVING case_year > 2000";
	$sql .= " ORDER BY YEAR(case_date), MONTH(case_date) ASC";
	
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$kases = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		echo json_encode($kases);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getClientsReportByMonth() {
	
	session_write_close();
	if ($_SESSION['user_customer_id']=="") {
		return false;
	}
	if (!is_numeric($_SESSION['user_customer_id'])) {
		return false;
	}
	$script_filename = $_SERVER['SCRIPT_FILENAME'];
	$arrScript = explode("/", $_SERVER['SCRIPT_FILENAME']);
	$host = $arrScript[count($arrScript)-1]; 
	
	$attribute = "client";
    $sql = "SELECT `parent_referring`.`corporation_id` referring_id, 
			TRIM(`referring`.`company_name`) referring, LOWER(ccase.case_status) case_status,
			YEAR(case_date) case_year, MONTH(case_date) case_month,
			MONTHNAME(case_date) case_month_name,
			COUNT(inj.injury_id) injury_count,
			COUNT(DISTINCT ccase.case_id) case_count
			FROM cse_case ccase
			
			INNER JOIN `cse_case_corporation` rcorp
			ON (ccase.case_uuid = rcorp.case_uuid AND rcorp.attribute = '" . $attribute . "' AND rcorp.deleted = 'N')
			INNER JOIN `cse_corporation` referring
			ON rcorp.corporation_uuid = referring.corporation_uuid
			INNER JOIN `cse_corporation` parent_referring
			ON referring.parent_corporation_uuid = parent_referring.corporation_uuid
			
			LEFT OUTER JOIN `cse_case_injury` cinj
			ON ccase.case_uuid = cinj.case_uuid
			LEFT OUTER JOIN `cse_injury` inj
			ON cinj.injury_uuid = inj.injury_uuid AND inj.deleted = 'N'
			WHERE ccase.deleted ='N' 
			AND ccase.customer_id = " . $_SESSION['user_customer_id'];
	$sql .= " 
	GROUP BY `parent_referring`.`company_name`, LOWER(ccase.case_status), YEAR(case_date), MONTH(case_date) 
            ORDER BY `parent_referring`.`company_name`, 
			YEAR(case_date), MONTH(case_date) ASC, LOWER(ccase.case_status)";
	
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$kases = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		$arrSummary = array();
		foreach($kases as $kase) {
			$referring_id = $kase->referring_id;
			$case_status = $kase->case_status;
			if ($case_status == "") {
				$case_status = "open";
			}
			$arrSummary[$referring_id]["referring"] = $kase->referring;
			$arrSummary[$referring_id][$kase->case_year][$kase->case_month]["case_month_name"] = $kase->case_month_name;
			$arrSummary[$referring_id][$kase->case_year][$kase->case_month]["injury_count_" . $case_status] = $kase->injury_count;
			$arrSummary[$referring_id][$kase->case_year][$kase->case_month]["case_count_" . $case_status] = $kase->case_count;
		}
		$arrOutput = array();
		foreach($arrSummary as $referring_id=>$summary) {
			$referring = "";
			foreach($summary as $summary_index=>$row) {
				//echo $summary_index . "\r\n";
				//continue;
				
				
				if ($summary_index=="referring") {
					$referring = $summary["referring"];
				} else {
					//die(print_r($row));
					//year
					$year = $summary_index;
					$arrMonths = $row;
					foreach($arrMonths as $month_number=>$month) {
						//die(print_r($month));
						$case_month_name = $month["case_month_name"];
						if (isset($month["injury_count_open"])) {
							$injury_count_open = $month["injury_count_open"];
						} else {
							$injury_count_open = 0;
						}
						if (isset($month["case_count_open"])) {
							$case_count_open = $month["case_count_open"];
						} else {
							$case_count_open = 0;
						}
						if (isset($month["injury_count_closed"])) {
							$injury_count_closed = $month["injury_count_closed"];
						} else {
							$injury_count_closed = 0;
						}
						if (isset($month["case_count_closed"])) {
							$case_count_closed = $month["case_count_closed"];
						} else {
							$case_count_closed = 0;
						}
						
						$myObj = (object) [
							"id" => $referring_id,
							"referring_id" => $referring_id,
							"referring" => $referring,
							"case_year" => $year,
							"case_month" => $month_number,
							"case_month_name" => $case_month_name,
							"injury_count_open" => $injury_count_open,
							"case_count_open" => $case_count_open,
							"injury_count_closed" => $injury_count_closed,
							"case_count_closed" => $case_count_closed
						];
						
						$arrOutput[] = $myObj;
					}
				}
			}
		}
		
		//die(print_r($arrOutput));
		echo json_encode($arrOutput);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getReferralsReportByMonth($attribute = "referring") {
	session_write_close();
	if ($_SESSION['user_customer_id']=="") {
		return false;
	}
	if (!is_numeric($_SESSION['user_customer_id'])) {
		return false;
	}
	$script_filename = $_SERVER['SCRIPT_FILENAME'];
	$arrScript = explode("/", $_SERVER['SCRIPT_FILENAME']);
	$host = $arrScript[count($arrScript)-1]; 
	
    $sql = "SELECT DISTINCT CONCAT(`parent_referring`.`corporation_id`, '~', YEAR(case_date), '~', MONTH(case_date)) id, 
			`parent_referring`.`corporation_id` referring_id, 
			TRIM(`referring`.`company_name`) referring, 
			YEAR(case_date) case_year, MONTH(case_date) case_month,
			MONTHNAME(case_date) case_month_name,
			COUNT(inj.injury_id) injury_count,
			COUNT(DISTINCT ccase.case_id) case_count
			FROM cse_case ccase
			
			INNER JOIN `cse_case_corporation` rcorp
			ON (ccase.case_uuid = rcorp.case_uuid AND rcorp.attribute = '" . $attribute . "' AND rcorp.deleted = 'N')
			INNER JOIN `cse_corporation` referring
			ON rcorp.corporation_uuid = referring.corporation_uuid
			INNER JOIN `cse_corporation` parent_referring
			ON referring.parent_corporation_uuid = parent_referring.corporation_uuid
			
			LEFT OUTER JOIN `cse_case_injury` cinj
			ON ccase.case_uuid = cinj.case_uuid
			LEFT OUTER JOIN `cse_injury` inj
			ON cinj.injury_uuid = inj.injury_uuid AND inj.deleted = 'N'
			WHERE ccase.deleted ='N' 
			AND ccase.customer_id = " . $_SESSION['user_customer_id'];
	$sql .= " GROUP BY `parent_referring`.`company_name`, YEAR(case_date), MONTH(case_date)";
	$sql .= " ORDER BY `parent_referring`.`company_name`, YEAR(case_date), MONTH(case_date) ASC";
	
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$kases = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		//die(print_r($kases));
		echo json_encode($kases);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getTokenInputKases() {
	session_write_close();
	if ($_SESSION['user_customer_id']=="") {
		return false;
	}
	if (!is_numeric($_SESSION['user_customer_id'])) {
		return false;
	}
	$search_term = passed_var("q", "get");
	
	if (strlen($search_term) < 3) {
		return false;
	}
	
		$sql = searchKases($search_term, "return_query");
		
		//die($sql);
		
		$replace_me = "CONCAT(app.first_name,' ',app.last_name,' vs ', IFNULL(employer.`company_name`, ''),' - ', 
            REPLACE(IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')), '00/00/0000', '')) `name`";
		$replace_with = "IFNULL(CONCAT(
		IF(ccase.case_number='', ccase.file_number, ccase.case_number)
		, ' - ', app.first_name,' ',app.last_name,' vs ', IFNULL(employer.`company_name`, ''),'<br />DOI:', 
			REPLACE(IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')), '00/00/0000', ''),
			REPLACE(IF (DATE_FORMAT(inj.end_date, '%m/%d/%Y') IS NULL, '', CONCAT(' - ', DATE_FORMAT(inj.end_date, '%m/%d/%Y'))), '00/00/0000', '')
			), ccase.case_name) `name`";
		
		$sql = str_replace($replace_me, $replace_with, $sql);
		//die($sql);
		
		$replace_me = "inj.injury_id id, ccase.case_id";
		$replace_with = "inj.injury_id, ccase.case_id, ccase.case_id id";
		$sql = str_replace($replace_me, $replace_with, $sql);
		
		$replace_me = "OR app.phone";
		$replace_with = "/*OR app.phone";
		$sql = str_replace($replace_me, $replace_with, $sql);
		
		$replace_me = "OR employer.`company_name";
		$replace_with = "*/
		OR employer.`company_name";
		$sql = str_replace($replace_me, $replace_with, $sql);
		
		$replace_me = "OR defense.`company_name`";
		$replace_with = "/*OR defense.`company_name`";
		$sql = str_replace($replace_me, $replace_with, $sql);
		
		$replace_me = "OR ccase.case_number";
		$replace_with = "*/
		OR ccase.case_number";
		$sql = str_replace($replace_me, $replace_with, $sql);
		
		$replace_me = "OR inj.adj_number";
		$replace_with = "/*
		OR inj.adj_number";
		$sql = str_replace($replace_me, $replace_with, $sql);
		
		$replace_me = ") ORDER BY";
		$replace_with = "*/
		) ORDER BY";
		$sql = str_replace($replace_me, $replace_with, $sql);
		
		$replace_me = "' ORDER BY";
		$replace_with = "*/
		) ORDER BY";
		$sql = str_replace($replace_me, $replace_with, $sql);
		
		if ($_SESSION["user_customer_id"]!=1121) {	
			//special case goldberg 08/29/2018 per thomas
			$replace_me = "ccase.case_name)), 
			case_id, injury_number";
			$replace_with = "ccase.case_name)), 
                file_number,
			IF(INSTR(file_number, '*') > 0, 'A', 'B'), injury_number";
			
			$sql = str_replace($replace_me, $replace_with, $sql);
			
			//die($sql);
		}
		
	
	
	$script_filename = $_SERVER['SCRIPT_FILENAME'];
	$arrScript = explode("/", $_SERVER['SCRIPT_FILENAME']);
	$host = $arrScript[count($arrScript)-1]; 
	
	if ($sql=="") {
		$sql = "SELECT DISTINCT 
				inj.injury_id, ccase.case_id id, ccase.lien_filed, ccase.special_instructions,ccase.case_description, inj.injury_number, ccase.case_uuid uuid, IF(ccase.case_number='UNKNOWN' AND ccase.file_number!='', '', ccase.case_number) case_number, ccase.file_number, ccase.cpointer,inj.injury_number, inj.adj_number, ccase.rating, 
				IF (DATE_FORMAT(ccase.case_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.case_date, '%m/%d/%Y')) `case_date`, 
				IF (DATE_FORMAT(ccase.terminated_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.terminated_date, '%m/%d/%Y')) `terminated_date`,
	ccase.case_type, 
				venue.venue_uuid, IF(venue.venue IS NULL, '', venue.venue) venue, venue_abbr, ccase.case_status, ccase.case_substatus, ccase.case_subsubstatus, ccase.submittedOn, ccase.supervising_attorney,
		ccase.attorney, ccase.worker, ccase.interpreter_needed, ccase.file_location, ccase.case_language `case_language`, 
				app.person_id applicant_id, app.person_uuid applicant_uuid, IFNULL(app.salutation, '') applicant_salutation,
				IF (app.first_name IS NULL, '', TRIM(app.first_name)) first_name , IF(app.last_name IS NULL, '', app.last_name) last_name, IFNULL(app.full_name, '') `full_name`, IFNULL(app.language, '') language,
				IF (DATE_FORMAT(app.dob, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(app.dob, '%m/%d/%Y')) dob, 
				IF (app.ssn = 'XXXXXXXXX', '', app.ssn) ssn, IFNULL(app.email, '') applicant_email, IFNULL(app.phone, '') applicant_phone,IFNULL(app.full_address, '') applicant_full_address,
				IFNULL(employer.`corporation_id`,-1) employer_id, employer.`corporation_uuid` employer_uuid, IFNULL(employer.`company_name`, '') employer, 
				IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')) start_date, IF (DATE_FORMAT(inj.statute_limitation, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.statute_limitation, '%m/%d/%Y')) statute_limitation, 
				IF (DATE_FORMAT(inj.end_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.end_date, '%m/%d/%Y')) end_date, inj.ct_dates_note,
				IF (inj.occupation IS NULL, '' , inj.occupation) occupation,
				
				IFNULL(CONCAT(ccase.case_number,
            ' - ',
            app.first_name,
            ' ',
            app.last_name,
            ' vs ',
            IFNULL(employer.`company_name`, ''),
            '
            DOI:',
            REPLACE(IF(DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL,
                    '',
                    DATE_FORMAT(inj.start_date, '%m/%d/%Y')),
                '00/00/0000',
                ''),
            REPLACE(IF(DATE_FORMAT(inj.end_date, '%m/%d/%Y') IS NULL,
                    '',
                    CONCAT(' - ',
                            DATE_FORMAT(inj.end_date, '%m/%d/%Y'))),
                '00/00/0000',
                '')), ccase.case_name) `name`, 
				
				ccase.case_name,
				
				IFNULL(lien.lien_id, -1) lien_id, 
				IFNULL(settlement.settlement_id, IFNULL(settlementsheet.settlementsheet_id, -1)) settlement_id,
				IF (cif.fee_uuid IS NOT NULL, '1', '-1') fee_id
				, ccase.injury_type, ccase.sub_in FROM cse_case ccase ";
	
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
				INNER JOIN (
					SELECT ccase.case_id FROM cse_case ccase
				
					INNER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
					INNER JOIN cse_person app ON ccapp.person_uuid = app.person_uuid
				
					WHERE ccase.deleted ='N' 
					AND ccase.customer_id = " . $_SESSION['user_customer_id'];
					
						$sql .= " AND (";
						$arrSearch[] = " app.first_name LIKE '%" . $search_term . "%' ";
						$arrSearch[] = " app.last_name LIKE '%" . $search_term . "%' ";
						$arrSearch[] = " app.full_name LIKE '%" . $search_term . "%' ";
						$sql .= implode(" OR ", $arrSearch);
						$sql .= ")";
						
				$sql .= "UNION
					
					SELECT ccase.case_id FROM cse_case ccase
	
					LEFT OUTER JOIN `cse_case_corporation` ccorp
					ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
					LEFT OUTER JOIN `cse_corporation` employer
					ON ccorp.corporation_uuid = employer.corporation_uuid
				
					WHERE ccase.deleted = 'N'
					AND ccase.customer_id = " . $_SESSION['user_customer_id'] . "
					AND employer.`company_name` LIKE '%" . $search_term . "%'";
				 
				$sql .= ") filtered_cases
				ON ccase.case_id = filtered_cases.case_id
				LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
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
				INNER JOIN `cse_case_injury` cinj
				ON ccase.case_uuid = cinj.case_uuid AND cinj.deleted = 'N' AND cinj.`attribute` = 'main'
				INNER JOIN `cse_injury` inj
				ON cinj.injury_uuid = inj.injury_uuid AND inj.deleted = 'N'
				LEFT OUTER JOIN `cse_injury_lien` cil
				ON inj.injury_uuid = cil.injury_uuid
				LEFT OUTER JOIN `cse_injury_fee` cif ON inj.injury_uuid = cif.injury_uuid AND cif.deleted = 'N'
				LEFT OUTER JOIN `cse_lien` lien
				ON cil.lien_uuid = lien.lien_uuid
				LEFT OUTER JOIN `cse_injury_settlement` cis
				ON inj.injury_uuid = cis.injury_uuid AND cis.deleted = 'N' AND cis.`attribute` = 'main'
				LEFT OUTER JOIN `cse_settlement` settlement
				ON cis.settlement_uuid = settlement.settlement_uuid
				WHERE 1 ";
			
		if ($_SESSION["user_customer_id"]!=1121) {	
			$sql .= " 
			ORDER BY ccase.case_id, inj.injury_number";
		} else {
			//special case goldberg 08/29/2018 per thomas
			$sql .= " 
			ORDER BY file_number,
			IF(INSTR(file_number, '*') > 0, 'A', 'B'), inj.injury_number";
		}
		
	}
	
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$kases = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		
		$previous_case_id =  "";;
		$previous_adj_number = "";
		$previous_start_date = "";
		$previous_end_date = "";
		
		$insert_key = 0;
		//clean up
		//$json = json_encode($kases);
		
		//if ($_SERVER['REMOTE_ADDR']=='47.153.49.248') {
		//die(print_r($kases));
		$return_kases = array();
		foreach($kases as $kase_index=>$kase) {
			if ($kase->case_name!="") {
				if (strpos($kase->name, $kase->case_name)==false) {
					$arrName = explode(" - ", $kase->name);
					if (count($arrName) > 1) {
						$arrVs = explode("<br>", $arrName[1]);
						$arrVs[0] = $kase->case_name;
						$arrName[1] = implode("<br>", $arrVs);
						
						$kase->name = implode(" - " , $arrName);
					}
				}
			}
			
			if ($previous_case_id == $kase->case_id && $previous_adj_number == $kase->adj_number && $previous_start_date == $kase->start_date && $previous_end_date == $kase->end_date) {
				//die(print_r($kase));
				//that should not happen
				//unset($kases[$kase_index]);
				
			} else {
				$return_kases[] = $kase;
			}
			
			$previous_case_id =  $kase->case_id;
			$previous_adj_number = $kase->adj_number;
			$previous_start_date = $kase->start_date;
			$previous_end_date = $kase->end_date;
		}
		
		//die(print_r($return_kases));
		$json = json_encode($return_kases);
		//}
		
		
		echo $json;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	exit();
}
function lookupKases() {
	session_write_close();
	if ($_SESSION['user_customer_id']=="") {
		return false;
	}
	if (!is_numeric($_SESSION['user_customer_id'])) {
		return false;
	}
	$script_filename = $_SERVER['SCRIPT_FILENAME'];
	$arrScript = explode("/", $_SERVER['SCRIPT_FILENAME']);
	$host = $arrScript[count($arrScript)-1]; 
	
    $sql = "SELECT DISTINCT 
			CONCAT(ccase.case_id,'-',ccase.case_uuid) id, ccase.case_uuid uuid, IF(ccase.case_number='UNKNOWN' AND ccase.file_number!='', '', ccase.case_number) case_number, ccase.file_number, ccase.cpointer,ccase.lien_filed, inj.adj_number, ccase.rating,
			IF (DATE_FORMAT(ccase.case_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.case_date, '%m/%d/%Y')) `case_date`, 
			IF (DATE_FORMAT(ccase.terminated_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.terminated_date, '%m/%d/%Y')) `terminated_date`,
ccase.case_type, 
			venue.venue_uuid, IF(venue.venue IS NULL, '', venue.venue) venue, venue_abbr, ccase.case_status, ccase.case_substatus, ccase.case_subsubstatus, ccase.submittedOn, ccase.supervising_attorney,
    ccase.attorney, ccase.worker, ccase.interpreter_needed, ccase.file_location, ccase.case_language `case_language`, 
			app.person_id applicant_id, app.person_uuid applicant_uuid, IFNULL(app.salutation, '') applicant_salutation,
			IF (app.first_name IS NULL, '', TRIM(app.first_name)) first_name , IF(app.last_name IS NULL, '', app.last_name) last_name, IFNULL(app.full_name, '') `full_name`, IFNULL(app.language, '') language,

			IF (DATE_FORMAT(app.dob, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(app.dob, '%m/%d/%Y')) dob, 
			IF (app.ssn = 'XXXXXXXXX', '', app.ssn) ssn, IFNULL(app.email, '') applicant_email, IFNULL(app.phone, '') applicant_phone,IFNULL(app.full_address, '') applicant_full_address,
			IFNULL(employer.`corporation_id`,-1) employer_id, employer.`corporation_uuid` employer_uuid, IFNULL(employer.`company_name`, '') employer, 
			IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')) start_date, IF (DATE_FORMAT(inj.statute_limitation, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.statute_limitation, '%m/%d/%Y')) statute_limitation, 
			IF (DATE_FORMAT(inj.end_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.end_date, '%m/%d/%Y')) end_date, inj.ct_dates_note,
			IF (inj.occupation IS NULL, '' , inj.occupation) occupation,
			CONCAT(app.first_name,' ', IF(app.middle_name='','',CONCAT(app.middle_name,' ')),app.last_name,' vs ', IFNULL(employer.`company_name`, '')) `name`, ccase.case_name
			, ccase.injury_type, ccase.sub_in FROM cse_case ccase ";

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
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
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
			INNER JOIN `cse_case_injury` cinj
			ON ccase.case_uuid = cinj.case_uuid
			INNER JOIN `cse_injury` inj
			ON cinj.injury_uuid = inj.injury_uuid AND inj.deleted = 'N'
			LEFT OUTER JOIN `cse_injury_lien` cil
			ON inj.injury_uuid = cil.injury_uuid
			LEFT OUTER JOIN `cse_injury_fee` cif ON inj.injury_uuid = cif.injury_uuid AND cif.deleted = 'N'
			LEFT OUTER JOIN `cse_lien` lien
			ON cil.lien_uuid = lien.lien_uuid
			LEFT OUTER JOIN `cse_injury_settlement` cis
			ON inj.injury_uuid = cis.injury_uuid AND cis.deleted = 'N' AND cis.`attribute` = 'main'
			LEFT OUTER JOIN `cse_settlement` settlement
			ON cis.settlement_uuid = settlement.settlement_uuid
			LEFT OUTER JOIN `cse_settlementsheet` settlementsheet
			ON cis.settlement_uuid = settlementsheet.settlementsheet_uuid
			WHERE ccase.deleted ='N' 
			AND cinj.deleted ='N' 
			AND ccase.customer_id = " . $_SESSION['user_customer_id'] . "
			AND inj.customer_id = " . $_SESSION['user_customer_id'] . "
			ORDER BY ccase.case_id";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$kases = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		echo json_encode($kases);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getRecentKases() {
	getRecentKasesNew();
	die();
	//obsolete below
	if ($_SESSION['user_customer_id']=="") {
		return false;
	}
	if (!is_numeric($_SESSION['user_customer_id'])) {
		return false;
	}
	$script_filename = $_SERVER['SCRIPT_FILENAME'];
	$arrScript = explode("/", $_SERVER['SCRIPT_FILENAME']);
	$host = $arrScript[count($arrScript)-1]; 
	
    $sql = "SELECT DISTINCT 
			inj.injury_id id, ccase.case_id, ccase.lien_filed, ccase.special_instructions,ccase.case_description, inj.injury_number, recent.time_stamp, ccase.case_uuid uuid, IF(ccase.case_number='UNKNOWN' AND ccase.file_number!='', '', ccase.case_number) case_number, ccase.file_number, ccase.cpointer,inj.injury_number, inj.adj_number, ccase.rating,
			IF (DATE_FORMAT(ccase.case_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.case_date, '%m/%d/%Y')) `case_date`, 
			IF (DATE_FORMAT(ccase.terminated_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.terminated_date, '%m/%d/%Y')) `terminated_date`,
ccase.case_type, 
			venue.venue_uuid, IFNULL(venue.venue, '') venue, IFNULL(venue_abbr, '') venue_abbr, ccase.case_status, ccase.case_substatus, ccase.case_subsubstatus, ccase.submittedOn, ccase.supervising_attorney,
    ccase.attorney, ccase.worker, ccase.interpreter_needed, ccase.file_location, ccase.case_language `case_language`, 
			app.person_id applicant_id, app.person_uuid applicant_uuid, IFNULL(app.salutation, '') applicant_salutation,
			IF (app.first_name IS NULL, '', TRIM(app.first_name)) first_name , IF(app.last_name IS NULL, '', app.last_name) last_name, IFNULL(app.full_name, '') `full_name`, IFNULL(app.language, '') language,
			IF (DATE_FORMAT(app.dob, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(app.dob, '%m/%d/%Y')) dob, 
			IF (app.ssn = 'XXXXXXXXX', '', app.ssn) ssn, IFNULL(app.email, '') applicant_email, IFNULL(app.phone, '') applicant_phone,IFNULL(app.full_address, '') applicant_full_address,
			IFNULL(employer.`corporation_id`,-1) employer_id, employer.`corporation_uuid` employer_uuid, IFNULL(employer.`company_name`, '') employer, 
			IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')) start_date, IF (DATE_FORMAT(inj.statute_limitation, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.statute_limitation, '%m/%d/%Y')) statute_limitation, 
			IF (DATE_FORMAT(inj.end_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.end_date, '%m/%d/%Y')) end_date, inj.ct_dates_note,
			IF (inj.occupation IS NULL, '' , inj.occupation) occupation
			, ccase.injury_type, ccase.sub_in FROM cse_case ccase ";

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

INNER JOIN (
SELECT cct.case_id, MAX( time_stamp ) time_stamp
FROM  `cse_case_track` cct
INNER JOIN cse_case ccase ON cct.case_id = ccase.case_id
WHERE operation =  'view'
AND user_uuid =  '" . $_SESSION['user_id'] . "'
AND ccase.customer_id = " . $_SESSION['user_customer_id'] . "
AND ccase.deleted =  'N'
GROUP BY cct.case_id
ORDER BY MAX( time_stamp ) DESC 
LIMIT 0 , 15
) recent
ON ccase.case_id = recent.case_id
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
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
			LEFT OUTER JOIN `cse_case_injury` cinj
			ON ccase.case_uuid = cinj.case_uuid
			LEFT OUTER JOIN `cse_injury` inj
			ON cinj.injury_uuid = inj.injury_uuid AND inj.deleted = 'N' AND cinj.deleted ='N' 
			LEFT OUTER JOIN `cse_injury_lien` cil
			ON inj.injury_uuid = cil.injury_uuid
			LEFT OUTER JOIN `cse_injury_fee` cif ON inj.injury_uuid = cif.injury_uuid AND cif.deleted = 'N'
			LEFT OUTER JOIN `cse_lien` lien
			ON cil.lien_uuid = lien.lien_uuid
			LEFT OUTER JOIN `cse_injury_settlement` cis
			ON inj.injury_uuid = cis.injury_uuid AND cis.deleted = 'N' AND cis.`attribute` = 'main'
			 LEFT OUTER JOIN `cse_settlement` settlement
			ON cis.settlement_uuid = settlement.settlement_uuid
			LEFT OUTER JOIN `cse_settlementsheet` settlementsheet
			ON cis.settlement_uuid = settlementsheet.settlementsheet_uuid
			WHERE ccase.deleted ='N' 
			AND ccase.customer_id = " . $_SESSION['user_customer_id'] . "
			#AND app.person_uuid IS NOT NULL
			ORDER BY recent.time_stamp DESC";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$kases = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		echo json_encode($kases);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getUnattendedKases() {
	getUnattendedKasesAll(true);
}
function getUnattendedKasesAll($blnMyCases = false) {
	$thirtyfive_days = mktime(0, 0, 0, date("m"),   date("d") - 35,   date("Y"));
	
	$arrDay = firstAvailableDay( date("Y-m-d", $thirtyfive_days));
	$thirtyfive_days = $arrDay["linux_date"];
	$customer_id = $_SESSION['user_customer_id'];
	$user_id = $_SESSION['user_plain_id'];
	$user_nickname = $_SESSION['user_nickname'];
		
	$sql = " 
	INNER JOIN (
		SELECT ctr.case_id, MAX(ctr.time_stamp)
		FROM cse_case ccase
		INNER JOIN cse_case_track ctr
		ON ccase.case_id = ctr.case_id
		WHERE 1";
		if ($blnMyCases) { 
			$sql .= " AND (ccase.worker = '" . $user_id . "' OR ccase.worker = '" . $user_nickname . "')";
		}
		$sql .= " AND ccase.case_status NOT LIKE '%close%' AND ccase.case_status NOT LIKE 'CL-%' AND ccase.case_status NOT LIKE 'CLOSED%' AND ccase.case_status NOT LIKE 'Sub%' AND ccase.case_status != 'DROPPED' AND ccase.case_status != 'REJECTED' AND ccase.case_status != 'OP-SUBOUT' AND ccase.case_status != 'Sub'
		AND ccase.customer_id = '" . $customer_id . "'
		GROUP BY ctr.case_id
		HAVING MAX(ctr.time_stamp) < '" . $thirtyfive_days . "'
	) unattendeds
	ON ccase.case_id = unattendeds.case_id
	";
	
	$_SESSION["unattended_query"] = $sql;
	
	getKases();
}
function filterKases() {
	$_SESSION["filter_attorney"] = passed_var("val_attorney", "post");
	$_SESSION["filter_worker"] = passed_var("val_worker", "post");
	session_write_close();
	
	$success = array("attorney"=>$_SESSION["filter_attorney"], "worker"=>$_SESSION["filter_worker"]);
    echo json_encode($success);
}
function employeeWorkload($user_id) {
	session_write_close();
	
	$user = getUserInfo($user_id);
	$customer_id = $_SESSION['user_customer_id'];
	
	try {
		$sql = "SELECT ccase.case_status, COUNT(case_id) case_count 
		FROM cse_case ccase
		WHERE 1";		
		$sql .= " 
		AND (
			ccase.worker = '" . $user->id . "'
			OR ccase.supervising_attorney = '" . $user->id . "'
			OR ccase.attorney = '" . $user->id . "'
			
			OR
			
			ccase.worker = '" . $user->nickname . "'
			OR ccase.supervising_attorney = '" . $user->nickname . "'
			OR ccase.attorney = '" . $user->nickname . "'
		)";
		
		$sql .= "
		AND ccase.deleted = 'N'
		AND ccase.customer_id = :customer_id
		
		AND INSTR(ccase.case_status, 'Closed') = 0 AND INSTR(ccase.case_status, 'CL-') = 0
		AND INSTR(ccase.case_status, 'Dropped') = 0
		AND INSTR(ccase.case_status, 'REJECTED') = 0
		
		GROUP BY ccase.case_status";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$case_status = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		$return = "";
		
		$arrRows = array();
		$total_count = 0;
		foreach($case_status as $stat) {
			$row = "
			<tr>
				<td align='left' valign='top' style='font-size:1.25em'><input type='checkbox' class='checkkasestatus' value='" . str_replace(" ", "_", strtoupper($stat->case_status)) . "' /></td>
				<td align='left' valign='top' style='font-size:1.25em'>" . strtoupper($stat->case_status) . "</td>
				<td align='left' valign='top' style='font-size:1.25em; cursor:pointer; text-decoration:underline' class='listkases' id='listkases_" . str_replace(" ", "_", strtoupper($stat->case_status)) . "'>" . $stat->case_count . "</td>
			</tr>";
			$arrRows[] = $row;
			$total_count += $stat->case_count;
		}
		
		$arrReturn = array();
		//die(print_r($arrRows));
		if (count($arrRows) > 0) {
			$row = "
			<tr>
				<th align='left' valign='top' style='font-size:1.25em; border-top:1px solid white' colspan='2'>Total</th><th align='left' valign='top' style='font-size:1.25em; border-top:1px solid white'>" . $total_count . "</th>
			</tr>";
			$arrRows[] = $row;
			
			$arrReturn[] = "
			<div style='display:inline-block; vertical-align:top'>
				<table cellpadding='0' cellspacing='0' style='min-width:200px' class='workload_table'>
					<thead>
					<tr>
						<th align='left' valign='top' nowrap style='font-size:1.25em'>
							<input type='checkbox' id='allkasestatus' value='' />
						</th>
						<th align='left' valign='top' nowrap style='font-size:1.25em'>
							<div style='float:right'>
									<button class='btn btn-xs btn-primary' id='printstatus_selected' disabled>List Selected</button>
									&nbsp;
							</div>
							Status
						</th>
						<th align='left' valign='top' style='font-size:1.25em'>Kases</th>
					</tr>
					</thead>
					" . implode("", $arrRows) . "
				</table>
			</div>";
		}
		
		//case types
		$sql = "SELECT IF(ccase.case_type='Personal Injury', 'NewPI', ccase.case_type) case_type, COUNT(case_id) case_count 
		FROM cse_case ccase
		WHERE 1";		
		$sql .= " 
		AND (
			ccase.worker = '" . $user->id . "'
			OR ccase.supervising_attorney = '" . $user->id . "'
			OR ccase.attorney = '" . $user->id . "'
			
			OR
			
			ccase.worker = '" . $user->nickname . "'
			OR ccase.supervising_attorney = '" . $user->nickname . "'
			OR ccase.attorney = '" . $user->nickname . "'
		)";
		
		$sql .= "
		AND ccase.deleted = 'N'
		AND ccase.customer_id = :customer_id
		
		AND INSTR(ccase.case_status, 'Closed') = 0 AND INSTR(ccase.case_status, 'CL-') = 0
		AND INSTR(ccase.case_status, 'Dropped') = 0
		AND INSTR(ccase.case_status, 'REJECTED') = 0
		
		GROUP BY IF(ccase.case_type='Personal Injury', 'NewPI', ccase.case_type)";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$case_status = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		$arrRows = array();
		$total_count = 0;
		foreach($case_status as $stat) {
			$case_type = str_replace("_", " ", $stat->case_type);
			if ($case_type=="NewPI") {
				$case_type = "PI";
			}
			$row = "
			<tr>
				<td align='left' valign='top' style='font-size:1.25em'><input type='checkbox' class='checkkasetype' value='" . str_replace(" ", "_", strtoupper($stat->case_type)) . "' /></td>
				<td align='left' valign='top' style='font-size:1.25em'>" . strtoupper($case_type) . "</td>
				<td align='left' valign='top' style='font-size:1.25em; cursor:pointer; text-decoration:underline' class='listkasestype' id='listkasestype_" . str_replace(" ", "_", strtoupper($stat->case_type)) . "'>" . $stat->case_count . "</td>
			</tr>";
			$arrRows[] = $row;
			$total_count += $stat->case_count;
		}
		
		//die(print_r($arrRows));
		if (count($arrRows) > 0) {
			$row = "
			<tr>
				<th align='left' valign='top' style='font-size:1.25em; border-top:1px solid white' colspan='2'>Total</th><th align='left' valign='top' style='font-size:1.25em; border-top:1px solid white'>" . $total_count . "</th>
			</tr>";
			$arrRows[] = $row;
			
			$arrReturn[] = "
			<div style='display:inline-block; vertical-align:top; border-left:1px solid white; padding-left:50px;'>
				<table cellpadding='0' cellspacing='0' style='min-width:200px' class='workload_table'>
					<thead>
					<tr>
						<th align='left' valign='top' nowrap style='font-size:1.25em'>
							<input type='checkbox' id='allkasetype' value='' />
						</th>
						<th align='left' valign='top' nowrap style='font-size:1.25em'>
							<div style='float:right'>
									<button class='btn btn-xs btn-primary' id='printtype_selected' disabled>List Selected</button>
									&nbsp;
							</div>
							Type
						</th>
						<th align='left' valign='top' style='font-size:1.25em'>Kases</th>
					</tr>
					</thead>
					" . implode("", $arrRows) . "
				</table>
			</div>";
		}
		
		$return = "
		<div style='font-size:1.6em; margin-bottom:20px; text-align:center' id='workload_title'>" . $_SESSION["user_customer_name"] . "
		</div>
		<div style='font-size:1.6em; margin-bottom:20px'>
			<input type='hidden' id='user_id' value='" . $user->id . "' />
			<div style='float:right; font-size:0.8em'>As of " . date("m/d/Y g:iA") . "</div>
			Employee Workload - " . $user->user_name . "&nbsp;&nbsp;<button class='btn btn-xs btn-primary print_workload' id='print_workload_" . $user_id . "' style='display:none'>Print</button>
			&nbsp;&nbsp;
			<button id='user_" . $user_id . "' class='btn btn-xs kase_user'>List All Kases</button>
		</div>
		" . implode("", $arrReturn);
		
		echo $return;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
       	echo json_encode($error);
	}
	exit();
}
function workerKasesByStatus() {
	$user_id = passed_var("user_id", "post");
	$case_status = passed_var("case_status", "post");
	
	workerKases($user_id, $case_status);
}
function workerKasesByType() {
	$user_id = passed_var("user_id", "post");
	$case_type = passed_var("case_type", "post");
	
	workerKases($user_id, "", $case_type);
}
function workerKases($user_id, $case_status = "", $case_type = "") {
	searchKases($user_id, "worker", false, "", $case_status, $case_type);
}
function searchMine($search_term, $modifier) {
	searchKases($search_term, $modifier, false, "mine");
}
function searchRelatedRolodex($search_term, $modifier) {
	searchKases($search_term, $modifier, false, "rolodex");
}
function searchKases($search_term, $modifier = "", $blnShowSQL = false, $origin = "", $search_case_status = "", $search_case_type = "") {
	$search_term = clean_html($search_term);
	$search_term = str_replace("_", " ", $search_term);
	$search_term = str_replace("~", "*", $search_term);
	$search_term = trim($search_term);
	
	if ($modifier != "closed") {
		if (!is_numeric($search_term)) {
			if (strlen($search_term) < 2 && strpos($modifier, "starts_with") === false) {
				return false;
				//getKases();
			}
		}
	} else {
		//there are much more closed than opened
		if (!is_numeric($search_term)) {
			if (strlen($search_term) < 2) {
				return false;
			}
		}
	}
	if ($_SESSION['user_customer_id']=="") {
		return false;
	}
	if (!is_numeric($_SESSION['user_customer_id'])) {
		return false;
	}
	
	$customer_id = $_SESSION['user_customer_id'];
	
	//related search
	if ($origin=="rolodex") {
		$sql = "SELECT related 
		FROM cse_rolodex_relations
		WHERE rolodex_uuid = :search_term
		AND deleted = 'N'
		AND customer_id = :customer_id";
		
		try {
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("search_term", $search_term);
			$stmt->bindParam("customer_id", $customer_id);
			$stmt->execute();
			$related = $stmt->fetchObject();
			$stmt->closeCursor(); $stmt = null; $db = null;
		} catch(PDOException $e) {
			$error = array("error"=> array("text"=>$e->getMessage()));
				echo json_encode($error);
		}
	}
	//re-initialize the filters
	$_SESSION["filter_attorney"] = "";
	$_SESSION["filter_worker"] = "";
	
	$blnModifiedSearch = false;
	$sql = "SELECT DISTINCT 
			inj.injury_id id, ccase.case_id, ccase.lien_filed, ccase.special_instructions,ccase.case_description, inj.injury_number, ccase.case_uuid uuid, IF(ccase.case_number='UNKNOWN' AND ccase.file_number!='', '', ccase.case_number) case_number, ccase.file_number, ccase.cpointer,ccase.source, inj.adj_number, ccase.rating, 
			IF (DATE_FORMAT(ccase.case_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.case_date, '%m/%d/%Y')) `case_date`, 
			IF (DATE_FORMAT(ccase.terminated_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.terminated_date, '%m/%d/%Y')) `terminated_date`,
ccase.case_type, ccase.medical, ccase.td, ccase.rehab,  ccase.edd, ccase.claims,
			venue.venue_uuid, IF(venue.venue IS NULL, '', venue.venue) venue, venue.address1 venue_street, venue.address2 venue_suite, venue.city venue_city, venue.zip venue_zip, venue_abbr, ccase.case_status, ccase.case_substatus, ccase.case_subsubstatus, ccase.submittedOn, ccase.supervising_attorney,
    ccase.attorney, 
			IFNULL(superatt.nickname, '') as supervising_attorney_name, IFNULL(superatt.user_name, '') as supervising_attorney_full_name,
			ccase.worker, ccase.interpreter_needed, ccase.file_location, ccase.case_language `case_language`, 
			app.person_id applicant_id, app.person_uuid applicant_uuid, IFNULL(app.salutation, '') applicant_salutation,
			IF (app.first_name IS NULL, '', TRIM(app.first_name)) first_name , IF(app.last_name IS NULL, '', app.last_name) last_name, IFNULL(app.full_name, '') `full_name`, IFNULL(app.language, '') language,
			IF (DATE_FORMAT(app.dob, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(app.dob, '%m/%d/%Y')) dob, app.dob app_dob, 
			IF (app.ssn = 'XXXXXXXXX', '', app.ssn) ssn, IFNULL(app.email, '') applicant_email, IFNULL(app.phone, '') applicant_phone,IFNULL(app.full_address, '') applicant_full_address,
			
			IFNULL(employer.`corporation_id`,-1) employer_id, employer.`corporation_uuid` employer_uuid, IFNULL(employer.`company_name`, '') employer, employer.`full_address` employer_full_address, employer.`suite` `employer_suite`,
			
			IFNULL(plaintiff.`company_name`, '') plaintiff,
			
			IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')) start_date, IF (DATE_FORMAT(inj.statute_limitation, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.statute_limitation, '%m/%d/%Y')) statute_limitation, 
			IF (DATE_FORMAT(inj.end_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.end_date, '%m/%d/%Y')) end_date, inj.ct_dates_note,
			IF (inj.occupation IS NULL, '' , inj.occupation) occupation,
			CONCAT(app.first_name,' ',app.last_name,' vs ', IFNULL(employer.`company_name`, ''),' - ', 
            REPLACE(IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')), '00/00/0000', '')) `name`, ccase.case_name, att.user_id attorney_id, user.user_id, 
			IFNULL(att.nickname, '') as attorney_name, IFNULL(att.user_name, '') as attorney_full_name, 
			IFNULL(user.nickname, '') as worker_name, IFNULL(user.user_name, '') as worker_full_name,
			IFNULL(lien.lien_id, -1) lien_id, 
			IFNULL(settlement.settlement_id, IFNULL(settlementsheet.settlementsheet_id, -1)) settlement_id,
			IF (cif.fee_uuid IS NOT NULL, '1', '-1') fee_id, ccase.injury_type, ccase.sub_in,
			IFNULL(jet.jetfile_id, '-1') jetfile_id,
			IFNULL(jet.jetfile_case_id, '-1') jetfile_case_id,
			IFNULL(jet.jetfile_dor_id, '-1') jetfile_dor_id,
			IFNULL(jet.jetfile_dore_id, '-1') jetfile_dore_id,
			IFNULL(jet.jetfile_lien_id, '-1') jetfile_lien_id,
			IFNULL(jet.app_filing_id, '-1') app_filing_id,
			IFNULL(jet.dor_filing_id, '-1') dor_filing_id,
			IFNULL(jet.dore_filing_id, '-1') dore_filing_id,
			IFNULL(jet.lien_filing_id, '-1') lien_filing_id,
			IFNULL(pinj.personal_injury_date, '') personal_injury_date
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
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N'
			LEFT OUTER JOIN cse_personal_injury pinj ON ccase.case_id = pinj.case_id AND pinj.deleted = 'N'
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
			LEFT OUTER JOIN `cse_corporation` `employer`
			ON ccorp.corporation_uuid = employer.corporation_uuid
			
			LEFT OUTER JOIN `cse_case_corporation` ccorp2
			ON (ccase.case_uuid = ccorp2.case_uuid  AND ccorp2.attribute = 'carrier' AND ccorp2.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` `carrier`
			ON ccorp2.corporation_uuid = carrier.corporation_uuid
			
			LEFT OUTER JOIN `cse_case_corporation` ccorp3
			ON (ccase.case_uuid = ccorp3.case_uuid  AND ccorp3.attribute = 'defense' AND ccorp3.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` `defense`
			ON ccorp3.corporation_uuid = defense.corporation_uuid";
			
			if ($modifier!="client") {
				$sql .= "
				LEFT OUTER JOIN `cse_case_corporation` ccorp4
				ON (ccase.case_uuid = ccorp4.case_uuid  AND ccorp4.attribute = 'client' AND ccorp4.deleted = 'N')
				LEFT OUTER JOIN `cse_corporation` `client`
				ON ccorp4.corporation_uuid = client.corporation_uuid
				";
			}
			if ($modifier!="plaintiff") {
				$sql .= "
				LEFT OUTER JOIN `cse_case_corporation` ccorp5
				ON (ccase.case_uuid = ccorp5.case_uuid  AND ccorp5.attribute = 'plaintiff' AND ccorp5.deleted = 'N')
				LEFT OUTER JOIN `cse_corporation` `plaintiff`
				ON ccorp5.corporation_uuid = plaintiff.corporation_uuid
				";
			}
			
			if ($modifier=="doctors" || $modifier=="medical_provider" || $modifier=="employee") {
				$sql .= "
				INNER JOIN `cse_case_corporation` ccorp_medical
				ON (
					ccase.case_uuid = ccorp_medical.case_uuid  
					AND ccorp_medical.attribute = 'medical_provider' 
					AND ccorp_medical.deleted = 'N'
				)
				INNER JOIN `cse_corporation` `medical_provider`
				ON ccorp_medical.corporation_uuid = medical_provider.corporation_uuid
				";
			}
			
	if ($origin=="rolodex") {
		$sql .= "
		INNER JOIN `cse_case_corporation` casecorp
		ON (ccase.case_uuid = casecorp.case_uuid AND casecorp.deleted = 'N')
		INNER JOIN `cse_corporation` `rolodex`
		ON casecorp.corporation_uuid = rolodex.corporation_uuid
		";
	}
	if ($modifier!="return_query" && $modifier!="worker" && $modifier!="employer" && $modifier!="carrier" && $modifier!="employee" && $modifier!="" && $modifier!="closed") {
		$sql .= "
		LEFT OUTER JOIN `cse_case_corporation` `ccorp_" . $modifier . "`
		ON (ccase.case_uuid = `ccorp_" . $modifier . "`.case_uuid  AND `ccorp_" . $modifier . "`.attribute = '" . $modifier . "' AND `ccorp_" . $modifier . "`.deleted = 'N')
		LEFT OUTER JOIN `cse_corporation` `" . $modifier . "`
		ON `ccorp_" . $modifier . "`.corporation_uuid = `" . $modifier . "`.corporation_uuid";
	}
	$sql .= "
			INNER JOIN `cse_case_injury` cinj
			ON ccase.case_uuid = cinj.case_uuid AND cinj.deleted = 'N' AND cinj.`attribute` = 'main'
			INNER JOIN `cse_injury` inj
			ON cinj.injury_uuid = inj.injury_uuid AND inj.deleted = 'N'
			LEFT OUTER JOIN `cse_injury_lien` cil
			ON inj.injury_uuid = cil.injury_uuid
			LEFT OUTER JOIN `cse_injury_fee` cif ON inj.injury_uuid = cif.injury_uuid AND cif.deleted = 'N'
			LEFT OUTER JOIN `cse_lien` lien
			ON cil.lien_uuid = lien.lien_uuid
			LEFT OUTER JOIN `cse_injury_settlement` cis
			ON inj.injury_uuid = cis.injury_uuid AND cis.deleted = 'N' AND cis.`attribute` = 'main'
			 LEFT OUTER JOIN `cse_settlement` settlement
			ON cis.settlement_uuid = settlement.settlement_uuid
			LEFT OUTER JOIN `cse_settlementsheet` settlementsheet
			ON cis.settlement_uuid = settlementsheet.settlementsheet_uuid
			
			LEFT OUTER JOIN `cse_jetfile` jet
			ON inj.injury_uuid = jet.injury_uuid
			
			LEFT OUTER JOIN ikase.`cse_user` superatt
			ON ccase.supervising_attorney = superatt.user_id
			LEFT OUTER JOIN ikase.`cse_user` att
			ON ccase.attorney = att.user_id
			LEFT OUTER JOIN ikase.`cse_user` user
			ON ccase.worker = user.user_id
			WHERE ccase.deleted ='N' 
			AND IF (inj.deleted IS NULL, 'N' , inj.deleted) = 'N'";
	if ($modifier!="") {
		switch($modifier) {
			case "open":
				$sql .= " 
				AND ccase.case_status LIKE '%OP%'";
				break;
			case "closed":
			case "close":
				$sql .= " 
				AND ccase.case_status LIKE '%CLOSE%'";
				break;
			case "sol":
				$sql .= " 
				AND inj.statute_limitation = '" . date("Y-m-d", strtotime($search_term)) . "'";
				break;
			case "worker":
				$sql .= " 
				AND ccase.case_status NOT LIKE '%CLOSE%'
				AND ccase.case_status != 'DROPPED' 
				AND ccase.case_status != 'REJECTED'";
				
				$user = getUserInfo($search_term);
				
				$sql .= " 
				AND (
					ccase.worker = '" . $user->id . "'
					OR ccase.supervising_attorney = '" . $user->id . "'
					OR ccase.attorney = '" . $user->id . "'
					
					OR
					
					ccase.worker = '" . $user->nickname . "'
					OR ccase.supervising_attorney = '" . $user->nickname . "'
					OR ccase.attorney = '" . $user->nickname . "'
				)";
				
				if ($search_case_status!="") {
					$arrCaseStatus = array();
					//first check if it's array
					$blnArray = (strpos($search_case_status, "|")!==false);
					
					if (!$blnArray) {
						$arrCaseStatus[] = $search_case_status;
					} else {
						$arrCaseStatus = explode("|", $search_case_status);
					} 
					$sql .= "AND 
					(";
					$arrCaseStatusSQL = array();
					foreach($arrCaseStatus as $case_status) {
						$search_case_status = trim(strtolower(str_replace("_", " ", $case_status)));
						
						$arrCaseStatusSQL[] = " 
						TRIM(LOWER(ccase.case_status)) = '" . $search_case_status . "'";
					}
					$sql .= implode(" OR ", $arrCaseStatusSQL);
					$sql .= "
					)";
					//die($sql);
				}
				if ($search_case_type!="") {
					//first check if it's array
					$arrCaseType = array();
					$blnArray = (strpos($search_case_type, "|")!==false);
					
					if (!$blnArray) {
						$arrCaseType[] = $search_case_type;
					} else {
						$arrCaseType = explode("|", $search_case_type);
					} 
					$sql .= "AND 
					(";
					$arrCaseTypeSQL = array();
					foreach($arrCaseType as $case_type) {
						$search_case_type = trim(strtolower(str_replace("_", " ", $case_type)));
						
						$arrCaseTypeSQL[] = " 
						TRIM(LOWER(ccase.case_type)) = '" . $search_case_type . "'";
					}
					$sql .= implode(" OR ", $arrCaseTypeSQL);
					$sql .= "
					)";
					//die($sql);
				}
				break;
			case "starts_with_first":
				$sql .= " 
				AND app.first_name LIKE '" . addslashes($search_term) . "%'";
				break;
			case "starts_with_last":
				$sql .= " 
				AND app.last_name LIKE '" . addslashes($search_term) . "%'";
				break;
			case "subout":
				$sql .= " 
				AND ccase.case_status LIKE '%SUB%'";
				$sql .= " 
				AND ccase.case_status NOT LIKE '%CLOSE%'";
				break;
			case "doctors":
				$sql .= " 
				AND (medical_provider.company_name LIKE '" . addslashes($search_term) . "%'";
				$sql .= " 
				OR medical_provider.full_name LIKE '" . addslashes($search_term) . "%'";
				$sql .= " 
				OR medical_provider.last_name LIKE '" . addslashes($search_term) . "%')";
				break;
			default:
				if ($origin=="rolodex") {
					$sql .= " 
					AND (`rolodex`.parent_corporation_uuid = [PARENT_UUID])";
				} else {
					if ($modifier!="return_query" && $modifier!="employee" && $modifier!="worker" && $modifier!="") {
						//look up the company name
						if ($modifier!="applicant") {
							if (is_numeric($search_term)) {
								$corp = getCorporationInfo($search_term);
								if (is_object($corp)) {
									$sql .= " 
									AND (`" . $modifier . "`.company_name = '" . addslashes($corp->company_name) . "')";
									
								} else {
									$sql .= " 
									AND (`" . $modifier . "`.parent_corporation_uuid = '" . $search_term . "')";
								}						
							} 
							$sql .= " 
							AND ccase.case_status NOT LIKE '%CLOSE%'";
						} else {
							$sql .= " 
								AND (`app`.parent_person_uuid = '" . addslashes($search_term) . "')";
						}
						$blnModifiedSearch = true;
					}
				}
				break;
		}
	} else {	
		//per steve, search all even closed 3/31/2017
		if ($_SESSION['user_customer_id']!=1075) {
			$sql .= " 
			AND ccase.case_status NOT LIKE '%CLOSE%'";
		}
	}

	$sql .= " 
	AND ccase.customer_id = " . $_SESSION['user_customer_id'];
	$sql .= " 
	AND (inj.customer_id = " . $_SESSION['user_customer_id'] . " OR inj.customer_id IS NULL) ";
	//search now
	//die($sql);
	//check if posted that this is a parent_corporation_uuid search
	if ($modifier!="employer" && $modifier!="medical_provider" && $modifier!="doctors" && $modifier!="carrier" && $modifier!="defense" && $modifier!="employee" && $modifier!="worker" && $modifier!="client" && $modifier!="sol" && !$blnModifiedSearch) {
		$sql .= " AND (";
		
		$blnValidDate = false;
		if (strlen($search_term)==10) {
			$date = str_replace("-", "/", $search_term);
			$arrDate = explode("/", $date);
			//die(print_r($arrDate));
			$blnValidDate = false;
			if (count($arrDate) > 2) {
				if (strlen($arrDate[2])==4) {
					$date = $arrDate[2] . "-" . $arrDate[0] . "-" . $arrDate[1];
				}
				$blnValidDate = isValidDate($date, "Y-m-d");
			}
		}
		
		if (strlen($search_term)==10 && $blnValidDate) {
			$sql .= "(
				app.`dob` = '" . date("Y-m-d", strtotime(str_replace("-", "/", $search_term))) . "'
				OR
				app.`dob` = '" . $search_term . "'
				OR
				app.`dob` = '" . date("m/d/Y", strtotime(str_replace("-", "/", $search_term))) . "'
			)
			OR inj.`start_date` = '" . date("Y-m-d", strtotime(str_replace("-", "/", $search_term))) . "'
			OR inj.`end_date` = '" . date("Y-m-d", strtotime(str_replace("-", "/", $search_term))) . "')";
		} else {
			$arrFullName = explode(" ", $search_term);
			$first_name = $search_term;
			$last_name = $search_term;
			if (count($arrFullName) > 1) {
				$first_name = $arrFullName[0];
				$last_name = $arrFullName[count($arrFullName) - 1];
				$sql .= "
				(app.first_name LIKE '%" . addslashes($first_name) . "%'
				AND app.last_name LIKE '%" . addslashes($last_name) . "%')";
			} else {
				$sql .= "
				app.first_name LIKE '%" . addslashes($search_term) . "%'
				OR app.last_name LIKE '%" . addslashes($search_term) . "%'";			
			}
			$sql .= "
			OR app.aka LIKE '%" . addslashes($search_term) . "%'
			OR app.full_name LIKE '%" . addslashes($search_term) . "%'
			OR app.phone LIKE '%" . addslashes($search_term) . "%'
			OR app.work_phone LIKE '%" . addslashes($search_term) . "%'
			OR app.cell_phone LIKE '%" . addslashes($search_term) . "%'
			OR app.email LIKE '%" . addslashes($search_term) . "%'
			OR app.work_email LIKE '%" . addslashes($search_term) . "%'
			OR app.full_address LIKE '%" . addslashes($search_term) . "%'
			OR employer.`company_name` LIKE '%" . addslashes($search_term) . "%'
			OR defense.`company_name` LIKE '%" . addslashes($search_term) . "%'
			OR inj.`occupation` LIKE '%" . addslashes($search_term) . "%'";
			if (is_numeric($search_term)) {
				$sql .= " 
				OR ccase.case_id = '" . addslashes($search_term) . "'
				OR app.ssn_last_four LIKE '%" . addslashes($search_term) . "%'
				OR ccase.cpointer LIKE '%" . addslashes($search_term) . "%'";
			}
			if ($modifier!="return_query" && $modifier!="subout" && $modifier!="closed" && $modifier!="employee" && $modifier!="") {
				$sql .= " 
				OR `" . $modifier . "`.parent_corporation_uuid = '" . $search_term . "'";
			}
			$sql .= " 
			OR ccase.case_number LIKE '%" . addslashes($search_term) . "%'
			OR ccase.case_name LIKE '%" . addslashes($search_term) . "%'
			OR ccase.file_number LIKE '%" . addslashes($search_term) . "%'
				OR inj.adj_number LIKE '%" . addslashes($search_term) . "%'
			)";
		}
		
		if (strlen($search_term)==9) {
			$sql .= " 
			OR app.ssn = '" . addslashes($search_term) . "'";
		}
	}
	
	if (isset($_SESSION["search_employee_name"])) {
		$sql .= " 
		 AND `" . $_SESSION["search_partie_type"] . "`.full_name = '" . addslashes($_SESSION["search_employee_name"]) . "'";
		unset($_SESSION["search_employee_name"]);
		unset($_SESSION["search_partie_type"]);
		//die($sql);
	}
	
	//medical search
	if ($modifier=="medical_provider") {
		$sql .= "
		UNION 
		SELECT DISTINCT 
			inj.injury_id id, ccase.case_id, ccase.lien_filed, ccase.special_instructions,ccase.case_description, inj.injury_number, ccase.case_uuid uuid, IF(ccase.case_number='UNKNOWN' AND ccase.file_number!='', '', ccase.case_number) case_number, ccase.file_number, ccase.cpointer,ccase.source, inj.adj_number, ccase.rating, 
			IF (DATE_FORMAT(ccase.case_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.case_date, '%m/%d/%Y')) `case_date`, 
			IF (DATE_FORMAT(ccase.terminated_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.terminated_date, '%m/%d/%Y')) `terminated_date`,
ccase.case_type, ccase.medical, ccase.td, ccase.rehab,  ccase.edd, ccase.claims,
			venue.venue_uuid, IF(venue.venue IS NULL, '', venue.venue) venue, venue.address1 venue_street, venue.address2 venue_suite, venue.city venue_city, venue.zip venue_zip, venue_abbr, ccase.case_status, ccase.case_substatus, ccase.case_subsubstatus, ccase.submittedOn, ccase.supervising_attorney,
    ccase.attorney, 
			IFNULL(superatt.nickname, '') as supervising_attorney_name, IFNULL(superatt.user_name, '') as supervising_attorney_full_name,
			ccase.worker, ccase.interpreter_needed, ccase.file_location, ccase.case_language `case_language`, 
			app.person_id applicant_id, app.person_uuid applicant_uuid, IFNULL(app.salutation, '') applicant_salutation,
			IF (app.first_name IS NULL, '', TRIM(app.first_name)) first_name , IF(app.last_name IS NULL, '', app.last_name) last_name, IFNULL(app.full_name, '') `full_name`, IFNULL(app.language, '') language,
			IF (DATE_FORMAT(app.dob, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(app.dob, '%m/%d/%Y')) dob, app.dob app_dob, 
			IF (app.ssn = 'XXXXXXXXX', '', app.ssn) ssn, IFNULL(app.email, '') applicant_email, IFNULL(app.phone, '') applicant_phone,IFNULL(app.full_address, '') applicant_full_address,
			IFNULL(employer.`corporation_id`,-1) employer_id, employer.`corporation_uuid` employer_uuid, IFNULL(employer.`company_name`, '') employer, employer.`full_address` employer_full_address, employer.`suite` `employer_suite`,
			
			IFNULL(plaintiff.`company_name`, '') plaintiff,
			
			IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')) start_date, IF (DATE_FORMAT(inj.statute_limitation, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.statute_limitation, '%m/%d/%Y')) statute_limitation, 
			IF (DATE_FORMAT(inj.end_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.end_date, '%m/%d/%Y')) end_date, inj.ct_dates_note,
			IF (inj.occupation IS NULL, '' , inj.occupation) occupation,
			CONCAT(app.first_name,' ',app.last_name,' vs ', IFNULL(employer.`company_name`, ''),' - ', 
            REPLACE(IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')), '00/00/0000', '')) `name`, ccase.case_name, att.user_id attorney_id, user.user_id, 
			IFNULL(att.nickname, '') as attorney_name, IFNULL(att.user_name, '') as attorney_full_name, 
			IFNULL(user.nickname, '') as worker_name, IFNULL(user.user_name, '') as worker_full_name,
			IFNULL(lien.lien_id, -1) lien_id, 
			IFNULL(settlement.settlement_id, IFNULL(settlementsheet.settlementsheet_id, -1)) settlement_id,
			IF (cif.fee_uuid IS NOT NULL, '1', '-1') fee_id, ccase.injury_type, ccase.sub_in,
			IFNULL(jet.jetfile_id, '-1') jetfile_id,
			IFNULL(jet.jetfile_case_id, '-1') jetfile_case_id,
			IFNULL(jet.jetfile_dor_id, '-1') jetfile_dor_id,
			IFNULL(jet.jetfile_dore_id, '-1') jetfile_dore_id,
			IFNULL(jet.jetfile_lien_id, '-1') jetfile_lien_id,
			IFNULL(jet.app_filing_id, '-1') app_filing_id,
			IFNULL(jet.dor_filing_id, '-1') dor_filing_id,
			IFNULL(jet.dore_filing_id, '-1') dore_filing_id,
			IFNULL(jet.lien_filing_id, '-1') lien_filing_id,
			IFNULL(pinj.personal_injury_date, '') personal_injury_date
			FROM cse_case ccase  
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N'
			LEFT OUTER JOIN cse_personal_injury pinj ON ccase.case_id = pinj.case_id AND pinj.deleted = 'N'
			LEFT OUTER JOIN cse_person app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			LEFT OUTER JOIN `cse_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` `employer`
			ON ccorp.corporation_uuid = employer.corporation_uuid
			
			LEFT OUTER JOIN `cse_case_corporation` ccorp2
			ON (ccase.case_uuid = ccorp2.case_uuid  AND ccorp2.attribute = 'carrier' AND ccorp2.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` `carrier`
			ON ccorp2.corporation_uuid = carrier.corporation_uuid
			
			LEFT OUTER JOIN `cse_case_corporation` ccorp3
			ON (ccase.case_uuid = ccorp3.case_uuid  AND ccorp3.attribute = 'defense' AND ccorp3.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` `defense`
			ON ccorp3.corporation_uuid = defense.corporation_uuid
			
			LEFT OUTER JOIN `cse_case_corporation` ccorp4
			ON (ccase.case_uuid = ccorp4.case_uuid  AND ccorp4.attribute = 'client' AND ccorp4.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` `client`
			ON ccorp4.corporation_uuid = client.corporation_uuid

			LEFT OUTER JOIN `cse_case_corporation` ccorp5
			ON (ccase.case_uuid = ccorp5.case_uuid  AND ccorp5.attribute = 'plaintiff' AND ccorp5.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` `plaintiff`
			ON ccorp5.corporation_uuid = plaintiff.corporation_uuid
			
						
			INNER JOIN `cse_case_injury` cinj
			ON ccase.case_uuid = cinj.case_uuid AND cinj.deleted = 'N' AND cinj.`attribute` = 'main'
			INNER JOIN `cse_injury` inj
			ON cinj.injury_uuid = inj.injury_uuid AND inj.deleted = 'N'
			LEFT OUTER JOIN `cse_injury_lien` cil
			ON inj.injury_uuid = cil.injury_uuid
			LEFT OUTER JOIN `cse_injury_fee` cif ON inj.injury_uuid = cif.injury_uuid AND cif.deleted = 'N'
			LEFT OUTER JOIN `cse_lien` lien
			ON cil.lien_uuid = lien.lien_uuid
			LEFT OUTER JOIN `cse_injury_settlement` cis
			ON inj.injury_uuid = cis.injury_uuid AND cis.deleted = 'N' AND cis.`attribute` = 'main'
			 LEFT OUTER JOIN `cse_settlement` settlement
			ON cis.settlement_uuid = settlement.settlement_uuid
			LEFT OUTER JOIN `cse_settlementsheet` settlementsheet
			ON cis.settlement_uuid = settlementsheet.settlementsheet_uuid
			
			LEFT OUTER JOIN `cse_jetfile` jet
			ON inj.injury_uuid = jet.injury_uuid
			
			LEFT OUTER JOIN ikase.`cse_user` superatt
			ON ccase.supervising_attorney = superatt.user_id
			LEFT OUTER JOIN ikase.`cse_user` att
			ON ccase.attorney = att.user_id
			LEFT OUTER JOIN ikase.`cse_user` user
			ON ccase.worker = user.user_id
            
            INNER JOIN (
				SELECT DISTINCT ccase.case_uuid
				FROM cse_person_corporation cpc
				INNER JOIN cse_corporation corp
				ON cpc.corporation_uuid = corp.corporation_uuid

				INNER JOIN cse_case_corporation ccc
				ON corp.corporation_uuid = ccc.corporation_uuid

				INNER JOIN cse_case ccase
				ON ccc.case_uuid = ccase.case_uuid
				WHERE corp.parent_corporation_uuid = '" . addslashes($search_term) . "'
            ) prior_cases
            ON ccase.case_uuid = prior_cases.case_uuid
			WHERE ccase.deleted ='N' 
			AND IF (inj.deleted IS NULL, 'N' , inj.deleted) = 'N' 
            AND ccase.customer_id = " . $_SESSION['user_customer_id'] . " AND (inj.customer_id = " . $_SESSION['user_customer_id'] . " OR inj.customer_id IS NULL)  
            #ORDER BY IF (TRIM(first_name) = '', TRIM(full_name), first_name), last_name, case_id, injury_number";
		//die($sql);
	} else {
		//$sql .= " ORDER BY IF (TRIM(app.last_name) = '', TRIM(app.full_name), TRIM(app.last_name)), app.last_name, ccase.case_id, inj.injury_number";
		if ($_SESSION["user_customer_id"]!=1121) {	
			$order_by = " ORDER BY 
				TRIM(IFNULL(
					CONCAT(app.first_name,
					' ',
					app.last_name,
					' vs ',
					IFNULL(employer.`company_name`, ''),
					' - ',
					REPLACE(IF(DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL,
							'',
							DATE_FORMAT(inj.start_date, '%m/%d/%Y')),
						'00/00/0000',
						'')),
					ccase.case_name)), 
				case_id, injury_number
			";
		} else {
			//special case goldberg 08/29/2018 per thomas
			$order_by = " ORDER BY 
			TRIM(IFNULL(app.full_name, ccase.case_name)), case_id,
			IF(INSTR(file_number, '*') > 0, 'A', 'B'), injury_number";
		}
		
		if ($modifier=="worker") {
			$order_by = " ORDER BY 
			TRIM(IFNULL(app.full_name, IF(IFNULL(plaintiff.`company_name`, '') = '', ccase.case_name, plaintiff.company_name))), case_id,
			IF(INSTR(file_number, '*') > 0, 'A', 'B'), injury_number";
		}
		
		$sql .= $order_by;
	}
	if ($modifier=="return_query") {
		//just return the query
		//die($sql);
		return $sql;
	}
	
	//straight up search query, no modifiers
	if ($modifier == "" && $blnShowSQL == false) {
		$sql = "
		SELECT DISTINCT * FROM (
	
SELECT  
			inj.injury_id id, ccase.case_id, ccase.lien_filed, ccase.special_instructions,ccase.case_description, inj.injury_number, ccase.case_uuid uuid, IF(ccase.case_number='UNKNOWN' AND ccase.file_number!='', '', ccase.case_number) case_number, ccase.file_number, ccase.cpointer,ccase.source, inj.adj_number, ccase.rating, 
			IF (DATE_FORMAT(ccase.case_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.case_date, '%m/%d/%Y')) `case_date`, 
			IF (DATE_FORMAT(ccase.terminated_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.terminated_date, '%m/%d/%Y')) `terminated_date`,
ccase.case_type, ccase.medical, ccase.td, ccase.rehab,  ccase.edd, ccase.claims,
			venue.venue_uuid, IF(venue.venue IS NULL, '', venue.venue) venue, venue.address1 venue_street, venue.address2 venue_suite, venue.city venue_city, venue.zip venue_zip, venue_abbr, ccase.case_status, ccase.case_substatus, ccase.case_subsubstatus, ccase.submittedOn, ccase.supervising_attorney,
    ccase.attorney, 
			IFNULL(superatt.nickname, '') as supervising_attorney_name, IFNULL(superatt.user_name, '') as supervising_attorney_full_name,
			ccase.worker, ccase.interpreter_needed, ccase.file_location, ccase.case_language `case_language`, 
			app.person_id applicant_id, app.person_uuid applicant_uuid, IFNULL(app.salutation, '') applicant_salutation,
			IF (app.first_name IS NULL, '', TRIM(app.first_name)) first_name , IF(app.last_name IS NULL, '', app.last_name) last_name, IFNULL(app.full_name, '') `full_name`, IFNULL(app.language, '') language,
			IF (DATE_FORMAT(app.dob, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(app.dob, '%m/%d/%Y')) dob, app.dob app_dob, 
			IF (app.ssn = 'XXXXXXXXX', '', app.ssn) ssn, IFNULL(app.email, '') applicant_email, IFNULL(app.phone, '') applicant_phone,IFNULL(app.full_address, '') applicant_full_address,
			IFNULL(employer.`corporation_id`,-1) employer_id, employer.`corporation_uuid` employer_uuid, IFNULL(employer.`company_name`, '') employer, employer.`full_address` employer_full_address, employer.`suite` `employer_suite`,
			IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')) start_date, IF (DATE_FORMAT(inj.statute_limitation, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.statute_limitation, '%m/%d/%Y')) statute_limitation, 
			IF (DATE_FORMAT(inj.end_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.end_date, '%m/%d/%Y')) end_date, inj.ct_dates_note,
			IF (inj.occupation IS NULL, '' , inj.occupation) occupation,
			CONCAT(app.first_name,' ',app.last_name,' vs ', IFNULL(employer.`company_name`, ''),' - ', 
            REPLACE(IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')), '00/00/0000', '')) `name`, ccase.case_name, att.user_id attorney_id, user.user_id, 
			IFNULL(att.nickname, '') as attorney_name, IFNULL(att.user_name, '') as attorney_full_name, 
			IFNULL(user.nickname, '') as worker_name, IFNULL(user.user_name, '') as worker_full_name,
			IFNULL(lien.lien_id, -1) lien_id, 
			IFNULL(settlement.settlement_id, IFNULL(settlementsheet.settlementsheet_id, -1)) settlement_id,
			IF (cif.fee_uuid IS NOT NULL, '1', '-1') fee_id, ccase.injury_type, ccase.sub_in,
			IFNULL(jet.jetfile_id, '-1') jetfile_id,
			IFNULL(jet.jetfile_case_id, '-1') jetfile_case_id,
			IFNULL(jet.jetfile_dor_id, '-1') jetfile_dor_id,
			IFNULL(jet.jetfile_dore_id, '-1') jetfile_dore_id,
			IFNULL(jet.jetfile_lien_id, '-1') jetfile_lien_id,
			IFNULL(jet.app_filing_id, '-1') app_filing_id,
			IFNULL(jet.dor_filing_id, '-1') dor_filing_id,
			IFNULL(jet.dore_filing_id, '-1') dore_filing_id,
			IFNULL(jet.lien_filing_id, '-1') lien_filing_id,
			IFNULL(pinj.personal_injury_date, '') personal_injury_date
			FROM cse_case ccase  
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N'
			LEFT OUTER JOIN cse_personal_injury pinj ON ccase.case_id = pinj.case_id AND pinj.deleted = 'N'
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
			LEFT OUTER JOIN `cse_corporation` `employer`
			ON ccorp.corporation_uuid = employer.corporation_uuid
			
			LEFT OUTER JOIN `cse_case_corporation` ccorp2
			ON (ccase.case_uuid = ccorp2.case_uuid  AND ccorp2.attribute = 'carrier' AND ccorp2.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` `carrier`
			ON ccorp2.corporation_uuid = carrier.corporation_uuid
			
			LEFT OUTER JOIN `cse_case_corporation` ccorp3
			ON (ccase.case_uuid = ccorp3.case_uuid  AND ccorp3.attribute = 'defense' AND ccorp3.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` `defense`
			ON ccorp3.corporation_uuid = defense.corporation_uuid
			
			LEFT OUTER JOIN `cse_case_corporation` ccorp4
			ON (ccase.case_uuid = ccorp4.case_uuid  AND ccorp4.attribute = 'client' AND ccorp4.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` `client`
			ON ccorp4.corporation_uuid = client.corporation_uuid
			INNER JOIN `cse_case_injury` cinj
			ON ccase.case_uuid = cinj.case_uuid AND cinj.deleted = 'N' AND cinj.`attribute` = 'main'
			INNER JOIN `cse_injury` inj
			ON cinj.injury_uuid = inj.injury_uuid AND inj.deleted = 'N'
			LEFT OUTER JOIN `cse_injury_lien` cil
			ON inj.injury_uuid = cil.injury_uuid
			LEFT OUTER JOIN `cse_injury_fee` cif ON inj.injury_uuid = cif.injury_uuid AND cif.deleted = 'N'
			LEFT OUTER JOIN `cse_lien` lien
			ON cil.lien_uuid = lien.lien_uuid
			LEFT OUTER JOIN `cse_injury_settlement` cis
			ON inj.injury_uuid = cis.injury_uuid AND cis.deleted = 'N' AND cis.`attribute` = 'main'
			 LEFT OUTER JOIN `cse_settlement` settlement
			ON cis.settlement_uuid = settlement.settlement_uuid
			LEFT OUTER JOIN `cse_settlementsheet` settlementsheet
			ON cis.settlement_uuid = settlementsheet.settlementsheet_uuid
			
			LEFT OUTER JOIN `cse_jetfile` jet
			ON inj.injury_uuid = jet.injury_uuid
			
			LEFT OUTER JOIN ikase.`cse_user` superatt
			ON ccase.supervising_attorney = superatt.user_id
			LEFT OUTER JOIN ikase.`cse_user` att
			ON ccase.attorney = att.user_id
			LEFT OUTER JOIN ikase.`cse_user` user
			ON ccase.worker = user.user_id
			WHERE ccase.deleted ='N' 
			AND IF (inj.deleted IS NULL, 'N' , inj.deleted) = 'N' 
			AND ccase.case_status NOT LIKE '%CLOSE%' 
	AND ccase.customer_id = " . $_SESSION['user_customer_id'] . " 
	AND (inj.customer_id = " . $_SESSION['user_customer_id'] . " OR inj.customer_id IS NULL)  AND (
				app.first_name LIKE '%" . addslashes($search_term) . "%'
				OR app.last_name LIKE '%" . addslashes($search_term) . "%'
			OR app.aka LIKE '%" . addslashes($search_term) . "%'
			OR app.full_name LIKE '%" . addslashes($search_term) . "%'
			OR app.phone LIKE '%" . addslashes($search_term) . "%'
			OR app.work_phone LIKE '%" . addslashes($search_term) . "%'
			OR app.cell_phone LIKE '%" . addslashes($search_term) . "%'
			OR app.email LIKE '%" . addslashes($search_term) . "%'
			OR app.work_email LIKE '%" . addslashes($search_term) . "%'
			OR app.full_address LIKE '%" . addslashes($search_term) . "%'
			/*
            OR employer.`company_name` LIKE '%" . addslashes($search_term) . "%'
			OR defense.`company_name` LIKE '%" . addslashes($search_term) . "%'
            */
			OR inj.`occupation` LIKE '%" . addslashes($search_term) . "%' 
			OR ccase.case_number LIKE '%" . addslashes($search_term) . "%'
			OR ccase.case_name LIKE '%" . addslashes($search_term) . "%'
			OR ccase.file_number LIKE '%" . addslashes($search_term) . "%'
				OR inj.adj_number LIKE '%" . addslashes($search_term) . "%'
			) 

UNION
	
SELECT  
			inj.injury_id id, ccase.case_id, ccase.lien_filed, ccase.special_instructions,ccase.case_description, inj.injury_number, ccase.case_uuid uuid, IF(ccase.case_number='UNKNOWN' AND ccase.file_number!='', '', ccase.case_number) case_number, ccase.file_number, ccase.cpointer,ccase.source, inj.adj_number, ccase.rating, 
			IF (DATE_FORMAT(ccase.case_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.case_date, '%m/%d/%Y')) `case_date`, 
			IF (DATE_FORMAT(ccase.terminated_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.terminated_date, '%m/%d/%Y')) `terminated_date`,
ccase.case_type, ccase.medical, ccase.td, ccase.rehab,  ccase.edd, ccase.claims,
			venue.venue_uuid, IF(venue.venue IS NULL, '', venue.venue) venue, venue.address1 venue_street, venue.address2 venue_suite, venue.city venue_city, venue.zip venue_zip, venue_abbr, ccase.case_status, ccase.case_substatus, ccase.case_subsubstatus, ccase.submittedOn, ccase.supervising_attorney,
    ccase.attorney, 
			IFNULL(superatt.nickname, '') as supervising_attorney_name, IFNULL(superatt.user_name, '') as supervising_attorney_full_name,
			ccase.worker, ccase.interpreter_needed, ccase.file_location, ccase.case_language `case_language`, 
			app.person_id applicant_id, app.person_uuid applicant_uuid, IFNULL(app.salutation, '') applicant_salutation,
			IF (app.first_name IS NULL, '', TRIM(app.first_name)) first_name , IF(app.last_name IS NULL, '', app.last_name) last_name, IFNULL(app.full_name, '') `full_name`, IFNULL(app.language, '') language,
			IF (DATE_FORMAT(app.dob, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(app.dob, '%m/%d/%Y')) dob, app.dob app_dob, 
			IF (app.ssn = 'XXXXXXXXX', '', app.ssn) ssn, IFNULL(app.email, '') applicant_email, IFNULL(app.phone, '') applicant_phone,IFNULL(app.full_address, '') applicant_full_address,
			IFNULL(employer.`corporation_id`,-1) employer_id, employer.`corporation_uuid` employer_uuid, IFNULL(employer.`company_name`, '') employer, employer.`full_address` employer_full_address, employer.`suite` `employer_suite`,
			IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')) start_date, IF (DATE_FORMAT(inj.statute_limitation, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.statute_limitation, '%m/%d/%Y')) statute_limitation, 
			IF (DATE_FORMAT(inj.end_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.end_date, '%m/%d/%Y')) end_date, inj.ct_dates_note,
			IF (inj.occupation IS NULL, '' , inj.occupation) occupation,
			CONCAT(app.first_name,' ',app.last_name,' vs ', IFNULL(employer.`company_name`, ''),' - ', 
            REPLACE(IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')), '00/00/0000', '')) `name`, ccase.case_name, att.user_id attorney_id, user.user_id, 
			IFNULL(att.nickname, '') as attorney_name, IFNULL(att.user_name, '') as attorney_full_name, 
			IFNULL(user.nickname, '') as worker_name, IFNULL(user.user_name, '') as worker_full_name,
			IFNULL(lien.lien_id, -1) lien_id, 
			IFNULL(settlement.settlement_id, IFNULL(settlementsheet.settlementsheet_id, -1)) settlement_id,
			IF (cif.fee_uuid IS NOT NULL, '1', '-1') fee_id, ccase.injury_type, ccase.sub_in,
			IFNULL(jet.jetfile_id, '-1') jetfile_id,
			IFNULL(jet.jetfile_case_id, '-1') jetfile_case_id,
			IFNULL(jet.jetfile_dor_id, '-1') jetfile_dor_id,
			IFNULL(jet.jetfile_dore_id, '-1') jetfile_dore_id,
			IFNULL(jet.jetfile_lien_id, '-1') jetfile_lien_id,
			IFNULL(jet.app_filing_id, '-1') app_filing_id,
			IFNULL(jet.dor_filing_id, '-1') dor_filing_id,
			IFNULL(jet.dore_filing_id, '-1') dore_filing_id,
			IFNULL(jet.lien_filing_id, '-1') lien_filing_id,
			IFNULL(pinj.personal_injury_date, '') personal_injury_date
			FROM cse_case ccase  
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N' 
			LEFT OUTER JOIN cse_personal_injury pinj ON ccase.case_id = pinj.case_id AND pinj.deleted = 'N' 
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
			INNER JOIN `cse_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			INNER JOIN `cse_corporation` `employer`
			ON ccorp.corporation_uuid = employer.corporation_uuid
			
			INNER JOIN (
				
				SELECT corporation_id
				
				FROM `cse_corporation` 
				
				WHERE 1 AND INSTR(`company_name`, '" . addslashes($search_term) . "') > 0
  
          			AND `type` = 'employer'
			) emps
            
			ON employer.corporation_id = emps.corporation_id
			
			INNER JOIN `cse_case_injury` cinj
			ON ccase.case_uuid = cinj.case_uuid AND cinj.deleted = 'N' AND cinj.`attribute` = 'main'
			INNER JOIN `cse_injury` inj
			ON cinj.injury_uuid = inj.injury_uuid AND inj.deleted = 'N'
			LEFT OUTER JOIN `cse_injury_lien` cil
			ON inj.injury_uuid = cil.injury_uuid
			LEFT OUTER JOIN `cse_injury_fee` cif ON inj.injury_uuid = cif.injury_uuid AND cif.deleted = 'N'
			LEFT OUTER JOIN `cse_lien` lien
			ON cil.lien_uuid = lien.lien_uuid
			LEFT OUTER JOIN `cse_injury_settlement` cis
			ON inj.injury_uuid = cis.injury_uuid AND cis.deleted = 'N' AND cis.`attribute` = 'main'
			 LEFT OUTER JOIN `cse_settlement` settlement
			ON cis.settlement_uuid = settlement.settlement_uuid
			LEFT OUTER JOIN `cse_settlementsheet` settlementsheet
			ON cis.settlement_uuid = settlementsheet.settlementsheet_uuid
			
			LEFT OUTER JOIN `cse_jetfile` jet
			ON inj.injury_uuid = jet.injury_uuid
			
			LEFT OUTER JOIN ikase.`cse_user` superatt
			ON ccase.supervising_attorney = superatt.user_id
			LEFT OUTER JOIN ikase.`cse_user` att
			ON ccase.attorney = att.user_id
			LEFT OUTER JOIN ikase.`cse_user` user
			ON ccase.worker = user.user_id
			
            WHERE ccase.deleted ='N' 
			AND IF (inj.deleted IS NULL, 'N' , inj.deleted) = 'N' 
			AND ccase.case_status NOT LIKE '%CLOSE%' 
	AND ccase.customer_id = " . $_SESSION['user_customer_id'] . " 

UNION


SELECT  
			inj.injury_id id, ccase.case_id, ccase.lien_filed, ccase.special_instructions,ccase.case_description, inj.injury_number, ccase.case_uuid uuid, IF(ccase.case_number='UNKNOWN' AND ccase.file_number!='', '', ccase.case_number) case_number, ccase.file_number, ccase.cpointer,ccase.source, inj.adj_number, ccase.rating, 
			IF (DATE_FORMAT(ccase.case_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.case_date, '%m/%d/%Y')) `case_date`, 
			IF (DATE_FORMAT(ccase.terminated_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.terminated_date, '%m/%d/%Y')) `terminated_date`,
ccase.case_type, ccase.medical, ccase.td, ccase.rehab,  ccase.edd, ccase.claims,
			venue.venue_uuid, IF(venue.venue IS NULL, '', venue.venue) venue, venue.address1 venue_street, venue.address2 venue_suite, venue.city venue_city, venue.zip venue_zip, venue_abbr, ccase.case_status, ccase.case_substatus, ccase.case_subsubstatus, ccase.submittedOn, ccase.supervising_attorney,
    ccase.attorney, 
			IFNULL(superatt.nickname, '') as supervising_attorney_name, IFNULL(superatt.user_name, '') as supervising_attorney_full_name,
			ccase.worker, ccase.interpreter_needed, ccase.file_location, ccase.case_language `case_language`, 
			app.person_id applicant_id, app.person_uuid applicant_uuid, IFNULL(app.salutation, '') applicant_salutation,
			IF (app.first_name IS NULL, '', TRIM(app.first_name)) first_name , IF(app.last_name IS NULL, '', app.last_name) last_name, IFNULL(app.full_name, '') `full_name`, IFNULL(app.language, '') language,
			IF (DATE_FORMAT(app.dob, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(app.dob, '%m/%d/%Y')) dob, app.dob app_dob, 
			IF (app.ssn = 'XXXXXXXXX', '', app.ssn) ssn, IFNULL(app.email, '') applicant_email, IFNULL(app.phone, '') applicant_phone,IFNULL(app.full_address, '') applicant_full_address,
			IFNULL(employer.`corporation_id`,-1) employer_id, employer.`corporation_uuid` employer_uuid, IFNULL(employer.`company_name`, '') employer, employer.`full_address` employer_full_address, employer.`suite` `employer_suite`,
			IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')) start_date, IF (DATE_FORMAT(inj.statute_limitation, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.statute_limitation, '%m/%d/%Y')) statute_limitation, 
			IF (DATE_FORMAT(inj.end_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.end_date, '%m/%d/%Y')) end_date, inj.ct_dates_note,
			IF (inj.occupation IS NULL, '' , inj.occupation) occupation,
			CONCAT(app.first_name,' ',app.last_name,' vs ', IFNULL(employer.`company_name`, ''),' - ', 
            REPLACE(IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')), '00/00/0000', '')) `name`, ccase.case_name, att.user_id attorney_id, user.user_id, 
			IFNULL(att.nickname, '') as attorney_name, IFNULL(att.user_name, '') as attorney_full_name, 
			IFNULL(user.nickname, '') as worker_name, IFNULL(user.user_name, '') as worker_full_name,
			IFNULL(lien.lien_id, -1) lien_id, 
			IFNULL(settlement.settlement_id, IFNULL(settlementsheet.settlementsheet_id, -1)) settlement_id,
			IF (cif.fee_uuid IS NOT NULL, '1', '-1') fee_id, ccase.injury_type, ccase.sub_in,
			IFNULL(jet.jetfile_id, '-1') jetfile_id,
			IFNULL(jet.jetfile_case_id, '-1') jetfile_case_id,
			IFNULL(jet.jetfile_dor_id, '-1') jetfile_dor_id,
			IFNULL(jet.jetfile_dore_id, '-1') jetfile_dore_id,
			IFNULL(jet.jetfile_lien_id, '-1') jetfile_lien_id,
			IFNULL(jet.app_filing_id, '-1') app_filing_id,
			IFNULL(jet.dor_filing_id, '-1') dor_filing_id,
			IFNULL(jet.dore_filing_id, '-1') dore_filing_id,
			IFNULL(jet.lien_filing_id, '-1') lien_filing_id,
			IFNULL(pinj.personal_injury_date, '') personal_injury_date
			FROM cse_case ccase  
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N'
			LEFT OUTER JOIN cse_personal_injury pinj ON ccase.case_id = pinj.case_id AND pinj.deleted = 'N' 
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
			LEFT OUTER JOIN `cse_corporation` `employer`
			ON ccorp.corporation_uuid = employer.corporation_uuid
            
            INNER JOIN `cse_case_corporation` ccorp3
			ON (ccase.case_uuid = ccorp3.case_uuid  AND ccorp3.attribute = 'defense' AND ccorp3.deleted = 'N')
			INNER JOIN `cse_corporation` `defense`
			ON ccorp3.corporation_uuid = defense.corporation_uuid
			
			
			INNER JOIN (
				
				SELECT corporation_id
				
				FROM `cse_corporation` 
				
				WHERE 1 AND INSTR(`company_name`, '" . addslashes($search_term) . "') > 0
  
          			AND `type` = 'defense'
			) defs
            
			ON defense.corporation_id = defs.corporation_id
			
			INNER JOIN `cse_case_injury` cinj
			ON ccase.case_uuid = cinj.case_uuid AND cinj.deleted = 'N' AND cinj.`attribute` = 'main'
			INNER JOIN `cse_injury` inj
			ON cinj.injury_uuid = inj.injury_uuid AND inj.deleted = 'N'
			LEFT OUTER JOIN `cse_injury_lien` cil
			ON inj.injury_uuid = cil.injury_uuid
			LEFT OUTER JOIN `cse_injury_fee` cif ON inj.injury_uuid = cif.injury_uuid AND cif.deleted = 'N'
			LEFT OUTER JOIN `cse_lien` lien
			ON cil.lien_uuid = lien.lien_uuid
			LEFT OUTER JOIN `cse_injury_settlement` cis
			ON inj.injury_uuid = cis.injury_uuid AND cis.deleted = 'N' AND cis.`attribute` = 'main'
			 LEFT OUTER JOIN `cse_settlement` settlement
			ON cis.settlement_uuid = settlement.settlement_uuid
			LEFT OUTER JOIN `cse_settlementsheet` settlementsheet
			ON cis.settlement_uuid = settlementsheet.settlementsheet_uuid
			
			LEFT OUTER JOIN `cse_jetfile` jet
			ON inj.injury_uuid = jet.injury_uuid
			
			LEFT OUTER JOIN ikase.`cse_user` superatt
			ON ccase.supervising_attorney = superatt.user_id
			LEFT OUTER JOIN ikase.`cse_user` att
			ON ccase.attorney = att.user_id
			LEFT OUTER JOIN ikase.`cse_user` user
			ON ccase.worker = user.user_id
			
            WHERE ccase.deleted ='N' 
			AND IF (inj.deleted IS NULL, 'N' , inj.deleted) = 'N' 
			AND ccase.case_status NOT LIKE '%CLOSE%' 
	AND ccase.customer_id = " . $_SESSION['user_customer_id'] . " 
		
    ORDER BY 
				TRIM(IFNULL(
					CONCAT(first_name,
					' ',
					last_name,
					' vs ',
					IFNULL(employer, ''),
					' - ',
					REPLACE(IF(DATE_FORMAT(start_date, '%m/%d/%Y') IS NULL,
							'',
							DATE_FORMAT(start_date, '%m/%d/%Y')),
						'00/00/0000',
						'')),
					case_name)), 
				case_id, injury_number
) results
			                
			";
		//die($sql);
	}	
	
	if ($origin=="rolodex") {
		$arrOtherPerson = array();
		if (is_object($related)) {
			//remove order by
			$arrSQL = explode("ORDER BY", $sql);
			$sql = $arrSQL[0];
			//die($sql);
			$arrOthers = json_decode($related->related);
			//die(print_r($arrOthers));
			$arrUnion = array();
			foreach($arrOthers as $other) {
				$replace_with = "'" . $other->rolodex_uuid . "'";
				
				$union_sql = str_replace("[PARENT_UUID]", $replace_with, $sql);
				$arrUnion[] = $union_sql;
			}
			$glue = "
			UNION
			";
			//die(print_r($arrUnion));
			$sql = implode($glue, $arrUnion);
			
			if ($_SESSION["user_customer_id"]!=1121) {	
				$order_by = " ORDER BY 
					TRIM(IFNULL(
						CONCAT(first_name,
						' ',
						last_name,
						' vs ',
						IFNULL(employer, ''),
						' - ',
						REPLACE(IF(DATE_FORMAT(start_date, '%m/%d/%Y') IS NULL,
								'',
								DATE_FORMAT(start_date, '%m/%d/%Y')),
							'00/00/0000',
							'')),
						case_name)), 
					case_id, injury_number
				";
			} else {
				//special case goldberg 08/29/2018 per thomas
				$order_by = " ORDER BY 
				TRIM(IFNULL(full_name, case_name)), case_id,
				IF(INSTR(file_number, '*') > 0, 'A', 'B'), injury_number";
			}
			
			$sql .= $order_by;
			
		} else {
			//there are no related rolodex entries
			$sql = str_replace("[PARENT_UUID]", "'" . $search_term . "'", $sql);
		}
	
	}
	//
	//record the query, might need it for printing
	if ($blnShowSQL) {
		return $sql;
	}
	$_SESSION["current_kase_query"] = $sql;
	$_SESSION["current_kase_search_term"] = $search_term;
	session_write_close();
	
	writeQuery($sql);
	//die("nn");
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$kases = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		
			
		//however, is it a case number search?	
		if ($modifier!="worker") {
			$insert_key = 0;	
			$previous_case_id =  "";;
			$previous_adj_number = "";
			$previous_start_date = "";
			$previous_end_date = "";
			
			foreach($kases as $kase_index=>$kase) {
				$kase->exact_match = 0;
				if ($search_term!="") {
					if ($search_term==$kase->case_number || $search_term==$kase->file_number || strtolower($search_term)==strtolower($kase->first_name) || strtolower($search_term)==strtolower($kase->last_name) || strtolower($search_term)==strtolower($kase->employer)) {
						unset($kases[$kase_index]);
						$kase->exact_match = 1;
						//return on top
						//array_unshift($kases,$kase);
						$inserted = array( $kase ); // Not necessarily an array
						array_splice( $kases, $insert_key, 0, $inserted ); 
						$insert_key++;
					}
				}
				
				if ($previous_case_id == $kase->case_id && $previous_adj_number == $kase->adj_number && $previous_start_date == $kase->start_date && $previous_end_date == $kase->end_date) {
					//that should not happen
					unset($kases[$kase_index]);
				}
			}
		}
		

		echo json_encode($kases);
		/*
		if (count($kases) > 0) {
			echo json_encode($kases);
		} else {
			echo json_encode(array("success"=>false));
		}
		*/
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function runLastQueryMonths() {
	runLastQuery(true);
}
function runLastQuery($blnByMonth = false) {
	session_write_close();
	
	$sql = lastKaseQuery();
	$search_term = "";
	
	if ($sql=="") {
		if (!isset($_SESSION["current_kase_query"])) {
			return false;
		}
		$search_term = '';
		if (isset($_SESSION["current_kase_search_term"])) {
			$search_term = $_SESSION["current_kase_search_term"];
		}
		$sql = strtolower($_SESSION["current_kase_query"]);
		
		if (strpos($sql, " distinct") === false) {
			$sql = str_replace("select ", "select distinct '" . $search_term . "' search_term, ", $sql);
		} else {
			$sql = str_replace("distinct", "distinct '" . $search_term . "' search_term,", $sql);
		}
	}
	if ($blnByMonth) {
		$sql = str_replace("order by", "ORDER BY YEAR(inj.`statute_limitation`), MONTH(inj.`statute_limitation`),", $sql);
	}
	/*
	if ($_SERVER['REMOTE_ADDR']=='47.153.49.248') {
		die($sql);
	}
	*/
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt = $db->query($sql);
		$kases = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		echo json_encode($kases);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKase($id, $blnRetry = false) {
	session_write_close();
	if ($_SESSION['user_customer_id']=="") {
		return false;
	}
	if (!is_numeric($_SESSION['user_customer_id'])) {
		return false;
	}
	if ($id < 0) {
		$error = array("error"=> array("text"=>"Nah"));
		echo json_encode($error);
		//newKase();
		return;
	}
	$sql = "SELECT inj.injury_id id, ccase.case_id, ccase.lien_filed, ccase.special_instructions,ccase.case_description, inj.injury_number, ccase.case_uuid uuid, IF(ccase.case_number='UNKNOWN' AND ccase.file_number!='', '', ccase.case_number) case_number, ccase.file_number, ccase.filing_date, ccase.cpointer,ccase.source, inj.injury_number, inj.adj_number, ccase.rating, ccase.injury_type, ccase.sub_in, 
			IF (DATE_FORMAT(ccase.case_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.case_date, '%m/%d/%Y')) `case_date`, 
			IF (DATE_FORMAT(ccase.terminated_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.terminated_date, '%m/%d/%Y')) `terminated_date`,
ccase.case_type, ccase.medical, ccase.td, ccase.rehab,  ccase.edd, ccase.claims,
			venue.venue_uuid, IF(venue.venue IS NULL, '', venue.venue) venue, venue.address1 venue_street, venue.address2 venue_suite, venue.city venue_city, venue.zip venue_zip, venue_abbr, ccase.case_status, ccase.case_substatus, ccase.case_subsubstatus, ccase.submittedOn, ccase.supervising_attorney,
    ccase.attorney, 
			IFNULL(superatt.nickname, '') as supervising_attorney_name, IFNULL(superatt.user_name, '') as supervising_attorney_full_name,
			ccase.worker, ccase.interpreter_needed, ccase.file_location, ccase.case_language `case_language`, 
			IFNULL(app.person_id, -1) applicant_id, app.person_uuid applicant_uuid, IFNULL(app.salutation, '') applicant_salutation,
			IF (app.first_name IS NULL, '', TRIM(app.first_name)) first_name , IF(app.last_name IS NULL, '', app.last_name) last_name, IFNULL(app.full_name, '') `full_name`, IFNULL(app.language, '') language,
			IF (DATE_FORMAT(app.dob, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(app.dob, '%m/%d/%Y')) dob, app.dob app_dob, 
			IFNULL(IF (app.ssn = 'XXXXXXXXX', '', app.ssn), '') ssn, IFNULL(app.email, '') applicant_email, IFNULL(app.phone, '') applicant_phone,IFNULL(app.full_address, '') applicant_full_address,
			IFNULL(employer.`corporation_id`,-1) employer_id, employer.`corporation_uuid` employer_uuid, IFNULL(employer.`company_name`, '') employer, employer.`full_address` employer_full_address, employer.`suite` `employer_suite`,
			
			IFNULL(defendant.`corporation_id`,-1) defendant_id, defendant.company_name defendant, defendant.full_name defendant_full_name, defendant.street defendant_street, defendant.city defendant_city,
			defendant.state defendant_state, defendant.zip defendant_zip,
			
			IFNULL(plaintiff.`corporation_id`,-1) plaintiff_id, plaintiff.`corporation_uuid` plaintiff_uuid, plaintiff.`company_name` plaintiff, plaintiff.`full_address` plaintiff_full_address,
			
			IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')) start_date, IF (DATE_FORMAT(inj.statute_limitation, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.statute_limitation, '%m/%d/%Y')) statute_limitation, 
			IF (DATE_FORMAT(inj.end_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.end_date, '%m/%d/%Y')) end_date, inj.ct_dates_note,
			IF (inj.occupation IS NULL, '' , inj.occupation) occupation,
			CONCAT(app.first_name,' ',app.last_name,' vs ', IFNULL(employer.`company_name`, ''),' - ', 
            REPLACE(IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')), '00/00/0000', '')) `name`, ccase.case_name, 
			att.user_id attorney_id, user.user_id, 
			IFNULL(att.nickname, '') as attorney_name, IFNULL(att.user_name, '') as attorney_full_name, 
			IFNULL(user.nickname, '') as worker_name, IFNULL(user.user_name, '') as worker_full_name,
			IFNULL(lien.lien_id, -1) lien_id, 
			IFNULL(settlement.settlement_id, IFNULL(settlementsheet.settlementsheet_id, -1)) settlement_id,
			
			IF (cif.fee_uuid IS NOT NULL, '1', '-1') fee_id, ccase.injury_type, ccase.sub_in,
			IFNULL(pi.personal_injury_date, '') personal_injury_date,
			IFNULL(pi.loss_date, '') personal_injury_loss_date,
			IFNULL(pi.statute_limitation, '') personal_statute_limitation
			FROM cse_case ccase 
			LEFT OUTER JOIN `cse_personal_injury` pi 
			ON ccase.case_id = pi.case_id AND pi.deleted = 'N'
			";

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
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N'
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
			
			LEFT OUTER JOIN `cse_case_corporation` pcorp
			ON (ccase.case_uuid = pcorp.case_uuid AND pcorp.attribute = 'plaintiff' AND pcorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` plaintiff
			ON pcorp.corporation_uuid = plaintiff.corporation_uuid
			
			LEFT OUTER JOIN `cse_case_injury` cinj
			ON ccase.case_uuid = cinj.case_uuid AND cinj.deleted = 'N' AND cinj.`attribute` = 'main'
			LEFT OUTER JOIN `cse_injury` inj
			ON cinj.injury_uuid = inj.injury_uuid AND inj.deleted = 'N'
			LEFT OUTER JOIN `cse_injury_lien` cil
			ON inj.injury_uuid = cil.injury_uuid
			LEFT OUTER JOIN `cse_injury_fee` cif ON inj.injury_uuid = cif.injury_uuid AND cif.deleted = 'N'
			LEFT OUTER JOIN `cse_lien` lien
			ON cil.lien_uuid = lien.lien_uuid
			
			LEFT OUTER JOIN `cse_injury_settlement` cis
			ON inj.injury_uuid = cis.injury_uuid AND cis.deleted = 'N' AND cis.`attribute` = 'main'
			
			LEFT OUTER JOIN `cse_settlement` settlement
			ON cis.settlement_uuid = settlement.settlement_uuid
			
			LEFT OUTER JOIN `cse_settlementsheet` settlementsheet
			ON cis.settlement_uuid = settlementsheet.settlementsheet_uuid
			
			LEFT OUTER JOIN ikase.`cse_user` superatt
			ON ccase.supervising_attorney = superatt.user_id
			LEFT OUTER JOIN ikase.`cse_user` att
			ON ccase.attorney = att.user_id
			LEFT OUTER JOIN ikase.`cse_user` user
			ON ccase.worker = user.user_id
			WHERE ccase.case_id=:id
			AND ccase.deleted = 'N'
			AND ccase.customer_id = :customer_id";
	//die($sql);
	try {
		$customer_id = $_SESSION['user_customer_id'];
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($id > 0) {
			$stmt->bindParam("id", $id);
		}
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$kase = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		if (!is_object($kase) && !$blnRetry) {
			$kase_info = getKaseInfo($id);
			
			//it might be a cancelled order
			$sql = "SELECT ccase.case_id
			FROM cse_case ccase
			WHERE (
				case_number IN (
					SELECT IF(file_number='', case_number, file_number) file_number FROM ikase_goldberg2.cse_case
					WHERE case_id = :id
					)
				OR
				file_number IN (
					SELECT IF(file_number='', case_number, file_number) file_number FROM ikase_goldberg2.cse_case
					WHERE case_id = :id
					)
				)
			AND ccase.deleted = 'N'
			AND ccase.customer_id = :customer_id";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("id", $id);
			$stmt->bindParam("customer_id", $customer_id);
			$stmt->execute();
			$kase = $stmt->fetchObject();
			$stmt->closeCursor(); $stmt = null; $db = null;
			
			if (is_object($kase)) {
				//retry, only one time
				getKase($kase->case_id, true);
				return;
			}
			return false;
		}
		if ($kase->case_name != "") {
			$kase->name = $kase->case_name;
		}
		
		if ($kase->case_number != "" && $kase->file_number=="") {
			$kase->file_number = $kase->case_number;
			$kase->case_number = "";
		}
        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($kase);
        } else {
            echo $_GET['callback'] . '(' . json_encode($kase) . ');';
        }
		return $kase;

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getMatrixActivitySent ($case_id) {
	session_write_close();
	
	$sql = "SELECT COUNT(ca.activity_id) activity_count, IFNULL(MAX(activity_date), '') activity_date, IFNULL(MAX(activity_id), '') activity_id
	FROM cse_activity ca
	INNER JOIN cse_case_activity cca
	ON ca.activity_uuid = cca.activity_uuid
	INNER JOIN cse_case ccase
	ON cca.case_uuid = ccase.case_uuid
	
	WHERE (ca.activity_category = 'Matrix Referral sent' OR ca.activity_category = 'Matrix Referral exported')
	AND ccase.case_id = :case_id
	AND ccase.customer_id = :customer_id
	AND ca.deleted = 'N'
	ORDER BY ca.activity_id DESC
	LIMIT 0, 1";
	
	$customer_id = $_SESSION["user_customer_id"];
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("customer_id", $customer_id);
		
		$stmt->execute();
		$activity = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		//print_r($kases);
		
        echo json_encode($activity);

		//die($sql);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getMatrix() {
	session_write_close();
	
	$id = passed_var("id", "post");
	$adj_number = passed_var("adj_number", "post");
	$ssn = passed_var("nss", "post");
	if (strtolower(trim($adj_number))=="unassigned") {
		$adj_number = "";
	}	
	$blnSingle = (strpos($id, "|")===false);
	$url = "https://www.matrixdocuments.com/dis/pws/manage/request/check_request.php";
	$arrValues = array("case_id"=>$id, "cus_id"=>$_SESSION["user_customer_id"], "adj_number"=>$adj_number, "nss"=>$ssn);
	//die(print_r($arrValues));
	$result = post_curl($url, $arrValues);
	//die($result);
	$jresult = json_decode($result);
	
	if ($blnSingle && is_object($jresult)) {
		if ($jresult->imported=="Y") {
			$request_id = $jresult->id;
			$request_date = date("Y-m-d H:i:s", strtotime($jresult->time_stamp));
			
			//make sure everything is tracked
			$sql = "INSERT INTO `cse_case_matrixrequest` (`case_id`, `request_id`, `request_by`, `request_date`, `customer_id`)
			
			SELECT :case_id, :request_id, :request_by, :request_date, :customer_id
			FROM dual
			WHERE NOT EXISTS (
				SELECT * 
				FROM `cse_case_matrixrequest` 
				WHERE case_id = :case_id
				AND request_id = :request_id
				AND customer_id = :customer_id
			)";
		
			try {
				$db = getConnection();
				$stmt = $db->prepare($sql);
				$stmt->bindParam("case_id", $id);
				$stmt->bindParam("request_id", $request_id);
				$stmt->bindParam("request_date", $request_date);
				$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
				$stmt->bindParam("request_by", $_SESSION['user_nickname']);
				$stmt->execute();
			} catch(PDOException $e) {
				$error = array("error"=> array("text"=>$e->getMessage()));
					echo json_encode($error);
			}
		}
	} else {
		//$arrIDs = explode("|", $id);
		//foreach($arrIDs as $case_id) {
		//}
		if (is_object($jresult)) {
			foreach($jresult as $res) {
				$request_id = $res->id;
				$request_date = date("Y-m-d H:i:s", strtotime($res->time_stamp));
				$case_id = $res->case_id;
				//make sure everything is tracked
				$sql = "INSERT INTO `cse_case_matrixrequest` (`case_id`, `request_id`, `request_by`, `request_date`, `customer_id`)
				
				SELECT :case_id, :request_id, :request_by, :request_date, :customer_id
				FROM dual
				WHERE NOT EXISTS (
					SELECT * 
					FROM `cse_case_matrixrequest` 
					WHERE case_id = :case_id
					AND request_id = :request_id
					AND customer_id = :customer_id
				)";
			
				try {
					$db = getConnection();
					$stmt = $db->prepare($sql);
					$stmt->bindParam("case_id", $case_id);
					$stmt->bindParam("request_id", $request_id);
					$stmt->bindParam("request_date", $request_date);
					$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
					$stmt->bindParam("request_by", $_SESSION['user_nickname']);
					$stmt->execute();
				} catch(PDOException $e) {
					$error = array("error"=> array("text"=>$e->getMessage()));
						echo json_encode($error);
				}
			}
		}
	}
	die($result);
}
function findMatrixOrderADJ($id, $adj_number) {
	session_write_close();
	
	if (strtolower(trim($adj_number))=="unassigned") {
		findMatrixOrder($id);
		return;
		$adj_number = "";
	}
	$adj_number = str_replace("~", ";", $adj_number);
	$url = "https://www.matrixdocuments.com/dis/pws/manage/request/check_adj.php";
	$arrValues = array("case_id"=>$id, "adj_number"=>$adj_number);
	$result = post_curl($url, $arrValues);
	die($result);
}
function findMatrixOrder($id) {
	session_write_close();
	$kase = getKaseInfo($id);
	//die(print_r($kase));
	$attorney_id = $_SESSION['user_inhouse_id'];
	$applicant = $kase->first_name . " " . $kase->last_name;
	if ($kase->full_name!="") {
		$applicant = $kase->full_name;
	}
	$employer = $kase->employer;
	
	$url = "https://www.matrixdocuments.com/dis/pws/manage/request/check_order.php";
	$arrValues = array("case_id"=>$id, "attorney_id"=>$attorney_id, "applicant"=>$applicant, "employer"=>$employer);
	
	//die(json_encode($arrValues));
	$result = post_curl($url, $arrValues);
	die($result);
}
function searchMatrixOrder($applicant) {
	session_write_close();
	$applicant = str_replace("_", " ", $applicant);
	$url = "https://www.matrixdocuments.com/dis/pws/manage/request/find_order.php";
	$arrValues = array("applicant"=>$applicant);
	
	//die(json_encode($arrValues));
	$result = post_curl($url, $arrValues);
	die($result);
}
function linkMatrixOrder() {
	session_write_close();
	
	$customer_id = $_SESSION["user_customer_id"];
	$case_id = passed_var("case_id", "post");
	$order_id = passed_var("order_id", "post");
	$order_date = passed_var("order_date", "post");
	
	$sql = "INSERT INTO cse_case_matrixorder (case_id, order_id, order_date, order_info, customer_id)";
	$sql .= "
	VALUES (:case_id, :order_id, :order_date, '', :customer_id)";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("order_id", $order_id);
		$stmt->bindParam("order_date", $order_date);
		$stmt->execute();
		$stmt = null; $db = null;
		
		die(json_encode(array("success"=>true)));
		//die("done");
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
		die(print_r($error));
		die();
	}
}
function getLinkedMatrixOrder($case_id) {
	$sql = "SELECT * 
	FROM cse_case_matrixorder
	WHERE case_id = :case_id
	AND customer_id = :customer_id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
		$stmt->bindParam("case_id", $case_id);
		$stmt->execute();
		$order = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		if (is_object($order)) {
			die(json_encode($order));
		} 
		//die("done");
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
		die(print_r($error));
		die();
	}
}
function getLinkedMatrixOrderInfo($id, $case_id) {
	session_write_close();
	//$id = passed_var("order_id", "post");
	
	$url = "https://www.matrixdocuments.com/dis/pws/manage/request/get_order_info.php";
	$arrValues = array("id"=>$id);
	$result = post_curl($url, $arrValues);
	
	try {
		//do we have a matrixorder already?
		$sql = "SELECT * FROM 
		cse_case_matrixorder
		WHERE case_id = :case_id
		AND order_id = :id
		AND customer_id = :customer_id";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$order = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		if (!is_object($order)) {
			$arrResult = json_decode($result);
			
			$order_date = date("Y-m-d", strtotime($arrResult->actual_assigned_date));
			//let's update the order_info
			$sql = "INSERT INTO cse_case_matrixorder (case_id, order_id, order_date, order_info, customer_id)";
			$sql .= "
			VALUES (:case_id, :id, :order_date, :order_info, :customer_id)";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("case_id", $case_id);
			$stmt->bindParam("id", $id);
			$stmt->bindParam("order_date", $order_date);
			$stmt->bindParam("order_info", $result);
			$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
			$stmt->execute();
			$stmt = null; $db = null;
		} else {		
			//let's update the order_info
			$sql = "UPDATE cse_case_matrixorder
			SET order_info = :order_info
			WHERE case_id = :case_id";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("case_id", $case_id);
			$stmt->bindParam("order_info", $result);
			$stmt->execute();
			$stmt = null; $db = null;
		}
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
		die(print_r($error));
		die();
	}
	die($result);
}
function getMatrixLocation() {
	session_write_close();
	$id = passed_var("id", "post");
	$field_id = passed_var("field_id", "post");
	$order_id = passed_var("order_id", "post");
	$facility = passed_var("facility", "post");
	
	$url = "https://www.matrixdocuments.com/dis/pws/manage/request/check_location.php";
	$arrValues = array("case_id"=>$id, "order_id"=>$order_id, "field_id"=>$field_id, "facility"=>$facility);
	$result = post_curl($url, $arrValues);
	die($result);
}
function getRequestLocation() {
	session_write_close();
	$id = passed_var("id", "post");
	$field_id = passed_var("field_id", "post");
	$order_id = passed_var("order_id", "post");
	$facility = passed_var("facility", "post");
	
	$url = "https://www.matrixdocuments.com/dis/pws/manage/request/check_request_location.php";
	$arrValues = array("case_id"=>$id, "order_id"=>$order_id, "field_id"=>$field_id, "facility"=>$facility);
	$result = post_curl($url, $arrValues);
	die($result);
}
function getMatrixLocationByReq() {
	session_write_close();
	$id = passed_var("id", "post");
	$field_id = passed_var("field_id", "post");
	$request_id = passed_var("order_id", "post");
	$facility = passed_var("facility", "post");
	
	$url = "https://www.matrixdocuments.com/dis/pws/manage/request/check_matrix_location.php";
	$arrValues = array("case_id"=>$id, "request_id"=>$request_id, "field_id"=>$field_id, "facility"=>$facility);
	$result = post_curl($url, $arrValues);
	die($result);
}
function addMatrixLocation() {
	session_write_close();
	$case_id = passed_var("case_id", "post");
	$request_id = passed_var("request_id", "post");
	$data = passed_var("data", "post");
	
	$jdata = json_decode($data);
	//associate the corp and the request
	//die(print_r($jdata));
	$corporation_id = $jdata->corporation_id;
	$sql = "INSERT INTO `cse_corporation_matrixrequest` (`corporation_id`, `request_id`, `request_by`, `customer_id`)
	VALUES (:corporation_id, :request_id, :request_by, :customer_id)";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
		$stmt->bindParam("request_by", $_SESSION['user_nickname']);
		$stmt->bindParam("request_id", $request_id);
		$stmt->bindParam("corporation_id", $corporation_id);
		$stmt->execute();
		$stmt = null; $db = null;
		//die("done");
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
		die(print_r($error));
		die();
	}
	$url = "https://www.matrixdocuments.com/dis/pws/manage/request/add_ikase_location.php";
	$arrValues = array("case_id"=>$case_id, "cus_id"=>$_SESSION['user_customer_id'], "request_id"=>$request_id, "data"=>$data);
	$result = post_curl($url, $arrValues);
	die($result);
}
function getEmployeeKases($employee, $partie_type) {
	$_SESSION["search_employee_name"] = $employee;
	$_SESSION["search_partie_type"] = $partie_type;
	searchKases($employee, "employee");
}
function getRelatedKases($id) {
	session_write_close();
	$sql = "SELECT DISTINCT ccase.case_uuid
		FROM cse_case ccase

		INNER JOIN (
			SELECT DISTINCT ccase.case_id, ccase.case_uuid
			FROM  cse_case_injury cci
			INNER JOIN cse_case ccase
			ON cci.case_uuid = ccase.case_uuid
				
			INNER JOIN (
				SELECT injury_uuid 
				FROM cse_case_injury cinj 
				INNER JOIN cse_case ccase
				ON cinj.case_uuid = ccase.case_uuid
				where case_id = :id
				AND ccase.customer_id = " . $_SESSION['user_customer_id'] . "
			) injury_list
			ON cci.injury_uuid = injury_list.injury_uuid
		) related_cases
		ON ccase.case_uuid = related_cases.case_uuid";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		
		$stmt->execute();
		$kases = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		//print_r($kases);
		
        return $kases;

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKaseSSNClaim($case_id) {
	session_write_close();
	
	$customer_id = $_SESSION["user_customer_id"];
	
	$sql = "SELECT clm.claim_id, clm.claim_info, clm.claim_id id
		FROM cse_claim clm
		INNER JOIN cse_case ccase
		ON clm.case_uuid = ccase.case_uuid
		WHERE clm.deleted = 'N'
		AND ccase.deleted = 'N'
		AND ccase.customer_id = :customer_id
		AND ccase.case_id = :case_id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("case_id", $case_id);
		
		$stmt->execute();
		$claim = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;
		
        echo json_encode($claim);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getInactiveWCABSubOutKases(){
	$_SESSION["inactive_subout"] = true;
	getInactiveWCABKases();
}
function getInactiveWCABKases() {
	die();
	//$_SESSION["inactive_type"] = "wcab";
	//getInactiveKases();
}
function getInactivePIKases() {
	$_SESSION["inactive_type"] = "pi";
	getInactiveKases();
}
function getInactiveKases() {
	$thirtyfive_days = mktime(0, 0, 0, date("m"),   date("d") - 35,   date("Y"));
	
	$arrDay = firstAvailableDay( date("Y-m-d", $thirtyfive_days));
	$thirtyfive_days = $arrDay["linux_date"];
	$customer_id = $_SESSION['user_customer_id'];
	
	
	$sql = "
	INNER JOIN (
	SELECT ctr.case_id, MAX(ctr.time_stamp)
	FROM cse_case ccase
	INNER JOIN cse_case_track ctr
	ON ccase.case_id = ctr.case_id
	WHERE 1 
	AND ccase.case_status NOT LIKE '%close%' AND ccase.case_status NOT LIKE 'CL-%' AND ccase.case_status NOT LIKE 'CLOSED%' AND ccase.case_status != 'DROPPED' AND ccase.case_status != 'REJECTED'
	AND ccase.deleted ='N' 
	AND ccase.customer_id = '" . $customer_id . "'
	GROUP BY ctr.case_id
	HAVING MAX(ctr.time_stamp) < '" . $thirtyfive_days . "'
	) inactivecounts
	ON ccase.case_id = inactivecounts.case_id";
	
	$_SESSION["inactive_query"] = $sql;
	
	getKases();
}
function getInactiveWCABSuboutCount() {
	$_SESSION["inactive_subout"] = true;
	getInactiveWCABCount();
}
function getInactiveWCABCount() {
	$_SESSION["inactive_type"] = "wcab";
	getInactiveCount();
}
function getInactivePICount() {
	$_SESSION["inactive_type"] = "pi";
	getInactiveCount();
}
function getInactiveCount() {
	$wcab_only = false;
	$pi_only = false;
	$subout_equal = " != ";
	if (isset($_SESSION["inactive_type"])) {
		$wcab_only = ($_SESSION["inactive_type"] == "wcab");
		$pi_only = ($_SESSION["inactive_type"] == "pi");
		
		unset($_SESSION["inactive_type"]);
		
		if (isset($_SESSION["inactive_subout"])) {
			$subout_equal = " = ";
			unset($_SESSION["inactive_subout"]);
		}
	}
	
	session_write_close();
	$thirtyfive_days = mktime(0, 0, 0, date("m"),   date("d") - 35,   date("Y"));
	
	$arrDay = firstAvailableDay( date("Y-m-d", $thirtyfive_days));
	$thirtyfive_days = $arrDay["linux_date"];
	
	$sql = "SELECT COUNT(case_id) case_count
	FROM (
	SELECT ctr.case_id, MAX(ctr.time_stamp)
	FROM cse_case ccase
	INNER JOIN cse_case_track ctr
	ON ccase.case_id = ctr.case_id
	WHERE 1 
	AND ccase.case_status NOT LIKE '%close%' AND ccase.case_status NOT LIKE 'CL-%' AND ccase.case_status NOT LIKE 'CLOSED%' AND ccase.case_status != 'DROPPED' AND ccase.case_status != 'REJECTED'
	AND ccase.deleted ='N' 
	AND ccase.customer_id = :customer_id";
	if ($pi_only) {
		$sql .= "
		AND ccase.case_type NOT LIKE 'WC%' 
		AND ccase.case_type NOT LIKE 'W/C%' 
		AND ccase.case_type NOT LIKE 'Worker%'
		AND ccase.case_type != 'social_security' ";
	}
	if ($wcab_only) {
		$sql .= " 
		AND (ccase.case_type LIKE 'WC%' 
		OR ccase.case_type LIKE 'W/C%' 
		OR ccase.case_type LIKE 'Worker%') ";
	}
	if ($subout_equal == " = ") {
		$sql .= " 
		AND (ccase.case_status ". $subout_equal . " 'OP-SUBOUT'";
		$sql .= " 
		OR ccase.case_status ". $subout_equal . " 'Sub')";
	}
	if ($subout_equal == " != ") {
		$sql .= " 
		AND ccase.case_status ". $subout_equal . " 'OP-SUBOUT'";
		$sql .= " 
		AND ccase.case_status ". $subout_equal . " 'Sub'";
	}
	$sql .= " 
	AND ccase.case_status NOT LIKE 'Closed%'
	AND ccase.case_status NOT IN ('Dismissed', 'Dropped', 'Settled')";
	
	$sql .= " 
	GROUP BY ctr.case_id
	HAVING MAX(ctr.time_stamp) < :thirtyfive_days
	) counts";
	
	if ($_SERVER['REMOTE_ADDR']=='47.153.56.2') {
		//echo $thirtyfive_days . "\r\n";
		//die($sql);
	}
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$customer_id = $_SESSION['user_customer_id'];
		$user_id = $_SESSION['user_plain_id'];
		$user_nickname = $_SESSION['user_nickname'];
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("thirtyfive_days", $thirtyfive_days);
		$stmt->execute();
		$unattended = $stmt->fetchObject();
		
		$stmt->closeCursor(); $stmt = null; $db = null;
		echo json_encode($unattended);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
			echo json_encode($error);
	}
}
function getUnattendedCount() {
	getUnattendedCountAll(true);
}
function getUnattendedCountAll($blnMyCases = false) {
	session_write_close();
	$thirtyfive_days = mktime(0, 0, 0, date("m"),   date("d") - 35,   date("Y"));
	
	$arrDay = firstAvailableDay( date("Y-m-d", $thirtyfive_days));
	$thirtyfive_days = $arrDay["linux_date"];
	
	$sql = "SELECT COUNT(case_id) case_count
	FROM (
	SELECT ctr.case_id, MAX(ctr.time_stamp)
	FROM cse_case ccase
	INNER JOIN cse_case_track ctr
	ON ccase.case_id = ctr.case_id
	WHERE 1";
	if ($blnMyCases) {
		$sql .= " AND (ccase.worker = :user_id OR ccase.worker = :user_nickname)";
	}
	$sql .= " AND ccase.case_status NOT LIKE '%close%' AND ccase.case_status NOT LIKE 'CL-%' AND ccase.case_status NOT LIKE 'CLOSED%' AND ccase.case_status NOT LIKE 'Sub%' AND ccase.case_status != 'DROPPED' AND ccase.case_status != 'REJECTED' AND ccase.case_status != 'OP-SUBOUT' AND ccase.case_status != 'Sub'
	AND ccase.deleted ='N' 
	AND ccase.customer_id = :customer_id
	GROUP BY ctr.case_id
	HAVING MAX(ctr.time_stamp) < :thirtyfive_days
	) counts";
	if (!$blnMyCases) {
	//	die($sql);
	}
	/*
	if ($_SERVER['REMOTE_ADDR']=='47.153.56.2') {
		echo $sql . "<br />";
	}
	*/
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$customer_id = $_SESSION['user_customer_id'];
		$user_id = $_SESSION['user_plain_id'];
		$user_nickname = $_SESSION['user_nickname'];
		$stmt->bindParam("customer_id", $customer_id);
		if ($blnMyCases) {
			$stmt->bindParam("user_id", $user_id);
			$stmt->bindParam("user_nickname", $user_nickname);
		}
		$stmt->bindParam("thirtyfive_days", $thirtyfive_days);
		$stmt->execute();
		$unattended = $stmt->fetchObject();
		
		$stmt->closeCursor(); $stmt = null; $db = null;
		echo json_encode($unattended);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
			echo json_encode($error);
	}
}
function getAbacusInfo($thumbnail_folder, $db_name) {
	session_write_close();
	if ($_SESSION['user_customer_id']=="") {
		return false;
	}
	if (!is_numeric($_SESSION['user_customer_id'])) {
		return false;
	}
	$arrFolder = explode("/", $thumbnail_folder);
	$thumbnail_folder = $arrFolder[0];
	if (strlen($thumbnail_folder) > 0) {
		$thumbnail_folder = strtolower($thumbnail_folder);
		$sql = "SELECT ccase.case_id id, ccase.case_uuid uuid, ccase.lien_filed, ccase.special_instructions,ccase.case_description, IF(ccase.case_number='UNKNOWN' AND ccase.file_number!='', '', ccase.case_number) case_number, ccase.file_number, ccase.cpointer,inj.adj_number,
				ccase.case_date, ccase.case_type, venue.venue_uuid, ccase.rating, ccase.medical, ccase.td, ccase.rehab,  ccase.edd, ccase.claims, ccase.injury_type, ccase.sub_in,
				
				venue_corporation.corporation_id venue_id, venue.venue_uuid, IF(venue.venue IS NULL, '', venue.venue) venue, venue_abbr, 
				venue_corporation.street venue_street, venue_corporation.city venue_city, 
				venue_corporation.state venue_state, venue_corporation.zip venue_zip,
				
				ccase.case_status, ccase.case_substatus, ccase.case_subsubstatus, ccase.submittedOn, ccase.supervising_attorney,
		ccase.attorney, ccase.worker, ccase.interpreter_needed, ccase.file_location, ccase.case_language `case_language`, 
				app.deleted applicant_deleted, app.person_id applicant_id, app.person_uuid applicant_uuid, app.salutation applicant_salutation, IFNULL(app.full_name, '') `full_name`, app.first_name, app.last_name, app.middle_name, app.`aka`, 
				app.dob, app.gender, app.ssn, app.ein, app.full_address applicant_full_address, app.street applicant_street, app.suite applicant_suite, app.city applicant_city, app.state applicant_state, app.zip applicant_zip, app.phone applicant_phone, app.fax applicant_fax, app.cell_phone applicant_cell, app.age applicant_age,
				
				IFNULL(employer.`corporation_id`,-1) employer_id, employer.company_name employer, employer.full_name employer_full_name, employer.street employer_street, employer.city employer_city,
				employer.state employer_state, employer.zip employer_zip,
				
				IFNULL(defendant.`corporation_id`,-1) defendant_id, defendant.company_name defendant, defendant.full_name defendant_full_name, defendant.street defendant_street, defendant.city defendant_city,
				defendant.state defendant_state, defendant.zip defendant_zip,
				
				CONCAT(app.first_name,' ', IF(app.middle_name='','',CONCAT(app.middle_name,' ')),app.last_name,' vs ', IFNULL(employer.`company_name`, '')) `name`, ccase.case_name, 
				
				IFNULL(att.nickname, '') as attorney_name, 
				IFNULL(att.user_name, '') as attorney_full_name, 
				IFNULL(att.user_email, '') as attorney_email, 
				IFNULL(user.nickname, '') as worker_name, IFNULL(user.user_name, '') as worker_full_name, IFNULL(user.user_email, '') as worker_email,
				IFNULL(lien.lien_id, -1) lien_id, 
				IFNULL(settlement.settlement_id, IFNULL(settlementsheet.settlementsheet_id, -1)) settlement_id,
				IF (cif.fee_uuid IS NOT NULL, '1', '-1') fee_id,
				job.job_id worker_job_id, job.job_uuid worker_job_uuid, if(job.job IS NULL, '', job.job) worker_job
				
				FROM `ikase_" . $db_name . "`. cse_case ccase ";
	
				if (isset($_SESSION["restricted_clients"])) {
					$restricted_clients = $_SESSION["restricted_clients"];
					
					if ($restricted_clients!="") {
						//$restricted_clients = "'" . str_replace(",", "','", $restricted_clients) . "'";
						$sql .= " INNER JOIN (
								SELECT DISTINCT ccorp.case_uuid
								FROM `ikase_" . $db_name . "`.cse_case_corporation ccorp
								INNER JOIN `ikase_" . $db_name . "`.cse_corporation corp
								ON ccorp.corporation_uuid = corp.corporation_uuid
								where corp.parent_corporation_uuid IN (" . $restricted_clients . ")
							) restricteds
							ON ccase.case_uuid = restricteds.case_uuid";
					}
				}
				
				$sql .= " 
				LEFT OUTER JOIN `ikase_" . $db_name . "`.cse_case_person ccapp ON (ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N')
				LEFT OUTER JOIN ";
	if (($_SESSION['user_customer_id']==1033)) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "`ikase_" . $db_name . "`.cse_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
				LEFT OUTER JOIN `ikase_" . $db_name . "`.`cse_case_venue` cvenue
				ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
				LEFT OUTER JOIN `ikase_" . $db_name . "`.`cse_venue` venue
				ON cvenue.venue_uuid = venue.venue_uuid
				
				LEFT OUTER JOIN `ikase_" . $db_name . "`.`cse_case_corporation` ccorp
				ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
				LEFT OUTER JOIN `ikase_" . $db_name . "`.`cse_corporation` employer
				ON ccorp.corporation_uuid = employer.corporation_uuid
				
				LEFT OUTER JOIN `ikase_" . $db_name . "`.`cse_case_corporation` dcorp
				ON (ccase.case_uuid = dcorp.case_uuid AND ccorp.attribute = 'defendant' AND dcorp.deleted = 'N')
				LEFT OUTER JOIN `ikase_" . $db_name . "`.`cse_corporation` defendant
				ON dcorp.corporation_uuid = defendant.corporation_uuid
				
				LEFT OUTER JOIN `ikase_" . $db_name . "`.`cse_case_corporation` ccorp_venue
				ON (ccase.case_uuid = ccorp_venue.case_uuid AND ccorp_venue.attribute = 'venue' AND ccorp_venue.deleted = 'N')
				LEFT OUTER JOIN `ikase_" . $db_name . "`.`cse_corporation` venue_corporation
				ON ccorp_venue.corporation_uuid = venue_corporation.corporation_uuid
				
				LEFT OUTER JOIN `ikase_" . $db_name . "`.`cse_case_injury` cinj
				ON ccase.case_uuid = cinj.case_uuid
				LEFT OUTER JOIN `ikase_" . $db_name . "`.`cse_injury` inj
				ON cinj.injury_uuid = inj.injury_uuid
				LEFT OUTER JOIN `ikase_" . $db_name . "`.`cse_injury_lien` cil
				ON inj.injury_uuid = cil.injury_uuid
				LEFT OUTER JOIN `ikase_" . $db_name . "`.`cse_injury_fee` cif ON inj.injury_uuid = cif.injury_uuid AND cif.deleted = 'N'
				LEFT OUTER JOIN `ikase_" . $db_name . "`.`cse_lien` lien
				ON cil.lien_uuid = lien.lien_uuid
				LEFT OUTER JOIN `ikase_" . $db_name . "`.`cse_injury_settlement` cis
				ON inj.injury_uuid = cis.injury_uuid AND cis.deleted = 'N' AND cis.`attribute` = 'main'
			 	LEFT OUTER JOIN `ikase_" . $db_name . "`.`cse_settlement` settlement
				ON cis.settlement_uuid = settlement.settlement_uuid
				
				LEFT OUTER JOIN ikase.`cse_user` att
				ON ccase.attorney = att.user_id
				LEFT OUTER JOIN ikase.`cse_user` user
				ON ccase.worker = user.user_id
				
				LEFT OUTER JOIN ikase.`cse_user_job` cjob
				ON (user.user_uuid = cjob.user_uuid AND cjob.deleted = 'N')
				LEFT OUTER JOIN ikase.`cse_job` job
				ON cjob.job_uuid = job.job_uuid
				
				where REPLACE(LOWER(CONCAT(app.last_name, app.first_name)), ' ', '') = '" . $thumbnail_folder . "'
				AND ccase.customer_id = " . $_SESSION['user_customer_id'] . "";
		
		//die($sql);
		try {
			$db = getConnection();
			$stmt = $db->prepare($sql);
			//$stmt->bindParam("thumbnail_folder", $thumbnail_folder);
		
			$stmt->execute();
			$kase = $stmt->fetchObject();
			
			$stmt->closeCursor(); $stmt = null; $db = null;
			if ($kase->case_name != "") {
				$kase->name = $kase->case_name;
			}
			if ($kase->case_number != "" && $kase->file_number=="") {
				$kase->file_number = $kase->case_number;
				$kase->case_number = "";
			}
			//print_r($kase);
			
			return $kase;
	
		} catch(PDOException $e) {
			$error = array("error"=> array("text"=>$e->getMessage()));
				echo json_encode($error);
		}
	}
}
function getKaseInfo($id, $return = "") {
	session_write_close();
	if ($_SESSION['user_customer_id']=="") {
		return false;
	}
	if (!is_numeric($_SESSION['user_customer_id'])) {
		return false;
	}
	if ($id < 0) {
		$error = array("error"=> array("text"=>"Nah"));
		echo json_encode($error);
		//newKase();
		return;
	}
	$db_name = "";
	if ($return=="") {
		if (isset($_SESSION["return"])) {
			$db_name = "`" . $_SESSION["return"]. "`.";
		}
	} else {
		$db_name = "`" . $return. "`.";
	}
	$sql = "SELECT ccase.case_id id, ccase.case_uuid uuid, ccase.lien_filed, ccase.special_instructions,ccase.case_description, IF(ccase.case_number='UNKNOWN' AND ccase.file_number!='', '', ccase.case_number) case_number, ccase.file_number, ccase.cpointer,inj.adj_number, inj.injury_id,
			ccase.case_date, ccase.case_type, venue.venue_uuid, ccase.rating, ccase.medical, ccase.td, ccase.rehab,  ccase.edd, ccase.claims, ccase.injury_type, ccase.sub_in,
			
			venue_corporation.corporation_id venue_id, venue.venue_uuid, IF(venue.venue IS NULL, '', venue.venue) venue, venue_abbr, 
			venue_corporation.street venue_street, venue_corporation.city venue_city, 
			venue_corporation.state venue_state, venue_corporation.zip venue_zip,
			
			ccase.case_status, ccase.case_substatus, ccase.case_subsubstatus, ccase.submittedOn, ccase.supervising_attorney,
    ccase.attorney, ccase.worker, ccase.interpreter_needed, ccase.file_location, ccase.case_language `case_language`, 
			app.deleted applicant_deleted, app.person_id applicant_id, app.person_uuid applicant_uuid, app.salutation applicant_salutation, IFNULL(app.full_name, '') `full_name`, app.first_name, app.last_name, app.middle_name, app.`aka`, 
			app.dob, app.gender, app.ssn, app.ein, app.ein, app.full_address applicant_full_address, app.street applicant_street, app.suite applicant_suite, app.city applicant_city, app.state applicant_state, app.zip applicant_zip, app.phone applicant_phone, app.fax applicant_fax, app.cell_phone applicant_cell, app.age applicant_age,
			
			IFNULL(employer.`corporation_id`,-1) employer_id, employer.company_name employer, employer.full_name employer_full_name, employer.street employer_street, employer.city employer_city,
			employer.state employer_state, employer.zip employer_zip,
			
			IFNULL(defendant.`corporation_id`,-1) defendant_id, defendant.company_name defendant, defendant.full_name defendant_full_name, defendant.street defendant_street, defendant.city defendant_city,
			defendant.state defendant_state, defendant.zip defendant_zip,
			
			CONCAT(app.first_name,' ', IF(app.middle_name='','',CONCAT(app.middle_name,' ')),app.last_name,' vs ', IFNULL(employer.`company_name`, '')) `name`, ccase.case_name, 
			
			IFNULL(att.nickname, '') as attorney_name, 
			IFNULL(att.user_name, '') as attorney_full_name, 
			IFNULL(att.user_email, '') as attorney_email, 
			IFNULL(user.nickname, '') as worker_name, IFNULL(user.user_name, '') as worker_full_name, IFNULL(user.user_email, '') as worker_email,
			IFNULL(lien.lien_id, -1) lien_id, 
			IFNULL(settlement.settlement_id, IFNULL(settlementsheet.settlementsheet_id, -1)) settlement_id,
			IF (cif.fee_uuid IS NOT NULL, '1', '-1') fee_id,
			job.job_id worker_job_id, job.job_uuid worker_job_uuid, if(job.job IS NULL, '', job.job) worker_job
			
			FROM " . $db_name . "cse_case ccase ";

			if (isset($_SESSION["restricted_clients"])) {
				$restricted_clients = $_SESSION["restricted_clients"];
				
				if ($restricted_clients!="") {
					//$restricted_clients = "'" . str_replace(",", "','", $restricted_clients) . "'";
					$sql .= " INNER JOIN (
							SELECT DISTINCT ccorp.case_uuid
							FROM " . $db_name . "cse_case_corporation ccorp
							INNER JOIN " . $db_name . "cse_corporation corp
							ON ccorp.corporation_uuid = corp.corporation_uuid
							where corp.parent_corporation_uuid IN (" . $restricted_clients . ")
						) restricteds
						ON ccase.case_uuid = restricteds.case_uuid";
				}
			}
			
			$sql .= " 
			LEFT OUTER JOIN " . $db_name . "cse_case_person ccapp ON (ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N')
			LEFT OUTER JOIN ";
if (($_SESSION['user_customer_id']==1033)) { 
	$sql .= "(" . SQL_PERSONX . ")";
} else {
	$sql .= "" . $db_name . "cse_person";
}
$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN " . $db_name . "`cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN " . $db_name . "`cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
			LEFT OUTER JOIN " . $db_name . "`cse_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN " . $db_name . "`cse_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			
			LEFT OUTER JOIN " . $db_name . "`cse_case_corporation` dcorp
			ON (ccase.case_uuid = dcorp.case_uuid AND ccorp.attribute = 'defendant' AND dcorp.deleted = 'N')
			LEFT OUTER JOIN " . $db_name . "`cse_corporation` defendant
			ON dcorp.corporation_uuid = defendant.corporation_uuid
			
			LEFT OUTER JOIN " . $db_name . "`cse_case_corporation` ccorp_venue
			ON (ccase.case_uuid = ccorp_venue.case_uuid AND ccorp_venue.attribute = 'venue' AND ccorp_venue.deleted = 'N')
			LEFT OUTER JOIN " . $db_name . "`cse_corporation` venue_corporation
			ON ccorp_venue.corporation_uuid = venue_corporation.corporation_uuid
			LEFT OUTER JOIN " . $db_name . "`cse_case_injury` cinj
			ON ccase.case_uuid = cinj.case_uuid
			LEFT OUTER JOIN " . $db_name . "`cse_injury` inj
			ON cinj.injury_uuid = inj.injury_uuid
			LEFT OUTER JOIN " . $db_name . "`cse_injury_lien` cil
			ON inj.injury_uuid = cil.injury_uuid
			LEFT OUTER JOIN " . $db_name . "`cse_injury_fee` cif 
			ON inj.injury_uuid = cif.injury_uuid AND cif.deleted = 'N'
			LEFT OUTER JOIN " . $db_name . "`cse_lien` lien
			ON cil.lien_uuid = lien.lien_uuid
			LEFT OUTER JOIN " . $db_name . "`cse_injury_settlement` cis
			ON inj.injury_uuid = cis.injury_uuid AND cis.deleted = 'N' AND cis.`attribute` = 'main'
			 LEFT OUTER JOIN " . $db_name . "`cse_settlement` settlement
			ON cis.settlement_uuid = settlement.settlement_uuid
			
			LEFT OUTER JOIN " . $db_name . "`cse_settlementsheet` settlementsheet
			ON cis.settlement_uuid = settlementsheet.settlementsheet_uuid
			
			LEFT OUTER JOIN ikase.`cse_user` att
			ON ccase.attorney = att.user_id
			LEFT OUTER JOIN ikase.`cse_user` user
			ON ccase.worker = user.user_id
			
			LEFT OUTER JOIN ikase.`cse_user_job` cjob
			ON (user.user_uuid = cjob.user_uuid AND cjob.deleted = 'N')
			LEFT OUTER JOIN ikase.`cse_job` job
			ON cjob.job_uuid = job.job_uuid
			
			where ccase.case_id=:id
			AND ccase.customer_id = " . $_SESSION['user_customer_id'] . "";
	
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($id > 0) {
			$stmt->bindParam("id", $id);
		}
		$stmt->execute();
		$kase = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;
		if ($kase->case_name != "") {
			$kase->name = $kase->case_name;
		}
		if ($kase->case_number != "" && $kase->file_number=="") {
			$kase->file_number = $kase->case_number;
			$kase->case_number = "";
		}
		//print_r($kase);
		
        return $kase;

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKaseInfoByFee($fee_id) {
	$sql = "SELECT cci.case_uuid uuid
	FROM `cse_settlement` sett
	INNER JOIN `cse_settlement_fee` sfee
	ON sett.settlement_uuid = sfee.settlement_uuid
	INNER JOIN `cse_fee` fee
	ON sfee.fee_uuid = fee.fee_uuid
	INNER JOIN cse_injury_settlement cis
	ON sett.settlement_uuid = cis.settlement_uuid AND cis.deleted = 'N' AND cis.`attribute` = 'main'
	INNER JOIN cse_case_injury cci
	ON cis.injury_uuid = cci.injury_uuid
	WHERE fee.fee_id = :fee_id
	AND fee.customer_id = :customer_id";
	
	$customer_id = $_SESSION["user_customer_id"];
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("fee_id", $fee_id);
		$stmt->bindParam("customer_id", $customer_id);
		
		$stmt->execute();		
		$kase = $stmt->fetchObject();
		
		$stmt->closeCursor(); $stmt = null; $db = null;
		//die($injury);   
		return $kase; 
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKaseInfoByRx($rx_id) {
	session_write_close();
	if ($_SESSION['user_customer_id']=="") {
		return false;
	}
	if (!is_numeric($_SESSION['user_customer_id'])) {
		return false;
	}
	if ($rx_id < 0) {
		$error = array("error"=> array("text"=>"Nah"));
		echo json_encode($error);
		return;
	}
	$sql = "SELECT ccase.case_id id, ccase.case_uuid uuid, ccase.lien_filed, ccase.special_instructions,ccase.case_description, IF(ccase.case_number='UNKNOWN' AND ccase.file_number!='', '', ccase.case_number) case_number, ccase.file_number, ccase.cpointer,inj.adj_number,
			ccase.case_date, ccase.case_type, venue.venue_uuid, ccase.rating, ccase.medical, ccase.td, ccase.rehab,  ccase.edd, ccase.claims, ccase.injury_type, ccase.sub_in,
			venue.venue_uuid, IF(venue.venue IS NULL, '', venue.venue) venue, venue_abbr, 
			venue_corporation.street venue_street, venue_corporation.city venue_city, 
			venue_corporation.state venue_state, venue_corporation.zip venue_zip,
			ccase.case_status, ccase.case_substatus, ccase.case_subsubstatus, ccase.submittedOn, ccase.supervising_attorney,
    ccase.attorney, ccase.worker, ccase.interpreter_needed, ccase.file_location, ccase.case_language `case_language`, 
			app.deleted applicant_deleted, app.person_id applicant_id, app.person_uuid applicant_uuid, app.salutation applicant_salutation, app.first_name, app.last_name, app.middle_name, app.`aka`, 
			app.dob, app.gender, app.ssn, app.ein, app.full_address applicant_full_address, app.street applicant_street, app.suite applicant_suite, app.city applicant_city, app.state applicant_state, app.zip applicant_zip, app.phone applicant_phone, app.fax applicant_fax, app.cell_phone applicant_cell, app.age applicant_age,
			IFNULL(employer.`corporation_id`,-1) employer_id, employer.company_name employer, employer.full_name employer_full_name, employer.street employer_street, employer.city employer_city,
			employer.state employer_state, employer.zip employer_zip,
			CONCAT(app.first_name,' ', IF(app.middle_name='','',CONCAT(app.middle_name,' ')),app.last_name,' vs ', IFNULL(employer.`company_name`, '')) `name`, ccase.case_name,
			IFNULL(att.nickname, '') as attorney_name, IFNULL(att.user_name, '') as attorney_full_name, 
			IFNULL(user.nickname, '') as worker_name, IFNULL(user.user_name, '') as worker_full_name,
			IFNULL(lien.lien_id, -1) lien_id, 
			IFNULL(settlement.settlement_id, IFNULL(settlementsheet.settlementsheet_id, -1)) settlement_id,
			IF (cif.fee_uuid IS NOT NULL, '1', '-1') fee_id
			, ccase.injury_type, ccase.sub_in FROM cse_case ccase ";

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
			INNER JOIN cse_case_person ccapp ON (ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N')
			INNER JOIN cse_person app ON ccapp.person_uuid = app.person_uuid
			INNER JOIN cse_person_rx cpr ON app.person_uuid = cpr.person_uuid
			INNER JOIN cse_rx crx ON cpr.rx_uuid = crx.rx_uuid
			LEFT OUTER JOIN `cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			LEFT OUTER JOIN `cse_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			
			LEFT OUTER JOIN `cse_case_corporation` ccorp_venue
			ON (ccase.case_uuid = ccorp_venue.case_uuid AND ccorp_venue.attribute = 'venue' AND ccorp_venue.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` venue_corporation
			ON ccorp_venue.corporation_uuid = venue_corporation.corporation_uuid
			
			
			LEFT OUTER JOIN `cse_case_injury` cinj
			ON ccase.case_uuid = cinj.case_uuid
			LEFT OUTER JOIN `cse_injury` inj
			ON cinj.injury_uuid = inj.injury_uuid
			LEFT OUTER JOIN `cse_injury_lien` cil
			ON inj.injury_uuid = cil.injury_uuid
			LEFT OUTER JOIN `cse_injury_fee` cif ON inj.injury_uuid = cif.injury_uuid AND cif.deleted = 'N'
			LEFT OUTER JOIN `cse_lien` lien
			ON cil.lien_uuid = lien.lien_uuid
			LEFT OUTER JOIN `cse_injury_settlement` cis
			ON inj.injury_uuid = cis.injury_uuid AND cis.deleted = 'N' AND cis.`attribute` = 'main'
			 LEFT OUTER JOIN `cse_settlement` settlement
			ON cis.settlement_uuid = settlement.settlement_uuid
			LEFT OUTER JOIN `cse_settlementsheet` settlementsheet
			ON cis.settlement_uuid = settlementsheet.settlementsheet_uuid
			LEFT OUTER JOIN ikase.`cse_user` att
			ON ccase.attorney = att.user_id
			LEFT OUTER JOIN ikase.`cse_user` user
			ON ccase.worker = user.user_id
			
			where crx.rx_id=:rx_id
			AND ccase.customer_id = " . $_SESSION['user_customer_id'] . "";

	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($rx_id > 0) {
			$stmt->bindParam("rx_id", $rx_id);
		}
		$stmt->execute();
		$kase = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;

        return $kase;

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKaseInfoByApplicant($person_id) {
	session_write_close();
	if ($_SESSION['user_customer_id']=="") {
		return false;
	}
	if (!is_numeric($_SESSION['user_customer_id'])) {
		return false;
	}
	if ($person_id < 0) {
		$error = array("error"=> array("text"=>"Nah"));
		echo json_encode($error);
		//newKase();
		return;
	}
	$sql = "SELECT ccase.case_id id, ccase.case_uuid uuid, ccase.lien_filed, ccase.special_instructions,ccase.case_description, IF(ccase.case_number='UNKNOWN' AND ccase.file_number!='', '', ccase.case_number) case_number, ccase.file_number, ccase.cpointer,inj.adj_number,
			ccase.case_date, ccase.case_type, venue.venue_uuid, ccase.rating, ccase.medical, ccase.td, ccase.rehab,  ccase.edd, ccase.claims, ccase.injury_type, ccase.sub_in,
			venue.venue_uuid, IF(venue.venue IS NULL, '', venue.venue) venue, venue_abbr, 
			venue_corporation.street venue_street, venue_corporation.city venue_city, 
			venue_corporation.state venue_state, venue_corporation.zip venue_zip,
			ccase.case_status, ccase.case_substatus, ccase.case_subsubstatus, ccase.submittedOn, ccase.supervising_attorney,
    ccase.attorney, ccase.worker, ccase.interpreter_needed, ccase.file_location, ccase.case_language `case_language`, 
			app.deleted applicant_deleted, app.person_id applicant_id, app.person_uuid applicant_uuid, app.salutation applicant_salutation, app.first_name, app.last_name, app.middle_name, app.`aka`, 
			app.dob, app.gender, app.ssn, app.ein, app.full_address applicant_full_address, app.street applicant_street, app.suite applicant_suite, app.city applicant_city, app.state applicant_state, app.zip applicant_zip, app.phone applicant_phone, app.fax applicant_fax, app.cell_phone applicant_cell, app.age applicant_age,
			IFNULL(employer.`corporation_id`,-1) employer_id, employer.company_name employer, employer.full_name employer_full_name, employer.street employer_street, employer.city employer_city,
			employer.state employer_state, employer.zip employer_zip,
			CONCAT(app.first_name,' ', IF(app.middle_name='','',CONCAT(app.middle_name,' ')),app.last_name,' vs ', IFNULL(employer.`company_name`, '')) `name`, ccase.case_name,
			IFNULL(att.nickname, '') as attorney_name, IFNULL(att.user_name, '') as attorney_full_name, 
			IFNULL(user.nickname, '') as worker_name, IFNULL(user.user_name, '') as worker_full_name,
			IFNULL(lien.lien_id, -1) lien_id, 
			IFNULL(settlement.settlement_id, IFNULL(settlementsheet.settlementsheet_id, -1)) settlement_id,
			IF (cif.fee_uuid IS NOT NULL, '1', '-1') fee_id
			, ccase.injury_type, ccase.sub_in FROM cse_case ccase ";

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
			INNER JOIN cse_case_person ccapp ON (ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N')
			INNER JOIN cse_person app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			LEFT OUTER JOIN `cse_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			
			LEFT OUTER JOIN `cse_case_corporation` ccorp_venue
			ON (ccase.case_uuid = ccorp_venue.case_uuid AND ccorp_venue.attribute = 'venue' AND ccorp_venue.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` venue_corporation
			ON ccorp_venue.corporation_uuid = venue_corporation.corporation_uuid
			
			
			LEFT OUTER JOIN `cse_case_injury` cinj
			ON ccase.case_uuid = cinj.case_uuid
			LEFT OUTER JOIN `cse_injury` inj
			ON cinj.injury_uuid = inj.injury_uuid
			LEFT OUTER JOIN `cse_injury_lien` cil
			ON inj.injury_uuid = cil.injury_uuid
			LEFT OUTER JOIN `cse_injury_fee` cif ON inj.injury_uuid = cif.injury_uuid AND cif.deleted = 'N'
			LEFT OUTER JOIN `cse_lien` lien
			ON cil.lien_uuid = lien.lien_uuid
			LEFT OUTER JOIN `cse_injury_settlement` cis
			ON inj.injury_uuid = cis.injury_uuid AND cis.deleted = 'N' AND cis.`attribute` = 'main'
			 LEFT OUTER JOIN `cse_settlement` settlement
			ON cis.settlement_uuid = settlement.settlement_uuid
			LEFT OUTER JOIN `cse_settlementsheet` settlementsheet
			ON cis.settlement_uuid = settlementsheet.settlementsheet_uuid
			LEFT OUTER JOIN ikase.`cse_user` att
			ON ccase.attorney = att.user_id
			LEFT OUTER JOIN ikase.`cse_user` user
			ON ccase.worker = user.user_id
			
			where app.person_id=:person_id
			AND ccase.customer_id = " . $_SESSION['user_customer_id'] . "";

	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($person_id > 0) {
			$stmt->bindParam("person_id", $person_id);
		}
		$stmt->execute();
		$kase = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;

        return $kase;

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKaseInfoByPartie($corporation_id) {
	session_write_close();
	if ($_SESSION['user_customer_id']=="") {
		return false;
	}
	if (!is_numeric($_SESSION['user_customer_id'])) {
		return false;
	}
	if ($corporation_id < 0) {
		$error = array("error"=> array("text"=>"Nah"));
		echo json_encode($error);
		//newKase();
		return;
	}
	$sql = "SELECT ccase.case_id id, ccase.lien_filed, ccase.special_instructions,ccase.case_description, ccase.case_uuid uuid, corp.company_name, ccorp.attribute
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
			INNER JOIN `cse_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.deleted = 'N')
			INNER JOIN `cse_corporation` corp
			ON ccorp.corporation_uuid = corp.corporation_uuid
			where corp.corporation_id=:corporation_id
			AND ccase.customer_id = " . $_SESSION['user_customer_id'] . "";

	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($corporation_id > 0) {
			$stmt->bindParam("corporation_id", $corporation_id);
		}
		$stmt->execute();
		$kase = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;

        return $kase;

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKaseInfoPriorMedical($corporation_id) {
	session_write_close();
	if ($_SESSION['user_customer_id']=="") {
		return false;
	}
	if (!is_numeric($_SESSION['user_customer_id'])) {
		return false;
	}
	if ($corporation_id < 0) {
		$error = array("error"=> array("text"=>"Nah"));
		echo json_encode($error);
		//newKase();
		return;
	}
	$sql = "SELECT DISTINCT ccase.case_id id, ccase.lien_filed, ccase.special_instructions,ccase.case_description, ccase.case_uuid uuid, corp.company_name, 'prior_medical' `attribute`
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
			INNER JOIN cse_case_person ccp
			ON ccase.case_uuid = ccp.case_uuid
			INNER JOIN `cse_person_corporation` ccorp
			ON (ccp.person_uuid = ccorp.person_uuid AND ccorp.deleted = 'N')
			INNER JOIN `cse_corporation` corp
			ON ccorp.corporation_uuid = corp.corporation_uuid
			where corp.corporation_id=:corporation_id
			AND ccase.customer_id = " . $_SESSION['user_customer_id'] . "";
//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($corporation_id > 0) {
			$stmt->bindParam("corporation_id", $corporation_id);
		}
		$stmt->execute();
		$kase = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;

        return $kase;

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKaseInfoByEvent($event_id) {
	session_write_close();
	if ($_SESSION['user_customer_id']=="") {
		return false;
	}
	if (!is_numeric($_SESSION['user_customer_id'])) {
		return false;
	}
	if ($event_id < 0) {
		$error = array("error"=> array("text"=>"Nah"));
		echo json_encode($error);
		//newKase();
		return;
	}
	$sql = "SELECT ccase.case_id id, ccase.case_uuid uuid, ccase.lien_filed, ccase.special_instructions,ccase.case_description, 
			`event_id`, eve.`event_uuid`, `event_title`, `event_dateandtime`
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
			INNER JOIN `cse_case_event` ceve
			ON ccase.case_uuid = ceve.case_uuid
			INNER JOIN `cse_event` eve
			ON ceve.event_uuid = eve.event_uuid
			where eve.event_id=:event_id
			AND ccase.customer_id = " . $_SESSION['user_customer_id'] . "";
//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($event_id > 0) {
			$stmt->bindParam("event_id", $event_id);
		}
		$stmt->execute();
		$kase = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;

        return $kase;

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKaseInfoByMessage($message_id) {
	session_write_close();
	if ($_SESSION['user_customer_id']=="") {
		return false;
	}
	if (!is_numeric($_SESSION['user_customer_id'])) {
		return false;
	}
	if ($message_id < 0) {
		$error = array("error"=> array("text"=>"Nah"));
		echo json_encode($error);
		//newKase();
		return;
	}
	$sql = "SELECT ccase.case_id id, ccase.lien_filed, ccase.special_instructions,ccase.case_description, ccase.case_uuid uuid, 
			`message_id`, msg.`message_uuid`, `subject`
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
			INNER JOIN `cse_case_message` cmsg
			ON ccase.case_uuid = cmsg.case_uuid
			INNER JOIN `cse_message` msg
			ON cmsg.message_uuid = msg.message_uuid
			where msg.message_id=:message_id
			AND ccase.customer_id = " . $_SESSION['user_customer_id'] . "";
//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($message_id > 0) {
			$stmt->bindParam("message_id", $message_id);
		}
		$stmt->execute();
		$kase = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;

        return $kase;

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKaseInfoByTask($task_id) {
	session_write_close();
	if ($_SESSION['user_customer_id']=="") {
		return false;
	}
	if (!is_numeric($_SESSION['user_customer_id'])) {
		return false;
	}
	if ($task_id < 0) {
		$error = array("error"=> array("text"=>"Nah"));
		echo json_encode($error);
		//newKase();
		return;
	}
	$sql = "SELECT ccase.case_id id, ccase.case_uuid uuid, 
			`task_id`, tsk.`task_uuid`, ccase.lien_filed, ccase.special_instructions,ccase.case_description, `task_title`, `end_date`
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
			INNER JOIN `cse_case_task` ctsk
			ON ccase.case_uuid = ctsk.case_uuid
			INNER JOIN `cse_task` tsk
			ON ctsk.task_uuid = tsk.task_uuid
			where tsk.task_id=:task_id
			AND ccase.customer_id = " . $_SESSION['user_customer_id'] . "";
//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($task_id > 0) {
			$stmt->bindParam("task_id", $task_id);
		}
		$stmt->execute();
		$kase = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;

        return $kase;

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function getKaseInfoByCheck($check_id) {
	session_write_close();
	if ($_SESSION['user_customer_id']=="") {
		return false;
	}
	if (!is_numeric($_SESSION['user_customer_id'])) {
		return false;
	}
	if ($check_id < 0) {
		$error = array("error"=> array("text"=>"Nah"));
		echo json_encode($error);
		//newKase();
		return;
	}
	$sql = "SELECT ccase.case_id id, ccase.case_uuid uuid, 
			`check_id`, tsk.`check_uuid`, `check_number`, `transaction_date`
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
			INNER JOIN `cse_case_check` ctsk
			ON ccase.case_uuid = ctsk.case_uuid
			INNER JOIN `cse_check` tsk
			ON ctsk.check_uuid = tsk.check_uuid
			where tsk.check_id=:check_id
			AND ccase.customer_id = " . $_SESSION['user_customer_id'];
//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($check_id > 0) {
			$stmt->bindParam("check_id", $check_id);
		}
		$stmt->execute();
		$kase = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;

        return $kase;

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKaseInfoByDeduction($deduction_id) {
	session_write_close();
	if ($_SESSION['user_customer_id']=="") {
		return false;
	}
	if (!is_numeric($_SESSION['user_customer_id'])) {
		return false;
	}
	if ($deduction_id < 0) {
		$error = array("error"=> array("text"=>"Nah"));
		echo json_encode($error);
		//newKase();
		return;
	}
	$sql = "SELECT ccase.case_id id, ccase.case_uuid uuid
			FROM cse_case ccase ";
			$sql .= " 
			INNER JOIN `cse_case_deduction` ctsk
			ON ccase.case_uuid = ctsk.case_uuid
			INNER JOIN `cse_deduction` tsk
			ON ctsk.deduction_uuid = tsk.deduction_uuid
			where tsk.deduction_id = :deduction_id
			AND ccase.customer_id = " . $_SESSION['user_customer_id'];
//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($deduction_id > 0) {
			$stmt->bindParam("deduction_id", $deduction_id);
		}
		$stmt->execute();
		$kase = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;

        return $kase;

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKaseInfoByCheckRequest($checkrequest_id) {
	session_write_close();
	if ($_SESSION['user_customer_id']=="") {
		return false;
	}
	if (!is_numeric($_SESSION['user_customer_id'])) {
		return false;
	}
	if ($checkrequest_id < 0) {
		$error = array("error"=> array("text"=>"Nah"));
		echo json_encode($error);
		//newKase();
		return;
	}
	$sql = "SELECT ccase.case_id id, ccase.case_uuid uuid
			FROM cse_case ccase ";
			$sql .= " 
			INNER JOIN `cse_case_checkrequest` ctsk
			ON ccase.case_uuid = ctsk.case_uuid
			INNER JOIN `cse_checkrequest` tsk
			ON ctsk.checkrequest_uuid = tsk.checkrequest_uuid
			where tsk.checkrequest_id = :checkrequest_id
			AND ccase.customer_id = " . $_SESSION['user_customer_id'];
//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($checkrequest_id > 0) {
			$stmt->bindParam("checkrequest_id", $checkrequest_id);
		}
		$stmt->execute();
		$kase = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;

        return $kase;

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKaseInfoByInjury($injury_id) {
	session_write_close();
	if ($_SESSION['user_customer_id']=="") {
		return false;
	}
	if (!is_numeric($_SESSION['user_customer_id'])) {
		return false;
	}
	if ($injury_id < 0) {
		$error = array("error"=> array("text"=>"Nah"));
		echo json_encode($error);
		//newKase();
		return;
	}
	$sql = "SELECT ccase.case_id id, ccase.case_uuid uuid, 
			`injury_id`, inj.`injury_uuid`, inj.`adj_number`, `type`, `occupation`, 
			`start_date`, `end_date`, `explanation`, `full_address`, `suite`, 
			inj.`customer_id`, inj.`deleted`, ccase.`injury_type`
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
			INNER JOIN `cse_case_injury` cinj
			ON ccase.case_uuid = cinj.case_uuid
			INNER JOIN `cse_injury` inj
			ON cinj.injury_uuid = inj.injury_uuid
			where inj.injury_id=:injury_id
			AND ccase.customer_id = " . $_SESSION['user_customer_id'] . "";
//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($injury_id > 0) {
			$stmt->bindParam("injury_id", $injury_id);
		}
		$stmt->execute();
		$kase = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;

        return $kase;

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>"kase info: " . $e->getMessage()));
        	echo json_encode($error);
	}
}
function getKaseInfoByDocument($document_id) {
	session_write_close();
	if ($_SESSION['user_customer_id']=="") {
		return false;
	}
	if (!is_numeric($_SESSION['user_customer_id'])) {
		return false;
	}
	if ($document_id < 0) {
		$error = array("error"=> array("text"=>"Nah"));
		echo json_encode($error);
		//newKase();
		return;
	}
	$sql = "SELECT ccase.case_id id, ccase.case_uuid uuid, 
			`document`.*
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
			INNER JOIN `cse_case_document` ccn
			ON ccase.case_uuid = ccn.case_uuid
			INNER JOIN `cse_document` document
			ON ccn.document_uuid = document.document_uuid
			where document.document_id=:document_id
			AND ccase.customer_id = " . $_SESSION['user_customer_id'] . "";
//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($document_id > 0) {
			$stmt->bindParam("document_id", $document_id);
		}
		$stmt->execute();
		$kase = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;

        return $kase;

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKaseInfoByNote($notes_id) {
	session_write_close();
	if ($_SESSION['user_customer_id']=="") {
		return false;
	}
	if (!is_numeric($_SESSION['user_customer_id'])) {
		return false;
	}
	if ($notes_id < 0) {
		$error = array("error"=> array("text"=>"Nah"));
		echo json_encode($error);
		//newKase();
		return;
	}
	$sql = "SELECT ccase.case_id id, ccase.case_uuid uuid, 
			`notes`.*
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
			INNER JOIN `cse_case_notes` ccn
			ON ccase.case_uuid = ccn.case_uuid
			INNER JOIN `cse_notes` notes
			ON ccn.notes_uuid = notes.notes_uuid
			where notes.notes_id=:notes_id
			AND ccase.customer_id = " . $_SESSION['user_customer_id'] . "";
//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($notes_id > 0) {
			$stmt->bindParam("notes_id", $notes_id);
		}
		$stmt->execute();
		$kase = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;

        return $kase;

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKaseInfoByInjuryNumber($injury_number_id) {
	session_write_close();
	if ($_SESSION['user_customer_id']=="") {
		return false;
	}
	if (!is_numeric($_SESSION['user_customer_id'])) {
		return false;
	}
	if ($injury_number_id < 0) {
		$error = array("error"=> array("text"=>"Nah"));
		echo json_encode($error);
		//newKase();
		return;
	}
	$sql = "SELECT ccase.case_id id, ccase.case_uuid uuid, 
			`injury_id`, inj.`injury_uuid`, inj.`adj_number`, `type`, `occupation`, 
			`start_date`, `end_date`, `explanation`, `full_address`, `suite`, 
			inj.`customer_id`, inj.`deleted`, ccase.`injury_type`
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
			INNER JOIN `cse_case_injury` cinj
			ON ccase.case_uuid = cinj.case_uuid
			INNER JOIN `cse_injury` inj
			ON cinj.injury_uuid = inj.injury_uuid
			INNER JOIN `cse_injury_injury_number` ciin
			ON inj.injury_uuid = ciin.injury_uuid
			INNER JOIN `cse_injury_number` cin
			ON ciin.injury_number_uuid = cin.injury_number_uuid
			where cin.injury_number_id=:injury_number_id
			AND ccase.customer_id = " . $_SESSION['user_customer_id'] . "";
//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($injury_number_id > 0) {
			$stmt->bindParam("injury_number_id", $injury_number_id);
		}
		$stmt->execute();
		$kase = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;

        return $kase;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKaseInfoByExam($exam_id) {	
	session_write_close();
	if ($_SESSION['user_customer_id']=="") {
		return false;
	}
	if (!is_numeric($_SESSION['user_customer_id'])) {
		return false;
	}
	if ($exam_id < 0) {
		$error = array("error"=> array("text"=>"Nah"));
		echo json_encode($error);
		//newKase();
		return;
	}
	$sql = "SELECT ccase.case_id id, ccase.case_uuid uuid, corp.company_name
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
			INNER JOIN `cse_case_corporation` ccorp
			ON ccase.case_uuid = ccorp.case_uuid
			INNER JOIN `cse_corporation` corp
			ON ccorp.corporation_uuid = corp.corporation_uuid
			INNER JOIN `cse_corporation_exam` ccorpx
			ON corp.corporation_uuid = ccorpx.corporation_uuid
			INNER JOIN `cse_exam` cex
			ON ccorpx.exam_uuid = cex.exam_uuid
			where cex.exam_id=:exam_id
			AND ccase.customer_id = " . $_SESSION['user_customer_id'] . "";
//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($exam_id > 0) {
			$stmt->bindParam("exam_id", $exam_id);
		}
		$stmt->execute();
		$kase = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;

        return $kase;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKaseInfoBySettlement($settlement_id) {
	session_write_close();
	if ($_SESSION['user_customer_id']=="") {
		return false;
	}
	if (!is_numeric($_SESSION['user_customer_id'])) {
		return false;
	}
	if ($settlement_id < 0) {
		$error = array("error"=> array("text"=>"Nah"));
		echo json_encode($error);
		//newKase();
		return;
	}
	$sql = "SELECT ccase.case_id id, ccase.case_uuid uuid, 
			`injury_id`, inj.`injury_uuid`, inj.`adj_number`, `type`, `occupation`, 
			`start_date`, `end_date`, `explanation`, `full_address`, `suite`, 
			inj.`customer_id`, inj.`deleted`
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
			INNER JOIN `cse_case_injury` cinj
			ON ccase.case_uuid = cinj.case_uuid
			INNER JOIN `cse_injury` inj
			ON cinj.injury_uuid = inj.injury_uuid
			INNER JOIN `cse_injury_settlement` ciin
			ON inj.injury_uuid = ciin.injury_uuid AND ciin.deleted = 'N' AND ciin.`attribute` = 'main'
			INNER JOIN `cse_settlement` cin
			ON ciin.settlement_uuid = cin.settlement_uuid
			where cin.settlement_id=:settlement_id
			AND ccase.customer_id = " . $_SESSION['user_customer_id'] . "";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($settlement_id > 0) {
			$stmt->bindParam("settlement_id", $settlement_id);
		}


		$stmt->execute();
		$kase = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;

        return $kase;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKaseInfoByLien($lien_id) {
	if ($_SESSION['user_customer_id']=="") {
		return false;
	}
	if (!is_numeric($_SESSION['user_customer_id'])) {
		return false;
	}
	if ($lien_id < 0) {
		$error = array("error"=> array("text"=>"Nah"));
		echo json_encode($error);
		//newKase();
		return;
	}
	session_write_close();
	$sql = "SELECT ccase.case_id id, ccase.case_uuid uuid, 
			`injury_id`, inj.`injury_uuid`, inj.`adj_number`, `type`, `occupation`, 
			`start_date`, `end_date`, `explanation`, `full_address`, `suite`, 
			inj.`customer_id`, inj.`deleted`
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
			INNER JOIN `cse_case_injury` cinj
			ON ccase.case_uuid = cinj.case_uuid
			INNER JOIN `cse_injury` inj
			ON cinj.injury_uuid = inj.injury_uuid
			INNER JOIN `cse_injury_lien` ciin
			ON inj.injury_uuid = ciin.injury_uuid
			INNER JOIN `cse_lien` cin
			ON ciin.lien_uuid = cin.lien_uuid
			where cin.lien_id=:lien_id
			AND ccase.customer_id = " . $_SESSION['user_customer_id'] . "";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($lien_id > 0) {
			$stmt->bindParam("lien_id", $lien_id);
		}
		$stmt->execute();
		$kase = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;

        return $kase;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKaseVenueInfo($id) {
	if ($_SESSION['user_customer_id']=="") {
		return false;
	}
	if (!is_numeric($_SESSION['user_customer_id'])) {
		return false;
	}
	if ($id < 0) {
		$error = array("error"=> array("text"=>"Nah"));
		echo json_encode($error);
		//newKase();
		return;
	}
	session_write_close();
	$sql = "SELECT 
			venue_corporation.*
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
			INNER JOIN `cse_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.deleted = 'N')
			INNER JOIN `cse_corporation` venue_corporation
			ON ccorp.corporation_uuid = venue_corporation.corporation_uuid AND (ccorp.attribute = 'venue' OR `venue_corporation`.`type` = 'venue')
			where ccase.case_id=:id
			AND venue_corporation.deleted = 'N'
			AND ccase.customer_id = " . $_SESSION['user_customer_id'];
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($id > 0) {
			$stmt->bindParam("id", $id);
		}
		$stmt->execute();
		$venue = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;

        return $venue;

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKaseJetFile($case_id, $injury_id) {
	session_write_close();
	$sql = "SELECT ccase.case_id id, ccase.case_uuid uuid, ccase.lien_filed, ccase.case_number, ccase.cpointer,
		inj.injury_id, inj.adj_number, inj.occupation, inj.start_date, inj.end_date, inj.full_address, inj.street, inj.city, inj.state, inj.zip,
		ccase.case_date, ccase.case_type, venue.venue_uuid, ccase.rating, ccase.medical, ccase.td, ccase.rehab,  ccase.edd, ccase.claims, ccase.injury_type, ccase.sub_in,
		
		venue_corporation.corporation_id venue_id, venue.venue_uuid, IF(venue.venue IS NULL, '', venue.venue) venue, venue_abbr, 
		venue_corporation.street venue_street, venue_corporation.city venue_city, 
		venue_corporation.state venue_state, venue_corporation.zip venue_zip,
		
		ccase.case_status, ccase.case_substatus, ccase.case_subsubstatus, ccase.submittedOn, ccase.supervising_attorney,
ccase.attorney, ccase.worker, ccase.interpreter_needed, ccase.file_location, ccase.case_language `case_language`, 
		app.deleted applicant_deleted, app.person_id applicant_id, app.person_uuid applicant_uuid, app.salutation applicant_salutation, IFNULL(app.full_name, '') `full_name`, app.first_name, app.last_name, app.middle_name, app.`aka`, 
		app.dob, app.gender, app.ssn, app.ein, app.full_address applicant_full_address, app.street applicant_street, app.suite applicant_suite, app.city applicant_city, app.state applicant_state, app.zip applicant_zip, app.phone applicant_phone, app.fax applicant_fax, app.cell_phone applicant_cell, app.age applicant_age,
		
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
		IFNULL(settlement.settlement_id, IFNULL(settlementsheet.settlementsheet_id, -1)) settlement_id,
		IF (cif.fee_uuid IS NOT NULL, '1', '-1') fee_id,
		job.job_id worker_job_id, job.job_uuid worker_job_uuid, if(job.job IS NULL, '', job.job) worker_job,
		IFNULL(jfile.jetfile_id, '') jetfile_id,
		IFNULL(jfile.jetfile_case_id, '') jetfile_case_id, 
		IFNULL(jfile.app_filing_id, '') app_filing_id, 
		IFNULL(jfile.info, '') jetfile_info,
		IFNULL(jfile.jetfile_dor_id, '') jetfile_dor_id,
		IFNULL(jfile.dor_info, '') dor_info,
		IFNULL(jfile.jetfile_dore_id, '') jetfile_dore_id,
		IFNULL(jfile.dore_info, '') dore_info,
		IFNULL(jfile.jetfile_lien_id, '') jetfile_lien_id,
		IFNULL(jfile.lien_info, '') lien_info
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
			LEFT OUTER JOIN `cse_settlementsheet` settlementsheet
			ON cis.settlement_uuid = settlementsheet.settlementsheet_uuid
			
			LEFT OUTER JOIN ikase.`cse_user` att
			ON ccase.attorney = att.user_id
			LEFT OUTER JOIN ikase.`cse_user` user
			ON ccase.worker = user.user_id
			
			LEFT OUTER JOIN ikase.`cse_user_job` cjob
			ON (user.user_uuid = cjob.user_uuid AND cjob.deleted = 'N')
			LEFT OUTER JOIN ikase.`cse_job` job
			ON cjob.job_uuid = job.job_uuid
			
			where 1
			AND inj.injury_id=:injury_id
			AND ccase.case_id=:case_id
			AND ccase.customer_id = " . $_SESSION['user_customer_id'] . "";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("injury_id", $injury_id);
		
		$stmt->execute();
		$kase = $stmt->fetchObject();
		
		//die(print_r($kase));
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		return $kase;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
	}
}
function searchAdvanceKase() {
	//session write close is later because we save the last query in session
	$sql = "SELECT DISTINCT 
			inj.injury_id id, ccase.case_id, ccase.lien_filed,ccase.case_description, inj.injury_number, ccase.case_uuid uuid, IF(ccase.case_number='UNKNOWN' AND ccase.file_number!='', '', ccase.case_number) case_number, ccase.file_number, ccase.cpointer,ccase.source, inj.injury_number, inj.adj_number, ccase.rating, 
			IF (DATE_FORMAT(ccase.case_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.case_date, '%m/%d/%Y')) `case_date`, 
			IF (DATE_FORMAT(ccase.terminated_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.terminated_date, '%m/%d/%Y')) `terminated_date`,
ccase.case_type, ccase.medical, ccase.td, ccase.rehab,  ccase.edd, ccase.claims,
			venue.venue_uuid, IF(venue.venue IS NULL, '', venue.venue) venue, venue.address1 venue_street, venue.address2 venue_suite, venue.city venue_city, venue.zip venue_zip, venue_abbr, ccase.case_status, ccase.case_substatus, ccase.case_subsubstatus, ccase.submittedOn, ccase.supervising_attorney,
    ccase.supervising_attorney,
    ccase.attorney, 
			IFNULL(superatt.nickname, '') as supervising_attorney_name, IFNULL(superatt.user_name, '') as supervising_attorney_full_name,
			ccase.worker, ccase.interpreter_needed, ccase.file_location, ccase.case_language `case_language`, 
			app.person_id applicant_id, app.person_uuid applicant_uuid, IFNULL(app.salutation, '') applicant_salutation,
			IF (app.first_name IS NULL, '', TRIM(app.first_name)) first_name , IF(app.last_name IS NULL, '', app.last_name) last_name, IFNULL(app.full_name, '') `full_name`, IFNULL(app.language, '') language,
			IF (DATE_FORMAT(app.dob, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(app.dob, '%m/%d/%Y')) dob, app.dob app_dob, 
			IF (app.ssn = 'XXXXXXXXX', '', app.ssn) ssn, IFNULL(app.email, '') applicant_email, IFNULL(app.phone, '') applicant_phone,IFNULL(app.full_address, '') applicant_full_address,
			IFNULL(employer.`corporation_id`,-1) employer_id, employer.`corporation_uuid` employer_uuid, IFNULL(employer.`company_name`, '') employer, employer.`full_address` employer_full_address, employer.`suite` `employer_suite`,
			IFNULL(defendant.`corporation_id`,-1) defendant_id, defendant.`corporation_uuid` defendant_uuid, defendant.`company_name` defendant, defendant.`full_address` defendant_full_address,
			IFNULL(plaintiff.`corporation_id`,-1) plaintiff_id, plaintiff.`corporation_uuid` plaintiff_uuid, plaintiff.`company_name` plaintiff, plaintiff.`full_address` plaintiff_full_address,
			IFNULL(client.`corporation_id`,-1) client_id, client.`corporation_uuid` client_uuid, client.`company_name` client, client.`full_address` client_full_address,
			IFNULL(passenger.`corporation_id`,-1) passenger_id, passenger.`corporation_uuid` passenger_uuid, passenger.`company_name` passenger, passenger.`full_address` passenger_full_address,
			IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')) start_date, IF (DATE_FORMAT(inj.statute_limitation, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.statute_limitation, '%m/%d/%Y')) statute_limitation, 
			IF (DATE_FORMAT(inj.end_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.end_date, '%m/%d/%Y')) end_date, inj.ct_dates_note,
			IF (inj.occupation IS NULL, '' , inj.occupation) occupation,
			CONCAT(app.first_name,' ',app.last_name,' vs ', IFNULL(employer.`company_name`, ''),' - ', 
            REPLACE(IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')), '00/00/0000', '')) `name`, ccase.case_name, 
			att.user_id attorney_id, user.user_id, 
			IFNULL(att.nickname, '') as attorney_name, IFNULL(att.user_name, '') as attorney_full_name, 
			IFNULL(user.nickname, '') as worker_name, IFNULL(user.user_name, '') as worker_full_name,
			IFNULL(lien.lien_id, -1) lien_id, 
			IFNULL(settlement.settlement_id, IFNULL(settlementsheet.settlementsheet_id, -1)) settlement_id,
			IF (cif.fee_uuid IS NOT NULL, '1', '-1') fee_id
			, ccase.injury_type, ccase.sub_in, ccase.special_instructions 
			
			FROM cse_case ccase 
			INNER JOIN `cse_case_injury` cinj
			ON ccase.case_uuid = cinj.case_uuid AND cinj.deleted = 'N' AND cinj.`attribute` = 'main'
			INNER JOIN `cse_injury` inj
			ON cinj.injury_uuid = inj.injury_uuid AND inj.deleted = 'N'
			";

			if (isset($_SESSION["restricted_clients"])) {
				$restricted_clients = $_SESSION["restricted_clients"];
				
				if ($restricted_clients!="") {
					//$restricted_clients = "'" . str_replace(",", "','", $restricted_clients) . "'";
					$sql .= " 
						INNER JOIN (
							SELECT DISTINCT ccorp.case_uuid
							FROM cse_case_corporation ccorp
							INNER JOIN cse_corporation corp
							ON ccorp.corporation_uuid = corp.corporation_uuid
							where corp.parent_corporation_uuid IN (" . $restricted_clients . ")
						) restricteds
						ON ccase.case_uuid = restricteds.case_uuid";
				}
			}
			$bodyparts = "";
			if (isset($_POST["bodypartSearch"])) {
				//die(print_r($_POST));
				$value = $_POST["bodypartSearch"];
				if (count($value) > 0) {
					$bodyparts = "'" . implode("','", $value) . "'";
				}
				
				//die($bodyparts);
			}
			//bodyparts
			if ($bodyparts!="") {
				$sql .= " 
				INNER JOIN cse_injury_bodyparts cib
				ON inj.injury_uuid = cib.injury_uuid AND cib.`status` != 'N'";
			}
			$sql .= " 
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N'
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
			LEFT OUTER JOIN `cse_case_corporation` ecorp
			ON (ccase.case_uuid = ecorp.case_uuid AND ecorp.attribute = 'defendant' AND ecorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` defendant
			ON ecorp.corporation_uuid = defendant.corporation_uuid
			
			LEFT OUTER JOIN `cse_case_corporation` fcorp
			ON (ccase.case_uuid = fcorp.case_uuid AND fcorp.attribute = 'plaintiff' AND fcorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` plaintiff
			ON fcorp.corporation_uuid = plaintiff.corporation_uuid
			
			LEFT OUTER JOIN `cse_case_corporation` gcorp
			ON (ccase.case_uuid = gcorp.case_uuid AND gcorp.attribute = 'client' AND gcorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` `client`
			ON gcorp.corporation_uuid = client.corporation_uuid
			
			LEFT OUTER JOIN `cse_case_corporation` hcorp
			ON (ccase.case_uuid = hcorp.case_uuid AND hcorp.attribute = 'passenger' AND hcorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` `passenger`
			ON hcorp.corporation_uuid = passenger.corporation_uuid
			
			
			LEFT OUTER JOIN `cse_injury_lien` cil
			ON inj.injury_uuid = cil.injury_uuid
			LEFT OUTER JOIN `cse_injury_fee` cif ON inj.injury_uuid = cif.injury_uuid AND cif.deleted = 'N'
			LEFT OUTER JOIN `cse_lien` lien
			ON cil.lien_uuid = lien.lien_uuid
			LEFT OUTER JOIN `cse_injury_settlement` cis
			ON inj.injury_uuid = cis.injury_uuid AND cis.deleted = 'N' AND cis.`attribute` = 'main'
			 LEFT OUTER JOIN `cse_settlement` settlement
			ON cis.settlement_uuid = settlement.settlement_uuid
			LEFT OUTER JOIN `cse_settlementsheet` settlementsheet
			ON cis.settlement_uuid = settlementsheet.settlementsheet_uuid
			LEFT OUTER JOIN ikase.`cse_user` superatt
			ON ccase.supervising_attorney = superatt.user_id
			LEFT OUTER JOIN ikase.`cse_user` att
			ON ccase.attorney = att.user_id
			LEFT OUTER JOIN ikase.`cse_user` user
			ON ccase.worker = user.user_id
			WHERE ccase.deleted ='N' 
			AND ccase.customer_id = " . $_SESSION['user_customer_id'];
	//now put the search terms together
	$arrSearches = array();
	$blnSpecialInstructions = false;
	$arrEmployees = array();
	$starts_with = "";
	//die(print_r($_POST));
	foreach($_POST as $fieldname=>$value) {
		if ($fieldname=="bodypartSearch") {
			/*
			if ($value!="") {
				$bodyparts = "'" . implode("','", $value) . "'";
			}
			*/
			continue;
		}
		if ($fieldname == "subouts") {
			if ($value!="") {
				if ($value == "Y") {
					$sql_subout = " (ccase.case_status = 'OP-SUBOUT'";
					$sql_subout .= " OR ccase.case_status = 'Sub')";
				} else {
					$sql_subout = " (ccase.case_status != 'OP-SUBOUT'";
					$sql_subout .= " AND ccase.case_status != 'Sub')";
				}
				$arrSearches[] = $sql_subout;
			}
			continue;
		}
		
		$value = passed_var($fieldname, "post");
		
		if (strpos($fieldname, "date") > -1) {
			if ($value!="") {
				$value = date("Y-m-d", strtotime($value));
			}
		}
		if ($fieldname=="case_date") {
			if ($value!="") {
				$arrSearches[] = " ccase.`case_date` >= '" . $value . "'";
			}
			continue;
		}
		if ($fieldname=="case_throughdate") {
			if ($value!="") {
				$arrSearches[] = " ccase.`case_date` <= '" . $value . "'";
			}
			continue;
		}
		if ($fieldname=="employee") {
			$arrEmployees[] = $value;
			continue;
		}
		if ($fieldname=="special_instructions") {
			if ($value=="Y") {
				$blnSpecialInstructions = true;
			}
			continue;
		}
		//last name starts with
		if ($fieldname=="starts_with") {
			$starts_with = $value;
			continue;
		}	
		//echo $fieldname . " --> " . $value . " --> " . strpos($fieldname, "sol_") . "\r\n";
		if (strpos($fieldname, "sol_") === false) {
			if ($value!="") {
				switch ($fieldname) {
					case "full_name":
						$search_field = " app.`" . $fieldname . "` LIKE '%";
						$arrSearches[] = $search_field . $value . "%'";
						break;
					case "venue":
						$search_field = " `cvenue`.`venue_uuid` = '" . $value . "'";
						$arrSearches[] = $search_field;
						break;
					case "case_type":
						if ($value!="WCAB All" && $value!="PI All") {
							$search_field = " `" . $fieldname . "` LIKE '%";
							$arrSearches[] = $search_field . $value . "%'";
						}
						if ($value=="WCAB All") {
							$arrSearches[] = "
							(
							INSTR(`case_type`, 'WC') = 1  
							OR INSTR(`case_type`, 'Worker') = 1
							OR INSTR(`case_type`, 'W/C') = 1
							)";
						}
						if ($value=="PI All") {
							$arrSearches[] = "
							(
							`case_type` NOT LIKE 'WC%'
							AND `case_type` NOT LIKE 'Worker%'
							AND `case_type` NOT LIKE 'W/C%'
							)";
						}
						break;
					default:
						$search_field = " `" . $fieldname . "` LIKE '%";
						$arrSearches[] = $search_field . $value . "%'";
				}
				
			}
		}
		if (strpos($fieldname, "sol_") > -1) {
			if ($fieldname=="sol_startdate" && $value!="") {
				$arrSearches[] = " inj.`statute_limitation` >= '" . $value . "'";
			}
			if ($fieldname=="sol_enddate" && $value!="") {
				$arrSearches[] = " inj.`statute_limitation` <= '" . $value . "'";
			}
		}
	}
	
	
	if (count($arrSearches) == 0 && !$blnSpecialInstructions && $bodyparts == "" && $starts_with == "") {
		echo json_encode(array("search_error"=>"no search variables"));
		die();
	}
	if ($blnSpecialInstructions) {
		$sql .= " 
		AND ccase.special_instructions != 'undefined' AND ccase.special_instructions != ''";
	}
	if (count($arrSearches) > 0) {
		$sql .= " 
		AND " . implode(" AND ", $arrSearches);
	}
	if ($bodyparts!="") {
		$sql .= " 
		AND cib.bodyparts_uuid IN (" . $bodyparts . ")";
	}
	if ($starts_with!="") {
		$sql .= " 
		AND (
			app.`last_name` LIKE '" . $starts_with . "%'
			OR
			plaintiff.`company_name` LIKE '" . $starts_with . "%'
		)";
	}
	if (count($arrEmployees) > 0) {
		$employee_list = implode(",", $arrEmployees);
		//get all the initials
		if ($employee_list!="") {
			//get nicknames
			$sql_init = "SELECT nickname FROM ikase.cse_user
			WHERE user_id IN  (" . $employee_list . ")";
			$arrNicknames = array();
			try {
				$db = getConnection();
				$stmt = $db->query($sql_init);
				$nicknames = $stmt->fetchAll(PDO::FETCH_OBJ);
				$stmt->closeCursor(); $stmt = null; $db = null;
				foreach($nicknames as $nickname) {
					$arrNicknames[] = $nickname->nickname;
				}
			} catch(PDOException $e) {
				$error = array("error"=> array("text"=>$e->getMessage()));
					echo json_encode($error);
			}
			$nicklist = "'" . implode("', '", $arrNicknames) . "'";		
		
			$sql .= " 
			AND (
				ccase.supervising_attorney IN (" . $employee_list . ")
				OR ccase.attorney IN (" . $employee_list . ")
				OR ccase.worker IN (" . $employee_list . ")
				
				OR 
				ccase.supervising_attorney IN (" . $nicklist . ")
				OR ccase.attorney IN (" . $nicklist . ")
				OR ccase.worker IN (" . $nicklist . ")
			)";
		}
	}
	/*
	$sql .= "
	ORDER BY IF (TRIM(app.last_name) = '', TRIM(app.full_name), TRIM(app.last_name)), TRIM(app.first_name), ccase.case_id, inj.injury_number";
	*/
	$start_sort = "";
	if ($starts_with!="") {
		$start_sort = "app.first_name ASC,
		";
	}
	$sql .= " ORDER BY 
			TRIM(IFNULL(
				CONCAT(app.first_name,
				' ',
				app.last_name,
				' vs ',
				IFNULL(employer.`company_name`, ''),
				' - ',
				REPLACE(IF(DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL,
						'',
						DATE_FORMAT(inj.start_date, '%m/%d/%Y')),
					'00/00/0000',
					'')),
				ccase.case_name))," . $start_sort . " 
			case_id, injury_number
		";
		//$sql .= " LIMIT 0, 1000";
	
	
	$_SESSION["current_kase_query"] = $sql;
	$_SESSION["current_kase_search_term"] = "Advanced Search";
	$_SESSION["current_kase_search_terms"] = json_encode($_POST);
	
	writeQuery($sql);
	writeSearchTerms(json_encode($_POST));	
	
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$kases = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		echo json_encode($kases);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
			echo json_encode($error);
	}
	exit();
}
function writeQuery($sql) {
	//write to a text file
	$session_save_path = 'C:\\inetpub\\wwwroot\\ikase.org\\sessions\\';
	$filename = $session_save_path . 'current_query_' . $_SESSION['user_plain_id'] . '.txt';
	$somecontent = $sql;
	if ($_SERVER['REMOTE_ADDR'] == "47.156.103.17") { 
		//die($sql);
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

	//echo "Success, wrote ($somecontent) to file ($filename)";

	fclose($handle);
}
function lastKaseQuery() {
	//write to a text file
	$session_save_path = 'C:\\inetpub\\wwwroot\\ikase.org\\sessions\\';
	$filename = $session_save_path . 'current_query_' . $_SESSION['user_plain_id'] . '.txt';
	
	$sql = "";
	if (file_exists($filename)) {
		$handle = fopen($filename, "r");
		$contents = fread($handle, filesize($filename));
		fclose($handle);
	
		$sql = $contents;
	}
	return $sql;
}
function writeSearchTerms($terms) {
	//write to a text file
	$session_save_path = 'C:\\inetpub\\wwwroot\\ikase.org\\sessions\\';
	$filename = $session_save_path . 'search_terms_' . $_SESSION['user_plain_id'] . '.txt';
	$somecontent = $terms;
	
	if (!$handle = fopen($filename, 'w')) {
		 echo "Cannot open file ($filename)";
		 exit;
	}

	// Write $somecontent to our opened file.
	if (fwrite($handle, $somecontent) === FALSE) {
		echo "Cannot write to file ($filename)";
		exit;
	}

	//echo "Success, wrote ($somecontent) to file ($filename)";

	fclose($handle);
}
function addIntake() {
	session_write_close();
	
	$customer_id = $_SESSION['user_customer_id'];
	$case_uuid = uniqid("IN", false);
	$case_date = date("Y-m-d H:i:s");
	$submittedon = date("Y-m-d");
	$case_status = "Intake";
	
	$file_number = passed_var("file_number", "post");
	$case_type = passed_var("case_type", "post");
	
	$sql = "INSERT INTO cse_case (case_uuid, file_number, case_number, venue, case_date, case_type, case_status, submittedOn, customer_id) 
	VALUES (:case_uuid, :file_number, '', '', :case_date, :case_type, :case_status, :submittedon, :customer_id)";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_uuid", $case_uuid);
		$stmt->bindParam("file_number", $file_number);
		$stmt->bindParam("case_date", $case_date);
		$stmt->bindParam("submittedon", $submittedon);
		$stmt->bindParam("case_type", $case_type);
		$stmt->bindParam("case_status", $case_status);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		
		$case_id = $db->lastInsertId();
		
		$stmt = null; $db = null;
		
		$injury_uuid = uniqid("IN", false);
		//injury goes with every case
		$sql_injury = "INSERT INTO `cse_injury` (`injury_uuid`, `injury_number`, `explanation`, `customer_id`)
		VALUES('" . $injury_uuid . "', 1, '', '" . $_SESSION['user_customer_id'] . "')";
		//die($sql_injury);
		$db = getConnection();
		$stmt = $db->prepare($sql_injury);  
		$stmt->execute();
		$injury_id = $db->lastInsertId();
		$stmt = null; $db = null;
		
		//and injury relationship since we already have uuids
		$case_table_uuid = uniqid("IN", false);
		$attribute_1 = "main";
		$last_updated_date = date("Y-m-d H:i:s");
		//now we have to attach the injury to the case 
		$sql_injury = "INSERT INTO cse_case_injury (`case_injury_uuid`, `case_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $injury_uuid . "', 'main', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
		
		$db = getConnection();
		$stmt = $db->prepare($sql_injury);  
		$stmt->execute();
		$stmt = null; $db = null;
		
		trackKase("insert", $case_id);
		trackInjury("insert", $injury_id);
		
		echo json_encode(array("success"=>true, "case_id"=>$case_id, "injury_id"=>$injury_id, "case_uuid"=>$case_uuid));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
	}
	exit();
}
function addKase() {
	$request = Slim::getInstance()->request();
	
	$submittedOn = date("Y-m-d H:i:s");
	$case_date = date("Y-m-d", strtotime($_POST["case_date"]));
	$terminated_date = passed_var("terminated_date", "post");
	if ($terminated_date!="") {
		$terminated_date = date("Y-m-d", strtotime($terminated_date));
	} else {
		$terminated_date = "0000-00-00";
	}
	$filing_date = passed_var("filing_date", "post");
	if ($filing_date!="") {
		$filing_date = date("Y-m-d", strtotime($filing_date));
	} else {
		$filing_date = "0000-00-00";
	}
	$customer_id = $_SESSION['user_customer_id'];
	$case_uuid = uniqid("KS", false);
	$injury_uuid = uniqid("KI", false);
	$case_number = passed_var("case_number", "post");
	$file_number = passed_var("file_number", "post");
	$adj_number = passed_var("adj_number", "post");
	$cpointer = $adj_number;
	$case_type = passed_var("case_type", "post");
	if ($_SERVER['REMOTE_ADDR']=='71.106.134.58') {
	//	die($sql);
	}
	
	$blnWCAB = checkWCAB($case_type);
	$blnImm = ($case_type == "immigration");
	if (!$blnWCAB) {
		$cpointer = $file_number;
	}
	
	$sql = "INSERT INTO cse_case (case_uuid, case_number, file_number, cpointer, adj_number, case_date, filing_date, terminated_date, case_type, venue, case_status, case_substatus, case_subsubstatus, submittedOn, supervising_attorney, attorney, worker, customer_id, medical, td, rehab, edd, claims, case_language, `interpreter_needed`, `injury_type`, `sub_in`) 
	VALUES (:case_uuid, :case_number, '" . $file_number . "', '" . $cpointer . "', :adj_number, :case_date, :filing_date, :terminated_date, :case_type, :venue, :case_status, :case_substatus, :case_subsubstatus, :submittedOn, :supervising_attorney, :attorney, :worker, :customer_id, :medical, :td, :rehab, :edd, :claims, :case_language, :interpreter_needed, :injury_type, :sub_in)";
	
	$venue = passed_var("venue", "post");
	$case_status = passed_var("case_status", "post");
	$case_substatus = passed_var("case_substatus", "post");
	$case_subsubstatus = passed_var("case_subsubstatus", "post");
	
	$case_status = str_replace("`", "'", $case_status);
	$case_substatus = str_replace("`", "'", $case_substatus);
	$case_subsubstatus = str_replace("`", "'", $case_subsubstatus);
	
	$supervising_attorney = passed_var("supervising_attorney", "post");
	$attorney = passed_var("attorney", "post");
	$worker = passed_var("worker", "post");
	$case_language = passed_var("case_language", "post");
	$interpreter_needed = passed_var("interpreter_needed", "post");
	$sub_in = passed_var("sub_in", "post");
	$injury_type = passed_var("injury_type", "post");
	$representing = passed_var("representing", "post");
	if ($representing!="") {
		$injury_type .= "|" . $representing;
	}
	if ($interpreter_needed!="Y") {
		$interpreter_needed = "N";
	}
	if ($sub_in!="Y") {
		$sub_in = "N";
	}
	
	$arrBenefitsClaims = array("medical", "td", "rehab", "edd", "third_party_claims", "132a_claims", "serious_claims", "ada_claims", "ss_claims");
	$arrClaimsValues = array();
	$medical = "";
	$td = "";
	$rehab = "";
	$edd = "";
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		
		if (in_array($fieldname, $arrBenefitsClaims)) {
			if (strpos($fieldname, "_claims") > -1) {
				if ($value!="") {
					$arrClaimsValues[] = $value;
				}	
			}
			if ($fieldname == "medical") {
				$medical = $value;
			}
			if ($fieldname == "td") {
				$td = $value;
			}
			if ($fieldname == "rehab") {
				$rehab = $value;
			}
			if ($fieldname == "edd") {
				$edd = $value;
			}
			continue;
		}
	}

	$db = getConnection();
	try {
		//insert injury first
		$adj_field = "";
		$adj_value = "";
		if ($blnImm) {
			$adj_field = ", `adj_number`";
			$adj_value = ", '" . $case_number . "'";
		}
		$sql_injury = "INSERT INTO `cse_injury` (`injury_uuid`, `injury_number`, `explanation`, `customer_id`" . $adj_field . ")
		VALUES('" . $injury_uuid . "', 1, '', '" . $_SESSION['user_customer_id'] . "'" . $adj_value . ")";
		//die($sql_injury);
		$stmt = $db->prepare($sql_injury);  
		$stmt->execute();
		$injury_id = $db->lastInsertId();
		//and injury relationship since we already have uuids
		
		$case_table_uuid = uniqid("KA", false);
		$attribute_1 = "main";
		$last_updated_date = date("Y-m-d H:i:s");
		//now we have to attach the injury to the case 
		$sql_injury = "INSERT INTO cse_case_injury (`case_injury_uuid`, `case_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $injury_uuid . "', 'main', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
		$stmt = $db->prepare($sql_injury);  
		$stmt->execute();
		
		//now let's add the case itself
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("case_uuid", $case_uuid);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("case_number", $case_number);
		//$stmt->bindParam("file_number", $file_number);
		$stmt->bindParam("adj_number", $adj_number);
		//$stmt->bindParam("cpointer", $cpointer);
		$stmt->bindParam("case_date", $case_date);
		$stmt->bindParam("filing_date", $filing_date);
		$stmt->bindParam("terminated_date", $terminated_date);
		$stmt->bindParam("case_type", $case_type);
		$stmt->bindParam("venue", $venue);
		$stmt->bindParam("case_status", $case_status);
		$stmt->bindParam("case_substatus", $case_substatus);
		$stmt->bindParam("case_subsubstatus", $case_subsubstatus);
		$stmt->bindParam("submittedOn", $submittedOn);
		$stmt->bindParam("supervising_attorney", $supervising_attorney);
		$stmt->bindParam("attorney", $attorney);
		$stmt->bindParam("worker", $worker);
		$stmt->bindParam("case_language", $case_language);
		$stmt->bindParam("interpreter_needed", $interpreter_needed);
		$stmt->bindParam("injury_type", $injury_type);
		$stmt->bindParam("sub_in", $sub_in);
		//benefits and claims
		$stmt->bindParam("medical", $medical);
		$stmt->bindParam("td", $td);
		$stmt->bindParam("rehab", $rehab);
		$stmt->bindParam("edd", $edd);
		$claims = implode("|", $arrClaimsValues);
		$stmt->bindParam("claims",  $claims);
		

		$stmt->execute();
		$new_id = $db->lastInsertId();

		/*
		//NO LONGER DOING INCREMENT HERE, DOING IT IN postTheCustomerSettingByName
		//06/01/2018
		
		//increment the case_number_next
		$sql = "UPDATE cse_setting cset
		SET cset.setting_value = cset.setting_value + 1
		WHERE cset.setting = 'case_number_next'
		AND cset.customer_id = " . $_SESSION['user_customer_id'];
		
		//echo $sql . "\r\n";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		$db = null;
		*/
		
		//look up the next case number
		$sql = "SELECT * 
		FROM cse_setting cset
		WHERE cset.setting = 'case_number_next'
		AND cset.customer_id = " . $_SESSION['user_customer_id'];
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$setting = $stmt->fetchObject();
		
		if (!is_object($setting)) {
			$file_number_next = "";
			if ($blnWCAB) {
				if ($case_number=="" && $file_number!="") {
					$case_number = $file_number;
				}
				$case_number_next = preg_replace("/[^0-9,.]/", "", $case_number);
			} else {
				$case_number_next = preg_replace("/[^0-9,.]/", "", $file_number);
			}
			$case_number_next++;	
		} else {
			$case_number_next = $setting->setting_value;
		}
		echo json_encode(array("id"=>$new_id, "injury_id"=>$injury_id, "case_number"=>$case_number, "case_number_next"=>$case_number_next)); 
		
		//venue
		if (isset($_POST["venue"])) {	
			if ($_POST["venue"]!="") {
				//now we have to attach the venue to the case
				$case_venue_uuid = uniqid("KS", false);
				$last_updated_date = date("Y-m-d H:i:s");
				
				$sql = "INSERT INTO cse_case_venue (`case_venue_uuid`, `case_uuid`, `venue_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $case_venue_uuid  . "', '" . $case_uuid . "', '" . $venue . "', 'main', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
						
				$stmt = $db->prepare($sql);  
				$stmt->execute();
	
				$sql = "INSERT INTO cse_case_corporation (`case_corporation_uuid`, `case_uuid`, `corporation_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $case_venue_uuid  . "', '" . $case_uuid . "', '" . $venue . "', 'venue', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
				
				$stmt = $db->prepare($sql);  
				$stmt->execute();

				$table_uuid = uniqid("KS", false);
				//now save the venue as corporation for parties
				$sql = "INSERT INTO cse_corporation (`corporation_uuid`, `parent_corporation_uuid`, `company_name`, `type`, `aka`, `employee_phone`, `full_address`, `street`, `city`, `state`, `zip`, `salutation`, `customer_id`, `copying_instructions`) 
				SELECT '" . $table_uuid . "', '" . $venue . "', `venue`, 'venue', `venue_abbr`, `phone`, CONCAT(`address1`, ',', `address2`,',', `city`,' ', `zip`) full_address, CONCAT(`address1`,',', `address2`) street, `city`,'CA', `zip`, 'Your Honor', " . $_SESSION['user_customer_id'] . ", ''  
				FROM `cse_venue`
				WHERE venue_uuid = '" . $venue . "'";

				$stmt = $db->prepare($sql);  
				$stmt->execute();

				$table_name = "corporation";
				$case_table_uuid = uniqid("KA", false);
				$attribute_1 = "main";
				$last_updated_date = date("Y-m-d H:i:s");
				$sql = "INSERT INTO cse_case_" . $table_name . " (`case_" . $table_name . "_uuid`, `case_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', '" . $attribute_1 . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
						
				$stmt = $db->prepare($sql);  
				$stmt->execute();
			}
		}
		
		trackKase("insert", $new_id);
		trackInjury("insert", $injury_id);
	} catch(PDOException $e) {
		echo "ERROR:" . $sql;
		die();
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
	$db = null;
}
function viewKase() {
	$id = passed_var("id", "post");
	
	if ($_SESSION['user_customer_id']=="") {
		return false;
	}
	if (!is_numeric($_SESSION['user_customer_id'])) {
		return false;
	}
	if (!is_numeric($id)) {
		return false;
	}
	
	trackKase("view", $id);
	
	echo json_encode(array("success"=>$id)); 
}
function leaveKase() {
	$id = passed_var("id", "post");
	
	if ($_SESSION['user_customer_id']=="") {
		return false;
	}
	if (!is_numeric($_SESSION['user_customer_id'])) {
		return false;
	}
	if (!is_numeric($id)) {
		return false;
	}
	
	trackKase("no_note", $id);
	
	echo json_encode(array("success"=>$id)); 
}
function getEmployeeKaseCountByJob($user_id, $job) {
	session_write_close();
	
	$customer_id = $_SESSION["user_customer_id"];
	$user = getUserInfo($user_id);
	$nickname = $user->nickname;
	
	
	$sql = "SELECT COUNT(case_id) case_count
	FROM cse_case ccase
	WHERE 1 ";
	
	if ($job!="all") {
		$sql .= "
		AND (`" . $job . "` = :nickname OR `" . $job . "` = :user_id )";
	} else {
		$sql .= "
		AND (
			(`attorney` = :nickname OR `attorney` = :user_id )
			OR
			(`supervising_attorney` = :nickname OR `supervising_attorney` = :user_id )
			OR
			(`worker` = :nickname OR `worker` = :user_id )
		)";
	}
	$sql .= "
	AND ccase.deleted = 'N'
	AND ccase.customer_id = :customer_id";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("user_id", $user_id);
		$stmt->bindParam("nickname", $nickname);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		
		$cases = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		echo json_encode(array("success"=>true, "case_count"=>$cases->case_count));
		
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	exit();
}
function deleteAttorneyKases() {
	session_write_close();
	
	if ($_SESSION['user_role']!="admin" && $_SESSION['user_role']!="masteradmin") {
		//ikase support only
		return false;
	}
	//die(json_encode($_POST));
	
	$user_id = passed_var("user_id", "post");
	$job = passed_var("job", "post");
	
	$customer_id = $_SESSION["user_customer_id"];
	
	$user = getUserInfo($user_id);
	$nickname = $user->nickname;
	
	$sql = "SELECT case_id 
	FROM cse_case ccase 
	WHERE 1
	";
	
	if ($job!="all") {
		$sql .= "
		AND (`" . $job . "` = :nickname OR `" . $job . "` = :user_id )";
	} else {
		$sql .= "
		AND (
			(`attorney` = :nickname OR `attorney` = :user_id )
			OR
			(`supervising_attorney` = :nickname OR `supervising_attorney` = :user_id )
			OR
			(`worker` = :nickname OR `worker` = $user_id )
		)";
	}
	$sql .= "
	AND ccase.deleted = 'N'
	AND ccase.customer_id = :customer_id";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("user_id", $user_id);
		$stmt->bindParam("nickname", $nickname);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		
		$cases = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		//die(print_r($cases));
		$stmt->closeCursor(); $stmt = null; $db = null;
		$arrID = array();
		foreach($cases as $case) {
			$arrID[] = $case->case_id;
		}
		if (count($arrID) > 0) {
			deleteKase($arrID);
		}
		
		echo json_encode(array("success"=>true));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	exit();
}
function deleteKase($arrID = array()) {
	$blnBulkDelete = (count($arrID) > 0);
	if (!$blnBulkDelete) {
		$id = passed_var("id", "post");
		if (!is_numeric($id)) {
			return false;
		}
	}
	if ($_SESSION['user_customer_id']=="") {
		return false;
	}
	if (!is_numeric($_SESSION['user_customer_id'])) {
		return false;
	}
	$customer_id = $_SESSION['user_customer_id'];
	
	if (!$blnBulkDelete) {
		$sql = "UPDATE " .
			"cse_case " .
			"SET deleted = 'Y'
			WHERE case_id=:id
			AND customer_id = :customer_id";
	} else {
		$sql = "UPDATE " .
			"cse_case " .
			"SET deleted = 'Y'
			WHERE case_id IN (" . implode(",", $arrID) . ")
			AND customer_id = :customer_id";
			//die($sql);
	}
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if (!$blnBulkDelete) {
			$stmt->bindParam("id", $id);
		}
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$stmt = null; $db = null;
		if (!$blnBulkDelete) {
			echo json_encode(array("success"=>"kase " . $id . " marked as deleted"));
			$blnTracked = trackKase("delete", $id);
		} else {
			foreach($arrID as $case_id) {
				$blnTracked = trackKase("delete", $case_id);
			}
		}
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function acceptKase() {
	$case_id = passed_var("case_id", "post");
	$sql = "UPDATE cse_case
			SET case_status = 'Open'
			WHERE case_id = :case_id
			AND customer_id = :customer_id";
	try {
		$customer_id = $_SESSION['user_customer_id'];
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$stmt = null; $db = null;
		echo json_encode(array("success"=>true, "message"=>"kase " . $case_id . " accepted"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	trackKase("accept", $case_id);
}
function rejectKase() {
	$case_id = passed_var("case_id", "post");
	
	$sql = "UPDATE cse_case
			SET case_status = 'REJECTED'
			WHERE case_id = :case_id
			AND customer_id = :customer_id";
	try {
		$customer_id = $_SESSION['user_customer_id'];
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$stmt = null; $db = null;
		echo json_encode(array("success"=>true, "message"=>"kase " . $case_id . " rejected"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	trackKase("reject", $case_id);
}
function nameKase($case_id, $case_name) {
	$sql = "UPDATE cse_case
			SET case_name = :case_name
			WHERE case_id = :case_id
			AND customer_id = :customer_id";
	try {
		$customer_id = $_SESSION['user_customer_id'];
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("case_name", $case_name);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$stmt = null; $db = null;
		return json_encode(array("success"=>true, "case_name"=>$case_name, "message"=>"kase " . $case_id . " named " . $case_name));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	trackKase("update", $case_id);
}
function fileLocation() {
	session_write_close();
	$case_id = passed_var("case_id", "post");
	$file_location = passed_var("file_location", "post");
	
	if (!is_numeric($case_id)) {
		die("oh no");
	}
	
	$sql = "UPDATE cse_case
	SET file_location = :file_location
	WHERE case_id = :case_id
	AND customer_id = :customer_id";
	
	try {
		$customer_id = $_SESSION['user_customer_id'];
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("file_location", $file_location);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$stmt = null; $db = null;
		echo json_encode(array("success"=>true, "case_id"=>$case_id));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	trackKase("filelocation", $case_id);
}
function getFileLocationInfo($case_id) {
	session_write_close();
	$sql = "SELECT user_logon, time_stamp
	FROM cse_case_track
	WHERE operation = 'filelocation'
	AND case_id = :case_id
	AND customer_id = :customer_id
	ORDER BY case_track_id DESC
	LIMIT 0, 1";
	
	try {
		$customer_id = $_SESSION['user_customer_id'];
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$file_location = $stmt->fetchObject();
		
		$stmt->closeCursor(); $stmt = null; $db = null;
		echo json_encode($file_location);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function saveSSNClaim() {
	session_write_close();
	
	$claim_id = passed_var("claim_id", "post");
	$case_id = passed_var("case_id", "post");
	$claim_info = json_encode($_POST);
	$customer_id = $_SESSION['user_customer_id'];
	
	$kase = getKaseInfo($case_id);
	$case_uuid = $kase->uuid;
	
	if ($claim_id=="") {
		$sql = "INSERT INTO cse_claim (`case_uuid`, `claim_info`, `customer_id`)
		VALUES (:case_uuid, :claim_info, :customer_id)";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_uuid", $case_uuid);
		$stmt->bindParam("claim_info", $claim_info);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$claim_id = $db->lastInsertId();
		$stmt = null; $db = null;
	} else {
		$sql = "UPDATE cse_claim 
		SET `claim_info` = :claim_info
		WHERE `claim_id` = :claim_id
		AND `customer_id` = :customer_id";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("claim_id", $claim_id);
		$stmt->bindParam("claim_info", $claim_info);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$stmt = null; $db = null;
	}
	
	echo json_encode(array("success"=>true, "claim_id"=>$claim_id));
}
function renameKase() {
	session_write_close();
	$case_id = passed_var("case_id", "post");
	
	if (!is_numeric($case_id)) {
		die("oh no");
	}
	$kase = getKaseInfo($case_id);
	$case_type = $kase->case_type;
	
	$blnWCAB = checkWCAB($case_type);
	
	$arrCaseName = array();
	
	if ($blnWCAB) {
		$arrCaseName[] = $kase->full_name;
		$employer = getKasePartiesInfo($case_id, "employer");
		if (count($employer) > 0) {
			$arrCaseName[] = $employer[0]->company_name;
		}
	} else {
		$plaintiff = getKasePartiesInfo($case_id, "plaintiff");
		if (count($plaintiff) > 0) {
			$arrCaseName[] = $plaintiff[0]->company_name;
		}
		$defendant = getKasePartiesInfo($case_id, "defendant");
		if (count($defendant) > 0) {
			$arrCaseName[] = $defendant[0]->company_name;
		}
	}
	
	if (count($arrCaseName) > 0) {
		$case_name = implode(" vs " , $arrCaseName);
		//die($case_name);
		$result = nameKase($case_id, $case_name);
		die($result);
	} else {
		$case_number = $kase->case_number;
		if ($case_number=="") {
			$case_number = $kase->file_number;
		}
		die(json_encode(array("success"=>true, "case_name"=>$case_number, "message"=>"no parties")));
	}
	
}
function jetfileKase() {
	//die(print_r($_POST));
	$injury_id = passed_var("injury_id", "post");
	$injury = getInjuryInfo($injury_id);
	$case_id = $injury->main_case_id;
	$case = getKaseInfo($case_id);
	$parties = getKaseParties($case_id, "", true);
	//die($parties);
	//die(json_encode($injury));
	$form = passed_var("form", "post");
	
	//$url = 'https://www.cajetfile.com/api/filing_pack.php';
	$url = 'https://www.cajetfile.com/limapi/index.php/app/file';
	$fields = array("form"=>$form, "injury"=>json_encode($injury), "case"=>json_encode($case), "parties"=>json_encode($parties));
	//die(print_r($parties));
	//$fields = array("injury_id"=>$injury_id, "form"=>$form, "user_id"=>$_SESSION["user_plain_id"], "cus_id"=>$_SESSION["user_customer_id"]);
	//scode=DCH&radius=10&zip=91331
	$fields_string = "";
	foreach($fields as $key=>$value) { 
		$fields_string .= $key . '=' . urlencode($value) . '&'; 
	}
	rtrim($fields_string, '&');
	
	//die($url . "?" . $fields_string);
	//open connection
	$ch = curl_init();
	
	//set the url, number of POST vars, POST data
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch,CURLOPT_HEADER, false); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt ($ch, CURLOPT_COOKIEFILE, "cookies.txt");
	curl_setopt($ch, CURLOPT_POST, count($fields_string));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
	//curl_setopt($ch,CURLOPT_FOLLOWLOCATION,TRUE);
	
	//execute post
	$result = curl_exec($ch);
	if($result === false) {
		$arrError[] = "Error Number:".curl_errno($ch);
		$arrError[] = "Error String:".curl_error($ch);
		
		echo json_encode(array("error"=>$arrError));
		die();
	}
	die($result);
	
	$arrResult = json_decode($result);
	
	//die(print_r($arrResult));
	$filing_id = $arrResult->filing_id;
	
	//update our side of filing
	$sql = "INSERT INTO `cse_filing`
	(`injury_id`, `injury_uuid`, `filing_id`, `customer_id`, `user_id`, `filing_date`, `form`)
	VALUES
	(" . $injury->id . ", '" . $injury->uuid . "', " . $filing_id . ", " . $_SESSION["user_customer_id"] . ", " . $_SESSION["user_plain_id"] . ", '" . date("Y-m-d H:i:s") . "', '" . $form . "');";

	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$stmt = null; $db = null;
		//echo json_encode(array("success"=>"injury " . $injury_id . " filed", "filing_id"=>$filing_id));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
	}
	trackInjury("filed", $injury->id);
	
	//now send the stuff to cajetfile
	$kase = getKaseInfo($injury->main_case_id);
	//get the venue
	$venue = getCorporationInfo($kase->venue_id);
	//body parts
	$body_parts = getBodypartsInfo($injury->main_case_id, $injury->id);
	$arrInjuryAddress = explode(", ", $injury->full_address);
	//die(print_r($arrInjuryAddress));
	die(print_r($injury));
	
	$url = 'https://www.cajetfile.com/api/jetfile.php';
	$fields = array("filing_id"=>$filing_id, "case"=>json_encode($kase), "injury"=>json_encode($injury), "body_parts"=>json_encode($body_parts));
	$fields_string = "";
	foreach($fields as $key=>$value) { 
		$fields_string .= $key.'='.$value.'&'; 
	}
	rtrim($fields_string, '&');
	
	//open connection
	$ch = curl_init();
	
	//set the url, number of POST vars, POST data
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch,CURLOPT_HEADER, false); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt ($ch, CURLOPT_COOKIEFILE, "cookies.txt");
	curl_setopt($ch, CURLOPT_POST, count($fields_string));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
	//curl_setopt($ch,CURLOPT_FOLLOWLOCATION,TRUE);
	
	//execute post
	$result = curl_exec($ch);
	if($result === false) {
		$arrError[] = "Error Number:".curl_errno($ch);
		$arrError[] = "Error String:".curl_error($ch);
		
		echo json_encode(array("error"=>$arrError));
		die();
	}
	//$arrResult = json_decode($result);
	die($result);
}
function findA1Related($cpointer) {
	$data_source = $_SESSION['user_data_source'];
	
	$sql = "SELECT inj.injury_id
	FROM " . $data_source . ".injury ginj
	INNER JOIN ikase_" . $data_source . ".cse_case ccase
	ON ginj.CASENO = ccase.cpointer
	INNER JOIN ikase_" . $data_source . ".cse_case_injury cinj
	ON ccase.case_uuid = cinj.case_uuid
	INNER JOIN ikase_" . $data_source . ".cse_injury inj
	ON cinj.injury_uuid = inj.injury_uuid
	WHERE CASE_NO = (
	SELECT CASE_NO
	FROM " . $data_source . ".injury 
	where CASENO = " . $cpointer . "
	)
	AND CASENO != " . $cpointer;
}
function assignWorker() {
	session_write_close();
	$customer_id =  $_SESSION["user_customer_id"];
	$case_ids = passed_var("case_ids", "post");
	$worker = passed_var("worker", "post");
	
	$sql_case_ids = "'" . str_replace(",", "','", $case_ids) . "'";
	
	$sql = "UPDATE `cse_case` 
	SET `worker` = '" . $worker . "'
	WHERE case_id IN (" . $sql_case_ids . ")
	AND customer_id = '" . $customer_id . "'";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		//$stmt->bindParam("case_ids", $case_ids);
		//$stmt->bindParam("worker", $worker);
		//$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		
		//echo json_encode(array("success"=>"assigned"));
		
		$arrCaseID = explode(",", $case_ids);
		foreach($arrCaseID as $case_id) {
			trackKase("update", $case_id);
		}
		
		echo json_encode(array("success"=>true, "worker"=>$worker));
		
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function transferKases() {
	session_write_close();
	
	$ids = passed_var("ids", "post");
	$from_id = passed_var("from", "post");
	
	if ($ids=="" || $from_id=="undefined") {
		die(false);		
	}
	
	$user_id = passed_var("assignee", "post");
	$transfer_tasks = passed_var("transfer_tasks", "post");
	$transfer_events = passed_var("transfer_events", "post");
	
	$customer_id =  $_SESSION["user_customer_id"];
	
	$from_user = getUserInfo($from_id);
	$from_user_uuid = $from_user->uuid;
	
	$to_user = getUserInfo($user_id);
	$to_user_uuid = $to_user->uuid;
	
	try {
		$sql = "SELECT ccase.*
		FROM cse_case ccase
		WHERE `case_id` IN (" . $ids . ")
		AND customer_id = :customer_id";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$kases = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		foreach($kases as $kase) {	
			$kase_id = $kase->case_id;
			
			$blnTasksTransferred = false;
			if ($transfer_tasks=="Y") {
				//get all the tasks assigned to from for this kase
				$tasks = getTaskCaseInbox($kase_id, true);
			
				$arrTasks = array();
				foreach($tasks as $task) {
					if (strpos($task->assignee, $from_user->nickname) !== false || strpos($task->cc, $from_user->nickname) !== false) {
						$arrTasks[] = $task->id;
					}
				}
				if (count($arrTasks) > 0) {
					//die(print_r($arrTasks));
					$ids = implode(",", $arrTasks);
					
					$blnTasksTransferred = transferTasks($ids, $from_user->id, $to_user->id);
				}
			}
			$worker = $kase->worker;
			$arrAssignee = explode(";", $worker);
			
			//die(print_r($from_user));
			
			foreach($arrAssignee as $andex=>$ass) {
				if (strtolower($ass)==strtolower($from_user->nickname) || $ass==$from_user->id) {
					if (is_numeric($worker)) {
						$arrAssignee[$andex] = $to_user->id;
					} else {
						$arrAssignee[$andex] = $to_user->nickname;
					}
				}
			}
			$worker = implode(";", $arrAssignee);
			
			$attorney = $kase->attorney;
			$arrAssignee = explode(";", $attorney);
			//print_r($arrAssignee);
			//die(print_r($from_user));
			foreach($arrAssignee as $andex=>$ass) {
				if (strtolower($ass)==strtolower($from_user->nickname) || $ass==$from_user->id) {
					if (is_numeric($attorney)) {
						$arrAssignee[$andex] = $to_user->id;
					} else {
						$arrAssignee[$andex] = $to_user->nickname;
					}
				}
			}
			$attorney = implode(";", $arrAssignee);
			
			$supervising_attorney = $kase->supervising_attorney;
			$arrAssignee = explode(";", $supervising_attorney);
			foreach($arrAssignee as $andex=>$ass) {
				if (strtolower($ass)==strtolower($from_user->nickname) || $ass==$from_user->id) {
					if (is_numeric($supervising_attorney)) {
						$arrAssignee[$andex] = $to_user->id;
					} else {
						$arrAssignee[$andex] = $to_user->nickname;
					}
				}
			}
			$supervising_attorney = implode(";", $arrAssignee);
			
			//die($kase->worker . " - " . $worker);
			$sql = "UPDATE cse_case ccase
				SET ccase.`worker` = :worker, 
				ccase.attorney = :attorney, 
				ccase.supervising_attorney = :supervising_attorney
				WHERE ccase.`case_id` = :kase_id
				AND ccase.customer_id = :customer_id";
			//echo $sql . "\r\n";
			//print_r($arrAssignee);
			//die(print_r($arrCc));
			//echo $assignee . "\r\n";
			//echo $cc . "\r\n";
			//die();
			
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("customer_id", $customer_id);
			$stmt->bindParam("kase_id", $kase_id);
			$stmt->bindParam("worker", $worker);
			$stmt->bindParam("attorney", $attorney);
			$stmt->bindParam("supervising_attorney", $supervising_attorney);
			$stmt->execute();
			
			$stmt = null; $db = null;
			
			trackKase("transfer", $kase_id);
		}
		echo json_encode(array("success"=>"transfer completed", "ids"=>$ids));
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function unrelateKase() {
	session_write_close();
	$case_id = passed_var("case_id", "post");
	$kase = getKaseInfo($case_id);
	$case_uuid = $kase->uuid;
	$injury_id = passed_var("injury_id", "post");
	$injury = getInjuryInfo($injury_id);
	$injury_uuid = $injury->uuid;
	
	$sql = "UPDATE `cse_case_injury` 
	SET `deleted` = 'Y'
	WHERE case_uuid = :case_uuid
	AND injury_uuid = :injury_uuid
	AND customer_id = :customer_id
	AND attribute = 'related'";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("case_uuid", $case_uuid);
		$stmt->bindParam("injury_uuid", $injury_uuid);
		$stmt->bindParam("customer_id", $_SESSION["user_customer_id"]);
		$stmt->execute();
		
		echo json_encode(array("success"=>"unrelated")); 
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function relateKase() {
	session_write_close();
	$request = Slim::getInstance()->request();
	
	$case_id = passed_var("case_id", "post");
	$kase = getKaseInfo($case_id);
	$case_uuid = $kase->uuid;
	$injury_id = passed_var("injury_id", "post");
	$injury = getInjuryInfo($injury_id);
	$injury_uuid = $injury->uuid;
	
	//first check if it's already in
	$sql = "SELECT COUNT(`case_injury_id`) thecount
	FROM `cse_case_injury`
	WHERE case_uuid = :case_uuid
	AND injury_uuid = :injury_uuid
	AND customer_id = :customer_id
	AND deleted = 'N'";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("case_uuid", $case_uuid);
		$stmt->bindParam("injury_uuid", $injury_uuid);
		$stmt->bindParam("customer_id", $_SESSION["user_customer_id"]);
		$stmt->execute();
		$case_injury = $stmt->fetchObject();
		if ($case_injury->thecount == 0) {
			//insert it
			$case_table_uuid = uniqid("KA", false);
			$attribute_1 = "related";
			$last_updated_date = date("Y-m-d H:i:s");
			//now we have to attach the injury to the case 
			$sql_injury = "INSERT INTO `cse_case_injury` (`case_injury_uuid`, `case_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $injury_uuid . "', 'related', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			
			$stmt = $db->prepare($sql_injury);  
			$stmt->execute();
		}
		$stmt = null; $db = null;
		die(json_encode(array("success"=>true)));
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function updateKaseField() {
	session_write_close();
	
	$id = passed_var("id", "post");
	$fieldname = passed_var("fieldname", "post");
	$value = passed_var("value", "post");
	$customer_id = $_SESSION['user_customer_id'];
	
	if (strpos($fieldname, "date")!==false) {
		if ($value!="") {
			$value = date("Y-m-d", strtotime($value));
		} else {
			$value = "0000-00-00";
		}
	}
	
	$sql = "UPDATE cse_case 
	SET `" . $fieldname . "` = :value
	WHERE case_id = :id
	AND customer_id = :customer_id";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		
		$stmt->bindParam("id", $id);
		$stmt->bindParam("value",  $value);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>$id)); 
		
		trackKase("update", $id);
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
	exit();
}
function updateKase() {
	$request = Slim::getInstance()->request();
	
	$id = passed_var("id", "post");
	$kase = getKaseInfo($id);
	
	$case_date = date("Y-m-d", strtotime($_POST["case_date"]));
	$terminated_date = passed_var("terminated_date", "post");
	if ($terminated_date!="") {
		$terminated_date = date("Y-m-d", strtotime($terminated_date));
	} else {
		$terminated_date = "0000-00-00";
	}
	$filing_date = passed_var("filing_date", "post");
	if ($filing_date!="") {
		$filing_date = date("Y-m-d", strtotime($filing_date));
	} else {
		$filing_date = "0000-00-00";
	}
	$case_uuid = passed_var("table_uuid", "post");
	//$case_name = passed_var("case_name", "post");
	$case_number = passed_var("case_number", "post");
	$file_number = passed_var("file_number", "post");
	$adj_number = passed_var("adj_number", "post");
	$case_type = passed_var("case_type", "post");
	$venue = passed_var("venue", "post");
	$case_status = passed_var("case_status", "post");
	
	//current status IS NOT closed, but posted status IS closed
	$blnClosingKase = (strpos($kase->case_status, "Close")===false && strpos($case_status, "Close")!==false);
	
	$case_substatus = passed_var("case_substatus", "post");
	$case_subsubstatus = passed_var("case_subsubstatus", "post");
	
	$case_status = str_replace("`", "'", $case_status);
	$case_substatus = str_replace("`", "'", $case_substatus);
	$case_subsubstatus = str_replace("`", "'", $case_subsubstatus);
	
	//$rating = passed_var("rating", "post");
	$supervising_attorney = passed_var("supervising_attorney", "post");
	$attorney = passed_var("attorney", "post");
	$worker = passed_var("worker", "post");
	$case_language = passed_var("case_language", "post");
	$interpreter_needed = passed_var("interpreter_needed", "post");
	$special_instructions = passed_var("special_instructions", "post");
	$case_note = passed_var("case_note", "post");
	$suit = passed_var("suit", "post");
	$jurisdiction = passed_var("jurisdiction", "post");
	$sub_in = passed_var("sub_in", "post");
	
	$sub_in_date = passed_var("sub_in_date", "post");
	$sub_out_date = passed_var("sub_out_date", "post");
	
	//put it all in json
	$arrCaseInfo = array(
		"suit"			=> $suit, 
		"jurisdiction" 	=> $jurisdiction, 
		"case_note" 	=> addslashes($case_note),
		"sub_in_date" 	=> $sub_in_date, 
		"sub_out_date" 	=> $sub_out_date, 
	);
	
	$case_description = json_encode($arrCaseInfo);
	//die($case_description);
	$injury_type = passed_var("injury_type", "post");
	$representing = passed_var("representing", "post");
	if ($representing!="") {
		$injury_type .= "|" . $representing;
	}
	if ($interpreter_needed!="Y") {
		$interpreter_needed = "N";
	}
	if ($sub_in!="Y") {
		$sub_in = "N";
	}
	$arrBenefitsClaims = array("medical", "td", "rehab", "edd", "third_party_claims", "132a_claims", "serious_claims", "ada_claims", "ss_claims");
	$arrClaimsValues = array();
	$medical = "";
	$td = "";
	$rehab = "";
	$edd = "";
	$blnThirdParty = false;
	
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		
		if (in_array($fieldname, $arrBenefitsClaims)) {
			if (strpos($fieldname, "_claims") > -1) {
				if ($value!="") {
					$choice_fieldname = str_replace("_claims", "InHouseChoice", $fieldname);
					
					//there could be a matching choice for inhouse
					if (isset($_POST[$choice_fieldname])) {
						$value .= "~" .  $_POST[$choice_fieldname];
					}
					$arrClaimsValues[] = $value;
				}	
			}
			if ($fieldname == "medical") {
				$medical = $value;
			}
			if ($fieldname == "td") {
				$td = $value;
			}
			if ($fieldname == "rehab") {
				$rehab = $value;
			}
			if ($fieldname == "edd") {
				$edd = $value;
			}
			if ($fieldname == "third_party_claims") {
				$blnThirdParty = true;
			}
			continue;
		}
	}
	$claims = implode("|", $arrClaimsValues);
	//case_name = :case_name,	
	$sql = "UPDATE cse_case 
	SET case_number = :case_number, 
	adj_number = :adj_number, 
	file_number = :file_number, 
	case_date =  :case_date,  
	filing_date = :filing_date,
	terminated_date = :terminated_date,
	case_type =  :case_type,
	`venue` =  :venue, 
	case_status = :case_status,
	injury_type = :injury_type,
	case_substatus = :case_substatus,
	case_subsubstatus = :case_subsubstatus,
	supervising_attorney = :supervising_attorney,
	attorney =  :attorney,
	`worker` =  :worker,
	`case_language` = :case_language,
	`special_instructions` = :special_instructions,
	`case_description` = :case_description,
	`interpreter_needed` = :interpreter_needed,
	`medical` =  :medical,
	`td` =  :td,
	`rehab` =  :rehab,
	`edd` =  :edd,
	`claims` = :claims,
	`sub_in` = '" .  $sub_in . "'
	WHERE case_id = :id
	AND customer_id = " . $_SESSION['user_customer_id'];
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		
		$stmt->bindParam("id", $id);
		$stmt->bindParam("case_number", $case_number);
		//$stmt->bindParam("case_name", $case_name);
		$stmt->bindParam("adj_number", $adj_number);
		$stmt->bindParam("file_number", $file_number);
		$stmt->bindParam("case_date", $case_date);
		$stmt->bindParam("filing_date", $filing_date);
		$stmt->bindParam("terminated_date", $terminated_date);
		//$stmt->bindParam("rating", $rating);
		$stmt->bindParam("case_type", $case_type);
		$stmt->bindParam("venue", $venue);
		$stmt->bindParam("case_status", $case_status);
		$stmt->bindParam("case_substatus", $case_substatus);
		$stmt->bindParam("case_subsubstatus", $case_subsubstatus);
		$stmt->bindParam("supervising_attorney", $supervising_attorney);
		$stmt->bindParam("attorney", $attorney);
		$stmt->bindParam("worker", $worker);
		$stmt->bindParam("case_language", $case_language);
		$stmt->bindParam("special_instructions", $special_instructions);
		$stmt->bindParam("case_description", $case_description);
		$stmt->bindParam("interpreter_needed", $interpreter_needed);
		$stmt->bindParam("injury_type", $injury_type);
		$stmt->bindParam("medical", $medical);
		$stmt->bindParam("td", $td);
		$stmt->bindParam("rehab", $rehab);
		$stmt->bindParam("edd", $edd);
		$stmt->bindParam("claims",  $claims);
		//$stmt->bindParam("sub_in",  $sub_in);
		$stmt->execute();
		
		$stmt = null; $db = null;
		
		if ($case_type=="immigration") {
			//update the adj
			$sql = "UPDATE cse_injury ci, cse_case ccase, cse_case_injury cci
			SET ci.adj_number = :case_number
			WHERE ci.injury_uuid = cci.injury_uuid
			AND cci.case_uuid = ccase.case_uuid
			AND ccase.case_id = :case_id
			AND ccase.customer_id = " . $_SESSION['user_customer_id'];
			
			//die($case_number . "<br />" . $id . "<br />" . $sql);
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			
			$stmt->bindParam("case_number", $case_number);
			$stmt->bindParam("case_id",  $id);
			$stmt->execute();
			
			$stmt = null; $db = null;
		}
		$blnClosingNotification = false;
		if ($blnClosingKase) {
		//third party attorney
			if ($blnThirdParty) {
				//send interoffice to alert case workers that there is a third party
				$blnClosingNotification = true;
			}
		}
		echo json_encode(array("success"=>$id, "closing"=>$blnClosingNotification)); 
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
	
	//venue
	if (isset($_POST["venue"])) {
		if ($_POST["venue"]!="") {
			$venue = $_POST["venue"];
			if($venue != $kase->venue_uuid) {
				//clear out any previous venue
				$sql = "UPDATE cse_case_venue 
				SET deleted = 'Y'
				WHERE case_uuid = '" . $case_uuid . "'";
				
				$db = getConnection();
				$stmt = $db->prepare($sql);
				$stmt->execute();
				
				//clear out any previous venue
				$sql = "UPDATE cse_case_corporation
				SET deleted = 'Y'
				WHERE case_uuid = '" . $case_uuid . "'
				AND attribute = 'venue'";
				
				$db = getConnection();
				$stmt = $db->prepare($sql);
				$stmt->execute();
								
				//now we have to attach the venue to the case
				$case_venue_uuid = uniqid("KS", false);
				$last_updated_date = date("Y-m-d H:i:s");
				
				$sql = "INSERT INTO cse_case_venue (`case_venue_uuid`, `case_uuid`, `venue_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $case_venue_uuid  . "', '" . $case_uuid . "', '" . passed_var("venue", "post") . "', 'main', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
				
				try {
					$db = getConnection();
					$stmt = $db->prepare($sql);  
				
					$stmt->execute();
				} catch(PDOException $e) {
					echo '{"error":{"text":'. $e->getMessage() .'}}'; 
				}
				
				$table_uuid = uniqid("KS", false);
				//now save the venue as corporation for parties
				$sql = "INSERT INTO cse_corporation (`corporation_uuid`, `parent_corporation_uuid`, `company_name`, `type`, `aka`, `employee_phone`, `full_address`, `street`, `city`, `state`, `zip`, `salutation`, `copying_instructions`, `customer_id`) 
				SELECT '" . $table_uuid . "', '" . $venue . "', `venue`, 'venue', `venue_abbr`, `phone`, CONCAT(`address1`, ',', `address2`,',', `city`,' ', `zip`) full_address, CONCAT(`address1`,',', `address2`) street, `city`,'CA', `zip`, 'Your Honor', ''," . $_SESSION['user_customer_id'] . " 
				FROM `cse_venue`
				WHERE venue_uuid = '" . $venue . "'";
				try {
					$db = getConnection();
					$stmt = $db->prepare($sql);  
					$stmt->execute();
					$stmt = null; $db = null;
				} catch(PDOException $e) {
					echo '{"error":{"text":'. $e->getMessage() .'}}'; 
				}
				$table_name = "corporation";
				$case_table_uuid = uniqid("KA", false);
				$attribute_1 = "main";
				$last_updated_date = date("Y-m-d H:i:s");
				$sql = "INSERT INTO cse_case_" . $table_name . " (`case_" . $table_name . "_uuid`, `case_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', 'venue', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
						
				try { 	
					$db = getConnection();
					$stmt = $db->prepare($sql);  
					$stmt->execute();
					$new_id = $db->lastInsertId();
					$stmt = null; $db = null;
				} catch(PDOException $e) {
					echo '{"error":{"text":'. $e->getMessage() .'}}'; 
				}
			}
		//}
		}
	}
	
	trackKase("update", $id);
	
	exit();
}
function trackKase($operation, $case_id) {
	$sql = "INSERT INTO cse_case_track (`user_uuid`, `user_logon`, `operation`, `case_id`, `case_uuid`, `case_number`, `file_number`, `adj_number`, `case_date`, `case_type`, `rating`, `venue`, `case_status`, `case_substatus`, `submittedOn`, `attorney`, `worker`, `case_language`, `interpreter_needed`, `deleted`, `customer_id`)
	SELECT '" . $_SESSION['user_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $operation . "', `case_id`, `case_uuid`, `case_number`, `file_number`, `adj_number`, `case_date`, `case_type`, `rating`, `venue`, `case_status`, `case_substatus`, `submittedOn`, `attorney`, `worker`, `case_language`, `interpreter_needed`, `deleted`, `customer_id` 
	FROM cse_case
	WHERE 1
	AND case_id = " . $case_id . "
	AND customer_id = " . $_SESSION['user_customer_id'] . "
	LIMIT 0, 1";

	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		if ($operation!="view") {
			//new the case_uuid
			$kase = getKaseInfo($case_id);
			$activity_category = "Case";
			if ($operation=="accept") {
				$activity_category = "Intake Accepted";
			}
			if ($operation=="accept") {
				$activity_category = "Intake Rejected";
			}
			$case_type = "";
			switch($operation){
				case "accept":
				case "reject":
				case "insert":
					$operation .= "ed";
					$case_type = $kase->case_type . " ";
					break;
				case "transfer":
					$operation .= "red";
					break;
				case "update":
				case "delete":
					$operation .= "d";
					break;
			}
			$new_id = $db->lastInsertId();
			//$thelink = "<a href='#kases/" . $kase->id . "'>" . $kase->number . "</a>";
			
			$activity = $case_type . "Kase was " . $operation . "  by " . $_SESSION['user_name'];
			$activity .= ".  Status: " . $kase->case_status;
			recordActivity($operation, $activity, $kase->uuid, $new_id, $activity_category);
		}
		
		$stmt = null; $db = null;
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function kaseWorkerSummary() {
	session_write_close();
	$customer_id = $_SESSION["user_customer_id"];
	
	$sql = "
	SELECT user_id, nickname, user_name, SUM(case_count) kase_count
	FROM (
		SELECT 'worker' job, IF(usr_byid.user_name IS NULL, IFNULL(usr_nick.user_name, ''), usr_byid.user_name) user_name, 
		IF(usr_byid.user_id IS NULL, IFNULL(usr_nick.user_id, '-1'), usr_byid.user_id) user_id, 
		IF(usr_byid.nickname IS NULL, IFNULL(usr_nick.nickname, ''), usr_byid.nickname) nickname, 
		COUNT(ccase.case_id) case_count
		
		FROM cse_case ccase
		
		LEFT OUTER JOIN ikase.cse_user usr_byid
		ON ccase.worker = usr_byid.user_id AND usr_byid.deleted = 'N'
		
		LEFT OUTER JOIN ikase.cse_user usr_nick
		ON ccase.worker = usr_nick.nickname AND usr_nick.customer_id = :customer_id AND usr_nick.deleted = 'N'
		
		WHERE 1
		AND ccase.worker != '' AND ccase.deleted = 'N'
		AND ccase.case_status NOT LIKE '%close%' AND ccase.case_status NOT LIKE 'CL-%' AND ccase.case_status NOT LIKE 'CLOSED%' AND ccase.case_status != 'DROPPED' AND ccase.case_status != 'REJECTED'
		AND ccase.customer_id = :customer_id
		GROUP BY IF(usr_byid.user_name IS NULL, IFNULL(usr_nick.user_name, ''), usr_byid.user_name)
		
		UNION
		
		SELECT 'attorney' job, IF(usr_byid.user_name IS NULL, IFNULL(usr_nick.user_name, ''), usr_byid.user_name) user_name, 
		IF(usr_byid.user_id IS NULL, IFNULL(usr_nick.user_id, '-1'), usr_byid.user_id) user_id, 
		IF(usr_byid.nickname IS NULL, IFNULL(usr_nick.nickname, ''), usr_byid.nickname) nickname,  
		COUNT(ccase.case_id) case_count
		
		FROM cse_case ccase
		
		LEFT OUTER JOIN ikase.cse_user usr_byid
		ON ccase.attorney = usr_byid.user_id AND usr_byid.deleted = 'N'
		
		LEFT OUTER JOIN ikase.cse_user usr_nick
		ON ccase.attorney = usr_nick.nickname AND usr_nick.customer_id = :customer_id AND usr_nick.deleted = 'N'
		
		WHERE 1
		AND ccase.attorney != '' AND ccase.deleted = 'N'
		AND ccase.case_status NOT LIKE '%close%' AND ccase.case_status NOT LIKE 'CL-%' AND ccase.case_status NOT LIKE 'CLOSED%' AND ccase.case_status != 'DROPPED' AND ccase.case_status != 'REJECTED'
		AND ccase.customer_id = :customer_id
		GROUP BY IF(usr_byid.user_name IS NULL, IFNULL(usr_nick.user_name, ''), usr_byid.user_name)
		
		UNION
		
		SELECT 'supervising_attorney' job, IF(usr_byid.user_name IS NULL, IFNULL(usr_nick.user_name, ''), usr_byid.user_name) user_name, 
		IF(usr_byid.user_id IS NULL, IFNULL(usr_nick.user_id, '-1'), usr_byid.user_id) user_id, 
		IF(usr_byid.nickname IS NULL, IFNULL(usr_nick.nickname, ''), usr_byid.nickname) nickname,  
		COUNT(ccase.case_id) case_count
		
		FROM cse_case ccase
		
		LEFT OUTER JOIN ikase.cse_user usr_byid
		ON ccase.supervising_attorney = usr_byid.user_id AND usr_byid.deleted = 'N'
		
		LEFT OUTER JOIN ikase.cse_user usr_nick
		ON ccase.supervising_attorney = usr_nick.nickname AND usr_nick.customer_id = :customer_id AND usr_nick.deleted = 'N'
		
		WHERE 1
		AND ccase.attorney != '' AND ccase.deleted = 'N'
		AND ccase.case_status NOT LIKE '%close%' AND ccase.case_status NOT LIKE 'CL-%' AND ccase.case_status NOT LIKE 'CLOSED%' AND ccase.case_status != 'DROPPED' AND ccase.case_status != 'REJECTED'
		AND ccase.customer_id = :customer_id
		GROUP BY IF(usr_byid.user_name IS NULL, IFNULL(usr_nick.user_name, ''), usr_byid.user_name)
	) case_workers
	
	WHERE user_name != ''
	GROUP BY user_name";
	//die($sql);  
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$summary = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		echo json_encode($summary);
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function getCaseIDsbyLastNameLetter($letter) {
	session_write_close();
	$customer_id = $_SESSION["user_customer_id"];
	
	$sql = "SELECT DISTINCT case_id FROM (
		SELECT wcabs.*
		FROM(
			SELECT  'wcab' case_type, TRIM(app.last_name) last_name, TRIM(app.first_name) first_name, ccase.case_id
			FROM cse_case ccase
	
			INNER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N'
			INNER JOIN cse_person app ON ccapp.person_uuid = app.person_uuid
			WHERE app.last_name != ''
			AND INSTR(app.last_name, 'No Name') = 0
			AND ccase.customer_id = :customer_id
		) wcabs
	
		UNION
		SELECT pis.* 
		FROM (
			SELECT  'pi' case_type, TRIM(SUBSTR(plaintiff.company_name, INSTR(plaintiff.company_name, ' '))) last_name,
			TRIM(REPLACE(plaintiff.company_name, SUBSTR(plaintiff.company_name, INSTR(plaintiff.company_name, ' ')), '')) first_name,
			ccase.case_id
			FROM cse_case ccase
			INNER JOIN `cse_case_corporation` pcorp
			ON (ccase.case_uuid = pcorp.case_uuid AND pcorp.attribute = 'plaintiff' AND pcorp.deleted = 'N')
			INNER JOIN `cse_corporation` plaintiff
			ON pcorp.corporation_uuid = plaintiff.corporation_uuid
			WHERE 1
			AND ccase.customer_id = :customer_id
			
		) pis
	) cases
	WHERE SUBSTR(REPLACE(cases.last_name, '(', ''), 1, 1) = :letter";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("letter", $letter);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$kases = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		$stmt->closeCursor(); $stmt = null; $db = null;
		$arrID = array();
		foreach($kases as $kase) {
			$arrID[] = $kase->case_id;
		}
		echo implode(",", $arrID);
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function getCaseCountByLastNameLetter() {
	session_write_close();
	
	$customer_id = $_SESSION["user_customer_id"];
	$sql = "SELECT SUBSTR(REPLACE(cases.last_name, '(', ''), 1, 1) first_letter, COUNT(DISTINCT cases.case_id) case_count
	FROM (
		SELECT wcabs.*
		FROM(
			SELECT  'wcab' case_type, TRIM(app.last_name) last_name, TRIM(app.first_name) first_name, ccase.case_id
			FROM cse_case ccase
	
			INNER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N'
			INNER JOIN  ";
			
	if (($_SESSION['user_customer_id']==1033)) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "cse_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			WHERE app.last_name != ''
			AND ccase.deleted = 'N'
			AND ccase.customer_id = :customer_id
			AND INSTR(app.last_name, 'No Name') = 0
			AND INSTR(ccase.case_status, 'Closed') = 0 AND INSTR(ccase.case_status, 'CL-') = 0
			AND INSTR(ccase.case_status, 'Dropped') = 0
			AND INSTR(ccase.case_status, 'REJECTED') = 0
			AND INSTR(ccase.case_status, 'Intake') = 0
			#ORDER BY TRIM(REPLACE(app.last_name, '(', ''))
		) wcabs
	
		UNION
		SELECT pis.* 
		FROM (
			SELECT  'pi' case_type, TRIM(SUBSTR(plaintiff.company_name, INSTR(plaintiff.company_name, ' '))) last_name,
			TRIM(REPLACE(plaintiff.company_name, SUBSTR(plaintiff.company_name, INSTR(plaintiff.company_name, ' ')), '')) first_name,
			ccase.case_id
			FROM cse_case ccase
			INNER JOIN `cse_case_corporation` pcorp
			ON (ccase.case_uuid = pcorp.case_uuid AND pcorp.attribute = 'plaintiff' AND pcorp.deleted = 'N')
			INNER JOIN `cse_corporation` plaintiff
			ON pcorp.corporation_uuid = plaintiff.corporation_uuid
			WHERE 1
			AND ccase.deleted = 'N'
			AND ccase.customer_id = :customer_id
			AND INSTR(ccase.case_status, 'Closed') = 0 AND INSTR(ccase.case_status, 'CL-') = 0
			AND INSTR(ccase.case_status, 'Dropped') = 0
			AND INSTR(ccase.case_status, 'REJECTED') = 0
			AND INSTR(ccase.case_status, 'Intake') = 0
			#ORDER BY SUBSTR(plaintiff.company_name, INSTR(plaintiff.company_name, ' '))
		) pis
	) cases
	
	GROUP BY SUBSTR(REPLACE(cases.last_name, '(', ''), 1, 1)";

	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$alphas = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		echo json_encode($alphas);
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
?>	

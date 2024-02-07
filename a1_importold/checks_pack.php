<?php
$app->get('/checks/kases/:case_id', authorize('user'), 'getChecks');
$app->get('/checks/kinvoice/:kinvoice_id', authorize('user'), 'getKinvoiceChecks');
$app->get('/checks/account/:account_id', authorize('user'), 'getAccountChecks');
$app->get('/checks/accountbyledger/:account_id/:ledger', authorize('user'), 'getAccountChecksByLedger');
$app->get('/checks/kaseaccount/:case_id/:account_id', authorize('user'), 'getKaseAccountChecks');
$app->get('/checks/in/:case_id', authorize('user'), 'getPayments');
$app->get('/checks/out/:case_id', authorize('user'), 'getDisbursments');
$app->get('/checks/settlement/:case_id/:recipient', authorize('user'), 'getSettlementChecks');
$app->get('/checks/fee/:fee_id', authorize('user'), 'getFeeChecks');
$app->get('/checks/printed', authorize('user'), 'getPrinteds');
$app->get('/checks/unprinted', authorize('user'), 'getUnprinteds');
$app->get('/checks/cleared', authorize('user'), 'getCleareds');
$app->get('/checks/uncleared', authorize('user'), 'getUncleareds');
$app->get('/checks/clearedbyledger/:ledger/:account_id', authorize('user'), 'getClearedsByLedger');
$app->get('/checks/unclearedbyledger/:ledger/:account_id', authorize('user'), 'getUnclearedsByLedger');


$app->get('/checks/clearedtotals', authorize('user'), 'getLedgerTotals');
$app->get('/checks/unclearedtotals', authorize('user'), 'getUnclearedLedgerTotals');
$app->get('/account/clearedtotals/:account_id', authorize('user'), 'getAccountLedgerTotals');
$app->get('/account/unclearedtotals/:account_id', authorize('user'), 'getAccountUnclearedLedgerTotals');

$app->get('/checks/categories', authorize('user'), 'getCheckCategories');

$app->get('/checks/:id', authorize('user'), 'getCheck');
$app->get('/checkbycase/:id/:case_id', authorize('user'), 'getCheckByCase');

$app->post('/check/add', authorize('user'), 'addCheck');
$app->post('/check/update', authorize('user'), 'updateCheck');
$app->post('/check/delete', authorize('user'), 'deleteCheck');
$app->post('/check/void', authorize('user'), 'voidCheck');
$app->post('/check/clear', authorize('user'), 'clearCheck');
$app->post('/check/unclear', authorize('user'), 'unclearCheck');
$app->post('/printcheck', authorize('user'), 'printCheck');


$app->post('/cost_type/add', authorize('user'), 'saveCostTypes');
$app->post('/cost_type/update', authorize('user'), 'updateCostTypes');

$app->get('/checkrequest/:id', authorize('user'), 'getCheckRequest');
$app->get('/checkrequests', authorize('user'), 'getCheckRequests');
$app->get('/checkrequests/mine/:approval', authorize('user'), 'getMyCheckRequests');
$app->get('/checkrequests/approved', authorize('user'), 'getApprovedCheckRequests');
$app->get('/checkrequests/denied', authorize('user'), 'getRejectedCheckRequests');
$app->get('/checkrequests/kases/:case_id', authorize('user'), 'getKaseCheckRequests');
$app->get('/checkrequests/all', authorize('user'), 'getAllCheckRequests');

$app->get('/accountrequests/:account/:approved', authorize('user'), 'getCheckRequestsByAccount');

$app->get('/checkrequests/categories', authorize('user'), 'getCheckRequestCategories');
$app->post('/checkrequest_type/add', authorize('user'), 'saveCheckRequestTypes');
$app->post('/checkrequest_type/update', authorize('user'), 'updateCheckRequestTypes');

$app->post('/checkrequest/update', authorize('user'), 'updateCheckRequest');
$app->post('/checkrequest/add', authorize('user'), 'addCheckRequest');
$app->post('/checkrequest/approve', authorize('user'), 'approveCheckRequest');
$app->post('/checkrequest/reject', authorize('user'), 'rejectCheckRequest');
$app->post('/checkrequest/void', authorize('user'), 'voidCheckRequest');
$app->post('/checkrequest/delete', authorize('user'), 'deleteCheckRequest');

$app->post('/checkrequest/detach', authorize('user'), 'detachCheckRequest');


function getCheckByCase($id, $case_id) {
	getCheck($id, $case_id);
}
function getCheck($id, $case_id = "") {
	session_write_close();
	//return a row if id is valid
	$sql = "SELECT `check`.*, `check`.`check_id` `id` , `check`.`check_uuid` `uuid`, 
	
		IF(`corp`.`company_name` IS NULL, `pers`.`full_name`, `corp`.`company_name`) payable_full_name,
		IF (`corp`.`company_name` IS NOT NULL, IF(`corp`.`type` = 'recipient', 'records', 'standard'), 'standard') payable_type,
        IF(`corp`.`company_name` IS NULL, 'person', 'corporation') payable_table,
		IF(`corp`.`company_name` IS NULL, pers.person_id, corp.corporation_id) payable_id,
		IFNULL(`thefrom`.corporation_id, -1) from_id,
		IFNULL(`prints`.print_date, '') print_date, IFNULL(`prints`.print_by, '') print_by,
		ccase.case_id,
		IFNULL(acc.account_id, -1) account_id, IFNULL(acc.account_name, '') account_name, IFNULL(acc.account_balance, '') account_balance,
		IFNULL(acc.account_type, '') account_type,
		IFNULL(creq.payable_type, '') request_payable_type
	
		FROM `cse_check` `check` 
		
		LEFT OUTER JOIN cse_case_check ccheck
		ON `check`.check_uuid = ccheck.check_uuid
		LEFT OUTER JOIN cse_case ccase
		ON ccheck.case_uuid = ccase.case_uuid
		
		LEFT OUTER JOIN cse_account_check cac
		ON `check`.check_uuid = cac.check_uuid
		LEFT OUTER JOIN cse_account acc
		ON cac.account_uuid = acc.account_uuid
		
		LEFT OUTER JOIN cse_checkrequest creq
		ON `check`.check_uuid = creq.check_uuid
					
		LEFT OUTER JOIN cse_corporation_check ccc
		ON `check`.check_uuid = ccc.check_uuid AND ccc.deleted = 'N' AND ccc.attribute != 'from'
		LEFT OUTER JOIN cse_corporation corp
		ON ccc.corporation_uuid = corp.corporation_uuid
		
		LEFT OUTER JOIN cse_corporation_check ccf
		ON `check`.check_uuid = ccf.check_uuid AND ccf.deleted = 'N' AND ccf.attribute = 'from'
		LEFT OUTER JOIN cse_corporation thefrom
		ON ccf.corporation_uuid = thefrom.corporation_uuid
		
		LEFT OUTER JOIN cse_person_check cpc
		ON `check`.check_uuid = cpc.check_uuid AND cpc.deleted = 'N'
		LEFT OUTER JOIN cse_person pers
		ON cpc.person_uuid = pers.person_uuid
		
		LEFT OUTER JOIN (
			SELECT cct.check_uuid, cct.`time_stamp` print_date, cct.`user_logon` print_by, last_track.track_count
			FROM cse_check_track cct
			INNER JOIN (
				SELECT `cct`.`check_id`, MAX(check_track_id) max_track_id, COUNT(check_track_id) track_count
				FROM cse_check_track cct
				WHERE operation = 'printed'
				GROUP BY `cct`.`check_id`
			) last_track
			ON cct.check_track_id = last_track.max_track_id
			WHERE operation = 'printed'
		)	`prints`
		ON `check`.check_uuid = `prints`.check_uuid
		
		WHERE `check`.`check_id` = :id
		AND `check`.`customer_id` = " . $_SESSION['user_customer_id'] . "
		AND `check`.deleted = 'N'";
		
		if ($case_id!="") {
			$sql .= "
			AND ccase.case_id = :case_id";
		}
		//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		if ($case_id!="") {
			$stmt->bindParam("case_id", $case_id);
		}
		$stmt->execute();
		$check = $stmt->fetchObject();
		$db = null;
		//die($sql);

        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($check);
        } else {
            echo $_GET['callback'] . '(' . json_encode($check) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getCheckInfo($id) {
	session_write_close();
	$customer_id = $_SESSION['user_customer_id'];
	//return a row if id is valid
	$sql = "SELECT `check`.*, `check`.`check_id` `id`, `check`.`check_uuid` `uuid`, 
	
		IF(`corp`.`company_name` IS NULL, `pers`.`full_name`, `corp`.`company_name`) payable_full_name,
		IF (`corp`.`company_name` IS NOT NULL, IF(`corp`.`type` = 'recipient', 'records', 'standard'), 'standard') payable_type,
        IF(`corp`.`company_name` IS NULL, 'person', 'corporation') payable_table,
		IF(`corp`.`company_name` IS NULL, pers.person_id, corp.corporation_id) payable_id
		
		FROM `cse_check` `check` 
		
		
		LEFT OUTER JOIN cse_corporation_check ccc
		ON `check`.check_uuid = ccc.check_uuid AND ccc.deleted = 'N'
		LEFT OUTER JOIN cse_corporation corp
		ON ccc.corporation_uuid = corp.corporation_uuid
		
		LEFT OUTER JOIN cse_person_check cpc
		ON `check`.check_uuid = cpc.check_uuid AND cpc.deleted = 'N'
		LEFT OUTER JOIN cse_person pers
		ON cpc.person_uuid = pers.person_uuid
		
		WHERE `check`.`check_id` = '" . $id . "'
		AND `check`.`customer_id` = $customer_id";

	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		/*$stmt->bindParam("id", $id);
		$stmt->bindParam("customer_id", $customer_id);
		*/
		$stmt->execute();
		$check = $stmt->fetchObject();
		//echo $id . "<br />";
		//die($sql);
		$stmt->closeCursor(); $stmt = null; $db = null;
		//die(print_r($check));
        return $check;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKaseAccountChecks($case_id, $account_id) {
	getChecks($case_id, "", "", $account_id);
}
function getAccountChecksByLedger($account_id, $ledger) {
	getAccountChecks($account_id, $ledger);
}
function getAccountChecks($account_id, $ledger = "") {
	getChecks("", $ledger, "", $account_id);
}
function getKinvoiceChecks($kinvoice_id) {
	getChecks("", "", $kinvoice_id);
}
function getDisbursments($case_id) {
	getChecks($case_id, "OUT", "");
}
function getPayments($case_id) {
	getChecks($case_id, "IN", "");
}
function getUnclearedsByLedger($ledger, $account_id = "") {
	if ($account_id=="_") {
		$account_id = "";
	}
	getUncleareds($ledger, $account_id);
}
function getClearedsByLedger($ledger, $account_id = "") {
	if ($account_id=="_") {
		$account_id = "";
	}
	getCleareds($ledger, $account_id);
}
function getUncleareds($ledger = "", $account_id = "") {
	getChecks("", $ledger, "", $account_id,true);
}
function getCleareds($ledger = "", $account_id = "") {
	getChecks("", $ledger, "", $account_id, false, "C");
}
function getUnprinteds($ledger = "", $account_id = "") {
	getChecks("", $ledger, "", $account_id, false, "unprinted");
}
function getPrinteds($ledger = "", $account_id = "") {
	getChecks("", $ledger, "", $account_id, false, "printed");
}

function getChecks($case_id, $ledger = "", $kinvoice_id = "", $account_id = "", $blnUncleared = false, $check_status = "") {
	session_write_close();
	
	$case_join = "INNER";
	if ($case_id=="") {
		$case_join = "LEFT OUTER";
	}
    $sql = "SELECT DISTINCT `check`.*, `check`.check_id id , `check`.check_uuid uuid, ccase.case_id, ccase.case_uuid, ccase.file_number, ccase.case_number, ccase.case_name ";
			
	if (($ledger == "IN" || $ledger == "") && $kinvoice_id!="") {
		$sql .= ", 
		ki.kinvoice_id, ki.kinvoice_number, corp.corporation_id, corp.company_name";
	}
	$account_join = "INNER";
	if ($account_id!="" && $account_id!="-1") {
		$account = getBankAccountInfo($account_id);
		
		if ($account->account_type=="operating") {
			$account_join = "LEFT OUTER";
			//only listing checks against cases, ledger OUT
			$case_join = "INNER";
		}
		
		$sql .= ", 
		acc.account_id, acc.account_name, acc.account_balance,
		IFNULL(creq.payable_type, '') request_payable_type";
	}
$sql .= ", 	
	IFNULL(ccd.check_attachments, '') attachments, 
	
	IFNULL(IF(`corp`.`company_name` IS NULL, `pers`.`full_name`, `corp`.`company_name`), '') payable_full_name,
	IF (`corp`.`company_name` IS NOT NULL, IF(`corp`.`type` = 'recipient', 'records', 'standard'), 'standard') payable_type,
	IF(`corp`.`company_name` IS NULL, 'person', 'corporation') payable_table,
	IF(`corp`.`company_name` IS NULL, pers.person_id, corp.corporation_id) payable_id,
	IFNULL(`thefrom`.corporation_id, -1) from_id,
	IFNULL(`thefrom`.company_name, '') thefrom,
	IFNULL(`prints`.print_date, '') print_date, IFNULL(`prints`.print_by, '') print_by
	";
	$sql_select = $sql;
	
	$sql .= "
	FROM `cse_check` `check`
	INNER JOIN (
		SELECT check_id 
		FROM cse_check_track cct
		WHERE operation = 'insert'
	) cct
	ON `check`.check_id = cct.check_id
	
	LEFT OUTER JOIN cse_corporation_check ccc
	ON `check`.check_uuid = ccc.check_uuid AND ccc.deleted = 'N' AND ccc.attribute != 'from'
	LEFT OUTER JOIN cse_corporation corp
	ON ccc.corporation_uuid = corp.corporation_uuid
		
	LEFT OUTER JOIN cse_corporation_check ccf
	ON `check`.check_uuid = ccf.check_uuid AND ccf.deleted = 'N' AND ccf.attribute = 'from'
	LEFT OUTER JOIN cse_corporation thefrom
	ON ccf.corporation_uuid = thefrom.corporation_uuid
	
	LEFT OUTER JOIN cse_person_check cpc
	ON `check`.check_uuid = cpc.check_uuid AND cpc.deleted = 'N'
	LEFT OUTER JOIN cse_person pers
	ON cpc.person_uuid = pers.person_uuid
	
	" . $case_join . " JOIN cse_case_check cci
	ON `check`.check_uuid = cci.check_uuid
	AND cci.customer_id = :customer_id
	AND cci.deleted = 'N'
	" . $case_join . " JOIN cse_case ccase
	ON (
		cci.case_uuid = ccase.case_uuid";
	if ($case_id!="") {
		$sql .= "
		AND `ccase`.`case_id` = :case_id";
	}
	$sql .= "
	)";
	
	if ($account_id!="" && $account_id!="-1") {
		//was there a request for the check
		$sql .= "
		LEFT OUTER JOIN cse_checkrequest creq
		ON `check`.check_uuid = creq.check_uuid
		";
	}
	if (($ledger == "IN" || $ledger == "") && $kinvoice_id!="") {
		$sql .= "
		INNER JOIN cse_kinvoice_check cck
		ON `check`.check_uuid = cck.check_uuid
		INNER JOIN cse_kinvoice ki
		ON cck.kinvoice_uuid = ki.kinvoice_uuid
		
		INNER JOIN cse_corporation_kinvoice corpinv
		ON ki.kinvoice_uuid = corpinv.kinvoice_uuid
		
		INNER JOIN cse_corporation corp
		ON corpinv.corporation_uuid = corp.corporation_uuid";
	}
	if ($account_id != "") {
		$sql .= "
			" . $account_join . " JOIN cse_account_check cac
			ON `check`.check_uuid = cac.check_uuid
			" . $account_join . " JOIN cse_account acc
			ON cac.account_uuid = acc.account_uuid
		";
	}
	$sql .= "
	LEFT OUTER JOIN (
		SELECT icmd.check_uuid, GROUP_CONCAT(DISTINCT icd.document_filename) check_attachments
        FROM `cse_check_document` icmd
        INNER JOIN `cse_check` im
        ON icmd.check_uuid = im.check_uuid
        INNER JOIN `cse_document` icd
        ON icmd.document_uuid = icd.document_uuid
        WHERE 1
        GROUP BY check_uuid
    ) ccd
	ON `check`.check_uuid = ccd.check_uuid
	";
	$sql .= "
	LEFT OUTER JOIN (
		SELECT cct.check_uuid, cct.`time_stamp` print_date, cct.`user_logon` print_by, last_track.track_count
		FROM cse_check_track cct
		INNER JOIN (
			SELECT `cct`.`check_id`, MAX(check_track_id) max_track_id, COUNT(check_track_id) track_count
			FROM cse_check_track cct
			WHERE operation = 'printed'
			GROUP BY `cct`.`check_id`
		) last_track
		ON cct.check_track_id = last_track.max_track_id
		WHERE operation = 'printed'
	)	`prints`
	ON `check`.check_uuid = `prints`.check_uuid";

	$sql .= "
	WHERE 1
	AND `check`.customer_id = :customer_id
	AND `check`.deleted = 'N'";
	
	if ($ledger!="") {
		$sql .= "
		AND (`check`.ledger = :ledger";
		if ($ledger=="OUT") {
			$sql .= "
			OR `check`.ledger = 'DIS'";
		}
		$sql .= "
		)";
	}
	if ($blnUncleared) {
		//check has not cleared, created in ikase
		$sql .= "
		AND `check`.check_status != 'C'
		AND `check`.check_uuid LIKE 'KS%'";
	}
	if ($check_status!="" && strpos($check_status, "printed")===false) {
		//printed status comes from tracking table
		$sql .= "
		AND `check`.check_status = :check_status";
	}
	if ($check_status=="printed") {
		//printed status comes from tracking table
		$sql .= "
		AND `prints`.check_uuid IS NOT NULL";
	}
	if ($check_status=="unprinted") {
		//printed status comes from tracking table
		//if they cleared, they were printed at some point
		$sql .= "
		AND `prints`.check_uuid IS NULL
		AND `check`.ledger = 'OUT'
		AND `check`.check_status != 'C'";
	}
	if ($kinvoice_id!="") {
		$sql .= "
		AND ki.kinvoice_id= :kinvoice_id";
	}
	if ($account_id!="") {
		if ($account->account_type=="operating") {
			$sql .= "
			AND `check`.ledger = 'OUT'
			AND acc.account_id IS NULL";
		} else {
			$sql .= "
			AND acc.account_id= :account_id";
			$sql .= "
			ORDER BY `check`.check_status ASC, `check`.`check_id` ASC";
		}
		
	} else {
		$sql .= "
		ORDER BY `check`.`check_id` ASC";
	}
	if ($account_id!="") {
		if ($account->account_type=="operating") {
	//	die($sql);
			$sql .= "
			
			UNION
			
			" . $sql_select . 
			"
			FROM cse_check `check`
			INNER JOIN (
				SELECT check_id 
                FROM cse_check_track cct
                WHERE operation = 'insert'
			) cct
            ON `check`.check_id = cct.check_id
			INNER JOIN cse_account_check cac
			ON `check`.check_uuid = `cac`.check_uuid
			INNER JOIN cse_account acc
			ON `cac`.account_uuid = acc.account_uuid
			LEFT OUTER JOIN cse_corporation_check ccc
				ON `check`.check_uuid = ccc.check_uuid AND ccc.deleted = 'N' AND ccc.attribute != 'from'
				LEFT OUTER JOIN cse_corporation corp
				ON ccc.corporation_uuid = corp.corporation_uuid
					
				LEFT OUTER JOIN cse_corporation_check ccf
				ON `check`.check_uuid = ccf.check_uuid AND ccf.deleted = 'N' AND ccf.attribute = 'from'
				LEFT OUTER JOIN cse_corporation thefrom
				ON ccf.corporation_uuid = thefrom.corporation_uuid
				
				LEFT OUTER JOIN cse_person_check cpc
				ON `check`.check_uuid = cpc.check_uuid AND cpc.deleted = 'N'
				LEFT OUTER JOIN cse_person pers
				ON cpc.person_uuid = pers.person_uuid
				
				LEFT OUTER JOIN cse_case_check cci
				ON `check`.check_uuid = cci.check_uuid	AND cci.customer_id = :customer_id	AND cci.deleted = 'N'
				LEFT OUTER JOIN cse_case ccase
				ON cci.case_uuid = ccase.case_uuid
				
				
				LEFT OUTER JOIN (
					SELECT icmd.check_uuid, GROUP_CONCAT(DISTINCT icd.document_filename) check_attachments
					FROM `cse_check_document` icmd
					INNER JOIN `cse_check` im
					ON icmd.check_uuid = im.check_uuid
					INNER JOIN `cse_document` icd
					ON icmd.document_uuid = icd.document_uuid
					WHERE 1
					GROUP BY check_uuid
				) ccd
				ON `check`.check_uuid = ccd.check_uuid
				
				LEFT OUTER JOIN (
					SELECT cct.check_uuid, cct.`time_stamp` print_date, cct.`user_logon` print_by, last_track.track_count
					FROM cse_check_track cct
					INNER JOIN (
						SELECT `cct`.`check_id`, MAX(check_track_id) max_track_id, COUNT(check_track_id) track_count
						FROM cse_check_track cct
						WHERE operation = 'printed'
						GROUP BY `cct`.`check_id`
					) last_track
					ON cct.check_track_id = last_track.max_track_id
					WHERE operation = 'printed'
				)	`prints`
				ON `check`.check_uuid = `prints`.check_uuid
			
			
			WHERE `check`.ledger = 'IN'
			AND `check`.deleted = 'N'
			AND acc.account_type = 'operating'
			AND `check`.customer_id = :customer_id
			ORDER BY check_status ASC, `check_id` ASC";
			
		}
	}
	
	//die($sql);
	try {
		$customer_id = $_SESSION['user_customer_id'];
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($case_id!="") {
			$stmt->bindParam("case_id", $case_id);
		}
		$stmt->bindParam("customer_id", $customer_id);
		if ($ledger!="") {
			$stmt->bindParam("ledger", $ledger);
		}
		if ($kinvoice_id!="") {
			$stmt->bindParam("kinvoice_id", $kinvoice_id);
		}
		if ($account_id!="") {
			$stmt->bindParam("account_id", $account_id);
		}
		if ($check_status!="" && strpos($check_status, "printed")===false) {
			$stmt->bindParam("check_status", $check_status);
		}
		$stmt->execute();
		$checks = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		echo json_encode($checks);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getAccountLedgerTotals($account_id) {
	getLedgerTotals(true, $account_id);
}
function getAccountUnclearedLedgerTotals($account_id) {
	getUnclearedLedgerTotals($account_id);
}
function getUnclearedLedgerTotals($account_id = "") {
	getLedgerTotals(false, $account_id);
}
function getLedgerTotals($blnCleared = true, $account_id = "") {
	session_write_close();
	$customer_id = $_SESSION["user_customer_id"];
	
	$sql = "SELECT chk.ledger, SUM(chk.amount_due) amount_totals, SUM(chk.payment) payment_totals, SUM(ABS(chk.adjustment)) adjustment_totals,
	COUNT(DISTINCT ccase.case_id) case_count
	FROM `cse_check` `chk` 
	INNER JOIN cse_case_check ccc
	ON chk.check_uuid = ccc.check_uuid
	INNER JOIN cse_case ccase
	ON ccc.case_uuid = ccase.case_uuid";
	if ($account_id != "") {
		$sql .= "
		INNER JOIN cse_account_check cac
		ON chk.check_uuid = cac.check_uuid
		INNER JOIN cse_account acc
		ON cac.account_uuid = acc.account_uuid";
	}
	$sql .= "
	WHERE 1
	AND ccase.customer_id = :customer_id
	AND chk.check_status != 'V'";
	if ($blnCleared) {
		$sql .= "
		AND chk.check_status = 'C'";
	} else {
		$sql .= "
		AND INSTR(chk.check_uuid, 'KS') = 1
		AND chk.check_status != 'C'";
	}
	if ($account_id != "") {
		$sql .= "
		AND acc.account_id = :account_id";
	}
	$sql .= "
	GROUP BY chk.ledger";
	
	die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		if ($account_id != "") {
			$stmt->bindParam("account_id", $account_id);
		}
		$stmt->execute();
		$ledgers = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		echo json_encode($ledgers);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getSettlementChecks($case_id, $recipient) {
	session_write_close();
	$ledger = "OUT";
    $sql = "SELECT DISTINCT `check`.*, `check`.check_id id , `check`.check_uuid uuid, ccase.case_id, ccase.case_uuid, 	
			IFNULL(ccd.check_attachments, '') attachments, 
	
			IF(`corp`.`company_name` IS NULL, `pers`.`full_name`, `corp`.`company_name`) payable_full_name,
			IF (`corp`.`company_name` IS NOT NULL, IF(`corp`.`type` = 'recipient', 'records', 'standard'), 'standard') payable_type,
			IF(`corp`.`company_name` IS NULL, 'person', 'corporation') payable_table,
			IF(`corp`.`company_name` IS NULL, pers.person_id, corp.corporation_id) payable_id
			
			FROM `cse_check` `check`
						
			LEFT OUTER JOIN cse_corporation_check ccc
			ON `check`.check_uuid = ccc.check_uuid AND ccc.deleted = 'N'
			LEFT OUTER JOIN cse_corporation corp
			ON ccc.corporation_uuid = corp.corporation_uuid
			
			LEFT OUTER JOIN cse_person_check cpc
			ON `check`.check_uuid = cpc.check_uuid AND cpc.deleted = 'N'
			LEFT OUTER JOIN cse_person pers
			ON cpc.person_uuid = pers.person_uuid
			
			INNER JOIN cse_case_check cci
			ON `check`.check_uuid = cci.check_uuid AND attribute = :recipient
			INNER JOIN cse_case ccase
			ON (cci.case_uuid = ccase.case_uuid
			AND `ccase`.`case_id` = :case_id)
			WHERE 1
			AND cci.customer_id = :customer_id
			AND cci.deleted = 'N'
			AND `check`.deleted = 'N'";
	if ($ledger!="") {
		$sql .= "
		AND `check`.ledger = :ledger";
	}
	$sql .= "
	LEFT OUTER JOIN (
		SELECT icmd.check_uuid, GROUP_CONCAT(DISTINCT icd.document_filename) check_attachments
        FROM `cse_check_document` icmd
        INNER JOIN `cse_check` im
        ON icmd.check_uuid = im.check_uuid
        INNER JOIN `cse_document` icd
        ON icmd.document_uuid = icd.document_uuid
        WHERE 1
        GROUP BY check_uuid
    ) ccd
	ON `check`.check_uuid = ccd.check_uuid
	";
	$sql .= "
	ORDER BY `check`.`check_id` ASC";
	//die($sql);
	try {
		$customer_id = $_SESSION['user_customer_id'];
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("customer_id", $customer_id);
		if ($ledger!="") {
			$stmt->bindParam("ledger", $ledger);
		}
		$stmt->bindParam("recipient", $recipient);
		$stmt->execute();
		$checks = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		echo json_encode($checks);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getCheckRequestCategories() {
	session_write_close();
	
	$sql = "SELECT cct.*, cct.checkrequest_type_id id 
	FROM `cse_checkrequest_type` cct 
	WHERE 1
	ORDER BY checkrequest_type ASC";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$cats = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		echo json_encode($cats);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function saveCheckRequestTypes() {
	session_write_close();
	$user_uuid = $_SESSION["user_id"];
	$right_now = date("Y-m-d H:i:s");
	$checkrequest_type = passed_var("checkrequest_type", "post");

	$table_name = "checkrequest_type";
	
	$sql = "INSERT INTO `cse_" . $table_name . "` (" . $table_name . ", last_change_user, last_change_date)
	SELECT :checkrequest_type, :user_uuid, :right_now
						FROM dual
						WHERE NOT EXISTS (
							SELECT * 
							FROM `cse_" . $table_name . "` 
							WHERE " . $table_name . " = :checkrequest_type
						)";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("checkrequest_type", $checkrequest_type);
		$stmt->bindParam("user_uuid", $user_uuid);
		$stmt->bindParam("right_now", $right_now);
		$stmt->execute();
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>true));
	} catch(PDOException $e) {
		$error = array("error3"=> array("text"=>$e->getMessage()));
		die(json_encode($error));
	}	
}
function updateCheckRequest() {
	session_write_close();
	$table_uuid = uniqid("SF", false);
	$user_uuid = $_SESSION["user_id"];
	$right_now = date("Y-m-d H:i:s");
	
	$id = passed_var("table_id", "post");
	$amount = passed_var("amount", "post");

	$table_name = "checkrequest";
	
	$sql = "UPDATE `cse_" . $table_name . "` 
	SET `amount` = '" . $amount . "'
	WHERE `" . $table_name . "_id` = '" . $id . "'";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		//$stmt->bindParam("id", $id);
		//$stmt->bindParam("amount", $amount);
		
		$stmt->execute();
		$stmt = null; $db = null;
		//$stmt->bindParam("user_uuid", $user_uuid);
		//$stmt->bindParam("right_now", $right_now);
		echo json_encode(array("success"=>true));
	} catch(PDOException $e) {
		$error = array("error3"=> array("text"=>$e->getMessage()));
		die(json_encode($error));
	}	
}
function updateCheckRequestTypes() {
	session_write_close();
	$table_uuid = uniqid("SF", false);
	$user_uuid = $_SESSION["user_id"];
	$right_now = date("Y-m-d H:i:s");
	$id = passed_var("checkrequest_type_id", "post");
	$deleted = passed_var("deleted", "post");
	$checkrequest_type = passed_var("checkrequest_type", "post");

	$table_name = "checkrequest_type";
	
	$sql = "UPDATE `cse_" . $table_name . "` 
	SET `" . $table_name . "` = :checkrequest_type,
	deleted = :deleted, 
	last_change_user = :user_uuid, 
	last_change_date = :right_now
	WHERE `" . $table_name . "_id` = :id";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->bindParam("checkrequest_type", $checkrequest_type);
		$stmt->bindParam("deleted", $deleted);
		$stmt->bindParam("user_uuid", $user_uuid);
		$stmt->bindParam("right_now", $right_now);
		$stmt->execute();
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>true));
	} catch(PDOException $e) {
		$error = array("error3"=> array("text"=>$e->getMessage()));
		die(json_encode($error));
	}	
}
function getCheckCategories() {
	session_write_close();
	
	$sql = "SELECT cct.*, cct.cost_type_id id 
	FROM `cse_cost_type` cct 
	WHERE 1
	ORDER BY cost_type ASC";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$cats = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		echo json_encode($cats);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function saveCostTypes() {
	session_write_close();
	$user_uuid = $_SESSION["user_id"];
	$right_now = date("Y-m-d H:i:s");
	$cost_type = passed_var("cost_type", "post");

	$table_name = "cost_type";
	
	$sql = "INSERT INTO `cse_" . $table_name . "` (" . $table_name . ", last_change_user, last_change_date)
	SELECT :cost_type, :user_uuid, :right_now
						FROM dual
						WHERE NOT EXISTS (
							SELECT * 
							FROM `cse_" . $table_name . "` 
							WHERE " . $table_name . " = :cost_type
						)";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("cost_type", $cost_type);
		$stmt->bindParam("user_uuid", $user_uuid);
		$stmt->bindParam("right_now", $right_now);
		$stmt->execute();
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>true));
	} catch(PDOException $e) {
		$error = array("error3"=> array("text"=>$e->getMessage()));
		die(json_encode($error));
	}	
}
function updateCostTypes() {
	session_write_close();
	$table_uuid = uniqid("SF", false);
	$user_uuid = $_SESSION["user_id"];
	$right_now = date("Y-m-d H:i:s");
	$id = passed_var("cost_type_id", "post");
	$deleted = passed_var("deleted", "post");
	$cost_type = passed_var("cost_type", "post");

	$table_name = "cost_type";
	
	$sql = "UPDATE `cse_" . $table_name . "` 
	SET `" . $table_name . "` = :cost_type,
	deleted = :deleted, 
	last_change_user = :user_uuid, 
	last_change_date = :right_now
	WHERE `" . $table_name . "_id` = :id";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->bindParam("cost_type", $cost_type);
		$stmt->bindParam("deleted", $deleted);
		$stmt->bindParam("user_uuid", $user_uuid);
		$stmt->bindParam("right_now", $right_now);
		$stmt->execute();
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>true));
	} catch(PDOException $e) {
		$error = array("error3"=> array("text"=>$e->getMessage()));
		die(json_encode($error));
	}	
}
function getFeeChecks($fee_id) {
	session_write_close();
	$ledger = "OUT";
    $sql = "SELECT DISTINCT `check`.*, `check`.check_id id , `check`.check_uuid uuid, ccase.case_id, ccase.case_uuid, 	
			IFNULL(ccd.check_attachments, '') attachments, 
	
			IF(`corp`.`company_name` IS NULL, `pers`.`full_name`, `corp`.`company_name`) payable_full_name,
			IF (`corp`.`company_name` IS NOT NULL, IF(`corp`.`type` = 'recipient', 'records', 'standard'), 'standard') payable_type,
			IF(`corp`.`company_name` IS NULL, 'person', 'corporation') payable_table,
			IF(`corp`.`company_name` IS NULL, pers.person_id, corp.corporation_id) payable_id
			
			FROM `cse_check` `check`
						
			LEFT OUTER JOIN cse_corporation_check ccc
			ON `check`.check_uuid = ccc.check_uuid AND ccc.deleted = 'N'
			LEFT OUTER JOIN cse_corporation corp
			ON ccc.corporation_uuid = corp.corporation_uuid
			
			LEFT OUTER JOIN cse_person_check cpc
			ON `check`.check_uuid = cpc.check_uuid AND cpc.deleted = 'N'
			LEFT OUTER JOIN cse_person pers
			ON cpc.person_uuid = pers.person_uuid
			
			INNER JOIN cse_case_check cci
			ON `check`.check_uuid = cci.check_uuid
			
			INNER JOIN cse_case ccase
			ON cci.case_uuid = ccase.case_uuid
			
			INNER JOIN cse_fee_check cfc
			ON `check`.check_uuid = cfc.check_uuid
			
			INNER JOIN cse_fee fee
			ON (cfc.fee_uuid = fee.fee_uuid AND fee.fee_id = :fee_id)";
	$sql .= "
	LEFT OUTER JOIN (
		SELECT icmd.check_uuid, GROUP_CONCAT(DISTINCT icd.document_filename) check_attachments
        FROM `cse_check_document` icmd
        INNER JOIN `cse_check` im
        ON icmd.check_uuid = im.check_uuid
        INNER JOIN `cse_document` icd
        ON icmd.document_uuid = icd.document_uuid
        WHERE 1
        GROUP BY check_uuid
    ) ccd
	ON `check`.check_uuid = ccd.check_uuid
	";			
	$sql .= "
			WHERE 1
			AND cci.customer_id = :customer_id
			AND cci.deleted = 'N'
			AND `check`.deleted = 'N'";
	$sql .= "
	ORDER BY `check`.`check_id` ASC";
	//die($sql);
	try {
		$customer_id = $_SESSION['user_customer_id'];
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("fee_id", $fee_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$checks = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		echo json_encode($checks);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function addCheck() {
	session_write_close();
	//die(print_r($_POST));
	$request = Slim::getInstance()->request();
	$db = getConnection();
	$arrFields = array();
	$arrSet = array();
	$table_name = "";
	$table_id = "";
	$recipient = "";
	$case_id = "";
	$case_uuid = "";
	$invoice_number = "";
	$kinvoice_id = "";
	$carrier_uuid = "";
	$person_uuid = "";
	$account_id = "";
	$account_uuid = "";
	$account_type = "";
	$payback_id = "";
	$payback_check_uuid = "";
	$fee_id = "";
	$checkrequest_id = "";
	$checkrequest_uuid = "";
	$fee_uuid = "";
	$transaction_date = "";
	$method = "";
	$payments = 0;
	$payable_to_id =  "";
	$check_from_id = "";
	$check_number = passed_var("check_number", "post");
	$next_check_number = "";
	
	if (!isset($_POST["payable_to"])) {
		$payable_to_id =  passed_var("corp_id", "post");
	}
	if (isset($_POST["payable_to"])) {
		$payable_to_id =  passed_var("payable_to", "post");
	}
	if (isset($_POST["check_from"])) {
		$check_from_id =  passed_var("check_from", "post");
	}
	$payable_to = "";
	
	$customer_id = $_SESSION["user_customer_id"];
	
	$arrAttachments = array();
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		
		$fieldname = str_replace("Input", "", $fieldname);
		//fix for defaults
		if ($fieldname=="amount_due" || $fieldname=="payment" || $fieldname=="balance") {
			if ($value=="") {
				$value = 0.00;
			}
		}
		if ($fieldname=="payment") {
			$payments = $value;
		}
		if ($fieldname=="method") {
			$method = $value;
		}
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="payback_id") {
			$payback_id = $value;
			if ($payback_id!="" && $payback_id!="-1") {
				$payback_check = getCheckInfo($payback_id);
				$payback_check_id = $payback_check->id;
				$payback_check_uuid = $payback_check->uuid;
			}
			continue;
		}
		
		if ($fieldname=="account_id") {
			$account_id = $value;
			if ($account_id!="" && $account_id!="-1") {
				$account = getBankAccountInfo($account_id);
				$account_uuid = $account->uuid;
				$account_type = $account->account_type;
				
				if ($account->account_info!="") {
					$account_info = json_decode($account->account_info);
					foreach($account_info as $iindex=>$info) {
						//is it the same as what the user entered?
						if ($info->name=="current_check_numberInput" && $info->value==$check_number) {
							$next_check_number = $info->value;
							//increment
							$next_check_number++;
							$info->value = $next_check_number;
							$account_info[$iindex] = $info;
							break;
						}
					}
					//updated, back to json
					$account_info = json_encode($account_info);
					
					//update
					$sql = "UPDATE cse_account
					SET account_info = :account_info
					WHERE account_uuid = :account_uuid
					AND customer_id = :customer_id";
					try { 
						$db = getConnection();
						$stmt = $db->prepare($sql);  
						$stmt->bindParam("account_info", $account_info);
						$stmt->bindParam("account_uuid", $account_uuid);
						$stmt->bindParam("customer_id", $customer_id);
						$stmt->execute();
						$stmt = null; $db = null;
					} catch(PDOException $e) {	
						die( '{"error":{"text":'. $e->getMessage() .'}}'); 
					}
				}
			}
			continue;
		}
		if ($fieldname=="checkrequest_id") {
			$checkrequest_id = $value;
			if ($checkrequest_id!="") {
				$checkrequest = getCheckRequestInfo($checkrequest_id);
				$checkrequest_uuid = $checkrequest->uuid;
			}
			continue;
		}
		if ($fieldname=="case_id") {
			if ($value!="" && $value!="0" && $value!="-1" && $value!="-2") {
				$case_id = $value;
				$kase = getKaseInfo($case_id);
				$case_uuid = $kase->uuid;
			}
			continue;
		}
		if ($fieldname=="recipient") {
			$recipient = $value;
			continue;
		}
		if ($fieldname=="attachments") {
			if ($value!="") {
				$attachments = $value;
				$arrAttachments[] = $value;
			}
			continue;
		}
		
		if ($fieldname=="invoice_number") {
			$invoice_number = $value;
			continue;
		}
		if ($fieldname=="kinvoice_id") {
			$kinvoice_id = $value;
			continue;
		}
		if ($fieldname=="carrier") {
			if ($value!="") {
				$carrier = getCorporationInfo($value);
				$carrier_uuid = $carrier->uuid;
			}
			continue;
		}
		if ($fieldname=="person") {
			if ($value!="") {
				$person = getPersonInfo($value);
				$person_uuid = $person->uuid;
			}
			continue;
		}
		//FOR NOW
		if ($fieldname=="table_id" || $fieldname=="send_document_id" || $fieldname=="corp_id" || $fieldname=="payable_to" || $fieldname=="check_from" || $fieldname=="account_type") {
			continue;
		}
		if ($fieldname=="fee_id") {
			$fee_id = $value;
			if ($fee_id!="") { 
				$fee = getFeeInfo($fee_id);
				$fee_uuid = $fee->uuid;
			}
			continue;
		}
		if (strpos($fieldname, "_date") > -1) {
			if ($value!="") {
				$value = date("Y-m-d", strtotime($value));
				if ($fieldname=="transaction_date") {
					$transaction_date = $value;
				}
			} else {
				$value = "0000-00-00";
			}
		}

		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "'" . addslashes($value) . "'";
	}
	$case_uuid = "";
	if ($case_id!="") {
		$kase = getKaseInfo($case_id);
		$case_uuid = $kase->uuid;
	}	
	$arrParties = explode("|", $payable_to_id);
	$corporation_id = ""; $person_id = ""; $request_customer_id = "";
	
	if (count($arrParties) > 1) {
		//die(print_r($arrParties));
		
		if ($arrParties[1]=="C") {
			$corporation_id = $arrParties[0];
			$corporation = getCorporationInfo($corporation_id);
			$payable_to = $corporation->company_name;
			$payable_type = 'C';
		}
		
		if ($arrParties[1]=="P") {
			$person_id = $arrParties[0];
			$person = getPersonInfo($person_id);
			$payable_to = $person->full_name;
			$payable_type = 'P';
		}
		
		if ($arrParties[1]=="X" || $arrParties[1]=="F") {
			//firm checkrequest
			$request_customer_id = $customer_id;
			$payable_type = 'F';
		}
	}
	
	if ($check_from_id!="") {
		$corporation = getCorporationInfo($check_from_id);
		$carrier_uuid = $corporation->uuid;
	}
	$arrFields[] = "`customer_id`";
	$arrSet[] = $_SESSION['user_customer_id'];
	if ($carrier_uuid!="") {
		$arrFields[] = "`carrier_uuid`";
		$arrSet[] = "'" . $carrier_uuid . "'";
	}
	if ($payback_check_uuid!="") {
		$arrFields[] = "`parent_check_uuid`";
		$arrSet[] = "'" . $payback_check_uuid . "'";
	}
	
	$table_uuid = uniqid("KS", false);
	$sql = "INSERT INTO `cse_" . $table_name ."` (`" . $table_name . "_uuid`, " . implode(",", $arrFields) . ") 
			VALUES('" . $table_uuid . "', " . implode(",", $arrSet) . ")";
			//die(print_r($arrFields));
			
	$last_updated_date = date("Y-m-d H:i:s");
	try { 
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		$new_id = $db->lastInsertId();
		$stmt = null; $db = null;
				
		if ($case_id!="") {
			$case_table_uuid = uniqid("KA", false);
			$attribute_1 = "main";
			if ($recipient!="") {
				$attribute_1 = $recipient;
			}
			if ($request_customer_id!="") {
				$attribute_1 = "firm";
			}
			//now we have to attach the check to the case 
			$sql = "INSERT INTO cse_case_" . $table_name . " (`case_" . $table_name . "_uuid`, `case_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', '" . $attribute_1 . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			$stmt = null; $db = null;
			
			if ($request_customer_id!="") {
				$attribute_1 = "firm";
			}
		}
		
		if ($carrier_uuid!="") {
			$case_table_uuid = uniqid("KC", false);
			$attribute_1 = "main";
			if ($recipient!="") {
				$attribute_1 = $recipient;
			}
			if ($check_from_id!="") {
				$attribute_1 = "from";
			}
			//now we have to attach the check to the carrier 
			$sql = "INSERT INTO cse_corporation_" . $table_name . " (`corporation_" . $table_name . "_uuid`, `corporation_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_table_uuid  ."', '" . $carrier_uuid . "', '" . $table_uuid . "', '" . $attribute_1 . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			$stmt = null; $db = null;
		}
		
		if ($person_uuid!="") {
			$case_table_uuid = uniqid("KC", false);
			$attribute_1 = "main";
			if ($recipient!="") {
				$attribute_1 = $recipient;
			}
			//now we have to attach the check to the case 
			$sql = "INSERT INTO cse_person_" . $table_name . " (`person_" . $table_name . "_uuid`, `person_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_table_uuid  ."', '" . $person_uuid . "', '" . $table_uuid . "', '" . $attribute_1 . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			$stmt = null; $db = null;
		}
		
		if ($account_uuid!="") {
			$account_table_uuid = uniqid("KC", false);
			$attribute_1 = $account_type;
			
			//now we have to attach the check to the case 
			$sql = "INSERT INTO cse_account_" . $table_name . " (`account_" . $table_name . "_uuid`, `account_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $account_table_uuid  ."', '" . $account_uuid . "', '" . $table_uuid . "', '" . $attribute_1 . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			$stmt = null; $db = null;
			
			if ($method!="transfer") {
				//update the account balance, transfers are already recalculated
				$sql = "UPDATE cse_account
				SET account_balance = (account_balance + " . $payments . ")
				WHERE account_id = " . $account_id . "
				AND customer_id = '" . $_SESSION['user_customer_id'] . "'";
				
				$db = getConnection();
				$stmt = $db->prepare($sql);  
				$stmt->execute();
				$stmt = null; $db = null;
				
				trackAccount("update", $account_id);
			}
		} else {
			//do we have a setting for check number?
			$sql = "SELECT setting_value
			FROM cse_setting cset
			WHERE cset.setting = 'check_number'
			AND cset.customer_id = :customer_id";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("customer_id", $customer_id);
			$stmt->execute();
			$setting = $stmt->fetchObject();
			$stmt = null; $db = null;
			
			if (is_object($setting)) {
				if ($setting->setting_value = $check_number) {
					//increment the case_number_next
					$sql = "UPDATE cse_setting cset
					SET cset.setting_value = cset.setting_value + 1
					WHERE cset.setting = 'check_number'
					AND cset.customer_id = :customer_id";
					
					//echo $sql . "\r\n";
					
					$db = getConnection();
					$stmt = $db->prepare($sql);  
					$stmt->bindParam("customer_id", $customer_id);
					$stmt->execute();
					$db = null;
				}
			} else {
				if (is_numeric($check_number)) {
					$check_number++;
					//let's start it from here
					$setting_uuid = uniqid("ST", false);
					//increment the case_number_next
					$sql = "INSERT INTO cse_setting 
					(setting_uuid, category, setting, setting_value, customer_id)
					VALUES ('" . $setting_uuid . "', 'checks', 'check_number', '" . $check_number . "', :customer_id)";
					
					//echo $sql . "\r\n";
					
					$db = getConnection();
					$stmt = $db->prepare($sql);  
					$stmt->bindParam("customer_id", $customer_id);
					$stmt->execute();
					$db = null;
				}
			}
		}
		
		if ($fee_uuid!="") {
			$fee_table_uuid = uniqid("FC", false);
			$attribute_1 = "main";
			if ($recipient!="") {
				$attribute_1 = $recipient;
			}
			//now we have to attach the check to the case 
			$sql = "INSERT INTO cse_fee_" . $table_name . " (`fee_" . $table_name . "_uuid`, `fee_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $fee_table_uuid  ."', '" . $fee_uuid . "', '" . $table_uuid . "', '" . $attribute_1 . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			$stmt = null; $db = null;
			
			//update the receive date
			$sql = "UPDATE cse_fee
			SET fee_date = :transaction_date
			WHERE fee_uuid = :fee_uuid
			AND customer_id = :customer_id";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);  	
			$stmt->bindParam("transaction_date", $transaction_date);
			$stmt->bindParam("fee_uuid", $fee_uuid);
			$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
			$stmt->execute();
			$stmt = null; $db = null;
		}
		
		if ($checkrequest_uuid!="") {
			//update the receive date
			$sql = "UPDATE cse_checkrequest
			SET check_uuid = :check_uuid
			WHERE checkrequest_uuid = :checkrequest_uuid
			AND customer_id = :customer_id";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);  	
			$stmt->bindParam("check_uuid", $table_uuid);
			$stmt->bindParam("checkrequest_uuid", $checkrequest_uuid);
			$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
			$stmt->execute();
			$stmt = null; $db = null;
		}
		
		//invoice
		if ($kinvoice_id!="0" && $kinvoice_id!="") {
			$kinvoiceitems = getKInvoiceItems($kinvoice_id, true);
			foreach($kinvoiceitems as $kitem) {
				$kinvoice_uuid = $kitem->kinvoice_uuid;
				//just one
				break;
			}
			$kinvoice_table_uuid = uniqid("KP", false);
			$attribute_1 = "main";
			
			//now we have to attach the check to the kinvoice 
			$sql = "INSERT INTO cse_kinvoice_" . $table_name . " (`kinvoice_" . $table_name . "_uuid`, `kinvoice_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $kinvoice_table_uuid  ."', '" . $kinvoice_uuid . "', '" . $table_uuid . "', '" . $attribute_1 . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			$stmt = null; $db = null;
			
			if ($method!="transfer") {
				//update the payments on kinvoice
				$sql = "UPDATE `cse_kinvoice` 
				SET `payments` = `payments` + " . $payments . "
				WHERE `kinvoice_id` = '" . $kinvoice_id . "'
				AND customer_id = " . $_SESSION['user_customer_id'];
				
				$db = getConnection();
				$stmt = $db->prepare($sql);  
				$stmt->execute();
				$stmt = null; $db = null;
				
				trackKInvoice("payment", $kinvoice_id);
			}
		}
		
		foreach ($arrAttachments as $attachment) {
			$document_name = $attachment;
			//first check if this document is _already_ attached
			$sql = "SELECT COUNT(doc.document_id) thecount
			FROM `cse_document` doc
			INNER JOIN `cse_check_document` cnd
			ON doc.document_uuid = cnd.document_uuid
			WHERE doc.document_name = '" . $document_name . "'";
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->execute();
			$document_count = $stmt->fetchObject();
			$stmt->closeCursor(); $stmt = null; $db = null;	
			
			if ($document_count->thecount==0) {
				$document_name = explode("/", $document_name);
				$document_name = $document_name[count($document_name) - 1];
				$document_date = date("Y-m-d H:i:s");
				$document_extension = explode(".", $document_name);
				$document_extension = $document_extension[count($document_extension) - 1];
				
				$description = "check attachment";
				$description_html = "check attachment";
				$type = "check_attachment";
				$verified = "Y";
				
				//attachment is a document
				$document_uuid = uniqid("KS");
				$sql = "INSERT INTO cse_document (document_uuid, parent_document_uuid, document_name, document_date, document_filename, document_extension, description, description_html, type, verified, customer_id) 
			VALUES (:document_uuid, :parent_document_uuid, :document_name, :document_date, :document_filename, :document_extension, :description, :description_html, :type, :verified, :customer_id)";
				$db = getConnection();
				$stmt = $db->prepare($sql);  
				$stmt->bindParam("document_uuid", $document_uuid);
				$stmt->bindParam("parent_document_uuid", $document_uuid);
				$stmt->bindParam("document_name", $document_name);
				$stmt->bindParam("document_date", $document_date);
				$stmt->bindParam("document_filename", $document_name);
				$stmt->bindParam("document_extension", $document_extension);
				$stmt->bindParam("description", $description);
				$stmt->bindParam("description_html", $description_html);
				$stmt->bindParam("type", $type);
				$stmt->bindParam("verified", $verified);
				$stmt->bindParam("customer_id", $customer_id);
				$stmt->execute();
				$document_id = $db->lastInsertId();
				$stmt = null; $db = null;	
				//die(print_r($newEmployee));
				trackDocument("insert", $document_id);
				
				$message_document_uuid = uniqid("TD", false);
				$last_updated_date = date("Y-m-d H:i:s");
				$sql = "INSERT INTO cse_check_document (`check_document_uuid`, `check_uuid`, `document_uuid`, `attribute_1`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $message_document_uuid  ."', '" . $table_uuid . "', '" . $document_uuid . "', 'attach', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
				$db = getConnection();
				$stmt = $db->prepare($sql);  
				$stmt->execute();
				$stmt = null; $db = null;	
				if ($case_uuid!="") {
					$sql = "INSERT INTO cse_case_document (`case_document_uuid`, `case_uuid`, `document_uuid`, `attribute_1`, `attribute_2`, `last_updated_date`, `last_update_user`, `customer_id`)
					VALUES ('" . $message_document_uuid  ."', '" . $case_uuid . "', '" . $document_uuid . "', 'attach', '', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $customer_id . "')";
					$db = getConnection();
					$stmt = $db->prepare($sql);  
					$stmt->execute();
					$stmt = null; $db = null;	
				}
			}
		}
		
		//payee
		if ($corporation_id!="") {
			$payable_id = $corporation_id;
			$payable_table = "corporation";
			
			$case_table_uuid = uniqid("RC", false);
			$attribute_1 = "main";
			
			//now we have to attach the check to the case 
			$sql = "INSERT INTO cse_corporation_" . $table_name . " (`corporation_" . $table_name . "_uuid`, `corporation_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_table_uuid  ."', '" . $corporation->uuid . "', '" . $table_uuid . "', '" . $attribute_1 . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			$stmt = null; $db = null;
		}
		
		if ($person_id!="") {
			$payable_id = $person_id;
			$payable_table = "person";
			$case_table_uuid = uniqid("RP", false);
			$attribute_1 = "main";
			
			//now we have to attach the check to the case 
			$sql = "INSERT INTO cse_person_" . $table_name . " (`person_" . $table_name . "_uuid`, `person_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_table_uuid  ."', '" . $person->uuid . "', '" . $table_uuid . "', '" . $attribute_1 . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			$stmt = null; $db = null;
		}
		
		
		//track now
		trackCheck("insert", $new_id);
		
		$sql_payback = "";
		if ($payback_check_uuid!="") {
			//update the parent payment column
			$sql_payback = "UPDATE cse_check
			SET payment = :payments
			WHERE check_id = :check_id
			AND customer_id = :customer_id";
			
			$db = getConnection();
			$stmt = $db->prepare($sql_payback);  
			$stmt->bindParam("check_id", $payback_check_id);
			$stmt->bindParam("payments", $payments);
			$stmt->bindParam("customer_id", $customer_id);
			$stmt->execute();
			$stmt = null; $db = null;	
			
			trackCheck("reimburse", $payback_check_id);
		}
		
		$db = null;
		
		echo json_encode(array("id"=>$new_id, "uuid"=>$table_uuid, "recipient"=>$recipient)); 
		
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}
function updateCheck() {
	$request = Slim::getInstance()->request();
	$db = getConnection();
	$arrSet = array();
	$where_clause = "";
	$table_name = "";
	$table_id = "";
	$recipient = "";
	$corporation_uuid = "";
	$person_uuid = "";
	$invoice_number = "";
	$kinvoice_id = "";
	$payment = 0;
	$attachments = "";
	$case_uuid = "";
	$fee_id = "";
	$account_id = "";
	$arrAttachments = array();
	$customer_id = $_SESSION["user_customer_id"];
	$check_from_id = "";
	if (isset($_POST["check_from"])) {
		$check_from_id =  passed_var("check_from", "post");
	}
	
	$payable_to_id =  "";
	if (!isset($_POST["payable_to"])) {
		$payable_to_id =  passed_var("corp_id", "post");
	}
	if (isset($_POST["payable_to"])) {
		$payable_to_id =  passed_var("payable_to", "post");
	}
	$payable_to = "";
	
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");

		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		//skip fields in update
		if ($fieldname=="case_id") {
			$kase = getKaseInfo($value);
			$case_uuid = $kase->uuid;;
			continue;
		}
		if ($fieldname=="fee_id") {
			continue;
		}
		if ($fieldname=="account_id") {
			$account_id = $value;
			continue;
		}
		if ($fieldname=="send_document_id") {
			$send_document_id = $value;
			if ($send_document_id!="") {
				//die("send:" . $send_document_id);
				$arrDocs = explode("|", $send_document_id);
				foreach($arrDocs as $send_document_id) {
					$send_document = getDocumentInfo($send_document_id);
					$arrAttachments[] = $send_document->document_filename;
				}
			}
			continue;
		}
		if ($fieldname=="attachments") {
			if ($value!="") {
				$attachments = $value;
				$arrAttachments[] = $value;
			}
			continue;
		}
		if ($fieldname=="recipient") {
			$recipient = $value;
			continue;
		}
		if ($fieldname=="payment") {
			$payment = $value;
			//continue;
		}
		if ($fieldname=="kinvoice_id") {
			$kinvoice_id = $value;
			continue;
		}
		if ($fieldname=="invoice_number") {
			$invoice_number = $value;
			continue;
		}
		if (strpos($fieldname, "_date") > -1) {
			if ($value!="") {
				$value = date("Y-m-d", strtotime($value));
			} else {
				$value = "0000-00-00";
			}
		}
		if ($fieldname=="carrier") {
			$carrier = getCorporationInfo($value);
			$corporation_uuid = $carrier->uuid;
			continue;
		}
		
		if ($fieldname=="payback_id" || $fieldname=="ledger") {
			continue;
		}
		if ($fieldname=="corp_id" || $fieldname=="payable_to" || $fieldname=="check_from") {
			continue;
		}
		if ($fieldname=="table_id" || $fieldname=="id") {
			$table_id = $value;
			$where_clause = " = " . $value;
		} else {
			$arrSet[] = "`" . $fieldname . "` = '" . addslashes($value) . "'";
		}
	}
	//echo $where_clause . "\r\ntab:" . $table_id . "\r\n";
	$my_check = getCheckInfo($table_id);
	//die(print_r($my_check));
	$current_payment = $my_check->payment;
	
	$arrParties = explode("|", $payable_to_id);
	$corporation_id = ""; $person_id = ""; $request_customer_id = "";
	
	if (count($arrParties) > 1) {
		//die(print_r($arrParties));
		
		if ($arrParties[1]=="C") {
			$corporation_id = $arrParties[0];
			$corporation = getCorporationInfo($corporation_id);
			//die(print_r($corporation));
			$payable_to = $corporation->company_name;
			$payable_type = 'C';
			
			$corporation_uuid = $corporation->uuid;
		}
		
		if ($arrParties[1]=="P") {
			$person_id = $arrParties[0];
			$person = getPersonInfo($person_id);
			$payable_to = $person->full_name;
			$payable_type = 'P';
			
			$person_uuid = $person->uuid;
		}
		
		if ($arrParties[1]=="X" || $arrParties[1]=="F") {
			//firm checkrequest
			$request_customer_id = $customer_id;
			$payable_type = 'F';
		}
	}
	
	
	$table_uuid = $my_check->uuid;
	
	//carrier
	if ($check_from_id!="") {
		$corporation = getCorporationInfo($check_from_id);
		
		if ($my_check->carrier_uuid != $corporation->uuid) {
			//clear out anything else
			$sql = "UPDATE cse_corporation_" . $table_name . "
			SET deleted = 'Y'
			WHERE `" . $table_name . "_uuid` = '" . $table_uuid . "'
			AND attribute = 'from'";
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			$stmt = null; $db = null;
			
			$carrier_uuid = $corporation->uuid;
			
			$case_table_uuid = uniqid("KC", false);
			$attribute_1 = "from";
			$last_updated_date = date("Y-m-d H:i:s");
			
			//now we have to attach the check to the carrier 
			$sql = "INSERT INTO cse_corporation_" . $table_name . " (`corporation_" . $table_name . "_uuid`, `corporation_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_table_uuid  ."', '" . $carrier_uuid . "', '" . $table_uuid . "', '" . $attribute_1 . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			$stmt = null; $db = null;

		}
	}

	$where_clause = "`" . $table_name . "_id`" . $where_clause;
	$sql = "
	UPDATE `cse_" . $table_name . "`
	SET " . implode(", ", $arrSet) . "
	WHERE " . $where_clause;
	$sql .= " 
	AND `cse_" . $table_name . "`.customer_id = '" . $customer_id . "'";
	
	try {		
		//die($sql);  
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		if ($corporation_uuid!="" || $person_uuid!="") {
			$sql = "UPDATE `cse_corporation_" . $table_name . "`
			SET deleted = 'Y'
			WHERE check_uuid = '" . $table_uuid . "'";
			$sql .= " 
			AND customer_id = '" . $customer_id . "'";
			//die($sql);
			$stmt = $db->prepare($sql);  	
			$stmt->execute();
			
			$sql = "UPDATE `cse_person_" . $table_name . "`
			SET deleted = 'Y'
			WHERE check_uuid = '" . $table_uuid . "'";
			$sql .= " 
			AND customer_id = '" . $customer_id . "'";
			//die($sql);
			$stmt = $db->prepare($sql);  	
			$stmt->execute();
		}
		if ($corporation_uuid!="") {
			$last_updated_date = date("Y-m-d H:i:s");
			$case_table_uuid = uniqid("KC", false);
			$attribute_1 = "main";
			if ($recipient!="") {
				$attribute_1 = $recipient;
			}
			//now we have to attach the check to the case 
			$sql = "INSERT INTO cse_corporation_" . $table_name . " (`corporation_" . $table_name . "_uuid`, `corporation_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_table_uuid  ."', '" . $corporation_uuid . "', '" . $table_uuid . "', '" . $attribute_1 . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $customer_id . "')";
			
			$stmt = $db->prepare($sql);  	
			$stmt->execute();
			$stmt = null; $db = null;		
		}
		if ($person_uuid!="") {
			$last_updated_date = date("Y-m-d H:i:s");
			$case_table_uuid = uniqid("KC", false);
			$attribute_1 = "main";
			if ($recipient!="") {
				$attribute_1 = $recipient;
			}
			//now we have to attach the check to the case 
			$sql = "INSERT INTO cse_person_" . $table_name . " (`person_" . $table_name . "_uuid`, `person_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_table_uuid  ."', '" . $person_uuid . "', '" . $table_uuid . "', '" . $attribute_1 . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $customer_id . "')";
			
			$stmt = $db->prepare($sql);  	
			$stmt->execute();
			$stmt = null; $db = null;		
		}
		
		if ($kinvoice_id!="") {
			//update the payments on kinvoice
			$sql = "UPDATE `cse_kinvoice` 
			SET `payments` = `payments` - " . $current_payment . " + " . $payment . "
			WHERE `kinvoice_id` = :kinvoice_id
			AND customer_id = :customer_id";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->bindParam("kinvoice_id", $kinvoice_id);
			$stmt->bindParam("customer_id", $customer_id);	
			$stmt->execute();
			$stmt = null; $db = null;		
			
			trackKInvoice("update_payment", $kinvoice_id);
		}
		
		foreach ($arrAttachments as $attachment) {
			$document_name = $attachment;
			//first check if this document is _already_ attached
			$sql = "SELECT COUNT(doc.document_id) thecount
			FROM `cse_document` doc
			INNER JOIN `cse_check_document` cnd
			ON doc.document_uuid = cnd.document_uuid
			WHERE doc.document_name = '" . $document_name . "'";
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->execute();
			$document_count = $stmt->fetchObject();
			$stmt->closeCursor(); $stmt = null; $db = null;	
			
			if ($document_count->thecount==0) {
				$document_name = explode("/", $document_name);
				$document_name = $document_name[count($document_name) - 1];
				$document_date = date("Y-m-d H:i:s");
				$document_extension = explode(".", $document_name);
				$document_extension = $document_extension[count($document_extension) - 1];
				
				$description = "check attachment";
				$description_html = "check attachment";
				$type = "check_attachment";
				$verified = "Y";
				
				//attachment is a document
				$document_uuid = uniqid("KS");
				$sql = "INSERT INTO cse_document (document_uuid, parent_document_uuid, document_name, document_date, document_filename, document_extension, description, description_html, type, verified, customer_id) 
			VALUES (:document_uuid, :parent_document_uuid, :document_name, :document_date, :document_filename, :document_extension, :description, :description_html, :type, :verified, :customer_id)";
				$db = getConnection();
				$stmt = $db->prepare($sql);  
				$stmt->bindParam("document_uuid", $document_uuid);
				$stmt->bindParam("parent_document_uuid", $document_uuid);
				$stmt->bindParam("document_name", $document_name);
				$stmt->bindParam("document_date", $document_date);
				$stmt->bindParam("document_filename", $document_name);
				$stmt->bindParam("document_extension", $document_extension);
				$stmt->bindParam("description", $description);
				$stmt->bindParam("description_html", $description_html);
				$stmt->bindParam("type", $type);
				$stmt->bindParam("verified", $verified);
				$stmt->bindParam("customer_id", $customer_id);
				$stmt->execute();
				$new_id = $db->lastInsertId();
				$stmt = null; $db = null;	
				//die(print_r($newEmployee));
				trackDocument("insert", $new_id);
				
				$message_document_uuid = uniqid("TD", false);
				$last_updated_date = date("Y-m-d H:i:s");
				$sql = "INSERT INTO cse_check_document (`check_document_uuid`, `check_uuid`, `document_uuid`, `attribute_1`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $message_document_uuid  ."', '" . $table_uuid . "', '" . $document_uuid . "', 'attach', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $customer_id . "')";
				$db = getConnection();
				$stmt = $db->prepare($sql);  
				$stmt->execute();
				$stmt = null; $db = null;	
				if ($case_uuid!="") {
					$sql = "INSERT INTO cse_case_document (`case_document_uuid`, `case_uuid`, `document_uuid`, `attribute_1`, `attribute_2`, `last_updated_date`, `last_update_user`, `customer_id`)
					VALUES ('" . $message_document_uuid  ."', '" . $case_uuid . "', '" . $document_uuid . "', 'attach', '', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $customer_id . "')";
					$db = getConnection();
					$stmt = $db->prepare($sql);  
					$stmt->execute();
					$stmt = null; $db = null;	
				}
			}
		}
		
		if ($case_uuid!="") {
			if ($request_customer_id!="") {
				$sql = "UPDATE cse_case_check
				SET `attribute` = 'firm'
				WHERE `case_uuid` = :case_uuid
				AND `check_uuid` = :check_uuid
				AND `customer_id` = :customer_id";
				
				$db = getConnection();
				$stmt = $db->prepare($sql);  
				$stmt->bindParam("case_uuid", $case_uuid);
				$stmt->bindParam("check_uuid", $table_uuid);
				$stmt->bindParam("customer_id", $customer_id);
				
				$stmt->execute();
				$stmt = null; $db = null;	
			}
		}
		//track now
		trackCheck("update", $table_id);
		
		$db = null;
		
		echo json_encode(array("success"=>$table_id, "recipient"=>$recipient)); 
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}
function printCheck() {
	$id = passed_var("id", "post");
	$copy = passed_var("copy", "post");
	/*
	$customer_id = passed_var("customer_id", "post");
	$user_id = passed_var("user_id", "post");
	$sess_id = passed_var("sess_id", "post");
	
	//make sure the sess_id is valid
	*/
	try {
		/*
		$sql = "SELECT user_id
		FROM ikase.cse_user
		WHERE user_id = :user_id
		AND customer_id = :customer_id
		AND sess_id = :sess_id";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("user_id", $user_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("sess_id", $sess_id);
		$stmt->execute();
		$user = $stmt->fetchObject();
		$db = null;
		
		if (is_object($user)) {
			*/
			//track now
			if ($copy=="check") {
				trackCheck("printed", $id);
			}
			if ($copy=="copy") {
				trackCheck("copy_printed", $id);
			}
			echo json_encode(array("success"=>"check printed"));
		//}
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
       	echo json_encode($error);
	}
}
function clearCheck() {
	$id = passed_var("id", "post");
	$customer_id = $_SESSION['user_customer_id'];
	
	try {
		$sql = "UPDATE cse_check chk
		SET chk.`check_status` = 'C'
		WHERE `check_id`=:id
		AND customer_id = :customer_id";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		//track now
		trackCheck("clear", $id);
		
		$db = null;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
       	echo json_encode($error);
	}
	echo json_encode(array("success"=>"check cleared"));
}
function unclearCheck() {
	$id = passed_var("id", "post");
	$customer_id = $_SESSION['user_customer_id'];
	
	try {
		$sql = "UPDATE cse_check chk
		SET chk.`check_status` = 'P'
		WHERE `check_id`=:id
		AND customer_id = :customer_id";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		//track now
		trackCheck("unclear", $id);
		
		$db = null;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
       	echo json_encode($error);
	}
	echo json_encode(array("success"=>"check uncleared"));
}
function voidCheck() {
	$id = passed_var("id", "post");
	$customer_id = $_SESSION['user_customer_id'];
	
	try {
		$check = getCheckInfo($id);
		$current_payment = $check->payment;
		
		$sql = "UPDATE cse_check chk
		SET chk.`check_status` = 'V'
		WHERE `check_id`=:id
		AND customer_id = :customer_id";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		//track now
		trackCheck("void", $id);
		
		$db = null;
		
		//let's get the kinvoice_id
		//let's update payment
		
		$kinvoice = getKInvoiceByCheck($id, true);
		
		if (is_object($kinvoice)) {
			$kinvoice_id = $kinvoice->id;
					
			//update the payments on kinvoice
			$sql = "UPDATE `cse_kinvoice` 
			SET `payments` = `payments` - " . $current_payment . "
			WHERE `kinvoice_id` = :kinvoice_id
			AND customer_id = :customer_id";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->bindParam("kinvoice_id", $kinvoice_id);
			$stmt->bindParam("customer_id", $customer_id);	
			$stmt->execute();
			$db = null;
			
			trackKInvoice("void_payment", $kinvoice_id);
		}
		echo json_encode(array("success"=>"check marked as void"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function deleteCheck() {
	$id = passed_var("id", "post");
	$customer_id = $_SESSION['user_customer_id'];
	
	try {
		$check = getCheckInfo($id);
		$current_payment = $check->payment;
		
		$sql = "UPDATE cse_check chk
		SET chk.`deleted` = 'Y'
		WHERE `check_id`=:id
		AND customer_id = :customer_id";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		//track now
		trackCheck("delete", $id);
		
		$db = null;
		
		//let's get the kinvoice_id
		//let's update payment
		
		$kinvoice = getKInvoiceByCheck($id, true);
		
		if (is_object($kinvoice)) {
			$kinvoice_id = $kinvoice->id;
					
			//update the payments on kinvoice
			$sql = "UPDATE `cse_kinvoice` 
			SET `payments` = `payments` - " . $current_payment . "
			WHERE `kinvoice_id` = :kinvoice_id
			AND customer_id = :customer_id";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->bindParam("kinvoice_id", $kinvoice_id);
			$stmt->bindParam("customer_id", $customer_id);	
			$stmt->execute();
			$db = null;
			
			trackKInvoice("cancel_payment", $kinvoice_id);
		}
		echo json_encode(array("success"=>"check marked as deleted"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function trackCheck($operation, $check_id) {
	$sql = "INSERT INTO cse_check_track (`user_uuid`, `user_logon`, `operation`, `time_stamp`, `check_id`, `check_uuid`, `carrier_uuid`, `check_number`, `check_date`, `check_type`, `ledger`, `name`, `amount_due`, `payment`, `balance`, `transaction_date`, `memo`, `customer_id`, `deleted`)
	SELECT '" . $_SESSION['user_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $operation . "', '". date("Y-m-d H:i:s") . "', `check_id`, `check_uuid`, `carrier_uuid`, `check_number`, `check_date`, `check_type`, `ledger`, `name`, `amount_due`, `payment`, `balance`, `transaction_date`, `memo`, `customer_id`, `deleted`
	FROM cse_check
	WHERE 1
	AND check_id = " . $check_id . "
	AND customer_id = " . $_SESSION['user_customer_id'] . "
	LIMIT 0, 1";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
	
		$stmt->execute();
		
		$new_id = $db->lastInsertId();
	
		$check = getCheckInfo($check_id);
		//die(print_r($check));
		//new the case_uuid
		$kase = getKaseInfoByCheck($check_id);
		$case_uuid = "";
		if (is_object($kase)) {
			$case_uuid = $kase->uuid;
		}
		$activity_category = "Check";
		switch($operation){
			case "insert":
				$operation .= "ed";
				break;
			case "update":
				$operation .= "d";
				break;
			case "delete":
				$operation .= "d";
				break;
		}
		$activity_uuid = uniqid("KS", false);
		$title = "Payment";
		if ($check->ledger=="OUT") {
			$title = "Disbursment";
		}
		$activity = $title  . " was " . $operation . "  by " . $_SESSION['user_name'] . "
		
		Check #:" . $check->check_number . "
		Check Date:" . date("m/d/y", strtotime($check->check_date)) . "
		Category:" . $check->check_type . "
		Due #:" . $check->amount_due . "
		Amount #:" . $check->payment . "
		Outstanding:" . $check->balance . "
		Memo:" . $check->memo;
		recordActivity($operation, $activity, $case_uuid, $new_id, $activity_category);
	
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}

function getMyCheckRequests($approval) {
	$blnLate = false;
	switch ($approval) {
		case "late":
			$blnLate = true;
		case "pending":
			$approved = "P";
			break;
		case "approved":
			$approved = "Y";
			break;
		case "denied":
			$approved = "N";
			break;
	}
	getCheckRequests("", "", $approved, true, $blnLate);
}
function getApprovedCheckRequests() {
	getCheckRequests("", "", "Y");
}
function getRejectedCheckRequests() {
	getCheckRequests("", "", "N");
}
function getKaseCheckRequests($case_id) {
	getCheckRequests($case_id, "");
}
function getCheckRequestInfo($id) {
	return getCheckRequests("", "", "", false, false, $id, true);
}
function getAllCheckRequests() {
	return getCheckRequests("", "", "", false, false, "", false, true);
}
function getCheckRequest($id, $blnReturn = false) {
	getCheckRequests("", "", "", false, false, $id);
}
function getCheckRequestsByAccount($account, $approved) {
	getCheckRequests("", "", $approved, false, false, "", false, false, $account);
}
function getCheckRequests($case_id = "", $payable_id = "", $approved = "P", $mine = false, $blnLate = false, $checkrequest_id = "", $blnReturn = false, $blnAll = false, $account = "") {
	session_write_close();
	
	$customer_id = $_SESSION["user_customer_id"];
	$today = date("Y-m-d");
	//IF(cis.settlement_uuid IS NULL, 'N', 'Y') 
	$sql = "SELECT DISTINCT cr.*,
	
	IFNULL(IF(payable_type = 'C', corp.corporation_id, pers.person_id), '') payable_id, 
	
	IF (IF(payable_type = 'C', corp.corporation_id, pers.person_id) IS NULL, 'customer', IF(payable_type = 'C', 'corporation', 'person')) payable_table,
	
	ccase.case_id, ccase.case_name, ccase.case_number, ccase.file_number, ccase.case_status, ccase.case_type,
	usr.user_id, usr.user_name, usr.nickname, 
	
	IF(payable_type = 'C', `corp`.`full_name`, `pers`.`full_name`) payable_full_name,
	IF (payable_type = 'C', IF(`corp`.`type` = 'recipient', 'records', 'standard'), 'standard') payable_type,
	
	IFNULL(reviewer.user_name, '') reviewer_name, IFNULL(reviewer.nickname, '') reviewer_nickname, 
	
	cr.checkrequest_id id, cr.checkrequest_uuid uuid,
	IFNULL(acct.account_id, -1) account_id, IFNULL(acct.account_type, '') account_type, IFNULL(acct.account_name, '') account_name,
	IFNULL(`check`.check_id, -1) check_id, '' case_settled 
	 
	FROM cse_checkrequest cr
	
	INNER JOIN cse_case_checkrequest case_check
	ON cr.checkrequest_uuid = case_check.checkrequest_uuid AND case_check.deleted = 'N'
	INNER JOIN cse_case ccase
	ON case_check.case_uuid = ccase.case_uuid
	/*
	INNER JOIN cse_case_injury cinj
	ON ccase.case_uuid = cinj.case_uuid
	LEFT OUTER JOIN cse_injury_settlement cis
	ON cinj.injury_uuid = cis.injury_uuid AND cis.deleted = 'N' AND cis.`attribute` = 'main'
	*/
	LEFT OUTER JOIN cse_check `check`
	ON cr.check_uuid = `check`.check_uuid
	
	LEFT OUTER JOIN cse_corporation_checkrequest ccc
	ON cr.checkrequest_uuid = ccc.checkrequest_uuid AND ccc.deleted = 'N'
	LEFT OUTER JOIN cse_corporation corp
	ON ccc.corporation_uuid = corp.corporation_uuid
	
	LEFT OUTER JOIN cse_person_checkrequest cpc
	ON cr.checkrequest_uuid = cpc.checkrequest_uuid AND cpc.deleted = 'N'
	LEFT OUTER JOIN ";
			
	if (($_SESSION['user_customer_id']==1033)) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "cse_person";
	}
	$sql .= " pers
	ON cpc.person_uuid = pers.person_uuid
	";
	$join = "LEFT OUTER JOIN";
	if ($account=="trust") {
		$join = "INNER JOIN";
	}
	$sql .= "
	" . $join . " cse_account_checkrequest apc
	ON cr.checkrequest_uuid = apc.checkrequest_uuid AND apc.deleted = 'N'
	" . $join . " cse_account acct
	ON apc.account_uuid = acct.account_uuid
	
	INNER JOIN ikase.cse_user usr
	ON cr.requested_by = usr.user_uuid
	
	LEFT OUTER JOIN ikase.cse_user reviewer
	ON cr.reviewed_by = reviewer.user_uuid
	
	WHERE 1";
	$sql .= "
	AND cr.customer_id = :customer_id";
	
	if (!$blnReturn) {
		$sql .= "
		AND cr.deleted = 'N'";
	}
	if (!$blnAll) {
		if ($case_id=="") {
			if ($approved!="") {
				$sql .= "
				AND cr.approved = '" . $approved . "'";
			}
			if ($mine) {
				$sql .= "
				AND cr.requested_by = '" . $_SESSION["user_id"] . "'";
			}
		}
	
		if ($case_id!="") {
			$sql .= "
			AND ccase.case_id = :case_id";
		}
		if ($checkrequest_id!="") {
			$sql .= "
			AND cr.checkrequest_id = :checkrequest_id";
		}
		if ($payable_id!="") {
			$sql .= "
			AND (
				IF (cr.payable_type = 'C', corp.corporation_id = :payable_id, pers.person_id = :payable_id) 
			)";
		}
		
		if ($blnLate) {
			$sql .= "
			AND cr.needed_date < :today";
		}
		if ($checkrequest_id=="") {
			$sql .= "
			AND INSTR(cr.checkrequest_uuid, 'KS') > 0";
		}
		
		if ($account=="trust") {
			$sql .= "
			AND acct.account_type = :account
			AND acct.deleted = 'N'
			AND acct.account_id IS NOT NULL";
		}
		if ($account=="operating") {
			$sql .= "
			AND acct.account_id IS NULL";
		}
	}
	
	if ($approved=="M") {
		$sql .= "
		ORDER BY cr.approved DESC, cr.request_date ASC";
	} else {
		$sql .= "
		ORDER BY cr.request_date DESC";
	}
	if ($_SERVER['REMOTE_ADDR'] == "172.119.227.47") {
		die($sql);
	}
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("customer_id", $customer_id);
		if ($case_id!="") {
			$stmt->bindParam("case_id", $case_id);
		}
		if ($payable_id!="") {
			$stmt->bindParam("payable_id", $payable_id);
		}
		if ($blnLate) {
			$stmt->bindParam("today", $today);
		}
		if ($checkrequest_id!="") {
			$stmt->bindParam("checkrequest_id", $checkrequest_id);
		}
		if ($account=="trust") {
			$stmt->bindParam("account", $account);
		}
		$stmt->execute();
		if ($checkrequest_id=="") {
			$checkrequests = $stmt->fetchAll(PDO::FETCH_OBJ);
		} else {
			$checkrequests = $stmt->fetchObject();
		}
		$db = null;
		
		if ($blnReturn) {
			return $checkrequests;
		} else {
			echo json_encode($checkrequests);
		}
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}
function attachCheckRequests() {
	session_write_close();
	$case_id = passed_var("case_id", "post");
	$customer_id = $_SESSION["user_customer_id"];
	
	$sql = "INSERT INTO `cse_account_checkrequest`
	(
	`account_checkrequest_uuid`,
	`account_uuid`,
	`checkrequest_uuid`,
	`attribute`,
	`last_updated_date`,
	`last_update_user`,
	
	`customer_id`)
	
	SELECT 
	REPLACE(req.checkrequest_uuid, 'KS', 'AR') `account_checkrequest_uuid`, 
	'RD5c05af208768f' account_uuid, 
	req.checkrequest_uuid, 'main', 
	'" . date("Y-m-d H:i:s") . "', 
	'system',
	req.customer_id
	FROM cse_checkrequest req
	INNER JOIN cse_case_checkrequest creq
	ON req.checkrequest_uuid = creq.checkrequest_uuid
	INNER JOIN cse_case ccase
	ON creq.case_uuid = ccase.case_uuid
	LEFT OUTER JOIN cse_account_checkrequest areq
	ON req.checkrequest_uuid = areq.checkrequest_uuid
	LEFT OUTER JOIN cse_account acct
	ON areq.account_uuid = acct.account_uuid
	WHERE ccase.case_id = :case_id
	AND ccase.customer_id = :customer_id
	AND acct.account_id IS NULL
	AND req.approved = 'P'";
	
	//not turned on yet
}
function addCheckRequest() {
	session_write_close();
	//die(print_r($_POST));
	$request = Slim::getInstance()->request();
	$db = getConnection();
	$arrFields = array();
	$arrSet = array();
	$table_name = "";
	$table_id = "";
	$customer_id = $_SESSION['user_customer_id'];
	$case_id =  passed_var("case_id", "post");
	if (!isset($_POST["payable_to"])) {
		$payable_to_id =  passed_var("corp_id", "post") . "|C";
	} else {
		$payable_to_id =  passed_var("payable_to", "post");
	}
	$payable_to = "";
	$rush_request = "N";
	$needed_date = "";
	$request_date = "";
	$amount = 0;
	$reason = "";
	$account_id = "";
	//person or corporation
	$arrParties = explode("|", $payable_to_id);
	//die(print_r($arrParties));
	$corporation_id = "";
	if ($arrParties[1]=="C") {
		$corporation_id = $arrParties[0];
		$corporation = getCorporationInfo($corporation_id);
		$payable_to = $corporation->company_name;
		$payable_type = 'C';
	}
	$person_id = "";
	if ($arrParties[1]=="P") {
		$person_id = $arrParties[0];
		$person = getPersonInfo($person_id);
		$payable_to = $person->full_name;
		$payable_type = 'P';
	}
	$request_customer_id = "";
	if ($arrParties[1]=="X" || $arrParties[1]=="F") {
		//firm checkrequest
		$request_customer_id = $customer_id;
		$payable_type = 'F';
	}
	
	$checkrequest_id = -1;
	
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		
		$fieldname = str_replace("Input", "", $fieldname);
		//fix for defaults
		if ($fieldname=="amount") {
			if ($value=="") {
				$value = 0.00;
			}
			$amount = $value;
		}
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="reason") {
			$reason = $value;
		}
		if ($fieldname=="rush_request") {
			$rush_request = $value;
			continue;
		}
		if ($fieldname=="case_id") {
			$case_id = $value;
			continue;
		}
		if ($fieldname=="account_id") {
			$account_id = $value;
			continue;
		}
		
		//FOR NOW
		if ($fieldname=="table_id" || $fieldname=="corp_id" || $fieldname=="payable_to") {
			continue;
		}

		if (strpos($fieldname, "_date") > -1) {
			if ($value!="") {
				$value = date("Y-m-d", strtotime($value));
			} else {
				$value = "0000-00-00";
			}
			if ($fieldname=="request_date") {
				$request_date = $value;
			}
			if ($fieldname=="needed_date") {
				$needed_date = $value;
			}
		}

		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "'" . addslashes($value) . "'";
	}
	
	$case_uuid = "";
	$case_number = "";
	if ($case_id!="") {
		$kase = getKaseInfo($case_id);
		$case_uuid = $kase->uuid;
		$case_number = $kase->case_number;
		if ($case_number=="") {
			$case_number = $kase->file_number;
		}
	}
	$account_uuid = "";
	if ($account_id!="" && $account_id!="-1") {
		$account = getBankAccountInfo($account_id);
		$account_uuid = $account->uuid;
	}
	$arrFields[] = "`rush_request`";
	$arrSet[] = "'" . $rush_request . "'";
	
	$arrFields[] = "`customer_id`";
	$arrSet[] = "'" . $customer_id . "'";
		
	$arrFields[] = "`requested_by`";
	$arrSet[] = "'" . $_SESSION['user_id'] . "'";
	
	$arrFields[] = "`payable_to`";
	if ($request_customer_id!="") {
		$payable_to = $_SESSION["user_customer_name"];
	}
	$arrSet[] = "'" . addslashes($payable_to) . "'";
	
	$arrFields[] = "`payable_type`";
	$arrSet[] = "'" . $payable_type . "'";
	
	//print_r($arrFields);
	//die(print_r($arrSet));
	
	$table_uuid = uniqid("KS", false);
	$sql = "INSERT INTO `cse_" . $table_name ."` (`" . $table_name . "_uuid`, " . implode(",", $arrFields) . ") 
			VALUES('" . $table_uuid . "', " . implode(",", $arrSet) . ")";
			//die(print_r($arrFields));
	//die($sql);  	
	$last_updated_date = date("Y-m-d H:i:s");
	try { 
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		$new_id = $db->lastInsertId();
		
		if ($account_id!="") {
			$account_table_uuid = uniqid("KR", false);
			$attribute_1 = "main";
			
			//now we have to attach the check to the account 
			$sql = "INSERT INTO cse_account_" . $table_name . " (`account_" . $table_name . "_uuid`, `account_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $account_table_uuid  ."', '" . $account_uuid . "', '" . $table_uuid . "', '" . $attribute_1 . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			
			$stmt = $db->prepare($sql);  	
			$stmt->execute();
		}
		
		if ($case_id!="") {
			$case_table_uuid = uniqid("KR", false);
			$attribute_1 = "main";
			
			if ($request_customer_id!="") {
				$attribute_1 = "firm";
			}
			//now we have to attach the check to the case 
			$sql = "INSERT INTO cse_case_" . $table_name . " (`case_" . $table_name . "_uuid`, `case_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', '" . $attribute_1 . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			
			$stmt = $db->prepare($sql);  	
			$stmt->execute();
		}
		
		if ($corporation_id!="") {
			$payable_id = $corporation_id;
			$payable_table = "corporation";
			
			$case_table_uuid = uniqid("RC", false);
			$attribute_1 = "main";
			
			//now we have to attach the check to the corp 
			$sql = "INSERT INTO cse_corporation_" . $table_name . " (`corporation_" . $table_name . "_uuid`, `corporation_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_table_uuid  ."', '" . $corporation->uuid . "', '" . $table_uuid . "', '" . $attribute_1 . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			
			$stmt = $db->prepare($sql);  	
			$stmt->execute();
		}
		
		if ($person_id!="") {
			$payable_id = $person_id;
			$payable_table = "person";
			$case_table_uuid = uniqid("RP", false);
			$attribute_1 = "main";
			
			//now we have to attach the check to the person 
			$sql = "INSERT INTO cse_person_" . $table_name . " (`person_" . $table_name . "_uuid`, `person_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_table_uuid  ."', '" . $person->uuid . "', '" . $table_uuid . "', '" . $attribute_1 . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			
			$stmt = $db->prepare($sql);  	
			$stmt->execute();
		}
		
		//track now
		trackCheckRequest("insert", $new_id);
		
		$db = null;
		
		echo json_encode(array("id"=>$new_id, "uuid"=>$table_uuid, "payable_to"=>str_replace("|", "_", $payable_to_id))); 
		
		//let's notify the check request authorizer
		$sql = "SELECT user_id, user_uuid, nickname
		FROM ikase.cse_user
		WHERE cis_id = 1
		AND customer_id = :customer_id";
		
		$customer_id = $_SESSION['user_customer_id'];
		
		$db = getConnection();
		$stmt = $db->prepare($sql);  	
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$users =  $stmt->fetchAll(PDO::FETCH_OBJ);
		
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		if ($request_customer_id!="") {
			$payable_to = $_SESSION["user_customer_name"];
			$payable_id = $customer_id;
			$payable_table = "customer";
		}
		$from = $_SESSION["user_nickname"];
		$dateandtime = date("Y-m-d H:i:s");
		$pcounter = 0;
		$subject = "Check Request from " . $_SESSION["user_name"];
		$message = "<a href='#checkrequests'>Check Request</a> for Case <a href='v8.php?n=#kases/" . $case_id . "' target='_blank'>" . $case_number . "</a>
		<a href='#payments/" . $case_id . "' target='_blank'>Review Books</a>";
		$message .= "<br /><br />Payable To:" . $payable_to;
		$message .= "<br />Needed By:" . date("m/d/Y", strtotime($needed_date));
		if ($rush_request=="Y") {
			$message .= "&nbsp;<span style='background:red; color:white; padding:2px'>RUSH</span>";
		}
		$message .= "<br />Reason:" . $reason;
		$message .= '<br /><br /><button class="btn btn-xs btn-success approve_request" id="approve_request_' . $new_id . '" onclick="parent.confirmApprovalRequest(event)">Approve</button>&nbsp;|&nbsp;<button class="btn btn-xs btn-danger reject_request" id="reject_request_' . $new_id . '" onclick="parent.confirmRejectRequest(event)">Reject</button>
		<form id="request_approval_form">
			<input type="hidden" id="request_id" value="' . $new_id . '" />
			<input type="hidden" id="request_case_id" value="' . $case_id . '" />
			<input type="hidden" id="request_nickname" value="' . $_SESSION["user_nickname"] . '" />
			<input type="hidden" id="request_date" value="' .  date("Y-m-d") . '" />
			<input type="hidden" id="payable_id" value="' . $payable_id . '" />
			<input type="hidden" id="payable_table" value="' . $payable_table . '" />
			<input type="hidden" id="payable_to" value="' .  $payable_to . '" />
			<input type="hidden" id="request_case_name" value="' .  $case_number . '" />
			<input type="hidden" id="request_amount" value="' . $amount . '" />
		</form>';
		
		foreach($users as $to_user) {
			$message_to = $to_user->nickname;
			$thread_uuid = uniqid("TD", false);
			
			//i have the worker, i can send an interoffice message
			$sql = "INSERT INTO `cse_thread` (`customer_id`, `dateandtime`, `thread_uuid`, `from`, `subject`) 
			VALUES('" . $customer_id . "', '" . $dateandtime . "','" . $thread_uuid . "', '" . $from . "', '" . $subject . "')";
			//echo $sql . "<br />";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);						
			$stmt->execute();
			$stmt = null; $db = null;
			
			$message_uuid = uniqid("K" . $pcounter, false);
			$reminder_message_uuid = uniqid("RM", false);
	
			$sql = "INSERT INTO cse_message (`message_uuid`, `message_type`, `dateandtime`, `from`, `message_to`, `message`, `callback_date`, `customer_id`)
			VALUES ('" . $message_uuid . "', 'reminder', '" . $dateandtime . "', 'system', '" . $message_to . "', '" . addslashes($message) . "', '0000-00-00 00:00:00', '" . $customer_id . "')";   
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->execute();	
			$message_id = $db->lastInsertId();
			$stmt = null; $db = null;
			
			$case_message_uuid = uniqid("T" . $pcounter, false);
			$sql = "INSERT INTO cse_thread_message (`thread_message_uuid`, `thread_uuid`, `message_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`, message_id)
			VALUES ('" . $case_message_uuid  ."', '" . $thread_uuid . "', '" . $message_uuid . "', 'main', '" . $dateandtime . "', '" . $_SESSION["user_id"] . "', '" . $customer_id . "','" . $message_id . "')";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			$stmt = null; $db = null;
			
			//source
			$message_user_uuid = uniqid("T" . $pcounter, false);
			$sql = "INSERT INTO cse_message_user 
			(`message_user_uuid`, `message_uuid`, `user_uuid`, `type`, `last_updated_date`, `last_update_user`, `customer_id`, `thread_uuid`, message_id, user_id)
			VALUES ('" . $message_user_uuid  ."', '" . $message_uuid . "', '" . $_SESSION["user_id"] . "', 'from', '" . $dateandtime . "', '" . $_SESSION["user_id"] . "', '" . $customer_id . "', '". $thread_uuid . "','" . $message_id . "','" . $_SESSION["user_plain_id"] . "')";
			//echo $sql . "<br />";	
	
			$db = getConnection();	
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			$stmt = null; $db = null;
			
			//destination
			$message_user_uuid = uniqid("F" . $pcounter, false); 
			$sql = "INSERT INTO cse_message_user (`message_user_uuid`, `message_uuid`, `user_uuid`, `thread_uuid`, `type`, `read_date`, `action`, `last_updated_date`, `last_update_user`, `customer_id`, `user_type`, message_id, user_id)
			VALUES ('" . $message_user_uuid . "', '" . $message_uuid . "', '" . $to_user->user_uuid . "', '', 'to', '0000-00-00 00:00:00', 'reminder', '0000-00-00 00:00:00', '" . $_SESSION["user_id"] . "', '" . $customer_id . "', 'user','" . $message_id . "','" . $to_user->user_id . "')";
			
			$db = getConnection();	
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			$stmt = null; $db = null;
			
			$pcounter++;
		}
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}
function approveCheckRequest() {
	session_write_close();

	$id = passed_var("id", "post");
	$check_number = passed_var("check_number", "post");
	
	$review_date = date("Y-m-d");
	$user_uuid = $_SESSION["user_id"];
	$customer_id = $_SESSION["user_customer_id"];
	
	$sql = "UPDATE `cse_checkrequest`
	SET `approved` = 'Y',
	`check_number` = :check_number,
	`review_date` = :review_date,
	`reviewed_by` = :user_uuid
	WHERE `checkrequest_id` = :id
	AND customer_id = :customer_id";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->bindParam("review_date", $review_date);
		$stmt->bindParam("check_number", $check_number);
		$stmt->bindParam("user_uuid", $user_uuid);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$stmt = null; $db = null;
		
		//update the message
		$sql = "UPDATE`cse_message`
		SET `message` = CONCAT('<div>APPROVED</div>', `message`),
		`status` = 'approved'
		WHERE `message` LIKE '%\"request_id\" value=\"" . $id . "%'
		AND customer_id = :customer_id";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$stmt = null; $db = null;
		
		trackCheckRequest("approve", $id);
		/*
		//do we have a setting for check number?
		$sql = "SELECT setting_value
		FROM cse_setting cset
		WHERE cset.setting = 'check_number'
		AND cset.customer_id = :customer_id";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$setting = $stmt->fetchObject();
		$stmt = null; $db = null;
		
		if (is_object($setting)) {
			if ($setting->setting_value = $check_number) {
				//increment the case_number_next
				$sql = "UPDATE cse_setting cset
				SET cset.setting_value = cset.setting_value + 1
				WHERE cset.setting = 'check_number'
				AND cset.customer_id = :customer_id";
				
				//echo $sql . "\r\n";
				
				$db = getConnection();
				$stmt = $db->prepare($sql);  
				$stmt->bindParam("customer_id", $customer_id);
				$stmt->execute();
				$db = null;
			}
		} else {
			if (is_numeric($check_number)) {
				$check_number++;
				//let's start it from here
				$setting_uuid = uniqid("ST", false);
				//increment the case_number_next
				$sql = "INSERT INTO cse_setting 
				(setting_uuid, category, setting, setting_value, customer_id)
				VALUES ('" . $setting_uuid . "', 'checks', 'check_number', '" . $check_number . "', :customer_id)";
				
				//echo $sql . "\r\n";
				
				$db = getConnection();
				$stmt = $db->prepare($sql);  
				$stmt->bindParam("customer_id", $customer_id);
				$stmt->execute();
				$db = null;
			}
		}
		*/
		echo json_encode(array("success"=>true));
	} catch(PDOException $e) {
		$error = array("error3"=> array("text"=>$e->getMessage()));
		die(json_encode($error));
	}	
}
function rejectCheckRequest() {
	session_write_close();

	$id = passed_var("id", "post");
	$reject_reason = passed_var("reject_reason", "post");
	
	$review_date = date("Y-m-d");
	$user_uuid = $_SESSION["user_id"];
	$customer_id = $_SESSION["user_customer_id"];
	
	$sql = "UPDATE `cse_checkrequest`
	SET `approved` = 'N',
	`rejection_reason` = :reject_reason,
	`review_date` = :review_date,
	`reviewed_by` = :user_uuid
	WHERE `checkrequest_id` = :id
	AND customer_id = :customer_id";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->bindParam("review_date", $review_date);
		$stmt->bindParam("reject_reason", $reject_reason);
		$stmt->bindParam("user_uuid", $user_uuid);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$stmt = null; $db = null;
		
		//update the message
		$sql = "UPDATE`cse_message`
		SET `message` = CONCAT('<div>DENIED</div>', `message`),
		`status` = 'rejected'
		WHERE `message` LIKE '%\"request_id\" value=\"" . $id . "%'";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$stmt = null; $db = null;
		
		trackCheckRequest("reject", $id);
		
		echo json_encode(array("success"=>true));
	} catch(PDOException $e) {
		$error = array("error3"=> array("text"=>$e->getMessage()));
		die(json_encode($error));
	}	
}
function deleteCheckRequest() {
	session_write_close();

	$id = passed_var("id", "post");
	
	$review_date = date("Y-m-d");
	$user_uuid = $_SESSION["user_id"];
	$customer_id = $_SESSION["user_customer_id"];
	$reject_reason = "Deleted by " . $_SESSION["user_nickname"];
	
	$sql = "UPDATE `cse_checkrequest`
	SET `approved` = 'N',
	`deleted` = 'Y',
	`rejection_reason` = :reject_reason
	WHERE `checkrequest_id` = :id
	AND customer_id = :customer_id";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->bindParam("reject_reason", $reject_reason);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$stmt = null; $db = null;
		
		//update the message
		$sql = "UPDATE`cse_message`
		SET `message` = CONCAT('<div>DELETED</div>', `message`),
		`status` = 'deleted'
		WHERE `message` LIKE '%\"request_id\" value=\"" . $id . "%'";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$stmt = null; $db = null;
		
		trackCheckRequest("delete", $id);
		
		echo json_encode(array("success"=>true));
	} catch(PDOException $e) {
		$error = array("error3"=> array("text"=>$e->getMessage()));
		die(json_encode($error));
	}	
}
function detachCheckRequest() {
	session_write_close();
	
	$id = passed_var("id", "post");
	
	$customer_id = $_SESSION["user_customer_id"];
	
	$sql = "UPDATE cse_account_checkrequest cac, cse_checkrequest req
	SET cac.deleted = 'Y'
	WHERE 1
	AND cac.checkrequest_uuid = req.checkrequest_uuid
	AND req.checkrequest_id = :id
	AND req.customer_id = :customer_id";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$stmt = null; $db = null;
		
		trackCheckRequest("detach", $id);
		
		echo json_encode(array("success"=>true));
	} catch(PDOException $e) {
		$error = array("error3"=> array("text"=>$e->getMessage()));
		die(json_encode($error));
	}	
}
function voidCheckRequest() {
	session_write_close();

	$id = passed_var("id", "post");
	
	$review_date = date("Y-m-d");
	$user_uuid = $_SESSION["user_id"];
	$customer_id = $_SESSION["user_customer_id"];
	$reject_reason = "Void by " . $_SESSION["user_nickname"];
	
	$sql = "UPDATE `cse_checkrequest`
	SET `approved` = 'V',
	`rejection_reason` = :reject_reason
	WHERE `checkrequest_id` = :id
	AND customer_id = :customer_id";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->bindParam("reject_reason", $reject_reason);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$stmt = null; $db = null;
		
		//update the message
		$sql = "UPDATE`cse_message`
		SET `message` = CONCAT('<div>VOID</div>', `message`),
		`status` = 'void'
		WHERE `message` LIKE '%\"request_id\" value=\"" . $id . "%'";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$stmt = null; $db = null;
		
		trackCheckRequest("void", $id);
		
		echo json_encode(array("success"=>true));
	} catch(PDOException $e) {
		$error = array("error3"=> array("text"=>$e->getMessage()));
		die(json_encode($error));
	}	
}
function trackCheckRequest($operation, $checkrequest_id) {
	$sql = "INSERT INTO cse_checkrequest_track (`user_uuid`, `user_logon`, `operation`, `time_stamp`, `checkrequest_id`, `checkrequest_uuid`, `check_uuid`, `requested_by`, `payable_to`, `payable_type`, `rush_request`, `request_date`, `amount`, `needed_date`, `reason`, `reviewed_by`, `review_date`, `approved`, `check_number`, `rejection_reason`, `customer_id`, `deleted`)
	SELECT '" . $_SESSION['user_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $operation . "', '". date("Y-m-d H:i:s") . "', `checkrequest_id`, `checkrequest_uuid`, `check_uuid`, `requested_by`, `payable_to`, `payable_type`, `rush_request`, `request_date`, `amount`, `needed_date`, `reason`, `reviewed_by`, `review_date`, `approved`, `check_number`, `rejection_reason`, `customer_id`, `deleted`
	FROM cse_checkrequest
	WHERE 1
	AND checkrequest_id = " . $checkrequest_id . "
	AND customer_id = " . $_SESSION['user_customer_id'] . "
	LIMIT 0, 1";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
	
		$stmt->execute();
		
		$new_id = $db->lastInsertId();
	
		$checkrequest = getCheckRequestInfo($checkrequest_id);
		//die(print_r($check));
		//new the case_uuid
		$kase = getKaseInfoByCheckRequest($checkrequest_id);
		$case_uuid = "";
		if (is_object($kase)) {
			$case_uuid = $kase->uuid;
		}
		$activity_category = "Check Request";
		switch($operation){
			case "reject":
			case "insert":
				$operation .= "ed";
				break;
			case "approve":
			case "update":
			case "delete":
				$operation .= "d";
				break;
		}
		$activity_uuid = uniqid("KS", false);
		$title = "Check Request";
		$activity = $title  . " was " . $operation . "  by " . $_SESSION['user_name'] . "
		
		Payable To:" . $checkrequest->payable_to . "
		Request Date:" . date("m/d/y", strtotime($checkrequest->request_date)) . "
		Needed Date:" . date("m/d/y", strtotime($checkrequest->needed_date)) . "
		Amount : $" . $checkrequest->amount . "
		Reason:" . $checkrequest->reason;
		if ($checkrequest->rush_request=="Y") {
			$activity .= "
			RUSH";
		}
		$status = "Pending";
		if ($checkrequest->approved=="N") {
			$status = "Denied";
		}
		if ($checkrequest->approved=="Y") {
			$status = "Approved";
		}
		$activity .= "
		Status:" . $status;
			
		if ($checkrequest->reviewed_by!="") {
			$activity .= "
			Reviewed By:" . $checkrequest->reviewer_name . "
			Review Date:" . date("m/d/Y g:A", strtotime($checkrequest->review_date));
		}
		if ($checkrequest->approved=="N") {
			$activity .= "
			Rejected Reason:" . $checkrequest->rejection_reason;
		}
		recordActivity($operation, $activity, $case_uuid, $new_id, $activity_category);
	
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
?>